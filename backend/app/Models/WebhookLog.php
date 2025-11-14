<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'event_type',
        'event_id',
        'payload',
        'signature_verified',
        'signature',
        'status',
        'retry_count',
        'processed_at',
        'transaction_id',
        'subscription_id',
        'error_message',
        'error_trace',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'payload' => 'array',
        'error_trace' => 'array',
        'signature_verified' => 'boolean',
        'retry_count' => 'integer',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Check if webhook is processed
     */
    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    /**
     * Check if webhook failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if webhook is pending
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Mark as processed
     */
    public function markAsProcessed(): bool
    {
        return $this->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $error, array $trace = null): bool
    {
        return $this->update([
            'status' => 'failed',
            'error_message' => $error,
            'error_trace' => $trace,
            'processed_at' => now(),
        ]);
    }

    /**
     * Increment retry count
     */
    public function incrementRetry(): bool
    {
        return $this->increment('retry_count');
    }

    /**
     * Scope by provider
     */
    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope by event type
     */
    public function scopeByEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope verified webhooks
     */
    public function scopeVerified($query)
    {
        return $query->where('signature_verified', true);
    }

    /**
     * Scope unprocessed webhooks
     */
    public function scopeUnprocessed($query)
    {
        return $query->whereIn('status', ['pending', 'processing']);
    }

    /**
     * Scope failed webhooks
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
