<?php

namespace App\Services;

use App\Models\User;
use App\Models\SocialIdentity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class SocialAuthService
{
    public function __construct(
        private AppleClientSecretService $appleClientSecretService
    ) {}

    /**
     * Get the redirect URL for the provider
     */
    public function getRedirectUrl(string $provider): string
    {
        if ($provider === 'apple') {
            // Apple requires a dynamically generated client secret
            $clientSecret = $this->appleClientSecretService->generate();
            
            return Socialite::driver($provider)
                ->setClientSecret($clientSecret)
                ->stateless()
                ->redirect()
                ->getTargetUrl();
        }

        return Socialite::driver($provider)
            ->stateless()
            ->redirect()
            ->getTargetUrl();
    }

    /**
     * Handle the callback from the provider
     */
    public function handleCallback(string $provider)
    {
        try {
            if ($provider === 'apple') {
                $clientSecret = $this->appleClientSecretService->generate();
                $providerUser = Socialite::driver($provider)
                    ->setClientSecret($clientSecret)
                    ->stateless()
                    ->user();
            } else {
                $providerUser = Socialite::driver($provider)
                    ->stateless()
                    ->user();
            }

            return $this->findOrCreateUser($provider, $providerUser);
        } catch (InvalidStateException $e) {
            throw new \Exception('Invalid state. Please try again.');
        } catch (\Exception $e) {
            throw new \Exception('Authentication failed: ' . $e->getMessage());
        }
    }

    /**
     * Find or create user from provider data
     */
    private function findOrCreateUser(string $provider, $providerUser): User
    {
        return DB::transaction(function () use ($provider, $providerUser) {
            // Try to find existing social identity
            $socialIdentity = SocialIdentity::where('provider', $provider)
                ->where('provider_id', $providerUser->getId())
                ->first();

            if ($socialIdentity) {
                // Update tokens
                $socialIdentity->update([
                    'access_token' => $providerUser->token,
                    'refresh_token' => $providerUser->refreshToken,
                    'token_expires_at' => $providerUser->expiresIn 
                        ? now()->addSeconds($providerUser->expiresIn) 
                        : null,
                ]);

                return $socialIdentity->user;
            }

            // Try to find user by email
            $email = $providerUser->getEmail();
            $user = null;

            if ($email) {
                $user = User::where('email', $email)->first();
            }

            // Create new user if not found
            if (!$user) {
                $user = User::create([
                    'first_name' => $this->extractFirstName($providerUser),
                    'last_name' => $this->extractLastName($providerUser),
                    'email' => $email,
                    'email_verified_at' => now(), // Provider verified
                    'password' => Hash::make(Str::random(32)), // Random password
                ]);
            }

            // Create social identity
            SocialIdentity::create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_id' => $providerUser->getId(),
                'email' => $email,
                'access_token' => $providerUser->token,
                'refresh_token' => $providerUser->refreshToken,
                'token_expires_at' => $providerUser->expiresIn 
                    ? now()->addSeconds($providerUser->expiresIn) 
                    : null,
            ]);

            return $user;
        });
    }

    private function extractFirstName($providerUser): string
    {
        // Try different provider formats
        if (isset($providerUser->user['given_name'])) {
            return $providerUser->user['given_name'];
        }
        
        if (isset($providerUser->user['name']['firstName'])) {
            return $providerUser->user['name']['firstName'];
        }

        $name = $providerUser->getName() ?? $providerUser->getNickname() ?? 'User';
        $parts = explode(' ', $name);
        return $parts[0];
    }

    private function extractLastName($providerUser): string
    {
        // Try different provider formats
        if (isset($providerUser->user['family_name'])) {
            return $providerUser->user['family_name'];
        }
        
        if (isset($providerUser->user['name']['lastName'])) {
            return $providerUser->user['name']['lastName'];
        }

        $name = $providerUser->getName() ?? '';
        $parts = explode(' ', $name);
        return count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
    }
}