# Admin 完全控制 Profile Builder 指南

## 🎯 概述

Admin 现在可以完全控制 Profile Builder 的所有方面：
1. **Tab 可见性** - 控制哪些 tabs 显示（Profile, Company, Services, Links, Design, Style, Watermarks）
2. **输入字段** - 控制每个 tab 内的所有输入字段
3. **设计选项** - 控制主题、字体、按钮样式、配色方案、布局等
4. **计划权限** - 为不同订阅计划设置不同的可用选项

## 📋 Admin 管理界面

访问：`/AdminManagement/profile-builder-design`

### 1. Tab 控制

在 **Tab Controls** 标签页：

#### 添加 Tab 配置
1. 点击 "Add New Tab Control"
2. 填写信息：
   - **Option ID**: `default_tabs`
   - **Display Name**: `Default Tab Configuration`
   - **Enabled Tabs**: `profile, company, services, links, design, style, watermarks`
3. 选择可用计划（或不选表示所有计划可用）
4. 勾选 "Active" 和 "Default"
5. 保存

#### Tab 可见性规则
- 如果没有 Tab Control 配置，显示所有默认 tabs
- 如果有配置，只显示配置中列出的 tabs
- 用逗号分隔 tab IDs：`profile, company, services`

### 2. 字段控制

在 **Profile Fields**, **Company Fields**, **Services Fields**, **Links Fields** 标签页：

#### 添加字段
1. 点击 "Add New Field"
2. 填写字段信息：
   - **Field Key**: `name` (唯一标识符)
   - **Label**: `Full Name` (显示标签)
   - **Field Type**: 选择类型（text, email, tel, url, textarea, number, image, repeater）
   - **Placeholder**: `Enter your name` (可选)
   - **Help Text**: `This will be displayed on your profile` (可选)
3. 设置验证规则：
   - **Min Length**: 最小字符数
   - **Max Length**: 最大字符数
4. 设置状态：
   - **Required**: 是否必填
   - **Visible**: 是否可见
5. 选择可用计划
6. 设置显示顺序（Display Order）
7. 保存

#### 字段管理
- **编辑**: 点击字段的 "Edit" 按钮
- **删除**: 点击字段的 "Delete" 按钮
- **快速编辑**: 直接在表格中修改 Order, Required, Visible
- **排序**: 通过 Display Order 控制字段显示顺序

### 3. 设计选项控制

#### Themes（主题）
- 控制可用的主题样式
- 配置背景颜色和 CSS 类

#### Fonts（字体）
- 控制可用的字体选项
- 配置字体家族

#### Button Styles（按钮样式）
- 控制按钮的样式
- 配置 CSS 类

#### Profile Styles（个人资料样式）
- 控制个人资料的显示样式
- Classic, Modern, Minimal 等

#### Color Schemes（配色方案）
- 控制主题配色
- Primary, Secondary, Accent 颜色

#### Layouts（布局）
- 控制页面布局选项
- 对齐方式、最大宽度等

#### Feature Toggles（功能开关）
- 控制特定功能的开关
- 如统计显示、水印等

## 🔧 工作原理

### Frontend 流程

1. **页面加载时**
   ```javascript
   onMounted(async () => {
     await loadTabsConfig();        // 加载 tabs 配置
     await loadFieldsConfig();      // 加载字段配置
     await loadDesignOptions();     // 加载设计选项
   });
   ```

2. **Tab 过滤**
   - 从 `tab_control` 类型的设计选项读取配置
   - 只显示配置中列出的 tabs
   - 根据用户的订阅计划过滤

3. **字段动态渲染**
   - 从 API 获取字段配置
   - 根据字段的 `is_visible` 属性决定是否显示
   - 根据 `is_required` 属性设置验证
   - 根据 `display_order` 排序

4. **设计选项过滤**
   - 根据用户的订阅计划过滤可用选项
   - 只显示标记为 `is_active` 的选项
   - 默认选择标记为 `is_default` 的选项

### Backend API

#### Tabs 配置
```
GET /api/profile-design-options?plan=business
Response: {
  data: {
    tab_control: [{
      option_id: "default_tabs",
      config: {
        tabs: ["profile", "company", "services", "links", "design", "style"]
      }
    }]
  }
}
```

#### 字段配置
```
GET /api/admin/profile-builder-fields
Response: {
  success: true,
  data: {
    profile: [
      {
        field_key: "name",
        label: "Full Name",
        field_type: "text",
        is_required: true,
        is_visible: true,
        display_order: 0
      }
    ],
    company: [...],
    services: [...],
    links: [...]
  }
}
```

#### 设计选项
```
GET /api/profile-design-options?plan=business
Response: {
  data: {
    theme: [...],
    font: [...],
    button_style: [...],
    color_scheme: [...],
    layout: [...]
  }
}
```

