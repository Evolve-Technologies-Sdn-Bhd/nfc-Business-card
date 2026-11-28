<!-- pages/AdminManagement/card-templates.vue -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Design Library"
      eyebrow-icon="heroicons:rectangle-stack"
      title="Card Templates"
      description="Upload and curate the NFC card designs customers can pick from — track processing status and publish at a glance."
    >
      <template #actions>
        <button
          type="button"
          class="admin-btn admin-btn--outline"
          :disabled="loading"
          @click="loadTemplates"
        >
          <Icon
            name="heroicons:arrow-path"
            class="h-4 w-4"
            :class="{ 'animate-spin': loading }"
          />
          Refresh
        </button>
        <button
          type="button"
          class="admin-btn admin-btn--primary"
          @click="openUploadModal"
        >
          <Icon name="heroicons:plus" class="h-4 w-4" />
          Upload Template
        </button>
      </template>
    </AdminPageHeader>

    <!-- Library summary -->
    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <AdminStatTile
        label="Total templates"
        :value="templates.length"
        icon="heroicons:rectangle-stack"
        :loading="loading"
      />
      <AdminStatTile
        label="Active"
        :value="activeCount"
        icon="heroicons:check-circle"
        icon-class="!bg-success-50 !text-success-600"
        :loading="loading"
      />
      <AdminStatTile
        label="Processing"
        :value="processingCount"
        icon="heroicons:clock"
        icon-class="!bg-warning-50 !text-warning-600"
        :loading="loading"
      />
      <AdminStatTile
        label="Failed"
        :value="failedCount"
        icon="heroicons:x-circle"
        icon-class="!bg-error-50 !text-error-600"
        :loading="loading"
      />
    </div>

    <!-- Filters -->
    <AdminFilterBar>
      <template #search>
        <div class="relative">
          <label class="admin-label">Search</label>
          <div class="relative">
            <Icon
              name="heroicons:magnifying-glass"
              class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-secondary-400"
            />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search template name…"
              class="admin-field pl-9"
            />
          </div>
        </div>
      </template>

      <div class="min-w-[12rem]">
        <label class="admin-label">Status</label>
        <select v-model="currentFilter" class="admin-field">
          <option
            v-for="filter in filters"
            :key="filter.value"
            :value="filter.value"
          >
            {{ filter.label }}
          </option>
        </select>
      </div>

      <div class="min-w-[12rem]">
        <label class="admin-label">Plan</label>
        <select v-model="planFilter" class="admin-field">
          <option value="">All Plans</option>
          <option value="basic">Basic</option>
          <option value="premium">Premium</option>
          <option value="business">Business</option>
        </select>
      </div>

      <template #actions>
        <span class="text-body-sm text-secondary-500">
          {{ filteredTemplates.length }} of {{ templates.length }} shown
        </span>
        <button
          type="button"
          class="admin-btn admin-btn--outline admin-btn--sm"
          @click="clearFilters"
        >
          <Icon name="heroicons:x-mark" class="h-4 w-4" />
          Clear filters
        </button>
      </template>
    </AdminFilterBar>

    <!-- Template library -->
    <AdminSectionCard flush>
      <template #title>Template library</template>
      <template #header-actions>
        <span class="text-caption text-secondary-500">
          {{ templates.length }} template{{ templates.length === 1 ? "" : "s" }}
        </span>
      </template>

      <!-- Loading -->
      <AdminLoadingState v-if="loading" variant="cards" :rows="8" />

      <!-- Empty -->
      <AdminEmptyState
        v-else-if="!filteredTemplates.length"
        icon="heroicons:rectangle-stack"
        title="No templates found"
        :description="
          currentFilter === 'all' && !planFilter && !searchQuery
            ? 'Upload your first card template to get started.'
            : 'No templates match the current filters or search.'
        "
      >
        <template #actions>
          <button
            v-if="currentFilter === 'all' && !planFilter && !searchQuery"
            type="button"
            class="admin-btn admin-btn--primary"
            @click="openUploadModal"
          >
            <Icon name="heroicons:plus" class="h-4 w-4" />
            Upload Template
          </button>
        </template>
      </AdminEmptyState>

      <!-- Grid -->
      <div
        v-else
        class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
      >
        <article
          v-for="template in filteredTemplates"
          :key="template.id"
          class="admin-panel group flex flex-col overflow-hidden"
        >
          <div class="relative aspect-[5/3] bg-secondary-100">
            <!-- Artwork + designer placeholders filled with sample data -->
              <CardTemplateFace
                v-if="template.front_image_url"
                :template="template"
                :image-url="getImageUrl(template.front_image_url)"
                :alt="template.name"
                class="absolute inset-0 h-full w-full"
              />
            <div v-else class="flex h-full w-full flex-col items-center justify-center gap-2 text-secondary-400">
              <Icon name="heroicons:photo" class="h-12 w-12" />
              <span class="text-caption">No preview</span>
            </div>

            <!-- Status -->
            <div class="absolute left-2 top-2">
              <AdminStatusBadge
                :status="template.processing_status"
                :label="statusLabel(template.processing_status)"
              />
            </div>
            <div v-if="template.is_hidden" class="absolute right-2 top-2">
              <AdminStatusBadge tone="neutral" label="Hidden" />
            </div>

            <!-- Hover actions (desktop) -->
            <div
              class="absolute inset-0 hidden items-center justify-center gap-2 bg-black/50 opacity-0 transition-opacity focus-within:opacity-100 sm:flex group-hover:opacity-100"
            >
              <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white text-secondary-700 transition hover:bg-accent-100 hover:text-accent-700"
                title="Edit"
                @click="openEditModal(template)"
              >
                <Icon name="heroicons:pencil" class="h-5 w-5" />
              </button>
              <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white text-secondary-700 transition hover:bg-warning-50 hover:text-warning-700"
                :title="template.is_hidden ? 'Show' : 'Hide'"
                @click="toggleVisibility(template)"
              >
                <Icon
                  :name="template.is_hidden ? 'heroicons:eye' : 'heroicons:eye-slash'"
                  class="h-5 w-5"
                />
              </button>
              <button
                v-if="template.processing_status === 'failed'"
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white text-warning-600 transition hover:bg-warning-50"
                title="Retry Processing"
                @click="retryProcessing(template)"
              >
                <Icon name="heroicons:arrow-path" class="h-5 w-5" />
              </button>
              <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white text-error-600 transition hover:bg-error-50"
                title="Delete"
                @click="confirmDelete(template)"
              >
                <Icon name="heroicons:trash" class="h-5 w-5" />
              </button>
            </div>
          </div>

          <!-- Info -->
          <div class="flex flex-1 flex-col p-4">
            <div class="flex items-start justify-between gap-2">
              <h3 class="truncate font-semibold text-secondary-900">
                {{ template.name }}
              </h3>
              <span
                v-if="template.category"
                class="admin-badge admin-badge--neutral admin-badge--plain shrink-0"
              >
                {{ template.category }}
              </span>
            </div>
            <p class="mt-1 line-clamp-2 min-h-[2.5rem] text-caption text-secondary-500">
              {{ template.description || 'No description' }}
            </p>

            <div class="mt-3 flex flex-wrap gap-1">
              <AdminStatusBadge
                v-for="plan in template.plan_types || []"
                :key="plan"
                :tone="planTone(plan)"
                :label="plan"
              />
            </div>

            <div
              v-if="template.processing_status === 'failed' && template.processing_error"
              class="mt-2"
            >
              <p class="truncate text-caption text-error-600" :title="template.processing_error">
                Error: {{ template.processing_error }}
              </p>
            </div>

            <!-- Mobile actions -->
            <div class="mt-3 flex items-center gap-2 border-t border-secondary-100 pt-3 sm:hidden">
              <button
                type="button"
                class="admin-btn admin-btn--outline admin-btn--sm flex-1"
                @click="openEditModal(template)"
              >
                <Icon name="heroicons:pencil" class="h-4 w-4" />
                Edit
              </button>
              <button
                type="button"
                class="admin-btn admin-btn--outline admin-btn--sm"
                :title="template.is_hidden ? 'Show' : 'Hide'"
                @click="toggleVisibility(template)"
              >
                <Icon
                  :name="template.is_hidden ? 'heroicons:eye' : 'heroicons:eye-slash'"
                  class="h-4 w-4"
                />
              </button>
              <button
                v-if="template.processing_status === 'failed'"
                type="button"
                class="admin-btn admin-btn--outline admin-btn--sm"
                title="Retry"
                @click="retryProcessing(template)"
              >
                <Icon name="heroicons:arrow-path" class="h-4 w-4" />
              </button>
              <button
                type="button"
                class="admin-btn admin-btn--outline admin-btn--sm text-error-600 hover:bg-error-50"
                title="Delete"
                @click="confirmDelete(template)"
              >
                <Icon name="heroicons:trash" class="h-4 w-4" />
              </button>
            </div>
          </div>
        </article>
      </div>
    </AdminSectionCard>

    <!-- Upload Template dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="showUploadModal"
          class="admin-dialog-backdrop"
          @click.self="showUploadModal = false"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="Upload new template"
            style="width: min(44rem, 100%); margin-top: 5vh; max-height: 90vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
                >
                  <Icon name="heroicons:cloud-arrow-up" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">Upload new template</p>
                  <p class="admin-panel-subtitle">
                    Publish a fresh card design to the library.
                  </p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="showUploadModal = false"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <form class="flex min-h-0 flex-1 flex-col" @submit.prevent="uploadTemplate">
              <div class="admin-panel-body flex-1 space-y-6 overflow-y-auto">
                <div class="admin-form-section">
                  <p class="admin-form-section-title">Details</p>
                  <p class="admin-form-section-desc">
                    Name the design and describe it for the team.
                  </p>
                  <div class="mt-4 space-y-4">
                    <div>
                      <label class="admin-label">
                        Template name <span class="text-error-600">*</span>
                      </label>
                      <input
                        v-model="uploadForm.name"
                        type="text"
                        placeholder="e.g. Emerald Executive, Navy Signature"
                        class="admin-field"
                        maxlength="80"
                      />
                    </div>
                    <div>
                      <label class="admin-label">Description</label>
                      <textarea
                        v-model="uploadForm.description"
                        rows="2"
                        placeholder="Short description about this template design"
                        class="admin-field resize-none"
                        maxlength="200"
                      ></textarea>
                    </div>
                  </div>
                </div>

                <div class="admin-form-section">
                  <p class="admin-form-section-title">Category &amp; plan</p>
                  <p class="admin-form-section-desc">
                    Where it belongs and which tiers can use it.
                  </p>
                  <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                      <label class="admin-label">Category</label>
                      <select v-model="uploadForm.category" class="admin-field">
                        <option
                          v-for="cat in categoryOptions"
                          :key="cat.value"
                          :value="cat.value"
                        >
                          {{ cat.label }}
                        </option>
                      </select>
                    </div>
                    <div>
                      <label class="admin-label">
                        Plan <span class="text-error-600">*</span>
                      </label>
                      <select v-model="uploadForm.plan" class="admin-field">
                        <option
                          v-for="plan in planOptions"
                          :key="plan.value"
                          :value="plan.value"
                        >
                          {{ plan.label }} — {{ plan.description }}
                        </option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="admin-form-section">
                  <p class="admin-form-section-title">Artwork</p>
                  <p class="admin-form-section-desc">
                    PNG or JPG up to 10MB. Recommended 1050×630px (5:3).
                  </p>

                  <div class="mt-4 space-y-4">
                    <div>
                      <label class="admin-label">
                        Front design <span class="text-error-600">*</span>
                      </label>
                      <div
                        class="cursor-pointer rounded-lg border-2 border-dashed border-secondary-300 p-6 text-center transition-colors hover:border-accent-400"
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
                          <img
                            :src="uploadForm.frontPreview"
                            alt="Front Preview"
                            class="mx-auto max-h-32 rounded"
                          />
                        </div>
                        <Icon
                          v-else
                          name="heroicons:photo"
                          class="mx-auto mb-2 h-12 w-12 text-secondary-400"
                        />
                        <p class="text-body text-secondary-600">
                          {{
                            uploadForm.frontFile
                              ? uploadForm.frontFile.name
                              : 'Click or drag to upload front design'
                          }}
                        </p>
                      </div>
                    </div>

                    <div v-if="uploadForm.plan !== 'basic'">
                      <label class="admin-label">
                        Back design
                        <span class="text-caption text-secondary-400">(optional)</span>
                      </label>
                      <div
                        class="cursor-pointer rounded-lg border-2 border-dashed border-secondary-300 p-6 text-center transition-colors hover:border-accent-400"
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
                          <img
                            :src="uploadForm.backPreview"
                            alt="Back Preview"
                            class="mx-auto max-h-32 rounded"
                          />
                        </div>
                        <Icon
                          v-else
                          name="heroicons:photo"
                          class="mx-auto mb-2 h-12 w-12 text-secondary-400"
                        />
                        <p class="text-body text-secondary-600">
                          {{
                            uploadForm.backFile
                              ? uploadForm.backFile.name
                              : 'Click or drag to upload back design'
                          }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <div class="mt-4 rounded-xl border border-success-200 bg-success-50 p-4">
                    <div class="flex gap-3">
                      <Icon
                        name="heroicons:cloud-arrow-up"
                        class="mt-0.5 h-5 w-5 shrink-0 text-success-600"
                      />
                      <p class="text-body text-success-800">
                        Images upload to the Cloudinary CDN for fast worldwide delivery.
                        Your template is available immediately after upload.
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="admin-dialog-foot">
                <button
                  type="button"
                  class="admin-btn admin-btn--ghost"
                  @click="showUploadModal = false"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="admin-btn admin-btn--primary"
                  :disabled="uploading || _uploadSubmitLock || !isUploadFormValid"
                  :aria-busy="uploading"
                >
                  <Icon
                    v-if="uploading"
                    name="heroicons:arrow-path"
                    class="h-4 w-4 animate-spin"
                  />
                  {{ uploading ? 'Uploading…' : 'Upload & Process' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Edit Template dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="showEditModal && editForm"
          class="admin-dialog-backdrop"
          @click.self="showEditModal = false"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="Edit template"
            style="width: min(40rem, 100%); margin-top: 5vh; max-height: 90vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
                >
                  <Icon name="heroicons:pencil-square" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">Edit template</p>
                  <p class="admin-panel-subtitle">{{ editForm.name }}</p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="showEditModal = false"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <form class="flex min-h-0 flex-1 flex-col" @submit.prevent="saveEdit">
              <div class="admin-panel-body flex-1 space-y-6 overflow-y-auto">
                <div class="admin-form-section">
                  <p class="admin-form-section-title">Details</p>
                  <div class="mt-4 space-y-4">
                    <div>
                      <label class="admin-label">
                        Template name <span class="text-error-600">*</span>
                      </label>
                      <input
                        v-model="editForm.name"
                        type="text"
                        class="admin-field"
                        maxlength="80"
                      />
                    </div>
                    <div>
                      <label class="admin-label">Description</label>
                      <textarea
                        v-model="editForm.description"
                        rows="2"
                        class="admin-field resize-none"
                        maxlength="200"
                      ></textarea>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                      <div>
                        <label class="admin-label">Category</label>
                        <select v-model="editForm.category" class="admin-field">
                          <option
                            v-for="cat in categoryOptions"
                            :key="cat.value"
                            :value="cat.value"
                          >
                            {{ cat.label }}
                          </option>
                        </select>
                      </div>
                      <div>
                        <label class="admin-label">Sort order</label>
                        <input
                          v-model.number="editForm.sort_order"
                          type="number"
                          class="admin-field"
                        />
                      </div>
                    </div>
                    <div>
                      <label class="admin-label">Plan types</label>
                      <div
                        class="flex flex-wrap gap-4 rounded-lg border border-secondary-200 bg-secondary-50 p-3"
                      >
                        <label
                          v-for="plan in planOptions"
                          :key="plan.value"
                          class="flex cursor-pointer items-center gap-2"
                        >
                          <input
                            type="checkbox"
                            :value="plan.value"
                            v-model="editForm.plan_types"
                            class="h-4 w-4 rounded border-secondary-300 text-accent-600 focus:ring-accent-500"
                          />
                          <span class="text-body text-secondary-700">{{ plan.label }}</span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="admin-form-section">
                  <p class="admin-form-section-title">Artwork</p>
                  <p class="admin-form-section-desc">
                    Leave empty to keep the current image.
                  </p>

                  <div class="mt-4 space-y-4">
                    <!-- Front -->
                    <div>
                      <div class="flex items-center justify-between">
                        <label class="admin-label !mb-0">Front design</label>
                        <button
                          v-if="editFrontFile || editForm._removeFront"
                          type="button"
                          class="text-caption text-secondary-500 hover:text-error-600"
                          @click="revertFrontImage"
                        >
                          Revert to original
                        </button>
                      </div>
                      <div
                        class="mt-1 cursor-pointer rounded-lg border-2 border-dashed border-secondary-300 p-4 text-center transition-colors hover:border-accent-400"
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
                          <img
                            :src="editFrontPreview"
                            alt="Front Preview"
                            class="mx-auto max-h-28 rounded"
                          />
                        </div>
                        <div
                          v-else-if="editForm.front_image_url && !editForm._removeFront"
                          class="mb-3"
                        >
                          <img
                            :src="getImageUrl(editForm.front_image_url)"
                            alt="Current Front"
                            class="mx-auto max-h-28 rounded opacity-70"
                          />
                        </div>
                        <Icon
                          v-else
                          name="heroicons:photo"
                          class="mx-auto mb-2 h-10 w-10 text-secondary-400"
                        />
                        <p class="text-body text-secondary-600">
                          {{
                            editFrontFile
                              ? editFrontFile.name
                              : (editForm._removeFront
                                  ? '(Image will be removed)'
                                  : 'Click or drag to replace front design')
                          }}
                        </p>
                        <p class="text-helper mt-1">PNG, JPG up to 10MB</p>
                      </div>
                    </div>

                    <!-- Back -->
                    <div>
                      <div class="flex items-center justify-between">
                        <label class="admin-label !mb-0">Back design</label>
                        <button
                          v-if="editBackFile || editForm._removeBack"
                          type="button"
                          class="text-caption text-secondary-500 hover:text-error-600"
                          @click="revertBackImage"
                        >
                          Revert to original
                        </button>
                      </div>
                      <div
                        class="mt-1 cursor-pointer rounded-lg border-2 border-dashed border-secondary-300 p-4 text-center transition-colors hover:border-accent-400"
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
                          <img
                            :src="editBackPreview"
                            alt="Back Preview"
                            class="mx-auto max-h-28 rounded"
                          />
                        </div>
                        <div
                          v-else-if="editForm.back_image_url && !editForm._removeBack"
                          class="mb-3"
                        >
                          <img
                            :src="getImageUrl(editForm.back_image_url)"
                            alt="Current Back"
                            class="mx-auto max-h-28 rounded opacity-70"
                          />
                        </div>
                        <Icon
                          v-else
                          name="heroicons:photo"
                          class="mx-auto mb-2 h-10 w-10 text-secondary-400"
                        />
                        <p class="text-body text-secondary-600">
                          {{
                            editBackFile
                              ? editBackFile.name
                              : (editForm._removeBack
                                  ? '(Image will be removed)'
                                  : 'Click or drag to replace back design')
                          }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="admin-form-section">
                  <p class="admin-form-section-title">Visibility</p>
                  <div class="mt-4 flex flex-wrap items-center gap-4">
                    <label class="flex cursor-pointer items-center gap-2">
                      <input
                        type="checkbox"
                        v-model="editForm.is_active"
                        class="h-4 w-4 rounded border-secondary-300 text-accent-600 focus:ring-accent-500"
                      />
                      <span class="text-body text-secondary-700">Active</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2">
                      <input
                        type="checkbox"
                        v-model="editForm.is_hidden"
                        class="h-4 w-4 rounded border-secondary-300 text-accent-600 focus:ring-accent-500"
                      />
                      <span class="text-body text-secondary-700">Hidden</span>
                    </label>
                  </div>
                </div>
              </div>

              <div class="admin-dialog-foot">
                <button
                  type="button"
                  class="admin-btn admin-btn--ghost"
                  @click="showEditModal = false"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="admin-btn admin-btn--primary"
                  :disabled="saving || !isEditFormValid"
                >
                  <Icon
                    v-if="saving"
                    name="heroicons:arrow-path"
                    class="h-4 w-4 animate-spin"
                  />
                  {{ saving ? 'Saving…' : 'Save changes' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Shared confirmation dialog -->
    <AdminConfirmDialog
      v-model="confirmOpen"
      :title="confirmConfig.title"
      :message="confirmConfig.message"
      :confirm-label="confirmConfig.confirmLabel"
      :tone="confirmConfig.tone"
      :loading="confirmBusy"
      @confirm="runConfirm"
    />
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
const searchQuery = ref('');
const currentFilter = ref('all');
const planFilter = ref('');
const showUploadModal = ref(false);
const showEditModal = ref(false);
const uploading = ref(false);
const saving = ref(false);
const editForm = ref(null);

// Confirmation dialog state
const confirmOpen = ref(false);
const confirmBusy = ref(false);
const confirmConfig = ref({
  title: '',
  message: '',
  confirmLabel: 'Delete',
  tone: 'danger',
});
let confirmAction = null;

const askConfirm = (config, action) => {
  confirmConfig.value = { confirmLabel: 'Confirm', tone: 'primary', ...config };
  confirmAction = action;
  confirmOpen.value = true;
};

const runConfirm = async () => {
  if (!confirmAction) return;
  confirmBusy.value = true;
  try {
    await confirmAction();
  } finally {
    confirmBusy.value = false;
    confirmOpen.value = false;
    confirmAction = null;
  }
};

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

  // Text search (name or description)
  const q = searchQuery.value.trim().toLowerCase();
  if (q) {
    result = result.filter(t =>
      (t.name || '').toLowerCase().includes(q) ||
      (t.description || '').toLowerCase().includes(q)
    );
  }

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

// Status label helper (title-case for readability)
const statusLabel = (status) => {
  if (!status) return '';
  return status.charAt(0).toUpperCase() + status.slice(1);
};

// Plan badge tone helper
const planTone = (plan) => {
  const map = { basic: 'neutral', premium: 'info', business: 'brass' };
  return map[plan] || 'neutral';
};

// Clear all filters
const clearFilters = () => {
  searchQuery.value = '';
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
  askConfirm(
    {
      title: 'Delete template',
      message: `Are you sure you want to delete "${template.name}"? This action cannot be undone.`,
      confirmLabel: 'Delete',
      tone: 'danger',
    },
    async () => {
      try {
        const response = await $api.delete(`/admin/card-templates/${template.id}`);
        if (response.success) {
          $toast.success('Template deleted');
          await loadTemplates();
        }
      } catch (error) {
        console.error('Delete error:', error);
        $toast.error('Failed to delete template');
      }
    }
  );
};

// Lifecycle
onMounted(() => {
  loadTemplates();
});
</script>
