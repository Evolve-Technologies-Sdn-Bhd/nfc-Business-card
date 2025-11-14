<template>
  <div class="payment-method-selector">
    <h3 class="text-xl font-semibold mb-4">Select Payment Method</h3>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error State -->
    <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
      {{ error }}
    </div>

    <!-- Payment Rails -->
    <div v-if="!loading && rails" class="space-y-4">
      <!-- Card Payment -->
      <div 
        v-if="rails.card"
        class="payment-option border rounded-lg p-4 cursor-pointer hover:border-blue-500 transition-colors"
        :class="{ 'border-blue-500 bg-blue-50': selectedRail === 'card' }"
        @click="selectRail('card')"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="payment-icon">
              <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
            </div>
            <div>
              <h4 class="font-medium">{{ rails.card.name }}</h4>
              <p class="text-sm text-gray-600">Visa, Mastercard, AMEX</p>
            </div>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-600">
              {{ rails.card.fees?.percentage || 2.9 }}% + RM{{ rails.card.fees?.fixed || 0.50 }}
            </p>
            <span class="text-xs text-green-600 flex items-center">
              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              Instant
            </span>
          </div>
        </div>
      </div>

      <!-- FPX Online Banking -->
      <div 
        v-if="rails.fpx"
        class="payment-option border rounded-lg p-4 cursor-pointer hover:border-blue-500 transition-colors"
        :class="{ 'border-blue-500 bg-blue-50': selectedRail === 'fpx' }"
        @click="selectRail('fpx')"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="payment-icon">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
              </svg>
            </div>
            <div>
              <h4 class="font-medium">{{ rails.fpx.name }}</h4>
              <p class="text-sm text-gray-600">Maybank, CIMB, and more</p>
            </div>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-600">{{ rails.fpx.fees?.percentage || 1.5 }}%</p>
            <span class="text-xs text-blue-600 flex items-center">
              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              Instant
            </span>
          </div>
        </div>
      </div>

      <!-- E-Wallet -->
      <div 
        v-if="rails.ewallet"
        class="payment-option border rounded-lg p-4 cursor-pointer hover:border-blue-500 transition-colors"
        :class="{ 'border-blue-500 bg-blue-50': selectedRail === 'ewallet' }"
        @click="selectRail('ewallet')"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="payment-icon">
              <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
            </div>
            <div>
              <h4 class="font-medium">{{ rails.ewallet.name }}</h4>
              <p class="text-sm text-gray-600">TNG, GrabPay, Boost, ShopeePay</p>
            </div>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-600">{{ rails.ewallet.fees?.percentage || 2.0 }}%</p>
            <span class="text-xs text-blue-600 flex items-center">
              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              Instant
            </span>
          </div>
        </div>
      </div>

      <!-- Manual Bank Transfer -->
      <div 
        v-if="rails.manual_bank_transfer"
        class="payment-option border rounded-lg p-4 cursor-pointer hover:border-blue-500 transition-colors"
        :class="{ 'border-blue-500 bg-blue-50': selectedRail === 'manual_bank_transfer' }"
        @click="selectRail('manual_bank_transfer')"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="payment-icon">
              <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
              </svg>
            </div>
            <div>
              <h4 class="font-medium">{{ rails.manual_bank_transfer.name }}</h4>
              <p class="text-sm text-gray-600">Direct bank transfer</p>
            </div>
          </div>
          <div class="text-right">
            <p class="text-sm text-gray-600">No fees</p>
            <span class="text-xs text-yellow-600 flex items-center">
              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
              </svg>
              1-2 days
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Fee Calculation -->
    <div v-if="selectedRail && amount > 0" class="mt-6 bg-gray-50 rounded-lg p-4">
      <div class="flex justify-between items-center mb-2">
        <span class="text-gray-600">Amount:</span>
        <span class="font-medium">RM {{ amount.toFixed(2) }}</span>
      </div>
      <div v-if="feeCalculation && feeCalculation.fee !== undefined" class="flex justify-between items-center mb-2">
        <span class="text-gray-600">Processing Fee:</span>
        <span class="font-medium">RM {{ feeCalculation.fee.toFixed(2) }}</span>
      </div>
      <div v-if="feeCalculation && feeCalculation.total !== undefined" class="flex justify-between items-center pt-2 border-t border-gray-200">
        <span class="font-semibold">Total:</span>
        <span class="font-semibold text-lg">RM {{ feeCalculation.total.toFixed(2) }}</span>
      </div>
    </div>

    <!-- Continue Button -->
    <button
      v-if="selectedRail"
      @click="continueToPayment"
      :disabled="loading"
      class="w-full mt-6 bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
    >
      Continue with {{ getRailName(selectedRail) }}
    </button>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { usePayment } from '~/composables/usePayment'

