# Profile Builder Fields 后端设置指南

## ✅ 已创建的文件

1. **数据库迁移**
   - `backend/database/migrations/2025_11_21_000000_create_profile_builder_fields_table.php`

2. **Model**
   - `backend/app/Models/ProfileBuilderField.php`

3. **Controller**
   - `backend/app/Http/Controllers/Api/Admin/ProfileBuilderFieldController.php`

4. **路由**
   - 已在 `backend/routes/api.php` 中添加

## 🚀 运行步骤

### 1. 运行数据库迁移

打开终端，进入 backend 目录：

```bash
cd backend
php artisan migrate
```

### 2. 清除缓存

```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### 3. 重启服务器（如果正在运行）

```bash
# 停止当前服务器 (Ctrl+C)
# 然后重新启动
php artisan serve
```

## 📋 API 端点

现在可用的 API 端点：

```
GET    /api/admin/profile-builder-fields       - 获取所有字段（按 tab 分组）
POST   /api/admin/profile-builder-fields       - 创建新字段
PUT    /api/admin/profile-builder-fields/{id}  - 更新字段
DELETE /api/admin/profile-builder-fields/{id}  - 删除字段
```

## 🎯 测试

运行迁移后，刷新前端页面：
1. 访问 `/AdminManagement/profile-builder-design`
2. 点击任意 Field Tabs（Profile Fields, Company Fields 等）
3. 应该不再显示 "Failed to load fields"
4. 可以添加/编辑/删除字段

## 📊 数据库表结构

```sql
profile_builder_fields
├── id
├── tab (profile, company, services, links)
├── field_key (唯一)
├── field_type (text, email, tel, url, textarea, number, image, repeater)
├── label
├── placeholder
├── help_text
├── is_required
├── is_visible
├── validation_rules (JSON)
├── available_plans (JSON)
├── display_order
├── config (JSON)
├── created_at
└── updated_at
```

## 🔧 如果遇到问题

### 错误：Class not found
```bash
composer dump-autoload
```

### 错误：Migration already exists
检查是否已经有类似的迁移文件，删除旧的再运行

### 错误：SQLSTATE[42S01]: Base table already exists
```bash
php artisan migrate:rollback --step=1
php artisan migrate
```

## ✨ 完成！

运行这些命令后，Profile Builder Fields 管理功能就完全可用了！
