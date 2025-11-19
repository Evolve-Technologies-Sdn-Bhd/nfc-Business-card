# 🔴 订单自动拒绝功能 - 重复 Email 检测

## ✅ 功能总览

当 Super Admin 尝试批准包含**已存在 employee emails** 的订单时：
1. ✅ **自动拒绝订单** - 无需 Admin 手动拒绝
2. ✅ **记录拒绝原因** - 详细列出重复的 employees
3. ✅ **发送通知给用户** - 自动通知提交订单的用户
4. ✅ **显示清晰消息** - Admin 看到自动拒绝的提示

---

## 🔄 完整流程

### 场景：订单包含重复 Email

#### 1️⃣ **User 提交订单**

```javascript
{
  cards: [
    { name: "Admin", email: "admin@co.com", is_admin_card: true },
    { name: "John Doe", email: "john@co.com", is_employee_card: true },  // ✅ 新员工
    { name: "Jane Smith", email: "jane@co.com", is_employee_card: true }  // ❌ 已存在
  ]
}
```

#### 2️⃣ **Admin 点击 Approve**

在 Admin 的通知管理页面点击 **Approve** 按钮

#### 3️⃣ **后端自动检测**

```php
// 预验证所有 employee emails
$existingEmployees = [];
foreach ($cards as $cardData) {
    if (($cardData['is_employee_card'] ?? false) && !empty($cardData['email'])) {
        $existingUser = User::where('email', $cardData['email'])->first();
        if ($existingUser) {
            $existingEmployees[] = [
                'name' => $cardData['name'],
                'email' => $cardData['email'],
                'existing_user_id' => $existingUser->id,
            ];
        }
    }
}
```

#### 4️⃣ **自动拒绝订单**

```php
if (count($existingEmployees) > 0) {
    // 构建拒绝原因
    $rejectionReason = "Order automatically rejected: 1 employee email(s) already exist in the system.

Existing employees:
• Jane Smith (jane@co.com)

⚠️ Solution: Please remove these employees from your order or use different email addresses.
Note: Each employee can only have one account in the system.";
    
    // 自动拒绝通知
    $notification->reject($rejectionReason, auth()->id());
    
    // 发送通知给用户
    $this->notificationService->create(
        $user,
        'order_rejected',
        [
            'order_number' => $notification->id,
            'rejection_reason' => $rejectionReason,
            'existing_employees_count' => 1,
            'existing_employees' => $existingEmployees,
        ]
    );
}
```

#### 5️⃣ **Admin 看到消息**

```
🔴 Order Automatically Rejected!

1 employee email(s) already exist:

• Jane Smith (jane@co.com)

✅ The user has been notified with the rejection reason.

💡 Solution: User should remove duplicate employees or use different emails.
```

#### 6️⃣ **User 收到通知**

用户在通知中心收到：

```
Order Rejected

Order Number: #12345
Rejection Reason:

Order automatically rejected: 1 employee email(s) already exist in the system.

Existing employees:
• Jane Smith (jane@co.com)

⚠️ Solution: Please remove these employees from your order or use different email addresses.
Note: Each employee can only have one account in the system.
```

---

## 📊 流程图

```
User 提交订单
        ↓
    发送通知给 Admin
        ↓
Admin 点击 Approve
        ↓
┌─────────────────────────┐
│ 后端预验证 Employee Emails│
└─────────────────────────┘
        ↓
    检查每个 email
        ↓
┌──────────────────┐
│ 发现重复 Email？  │
└──────────────────┘
       ↙          ↘
    是              否
     ↓              ↓
┌─────────────────┐  ┌────────────────┐
│ 🔴 自动拒绝订单 │  │ ✅ 批准订单     │
└─────────────────┘  └────────────────┘
     ↓                      ↓
┌─────────────────┐  ┌────────────────┐
│ 记录拒绝原因    │  │ 创建 Accounts  │
└─────────────────┘  └────────────────┘
     ↓                      ↓
┌─────────────────┐  ┌────────────────┐
│ 发送通知给 User │  │ 创建 Cards     │
└─────────────────┘  └────────────────┘
     ↓                      ↓
┌─────────────────┐  ┌────────────────┐
│ Admin 看到消息  │  │ 发送成功通知   │
└─────────────────┘  └────────────────┘
     ↓                      ↓
 订单已拒绝          订单已批准
 Notification 显示   Notification 显示
 "Rejected" 状态    "Approved" 状态
```

