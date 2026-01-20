<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\Subscription;
use App\Models\Refund;
use App\Models\WebhookLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;

/**
 * Fiuu Payment Service
 * 
 * Handles all payment processing through Fiuu (formerly MOLPay/Razer Merchant Services)
 * Supports: Credit/Debit Cards, FPX, E-wallets (TNG, GrabPay, Boost, ShopeePay), DuitNow
 */
class PaymentService
{
    protected string $provider = 'fiuu';
    protected array $config;

    // Fiuu Status Codes
    const STATUS_SUCCESS = '00';
    const STATUS_FAILED = '11';
    const STATUS_PENDING = '22';
    const STATUS_PROCESSING = '33';

    public function __construct(?string $provider = null)
    {
        $this->provider = $provider ?? config('payment.default_provider', 'fiuu');
        $this->config = config("payment.providers.{$this->provider}", []);
    }

    /**
     * Generate vcode for payment request
     * Formula: MD5(amount + merchant_id + order_id + verify_key)
     */
    public function generateVcode(float $amount, string $orderId, string $currency = 'MYR'): string
    {
        $formattedAmount = number_format($amount, 2, '.', '');
        $merchantId = $this->config['merchant_id'];
        $verifyKey = $this->config['verify_key'];

        // Formula: MD5(amount + merchant_id + order_id + verify_key + currency)
        $string = $formattedAmount . $merchantId . $orderId . $verifyKey . $currency;

        return md5($string);
    }

    /**
     * Verify skey from Fiuu webhook response
     * Formula: MD5(amount + merchant_id + order_id + verify_key)
     */
    public function verifySkey(float $amount, string $orderId, string $receivedSkey, string $currency = 'MYR'): bool
    {
        $formattedAmount = number_format($amount, 2, '.', '');
        $merchantId = $this->config['merchant_id'];
        $verifyKey = $this->config['verify_key'];

        // Formula: MD5(amount + merchant_id + order_id + verify_key + currency)
        $string = $formattedAmount . $merchantId . $orderId . $verifyKey . $currency;
        $expectedSkey = md5($string);

        $isValid = hash_equals($expectedSkey, $receivedSkey);

        if (!$isValid) {
            Log::warning('Fiuu skey verification failed', [
                'order_id' => $orderId,
                'expected' => $expectedSkey,
                'received' => $receivedSkey,
            ]);
        }

        return $isValid;
    }

    /**
     * Map Fiuu status code to internal status
     */
    public function mapFiuuStatus(string $fiuuStatus): string
    {
        return match ($fiuuStatus) {
            self::STATUS_SUCCESS => 'succeeded',
            self::STATUS_FAILED => 'failed',
            self::STATUS_PENDING => 'pending',
            self::STATUS_PROCESSING => 'processing',
            default => 'failed',
        };
    }

    /**
     * Get Fiuu payment URL based on environment
     * Note: Fiuu migrated from sandbox.merchant.razer.com to sandbox-payment.fiuu.com
     */
    protected function getPaymentUrl(): string
    {
        // New Fiuu domain (rebranded from Razer Merchant Services)
        if ($this->config['sandbox'] ?? true) {
            return 'https://sandbox-payment.fiuu.com/RMS/pay/';
        }
        return 'https://pay.fiuu.com/RMS/pay/';
    }

    /**
     * Process card payment via Fiuu
     */
    public function processCardPayment(User $user, array $data): Transaction
    {
        return $this->initiateFiuuPayment($user, $data, 'credit');
    }

    /**
     * Process FPX bank payment via Fiuu 
     */
    public function processFPXPayment(User $user, array $data): Transaction
    {
        $channel = $data['bank_code'] ?? $data['channel'] ?? 'fpx';
        return $this->initiateFiuuPayment($user, $data, $channel);
    }

    /**
     * Process e-wallet payment via Fiuu
     */
    public function processEWalletPayment(User $user, array $data): Transaction
    {
        $walletType = $data['wallet_type'] ?? 'tng';

        // Map wallet types to Fiuu channel codes
        $walletMap = [
            'tng' => 'TNG-EWALLET',
            'grabpay' => 'GRABPAY',
            'boost' => 'BOOST',
            'shopeepay' => 'SHOPEEPAY',
        ];

        $channel = $walletMap[$walletType] ?? $walletType;

        return $this->initiateFiuuPayment($user, $data, $channel);
    }

    /**
     * Verify Notification Signature (Server-to-Server)
     * Formula:
     * key0 = md5(tranID + orderid + status + domain + amount + currency)
     * key1 = md5(paydate + domain + key0 + appcode + secret_key)
     */
    public function verifyNotificationSignature(array $data): bool
    {
        $tranID = $data['tranID'] ?? '';
        $orderid = $data['orderid'] ?? '';
        $status = $data['status'] ?? '';
        $domain = $data['domain'] ?? '';
        $amount = $data['amount'] ?? '';
        $currency = $data['currency'] ?? 'MYR';
        $paydate = $data['paydate'] ?? '';
        $appcode = $data['appcode'] ?? '';
        $skey = $data['skey'] ?? '';

        $secretKey = $this->config['secret_key'] ?? '';

        $key0 = md5($tranID . $orderid . $status . $domain . $amount . $currency);
        $key1 = md5($paydate . $domain . $key0 . $appcode . $secretKey);

        return $skey === $key1;
    }

