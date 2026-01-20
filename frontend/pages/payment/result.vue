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
        <button v-if="isSuccess" @click="downloadInvoice" class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          <svg class="mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Download Invoice
        </button>

        <button @click="handleReturn" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          Return to Dashboard
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const route = useRoute()

const orderId = route.query.order_id
const status = route.query.status
const statusCode = route.query.status_code
const amount = route.query.amount
const currency = route.query.currency
const tranId = route.query.tran_id

const isSuccess = computed(() => statusCode === '00')

const downloadInvoice = async () => {
    try {
        // Find transaction/invoice first
        const { data: transaction } = await useFetch(`/api/payment/transactions?order_id=${orderId}`)
        // Note: This API implementation assumes we can filter by order_id or we need a specific endpoint
        // Alternatively, since we don't have a direct "get invoice by order id" publicly exposed yet, 
        // we might need to rely on the user navigating to dashboard to see it, OR implement a specific lookup.
        // For now, let's open the dashboard invoice page
        navigateTo('/dashboard/invoices')
    } catch (e) {
        console.error('Failed to download invoice', e)
    }
}

const handleReturn = () => {
    // Determine dashboard route based on user plan (mock logic for now if plan not available in query)
    // Real implementation would check user store
    navigateTo('/dashboard')
}
</script>
