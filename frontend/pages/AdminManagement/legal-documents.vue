<!-- pages/AdminManagement/legal-documents.vue -->
<template>
  <div>
    <!-- Header -->
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-3xl font-bold text-secondary-900">
            Legal Documents Management
          </h1>
          <p class="mt-2 text-secondary-600">
            Manage Terms of Service and Privacy Policy
          </p>
        </div>
      </div>
    </div>

    <!-- Document Tabs -->
    <div class="card mb-6">
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
                <label
                  class="block text-sm font-medium text-secondary-700 mb-1"
                >
                  Version
                </label>
                <input
                  v-model="termsData.version"
                  type="text"
                  class="input"
                  placeholder="1.0"
                  required
                />
              </div>
              <div>
                <label
                  class="block text-sm font-medium text-secondary-700 mb-1"
                >
                  Effective Date
                </label>
                <input
                  v-model="termsData.effective_date"
                  type="date"
                  class="input"
                  required
                />
              </div>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
              <p class="text-sm text-amber-800">
                <strong>Workflow:</strong> (1) Upload your PDF file below, (2) Set version and effective date, (3) Click Save Changes to store metadata.
              </p>
            </div>

            <!-- PDF Upload Section -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <h3 class="text-sm font-semibold text-secondary-900 mb-3">
                PDF Document Upload
              </h3>
              <p class="text-sm text-secondary-600 mb-4">
                Upload a PDF file containing the Terms of Service document. This PDF will be displayed to users who access the Terms of Service.
              </p>

              <div
                v-if="termsPdfStatus.exists"
                class="mb-4 flex items-center justify-between bg-green-50 border border-green-200 rounded p-3"
              >
                <div
                  class="flex items-center space-x-2 text-sm text-green-700"
                >
                  <Icon
                    name="heroicons:document-check"
                    class="h-5 w-5 text-green-500"
                  />
                  <span
                    >{{ getDisplayFileName(termsPdfStatus.fileInfo?.original_filename) }} ({{
                      formatFileSize(termsPdfStatus.fileInfo?.size)
                    }})</span
                  >
                </div>
                <button
                  type="button"
                  @click="downloadPdf('terms')"
                  class="btn btn-sm btn-outline"
                >
                  <Icon name="heroicons:arrow-down-tray" class="h-4 w-4 mr-1" />
                  Download
                </button>
              </div>

              <div v-else class="mb-4 text-sm text-secondary-500 bg-yellow-50 border border-yellow-200 rounded p-3">
                <Icon
                  name="heroicons:exclamation-triangle"
                  class="h-5 w-5 inline mr-1 text-yellow-600"
                />
                No PDF uploaded yet. Please upload a PDF file below.
              </div>

              <div class="flex items-center space-x-3">
                <input
                  type="file"
                  ref="termsFileInput"
                  accept="application/pdf"
                  @change="handleTermsFileSelect"
                  class="hidden"
                />
                <button
                  type="button"
                  @click="$refs.termsFileInput.click()"
                  :disabled="uploadingTermsPdf"
                  class="btn btn-sm btn-primary"
                >
                  <Icon v-if="!uploadingTermsPdf" name="heroicons:arrow-up-tray" class="h-4 w-4 mr-1" />
                  <span v-if="uploadingTermsPdf" class="animate-spin">⏳</span>
                  {{ uploadingTermsPdf ? 'Uploading...' : 'Choose PDF File' }}
                </button>
                <span class="text-xs text-secondary-500">Max 10MB</span>
              </div>
            </div>

            <!-- Last Updated Info -->
            <div v-if="termsData.updated_at" class="text-sm text-secondary-600">
              <p>
                Last updated: {{ formatDate(termsData.updated_at) }}
                <span v-if="termsData.updater">
                  by {{ termsData.updater.first_name }}
                  {{ termsData.updater.last_name }}
                </span>
              </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end items-center">
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
                  {{ savingTerms ? "Saving..." : "Save Changes" }}
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
                <label
                  class="block text-sm font-medium text-secondary-700 mb-1"
                >
                  Version
                </label>
                <input
                  v-model="privacyData.version"
                  type="text"
                  class="input"
                  placeholder="1.0"
                  required
                />
              </div>
              <div>
                <label
                  class="block text-sm font-medium text-secondary-700 mb-1"
                >
                  Effective Date
                </label>
                <input
                  v-model="privacyData.effective_date"
                  type="date"
                  class="input"
                  required
                />
              </div>
            </div>

            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
              <p class="text-sm text-amber-800">
                <strong>Workflow:</strong> (1) Upload your PDF file below, (2) Set version and effective date, (3) Click Save Changes to store metadata.
              </p>
            </div>

            <!-- PDF Upload Section -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <h3 class="text-sm font-semibold text-secondary-900 mb-3">
                PDF Document Upload
              </h3>
              <p class="text-sm text-secondary-600 mb-4">
                Upload a PDF file containing the Privacy Policy document. This PDF will be displayed to users who access the Privacy Policy.
              </p>

              <div
                v-if="privacyPdfStatus.exists"
                class="mb-4 flex items-center justify-between bg-green-50 border border-green-200 rounded p-3"
              >
                <div
                  class="flex items-center space-x-2 text-sm text-green-700"
                >
                  <Icon
                    name="heroicons:document-check"
                    class="h-5 w-5 text-green-500"
                  />
                  <span
                    >{{ getDisplayFileName(privacyPdfStatus.fileInfo?.original_filename) }} ({{
                      formatFileSize(privacyPdfStatus.fileInfo?.size)
                    }})</span
                  >
                </div>
                <button
                  type="button"
                  @click="downloadPdf('privacy')"
                  class="btn btn-sm btn-outline"
                >
                  <Icon name="heroicons:arrow-down-tray" class="h-4 w-4 mr-1" />
                  Download
                </button>
              </div>

              <div v-else class="mb-4 text-sm text-secondary-500 bg-yellow-50 border border-yellow-200 rounded p-3">
                <Icon
                  name="heroicons:exclamation-triangle"
                  class="h-5 w-5 inline mr-1 text-yellow-600"
                />
                No PDF uploaded yet. Please upload a PDF file below.
              </div>

              <div class="flex items-center space-x-3">
                <input
                  type="file"
                  ref="privacyFileInput"
                  accept="application/pdf"
                  @change="handlePrivacyFileSelect"
                  class="hidden"
                />
                <button
                  type="button"
                  @click="$refs.privacyFileInput.click()"
                  :disabled="uploadingPrivacyPdf"
                  class="btn btn-sm btn-primary"
                >
                  <Icon v-if="!uploadingPrivacyPdf" name="heroicons:arrow-up-tray" class="h-4 w-4 mr-1" />
                  <span v-if="uploadingPrivacyPdf" class="animate-spin">⏳</span>
                  {{ uploadingPrivacyPdf ? 'Uploading...' : 'Choose PDF File' }}
                </button>
                <span class="text-xs text-secondary-500">Max 10MB</span>
              </div>
            </div>

            <!-- Last Updated Info -->
            <div
              v-if="privacyData.updated_at"
              class="text-sm text-secondary-600"
            >
              <p>
                Last updated: {{ formatDate(privacyData.updated_at) }}
                <span v-if="privacyData.updater">
                  by {{ privacyData.updater.first_name }}
                  {{ privacyData.updater.last_name }}
                </span>
              </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end items-center">
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
                  {{ savingPrivacy ? "Saving..." : "Save Changes" }}
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
        class="card max-w-6xl w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col"
        @click.stop
      >
        <div
          class="flex items-center justify-between p-6 border-b border-secondary-200"
        >
          <h3 class="text-xl font-bold text-secondary-900">
            {{
              previewType === "terms" ? "Terms of Service" : "Privacy Policy"
            }}
            Preview
          </h3>
          <button
            @click="showPreview = false"
            class="p-2 text-secondary-400 hover:text-secondary-500 rounded-lg hover:bg-secondary-100"
          >
            <Icon name="heroicons:x-mark" class="h-6 w-6" />
          </button>
        </div>
        <div class="flex-1 p-6 overflow-hidden">
          <PdfViewer
            :url="previewPdfUrl"
            :require-auth="true"
            min-height="calc(90vh - 200px)"
            @loaded="onPdfLoaded"
            @error="onPdfError"
          />
        </div>
        <div class="flex justify-end p-6 border-t border-secondary-200">
          <button @click="showPreview = false" class="btn btn-outline">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Meta tags
