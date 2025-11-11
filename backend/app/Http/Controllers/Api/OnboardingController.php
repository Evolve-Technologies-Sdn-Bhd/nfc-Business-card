<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\NfcCard;
use App\Models\NfcTag;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
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

        // Update user's subscription plan
        $user->update([
            'subscription_plan' => $plan,
            'subscription_start_date' => now(),
            'subscription_end_date' => now()->addYear(),
            'subscription_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plan selected successfully',
            'data' => [
                'plan' => $plan,
                'subscription_active' => true,
                'subscription_end_date' => $user->subscription_end_date
            ]
        ]);
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

        $user = $request->user();

        // Update user profile with card information
        $profile = $user->profile;
        $profile->update([
            'name' => $request->name,
            'title' => $request->position,
            'phone' => $request->contactNumber,
            'email' => $request->email,
            'website' => $request->website,
            'location' => $request->address,
            'bio' => $request->companyBackground,
        ]);

        // Store additional card info in user settings
        $cardInfo = [
            'services' => $request->services,
            'social_links' => [
                'linkedin' => $request->linkedin,
                'twitter' => $request->twitter,
                'facebook' => $request->facebook,
                'instagram' => $request->instagram,
            ]
        ];

        $user->update([
            'settings' => array_merge($user->settings ?? [], ['card_info' => $cardInfo])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Card information saved successfully',
            'data' => [
                'profile' => $profile,
                'card_info' => $cardInfo
            ]
        ]);
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
     * Process payment and create order
     */
    public function processPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:stripe,razorpay',
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

        try {
            DB::beginTransaction();

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

            // Create NFC card order
            $nfcCard = NfcCard::create([
                'user_id' => $user->id,
                'card_owner' => $request->card_info['name'],
                'billing_address' => json_encode($request->billing_address),
                'contact_number' => $request->card_info['contact_number'],
                'purchase_date' => now(),
                'subscription_plan' => $plan,
                'purchase_amount' => $total,
                'payment_method' => $request->payment_method,
                'shipping_address' => $request->card_info['address'],
                'status' => 'inactive', // Will be activated when admin encodes the physical NFC card
                'notes' => 'Order created during onboarding',
            ]);

            // Create payment record (you might want to create a separate payments table)
            $paymentInfo = [
                'payment_method' => $request->payment_method,
                'amount' => $total,
                'status' => 'completed', // Mock payment success
                'transaction_id' => 'MOCK_' . Str::random(16),
                'billing_address' => $request->billing_address,
                'processed_at' => now()->toISOString(),
            ];

            // Store payment information
            $user->update([
                'settings' => array_merge($user->settings ?? [], [
                    'payment_info' => $paymentInfo,
                    'order_id' => $nfcCard->id
                ])
            ]);

            // Update card status to active
            $nfcCard->update(['status' => 'active']);

            // Update user subscription
            $user->update([
                'subscription_active' => true,
                'has_physical_card' => true,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'order_id' => $nfcCard->id,
                    'total_amount' => $total,
                    'payment_status' => 'completed',
                    'card_status' => 'active',
                    'estimated_delivery' => now()->addDays(7)->format('Y-m-d'),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
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