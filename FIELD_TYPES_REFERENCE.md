# Profile Builder Field Types Reference

## 完整字段类型列表

### 基础输入类型

#### 1. **text** - 文本输入
```json
{
  "field_type": "text",
  "label": "Full Name",
  "placeholder": "Enter your name",
  "validation_rules": {
    "min": 2,
    "max": 100
  }
}
```

#### 2. **email** - 邮箱
```json
{
  "field_type": "email",
  "label": "Email Address",
  "placeholder": "your@email.com",
  "is_required": true
}
```

#### 3. **tel** - 电话号码
```json
{
  "field_type": "tel",
  "label": "Phone Number",
  "placeholder": "+60 12-345 6789"
}
```

#### 4. **url** - 网址
```json
{
  "field_type": "url",
  "label": "Website",
  "placeholder": "https://example.com"
}
```

#### 5. **number** - 数字
```json
{
  "field_type": "number",
  "label": "Years of Experience",
  "config": {
    "min": 0,
    "max": 50,
    "step": 1
  }
}
```

#### 6. **textarea** - 多行文本
```json
{
  "field_type": "textarea",
  "label": "Biography",
  "placeholder": "Tell us about yourself...",
  "config": {
    "rows": 6,
    "maxLength": 1000
  }
}
```

#### 7. **richtext** - 富文本编辑器
```json
{
  "field_type": "richtext",
  "label": "Detailed Description",
  "config": {
    "toolbar": ["bold", "italic", "underline", "link", "bulletList", "orderedList"],
    "maxLength": 5000
  }
}
```

### 选择类型

#### 8. **select** - 下拉选择
```json
{
  "field_type": "select",
  "label": "Industry",
  "config": {
    "options": [
      "Technology",
      "Finance",
      "Healthcare",
      "Education",
      "Retail"
    ],
    "allowCustom": false
  }
}
```

#### 9. **checkbox** - 复选框
```json
{
  "field_type": "checkbox",
  "label": "I agree to terms and conditions",
  "config": {
    "defaultValue": false
  }
}
```

#### 10. **toggle** - 开关
```json
{
  "field_type": "toggle",
  "label": "Enable Notifications",
  "config": {
    "defaultValue": true,
    "onLabel": "Enabled",
    "offLabel": "Disabled"
  }
}
```

### 日期和时间

#### 11. **date** - 日期选择器
```json
{
  "field_type": "date",
  "label": "Date of Birth",
  "config": {
    "format": "YYYY-MM-DD",
    "minDate": "1900-01-01",
    "maxDate": "today"
  }
}
```

### 文件上传

#### 12. **image** - 图片上传
```json
{
  "field_type": "image",
  "label": "Profile Picture",
  "config": {
    "maxSize": 5242880,
    "allowedFormats": ["jpg", "jpeg", "png", "gif", "webp"],
    "aspectRatio": "1:1",
    "maxWidth": 1024,
    "maxHeight": 1024
  }
}
```

#### 13. **video** - 视频上传/链接
```json
{
  "field_type": "video",
  "label": "Promotional Video",
  "config": {
    "allowUpload": true,
    "allowUrl": true,
    "maxSize": 52428800,
    "allowedFormats": ["mp4", "webm", "mov"],
    "allowedProviders": ["youtube", "vimeo"]
  }
}
```

#### 14. **file** - 文件上传
```json
{
  "field_type": "file",
  "label": "Resume / CV",
  "config": {
    "maxSize": 10485760,
    "allowedFormats": ["pdf", "doc", "docx"],
    "multiple": false
  }
}
```

#### 15. **gallery** - 图片/视频画廊
```json
{
  "field_type": "gallery",
  "label": "Portfolio Gallery",
  "config": {
    "maxItems": 20,
    "allowImages": true,
    "allowVideos": true,
    "maxFileSize": 10485760,
    "sortable": true
  }
}
```

### 可重复字段

