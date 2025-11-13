# Notification System Documentation

## Overview

Complete notification system for user accounts and admin management with 15 notification types.

---

## Backend Structure

### Database Migration

Location: `backend/database/migrations/2025_01_12_000001_create_notifications_table.php`

**Table Schema:**

```sql
notifications
├── id
├── user_id (foreign key to users)
├── type (enum: 15 types)
├── title
├── message
├── data (json)
├── is_read (boolean)
├── read_at (timestamp)
├── priority (enum: low, normal, high, urgent)
├── action_url
├── action_text
├── icon
├── created_at
└── updated_at
```

**Run Migration:**

```bash
cd backend
php artisan migrate
```

---

## Notification Types

### 1. Registration Success

```php
$notificationService->create($user, 'registration_success');
```

### 2. Email Verification

```php
$notificationService->create($user, 'email_verification');
```

### 3. Login from New Device

```php
$notificationService->create($user, 'login_new_device', [
    'device' => 'Chrome on Windows',
    'ip' => '192.168.1.1'
]);
```

### 4. Profile Updated

```php
$notificationService->create($user, 'profile_updated');
```

### 5. Link Milestone

```php
$notificationService->create($user, 'link_milestone', [
    'milestone' => '1000'
]);
```

### 6. Landing Page Viewed

```php
$notificationService->create($user, 'landing_page_viewed', [
    'count' => '50'
]);
```

### 7. Contact Request

```php
$notificationService->create($user, 'contact_request', [
    'name' => 'John Doe'
]);
```

### 8. Subscription Upgrade

```php
$notificationService->create($user, 'subscription_upgrade', [
    'plan' => 'Premium'
]);
```

### 9. Payment Successful

```php
$notificationService->create($user, 'payment_successful', [
    'amount' => '$29.99'
]);
```

### 10. Payment Failed

```php
$notificationService->create($user, 'payment_failed', [
    'amount' => '$29.99',
    'priority' => 'urgent'
]);
```

### 11. Account Warning

```php
$notificationService->create($user, 'account_warning', [
    'warning_message' => 'Your account will expire in 3 days',
    'priority' => 'urgent'
]);
```

### 12. Password Changed

```php
$notificationService->create($user, 'password_changed');
```

### 13. NFC Card Purchased

```php
$notificationService->create($user, 'nfc_card_purchased', [
    'order_number' => 'ORD-12345'
]);
```

### 14. NFC Card Delivered

```php
$notificationService->create($user, 'nfc_card_delivered', [
    'order_number' => 'ORD-12345'
]);
```

### 15. NFC Card Activated

```php
$notificationService->create($user, 'nfc_card_activated');
```

---

## Backend API Endpoints

### User Endpoints

#### Get Notifications

```
GET /api/notifications
Query Parameters:
  - limit (default: 50)
  - unread_only (boolean)

Response:
{
  "success": true,
  "data": {
    "notifications": [...],
    "unread_count": 5
  }
}
```

#### Get Unread Count

```
GET /api/notifications/unread-count

Response:
{
  "success": true,
  "data": {
    "unread_count": 5
  }
}
```

#### Mark as Read

```
POST /api/notifications/{id}/mark-read

Response:
{
  "success": true,
  "message": "Notification marked as read"
}
```

#### Mark All as Read

```
POST /api/notifications/mark-all-read

Response:
{
  "success": true,
  "message": "All notifications marked as read"
}
```

#### Delete Notification

```
DELETE /api/notifications/{id}

Response:
{
  "success": true,
  "message": "Notification deleted"
}
```

#### Delete All Read

```
DELETE /api/notifications/read/all

Response:
{
  "success": true,
  "message": "All read notifications deleted"
}
```

---

### Admin Endpoints

#### Get All Notifications

```
GET /api/admin/notifications
Query Parameters:
  - type (filter by notification type)
  - user_id (filter by user)
  - per_page (default: 50)

Response:
{
  "success": true,
  "data": {
    "data": [...],
    "current_page": 1,
    "total": 100
  }
}
```

#### Send Announcement to All Users

```
POST /api/admin/notifications/announcement

Body:
{
  "title": "System Maintenance",
  "message": "We will be performing maintenance...",
  "priority": "high",
  "action_url": "https://...",
  "action_text": "Learn More",
  "icon": "📢"
}

Response:
{
  "success": true,
  "message": "Announcement sent to all users",
  "data": {
    "notifications_sent": 1523
  }
}
```

#### Send to Specific Users

```
POST /api/admin/notifications/send-to-users

Body:
{
  "user_ids": [1, 2, 3],
  "title": "Special Offer",
  "message": "You've been selected...",
  "priority": "normal"
}

Response:
{
  "success": true,
  "message": "Notification sent to selected users",
  "data": {
    "notifications_sent": 3
  }
}
```

#### Send System Message

```
POST /api/admin/notifications/system-message

Body:
{
  "message": "Your account will expire soon",
  "priority": "urgent",
  "broadcast": true
}
// OR
{
  "message": "Your subscription is active",
  "priority": "normal",
  "user_ids": [1, 2, 3]
}

Response:
{
  "success": true,
  "message": "System message sent",
  "data": {
    "notifications_sent": 100
  }
}
```

#### Get Statistics

