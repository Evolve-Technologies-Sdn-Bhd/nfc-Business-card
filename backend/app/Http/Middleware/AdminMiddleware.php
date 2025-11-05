<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin privileges required.',
                'error' => 'INSUFFICIENT_PERMISSIONS'
            ], 403);
        }

        // Update last admin action timestamp
        $user->update(['last_admin_action_at' => now()]);

        return $next($request);
    }
}
