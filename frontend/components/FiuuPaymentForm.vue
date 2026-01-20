<template>
  <div class="fiuu-payment-form">
    <h3 class="text-xl font-semibold mb-4">{{ getPaymentTitle }}</h3>

    <!-- Payment Info Banner -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
      <div class="flex items-start gap-3">
        <svg class="w-6 h-6 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <p class="text-sm text-blue-800 font-medium">Secure Payment via Fiuu</p>
          <p class="text-sm text-blue-600 mt-1">You will be redirected to Fiuu's secure payment gateway to complete your transaction.</p>
        </div>
      </div>
    </div>

    <!-- Payment Method Icons Based on Rail -->
    <div v-if="paymentRail === 'card'" class="flex items-center gap-2 mb-4">
      <img src="https://upload.wikimedia.org/wikipedia/commons/0/04/Visa.svg" alt="Visa" class="h-6">
      <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard" class="h-6">
      <img src="https://upload.wikimedia.org/wikipedia/commons/3/30/American_Express_logo.svg" alt="American Express" class="h-6">
    </div>
    
    <div v-else-if="paymentRail === 'fpx'" class="flex items-center gap-2 mb-4">
      <span class="text-sm text-gray-600">Available Banks: Maybank, CIMB, Public Bank, RHB, and more</span>
    </div>
    
    <div v-else-if="paymentRail === 'ewallet'" class="flex items-center gap-2 mb-4">
      <span class="text-sm text-gray-600">TNG, GrabPay, Boost, ShopeePay</span>
    </div>

    <!-- Payment Summary -->
    <div class="bg-gray-50 rounded-lg p-4 mb-6">
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
        <span class="font-semibold text-lg text-primary-600">RM {{ total.toFixed(2) }}</span>
      </div>
    </div>

    <!-- Description -->
    <div v-if="description" class="mb-4">
      <p class="text-sm text-gray-600">{{ description }}</p>
    </div>

    <!-- Error Message -->
    <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
      {{ errorMessage }}
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
        @click="initiatePayment"
        :disabled="processing"
        class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
      >
        <span v-if="!processing">Pay RM {{ total.toFixed(2) }}</span>
        <span v-else class="flex items-center justify-center">
          <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Processing...
        </span>
      </button>
    </div>

    <!-- Hidden form for Fiuu redirect -->
    <form
      v-if="fiuuFormData"
      ref="fiuuForm"
      :action="fiuuRedirectUrl"
      method="POST"
      style="display: none;"
    >
      <input
        v-for="(value, key) in fiuuFormData"
        :key="key"
        type="hidden"
        :name="key"
        :value="value"
      />
    </form>
  </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'
import { usePayment } from '~/composables/usePayment'

const props = defineProps({
  amount: {
    type: Number,
    required: true
  },
  fee: {
    type: Number,
    default: 0
  },
  paymentRail: {
    type: String,
    default: 'card'
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

const emit = defineEmits(['payment-success', 'payment-failed', 'back'])

const { initiatePayment: apiInitiatePayment, loading, error } = usePayment()

const processing = ref(false)
const errorMessage = ref(null)
const fiuuFormData = ref(null)
const fiuuRedirectUrl = ref(null)
const fiuuForm = ref(null)

const total = computed(() => props.amount + props.fee)

const getPaymentTitle = computed(() => {
  switch (props.paymentRail) {
    case 'card': return 'Card Payment'
    case 'fpx': return 'FPX Online Banking'
    case 'ewallet': return 'E-Wallet Payment'
    default: return 'Payment'
  }
})

// Initiate payment and redirect to Fiuu
const initiatePayment = async () => {
  processing.value = true
  errorMessage.value = null
  
  try {
    const response = await apiInitiatePayment({
      amount: total.value,
      payment_rail: props.paymentRail,
      description: props.description,
      metadata: props.metadata
    })
    
    console.log('Payment initiation response:', response)
    
    if (response.success && response.transaction) {
      const transaction = response.transaction
      
      // Check if we have Fiuu redirect data
      const redirectUrl = transaction.payment_url || transaction.redirect_url
      
      if (redirectUrl) {
        // Direct redirect
        console.log('Redirecting to Fiuu:', redirectUrl)
        window.location.href = redirectUrl
      } else {
        // Fallback - payment may have succeeded or needs different handling
        emit('payment-success', transaction)
      }
    } else {
      throw new Error(response.message || 'Failed to initiate payment')
    }
  } catch (err) {
    console.error('Payment initiation failed:', err)
    errorMessage.value = err.message || err.data?.message || 'Payment failed. Please try again.'
    emit('payment-failed', err)
  } finally {
    processing.value = false
  }
}
</script>

<style scoped>
.fiuu-payment-form {
  @apply space-y-4;
}
</style>
