# Profile Builder 重新设计 - 使用现有数据库

## 🎯 目标

使用现有的 `profile_builder_fields` 表实现动态 Sections 和 Fields 管理，无需创建新表。

---

## 📊 现有数据库结构

### `profile_builder_fields` 表（已存在）
```sql
- id (bigint)
- tab (varchar) - 目前: profile, company, services, links
- field_key (varchar) - 字段唯一标识
- field_type (varchar) - text, email, tel, url, textarea, number, image, repeater
- label (varchar) - 显示标签
- placeholder (text)
- help_text (text)
- is_required (boolean)
- is_visible (boolean)
- validation_rules (json)
- available_plans (json)
- display_order (integer)
- config (json)
- created_at, updated_at
```

---

## ✅ 方案：扩展现有表而不创建新表

### 策略
将 `tab` 字段用作 **动态 Section 标识符**，通过约定和配置来实现 Section 管理。

---

## 🔧 实施方案

### Phase 1: 数据库扩展（Migration）

**文件**: `2025_11_24_add_section_support_to_profile_builder_fields.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profile_builder_fields', function (Blueprint $table) {
            // 扩展 field_type 支持更多类型
            // 注意: 如果数据库是 MySQL，可能需要先修改列类型
            
            // 添加 section 相关配置到 config JSON
            // config 字段现在可以包含:
            // - section_icon: 图标名称
            // - section_category: general/design
            // - section_description: Section 描述
            // - is_section_header: true/false (标记这是一个 section header)
        });
    }

    public function down(): void
    {
        // No changes needed - we're just using existing JSON field
    }
};
```

**关键点**：
- 不添加新列，使用现有的 `config` JSON 字段存储 Section 元数据
- `tab` 字段作为 Section 的唯一标识符

---

### Phase 2: Section 管理逻辑

#### 创建 Section 的方式

**方式 1: 使用特殊的 Field 记录作为 Section Header**

```php
// 创建一个新的 Section "portfolio"
ProfileBuilderField::create([
    'tab' => 'portfolio',  // Section 标识符
    'field_key' => '_section_header_portfolio',  // 特殊标记
    'field_type' => 'section_header',  // 新类型
    'label' => 'Portfolio',  // Section 显示名称
    'is_visible' => true,
    'display_order' => 5,
    'config' => [
        'is_section_header' => true,
        'section_icon' => 'heroicons:briefcase',
        'section_category' => 'general',
        'section_description' => 'Showcase your projects and work',
    ],
    'available_plans' => ['premium', 'business'],
]);

// 为 Portfolio Section 添加字段
ProfileBuilderField::create([
    'tab' => 'portfolio',  // 属于 portfolio section
    'field_key' => 'portfolio_title',
    'field_type' => 'text',
    'label' => 'Portfolio Title',
    'is_required' => true,
    'display_order' => 1,
    'available_plans' => ['premium', 'business'],
]);
```

**方式 2: 使用配置文件定义 Sections**

创建 `config/profile_sections.php`:

```php
<?php

return [
    'sections' => [
        'profile' => [
            'name' => 'Profile',
            'icon' => 'heroicons:user',
            'category' => 'general',
            'description' => 'Personal information',
            'display_order' => 1,
        ],
        'company' => [
            'name' => 'Company & Team',
            'icon' => 'heroicons:building-office',
            'category' => 'general',
            'description' => 'Company information',
            'display_order' => 2,
        ],
        'services' => [
            'name' => 'Services',
            'icon' => 'heroicons:rocket-launch',
            'category' => 'general',
            'description' => 'Services and products',
            'display_order' => 3,
        ],
        'links' => [
            'name' => 'Social Media & Links',
            'icon' => 'heroicons:link',
            'category' => 'general',
            'description' => 'Social links',
            'display_order' => 4,
        ],
        'portfolio' => [
            'name' => 'Portfolio',
            'icon' => 'heroicons:briefcase',
            'category' => 'general',
            'description' => 'Projects and work samples',
            'display_order' => 5,
        ],
        'blog' => [
            'name' => 'Blog',
            'icon' => 'heroicons:newspaper',
            'category' => 'general',
            'description' => 'Blog posts and articles',
            'display_order' => 6,
        ],
    ],
];
```

---

### Phase 3: 后端实现

#### 更新 ProfileBuilderFieldController

