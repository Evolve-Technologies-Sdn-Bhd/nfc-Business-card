<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slug',
        'name',
        'title',
        'company',
        'bio',
        'email',
        'phone',
        'website',
        'location',
        'profile_image',
        'profile_image_path',
        'company_logo',
        'company_logo_path',
        'theme',
        'background_color',
        'text_color',
        'font',
        'button_style',
        'show_watermark',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_watermark' => 'boolean',
        'settings' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function socialLinks()
    {
        return $this->hasMany(SocialLink::class)->orderBy('order');
    }

    public function analytics()
    {
        return $this->morphMany(Analytics::class, 'trackable');
    }

    public function getProfileUrlAttribute()
    {
        return config('app.url') . '/Homepage/' . $this->slug;
    }
}
