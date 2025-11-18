# Admin Order Approval Workflow - Setup Guide

## Overview
This document outlines the complete setup for the Admin Order Approval workflow for NFC card orders.

## What Changed

### Frontend
1. **BusinessPlanNFCCard.vue** - Modified `confirmOrder()` function to send notifications instead of direct save
2. **notifications.vue** - Added Approve/Reject buttons and modal for order management

### Backend
1. **Notification Model** - Added approval fields and methods
2. **NotificationController** - Added `store()` method to create notifications
3. **AdminNotificationController** - Added `approveOrder()` and `rejectOrder()` methods
4. **Routes** - Added new endpoints for notification creation and approval
5. **Database Migration** - Added approval-related columns

## Setup Instructions

### Step 1: Run Database Migration
```bash
cd backend
php artisan migrate
```

This will add the following columns to the `notifications` table:
- `is_approved` (boolean)
- `is_rejected` (boolean)
- `rejection_reason` (text)
- `approved_at` (timestamp)
- `rejected_at` (timestamp)
- `approved_by` (foreign key to users)
- `rejected_by` (foreign key to users)
- `sticky` (boolean)

And update the `type` enum to include:
- `business_card_order_request`
- `business_bulk_order_placed`

### Step 2: Clear Route Cache (if applicable)
```bash
php artisan route:cache --clear
```

### Step 3: Test the Workflow

#### User Side:
1. Navigate to Business Plan NFC Card Design page
2. Fill in employee information
3. Click "Place Order"
4. Confirm the order
5. Should see: "✅ Order submitted successfully! Your request has been sent to the Super Admin for approval."

#### Admin Side:
1. Navigate to Admin Management > Notifications
2. Look for notifications with type "Business Card Order Request" (marked as urgent/red)
3. Click "Approve" to approve the order
4. Or click "Reject" to reject with a reason
5. Once processed, the notification will show status badge (✓ Approved or ✗ Rejected)

## API Endpoints

### Create Notification
```
POST /api/notifications
Authorization: Bearer {token}

{
  "user_id": 1,
  "type": "business_card_order_request",
  "title": "New NFC Card Order from John Doe",
  "message": "John Doe has requested a new NFC card. Please review and approve or reject the order.",
  "priority": "urgent",
  "sticky": true,
  "data": {
    "name": "John Doe",
    "position": "Manager",
    "email": "john@example.com",
    ...
  }
}
```

### Approve Order
```
POST /api/admin/notifications/{id}/approve
Authorization: Bearer {token}
```

### Reject Order
```
POST /api/admin/notifications/{id}/reject
Authorization: Bearer {token}

{
  "rejection_reason": "Reason for rejection"
}
```

## Troubleshooting

### 403 Permission Denied Error
- Ensure user is authenticated
- Check that the route is within the `auth` middleware group
- Verify user has proper permissions

### Notification Not Appearing
- Check that Super Admin user exists in database
- Verify notification was created in database
- Check browser console for API errors

### Migration Issues
- Ensure all previous migrations have run successfully
- Check database connection
- Verify migration file syntax

## Database Schema

### notifications table (new columns)
```sql
ALTER TABLE notifications ADD COLUMN is_approved BOOLEAN DEFAULT FALSE;
ALTER TABLE notifications ADD COLUMN is_rejected BOOLEAN DEFAULT FALSE;
ALTER TABLE notifications ADD COLUMN rejection_reason TEXT NULL;
ALTER TABLE notifications ADD COLUMN approved_at TIMESTAMP NULL;
ALTER TABLE notifications ADD COLUMN rejected_at TIMESTAMP NULL;
ALTER TABLE notifications ADD COLUMN approved_by BIGINT UNSIGNED NULL;
ALTER TABLE notifications ADD COLUMN rejected_by BIGINT UNSIGNED NULL;
ALTER TABLE notifications ADD COLUMN sticky BOOLEAN DEFAULT FALSE;
ALTER TABLE notifications MODIFY type ENUM(..., 'business_card_order_request', 'business_bulk_order_placed');
```

## Files Modified

### Frontend
- `frontend/pages/UserDashboard/NFCCardDesign/BusinessPlanNFCCard.vue`
- `frontend/pages/AdminManagement/notifications.vue`

### Backend
- `backend/app/Models/Notification.php`
- `backend/app/Http/Controllers/Api/NotificationController.php`
- `backend/app/Http/Controllers/Api/AdminNotificationController.php`
- `backend/routes/api.php`
- `backend/database/migrations/2025_11_18_000000_add_approval_fields_to_notifications.php`

## Workflow Summary

```
User Places Order
    ↓
Send Notification to Super Admin (urgent, sticky)
    ↓
Admin Sees Notification in Admin Panel
    ↓
Admin Approves/Rejects
    ↓
User Gets Notification of Status
    ↓
If Approved: NFC Card Created
If Rejected: Order Cancelled with Reason
```