useHead({
  title: "Legal Documents - Admin - NFCGo",
});

definePageMeta({
  middleware: ["auth", "admin"],
  layout: "admin-management",
});

const { $api, $toast } = useNuxtApp();

// Reactive data
const activeTab = ref("terms");
const savingTerms = ref(false);
const savingPrivacy = ref(false);
const showPreview = ref(false);
const previewType = ref("terms");
const uploadingTermsPdf = ref(false);
const uploadingPrivacyPdf = ref(false);
const termsFileInput = ref(null);
const privacyFileInput = ref(null);

const termsPdfStatus = reactive({
  exists: false,
  fileInfo: null,
});

const privacyPdfStatus = reactive({
  exists: false,
  fileInfo: null,
});

// Computed preview PDF URL
const previewPdfUrl = computed(() => {
  const type = previewType.value === "terms" ? "terms" : "privacy";
  return `http://localhost:8000/api/admin/legal/pdf/${type}/download`;
});

// Preview handlers
const onPdfLoaded = () => {
  console.log("PDF loaded successfully in preview");
};

const onPdfError = (errorMessage) => {
  console.error("PDF preview error:", errorMessage);
  $toast.error("Failed to load PDF preview");
};

const termsData = reactive({
  content: "",
  version: "1.0",
  effective_date: new Date().toISOString().split("T")[0],
  updated_at: null,
  updater: null,
});

