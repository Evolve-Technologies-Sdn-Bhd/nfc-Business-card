<template>
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
    <!-- Header -->
    <div class="flex items-center justify-between px-5 py-4 bg-gradient-to-r from-slate-50 to-gray-50 border-b border-gray-100">
      <div>
        <h3 class="text-base font-bold text-gray-900">Layout Designer</h3>
        <p class="text-xs text-gray-500 mt-0.5">Drag to reorder • Toggle to show/hide</p>
      </div>
      <button 
        @click="resetToDefault" 
        class="p-2 text-gray-400 hover:text-gray-600 hover:bg-white rounded-lg transition-all"
        title="Reset to default"
      >
        <Icon name="heroicons:arrow-path" class="w-4 h-4" />
      </button>
    </div>

    <!-- Section List -->
    <div class="p-3">
      <draggable
        v-model="localSections"
        :disabled="!canCustomizeLayout"
        @change="onSectionOrderChange"
        item-key="id"
        handle=".drag-handle"
        ghost-class="opacity-50"
        animation="200"
        class="space-y-2"
      >
        <template #item="{ element: section, index }">
          <div
            :class="[
              'flex items-center gap-3 p-3 rounded-xl border transition-all cursor-pointer',
              section.enabled 
                ? selectedSection === section.id 
                  ? 'bg-blue-50 border-blue-300 shadow-sm' 
                  : 'bg-gray-50 border-gray-200 hover:border-gray-300 hover:bg-gray-100'
                : 'bg-gray-50/50 border-gray-100 opacity-50'
            ]"
            @click="selectSection(section.id)"
          >
            <!-- Drag Handle -->
            <div 
              class="drag-handle p-1 text-gray-400 hover:text-gray-600 cursor-grab active:cursor-grabbing"
              :class="{ 'opacity-50 cursor-not-allowed': !canCustomizeLayout }"
            >
              <Icon name="heroicons:bars-3" class="w-4 h-4" />
            </div>

            <!-- Order Badge -->
            <span class="w-6 h-6 flex items-center justify-center text-xs font-bold rounded-md bg-white border border-gray-200 text-gray-500">
              {{ index + 1 }}
            </span>

            <!-- Section Icon -->
            <div 
              class="w-9 h-9 rounded-lg flex items-center justify-center text-lg flex-shrink-0"
              :style="{ background: getSectionColor(section.id) }"
            >
              {{ getSectionEmoji(section.id) }}
            </div>

            <!-- Section Info -->
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-gray-900 truncate">{{ section.name }}</p>
              <p class="text-xs text-gray-400 truncate">
                {{ section.fields?.length > 0 ? `${getFieldCount(section.id)}/${section.fields.length} fields` : (section.description || getSectionDescription(section.id)) }}
              </p>
            </div>

            <!-- Toggle -->
            <button
              @click.stop="toggleSection(section.id)"
              :disabled="!canCustomizeLayout || section.id === 'hero'"
              :class="[
                'relative w-10 h-6 rounded-full transition-colors flex-shrink-0',
                section.id === 'hero'
                  ? 'bg-gray-300 opacity-60 cursor-not-allowed'
                  : (section.enabled ? 'bg-blue-500' : 'bg-gray-300'),
                (!canCustomizeLayout || section.id === 'hero') && 'opacity-50 cursor-not-allowed'
              ]"
            >
              <span 
                :class="[
                  'absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-transform',
                  section.id === 'hero' || section.enabled ? 'left-5' : 'left-1'
                ]"
              />
            </button>
          </div>
        </template>
      </draggable>
    </div>

    <!-- Selected Section Fields with Sub-sections -->
    <div v-if="selectedSectionData && selectedSectionData.enabled" class="border-t border-gray-100 bg-gray-50/50 p-3">
      <div class="flex items-center justify-between mb-3 px-1">
        <div class="flex items-center gap-2">
          <div 
            class="w-6 h-6 rounded-md flex items-center justify-center text-sm"
            :style="{ background: getSectionColor(selectedSection) }"
          >
            {{ getSectionEmoji(selectedSection) }}
          </div>
          <span class="text-sm font-semibold text-gray-700">{{ selectedSectionData.name }} Fields</span>
        </div>
        <span class="text-xs text-gray-400 bg-white px-2 py-1 rounded-full border border-gray-200">
          {{ enabledFieldsCount }}/{{ selectedSectionFields.length }}
        </span>
      </div>

      <!-- Fields grouped by sub-section (flat display, no expand/collapse) -->
      <div v-if="hasSubSections(selectedSection)" class="space-y-3">
        <div 
          v-for="(subSection, subIdx) in getSubSections(selectedSection)" 
          :key="subSection.id"
        >
          <!-- Sub-section Label -->
          <div class="flex items-center gap-2 mb-2 px-1">
            <div 
              class="w-5 h-5 rounded flex items-center justify-center text-xs"
              :style="{ background: getSectionColor(subSection.id) }"
            >
              {{ getSectionEmoji(subSection.id) }}
            </div>
            <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">{{ subSection.name }}</span>
            <div class="flex-1 h-px bg-gray-200"></div>
            <!-- Sub-section Toggle All -->
            <button
              @click.stop="toggleSubSection(selectedSection, subSection.id)"
              :disabled="!canCustomizeLayout"
              class="text-[10px] px-2 py-0.5 rounded bg-gray-100 text-gray-500 hover:bg-gray-200 transition-colors"
            >
              {{ isSubSectionEnabled(selectedSection, subSection.id) ? 'Hide All' : 'Show All' }}
            </button>
          </div>
          
          <!-- Fields directly displayed -->
          <div class="space-y-1.5">
            <div 
              v-for="(field, fieldIdx) in getSubSectionFields(selectedSection, subSection.id)"
              :key="field.field_key"
              :class="[
                'flex items-center gap-2 px-3 py-2 rounded-lg border bg-white transition-all',
                field.enabled 
                  ? 'border-gray-200 hover:border-gray-300' 
                  : 'border-gray-100 opacity-50'
              ]"
            >
              <span class="w-5 h-5 flex items-center justify-center text-[10px] font-medium rounded bg-gray-100 text-gray-400">
                {{ fieldIdx + 1 }}
              </span>
              <Icon :name="field.icon || getFieldIcon(field.field_type)" class="w-4 h-4 text-gray-400" />
              <span class="flex-1 text-xs text-gray-700 truncate">{{ field.label || field.field_key }}</span>
              <button
                @click.stop="toggleField(selectedSection, field.field_key)"
                :disabled="!canCustomizeLayout"
                :class="[
                  'relative w-8 h-5 rounded-full transition-colors flex-shrink-0',
                  field.enabled ? 'bg-green-500' : 'bg-gray-300',
                  !canCustomizeLayout && 'opacity-50 cursor-not-allowed'
                ]"
              >
                <span 
                  :class="[
                    'absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform',
                    field.enabled ? 'left-3.5' : 'left-0.5'
                  ]"
                />
              </button>
            </div>
            <div v-if="!getSubSectionFields(selectedSection, subSection.id)?.length" class="text-center py-2 text-gray-400 text-xs">
              No fields
            </div>
          </div>
        </div>
      </div>

      <!-- Regular fields (no sub-sections) -->
      <draggable
        v-else-if="selectedSectionFields.length > 0"
        v-model="localFieldLayouts[selectedSection]"
        :disabled="!canCustomizeLayout"
        @change="onFieldOrderChange(selectedSection)"
        item-key="field_key"
        handle=".field-drag"
        ghost-class="opacity-50"
        animation="150"
        class="space-y-1.5"
      >
        <template #item="{ element: field, index: fieldIndex }">
          <div 
            :class="[
              'flex items-center gap-2 px-3 py-2 rounded-lg border bg-white transition-all',
              field.enabled 
                ? 'border-gray-200 hover:border-gray-300' 
                : 'border-gray-100 opacity-50'
            ]"
          >
            <div 
              class="field-drag p-0.5 text-gray-400 cursor-grab active:cursor-grabbing"
              :class="{ 'opacity-50 cursor-not-allowed': !canCustomizeLayout }"
            >
              <Icon name="heroicons:bars-2" class="w-3.5 h-3.5" />
            </div>
            <span class="w-5 h-5 flex items-center justify-center text-[10px] font-medium rounded bg-gray-100 text-gray-400">
              {{ fieldIndex + 1 }}
            </span>
            <Icon :name="field.icon || getFieldIcon(field.field_type)" class="w-4 h-4 text-gray-400" />
            <span class="flex-1 text-xs text-gray-700 truncate">{{ field.label || field.field_key }}</span>
            <button
              @click.stop="toggleField(selectedSection, field.field_key)"
              :disabled="!canCustomizeLayout || selectedSection === 'hero'"
              :class="[
                'relative w-8 h-5 rounded-full transition-colors flex-shrink-0',
                selectedSection === 'hero'
                  ? 'bg-gray-300 opacity-60 cursor-not-allowed'
                  : (field.enabled ? 'bg-green-500' : 'bg-gray-300'),
                (!canCustomizeLayout || selectedSection === 'hero') && 'opacity-50 cursor-not-allowed'
              ]"
            >
              <span 
                :class="[
                  'absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform',
                  selectedSection === 'hero' || field.enabled ? 'left-3.5' : 'left-0.5'
                ]"
              />
            </button>
          </div>
        </template>
      </draggable>

      <div v-else class="text-center py-6 text-gray-400">
        <Icon name="heroicons:inbox" class="w-8 h-8 mx-auto mb-2 opacity-50" />
        <p class="text-xs">No configurable fields</p>
      </div>
    </div>

    <!-- Upgrade Notice -->
    <div v-if="!canCustomizeLayout" class="flex items-center justify-center gap-2 py-3 bg-amber-50 text-amber-700 text-xs font-medium">
      <Icon name="heroicons:lock-closed" class="w-4 h-4" />
      <span>Upgrade to customize layout</span>
    </div>
  </div>
