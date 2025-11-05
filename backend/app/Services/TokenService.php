<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TokenService
{
    /**
     * The secret key for HMAC hashing.
     *
     * @var string
     */
    protected string $secret;

    /**
     * Default token expiry in hours.
     *
     * @var int
     */
    protected int $defaultExpiryHours;

    public function __construct()
    {
        $this->secret = config('app.reset_token_secret') ?? config('app.key');
        $this->defaultExpiryHours = config('auth.passwords.users.expire', 60) / 60; // Convert minutes to hours
        
        if (!$this->secret) {
            throw new \RuntimeException('Reset token secret is not configured');
        }
    }

    /**
     * Generate a secure random token.
     *
     * @return string Raw token (96 characters hex)
     */
    public function generateToken(): string
    {
        return Str::random(96);
    }

    /**
     * Hash a token for secure storage.
     *
     * @param string $token Raw token
     * @return string Hashed token
     */
    public function hashToken(string $token): string
    {
        return hash_hmac('sha256', $token, $this->secret);
    }

    /**
     * Verify a token matches its hash using timing-safe comparison.
     *
     * @param string $token Raw token
     * @param string $hash Stored hash
     * @return bool
     */
    public function verifyToken(string $token, string $hash): bool
    {
        $computedHash = $this->hashToken($token);
        return hash_equals($hash, $computedHash);
    }

    /**
     * Get expiry datetime for a token.
     *
     * @param int|null $hours Hours until expiry (null = use default)
     * @return Carbon
     */
    public function getExpiryDate(?int $hours = null): Carbon
    {
        $hours = $hours ?? $this->defaultExpiryHours;
        return Carbon::now()->addHours($hours);
    }

    /**
     * Generate complete token data ready for storage.
     *
     * @param int|null $expiryHours Hours until expiry (null = use default)
     * @return array ['raw_token' => string, 'token_hash' => string, 'expires_at' => Carbon]
     */
    public function createTokenData(?int $expiryHours = null): array
    {
        $rawToken = $this->generateToken();
        
        return [
            'raw_token' => $rawToken,
            'token_hash' => $this->hashToken($rawToken),
            'expires_at' => $this->getExpiryDate($expiryHours),
        ];
    }

    /**
     * Check if a token has expired.
     *
     * @param Carbon $expiresAt
     * @return bool
     */
    public function isExpired(Carbon $expiresAt): bool
    {
        return $expiresAt->isPast();
    }

    /**
     * Get remaining time until expiry in minutes.
     *
     * @param Carbon $expiresAt
     * @return int
     */
    public function getRemainingMinutes(Carbon $expiresAt): int
    {
        return max(0, now()->diffInMinutes($expiresAt, false));
    }
}