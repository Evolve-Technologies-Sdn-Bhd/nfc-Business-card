<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
} 