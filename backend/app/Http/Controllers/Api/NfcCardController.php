<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NfcCard;
use App\Models\NfcTag;
use App\Models\LandingPage;
use App\Models\ProfileDesignOption;
use App\Models\Transaction;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class NfcCardController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Helper — create activity_logs entry for user-initiated NFC card flows.
     * Wrapped in try/catch since audit log is non-critical side effect.
     */
    protected function recordUserNfcCardActivity(
        $user,
        string $actionType,
        string $description,
        string $entityType = 'nfc_card',
        ?int $entityId = null,
        array $metadata = []
    ): void {
        try {
            $defaults = [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id' => $user?->id,
                'business_account_id' => $user?->isBusinessAccount() ? $user?->id : ($user?->business_account_id ?? null),
                'action_type' => $actionType,
                'action_description' => $description,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'metadata' => count($metadata) ? json_encode($metadata) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            ActivityLog::create($defaults);
        } catch (\Throwable $e) {
            // Non-critical: never break main API response because of audit log.
            report($e);
        }
    }
    public function index(Request $request)
    {
        $user = $request->user();
        \Log::info('NFC Cards index called', ['user_id' => $user?->id, 'user_email' => $user?->email]);

        // Free accounts that pre-date digital cards get theirs on first visit.
        app(\App\Services\DigitalCardService::class)->ensureFor($user);

        $plan = $user->subscription_plan;
        $subActive = (bool) $user->subscription_active;
        $hasActiveAccess = in_array($plan, ['basic', 'premium', 'business'], true) && $subActive;
        if (!$hasActiveAccess) {
            // A digital card is not an order, so it keeps upgrade_required = true.
            $hasActiveAccess = $user->nfcCards()
                ->physical()
                ->whereIn('status', ['payment_verified','processing','shipped','delivered','active','pending_payment','awaiting_payment_verification'])
                ->exists();
        }

        if (!$hasActiveAccess) {
            // Listing your own cards is not a privileged action: a Free user (or
            // one whose cards have all expired/cancelled) simply gets their own
            // list — usually empty — plus a flag the UI can use for upsell copy.
            // Answering 403 here made every Free visit to My Cards / the builder
            // log request failures.
            return response()->json([
                'success' => true,
                'nfc_cards' => $user->nfcCards()->orderBy('created_at', 'desc')->get(),
                'upgrade_required' => true,
                'message' => 'Order an NFC card to manage it here.',
            ]);
        }

        $eagerUser = 'user:id,first_name,last_name,email,job_title,name_slug';

        // If user is Business Admin, get all cards under the business account
        if ($user->isBusinessAccount()) {
            // Cards issued under the business (employees) PLUS the owner's own
            // cards — an owner card created without business_account_id was
            // otherwise missing from Business Cards and Business Analytics.
            $nfcCards = NfcCard::where(function ($q) use ($user) {
                    $q->where('business_account_id', $user->id)
                      ->orWhere('user_id', $user->id);
                })
                ->with(['nfcTag', $eagerUser, 'transaction', 'cardTemplate'])
                ->orderBy('created_at', 'desc')
                ->get();

            \Log::info('Business Admin NFC cards retrieved', [
                'user_id' => $user->id,
                'count' => $nfcCards->count(),
                'business_account_id' => $user->id
            ]);
        } else {
            // Regular user or employee - only get their own cards
            $nfcCards = $user->nfcCards()
                ->with(['nfcTag', $eagerUser, 'transaction', 'cardTemplate'])
                ->orderBy('created_at', 'desc')
                ->get();
            \Log::info('NFC cards retrieved', ['user_id' => $user->id, 'count' => $nfcCards->count()]);
        }

        // Bulk hydrate name-slug clash counts for all card owners (1 query instead of N+1)
        $relatedUsers = $nfcCards->pluck('user')->filter();
        if ($relatedUsers->isNotEmpty()) {
            \App\Models\User::hydrateNameSlugClashCounts($relatedUsers);
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

        $hasActiveAccess = $user->hasPremiumSubscription() || $nfcCard->canAccessProfileBuilder();

        if (!$hasActiveAccess) {
            return response()->json([
                'success' => false,
                'message' => 'This feature requires a Premium subscription or payment verification',
                'upgrade_required' => true
            ], 403);
        }

        $nfcCard->load(['nfcTag', 'analytics', 'user:id,first_name,last_name,email,job_title', 'transaction', 'cancelledBy']);

        return response()->json([
            'success' => true,
            'nfc_card' => $nfcCard
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

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
            'card_template_id' => 'nullable|exists:card_templates,id',
            'is_custom_design' => 'nullable|boolean',
            'custom_design_front' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'custom_design_back' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'design_notes' => 'nullable|string|max:1000',
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

        // Company card template locked by the Business owner (Team Members →
        // Company profile & locks): every team card uses it, whatever design
        // the order form sent. This was only applied to the bulk CSV upload,
        // which no screen uses, so the lock had no effect on real orders.
        $companyTemplateId = null;
        if ($businessAccountId) {
            $owner = $user->id === $businessAccountId ? $user : \App\Models\User::find($businessAccountId);
            $settings = $owner ? app(\App\Services\BusinessTenantService::class)->settingsFor($owner) : null;
            if ($settings && $settings->card_template_id
                && in_array(\App\Services\BusinessTenantService::CARD_TEMPLATE_LOCK, $settings->locked_fields ?? [], true)) {
                $companyTemplateId = (int) $settings->card_template_id;
            }
        }

        // Generate unique NFC card ID (this would typically be done by admin)
        $nfcCardId = 'NFC-' . strtoupper(Str::random(12));

        $defaultStatus = 'pending_payment';
        $plan = $request->subscription_plan;

        // Free plan - activate immediately
        if ($plan === 'free') {
            $defaultStatus = 'active';
        }

        // Handle custom design file uploads
        $customFrontUrl = null;
        $customBackUrl = null;
        // A locked company template rules out a custom design upload.
        $isCustomDesign = $companyTemplateId ? false : (bool) $request->input('is_custom_design', false);

        if ($isCustomDesign) {
            if ($request->hasFile('custom_design_front')) {
                $frontFile = $request->file('custom_design_front');
                $frontFileName = 'card-designs/' . $nfcCardId . '-front-' . time() . '.' . $frontFile->extension();
                $frontFile->storeAs('public', $frontFileName);
                $customFrontUrl = Storage::url($frontFileName);
            }
            if ($request->hasFile('custom_design_back')) {
                $backFile = $request->file('custom_design_back');
                $backFileName = 'card-designs/' . $nfcCardId . '-back-' . time() . '.' . $backFile->extension();
                $backFile->storeAs('public', $backFileName);
                $customBackUrl = Storage::url($backFileName);
            }
        }

        $cardTemplateId = $request->input('card_template_id');
        if ($isCustomDesign) {
            $cardTemplateId = null;
        }
        if ($companyTemplateId) {
            $cardTemplateId = $companyTemplateId;
        }

        $nfcCard = NfcCard::create([
            'user_id' => $user->id,
            'business_account_id' => $businessAccountId,
            'nfc_card_id' => $nfcCardId,
            'card_template_id' => $cardTemplateId,
            'card_owner' => $request->card_owner,
            'billing_address' => $request->billing_address,
            'contact_number' => $request->contact_number,
            'purchase_date' => now(),
            'subscription_plan' => $plan,
            'purchase_amount' => $request->purchase_amount,
            'payment_method' => $request->payment_method ?? 'manual_bank_transfer',
            'shipping_address' => $request->shipping_address,
            'notes' => $request->notes,
            'is_custom_design' => $isCustomDesign,
            'custom_design_front_url' => $customFrontUrl,
            'custom_design_back_url' => $customBackUrl,
            'design_notes' => $request->input('design_notes'),
            'status' => $defaultStatus,
        ]);

        // For free plan - activate subscription immediately
        if ($plan === 'free') {
            $user->update([
                'subscription_plan' => $plan,
                'subscription_start_date' => now(),
                'subscription_end_date' => now()->addYear(),
                'subscription_active' => true,
            ]);

            $this->notificationService->create($user, 'nfc_card_purchased', [
                'card_id' => $nfcCardId,
                'amount' => '$' . number_format($request->purchase_amount, 2),
                'plan' => ucfirst($plan),
            ]);
        } else {
            // Paid plan - create placeholder Transaction manual bank transfer (pending)
            $transactionId = 'TXN-' . strtoupper(Str::random(16));

            $transaction = Transaction::create([
                'transaction_id' => $transactionId,
                'user_id' => $user->id,
                'nfc_card_id' => $nfcCard->id,
                'type' => 'purchase',
                'payment_rail' => 'manual_bank_transfer',
                'provider' => 'manual',
                'amount' => $request->purchase_amount,
                'currency' => config('app.currency', 'MYR'),
                'fee' => 0,
                'net_amount' => $request->purchase_amount,
                'status' => 'pending',
                'description' => 'NFC Card ' . strtoupper($plan) . ' Order #' . $nfcCardId,
            ]);

            // Link transaction back to NfcCard
            $nfcCard->update(['transaction_id' => $transaction->id]);

            $this->notificationService->create($user, 'nfc_card_order_created', [
                'nfc_card_id' => $nfcCardId,
                'amount' => 'RM' . number_format($request->purchase_amount, 2),
                'plan' => ucfirst($plan),
                'transaction_id' => $transactionId,
                'action_url' => '/UserDashboard/CardManagement',
                'action_text' => 'View Order',
            ]);
        }

        // Activity log — order created
        $this->recordUserNfcCardActivity(
            $user,
            'nfc_card_order_created',
            $plan === 'free'
                ? 'Free NFC card created for ' . $user->email
                : 'NFC Card ' . strtoupper($plan) . ' order submitted by ' . $user->email,
            'nfc_card',
            $nfcCard->id,
            [
                'plan' => $plan,
                'purchase_amount' => (float) $request->purchase_amount,
                'transaction_id' => isset($transaction) ? $transaction->transaction_id : null,
                'business_account_id' => $businessAccountId,
                'card_id' => $nfcCardId,
                'card_owner' => $request->card_owner,
                'card_template_id' => $cardTemplateId,
                'is_custom_design' => $isCustomDesign,
                'custom_design_front_url' => $customFrontUrl,
                'custom_design_back_url' => $customBackUrl,
                'has_design_notes' => !empty($request->input('design_notes')),
            ]
        );

        return response()->json([
            'success' => true,
            'nfc_card' => $nfcCard->load(['transaction', 'cardTemplate']),
            'message' => $plan === 'free'
                ? 'NFC card created successfully'
                : 'NFC card order created successfully. Please complete payment and upload proof.',
            // true when the company's locked card template replaced the chosen design
            'company_template_applied' => (bool) $companyTemplateId,
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
        $user = $request->user();

        // A card keeps ONE tag ID for life: the physical chip / printed QR can
        // point at it, so re-activating a paused card must never mint a new ID.
        $existingTag = NfcTag::where('nfc_card_id', $nfcCard->nfc_card_id)->first();

        $validator = Validator::make($request->all(), [
            'nfc_id' => [
                'nullable',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('nfc_tags', 'nfc_id')->ignore($existingTag?->id),
            ],
            'name' => 'nullable|string|max:255',
            'activation_code' => 'nullable|string|max:32',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $isOwner = $nfcCard->user_id === $user->id;

        // Claiming a card that is NOT already assigned to this user requires the
        // card's activation code (proves physical ownership of the card).
        // Owners — including cards already assigned to them through a paid order —
        // are exempt and never need to re-enter the code.
        if (!$isOwner) {
            $provided = strtoupper(trim((string) $request->input('activation_code', '')));
            $expected = strtoupper((string) $nfcCard->activation_code);

            if ($provided === '' || $expected === '' || !hash_equals($expected, $provided)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This card does not belong to your account. Please enter the activation code printed with your card to claim it.'
                ], 422);
            }
        }

        // Check if user has access: premium sub OR card is in verified+ state
        $hasAccess = $user->hasPremiumSubscription() || $nfcCard->canAccessProfileBuilder();
        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'This feature requires a Premium subscription or payment verification',
                'upgrade_required' => true
            ], 403);
        }

        // Claim ownership now that the activation code has been verified.
        if (!$isOwner) {
            $nfcCard->update(['user_id' => $user->id]);
        }

        // Create or update NFC tag — existing tag ID wins; a new one is only
        // issued (client-supplied or generated) the first time.
        $nfcId = $existingTag?->nfc_id
            ?: ($request->filled('nfc_id') ? $request->nfc_id : 'NFC-' . strtoupper(Str::random(9)));

        $nfcTag = NfcTag::updateOrCreate(
            ['nfc_card_id' => $nfcCard->nfc_card_id],
            [
                'user_id' => $request->user()->id,
                'nfc_id' => $nfcId,
                'name' => $request->name ?? $existingTag?->name ?? 'Business Card',
                'status' => 'active',
            ]
        );

        // Update card status only if not already active/delivered
        if (!$nfcCard->isActive()) {
            $nfcCard->update(['status' => 'active']);
        }

        // Send NFC card activated notification
        $this->notificationService->create($request->user(), 'nfc_card_activated', [
            'card_id' => $nfcCard->nfc_card_id,
            'nfc_id' => $nfcTag->nfc_id,
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
        $user = $request->user();
        // Check ownership
        if ($nfcCard->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $hasAccess = $user->hasPremiumSubscription() || $nfcCard->canAccessProfileBuilder();
        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'This feature requires a Premium subscription or payment verification',
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
        $user = $request->user();
        // Check ownership
        if ($nfcCard->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $hasAccess = $user->hasPremiumSubscription() || $nfcCard->canAccessProfileBuilder();
        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'This feature requires a Premium subscription or payment verification',
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

    public function uploadPaymentProof(Request $request, NfcCard $nfcCard)
    {
        try {
            $user = $request->user();

            if ($nfcCard->user_id !== $user->id && !($user->isBusinessAccount() && $nfcCard->business_account_id === $user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this card order'
                ], 403);
            }

            if (!in_array($nfcCard->status, ['pending_payment', 'awaiting_payment_verification'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment proof can only be uploaded for orders that have not yet been verified'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'reference_code' => 'nullable|string|max:100',
                'bank_name' => 'nullable|string|max:100',
                'payment_date' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please check your inputs and try again.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('payment_proof');
            if (!$file || !$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment proof file is invalid or corrupted. Please re-upload.'
                ], 422);
            }

            // ── Generate base URL from current request (fixes localhost:8000 ghost) ──
            $scheme = $request->getScheme();
            $host = $request->getHost();
            $port = $request->getPort();
            $portSuffix = in_array((int)$port, [80, 443], true) ? '' : ':' . $port;
            $baseUrl = rtrim($scheme . '://' . $host . $portSuffix, '/');
            $fallbackUrl = rtrim(Config::get('app.url'), '/');
            if (empty($baseUrl) || str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1')) {
                $baseUrl = $fallbackUrl;
            }

            // ── Store proof to public disk ──
            $ext = $file->getClientOriginalExtension() ?: $file->extension() ?: 'pdf';
            $relativeDir = 'payment-proofs';
            $fileNameNoDir = $nfcCard->nfc_card_id . '-' . time() . '.' . $ext;
            $storedPath = $file->storeAs($relativeDir, $fileNameNoDir, 'public');
            $fileName = $relativeDir . '/' . $fileNameNoDir;
            if (!$storedPath) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save payment proof file to storage. Please try again.'
                ], 500);
            }
            $rawProofUrl = Storage::disk('public')->url($fileName);
            // Correct localhost / wrong scheme ghost URLs (matches system settings logic)
            if ($rawProofUrl && (str_contains($rawProofUrl, 'localhost:8000') || stripos($rawProofUrl, 'http://') === 0 && stripos($baseUrl, 'https://') === 0)) {
                $path = preg_replace('#^https?://[^/]+#', '', $rawProofUrl);
                if ($path) {
                    $rawProofUrl = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
                }
            }
            $proofUrl = $rawProofUrl;

            // ── Resolve a safe amount (never pass NULL to decimal column) ──
            $amount = $nfcCard->purchase_amount;
            if ($amount === null || $amount === '') {
                $planFallback = [
                    'free' => 0,
                    'basic' => 29.00,
                    'premium' => 59.00,
                    'business' => 149.00,
                ];
                $p = strtolower((string)($nfcCard->subscription_plan ?? 'premium'));
                $amount = $planFallback[$p] ?? 59.00;
            }
            $amountNumeric = is_numeric($amount) ? (float)$amount : (float)filter_var((string)$amount, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            if (!is_finite($amountNumeric) || $amountNumeric < 0) {
                $amountNumeric = 0;
            }
            $amountNumeric = round($amountNumeric, 2);
            $currency = config('app.currency', 'MYR');

            // ── Atomic: transaction record + nfc_card status update ──
            DB::beginTransaction();
            try {
                $transaction = $nfcCard->transaction;

                if (!$transaction) {
                    $transactionId = 'TXN-' . strtoupper(Str::random(16));
                    $transaction = Transaction::create([
                        'transaction_id' => $transactionId,
                        'user_id' => $nfcCard->user_id,
                        'nfc_card_id' => $nfcCard->id,
                        'type' => 'purchase',
                        'payment_rail' => 'manual_bank_transfer',
                        'provider' => 'manual',
                        'amount' => $amountNumeric,
                        'currency' => $currency,
                        'fee' => 0,
                        'net_amount' => $amountNumeric,
                        'status' => 'pending',
                        'description' => 'Payment for NFC Card Order #' . $nfcCard->nfc_card_id,
                    ]);
                    // Safe update — skip if column missing (migration not applied yet on older DBs)
                    try {
                        DB::statement(
                            "UPDATE nfc_cards SET transaction_id = ? WHERE id = ?",
                            [$transaction->id, $nfcCard->id]
                        );
                        $nfcCard->setRelation('transaction', $transaction->fresh());
                    } catch (\Throwable $e) {
                        // Ignore — transaction_id column might not exist in legacy tables
                        report($e);
                    }
                }

                $transaction->update([
                    'payment_proof_url' => $proofUrl,
                    'payment_proof_uploaded_at' => now(),
                    'bank_name' => $request->input('bank_name') ?? $transaction->bank_name,
                    'bank_reference_code' => $request->input('reference_code') ?? $transaction->bank_reference_code,
                    'status' => 'pending',
                ]);

                $nfcCard->update([
                    'status' => 'awaiting_payment_verification',
                ]);
                DB::commit();
            } catch (\Throwable $dbEx) {
                DB::rollBack();
                // Clean up orphan uploaded proof file so we don't leave garbage
                try { Storage::disk('public')->delete($fileName); } catch (\Throwable $_) {}
                throw $dbEx;
            }

            // Reload fresh relationships
            $nfcCard->load(['transaction']);

            // Notify user
            try {
                $this->notificationService->create($user, 'system_message', [
                    'system_message' => 'Payment proof for order #' . $nfcCard->nfc_card_id . ' has been submitted. Awaiting admin verification.',
                    'icon' => '💵',
                    'priority' => 'normal',
                ]);
            } catch (\Throwable $e) { report($e); }

            // Notify all admins
            try {
                $adminUserIds = User::where('is_admin', true)->pluck('id')->toArray();
                if (!empty($adminUserIds)) {
                    $this->notificationService->createForMultiple($adminUserIds, 'nfc_card_payment_proof_uploaded', [
                        'nfc_card_id' => $nfcCard->nfc_card_id,
                        'user_name' => $user->full_name ?? $user->name ?? $user->email,
                        'user_email' => $user->email,
                        'payment_proof_url' => $proofUrl,
                        'reference_code' => $request->input('reference_code') ?? '',
                        'action_url' => '/AdminManagement/nfc-cards',
                        'action_text' => 'Verify Payment',
                    ]);
                }
            } catch (\Throwable $e) { report($e); }

            // Activity log — payment proof uploaded
            try {
                $this->recordUserNfcCardActivity(
                    $user,
                    'nfc_card_payment_proof_uploaded',
                    'Payment proof for card order #' . $nfcCard->nfc_card_id . ' uploaded by ' . $user->email,
                    'nfc_card',
                    $nfcCard->id,
                    [
                        'proof_filename' => $fileName,
                        'proof_url' => $proofUrl,
                        'file_size_bytes' => $file->getSize() ?? null,
                        'bank_name' => $request->input('bank_name') ?? null,
                        'reference_code' => $request->input('reference_code') ?? null,
                        'payment_date' => $request->input('payment_date') ?? null,
                        'transaction_id' => $transaction->transaction_id ?? null,
                        'card_id' => $nfcCard->nfc_card_id,
                    ]
                );
            } catch (\Throwable $e) { report($e); }

            return response()->json([
                'success' => true,
                'message' => 'Payment proof submitted successfully. Please wait for admin verification.',
                'nfc_card' => $nfcCard,
                'proof_url' => $proofUrl,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => $ve->getMessage() ?: 'Validation failed.',
                'errors' => $ve->errors(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);
            $friendly = 'Server error. Please try again later.';
            $detail = $e->getMessage();
            if (stripos($detail, 'no such column') !== false || stripos($detail, 'unknown column') !== false) {
                $friendly = 'System configuration out of date — please run php artisan migrate on the server or contact support.';
            } elseif (stripos($detail, 'SQLSTATE') !== false || stripos($detail, 'database') !== false) {
                $friendly = 'Database error. Please try again or contact support if the issue persists.';
            } elseif (stripos($detail, 'disk') !== false || stripos($detail, 'storage') !== false || stripos($detail, 'mkdir') !== false || stripos($detail, 'permission') !== false) {
                $friendly = 'Storage permission error — please ensure storage folder is writable or run php artisan storage:link.';
            }
            return response()->json([
                'success' => false,
                'message' => $friendly,
                'debug_message' => app()->environment('local', 'staging') ? $detail : null,
            ], 500);
        }
    }

    public function confirmReceived(Request $request, NfcCard $nfcCard)
    {
        $user = $request->user();

        if ($nfcCard->user_id !== $user->id && !($user->isBusinessAccount() && $nfcCard->business_account_id === $user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this card'
            ], 403);
        }

        // Validate status: shipped (normal flow) OR delivered (admin override not yet activated)
        if (!$nfcCard->canUserConfirmAndActivate()) {
            $statusLabel = method_exists($nfcCard, 'statusLabels') ? (collect($nfcCard->statusLabels())->get($nfcCard->status, $nfcCard->status)) : $nfcCard->status;
            return response()->json([
                'success' => false,
                'message' => 'This card status (' . $statusLabel . ') cannot be activated. Only Shipped OR Delivered (Not Activated) statuses are allowed.',
            ], 422);
        }

        $oldStatus = $nfcCard->status; // "shipped" ATAU "delivered"

        $nfcCard->update([
            'status' => 'active',        // User confirm → terus AKTIF, tak perlu step activate berasingan
            'user_received_confirmed_at' => $nfcCard->user_received_confirmed_at ?? now(),
            'delivered_date' => $nfcCard->delivered_date ?? now(),
        ]);

        // Activate subscription if not already active
        if (!$user->subscription_active || $user->subscription_plan !== $nfcCard->subscription_plan) {
            $user->update([
                'subscription_plan' => $nfcCard->subscription_plan,
                'subscription_start_date' => now(),
                'subscription_end_date' => now()->addYear(),
                'subscription_active' => true,
            ]);
        }

        // Mark transaction as succeeded if exists
        if ($nfcCard->transaction && $nfcCard->transaction->status !== 'succeeded') {
            $nfcCard->transaction->update([
                'status' => 'succeeded',
                'verified_at' => $nfcCard->transaction->verified_at ?? now(),
            ]);
        }

        $this->notificationService->create($user, 'nfc_card_activated', [
            'card_id' => $nfcCard->nfc_card_id,
            'action_url' => '/UserDashboard/UserManagement/PremiumPlanUser/PremiumProfileBuilder',
            'action_text' => 'Design Profile Now',
        ]);

        $this->notificationService->create($user, 'subscription_upgrade', [
            'plan' => ucfirst($nfcCard->subscription_plan),
        ]);

        // Notify admins
        $adminUserIds = User::where('is_admin', true)->pluck('id')->toArray();
        if (!empty($adminUserIds)) {
            $this->notificationService->createForMultiple($adminUserIds, 'nfc_card_delivered', [
                'order_number' => $nfcCard->nfc_card_id,
                'card_id' => $nfcCard->nfc_card_id,
                'user_name' => $user->full_name ?? $user->name ?? $user->email,
                'user_email' => $user->email,
                'message' => 'User has confirmed receipt of card #' . $nfcCard->nfc_card_id,
            ]);
        }

        // Activity log — user confirmed receipt
        $this->recordUserNfcCardActivity(
            $user,
            'nfc_card_user_confirmed_receipt',
            'User ' . $user->email . ' confirmed receipt of physical card #' . $nfcCard->nfc_card_id,
            'nfc_card',
            $nfcCard->id,
            [
                'card_id' => $nfcCard->nfc_card_id,
                'confirmed_at' => now()->toIso8601String(),
                'subscription_plan' => $nfcCard->subscription_plan,
                'subscription_end_date' => $user->subscription_end_date,
                'transaction_id' => $nfcCard->transaction->transaction_id ?? null,
                'delivered_date' => $nfcCard->delivered_date,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Card receipt confirmed. Your subscription is now active!',
            'nfc_card' => $nfcCard->fresh('transaction'),
        ], 200);
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
     * Track NFC card tap (public endpoint)
     */
    public function trackTap(Request $request, NfcCard $nfcCard)
    {
        // Create analytics record for this tap
        \App\Models\Analytics::create([
            'trackable_type' => NfcCard::class,
            'trackable_id' => $nfcCard->id,
            'action' => 'nfc_tap',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $this->getDeviceType($request->userAgent()),
            'browser' => $this->getBrowser($request->userAgent()),
            'platform' => $this->getPlatform($request->userAgent()),
            'referrer' => $request->header('Referer'),
            'data' => [
                'duration' => $request->input('duration', 0),
                'source' => $request->input('source', 'nfc_tap'),
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tap tracked successfully'
        ]);
    }

    private function getDeviceType($userAgent)
    {
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            if (preg_match('/iPad|Tablet/', $userAgent)) {
                return 'tablet';
            }
            return 'mobile';
        }
        return 'desktop';
    }

    private function getBrowser($userAgent)
    {
        if (preg_match('/Chrome/', $userAgent)) return 'Chrome';
        if (preg_match('/Firefox/', $userAgent)) return 'Firefox';
        if (preg_match('/Safari/', $userAgent)) return 'Safari';
        if (preg_match('/Edge/', $userAgent)) return 'Edge';
        if (preg_match('/Opera/', $userAgent)) return 'Opera';
        return 'Other';
    }

    private function getPlatform($userAgent)
    {
        if (preg_match('/Windows/', $userAgent)) return 'Windows';
        if (preg_match('/Mac/', $userAgent)) return 'macOS';
        if (preg_match('/Linux/', $userAgent)) return 'Linux';
        if (preg_match('/Android/', $userAgent)) return 'Android';
        if (preg_match('/iPhone|iPad/', $userAgent)) return 'iOS';
        return 'Other';
    }

    /**
     * Get landing page for an NFC card
     */
    public function getLandingPage(Request $request, NfcCard $nfcCard)
    {
        // Public access - no authentication required
        // Anyone with the NFC card ID can view the landing page
        
        // A Free digital card is withdrawn (inactive) when the free window ends;
        // its public profile goes offline with it.
        if ($nfcCard->is_digital && $nfcCard->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'This profile is not available.'
            ], 404);
        }

        $landingPage = $nfcCard->landingPage;

        // Auto-create landing page if it doesn't exist (for all plan types)
        if (!$landingPage) {
            $landingPage = LandingPage::create([
                'nfc_card_id' => $nfcCard->id,
                'name' => $nfcCard->user->name ?? '',
                'email' => $nfcCard->user->email ?? '',
            ]);
            // A Business member's new profile starts with the company's values.
            app(\App\Services\BusinessTenantService::class)->applyCompanyValues($landingPage);
        }

        // Respect the owner's publication state: an unpublished profile
        // (is_active = false) must not leak the owner's name, email, phone,
        // address or social links on this unauthenticated endpoint.
        if (!$landingPage->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This profile is not available.'
            ], 404);
        }

        // Load social links relationship
        $landingPage->load('socialLinks');

        return response()->json([
            'success' => true,
            'landing_page' => $landingPage,
            'show_nfcgo_branding' => $this->shouldShowNfcgoBranding($nfcCard, $landingPage),
            'effective_features' => $this->effectiveFeatures($nfcCard, $landingPage),
            'videos' => $this->profileVideos($nfcCard, $landingPage),
            'design' => $this->resolvedDesign($nfcCard, $landingPage),
        ]);
    }

    /**
     * Decide whether the NFCGo "Powered by" branding renders on a public profile.
     *
     * Admin-controlled via the 'nfcgo_branding' feature toggle:
     *   - is_active       → master on/off switch
     *   - available_plans → the plans that display the branding
     *
     * Owners who unlocked the "Remove Branding" capability can still hide it,
     * so plan defaults and the paid override never conflict.
     */
    private function shouldShowNfcgoBranding(NfcCard $nfcCard, LandingPage $landingPage): bool
    {
        $policy = ProfileDesignOption::query()
            ->where('type', 'feature_toggle')
            ->where('option_id', 'nfcgo_branding')
            ->first();

        if (!$policy || !$policy->is_active) {
            return false;
        }

        $plan = strtolower($nfcCard->subscription_plan ?: ($nfcCard->user->subscription_plan ?? 'free'));

        if (!$policy->isAvailableForPlan($plan)) {
            return false;
        }

        // Per-profile override: a purchased "Remove Branding" hides the badge.
        $features = $landingPage->features ?? [];

        return ($features['remove_branding'] ?? false) !== true;
    }

    /**
     * Profile features that show as actions on the public page (Save Contact,
     * Message, QR Code, Share, Book), keyed by feature id.
     *
     * Each one is ON only when:
     *   1. the admin feature toggle (profile_design_options, type feature_toggle)
     *      is active and allowed for the card's plan — features without an
     *      admin toggle (social_sharing) skip this check; and
     *   2. the owner has not switched it off. The builders store the owner's
     *      choice under two names (Features panel: `vcard_download`; Profile tab
     *      toggle: `vcardEnabled`), so OFF under either name wins.
     *
     * Before this, the public page treated every feature as ON unless the
     * `vcard_download`-style key was saved as false, so a disabled Profile-tab
     * toggle or a plan that does not include the feature still showed the action.
     */
    /**
     * Public contact form on a profile ("Message" action).
     *
     * POST /api/nfc-cards/{nfcCard}/contact
     * Accepted only while the profile is online and its contact_form feature
     * is on (plan + admin toggle + owner switch, see effectiveFeatures()). The
     * owner gets an in-app notification and an e-mail at the address set in the
     * builder (Contact Form e-mail), falling back to the account e-mail.
     */
    public function submitContactForm(Request $request, NfcCard $nfcCard)
    {
        $landingPage = $nfcCard->landingPage;

        $offline = !$landingPage
            || !$landingPage->is_active
            || ($nfcCard->is_digital && $nfcCard->status !== 'active');

        if ($offline || !($this->effectiveFeatures($nfcCard, $landingPage)['contact_form'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => 'This profile is not accepting messages.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $owner = $nfcCard->user;

        if (!$owner) {
            return response()->json(['success' => false, 'message' => 'This profile is not accepting messages.'], 403);
        }

        try {
            $this->notificationService->create($owner, 'contact_request', [
                'name' => $data['name'],
                'sender_email' => $data['email'],
                'sender_phone' => $data['phone'] ?? null,
                'sender_message' => $data['message'],
                'nfc_card_id' => $nfcCard->id,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Contact form notification failed', ['card' => $nfcCard->id, 'error' => $e->getMessage()]);
        }

        $features = is_array($landingPage->features) ? $landingPage->features : [];
        $to = $features['contact_form_email'] ?? $features['contactFormEmail'] ?? null;
        $to = filter_var($to, FILTER_VALIDATE_EMAIL) ? $to : $owner->email;

        if ($to) {
            try {
                $body = "New message from your NFCGo profile\n\n"
                    . "Name: {$data['name']}\n"
                    . "E-mail: {$data['email']}\n"
                    . (!empty($data['phone']) ? "Phone: {$data['phone']}\n" : '')
                    . "\n{$data['message']}\n";

                \Illuminate\Support\Facades\Mail::raw($body, function ($mail) use ($to, $data) {
                    $mail->to($to)
                        ->replyTo($data['email'], $data['name'])
                        ->subject('[NFC Go] New message from ' . $data['name']);
                });
            } catch (\Throwable $e) {
                \Log::warning('Contact form e-mail failed', ['card' => $nfcCard->id, 'error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Message sent.',
        ]);
    }

    /**
     * Player URLs for the profile videos, or null when there is none or the
     * card's plan does not include the field (Introduction Video: as set on
     * the introVideo builder field, Premium/Business by default; Company
     * Video: companyVideo, Business by default). Only YouTube/Vimeo links
     * become players (App\Support\VideoEmbed).
     */
    private function profileVideos(NfcCard $nfcCard, LandingPage $landingPage): array
    {
        $plan = strtolower($nfcCard->subscription_plan ?: ($nfcCard->user->subscription_plan ?? 'free'));

        $allowed = \App\Models\ProfileBuilderField::query()
            ->whereIn('field_key', ['introVideo', 'companyVideo'])
            ->get(['field_key', 'available_plans', 'is_visible'])
            ->mapWithKeys(function ($field) use ($plan) {
                $plans = $field->available_plans;
                if (is_string($plans)) {
                    $plans = json_decode($plans, true);
                }
                $ok = $field->is_visible && (empty($plans) || in_array($plan, (array) $plans, true));
                return [$field->field_key => $ok];
            });

        return [
            'intro' => ($allowed['introVideo'] ?? false) ? \App\Support\VideoEmbed::embedUrl($landingPage->intro_video) : null,
            'company' => ($allowed['companyVideo'] ?? false) ? \App\Support\VideoEmbed::embedUrl($landingPage->company_video) : null,
        ];
    }

    /** System default palette ("Ink & Brass"), also the fallback for locked themes. */
    public const DEFAULT_DESIGN = [
        'theme' => 'ink-brass',
        'background_color' => '#06090d',
        'text_color' => '#f7f5f0',
        'accent_color' => '#c2a068',
        'bg_gradient' => 'radial-gradient(120% 90% at 12% 0%, rgba(194, 160, 104, 0.10) 0%, transparent 46%), radial-gradient(90% 80% at 92% 12%, rgba(194, 160, 104, 0.08) 0%, transparent 52%), #06090d',
    ];

    /**
     * The colours the public profile renders with: the owner's theme and
     * colours from the Profile Builder.
     *
     * - A theme the card's plan does not include (admin theme options,
     *   type=theme, available_plans) falls back to the system palette; Free
     *   cards get the themes offered to Free or Basic, like the builder.
     * - The theme gradient is used only while the background is still that
     *   theme's colour; a custom background colour replaces it.
     *
     * The public page used to force "Ink & Brass" for everyone, so a theme
     * chosen in the builder never showed on the live profile.
     */
    private function resolvedDesign(NfcCard $nfcCard, LandingPage $landingPage): array
    {
        $plan = strtolower($nfcCard->subscription_plan ?: ($nfcCard->user->subscription_plan ?? 'free'));
        $config = is_array($landingPage->design_config) ? $landingPage->design_config : [];
        $themeId = $landingPage->theme ?: ($config['theme']['id'] ?? null);

        // Still exactly the landing_pages column defaults (theme "minimal",
        // #FFFFFF on #000000): the owner never picked a theme. Choosing
        // "Minimal" in the builder writes the theme's own text colour instead.
        $untouched = strtolower((string) $landingPage->theme) === 'minimal'
            && strtoupper((string) $landingPage->background_color) === '#FFFFFF'
            && strtoupper((string) $landingPage->text_color) === '#000000'
            && empty($config['theme']);
        if (!$themeId || $untouched) {
            return self::DEFAULT_DESIGN + ['locked' => false];
        }

        $option = ProfileDesignOption::query()->where('type', 'theme')->where('option_id', $themeId)->first();

        // Not one of the admin themes: never chosen. The landing_pages columns
        // default to theme "minimal" / white / black, so most untouched
        // profiles carry that value without the owner ever picking it.
        if (!$option) {
            return self::DEFAULT_DESIGN + ['locked' => false];
        }

        $allowed = $option->is_active && ($plan === 'free'
            ? ($option->isAvailableForPlan('free') || $option->isAvailableForPlan('basic'))
            : $option->isAvailableForPlan($plan));
        if (!$allowed) {
            return self::DEFAULT_DESIGN + ['locked' => true];
        }

        $theme = array_merge(
            is_array($config['theme'] ?? null) ? $config['theme'] : [],
            is_array($option?->config) ? $option->config : []
        );

        $background = $landingPage->background_color ?: ($theme['backgroundColor'] ?? self::DEFAULT_DESIGN['background_color']);

        $gradient = $landingPage->bg_gradient ?: null;
        if (!$gradient && !empty($theme['bgGradient'])
            && strcasecmp((string) $background, (string) ($theme['backgroundColor'] ?? '')) === 0) {
            $gradient = $theme['bgGradient'];
        }

        return [
            'theme' => $themeId,
            'background_color' => $background,
            'text_color' => $landingPage->text_color ?: ($theme['textColor'] ?? self::DEFAULT_DESIGN['text_color']),
            'accent_color' => $landingPage->accent_color ?: ($theme['accentColor'] ?? self::DEFAULT_DESIGN['accent_color']),
            'bg_gradient' => $gradient,
            'locked' => false,
        ];
    }

    public const PROFILE_ACTION_FEATURES = [
        'vcard_download' => 'vcardEnabled',
        'contact_form' => 'contactFormEnabled',
        'qr_code' => 'qrCodeEnabled',
        'booking_integration' => null,
        'social_sharing' => null,
    ];

    private function effectiveFeatures(NfcCard $nfcCard, LandingPage $landingPage): array
    {
        $plan = strtolower($nfcCard->subscription_plan ?: ($nfcCard->user->subscription_plan ?? 'free'));
        $saved = is_array($landingPage->features) ? $landingPage->features : [];

        $adminToggles = ProfileDesignOption::query()
            ->where('type', 'feature_toggle')
            ->whereIn('option_id', array_keys(self::PROFILE_ACTION_FEATURES))
            ->get()
            ->keyBy('option_id');

        $result = [];
        foreach (self::PROFILE_ACTION_FEATURES as $key => $alias) {
            $toggle = $adminToggles->get($key);
            $allowedByPlan = !$toggle || ($toggle->is_active && $toggle->isAvailableForPlan($plan));

            $ownerOn = ($saved[$key] ?? true) !== false
                && ($alias === null || ($saved[$alias] ?? true) !== false);

            $result[$key] = $allowedByPlan && $ownerOn;
        }

        return $result + self::linkSettings($nfcCard, $landingPage);
    }

    /**
     * Link options from the builder's Links tab, resolved for the public page:
     *  - open_new_tab   (owner "Open in New Tab", default on)
     *  - click_tracking (admin click_tracking toggle for the plan AND owner
     *                    "Enable Click Analytics", default on)
     */
    public static function linkSettings(NfcCard $nfcCard, LandingPage $landingPage): array
    {
        $plan = strtolower($nfcCard->subscription_plan ?: ($nfcCard->user->subscription_plan ?? 'free'));
        $saved = is_array($landingPage->features) ? $landingPage->features : [];

        $toggle = ProfileDesignOption::query()
            ->where('type', 'feature_toggle')
            ->where('option_id', 'click_tracking')
            ->first();
        $trackingAllowed = !$toggle || ($toggle->is_active && $toggle->isAvailableForPlan($plan));

        return [
            'open_new_tab' => ($saved['openNewTab'] ?? true) !== false,
            'click_tracking' => $trackingAllowed && ($saved['clickTracking'] ?? true) !== false,
        ];
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

        // A withdrawn Free digital card (free window ended) is read-only.
        if ($nfcCard->is_digital && $nfcCard->status !== 'active') {
            return response()->json([
                'success' => false,
                'code' => 'FREE_WINDOW_ENDED',
                'message' => 'Your free trial has ended. Get an NFC card to save your profile.',
            ], 403);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            // Basic Info
            'name' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'pronouns' => 'nullable|string|max:50',
            'qualification' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'intro_video' => ['nullable', 'string', 'max:500', \App\Support\VideoEmbed::rule()],
            'tagline' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|string|max:255', // Changed from email to string to allow empty
            'website' => 'nullable|string|max:500', // Changed from url to string to allow empty
            'address' => 'nullable|string',
            
            // Images
            'profile_image' => 'nullable|string',
            'cover_banner' => 'nullable|string',
            'company_logo' => 'nullable|string',
            
            // Company Info
            'company_logo_text' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:255',
            'company_registration_no' => 'nullable|string|max:255',
            'company_department' => 'nullable|string|max:255',
            
            // Address Details
            'address_name' => 'nullable|string|max:255',
            'address_street' => 'nullable|string|max:255',
            'address_area' => 'nullable|string|max:255',
            'address_city_state' => 'nullable|string|max:255',
            'address_country' => 'nullable|string|max:255',
            'address_map_url' => 'nullable|string|max:500', // Changed from url to string to allow empty
            
            // JSON/Repeater fields
            'stats' => 'nullable|array',
            'services' => 'nullable|array',
            'team_members' => 'nullable|array',
            'education' => 'nullable|array',
            'certifications' => 'nullable|array',
            'expertise' => 'nullable|array',
            'awards' => 'nullable|array',
            'working_hours' => 'nullable|array',
            'service_features' => 'nullable|array',
            'service_tags' => 'nullable|array',
            
            // Contact Methods
            'phone_number' => 'nullable|string|max:50',
            'phone_label' => 'nullable|string|max:100',
            'email_address' => 'nullable|string|max:255', // Changed from email to string to allow empty
            'email_label' => 'nullable|string|max:100',
            'whatsapp_number' => 'nullable|string|max:50',
            'whatsapp_label' => 'nullable|string|max:100',
            'website_url' => 'nullable|string|max:500', // Changed from url to string to allow empty
            'website_label' => 'nullable|string|max:100',
            
            // Design Settings
            'profile_style' => 'nullable|string|max:50',
            'theme' => 'nullable|string|max:50',
            'background_color' => 'nullable|string|max:50',
            'text_color' => 'nullable|string|max:50',
            'accent_color' => 'nullable|string|max:50',
            'bg_gradient' => 'nullable|string',
            'card_style' => 'nullable|string|max:50',
            'bg_animation' => 'nullable|string|max:50',
            'card_effect_color' => 'nullable|string|max:50',
            'card_effect_opacity' => 'nullable|numeric|min:0|max:1',
            'font' => 'nullable|string|max:50',
            'button_style' => 'nullable|string|max:50',
            'color_scheme' => 'nullable|string|max:50',
            'layout' => 'nullable|string|max:50',
            'show_watermark' => 'nullable|boolean',
            
            // Design & Fields Configuration
            'design_config' => 'nullable|array',
            'visible_fields' => 'nullable|array',
            
            // Features
            'features' => 'nullable|array',
            'feature_order' => 'nullable|array',
            
            // Company additional
            'company_description' => 'nullable|string',
            'company_video' => ['nullable', 'string', 'max:500', \App\Support\VideoEmbed::rule()],
            'industry' => 'nullable|string|max:100',
            'established_year' => 'nullable', // Allow string or number
            'employee_count' => 'nullable', // Allow string or number
            'postal_code' => 'nullable|string|max:20',
            'coordinates' => 'nullable|string|max:100',
            'company_whatsapp' => 'nullable|string|max:50',
            
            // Services additional
            'service_name' => 'nullable|string|max:255',
            'service_category' => 'nullable|string|max:100',
            'service_image' => 'nullable|string|max:500',
            'service_video' => 'nullable|string|max:500',
            'service_description' => 'nullable|string',
            'service_price' => 'nullable|string|max:50',
            'service_old_price' => 'nullable|string|max:50',
            'service_duration' => 'nullable|string|max:100',
            'service_brochure' => 'nullable|string|max:500',
            'booking_enabled' => 'nullable|boolean',
            'booking_url' => 'nullable|string|max:500',
            'gallery' => 'nullable|array',
            
            // Portfolio
            'portfolio_title' => 'nullable|string|max:255',
            'portfolio_description' => 'nullable|string',
            'portfolio_category' => 'nullable|string|max:100',
            'portfolio_tags' => 'nullable|array',
            'portfolio_cover_image' => 'nullable|string|max:500',
            'portfolio_gallery' => 'nullable|array',
            'projects' => 'nullable|array',
            'project_url' => 'nullable|string|max:500',
            'date_completed' => 'nullable|string|max:50',
            'client_name' => 'nullable|string|max:255',
            'portfolio_location' => 'nullable|string|max:255',
            'skills_used' => 'nullable|array',
            'pdf_download' => 'nullable|string|max:500',
            
            // Blog
            'blog_enabled' => 'nullable|boolean',
            'blog_posts' => 'nullable|array',
            'blog_gallery' => 'nullable|array',
            'blog_title' => 'nullable|string|max:255',
            'blog_slug' => 'nullable|string|max:255',
            'blog_cover_image' => 'nullable|string|max:500',
            'blog_category' => 'nullable|string|max:100',
            'blog_tags' => 'nullable|array',
            'author_name' => 'nullable|string|max:255',
            'published_date' => 'nullable|string|max:50',
            'reading_time' => 'nullable|string|max:50',
            'blog_content' => 'nullable|string',
            'external_link' => 'nullable|string|max:500',
            'related_posts' => 'nullable|array',
            
            // Links additional
            'appointment_link' => 'nullable|string|max:500',
            'payment_button_text' => 'nullable|string|max:100',
            'payment_button_url' => 'nullable|string|max:500',
            
            // Links validation
            'links' => 'nullable|array',
            'links.*.id' => 'nullable|exists:social_links,id',
            'links.*.title' => 'required_with:links|string|max:255',
            'links.*.url' => 'required_with:links|string|max:500', // Changed from url to string
            'links.*.platform' => 'required_with:links|string|max:50',
            'links.*.is_active' => 'nullable|boolean',
            'links.*.order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            \Log::error('Landing page validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input_keys' => array_keys($request->all())
            ]);
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Update or create landing page
        $landingPageData = $request->except('links'); // Exclude links from direct save

        // Business members: company fields the owner locked are enforced here,
        // not only greyed out in the builder (a crafted request used to be able
        // to overwrite the company name, logo or branding).
        $tenant = app(\App\Services\BusinessTenantService::class);
        $editor = $request->user();
        if ($editor->isBusinessEmployee()) {
            if (!$tenant->memberHasAccess($editor)) {
                return response()->json([
                    'success' => false,
                    'code' => 'BUSINESS_ACCESS_INACTIVE',
                    'message' => 'Your company Business plan or your seat is not active. Contact your company administrator.',
                ], 403);
            }
            [$landingPageData, $lockedOverrides] = $tenant->applyLocks($editor, $landingPageData);
        }

        $landingPage = LandingPage::updateOrCreate(
            ['nfc_card_id' => $nfcCard->id],
            $landingPageData
        );

        // First save of a Business member's profile: fill what they left empty
        // with the company's shared values (locked ones were applied above).
        if ($landingPage->wasRecentlyCreated && $editor->isBusinessEmployee()) {
            $tenant->applyCompanyValues($landingPage);
        }

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
            // Fields kept at the company value because the Business owner locked them.
            'locked_overrides' => $lockedOverrides ?? [],
            'message' => 'Landing page saved successfully'
        ]);
    }
} 