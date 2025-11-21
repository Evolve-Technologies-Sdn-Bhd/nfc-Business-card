# 认证问题调试指南

## 问题现象
调试信息显示：
```
🔍 Debug Info
User: testing1@gmail.com
Role: admin
Auth Token: ❌ Missing
```

这表明用户信息存在，但认证令牌丢失。

## 可能的原因

### 1. Token 存储位置不匹配
不同的应用可能使用不同的 localStorage 键名：
- `auth_token`
- `token`  
- `access_token`
- `authToken`
- `sanctum_token`

### 2. Token 过期或被清除
- 浏览器清除了 localStorage
- Token 已过期但用户信息仍在 authStore 中
- 手动清除了浏览器数据

### 3. 认证流程问题
- 登录时没有正确保存 token
- authStore 和 localStorage 不同步

## 调试步骤

### 1. 检查浏览器存储
在浏览器控制台运行：
```javascript
// 检查 localStorage
console.log('localStorage keys:', Object.keys(localStorage));
console.log('auth_token:', localStorage.getItem('auth_token'));
console.log('token:', localStorage.getItem('token'));
console.log('access_token:', localStorage.getItem('access_token'));

// 检查 sessionStorage
console.log('sessionStorage keys:', Object.keys(sessionStorage));
console.log('auth_token:', sessionStorage.getItem('auth_token'));
```

### 2. 检查网络请求
在 Network 标签页中查看：
- API 请求是否包含 Authorization header
- 是否返回 401 Unauthorized 错误

### 3. 检查 authStore 状态
```javascript
// 在控制台检查
console.log('authStore.user:', useAuthStore().user);
console.log('authStore state:', useAuthStore().$state);
```

## 解决方案

### 1. 重新登录
最简单的解决方案：
- 退出登录
- 重新登录
- 确保 token 正确保存

### 2. 手动设置 Token
如果知道正确的 token：
```javascript
localStorage.setItem('auth_token', 'your_token_here');
// 然后刷新页面
```

### 3. 检查登录逻辑
确保登录时正确保存 token：
```javascript
// 登录成功后
localStorage.setItem('auth_token', response.token);
// 或者
localStorage.setItem('token', response.access_token);
```

## 当前的调试增强

Settings.vue 现在包含详细的认证调试信息：

```
🔐 Auth Debug
localStorage auth_token: ✅/❌
localStorage token: ✅/❌  
sessionStorage token: ✅/❌
AuthStore user: ✅/❌
```

这将帮助识别具体的问题所在。

## 常见修复方法

### 1. Token 键名不匹配
如果 token 存储在不同的键下，更新 authToken computed：
```javascript
const authToken = computed(() => {
  if (process.client) {
    return localStorage.getItem('正确的键名');
  }
  return null;
});
```

### 2. 自动刷新 Token
如果 token 过期，实现自动刷新：
```javascript
// 在 API 拦截器中
if (error.response?.status === 401) {
  // 尝试刷新 token 或重定向到登录
}
```

### 3. 同步 authStore 和 localStorage
确保两者保持同步：
```javascript
// 在 authStore 中
const setToken = (token) => {
  localStorage.setItem('auth_token', token);
  // 更新 store 状态
};
```

## 预防措施

1. **统一 Token 管理**: 使用统一的 token 管理方案
2. **Token 验证**: 定期验证 token 有效性
3. **错误处理**: 优雅处理 token 过期情况
4. **调试工具**: 保留调试信息帮助排查问题
