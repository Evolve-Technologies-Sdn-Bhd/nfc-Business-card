# Business Plan 功能测试指南

## 测试场景

### 场景 1: Admin 创建 Business 账户

```bash
# 测试API
POST /api/admin/users
Authorization: Bearer {admin_token}

{
  "first_name": "Test",
  "last_name": "Company",
  "email": "testcompany@example.com",
  "password": "Password123!",
  "company": "Test Corporation",
  "job_title": "CEO",
  "phone": "+60123456789",
  "subscription_plan": "business",
  "total_account_slots": 10,
  "total_card_quota": 10
}

# 预期结果
✅ User创建成功
✅ total_account_slots = 10
✅ total_card_quota = 10
✅ parent_business_id = NULL
✅ 自动创建一张NFC card，business_account_id = user.id
```

### 场景 2: Business 账户查看 Quota

```bash
POST /api/login
{
  "email": "testcompany@example.com",
  "password": "Password123!"
}

# 获取token后
GET /api/business/card-quota
Authorization: Bearer {business_token}

# 预期结果
{
  "success": true,
  "data": {
    "total_card_quota": 10,
    "ordered_cards_count": 1,  // Admin创建时的卡
    "available_card_quota": 9,
    "business_account_id": {business_user_id},
    "account_info": {
      "total_account_slots": 10,
      "employees_count": 0,
      "available_account_slots": 10
    }
  }
}
```

### 场景 3: Business 账户创建员工

```bash
POST /api/business/employees
Authorization: Bearer {business_token}

{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john.doe@testcompany.com",
  "password": "Password123!",
  "password_confirmation": "Password123!",
  "phone": "+60123456788",
  "job_title": "Sales Manager"
}

# 预期结果
✅ 员工创建成功
✅ parent_business_id = business_user_id
✅ subscription_plan = 'business'
✅ company = 'Test Corporation' (继承)
✅ employees_count: 0 → 1
✅ available_account_slots: 10 → 9
```

### 场景 4: 员工登录并查看 Quota

```bash
POST /api/login
{
  "email": "john.doe@testcompany.com",
  "password": "Password123!"
}

# 获取token后
GET /api/business/card-quota
Authorization: Bearer {employee_token}

# 预期结果
{
  "success": true,
  "data": {
    "total_card_quota": 10,
    "ordered_cards_count": 1,
    "available_card_quota": 9,
    "business_account_id": {business_user_id},  // 父级ID
    "account_info": {
      "total_account_slots": 10,
      "employees_count": 1,
      "available_account_slots": 9
    }
  }
}
```

### 场景 5: 员工订购 NFC 卡

```bash
POST /api/nfc-cards
Authorization: Bearer {employee_token}

{
  "card_owner": "John Doe",
  "billing_address": "123 Test St",
  "contact_number": "+60123456788",
  "subscription_plan": "business",
  "purchase_amount": 49.00,
  "payment_method": "stripe",
  "shipping_address": "123 Test St",
  "notes": "Employee card order"
}

# 预期结果
✅ NFC卡创建成功
✅ user_id = employee_id
✅ business_account_id = business_user_id (父级)
✅ ordered_cards_count: 1 → 2
✅ available_card_quota: 9 → 8
```

### 场景 6: Business 账户再次查看 Quota

```bash
GET /api/business/card-quota
Authorization: Bearer {business_token}

# 预期结果
{
  "success": true,
  "data": {
    "total_card_quota": 10,
    "ordered_cards_count": 2,  // Business账户 + 员工
    "available_card_quota": 8,
    "business_account_id": {business_user_id},
    "account_info": {
      "total_account_slots": 10,
      "employees_count": 1,
      "available_account_slots": 9
    }
  }
}
```

### 场景 7: 超出 Quota 限制

```bash
# 假设已经创建了10个员工
POST /api/business/employees
Authorization: Bearer {business_token}

{
  "first_name": "Extra",
  "last_name": "Employee",
  "email": "extra@testcompany.com",
  "password": "Password123!",
  "password_confirmation": "Password123!"
}

# 预期结果
❌ HTTP 403
{
  "success": false,
  "message": "Cannot create employee. Account slots quota exceeded.",
  "data": {
    "total_account_slots": 10,
    "employees_count": 10,
    "available_account_slots": 0
  }
}
```

### 场景 8: Admin 增加 Quota

```bash
PUT /api/admin/users/{business_user_id}
Authorization: Bearer {admin_token}

{
  "total_account_slots": 20,
  "total_card_quota": 20
}

# 预期结果
✅ Quota更新成功
✅ total_account_slots: 10 → 20
✅ total_card_quota: 10 → 20
✅ available_account_slots: 0 → 10
✅ available_card_quota: 8 → 18
```

### 场景 9: Admin 尝试减少 Quota（失败）

