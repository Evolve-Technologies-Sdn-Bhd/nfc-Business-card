<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class WebhookController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        // No auth middleware - webhooks come from external providers
    }

    /**
     * Handle Stripe webhook
     */
    public function handleStripe(Request $request)
    {
        try {
            $payload = $request->getContent();
            $signature = $request->header('Stripe-Signature');

            // Verify signature
            $paymentService = new PaymentService('stripe');
            $signatureVerified = $paymentService->verifyWebhookSignature(
                $payload,
                $signature,
                'stripe'
            );

            if (!$signatureVerified && config('payment.webhooks.verify_signatures')) {
                Log::warning('Stripe webhook signature verification failed');
                return response()->json(['error' => 'Invalid signature'], 400);
            }

            // Parse event
            $event = json_decode($payload, true);
            $event['signature_verified'] = $signatureVerified;

            // Handle webhook
            $webhookLog = $paymentService->handleWebhook($event, 'stripe');

            Log::info('Stripe webhook received', [
                'event_type' => $event['type'],
                'event_id' => $event['id'],
                'webhook_log_id' => $webhookLog->id,
            ]);

            return response()->json(['received' => true]);

        } catch (Exception $e) {
            Log::error('Stripe webhook handling failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle Billplz webhook
     */
    public function handleBillplz(Request $request)
    {
        try {
            $payload = $request->all();
            
            // Verify X-Signature for Billplz
            $xSignature = $request->header('X-Signature');
            $billplzSignatureKey = config('payment.providers.billplz.x_signature_key');
            
            $signatureVerified = false;
            if ($xSignature && $billplzSignatureKey) {
                // Billplz signature verification
                $data = [
                    'amount' => $payload['amount'] ?? '',
                    'collection_id' => $payload['collection_id'] ?? '',
                    'id' => $payload['id'] ?? '',
                    'paid' => $payload['paid'] ?? '',
                    'paid_at' => $payload['paid_at'] ?? '',
                    'state' => $payload['state'] ?? '',
                ];
                
                $generatedSignature = hash_hmac('sha256', http_build_query($data), $billplzSignatureKey);
                $signatureVerified = hash_equals($generatedSignature, $xSignature);
            }

            if (!$signatureVerified && config('payment.webhooks.verify_signatures')) {
                Log::warning('Billplz webhook signature verification failed');
                return response()->json(['error' => 'Invalid signature'], 400);
            }

            // Prepare event data
            $event = [
                'type' => $payload['paid'] === 'true' ? 'bill.paid' : 'bill.pending',
                'id' => $payload['id'] ?? null,
                'data' => $payload,
                'signature_verified' => $signatureVerified,
            ];

            // Handle webhook
            $paymentService = new PaymentService('billplz');
            $webhookLog = $paymentService->handleWebhook($event, 'billplz');

            Log::info('Billplz webhook received', [
                'event_type' => $event['type'],
                'bill_id' => $payload['id'] ?? null,
                'webhook_log_id' => $webhookLog->id,
            ]);

            return response()->json(['received' => true]);

        } catch (Exception $e) {
            Log::error('Billplz webhook handling failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle Senangpay webhook/callback
     */
    public function handleSenangpay(Request $request)
    {
        try {
            $payload = $request->all();
            $secretKey = config('payment.providers.senangpay.secret_key');
            
            // Verify hash
            $signatureVerified = false;
            if (isset($payload['hash']) && $secretKey) {
                $generatedHash = md5($secretKey . $payload['status_id'] . $payload['order_id'] . $payload['transaction_id'] . $payload['msg']);
                $signatureVerified = hash_equals($generatedHash, $payload['hash']);
            }

            if (!$signatureVerified && config('payment.webhooks.verify_signatures')) {
                Log::warning('Senangpay callback verification failed');
                return response()->json(['error' => 'Invalid hash'], 400);
            }

            // Prepare event data
            $event = [
                'type' => $payload['status_id'] == 1 ? 'payment.succeeded' : 'payment.failed',
                'id' => $payload['transaction_id'] ?? null,
                'data' => $payload,
                'signature_verified' => $signatureVerified,
            ];

            // Handle webhook
            $paymentService = new PaymentService('senangpay');
            $webhookLog = $paymentService->handleWebhook($event, 'senangpay');

            Log::info('Senangpay callback received', [
                'event_type' => $event['type'],
                'transaction_id' => $payload['transaction_id'] ?? null,
                'webhook_log_id' => $webhookLog->id,
            ]);

            return response()->json(['received' => true]);

        } catch (Exception $e) {
            Log::error('Senangpay callback handling failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Callback processing failed'], 500);
        }
    }

    /**
     * Handle Xendit webhook
     */
    public function handleXendit(Request $request)
    {
        try {
            $payload = $request->getContent();
            $webhookToken = $request->header('X-Callback-Token');
            $expectedToken = config('payment.providers.xendit.webhook_token');

            // Verify webhook token
            $signatureVerified = $webhookToken === $expectedToken;

            if (!$signatureVerified && config('payment.webhooks.verify_signatures')) {
                Log::warning('Xendit webhook token verification failed');
                return response()->json(['error' => 'Invalid token'], 400);
            }

            // Parse event
            $event = json_decode($payload, true);
            $event['signature_verified'] = $signatureVerified;

            // Handle webhook
            $paymentService = new PaymentService('xendit');
            $webhookLog = $paymentService->handleWebhook($event, 'xendit');

            Log::info('Xendit webhook received', [
                'event_type' => $event['event'] ?? 'unknown',
                'webhook_log_id' => $webhookLog->id,
            ]);

            return response()->json(['received' => true]);

        } catch (Exception $e) {
            Log::error('Xendit webhook handling failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }
}
