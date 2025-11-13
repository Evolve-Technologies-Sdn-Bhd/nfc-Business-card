# Plan-Based Login Redirect System

## 功能说明 (Feature Description)

系统现在会根据用户的订阅计划 (subscription plan) 在登录后自动重定向到不同的页面。

The system now automatically redirects users to different pages based on their subscription plan after login.

## 重定向规则 (Redirect Rules)

| Plan         | Redirect Path                                                           | 说明 (Description)                               |
| ------------ | ----------------------------------------------------------------------- | ------------------------------------------------ |
| **Admin**    | `/AdminManagement`                                                      | 管理员用户直接进入管理后台                       |
| **Business** | `/UserDashboard/UserManagement/BusinessPlanUser/BusinessCardManagement` | Business plan 用户查看 Business 专属卡片管理页面 |
| **Premium**  | `/UserDashboard/CardManagement`                                         | Premium 用户使用标准卡片管理                     |
| **Basic**    | `/UserDashboard/CardManagement`                                         | Basic 用户使用标准卡片管理                       |
| **Free**     | `/UserDashboard`                                                        | Free 用户查看基础仪表板                          |

## 实现细节 (Implementation Details)

### 1. Auth Store 新增方法

在 `frontend/stores/auth.js` 中添加了 `getRedirectPathByPlan()` 方法：

```javascript
getRedirectPathByPlan() {
  if (!this.user) {
    return "/UserDashboard/CardManagement";
  }

  // Admin users go to admin panel
  if (this.isAdmin()) {
    return "/AdminManagement";
  }

  // Get user's plan
  const userPlan = this.user.subscription_plan || this.user.plan || "free";

  // Route based on plan
  switch (userPlan.toLowerCase()) {
    case "business":
      return "/UserDashboard/UserManagement/BusinessPlanUser/BusinessCardManagement";
    case "premium":
      return "/UserDashboard/CardManagement";
    case "basic":
      return "/UserDashboard/CardManagement";
    case "free":
      return "/UserDashboard";
    default:
      return "/UserDashboard/CardManagement";
  }
}
```

### 2. Login Page 更新

在 `frontend/pages/UserAccount/login.vue` 中的三个地方使用了这个方法：

#### a) 常规登录

```javascript
const handleLogin = async () => {
  // ...
  let redirectPath = route.query.redirect;

  if (!redirectPath) {
    redirectPath = authStore.getRedirectPathByPlan();
  }

  await router.push(redirectPath);
};
```

#### b) 2FA 验证后

```javascript
const handle2FA = async () => {
  // ...
  let redirectPath = route.query.redirect;

  if (!redirectPath) {
    redirectPath = authStore.getRedirectPathByPlan();
  }

  await router.push(redirectPath);
};
```

#### c) 已登录用户访问登录页

```javascript
onMounted(() => {
  if (authStore.isAuthenticated) {
    const redirect = route.query.redirect || authStore.getRedirectPathByPlan();
    router.push(redirect);
  }
});

watch(
  () => authStore.isAuthenticated,
  (isAuth) => {
    if (isAuth) {
      const redirect =
        route.query.redirect || authStore.getRedirectPathByPlan();
      router.push(redirect);
    }
  }
);
```

## 如何测试 (How to Test)

### 1. 测试 Business Plan 用户

在数据库中设置用户的 subscription_plan：

```sql
UPDATE users
SET subscription_plan = 'business'
WHERE email = 'test@example.com';
```

然后登录该账号，系统会自动重定向到：
`/UserDashboard/UserManagement/BusinessPlanUser/BusinessCardManagement`

### 2. 测试其他 Plan

```sql
-- Free plan
UPDATE users SET subscription_plan = 'free' WHERE email = 'test@example.com';
-- 重定向到: /UserDashboard

-- Basic plan
UPDATE users SET subscription_plan = 'basic' WHERE email = 'test@example.com';
-- 重定向到: /UserDashboard/CardManagement

-- Premium plan
UPDATE users SET subscription_plan = 'premium' WHERE email = 'test@example.com';
-- 重定向到: /UserDashboard/CardManagement

-- Business plan
UPDATE users SET subscription_plan = 'business' WHERE email = 'test@example.com';
-- 重定向到: /UserDashboard/UserManagement/BusinessPlanUser/BusinessCardManagement
```

### 3. 测试 Admin 用户

```sql
UPDATE users
SET is_admin = true
WHERE email = 'admin@example.com';
```

Admin 用户登录后会重定向到 `/AdminManagement`，无论其 subscription_plan 是什么。

## Query Parameter 覆盖 (Query Parameter Override)

你仍然可以使用 `redirect` 查询参数强制重定向到特定页面：

```
/UserAccount/login?redirect=/UserDashboard/Settings
```

这会覆盖基于 plan 的自动重定向。

## Business Plan 页面结构

Business plan 用户可以访问以下专属页面：

```
/UserDashboard/UserManagement/BusinessPlanUser/
  ├── BusinessCardManagement.vue      (默认登录页面)
  ├── BusinessLinkManagement.vue
  └── BusinessProfileBuilder.vue
```

## 注意事项 (Notes)

1. **检查字段优先级**：系统会优先检查 `subscription_plan` 字段，如果为空则使用 `plan` 字段
2. **大小写不敏感**：plan 名称会转换为小写进行比较
3. **默认行为**：如果 plan 无法识别，默认重定向到 `/UserDashboard/CardManagement`
4. **OAuth 登录**：Google 和 Apple 登录后也会使用相同的重定向逻辑

## 扩展 (Extension)

如果将来需要为 Free、Basic 或 Premium plan 添加专属页面，只需：

1. 在对应文件夹下创建页面文件
2. 更新 `getRedirectPathByPlan()` 方法中的路径

例如：

```javascript
case "premium":
  return "/UserDashboard/UserManagement/PremiumPlanUser/PremiumDashboard";
```
