import { ref } from 'vue'

/**
 * usePayment — payment composable for the Fiuu checkout flow.
 *
 * Only the methods actually consumed by the payment UI are kept here:
 *   - getPaymentRails / calculateFees  → PaymentMethodSelector
 *   - initiatePayment                  → FiuuPaymentForm, EWalletSelector, ManualBankTransfer
 *   - getTransaction                   → payment/status.vue
 *   - uploadPaymentProof               → ManualBankTransfer
 *
 * Remote endpoints: GET /payment/rails, POST /payment/calculate-fees,
 * POST /payment/initiate, GET /payment/transactions/{id},
 * POST /payment/transactions/{id}/upload-proof (all defined in routes/api.php).
 */
export const usePayment = () => {
  const loading = ref(false)
  const error = ref(null)

  const config = useRuntimeConfig()
  const baseURL = config.public.apiBaseUrl || 'http://localhost:8000/api'

  const getAuthHeaders = () => {
    // Read token from cookie (where auth store saves it)
    const tokenCookie = useCookie('auth-token')
    const token = tokenCookie.value
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

  // Upload payment proof (manual bank transfer)
  const uploadPaymentProof = async (transactionId, proofFile) => {
    loading.value = true
    error.value = null
    try {
      const formData = new FormData()
      formData.append('proof_file', proofFile)

      // Read token from cookie (where auth store saves it)
      const tokenCookie = useCookie('auth-token')
      const token = tokenCookie.value
      const response = await $fetch(`${baseURL}/payment/transactions/${transactionId}/upload-proof`, {
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

  return {
    loading,
    error,
    getPaymentRails,
    calculateFees,
    initiatePayment,
    getTransaction,
    uploadPaymentProof
  }
}