</template>

<script setup>
import draggable from 'vuedraggable';

const props = defineProps({
  sections: { type: Array, default: () => [] },
  sectionLayout: { type: Array, default: () => [] },
  fieldLayout: { type: Object, default: () => ({}) },
  canCustomizeLayout: { type: Boolean, default: true }
});

const emit = defineEmits(['update:sectionLayout', 'update:fieldLayout']);

// State
const selectedSection = ref(null);
const localSections = ref([]);
const localFieldLayouts = ref({});
const expandedSubSections = ref({});
const subSectionEnabled = ref({}); // Track sub-section enabled state

// Sub-section definitions - maps section ID to its sub-sections
// Must match the section IDs and fieldKeys in BusinessProfileBuilder's landingPageSectionsConfig
const subSectionDefs = {
  profileAchievements: [
    { id: 'about', name: 'About Me', fields: ['bio', 'profileStats'] },
    { id: 'education', name: 'Education', fields: ['education', 'certifications'] },
    { id: 'awards', name: 'Awards', fields: ['awards'] }
  ],
  companyTeam: [
    { id: 'company', name: 'Company Info', fields: ['companyLogo', 'companyLogoText', 'companyName', 'companyRegNo', 'companyDescription', 'industry', 'establishedYear', 'employeeCount'] },
    { id: 'video', name: 'Video', fields: ['companyVideo'] },
    { id: 'team', name: 'Team', fields: ['teamMembers'] }
  ]
};

