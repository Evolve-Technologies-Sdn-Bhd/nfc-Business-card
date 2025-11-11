<template>
  <Teleport to="body">
    <Transition name="chat-modal">
      <div v-if="isOpen" class="chat-modal-overlay" @click.self="closeChat">
        <div class="chat-modal">
          <!-- Header -->
          <div class="chat-header">
            <div class="header-content">
              <div class="bot-avatar">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                  class="bot-icon"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"
                  />
                </svg>
              </div>
              <div class="header-text">
                <h3 class="bot-name">NFCGo Assistant</h3>
                <p class="bot-status">
                  <span class="status-dot"></span>
                  Online
                </p>
              </div>
            </div>
            <button @click="closeChat" class="minimize-btn" aria-label="Close chat">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="minimize-icon"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
              </svg>
            </button>
          </div>

          <!-- Messages Container -->
          <div ref="messagesContainer" class="messages-container">
            <!-- Welcome Message -->
            <div v-if="messages.length === 0" class="welcome-message">
              <div class="welcome-icon">👋</div>
              <h4>Hi there! How can I help you today?</h4>
              <p class="text-sm text-gray-500 mt-2">
                Ask me anything about our NFC business cards, pricing, or services!
              </p>
              
              <!-- Quick Questions -->
              <div class="quick-questions">
                <p class="quick-questions-title">Quick questions:</p>
                <button
                  v-for="question in quickQuestions"
                  :key="question"
                  @click="sendQuickQuestion(question)"
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
              class="message"
              :class="message.type"
            >
              <div v-if="message.type === 'bot'" class="message-avatar">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                  class="avatar-icon"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"
                  />
                </svg>
              </div>

              <div class="message-bubble" :class="message.type">
                <div class="message-content" v-html="formatMessage(message.text)"></div>
                <div class="message-time">{{ formatTime(message.timestamp) }}</div>

                <!-- Feedback Buttons (only for bot messages with answers) -->
                <div
                  v-if="message.type === 'bot' && message.showFeedback && !message.feedbackGiven"
                  class="feedback-buttons"
                >
                  <p class="feedback-question">Was this helpful?</p>
                  <div class="feedback-actions">
                    <button
                      @click="handleFeedback(index, 'helpful')"
                      class="feedback-btn helpful"
                    >
                      👍 Yes
                    </button>
                    <button
                      @click="handleFeedback(index, 'not-helpful')"
                      class="feedback-btn not-helpful"
                    >
                      👎 No
                    </button>
                  </div>
                </div>

                <!-- Feedback Form -->
                <div v-if="message.showFeedbackForm" class="feedback-form">
                  <p class="feedback-form-title">Help us improve:</p>
                  
                  <!-- Star Rating -->
                  <div class="star-rating">
                    <button
                      v-for="star in 5"
                      :key="star"
                      @click="setRating(index, star)"
                      class="star-btn"
                      :class="{ active: star <= (message.rating || 0) }"
                    >
                      ★
                    </button>
                  </div>

                  <!-- Feedback Text -->
                  <textarea
                    v-model="message.feedbackText"
                    placeholder="What could we improve? (optional)"
                    class="feedback-textarea"
                    rows="3"
                  ></textarea>

                  <!-- Email (optional) -->
                  <input
                    v-model="message.feedbackEmail"
                    type="email"
                    placeholder="Your email (optional)"
                    class="feedback-email"
                  />

                  <div class="feedback-form-actions">
                    <button @click="submitFeedback(index)" class="submit-feedback-btn">
                      Submit Feedback
                    </button>
                    <button @click="closeFeedbackForm(index)" class="cancel-feedback-btn">
                      Cancel
                    </button>
                  </div>
                </div>

                <!-- Thank You Message -->
                <div v-if="message.feedbackGiven" class="feedback-thanks">
                  ✓ Thank you for your feedback!
                </div>
              </div>
            </div>

            <!-- Typing Indicator -->
            <div v-if="isTyping" class="message bot">
              <div class="message-avatar">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                  class="avatar-icon"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"
                  />
                </svg>
              </div>
              <div class="message-bubble bot">
                <div class="typing-indicator">
                  <span></span>
                  <span></span>
                  <span></span>
                </div>
              </div>
            </div>
          </div>

          <!-- Input Area -->
          <div class="input-area">
            <form @submit.prevent="sendMessage" class="input-form">
              <input
                v-model="userInput"
                type="text"
                placeholder="Type your question..."
                class="message-input"
                :disabled="isTyping"
                @keyup.enter="sendMessage"
              />
              <button
                type="submit"
                class="send-btn"
                :disabled="!userInput.trim() || isTyping"
                aria-label="Send message"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                  class="send-icon"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"
                  />
                </svg>
              </button>
            </form>
            <p class="input-footer">Powered by NFCGo AI</p>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, nextTick, watch } from 'vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'ask', 'submitFeedback'])

