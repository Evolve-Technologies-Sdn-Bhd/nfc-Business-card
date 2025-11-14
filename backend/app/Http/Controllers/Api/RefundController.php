<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Models\Transaction;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

class RefundController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->middleware('auth:sanctum');
        $this->paymentService = $paymentService;
    }

    /**
     * Request a refund (user)
     */
    public function requestRefund(Request $request, $transactionId)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'nullable|numeric|min:0.01',
            'reason' => 'required|in:duplicate,fraudulent,requested,error,other',
            'notes' => 'nullable|string|max:500',
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

            // Validate transaction can be refunded
            if (!$transaction->isSuccessful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only successful transactions can be refunded',
                ], 400);
            }

            if ($transaction->refundable_amount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction has already been fully refunded',
                ], 400);
            }

            $amount = $request->amount ?? $transaction->refundable_amount;

            if ($amount > $transaction->refundable_amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Refund amount exceeds refundable amount',
                ], 400);
            }

            // Check if auto-approval or manual review required
            $autoApprovalLimit = config('payment.refunds.auto_approval_limit');
            $manualReviewRequired = config('payment.refunds.manual_review_required');

            $data = [
                'amount' => $amount,
                'reason' => $request->reason,
                'notes' => $request->notes,
            ];

            // Auto-approve if below limit
            if ($amount <= $autoApprovalLimit) {
                $refund = $this->paymentService->processRefund($transaction, $data);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Refund processed successfully',
                    'refund' => [
                        'id' => $refund->id,
                        'refund_id' => $refund->refund_id,
                        'amount' => $refund->amount,
                        'status' => $refund->status,
                        'created_at' => $refund->created_at,
                    ],
                ], 201);
            }

            // Create pending refund for manual review
            $refund = Refund::create([
                'transaction_id' => $transaction->id,
                'user_id' => $user->id,
                'provider' => $transaction->provider,
                'amount' => $amount,
                'currency' => $transaction->currency,
                'status' => 'pending',
                'reason' => $request->reason,
                'notes' => $request->notes,
            ]);

            Log::info('Refund requested', [
                'refund_id' => $refund->refund_id,
                'transaction_id' => $transaction->transaction_id,
                'amount' => $amount,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Refund request submitted for review',
                'refund' => [
                    'id' => $refund->id,
                    'refund_id' => $refund->refund_id,
                    'amount' => $refund->amount,
                    'status' => $refund->status,
                    'created_at' => $refund->created_at,
                ],
            ], 201);

        } catch (Exception $e) {
            Log::error('Refund request failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to request refund',
            ], 500);
        }
    }

    /**
     * Get refund details
     */
    public function show($refundId)
    {
        try {
            $user = auth()->user();
            $refund = Refund::where('refund_id', $refundId)
                ->where('user_id', $user->id)
                ->with(['transaction', 'processedBy'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'refund' => [
                    'id' => $refund->id,
                    'refund_id' => $refund->refund_id,
                    'transaction_id' => $refund->transaction->transaction_id,
                    'amount' => $refund->amount,
                    'currency' => $refund->currency,
                    'status' => $refund->status,
                    'reason' => $refund->reason,
                    'notes' => $refund->notes,
                    'processed_by' => $refund->processedBy ? $refund->processedBy->name : null,
                    'processed_at' => $refund->processed_at,
                    'failure_message' => $refund->failure_message,
                    'created_at' => $refund->created_at,
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Refund not found',
            ], 404);
        }
    }

    /**
     * Get refund history for user
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $perPage = $request->get('per_page', 15);

            $query = Refund::where('user_id', $user->id)
                ->with('transaction');

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $refunds = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'refunds' => $refunds,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to fetch refunds', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch refunds',
            ], 500);
        }
    }

    /**
     * Process pending refund (admin only)
     */
    public function processRefund(Request $request, $refundId)
    {
        // This middleware should be added in routes
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $admin = auth()->user();
            
            if (!$admin->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Admin access required.',
                ], 403);
            }

            $refund = Refund::where('refund_id', $refundId)
                ->with('transaction')
                ->firstOrFail();

            if ($refund->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Refund has already been processed',
                ], 400);
            }

            if ($request->action === 'approve') {
                // Process the refund
                $data = [
                    'amount' => $refund->amount,
                    'reason' => $refund->reason,
                    'notes' => $request->notes ?? $refund->notes,
                    'processed_by' => $admin->id,
                ];

                $refund = $this->paymentService->processRefund($refund->transaction, $data);

                return response()->json([
                    'success' => true,
                    'message' => 'Refund approved and processed',
                    'refund' => [
                        'id' => $refund->id,
                        'status' => $refund->status,
                        'processed_at' => $refund->processed_at,
                    ],
                ]);
            } else {
                // Reject the refund
                $refund->update([
                    'status' => 'cancelled',
                    'notes' => $request->notes ?? $refund->notes,
                    'processed_by' => $admin->id,
                    'processed_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Refund rejected',
                ]);
            }

        } catch (Exception $e) {
            Log::error('Refund processing failed', [
                'refund_id' => $refundId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process refund',
            ], 500);
        }
    }

    /**
     * Get pending refunds (admin only)
     */
    public function getPendingRefunds(Request $request)
    {
        try {
            $admin = auth()->user();
            
            if (!$admin->is_admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Admin access required.',
                ], 403);
            }

            $perPage = $request->get('per_page', 20);

            $refunds = Refund::where('status', 'pending')
                ->with(['user', 'transaction'])
                ->orderBy('created_at', 'asc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'refunds' => $refunds,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to fetch pending refunds', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending refunds',
            ], 500);
        }
    }
}
