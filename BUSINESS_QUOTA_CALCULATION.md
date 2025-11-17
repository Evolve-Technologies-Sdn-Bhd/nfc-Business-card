# Business Account Quota Calculation System

## 概述 (Overview)

Business 账户和其 employees 之间的 quota 计算系统，用于管理员工账户数量和 NFC 卡订购数量。

---

## 数据结构 (Data Structure)

### Users Table 字段

```sql
- total_account_slots: INT DEFAULT 0  -- 总员工账户配额
- total_card_quota: INT DEFAULT 0      -- 总NFC卡订购配额
- parent_business_id: BIGINT NULLABLE  -- 父级Business账户ID（员工用）
```

### NFC Cards Table 字段

```sql
- business_account_id: BIGINT NULLABLE -- 所属Business账户ID（用于quota统计）
```

---

## Quota 计算逻辑 (Calculation Logic)

### 1. Business 账户拥有者 (Business Account Owner)

**条件**: `subscription_plan = 'business'` AND `parent_business_id IS NULL`

**Quota 计算公式**:

```php
// 员工账户使用量
$employeesCount = User::where('parent_business_id', $businessAccountId)->count();

// NFC卡订购使用量
$orderedCardsCount = NfcCard::where('business_account_id', $businessAccountId)
    ->where('subscription_plan', 'business')
    ->count();

// 可用配额
$availableAccountSlots = $total_account_slots - $employeesCount;
$availableCardQuota = $total_card_quota - $orderedCardsCount;
```

**示例**:

```json
{
  "total_account_slots": 50, // 管理员设置的总配额
  "employees_count": 12, // 已创建员工数
  "available_account_slots": 38, // 可创建员工数 (50 - 12)

  "total_card_quota": 50, // 管理员设置的总配额
  "ordered_cards_count": 15, // 已订购卡数（包括admin本人和所有员工）
  "available_card_quota": 35 // 可订购卡数 (50 - 15)
}
```

### 2. 员工账户 (Employee Account)

**条件**: `parent_business_id IS NOT NULL`

**特点**:

- 继承父级 Business 账户的 quota
- 可以订购 NFC 卡（计入 Business 账户的 quota）
- 不能创建员工
- 不能查看 quota 统计

**订购卡时**:

```php
// 查找父级Business账户
$businessAccount = User::find($employee->parent_business_id);

// 检查父级账户的quota
$quotaInfo = $businessAccount->getQuotaInfo();
if ($quotaInfo['available_card_quota'] <= 0) {
    // 配额已满，不能订购
}

// 创建NFC卡时设置business_account_id
NfcCard::create([
    'user_id' => $employee->id,
    'business_account_id' => $businessAccount->id,
    'subscription_plan' => 'business',
    // ...
]);
```

---

## API 端点 (API Endpoints)

### 1. GET /api/business/card-quota

**用途**: 获取当前 Business 账户的 quota 信息

**权限**: Business 账户拥有者 或 员工

**响应**:

```json
{
  "success": true,
  "data": {
    "total_card_quota": 50,
    "ordered_cards_count": 15,
    "available_card_quota": 35,
    "business_account_id": 123,
    "account_info": {
      "total_account_slots": 50,
      "employees_count": 12,
      "available_account_slots": 38
    }
  }
}
```

### 2. GET /api/business/employees

**用途**: 获取员工列表及 quota 信息

**权限**: 仅 Business 账户拥有者

**响应**:

```json
{
  "success": true,
  "data": {
    "employees": [
      {
        "id": 456,
        "full_name": "Jane Smith",
        "email": "jane@company.com",
        "job_title": "Sales Manager",
        "created_at": "2025-01-01T10:00:00Z"
      }
    ],
    "quota_info": {
      "total_account_slots": 50,
      "employees_count": 12,
      "available_account_slots": 38,
      "total_card_quota": 50,
      "ordered_cards_count": 15,
      "available_card_quota": 35
    }
  }
}
```

### 3. POST /api/business/employees

**用途**: 创建新员工账户

**权限**: 仅 Business 账户拥有者

**请求**:

```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@company.com",
  "phone": "+60123456789",
  "job_title": "Sales Rep",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!"
}
```

**验证逻辑**:

