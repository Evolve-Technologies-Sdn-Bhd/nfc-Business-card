# Profile Builder Redesign - Dynamic Sections & Fields System

## 📋 Overview

重新设计 Profile Builder 系统，支持动态的 Section（Tab）和 Field 管理，Admin 可以自由添加新的 sections 和不同类型的 input fields。

## 🎯 需求

### 新增 Sections (6个主要 Tab)
1. **Profile Tab** - 个人资料
2. **Company Tab & Team Member** - 公司信息和团队成员
3. **Services Tab** - 服务/产品
4. **Social Media & Links Tab** - 社交媒体和链接
5. **Portfolio Tab** - 作品集
6. **Blog Tab** - 博客文章

### 新增字段类型
- `image` - 图片上传
- `video` - 视频上传/URL
- `richtext` - 富文本编辑器 (Markdown/HTML)
- `select` - 下拉选择
- `repeater` - 可重复字段组 (增强版)
- `file` - 文件上传 (PDF, etc.)
- `date` - 日期选择器
- `icon` - 图标选择器
- `toggle` - 开关
- `checkbox` - 复选框
- `gallery` - 图片/视频画廊

### 关键特性
- ✅ Admin 可以动态添加新的 Section
- ✅ Admin 可以为每个 Section 添加不同类型的 Field
- ✅ 使用原有的 API 结构
- ✅ 支持按 Plan (Basic/Premium/Business) 分配权限
- ✅ 字段顺序可调整
- ✅ 字段验证规则可配置

---

## 🏗️ 系统架构

### 当前系统
```
profile_builder_fields (表)
├── tab: profile, company, services, links
├── field_key, field_type, label
├── available_plans (JSON)
└── config (JSON)

API:
- GET /profile-builder-fields (用户获取字段)
- POST /admin/profile-builder-fields (Admin 创建字段)
- PUT /admin/profile-builder-fields/{id} (Admin 更新字段)
- DELETE /admin/profile-builder-fields/{id} (Admin 删除字段)
```

### 新系统架构

#### 1. 数据库设计

**新增表: `profile_builder_sections`**
```sql
- id (bigint)
- section_key (varchar) - UNIQUE (例如: profile, company, portfolio, blog)
- section_name (varchar) - 显示名称
- icon (varchar) - 图标名称
- category (enum: general, design) - 分类
- is_active (boolean) - 是否启用
- display_order (int) - 排序
- available_plans (json) - 哪些 plan 可以使用
- description (text) - 描述
- config (json) - 额外配置
- created_at, updated_at
```

**更新表: `profile_builder_fields`**
```sql
现有字段 +
- section_id (bigint) - 关联到 profile_builder_sections
- (保留 tab 字段兼容性，但逐步迁移到 section_id)
- field_type 扩展: image, video, richtext, select, repeater, file, date, icon, toggle, checkbox, gallery
```

#### 2. 后端实现

**新增 Model**: `ProfileBuilderSection`
```php
<?php
namespace App\Models;

class ProfileBuilderSection extends Model
{
    protected $fillable = [
        'section_key', 'section_name', 'icon', 'category',
        'is_active', 'display_order', 'available_plans',
        'description', 'config'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'available_plans' => 'array',
        'config' => 'array',
    ];
    
    // Relationship
    public function fields()
    {
        return $this->hasMany(ProfileBuilderField::class, 'section_id')
                    ->orderBy('display_order');
    }
}
```

**新增 Controller**: `ProfileBuilderSectionController`
```php
<?php
namespace App\Http\Controllers\Api\Admin;

class ProfileBuilderSectionController extends Controller
{
    public function index(Request $request); // 获取所有 sections
    public function store(Request $request); // 创建新 section
    public function update(Request $request, $id); // 更新 section
    public function destroy($id); // 删除 section
    public function reorder(Request $request); // 调整顺序
}
```

**更新 Controller**: `ProfileBuilderFieldController`
```php
- 添加 section_id 支持
- 支持新的字段类型验证
- 支持 repeater 子字段配置
```

**新增 Routes**:
```php
// User Routes (public)
Route::get('/profile-builder-sections', [ProfileBuilderSectionController::class, 'index']);
Route::get('/profile-builder-fields', [ProfileBuilderFieldController::class, 'index']);

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::resource('profile-builder-sections', ProfileBuilderSectionController::class);
    Route::post('profile-builder-sections/reorder', [ProfileBuilderSectionController::class, 'reorder']);
    
    Route::resource('profile-builder-fields', ProfileBuilderFieldController::class);
});
```