const messagesContainer = ref(null)
const userInput = ref('')
const messages = ref([])
const isTyping = ref(false)

const quickQuestions = ref([
  'What services do you offer?',
  'How much does it cost?',
  'What is NFC technology?'
])

const closeChat = () => {
  emit('close')
}

const sendQuickQuestion = (question) => {
  userInput.value = question
  sendMessage()
}

const sendMessage = async () => {
  if (!userInput.value.trim() || isTyping.value) return

  const question = userInput.value.trim()
  
  // Add user message
  messages.value.push({
    type: 'user',
    text: question,
    timestamp: new Date()
  })

  userInput.value = ''
  scrollToBottom()

  // Show typing indicator
  isTyping.value = true

  // Emit to parent to get answer
  emit('ask', question, (response) => {
    isTyping.value = false

    if (response.success && response.answer) {
      // Found answer
      messages.value.push({
        type: 'bot',
        text: response.answer,
        timestamp: new Date(),
        showFeedback: true,
        feedbackGiven: false,
        questionId: response.questionId
      })
    } else {
      // No answer found
      messages.value.push({
        type: 'bot',
        text: "I'm sorry, I don't have an answer to that specific question. Would you like to leave feedback so our team can help you?",
        timestamp: new Date(),
        showFeedback: true,
        feedbackGiven: false,
        noAnswer: true
      })
    }

    scrollToBottom()
  })
}

const handleFeedback = (messageIndex, type) => {
  const message = messages.value[messageIndex]
  
  if (type === 'helpful') {
    message.feedbackGiven = true
    message.showFeedback = false
    
    // Emit helpful feedback
    if (message.questionId) {
      emit('submitFeedback', {
        questionId: message.questionId,
        feedbackType: 'helpful',
        rating: 5
      })
    }
  } else {
    // Show feedback form
    message.showFeedbackForm = true
    message.showFeedback = false
    message.rating = 0
    message.feedbackText = ''
    message.feedbackEmail = ''
  }

  scrollToBottom()
}

const setRating = (messageIndex, rating) => {
  messages.value[messageIndex].rating = rating
}

const submitFeedback = (messageIndex) => {
  const message = messages.value[messageIndex]
  
  const feedbackData = {
    questionId: message.questionId || null,
    userQuestion: messages.value[messageIndex - 1]?.text || '',
    feedbackType: message.noAnswer ? 'no-answer' : 'not-helpful',
    rating: message.rating || 1,
    userMessage: message.feedbackText || '',
    userEmail: message.feedbackEmail || ''
  }

  emit('submitFeedback', feedbackData, (success) => {
    if (success) {
      message.showFeedbackForm = false
      message.feedbackGiven = true
      scrollToBottom()
    }
  })
}

const closeFeedbackForm = (messageIndex) => {
  messages.value[messageIndex].showFeedbackForm = false
  messages.value[messageIndex].showFeedback = true
}

const formatMessage = (text) => {
  // Convert newlines to <br>
  return text.replace(/\n/g, '<br>')
}

const formatTime = (timestamp) => {
  const date = new Date(timestamp)
  return date.toLocaleTimeString('en-US', { 
    hour: 'numeric', 
    minute: '2-digit',
    hour12: true 
  })
}

const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
    }
  })
}

// Watch for chat opening
watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    nextTick(() => {
      scrollToBottom()
    })
  }
})
</script>

<style scoped>
.chat-modal-overlay {
  position: fixed;
  bottom: 6rem;
  right: 2rem;
  z-index: 9998;
}

