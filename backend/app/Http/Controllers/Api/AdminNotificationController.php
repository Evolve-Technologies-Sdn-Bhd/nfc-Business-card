<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Models\User;
use App\Models\Notification;
use App\Models\NfcCard;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            ->orderBy('pinned', 'desc') // Pinned notifications first
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

    /**
     * Approve order request
     */
    public function approveOrder($id)
    {
        $notification = Notification::findOrFail($id);

        // Validate notification type
        if ($notification->type !== 'business_card_order_request') {
            return response()->json([
                'success' => false,
                'message' => 'This notification is not an order request',
            ], 400);
        }

        // Check if already processed
        if ($notification->is_approved || $notification->is_rejected) {
            return response()->json([
                'success' => false,
                'message' => 'This order has already been processed',
            ], 400);
        }

        // Approve the notification
        $notification->approve(auth()->id());
        \Log::info('Notification approved', ['notification_id' => $notification->id]);

        // Get order data
        $orderData = $notification->data ?? [];
        
        // Get the user who placed the order (from notification data)
        $requestingUserId = $orderData['requesting_user_id'] ?? $notification->user_id;
        \Log::info('Requesting user ID', ['requesting_user_id' => $requestingUserId, 'notification_user_id' => $notification->user_id]);
        
        // Get user and order data
        $user = User::find($requestingUserId);
        if (!$user) {
            \Log::error('User not found', ['user_id' => $requestingUserId]);
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }
        \Log::info('User found', ['user_id' => $user->id, 'name' => $user->full_name]);

        // Create NFC card from order data
        \Log::info('Order data', ['order_data' => $orderData]);
        $businessAccountId = null;
        
        // Determine subscription plan
        $subscriptionPlan = $orderData['subscription_plan'] ?? 'premium';
        \Log::info('Subscription plan determined', ['plan' => $subscriptionPlan]);
        
        // Determine business account ID if it's a business plan
        if ($subscriptionPlan === 'business') {
            // Check if user is a business account or employee
            if (method_exists($user, 'isBusinessAccount') && $user->isBusinessAccount()) {
                $businessAccountId = $user->id;
                \Log::info('User is business account', ['business_account_id' => $businessAccountId]);
            } elseif (method_exists($user, 'isBusinessEmployee') && $user->isBusinessEmployee()) {
                $businessAccountId = $user->parent_business_id;
                \Log::info('User is business employee', ['business_account_id' => $businessAccountId]);
            } else {
                // If methods don't exist, check parent_business_id directly
                $businessAccountId = $user->parent_business_id ?? $user->id;
                \Log::info('Using fallback business account ID', ['business_account_id' => $businessAccountId]);
            }
        }

        try {
            // Generate unique NFC card ID
            $nfcCardId = 'NFC-' . strtoupper(Str::random(12));
            \Log::info('NFC card ID generated', ['nfc_card_id' => $nfcCardId]);

            // Create the NFC card
            \Log::info('Creating NFC card with data', [
                'user_id' => $user->id,
                'business_account_id' => $businessAccountId,
                'nfc_card_id' => $nfcCardId,
                'card_owner' => $orderData['name'] ?? $user->full_name,
                'subscription_plan' => $subscriptionPlan,
            ]);
            
            $nfcCard = NfcCard::create([
                'user_id' => $user->id,
                'business_account_id' => $businessAccountId,
                'nfc_card_id' => $nfcCardId,
                'card_owner' => $orderData['name'] ?? $user->full_name,
                'billing_address' => $orderData['business_address'] ?? $orderData['email'] ?? '',
                'contact_number' => $orderData['contact_number'] ?? '',
                'purchase_date' => now(),
                'subscription_plan' => $subscriptionPlan,
                'purchase_amount' => $orderData['purchase_amount'] ?? 0,
                'payment_method' => 'Admin Approved',
                'shipping_address' => $orderData['delivery_address'] ?? '',
                'notes' => 'Auto-created from order approval: ' . $notification->id,
            ]);
            
            \Log::info('NFC card created successfully', ['nfc_card_id' => $nfcCard->id, 'nfc_card_number' => $nfcCardId]);

            // Send confirmation notification to user
            $this->notificationService->create(
                $user,
                'nfc_card_purchased',
                [
                    'card_id' => $nfcCardId,
                    'order_number' => $notification->id,
                    'amount' => '$' . number_format($orderData['purchase_amount'] ?? 0, 2),
                    'plan' => ucfirst($subscriptionPlan),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Order approved successfully and NFC card created',
                'data' => [
                    'notification_id' => $notification->id,
                    'nfc_card_id' => $nfcCard->id,
                    'nfc_card_number' => $nfcCardId,
                    'is_approved' => $notification->is_approved,
                    'approved_at' => $notification->approved_at,
                ],
            ]);
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Error creating NFC card on approval: ' . $e->getMessage(), [
                'notification_id' => $notification->id,
                'user_id' => $user->id,
                'order_data' => $orderData,
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Order approved but failed to create NFC card: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject order request
     */
    public function rejectOrder(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $notification = Notification::findOrFail($id);

        // Validate notification type
        if ($notification->type !== 'business_card_order_request') {
            return response()->json([
                'success' => false,
                'message' => 'This notification is not an order request',
            ], 400);
        }

        // Check if already processed
        if ($notification->is_approved || $notification->is_rejected) {
            return response()->json([
                'success' => false,
                'message' => 'This order has already been processed',
            ], 400);
        }

        // Reject the notification
        $notification->reject($validated['rejection_reason'], auth()->id());

        // Send rejection notification to user
        $user = User::find($notification->user_id);
        if ($user) {
            $this->notificationService->create(
                $user,
                'payment_failed',
                [
                    'amount' => $notification->data['purchase_amount'] ?? 'N/A',
                    'reason' => $validated['rejection_reason'],
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Order rejected successfully',
            'data' => [
                'notification_id' => $notification->id,
                'is_rejected' => $notification->is_rejected,
                'rejection_reason' => $notification->rejection_reason,
                'rejected_at' => $notification->rejected_at,
            ],
        ]);
    }
}