#### 3. 前端实现

**Admin 管理页面**: `profile-builder-design.vue`

```vue
<template>
  <!-- Section Management -->
  <div class="section-management">
    <h2>Manage Sections</h2>
    <button @click="openAddSectionModal">+ Add New Section</button>
    
    <draggable v-model="sections" @end="updateSectionOrder">
      <div v-for="section in sections" :key="section.id">
        <div class="section-header">
          <Icon :name="section.icon" />
          <span>{{ section.section_name }}</span>
          <button @click="editSection(section)">Edit</button>
          <button @click="deleteSection(section)">Delete</button>
        </div>
        
        <!-- Fields for this section -->
        <div class="section-fields">
          <h3>Fields for {{ section.section_name }}</h3>
          <button @click="openAddFieldModal(section)">+ Add Field</button>
          
          <table>
            <thead>
              <tr>
                <th>Field Name</th>
                <th>Type</th>
                <th>Required</th>
                <th>Plan Availability</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="field in section.fields" :key="field.id">
                <td>{{ field.label }}</td>
                <td>{{ field.field_type }}</td>
                <td>{{ field.is_required ? 'Yes' : 'No' }}</td>
                <td>{{ field.available_plans.join(', ') }}</td>
                <td>
                  <button @click="editField(field)">Edit</button>
                  <button @click="deleteField(field)">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </draggable>
  </div>
  
  <!-- Add/Edit Section Modal -->
  <Modal v-model="showSectionModal">
    <h3>{{ sectionModalMode === 'add' ? 'Add' : 'Edit' }} Section</h3>
    <form @submit.prevent="saveSection">
      <input v-model="sectionForm.section_key" placeholder="Section Key (e.g., portfolio)" />
      <input v-model="sectionForm.section_name" placeholder="Display Name (e.g., Portfolio)" />
      <IconPicker v-model="sectionForm.icon" />
      <select v-model="sectionForm.category">
        <option value="general">General</option>
        <option value="design">Design</option>
      </select>
      
      <!-- Plan Availability -->
      <div>
        <label><input type="checkbox" value="basic" v-model="sectionForm.available_plans" /> Basic</label>
        <label><input type="checkbox" value="premium" v-model="sectionForm.available_plans" /> Premium</label>
        <label><input type="checkbox" value="business" v-model="sectionForm.available_plans" /> Business</label>
      </div>
      
      <button type="submit">Save</button>
    </form>
  </Modal>
  
  <!-- Add/Edit Field Modal -->
  <Modal v-model="showFieldModal">
    <h3>{{ fieldModalMode === 'add' ? 'Add' : 'Edit' }} Field</h3>
    <form @submit.prevent="saveField">
      <input v-model="fieldForm.field_key" placeholder="Field Key (e.g., title)" />
      <input v-model="fieldForm.label" placeholder="Label (e.g., Project Title)" />
      
      <select v-model="fieldForm.field_type">
        <option value="text">Text</option>
        <option value="email">Email</option>
        <option value="tel">Phone</option>
        <option value="url">URL</option>
        <option value="textarea">Textarea</option>
        <option value="richtext">Rich Text</option>
        <option value="number">Number</option>
        <option value="date">Date</option>
        <option value="select">Select</option>
        <option value="image">Image</option>
        <option value="video">Video</option>
        <option value="file">File</option>
        <option value="gallery">Gallery</option>
        <option value="repeater">Repeater</option>
        <option value="toggle">Toggle</option>
        <option value="checkbox">Checkbox</option>
        <option value="icon">Icon Picker</option>
      </select>
      
      <!-- Type-specific config -->
      <div v-if="fieldForm.field_type === 'select'">
        <label>Options (comma-separated)</label>
        <input v-model="fieldForm.config.options" placeholder="Option 1, Option 2, Option 3" />
      </div>
      
      <div v-if="fieldForm.field_type === 'repeater'">
        <label>Sub-fields (JSON)</label>
        <textarea v-model="fieldForm.config.sub_fields" rows="4"></textarea>
        <label>Max Items</label>
        <input type="number" v-model="fieldForm.config.max_items" />
      </div>
      
      <!-- Validation -->
      <label><input type="checkbox" v-model="fieldForm.is_required" /> Required</label>
      <label><input type="checkbox" v-model="fieldForm.is_visible" /> Visible</label>
      
      <!-- Plan Availability -->
      <div>
        <label><input type="checkbox" value="basic" v-model="fieldForm.available_plans" /> Basic</label>
        <label><input type="checkbox" value="premium" v-model="fieldForm.available_plans" /> Premium</label>
        <label><input type="checkbox" value="business" v-model="fieldForm.available_plans" /> Business</label>
      </div>
      
      <button type="submit">Save</button>
    </form>
  </Modal>
</template>

<script setup>
const sections = ref([]);
const showSectionModal = ref(false);
const showFieldModal = ref(false);

const loadSections = async () => {
  const response = await $fetch('/api/admin/profile-builder-sections');
  sections.value = response.data;
};

const saveSection = async () => {
  if (sectionModalMode.value === 'add') {
    await $fetch('/api/admin/profile-builder-sections', {
      method: 'POST',
      body: sectionForm.value
    });
  } else {
    await $fetch(`/api/admin/profile-builder-sections/${editingSectionId.value}`, {
      method: 'PUT',
      body: sectionForm.value
    });
  }
  await loadSections();
  showSectionModal.value = false;
};

// Similar for fields...
</script>
```

