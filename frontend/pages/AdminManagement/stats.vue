<!-- pages/AdminManagement/stats.vue -->
<template>
  <div>
    <div class="mb-8">
      <h1 class="text-2xl sm:text-3xl font-bold text-secondary-900">Analytics</h1>
      <p class="mt-2 text-secondary-600">
        Comprehensive overview of platform performance and usage
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
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- User Statistics -->
      <div class="card">
        <div class="card-header">
          <h3 class="text-lg font-medium text-secondary-900">
            User Statistics
          </h3>
        </div>
        <div class="card-body">
          <div v-if="loading" class="flex justify-center py-8">
            <div class="spinner"></div>
          </div>
          <div v-else-if="stats?.users" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div class="text-center p-4 bg-secondary-50 rounded-lg">
                <p class="text-2xl font-bold text-primary-600">
                  {{ stats.users.total }}
                </p>
                <p class="text-sm text-secondary-600">Total Users</p>
              </div>
              <div class="text-center p-4 bg-secondary-50 rounded-lg">
                <p class="text-2xl font-bold text-success-600">
                  {{ stats.users.active }}
                </p>
                <p class="text-sm text-secondary-600">Active Subscriptions</p>
              </div>
            </div>
            <div class="text-center p-4 bg-secondary-50 rounded-lg">
              <p class="text-2xl font-bold text-warning-600">
                {{ stats.users.admin }}
              </p>
              <p class="text-sm text-secondary-600">Admin Users</p>
            </div>
            <div class="text-center p-4 bg-secondary-50 rounded-lg">
              <p class="text-2xl font-bold text-info-600">
                {{ stats.users.new_this_month }}
              </p>
              <p class="text-sm text-secondary-600">New This Month</p>
            </div>
          </div>
          <div v-else class="text-center py-8 text-secondary-500">
            <p>No user statistics available</p>
          </div>
        </div>
      </div>

      <!-- Subscription Breakdown -->
      <div class="card">
        <div class="card-header">
          <h3 class="text-lg font-medium text-secondary-900">
            Subscription Breakdown
          </h3>
        </div>
        <div class="card-body">
          <div v-if="loading" class="flex justify-center py-8">
            <div class="spinner"></div>
          </div>
          <div v-else-if="stats?.subscriptions" class="space-y-3">
            <div
              v-for="(count, plan) in stats.subscriptions"
              :key="plan"
              class="flex items-center justify-between"
            >
              <div class="flex items-center">
                <div
                  :class="['w-3 h-3 rounded-full mr-3', getPlanColor(plan)]"
                ></div>
                <span
                  class="text-sm font-medium text-secondary-900 capitalize"
                  >{{ plan }}</span
                >
              </div>
              <span class="text-sm font-semibold text-secondary-900">{{
                count
              }}</span>
            </div>
          </div>
          <div v-else class="text-center py-8 text-secondary-500">
            <p>No subscription data available</p>
          </div>
        </div>
      </div>
    </div>

    <!-- NFC Cards and Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
      <!-- NFC Card Statistics -->
      <div class="card">
        <div class="card-header">
          <h3 class="text-lg font-medium text-secondary-900">
            NFC Card Statistics
          </h3>
        </div>
        <div class="card-body">
          <div v-if="loading" class="flex justify-center py-8">
            <div class="spinner"></div>
          </div>
          <div v-else-if="stats?.nfc_cards" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div class="text-center p-4 bg-secondary-50 rounded-lg">
                <p class="text-2xl font-bold text-primary-600">
                  {{ stats.nfc_cards.total }}
                </p>
                <p class="text-sm text-secondary-600">Total Cards</p>
              </div>
              <div class="text-center p-4 bg-secondary-50 rounded-lg">
                <p class="text-2xl font-bold text-success-600">
                  {{ stats.nfc_cards.active }}
                </p>
                <p class="text-sm text-secondary-600">Active Cards</p>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div class="text-center p-4 bg-secondary-50 rounded-lg">
                <p class="text-2xl font-bold text-warning-600">
                  {{ stats.nfc_cards.pending }}
                </p>
                <p class="text-sm text-secondary-600">Pending</p>
              </div>
              <div class="text-center p-4 bg-secondary-50 rounded-lg">
                <p class="text-2xl font-bold text-info-600">
                  {{ stats.nfc_cards.shipped }}
                </p>
                <p class="text-sm text-secondary-600">Shipped</p>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8 text-secondary-500">
            <p>No NFC card statistics available</p>
          </div>
        </div>
      </div>

      <!-- Analytics Overview -->
      <div class="card">
        <div class="card-header">
          <h3 class="text-lg font-medium text-secondary-900">
            Analytics Overview
          </h3>
        </div>
        <div class="card-body">
          <div v-if="loading" class="flex justify-center py-8">
            <div class="spinner"></div>
          </div>
          <div v-else-if="stats?.analytics" class="space-y-4">
            <div class="text-center p-4 bg-secondary-50 rounded-lg">
              <p class="text-2xl font-bold text-primary-600">
                {{ stats.analytics.total_tracks }}
              </p>
              <p class="text-sm text-secondary-600">Total Analytics Events</p>
            </div>
            <div class="grid grid-cols-3 gap-4">
              <div class="text-center p-3 bg-secondary-50 rounded-lg">
                <p class="text-lg font-bold text-blue-600">
                  {{ stats.analytics.profile_views }}
                </p>
                <p class="text-xs text-secondary-600">Profile Views</p>
              </div>
              <div class="text-center p-3 bg-secondary-50 rounded-lg">
                <p class="text-lg font-bold text-green-600">
                  {{ stats.analytics.nfc_taps }}
                </p>
                <p class="text-xs text-secondary-600">NFC Taps</p>
              </div>
              <div class="text-center p-3 bg-secondary-50 rounded-lg">
                <p class="text-lg font-bold text-purple-600">
                  {{ stats.analytics.link_clicks }}
                </p>
                <p class="text-xs text-secondary-600">Link Clicks</p>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8 text-secondary-500">
            <p>No analytics data available</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Storage and System Info -->
    <div class="mt-8">
      <div class="card">
        <div class="card-header">
          <h3 class="text-lg font-medium text-secondary-900">
            Storage & System Information
          </h3>
        </div>
        <div class="card-body">
          <div v-if="loading" class="flex justify-center py-8">
            <div class="spinner"></div>
          </div>
          <div
            v-else-if="stats?.storage"
            class="grid grid-cols-1 md:grid-cols-3 gap-6"
          >
            <div class="text-center">
              <Icon
                name="heroicons:server"
                class="h-8 w-8 mx-auto text-primary-500 mb-2"
              />
              <h4 class="text-sm font-medium text-secondary-900">
                Total Storage Used
              </h4>
              <p class="text-lg font-semibold text-secondary-900">
                {{ stats.storage.total_used?.formatted || "0 B" }}
              </p>
            </div>
            <div class="text-center">
              <Icon
                name="heroicons:photo"
                class="h-8 w-8 mx-auto text-primary-500 mb-2"
              />
              <h4 class="text-sm font-medium text-secondary-900">
                Profile Images
              </h4>
              <p class="text-lg font-semibold text-secondary-900">
                {{ stats.storage.profiles || 0 }}
              </p>
            </div>
            <div class="text-center">
              <Icon
                name="heroicons:building-office"
                class="h-8 w-8 mx-auto text-primary-500 mb-2"
              />
              <h4 class="text-sm font-medium text-secondary-900">
                Company Logos
              </h4>
              <p class="text-lg font-semibold text-secondary-900">
                {{ stats.storage.logos || 0 }}
              </p>
            </div>
          </div>
          <div v-else class="text-center py-8 text-secondary-500">
            <p>No storage information available</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity Timeline -->
    <div class="mt-8">
      <div class="card">
        <div class="card-header">
          <h3 class="text-lg font-medium text-secondary-900">
            Recent System Activity
          </h3>
        </div>
        <div class="card-body">
          <div v-if="loading" class="flex justify-center py-8">
            <div class="spinner"></div>
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
                <p class="text-sm text-secondary-900 leading-snug font-medium">
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

    <!-- Export Options -->
    <div class="mt-8">
      <div class="card">
        <div class="card-header">
          <h3 class="text-lg font-medium text-secondary-900">Data Export</h3>
        </div>
        <div class="card-body">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <button 
              @click="exportUsers" 
              :disabled="exportingUsers"
              class="btn btn-outline w-full"
            >
              <div v-if="exportingUsers" class="spinner mr-2"></div>
              <Icon v-else name="heroicons:users" class="h-5 w-5 mr-2" />
              {{ exportingUsers ? 'Exporting...' : 'Export Users' }}
            </button>
            <button 
              @click="exportNfcCards" 
              :disabled="exportingCards"
              class="btn btn-outline w-full"
            >
              <div v-if="exportingCards" class="spinner mr-2"></div>
              <Icon v-else name="heroicons:credit-card" class="h-5 w-5 mr-2" />
              {{ exportingCards ? 'Exporting...' : 'Export NFC Cards' }}
            </button>
            <button 
              @click="exportAnalytics" 
              :disabled="exportingAnalytics"
              class="btn btn-outline w-full"
            >
              <div v-if="exportingAnalytics" class="spinner mr-2"></div>
              <Icon v-else name="heroicons:chart-bar" class="h-5 w-5 mr-2" />
              {{ exportingAnalytics ? 'Exporting...' : 'Export Analytics' }}
            </button>
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

