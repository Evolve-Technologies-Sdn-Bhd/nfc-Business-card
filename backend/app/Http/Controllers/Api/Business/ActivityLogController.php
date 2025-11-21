<?php

namespace App\Http\Controllers\Api\Business;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    /**
     * Get activity logs for Business Admin's employees
     * Business Admin can only view, not delete
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Validate user is Business account
        if (!$user->isBusinessAccount()) {
            return response()->json([
                'success' => false,
                'message' => 'Only Business account owners can view activity logs'
            ], 403);
        }

        // Query parameters
        $perPage = $request->input('per_page', 50);
        $employeeId = $request->input('employee_id');
        $actionType = $request->input('action_type');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $search = $request->input('search');

        // Build query - get activities for this Business account
        $query = ActivityLog::with(['user:id,first_name,last_name,email,job_title'])
            ->forBusinessAccount($user->id)
            ->orderBy('created_at', 'desc');

        // Filter by specific employee
        if ($employeeId) {
            $query->where('user_id', $employeeId);
        }

        // Filter by action type
        if ($actionType) {
            $query->where('action_type', $actionType);
        }

        // Filter by date range
        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        } elseif ($startDate) {
            $query->where('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        // Search in description
        if ($search) {
            $query->where('action_description', 'like', "%{$search}%");
        }

        $logs = $query->paginate($perPage);

        // Add change summary to each log
        $logs->getCollection()->transform(function ($log) {
            return [
                'id' => $log->id,
                'user' => [
                    'id' => $log->user->id,
                    'name' => $log->user->full_name,
                    'email' => $log->user->email,
                    'job_title' => $log->user->job_title,
                ],
                'action_type' => $log->action_type,
                'action_description' => $log->action_description,
                'entity_type' => $log->entity_type,
                'entity_id' => $log->entity_id,
                'changes' => $log->change_summary,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'created_at' => $log->created_at->toDateTimeString(),
                'created_at_human' => $log->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'current_page' => $logs->currentPage(),
            'last_page' => $logs->lastPage(),
            'per_page' => $logs->perPage(),
            'total' => $logs->total(),
        ]);
    }

    /**
     * Get activity log statistics
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics(Request $request)
    {
        $user = auth()->user();

        if (!$user->isBusinessAccount()) {
            return response()->json([
                'success' => false,
                'message' => 'Only Business account owners can view statistics'
            ], 403);
        }

        $days = $request->input('days', 30);
        $startDate = Carbon::now()->subDays($days);
        $todayStart = Carbon::today();

        // Get most common action type
        $mostCommonAction = ActivityLog::forBusinessAccount($user->id)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('action_type, COUNT(*) as count')
            ->groupBy('action_type')
            ->orderBy('count', 'desc')
            ->first();

        // Get most active employee
        $mostActiveEmployee = ActivityLog::forBusinessAccount($user->id)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('user_id, COUNT(*) as count')
            ->groupBy('user_id')
            ->orderBy('count', 'desc')
            ->with('user:id,first_name,last_name')
            ->first();

        $stats = [
            'total_logs' => ActivityLog::forBusinessAccount($user->id)->count(),
            'today_count' => ActivityLog::forBusinessAccount($user->id)
                ->where('created_at', '>=', $todayStart)
                ->count(),
            'recent_activities' => ActivityLog::forBusinessAccount($user->id)
                ->where('created_at', '>=', $startDate)
                ->count(),
            'most_active_employee' => $mostActiveEmployee ? [
                'name' => $mostActiveEmployee->user->full_name,
                'count' => $mostActiveEmployee->count,
            ] : null,
            'most_common_action' => $mostCommonAction ? [
                'type' => $mostCommonAction->action_type,
                'count' => $mostCommonAction->count,
            ] : null,
            'by_action_type' => ActivityLog::forBusinessAccount($user->id)
                ->where('created_at', '>=', $startDate)
                ->selectRaw('action_type, COUNT(*) as count')
                ->groupBy('action_type')
                ->get()
                ->pluck('count', 'action_type'),
            'by_employee' => ActivityLog::forBusinessAccount($user->id)
                ->where('created_at', '>=', $startDate)
                ->with('user:id,first_name,last_name')
                ->get()
                ->groupBy('user_id')
                ->map(function ($activities, $userId) {
                    $user = $activities->first()->user;
                    return [
                        'employee_name' => $user->full_name,
                        'count' => $activities->count(),
                    ];
                })
                ->values(),
            'recent_7_days' => $this->getActivityTrend($user->id, 7),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get activity trend for last N days
     */
    private function getActivityTrend($businessAccountId, $days)
    {
        $trend = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $count = ActivityLog::forBusinessAccount($businessAccountId)
                ->whereDate('created_at', $date)
                ->count();
            
            $trend[] = [
                'date' => $date,
                'count' => $count,
            ];
        }
        return $trend;
    }

    /**
     * Get available action types for filtering
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function actionTypes()
    {
        $user = auth()->user();

        if (!$user->isBusinessAccount()) {
            return response()->json([
                'success' => false,
                'message' => 'Only Business account owners can access this'
            ], 403);
        }

        $actionTypes = [
            'login' => 'User Login',
            'logout' => 'User Logout',
            'profile_updated' => 'Profile Updated',
            'password_changed' => 'Password Changed',
            'email_changed' => 'Email Changed',
            'landing_page_updated' => 'Landing Page Updated',
            'landing_page_created' => 'Landing Page Created',
            'nfc_card_activated' => 'NFC Card Activated',
            'nfc_card_deactivated' => 'NFC Card Deactivated',
            'link_added' => 'Link Added',
            'link_updated' => 'Link Updated',
            'link_deleted' => 'Link Deleted',
            'link_reordered' => 'Links Reordered',
            'profile_image_updated' => 'Profile Image Updated',
            'company_logo_updated' => 'Company Logo Updated',
            'social_links_updated' => 'Social Links Updated',
            'contact_info_updated' => 'Contact Info Updated',
            'card_design_updated' => 'Card Design Updated',
            'settings_changed' => 'Settings Changed',
        ];

        return response()->json([
            'success' => true,
            'data' => $actionTypes,
        ]);
    }

    /**
     * Get single activity log detail
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = auth()->user();

        if (!$user->isBusinessAccount()) {
            return response()->json([
                'success' => false,
                'message' => 'Only Business account owners can view activity logs'
            ], 403);
        }

        $log = ActivityLog::with(['user:id,first_name,last_name,email,job_title,phone'])
            ->forBusinessAccount($user->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $log->id,
                'user' => [
                    'id' => $log->user->id,
                    'name' => $log->user->full_name,
                    'email' => $log->user->email,
                    'job_title' => $log->user->job_title,
                    'phone' => $log->user->phone,
                ],
                'action_type' => $log->action_type,
                'action_description' => $log->action_description,
                'entity_type' => $log->entity_type,
                'entity_id' => $log->entity_id,
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
                'changes' => $log->change_summary,
                'metadata' => $log->metadata,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'created_at' => $log->created_at->toDateTimeString(),
                'created_at_human' => $log->created_at->diffForHumans(),
            ],
        ]);
    }

    /**
     * Export activity logs to CSV
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        $user = auth()->user();

        if (!$user->isBusinessAccount()) {
            return response()->json([
                'success' => false,
                'message' => 'Only Business account owners can export activity logs'
            ], 403);
        }

        $employeeId = $request->input('employee_id');
        $actionType = $request->input('action_type');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Build query
        $query = ActivityLog::with(['user:id,first_name,last_name,email'])
            ->forBusinessAccount($user->id)
            ->orderBy('created_at', 'desc');

        if ($employeeId) {
            $query->where('user_id', $employeeId);
        }
        if ($actionType) {
            $query->where('action_type', $actionType);
        }
        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        $logs = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="activity-logs-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, ['Date', 'Time', 'Employee', 'Email', 'Action', 'Description', 'IP Address']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->toDateString(),
                    $log->created_at->toTimeString(),
                    $log->user->full_name,
                    $log->user->email,
                    $log->action_type,
                    $log->action_description,
                    $log->ip_address,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
