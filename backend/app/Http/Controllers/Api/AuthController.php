<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\RememberToken;
use App\Services\NotificationService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

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
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/', // Require uppercase, lowercase, number, special char
            ],
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
        ], [
            'email.unique' => 'This email is already registered. Please login or use a different email.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&).',
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
                Log::error('Failed to send registration notification: ' . $e->getMessage());
            }

            // Send email verification link (non-blocking)
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Exception $e) {
                Log::warning('Failed to send email verification link after registration: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'user' => $user->fresh(),
                'token' => $token,
                'token_type' => 'Bearer',
                'needs_email_verification' => !$user->hasVerifiedEmail(),
                'verification_email_sent' => true,
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

        $emailVerified = $user->hasVerifiedEmail();
        $response = response()->json([
            'success' => true,
            'user' => $user->load('nfcCards'),
            'token' => $token,
            'token_type' => 'Bearer',
            'needs_email_verification' => !$emailVerified,
        ]);

        // Attach remember me cookie if created
        if ($rememberTokenCookie) {
            $response->withCookie($rememberTokenCookie);
        }

        return $response;
    }

    /**
     * Verify the user's email address via signed URL from the email.
     * GET /api/email/verify/{id}/{hash}?expires=...&signature=...
     */
    public function verifyEmail(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            $frontend = config('services.frontend_url', env('FRONTEND_URL', 'http://localhost:3002'));
            return redirect()->away($frontend . '/UserAccount/login?email_already_verified=1');
        }

        try {
            if ($request->user()->markEmailAsVerified()) {
                event(new Verified($request->user()));
            }

            // Onboarding in-app notification (verified badge)
            try {
                $this->notificationService->create($request->user(), 'email_verified', [
                    'name' => $request->user()->first_name,
                ]);
            } catch (\Throwable) {
                // Non-blocking
            }

            $frontend = config('services.frontend_url', env('FRONTEND_URL', 'http://localhost:3002'));
            return redirect()->away($frontend . '/UserAccount/login?email_verified=1');
        } catch (\Throwable $e) {
            Log::error('Email verification failed: ' . $e->getMessage());

            $frontend = config('services.frontend_url', env('FRONTEND_URL', 'http://localhost:3002'));
            return redirect()->away($frontend . '/UserAccount/verify-email?error=invalid_signature');
        }
    }

    /**
     * Resend the email verification notification.
     * POST /api/email/resend
     * Auth: (optional) valid sanctum token for logged-in unverified users, OR email param for guests
     */
    public function resendVerificationEmail(Request $request)
    {
        // Determine target user
        $user = $request->user();

        if (!$user && $request->has('email')) {
            $validator = Validator::make($request->only('email'), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email address.',
                ], 422);
            }

            $user = User::where('email', $request->input('email'))->first();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authenticated user or valid email is required.',
            ], 401);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'already_verified' => true,
                'message' => 'Your email has already been verified.',
            ]);
        }

        // Throttle resends: max 3 per hour per email
        $throttleKey = 'verify-email|' . strtolower($user->email);
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'success' => false,
                'message' => "Too many resend attempts. Please try again in {$seconds} seconds.",
                'retry_after' => $seconds,
            ], 429);
        }

        RateLimiter::hit($throttleKey, 3600);

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            Log::error('Failed to resend verification email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to send verification email. Please try again later.',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'message' => 'Verification link has been resent. Please check your inbox (and spam folder).',
        ]);
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

        // BUGFIX: Auto-complete onboarding for users who have paid but haven't clicked "Go to Dashboard"
        // This is a fallback in case the frontend auto-complete or webhook update didn't happen
        if ($user->is_new_user === true && $user->subscription_active === true) {
            try {
                $user->update(['is_new_user' => false]);

                \Log::info('Auto-completed onboarding for paid user (profile fetch fallback)', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'subscription_plan' => $user->subscription_plan,
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to auto-complete onboarding in profile endpoint', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue anyway - don't fail the request
            }
        }

        // Eager load SoT relationships: nfcCards + subscriptions (single source of truth)
        // Accessors for subscription_plan, subscription_active etc. will read from subscriptions first
        return response()->json([
            'success' => true,
            'user' => $user->load(['nfcCards', 'latestSubscription']),
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
     * Change the authenticated user's password (from Security tab).
     *
     * POST /api/user/change-password
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|max:255|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // Require current password UNLESS this is an OAuth-only user setting their first password
        if ($user->hasLocalPassword()) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kata laluan semasa tidak tepat.',
                    'errors' => ['current_password' => ['Kata laluan semasa tidak tepat.']],
                ], 401);
            }
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        ActivityLog::logActivity(
            $user,
            'password_changed',
            'User changed their own password',
            [
                'metadata' => [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
            ],
        );

        try {
            $this->notificationService->create($user, 'password_changed', []);
        } catch (\Throwable) { /* ignore non-critical */ }

        return response()->json([
            'success' => true,
            'message' => 'Kata laluan berjaya dikemaskini.',
        ]);
    }

    /**
     * List active authenticated sessions (Sanctum tokens + remember tokens) for the current user.
     *
     * GET /api/user/sessions
     */
    public function getActiveSessions(Request $request)
    {
        $user = $request->user();

        $currentTokenId = $request->user()?->currentAccessToken()?->id;

        // Sanctum personal access tokens
        $sanctumTokens = $user->tokens()
            ->orderBy('last_used_at', 'desc')
            ->get()
            ->map(function ($token) use ($currentTokenId) {
                $ua = (string) ($token->device ?? $token->name ?? '');
                $createdBy = $this->parseUserAgent($ua);
                return [
                    'id' => (int) $token->id,
                    'type' => 'token',
                    'name' => $token->name ?? 'Unnamed Session',
                    'device' => $createdBy['device'] ?? 'Unknown Device',
                    'platform' => $createdBy['platform'] ?? null,
                    'browser' => $createdBy['browser'] ?? null,
                    'ip_address' => $token->ip_address ?? null,
                    'last_seen_at' => $token->last_used_at?->toIso8601String() ?? $token->created_at->toIso8601String(),
                    'created_at' => $token->created_at->toIso8601String(),
                    'is_current' => $currentTokenId !== null && (int) $token->id === (int) $currentTokenId,
                ];
            });

        // Remember me (cookie) tokens
        $rememberTokens = RememberToken::where('user_id', $user->id)
            ->where('expires_at', '>=', now())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($token) {
                $info = $this->parseUserAgent((string) $token->user_agent);
                return [
                    'id' => 'remember_'.$token->id,
                    'type' => 'remember',
                    'name' => 'Remember Me Session',
                    'device' => $info['device'] ?? 'Unknown Device',
                    'platform' => $info['platform'] ?? null,
                    'browser' => $info['browser'] ?? null,
                    'ip_address' => $token->ip_address ?? null,
                    'last_seen_at' => $token->last_used_at?->toIso8601String() ?? $token->created_at->toIso8601String(),
                    'created_at' => $token->created_at->toIso8601String(),
                    'is_current' => false,
                ];
            });

        return response()->json([
            'success' => true,
            'sessions' => $sanctumTokens->concat($rememberTokens)->values()->all(),
        ]);
    }

    /**
     * Revoke an individual session (either Sanctum token by id or Remember token by id).
     *
     * DELETE /api/user/sessions/{id}
     */
    public function revokeSession(Request $request, string $id)
    {
        $user = $request->user();

        // Remember tokens are prefixed with "remember_"
        if (str_starts_with($id, 'remember_')) {
            $tokenId = (int) substr($id, 9);
            $token = RememberToken::where('id', $tokenId)->where('user_id', $user->id)->first();
            if (!$token) {
                return response()->json(['success' => false, 'message' => 'Session not found.'], 404);
            }
            $token->delete();
            return response()->json(['success' => true, 'message' => 'Sesi telah dilog keluar.']);
        }

        $token = $user->tokens()->find((int) $id);
        if (!$token) {
            return response()->json(['success' => false, 'message' => 'Session not found.'], 404);
        }

        $currentId = $request->user()?->currentAccessToken()?->id;
        if ($currentId !== null && (int) $token->id === (int) $currentId) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak boleh membatalkan sesi semasa. Gunakan Logout atau Logout Everywhere.',
            ], 400);
        }

        $token->delete();

        ActivityLog::logActivity(
            $user,
            'session_revoked',
            "User revoked session token id: {$token->id}",
            ['metadata' => ['ip_address' => $request->ip(), 'revoked_token_id' => $token->id]],
        );

        return response()->json(['success' => true, 'message' => 'Sesi telah dilog keluar.']);
    }

    // ========================================================================
    // TWO-FACTOR AUTHENTICATION (TOTP — RFC 6238)
    // Lightweight native implementation (no external library required).
    // Algorithm: HMAC-SHA1 with 30s period, 6 digit OTP, 160-bit base32 secret.
    // ========================================================================

    /**
     * Generate 2FA secret + QR code payload for the enrollment step.
     * User must scan the QR, then confirm with a valid 6-digit OTP to enable.
     *
     * POST /api/user/2fa/setup
     */
    public function setupTwoFactor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'sometimes|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        if ($user->two_factor_enabled === true) {
            return response()->json([
                'success' => false,
                'message' => '2FA sudah diaktifkan. Sila nyahaktifkan dahulu untuk generate secret baru.',
            ], 400);
        }

        // Generate 160-bit secret → 32 chars base32
        $secret = $this->generateTotpSecret();
        $recoveryCodes = $this->generateRecoveryCodes(8);

        // Store temporarily in two_factor_secret (encrypted via User model cast)
        // with a "pending" flag so user cannot use it until confirm.
        $user->update([
            'two_factor_secret' => encrypt(json_encode([
                'secret' => $secret,
                'status' => 'pending',
                'recovery_codes' => $recoveryCodes,
                'created_at' => now()->toIso8601String(),
            ])),
        ]);

        // otpauth://totp/Label?secret=...&issuer=...
        $issuer = 'NFCGo Business Card';
        $label = rawurlencode($user->email ?? 'user');
        $qrPayload = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            rawurlencode($issuer),
            $label,
            $secret,
            rawurlencode($issuer)
        );

        ActivityLog::logActivity($user, '2fa_setup_initiated', 'User initiated 2FA setup', []);

        return response()->json([
            'success' => true,
            'secret' => $secret,
            'qr_data' => $qrPayload,
            'recovery_codes' => $recoveryCodes,
            'instructions' => 'Scan QR dalam Google Authenticator / Authy, kemudian hantar 6 digit OTP untuk /2fa/confirm.',
        ]);
    }

    /**
     * Validate a 6-digit OTP against the pending 2FA secret and, if valid,
     * flip two_factor_enabled = true for future logins.
     *
     * POST /api/user/2fa/confirm
     */
    public function confirmTwoFactor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|size:6|regex:/^[0-9]+$/',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        if ($user->two_factor_enabled === true) {
            return response()->json(['success' => false, 'message' => '2FA already enabled.'], 400);
        }

        $secretPayload = null;
        try {
            $secretPayload = $user->two_factor_secret ? json_decode(decrypt($user->two_factor_secret), true, 512, JSON_THROW_ON_ERROR) : null;
        } catch (\Throwable) {
            $secretPayload = null;
        }

        if (!is_array($secretPayload) || ($secretPayload['status'] ?? null) !== 'pending' || empty($secretPayload['secret'])) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi setup 2FA sudah tamat. Sila jalankan /2fa/setup sekali lagi.',
            ], 400);
        }

        $valid = $this->verifyTotp($secretPayload['secret'], $request->code, 1); // ± 1 period drift (30s)
        if (!$valid) {
            return response()->json([
                'success' => false,
                'message' => 'Kod OTP tidak tepat.',
                'errors' => ['code' => ['Kod OTP tidak tepat.']],
            ], 400);
        }

        // Finalize: mark "confirmed" and persist recovery codes
        $confirmed = [
            'secret' => $secretPayload['secret'],
            'status' => 'confirmed',
            'recovery_codes' => $secretPayload['recovery_codes'] ?? [],
            'confirmed_at' => now()->toIso8601String(),
        ];

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => encrypt(json_encode($confirmed)),
        ]);

        ActivityLog::logActivity($user, '2fa_enabled', '2FA successfully enabled for user', [
            'metadata' => ['ip_address' => $request->ip()],
        ]);

        return response()->json([
            'success' => true,
            'message' => '2FA berjaya diaktifkan. Simpan recovery codes di tempat selamat.',
            'recovery_codes' => $confirmed['recovery_codes'],
        ]);
    }

    /**
     * Disable 2FA (requires password confirmation OR a valid recovery/OTP code).
     *
     * POST /api/user/2fa/disable
     */
    public function disableTwoFactor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required_without:recovery_code|string',
            'recovery_code' => 'required_without:password|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        if (!$user->two_factor_enabled) {
            return response()->json(['success' => false, 'message' => '2FA tidak aktif.'], 400);
        }

        $confirmed = false;
        if ($user->hasLocalPassword() && $request->filled('password')) {
            $confirmed = Hash::check($request->password, $user->password);
        }
        if (!$confirmed && $request->filled('recovery_code')) {
            $secretPayload = null;
            try {
                $secretPayload = $user->two_factor_secret ? json_decode(decrypt($user->two_factor_secret), true, 512, JSON_THROW_ON_ERROR) : null;
            } catch (\Throwable) {
                $secretPayload = null;
            }
            $codes = is_array($secretPayload) ? ($secretPayload['recovery_codes'] ?? []) : [];
            $confirmed = in_array($request->recovery_code, $codes, true);
            // Use once → remove from list (handled on update below)
        }

        if (!$confirmed) {
            return response()->json([
                'success' => false,
                'message' => 'Pengesahan gagal. Sila masukkan kata laluan atau recovery code yang betul.',
            ], 401);
        }

        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
        ]);

        ActivityLog::logActivity($user, '2fa_disabled', '2FA disabled for user', [
            'metadata' => ['ip_address' => $request->ip()],
        ]);

        return response()->json([
            'success' => true,
            'message' => '2FA berjaya dinyahaktifkan.',
        ]);
    }

    /**
     * Regenerate 8 fresh recovery codes (rotates the list — old ones stop working).
     *
     * POST /api/user/2fa/recovery-codes/regenerate
     */
    public function regenerate2FARecoveryCodes(Request $request)
    {
        $user = $request->user();
        if (!$user->two_factor_enabled) {
            return response()->json(['success' => false, 'message' => '2FA tidak aktif.'], 400);
        }

        $secretPayload = null;
        try {
            $secretPayload = $user->two_factor_secret ? json_decode(decrypt($user->two_factor_secret), true, 512, JSON_THROW_ON_ERROR) : null;
        } catch (\Throwable) {
            $secretPayload = null;
        }

        if (!is_array($secretPayload) || empty($secretPayload['secret'])) {
            return response()->json(['success' => false, 'message' => 'Secret 2FA tidak sah.'], 500);
        }

        $newCodes = $this->generateRecoveryCodes(8);
        $secretPayload['recovery_codes'] = $newCodes;
        $secretPayload['recovery_codes_regenerated_at'] = now()->toIso8601String();

        $user->update([
            'two_factor_secret' => encrypt(json_encode($secretPayload)),
        ]);

        ActivityLog::logActivity($user, '2fa_recovery_codes_rotated', 'User regenerated their 2FA recovery codes', []);

        return response()->json([
            'success' => true,
            'recovery_codes' => $newCodes,
            'message' => 'Recovery codes baharu telah dijana. Yang lama tidak lagi sah.',
        ]);
    }

    // ---------------------------------------------------------------
    // Internal helpers for Settings Security Tab (parsing / crypto)
    // ---------------------------------------------------------------

    /**
     * Lightweight user-agent parser: returns device/platform/browser.
     *
     * @return array{device:string,platform:string|null,browser:string|null}
     */
    private function parseUserAgent(string $ua): array
    {
        $device = 'Unknown Device';
        $platform = null;
        $browser = null;

        if (preg_match('/iPhone/', $ua)) {
            $device = 'iPhone';
            $platform = 'iOS';
        } elseif (preg_match('/iPad/', $ua)) {
            $device = 'iPad';
            $platform = 'iPadOS';
        } elseif (preg_match('/Android/', $ua)) {
            $device = 'Android';
            $platform = 'Android';
        } elseif (preg_match('/Windows/', $ua)) {
            $device = 'Windows PC';
            $platform = 'Windows';
        } elseif (preg_match('/Macintosh|Mac OS X/', $ua)) {
            $device = 'Mac';
            $platform = 'macOS';
        } elseif (preg_match('/Linux/', $ua)) {
            $device = 'Linux PC';
            $platform = 'Linux';
        }

        if (preg_match('#Edg(e?)/([0-9]+)#i', $ua, $m)) {
            $browser = 'Edge '.$m[2];
        } elseif (preg_match('#Firefox/([0-9.]+)#i', $ua, $m)) {
            $browser = 'Firefox '.$m[1];
        } elseif (preg_match('#Chrome/([0-9.]+)#i', $ua, $m)) {
            $browser = 'Chrome '.$m[1];
        } elseif (preg_match('#Safari/([0-9.]+)#i', $ua, $m) && !str_contains($ua, 'Chrome')) {
            $browser = 'Safari '.$m[1];
        }

        return compact('device', 'platform', 'browser');
    }

    /**
     * RFC 3548 Base32 encoder (without padding stripping — produces standard 32-char secrets).
     */
    private function generateTotpSecret(int $bytes = 20): string
    {
        $raw = random_bytes($bytes);
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output = '';
        $buffer = 0;
        $bitsLeft = 0;
        foreach (str_split($raw) as $b) {
            $buffer = ($buffer << 8) | ord($b);
            $bitsLeft += 8;
            while ($bitsLeft >= 5) {
                $bitsLeft -= 5;
                $output .= $chars[($buffer >> $bitsLeft) & 0x1F];
            }
        }
        if ($bitsLeft > 0) {
            $output .= $chars[($buffer << (5 - $bitsLeft)) & 0x1F];
        }
        return $output;
    }

    /**
     * Verify a TOTP code against a base32 secret.
     *
     * @param int $drift Allows N periods before/after current 30s window (± N*30s)
     */
    private function verifyTotp(string $base32Secret, string $code, int $drift = 1): bool
    {
        $secret = $this->base32Decode($base32Secret);
        if ($secret === '' || !preg_match('/^[0-9]{6}$/', $code)) {
            return false;
        }

        $period = 30;
        $now = (int) floor(time() / $period);
        for ($i = -$drift; $i <= $drift; $i++) {
            $counter = $this->packCounter($now + $i);
            $hmac = hash_hmac('sha1', $counter, $secret, true);
            $offset = ord($hmac[strlen($hmac) - 1]) & 0x0F;
            $otp = (
                ((ord($hmac[$offset]) & 0x7F) << 24) |
                ((ord($hmac[$offset + 1]) & 0xFF) << 16) |
                ((ord($hmac[$offset + 2]) & 0xFF) << 8) |
                (ord($hmac[$offset + 3]) & 0xFF)
            ) % 1000000;
            $expected = str_pad((string) $otp, 6, '0', STR_PAD_LEFT);
            if (hash_equals($expected, $code)) {
                return true;
            }
        }
        return false;
    }

    private function base32Decode(string $base32): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32 = strtoupper(rtrim($base32, '='));
        $buffer = 0;
        $bitsLeft = 0;
        $output = '';
        foreach (str_split($base32) as $c) {
            $v = strpos($chars, $c);
            if ($v === false) {
                return '';
            }
            $buffer = ($buffer << 5) | $v;
            $bitsLeft += 5;
            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }
        return $output;
    }

    private function packCounter(int $counter): string
    {
        return pack('N2', 0, $counter);
    }

    /**
     * @return string[] N random "XXXX-XXXX" format recovery codes
     */
    private function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $a = strtoupper(substr(bin2hex(random_bytes(4)), 0, 4));
            $b = strtoupper(substr(bin2hex(random_bytes(4)), 0, 4));
            $codes[] = "{$a}-{$b}";
        }
        return $codes;
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
