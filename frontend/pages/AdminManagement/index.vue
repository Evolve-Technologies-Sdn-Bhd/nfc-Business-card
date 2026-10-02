<!-- pages/AdminManagement/index.vue -->
<template>
  <div class="relative">
    <!-- ==============================================
         FULL-PAGE INITIAL LOADING
         Show when: dashboard belum fetch lagi (null) OR API still loading
         ============================================== -->
    <div
      v-if="dashboard === null || loading"
      class="w-full min-h-[60vh] flex items-center justify-center py-16"
    >
      <NFCGoWaveLoader
        variant="wave"
        size="lg"
        :showText="true"
        labelText="NFCGo"
        hintText="Memuatkan dashboard..."
      />
    </div>

    <!-- ==============================================
         MAIN CONTENT (only after dashboard loaded)
         ============================================== -->
    <div v-else>
      <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-secondary-900">Admin Dashboard</h1>
        <p class="mt-2 text-secondary-600">
          Monitor and manage your NFC business card platform
        </p>
      </div>

      <!-- Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div v-for="stat in statsCards" :key="stat.name" class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div :class="['p-3 rounded-lg', stat.bgColor]">
              <Icon :name="stat.icon" class="h-6 w-6 text-white" />
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-secondary-600">
              {{ stat.name }}
            </p>
            <p class="text-2xl font-semibold text-secondary-900">
              {{ stat.value }}
            </p>
          </div>
        </div>
        <div class="mt-4">
          <div class="flex items-center text-sm">
            <Icon
              :name="
                stat.trend === 'up'
                  ? 'heroicons:arrow-trending-up'
                  : 'heroicons:arrow-trending-down'
              "
              :class="[
                'h-4 w-4 mr-1',
                stat.trend === 'up' ? 'text-success-500' : 'text-error-500',
              ]"
            />
            <span
              :class="[
                stat.trend === 'up' ? 'text-success-600' : 'text-error-600',
              ]"
            >
              {{ stat.change }}
            </span>
            <span class="text-secondary-500 ml-1">from last month</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Recent Users -->
      <div class="card">
        <div class="card-header flex flex-wrap items-center justify-between gap-2 w-full">
          <h3 class="text-lg font-medium text-secondary-900 whitespace-nowrap">Recent Users</h3>
          <NuxtLink
            to="/AdminManagement/users"
            class="text-sm text-primary-600 hover:text-primary-500 whitespace-nowrap"
          >
            View all
          </NuxtLink>
        </div>
        <div class="card-body">
          <div v-if="loading" class="flex justify-center py-10">
            <NFCGoWaveLoader
              variant="dotPulse"
              size="sm"
              :showText="true"
              labelText="NFCGo"
              :showHintText="false"
              inline
            />
          </div>
          <div v-else-if="dashboard?.recent_users?.length" class="space-y-4">
            <div
              v-for="user in dashboard.recent_users"
              :key="user.id"
              class="flex items-center space-x-3"
            >
              <img
                :src="user.profile?.profile_image || '/default-avatar.png'"
                :alt="user.full_name"
                class="h-10 w-10 rounded-full object-cover"
              />
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-secondary-900 truncate">
                  {{ user.full_name }}
                </p>
                <p class="text-xs text-secondary-500 truncate">
                  {{ user.email }}
                </p>
              </div>
              <div class="flex items-center space-x-2">
                <span
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    getSubscriptionBadgeClass(user.subscription_plan),
                  ]"
                >
                  {{ String(user.subscription_plan || '').replace(/\b\w/g, c => c.toUpperCase()) }}
                </span>
                <span
                  v-if="user.is_admin"
                  class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full"
                >
                  Admin
                </span>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8 text-secondary-500">
            <Icon
              name="heroicons:users"
              class="h-12 w-12 mx-auto text-secondary-300 mb-4"
            />
            <p>No recent users</p>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="card">
        <div class="card-header flex flex-wrap items-center justify-between gap-2 w-full">
          <h3 class="text-lg font-medium text-secondary-900 whitespace-nowrap">
            Recent Activity
          </h3>
          <NuxtLink
            to="/AdminManagement/SystemAuditLog"
            class="text-sm text-primary-600 hover:text-primary-500 whitespace-nowrap"
          >
            View all
          </NuxtLink>
        </div>
        <div class="card-body">
          <div v-if="loading" class="flex justify-center py-10">
            <NFCGoWaveLoader
              variant="dotPulse"
              size="sm"
              :showText="true"
              labelText="NFCGo"
              :showHintText="false"
              inline
            />
          </div>
          <div v-else-if="dashboard?.recent_activity?.length" class="space-y-4">
            <div
              v-for="activity in dashboard.recent_activity"
              :key="activity.id"
              class="flex items-start space-x-3"
            >
              <div class="flex-shrink-0 mt-0.5">
                <div
                  :class="[
                    'p-2 rounded-lg',
                    getActivityIconClass(activity.action),
                  ]"
                >
                  <Icon
                    :name="getActivityIcon(activity.action)"
                    class="h-4 w-4 text-white"
                  />
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-secondary-900 leading-snug">
                  {{ getActivityTitle(activity) }}
                </p>
                <p
                  v-if="getActivityContext(activity)"
                  class="text-xs text-secondary-500 mt-1 truncate"
                  :title="getActivityContext(activity)"
                >
                  {{ getActivityContext(activity) }}
                </p>
                <p class="text-xs text-secondary-400 mt-1">
                  {{ formatTimeAgo(activity.created_at) }}
                </p>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8 text-secondary-500">
            <Icon
              name="heroicons:clock"
              class="h-12 w-12 mx-auto text-secondary-300 mb-4"
            />
            <p>No recent activity</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Subscription Breakdown -->
    <div class="mt-8">
      <div class="card">
        <div class="card-header">
          <h3 class="text-lg font-medium text-secondary-900">
            Subscription Breakdown
          </h3>
        </div>
        <div class="card-body">
          <div v-if="loading" class="flex justify-center py-10">
            <NFCGoWaveLoader
              variant="dotPulse"
              size="sm"
              :showText="true"
              labelText="NFCGo"
              :showHintText="false"
              inline
            />
          </div>
          <div
            v-else-if="dashboard?.subscription_breakdown?.length"
            class="grid grid-cols-1 md:grid-cols-4 gap-4"
          >
            <div
              v-for="plan in dashboard.subscription_breakdown"
              :key="plan.subscription_plan"
              class="text-center p-4 rounded-lg bg-secondary-50"
            >
              <h4 class="text-lg font-semibold text-secondary-900">
                {{ String(plan.subscription_plan || '').replace(/\b\w/g, c => c.toUpperCase()) }}
              </h4>
              <p class="text-3xl font-bold text-primary-600">
                {{ plan.count }}
              </p>
              <p class="text-sm text-secondary-500">users</p>
            </div>
          </div>
          <div v-else class="text-center py-8 text-secondary-500">
            <p>No subscription data available</p>
          </div>
        </div>
      </div>
    </div>

    <!-- System Stats -->
    <div class="mt-8">
      <div class="card">
        <div class="card-header">
          <h3 class="text-lg font-medium text-secondary-900">
            System Information
          </h3>
        </div>
        <div class="card-body">
          <div v-if="loading" class="flex justify-center py-10">
            <NFCGoWaveLoader
              variant="dotPulse"
              size="sm"
              :showText="true"
              labelText="NFCGo"
              :showHintText="false"
              inline
            />
          </div>
          <div
            v-else-if="dashboard?.system_stats"
            class="grid grid-cols-1 md:grid-cols-3 gap-6"
          >
            <div class="text-center">
              <Icon
                name="heroicons:server"
                class="h-8 w-8 mx-auto text-primary-500 mb-2"
              />
              <h4 class="text-sm font-medium text-secondary-900">
                Storage Used
              </h4>
              <p class="text-lg font-semibold text-secondary-900">
                {{
                  dashboard.system_stats.total_storage_used?.formatted || "0 B"
                }}
              </p>
            </div>
            <div class="text-center">
              <Icon
                name="heroicons:signal"
                class="h-8 w-8 mx-auto text-primary-500 mb-2"
              />
              <h4 class="text-sm font-medium text-secondary-900">
                Active Sessions
              </h4>
              <p class="text-lg font-semibold text-secondary-900">
                {{ dashboard.system_stats.active_sessions || 0 }}
              </p>
            </div>
            <div class="text-center">
              <Icon
                name="heroicons:clock"
                class="h-8 w-8 mx-auto text-primary-500 mb-2"
              />
              <h4 class="text-sm font-medium text-secondary-900">
                Last Backup
              </h4>
              <p class="text-lg font-semibold text-secondary-900">
                {{ formatDate(dashboard.system_stats.last_backup) }}
              </p>
            </div>
          </div>
          <div v-else class="text-center py-8 text-secondary-500">
            <p>No system information available</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: "admin-management",
  middleware: "admin",
});

