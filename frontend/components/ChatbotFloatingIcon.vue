<template>
  <div 
    v-if="!isOpen"
    class="chatbot-floating-icon" 
    tabindex="0" 
    role="button" 
    aria-label="Open AI Chat Panel"
    @click="openChat"
    @keydown="handleKeyDown"
  >
    <div class="icon-wrapper">
      <!-- Notification Badge -->
      <span v-if="hasUnreadMessages" class="notification-badge">
        {{ unreadCount }}
      </span>
      
      <!-- Chat Icon -->
      <svg class="chat-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.17L4 17.17V4h16v12z" fill="currentColor"/>
        <path d="M7 9h10v2H7zm0 4h7v2H7z" fill="currentColor"/>
      </svg>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

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

const hasUnreadMessages = computed(() => props.unreadCount > 0)

const openChat = () => {
  emit('open')
}

// Keyboard accessibility
const handleKeyDown = (event) => {
  if (event.key === 'Enter' || event.key === ' ') {
    event.preventDefault()
    openChat()
  }
}
</script>

<style scoped>
.chatbot-floating-icon {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 99999 !important;
  cursor: pointer;
}

.icon-wrapper {
  position: relative;
  width: 56px;
  height: 56px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 16px rgba(102, 126, 234, 0.5);
  transition: all 0.3s ease;
  animation: pulse 2s infinite;
}

.icon-wrapper:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 24px rgba(102, 126, 234, 0.8);
}

.chatbot-floating-icon:focus {
  outline: 2px solid #667eea;
  outline-offset: 3px;
  border-radius: 50%;
}

.chat-icon {
  width: 28px;
  height: 28px;
  color: white;
}

.notification-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #ef4444;
  color: white;
  font-size: 12px;
  font-weight: bold;
  min-width: 20px;
  height: 20px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 6px;
  box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
  animation: bounce 1s infinite;
}

@keyframes pulse {
  0%, 100% {
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
  }
  50% {
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.6), 0 0 0 10px rgba(102, 126, 234, 0.1);
  }
}

@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-4px);
  }
}

@media (max-width: 768px) {
  .chatbot-floating-icon {
    bottom: 1rem;
    right: 1rem;
  }
  
  .icon-wrapper {
    width: 52px;
    height: 52px;
  }
  
  .chat-icon {
    width: 26px;
    height: 26px;
  }
}
</style>
