# 🔒 订单验证和查看权限 - 完整实现

## ✅ 功能总览

### 1. **订单验证 - 防止重复 Email**

当 Super Admin 批准 Business Plan NFC 卡订单时：
- ✅ **预先验证**所有 employee emails 是否已存在
- ❌ **拒绝整个订单**如果发现重复的 employee email
- ✅ **显示详细错误**列出所有重复的 employees
- ✅ **保护数据完整性**确保 card 和 account 始终一对一

### 2. **Business Admin 查看权限**

Business Plan User 可以：
- ✅ 查看**所有旗下 employee 的 NFC 卡片**
- ✅ 查看**所有旗下 employee 的账户信息**
- ✅ 管理**employee 的卡片和账户**
- ✅ 查看**完整的 business account 统计**

---

## 🔍 订单验证流程

### 场景：重复 Email 检测

#### 订单包含：
```javascript
{
  cards: [
    { name: "Admin", email: "admin@co.com", is_admin_card: true },
    { name: "John Doe", email: "john@co.com", is_employee_card: true },  // ✅ 新员工
    { name: "Jane Smith", email: "jane@co.com", is_employee_card: true }  // ❌ 已存在
  ]
}
```

#### Admin 点击 Approve

**后端预验证：**
```php
// Line 315-346: AdminNotificationController.php
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

// If any employee accounts already exist, reject the order
if (count($existingEmployees) > 0) {
    return response()->json([
        'success' => false,
        'message' => "Cannot approve order: ..." ,
        'existing_employees' => $existingEmployees,
    ], 400);
}
```

**前端显示错误：**
```
❌ Cannot approve order!

1 employee email(s) already exist:

• Jane Smith (jane@co.com)

These employees already have accounts in the system.
```

### 验证逻辑图

```
Admin 点击 Approve
        ↓
┌─────────────────────────┐
│ 步骤 1: 预验证所有 Emails  │
└─────────────────────────┘
        ↓
    检查每个 employee email
        ↓
    ┌──────────────┐
    │ Email 已存在？│
    └──────────────┘
       ↙          ↘
    是              否
     ↓              ↓
❌ 拒绝订单      ✅ 继续批准
     ↓              ↓
 返回错误列表    创建 accounts + cards
     ↓              ↓
 显示重复员工    发送通知
```

---

## 👥 Business Admin 查看权限

### 数据库结构

#### Users 表
```sql
id | email            | parent_business_id | subscription_plan
---+------------------+--------------------+------------------
123 | admin@co.com    | NULL               | business         -- Business Admin
234 | john@co.com     | 123                | premium          -- Employee
235 | jane@co.com     | 123                | premium          -- Employee
```

#### NFC Cards 表
```sql
id | user_id | business_account_id | card_owner
---+---------+---------------------+-------------
1  | 123     | 123                 | Admin User     -- Admin 的卡
2  | 234     | 123                 | John Doe       -- John 的卡 (link 到 John)
3  | 235     | 123                 | Jane Smith     -- Jane 的卡 (link 到 Jane)
```

### API 查询逻辑

#### 获取所有 NFC 卡片

**文件：** `backend/app/Http/Controllers/Api/NfcCardController.php`

```php
// Line 37-54
if ($user->isBusinessAccount()) {
    // Business Admin: 获取所有旗下卡片
    $nfcCards = NfcCard::where('business_account_id', $user->id)
        ->with(['nfcTag', 'user:id,first_name,last_name,email,job_title'])
        ->orderBy('created_at', 'desc')
        ->get();
} else {
    // Employee: 只获取自己的卡片
    $nfcCards = $user->nfcCards()->with('nfcTag')->orderBy('created_at', 'desc')->get();
}
```

#### 查看单个卡片详情

```php
// Line 62-92
$isOwner = $nfcCard->user_id === $user->id;
$isBusinessAdmin = $user->isBusinessAccount() && $nfcCard->business_account_id === $user->id;

if (!$isOwner && !$isBusinessAdmin) {
    return response()->json(['message' => 'Unauthorized'], 403);
}
```

### 权限矩阵

| 用户类型 | 查看自己的卡片 | 查看员工的卡片 | 查看员工账户 | 创建员工账户 |
|---------|--------------|--------------|------------|------------|
| **Business Admin** | ✅ | ✅ | ✅ | ✅ |
| **Employee** | ✅ | ❌ | ❌ | ❌ |
| **Super Admin** | ✅ | ✅ | ✅ | ✅ |