const adminStore = useAdminStore();
const { dashboard, loading } = storeToRefs(adminStore);

const isMounted = ref(false);

// Helper to build a single stat card entry from real overview.trends data
const buildStatCard = (key, name, icon, bgColor) => {
  const overview = dashboard.value?.overview || {};
  const trends = overview?.trends?.[key] || {};
  const direction = trends.direction || "up";
  const pct = Number(trends.change_pct ?? 0);
  const sign = pct >= 0 ? "+" : "-";
  return {
    name,
    value: overview?.[key] ?? 0,
    icon,
    bgColor,
    trend: direction,
    change: `${sign}${Math.abs(pct)}%`,
  };
};

// Stats cards data — values & trends come from the API (no hardcoded fakes)
const statsCards = computed(() => [
  buildStatCard("total_users", "Total Users", "heroicons:users", "bg-primary-500"),
  buildStatCard("total_profiles", "Total Profiles", "heroicons:identification", "bg-success-500"),
  buildStatCard("total_nfc_cards", "NFC Cards", "heroicons:credit-card", "bg-warning-500"),
  buildStatCard("total_analytics", "Total Analytics", "heroicons:chart-bar", "bg-info-500"),
]);

// Fetch dashboard data on mount + set up live auto-refresh every 30s
onMounted(async () => {
  isMounted.value = true;
  await adminStore.fetchDashboard();

  const intervalId = setInterval(async () => {
    if (!isMounted.value) return;
    try {
      await adminStore.fetchDashboard();
    } catch (_) {
      /* ignore transient network errors during silent refresh */
    }
  }, 30000);

  onBeforeUnmount(() => {
    isMounted.value = false;
    clearInterval(intervalId);
  });
});

