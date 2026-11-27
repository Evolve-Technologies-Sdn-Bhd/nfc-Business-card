<!-- pages/AdminManagement/invoices/index.vue
     Admin invoice ledger — Ink & Brass layout.
     KPI summary of collection health → the shared invoice list (which owns
     its own filters/pagination) → the invoice preview modal. -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Billing"
      eyebrow-icon="heroicons:document-text"
      title="Invoice Management"
      description="Monitor every invoice issued across NFCGo — track collection performance, revenue and outstanding balances."
    />

    <!-- Collection health KPIs -->
    <div
      v-if="statistics"
      class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
    >
      <AdminStatTile
        label="Total Invoices"
        :value="statistics.total_invoices"
        icon="heroicons:document-text"
      />

      <AdminStatTile
        label="Paid Invoices"
        :value="statistics.paid_invoices"
        icon="heroicons:check-circle"
      >
        <div class="mt-3">
          <div
            class="mb-1 flex items-center justify-between text-helper text-secondary-500"
          >
            <span>Payment rate</span>
            <span v-if="statistics.total_invoices > 0">
              {{
                Math.round(
                  (statistics.paid_invoices / statistics.total_invoices) * 100
                )
              }}%
            </span>
          </div>
          <div class="h-1.5 w-full overflow-hidden rounded-full bg-secondary-200">
            <div
              class="h-full rounded-full bg-success-500 transition-all duration-300"
              :style="{
                width:
                  statistics.total_invoices > 0
                    ? `${(statistics.paid_invoices / statistics.total_invoices) * 100}%`
                    : '0%',
              }"
            ></div>
          </div>
        </div>
      </AdminStatTile>

      <AdminStatTile
        label="Revenue Collected"
        :value="formatCurrency(statistics.paid_amount)"
        icon="heroicons:currency-dollar"
      >
        <div class="mt-3">
          <div
            class="mb-1 flex items-center justify-between text-helper text-secondary-500"
          >
            <span>Collection rate</span>
            <span v-if="statistics.paid_amount + statistics.pending_amount > 0">
              {{
                Math.round(
                  (statistics.paid_amount /
                    (statistics.paid_amount + statistics.pending_amount)) *
                    100
                )
              }}%
            </span>
          </div>
          <div class="h-1.5 w-full overflow-hidden rounded-full bg-secondary-200">
            <div
              class="h-full rounded-full bg-info-500 transition-all duration-300"
              :style="{
                width:
                  statistics.paid_amount + statistics.pending_amount > 0
                    ? `${(statistics.paid_amount / (statistics.paid_amount + statistics.pending_amount)) * 100}%`
                    : '0%',
              }"
            ></div>
          </div>
        </div>
      </AdminStatTile>

      <AdminStatTile
        label="Outstanding"
        :value="formatCurrency(statistics.pending_amount)"
        icon="heroicons:clock"
      />
    </div>

    <!-- Invoice ledger -->
    <div class="mt-6">
      <InvoiceList @preview="handlePreview" />
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