```bash
PUT /api/admin/users/{business_user_id}
Authorization: Bearer {admin_token}

{
  "total_card_quota": 5  // 当前已订购10张
}

# 预期结果
❌ HTTP 422
{
  "success": false,
  "message": "Cannot set card quota to 5. Already ordered: 10 cards.",
  "errors": {
    "total_card_quota": [
      "Minimum value is 10 (already ordered cards)"
    ]
  }
}
```

### 场景 10: 查看员工列表

```bash
GET /api/business/employees
Authorization: Bearer {business_token}

# 预期结果
{
  "success": true,
  "data": {
    "employees": [
      {
        "id": 123,
        "full_name": "John Doe",
        "email": "john.doe@testcompany.com",
        "job_title": "Sales Manager",
        "phone": "+60123456788",
        "created_at": "2025-11-14T10:00:00Z",
        "subscription_active": true
      }
    ],
    "quota_info": {
      "total_account_slots": 20,
      "employees_count": 1,
      "available_account_slots": 19,
      "total_card_quota": 20,
      "ordered_cards_count": 2,
      "available_card_quota": 18
    }
  }
}
```

## 数据库验证

### 检查 Business 账户

```sql
SELECT
  id,
  email,
  subscription_plan,
  total_account_slots,
  total_card_quota,
  parent_business_id
FROM users
WHERE subscription_plan = 'business' AND parent_business_id IS NULL;
```

### 检查员工

```sql
SELECT
  id,
  email,
  parent_business_id,
  subscription_plan,
  company
FROM users
WHERE parent_business_id IS NOT NULL;
```

### 检查 NFC 卡

```sql
SELECT
  id,
  user_id,
  business_account_id,
  subscription_plan,
  card_owner
FROM nfc_cards
WHERE subscription_plan = 'business';
```

### Quota 统计

```sql
-- 员工数量
SELECT
  b.id AS business_id,
  b.email AS business_email,
  b.total_account_slots,
  COUNT(e.id) AS employees_count,
  (b.total_account_slots - COUNT(e.id)) AS available_slots
FROM users b
LEFT JOIN users e ON e.parent_business_id = b.id
WHERE b.subscription_plan = 'business' AND b.parent_business_id IS NULL
GROUP BY b.id;

-- 卡片数量
SELECT
  b.id AS business_id,
  b.email AS business_email,
  b.total_card_quota,
  COUNT(c.id) AS ordered_cards,
  (b.total_card_quota - COUNT(c.id)) AS available_quota
FROM users b
LEFT JOIN nfc_cards c ON c.business_account_id = b.id
WHERE b.subscription_plan = 'business' AND b.parent_business_id IS NULL
GROUP BY b.id;
```

## 常见问题排查

### 问题 1: Quota 不更新

**症状**: 创建员工或订购卡片后，quota 数字不变

**检查**:

```sql
-- 检查parent_business_id是否正确设置
SELECT id, email, parent_business_id FROM users WHERE id = {employee_id};

-- 检查business_account_id是否正确设置
SELECT id, user_id, business_account_id FROM nfc_cards WHERE id = {card_id};
```

**解决**: 确保创建时正确设置了外键

### 问题 2: 员工无法订购卡片

**症状**: 员工订购时提示"Business account not found"

**检查**:

```sql
-- 验证员工的parent_business_id
SELECT
  e.id,
  e.email,
  e.parent_business_id,
  b.id AS business_exists
FROM users e
LEFT JOIN users b ON b.id = e.parent_business_id
WHERE e.id = {employee_id};
```

**解决**: 确保 parent_business_id 指向有效的 Business 账户

### 问题 3: Admin 无法减少 quota

**症状**: 即使没有超出，也无法减少 quota

**检查**:

```php
// User模型的getQuotaInfo()方法是否正常工作
$user = User::find($businessUserId);
dd($user->getQuotaInfo());
```

**解决**: 确保 User 模型正确导入 NfcCard，且 getQuotaInfo()方法存在

## 成功标准

✅ Admin 可以创建 Business 账户并设置 quota
✅ Business 账户可以查看自己的 quota 信息
✅ Business 账户可以创建员工（在 quota 范围内）
✅ 员工可以查看父级 Business 账户的 quota
✅ 员工可以订购 NFC 卡（计入父级 quota）
✅ Quota 统计实时更新
✅ 超出 quota 时正确拒绝操作
✅ Admin 可以增加 quota（不受限制）
✅ Admin 减少 quota 时验证不低于当前使用量
✅ 删除员工后 quota 自动释放

## 下一步

如果所有测试通过，Business plan 功能已经完整实现并可以正常运行！

如果遇到问题，请参考上述排查步骤或查看错误日志：

```bash
tail -f backend/storage/logs/laravel.log
```
