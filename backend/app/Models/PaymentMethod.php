<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'provider',
        'provider_payment_method_id',
        'card_brand',
        'card_last4',
        'card_exp_month',
        'card_exp_year',
        'card_fingerprint',
        'bank_name',
        'bank_account_last4',
        'ewallet_type',
        'ewallet_account_id',
        'is_default',
        'is_verified',
        'metadata',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_verified' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'provider_payment_method_id',
        'card_fingerprint',
    ];

    /**
     * Get the user that owns the payment method
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get transactions using this payment method
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get subscriptions using this payment method
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get display name for the payment method
     */
    public function getDisplayNameAttribute(): string
    {
        return match($this->type) {
            'card' => "{$this->card_brand} •••• {$this->card_last4}",
            'bank' => "{$this->bank_name} •••• {$this->bank_account_last4}",
            'ewallet' => ucfirst($this->ewallet_type),
            default => 'Payment Method',
        };
    }

    /**
     * Check if card is expired
     */
    public function isExpired(): bool
    {
        if ($this->type !== 'card' || !$this->card_exp_month || !$this->card_exp_year) {
            return false;
        }

        $expiryDate = \Carbon\Carbon::createFromDate(
            $this->card_exp_year,
            $this->card_exp_month,
            1
        )->endOfMonth();

        return $expiryDate->isPast();
    }

    /**
     * Scope to get default payment method
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to get verified payment methods
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
