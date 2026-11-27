<!-- components/InvoicePreviewModal.vue
     Invoice PDF preview dialog with download and print actions. -->
<template>
  <Teleport to="body">
    <Transition name="admin-fade">
      <div
        v-if="show"
        class="admin-dialog-backdrop"
        @click.self="close"
      >
        <div
          class="admin-dialog flex flex-col"
          role="dialog"
          aria-modal="true"
          :aria-label="`Invoice ${invoice?.invoice_number || ''}`"
          style="width: min(56rem, 100%); margin-top: 4vh; max-height: 92vh"
        >
          <!-- Header -->
          <div class="admin-dialog-head">
            <div class="flex items-center gap-3">
              <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
              >
                <Icon name="heroicons:document-text" class="h-5 w-5" />
              </span>
              <div class="min-w-0">
                <p class="admin-panel-title truncate">
                  Invoice {{ invoice?.invoice_number }}
                </p>
                <p class="admin-panel-subtitle">
                  {{ formatDate(invoice?.created_at) }}
                </p>
              </div>
            </div>
            <button
              type="button"
              class="admin-icon-btn"
              aria-label="Close"
              @click="close"
            >
              <Icon name="heroicons:x-mark" class="h-5 w-5" />
            </button>
          </div>

          <!-- Body -->
          <div class="admin-panel-body flex min-h-0 flex-1 flex-col overflow-y-auto">
            <div v-if="invoice" class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div class="rounded-lg border border-secondary-200 bg-secondary-50 p-3">
                <p class="text-caption uppercase tracking-wide text-secondary-500">Amount</p>
                <p class="mt-1 text-xl font-semibold text-secondary-900">
                  {{ formatCurrency(invoice.total_amount, invoice.currency) }}
                </p>
              </div>
              <div class="rounded-lg border border-secondary-200 bg-secondary-50 p-3">
                <p class="text-caption uppercase tracking-wide text-secondary-500">Status</p>
                <div class="mt-1.5">
                  <AdminStatusBadge
                    :status="invoice.status"
                    :label="getStatusLabel(invoice.status)"
                    :tone="statusTone(invoice.status)"
                  />
                </div>
                <p v-if="invoice.due_date" class="mt-2 text-caption text-secondary-500">
                  Due: {{ formatDate(invoice.due_date) }}
                </p>
              </div>
            </div>

            <div class="flex-1 overflow-hidden rounded-lg border border-secondary-200" style="min-height: 28rem;">
              <iframe
                v-if="previewUrl"
                :src="previewUrl"
                class="h-full w-full"
                frameborder="0"
                title="Invoice preview"
              ></iframe>
              <div v-else class="flex h-full min-h-[28rem] items-center justify-center bg-secondary-50">
                <p class="text-body text-secondary-500">Loading preview…</p>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="admin-dialog-foot">
            <button
              type="button"
              class="admin-btn admin-btn--ghost"
              @click="close"
            >
              Close
            </button>
            <button
              type="button"
              class="admin-btn admin-btn--outline"
              @click="handlePrint"
              :disabled="!previewUrl"
            >
              <Icon name="heroicons:printer" class="h-4 w-4" />
              Print
            </button>
            <button
              type="button"
              class="admin-btn admin-btn--primary"
              :disabled="downloading"
              @click="handleDownload"
            >
              <Icon
                :name="downloading ? 'heroicons:arrow-path' : 'heroicons:arrow-down-tray'"
                class="h-4 w-4"
                :class="{ 'animate-spin': downloading }"
              />
              {{ downloading ? 'Downloading…' : 'Download PDF' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onBeforeUnmount } from 'vue';
import { useInvoices } from '~/composables/useInvoices';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  invoice: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['close']);

const {
  getPreviewUrl,
  downloadInvoice,
  formatCurrency,
  formatDate,
  getStatusLabel,
} = useInvoices();

const downloading = ref(false);
const previewUrl = computed(() => (props.invoice ? getPreviewUrl(props.invoice) : null));

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

const close = () => {
  emit('close');
};

const handleDownload = async () => {
  if (!props.invoice) return;
  downloading.value = true;
  try {
    await downloadInvoice(props.invoice);
  } finally {
    downloading.value = false;
  }
};

const handlePrint = () => {
  if (previewUrl.value) {
    window.open(previewUrl.value, '_blank');
  }
};

let escapeHandler = null;

watch(
  () => props.show,
  (newValue) => {
    if (newValue) {
      escapeHandler = (e) => {
        if (e.key === 'Escape') close();
      };
      document.addEventListener('keydown', escapeHandler);
    } else if (escapeHandler) {
      document.removeEventListener('keydown', escapeHandler);
      escapeHandler = null;
    }
  }
);

onBeforeUnmount(() => {
  if (escapeHandler) document.removeEventListener('keydown', escapeHandler);
});
</script>
