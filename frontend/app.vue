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
const authStore = useAuthStore();

// Initialize authentication state from cookie
// Only fetch profile on client-side after plugins are ready
onMounted(async () => {
  const tokenCookie = useCookie("auth-token");
  if (tokenCookie.value && !authStore.isAuthenticated) {
    try {
      await authStore.fetchProfile();
    } catch (error) {
      // Handle error silently - token might be expired
      console.log("Failed to fetch profile, token might be expired");
    }
  }
});
</script>

<style>
/* Global styles are handled in assets/css/main.css */
</style>