**用户页面**: `BusinessProfileBuilder.vue`

```vue
<template>
  <div class="profile-builder">
    <!-- Section Tabs (Dynamic) -->
    <div class="tabs">
      <button
        v-for="section in availableSections"
        :key="section.id"
        @click="activeSection = section.section_key"
        :class="{ active: activeSection === section.section_key }"
      >
        <Icon :name="section.icon" />
        {{ section.section_name }}
      </button>
    </div>
    
    <!-- Section Content (Dynamic) -->
    <div class="section-content">
      <div v-for="section in availableSections" :key="section.id" v-show="activeSection === section.section_key">
        <h2>{{ section.section_name }}</h2>
        
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
const activeSection = ref('profile');
const profileData = ref({});

const loadSections = async () => {
  const response = await $fetch('/api/profile-builder-sections', {
    params: { plan: authStore.user?.subscription_plan }
  });
  sections.value = response.data;
  
  // Load fields for each section
  for (const section of sections.value) {
    const fieldsResponse = await $fetch('/api/profile-builder-fields', {
      params: { section_id: section.id, plan: authStore.user?.subscription_plan }
    });
    section.fields = fieldsResponse.data;
  }
};

const availableSections = computed(() => {
  return sections.value.filter(s => s.is_active);
});
</script>
```

**动态字段组件**: `DynamicFormField.vue`

