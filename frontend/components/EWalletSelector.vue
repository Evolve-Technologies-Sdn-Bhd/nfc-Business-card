<template>
  <div class="ewallet-selector">
    <h3 class="text-xl font-semibold mb-4">E-Wallet Payment</h3>

    <!-- E-Wallet Options -->
    <div class="grid grid-cols-2 gap-4 mb-6">
      <div
        v-for="wallet in ewallets"
        :key="wallet.code"
        @click="selectedWallet = wallet.code"
        class="ewallet-option border rounded-lg p-4 cursor-pointer hover:border-blue-500 transition-colors"
        :class="{ 'border-blue-500 bg-blue-50': selectedWallet === wallet.code }"
      >
        <div class="flex flex-col items-center space-y-2">
          <div class="w-16 h-16 rounded-full flex items-center justify-center"
               :style="{ backgroundColor: wallet.color }">
            <span class="text-white font-bold text-lg">{{ wallet.short }}</span>
          </div>
          <span class="font-medium text-center">{{ wallet.name }}</span>
        </div>
      </div>
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

    <!-- QR Code Display -->
    <div v-if="qrCode" class="bg-white border rounded-lg p-6 mb-4">
      <p class="text-center text-gray-700 font-medium mb-4">
        Scan QR code with your {{ getWalletName(selectedWallet) }} app
      </p>
      <div class="flex justify-center mb-4">
        <img :src="qrCode" alt="QR Code" class="w-64 h-64 border-2 border-gray-200 rounded-lg" />
      </div>
      <p class="text-center text-sm text-gray-600">
        Or open {{ getWalletName(selectedWallet) }} app and select "Scan to Pay"
      </p>
      
      <!-- Countdown Timer -->
      <div v-if="expiresIn > 0" class="mt-4 text-center">
        <p class="text-sm text-gray-600">
          QR code expires in: 
          <span class="font-semibold text-red-600">{{ formatTime(expiresIn) }}</span>
        </p>
      </div>
    </div>

    <!-- Instructions -->
    <div v-if="selectedWallet && !qrCode" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
      <div class="flex items-start space-x-2">
        <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
        </svg>
        <div>
          <p class="font-medium text-blue-800">How to pay</p>
          <ol class="text-sm text-blue-700 mt-1 list-decimal list-inside space-y-1">
            <li>Click "Open {{ getWalletName(selectedWallet) }} App" below</li>
            <li>Your {{ getWalletName(selectedWallet) }} app will open automatically</li>
            <li>Review the payment details</li>
            <li>Complete the secure authorization in your app</li>
            <li>Wait for payment confirmation</li>
          </ol>
        </div>
      </div>
    </div>
    
    <!-- Deep Link Instructions (after QR generated) -->
    <div v-if="qrCode && deepLink" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
      <div class="flex items-start space-x-2">
        <svg class="w-5 h-5 text-green-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        <div>
          <p class="font-medium text-green-800">Payment Ready!</p>
          <p class="text-sm text-green-700 mt-1">
            Click "Open {{ getWalletName(selectedWallet) }} App" below to complete your payment.
            You'll be redirected to your {{ getWalletName(selectedWallet) }} app to authorize the payment with Secure Sign.
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
        @click="handleBack"
        type="button"
        class="flex-1 border border-gray-300 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-50 transition-colors font-medium"
      >
        Back
      </button>
      <button
        v-if="!qrCode"
        @click="handlePayment"
        :disabled="!selectedWallet || processing"
        class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
      >
        <span v-if="!processing">Continue to {{ getWalletName(selectedWallet) || 'Payment' }}</span>
        <span v-else class="flex items-center justify-center">
          <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Generating...
        </span>
      </button>
      <button
        v-else
        @click="openDeepLink"
        class="flex-1 bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 transition-colors font-medium"
      >
        Open {{ getWalletName(selectedWallet) }} App
      </button>
    </div>

    <!-- Checking Payment Status -->
    <div v-if="checkingStatus" class="mt-4 text-center">
      <div class="flex items-center justify-center space-x-2 text-gray-600">
        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Waiting for payment confirmation...</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onUnmounted } from 'vue'
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

const emit = defineEmits(['payment-success', 'payment-failed', 'back'])

const { initiatePayment, getTransaction, loading, error } = usePayment()
const { showToast } = useToast()

