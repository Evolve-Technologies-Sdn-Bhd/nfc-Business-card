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
     * Base gate: the user must be flagged as an admin (`users.is_admin`).
     *
     * Optional permission gate: routes may declare a required permission via
     * the `admin` middleware parameter, e.g. `->middleware('admin:billing.refunds')`.
     * The permission string must come from AdminRoleController::permissionCatalogue().
     * Super admins implicitly hold every permission (see User::hasAdminPermission()).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $permission
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        $user = $request->user();

        if (!$user || !$user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin privileges required.',
                'error' => 'INSUFFICIENT_PERMISSIONS'
            ], 403);
        }

        if ($permission !== null && !$user->hasAdminPermission($permission)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Missing required permission: ' . $permission,
                'error' => 'INSUFFICIENT_PERMISSIONS',
                'required_permission' => $permission,
            ], 403);
        }

        // Update last admin action timestamp
        $user->update(['last_admin_action_at' => now()]);

        return $next($request);
    }
}
