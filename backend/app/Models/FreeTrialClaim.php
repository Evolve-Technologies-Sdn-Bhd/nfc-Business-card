<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A single, permanent record that a free trial was claimed.
 *
 * Rows are intentionally decoupled from the User model (nullable user_id, no
 * cascade delete) so the "one trial per person" rule survives account deletion.
 */
class FreeTrialClaim extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'email_normalized',
        'email_hash',
        'ip_address',
        'ip_subnet',
        'device_id',
        'user_agent',
        'claimed_at',
    ];

    protected $casts = [
        'claimed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
