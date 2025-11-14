<template>
  <div class="card-payment-form">
    <h3 class="text-xl font-semibold mb-4">Card Payment</h3>

    <!-- Card Brand Icons -->
    <div class="flex items-center gap-2 mb-4">
      <img src="https://upload.wikimedia.org/wikipedia/commons/0/04/Visa.svg" alt="Visa" class="h-6">
      <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard" class="h-6">
      <img src="https://upload.wikimedia.org/wikipedia/commons/3/30/American_Express_logo.svg" alt="American Express" class="h-6">
    </div>

    <!-- Tap Card Helper (Optional) -->
    <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 mb-4 flex items-start gap-3">
      <svg class="w-5 h-5 text-orange-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
      </svg>
      <p class="text-sm text-orange-800">Tap card on your phone to fill in card details (if supported)</p>
    </div>

    <!-- Card Details Section -->
    <div class="mb-6">
      <h4 class="text-base font-semibold text-gray-900 mb-4">Card Details</h4>

      <!-- Card Number -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Card Number
        </label>
        <input
          v-model="cardForm.number"
          @input="formatCardNumber"
          @blur="validateCardNumber"
          type="text"
          inputmode="numeric"
          maxlength="19"
          placeholder="1234 5678 9012 3456"
          class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
          :class="{ 'border-red-500': errors.cardNumber }"
        >
        <p v-if="errors.cardNumber" class="text-red-600 text-sm mt-1">{{ errors.cardNumber }}</p>
      </div>

      <!-- Expiry Date and CVV -->
      <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Expiry Date (MM/YY)
          </label>
          <input
            v-model="cardForm.expiry"
            @input="formatExpiry"
            @blur="validateExpiry"
            type="text"
            inputmode="numeric"
            maxlength="5"
            placeholder="MM/YY"
            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
            :class="{ 'border-red-500': errors.expiry }"
          >
          <p v-if="errors.expiry" class="text-red-600 text-sm mt-1">{{ errors.expiry }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center gap-1">
            CVV
            <button
              type="button"
              @click="showCvvInfo = !showCvvInfo"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
            </button>
          </label>
          <input
            v-model="cardForm.cvv"
            @input="formatCvv"
            @blur="validateCvv"
            type="text"
            inputmode="numeric"
            :maxlength="cardBrand === 'amex' ? 4 : 3"
            placeholder="123"
            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
            :class="{ 'border-red-500': errors.cvv }"
          >
          <p v-if="errors.cvv" class="text-red-600 text-sm mt-1">{{ errors.cvv }}</p>
          
          <!-- CVV Info Tooltip -->
          <div v-if="showCvvInfo" class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded text-xs text-blue-800">
            <p class="font-semibold mb-1">Card Verification Value (CVV)</p>
            <p>• Visa/Mastercard: 3 digits on the back</p>
            <p>• American Express: 4 digits on the front</p>
          </div>
        </div>
      </div>

      <!-- Name on Card -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">
          Name on Card
        </label>
        <input
          v-model="cardForm.name"
          @blur="validateName"
          type="text"
          placeholder="JOHN DOE"
          class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors uppercase"
          :class="{ 'border-red-500': errors.name }"
        >
        <p v-if="errors.name" class="text-red-600 text-sm mt-1">{{ errors.name }}</p>
      </div>
    </div>

    <!-- Save Card Option -->
    <div class="mb-4">
      <label class="flex items-center space-x-2 cursor-pointer">
        <input 
          type="checkbox" 
          v-model="saveCard"
          class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
        >
        <span class="text-sm text-gray-700">Save card for future payments</span>
      </label>
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

    <!-- 3DS Modal -->
    <div v-if="show3DS" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h4 class="text-lg font-semibold mb-4">3D Secure Verification</h4>
        <p class="text-gray-600 mb-4">Please complete the verification with your bank.</p>
        <div id="3ds-container"></div>
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
        :disabled="processing || !stripe"
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { loadStripe } from '@stripe/stripe-js'
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

const { initiatePayment, confirmPayment, loading, error } = usePayment()
const { showToast } = useToast()

const stripe = ref(null)
const processing = ref(false)
const showCvvInfo = ref(false)
const show3DS = ref(false)

// Card form data
const cardForm = ref({
  number: '',
  expiry: '',
  cvv: '',
  name: ''
})

// Form errors
const errors = ref({
  cardNumber: '',
  expiry: '',
  cvv: '',
  name: ''
})

const cardBrand = ref('unknown')
const saveCard = ref(false)
const total = computed(() => props.amount + props.fee)

// Initialize Stripe
onMounted(async () => {
  try {
    const config = useRuntimeConfig()
    const stripeKey = config.public.stripePublishableKey
    
    if (!stripeKey) {
      error.value = 'Stripe is not configured. Please add NUXT_PUBLIC_STRIPE_PUBLISHABLE_KEY to .env'
      return
    }

    stripe.value = await loadStripe(stripeKey)
  } catch (err) {
    console.error('Failed to initialize Stripe:', err)
    error.value = 'Failed to initialize payment form'
  }
})

// Format card number with spaces
const formatCardNumber = (e) => {
  let value = e.target.value.replace(/\s/g, '').replace(/\D/g, '')
  
  // Detect card brand
  if (value.startsWith('34') || value.startsWith('37')) {
    cardBrand.value = 'amex'
  } else if (value.startsWith('4')) {
    cardBrand.value = 'visa'
  } else if (/^5[1-5]/.test(value)) {
    cardBrand.value = 'mastercard'
  } else {
    cardBrand.value = 'unknown'
  }
  
  // Format with spaces
  const groups = value.match(/.{1,4}/g)
  cardForm.value.number = groups ? groups.join(' ') : value
  errors.value.cardNumber = ''
}

// Format expiry MM/YY
const formatExpiry = (e) => {
  let value = e.target.value.replace(/\D/g, '')
  if (value.length >= 2) {
    value = value.slice(0, 2) + '/' + value.slice(2, 4)
  }
  cardForm.value.expiry = value
  errors.value.expiry = ''
}

// Format CVV
const formatCvv = (e) => {
  cardForm.value.cvv = e.target.value.replace(/\D/g, '')
  errors.value.cvv = ''
}

// Luhn algorithm validation
const luhnCheck = (cardNumber) => {
  const digits = cardNumber.replace(/\s/g, '').split('').reverse()
  let sum = 0
  for (let i = 0; i < digits.length; i++) {
    let digit = parseInt(digits[i])
    if (i % 2 === 1) {
      digit *= 2
      if (digit > 9) digit -= 9
    }
    sum += digit
  }
  return sum % 10 === 0
}

// Validate card number
const validateCardNumber = () => {
  const number = cardForm.value.number.replace(/\s/g, '')
  if (!number) {
    errors.value.cardNumber = 'Card number is required'
    return false
  }
  if (number.length < 13 || number.length > 19) {
    errors.value.cardNumber = 'Invalid card number length'
    return false
  }
  if (!luhnCheck(cardForm.value.number)) {
    errors.value.cardNumber = 'Invalid card number'
    return false
  }
  errors.value.cardNumber = ''
  return true
}

// Validate expiry
const validateExpiry = () => {
  const expiry = cardForm.value.expiry
  if (!expiry || !expiry.includes('/')) {
    errors.value.expiry = 'Invalid format (MM/YY)'
    return false
  }
  const [month, year] = expiry.split('/')
  const monthNum = parseInt(month)
  if (monthNum < 1 || monthNum > 12) {
    errors.value.expiry = 'Invalid month'
    return false
  }
  const currentYear = new Date().getFullYear() % 100
  const currentMonth = new Date().getMonth() + 1
  const yearNum = parseInt(year)
  if (yearNum < currentYear || (yearNum === currentYear && monthNum < currentMonth)) {
    errors.value.expiry = 'Card has expired'
    return false
  }
  errors.value.expiry = ''
  return true
}

// Validate CVV
const validateCvv = () => {
  if (!cardForm.value.cvv) {
    errors.value.cvv = 'CVV is required'
    return false
  }
  const expectedLength = cardBrand.value === 'amex' ? 4 : 3
  if (cardForm.value.cvv.length !== expectedLength) {
    errors.value.cvv = `CVV must be ${expectedLength} digits`
    return false
  }
  errors.value.cvv = ''
  return true
}

// Validate name
const validateName = () => {
  if (!cardForm.value.name.trim()) {
    errors.value.name = 'Name on card is required'
    return false
  }
  errors.value.name = ''
  return true
}

// Validate form
const validateForm = () => {
  return validateCardNumber() && validateExpiry() && validateCvv() && validateName()
}

// Handle payment
const handlePayment = async () => {
  if (!validateForm()) {
    error.value = 'Please correct the errors above'
    return
  }
  
  if (!stripe.value) {
    error.value = 'Stripe is not loaded'
    return
  }
  
  processing.value = true
  error.value = null
  
  try {
    const [expMonth, expYear] = cardForm.value.expiry.split('/')
    
    const { paymentMethod, error: stripeError } = await stripe.value.createPaymentMethod({
      type: 'card',
      card: {
        number: cardForm.value.number.replace(/\s/g, ''),
        exp_month: parseInt(expMonth),
        exp_year: parseInt('20' + expYear),
        cvc: cardForm.value.cvv
      },
      billing_details: {
        name: cardForm.value.name
      }
    })
    
    if (stripeError) {
      throw new Error(stripeError.message)
    }
    
    const response = await initiatePayment({
      amount: total.value,
      currency: 'MYR',
      payment_rail: 'card',
      stripe_payment_method_id: paymentMethod.id,
      description: props.description,
      save_payment_method: saveCard.value,
      metadata: props.metadata
    })
    
    if (response.requires_action && response.client_secret) {
      show3DS.value = true
      const { error: confirmError, paymentIntent } = await stripe.value.confirmCardPayment(response.client_secret)
      show3DS.value = false
      
      if (confirmError) throw new Error(confirmError.message)
      
      const confirmResponse = await confirmPayment(response.transaction.transaction_id, paymentIntent.id)
      if (confirmResponse.success) {
        showToast('Payment successful!', 'success')
        emit('payment-success', confirmResponse.transaction)
      }
    } else if (response.transaction.status === 'succeeded') {
      showToast('Payment successful!', 'success')
      emit('payment-success', response.transaction)
    }
  } catch (err) {
    console.error('Payment error:', err)
    error.value = err.message || 'Payment failed'
    showToast(error.value, 'error')
    emit('payment-failed', err)
  } finally {
    processing.value = false
  }
}
</script>

<style scoped>
input:focus {
  outline: none;
}
</style>
