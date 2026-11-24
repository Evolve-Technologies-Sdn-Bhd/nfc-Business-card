<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Email verification enforcement with a grace period.
 *
 * Reliability goal: require a verified email to use the product, WITHOUT
 * locking anyone out unexpectedly.
 *
 *  - Accounts that existed before enforcement was introduced are grandfathered
 *    in (see the backfill migration) and are already verified.
 *  - Newly registered accounts may use the product for `auth.verification_grace_days`
 *    days, after which unverified access is rejected.
 *  - Admin accounts are never blocked.
 *  - A small set of auth-critical endpoints stays reachable so a blocked user
 *    can still fetch their profile, log out, or request a new verification email.
 */
class EnsureEmailIsVerifiedWithGrace
{
    /**
     * Endpoints that remain reachable even when the grace period has lapsed.
     *
     * Paths are compared against $request->path() (no leading slash).
     *
     * @var array<int, string>
     */
    protected array $except = [
        'api/me',
        'api/logout',
        'api/email/resend',
        'api/complete-onboarding',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // No authenticated user here — let the auth middleware decide.
        if (!$user) {
            return $next($request);
        }

        if (in_array($request->path(), $this->except, true)) {
            return $next($request);
        }

        if ($user->hasVerifiedEmail()) {
            return $next($request);
        }

        // Admin accounts are never blocked by email verification.
        if (!empty($user->is_admin)) {
            return $next($request);
        }

        // Grace period for newly registered accounts.
        $graceDays = (int) config('auth.verification_grace_days', 7);
        if ($graceDays > 0 && $user->created_at && $user->created_at->gt(now()->subDays($graceDays))) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Please verify your email address to continue. Check your inbox for the verification link.',
            'error' => 'EMAIL_NOT_VERIFIED',
            'email_verification_required' => true,
        ], 403);
    }
}
