// middleware/onboarding-access.js
//
// Access rule for the self-serve onboarding funnel (plan -> design -> checkout).
//
//  • Signed-in users pass through unchanged.
//  • A visitor with NO account is only allowed in while shopping a plan that
//    supports guest checkout (Basic / Premium) — the plan is read from ?plan=
//    on the first step or from the draft already saved for this tab.
//  • Every other plan (free / business / none) still requires an account, so
//    the visitor is sent to register with their intended plan preserved.

const GUEST_CHECKOUT_PLANS = ["basic", "premium"];

export default defineNuxtRouteMiddleware((to) => {
  const authStore = useAuthStore();
  if (authStore.isAuthenticated) return;

  const requested = String(to.query.plan || "").toLowerCase();
  let plan = GUEST_CHECKOUT_PLANS.includes(requested) ? requested : null;

  // Past the first step the plan is no longer in the URL, so fall back to the
  // draft the funnel already persisted for this tab.
  if (!plan && process.client) {
    const stored = String(
      sessionStorage.getItem("selectedPlan") || ""
    ).toLowerCase();
    if (GUEST_CHECKOUT_PLANS.includes(stored)) plan = stored;
  }

  if (plan) return;

  return navigateTo({
    path: "/UserAccount/register",
    query: to.query.plan ? { plan: to.query.plan } : undefined,
  });
});
