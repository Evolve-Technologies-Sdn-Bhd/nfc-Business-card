<!-- pages/AdminManagement/stats.vue
     Platform analytics — Ink & Brass layout.
     KPI strip → user / subscription panels → NFC / analytics panels →
     storage → activity timeline → data export. All values come from the
     admin store; the data sources and helpers are unchanged. -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Insights"
      eyebrow-icon="heroicons:chart-bar"
      title="Analytics"
      description="A comprehensive overview of platform performance, engagement and storage across NFCGo."
    />

    <!-- KPI strip -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <AdminStatTile
        v-for="stat in statsCards"
        :key="stat.name"
        :label="stat.name"
        :value="stat.value"
        :icon="stat.icon"
        :loading="loading && !stats"
      />
    </div>

    <!-- Users + subscriptions -->
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
      <!-- User Statistics -->
      <AdminSectionCard
        title="User Statistics"
        subtitle="Account base and subscription health"
      >
        <div v-if="loading" class="space-y-3" role="status" aria-busy="true">
          <span class="admin-skeleton block h-4 w-1/2"></span>
          <span class="admin-skeleton block h-4 w-2/3"></span>
          <span class="admin-skeleton block h-4 w-1/3"></span>
        </div>
        <div
          v-else-if="stats?.users"
          class="grid grid-cols-1 gap-4 sm:grid-cols-2"
        >
          <AdminStatTile
            label="Total Users"
            :value="stats.users.total"
            icon="heroicons:users"
          />
          <AdminStatTile
            label="Active Subscriptions"
            :value="stats.users.active"
            icon="heroicons:check-circle"
          />
          <AdminStatTile
            label="Admin Users"
            :value="stats.users.admin"
            icon="heroicons:shield-check"
          />
          <AdminStatTile
            label="New This Month"
            :value="stats.users.new_this_month"
            icon="heroicons:sparkles"
          />
        </div>
        <AdminEmptyState
          v-else
          icon="heroicons:users"
          title="No user statistics available"
          description="User metrics will appear once data has been collected."
        />
      </AdminSectionCard>

      <!-- Subscription Breakdown -->
      <AdminSectionCard
        title="Subscription Breakdown"
        subtitle="Distribution of plans across the platform"
      >
        <div v-if="loading" class="space-y-3" role="status" aria-busy="true">
          <span class="admin-skeleton block h-4 w-1/2"></span>
          <span class="admin-skeleton block h-4 w-2/3"></span>
          <span class="admin-skeleton block h-4 w-1/3"></span>
        </div>
        <div v-else-if="stats?.subscriptions" class="space-y-3">
          <div
            v-for="(count, plan) in stats.subscriptions"
            :key="plan"
            class="flex items-center justify-between rounded-lg border border-secondary-100 px-4 py-3"
          >
            <div class="flex items-center">
              <div
                :class="['mr-3 h-3 w-3 rounded-full', getPlanColor(plan)]"
              ></div>
              <span class="text-body font-medium capitalize text-secondary-900">
                {{ plan }}
              </span>
            </div>
            <span class="text-body font-semibold tabular-nums text-secondary-900">
              {{ count }}
            </span>
          </div>
        </div>
        <AdminEmptyState
          v-else
          icon="heroicons:chart-pie"
          title="No subscription data available"
          description="Plan distribution will appear once users subscribe."
        />
      </AdminSectionCard>
    </div>

    <!-- NFC cards + analytics -->
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
      <!-- NFC Card Statistics -->
      <AdminSectionCard
        title="NFC Card Statistics"
        subtitle="Fulfilment pipeline for physical cards"
      >
        <div v-if="loading" class="space-y-3" role="status" aria-busy="true">
          <span class="admin-skeleton block h-4 w-1/2"></span>
          <span class="admin-skeleton block h-4 w-2/3"></span>
          <span class="admin-skeleton block h-4 w-1/3"></span>
        </div>
        <div
          v-else-if="stats?.nfc_cards"
          class="grid grid-cols-1 gap-4 sm:grid-cols-2"
        >
          <AdminStatTile
            label="Total Cards"
            :value="stats.nfc_cards.total"
            icon="heroicons:credit-card"
          />
          <AdminStatTile
            label="Active Cards"
            :value="stats.nfc_cards.active"
            icon="heroicons:check-circle"
          />
          <AdminStatTile
            label="Pending"
            :value="stats.nfc_cards.pending"
            icon="heroicons:clock"
          />
          <AdminStatTile
            label="Shipped"
            :value="stats.nfc_cards.shipped"
            icon="heroicons:truck"
          />
        </div>
        <AdminEmptyState
          v-else
          icon="heroicons:credit-card"
          title="No NFC card statistics available"
          description="Card metrics will appear once cards are issued."
        />
      </AdminSectionCard>

      <!-- Analytics Overview -->
      <AdminSectionCard
        title="Analytics Overview"
        subtitle="Engagement events recorded across profiles and cards"
      >
        <div v-if="loading" class="space-y-3" role="status" aria-busy="true">
          <span class="admin-skeleton block h-4 w-1/2"></span>
          <span class="admin-skeleton block h-4 w-2/3"></span>
          <span class="admin-skeleton block h-4 w-1/3"></span>
        </div>
        <div v-else-if="stats?.analytics" class="space-y-4">
          <AdminStatTile
            label="Total Analytics Events"
            :value="stats.analytics.total_tracks"
            icon="heroicons:chart-bar"
          />
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <AdminStatTile
              label="Profile Views"
              :value="stats.analytics.profile_views"
              icon="heroicons:eye"
            />
            <AdminStatTile
              label="NFC Taps"
              :value="stats.analytics.nfc_taps"
              icon="heroicons:credit-card"
            />
            <AdminStatTile
              label="Link Clicks"
              :value="stats.analytics.link_clicks"
              icon="heroicons:cursor-arrow-rays"
            />
          </div>
        </div>
        <AdminEmptyState
          v-else
          icon="heroicons:chart-bar"
          title="No analytics data available"
          description="Engagement events will surface here."
        />
      </AdminSectionCard>
    </div>

    <!-- Storage -->
    <AdminSectionCard
      class="mt-6"
      title="Storage & System Information"
      subtitle="Disk usage across uploads and assets"
    >
      <div v-if="loading" class="space-y-3" role="status" aria-busy="true">
        <span class="admin-skeleton block h-4 w-1/2"></span>
        <span class="admin-skeleton block h-4 w-2/3"></span>
        <span class="admin-skeleton block h-4 w-1/3"></span>
      </div>
      <div
        v-else-if="stats?.storage"
        class="grid grid-cols-1 gap-4 sm:grid-cols-3"
      >
        <AdminStatTile
          label="Total Storage Used"
          :value="stats.storage.total_used?.formatted || '0 B'"
          icon="heroicons:server"
        />
        <AdminStatTile
          label="Profile Images"
          :value="stats.storage.profiles || 0"
          icon="heroicons:photo"
        />
        <AdminStatTile
          label="Company Logos"
          :value="stats.storage.logos || 0"
          icon="heroicons:building-office"
        />
      </div>
      <AdminEmptyState
        v-else
        icon="heroicons:server"
        title="No storage information available"
        description="Storage metrics will appear here."
      />
    </AdminSectionCard>

    <!-- Recent activity timeline -->
    <AdminSectionCard
      class="mt-6"
      title="Recent System Activity"
      subtitle="Latest events across the platform"
      flush
    >
      <div v-if="loading" class="p-5" role="status" aria-busy="true">
        <span class="admin-skeleton block h-4 w-1/3"></span>
        <span class="admin-skeleton mt-3 block h-4 w-2/3"></span>
      </div>
      <ul
        v-else-if="dashboard?.recent_activity?.length"
        class="divide-y divide-secondary-100"
      >
        <li
          v-for="activity in dashboard.recent_activity"
          :key="activity.id"
          class="flex items-start gap-3 px-6 py-4"
        >
          <span
            :class="['mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg', getActivityIconClass(activity.action)]"
          >
            <Icon :name="getActivityIcon(activity.action)" class="h-4 w-4 text-white" />
          </span>
          <div class="min-w-0 flex-1">
            <p class="text-body font-medium leading-snug text-secondary-900">
              {{ getActivityTitle(activity) }}
            </p>
            <p
              v-if="getActivityContext(activity)"
              class="mt-1 truncate text-caption text-secondary-500"
              :title="getActivityContext(activity)"
            >
              {{ getActivityContext(activity) }}
            </p>
            <p class="mt-1 text-caption text-secondary-400">
              {{ formatTimeAgo(activity.created_at) }}
            </p>
          </div>
        </li>
      </ul>
      <AdminEmptyState
        v-else
        icon="heroicons:clock"
        title="No recent activity"
        description="Platform events will show up here."
      />
    </AdminSectionCard>

    <!-- Data export -->
    <AdminSectionCard
      class="mt-6"
      title="Data Export"
      subtitle="Download platform data as CSV"
    >
      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <button
          type="button"
          class="admin-btn admin-btn--outline w-full"
          :disabled="exportingUsers"
          @click="exportUsers"
        >
          <Icon
            v-if="exportingUsers"
            name="heroicons:arrow-path"
            class="h-4 w-4 animate-spin"
          />
          <Icon v-else name="heroicons:users" class="h-4 w-4" />
          {{ exportingUsers ? "Exporting…" : "Export Users" }}
        </button>
        <button
          type="button"
          class="admin-btn admin-btn--outline w-full"
          :disabled="exportingCards"
          @click="exportNfcCards"
        >
          <Icon
            v-if="exportingCards"
            name="heroicons:arrow-path"
            class="h-4 w-4 animate-spin"
          />
          <Icon v-else name="heroicons:credit-card" class="h-4 w-4" />
          {{ exportingCards ? "Exporting…" : "Export NFC Cards" }}
        </button>
        <button
          type="button"
          class="admin-btn admin-btn--outline w-full"
          :disabled="exportingAnalytics"
          @click="exportAnalytics"
        >
          <Icon
            v-if="exportingAnalytics"
            name="heroicons:arrow-path"
            class="h-4 w-4 animate-spin"
          />
          <Icon v-else name="heroicons:chart-bar" class="h-4 w-4" />
          {{ exportingAnalytics ? "Exporting…" : "Export Analytics" }}
        </button>
      </div>
    </AdminSectionCard>
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
  },
  {
    name: "Active Subscriptions",
    value: stats.value?.users?.active || 0,
    icon: "heroicons:check-circle",
  },
  {
    name: "NFC Cards",
    value: stats.value?.nfc_cards?.total || 0,
    icon: "heroicons:credit-card",
  },
  {
    name: "Total Analytics",
    value: stats.value?.analytics?.total_tracks || 0,
    icon: "heroicons:chart-bar",
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
    basic: "bg-info-400",
    premium: "bg-primary-400",
    business: "bg-success-400",
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
    profile_view: "bg-info-500",
    nfc_tap: "bg-success-500",
    link_click: "bg-primary-500",

    user_logged_in: "bg-warning-500",
    user_login_success: "bg-warning-500",
    user_logged_out: "bg-secondary-500",
    user_registered: "bg-success-500",
    registration: "bg-success-500",
    user_verified: "bg-success-500",
    email_verified: "bg-info-500",
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
    order_placed: "bg-info-500",
    nfc_order_placed: "bg-info-500",
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
    template_applied: "bg-primary-500",
    theme_changed: "bg-success-500",

    admin_approved_user: "bg-success-500",
    admin_rejected_user: "bg-error-500",
    admin_verified_payment: "bg-success-500",
    admin_created_card: "bg-primary-500",
    admin_updated_config: "bg-info-500",
    admin_updated_branding: "bg-primary-500",
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
