<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get user notifications
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $limit = $request->input('limit', 50);
        $unreadOnly = $request->boolean('unread_only', false);

        $query = $user->notifications()
            ->orderBy('pinned', 'desc') // Pinned notifications first
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($unreadOnly) {
            $query->where('is_read', false);
        }

        $notifications = $query->get();
        $unreadCount = $this->notificationService->getUnreadCount($user);

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ],
        ]);
    }

    /**
     * Get unread count
     */
    public function getUnreadCount()
    {
        $user = auth()->user();
        $count = $this->notificationService->getUnreadCount($user);

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count,
            ],
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $user = auth()->user();
        $notification = $user->notifications()->findOrFail($id);

        $this->notificationService->markAsRead($notification);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead()
    {
        $user = auth()->user();
        $this->notificationService->markAllAsRead($user);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $notification = $user->notifications()->findOrFail($id);

        $this->notificationService->delete($notification);

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead()
    {
        $user = auth()->user();
        
        $user->notifications()
            ->where('is_read', true)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'All read notifications deleted',
        ]);
    }

    /**
     * Create notification (for order requests)
     */
    public function store(Request $request)
    {
        \Log::info('Notification store called', [
            'user' => auth()->user()?->id,
            'authenticated' => auth()->check(),
        ]);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:registration_success,email_verification,login_new_device,profile_updated,link_milestone,landing_page_viewed,contact_request,subscription_upgrade,payment_successful,payment_failed,account_warning,password_changed,nfc_card_purchased,nfc_card_delivered,nfc_card_linked,nfc_card_activated,nfc_card_expired,app_update,system_message,admin_announcement,business_card_order_request,account_created,order_rejected',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'sticky' => 'nullable|boolean',
            'data' => 'nullable|array',
        ]);

        try {
            \Log::info('Creating notification', ['validated' => $validated]);

            // Create notification directly without service
            $notification = \App\Models\Notification::create([
                'user_id' => $validated['user_id'],
                'type' => $validated['type'],
                'title' => $validated['title'],
                'message' => $validated['message'],
                'data' => $validated['data'] ?? [],
                'priority' => $validated['priority'] ?? 'normal',
                'sticky' => $validated['sticky'] ?? false,
                'pinned' => $validated['sticky'] ?? false,
            ]);

            \Log::info('Notification created successfully', ['notification_id' => $notification->id]);

            return response()->json([
                'success' => true,
                'message' => 'Notification created successfully',
                'data' => $notification,
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error creating notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Notification creation error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create notification: ' . $e->getMessage(),
            ], 500);
        }
    }
}
