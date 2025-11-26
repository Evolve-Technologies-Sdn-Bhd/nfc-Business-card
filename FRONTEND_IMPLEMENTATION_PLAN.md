# Frontend Implementation Plan

## 🎯 Strategy: Update Existing Files

**Decision**: We will update the existing files rather than rewrite them.
- ✅ Preserve existing UI/UX
- ✅ Keep working functionality
- ✅ Add dynamic sections support

---

## 📝 Phase 1: User Profile Builder

### File: `frontend/pages/UserDashboard/UserManagement/BusinessPlanUser/BusinessProfileBuilder.vue`

#### Current State
- ✅ Has main categories (General, Design)
- ✅ Has hardcoded tabs (Profile, Company, Services, Links)
- ✅ Has tab content with forms
- ✅ Has save/preview functionality

#### Changes Needed

1. **Replace Hardcoded Tabs** ✏️
   ```vue
   // OLD: Hardcoded
   const availableGeneralTabs = computed(() => {
     return allTabs.value.filter(tab => tab.category === 'general');
   });
   
   // NEW: Load from API
   const sections = ref([]);
   const loadSections = async () => {
     const response = await $fetch('/api/profile-builder-sections', {
       params: { plan: authStore.user?.subscription_plan }
     });
     sections.value = response.data;
   };
   
   const availableGeneralTabs = computed(() => {
     return sections.value.filter(s => s.category === 'general');
   });
   ```

2. **Dynamic Tab Content** ✏️
   ```vue
   <!-- OLD: Static content for each tab -->
   <div v-if="activeTab === 'profile'">...</div>
   <div v-if="activeTab === 'company'">...</div>
   
   <!-- NEW: Dynamic rendering -->
   <div v-for="section in sections" :key="section.section_key">
     <div v-if="activeTab === section.section_key">
       <DynamicFormField
         v-for="field in section.fields"
         :key="field.id"
         :field="field"
         v-model="profileData[field.field_key]"
       />
     </div>
   </div>
   ```

3. **Update Tab Icons** ✏️
   ```vue
   <!-- Use section.icon from API -->
   <Icon :name="section.icon" class="w-4 h-4" />
   ```

4. **Keep Existing Features** ✅
   - NFC card selector
   - Save button
   - Preview panel
   - Validation
   - All existing design options

---

## 📝 Phase 2: Admin Management Page

### File: `frontend/pages/AdminManagement/profile-builder-design.vue`

#### Current State
- ✅ Has plan accordions (Basic, Premium, Business)
- ✅ Has hardcoded General/Design sections
- ✅ Has checkbox selection for fields
- ✅ Has "Apply Designs" management section

#### Changes Needed

1. **Load Available Sections** ✏️
   ```vue
   const availableSections = ref([]);
   const loadSections = async () => {
     const response = await $fetch('/api/admin/profile-builder/sections');
     availableSections.value = response.data.sections;
   };
   ```

2. **Dynamic General Tabs** ✏️
   ```vue
   // OLD: Hardcoded
   const generalTabs = [
     { id: 'field_profile', name: 'Profile', ... },
     { id: 'field_company', name: 'Company', ... },
   ];
   
   // NEW: From API
   const generalTabs = computed(() => {
     return availableSections.value
       .filter(s => s.category === 'general')
       .map(s => ({
         id: `field_${s.key}`,
         name: s.name,
         icon: s.icon,
         category: s.category
       }));
   });
   ```

3. **Add Section Management** ✨ NEW
   ```vue
   <div class="mb-6">
     <div class="flex items-center justify-between">
       <h2>Manage Sections</h2>
       <button @click="showAddSectionModal = true">
         + Add New Section
       </button>
     </div>
   </div>
   
   <!-- Section List -->
   <div class="grid grid-cols-3 gap-4">
     <div v-for="section in availableSections" :key="section.key">
       <div class="card">
         <Icon :name="section.icon" />
         <h3>{{ section.name }}</h3>
         <p class="text-xs">{{ section.description }}</p>
         <div class="badge">{{ section.has_fields ? 'Has Fields' : 'Empty' }}</div>
       </div>
     </div>
   </div>
   ```

4. **Keep Existing Features** ✅
   - Plan accordions
   - Field checkbox selection
   - Design options management
   - Save functionality

---

## 🔧 Components to Create/Update

### 1. DynamicFormField.vue (Update Existing)
Location: `frontend/components/DynamicFormField.vue` (might exist already)

