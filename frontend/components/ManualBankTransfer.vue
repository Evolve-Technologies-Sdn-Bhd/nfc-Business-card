<template>
  <div class="manual-bank-transfer">
    <h3 class="text-xl font-semibold mb-4">Manual Bank Transfer</h3>

    <!-- Payment Summary -->
    <div class="bg-gray-50 rounded-lg p-4 mb-6">
      <div class="flex justify-between items-center mb-2">
        <span class="text-gray-600">Amount to Transfer:</span>
        <span class="font-semibold text-lg">RM {{ amount.toFixed(2) }}</span>
      </div>
      <p class="text-sm text-gray-600 mt-2">No processing fees for manual bank transfer</p>
    </div>

    <!-- Bank Details (After initiating) -->
    <div v-if="bankDetails" class="mb-6">
      <!-- Reference Code (Most Important) -->
      <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-4 mb-4">
        <p class="text-sm font-medium text-blue-900 mb-2">Payment Reference Code</p>
        <div class="flex items-center justify-between bg-white rounded-lg p-3">
          <code class="text-xl font-bold text-blue-600">{{ bankDetails.reference_code }}</code>
          <button
            @click="copyToClipboard(bankDetails.reference_code)"
            class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm"
          >
            Copy
          </button>
        </div>
        <p class="text-xs text-blue-700 mt-2">
          ⚠️ IMPORTANT: Include this reference code in your bank transfer
        </p>
      </div>

      <!-- Bank Account Details -->
      <div class="space-y-3 mb-6">
        <div v-for="bank in bankDetails.banks" :key="bank.name" class="border rounded-lg p-4">
          <div class="flex items-center justify-between mb-3">
            <h4 class="font-semibold text-gray-800">{{ bank.name }}</h4>
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
            </svg>
          </div>
          <div class="space-y-2">
            <div>
              <p class="text-xs text-gray-600">Account Name</p>
              <p class="font-medium">{{ bankDetails.account_name }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-600">Account Number</p>
              <div class="flex items-center justify-between">
                <code class="font-medium">{{ bank.account_number }}</code>
                <button
                  @click="copyToClipboard(bank.account_number)"
                  class="text-xs text-blue-600 hover:text-blue-700"
                >
                  Copy
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Instructions -->
      <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <h4 class="font-semibold text-yellow-900 mb-2 flex items-center">
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
          Transfer Instructions
        </h4>
        <ol class="text-sm text-yellow-800 list-decimal list-inside space-y-1">
          <li>Transfer exactly RM {{ amount.toFixed(2) }} to one of the accounts above</li>
          <li>Include the reference code <strong>{{ bankDetails.reference_code }}</strong> in the transfer remarks</li>
          <li>Save your transaction receipt</li>
          <li>Upload the receipt below for verification</li>
          <li>We will verify and confirm your payment within 1-2 business days</li>
        </ol>
      </div>

      <!-- Upload Payment Proof -->
      <div class="border-2 border-dashed rounded-lg p-6 mb-4">
        <h4 class="font-semibold mb-3">Upload Payment Receipt</h4>
        
        <!-- Preview -->
        <div v-if="proofPreview" class="mb-4">
          <img :src="proofPreview" alt="Receipt preview" class="max-w-sm rounded-lg border" />
          <button
            @click="removeProof"
            class="mt-2 text-sm text-red-600 hover:text-red-700"
          >
            Remove
          </button>
        </div>

        <!-- Upload Input -->
        <div v-if="!proofPreview" class="text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
          </svg>
          <input
            ref="fileInput"
            type="file"
            accept="image/*,.pdf"
            @change="handleFileSelect"
            class="hidden"
          />
          <button
            @click="$refs.fileInput.click()"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Choose File
          </button>
          <p class="text-xs text-gray-600 mt-2">
            Accepted formats: JPG, PNG, PDF (max 5MB)
          </p>
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
          @click="uploadProof"
          :disabled="!proofFile || uploading"
          class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
        >
          <span v-if="!uploading">Submit Receipt</span>
          <span v-else class="flex items-center justify-center">
            <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Uploading...
          </span>
        </button>
      </div>
    </div>

    <!-- Initial Generate Reference Button -->
    <div v-else>
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
        <p class="text-sm text-blue-700">
          Click below to generate your unique payment reference code and view bank account details.
        </p>
      </div>

      <!-- Error Message -->
      <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
        {{ error }}
      </div>

      <div class="flex space-x-3">
        <button
          @click="$emit('back')"
          type="button"
          class="flex-1 border border-gray-300 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-50 transition-colors font-medium"
        >
          Back
        </button>
        <button
          @click="generateReference"
          :disabled="processing"
          class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
        >
          <span v-if="!processing">Generate Reference Code</span>
          <span v-else class="flex items-center justify-center">
            <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Generating...
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { usePayment } from '~/composables/usePayment'
import { useToast } from '~/composables/useToast'

const props = defineProps({
  amount: {
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

const emit = defineEmits(['payment-initiated', 'proof-uploaded', 'back'])

const { initiatePayment, uploadPaymentProof, loading, error } = usePayment()
const { showToast } = useToast()

const processing = ref(false)
const uploading = ref(false)
const bankDetails = ref(null)
const transactionId = ref(null)
const proofFile = ref(null)
const proofPreview = ref(null)
const fileInput = ref(null)

const generateReference = async () => {
  processing.value = true
  error.value = null

  try {
    const response = await initiatePayment({
      amount: props.amount,
      currency: 'MYR',
      payment_rail: 'manual_bank',
      description: props.description,
      metadata: props.metadata
    })

    if (response.success) {
      bankDetails.value = response.bank_details
      transactionId.value = response.transaction.transaction_id
      showToast('Reference code generated successfully', 'success')
      emit('payment-initiated', response.transaction)
    } else {
      throw new Error('Failed to generate reference code')
    }
  } catch (err) {
    console.error('Manual transfer error:', err)
    error.value = err.message || 'Failed to generate reference code'
    showToast(error.value, 'error')
  } finally {
    processing.value = false
  }
}

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (!file) return

  // Validate file size (5MB max)
  if (file.size > 5 * 1024 * 1024) {
    error.value = 'File size must be less than 5MB'
    return
  }

  // Validate file type
  const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf']
  if (!validTypes.includes(file.type)) {
    error.value = 'Invalid file type. Please upload JPG, PNG, or PDF'
    return
  }

  proofFile.value = file
  error.value = null

  // Create preview for images
  if (file.type.startsWith('image/')) {
    const reader = new FileReader()
    reader.onload = (e) => {
      proofPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  } else {
    proofPreview.value = 'PDF file selected'
  }
}

const removeProof = () => {
  proofFile.value = null
  proofPreview.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const uploadProof = async () => {
  if (!proofFile.value) {
    error.value = 'Please select a receipt to upload'
    return
  }

  uploading.value = true
  error.value = null

  try {
    const response = await uploadPaymentProof(transactionId.value, proofFile.value)

    if (response.success) {
      showToast('Receipt uploaded successfully. We will verify your payment soon.', 'success')
      emit('proof-uploaded', response.transaction)
    } else {
      throw new Error('Failed to upload receipt')
    }
  } catch (err) {
    console.error('Upload error:', err)
    error.value = err.message || 'Failed to upload receipt'
    showToast(error.value, 'error')
  } finally {
    uploading.value = false
  }
}

const copyToClipboard = async (text) => {
  try {
    await navigator.clipboard.writeText(text)
    showToast('Copied to clipboard', 'success')
  } catch (err) {
    console.error('Failed to copy:', err)
    showToast('Failed to copy', 'error')
  }
}
</script>

<style scoped>
code {
  font-family: 'Courier New', monospace;
}
</style>
