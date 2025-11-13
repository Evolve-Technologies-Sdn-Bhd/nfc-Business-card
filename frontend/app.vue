<!-- <template>
  <div>
    <NuxtRouteAnnouncer />
    <NuxtWelcome />
  </div>
</template> -->

<!-- app.vue -->
<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>

  <!-- Chatbot - Only on Homepage -->
  <ClientOnly>
    <template v-if="isHomePage">
      <ChatbotFloatingIcon
        :is-open="isChatOpen"
        :unread-count="unreadCount"
        @open="openChat"
        @close="closeChat"
      />
      <ChatbotInterface
        :is-open="isChatOpen"
        @close="closeChat"
        @ask="handleAsk"
        @submit-feedback="handleSubmitFeedback"
      />
    </template>
  </ClientOnly>
</template>

<script setup>
import { ref, computed } from 'vue'
import ChatbotFloatingIcon from '~/components/ChatbotFloatingIcon.vue'
import ChatbotInterface from '~/components/ChatbotInterface.vue'

const route = useRoute()

// Chat state - STARTS CLOSED
const isChatOpen = ref(false)
const unreadCount = ref(0)
const { $api } = useNuxtApp()

// Only show chatbot on homepage
const isHomePage = computed(() => route.path === '/')

// Log initial state for debugging
console.log('🤖 Chatbot initialized - isChatOpen:', isChatOpen.value)

const openChat = () => {
  console.log('🤖 Opening chat')
  isChatOpen.value = true
  unreadCount.value = 0
}

const closeChat = () => {
  console.log('🤖 Closing chat')
  isChatOpen.value = false
}

// Handle chatbot question
const handleAsk = async (question, callback) => {
  try {
    const response = await $api.post('/chatbot/ask', {
      question: question
    })

    if (response.data.success && response.data.found) {
      // Found an answer
      callback({
        success: true,
        answer: response.data.data.answer,
        questionId: response.data.data.id
      })
    } else {
      // No answer found
      callback({
        success: false,
        answer: null
      })
    }
  } catch (error) {
    console.error('Error asking chatbot:', error)
    callback({
      success: false,
      answer: null,
      error: error.message
    })
  }
}

// Handle feedback submission
const handleSubmitFeedback = async (feedbackData, callback) => {
  try {
    const payload = {
      question_id: feedbackData.questionId || null,
      user_question: feedbackData.userQuestion || '',
      user_message: feedbackData.userMessage || '',
      user_email: feedbackData.userEmail || '',
      rating: feedbackData.rating || 1,
      feedback_type: feedbackData.feedbackType === 'no-answer' ? 'not_found' : 
                      feedbackData.feedbackType === 'not-helpful' ? 'rating' : 
                      feedbackData.feedbackType === 'helpful' ? 'rating' : 'general'
    }

    const response = await $api.post('/chatbot/feedback', payload)

    if (response.data.success) {
      callback(true)
    } else {
      callback(false)
    }
  } catch (error) {
    console.error('Error submitting feedback:', error)
    callback(false)
  }
}
</script>

<style>
/* Global styles are handled in assets/css/main.css */
</style>
