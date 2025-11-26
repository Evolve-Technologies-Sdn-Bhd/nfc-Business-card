# Layout 和 Features 功能说明

**Date**: November 25, 2025

---

## 🎨 Layout (布局设置)

### 用途
**Layout** 控制用户公开 Profile 页面的**页面布局和对齐方式**。

### 具体功能

#### 1. **Alignment（对齐方式）**
控制 Profile 内容在页面上的对齐位置：
- `left` - 内容靠左对齐
- `center` - 内容居中对齐（最常用）
- `right` - 内容靠右对齐

#### 2. **Max Width（最大宽度）**
控制 Profile 内容区域的最大宽度：
- 例如：`800px`, `1000px`, `100%`
- 防止内容在大屏幕上过度拉伸
- 提升可读性

### 配置示例
```javascript
{
  id: 'centered',
  name: 'Centered Layout',
  config: {
    alignment: 'center',
    maxWidth: '800px'
  }
}

{
  id: 'wide',
  name: 'Wide Layout',
  config: {
    alignment: 'center',
    maxWidth: '1200px'
  }
}

{
  id: 'full',
  name: 'Full Width',
  config: {
    alignment: 'left',
    maxWidth: '100%'
  }
}
```

### 视觉效果

#### Center Layout (800px)
```
┌────────────────────────────────────┐
│                                    │
│     ┌──────────────────┐           │
│     │   Profile Card   │           │
│     │                  │           │
│     │   Content Area   │           │
│     │   (800px max)    │           │
│     │                  │           │
│     └──────────────────┘           │
│                                    │
└────────────────────────────────────┘
```

#### Left Layout (1000px)
```
┌────────────────────────────────────┐
│                                    │
│ ┌────────────────────┐             │
│ │   Profile Card     │             │
│ │                    │             │
│ │   Content Area     │             │
│ │   (1000px max)     │             │
│ │                    │             │
│ └────────────────────┘             │
│                                    │
└────────────────────────────────────┘
```

### 应用场景
- **Centered (800px)** - 个人简介，专业名片
- **Centered (1000px)** - 公司介绍，作品集
- **Full Width** - 画廊展示，图片密集型

---

## ✨ Features (功能开关)

### 用途
**Features** 是一个**功能开关系统**，用于控制 Profile 页面上某些特定功能的显示与隐藏。

### 具体功能

#### 1. **Feature Key（功能标识）**
每个功能都有唯一的 key：
- `stats` - 统计数据展示
- `social_share` - 社交分享按钮
- `download_vcard` - 下载名片功能
- `analytics` - 访问统计
- `watermark` - 水印显示
- `theme_switcher` - 主题切换器

#### 2. **Enabled（默认状态）**
- `true` - 功能默认开启
- `false` - 功能默认关闭

### 配置示例
```javascript
// 统计功能
{
  id: 'feature_stats',
  name: 'Profile Statistics',
  config: {
    feature_key: 'stats',
    enabled: true
  }
}

// 社交分享
{
  id: 'feature_social',
  name: 'Social Share Buttons',
  config: {
    feature_key: 'social_share',
    enabled: true
  }
}

// 水印（可能只有基础计划需要）
{
  id: 'feature_watermark',
  name: 'Watermark Display',
  config: {
    feature_key: 'watermark',
    enabled: false // Premium/Business 用户不显示
  }
}
```

### 前端使用示例
```vue
<template>
  <!-- 只有当 stats 功能启用时才显示 -->
  <div v-if="isFeatureEnabled('stats')" class="stats-section">
    <h3>Profile Views: {{ viewCount }}</h3>
  </div>

  <!-- 只有当 social_share 功能启用时才显示 -->
  <div v-if="isFeatureEnabled('social_share')" class="share-buttons">
    <button>Share on Twitter</button>
    <button>Share on LinkedIn</button>
  </div>

  <!-- 水印显示（基础计划）-->
  <div v-if="isFeatureEnabled('watermark')" class="watermark">
    Powered by NFC Business Card
  </div>
</template>

<script setup>
const featureToggles = ref([
  { feature_key: 'stats', enabled: true },
  { feature_key: 'social_share', enabled: true },
  { feature_key: 'watermark', enabled: false }
]);

const isFeatureEnabled = (key) => {
  const feature = featureToggles.value.find(f => f.feature_key === key);
  return feature?.enabled || false;
};
</script>
```

---

## 📊 Plan-based 使用场景

### Layout 按计划分配

| Plan | Available Layouts |
|------|-------------------|
| **Basic** | Centered (800px) |
| **Premium** | Centered (800px), Centered (1000px) |
| **Business** | All layouts (800px, 1000px, Full Width, Left, Right) |

### Features 按计划分配

| Feature | Basic | Premium | Business |
|---------|-------|---------|----------|
| **Stats** | ❌ | ✅ | ✅ |
| **Social Share** | ✅ | ✅ | ✅ |
| **Download vCard** | ❌ | ✅ | ✅ |
| **Analytics** | ❌ | ❌ | ✅ |
| **Watermark** | ✅ (强制) | ❌ | ❌ |
| **Theme Switcher** | ❌ | ✅ | ✅ |

