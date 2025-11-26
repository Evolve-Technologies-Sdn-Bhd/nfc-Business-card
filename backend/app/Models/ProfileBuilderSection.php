<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileBuilderSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'icon',
        'category',
        'description',
        'display_order',
        'available_plans',
        'has_fields',
        'is_active',
    ];

    protected $casts = [
        'available_plans' => 'array',
        'has_fields' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Get the fields for this section
     */
    public function fields()
    {
        return $this->hasMany(ProfileBuilderField::class, 'tab', 'key');
    }

    /**
     * Scope for active sections
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for general category sections
     */
    public function scopeGeneral($query)
    {
        return $query->where('category', 'general');
    }

    /**
     * Scope for design category sections
     */
    public function scopeDesign($query)
    {
        return $query->where('category', 'design');
    }

    /**
     * Check if section is available for a specific plan
     */
    public function isAvailableForPlan($plan)
    {
        if (empty($this->available_plans)) {
            return true; // Available for all plans
        }
        return in_array($plan, $this->available_plans);
    }
}
