# 快速开始 - 使用现有数据库实现动态 Sections

## 📋 文件已创建

1. ✅ **配置文件**: `backend/config/profile_sections.php`
   - 定义所有 Sections 的元数据

2. ✅ **示例 Seeders**:
   - `backend/database/seeders/PortfolioSectionSeeder.php`
   - `backend/database/seeders/BlogSectionSeeder.php`

3. ✅ **实施文档**: `PROFILE_BUILDER_USING_EXISTING_DB.md`
   - 完整的实施方案

---

## 🚀 快速实施步骤

### Step 1: 运行 Seeders 添加新 Sections

```bash
cd backend

# 运行 Portfolio Section Seeder
php artisan db:seed --class=PortfolioSectionSeeder

# 运行 Blog Section Seeder
php artisan db:seed --class=BlogSectionSeeder
```

### Step 2: 更新 ProfileBuilderFieldController

在 `backend/app/Http/Controllers/Api/Admin/ProfileBuilderFieldController.php` 中添加新方法：

```php
/**
 * Get all sections with their fields
 */
public function getSections(Request $request)
{
    $plan = $request->query('plan');
    
    $query = ProfileBuilderField::orderBy('tab')->orderBy('display_order');
    
    if ($plan) {
        $query->where(function($q) use ($plan) {
            $q->whereJsonContains('available_plans', $plan)
              ->orWhereNull('available_plans')
              ->orWhereJsonLength('available_plans', 0);
        });
    }
    
    $fields = $query->get();
    
    // Group by tab (section)
    $sections = $fields->groupBy('tab')->map(function ($sectionFields, $tabKey) {
        $sectionConfig = config("profile_sections.sections.{$tabKey}", [
            'name' => ucfirst($tabKey),
            'icon' => 'heroicons:folder',
            'category' => 'general',
        ]);
        
        return [
            'section_key' => $tabKey,
            'section_name' => $sectionConfig['name'],
            'icon' => $sectionConfig['icon'],
            'category' => $sectionConfig['category'],
            'description' => $sectionConfig['description'] ?? '',
            'display_order' => $sectionConfig['display_order'] ?? 0,
            'fields' => $sectionFields->values(),
        ];
    })->sortBy('display_order')->values();
    
    return response()->json([
        'success' => true,
        'data' => $sections,
    ]);
}
```

### Step 3: 添加路由

在 `backend/routes/api.php` 中添加：

```php
Route::middleware('auth:sanctum')->group(function () {
    // User Routes - Get sections with fields
    Route::get('/profile-builder-sections', [ProfileBuilderFieldController::class, 'getSections']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Admin Routes
    Route::get('/profile-builder-sections', [ProfileBuilderFieldController::class, 'getSections']);
});
```

### Step 4: 测试 API

```bash
# 测试获取所有 Sections
curl http://localhost:8000/api/profile-builder-sections \
  -H "Authorization: Bearer YOUR_TOKEN"

# 测试按 Plan 过滤
curl http://localhost:8000/api/profile-builder-sections?plan=business \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 🎨 前端使用示例

### User Profile Builder

```vue
<template>
  <div class="profile-builder">
    <!-- Dynamic Section Tabs -->
    <div class="tabs">
      <button
        v-for="section in sections"
        :key="section.section_key"
        @click="currentSection = section.section_key"
        :class="{ active: currentSection === section.section_key }"
      >
        <Icon :name="section.icon" />
        {{ section.section_name }}
      </button>
    </div>
    
    <!-- Dynamic Section Content -->
    <div class="section-content">
      <div v-for="section in sections" :key="section.section_key" v-show="currentSection === section.section_key">
        <h2>{{ section.section_name }}</h2>
        <p class="text-gray-600">{{ section.description }}</p>
        
        <!-- Render Fields Dynamically -->
        <div v-for="field in section.fields" :key="field.id" class="field-wrapper">
          <DynamicFormField
            :field="field"
            v-model="profileData[field.field_key]"
            :nfc-card-id="selectedNfcCardId"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const sections = ref([]);
const currentSection = ref('profile');
const profileData = ref({});

const loadSections = async () => {
  const response = await $fetch('/api/profile-builder-sections', {
    params: {
      plan: authStore.user?.subscription_plan
    }
  });
  sections.value = response.data;
};

