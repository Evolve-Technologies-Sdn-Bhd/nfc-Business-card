<!-- pages/AdminManagement/legal-documents.vue -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Compliance"
      eyebrow-icon="heroicons:document-text"
      title="Legal Documents"
      description="Publish, version and preview the Terms of Service and Privacy Policy PDFs shown across NFCGo."
    />

    <!-- KPI tiles -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
      <AdminStatTile
        label="Terms version"
        :value="termsData.version"
        icon="heroicons:document-text"
      />
      <AdminStatTile
        label="Privacy version"
        :value="privacyData.version"
        icon="heroicons:shield-check"
        icon-class="!bg-accent-50 !text-accent-600"
      />
      <AdminStatTile
        label="PDFs published"
        :value="`${(termsPdfStatus.exists ? 1 : 0) + (privacyPdfStatus.exists ? 1 : 0)} / 2`"
        icon="heroicons:document-check"
        icon-class="!bg-success-50 !text-success-600"
      />
    </div>

    <!-- Document register -->
    <AdminSectionCard flush class="mt-5">
      <template #title>Document register</template>
      <template #header-actions>
        <span class="text-caption text-secondary-500">
          {{ (termsPdfStatus.exists ? 1 : 0) + (privacyPdfStatus.exists ? 1 : 0) }} of 2 PDFs published
        </span>
      </template>

      <div class="admin-scroll">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Document</th>
              <th>Stored type</th>
              <th>Version</th>
              <th>Last updated</th>
              <th>PDF status</th>
              <th class="admin-col-actions">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="doc in [
                {
                  key: 'terms',
                  title: 'Terms of Service',
                  type: 'terms_of_service',
                  version: termsData.version,
                  updatedAt: termsData.updated_at,
                  exists: termsPdfStatus.exists,
                },
                {
                  key: 'privacy',
                  title: 'Privacy Policy',
                  type: 'privacy_policy',
                  version: privacyData.version,
                  updatedAt: privacyData.updated_at,
                  exists: privacyPdfStatus.exists,
                },
              ]"
              :key="doc.key"
            >
              <td>
                <div class="flex items-center gap-3">
                  <span
                    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
                  >
                    <Icon name="heroicons:document-text" class="h-5 w-5" />
                  </span>
                  <span class="font-semibold text-secondary-900">
                    {{ doc.title }}
                  </span>
                </div>
              </td>
              <td>
                <code
                  class="rounded bg-secondary-100 px-1.5 py-0.5 text-caption text-secondary-600"
                >
                  {{ doc.type }}
                </code>
              </td>
              <td class="tabular-nums">{{ doc.version }}</td>
              <td class="text-secondary-500">
                {{ doc.updatedAt ? formatDate(doc.updatedAt) : "Not saved yet" }}
              </td>
              <td>
                <AdminStatusBadge
                  :tone="doc.exists ? 'success' : 'warning'"
                  :label="doc.exists ? 'Published' : 'Awaiting PDF'"
                />
              </td>
              <td class="admin-col-actions">
                <button
                  type="button"
                  class="admin-btn admin-btn--outline admin-btn--sm"
                  @click="activeTab = doc.key"
                >
                  <Icon name="heroicons:pencil" class="h-4 w-4" />
                  Edit
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </AdminSectionCard>

    <!-- Workspace -->
    <AdminSectionCard flush class="mt-5">
      <template #title>Document workspace</template>
      <template #header-actions>
        <div
          class="inline-flex rounded-lg border border-secondary-200 bg-secondary-50 p-0.5"
        >
          <button
            type="button"
            class="rounded-md px-3 py-1.5 text-caption font-semibold transition"
            :class="
              activeTab === 'terms'
                ? 'bg-white text-accent-700 shadow-sm'
                : 'text-secondary-500 hover:text-secondary-700'
            "
            @click="activeTab = 'terms'"
          >
            Terms of Service
          </button>
          <button
            type="button"
            class="rounded-md px-3 py-1.5 text-caption font-semibold transition"
            :class="
              activeTab === 'privacy'
                ? 'bg-white text-accent-700 shadow-sm'
                : 'text-secondary-500 hover:text-secondary-700'
            "
            @click="activeTab = 'privacy'"
          >
            Privacy Policy
          </button>
        </div>
      </template>

      <div class="admin-panel-body">
        <!-- Terms of Service -->
        <form
          v-show="activeTab === 'terms'"
          @submit.prevent="saveDocument('terms_of_service')"
        >
          <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Metadata column -->
            <div class="space-y-5">
              <div class="admin-form-section">
                <p class="admin-form-section-title">Document metadata</p>
                <p class="admin-form-section-desc">
                  Set the version and effective date stored for this document.
                </p>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <label class="admin-label">
                      Version <span class="text-error-600">*</span>
                    </label>
                    <input
                      v-model.number="termsData.version"
                      type="number"
                      min="1.0"
                      step="0.1"
                      class="admin-field tabular-nums"
                      :class="versionErrors.terms ? '!border-error-400' : ''"
                      placeholder="1.0"
                      required
                      @input="validateTermsVersion"
                      @change="validateTermsVersion"
                    />
                    <p
                      v-if="versionErrors.terms"
                      class="text-helper mt-1 text-error-600"
                    >
                      {{ versionErrors.terms }}
                    </p>
                  </div>
                  <div>
                    <label class="admin-label">
                      Effective date <span class="text-error-600">*</span>
                    </label>
                    <input
                      v-model="termsData.effective_date"
                      type="date"
                      class="admin-field"
                      required
                    />
                  </div>
                </div>
              </div>

              <div
                class="flex items-start gap-3 rounded-xl border border-warning-200 bg-warning-50 p-4"
              >
                <Icon
                  name="heroicons:information-circle"
                  class="mt-0.5 h-5 w-5 shrink-0 text-warning-600"
                />
                <p class="text-body-sm text-warning-800">
                  <strong class="font-semibold">Workflow:</strong> (1) upload your
                  PDF on the right, (2) set version and effective date, (3) save to
                  store the metadata.
                </p>
              </div>

              <p v-if="termsData.updated_at" class="text-body-sm text-secondary-600">
                Last updated {{ formatDate(termsData.updated_at) }}
                <span v-if="termsData.updater">
                  by {{ termsData.updater.first_name }}
                  {{ termsData.updater.last_name }}
                </span>
              </p>
            </div>

            <!-- PDF column -->
            <div>
              <div class="rounded-xl border border-secondary-200 bg-secondary-50 p-4">
                <p class="admin-form-section-title">PDF document</p>
                <p class="admin-form-section-desc">
                  The uploaded PDF is shown to users who open the Terms of Service.
                </p>

                <div
                  v-if="termsPdfStatus.exists"
                  class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-success-200 bg-success-50 p-3"
                >
                  <div
                    class="flex min-w-0 items-center gap-2 text-body-sm text-success-700"
                  >
                    <Icon
                      name="heroicons:document-check"
                      class="h-5 w-5 shrink-0 text-success-500"
                    />
                    <span class="truncate">
                      {{ getDisplayFileName(termsPdfStatus.fileInfo?.original_filename) }}
                      ({{ formatFileSize(termsPdfStatus.fileInfo?.size) }})
                    </span>
                  </div>
                  <button
                    type="button"
                    class="admin-btn admin-btn--outline admin-btn--sm"
                    @click="downloadPdf('terms')"
                  >
                    <Icon name="heroicons:arrow-down-tray" class="h-4 w-4" />
                    Download
                  </button>
                </div>

                <div
                  v-else
                  class="mt-4 flex items-center gap-2 rounded-lg border border-warning-200 bg-warning-50 p-3 text-body-sm text-warning-700"
                >
                  <Icon
                    name="heroicons:exclamation-triangle"
                    class="h-5 w-5 shrink-0 text-warning-600"
                  />
                  No PDF uploaded yet — upload one below.
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                  <input
                    ref="termsFileInput"
                    type="file"
                    accept="application/pdf"
                    class="hidden"
                    @change="handleTermsFileSelect"
                  />
                  <button
                    type="button"
                    class="admin-btn admin-btn--primary admin-btn--sm"
                    :disabled="uploadingTermsPdf"
                    @click="$refs.termsFileInput.click()"
                  >
                    <Icon
                      v-if="!uploadingTermsPdf"
                      name="heroicons:arrow-up-tray"
                      class="h-4 w-4"
                    />
                    <Icon
                      v-else
                      name="heroicons:arrow-path"
                      class="h-4 w-4 animate-spin"
                    />
                    {{ uploadingTermsPdf ? "Uploading…" : "Choose PDF file" }}
                  </button>
                  <span class="admin-hint !mt-0">Max 10 MB</span>
                </div>
              </div>
            </div>
          </div>

          <div
            class="mt-6 flex flex-wrap items-center justify-end gap-3 border-t border-secondary-200 pt-5"
          >
            <button
              type="button"
              class="admin-btn admin-btn--outline"
              @click="previewDocument('terms')"
            >
              <Icon name="heroicons:eye" class="h-4 w-4" />
              Preview
            </button>
            <button
              type="submit"
              :disabled="savingTerms"
              class="admin-btn admin-btn--primary"
            >
              <Icon
                v-if="savingTerms"
                name="heroicons:arrow-path"
                class="h-4 w-4 animate-spin"
              />
              <Icon v-else name="heroicons:check" class="h-4 w-4" />
              {{ savingTerms ? "Saving…" : "Save changes" }}
            </button>
          </div>
        </form>

        <!-- Privacy Policy -->
        <form
          v-show="activeTab === 'privacy'"
          @submit.prevent="saveDocument('privacy_policy')"
        >
          <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Metadata column -->
            <div class="space-y-5">
              <div class="admin-form-section">
                <p class="admin-form-section-title">Document metadata</p>
                <p class="admin-form-section-desc">
                  Set the version and effective date stored for this document.
                </p>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <label class="admin-label">
                      Version <span class="text-error-600">*</span>
                    </label>
                    <input
                      v-model.number="privacyData.version"
                      type="number"
                      min="1.0"
                      step="0.1"
                      class="admin-field tabular-nums"
                      :class="versionErrors.privacy ? '!border-error-400' : ''"
                      placeholder="1.0"
                      required
                      @input="validatePrivacyVersion"
                      @change="validatePrivacyVersion"
                    />
                    <p
                      v-if="versionErrors.privacy"
                      class="text-helper mt-1 text-error-600"
                    >
                      {{ versionErrors.privacy }}
                    </p>
                  </div>
                  <div>
                    <label class="admin-label">
                      Effective date <span class="text-error-600">*</span>
                    </label>
                    <input
                      v-model="privacyData.effective_date"
                      type="date"
                      class="admin-field"
                      required
                    />
                  </div>
                </div>
              </div>

              <div
                class="flex items-start gap-3 rounded-xl border border-warning-200 bg-warning-50 p-4"
              >
                <Icon
                  name="heroicons:information-circle"
                  class="mt-0.5 h-5 w-5 shrink-0 text-warning-600"
                />
                <p class="text-body-sm text-warning-800">
                  <strong class="font-semibold">Workflow:</strong> (1) upload your
                  PDF on the right, (2) set version and effective date, (3) save to
                  store the metadata.
                </p>
              </div>

              <p
                v-if="privacyData.updated_at"
                class="text-body-sm text-secondary-600"
              >
                Last updated {{ formatDate(privacyData.updated_at) }}
                <span v-if="privacyData.updater">
                  by {{ privacyData.updater.first_name }}
                  {{ privacyData.updater.last_name }}
                </span>
              </p>
            </div>

            <!-- PDF column -->
            <div>
              <div class="rounded-xl border border-secondary-200 bg-secondary-50 p-4">
                <p class="admin-form-section-title">PDF document</p>
                <p class="admin-form-section-desc">
                  The uploaded PDF is shown to users who open the Privacy Policy.
                </p>

                <div
                  v-if="privacyPdfStatus.exists"
                  class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-success-200 bg-success-50 p-3"
                >
                  <div
                    class="flex min-w-0 items-center gap-2 text-body-sm text-success-700"
                  >
                    <Icon
                      name="heroicons:document-check"
                      class="h-5 w-5 shrink-0 text-success-500"
                    />
                    <span class="truncate">
                      {{ getDisplayFileName(privacyPdfStatus.fileInfo?.original_filename) }}
                      ({{ formatFileSize(privacyPdfStatus.fileInfo?.size) }})
                    </span>
                  </div>
                  <button
                    type="button"
                    class="admin-btn admin-btn--outline admin-btn--sm"
                    @click="downloadPdf('privacy')"
                  >
                    <Icon name="heroicons:arrow-down-tray" class="h-4 w-4" />
                    Download
                  </button>
                </div>

                <div
                  v-else
                  class="mt-4 flex items-center gap-2 rounded-lg border border-warning-200 bg-warning-50 p-3 text-body-sm text-warning-700"
                >
                  <Icon
                    name="heroicons:exclamation-triangle"
                    class="h-5 w-5 shrink-0 text-warning-600"
                  />
                  No PDF uploaded yet — upload one below.
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                  <input
                    ref="privacyFileInput"
                    type="file"
                    accept="application/pdf"
                    class="hidden"
                    @change="handlePrivacyFileSelect"
                  />
                  <button
                    type="button"
                    class="admin-btn admin-btn--primary admin-btn--sm"
                    :disabled="uploadingPrivacyPdf"
                    @click="$refs.privacyFileInput.click()"
                  >
                    <Icon
                      v-if="!uploadingPrivacyPdf"
                      name="heroicons:arrow-up-tray"
                      class="h-4 w-4"
                    />
                    <Icon
                      v-else
                      name="heroicons:arrow-path"
                      class="h-4 w-4 animate-spin"
                    />
                    {{ uploadingPrivacyPdf ? "Uploading…" : "Choose PDF file" }}
                  </button>
                  <span class="admin-hint !mt-0">Max 10 MB</span>
                </div>
              </div>
            </div>
          </div>

          <div
            class="mt-6 flex flex-wrap items-center justify-end gap-3 border-t border-secondary-200 pt-5"
          >
            <button
              type="button"
              class="admin-btn admin-btn--outline"
              @click="previewDocument('privacy')"
            >
              <Icon name="heroicons:eye" class="h-4 w-4" />
              Preview
            </button>
            <button
              type="submit"
              :disabled="savingPrivacy"
              class="admin-btn admin-btn--primary"
            >
              <Icon
                v-if="savingPrivacy"
                name="heroicons:arrow-path"
                class="h-4 w-4 animate-spin"
              />
              <Icon v-else name="heroicons:check" class="h-4 w-4" />
              {{ savingPrivacy ? "Saving…" : "Save changes" }}
            </button>
          </div>
        </form>
      </div>
    </AdminSectionCard>

    <!-- Preview modal -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="showPreview"
          class="admin-dialog-backdrop"
          @click.self="showPreview = false"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            :aria-label="
              previewType === 'terms' ? 'Terms of Service' : 'Privacy Policy'
            "
            style="width: min(64rem, 100%); margin-top: 5vh; max-height: 90vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
                >
                  <Icon name="heroicons:document-text" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">
                    {{
                      previewType === "terms" ? "Terms of Service" : "Privacy Policy"
                    }}
                  </p>
                  <p class="admin-panel-subtitle">Document preview</p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="showPreview = false"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <div class="admin-panel-body flex-1 overflow-y-auto">
              <PdfViewer
                :url="previewPdfUrl"
                :require-auth="true"
                min-height="calc(90vh - 200px)"
                @loaded="onPdfLoaded"
                @error="onPdfError"
              />
            </div>

            <div class="admin-dialog-foot">
              <button
                type="button"
                class="admin-btn admin-btn--outline"
                @click="showPreview = false"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
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
  version: 1.0,
  effective_date: new Date().toISOString().split("T")[0],
  updated_at: null,
  updater: null,
});

const privacyData = reactive({
  content: "",
  version: 1.0,
  effective_date: new Date().toISOString().split("T")[0],
  updated_at: null,
  updater: null,
});

// Version validation errors
const versionErrors = reactive({
  terms: "",
  privacy: "",
});

// Validate Terms version
const validateTermsVersion = () => {
  const version = termsData.version;
  if (version === null || version === undefined || version === '') {
    termsData.version = 1.0;
    versionErrors.terms = "";
  } else if (typeof version === 'number' && version < 1.0) {
    versionErrors.terms = "Version cannot be less than 1.0";
    termsData.version = 1.0;
  } else {
    versionErrors.terms = "";
  }
};

// Validate Privacy version
const validatePrivacyVersion = () => {
  const version = privacyData.version;
  if (version === null || version === undefined || version === '') {
    privacyData.version = 1.0;
    versionErrors.privacy = "";
  } else if (typeof version === 'number' && version < 1.0) {
    versionErrors.privacy = "Version cannot be less than 1.0";
    privacyData.version = 1.0;
  } else {
    versionErrors.privacy = "";
  }
};

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
