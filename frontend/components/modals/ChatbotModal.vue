<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4 bg-black/50 backdrop-blur-sm"
  >
    <div class="bg-white rounded-t-3xl sm:rounded-2xl w-full sm:max-w-2xl h-[80vh] sm:h-[600px] flex flex-col shadow-2xl">
      <!-- Header -->
      <div class="flex items-center justify-between p-4 border-b border-secondary-200 bg-gradient-to-r from-primary-600 to-primary-500 rounded-t-3xl sm:rounded-t-2xl">
        <div class="flex items-center">
          <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center mr-3">
            <Icon name="heroicons:chat-bubble-left-right" class="h-6 w-6 text-white" />
          </div>
          <div>
            <h3 class="text-lg font-bold text-white">AI Assistant</h3>
            <p class="text-xs text-primary-100">Ask me anything about NFCGo</p>
          </div>
        </div>
        <button
          @click="$emit('update:show', false)"
          class="p-2 hover:bg-white/20 rounded-lg transition-colors"
        >
          <Icon name="heroicons:x-mark" class="h-6 w-6 text-white" />
        </button>
      </div>

      <!-- Messages Container -->
      <div class="flex-1 overflow-y-auto p-4 space-y-4" ref="chatMessagesRef">
        <!-- Welcome Message -->
        <div v-if="messages.length === 0" class="text-center py-8">
          <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <Icon name="heroicons:sparkles" class="h-8 w-8 text-primary-600" />
          </div>
          <h4 class="text-lg font-semibold text-secondary-900 mb-2">Welcome to NFCGo AI Assistant!</h4>
          <p class="text-sm text-secondary-600 mb-4">I can help you with:</p>
          
          <div class="grid grid-cols-1 gap-2 max-w-sm mx-auto text-left">
            <button
              @click="sendQuickMessage('How does NFC business card work?')"
              class="p-3 bg-secondary-50 hover:bg-secondary-100 rounded-lg text-sm text-secondary-700 transition-colors text-left"
            >
              💡 How does NFC business card work?
            </button>
            <button
              @click="sendQuickMessage('What are the pricing plans?')"
              class="p-3 bg-secondary-50 hover:bg-secondary-100 rounded-lg text-sm text-secondary-700 transition-colors text-left"
            >
              💰 What are the pricing plans?
            </button>
            <button
              @click="sendQuickMessage('Can I customize my digital profile?')"
              class="p-3 bg-secondary-50 hover:bg-secondary-100 rounded-lg text-sm text-secondary-700 transition-colors text-left"
            >
              🎨 Can I customize my digital profile?
            </button>
          </div>
        </div>

        <!-- Chat Messages -->
        <div
          v-for="(message, index) in messages"
          :key="index"
          :class="[
            'flex',
            message.sender === 'user' ? 'justify-end' : 'justify-start'
          ]"
        >
          <div class="max-w-[80%]">
            <div
              :class="[
                'rounded-2xl px-4 py-3',
                message.sender === 'user'
                  ? 'bg-primary-600 text-white rounded-br-sm'
                  : 'bg-secondary-100 text-secondary-900 rounded-bl-sm'
              ]"
            >
              <p class="text-sm whitespace-pre-wrap">{{ message.text }}</p>
              <p
                :class="[
                  'text-xs mt-1',
                  message.sender === 'user' ? 'text-primary-100' : 'text-secondary-500'
                ]"
              >
                {{ formatTime(message.timestamp) }}
              </p>
            </div>
            
            <!-- Feedback buttons for AI responses -->
            <div v-if="message.sender === 'ai' && message.questionId && !message.feedbackGiven" class="flex items-center space-x-2 mt-2 ml-2">
              <span class="text-xs text-secondary-500">Was this helpful?</span>
              <button
                @click="submitFeedback(message, true)"
                class="p-1 hover:bg-green-100 rounded transition-colors"
                title="Helpful"
              >
                <Icon name="heroicons:hand-thumb-up" class="h-4 w-4 text-green-600" />
              </button>
              <button
                @click="submitFeedback(message, false)"
                class="p-1 hover:bg-red-100 rounded transition-colors"
                title="Not helpful"
              >
                <Icon name="heroicons:hand-thumb-down" class="h-4 w-4 text-red-600" />
              </button>
            </div>
            <div v-else-if="message.sender === 'ai' && message.feedbackGiven" class="ml-2 mt-1">
              <span class="text-xs text-green-600">✓ Thanks for your feedback!</span>
            </div>
          </div>
        </div>

        <!-- Typing Indicator -->
        <div v-if="isTyping" class="flex justify-start">
          <div class="bg-secondary-100 rounded-2xl rounded-bl-sm px-4 py-3">
            <div class="flex space-x-2">
              <div class="w-2 h-2 bg-secondary-400 rounded-full animate-bounce" style="animation-delay: 0s"></div>
              <div class="w-2 h-2 bg-secondary-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
              <div class="w-2 h-2 bg-secondary-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Input Area -->
      <div class="p-4 border-t border-secondary-200 bg-secondary-50">
        <form @submit.prevent="sendMessage" class="flex space-x-2">
          <input
            v-model="chatInput"
            type="text"
            placeholder="Type your message..."
            class="flex-1 px-4 py-3 border border-secondary-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            :disabled="isTyping"
          />
          <button
            type="submit"
            :disabled="!chatInput.trim() || isTyping"
            class="btn btn-primary px-6 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <Icon name="heroicons:paper-airplane" class="h-5 w-5" />
          </button>
        </form>
        <button
          @click="$emit('open-feedback')"
          class="mt-2 text-xs text-primary-600 hover:text-primary-700 flex items-center space-x-1"
        >
          <Icon name="heroicons:chat-bubble-bottom-center-text" class="h-4 w-4" />
          <span>Send us feedback or report an issue</span>
        </button>
      </div>
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

