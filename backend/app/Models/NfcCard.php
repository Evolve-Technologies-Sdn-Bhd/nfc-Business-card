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
        'nfc_card_id',
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
        'shipped_date',
        'delivered_date',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'expiry_date' => 'date',
        'shipped_date' => 'date',
        'delivered_date' => 'date',
        'purchase_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        // Generate unique card_id when creating
        static::creating(function ($nfcCard) {
            if (empty($nfcCard->card_id)) {
                $nfcCard->card_id = 'CARD-' . strtoupper(Str::random(8));
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

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByPlan($query, $plan)
    {
        return $query->where('subscription_plan', $plan);
    }

    public function isActive()
    {
        return $this->status === 'active';
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

    public function getFormattedPurchaseAmountAttribute()
    {
        return '$' . number_format($this->purchase_amount, 2);
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
     * Resolve route binding - supports 'nfc_card_id', phone number (contact_number), and numeric 'id'.
     * Lookup priority:
     *   1. Exact nfc_card_id (e.g. NFC-BY1CINMFLU7X) — backward compatible
     *   2. Contact number (phone number normalized) — new feature
     *   3. Numeric database id — backward compatible
     *
     * This allows URLs like:
     *   /nfc-cards/NFC-BY1CINMFLU7X/landing-page   (legacy)
     *   /nfc-cards/60123456789/landing-page          (new — phone number)
     *   /nfc-cards/5/landing-page                    (legacy numeric id)
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // Priority 1: exact nfc_card_id (e.g. NFC-XXXXXXXXXXXX) — never break this
        $card = $this->where('nfc_card_id', $value)->first();
        if ($card) {
            return $card;
        }

        // Priority 2: phone number lookup (only if value plausibly looks like a phone number)
        if (self::looksLikePhoneNumber($value)) {
            $card = self::findByPhoneNumber($value);
            if ($card) {
                return $card;
            }
        }

        // Priority 3: numeric database id (small integers — original cards)
        if (!$card && is_numeric($value)) {
            $card = $this->where('id', $value)->first();
        }

        return $card;
    }
} 