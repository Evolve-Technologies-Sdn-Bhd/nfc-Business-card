<template>
  <div>
    <!-- Header -->
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-3xl font-bold text-secondary-900">
            Invoice Management
          </h1>
          <p class="mt-2 text-secondary-600">Manage all customer invoices</p>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div
      v-if="statistics"
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
    >
      <div class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="p-3 rounded-lg bg-primary-500">
              <Icon name="heroicons:document-text" class="h-6 w-6 text-white" />
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-secondary-600">Total Invoices</p>
            <p class="text-2xl font-semibold text-secondary-900">
              {{ statistics.total_invoices }}
            </p>
          </div>
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="p-3 rounded-lg bg-success-500">
              <Icon name="heroicons:check-circle" class="h-6 w-6 text-white" />
            </div>
          </div>
          <div class="ml-4 flex-1">
            <p class="text-sm font-medium text-secondary-600">Paid Invoices</p>
            <p class="text-2xl font-semibold text-secondary-900">
              {{ statistics.paid_invoices }}
            </p>
            <div class="mt-2">
              <div
                class="flex items-center justify-between text-xs text-secondary-500 mb-1"
              >
                <span>Payment Rate</span>
                <span v-if="statistics.total_invoices > 0">
                  {{
                    Math.round(
                      (statistics.paid_invoices / statistics.total_invoices) *
                        100
                    )
                  }}%
                </span>
              </div>
              <div class="w-full bg-secondary-200 rounded-full h-1.5">
                <div
                  class="bg-success-500 h-1.5 rounded-full transition-all duration-300"
                  :style="{
                    width:
                      statistics.total_invoices > 0
                        ? `${
                            (statistics.paid_invoices /
                              statistics.total_invoices) *
                            100
                          }%`
                        : '0%',
                  }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="p-3 rounded-lg bg-info-500">
              <Icon
                name="heroicons:currency-dollar"
                class="h-6 w-6 text-white"
              />
            </div>
          </div>
          <div class="ml-4 flex-1">
            <p class="text-sm font-medium text-secondary-600">Total Revenue</p>
            <p class="text-2xl font-semibold text-secondary-900">
              {{ formatCurrency(statistics.paid_amount) }}
            </p>
            <div class="mt-2">
              <div
                class="flex items-center justify-between text-xs text-secondary-500 mb-1"
              >
                <span>Collection Rate</span>
                <span
                  v-if="statistics.paid_amount + statistics.pending_amount > 0"
                >
                  {{
                    Math.round(
                      (statistics.paid_amount /
                        (statistics.paid_amount + statistics.pending_amount)) *
                        100
                    )
                  }}%
                </span>
              </div>
              <div class="w-full bg-secondary-200 rounded-full h-1.5">
                <div
                  class="bg-info-500 h-1.5 rounded-full transition-all duration-300"
                  :style="{
                    width:
                      statistics.paid_amount + statistics.pending_amount > 0
                        ? `${
                            (statistics.paid_amount /
                              (statistics.paid_amount +
                                statistics.pending_amount)) *
                            100
                          }%`
                        : '0%',
                  }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="p-3 rounded-lg bg-warning-500">
              <Icon name="heroicons:clock" class="h-6 w-6 text-white" />
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-secondary-600">Pending Amount</p>
            <p class="text-2xl font-semibold text-secondary-900">
              {{ formatCurrency(statistics.pending_amount) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Invoice table -->
    <div class="card">
      <div class="card-body">
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
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useInvoices } from "~/composables/useInvoices";
import InvoiceList from "~/components/InvoiceList.vue";
import InvoicePreviewModal from "~/components/InvoicePreviewModal.vue";

useHead({
  title: "Invoice Management - Admin - NFCGo",
});

definePageMeta({
  middleware: ["auth", "admin"],
  layout: "admin-management",
});

const { statistics, fetchStatistics, formatCurrency } = useInvoices();

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
  try {
    await fetchStatistics();
  } catch (error) {
    console.error("Failed to load statistics:", error);
    // Page will still render with empty/default statistics
  }
});
</script>
