export default defineNuxtRouteMiddleware((to) => {
  // Only apply to admin routes and only on client side
  if (process.client && to.path.startsWith("/AdminManagement")) {
    const authStore = useAuthStore();

    console.log("Admin middleware check:", {
      path: to.path,
      isAuthenticated: authStore.isAuthenticated,
      isAdmin: authStore.isAdmin(),
      user: authStore.user,
    });

    // Check if user is authenticated
    if (!authStore.isAuthenticated) {
      console.log("Not authenticated, redirecting to login");
      return navigateTo("/UserAccount/login");
    }

    // Check if user is admin
    if (!authStore.isAdmin()) {
      console.log("Not admin, redirecting to UserDashboard");
      // Redirect non-admin users to UserDashboard
      return navigateTo("/UserDashboard");
    }

    console.log("Admin check passed, allowing access");
  }
});
