<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardDesign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'card_size',
        'bleed',
        'background_front_url',
        'background_back_url',
        'front_elements',
        'back_elements',
        'notes',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'bleed' => 'boolean',
        'is_active' => 'boolean',
        'front_elements' => 'array',
        'back_elements' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
