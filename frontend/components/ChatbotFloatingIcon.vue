<template>
  <div class="chatbot-floating-icon">
    <!-- Floating Chat Button -->
    <button
      v-if="!isChatOpen"
      @click="openChat"
      class="chat-button"
      :class="{ 'has-notification': hasUnreadMessages }"
      aria-label="Open chat"
    >
      <!-- Chat Icon -->
        <svg
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke-width="1.5"
          stroke="currentColor"
          class="chat-icon"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"
          />
        </svg>

        <!-- Notification Badge -->
        <span v-if="hasUnreadMessages" class="notification-badge">
          {{ unreadCount > 9 ? '9+' : unreadCount }}
        </span>
      </button>

    <!-- Close Button (when chat is open) -->
    <button
      v-if="isChatOpen"
      @click="closeChat"
      class="close-button"
      aria-label="Close chat"
    >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke-width="1.5"
          stroke="currentColor"
          class="close-icon"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>
      </button>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  unreadCount: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['open', 'close'])

const isChatOpen = computed(() => props.isOpen)
const hasUnreadMessages = computed(() => props.unreadCount > 0)

const openChat = () => {
  emit('open')
}

const closeChat = () => {
  emit('close')
}
</script>

<style scoped>
.chatbot-floating-icon {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  z-index: 9999;
}

/* Chat Button */
.chat-button {
  position: relative;
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
  transition: all 0.3s ease;
}

.chat-button:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
}

.chat-button:active {
  transform: scale(0.95);
}

.chat-button.has-notification {
  animation: pulse 2s infinite;
}

.chat-icon {
  width: 32px;
  height: 32px;
  color: white;
}

/* Notification Badge */
.notification-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #ef4444;
  color: white;
  font-size: 11px;
  font-weight: bold;
  padding: 2px 6px;
  border-radius: 12px;
  min-width: 20px;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  animation: badge-bounce 0.5s ease;
}

/* Close Button */
.close-button {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(245, 87, 108, 0.4);
  transition: all 0.3s ease;
}

.close-button:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 20px rgba(245, 87, 108, 0.6);
}

.close-button:active {
  transform: scale(0.95);
}

.close-icon {
  width: 28px;
  height: 28px;
  color: white;
}

/* Animations */
@keyframes pulse {
  0%, 100% {
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
  }
  50% {
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.8);
  }
}

@keyframes badge-bounce {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.2);
  }
}

/* Transitions */
.bounce-enter-active {
  animation: bounce-in 0.5s;
}

.bounce-leave-active {
  animation: bounce-out 0.3s;
}

@keyframes bounce-in {
  0% {
    transform: scale(0);
    opacity: 0;
  }
  50% {
    transform: scale(1.1);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

@keyframes bounce-out {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  100% {
    transform: scale(0);
    opacity: 0;
  }
}

.rotate-enter-active {
  animation: rotate-in 0.3s;
}

.rotate-leave-active {
  animation: rotate-out 0.3s;
}

@keyframes rotate-in {
  0% {
    transform: scale(0) rotate(-180deg);
    opacity: 0;
  }
  100% {
    transform: scale(1) rotate(0deg);
    opacity: 1;
  }
}

@keyframes rotate-out {
  0% {
    transform: scale(1) rotate(0deg);
    opacity: 1;
  }
  100% {
    transform: scale(0) rotate(180deg);
    opacity: 0;
  }
}

/* Responsive */
@media (max-width: 768px) {
  .chatbot-floating-icon {
    bottom: 1.5rem;
    right: 1.5rem;
  }

  .chat-button,
  .close-button {
    width: 56px;
    height: 56px;
  }

  .chat-icon,
  .close-icon {
    width: 28px;
    height: 28px;
  }
}
</style>
