<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessManager
{
    /**
     * Ensure the authenticated user may manage a Business tenant.
     *
     * Passes for the Business account owner and for employees delegated the
     * `admin` business role. Passing the `cards` scope also admits employees
     * delegated the `card_manager` role (card-only management).
     *
     * Usage: ->middleware('business.manager')  |  ->middleware('business.manager:cards')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $scope
     */
    public function handle(Request $request, Closure $next, ?string $scope = null): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $allowed = $scope === 'cards'
            ? $user->canManageBusinessCards()
            : $user->canManageBusiness();

        if (!$allowed) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. You do not have permission to manage this Business account.',
                'required_role' => $scope === 'cards'
                    ? ['owner', 'admin', 'card_manager']
                    : ['owner', 'admin'],
            ], 403);
        }

        return $next($request);
    }
}
