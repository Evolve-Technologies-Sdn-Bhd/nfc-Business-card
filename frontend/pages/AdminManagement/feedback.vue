<!-- pages/AdminManagement/feedback.vue -->
<template>
  <div>
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-secondary-900">Feedback Management</h1>
      <p class="mt-2 text-secondary-600">
        View and manage user feedback and reports
      </p>
    </div>

    <!-- Statistics Cards -->
    <div v-if="!loadingStats" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="p-3 rounded-lg bg-blue-100">
              <Icon name="heroicons:chat-bubble-left-right" class="h-6 w-6 text-blue-600" />
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-secondary-600">Total Feedback</p>
            <p class="text-2xl font-semibold text-secondary-900">
              {{ statistics?.total_feedback || 0 }}
            </p>
          </div>
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="p-3 rounded-lg bg-yellow-100">
              <Icon name="heroicons:bell" class="h-6 w-6 text-yellow-600" />
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-secondary-600">Unread</p>
            <p class="text-2xl font-semibold text-secondary-900">
              {{ statistics?.unread_feedback || 0 }}
            </p>
          </div>
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="p-3 rounded-lg bg-orange-100">
              <Icon name="heroicons:exclamation-triangle" class="h-6 w-6 text-orange-600" />
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-secondary-600">Pending</p>
            <p class="text-2xl font-semibold text-secondary-900">
              {{ statistics?.pending_feedback || 0 }}
            </p>
          </div>
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="p-3 rounded-lg bg-green-100">
              <Icon name="heroicons:star" class="h-6 w-6 text-green-600" />
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-secondary-600">Avg Rating</p>
            <p class="text-2xl font-semibold text-secondary-900">
              {{ statistics?.average_rating || '0' }} / 5
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters Section -->
    <div class="card p-6 mb-8">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Search -->
        <div>
          <label class="block text-sm font-medium text-secondary-700 mb-2">Search</label>
          <input
            v-model="filters.search"
            type="text"
            maxlength="150"
            placeholder="Search by name, email..."
            class="w-full px-4 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            @input="applyFilters"
          />
          <p class="text-xs text-secondary-500 mt-1">{{ filters.search.length }}/150</p>
        </div>

        <!-- Status Filter -->
        <div>
          <label class="block text-sm font-medium text-secondary-700 mb-2">Status</label>
          <select
            v-model="filters.status"
            class="w-full px-4 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            @change="applyFilters"
          >
            <option value="all">All</option>
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="resolved">Resolved</option>
          </select>
        </div>

        <!-- Category Filter -->
        <div>
          <label class="block text-sm font-medium text-secondary-700 mb-2">Category</label>
          <select
            v-model="filters.category"
            class="w-full px-4 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            @change="applyFilters"
          >
            <option value="all">All</option>
            <option value="bug">🐛 Bug Report</option>
            <option value="feature">💡 Feature Request</option>
            <option value="question">❓ Question</option>
            <option value="complaint">😞 Complaint</option>
            <option value="suggestion">💭 Suggestion</option>
            <option value="other">📝 Other</option>
          </select>
        </div>

        <!-- Read Status Filter -->
        <div>
          <label class="block text-sm font-medium text-secondary-700 mb-2">Read Status</label>
          <select
            v-model="filters.is_read"
            class="w-full px-4 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            @change="applyFilters"
          >
            <option value="">All</option>
            <option value="true">Read</option>
            <option value="false">Unread</option>
          </select>
        </div>

        <!-- Reset Filters -->
        <div class="flex items-end">
          <button
            @click="resetFilters"
            class="w-full py-2 px-4 bg-secondary-200 text-secondary-700 rounded-lg font-medium hover:bg-secondary-300 transition-colors"
          >
            Reset Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Feedback List -->
    <div class="card">
      <div class="card-header">
        <h3 class="text-lg font-medium text-secondary-900">
          Feedback Items ({{ pagination.total }})
        </h3>
      </div>

      <div class="card-body">
        <div v-if="loading" class="flex justify-center py-8">
          <div class="spinner"></div>
        </div>

        <div v-else-if="feedbackList.length">
          <div class="space-y-4">
            <div
              v-for="feedback in feedbackList"
              :key="feedback.id"
              class="border border-secondary-200 rounded-lg p-4 hover:shadow-md transition-shadow"
              :class="{ 'bg-blue-50': !feedback.is_read }"
            >
              <!-- Header -->
              <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                  <div class="flex items-center space-x-3 mb-2">
                    <!-- Category Badge -->
                    <span
                      :class="[
                        'px-3 py-1 text-xs font-medium rounded-full',
                        getCategoryBadgeClass(feedback.category),
                      ]"
                    >
                      {{ getCategoryLabel(feedback.category) }}
                    </span>

                    <!-- Status Badge -->
                    <span
                      :class="[
                        'px-3 py-1 text-xs font-medium rounded-full',
                        getStatusBadgeClass(feedback.status),
                      ]"
                    >
                      {{ feedback.status }}
                    </span>

                    <!-- Unread Badge -->
                    <span
                      v-if="!feedback.is_read"
                      class="px-3 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full"
                    >
                      Unread
                    </span>

                    <!-- Rating -->
                    <span v-if="feedback.rating" class="text-yellow-500">
                      {{ '⭐'.repeat(feedback.rating) }}
                    </span>
                  </div>

                  <h4 class="font-semibold text-secondary-900">
                    {{ feedback.user_name || 'Anonymous' }}
                  </h4>
                  <p class="text-sm text-secondary-500">
                    {{ feedback.user_email || 'No email provided' }}
                  </p>
                </div>

                <button
                  @click="selectedFeedback = feedback"
                  class="px-4 py-2 text-sm font-medium text-primary-600 hover:text-primary-700 transition-colors"
                >
                  View Details
                </button>
              </div>

              <!-- Message Preview -->
              <p class="text-secondary-700 mb-3 line-clamp-2">
                {{ feedback.user_message }}
              </p>

              <!-- Footer -->
              <div class="flex items-center justify-between text-xs text-secondary-500">
                <span>{{ formatDate(feedback.created_at) }}</span>
                <span>{{ formatTime(feedback.created_at) }}</span>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <AdminPagination
            :current-page="pagination.current_page"
            :last-page="pagination.last_page"
            :per-page="pagination.per_page"
            :total="pagination.total"
            item-label="results"
            @page-change="changePage"
            @per-page-change="changeItemsPerPage"
          />
        </div>

        <div v-else class="text-center py-12">
          <Icon name="heroicons:chat-bubble-left-right" class="h-12 w-12 mx-auto text-secondary-300 mb-4" />
          <p class="text-secondary-600">No feedback found</p>
        </div>
      </div>
    </div>

    <!-- Feedback Detail Modal -->
    <div
      v-if="selectedFeedback"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
      @click="selectedFeedback = null"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
        @click.stop
      >
        <!-- Modal Header -->
        <div class="p-6 border-b border-secondary-200 flex items-center justify-between">
          <div>
            <h3 class="text-xl font-bold text-secondary-900">Feedback Details</h3>
            <p class="text-sm text-secondary-600 mt-1">
              {{ formatDate(selectedFeedback.created_at) }} at {{ formatTime(selectedFeedback.created_at) }}
            </p>
          </div>
          <button
            @click="selectedFeedback = null"
            class="p-2 text-secondary-400 hover:text-secondary-600 hover:bg-secondary-100 rounded-lg transition-colors"
          >
            <Icon name="heroicons:x-mark" class="h-6 w-6" />
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-6">
          <!-- User Information -->
          <div>
            <h4 class="text-sm font-semibold text-secondary-700 mb-2">User Information</h4>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-xs text-secondary-600">Name</p>
                <p class="text-sm font-medium text-secondary-900">
                  {{ selectedFeedback.user_name || 'Anonymous' }}
                </p>
              </div>
              <div>
                <p class="text-xs text-secondary-600">Email</p>
                <p class="text-sm font-medium text-secondary-900">
                  {{ selectedFeedback.user_email || 'Not provided' }}
                </p>
              </div>
            </div>
          </div>

          <!-- Feedback Details -->
          <div>
            <h4 class="text-sm font-semibold text-secondary-700 mb-2">Feedback Details</h4>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-xs text-secondary-600">Category</p>
                <p class="text-sm font-medium text-secondary-900">
                  {{ getCategoryLabel(selectedFeedback.category) }}
                </p>
              </div>
              <div>
                <p class="text-xs text-secondary-600">Status</p>
                <p class="text-sm font-medium text-secondary-900 capitalize">
                  {{ selectedFeedback.status }}
                </p>
              </div>
              <div v-if="selectedFeedback.rating">
                <p class="text-xs text-secondary-600">Rating</p>
                <p class="text-sm font-medium text-yellow-500">
                  {{ '⭐'.repeat(selectedFeedback.rating) }} ({{ selectedFeedback.rating }}/5)
                </p>
              </div>
              <div>
                <p class="text-xs text-secondary-600">Read Status</p>
                <p class="text-sm font-medium text-secondary-900">
                  {{ selectedFeedback.is_read ? 'Read' : 'Unread' }}
                </p>
              </div>
            </div>
          </div>

          <!-- Message -->
          <div>
            <h4 class="text-sm font-semibold text-secondary-700 mb-2">Message</h4>
            <div class="bg-secondary-50 p-4 rounded-lg">
              <p class="text-secondary-800 whitespace-pre-wrap">
                {{ selectedFeedback.user_message }}
              </p>
            </div>
          </div>

          <!-- Admin Notes -->
          <div>
            <label class="block text-sm font-semibold text-secondary-700 mb-2">Admin Notes</label>
            <textarea
              v-model="adminNotes"
              rows="3"
              placeholder="Add your notes here..."
              class="w-full px-4 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            ></textarea>
          </div>

          <!-- Status Update -->
          <div>
            <label class="block text-sm font-semibold text-secondary-700 mb-2">Update Status</label>
            <select
              v-model="newStatus"
              class="w-full px-4 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            >
              <option value="pending">Pending</option>
              <option value="in_progress">In Progress</option>
              <option value="resolved">Resolved</option>
            </select>
          </div>

          <!-- IP Address -->
          <div v-if="selectedFeedback.ip_address" class="pt-4 border-t border-secondary-200">
            <p class="text-xs text-secondary-600">IP Address</p>
            <p class="text-sm font-mono text-secondary-900">{{ selectedFeedback.ip_address }}</p>
          </div>
        </div>

        <!-- Modal Actions -->
        <div class="p-6 border-t border-secondary-200 flex space-x-3">
          <button
            v-if="!selectedFeedback.is_read"
            @click="markAsRead"
            :disabled="updatingFeedback"
            class="flex-1 py-2 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:opacity-50"
          >
            {{ updatingFeedback ? 'Updating...' : 'Mark as Read' }}
          </button>

          <button
            @click="updateFeedbackStatus"
            :disabled="updatingFeedback || newStatus === selectedFeedback.status"
            class="flex-1 py-2 px-4 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors disabled:opacity-50"
          >
            {{ updatingFeedback ? 'Updating...' : 'Update Status' }}
          </button>

          <button
            @click="deleteFeedback"
            :disabled="updatingFeedback"
            class="py-2 px-4 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors disabled:opacity-50"
          >
            {{ updatingFeedback ? '...' : 'Delete' }}
          </button>

          <button
            @click="selectedFeedback = null"
            class="py-2 px-4 bg-secondary-200 text-secondary-700 rounded-lg font-medium hover:bg-secondary-300 transition-colors"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: "admin-management",
});

