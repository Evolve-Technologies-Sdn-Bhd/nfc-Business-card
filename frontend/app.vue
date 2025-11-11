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

  <!-- Global Chatbot - Available on all pages -->
  <ChatbotFloatingIcon
    :is-open="isChatOpen"
    :unread-count="unreadCount"
    @open="openChat"
    @close="closeChat"
  />
  <ChatbotInterface
    :is-open="isChatOpen"
    @close="closeChat"
  />
</template>

<script setup>
const isChatOpen = ref(false);
const unreadCount = ref(0);

const openChat = () => {
  isChatOpen.value = true;
  unreadCount.value = 0;
};

const closeChat = () => {
  isChatOpen.value = false;
};
</script>

<style>
/* Global styles are handled in assets/css/main.css */
</style>
