<template>
  <AdminManagement>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Invoice Management</h1>
        <p class="mt-2 text-sm text-gray-600">
          Manage all customer invoices
        </p>
      </div>

      <!-- Statistics cards -->
      <div v-if="statistics" class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <dt class="text-sm font-medium text-gray-500 truncate">Total Invoices</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ statistics.total_invoices }}</dd>
          </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <dt class="text-sm font-medium text-gray-500 truncate">Paid Invoices</dt>
            <dd class="mt-1 text-3xl font-semibold text-green-600">{{ statistics.paid_invoices }}</dd>
          </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <dt class="text-sm font-medium text-gray-500 truncate">Total Revenue</dt>
            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ formatCurrency(statistics.paid_amount) }}</dd>
          </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <dt class="text-sm font-medium text-gray-500 truncate">Pending Amount</dt>
            <dd class="mt-1 text-3xl font-semibold text-blue-600">{{ formatCurrency(statistics.pending_amount) }}</dd>
          </div>
        </div>
      </div>

      <!-- Invoice table -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <InvoiceList @preview="handlePreview" />
        </div>
      </div>

      <!-- Preview modal -->
      <InvoicePreviewModal
        :show="showPreviewModal"
        :invoice="selectedInvoice"
        @close="closePreview"
      />
    </div>
  </AdminManagement>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useInvoices } from '~/composables/useInvoices';
import InvoiceList from '~/components/InvoiceList.vue';
import InvoicePreviewModal from '~/components/InvoicePreviewModal.vue';

definePageMeta({
  middleware: ['auth', 'admin']
});

const {
  statistics,
  fetchStatistics,
  formatCurrency,
} = useInvoices();

const showPreviewModal = ref(false);
const selectedInvoice = ref(null);

const handlePreview = (invoice) => {
  selectedInvoice.value = invoice;
  showPreviewModal.value = true;
};

const closePreview = () => {
  showPreviewModal.value = false;
  selectedInvoice.value = null;
};

onMounted(async () => {
  await fetchStatistics();
});
</script>
