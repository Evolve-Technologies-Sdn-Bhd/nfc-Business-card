<!-- pages/dashboard/invoices/index.vue — the signed-in user's invoices.
     Previously wrapped its content in <UserDashboard>, which is a layout, not a
     component, so the page rendered with no portal shell at all. It now uses
     the `user-dashboard` layout like every other portal page. -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Account"
      eyebrow-icon="heroicons:document-text"
      title="Invoices"
      description="Every invoice issued for your card orders. Preview or download a PDF copy at any time."
    >
      <template #actions>
        <NuxtLink to="/UserDashboard/Settings" class="admin-btn admin-btn--ghost">
          <Icon name="heroicons:cog-6-tooth" class="h-4 w-4" />
          Billing settings
        </NuxtLink>
      </template>
    </AdminPageHeader>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <AdminStatTile label="Total invoices" :value="statistics?.total_invoices ?? 0" icon="heroicons:document-text" :loading="!statsLoaded" />
      <AdminStatTile label="Paid" :value="statistics?.paid_invoices ?? 0" icon="heroicons:check-circle" :loading="!statsLoaded" />
      <AdminStatTile label="Pending" :value="statistics?.pending_invoices ?? 0" icon="heroicons:clock" :loading="!statsLoaded" />
      <AdminStatTile
        label="Total amount"
        :value="formatCurrency(statistics?.total_amount ?? 0)"
        icon="heroicons:banknotes"
        :loading="!statsLoaded"
      />
    </div>

    <InvoiceList @preview="handlePreview" />

    <InvoicePreviewModal :show="showPreviewModal" :invoice="selectedInvoice" @close="closePreview" />
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useInvoices } from "~/composables/useInvoices";
import InvoiceList from "~/components/InvoiceList.vue";
import InvoicePreviewModal from "~/components/InvoicePreviewModal.vue";

definePageMeta({
  layout: "user-dashboard",
  middleware: "auth",
});

useHead({ title: "Invoices — NFCGo" });

const { statistics, fetchStatistics, formatCurrency } = useInvoices();

const statsLoaded = ref(false);
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
  } catch {
    // useInvoices already reported it; the tiles fall back to zero.
  } finally {
    statsLoaded.value = true;
  }
});
</script>
