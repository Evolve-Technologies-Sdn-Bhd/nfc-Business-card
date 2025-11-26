# Function Completion Plan - 完善全部功能

## 📊 Current Status Analysis

### ✅ Already Implemented
1. **Backend**
   - ProfileBuilderFieldController with getSections()
   - API routes for sections and fields
   - 18 field types validation
   - Portfolio and Blog seeders

2. **Frontend**
   - User Profile Builder - dynamic sections loading
   - Admin Profile Builder - section management
   - Basic field rendering

### ⚠️ Missing/Incomplete

#### 1. **Field Type Components** (Missing)
Need components for new field types:
- [ ] RichTextEditor.vue - For richtext type
- [ ] VideoInput.vue - For video URL/upload
- [ ] GalleryUpload.vue - For multiple images/videos
- [ ] DatePicker.vue - For date selection
- [ ] IconPicker.vue - For icon selection
- [ ] ColorPicker.vue - For color picker
- [ ] RepeaterField.vue - For repeatable field groups (may exist, need to check)

#### 2. **DynamicFormField Component** (Incomplete)
- [ ] Add support for all 18 field types
- [ ] Proper v-model binding
- [ ] Validation integration
- [ ] Error display

#### 3. **User Profile Builder** (Incomplete)
- [ ] Render Portfolio section fields
- [ ] Render Blog section fields
- [ ] Save/load portfolio data
- [ ] Save/load blog data
- [ ] Field validation

#### 4. **Admin Management** (Incomplete)
- [ ] Field type selector with all 18 types
- [ ] Config editor for each field type
- [ ] Preview for repeater sub-fields
- [ ] Validation rules editor

#### 5. **Data Persistence** (Incomplete)
- [ ] Landing page data structure for new sections
- [ ] Save portfolio data to landing_page
- [ ] Save blog data to landing_page
- [ ] Load and display on public profile

---

## 🎯 Implementation Priority

### Phase 1: Critical Components (High Priority)
**Goal**: Enable basic functionality for all field types

1. **Update DynamicFormField.vue**
   - Support all 18 field types
   - Basic rendering for each type
   - v-model binding

2. **Create Essential Components**
   - DatePicker.vue (using native or library)
   - ColorPicker.vue (using native color input)
   - Simple text fallbacks for complex types

### Phase 2: Rich Components (Medium Priority)
**Goal**: Enhance user experience

3. **Create Rich Components**
   - RichTextEditor.vue (using TipTap or similar)
   - GalleryUpload.vue (multiple file upload)
   - VideoInput.vue (URL + upload)

4. **Enhance RepeaterField.vue**
   - Support nested fields
   - Drag-and-drop reordering
   - Add/remove items

### Phase 3: Advanced Features (Low Priority)
**Goal**: Professional-grade features

5. **Create Advanced Components**
   - IconPicker.vue (searchable icon library)
   - Advanced validation
   - Real-time preview

6. **Data Integration**
   - Landing page rendering
   - Public profile display
   - SEO optimization

---

## 📝 Detailed Implementation

### 1. DynamicFormField.vue Enhancement

**Location**: `frontend/components/DynamicFormField.vue`

**Current Status**: May exist with basic support

