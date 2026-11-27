<!-- pages/AdminManagement/index.vue
     Administration dashboard — "Ink & Brass" information architecture.
     Overview KPIs → operational KPIs → revenue trend → pending items →
     recent users / activity → subscription mix → system health.
     Every value comes from /admin/dashboard (no mock data). -->
<template>
  <div>
    <!-- Full-page loader — only on the very first paint -->
    <div v-if="dashboard === null && !error" class="w-full">
      <NFCGoWaveLoader
        variant="wave"
        size="lg"
        theme="light"
        :showText="true"
        hintText="Loading dashboard..."
      />
    </div>

    <!-- Error state with retry -->
    <div v-else-if="error && !dashboard" class="mx-auto max-w-md py-16 text-center">
      <div class="rounded-2xl border border-secondary-200 bg-white p-8 shadow-sm">
        <span class="admin-kpi-ico mx-auto">
          <Icon name="heroicons:exclamation-triangle" class="h-6 w-6" />
        </span>
        <h3 class="mt-4 text-h6 font-semibold text-secondary-900">Couldn't load dashboard</h3>
        <p class="mt-2 text-body text-secondary-500">{{ error }}</p>
        <button
          type="button"
          class="admin-btn admin-btn--primary mt-5"
          @click="refresh"
        >
          <Icon name="heroicons:arrow-path" class="h-4 w-4" :class="{ 'animate-spin': loading }" />
          Retry
        </button>
      </div>
    </div>

    <div v-else>
      <AdminPageHeader
        eyebrow="Overview"
        eyebrow-icon="heroicons:squares-2x2"
        title="Dashboard"
        description="Monitor platform health, user growth and recent activity across NFCGo."
      >
        <template #actions>
          <div class="flex flex-wrap items-center gap-2">
            <!-- Period selector -->
            <div class="flex items-center gap-1 rounded-lg border border-secondary-200 bg-white p-0.5">
              <button
                v-for="p in periodOptions"
                :key="p.value"
                type="button"
                class="rounded-md px-2.5 py-1 text-caption font-medium transition-colors"
                :class="period === p.value
                  ? 'bg-ink text-paper'
                  : 'text-secondary-500 hover:bg-secondary-100'"
                @click="changePeriod(p.value)"
              >
                {{ p.label }}
              </button>
            </div>

            <!-- Last updated + pause toggle -->
            <div class="hidden items-center gap-2 md:flex">
              <span class="text-caption text-secondary-400">
                {{ lastUpdatedLabel }}
              </span>
              <button
                type="button"
                class="admin-btn admin-btn--ghost admin-btn--sm"
                :title="autoRefreshPaused ? 'Resume auto-refresh' : 'Pause auto-refresh'"
                @click="toggleAutoRefresh"
              >
                <Icon
                  :name="autoRefreshPaused ? 'heroicons:play' : 'heroicons:pause'"
                  class="h-4 w-4"
                />
              </button>
            </div>

            <button
              type="button"
              class="admin-btn admin-btn--outline admin-btn--sm"
              :disabled="loading"
              @click="refresh"
            >
              <Icon
                name="heroicons:arrow-path"
                class="h-4 w-4"
                :class="{ 'animate-spin': loading }"
              />
              Refresh
            </button>
            <NuxtLink to="/AdminManagement/users" class="admin-btn admin-btn--primary admin-btn--sm">
              <Icon name="heroicons:users" class="h-4 w-4" />
              Manage users
            </NuxtLink>
          </div>
        </template>
      </AdminPageHeader>

      <!-- ===================== QUICK ACTIONS ===================== -->
      <div class="mb-6 flex flex-wrap gap-2">
        <NuxtLink
          v-for="qa in quickActions"
          :key="qa.to"
          :to="qa.to"
          class="admin-btn admin-btn--outline admin-btn--sm"
        >
          <Icon :name="qa.icon" class="h-4 w-4" />
          {{ qa.label }}
        </NuxtLink>
      </div>

      <!-- ============================ KPI ROW ============================ -->
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <button
          v-for="stat in statsCards"
          :key="stat.name"
          type="button"
          class="text-left"
          @click="stat.to && navigateTo(stat.to)"
        >
          <AdminStatTile
            :label="stat.name"
            :value="stat.value"
            :icon="stat.icon"
            :trend="stat.trend"
            :trend-value="stat.change"
            :hint="stat.hint"
            :loading="loading && !dashboard"
          />
        </button>
      </div>

      <!-- ===================== OPERATIONAL KPIs ===================== -->
      <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <button
          v-for="kpi in operationalCards"
          :key="kpi.name"
          type="button"
          class="text-left"
          @click="kpi.to && navigateTo(kpi.to)"
        >
          <AdminStatTile
            :label="kpi.name"
            :value="kpi.value"
            :icon="kpi.icon"
            :trend="kpi.trend"
            :trend-value="kpi.change"
            :hint="kpi.hint"
            :loading="loading && !dashboard"
          />
        </button>
      </div>

      <!-- ===================== REVENUE TREND + PENDING ITEMS ===================== -->
      <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <!-- Revenue trend chart -->
        <AdminSectionCard
          title="Revenue Trend"
          :subtitle="`Succeeded transactions over the last ${period} days`"
          class="xl:col-span-2"
        >
          <AnalyticsChart
            type="area"
            :data="revenueChartData"
            :height="220"
            title="Revenue trend"
            :value-formatter="formatCurrency"
            empty-message="No revenue in this period yet"
          />
        </AdminSectionCard>

        <!-- Pending items widget -->
        <AdminSectionCard title="Pending Actions" subtitle="Items needing admin attention">
          <div v-if="hasPendingItems" class="space-y-3">
            <NuxtLink
              v-if="pendingVerificationCards.length"
              to="/AdminManagement/nfc-cards?status=awaiting_payment_verification"
              class="flex items-center justify-between rounded-xl border border-warning-200 bg-warning-50/60 p-3 hover:bg-warning-50"
            >
              <div class="flex items-center gap-3">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-warning-100">
                  <Icon name="heroicons:credit-card" class="h-5 w-5 text-warning-600" />
                </span>
                <div>
                  <p class="text-body font-medium text-secondary-900">Payment verifications</p>
                  <p class="text-caption text-secondary-500">Cards awaiting manual review</p>
                </div>
              </div>
              <span class="text-h6 font-bold text-warning-600">{{ pendingVerificationCards.length }}</span>
            </NuxtLink>

            <NuxtLink
              v-if="pendingRefunds.length"
              to="/AdminManagement/refunds?status=pending"
              class="flex items-center justify-between rounded-xl border border-error-200 bg-error-50/60 p-3 hover:bg-error-50"
            >
              <div class="flex items-center gap-3">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-error-100">
                  <Icon name="heroicons:arrow-uturn-left" class="h-5 w-5 text-error-600" />
                </span>
                <div>
                  <p class="text-body font-medium text-secondary-900">Pending refunds</p>
                  <p class="text-caption text-secondary-500">Refund requests to process</p>
                </div>
              </div>
              <span class="text-h6 font-bold text-error-600">{{ pendingRefunds.length }}</span>
            </NuxtLink>
          </div>
          <AdminEmptyState
            v-else
            icon="heroicons:check-badge"
            title="All caught up"
            description="No pending actions require your attention."
          />
        </AdminSectionCard>
      </div>

      <!-- ===================== USERS + ACTIVITY ===================== -->
      <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <!-- Recent users -->
        <AdminSectionCard title="Recent Users" subtitle="Newest accounts on the platform" flush>
          <template #header-actions>
            <NuxtLink to="/AdminManagement/users" class="admin-btn admin-btn--ghost admin-btn--sm">
              View all
              <Icon name="heroicons:arrow-right" class="h-4 w-4" />
            </NuxtLink>
          </template>

          <div v-if="loading && !dashboard?.recent_users" class="p-4">
            <AdminLoadingState variant="block" />
          </div>
          <ul v-else-if="dashboard?.recent_users?.length" class="divide-y divide-secondary-100">
            <li
              v-for="u in dashboard.recent_users"
              :key="u.id"
              class="flex cursor-pointer items-center gap-3 px-6 py-3.5 transition-colors hover:bg-secondary-50"
              @click="navigateTo(`/AdminManagement/users?open=${u.id}`)"
            >
              <UserAvatar
                :src="u.profile_image"
                :name="u.full_name"
                class="h-10 w-10 shrink-0 rounded-full ring-1 ring-secondary-200"
              />
              <div class="min-w-0 flex-1">
                <p class="truncate text-body font-semibold text-secondary-900">{{ u.full_name }}</p>
                <p class="truncate text-caption text-secondary-500">{{ u.email }}</p>
              </div>
              <div class="flex shrink-0 items-center gap-2">
                <AdminStatusBadge :status="u.subscription_plan" :label="planLabel(u.subscription_plan)" />
                <AdminStatusBadge v-if="u.is_admin" tone="danger" label="Admin" />
              </div>
            </li>
          </ul>
          <AdminEmptyState
            v-else
            icon="heroicons:users"
            title="No recent users"
            description="New sign-ups will appear here."
          />
        </AdminSectionCard>

        <!-- Recent activity -->
        <AdminSectionCard title="Recent Activity" subtitle="Latest events across the platform" flush>
          <template #header-actions>
            <NuxtLink to="/AdminManagement/SystemAuditLog" class="admin-btn admin-btn--ghost admin-btn--sm">
              View all
              <Icon name="heroicons:arrow-right" class="h-4 w-4" />
            </NuxtLink>
          </template>

          <div v-if="loading && !dashboard?.recent_activity" class="p-4">
            <AdminLoadingState variant="block" />
          </div>
          <ul v-else-if="dashboard?.recent_activity?.length" class="divide-y divide-secondary-100">
            <li
              v-for="activity in dashboard.recent_activity"
              :key="activity.id"
              class="flex cursor-pointer items-start gap-3 px-6 py-3.5 transition-colors hover:bg-secondary-50"
              @click="openActivityEntity(activity)"
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
                  class="mt-0.5 truncate text-caption text-secondary-500"
                  :title="getActivityContext(activity)"
                >
                  {{ getActivityContext(activity) }}
                </p>
                <p class="mt-0.5 text-caption text-secondary-400">
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
      </div>

      <!-- ============ SUBSCRIPTION MIX + SYSTEM HEALTH ============ -->
      <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <!-- Subscription breakdown -->
        <AdminSectionCard title="Subscription Breakdown" subtitle="Distribution of user plans (active / total)">
          <div v-if="subscriptionRows.length" class="space-y-4">
            <div v-for="row in subscriptionRows" :key="row.plan">
              <div class="mb-1.5 flex items-center justify-between">
                <span class="text-body font-medium text-secondary-900">{{ row.label }}</span>
                <span class="text-body tabular-nums text-secondary-600">
                  {{ row.active }}<span class="text-secondary-400">/{{ row.count }} active</span>
                  <span class="text-secondary-400"> · {{ row.pct }}%</span>
                </span>
              </div>
              <div class="h-2 w-full overflow-hidden rounded-full bg-secondary-100">
                <div
                  class="h-full rounded-full"
                  :style="{ width: row.pct + '%', background: 'linear-gradient(90deg, var(--admin-brass-soft), var(--admin-brass-deep))' }"
                ></div>
              </div>
            </div>
          </div>
          <AdminEmptyState
            v-else
            icon="heroicons:chart-pie"
            title="No subscription data"
            description="Plan distribution will appear once users subscribe."
          />
        </AdminSectionCard>

        <!-- System health -->
        <AdminSectionCard title="System Information" subtitle="Platform storage and session health">
          <div v-if="dashboard?.system_stats" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-secondary-200 bg-secondary-50/60 p-4 text-center">
              <span class="admin-kpi-ico mx-auto">
                <Icon name="heroicons:server" class="h-5 w-5" />
              </span>
              <p class="mt-2 text-caption uppercase tracking-wide text-secondary-500">Storage Used</p>
              <p class="text-h5 text-secondary-900">
                {{ dashboard.system_stats.total_storage_used?.formatted || "0 B" }}
              </p>
            </div>
            <div class="rounded-xl border border-secondary-200 bg-secondary-50/60 p-4 text-center">
              <span class="admin-kpi-ico mx-auto">
                <Icon name="heroicons:signal" class="h-5 w-5" />
              </span>
              <p class="mt-2 text-caption uppercase tracking-wide text-secondary-500">Active Sessions</p>
              <p class="text-h5 text-secondary-900">{{ dashboard.system_stats.active_sessions || 0 }}</p>
            </div>
            <div class="rounded-xl border border-secondary-200 bg-secondary-50/60 p-4 text-center">
              <span class="admin-kpi-ico mx-auto">
                <Icon name="heroicons:clock" class="h-5 w-5" />
              </span>
              <p class="mt-2 text-caption uppercase tracking-wide text-secondary-500">Last Backup</p>
              <p class="text-h5 text-secondary-900">
                {{ dashboard.system_stats.last_backup ? formatDate(dashboard.system_stats.last_backup) : "Not configured" }}
              </p>
            </div>
          </div>
          <AdminEmptyState
            v-else
            icon="heroicons:server"
            title="No system information"
            description="Health metrics will appear once available."
          />
        </AdminSectionCard>
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
const { dashboard, loading, error } = storeToRefs(adminStore);

