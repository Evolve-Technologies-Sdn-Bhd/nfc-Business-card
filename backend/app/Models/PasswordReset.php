<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PasswordReset extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'token_hash',
        'used',
        'expires_at',
        'used_at',
        'request_ip',
        'user_agent',
        'used_ip',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'used' => 'boolean',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Get the user that owns the password reset.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the token is valid (not used and not expired).
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return !$this->used && $this->expires_at->isFuture();
    }

    /**
     * Mark the token as used.
     *
     * @param string|null $ip
     * @return bool
     */
    public function markAsUsed(?string $ip = null): bool
    {
        $this->used = true;
        $this->used_at = now();
        $this->used_ip = $ip;
        
        return $this->save();
    }

    /**
     * Scope to only include valid tokens.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValid($query)
    {
        return $query->where('used', false)
                    ->where('expires_at', '>', now());
    }

    /**
     * Scope to only include expired tokens.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Clean up old password reset records.
     *
     * @return int Number of records deleted
     */
    public static function cleanupExpired(): int
    {
        $sevenDaysAgo = Carbon::now()->subDays(7);
        
        return self::where(function ($query) use ($sevenDaysAgo) {
            $query->where('expires_at', '<', now())
                  ->orWhere(function ($q) use ($sevenDaysAgo) {
                      $q->where('used', true)
                        ->where('used_at', '<', $sevenDaysAgo);
                  });
        })->delete();
    }

    /**
     * Invalidate all unused tokens for a user.
     *
     * @param int $userId
     * @return int Number of records updated
     */
    public static function invalidateUserTokens(int $userId): int
    {
        return self::where('user_id', $userId)
                   ->where('used', false)
                   ->update([
                       'used' => true,
                       'used_at' => now(),
                   ]);
    }
}