---

## 🔒 后端实现

### 文件：`backend/app/Http/Controllers/Api/AdminNotificationController.php`

#### 关键代码段：

```php
// Line 315-372: 预验证和自动拒绝

// PRE-VALIDATION: Check if any employee emails already exist
$existingEmployees = [];
foreach ($cards as $cardData) {
    if (($cardData['is_employee_card'] ?? false) && !empty($cardData['email'])) {
        $existingUser = User::where('email', $cardData['email'])->first();
        if ($existingUser) {
            $existingEmployees[] = [
                'name' => $cardData['name'],
                'email' => $cardData['email'],
                'existing_user_id' => $existingUser->id,
            ];
        }
    }
}

// If any employee accounts already exist, auto-reject the order
if (count($existingEmployees) > 0) {
    \Log::warning('Order contains existing employee emails - auto-rejecting', [
        'existing_employees' => $existingEmployees
    ]);
    
    // Build detailed rejection reason
    $rejectionReason = "Order automatically rejected: " . count($existingEmployees) 
        . " employee email(s) already exist in the system.\n\n";
    $rejectionReason .= "Existing employees:\n";
    foreach ($existingEmployees as $emp) {
        $rejectionReason .= "• {$emp['name']} ({$emp['email']})\n";
    }
    $rejectionReason .= "\n⚠️ Solution: Please remove these employees from your order or use different email addresses.\n";
    $rejectionReason .= "Note: Each employee can only have one account in the system.";
    
    // Auto-reject the notification
    $notification->reject($rejectionReason, auth()->id());
    
    // Send rejection notification to the user
    $this->notificationService->create(
        $user,
        'order_rejected',
        [
            'order_number' => $notification->id,
            'rejection_reason' => $rejectionReason,
            'existing_employees_count' => count($existingEmployees),
            'existing_employees' => $existingEmployees,
        ]
    );
    
    \Log::info('Order auto-rejected and notification sent', [
        'notification_id' => $notification->id,
        'user_id' => $user->id,
        'existing_employees_count' => count($existingEmployees)
    ]);
    
    return response()->json([
        'success' => false,
        'message' => 'Order automatically rejected due to duplicate employee emails',
        'rejection_reason' => $rejectionReason,
        'existing_employees' => $existingEmployees,
        'notification_id' => $notification->id,
        'is_rejected' => true,
    ], 400);
}
```

---

## 💻 前端实现

### 文件：`frontend/pages/AdminManagement/notifications.vue`

#### 关键代码段：

```javascript
// Line 1408-1451: 错误处理和消息显示

} catch (error) {
  console.error("Error approving order:", error);
  
  // Handle validation errors (existing employees - auto-rejected)
  if (error.data?.existing_employees) {
    const existingEmps = error.data.existing_employees;
    const isRejected = error.data?.is_rejected;
    
    let errorMessage = isRejected 
      ? `🔴 Order Automatically Rejected!\n\n`
      : `❌ Cannot approve order!\n\n`;
    
    errorMessage += `${existingEmps.length} employee email(s) already exist:\n\n`;
    
    existingEmps.forEach(emp => {
      errorMessage += `• ${emp.name} (${emp.email})\n`;
    });
    
    if (isRejected) {
      errorMessage += `\n✅ The user has been notified with the rejection reason.`;
      errorMessage += `\n\n💡 Solution: User should remove duplicate employees or use different emails.`;
    } else {
      errorMessage += `\nThese employees already have accounts in the system.`;
    }
    
    $toast.error(errorMessage, {
      duration: 12000,
    });
    
    // Reload notifications to show rejected status
    if (isRejected) {
      loadNotifications();
      loadStatistics();
    }
  }
}
```

