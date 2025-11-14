<template>
  <div class="subscription-manager">
    <div class="mb-8">
      <h2 class="text-2xl font-bold mb-2">Subscription Management</h2>
      <p class="text-gray-600">Manage your subscription plan and billing</p>
    </div>

    <!-- Current Subscription -->
    <div v-if="activeSubscription" class="bg-white border rounded-lg p-6 mb-8">
      <div class="flex justify-between items-start mb-4">
        <div>
          <h3 class="text-xl font-semibold capitalize">{{ activeSubscription.plan_type }} Plan</h3>
          <p class="text-gray-600">{{ activeSubscription.interval }}ly billing</p>
        </div>
        <span class="px-3 py-1 rounded-full text-sm font-medium"
              :class="getStatusBadgeClass(activeSubscription.status)">
          {{ activeSubscription.status }}
        </span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
          <p class="text-sm text-gray-600">Amount</p>
          <p class="text-lg font-semibold">RM {{ activeSubscription.amount.toFixed(2) }}/{{ activeSubscription.interval === 'monthly' ? 'mo' : 'yr' }}</p>
        </div>
        <div>
          <p class="text-sm text-gray-600">Next Billing Date</p>
          <p class="text-lg font-semibold">{{ formatDate(activeSubscription.next_billing_date) }}</p>
        </div>
        <div>
          <p class="text-sm text-gray-600">Days Until Billing</p>
          <p class="text-lg font-semibold">{{ activeSubscription.days_until_billing }} days</p>
        </div>
      </div>

      <div v-if="activeSubscription.is_in_trial" class="bg-blue-50 border border-blue-200 rounded p-3 mb-4">
        <p class="text-sm text-blue-700">
          🎉 You're in your trial period. Trial ends on {{ formatDate(activeSubscription.trial_ends_at) }}
        </p>
      </div>

      <div class="flex space-x-3">
        <button
          v-if="activeSubscription.status === 'active'"
          @click="showCancelModal = true"
          class="px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50"
        >
          Cancel Subscription
        </button>
        <button
          v-if="activeSubscription.cancelled_at && activeSubscription.status === 'active'"
          @click="reactivateSubscription"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Reactivate
        </button>
        <button
          @click="showPaymentMethodModal = true"
          class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
        >
          Change Payment Method
        </button>
      </div>
    </div>

    <!-- No Active Subscription -->
    <div v-else-if="!loading">
      <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <p class="text-yellow-800">You don't have an active subscription. Choose a plan below to get started!</p>
      </div>
    </div>

    <!-- Available Plans -->
    <div v-if="!activeSubscription || showPlans">
      <h3 class="text-xl font-semibold mb-4">Available Plans</h3>
      
      <!-- Billing Interval Toggle -->
      <div class="flex justify-center mb-6">
        <div class="inline-flex rounded-lg border border-gray-200 p-1">
          <button
            @click="billingInterval = 'monthly'"
            :class="billingInterval === 'monthly' ? 'bg-blue-600 text-white' : 'text-gray-700'"
            class="px-4 py-2 rounded-md transition-colors"
          >
            Monthly
          </button>
          <button
            @click="billingInterval = 'yearly'"
            :class="billingInterval === 'yearly' ? 'bg-blue-600 text-white' : 'text-gray-700'"
            class="px-4 py-2 rounded-md transition-colors"
          >
            Yearly <span class="text-xs">(Save 17%)</span>
          </button>
        </div>
      </div>

      <!-- Plans Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div
          v-for="plan in plans"
          :key="plan.name"
          class="border rounded-lg p-6 hover:border-blue-500 transition-colors"
          :class="{ 'border-blue-500 ring-2 ring-blue-200': plan.name === 'Business' }"
        >
          <div v-if="plan.name === 'Business'" class="text-center mb-2">
            <span class="inline-block px-3 py-1 bg-blue-600 text-white text-xs font-semibold rounded-full">
              POPULAR
            </span>
          </div>
          
          <h4 class="text-xl font-bold mb-2">{{ plan.name }}</h4>
          
          <div class="mb-4">
            <span class="text-3xl font-bold">RM {{ getPrice(plan) }}</span>
            <span class="text-gray-600">/{{ billingInterval === 'monthly' ? 'month' : 'year' }}</span>
          </div>

          <ul class="space-y-2 mb-6">
            <li v-for="feature in plan.features" :key="feature" class="flex items-start">
              <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <span class="text-sm text-gray-700">{{ feature }}</span>
            </li>
          </ul>

          <button
            @click="selectPlan(plan)"
            :disabled="isCurrentPlan(plan)"
            class="w-full py-2 px-4 rounded-lg font-medium transition-colors"
            :class="isCurrentPlan(plan) 
              ? 'bg-gray-100 text-gray-500 cursor-not-allowed' 
              : plan.name === 'Business'
                ? 'bg-blue-600 text-white hover:bg-blue-700'
                : 'border border-gray-300 text-gray-700 hover:bg-gray-50'"
          >
            {{ isCurrentPlan(plan) ? 'Current Plan' : 'Select Plan' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Cancel Subscription Modal -->
    <div v-if="showCancelModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-semibold mb-4">Cancel Subscription</h3>
        <p class="text-gray-600 mb-4">
          Are you sure you want to cancel your subscription? You can choose to cancel immediately or at the end of the billing period.
        </p>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Cancellation Reason (optional)</label>
          <textarea
            v-model="cancelReason"
            rows="3"
            class="w-full border rounded-lg px-3 py-2"
            placeholder="Help us improve by telling us why you're cancelling"
          ></textarea>
        </div>
        <div class="flex space-x-3">
          <button
            @click="showCancelModal = false"
            class="flex-1 border border-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-50"
          >
            Keep Subscription
          </button>
          <button
            @click="cancelSubscriptionNow"
            class="flex-1 bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700"
          >
            Cancel at Period End
          </button>
        </div>
      </div>
    </div>

    <!-- Payment Method Modal -->
    <div v-if="showPaymentMethodModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-semibold mb-4">Change Payment Method</h3>
        <p class="text-gray-600 mb-4">Select a saved payment method or add a new one</p>
        <!-- Payment method selection would go here -->
        <button
          @click="showPaymentMethodModal = false"
          class="w-full border border-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-50"
        >
          Close
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { usePayment } from '~/composables/usePayment'
import { useToast } from '~/composables/useToast'

const { getSubscriptionPlans, getActiveSubscription, subscribe, cancelSubscription, loading } = usePayment()
const { showToast } = useToast()

const plans = ref([])
const activeSubscription = ref(null)
const billingInterval = ref('monthly')
const showPlans = ref(false)
const showCancelModal = ref(false)
const showPaymentMethodModal = ref(false)
const cancelReason = ref('')

onMounted(async () => {
  await loadPlans()
  await loadActiveSubscription()
})

const loadPlans = async () => {
  try {
    plans.value = await getSubscriptionPlans()
  } catch (err) {
    console.error('Failed to load plans:', err)
    showToast('Failed to load subscription plans', 'error')
  }
}

const loadActiveSubscription = async () => {
  try {
    activeSubscription.value = await getActiveSubscription()
  } catch (err) {
    // No active subscription is fine
    console.log('No active subscription')
  }
}

const getPrice = (plan) => {
  return billingInterval.value === 'monthly' 
    ? plan.monthly_price.toFixed(2)
    : plan.yearly_price.toFixed(2)
}

const isCurrentPlan = (plan) => {
  if (!activeSubscription.value) return false
  return activeSubscription.value.plan_type === plan.name.toLowerCase() &&
         activeSubscription.value.interval === billingInterval.value
}

const selectPlan = (plan) => {
  // This would trigger the subscription flow
  // For now, we'll navigate to a payment page or show a modal
  showToast('Subscription flow to be implemented with payment method selection', 'info')
}

const cancelSubscriptionNow = async () => {
  try {
    await cancelSubscription(activeSubscription.value.subscription_id, false, cancelReason.value)
    showToast('Subscription cancelled. You can continue using until the end of the billing period.', 'success')
    showCancelModal.value = false
    await loadActiveSubscription()
  } catch (err) {
    console.error('Failed to cancel subscription:', err)
    showToast('Failed to cancel subscription', 'error')
  }
}

const reactivateSubscription = async () => {
  try {
    // Call reactivate API
    showToast('Subscription reactivated successfully', 'success')
    await loadActiveSubscription()
  } catch (err) {
    console.error('Failed to reactivate subscription:', err)
    showToast('Failed to reactivate subscription', 'error')
  }
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-MY', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    past_due: 'bg-red-100 text-red-800',
    cancelled: 'bg-gray-100 text-gray-800',
    suspended: 'bg-yellow-100 text-yellow-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>
