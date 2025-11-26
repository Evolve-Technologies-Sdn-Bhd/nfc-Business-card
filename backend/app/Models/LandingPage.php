<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LandingPage extends Model
{
    protected $fillable = [
        'nfc_card_id',
        // Basic Info
        'name',
        'title',
        'qualification',
        'bio',
        'phone',
        'email',
        'website',
        'address',
        'profile_image',
        'profile_image_path',
        'company_logo',
        'company_logo_path',
        'location',
        // Company Info
        'company_logo_text',
        'company_name',
        'company_registration_no',
        'company_department',
        // Address Details
        'address_name',
        'address_street',
        'address_area',
        'address_city_state',
        'address_country',
        'address_map_url',
        // JSON/Repeater fields
        'stats',
        'services',
        'social_links',
        'team_members',
        'education',
        'certifications',
        'expertise',
        'awards',
        'working_hours',
        'service_features',
        'service_tags',
        // New text fields
        'pronouns',
        'tagline',
        'cover_banner',
        'cover_banner_path',
        // Contact Methods
        'phone_number',
        'phone_label',
        'email_address',
        'email_label',
        'whatsapp_number',
        'whatsapp_label',
        'website_url',
        'website_label',
        // Design Settings
        'profile_style',
        'theme',
        'background_color',
        'text_color',
        'font',
        'button_style',
        'color_scheme',
        'layout',
        'show_watermark',
        // Design & Fields Configuration
        'design_config',
        'visible_fields',
        // Features
        'features',
        'feature_order',
        // Company additional
        'company_description',
        'company_video',
        'industry',
        'established_year',
        'employee_count',
        'postal_code',
        'coordinates',
        'company_whatsapp',
        // Services additional
        'service_name',
        'service_category',
        'service_image',
        'service_video',
        'service_description',
        'service_price',
        'service_old_price',
        'service_duration',
        'service_brochure',
        'booking_enabled',
        'booking_url',
        'gallery',
        // Portfolio
        'portfolio_title',
        'portfolio_description',
        'portfolio_category',
        'portfolio_tags',
        'portfolio_cover_image',
        'portfolio_gallery',
        'projects',
        'project_url',
        'date_completed',
        'client_name',
        'portfolio_location',
        'skills_used',
        'pdf_download',
        // Blog
        'blog_enabled',
        'blog_posts',
        'blog_gallery',
        'blog_title',
        'blog_slug',
        'blog_cover_image',
        'blog_category',
        'blog_tags',
        'author_name',
        'published_date',
        'reading_time',
        'blog_content',
        'external_link',
        'related_posts',
        // Links additional
        'appointment_link',
        'payment_button_text',
        'payment_button_url',
        // Status
        'is_active',
        'settings',
    ];

    protected $casts = [
        'stats' => 'array',
        'services' => 'array',
        'social_links' => 'array',
        'team_members' => 'array',
        'education' => 'array',
        'certifications' => 'array',
        'expertise' => 'array',
        'awards' => 'array',
        'working_hours' => 'array',
        'service_features' => 'array',
        'service_tags' => 'array',
        'settings' => 'array',
        'design_config' => 'array',
        'visible_fields' => 'array',
        'features' => 'array',
        'feature_order' => 'array',
        'projects' => 'array',
        'portfolio_gallery' => 'array',
        'portfolio_tags' => 'array',
        'skills_used' => 'array',
        'blog_enabled' => 'boolean',
        'blog_posts' => 'array',
        'blog_gallery' => 'array',
        'blog_tags' => 'array',
        'related_posts' => 'array',
        'gallery' => 'array',
        'booking_enabled' => 'boolean',
        'show_watermark' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the NFC card that owns this landing page
     */
    public function nfcCard()
    {
        return $this->belongsTo(NfcCard::class, 'nfc_card_id');
    }

    /**
     * Get the social links for this landing page
     */
    public function socialLinks()
    {
        return $this->hasMany(SocialLink::class, 'landing_page_id');
    }

    /**
     * Get analytics for this landing page
     */
    public function analytics()
    {
        return $this->morphMany(Analytics::class, 'trackable');
    }

    /**
     * Get the user through the NFC card
     */
    public function user()
    {
        return $this->hasOneThrough(User::class, NfcCard::class, 'id', 'id', 'nfc_card_id', 'user_id');
    }
}
