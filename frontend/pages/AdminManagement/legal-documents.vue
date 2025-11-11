<!-- pages/AdminManagement/legal-documents.vue -->
<template>
  <AdminManagement>
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-secondary-900">
            Legal Documents Management
          </h1>
          <p class="mt-1 text-sm text-secondary-600">
            Manage Terms of Service and Privacy Policy
          </p>
        </div>
      </div>

      <!-- Document Tabs -->
      <div class="bg-white rounded-lg shadow">
        <div class="border-b border-secondary-200">
          <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
            <button
              @click="activeTab = 'terms'"
              :class="[
                activeTab === 'terms'
                  ? 'border-primary-500 text-primary-600'
                  : 'border-transparent text-secondary-500 hover:text-secondary-700 hover:border-secondary-300',
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
              ]"
            >
              Terms of Service
            </button>
            <button
              @click="activeTab = 'privacy'"
              :class="[
                activeTab === 'privacy'
                  ? 'border-primary-500 text-primary-600'
                  : 'border-transparent text-secondary-500 hover:text-secondary-700 hover:border-secondary-300',
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
              ]"
            >
              Privacy Policy
            </button>
          </nav>
        </div>

        <!-- Terms of Service Tab -->
        <div v-show="activeTab === 'terms'" class="p-6">
          <form @submit.prevent="saveDocument('terms_of_service')">
            <div class="space-y-6">
              <!-- Document Info -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Version
                  </label>
                  <input
                    v-model="termsData.version"
                    type="text"
                    class="input"
                    placeholder="1.0"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Effective Date
                  </label>
                  <input
                    v-model="termsData.effective_date"
                    type="date"
                    class="input"
                  />
                </div>
              </div>

              <!-- Content Editor -->
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">
                  Content
                </label>
                <textarea
                  v-model="termsData.content"
                  rows="20"
                  class="input font-mono text-sm"
                  placeholder="Enter Terms of Service content here. You can use Markdown formatting."
                ></textarea>
                <p class="mt-1 text-xs text-secondary-500">
                  Supports Markdown formatting
                </p>
              </div>

              <!-- Last Updated Info -->
              <div v-if="termsData.updated_at" class="text-sm text-secondary-600">
                <p>
                  Last updated: {{ formatDate(termsData.updated_at) }}
                  <span v-if="termsData.updater">
                    by {{ termsData.updater.first_name }} {{ termsData.updater.last_name }}
                  </span>
                </p>
              </div>

              <!-- Action Buttons -->
              <div class="flex justify-between items-center">
                <button
                  type="button"
                  @click="goBack"
                  class="btn btn-outline"
                >
                  <Icon name="heroicons:arrow-left" class="h-5 w-5 mr-2" />
                  Back to Dashboard
                </button>
                
                <div class="flex space-x-3">
                  <button
                    type="button"
                    @click="previewDocument('terms')"
                    class="btn btn-outline"
                  >
                    <Icon name="heroicons:eye" class="h-5 w-5 mr-2" />
                    Preview
                  </button>
                  <button
                    type="submit"
                    :disabled="savingTerms"
                    class="btn btn-primary"
                  >
                    <div v-if="savingTerms" class="spinner mr-2"></div>
                    <Icon v-else name="heroicons:check" class="h-5 w-5 mr-2" />
                    {{ savingTerms ? 'Saving...' : 'Save Changes' }}
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>

        <!-- Privacy Policy Tab -->
        <div v-show="activeTab === 'privacy'" class="p-6">
          <form @submit.prevent="saveDocument('privacy_policy')">
            <div class="space-y-6">
              <!-- Document Info -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Version
                  </label>
                  <input
                    v-model="privacyData.version"
                    type="text"
                    class="input"
                    placeholder="1.0"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Effective Date
                  </label>
                  <input
                    v-model="privacyData.effective_date"
                    type="date"
                    class="input"
                  />
                </div>
              </div>

              <!-- Content Editor -->
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">
                  Content
                </label>
                <textarea
                  v-model="privacyData.content"
                  rows="20"
                  class="input font-mono text-sm"
                  placeholder="Enter Privacy Policy content here. You can use Markdown formatting."
                ></textarea>
                <p class="mt-1 text-xs text-secondary-500">
                  Supports Markdown formatting
                </p>
              </div>

              <!-- Last Updated Info -->
              <div v-if="privacyData.updated_at" class="text-sm text-secondary-600">
                <p>
                  Last updated: {{ formatDate(privacyData.updated_at) }}
                  <span v-if="privacyData.updater">
                    by {{ privacyData.updater.first_name }} {{ privacyData.updater.last_name }}
                  </span>
                </p>
              </div>

              <!-- Action Buttons -->
              <div class="flex justify-between items-center">
                <button
                  type="button"
                  @click="goBack"
                  class="btn btn-outline"
                >
                  <Icon name="heroicons:arrow-left" class="h-5 w-5 mr-2" />
                  Back to Dashboard
                </button>
                
                <div class="flex space-x-3">
                  <button
                    type="button"
                    @click="previewDocument('privacy')"
                    class="btn btn-outline"
                  >
                    <Icon name="heroicons:eye" class="h-5 w-5 mr-2" />
                    Preview
                  </button>
                  <button
                    type="submit"
                    :disabled="savingPrivacy"
                    class="btn btn-primary"
                  >
                    <div v-if="savingPrivacy" class="spinner mr-2"></div>
                    <Icon v-else name="heroicons:check" class="h-5 w-5 mr-2" />
                    {{ savingPrivacy ? 'Saving...' : 'Save Changes' }}
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Preview Modal -->
      <div
        v-if="showPreview"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
        @click="showPreview = false"
      >
        <div
          class="bg-white rounded-2xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden"
          @click.stop
        >
          <div class="flex items-center justify-between p-6 border-b border-secondary-200">
            <h3 class="text-xl font-bold text-secondary-900">
              {{ previewType === 'terms' ? 'Terms of Service' : 'Privacy Policy' }} Preview
            </h3>
            <button
              @click="showPreview = false"
              class="p-2 text-secondary-400 hover:text-secondary-500 rounded-lg hover:bg-secondary-100"
            >
              <Icon name="heroicons:x-mark" class="h-6 w-6" />
            </button>
          </div>
          <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
            <div class="prose prose-sm max-w-none" v-html="renderedPreview"></div>
          </div>
          <div class="flex justify-end p-6 border-t border-secondary-200">
            <button @click="showPreview = false" class="btn btn-outline">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminManagement>