const route = useRoute();
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
const applyFilters = () => {
  filters.value.currentPage = 1;
  loadFeedback();
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

const changeItemsPerPage = () => {
  filters.value.currentPage = 1;
  loadFeedback();
};

// Compute visible page numbers
const visiblePages = computed(() => {
  const total = pagination.value.last_page || 1;
  const current = pagination.value.current_page || 1;
  const pages = [];
  
  let start = Math.max(1, current - 2);
  let end = Math.min(total, start + 4);
  
  // Adjust start if we're near the end
  if (end - start < 4) {
    start = Math.max(1, end - 4);
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  
  return pages;
});

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
const deleteFeedback = async () => {
  if (!confirm('Are you sure you want to delete this feedback?')) return;

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
    bug: '🐛 Bug Report',
    feature: '💡 Feature Request',
    question: '❓ Question',
    complaint: '😞 Complaint',
    suggestion: '💭 Suggestion',
    other: '📝 Other',
  };
  return labels[category] || category;
};

const getCategoryBadgeClass = (category) => {
  const classes = {
    bug: 'bg-red-100 text-red-800',
    feature: 'bg-purple-100 text-purple-800',
    question: 'bg-blue-100 text-blue-800',
    complaint: 'bg-orange-100 text-orange-800',
    suggestion: 'bg-green-100 text-green-800',
    other: 'bg-gray-100 text-gray-800',
  };
  return classes[category] || 'bg-gray-100 text-gray-800';
};

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-blue-100 text-blue-800',
    resolved: 'bg-green-100 text-green-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

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
  onBeforeUnmount(() => clearInterval(interval));
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.spinner {
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
