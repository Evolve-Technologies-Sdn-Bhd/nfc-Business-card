<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

class ManualBankTransferController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->middleware('auth:sanctum');
        $this->paymentService = $paymentService;
    }

    /**
     * Get pending manual bank transfers awaiting verification
     */
    public function getPendingTransfers(Request $request)
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

            $transfers = Transaction::awaitingVerification()
                ->with(['user'])
                ->orderBy('payment_proof_uploaded_at', 'asc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'transfers' => $transfers->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'transaction_id' => $transaction->transaction_id,
                        'user' => [
                            'id' => $transaction->user->id,
                            'name' => $transaction->user->name,
                            'email' => $transaction->user->email,
                        ],
                        'amount' => $transaction->amount,
                        'currency' => $transaction->currency,
                        'bank_reference_code' => $transaction->bank_reference_code,
                        'payment_proof_url' => $transaction->payment_proof_url,
                        'payment_proof_uploaded_at' => $transaction->payment_proof_uploaded_at,
                        'description' => $transaction->description,
                        'metadata' => $transaction->metadata,
                        'created_at' => $transaction->created_at,
                    ];
                }),
                'pagination' => [
                    'current_page' => $transfers->currentPage(),
                    'total' => $transfers->total(),
                    'per_page' => $transfers->perPage(),
                ],
            ]);

        } catch (Exception $e) {
            Log::error('Failed to fetch pending transfers', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending transfers',
            ], 500);
        }
    }

    /**
     * Verify manual bank transfer
     */
    public function verifyTransfer(Request $request, $transactionId)
    {
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

            $transaction = Transaction::where('transaction_id', $transactionId)
                ->where('payment_rail', 'manual_bank')
                ->with('user')
                ->firstOrFail();

            if ($transaction->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction has already been processed',
                ], 400);
            }

            if ($request->action === 'approve') {
                // Verify the payment
                $transaction = $this->paymentService->verifyManualBankTransfer($transaction, $admin);

                Log::info('Manual bank transfer approved', [
                    'transaction_id' => $transaction->transaction_id,
                    'admin_id' => $admin->id,
                ]);

                // TODO: Send notification to user

                return response()->json([
                    'success' => true,
                    'message' => 'Payment verified successfully',
                    'transaction' => [
                        'id' => $transaction->id,
                        'transaction_id' => $transaction->transaction_id,
                        'status' => $transaction->status,
                        'verified_at' => $transaction->verified_at,
                        'verified_by' => $admin->name,
                    ],
                ]);
            } else {
                // Reject the payment
                $transaction->update([
                    'status' => 'failed',
                    'failure_code' => 'payment_rejected',
                    'failure_message' => $request->notes ?? 'Payment proof rejected by admin',
                    'verified_by' => $admin->id,
                    'verified_at' => now(),
                ]);

                Log::info('Manual bank transfer rejected', [
                    'transaction_id' => $transaction->transaction_id,
                    'admin_id' => $admin->id,
                    'reason' => $request->notes,
                ]);

                // TODO: Send notification to user

                return response()->json([
                    'success' => true,
                    'message' => 'Payment rejected',
                ]);
            }

        } catch (Exception $e) {
            Log::error('Transfer verification failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify transfer',
            ], 500);
        }
    }

    /**
     * Get all manual bank transfer transactions
     */
    public function getAllTransfers(Request $request)
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
            $query = Transaction::where('payment_rail', 'manual_bank')
                ->with(['user', 'verifiedBy']);

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Date range
            if ($request->has('from_date')) {
                $query->where('created_at', '>=', $request->from_date);
            }

            if ($request->has('to_date')) {
                $query->where('created_at', '<=', $request->to_date);
            }

            $transfers = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'transfers' => $transfers,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to fetch transfers', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transfers',
            ], 500);
        }
    }
}
