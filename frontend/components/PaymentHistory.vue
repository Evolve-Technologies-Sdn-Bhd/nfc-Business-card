<template>
  <div class="payment-history">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold">Payment History</h2>
      <button
        @click="refreshHistory"
        :disabled="loading"
        class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center space-x-2"
      >
        <svg class="w-4 h-4" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <span>Refresh</span>
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white border rounded-lg p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="filters.status" class="w-full border rounded-lg px-3 py-2">
            <option value="">All Status</option>
            <option value="succeeded">Succeeded</option>
            <option value="pending">Pending</option>
            <option value="failed">Failed</option>
            <option value="refunded">Refunded</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
          <select v-model="filters.payment_rail" class="w-full border rounded-lg px-3 py-2">
            <option value="">All Methods</option>
            <option value="card">Card</option>
            <option value="fpx">FPX</option>
            <option value="ewallet">E-Wallet</option>
            <option value="manual_bank">Bank Transfer</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
          <input
            type="date"
            v-model="filters.from_date"
            class="w-full border rounded-lg px-3 py-2"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
          <input
            type="date"
            v-model="filters.to_date"
            class="w-full border rounded-lg px-3 py-2"
          />
        </div>
      </div>
      <div class="mt-4 flex justify-end">
        <button
          @click="applyFilters"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Apply Filters
        </button>
      </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white border rounded-lg overflow-hidden">
      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <div v-else-if="transactions && transactions.data && transactions.data.length > 0">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Transaction ID
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Amount
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Method
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="transaction in transactions.data" :key="transaction.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <code class="text-sm font-medium text-gray-900">{{ transaction.transaction_id }}</code>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ formatDate(transaction.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm font-medium">RM {{ transaction.amount.toFixed(2) }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="getMethodBadgeClass(transaction.payment_rail)">
                    {{ getMethodName(transaction.payment_rail) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="getStatusBadgeClass(transaction.status)">
                    {{ transaction.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <button
                    @click="viewDetails(transaction)"
                    class="text-blue-600 hover:text-blue-700 mr-3"
                  >
                    View
                  </button>
                  <button
                    v-if="canRefund(transaction)"
                    @click="openRefundModal(transaction)"
                    class="text-green-600 hover:text-green-700"
                  >
                    Refund
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t">
          <div class="text-sm text-gray-700">
            Showing {{ transactions.from || 0 }} to {{ transactions.to || 0 }} of {{ transactions.total || 0 }} results
          </div>
          <div class="flex space-x-2">
            <button
              @click="changePage(transactions.current_page - 1)"
              :disabled="transactions.current_page <= 1"
              class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50"
            >
              Previous
            </button>
            <button
              @click="changePage(transactions.current_page + 1)"
              :disabled="transactions.current_page >= transactions.last_page"
              class="px-3 py-1 border rounded hover:bg-gray-100 disabled:opacity-50"
            >
              Next
            </button>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p class="text-gray-600">No transactions found</p>
      </div>
    </div>

    <!-- Transaction Details Modal -->
    <div v-if="selectedTransaction" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-start mb-4">
          <h3 class="text-xl font-semibold">Transaction Details</h3>
          <button @click="selectedTransaction = null" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="space-y-3">
          <div class="flex justify-between py-2 border-b">
            <span class="text-gray-600">Transaction ID:</span>
            <code class="font-medium">{{ selectedTransaction.transaction_id }}</code>
          </div>
          <div class="flex justify-between py-2 border-b">
            <span class="text-gray-600">Status:</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="getStatusBadgeClass(selectedTransaction.status)">
              {{ selectedTransaction.status }}
            </span>
          </div>
          <div class="flex justify-between py-2 border-b">
            <span class="text-gray-600">Amount:</span>
            <span class="font-medium">RM {{ selectedTransaction.amount.toFixed(2) }}</span>
          </div>
          <div class="flex justify-between py-2 border-b">
            <span class="text-gray-600">Payment Method:</span>
            <span>{{ getMethodName(selectedTransaction.payment_rail) }}</span>
          </div>
          <div class="flex justify-between py-2 border-b">
            <span class="text-gray-600">Date:</span>
            <span>{{ formatDate(selectedTransaction.created_at) }}</span>
          </div>
          <div v-if="selectedTransaction.description" class="flex justify-between py-2 border-b">
            <span class="text-gray-600">Description:</span>
            <span>{{ selectedTransaction.description }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Refund Modal -->
    <div v-if="refundTransaction" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-semibold mb-4">Request Refund</h3>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Refund Amount (RM)</label>
          <input
            type="number"
            v-model="refundAmount"
            :max="refundTransaction.amount"
            step="0.01"
            class="w-full border rounded-lg px-3 py-2"
          />
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
          <textarea
            v-model="refundReason"
            rows="3"
            class="w-full border rounded-lg px-3 py-2"
            placeholder="Please provide a reason for the refund"
          ></textarea>
        </div>
        <div class="flex space-x-3">
          <button
            @click="refundTransaction = null"
            class="flex-1 border border-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="submitRefund"
            :disabled="!refundAmount || !refundReason"
            class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            Submit Refund
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { usePayment } from '~/composables/usePayment'
import { useToast } from '~/composables/useToast'

const { getTransactionHistory, requestRefund, loading } = usePayment()
const { showToast } = useToast()

const transactions = ref(null)
const filters = ref({
  status: '',
  payment_rail: '',
  from_date: '',
  to_date: '',
  per_page: 20
})
const selectedTransaction = ref(null)
const refundTransaction = ref(null)
const refundAmount = ref(0)
const refundReason = ref('')

onMounted(() => {
  loadTransactions()
})

const loadTransactions = async () => {
  try {
    const data = await getTransactionHistory(filters.value)
    transactions.value = data
  } catch (err) {
    console.error('Failed to load transactions:', err)
    showToast('Failed to load payment history', 'error')
  }
}

const refreshHistory = () => {
  loadTransactions()
}

const applyFilters = () => {
  loadTransactions()
}

const changePage = (page) => {
  filters.value.page = page
  loadTransactions()
}

const viewDetails = (transaction) => {
  selectedTransaction.value = transaction
}

const canRefund = (transaction) => {
  return transaction.status === 'succeeded' && !transaction.refunded_amount
}

const openRefundModal = (transaction) => {
  refundTransaction.value = transaction
  refundAmount.value = transaction.amount
  refundReason.value = ''
}

const submitRefund = async () => {
  try {
    await requestRefund(
      refundTransaction.value.transaction_id,
      refundAmount.value,
      refundReason.value
    )
    showToast('Refund request submitted successfully', 'success')
    refundTransaction.value = null
    loadTransactions()
  } catch (err) {
    console.error('Refund request failed:', err)
    showToast('Failed to submit refund request', 'error')
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString('en-MY', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStatusBadgeClass = (status) => {
  const classes = {
    succeeded: 'bg-green-100 text-green-800',
    pending: 'bg-yellow-100 text-yellow-800',
    failed: 'bg-red-100 text-red-800',
    refunded: 'bg-gray-100 text-gray-800',
    processing: 'bg-blue-100 text-blue-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getMethodBadgeClass = (rail) => {
  const classes = {
    card: 'bg-blue-100 text-blue-800',
    fpx: 'bg-green-100 text-green-800',
    ewallet: 'bg-purple-100 text-purple-800',
    manual_bank: 'bg-gray-100 text-gray-800'
  }
  return classes[rail] || 'bg-gray-100 text-gray-800'
}

const getMethodName = (rail) => {
  const names = {
    card: 'Card',
    fpx: 'FPX',
    ewallet: 'E-Wallet',
    manual_bank: 'Bank Transfer'
  }
  return names[rail] || rail
}
</script>