const isMounted = ref(false);
const period = ref(30);
const autoRefreshPaused = ref(false);
const lastUpdated = ref(null);

const periodOptions = [
  { label: "7D", value: 7 },
  { label: "30D", value: 30 },
  { label: "90D", value: 90 },
];

const quickActions = [
  { label: "Add User", icon: "heroicons:user-plus", to: "/AdminManagement/users" },
  { label: "Register NFC Card", icon: "heroicons:credit-card", to: "/AdminManagement/nfc-cards" },
  { label: "Card Templates", icon: "heroicons:document-duplicate", to: "/AdminManagement/card-templates" },
  { label: "Notifications", icon: "heroicons:bell", to: "/AdminManagement/notifications" },
  { label: "Audit Log", icon: "heroicons:clipboard-document-list", to: "/AdminManagement/SystemAuditLog" },
];

const refresh = () => {
  adminStore.fetchDashboard(period.value).then(() => {
    lastUpdated.value = new Date();
  });
};

const changePeriod = (days) => {
  period.value = days;
  refresh();
};

const toggleAutoRefresh = () => {
  autoRefreshPaused.value = !autoRefreshPaused.value;
};

// Helper to build a single stat entry from real overview.trends data
const buildStatCard = (key, name, icon, to, hint = "vs previous period") => {
  const overview = dashboard.value?.overview || {};
  const trends = overview?.trends?.[key] || {};
  const direction = trends.direction || "up";
  const pct = Number(trends.change_pct ?? 0);
  const sign = pct >= 0 ? "+" : "-";
  return {
    name,
    value: overview?.[key] ?? 0,
    icon,
    trend: direction,
    change: `${sign}${Math.abs(pct)}%`,
    hint,
    to,
  };
};