#### 16. **repeater** - 可重复字段组
```json
{
  "field_type": "repeater",
  "label": "Work Experience",
  "config": {
    "max_items": 10,
    "addButtonText": "Add Experience",
    "sub_fields": [
      {
        "key": "company",
        "label": "Company Name",
        "type": "text",
        "required": true
      },
      {
        "key": "position",
        "label": "Job Title",
        "type": "text",
        "required": true
      },
      {
        "key": "duration",
        "label": "Duration",
        "type": "text",
        "placeholder": "Jan 2020 - Present"
      },
      {
        "key": "description",
        "label": "Description",
        "type": "textarea",
        "rows": 3
      }
    ]
  }
}
```

### 特殊输入

#### 17. **icon** - 图标选择器
```json
{
  "field_type": "icon",
  "label": "Service Icon",
  "config": {
    "iconSet": "heroicons",
    "allowSearch": true,
    "categories": ["business", "communication", "social", "tech"]
  }
}
```

#### 18. **color** - 颜色选择器
```json
{
  "field_type": "color",
  "label": "Brand Color",
  "config": {
    "format": "hex",
    "allowAlpha": false,
    "presets": ["#FF0000", "#00FF00", "#0000FF"]
  }
}
```

---

## 字段配置选项

### 通用选项（所有字段类型）

```json
{
  "field_key": "unique_identifier",
  "field_type": "text",
  "label": "Display Label",
  "placeholder": "Placeholder text",
  "help_text": "Helper text below field",
  "is_required": false,
  "is_visible": true,
  "display_order": 0,
  "available_plans": ["basic", "premium", "business"],
  "validation_rules": {
    "min": 0,
    "max": 255,
    "pattern": "regex_pattern"
  },
  "config": {}
}
```

### Validation Rules

```javascript
validation_rules: {
  min: 2,              // 最小长度/值
  max: 100,            // 最大长度/值
  pattern: "^[A-Za-z]+$",  // 正则表达式
  required: true,      // 必填
  email: true,         // 邮箱格式
  url: true,           // URL 格式
  numeric: true,       // 数字
  alpha: true,         // 字母
  alphanumeric: true   // 字母+数字
}
```

---

## 使用示例

### Portfolio Section 完整示例

```json
{
  "section_key": "portfolio",
  "section_name": "Portfolio",
  "icon": "heroicons:briefcase",
  "category": "general",
  "is_active": true,
  "display_order": 5,
  "available_plans": ["premium", "business"],
  "fields": [
    {
      "field_key": "portfolio_title",
      "field_type": "text",
      "label": "Portfolio Title",
      "is_required": true,
      "display_order": 1
    },
    {
      "field_key": "portfolio_description",
      "field_type": "richtext",
      "label": "Portfolio Description",
      "display_order": 2
    },
    {
      "field_key": "projects",
      "field_type": "repeater",
      "label": "Projects",
      "display_order": 3,
      "config": {
        "max_items": 12,
        "addButtonText": "Add Project",
        "sub_fields": [
          {
            "key": "title",
            "label": "Project Title",
            "type": "text",
            "required": true
          },
          {
            "key": "category",
            "label": "Category",
            "type": "select",
            "options": ["Web Design", "Mobile App", "Branding", "Photography"]
          },
          {
            "key": "description",
            "label": "Description",
            "type": "richtext"
          },
          {
            "key": "coverImage",
            "label": "Cover Image",
            "type": "image"
          },
          {
            "key": "gallery",
            "label": "Project Gallery",
            "type": "gallery",
            "maxItems": 10
          },
          {
            "key": "projectUrl",
            "label": "Project URL",
            "type": "url"
          },
          {
            "key": "dateCompleted",
            "label": "Completion Date",
            "type": "date"
          },
          {
            "key": "clientName",
            "label": "Client Name",
            "type": "text"
          },
          {
            "key": "tags",
            "label": "Tags",
            "type": "repeater",
            "sub_fields": [
              {
                "key": "tag",
                "label": "Tag",
                "type": "text"
              }
            ]
          }
        ]
      }
    }
  ]
}
```

### Blog Section 完整示例