```vue
<template>
  <div class="dynamic-field">
    <label>
      {{ field.label }}
      <span v-if="field.is_required" class="required">*</span>
    </label>
    <p v-if="field.help_text" class="help-text">{{ field.help_text }}</p>
    
    <!-- Text, Email, Tel, URL, Number -->
    <input
      v-if="['text', 'email', 'tel', 'url', 'number'].includes(field.field_type)"
      :type="field.field_type"
      :placeholder="field.placeholder"
      v-model="localValue"
      @input="updateValue"
    />
    
    <!-- Textarea -->
    <textarea
      v-else-if="field.field_type === 'textarea'"
      :placeholder="field.placeholder"
      v-model="localValue"
      @input="updateValue"
      rows="4"
    ></textarea>
    
    <!-- Rich Text Editor -->
    <RichTextEditor
      v-else-if="field.field_type === 'richtext'"
      v-model="localValue"
      @update:modelValue="updateValue"
    />
    
    <!-- Date Picker -->
    <DatePicker
      v-else-if="field.field_type === 'date'"
      v-model="localValue"
      @update:modelValue="updateValue"
    />
    
    <!-- Select Dropdown -->
    <select
      v-else-if="field.field_type === 'select'"
      v-model="localValue"
      @change="updateValue"
    >
      <option value="">-- Select --</option>
      <option v-for="option in getSelectOptions(field)" :key="option" :value="option">
        {{ option }}
      </option>
    </select>
    
    <!-- Image Upload -->
    <ImageUpload
      v-else-if="field.field_type === 'image'"
      v-model="localValue"
      :nfc-card-id="nfcCardId"
      @update:modelValue="updateValue"
    />
    
    <!-- Video Upload/URL -->
    <VideoInput
      v-else-if="field.field_type === 'video'"
      v-model="localValue"
      @update:modelValue="updateValue"
    />
    
    <!-- File Upload -->
    <FileUpload
      v-else-if="field.field_type === 'file'"
      v-model="localValue"
      :nfc-card-id="nfcCardId"
      :accept="field.config?.accept || '.pdf,.doc,.docx'"
      @update:modelValue="updateValue"
    />
    
    <!-- Gallery -->
    <GalleryUpload
      v-else-if="field.field_type === 'gallery'"
      v-model="localValue"
      :nfc-card-id="nfcCardId"
      :max-items="field.config?.max_items || 10"
      @update:modelValue="updateValue"
    />
    
    <!-- Repeater -->
    <RepeaterField
      v-else-if="field.field_type === 'repeater'"
      v-model="localValue"
      :sub-fields="field.config?.sub_fields || []"
      :max-items="field.config?.max_items || 5"
      @update:modelValue="updateValue"
    />
    
    <!-- Toggle -->
    <Toggle
      v-else-if="field.field_type === 'toggle'"
      v-model="localValue"
      @update:modelValue="updateValue"
    />
    
    <!-- Checkbox -->
    <Checkbox
      v-else-if="field.field_type === 'checkbox'"
      v-model="localValue"
      @update:modelValue="updateValue"
    />
    
    <!-- Icon Picker -->
    <IconPicker
      v-else-if="field.field_type === 'icon'"
      v-model="localValue"
      @update:modelValue="updateValue"
    />
  </div>
</template>

<script setup>
const props = defineProps({
  field: Object,
  modelValue: [String, Number, Boolean, Array, Object],
  nfcCardId: [String, Number]
});

const emit = defineEmits(['update:modelValue']);

const localValue = ref(props.modelValue);

watch(() => props.modelValue, (newVal) => {
  localValue.value = newVal;
});

const updateValue = () => {
  emit('update:modelValue', localValue.value);
};

const getSelectOptions = (field) => {
  if (field.config?.options) {
    if (typeof field.config.options === 'string') {
      return field.config.options.split(',').map(opt => opt.trim());
    }
    return field.config.options;
  }
  return [];
};
</script>
```

---

## 📝 实施步骤

### Phase 1: 数据库和后端 (Backend)

1. **创建 Migration**
   - `create_profile_builder_sections_table.php`
   - 更新 `profile_builder_fields` 表添加 `section_id`

2. **创建 Model**
   - `ProfileBuilderSection.php`
   - 更新 `ProfileBuilderField.php` 添加 relationship

3. **创建 Controller**
   - `ProfileBuilderSectionController.php`
   - 更新 `ProfileBuilderFieldController.php`

4. **更新 Routes**
   - 添加 section 管理路由
   - 更新 field 路由支持 section_id

5. **创建 Seeder**
   - 初始化 6 个默认 sections
   - 迁移现有 fields 到对应的 sections

### Phase 2: 前端管理页面 (Admin)

1. **更新 `profile-builder-design.vue`**
   - 添加 Section 管理功能
   - 更新 Field 管理功能支持 section_id
   - 添加拖拽排序
   - 添加新的字段类型支持

2. **创建新组件**
   - `SectionModal.vue` - Section 添加/编辑 modal
   - `FieldModal.vue` - Field 添加/编辑 modal (增强版)
   - `FieldTypeConfig.vue` - 不同字段类型的配置界面

### Phase 3: 前端用户页面 (User)

1. **更新 `BusinessProfileBuilder.vue`**
   - 动态加载 sections
   - 动态渲染 tabs
   - 使用 `DynamicFormField` 组件

2. **创建/增强组件**
   - `DynamicFormField.vue` - 动态字段渲染
   - `RichTextEditor.vue` - 富文本编辑器
   - `VideoInput.vue` - 视频输入
   - `FileUpload.vue` - 文件上传
   - `GalleryUpload.vue` - 画廊上传
   - `RepeaterField.vue` - 可重复字段组
   - `IconPicker.vue` - 图标选择器
   - `Toggle.vue` - 开关
   - `DatePicker.vue` - 日期选择器

### Phase 4: 测试和优化

1. **功能测试**
   - Admin 添加/编辑/删除 section
   - Admin 添加/编辑/删除 field
   - 用户根据 plan 看到正确的 sections 和 fields
   - 所有字段类型正确渲染和保存

2. **性能优化**
   - 懒加载 sections
   - 优化图片/文件上传
   - 缓存 section 配置