```php
// 1. 检查quota是否已满
if ($availableAccountSlots <= 0) {
    return error('Account slots quota exceeded');
}

// 2. 创建员工账户
$employee = User::create([
    'parent_business_id' => $businessAccount->id,
    'subscription_plan' => 'business',
    'company' => $businessAccount->company, // 继承公司名
    // ...
]);
```

### 4. GET /api/business/can-order-card

**用途**: 检查是否可以订购 NFC 卡

**权限**: Business 账户拥有者 或 员工

**响应**:

```json
{
  "success": true,
  "data": {
    "can_order": true,
    "total_card_quota": 50,
    "ordered_cards_count": 15,
    "available_card_quota": 35,
    "message": "Card can be ordered"
  }
}
```

---

## Admin 管理端点 (Admin Endpoints)

### 1. POST /api/admin/users (创建 Business 账户)

**请求**:

```json
{
  "first_name": "Company",
  "last_name": "Admin",
  "email": "admin@company.com",
  "password": "SecurePass123!",
  "subscription_plan": "business",
  "total_account_slots": 50,
  "total_card_quota": 50
}
```

**逻辑**:

```php
if ($subscriptionPlan === 'business') {
    $user->total_account_slots = $request->total_account_slots ?? 10;
    $user->total_card_quota = $request->total_card_quota ?? 10;
} else {
    $user->total_account_slots = 0;
    $user->total_card_quota = 0;
}
```

### 2. PUT /api/admin/users/{id} (更新 Business 账户 quota)

**请求**:

```json
{
  "subscription_plan": "business",
  "total_account_slots": 100,
  "total_card_quota": 100
}
```

**验证规则**:

```php
// 不能低于当前使用量
$quotaInfo = $user->getQuotaInfo();

if ($newCardQuota < $quotaInfo['ordered_cards_count']) {
    return error("Cannot set card quota to {$newCardQuota}. Already ordered: {$quotaInfo['ordered_cards_count']} cards.");
}

if ($newAccountSlots < $quotaInfo['employees_count']) {
    return error("Cannot set account slots to {$newAccountSlots}. Current employees: {$quotaInfo['employees_count']}.");
}
```

### 3. GET /api/admin/users (列出用户时显示 quota)

**响应**:

```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 123,
        "full_name": "Company Admin",
        "subscription_plan": "business",
        "quota_info": {
          "total_account_slots": 50,
          "employees_count": 12,
          "available_account_slots": 38,
          "total_card_quota": 50,
          "ordered_cards_count": 15,
          "available_card_quota": 35
        }
      }
    ]
  }
}
```

---

## User Model 辅助方法 (Helper Methods)

```php
// 检查是否为Business账户拥有者
$user->isBusinessAccount(): bool

// 检查是否为员工
$user->isBusinessEmployee(): bool

// 获取Business账户ID（自己或父级）
$user->business_account_id: int

// 获取quota信息
$user->getQuotaInfo(): array
// 返回: [
//   'total_account_slots' => 50,
//   'employees_count' => 12,
//   'available_account_slots' => 38,
//   'total_card_quota' => 50,
//   'ordered_cards_count' => 15,
//   'available_card_quota' => 35
// ]

// 获取员工列表
$user->employees(): HasMany

// 获取父级Business账户
$user->parentBusiness(): BelongsTo
```

---

## 业务规则 (Business Rules)

### 创建员工时

1. ✅ 检查`available_account_slots > 0`
2. ✅ 设置`parent_business_id = Business账户ID`
3. ✅ 继承父级的`company`名称
4. ✅ 设置`subscription_plan = 'business'`
5. ✅ 不设置 quota 字段（员工不需要）

### 订购 NFC 卡时

1. ✅ 检查`available_card_quota > 0`
2. ✅ 设置`business_account_id = Business账户ID`
3. ✅ 设置`subscription_plan = 'business'`
4. ✅ Business 账户拥有者和员工都可以订购
5. ✅ 所有订购都计入 Business 账户的 quota

### Admin 更新 quota 时

1. ✅ `total_card_quota` >= `ordered_cards_count`
2. ✅ `total_account_slots` >= `employees_count`
3. ✅ 从 Business plan 改为其他 plan 时，必须先删除所有员工
4. ✅ 非 Business plan 的 quota 字段设为 0

### 删除员工时

1. ✅ 检查员工是否有订购的 NFC 卡
2. ✅ 如果有卡，必须先转移或删除卡
3. ✅ 删除后，`available_account_slots`自动增加

