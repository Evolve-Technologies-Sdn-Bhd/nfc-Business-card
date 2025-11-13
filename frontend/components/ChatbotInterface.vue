<!-- components/ChatbotInterface.vue -->
<template>
  <Teleport to="body">
    <div 
      v-if="isOpen"
      class="chatbot-interface" 
      role="dialog" 
      aria-labelledby="chatbot-title" 
      aria-modal="true"
    >
      <!-- Header -->
      <div class="chatbot-header">
        <div class="header-content">
          <div class="bot-avatar">
            <Icon name="heroicons:chat-bubble-left-ellipsis" class="h-6 w-6" />
          </div>
          <div>
            <h3 id="chatbot-title" class="header-title">AI Assistant</h3>
            <p class="header-subtitle">Always here to help</p>
          </div>
        </div>
        <button 
          class="close-button" 
          @click="closeChat"
          aria-label="Close chat"
        >
          <Icon name="heroicons:x-mark" class="h-6 w-6" />
        </button>
      </div>

      <!-- Messages Container -->
      <div ref="messagesContainer" class="messages-container">
        <!-- Welcome Message -->
        <div v-if="messages.length === 0" class="welcome-section">
          <div class="welcome-icon">
            <Icon name="heroicons:sparkles" class="h-12 w-12" />
          </div>
          <h4 class="welcome-title">Hi there! 👋</h4>
          <p class="welcome-text">I'm your AI assistant. Ask me anything about our services!</p>
          
          <!-- Quick Questions -->
          <div v-if="quickQuestions.length > 0" class="quick-questions">
            <p class="quick-questions-title">Quick questions:</p>
            <button
              v-for="(question, index) in quickQuestions"
              :key="index"
              @click="askQuickQuestion(question)"
              class="quick-question-btn"
            >
              {{ question }}
            </button>
          </div>
        </div>

        <!-- Messages -->
        <div
          v-for="(message, index) in messages"
          :key="index"
          :class="['message', message.type]"
        >
          <div class="message-bubble">
            <div class="message-content">{{ message.text }}</div>
            <div class="message-time">{{ formatTime(message.timestamp) }}</div>
            
            <!-- Feedback Buttons for AI responses -->
            <div v-if="message.type === 'ai' && message.questionId && !message.feedbackGiven" class="feedback-buttons">
              <p class="feedback-prompt">Was this helpful?</p>
              <div class="feedback-actions">
                <button 
                  @click="giveFeedback(message, true)"
                  class="feedback-btn helpful"
                  title="Yes, helpful"
                >
                  <Icon name="heroicons:hand-thumb-up" class="h-4 w-4" />
                  Yes
                </button>
                <button 
                  @click="giveFeedback(message, false)"
                  class="feedback-btn not-helpful"
                  title="No, not helpful"
                >
                  <Icon name="heroicons:hand-thumb-down" class="h-4 w-4" />
                  No
                </button>
              </div>
            </div>

            <!-- Feedback Form (shown after clicking "No") -->
            <div v-if="message.showFeedbackForm" class="feedback-form">
              <p class="feedback-form-title">Help us improve</p>
              
              <!-- Star Rating -->
              <div class="star-rating">
                <button
                  v-for="star in 5"
                  :key="star"
                  @click="message.rating = star"
                  class="star-btn"
                  :class="{ active: star <= (message.rating || 0) }"
                  :aria-label="`Rate ${star} stars`"
                >
                  <Icon 
                    :name="star <= (message.rating || 0) ? 'heroicons:star-solid' : 'heroicons:star'" 
                    class="h-5 w-5" 
                  />
                </button>
              </div>

              <!-- Feedback Text -->
              <textarea
                v-model="message.feedbackText"
                placeholder="Tell us what went wrong..."
                class="feedback-textarea"
                rows="3"
              ></textarea>

              <!-- Email (optional) -->
              <input
                v-model="message.feedbackEmail"
                type="email"
                placeholder="Your email (optional)"
                class="feedback-input"
              />

              <!-- Submit Button -->
              <button
                @click="submitDetailedFeedback(message)"
                class="feedback-submit-btn"
                :disabled="!message.rating"
              >
                <Icon name="heroicons:paper-airplane" class="h-4 w-4 mr-2" />
                Submit Feedback
              </button>
            </div>

            <!-- Feedback Thank You -->
            <div v-if="message.feedbackSubmitted" class="feedback-thanks">
              <Icon name="heroicons:check-circle" class="h-5 w-5 text-green-600 mr-2" />
              <span>Thank you for your feedback!</span>
            </div>
          </div>
        </div>

        <!-- Typing Indicator -->
        <div v-if="isTyping" class="message ai">
          <div class="message-bubble">
            <div class="typing-indicator">
              <span></span>
              <span></span>
              <span></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Input Area -->
      <div class="input-container">
        <input
          ref="messageInput"
          v-model="currentMessage"
          type="text"
          placeholder="Type your question..."
          class="message-input"
          @keydown.enter="sendMessage"
          :disabled="isTyping"
        />
        <button
          @click="sendMessage"
          class="send-button"
          :disabled="!currentMessage.trim() || isTyping"
          aria-label="Send message"
        >
          <Icon name="heroicons:paper-airplane" class="h-5 w-5" />
        </button>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch, nextTick, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'ask', 'submit-feedback'])

