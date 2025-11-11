// middleware/guest.js
export default defineNuxtRouteMiddleware((to, from) => {
  const authStore = useAuthStore();

  // Check if user is already authenticated
  if (authStore.isAuthenticated) {
    // Check if user has completed onboarding by looking for subscription plan
    const user = authStore.user;
    if (user && user.subscription_plan) {
      // User has completed onboarding, redirect to dashboard
      return navigateTo("/UserDashboard");
    } else {
      // User hasn't completed onboarding, let them continue with onboarding flow
      // Don't redirect automatically
    }
  }
});