onMounted(() => {
  loadSections();
});
</script>
```

---

## 📝 添加新 Section 的方法

### 方法 1: 通过 Seeder（推荐）

创建新的 Seeder:

```php
<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProfileBuilderField;

class TestimonialsSectionSeeder extends Seeder
{
    public function run(): void
    {
        ProfileBuilderField::create([
            'tab' => 'testimonials',  // 新 Section 标识符
            'field_key' => 'testimonials_list',
            'field_type' => 'repeater',
            'label' => 'Client Testimonials',
            'is_visible' => true,
            'display_order' => 1,
            'available_plans' => json_encode(['premium', 'business']),
            'config' => json_encode([
                'max_items' => 10,
                'sub_fields' => [
                    ['key' => 'clientName', 'label' => 'Client Name', 'type' => 'text'],
                    ['key' => 'rating', 'label' => 'Rating', 'type' => 'number'],
                    ['key' => 'comment', 'label' => 'Comment', 'type' => 'textarea'],
                ],
            ]),
        ]);
    }
}
```

然后在 `config/profile_sections.php` 中添加 Section 配置：

```php
'testimonials' => [
    'name' => 'Testimonials',
    'icon' => 'heroicons:chat-bubble-left-right',
    'category' => 'general',
    'description' => 'Client reviews and testimonials',
    'display_order' => 7,
    'available_plans' => ['premium', 'business'],
],
```

运行 Seeder:
```bash
php artisan db:seed --class=TestimonialsSectionSeeder
```

### 方法 2: 通过 API（动态创建）

Admin 可以通过 API 动态创建新 Section 的第一个字段：

```javascript
await $fetch('/api/admin/profile-builder-fields', {
  method: 'POST',
  body: {
    tab: 'testimonials',  // 新 Section
    field_key: 'testimonials_list',
    field_type: 'repeater',
    label: 'Client Testimonials',
    is_visible: true,
    display_order: 1,
    available_plans: ['premium', 'business'],
    config: {
      max_items: 10,
      sub_fields: [
        { key: 'clientName', label: 'Client Name', type: 'text' },
        { key: 'rating', label: 'Rating', type: 'number' },
        { key: 'comment', label: 'Comment', type: 'textarea' },
      ],
    },
  }
});
```

然后手动更新 `config/profile_sections.php` 添加 Section 元数据。

---

## 🎯 当前系统状态

### 已有的 Sections (4个)
- ✅ **profile** - Profile Tab
- ✅ **company** - Company & Team Tab
- ✅ **services** - Services Tab
- ✅ **links** - Social Media & Links Tab

### 新增的 Sections (2个示例)
- 🆕 **portfolio** - Portfolio Tab (可运行 PortfolioSectionSeeder 添加)
- 🆕 **blog** - Blog Tab (可运行 BlogSectionSeeder 添加)

### 可扩展的 Sections
你可以随时添加更多 Sections，例如：
- **testimonials** - 客户评价
- **achievements** - 成就奖项
- **certifications** - 证书资质
- **gallery** - 图片画廊
- **videos** - 视频展示
- **faq** - 常见问题
- **contact** - 联系方式

---

## ✅ 验证步骤

### 1. 检查配置文件
```bash
cat backend/config/profile_sections.php
```

### 2. 检查数据库
```sql
-- 查看所有 tabs (sections)
SELECT DISTINCT tab FROM profile_builder_fields;

-- 查看特定 Section 的字段
SELECT field_key, field_type, label FROM profile_builder_fields WHERE tab = 'portfolio';
```

### 3. 测试 API
```bash
curl http://localhost:8000/api/profile-builder-sections
```

---

## 🎉 优势总结

1. **无需新表** - 使用现有的 `profile_builder_fields` 表
2. **简单扩展** - 只需添加新的 `tab` 值即可创建新 Section
3. **配置驱动** - Section 元数据在配置文件中管理
4. **向后兼容** - 现有的 4 个 tabs 继续正常工作
5. **灵活强大** - 支持 18+ 种字段类型
6. **易于管理** - Admin 可以通过 Seeder 或 API 添加新内容

---

准备好了吗？开始实施！🚀
