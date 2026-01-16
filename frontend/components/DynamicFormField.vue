<template>
  <div v-if="shouldShowField" class="space-y-1">
    <!-- Label -->
    <label class="block text-sm font-medium text-gray-700">
      {{ field.label }}
      <span v-if="!field.is_required" class="text-xs text-gray-500">(Optional)</span>
      <span v-if="field.is_required" class="text-error-600">*</span>
    </label>

    <!-- Help Text -->
    <p v-if="field.help_text" class="text-xs text-gray-500 mb-1">
      {{ field.help_text }}
    </p>

    <!-- Text Input -->
    <input
      v-if="field.field_type === 'text' || field.field_type === 'email' || field.field_type === 'tel' || field.field_type === 'url'"
      :type="field.field_type"
      v-model="modelValue[field.field_key]"
      :placeholder="field.placeholder"
      :required="field.is_required"
      :minlength="field.validation_rules?.min"
      :maxlength="field.validation_rules?.max"
      class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
      @input="$emit('update:modelValue', modelValue)"
    />

    <!-- Textarea -->
    <div v-else-if="field.field_type === 'textarea'" class="space-y-1">
      <textarea
        v-model="modelValue[field.field_key]"
        :placeholder="field.placeholder"
        :required="field.is_required"
        :minlength="field.validation_rules?.min"
        :maxlength="getTextareaMaxLength(field)"
        :rows="field.config?.rows || 4"
        class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
        :class="{ 'border-amber-400 focus:ring-amber-400': isNearCharLimit(field) }"
        @input="$emit('update:modelValue', modelValue)"
      ></textarea>
      <p 
        class="text-xs text-right"
        :class="isNearCharLimit(field) ? 'text-amber-600 font-medium' : 'text-gray-500'"
      >
        <span v-if="isAtCharLimit(field) && getTextareaMaxLength(field) === 300" class="text-red-500 font-bold mr-1">About Me cannot exceed 300 characters</span>
        <span v-else>
            {{ (modelValue[field.field_key] || '').length }} / {{ getTextareaMaxLength(field) }} characters
            <span v-if="isAtCharLimit(field)" class="text-red-500 ml-1">• Limit reached</span>
        </span>
      </p>
    </div>

    <!-- Number Input -->
    <input
      v-else-if="field.field_type === 'number'"
      type="number"
      v-model.number="modelValue[field.field_key]"
      :placeholder="field.placeholder"
      :required="field.is_required"
      :min="field.validation_rules?.min"
      :max="field.validation_rules?.max"
      class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
      @input="$emit('update:modelValue', modelValue)"
    />

    <!-- Rich Text Editor -->
    <div v-else-if="field.field_type === 'richtext'" class="border border-gray-300 rounded-lg overflow-hidden"
         :class="{ 'border-amber-400': userPlan === 'basic' && isNearCharLimit(field), 'border-red-500': userPlan === 'basic' && isAtCharLimit(field) }">
      <textarea
        v-model="modelValue[field.field_key]"
        :placeholder="field.placeholder"
        :required="field.is_required"
        :maxlength="userPlan === 'basic' ? getTextareaMaxLength(field) : undefined"
        rows="8"
        class="w-full px-3 py-2 text-sm resize-none focus:ring-2 focus:ring-blue-500 focus:border-transparent origin-top"
        @input="$emit('update:modelValue', modelValue)"
      ></textarea>
      <div class="bg-gray-50 px-3 py-1 text-xs text-gray-500 border-t flex justify-between items-center"
           :class="{ 'bg-amber-50 text-amber-700': userPlan === 'basic' && isNearCharLimit(field) }">
        <span>Rich text editor (basic). Use formatting in your text.</span>
        <!-- Basic Plan: Show limit and error -->
        <span v-if="userPlan === 'basic' && isAtCharLimit(field)" class="text-red-500 font-bold">About Me cannot exceed 300 characters</span>
        <span v-else-if="userPlan === 'basic'">
           {{ (modelValue[field.field_key] || '').length }} / {{ getTextareaMaxLength(field) }}
        </span>
        <!-- Business/Premium Plan: Show count only, no limit -->
        <span v-else class="text-gray-500">
           {{ (modelValue[field.field_key] || '').length }} characters
        </span>
      </div>
    </div>

    <!-- Date Picker -->
    <DatePicker
      v-else-if="field.field_type === 'date'"
      v-model="modelValue[field.field_key]"
      :required="field.is_required"
      :min="field.validation_rules?.min"
      :max="field.validation_rules?.max"
      @update:modelValue="$emit('update:modelValue', modelValue)"
    />

    <!-- Select Dropdown -->
    <select
      v-else-if="field.field_type === 'select'"
      v-model="modelValue[field.field_key]"
      :required="field.is_required"
      class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
      @change="$emit('update:modelValue', modelValue)"
    >
      <option value="">{{ field.placeholder || 'Select an option...' }}</option>
      <option
        v-for="option in getSelectOptions(field.field_key)"
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
      <div class="relative">
        <input
          type="checkbox"
          v-model="modelValue[field.field_key]"
          class="sr-only peer"
          @change="$emit('update:modelValue', modelValue)"
        />
        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
      </div>
      <span class="ml-3 text-sm font-medium text-gray-700">
        {{ field.config?.onLabel || 'Enabled' }}
      </span>
    </label>

    <!-- Checkbox -->
    <label
      v-else-if="field.field_type === 'checkbox'"
      class="flex items-center cursor-pointer"
    >
      <input
        type="checkbox"
        v-model="modelValue[field.field_key]"
        class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
        @change="$emit('update:modelValue', modelValue)"
      />
      <span class="ml-2 text-sm text-gray-700">
        {{ field.placeholder || field.label }}
      </span>
    </label>

    <!-- Image Upload -->
    <div v-else-if="field.field_type === 'image'" class="space-y-2">
      <div v-if="modelValue[field.field_key]" class="relative inline-block">
        <img
          :src="modelValue[field.field_key]"
          alt="Uploaded image"
          class="max-w-xs rounded-lg border"
        />
        <button
          @click="modelValue[field.field_key] = ''; $emit('update:modelValue', modelValue)"
          type="button"
          class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center"
        >
          ×
        </button>
      </div>
      <input
        type="file"
        accept="image/*"
        @change="handleImageUpload($event, field.field_key)"
        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg"
      />
    </div>

    <!-- Video Input -->
    <VideoInput
      v-else-if="field.field_type === 'video'"
      v-model="modelValue[field.field_key]"
      :allow-upload="field.config?.allowUpload !== false"
      :max-size="field.config?.maxSize || 50"
      @update:modelValue="$emit('update:modelValue', modelValue)"
    />

    <!-- File Upload -->
    <div v-else-if="field.field_type === 'file'" class="space-y-2">
      <div v-if="modelValue[field.field_key]" class="text-sm text-gray-700 flex items-center gap-2">
        <Icon name="heroicons:document" class="w-4 h-4" />
        {{ modelValue[field.field_key] }}
        <button
          @click="modelValue[field.field_key] = ''; $emit('update:modelValue', modelValue)"
          type="button"
          class="text-red-500 hover:text-red-700"
        >
          Remove
        </button>
      </div>
      <input
        type="file"
        :accept="field.config?.accept || '*/*'"
        @change="handleFileUpload($event, field.field_key)"
        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg"
      />
    </div>

    <!-- Gallery Upload -->
    <GalleryUpload
      v-else-if="field.field_type === 'gallery'"
      v-model="modelValue[field.field_key]"
      :max-items="field.config?.maxItems"
      :allow-videos="field.config?.allowVideos"
      :max-file-size="field.config?.maxFileSize || 10"
      @update:modelValue="$emit('update:modelValue', modelValue)"
    />

    <!-- Repeater (Stats, Services, Team Members, etc.) -->
    <div v-else-if="field.field_type === 'repeater'" class="space-y-3">
      <!-- Add Item Button -->
      <button
        type="button"
        @click="addRepeaterItem(field.field_key)"
        class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800 font-medium"
      >
        <Icon name="heroicons:plus-circle" class="w-5 h-5" />
        Add Item
      </button>
      
      <!-- Items List -->
      <div
        v-for="(item, index) in getRepeaterItems(field.field_key)"
        :key="index"
        class="p-3 bg-gray-50 rounded-lg border border-gray-200 space-y-2 relative"
      >
        <!-- Remove Button -->
        <button
          type="button"
          @click="removeRepeaterItem(field.field_key, index)"
          class="absolute top-2 right-2 text-gray-400 hover:text-red-500"
          title="Remove item"
        >
          <Icon name="heroicons:x-mark" class="w-4 h-4" />
        </button>
        
        <div class="text-xs text-gray-400 font-medium mb-2">Item {{ index + 1 }}</div>
        
        <div
          v-for="subField in getSubFields(field.field_key)"
          :key="subField.key"
          class="flex flex-col"
        >
          <label class="text-xs font-medium text-gray-600 mb-1">
            {{ subField.label }}
          </label>
          <!-- Select type -->
          <select
            v-if="subField.type === 'select'"
            v-model="modelValue[field.field_key][index][subField.key]"
            class="px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500"
            @change="$emit('update:modelValue', modelValue)"
          >
            <option value="">{{ subField.placeholder || 'Select...' }}</option>
            <option v-for="opt in subField.options" :key="opt" :value="opt">{{ opt }}</option>
          </select>
          <!-- Textarea type -->
          <textarea
            v-else-if="subField.type === 'textarea'"
            v-model="modelValue[field.field_key][index][subField.key]"
            :placeholder="subField.placeholder"
            rows="2"
            class="px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500"
            @input="$emit('update:modelValue', modelValue)"
          />
          <!-- Default input -->
          <input
            v-else
            :type="subField.type || 'text'"
            v-model="modelValue[field.field_key][index][subField.key]"
            :placeholder="subField.placeholder"
            :maxlength="subField.maxlength"
            class="px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500"
            @input="$emit('update:modelValue', modelValue)"
          />
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-if="!modelValue[field.field_key] || modelValue[field.field_key].length === 0" class="text-center py-4 text-gray-400 text-sm">
        No items yet. Click "Add Item" to start.
      </div>
    </div>

    <!-- Icon Picker -->
    <IconPicker
      v-else-if="field.field_type === 'icon'"
      v-model="modelValue[field.field_key]"
      @update:modelValue="$emit('update:modelValue', modelValue)"
    />

    <!-- Color Picker -->
    <ColorPicker
      v-else-if="field.field_type === 'color'"
      v-model="modelValue[field.field_key]"
      @update:modelValue="$emit('update:modelValue', modelValue)"
    />

    <!-- Tags Input -->
    <div v-else-if="field.field_type === 'tags'" class="space-y-2">
      <div class="flex flex-wrap gap-2">
        <span
          v-for="(tag, index) in (modelValue[field.field_key] || [])"
          :key="index"
          class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-800 text-sm rounded-full"
        >
          {{ tag }}
          <button
            type="button"
            @click="removeTag(field.field_key, index)"
            class="text-blue-600 hover:text-blue-800"
          >
            <Icon name="heroicons:x-mark" class="w-3 h-3" />
          </button>
        </span>
      </div>
      <div class="flex gap-2">
        <input
          type="text"
          v-model="newTagInput"
          :placeholder="field.placeholder || 'Type and press Enter to add tag'"
          class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          @keydown.enter.prevent="addTag(field.field_key)"
        />
        <button
          type="button"
          @click="addTag(field.field_key)"
          class="px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Add
        </button>
      </div>
    </div>

    <!-- Fallback for unknown types -->
    <input
      v-else
      type="text"
      v-model="modelValue[field.field_key]"
      :placeholder="field.placeholder"
      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
      @input="$emit('update:modelValue', modelValue)"
    />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import DatePicker from './DatePicker.vue';
