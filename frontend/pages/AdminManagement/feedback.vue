<!-- pages/AdminManagement/feedback.vue -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Voice of Customer"
      eyebrow-icon="heroicons:chat-bubble-left-right"
      title="Feedback Management"
      description="Triage every user report in one queue — mark items read, track their status and record internal notes."
    />

    <!-- KPI row -->
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <AdminStatTile
        label="Total feedback"
        :value="statistics?.total_feedback || 0"
        icon="heroicons:chat-bubble-left-right"
        :loading="loadingStats"
      />
      <AdminStatTile
        label="Unread"
        :value="statistics?.unread_feedback || 0"
        icon="heroicons:bell"
        :loading="loadingStats"
      />
      <AdminStatTile
        label="Pending"
        :value="statistics?.pending_feedback || 0"
        icon="heroicons:exclamation-triangle"
        :loading="loadingStats"
      />
      <AdminStatTile
        label="Average rating"
        :value="`${statistics?.average_rating || '0'} / 5`"
        icon="heroicons:star"
        :loading="loadingStats"
      />
    </div>

    <!-- Filters -->
    <AdminFilterBar @submit="applyFilters">
      <template #search>
        <div class="mb-[0.35rem] flex items-baseline justify-between gap-2">
          <label class="admin-label !mb-0">Search</label>
          <span class="text-helper text-secondary-400">
            {{ filters.search.length }}/150
          </span>
        </div>
        <div class="relative">
          <Icon
            name="heroicons:magnifying-glass"
            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-secondary-400"
          />
          <input
            v-model="filters.search"
            type="text"
            maxlength="150"
            placeholder="Search by name or email…"
            class="admin-field !pl-9 !pr-9"
            @input="onSearchInput"
          />
          <button
            v-if="filters.search"
            type="button"
            class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md p-1 text-secondary-400 transition hover:bg-secondary-100 hover:text-secondary-600"
            aria-label="Clear search"
            @click="clearSearch"
          >
            <Icon name="heroicons:x-mark" class="h-4 w-4" />
          </button>
        </div>
      </template>

      <div class="min-w-[10rem]">
        <label class="admin-label">Status</label>
        <select
          v-model="filters.status"
          class="admin-field"
          @change="applyFilters"
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
          v-model="filters.category"
          class="admin-field"
          @change="applyFilters"
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

      <div class="min-w-[10rem]">
        <label class="admin-label">Read status</label>
        <select
          v-model="filters.is_read"
          class="admin-field"
          @change="applyFilters"
        >
          <option value="">All</option>
          <option value="true">Read</option>
          <option value="false">Unread</option>
        </select>
      </div>

      <template #actions>
        <span class="w-full text-right text-body-sm text-secondary-500 sm:w-auto">
          {{ pagination.total }} found
        </span>
        <button
          type="button"
          class="admin-btn admin-btn--outline admin-btn--sm"
          @click="resetFilters"
        >
          <Icon name="heroicons:x-mark" class="h-4 w-4" />
          Reset
        </button>
      </template>
    </AdminFilterBar>

    <!-- Feedback list -->
    <AdminSectionCard>
      <template #title>Feedback inbox</template>
      <template #header-actions>
        <span class="text-caption text-secondary-500">
          {{ pagination.total }} item{{ pagination.total === 1 ? "" : "s" }}
        </span>
      </template>

      <!-- Loading -->
      <AdminLoadingState v-if="loading" variant="cards" :rows="6" />

      <!-- Empty -->
      <AdminEmptyState
        v-else-if="!feedbackList.length"
        icon="heroicons:chat-bubble-left-right"
        title="No feedback found"
        description="Nothing matches the current filters. Adjust or reset them to see more."
      />

      <!-- Cards -->
      <div
        v-else
        class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3"
      >
        <article
          v-for="feedback in feedbackList"
          :key="feedback.id"
          class="admin-panel flex flex-col p-5"
          :class="{ 'admin-panel-accent': !feedback.is_read }"
        >
          <div class="flex flex-wrap items-center gap-2">
            <AdminStatusBadge
              :tone="categoryTone(feedback.category)"
              :label="getCategoryLabel(feedback.category)"
            />
            <AdminStatusBadge
              :tone="statusTone(feedback.status)"
              :label="getStatusLabel(feedback.status)"
            />
            <AdminStatusBadge
              v-if="!feedback.is_read"
              tone="danger"
              label="Unread"
            />
            <span
              v-if="feedback.rating"
              class="ml-auto text-warning-500"
              :title="`${feedback.rating} / 5`"
            >
              {{ "★".repeat(feedback.rating) }}
            </span>
          </div>

          <div class="mt-3">
            <p class="text-body font-semibold text-secondary-900">
              {{ feedback.user_name || "Anonymous" }}
            </p>
            <p class="truncate text-caption text-secondary-500">
              {{ feedback.user_email || "No email provided" }}
            </p>
          </div>

          <p class="mt-3 line-clamp-2 text-body text-secondary-700">
            {{ feedback.user_message }}
          </p>

          <div
            class="mt-auto flex flex-wrap items-center justify-between gap-3 pt-4"
          >
            <span class="text-caption text-secondary-500">
              {{ formatDate(feedback.created_at) }} ·
              {{ formatTime(feedback.created_at) }}
            </span>
            <button
              type="button"
              class="admin-btn admin-btn--outline admin-btn--sm"
              @click="selectedFeedback = feedback"
            >
              <Icon name="heroicons:eye" class="h-4 w-4" />
              Details
            </button>
          </div>
        </article>
      </div>
    </AdminSectionCard>

    <AdminPagination
      v-if="feedbackList.length > 0"
      :current-page="pagination.current_page"
      :last-page="pagination.last_page"
      :per-page="itemsPerPage"
      :total="pagination.total"
      item-label="results"
      @page-change="changePage"
      @per-page-change="changeItemsPerPage"
    />

    <!-- Feedback detail dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="selectedFeedback"
          class="admin-dialog-backdrop"
          @click.self="selectedFeedback = null"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="Feedback details"
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
                  <p class="admin-panel-title">Feedback details</p>
                  <p class="admin-panel-subtitle">
                    {{ formatDate(selectedFeedback.created_at) }} ·
                    {{ formatTime(selectedFeedback.created_at) }}
                  </p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="selectedFeedback = null"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <div class="admin-panel-body flex-1 space-y-6 overflow-y-auto">
              <!-- From -->
              <div>
                <p class="admin-form-section-title">From</p>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <p class="text-caption text-secondary-500">Name</p>
                    <p class="text-body font-medium text-secondary-900">
                      {{ selectedFeedback.user_name || "Anonymous" }}
                    </p>
                  </div>
                  <div>
                    <p class="text-caption text-secondary-500">Email</p>
                    <p class="text-body font-medium break-all text-secondary-900">
                      {{ selectedFeedback.user_email || "Not provided" }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Classification -->
              <div>
                <p class="admin-form-section-title">Classification</p>
                <div class="mt-4 flex flex-wrap items-center gap-2">
                  <AdminStatusBadge
                    :tone="categoryTone(selectedFeedback.category)"
                    :label="getCategoryLabel(selectedFeedback.category)"
                  />
                  <AdminStatusBadge
                    :tone="statusTone(selectedFeedback.status)"
                    :label="getStatusLabel(selectedFeedback.status)"
                  />
                  <AdminStatusBadge
                    :tone="selectedFeedback.is_read ? 'success' : 'danger'"
                    :label="selectedFeedback.is_read ? 'Read' : 'Unread'"
                  />
                  <AdminStatusBadge
                    v-if="selectedFeedback.rating"
                    tone="warning"
                    :label="`${selectedFeedback.rating} / 5 ★`"
                  />
                </div>
              </div>

              <!-- Conversation context -->
              <div
                v-if="
                  selectedFeedback.user_question || selectedFeedback.bot_response
                "
              >
                <p class="admin-form-section-title">Conversation context</p>
                <div class="mt-4 space-y-4">
                  <div v-if="selectedFeedback.user_question">
                    <p class="text-caption font-medium text-secondary-500">
                      User question
                    </p>
                    <div
                      class="mt-1.5 rounded-xl border border-secondary-200 bg-secondary-50 p-3 text-body text-secondary-700"
                    >
                      {{ selectedFeedback.user_question }}
                    </div>
                  </div>
                  <div v-if="selectedFeedback.bot_response">
                    <p class="text-caption font-medium text-secondary-500">
                      Bot response
                    </p>
                    <div
                      class="mt-1.5 rounded-xl border border-info-200 border-l-4 border-l-info-400 bg-info-50 p-3 text-body text-secondary-700"
                    >
                      {{ selectedFeedback.bot_response }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Message -->
              <div v-if="selectedFeedback.user_message">
                <p class="admin-form-section-title">Message</p>
                <div
                  class="mt-4 rounded-xl border border-secondary-200 bg-secondary-50 p-4"
                >
                  <span
                    v-if="selectedFeedback.feedback_type"
                    class="admin-badge admin-badge--neutral admin-badge--plain"
                  >
                    {{
                      selectedFeedback.feedback_type === "thumbs_up"
                        ? "Positive"
                        : selectedFeedback.feedback_type === "thumbs_down"
                        ? "Negative"
                        : "Comment"
                    }}
                  </span>
                  <p class="mt-2 whitespace-pre-wrap text-body text-secondary-800">
                    {{ selectedFeedback.user_message }}
                  </p>
                </div>
              </div>

              <!-- Handling -->
              <div>
                <p class="admin-form-section-title">Handling</p>
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div>
                    <label class="admin-label">Update status</label>
                    <select v-model="newStatus" class="admin-field">
                      <option value="pending">Pending</option>
                      <option value="in_progress">In Progress</option>
                      <option value="resolved">Resolved</option>
                    </select>
                    <p class="admin-hint">
                      Changes apply immediately once saved.
                    </p>
                  </div>
                  <div>
                    <label class="admin-label">Admin notes</label>
                    <textarea
                      v-model="adminNotes"
                      rows="3"
                      placeholder="Internal notes…"
                      class="admin-field"
                    ></textarea>
                  </div>
                </div>
              </div>

              <!-- Origin -->
              <div v-if="selectedFeedback.ip_address">
                <p class="admin-form-section-title">Origin</p>
                <p class="mt-2 font-mono text-body text-secondary-900">
                  {{ selectedFeedback.ip_address }}
                </p>
              </div>
            </div>

            <div class="admin-dialog-foot">
              <button
                type="button"
                class="admin-btn admin-btn--danger mr-auto"
                :disabled="updatingFeedback"
                @click="deleteFeedback"
              >
                <Icon name="heroicons:trash" class="h-4 w-4" />
                Delete
              </button>
              <button
                type="button"
                class="admin-btn admin-btn--ghost"
                @click="selectedFeedback = null"
              >
                Close
              </button>
              <button
                v-if="!selectedFeedback.is_read"
                type="button"
                class="admin-btn admin-btn--outline"
                :disabled="updatingFeedback"
                @click="markAsRead"
              >
                <Icon
                  v-if="updatingFeedback"
                  name="heroicons:arrow-path"
                  class="h-4 w-4 animate-spin"
                />
                Mark as read
              </button>
              <button
                type="button"
                class="admin-btn admin-btn--primary"
                :disabled="
                  updatingFeedback || newStatus === selectedFeedback.status
                "
                @click="updateFeedbackStatus"
              >
                <Icon
                  v-if="updatingFeedback"
                  name="heroicons:arrow-path"
                  class="h-4 w-4 animate-spin"
                />
                Update status
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

const loading = ref(false);
const loadingStats = ref(false);
const updatingFeedback = ref(false);
const selectedFeedback = ref(null);
const adminNotes = ref('');
const newStatus = ref('pending');

const feedbackList = ref([]);
const statistics = ref(null);

const filters = ref({
  search: '',
  status: 'all',
  category: 'all',
  is_read: '',
  currentPage: 1,
});

const itemsPerPage = ref(10);

const pagination = ref({
  total: 0,
  per_page: 10,
  current_page: 1,
  last_page: 1,
});

// Confirmation dialog state
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

// Load feedback data
const loadFeedback = async () => {
  loading.value = true;
  try {
    const query = new URLSearchParams({
      per_page: itemsPerPage.value,
      page: filters.value.currentPage,
    });

    if (filters.value.search) query.append('search', filters.value.search);
    if (filters.value.status !== 'all') query.append('status', filters.value.status);
    if (filters.value.category !== 'all') query.append('category', filters.value.category);
    if (filters.value.is_read) query.append('is_read', filters.value.is_read);

    const response = await $api.get(`/admin/chatbot/feedback?${query}`);

    if (response.success) {
      feedbackList.value = response.data;
      pagination.value = response.pagination;
    }
  } catch (error) {
    console.error('Failed to load feedback:', error);
    $toast.error('Failed to load feedback');
  } finally {
    loading.value = false;
  }
};

// Load statistics
const loadStatistics = async () => {
  loadingStats.value = true;
  try {
    const response = await $api.get('/admin/chatbot/feedback/statistics');
    if (response.success) {
      statistics.value = response.data;
    }
  } catch (error) {
    console.error('Failed to load statistics:', error);
  } finally {
    loadingStats.value = false;
  }
};

// Apply filters
let searchTimer = null;
const applyFilters = () => {
  if (searchTimer) {
    clearTimeout(searchTimer);
    searchTimer = null;
  }
  filters.value.currentPage = 1;
  loadFeedback();
};

// Debounced search input (400ms)
const onSearchInput = () => {
  if (searchTimer) clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    searchTimer = null;
    applyFilters();
  }, 400);
};

