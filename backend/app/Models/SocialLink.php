<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'platform',
        'url',
        'title',
        'order',
        'is_active',
        'click_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'click_count' => 'integer',
        'order' => 'integer',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function analytics()
    {
        return $this->morphMany(Analytics::class, 'trackable');
    }

    public function incrementClickCount()
    {
        $this->increment('click_count');
    }
}
