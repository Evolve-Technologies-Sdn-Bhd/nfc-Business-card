<template>
  <Teleport to="body">
  <div class="chatbot-widget" :class="{ 'chatbot-mobile': isMobile }">
    <!-- Floating Chat Button -->
    <button
      v-if="!isOpen"
      @click="toggleChat"
      class="chatbot-float-button"
      aria-label="Open chatbot"
    >
      <Icon name="heroicons:chat-bubble-left-right" class="h-7 w-7" />
      <span v-if="unreadCount > 0" class="chatbot-badge">{{ unreadCount }}</span>
    </button>

    <!-- Chat Window -->
    <div v-if="isOpen" class="chatbot-window">
      <!-- Header -->
      <div class="chatbot-header">
        <div class="flex items-center space-x-3">
          <div class="chatbot-avatar">
            <Icon name="heroicons:sparkles" class="h-5 w-5 text-white" />
          </div>
          <div>
            <h3 class="chatbot-title">NFC Card Assistant</h3>
            <p class="chatbot-status">
              <span class="chatbot-status-dot"></span>
              Online
            </p>
          </div>
        </div>
        <button @click="toggleChat" class="chatbot-close-btn" aria-label="Close chat">
          <Icon name="heroicons:x-mark" class="h-5 w-5" />
        </button>
      </div>

      <!-- Messages Area -->
      <div ref="messagesContainer" class="chatbot-messages">
        <!-- Welcome Message -->
        <div v-if="messages.length === 0" class="chatbot-welcome">
          <Icon name="heroicons:hand-raised" class="h-12 w-12 text-primary-500 mb-3" />
          <h4 class="text-lg font-semibold text-gray-900 mb-2">Welcome! 👋</h4>
          <p class="text-sm text-gray-600 mb-4">
            Ask me anything about our NFC business cards, features, or pricing!
          </p>
          
          <!-- Quick Questions -->
          <div class="space-y-2">
            <p class="text-xs font-medium text-gray-500 mb-2">Quick Questions:</p>
            <button
              v-for="(quick, index) in quickQuestions"
              :key="index"
              @click="sendQuickQuestion(quick)"
              class="chatbot-quick-btn"
            >
              {{ quick }}
            </button>
          </div>
        </div>

        <!-- Message List -->
        <div
          v-for="(message, index) in messages"
          :key="index"
          :class="[
            'chatbot-message',
            message.type === 'user' ? 'chatbot-message-user' : 'chatbot-message-bot'
          ]"
        >
          <div v-if="message.type === 'bot'" class="chatbot-message-avatar">
            <Icon name="heroicons:sparkles" class="h-4 w-4 text-white" />
          </div>
          
          <div class="chatbot-message-bubble">
            <p class="chatbot-message-text" v-html="formatMessage(message.text)"></p>
            
            <!-- Feedback Buttons (only for bot messages with answer) -->
            <div
              v-if="message.type === 'bot' && message.questionId && !message.feedbackGiven"
              class="chatbot-feedback-btns"
            >
              <button
                @click="submitFeedback(message, 5, index)"
                class="chatbot-feedback-btn chatbot-feedback-helpful"
                title="Helpful"
              >
                <Icon name="heroicons:hand-thumb-up" class="h-4 w-4" />
              </button>
              <button
                @click="submitFeedback(message, 1, index)"
                class="chatbot-feedback-btn chatbot-feedback-not-helpful"
                title="Not helpful"
              >
                <Icon name="heroicons:hand-thumb-down" class="h-4 w-4" />
              </button>
            </div>

            <!-- Feedback Thank You -->
            <div v-if="message.feedbackGiven" class="chatbot-feedback-thanks">
              <Icon name="heroicons:check-circle" class="h-4 w-4 text-green-600" />
              <span class="text-xs text-green-600">Thanks for your feedback!</span>
            </div>
          </div>

          <div v-if="message.type === 'user'" class="chatbot-message-avatar-user">
            <Icon name="heroicons:user" class="h-4 w-4 text-white" />
          </div>
        </div>

        <!-- Typing Indicator -->
        <div v-if="isTyping" class="chatbot-message chatbot-message-bot">
          <div class="chatbot-message-avatar">
            <Icon name="heroicons:sparkles" class="h-4 w-4 text-white" />
          </div>
          <div class="chatbot-message-bubble">
            <div class="chatbot-typing">
              <span></span>
              <span></span>
              <span></span>
            </div>
          </div>
        </div>

        <!-- Feedback Form (shown when no answer found) -->
        <div v-if="showFeedbackForm" class="chatbot-feedback-form">
          <p class="text-sm font-medium text-gray-700 mb-3">
            📝 Help us improve! Leave your contact info and we'll get back to you:
          </p>
          <input
            v-model="feedbackData.name"
            type="text"
            placeholder="Your name (optional)"
            class="chatbot-input-small mb-2"
          />
          <input
            v-model="feedbackData.email"
            type="email"
            placeholder="Your email (optional)"
            class="chatbot-input-small mb-2"
          />
          <textarea
            v-model="feedbackData.message"
            placeholder="Additional details..."
            rows="2"
            class="chatbot-input-small mb-3"
          ></textarea>
          <div class="flex space-x-2">
            <button @click="submitDetailedFeedback" class="chatbot-btn-primary">
              Send Feedback
            </button>
            <button @click="closeFeedbackForm" class="chatbot-btn-secondary">
              No Thanks
            </button>
          </div>
        </div>
      </div>

      <!-- Input Area -->
      <div class="chatbot-input-area">
        <input
          v-model="userInput"
          @keypress.enter="sendMessage"
          type="text"
          placeholder="Type your message..."
          class="chatbot-input"
          :disabled="isTyping"
        />
        <button
          @click="sendMessage"
          :disabled="!userInput.trim() || isTyping"
          class="chatbot-send-btn"
          aria-label="Send message"
        >
          <Icon name="heroicons:paper-airplane" class="h-5 w-5" />
        </button>
      </div>
    </div>
  </div>
  </Teleport>