// Clear the search box immediately
const clearSearch = () => {
  filters.value.search = '';
  applyFilters();
};

// Reset filters
const resetFilters = () => {
  filters.value = {
    search: '',
    status: 'all',
    category: 'all',
    is_read: '',
    currentPage: 1,
  };
  itemsPerPage.value = 10;
  loadFeedback();
};

// Pagination
const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    filters.value.currentPage = page;
    loadFeedback();
  }
};

const changeItemsPerPage = (val) => {
  itemsPerPage.value = val;
  filters.value.currentPage = 1;
  loadFeedback();
};

// Mark as read
const markAsRead = async () => {
  updatingFeedback.value = true;
  try {
    const response = await $api.put(`/admin/chatbot/feedback/${selectedFeedback.value.id}/read`);

    if (response.success) {
      selectedFeedback.value.is_read = true;
      $toast.success('Marked as read');
      loadFeedback();
      loadStatistics();
      // Notify layout to update unread badge immediately
      window.dispatchEvent(new CustomEvent('admin-feedback-updated'));
    }
  } catch (error) {
    console.error('Failed to mark as read:', error);
    $toast.error('Failed to mark as read');
  } finally {
    updatingFeedback.value = false;
  }
};

// Update status
const updateFeedbackStatus = async () => {
  updatingFeedback.value = true;
  try {
    const response = await $api.put(`/admin/chatbot/feedback/${selectedFeedback.value.id}/status`, {
      status: newStatus.value,
      admin_notes: adminNotes.value,
    });

    if (response.success) {
      $toast.success('Status updated');
      selectedFeedback.value = null;
      loadFeedback();
      loadStatistics();
      // Notify layout to update unread badge immediately
      window.dispatchEvent(new CustomEvent('admin-feedback-updated'));
    }
  } catch (error) {
    console.error('Failed to update status:', error);
    $toast.error('Failed to update status');
  } finally {
    updatingFeedback.value = false;
  }
};

