# 统一的 Profile Builder 管理系统

## ✅ 完成状态

已成功将设计选项管理和输入字段管理合并到单一页面：`profile-builder-design.vue`

## 🎯 功能概览

### 1. 设计选项管理（Design Options）
- ✅ **Themes** - 主题样式
- ✅ **Fonts** - 字体选择
- ✅ **Button Styles** - 按钮样式
- ✅ **Profile Styles** - 个人资料样式
- ✅ **Color Schemes** - 配色方案
- ✅ **Layouts** - 布局设置
- ✅ **Tab Controls** - 标签页控制
- ✅ **Features** - 功能开关

### 2. 输入字段管理（Input Fields）
- ⚠️ **Profile Fields** - 个人资料字段（需要后端 API）
- ⚠️ **Company Fields** - 公司信息字段（需要后端 API）
- ⚠️ **Service Fields** - 服务字段（需要后端 API）
- ⚠️ **Link Fields** - 链接字段（需要后端 API）

## 🎨 UI 改进

### 计划过滤器
- 顶部添加计划过滤按钮：All Plans / Basic / Premium / Business
- 根据选择的计划动态过滤显示相应的选项
- 视觉标识：Basic(蓝色) / Premium(紫色) / Business(琥珀色)

### 字段表格视图
- 紧凑的表格布局
- 内联编辑：Order、Required、Visible
- 计划可用性可视化（彩色徽章）
- 快速操作按钮：Edit / Delete

### 设计选项网格视图
- 精美的卡片设计
- 实时预览区域（主题、字体、颜色等）
- 计划可用性指示器（彩色圆点）
- 状态徽章：Default、Active/Inactive

### 模态框设计
- 现代化的圆角设计
- 独立的 Header / Body / Footer 区域
- 响应式表单布局
- 改进的计划选择器（大卡片样式）
- 配置区域使用灰色背景区分

## 📊 数据结构

### 设计选项（Design Options）
```javascript
{
  id: number,
  type: string,
  option_id: string,
  name: string,
  description: string,
  config: object,
  is_active: boolean,
  is_default: boolean,
  available_plans: array,
  display_order: number
}
```

### 输入字段（Input Fields）
```javascript
{
  id: number,
  tab: string,
  field_key: string,
  field_type: string,
  label: string,
  placeholder: string,
  help_text: string,
  is_required: boolean,
  is_visible: boolean,
  validation_rules: object,
  available_plans: array,
  display_order: number,
  config: object
}
```

## 🔧 功能特性

### 通用功能
1. **计划过滤** - 根据订阅计划过滤选项
2. **添加/编辑/删除** - 完整的 CRUD 操作
3. **排序控制** - Display Order 字段
4. **计划权限** - 选择哪些计划可以使用
5. **实时更新** - 修改后立即反映

### 设计选项专属
- **Active/Inactive 切换** - 快速启用/禁用
- **设置为默认** - 标记默认选项
- **实时预览** - 查看设计效果

### 输入字段专属
- **Required/Visible 切换** - 内联快速编辑
- **字段类型选择** - text, email, tel, url, textarea, number, image, repeater
- **验证规则** - Min/Max 长度限制

## 🚀 技术实现

### 响应式状态
```javascript
const currentTab = ref('theme')
const selectedPlanFilter = ref(null)
const options = ref({})  // 设计选项
const fields = ref({})   // 输入字段
```

### 关键 Computed
- `isFieldTab` - 判断当前是否为字段 tab
- `currentOptions` - 获取当前 tab 的数据
- `getFilteredOptions` - 根据计划过滤选项
- `isAvailableForPlan` - 判断选项对计划可用性

### API 端点

#### 设计选项 API（已实现）
```
GET    /api/admin/profile-design-options
POST   /api/admin/profile-design-options
PUT    /api/admin/profile-design-options/{id}
DELETE /api/admin/profile-design-options/{id}
POST   /api/admin/profile-design-options/{id}/toggle-active
POST   /api/admin/profile-design-options/{id}/set-default
```

#### 输入字段 API（需要实现）
```
GET    /api/admin/profile-builder-fields
POST   /api/admin/profile-builder-fields
PUT    /api/admin/profile-builder-fields/{id}
DELETE /api/admin/profile-builder-fields/{id}
```

## ⚠️ 当前限制

### 字段管理需要后端支持
字段管理功能的前端已完成，但需要后端实现以下内容：

1. **数据库表**
```sql
CREATE TABLE profile_builder_fields (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tab VARCHAR(50) NOT NULL,
    field_key VARCHAR(100) UNIQUE NOT NULL,
    field_type VARCHAR(50) NOT NULL,
    label VARCHAR(255) NOT NULL,
    placeholder TEXT,
    help_text TEXT,
    is_required BOOLEAN DEFAULT FALSE,
    is_visible BOOLEAN DEFAULT TRUE,
    validation_rules JSON,
    available_plans JSON,
    display_order INT DEFAULT 0,
    config JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

2. **Laravel Controller** (`ProfileBuilderFieldController.php`)
3. **Laravel Model** (`ProfileBuilderField.php`)
4. **API 路由注册**

## 📝 使用说明

### 访问页面
```
/AdminManagement/profile-builder-design
```

### 工作流程
1. **选择 Tab** - 点击顶部导航选择要管理的类型
2. **过滤计划**（可选）- 选择特定计划查看该计划的选项
3. **查看列表** - 字段显示表格，设计选项显示网格
4. **添加新项** - 点击"Add"按钮打开模态框
5. **编辑/删除** - 使用操作按钮管理现有项
6. **内联编辑**（字段）- 直接在表格中修改 Order/Required/Visible

### 计划可用性设置
- **不勾选任何计划** = 对所有计划可用
- **勾选特定计划** = 仅对勾选的计划可用
- 视觉指示器会实时显示可用性

## 🎯 下一步

### 立即可用
- ✅ 设计选项管理完全可用
- ✅ UI/UX 已优化完成
- ✅ 计划过滤功能完整

### 待实现（后端）
- ⏳ Profile Builder Fields API
- ⏳ 数据库迁移文件
- ⏳ Controller 和 Model
- ⏳ API 路由

## 💡 设计亮点

1. **统一管理** - 单一页面管理所有配置
2. **直观过滤** - 彩色计划过滤器
3. **实时预览** - 设计选项即时可见
4. **快速编辑** - 内联编辑减少点击
5. **视觉反馈** - 清晰的状态指示器
6. **响应式设计** - 移动端友好
7. **现代 UI** - Tailwind CSS 精美样式
8. **性能优化** - Computed 属性高效过滤

## 🔍 测试建议

### 设计选项（当前可测试）
1. 添加新的 Theme
2. 编辑现有 Font
3. 设置 Color Scheme 为默认
4. Toggle Button Style Active/Inactive
5. 使用计划过滤器过滤
6. 删除 Layout 选项

### 字段管理（需后端后测试）
1. 添加新的 Profile Field
2. 修改字段的 Required/Visible 状态
3. 调整 Display Order
4. 设置字段的计划可用性
5. 配置验证规则

## 📦 文件清单

- ✅ `frontend/pages/AdminManagement/profile-builder-design.vue` - 统一管理页面
- ❌ `frontend/pages/AdminManagement/profile-builder-fields.vue` - 已删除（功能合并）
- ✅ `UNIFIED_PROFILE_BUILDER_MANAGEMENT.md` - 本文档

## ✨ 总结

成功创建了一个功能强大、界面精美的统一管理系统。设计选项管理完全可用，字段管理的前端已准备就绪，等待后端 API 实现即可启用完整功能。
