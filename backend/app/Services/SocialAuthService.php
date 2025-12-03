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

        // For Google OAuth:
        // - 'select_account': Always show account picker
        // - This allows users to choose which Google account to use
        // - Google handles 2FA based on device trust and account settings
        // - Even on trusted devices, 2FA was verified during initial device authorization
        return Socialite::driver($provider)
            ->with(['prompt' => 'select_account'])
            ->stateless()
            ->redirect()
            ->getTargetUrl();
    }

    /**
     * Handle the callback from the provider
     */
    public function handleCallback(string $provider)
    {
        $providerUser = $this->getOAuthUser($provider);
        return $this->findOrCreateUser($provider, $providerUser);
    }

    /**
     * Get the OAuth user from the provider without creating/finding a user
     * Used for account linking where we just need the OAuth info
     */
    public function getOAuthUser(string $provider)
    {
        try {
            // Configure Guzzle options for SSL (Windows development fix)
            $guzzleOptions = [];
            if (app()->environment('local') && env('CURL_VERIFY_SSL', true) === false) {
                $guzzleOptions = ['verify' => false];
            }

            if ($provider === 'apple') {
                $clientSecret = $this->appleClientSecretService->generate();
                $socialite = Socialite::driver($provider)
                    ->setClientSecret($clientSecret)
                    ->stateless();
                
                if (!empty($guzzleOptions)) {
                    $socialite->setHttpClient(
                        new \GuzzleHttp\Client($guzzleOptions)
                    );
                }
                
                return $socialite->user();
            } else {
                $socialite = Socialite::driver($provider)->stateless();
                
                if (!empty($guzzleOptions)) {
                    $socialite->setHttpClient(
                        new \GuzzleHttp\Client($guzzleOptions)
                    );
                }
                
                return $socialite->user();
            }
        } catch (InvalidStateException $e) {
            throw new \Exception('Invalid state. Please try again.');
        } catch (\Exception $e) {
            throw new \Exception('Authentication failed: ' . $e->getMessage());
        }
    }

    /**
     * Find or create user from provider data
     * 
     * @throws \Exception if email exists with local password but no OAuth link
     */
    private function findOrCreateUser(string $provider, $providerUser): User
    {
        return DB::transaction(function () use ($provider, $providerUser) {
            // Try to find existing social identity
            $socialIdentity = SocialIdentity::where('provider', $provider)
                ->where('provider_id', $providerUser->getId())
                ->first();

            if ($socialIdentity) {
                // User has already linked this OAuth provider - allow login
                $socialIdentity->update([
                    'access_token' => $providerUser->token,
                    'refresh_token' => $providerUser->refreshToken,
                    'token_expires_at' => $providerUser->expiresIn 
                        ? now()->addSeconds($providerUser->expiresIn) 
                        : null,
                ]);

                $user = $socialIdentity->user;
                
                // BUGFIX: Mark existing user as NOT new
                // This ensures returning users are redirected to Dashboard, not Plan Selection
                if ($user->is_new_user === true) {
                    $user->update(['is_new_user' => false]);
                    
                    \Log::info('Existing OAuth user marked as not new', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'provider' => $provider,
                    ]);
                }

                return $user;
            }

            // Try to find user by email
            $email = $providerUser->getEmail();
            $user = null;

            if ($email) {
                $user = User::where('email', strtolower($email))->first();
            }

            // SECURITY CHECK: If user exists with local password but hasn't linked this OAuth provider,
            // block the OAuth login to prevent unauthorized access
            if ($user) {
                // Check if user has a local password (not OAuth-only)
                $hasLocalPassword = $user->hasLocalPassword();
                
                // Check if user has already linked ANY OAuth provider
                $hasAnyOAuth = $user->socialIdentities()->exists();
                
                // Check if this specific provider is already linked
                $hasThisProvider = $user->socialIdentities()
                    ->where('provider', $provider)
                    ->exists();

                // Block OAuth if: user has local password AND has NOT linked this OAuth provider
                // Users who signed up with password must explicitly link OAuth from settings
                if ($hasLocalPassword && !$hasThisProvider) {
                    $providerDisplay = ucfirst($provider);
                    
                    \Log::warning('OAuth login blocked for password-backed account', [
                        'user_id' => $user->id,
                        'email' => $email,
                        'provider' => $provider,
                        'has_local_password' => true,
                        'has_any_oauth' => $hasAnyOAuth,
                    ]);

                    throw new \Exception(
                        "OAUTH_BLOCKED:An account with this email already exists and uses a password. " .
                        "Please sign in with your email and password, or link {$providerDisplay} from your Account Settings after logging in."
                    );
                }
            }

            // Create new user if not found
            if (!$user) {
                $user = User::create([
                    'first_name' => $this->extractFirstName($providerUser),
                    'last_name' => $this->extractLastName($providerUser),
                    'email' => strtolower($email),
                    'email_verified_at' => now(), // Provider verified
                    'password' => null, // OAuth-only users have no local password
                    'provider' => $provider, // Mark primary OAuth provider
                    'is_new_user' => true,
                ]);
                
                \Log::info('New user created via OAuth', [
                    'user_id' => $user->id,
                    'email' => $email,
                    'provider' => $provider,
                ]);
            }

            // Create social identity link
            SocialIdentity::create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_id' => $providerUser->getId(),
                'email' => strtolower($email),
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