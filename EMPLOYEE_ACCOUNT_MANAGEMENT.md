# 员工账户管理流程 (Employee Account Management)

## 概述

**正确流程：**

1. Business Admin 上传员工数据（CSV/XLSX）→ 创建 NFC 卡订单
2. 系统发送通知给 Super Admin（置顶）
3. Super Admin 审批订单
4. Super Admin 创建员工账户（审批后）

员工账户**必须由 Super Admin 创建**。Business Admin 只能上传员工数据用于 NFC 卡订单，不能创建员工账户。

## 角色和权限

### Super Admin

- ✅ 创建 Business 账户
- ✅ 创建员工账户（under Business 账户）
- ✅ 管理所有用户
- ✅ 审批 NFC 卡订单
- ✅ 调整配额（total_account_slots）

### Business Admin

- ✅ 查看员工列表
- ✅ 上传员工数据（仅用于 NFC 卡订单）
- ✅ 设计 NFC 卡
- ✅ 重置员工密码
- ❌ **不能**创建员工账户
- ❌ **不能**删除员工账户（需联系 Super Admin）

### 员工 (Employee)

- ✅ 登录系统
- ✅ 查看自己的 NFC 卡
- ✅ 更新个人信息
- ❌ 不能创建其他员工

## API 端点

### Super Admin APIs

#### 1. 创建 Business 员工账户

```http
POST /api/admin/business-employees
Authorization: Bearer {super_admin_token}
Content-Type: application/json

{
  "business_account_id": 123,
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@company.com",
  "phone": "+60 12-345 6789",
  "job_title": "Sales Manager",
  "password": "SecurePassword123"
}
```

**响应：**

```json
{
  "success": true,
  "message": "Employee account created successfully",
  "data": {
    "employee": {
      "id": 456,
      "full_name": "John Doe",
      "email": "john@company.com",
      "job_title": "Sales Manager",
      "parent_business_id": 123
    },
    "business_account": {
      "id": 123,
      "name": "Business Owner Name",
      "remaining_slots": 7
    }
  }
}
```

#### 配额验证

- 系统自动检查 Business 账户的 `total_account_slots`
- 如果配额不足，返回 403 错误
- Super Admin 可以通过 `/api/admin/users/{userId}` 更新配额

### Business Admin APIs

#### 1. 获取员工列表

```http
GET /api/business/employees
Authorization: Bearer {business_admin_token}
```

#### 2. 上传员工数据（创建 NFC 卡订单）

```http
POST /api/business/employees/upload
Authorization: Bearer {business_admin_token}
Content-Type: multipart/form-data

file: employees.csv
design_method: template
selected_template: basic
```

**注意：此 API 不创建员工账户，只创建 NFC 卡订单**

#### 3. 重置员工密码

```http
POST /api/business/employees/{id}/reset-password
Authorization: Bearer {business_admin_token}
```

#### 4. 获取员工详情

```http
GET /api/business/employees/{id}/details
Authorization: Bearer {business_admin_token}
```

#### ~~5. 创建员工（已废弃）~~

```http
POST /api/business/employees
```

**状态：** DEPRECATED - 此端点已禁用

**响应：**

```json
{
  "success": false,
  "message": "Employee accounts must be created by Super Admin. Business accounts can upload employee data for NFC card orders via /business/employees/upload endpoint.",
  "action_required": "Please contact Super Admin to create employee accounts."
}
```

## 完整工作流程

### 场景：Business 公司需要为 10 个员工创建账户和 NFC 卡

#### Step 1: Business Admin 上传员工数据并订购 NFC 卡

1. Business Admin 登录
2. 导航到 "Order New Card"
3. 选择 "Bulk Upload"
4. 上传 CSV 文件或手动输入员工信息：
   - Name
   - Email
   - Position
   - Contact Number
   - Address
   - Delivery Address
5. 选择卡片设计（模板或自定义）
6. 预览订单
7. 点击 "Place Order"
8. 确认对话框显示：
   - 订单摘要
   - 员工列表（10 人）
   - 警告：将通知 Super Admin 审批
