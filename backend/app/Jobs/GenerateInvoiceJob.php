<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Services\InvoiceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Exception;

class GenerateInvoiceJob implements ShouldQueue
{
    use Queueable;

    public $transaction;
    public $tries = 3;
    public $timeout = 120;
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
        $this->onQueue(config('invoice.queue.name', 'invoices'));
    }

    /**
     * Execute the job.
     */
    public function handle(InvoiceService $invoiceService): void
    {
        try {
            Log::info('Starting invoice generation', [
                'transaction_id' => $this->transaction->id,
                'transaction_number' => $this->transaction->transaction_number,
            ]);

            // Check if transaction is eligible for invoice
            if (!$this->isEligibleForInvoice()) {
                Log::warning('Transaction not eligible for invoice generation', [
                    'transaction_id' => $this->transaction->id,
                    'status' => $this->transaction->status,
                ]);
                return;
            }

            // Generate invoice
            $invoice = $invoiceService->generateInvoiceFromTransaction($this->transaction);

            if ($invoice) {
                Log::info('Invoice generated successfully', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'transaction_id' => $this->transaction->id,
                ]);

                // Mark as issued and send email
                $invoiceService->markAsIssued($invoice);
                
                Log::info('Invoice issued and email sent', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                ]);
            } else {
                Log::warning('Invoice already exists for transaction', [
                    'transaction_id' => $this->transaction->id,
                ]);
            }

        } catch (Exception $e) {
            Log::error('Failed to generate invoice', [
                'transaction_id' => $this->transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger retry logic
            throw $e;
        }
    }

    /**
     * Check if transaction is eligible for invoice generation.
     */
    protected function isEligibleForInvoice(): bool
    {
        // Only generate invoices for succeeded transactions
        if ($this->transaction->status !== 'succeeded') {
            return false;
        }

        // Don't generate invoices for failed or cancelled transactions
        if (in_array($this->transaction->status, ['failed', 'cancelled', 'refunded'])) {
            return false;
        }

        // Must have a user associated
        if (!$this->transaction->user_id) {
            return false;
        }

        return true;
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Invoice generation job failed permanently', [
            'transaction_id' => $this->transaction->id,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Optionally notify admins about the failure
        // You could send a notification to administrators here
    }

    /**
     * Calculate the number of seconds to wait before retrying.
     */
    public function backoff(): array
    {
        // Exponential backoff: 60s, 120s, 240s
        return [60, 120, 240];
    }
}

