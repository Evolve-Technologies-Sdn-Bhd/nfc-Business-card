<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log for authenticated users
        if (!auth()->check()) {
            return $response;
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();

        $isAdmin = (bool) $user->is_admin;
        $isBusiness = (bool) $user->isBusinessAccount();
        $isBusinessEmployee = (bool) $user->parent_business_id;

        // Skip log for non-business / non-admin / non-employee users
        if (!$isAdmin && !$isBusiness && !$isBusinessEmployee) {
            return $response;
        }

        // Only log successful responses (2xx) — skip 204 empty content
        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300 || $status === 204) {
            return $response;
        }

        // Determine action type and description based on route
        $route = $request->route();
        if (!$route) {
            return $response;
        }

        $method = $request->method();
        $path = $request->path();
        $actionMapping = $this->getActionMapping($method, $path, $request);
        if (!$actionMapping && $isAdmin) {
            // Fallback for any admin routes that don't have an explicit mapping
            // (avoids holes in audit log coverage for Admin pages)
            $actionMapping = $this->getFallbackAdminMapping($method, $path, $request);
        }

        if ($actionMapping) {
            $options = $actionMapping['options'] ?? [];
            $options['metadata'] = array_merge(
                $options['metadata'] ?? [],
                [
                    'route_uri' => $request->route()?->uri(),
                    'http_method' => $method,
                    'request_path' => $path,
                    'response_status' => $status,
                    'admin_context' => $isAdmin,
                ]
            );
            ActivityLog::logActivity(
                $user,
                $actionMapping['type'],
                $actionMapping['description'],
                $options
            );
        }

        return $response;
    }

    /**
     * Map request to activity log entry
     */
    private function getActionMapping($method, $path, $request)
    {
        // Profile updates
        if ($method === 'PUT' && str_contains($path, 'user/profile')) {
            return [
                'type' => 'profile_updated',
                'description' => 'Updated profile information',
                'options' => [
                    'entity_type' => 'profile',
                    'old_values' => $this->getOldProfileValues($request),
                    'new_values' => $request->only(['bio', 'job_title', 'company', 'location', 'website']),
                ],
            ];
        }

        // Landing page updates
        if ($method === 'PUT' && preg_match('/nfc-cards\/(\d+)\/landing-page/', $path, $matches)) {
            return [
                'type' => 'landing_page_updated',
                'description' => 'Updated NFC card landing page',
                'options' => [
                    'entity_type' => 'nfc_card',
                    'entity_id' => $matches[1],
                    'new_values' => $request->only(['content', 'title', 'description', 'theme']),
                ],
            ];
        }

        // Password changes
        if ($method === 'POST' && str_contains($path, 'password')) {
            return [
                'type' => 'password_changed',
                'description' => 'Changed password',
                'options' => [
                    'entity_type' => 'account',
                ],
            ];
        }

        // Link management
        if ($method === 'POST' && str_contains($path, 'links')) {
            return [
                'type' => 'link_added',
                'description' => 'Added new link: ' . ($request->input('title') ?? 'Untitled'),
                'options' => [
                    'entity_type' => 'link',
                    'new_values' => $request->only(['title', 'url', 'type']),
                ],
            ];
        }

        if ($method === 'PUT' && preg_match('/links\/(\d+)/', $path, $matches)) {
            return [
                'type' => 'link_updated',
                'description' => 'Updated link: ' . ($request->input('title') ?? 'Untitled'),
                'options' => [
                    'entity_type' => 'link',
                    'entity_id' => $matches[1],
                    'new_values' => $request->only(['title', 'url', 'type', 'is_active']),
                ],
            ];
        }

        if ($method === 'DELETE' && preg_match('/links\/(\d+)/', $path, $matches)) {
            return [
                'type' => 'link_deleted',
                'description' => 'Deleted link',
                'options' => [
                    'entity_type' => 'link',
                    'entity_id' => $matches[1],
                ],
            ];
        }

        if ($method === 'POST' && str_contains($path, 'links/reorder')) {
            return [
                'type' => 'link_reordered',
                'description' => 'Reordered links',
                'options' => [
                    'entity_type' => 'link',
                    'new_values' => ['order' => $request->input('links')],
                ],
            ];
        }

        // NFC card activation/deactivation
        if ($method === 'POST' && preg_match('/nfc-cards\/(\d+)\/activate/', $path, $matches)) {
            return [
                'type' => 'nfc_card_activated',
                'description' => 'Activated NFC card',
                'options' => [
                    'entity_type' => 'nfc_card',
                    'entity_id' => $matches[1],
                ],
            ];
        }

        if ($method === 'POST' && preg_match('/nfc-cards\/(\d+)\/deactivate/', $path, $matches)) {
            return [
                'type' => 'nfc_card_deactivated',
                'description' => 'Deactivated NFC card',
                'options' => [
                    'entity_type' => 'nfc_card',
                    'entity_id' => $matches[1],
                ],
            ];
        }

        // Profile image updates
        if ($method === 'POST' && str_contains($path, 'upload/profile-image')) {
            return [
                'type' => 'profile_image_updated',
                'description' => 'Updated profile image',
                'options' => [
                    'entity_type' => 'profile',
                ],
            ];
        }

        // Company logo updates
        if ($method === 'POST' && str_contains($path, 'upload/company-logo')) {
            return [
                'type' => 'company_logo_updated',
                'description' => 'Updated company logo',
                'options' => [
                    'entity_type' => 'profile',
                ],
            ];
        }

        // Settings changes
        if ($method === 'PUT' && str_contains($path, 'settings')) {
            return [
                'type' => 'settings_changed',
                'description' => 'Updated account settings',
                'options' => [
                    'entity_type' => 'settings',
                    'new_values' => $request->except(['password', 'password_confirmation']),
                ],
            ];
        }

        return null;
    }

    /**
     * Fallback auto-mapper for admin routes.
     * Uses route name + HTTP method to infer action_type + description
     * + entity_type / entity_id from URL segments.
     */
    private function getFallbackAdminMapping(string $method, string $path, Request $request): ?array
    {
        if (!str_starts_with($path, 'api/admin/') && !str_starts_with($path, 'admin/')) {
            return null;
        }

        // Strip admin prefix
        $cleanPath = preg_replace('#^/?api/admin/?#', '', $path);
        if ($cleanPath === null) {
            return null;
        }
        $segments = explode('/', trim($cleanPath, '/'));
        if ($segments === []) {
            return null;
        }

        $first = $segments[0];

        // Known mutating admin entity routes
        $mutations = [
            'POST' => ['verb' => 'Created', 'type_prefix' => 'admin_create'],
            'PUT'  => ['verb' => 'Updated', 'type_prefix' => 'admin_update'],
            'PATCH' => ['verb' => 'Updated', 'type_prefix' => 'admin_update'],
            'DELETE' => ['verb' => 'Deleted', 'type_prefix' => 'admin_delete'],
        ];

        // Admin dashboards / stat pages are GET-only — no audit
        if (!isset($mutations[$method]) && !in_array($first, ['notifications', 'payments', 'invoices', 'plan-prices'], true)) {
            // For POST/PUT/DELETE of other routes, fall through to the mutation handling below.
            // For GET routes that don't need audit:
            if ($method === 'GET' || $method === 'HEAD' || $method === 'OPTIONS') {
                return null;
            }
        }

        if (isset($mutations[$method])) {
            $verb = $mutations[$method]['verb'];
            $prefix = $mutations[$method]['type_prefix'];

            $entityType = $this->adminSegmentToEntity($first);
            $entityId = $segments[1] ?? null;
            if (!ctype_digit((string) $entityId)) {
                $entityId = null;
            }

            // Sub-entity action e.g. admin/users/123/reset-password → verb includes sub
            $subAction = null;
            if (count($segments) >= 3 && ctype_digit((string) ($segments[1] ?? ''))) {
                $subAction = implode(' ', array_slice($segments, 2));
            }

            $action = $verb . ' ' . ucwords(str_replace('_', ' ', $entityType));
            if ($subAction) {
                $action .= ' → ' . ucwords(str_replace(['-', '_'], ' ', $subAction));
            }

            $type = $prefix . '_' . $entityType;
            if ($subAction) {
                $type .= '_' . str_replace(['-', ' '], '_', strtolower($subAction));
            }

            $sensitiveFields = ['password', 'password_confirmation', 'current_password', 'secret', 'two_factor_secret'];

            return [
                'type' => $type,
                'description' => $action,
                'options' => [
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'new_values' => $method !== 'DELETE' ? $request->except($sensitiveFields) : null,
                    'metadata' => [
                        'admin_path' => $cleanPath,
                        'route_segments' => $segments,
                    ],
                ],
            ];
        }

        // Admin notification subactions (approve/reject etc.) already have specific POST prefix
        return null;
    }

    /**
     * Convert first admin URL segment → entity_type label for activity logs.
     */
    private function adminSegmentToEntity(string $segment): string
    {
        return match ($segment) {
            'users' => 'user',
            'business-users' => 'business_user',
            'business_user_assignments' => 'business_user',
            'nfc-cards' => 'nfc_card',
            'legal' => 'legal_document',
            'notifications' => 'admin_notification',
            'chatbot' => 'chatbot_item',
            'payments' => 'payment',
            'manual-transfers' => 'payment',
            'refunds' => 'refund',
            'invoices' => 'invoice',
            'plan-prices' => 'plan_price',
            default => str_replace(['-', ' '], '_', strtolower($segment)),
        };
    }

    /**
     * Get old profile values (would need to fetch from database)
     */
    private function getOldProfileValues($request)
    {
        // In a real implementation, you would fetch the current values from the database
        // before they are updated
        return [];
    }
}
