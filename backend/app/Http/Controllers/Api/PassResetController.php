<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use App\Mail\PasswordResetMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
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
                Mail::to($user->email)->send(new PasswordResetMail($user, $rawToken, $ip));
            } catch (\Exception $e) {
                \Log::error('Password reset email failed: ' . $e->getMessage());
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