**Required Updates**:
```vue
<template>
  <div class="dynamic-field" :class="`field-type-${field.field_type}`">
    <label v-if="field.label" class="block text-sm font-medium mb-2">
      {{ field.label }}
      <span v-if="field.is_required" class="text-red-500">*</span>
    </label>
    
    <!-- Basic Text Inputs -->
    <input
      v-if="['text', 'email', 'tel', 'url', 'number'].includes(field.field_type)"
      :type="field.field_type"
      v-model="localValue"
      :placeholder="field.placeholder"
      :required="field.is_required"
      class="w-full px-3 py-2 border rounded-lg"
    />
    
    <!-- Textarea -->
    <textarea
      v-else-if="field.field_type === 'textarea'"
      v-model="localValue"
      :placeholder="field.placeholder"
      :required="field.is_required"
      class="w-full px-3 py-2 border rounded-lg"
      rows="4"
    />
    
    <!-- Rich Text Editor -->
    <RichTextEditor
      v-else-if="field.field_type === 'richtext'"
      v-model="localValue"
      :config="field.config"
    />
    
    <!-- Date Picker -->
    <DatePicker
      v-else-if="field.field_type === 'date'"
      v-model="localValue"
    />
    
    <!-- Select Dropdown -->
    <select
      v-else-if="field.field_type === 'select'"
      v-model="localValue"
      class="w-full px-3 py-2 border rounded-lg"
    >
      <option value="">Select...</option>
      <option
        v-for="option in field.config?.options || []"
        :key="option"
        :value="option"
      >
        {{ option }}
      </option>
    </select>
    
    <!-- Toggle Switch -->
    <label
      v-else-if="field.field_type === 'toggle'"
      class="flex items-center cursor-pointer"
    >
      <input
        type="checkbox"
        v-model="localValue"
        class="sr-only peer"
      />
      <div class="toggle-switch"></div>
      <span class="ml-3 text-sm">{{ field.config?.onLabel || 'On' }}</span>
    </label>
    
    <!-- Checkbox -->
    <label
      v-else-if="field.field_type === 'checkbox'"
      class="flex items-center"
    >
      <input
        type="checkbox"
        v-model="localValue"
        class="w-4 h-4 rounded"
      />
      <span class="ml-2 text-sm">{{ field.placeholder }}</span>
    </label>
    
    <!-- Image Upload -->
    <ImageUpload
      v-else-if="field.field_type === 'image'"
      v-model="localValue"
      :nfc-card-id="nfcCardId"
      :max-size="field.config?.maxSize"
    />
    
    <!-- Video Input -->
    <VideoInput
      v-else-if="field.field_type === 'video'"
      v-model="localValue"
      :allow-upload="field.config?.allowUpload !== false"
    />
    
    <!-- File Upload -->
    <FileUpload
      v-else-if="field.field_type === 'file'"
      v-model="localValue"
      :accept="field.config?.accept"
      :max-size="field.config?.maxSize"
    />
    
    <!-- Gallery Upload -->
    <GalleryUpload
      v-else-if="field.field_type === 'gallery'"
      v-model="localValue"
      :max-items="field.config?.maxItems"
      :allow-videos="field.config?.allowVideos"
    />
    
    <!-- Repeater Field -->
    <RepeaterField
      v-else-if="field.field_type === 'repeater'"
      v-model="localValue"
      :config="field.config"
      :nfc-card-id="nfcCardId"
    />
    
    <!-- Icon Picker -->
    <IconPicker
      v-else-if="field.field_type === 'icon'"
      v-model="localValue"
    />
    
    <!-- Color Picker -->
    <ColorPicker
      v-else-if="field.field_type === 'color'"
      v-model="localValue"
    />
    
    <!-- Fallback for unknown types -->
    <input
      v-else
      type="text"
      v-model="localValue"
      class="w-full px-3 py-2 border rounded-lg"
    />
    
    <!-- Help Text -->
    <p v-if="field.help_text" class="mt-1 text-xs text-gray-500">
      {{ field.help_text }}
    </p>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
  field: {
    type: Object,
    required: true
  },
  modelValue: {
    type: [String, Number, Boolean, Array, Object],
    default: null
  },
  nfcCardId: {
    type: [String, Number],
    default: null
  }
});

const emit = defineEmits(['update:modelValue']);

const localValue = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});
</script>

<style scoped>
.toggle-switch {
  @apply relative w-11 h-6 bg-gray-200 rounded-full peer-checked:bg-blue-600 transition-colors;
}
.toggle-switch::after {
  @apply absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full transition-transform peer-checked:translate-x-5;
  content: '';
}
</style>
```

---

### 2. Create Missing Components

#### DatePicker.vue
```vue
<template>
  <input
    type="date"
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
    class="w-full px-3 py-2 border rounded-lg"
  />
</template>

<script setup>
defineProps({
  modelValue: String
});
defineEmits(['update:modelValue']);
</script>
```

#### ColorPicker.vue
```vue
<template>
  <div class="flex items-center gap-2">
    <input
      type="color"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      class="w-12 h-10 rounded border-2"
    />
    <input
      type="text"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      class="flex-1 px-3 py-2 border rounded-lg font-mono"
      placeholder="#000000"
    />
  </div>
</template>

<script setup>
defineProps({
  modelValue: String
});
defineEmits(['update:modelValue']);
</script>
```

#### VideoInput.vue
```vue
<template>
  <div class="space-y-3">
    <div class="flex gap-2">
      <button
        @click="inputMethod = 'url'"
        :class="inputMethod === 'url' ? 'btn-primary' : 'btn-secondary'"
        class="px-4 py-2 rounded-lg text-sm"
      >
        URL
      </button>
      <button
        v-if="allowUpload"
        @click="inputMethod = 'upload'"
        :class="inputMethod === 'upload' ? 'btn-primary' : 'btn-secondary'"
        class="px-4 py-2 rounded-lg text-sm"
      >
        Upload
      </button>
    </div>
    
    <input
      v-if="inputMethod === 'url'"
      type="url"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      placeholder="https://youtube.com/watch?v=..."
      class="w-full px-3 py-2 border rounded-lg"
    />
    
    <input
      v-else
      type="file"
      accept="video/*"
      @change="handleFileUpload"
      class="w-full px-3 py-2 border rounded-lg"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  modelValue: String,
  allowUpload: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['update:modelValue']);

const inputMethod = ref('url');

const handleFileUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;
  
  // TODO: Upload file to server
  // emit('update:modelValue', uploadedUrl);
};
</script>
```

