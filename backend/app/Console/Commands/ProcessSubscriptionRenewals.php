<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\Transaction;
use App\Services\InvoiceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Process automatic subscription renewals for rows where next_billing_date <= today()
 * and subscription status == active.
 *
 * NOTE: Auto-charging requires actual payment gateway integration (Fiuu card-on-file tokenization
 * or Stripe Subscriptions). This command handles the STATUS MACHINE side (creates pending
 * transactions/invoices, transitions to PAST_DUE on initial failure, triggers dunning).
 * The actual charge() call is the responsibility of a Fiuu auto-debit handler plugged here.
 */
class ProcessSubscriptionRenewals extends Command
{
    protected $signature = 'subscriptions:process-renewals {--dry-run : Only log intended actions, no writes}';
    protected $description = 'Process active subscriptions due for renewal; create Transactions + Invoices and transition status.';

    public function handle(InvoiceService $invoiceService): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $this->info($dryRun ? '[DRY RUN] Processing renewals (no writes)...' : 'Processing renewals...');

        $due = Subscription::dueForBilling()
            ->with(['user'])
            ->get();

        $countProcessed = 0;
        $countPaid = 0;
        $countPastDue = 0;

        foreach ($due as $subscription) {
            try {
                DB::transaction(function () use ($subscription, $dryRun, &$countPaid, &$countPastDue) {
                    $countProcessed++;
                    $nextBilling = $subscription->next_billing_date;
                    $periodStart = $nextBilling ?? now();
                    $periodEnd = match (strtolower($subscription->interval ?? 'monthly')) {
                        'yearly', 'annual' => $periodStart->copy()->addYear(),
                        'weekly' => $periodStart->copy()->addWeek(),
                        default => $periodStart->copy()->addMonth(),
                    };

                    // --- 1. Create pending Transaction for this billing cycle
                    $txn = Transaction::make([
                        'user_id' => $subscription->user_id,
                        'subscription_id' => $subscription->id,
                        'payment_method_id' => $subscription->payment_method_id,
                        'provider' => $subscription->provider,
                        'type' => 'subscription_renewal',
                        'amount' => $subscription->amount,
                        'currency' => $subscription->currency ?? 'MYR',
                        'status' => 'pending',
                        'description' => "Renewal {$subscription->plan_name} - {$periodStart->toDateString()} to {$periodEnd->toDateString()}",
                    ]);

                    if (!$dryRun) {
                        $txn->save();
                    }

                    // --- 2. Attempt charge (payment gateway plugin-point)
                    //     For now, we mark PAST_DUE because there is no saved card auto-debit
                    //     integration. TODO: replace with actual call to Fiuu/Stripe card-on-file.
                    $chargeSucceeded = false;
                    // $chargeSucceeded = $this->attemptAutoCharge($subscription, $txn);

                    if ($chargeSucceeded) {
                        if (!$dryRun) {
                            $txn->update([
                                'status' => 'succeeded',
                                'provider_transaction_id' => $this->extractProviderTxnId($subscription, $txn) ?? null,
                            ]);
                            $subscription->update([
                                'current_period_start' => $periodStart,
                                'current_period_end' => $periodEnd,
                                'next_billing_date' => $periodEnd,
                            ]);
                            try {
                                $invoiceService->generateInvoiceFromTransaction($txn);
                            } catch (\Throwable $e) {
                                Log::warning('Failed to generate invoice for renewal txn '.$txn->id, ['e' => $e->getMessage()]);
                            }
                        }
                        $countPaid++;
                    } else {
                        // Past due state: mark subscription and record dunning log
                        if (!$dryRun) {
                            $txn->update(['status' => 'failed']);
                            $subscription->update(['status' => 'past_due']);
                            try {
                                $subscription->dunningLogs()->create([
                                    'user_id' => $subscription->user_id,
                                    'attempt_number' => 1,
                                    'status' => 'failed',
                                    'error_message' => 'No saved payment method / auto-debit integration pending',
                                    'next_retry_at' => now()->addDays(2),
                                ]);
                            } catch (\Throwable) { /* ignore if dunning table/relation missing */ }
                        }
                        $countPastDue++;
                    }
                });
            } catch (\Throwable $e) {
                Log::error('Renewal processing failed for subscription '.$subscription->id, [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->error("Subscription {$subscription->id}: {$e->getMessage()}");
            }
        }

        $this->table(['Metric', 'Count'], [
            ['Subscriptions due', $due->count()],
            ['Processed (transaction created)', $countProcessed],
            ['Charge PAID (auto-debit)', $countPaid],
            ['Transitioned to PAST_DUE', $countPastDue],
        ]);

        Log::info('subscriptions:process-renewals completed', [
            'due' => $due->count(),
            'paid' => $countPaid,
            'past_due' => $countPastDue,
            'dry_run' => $dryRun,
        ]);

        return Command::SUCCESS;
    }
}