---

## 📊 实际示例

### 场景 1：正常批准（无重复）

**订单：**
```javascript
{
  cards: [
    { name: "Admin", email: "admin@co.com", is_admin_card: true },
    { name: "New John", email: "newjohn@co.com", is_employee_card: true },
    { name: "New Jane", email: "newjane@co.com", is_employee_card: true }
  ]
}
```

**结果：**
```
✅ Order approved successfully!

📦 3 NFC card(s) created
👥 2 employee account(s) created
📧 Login credentials sent to employees
```

**数据库：**
- ✅ 2 个新的 employee accounts 创建
- ✅ 3 张 NFC cards 创建
- ✅ 所有卡片正确链接到对应的 user

### 场景 2：包含重复 Email

**订单：**
```javascript
{
  cards: [
    { name: "Admin", email: "admin@co.com", is_admin_card: true },
    { name: "John", email: "john@co.com", is_employee_card: true },      // ✅ 新
    { name: "Jane", email: "jane@co.com", is_employee_card: true },      // ❌ 已存在
    { name: "Bob", email: "bob@co.com", is_employee_card: true }         // ❌ 已存在
  ]
}
```

**结果：**
```
❌ Cannot approve order!

2 employee email(s) already exist:

• Jane Smith (jane@co.com)
• Bob Wilson (bob@co.com)

These employees already have accounts in the system.
```

**数据库：**
- ❌ **整个订单被拒绝**
- ❌ 没有创建任何 account
- ❌ 没有创建任何 card
- ✅ 数据完整性保持不变

---

## 🎯 Business Admin 视图

### 卡片列表

当 Business Admin 查看卡片列表时，看到：

```json
{
  "success": true,
  "nfc_cards": [
    {
      "id": 1,
      "nfc_card_id": "NFC-ABC123",
      "card_owner": "Admin User",
      "user_id": 123,
      "business_account_id": 123,
      "user": {
        "id": 123,
        "first_name": "Admin",
        "last_name": "User",
        "email": "admin@co.com",
        "job_title": "CEO"
      }
    },
    {
      "id": 2,
      "nfc_card_id": "NFC-DEF456",
      "card_owner": "John Doe",
      "user_id": 234,
      "business_account_id": 123,
      "user": {
        "id": 234,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john@co.com",
        "job_title": "Manager"
      }
    },
    {
      "id": 3,
      "nfc_card_id": "NFC-GHI789",
      "card_owner": "Jane Smith",
      "user_id": 235,
      "business_account_id": 123,
      "user": {
        "id": 235,
        "first_name": "Jane",
        "last_name": "Smith",
        "email": "jane@co.com",
        "job_title": "Developer"
      }
    }
  ]
}
```

### 员工列表

在 `BusinessEmployeeManagement.vue` 中，Business Admin 可以看到：

```json
{
  "success": true,
  "employees": [
    {
      "id": 234,
      "full_name": "John Doe",
      "email": "john@co.com",
      "job_title": "Manager",
      "parent_business_id": 123,
      "subscription_active": true,
      "nfc_cards_count": 1
    },
    {
      "id": 235,
      "full_name": "Jane Smith",
      "email": "jane@co.com",
      "job_title": "Developer",
      "parent_business_id": 123,
      "subscription_active": true,
      "nfc_cards_count": 1
    }
  ]
}
```

---

## 🔒 安全特性

### 1. 预验证（Pre-Validation）

| 特性 | 说明 |
|-----|------|
| ✅ **邮箱唯一性** | 检查所有 employee emails 是否已存在 |
| ✅ **原子操作** | 如果有重复，整个订单被拒绝 |
| ✅ **详细反馈** | 列出所有重复的 employees |
| ✅ **数据完整性** | 确保 card 和 account 一对一关系 |

### 2. 访问控制（Access Control）

| 操作 | Business Admin | Employee | Super Admin |
|-----|----------------|----------|-------------|
| **查看自己的卡片** | ✅ | ✅ | ✅ |
| **查看员工卡片** | ✅ | ❌ | ✅ |
| **修改员工卡片** | ✅ | ❌ | ✅ |
| **删除员工卡片** | ✅ | ❌ | ✅ |
| **查看所有账户** | ✅ (旗下) | ❌ | ✅ (所有) |

### 3. 数据关联（Data Linking）

```
NFC Card
  ↓ user_id (card owner)
Employee Account
  ↓ parent_business_id
Business Admin Account
```

