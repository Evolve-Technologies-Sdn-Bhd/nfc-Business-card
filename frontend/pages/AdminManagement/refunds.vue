<!-- pages/AdminManagement/refunds.vue
     Pending refund review queue — Ink & Brass layout.
     KPI summary → flush queue table → review dialog → confirm dialog. -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Payments"
      eyebrow-icon="heroicons:banknotes"
      title="Refund Requests"
      description="Review, approve or reject pending refund requests raised by customers."
    >
      <template #actions>
        <button
          type="button"
          class="admin-btn admin-btn--outline admin-btn--sm"
          :disabled="loading"
          @click="loadRefunds"
        >
          <Icon
            name="heroicons:arrow-path"
            class="h-4 w-4"
            :class="{ 'animate-spin': loading }"
          />
          Refresh
        </button>
      </template>
    </AdminPageHeader>

    <!-- Queue summary -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <AdminStatTile
        label="Pending refunds"
        :value="pagination.total"
        icon="heroicons:banknotes"
        hint="Awaiting a decision"
        :loading="loading"
      />
      <AdminStatTile
        label="Pending value"
        :value="formatAmount(pendingTotal, 'MYR')"
        icon="heroicons:currency-dollar"
        hint="Total amount to refund"
        :loading="loading"
      />
    </div>

    <!-- Filter bar -->
    <AdminFilterBar class="mt-6" @submit="applyFilters">
      <template #search>
        <label class="admin-label">Search</label>
        <div class="relative">
          <Icon
            name="heroicons:magnifying-glass"
            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-secondary-400"
          />
          <input
            v-model="search"
            type="text"
            placeholder="Search refund ID, customer, transaction…"
            class="admin-field !pl-9"
            maxlength="150"
            @input="onSearchInput"
          />
        </div>
      </template>

      <template #actions>
        <span class="w-full text-right text-body-sm text-secondary-500 sm:w-auto">
          {{ pagination.total }} pending
        </span>
        <button
          type="button"
          class="admin-btn admin-btn--outline admin-btn--sm"
          :disabled="!search"
          @click="clearSearch"
        >
          <Icon name="heroicons:x-mark" class="h-4 w-4" />
          Clear
        </button>
      </template>
    </AdminFilterBar>

    <!-- Queue -->
    <AdminSectionCard
      class="mt-6"
      title="Pending queue"
      subtitle="Refund requests waiting for review"
      flush
    >
      <template #header-actions>
        <span class="text-caption text-secondary-500">
          {{ pagination.total }} pending
        </span>
      </template>

      <!-- Loading -->
      <AdminLoadingState v-if="loading" variant="table" :rows="6" :columns="7" />

      <!-- Empty -->
      <AdminEmptyState
        v-else-if="!refunds.length"
        icon="heroicons:banknotes"
        :title="search ? 'No matching refund requests' : 'No pending refund requests'"
        :description="
          search
            ? 'Try adjusting your search to find the request.'
            : 'New requests will appear here as customers submit them.'
        "
      />

      <!-- Table -->
      <div v-else class="admin-scroll">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Refund</th>
              <th>Customer</th>
              <th>Transaction</th>
              <th>Amount</th>
              <th>Reason</th>
              <th>Requested</th>
              <th class="admin-col-actions">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="refund in refunds" :key="refund.id">
              <td>
                <div class="font-mono text-body font-semibold text-secondary-900">
                  {{ refund.refund_id }}
                </div>
                <AdminStatusBadge class="mt-1" tone="warning" label="Pending" />
              </td>
              <td>
                <div class="text-body font-medium text-secondary-900">
                  {{ refund.user?.full_name || refund.user?.name || "Unknown" }}
                </div>
                <div class="text-caption text-secondary-500">
                  {{ refund.user?.email || "N/A" }}
                </div>
              </td>
              <td>
                <div class="font-mono text-body text-secondary-700">
                  {{ refund.transaction?.transaction_id || "N/A" }}
                </div>
                <div
                  v-if="refund.transaction?.order_id"
                  class="text-caption text-secondary-500"
                >
                  Order #{{ refund.transaction.order_id }}
                </div>
              </td>
              <td>
                <div class="text-body font-semibold text-secondary-900">
                  {{ formatAmount(refund.amount, refund.currency) }}
                </div>
              </td>
              <td>
                <span class="text-body text-secondary-700">
                  {{ getReasonLabel(refund.reason) }}
                </span>
              </td>
              <td class="text-caption text-secondary-500">
                {{ formatDate(refund.created_at) }}
              </td>
              <td class="admin-col-actions">
                <button
                  type="button"
                  class="admin-btn admin-btn--primary admin-btn--sm"
                  @click="openReview(refund)"
                >
                  <Icon name="heroicons:eye" class="h-4 w-4" />
                  Review
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </AdminSectionCard>

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

    <!-- Review dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="selectedRefund"
          class="admin-dialog-backdrop"
          @click.self="closeReview"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="Review refund"
            style="width: min(40rem, 100%); margin-top: 5vh; max-height: 90vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-warning-50 text-warning-700"
                >
                  <Icon name="heroicons:banknotes" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">Review refund</p>
                  <p class="admin-panel-subtitle font-mono">
                    {{ selectedRefund.refund_id }}
                  </p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="closeReview"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <div class="admin-panel-body flex-1 space-y-6 overflow-y-auto">
              <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <dt class="text-caption font-medium text-secondary-500">
                    Customer
                  </dt>
                  <dd class="text-body text-secondary-900">
                    {{
                      selectedRefund.user?.full_name ||
                      selectedRefund.user?.name ||
                      "Unknown"
                    }}
                  </dd>
                  <dd class="text-caption text-secondary-500">
                    {{ selectedRefund.user?.email || "N/A" }}
                  </dd>
                </div>
                <div>
                  <dt class="text-caption font-medium text-secondary-500">
                    Amount
                  </dt>
                  <dd class="text-body font-semibold text-secondary-900">
                    {{ formatAmount(selectedRefund.amount, selectedRefund.currency) }}
                  </dd>
                </div>
                <div>
                  <dt class="text-caption font-medium text-secondary-500">
                    Transaction
                  </dt>
                  <dd class="font-mono text-body text-secondary-900">
                    {{ selectedRefund.transaction?.transaction_id || "N/A" }}
                  </dd>
                </div>
                <div>
                  <dt class="text-caption font-medium text-secondary-500">
                    Provider
                  </dt>
                  <dd class="text-body capitalize text-secondary-900">
                    {{ selectedRefund.provider || "N/A" }}
                  </dd>
                </div>
                <div>
                  <dt class="text-caption font-medium text-secondary-500">
                    Reason
                  </dt>
                  <dd class="text-body text-secondary-900">
                    {{ getReasonLabel(selectedRefund.reason) }}
                  </dd>
                </div>
                <div>
                  <dt class="text-caption font-medium text-secondary-500">
                    Requested
                  </dt>
                  <dd class="text-body text-secondary-900">
                    {{ formatDate(selectedRefund.created_at) }}
                  </dd>
                </div>
              </dl>

              <div v-if="selectedRefund.notes" class="admin-form-section">
                <p class="admin-form-section-title">Customer notes</p>
                <p class="admin-form-section-desc">{{ selectedRefund.notes }}</p>
              </div>

              <div>
                <label class="admin-label" for="refund-admin-notes">
                  Admin notes
                </label>
                <textarea
                  id="refund-admin-notes"
                  v-model="notes"
                  rows="3"
                  maxlength="500"
                  placeholder="Optional notes for this decision…"
                  class="admin-field"
                ></textarea>
                <p class="admin-hint">{{ (notes || "").length }}/500</p>
              </div>
            </div>

            <div class="admin-dialog-foot">
              <button
                type="button"
                class="admin-btn admin-btn--ghost"
                @click="closeReview"
              >
                Cancel
              </button>
              <button
                type="button"
                class="admin-btn admin-btn--danger"
                :disabled="processing"
                @click="process('reject')"
              >
                <Icon
                  v-if="processing"
                  name="heroicons:arrow-path"
                  class="h-4 w-4 animate-spin"
                />
                Reject
              </button>
              <button
                type="button"
                class="admin-btn admin-btn--primary"
                :disabled="processing"
                @click="process('approve')"
              >
                <Icon
                  v-if="processing"
                  name="heroicons:arrow-path"
                  class="h-4 w-4 animate-spin"
                />
                Approve
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Confirmation dialog -->
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
  middleware: "admin",
});

