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
        <span v-if="unreadCount > 0" class="chatbot-badge">{{
          unreadCount
        }}</span>
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
          <button
            @click="toggleChat"
            class="chatbot-close-btn"
            aria-label="Close chat"
          >
            <Icon name="heroicons:x-mark" class="h-5 w-5" />
          </button>
        </div>

        <!-- Messages Area -->
        <div ref="messagesContainer" class="chatbot-messages">
          <!-- Welcome Message -->
          <div v-if="messages.length === 0" class="chatbot-welcome">
            <Icon
              name="heroicons:hand-raised"
              class="h-12 w-12 text-primary-500 mb-3"
            />
            <h4 class="text-lg font-semibold text-gray-900 mb-2">
              Welcome! 👋
            </h4>
            <p class="text-sm text-gray-600 mb-4">
              Ask me anything about our NFC business cards, features, or
              pricing!
            </p>

            <!-- Quick Questions -->
            <div class="space-y-2">
              <p class="text-xs font-medium text-gray-500 mb-2">
                Quick Questions:
              </p>
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
              message.type === 'user'
                ? 'chatbot-message-user'
                : 'chatbot-message-bot',
            ]"
          >
            <div v-if="message.type === 'bot'" class="chatbot-message-avatar">
              <Icon name="heroicons:sparkles" class="h-4 w-4 text-white" />
            </div>

            <div class="chatbot-message-bubble">
              <p
                class="chatbot-message-text"
                v-html="formatMessage(message.text)"
              ></p>


            </div>

            <div
              v-if="message.type === 'user'"
              class="chatbot-message-avatar-user"
            >
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
              📝 Help us improve! Leave your contact info and we'll get back to
              you:
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
              <button
                @click="submitDetailedFeedback"
                class="chatbot-btn-primary"
              >
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

        <!-- Feedback Link -->
        <div class="chatbot-feedback-link-container">
          <a 
            href="#" 
            class="chatbot-feedback-link"
            @click.prevent="showFeedbackPanel = true"
          >
            💬 Send Feedback
          </a>
        </div>
      </div>
    </div>
    
    <!-- Feedback Modal (renders OUTSIDE chatbot via its own Teleport) -->
    <FeedbackModal
      v-model:show="showFeedbackPanel"
      :context-data="feedbackContext"
    />
  </Teleport>
</template>

<script setup>
import { ref, nextTick, onMounted, computed } from "vue";

// State
import FeedbackModal from "~/components/modals/FeedbackModal.vue";

// State
const isOpen = ref(false);
const userInput = ref("");
const messages = ref([]);
const isTyping = ref(false);
const unreadCount = ref(0);
const showFeedbackForm = ref(false); // For inline "no answer" form
const lastUserQuestion = ref("");
const messagesContainer = ref(null);

// For inline "no answer" form
const feedbackData = ref({
  name: "",
  email: "",
  message: "",
});

// General Feedback Modal State
const showFeedbackPanel = ref(false);
const activeFeedbackMessage = ref(null); // Context for feedback

// Feedback Context computed property
const feedbackContext = computed(() => {
    if (activeFeedbackMessage.value) {
        return {
            user_question: activeFeedbackMessage.value.index > 0 
                ? messages.value[activeFeedbackMessage.value.index - 1]?.text 
                : "",
            bot_response: activeFeedbackMessage.value.text,
            feedback_type: 'comment',
            conversation_id: "session-" + Date.now()
        };
    }
    return {
        feedback_type: 'comment',
        conversation_id: "session-" + Date.now()
    };
});

