<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-user email notification preferences (Settings → Notifications tab).
 */
class UserNotificationPreference extends Model
{
    /**
     * Default preferences applied when a user has never saved any.
     *
     * @var array<string, bool>
     */
    public const DEFAULTS = [
        'profile_views' => true,
        'nfc_taps' => true,
        'weekly_reports' => true,
        'product_updates' => false,
    ];

    protected $fillable = [
        'user_id',
        'profile_views',
        'nfc_taps',
        'weekly_reports',
        'product_updates',
    ];

    protected $casts = [
        'profile_views' => 'boolean',
        'nfc_taps' => 'boolean',
        'weekly_reports' => 'boolean',
        'product_updates' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
