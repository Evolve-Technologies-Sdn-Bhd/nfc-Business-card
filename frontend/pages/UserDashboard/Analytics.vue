<!-- pages/UserDashboard/Analytics.vue -->
<template>
  <div class="min-h-screen bg-secondary-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-secondary-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center space-x-4">
            <div>
              <h1 class="page-title text-secondary-900">
                {{ cardTitle }}
              </h1>
              <p class="card-subtitle">{{ cardSubtitle }}</p>
            </div>
          </div>
          <div class="flex items-center space-x-4">
            <div v-if="loading" class="spinner h-5 w-5" aria-hidden="true"></div>
            <select
              v-model="selectedPeriod"
              @change="loadAnalytics"
              class="input text-input"
            >
              <option value="7d">Last 7 days</option>
              <option value="30d">Last 30 days</option>
              <option value="90d">Last 90 days</option>
              <option value="1y">Last year</option>
            </select>
            <button
              @click="exportAnalytics"
              class="btn btn-outline btn-sm"
              :disabled="!selectedCardId || loading"
            >
              <Icon name="heroicons:arrow-down-tray" class="h-4 w-4 mr-2" />
              Export
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Subscription Notice for Free Users -->
      <div v-if="!hasPremiumSubscription" class="mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex items-center">
            <Icon
              name="heroicons:information-circle"
              class="h-5 w-5 text-blue-400 mr-2"
            />
            <div class="flex-1">
              <h3 class="text-body font-medium text-blue-800">
                Free Plan Analytics
              </h3>
              <p class="text-body text-blue-700 mt-1">
                You're viewing basic analytics. Upgrade to Premium for detailed
                insights, device breakdowns, geographic data, and more.
              </p>
            </div>
            <button @click="upgradeToPremium" class="btn btn-primary btn-sm">
              <Icon name="heroicons:star" class="h-4 w-4 mr-1" />
              Upgrade to Premium
            </button>
          </div>
        </div>
      </div>

      <!-- Error state with retry -->
      <div
        v-if="error"
        class="mb-6 bg-error-50 border border-error-200 rounded-lg p-4"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <Icon
              name="heroicons:exclamation-triangle"
              class="h-5 w-5 text-error-500 mr-2"
            />
            <span class="text-error text-error-700">{{ error }}</span>
          </div>
          <button
            @click="loadAnalytics"
            class="btn btn-outline btn-sm"
            :disabled="loading"
          >
            <Icon name="heroicons:arrow-path" class="h-4 w-4 mr-1" />
            Retry
          </button>
        </div>
      </div>

      <!-- Loading skeleton (first load only) -->
      <div v-if="loading && !loaded" class="space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="n in 4" :key="`sk-kpi-${n}`" class="card p-6">
            <div class="h-4 w-24 bg-secondary-200 rounded animate-pulse mb-3"></div>
            <div class="h-7 w-20 bg-secondary-200 rounded animate-pulse mb-3"></div>
            <div class="h-3 w-28 bg-secondary-200 rounded animate-pulse"></div>
          </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div v-for="n in 2" :key="`sk-chart-${n}`" class="card">
            <div class="card-header">
              <div class="h-5 w-32 bg-secondary-200 rounded animate-pulse"></div>
            </div>
            <div class="card-body">
              <div
                class="h-56 w-full bg-secondary-100 rounded-lg animate-pulse"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Loaded content -->
      <template v-else-if="loaded">
        <!-- Overview Stats -->
        <div
          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
        >
          <div class="card p-6">
            <div class="flex items-center">
              <div class="ml-4">
                <p class="stat-label text-secondary-500">
                  Total Taps
                </p>
                <p class="stat-value text-secondary-900">
                  {{ analytics.totalTaps }}
                </p>
                <p
                  v-if="analytics.tapGrowth !== null"
                  class="text-body"
                  :class="
                    analytics.tapGrowth >= 0
                      ? 'text-success-600'
                      : 'text-error-600'
                  "
                >
                  {{ analytics.tapGrowth >= 0 ? "+" : "" }}{{ analytics.tapGrowth }}%
                  vs previous period
                </p>
              </div>
            </div>
          </div>

          <div class="card p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-blue-100 p-3 rounded-lg">
                <Icon name="heroicons:users" class="h-6 w-6 text-blue-600" />
              </div>
              <div class="ml-4">
                <p class="stat-label text-secondary-500">
                  Unique Visitors
                </p>
                <p class="stat-value text-secondary-900">
                  {{ analytics.uniqueVisitors }}
                </p>
                <p
                  v-if="analytics.visitorGrowth !== null"
                  class="text-body"
                  :class="
                    analytics.visitorGrowth >= 0
                      ? 'text-success-600'
                      : 'text-error-600'
                  "
                >
                  {{ analytics.visitorGrowth >= 0 ? "+" : ""
                  }}{{ analytics.visitorGrowth }}% vs previous period
                </p>
              </div>
            </div>
          </div>

          <div class="card p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-green-100 p-3 rounded-lg">
                <Icon name="heroicons:clock" class="h-6 w-6 text-green-600" />
              </div>
              <div class="ml-4">
                <p class="stat-label text-secondary-500">
                  Avg. Engagement
                </p>
                <p class="stat-value text-secondary-900">
                  {{ analytics.avgEngagement }}s
                </p>
              </div>
            </div>
          </div>

          <div class="card p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-orange-100 p-3 rounded-lg">
                <Icon name="heroicons:map-pin" class="h-6 w-6 text-orange-600" />
              </div>
              <div class="ml-4">
                <p class="stat-label text-secondary-500">
                  Top Location
                </p>
                <p class="stat-value text-secondary-900">
                  {{ analytics.topLocation }}
                </p>
                <p class="text-helper">
                  {{ analytics.topLocationTaps }} taps
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
          <!-- Taps Over Time Chart -->
          <div class="card">
            <div class="card-header">
              <h3 class="card-title text-secondary-900">
                Taps Over Time
              </h3>
              <p class="card-subtitle">
                Daily tap count for the selected period
              </p>
            </div>
            <div class="card-body">
              <div
                v-if="!hasPremiumSubscription"
                class="h-64 bg-secondary-50 rounded-lg flex items-center justify-center"
              >
                <div class="text-center">
                  <Icon
                    name="heroicons:lock-closed"
                    class="h-12 w-12 text-secondary-400 mx-auto mb-2"
                  />
                  <p class="text-secondary-600">
                    Detailed charts available with Premium subscription
                  </p>
                  <button
                    @click="upgradeToPremium"
                    class="btn btn-primary btn-sm mt-2"
                  >
                    <Icon name="heroicons:star" class="h-4 w-4 mr-1" />
                    Upgrade to Premium
                  </button>
                </div>
              </div>
              <AnalyticsChart
                v-else
                type="area"
                :data="dailySeries"
                :height="256"
                title="Taps over time"
                empty-message="No taps recorded for this period"
              />
            </div>
          </div>

          <!-- Device Types -->
          <div class="card">
            <div class="card-header">
              <h3 class="card-title text-secondary-900">
                Device Types
              </h3>
              <p class="card-subtitle">Breakdown by device type</p>
            </div>
            <div class="card-body">
              <div v-if="!hasPremiumSubscription" class="text-center py-8">
                <Icon
                  name="heroicons:lock-closed"
                  class="h-12 w-12 text-secondary-400 mx-auto mb-2"
                />
                <p class="text-secondary-600">
                  Device breakdown available with Premium subscription
                </p>
                <button
                  @click="upgradeToPremium"
                  class="btn btn-primary btn-sm mt-2"
                >
                  <Icon name="heroicons:star" class="h-4 w-4 mr-1" />
                  Upgrade to Premium
                </button>
              </div>
              <AnalyticsChart
                v-else
                type="bar"
                :data="deviceSeries"
                :height="256"
                title="Device breakdown"
                empty-message="No device data available yet"
              />
            </div>
          </div>
        </div>

        <!-- Detailed Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Recent Taps -->
          <div class="lg:col-span-2">
            <div class="card">
              <div class="card-header">
                <div class="flex items-center justify-between">
                  <h3 class="card-title text-secondary-900">
                    Recent Taps
                  </h3>
                  <button
                    @click="refreshTaps"
                    class="btn btn-sm btn-ghost"
                    :disabled="loading"
                  >
                    <Icon name="heroicons:arrow-path" class="h-4 w-4" />
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div
                  v-if="!hasPremiumSubscription"
                  class="text-center py-8 text-secondary-500"
                >
                  <Icon
                    name="heroicons:lock-closed"
                    class="h-12 w-12 mx-auto mb-3"
                  />
                  <p class="text-body font-medium">
                    Recent taps available with Premium subscription
                  </p>
                  <button
                    @click="upgradeToPremium"
                    class="btn btn-primary btn-sm mt-2"
                  >
                    <Icon name="heroicons:star" class="h-4 w-4 mr-1" />
                    Upgrade to Premium
                  </button>
                </div>
                <div
                  v-else-if="recentTaps.length === 0"
                  class="text-center py-8 text-secondary-500"
                >
                  <Icon
                    name="heroicons:cursor-arrow-ripple"
                    class="h-12 w-12 mx-auto mb-3"
                  />
                  <p class="text-body font-medium">No taps recorded yet</p>
                  <p class="text-helper">
                    Taps will appear here when people interact with your NFC
                    card
                  </p>
                </div>

                <div v-else class="space-y-3">
                  <div
                    v-for="tap in recentTaps"
                    :key="tap.id"
                    class="flex items-center justify-between p-3 bg-secondary-50 rounded-lg"
                  >
                    <div class="flex items-center space-x-3">
                      <div
                        class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center"
                      >
                        <Icon
                          name="heroicons:cursor-arrow-ripple"
                          class="h-5 w-5 text-purple-600"
                        />
                      </div>
                      <div>
                        <div class="flex items-center space-x-2">
                          <span class="text-body font-medium text-secondary-900">{{
                            tap.location
                          }}</span>
                          <span class="text-caption text-secondary-500">•</span>
                          <span class="text-caption text-secondary-500">{{
                            tap.device
                          }}</span>
                        </div>
                        <p class="text-caption text-secondary-600">
                          {{ formatDateTime(tap.timestamp) }}
                        </p>
                      </div>
                    </div>
                    <div class="text-right">
                      <p class="text-body text-secondary-900">
                        {{ tap.duration }}s
                      </p>
                      <p class="text-caption text-secondary-500">duration</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Analytics Summary -->
          <div class="space-y-6">
            <!-- Geographic Data -->
            <div class="card">
              <div class="card-header">
                <h3 class="card-title text-secondary-900">
                  Top Locations
                </h3>
              </div>
              <div class="card-body">
                <div v-if="!hasPremiumSubscription" class="text-center py-8">
                  <Icon
                    name="heroicons:lock-closed"
                    class="h-12 w-12 text-secondary-400 mx-auto mb-2"
                  />
                  <p class="text-secondary-600">
                    Geographic data available with Premium subscription
                  </p>
                  <button
                    @click="upgradeToPremium"
                    class="btn btn-primary btn-sm mt-2"
                  >
                    <Icon name="heroicons:star" class="h-4 w-4 mr-1" />
                    Upgrade to Premium
                  </button>
                </div>
                <div
                  v-else-if="analytics.topLocations.length === 0"
                  class="text-center py-8 text-secondary-500"
                >
                  <Icon name="heroicons:map-pin" class="h-12 w-12 mx-auto mb-2" />
                  <p class="text-body">No location data available yet</p>
                </div>
                <div v-else class="space-y-3">
                  <div
                    v-for="location in analytics.topLocations"
                    :key="location.name"
                    class="flex items-center justify-between"
                  >
                    <div class="flex items-center">
                      <Icon
                        name="heroicons:map-pin"
                        class="h-4 w-4 text-secondary-400 mr-2"
                      />
                      <span class="text-body text-secondary-900">{{
                        location.name
                      }}</span>
                    </div>
                    <span class="text-body font-medium text-secondary-600">{{
                      location.taps
                    }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Time Distribution -->
            <div class="card">
              <div class="card-header">
                <h3 class="card-title text-secondary-900">
                  Peak Hours
                </h3>
              </div>
              <div class="card-body">
                <div v-if="!hasPremiumSubscription" class="text-center py-8">
                  <Icon
                    name="heroicons:lock-closed"
                    class="h-12 w-12 text-secondary-400 mx-auto mb-2"
                  />
                  <p class="text-secondary-600">
                    Time analytics available with Premium subscription
                  </p>
                  <button
                    @click="upgradeToPremium"
                    class="btn btn-primary btn-sm mt-2"
                  >
                    <Icon name="heroicons:star" class="h-4 w-4 mr-1" />
                    Upgrade to Premium
                  </button>
                </div>
                <div
                  v-else-if="analytics.peakHours.length === 0"
                  class="text-center py-8 text-secondary-500"
                >
                  <Icon name="heroicons:clock" class="h-12 w-12 mx-auto mb-2" />
                  <p class="text-body">No time data available yet</p>
                </div>
                <div v-else class="space-y-3">
                  <div
                    v-for="hour in analytics.peakHours"
                    :key="hour.time"
                    class="flex items-center justify-between"
                  >
                    <span class="text-body text-secondary-900">{{
                      hour.time
                    }}</span>
                    <div class="flex items-center space-x-2">
                      <div class="w-16 bg-secondary-200 rounded-full h-2">
                        <div
                          class="bg-orange-500 h-2 rounded-full"
                          :style="{ width: `${hour.percentage}%` }"
                        ></div>
                      </div>
                      <span class="text-body text-secondary-600">{{
                        hour.taps
                      }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Performance Score -->
            <div class="card">
              <div class="card-header">
                <h3 class="card-title text-secondary-900">
                  Performance Score
                </h3>
              </div>
              <div class="card-body text-center">
                <div class="relative w-24 h-24 mx-auto mb-4">
                  <svg
                    class="w-24 h-24 transform -rotate-90"
                    viewBox="0 0 36 36"
                  >
                    <path
                      class="text-secondary-200"
                      stroke="currentColor"
                      stroke-width="3"
                      fill="none"
                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                    />
                    <path
                      class="text-primary-600"
                      stroke="currentColor"
                      stroke-width="3"
                      fill="none"
                      stroke-linecap="round"
                      :stroke-dasharray="`${analytics.performanceScore}, 100`"
                      d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                    />
                  </svg>
                  <div
                    class="absolute inset-0 flex items-center justify-center"
                  >
                    <span class="stat-value text-secondary-900"
                      >{{ analytics.performanceScore }}%</span
                    >
                  </div>
                </div>
                <p class="card-subtitle">
                  Based on tap frequency and engagement
                </p>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
// Layout
definePageMeta({
  layout: "user-dashboard",
});

// Route params
const route = useRoute();

// Stores
const authStore = useAuthStore();

// Reactive data
const loading = ref(true);
const loaded = ref(false);
const error = ref(null);
const selectedPeriod = ref("30d");

// ── Card selection ──────────────────────────────────────────────────────────
// CardManagement links here with ?cardId=<NfcCard id>&nfc_card_id=...&card_number=...
// Fall back to the user's first card (from /me -> user.nfcCards) when no param.
const userCards = computed(() => authStore.user?.nfcCards || []);

const selectedCard = computed(() => {
  const queryId = route.query.cardId;
  if (queryId) {
    const match = userCards.value.find((c) => String(c.id) === String(queryId));
    if (match) return match;
    return {
      id: queryId,
      card_number: route.query.card_number || null,
      nfc_card_id: route.query.nfc_card_id || null,
      card_owner: null,
      card_id: null,
    };
  }
  return userCards.value[0] || null;
});

const selectedCardId = computed(() => selectedCard.value?.id ?? null);

// Card info returned by the API (preferred for the header once loaded)
const cardInfo = ref(null);

const cardTitle = computed(
  () =>
    cardInfo.value?.card_owner ||
    selectedCard.value?.card_owner ||
    "Link Analytics"
);

const cardSubtitle = computed(() => {
  const info = cardInfo.value || selectedCard.value;
  if (!info) return "—";
  const num = info.card_number ? `Card #${info.card_number}` : null;
  const ref = info.card_id || info.nfc_card_id || null;
  return [num, ref].filter(Boolean).join(" • ") || "NFC card";
});

// Check if user has Premium subscription for detailed analytics
const hasPremiumSubscription = computed(() => {
  const user = authStore.user;
  return (
    user?.subscription_plan === "basic" ||
    user?.subscription_plan === "premium" ||
    user?.subscription_plan === "business"
  );
});

// Analytics data
const analytics = reactive({
  totalTaps: 0,
  tapGrowth: null,
  uniqueVisitors: 0,
  visitorGrowth: null,
  avgEngagement: 0,
  topLocation: "No data",
  topLocationTaps: 0,
  performanceScore: 0,
  deviceTypes: [],
  topLocations: [],
  peakHours: [],
});

// Time series for the "Taps Over Time" chart
const dailySeries = ref([]);
const recentTaps = ref([]);

// Breakdown series for the bar chart
const deviceSeries = computed(() =>
  (analytics.deviceTypes || []).map((d) => ({
    label: d.type,
    value: Number(d.count) || 0,
  }))
);

// ── Formatting helpers ──────────────────────────────────────────────────────
const MONTHS = [
  "Jan", "Feb", "Mar", "Apr", "May", "Jun",
  "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
];
const pad2 = (n) => String(n).padStart(2, "0");

const formatShortDate = (value) => {
  const date = new Date(`${value}T00:00:00`);
  if (Number.isNaN(date.getTime())) return String(value);
  return `${MONTHS[date.getMonth()]} ${date.getDate()}`;
};

// Fill missing days so the x-axis is evenly spaced across the real range.
const buildDailySeries = (dailyTaps, range) => {
  const list = Array.isArray(dailyTaps) ? dailyTaps : [];
  const start = range?.start ? new Date(`${range.start}T00:00:00`) : null;
  const end = range?.end ? new Date(`${range.end}T00:00:00`) : null;

  if (
    !start ||
    !end ||
    Number.isNaN(start.getTime()) ||
    Number.isNaN(end.getTime()) ||
    start > end
  ) {
    return list.map((d) => ({
      label: formatShortDate(d.date),
      value: Number(d.count) || 0,
    }));
  }

  const counts = new Map(list.map((d) => [d.date, Number(d.count) || 0]));
  const out = [];
  const cursor = new Date(start);
  let guard = 0;
  while (cursor <= end && guard < 400) {
    const key = `${cursor.getFullYear()}-${pad2(cursor.getMonth() + 1)}-${pad2(
      cursor.getDate()
    )}`;
    out.push({ label: formatShortDate(key), value: counts.get(key) || 0 });
    cursor.setDate(cursor.getDate() + 1);
    guard++;
  }
  return out;
};

// A growth figure is only shown when it is a real, non-zero number.
// The API returns exactly 0 when there is no previous period to compare with,
// which would be a misleading "0%" badge — so we hide it in that case.
const normalizeGrowth = (value) => {
  const n = Number(value);
  return Number.isFinite(n) && n !== 0 ? n : null;
};

// ── Data loading ────────────────────────────────────────────────────────────
const loadAnalytics = async () => {
  const { $api } = useNuxtApp();

  if (!selectedCardId.value) {
    error.value =
      "No NFC card found for your account. Add a card to see its analytics.";
    loading.value = false;
    return;
  }

  loading.value = true;
  error.value = null;

  try {
    const response = await $api.get(
      `/analytics/nfc-card/${selectedCardId.value}`,
      { params: { period: selectedPeriod.value } }
    );

    if (!response?.success) {
      throw new Error(response?.message || "Failed to load analytics");
    }

    const data = response.data || {};

    cardInfo.value = data.card_info || null;

    Object.assign(analytics, {
      totalTaps: data.total_taps || 0,
      tapGrowth: normalizeGrowth(data.tap_growth),
      uniqueVisitors: data.unique_visitors || 0,
      visitorGrowth: normalizeGrowth(data.visitor_growth),
      avgEngagement: data.avg_engagement || 0,
      topLocation: data.top_location || "No data",
      topLocationTaps: data.top_location_taps || 0,
      performanceScore: data.performance_score || 0,
      deviceTypes: data.device_breakdown || [],
      topLocations: data.top_locations || [],
      peakHours: data.peak_hours || [],
    });

    dailySeries.value = buildDailySeries(data.daily_taps, data.date_range);

    recentTaps.value = (data.recent_taps || []).map((tap) => ({
      id: tap.id,
      location: tap.city || tap.country || tap.ip_address || "Unknown location",
      device: tap.device_type || "Unknown device",
      timestamp: tap.created_at ? new Date(tap.created_at) : null,
      duration: Number(tap?.data?.duration) || 0,
    }));

    loaded.value = true;
  } catch (err) {
    if (err?.__aborted) return;
    error.value =
      err?.response?.data?.message || err?.message || "Failed to load analytics data";
    const { $toast } = useNuxtApp();
    $toast?.error?.(error.value);
  } finally {
    loading.value = false;
  }
};

const refreshTaps = () => loadAnalytics();

const upgradeToPremium = () => {
  const { $toast } = useNuxtApp();
  $toast?.info?.("Redirecting to upgrade page...");
};

const exportAnalytics = async () => {
  const { $api, $toast } = useNuxtApp();
  if (!selectedCardId.value) return;

  try {
    const blob = await $api.get(
      `/analytics/nfc-card/${selectedCardId.value}/export`,
      {
        params: { period: selectedPeriod.value },
        responseType: "blob",
      }
    );

    const url = URL.createObjectURL(
      new Blob([blob], { type: "text/csv;charset=utf-8;" })
    );
    const link = document.createElement("a");
    link.href = url;
    link.download = `card-analytics-${selectedPeriod.value}-${new Date()
      .toISOString()
      .slice(0, 10)}.csv`;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);

    $toast?.success?.("Analytics export downloaded");
  } catch (err) {
    if (err?.__aborted) return;
    $toast?.error?.("Failed to export analytics");
  }
};

const formatDateTime = (date) => {
  if (!date) return "—";
  return new Intl.DateTimeFormat("en-US", {
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
    hour12: true,
  }).format(new Date(date));
};

// Lifecycle
onMounted(async () => {
  await loadAnalytics();
});
</script>
