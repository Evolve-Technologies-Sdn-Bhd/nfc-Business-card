<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NfcCard;
use App\Models\NfcTag;
use App\Models\LandingPage;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NfcCardController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function index(Request $request)
    {
        $user = $request->user();
        \Log::info('NFC Cards index called', ['user_id' => $user?->id, 'user_email' => $user?->email]);

        // Check if user has premium subscription
        if (!$user->hasPremiumSubscription()) {
            \Log::warning('User does not have premium subscription', ['user_id' => $user?->id]);
            return response()->json([
                'success' => false,
                'message' => 'This feature requires a Premium subscription',
                'upgrade_required' => true
            ], 403);
        }

        // If user is Business Admin, get all cards under the business account
        if ($user->isBusinessAccount()) {
            // Get cards for business admin and all employees
            $nfcCards = NfcCard::where('business_account_id', $user->id)
                ->with(['nfcTag', 'user:id,first_name,last_name,email,job_title'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            \Log::info('Business Admin NFC cards retrieved', [
                'user_id' => $user->id,
                'count' => $nfcCards->count(),
                'business_account_id' => $user->id
            ]);
        } else {
            // Regular user or employee - only get their own cards
            $nfcCards = $user->nfcCards()->with('nfcTag')->orderBy('created_at', 'desc')->get();
            \Log::info('NFC cards retrieved', ['user_id' => $user->id, 'count' => $nfcCards->count()]);
        }

        return response()->json([
            'success' => true,
            'nfc_cards' => $nfcCards
        ]);
    }

    public function show(Request $request, NfcCard $nfcCard)
    {
        $user = $request->user();
        
        // Check ownership: card owner OR Business Admin
        $isOwner = $nfcCard->user_id === $user->id;
        $isBusinessAdmin = $user->isBusinessAccount() && $nfcCard->business_account_id === $user->id;
        
        if (!$isOwner && !$isBusinessAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this card'
            ], 403);
        }

        // Check if user has premium subscription
        if (!$user->hasPremiumSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'This feature requires a Premium subscription',
                'upgrade_required' => true
            ], 403);
        }

        $nfcCard->load('nfcTag', 'analytics', 'user:id,first_name,last_name,email,job_title');

        return response()->json([
            'success' => true,
            'nfc_card' => $nfcCard
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        // Check if user has premium subscription
        if (!$user->hasPremiumSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'This feature requires a Premium subscription',
                'upgrade_required' => true
            ], 403);
        }

        // For Business plan, check quota before allowing card order
        if ($request->subscription_plan === 'business') {
            $businessAccount = $user->isBusinessAccount() ? $user : $user->parentBusiness;
            
            if (!$businessAccount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Business account not found'
                ], 404);
            }

            $quotaInfo = $businessAccount->getQuotaInfo();
            
            if ($quotaInfo['available_card_quota'] <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Card quota exceeded. Please contact admin to increase quota.',
                    'quota_info' => [
                        'total_card_quota' => $quotaInfo['total_card_quota'],
                        'ordered_cards_count' => $quotaInfo['ordered_cards_count'],
                        'available_card_quota' => 0
                    ]
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'card_owner' => 'required|string|max:255',
            'billing_address' => 'required|string',
            'contact_number' => 'required|string|max:20',
            'subscription_plan' => 'required|in:free,basic,premium,business',
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

        // Determine business_account_id for Business plan orders
        $businessAccountId = null;
        if ($request->subscription_plan === 'business') {
            if ($user->isBusinessAccount()) {
                $businessAccountId = $user->id;
            } elseif ($user->isBusinessEmployee()) {
                $businessAccountId = $user->parent_business_id;
            }
        }

        // Generate unique NFC card ID (this would typically be done by admin)
        $nfcCardId = 'NFC-' . strtoupper(Str::random(12));

        $nfcCard = NfcCard::create([
            'user_id' => $user->id,
            'business_account_id' => $businessAccountId,
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

        // Send NFC card purchased notification
        $this->notificationService->create($user, 'nfc_card_purchased', [
            'card_id' => $nfcCardId,
            'amount' => '$' . number_format($request->purchase_amount, 2),
            'plan' => ucfirst($request->subscription_plan),
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

        // Check if user has premium subscription
        if (!$request->user()->hasPremiumSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'This feature requires a Premium subscription',
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

        // Check if user has premium subscription
        if (!$request->user()->hasPremiumSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'This feature requires a Premium subscription',
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

        // Send NFC card activated notification
        $this->notificationService->create($request->user(), 'nfc_card_activated', [
            'card_id' => $nfcCard->nfc_card_id,
            'nfc_id' => $request->nfc_id,
        ]);

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

        // Check if user has premium subscription
        if (!$request->user()->hasPremiumSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'This feature requires a Premium subscription',
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

        // Check if user has premium subscription
        if (!$request->user()->hasPremiumSubscription()) {
            return response()->json([
                'success' => false,
                'message' => 'This feature requires a Premium subscription',
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
                'has_basic_subscription' => $user->hasBasicSubscription(),
                'has_premium_subscription' => $user->hasPremiumSubscription(),
                'has_physical_card' => $user->hasPhysicalCard(),
                'active_nfc_card' => $user->activeNfcCard
            ]
        ]);
    }

    /**
     * Get landing page for an NFC card
     */
    public function getLandingPage(Request $request, NfcCard $nfcCard)
    {
        // Public access - no authentication required
        // Anyone with the NFC card ID can view the landing page
        
        $landingPage = $nfcCard->landingPage;

        if (!$landingPage) {
            return response()->json([
                'success' => false,
                'message' => 'No landing page found for this card'
            ], 404);
        }

        // Load social links relationship
        $landingPage->load('socialLinks');

        return response()->json([
            'success' => true,
            'landing_page' => $landingPage
        ]);
    }

    /**
     * Update or create landing page for an NFC card
     */
    public function updateLandingPage(Request $request, NfcCard $nfcCard)
    {
        // Check ownership
        if ($nfcCard->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:500',
            'address' => 'nullable|string',
            'profile_image' => 'nullable|string',
            'company_logo' => 'nullable|string',
            'company_logo_text' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:255',
            'company_registration_no' => 'nullable|string|max:255',
            'company_department' => 'nullable|string|max:255',
            'address_name' => 'nullable|string|max:255',
            'address_street' => 'nullable|string|max:255',
            'address_area' => 'nullable|string|max:255',
            'address_city_state' => 'nullable|string|max:255',
            'address_country' => 'nullable|string|max:255',
            'address_map_url' => 'nullable|url',
            'stats' => 'nullable|array',
            'services' => 'nullable|array',
            'team_members' => 'nullable|array',
            'phone_number' => 'nullable|string|max:50',
            'phone_label' => 'nullable|string|max:100',
            'email_address' => 'nullable|email|max:255',
            'email_label' => 'nullable|string|max:100',
            'whatsapp_number' => 'nullable|string|max:50',
            'whatsapp_label' => 'nullable|string|max:100',
            'website_url' => 'nullable|url|max:500',
            'website_label' => 'nullable|string|max:100',
            'profile_style' => 'nullable|string|max:50',
            'theme' => 'nullable|string|max:50',
            'background_color' => 'nullable|string|max:50',
            'text_color' => 'nullable|string|max:50',
            'font' => 'nullable|string|max:50',
            'button_style' => 'nullable|string|max:50',
            'show_watermark' => 'nullable|boolean',
            
            // Links validation
            'links' => 'nullable|array',
            'links.*.id' => 'nullable|exists:social_links,id',
            'links.*.title' => 'required_with:links|string|max:255',
            'links.*.url' => 'required_with:links|url',
            'links.*.platform' => 'required_with:links|string|max:50',
            'links.*.is_active' => 'nullable|boolean',
            'links.*.order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Update or create landing page
        $landingPageData = $request->except('links'); // Exclude links from direct save
        $landingPage = LandingPage::updateOrCreate(
            ['nfc_card_id' => $nfcCard->id],
            $landingPageData
        );

        // Handle links sync if provided
        if ($request->has('links')) {
            $links = $request->input('links', []);
            $existingLinkIds = [];
            
            foreach ($links as $linkData) {
                if (isset($linkData['id']) && $linkData['id']) {
                    // Update existing link
                    $link = \App\Models\SocialLink::where('id', $linkData['id'])
                        ->where('landing_page_id', $landingPage->id)
                        ->first();
                    
                    if ($link) {
                        $link->update([
                            'title' => $linkData['title'],
                            'url' => $linkData['url'],
                            'platform' => $linkData['platform'],
                            'is_active' => $linkData['is_active'] ?? true,
                            'order' => $linkData['order'] ?? 0,
                        ]);
                        $existingLinkIds[] = $link->id;
                    }
                } else {
                    // Create new link
                    $newLink = \App\Models\SocialLink::create([
                        'landing_page_id' => $landingPage->id,
                        'title' => $linkData['title'],
                        'url' => $linkData['url'],
                        'platform' => $linkData['platform'],
                        'is_active' => $linkData['is_active'] ?? true,
                        'order' => $linkData['order'] ?? 0,
                    ]);
                    $existingLinkIds[] = $newLink->id;
                }
            }
            
            // Delete links that are not in the request (were removed by user)
            \App\Models\SocialLink::where('landing_page_id', $landingPage->id)
                ->whereNotIn('id', $existingLinkIds)
                ->delete();
        }

        // Reload landing page with relationships
        $landingPage->load('socialLinks');

        return response()->json([
            'success' => true,
            'landing_page' => $landingPage,
            'message' => 'Landing page saved successfully'
        ]);
    }
} 