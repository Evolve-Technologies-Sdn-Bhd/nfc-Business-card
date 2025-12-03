<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use App\Mail\PasswordResetMail;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Request a password reset link.
     * 
     * POST /api/auth/password-reset-request
     * Body: { "email": "user@example.com" }
     */
    public function requestReset(Request $request)
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

        $email = $request->email;
        $ip = $request->ip();

        // Rate limiting: 5 attempts per hour per email
        $emailKey = 'password-reset-email:' . $email;
        $ipKey = 'password-reset-ip:' . $ip;

        if (RateLimiter::tooManyAttempts($emailKey, 5)) {
            $seconds = RateLimiter::availableIn($emailKey);
            return response()->json([
                'success' => false,
                'message' => 'Too many reset attempts. Please try again in ' . ceil($seconds / 60) . ' minutes.'
            ], 429);
        }

        if (RateLimiter::tooManyAttempts($ipKey, 10)) {
            $seconds = RateLimiter::availableIn($ipKey);
            return response()->json([
                'success' => false,
                'message' => 'Too many requests from this IP. Please try again later.'
            ], 429);
        }

        // Always return success to prevent email enumeration
        RateLimiter::hit($emailKey, 3600); // 1 hour
        RateLimiter::hit($ipKey, 3600);

        // Find user
        $user = User::where('email', $email)->first();

        if ($user) {
            // Check if user is OAuth-only (signed up with Google/Apple, no local password)
            if ($user->isOAuthOnly()) {
                $providerName = $user->getOAuthProviderDisplayName() ?? 'a social login provider';
                
                \Log::info('Password reset requested for OAuth-only user', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'provider' => $user->provider,
                ]);

                // Return a specific response for OAuth users
                // Note: This does reveal that the email exists, but it's necessary UX
                // to prevent user confusion. The trade-off is acceptable here.
                return response()->json([
                    'success' => false,
                    'oauth_user' => true,
                    'provider' => $user->provider,
                    'provider_display' => $providerName,
                    'message' => "This account was created using {$providerName} Sign-In and does not use a password. Please continue logging in with {$providerName}.",
                ], 200); // 200 status to handle gracefully on frontend
            }

            // User has a local password - proceed with normal reset flow
            // Generate secure random token
            $rawToken = bin2hex(random_bytes(48)); // 96 characters
            
            // Hash the token with server secret
            $tokenHash = hash_hmac('sha256', $rawToken, config('app.key'));

            // Invalidate any existing unused tokens for this user
            PasswordReset::invalidateUserTokens($user->id);

            // Create new password reset record
            $passwordReset = PasswordReset::create([
                'user_id' => $user->id,
                'token_hash' => $tokenHash,
                'used' => false,
                'expires_at' => Carbon::now()->addHour(), // 60 minutes expiry
                'request_ip' => $ip,
                'user_agent' => $request->userAgent(),
            ]);

            // Send email
            try {
                $mailDriver = config('mail.default');
                
                \Log::info('=== PASSWORD RESET EMAIL ATTEMPT ===', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'request_ip' => $ip,
                    'mail_driver' => $mailDriver,
                    'mail_config' => [
                        'driver' => $mailDriver,
                        'host' => config('mail.mailers.smtp.host'),
                        'port' => config('mail.mailers.smtp.port'),
                        'username' => config('mail.mailers.smtp.username'),
                        'username_is_placeholder' => str_contains(config('mail.mailers.smtp.username') ?? '', 'your_mailtrap'),
                        'password_set' => !empty(config('mail.mailers.smtp.password')),
                        'encryption' => config('mail.mailers.smtp.encryption'),
                        'from_address' => config('mail.from.address'),
                        'from_name' => config('mail.from.name'),
                    ],
                ]);

                // CRITICAL CHECK: Detect if using placeholder credentials
                $smtpUsername = config('mail.mailers.smtp.username');
                if ($mailDriver === 'smtp' && str_contains($smtpUsername ?? '', 'your_mailtrap')) {
                    \Log::error('❌ PASSWORD RESET EMAIL BLOCKED - PLACEHOLDER CREDENTIALS DETECTED', [
                        'issue' => 'MAIL_USERNAME contains placeholder text',
                        'current_value' => $smtpUsername,
                        'action_required' => 'Update MAIL_USERNAME and MAIL_PASSWORD in .env with real credentials',
                    ]);
                    // Don't throw exception - fail silently for security
                    // But the email won't actually be sent
                }

                // CRITICAL CHECK: Warn if using 'log' driver
                if ($mailDriver === 'log') {
                    \Log::warning('⚠️  PASSWORD RESET EMAIL SENT TO LOG FILE (NOT REAL EMAIL)', [
                        'issue' => 'Using log mail driver - emails are written to laravel.log, not sent to inbox',
                        'user_email' => $user->email,
                        'action_required' => 'Update .env: MAIL_MAILER=smtp',
                        'log_location' => storage_path('logs/laravel.log'),
                    ]);
                }

                // Send the email
                Mail::to($user->email)->send(new PasswordResetMail($user, $rawToken, $ip));
                
                \Log::info('✅ Password reset email SENT (claimed success)', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'mail_driver' => $mailDriver,
                    'note' => $mailDriver === 'log' 
                        ? 'Email written to log file, NOT sent to inbox' 
                        : 'Email queued for delivery - check provider logs if not received',
                ]);
            } catch (\Exception $e) {
                \Log::error('❌ PASSWORD RESET EMAIL EXCEPTION THROWN', [
                    'error' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'user_id' => $user->id ?? null,
                    'email' => $user->email ?? null,
                    'mail_config' => [
                        'driver' => config('mail.default'),
                        'host' => config('mail.mailers.smtp.host'),
                        'port' => config('mail.mailers.smtp.port'),
                        'username_set' => !empty(config('mail.mailers.smtp.username')),
                        'password_set' => !empty(config('mail.mailers.smtp.password')),
                    ],
                    'trace' => $e->getTraceAsString(),
                ]);
                
                // Don't reveal error to user for security
                // But log it thoroughly for debugging
            }
        }

        // Always return the same message (security best practice)
        return response()->json([
            'success' => true,
            'message' => 'If that email is registered, we\'ve sent password reset instructions to it.'
        ]);
    }

    /**
     * Reset the password using the token.
     * 
     * POST /api/auth/password-reset
     * Body: { "token": "...", "password": "...", "password_confirmation": "..." }
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|size:96',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $rawToken = $request->token;
        $tokenHash = hash_hmac('sha256', $rawToken, config('app.key'));

        // Find the password reset record
        $passwordReset = PasswordReset::where('token_hash', $tokenHash)
            ->with('user')
            ->first();

        // Validate token
        if (!$passwordReset) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid reset token. Please request a new password reset link.'
            ], 400);
        }

        if ($passwordReset->used) {
            return response()->json([
                'success' => false,
                'message' => 'This reset link has already been used. Please request a new one if needed.'
            ], 400);
        }

        if ($passwordReset->expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'This reset link has expired. Please request a new password reset link.'
            ], 400);
        }

        // Update user password
        $user = $passwordReset->user;
        $user->password = Hash::make($request->password);
        $user->save();

        // Mark token as used
        $passwordReset->markAsUsed($request->ip());

        // Invalidate all other tokens for this user
        PasswordReset::invalidateUserTokens($user->id);

        // Optional: Revoke all user sessions/tokens (force re-login)
        // $user->tokens()->delete(); // If using Sanctum
        
        // Log the password change
        \Log::info('Password reset completed', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip()
        ]);

        // Send password changed notification
        $this->notificationService->create($user, 'password_changed', [
            'ip' => $request->ip(),
            'time' => now()->format('Y-m-d H:i:s'),
        ]);

        // Optional: Send confirmation email
        // Mail::to($user->email)->send(new PasswordChangedMail($user));

        return response()->json([
            'success' => true,
            'message' => 'Your password has been reset successfully. You can now sign in with your new password.'
        ]);
    }

    /**
     * Verify if a reset token is valid (optional endpoint for frontend validation).
     * 
     * GET /api/auth/verify-reset-token/{token}
     */
    public function verifyToken($token)
    {
        if (strlen($token) !== 96) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Invalid token format.'
            ], 400);
        }

        $tokenHash = hash_hmac('sha256', $token, config('app.key'));
        
        $passwordReset = PasswordReset::where('token_hash', $tokenHash)
            ->valid()
            ->first();

        if (!$passwordReset) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Invalid or expired reset token.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'valid' => true,
            'expires_at' => $passwordReset->expires_at->toIso8601String(),
        ]);
    }
}
