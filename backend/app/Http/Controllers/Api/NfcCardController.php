<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NfcCard;
use App\Models\NfcTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NfcCardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Check if user has pro subscription
        if (!$user->hasProSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'This feature requires a Pro subscription',
                'upgrade_required' => true
            ], 403);
        }

        $nfcCards = $user->nfcCards()->with('nfcTag')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'nfc_cards' => $nfcCards
        ]);
    }

    public function show(Request $request, NfcCard $nfcCard)
    {
        // Check ownership
        if ($nfcCard->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if user has pro subscription
        if (!$request->user()->hasProSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'This feature requires a Pro subscription',
                'upgrade_required' => true
            ], 403);
        }

        $nfcCard->load('nfcTag', 'analytics');

        return response()->json([
            'success' => true,
            'nfc_card' => $nfcCard
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        // Check if user has pro subscription
        if (!$user->hasProSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'This feature requires a Pro subscription',
                'upgrade_required' => true
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'card_owner' => 'required|string|max:255',
            'billing_address' => 'required|string',
            'contact_number' => 'required|string|max:20',
            'subscription_plan' => 'required|in:basic,pro,enterprise',
            'purchase_amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:100',
            'shipping_address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate unique NFC card ID (this would typically be done by admin)
        $nfcCardId = 'NFC-' . strtoupper(Str::random(12));

        $nfcCard = NfcCard::create([
            'user_id' => $user->id,
            'nfc_card_id' => $nfcCardId,
            'card_owner' => $request->card_owner,
            'billing_address' => $request->billing_address,
            'contact_number' => $request->contact_number,
            'purchase_date' => now(),
            'subscription_plan' => $request->subscription_plan,
            'purchase_amount' => $request->purchase_amount,
            'payment_method' => $request->payment_method,
            'shipping_address' => $request->shipping_address,
            'notes' => $request->notes,
        ]);

        // Update user's subscription details
        $user->update([
            'subscription_plan' => $request->subscription_plan,
            'subscription_start_date' => now(),
            'subscription_end_date' => now()->addYear(),
            'subscription_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'nfc_card' => $nfcCard,
            'message' => 'NFC card order created successfully'
        ], 201);
    }

    public function update(Request $request, NfcCard $nfcCard)
    {
        // Check ownership
        if ($nfcCard->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if user has pro subscription
        if (!$request->user()->hasProSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'This feature requires a Pro subscription',
                'upgrade_required' => true
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'card_owner' => 'nullable|string|max:255',
            'billing_address' => 'nullable|string',
            'contact_number' => 'nullable|string|max:20',
            'shipping_address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $nfcCard->update($request->only([
            'card_owner',
            'billing_address',
            'contact_number',
            'shipping_address',
            'notes'
        ]));

        return response()->json([
            'success' => true,
            'nfc_card' => $nfcCard,
            'message' => 'NFC card updated successfully'
        ]);
    }

    public function activate(Request $request, NfcCard $nfcCard)
    {
        // Check ownership
        if ($nfcCard->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if user has pro subscription
        if (!$request->user()->hasProSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'This feature requires a Pro subscription',
                'upgrade_required' => true
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'nfc_id' => 'required|string|unique:nfc_tags,nfc_id',
            'name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Create or update NFC tag
        $nfcTag = NfcTag::updateOrCreate(
            ['nfc_card_id' => $nfcCard->nfc_card_id],
            [
                'user_id' => $request->user()->id,
                'nfc_id' => $request->nfc_id,
                'name' => $request->name ?? 'Business Card',
                'status' => 'active',
            ]
        );

        // Update card status
        $nfcCard->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'nfc_tag' => $nfcTag,
            'nfc_card' => $nfcCard,
            'message' => 'NFC card activated successfully'
        ]);
    }

    public function deactivate(Request $request, NfcCard $nfcCard)
    {
        // Check ownership
        if ($nfcCard->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if user has pro subscription
        if (!$request->user()->hasProSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'This feature requires a Pro subscription',
                'upgrade_required' => true
            ], 403);
        }

        $nfcCard->update(['status' => 'inactive']);

        // Deactivate associated NFC tag
        if ($nfcCard->nfcTag) {
            $nfcCard->nfcTag->update(['status' => 'inactive']);
        }

        return response()->json([
            'success' => true,
            'message' => 'NFC card deactivated successfully'
        ]);
    }

    public function analytics(Request $request, NfcCard $nfcCard)
    {
        // Check ownership
        if ($nfcCard->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if user has pro subscription
        if (!$request->user()->hasProSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'This feature requires a Pro subscription',
                'upgrade_required' => true
            ], 403);
        }

        // Get analytics for the NFC card
        $analytics = $nfcCard->analytics()
            ->where('action', 'nfc_tap')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalTaps = $analytics->count();
        $recentTaps = $analytics->take(10);

        return response()->json([
            'success' => true,
            'data' => [
                'total_taps' => $totalTaps,
                'recent_taps' => $recentTaps,
                'nfc_card' => $nfcCard->load('nfcTag')
            ]
        ]);
    }

    public function subscriptionStatus(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'subscription_plan' => $user->subscription_plan,
                'subscription_active' => $user->subscription_active,
                'subscription_start_date' => $user->subscription_start_date,
                'subscription_end_date' => $user->subscription_end_date,
                'has_pro_subscription' => $user->hasProSubscription(),
                'has_physical_card' => $user->hasPhysicalCard(),
                'active_nfc_card' => $user->activeNfcCard
            ]
        ]);
    }
} 