<!-- pages/UserDashboard/UserManagement/PremiumPlanUser/PremiumAnalytics.vue -->
<!-- Premium Plan Analytics - Same as Business but without Employee filter -->
<template>
  <div>
    <!-- Header -->
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-3xl font-bold text-secondary-900">Analytics</h1>
          <p class="mt-2 text-secondary-600">
            Track engagement and performance metrics
          </p>
        </div>
        <div class="mt-4 sm:mt-0">
          <button @click="exportAnalytics" class="btn btn-primary">
            <Icon name="heroicons:arrow-down-tray" class="h-5 w-5 mr-2" />
            Export Data
          </button>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- NFC Card Selector -->
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2">Select Card</label>
            <select
              v-model="selectedCardId"
              @change="onCardChange"
              class="input w-full"
            >
              <option value="all">📊 All Cards (Total Overview)</option>
              <option
                v-for="card in nfcCards"
                :key="card.id"
                :value="card.id"
              >
                {{ card.nfc_card_id || `Card #${card.id}` }} - {{ card.card_owner || 'My Card' }}
              </option>
            </select>
          </div>
          <!-- Period Selector -->
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2">Time Period</label>
            <select
              v-model="selectedPeriod"
              @change="loadAnalytics"
              class="input w-full"
            >
              <option value="7d">Last 7 days</option>
              <option value="30d">Last 30 days</option>
              <option value="90d">Last 90 days</option>
              <option value="1y">Last year</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0 bg-purple-100 p-3 rounded-lg">
            <Icon name="heroicons:cursor-arrow-ripple" class="h-6 w-6 text-purple-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-secondary-500">Total Taps</p>
            <p class="text-2xl font-semibold text-secondary-900">
              {{ analytics.totalTaps }}
            </p>
            <p :class="analytics.tapGrowth >= 0 ? 'text-sm text-success-600' : 'text-sm text-red-600'">
              {{ analytics.tapGrowth >= 0 ? '+' : '' }}{{ analytics.tapGrowth }}% from last period
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
            <p class="text-sm font-medium text-secondary-500">Unique Visitors</p>
            <p class="text-2xl font-semibold text-secondary-900">
              {{ analytics.uniqueVisitors }}
            </p>
            <p :class="analytics.visitorGrowth >= 0 ? 'text-sm text-success-600' : 'text-sm text-red-600'">
              {{ analytics.visitorGrowth >= 0 ? '+' : '' }}{{ analytics.visitorGrowth }}% from last period
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
            <p class="text-sm font-medium text-secondary-500">Avg. Engagement</p>
            <p class="text-2xl font-semibold text-secondary-900">
              {{ analytics.avgEngagement }}s
            </p>
            <p :class="analytics.engagementGrowth >= 0 ? 'text-sm text-success-600' : 'text-sm text-red-600'">
              {{ analytics.engagementGrowth >= 0 ? '+' : '' }}{{ analytics.engagementGrowth }}% from last period
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
            <p class="text-sm font-medium text-secondary-500">Top Location</p>
            <p class="text-lg font-semibold text-secondary-900">
              {{ analytics.topLocation }}
            </p>
            <p class="text-sm text-secondary-600">
              {{ analytics.topLocationTaps }} taps
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
      <!-- Taps Over Time Chart -->
      <div class="card">
        <div class="card-header">
          <h3 class="text-lg font-medium text-secondary-900">Taps Over Time</h3>
          <p class="text-sm text-secondary-600">Daily tap count for the selected period</p>
        </div>
        <div class="card-body">
          <div v-if="dailyTaps.length === 0" class="h-64 bg-secondary-50 rounded-lg flex items-center justify-center">
            <div class="text-center">
              <Icon name="heroicons:chart-bar" class="h-12 w-12 text-secondary-400 mx-auto mb-2" />
              <p class="text-secondary-600">No tap data available yet</p>
              <p class="text-sm text-secondary-500">Taps will appear here once recorded</p>
            </div>
          </div>
          <div v-else class="h-64">
            <!-- Simple Bar Chart -->
            <div class="flex items-end justify-between h-48 gap-1">
              <div
                v-for="(day, index) in dailyTaps"
                :key="index"
                class="flex-1 flex flex-col items-center group"
              >
                <div
                  class="w-full bg-primary-500 rounded-t transition-all hover:bg-primary-600 cursor-pointer relative"
                  :style="{ height: `${getBarHeight(day.count)}%`, minHeight: day.count > 0 ? '4px' : '0' }"
                >
                  <!-- Tooltip -->
                  <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-secondary-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                    {{ day.count }} taps
                  </div>
                </div>
              </div>
            </div>
            <!-- X-axis labels -->
            <div class="flex justify-between mt-2 text-xs text-secondary-500">
              <span>{{ formatChartDate(dailyTaps[0]?.date) }}</span>
              <span v-if="dailyTaps.length > 1">{{ formatChartDate(dailyTaps[dailyTaps.length - 1]?.date) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Device Types -->
      <div class="card">
        <div class="card-header">
          <h3 class="text-lg font-medium text-secondary-900">Device Types</h3>
          <p class="text-sm text-secondary-600">Breakdown by device type</p>
        </div>
        <div class="card-body">
          <div
            v-if="analytics.deviceTypes.length === 0"
            class="text-center py-8 text-secondary-500"
          >
            <Icon name="heroicons:device-phone-mobile" class="h-12 w-12 mx-auto mb-2" />
            <p>No device data available yet</p>
          </div>
          <div v-else class="space-y-4">
            <div
              v-for="device in analytics.deviceTypes"
              :key="device.type"
              class="flex items-center justify-between"
            >
              <div class="flex items-center">
                <Icon :name="getDeviceIcon(device.type)" class="h-5 w-5 text-secondary-400 mr-3" />
                <span class="text-sm font-medium text-secondary-900">{{ device.type }}</span>
              </div>
              <div class="flex items-center space-x-3">
                <div class="w-24 bg-secondary-200 rounded-full h-2">
                  <div
                    class="bg-primary-600 h-2 rounded-full transition-all duration-300"
                    :style="{ width: `${device.percentage}%` }"
                  ></div>
                </div>
                <span class="text-sm text-secondary-600 w-12 text-right">{{ device.percentage }}%</span>
              </div>
            </div>
          </div>
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
              <h3 class="text-lg font-medium text-secondary-900">Recent Taps</h3>
              <button @click="refreshTaps" class="btn btn-sm btn-ghost">
                <Icon name="heroicons:arrow-path" class="h-4 w-4" />
              </button>
            </div>
          </div>
          <div class="card-body">
            <div v-if="loading" class="flex items-center justify-center py-8">
              <div class="spinner mr-3"></div>
              <span class="text-secondary-600">Loading recent taps...</span>
            </div>

            <div
              v-else-if="recentTaps.length === 0"
              class="text-center py-8 text-secondary-500"
            >
              <Icon name="heroicons:cursor-arrow-ripple" class="h-12 w-12 mx-auto mb-3" />
              <p class="font-medium">No taps recorded yet</p>
              <p class="text-sm">Taps will appear here when people interact with your NFC tag</p>
            </div>

            <div v-else class="space-y-3">
              <div
                v-for="tap in recentTaps"
                :key="tap.id"
                class="flex items-center justify-between p-3 bg-secondary-50 rounded-lg"
              >
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                    <Icon name="heroicons:cursor-arrow-ripple" class="h-5 w-5 text-purple-600" />
                  </div>
                  <div>
                    <div class="flex items-center space-x-2">
                      <span class="text-sm font-medium text-secondary-900">{{ tap.location || "Unknown Location" }}</span>
                      <span class="text-xs text-secondary-500">•</span>
                      <span class="text-xs text-secondary-500">{{ tap.device || "Unknown Device" }}</span>
                    </div>
                    <p class="text-xs text-secondary-600">{{ formatDateTime(tap.timestamp) }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="text-sm text-secondary-900">{{ tap.duration || 0 }}s</p>
                  <p class="text-xs text-secondary-500">duration</p>
                </div>
              </div>
            </div>

            <div v-if="recentTaps.length >= 10" class="mt-4 text-center">
              <button @click="loadMoreTaps" class="btn btn-sm btn-outline">Load More</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Analytics Summary -->
      <div class="space-y-6">
        <!-- Geographic Data -->
        <div class="card">
          <div class="card-header">
            <h3 class="text-lg font-medium text-secondary-900">Top Locations</h3>
          </div>
          <div class="card-body">
            <div
              v-if="analytics.topLocations.length === 0"
              class="text-center py-8 text-secondary-500"
            >
              <Icon name="heroicons:map-pin" class="h-12 w-12 mx-auto mb-2" />
              <p>No location data available yet</p>
            </div>
            <div v-else class="space-y-3">
              <div
                v-for="location in analytics.topLocations"
                :key="location.name"
                class="flex items-center justify-between"
              >
                <div class="flex items-center">
                  <Icon name="heroicons:map-pin" class="h-4 w-4 text-secondary-400 mr-2" />
                  <span class="text-sm text-secondary-900">{{ location.name }}</span>
                </div>
                <span class="text-sm font-medium text-secondary-600">{{ location.taps }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Time Distribution -->
        <div class="card">
          <div class="card-header">
            <h3 class="text-lg font-medium text-secondary-900">Peak Hours</h3>
          </div>
          <div class="card-body">
            <div
              v-if="analytics.peakHours.length === 0"
              class="text-center py-8 text-secondary-500"
            >
              <Icon name="heroicons:clock" class="h-12 w-12 mx-auto mb-2" />
              <p>No time data available yet</p>
            </div>
            <div v-else class="space-y-3">
              <div
                v-for="hour in analytics.peakHours"
                :key="hour.time"
                class="flex items-center justify-between"
              >
                <span class="text-sm text-secondary-900">{{ hour.time }}</span>
                <div class="flex items-center space-x-2">
                  <div class="w-16 bg-secondary-200 rounded-full h-2">
                    <div
                      class="bg-orange-500 h-2 rounded-full"
                      :style="{ width: `${hour.percentage}%` }"
                    ></div>
                  </div>
                  <span class="text-sm text-secondary-600">{{ hour.taps }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Performance Score -->
        <div class="card">
          <div class="card-header">
            <h3 class="text-lg font-medium text-secondary-900">Performance Score</h3>
          </div>
          <div class="card-body text-center">
            <div class="relative w-24 h-24 mx-auto mb-4">
              <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 36 36">
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
              <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-xl font-bold text-secondary-900">{{ analytics.performanceScore }}%</span>
              </div>
            </div>
            <p class="text-sm text-secondary-600">Based on tap frequency and engagement</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Layout
definePageMeta({
  layout: "user-dashboard",
  middleware: ["auth"],
});

// Route, Stores, API
const router = useRouter();
const authStore = useAuthStore();
const { $api, $toast } = useNuxtApp();

// Reactive data
const loading = ref(true);
const selectedPeriod = ref("30d");
const selectedCardId = ref("all");
const nfcCards = ref([]);

// Analytics data
const analytics = reactive({
  totalTaps: 0,
  tapGrowth: 0,
  uniqueVisitors: 0,
  visitorGrowth: 0,
  avgEngagement: 0,
  engagementGrowth: 0,
  topLocation: "Unknown",
  topLocationTaps: 0,
  performanceScore: 0,
  deviceTypes: [],
  topLocations: [],
  peakHours: [],
});

const recentTaps = ref([]);
const dailyTaps = ref([]);

// Methods
const loadNfcCards = async () => {
  try {
    const response = await $api.get("/nfc-cards");
    if (response.success) {
      nfcCards.value = response.nfc_cards || [];
      // Load analytics for default "All Cards" selection
      if (nfcCards.value.length > 0) {
        await loadAnalytics();
      } else {
        loading.value = false;
      }
    }
  } catch (error) {
    console.error("Failed to load NFC cards:", error);
    loading.value = false;
  }
};

const onCardChange = async () => {
  if (selectedCardId.value) {
    await loadAnalytics();
  }
};

const loadAnalytics = async () => {
  if (!selectedCardId.value) {
    return;
  }

  loading.value = true;
  try {
    const params = {
      period: selectedPeriod.value,
    };

    // Determine API endpoint based on card selection
    const endpoint = selectedCardId.value === "all" 
      ? `/analytics/user/overview`  // All cards overview for this user
      : `/analytics/nfc-card/${selectedCardId.value}`; // Single card

    const response = await $api.get(endpoint, { params });

    if (response.success) {
      // Update analytics data with real data
      Object.assign(analytics, {
        totalTaps: response.data.total_taps || 0,
        tapGrowth: response.data.tap_growth || 0,
        uniqueVisitors: response.data.unique_visitors || 0,
        visitorGrowth: response.data.visitor_growth || 0,
        avgEngagement: response.data.avg_engagement || 0,
        engagementGrowth: response.data.engagement_growth || 0,
        topLocation: response.data.top_location || "Unknown",
        topLocationTaps: response.data.top_location_taps || 0,
        performanceScore: response.data.performance_score || 0,
        deviceTypes: response.data.device_breakdown || [],
        topLocations: response.data.top_locations || [],
        peakHours: response.data.peak_hours || [],
      });

      // Update recent taps
      if (response.data.recent_taps) {
        recentTaps.value = response.data.recent_taps.map((tap) => ({
          id: tap.id,
          location: tap.city || tap.country || tap.ip_address || "Unknown Location",
          device: tap.device_type ? tap.device_type.charAt(0).toUpperCase() + tap.device_type.slice(1) : "Unknown Device",
          timestamp: new Date(tap.created_at),
          duration: tap.data?.duration || 0,
          browser: tap.browser || "Unknown",
          platform: tap.platform || "Unknown",
        }));
      }

      // Update daily taps for chart
      if (response.data.daily_taps) {
        dailyTaps.value = response.data.daily_taps;
      }
    }
  } catch (error) {
    console.error("Analytics error:", error);
  } finally {
    loading.value = false;
  }
};

const refreshTaps = async () => {
  await loadAnalytics();
  $toast.success("Analytics refreshed");
};

const loadMoreTaps = async () => {
  $toast.info("Loading more taps...");
  // Implement pagination for recent taps
};

const exportAnalytics = async () => {
  if (!selectedCardId.value) {
    $toast.error("Please select a card first");
    return;
  }

  try {
    $toast.info("Preparing export...");

    // Build params
    const params = { period: selectedPeriod.value };

    // Determine endpoint based on card selection
    const endpoint = selectedCardId.value === "all"
      ? `/analytics/user/overview/export`
      : `/analytics/nfc-card/${selectedCardId.value}/export`;

    // Make authenticated API call (returns raw text/csv)
    const response = await $api.get(endpoint, { 
      params,
      responseType: 'text'
    });

    // Create blob from CSV data
    const csvData = typeof response === 'string' ? response : response.data || response;
    const blob = new Blob([csvData], { type: 'text/csv;charset=utf-8;' });
    
    // Create download link
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `analytics_${selectedCardId.value === 'all' ? 'all_cards' : selectedCardId.value}_${new Date().toISOString().slice(0, 10)}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);

    $toast.success("Analytics exported successfully!");
  } catch (error) {
    console.error("Export error:", error);
    $toast.error(error.message || "Failed to export analytics");
  }
};

const getDeviceIcon = (deviceType) => {
  const icons = {
    Mobile: "heroicons:device-phone-mobile",
    Desktop: "heroicons:computer-desktop",
    Tablet: "heroicons:device-tablet",
  };
  return icons[deviceType] || "heroicons:device-phone-mobile";
};

const formatDateTime = (date) => {
  return new Intl.DateTimeFormat("en-US", {
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
    hour12: true,
  }).format(new Date(date));
};

const getBarHeight = (count) => {
  if (dailyTaps.value.length === 0) return 0;
  const maxCount = Math.max(...dailyTaps.value.map((d) => d.count), 1);
  return Math.round((count / maxCount) * 100);
};

const formatChartDate = (dateStr) => {
  if (!dateStr) return "";
  const date = new Date(dateStr);
  return new Intl.DateTimeFormat("en-US", {
    month: "short",
    day: "numeric",
  }).format(date);
};

// Lifecycle
onMounted(async () => {
  // Check if user is Premium Plan
  const user = authStore.user;
  if (user?.subscription_plan !== "premium") {
    $toast.error("This page is only for Premium Plan users");
    router.push("/UserDashboard");
    return;
  }

  // Load NFC cards for the current user
  await loadNfcCards();
});
</script>
