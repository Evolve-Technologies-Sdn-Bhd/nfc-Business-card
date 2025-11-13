# Notification Triggers Integration Guide

本文档记录了所有已集成的通知触发器位置和实现细节。

## 已集成的通知触发器

### 1. ✅ 注册成功通知 (`registration_success`)

**位置**: `AuthController@register`  
**触发时机**: 用户成功注册账户后  
**数据参数**:

- `name`: 用户名
- `profile_url`: 个人资料页面 URL

```php
$this->notificationService->create($user, 'registration_success', [
    'name' => $user->first_name,
    'profile_url' => config('app.frontend_url') . '/UserDashboard/overview',
]);
```

---

### 2. ✅ 新设备登录通知 (`login_new_device`)

**位置**: `AuthController@login`  
**触发时机**: 用户从新设备/浏览器登录时  
**检测机制**: 对比 `user_agent` 字符串  
**数据参数**:

- `device`: 设备类型（Windows PC, Mac, iPhone 等）
- `ip`: 登录 IP 地址
- `time`: 登录时间

```php
if ($lastLoginDevice && $lastLoginDevice !== $currentDevice) {
    $this->notificationService->create($user, 'login_new_device', [
        'device' => $this->getDeviceInfo($currentDevice),
        'ip' => $request->ip(),
        'time' => now()->format('Y-m-d H:i:s'),
    ]);
}
```

**数据库支持**:

- 添加了 `users.last_login_device` 字段（迁移文件: `2025_01_12_000002_add_last_login_device_to_users_table.php`）

---

### 3. ✅ 个人资料更新通知 (`profile_updated`)

**位置**: `ProfileController@update`  
**触发时机**: 用户更新个人资料后  
**数据参数**:

- `fields`: 更新的字段列表（逗号分隔）

```php
$this->notificationService->create($request->user(), 'profile_updated', [
    'fields' => implode(', ', array_keys(array_filter($mappedData, function($value, $key) use ($profile) {
        return $profile->wasChanged($key);
    }, ARRAY_FILTER_USE_BOTH))),
]);
```

---

### 4. ✅ 密码更改通知 (`password_changed`)

**位置**: `PasswordResetController@resetPassword`  
**触发时机**: 用户成功重置密码后  
**数据参数**:

- `ip`: 操作 IP 地址
- `time`: 更改时间

```php
$this->notificationService->create($user, 'password_changed', [
    'ip' => $request->ip(),
    'time' => now()->format('Y-m-d H:i:s'),
]);
```

---

### 5. ✅ NFC 卡购买通知 (`nfc_card_purchased`)

**位置**: `NfcCardController@store`  
**触发时机**: 用户购买 NFC 卡后  
**数据参数**:

- `card_id`: NFC 卡 ID
- `amount`: 购买金额
- `plan`: 订阅计划

```php
$this->notificationService->create($user, 'nfc_card_purchased', [
    'card_id' => $nfcCardId,
    'amount' => '$' . number_format($request->purchase_amount, 2),
    'plan' => ucfirst($request->subscription_plan),
]);
```

---

### 6. ✅ NFC 卡激活通知 (`nfc_card_activated`)

**位置**: `NfcCardController@activate`  
**触发时机**: 用户激活 NFC 卡后  
**数据参数**:

- `card_id`: NFC 卡 ID
- `nfc_id`: NFC 标签 ID

```php
$this->notificationService->create($request->user(), 'nfc_card_activated', [
    'card_id' => $nfcCard->nfc_card_id,
    'nfc_id' => $request->nfc_id,
]);
```

---

### 7. ✅ 链接点击里程碑通知 (`link_milestone`)

**位置**: `LinkController@trackClick`  
**触发时机**: 链接点击数达到 100 的倍数时  
**检测机制**: `$link->click_count % 100 === 0 && $link->click_count > 0`  
**数据参数**:

- `link_title`: 链接标题
- `click_count`: 总点击数

```php
if ($link->click_count % 100 === 0 && $link->click_count > 0) {
    $user = $link->profile->user;
    $this->notificationService->create($user, 'link_milestone', [
        'link_title' => $link->title,
        'click_count' => $link->click_count,
    ]);
}
```

---

## 未实现的通知类型

以下通知类型已在 `NotificationService` 中定义模板，但**尚未集成触发器**：

### 📧 Email Verification (`email_verification`)

**建议位置**: Email 验证控制器  
**触发时机**: 发送邮箱验证链接后

### 👁️ Landing Page Viewed (`landing_page_viewed`)

**建议位置**: 分析控制器  
**触发时机**: 着陆页被访问时

