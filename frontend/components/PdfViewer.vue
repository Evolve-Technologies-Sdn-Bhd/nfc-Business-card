<!-- components/PdfViewer.vue -->
<template>
  <div class="pdf-viewer-container">
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="spinner"></div>
      <span class="ml-3 text-secondary-600">Loading PDF...</span>
    </div>

    <div v-else-if="error" class="text-center py-12">
      <Icon
        name="heroicons:exclamation-triangle"
        class="h-12 w-12 text-amber-500 mx-auto mb-3"
      />
      <p class="text-secondary-600">{{ error }}</p>
      <button
        v-if="retryable"
        @click="loadPdf"
        class="mt-4 btn btn-outline btn-sm"
      >
        <Icon name="heroicons:arrow-path" class="h-4 w-4 mr-2" />
        Retry
      </button>
    </div>

    <div v-else-if="pdfUrl" class="pdf-content">
      <iframe
        :src="pdfUrl"
        class="w-full h-full border-0 rounded-lg"
        :style="{ minHeight: minHeight }"
        title="PDF Document Viewer"
        @load="onIframeLoad"
        @error="onIframeError"
      ></iframe>
    </div>

    <div v-else class="text-center py-12 text-secondary-500">
      <Icon
        name="heroicons:document"
        class="h-12 w-12 text-secondary-300 mx-auto mb-3"
      />
      <p>No PDF available</p>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  // URL to fetch the PDF from
  url: {
    type: String,
    required: false,
    default: null,
  },
  // Direct blob URL if already fetched
  blobUrl: {
    type: String,
    required: false,
    default: null,
  },
  // Minimum height for the PDF viewer
  minHeight: {
    type: String,
    default: "600px",
  },
  // Whether authentication is required
  requireAuth: {
    type: Boolean,
    default: false,
  },
  // Whether to disable download (currently informational - browsers may override)
  disableDownload: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["loaded", "error"]);

const { $api } = useNuxtApp();

const loading = ref(false);
const error = ref(null);
const pdfUrl = ref(null);
const retryable = ref(true);

// Load PDF from URL
const loadPdf = async () => {
  if (!props.url && !props.blobUrl) {
    error.value = "No PDF source provided";
    retryable.value = false;
    emit("error", error.value);
    return;
  }

  // If blob URL is provided, use it directly
  if (props.blobUrl) {
    pdfUrl.value = props.blobUrl;
    emit("loaded");
    return;
  }

  loading.value = true;
  error.value = null;

  try {
    let response;
    
    if (props.requireAuth) {
      // Fetch with authentication
      const tokenCookie = useCookie("auth-token");
      const token = tokenCookie.value;

      if (!token) {
        throw new Error("Authentication required");
      }

      response = await fetch(props.url, {
        method: "GET",
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });
    } else {
      // Public fetch without auth
      response = await fetch(props.url);
    }

    if (!response.ok) {
      if (response.status === 404) {
        throw new Error("PDF document not found");
      } else if (response.status === 401) {
        throw new Error("Authentication required to view this document");
      } else {
        throw new Error(`Failed to load PDF (Status: ${response.status})`);
      }
    }

    const blob = await response.blob();
    
    if (blob.type !== "application/pdf") {
      throw new Error("Invalid PDF file");
    }

    // Create blob URL
    const blobUrl = URL.createObjectURL(blob);
    
    // Add fragment to disable download if requested (limited browser support)
    pdfUrl.value = props.disableDownload 
      ? `${blobUrl}#toolbar=0&navpanes=0&scrollbar=1`
      : blobUrl;

    emit("loaded");
  } catch (err) {
    console.error("Error loading PDF:", err);
    error.value = err.message || "Failed to load PDF document";
    retryable.value = true;
    emit("error", error.value);
  } finally {
    loading.value = false;
  }
};

// Handle iframe load
const onIframeLoad = () => {
  // Emit loaded event when iframe finishes loading
  emit("loaded");
};

// Handle iframe error
const onIframeError = (event) => {
  console.error("Iframe error:", event);
  error.value = "Failed to display PDF in viewer";
  retryable.value = true;
  emit("error", error.value);
};

// Load PDF when component mounts or URL changes
watch([() => props.url, () => props.blobUrl], () => {
  loadPdf();
}, { immediate: true });

// Cleanup blob URL when component unmounts
onUnmounted(() => {
  if (pdfUrl.value && pdfUrl.value.startsWith("blob:")) {
    URL.revokeObjectURL(pdfUrl.value);
  }
});
</script>

<style scoped>
.pdf-viewer-container {
  width: 100%;
  height: 100%;
  position: relative;
}

.pdf-content {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
}
</style>
