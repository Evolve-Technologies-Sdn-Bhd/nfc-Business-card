<!-- components/InvoiceList.vue
     Shared invoice list with filters, table, pagination and preview.
     Used by both admin and user invoice pages. -->
<template>
  <div>
    <!-- Filter bar -->
    <AdminFilterBar @submit="applyFilters">
      <template #search>
        <label class="admin-label">Search</label>
        <div class="relative">
          <Icon
            name="heroicons:magnifying-glass"
            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-secondary-400"
          />
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search invoice number or customer…"
            class="admin-field !pl-9"
            maxlength="150"
            @input="debounceSearch"
          />
        </div>
      </template>

      <div class="min-w-[10rem]">
        <label class="admin-label">Status</label>
        <select v-model="filters.status" class="admin-field" @change="applyFilters">
          <option value="">All statuses</option>
          <option value="draft">Draft</option>
          <option value="issued">Issued</option>
          <option value="paid">Paid</option>
          <option value="cancelled">Cancelled</option>
          <option value="refunded">Refunded</option>
        </select>
      </div>

      <div class="min-w-[9rem]">
        <label class="admin-label">From</label>
        <input
          v-model="filters.from_date"
          type="date"
          class="admin-field"
          @change="applyFilters"
        />
      </div>

      <div class="min-w-[9rem]">
        <label class="admin-label">To</label>
        <input
          v-model="filters.to_date"
          type="date"
          class="admin-field"
          @change="applyFilters"
        />
      </div>

      <template #actions>
        <span class="w-full text-right text-body-sm text-secondary-500 sm:w-auto">
          {{ pagination.total }} invoice{{ pagination.total === 1 ? '' : 's' }}
        </span>
        <button
          type="button"
          class="admin-btn admin-btn--outline admin-btn--sm"
          :disabled="!hasActiveFilters"
          @click="clearFilters"
        >
          <Icon name="heroicons:x-mark" class="h-4 w-4" />
          Clear
        </button>
      </template>
    </AdminFilterBar>

    <!-- Loading state -->
    <AdminLoadingState v-if="loading" variant="table" :rows="6" :columns="5" />

    <!-- Empty state -->
    <AdminEmptyState
      v-else-if="invoices.length === 0"
      icon="heroicons:document-text"
      :title="hasActiveFilters ? 'No matching invoices' : 'No invoices yet'"
      :description="
        hasActiveFilters
          ? 'Try adjusting the search, status or date filters above.'
          : 'Invoices issued on the platform will appear here.'
      "
    />

    <!-- Invoices table -->
    <div v-else class="admin-panel overflow-hidden">
      <div class="admin-scroll">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Invoice</th>
              <th>Customer</th>
              <th>Date</th>
              <th class="text-right">Amount</th>
              <th>Status</th>
              <th class="admin-col-actions">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="invoice in invoices" :key="invoice.id">
              <td>
                <div class="flex flex-col">
                  <span class="font-medium text-secondary-900">
                    {{ invoice.invoice_number }}
                  </span>
                  <span v-if="invoice.version > 1" class="text-caption text-secondary-500">
                    v{{ invoice.version }}
                  </span>
                </div>
              </td>
              <td class="text-secondary-700">
                {{ customerName(invoice) }}
              </td>
              <td class="whitespace-nowrap text-secondary-600">
                {{ formatDate(invoice.created_at) }}
              </td>
              <td class="whitespace-nowrap text-right font-medium text-secondary-900">
                {{ formatCurrency(invoice.total_amount, invoice.currency) }}
              </td>
              <td>
                <AdminStatusBadge
                  :status="invoice.status"
                  :label="getStatusLabel(invoice.status)"
                  :tone="statusTone(invoice.status)"
                />
              </td>
              <td class="admin-col-actions">
                <div class="flex items-center justify-end gap-1.5">
                  <button
                    type="button"
                    class="admin-btn admin-btn--outline admin-btn--sm"
                    @click="$emit('preview', invoice)"
                  >
                    <Icon name="heroicons:eye" class="h-4 w-4" />
                    View
                  </button>
                  <InvoiceDownloadButton :invoice="invoice" />
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <AdminPagination
      v-if="!loading && invoices.length > 0"
      :current-page="pagination.current_page"
      :last-page="pagination.last_page"
      :per-page="pagination.per_page"
      :total="pagination.total"
      item-label="invoices"
      @page-change="changePage"
      @per-page-change="changePerPage"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useInvoices } from '~/composables/useInvoices';
import InvoiceDownloadButton from './InvoiceDownloadButton.vue';

const emit = defineEmits(['preview']);

const {
  invoices,
  loading,
  pagination,
  fetchInvoices,
  formatCurrency,
  formatDate,
  getStatusLabel,
} = useInvoices();

const filters = ref({
  status: '',
  search: '',
  from_date: '',
  to_date: '',
  page: 1,
  per_page: pagination.value.per_page,
});

const hasActiveFilters = computed(
  () => !!(filters.value.search || filters.value.status || filters.value.from_date || filters.value.to_date)
);

let searchTimeout = null;

const statusTone = (status) => {
  switch (status) {
    case 'paid':
      return 'success';
    case 'issued':
      return 'info';
    case 'draft':
      return 'neutral';
    case 'cancelled':
      return 'danger';
    case 'refunded':
      return 'warning';
    default:
      return 'neutral';
  }
};

const customerName = (invoice) => {
  const user = invoice.user;
  if (!user) return '—';
  const name = [user.first_name, user.last_name].filter(Boolean).join(' ').trim();
  return name || user.name || user.email || '—';
};

const applyFilters = () => {
  filters.value.page = 1;
  loadInvoices();
};

const clearFilters = () => {
  filters.value = {
    status: '',
    search: '',
    from_date: '',
    to_date: '',
    page: 1,
    per_page: pagination.value.per_page,
  };
  loadInvoices();
};

const debounceSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => applyFilters(), 400);
};

const changePage = (page) => {
  const last = pagination.value.last_page || 1;
  const target = Math.min(Math.max(1, page), last);
  if (target === filters.value.page) return;
  filters.value.page = target;
  loadInvoices();
};

const changePerPage = (perPage) => {
  filters.value.per_page = perPage;
  filters.value.page = 1;
  loadInvoices();
};

const loadInvoices = async () => {
  await fetchInvoices(filters.value);
};

onMounted(() => {
  loadInvoices();
});

onBeforeUnmount(() => {
  if (searchTimeout) clearTimeout(searchTimeout);
});
</script>
