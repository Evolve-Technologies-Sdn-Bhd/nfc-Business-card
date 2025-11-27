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
                  disabled
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
                  disabled
                />
              </div>
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
                    >PDF uploaded ({{
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

            <!-- PDF Upload Section -->
            <div
              class="bg-secondary-50 rounded-lg p-4 border border-secondary-200"
            >
              <h3 class="text-sm font-semibold text-secondary-900 mb-3">
                PDF Document
              </h3>

              <div
                v-if="termsPdfStatus.exists"
                class="mb-3 flex items-center justify-between"
              >
                <div
                  class="flex items-center space-x-2 text-sm text-secondary-600"
                >
                  <Icon
                    name="heroicons:document-check"
                    class="h-5 w-5 text-green-500"
                  />
                  <span
                    >PDF uploaded ({{
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
                  Download PDF
                </button>
              </div>

              <div v-else class="mb-3 text-sm text-secondary-500">
                <Icon
                  name="heroicons:information-circle"
                  class="h-5 w-5 inline mr-1"
                />
                No PDF uploaded yet
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
                  class="btn btn-sm btn-outline"
                >
                  <Icon name="heroicons:arrow-up-tray" class="h-4 w-4 mr-1" />
                  {{ termsPdfStatus.exists ? "Replace PDF" : "Upload PDF" }}
                </button>
                <span
                  v-if="uploadingTermsPdf"
                  class="text-sm text-secondary-600"
                >
                  <div class="spinner spinner-sm mr-1"></div>
                  Uploading...
                </span>
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
            <div class="flex justify-between items-center">
              <button type="button" @click="goBack" class="btn btn-outline">
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
                  disabled
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
                  disabled
                />
              </div>
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
                    >PDF uploaded ({{
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
            <div class="flex justify-between items-center">
              <button type="button" @click="goBack" class="btn btn-outline">
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
        class="card max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden"
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

// Simple markdown to HTML converter
const parseMarkdown = (markdown) => {
  if (!markdown) return "No content yet.";

  let html = markdown
    // Headers
    .replace(
      /^### (.*$)/gim,
      '<h3 class="text-lg font-semibold mt-4 mb-2">$1</h3>'
    )
    .replace(/^## (.*$)/gim, '<h2 class="text-xl font-bold mt-6 mb-3">$1</h2>')
    .replace(/^# (.*$)/gim, '<h1 class="text-2xl font-bold mt-8 mb-4">$1</h1>')
    // Bold
    .replace(/\*\*(.+?)\*\*/g, '<strong class="font-semibold">$1</strong>')
    // Italic
    .replace(/\*(.+?)\*/g, '<em class="italic">$1</em>')
    // Links
    .replace(
      /\[([^\]]+)\]\(([^)]+)\)/g,
      '<a href="$2" class="text-primary-600 hover:underline" target="_blank">$1</a>'
    )
    // Line breaks
    .replace(/\n\n/g, '</p><p class="mb-4">')
    // Lists
    .replace(/^\* (.+)$/gim, '<li class="ml-4">• $1</li>')
    .replace(/^- (.+)$/gim, '<li class="ml-4">• $1</li>');

  return `<p class="mb-4">${html}</p>`;
};

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

// Computed
const renderedPreview = computed(() => {
  const content =
    previewType.value === "terms" ? termsData.content : privacyData.content;
  return parseMarkdown(content);
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

// Save document - PDF upload instead of text content
// Note: This now focuses on PDF uploads via file input handlers
// The actual save is triggered through handleTermsFileSelect and handlePrivacyFileSelect
const saveDocument = async (type) => {
  // This function is kept for compatibility but now redirects to PDF upload workflow
  const isTerms = type === "terms_of_service";
  const docName = isTerms ? "Terms of Service" : "Privacy Policy";
  
  $toast.info(
    `To save ${docName}, please upload a PDF file using the file upload button above.`
  );
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
      await checkPdfStatus("terms");
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
      await checkPdfStatus("privacy");
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
    const token = localStorage.getItem("auth_token");

    const url = `${apiBaseUrl}/admin/legal/pdf/${type}/download`;

    // Create a temporary link and trigger download
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", "");
    link.style.display = "none";

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
    link.href = blobUrl;
    link.download =
      type === "terms" ? "Terms-of-Service.pdf" : "Privacy-Policy.pdf";

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

// Format file size
const formatFileSize = (bytes) => {
  if (!bytes) return "0 Bytes";

  const k = 1024;
  const sizes = ["Bytes", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));

  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + " " + sizes[i];
};

// Go back to admin dashboard
const goBack = () => {
  navigateTo("/AdminManagement");
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
