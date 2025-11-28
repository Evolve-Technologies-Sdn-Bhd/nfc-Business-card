<template>
  <div
    v-if="isVisible"
    :class="[
      'dynamic-section',
      `section-${section.id}`,
      `section-type-${section.type || 'default'}`,
      sectionClasses
    ]"
    :style="sectionStyles"
    :data-section="section.id"
  >
    <!-- Section Header (Optional) -->
    <div v-if="showHeader" class="section-header" :style="headerStyles">
      <div class="flex items-center gap-3">
        <div v-if="section.icon" class="section-icon" :style="iconStyles">
          <Icon :name="section.icon" class="w-6 h-6" />
        </div>
        <div>
          <h2 class="section-title" :style="titleStyles">{{ section.name }}</h2>
          <p v-if="section.description" class="section-description" :style="descriptionStyles">
            {{ section.description }}
          </p>
        </div>
      </div>
    </div>

    <!-- Section Content -->
    <div class="section-content" :style="contentStyles">
      <!-- Dynamic Fields Rendering -->
      <div
        v-for="field in visibleFields"
        :key="field.field_key"
        :class="['field-wrapper', `field-${field.field_type}`]"
        :style="getFieldWrapperStyle(field)"
      >
        <DynamicField
          :field="field"
          :value="getFieldValue(field.field_key)"
          :design-settings="designSettings"
        />
      </div>

      <!-- Slot for custom content -->
      <slot />
    </div>
  </div>
</template>

<script setup>
import DynamicField from './DynamicField.vue';

const props = defineProps({
  section: {
    type: Object,
    required: true
  },
  fields: {
    type: Array,
    default: () => []
  },
  fieldLayout: {
    type: Array,
    default: () => []
  },
  profileData: {
    type: Object,
    default: () => ({})
  },
  designSettings: {
    type: Object,
    default: () => ({})
  },
  showHeader: {
    type: Boolean,
    default: true
  },
  containerStyle: {
    type: String,
    default: 'card' // 'card', 'flat', 'bordered'
  }
});

// Computed: Visible fields based on fieldLayout
const visibleFields = computed(() => {
  if (props.fieldLayout && props.fieldLayout.length > 0) {
    // Use custom field layout
    return props.fieldLayout
      .filter(f => f.enabled)
      .sort((a, b) => (a.order ?? 0) - (b.order ?? 0));
  }
  // Use default fields from section
  return props.fields.filter(f => f.enabled !== false);
});

// Computed: Check if section should be visible
const isVisible = computed(() => {
  // Check if section has any visible fields or content
  return visibleFields.value.length > 0 || props.section.alwaysShow;
});

// Get field value from profileData
const getFieldValue = (fieldKey) => {
  // Handle nested keys (e.g., 'company.name')
  const keys = fieldKey.split('.');
  let value = props.profileData;
  
  for (const key of keys) {
    if (value && typeof value === 'object') {
      value = value[key];
    } else {
      return null;
    }
  }
  
  return value;
};

// Section styles based on containerStyle
const sectionStyles = computed(() => {
  const baseStyles = {
    marginBottom: '2rem',
    animation: 'fadeInUp 0.6s ease-out',
  };

  if (props.containerStyle === 'card') {
    return {
      ...baseStyles,
      background: 'rgba(255, 255, 255, 0.05)',
      backdropFilter: 'blur(20px)',
      border: '1px solid rgba(255, 255, 255, 0.1)',
      borderRadius: '1.5rem',
      padding: '2rem',
      boxShadow: '0 20px 60px rgba(0, 0, 0, 0.3)',
    };
  } else if (props.containerStyle === 'bordered') {
    return {
      ...baseStyles,
      border: '2px solid rgba(255, 255, 255, 0.2)',
      borderRadius: '1rem',
      padding: '1.5rem',
    };
  } else {
    // flat style
    return {
      ...baseStyles,
      padding: '1rem 0',
    };
  }
});

const sectionClasses = computed(() => {
  return [
    'transition-all',
    'duration-300',
    props.containerStyle === 'card' ? 'hover:shadow-2xl' : ''
  ].filter(Boolean).join(' ');
});

const headerStyles = computed(() => ({
  marginBottom: '1.5rem',
  paddingBottom: '1rem',
  borderBottom: props.containerStyle !== 'flat' ? '1px solid rgba(255, 255, 255, 0.1)' : 'none',
}));

const iconStyles = computed(() => ({
  width: '3rem',
  height: '3rem',
  borderRadius: '0.75rem',
  background: 'linear-gradient(135deg, #667eea, #764ba2)',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  color: '#fff',
}));

const titleStyles = computed(() => ({
  fontSize: '1.5rem',
  fontWeight: '700',
  color: props.designSettings.textColor || '#fff',
  marginBottom: '0.25rem',
}));

const descriptionStyles = computed(() => ({
  fontSize: '0.875rem',
  color: 'rgba(255, 255, 255, 0.7)',
}));

const contentStyles = computed(() => ({
  display: 'grid',
  gap: '1rem',
}));

const getFieldWrapperStyle = (field) => {
  // Different layouts for different field types
  const styles = {
    display: 'block',
  };

  // Full width for certain field types
  if (['richtext', 'textarea', 'repeater'].includes(field.field_type)) {
    styles.gridColumn = '1 / -1';
  }

  return styles;
};
</script>

<style scoped>
.dynamic-section {
  position: relative;
  overflow: hidden;
}

.section-header {
  position: relative;
  z-index: 2;
}

.section-content {
  position: relative;
  z-index: 2;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive grid */
.section-content {
  grid-template-columns: 1fr;
}

@media (min-width: 768px) {
  .section-content {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .section-content {
    grid-template-columns: repeat(3, 1fr);
  }
}

/* Field type specific styles */
.field-wrapper.field-image {
  grid-column: 1 / -1;
  text-align: center;
}

.field-wrapper.field-richtext,
.field-wrapper.field-textarea,
.field-wrapper.field-repeater {
  grid-column: 1 / -1;
}
</style>
