<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminRoleController extends Controller
{
    /**
     * The canonical permission catalogue, grouped by domain.
     *
     * The array keys are the exact strings persisted in the
     * `users.admin_permissions` JSON column — no other value may ever be stored.
     *
     * @return array<string, array{label: string, permissions: array<string, string>}>
     */
    public static function permissionCatalogue(): array
    {
        return [
            'users' => [
                'label' => 'User Management',
                'permissions' => [
                    'user_management.view' => 'View users & business accounts',
                    'user_management.create' => 'Create users & business accounts',
                    'user_management.update' => 'Update users & business accounts',
                    'user_management.delete' => 'Delete users & business accounts',
                ],
            ],
            'cards' => [
                'label' => 'NFC Cards & Templates',
                'permissions' => [
                    'nfc_management.view' => 'View NFC cards',
                    'nfc_management.create' => 'Register NFC cards',
                    'nfc_management.update' => 'Update NFC cards',
                    'nfc_management.delete' => 'Delete NFC cards',
                    'nfc_management.orders' => 'Process NFC card orders',
                    'content.templates' => 'Manage card templates',
                ],
            ],
            'billing' => [
                'label' => 'Payments, Invoices & Refunds',
                'permissions' => [
                    'billing.view' => 'View payments & transactions',
                    'billing.invoices' => 'Manage invoices',
                    'billing.refunds' => 'Approve or reject refunds',
                    'billing.prices' => 'Manage plan prices',
                ],
            ],
            'content' => [
                'label' => 'Content & Design',
                'permissions' => [
                    'content.profile_builder' => 'Manage profile builder sections & fields',
                    'content.legal_documents' => 'Manage legal documents',
                ],
            ],
            'support' => [
                'label' => 'Support & Communication',
                'permissions' => [
                    'support.notifications' => 'Send admin notifications',
                    'support.feedback' => 'Manage user feedback',
                    'support.chatbot' => 'Manage chatbot FAQ knowledge base',
                ],
            ],
            'analytics' => [
                'label' => 'Analytics & Reporting',
                'permissions' => [
                    'analytics.view' => 'View analytics & statistics',
                    'analytics.export' => 'Export data (CSV)',
                ],
            ],
            'system' => [
                'label' => 'System & Configuration',
                'permissions' => [
                    'system.settings' => 'Manage system settings',
                    'system.audit_log' => 'View system audit log',
                ],
            ],
        ];
    }

    /**
     * Flat list of every valid permission key.
     *
     * @return array<int, string>
     */
    public static function allPermissionKeys(): array
    {
        $keys = [];
        foreach (self::permissionCatalogue() as $domain) {
            $keys = array_merge($keys, array_keys($domain['permissions']));
        }
        return $keys;
    }

    /**
     * Roles that may be assigned to an admin account.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function assignableRoles(): array
    {
        return [
            ['value' => 'super_admin', 'label' => 'Super Admin'],
            ['value' => 'admin', 'label' => 'Admin'],
            ['value' => 'moderator', 'label' => 'Moderator'],
        ];
    }

    /**
     * List admin users, the assignable roles and the permission catalogue.
     *
     * GET /api/admin/roles
     */
    public function index(Request $request)
    {
        try {
            $admins = User::where('is_admin', true)
                ->orderByRaw("CASE admin_role WHEN 'super_admin' THEN 0 WHEN 'admin' THEN 1 WHEN 'moderator' THEN 2 ELSE 3 END")
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'last_name', 'email', 'is_admin', 'admin_role', 'admin_permissions', 'last_admin_action_at', 'created_at'])
                ->map(function (User $user) {
                    return [
                        'id' => $user->id,
                        'full_name' => $user->full_name,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'is_admin' => (bool) $user->is_admin,
                        'admin_role' => $user->admin_role,
                        'admin_role_display' => $user->admin_role_display,
                        'admin_permissions' => $user->admin_permissions ?? [],
                        'last_admin_action_at' => $user->last_admin_action_at,
                        'created_at' => $user->created_at,
                    ];
                })->values();

            $caller = $request->user();

            return response()->json([
                'success' => true,
                'data' => [
                    'roles' => self::assignableRoles(),
                    'permission_catalogue' => self::permissionCatalogue(),
                    'admins' => $admins,
                    'is_super_admin' => $caller ? (bool) $caller->isSuperAdmin() : false,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('AdminRoleController::index error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch admin roles.',
            ], 500);
        }
    }

    /**
     * Update a user's admin role and permissions.
     *
     * PUT /api/admin/users/{user}/role
     * Body: { admin_role: super_admin|admin|moderator, admin_permissions: string[] }
     *
     * Only a super admin may grant or modify admin roles/permissions, and no
     * caller may modify their own privileges (self-escalation guard).
     */
    public function update(Request $request, User $user)
    {
        $caller = $request->user();

        // Only a super admin may change admin roles/permissions.
        if (!$caller || !$caller->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only a super admin can manage admin roles and permissions.',
                'error' => 'SUPER_ADMIN_REQUIRED',
            ], 403);
        }

        // Prevent a user from escalating (or otherwise altering) their own privileges.
        if ($caller->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot change your own admin role or permissions.',
                'error' => 'SELF_MODIFICATION_FORBIDDEN',
            ], 403);
        }

        // Super admin accounts cannot be altered by other admins (lockout guard),
        // mirroring AdminController::updateUser behaviour.
        if ($user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot modify another super admin account.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'admin_role' => 'required|in:super_admin,admin,moderator',
            'admin_permissions' => 'nullable|array',
            'admin_permissions.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Never persist arbitrary strings — submitted permissions must be a
        // strict subset of the catalogue.
        $allowed = self::allPermissionKeys();
        $submitted = $request->input('admin_permissions', []);
        $submitted = is_array($submitted) ? array_values($submitted) : [];

        $invalid = array_values(array_diff($submitted, $allowed));
        if (!empty($invalid)) {
            return response()->json([
                'success' => false,
                'message' => 'One or more permissions are not part of the permission catalogue.',
                'errors' => [
                    'admin_permissions' => $invalid,
                ],
            ], 422);
        }

        try {
            // Super admins implicitly hold every permission; store the full set
            // for consistency so the UI round-trips predictably.
            $permissions = $request->admin_role === 'super_admin'
                ? array_values($allowed)
                : array_values(array_unique($submitted));

            $user->update([
                'is_admin' => true,
                'admin_role' => $request->admin_role,
                'admin_permissions' => $permissions,
            ]);

            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Admin role and permissions updated successfully.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'full_name' => $user->full_name,
                        'email' => $user->email,
                        'is_admin' => (bool) $user->is_admin,
                        'admin_role' => $user->admin_role,
                        'admin_role_display' => $user->admin_role_display,
                        'admin_permissions' => $user->admin_permissions ?? [],
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('AdminRoleController::update error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update admin role and permissions.',
            ], 500);
        }
    }
}
