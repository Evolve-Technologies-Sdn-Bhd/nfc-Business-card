# Bulk Employee Upload & Notification System

## 概述

此功能允许 Business 账户批量上传员工数据并创建 NFC 卡订单，同时向 Super Admin 发送置顶通知。

**重要说明：**

- ✅ **正确流程顺序：**
  1. Business Admin 上传员工数据 → 创建 NFC 卡订单
  2. 发送置顶通知给 Super Admin
  3. Super Admin 审批订单
  4. Super Admin 创建员工账户（审批通过后）
- ✅ **权限分离** - Business Admin 不能创建账户，只能提交订单
- ✅ **审批机制** - 所有订单需 Super Admin 审批后才创建员工账户

## 新增功能

### 1. 批量上传员工数据

- 支持 CSV 和 XLSX 文件格式
- 手动添加员工功能
- 实时预览上传的员工数据
- 下载 CSV 模板

### 2. 订单确认系统

- Place Order 前显示确认对话框
- 显示订单摘要和员工列表
- 确认后才提交订单

### 3. Super Admin 通知系统

- 高优先级置顶通知
- 包含订单详情和 Excel 文件
- 邮件通知支持

## 后端 API

### Super Admin APIs

#### 1. 创建 Business 员工账户 (POST /api/admin/business-employees)

**仅限 Super Admin 访问**

**请求参数：**

```json
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

**响应示例：**

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

### Business Admin APIs

#### 1. 批量上传员工数据 (POST /api/business/employees/upload)

**注意：此 API 不创建员工账户，只创建 NFC 卡订单**

**请求参数：**

```
- file: CSV/XLSX文件 (required, max 5MB)
- design_method: template | custom (required)
- selected_template: 模板名称 (required if design_method=template)
- front_design: 前面设计URL (required if design_method=custom)
- back_design: 后面设计URL (required if design_method=custom)
```

**响应示例：**

```json
{
  "success": true,
  "message": "3 employee card orders created successfully. Super Admin has been notified.",
  "data": {
    "created_count": 3,
    "error_count": 0,
    "cards": [
      {
        "id": 101,
        "name": "John Doe",
        "email": "john@company.com",
        "position": "Sales Manager"
      }
    ],
    "errors": [],
    "remaining_quota": 17
  }
}
```

### 2. 重置员工密码 (POST /api/business/employees/{id}/reset-password)

**Business Admin 可重置其员工密码**

**响应示例：**

```json
{
  "success": true,
  "message": "Password reset successfully. New password has been sent to employee's email.",
  "data": {
    "employee_email": "employee@company.com",
    "temporary_password": "Abc123XYZ456"
  }
}
```

### 3. 获取员工详情 (GET /api/business/employees/{id}/details)

**响应示例：**

```json
{
  "success": true,
  "data": {
    "employee": {
      "id": 123,
      "full_name": "John Doe",
      "email": "john@company.com",
      "phone": "+60 12-345 6789",
      "job_title": "Sales Manager"
    },
    "nfc_cards": [
      {
        "id": 101,
        "name": "John Doe",
        "position": "Sales Manager",
        "status": "pending",
        "order_date": "2025-11-14"
      }
    ]
  }
}
```

## 数据库更改

### 新增字段到 notifications 表：

```sql
ALTER TABLE notifications ADD COLUMN pinned BOOLEAN DEFAULT false;
ALTER TABLE notifications ADD COLUMN attachments JSON NULL;
```

### 新增 notification 类型：

- `business_bulk_order_placed` - Business 账户批量订单通知
- `employee_password_reset` - 员工密码重置通知

## CSV 文件格式

**必需列：**

- Name
- Email
- Position
- Contact Number
- Address
- Delivery Address

**可选列：**

- Website

**示例 CSV：**

```csv
Name,Email,Position,Contact Number,Website,Address,Delivery Address
John Doe,john@example.com,Sales Manager,+60 12-345 6789,https://example.com,"123 Main St, KL","123 Main St, KL"
Jane Smith,jane@example.com,Marketing Lead,+60 12-345 6790,https://example.com,"456 Oak Ave, PJ","456 Oak Ave, PJ"
```

## 前端集成

### BusinessPlanNFCCard.vue 新功能：

1. **订单类型选择**

   - Single Employee（单个员工）
   - Bulk Upload（批量上传）

2. **手动添加员工**

   ```javascript
   // 添加员工到列表
   addManualEmployee();

   // 移除员工
   removeEmployee(index);

   // 重置表单
   resetManualForm();
   ```

3. **文件上传**

   ```javascript
   // 处理文件上传
   handleEmployeeFileUpload(event);

   // 解析CSV
   parseCSV(csvData);

   // 下载模板
   downloadTemplate();
   ```

4. **确认对话框**

   ```javascript
   // 显示确认对话框
   showConfirmModal = true;

   // 确认并提交订单
   confirmOrder();
   ```

## 通知系统

### Super Admin 通知数据结构：

```json
{
  "type": "business_bulk_order_placed",
  "priority": "high",
  "pinned": true,
  "title": "🚨 New Bulk NFC Card Order",
  "message": "Company ABC placed a bulk order for 10 employee cards.",
  "business_account": {
    "id": 456,
    "name": "Business Owner",
    "company": "Company ABC",
    "email": "owner@company.com"
  },
  "order_details": {
    "employee_count": 10,
    "design_method": "template",
    "selected_template": "basic",
    "order_date": "2025-11-14 10:30:00"
  },
  "file_path": "employee-uploads/abc123.xlsx",
  "cards": [...]
}
```

### 通知排序规则：

1. Pinned 通知优先显示（置顶）
2. 按创建时间倒序

## 安装步骤

### 1. 运行数据库迁移

```bash
php artisan migrate
```

### 2. 安装 Excel 处理包（如果还未安装）

```bash
composer require maatwebsite/excel
```

### 3. 配置邮件通知

确保`.env`文件中配置了邮件设置：

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@nfcgo.com
MAIL_FROM_NAME="NFCGo"
```

