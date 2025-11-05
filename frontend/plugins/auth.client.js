// plugins/auth.client.js
export default defineNuxtPlugin(async (nuxtApp) => {
  // Only run on client side
  if (process.server) return;

  const authStore = useAuthStore();

  // Initialize auth state on app start
  await authStore.initAuth();
}); 