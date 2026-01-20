<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
      <!-- Loading State -->
      <div v-if="loading" class="text-center">
        <svg class="animate-spin h-12 w-12 text-blue-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <h2 class="text-xl font-semibold text-gray-900 mb-2">Checking Payment Status...</h2>
        <p class="text-gray-600">Please wait while we verify your payment</p>
      </div>

      <!-- Success State -->
      <div v-else-if="status === 'succeeded'" class="text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h2>
        <p class="text-gray-600 mb-6">Your payment has been processed successfully.</p>
        
        <div v-if="transaction" class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
          <div class="flex justify-between py-2">
            <span class="text-gray-600">Transaction ID:</span>
            <span class="font-mono text-sm">{{ transaction.transaction_id }}</span>
          </div>
          <div class="flex justify-between py-2">
            <span class="text-gray-600">Amount:</span>
            <span class="font-semibold">RM {{ transaction.amount?.toFixed(2) }}</span>
          </div>
          <div class="flex justify-between py-2">
            <span class="text-gray-600">Payment Method:</span>
            <span class="capitalize">{{ transaction.payment_rail }}</span>
          </div>
        </div>

        <button
          @click="goToDashboard"
          class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors font-medium"
        >
          Return to Dashboard
        </button>
      </div>

      <!-- Pending State -->
      <div v-else-if="status === 'pending' || status === 'processing'" class="text-center">
        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="animate-spin h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Payment Pending</h2>
        <p class="text-gray-600 mb-6">
          Your payment is being processed. This may take a few moments.
        </p>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
          <p class="text-sm text-blue-800">
            We'll send you a confirmation once the payment is complete.
          </p>
        </div>

        <button
          @click="checkAgain"
          class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors font-medium mb-3"
        >
          Check Status Again
        </button>
        
        <button
          @click="goToDashboard"
          class="w-full border border-gray-300 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-50 transition-colors font-medium"
        >
          Return to Dashboard
        </button>
      </div>

      <!-- Failed State -->
      <div v-else-if="status === 'failed' || status === 'cancelled'" class="text-center">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Payment {{ status === 'cancelled' ? 'Cancelled' : 'Failed' }}</h2>
        <p class="text-gray-600 mb-6">
          {{ status === 'cancelled' ? 'You cancelled the payment.' : 'We were unable to process your payment.' }}
        </p>

        <button
          @click="tryAgain"
          class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors font-medium mb-3"
        >
          Try Again
        </button>
        
        <button
          @click="goToDashboard"
          class="w-full border border-gray-300 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-50 transition-colors font-medium"
        >
          Return to Dashboard
        </button>
      </div>

      <!-- Error State -->
      <div v-else class="text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-10 h-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Unable to Verify Payment</h2>
        <p class="text-gray-600 mb-6">
          We couldn't retrieve the payment status. Please check your transaction history.
        </p>

        <button
          @click="goToDashboard"
          class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors font-medium"
        >
          Return to Dashboard
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePayment } from '~/composables/usePayment'

definePageMeta({
  layout: false,
  middleware: 'auth'
})

const route = useRoute()
const router = useRouter()
const { getTransaction } = usePayment()

const loading = ref(true)
const status = ref(null)
const transaction = ref(null)

onMounted(async () => {
  // Handle Fiuu return URL parameters
  const fiuuOrderId = route.query.order_id
  const fiuuStatus = route.query.status_code || route.query.status
  const fiuuAmount = route.query.amount
  const fiuuTranId = route.query.tran_id
  
  // Legacy support for other payment providers
  const transactionId = route.query.transaction_id || route.query.billplz_id || fiuuOrderId
  
  if (!transactionId) {
    status.value = 'error'
    loading.value = false
    return
  }

  // If we have Fiuu status code directly from URL, map it
  if (fiuuStatus) {
    const statusMap = {
      '00': 'succeeded',
      'Success': 'succeeded',
      '11': 'failed',
      'Failed': 'failed',
      '22': 'pending',
      'Pending': 'pending',
      '33': 'processing',
      'Processing': 'processing'
    }
    
    status.value = statusMap[fiuuStatus] || 'unknown'
    
    // Create a basic transaction object from URL params
    transaction.value = {
      transaction_id: fiuuOrderId,
      fiuu_tran_id: fiuuTranId,
      amount: parseFloat(fiuuAmount) || 0,
      currency: route.query.currency || 'MYR',
      status: status.value,
      payment_rail: route.query.channel || 'card'
    }
    
    loading.value = false
    
    // If successful, try to get full transaction details from API
    if (status.value === 'succeeded') {
      try {
        const result = await getTransaction(fiuuOrderId)
        if (result) {
          transaction.value = result
        }
      } catch (err) {
        console.log('Could not fetch full transaction details:', err)
        // Keep using URL params
      }
    }
  } else {
    // Fetch transaction status from API
    await checkPaymentStatus(transactionId)
  }
})

const checkPaymentStatus = async (transactionId) => {
  loading.value = true
  
  try {
    const result = await getTransaction(transactionId)
    transaction.value = result
    status.value = result.status
  } catch (error) {
    console.error('Failed to get transaction status:', error)
    status.value = 'error'
  } finally {
    loading.value = false
  }
}

const checkAgain = async () => {
  const transactionId = route.query.transaction_id || route.query.billplz_id || route.query.order_id
  if (transactionId) {
    await checkPaymentStatus(transactionId)
  }
}

const tryAgain = () => {
  router.push('/user-dashboard/payment')
}

const goToDashboard = () => {
  router.push('/user-dashboard')
}
</script>