const privacyData = reactive({
  content: "",
  version: "1.0",
  effective_date: new Date().toISOString().split("T")[0],
  updated_at: null,
  updater: null,
});

// Load documents on mount
onMounted(async () => {
  await loadDocuments();
  await checkPdfStatus("terms");
  await checkPdfStatus("privacy");
});

// Check PDF status
const checkPdfStatus = async (type) => {
  try {
    const response = await $api.get(`/admin/legal/pdf/${type}/status`);
    if (response.success) {
      if (type === "terms") {
        Object.assign(termsPdfStatus, {
          exists: response.exists,
          fileInfo: response.file_info,
        });
      } else {
        Object.assign(privacyPdfStatus, {
          exists: response.exists,
          fileInfo: response.file_info,
        });
      }
    }
  } catch (error) {
    console.error("Error checking PDF status:", error);
  }
};

// Load documents
const loadDocuments = async () => {
  try {
    const response = await $api.get("/legal/documents");

    if (response.success && response.data) {
      response.data.forEach((doc) => {
        if (doc.type === "terms_of_service") {
          Object.assign(termsData, {
            content: doc.content,
            version: doc.version,
            effective_date: doc.effective_date
              ? new Date(doc.effective_date).toISOString().split("T")[0]
              : termsData.effective_date,
            updated_at: doc.updated_at,
            updater: doc.updater,
          });
        } else if (doc.type === "privacy_policy") {
          Object.assign(privacyData, {
            content: doc.content,
            version: doc.version,
            effective_date: doc.effective_date
              ? new Date(doc.effective_date).toISOString().split("T")[0]
              : privacyData.effective_date,
            updated_at: doc.updated_at,
            updater: doc.updater,
          });
        }
      });
    }
  } catch (error) {
    console.error("Error loading documents:", error);
    // Don't show error toast for initial load - documents might not exist yet
  }
};

