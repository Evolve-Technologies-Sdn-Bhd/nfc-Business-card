# 系统状态检查

## 🔍 快速诊断

### 检查 1: 前端是否运行

打开新的命令提示符（CMD）：
```cmd
cd frontend
npm run dev
```

**预期结果**：应该看到类似：
```
VITE v5.x.x  ready in xxx ms
➜  Local:   http://localhost:3000/
➜  Network: use --host to expose
```

**如果失败**：
1. 检查是否有 Vue 编译错误
2. 查看错误信息并复制给我

### 检查 2: 后端是否运行

打开新的命令提示符（CMD）：
```cmd
cd backend
php artisan serve
```

**预期结果**：应该看到：
```
Starting Laravel development server: http://127.0.0.1:8000
```

**如果失败**：
1. 检查 PHP 是否安装
2. 检查端口 8000 是否被占用

### 检查 3: 浏览器控制台

1. 打开浏览器（Chrome/Edge）
2. 按 F12 打开开发工具
3. 访问：`http://localhost:3000/AdminManagement`
4. 查看 Console 标签

**正常**：没有红色错误
**有问题**：截图错误信息

### 检查 4: Vue 编译错误

查看运行 `npm run dev` 的终端窗口。

**正常**：没有错误，显示 "ready in xxx ms"
**有问题**：会显示具体的语法错误

## 🐛 常见错误及解决

### 错误 1: "Cannot read property 'join' of null"

**位置**: profile-builder-design.vue

**已修复** ✅ 但需要重启前端：
```cmd
Ctrl + C (停止)
npm run dev (重新启动)
```

### 错误 2: "Column 'available_plans' doesn't exist"

**位置**: 后端 API

**已修复** ✅ 但需要重启后端：
```cmd
Ctrl + C (停止)
php artisan serve (重新启动)
```

### 错误 3: "Element is missing end tag"

**位置**: notifications.vue 或 profile-builder-design.vue

**已修复** ✅ 重启前端即可

### 错误 4: 白屏或页面不显示

**可能原因**：
1. 前端编译错误
2. API 返回错误
3. 浏览器缓存

**解决方法**：
1. 重启前端和后端
2. 清除浏览器缓存（Ctrl + Shift + Delete）
3. 硬刷新（Ctrl + Shift + R）

## 📋 完整重启步骤

如果系统完全无法访问，按照以下步骤完全重启：

### 步骤 1: 停止所有服务

在所有相关的命令提示符窗口按 `Ctrl + C`

### 步骤 2: 清除缓存

**前端**：
```cmd
cd frontend
rm -rf .nuxt
rm -rf node_modules/.cache
```

或在 Windows：
```cmd
cd frontend
rmdir /s .nuxt
```

**后端**：
```cmd
cd backend
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 步骤 3: 重启后端

打开新的 CMD 窗口：
```cmd
cd C:\Users\User\Intern\NFCs\nfc-Business-card\backend
php artisan serve
```

等待显示：`Starting Laravel development server: http://127.0.0.1:8000`

### 步骤 4: 重启前端

打开另一个新的 CMD 窗口：
```cmd
cd C:\Users\User\Intern\NFCs\nfc-Business-card\frontend
npm run dev
```

等待显示：`ready in xxx ms`

### 步骤 5: 测试访问

打开浏览器，按顺序访问：

1. `http://localhost:3000/` （首页）
2. `http://localhost:3000/login` （登录页）
3. `http://localhost:3000/AdminManagement` （Admin Dashboard）

如果都能访问，系统已恢复！

## 🔧 详细诊断

### 检查前端编译

如果前端无法启动，查看错误类型：

**语法错误**：
```
[plugin:vite:vue] Element is missing end tag
```
→ 某个 Vue 文件有未闭合的标签

**导入错误**：
```
Failed to resolve import
```
→ 缺少依赖或路径错误

**运行时错误**：
```
Cannot read property 'x' of undefined
```
→ 代码尝试访问不存在的属性

### 检查后端 API

测试 API 是否正常：

打开浏览器访问：
```
http://127.0.0.1:8000/api/profile-design-options
```

**正常响应**：
```json
{
  "success": true,
  "data": {
    "theme": [...],
    "font": [...]
  }
}
```

**错误响应**：
```json
{
  "message": "SQLSTATE[42S22]: Column not found: 1054 Unknown column 'available_plans'"
}
```
→ 需要运行迁移

## 📊 系统状态指示

### ✅ 正常状态
- 前端: `ready in xxx ms`
- 后端: `Starting Laravel development server: http://127.0.0.1:8000`
- 浏览器: 页面正常显示，无控制台错误
- API: 返回正确的 JSON 数据

### ⚠️ 部分问题
- 页面可以访问但某些功能不工作
- 浏览器控制台有警告（黄色）
- API 返回数据但格式不完整

### ❌ 严重问题
- 前端无法启动（红色错误）
- 后端返回 500 错误
- 页面白屏
- 浏览器控制台大量红色错误

## 💡 获取帮助

如果以上步骤都无法解决问题，请提供：

1. **前端错误信息**：
   - 复制 `npm run dev` 终端的完整错误
   - 截图浏览器控制台的错误

2. **后端错误信息**：
   - 复制 `php artisan serve` 终端的错误
   - 查看 `backend/storage/logs/laravel.log` 最后几行

3. **浏览器信息**：
   - 访问哪个 URL 时出错
   - F12 Console 显示的错误
   - Network 标签是否有失败的请求（红色）

4. **当前状态**：
   - 哪些页面可以访问
   - 哪些页面不能访问
   - 是否已运行数据库迁移

---

**快速提示**：大多数问题都可以通过完全重启（步骤 1-5）解决！