// Delete feedback
const deleteFeedback = () => {
  askConfirm(
    {
      title: "Delete feedback",
      message:
        "Are you sure you want to delete this feedback? This action cannot be undone.",
      confirmLabel: "Delete",
      tone: "danger",
    },
    async () => {
      updatingFeedback.value = true;
      try {
        const response = await $api.delete(`/admin/chatbot/feedback/${selectedFeedback.value.id}`);

        if (response.success) {
          $toast.success('Feedback deleted');
          selectedFeedback.value = null;
          loadFeedback();
          loadStatistics();
          // Notify layout to update unread badge immediately
          window.dispatchEvent(new CustomEvent('admin-feedback-updated'));
        }
      } catch (error) {
        console.error('Failed to delete feedback:', error);
        $toast.error('Failed to delete feedback');
      } finally {
        updatingFeedback.value = false;
      }
    }
  );
};

// Watchers
watch(selectedFeedback, (newFeedback) => {
  if (newFeedback) {
    adminNotes.value = newFeedback.admin_notes || '';
    newStatus.value = newFeedback.status;
  }
});

// Helper functions
const getCategoryLabel = (category) => {
  const labels = {
    bug: 'Bug Report',
    feature: 'Feature Request',
    question: 'Question',
    complaint: 'Complaint',
    suggestion: 'Suggestion',
    other: 'Other',
  };
  return labels[category] || category;
};

