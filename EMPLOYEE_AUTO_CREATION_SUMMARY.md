# 🚀 员工账户自动创建功能 - 完整实现

## ✅ 功能总览

当 Super Admin 批准 Business Plan NFC 卡订单时，系统会自动：

1. ✅ 为每张 **employee card** 创建对应的 employee user account
2. ✅ 使用统一的默认密码：**Welcome123@**
3. ✅ 创建 NFC 卡片并关联到员工账户
4. ✅ 发送欢迎通知给员工（包含登录凭证）
5. ✅ 智能验证：检查账户是否已存在，避免重复创建

---

## 🔄 完整工作流程

### 1️⃣ **Business Admin 提交订单**

在 `BusinessPlanNFCCard.vue` 页面：

```javascript
// 员工信息（手动添加或 CSV 上传）
{
  name: "John Employee",
  email: "john@company.com",
  position: "Manager",
  contactNumber: "+60123456789",
  is_employee_card: true  // ← 关键标识
}
```

### 2️⃣ **Super Admin 批准订单**

在 `notifications.vue` 页面点击 **Approve** 按钮

**确认弹窗显示：**
```
Are you sure you want to approve this order?

📦 Total Cards: 3
👤 Admin Card: 1
👥 Employee Cards: 2

⚠️ This will automatically:
✓ Create 3 NFC card(s)
✓ Create 2 employee account(s)
✓ Set default password: Welcome123@
✓ Send login credentials to employees
```

### 3️⃣ **系统自动处理**

#### ✅ 智能账户验证
```php
// 检查员工账户是否已存在
$employeeUser = User::where('email', $cardData['email'])->first();

if (!$employeeUser) {
    // 创建新账户
} else {
    // 使用现有账户，跳过创建
    \Log::info("Employee account already exists");
}
```

#### ✅ 创建 Employee Account
```php
$defaultPassword = 'Welcome123@';

$employeeUser = User::create([
    'first_name' => $firstName,        // 从 name 拆分
    'last_name' => $lastName,          // 从 name 拆分
    'email' => $cardData['email'],
    'password' => Hash::make($defaultPassword),
    'phone' => $cardData['contact_number'],
    'job_title' => $cardData['position'],
    'company' => $businessAccount->company,
    'subscription_plan' => 'premium',
    'subscription_active' => true,
    'parent_business_id' => $businessAccountId,
]);
```

#### ✅ 创建 NFC 卡片
```php
$nfcCard = NfcCard::create([
    'user_id' => $employeeUser->id,  // ← 使用员工的 user_id
    'business_account_id' => $businessAccountId,
    'nfc_card_id' => 'NFC-' . Str::random(12),
    'card_owner' => $cardData['name'],
    // ... 其他字段
]);
```

#### ✅ 发送欢迎通知
```php
$this->notificationService->create(
    $employeeUser,
    'account_created',
    [
        'welcome_message' => 'Your employee account has been created',
        'email' => $cardData['email'],
        'temporary_password' => 'Welcome123@',
        'company' => $businessAccount->company,
    ]
);
```

### 4️⃣ **成功消息显示**

```
✅ Order approved successfully!

📦 3 NFC card(s) created
👥 2 employee account(s) created
📧 Login credentials sent to employees

ℹ️ 1 employee(s) already had accounts
```

### 5️⃣ **Employee 收到通知并登录**

**通知内容：**
```
Your employee account has been created by [Admin Name]

Email: john@company.com
Password: Welcome123@
Company: ABC Company
```

**登录步骤：**
1. 访问登录页面
2. 使用邮箱：`john@company.com`
3. 使用密码：`Welcome123@`
4. 登录成功！
5. （建议首次登录后修改密码）

---

## 📊 实际示例

### 场景：批量创建 5 个员工账户

**User 上传的数据：**
```csv
Name,Email,Position,Contact Number
John Doe,john@company.com,Manager,+60123456789
Jane Smith,jane@company.com,Developer,+60198765432
Bob Wilson,bob@company.com,Designer,+60187654321
Alice Brown,alice@company.com,Marketing,+60176543210
Tom Green,tom@company.com,Sales,+60165432109
```

**Admin 点击 Approve 后：**

#### 1️⃣ 系统检查
- `john@company.com` → ❌ 不存在 → 创建新账户
- `jane@company.com` → ❌ 不存在 → 创建新账户
- `bob@company.com` → ✅ **已存在** → 使用现有账户
- `alice@company.com` → ❌ 不存在 → 创建新账户
- `tom@company.com` → ❌ 不存在 → 创建新账户

#### 2️⃣ 创建结果
- ✅ **4 个新账户**被创建（John, Jane, Alice, Tom）
- ✅ **5 张 NFC 卡片**全部创建成功
- ✅ **4 封欢迎邮件**发送（Bob 已有账户，不发送）

#### 3️⃣ 登录凭证
所有新员工都使用：
- 📧 Email: 各自的邮箱
- 🔑 Password: `Welcome123@`

---

## 🔒 安全特性

| 特性 | 说明 |
|-----|------|
| ✅ **统一密码** | 所有员工使用相同的默认密码 `Welcome123@` |
| ✅ **密码加密** | 使用 `Hash::make()` 加密存储，不存储明文 |
| ✅ **账户验证** | 自动检查邮箱是否已存在，避免重复创建 |
| ✅ **事务处理** | 使用 Database Transaction，失败自动回滚 |
| ✅ **错误容错** | 即使创建账户失败，NFC 卡片仍会创建 |
| ✅ **详细日志** | 所有操作都有完整的日志记录 |

