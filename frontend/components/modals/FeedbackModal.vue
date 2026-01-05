<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
    @click="$emit('update:show', false)"
  >
    <div
      class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto"
      @click.stop
    >
      <div class="p-6 border-b border-secondary-200">
        <h3 class="text-xl font-bold text-secondary-900">Send Feedback</h3>
        <p class="text-sm text-secondary-600 mt-1">Help us improve by sharing your thoughts</p>
      </div>

      <form @submit.prevent="submitFeedback" class="p-6 space-y-4">
        <!-- Category -->
        <div>
          <label class="block text-sm font-medium text-secondary-700 mb-2">
            Category <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.category"
            required
            class="w-full px-4 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          >
            <option value="">Select a category</option>
            <option value="bug">🐛 Bug Report</option>
            <option value="feature">💡 Feature Request</option>
            <option value="question">❓ Question</option>
            <option value="complaint">😞 Complaint</option>
            <option value="suggestion">💭 Suggestion</option>
            <option value="other">📝 Other</option>
          </select>
        </div>

        <!-- Name (Optional) -->
        <div>
          <label class="block text-sm font-medium text-secondary-700 mb-2">
            Your Name (Optional)
          </label>
          <input
            v-model="form.user_name"
            type="text"
            placeholder="John Doe"
            class="w-full px-4 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          />
        </div>

        <!-- Email (Optional) -->
        <div>
          <label class="block text-sm font-medium text-secondary-700 mb-2">
            Your Email (Optional)
          </label>
          <input
            v-model="form.user_email"
            type="email"
            placeholder="john@example.com"
            class="w-full px-4 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          />
        </div>

        <!-- Message -->
        <div>
          <label class="block text-sm font-medium text-secondary-700 mb-2">
            Message <span class="text-red-500">*</span>
          </label>
          <textarea
            v-model="form.message"
            rows="4"
            required
            placeholder="Please describe your feedback in detail..."
            class="w-full px-4 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          ></textarea>
        </div>

        <!-- Rating -->
        <div>
          <label class="block text-sm font-medium text-secondary-700 mb-2">
            Overall Experience (Optional)
          </label>
          <div class="flex space-x-2">
            <button
              v-for="star in 5"
              :key="star"
              type="button"
              @click="form.rating = star"
              class="text-2xl focus:outline-none transition-transform hover:scale-110"
            >
              {{ star <= form.rating ? '⭐' : '☆' }}
            </button>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex space-x-3 pt-4">
          <button
            type="button"
            @click="$emit('update:show', false)"
            class="flex-1 py-2 px-4 bg-secondary-100 text-secondary-700 rounded-lg font-medium hover:bg-secondary-200 transition-colors"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="submitting"
            class="flex-1 py-2 px-4 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors disabled:opacity-50"
          >
            {{ submitting ? 'Sending...' : 'Send Feedback' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:show'])

const { $api, $toast } = useNuxtApp()

const submitting = ref(false)
const form = reactive({
  category: '',
  user_name: '',
  user_email: '',
  message: '',
  rating: 0
})

const resetForm = () => {
  form.category = ''
  form.user_name = ''
  form.user_email = ''
  form.message = ''
  form.rating = 0
}

const submitFeedback = async () => {
  if (!form.category || !form.message) {
    $toast.error('Please fill in all required fields')
    return
  }

  submitting.value = true

  try {
    const response = await $api.post('/chatbot/feedback', {
      category: form.category,
      message: form.message,
      user_name: form.user_name,
      user_email: form.user_email,
      rating: form.rating
    })

    if (response.success) {
      $toast.success('Thank you for your feedback!')
      resetForm()
      emit('update:show', false)
    } else {
      $toast.error(response.message || 'Failed to submit feedback')
    }
  } catch (error) {
    console.error('Feedback submission error:', error)
    let errorMessage = 'Failed to submit feedback. Please try again later.'
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    }
    $toast.error(errorMessage)
  } finally {
    submitting.value = false
  }
}

// Reset form when modal closes
watch(() => props.show, (newValue) => {
  if (!newValue) {
    resetForm()
  }
})
</script>
