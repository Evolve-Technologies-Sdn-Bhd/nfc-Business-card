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
            
            // Directly redirect to OAuth provider
            return redirect($redirectUrl);
        } catch (\Exception $e) {
            \Log::error('OAuth redirect error: ' . $e->getMessage());
            return redirect(config('services.frontend_url') . '/login?error=oauth_init_failed');
        }
    }

    /**
     * Handle provider callback
     */
    public function callback(Request $request, string $provider)
    {
        $state = $request->query('state');
        $code = $request->query('code');
        $error = $request->query('error');

        // Check if user denied authorization
        if ($error) {
            return redirect(config('services.frontend_url') . '/login?error=access_denied');
        }

        // Verify state
        $stateData = Cache::pull("oauth_state_{$state}");
        
        if (!$stateData || $stateData['provider'] !== $provider) {
            return redirect(config('services.frontend_url') . '/login?error=invalid_state');
        }

        if (!$code) {
            return redirect(config('services.frontend_url') . '/login?error=no_code');
        }

        try {
            $user = $this->socialAuthService->handleCallback($provider);
            
            // Create Sanctum token for API authentication
            $token = $user->createToken('oauth-token')->plainTextToken;

            // Redirect to frontend with token
            $redirectTo = $stateData['redirect_to'] ?? '/dashboard';
            $frontendUrl = config('services.frontend_url');
            
            return redirect($frontendUrl . '/auth/callback?token=' . urlencode($token) . '&redirect=' . urlencode($redirectTo));
        } catch (\Exception $e) {
            \Log::error('Social auth error: ' . $e->getMessage(), [
                'provider' => $provider,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect(config('services.frontend_url') . '/login?error=auth_failed&message=' . urlencode($e->getMessage()));
        }
    }
}