const CATEGORY_TONES = {
  bug: 'danger',
  feature: 'brass',
  question: 'info',
  complaint: 'warning',
  suggestion: 'success',
  other: 'neutral',
};
const categoryTone = (category) => CATEGORY_TONES[category] || 'neutral';

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Pending',
    in_progress: 'In Progress',
    resolved: 'Resolved',
    new: 'New',
    open: 'Open',
    closed: 'Closed',
  };
  const label = labels[status];
  if (label) return label;
  // Fallback: convert snake_case to Title Case
  return String(status || 'Unknown')
    .toLowerCase()
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (c) => c.toUpperCase())
    .trim() || 'Unknown';
};

const STATUS_TONES = {
  pending: 'warning',
  in_progress: 'info',
  resolved: 'success',
  new: 'info',
  open: 'info',
  closed: 'neutral',
};
const statusTone = (status) => STATUS_TONES[status] || 'neutral';

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const formatTime = (date) => {
  return new Date(date).toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true,
  });
};

// Load data on mount
onMounted(() => {
  loadFeedback();
  loadStatistics();

  // Refresh statistics every 30 seconds
  const interval = setInterval(loadStatistics, 30000);
  onBeforeUnmount(() => {
    clearInterval(interval);
    if (searchTimer) clearTimeout(searchTimer);
  });
});
</script>
