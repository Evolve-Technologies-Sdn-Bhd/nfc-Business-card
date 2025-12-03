<?php

namespace App\Http\Middleware;

use App\Models\RememberToken;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RememberMe
{
    /**
     * Handle an incoming request.
     * 
     * Check for remember_token cookie and auto-authenticate if valid.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if user is already authenticated
        if (Auth::guard('sanctum')->check()) {
            return $next($request);
        }

        // Check for remember_token cookie
        if (!$request->hasCookie('remember_token')) {
            return $next($request);
        }

        $plainToken = $request->cookie('remember_token');
        
        if (!$plainToken) {
            return $next($request);
        }

        // Hash the token to look it up in the database
        $tokenHash = RememberToken::hashToken($plainToken);

        // Find the token in the database
        $rememberToken = RememberToken::findValidToken($tokenHash);

        if (!$rememberToken) {
            // Token not found or expired - clear the cookie
            return $next($request)->withCookie(
                cookie()->forget('remember_token')
            );
        }

        // Get the user
        $user = $rememberToken->user;

        if (!$user) {
            // User not found - revoke token and clear cookie
            $rememberToken->delete();
            return $next($request)->withCookie(
                cookie()->forget('remember_token')
            );
        }

        // Only auto-authenticate for users with password (email/password login)
        // Do NOT auto-authenticate OAuth-only users
        if ($user->isOAuthOnly()) {
            $rememberToken->delete();
            return $next($request)->withCookie(
                cookie()->forget('remember_token')
            );
        }

        // Update last used timestamp
        $rememberToken->updateLastUsed();

        // Create a new Sanctum token for this session
        $token = $user->createToken('remember_me_auth')->plainTextToken;

        // Set the token in the request for Sanctum authentication
        $request->headers->set('Authorization', 'Bearer ' . $token);

        // Authenticate the user
        Auth::guard('sanctum')->setUser($user);

        return $next($request);
    }
}
