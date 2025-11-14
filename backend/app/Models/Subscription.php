<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'payment_method_id',
        'subscription_id',
        'provider',
        'provider_subscription_id',
        'plan_name',
        'plan_type',
        'amount',
        'currency',
        'interval',
        'status',
        'current_period_start',
        'current_period_end',
        'next_billing_date',
        'trial_ends_at',
        'cancelled_at',
        'cancellation_reason',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'next_billing_date' => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'provider_subscription_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscription) {
            if (empty($subscription->subscription_id)) {
                $subscription->subscription_id = 'SUB-' . strtoupper(Str::random(16));
            }
        });
    }

    /**
     * Get the user that owns the subscription
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payment method
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get transactions for this subscription
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get dunning logs
     */
    public function dunningLogs(): HasMany
    {
        return $this->hasMany(DunningLog::class);
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if subscription is past due
     */
    public function isPastDue(): bool
    {
        return $this->status === 'past_due';
    }

    /**
     * Check if subscription is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if in trial period
     */
    public function isInTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if subscription is due for renewal
     */
    public function isDueForRenewal(): bool
    {
        return $this->next_billing_date && 
               $this->next_billing_date->isPast() &&
               $this->status === 'active';
    }

    /**
     * Get days until next billing
     */
    public function getDaysUntilBillingAttribute(): ?int
    {
        if (!$this->next_billing_date) {
            return null;
        }

        return now()->diffInDays($this->next_billing_date, false);
    }

    /**
     * Cancel subscription
     */
    public function cancel(string $reason = null): bool
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        return true;
    }

    /**
     * Reactivate subscription
     */
    public function reactivate(): bool
    {
        $this->update([
            'status' => 'active',
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);

        return true;
    }

    /**
     * Suspend subscription
     */
    public function suspend(): bool
    {
        $this->update(['status' => 'suspended']);
        return true;
    }

    /**
     * Scope active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope past due subscriptions
     */
    public function scopePastDue($query)
    {
        return $query->where('status', 'past_due');
    }

    /**
     * Scope subscriptions due for billing
     */
    public function scopeDueForBilling($query)
    {
        return $query->where('status', 'active')
            ->where('next_billing_date', '<=', now());
    }

    /**
     * Scope by plan type
     */
    public function scopeByPlan($query, string $plan)
    {
        return $query->where('plan_type', $plan);
    }
}
