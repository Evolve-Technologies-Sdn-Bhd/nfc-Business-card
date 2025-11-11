<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegalDocument extends Model
{
    protected $fillable = [
        'type',
        'content',
        'version',
        'effective_date',
        'updated_by',
    ];

    protected $casts = [
        'effective_date' => 'datetime',
    ];

    /**
     * Get the user who last updated this document
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get document by type
     */
    public static function getByType(string $type)
    {
        return self::where('type', $type)->first();
    }

    /**
     * Get Terms of Service
     */
    public static function getTermsOfService()
    {
        return self::getByType('terms_of_service');
    }

    /**
     * Get Privacy Policy
     */
    public static function getPrivacyPolicy()
    {
        return self::getByType('privacy_policy');
    }
}
