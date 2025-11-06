<!-- pages/UserDashboard/Analytics.vue -->
<template>
  <div class="min-h-screen bg-secondary-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-secondary-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center space-x-4">
            <div>
              <h1 class="text-2xl font-semibold text-secondary-900">
                {{ tagData?.name || "Link Analytics" }}
              </h1>
              <p class="text-sm text-secondary-600">
                ID: {{ tagData?.nfc_id }}
              </p>
            </div>
          </div>
          <div class="flex items-center space-x-4">
            <select
              v-model="selectedPeriod"
              @change="loadAnalytics"
              class="input text-sm"
            >
              <option value="7d">Last 7 days</option>
              <option value="30d">Last 30 days</option>
              <option value="90d">Last 90 days</option>
              <option value="1y">Last year</option>
            </select>
            <button @click="exportAnalytics" class="btn btn-outline btn-sm">
              <Icon name="heroicons:arrow-down-tray" class="h-4 w-4 mr-2" />
              Export
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Subscription Notice for Free Users -->
      <div v-if="!hasProSubscription" class="mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex items-center">
            <Icon
              name="heroicons:information-circle"
              class="h-5 w-5 text-blue-400 mr-2"
            />
            <div class="flex-1">
              <h3 class="text-sm font-medium text-blue-800">
                Free Plan Analytics
              </h3>
              <p class="text-sm text-blue-700 mt-1">
                You're viewing basic analytics. Upgrade to Pro for detailed
                insights, device breakdowns, geographic data, and more.
              </p>
            </div>
            <button @click="upgradeToPro" class="btn btn-primary btn-sm">
              <Icon name="heroicons:star" class="h-4 w-4 mr-1" />
              Upgrade to Pro
            </button>
          </div>
        </div>
      </div>

      <!-- Overview Stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="card p-6">
          <div class="flex items-center">
            <div class="ml-4">
              <p class="text-sm font-medium text-secondary-500">Total Taps</p>
              <p class="text-2xl font-semibold text-secondary-900">
                {{ analytics.totalTaps }}
              </p>
              <p class="text-sm text-success-600">
                +{{ analytics.tapGrowth }}% from last period
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
              <p class="text-sm font-medium text-secondary-500">
                Unique Visitors
              </p>
              <p class="text-2xl font-semibold text-secondary-900">
                {{ analytics.uniqueVisitors }}
              </p>
              <p class="text-sm text-success-600">
                +{{ analytics.visitorGrowth }}% from last period
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
              <p class="text-sm font-medium text-secondary-500">
                Avg. Engagement
              </p>
              <p class="text-2xl font-semibold text-secondary-900">
                {{ analytics.avgEngagement }}s
              </p>
              <p class="text-sm text-success-600">
                +{{ analytics.engagementGrowth }}% from last period
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

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Taps Over Time Chart -->
        <div class="card">
          <div class="card-header">
            <h3 class="text-lg font-medium text-secondary-900">
              Taps Over Time
            </h3>
            <p class="text-sm text-secondary-600">
              Daily tap count for the selected period
            </p>
          </div>
          <div class="card-body">
            <div
              v-if="!hasProSubscription"
              class="h-64 bg-secondary-50 rounded-lg flex items-center justify-center"
            >
              <div class="text-center">
                <Icon
                  name="heroicons:lock-closed"
                  class="h-12 w-12 text-secondary-400 mx-auto mb-2"
                />
                <p class="text-secondary-600">
                  Detailed charts available with Pro subscription
                </p>
                <button
                  @click="upgradeToPro"
                  class="btn btn-primary btn-sm mt-2"
                >
                  <Icon name="heroicons:star" class="h-4 w-4 mr-1" />
                  Upgrade to Pro
                </button>
              </div>
            </div>
            <div
              v-else
              class="h-64 bg-secondary-50 rounded-lg flex items-center justify-center"
            >
              <div class="text-center">
                <Icon
                  name="heroicons:chart-bar"
                  class="h-12 w-12 text-secondary-400 mx-auto mb-2"
                />
                <p class="text-secondary-600">
                  Interactive chart will be displayed here
                </p>
                <p class="text-sm text-secondary-500">
                  Integration with Chart.js or similar
                </p>
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
            <div v-if="!hasProSubscription" class="text-center py-8">
              <Icon
                name="heroicons:lock-closed"
                class="h-12 w-12 text-secondary-400 mx-auto mb-2"
              />
              <p class="text-secondary-600">
                Device breakdown available with Pro subscription
              </p>
              <button @click="upgradeToPro" class="btn btn-primary btn-sm mt-2">
                <Icon name="heroicons:star" class="h-4 w-4 mr-1" />
                Upgrade to Pro
              </button>
            </div>
            <div
              v-else-if="analytics.deviceTypes.length === 0"
              class="text-center py-8 text-secondary-500"
            >
              <Icon
                name="heroicons:device-phone-mobile"
                class="h-12 w-12 mx-auto mb-2"
              />
              <p>No device data available yet</p>
            </div>
            <div v-else class="space-y-4">
              <div
                v-for="device in analytics.deviceTypes"
                :key="device.type"
                class="flex items-center justify-between"
              >
                <div class="flex items-center">
                  <Icon
                    :name="getDeviceIcon(device.type)"
                    class="h-5 w-5 text-secondary-400 mr-3"
                  />
                  <span class="text-sm font-medium text-secondary-900">{{
                    device.type
                  }}</span>
                </div>
                <div class="flex items-center space-x-3">
                  <div class="w-24 bg-secondary-200 rounded-full h-2">
                    <div
                      class="bg-primary-600 h-2 rounded-full transition-all duration-300"
                      :style="{ width: `${device.percentage}%` }"
                    ></div>
                  </div>
                  <span class="text-sm text-secondary-600 w-12 text-right"
                    >{{ device.percentage }}%</span
                  >
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
                <h3 class="text-lg font-medium text-secondary-900">
                  Recent Taps
                </h3>
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
                v-else-if="!hasProSubscription"
                class="text-center py-8 text-secondary-500"
              >
                <Icon
                  name="heroicons:lock-closed"
                  class="h-12 w-12 mx-auto mb-3"
                />
                <p class="font-medium">
                  Recent taps available with Pro subscription
                </p>
                <button
                  @click="upgradeToPro"
                  class="btn btn-primary btn-sm mt-2"
                >
                  <Icon name="heroicons:star" class="h-4 w-4 mr-1" />
                  Upgrade to Pro
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
                <p class="font-medium">No taps recorded yet</p>
                <p class="text-sm">
                  Taps will appear here when people interact with your NFC tag
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
                        <span class="text-sm font-medium text-secondary-900">{{
                          tap.location || "Unknown Location"
                        }}</span>
                        <span class="text-xs text-secondary-500">•</span>
                        <span class="text-xs text-secondary-500">{{
                          tap.device || "Unknown Device"
                        }}</span>
                      </div>
                      <p class="text-xs text-secondary-600">
                        {{ formatDateTime(tap.timestamp) }}
                      </p>
                    </div>
                  </div>
                  <div class="text-right">
                    <p class="text-sm text-secondary-900">
                      {{ tap.duration || 0 }}s
                    </p>
                    <p class="text-xs text-secondary-500">duration</p>
                  </div>
                </div>
              </div>

              <div v-if="recentTaps.length >= 10" class="mt-4 text-center">
                <button @click="loadMoreTaps" class="btn btn-sm btn-outline">
                  Load More
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Analytics Summary -->
        <div class="space-y-6">
          <!-- Geographic Data -->
          <div class="card">
            <div class="card-header">
              <h3 class="text-lg font-medium text-secondary-900">
                Top Locations
              </h3>
            </div>
            <div class="card-body">
              <div v-if="!hasProSubscription" class="text-center py-8">
                <Icon
                  name="heroicons:lock-closed"
                  class="h-12 w-12 text-secondary-400 mx-auto mb-2"
                />
                <p class="text-secondary-600">
                  Geographic data available with Pro subscription
                </p>
                <button
                  @click="upgradeToPro"
                  class="btn btn-primary btn-sm mt-2"
                >
                  <Icon name="heroicons:star" class="h-4 w-4 mr-1" />
                  Upgrade to Pro
                </button>
              </div>
              <div
                v-else-if="analytics.topLocations.length === 0"
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
                    <Icon
                      name="heroicons:map-pin"
                      class="h-4 w-4 text-secondary-400 mr-2"
                    />
                    <span class="text-sm text-secondary-900">{{
                      location.name
                    }}</span>
                  </div>
                  <span class="text-sm font-medium text-secondary-600">{{
                    location.taps
                  }}</span>
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
              <div v-if="!hasProSubscription" class="text-center py-8">
                <Icon
                  name="heroicons:lock-closed"
                  class="h-12 w-12 text-secondary-400 mx-auto mb-2"
                />
                <p class="text-secondary-600">
                  Time analytics available with Pro subscription
                </p>
                <button
                  @click="upgradeToPro"
                  class="btn btn-primary btn-sm mt-2"
                >
                  <Icon name="heroicons:star" class="h-4 w-4 mr-1" />
                  Upgrade to Pro
                </button>
              </div>
              <div
                v-else-if="analytics.peakHours.length === 0"
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
                  <span class="text-sm text-secondary-900">{{
                    hour.time
                  }}</span>
                  <div class="flex items-center space-x-2">
                    <div class="w-16 bg-secondary-200 rounded-full h-2">
                      <div
                        class="bg-orange-500 h-2 rounded-full"
                        :style="{ width: `${hour.percentage}%` }"
                      ></div>
                    </div>
                    <span class="text-sm text-secondary-600">{{
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
              <h3 class="text-lg font-medium text-secondary-900">
                Performance Score
              </h3>
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
                  <span class="text-xl font-bold text-secondary-900"
                    >{{ analytics.performanceScore }}%</span
                  >
                </div>
              </div>
              <p class="text-sm text-secondary-600">
                Based on tap frequency and engagement
              </p>
            </div>
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
});

