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
        'company_logo',
        'theme',
        'background_color',
        'text_color',
        'font',
        'button_style',
        'show_watermark',
        'is_active',
    ];

    protected $casts = [
        'show_watermark' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the profile
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get Terms of Service document
     */
    public static function getTermsOfService()
    {
        return self::where('type', 'terms')->first();
    }

    /**
     * Get Privacy Policy document
     */
    public static function getPrivacyPolicy()
    {
        return self::where('type', 'privacy')->first();
    }
}
