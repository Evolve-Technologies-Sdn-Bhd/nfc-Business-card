<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

class SubscriptionController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->middleware('auth:sanctum');
        $this->paymentService = $paymentService;
    }

    /**
     * Get available subscription plans
     */
    public function getPlans()
    {
        try {
            $plans = config('payment.subscriptions.plans');
            $trialDays = config('payment.subscriptions.trial_days');

            $formattedPlans = [];
            foreach ($plans as $planType => $pricing) {
                $formattedPlans[$planType] = [
                    'name' => ucfirst($planType),
                    'pricing' => $pricing,
                    'trial_days' => $trialDays,
                    'features' => $this->getPlanFeatures($planType),
                ];
            }

            return response()->json([
                'success' => true,
                'plans' => $formattedPlans,
                'currency' => config('payment.currency'),
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch plans',
            ], 500);
        }
    }

    /**
     * Get user's active subscription
     */
    public function getActiveSubscription()
    {
        try {
            $user = auth()->user();
            $subscription = Subscription::where('user_id', $user->id)
                ->whereIn('status', ['active', 'past_due'])
                ->with('paymentMethod')
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => true,
                    'subscription' => null,
                    'message' => 'No active subscription',
                ]);
            }

            return response()->json([
                'success' => true,
                'subscription' => [
                    'id' => $subscription->id,
                    'subscription_id' => $subscription->subscription_id,
                    'plan_name' => $subscription->plan_name,
                    'plan_type' => $subscription->plan_type,
                    'amount' => $subscription->amount,
                    'currency' => $subscription->currency,
                    'interval' => $subscription->interval,
                    'status' => $subscription->status,
                    'is_active' => $subscription->isActive(),
                    'is_in_trial' => $subscription->isInTrial(),
                    'current_period_start' => $subscription->current_period_start,
                    'current_period_end' => $subscription->current_period_end,
                    'next_billing_date' => $subscription->next_billing_date,
                    'days_until_billing' => $subscription->days_until_billing,
                    'trial_ends_at' => $subscription->trial_ends_at,
                    'cancelled_at' => $subscription->cancelled_at,
                    'payment_method' => $subscription->paymentMethod ? [
                        'type' => $subscription->paymentMethod->type,
                        'display_name' => $subscription->paymentMethod->display_name,
                    ] : null,
                    'created_at' => $subscription->created_at,
                ],
            ]);

        } catch (Exception $e) {
            Log::error('Failed to fetch subscription', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subscription',
            ], 500);
        }
    }

    /**
     * Get subscription history
     */
    public function getSubscriptionHistory(Request $request)
    {
        try {
            $user = auth()->user();
            $perPage = $request->get('per_page', 10);

            $subscriptions = Subscription::where('user_id', $user->id)
                ->with(['transactions', 'paymentMethod'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'subscriptions' => $subscriptions,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subscription history',
            ], 500);
        }
    }

    /**
     * Create a new subscription
     */
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_type' => 'required|in:basic,business,premium',
            'interval' => 'required|in:monthly,yearly',
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = auth()->user();

            // Check for existing active subscription
            $existingSubscription = Subscription::where('user_id', $user->id)
                ->whereIn('status', ['active', 'past_due'])
                ->first();

            if ($existingSubscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active subscription',
                ], 400);
            }

            $planType = $request->plan_type;
            $interval = $request->interval;
            $plans = config('payment.subscriptions.plans');
            $amount = $plans[$planType][$interval];

            // Create subscription
            $trialEndsAt = config('payment.subscriptions.trial_days') > 0 
                ? now()->addDays(config('payment.subscriptions.trial_days'))
                : null;

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'payment_method_id' => $request->payment_method_id,
                'plan_name' => ucfirst($planType) . ' Plan',
                'plan_type' => $planType,
                'amount' => $amount,
                'currency' => config('payment.currency'),
                'interval' => $interval,
                'status' => 'active',
                'current_period_start' => now(),
                'current_period_end' => $interval === 'monthly' ? now()->addMonth() : now()->addYear(),
                'next_billing_date' => $trialEndsAt ?? ($interval === 'monthly' ? now()->addMonth() : now()->addYear()),
                'trial_ends_at' => $trialEndsAt,
            ]);

            // Update user subscription plan
            $user->update([
                'subscription_plan' => $planType,
                'subscription_status' => 'active',
            ]);

            Log::info('Subscription created', [
                'subscription_id' => $subscription->subscription_id,
                'user_id' => $user->id,
                'plan' => $planType,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription created successfully',
                'subscription' => [
                    'id' => $subscription->id,
                    'subscription_id' => $subscription->subscription_id,
                    'plan_type' => $subscription->plan_type,
                    'amount' => $subscription->amount,
                    'interval' => $subscription->interval,
                    'status' => $subscription->status,
                    'trial_ends_at' => $subscription->trial_ends_at,
                    'next_billing_date' => $subscription->next_billing_date,
                ],
            ], 201);

        } catch (Exception $e) {
            Log::error('Subscription creation failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription',
            ], 500);
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request, $subscriptionId)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500',
            'immediate' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = auth()->user();
            $subscription = Subscription::where('subscription_id', $subscriptionId)
                ->where('user_id', $user->id)
                ->firstOrFail();

            if ($subscription->isCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription is already cancelled',
                ], 400);
            }

            $immediate = $request->get('immediate', false);

            if ($immediate) {
                // Cancel immediately
                $subscription->cancel($request->reason);
                $user->update(['subscription_status' => 'cancelled']);
            } else {
                // Cancel at end of billing period
                $subscription->update([
                    'cancelled_at' => $subscription->current_period_end,
                    'cancellation_reason' => $request->reason,
                ]);
            }

            Log::info('Subscription cancelled', [
                'subscription_id' => $subscription->subscription_id,
                'user_id' => $user->id,
                'immediate' => $immediate,
            ]);

            return response()->json([
                'success' => true,
                'message' => $immediate 
                    ? 'Subscription cancelled immediately' 
                    : 'Subscription will be cancelled at the end of billing period',
                'subscription' => [
                    'status' => $subscription->status,
                    'cancelled_at' => $subscription->cancelled_at,
                ],
            ]);

        } catch (Exception $e) {
            Log::error('Subscription cancellation failed', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel subscription',
            ], 500);
        }
    }

    /**
     * Reactivate cancelled subscription
     */
    public function reactivate($subscriptionId)
    {
        try {
            $user = auth()->user();
            $subscription = Subscription::where('subscription_id', $subscriptionId)
                ->where('user_id', $user->id)
                ->firstOrFail();

            if (!$subscription->isCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription is not cancelled',
                ], 400);
            }

            $subscription->reactivate();
            $user->update(['subscription_status' => 'active']);

            Log::info('Subscription reactivated', [
                'subscription_id' => $subscription->subscription_id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription reactivated successfully',
            ]);

        } catch (Exception $e) {
            Log::error('Subscription reactivation failed', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reactivate subscription',
            ], 500);
        }
    }

    /**
     * Update subscription payment method
     */
    public function updatePaymentMethod(Request $request, $subscriptionId)
    {
        $validator = Validator::make($request->all(), [
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = auth()->user();
            $subscription = Subscription::where('subscription_id', $subscriptionId)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $subscription->update([
                'payment_method_id' => $request->payment_method_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment method updated successfully',
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment method',
            ], 500);
        }
    }

    /**
     * Helper: Get plan features
     */
    protected function getPlanFeatures($planType)
    {
        $features = [
            'basic' => [
                '1 NFC Card',
                'Basic Landing Page',
                'Email Support',
                'Basic Analytics',
            ],
            'business' => [
                '5 NFC Cards',
                'Custom Landing Page',
                'Priority Support',
                'Advanced Analytics',
                'Team Members (3)',
                'Custom Branding',
            ],
            'premium' => [
                'Unlimited NFC Cards',
                'Premium Landing Page',
                '24/7 Priority Support',
                'Premium Analytics',
                'Unlimited Team Members',
                'White Label Branding',
                'API Access',
                'Custom Domain',
            ],
        ];

        return $features[$planType] ?? [];
    }
}
