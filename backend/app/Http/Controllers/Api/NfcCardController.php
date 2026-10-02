<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NfcCard;
use App\Models\NfcTag;
use App\Models\LandingPage;
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

        $plan = $user->subscription_plan;
        $subActive = (bool) $user->subscription_active;
        $hasActiveAccess = in_array($plan, ['basic', 'premium', 'business'], true) && $subActive;
        if (!$hasActiveAccess) {
            $hasActiveAccess = $user->nfcCards()
                ->whereIn('status', ['payment_verified','processing','shipped','delivered','active','pending_payment','awaiting_payment_verification'])
                ->exists();
        }

        if (!$hasActiveAccess) {
            \Log::warning('User does not have active card access', ['user_id' => $user?->id]);
            return response()->json([
                'success' => false,
                'message' => 'This feature requires an active Premium subscription or NFC card order',
                'upgrade_required' => true
            ], 403);
        }

        $eagerUser = 'user:id,first_name,last_name,email,job_title,name_slug';

        // If user is Business Admin, get all cards under the business account
        if ($user->isBusinessAccount()) {
            // Get cards for business admin and all employees
            $nfcCards = NfcCard::where('business_account_id', $user->id)
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
        $isCustomDesign = (bool) $request->input('is_custom_design', false);

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
        // Check ownership
        if ($nfcCard->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
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

        // Update card status only if not already active/delivered
        if (!$nfcCard->isActive()) {
            $nfcCard->update(['status' => 'active']);
        }

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
        
        $landingPage = $nfcCard->landingPage;

        // Auto-create landing page if it doesn't exist (for all plan types)
        if (!$landingPage) {
            $landingPage = LandingPage::create([
                'nfc_card_id' => $nfcCard->id,
                'name' => $nfcCard->user->name ?? '',
                'email' => $nfcCard->user->email ?? '',
            ]);
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
            // Basic Info
            'name' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'pronouns' => 'nullable|string|max:50',
            'qualification' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
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
            'company_video' => 'nullable|string|max:500',
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