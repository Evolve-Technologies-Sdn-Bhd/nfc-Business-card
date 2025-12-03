<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\SocialAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    public function __construct(
        private SocialAuthService $socialAuthService
    ) {}

    /**
     * Redirect to provider
     */
    public function redirect(Request $request, string $provider)
    {
        // Validate provider
        if (!in_array($provider, ['google', 'apple'])) {
            return response()->json(['error' => 'Invalid provider'], 400);
        }

        // Generate and store state for CSRF protection
        $state = Str::random(40);
        $redirectTo = $request->query('redirect_to', '/dashboard');
        $linkToken = $request->query('link_token');
        
        // If there's a link token, validate it
        $linkData = null;
        if ($linkToken) {
            $linkData = Cache::get("link_account:{$linkToken}");
            if (!$linkData || $linkData['provider'] !== $provider) {
                return redirect(config('services.frontend_url') . '/UserDashboard/Settings?error=invalid_link_token');
            }
        }
        
        // Store state with redirect URL and link token data for 10 minutes
        Cache::put("oauth_state_{$state}", [
            'provider' => $provider,
            'redirect_to' => $redirectTo,
            'link_token' => $linkToken,
            'link_data' => $linkData,
        ], 600);

        try {
            $redirectUrl = $this->socialAuthService->getRedirectUrl($provider);
            
            // Append state to URL
            $separator = str_contains($redirectUrl, '?') ? '&' : '?';
            $redirectUrl .= $separator . 'state=' . $state;
            
            // Return actual HTTP redirect instead of JSON
            // This allows direct browser navigation to work properly
            return redirect()->away($redirectUrl);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to initialize authentication: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle provider callback
     */
    public function callback(Request $request, string $provider)
    {
        $state = $request->query('state');
        $code = $request->query('code');

        // Verify state
        $stateData = Cache::pull("oauth_state_{$state}");
        
        if (!$stateData || $stateData['provider'] !== $provider) {
            return redirect(config('services.frontend_url') . '/auth/callback?error=invalid_state');
        }

        if (!$code) {
            return redirect(config('services.frontend_url') . '/auth/callback?error=no_code');
        }

        try {
            // Check if this is a link operation
            $isLinkOperation = isset($stateData['link_data']) && $stateData['link_data'] !== null;
            
            if ($isLinkOperation) {
                // Handle account linking
                return $this->handleLinkCallback($request, $provider, $stateData);
            }
            
            // Normal login/registration flow
            $user = $this->socialAuthService->handleCallback($provider);
            
            // Log login activity for OAuth login
            ActivityLog::logActivity(
                $user,
                'login',
                'User logged in via ' . ucfirst($provider) . ' OAuth',
                [
                    'metadata' => [
                        'ip_address' => $request->ip(),
                        'provider' => $provider,
                        'user_agent' => $request->userAgent(),
                    ]
                ]
            );
            
            // Create a Sanctum token for the user
            $token = $user->createToken('oauth-token')->plainTextToken;

            // Redirect to frontend callback page with token and is_new_user flag
            $frontendUrl = config('services.frontend_url');
            $isNewUser = $user->is_new_user ? 'true' : 'false';
            
            return redirect($frontendUrl . '/auth/callback?token=' . $token . '&is_new_user=' . $isNewUser);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            
            // Check if this is an OAuth blocked error (password-backed account)
            if (str_starts_with($errorMessage, 'OAUTH_BLOCKED:')) {
                $cleanMessage = str_replace('OAUTH_BLOCKED:', '', $errorMessage);
                
                \Log::info('OAuth login blocked - redirecting to login with message', [
                    'provider' => $provider,
                    'message' => $cleanMessage,
                ]);
                
                // Redirect to login page with oauth_blocked error type
                return redirect(config('services.frontend_url') . '/UserAccount/login?error=oauth_blocked&message=' . urlencode($cleanMessage));
            }
            
            \Log::error('Social auth error: ' . $errorMessage, [
                'provider' => $provider,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect(config('services.frontend_url') . '/auth/callback?error=auth_failed&message=' . urlencode($errorMessage));
        }
    }

    /**
     * Handle OAuth callback for account linking (not login)
     */
    private function handleLinkCallback(Request $request, string $provider, array $stateData)
    {
        $linkData = $stateData['link_data'];
        $linkToken = $stateData['link_token'];
        $frontendUrl = config('services.frontend_url');

        try {
            // Get the OAuth user info without creating/finding a user
            $oauthUser = $this->socialAuthService->getOAuthUser($provider);
            
            // Verify the email matches the authenticated user who initiated the link
            $user = \App\Models\User::find($linkData['user_id']);
            
            if (!$user) {
                return redirect($frontendUrl . '/UserDashboard/Settings?link_error=' . urlencode('User not found. Please try again.'));
            }

            // Delete the link token from cache
            Cache::forget("link_account:{$linkToken}");

            // Check if this OAuth account is already linked to another user
            $existingIdentity = \App\Models\SocialIdentity::where('provider', $provider)
                ->where('provider_id', $oauthUser->getId())
                ->first();

            if ($existingIdentity && $existingIdentity->user_id !== $user->id) {
                return redirect($frontendUrl . '/UserDashboard/Settings?link_error=' . urlencode('This ' . ucfirst($provider) . ' account is already linked to another user.'));
            }

            // Check if user already has this provider linked
            if ($user->socialIdentities()->where('provider', $provider)->exists()) {
                return redirect($frontendUrl . '/UserDashboard/Settings?link_success=' . urlencode(ucfirst($provider) . ' is already linked to your account.'));
            }

            // Create the social identity link
            $user->socialIdentities()->create([
                'provider' => $provider,
                'provider_id' => $oauthUser->getId(),
                'email' => $oauthUser->getEmail(),
                'name' => $oauthUser->getName(),
                'avatar' => $oauthUser->getAvatar(),
                'access_token' => $oauthUser->token,
                'refresh_token' => $oauthUser->refreshToken ?? null,
                'token_expires_at' => $oauthUser->expiresIn ? now()->addSeconds($oauthUser->expiresIn) : null,
            ]);

            // Log the link activity
            ActivityLog::logActivity(
                $user,
                'account_linked',
                'User linked ' . ucfirst($provider) . ' OAuth account',
                [
                    'metadata' => [
                        'ip_address' => $request->ip(),
                        'provider' => $provider,
                        'user_agent' => $request->userAgent(),
                    ]
                ]
            );

            \Log::info('OAuth account linked successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'provider' => $provider,
            ]);

            return redirect($frontendUrl . '/UserDashboard/Settings?link_success=' . urlencode(ucfirst($provider) . ' account linked successfully!'));
        } catch (\Exception $e) {
            \Log::error('OAuth link error: ' . $e->getMessage(), [
                'provider' => $provider,
                'link_data' => $linkData,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect($frontendUrl . '/UserDashboard/Settings?link_error=' . urlencode('Failed to link account: ' . $e->getMessage()));
        }
    }
}