**确保：**
- ✅ Employee 的卡片 `user_id` = Employee 的 ID
- ✅ Employee 的卡片 `business_account_id` = Business Admin 的 ID
- ✅ Employee 账户 `parent_business_id` = Business Admin 的 ID

---

## 🧪 测试场景

### 测试 1: 订单包含重复 Email

**步骤：**
1. 确保数据库中已有 `jane@co.com` 账户
2. Business Admin 提交订单，包含 `jane@co.com`
3. Super Admin 点击 Approve

**预期结果：**
```
❌ Cannot approve order!

1 employee email(s) already exist:
• Jane Smith (jane@co.com)
```

**验证：**
```sql
-- 检查没有创建新账户
SELECT COUNT(*) FROM users WHERE email = 'jane@co.com';
-- 应该仍然是 1

-- 检查没有创建新卡片
SELECT COUNT(*) FROM nfc_cards WHERE created_at > NOW() - INTERVAL 1 MINUTE;
-- 应该是 0
```

### 测试 2: Business Admin 查看所有卡片

**步骤：**
1. Business Admin 登录
2. 访问 NFC Cards 页面

**预期结果：**
- ✅ 看到自己的卡片
- ✅ 看到所有员工的卡片
- ✅ 每张卡片显示对应的 user 信息

**验证：**
```javascript
// API 调用
GET /api/nfc-cards

// Response 应该包含
{
  nfc_cards: [
    { card_owner: "Admin", user: {...} },
    { card_owner: "Employee 1", user: {...} },
    { card_owner: "Employee 2", user: {...} }
  ]
}
```

### 测试 3: Employee 只能查看自己的卡片

**步骤：**
1. Employee 登录
2. 访问 NFC Cards 页面

**预期结果：**
- ✅ 只看到自己的卡片
- ❌ 看不到其他员工的卡片
- ❌ 看不到 Business Admin 的卡片

**验证：**
```javascript
// API 调用
GET /api/nfc-cards

// Response 应该只包含
{
  nfc_cards: [
    { card_owner: "Current Employee", user_id: [current_user_id] }
  ]
}
```

---

## 📋 关键代码位置

### 1. 订单验证

**文件：** `backend/app/Http/Controllers/Api/AdminNotificationController.php`

| 行数 | 功能 |
|-----|------|
| 315-328 | 预验证所有 employee emails |
| 330-346 | 如果有重复，拒绝订单并返回错误 |
| 359-429 | 创建 employee accounts 和 NFC cards |

### 2. 前端错误显示

**文件：** `frontend/pages/AdminManagement/notifications.vue`

| 行数 | 功能 |
|-----|------|
| 1411-1425 | 显示重复 email 错误信息 |
| 1374-1406 | 显示成功消息 |

### 3. NFC 卡片查询

**文件：** `backend/app/Http/Controllers/Api/NfcCardController.php`

| 行数 | 功能 |
|-----|------|
| 37-54 | Business Admin 查看所有旗下卡片 |
| 62-92 | 权限检查（owner 或 Business Admin）|

---

## ✅ 功能总结

### ✅ 已实现功能

1. **订单预验证**
   - ✅ 检查重复 employee emails
   - ✅ 拒绝包含重复 email 的订单
   - ✅ 显示详细的错误信息

2. **Business Admin 权限**
   - ✅ 查看所有旗下 employee 的 NFC 卡片
   - ✅ 查看所有旗下 employee 的账户
   - ✅ 管理 employee 的卡片和账户

3. **数据完整性**
   - ✅ Employee card 正确链接到 employee account
   - ✅ 使用 `user_id` 关联 card owner
   - ✅ 使用 `business_account_id` 关联 business admin
   - ✅ 使用 `parent_business_id` 关联 employee 到 business

4. **安全控制**
   - ✅ 原子操作（全部成功或全部失败）
   - ✅ 权限验证（只能查看授权的数据）
   - ✅ 详细日志记录

---

## 🎯 核心原则

1. **Card 和 Account 必须一对一**
   - 不允许创建卡片而不创建账户
   - 不允许重复的 employee email

2. **Business Admin 拥有完整可见性**
   - 可以看到所有旗下 employee 的卡片
   - 可以看到所有旗下 employee 的账户
   - 可以管理所有旗下资源

3. **Employee 只能访问自己的资源**
   - 只能看到自己的卡片
   - 只能修改自己的数据

---

**最后更新：** 2025-11-19  
**版本：** 3.0 - 添加订单验证和 Business Admin 查看权限
