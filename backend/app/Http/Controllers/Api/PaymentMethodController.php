<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentMethodController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->middleware('auth:sanctum');
        $this->paymentService = $paymentService;
    }

    /**
     * Get all payment methods for authenticated user
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $query = PaymentMethod::where('user_id', $user->id);

            // Filter by type
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            // Only verified
            if ($request->get('verified_only', false)) {
                $query->verified();
            }

            $paymentMethods = $query->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($method) {
                    return [
                        'id' => $method->id,
                        'type' => $method->type,
                        'provider' => $method->provider,
                        'display_name' => $method->display_name,
                        'card_brand' => $method->card_brand,
                        'card_last4' => $method->card_last4,
                        'card_exp_month' => $method->card_exp_month,
                        'card_exp_year' => $method->card_exp_year,
                        'is_expired' => $method->isExpired(),
                        'bank_name' => $method->bank_name,
                        'bank_account_last4' => $method->bank_account_last4,
                        'ewallet_type' => $method->ewallet_type,
                        'is_default' => $method->is_default,
                        'is_verified' => $method->is_verified,
                        'created_at' => $method->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'payment_methods' => $paymentMethods,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to fetch payment methods', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment methods',
            ], 500);
        }
    }

    /**
     * Get a specific payment method
     */
    public function show($id)
    {
        try {
            $user = auth()->user();
            $paymentMethod = PaymentMethod::where('user_id', $user->id)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'payment_method' => [
                    'id' => $paymentMethod->id,
                    'type' => $paymentMethod->type,
                    'provider' => $paymentMethod->provider,
                    'display_name' => $paymentMethod->display_name,
                    'card_brand' => $paymentMethod->card_brand,
                    'card_last4' => $paymentMethod->card_last4,
                    'card_exp_month' => $paymentMethod->card_exp_month,
                    'card_exp_year' => $paymentMethod->card_exp_year,
                    'is_expired' => $paymentMethod->isExpired(),
                    'bank_name' => $paymentMethod->bank_name,
                    'ewallet_type' => $paymentMethod->ewallet_type,
                    'is_default' => $paymentMethod->is_default,
                    'is_verified' => $paymentMethod->is_verified,
                    'created_at' => $paymentMethod->created_at,
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method not found',
            ], 404);
        }
    }

    /**
     * Save a new payment method
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:card,bank,ewallet',
            'provider_payment_method_id' => 'nullable|string',
            'is_default' => 'boolean',
            
            // Card fields
            'card_brand' => 'required_if:type,card|string',
            'card_last4' => 'required_if:type,card|string|size:4',
            'card_exp_month' => 'required_if:type,card|string|size:2',
            'card_exp_year' => 'required_if:type,card|string|size:4',
            'card_fingerprint' => 'nullable|string',
            
            // Bank fields
            'bank_name' => 'required_if:type,bank|string',
            'bank_account_last4' => 'nullable|string|size:4',
            
            // E-wallet fields
            'ewallet_type' => 'required_if:type,ewallet|in:tng,grabpay,boost,shopeepay',
            'ewallet_account_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = auth()->user();
            
            $paymentMethod = $this->paymentService->savePaymentMethod($user, $request->all());

            Log::info('Payment method saved', [
                'user_id' => $user->id,
                'payment_method_id' => $paymentMethod->id,
                'type' => $paymentMethod->type,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment method saved successfully',
                'payment_method' => [
                    'id' => $paymentMethod->id,
                    'type' => $paymentMethod->type,
                    'display_name' => $paymentMethod->display_name,
                    'is_default' => $paymentMethod->is_default,
                    'created_at' => $paymentMethod->created_at,
                ],
            ], 201);

        } catch (Exception $e) {
            Log::error('Failed to save payment method', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save payment method',
            ], 500);
        }
    }

    /**
     * Update payment method (mainly for setting default)
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'is_default' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = auth()->user();
            $paymentMethod = PaymentMethod::where('user_id', $user->id)
                ->findOrFail($id);

            // Set as default
            if ($request->has('is_default') && $request->is_default) {
                // Unset other defaults
                PaymentMethod::where('user_id', $user->id)
                    ->where('id', '!=', $id)
                    ->update(['is_default' => false]);
                
                $paymentMethod->update(['is_default' => true]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment method updated successfully',
                'payment_method' => [
                    'id' => $paymentMethod->id,
                    'is_default' => $paymentMethod->is_default,
                ],
            ]);

        } catch (Exception $e) {
            Log::error('Failed to update payment method', [
                'payment_method_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment method',
            ], 500);
        }
    }

    /**
     * Delete a payment method
     */
    public function destroy($id)
    {
        try {
            $user = auth()->user();
            $paymentMethod = PaymentMethod::where('user_id', $user->id)
                ->findOrFail($id);

            // Check if it's used in active subscriptions
            $activeSubscriptions = $paymentMethod->subscriptions()
                ->whereIn('status', ['active', 'past_due'])
                ->count();

            if ($activeSubscriptions > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete payment method with active subscriptions',
                ], 400);
            }

            $paymentMethod->delete();

            Log::info('Payment method deleted', [
                'user_id' => $user->id,
                'payment_method_id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment method deleted successfully',
            ]);

        } catch (Exception $e) {
            Log::error('Failed to delete payment method', [
                'payment_method_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment method',
            ], 500);
        }
    }

    /**
     * Get default payment method
     */
    public function getDefault()
    {
        try {
            $user = auth()->user();
            $paymentMethod = PaymentMethod::where('user_id', $user->id)
                ->where('is_default', true)
                ->first();

            if (!$paymentMethod) {
                return response()->json([
                    'success' => true,
                    'payment_method' => null,
                    'message' => 'No default payment method set',
                ]);
            }

            return response()->json([
                'success' => true,
                'payment_method' => [
                    'id' => $paymentMethod->id,
                    'type' => $paymentMethod->type,
                    'display_name' => $paymentMethod->display_name,
                    'card_last4' => $paymentMethod->card_last4,
                    'is_expired' => $paymentMethod->isExpired(),
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch default payment method',
            ], 500);
        }
    }
}