```json
{
  "section_key": "blog",
  "section_name": "Blog",
  "icon": "heroicons:newspaper",
  "category": "general",
  "is_active": true,
  "display_order": 6,
  "available_plans": ["premium", "business"],
  "fields": [
    {
      "field_key": "blog_enabled",
      "field_type": "toggle",
      "label": "Enable Blog Section",
      "display_order": 1,
      "config": {
        "defaultValue": true
      }
    },
    {
      "field_key": "blog_posts",
      "field_type": "repeater",
      "label": "Blog Posts",
      "display_order": 2,
      "config": {
        "max_items": 20,
        "addButtonText": "Add Post",
        "sub_fields": [
          {
            "key": "title",
            "label": "Post Title",
            "type": "text",
            "required": true
          },
          {
            "key": "slug",
            "label": "URL Slug",
            "type": "text",
            "placeholder": "auto-generated-from-title"
          },
          {
            "key": "coverImage",
            "label": "Cover Image",
            "type": "image",
            "required": true
          },
          {
            "key": "category",
            "label": "Category",
            "type": "select",
            "options": ["Technology", "Business", "Lifestyle", "News"]
          },
          {
            "key": "tags",
            "label": "Tags (comma-separated)",
            "type": "text",
            "placeholder": "tag1, tag2, tag3"
          },
          {
            "key": "publishedDate",
            "label": "Published Date",
            "type": "date",
            "required": true
          },
          {
            "key": "content",
            "label": "Content",
            "type": "richtext",
            "required": true
          },
          {
            "key": "excerpt",
            "label": "Excerpt",
            "type": "textarea",
            "rows": 3,
            "maxLength": 300
          },
          {
            "key": "featured",
            "label": "Featured Post",
            "type": "checkbox"
          }
        ]
      }
    }
  ]
}
```

---

## 前端组件实现参考

### DynamicFormField.vue 使用方法

```vue
<template>
  <DynamicFormField
    :field="fieldConfig"
    v-model="formData.fieldKey"
    :nfc-card-id="selectedCardId"
    @update:modelValue="handleFieldUpdate"
  />
</template>

<script setup>
const fieldConfig = {
  field_key: 'bio',
  field_type: 'richtext',
  label: 'Biography',
  is_required: true,
  help_text: 'Share your professional background'
};

const formData = reactive({
  bio: ''
});

const handleFieldUpdate = (value) => {
  console.log('Field updated:', value);
};
</script>
```

---

## 数据库存储格式

### NFC Card Profile Data

```json
{
  "nfc_card_id": 123,
  "profile_data": {
    "profile": {
      "name": "John Doe",
      "email": "john@example.com",
      "bio": "<p>Professional web developer...</p>"
    },
    "company": {
      "companyName": "Tech Corp",
      "companyLogo": "/uploads/logos/tech-corp.png"
    },
    "portfolio": {
      "projects": [
        {
          "title": "E-commerce Platform",
          "category": "Web Design",
          "coverImage": "/uploads/projects/ecommerce.jpg",
          "gallery": [
            "/uploads/gallery/1.jpg",
            "/uploads/gallery/2.jpg"
          ],
          "dateCompleted": "2024-12-01"
        }
      ]
    },
    "blog": {
      "blog_enabled": true,
      "blog_posts": [
        {
          "title": "My First Blog Post",
          "slug": "my-first-blog-post",
          "coverImage": "/uploads/blog/post1.jpg",
          "content": "<p>Blog content here...</p>",
          "publishedDate": "2025-01-15",
          "featured": true
        }
      ]
    }
  }
}
```

---

## API Response 格式

### GET /api/profile-builder-sections?plan=business

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "section_key": "profile",
      "section_name": "Profile",
      "icon": "heroicons:user",
      "category": "general",
      "is_active": true,
      "display_order": 1,
      "available_plans": ["basic", "premium", "business"],
      "fields": [
        {
          "id": 1,
          "section_id": 1,
          "field_key": "name",
          "field_type": "text",
          "label": "Full Name",
          "is_required": true,
          "is_visible": true,
          "display_order": 1
        }
      ]
    }
  ]
}
```
