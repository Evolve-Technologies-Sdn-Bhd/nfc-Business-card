<!-- pages/index.vue -->
<!-- Root route handler - redirects to Homepage -->
<script setup>
// Redirect to Homepage for unauthenticated users
// The auth middleware will handle authenticated users appropriately
definePageMeta({
  middleware: defineNuxtRouteMiddleware(() => {
    return navigateTo('/Homepage', { redirectCode: 301 });
  }),
});
</script>
