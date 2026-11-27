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
              <Icon name="heroicons:sparkles" class="h-5 w-5" />
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
              class="h-12 w-12 text-accent-500 mb-3"
            />
            <h4 class="card-title text-primary-900 mb-2">
              Welcome!
            </h4>
            <p class="text-body text-secondary-600 mb-4">
              Ask me anything about our NFC business cards, features, or
              pricing!
            </p>

            <!-- Quick Questions -->
            <div class="space-y-2">
              <p class="text-caption font-medium text-secondary-500 mb-2">
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
              <Icon name="heroicons:sparkles" class="h-4 w-4" />
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
              <Icon name="heroicons:user" class="h-4 w-4" />
            </div>
          </div>

          <!-- Typing Indicator -->
          <div v-if="isTyping" class="chatbot-message chatbot-message-bot">
            <div class="chatbot-message-avatar">
              <Icon name="heroicons:sparkles" class="h-4 w-4" />
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
            <p class="text-body font-medium text-secondary-700 mb-3">
              Help us improve! Leave your contact info and we'll get back to
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
            <Icon name="heroicons:chat-bubble-left-ellipsis" class="inline h-4 w-4 align-text-bottom mr-1" aria-hidden="true" />
            Send Feedback
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
const sessionId = ref("session-" + Date.now());

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
    // Ask our own backend, which searches the knowledge base first and
    // proxies to the AI assistant server-side. The webhook URL is never
    // exposed to the browser.
    const { $api } = useNuxtApp();

    const response = await $api.post("/chatbot/ask", {
      question,
      session_id: sessionId.value,
    });

    const answer = response?.data?.answer;

    if (response?.success && response?.found && answer) {
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
        text:
          response?.message ||
          "I'm sorry, I don't have an answer to that question yet. Would you like to leave your contact details so our team can help you?",
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
      text: "Thank you! We've received your message and will get back to you soon.",
      timestamp: new Date(),
    });
  } catch (error) {
    console.error("Feedback submission error:", error);
    messages.value.push({
      type: "bot",
      text: "Thank you for your message! Our team will review it.",
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

// Format message text (convert URLs to links, line breaks, etc.)
const formatMessage = (text) => {
  if (!text) return "";

  // Convert URLs to clickable links
  let formatted = text.replace(
    /(https?:\/\/[^\s]+)/g,
    '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-accent-700 font-medium hover:underline">$1</a>',
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
/* =========================================================
   NFC Card Assistant — Ink & Brass
   Re-skinned to the system default theme (Ink / Brass / Paper).
   Every colour resolves from the live theme CSS variables with
   brand fallbacks, so the widget also follows theme switching.
   ========================================================= */

/* Widget Container */
.chatbot-widget {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 9999;
  font-family:
    -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;

  /* Ink & Brass design tokens */
  --cb-ink: var(--theme-primary-900, #0b1017);
  --cb-ink-700: var(--theme-primary-700, #141d2b);
  --cb-brass: var(--theme-accent-500, #c2a068);
  --cb-brass-600: var(--theme-accent-600, #a9854d);
  --cb-brass-700: var(--theme-accent-700, #8c6b3b);
  --cb-brass-100: var(--theme-accent-100, #f5eedd);
  --cb-paper: var(--theme-secondary-50, #faf9f6);
  --cb-paper-2: var(--theme-secondary-100, #f3f1ea);
  --cb-line: var(--theme-secondary-200, #e6e2d6);
  --cb-line-strong: var(--theme-secondary-300, #cdc7b7);
  --cb-muted: var(--theme-secondary-600, #5c5548);
  --cb-muted-2: var(--theme-secondary-500, #7f7763);
  --cb-white: #ffffff;
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
  background: linear-gradient(150deg, var(--cb-ink-700) 0%, var(--cb-ink) 100%);
  color: var(--cb-brass);
  border: 1px solid color-mix(in srgb, var(--cb-brass) 45%, transparent);
  box-shadow:
    0 10px 26px rgba(11, 16, 23, 0.35),
    0 0 0 0 color-mix(in srgb, var(--cb-brass) 45%, transparent);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease;
  position: relative;
}

.chatbot-float-button:hover {
  transform: translateY(-2px) scale(1.04);
  box-shadow:
    0 14px 32px rgba(11, 16, 23, 0.42),
    0 0 0 6px color-mix(in srgb, var(--cb-brass) 16%, transparent);
}

.chatbot-float-button:focus-visible,
.chatbot-close-btn:focus-visible,
.chatbot-send-btn:focus-visible,
.chatbot-quick-btn:focus-visible,
.chatbot-btn-primary:focus-visible,
.chatbot-btn-secondary:focus-visible,
.chatbot-feedback-link:focus-visible {
  outline: 2px solid var(--cb-brass);
  outline-offset: 2px;
}

.chatbot-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: var(--cb-brass);
  color: var(--cb-ink);
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 700;
  box-shadow: 0 0 0 2px var(--cb-paper);
}

/* Chat Window */
.chatbot-window {
  width: 380px;
  height: 600px;
  background: var(--cb-white);
  border: 1px solid var(--cb-line);
  border-radius: 18px;
  box-shadow:
    0 24px 60px rgba(11, 16, 23, 0.22),
    0 4px 14px rgba(11, 16, 23, 0.1);
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
    border: none;
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
  position: relative;
  background: linear-gradient(150deg, var(--cb-ink-700) 0%, var(--cb-ink) 100%);
  color: var(--cb-paper);
  padding: 18px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.chatbot-header::after {
  content: "";
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  height: 2px;
  background: linear-gradient(
    90deg,
    transparent,
    var(--cb-brass) 50%,
    transparent
  );
  opacity: 0.75;
}

.chatbot-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(150deg, var(--cb-brass) 0%, var(--cb-brass-600) 100%);
  color: var(--cb-ink);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 10px color-mix(in srgb, var(--cb-brass) 40%, transparent);
}

.chatbot-title {
  font-size: 16px;
  font-weight: 600;
  letter-spacing: -0.01em;
  margin: 0;
}

.chatbot-status {
  font-size: 12px;
  color: color-mix(in srgb, var(--cb-paper) 78%, transparent);
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 2px;
}

.chatbot-status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--cb-brass);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--cb-brass) 25%, transparent);
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
  color: var(--cb-paper);
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
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
  background: var(--cb-paper);
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
  padding: 11px 14px;
  background: var(--cb-white);
  border: 1px solid var(--cb-line);
  border-radius: 10px;
  font-size: 13px;
  color: var(--cb-ink);
  cursor: pointer;
  transition: all 0.2s ease;
}

.chatbot-quick-btn:hover {
  background: var(--cb-brass-100);
  border-color: var(--cb-brass);
  transform: translateX(2px);
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
  background: var(--cb-ink);
  color: var(--cb-brass);
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
  background: var(--cb-brass);
  color: var(--cb-ink);
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
  background: var(--cb-white);
  border: 1px solid var(--cb-line);
  color: var(--cb-ink);
  border-bottom-left-radius: 4px;
}

.chatbot-message-user .chatbot-message-bubble {
  background: var(--cb-ink);
  color: var(--cb-paper);
  border-bottom-right-radius: 4px;
}

.chatbot-message-text {
  font-size: 14px;
  line-height: 1.5;
  margin: 0;
  word-wrap: break-word;
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
  background: var(--cb-brass-600);
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
  background: var(--cb-white);
  border: 1px solid var(--cb-line);
  border-radius: 12px;
  padding: 16px;
  margin-top: 12px;
}

.chatbot-input-small {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid var(--cb-line);
  border-radius: 8px;
  font-size: 13px;
  color: var(--cb-ink);
  background: var(--cb-white);
  transition:
    border-color 0.2s,
    box-shadow 0.2s;
}

.chatbot-input-small:focus {
  outline: none;
  border-color: var(--cb-brass);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--cb-brass) 16%, transparent);
}

.chatbot-btn-primary {
  flex: 1;
  padding: 8px 16px;
  background: var(--cb-ink);
  color: var(--cb-paper);
  border: none;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.chatbot-btn-primary:hover {
  background: var(--cb-brass);
  color: var(--cb-ink);
}

.chatbot-btn-secondary {
  flex: 1;
  padding: 8px 16px;
  background: var(--cb-white);
  color: var(--cb-muted);
  border: 1px solid var(--cb-line);
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.chatbot-btn-secondary:hover {
  background: var(--cb-paper-2);
  border-color: var(--cb-line-strong);
  color: var(--cb-ink);
}

/* Input Area */
.chatbot-input-area {
  padding: 16px 20px;
  background: var(--cb-white);
  border-top: 1px solid var(--cb-line);
  display: flex;
  gap: 8px;
}

.chatbot-input {
  flex: 1;
  padding: 10px 14px;
  border: 1px solid var(--cb-line);
  border-radius: 24px;
  font-size: 14px;
  color: var(--cb-ink);
  background: var(--cb-paper);
  transition:
    border-color 0.2s,
    box-shadow 0.2s;
}

.chatbot-input::placeholder {
  color: var(--cb-muted-2);
}

.chatbot-input:focus {
  outline: none;
  border-color: var(--cb-brass);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--cb-brass) 16%, transparent);
}

.chatbot-input:disabled {
  background: var(--cb-paper-2);
  cursor: not-allowed;
}

.chatbot-send-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--cb-brass);
  color: var(--cb-ink);
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  flex-shrink: 0;
}

.chatbot-send-btn:hover:not(:disabled) {
  background: var(--cb-brass-600);
}

.chatbot-send-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

/* Feedback Link Anchor */
.chatbot-feedback-link-container {
  text-align: center;
  padding: 10px 16px 12px;
  background: var(--cb-paper-2);
  border-top: 1px solid var(--cb-line);
}

.chatbot-feedback-link {
  color: var(--cb-brass-700);
  font-size: 13px;
  text-decoration: none;
  cursor: pointer;
  transition: color 0.2s ease;
  font-weight: 600;
}

.chatbot-feedback-link:hover {
  color: var(--cb-ink);
  text-decoration: underline;
}
</style>
