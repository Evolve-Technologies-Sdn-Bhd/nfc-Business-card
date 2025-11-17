<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="close"></div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
        <!-- Header -->
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
              Invoice {{ invoice?.invoice_number }}
            </h3>
            <button @click="close" class="text-gray-400 hover:text-gray-500">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Invoice details -->
          <div v-if="invoice" class="grid grid-cols-2 gap-4 mb-6 text-sm">
            <div>
              <p class="text-gray-600">Date:</p>
              <p class="font-medium">{{ formatDate(invoice.created_at) }}</p>
            </div>
            <div>
              <p class="text-gray-600">Status:</p>
              <span :class="getStatusBadgeClass(invoice.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                {{ getStatusLabel(invoice.status) }}
              </span>
            </div>
            <div>
              <p class="text-gray-600">Amount:</p>
              <p class="font-medium text-lg">{{ formatCurrency(invoice.total_amount, invoice.currency) }}</p>
            </div>
            <div v-if="invoice.due_date">
              <p class="text-gray-600">Due Date:</p>
              <p class="font-medium">{{ formatDate(invoice.due_date) }}</p>
            </div>
          </div>

          <!-- PDF Preview -->
          <div class="border rounded-lg overflow-hidden" style="height: 600px;">
            <iframe
              v-if="previewUrl"
              :src="previewUrl"
              class="w-full h-full"
              frameborder="0"
            ></iframe>
            <div v-else class="flex items-center justify-center h-full bg-gray-50">
              <p class="text-gray-500">Loading preview...</p>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
          <button
            @click="handleDownload"
            :disabled="downloading"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
          >
            <svg v-if="downloading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ downloading ? 'Downloading...' : 'Download PDF' }}
          </button>
          <button
            @click="handlePrint"
            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm"
          >
            Print
          </button>
          <button
            @click="close"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:w-auto sm:text-sm"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useInvoices } from '~/composables/useInvoices';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  invoice: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close']);

const { 
  getPreviewUrl, 
  downloadInvoice, 
  formatCurrency, 
  formatDate, 
  getStatusBadgeClass, 
  getStatusLabel 
} = useInvoices();

const downloading = ref(false);
const previewUrl = computed(() => props.invoice ? getPreviewUrl(props.invoice) : null);

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

// Close on Escape key
watch(() => props.show, (newValue) => {
  if (newValue) {
    const handleEscape = (e) => {
      if (e.key === 'Escape') {
        close();
      }
    };
    document.addEventListener('keydown', handleEscape);
    return () => document.removeEventListener('keydown', handleEscape);
  }
});
</script>
