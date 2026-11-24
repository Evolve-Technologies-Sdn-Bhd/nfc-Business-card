<!-- pages/AdminManagement/refunds.vue -->
<template>
  <div>
    <!-- Header -->
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="page-title text-secondary-900">Refund Requests</h1>
          <p class="mt-2 text-secondary-600">
            Review and process pending refund requests
          </p>
        </div>
        <div class="mt-4 sm:mt-0">
          <button @click="loadRefunds" class="btn btn-outline" :disabled="loading">
            <Icon name="heroicons:arrow-path" class="h-5 w-5 mr-2" />
            Refresh
          </button>
        </div>
      </div>
    </div>

    <!-- Pending refunds -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title text-secondary-900">
          Pending Refunds ({{ pagination.total }})
        </h3>
      </div>
      <div class="card-body p-0">
        <div v-if="loading" class="flex justify-center py-12">
          <div class="spinner text-primary-600"></div>
        </div>
        <div v-else-if="refunds.length" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-secondary-200">
            <thead class="bg-secondary-50">
              <tr>
                <th class="px-3 sm:px-6 py-3 text-left table-head">Refund</th>
                <th class="px-3 sm:px-6 py-3 text-left table-head">User</th>
                <th class="px-3 sm:px-6 py-3 text-left table-head">Transaction</th>
                <th class="px-3 sm:px-6 py-3 text-left table-head">Amount</th>
                <th class="px-3 sm:px-6 py-3 text-left table-head">Reason</th>
                <th class="px-3 sm:px-6 py-3 text-left table-head">Requested</th>
                <th class="px-3 sm:px-6 py-3 text-left table-head">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-secondary-200">
              <tr v-for="refund in refunds" :key="refund.id" class="hover:bg-secondary-50">
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                  <div class="table-cell font-mono text-secondary-900">{{ refund.refund_id }}</div>
                  <span class="mt-1 inline-block px-2 py-0.5 text-badge bg-yellow-100 text-yellow-800 rounded-full">
                    Pending
                  </span>
                </td>
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                  <div class="table-cell font-medium text-secondary-900">
                    {{ refund.user?.full_name || refund.user?.name || "Unknown" }}
                  </div>
                  <div class="table-cell text-secondary-500">{{ refund.user?.email || "N/A" }}</div>
                </td>
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                  <div class="table-cell font-mono text-secondary-700">
                    {{ refund.transaction?.transaction_id || "N/A" }}
                  </div>
                  <div v-if="refund.transaction?.order_id" class="text-caption text-secondary-500">
                    Order #{{ refund.transaction.order_id }}
                  </div>
                </td>
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                  <div class="table-cell font-semibold text-secondary-900">
                    {{ formatAmount(refund.amount, refund.currency) }}
                  </div>
                </td>
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                  <span class="px-2 py-1 text-badge rounded-full bg-secondary-100 text-secondary-700">
                    {{ getReasonLabel(refund.reason) }}
                  </span>
                </td>
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap table-cell text-secondary-500">
                  {{ formatDate(refund.created_at) }}
                </td>
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap table-cell font-medium">
                  <button @click="openReview(refund)" class="btn btn-outline btn-sm">
                    <Icon name="heroicons:eye" class="h-4 w-4 mr-1" />
                    Review
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center py-12">
          <Icon name="heroicons:banknotes" class="h-12 w-12 mx-auto text-secondary-300 mb-4" />
          <p class="text-secondary-500">No pending refund requests</p>
        </div>
      </div>
    </div>

    <AdminPagination
      v-if="refunds.length > 0"
      :current-page="pagination.current_page"
      :last-page="pagination.last_page"
      :per-page="pagination.per_page"
      :total="pagination.total"
      item-label="refunds"
      @page-change="changePage"
      @per-page-change="changeItemsPerPage"
    />

    <!-- Review modal -->
    <div
      v-if="selectedRefund"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
      @click.self="closeReview"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-secondary-200">
          <h3 class="modal-title text-secondary-900">Review Refund</h3>
          <p class="mt-1 card-subtitle font-mono">{{ selectedRefund.refund_id }}</p>
        </div>
        <div class="p-6 space-y-4">
          <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <dt class="text-caption font-medium text-secondary-500">User</dt>
              <dd class="text-body text-secondary-900">
                {{ selectedRefund.user?.full_name || selectedRefund.user?.name || "Unknown" }}
              </dd>
              <dd class="text-caption text-secondary-500">{{ selectedRefund.user?.email || "N/A" }}</dd>
            </div>
            <div>
              <dt class="text-caption font-medium text-secondary-500">Amount</dt>
              <dd class="text-body font-semibold text-secondary-900">
                {{ formatAmount(selectedRefund.amount, selectedRefund.currency) }}
              </dd>
            </div>
            <div>
              <dt class="text-caption font-medium text-secondary-500">Transaction</dt>
              <dd class="text-body font-mono text-secondary-900">
                {{ selectedRefund.transaction?.transaction_id || "N/A" }}
              </dd>
            </div>
            <div>
              <dt class="text-caption font-medium text-secondary-500">Provider</dt>
              <dd class="text-body text-secondary-900 capitalize">{{ selectedRefund.provider || "N/A" }}</dd>
            </div>
            <div>
              <dt class="text-caption font-medium text-secondary-500">Reason</dt>
              <dd class="text-body text-secondary-900">{{ getReasonLabel(selectedRefund.reason) }}</dd>
            </div>
            <div>
              <dt class="text-caption font-medium text-secondary-500">Requested</dt>
              <dd class="text-body text-secondary-900">{{ formatDate(selectedRefund.created_at) }}</dd>
            </div>
          </dl>

          <div v-if="selectedRefund.notes">
            <p class="text-caption font-medium text-secondary-500 mb-1">User Notes</p>
            <div class="bg-secondary-50 rounded-lg p-3 text-body text-secondary-700">
              {{ selectedRefund.notes }}
            </div>
          </div>

          <div>
            <label class="block text-label text-secondary-700 mb-2">Admin Notes</label>
            <textarea
              v-model="notes"
              rows="3"
              maxlength="500"
              placeholder="Optional notes for this decision..."
              class="input"
            ></textarea>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-secondary-200 flex justify-end space-x-3">
          <button type="button" @click="closeReview" class="btn btn-outline">Cancel</button>
          <button
            type="button"
            @click="process('reject')"
            :disabled="processing"
            class="btn btn-error"
          >
            <div v-if="processing" class="spinner mr-2"></div>
            Reject
          </button>
          <button
            type="button"
            @click="process('approve')"
            :disabled="processing"
            class="btn btn-success"
          >
            <div v-if="processing" class="spinner mr-2"></div>
            Approve
          </button>
        </div>
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