### 📬 Contact Request (`contact_request`)

**建议位置**: 联系表单控制器  
**触发时机**: 有人通过名片发送联系请求

### ⬆️ Subscription Upgrade (`subscription_upgrade`)

**建议位置**: 订阅控制器  
**触发时机**: 用户升级订阅计划

### ✅ Payment Successful (`payment_successful`)

**建议位置**: 支付控制器  
**触发时机**: 支付成功后  
**数据参数**: `amount`, `plan`, `invoice_url`

### ❌ Payment Failed (`payment_failed`)

**建议位置**: 支付控制器  
**触发时机**: 支付失败后  
**数据参数**: `amount`, `reason`

### ⚠️ Account Warning (`account_warning`)

**建议位置**: 管理员控制器  
**触发时机**: 账户出现异常行为

### 📦 NFC Card Delivered (`nfc_card_delivered`)

**建议位置**: 物流管理控制器  
**触发时机**: NFC 卡发货/送达  
**数据参数**: `card_id`, `tracking_number`

### ⏰ NFC Card Expiring (`nfc_card_expiring`)

**建议位置**: 定时任务 (Scheduler)  
**触发时机**: NFC 卡即将过期（30 天前）  
**数据参数**: `card_id`, `expiry_date`

---

## 部署步骤

### 1. 运行数据库迁移

```bash
cd backend
php artisan migrate
```

这将创建：

- `notifications` 表
- `users.last_login_device` 字段

### 2. 测试通知创建

```bash
php artisan tinker
```

```php
$user = User::first();
$notificationService = app(\App\Services\NotificationService::class);

// 测试注册通知
$notificationService->create($user, 'registration_success', [
    'name' => $user->first_name,
    'profile_url' => 'http://localhost:3000/UserDashboard/overview',
]);

// 查看通知
$user->notifications;
```

### 3. 验证前端集成

- 访问 `/UserDashboard/overview` 查看侧边栏通知图标
- 访问 `/UserDashboard/Notifications` 查看完整通知列表
- 确认未读数量显示正确
- 测试标记为已读功能

---

## 后续集成建议

### 优先级 1：支付通知

创建 `PaymentController` 并集成：

- `payment_successful`
- `payment_failed`

### 优先级 2：邮箱验证

在现有邮箱验证流程中添加：

- `email_verification`

### 优先级 3：联系表单

创建联系功能并集成：

- `contact_request`

### 优先级 4：物流追踪

创建物流管理功能并集成：

- `nfc_card_delivered`

### 优先级 5：定时任务

创建 Laravel Scheduler 任务：

- `nfc_card_expiring` (每日检查)

---

## 通知测试清单

- [x] 注册成功 → 自动发送通知
- [x] 新设备登录 → 检测并通知
- [x] 更新资料 → 显示更新的字段
- [x] 重置密码 → 发送安全通知
- [x] 购买 NFC 卡 → 确认订单
- [x] 激活 NFC 卡 → 激活成功
- [x] 链接点击里程碑 → 每 100 次通知
- [ ] 支付成功/失败 → 待实现
- [ ] 邮箱验证 → 待实现
- [ ] 联系请求 → 待实现

---

## 开发者备忘

### 添加新的通知触发器步骤：

1. **在控制器中注入 `NotificationService`**

```php
protected $notificationService;

public function __construct(NotificationService $notificationService)
{
    $this->notificationService = $notificationService;
}
```

2. **在合适的方法中调用通知**

```php
$this->notificationService->create($user, 'notification_type', [
    'placeholder1' => $value1,
    'placeholder2' => $value2,
]);
```

3. **确保通知类型已在 `NotificationService` 中定义模板**

4. **测试通知创建和显示**

5. **更新本文档**

---

## 相关文件

- **Backend**:
  - `app/Services/NotificationService.php` - 通知服务
  - `app/Models/Notification.php` - 通知模型
  - `app/Http/Controllers/NotificationController.php` - 用户 API
  - `app/Http/Controllers/Admin/AdminNotificationController.php` - 管理员 API
- **Frontend**:

  - `composables/useNotifications.js` - 通知组合式函数
  - `layouts/UserDashboard.vue` - 侧边栏通知下拉菜单
  - `pages/UserDashboard/Notifications.vue` - 完整通知页面
  - `pages/AdminManagement/notifications.vue` - 管理员通知管理

- **Database**:
  - `database/migrations/2025_01_12_000001_create_notifications_table.php`
  - `database/migrations/2025_01_12_000002_add_last_login_device_to_users_table.php`

---

## 最后更新

日期: 2025-01-12  
作者: Development Team  
版本: 1.0
