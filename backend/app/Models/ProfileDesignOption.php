<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileDesignOption extends Model
{
    protected $fillable = [
        'type',
        'option_id',
        'name',
        'config',
        'is_active',
        'is_default',
        'available_plans',
        'display_order',
        'description',
    ];

    protected $casts = [
        'config' => 'array',
        'available_plans' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Scope to get only active options
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get options by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get default option
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to order by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    /**
     * Scope to get options available for a specific plan
     */
    public function scopeForPlan($query, $plan)
    {
        return $query->where(function($q) use ($plan) {
            $q->whereNull('available_plans')
              ->orWhereJsonContains('available_plans', $plan);
        });
    }

    /**
     * Check if option is available for a specific plan
     */
    public function isAvailableForPlan($plan)
    {
        if (empty($this->available_plans)) {
            return true; // null means available for all plans
        }
        return in_array($plan, $this->available_plans);
    }
}