---

## 📋 数据库变化

### Notifications 表

#### 被拒绝的通知：
```sql
UPDATE notifications
SET 
  is_rejected = 1,
  rejection_reason = 'Order automatically rejected: ...',
  rejected_at = NOW(),
  rejected_by = [admin_id]
WHERE id = [notification_id];
```

#### 新的拒绝通知（发给用户）：
```sql
INSERT INTO notifications (
  user_id,
  type,
  title,
  message,
  data,
  priority,
  created_at
) VALUES (
  [user_id],
  'order_rejected',
  'Order Rejected',
  'Your order has been rejected',
  JSON_OBJECT(
    'order_number', [order_id],
    'rejection_reason', '...',
    'existing_employees_count', 1,
    'existing_employees', [...]
  ),
  'high',
  NOW()
);
```

---

## 🎯 实际示例

### 示例 1：单个重复 Email

**订单：**
```javascript
{
  cards: [
    { name: "Admin", email: "admin@co.com", is_admin_card: true },
    { name: "Jane Smith", email: "jane@co.com", is_employee_card: true }  // ❌ 已存在
  ]
}
```

**Admin 点击 Approve 后：**

**Admin 看到：**
```
🔴 Order Automatically Rejected!

1 employee email(s) already exist:

• Jane Smith (jane@co.com)

✅ The user has been notified with the rejection reason.

💡 Solution: User should remove duplicate employees or use different emails.
```

**User 收到通知：**
```
Order Rejected

Order automatically rejected: 1 employee email(s) already exist in the system.

Existing employees:
• Jane Smith (jane@co.com)

⚠️ Solution: Please remove these employees from your order or use different email addresses.
Note: Each employee can only have one account in the system.
```

**数据库：**
- ✅ 订单通知标记为 `is_rejected = 1`
- ✅ 拒绝原因已保存
- ✅ 新的 `order_rejected` 通知发送给用户
- ❌ 没有创建任何 account
- ❌ 没有创建任何 card

### 示例 2：多个重复 Email

**订单：**
```javascript
{
  cards: [
    { name: "Admin", email: "admin@co.com", is_admin_card: true },
    { name: "John Doe", email: "john@co.com", is_employee_card: true },      // ✅ 新
    { name: "Jane Smith", email: "jane@co.com", is_employee_card: true },    // ❌ 已存在
    { name: "Bob Wilson", email: "bob@co.com", is_employee_card: true }      // ❌ 已存在
  ]
}
```

**Admin 点击 Approve 后：**

**Admin 看到：**
```
🔴 Order Automatically Rejected!

2 employee email(s) already exist:

• Jane Smith (jane@co.com)
• Bob Wilson (bob@co.com)

✅ The user has been notified with the rejection reason.

💡 Solution: User should remove duplicate employees or use different emails.
```

**User 收到通知：**
```
Order Rejected

Order automatically rejected: 2 employee email(s) already exist in the system.

Existing employees:
• Jane Smith (jane@co.com)
• Bob Wilson (bob@co.com)

⚠️ Solution: Please remove these employees from your order or use different email addresses.
Note: Each employee can only have one account in the system.
```

---

## 🧪 测试步骤

### 测试 1: 单个重复 Email

**准备：**
1. 确保数据库中有 `jane@co.com` 账户

**步骤：**
1. Business Admin 登录
2. 提交订单，包含 `jane@co.com`
3. Super Admin 登录
4. 进入 Notification Management
5. 找到订单通知，点击 **Approve**

**预期结果：**

**Admin 看到：**
- Toast 消息：🔴 Order Automatically Rejected!
- 列出重复的 employee
- 显示"用户已收到通知"消息

