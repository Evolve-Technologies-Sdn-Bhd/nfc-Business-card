<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-md">
      <div class="text-center">
        <!-- Success Icon -->
        <div v-if="isSuccess" class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
          <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <!-- Failed Icon -->
        <div v-else class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
          <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </div>

        <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
          {{ isSuccess ? 'Payment Successful' : 'Payment Failed' }}
        </h2>
        <p class="mt-2 text-sm text-gray-600">
          {{ isSuccess ? 'Your transaction has been completed successfully.' : 'There was an issue processing your payment.' }}
        </p>
      </div>

      <div class="mt-8 space-y-4">
        <div class="flex justify-between border-b pb-2">
          <span class="text-gray-600">Order ID</span>
          <span class="font-medium">{{ orderId }}</span>
        </div>
        <div class="flex justify-between border-b pb-2">
          <span class="text-gray-600">Status</span>
          <span :class="isSuccess ? 'text-green-600 font-bold' : 'text-red-600 font-bold'">{{ status }}</span>
        </div>
        <div class="flex justify-between border-b pb-2">
          <span class="text-gray-600">Amount</span>
          <span class="font-medium">{{ currency }} {{ amount }}</span>
        </div>
        <div class="flex justify-between border-b pb-2">
          <span class="text-gray-600">Transaction ID</span>
          <span class="font-medium">{{ tranId }}</span>
        </div>
      </div>

      <div class="mt-8 space-y-3">
        <button 
          v-if="isSuccess" 
          @click="downloadInvoice" 
          :disabled="isDownloading"
          class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg v-if="!isDownloading" class="mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <svg v-else class="mr-2 h-5 w-5 text-gray-500 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ isDownloading ? 'Generating Invoice...' : 'Download Invoice' }}
        </button>

        <!-- Email Checkbox -->
        <div v-if="isSuccess" class="flex items-center gap-2 px-1">
          <input 
            type="checkbox" 
            id="sendEmailCheckbox"
            v-model="sendEmail"
            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-2 focus:ring-indigo-500"
          />
          <label 
            for="sendEmailCheckbox" 
            class="text-sm text-gray-600 cursor-pointer select-none"
          >
            Email me a copy of this invoice
          </label>
        </div>

        <button @click="handleReturn" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          Return to Dashboard
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '~/stores/auth'

const route = useRoute()
const config = useRuntimeConfig()

const orderId = route.query.order_id
const status = route.query.status
const statusCode = route.query.status_code
const amount = route.query.amount
const currency = route.query.currency
const tranId = route.query.tran_id

const isSuccess = computed(() => statusCode === '00')
const sendEmail = ref(false)
const isDownloading = ref(false)

const downloadInvoice = async () => {
    if (isDownloading.value) return
    
    // Validate transaction data
    if (!orderId || !tranId || !amount || !status) {
        const { $toast } = useNuxtApp()
        $toast.error('Transaction data is missing. Please contact support.')
        return
    }

    // Check if payment was successful
    if (statusCode !== '00') {
        const { $toast } = useNuxtApp()
        $toast.error('Invoice can only be downloaded for successful payments.')
        return
    }

    isDownloading.value = true
    const { $toast } = useNuxtApp()

    try {
        // Get auth token for the request
        const authStore = useAuthStore()
        const token = authStore.token || useCookie('auth_token').value

        // Build the API URL
        const apiBase = config.public.apiBase || 'http://localhost:8000/api'
        const downloadUrl = `${apiBase}/invoices/download-by-order`

        // Make request to generate and download invoice
        const response = await fetch(downloadUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/pdf',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({
                order_id: orderId,
                send_email: sendEmail.value
            })
        })

        if (!response.ok) {
            // Try to parse error message
            const contentType = response.headers.get('content-type')
            if (contentType && contentType.includes('application/json')) {
                const errorData = await response.json()
                throw new Error(errorData.message || 'Failed to generate invoice')
            }
            throw new Error('Failed to generate invoice')
        }

        // Get the PDF blob
        const blob = await response.blob()
        
        if (!blob || blob.size === 0) {
            throw new Error('Generated invoice is empty')
        }

        // Create download link and trigger download
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        link.download = `Invoice-${orderId}.pdf`
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        window.URL.revokeObjectURL(url)

        // Show success message
        if (sendEmail.value) {
            $toast.success('Invoice downloaded and sent to your email!')
        } else {
            $toast.success('Invoice downloaded to your computer!')
        }

    } catch (e) {
        console.error('Failed to download invoice', e)
        $toast.error(e.message || 'Unable to download invoice. Please try again later.')
    } finally {
        isDownloading.value = false
    }
}

const handleReturn = async () => {
    const authStore = useAuthStore()
    const { $toast } = useNuxtApp()
    
    // Payment status validation
    if (statusCode === '00' || status === 'Success') {
        // Payment successful - navigate to dashboard
        try {
            await authStore.fetchProfile()
            navigateTo('/UserDashboard')
            $toast.success('Payment successful! Welcome to your dashboard.')
        } catch (error) {
            console.error('Failed to refresh user data:', error)
            navigateTo('/UserDashboard')
        }
    } else if (['22', 'Pending', 'Processing', '33'].includes(statusCode)) {
        // Payment pending - go to homepage
        navigateTo('/Homepage')
        $toast.info('Your payment is pending. We will notify you once it is confirmed.')
    } else {
        // Payment failed or cancelled - go to homepage
        navigateTo('/Homepage')
        $toast.warning('Payment was not completed. Please try again if needed.')
    }
}
</script>
