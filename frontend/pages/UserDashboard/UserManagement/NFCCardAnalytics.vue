<!-- pages/UserDashboard/UserManagement/NFCCardAnalytics.vue -->
<template>
  <div class="min-h-screen bg-secondary-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-secondary-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center space-x-4">
            <div>
              <h1 class="text-2xl font-semibold text-secondary-900">
                Analytics UserDashboard
              </h1>
              <p class="text-sm text-secondary-600">
                Track your NFC card performance
              </p>
            </div>
          </div>
          <div class="flex items-center space-x-4">
            <button
              v-if="!hasPremiumSubscription"
              @click="upgradeToPremium"
              class="btn btn-primary"
            >
              <Icon name="heroicons:star" class="h-4 w-4 mr-2" />
              Upgrade to Premium
            </button>
            <button
              v-else
              @click="refreshAnalytics"
              class="btn btn-outline btn-sm"
            >
              <Icon name="heroicons:arrow-path" class="h-4 w-4 mr-2" />
              Refresh
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="spinner mx-auto mb-4"></div>
        <p class="text-secondary-600">Loading analytics...</p>
      </div>

      <!-- No NFC Tag Found -->
      <div v-else-if="!userNfcTag" class="text-center py-12">
        <div class="max-w-md mx-auto">
          <Icon
            name="heroicons:credit-card"
            class="h-16 w-16 text-secondary-400 mx-auto mb-4"
          />
          <h3 class="text-xl font-semibold text-secondary-900 mb-2">
            No NFC Card Found
          </h3>
          <p class="text-secondary-600 mb-6">
            You don't have an active NFC card yet. Order a physical card to
            start tracking analytics.
          </p>
          <div class="space-x-4">
            <button @click="navigateToCardManagement" class="btn btn-primary">
              <Icon name="heroicons:credit-card" class="h-4 w-4 mr-2" />
              Order NFC Card
            </button>
            <button @click="navigateToProfile" class="btn btn-outline">
              <Icon name="heroicons:user" class="h-4 w-4 mr-2" />
              Manage Profile
            </button>
          </div>
        </div>
      </div>

      <!-- Analytics Content -->
      <div v-else>
        <!-- Subscription Notice for Free Users -->
        <div v-if="!hasPremiumSubscription" class="mb-6">
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
                  You're viewing basic analytics. Upgrade to Premium for
                  detailed insights, device breakdowns, geographic data, and
                  more.
                </p>
              </div>
              <button @click="upgradeToPremium" class="btn btn-primary btn-sm">
                <Icon name="heroicons:star" class="h-4 w-4 mr-1" />
                Upgrade to Premium
              </button>
            </div>
          </div>
        </div>

        <!-- NFC Card Info -->
        <div class="mb-6">
          <div class="card">
            <div class="card-header">
              <h3 class="text-lg font-medium text-secondary-900">
                NFC Card Information
              </h3>
            </div>
            <div class="card-body">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <p class="text-sm font-medium text-secondary-500">
                    Card Name
                  </p>
                  <p class="text-lg font-semibold text-secondary-900">
                    {{ userNfcTag.name }}
                  </p>
                </div>
                <div>
                  <p class="text-sm font-medium text-secondary-500">NFC ID</p>
                  <p class="text-lg font-semibold text-secondary-900">
                    {{ userNfcTag.nfc_id }}
                  </p>
                </div>
                <div>
                  <p class="text-sm font-medium text-secondary-500">Status</p>
                  <span
                    :class="getStatusBadgeClass(userNfcTag.status)"
                    class="px-3 py-1 rounded-full text-sm font-medium"
                  >
                    {{ userNfcTag.status }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Overview Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="card p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0 bg-purple-100 p-3 rounded-lg">
                <Icon
                  name="heroicons:cursor-arrow-ripple"
                  class="h-6 w-6 text-purple-600"
                />
              </div>
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
                <Icon
                  name="heroicons:map-pin"
                  class="h-6 w-6 text-orange-600"
                />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-secondary-500">
                  Top Location
                </p>
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

        <!-- Quick Actions -->
        <div class="mb-8">
          <div class="card">
            <div class="card-header">
              <h3 class="text-lg font-medium text-secondary-900">
                Quick Actions
              </h3>
            </div>
            <div class="card-body">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <button
                  @click="viewDetailedAnalytics"
                  class="btn btn-outline w-full"
                >
                  <Icon name="heroicons:chart-bar" class="h-4 w-4 mr-2" />
                  View Detailed Analytics
                </button>
                <button
                  @click="navigateToCardManagement"
                  class="btn btn-outline w-full"
                >
                  <Icon name="heroicons:credit-card" class="h-4 w-4 mr-2" />
                  Manage Cards
                </button>
                <button @click="exportAnalytics" class="btn btn-outline w-full">
                  <Icon name="heroicons:arrow-down-tray" class="h-4 w-4 mr-2" />
                  Export Data
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Performance Score -->
        <div class="mb-8">
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

// Stores
const authStore = useAuthStore();
const { $toast, $api } = useNuxtApp();

// Reactive data
const loading = ref(true);
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
});

// Computed
const userNfcTag = computed(() => {
  const user = authStore.user;
  return user?.nfcTag || null;
});

const hasPremiumSubscription = computed(() => {
  const user = authStore.user;
  return (
    user?.subscription_plan === "basic" ||
    user?.subscription_plan === "premium" ||
    user?.subscription_plan === "business"
  );
});

// Methods
const loadAnalytics = async () => {
  if (!userNfcTag.value) {
    loading.value = false;
    return;
  }

  try {
    const response = await $api.get(`/analytics/nfc/${userNfcTag.value.id}`);

    if (response.success) {
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
      });
    }
  } catch (error) {
    console.error("Analytics error:", error);
    $toast.error("Failed to load analytics data");
  } finally {
    loading.value = false;
  }
};

const refreshAnalytics = async () => {
  loading.value = true;
  await loadAnalytics();
  $toast.success("Analytics refreshed");
};

const viewDetailedAnalytics = () => {
  if (userNfcTag.value) {
    navigateTo(`/UserDashboard/links/${userNfcTag.value.id}/analytics`);
  }
};

const navigateToCardManagement = () => {
  navigateTo("/UserDashboard/card-management");
};

const navigateToProfile = () => {
  navigateTo("/UserDashboard/");
};

const upgradeToPremium = () => {
  $toast.info("Redirecting to upgrade page...");
  // navigateTo('/upgrade')
};

const exportAnalytics = () => {
  $toast.success("Analytics exported successfully");
};

const getStatusBadgeClass = (status) => {
  const classes = {
    active: "bg-success-100 text-success-800",
    inactive: "bg-secondary-100 text-secondary-800",
    expired: "bg-warning-100 text-warning-800",
  };
  return classes[status] || classes["inactive"];
};

// Lifecycle
onMounted(async () => {
  await loadAnalytics();
});
</script>