const loading = ref(false);
const processing = ref(false);
const refunds = ref([]);
const pagination = ref({ total: 0, per_page: 20, current_page: 1, last_page: 1 });

const selectedRefund = ref(null);
const notes = ref("");
const itemsPerPage = ref(20);

const loadRefunds = async () => {
  loading.value = true;
  try {
    const response = await $api.get("/admin/payments/refunds/pending", {
      params: { per_page: itemsPerPage.value, page: pagination.value.current_page },
    });
    if (response.success && response.refunds) {
      refunds.value = response.refunds.data || [];
      pagination.value = {
        total: response.refunds.total ?? 0,
        per_page: response.refunds.per_page ?? itemsPerPage.value,
        current_page: response.refunds.current_page ?? 1,
        last_page: response.refunds.last_page ?? 1,
      };
    }
  } catch (error) {
    console.error("Failed to load pending refunds:", error);
    $toast.error("Failed to load pending refunds");
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    pagination.value.current_page = page;
    loadRefunds();
  }
};

const changeItemsPerPage = (perPage) => {
  itemsPerPage.value = perPage;
  pagination.value.current_page = 1;
  loadRefunds();
};

const openReview = (refund) => {
  selectedRefund.value = refund;
  notes.value = "";
};

const closeReview = () => {
  selectedRefund.value = null;
  notes.value = "";
};

const process = async (action) => {
  if (!selectedRefund.value) return;

  const label = action === "approve" ? "approve" : "reject";
  const message =
    action === "approve"
      ? `Approve the refund of ${formatAmount(selectedRefund.value.amount, selectedRefund.value.currency)}? This will process the refund immediately.`
      : `Reject this refund request? The refund will be cancelled.`;
  if (!confirm(message)) return;

  processing.value = true;
  try {
    const response = await $api.post(
      `/admin/payments/refunds/${selectedRefund.value.refund_id}/process`,
      { action, notes: notes.value || null }
    );

    if (response.success) {
      $toast.success(response.message || `Refund ${label}d successfully`);
      closeReview();
      loadRefunds();
    } else {
      $toast.error(response.message || `Failed to ${label} refund`);
    }
  } catch (error) {
    console.error(`Failed to ${label} refund:`, error);
    const errors = error?.data?.errors;
    let detailMsg = error?.data?.message || error?.message || `Failed to ${label} refund`;
    if (errors && typeof errors === "object") {
      const firstField = Object.keys(errors)[0];
      if (firstField && Array.isArray(errors[firstField]) && errors[firstField][0]) {
        detailMsg = `${detailMsg}: ${errors[firstField][0]}`;
      }
    }
    $toast.error(detailMsg);
  } finally {
    processing.value = false;
  }
};

// ---- Helpers ----
const getReasonLabel = (reason) => {
  const labels = {
    duplicate: "Duplicate",
    fraudulent: "Fraudulent",
    requested: "Customer Request",
    error: "Error",
    other: "Other",
  };
  return labels[reason] || reason || "Unknown";
};

const formatAmount = (amount, currency) => {
  const symbol = currency === "MYR" ? "RM" : currency || "";
  const value = parseFloat(amount || 0).toFixed(2);
  return `${symbol} ${value}`.trim();
};

const formatDate = (date) => {
  if (!date) return "N/A";
  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

onMounted(loadRefunds);
</script>
