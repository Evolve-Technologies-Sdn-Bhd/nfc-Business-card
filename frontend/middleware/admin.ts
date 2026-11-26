export default defineNuxtRouteMiddleware((to) => {
  // Only apply to admin routes and only on client side
  if (process.client && to.path.startsWith("/AdminManagement")) {
    const authStore = useAuthStore();

    // Check if user is authenticated
    if (!authStore.isAuthenticated) {
      return navigateTo(
        `/UserAccount/login?redirect=${encodeURIComponent(to.fullPath)}&admin=1`
      );
    }

    // Check if user is admin
    if (!authStore.isAdmin()) {
      // Redirect non-admin users to UserDashboard
      return navigateTo("/UserDashboard");
    }
  }
});