const emit = defineEmits(['update:show', 'open-feedback'])

const chatMessagesRef = ref(null)
const chatId = ref(null)
const chatInput = ref('')
const messages = ref([])
const isTyping = ref(false)

// Initialize chat session when modal opens
watch(() => props.show, (newValue) => {
  if (newValue && !chatId.value) {
    chatId.value = Date.now()
  }
})

const formatTime = (date) => {
  return new Date(date).toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit'
  })
}

const scrollToBottom = () => {
  nextTick(() => {
    if (chatMessagesRef.value) {
      chatMessagesRef.value.scrollTop = chatMessagesRef.value.scrollHeight
    }
  })
}

const sendQuickMessage = (message) => {
  chatInput.value = message
  sendMessage()
}

const sendMessage = async () => {
  if (!chatInput.value.trim()) return

  const userMessage = {
    sender: 'user',
    text: chatInput.value,
    timestamp: new Date()
  }

  messages.value.push(userMessage)
  const question = chatInput.value
  chatInput.value = ''

  scrollToBottom()
  isTyping.value = true

  try {
    // Direct POST to n8n webhook
    const response = await fetch('https://n8n.jiosgroup.com/webhook/e529b3a4-d09d-45d6-8de5-01cc6885bcb7/chat', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        sessionId: chatId.value,
        chatInput: question
      })
    })

    if (!response.ok) {
      throw new Error(`Webhook error: ${response.status} ${response.statusText}`)
    }

    const data = await response.json()

    let aiResponse = data?.output || data?.text || data?.message || (data?.data && data.data.output) || null
    let questionId = data?.id || null

    if (!aiResponse) {
      aiResponse = "I couldn't find a specific answer to your question. Please contact support or try again later."
    }

    messages.value.push({
      sender: 'ai',
      text: aiResponse,
      timestamp: new Date(),
      questionId: questionId
    })

    isTyping.value = false
    scrollToBottom()
  } catch (error) {
    console.error('Chatbot error:', error)
    
    // Fallback to static responses if API fails
    const aiResponse = getAIResponseFallback(question)
    messages.value.push({
      sender: 'ai',
      text: aiResponse,
      timestamp: new Date()
    })
    
    isTyping.value = false
    scrollToBottom()
  }
}

const submitFeedback = (message, isHelpful) => {
  // Just mark as given locally
  message.feedbackGiven = true
}

// Fallback responses when API is unavailable
const getAIResponseFallback = (question) => {
  const lowerQuestion = question.toLowerCase()

  if (lowerQuestion.includes('how') && lowerQuestion.includes('work')) {
    return "NFC business cards work through Near Field Communication technology. Simply tap your card on any NFC-enabled smartphone, and your digital profile opens instantly - no app needed! The recipient can view your contact info, social media, portfolio, and save everything with one tap."
  } else if (lowerQuestion.includes('pricing') || lowerQuestion.includes('price') || lowerQuestion.includes('cost')) {
    return "We offer 4 plans:\n\n• Free: 1 profile, basic features\n• Basic: 3 profiles, premium templates (contact for pricing)\n• Premium: 10 profiles, team management (contact for pricing)\n• Business: Unlimited profiles, white label (contact for pricing)\n\nAll paid plans include a 14-day free trial with no credit card required!"
  } else if (lowerQuestion.includes('custom')) {
    return "Yes! You can fully customize your digital profile with:\n\n• Your photo and branding\n• Custom colors and themes\n• Links to social media, website, portfolio\n• Contact forms and calendars\n• Video introductions\n• Product galleries\n\nUpdate anytime without reprinting cards!"
  } else if (lowerQuestion.includes('card') && (lowerQuestion.includes('material') || lowerQuestion.includes('type'))) {
    return "We offer premium card materials:\n\n• PVC Plastic - Durable, affordable, vibrant colors\n• Metal - Premium feel, ultra-durable, modern design\n• Bamboo - Eco-friendly, sustainable, unique texture\n\nAll cards include embedded NFC chips for instant sharing!"
  } else if (lowerQuestion.includes('analytics') || lowerQuestion.includes('track')) {
    return "Yes! Our analytics dashboard shows:\n\n• Total taps and views\n• Geographic locations\n• Time and date of interactions\n• Link clicks breakdown\n• Contact saves\n\nPerfect for measuring networking ROI!"
  } else if (lowerQuestion.includes('team') || lowerQuestion.includes('business') || lowerQuestion.includes('company')) {
    return "Absolutely! Our Business plan includes:\n\n• Centralized team management\n• Brand consistency controls\n• Bulk profile creation\n• Advanced analytics dashboard\n• API access\n• White label options\n\nPerfect for sales teams and enterprises!"
  } else {
    return "Thanks for your question! NFCGo makes professional networking effortless with NFC technology. You can:\n\n• Share contact info instantly\n• Showcase portfolios and social media\n• Track engagement analytics\n• Update profiles anytime\n• Manage team profiles\n\nWould you like to know more about our features, pricing, or how NFC technology works?"
  }
}
</script>