#### GalleryUpload.vue
```vue
<template>
  <div class="space-y-3">
    <div class="grid grid-cols-3 gap-2">
      <div
        v-for="(item, index) in items"
        :key="index"
        class="relative aspect-square bg-gray-100 rounded-lg overflow-hidden"
      >
        <img
          v-if="item.type === 'image'"
          :src="item.url"
          class="w-full h-full object-cover"
        />
        <video
          v-else
          :src="item.url"
          class="w-full h-full object-cover"
        />
        <button
          @click="removeItem(index)"
          class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6"
        >
          ×
        </button>
      </div>
      
      <label
        v-if="!maxItems || items.length < maxItems"
        class="aspect-square bg-gray-50 border-2 border-dashed rounded-lg flex items-center justify-center cursor-pointer hover:bg-gray-100"
      >
        <span class="text-4xl text-gray-400">+</span>
        <input
          type="file"
          multiple
          :accept="allowVideos ? 'image/*,video/*' : 'image/*'"
          @change="handleFileUpload"
          class="hidden"
        />
      </label>
    </div>
    
    <p class="text-xs text-gray-500">
      {{ items.length }} / {{ maxItems || '∞' }} items
    </p>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  modelValue: Array,
  maxItems: Number,
  allowVideos: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue']);

const items = ref(props.modelValue || []);

watch(() => props.modelValue, (newVal) => {
  items.value = newVal || [];
});

const handleFileUpload = async (event) => {
  const files = Array.from(event.target.files);
  // TODO: Upload files and get URLs
  // items.value.push(...uploadedItems);
  emit('update:modelValue', items.value);
};

const removeItem = (index) => {
  items.value.splice(index, 1);
  emit('update:modelValue', items.value);
};
</script>
```

---

### 3. Admin Field Type Selector

Update admin form modal to support all field types:

```vue
<!-- In profile-builder-design.vue modal -->
<div v-if="isFieldTab">
  <label class="block text-sm font-medium mb-2">Field Type</label>
  <select v-model="formData.field_type" class="w-full px-3 py-2 border rounded-lg">
    <optgroup label="Text Inputs">
      <option value="text">Text</option>
      <option value="email">Email</option>
      <option value="tel">Phone</option>
      <option value="url">URL</option>
      <option value="textarea">Text Area</option>
      <option value="richtext">Rich Text Editor</option>
    </optgroup>
    <optgroup label="Selection">
      <option value="number">Number</option>
      <option value="date">Date</option>
      <option value="select">Dropdown Select</option>
      <option value="toggle">Toggle Switch</option>
      <option value="checkbox">Checkbox</option>
    </optgroup>
    <optgroup label="Media">
      <option value="image">Image Upload</option>
      <option value="video">Video URL/Upload</option>
      <option value="file">File Upload</option>
      <option value="gallery">Gallery (Multiple Images)</option>
    </optgroup>
    <optgroup label="Advanced">
      <option value="repeater">Repeater (Nested Fields)</option>
      <option value="icon">Icon Picker</option>
      <option value="color">Color Picker</option>
    </optgroup>
  </select>
</div>
```

---

## 🧪 Testing Checklist

### Component Testing
- [ ] Each field type renders correctly
- [ ] v-model binding works
- [ ] Validation triggers properly
- [ ] Error messages display
- [ ] Help text shows correctly

### Integration Testing
- [ ] Portfolio fields save to database
- [ ] Blog fields save to database
- [ ] Data loads correctly on page refresh
- [ ] Public profile displays data
- [ ] Plan-based visibility works

### User Flow Testing
- [ ] User can add portfolio items
- [ ] User can add blog posts
- [ ] User can upload images/videos
- [ ] User can reorder items
- [ ] User can delete items

---

## 📦 Implementation Order

1. ✅ **Now**: Create missing components
2. ✅ **Next**: Update DynamicFormField
3. ⏳ **Then**: Test all field types
4. ⏳ **Finally**: Data persistence

---

Last Updated: November 25, 2025