    /**
     * Initiate Fiuu payment and create transaction
     */
    public function initiateFiuuPayment(User $user, array $data, ?string $channel = null): Transaction
    {
        try {
            $amount = $data['amount'];
            $currency = $data['currency'] ?? config('payment.currency', 'MYR');
            $description = $data['description'] ?? 'Payment';
            $metadata = $data['metadata'] ?? [];

            // Generate unique order ID
            $orderId = 'NFC-' . strtoupper(Str::random(16));

            // Calculate fees based on payment rail
            $railType = $this->determineRailType($channel);
            $railConfig = config("payment.rails.{$railType}", []);
            $feePercentage = $railConfig['fee_percentage'] ?? 2.0;
            $feeFixed = $railConfig['fee_fixed'] ?? 0;
            $fee = ($amount * $feePercentage / 100) + $feeFixed;
            $netAmount = $amount - $fee;

            // Generate vcode
            $vcode = $this->generateVcode($amount, $orderId, $currency);

            // Construct Payment URL with parameters
            $paymentUrl = $this->getPaymentUrl() . $this->config['merchant_id'] . '/?' . http_build_query([
                'amount' => number_format($amount, 2, '.', ''),
                'orderid' => $orderId,
                'bill_name' => $user->name ?? 'Customer',
                'bill_email' => $user->email,
                'bill_mobile' => $data['bill_mobile'] ?? $user->phone ?? '0123456789',
                'bill_desc' => $description,
                'currency' => $currency,
                'vcode' => $vcode,
                'channel' => $channel, // Optional
                'returnurl' => $this->config['return_url'],
                'callbackurl' => $this->config['notification_url'],
            ]);

            // Create transaction record
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'type' => $data['type'] ?? 'payment',
                'payment_rail' => $railType,
                'provider' => 'fiuu',
                'provider_transaction_id' => $orderId,
                'amount' => $amount,
                'currency' => $currency,
                'fee' => $fee,
                'net_amount' => $netAmount,
                'status' => 'pending',
                'description' => $description,
                'bank_name' => $data['bank_code'] ?? null,
                'ewallet_type' => $data['wallet_type'] ?? null,
                'metadata' => array_merge($metadata, [
                    'fiuu_channel' => $channel,
                    'vcode' => $vcode,
                    'payment_url' => $this->getPaymentUrl(),
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Build payment form data
            $paymentData = [
                'MerchantID' => $this->config['merchant_id'],
                'orderid' => $orderId,
                'amount' => number_format($amount, 2, '.', ''),
                'bill_name' => $user->name ?? 'Customer',
                'bill_email' => $user->email,
                'bill_mobile' => $user->phone ?? '',
                'bill_desc' => $description,
                'currency' => $currency,
                'vcode' => $vcode,
                'returnurl' => $this->config['return_url'],
                'callbackurl' => $this->config['notification_url'],
            ];

            if ($channel) {
                $paymentData['channel'] = $channel;
            }

            // Store payment data in transaction metadata
            $transaction->update([
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'payment_form_data' => $paymentData,
                    'payment_url' => $paymentUrl, // Add this for frontend
                    'redirect_url' => $paymentUrl, // Unified key
                ]),
            ]);

            Log::info('Fiuu payment initiated', [
                'order_id' => $orderId,
                'user_id' => $user->id,
                'amount' => $amount,
                'channel' => $channel,
            ]);

            return $transaction;

        } catch (Exception $e) {
            Log::error('Fiuu payment initiation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Determine payment rail type from channel
     */
    protected function determineRailType(?string $channel): string
    {
        if (!$channel) {
            return 'card';
        }

        $channel = strtolower($channel);

        if (in_array($channel, ['credit', 'mastercard', 'visa', 'amex'])) {
            return 'card';
        }

        if (str_starts_with($channel, 'fpx') || in_array($channel, ['maybank2u', 'cimb', 'rhb', 'pbb'])) {
            return 'fpx';
        }

        if (in_array($channel, ['tng', 'tng-ewallet', 'grabpay', 'boost', 'shopeepay'])) {
            return 'ewallet';
        }

        if (str_starts_with($channel, 'duitnow')) {
            return 'duitnow';
        }

        return 'card';
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
     * Process refund via Fiuu
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

            // For Fiuu refunds, we need to call their API
            if ($transaction->provider === 'fiuu') {
                $refundResult = $this->processFiuuRefund($transaction, $amount, $reason);

                $refund->update([
                    'provider_refund_id' => $refundResult['refund_id'] ?? null,
                    'status' => $refundResult['success'] ? 'succeeded' : 'failed',
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
     * Process refund through Fiuu API
     */
    protected function processFiuuRefund(Transaction $transaction, float $amount, string $reason): array
    {
        try {
            $refundUrl = $this->config['sandbox'] ?? true
                ? 'https://sandbox.merchant.razer.com/RMS/API/refundAPI/index.php'
                : 'https://api.merchant.razer.com/RMS/API/refundAPI/index.php';

            $response = Http::asForm()->post($refundUrl, [
                'RefundType' => 'P', // Partial refund
                'MerchantID' => $this->config['merchant_id'],
                'RefID' => $transaction->provider_transaction_id,
                'TxnID' => $transaction->metadata['fiuu_tran_id'] ?? '',
                'Amount' => number_format($amount, 2, '.', ''),
                'signature' => $this->generateRefundSignature($transaction, $amount),
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'refund_id' => $response->json('RefundID'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->body(),
            ];

        } catch (Exception $e) {
            Log::error('Fiuu refund API call failed', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate refund signature for Fiuu
     */
    protected function generateRefundSignature(Transaction $transaction, float $amount): string
    {
        $string = $transaction->provider_transaction_id
            . number_format($amount, 2, '.', '')
            . $this->config['merchant_id']
            . $this->config['secret_key'];

        return md5($string);
    }

    /**
     * Handle Fiuu webhook event
     */
    public function handleWebhook(array $data, ?string $provider = null): WebhookLog
    {
        $provider = $provider ?? 'fiuu';

        // Create webhook log
        $webhookLog = WebhookLog::create([
            'provider' => $provider,
            'event_type' => $data['status'] ?? 'unknown',
            'event_id' => $data['tranID'] ?? null,
            'payload' => $data,
            'signature_verified' => $data['signature_verified'] ?? false,
            'status' => 'pending',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        try {
            $this->processFiuuWebhook($data, $webhookLog);
            $webhookLog->markAsProcessed();
        } catch (Exception $e) {
            $webhookLog->markAsFailed($e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $webhookLog;
    }

    /**
     * Process Fiuu webhook
     */
    protected function processFiuuWebhook(array $data, WebhookLog $log): void
    {
        $orderId = $data['orderid'] ?? null;
        $status = $data['status'] ?? null;
        $tranId = $data['tranID'] ?? null;
        $amount = $data['amount'] ?? 0;

        if (!$orderId || !$status) {
            throw new Exception('Missing required webhook fields: orderid or status');
        }

        // Find transaction by order ID
        $transaction = Transaction::where('provider_transaction_id', $orderId)->first();

        if (!$transaction) {
            Log::warning('Transaction not found for Fiuu webhook', [
                'order_id' => $orderId,
                'status' => $status,
            ]);
            return;
        }

        // Map status and update transaction
        $mappedStatus = $this->mapFiuuStatus($status);

        $transaction->update([
            'status' => $mappedStatus,
            'paid_at' => $mappedStatus === 'succeeded' ? now() : null,
            'metadata' => array_merge($transaction->metadata ?? [], [
                'fiuu_tran_id' => $tranId,
                'fiuu_appcode' => $data['appcode'] ?? null,
                'fiuu_channel' => $data['channel'] ?? null,
                'fiuu_paydate' => $data['paydate'] ?? null,
                'fiuu_status_code' => $status,
                'webhook_received_at' => now()->toIso8601String(),
            ]),
        ]);

        Log::info('Fiuu webhook processed', [
            'order_id' => $orderId,
            'status' => $mappedStatus,
            'tran_id' => $tranId,
        ]);

        // If payment succeeded, mark user as not new
        if ($mappedStatus === 'succeeded' && $transaction->user) {
            $transaction->user->update(['is_new_user' => false]);
        }
    }

    /**
     * Query transaction status from Fiuu API
     */
    public function queryTransactionStatus(string $orderId): array
    {
        try {
            $queryUrl = $this->config['sandbox'] ?? true
                ? 'https://sandbox.merchant.razer.com/RMS/API/chkstat/q_by_oid.php'
                : 'https://api.merchant.razer.com/RMS/API/chkstat/q_by_oid.php';

            $response = Http::asForm()->post($queryUrl, [
                'MerchantID' => $this->config['merchant_id'],
                'orderid' => $orderId,
                'vcode' => $this->generateVcode(0, $orderId, 'MYR'), // Amount doesn't matter for query, assuming MYR
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->body(),
            ];

        } catch (Exception $e) {
            Log::error('Fiuu query API call failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get available payment channels
     */
    public function getAvailableChannels(): array
    {
        return $this->config['payment_channels'] ?? [];
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature, ?string $provider = null): bool
    {
        // For Fiuu, we verify using skey
        $data = json_decode($payload, true);

        if (!$data || !isset($data['amount']) || !isset($data['orderid']) || !isset($data['skey'])) {
            return false;
        }

        $currency = $data['currency'] ?? 'MYR';
        return $this->verifySkey((float) $data['amount'], $data['orderid'], $data['skey'], $currency);
    }
}
