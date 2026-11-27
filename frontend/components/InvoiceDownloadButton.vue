<!-- components/InvoiceDownloadButton.vue
     Download button for an invoice PDF. -->
<template>
  <button
    type="button"
    class="admin-btn admin-btn--outline admin-btn--sm"
    :disabled="downloading"
    @click="handleDownload"
  >
    <Icon
      :name="downloading ? 'heroicons:arrow-path' : 'heroicons:arrow-down-tray'"
      class="h-4 w-4"
      :class="{ 'animate-spin': downloading }"
    />
    {{ downloading ? 'Downloading…' : 'Download' }}
  </button>
</template>

<script setup>
import { ref } from 'vue';
import { useInvoices } from '~/composables/useInvoices';

const props = defineProps({
  invoice: {
    type: Object,
    required: true,
  },
});

const { downloadInvoice } = useInvoices();
const downloading = ref(false);

const handleDownload = async () => {
  downloading.value = true;
  try {
    await downloadInvoice(props.invoice);
  } finally {
    downloading.value = false;
  }
};
</script>
