<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CardTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'thumbnail_url',
        'front_image_url',
        'back_image_url',
        'original_front_url',
        'original_back_url',
        'category',
        'plan_types',
        'color_scheme',
        'is_active',
        'is_hidden',
        'sort_order',
        'processing_status',
        'processing_error',
        'created_by',
    ];

    protected $casts = [
        'plan_types' => 'array',
        'color_scheme' => 'array',
        'is_active' => 'boolean',
        'is_hidden' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name) . '-' . Str::random(6);
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_hidden', false);
    }

    public function scopeCompleted($query)
    {
        return $query->where('processing_status', 'completed');
    }

    public function scopeForPlan($query, $planType)
    {
        return $query->whereJsonContains('plan_types', $planType);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Check if template is available for a specific plan
    public function isAvailableForPlan($planType)
    {
        if (empty($this->plan_types)) {
            return true; // Available for all if not specified
        }
        return in_array($planType, $this->plan_types);
    }
}