```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfileBuilderField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileBuilderFieldController extends Controller
{
    /**
     * Get all sections with their fields
     */
    public function getSections(Request $request)
    {
        $plan = $request->query('plan');
        
        // Get all fields
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
            // Get section metadata from config file
            $sectionConfig = config("profile_sections.sections.{$tabKey}", [
                'name' => ucfirst($tabKey),
                'icon' => 'heroicons:folder',
                'category' => 'general',
            ]);
            
            // Filter out section headers
            $fields = $sectionFields->filter(function($field) {
                return $field->field_type !== 'section_header';
            })->values();
            
            return [
                'section_key' => $tabKey,
                'section_name' => $sectionConfig['name'],
                'icon' => $sectionConfig['icon'],
                'category' => $sectionConfig['category'],
                'description' => $sectionConfig['description'] ?? '',
                'display_order' => $sectionConfig['display_order'] ?? 0,
                'fields' => $fields,
            ];
        })->sortBy('display_order')->values();
        
        return response()->json([
            'success' => true,
            'data' => $sections,
        ]);
    }
    
    /**
     * Get available section types
     */
    public function getAvailableSections()
    {
        $sections = config('profile_sections.sections', []);
        
        // Get sections that have at least one field
        $usedSections = ProfileBuilderField::select('tab')
            ->distinct()
            ->pluck('tab');
        
        return response()->json([
            'success' => true,
            'data' => [
                'all_sections' => $sections,
                'used_sections' => $usedSections,
            ],
        ]);
    }
    
    /**
     * Store a new field (with section support)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tab' => 'required|string|max:50',  // Section identifier
            'field_key' => 'required|string|max:100|unique:profile_builder_fields,field_key',
            'field_type' => 'required|in:text,email,tel,url,textarea,richtext,number,date,select,image,video,file,gallery,repeater,toggle,checkbox,icon,color',
            'label' => 'required|string|max:255',
            'placeholder' => 'nullable|string',
            'help_text' => 'nullable|string',
            'is_required' => 'boolean',
            'is_visible' => 'boolean',
            'validation_rules' => 'nullable|array',
            'available_plans' => 'nullable|array',
            'display_order' => 'integer|min:0',
            'config' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $field = ProfileBuilderField::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Field created successfully',
            'data' => $field,
        ], 201);
    }
    
    // ... 其他方法保持不变
}
```

#### 更新 Routes

```php
// routes/api.php

Route::middleware('auth:sanctum')->group(function () {
    // User Routes
    Route::get('/profile-builder-sections', [ProfileBuilderFieldController::class, 'getSections']);
    Route::get('/profile-builder-fields', [ProfileBuilderFieldController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Admin Routes
    Route::prefix('profile-builder')->group(function () {
        Route::get('/sections', [ProfileBuilderFieldController::class, 'getAvailableSections']);
        Route::get('/sections-with-fields', [ProfileBuilderFieldController::class, 'getSections']);
        Route::get('/fields', [ProfileBuilderFieldController::class, 'index']);
        Route::post('/fields', [ProfileBuilderFieldController::class, 'store']);
        Route::put('/fields/{id}', [ProfileBuilderFieldController::class, 'update']);
        Route::delete('/fields/{id}', [ProfileBuilderFieldController::class, 'destroy']);
    });
});
```

---

### Phase 4: 前端实现

#### Admin 页面 - Section 管理