// State
const messages = ref([])
const currentMessage = ref('')
const isTyping = ref(false)
const messagesContainer = ref(null)
const messageInput = ref(null)

// Quick Questions
const quickQuestions = ref([
  'What is an NFC business card?',
  'How do I customize my profile?',
  'What are your pricing plans?',
  'How do I share my card?'
])

// Keyboard accessibility - Close on Esc key
const handleEscKey = (event) => {
  if (event.key === 'Escape' && props.isOpen) {
    closeChat()
  }
}

// Add/remove event listener
onMounted(() => {
  document.addEventListener('keydown', handleEscKey)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscKey)
})

// Methods
const closeChat = () => {
  emit('close')
}

const formatTime = (timestamp) => {
  if (!timestamp) return 'Just now'
  const now = new Date()
  const messageTime = new Date(timestamp)
  const diffMs = now - messageTime
  const diffMins = Math.floor(diffMs / 60000)
  
  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins}m ago`
  const diffHours = Math.floor(diffMins / 60)
  if (diffHours < 24) return `${diffHours}h ago`
  return messageTime.toLocaleDateString()
}

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

const askQuickQuestion = (question) => {
  currentMessage.value = question
  sendMessage()
}

const sendMessage = async () => {
  const message = currentMessage.value.trim()
  if (!message || isTyping.value) return

  // Add user message
  messages.value.push({
    type: 'user',
    text: message,
    timestamp: new Date()
  })

  const userQuestion = message
  currentMessage.value = ''
  scrollToBottom()
  isTyping.value = true

  // Emit ask event to parent
  emit('ask', userQuestion, (response) => {
    isTyping.value = false
    
    if (response.success && response.answer) {
      // Add AI response
      messages.value.push({
        type: 'ai',
        text: response.answer,
        timestamp: new Date(),
        questionId: response.questionId,
        userQuestion: userQuestion,
        feedbackGiven: false,
        showFeedbackForm: false,
        feedbackSubmitted: false,
        rating: 0,
        feedbackText: '',
        feedbackEmail: ''
      })
    } else {
      // No answer found
      messages.value.push({
        type: 'ai',
        text: "I'm sorry, I don't have an answer to that question yet. Our team will review your question and update the knowledge base soon!",
        timestamp: new Date(),
        userQuestion: userQuestion,
        feedbackGiven: false
      })
    }
    
    scrollToBottom()
  })
}

const giveFeedback = (message, isHelpful) => {
  message.feedbackGiven = true
  
  if (isHelpful) {
    // Submit helpful feedback immediately
    emit('submit-feedback', {
      questionId: message.questionId,
      userQuestion: message.userQuestion,
      rating: 5,
      feedbackType: 'helpful'
    }, () => {
      message.feedbackSubmitted = true
      scrollToBottom()
    })
  } else {
    // Show detailed feedback form for not helpful
    message.showFeedbackForm = true
    message.rating = 1
    scrollToBottom()
  }
}

const submitDetailedFeedback = (message) => {
  if (!message.rating) return

  // Submit detailed feedback
  emit('submit-feedback', {
    questionId: message.questionId,
    userQuestion: message.userQuestion,
    rating: message.rating,
    userMessage: message.feedbackText,
    userEmail: message.feedbackEmail,
    feedbackType: 'not-helpful'
  }, () => {
    message.showFeedbackForm = false
    message.feedbackSubmitted = true
    scrollToBottom()
  })
}

// Watch for panel open/close
watch(() => props.isOpen, (newValue) => {
  if (newValue) {
    nextTick(() => {
      messageInput.value?.focus()
      scrollToBottom()
    })
  }
})

// Escape key listener
if (typeof window !== 'undefined') {
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && props.isOpen) {
      closeChat()
    }
  })
}
</script>

<style scoped>
.chatbot-interface {
  position: fixed;
  bottom: 90px;
  right: 20px;
  width: 320px;
  max-height: 480px;
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  z-index: 99998 !important;
  animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Header */
.chatbot-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 0.75rem 0.75rem 0 0;
  color: white;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.bot-avatar {
  width: 2rem;
  height: 2rem;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bot-avatar .h-6 {
  width: 1.25rem;
  height: 1.25rem;
}

.header-title {
  font-size: 0.875rem;
  font-weight: 600;
  margin: 0;
}

.header-subtitle {
  font-size: 0.625rem;
  opacity: 0.9;
  margin: 0;
}

.close-button {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  width: 1.75rem;
  height: 1.75rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.close-button:hover {
  background: rgba(255, 255, 255, 0.3);
}

.close-button:focus {
  outline: 2px solid white;
  outline-offset: 2px;
}

/* Messages Container */
.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 0.875rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  max-height: 350px;
}

/* Welcome Section */
.welcome-section {
  text-align: center;
  padding: 0.5rem 0;
}

.welcome-icon {
  width: 3rem;
  height: 3rem;
  margin: 0 auto 0.75rem;
  background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #667eea;
}

.welcome-icon .h-12 {
  width: 2rem;
  height: 2rem;
}

.welcome-title {
  font-size: 1rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.welcome-text {
  color: #6b7280;
  font-size: 0.8125rem;
  margin-bottom: 1rem;
}

.quick-questions {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
  align-items: stretch;
}

.quick-questions-title {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #4b5563;
  text-align: left;
  margin-bottom: 0.375rem;
}

.quick-question-btn {
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  padding: 0.5rem 0.75rem;
  font-size: 0.8125rem;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s;
  text-align: left;
}

.quick-question-btn:hover {
  background: #e5e7eb;
  border-color: #667eea;
}

/* Messages */
.message {
  display: flex;
  margin-bottom: 0.75rem;
}

.message.user {
  justify-content: flex-end;
}

.message.ai {
  justify-content: flex-start;
}

.message-bubble {
  max-width: 75%;
  padding: 0.875rem 1rem;
  border-radius: 1rem;
  font-size: 0.875rem;
  line-height: 1.5;
}

.message.user .message-bubble {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-bottom-right-radius: 0.25rem;
}

.message.ai .message-bubble {
  background: #f3f4f6;
  color: #1f2937;
  border-bottom-left-radius: 0.25rem;
}

.message-content {
  margin-bottom: 0.25rem;
}

.message-time {
  font-size: 0.625rem;
  opacity: 0.7;
}

/* Feedback */
.feedback-buttons {
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid #e5e7eb;
}

.feedback-prompt {
  font-size: 0.75rem;
  font-weight: 600;
  color: #6b7280;
  margin-bottom: 0.5rem;
}

.feedback-actions {
  display: flex;
  gap: 0.5rem;
}

.feedback-btn {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.375rem 0.75rem;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  border: 1px solid;
}

.feedback-btn.helpful {
  background: white;
  border-color: #10b981;
  color: #10b981;
}

.feedback-btn.helpful:hover {
  background: #10b981;
  color: white;
}

.feedback-btn.not-helpful {
  background: white;
  border-color: #ef4444;
  color: #ef4444;
}

.feedback-btn.not-helpful:hover {
  background: #ef4444;
  color: white;
}

/* Feedback Form */
.feedback-form {
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid #e5e7eb;
}

.feedback-form-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.75rem;
}

.star-rating {
  display: flex;
  gap: 0.25rem;
  margin-bottom: 0.75rem;
}

.star-btn {
  background: none;
  border: none;
  color: #d1d5db;
  cursor: pointer;
  padding: 0;
  transition: color 0.2s;
}

.star-btn.active {
  color: #fbbf24;
}

.star-btn:hover {
  color: #fbbf24;
}

.feedback-textarea {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  resize: none;
  font-family: inherit;
}

.feedback-textarea:focus {
  outline: none;
  border-color: #667eea;
}

.feedback-input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.feedback-input:focus {
  outline: none;
  border-color: #667eea;
}

.feedback-submit-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 0.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.feedback-submit-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.feedback-submit-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.feedback-thanks {
  display: flex;
  align-items: center;
  margin-top: 0.75rem;
  padding: 0.5rem;
  background: #d1fae5;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  color: #065f46;
}

/* Typing Indicator */
.typing-indicator {
  display: flex;
  gap: 0.25rem;
  padding: 0.5rem 0;
}

.typing-indicator span {
  width: 0.5rem;
  height: 0.5rem;
  background: #9ca3af;
  border-radius: 50%;
  animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) {
  animation-delay: 0.2s;
}

.typing-indicator span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes typing {
  0%, 60%, 100% {
    transform: translateY(0);
    opacity: 0.7;
  }
  30% {
    transform: translateY(-10px);
    opacity: 1;
  }
}

/* Input Container */
.input-container {
  display: flex;
  gap: 0.5rem;
  padding: 0.75rem 0.875rem;
  border-top: 1px solid #e5e7eb;
}

.message-input {
  flex: 1;
  padding: 0.625rem 0.875rem;
  border: 1px solid #d1d5db;
  border-radius: 1.25rem;
  font-size: 0.8125rem;
  outline: none;
  transition: border-color 0.2s;
}

.message-input:focus {
  border-color: #667eea;
}

.message-input:disabled {
  background: #f9fafb;
  cursor: not-allowed;
}

.send-button {
  width: 2.25rem;
  height: 2.25rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 50%;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}

.send-button:hover:not(:disabled) {
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.send-button:focus {
  outline: 2px solid #667eea;
  outline-offset: 2px;
}

.send-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Responsive */
@media (max-width: 768px) {
  .chatbot-interface {
    width: calc(100vw - 2rem);
    right: 1rem;
    bottom: 4.5rem;
    max-height: 420px;
  }
  
  .messages-container {
    max-height: 280px;
    padding: 0.75rem;
  }
  
  .input-container {
    padding: 0.625rem 0.75rem;
  }
}
</style>