// Stats tiles — values & trends come from the API (no hardcoded fakes)
const statsCards = computed(() => [
  buildStatCard("total_users", "Total Users", "heroicons:users", "/AdminManagement/users"),
  buildStatCard("total_profiles", "Total Profiles", "heroicons:identification", "/AdminManagement/profile-builder-design"),
  buildStatCard("total_nfc_cards", "NFC Cards", "heroicons:credit-card", "/AdminManagement/nfc-cards"),
  buildStatCard("total_analytics", "Total Analytics", "heroicons:chart-bar", "/AdminManagement/stats"),
]);

// Operational KPI tiles
const operationalCards = computed(() => {
  const op = dashboard.value?.operational || {};
  const revChange = Number(op.revenue_change_pct ?? 0);
  const revSign = revChange >= 0 ? "+" : "-";
  return [
    {
      name: "Revenue",
      value: formatCurrency(op.revenue_this_period || 0),
      icon: "heroicons:banknotes",
      trend: revChange >= 0 ? "up" : "down",
      change: `${revSign}${Math.abs(revChange)}%`,
      hint: "vs previous period",
      to: "/AdminManagement/invoices",
    },
    {
      name: "Pending Payments",
      value: op.pending_payments ?? 0,
      icon: "heroicons:clock",
      trend: "neutral",
      change: "",
      hint: "awaiting processing",
      to: "/AdminManagement/invoices?status=pending",
    },
    {
      name: "Pending Orders",
      value: op.pending_orders ?? 0,
      icon: "heroicons:shopping-bag",
      trend: "neutral",
      change: "",
      hint: "awaiting review",
      to: "/AdminManagement/nfc-cards?status=pending",
    },
    {
      name: "Pending Refunds",
      value: op.pending_refunds ?? 0,
      icon: "heroicons:arrow-uturn-left",
      trend: "neutral",
      change: "",
      hint: "to be processed",
      to: "/AdminManagement/refunds?status=pending",
    },
  ];
});

