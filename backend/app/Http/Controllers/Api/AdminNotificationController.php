<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get all notifications (Admin view)
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 50);
        $type = $request->input('type');
        $userId = $request->input('user_id');

        $query = Notification::with('user')
            ->orderBy('created_at', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $notifications = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * Send announcement to all users
     */
    public function sendAnnouncement(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'action_url' => 'nullable|url',
            'action_text' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:10',
        ]);

        $notifications = $this->notificationService->broadcast('admin_announcement', [
            'announcement_title' => $validated['title'],
            'announcement_message' => $validated['message'],
            'priority' => $validated['priority'],
            'action_url' => $validated['action_url'] ?? null,
            'action_text' => $validated['action_text'] ?? null,
            'icon' => $validated['icon'] ?? '📢',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Announcement sent to all users',
            'data' => [
                'notifications_sent' => count($notifications),
            ],
        ]);
    }

    /**
     * Send announcement to specific users
     */
    public function sendToUsers(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'action_url' => 'nullable|url',
            'action_text' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:10',
        ]);

        $notifications = $this->notificationService->createForMultiple(
            $validated['user_ids'],
            'admin_announcement',
            [
                'announcement_title' => $validated['title'],
                'announcement_message' => $validated['message'],
                'priority' => $validated['priority'],
                'action_url' => $validated['action_url'] ?? null,
                'action_text' => $validated['action_text'] ?? null,
                'icon' => $validated['icon'] ?? '📢',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Notification sent to selected users',
            'data' => [
                'notifications_sent' => count($notifications),
            ],
        ]);
    }

    /**
     * Send system message
     */
    public function sendSystemMessage(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'broadcast' => 'boolean',
        ]);

        $broadcast = $validated['broadcast'] ?? false;

        if ($broadcast) {
            $notifications = $this->notificationService->broadcast('system_message', [
                'system_message' => $validated['message'],
                'priority' => $validated['priority'],
            ]);
        } elseif (!empty($validated['user_ids'])) {
            $notifications = $this->notificationService->createForMultiple(
                $validated['user_ids'],
                'system_message',
                [
                    'system_message' => $validated['message'],
                    'priority' => $validated['priority'],
                ]
            );
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Please specify user_ids or set broadcast to true',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'System message sent',
            'data' => [
                'notifications_sent' => count($notifications),
            ],
        ]);
    }

    /**
     * Get notification statistics
     */
    public function getStatistics()
    {
        $stats = [
            'total_notifications' => Notification::count(),
            'total_unread' => Notification::where('is_read', false)->count(),
            'total_read' => Notification::where('is_read', true)->count(),
            'by_type' => Notification::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->get(),
            'by_priority' => Notification::selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->get(),
            'recent_7_days' => Notification::where('created_at', '>=', now()->subDays(7))
                ->count(),
            'today' => Notification::whereDate('created_at', today())->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Delete notification (Admin)
     */
    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }

    /**
     * Delete old notifications
     */
    public function cleanupOldNotifications(Request $request)
    {
        $days = $request->input('days', 30);
        
        $deleted = Notification::where('created_at', '<', now()->subDays($days))
            ->where('is_read', true)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} old notifications",
            'data' => [
                'deleted_count' => $deleted,
            ],
        ]);
    }
}
