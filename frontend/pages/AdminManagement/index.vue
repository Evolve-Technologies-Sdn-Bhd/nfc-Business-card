<!-- pages/AdminManagement/index.vue -->
<template>
  <div>
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-secondary-900">Admin Dashboard</h1>
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
        <div class="card-header">
          <h3 class="text-lg font-medium text-secondary-900">Recent Users</h3>
          <NuxtLink
            to="/AdminManagement/users"
            class="text-sm text-primary-600 hover:text-primary-500"
          >
            View all
          </NuxtLink>
        </div>
        <div class="card-body">
          <div v-if="loading" class="flex justify-center py-8">
            <div class="spinner"></div>
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
                  {{ user.subscription_plan }}
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
        <div class="card-header">
          <h3 class="text-lg font-medium text-secondary-900">
            Recent Activity
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
              <div class="flex-shrink-0">
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
                <p class="text-sm text-secondary-900">
                  {{ getActivityDescription(activity) }}
                </p>
                <p class="text-xs text-secondary-500 mt-1">
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
          <div v-if="loading" class="flex justify-center py-8">
            <div class="spinner"></div>
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
              <h4 class="text-lg font-semibold text-secondary-900 capitalize">
                {{ plan.subscription_plan }}
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
          <div v-if="loading" class="flex justify-center py-8">
            <div class="spinner"></div>
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
</template>

<script setup>
definePageMeta({
  layout: "admin-management",
  middleware: "admin",
});

const adminStore = useAdminStore();
const { dashboard, loading } = storeToRefs(adminStore);

// Stats cards data
const statsCards = computed(() => [
  {
    name: "Total Users",
    value: dashboard.value?.overview?.total_users || 0,
    icon: "heroicons:users",
    bgColor: "bg-primary-500",
    trend: "up",
    change: "+12%",
  },
  {
    name: "Total Profiles",
    value: dashboard.value?.overview?.total_profiles || 0,
    icon: "heroicons:identification",
    bgColor: "bg-success-500",
    trend: "up",
    change: "+8%",
  },
  {
    name: "NFC Cards",
    value: dashboard.value?.overview?.total_nfc_cards || 0,
    icon: "heroicons:credit-card",
    bgColor: "bg-warning-500",
    trend: "up",
    change: "+15%",
  },
  {
    name: "Total Analytics",
    value: dashboard.value?.overview?.total_analytics || 0,
    icon: "heroicons:chart-bar",
    bgColor: "bg-info-500",
    trend: "up",
    change: "+23%",
  },
]);

// Fetch dashboard data on mount
onMounted(async () => {
  await adminStore.fetchDashboard();
});

// Helper functions
const getSubscriptionBadgeClass = (plan) => {
  const classes = {
    free: "bg-secondary-100 text-secondary-800",
    basic: "bg-blue-100 text-blue-800",
    pro: "bg-purple-100 text-purple-800",
    enterprise: "bg-green-100 text-green-800",
  };
  return classes[plan] || classes.free;
};

const getActivityIcon = (action) => {
  const icons = {
    profile_view: "heroicons:eye",
    nfc_tap: "heroicons:credit-card",
    link_click: "heroicons:cursor-arrow-rays",
    user_login: "heroicons:arrow-right-on-rectangle",
  };
  return icons[action] || "heroicons:information-circle";
};

const getActivityIconClass = (action) => {
  const classes = {
    profile_view: "bg-blue-500",
    nfc_tap: "bg-green-500",
    link_click: "bg-purple-500",
    user_login: "bg-orange-500",
  };
  return classes[action] || "bg-secondary-500";
};

const getActivityDescription = (activity) => {
  const descriptions = {
    profile_view: `${activity.trackable?.name || "Profile"} was viewed`,
    nfc_tap: `NFC tag was tapped`,
    link_click: `Link was clicked`,
    user_login: `User logged in`,
  };
  return descriptions[activity.action] || "Activity recorded";
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