// Revenue chart data
const revenueChartData = computed(() =>
  (dashboard.value?.revenue_trend || []).map((r) => ({
    label: r.date ? new Date(r.date).toLocaleDateString(undefined, { month: "short", day: "numeric" }) : "",
    value: r.total || 0,
  }))
);

// Pending items
const pendingVerificationCards = computed(() =>
  dashboard.value?.pending_items?.cards_awaiting_verification || []
);
const pendingRefunds = computed(() => dashboard.value?.pending_items?.pending_refunds || []);
const hasPendingItems = computed(() =>
  pendingVerificationCards.value.length > 0 || pendingRefunds.value.length > 0
);

// Subscription breakdown with computed share percentages
const subscriptionRows = computed(() => {
  const list = dashboard.value?.subscription_breakdown || [];
  const total = list.reduce((sum, p) => sum + Number(p.count || 0), 0);
  return list.map((p) => ({
    plan: p.subscription_plan,
    label: planLabel(p.subscription_plan),
    count: Number(p.count || 0),
    active: Number(p.active || 0),
    pct: total > 0 ? Math.round((Number(p.count || 0) / total) * 100) : 0,
  }));
});

const planLabel = (plan) =>
  String(plan || "free").replace(/\b\w/g, (c) => c.toUpperCase());