</template>

<script setup>
// Meta tags
useHead({
  title: 'Legal Documents - Admin - NFCGo',
});

definePageMeta({
  middleware: 'admin',
  layout: false,
});

const { $api, $toast } = useNuxtApp();

// Simple markdown to HTML converter
const parseMarkdown = (markdown) => {
  if (!markdown) return 'No content yet.';
  
  let html = markdown
    // Headers
    .replace(/^### (.*$)/gim, '<h3 class="text-lg font-semibold mt-4 mb-2">$1</h3>')
    .replace(/^## (.*$)/gim, '<h2 class="text-xl font-bold mt-6 mb-3">$1</h2>')
    .replace(/^# (.*$)/gim, '<h1 class="text-2xl font-bold mt-8 mb-4">$1</h1>')
    // Bold
    .replace(/\*\*(.+?)\*\*/g, '<strong class="font-semibold">$1</strong>')
    // Italic
    .replace(/\*(.+?)\*/g, '<em class="italic">$1</em>')
    // Links
    .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-primary-600 hover:underline" target="_blank">$1</a>')
    // Line breaks
    .replace(/\n\n/g, '</p><p class="mb-4">')
    // Lists
    .replace(/^\* (.+)$/gim, '<li class="ml-4">• $1</li>')
    .replace(/^- (.+)$/gim, '<li class="ml-4">• $1</li>');
  
  return `<p class="mb-4">${html}</p>`;
};

// Reactive data
const activeTab = ref('terms');
const savingTerms = ref(false);
const savingPrivacy = ref(false);
const showPreview = ref(false);
const previewType = ref('terms');

const termsData = reactive({
  content: '',
  version: '1.0',
  effective_date: new Date().toISOString().split('T')[0],
  updated_at: null,
  updater: null,
});

const privacyData = reactive({
  content: '',
  version: '1.0',
  effective_date: new Date().toISOString().split('T')[0],
  updated_at: null,
  updater: null,
});

// Computed
const renderedPreview = computed(() => {
  const content = previewType.value === 'terms' ? termsData.content : privacyData.content;
  return parseMarkdown(content);
});

// Load documents on mount
onMounted(async () => {
  await loadDocuments();
});

// Load documents
const loadDocuments = async () => {
  try {
    const response = await $api.get('/legal/documents');
    
    if (response.success && response.data) {
      response.data.forEach((doc) => {
        if (doc.type === 'terms_of_service') {
          Object.assign(termsData, {
            content: doc.content,
            version: doc.version,
            effective_date: doc.effective_date ? new Date(doc.effective_date).toISOString().split('T')[0] : termsData.effective_date,
            updated_at: doc.updated_at,
            updater: doc.updater,
          });
        } else if (doc.type === 'privacy_policy') {
          Object.assign(privacyData, {
            content: doc.content,
            version: doc.version,
            effective_date: doc.effective_date ? new Date(doc.effective_date).toISOString().split('T')[0] : privacyData.effective_date,
            updated_at: doc.updated_at,
            updater: doc.updater,
          });
        }
      });
    }
  } catch (error) {
    console.error('Error loading documents:', error);
    // Don't show error toast for initial load - documents might not exist yet
  }
};

// Save document
const saveDocument = async (type) => {
  const isTerms = type === 'terms_of_service';
  const data = isTerms ? termsData : privacyData;
  
  if (isTerms) {
    savingTerms.value = true;
  } else {
    savingPrivacy.value = true;
  }

  try {
    const response = await $api.put(`/admin/legal/documents/${type}`, {
      content: data.content,
      version: data.version,
      effective_date: data.effective_date,
    });

    if (response.success) {
      $toast.success(`${isTerms ? 'Terms of Service' : 'Privacy Policy'} updated successfully`);
      
      // Update local data with response
      Object.assign(data, {
        updated_at: response.data.updated_at,
        updater: response.data.updater,
      });
    }
  } catch (error) {
    console.error('Error saving document:', error);
    $toast.error('Failed to save document. Please try again.');
  } finally {
    if (isTerms) {
      savingTerms.value = false;
    } else {
      savingPrivacy.value = false;
    }
  }
};

// Preview document
const previewDocument = (type) => {
  previewType.value = type;
  showPreview.value = true;
};

// Go back to admin dashboard
const goBack = () => {
  navigateTo('/AdminManagement');
};

// Format date
const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>
