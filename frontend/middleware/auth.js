// middleware/auth.js
export default defineNuxtRouteMiddleware((to, from) => {
  const authStore = useAuthStore();

  // Check if user is authenticated
  if (!authStore.isAuthenticated) {
    console.log("User not authenticated, redirecting to login");
    // Redirect to login with the intended destination
    return navigateTo({
      path: "/login",
      query: { redirect: to.fullPath },
    });
  }

  // If user is admin and trying to access regular dashboard, redirect to admin panel
  if (authStore.isAdmin() && to.path.startsWith("/dashboard")) {
    console.log("Admin user accessing dashboard, redirecting to admin panel");
    return navigateTo("/admin");
  }

  console.log("User authenticated, proceeding to:", to.path);
});