// Check if section has sub-sections
const hasSubSections = (sectionId) => {
  return !!subSectionDefs[sectionId];
};

// Get sub-sections for a section
const getSubSections = (sectionId) => {
  return subSectionDefs[sectionId] || [];
};

// Toggle sub-section expand/collapse
const toggleSubSectionExpand = (sectionId, subSectionId) => {
  const key = `${sectionId}-${subSectionId}`;
  expandedSubSections.value[key] = !expandedSubSections.value[key];
};

// Check if sub-section is enabled
const isSubSectionEnabled = (sectionId, subSectionId) => {
  const key = `${sectionId}-${subSectionId}`;
  // Default to true if not set
  return subSectionEnabled.value[key] !== false;
};

// Toggle sub-section enabled state
const toggleSubSection = (sectionId, subSectionId) => {
  if (!props.canCustomizeLayout) return;
  const key = `${sectionId}-${subSectionId}`;
  const currentState = subSectionEnabled.value[key] !== false;
  subSectionEnabled.value[key] = !currentState;
  
  // Also toggle all fields in this sub-section
  const subSection = subSectionDefs[sectionId]?.find(s => s.id === subSectionId);
  if (subSection) {
    const fields = localFieldLayouts.value[sectionId] || [];
    fields.forEach(field => {
      if (subSection.fields.includes(field.field_key)) {
        field.enabled = !currentState;
      }
    });
  }
  emitChanges();
};