const selectedWallet = ref(null)
const processing = ref(false)
const qrCode = ref(null)
const deepLink = ref(null)
const transactionId = ref(null)
const checkingStatus = ref(false)
const expiresIn = ref(600) // 10 minutes
let statusCheckInterval = null
let expiryInterval = null

const total = computed(() => props.amount + props.fee)

const ewallets = ref([
  { code: 'tng', name: "Touch 'n Go", short: 'TNG', color: '#0066CC' },
  { code: 'grabpay', name: 'GrabPay', short: 'GP', color: '#00B14F' },
  { code: 'boost', name: 'Boost', short: 'BST', color: '#FF6B00' },
  { code: 'shopeepay', name: 'ShopeePay', short: 'SP', color: '#EE4D2D' }
])

const getWalletName = (code) => {
  const wallet = ewallets.value.find(w => w.code === code)
  return wallet ? wallet.name : code
}

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

const handlePayment = async () => {
  if (!selectedWallet.value) {
    error.value = 'Please select an e-wallet'
    return
  }

  processing.value = true
  error.value = null

  try {
    const config = useRuntimeConfig()
    const callbackUrl = `${config.public.apiUrl || 'http://localhost:8000'}/api/webhooks/billplz`
    const redirectUrl = `${config.public.appUrl || window.location.origin}/payment/status`

    const response = await initiatePayment({
      amount: total.value,
      currency: 'MYR',
      payment_rail: 'ewallet',
      wallet_type: selectedWallet.value,
      description: props.description,
      callback_url: callbackUrl,
      redirect_url: redirectUrl,
      metadata: props.metadata
    })

    if (response.success) {
      transactionId.value = response.transaction.transaction_id
      
      // Extract URLs from metadata
      const metadata = response.transaction.metadata || {}
      qrCode.value = metadata.qr_code_url || null
      deepLink.value = metadata.deep_link_url || null
      
      // Automatically open deep link if on mobile
      if (deepLink.value && isMobile()) {
        setTimeout(() => {
          window.location.href = deepLink.value
        }, 500)
      }
      
      // Start checking payment status
      startStatusCheck()
      
      // Start expiry countdown
      startExpiryCountdown()
      
      showToast('Please complete payment in your ' + getWalletName(selectedWallet.value) + ' app', 'success')
    } else {
      throw new Error('Failed to generate payment link')
    }
  } catch (err) {
    console.error('E-wallet payment error:', err)
    error.value = err.message || 'Failed to initiate payment'
    showToast(error.value, 'error')
    emit('payment-failed', err)
  } finally {
    processing.value = false
  }
}

const isMobile = () => {
  return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
}

const openDeepLink = () => {
  if (deepLink.value) {
    window.location.href = deepLink.value
  }
}

const startStatusCheck = () => {
  checkingStatus.value = true
  
  // Check status every 3 seconds
  statusCheckInterval = setInterval(async () => {
    try {
      const transaction = await getTransaction(transactionId.value)
      
      if (transaction.status === 'succeeded') {
        clearInterval(statusCheckInterval)
        clearInterval(expiryInterval)
        checkingStatus.value = false
        showToast('Payment successful!', 'success')
        emit('payment-success', transaction)
      } else if (transaction.status === 'failed' || transaction.status === 'cancelled') {
        clearInterval(statusCheckInterval)
        clearInterval(expiryInterval)
        checkingStatus.value = false
        showToast('Payment failed or cancelled', 'error')
        emit('payment-failed', transaction)
      }
    } catch (err) {
      console.error('Failed to check payment status:', err)
    }
  }, 3000)
}

const startExpiryCountdown = () => {
  expiryInterval = setInterval(() => {
    expiresIn.value--
    
    if (expiresIn.value <= 0) {
      clearInterval(statusCheckInterval)
      clearInterval(expiryInterval)
      checkingStatus.value = false
      error.value = 'QR code has expired. Please try again.'
      qrCode.value = null
    }
  }, 1000)
}

const handleBack = () => {
  if (statusCheckInterval) {
    clearInterval(statusCheckInterval)
  }
  if (expiryInterval) {
    clearInterval(expiryInterval)
  }
  emit('back')
}

onUnmounted(() => {
  if (statusCheckInterval) {
    clearInterval(statusCheckInterval)
  }
  if (expiryInterval) {
    clearInterval(expiryInterval)
  }
})
</script>

<style scoped>
.ewallet-option {
  transition: all 0.2s ease;
}

.ewallet-option:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
</style>
