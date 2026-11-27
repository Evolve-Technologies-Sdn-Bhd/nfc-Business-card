<!-- pages/AdminManagement/chatbot-faq.vue -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Support"
      eyebrow-icon="heroicons:question-mark-circle"
      title="Chatbot Management"
      description="Curate the FAQ knowledge base and work through the feedback the assistant collects."
    >
      <template #actions>
        <button
          v-if="activeTab === 'faq'"
          type="button"
          class="admin-btn admin-btn--primary"
          @click="openCreate"
        >
          <Icon name="heroicons:plus" class="h-4 w-4" />
          Add question
        </button>
      </template>
    </AdminPageHeader>

    <!-- Tabs -->
    <div class="relative mb-6 flex">
      <span
        class="pointer-events-none absolute inset-x-0 bottom-0 h-px bg-secondary-200"
        aria-hidden="true"
      ></span>
      <button
        type="button"
        class="relative whitespace-nowrap border-b-2 px-4 pb-3 text-navigation transition-colors"
        :class="
          activeTab === 'faq'
            ? 'border-accent-500 text-secondary-900'
            : 'border-transparent text-secondary-500 hover:text-secondary-800'
        "
        @click="activeTab = 'faq'"
      >
        FAQ knowledge base
      </button>
      <button
        type="button"
        class="relative whitespace-nowrap border-b-2 px-4 pb-3 text-navigation transition-colors"
        :class="
          activeTab === 'feedback'
            ? 'border-accent-500 text-secondary-900'
            : 'border-transparent text-secondary-500 hover:text-secondary-800'
        "
        @click="switchToFeedback"
      >
        User feedback
      </button>
    </div>

    <!-- ================= FAQ TAB ================= -->
    <template v-if="activeTab === 'faq'">
      <AdminFilterBar @submit="loadFaq">
        <template #search>
          <div>
            <label class="admin-label">Search</label>
            <div class="relative">
              <Icon
                name="heroicons:magnifying-glass"
                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-secondary-400"
              />
              <input
                v-model="faqFilters.search"
                type="text"
                maxlength="150"
                placeholder="Search questions…"
                class="admin-field !pl-9 !pr-9"
                @input="onFaqSearchInput"
              />
              <button
                v-if="faqFilters.search"
                type="button"
                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md p-1 text-secondary-400 transition hover:bg-secondary-100 hover:text-secondary-600"
                aria-label="Clear search"
                @click="clearFaqSearch"
              >
                <Icon name="heroicons:x-mark" class="h-4 w-4" />
              </button>
            </div>
          </div>
        </template>

        <div class="min-w-[10rem]">
          <label class="admin-label">Status</label>
          <select
            v-model="faqFilters.is_active"
            class="admin-field"
            @change="loadFaq"
          >
            <option value="all">All statuses</option>
            <option value="true">Active</option>
            <option value="false">Inactive</option>
          </select>
        </div>

        <template #actions>
          <span class="text-body-sm text-secondary-500">
            {{ faqPagination.total }} question{{
              faqPagination.total === 1 ? "" : "s"
            }}
          </span>
          <button
            type="button"
            class="admin-btn admin-btn--outline admin-btn--sm"
            @click="resetFaqFilters"
          >
            <Icon name="heroicons:x-mark" class="h-4 w-4" />
            Reset
          </button>
        </template>
      </AdminFilterBar>

      <AdminSectionCard>
        <template #title>Knowledge base</template>
        <template #header-actions>
          <span class="text-caption text-secondary-500">
            {{ faqPagination.total }} entr{{
              faqPagination.total === 1 ? "y" : "ies"
            }}
          </span>
        </template>

        <!-- Loading -->
        <AdminLoadingState v-if="faqLoading" variant="cards" :rows="6" />

        <!-- Empty -->
        <AdminEmptyState
          v-else-if="!faqList.length"
          icon="heroicons:question-mark-circle"
          title="No questions found"
          description="Add your first FAQ entry, or adjust the filters above."
        >
          <template #actions>
            <button
              type="button"
              class="admin-btn admin-btn--primary"
              @click="openCreate"
            >
              <Icon name="heroicons:plus" class="h-4 w-4" />
              Add question
            </button>
          </template>
        </AdminEmptyState>

        <!-- Cards -->
        <div
          v-else
          class="grid grid-cols-1 gap-4 lg:grid-cols-2 xl:grid-cols-3"
        >
          <article
            v-for="item in faqList"
            :key="item.id"
            class="admin-panel flex flex-col p-5"
          >
            <div class="flex flex-wrap items-center gap-2">
              <AdminStatusBadge
                :tone="item.is_active ? 'success' : 'neutral'"
                :label="item.is_active ? 'Active' : 'Inactive'"
              />
              <AdminStatusBadge tone="info" :label="`Priority ${item.priority}`" />
              <span class="ml-auto text-caption text-secondary-500">
                {{ item.view_count }} views
              </span>
            </div>

            <h4 class="mt-3 text-body font-semibold text-secondary-900">
              {{ item.question }}
            </h4>
            <p class="mt-1 line-clamp-2 text-body text-secondary-600">
              {{ item.answer }}
            </p>

            <div
              v-if="item.keywords && item.keywords.length"
              class="mt-3 flex flex-wrap gap-1"
            >
              <span
                v-for="kw in item.keywords"
                :key="kw"
                class="admin-badge admin-badge--neutral admin-badge--plain"
              >
                {{ kw }}
              </span>
            </div>

            <div class="mt-auto flex items-center justify-end gap-1.5 pt-4">
              <button
                type="button"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-secondary-500 transition hover:bg-secondary-100 hover:text-primary-700"
                :title="item.is_active ? 'Deactivate' : 'Activate'"
                @click="toggleActive(item)"
              >
                <Icon
                  :name="
                    item.is_active
                      ? 'heroicons:no-symbol'
                      : 'heroicons:check-circle'
                  "
                  class="h-5 w-5"
                />
              </button>
              <button
                type="button"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-secondary-500 transition hover:bg-warning-50 hover:text-warning-700"
                title="Edit"
                @click="openEdit(item)"
              >
                <Icon name="heroicons:pencil" class="h-4 w-4" />
              </button>
              <button
                type="button"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-secondary-500 transition hover:bg-error-50 hover:text-error-600"
                title="Delete"
                @click="deleteFaq(item)"
              >
                <Icon name="heroicons:trash" class="h-4 w-4" />
              </button>
            </div>
          </article>
        </div>
      </AdminSectionCard>

      <AdminPagination
        v-if="faqList.length > 0"
        :current-page="faqPagination.current_page"
        :last-page="faqPagination.last_page"
        :per-page="faqItemsPerPage"
        :total="faqPagination.total"
        item-label="questions"
        @page-change="changeFaqPage"
        @per-page-change="changeFaqPerPage"
      />
    </template>

    <!-- ================= FEEDBACK TAB ================= -->
    <template v-else>
      <!-- KPI row -->
      <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <AdminStatTile
          label="Total feedback"
          :value="feedbackStats.total_feedback || 0"
          icon="heroicons:chat-bubble-left-right"
        />
        <AdminStatTile
          label="Unread"
          :value="feedbackStats.unread_feedback || 0"
          icon="heroicons:bell"
        />
        <AdminStatTile
          label="Pending"
          :value="feedbackStats.pending_feedback || 0"
          icon="heroicons:exclamation-triangle"
        />
        <AdminStatTile
          label="Average rating"
          :value="`${feedbackStats.average_rating || 0} / 5`"
          icon="heroicons:star"
        />
      </div>

      <!-- Filters -->
      <AdminFilterBar @submit="applyFeedbackFilters">
        <template #search>
          <div>
            <label class="admin-label">Search</label>
            <div class="relative">
              <Icon
                name="heroicons:magnifying-glass"
                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-secondary-400"
              />
              <input
                v-model="feedbackFilters.search"
                type="text"
                maxlength="150"
                placeholder="Search name, email, or message…"
                class="admin-field !pl-9 !pr-9"
                @input="onFeedbackSearchInput"
              />
              <button
                v-if="feedbackFilters.search"
                type="button"
                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md p-1 text-secondary-400 transition hover:bg-secondary-100 hover:text-secondary-600"
                aria-label="Clear search"
                @click="clearFeedbackSearch"
              >
                <Icon name="heroicons:x-mark" class="h-4 w-4" />
              </button>
            </div>
          </div>
        </template>

        <div class="min-w-[10rem]">
          <label class="admin-label">Status</label>
          <select
            v-model="feedbackFilters.status"
            class="admin-field"
            @change="applyFeedbackFilters"
          >
            <option value="all">All statuses</option>
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="resolved">Resolved</option>
          </select>
        </div>

        <div class="min-w-[10rem]">
          <label class="admin-label">Category</label>
          <select
            v-model="feedbackFilters.category"
            class="admin-field"
            @change="applyFeedbackFilters"
          >
            <option value="all">All categories</option>
            <option value="bug">Bug Report</option>
            <option value="feature">Feature Request</option>
            <option value="question">Question</option>
            <option value="complaint">Complaint</option>
            <option value="suggestion">Suggestion</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div class="min-w-[9rem]">
          <label class="admin-label">Read status</label>
          <select
            v-model="feedbackFilters.is_read"
            class="admin-field"
            @change="applyFeedbackFilters"
          >
            <option value="all">All</option>
            <option value="true">Read</option>
            <option value="false">Unread</option>
          </select>
        </div>

        <template #actions>
          <span class="text-body-sm text-secondary-500">
            {{ feedbackPagination.total }} item{{
              feedbackPagination.total === 1 ? "" : "s"
            }}
          </span>
          <button
            type="button"
            class="admin-btn admin-btn--outline admin-btn--sm"
            @click="resetFeedbackFilters"
          >
            <Icon name="heroicons:x-mark" class="h-4 w-4" />
            Reset
          </button>
        </template>
      </AdminFilterBar>

      <AdminSectionCard>
        <template #title>User feedback</template>
        <template #header-actions>
          <span class="text-caption text-secondary-500">
            {{ feedbackPagination.total }} item{{
              feedbackPagination.total === 1 ? "" : "s"
            }}
          </span>
        </template>

        <!-- Loading -->
        <AdminLoadingState v-if="feedbackLoading" variant="cards" :rows="6" />

        <!-- Empty -->
        <AdminEmptyState
          v-else-if="!feedbackList.length"
          icon="heroicons:chat-bubble-left-right"
          title="No feedback found"
          description="Nothing has come through the assistant yet."
        />

        <!-- Cards -->
        <div
          v-else
          class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3"
        >
          <article
            v-for="fb in feedbackList"
            :key="fb.id"
            class="admin-panel flex flex-col p-5"
            :class="{ 'admin-panel-accent': !fb.is_read }"
          >
            <div class="flex flex-wrap items-center gap-2">
              <AdminStatusBadge
                :tone="statusTone(fb.status)"
                :label="getStatusLabel(fb.status)"
              />
              <AdminStatusBadge
                v-if="!fb.is_read"
                tone="danger"
                label="Unread"
              />
              <span class="ml-auto text-caption text-secondary-500">
                {{ formatDate(fb.created_at) }}
              </span>
            </div>

            <p class="mt-3 text-body font-semibold text-secondary-900">
              {{ fb.user_name || "Anonymous" }}
            </p>
            <p class="truncate text-caption text-secondary-500">
              {{ fb.user_email || "No email provided" }}
            </p>
            <p class="mt-2 line-clamp-2 text-body text-secondary-700">
              {{ fb.user_message }}
            </p>

            <div class="mt-auto flex items-center justify-end gap-1.5 pt-4">
              <button
                type="button"
                class="admin-btn admin-btn--outline admin-btn--sm"
                title="View detail"
                @click="openFeedbackDetail(fb)"
              >
                <Icon name="heroicons:eye" class="h-4 w-4" />
                View
              </button>
              <button
                v-if="!fb.is_read"
                type="button"
                class="admin-btn admin-btn--outline admin-btn--sm"
                title="Mark as read"
                @click="markFeedbackRead(fb)"
              >
                <Icon name="heroicons:envelope-open" class="h-4 w-4" />
                Mark read
              </button>
              <button
                type="button"
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-secondary-500 transition hover:bg-error-50 hover:text-error-600"
                title="Delete"
                @click="deleteFeedback(fb)"
              >
                <Icon name="heroicons:trash" class="h-4 w-4" />
              </button>
            </div>
          </article>
        </div>
      </AdminSectionCard>

      <AdminPagination
        v-if="feedbackList.length > 0"
        :current-page="feedbackPagination.current_page"
        :last-page="feedbackPagination.last_page"
        :per-page="feedbackItemsPerPage"
        :total="feedbackPagination.total"
        item-label="results"
        @page-change="changeFeedbackPage"
        @per-page-change="changeFeedbackPerPage"
      />
    </template>

    <!-- Create / Edit question dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="showFormModal"
          class="admin-dialog-backdrop"
          @click.self="closeFormModal"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            :aria-label="editingId ? 'Edit question' : 'Add question'"
            style="width: min(44rem, 100%); margin-top: 5vh; max-height: 90vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
                >
                  <Icon
                    :name="editingId ? 'heroicons:pencil-square' : 'heroicons:plus'"
                    class="h-5 w-5"
                  />
                </span>
                <div>
                  <p class="admin-panel-title">
                    {{ editingId ? "Edit question" : "Add question" }}
                  </p>
                  <p class="admin-panel-subtitle">
                    {{
                      editingId
                        ? "Update this FAQ entry"
                        : "Add a new entry to the knowledge base"
                    }}
                  </p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="closeFormModal"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <form class="flex min-h-0 flex-1 flex-col" @submit.prevent="saveFaq">
              <div class="admin-panel-body flex-1 space-y-6 overflow-y-auto">
                <!-- Content -->
                <div class="admin-form-section">
                  <p class="admin-form-section-title">Content</p>
                  <p class="admin-form-section-desc">
                    The question users ask and the answer the assistant returns.
                  </p>
                  <div class="mt-4 space-y-4">
                    <div>
                      <label class="admin-label">
                        Question <span class="text-error-600">*</span>
                      </label>
                      <input
                        v-model="form.question"
                        type="text"
                        required
                        maxlength="500"
                        class="admin-field"
                      />
                      <span
                        class="text-helper mt-1 block text-right text-secondary-400"
                      >
                        {{ form.question.length }}/500
                      </span>
                    </div>
                    <div>
                      <label class="admin-label">
                        Answer <span class="text-error-600">*</span>
                      </label>
                      <textarea
                        v-model="form.answer"
                        required
                        rows="5"
                        maxlength="5000"
                        class="admin-field"
                      ></textarea>
                      <span
                        class="text-helper mt-1 block text-right text-secondary-400"
                      >
                        {{ form.answer.length }}/5000
                      </span>
                    </div>
                    <div>
                      <label class="admin-label">Keywords</label>
                      <input
                        v-model="form.keywords"
                        type="text"
                        placeholder="e.g. price, pricing, cost, plan"
                        class="admin-field"
                      />
                      <p class="admin-hint">Separate keywords with commas.</p>
                    </div>
                  </div>
                </div>

                <!-- Ranking & visibility -->
                <div class="admin-form-section">
                  <p class="admin-form-section-title">Ranking &amp; visibility</p>
                  <p class="admin-form-section-desc">
                    Control how prominently the entry surfaces.
                  </p>
                  <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                      <label class="admin-label">Priority</label>
                      <input
                        v-model.number="form.priority"
                        type="number"
                        min="0"
                        max="1000"
                        class="admin-field"
                      />
                      <p class="admin-hint">Higher priority surfaces first.</p>
                    </div>
                    <div>
                      <label class="admin-label">Status</label>
                      <select v-model="form.is_active" class="admin-field">
                        <option :value="true">Active</option>
                        <option :value="false">Inactive</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <div class="admin-dialog-foot">
                <button
                  type="button"
                  class="admin-btn admin-btn--ghost"
                  @click="closeFormModal"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="submitting"
                  class="admin-btn admin-btn--primary"
                >
                  <Icon
                    v-if="submitting"
                    name="heroicons:arrow-path"
                    class="h-4 w-4 animate-spin"
                  />
                  {{ editingId ? "Update question" : "Create question" }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Feedback detail / status dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="showFeedbackDetail"
          class="admin-dialog-backdrop"
          @click.self="closeFeedbackDetail"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="Feedback detail"
            style="width: min(44rem, 100%); margin-top: 5vh; max-height: 90vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
                >
                  <Icon name="heroicons:chat-bubble-left-right" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">Feedback detail</p>
                  <p class="admin-panel-subtitle">
                    Review the message and update its status.
                  </p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="closeFeedbackDetail"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <div
              v-if="selectedFeedback"
              class="admin-panel-body flex-1 space-y-6 overflow-y-auto"
            >
              <!-- Metadata -->
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <p class="text-helper font-medium uppercase tracking-wide text-secondary-500">
                    Submitted by
                  </p>
                  <p class="mt-1 text-body text-secondary-900">
                    {{ selectedFeedback.user_name || "Anonymous" }}
                  </p>
                  <p class="text-caption text-secondary-500">
                    {{ selectedFeedback.user_email || "No email provided" }}
                  </p>
                </div>
                <div>
                  <p class="text-helper font-medium uppercase tracking-wide text-secondary-500">
                    Received
                  </p>
                  <p class="mt-1 text-body text-secondary-900">
                    {{ formatDate(selectedFeedback.created_at) }}
                  </p>
                  <AdminStatusBadge
                    v-if="selectedFeedback.category"
                    class="mt-1"
                    tone="info"
                    :label="getCategoryLabel(selectedFeedback.category)"
                  />
                </div>
              </div>

              <div
                v-if="selectedFeedback.user_question"
                class="admin-panel rounded-lg bg-secondary-50 p-4"
              >
                <p class="text-helper font-medium uppercase tracking-wide text-secondary-500">
                  User question
                </p>
                <p class="mt-1 text-body text-secondary-800">
                  {{ selectedFeedback.user_question }}
                </p>
              </div>

              <div class="admin-panel rounded-lg bg-secondary-50 p-4">
                <p class="text-helper font-medium uppercase tracking-wide text-secondary-500">
                  Message
                </p>
                <p class="mt-1 whitespace-pre-wrap text-body text-secondary-800">
                  {{ selectedFeedback.user_message }}
                </p>
              </div>

              <div
                v-if="selectedFeedback.bot_response"
                class="admin-panel rounded-lg bg-secondary-50 p-4"
              >
                <p class="text-helper font-medium uppercase tracking-wide text-secondary-500">
                  Bot response
                </p>
                <p class="mt-1 whitespace-pre-wrap text-body text-secondary-800">
                  {{ selectedFeedback.bot_response }}
                </p>
              </div>

              <div
                v-if="selectedFeedback.admin_notes"
                class="admin-panel rounded-lg bg-accent-50 p-4"
              >
                <p class="text-helper font-medium uppercase tracking-wide text-accent-700">
                  Previous internal notes
                </p>
                <p class="mt-1 whitespace-pre-wrap text-body text-secondary-800">
                  {{ selectedFeedback.admin_notes }}
                </p>
              </div>

              <!-- Status & notes form -->
              <div class="admin-form-section">
                <p class="admin-form-section-title">Update status</p>
                <p class="admin-form-section-desc">
                  Set the workflow status and record internal notes.
                </p>
                <div class="mt-4 space-y-4">
                  <div>
                    <label class="admin-label">Status</label>
                    <select v-model="feedbackStatusForm.status" class="admin-field">
                      <option value="pending">Pending</option>
                      <option value="in_progress">In Progress</option>
                      <option value="resolved">Resolved</option>
                    </select>
                  </div>
                  <div>
                    <label class="admin-label">Internal notes</label>
                    <textarea
                      v-model="feedbackStatusForm.admin_notes"
                      rows="3"
                      maxlength="1000"
                      class="admin-field"
                      placeholder="Optional notes for your team…"
                    ></textarea>
                    <span
                      class="text-helper mt-1 block text-right text-secondary-400"
                    >
                      {{ feedbackStatusForm.admin_notes.length }}/1000
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="admin-dialog-foot">
              <button
                type="button"
                class="admin-btn admin-btn--ghost"
                @click="closeFeedbackDetail"
              >
                Close
              </button>
              <button
                type="button"
                :disabled="feedbackStatusSaving"
                class="admin-btn admin-btn--primary"
                @click="updateFeedbackStatus"
              >
                <Icon
                  v-if="feedbackStatusSaving"
                  name="heroicons:arrow-path"
                  class="h-4 w-4 animate-spin"
                />
                Save changes
              </button>
            </div>
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
definePageMeta({
  layout: "admin-management",
  middleware: ["auth", "admin"],
});

const { $api, $toast } = useNuxtApp();

const activeTab = ref("faq");

// ---- FAQ state ----
const faqLoading = ref(false);
const faqList = ref([]);
const faqFilters = ref({ search: "", is_active: "all", currentPage: 1 });
const faqItemsPerPage = ref(20);
const faqPagination = ref({ total: 0, per_page: 20, current_page: 1, last_page: 1 });

const showFormModal = ref(false);
const editingId = ref(null);
const submitting = ref(false);
const form = ref({ question: "", answer: "", keywords: "", priority: 0, is_active: true });

// ---- Feedback state ----
const feedbackLoading = ref(false);
const feedbackList = ref([]);
const feedbackStats = ref({});
const feedbackFilters = ref({
  search: "",
  status: "all",
  category: "all",
  is_read: "all",
  currentPage: 1,
});
const feedbackItemsPerPage = ref(10);
const feedbackPagination = ref({ total: 0, per_page: 10, current_page: 1, last_page: 1 });

// Feedback status form (for detail/status update)
const showFeedbackDetail = ref(false);
const selectedFeedback = ref(null);
const feedbackStatusForm = ref({ status: "pending", admin_notes: "" });
const feedbackStatusSaving = ref(false);

// ---- Confirmation dialog state ----
const confirmOpen = ref(false);
const confirmBusy = ref(false);
const confirmConfig = ref({
  title: "",
  message: "",
  confirmLabel: "Confirm",
  tone: "primary",
});
let confirmAction = null;

const askConfirm = (config, action) => {
  confirmConfig.value = { confirmLabel: "Confirm", tone: "primary", ...config };
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

// ================= FAQ =================
let faqSearchTimer = null;
const loadFaq = async () => {
  if (faqSearchTimer) {
    clearTimeout(faqSearchTimer);
    faqSearchTimer = null;
  }
  faqLoading.value = true;
  try {
    const query = new URLSearchParams({
      per_page: faqItemsPerPage.value,
      page: faqFilters.value.currentPage,
    });
    if (faqFilters.value.search) query.append("search", faqFilters.value.search);
    if (faqFilters.value.is_active !== "all") query.append("is_active", faqFilters.value.is_active);

    const response = await $api.get(`/admin/chatbot/questions?${query}`);
    if (response.success) {
      faqList.value = response.data;
      faqPagination.value = response.pagination;
    }
  } catch (error) {
    console.error("Failed to load FAQ questions:", error);
    $toast.error("Failed to load FAQ questions");
  } finally {
    faqLoading.value = false;
  }
};

const onFaqSearchInput = () => {
  if (faqSearchTimer) clearTimeout(faqSearchTimer);
  faqSearchTimer = setTimeout(() => {
    faqSearchTimer = null;
    faqFilters.value.currentPage = 1;
    loadFaq();
  }, 400);
};

const clearFaqSearch = () => {
  faqFilters.value.search = "";
  faqFilters.value.currentPage = 1;
  loadFaq();
};

const resetFaqFilters = () => {
  faqFilters.value = { search: "", is_active: "all", currentPage: 1 };
  loadFaq();
};

const changeFaqPage = (page) => {
  if (page >= 1 && page <= faqPagination.value.last_page) {
    faqFilters.value.currentPage = page;
    loadFaq();
  }
};

const changeFaqPerPage = (perPage) => {
  faqItemsPerPage.value = perPage;
  faqFilters.value.currentPage = 1;
  loadFaq();
};

const openCreate = () => {
  editingId.value = null;
  form.value = { question: "", answer: "", keywords: "", priority: 0, is_active: true };
  showFormModal.value = true;
};

const openEdit = (item) => {
  editingId.value = item.id;
  form.value = {
    question: item.question,
    answer: item.answer,
    keywords: (item.keywords || []).join(", "),
    priority: item.priority || 0,
    is_active: !!item.is_active,
  };
  showFormModal.value = true;
};

const closeFormModal = () => {
  showFormModal.value = false;
  editingId.value = null;
};

const parseKeywords = () =>
  form.value.keywords
    .split(",")
    .map((k) => k.trim())
    .filter((k) => k.length > 0);

const saveFaq = async () => {
  submitting.value = true;
  try {
    const payload = {
      question: form.value.question,
      answer: form.value.answer,
      keywords: parseKeywords(),
      priority: form.value.priority || 0,
      is_active: form.value.is_active,
    };

    const response = editingId.value
      ? await $api.put(`/admin/chatbot/questions/${editingId.value}`, payload)
      : await $api.post("/admin/chatbot/questions", payload);

    if (response.success) {
      $toast.success(editingId.value ? "Question updated" : "Question created");
      closeFormModal();
      loadFaq();
    } else {
      $toast.error(response.message || "Failed to save question");
    }
  } catch (error) {
    console.error("Failed to save question:", error);
    const errors = error?.data?.errors;
    let detailMsg = error?.data?.message || error?.message || "Failed to save question";
    if (errors && typeof errors === "object") {
      const firstField = Object.keys(errors)[0];
      if (firstField && Array.isArray(errors[firstField]) && errors[firstField][0]) {
        detailMsg = `${detailMsg}: ${errors[firstField][0]}`;
      }
    }
    $toast.error(detailMsg);
  } finally {
    submitting.value = false;
  }
};

const toggleActive = async (item) => {
  try {
    const response = await $api.post(`/admin/chatbot/questions/${item.id}/toggle-active`);
    if (response.success) {
      $toast.success(response.message || "Status updated");
      loadFaq();
    }
  } catch (error) {
    console.error("Failed to toggle question:", error);
    $toast.error("Failed to update question status");
  }
};

const deleteFaq = (item) => {
  askConfirm(
    {
      title: "Delete question",
      message: `Are you sure you want to delete "${item.question}"? This action cannot be undone.`,
      confirmLabel: "Delete",
      tone: "danger",
    },
    async () => {
      try {
        const response = await $api.delete(`/admin/chatbot/questions/${item.id}`);
        if (response.success) {
          $toast.success("Question deleted");
          loadFaq();
        }
      } catch (error) {
        console.error("Failed to delete question:", error);
        $toast.error("Failed to delete question");
      }
    }
  );
};

// ================= FEEDBACK =================
const switchToFeedback = () => {
  activeTab.value = "feedback";
  // Always refresh when switching to the feedback tab
  loadFeedback();
  loadFeedbackStats();
};

let feedbackSearchTimer = null;
const applyFeedbackFilters = () => {
  if (feedbackSearchTimer) {
    clearTimeout(feedbackSearchTimer);
    feedbackSearchTimer = null;
  }
  feedbackFilters.value.currentPage = 1;
  loadFeedback();
};

const onFeedbackSearchInput = () => {
  if (feedbackSearchTimer) clearTimeout(feedbackSearchTimer);
  feedbackSearchTimer = setTimeout(() => {
    feedbackSearchTimer = null;
    applyFeedbackFilters();
  }, 400);
};

const clearFeedbackSearch = () => {
  feedbackFilters.value.search = "";
  applyFeedbackFilters();
};

const resetFeedbackFilters = () => {
  feedbackFilters.value = {
    search: "",
    status: "all",
    category: "all",
    is_read: "all",
    currentPage: 1,
  };
  loadFeedback();
};

const loadFeedback = async () => {
  feedbackLoading.value = true;
  try {
    const query = new URLSearchParams({
      per_page: feedbackItemsPerPage.value,
      page: feedbackFilters.value.currentPage,
    });
    if (feedbackFilters.value.search) query.append("search", feedbackFilters.value.search);
    if (feedbackFilters.value.status !== "all") query.append("status", feedbackFilters.value.status);
    if (feedbackFilters.value.category !== "all") query.append("category", feedbackFilters.value.category);
    if (feedbackFilters.value.is_read !== "all") query.append("is_read", feedbackFilters.value.is_read === "true");

    const response = await $api.get(`/admin/chatbot/feedback?${query}`);
    if (response.success) {
      feedbackList.value = response.data;
      feedbackPagination.value = response.pagination;
    }
  } catch (error) {
    console.error("Failed to load feedback:", error);
    $toast.error("Failed to load feedback");
  } finally {
    feedbackLoading.value = false;
  }
};

const loadFeedbackStats = async () => {
  try {
    const response = await $api.get("/admin/chatbot/feedback/statistics");
    if (response.success) {
      feedbackStats.value = response.data;
    }
  } catch (error) {
    console.error("Failed to load feedback statistics:", error);
  }
};

const changeFeedbackPage = (page) => {
  if (page >= 1 && page <= feedbackPagination.value.last_page) {
    feedbackFilters.value.currentPage = page;
    loadFeedback();
  }
};

const changeFeedbackPerPage = (perPage) => {
  feedbackItemsPerPage.value = perPage;
  feedbackFilters.value.currentPage = 1;
  loadFeedback();
};

const markFeedbackRead = async (fb) => {
  try {
    const response = await $api.put(`/admin/chatbot/feedback/${fb.id}/read`);
    if (response.success) {
      $toast.success("Marked as read");
      loadFeedback();
      loadFeedbackStats();
      window.dispatchEvent(new CustomEvent("admin-feedback-updated"));
    }
  } catch (error) {
    console.error("Failed to mark feedback as read:", error);
    $toast.error("Failed to mark as read");
  }
};

const deleteFeedback = (fb) => {
  askConfirm(
    {
      title: "Delete feedback",
      message:
        "Are you sure you want to delete this feedback? This action cannot be undone.",
      confirmLabel: "Delete",
      tone: "danger",
    },
    async () => {
      try {
        const response = await $api.delete(`/admin/chatbot/feedback/${fb.id}`);
        if (response.success) {
          $toast.success("Feedback deleted");
          loadFeedback();
          loadFeedbackStats();
          window.dispatchEvent(new CustomEvent("admin-feedback-updated"));
        }
      } catch (error) {
        console.error("Failed to delete feedback:", error);
        $toast.error("Failed to delete feedback");
      }
    }
  );
};

// Feedback detail / status update
const openFeedbackDetail = (fb) => {
  selectedFeedback.value = fb;
  feedbackStatusForm.value = {
    status: fb.status || "pending",
    admin_notes: fb.admin_notes || "",
  };
  showFeedbackDetail.value = true;
};

const closeFeedbackDetail = () => {
  showFeedbackDetail.value = false;
  selectedFeedback.value = null;
};

const updateFeedbackStatus = async () => {
  if (!selectedFeedback.value) return;
  feedbackStatusSaving.value = true;
  try {
    const response = await $api.put(
      `/admin/chatbot/feedback/${selectedFeedback.value.id}/status`,
      {
        status: feedbackStatusForm.value.status,
        admin_notes: feedbackStatusForm.value.admin_notes,
      }
    );
    if (response.success) {
      $toast.success("Feedback status updated");
      closeFeedbackDetail();
      loadFeedback();
      loadFeedbackStats();
      window.dispatchEvent(new CustomEvent("admin-feedback-updated"));
    } else {
      $toast.error(response.message || "Failed to update status");
    }
  } catch (error) {
    console.error("Failed to update feedback status:", error);
    $toast.error("Failed to update status");
  } finally {
    feedbackStatusSaving.value = false;
  }
};

// ---- Helpers ----
const STATUS_TONES = {
  pending: "warning",
  in_progress: "info",
  resolved: "success",
};
const statusTone = (status) => STATUS_TONES[status] || "neutral";

const getStatusLabel = (status) => {
  const labels = {
    pending: "Pending",
    in_progress: "In Progress",
    resolved: "Resolved",
  };
  return labels[status] || String(status || "Unknown")
    .toLowerCase()
    .replace(/_/g, " ")
    .replace(/\b\w/g, (c) => c.toUpperCase());
};

const getCategoryLabel = (category) => {
  const labels = {
    bug: "Bug Report",
    feature: "Feature Request",
    question: "Question",
    complaint: "Complaint",
    suggestion: "Suggestion",
    other: "Other",
  };
  return labels[category] || String(category || "");
};

const formatDate = (date) => {
  if (!date) return "";
  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

onMounted(loadFaq);

onBeforeUnmount(() => {
  if (faqSearchTimer) clearTimeout(faqSearchTimer);
  if (feedbackSearchTimer) clearTimeout(feedbackSearchTimer);
});
</script>