const { $api, $toast } = useNuxtApp();
const adminStore = useAdminStore();
const { stats, loading, dashboard } = storeToRefs(adminStore);

// Export loading states
const exportingUsers = ref(false);
const exportingCards = ref(false);
const exportingAnalytics = ref(false);

// Stats cards data
const statsCards = computed(() => [
  {
    name: "Total Users",
    value: stats.value?.users?.total || 0,
    icon: "heroicons:users",
    bgColor: "bg-primary-500",
  },
  {
    name: "Active Subscriptions",
    value: stats.value?.users?.active || 0,
    icon: "heroicons:check-circle",
    bgColor: "bg-success-500",
  },
  {
    name: "NFC Cards",
    value: stats.value?.nfc_cards?.total || 0,
    icon: "heroicons:credit-card",
    bgColor: "bg-warning-500",
  },
  {
    name: "Total Analytics",
    value: stats.value?.analytics?.total_tracks || 0,
    icon: "heroicons:chart-bar",
    bgColor: "bg-info-500",
  },
]);

// Fetch data on mount
onMounted(async () => {
  await Promise.all([adminStore.fetchStats(), adminStore.fetchDashboard()]);
});

// Helper functions
const getPlanColor = (plan) => {
  const colors = {
    free: "bg-secondary-400",
    basic: "bg-blue-400",
    premium: "bg-purple-400",
    business: "bg-green-400",
  };
  return colors[plan] || "bg-secondary-400";
};