---

## 🎯 Admin 管理界面

### Layout 管理
```
┌──────────────────────────────────────────┐
│ Layouts                                  │
├──────────────────────────────────────────┤
│ ○ Centered (800px)                       │
│   • Alignment: center                    │
│   • Max Width: 800px                     │
│   Plans: [Business] [Premium] [Basic]   │
│                                          │
│ ○ Centered (1000px)                      │
│   • Alignment: center                    │
│   • Max Width: 1000px                    │
│   Plans: [Business] [Premium]           │
│                                          │
│ ○ Full Width                             │
│   • Alignment: left                      │
│   • Max Width: 100%                      │
│   Plans: [Business]                      │
└──────────────────────────────────────────┘
```

### Features 管理
```
┌──────────────────────────────────────────┐
│ Features                                 │
├──────────────────────────────────────────┤
│ ○ Profile Statistics                     │
│   • Feature Key: stats                   │
│   • Enabled: ✓                           │
│   Plans: [Business] [Premium]           │
│                                          │
│ ○ Social Share Buttons                   │
│   • Feature Key: social_share            │
│   • Enabled: ✓                           │
│   Plans: [Business] [Premium] [Basic]   │
│                                          │
│ ○ Watermark Display                      │
│   • Feature Key: watermark               │
│   • Enabled: ✓                           │
│   Plans: [Basic] (强制显示)              │
└──────────────────────────────────────────┘
```

---

## 🔧 技术实现

### Backend (Laravel)
```php
// profile_design_options table
[
    'type' => 'layout',
    'option_id' => 'centered_800',
    'name' => 'Centered (800px)',
    'config' => [
        'alignment' => 'center',
        'maxWidth' => '800px'
    ],
    'available_plans' => ['basic', 'premium', 'business']
]

[
    'type' => 'feature_toggle',
    'option_id' => 'feature_stats',
    'name' => 'Profile Statistics',
    'config' => [
        'feature_key' => 'stats',
        'enabled' => true
    ],
    'available_plans' => ['premium', 'business']
]
```

### Frontend (Vue.js)
```javascript
// 加载 Layout 选项
const layouts = ref([
  {
    id: 'centered_800',
    name: 'Centered (800px)',
    config: { alignment: 'center', maxWidth: '800px' }
  },
  // ...
]);

// 加载 Feature Toggles
const featureToggles = ref([
  {
    id: 'feature_stats',
    name: 'Profile Statistics',
    feature_key: 'stats',
    enabled: true
  },
  // ...
]);

// 应用 Layout
const applyLayout = (layout) => {
  const container = document.querySelector('.profile-container');
  container.style.textAlign = layout.config.alignment;
  container.style.maxWidth = layout.config.maxWidth;
  container.style.margin = layout.config.alignment === 'center' ? '0 auto' : '0';
};

// 检查 Feature
const isFeatureEnabled = (key) => {
  return featureToggles.value.find(f => f.feature_key === key)?.enabled || false;
};
```

---

## 💡 实际应用示例

### 示例 1: 个人作品集（Premium 用户）
```javascript
{
  layout: 'centered_1000',  // 较宽的布局展示作品
  features: {
    stats: true,              // 显示访问统计
    social_share: true,       // 社交分享按钮
    download_vcard: true,     // 下载名片
    watermark: false          // 无水印
  }
}
```

显示效果：
- 1000px 宽度，内容居中
- 页面顶部显示访问次数
- 底部有社交分享按钮
- 可下载 vCard
- 无水印

### 示例 2: 免费用户个人简介（Basic）
```javascript
{
  layout: 'centered_800',   // 标准布局
  features: {
    stats: false,             // 无统计功能
    social_share: true,       // 有分享按钮
    download_vcard: false,    // 不能下载
    watermark: true           // 强制显示水印
  }
}
```

显示效果：
- 800px 宽度，内容居中
- 无访问统计
- 有基本的分享功能
- 底部显示 "Powered by..."

---

## ✅ 总结

### Layout（布局）
- **目的**: 控制页面内容的对齐和宽度
- **配置**: `alignment` + `maxWidth`
- **影响**: 整体视觉布局
- **用户感知**: 直观，明显

### Features（功能开关）
- **目的**: 控制特定功能的开启/关闭
- **配置**: `feature_key` + `enabled`
- **影响**: 功能可用性
- **用户感知**: 功能层面

### 两者关系
- **Layout** 是 **视觉层面** 的配置
- **Features** 是 **功能层面** 的配置
- 两者独立但互补，共同构成完整的用户体验

---

## 📚 相关文档

1. **PROFILE_STYLE_REMOVAL.md** - Profile Style 移除说明
2. **ADMIN_FUNCTIONS_SUMMARY.md** - Admin 管理功能
3. **COLOR_SCHEMES_LAYOUTS.md** - Color Schemes 和 Layouts 详解

---

Last Updated: November 25, 2025