// Helper functions
const getSubscriptionBadgeClass = (plan) => {
  const classes = {
    free: "bg-secondary-100 text-secondary-800",
    basic: "bg-blue-100 text-blue-800",
    premium: "bg-purple-100 text-purple-800",
    business: "bg-green-100 text-green-800",
  };
  return classes[plan] || classes.free;
};

const getActivityIcon = (action) => {
  const icons = {
    // Analytics (card / profile interactions)
    profile_view: "heroicons:eye",
    nfc_tap: "heroicons:credit-card",
    link_click: "heroicons:cursor-arrow-rays",

    // ActivityLog — auth / session
    user_logged_in: "heroicons:arrow-right-on-rectangle",
    user_login_success: "heroicons:arrow-right-on-rectangle",
    user_logged_out: "heroicons:arrow-left-on-rectangle",
    user_registered: "heroicons:user-plus",
    registration: "heroicons:user-plus",
    user_verified: "heroicons:check-badge",
    email_verified: "heroicons:envelope-open",
    password_updated: "heroicons:key",
    password_reset_requested: "heroicons:arrow-path",
    password_reset_completed: "heroicons:check",

    // ActivityLog — 2FA / security
    two_fa_enabled: "heroicons:shield-check",
    two_fa_disabled: "heroicons:shield-exclamation",
    "2fa_enabled": "heroicons:shield-check",
    "2fa_disabled": "heroicons:shield-exclamation",
    "2fa_setup_initiated": "heroicons:shield-exclamation",
    "2fa_recovery_codes_rotated": "heroicons:key",

    // ActivityLog — payments / orders
    payment_created: "heroicons:credit-card",
    payment_succeeded: "heroicons:check-circle",
    payment_failed: "heroicons:no-symbol",
    payment_verified: "heroicons:check-circle",
    order_placed: "heroicons:shopping-bag",
    nfc_order_placed: "heroicons:shopping-bag",
    order_approved: "heroicons:check-badge",
    order_rejected: "heroicons:x-circle",
    order_shipped: "heroicons:truck",
    order_delivered: "heroicons:home-modern",
    subscription_activated: "heroicons:sparkles",
    subscription_cancelled: "heroicons:no-symbol",
    subscription_paused: "heroicons:pause-circle",
    subscription_renewed: "heroicons:arrow-path",

    // ActivityLog — cards / profile
    card_activated: "heroicons:credit-card",
    card_deactivated: "heroicons:credit-card-slash",
    card_ordered: "heroicons:credit-card",
    profile_updated: "heroicons:pencil-square",
    profile_published: "heroicons:megaphone",
    profile_unpublished: "heroicons:eye-slash",
    profile_builder_accessed: "heroicons:user-circle",
    template_applied: "heroicons:document-duplicate",
    theme_changed: "heroicons:swatch",

    // ActivityLog — admin / system
    admin_approved_user: "heroicons:user-check",
    admin_rejected_user: "heroicons:user-minus",
    admin_verified_payment: "heroicons:currency-dollar",
    admin_created_card: "heroicons:plus-circle",
    admin_updated_config: "heroicons:cog-6-tooth",
    admin_updated_branding: "heroicons:paint-brush",
    admin_deleted_user: "heroicons:trash",

    // ActivityLog — business
    business_created: "heroicons:building-office-2",
    business_employee_added: "heroicons:user-plus",
    business_employee_removed: "heroicons:user-minus",
    business_quota_updated: "heroicons:archive-box",
  };
  return icons[action] || "heroicons:information-circle";
};