const { $api, $toast } = useNuxtApp();

const loading = ref(false);
const processing = ref(false);
const refunds = ref([]);
const pagination = ref({ total: 0, per_page: 20, current_page: 1, last_page: 1 });
const pendingTotal = ref(0);

const search = ref("");
let searchTimer = null;

const selectedRefund = ref(null);
const notes = ref("");
const itemsPerPage = ref(20);

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

const loadRefunds = async () => {
  loading.value = true;
  try {
    const response = await $api.get("/admin/payments/refunds/pending", {
      params: {
        per_page: itemsPerPage.value,
        page: pagination.value.current_page,
        search: search.value || undefined,
      },
    });
    if (response.success && response.refunds) {
      refunds.value = response.refunds.data || [];
      pagination.value = {
        total: response.refunds.total ?? 0,
        per_page: response.refunds.per_page ?? itemsPerPage.value,
        current_page: response.refunds.current_page ?? 1,
        last_page: response.refunds.last_page ?? 1,
      };
      pendingTotal.value = response.pending_total ?? 0;
    }
  } catch (error) {
    console.error("Failed to load pending refunds:", error);
    $toast.error("Failed to load pending refunds");
  } finally {
    loading.value = false;
  }
};

const applyFilters = () => {
  pagination.value.current_page = 1;
  loadRefunds();
};

const onSearchInput = () => {
  if (searchTimer) clearTimeout(searchTimer);
  searchTimer = setTimeout(() => applyFilters(), 400);
};

const clearSearch = () => {
  search.value = "";
  applyFilters();
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

const process = (action) => {
  if (!selectedRefund.value) return;

  const message =
    action === "approve"
      ? `Approve the refund of ${formatAmount(selectedRefund.value.amount, selectedRefund.value.currency)}? This will process the refund immediately.`
      : `Reject this refund request? The refund will be cancelled.`;

  askConfirm(
    {
      title: action === "approve" ? "Approve refund" : "Reject refund",
      message,
      confirmLabel: action === "approve" ? "Approve" : "Reject",
      tone: action === "approve" ? "primary" : "danger",
    },
    () => submitRefundDecision(action)
  );
};

const submitRefundDecision = async (action) => {
  if (!selectedRefund.value) return;

  const label = action === "approve" ? "approve" : "reject";
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

onBeforeUnmount(() => {
  if (searchTimer) clearTimeout(searchTimer);
});
</script>
