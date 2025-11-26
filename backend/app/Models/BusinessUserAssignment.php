<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessUserAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'enabled_fields',
        'enabled_design_options',
        'enabled_features',
        'use_custom',
        'notes',
    ];

    protected $casts = [
        'enabled_fields' => 'array',
        'enabled_design_options' => 'array',
        'enabled_features' => 'array',
        'use_custom' => 'boolean',
    ];

    /**
     * Get the user that owns this assignment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create assignment for a user.
     */
    public static function getOrCreateForUser($userId)
    {
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'use_custom' => false,
                'enabled_fields' => [],
                'enabled_design_options' => [],
                'enabled_features' => [],
            ]
        );
    }

    /**
     * Check if a field is enabled for this user.
     */
    public function isFieldEnabled($fieldId)
    {
        if (!$this->use_custom) {
            return null; // Use default plan settings
        }
        return in_array($fieldId, $this->enabled_fields ?? []);
    }

    /**
     * Check if a design option is enabled for this user.
     */
    public function isDesignOptionEnabled($optionId)
    {
        if (!$this->use_custom) {
            return null; // Use default plan settings
        }
        return in_array($optionId, $this->enabled_design_options ?? []);
    }

    /**
     * Check if a feature is enabled for this user.
     */
    public function isFeatureEnabled($featureKey)
    {
        if (!$this->use_custom) {
            return null; // Use default plan settings
        }
        return in_array($featureKey, $this->enabled_features ?? []);
    }
}
