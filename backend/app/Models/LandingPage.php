<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'company_logo',
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
        // JSON fields
        'stats',
        'services',
        'social_links',
        'team_members',
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
        'show_watermark',
    ];

    protected $casts = [
        'stats' => 'array',
        'services' => 'array',
        'social_links' => 'array',
        'team_members' => 'array',
        'show_watermark' => 'boolean',
    ];

    /**
     * Get the NFC card that owns this landing page
     */
    public function nfcCard()
    {
        return $this->belongsTo(NfcCard::class, 'nfc_card_id');
    }
}
