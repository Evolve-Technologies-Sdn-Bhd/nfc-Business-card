<!-- <template>
  <div>
    <NuxtRouteAnnouncer />
    <NuxtWelcome />
  </div>
</template> -->

<!-- app.vue -->
<template>
  <div class="app-root">
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>

<script setup>
// Chatbot is now only on the Homepage
</script>

<style>
/* Global styles are handled in assets/css/main.css */

.app-root {
  width: 100%;
  min-height: 100vh;
  overflow-x: hidden;
  position: relative;
}

/* Prevent layout shift and horizontal scroll */
* {
  box-sizing: border-box;
}

body {
  overflow-x: hidden;
  position: relative;
}
</style>
