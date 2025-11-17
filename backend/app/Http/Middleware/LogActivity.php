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

        $user = auth()->user();

        // Only log for Business accounts and their employees
        if (!$user->isBusinessAccount() && !$user->parent_business_id) {
            return $response;
        }

        // Only log successful responses (2xx)
        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
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

        if ($actionMapping) {
            ActivityLog::logActivity(
                $user,
                $actionMapping['type'],
                $actionMapping['description'],
                $actionMapping['options'] ?? []
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
     * Get old profile values (would need to fetch from database)
     */
    private function getOldProfileValues($request)
    {
        // In a real implementation, you would fetch the current values from the database
        // before they are updated
        return [];
    }
}
