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
    <div
      v-if="pagination.last_page > 1"
      class="mt-6 flex items-center justify-between border-t border-secondary-200 bg-white px-4 py-3 sm:px-6"
    >
      <div class="flex flex-1 justify-between sm:hidden">
        <button
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="btn btn-outline btn-sm"
        >
          Previous
        </button>
        <button
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="btn btn-outline btn-sm"
        >
          Next
        </button>
      </div>
      <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div>
          <p class="text-sm text-secondary-700">
            Showing
            <span class="font-medium">{{
              (pagination.current_page - 1) * pagination.per_page + 1
            }}</span>
            to
            <span class="font-medium">{{
              Math.min(
                pagination.current_page * pagination.per_page,
                pagination.total
              )
            }}</span>
            of
            <span class="font-medium">{{ pagination.total }}</span>
            results
          </p>
        </div>
        <div>
          <nav
            class="isolate inline-flex -space-x-px rounded-md shadow-sm"
            aria-label="Pagination"
          >
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="relative inline-flex items-center rounded-l-md px-2 py-2 text-secondary-400 ring-1 ring-inset ring-secondary-300 hover:bg-secondary-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50"
            >
              <Icon name="heroicons:chevron-left" class="h-5 w-5" />
            </button>
            <button
              v-for="page in visiblePages"
              :key="page"
              @click="changePage(page)"
              :class="[
                page === pagination.current_page
                  ? 'z-10 bg-primary-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600'
                  : 'text-secondary-900 ring-1 ring-inset ring-secondary-300 hover:bg-secondary-50 focus:outline-offset-0',
                'relative inline-flex items-center px-4 py-2 text-sm font-semibold focus:z-20',
              ]"
            >
              {{ page }}
            </button>
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="relative inline-flex items-center rounded-r-md px-2 py-2 text-secondary-400 ring-1 ring-inset ring-secondary-300 hover:bg-secondary-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50"
            >
              <Icon name="heroicons:chevron-right" class="h-5 w-5" />
            </button>
          </nav>
        </div>
      </div>
    </div>
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
