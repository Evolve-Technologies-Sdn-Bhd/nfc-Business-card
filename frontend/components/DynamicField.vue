<template>
  <div v-if="hasValue" :class="['dynamic-field', `field-type-${field.field_type}`, `field-${field.field_key}`]">
    <!-- Text Fields (name, position, qualification, etc.) -->
    <div v-if="isTextField" class="field-text">
      <label v-if="showLabel && !isHeroField" class="field-label">{{ field.label }}</label>
      <div :class="['field-value text-content', getTextClass]">{{ value }}</div>
    </div>

    <!-- Image Field -->
    <div v-else-if="field.field_type === 'image'" class="field-image">
      <label v-if="showLabel" class="field-label">{{ field.label }}</label>
      <img
        :src="value"
        :alt="field.label"
        class="field-image-content"
        @error="handleImageError"
      />
    </div>

    <!-- Rich Text / Textarea -->
    <div v-else-if="field.field_type === 'richtext' || field.field_type === 'textarea'" class="field-richtext">
      <label v-if="showLabel" class="field-label">{{ field.label }}</label>
      <div class="field-value rich-content" v-html="sanitizedValue"></div>
    </div>

    <!-- URL / Link -->
    <div v-else-if="field.field_type === 'url'" class="field-url">
      <label v-if="showLabel" class="field-label">{{ field.label }}</label>
      <a
        :href="value"
        target="_blank"
        rel="noopener noreferrer"
        class="field-link"
      >
        <Icon name="heroicons:link" class="w-4 h-4 mr-2" />
        {{ getLinkText }}
      </a>
    </div>

    <!-- Email -->
    <div v-else-if="field.field_type === 'email'" class="field-email">
      <label v-if="showLabel" class="field-label">{{ field.label }}</label>
      <a :href="`mailto:${value}`" class="field-link">
        <Icon name="heroicons:envelope" class="w-4 h-4 mr-2" />
        {{ value }}
      </a>
    </div>

    <!-- Phone / Tel -->
    <div v-else-if="field.field_type === 'tel'" class="field-tel">
      <label v-if="showLabel" class="field-label">{{ field.label }}</label>
      <a :href="`tel:${value}`" class="field-link">
        <Icon name="heroicons:phone" class="w-4 h-4 mr-2" />
        {{ value }}
      </a>
    </div>

    <!-- Repeater (Array of items) -->
    <div v-else-if="field.field_type === 'repeater'" class="field-repeater">
      <label v-if="showLabel" class="field-label">{{ field.label }}</label>
      <div class="repeater-grid">
        <div
          v-for="(item, index) in arrayValue"
          :key="index"
          class="repeater-item"
        >
          <slot name="repeater-item" :item="item" :index="index">
            <!-- Default repeater item rendering -->
            <div v-if="typeof item === 'object'" class="repeater-card">
              <div v-for="(val, key) in item" :key="key" class="repeater-field">
                <span class="repeater-field-label">{{ formatKey(key) }}:</span>
                <span class="repeater-field-value">{{ val }}</span>
              </div>
            </div>
            <div v-else class="repeater-simple">
              {{ item }}
            </div>
          </slot>
        </div>
      </div>
    </div>

    <!-- Button -->
    <div v-else-if="field.field_type === 'button'" class="field-button">
      <button
        :class="['action-button', buttonClasses]"
        @click="handleButtonClick"
      >
        <Icon v-if="field.icon" :name="field.icon" class="w-5 h-5 mr-2" />
        {{ field.label || value }}
      </button>
    </div>

    <!-- Select / Dropdown -->
    <div v-else-if="field.field_type === 'select'" class="field-select">
      <label v-if="showLabel" class="field-label">{{ field.label }}</label>
      <div class="field-value">{{ getSelectLabel }}</div>
    </div>

    <!-- File -->
    <div v-else-if="field.field_type === 'file'" class="field-file">
      <label v-if="showLabel" class="field-label">{{ field.label }}</label>
      <a
        :href="value"
        target="_blank"
        rel="noopener noreferrer"
        class="field-link file-link"
      >
        <Icon name="heroicons:document" class="w-4 h-4 mr-2" />
        Download {{ field.label }}
      </a>
    </div>

    <!-- Default fallback -->
    <div v-else class="field-default">
      <label v-if="showLabel" class="field-label">{{ field.label }}</label>
      <div class="field-value">{{ value }}</div>
    </div>
  </div>
