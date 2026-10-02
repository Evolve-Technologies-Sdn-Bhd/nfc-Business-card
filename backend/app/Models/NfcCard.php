<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class NfcCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_account_id',
        'card_id',
        'card_number',
        'nfc_card_id',
        'transaction_id',
        'card_template_id',
        'slug',
        'card_owner',
        'billing_address',
        'contact_number',
        'purchase_date',
        'expiry_date',
        'status',
        'subscription_plan',
        'purchase_amount',
        'payment_method',
        'shipping_address',
        'tracking_number',
        'courier',
        'shipped_date',
        'delivered_date',
        'order_confirmed_at',
        'user_received_confirmed_at',
        'cancelled_by',
        'cancelled_reason',
        'is_custom_design',
        'custom_design_front_url',
        'custom_design_back_url',
        'design_notes',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'expiry_date' => 'date',
        'shipped_date' => 'date',
        'delivered_date' => 'date',
        'purchase_amount' => 'decimal:2',
        'card_number' => 'integer',
        'order_confirmed_at' => 'datetime',
        'user_received_confirmed_at' => 'datetime',
        'is_custom_design' => 'boolean',
    ];

    protected $appends = ['card_number', 'public_url', 'status_label', 'status_badge_class', 'can_access_profile_builder', 'timeline'];

    protected static function boot()
    {
        parent::boot();

        // Generate unique card_id and assign sequential card_number when creating
        static::creating(function ($nfcCard) {
            if (empty($nfcCard->attributes['card_id'])) {
                $nfcCard->card_id = 'CARD-' . strtoupper(Str::random(8));
            }
            if (empty($nfcCard->attributes['card_number']) && !empty($nfcCard->user_id)) {
                $max = (int) self::where('user_id', $nfcCard->user_id)
                    ->whereNotNull('card_number')
                    ->max('card_number');
                $nfcCard->attributes['card_number'] = $max + 1;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function businessAccount()
    {
        return $this->belongsTo(User::class, 'business_account_id');
    }

    public function analytics()
    {
        return $this->morphMany(Analytics::class, 'trackable');
    }

    public function nfcTag()
    {
        return $this->hasOne(NfcTag::class, 'nfc_card_id', 'nfc_card_id');
    }

    public function landingPage()
    {
        return $this->hasOne(LandingPage::class, 'nfc_card_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function cardTemplate()
    {
        return $this->belongsTo(CardTemplate::class, 'card_template_id');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'delivered', 'payment_verified', 'processing', 'shipped']);
    }

    public function scopeByPlan($query, $plan)
    {
        return $query->where('subscription_plan', $plan);
    }

    public function isActive()
    {
        return in_array($this->status, ['active', 'delivered']);
    }

    public function isPendingPayment()
    {
        return $this->status === 'pending_payment';
    }

    public function isAwaitingVerification()
    {
        return $this->status === 'awaiting_payment_verification';
    }

    public function isPaymentVerified()
    {
        return $this->status === 'payment_verified';
    }

    public function isProcessing()
    {
        return $this->status === 'processing';
    }

    public function isShipped()
    {
        return $this->status === 'shipped';
    }

    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function canAccessProfileBuilder()
    {
        return in_array($this->status, ['payment_verified', 'processing', 'shipped', 'delivered', 'active']);
    }

    public function getCanAccessProfileBuilderAttribute()
    {
        return $this->canAccessProfileBuilder();
    }

    public function canMarkAsProcessing()
    {
        return $this->status === 'payment_verified';
    }

    public function canMarkAsShipped()
    {
        return in_array($this->status, ['payment_verified', 'processing']);
    }

    public function canMarkAsDelivered()
    {
        return $this->status === 'shipped';
    }

    /**
     * User can press "I Have Received My Card" / "Activate Card" — accepts both statuses:
     * - shipped: normal flow user not yet confirmed
     * - delivered: admin override delivered case but not yet activated (not yet active)
     */
    public function canUserConfirmAndActivate()
    {
        return in_array($this->status, ['shipped', 'delivered']);
    }

    public function canCancel()
    {
        return !in_array($this->status, ['delivered', 'active', 'cancelled', 'expired']);
    }

    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->expiry_date) {
            return null;
        }
        return now()->diffInDays($this->expiry_date, false);
    }

    public function getStatusBadgeAttribute()
    {
        if ($this->isExpired()) {
            return 'expired';
        }
        return $this->status;
    }

    public static function statusLabels(): array
    {
        return [
            'pending_payment'                => 'Pending Payment',
            'awaiting_payment_verification'  => 'Awaiting Payment Verification',
            'payment_verified'               => 'Payment Verified',
            'processing'                     => 'Processing',
            'shipped'                        => 'Shipped',
            'delivered'                      => 'Delivered (Not Activated)',
            'active'                         => 'Active',
            'inactive'                       => 'Inactive',
            'expired'                        => 'Expired',
            'cancelled'                      => 'Cancelled',
            'replacement'                    => 'Replacement',
        ];
    }

    public static function statusBadgeClasses(): array
    {
        return [
            'pending_payment'                => 'bg-orange-100 text-orange-800',
            'awaiting_payment_verification'  => 'bg-yellow-100 text-yellow-800',
            'payment_verified'               => 'bg-emerald-100 text-emerald-800',
            'processing'                     => 'bg-purple-100 text-purple-800',
            'shipped'                        => 'bg-blue-100 text-blue-800',
            'delivered'                      => 'bg-green-100 text-green-800',
            'active'                         => 'bg-success-100 text-success-800',
            'inactive'                       => 'bg-secondary-100 text-secondary-800',
            'expired'                        => 'bg-warning-100 text-warning-800',
            'cancelled'                      => 'bg-red-100 text-red-800',
            'replacement'                    => 'bg-info-100 text-info-800',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        $labels = self::statusLabels();
        return $labels[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getStatusBadgeClassAttribute(): string
    {
        $classes = self::statusBadgeClasses();
        return $classes[$this->status] ?? 'bg-secondary-100 text-secondary-800';
    }

    public function getTimelineAttribute(): array
    {
        $createdAt = $this->created_at ? $this->created_at->toIso8601String() : null;
        $proofUploadedAt = $this->transaction && $this->transaction->payment_proof_uploaded_at
            ? $this->transaction->payment_proof_uploaded_at->toIso8601String()
            : null;
        $confirmedAt = $this->order_confirmed_at ? $this->order_confirmed_at->toIso8601String() : null;
        $shippedAt = $this->shipped_date ? (is_string($this->shipped_date) ? $this->shipped_date : $this->shipped_date->toIso8601String()) : null;
        $deliveredAt = $this->delivered_date ? (is_string($this->delivered_date) ? $this->delivered_date : $this->delivered_date->toIso8601String())
            : ($this->user_received_confirmed_at ? $this->user_received_confirmed_at->toIso8601String() : null);

        $statusOrder = [
            'pending_payment',
            'awaiting_payment_verification',
            'payment_verified',
            'processing',
            'shipped',
            'delivered',
            'active',
        ];

        $currentIndex = array_search($this->status, $statusOrder, true);
        if ($currentIndex === false) {
            $currentIndex = $this->isCancelled() ? -2 : -1;
        }

        $stepCompleted = function ($stepIndex) use ($currentIndex) {
            if ($this->isCancelled()) {
                return false;
            }
            return $currentIndex !== -1 && $stepIndex <= $currentIndex;
        };

        $stepCurrent = function ($stepIndex) use ($currentIndex) {
            return !$this->isCancelled() && $stepIndex === $currentIndex;
        };

        return [
            [
                'key'          => 'order_created',
                'label'        => 'Order Received',
                'description'  => 'Your order has been received and is awaiting payment',
                'date'         => $createdAt,
                'completed'    => true,
                'current'      => false,
            ],
            [
                'key'          => 'payment_uploaded',
                'label'        => 'Payment Proof Submitted',
                'description'  => 'Payment proof has been submitted for verification',
                'date'         => $proofUploadedAt,
                'completed'    => $stepCompleted(1),
                'current'      => $stepCurrent(1),
            ],
            [
                'key'          => 'payment_verified',
                'label'        => 'Payment Verified',
                'description'  => 'Admin has verified your payment. You can now start designing your profile!',
                'date'         => $confirmedAt,
                'completed'    => $stepCompleted(2),
                'current'      => $stepCurrent(2),
            ],
            [
                'key'          => 'processing',
                'label'        => 'Order Processing',
                'description'  => 'We are preparing your NFC card before shipment',
                'date'         => $this->status === 'processing' ? ($this->updated_at ? $this->updated_at->toIso8601String() : null) : ($this->isProcessing() || in_array($this->status, ['shipped','delivered','active']) ? $confirmedAt : null),
                'completed'    => $stepCompleted(3),
                'current'      => $stepCurrent(3),
            ],
            [
                'key'          => 'shipped',
                'label'        => 'Shipped',
                'description'  => $this->tracking_number
                    ? 'Card has been shipped. Tracking No: ' . $this->tracking_number . ' (' . ($this->courier ?? 'Courier') . '). Please press "I Have Received My Card" after physical delivery.'
                    : 'Card has been shipped. Please press "I Have Received My Card" after physical delivery.',
                'date'         => $shippedAt,
                'completed'    => $stepCompleted(4),
                'current'      => $stepCurrent(4),
            ],
            [
                'key'          => 'received_active',
                'label'        => 'Card Received & Active',
                'description'  => 'You have confirmed receipt of the physical card. Your subscription is now ACTIVE and the card is ready to use!',
                'date'         => $deliveredAt,
                'completed'    => $stepCompleted(5),
                'current'      => $stepCurrent(5),
            ],
        ];
    }

    public function getFormattedPurchaseAmountAttribute()
    {
        return 'RM' . number_format($this->purchase_amount, 2);
    }

    /**
     * Normalize a phone number by removing all non-digit characters.
     * Example: "+60 12-345 6789" -> "60123456789"
     *          "012-345 6789"   -> "0123456789"
     */
    public static function normalizePhoneNumber(?string $number): string
    {
        if (empty($number)) {
            return '';
        }
        return preg_replace('/\D/', '', $number);
    }

    /**
     * Generate common phone number variations for Malaysia (MY) lookup.
     * Returns an array of normalized strings to try matching against.
     */
    public static function generatePhoneVariations(string $normalized): array
    {
        $variations = [];
        $clean = $normalized;
        if (empty($clean)) {
            return $variations;
        }
        $variations[] = $clean;

        // MY format: add/remove "60" country code prefix
        if (str_starts_with($clean, '0') && strlen($clean) >= 9) {
            // e.g. 0123456789 -> 60123456789
            $variations[] = '60' . substr($clean, 1);
        }
        if (str_starts_with($clean, '60') && strlen($clean) >= 10) {
            // e.g. 60123456789 -> 0123456789
            $variations[] = '0' . substr($clean, 2);
        }
        if (str_starts_with($clean, '6') && !str_starts_with($clean, '60') && strlen($clean) >= 10) {
            // Unlikely, but handle 6+0 case
            $variations[] = '0' . substr($clean, 1);
        }

        return array_values(array_unique(array_filter($variations, fn($v) => !empty($v))));
    }

    /**
     * Look up an NfcCard by contact_number (with variations and conflict resolution).
     * Returns NfcCard|null, and logs a warning if multiple matches were found.
     */
    public static function findByPhoneNumber(string $value): ?self
    {
        $normalized = self::normalizePhoneNumber($value);
        if (empty($normalized)) {
            return null;
        }

        $variations = self::generatePhoneVariations($normalized);
        if (empty($variations)) {
            return null;
        }

        $matches = self::where(function ($q) use ($variations) {
                foreach ($variations as $v) {
                    $q->orWhereRaw("REGEXP_REPLACE(COALESCE(contact_number, ''), '[^0-9]', '') = ?", [$v]);
                }
            })
            ->with('landingPage')
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        if ($matches->count() === 0) {
            return null;
        }

        if ($matches->count() > 1) {
            $first = $matches->first();
            Log::warning('NfcCard::findByPhoneNumber — multiple cards matched same phone number', [
                'lookup_value' => $value,
                'normalized' => $normalized,
                'variations' => $variations,
                'match_ids' => $matches->pluck('id')->toArray(),
                'match_nfc_card_ids' => $matches->pluck('nfc_card_id')->toArray(),
                'selected_id' => $first->id,
                'selected_nfc_card_id' => $first->nfc_card_id,
            ]);
            return $first;
        }

        return $matches->first();
    }

    /**
     * Quick check: does the string look like a phone number?
     * Used to decide whether to trigger the (slightly more expensive) phone lookup path.
     */
    public static function looksLikePhoneNumber(string $value): bool
    {
        // Must be digits-only after normalization, length 9-15, and NOT a pure small numeric id (<= 6 digits)
        $normalized = self::normalizePhoneNumber($value);
        if (empty($normalized)) {
            return false;
        }
        if (strlen($normalized) < 9 || strlen($normalized) > 15) {
            return false;
        }
        return true;
    }

    /**
     * 1-based card index/number for this user (ordered by creation date).
     * Uses stored `card_number` column (populated at creation time) as primary source.
     * Falls back to counting query only for legacy rows without card_number set.
     */
    public function getCardNumberAttribute(): int
    {
        if (!empty($this->attributes['card_number'])) {
            return (int) $this->attributes['card_number'];
        }
        if (empty($this->user_id) || empty($this->created_at)) {
            return 0;
        }
        return (int) self::where('user_id', $this->user_id)
            ->where('created_at', '<', $this->created_at)
            ->count() + 1;
    }

    /**
     * The preferred public-facing profile URL for this card.
     * Uses pre-loaded user relation (from eager-load) whenever possible to avoid N+1.
     *   Priority 1 (pretty, short):  /profile/{name_slug}/{cardNumber}
     *       — used when this specific name_slug is UNIQUE among users (no name clash).
     *   Priority 2 (pretty, long):   /profile/{name_slug}/{cardNumber}/userid-{userPk}
     *       — used when this name_slug clashes with 2+ registered users; disambiguate
     *         using the authoritative user PRIMARY KEY suffix exactly as user requested.
     *   Priority 3 (fallback):       /profile/{nfc_card_id}
     *   Priority 4 (last fallback):  /profile/{id}
     */
    public function getPublicUrlAttribute(): string
    {
        $user = null;
        if ($this->relationLoaded('user')) {
            $user = $this->getRelation('user');
        }
        if (!$user && $this->user_id) {
            try {
                $user = User::find($this->user_id);
            } catch (\Throwable) {
                $user = null;
            }
        }
        if ($user && !empty($user->name_slug)) {
            $number = $this->card_number;
            $base = '/profile/' . $user->name_slug . '/' . $number;
            $hasClash = $user->relationLoaded('nameSlugClashCount')
                ? ($user->getRelation('nameSlugClashCount') > 1)
                : (!empty($user->id) ? $user->has_name_slug_clash : false);
            if ($hasClash && !empty($user->id)) {
                return $base . '/userid-' . $user->id;
            }
            return $base;
        }
        if (!empty($this->nfc_card_id)) {
            return '/profile/' . $this->nfc_card_id;
        }
        return '/profile/' . $this->id;
    }

    /**
     * Resolve an NfcCard from the pretty URL tuple:
     *   SHORT form (2 segments, unique name):  (name_slug, cardNumber)
     *   LONG  form (3 segments, clash name):   (name_slug, cardNumber, userId)
     *
     * @param  string     $userNameSlug
     * @param  string|int $cardIdentifier   Card number (1-based) or nfc_card_id / PK
     * @param  int|null   $userId           Exact user PK disambiguation for long URLs.
     */
    public static function resolveByPrettyUrl(string $userNameSlug, $cardIdentifier, ?int $userId = null): ?self
    {
        if ($userId !== null) {
            // LONG form — pin-point user by PRIMARY KEY, then get their Nth card.
            $user = User::find($userId);
            if (!$user || $user->name_slug !== $userNameSlug) {
                // Name slug must match what we have for this user (to prevent URL tampering),
                // or fall back to lookup without user constraint as last resort.
                $user = null;
            }
            if (!$user) {
                return null;
            }
        } else {
            // SHORT form — pick the OLDEST matching user with this slug (sorted by id asc).
            $user = User::where('name_slug', $userNameSlug)
                ->orderBy('id', 'asc')
                ->first();
            if (!$user) {
                return null;
            }
        }

        $allCards = self::where('user_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        if (is_numeric($cardIdentifier) && preg_match('/^\d{1,5}$/', (string) $cardIdentifier)) {
            $index = (int) $cardIdentifier - 1;
            if ($index >= 0 && $index < $allCards->count()) {
                return $allCards->get($index);
            }
        }

        $byRef = $allCards->firstWhere('nfc_card_id', $cardIdentifier)
            ?? $allCards->firstWhere('id', $cardIdentifier);
        return $byRef instanceof self ? $byRef : null;
    }

    /**
     * Resolve route binding.
     *
     * Nuxt/Vue cannot pass real "/" inside a single route param `[id]`, so the frontend
     * encodes the pretty URL path using "__" (double underscore) as the path separator.
     * Examples:
     *   2-segment short URL: /profile/nicole-tan/1           → encode → nicole-tan__1
     *   3-segment long URL:  /profile/nicole-tan/1/userid-87 → encode → nicole-tan__1__userid-87
     *
     * Decode priority (P0 first, stop on match):
     *   P0. Parse "__" segments: 2 or 3 parts → resolveByPrettyUrl
     *   P1. Exact nfc_card_id (legacy NFC-XXXXXXXXXXXX)
     *   P2. Phone number (fallback link-by-phone)
     *   P3. Numeric PK id
     *   P4. Single segment looks like a slug → assume card #1 of oldest matching user
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // P0 — pretty URL encoded with "__"
        if (is_string($value) && strpos($value, '__') !== false) {
            $parts = explode('__', $value);
            if (count($parts) === 2) {
                [$slugPart, $cardPart] = $parts;
                $card = self::resolveByPrettyUrl($slugPart, $cardPart, null);
                if ($card) return $card;
            }
            if (count($parts) === 3) {
                [$slugPart, $cardPart, $userSegment] = $parts;
                $uid = null;
                if (preg_match('/^userid-(\d+)$/i', (string) $userSegment, $m)) {
                    $uid = (int) $m[1];
                }
                if ($uid !== null) {
                    $card = self::resolveByPrettyUrl($slugPart, $cardPart, $uid);
                    if ($card) return $card;
                }
            }
        }

        // P1 — exact legacy nfc_card_id
        $card = $this->where('nfc_card_id', $value)->first();
        if ($card) return $card;

        // P2 — phone number link-by-phone
        if (self::looksLikePhoneNumber($value)) {
            $card = self::findByPhoneNumber($value);
            if ($card) return $card;
        }

        // P3 — numeric PK
        if (is_numeric($value)) {
            $card = $this->where('id', $value)->first();
            if ($card) return $card;
        }

        // P4 — single-segment name slug (looks like slug string, default card #1 oldest user)
        if (is_string($value) && strlen($value) >= 2 && !ctype_digit($value) && (strpos($value, '-') !== false || strpos($value, '_') !== false)) {
            $card = self::resolveByPrettyUrl($value, 1, null);
            if ($card) return $card;
        }

        return null;
    }
} 