```vue
<template>
  <div class="profile-builder-management">
    <!-- Section Selector -->
    <div class="mb-6">
      <h2 class="text-2xl font-bold mb-4">Manage Sections</h2>
      
      <!-- Section Tabs -->
      <div class="flex flex-wrap gap-2 mb-4">
        <button
          v-for="section in availableSections"
          :key="section.key"
          @click="currentSection = section.key"
          :class="[
            'px-4 py-2 rounded-lg font-medium',
            currentSection === section.key
              ? 'bg-blue-600 text-white'
              : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
          ]"
        >
          <Icon :name="section.icon" class="w-4 h-4 inline mr-2" />
          {{ section.name }}
        </button>
        
        <!-- Add New Section -->
        <button
          @click="showAddSectionModal = true"
          class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700"
        >
          <Icon name="heroicons:plus" class="w-4 h-4 inline mr-2" />
          Add Section
        </button>
      </div>
    </div>
    
    <!-- Fields for Current Section -->
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold">
          {{ getCurrentSectionName() }} Fields
        </h3>
        <button
          @click="openAddFieldModal(currentSection)"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <Icon name="heroicons:plus" class="w-4 h-4 inline mr-2" />
          Add Field
        </button>
      </div>
      
      <!-- Fields Table -->
      <table class="w-full">
        <thead>
          <tr class="bg-gray-50">
            <th class="px-4 py-2 text-left">Field</th>
            <th class="px-4 py-2 text-left">Type</th>
            <th class="px-4 py-2 text-center">Order</th>
            <th class="px-4 py-2 text-center">Plans</th>
            <th class="px-4 py-2 text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="field in currentSectionFields" :key="field.id">
            <td class="px-4 py-2">
              <div class="font-semibold">{{ field.label }}</div>
              <div class="text-xs text-gray-500">{{ field.field_key }}</div>
            </td>
            <td class="px-4 py-2">
              <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">
                {{ field.field_type }}
              </span>
            </td>
            <td class="px-4 py-2 text-center">
              <input
                v-model.number="field.display_order"
                type="number"
                class="w-16 px-2 py-1 border rounded text-center"
                @change="updateField(field)"
              />
            </td>
            <td class="px-4 py-2 text-center">
              <div class="flex items-center justify-center gap-1">
                <span
                  v-for="plan in field.available_plans"
                  :key="plan"
                  class="px-2 py-0.5 rounded text-xs"
                  :class="getPlanBadgeClass(plan)"
                >
                  {{ plan }}
                </span>
              </div>
            </td>
            <td class="px-4 py-2 text-center">
              <button
                @click="editField(field)"
                class="text-blue-600 hover:text-blue-800 mr-2"
              >
                Edit
              </button>
              <button
                @click="deleteField(field)"
                class="text-red-600 hover:text-red-800"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <!-- Add Section Modal -->
    <Modal v-model="showAddSectionModal">
      <h3 class="text-xl font-bold mb-4">Add New Section</h3>
      <form @submit.prevent="addNewSection">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-2">Section Key</label>
            <input
              v-model="newSection.key"
              type="text"
              placeholder="e.g., portfolio, testimonials"
              class="w-full px-3 py-2 border rounded"
              required
            />
          </div>
          <div>
            <label class="block text-sm font-medium mb-2">Section Name</label>
            <input
              v-model="newSection.name"
              type="text"
              placeholder="e.g., Portfolio, Testimonials"
              class="w-full px-3 py-2 border rounded"
              required
            />
          </div>
          <div>
            <label class="block text-sm font-medium mb-2">Icon</label>
            <input
              v-model="newSection.icon"
              type="text"
              placeholder="heroicons:briefcase"
              class="w-full px-3 py-2 border rounded"
            />
          </div>
          <div>
            <label class="block text-sm font-medium mb-2">Category</label>
            <select v-model="newSection.category" class="w-full px-3 py-2 border rounded">
              <option value="general">General</option>
              <option value="design">Design</option>
            </select>
          </div>
          <div class="flex justify-end gap-2">
            <button
              type="button"
              @click="showAddSectionModal = false"
              class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
              Add Section
            </button>
          </div>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
const availableSections = ref([]);
const currentSection = ref('profile');
const currentSectionFields = ref([]);
const showAddSectionModal = ref(false);
const newSection = ref({
  key: '',
  name: '',
  icon: 'heroicons:folder',
  category: 'general',
});

const loadSections = async () => {
  const response = await $fetch('/api/admin/profile-builder/sections');
  availableSections.value = Object.entries(response.data.all_sections).map(([key, config]) => ({
    key,
    ...config
  }));
};

const loadFieldsForSection = async (sectionKey) => {
  const response = await $fetch('/api/admin/profile-builder/fields', {
    params: { tab: sectionKey }
  });
  currentSectionFields.value = response.data;
};

const addNewSection = async () => {
  // 在配置文件中添加新 Section
  // 注意：这需要后端支持动态添加到 config 文件
  // 或者使用数据库方式（创建一个特殊的 section_header field）
  
  await $fetch('/api/admin/profile-builder/fields', {
    method: 'POST',
    body: {
      tab: newSection.value.key,
      field_key: `_section_header_${newSection.value.key}`,
      field_type: 'section_header',
      label: newSection.value.name,
      config: {
        is_section_header: true,
        section_icon: newSection.value.icon,
        section_category: newSection.value.category,
      },
      is_visible: true,
      display_order: 0,
    }
  });
  
  showAddSectionModal.value = false;
  await loadSections();
};

onMounted(() => {
  loadSections();
  loadFieldsForSection(currentSection.value);
});

watch(currentSection, (newSection) => {
  loadFieldsForSection(newSection);
});
</script>
```