</template>

<script setup>

const props = defineProps({
  field: {
    type: Object,
    required: true
  },
  value: {
    type: [String, Number, Boolean, Array, Object],
    default: null
  },
  designSettings: {
    type: Object,
    default: () => ({})
  },
  showLabel: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['button-click']);

// Computed: Check if field has a value
const hasValue = computed(() => {
  if (props.value === null || props.value === undefined) return false;
  if (typeof props.value === 'string' && props.value.trim() === '') return false;
  if (Array.isArray(props.value) && props.value.length === 0) return false;
  return true;
});

// Computed: Check if it's a text field
const isTextField = computed(() => {
  return ['text', 'number', 'date', 'time', 'datetime'].includes(props.field.field_type);
});

// Computed: Check if it's a hero field (name, position, etc.)
const isHeroField = computed(() => {
  const heroFields = ['name', 'position', 'qualification', 'pronouns', 'tagline'];
  return heroFields.includes(props.field.field_key);
});

// Computed: Get text class based on field key
const getTextClass = computed(() => {
  const classMap = {
    name: 'text-hero-name',
    position: 'text-hero-position',
    qualification: 'text-badge',
    pronouns: 'text-badge',
    tagline: 'text-tagline',
    bio: 'text-bio',
  };
  return classMap[props.field.field_key] || '';
});

// Computed: Sanitized HTML for rich text
const sanitizedValue = computed(() => {
  if (typeof props.value === 'string') {
    // Basic HTML sanitization - remove script tags and dangerous attributes
    return props.value
      .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
      .replace(/on\w+\s*=\s*["'][^"']*["']/gi, '')
      .replace(/javascript:/gi, '');
  }
  return '';
});

// Computed: Array value for repeater
const arrayValue = computed(() => {
  if (Array.isArray(props.value)) {
    return props.value;
  }
  return [];
});

// Computed: Link text for URL fields
const getLinkText = computed(() => {
  if (props.field.linkText) return props.field.linkText;
  try {
    const url = new URL(props.value);
    return url.hostname;
  } catch {
    return props.value;
  }
});

// Computed: Select label
const getSelectLabel = computed(() => {
  if (props.field.options && Array.isArray(props.field.options)) {
    const option = props.field.options.find(opt => opt.value === props.value);
    return option ? option.label : props.value;
  }
  return props.value;
});

// Computed: Button classes
const buttonClasses = computed(() => {
  const classes = ['btn'];
  const style = props.designSettings.buttonStyle || 'solid';
  classes.push(`btn-${style}`);
  return classes.join(' ');
});

// Format key for repeater fields
const formatKey = (key) => {
  return key
    .replace(/_/g, ' ')
    .replace(/([A-Z])/g, ' $1')
    .replace(/^./, str => str.toUpperCase())
    .trim();
};

// Handle image error
const handleImageError = (event) => {
  event.target.style.display = 'none';
};

// Handle button click
const handleButtonClick = () => {
  emit('button-click', props.field);
  
  // If field has an action URL
  if (props.field.action_url) {
    window.open(props.field.action_url, '_blank');
  }
};
</script>

<style scoped>
.dynamic-field {
  @apply transition-all duration-200;
}

.field-label {
  @apply block text-sm font-medium mb-2 opacity-70;
}

.field-value {
  @apply text-base;
}

/* Text content */
.text-content {
  @apply leading-relaxed;
}

/* Rich content */
.rich-content {
  @apply prose prose-invert max-w-none;
}

.rich-content :deep(p) {
  @apply mb-4;
}

.rich-content :deep(h1),
.rich-content :deep(h2),
.rich-content :deep(h3) {
  @apply font-bold mb-3 mt-6;
}

.rich-content :deep(ul),
.rich-content :deep(ol) {
  @apply ml-6 mb-4;
}

/* Image */
.field-image-content {
  @apply w-full h-auto rounded-lg shadow-lg max-w-md mx-auto;
}

/* Links */
.field-link {
  @apply inline-flex items-center text-primary-400 hover:text-primary-300 transition-colors duration-200 underline-offset-4 hover:underline;
}

.file-link {
  @apply px-4 py-2 bg-white/10 rounded-lg hover:bg-white/20;
}

/* Repeater */
.repeater-grid {
  @apply grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4;
}

.repeater-item {
  @apply transition-transform duration-200 hover:scale-105;
}

.repeater-card {
  @apply bg-white/5 rounded-lg p-4 border border-white/10;
}

.repeater-field {
  @apply mb-2 last:mb-0;
}

.repeater-field-label {
  @apply text-sm font-medium opacity-70 mr-2;
}

.repeater-field-value {
  @apply text-base;
}

.repeater-simple {
  @apply bg-white/5 rounded-lg p-3 text-center;
}

/* Button */
.action-button {
  @apply inline-flex items-center justify-center px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 active:scale-95;
}

.btn-solid {
  @apply bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow-lg hover:shadow-xl;
}

.btn-outline {
  @apply border-2 border-primary-500 text-primary-400 hover:bg-primary-500 hover:text-white;
}

.btn-soft {
  @apply bg-primary-500/20 text-primary-300 hover:bg-primary-500/30;
}

.btn-shadow {
  @apply bg-white/10 backdrop-blur-sm text-white shadow-2xl hover:bg-white/20;
}

/* ========== PROFILE BUILDER FIELD STYLES ========== */

/* Hero Section Fields */
.text-hero-name {
  @apply text-3xl md:text-4xl lg:text-5xl font-extrabold text-white tracking-wide text-center;
}

.text-hero-position {
  @apply text-lg md:text-xl font-semibold text-white/80 text-center;
}

.text-badge {
  @apply inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold 
         backdrop-blur-sm border border-white/20 text-white;
  background: linear-gradient(135deg, rgba(102, 126, 234, 0.3), rgba(118, 75, 162, 0.3));
}

.text-tagline {
  @apply text-base md:text-lg italic text-white/80 text-center mt-4;
}

.text-bio {
  @apply text-base leading-relaxed text-white/85;
}

/* Stats Field */
.field-stats .repeater-grid {
  @apply grid-cols-2 md:grid-cols-3 gap-4;
}

.field-stats .repeater-card {
  @apply text-center p-5 rounded-xl bg-white/5 border border-white/10;
}

/* Services Field */
.field-services .repeater-grid {
  @apply grid-cols-2 md:grid-cols-3 gap-4;
}

.field-services .repeater-card {
  @apply text-center p-6 rounded-xl bg-white/5 border border-white/10 
         transition-all duration-300 hover:transform hover:-translate-y-2 
         hover:shadow-lg cursor-pointer;
}

/* Social Links Field */
.field-socialLinks .repeater-grid {
  @apply grid-cols-2 md:grid-cols-4 gap-3;
}

.field-socialLinks .repeater-card {
  @apply flex items-center justify-center gap-2 p-4 rounded-xl bg-white/5 
         border border-white/10 transition-all duration-300 hover:bg-white/10;
}

/* Team Members Field */
.field-teamMembers .repeater-grid {
  @apply grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4;
}

.field-teamMembers .repeater-card {
  @apply text-center p-6 rounded-xl bg-white/5 border border-white/10;
}

/* Responsive */
@media (max-width: 768px) {
  .repeater-grid {
    @apply grid-cols-1;
  }
  
  .field-image-content {
    @apply max-w-full;
  }
  
  .text-hero-name {
    @apply text-2xl;
  }
  
  .text-hero-position {
    @apply text-base;
  }
}
</style>