9. 确认提交
10. 系统创建 NFC 卡订单（status: **pending**）
11. **注意：此时不创建员工账户**

#### Step 2: 系统发送通知给 Super Admin

1. 系统自动发送**置顶通知**给所有 Super Admin
2. 通知内容包括：
   - Business 账户信息
   - 订单员工数量（10 人）
   - Excel 文件附件
   - 订单详情
3. 通知优先级：**high**
4. 通知状态：**pinned**（置顶显示）
5. Super Admin 收到邮件通知

#### Step 3: Super Admin 审批订单

1. Super Admin 收到置顶通知
2. 点击查看订单详情
3. 下载并查看 Excel 文件
4. 验证员工信息
5. 检查 Business 账户配额
6. 审批决定：
   - ✅ **批准** → 进入 Step 4
   - ❌ **拒绝** → 通知 Business Admin 原因

#### Step 4: Super Admin 创建员工账户（审批后）

**方式 1: 手动逐个创建**

1. 从 Excel 文件获取员工信息
2. 对每个员工：
   - 导航到 "Add Employee"
   - 填写信息（First Name, Last Name, Email, Phone, Job Title）
   - 生成安全密码
   - 选择 Business 账户（parent_business_id）
   - 点击创建
3. 系统验证配额
4. 创建员工账户
5. 发送欢迎邮件给员工

**方式 2: 批量创建（推荐）**

Super Admin 可以使用批量创建功能：

```javascript
// 从通知中获取员工列表
const orderNotification = await getNotification(notificationId);
const employees = orderNotification.data.cards; // 从订单获取员工信息

// 批量创建员工账户
for (const emp of employees) {
  await fetch("/api/admin/business-employees", {
    method: "POST",
    headers: {
      Authorization: `Bearer ${superAdminToken}`,
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      business_account_id: orderNotification.data.business_account.id,
      first_name: extractFirstName(emp.name),
      last_name: extractLastName(emp.name),
      email: emp.email,
      phone: emp.contact_number,
      job_title: emp.position,
      password: generateSecurePassword(),
    }),
  });
}
```

#### Step 5: 生产和发货 NFC 卡

1. 更新订单状态：pending → **approved**
2. 生产 NFC 卡
3. 更新状态：approved → **processing**
4. 发货
5. 更新状态：processing → **shipped**
6. 配送完成
7. 更新状态：shipped → **delivered**
8. 通知 Business Admin 和员工

## 数据库结构

### users 表

```sql
id
first_name
last_name
email
password
phone
job_title
company
subscription_plan (business, premium, basic, super-admin)
subscription_active
parent_business_id (NULL for Business Admin, Business ID for Employees)
is_admin (true for Super Admin)
total_account_slots (只用于 Business 账户)
created_at
updated_at
```

### nfc_cards 表

```sql
id
user_id (Business Admin ID)
business_account_id (Business Admin ID)
name (员工姓名)
position (员工职位)
email (员工邮箱)
contact_number
business_address
delivery_address
subscription_plan (business)
design_method (template/custom)
selected_template
front_design
back_design
status (pending, approved, processing, shipped, delivered)
order_date
delivery_date
created_at
updated_at
```

## 配额管理

### total_account_slots (Business 账户专用)

- 同时控制**员工账户数量**和 **NFC 卡订单数量**
- 由 Super Admin 设置和调整

### 计算公式

```php
// 在 User Model 中
public function getQuotaInfo() {
    $totalSlots = $this->total_account_slots;
    $employeesCount = $this->employees()->count(); // 已创建的员工账户
    $orderedCardsCount = NfcCard::where('business_account_id', $this->id)->count();

    return [
        'total_account_slots' => $totalSlots,
        'employees_count' => $employeesCount,
        'available_account_slots' => $totalSlots - $employeesCount,
        'total_card_quota' => $totalSlots,
        'ordered_cards_count' => $orderedCardsCount,
        'available_card_quota' => $totalSlots - $orderedCardsCount,
        'available_quota' => min($totalSlots - $employeesCount, $totalSlots - $orderedCardsCount)
    ];
}
```

