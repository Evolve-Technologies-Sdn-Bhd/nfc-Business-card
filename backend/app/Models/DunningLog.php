<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DunningLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'transaction_id',
        'user_id',
        'attempt_number',
        'action_taken',
        'status',
        'next_retry_at',
        'failure_reason',
        'notification_sent',
        'notification_type',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'notification_sent' => 'boolean',
        'next_retry_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the subscription
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the failed transaction
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if retry is due
     */
    public function isRetryDue(): bool
    {
        return $this->next_retry_at && $this->next_retry_at->isPast();
    }

    /**
     * Check if successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'succeeded';
    }

    /**
     * Check if failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Scope pending dunning logs
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope logs due for retry
     */
    public function scopeDueForRetry($query)
    {
        return $query->where('status', 'pending')
            ->where('next_retry_at', '<=', now());
    }

    /**
     * Scope by action
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action_taken', $action);
    }
}
