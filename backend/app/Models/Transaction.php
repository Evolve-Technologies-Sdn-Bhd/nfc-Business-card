<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'payment_method_id',
        'type',
        'payment_rail',
        'provider',
        'provider_transaction_id',
        'amount',
        'currency',
        'fee',
        'net_amount',
        'status',
        'subscription_id',
        'is_recurring',
        'card_last4',
        'bank_name',
        'ewallet_type',
        'bank_reference_code',
        'payment_proof_url',
        'payment_proof_uploaded_at',
        'verified_by',
        'verified_at',
        'failure_code',
        'failure_message',
        'three_ds_status',
        'client_secret',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'is_recurring' => 'boolean',
        'metadata' => 'array',
        'payment_proof_uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'client_secret',
        'provider_transaction_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_id)) {
                $transaction->transaction_id = 'TXN-' . strtoupper(Str::random(16));
            }
        });
    }

    /**
     * Get the user that owns the transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payment method used
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the subscription (if recurring)
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the admin who verified the payment
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get refunds for this transaction
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Check if transaction is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'succeeded';
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Check if transaction is failed
     */
    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'cancelled']);
    }

    /**
     * Check if transaction requires action (3DS)
     */
    public function requiresAction(): bool
    {
        return $this->status === 'requires_action';
    }

    /**
     * Check if fully refunded
     */
    public function isFullyRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    /**
     * Get total refunded amount
     */
    public function getTotalRefundedAttribute(): float
    {
        return $this->refunds()
            ->where('status', 'succeeded')
            ->sum('amount');
    }

    /**
     * Get remaining refundable amount
     */
    public function getRefundableAmountAttribute(): float
    {
        return max(0, $this->amount - $this->total_refunded);
    }

    /**
     * Scope successful transactions
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'succeeded');
    }

    /**
     * Scope pending transactions
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'processing']);
    }

    /**
     * Scope by payment rail
     */
    public function scopeByRail($query, string $rail)
    {
        return $query->where('payment_rail', $rail);
    }

    /**
     * Scope recurring transactions
     */
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * Scope manual bank transfers awaiting verification
     */
    public function scopeAwaitingVerification($query)
    {
        return $query->where('payment_rail', 'manual_bank')
            ->where('status', 'pending')
            ->whereNotNull('payment_proof_url');
    }
}