---

## 📋 数据库结构

### Users 表 - Employee 账户

```sql
INSERT INTO users (
    first_name,
    last_name,
    email,
    password,
    phone,
    job_title,
    company,
    subscription_plan,
    subscription_active,
    parent_business_id
) VALUES (
    'John',
    'Doe',
    'john@company.com',
    '$2y$10$...[hashed_Welcome123@]',  -- Hash 加密
    '+60123456789',
    'Manager',
    'ABC Company',
    'premium',
    1,  -- Active
    123  -- Business Admin ID
);
```

### NFC Cards 表 - Employee 卡片

```sql
INSERT INTO nfc_cards (
    user_id,              -- Employee's user_id
    business_account_id,  -- Business Admin's ID
    nfc_card_id,
    card_owner,
    contact_number,
    subscription_plan
) VALUES (
    234,                  -- John's user_id
    123,                  -- Business Admin ID
    'NFC-ABC123DEF456',
    'John Doe',
    '+60123456789',
    'business'
);
```

---

## 🎯 关键代码位置

### 后端

**文件：** `backend/app/Http/Controllers/Api/AdminNotificationController.php`

**方法：** `approveOrder($id)`

**关键逻辑：**
```php
// Line ~336
$defaultPassword = 'Welcome123@';

// Line ~354
'password' => Hash::make($defaultPassword),

// Line ~374
'temporary_password' => $defaultPassword,
```

### 前端

**文件：** `frontend/pages/AdminManagement/notifications.vue`

**方法：** `approveOrder(notification)`

**关键逻辑：**
```javascript
// Line ~1361
confirmMessage += `✓ Set default password: Welcome123@\n`;

// Line ~1384-1385
successMessage += `\n👥 ${employeesCreated} employee account(s) created`;
successMessage += `\n📧 Login credentials sent to employees`;
```

---

## 📝 测试步骤

### 1. 准备测试
- 确保有 Business Admin 账户
- 准备 2-3 个员工信息（姓名、邮箱、职位、电话）

### 2. 提交订单
1. Business Admin 登录
2. 进入 `Card Design` → `Business Plan NFC Card`
3. 添加员工信息（手动或 CSV）
4. 提交订单

### 3. Admin 批准
1. Super Admin 登录
2. 进入 `Notification Management`
3. 找到订单通知
4. 点击 **Approve**
5. 确认弹窗中验证信息

### 4. 验证结果

#### ✅ 检查数据库
```sql
-- 检查员工账户
SELECT * FROM users 
WHERE email IN ('john@company.com', 'jane@company.com');

-- 检查 NFC 卡片
SELECT * FROM nfc_cards 
WHERE business_account_id = [Business Admin ID];

-- 检查通知
SELECT * FROM notifications 
WHERE type = 'account_created';
```

#### ✅ 测试登录
1. 使用员工邮箱：`john@company.com`
2. 使用密码：`Welcome123@`
3. 验证能成功登录
4. 验证能看到自己的 NFC 卡片

### 5. 测试重复创建
1. 再次提交相同员工的订单
2. Admin 批准
3. 验证：
   - ✅ 不会创建重复账户
   - ✅ 会显示 "employee(s) already had accounts"
   - ✅ 仍会创建新的 NFC 卡片

---

## 🐛 故障排查

### 问题：员工无法登录

**检查：**
```sql
SELECT email, subscription_active, parent_business_id 
FROM users 
WHERE email = 'john@company.com';
```

**可能原因：**
- `subscription_active` = 0 → 账户被停用
- `parent_business_id` = NULL → 不是员工账户

### 问题：密码不正确

**验证：**
```php
// 在 tinker 中测试
$user = User::where('email', 'john@company.com')->first();
Hash::check('Welcome123@', $user->password); // 应该返回 true
```

### 问题：员工看不到 NFC 卡片

**检查：**
```sql
SELECT * FROM nfc_cards 
WHERE user_id = [Employee User ID];
```

**可能原因：**
- `user_id` 没有正确设置为员工的 ID
- 卡片关联到了 Business Admin 而不是 Employee

---

## 📚 相关文档

- `ADMIN_ORDER_APPROVAL_SETUP.md` - Admin 批准流程
- `EMPLOYEE_ACCOUNT_MANAGEMENT.md` - 员工账户管理
- `BUSINESS_PLAN_TESTING.md` - Business Plan 测试

---

## ✅ 总结

**现在的流程非常简单：**

1. Business Admin 上传员工信息 📤
2. Super Admin 点击 Approve ✅
3. 系统自动创建一切 🤖
   - Employee accounts
   - NFC cards
   - Welcome notifications
4. Employees 登录使用 `Welcome123@` 🔑

**完全自动化，零手动操作！** 🚀

---

## 🔑 默认密码信息

| 项目 | 值 |
|-----|---|
| **默认密码** | `Welcome123@` |
| **适用对象** | 所有通过订单创建的 employee 账户 |
| **安全性** | Hash 加密存储，不存储明文 |
| **修改建议** | 建议员工首次登录后修改密码 |
| **重置方式** | Business Admin 可在员工管理页面重置 |

---

**最后更新：** 2025-11-19  
**版本：** 2.0 - 使用默认密码 Welcome123@
