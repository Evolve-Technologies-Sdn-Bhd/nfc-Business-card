<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileBuilderField extends Model
{
    protected $fillable = [
        'tab',
        'field_key',
        'field_type',
        'label',
        'placeholder',
        'help_text',
        'is_required',
        'is_visible',
        'validation_rules',
        'available_plans',
        'display_order',
        'config',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_visible' => 'boolean',
        'validation_rules' => 'array',
        'available_plans' => 'array',
        'display_order' => 'integer',
        'config' => 'array',
    ];

    // Scope to get fields by tab
    public function scopeByTab($query, $tab)
    {
        return $query->where('tab', $tab);
    }

    // Scope to get visible fields
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    // Scope to filter by plan
    public function scopeForPlan($query, $plan)
    {
        return $query->where(function ($q) use ($plan) {
            $q->whereJsonContains('available_plans', $plan)
              ->orWhereNull('available_plans')
              ->orWhereJsonLength('available_plans', 0);
        });
    }
}