// Save document - Updates metadata (version, effective_date) in database
// PDF file itself is uploaded separately via file input handlers
const saveDocument = async (type) => {
  const isTerms = type === "terms_of_service";
  const docName = isTerms ? "Terms of Service" : "Privacy Policy";
  const data = isTerms ? termsData : privacyData;

  // Check if PDF has been uploaded first
  const pdfStatus = isTerms ? termsPdfStatus : privacyPdfStatus;
  if (!pdfStatus.exists) {
    $toast.error(
      `Please upload a PDF file first before saving ${docName} metadata.`
    );
    return;
  }

  if (isTerms) {
    savingTerms.value = true;
  } else {
    savingPrivacy.value = true;
  }

  try {
    const response = await $api.put(`/admin/legal/documents/${type}`, {
      content: `PDF document uploaded. Version: ${data.version}`, // Placeholder content
      version: data.version,
      effective_date: data.effective_date,
    });

    if (response.success) {
      $toast.success(`${docName} metadata updated successfully`);
      await loadDocuments(); // Reload to get updated metadata
    }
  } catch (error) {
    console.error("Error saving document:", error);
    $toast.error(`Failed to save ${docName} metadata. Please try again.`);
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

// Handle Terms file selection
const handleTermsFileSelect = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  if (file.type !== "application/pdf") {
    $toast.error("Please select a PDF file");
    return;
  }

  if (file.size > 10 * 1024 * 1024) {
    // 10MB limit
    $toast.error("File size must be less than 10MB");
    return;
  }

  uploadingTermsPdf.value = true;

  try {
    const formData = new FormData();
    formData.append("pdf", file);

    const response = await $api.post(
      "/admin/legal/pdf/terms/upload",
      formData,
      {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      }
    );

    if (response.success) {
      $toast.success("Terms of Service PDF uploaded successfully");
      
      // Update status with response data if available
      if (response.original_filename && response.size) {
        Object.assign(termsPdfStatus, {
          exists: true,
          fileInfo: {
            original_filename: response.original_filename,
            size: response.size,
            url: response.path
          }
        });
      } else {
        // Fallback to checking status via API
        await checkPdfStatus("terms");
      }
    }
  } catch (error) {
    console.error("Error uploading PDF:", error);
    $toast.error("Failed to upload PDF. Please try again.");
  } finally {
    uploadingTermsPdf.value = false;
    if (termsFileInput.value) {
      termsFileInput.value.value = "";
    }
  }
};

// Handle Privacy file selection
const handlePrivacyFileSelect = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  if (file.type !== "application/pdf") {
    $toast.error("Please select a PDF file");
    return;
  }

  if (file.size > 10 * 1024 * 1024) {
    // 10MB limit
    $toast.error("File size must be less than 10MB");
    return;
  }

  uploadingPrivacyPdf.value = true;

  try {
    const formData = new FormData();
    formData.append("pdf", file);

    const response = await $api.post(
      "/admin/legal/pdf/privacy/upload",
      formData,
      {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      }
    );

    if (response.success) {
      $toast.success("Privacy Policy PDF uploaded successfully");
      
      // Update status with response data if available
      if (response.original_filename && response.size) {
        Object.assign(privacyPdfStatus, {
          exists: true,
          fileInfo: {
            original_filename: response.original_filename,
            size: response.size,
            url: response.path
          }
        });
      } else {
        // Fallback to checking status via API
        await checkPdfStatus("privacy");
      }
    }
  } catch (error) {
    console.error("Error uploading PDF:", error);
    $toast.error("Failed to upload PDF. Please try again.");
  } finally {
    uploadingPrivacyPdf.value = false;
    if (privacyFileInput.value) {
      privacyFileInput.value.value = "";
    }
  }
};

// Download PDF
const downloadPdf = async (type) => {
  try {
    const config = useRuntimeConfig();
    const apiBaseUrl = config.public.apiBaseUrl;
    const tokenCookie = useCookie("auth-token");
    const token = tokenCookie.value;

    if (!token) {
      $toast.error("Authentication required. Please log in again.");
      return;
    }

    const url = `${apiBaseUrl}/admin/legal/pdf/${type}/download`;

    // Add authorization header by fetching as blob first
    const response = await fetch(url, {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: "application/pdf",
      },
    });

    if (!response.ok) {
      throw new Error("Download failed");
    }

    const blob = await response.blob();
    const blobUrl = window.URL.createObjectURL(blob);
    
    // Create a temporary link and trigger download
    const link = document.createElement("a");
    link.href = blobUrl;
    link.download =
      type === "terms" ? "Terms-of-Service.pdf" : "Privacy-Policy.pdf";
    link.style.display = "none";

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Clean up the blob URL
    window.URL.revokeObjectURL(blobUrl);

    $toast.success("PDF downloaded successfully");
  } catch (error) {
    console.error("Error downloading PDF:", error);
    $toast.error("Failed to download PDF. Please try again.");
  }
};

// Format file size - always show KB with 2 decimal places for consistency
const formatFileSize = (bytes) => {
  if (!bytes) return "0.00 KB";
  
  const kb = bytes / 1024;
  return kb.toFixed(2) + " KB";
};

// Get display filename without extension
const getDisplayFileName = (filename) => {
  if (!filename) return "PDF uploaded";
  
  // Remove .pdf extension if present
  return filename.replace(/\.pdf$/i, '');
};

// Format date
const formatDate = (date) => {
  if (!date) return "";
  return new Date(date).toLocaleString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};
</script>