const getActivityIconClass = (action) => {
  const classes = {
    // Analytics interactions
    profile_view: "bg-blue-500",
    nfc_tap: "bg-green-500",
    link_click: "bg-purple-500",

    // Auth / session
    user_logged_in: "bg-orange-500",
    user_login_success: "bg-orange-500",
    user_logged_out: "bg-secondary-500",
    user_registered: "bg-success-500",
    registration: "bg-success-500",
    user_verified: "bg-success-500",
    email_verified: "bg-blue-500",
    password_updated: "bg-info-500",
    password_reset_requested: "bg-warning-500",
    password_reset_completed: "bg-success-500",

    // 2FA / security
    two_fa_enabled: "bg-success-500",
    two_fa_disabled: "bg-error-500",
    "2fa_enabled": "bg-success-500",
    "2fa_disabled": "bg-error-500",
    "2fa_setup_initiated": "bg-warning-500",
    "2fa_recovery_codes_rotated": "bg-info-500",

    // Payments / orders
    payment_created: "bg-warning-500",
    payment_succeeded: "bg-success-500",
    payment_failed: "bg-error-500",
    payment_verified: "bg-success-500",
    order_placed: "bg-blue-500",
    nfc_order_placed: "bg-blue-500",
    order_approved: "bg-success-500",
    order_rejected: "bg-error-500",
    order_shipped: "bg-primary-500",
    order_delivered: "bg-success-500",
    subscription_activated: "bg-success-500",
    subscription_cancelled: "bg-error-500",
    subscription_paused: "bg-warning-500",
    subscription_renewed: "bg-primary-500",

    // Cards / profile
    card_activated: "bg-success-500",
    card_deactivated: "bg-error-500",
    card_ordered: "bg-warning-500",
    profile_updated: "bg-info-500",
    profile_published: "bg-success-500",
    profile_unpublished: "bg-secondary-500",
    profile_builder_accessed: "bg-primary-500",
    template_applied: "bg-purple-500",
    theme_changed: "bg-pink-500",

    // Admin / system
    admin_approved_user: "bg-success-500",
    admin_rejected_user: "bg-error-500",
    admin_verified_payment: "bg-success-500",
    admin_created_card: "bg-primary-500",
    admin_updated_config: "bg-info-500",
    admin_updated_branding: "bg-purple-500",
    admin_deleted_user: "bg-error-500",

    // Business
    business_created: "bg-primary-500",
    business_employee_added: "bg-success-500",
    business_employee_removed: "bg-error-500",
    business_quota_updated: "bg-info-500",
  };
  return classes[action] || "bg-secondary-500";
};