### 4. 配置队列（推荐）

```bash
# 在.env中设置
QUEUE_CONNECTION=database

# 运行队列worker
php artisan queue:work
```

## 测试

### 测试批量上传：

```bash
# 使用Postman或curl
curl -X POST http://localhost:8000/api/business/employees/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@employees.csv" \
  -F "design_method=template" \
  -F "selected_template=basic"
```

### 测试重置密码：

```bash
curl -X POST http://localhost:8000/api/business/employees/123/reset-password \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 工作流程

### 完整流程（正确顺序）：

1. **Business Admin 上传员工数据**

   - Business Admin 登录后台
   - 导航到 "Order New Card"
   - 选择 Order Type: **Bulk Upload**
   - 上传 CSV/XLSX 文件或手动输入员工信息
   - 选择卡片设计（模板或自定义）
   - **注意：此步骤不创建员工账户，只准备订单数据**

2. **订单确认**

   - 系统显示确认对话框
   - 显示员工列表预览（10 人）
   - 显示订单摘要（设计方法、计划类型）
   - 警告提示：订单将发送给 Super Admin 审批
   - Business Admin 确认提交

3. **创建 NFC 卡订单**

   - 系统为每个员工创建 NFC 卡订单记录
   - 订单状态设为 **"pending"**（等待审批）
   - 存储 Excel 文件到服务器
   - **重要：此时不创建员工账户**

4. **发送通知给 Super Admin**

   - 系统自动发送**置顶通知**
   - 通知优先级：**high**
   - 通知状态：**pinned = true**
   - 通知内容包含：
     - Business 账户信息
     - 订单员工数量
     - Excel 文件路径
     - 订单详情（设计方法、模板等）
   - 发送邮件给所有 Super Admin

5. **Super Admin 审批订单**

   - Super Admin 收到置顶通知
   - 查看订单详情
   - 下载并审核 Excel 文件
   - 验证员工信息
   - 检查 Business 账户配额
   - 决定批准或拒绝

6. **Super Admin 创建员工账户（审批后）**

   - 从通知中的 Excel 获取员工信息
   - 使用 API `/api/admin/business-employees` 逐个或批量创建员工账户
   - 设置 `parent_business_id` 关联到 Business 账户
   - 生成安全密码
   - 发送欢迎邮件给员工
   - **此时员工才能登录系统**

7. **生产和发货 NFC 卡**
   - 更新订单状态：pending → approved → processing → shipped → delivered
   - 通知 Business Admin 和员工

## 注意事项

1. **权限分离**：

   - ❌ Business Admin **不能**创建员工账户
   - ✅ Business Admin **只能**上传员工数据用于 NFC 卡订单
   - ✅ 只有 Super Admin **可以**创建员工账户

2. **配额检查**：
   - Super Admin 创建员工时检查 Business 账户的 `total_account_slots`
   - Business Admin 上传数据时检查 NFC 卡配额
3. **文件大小限制**：最大 5MB
4. **支持格式**：CSV, XLSX
5. **必需字段验证**：Name, Email, Position 必须提供
6. **重复邮箱检查**：不能添加重复的员工邮箱
7. **当前用户排除**：不能将自己添加为员工

## 故障排除

### 问题：上传文件失败

- 检查文件格式是否正确（CSV 或 XLSX）
- 确认文件大小不超过 5MB
- 验证 CSV 列名是否正确

### 问题：通知未发送

- 检查队列是否运行：`php artisan queue:work`
- 检查数据库中是否有 Super Admin 用户
- 查看日志：`storage/logs/laravel.log`

### 问题：配额不足

- 联系 Super Admin 增加配额
- 检查`total_account_slots`字段

## 未来改进

1. ✅ 批量上传功能
2. ✅ 确认对话框
3. ✅ Super Admin 通知
4. ⏳ 邮件通知完善
5. ⏳ 员工账户自动创建
6. ⏳ 密码重置邮件模板
7. ⏳ Excel 文件预览功能
8. ⏳ 批量操作撤销功能
