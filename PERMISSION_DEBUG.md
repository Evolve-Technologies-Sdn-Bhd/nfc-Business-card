# 权限问题诊断指南

## 问题描述
Settings.vue 页面显示 "没有权限" 错误。

## 诊断步骤

### 1. 检查浏览器控制台
打开浏览器开发者工具 (F12)，查看 Console 标签页：

```
🔄 Loading plan prices...
❌ /plans endpoint not available: 404 Not Found
❌ /plan-prices endpoint not available: 403 Forbidden
❌ /subscription/plans endpoint not available: 404 Not Found
👤 User is not admin, skipping admin endpoint
📋 Using fallback plan data (matches database plan_prices table)
```

### 2. 检查网络请求
在 Network 标签页中查看失败的 API 请求：
- 状态码 403: 权限被拒绝
- 状态码 401: 未认证/会话过期
- 状态码 404: 端点不存在

### 3. 检查认证状态
在 Console 中运行：
```javascript
// 检查认证令牌
localStorage.getItem('auth_token')

// 检查用户信息
JSON.parse(localStorage.getItem('user') || '{}')
```

### 4. 检查调试信息
页面左下角应该显示调试信息（仅开发模式）：
```
🔍 Debug Info
User: user@example.com
Role: user
Auth Token: ✅ Present
NFC Cards: 1 loaded
Plan Prices: 4 loaded
```

## 常见问题和解决方案

### 问题 1: 认证令牌丢失
**症状**: Auth Token: ❌ Missing
**解决方案**: 重新登录

### 问题 2: 会话过期
**症状**: 401 Unauthorized 错误
**解决方案**: 刷新页面或重新登录

### 问题 3: 后端 API 端点不存在
**症状**: 所有 API 调用返回 404
**解决方案**: 
1. 确认后端服务器运行
2. 检查 API 路由配置
3. 使用 fallback 数据（已自动启用）

### 问题 4: 用户权限不足
**症状**: 403 Forbidden 错误
**解决方案**: 
1. 确认用户有访问权限
2. 检查后端权限配置
3. 使用公开端点而不是管理员端点

## 后端需要的 API 端点

### 公开端点（所有认证用户可访问）
```
GET /api/plans
GET /api/plan-prices  
GET /api/subscription/plans
```

### 用户端点
```
GET /api/nfc-cards
GET /api/settings/sessions
PUT /api/settings/personal-info
```

## 临时解决方案

如果 API 端点不可用，Settings.vue 会自动：
1. 使用 fallback 计划数据
2. 显示模拟会话数据
3. 继续正常工作

## 测试步骤

1. **登录测试**
   ```bash
   # 确保用户已登录
   curl -H "Authorization: Bearer {token}" http://localhost:3000/api/me
   ```

2. **API 端点测试**
   ```bash
   # 测试计划价格端点
   curl -H "Authorization: Bearer {token}" http://localhost:3000/api/plans
   curl -H "Authorization: Bearer {token}" http://localhost:3000/api/plan-prices
   ```

3. **权限测试**
   ```bash
   # 测试用户数据端点
   curl -H "Authorization: Bearer {token}" http://localhost:3000/api/nfc-cards
   ```

## 联系开发者

如果问题持续存在，请提供：
1. 浏览器控制台截图
2. 网络请求详情
3. 用户角色和权限信息
4. 后端日志（如果可访问）