// Get fields for a specific sub-section
const getSubSectionFields = (sectionId, subSectionId) => {
  const subSection = subSectionDefs[sectionId]?.find(s => s.id === subSectionId);
  if (!subSection) return [];
  
  const allFields = localFieldLayouts.value[sectionId] || [];
  return allFields.filter(f => subSection.fields.includes(f.field_key));
};

// Get enabled field count for a sub-section
const getSubSectionEnabledCount = (sectionId, subSectionId) => {
  const fields = getSubSectionFields(sectionId, subSectionId);
  return fields.filter(f => f.enabled).length;
};

// Section metadata with emoji, color, and description
const sectionMeta = {
  hero: { emoji: '👤', color: 'linear-gradient(135deg, #667eea, #764ba2)', description: 'Profile picture, name, badges' },
  // Combined sections
  profileAchievements: { emoji: '✨', color: 'linear-gradient(135deg, #667eea, #764ba2)', description: 'About me, education, awards' },
  companyTeam: { emoji: '🏢', color: 'linear-gradient(135deg, #11998e, #38ef7d)', description: 'Company, video, team' },
  // Sub-section icons
  aboutMe: { emoji: '✨', color: 'linear-gradient(135deg, #667eea, #764ba2)', description: 'Bio and statistics' },
  companyInfo: { emoji: '🏢', color: 'linear-gradient(135deg, #11998e, #38ef7d)', description: 'Company details' },
  // Legacy individual sections (for sub-section icons)
  about: { emoji: '✨', color: 'linear-gradient(135deg, #667eea, #764ba2)', description: 'Bio and statistics' },
  profile: { emoji: '👤', color: 'linear-gradient(135deg, #667eea, #764ba2)', description: 'Personal information' },
  company: { emoji: '🏢', color: 'linear-gradient(135deg, #11998e, #38ef7d)', description: 'Company details and logo' },
  video: { emoji: '🎬', color: 'linear-gradient(135deg, #f093fb, #f5576c)', description: 'Introduction video' },
  team: { emoji: '👥', color: 'linear-gradient(135deg, #667eea, #764ba2)', description: 'Team members' },
  education: { emoji: '🎓', color: 'linear-gradient(135deg, #4facfe, #00f2fe)', description: 'Education and certifications' },
  awards: { emoji: '🏆', color: 'linear-gradient(135deg, #ffd700, #ffed4e)', description: 'Awards and achievements' },
  // Other sections
  services: { emoji: '🚀', color: 'linear-gradient(135deg, #f093fb, #f5576c)', description: 'Services and expertise' },
  portfolio: { emoji: '📁', color: 'linear-gradient(135deg, #667eea, #764ba2)', description: 'Projects and work samples' },
  blog: { emoji: '📝', color: 'linear-gradient(135deg, #f093fb, #f5576c)', description: 'Blog posts and articles' },
  contact: { emoji: '📞', color: 'linear-gradient(135deg, #667eea, #764ba2)', description: 'Phone, email, WhatsApp' },
  location: { emoji: '📍', color: 'linear-gradient(135deg, #11998e, #38ef7d)', description: 'Address and map' },
  social: { emoji: '🌐', color: 'linear-gradient(135deg, #4facfe, #00f2fe)', description: 'Social media links' },
  gallery: { emoji: '🖼️', color: 'linear-gradient(135deg, #667eea, #764ba2)', description: 'Image gallery' },
  links: { emoji: '🔗', color: 'linear-gradient(135deg, #43e97b, #38f9d7)', description: 'Custom links' },
  vcard: { emoji: '💾', color: 'linear-gradient(135deg, #667eea, #764ba2)', description: 'Save contact button' },
};

