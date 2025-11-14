<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Get available payment methods/rails
     */
    public function getPaymentRails()
    {
        try {
            $rails = [];

            // Card payments
            if (config('payment.rails.card.enabled')) {
                $rails['card'] = [
                    'enabled' => true,
                    'name' => 'Credit/Debit Card',
                    'description' => 'Visa, Mastercard, AMEX',
                    'icon' => '💳',
                    'min_amount' => config('payment.rails.card.min_amount'),
                    'max_amount' => config('payment.rails.card.max_amount'),
                    'fee_percentage' => config('payment.rails.card.fee_percentage'),
                    'fee_fixed' => config('payment.rails.card.fee_fixed'),
                ];
            }

            // FPX
            if (config('payment.rails.fpx.enabled')) {
                $rails['fpx'] = [
                    'enabled' => true,
                    'name' => 'FPX Online Banking',
                    'description' => 'Malaysian banks',
                    'icon' => '🏦',
                    'min_amount' => config('payment.rails.fpx.min_amount'),
                    'max_amount' => config('payment.rails.fpx.max_amount'),
                    'fee_percentage' => config('payment.rails.fpx.fee_percentage'),
                    'banks' => config('payment.rails.fpx.banks'),
                ];
            }

            // DuitNow
            if (config('payment.rails.duitnow.enabled')) {
                $rails['duitnow'] = [
                    'enabled' => true,
                    'name' => 'DuitNow Online Banking',
                    'description' => 'Instant bank transfer',
                    'icon' => '💰',
                    'min_amount' => config('payment.rails.duitnow.min_amount'),
                    'max_amount' => config('payment.rails.duitnow.max_amount'),
                ];
            }

            // E-wallets
            if (config('payment.rails.ewallet.enabled')) {
                $wallets = [];
                foreach (config('payment.rails.ewallet.wallets') as $key => $wallet) {
                    $wallets[$key] = [
                        'name' => $wallet['name'],
                        'logo' => $wallet['logo'],
                        'enabled' => true,
                    ];
                }

                $rails['ewallet'] = [
                    'enabled' => true,
                    'name' => 'E-Wallet',
                    'description' => 'Touch n Go, GrabPay, Boost, ShopeePay',
                    'icon' => '📱',
                    'min_amount' => config('payment.rails.ewallet.min_amount'),
                    'max_amount' => config('payment.rails.ewallet.max_amount'),
                    'wallets' => $wallets,
                ];
            }

            // Manual bank transfer
            if (config('payment.rails.manual_bank.enabled')) {
                $rails['manual_bank'] = [
                    'enabled' => true,
                    'name' => 'Manual Bank Transfer',
                    'description' => 'Direct transfer to our bank account',
                    'icon' => '🏛️',
                    'accounts' => config('payment.rails.manual_bank.accounts'),
                    'verification_required' => config('payment.rails.manual_bank.verification_required'),
                ];
            }

            return response()->json([
                'success' => true,
                'payment_rails' => $rails,
            ]);

        } catch (Exception $e) {
            Log::error('Error fetching payment rails', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment methods',
            ], 500);
        }
    }

    /**
     * Initiate a payment
     */
    public function initiatePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'payment_rail' => 'required|in:card,fpx,duitnow,ewallet,manual_bank',
            'description' => 'nullable|string|max:500',
            'metadata' => 'nullable|array',
            
            // Card specific
            'payment_method_id' => 'required_if:payment_rail,card|string',
            
            // FPX specific
            'bank_code' => 'required_if:payment_rail,fpx|string',
            
            // E-wallet specific
            'wallet_type' => 'required_if:payment_rail,ewallet|in:tng,grabpay,boost,shopeepay',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = auth()->user();
            $data = $request->all();

            // Dispatch to appropriate payment method
            $transaction = match($request->payment_rail) {
                'card' => $this->paymentService->processCardPayment($user, $data),
                'fpx' => $this->paymentService->processFPXPayment($user, $data),
                'ewallet' => $this->paymentService->processEWalletPayment($user, $data),
                'manual_bank' => $this->paymentService->createManualBankTransfer($user, $data),
                default => throw new Exception('Unsupported payment rail'),
            };

            // Log transaction initiation
            Log::info('Payment initiated', [
                'transaction_id' => $transaction->transaction_id,
                'user_id' => $user->id,
                'amount' => $transaction->amount,
                'rail' => $request->payment_rail,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully',
                'transaction' => [
                    'id' => $transaction->id,
                    'transaction_id' => $transaction->transaction_id,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'status' => $transaction->status,
                    'payment_rail' => $transaction->payment_rail,
                    'client_secret' => $transaction->client_secret, // For 3DS
                    'requires_action' => $transaction->requiresAction(),
                    'metadata' => $transaction->metadata,
                    'created_at' => $transaction->created_at,
                ],
            ], 201);

        } catch (Exception $e) {
            Log::error('Payment initiation failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Confirm payment after 3DS authentication
     */
    public function confirmPayment(Request $request, $transactionId)
    {
        $validator = Validator::make($request->all(), [
            'payment_intent_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = auth()->user();
            $transaction = Transaction::where('transaction_id', $transactionId)
                ->where('user_id', $user->id)
                ->firstOrFail();

            // Verify the payment intent matches
            if ($transaction->provider_transaction_id !== $request->payment_intent_id) {
                throw new Exception('Payment intent mismatch');
            }

            // For Stripe, the webhook will update the status
            // Here we just return the current status
            $transaction->refresh();

            return response()->json([
                'success' => true,
                'transaction' => [
                    'id' => $transaction->id,
                    'transaction_id' => $transaction->transaction_id,
                    'status' => $transaction->status,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                ],
            ]);

        } catch (Exception $e) {
            Log::error('Payment confirmation failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm payment',
            ], 500);
        }
    }

    /**
     * Get transaction details
     */
    public function getTransaction($transactionId)
    {
        try {
            $user = auth()->user();
            $transaction = Transaction::where('transaction_id', $transactionId)
                ->where('user_id', $user->id)
                ->with(['paymentMethod', 'refunds'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'transaction' => [
                    'id' => $transaction->id,
                    'transaction_id' => $transaction->transaction_id,
                    'type' => $transaction->type,
                    'payment_rail' => $transaction->payment_rail,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'fee' => $transaction->fee,
                    'net_amount' => $transaction->net_amount,
                    'status' => $transaction->status,
                    'description' => $transaction->description,
                    'card_last4' => $transaction->card_last4,
                    'bank_name' => $transaction->bank_name,
                    'ewallet_type' => $transaction->ewallet_type,
                    'bank_reference_code' => $transaction->bank_reference_code,
                    'payment_proof_url' => $transaction->payment_proof_url,
                    'refunds' => $transaction->refunds,
                    'refundable_amount' => $transaction->refundable_amount,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at,
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
            ], 404);
        }
    }

    /**
     * Get transaction history
     */
    public function getTransactionHistory(Request $request)
    {
        try {
            $user = auth()->user();
            $perPage = $request->get('per_page', 15);

            $query = Transaction::where('user_id', $user->id)
                ->with(['paymentMethod', 'refunds']);

            // Filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('payment_rail')) {
                $query->where('payment_rail', $request->payment_rail);
            }

            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            // Date range
            if ($request->has('from_date')) {
                $query->where('created_at', '>=', $request->from_date);
            }

            if ($request->has('to_date')) {
                $query->where('created_at', '<=', $request->to_date);
            }

            $transactions = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'transactions' => $transactions,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to fetch transaction history', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transactions',
            ], 500);
        }
    }

    /**
     * Upload payment proof for manual bank transfer
     */
    public function uploadPaymentProof(Request $request, $transactionId)
    {
        $validator = Validator::make($request->all(), [
            'proof_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = auth()->user();
            $transaction = Transaction::where('transaction_id', $transactionId)
                ->where('user_id', $user->id)
                ->where('payment_rail', 'manual_bank')
                ->firstOrFail();

            // Upload file
            $file = $request->file('proof_file');
            $filename = 'payment_proof_' . $transaction->transaction_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('payment_proofs', $filename, 'public');
            $fileUrl = '/storage/' . $path;

            // Update transaction
            $transaction = $this->paymentService->uploadPaymentProof($transaction, $fileUrl);

            Log::info('Payment proof uploaded', [
                'transaction_id' => $transaction->transaction_id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment proof uploaded successfully. Awaiting verification.',
                'transaction' => [
                    'id' => $transaction->id,
                    'transaction_id' => $transaction->transaction_id,
                    'status' => $transaction->status,
                    'payment_proof_url' => $transaction->payment_proof_url,
                    'payment_proof_uploaded_at' => $transaction->payment_proof_uploaded_at,
                ],
            ]);

        } catch (Exception $e) {
            Log::error('Payment proof upload failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload payment proof',
            ], 500);
        }
    }

    /**
     * Calculate payment fees
     */
    public function calculateFees(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'payment_rail' => 'required|in:card,fpx,duitnow,ewallet,manual_bank',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $amount = $request->amount;
            $rail = $request->payment_rail;
            $railConfig = config("payment.rails.{$rail}");

            $feePercentage = $railConfig['fee_percentage'] ?? 0;
            $feeFixed = $railConfig['fee_fixed'] ?? 0;

            $fee = ($amount * $feePercentage / 100) + $feeFixed;
            $totalAmount = $amount + $fee;
            $netAmount = $amount - $fee;

            return response()->json([
                'success' => true,
                'calculation' => [
                    'amount' => $amount,
                    'fee' => round($fee, 2),
                    'fee_percentage' => $feePercentage,
                    'fee_fixed' => $feeFixed,
                    'total_amount' => round($totalAmount, 2),
                    'net_amount' => round($netAmount, 2),
                    'currency' => config('payment.currency'),
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate fees',
            ], 500);
        }
    }
}
