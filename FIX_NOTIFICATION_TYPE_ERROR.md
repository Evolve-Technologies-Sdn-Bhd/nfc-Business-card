# 🔧 修复通知类型错误

## ❌ 错误信息

```
Order approved but failed to create NFC card: 
Failed to create employee account for hi23@example.com: 
Invalid notification type: account_created
```

## 🔍 问题原因

数据库的 `notifications` 表的 `type` 枚举列缺少以下两个新的通知类型：
- `account_created` - 当创建 employee 账户时使用
- `order_rejected` - 当订单被拒绝时使用

## ✅ 解决方案

### 步骤 1: 运行新的 Migration

打开终端，进入后端目录并运行：

```bash
cd backend

# 运行 migration
php artisan migrate

# 如果提示 "Nothing to migrate"，可能需要先刷新
php artisan migrate:status

# 查看 migration 状态
```

### 步骤 2: 验证 Migration

检查 migration 是否成功运行：

```bash
# 进入 tinker
php artisan tinker

# 检查 notifications 表结构
Schema::getColumnType('notifications', 'type')

# 检查枚举值（可选）
DB::select("SHOW COLUMNS FROM notifications WHERE Field = 'type'");

# 退出
exit
```

### 步骤 3: 清除缓存

```bash
# 清除所有缓存
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 重启服务器
# 按 Ctrl+C 停止
php artisan serve
```

## 📋 Migration 文件详情

**文件**: `database/migrations/2025_11_19_000000_add_employee_notification_types.php`

**添加的通知类型**:
```php
'account_created',    // ✅ 新增：员工账户创建时
'order_rejected'      // ✅ 新增：订单被拒绝时
```

## 🔄 完整的通知类型列表

运行 migration 后，`notifications.type` 将包含：

1. `registration_success`
2. `email_verification`
3. `login_new_device`
4. `profile_updated`
5. `link_milestone`
6. `landing_page_viewed`
7. `contact_request`
8. `subscription_upgrade`
9. `payment_successful`
10. `payment_failed`
11. `account_warning`
12. `password_changed`
13. `nfc_card_purchased`
14. `nfc_card_delivered`
15. `nfc_card_linked`
16. `nfc_card_activated`
17. `nfc_card_expired`
18. `app_update`
19. `system_message`
20. `admin_announcement`
21. `business_card_order_request`
22. `business_bulk_order_placed`
23. **`account_created`** ← 新增
24. **`order_rejected`** ← 新增

## 🧪 测试步骤

### 测试 1: Account Created 通知

1. Business Admin 提交一个包含新员工的订单
2. Super Admin 批准订单
3. 检查：
   ```sql
   SELECT * FROM notifications 
   WHERE type = 'account_created' 
   ORDER BY created_at DESC 
   LIMIT 5;
   ```
4. 应该看到给新员工的通知，包含登录凭证

### 测试 2: Order Rejected 通知

1. Business Admin 提交一个包含已存在 email 的订单
2. Super Admin 点击 Approve
3. 系统自动拒绝订单
4. 检查：
   ```sql
   SELECT * FROM notifications 
   WHERE type = 'order_rejected' 
   ORDER BY created_at DESC 
   LIMIT 5;
   ```
5. 应该看到拒绝通知，包含详细原因

## 📊 数据验证

### 检查通知类型是否正确

```sql
-- 查看所有通知类型
SELECT DISTINCT type FROM notifications;

-- 检查新增的通知类型
SELECT COUNT(*) as count, type 
FROM notifications 
WHERE type IN ('account_created', 'order_rejected')
GROUP BY type;
```

## ⚠️ 常见问题

### Q1: Migration 运行后仍然报错？

**A**: 清除缓存并重启服务器
```bash
php artisan cache:clear
php artisan config:clear
# 重启服务器
```

### Q2: 显示 "Nothing to migrate"？

**A**: 检查 migration 文件是否存在
```bash
ls -la database/migrations/ | grep "2025_11_19"
```

如果不存在，需要重新创建 migration 文件。

### Q3: 报错 "Unknown column 'type'"？

**A**: 这个 migration 会先删除 type 列再重建，可能需要手动处理：
```bash
php artisan migrate:rollback --step=1
php artisan migrate
```

### Q4: 之前的通知怎么办？

**A**: 之前的通知不受影响。这个 migration 只是扩展枚举值，不会删除现有数据。

## 🎯 预期结果

运行 migration 后：

✅ **Admin 批准订单时**:
- 成功创建 employee accounts
- 成功创建 NFC cards
- 发送 `account_created` 通知给 employees
- 发送 `nfc_card_purchased` 通知给 business admin

✅ **订单包含重复 email 时**:
- 自动拒绝订单
- 发送 `order_rejected` 通知给 business admin
- 包含详细的拒绝原因

## 📝 后续步骤

1. ✅ 运行 migration
2. ✅ 清除缓存
3. ✅ 重启服务器
4. ✅ 测试批准订单功能
5. ✅ 测试自动拒绝功能
6. ✅ 验证通知是否正确发送

## 🚀 完成！

运行 migration 后，系统应该可以正常工作：
- ✅ 创建 employee accounts
- ✅ 发送 welcome 通知
- ✅ 自动拒绝重复订单
- ✅ 发送拒绝通知

---

**创建时间**: 2025-11-19  
**目的**: 修复 "Invalid notification type: account_created" 错误
