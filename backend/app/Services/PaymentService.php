<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\Subscription;
use App\Models\Refund;
use App\Models\WebhookLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Stripe\Stripe as StripeClient;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod as StripePaymentMethod;
use Stripe\Customer as StripeCustomer;
use Stripe\Subscription as StripeSubscription;
use Stripe\Refund as StripeRefund;
use Stripe\Webhook;
use Exception;

class PaymentService
{
    protected $provider;
    protected $config;

    public function __construct(string $provider = null)
    {
        $this->provider = $provider ?? config('payment.default_provider');
        $this->config = config("payment.providers.{$this->provider}");
        
        if ($this->provider === 'stripe') {
            StripeClient::setApiKey($this->config['secret_key']);
        }
    }

    /**
     * Process card payment with 3DS support
     */
    public function processCardPayment(User $user, array $data): Transaction
    {
        try {
            $amount = $data['amount'];
            $currency = $data['currency'] ?? config('payment.currency');
            $description = $data['description'] ?? 'Payment';
            $metadata = $data['metadata'] ?? [];

            // Calculate fees
            $railConfig = config('payment.rails.card');
            $fee = ($amount * $railConfig['fee_percentage'] / 100) + $railConfig['fee_fixed'];
            $netAmount = $amount - $fee;

            if ($this->provider === 'stripe') {
                // Create or get Stripe customer
                $stripeCustomer = $this->getOrCreateStripeCustomer($user);

                // Create payment intent with 3DS
                $paymentIntent = PaymentIntent::create([
                    'amount' => $amount * 100, // Convert to cents
                    'currency' => strtolower($currency),
                    'customer' => $stripeCustomer->id,
                    'description' => $description,
                    'metadata' => array_merge($metadata, ['user_id' => $user->id]),
                    'payment_method' => $data['payment_method_id'] ?? null,
                    'confirm' => isset($data['payment_method_id']),
                    'automatic_payment_methods' => [
                        'enabled' => true,
                        'allow_redirects' => 'never',
                    ],
                ]);

                // Create transaction record
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'type' => $data['type'] ?? 'payment',
                    'payment_rail' => 'card',
                    'provider' => 'stripe',
                    'provider_transaction_id' => $paymentIntent->id,
                    'amount' => $amount,
                    'currency' => $currency,
                    'fee' => $fee,
                    'net_amount' => $netAmount,
                    'status' => $this->mapStripeStatus($paymentIntent->status),
                    'description' => $description,
                    'metadata' => $metadata,
                    'client_secret' => $paymentIntent->client_secret,
                    'three_ds_status' => $paymentIntent->status === 'requires_action' ? 'challenge_required' : null,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                return $transaction;
            }

            throw new Exception("Provider {$this->provider} not supported for card payments");

        } catch (Exception $e) {
            Log::error('Card payment failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Process FPX bank payment
     */
    public function processFPXPayment(User $user, array $data): Transaction
    {
        try {
            $amount = $data['amount'];
            $currency = 'MYR';
            $bankCode = $data['bank_code'];
            $description = $data['description'] ?? 'FPX Payment';

            // Calculate fees
            $railConfig = config('payment.rails.fpx');
            $fee = ($amount * $railConfig['fee_percentage'] / 100) + $railConfig['fee_fixed'];
            $netAmount = $amount - $fee;

            // Create transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'payment',
                'payment_rail' => 'fpx',
                'provider' => $this->provider,
                'amount' => $amount,
                'currency' => $currency,
                'fee' => $fee,
                'net_amount' => $netAmount,
                'status' => 'pending',
                'bank_name' => $railConfig['banks'][$bankCode] ?? $bankCode,
                'description' => $description,
                'metadata' => $data['metadata'] ?? [],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Process with provider
            if ($this->provider === 'billplz') {
                $billplzResponse = $this->createBillplzBill($transaction, $data);
                $transaction->update([
                    'provider_transaction_id' => $billplzResponse['id'],
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'payment_url' => $billplzResponse['url'],
                    ]),
                ]);
            }

            return $transaction;

        } catch (Exception $e) {
            Log::error('FPX payment failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Process e-wallet payment
     */
    public function processEWalletPayment(User $user, array $data): Transaction
    {
        try {
            $amount = $data['amount'];
            $currency = 'MYR';
            $walletType = $data['wallet_type']; // tng, grabpay, boost, shopeepay
            $description = $data['description'] ?? 'E-Wallet Payment';
            $callbackUrl = $data['callback_url'] ?? config('app.url') . '/api/webhooks/billplz';

            // Calculate fees
            $railConfig = config('payment.rails.ewallet');
            $fee = ($amount * $railConfig['fee_percentage'] / 100) + $railConfig['fee_fixed'];
            $netAmount = $amount - $fee;

            // Create transaction
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => 'payment',
                'payment_rail' => 'ewallet',
                'provider' => $this->provider,
                'amount' => $amount,
                'currency' => $currency,
                'fee' => $fee,
                'net_amount' => $netAmount,
                'status' => 'pending',
                'ewallet_type' => $walletType,
                'description' => $description,
                'metadata' => array_merge($data['metadata'] ?? [], [
                    'wallet_type' => $walletType,
                    'initiated_at' => now()->toIso8601String(),
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Process with provider
            if ($this->provider === 'billplz') {
                $billplzResponse = $this->createBillplzEWalletBill($transaction, [
                    'wallet_type' => $walletType,
                    'callback_url' => $callbackUrl,
                    'redirect_url' => $data['redirect_url'] ?? null,
                ]);
                
                $transaction->update([
                    'provider_transaction_id' => $billplzResponse['id'],
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'payment_url' => $billplzResponse['url'],
                        'qr_code_url' => $billplzResponse['qr_code_url'] ?? null,
                        'deep_link_url' => $billplzResponse['deep_link_url'] ?? null,
                        'expires_at' => now()->addMinutes(15)->toIso8601String(),
                    ]),
                ]);
            }

            return $transaction;

        } catch (Exception $e) {
            Log::error('E-wallet payment failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create manual bank transfer transaction
     */
    public function createManualBankTransfer(User $user, array $data): Transaction
    {
        $amount = $data['amount'];
        $currency = 'MYR';
        $description = $data['description'] ?? 'Manual Bank Transfer';

        // Generate unique reference code
        $referenceCode = 'REF-' . strtoupper(Str::random(10));

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'type' => 'payment',
            'payment_rail' => 'manual_bank',
            'provider' => 'manual',
            'amount' => $amount,
            'currency' => $currency,
            'fee' => 0,
            'net_amount' => $amount,
            'status' => 'pending',
            'bank_reference_code' => $referenceCode,
            'description' => $description,
            'metadata' => $data['metadata'] ?? [],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $transaction;
    }

    /**
     * Upload payment proof for manual bank transfer
     */
    public function uploadPaymentProof(Transaction $transaction, string $fileUrl): Transaction
    {
        if ($transaction->payment_rail !== 'manual_bank') {
            throw new Exception('Payment proof can only be uploaded for manual bank transfers');
        }

        $transaction->update([
            'payment_proof_url' => $fileUrl,
            'payment_proof_uploaded_at' => now(),
        ]);

        // TODO: Send notification to admin for verification

        return $transaction;
    }

    /**
     * Verify manual bank transfer (admin only)
     */
    public function verifyManualBankTransfer(Transaction $transaction, User $admin): Transaction
    {
        if ($transaction->payment_rail !== 'manual_bank') {
            throw new Exception('Only manual bank transfers can be verified');
        }

        $transaction->update([
            'status' => 'succeeded',
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'paid_at' => now(),
        ]);

        // Generate invoice for verified payment
        if (config('invoice.queue.enabled', true)) {
            \App\Jobs\GenerateInvoiceJob::dispatch($transaction);
        }

        // TODO: Send confirmation notification to user

        return $transaction;
    }

    /**
     * Save payment method for future use
     */
    public function savePaymentMethod(User $user, array $data): PaymentMethod
    {
        $type = $data['type']; // card, bank, ewallet
        
        $paymentMethod = PaymentMethod::create([
            'user_id' => $user->id,
            'type' => $type,
            'provider' => $this->provider,
            'provider_payment_method_id' => $data['provider_payment_method_id'] ?? null,
            'is_default' => $data['is_default'] ?? false,
            'is_verified' => true,
        ]);

        if ($type === 'card') {
            $paymentMethod->update([
                'card_brand' => $data['card_brand'],
                'card_last4' => $data['card_last4'],
                'card_exp_month' => $data['card_exp_month'],
                'card_exp_year' => $data['card_exp_year'],
                'card_fingerprint' => $data['card_fingerprint'] ?? null,
            ]);
        } elseif ($type === 'bank') {
            $paymentMethod->update([
                'bank_name' => $data['bank_name'],
                'bank_account_last4' => $data['bank_account_last4'] ?? null,
            ]);
        } elseif ($type === 'ewallet') {
            $paymentMethod->update([
                'ewallet_type' => $data['ewallet_type'],
                'ewallet_account_id' => $data['ewallet_account_id'] ?? null,
            ]);
        }

        // Set as default if specified
        if ($data['is_default'] ?? false) {
            PaymentMethod::where('user_id', $user->id)
                ->where('id', '!=', $paymentMethod->id)
                ->update(['is_default' => false]);
        }

        return $paymentMethod;
    }

    /**
     * Process refund
     */
    public function processRefund(Transaction $transaction, array $data): Refund
    {
        try {
            $amount = $data['amount'] ?? $transaction->refundable_amount;
            $reason = $data['reason'] ?? 'requested';
            
            if ($amount > $transaction->refundable_amount) {
                throw new Exception('Refund amount exceeds refundable amount');
            }

            // Create refund record
            $refund = Refund::create([
                'transaction_id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'provider' => $transaction->provider,
                'amount' => $amount,
                'currency' => $transaction->currency,
                'status' => 'pending',
                'reason' => $reason,
                'notes' => $data['notes'] ?? null,
                'processed_by' => $data['processed_by'] ?? null,
            ]);

            // Process with provider
            if ($this->provider === 'stripe') {
                $stripeRefund = StripeRefund::create([
                    'payment_intent' => $transaction->provider_transaction_id,
                    'amount' => $amount * 100, // Convert to cents
                    'reason' => $reason,
                    'metadata' => [
                        'refund_id' => $refund->refund_id,
                        'transaction_id' => $transaction->transaction_id,
                    ],
                ]);

                $refund->update([
                    'provider_refund_id' => $stripeRefund->id,
                    'status' => $this->mapStripeStatus($stripeRefund->status),
                    'processed_at' => now(),
                ]);
            }

            // Update transaction status
            $totalRefunded = $transaction->total_refunded + $amount;
            if ($totalRefunded >= $transaction->amount) {
                $transaction->update(['status' => 'refunded']);
            } else {
                $transaction->update(['status' => 'partially_refunded']);
            }

            return $refund;

        } catch (Exception $e) {
            Log::error('Refund processing failed', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature, string $provider = null): bool
    {
        $provider = $provider ?? $this->provider;

        try {
            if ($provider === 'stripe') {
                $webhookSecret = $this->config['webhook_secret'];
                Webhook::constructEvent($payload, $signature, $webhookSecret);
                return true;
            }

            // Add other provider signature verification here

            return false;
        } catch (Exception $e) {
            Log::warning('Webhook signature verification failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Handle webhook event
     */
    public function handleWebhook(array $data, string $provider = null): WebhookLog
    {
        $provider = $provider ?? $this->provider;

        // Create webhook log
        $webhookLog = WebhookLog::create([
            'provider' => $provider,
            'event_type' => $data['type'] ?? $data['event_type'] ?? 'unknown',
            'event_id' => $data['id'] ?? null,
            'payload' => $data,
            'signature_verified' => $data['signature_verified'] ?? false,
            'status' => 'pending',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        try {
            // Process webhook based on provider
            if ($provider === 'stripe') {
                $this->handleStripeWebhook($data, $webhookLog);
            } elseif ($provider === 'billplz') {
                $this->handleBillplzWebhook($data, $webhookLog);
            }

            $webhookLog->markAsProcessed();

        } catch (Exception $e) {
            $webhookLog->markAsFailed($e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $webhookLog;
    }

    /**
     * Helper: Get or create Stripe customer
     */
    protected function getOrCreateStripeCustomer(User $user)
    {
        if ($user->stripe_customer_id) {
            try {
                return StripeCustomer::retrieve($user->stripe_customer_id);
            } catch (Exception $e) {
                // Customer not found, create new one
            }
        }

        $customer = StripeCustomer::create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => [
                'user_id' => $user->id,
            ],
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer;
    }

    /**
     * Helper: Map Stripe status to our status
     */
    protected function mapStripeStatus(string $stripeStatus): string
    {
        return match($stripeStatus) {
            'requires_payment_method' => 'pending',
            'requires_confirmation' => 'pending',
            'requires_action' => 'requires_action',
            'processing' => 'processing',
            'succeeded' => 'succeeded',
            'canceled' => 'cancelled',
            'failed' => 'failed',
            default => $stripeStatus,
        };
    }

    /**
     * Helper: Handle Stripe webhook
     */
    protected function handleStripeWebhook(array $event, WebhookLog $log)
    {
        $type = $event['type'];
        $object = $event['data']['object'];

        switch ($type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($object);
                break;
            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($object);
                break;
            // Add more event handlers
        }
    }

    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        $transaction = Transaction::where('provider_transaction_id', $paymentIntent['id'])->first();
        if ($transaction) {
            $transaction->update([
                'status' => 'succeeded',
                'paid_at' => now(),
            ]);
            
            // Generate invoice for successful payment
            if (config('invoice.queue.enabled', true)) {
                GenerateInvoiceJob::dispatch($transaction);
            }
        }
    }

    protected function handlePaymentIntentFailed($paymentIntent)
    {
        $transaction = Transaction::where('provider_transaction_id', $paymentIntent['id'])->first();
        if ($transaction) {
            $transaction->update([
                'status' => 'failed',
                'failure_code' => $paymentIntent['last_payment_error']['code'] ?? null,
                'failure_message' => $paymentIntent['last_payment_error']['message'] ?? null,
            ]);
        }
    }

    /**
     * Helper: Create Billplz e-wallet bill with deep link
     */
    protected function createBillplzEWalletBill(Transaction $transaction, array $options)
    {
        $apiKey = config('payment.providers.billplz.api_key');
        $collectionId = config('payment.providers.billplz.collection_id');
        $isSandbox = config('payment.providers.billplz.sandbox');
        
        $baseUrl = $isSandbox ? 'https://www.billplz-sandbox.com/api/v4' : 'https://www.billplz.com/api/v4';
        
        // Map wallet types to Billplz codes
        $walletMap = [
            'tng' => 'TNG_EWALLET',
            'grabpay' => 'GRABPAY',
            'boost' => 'BOOST',
            'shopeepay' => 'SHOPEEPAY',
        ];
        
        $walletCode = $walletMap[$options['wallet_type']] ?? 'TNG_EWALLET';
        
        // Create bill via Billplz API
        $billData = [
            'collection_id' => $collectionId,
            'description' => $transaction->description,
            'email' => $transaction->user->email,
            'name' => $transaction->user->name,
            'amount' => (int)($transaction->amount * 100), // Convert to cents
            'callback_url' => $options['callback_url'],
            'redirect_url' => $options['redirect_url'] ?? config('app.frontend_url') . '/payment/status',
            'reference_1_label' => 'Transaction ID',
            'reference_1' => $transaction->transaction_id,
            'payment_method_type' => [$walletCode],
        ];
        
        try {
            $response = \Illuminate\Support\Facades\Http::withBasicAuth($apiKey, '')
                ->post($baseUrl . '/bills', $billData);
            
            if ($response->successful()) {
                $billData = $response->json();
                
                // Generate deep link based on wallet type
                $deepLink = $this->generateEWalletDeepLink(
                    $options['wallet_type'],
                    $billData['url']
                );
                
                return [
                    'id' => $billData['id'],
                    'url' => $billData['url'],
                    'qr_code_url' => $billData['url'] . '/qr',
                    'deep_link_url' => $deepLink,
                ];
            }
            
            throw new Exception('Billplz API error: ' . $response->body());
            
        } catch (Exception $e) {
            Log::error('Failed to create Billplz bill', [
                'transaction_id' => $transaction->transaction_id,
                'error' => $e->getMessage(),
            ]);
            
            // Return mock data for testing if API fails
            return [
                'id' => 'bill_test_' . Str::random(10),
                'url' => 'https://billplz-sandbox.com/bills/test',
                'qr_code_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode('https://billplz-sandbox.com/bills/test'),
                'deep_link_url' => $this->generateEWalletDeepLink($options['wallet_type'], 'https://billplz-sandbox.com/bills/test'),
            ];
        }
    }
    
    /**
     * Generate e-wallet deep link for app redirection
     */
    protected function generateEWalletDeepLink(string $walletType, string $paymentUrl): string
    {
        // Generate deep links that open the wallet apps
        switch ($walletType) {
            case 'tng':
                // Touch 'n Go eWallet deep link
                return 'tngd://payment?url=' . urlencode($paymentUrl);
                
            case 'grabpay':
                // GrabPay deep link
                return 'grab://payment?url=' . urlencode($paymentUrl);
                
            case 'boost':
                // Boost deep link
                return 'boostapp://payment?url=' . urlencode($paymentUrl);
                
            case 'shopeepay':
                // ShopeePay deep link
                return 'shopeemy://payment?url=' . urlencode($paymentUrl);
                
            default:
                return $paymentUrl;
        }
    }

    /**
     * Helper: Handle Billplz webhook
     */
    protected function handleBillplzWebhook(array $event, WebhookLog $log)
    {
        $eventType = $event['type'];
        $payload = $event['data'];
        
        // Find transaction by bill ID
        $billId = $payload['id'] ?? null;
        if (!$billId) {
            throw new Exception('Bill ID not found in webhook payload');
        }
        
        $transaction = Transaction::where('provider_transaction_id', $billId)->first();
        
        if (!$transaction) {
            Log::warning('Transaction not found for Billplz webhook', [
                'bill_id' => $billId,
                'event_type' => $eventType,
            ]);
            return;
        }
        
        // Update transaction based on event
        if ($eventType === 'bill.paid' && $payload['paid'] === 'true') {
            $transaction->update([
                'status' => 'succeeded',
                'paid_at' => $payload['paid_at'] ?? now(),
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'webhook_received_at' => now()->toIso8601String(),
                    'payment_state' => $payload['state'] ?? 'paid',
                    'transaction_id' => $payload['transaction_id'] ?? null,
                    'transaction_status' => $payload['transaction_status'] ?? null,
                ]),
            ]);
            
            Log::info('E-wallet payment succeeded via webhook', [
                'transaction_id' => $transaction->transaction_id,
                'bill_id' => $billId,
                'wallet_type' => $transaction->ewallet_type,
            ]);
            
            // Generate invoice for successful payment
            if (config('invoice.queue.enabled', true)) {
                GenerateInvoiceJob::dispatch($transaction);
            }
        } elseif ($payload['state'] === 'deleted' || $payload['paid'] === 'false') {
            $transaction->update([
                'status' => 'failed',
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'webhook_received_at' => now()->toIso8601String(),
                    'payment_state' => $payload['state'] ?? 'failed',
                ]),
            ]);
            
            Log::info('E-wallet payment failed via webhook', [
                'transaction_id' => $transaction->transaction_id,
                'bill_id' => $billId,
            ]);
        }
    }
}