const getActorName = (activity) => {
  return (
    activity?.actor?.full_name ||
    activity?.owner?.full_name ||
    activity?.data?.full_name ||
    activity?.data?.user_name ||
    activity?.card?.card_owner ||
    (activity?.data?.email ? String(activity.data.email).split("@")[0] : null) ||
    null
  );
};

const getActivityTitle = (activity) => {
  const action = activity.action;
  const card = activity.card;
  const trackable = activity.trackable;
  const actorName = getActorName(activity);

  // ------- If description exists from ActivityLog, prefer it for rich context, fall through to structured for short -------
  const rawDescription = activity.description;
  const useDescForActivity = [
    "user_profile_updated", "profile_updated", "admin_updated_config", "admin_updated_branding",
    "business_quota_updated", "template_applied", "theme_changed",
  ].includes(action);

  if (rawDescription && useDescForActivity) {
    if (actorName) {
      return `${actorName} → ${rawDescription}`;
    }
    return rawDescription;
  }

  // ------- Analytics (card interactions) -------
  switch (action) {
    case "nfc_tap": {
      const cardId =
        card?.card_id ||
        (trackable && (trackable.nfc_id || trackable.card_id)) ||
        activity?.data?.nfc_id ||
        null;
      const cardNum = card?.card_number ? `#${card.card_number}` : "";
      const ownerName =
        actorName || card?.card_owner || trackable?.name || null;
      const parts = [];
      if (cardId) parts.push(`NFC ${cardId}${cardNum ? ` (${cardNum})` : ""}`);
      else parts.push("NFC tag");
      if (ownerName) parts.push(`owned by ${ownerName}`);
      parts.push("was tapped");
      return parts.join(" ");
    }
    case "profile_view": {
      const ownerName = actorName || card?.card_owner || "A profile";
      const parts = [`${ownerName} profile viewed`];
      if (card?.card_id) parts.push(`(${card.card_id})`);
      return parts.join(" ");
    }
    case "link_click": {
      const linkLabel = activity.data?.label || activity.data?.url || "A link";
      const parts = [`${linkLabel} clicked`];
      if (card?.card_id) parts.push(`on card ${card.card_id}`);
      if (actorName) parts.push(`(${actorName})`);
      return parts.join(" ");
    }

    // ------- Auth / session -------
    case "user_logged_in":
    case "user_login_success":
      return `${actorName || "A user"} logged in`;
    case "user_logged_out":
      return `${actorName || "A user"} logged out`;
    case "user_registered":
    case "registration":
      return `${actorName || "A new user"} registered an account`;
    case "user_verified":
      return `${actorName || "A user"} account verified`;
    case "email_verified":
      return `${actorName || "A user"} verified email address`;
    case "password_updated":
      return `${actorName || "A user"} changed password`;
    case "password_reset_requested":
      return `${actorName || "A user"} requested password reset`;
    case "password_reset_completed":
      return `${actorName || "A user"} password reset completed`;

    // ------- 2FA / security -------
    case "two_fa_enabled":
    case "2fa_enabled":
      return `${actorName || "A user"} enabled 2FA security`;
    case "two_fa_disabled":
    case "2fa_disabled":
      return `${actorName || "A user"} disabled 2FA security`;
    case "2fa_setup_initiated":
      return `${actorName || "A user"} started 2FA setup`;
    case "2fa_recovery_codes_rotated":
      return `${actorName || "A user"} regenerated 2FA recovery codes`;

    // ------- Payments / orders -------
    case "payment_created":
      return `Payment initiated by ${actorName || "a user"}`;
    case "payment_succeeded":
    case "payment_verified":
      return `Payment succeeded for ${actorName || "a user"}`;
    case "payment_failed":
      return `Payment failed for ${actorName || "a user"}`;
    case "order_placed":
    case "nfc_order_placed": {
      const cardNum = card?.card_number ? ` #${card.card_number}` : "";
      return `${actorName || "A user"} placed NFC card order${cardNum}`;
    }
    case "order_approved":
      return `Order approved — ${actorName || card?.card_owner || "a user"}`;
    case "order_rejected":
      return `Order rejected — ${actorName || card?.card_owner || "a user"}`;
    case "order_shipped":
      return `Order shipped to ${actorName || card?.card_owner || "a user"}`;
    case "order_delivered":
      return `Order delivered to ${actorName || card?.card_owner || "a user"}`;
    case "subscription_activated":
      return `${actorName || "A user"} subscription activated`;
    case "subscription_cancelled":
      return `${actorName || "A user"} subscription cancelled`;
    case "subscription_paused":
      return `${actorName || "A user"} subscription paused`;
    case "subscription_renewed":
      return `${actorName || "A user"} subscription renewed`;

    // ------- Cards / profile -------
    case "card_activated":
    case "nfc_card_activated": {
      const cid = card?.card_id || activity?.data?.card_id || "a card";
      return `${actorName || "User"} activated NFC ${cid}`;
    }
    case "card_deactivated": {
      const cid = card?.card_id || activity?.data?.card_id || "a card";
      return `${actorName || "User"} deactivated NFC ${cid}`;
    }
    case "card_ordered":
      return `${actorName || "A user"} ordered a new NFC card`;
    case "profile_updated":
    case "user_profile_updated":
      return `${actorName || "A user"} updated their profile`;
    case "profile_published":
      return `${actorName || "A user"} published their profile page`;
    case "profile_unpublished":
      return `${actorName || "A user"} unpublished their profile page`;
    case "profile_builder_accessed":
      return `${actorName || "A user"} opened the Profile Builder`;
    case "template_applied":
      return `${actorName || "A user"} applied a new profile template`;
    case "theme_changed":
      return `${actorName || "A user"} changed profile theme`;

    // ------- Admin actions -------
    case "admin_approved_user":
      return `Admin approved user: ${actorName || activity?.data?.user_email || "N/A"}`;
    case "admin_rejected_user":
      return `Admin rejected user: ${actorName || activity?.data?.user_email || "N/A"}`;
    case "admin_verified_payment":
      return `Admin verified payment (${actorName || activity?.data?.amount || "—"})`;
    case "admin_created_card": {
      const cid = card?.card_id || activity?.data?.card_id || "new card";
      return `Admin created NFC ${cid}`;
    }
    case "admin_updated_config":
      return `Admin updated system configuration`;
    case "admin_updated_branding":
      return `Admin updated branding / assets`;
    case "admin_deleted_user":
      return `Admin deleted user: ${actorName || activity?.data?.user_email || "N/A"}`;

    // ------- Business -------
    case "business_created":
      return `${actorName || "A user"} created a Business account`;
    case "business_employee_added":
      return `${actorName || "Business admin"} added an employee`;
    case "business_employee_removed":
      return `${actorName || "Business admin"} removed an employee`;
    case "business_quota_updated":
      return `${actorName || "Business admin"} updated employee quota`;

    default:
      if (rawDescription) {
        return actorName ? `${actorName} — ${rawDescription}` : rawDescription;
      }
      return "Activity recorded";
  }
};

const getActivityContext = (activity) => {
  const parts = [];
  if (activity.location) parts.push(activity.location);
  if (activity.device_type || activity.platform || activity.browser) {
    const dev = [activity.device_type, activity.platform, activity.browser]
      .filter(Boolean)
      .join(" · ");
    if (dev) parts.push(dev);
  }
  if (activity.ip_address) parts.push(`IP ${activity.ip_address}`);
  return parts.join("  •  ");
};

const formatTimeAgo = (timestamp) => {
  if (!timestamp) return "";
  const date = new Date(timestamp);
  const now = new Date();
  const diffInMinutes = Math.floor((now - date) / (1000 * 60));

  if (diffInMinutes < 1) return "Just now";
  if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
  if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h ago`;
  return `${Math.floor(diffInMinutes / 1440)}d ago`;
};

const formatDate = (dateString) => {
  if (!dateString) return "Never";
  return new Date(dateString).toLocaleDateString();
};
</script>