### 配额验证时机

1. **Super Admin 创建员工时**

   ```php
   if ($quotaInfo['available_account_slots'] <= 0) {
       return error('配额不足');
   }
   ```

2. **Business Admin 上传数据时**
   ```php
   if ($quotaInfo['available_card_quota'] < count($employees)) {
       return error('NFC 卡配额不足');
   }
   ```

## 前端 UI 提示

### Business Admin 页面

在 BusinessEmployeeManagement.vue 中显示：

```vue
<div class="info-message">
  <Icon name="heroicons:information-circle" />
  <p>
    Employee accounts are created by Super Admin. 
    You can upload employee details and design their NFC cards.
  </p>
</div>
```

如果 Business Admin 尝试创建员工，显示：

```vue
<div class="error-message">
  <Icon name="heroicons:exclamation-triangle" />
  <p>
    Only Super Admin can create employee accounts. 
    Please contact your administrator to add new employees.
  </p>
</div>
```

### Super Admin 页面

创建员工表单应该包含：

- Business Account 选择器
- Employee 信息输入
- 配额显示
- 创建按钮

## 测试用例

### 测试 1: Super Admin 创建员工

```bash
curl -X POST http://localhost:8000/api/admin/business-employees \
  -H "Authorization: Bearer SUPER_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "business_account_id": 123,
    "first_name": "Test",
    "last_name": "Employee",
    "email": "test@company.com",
    "phone": "+60123456789",
    "job_title": "Tester",
    "password": "Password123"
  }'
```

### 测试 2: Business Admin 尝试创建员工（应该失败）

```bash
curl -X POST http://localhost:8000/api/business/employees \
  -H "Authorization: Bearer BUSINESS_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "Test",
    "last_name": "Employee",
    "email": "test@company.com",
    "password": "Password123"
  }'

# 预期响应：
{
  "success": false,
  "message": "Employee accounts must be created by Super Admin...",
  "action_required": "Please contact Super Admin..."
}
```

### 测试 3: Business Admin 上传员工数据

```bash
curl -X POST http://localhost:8000/api/business/employees/upload \
  -H "Authorization: Bearer BUSINESS_ADMIN_TOKEN" \
  -F "file=@employees.csv" \
  -F "design_method=template" \
  -F "selected_template=basic"

# 应该成功创建 NFC 卡订单，但不创建员工账户
```

## 常见问题 (FAQ)

### Q: 为什么 Business Admin 不能创建员工？

A: 为了安全和权限管理，员工账户由 Super Admin 统一管理。这样可以：

- 确保账户创建符合公司政策
- 防止配额滥用
- 统一管理和审计

### Q: Business Admin 如何添加新员工？

A: Business Admin 应该：

1. 联系 Super Admin
2. 提供员工信息
3. Super Admin 创建员工账户
4. 然后 Business Admin 可以为员工上传数据并订购 NFC 卡

### Q: 配额如何计算？

A: `total_account_slots` 同时限制：

- 可创建的员工账户数量
- 可订购的 NFC 卡数量
- 两者共享同一个配额

### Q: 如果配额不足怎么办？

A: 联系 Super Admin 增加 `total_account_slots` 配额

## 安全考虑

1. **权限验证**：所有 API 都有严格的权限检查
2. **配额限制**：防止资源滥用
3. **邮箱唯一性**：防止重复账户
4. **密码安全**：使用 bcrypt 加密
5. **审计日志**：记录所有账户创建操作

## 迁移指南

如果之前允许 Business Admin 创建员工，迁移步骤：

1. 备份数据库
2. 运行迁移脚本
3. 更新前端代码，移除创建员工的 UI
4. 通知 Business Admin 新的流程
5. 培训 Super Admin 如何创建员工
6. 测试所有功能

## 总结

✅ **员工账户创建** = Super Admin only
✅ **员工数据上传** = Business Admin (用于 NFC 卡订单)
✅ **权限清晰** = 各司其职
✅ **安全可控** = 统一管理
