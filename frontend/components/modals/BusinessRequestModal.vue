<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
    @click="$emit('update:show', false)"
  >
    <div
      class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
      @click.stop
    >
      <!-- Modal Header -->
      <div
        class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 rounded-t-2xl"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <div class="p-3 bg-white/20 rounded-xl mr-4">
              <Icon
                name="heroicons:building-office"
                class="h-8 w-8 text-white"
              />
            </div>
            <div>
              <h3 class="text-2xl font-bold text-white">
                Business Plan Request
              </h3>
              <p class="text-indigo-100 text-sm mt-1">
                Tell us about your organization
              </p>
            </div>
          </div>
          <button
            @click="$emit('update:show', false)"
            class="p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-colors"
          >
            <Icon name="heroicons:x-mark" class="h-6 w-6" />
          </button>
        </div>
      </div>

      <!-- Modal Body -->
      <form @submit.prevent="submitRequest" class="p-6 space-y-6">
        <!-- Company Name -->
        <div>
          <label class="block text-sm font-semibold text-secondary-900 mb-2">
            Company Name <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.company_name"
            type="text"
            required
            placeholder="Enter your company name"
            class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
          />
        </div>

        <!-- Company Address -->
        <div>
          <label class="block text-sm font-semibold text-secondary-900 mb-2">
            Company Address <span class="text-red-500">*</span>
          </label>
          <textarea
            v-model="form.company_address"
            required
            rows="3"
            placeholder="Enter your company address"
            class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
          ></textarea>
        </div>

        <!-- Number of Cards (Quota) -->
        <div>
          <label class="block text-sm font-semibold text-secondary-900 mb-2">
            Number of NFC Cards Needed <span class="text-red-500">*</span>
          </label>
          <input
            v-model.number="form.quota"
            type="number"
            required
            min="1"
            placeholder="e.g., 50"
            class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
          />
          <p class="text-xs text-secondary-500 mt-2">
            How many NFC cards do you need for your team?
          </p>
        </div>

        <!-- Contact Person -->
        <div>
          <label class="block text-sm font-semibold text-secondary-900 mb-2">
            Contact Person <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.contact_person"
            type="text"
            required
            placeholder="Full name"
            class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
          />
        </div>

        <!-- Phone Number -->
        <div>
          <label class="block text-sm font-semibold text-secondary-900 mb-2">
            Phone Number <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.phone"
            type="tel"
            required
            placeholder="+60 12-345 6789"
            class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
          />
        </div>

        <!-- Email -->
        <div>
          <label class="block text-sm font-semibold text-secondary-900 mb-2">
            Email Address <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.email"
            type="email"
            required
            placeholder="your@company.com"
            class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
          />
        </div>

        <!-- Additional Notes -->
        <div>
          <label class="block text-sm font-semibold text-secondary-900 mb-2">
            Additional Notes (Optional)
          </label>
          <textarea
            v-model="form.notes"
            rows="3"
            placeholder="Any special requirements or questions?"
            class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
          ></textarea>
        </div>

        <!-- Action Buttons -->
        <div class="flex space-x-4 pt-4">
          <button
            type="button"
            @click="$emit('update:show', false)"
            class="flex-1 py-3 px-4 bg-secondary-100 text-secondary-700 rounded-xl font-medium hover:bg-secondary-200 transition-colors"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="submitting"
            class="flex-1 py-3 px-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-medium hover:from-indigo-700 hover:to-purple-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
          >
            <div v-if="submitting" class="flex items-center">
              <div
                class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"
              ></div>
              Submitting...
            </div>
            <span v-else>Submit Request</span>
          </button>
        </div>

        <p class="text-xs text-secondary-500 text-center">
          Our team will contact you within 24 hours to discuss your Business
          Plan setup.
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  user: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['update:show'])

const { $toast } = useNuxtApp()
const authStore = useAuthStore()

const submitting = ref(false)
const form = reactive({
  company_name: '',
  company_address: '',
  quota: null,
  contact_person: '',
  phone: '',
  email: '',
  notes: ''
})

// Pre-fill user info when modal opens
watch(() => props.show, (newValue) => {
  if (newValue && props.user) {
    form.email = props.user.email || ''
    form.contact_person = props.user.first_name && props.user.last_name
      ? `${props.user.first_name} ${props.user.last_name}`
      : props.user.name || ''
  }
})

const resetForm = () => {
  form.company_name = ''
  form.company_address = ''
  form.quota = null
  form.contact_person = props.user?.first_name && props.user?.last_name
    ? `${props.user.first_name} ${props.user.last_name}`
    : props.user?.name || ''
  form.phone = ''
  form.email = props.user?.email || ''
  form.notes = ''
}

const submitRequest = async () => {
  submitting.value = true

  try {
    // Prepare email content
    const subject = `Business Plan Request - ${form.company_name}`
    const body = `
Business Plan Request Details:

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
COMPANY INFORMATION
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Company Name: ${form.company_name}
Company Address: ${form.company_address}
Number of NFC Cards Needed: ${form.quota} cards

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CONTACT INFORMATION
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Contact Person: ${form.contact_person}
Email: ${form.email}
Phone: ${form.phone}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
USER ACCOUNT DETAILS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
User: ${authStore.user?.first_name || 'Guest'} ${authStore.user?.last_name || 'User'}
User Email: ${authStore.user?.email || form.email}
Submitted: ${new Date().toLocaleString()}
${form.notes ? `\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nADDITIONAL NOTES\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n${form.notes}` : ''}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Please contact this customer within 24 hours.
This is an automated request from NFCGo platform.
`

    // Create Gmail compose URL
    const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent(
      'genn.chong@clbgroups.com'
    )}&su=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`

    // Open Gmail in new tab
    window.open(gmailUrl, '_blank')

    // Show success message
    $toast.success(
      "Opening Gmail... Please send the email to complete your Business Plan request."
    )

    // Close modal and redirect to Homepage with success parameter after a short delay
    setTimeout(() => {
      emit('update:show', false)
      resetForm()
      // Navigate to Homepage with success parameter if authenticated, otherwise login
      if (authStore.isAuthenticated) {
        window.location.href = '/Homepage?businessRequest=success'
      } else {
        window.location.href = '/UserAccount/login'
      }
    }, 2000)
  } catch (error) {
    console.error('Business plan request error:', error)
    $toast.error(
      'Failed to open email client. Please contact genn.chong@clbgroups.com directly.'
    )
  } finally {
    submitting.value = false
  }
}
</script>