---

## 使用场景示例 (Use Cases)

### 场景 1: 创建 Business 账户

```
Admin创建Business账户:
- subscription_plan: 'business'
- total_account_slots: 50
- total_card_quota: 50

结果:
- 可创建50个员工账户
- 可订购50张NFC卡（包括admin本人）
```

### 场景 2: Business 账户创建员工

```
Business账户拥有者创建员工:
1. 检查 available_account_slots > 0 (50 - 0 = 50)
2. 创建员工账户 (parent_business_id = Business账户ID)
3. employees_count: 0 → 1
4. available_account_slots: 50 → 49
```

### 场景 3: 员工订购 NFC 卡

```
员工订购NFC卡:
1. 查找 parent_business_id → Business账户
2. 检查 Business账户的 available_card_quota > 0
3. 创建NFC卡 (business_account_id = Business账户ID)
4. ordered_cards_count 增加
5. available_card_quota 减少
```

### 场景 4: Admin 增加 quota

```
Admin更新Business账户:
- 当前: total_card_quota = 50, ordered = 45
- 请求: total_card_quota = 100

验证:
✅ 100 >= 45 (当前已订购数)
✅ 更新成功
✅ available_card_quota: 5 → 55
```

### 场景 5: Admin 尝试减少 quota（失败）

```
Admin更新Business账户:
- 当前: total_card_quota = 50, ordered = 45
- 请求: total_card_quota = 40

验证:
❌ 40 < 45 (当前已订购数)
❌ 返回错误: "Cannot set card quota to 40. Already ordered: 45 cards."
```

---

## 数据库迁移 (Database Migration)

运行迁移:

```bash
cd backend
php artisan migrate
```

迁移文件: `2025_11_14_100000_add_business_quota_fields.php`

添加的字段:

- `users.total_account_slots` (INT, DEFAULT 0)
- `users.total_card_quota` (INT, DEFAULT 0)
- `users.parent_business_id` (BIGINT, NULLABLE, FK to users.id)
- `nfc_cards.business_account_id` (BIGINT, NULLABLE, FK to users.id)

---

## 前端集成指南 (Frontend Integration)

### BusinessCardManagement.vue

```javascript
// 加载卡片quota信息
async function loadCardQuota() {
  const response = await $api.get("/business/card-quota");
  if (response.success) {
    totalCardQuota.value = response.data.total_card_quota;
    orderedCards.value = response.data.ordered_cards_count;
    availableQuota.value = response.data.available_card_quota;
  }
}

// 订购卡片前检查
async function orderCard() {
  const checkResponse = await $api.get("/business/can-order-card");
  if (!checkResponse.data.can_order) {
    $toast.error("Card quota exceeded. Please contact admin.");
    return;
  }

  // 继续订购流程
  const response = await $api.post("/nfc-cards", cardData);
}
```

### BusinessEmployeeManagement.vue

```javascript
// 加载员工列表和quota
async function loadEmployees() {
  const response = await $api.get("/business/employees");
  if (response.success) {
    employees.value = response.data.employees;
    quotaInfo.value = response.data.quota_info;
  }
}

// 创建员工
async function createEmployee(employeeData) {
  if (quotaInfo.value.available_account_slots <= 0) {
    $toast.error("Employee quota exceeded. Please contact admin.");
    return;
  }

  const response = await $api.post("/business/employees", employeeData);
  if (response.success) {
    $toast.success("Employee created successfully");
    loadEmployees(); // 刷新列表
  }
}
```

---

## 总结 (Summary)

**Quota 计算核心公式**:

```
可用员工配额 = total_account_slots - COUNT(employees)
可用卡片配额 = total_card_quota - COUNT(business_cards)

其中:
- employees = Users WHERE parent_business_id = Business账户ID
- business_cards = NfcCards WHERE business_account_id = Business账户ID
                   AND subscription_plan = 'business'
```

**权限分配**:

- ✅ Admin: 设置和修改 quota
- ✅ Business 账户拥有者: 创建员工、查看 quota、订购卡片
- ✅ 员工: 订购卡片（使用父级 quota）

**数据流向**:

```
Admin设置quota
    ↓
Business账户获得quota
    ↓
创建员工 OR 订购卡片
    ↓
quota自动减少
    ↓
统计和显示剩余quota
```
