<template>
  <div class="bank-payment-form">
    <h3 class="text-xl font-semibold mb-4">FPX Online Banking</h3>

    <!-- Bank Selection -->
    <div class="mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">
        Select Your Bank
      </label>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div
          v-for="bank in banks"
          :key="bank.code"
          @click="selectedBank = bank.code"
          class="bank-option border rounded-lg p-3 cursor-pointer hover:border-blue-500 transition-colors"
          :class="{ 'border-blue-500 bg-blue-50': selectedBank === bank.code }"
        >
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
              </svg>
            </div>
            <span class="font-medium">{{ bank.name }}</span>
          </div>
        </div>
      </div>
      <p v-if="!selectedBank" class="text-sm text-red-600 mt-2">Please select a bank</p>
    </div>

    <!-- Payment Summary -->
    <div class="bg-gray-50 rounded-lg p-4 mb-4">
      <div class="flex justify-between items-center mb-2">
        <span class="text-gray-600">Amount:</span>
        <span class="font-medium">RM {{ amount.toFixed(2) }}</span>
      </div>
      <div class="flex justify-between items-center mb-2">
        <span class="text-gray-600">Processing Fee:</span>
        <span class="font-medium">RM {{ fee.toFixed(2) }}</span>
      </div>
      <div class="flex justify-between items-center pt-2 border-t border-gray-200">
        <span class="font-semibold">Total:</span>
        <span class="font-semibold text-lg">RM {{ total.toFixed(2) }}</span>
      </div>
    </div>

    <!-- Important Notice -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
      <div class="flex items-start space-x-2">
        <svg class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
        <div>
          <p class="font-medium text-yellow-800">Important</p>
          <p class="text-sm text-yellow-700 mt-1">
            You will be redirected to your bank's website to complete the payment. 
            Please do not close the browser window until the payment is complete.
          </p>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
      {{ error }}
    </div>

    <!-- Action Buttons -->
    <div class="flex space-x-3">
      <button
        @click="$emit('back')"
        type="button"
        class="flex-1 border border-gray-300 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-50 transition-colors font-medium"
      >
        Back
      </button>
      <button
        @click="handlePayment"
        :disabled="!selectedBank || processing"
        class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
      >
        <span v-if="!processing">Continue to Bank</span>
        <span v-else class="flex items-center justify-center">
          <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Processing...
        </span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { usePayment } from '~/composables/usePayment'
import { useToast } from '~/composables/useToast'

const props = defineProps({
  amount: {
    type: Number,
    required: true
  },
  fee: {
    type: Number,
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  metadata: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['payment-initiated', 'payment-failed', 'back'])

const { initiatePayment, loading, error } = usePayment()
const { showToast } = useToast()

const selectedBank = ref(null)
const processing = ref(false)

const total = computed(() => props.amount + props.fee)

const banks = ref([
  { code: 'MB2U', name: 'Maybank' },
  { code: 'CIMBCLICKS', name: 'CIMB Bank' },
  { code: 'PBB0233', name: 'Public Bank' },
  { code: 'RHB0218', name: 'RHB Bank' },
  { code: 'HLB0224', name: 'Hong Leong Bank' },
  { code: 'ABMB0212', name: 'Alliance Bank' },
  { code: 'AMBB0209', name: 'AmBank' },
  { code: 'BIMB0340', name: 'Bank Islam' },
  { code: 'BMMB0341', name: 'Bank Muamalat' },
  { code: 'BSN0601', name: 'Bank Simpanan Nasional' },
  { code: 'KFH0346', name: 'Kuwait Finance House' },
  { code: 'OCBC0229', name: 'OCBC Bank' },
  { code: 'SCB0216', name: 'Standard Chartered' },
  { code: 'UOB0226', name: 'UOB Bank' },
  { code: 'ABB0233', name: 'Affin Bank' },
  { code: 'BCBB0235', name: 'CIMB Islamic Bank' }
])

const handlePayment = async () => {
  if (!selectedBank.value) {
    error.value = 'Please select a bank'
    return
  }

  processing.value = true
  error.value = null

  try {
    const config = useRuntimeConfig()
    const callbackUrl = `${config.public.appUrl}/payment/callback`

    const response = await initiatePayment({
      amount: total.value,
      currency: 'MYR',
      payment_rail: 'fpx',
      bank_code: selectedBank.value,
      description: props.description,
      callback_url: callbackUrl,
      metadata: props.metadata
    })

    if (response.success && response.redirect_url) {
      // Redirect to FPX payment page
      showToast('Redirecting to your bank...', 'info')
      
      // Store transaction ID in session storage for callback
      sessionStorage.setItem('pending_transaction_id', response.transaction.transaction_id)
      
      // Redirect to bank
      window.location.href = response.redirect_url
    } else {
      throw new Error('Failed to initiate FPX payment')
    }
  } catch (err) {
    console.error('FPX payment error:', err)
    error.value = err.message || 'Failed to initiate payment'
    showToast(error.value, 'error')
    emit('payment-failed', err)
  } finally {
    processing.value = false
  }
}
</script>

<style scoped>
.bank-option {
  transition: all 0.2s ease;
}

.bank-option:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.1);
}
</style>
