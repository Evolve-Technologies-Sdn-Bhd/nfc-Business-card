// middleware/portal-access.global.js
//
// Plan / role gate for user-portal feature pages. Hiding a sidebar item is not
// enough — a Free user could still type the Premium builder URL, or a Business
// member could open Employee Management and hit a wall of 403s. Every rule here
// comes from usePortalAccess, which mirrors the backend's own checks.

import { resolvePortalAccess } from "~/composables/usePortalAccess";

export default defineNuxtRouteMiddleware((to) => {
  if (!to.path.startsWith("/UserDashboard")) return;

  const authStore = useAuthStore();
  if (!authStore.isAuthenticated || !authStore.user) return; // `auth` middleware handles this
  if (typeof authStore.isAdmin === "function" && authStore.isAdmin()) return;

  const access = resolvePortalAccess(authStore.user, authStore.resolvedSubscriptionPlan);
  const verdict = access.accessFor(to.path);
  if (verdict.allowed) return;

  if (verdict.reason && process.client) {
    const { $toast } = useNuxtApp();
    $toast?.info?.(verdict.reason);
  }

  const target = verdict.redirect || "/UserDashboard";
  if (target === to.path) return;
  return navigateTo({ path: target, query: to.query }, { replace: true });
});