const formatCurrency = (value) => {
  const num = Number(value) || 0;
  return new Intl.NumberFormat("en-MY", {
    style: "currency",
    currency: "MYR",
    maximumFractionDigits: 0,
  }).format(num);
};

const lastUpdatedLabel = computed(() => {
  if (!lastUpdated.value) return "Updated just now";
  const diff = Math.floor((Date.now() - new Date(lastUpdated.value).getTime()) / 1000);
  if (diff < 5) return "Updated just now";
  if (diff < 60) return `Updated ${diff}s ago`;
  return `Updated ${Math.floor(diff / 60)}m ago`;
});

// Fetch dashboard data on mount + set up live auto-refresh every 60s
onMounted(async () => {
  isMounted.value = true;
  await adminStore.fetchDashboard(period.value);
  lastUpdated.value = new Date();

  const intervalId = setInterval(async () => {
    if (!isMounted.value || autoRefreshPaused.value) return;
    try {
      await adminStore.fetchDashboard(period.value);
      lastUpdated.value = new Date();
    } catch (_) {
      /* ignore transient network errors during silent refresh */
    }
  }, 60000);

  onBeforeUnmount(() => {
    isMounted.value = false;
    clearInterval(intervalId);
  });
});

// Navigate to the entity related to an activity event
const openActivityEntity = (activity) => {
  const trackableType = activity.trackable_type;
  const trackableId = activity.trackable_id;
  const ownerId = activity.owner?.user_id || activity.actor?.user_id;

  // Card-related activity → NFC card management
  if (trackableType === "App\\Models\\NfcCard" || trackableType === "App\\Models\\NfcTag" || trackableType === "App\\Models\\LandingPage") {
    navigateTo("/AdminManagement/nfc-cards");
    return;
  }
  // Payment / order / refund activity → invoices
  if (["payment_created", "payment_succeeded", "payment_failed", "payment_verified", "order_placed", "nfc_order_placed", "order_approved", "order_rejected", "order_shipped", "order_delivered", "subscription_activated", "subscription_cancelled", "subscription_paused", "subscription_renewed"].includes(activity.action)) {
    navigateTo("/AdminManagement/invoices");
    return;
  }
  // Refund activity → refunds
  if (activity.action.includes("refund")) {
    navigateTo("/AdminManagement/refunds");
    return;
  }
  // Fallback: if there's an owner/actor, go to their user record
  if (ownerId) {
    navigateTo(`/AdminManagement/users?open=${ownerId}`);
    return;
  }
  // Otherwise open the audit log
  navigateTo("/AdminManagement/SystemAuditLog");
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
    profile_view: "bg-info-500",
    nfc_tap: "bg-success-500",
    link_click: "bg-primary-500",

    // Auth / session
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

    // Cards / profile
    card_activated: "bg-success-500",
    card_deactivated: "bg-error-500",
    card_ordered: "bg-warning-500",
    profile_updated: "bg-info-500",
    profile_published: "bg-success-500",
    profile_unpublished: "bg-secondary-500",
    profile_builder_accessed: "bg-primary-500",
    template_applied: "bg-primary-500",
    theme_changed: "bg-success-500",

    // Admin / system
    admin_approved_user: "bg-success-500",
    admin_rejected_user: "bg-error-500",
    admin_verified_payment: "bg-success-500",
    admin_created_card: "bg-primary-500",
    admin_updated_config: "bg-info-500",
    admin_updated_branding: "bg-primary-500",
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

// Mask IP addresses — show only the first two octets for privacy
const maskIp = (ip) => {
  if (!ip) return null;
  const parts = String(ip).split(".");
  if (parts.length === 4) {
    return `${parts[0]}.${parts[1]}.x.x`;
  }
  // IPv6: show only the first 2 groups
  const v6parts = String(ip).split(":");
  if (v6parts.length >= 3) {
    return `${v6parts[0]}:${v6parts[1]}::`;
  }
  return "x.x.x.x";
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
  if (activity.ip_address) parts.push(`IP ${maskIp(activity.ip_address)}`);
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