```
GET /api/admin/notifications/statistics

Response:
{
  "success": true,
  "data": {
    "total_notifications": 1523,
    "total_unread": 342,
    "total_read": 1181,
    "by_type": [...],
    "by_priority": [...],
    "recent_7_days": 156,
    "today": 23
  }
}
```

#### Delete Notification (Admin)

```
DELETE /api/admin/notifications/{id}

Response:
{
  "success": true,
  "message": "Notification deleted"
}
```

#### Cleanup Old Notifications

```
POST /api/admin/notifications/cleanup

Body:
{
  "days": 30
}

Response:
{
  "success": true,
  "message": "Deleted 450 old notifications",
  "data": {
    "deleted_count": 450
  }
}
```

---

## Frontend Usage

### User Dashboard Integration

**Update UserDashboard.vue:**

```vue
<script setup>
import { useNotifications } from "~/composables/useNotifications";

const {
  notifications,
  unreadCount,
  loadNotifications,
  loadUnreadCount,
  markAsRead,
  markAllAsRead,
  getTimeAgo,
  getNotificationIcon,
} = useNotifications();

// Load on mount
onMounted(() => {
  loadNotifications();

  // Auto-refresh unread count every 30 seconds
  setInterval(() => {
    loadUnreadCount();
  }, 30000);
});
</script>

<template>
  <!-- Notification Bell Icon -->
  <button @click="showNotifications = !showNotifications" class="relative">
    <Icon name="heroicons:bell" class="h-6 w-6" />
    <span
      v-if="unreadCount > 0"
      class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
    >
      {{ unreadCount > 9 ? "9+" : unreadCount }}
    </span>
  </button>

  <!-- Notifications Dropdown -->
  <div v-if="showNotifications" class="notifications-dropdown">
    <div v-for="notification in notifications" :key="notification.id">
      <div @click="markAsRead(notification.id)">
        <span>{{ getNotificationIcon(notification.type) }}</span>
        <div>
          <h4>{{ notification.title }}</h4>
          <p>{{ notification.message }}</p>
          <span>{{ getTimeAgo(notification.created_at) }}</span>
        </div>
      </div>
    </div>

    <button @click="markAllAsRead">Mark All as Read</button>
  </div>
</template>
```

### Admin Dashboard Integration

**Access Admin Notification Management:**

```
/AdminManagement/notifications
```

**Features:**

- View all notifications
- Filter by type
- Send announcements to all users
- Send notifications to specific users
- View statistics
- Cleanup old notifications

---

## Usage Examples

### Example 1: Send notification when user registers

```php
// In AuthController@register
use App\Services\NotificationService;

public function register(Request $request, NotificationService $notificationService)
{
    $user = User::create([...]);

    // Send welcome notification
    $notificationService->create($user, 'registration_success');

    return response()->json([...]);
}
```

### Example 2: Send notification when payment succeeds

```php
// In PaymentController@handleWebhook
use App\Services\NotificationService;

public function handleWebhook(Request $request, NotificationService $notificationService)
{
    $user = User::find($userId);

    $notificationService->create($user, 'payment_successful', [
        'amount' => '$29.99',
        'action_url' => '/user/billing',
        'action_text' => 'View Invoice'
    ]);
}
```

### Example 3: Send NFC card status notification

```php
// In NfcCardController@updateStatus
use App\Services\NotificationService;

public function updateStatus($cardId, NotificationService $notificationService)
{
    $card = NfcCard::find($cardId);

    if ($card->status === 'delivered') {
        $notificationService->create($card->user, 'nfc_card_delivered', [
            'order_number' => $card->order_number
        ]);
    }
}
```

### Example 4: Admin sends announcement

```php
// Via API or Admin Panel
POST /api/admin/notifications/announcement
{
  "title": "New Feature Released!",
  "message": "Check out our new analytics dashboard with real-time insights.",
  "priority": "normal",
  "action_url": "/user/analytics",
  "action_text": "Try Now"
}
```

---

## Scheduled Tasks (Optional)

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Cleanup old notifications daily
    $schedule->call(function () {
        app(NotificationService::class)->deleteOldNotifications(30);
    })->daily();

    // Send daily summary notifications
    $schedule->call(function () {
        // Your logic here
    })->dailyAt('09:00');
}
```

---

## Best Practices

1. **Always use NotificationService** instead of creating notifications directly
2. **Set appropriate priority** for urgent notifications
3. **Include action URLs** when users need to take action
4. **Cleanup old notifications** regularly to keep database clean
5. **Don't spam users** - combine similar notifications when possible
6. **Test notifications** before broadcasting to all users

---

## Testing

### Test Notification Creation

```bash
php artisan tinker

$user = User::first();
$service = app(\App\Services\NotificationService::class);
$service->create($user, 'payment_successful', ['amount' => '$99.99']);
```

### Test Broadcast

```bash
$service = app(\App\Services\NotificationService::class);
$service->broadcast('admin_announcement', [
    'announcement_title' => 'Test',
    'announcement_message' => 'This is a test'
]);
```

---

## Summary

✅ **15 Notification Types** covering all user activities
✅ **User API** for viewing and managing notifications  
✅ **Admin API** for sending announcements and system messages
✅ **Priority System** (low, normal, high, urgent)
✅ **Action Links** for notifications requiring user action
✅ **Auto-cleanup** for old read notifications
✅ **Real-time Unread Count** for user dashboard
✅ **Admin Statistics Dashboard** for monitoring

The notification system is now ready for production use!
