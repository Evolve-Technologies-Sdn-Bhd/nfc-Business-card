// composables/usePortalAccess.js
//
// Single frontend source of truth for the user portal: which plan the user is
// on, what that plan lets them open, and where each feature lives for them.
//
// The rules MIRROR the backend — they do not invent new ones:
//   * Paid entitlement  → App\Services\EntitlementService
//       (active paid subscription OR a card whose status is payment_verified+)
//   * Business roles    → User::businessRole / canManageBusiness / canManageBusinessCards
//       (enforced server-side by the `business.manager` middleware)
//   * Activity log      → Business\ActivityLogController (owner account only)
//   * Premium analytics → PremiumAnalytics page (subscription_plan = premium)
//   * Business analytics→ AnalyticsController@businessOverview (subscription_plan = business)
//
// The sidebar, the dashboard and the `portal-access` route middleware all read
// from here, so a feature can never be visible in one place and blocked in
// another, and a restricted page cannot be reached just by typing its URL.
// (The API still enforces its own checks — this layer is about UX + routing.)

export const PORTAL_ROUTES = {
  dashboard: "/UserDashboard",
  cards: "/UserDashboard/CardManagement",
  businessCards: "/UserDashboard/UserManagement/BusinessPlanUser/BusinessCardManagement",
  builders: {
    free: "/UserDashboard/UserManagement/FreePlanUser/FreeProfileBuilder",
    basic: "/UserDashboard/UserManagement/BasicPlanUser/BasicProfileBuilder",
    premium: "/UserDashboard/UserManagement/PremiumPlanUser/PremiumProfileBuilder",
    business: "/UserDashboard/UserManagement/BusinessPlanUser/BusinessProfileBuilder",
  },
  analytics: "/UserDashboard/Analytics",
  premiumAnalytics: "/UserDashboard/UserManagement/PremiumPlanUser/PremiumAnalytics",
  businessAnalytics: "/UserDashboard/UserManagement/BusinessPlanUser/BusinessAnalytics",
  employees: "/UserDashboard/UserManagement/BusinessPlanUser/BusinessEmployeeManagement",
  activityLog: "/UserDashboard/UserManagement/BusinessPlanUser/BusinessActivityLog",
  notifications: "/UserDashboard/Notifications",
  settings: "/UserDashboard/Settings",
  invoices: "/dashboard/invoices",
  plans: "/Onboarding/plan",
};

// Same list as EntitlementService::CARD_ENTITLEMENT_STATUSES
export const ENTITLED_CARD_STATUSES = [
  "payment_verified",
  "processing",
  "shipped",
  "delivered",
  "active",
];

const PAID_PLANS = ["basic", "premium", "business"];

const lower = (v) => String(v ?? "").toLowerCase();

