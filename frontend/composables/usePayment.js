import { ref, computed } from 'vue'

export const usePayment = () => {
  const loading = ref(false)
  const error = ref(null)

  const config = useRuntimeConfig()
  const baseURL = config.public.apiBaseUrl || 'http://localhost:8000/api'

  const getAuthHeaders = () => {
    const token = localStorage.getItem('authToken')
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': token ? `Bearer ${token}` : ''
    }
  }

  // Get available payment rails
  const getPaymentRails = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch(`${baseURL}/payment/rails`, {
        method: 'GET',
        headers: getAuthHeaders()
      })
      return response
    } catch (err) {
      error.value = err.data?.message || 'Failed to fetch payment rails'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Calculate fees
  const calculateFees = async (amount, currency, paymentRail) => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch(`${baseURL}/payment/calculate-fees`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: { amount, currency, payment_rail: paymentRail }
      })
      return response.calculation
    } catch (err) {
      error.value = err.data?.message || 'Failed to calculate fees'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Initiate payment
  const initiatePayment = async (paymentData) => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch(`${baseURL}/payment/initiate`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: paymentData
      })
      return response
    } catch (err) {
      error.value = err.data?.message || 'Failed to initiate payment'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Confirm payment (3DS)
  const confirmPayment = async (transactionId, paymentIntentId) => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch(`${baseURL}/payment/transactions/${transactionId}/confirm`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: { payment_intent_id: paymentIntentId }
      })
      return response
    } catch (err) {
      error.value = err.data?.message || 'Failed to confirm payment'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Get transaction
  const getTransaction = async (transactionId) => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch(`${baseURL}/payment/transactions/${transactionId}`, {
        method: 'GET',
        headers: getAuthHeaders()
      })
      return response.transaction
    } catch (err) {
      error.value = err.data?.message || 'Failed to fetch transaction'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Get transaction history
  const getTransactionHistory = async (filters = {}) => {
    loading.value = true
    error.value = null
    try {
      const params = new URLSearchParams(filters).toString()
      const response = await $fetch(`${baseURL}/payment/transactions?${params}`, {
        method: 'GET',
        headers: getAuthHeaders()
      })
      return response.transactions
    } catch (err) {
      error.value = err.data?.message || 'Failed to fetch transaction history'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Upload payment proof
  const uploadPaymentProof = async (transactionId, proofFile) => {
    loading.value = true
    error.value = null
    try {
      const formData = new FormData()
      formData.append('proof_file', proofFile)

      const token = localStorage.getItem('authToken')
      const response = await $fetch(`${baseURL}/payment/transactions/${transactionId}/proof`, {
        method: 'POST',
        headers: {
          'Authorization': token ? `Bearer ${token}` : ''
        },
        body: formData
      })
      return response
    } catch (err) {
      error.value = err.data?.message || 'Failed to upload payment proof'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Get payment methods
  const getPaymentMethods = async (filters = {}) => {
    loading.value = true
    error.value = null
    try {
      const params = new URLSearchParams(filters).toString()
      const response = await $fetch(`${baseURL}/payment-methods?${params}`, {
        method: 'GET',
        headers: getAuthHeaders()
      })
      return response.payment_methods
    } catch (err) {
      error.value = err.data?.message || 'Failed to fetch payment methods'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Save payment method
  const savePaymentMethod = async (methodData) => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch(`${baseURL}/payment-methods`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: methodData
      })
      return response.payment_method
    } catch (err) {
      error.value = err.data?.message || 'Failed to save payment method'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Delete payment method
  const deletePaymentMethod = async (methodId) => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch(`${baseURL}/payment-methods/${methodId}`, {
        method: 'DELETE',
        headers: getAuthHeaders()
      })
      return response
    } catch (err) {
      error.value = err.data?.message || 'Failed to delete payment method'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Request refund
  const requestRefund = async (transactionId, amount, reason) => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch(`${baseURL}/refunds/transactions/${transactionId}/refund`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: { amount, reason }
      })
      return response.refund
    } catch (err) {
      error.value = err.data?.message || 'Failed to request refund'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Get refunds
  const getRefunds = async (filters = {}) => {
    loading.value = true
    error.value = null
    try {
      const params = new URLSearchParams(filters).toString()
      const response = await $fetch(`${baseURL}/refunds?${params}`, {
        method: 'GET',
        headers: getAuthHeaders()
      })
      return response.refunds
    } catch (err) {
      error.value = err.data?.message || 'Failed to fetch refunds'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Get subscription plans
  const getSubscriptionPlans = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch(`${baseURL}/subscriptions/plans`, {
        method: 'GET',
        headers: getAuthHeaders()
      })
      return response.plans
    } catch (err) {
      error.value = err.data?.message || 'Failed to fetch plans'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Get active subscription
  const getActiveSubscription = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch(`${baseURL}/subscriptions/active`, {
        method: 'GET',
        headers: getAuthHeaders()
      })
      return response.subscription
    } catch (err) {
      error.value = err.data?.message || 'Failed to fetch subscription'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Subscribe
  const subscribe = async (planType, interval, paymentMethodId, trialPeriodDays = 14) => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch(`${baseURL}/subscriptions/subscribe`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: { 
          plan_type: planType, 
          interval, 
          payment_method_id: paymentMethodId,
          trial_period_days: trialPeriodDays
        }
      })
      return response.subscription
    } catch (err) {
      error.value = err.data?.message || 'Failed to subscribe'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Cancel subscription
  const cancelSubscription = async (subscriptionId, cancelImmediately = false, reason = '') => {
    loading.value = true
    error.value = null
    try {
      const response = await $fetch(`${baseURL}/subscriptions/${subscriptionId}/cancel`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: { cancel_immediately: cancelImmediately, reason }
      })
      return response.subscription
    } catch (err) {
      error.value = err.data?.message || 'Failed to cancel subscription'
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    getPaymentRails,
    calculateFees,
    initiatePayment,
    confirmPayment,
    getTransaction,
    getTransactionHistory,
    uploadPaymentProof,
    getPaymentMethods,
    savePaymentMethod,
    deletePaymentMethod,
    requestRefund,
    getRefunds,
    getSubscriptionPlans,
    getActiveSubscription,
    subscribe,
    cancelSubscription
  }
}
