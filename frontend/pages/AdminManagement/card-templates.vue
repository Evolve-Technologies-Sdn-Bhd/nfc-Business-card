<!-- pages/AdminManagement/card-templates.vue -->
<template>
  <div>
    <!-- Header -->
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-secondary-900">Card Templates</h1>
          <p class="mt-2 text-secondary-600">
            Upload and manage NFC card design templates
          </p>
        </div>
        <div class="mt-4 sm:mt-0">
          <button @click="openUploadModal" class="btn btn-primary">
            <Icon name="heroicons:plus" class="h-5 w-5 mr-2" />
            Upload Template
          </button>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2">Status</label>
            <select v-model="currentFilter" class="input">
              <option v-for="filter in filters" :key="filter.value" :value="filter.value">
                {{ filter.label }}
              </option>
            </select>
          </div>
          <!-- Plan Filter -->
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2">Plan</label>
            <select v-model="planFilter" class="input">
              <option value="">All Plans</option>
              <option value="basic">Basic</option>
              <option value="premium">Premium</option>
              <option value="business">Business</option>
            </select>
          </div>
          <!-- Stats Summary -->
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2">Summary</label>
            <div class="flex items-center gap-4 h-[42px]">
              <span class="text-sm">
                <span class="font-semibold text-secondary-900">{{ templates.length }}</span>
                <span class="text-secondary-500"> total</span>
              </span>
              <span class="text-sm text-green-600">
                <Icon name="heroicons:check-circle" class="h-4 w-4 inline" />
                {{ activeCount }}
              </span>
              <span class="text-sm text-yellow-600">
                <Icon name="heroicons:clock" class="h-4 w-4 inline" />
                {{ processingCount }}
              </span>
              <span class="text-sm text-red-600">
                <Icon name="heroicons:x-circle" class="h-4 w-4 inline" />
                {{ failedCount }}
              </span>
            </div>
          </div>
        </div>
        <div class="mt-4 flex justify-between items-center">
          <button @click="clearFilters" class="btn btn-outline btn-sm">
            Clear Filters
          </button>
          <div class="text-sm text-secondary-500">
            {{ filteredTemplates.length }} templates found
          </div>
        </div>
      </div>
    </div>

    <!-- Templates Table/Grid -->
    <div class="card">
      <div class="card-body p-0">
        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center py-12">
          <div class="spinner"></div>
        </div>

        <!-- Templates Grid -->
        <div v-else-if="filteredTemplates.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-6">
          <div
            v-for="template in filteredTemplates"
            :key="template.id"
            class="border border-secondary-200 rounded-lg overflow-hidden group hover:shadow-md transition-shadow"
          >
            <!-- Template Image -->
            <div class="relative aspect-[5/3] bg-secondary-100">
              <img
                v-if="template.front_image_url"
                :src="getImageUrl(template.front_image_url)"
                :alt="template.name"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center">
                <Icon name="heroicons:photo" class="h-12 w-12 text-secondary-300" />
              </div>

              <!-- Status Badge -->
              <div class="absolute top-2 left-2">
                <span :class="getStatusBadgeClass(template.processing_status)">
                  {{ template.processing_status }}
                </span>
              </div>

              <!-- Hidden Badge -->
              <div v-if="template.is_hidden" class="absolute top-2 right-2">
                <span class="px-2 py-1 text-xs font-medium rounded-full bg-secondary-800 text-white">
                  Hidden
                </span>
              </div>

              <!-- Hover Actions -->
              <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                <button
                  @click="openEditModal(template)"
                  class="p-2 rounded-full bg-white text-secondary-700 hover:bg-primary-50"
                  title="Edit"
                >
                  <Icon name="heroicons:pencil" class="h-5 w-5" />
                </button>
                <button
                  @click="toggleVisibility(template)"
                  class="p-2 rounded-full bg-white text-secondary-700 hover:bg-yellow-50"
                  :title="template.is_hidden ? 'Show' : 'Hide'"
                >
                  <Icon :name="template.is_hidden ? 'heroicons:eye' : 'heroicons:eye-slash'" class="h-5 w-5" />
                </button>
                <button
                  v-if="template.processing_status === 'failed'"
                  @click="retryProcessing(template)"
                  class="p-2 rounded-full bg-white text-yellow-600 hover:bg-yellow-50"
                  title="Retry Processing"
                >
                  <Icon name="heroicons:arrow-path" class="h-5 w-5" />
                </button>
                <button
                  @click="confirmDelete(template)"
                  class="p-2 rounded-full bg-white text-red-600 hover:bg-red-50"
                  title="Delete"
                >
                  <Icon name="heroicons:trash" class="h-5 w-5" />
                </button>
              </div>
            </div>

            <!-- Template Info -->
            <div class="p-4">
              <h3 class="font-semibold text-secondary-900 truncate">{{ template.name }}</h3>
              <p class="text-sm text-secondary-500 mt-1 truncate">{{ template.description || 'No description' }}</p>
              
              <!-- Plan Types -->
              <div class="flex flex-wrap gap-1 mt-3">
                <span
                  v-for="plan in template.plan_types || []"
                  :key="plan"
                  :class="getPlanBadgeClass(plan)"
                >
                  {{ plan }}
                </span>
              </div>

              <!-- Error Message -->
              <div v-if="template.processing_status === 'failed' && template.processing_error" class="mt-2">
                <p class="text-xs text-red-600 truncate" :title="template.processing_error">
                  Error: {{ template.processing_error }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <Icon name="heroicons:photo" class="h-16 w-16 text-secondary-300 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-secondary-900 mb-2">No templates found</h3>
          <p class="text-secondary-500 mb-4">
            {{ currentFilter === 'all' && !planFilter ? 'Upload your first card template to get started.' : 'No templates match the current filters.' }}
          </p>
          <button v-if="currentFilter === 'all' && !planFilter" @click="openUploadModal" class="btn btn-primary">
            <Icon name="heroicons:plus" class="h-5 w-5 mr-2" />
            Upload Template
          </button>
        </div>
      </div>
    </div>

    <!-- Upload Modal -->
    <Transition name="modal">
      <div v-if="showUploadModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/50" @click="showUploadModal = false"></div>
          <div class="relative bg-white rounded-2xl shadow-xl max-w-2xl w-full p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-xl font-bold text-secondary-900">Upload New Template</h3>
              <button @click="showUploadModal = false" class="text-secondary-400 hover:text-secondary-600">
                <Icon name="heroicons:x-mark" class="h-6 w-6" />
              </button>
            </div>

            <form @submit.prevent="uploadTemplate" class="space-y-6">
              <!-- Template Name -->
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-2">Template Name *</label>
                <input
                  v-model="uploadForm.name"
                  type="text"
                  placeholder="e.g. Emerald Executive, Navy Signature"
                  class="input"
                  maxlength="80"
                />
              </div>

              <!-- Description -->
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-2">Description</label>
                <textarea
                  v-model="uploadForm.description"
                  rows="2"
                  placeholder="Short description about this template design"
                  class="input resize-none"
                  maxlength="200"
                ></textarea>
              </div>

              <!-- Category & Plan (2-column grid) -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Category -->
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-2">Category</label>
                  <select v-model="uploadForm.category" class="input">
                    <option v-for="cat in categoryOptions" :key="cat.value" :value="cat.value">
                      {{ cat.label }}
                    </option>
                  </select>
                </div>

                <!-- Plan (single select) -->
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-2">Plan *</label>
                  <select v-model="uploadForm.plan" class="input">
                    <option v-for="plan in planOptions" :key="plan.value" :value="plan.value">
                      {{ plan.label }} — {{ plan.description }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- Front Image Upload -->
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-2">Front Design *</label>
                <div
                  class="border-2 border-dashed border-secondary-300 rounded-lg p-6 text-center hover:border-secondary-400 transition-colors cursor-pointer"
                  @click="$refs.frontInput.click()"
                  @dragover.prevent
                  @drop.prevent="handleDrop($event, 'front')"
                >
                  <input
                    ref="frontInput"
                    type="file"
                    accept="image/*"
                    class="hidden"
                    @change="handleFileSelect($event, 'front')"
                  />
                  <div v-if="uploadForm.frontPreview" class="mb-3">
                    <img :src="uploadForm.frontPreview" alt="Front Preview" class="max-h-32 mx-auto rounded" />
                  </div>
                  <Icon v-else name="heroicons:photo" class="h-12 w-12 text-secondary-400 mx-auto mb-2" />
                  <p class="text-sm text-secondary-600">
                    {{ uploadForm.frontFile ? uploadForm.frontFile.name : 'Click or drag to upload front design' }}
                  </p>
                  <p class="text-xs text-secondary-400 mt-1">PNG, JPG up to 10MB. Recommended: 1050x630px (5:3 ratio)</p>
                </div>
              </div>

              <!-- Back Image Upload (Optional) -->
              <div v-if="uploadForm.plan !== 'basic'">
                <label class="block text-sm font-medium text-secondary-700 mb-2">Back Design (Optional)</label>
                <div
                  class="border-2 border-dashed border-secondary-300 rounded-lg p-6 text-center hover:border-secondary-400 transition-colors cursor-pointer"
                  @click="$refs.backInput.click()"
                  @dragover.prevent
                  @drop.prevent="handleDrop($event, 'back')"
                >
                  <input
                    ref="backInput"
                    type="file"
                    accept="image/*"
                    class="hidden"
                    @change="handleFileSelect($event, 'back')"
                  />
                  <div v-if="uploadForm.backPreview" class="mb-3">
                    <img :src="uploadForm.backPreview" alt="Back Preview" class="max-h-32 mx-auto rounded" />
                  </div>
                  <Icon v-else name="heroicons:photo" class="h-12 w-12 text-secondary-400 mx-auto mb-2" />
                  <p class="text-sm text-secondary-600">
                    {{ uploadForm.backFile ? uploadForm.backFile.name : 'Click or drag to upload back design' }}
                  </p>
                </div>
              </div>

              <!-- Cloudinary Upload Info -->
              <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex">
                  <Icon name="heroicons:cloud-arrow-up" class="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" />
                  <div class="ml-3">
                    <p class="text-sm text-green-800">
                      Images will be uploaded to Cloudinary CDN for fast, worldwide delivery. 
                      Your template will be available immediately after upload.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Submit Buttons -->
              <div class="flex justify-end gap-3 pt-4">
                <button type="button" @click="showUploadModal = false" class="btn btn-outline">
                  Cancel
                </button>
                <button
                  type="submit"
                  class="btn btn-primary"
                  :disabled="uploading || _uploadSubmitLock || !isUploadFormValid"
                  :aria-busy="uploading"
                >
                  <div v-if="uploading" class="spinner mr-2"></div>
                  {{ uploading ? 'Uploading...' : 'Upload & Process' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Edit Modal -->
    <Transition name="modal">
      <div v-if="showEditModal && editForm" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>
          <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-xl font-bold text-secondary-900">Edit Template</h3>
              <button @click="showEditModal = false" class="text-secondary-400 hover:text-secondary-600">
                <Icon name="heroicons:x-mark" class="h-6 w-6" />
              </button>
            </div>

            <form @submit.prevent="saveEdit" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-2">Template Name *</label>
                <input
                  v-model="editForm.name"
                  type="text"
                  class="input"
                  maxlength="80"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-2">Description</label>
                <textarea
                  v-model="editForm.description"
                  rows="2"
                  class="input resize-none"
                  maxlength="200"
                ></textarea>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-2">Category</label>
                  <select v-model="editForm.category" class="input">
                    <option v-for="cat in categoryOptions" :key="cat.value" :value="cat.value">
                      {{ cat.label }}
                    </option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-2">Sort Order</label>
                  <input
                    v-model.number="editForm.sort_order"
                    type="number"
                    class="input"
                  />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-2">Plan Types</label>
                <div class="flex flex-wrap gap-3 p-3 border border-secondary-200 rounded-lg bg-secondary-50">
                  <label
                    v-for="plan in planOptions"
                    :key="plan.value"
                    class="flex items-center cursor-pointer"
                  >
                    <input
                      type="checkbox"
                      :value="plan.value"
                      v-model="editForm.plan_types"
                      class="h-4 w-4 text-primary-600 border-secondary-300 rounded"
                    />
                    <span class="ml-2 text-sm text-secondary-700">{{ plan.label }}</span>
                  </label>
                </div>
              </div>

              <!-- Front Image Edit -->
              <div>
                <div class="flex items-center justify-between mb-2">
                  <label class="block text-sm font-medium text-secondary-700">Front Design</label>
                  <button
                    v-if="editFrontFile || editForm._removeFront"
                    type="button"
                    @click="revertFrontImage"
                    class="text-xs text-secondary-500 hover:text-red-600"
                  >
                    Revert to original
                  </button>
                </div>
                <div
                  class="border-2 border-dashed border-secondary-300 rounded-lg p-4 text-center hover:border-secondary-400 transition-colors cursor-pointer"
                  @click="editFrontInput?.click()"
                  @dragover.prevent
                  @drop.prevent="handleEditDrop($event, 'front')"
                >
                  <input
                    ref="editFrontInput"
                    type="file"
                    accept="image/*"
                    class="hidden"
                    @change="handleEditFileSelect($event, 'front')"
                  />
                  <div v-if="editFrontPreview" class="mb-3">
                    <img :src="editFrontPreview" alt="Front Preview" class="max-h-28 mx-auto rounded" />
                  </div>
                  <div v-else-if="editForm.front_image_url && !editForm._removeFront" class="mb-3">
                    <img :src="getImageUrl(editForm.front_image_url)" alt="Current Front" class="max-h-28 mx-auto rounded opacity-70" />
                  </div>
                  <Icon v-else name="heroicons:photo" class="h-10 w-10 text-secondary-400 mx-auto mb-2" />
                  <p class="text-sm text-secondary-600">
                    {{ editFrontFile ? editFrontFile.name : (editForm._removeFront ? '(Image will be removed)' : 'Click or drag to replace front design (leave empty to keep)') }}
                  </p>
                  <p class="text-xs text-secondary-400 mt-1">PNG, JPG up to 10MB</p>
                </div>
              </div>

              <!-- Back Image Edit -->
              <div>
                <div class="flex items-center justify-between mb-2">
                  <label class="block text-sm font-medium text-secondary-700">Back Design</label>
                  <button
                    v-if="editBackFile || editForm._removeBack"
                    type="button"
                    @click="revertBackImage"
                    class="text-xs text-secondary-500 hover:text-red-600"
                  >
                    Revert to original
                  </button>
                </div>
                <div
                  class="border-2 border-dashed border-secondary-300 rounded-lg p-4 text-center hover:border-secondary-400 transition-colors cursor-pointer"
                  @click="editBackInput?.click()"
                  @dragover.prevent
                  @drop.prevent="handleEditDrop($event, 'back')"
                >
                  <input
                    ref="editBackInput"
                    type="file"
                    accept="image/*"
                    class="hidden"
                    @change="handleEditFileSelect($event, 'back')"
                  />
                  <div v-if="editBackPreview" class="mb-3">
                    <img :src="editBackPreview" alt="Back Preview" class="max-h-28 mx-auto rounded" />
                  </div>
                  <div v-else-if="editForm.back_image_url && !editForm._removeBack" class="mb-3">
                    <img :src="getImageUrl(editForm.back_image_url)" alt="Current Back" class="max-h-28 mx-auto rounded opacity-70" />
                  </div>
                  <Icon v-else name="heroicons:photo" class="h-10 w-10 text-secondary-400 mx-auto mb-2" />
                  <p class="text-sm text-secondary-600">
                    {{ editBackFile ? editBackFile.name : (editForm._removeBack ? '(Image will be removed)' : 'Click or drag to replace back design (leave empty to keep)') }}
                  </p>
                </div>
              </div>

              <div class="flex items-center gap-4">
                <label class="flex items-center cursor-pointer">
                  <input type="checkbox" v-model="editForm.is_active" class="h-4 w-4 text-primary-600 border-secondary-300 rounded" />
                  <span class="ml-2 text-sm text-secondary-700">Active</span>
                </label>
                <label class="flex items-center cursor-pointer">
                  <input type="checkbox" v-model="editForm.is_hidden" class="h-4 w-4 text-primary-600 border-secondary-300 rounded" />
                  <span class="ml-2 text-sm text-secondary-700">Hidden</span>
                </label>
              </div>

              <div class="flex justify-end gap-3 pt-4">
                <button type="button" @click="showEditModal = false" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary" :disabled="saving || !isEditFormValid">
                  <div v-if="saving" class="spinner mr-2"></div>
                  {{ saving ? 'Saving...' : 'Save Changes' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Delete Confirmation Modal -->
    <Transition name="modal">
      <div v-if="showDeleteModal && deleteTarget" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/50" @click="showDeleteModal = false"></div>
          <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
            <div class="text-center">
              <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <Icon name="heroicons:exclamation-triangle" class="h-6 w-6 text-red-600" />
              </div>
              <h3 class="text-lg font-medium text-secondary-900 mb-2">Delete Template</h3>
              <p class="text-sm text-secondary-500 mb-6">
                Are you sure you want to delete "{{ deleteTarget.name }}"? This action cannot be undone.
              </p>
              <div class="flex justify-center gap-3">
                <button @click="showDeleteModal = false" class="btn btn-outline">Cancel</button>
                <button @click="deleteTemplate" class="btn bg-red-600 text-white hover:bg-red-700" :disabled="deleting">
                  <div v-if="deleting" class="spinner mr-2"></div>
                  {{ deleting ? 'Deleting...' : 'Delete' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { markRaw, toRaw } from 'vue';

definePageMeta({
  layout: 'admin-management',
  middleware: 'admin',
});

const { $api, $toast } = useNuxtApp();
const config = useRuntimeConfig();

// State
const loading = ref(true);
const templates = ref([]);
const currentFilter = ref('all');
const planFilter = ref('');
const showUploadModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const uploading = ref(false);
const saving = ref(false);
const deleting = ref(false);
const editForm = ref(null);
const deleteTarget = ref(null);

// Upload form
const uploadForm = reactive({
  name: '',
  description: '',
  category: 'business',
  plan: 'business',
  frontFile: null,
  frontPreview: null,
  backFile: null,
  backPreview: null,
});

// ⚠️ PLAIN OBJECT (not reactive) untuk simpan File objects — 100% bypass Vue Proxy wrap
// If we use reactive({ frontFile: File }), Vue 3 deep proxy can wrap File even with markRaw
// → FormData.append("front_image", proxyFile) → [object Object] not binary!
const __uploadFiles = Object.create(null);
__uploadFiles.front = null;
__uploadFiles.back = null;

let _uploadSubmitLock = false;
let _uploadAbortController = null;

const categoryOptions = [
  { value: 'business', label: 'Business' },
  { value: 'personal', label: 'Personal' },
  { value: 'creative', label: 'Creative' },
];

// Filter options
const filters = [
  { label: 'All', value: 'all' },
  { label: 'Active', value: 'active' },
  { label: 'Hidden', value: 'hidden' },
  { label: 'Processing', value: 'processing' },
  { label: 'Failed', value: 'failed' },
];

// Plan options (Free plan doesn't have NFC cards)
const planOptions = [
  { value: 'basic', label: 'Basic', description: 'Standard plan' },
  { value: 'premium', label: 'Premium', description: 'Advanced features' },
  { value: 'business', label: 'Business', description: 'Full access' },
];

// Computed
const filteredTemplates = computed(() => {
  let result = templates.value;
  
  // Status filter
  if (currentFilter.value === 'active') {
    result = result.filter(t => !t.is_hidden && t.processing_status === 'completed');
  } else if (currentFilter.value === 'hidden') {
    result = result.filter(t => t.is_hidden);
  } else if (currentFilter.value === 'processing') {
    result = result.filter(t => t.processing_status === 'processing' || t.processing_status === 'pending');
  } else if (currentFilter.value === 'failed') {
    result = result.filter(t => t.processing_status === 'failed');
  }
  
  // Plan filter
  if (planFilter.value) {
    result = result.filter(t => t.plan_types?.includes(planFilter.value));
  }
  
  return result;
});

// Clear all filters
const clearFilters = () => {
  currentFilter.value = 'all';
  planFilter.value = '';
};

const activeCount = computed(() => templates.value.filter(t => !t.is_hidden && t.processing_status === 'completed').length);
const processingCount = computed(() => templates.value.filter(t => t.processing_status === 'processing' || t.processing_status === 'pending').length);
const failedCount = computed(() => templates.value.filter(t => t.processing_status === 'failed').length);

const isUploadFormValid = computed(() => {
  return uploadForm.name.trim() && uploadForm.plan && uploadForm.frontFile;
});

// Methods
const loadTemplates = async () => {
  loading.value = true;
  try {
    const response = await $api.get('/admin/card-templates');
    if (response.success) {
      templates.value = response.data;
    }
  } catch (error) {
    console.error('Failed to load templates:', error);
    $toast.error('Failed to load templates');
  } finally {
    loading.value = false;
  }
};

// Smart host detection helper (same logic as api.client.js)
const _isLocalHostname = (h) =>
  h === "localhost" ||
  h === "127.0.0.1" ||
  h === "0.0.0.0" ||
  h.startsWith("10.") ||
  h.startsWith("192.168.") ||
  /^172\.(1[6-9]|2[0-9]|3[01])\./.test(h);

const getImageUrl = (url) => {
  if (!url) return '';
  if (url.startsWith('http')) return url;
  // Smartly pick base URL for local dev vs production
  let apiBase = (config.public?.apiBase || config.public?.apiBaseUrl || '').trim();
  if (typeof window !== "undefined" && window.location && window.location.hostname) {
    const host = window.location.hostname;
    if (_isLocalHostname(host)) {
      const scheme = window.location.protocol === "https:" ? "https" : "http";
      apiBase = `${scheme}://${host}:8000/api`;
    }
  }
  if (!apiBase) {
    apiBase = "http://localhost:8000/api";
  }
  return `${apiBase.replace(/\/api\/?$/, '')}${url.startsWith('/') ? url : '/' + url}`;
};

const getStatusBadgeClass = (status) => {
  const classes = {
    completed: 'px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800',
    processing: 'px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800',
    pending: 'px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800',
    failed: 'px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800',
  };
  return classes[status] || classes.pending;
};

const getPlanBadgeClass = (plan) => {
  const classes = {
    free: 'px-2 py-0.5 text-xs font-medium rounded-full bg-secondary-100 text-secondary-700',
    basic: 'px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700',
    premium: 'px-2 py-0.5 text-xs font-medium rounded-full bg-purple-100 text-purple-700',
    business: 'px-2 py-0.5 text-xs font-medium rounded-full bg-primary-100 text-primary-700',
  };
  return classes[plan] || classes.free;
};

const openUploadModal = () => {
  uploadForm.name = '';
  uploadForm.description = '';
  uploadForm.category = 'business';
  uploadForm.plan = 'business';
  uploadForm.frontFile = null;
  uploadForm.frontPreview = null;
  uploadForm.backFile = null;
  uploadForm.backPreview = null;
  // Clear non-reactive File storage too (to avoid accidentally re-uploading old file)
  __uploadFiles.front = null;
  __uploadFiles.back = null;
  showUploadModal.value = true;
};

const handleFileSelect = (event, side) => {
  const file = event.target.files[0];
  if (!file) return;

  if (side === 'front') {
    __uploadFiles.front = file;                         // plain object = NO Vue Proxy wrap
    uploadForm.frontFile = file;
    uploadForm.frontPreview = URL.createObjectURL(file);
  } else {
    __uploadFiles.back = file;
    uploadForm.backFile = file;
    uploadForm.backPreview = URL.createObjectURL(file);
  }
};

const handleDrop = (event, side) => {
  const file = event.dataTransfer.files[0];
  if (!file || !file.type.startsWith('image/')) return;

  if (side === 'front') {
    __uploadFiles.front = file;
    uploadForm.frontFile = file;
    uploadForm.frontPreview = URL.createObjectURL(file);
  } else {
    __uploadFiles.back = file;
    uploadForm.backFile = file;
    uploadForm.backPreview = URL.createObjectURL(file);
  }
};

const uploadTemplate = async () => {
  if (_uploadSubmitLock) return;
  if (!isUploadFormValid.value) return;

  if (_uploadAbortController) {
    try { _uploadAbortController.abort(); } catch (_) {}
    _uploadAbortController = null;
  }
  _uploadSubmitLock = true;
  uploading.value = true;

  try {
    const formData = new FormData();
    const selectedPlan = uploadForm.plan;
    formData.append('name', (uploadForm.name || '').trim() || `${planOptions.find(p => p.value === selectedPlan)?.label || 'Card'} Template`);
    formData.append('description', (uploadForm.description || '').trim());
    formData.append('category', uploadForm.category || 'business');
    formData.append('plan_types[]', selectedPlan);

    // ✅ Use __uploadFiles.front / back directly (native File, no Vue Proxy)
    //    Laravel $request->hasFile('front_image') will return TRUE now
    const frontFile = __uploadFiles.front;
    if (frontFile && frontFile instanceof File && frontFile.size > 0) {
      formData.append('front_image', frontFile, frontFile.name || 'front.png');
    } else if (uploadForm.frontFile instanceof File && uploadForm.frontFile.size > 0) {
      // Fallback (user programmatically set)
      formData.append('front_image', uploadForm.frontFile, uploadForm.frontFile.name || 'front.png');
    }
    const backFile = __uploadFiles.back;
    if (backFile && backFile instanceof File && backFile.size > 0) {
      formData.append('back_image', backFile, backFile.name || 'back.png');
    } else if (uploadForm.backFile instanceof File && uploadForm.backFile.size > 0) {
      formData.append('back_image', uploadForm.backFile, uploadForm.backFile.name || 'back.png');
    }

    if (typeof window !== 'undefined' && window.console && console.debug) {
      const entries = [];
      for (const [k, v] of formData.entries()) {
        if (v instanceof File) {
          entries.push(`  ${k}: File[name=${v.name}, size=${v.size}, type=${v.type}]`);
        } else {
          entries.push(`  ${k}: ${String(v)}`);
        }
      }
      console.debug('[uploadTemplate] FormData contents:\n' + entries.join('\n'));
    }

    _uploadAbortController = new AbortController();
    const response = await $api.post('/admin/card-templates', formData, {
      signal: _uploadAbortController.signal,
    });

    if (response?.success === true) {
      $toast.success(response.message || 'Template uploaded! Processing will begin shortly.');
      showUploadModal.value = false;
      resetUploadForm();
      await loadTemplates();
    } else {
      const msg = response?.message || 'Failed to upload template';
      $toast.error(msg);
      console.warn('[uploadTemplate] backend returned success=false', response);
    }
  } catch (error) {
    if (error?.name === 'CanceledError' || error?.code === 'ERR_CANCELED') return;
    console.error('[uploadTemplate] error:', error);
    const errData = error?.data || error?.response?.data || {};
    const flattenErrors = (errs) => {
      if (!errs || typeof errs !== 'object') return '';
      const out = [];
      for (const val of Object.values(errs)) {
        if (Array.isArray(val)) out.push(...val.map(v => String(v)));
        else if (val && typeof val === 'object') out.push(flattenErrors(val));
        else out.push(String(val));
      }
      return out.filter(Boolean).join('; ');
    };
    const validationMsg = flattenErrors(errData.errors);
    const msg = errData?.message || validationMsg || error?.message || 'Failed to upload template';
    $toast.error(msg);
  } finally {
    uploading.value = false;
    _uploadSubmitLock = false;
    _uploadAbortController = null;
  }
};
const resetUploadForm = () => {
  Object.assign(uploadForm, {
    name: '',
    description: '',
    category: 'business',
    plan: 'basic',
    frontFile: null,
    backFile: null,
    frontPreview: null,
    backPreview: null,
  });
  __uploadFiles.front = null;
  __uploadFiles.back = null;
};

const editFrontFile = ref(null);
const editFrontPreview = ref(null);
const editBackFile = ref(null);
const editBackPreview = ref(null);
const editFrontInput = ref(null);
const editBackInput = ref(null);

const openEditModal = (template) => {
  editForm.value = {
    id: template.id,
    name: template.name,
    description: template.description,
    category: template.category || 'business',
    plan_types: [...(template.plan_types || [])],
    is_active: template.is_active,
    is_hidden: template.is_hidden,
    sort_order: template.sort_order || 0,
    front_image_url: template.front_image_url,
    back_image_url: template.back_image_url,
    thumbnail_url: template.thumbnail_url,
    _removeFront: false,
    _removeBack: false,
  };
  editFrontFile.value = null;
  editFrontPreview.value = null;
  editBackFile.value = null;
  editBackPreview.value = null;
  showEditModal.value = true;
};

const handleEditFileSelect = (event, side) => {
  const file = event.target.files[0];
  if (!file) return;
  if (side === 'front') {
    editFrontFile.value = markRaw(file);
    editFrontPreview.value = URL.createObjectURL(file);
    editForm.value._removeFront = false;
  } else {
    editBackFile.value = markRaw(file);
    editBackPreview.value = URL.createObjectURL(file);
    editForm.value._removeBack = false;
  }
};

const handleEditDrop = (event, side) => {
  const file = event.dataTransfer.files[0];
  if (!file || !file.type.startsWith('image/')) return;
  if (side === 'front') {
    editFrontFile.value = markRaw(file);
    editFrontPreview.value = URL.createObjectURL(file);
    editForm.value._removeFront = false;
  } else {
    editBackFile.value = markRaw(file);
    editBackPreview.value = URL.createObjectURL(file);
    editForm.value._removeBack = false;
  }
};

const revertFrontImage = () => {
  editFrontFile.value = null;
  editFrontPreview.value = null;
  editForm.value._removeFront = false;
};

const revertBackImage = () => {
  editBackFile.value = null;
  editBackPreview.value = null;
  editForm.value._removeBack = false;
};

const isEditFormValid = computed(() => {
  return editForm.value
    && editForm.value.name?.trim()
    && Array.isArray(editForm.value.plan_types)
    && editForm.value.plan_types.length > 0;
});

const saveEdit = async () => {
  if (!editForm.value || !isEditFormValid.value) {
    $toast.error('Template name and at least one plan type are required');
    return;
  }

  saving.value = true;
  try {
    const hasFiles = editFrontFile.value || editBackFile.value;
    let response;

    if (hasFiles) {
      const formData = new FormData();
      formData.append('_method', 'PUT');
      formData.append('name', editForm.value.name.trim());
      formData.append('description', (editForm.value.description || '').trim());
      formData.append('category', editForm.value.category);
      (editForm.value.plan_types || []).forEach((p, i) => {
        formData.append(`plan_types[${i}]`, p);
      });
      formData.append('is_active', editForm.value.is_active ? 1 : 0);
      formData.append('is_hidden', editForm.value.is_hidden ? 1 : 0);
      formData.append('sort_order', editForm.value.sort_order ?? 0);
      if (editFrontFile.value) {
        const rawF = toRaw(editFrontFile.value);
        formData.append('front_image', rawF, rawF.name || 'front.png');
      }
      if (editBackFile.value) {
        const rawB = toRaw(editBackFile.value);
        formData.append('back_image', rawB, rawB.name || 'back.png');
      }
      response = await $api.post(`/admin/card-templates/${editForm.value.id}`, formData);
    } else {
      const payload = {
        name: editForm.value.name.trim(),
        description: (editForm.value.description || '').trim(),
        category: editForm.value.category,
        plan_types: editForm.value.plan_types,
        is_active: editForm.value.is_active,
        is_hidden: editForm.value.is_hidden,
        sort_order: editForm.value.sort_order ?? 0,
      };
      response = await $api.put(`/admin/card-templates/${editForm.value.id}`, payload);
    }

    if (response.success) {
      $toast.success('Template updated');
      showEditModal.value = false;
      await loadTemplates();
    } else {
      $toast.error(response.message || 'Failed to update template');
    }
  } catch (error) {
    console.error('Save error:', error);
    const msg = error?.data?.message || error?.response?.data?.message || error?.message || 'Failed to update template';
    $toast.error(msg);
  } finally {
    saving.value = false;
  }
};

const toggleVisibility = async (template) => {
  try {
    const response = await $api.post(`/admin/card-templates/${template.id}/toggle-visibility`);
    if (response.success) {
      $toast.success(response.message);
      await loadTemplates();
    }
  } catch (error) {
    console.error('Toggle error:', error);
    $toast.error('Failed to toggle visibility');
  }
};

const retryProcessing = async (template) => {
  try {
    const response = await $api.post(`/admin/card-templates/${template.id}/retry`);
    if (response.success) {
      $toast.success('Processing retry initiated');
      await loadTemplates();
    }
  } catch (error) {
    console.error('Retry error:', error);
    $toast.error('Failed to retry processing');
  }
};

const confirmDelete = (template) => {
  deleteTarget.value = template;
  showDeleteModal.value = true;
};

const deleteTemplate = async () => {
  if (!deleteTarget.value) return;

  deleting.value = true;
  try {
    const response = await $api.delete(`/admin/card-templates/${deleteTarget.value.id}`);
    if (response.success) {
      $toast.success('Template deleted');
      showDeleteModal.value = false;
      deleteTarget.value = null;
      await loadTemplates();
    }
  } catch (error) {
    console.error('Delete error:', error);
    $toast.error('Failed to delete template');
  } finally {
    deleting.value = false;
  }
};

// Lifecycle
onMounted(() => {
  loadTemplates();
});
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
