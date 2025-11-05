// middleware/admin.js
export default defineNuxtRouteMiddleware((to, from) => {
  const authStore = useAuthStore();

  // Check if user is authenticated
  if (!authStore.isAuthenticated) {
    console.log("User not authenticated, redirecting to login");
    return navigateTo({
      path: "/login",
      query: { redirect: to.fullPath },
    });
  }

  // Check if user is admin
  if (!authStore.user?.is_admin) {
    console.log("User not admin, redirecting to dashboard");
    return navigateTo({
      path: "/dashboard",
    });
  }

  console.log("User is admin, proceeding to:", to.path);
});
