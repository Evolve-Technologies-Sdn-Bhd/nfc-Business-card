<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\NfcCard;
use App\Models\NfcTag;
use App\Models\LandingPage;
use App\Services\FileUploadService;
use App\Services\FreeTrialGuard;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    protected $fileUploadService;
    protected PaymentService $paymentService;
    protected FreeTrialGuard $freeTrialGuard;

    public function __construct(
        FileUploadService $fileUploadService,
        PaymentService $paymentService,
        FreeTrialGuard $freeTrialGuard
    ) {
        $this->fileUploadService = $fileUploadService;
        $this->paymentService = $paymentService;
        $this->freeTrialGuard = $freeTrialGuard;
    }

    /**
     * Select plan and update user subscription
     */
    public function selectPlan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan' => 'required|in:free,basic,premium,business',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $plan = $request->plan;

        // ONE-TIME FREE TRIAL.
        // The free account is granted once per person (email) and per device /
        // network. When the rule is hit we refuse only the TRIAL, never the
        // account: the user can still pick a paid plan and buy a card.
        if ($plan === 'free') {
            $verdict = $this->freeTrialGuard->evaluate($request, $user);

            if (!$verdict['allowed']) {
                Log::info('Free trial refused', [
                    'user_id' => $user->id,
                    'code' => $verdict['code'] ?? null,
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'success' => false,
                    'code' => $verdict['code'] ?? 'TRIAL_UNAVAILABLE',
                    'message' => $verdict['message'] ?? 'Your free trial is no longer available. Please purchase an NFC card to continue.',
                    'can_purchase_card' => true,
                ], 403);
            }
        }

        try {
            DB::beginTransaction();

            // The Free plan requires no payment, so it can be activated immediately.
            //
            // It is a TIME-LIMITED account: the user is granted a short window
            // (config payment.subscriptions.trial_days, currently 14 days). If the
            // window ends and no NFC card has been purchased, the scheduled job
            // free-plans:deactivate-expired marks the account inactive (soft — login
            // is never blocked, only the free entitlement is withdrawn).
            if ($plan === 'free') {
                $trialDays = (int) config('payment.subscriptions.trial_days', 14);

                $user->update([
                    'subscription_plan' => 'free',
                    'subscription_start_date' => now(),
                    'subscription_end_date' => now()->addDays($trialDays),
                    'subscription_active' => true,
                    'deactivated_at' => null,
                ]);

                // Stamp the permanent ledger so this person cannot claim another
                // free trial later (even with a different alias email).
                $this->freeTrialGuard->claim($request, $user);
            } else {
                // PAID plans (basic / premium / business) are NOT activated here.
                //
                // Selecting a plan is only a statement of intent. Entitlement and the
                // physical NFC card are granted later, by the trusted payment flow:
                //   selectPlan -> checkout (/payment/initiate) -> Fiuu gateway
                //   -> signed webhook -> OrderFulfilmentService creates the card(s)
                // The fulfilled card (status payment_verified+) is what unlocks the
                // paid features, so a user must never receive a paid plan, or an
                // active card, simply by selecting it without paying.
                Log::info("Paid plan selected (awaiting payment) for user {$user->id}", [
                    'plan' => $plan,
                ]);
            }

            DB::commit();

            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => $plan === 'free'
                    ? 'Free plan activated successfully'
                    : 'Plan selected. Please complete payment to activate your plan.',
                'data' => [
                    'plan' => $plan,
                    'subscription_active' => (bool) $user->subscription_active,
                    'subscription_end_date' => $user->subscription_end_date,
                    'nfc_card' => null,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error selecting plan: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to select plan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save card information
     */
    public function saveCardInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'contactNumber' => 'required|string|max:20',
            'email' => 'required|email',
            'website' => 'nullable|url',
            'address' => 'required|string',
            'companyLogo' => 'nullable|string',
            // Premium plan fields
            'companyBackground' => 'nullable|string',
            'services' => 'nullable|string',
            'linkedin' => 'nullable|url',
            'twitter' => 'nullable|url',
            'facebook' => 'nullable|url',
            'instagram' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();

            // The user's most recent usable card. During onboarding this is empty:
            // the physical card is created by OrderFulfilmentService only after the
            // payment is verified, so we must not hard-fail when there is no card.
            $nfcCard = $user->nfcCards()
                ->whereNotIn('status', ['cancelled', 'expired'])
                ->latest()
                ->first();

            $cardInfo = [
                'services' => $request->services,
                'social_links' => [
                    'linkedin' => $request->linkedin,
                    'twitter' => $request->twitter,
                    'facebook' => $request->facebook,
                    'instagram' => $request->instagram,
                ]
            ];

            $landingPage = null;
            if ($nfcCard) {
                // Get or create landing page for this NFC card
                $landingPage = $nfcCard->landingPage ?? LandingPage::create([
                    'nfc_card_id' => $nfcCard->id,
                ]);

                // Update landing page with card information
                $landingPage->update([
                    'name' => $request->name,
                    'title' => $request->position,
                    'phone' => $request->contactNumber,
                    'email' => $request->email,
                    'website' => $request->website,
                    'address' => $request->address,
                    'bio' => $request->companyBackground,
                ]);
            }

            // Store additional card info in user settings
            $user->update([
                'settings' => array_merge($user->settings ?? [], ['card_info' => $cardInfo])
            ]);

            return response()->json([
                'success' => true,
                'message' => $nfcCard
                    ? 'Card information saved successfully'
                    : 'Card information saved. It will be applied to your profile once your order is fulfilled.',
                'data' => [
                    'landing_page' => $landingPage,
                    'card_info' => $cardInfo
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saving card info: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save card information: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload card design files
     */
    public function uploadCardDesign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'front_design' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
            'back_design' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
            'design_method' => 'required|in:template,custom',
            'selected_template' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        try {
            DB::beginTransaction();

            // Upload front design
            $frontResult = $this->fileUploadService->uploadCardDesign(
                $request->file('front_design'),
                $user->id,
                'front'
            );

            // Upload back design
            $backResult = $this->fileUploadService->uploadCardDesign(
                $request->file('back_design'),
                $user->id,
                'back'
            );

            // Store design information
            $designInfo = [
                'method' => $request->design_method,
                'template' => $request->selected_template,
                'front_design' => $frontResult['url'],
                'front_path' => $frontResult['path'],
                'back_design' => $backResult['url'],
                'back_path' => $backResult['path'],
                'uploaded_at' => now()->toISOString(),
            ];

            $user->update([
                'settings' => array_merge($user->settings ?? [], ['card_design' => $designInfo])
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Card design uploaded successfully',
                'data' => [
                    'front_url' => $frontResult['url'],
                    'back_url' => $backResult['url'],
                    'design_info' => $designInfo
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Card design upload failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload card design: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process payment and create order.
     *
     * This endpoint no longer simulates a successful payment. It hands off to the
     * real Fiuu payment initiation path and returns a genuine gateway redirect URL.
     * The order/card are created in a PRE-payment state and are only marked as paid
     * by the trusted gateway webhook.
     */
    public function processPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Payments are handled by the Fiuu gateway (card / FPX / e-wallet /
            // manual bank transfer). The previous whitelist of "stripe,razorpay"
            // rejected every real payment attempt, so it now accepts the actual
            // gateway identifier/short code instead of hard-coding a dead list.
            'payment_method' => 'required|string|max:50',
            'billing_address' => 'required|array',
            'billing_address.full_name' => 'required|string',
            'billing_address.address' => 'required|string',
            'billing_address.city' => 'required|string',
            'billing_address.zip_code' => 'required|string',
            'card_info' => 'required|array',
            'card_info.name' => 'required|string',
            'card_info.position' => 'required|string',
            'card_info.contact_number' => 'required|string',
            'card_info.email' => 'required|email',
            'card_info.address' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Get plan details
        $plan = $user->subscription_plan;
        $planPrices = [
            'basic' => 9,
            'premium' => 19,
            'business' => 49
        ];
        $planPrice = $planPrices[$plan] ?? 0;
        $cardPrice = 15; // Fixed card price
        $shippingCost = 5; // Fixed shipping cost
        $total = $planPrice + $cardPrice + $shippingCost;

        // Determine business_account_id for Business plan orders
        $businessAccountId = null;
        if ($plan === 'business') {
            if ($user->isBusinessAccount()) {
                $businessAccountId = $user->id;
            } elseif ($user->isBusinessEmployee()) {
                $businessAccountId = $user->parent_business_id;
            }

            // Check quota for Business plan
            $businessAccount = $businessAccountId ? User::find($businessAccountId) : null;
            if ($businessAccount) {
                $quotaInfo = $businessAccount->getQuotaInfo();
                if ($quotaInfo['available_card_quota'] <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Card quota exceeded. Please contact admin to increase quota.',
                        'quota_info' => $quotaInfo
                    ], 403);
                }
            }
        }

        // Gateway availability gate — never claim success when the gateway is not configured.
        $provider = config('payment.default_provider', 'fiuu');
        $providerConfig = config("payment.providers.{$provider}", []);
        $gatewayConfigured = !empty($providerConfig['enabled'])
            && !empty($providerConfig['merchant_id'])
            && !empty($providerConfig['verify_key']);

        if (!$gatewayConfigured) {
            Log::warning('Onboarding payment attempted but gateway is not configured', [
                'user_id' => $user->id,
                'provider' => $provider,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Online payment is currently unavailable. Please try again later or contact support.',
            ], 503);
        }

        try {
            // Create the NFC card order in the pre-payment state. It is NOT activated
            // here — only the trusted gateway webhook may mark it as paid.
            $nfcCard = NfcCard::create([
                'user_id' => $user->id,
                'business_account_id' => $businessAccountId,
                'nfc_card_id' => 'NFC-' . strtoupper(Str::random(12)),
                'card_owner' => $request->card_info['name'],
                'billing_address' => json_encode($request->billing_address),
                'contact_number' => $request->card_info['contact_number'],
                'purchase_date' => now(),
                'subscription_plan' => $plan,
                'purchase_amount' => $total,
                'payment_method' => $request->payment_method,
                'shipping_address' => $request->card_info['address'],
                'status' => 'pending_payment',
                'notes' => 'Order created during onboarding (awaiting online payment)',
            ]);

            // Hand off to the real payment initiation path. This creates the
            // Transaction + Order and returns a genuine gateway redirect URL.
            $transaction = $this->paymentService->processCardPayment($user, [
                'amount' => $total,
                'currency' => 'MYR',
                'payment_rail' => 'card',
                'description' => 'NFC Card ' . strtoupper($plan) . ' order',
                'metadata' => [
                    'plan' => $plan,
                    'card_type' => 'Standard',
                    'shipping_cost' => $shippingCost,
                    'billing_address' => $request->billing_address,
                    'card_info' => $request->card_info,
                    'source' => 'onboarding',
                ],
            ]);

            // Link the card <-> transaction so fulfilment can reuse this card.
            $transaction->update(['nfc_card_id' => $nfcCard->id]);
            $nfcCard->update(['transaction_id' => $transaction->id]);

            $order = $transaction->order;

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated. Please complete the payment on the gateway.',
                'data' => [
                    'order_id' => $nfcCard->id,
                    'order_number' => $order?->order_number,
                    'total_amount' => $total,
                    'payment_status' => 'pending', // NOT 'completed' — awaiting gateway confirmation
                    'card_status' => 'pending_payment',
                    'transaction_id' => $transaction->transaction_id,
                    'redirect_url' => $transaction->metadata['redirect_url'] ?? null,
                    'payment_url' => $transaction->metadata['payment_url'] ?? null,
                    'payment_form_data' => $transaction->metadata['payment_form_data'] ?? null,
                    'estimated_delivery' => now()->addDays(7)->format('Y-m-d'),
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order status
     */
    public function getOrderStatus(Request $request)
    {
        $user = $request->user();

        $nfcCard = $user->nfcCards()->latest()->first();

        if (!$nfcCard) {
            return response()->json([
                'success' => false,
                'message' => 'No order found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $nfcCard->id,
                'status' => $nfcCard->status,
                'purchase_date' => $nfcCard->purchase_date,
                'estimated_delivery' => $nfcCard->purchase_date->addDays(7)->format('Y-m-d'),
                'subscription_plan' => $nfcCard->subscription_plan,
                'total_amount' => $nfcCard->purchase_amount,
                'payment_method' => $nfcCard->payment_method,
            ]
        ]);
    }
} 