**User（Business Admin）：**
- 收到 `order_rejected` 通知
- 通知包含详细的拒绝原因

**数据库：**
```sql
-- 检查订单通知已被拒绝
SELECT is_rejected, rejection_reason 
FROM notifications 
WHERE id = [order_notification_id];
-- is_rejected = 1
-- rejection_reason 包含详细信息

-- 检查用户收到拒绝通知
SELECT * FROM notifications 
WHERE user_id = [business_admin_id] 
  AND type = 'order_rejected' 
  AND created_at > NOW() - INTERVAL 1 MINUTE;
-- 应该有 1 条记录

-- 检查没有创建新账户
SELECT COUNT(*) FROM users WHERE email = 'jane@co.com';
-- 应该仍然是 1（没有增加）

-- 检查没有创建新卡片
SELECT COUNT(*) FROM nfc_cards 
WHERE created_at > NOW() - INTERVAL 1 MINUTE;
-- 应该是 0
```

### 测试 2: 混合（有新有旧）

**订单：**
- 1 个新员工（john@co.com）
- 1 个已存在员工（jane@co.com）

**预期结果：**
- ❌ 整个订单被拒绝
- ❌ John 的账户也不会被创建
- ✅ 用户收到详细的拒绝原因

---

## ✅ 安全特性

| 特性 | 说明 |
|-----|------|
| ✅ **自动拒绝** | 检测到重复 email 立即拒绝，无需人工干预 |
| ✅ **详细原因** | 清楚列出所有重复的 employees |
| ✅ **用户通知** | 自动发送通知给提交订单的用户 |
| ✅ **数据完整性** | 确保不创建重复的 accounts |
| ✅ **Admin 反馈** | Admin 清楚知道订单被自动拒绝 |
| ✅ **日志记录** | 所有操作都有详细日志 |

---

## 🎯 优势

### 对 Admin
- ✅ **自动化** - 不需要手动拒绝
- ✅ **清晰反馈** - 知道为什么被拒绝
- ✅ **节省时间** - 自动处理重复问题

### 对 User
- ✅ **即时通知** - 立即知道订单状态
- ✅ **详细说明** - 清楚知道哪些 emails 重复
- ✅ **解决方案** - 提供明确的修复建议

### 对系统
- ✅ **数据完整性** - 防止重复数据
- ✅ **一致性** - 确保 card 和 account 一对一
- ✅ **可追溯** - 完整的日志记录

---

## 📝 通知内容

### order_rejected 通知（发给 User）

```json
{
  "type": "order_rejected",
  "title": "Order Rejected",
  "message": "Your order has been rejected",
  "data": {
    "order_number": 12345,
    "rejection_reason": "Order automatically rejected: ...",
    "existing_employees_count": 2,
    "existing_employees": [
      {
        "name": "Jane Smith",
        "email": "jane@co.com",
        "existing_user_id": 235
      },
      {
        "name": "Bob Wilson",
        "email": "bob@co.com",
        "existing_user_id": 236
      }
    ]
  },
  "priority": "high"
}
```

---

## 🔍 日志示例

```
[2025-11-19 11:11:00] local.WARNING: Order contains existing employee emails - auto-rejecting
{
  "existing_employees": [
    {
      "name": "Jane Smith",
      "email": "jane@co.com",
      "existing_user_id": 235
    }
  ]
}

[2025-11-19 11:11:01] local.INFO: Order auto-rejected and notification sent
{
  "notification_id": 12345,
  "user_id": 123,
  "existing_employees_count": 1
}
```

---

## ✅ 总结

**现在的流程：**

1. Admin 点击 Approve
2. 后端检测到重复 email
3. **自动拒绝订单** ✅
4. **发送通知给用户** ✅
5. **显示消息给 Admin** ✅
6. **不创建任何数据** ✅

**完全自动化！无需人工干预！** 🚀

---

**最后更新：** 2025-11-19  
**版本：** 4.0 - 添加自动拒绝功能
