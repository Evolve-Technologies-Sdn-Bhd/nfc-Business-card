# 测试 Profile Builder API

## 问题诊断

用户反映 Business Plan 的 Profile Builder 没有显示应该显示的内容。

## 测试步骤

### 1. 测试 Tabs API

打开浏览器控制台（F12），然后访问 Profile Builder 页面。

查看控制台输出，应该看到：
```
Loading tabs config...
Loading fields config...
Loading design options...
```

### 2. 手动测试 API

在浏览器控制台运行：

```javascript
// 测试 Tab Control API
fetch('http://localhost:8000/api/profile-design-options?plan=business', {
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('token')}`
  }
})
.then(r => r.json())
.then(data => console.log('Tab Control:', data.data.tab_control))

// 测试 Fields API
fetch('http://localhost:8000/api/admin/profile-builder-fields', {
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('token')}`
  }
})
.then(r => r.json())
.then(data => console.log('Fields:', data))
```

### 3. 检查数据

#### 预期结果 - Tab Control
如果 Admin 已配置 Tab Control，应该看到：
```json
{
  "tab_control": [{
    "id": 1,
    "option_id": "default_tabs",
    "config": {
      "tabs": ["profile", "company", "services", "links", "design", "style"]
    },
    "is_active": true
  }]
}
```

#### 预期结果 - Fields
如果 Admin 已配置字段，应该看到：
```json
{
  "success": true,
  "data": {
    "profile": [
      {
        "id": 1,
        "field_key": "name",
        "label": "Full Name",
        "field_type": "text",
        "is_required": true,
        "is_visible": true
      }
    ],
    "company": [],
    "services": [],
    "links": []
  }
}
```

## 可能的问题

### 问题 1：没有配置数据
**症状**：API 返回空数组或空对象
**解决**：Admin 需要先在 `/AdminManagement/profile-builder-design` 中配置

### 问题 2：API 返回 404
**症状**：`Failed to load fields config`
**解决**：确保已运行迁移：
```bash
cd backend
php artisan migrate
php artisan route:clear
```

### 问题 3：权限问题
**症状**：API 返回 401 或 403
**解决**：检查用户是否已登录，token 是否有效

### 问题 4：CORS 问题
**症状**：浏览器控制台显示 CORS 错误
**解决**：检查后端 CORS 配置

## 当前实现状态

### ✅ 已完成
- 后端 API 端点
- 数据库表和模型
- 前端 API 调用函数

### ⚠️ 待完善
- 前端模板需要使用动态字段
- 目前仍然显示硬编码的字段
- 需要添加动态字段渲染逻辑

## 下一步

如果 API 正常返回数据，但页面没有显示，说明需要修改前端模板，使用动态加载的字段替换硬编码字段。