/** Pure rule set over a user object — usable from stores, middleware and pages. */
export function resolvePortalAccess(user, resolvedPlan) {
  const cards = Array.isArray(user?.nfc_cards)
    ? user.nfc_cards
    : Array.isArray(user?.nfcCards)
      ? user.nfcCards
      : [];

  const plan = lower(resolvedPlan || user?.subscription_plan || "free") || "free";
  const accountPlan = lower(user?.subscription_plan || "free");
  const subscriptionActive = user?.subscription_active === true;

  // Free-plan digital cards (is_digital) are never an entitlement or an order.
  const physicalCards = cards.filter((c) => !c.is_digital);
  const entitledCards = cards.filter((c) => !c.is_digital && ENTITLED_CARD_STATUSES.includes(lower(c.status)));
  // Orders still waiting on payment (NfcCard::isPendingPayment / isAwaitingVerification)
  const pendingCards = physicalCards.filter((c) =>
    ["pending_payment", "awaiting_payment_verification"].includes(lower(c.status)),
  );

  const hasEligibleCard = (plans = null) =>
    entitledCards.some((c) => plans === null || plans.includes(lower(c.subscription_plan)));
  const hasActiveSubscription = (plans = PAID_PLANS) =>
    subscriptionActive && plans.includes(accountPlan);

  // EntitlementService::canAccessPaidProfileBuilder
  const canAccessPaidProfileBuilder = (tier = null) => {
    if (tier === null) return subscriptionActive || hasEligibleCard();
    const match = ["premium", "business"].includes(tier) ? [tier] : PAID_PLANS;
    return hasActiveSubscription(match) || hasEligibleCard(match);
  };

  // User::businessRole
  const isBusinessEmployee = user?.parent_business_id != null;
  const isBusinessOwner = accountPlan === "business" && !isBusinessEmployee;
  const businessRole = isBusinessOwner
    ? "owner"
    : isBusinessEmployee
      ? lower(user?.business_role) || "member"
      : null;
  const canManageBusiness = ["owner", "admin"].includes(businessRole);
  const canManageBusinessCards = ["owner", "admin", "card_manager"].includes(businessRole);

  const isBusiness = plan === "business";

  const routes = {
    cards: isBusiness ? PORTAL_ROUTES.businessCards : PORTAL_ROUTES.cards,
    builder: PORTAL_ROUTES.builders[plan] || PORTAL_ROUTES.builders.free,
    analytics: isBusiness
      ? PORTAL_ROUTES.businessAnalytics
      : plan === "premium"
        ? PORTAL_ROUTES.premiumAnalytics
        : PORTAL_ROUTES.analytics,
  };

  const can = {
    // Free plan keeps its own (restricted) builder; paid tiers need entitlement.
    builder: plan === "free" ? true : canAccessPaidProfileBuilder(plan),
    analytics:
      routes.analytics === PORTAL_ROUTES.businessAnalytics
        ? accountPlan === "business"
        : routes.analytics === PORTAL_ROUTES.premiumAnalytics
          ? accountPlan === "premium"
          : subscriptionActive || hasEligibleCard(),
    employees: canManageBusiness,
    activityLog: isBusinessOwner,
    invoices: physicalCards.length > 0 || PAID_PLANS.includes(accountPlan),
  };

  const deactivated = !subscriptionActive && !!user?.deactivated_at;
  const freeWindowEndsAt =
    accountPlan === "free" && user?.subscription_end_date ? user.subscription_end_date : null;

  /**
   * Can the user open `path`? Returns { allowed, redirect, reason }.
   * Only portal feature routes are judged; everything else is allowed.
   */
  const accessFor = (path) => {
    const p = String(path || "").replace(/\/+$/, "");
    const deny = (reason, redirect = PORTAL_ROUTES.dashboard) => ({ allowed: false, redirect, reason });
    const ok = { allowed: true };

    // Legacy single builder → the user's plan builder
    if (p === "/UserDashboard/ProfileBuilder") {
      return { allowed: false, redirect: routes.builder, reason: null };
    }

    const builderTier = Object.entries(PORTAL_ROUTES.builders).find(([, href]) => href === p)?.[0];
    if (builderTier) {
      if (builderTier === "free") {
        const ownsFreeCard = cards.some((c) => lower(c.subscription_plan) === "free");
        return plan === "free" || ownsFreeCard
          ? ok
          : { allowed: false, redirect: routes.builder, reason: null };
      }
      if (canAccessPaidProfileBuilder(builderTier)) return ok;
      return deny(
        `The ${builderTier.charAt(0).toUpperCase() + builderTier.slice(1)} Profile Builder unlocks once a ${builderTier} card payment is verified.`,
      );
    }

    if (p === PORTAL_ROUTES.premiumAnalytics) {
      return accountPlan === "premium" ? ok : { allowed: false, redirect: routes.analytics, reason: null };
    }
    if (p === PORTAL_ROUTES.businessAnalytics) {
      return accountPlan === "business"
        ? ok
        : { allowed: false, redirect: routes.analytics === p ? PORTAL_ROUTES.dashboard : routes.analytics, reason: null };
    }
    if (p === PORTAL_ROUTES.analytics) {
      if (routes.analytics !== p) return { allowed: false, redirect: routes.analytics, reason: null };
      return can.analytics ? ok : deny("Analytics becomes available once your plan is active.");
    }
    if (p === PORTAL_ROUTES.businessCards) {
      return isBusiness || accountPlan === "business"
        ? ok
        : { allowed: false, redirect: PORTAL_ROUTES.cards, reason: null };
    }
    if (p === PORTAL_ROUTES.employees) {
      return canManageBusiness ? ok : deny("Only the Business account owner or a delegated admin can manage team members.");
    }
    if (p === PORTAL_ROUTES.activityLog) {
      return isBusinessOwner ? ok : deny("The activity log is available to the Business account owner only.");
    }
    return ok;
  };

  return {
    plan,
    accountPlan,
    planLabel: plan.charAt(0).toUpperCase() + plan.slice(1),
    isFree: plan === "free",
    isBasic: plan === "basic",
    isPremium: plan === "premium",
    isBusiness,
    subscriptionActive,
    deactivated,
    freeWindowEndsAt,
    cards,
    entitledCards,
    pendingCards,
    hasEligibleCard,
    canAccessPaidProfileBuilder,
    businessRole,
    isBusinessOwner,
    isBusinessEmployee,
    canManageBusiness,
    canManageBusinessCards,
    routes,
    can,
    accessFor,
  };
}

/** Reactive wrapper bound to the auth store. */
export const usePortalAccess = () => {
  const authStore = useAuthStore();
  return computed(() => resolvePortalAccess(authStore.user, authStore.resolvedSubscriptionPlan));
};

/** Shared status wording for the plan chip and the dashboard. */
export function portalPlanStatus(a) {
  const fmt = (d) => {
    const date = new Date(d);
    return Number.isNaN(date.getTime())
      ? null
      : date.toLocaleDateString(undefined, { day: "numeric", month: "short", year: "numeric" });
  };
  if (a.deactivated) return { tone: "danger", label: "Inactive — choose a plan to reactivate" };
  if (a.subscriptionActive) {
    if (a.isFree && a.freeWindowEndsAt && fmt(a.freeWindowEndsAt)) {
      return { tone: "success", label: `Free window ends ${fmt(a.freeWindowEndsAt)}` };
    }
    return { tone: "success", label: "Active" };
  }
  if (a.entitledCards.length > 0) return { tone: "success", label: "Active — card payment verified" };
  if (a.pendingCards.length > 0) return { tone: "warning", label: "Awaiting payment verification" };
  return { tone: "neutral", label: "Not active" };
}
