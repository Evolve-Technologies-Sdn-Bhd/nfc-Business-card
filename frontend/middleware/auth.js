// middleware/auth.js
export default defineNuxtRouteMiddleware((to, from) => {
  const authStore = useAuthStore();

  // Skip check if navigating from the same path (refresh)
  if (to.path === from.path) {
    return;
  }

  // Check if user is authenticated
  if (!authStore.isAuthenticated) {
    console.log("User not authenticated, redirecting to login");
    // Redirect to login with the intended destination
    return navigateTo({
      path: "/UserAccount/login",
      query: { redirect: to.fullPath },
    });
  }

  // If user is admin and trying to access regular UserDashboard, redirect to admin panel
  if (authStore.isAdmin() && to.path.startsWith("/UserDashboard")) {
    console.log(
      "Admin user accessing UserDashboard, redirecting to admin panel"
    );
    return navigateTo("/AdminManagement");
  }

  console.log("User authenticated, proceeding to:", to.path);
});