const getSectionEmoji = (id) => sectionMeta[id]?.emoji || '📄';
const getSectionColor = (id) => sectionMeta[id]?.color || 'linear-gradient(135deg, #667eea, #764ba2)';
const getSectionDescription = (id) => sectionMeta[id]?.description || 'Section content';

// Format field key to human-readable label (e.g., 'companyName' -> 'Company Name')
const formatFieldLabel = (fieldKey) => {
  if (!fieldKey) return '';
  return fieldKey
    .replace(/([A-Z])/g, ' $1') // Add space before capital letters
    .replace(/^./, str => str.toUpperCase()) // Capitalize first letter
    .trim();
};

// Computed
const selectedSectionData = computed(() => 
  localSections.value.find(s => s.id === selectedSection.value)
);

// ...
const selectedSectionFields = computed(() => 
  localFieldLayouts.value[selectedSection.value] || []
);

const enabledFieldsCount = computed(() => 
  selectedSectionFields.value.filter(f => f.enabled).length
);

// Get field count for a section
const getFieldCount = (sectionId) => {
  const fields = localFieldLayouts.value[sectionId] || [];
  return fields.filter(f => f.enabled).length;
};

// Get default sections
const getDefaultSections = () => {
  if (props.sections?.length > 0) {
    return props.sections.map(s => ({
      id: s.id,
      name: s.name,
      enabled: true,
      fields: s.fields || []
    }));
  }
  return [
    { id: 'profile', name: 'Profile', enabled: true, fields: [] },
    { id: 'company', name: 'Company & Team', enabled: true, fields: [] },
    { id: 'services', name: 'Services', enabled: true, fields: [] },
    { id: 'social', name: 'Social Media & Links', enabled: true, fields: [] },
    { id: 'portfolio', name: 'Portfolio', enabled: true, fields: [] },
    { id: 'blog', name: 'Blog', enabled: true, fields: [] },
  ];
};

// Get default field layouts
const getDefaultFieldLayouts = () => {
  const layouts = {};
  const sections = getDefaultSections();
  sections.forEach(section => {
    layouts[section.id] = (section.fields || []).map(field => ({
      field_key: field.field_key,
      label: field.label || field.field_key,
      field_type: field.field_type || 'text',
      enabled: true
    }));
  });
  return layouts;
};