import ColorPicker from './ColorPicker.vue';
import VideoInput from './VideoInput.vue';
import GalleryUpload from './GalleryUpload.vue';
import IconPicker from './IconPicker.vue';

const props = defineProps({
  field: {
    type: Object,
    required: true,
  },
  modelValue: {
    type: Object,
    required: true,
  },
  userPlan: {
    type: String,
    default: 'basic',
  },
  nfcCardId: {
    type: [String, Number],
    default: null
  }
});

const emit = defineEmits(['update:modelValue']);

const { $api } = useNuxtApp();
const uploading = ref(false);

// Handle image upload - actually uploads to server
const handleImageUpload = async (event, fieldKey) => {
  const file = event.target.files[0];
  if (!file) return;
  
  // Validate file size (max 10MB)
  if (file.size > 10 * 1024 * 1024) {
    alert('File size exceeds 10MB limit');
    return;
  }
  
  try {
    uploading.value = true;
    
    const formData = new FormData();
    formData.append('image', file);
    
    // Determine upload endpoint based on field key
    let endpoint = '/upload/gallery-image'; // default
    if (fieldKey.includes('profile') || fieldKey.includes('avatar')) {
      endpoint = '/upload/profile-image';
    } else if (fieldKey.includes('logo') || fieldKey.includes('company')) {
      endpoint = '/upload/company-logo';
    } else if (fieldKey.includes('cover') || fieldKey.includes('banner')) {
      endpoint = '/upload/cover-banner';
    } else if (fieldKey.includes('service')) {
      endpoint = '/upload/service-image';
    } else if (fieldKey.includes('portfolio') || fieldKey.includes('project')) {
      endpoint = '/upload/portfolio-image';
    } else if (fieldKey.includes('blog')) {
      endpoint = '/upload/blog-image';
    }
    
    const response = await $api.post(endpoint, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    const imageUrl = response.data?.data?.url || response.data?.url;
    if (imageUrl) {
      props.modelValue[fieldKey] = imageUrl;
      emit('update:modelValue', props.modelValue);
    }
  } catch (error) {
    console.error('Image upload failed:', error);
    // Fallback to local preview
    const imageUrl = URL.createObjectURL(file);
    props.modelValue[fieldKey] = imageUrl;
    emit('update:modelValue', props.modelValue);
    alert('Upload failed, showing local preview only. Save may not persist this image.');
  } finally {
    uploading.value = false;
    event.target.value = '';
  }
};

// Handle file upload - actually uploads to server
const handleFileUpload = async (event, fieldKey) => {
  const file = event.target.files[0];
  if (!file) return;
  
  // Validate file size (max 20MB)
  if (file.size > 20 * 1024 * 1024) {
    alert('File size exceeds 20MB limit');
    return;
  }
  
  try {
    uploading.value = true;
    
    const formData = new FormData();
    formData.append('file', file);
    
    const response = await $api.post('/upload/file', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    
    const fileUrl = response.data?.data?.url || response.data?.url;
    if (fileUrl) {
      props.modelValue[fieldKey] = fileUrl;
      emit('update:modelValue', props.modelValue);
    }
  } catch (error) {
    console.error('File upload failed:', error);
    // Fallback to filename
    props.modelValue[fieldKey] = file.name;
    emit('update:modelValue', props.modelValue);
    alert('Upload failed. File reference saved but may not be accessible.');
  } finally {
    uploading.value = false;
    event.target.value = '';
  }
};

// Check if field should be visible based on plan and visibility settings
const shouldShowField = computed(() => {
  if (!props.field.is_visible) return false;
  
  // If no plan restrictions, show to all
  if (!props.field.available_plans || props.field.available_plans.length === 0) {
    return true;
  }
  
  // Check if user's plan is in available plans
  return props.field.available_plans.includes(props.userPlan);
});

// Textarea character limit helpers
const getTextareaMaxLength = (field) => {
  const fieldKey = field.field_key?.toLowerCase() || '';
  const isBioField = fieldKey.includes('bio') || fieldKey.includes('biography') || fieldKey.includes('about');
  
  // Bio/About fields: 300 char limit for Basic plan only, unlimited for others
  if (isBioField) {
    if (props.userPlan === 'basic') {
      return 300;
    }
    // Business/Premium plans have no limit - return a very high number
    return 10000;
  }

  // Otherwise, use backend max if provided
  if (field.validation_rules?.max) {
    return field.validation_rules.max;
  }
  
  // Default fallback
  return 500;
};

const isNearCharLimit = (field) => {
  const currentLength = (props.modelValue[field.field_key] || '').length;
  const maxLength = getTextareaMaxLength(field);
  return currentLength >= maxLength * 0.9; // 90% threshold
};

const isAtCharLimit = (field) => {
  const currentLength = (props.modelValue[field.field_key] || '').length;
  const maxLength = getTextareaMaxLength(field);
  return currentLength >= maxLength;
};

// Tags input handling
const newTagInput = ref('');

const addTag = (fieldKey) => {
  const tag = newTagInput.value.trim();
  if (!tag) return;
  
  if (!props.modelValue[fieldKey]) {
    props.modelValue[fieldKey] = [];
  }
  
  // Avoid duplicates
  if (!props.modelValue[fieldKey].includes(tag)) {
    props.modelValue[fieldKey].push(tag);
    emit('update:modelValue', props.modelValue);
  }
  
  newTagInput.value = '';
};

const removeTag = (fieldKey, index) => {
  if (props.modelValue[fieldKey]) {
    props.modelValue[fieldKey].splice(index, 1);
    emit('update:modelValue', props.modelValue);
  }
};

// Default sub_fields for common repeater types
const defaultSubFields = {
  education: [
    { key: 'degree', label: 'Degree/Certificate', placeholder: 'e.g. Bachelor of Science', type: 'text' },
    { key: 'institution', label: 'Institution', placeholder: 'e.g. University Name', type: 'text' },
    { key: 'year', label: 'Year', placeholder: 'e.g. 2020', type: 'text' },
  ],
  certifications: [
    { key: 'name', label: 'Certification Name', placeholder: 'e.g. PMP', type: 'text' },
    { key: 'issuer', label: 'Issuing Organization', placeholder: 'e.g. PMI', type: 'text' },
    { key: 'year', label: 'Year', placeholder: 'e.g. 2022', type: 'text' },
  ],
  profileStats: [
    { key: 'num', label: 'Number/Value', placeholder: 'e.g. 10+', type: 'text' },
    { key: 'label', label: 'Label', placeholder: 'e.g. Years Experience', type: 'text' },
  ],
  expertise: [
    { key: 'name', label: 'Skill Name', placeholder: 'e.g. Project Management', type: 'text' },
    { key: 'level', label: 'Level (%)', placeholder: 'e.g. 90', type: 'number' },
  ],
  workingHours: [
    { key: 'day', label: 'Day', placeholder: 'e.g. Monday', type: 'text' },
    { key: 'hours', label: 'Hours', placeholder: 'e.g. 9:00 AM - 5:00 PM', type: 'text' },
  ],
  awards: [
    { key: 'title', label: 'Award Title', placeholder: 'e.g. Best Employee', type: 'text' },
    { key: 'issuer', label: 'Issuer', placeholder: 'e.g. Company Name', type: 'text' },
    { key: 'year', label: 'Year', placeholder: 'e.g. 2023', type: 'text' },
  ],
  teamMembers: [
    { key: 'name', label: 'Name', placeholder: 'e.g. John Doe', type: 'text' },
    { key: 'role', label: 'Role', placeholder: 'e.g. Designer', type: 'text' },
    { key: 'initials', label: 'Initials', placeholder: 'e.g. JD', type: 'text', maxlength: 3 },
  ],
  services: [
    { key: 'icon', label: 'Icon (emoji)', placeholder: 'e.g. 🚀', type: 'text', maxlength: 4 },
    { key: 'name', label: 'Service Name', placeholder: 'e.g. Web Development', type: 'text' },
    { key: 'category', label: 'Category', placeholder: 'e.g. Development', type: 'select', 
      options: ['Consulting', 'Design', 'Development', 'Marketing', 'Photography', 'Video Production', 'Writing', 'Training', 'Coaching', 'Repairs', 'Cleaning', 'Beauty', 'Health', 'Legal', 'Financial', 'Other'] },
    { key: 'description', label: 'Description', placeholder: 'Brief description...', type: 'textarea' },
    { key: 'price', label: 'Price', placeholder: 'e.g. $99', type: 'text' },
  ],
  serviceFeatures: [
    { key: 'feature', label: 'Feature', placeholder: 'e.g. 24/7 Support', type: 'text' },
  ],
  serviceTags: [
    { key: 'tag', label: 'Tag', placeholder: 'e.g. Design', type: 'text' },
  ],
  socialLinks: [
    { key: 'platform', label: 'Platform', placeholder: 'e.g. LinkedIn', type: 'text' },
    { key: 'url', label: 'URL', placeholder: 'e.g. https://linkedin.com/...', type: 'url' },
  ],
  portfolioTags: [
    { key: 'tag', label: 'Tag', placeholder: 'e.g. Photography', type: 'text' },
  ],
  skillsUsed: [
    { key: 'skill', label: 'Skill/Tool', placeholder: 'e.g. Photoshop', type: 'text' },
  ],
  blogTags: [
    { key: 'tag', label: 'Tag', placeholder: 'e.g. Technology', type: 'text' },
  ],
};

// Default options for select fields
const defaultSelectOptions = {
  pronouns: ['He/Him', 'She/Her', 'They/Them', 'Prefer not to say'],
  industry: [
    'Technology', 'Healthcare', 'Finance', 'Education', 'Real Estate',
    'Manufacturing', 'Retail', 'Food & Beverage', 'Construction', 
    'Transportation', 'Entertainment', 'Legal', 'Marketing', 
    'Consulting', 'Non-Profit', 'Government', 'Other'
  ],
  serviceCategory: [
    'Consulting', 'Design', 'Development', 'Marketing', 'Photography',
    'Video Production', 'Writing', 'Training', 'Coaching', 'Repairs',
    'Cleaning', 'Beauty', 'Health', 'Legal', 'Financial', 'Other'
  ],
  linkType: ['Link', 'Call', 'Email', 'WhatsApp', 'SMS'],
  portfolioCategory: [
    'Web Design', 'Graphic Design', 'Photography', 'Video', 'Branding',
    'UI/UX', 'Illustration', 'Architecture', 'Interior Design', 'Product Design', 'Other'
  ],
  blogCategory: [
    'Technology', 'Business', 'Lifestyle', 'Travel', 'Food', 
    'Health', 'Finance', 'Education', 'Entertainment', 'News', 'Other'
  ],
  relatedPosts: [], // Dynamic, will be empty by default
};

// Get options for select field
const getSelectOptions = (fieldKey) => {
  if (props.field.config?.options && props.field.config.options.length > 0) {
    return props.field.config.options;
  }
  return defaultSelectOptions[fieldKey] || [];
};

// Get sub_fields for a field (use config or defaults)
const getSubFields = (fieldKey) => {
  if (props.field.config?.sub_fields && props.field.config.sub_fields.length > 0) {
    return props.field.config.sub_fields;
  }
  return defaultSubFields[fieldKey] || [{ key: 'value', label: 'Value', placeholder: 'Enter value', type: 'text' }];
};

// Get repeater items (ensure array exists)
const getRepeaterItems = (fieldKey) => {
  const maxItems = props.field.config?.max_items || 10;
  
  if (!props.modelValue[fieldKey]) {
    props.modelValue[fieldKey] = [];
  }
  
  return props.modelValue[fieldKey].slice(0, maxItems);
};

// Add new repeater item
const addRepeaterItem = (fieldKey) => {
  const maxItems = props.field.config?.max_items || 10;
  const subFields = getSubFields(fieldKey);
  
  if (!props.modelValue[fieldKey]) {
    props.modelValue[fieldKey] = [];
  }
  
  if (props.modelValue[fieldKey].length >= maxItems) {
    return; // Max items reached
  }
  
  const newItem = {};
  subFields.forEach(subField => {
    newItem[subField.key] = '';
  });
  props.modelValue[fieldKey].push(newItem);
  emit('update:modelValue', props.modelValue);
};

// Remove repeater item
const removeRepeaterItem = (fieldKey, index) => {
  if (props.modelValue[fieldKey] && props.modelValue[fieldKey].length > 0) {
    props.modelValue[fieldKey].splice(index, 1);
    emit('update:modelValue', props.modelValue);
  }
};
</script>