</template>

<script setup>
import { ref, nextTick, onMounted, computed } from 'vue';

// State
const isOpen = ref(false);
const userInput = ref('');
const messages = ref([]);
const isTyping = ref(false);
const unreadCount = ref(0);
const showFeedbackForm = ref(false);
const lastUserQuestion = ref('');
const messagesContainer = ref(null);

const feedbackData = ref({
  name: '',
  email: '',
  message: ''
});

const quickQuestions = [
  'What is an NFC business card?',
  'How do I create my digital card?',
  'What are the pricing plans?',
  'How do I share my card?',
];

const isMobile = computed(() => {
  if (process.client) {
    return window.innerWidth < 768;
  }
  return false;
});

// Toggle chat window
const toggleChat = () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    unreadCount.value = 0;
    nextTick(() => {
      scrollToBottom();
    });
  }
};

// Send message
const sendMessage = async () => {
  if (!userInput.value.trim() || isTyping.value) return;

  const question = userInput.value.trim();
  lastUserQuestion.value = question;

  // Add user message
  messages.value.push({
    type: 'user',
    text: question,
    timestamp: new Date()
  });

  userInput.value = '';
  isTyping.value = true;
  scrollToBottom();

  try {
    // Call n8n webhook directly
    const response = await $fetch('https://n8n.jiosgroup.com/webhook/e529b3a4-d09d-45d6-8de5-01cc6885bcb7/chat', {
      method: 'POST',
      body: { 
        question,
        chatId: Date.now() // Simple session identifier
      }
    });

    // Simulate typing delay
    await new Promise(resolve => setTimeout(resolve, 800));

    // Handle n8n response
    const answer = response.answer || response.message || response.data?.answer;
    
    if (answer) {
      // Add bot response
      messages.value.push({
        type: 'bot',
        text: answer,
        feedbackGiven: false,
        timestamp: new Date()
      });
    } else {
      // No answer found
      messages.value.push({
        type: 'bot',
        text: "I'm sorry, I don't have an answer to that question yet. Would you like to leave your contact details so our team can help you?",
        timestamp: new Date()
      });
      showFeedbackForm.value = true;
    }
  } catch (error) {
    console.error('Chatbot error:', error);
    messages.value.push({
      type: 'bot',
      text: "Sorry, I'm having trouble connecting right now. Please try again later or contact our support team.",
      timestamp: new Date()
    });
  } finally {
    isTyping.value = false;
    scrollToBottom();
  }
};

// Send quick question
const sendQuickQuestion = (question) => {
  userInput.value = question;
  sendMessage();
};

// Submit detailed feedback - now just shows confirmation without backend call
const submitDetailedFeedback = async () => {
  messages.value.push({
    type: 'bot',
    text: "Thank you! We've received your message. 🙏",
    timestamp: new Date()
  });

  closeFeedbackForm();
  scrollToBottom();
};

// Close feedback form
const closeFeedbackForm = () => {
  showFeedbackForm.value = false;
  feedbackData.value = { name: '', email: '', message: '' };
};

// Format message text (convert URLs to links, line breaks, etc.)
const formatMessage = (text) => {
  if (!text) return '';
  
  // Convert URLs to clickable links
  let formatted = text.replace(
    /(https?:\/\/[^\s]+)/g,
    '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-primary-600 hover:underline">$1</a>'
  );
  
  // Convert line breaks
  formatted = formatted.replace(/\n/g, '<br>');
  
  return formatted;
};

// Scroll to bottom of messages
const scrollToBottom = () => {
  nextTick(() => {
    if (messagesContainer.value) {
      messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
  });
};

// Initialize
onMounted(() => {
  // Optional: Show notification badge if there are updates
});
</script>

<style>
/* Widget Container */
.chatbot-widget {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 9999;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.chatbot-mobile {
  bottom: 10px;
  right: 10px;
}

/* Floating Button */
.chatbot-float-button {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s, box-shadow 0.2s;
  position: relative;
}

.chatbot-float-button:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 16px rgba(102, 126, 234, 0.5);
}

.chatbot-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #ef4444;
  color: white;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: bold;
}

