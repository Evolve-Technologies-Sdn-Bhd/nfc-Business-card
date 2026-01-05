<template>
  <div
    v-if="show"
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
    @click.self="$emit('update:show', false)"
  >
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col animate-modal-appear">
      <!-- Modal Header -->
      <div class="flex items-center justify-between p-4 border-b border-gray-200 bg-gradient-to-r from-primary-50 to-secondary-50">
        <div class="flex-1 min-w-0">
          <h2 class="text-xl font-bold text-gray-900 truncate">
            {{ type === 'privacy' ? 'Privacy Policy' : 'Terms of Service' }}
          </h2>
          <div v-if="docInfo && !loading" class="flex items-center gap-3 mt-1 text-sm text-gray-600">
            <span class="truncate max-w-[200px]" :title="docInfo.filename">
              <Icon name="heroicons:document" class="h-4 w-4 inline mr-1" />
              {{ docInfo.filename }}
            </span>
            <span class="text-gray-400">•</span>
            <span>{{ formatFileSize(docInfo.size) }}</span>
          </div>
        </div>
        <button
          @click="$emit('update:show', false)"
          class="p-2 rounded-full hover:bg-white/80 transition-colors ml-4"
          aria-label="Close modal"
        >
          <Icon name="heroicons:x-mark" class="h-6 w-6 text-gray-600" />
        </button>
      </div>
      
      <!-- Modal Body -->
      <div class="flex-1 overflow-hidden relative">
        <!-- Loading State -->
        <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-gray-50">
          <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto mb-4"></div>
            <p class="text-gray-600">Loading document...</p>
          </div>
        </div>
        
        <!-- Error State -->
        <div v-else-if="error" class="absolute inset-0 flex items-center justify-center bg-gray-50 p-8">
          <div class="text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <Icon name="heroicons:exclamation-triangle" class="h-8 w-8 text-red-600" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Document Not Available</h3>
            <p class="text-gray-600 mb-4">{{ error }}</p>
            <button
              @click="$emit('update:show', false)"
              class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
            >
              Close
            </button>
          </div>
        </div>
        
        <!-- PDF Viewer -->
        <iframe
          v-else-if="docInfo"
          :src="pdfUrl"
          class="w-full h-full border-0"
          style="min-height: 70vh;"
          title="Legal Document"
        ></iframe>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    default: '' // 'privacy' or 'terms'
  }
})

defineEmits(['update:show'])

const { $api } = useNuxtApp()

const loading = ref(false)
const docInfo = ref(null)
const error = ref('')

// Computed URL for PDF viewer
const pdfUrl = computed(() => {
  if (!props.type) return ''
  const config = useRuntimeConfig()
  const apiBaseUrl = config.public.apiBaseUrl || 'http://localhost:8000/api'
  return `${apiBaseUrl}/legal/pdf/${props.type}/view`
})

// Format file size for display
const formatFileSize = (bytes) => {
  if (!bytes) return ''
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}

// Load document info when modal opens
watch(() => props.show, async (newValue) => {
  if (newValue && props.type) {
    loading.value = true
    error.value = ''
    docInfo.value = null

    try {
      const response = await $api.get(`/legal/pdf/${props.type}/info`)
      if (response.success && response.data) {
        docInfo.value = response.data
      } else {
        error.value = response.message || 'Document not available.'
      }
    } catch (err) {
      console.error('Error loading legal document info:', err)
      error.value = err.data?.message || 'Failed to load document. Please try again later.'
    } finally {
      loading.value = false
    }
  }
})
</script>

<style scoped>
@keyframes modalAppear {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(-20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.animate-modal-appear {
  animation: modalAppear 0.3s ease-out;
}
</style>