const getActivityIcon = (action) => {
  const icons = {
    profile_view: "heroicons:eye",
    nfc_tap: "heroicons:credit-card",
    link_click: "heroicons:cursor-arrow-rays",

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

    two_fa_enabled: "heroicons:shield-check",
    two_fa_disabled: "heroicons:shield-exclamation",
    "2fa_enabled": "heroicons:shield-check",
    "2fa_disabled": "heroicons:shield-exclamation",
    "2fa_setup_initiated": "heroicons:shield-exclamation",
    "2fa_recovery_codes_rotated": "heroicons:key",

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

    card_activated: "heroicons:credit-card",
    card_deactivated: "heroicons:credit-card-slash",
    card_ordered: "heroicons:credit-card",
    profile_updated: "heroicons:pencil-square",
    profile_published: "heroicons:megaphone",
    profile_unpublished: "heroicons:eye-slash",
    profile_builder_accessed: "heroicons:user-circle",
    template_applied: "heroicons:document-duplicate",
    theme_changed: "heroicons:swatch",

    admin_approved_user: "heroicons:user-check",
    admin_rejected_user: "heroicons:user-minus",
    admin_verified_payment: "heroicons:currency-dollar",
    admin_created_card: "heroicons:plus-circle",
    admin_updated_config: "heroicons:cog-6-tooth",
    admin_updated_branding: "heroicons:paint-brush",
    admin_deleted_user: "heroicons:trash",

    business_created: "heroicons:building-office-2",
    business_employee_added: "heroicons:user-plus",
    business_employee_removed: "heroicons:user-minus",
    business_quota_updated: "heroicons:archive-box",
  };
  return icons[action] || "heroicons:information-circle";
};

