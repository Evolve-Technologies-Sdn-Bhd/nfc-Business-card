<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Fiuu Webhook Controller
 * 
 * Handles all incoming webhooks from Fiuu payment gateway:
 * - Return URL: Customer redirect after payment
 * - Notification URL: Primary server-to-server webhook
 * - Callback URL: Delayed payment notifications (cash channels)
 */
class FiuuWebhookController extends Controller
{
    protected PaymentService $paymentService;
    protected \App\Services\InvoiceService $invoiceService;

    public function __construct(PaymentService $paymentService, \App\Services\InvoiceService $invoiceService)
    {
        $this->paymentService = $paymentService;
        $this->invoiceService = $invoiceService;
    }

    /**
     * Handle Return URL - Customer redirect after payment completion
     * This displays payment result to the customer
     */
    public function handleReturn(Request $request)
    {
        Log::info('Fiuu Return URL received', $request->all());

        $orderId = $request->input('orderid');
        $status = $request->input('status');
        $amount = $request->input('amount');
        $tranId = $request->input('tranID');
        $skey = $request->input('skey');
        $currency = $request->input('currency', 'MYR');
        $channel = $request->input('channel');
        $errorDesc = $request->input('error_desc');

        // Verify signature
        $isValid = false;
        if ($amount && $orderId && $skey) {
            $isValid = $this->paymentService->verifySkey((float) $amount, $orderId, $skey, $currency);
        }

        // Find transaction
        $transaction = Transaction::where('provider_transaction_id', $orderId)->first();

        // Map status
        $statusText = match ($status) {
            '00' => 'Success',
            '11' => 'Failed',
            '22' => 'Pending',
            '33' => 'Processing',
            default => 'Unknown',
        };

        // If payment successful, ensure invoice is generated
        if ($status === '00' && $transaction) {
            try {
                $this->invoiceService->generateInvoiceFromTransaction($transaction);
            } catch (Exception $e) {
                Log::error('Failed to generate invoice in return handler', [
                    'order_id' => $orderId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $statusClass = match ($status) {
            '00' => 'success',
            '11' => 'failed',
            '22' => 'pending',
            '33' => 'processing',
            default => 'unknown',
        };

        // Redirect to frontend with status
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
        $redirectParams = http_build_query([
            'order_id' => $orderId,
            'status' => $statusText,
            'status_code' => $status,
            'amount' => $amount,
            'currency' => $currency,
            'tran_id' => $tranId,
        ]);

        return redirect("{$frontendUrl}/payment/result?{$redirectParams}");
    }

    /**
     * Handle Notification URL - Primary server-to-server webhook
     * This performs ALL database updates for payment status
     * 
     * CRITICAL: Must return "CBTOKEN:MPSTATOK" to acknowledge receipt
     */
    public function handleNotification(Request $request)
    {
        Log::info('Fiuu Notification URL received', $request->all());

        $orderId = $request->input('orderid');
        $status = $request->input('status');
        $amount = $request->input('amount');
        $tranId = $request->input('tranID');
        $skey = $request->input('skey');
        $appcode = $request->input('appcode');
        $channel = $request->input('channel');
        $paydate = $request->input('paydate');
        $errorCode = $request->input('error_code');
        $errorDesc = $request->input('error_desc');
        $nbcb = $request->input('nbcb');

        // Validate required parameters
        if (empty($orderId) || empty($status) || empty($skey) || empty($amount)) {
            Log::error('Fiuu notification missing required parameters', [
                'order_id' => $orderId,
                'has_status' => !empty($status),
                'has_skey' => !empty($skey),
                'has_amount' => !empty($amount),
            ]);
            return response('CBTOKEN:MPSUNK', 400);
        }

        // Verify signature
        if (!$this->paymentService->verifyNotificationSignature($request->all())) {
            Log::error('Fiuu notification signature verification failed', [
                'order_id' => $orderId,
            ]);
            return response('CBTOKEN:MPSUNK', 403);
        }

        try {
            // Check for duplicate webhook
            $transaction = Transaction::where('provider_transaction_id', $orderId)->first();

            if (!$transaction) {
                Log::error('Transaction not found for Fiuu notification', [
                    'order_id' => $orderId,
                ]);
                return response('CBTOKEN:MPSUNK', 404);
            }

            // Check if already processed with same status
            $existingStatus = $transaction->metadata['fiuu_status_code'] ?? null;
            if ($existingStatus === $status && $transaction->metadata['fiuu_tran_id'] ?? null === $tranId) {
                Log::warning('Duplicate Fiuu notification detected', [
                    'order_id' => $orderId,
                    'status' => $status,
                ]);
                return response('CBTOKEN:MPSTATOK', 200);
            }

            // Map Fiuu status to internal status
            $mappedStatus = $this->paymentService->mapFiuuStatus($status);

            // Update transaction
            $transaction->update([
                'status' => $mappedStatus,
                'paid_at' => $mappedStatus === 'succeeded' ? now() : null,
                'failure_code' => $errorCode,
                'failure_message' => $errorDesc,
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'fiuu_tran_id' => $tranId,
                    'fiuu_appcode' => $appcode,
                    'fiuu_channel' => $channel,
                    'fiuu_paydate' => $paydate,
                    'fiuu_status_code' => $status,
                    'fiuu_nbcb' => $nbcb,
                    'notification_received_at' => now()->toIso8601String(),
                ]),
            ]);

            // If payment succeeded, mark user as active and generate invoice
            if ($mappedStatus === 'succeeded') {
                if ($transaction->user) {
                    $transaction->user->update(['is_new_user' => false]);
                }

                // Generate Invoice
                try {
                    $this->invoiceService->generateInvoiceFromTransaction($transaction);
                } catch (Exception $e) {
                    Log::error('Failed to generate invoice for transaction', [
                        'transaction_id' => $transaction->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Fiuu notification processed successfully', [
                'order_id' => $orderId,
                'status' => $mappedStatus,
                'tran_id' => $tranId,
            ]);

            // Return acknowledgment
            return response('CBTOKEN:MPSTATOK', 200);

        } catch (Exception $e) {
            Log::error('Fiuu notification processing failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            return response('CBTOKEN:MPSUNK', 500);
        }
    }

    /**
     * Handle Callback URL - Delayed payment notifications
     * Used for cash payment channels (7-Eleven, etc.)
     * 
     * CRITICAL: Must return "CBTOKEN:MPSTATOK" to acknowledge receipt
     */
    public function handleCallback(Request $request)
    {
        Log::info('Fiuu Callback URL received', $request->all());

        $orderId = $request->input('orderid');
        $status = $request->input('status');
        $amount = $request->input('amount');
        $tranId = $request->input('tranID');
        $skey = $request->input('skey');
        $nbcb = $request->input('nbcb');

        // Validate required parameters
        if (empty($orderId) || empty($status) || empty($skey) || empty($amount)) {
            return response('CBTOKEN:MPSUNK', 400);
        }

        // Verify signature
        if (!$this->paymentService->verifyNotificationSignature($request->all())) {
            Log::error('Fiuu callback signature verification failed', [
                'order_id' => $orderId,
            ]);
            return response('CBTOKEN:MPSUNK', 403);
        }

        try {
            $transaction = Transaction::where('provider_transaction_id', $orderId)->first();

            if (!$transaction) {
                Log::error('Transaction not found for Fiuu callback', [
                    'order_id' => $orderId,
                ]);
                return response('CBTOKEN:MPSUNK', 404);
            }

            // Map status and update
            $mappedStatus = $this->paymentService->mapFiuuStatus($status);

            $transaction->update([
                'status' => $mappedStatus,
                'paid_at' => $mappedStatus === 'succeeded' ? now() : null,
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'fiuu_tran_id' => $tranId,
                    'fiuu_status_code' => $status,
                    'callback_received_at' => now()->toIso8601String(),
                ]),
            ]);

            // If payment succeeded, mark user as active and generate invoice
            if ($mappedStatus === 'succeeded') {
                if ($transaction->user) {
                    $transaction->user->update(['is_new_user' => false]);
                }

                // Generate Invoice
                try {
                    $this->invoiceService->generateInvoiceFromTransaction($transaction);
                } catch (Exception $e) {
                    Log::error('Failed to generate invoice for transaction', [
                        'transaction_id' => $transaction->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Fiuu callback processed successfully', [
                'order_id' => $orderId,
                'status' => $mappedStatus,
            ]);

            // Return acknowledgment
            if ($nbcb == 1) {
                return response('CBTOKEN:MPSTATOK', 200);
            }

            return response('OK', 200);

        } catch (Exception $e) {
            Log::error('Fiuu callback processing failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            return response('CBTOKEN:MPSUNK', 500);
        }
    }

    /**
     * IPN Acknowledgment - Send acknowledgment back to Fiuu
     * Called after processing return URL to confirm receipt
     */
    public function sendIPNAcknowledgment(Request $request)
    {
        $postData = $request->all();
        $postData['treq'] = '1';

        try {
            $response = \Illuminate\Support\Facades\Http::asForm()
                ->post('https://pay.fiuu.com/RMS/API/chkstat/returnipn.php', $postData);

            Log::info('IPN acknowledgment sent', [
                'order_id' => $postData['orderid'] ?? null,
                'response' => $response->body(),
            ]);

            return $response->successful();

        } catch (Exception $e) {
            Log::error('IPN acknowledgment failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
