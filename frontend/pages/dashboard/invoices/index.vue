<template>
  <UserDashboard>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Invoices</h1>
        <p class="mt-2 text-sm text-gray-600">
          View and download your invoices
        </p>
      </div>

      <!-- Statistics cards -->
      <div v-if="statistics" class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Total Invoices
                  </dt>
                  <dd class="text-lg font-semibold text-gray-900">
                    {{ statistics.total_invoices }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Paid
                  </dt>
                  <dd class="text-lg font-semibold text-green-600">
                    {{ statistics.paid_invoices }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Pending
                  </dt>
                  <dd class="text-lg font-semibold text-blue-600">
                    {{ statistics.pending_invoices }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Total Amount
                  </dt>
                  <dd class="text-lg font-semibold text-gray-900">
                    {{ formatCurrency(statistics.total_amount) }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Invoice list -->
      <InvoiceList @preview="handlePreview" />

      <!-- Preview modal -->
      <InvoicePreviewModal
        :show="showPreviewModal"
        :invoice="selectedInvoice"
        @close="closePreview"
      />
    </div>
  </UserDashboard>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useInvoices } from '~/composables/useInvoices';
import InvoiceList from '~/components/InvoiceList.vue';
import InvoicePreviewModal from '~/components/InvoicePreviewModal.vue';

definePageMeta({
  middleware: 'auth'
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
