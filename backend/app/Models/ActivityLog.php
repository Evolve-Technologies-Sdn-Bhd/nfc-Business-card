<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_account_id',
        'action_type',
        'action_description',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the related business account
     */
    public function businessAccount()
    {
        return $this->belongsTo(User::class, 'business_account_id');
    }

    /**
     * Log an activity
     * 
     * @param User $user
     * @param string $actionType
     * @param string $description
     * @param array $options
     * @return ActivityLog
     */
    public static function logActivity(
        User $user,
        string $actionType,
        string $description,
        array $options = []
    ) {
        // Determine business account
        $businessAccountId = null;
        if ($user->parent_business_id) {
            // User is an employee
            $businessAccountId = $user->parent_business_id;
        } elseif ($user->isBusinessAccount()) {
            // User is business account
            $businessAccountId = $user->id;
        }

        return self::create([
            'user_id' => $user->id,
            'business_account_id' => $businessAccountId,
            'action_type' => $actionType,
            'action_description' => $description,
            'entity_type' => $options['entity_type'] ?? null,
            'entity_id' => $options['entity_id'] ?? null,
            'old_values' => $options['old_values'] ?? null,
            'new_values' => $options['new_values'] ?? null,
            'metadata' => $options['metadata'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get formatted change summary
     */
    public function getChangeSummaryAttribute()
    {
        if (!$this->old_values || !$this->new_values) {
            return null;
        }

        $changes = [];
        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changes[] = [
                    'field' => $key,
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    /**
     * Scope for business account activities
     * Includes both:
     * 1. Employee activities (business_account_id = admin ID)
     * 2. Admin's own activities (user_id = admin ID)
     */
    public function scopeForBusinessAccount($query, $businessAccountId)
    {
        return $query->where(function($q) use ($businessAccountId) {
            $q->where('business_account_id', $businessAccountId)
              ->orWhere('user_id', $businessAccountId);
        });
    }

    /**
     * Scope for specific action type
     */
    public function scopeActionType($query, $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