## 📊 计划权限系统

### 如何工作

1. **不勾选任何计划** = 对所有计划可用
2. **勾选特定计划** = 仅对勾选的计划可用

### 示例配置

#### Basic Plan
- Tabs: Profile, Company, Links
- Themes: 2 个基础主题
- Fonts: 1 个字体
- Profile Fields: 基本字段（Name, Email, Phone）

#### Premium Plan
- Tabs: Profile, Company, Services, Links, Design
- Themes: 5 个主题
- Fonts: 3 个字体
- Profile Fields: 所有基本字段 + Bio, Qualification

#### Business Plan
- Tabs: 所有 tabs（Profile, Company, Services, Links, Design, Style, Watermarks）
- Themes: 所有主题
- Fonts: 所有字体
- Profile Fields: 所有字段
- Company Fields: 完整的公司信息
- Services Fields: 服务展示
- Links Fields: 社交链接

## 🎨 示例：配置 Basic Plan

### 1. 配置 Tabs
```
Tab Controls -> Add New
- Option ID: basic_tabs
- Enabled Tabs: profile, company, links
- Available Plans: ✓ Basic Plan
- Active: ✓
- Default: ✓
```

### 2. 配置 Profile Fields
```
Profile Fields -> Add New
- Field Key: name
- Label: Full Name
- Type: text
- Required: ✓
- Visible: ✓
- Available Plans: ✓ Basic, ✓ Premium, ✓ Business
```

### 3. 配置 Themes
```
Themes -> Add New
- Option ID: minimal
- Name: Minimal
- Background Color: #FFFFFF
- Available Plans: ✓ Basic, ✓ Premium, ✓ Business
- Active: ✓
- Default: ✓
```

## 🧪 测试步骤

### 1. 测试 Tab 控制
1. 在 Admin 页面添加 Tab Control 配置
2. 设置只显示 `profile, company`
3. 选择 Basic Plan
4. 以 Basic Plan 用户登录
5. 访问 Profile Builder
6. 确认只显示 Profile 和 Company tabs

### 2. 测试字段控制
1. 在 Admin 页面添加 Profile Field
2. 设置 Required = true
3. 选择 Premium Plan
4. 以 Premium Plan 用户登录
5. 尝试不填该字段保存
6. 确认显示验证错误

### 3. 测试设计选项
1. 在 Admin 页面添加 Theme
2. 设置为 Active 和 Default
3. 选择 Business Plan
4. 以 Business Plan 用户登录
5. 访问 Design tab
6. 确认新主题显示并默认选中

## ✨ 高级功能

### 动态字段类型

支持的字段类型：
- **text**: 单行文本输入
- **email**: 邮箱输入（带验证）
- **tel**: 电话号码输入
- **url**: URL 输入（带验证）
- **textarea**: 多行文本输入
- **number**: 数字输入
- **image**: 图片上传
- **repeater**: 可重复字段组

### 验证规则

支持的验证：
- **min**: 最小长度
- **max**: 最大长度
- **pattern**: 正则表达式（未来）
- **custom**: 自定义验证（未来）

### 条件显示

可以基于以下条件显示/隐藏：
- 订阅计划
- 字段值（未来）
- 用户角色（未来）

## 📝 最佳实践

1. **始终提供默认配置**
   - 至少配置一个 Tab Control
   - 为每个计划提供基础字段
   - 设置默认主题和字体

2. **合理分配计划权限**
   - Basic: 基础功能
   - Premium: 扩展功能
   - Business: 完整功能

3. **字段命名规范**
   - 使用小写字母和下划线
   - 描述性命名：`company_name`, `bio_text`
   - 避免特殊字符

4. **测试每个计划**
   - 创建测试用户
   - 切换不同计划
   - 验证所有功能

## 🔍 故障排查

### 问题：Tabs 不显示
- 检查是否有 Tab Control 配置
- 检查配置是否为 Active
- 检查用户的订阅计划是否匹配

### 问题：字段不显示
- 检查字段的 `is_visible` 是否为 true
- 检查字段的 `available_plans` 是否包含用户计划
- 检查浏览器控制台是否有错误

### 问题：设计选项不显示
- 检查选项的 `is_active` 是否为 true
- 检查选项的 `available_plans` 是否包含用户计划
- 清除浏览器缓存

## 🚀 总结

现在 Admin 拥有对 Profile Builder 的完全控制权：
- ✅ 控制哪些 tabs 显示
- ✅ 控制每个 tab 内的字段
- ✅ 控制所有设计选项
- ✅ 为不同计划设置不同权限
- ✅ 动态配置，无需修改代码

所有配置都通过 Admin 界面完成，无需技术知识！