// Initialize from props
const initializeFromProps = () => {
  const defaults = getDefaultSections();
  
  if (props.sectionLayout?.length > 0) {
    // Merge saved layout with current sections (to get fields)
    localSections.value = props.sectionLayout.map(s => {
      const defaultSection = defaults.find(d => d.id === s.id);
      const merged = {
        ...s,
        name: defaultSection?.name || s.name || s.id,
        description: defaultSection?.description || s.description,
        fields: defaultSection?.fields || s.fields || []
      };
      if (merged.id === 'hero') {
        merged.enabled = true;
      } else if (merged.enabled === undefined) {
        merged.enabled = true;
      }
      return merged;
    });
  } else {
    localSections.value = JSON.parse(JSON.stringify(defaults));
    const heroSection = localSections.value.find(s => s.id === 'hero');
    if (heroSection) heroSection.enabled = true;
  }

  // Initialize field layouts from sections or saved fieldLayout
  if (props.fieldLayout && Object.keys(props.fieldLayout).length > 0) {
    // Restore sub-section enabled state
    if (props.fieldLayout._subSections) {
      subSectionEnabled.value = JSON.parse(JSON.stringify(props.fieldLayout._subSections));
    }
    // Copy field layouts (excluding _subSections)
    const layouts = {};
    Object.keys(props.fieldLayout).forEach(key => {
      if (key !== '_subSections') {
        layouts[key] = JSON.parse(JSON.stringify(props.fieldLayout[key]));
      }
    });
    localFieldLayouts.value = layouts;
    // Ensure hero fields are always enabled
    if (localFieldLayouts.value.hero) {
      localFieldLayouts.value.hero = localFieldLayouts.value.hero.map(field => ({
        ...field,
        enabled: true
      }));
    }
  } else {
    // Build field layouts from sections
    const layouts = {};
    localSections.value.forEach(section => {
      layouts[section.id] = (section.fields || []).map(field => {
        // Handle both string fields and object fields
        const fieldKey = typeof field === 'string' ? field : field.field_key;
        const fieldLabel = typeof field === 'string' ? formatFieldLabel(field) : (field.label || field.field_key);
        return {
          field_key: fieldKey,
          label: fieldLabel,
          field_type: typeof field === 'object' ? (field.field_type || 'text') : 'text',
          enabled: true
        };
      });
    });
    localFieldLayouts.value = layouts;
  }

  // Auto-select first section
  if (!selectedSection.value && localSections.value.length > 0) {
    selectedSection.value = localSections.value[0].id;
  }
};

// Watch props
watch(() => props.sections, initializeFromProps, { deep: true });
watch(() => props.sectionLayout, initializeFromProps, { deep: true });
watch(() => props.fieldLayout, initializeFromProps, { deep: true });

onMounted(initializeFromProps);

// Actions
const selectSection = (id) => { selectedSection.value = id; };

const toggleSection = (id) => {
  if (!props.canCustomizeLayout || id === 'hero') return;
  const section = localSections.value.find(s => s.id === id);
  if (section) {
    section.enabled = !section.enabled;
    emitChanges();
  }
};

const toggleField = (sectionId, fieldKey) => {
  if (!props.canCustomizeLayout || sectionId === 'hero') return;
  const fields = localFieldLayouts.value[sectionId];
  const field = fields?.find(f => f.field_key === fieldKey);
  if (field) {
    field.enabled = !field.enabled;
    emitChanges();
  }
};

const onSectionOrderChange = () => emitChanges();
const onFieldOrderChange = () => emitChanges();

const emitChanges = () => {
  emit('update:sectionLayout', localSections.value.map((s, i) => ({
    id: s.id, order: i, enabled: s.enabled,
    subSections: subSectionEnabled.value // Include sub-section enabled states
  })));
  
  const fieldLayout = {};
  Object.keys(localFieldLayouts.value).forEach(sectionId => {
    fieldLayout[sectionId] = localFieldLayouts.value[sectionId].map((f, i) => ({
      field_key: f.field_key, label: f.label, field_type: f.field_type,
      order: i, enabled: f.enabled
    }));
  });
  // Include sub-section enabled state in fieldLayout
  fieldLayout._subSections = subSectionEnabled.value;
  emit('update:fieldLayout', fieldLayout);
};

const resetToDefault = () => {
  if (!props.canCustomizeLayout) return;
  localSections.value = JSON.parse(JSON.stringify(getDefaultSections()));
  localFieldLayouts.value = getDefaultFieldLayouts();
  subSectionEnabled.value = {}; // Reset sub-section enabled state
  expandedSubSections.value = {}; // Reset expanded state
  emitChanges();
};

// Field icon mapping
const getFieldIcon = (type) => {
  const icons = {
    text: 'heroicons:document-text',
    textarea: 'heroicons:document',
    email: 'heroicons:envelope',
    tel: 'heroicons:phone',
    url: 'heroicons:link',
    image: 'heroicons:photo',
    select: 'heroicons:chevron-down',
    repeater: 'heroicons:squares-plus',
    subsection: 'heroicons:square-3-stack-3d',
  };
  return icons[type] || 'heroicons:document';
};
</script>

<style scoped>
/* All styles are now using Tailwind classes */
</style>