```vue
<template>
  <div class="form-field">
    <!-- Text inputs -->
    <input v-if="['text', 'email', 'tel', 'url', 'number'].includes(field.field_type)"
           :type="field.field_type"
           v-model="modelValue"
           :placeholder="field.placeholder"
           :required="field.is_required" />
    
    <!-- Textarea -->
    <textarea v-else-if="field.field_type === 'textarea'"
              v-model="modelValue"
              :placeholder="field.placeholder" />
    
    <!-- Rich Text -->
    <RichTextEditor v-else-if="field.field_type === 'richtext'"
                    v-model="modelValue" />
    
    <!-- Date -->
    <DatePicker v-else-if="field.field_type === 'date'"
                v-model="modelValue" />
    
    <!-- Select -->
    <select v-else-if="field.field_type === 'select'"
            v-model="modelValue">
      <option v-for="opt in field.config.options" :key="opt">
        {{ opt }}
      </option>
    </select>
    
    <!-- Toggle -->
    <Toggle v-else-if="field.field_type === 'toggle'"
            v-model="modelValue" />
    
    <!-- Image Upload -->
    <ImageUpload v-else-if="field.field_type === 'image'"
                 v-model="modelValue"
                 :nfc-card-id="nfcCardId" />
    
    <!-- Video -->
    <VideoInput v-else-if="field.field_type === 'video'"
                v-model="modelValue" />
    
    <!-- Gallery -->
    <GalleryUpload v-else-if="field.field_type === 'gallery'"
                   v-model="modelValue"
                   :max-items="field.config.maxItems" />
    
    <!-- Repeater -->
    <RepeaterField v-else-if="field.field_type === 'repeater'"
                   v-model="modelValue"
                   :config="field.config"
                   :nfc-card-id="nfcCardId" />
  </div>
</template>

<script setup>
const props = defineProps({
  field: Object,
  modelValue: [String, Number, Boolean, Array, Object],
  nfcCardId: [String, Number]
});

const emit = defineEmits(['update:modelValue']);
</script>
```

### 2. New Helper Components

**RichTextEditor.vue** - For richtext fields
**VideoInput.vue** - For video URL/upload
**GalleryUpload.vue** - For multiple images/videos
**DatePicker.vue** - For date selection
**IconPicker.vue** - For icon selection
**ColorPicker.vue** - For color selection

---

## 📋 Implementation Steps

### Step 1: User Page Updates ⏳
1. Add `loadSections()` method to fetch from API
2. Replace hardcoded `allTabs` with dynamic `sections`
3. Update `availableGeneralTabs` and `availableDesignTabs` computed
4. Update template to use `section.section_key`, `section.icon`, etc.
5. Keep all existing features working

### Step 2: DynamicFormField Component ⏳
1. Check if component exists
2. Add support for all 18 field types
3. Handle field.config for each type
4. Proper v-model binding

### Step 3: Admin Page Updates ⏳
1. Add `loadSections()` method
2. Replace hardcoded `generalTabs` with dynamic sections
3. Add section management UI (optional)
4. Update field loading to use tabs from API
5. Keep plan accordion functionality

### Step 4: Testing ⏳
1. Test user page loads sections correctly
2. Test fields render for each section
3. Test save functionality
4. Test admin page shows all sections
5. Test field availability per plan

---

## 🎯 Backward Compatibility

### Existing Data
- ✅ Existing 4 tabs (profile, company, services, links) will still work
- ✅ Existing fields in database will be displayed
- ✅ No data migration needed

### New Features
- ✨ Portfolio section available for Premium/Business
- ✨ Blog section available for Business only
- ✨ Future sections can be added via API

---

## 📊 Timeline

| Phase | Est. Time | Status |
|-------|-----------|--------|
| User Page Update | 2 hours | ⏳ Pending |
| DynamicFormField | 1 hour | ⏳ Pending |
| Admin Page Update | 2 hours | ⏳ Pending |
| Testing | 1 hour | ⏳ Pending |
| **Total** | **6 hours** | |

---

## ✅ Success Criteria

1. **User Frontend**
   - [ ] Loads sections from API
   - [ ] Shows sections based on user's plan
   - [ ] Renders Portfolio and Blog sections
   - [ ] All field types work correctly
   - [ ] Save functionality works

2. **Admin Frontend**
   - [ ] Shows all available sections
   - [ ] Can manage field availability per plan
   - [ ] Section list displays correctly
   - [ ] Can add/edit/delete fields

3. **Compatibility**
   - [ ] Existing features still work
   - [ ] No breaking changes
   - [ ] Smooth transition

---

Last Updated: November 25, 2025