/* Chat Window */
.chatbot-window {
  width: 380px;
  height: 600px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: slideUp 0.3s ease-out;
}

@media (max-width: 768px) {
  .chatbot-window {
    position: fixed;
    bottom: 0;
    right: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    border-radius: 0;
  }
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
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.chatbot-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
}

.chatbot-title {
  font-size: 16px;
  font-weight: 600;
  margin: 0;
}

.chatbot-status {
  font-size: 12px;
  opacity: 0.9;
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 2px;
}

.chatbot-status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #10b981;
  display: inline-block;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.chatbot-close-btn {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: background 0.2s;
}

.chatbot-close-btn:hover {
  background: rgba(255, 255, 255, 0.1);
}

/* Messages Area */
.chatbot-messages {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  background: #f9fafb;
}

.chatbot-messages::-webkit-scrollbar {
  width: 6px;
}

.chatbot-messages::-webkit-scrollbar-track {
  background: transparent;
}

.chatbot-messages::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

/* Welcome Message */
.chatbot-welcome {
  text-align: center;
  padding: 40px 20px;
}

.chatbot-quick-btn {
  display: block;
  width: 100%;
  text-align: left;
  padding: 10px 12px;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 13px;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s;
}

.chatbot-quick-btn:hover {
  background: #f3f4f6;
  border-color: #667eea;
  color: #667eea;
}

/* Messages */
.chatbot-message {
  display: flex;
  align-items: flex-start;
  margin-bottom: 16px;
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.chatbot-message-bot {
  flex-direction: row;
}

.chatbot-message-user {
  flex-direction: row-reverse;
}

.chatbot-message-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-right: 8px;
}

.chatbot-message-avatar-user {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #6b7280;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-left: 8px;
}

.chatbot-message-bubble {
  max-width: 75%;
  padding: 12px 16px;
  border-radius: 12px;
  position: relative;
}

.chatbot-message-bot .chatbot-message-bubble {
  background: white;
  border: 1px solid #e5e7eb;
  border-bottom-left-radius: 4px;
}

.chatbot-message-user .chatbot-message-bubble {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-bottom-right-radius: 4px;
}

.chatbot-message-text {
  font-size: 14px;
  line-height: 1.5;
  margin: 0;
  word-wrap: break-word;
}

/* Feedback Buttons */
.chatbot-feedback-btns {
  display: flex;
  gap: 8px;
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px solid #f3f4f6;
}

.chatbot-feedback-btn {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  padding: 6px 10px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: #6b7280;
}

.chatbot-feedback-btn:hover {
  background: #f3f4f6;
}

.chatbot-feedback-helpful:hover {
  border-color: #10b981;
  color: #10b981;
}

.chatbot-feedback-not-helpful:hover {
  border-color: #ef4444;
  color: #ef4444;
}

.chatbot-feedback-thanks {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px solid #f3f4f6;
}

/* Typing Indicator */
.chatbot-typing {
  display: flex;
  gap: 4px;
  padding: 8px 0;
}

.chatbot-typing span {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #9ca3af;
  animation: bounce 1.4s infinite ease-in-out both;
}

.chatbot-typing span:nth-child(1) {
  animation-delay: -0.32s;
}

.chatbot-typing span:nth-child(2) {
  animation-delay: -0.16s;
}

@keyframes bounce {
  0%, 80%, 100% {
    transform: scale(0);
  }
  40% {
    transform: scale(1);
  }
}

/* Feedback Form */
.chatbot-feedback-form {
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 16px;
  margin-top: 12px;
}

.chatbot-input-small {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 13px;
  transition: border-color 0.2s;
}

.chatbot-input-small:focus {
  outline: none;
  border-color: #667eea;
}

.chatbot-btn-primary {
  flex: 1;
  padding: 8px 16px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: opacity 0.2s;
}

.chatbot-btn-primary:hover {
  opacity: 0.9;
}

.chatbot-btn-secondary {
  flex: 1;
  padding: 8px 16px;
  background: white;
  color: #6b7280;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.chatbot-btn-secondary:hover {
  background: #f9fafb;
}

/* Input Area */
.chatbot-input-area {
  padding: 16px 20px;
  background: white;
  border-top: 1px solid #e5e7eb;
  display: flex;
  gap: 8px;
}

.chatbot-input {
  flex: 1;
  padding: 10px 14px;
  border: 1px solid #e5e7eb;
  border-radius: 24px;
  font-size: 14px;
  transition: border-color 0.2s;
}

.chatbot-input:focus {
  outline: none;
  border-color: #667eea;
}

.chatbot-input:disabled {
  background: #f9fafb;
  cursor: not-allowed;
}

.chatbot-send-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: opacity 0.2s;
  flex-shrink: 0;
}

.chatbot-send-btn:hover:not(:disabled) {
  opacity: 0.9;
}

.chatbot-send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
