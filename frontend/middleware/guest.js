// middleware/guest.js
//
// Guest-only access for the public marketing pages (/Homepage/*).
// Signed-in visitors are never shown the public landing pages — they are sent
// straight to their own area:
//   • Admin                        -> /AdminManagement
//   • Account still in onboarding  -> /Onboarding/plan
//   • Everyone else                -> /UserDashboard
export default defineNuxtRouteMiddleware(() => {
  const authStore = useAuthStore();
  if (!authStore.isAuthenticated) return;

  if (authStore.isAdmin()) {
    return navigateTo("/AdminManagement");
  }

  if (authStore.user?.is_new_user === true) {
    return navigateTo("/Onboarding/plan");
  }

  return navigateTo("/UserDashboard");
});