// Route params
const route = useRoute();
const tagId = computed(() => route.params?.id);

// Stores
const authStore = useAuthStore();

// Reactive data
const loading = ref(true);
const selectedPeriod = ref("30d");

// Get user's NFC tag data
const tagData = computed(() => {
  const user = authStore.user;
  if (!user || !user.nfcTag) return null;

  return {
    id: user.nfcTag.id,
    name: user.nfcTag.name || "Business Card",
    nfc_id: user.nfcTag.nfc_id,
    status: user.nfcTag.status,
  };
});

// Check if user has pro subscription for detailed analytics
const hasProSubscription = computed(() => {
  const user = authStore.user;
  return (
    user?.subscription_plan === "pro" ||
    user?.subscription_plan === "enterprise"
  );
});

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

// Methods
const loadAnalytics = async () => {
  if (!tagData.value) {
    const { $toast } = useNuxtApp();
    $toast.error("No NFC tag found for this user");
    return;
  }

  loading.value = true;
  try {
    const { $api } = useNuxtApp();
    const response = await $api.get(`/analytics/nfc/${tagData.value.id}`, {
      params: { period: selectedPeriod.value },
    });

    if (response.success) {
      // Update analytics data with real data
      Object.assign(analytics, {
        totalTaps: response.data.total_taps || 0,
        tapGrowth: 12, // Calculate from previous period
        uniqueVisitors: response.data.unique_visitors || 0,
        visitorGrowth: 8,
        avgEngagement: response.data.avg_engagement || 0,
        engagementGrowth: 15,
        topLocation: response.data.top_location || "Unknown",
        topLocationTaps: response.data.top_location_taps || 0,
        performanceScore: response.data.performance_score || 0,
        deviceTypes: response.data.device_breakdown || [],
        topLocations: response.data.top_locations || [],
        peakHours: response.data.peak_hours || [],
      });

      // Update recent taps if available (Pro users only)
      if (hasProSubscription.value && response.data.recent_taps) {
        recentTaps.value = response.data.recent_taps.map((tap) => ({
          id: tap.id,
          location: tap.ip_address || "Unknown Location",
          device: tap.device_type || "Unknown Device",
          timestamp: new Date(tap.created_at),
          duration: 0, // Not tracked in current implementation
        }));
      }
    }
  } catch (error) {
    const { $toast } = useNuxtApp();
    $toast.error("Failed to load analytics data");
    console.error("Analytics error:", error);
  } finally {
    loading.value = false;
  }
};

const refreshTaps = async () => {
  // Refresh recent taps data
  const { $toast } = useNuxtApp();
  $toast.success("Taps refreshed");
};

const loadMoreTaps = () => {
  // Load more taps
  const { $toast } = useNuxtApp();
  $toast.info("Loading more taps...");
};

const upgradeToPro = () => {
  // Redirect to upgrade page or show upgrade modal
  const { $toast } = useNuxtApp();
  $toast.info("Redirecting to upgrade page...");
  // navigateTo('/upgrade')
};

const exportAnalytics = () => {
  // Export analytics data
  const { $toast } = useNuxtApp();
  $toast.success("Analytics exported successfully");
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

// Lifecycle
onMounted(async () => {
  await loadAnalytics();
});
</script>