---

## 📋 新增字段类型支持

### 扩展 field_type 验证

更新 `ProfileBuilderFieldController` 的验证规则：

```php
'field_type' => 'required|in:text,email,tel,url,textarea,richtext,number,date,select,image,video,file,gallery,repeater,toggle,checkbox,icon,color',
```

### 字段类型配置示例

```php
// Repeater Field
[
    'tab' => 'portfolio',
    'field_key' => 'projects',
    'field_type' => 'repeater',
    'label' => 'Projects',
    'config' => [
        'max_items' => 12,
        'sub_fields' => [
            ['key' => 'title', 'label' => 'Project Title', 'type' => 'text', 'required' => true],
            ['key' => 'description', 'label' => 'Description', 'type' => 'richtext'],
            ['key' => 'coverImage', 'label' => 'Cover Image', 'type' => 'image'],
            ['key' => 'gallery', 'label' => 'Gallery', 'type' => 'gallery', 'maxItems' => 10],
            ['key' => 'projectUrl', 'label' => 'Project URL', 'type' => 'url'],
            ['key' => 'dateCompleted', 'label' => 'Date Completed', 'type' => 'date'],
        ],
    ],
]

// Rich Text Field
[
    'tab' => 'blog',
    'field_key' => 'post_content',
    'field_type' => 'richtext',
    'label' => 'Post Content',
    'config' => [
        'toolbar' => ['bold', 'italic', 'underline', 'link', 'bulletList', 'orderedList'],
        'maxLength' => 5000,
    ],
]

// Gallery Field
[
    'tab' => 'portfolio',
    'field_key' => 'project_gallery',
    'field_type' => 'gallery',
    'label' => 'Project Gallery',
    'config' => [
        'maxItems' => 20,
        'allowImages' => true,
        'allowVideos' => true,
        'maxFileSize' => 10485760, // 10MB
    ],
]
```

---

## ✅ 优势

### 1. **无需新表**
- 使用现有的 `profile_builder_fields` 表
- 只需扩展 `config` JSON 字段
- 保持数据库简单

### 2. **向后兼容**
- 现有的 4 个 tabs (profile, company, services, links) 继续工作
- 不破坏现有数据
- 平滑迁移

### 3. **灵活扩展**
- 通过 `tab` 字段支持无限 sections
- 通过 `config` 支持各种字段类型配置
- 支持 18+ 种字段类型

### 4. **简单管理**
- Admin 可以通过配置文件或 API 添加新 Section
- 字段管理逻辑保持不变
- 前端只需按 `tab` 分组显示

---

## 🚀 实施步骤

### Step 1: 配置文件设置
```bash
# 创建 Section 配置文件
touch backend/config/profile_sections.php
```

### Step 2: 更新 Controller
```bash
# 添加 getSections() 和 getAvailableSections() 方法
# 修改 backend/app/Http/Controllers/Api/Admin/ProfileBuilderFieldController.php
```

### Step 3: 添加路由
```bash
# 更新 backend/routes/api.php
```

### Step 4: 前端更新
```bash
# 更新 Admin 管理页面支持 Section 管理
# 更新 User Profile Builder 支持动态 Sections
```

### Step 5: Seeder 数据
```bash
# 创建新的 Seeder 添加示例 Sections 和 Fields
php artisan make:seeder ProfileBuilderSectionsSeeder
```

---

## 📝 总结

使用现有的 `profile_builder_fields` 表，通过以下方式实现动态 Sections：

1. **`tab` 字段** = Section 标识符
2. **配置文件** = Section 元数据（名称、图标、分类）
3. **`config` JSON** = 字段特定配置和 Section 配置
4. **特殊 field_type** = `section_header` 标记 Section 头部（可选）

这样就不需要创建新的 `profile_builder_sections` 表，同时实现所有需要的功能！