const props = defineProps({
  amount: {
    type: Number,
    required: true
  },
  currency: {
    type: String,
    default: 'MYR'
  }
})

const emit = defineEmits(['rail-selected'])

// Default payment rails (fallback if API fails) - Define FIRST
const defaultRails = {
  card: {
    name: 'Credit/Debit Card',
    enabled: true,
    fees: {
      percentage: 2.9,
      fixed: 0.50
    }
  },
  fpx: {
    name: 'FPX Online Banking',
    enabled: true,
    fees: {
      percentage: 1.5,
      fixed: 0
    }
  },
  ewallet: {
    name: 'E-Wallet',
    enabled: true,
    fees: {
      percentage: 2.0,
      fixed: 0
    }
  },
  manual_bank_transfer: {
    name: 'Manual Bank Transfer',
    enabled: true,
    fees: {
      percentage: 0,
      fixed: 0
    }
  }
}

const { getPaymentRails, calculateFees, loading, error } = usePayment()

const rails = ref(defaultRails) // Initialize with defaults immediately
const selectedRail = ref(null)
const feeCalculation = ref(null)

onMounted(async () => {
  console.log('PaymentMethodSelector mounted with amount:', props.amount);
  console.log('Initial rails:', rails.value);
  
  try {
    const response = await getPaymentRails()
    console.log('API response:', response);
    // API returns { success: true, payment_rails: {...} }
    let apiRails = response.payment_rails || response.rails
    
    // Transform API structure to match component expectations
    if (apiRails) {
      // Map manual_bank to manual_bank_transfer
      if (apiRails.manual_bank) {
        apiRails.manual_bank_transfer = apiRails.manual_bank
        delete apiRails.manual_bank
      }
      
      // Transform fee structure from API format to component format
      Object.keys(apiRails).forEach(railKey => {
        const rail = apiRails[railKey]
        if (rail.fee_percentage !== undefined || rail.fee_fixed !== undefined) {
          rail.fees = {
            percentage: rail.fee_percentage || 0,
            fixed: rail.fee_fixed || 0
          }
        }
      })
    }
    
    rails.value = apiRails
    console.log('Payment rails loaded from API:', rails.value);
  } catch (err) {
    console.error('Failed to load payment rails from API, using defaults:', err)
    // Use default rails if API fails
    rails.value = defaultRails
    error.value = null // Clear error to show default options
    console.log('Using default rails:', rails.value);
  }
})

const selectRail = (rail) => {
  selectedRail.value = rail
}

// Manual fee calculation function
const calculateFeesManually = (amount, rail) => {
  const railConfig = rails.value[rail]
  if (!railConfig) return null

  const percentageFee = (amount * railConfig.fees.percentage) / 100
  const fixedFee = railConfig.fees.fixed
  const totalFee = percentageFee + fixedFee
  const total = amount + totalFee

  return {
    fees: {
      percentage_fee: percentageFee,
      fixed_fee: fixedFee,
      total_fee: totalFee
    },
    fee: totalFee,
    total: total,
    subtotal: amount
  }
}

// Calculate fees when rail is selected
watch([selectedRail, () => props.amount], async ([newRail, newAmount]) => {
  if (newRail && newAmount > 0) {
    try {
      // Try API calculation first
      feeCalculation.value = await calculateFees(newAmount, props.currency, newRail)
    } catch (err) {
      console.error('Failed to calculate fees via API, calculating manually:', err)
      // Fallback to manual calculation
      feeCalculation.value = calculateFeesManually(newAmount, newRail)
    }
  }
}, { immediate: true })

const getRailName = (rail) => {
  const names = {
    card: 'Card',
    fpx: 'FPX',
    ewallet: 'E-Wallet',
    manual_bank_transfer: 'Bank Transfer'
  }
  return names[rail] || rail
}

const continueToPayment = () => {
  console.log('Emitting rail-selected:', selectedRail.value, feeCalculation.value);
  emit('rail-selected', {
    rail: selectedRail.value,
    feeCalculation: feeCalculation.value
  })
}
</script>

<style scoped>
.payment-option {
  transition: all 0.2s ease;
}

.payment-option:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
</style>
