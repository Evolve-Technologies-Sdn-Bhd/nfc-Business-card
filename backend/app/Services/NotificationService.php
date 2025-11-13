<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Notification type templates
     */
    private const TEMPLATES = [
        'registration_success' => [
            'title' => 'Welcome to NFC GO!',
            'message' => 'Your account has been successfully created. Start building your digital profile now!',
            'icon' => '🎉',
            'priority' => 'normal',
        ],
        'email_verification' => [
            'title' => 'Verify Your Email',
            'message' => 'Please verify your email address to activate all features.',
            'icon' => '✉️',
            'priority' => 'high',
        ],
        'login_new_device' => [
            'title' => 'New Device Login',
            'message' => 'Your account was accessed from a new device. If this wasn\'t you, please secure your account.',
            'icon' => '🔒',
            'priority' => 'high',
        ],
        'profile_updated' => [
            'title' => 'Profile Updated',
            'message' => 'Your profile has been successfully updated.',
            'icon' => '✅',
            'priority' => 'low',
        ],
        'link_milestone' => [
            'title' => 'Milestone Reached!',
            'message' => 'Congratulations! Your link has reached {milestone} views.',
            'icon' => '🎯',
            'priority' => 'normal',
        ],
        'landing_page_viewed' => [
            'title' => 'Landing Page Activity',
            'message' => 'Your landing page was viewed {count} times today.',
            'icon' => '👀',
            'priority' => 'low',
        ],
        'contact_request' => [
            'title' => 'New Contact Request',
            'message' => 'You have received a new contact request from {name}.',
            'icon' => '📧',
            'priority' => 'normal',
        ],
        'subscription_upgrade' => [
            'title' => 'Subscription Upgraded',
            'message' => 'Your subscription has been upgraded to {plan}. Enjoy premium features!',
            'icon' => '⭐',
            'priority' => 'normal',
        ],
        'payment_successful' => [
            'title' => 'Payment Successful',
            'message' => 'Your payment of {amount} has been processed successfully.',
            'icon' => '💳',
            'priority' => 'normal',
        ],
        'payment_failed' => [
            'title' => 'Payment Failed',
            'message' => 'Your payment of {amount} could not be processed. Please update your payment method.',
            'icon' => '❌',
            'priority' => 'urgent',
        ],
        'account_warning' => [
            'title' => 'Account Warning',
            'message' => '{warning_message}',
            'icon' => '⚠️',
            'priority' => 'urgent',
        ],
        'password_changed' => [
            'title' => 'Password Changed',
            'message' => 'Your password has been successfully changed. If you didn\'t make this change, contact support immediately.',
            'icon' => '🔐',
            'priority' => 'high',
        ],
        'nfc_card_purchased' => [
            'title' => 'NFC Card Order Confirmed',
            'message' => 'Your NFC card order #{order_number} has been confirmed.',
            'icon' => '🛒',
            'priority' => 'normal',
        ],
        'nfc_card_delivered' => [
            'title' => 'NFC Card Delivered',
            'message' => 'Your NFC card #{order_number} has been delivered!',
            'icon' => '📦',
            'priority' => 'normal',
        ],
        'nfc_card_linked' => [
            'title' => 'NFC Card Linked',
            'message' => 'Your NFC card has been successfully linked to your profile.',
            'icon' => '🔗',
            'priority' => 'normal',
        ],
        'nfc_card_activated' => [
            'title' => 'NFC Card Activated',
            'message' => 'Your NFC card is now active and ready to use!',
            'icon' => '✨',
            'priority' => 'normal',
        ],
        'nfc_card_expired' => [
            'title' => 'NFC Card Expired',
            'message' => 'Your NFC card subscription has expired. Renew now to continue using premium features.',
            'icon' => '⏰',
            'priority' => 'high',
        ],
        'app_update' => [
            'title' => 'New Features Available',
            'message' => 'We\'ve released new features! Update now to enjoy the latest improvements.',
            'icon' => '🚀',
            'priority' => 'low',
        ],
        'system_message' => [
            'title' => 'System Message',
            'message' => '{system_message}',
            'icon' => 'ℹ️',
            'priority' => 'normal',
        ],
        'admin_announcement' => [
            'title' => '{announcement_title}',
            'message' => '{announcement_message}',
            'icon' => '📢',
            'priority' => 'normal',
        ],
    ];

    /**
     * Create a notification for a user
     */
    public function create(User $user, string $type, array $data = [])
    {
        $template = self::TEMPLATES[$type] ?? null;

        if (!$template) {
            throw new \InvalidArgumentException("Invalid notification type: {$type}");
        }

        // Replace placeholders in message
        $message = $this->replacePlaceholders($template['message'], $data);
        $title = $this->replacePlaceholders($template['title'], $data);

        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'icon' => $data['icon'] ?? $template['icon'],
            'priority' => $data['priority'] ?? $template['priority'],
            'action_url' => $data['action_url'] ?? null,
            'action_text' => $data['action_text'] ?? null,
        ]);
    }

    /**
     * Create notification for multiple users
     */
    public function createForMultiple(array $userIds, string $type, array $data = [])
    {
        $users = User::whereIn('id', $userIds)->get();
        $notifications = [];

        foreach ($users as $user) {
            $notifications[] = $this->create($user, $type, $data);
        }

        return $notifications;
    }

    /**
     * Broadcast to all users
     */
    public function broadcast(string $type, array $data = [])
    {
        $users = User::all();
        $notifications = [];

        foreach ($users as $user) {
            $notifications[] = $this->create($user, $type, $data);
        }

        return $notifications;
    }

    /**
     * Replace placeholders in text
     */
    private function replacePlaceholders(string $text, array $data)
    {
        foreach ($data as $key => $value) {
            $text = str_replace('{' . $key . '}', $value, $text);
        }

        return $text;
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications(User $user, $limit = 50)
    {
        return $user->notifications()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread count
     */
    public function getUnreadCount(User $user)
    {
        return $user->notifications()
            ->where('is_read', false)
            ->count();
    }

    /**
     * Mark as read
     */
    public function markAsRead(Notification $notification)
    {
        $notification->markAsRead();
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead(User $user)
    {
        $user->notifications()
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Delete notification
     */
    public function delete(Notification $notification)
    {
        $notification->delete();
    }

    /**
     * Delete old notifications
     */
    public function deleteOldNotifications($days = 30)
    {
        Notification::where('created_at', '<', now()->subDays($days))
            ->where('is_read', true)
            ->delete();
    }
}
