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
</template>

<script setup>
// Initialize auth store when app starts
const authStore = useAuthStore()

// Initialize authentication state from localStorage
// onMounted(() => {
//   authStore.initializeAuth()
// })
const tokenCookie = useCookie('auth-token')
if (tokenCookie.value && !authStore.isAuthenticated) {
  await authStore.fetchProfile().catch(() => {
    // Handle error silently
  })
}
</script>

<style>
/* Global styles are handled in assets/css/main.css */
</style>
