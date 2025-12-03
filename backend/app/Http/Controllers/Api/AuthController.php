<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use App\Models\ActivityLog;
use App\Models\RememberToken;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
        ], [
            'email.unique' => 'This email is already registered. Please login or use a different email.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'company' => $request->company,
                'job_title' => $request->job_title,
                'is_new_user' => true, // Mark as new user for onboarding
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            // Send registration success notification
            try {
                $this->notificationService->create($user, 'registration_success', [
                    'name' => $user->first_name,
                    'profile_url' => config('app.frontend_url') . '/UserDashboard/PlanSelection',
                ]);
            } catch (\Exception $e) {
                // Log notification error but don't fail registration
                \Log::error('Failed to send registration notification: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'user' => $user->fresh(),
                'token' => $token,
                'token_type' => 'Bearer',
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'remember' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Check if this is an OAuth-only account trying to login with password
        if ($user->isOAuthOnly()) {
            return response()->json([
                'success' => false,
                'message' => 'This account uses ' . $user->getOAuthProviderDisplayName() . ' login. Please sign in with ' . $user->getOAuthProviderDisplayName() . '.',
            ], 401);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // Log login activity
        ActivityLog::logActivity(
            $user,
            'login',
            'User logged in successfully',
            [
                'metadata' => [
                    'ip_address' => $request->ip(),
                    'device' => $this->getDeviceInfo($request->userAgent()),
                    'user_agent' => $request->userAgent(),
                ]
            ]
        );

        // Check if this is a new device/location (simplified check)
        $lastLoginDevice = $user->last_login_device ?? '';
        $currentDevice = $request->userAgent();
        
        if ($lastLoginDevice && $lastLoginDevice !== $currentDevice) {
            // Send new device login notification
            $this->notificationService->create($user, 'login_new_device', [
                'device' => $this->getDeviceInfo($currentDevice),
                'ip' => $request->ip(),
                'time' => now()->format('Y-m-d H:i:s'),
            ]);
        }
        
        // Update last login device
        $user->update(['last_login_device' => $currentDevice]);

        // Handle "Remember Me" functionality (only for email/password login)
        $rememberTokenCookie = null;
        if ($request->boolean('remember')) {
            $rememberTokenCookie = $this->createRememberToken($user, $request);
        }

        $response = response()->json([
            'success' => true,
            'user' => $user->load('nfcCards'),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);

        // Attach remember me cookie if created
        if ($rememberTokenCookie) {
            $response->withCookie($rememberTokenCookie);
        }

        return $response;
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        
        // Log logout activity before deleting token
        ActivityLog::logActivity(
            $user,
            'logout',
            'User logged out',
            [
                'metadata' => [
                    'ip_address' => $request->ip(),
                    'device' => $this->getDeviceInfo($request->userAgent()),
                    'user_agent' => $request->userAgent(),
                ]
            ]
        );
        
        $user->currentAccessToken()->delete();

        // Revoke remember me token if present
        $response = response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);

        if ($request->hasCookie('remember_token')) {
            $rememberToken = $request->cookie('remember_token');
            RememberToken::revokeToken(RememberToken::hashToken($rememberToken));
            
            // Clear the cookie
            $response->withCookie(Cookie::forget('remember_token'));
        }

        return $response;
    }

    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => $user->load('nfcCards')
        ]);
    }

    /**
     * Get simplified device info from user agent
     */
    private function getDeviceInfo($userAgent)
    {
        if (preg_match('/Windows/', $userAgent)) {
            return 'Windows PC';
        } elseif (preg_match('/Macintosh/', $userAgent)) {
            return 'Mac';
        } elseif (preg_match('/iPhone/', $userAgent)) {
            return 'iPhone';
        } elseif (preg_match('/iPad/', $userAgent)) {
            return 'iPad';
        } elseif (preg_match('/Android/', $userAgent)) {
            return 'Android Device';
        } else {
            return 'Unknown Device';
        }
    }

    /**
     * Mark user as no longer new (after completing onboarding/plan selection)
     */
    public function completeOnboarding(Request $request)
    {
        $user = $request->user();
        
        $user->update(['is_new_user' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Onboarding completed',
            'user' => $user->load('nfcCards')
        ]);
    }

    /**
     * Check if an email exists and what authentication method it uses.
     * Used by frontend to determine if OAuth login should be blocked.
     * 
     * GET /api/auth/check-email?email=user@example.com
     */
    public function checkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Email doesn't exist - OAuth can proceed to create new account
            return response()->json([
                'success' => true,
                'exists' => false,
                'has_password' => false,
                'has_oauth' => false,
                'oauth_providers' => [],
            ]);
        }

        // Check authentication methods
        $hasPassword = $user->hasLocalPassword();
        $oauthProviders = $user->socialIdentities()->pluck('provider')->toArray();
        $hasOAuth = count($oauthProviders) > 0;

        return response()->json([
            'success' => true,
            'exists' => true,
            'has_password' => $hasPassword,
            'has_oauth' => $hasOAuth,
            'oauth_providers' => $oauthProviders,
            // If user has password, OAuth login should be blocked
            'oauth_blocked' => $hasPassword && !$hasOAuth,
            'message' => $hasPassword && !$hasOAuth
                ? 'This account uses email/password login. Please sign in with your password.'
                : null,
        ]);
    }

    /**
     * Get list of linked OAuth accounts for the current user.
     * 
     * GET /api/user/linked-accounts
     */
    public function getLinkedAccounts(Request $request)
    {
        $user = $request->user();
        $socialIdentities = $user->socialIdentities()
            ->select('id', 'provider', 'provider_id', 'created_at')
            ->get()
            ->map(function ($identity) {
                return [
                    'id' => $identity->id,
                    'provider' => $identity->provider,
                    'linked_at' => $identity->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'accounts' => $socialIdentities,
            'has_password' => $user->hasLocalPassword(),
        ]);
    }

    /**
     * Verify user's password before sensitive operations.
     * 
     * POST /api/user/verify-password
     */
    public function verifyPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!$user->hasLocalPassword()) {
            return response()->json([
                'success' => false,
                'message' => 'This account does not have a password set.',
            ], 400);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password verified.',
        ]);
    }

    /**
     * Initiate OAuth account linking flow.
     * Verifies password and stores a temporary token to indicate this is a link operation.
     * 
     * POST /api/user/initiate-link-account
     */
    public function initiateLinkAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'required|string|in:google,apple',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $provider = $request->provider;

        // Check if provider is already linked
        if ($user->socialIdentities()->where('provider', $provider)->exists()) {
            return response()->json([
                'success' => false,
                'message' => ucfirst($provider) . ' is already linked to your account.',
            ], 400);
        }

        // Verify password first
        if (!$user->hasLocalPassword()) {
            return response()->json([
                'success' => false,
                'message' => 'This account does not have a password set.',
            ], 400);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password.',
            ], 401);
        }

        // Generate a secure linking token that will be used to verify this is a link operation
        $linkToken = Str::random(64);
        
        // Store the link token in cache for 10 minutes
        // The token is tied to the user ID and provider
        \Cache::put(
            "link_account:{$linkToken}",
            [
                'user_id' => $user->id,
                'provider' => $provider,
                'email' => $user->email,
                'created_at' => now()->toIso8601String(),
            ],
            now()->addMinutes(10)
        );

        // Return the OAuth redirect URL with the link token
        $redirectUrl = config('app.url') . "/api/auth/{$provider}/redirect?link_token={$linkToken}";

        return response()->json([
            'success' => true,
            'redirect_url' => $redirectUrl,
            'message' => 'Password verified. Redirecting to ' . ucfirst($provider) . ' to complete linking.',
        ]);
    }

    /**
     * Unlink an OAuth provider from the current user's account.
     * 
     * DELETE /api/user/linked-accounts/{provider}
     */
    public function unlinkAccount(Request $request, string $provider)
    {
        if (!in_array($provider, ['google', 'apple'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid provider.',
            ], 400);
        }

        $user = $request->user();

        // Check if provider is linked
        $identity = $user->socialIdentities()->where('provider', $provider)->first();

        if (!$identity) {
            return response()->json([
                'success' => false,
                'message' => ucfirst($provider) . ' is not linked to your account.',
            ], 404);
        }

        // Ensure user has another way to login
        $hasPassword = $user->hasLocalPassword();
        $otherProviders = $user->socialIdentities()->where('provider', '!=', $provider)->count();

        if (!$hasPassword && $otherProviders === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot unlink ' . ucfirst($provider) . '. You must have at least one login method. Please set a password first.',
            ], 400);
        }

        // Delete the social identity
        $identity->delete();

        \Log::info('OAuth provider unlinked', [
            'user_id' => $user->id,
            'email' => $user->email,
            'provider' => $provider,
        ]);

        return response()->json([
            'success' => true,
            'message' => ucfirst($provider) . ' has been unlinked from your account.',
        ]);
    }

    /**
     * Logout from all devices and revoke all remember tokens
     * 
     * POST /api/user/logout-everywhere
     */
    public function logoutEverywhere(Request $request)
    {
        $user = $request->user();

        // Log the activity
        ActivityLog::logActivity(
            $user,
            'logout_everywhere',
            'User logged out from all devices',
            [
                'metadata' => [
                    'ip_address' => $request->ip(),
                    'device' => $this->getDeviceInfo($request->userAgent()),
                    'user_agent' => $request->userAgent(),
                ]
            ]
        );

        // Revoke all Sanctum tokens
        $user->tokens()->delete();

        // Revoke all remember me tokens
        RememberToken::revokeAllForUser($user->id);

        $response = response()->json([
            'success' => true,
            'message' => 'You have been logged out from all devices.',
        ]);

        // Clear the remember me cookie if present
        if ($request->hasCookie('remember_token')) {
            $response->withCookie(Cookie::forget('remember_token'));
        }

        return $response;
    }

    /**
     * Create a remember me token for the user
     * 
     * @param User $user
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    private function createRememberToken(User $user, Request $request)
    {
        // Generate a cryptographically secure token
        $plainToken = RememberToken::generateToken();
        $tokenHash = RememberToken::hashToken($plainToken);

        // Store the hashed token in the database (30 days expiry)
        RememberToken::create([
            'user_id' => $user->id,
            'token_hash' => $tokenHash,
            'expires_at' => now()->addDays(30),
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
        ]);

        // Return httpOnly cookie with the plain token
        // Cookie expires in 30 days, secure=true for HTTPS, sameSite=lax for CSRF protection
        return Cookie::make(
            'remember_token',
            $plainToken,
            60 * 24 * 30, // 30 days in minutes
            '/',
            null,
            true, // secure (HTTPS only)
            true, // httpOnly
            false, // raw
            'lax' // sameSite
        );
    }
}