.chat-modal {
  width: 350px;
  height: 500px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Header */
.chat-header {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 1.25rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.bot-avatar {
  width: 40px;
  height: 40px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bot-icon {
  width: 24px;
  height: 24px;
}

.header-text {
  display: flex;
  flex-direction: column;
}

.bot-name {
  font-size: 1rem;
  font-weight: 600;
  margin: 0;
}

.bot-status {
  font-size: 0.75rem;
  display: flex;
  align-items: center;
  gap: 0.375rem;
  margin: 0.25rem 0 0 0;
  opacity: 0.9;
}

.status-dot {
  width: 8px;
  height: 8px;
  background: #10b981;
  border-radius: 50%;
  animation: pulse-dot 2s infinite;
}

@keyframes pulse-dot {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.minimize-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 8px;
  padding: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.minimize-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

.minimize-icon {
  width: 20px;
  height: 20px;
  color: white;
}

/* Messages Container */
.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
  background: #f9fafb;
}

/* Welcome Message */
.welcome-message {
  text-align: center;
  padding: 2rem 1rem;
}

.welcome-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.welcome-message h4 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 0.5rem 0;
}

.quick-questions {
  margin-top: 1.5rem;
  text-align: left;
}

.quick-questions-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #6b7280;
  margin-bottom: 0.75rem;
}

.quick-question-btn {
  display: block;
  width: 100%;
  text-align: left;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 0.75rem 1rem;
  margin-bottom: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.875rem;
  color: #4b5563;
}

.quick-question-btn:hover {
  background: #f3f4f6;
  border-color: #667eea;
  color: #667eea;
  transform: translateX(4px);
}

/* Messages */
.message {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.message.user {
  flex-direction: row-reverse;
}

.message-avatar {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.avatar-icon {
  width: 18px;
  height: 18px;
  color: white;
}

.message-bubble {
  max-width: 75%;
  border-radius: 12px;
  padding: 0.875rem 1rem;
  word-wrap: break-word;
}

.message-bubble.bot {
  background: white;
  color: #1f2937;
  border: 1px solid #e5e7eb;
}

.message-bubble.user {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.message-content {
  font-size: 0.9375rem;
  line-height: 1.5;
}

.message-time {
  font-size: 0.6875rem;
  opacity: 0.6;
  margin-top: 0.5rem;
}

/* Feedback */
.feedback-buttons {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.feedback-question {
  font-size: 0.8125rem;
  color: #6b7280;
  margin-bottom: 0.5rem;
}

.feedback-actions {
  display: flex;
  gap: 0.5rem;
}

.feedback-btn {
  flex: 1;
  padding: 0.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  background: white;
  cursor: pointer;
  font-size: 0.8125rem;
  transition: all 0.2s;
}

.feedback-btn:hover {
  background: #f3f4f6;
}

.feedback-btn.helpful:hover {
  border-color: #10b981;
  color: #10b981;
}

.feedback-btn.not-helpful:hover {
  border-color: #ef4444;
  color: #ef4444;
}

/* Feedback Form */
.feedback-form {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.feedback-form-title {
  font-size: 0.8125rem;
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
  font-size: 1.5rem;
  color: #d1d5db;
  cursor: pointer;
  transition: all 0.2s;
  padding: 0;
}

.star-btn:hover,
.star-btn.active {
  color: #fbbf24;
  transform: scale(1.1);
}

.feedback-textarea {
  width: 100%;
  padding: 0.625rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
  font-family: inherit;
  resize: none;
}

.feedback-email {
  width: 100%;
  padding: 0.625rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 0.875rem;
  margin-bottom: 0.75rem;
}

.feedback-form-actions {
  display: flex;
  gap: 0.5rem;
}

.submit-feedback-btn {
  flex: 1;
  padding: 0.625rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 0.8125rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.submit-feedback-btn:hover {
  opacity: 0.9;
  transform: translateY(-1px);
}

.cancel-feedback-btn {
  padding: 0.625rem 1rem;
  background: white;
  color: #6b7280;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 0.8125rem;
  cursor: pointer;
  transition: all 0.2s;
}

.cancel-feedback-btn:hover {
  background: #f3f4f6;
}

.feedback-thanks {
  margin-top: 1rem;
  padding: 0.75rem;
  background: #d1fae5;
  color: #065f46;
  border-radius: 6px;
  font-size: 0.8125rem;
  text-align: center;
}

/* Typing Indicator */
.typing-indicator {
  display: flex;
  gap: 0.25rem;
  padding: 0.25rem 0;
}

.typing-indicator span {
  width: 8px;
  height: 8px;
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
    opacity: 0.3;
    transform: translateY(0);
  }
  30% {
    opacity: 1;
    transform: translateY(-8px);
  }
}

/* Input Area */
.input-area {
  background: white;
  border-top: 1px solid #e5e7eb;
  padding: 1rem 1.5rem;
}

.input-form {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
}

.message-input {
  flex: 1;
  padding: 0.75rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 24px;
  font-size: 0.9375rem;
  outline: none;
  transition: all 0.2s;
}

.message-input:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.send-btn {
  width: 42px;
  height: 42px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.send-btn:hover:not(:disabled) {
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.send-icon {
  width: 20px;
  height: 20px;
  color: white;
}

.input-footer {
  text-align: center;
  font-size: 0.6875rem;
  color: #9ca3af;
  margin: 0;
}

/* Transitions */
.chat-modal-enter-active {
  animation: slide-up 0.3s ease-out;
}

.chat-modal-leave-active {
  animation: slide-down 0.2s ease-in;
}

@keyframes slide-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slide-down {
  from {
    opacity: 1;
    transform: translateY(0);
  }
  to {
    opacity: 0;
    transform: translateY(20px);
  }
}

/* Responsive */
@media (max-width: 768px) {
  .chat-modal-overlay {
    bottom: 5rem;
    right: 1rem;
    left: 1rem;
  }

  .chat-modal {
    width: 100%;
    height: 450px;
  }

  .message-bubble {
    max-width: 85%;
  }
}

/* Scrollbar */
.messages-container::-webkit-scrollbar {
  width: 6px;
}

.messages-container::-webkit-scrollbar-track {
  background: #f3f4f6;
}

.messages-container::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

.messages-container::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>