const getActivityIconClass = (action) => {
  const classes = {
    profile_view: "bg-blue-500",
    nfc_tap: "bg-green-500",
    link_click: "bg-purple-500",

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

    two_fa_enabled: "bg-success-500",
    two_fa_disabled: "bg-error-500",
    "2fa_enabled": "bg-success-500",
    "2fa_disabled": "bg-error-500",
    "2fa_setup_initiated": "bg-warning-500",
    "2fa_recovery_codes_rotated": "bg-info-500",

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

    card_activated: "bg-success-500",
    card_deactivated: "bg-error-500",
    card_ordered: "bg-warning-500",
    profile_updated: "bg-info-500",
    profile_published: "bg-success-500",
    profile_unpublished: "bg-secondary-500",
    profile_builder_accessed: "bg-primary-500",
    template_applied: "bg-purple-500",
    theme_changed: "bg-pink-500",

    admin_approved_user: "bg-success-500",
    admin_rejected_user: "bg-error-500",
    admin_verified_payment: "bg-success-500",
    admin_created_card: "bg-primary-500",
    admin_updated_config: "bg-info-500",
    admin_updated_branding: "bg-purple-500",
    admin_deleted_user: "bg-error-500",

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
    activity?.trackable?.name ||
    null
  );
};

const getActivityTitle = (activity) => {
  const action = activity.action;
  const card = activity.card;
  const trackable = activity.trackable;
  const actorName = getActorName(activity);
  const rawDescription = activity.description;
  const useDescForActivity = [
    "user_profile_updated", "profile_updated", "admin_updated_config", "admin_updated_branding",
    "business_quota_updated", "template_applied", "theme_changed",
  ].includes(action);

  if (rawDescription && useDescForActivity) {
    return actorName ? `${actorName} → ${rawDescription}` : rawDescription;
  }

  switch (action) {
    case "nfc_tap": {
      const cardId =
        card?.card_id ||
        (trackable && (trackable.nfc_id || trackable.card_id)) ||
        activity?.data?.nfc_id ||
        null;
      const cardNum = card?.card_number ? `#${card.card_number}` : "";
      const ownerName = actorName || card?.card_owner || trackable?.name || null;
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

// Download helper
const downloadCsv = async (endpoint, filename, loadingRef) => {
  loadingRef.value = true;
  try {
    const config = useRuntimeConfig();
    const apiBaseUrl = config.public.apiBaseUrl;
    const tokenCookie = useCookie("auth-token");
    const token = tokenCookie.value;

    if (!token) {
      $toast.error("Authentication required. Please log in again.");
      return;
    }

    const response = await fetch(`${apiBaseUrl}${endpoint}`, {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: "text/csv",
      },
    });

    if (!response.ok) {
      throw new Error("Export failed");
    }

    const blob = await response.blob();
    const url = window.URL.createObjectURL(blob);
    
    // Create temp link and trigger download
    const link = document.createElement("a");
    link.href = url;
    link.download = filename;
    link.style.display = "none";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Clean up
    window.URL.revokeObjectURL(url);
    
    $toast.success("Export completed successfully");
  } catch (error) {
    console.error("Export error:", error);
    $toast.error("Export failed. Please try again.");
  } finally {
    loadingRef.value = false;
  }
};

// Export functions
const exportUsers = () => {
  const today = new Date().toISOString().split('T')[0].replace(/-/g, '');
  downloadCsv('/admin/export/users', `users_export_${today}.csv`, exportingUsers);
};

const exportNfcCards = () => {
  const today = new Date().toISOString().split('T')[0].replace(/-/g, '');
  downloadCsv('/admin/export/nfc-cards', `nfc_cards_export_${today}.csv`, exportingCards);
};

const exportAnalytics = () => {
  const today = new Date().toISOString().split('T')[0].replace(/-/g, '');
  const thirtyDaysAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0].replace(/-/g, '');
  downloadCsv('/admin/export/analytics', `analytics_export_${thirtyDaysAgo}_to_${today}.csv`, exportingAnalytics);
};
</script>
