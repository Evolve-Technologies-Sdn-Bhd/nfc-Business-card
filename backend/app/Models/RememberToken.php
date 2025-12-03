<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RememberToken extends Model
{
    protected $fillable = [
        'user_id',
        'token_hash',
        'expires_at',
        'last_used_at',
        'user_agent',
        'ip_address',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the user that owns the remember token
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new cryptographically secure remember token
     * 
     * @return string Plain text token (60 characters)
     */
    public static function generateToken(): string
    {
        return Str::random(60);
    }

    /**
     * Hash a plain text token using SHA-256
     * 
     * @param string $plainToken
     * @return string
     */
    public static function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    /**
     * Check if the token has expired
     * 
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Update the last used timestamp
     * 
     * @return void
     */
    public function updateLastUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Delete expired tokens for cleanup
     * 
     * @return int Number of deleted tokens
     */
    public static function deleteExpired(): int
    {
        return static::where('expires_at', '<', now())->delete();
    }

    /**
     * Find a valid token by its hash
     * 
     * @param string $tokenHash
     * @return RememberToken|null
     */
    public static function findValidToken(string $tokenHash): ?RememberToken
    {
        return static::where('token_hash', $tokenHash)
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Revoke all tokens for a specific user
     * 
     * @param int $userId
     * @return int Number of deleted tokens
     */
    public static function revokeAllForUser(int $userId): int
    {
        return static::where('user_id', $userId)->delete();
    }

    /**
     * Revoke a specific token
     * 
     * @param string $tokenHash
     * @return bool
     */
    public static function revokeToken(string $tokenHash): bool
    {
        return static::where('token_hash', $tokenHash)->delete() > 0;
    }
}