const quickQuestions = [
  "What is an NFC business card?",
  "How do I create my digital card?",
  "What are the pricing plans?",
  "How do I share my card?",
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
    type: "user",
    text: question,
    timestamp: new Date(),
  });

  userInput.value = "";
  isTyping.value = true;
  scrollToBottom();

  try {
    // Call n8n webhook directly with correct payload format
    const response = await $fetch(
      "https://n8n.jiosgroup.com/webhook/e529b3a4-d09d-45d6-8de5-01cc6885bcb7/chat",
      {
        method: "POST",
        body: {
          action: "sendMessage",
          sessionId: `session-${Date.now()}`, // Session identifier
          chatInput: question,
        },
      },
    );

    // Simulate typing delay
    await new Promise((resolve) => setTimeout(resolve, 800));

    // Handle n8n response - n8n returns { output: "..." }
    const answer = response.output || response.answer || response.message || response.data?.answer;

    if (answer) {
      // Add bot response
      messages.value.push({
        type: "bot",
        text: answer,
        feedbackGiven: false,
        timestamp: new Date(),
      });
    } else {
      // No answer found
      messages.value.push({
        type: "bot",
        text: "I'm sorry, I don't have an answer to that question yet. Would you like to leave your contact details so our team can help you?",
        timestamp: new Date(),
      });
      showFeedbackForm.value = true;
    }
  } catch (error) {
    console.error("Chatbot error:", error);
    messages.value.push({
      type: "bot",
      text: "Sorry, I'm having trouble connecting right now. Please try again later or contact our support team.",
      timestamp: new Date(),
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

// Submit detailed feedback - when no answer was found
const submitDetailedFeedback = async () => {
  try {
    const { $api } = useNuxtApp();

    await $api.post("/chatbot/feedback", {
      category: "question",
      message:
        feedbackData.value.message || `Question: ${lastUserQuestion.value}`,
      user_name: feedbackData.value.name || null,
      user_email: feedbackData.value.email || null,
      user_question: lastUserQuestion.value,
    });

    messages.value.push({
      type: "bot",
      text: "Thank you! We've received your message and will get back to you soon. 🙏",
      timestamp: new Date(),
    });
  } catch (error) {
    console.error("Feedback submission error:", error);
    messages.value.push({
      type: "bot",
      text: "Thank you for your message! Our team will review it. 🙏",
      timestamp: new Date(),
    });
  }

  closeFeedbackForm();
  scrollToBottom();
};

// Close feedback form
const closeFeedbackForm = () => {
  showFeedbackForm.value = false;
  feedbackData.value = { name: "", email: "", message: "" };
};

// Handle Inline Feedback (Yes/No)
const handleInlineFeedback = async (message, type, index) => {
  try {
    const { $api } = useNuxtApp();
    const userQuestion = index > 0 ? messages.value[index - 1]?.text : "";

    // immediate visual feedback
    if (type === "thumbs_up") {
      // Optional: set a local state to show it was liked
    }

    await $api.post("/chatbot/feedback", {
      feedback_type: type, // 'thumbs_up' or 'thumbs_down'
      message: type === "thumbs_up" ? "Helpful" : "Not Helpful",
      user_question: userQuestion,
      bot_response: message.text,
      conversation_id: "session-" + Date.now(), // In a real app, use actual session ID
      category: "general",
    });

    messages.value[index].feedbackGiven = true;
  } catch (error) {
    console.error("Feedback error:", error);
    // Mark as given anyway to prevent spam
    messages.value[index].feedbackGiven = true;
  }
};

// Open Detailed Feedback Modal (Comment)
const openDetailedFeedback = (message, index) => {
  activeFeedbackMessage.value = { ...message, index };

  // Pre-fill modal functionality
  generalFeedback.value = {
    category: "suggestion",
    message: "",
    rating: 0,
    name: "",
    email: "",
  };

  showFeedbackPanel.value = true;
};

// Submit general feedback from the feedback panel
const submitGeneralFeedback = async () => {
  if (!generalFeedback.value.message.trim()) return;

  submittingFeedback.value = true;

  try {
    const { $api } = useNuxtApp();

    const response = await $api.post("/chatbot/feedback", {
      category: generalFeedback.value.category,
      message: generalFeedback.value.message,
      user_name: generalFeedback.value.name || null,
      user_email: generalFeedback.value.email || null,
      rating: generalFeedback.value.rating || null,
      feedback_type: "comment",
      conversation_id: "session-" + Date.now(),
      // Add context if this is feedback for a specific message
      ...(activeFeedbackMessage.value
        ? {
            user_question:
              activeFeedbackMessage.value.index > 0
                ? messages.value[activeFeedbackMessage.value.index - 1]?.text
                : "",
            bot_response: activeFeedbackMessage.value.text,
          }
        : {}),
    });

    if (response.success) {
      // Show success state in modal
      feedbackSuccess.value = true;
      
      // If this was related to a specific message, mark it as handled
      if (activeFeedbackMessage.value) {
        if (messages.value[activeFeedbackMessage.value.index]) {
          messages.value[activeFeedbackMessage.value.index].feedbackGiven = true;
        }
        activeFeedbackMessage.value = null;
      }

      // Auto-close after 2.5 seconds
      setTimeout(() => {
        if (feedbackSuccess.value) {
          closeFeedbackPanel();
          // Add success message to chat
          messages.value.push({
            type: "bot",
            text: "Thank you for your feedback! 🙏 Our team will review it and get back to you if needed.",
            timestamp: new Date(),
          });
          scrollToBottom();
        }
      }, 2500);
    }
  } catch (error) {
    console.error("Feedback submission error:", error);
    closeFeedbackPanel();
    messages.value.push({
      type: "bot",
      text: "Sorry, there was an error submitting your feedback. Please try again.",
      timestamp: new Date(),
    });
    scrollToBottom();
  } finally {
    submittingFeedback.value = false;
  }
};

// Format message text (convert URLs to links, line breaks, etc.)
const formatMessage = (text) => {
  if (!text) return "";

  // Convert URLs to clickable links
  let formatted = text.replace(
    /(https?:\/\/[^\s]+)/g,
    '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-primary-600 hover:underline">$1</a>',
  );

  // Convert line breaks
  formatted = formatted.replace(/\n/g, "<br>");

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
  font-family:
    -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
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
  transition:
    transform 0.2s,
    box-shadow 0.2s;
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
  0%,
  100% {
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

/* Inline Feedback Styles */
.chatbot-inline-feedback {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid #f3f4f6;
  animation: fadeIn 0.3s ease-in-out;
}

.feedback-label {
  display: block;
  font-size: 11px;
  color: #9ca3af;
  margin-bottom: 6px;
  font-weight: 500;
}

.feedback-actions {
  display: flex;
  gap: 8px;
}

.feedback-btn {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 16px;
  border: 1px solid #e5e7eb;
  background: white;
  color: #6b7280;
  font-size: 11px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.feedback-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.btn-yes:hover {
  border-color: #10b981;
  color: #10b981;
  background: #ecfdf5;
}

.btn-no:hover {
  border-color: #ef4444;
  color: #ef4444;
  background: #fef2f2;
}

.btn-comment:hover {
  border-color: #8b5cf6;
  color: #8b5cf6;
  background: #f5f3ff;
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
  0%,
  80%,
  100% {
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

/* Feedback Trigger Button in Header */
.chatbot-feedback-trigger {
  background: rgba(255, 255, 255, 0.15);
  border: none;
  color: white;
  cursor: pointer;
  padding: 6px;
  border-radius: 6px;
  transition: background 0.2s;
  margin-left: 8px;
}

.chatbot-feedback-trigger:hover {
  background: rgba(255, 255, 255, 0.25);
}

/* Feedback Panel Overlay */
.chatbot-feedback-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  animation: fadeIn 0.2s ease-out;
}

.chatbot-feedback-panel {
  background: white;
  border-radius: 12px;
  width: calc(100% - 32px);
  max-height: calc(100% - 64px);
  display: flex;
  flex-direction: column;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
  overflow: hidden;
  animation: slideUp 0.3s ease-out;
}

.feedback-panel-header {
  padding: 16px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #f9fafb;
}

.feedback-close-btn {
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition:
    color 0.2s,
    background 0.2s;
}

.feedback-close-btn:hover {
  color: #111827;
  background: #e5e7eb;
}

.feedback-panel-body {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
}

.feedback-panel-footer {
  padding: 16px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  gap: 8px;
  background: #f9fafb;
}

.feedback-select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  cursor: pointer;
  transition: border-color 0.2s;
}

.feedback-select:focus {
  outline: none;
  border-color: #667eea;
}

.feedback-textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  resize: vertical;
  min-height: 80px;
  transition: border-color 0.2s;
  font-family: inherit;
}

.feedback-textarea:focus {
  outline: none;
  border-color: #667eea;
}

.feedback-input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.2s;
}

.feedback-input:focus {
  outline: none;
  border-color: #667eea;
}

.feedback-rating {
  display: flex;
  gap: 4px;
}

.rating-star {
  background: none;
  border: none;
  color: #d1d5db;
  cursor: pointer;
  padding: 2px;
  transition:
    color 0.2s,
    transform 0.1s;
}

.rating-star:hover {
  transform: scale(1.1);
}

.rating-star.active {
  color: #fbbf24;
}

.feedback-submit-btn {
  flex: 1;
  padding: 10px 16px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: opacity 0.2s;
}

.feedback-submit-btn:hover:not(:disabled) {
  opacity: 0.9;
}

.feedback-submit-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.feedback-cancel-btn {
  padding: 10px 16px;
  background: white;
  color: #6b7280;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
}

.feedback-cancel-btn:hover {
  background: #f9fafb;
}

/* ========================================
   NEW REDESIGNED FEEDBACK MODAL STYLES
   ======================================== */

/* Success State Styling */
.chatbot-feedback-panel.feedback-success {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 300px;
}

.feedback-success-content {
  text-align: center;
  padding: 32px 24px;
  animation: fadeInUp 0.4s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.success-icon-wrapper {
  margin-bottom: 16px;
  animation: successBounce 0.6s ease-out;
}

@keyframes successBounce {
  0% { transform: scale(0); }
  50% { transform: scale(1.2); }
  100% { transform: scale(1); }
}

.success-icon {
  width: 64px;
  height: 64px;
  color: #10b981;
  filter: drop-shadow(0 4px 12px rgba(16, 185, 129, 0.3));
}

.success-title {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 8px;
}

.success-subtitle {
  font-size: 14px;
  color: #6b7280;
  margin-bottom: 24px;
}

.feedback-another-btn {
  padding: 10px 20px;
  background: transparent;
  color: #667eea;
  border: 1px solid #667eea;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.feedback-another-btn:hover {
  background: #667eea;
  color: white;
}

/* Enhanced Header */
.feedback-header-content {
  display: flex;
  align-items: center;
  gap: 12px;
}

.feedback-header-icon {
  font-size: 28px;
  line-height: 1;
}

.feedback-title {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.feedback-subtitle {
  font-size: 12px;
  color: #6b7280;
  margin: 2px 0 0 0;
}

/* Feedback Sections */
.feedback-section {
  margin-bottom: 20px;
}

.section-label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 8px;
}

.required-mark {
  color: #ef4444;
}

/* Star Rating - Large & Prominent */
.rating-section {
  background: linear-gradient(135deg, #f8f9ff 0%, #fdf4ff 100%);
  border-radius: 12px;
  padding: 16px;
  text-align: center;
  border: 1px solid #e5e7eb;
}

.star-rating-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.feedback-rating-large {
  display: flex;
  gap: 8px;
  justify-content: center;
}

.rating-star-large {
  background: none;
  border: none;
  color: #d1d5db;
  cursor: pointer;
  padding: 4px;
  transition: all 0.2s ease;
  border-radius: 4px;
}

.rating-star-large:hover {
  transform: scale(1.15);
}

.rating-star-large.active {
  color: #fbbf24;
  filter: drop-shadow(0 2px 8px rgba(251, 191, 36, 0.5));
}

.rating-star-large.hover {
  color: #fcd34d;
  transform: scale(1.1);
}

.rating-label {
  font-size: 14px;
  font-weight: 500;
  color: #6b7280;
  min-height: 20px;
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Custom Select Dropdown */
.custom-select-wrapper {
  position: relative;
}

.feedback-select-styled {
  width: 100%;
  padding: 12px 40px 12px 14px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-size: 14px;
  background: white;
  cursor: pointer;
  transition: all 0.2s;
  appearance: none;
  -webkit-appearance: none;
}

.feedback-select-styled:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.select-arrow {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 20px;
  height: 20px;
  color: #6b7280;
  pointer-events: none;
}

/* Enhanced Textarea */
.feedback-textarea-styled {
  width: 100%;
  padding: 14px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-size: 14px;
  resize: none;
  min-height: 100px;
  transition: all 0.2s;
  font-family: inherit;
  line-height: 1.5;
}

.feedback-textarea-styled:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.feedback-textarea-styled::placeholder {
  color: #9ca3af;
}

/* Character Counter */
.char-counter {
  text-align: right;
  font-size: 12px;
  color: #9ca3af;
  margin-top: 4px;
  transition: color 0.2s;
}

.char-counter.near-limit {
  color: #f59e0b;
}

/* Collapsible Contact Section */
.contact-section {
  border-top: 1px solid #e5e7eb;
  padding-top: 16px;
  margin-top: 8px;
}

.contact-toggle {
  display: flex;
  align-items: center;
  gap: 8px;
  background: none;
  border: none;
  color: #667eea;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  padding: 8px 0;
  transition: color 0.2s;
  width: 100%;
  text-align: left;
}

.contact-toggle:hover {
  color: #5b6fd6;
}

.toggle-icon {
  width: 16px;
  height: 16px;
  transition: transform 0.2s;
}

.contact-toggle.expanded .toggle-icon {
  transform: rotate(180deg);
}

.contact-fields {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 12px;
  padding-top: 12px;
}

/* Input with Icon */
.input-with-icon {
  position: relative;
}

.input-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  color: #9ca3af;
  pointer-events: none;
}

.feedback-input-styled {
  width: 100%;
  padding: 12px 14px 12px 40px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-size: 14px;
  transition: all 0.2s;
}

.feedback-input-styled:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.feedback-input-styled::placeholder {
  color: #9ca3af;
}

/* Slide Down Transition */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease;
  overflow: hidden;
}

.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  max-height: 0;
  margin-top: 0;
  padding-top: 0;
}

.slide-down-enter-to,
.slide-down-leave-from {
  opacity: 1;
  max-height: 150px;
}

/* Enhanced Footer Buttons */
.feedback-cancel-btn-styled {
  flex: 0 0 auto;
  padding: 12px 20px;
  background: white;
  color: #6b7280;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.feedback-cancel-btn-styled:hover {
  background: #f9fafb;
  border-color: #d1d5db;
}

.feedback-submit-btn-styled {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 12px 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.feedback-submit-btn-styled:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.feedback-submit-btn-styled:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

/* Loading Spinner */
.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .chatbot-feedback-panel {
    width: 100%;
    max-height: 100%;
    border-radius: 16px 16px 0 0;
    margin-top: auto;
  }
  
  .chatbot-feedback-overlay {
    align-items: flex-end;
  }
  
  .feedback-panel-body {
    padding: 20px;
    max-height: calc(100vh - 200px);
    overflow-y: auto;
  }
  
  .rating-star-large {
    padding: 8px;
  }
  
  .rating-star-large svg {
    width: 36px;
    height: 36px;
  }
  
  .feedback-submit-btn-styled,
  .feedback-cancel-btn-styled {
    padding: 14px 24px;
    font-size: 15px;
  }
}

/* Feedback Link Anchor */
.chatbot-feedback-link-container {
  text-align: center;
  padding: 10px 16px 12px;
  background: #f9fafb;
  border-top: 1px solid #e5e7eb;
}

.chatbot-feedback-link {
  color: #667eea;
  font-size: 13px;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s ease;
  font-weight: 500;
}

.chatbot-feedback-link:hover {
  color: #764ba2;
  text-decoration: underline;
}
</style>
