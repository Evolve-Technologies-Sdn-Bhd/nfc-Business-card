export default defineNuxtRouteMiddleware((to) => {
  // Only apply to admin routes
  if (to.path.startsWith("/AdminManagement")) {
    const authStore = useAuthStore();

    // Check if user is authenticated
    if (!authStore.isAuthenticated) {
      return navigateTo("/UserAccount/login");
    }

    // Check if user is admin
    if (!authStore.isAdmin()) {
      // Redirect non-admin users to UserDashboard
      return navigateTo("/UserDashboard");
    }
  }
});
