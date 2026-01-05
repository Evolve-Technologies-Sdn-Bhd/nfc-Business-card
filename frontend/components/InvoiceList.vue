<template>
  <div>
    <!-- Header with filters -->
    <div class="mb-6">
      <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
      >
        <h3 class="text-lg font-semibold text-secondary-900">Invoices</h3>

        <!-- Filters -->
        <div class="flex flex-col sm:flex-row gap-3">
          <!-- Status filter -->
          <select v-model="filters.status" @change="applyFilters" class="input">
            <option value="">All Statuses</option>
            <option value="draft">Draft</option>
            <option value="issued">Issued</option>
            <option value="paid">Paid</option>
            <option value="cancelled">Cancelled</option>
            <option value="refunded">Refunded</option>
          </select>

          <!-- Search -->
          <input
            v-model="filters.search"
            @input="debounceSearch"
            type="text"
            placeholder="Search invoice number..."
            class="input"
          />
        </div>
      </div>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="spinner"></div>
      <p class="ml-3 text-sm text-secondary-500">Loading invoices...</p>
    </div>

    <!-- Empty state -->
    <div v-else-if="invoices.length === 0" class="text-center py-12">
      <Icon
        name="heroicons:document-text"
        class="mx-auto h-12 w-12 text-secondary-400"
      />
      <h3 class="mt-2 text-sm font-medium text-secondary-900">No invoices</h3>
      <p class="mt-1 text-sm text-secondary-500">
        {{
          filters.search || filters.status
            ? "No invoices match your filters."
            : "Your invoices will appear here."
        }}
      </p>
    </div>

    <!-- Invoices table -->
    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-secondary-200">
        <thead class="bg-secondary-50">
          <tr>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
            >
              Invoice #
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
            >
              Date
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
            >
              Amount
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
            >
              Status
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-right text-xs font-medium text-secondary-500 uppercase tracking-wider"
            >
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-secondary-200">
          <tr
            v-for="invoice in invoices"
            :key="invoice.id"
            class="hover:bg-secondary-50"
          >
            <td
              class="px-6 py-4 whitespace-nowrap text-sm font-medium text-secondary-900"
            >
              {{ invoice.invoice_number }}
              <span
                v-if="invoice.version > 1"
                class="ml-2 text-xs text-secondary-500"
              >
                (v{{ invoice.version }})
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-500">
              {{ formatDate(invoice.created_at) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-900">
              {{ formatCurrency(invoice.total_amount, invoice.currency) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                :class="getStatusBadgeClass(invoice.status)"
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
              >
                {{ getStatusLabel(invoice.status) }}
              </span>
            </td>
            <td
              class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
            >
              <button
                @click="$emit('preview', invoice)"
                class="text-primary-600 hover:text-primary-900 mr-4"
              >
                View
              </button>
              <InvoiceDownloadButton :invoice="invoice" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <AdminPagination
      v-if="pagination.last_page > 1"
      :current-page="pagination.current_page"
      :last-page="pagination.last_page"
      :per-page="pagination.per_page"
      :total="pagination.total"
      item-label="results"
      @page-change="changePage"
      @per-page-change="() => {}"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useInvoices } from "~/composables/useInvoices";
import InvoiceDownloadButton from "./InvoiceDownloadButton.vue";

const emit = defineEmits(["preview"]);

const {
  invoices,
  loading,
  pagination,
  fetchInvoices,
  formatCurrency,
  formatDate,
  getStatusBadgeClass,
  getStatusLabel,
} = useInvoices();

const filters = ref({
  status: "",
  search: "",
  page: 1,
});

let searchTimeout = null;

const visiblePages = computed(() => {
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;
  const delta = 2;
  const range = [];

  for (
    let i = Math.max(2, current - delta);
    i <= Math.min(last - 1, current + delta);
    i++
  ) {
    range.push(i);
  }

  if (current - delta > 2) {
    range.unshift("...");
  }
  if (current + delta < last - 1) {
    range.push("...");
  }

  range.unshift(1);
  if (last > 1) {
    range.push(last);
  }

  return range.filter((v, i, a) => a.indexOf(v) === i);
});

const applyFilters = () => {
  filters.value.page = 1;
  loadInvoices();
};

const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 500);
};

const changePage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return;
  filters.value.page = page;
  loadInvoices();
};

const loadInvoices = async () => {
  await fetchInvoices(filters.value);
};

onMounted(() => {
  loadInvoices();
});
</script>