3. **用户体验优化**
   - 加载状态
   - 错误处理
   - 表单验证
   - 保存提示

---

## 🔍 关键技术点

### 1. Repeater Field 实现
```javascript
// Repeater 子字段配置示例
{
  "field_type": "repeater",
  "config": {
    "max_items": 5,
    "sub_fields": [
      {
        "key": "title",
        "label": "Title",
        "type": "text",
        "required": true
      },
      {
        "key": "description",
        "label": "Description",
        "type": "textarea"
      },
      {
        "key": "image",
        "label": "Image",
        "type": "image"
      }
    ]
  }
}
```

### 2. Plan-based Visibility
```javascript
// 后端查询
ProfileBuilderSection::where('is_active', true)
  ->where(function($q) use ($plan) {
    $q->whereJsonContains('available_plans', $plan)
      ->orWhereNull('available_plans')
      ->orWhereJsonLength('available_plans', 0);
  })
  ->with(['fields' => function($q) use ($plan) {
    $q->where('is_visible', true)
      ->where(function($q2) use ($plan) {
        $q2->whereJsonContains('available_plans', $plan)
           ->orWhereNull('available_plans')
           ->orWhereJsonLength('available_plans', 0);
      });
  }])
  ->orderBy('display_order')
  ->get();
```

### 3. 数据保存格式
```javascript
// User profile data 保存格式
{
  "nfc_card_id": 123,
  "section_data": {
    "profile": {
      "name": "John Doe",
      "email": "john@example.com",
      "bio": "..."
    },
    "portfolio": {
      "projects": [  // repeater
        {
          "title": "Project 1",
          "description": "...",
          "image": "/uploads/..."
        }
      ]
    },
    "blog": {
      "posts": [
        {
          "title": "Blog Post 1",
          "content": "<p>...</p>",
          "publishedDate": "2025-01-01"
        }
      ]
    }
  }
}
```

---

## ✅ 优势

1. **完全动态** - Admin 可以随时添加新的 section 和 field，无需修改代码
2. **灵活配置** - 每个字段都有独立的配置和验证规则
3. **Plan 控制** - 精确控制哪些 plan 可以使用哪些功能
4. **可扩展** - 新增字段类型只需添加组件，不需要修改核心逻辑
5. **向后兼容** - 保留 `tab` 字段，逐步迁移
6. **用户友好** - 统一的界面，所有字段类型都有一致的体验

---

## 📋 待办事项清单

### Backend
- [ ] 创建 `profile_builder_sections` 表 migration
- [ ] 更新 `profile_builder_fields` 表添加 `section_id`
- [ ] 创建 `ProfileBuilderSection` Model
- [ ] 创建 `ProfileBuilderSectionController`
- [ ] 更新 `ProfileBuilderFieldController` 支持 section_id
- [ ] 添加 API Routes
- [ ] 创建 Seeder 初始化默认 sections

### Frontend - Admin
- [ ] 重构 `profile-builder-design.vue` 添加 Section 管理
- [ ] 创建 Section Modal 组件
- [ ] 更新 Field Modal 支持新字段类型
- [ ] 添加拖拽排序功能
- [ ] 添加字段类型配置界面

### Frontend - User
- [ ] 重构 `BusinessProfileBuilder.vue` 支持动态 sections
- [ ] 创建 `DynamicFormField.vue` 组件
- [ ] 创建 `RichTextEditor.vue` 组件
- [ ] 创建 `VideoInput.vue` 组件
- [ ] 创建 `FileUpload.vue` 组件
- [ ] 创建 `GalleryUpload.vue` 组件
- [ ] 创建 `RepeaterField.vue` 组件
- [ ] 创建 `IconPicker.vue` 组件
- [ ] 创建 `Toggle.vue` 组件
- [ ] 创建 `DatePicker.vue` 组件

### Testing
- [ ] 测试 Section CRUD
- [ ] 测试 Field CRUD
- [ ] 测试 Plan-based visibility
- [ ] 测试所有字段类型渲染
- [ ] 测试数据保存和加载
- [ ] 性能测试

---

## 🚀 开始实施

准备好开始实施了吗？我们可以按照以下顺序进行：

1. **先实现 Backend** - 确保数据结构正确
2. **再实现 Admin 页面** - 让 Admin 可以管理
3. **最后实现 User 页面** - 让用户可以使用

你想从哪个部分开始？
