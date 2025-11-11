<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
        
        // Store state with redirect URL for 10 minutes
        Cache::put("oauth_state_{$state}", [
            'provider' => $provider,
            'redirect_to' => $redirectTo,
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
            $user = $this->socialAuthService->handleCallback($provider);
            
            // Create a Sanctum token for the user
            $token = $user->createToken('oauth-token')->plainTextToken;

            // Redirect to frontend callback page with token and is_new_user flag
            $frontendUrl = config('services.frontend_url');
            $isNewUser = $user->is_new_user ? 'true' : 'false';
            
            return redirect($frontendUrl . '/auth/callback?token=' . $token . '&is_new_user=' . $isNewUser);
        } catch (\Exception $e) {
            \Log::error('Social auth error: ' . $e->getMessage(), [
                'provider' => $provider,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect(config('services.frontend_url') . '/auth/callback?error=auth_failed&message=' . urlencode($e->getMessage()));
        }
    }
}