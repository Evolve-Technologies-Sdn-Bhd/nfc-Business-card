<!-- pages/AdminManagement/chatbot-faq.vue -->
<template>
  <div>
    <!-- Header -->
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="page-title text-secondary-900">Chatbot Management</h1>
          <p class="mt-2 text-secondary-600">
            Manage the FAQ knowledge base and review user feedback
          </p>
        </div>
        <div class="mt-4 sm:mt-0">
          <button v-if="activeTab === 'faq'" @click="openCreate" class="btn btn-primary">
            <Icon name="heroicons:plus" class="h-5 w-5 mr-2" />
            Add Question
          </button>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="mb-6 border-b border-secondary-200">
      <nav class="flex space-x-6">
        <button
          @click="activeTab = 'faq'"
          :class="[
            'pb-3 text-navigation border-b-2 transition-colors',
            activeTab === 'faq'
              ? 'border-primary-600 text-primary-600'
              : 'border-transparent text-secondary-500 hover:text-secondary-700',
          ]"
        >
          FAQ Knowledge Base
        </button>
        <button
          @click="switchToFeedback"
          :class="[
            'pb-3 text-navigation border-b-2 transition-colors',
            activeTab === 'feedback'
              ? 'border-primary-600 text-primary-600'
              : 'border-transparent text-secondary-500 hover:text-secondary-700',
          ]"
        >
          User Feedback
        </button>
      </nav>
    </div>

    <!-- ================= FAQ TAB ================= -->
    <template v-if="activeTab === 'faq'">
      <!-- Filters -->
      <div class="card mb-6">
        <div class="card-body">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-label text-secondary-700 mb-2">Search</label>
              <input
                v-model="faqFilters.search"
                type="text"
                maxlength="150"
                placeholder="Search questions..."
                class="input"
                @input="loadFaq"
              />
            </div>
            <div>
              <label class="block text-label text-secondary-700 mb-2">Status</label>
              <select v-model="faqFilters.is_active" class="input" @change="loadFaq">
                <option value="all">All</option>
                <option value="true">Active</option>
                <option value="false">Inactive</option>
              </select>
            </div>
            <div class="flex items-end">
              <button @click="resetFaqFilters" class="btn btn-outline btn-sm">Clear Filters</button>
            </div>
          </div>
        </div>
      </div>

      <!-- FAQ list -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title text-secondary-900">
            Questions ({{ faqPagination.total }})
          </h3>
        </div>
        <div class="card-body">
          <div v-if="faqLoading" class="flex justify-center py-12">
            <div class="spinner text-primary-600"></div>
          </div>

          <div v-else-if="faqList.length" class="space-y-4">
            <div
              v-for="item in faqList"
              :key="item.id"
              class="border border-secondary-200 rounded-lg p-4 hover:shadow-md transition-shadow"
            >
              <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                  <div class="flex items-center flex-wrap gap-2 mb-2">
                    <span
                      :class="[
                        'px-2 py-1 text-badge rounded-full',
                        item.is_active ? 'bg-success-100 text-success-800' : 'bg-secondary-100 text-secondary-600',
                      ]"
                    >
                      {{ item.is_active ? "Active" : "Inactive" }}
                    </span>
                    <span class="px-2 py-1 text-badge rounded-full bg-blue-100 text-blue-800">
                      Priority {{ item.priority }}
                    </span>
                    <span class="text-caption text-secondary-500">
                      {{ item.view_count }} views
                    </span>
                  </div>
                  <h4 class="font-semibold text-secondary-900">{{ item.question }}</h4>
                  <p class="text-body text-secondary-600 mt-1 line-clamp-2">{{ item.answer }}</p>
                  <div v-if="item.keywords && item.keywords.length" class="mt-2 flex flex-wrap gap-1">
                    <span
                      v-for="kw in item.keywords"
                      :key="kw"
                      class="px-2 py-0.5 text-badge bg-secondary-100 text-secondary-600 rounded"
                    >
                      {{ kw }}
                    </span>
                  </div>
                </div>
                <div class="flex items-center space-x-2 flex-shrink-0">
                  <button
                    @click="toggleActive(item)"
                    class="text-secondary-500 hover:text-secondary-700"
                    :title="item.is_active ? 'Deactivate' : 'Activate'"
                  >
                    <Icon :name="item.is_active ? 'heroicons:toggle-on' : 'heroicons:toggle-off'" class="h-6 w-6" />
                  </button>
                  <button
                    @click="openEdit(item)"
                    class="text-warning-600 hover:text-warning-800"
                    title="Edit"
                  >
                    <Icon name="heroicons:pencil" class="h-4 w-4" />
                  </button>
                  <button
                    @click="deleteFaq(item)"
                    class="text-error-600 hover:text-error-800"
                    title="Delete"
                  >
                    <Icon name="heroicons:trash" class="h-4 w-4" />
                  </button>
                </div>
              </div>
            </div>

            <AdminPagination
              :current-page="faqPagination.current_page"
              :last-page="faqPagination.last_page"
              :per-page="faqPagination.per_page"
              :total="faqPagination.total"
              item-label="questions"
              @page-change="changeFaqPage"
              @per-page-change="changeFaqPerPage"
            />
          </div>

          <div v-else class="text-center py-12">
            <Icon name="heroicons:question-mark-circle" class="h-12 w-12 mx-auto text-secondary-300 mb-4" />
            <p class="text-secondary-500">No questions found</p>
          </div>
        </div>
      </div>
    </template>

    <!-- ================= FEEDBACK TAB ================= -->
    <template v-else>
      <!-- Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="card p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-lg bg-blue-100">
              <Icon name="heroicons:chat-bubble-left-right" class="h-6 w-6 text-blue-600" />
            </div>
            <div class="ml-4">
              <p class="stat-label text-secondary-600">Total Feedback</p>
              <p class="stat-value text-secondary-900">{{ feedbackStats.total_feedback || 0 }}</p>
            </div>
          </div>
        </div>
        <div class="card p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-lg bg-yellow-100">
              <Icon name="heroicons:bell" class="h-6 w-6 text-yellow-600" />
            </div>
            <div class="ml-4">
              <p class="stat-label text-secondary-600">Unread</p>
              <p class="stat-value text-secondary-900">{{ feedbackStats.unread_feedback || 0 }}</p>
            </div>
          </div>
        </div>
        <div class="card p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-lg bg-orange-100">
              <Icon name="heroicons:exclamation-triangle" class="h-6 w-6 text-orange-600" />
            </div>
            <div class="ml-4">
              <p class="stat-label text-secondary-600">Pending</p>
              <p class="stat-value text-secondary-900">{{ feedbackStats.pending_feedback || 0 }}</p>
            </div>
          </div>
        </div>
        <div class="card p-6">
          <div class="flex items-center">
            <div class="p-3 rounded-lg bg-green-100">
              <Icon name="heroicons:star" class="h-6 w-6 text-green-600" />
            </div>
            <div class="ml-4">
              <p class="stat-label text-secondary-600">Avg Rating</p>
              <p class="stat-value text-secondary-900">{{ feedbackStats.average_rating || 0 }} / 5</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Feedback list -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title text-secondary-900">
            Feedback ({{ feedbackPagination.total }})
          </h3>
        </div>
        <div class="card-body">
          <div v-if="feedbackLoading" class="flex justify-center py-12">
            <div class="spinner text-primary-600"></div>
          </div>
          <div v-else-if="feedbackList.length" class="space-y-4">
            <div
              v-for="fb in feedbackList"
              :key="fb.id"
              class="border border-secondary-200 rounded-lg p-4"
              :class="{ 'bg-blue-50': !fb.is_read }"
            >
              <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                  <div class="flex items-center flex-wrap gap-2 mb-2">
                    <span
                      :class="[
                        'px-2 py-1 text-badge rounded-full',
                        getStatusBadgeClass(fb.status),
                      ]"
                    >
                      {{ getStatusLabel(fb.status) }}
                    </span>
                    <span v-if="!fb.is_read" class="px-2 py-1 text-badge bg-red-100 text-red-800 rounded-full">
                      Unread
                    </span>
                  </div>
                  <h4 class="font-semibold text-secondary-900">{{ fb.user_name || "Anonymous" }}</h4>
                  <p class="card-subtitle">{{ fb.user_email || "No email provided" }}</p>
                  <p class="text-body text-secondary-700 mt-2 line-clamp-2">{{ fb.user_message }}</p>
                </div>
                <div class="flex items-center space-x-2 flex-shrink-0">
                  <button
                    v-if="!fb.is_read"
                    @click="markFeedbackRead(fb)"
                    class="text-primary-600 hover:text-primary-800"
                    title="Mark as read"
                  >
                    <Icon name="heroicons:envelope-open" class="h-5 w-5" />
                  </button>
                  <button
                    @click="deleteFeedback(fb)"
                    class="text-error-600 hover:text-error-800"
                    title="Delete"
                  >
                    <Icon name="heroicons:trash" class="h-4 w-4" />
                  </button>
                </div>
              </div>
              <div class="mt-2 text-caption text-secondary-500">{{ formatDate(fb.created_at) }}</div>
            </div>

            <AdminPagination
              :current-page="feedbackPagination.current_page"
              :last-page="feedbackPagination.last_page"
              :per-page="feedbackPagination.per_page"
              :total="feedbackPagination.total"
              item-label="results"
              @page-change="changeFeedbackPage"
              @per-page-change="changeFeedbackPerPage"
            />
          </div>
          <div v-else class="text-center py-12">
            <Icon name="heroicons:chat-bubble-left-right" class="h-12 w-12 mx-auto text-secondary-300 mb-4" />
            <p class="text-secondary-500">No feedback found</p>
          </div>
        </div>
      </div>
    </template>

    <!-- Create / Edit Question Modal -->
    <div
      v-if="showFormModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
      @click.self="closeFormModal"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-secondary-200">
          <h3 class="modal-title text-secondary-900">
            {{ editingId ? "Edit Question" : "Add Question" }}
          </h3>
        </div>
        <form @submit.prevent="saveFaq" class="p-6 space-y-4">
          <div>
            <label class="block text-label text-secondary-700 mb-2">Question *</label>
            <input v-model="form.question" type="text" required maxlength="500" class="input" />
          </div>
          <div>
            <label class="block text-label text-secondary-700 mb-2">Answer *</label>
            <textarea v-model="form.answer" required rows="5" maxlength="5000" class="input"></textarea>
          </div>
          <div>
            <label class="block text-label text-secondary-700 mb-2">Keywords</label>
            <input
              v-model="form.keywords"
              type="text"
              placeholder="e.g. price, pricing, cost, plan"
              class="input"
            />
            <p class="text-helper mt-1">Separate keywords with commas.</p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-label text-secondary-700 mb-2">Priority</label>
              <input v-model.number="form.priority" type="number" min="0" max="1000" class="input" />
              <p class="text-helper mt-1">Higher priority surfaces first.</p>
            </div>
            <div>
              <label class="block text-label text-secondary-700 mb-2">Status</label>
              <select v-model="form.is_active" class="input">
                <option :value="true">Active</option>
                <option :value="false">Inactive</option>
              </select>
            </div>
          </div>
          <div class="flex justify-end space-x-3 pt-2">
            <button type="button" @click="closeFormModal" class="btn btn-outline">Cancel</button>
            <button type="submit" :disabled="submitting" class="btn btn-primary">
              <div v-if="submitting" class="spinner mr-2"></div>
              {{ editingId ? "Update Question" : "Create Question" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: "admin-management",
  middleware: "admin",
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
const feedbackFilters = ref({ status: "all", currentPage: 1 });
const feedbackItemsPerPage = ref(10);
const feedbackPagination = ref({ total: 0, per_page: 10, current_page: 1, last_page: 1 });
let feedbackLoadedOnce = false;

// ================= FAQ =================
const loadFaq = async () => {
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

const deleteFaq = async (item) => {
  if (!confirm(`Are you sure you want to delete "${item.question}"? This action cannot be undone.`)) return;
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
};

// ================= FEEDBACK =================
const switchToFeedback = () => {
  activeTab.value = "feedback";
  if (!feedbackLoadedOnce) {
    feedbackLoadedOnce = true;
    loadFeedback();
    loadFeedbackStats();
  }
};

const loadFeedback = async () => {
  feedbackLoading.value = true;
  try {
    const query = new URLSearchParams({
      per_page: feedbackItemsPerPage.value,
      page: feedbackFilters.value.currentPage,
    });
    if (feedbackFilters.value.status !== "all") query.append("status", feedbackFilters.value.status);

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

const deleteFeedback = async (fb) => {
  if (!confirm("Are you sure you want to delete this feedback?")) return;
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
};

// ---- Helpers ----
const getStatusBadgeClass = (status) => {
  const classes = {
    pending: "bg-yellow-100 text-yellow-800",
    in_progress: "bg-blue-100 text-blue-800",
    resolved: "bg-green-100 text-green-800",
  };
  return classes[status] || "bg-secondary-100 text-secondary-700";
};

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

const formatDate = (date) => {
  if (!date) return "";
  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

onMounted(loadFaq);
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
