<!-- pages/admin/stats.vue -->
<template>
    <div>
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-secondary-900">System Statistics</h1>
            <p class="mt-2 text-secondary-600">Comprehensive overview of platform performance and usage</p>
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
                        <p class="text-sm font-medium text-secondary-600">{{ stat.name }}</p>
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
                    <h3 class="text-lg font-medium text-secondary-900">User Statistics</h3>
                </div>
                <div class="card-body">
                    <div v-if="loading" class="flex justify-center py-8">
                        <div class="spinner"></div>
                    </div>
                    <div v-else-if="stats?.users" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-4 bg-secondary-50 rounded-lg">
                                <p class="text-2xl font-bold text-primary-600">{{ stats.users.total }}</p>
                                <p class="text-sm text-secondary-600">Total Users</p>
                            </div>
                            <div class="text-center p-4 bg-secondary-50 rounded-lg">
                                <p class="text-2xl font-bold text-success-600">{{ stats.users.active }}</p>
                                <p class="text-sm text-secondary-600">Active Subscriptions</p>
                            </div>
                        </div>
                        <div class="text-center p-4 bg-secondary-50 rounded-lg">
                            <p class="text-2xl font-bold text-warning-600">{{ stats.users.admin }}</p>
                            <p class="text-sm text-secondary-600">Admin Users</p>
                        </div>
                        <div class="text-center p-4 bg-secondary-50 rounded-lg">
                            <p class="text-2xl font-bold text-info-600">{{ stats.users.new_this_month }}</p>
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
                    <h3 class="text-lg font-medium text-secondary-900">Subscription Breakdown</h3>
                </div>
                <div class="card-body">
                    <div v-if="loading" class="flex justify-center py-8">
                        <div class="spinner"></div>
                    </div>
                    <div v-else-if="stats?.subscriptions" class="space-y-3">
                        <div v-for="(count, plan) in stats.subscriptions" :key="plan"
                            class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div :class="['w-3 h-3 rounded-full mr-3', getPlanColor(plan)]"></div>
                                <span class="text-sm font-medium text-secondary-900 capitalize">{{ plan }}</span>
                            </div>
                            <span class="text-sm font-semibold text-secondary-900">{{ count }}</span>
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
                    <h3 class="text-lg font-medium text-secondary-900">NFC Card Statistics</h3>
                </div>
                <div class="card-body">
                    <div v-if="loading" class="flex justify-center py-8">
                        <div class="spinner"></div>
                    </div>
                    <div v-else-if="stats?.nfc_cards" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-4 bg-secondary-50 rounded-lg">
                                <p class="text-2xl font-bold text-primary-600">{{ stats.nfc_cards.total }}</p>
                                <p class="text-sm text-secondary-600">Total Cards</p>
                            </div>
                            <div class="text-center p-4 bg-secondary-50 rounded-lg">
                                <p class="text-2xl font-bold text-success-600">{{ stats.nfc_cards.active }}</p>
                                <p class="text-sm text-secondary-600">Active Cards</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-4 bg-secondary-50 rounded-lg">
                                <p class="text-2xl font-bold text-warning-600">{{ stats.nfc_cards.pending }}</p>
                                <p class="text-sm text-secondary-600">Pending</p>
                            </div>
                            <div class="text-center p-4 bg-secondary-50 rounded-lg">
                                <p class="text-2xl font-bold text-info-600">{{ stats.nfc_cards.shipped }}</p>
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
                    <h3 class="text-lg font-medium text-secondary-900">Analytics Overview</h3>
                </div>
                <div class="card-body">
                    <div v-if="loading" class="flex justify-center py-8">
                        <div class="spinner"></div>
                    </div>
                    <div v-else-if="stats?.analytics" class="space-y-4">
                        <div class="text-center p-4 bg-secondary-50 rounded-lg">
                            <p class="text-2xl font-bold text-primary-600">{{ stats.analytics.total_tracks }}</p>
                            <p class="text-sm text-secondary-600">Total Analytics Events</p>
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="text-center p-3 bg-secondary-50 rounded-lg">
                                <p class="text-lg font-bold text-blue-600">{{ stats.analytics.profile_views }}</p>
                                <p class="text-xs text-secondary-600">Profile Views</p>
                            </div>
                            <div class="text-center p-3 bg-secondary-50 rounded-lg">
                                <p class="text-lg font-bold text-green-600">{{ stats.analytics.nfc_taps }}</p>
                                <p class="text-xs text-secondary-600">NFC Taps</p>
                            </div>
                            <div class="text-center p-3 bg-secondary-50 rounded-lg">
                                <p class="text-lg font-bold text-purple-600">{{ stats.analytics.link_clicks }}</p>
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
                    <h3 class="text-lg font-medium text-secondary-900">Storage & System Information</h3>
                </div>
                <div class="card-body">
                    <div v-if="loading" class="flex justify-center py-8">
                        <div class="spinner"></div>
                    </div>
                    <div v-else-if="stats?.storage" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <Icon name="heroicons:server" class="h-8 w-8 mx-auto text-primary-500 mb-2" />
                            <h4 class="text-sm font-medium text-secondary-900">Total Storage Used</h4>
                            <p class="text-lg font-semibold text-secondary-900">
                                {{ stats.storage.total_used?.formatted || '0 B' }}
                            </p>
                        </div>
                        <div class="text-center">
                            <Icon name="heroicons:photo" class="h-8 w-8 mx-auto text-primary-500 mb-2" />
                            <h4 class="text-sm font-medium text-secondary-900">Profile Images</h4>
                            <p class="text-lg font-semibold text-secondary-900">
                                {{ stats.storage.profiles || 0 }}
                            </p>
                        </div>
                        <div class="text-center">
                            <Icon name="heroicons:building-office" class="h-8 w-8 mx-auto text-primary-500 mb-2" />
                            <h4 class="text-sm font-medium text-secondary-900">Company Logos</h4>
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
                    <h3 class="text-lg font-medium text-secondary-900">Recent System Activity</h3>
                </div>
                <div class="card-body">
                    <div v-if="loading" class="flex justify-center py-8">
                        <div class="spinner"></div>
                    </div>
                    <div v-else-if="dashboard?.recent_activity?.length" class="space-y-4">
                        <div v-for="activity in dashboard.recent_activity" :key="activity.id"
                            class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div :class="['p-2 rounded-lg', getActivityIconClass(activity.action)]">
                                    <Icon :name="getActivityIcon(activity.action)" class="h-4 w-4 text-white" />
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
                        <Icon name="heroicons:clock" class="h-12 w-12 mx-auto text-secondary-300 mb-4" />
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
                        <button @click="exportUsers" class="btn btn-outline w-full">
                            <Icon name="heroicons:users" class="h-5 w-5 mr-2" />
                            Export Users
                        </button>
                        <button @click="exportNfcCards" class="btn btn-outline w-full">
                            <Icon name="heroicons:credit-card" class="h-5 w-5 mr-2" />
                            Export NFC Cards
                        </button>
                        <button @click="exportAnalytics" class="btn btn-outline w-full">
                            <Icon name="heroicons:chart-bar" class="h-5 w-5 mr-2" />
                            Export Analytics
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin',
    middleware: 'admin'
});

const adminStore = useAdminStore();
const { stats, loading, dashboard } = storeToRefs(adminStore);

// Stats cards data
const statsCards = computed(() => [
    {
        name: 'Total Users',
        value: stats.value?.users?.total || 0,
        icon: 'heroicons:users',
        bgColor: 'bg-primary-500'
    },
    {
        name: 'Active Subscriptions',
        value: stats.value?.users?.active || 0,
        icon: 'heroicons:check-circle',
        bgColor: 'bg-success-500'
    },
    {
        name: 'NFC Cards',
        value: stats.value?.nfc_cards?.total || 0,
        icon: 'heroicons:credit-card',
        bgColor: 'bg-warning-500'
    },
    {
        name: 'Total Analytics',
        value: stats.value?.analytics?.total_tracks || 0,
        icon: 'heroicons:chart-bar',
        bgColor: 'bg-info-500'
    }
]);

// Fetch data on mount
onMounted(async () => {
    await Promise.all([
        adminStore.fetchStats(),
        adminStore.fetchDashboard()
    ]);
});

// Helper functions
const getPlanColor = (plan) => {
    const colors = {
        free: 'bg-secondary-400',
        basic: 'bg-blue-400',
        pro: 'bg-purple-400',
        enterprise: 'bg-green-400'
    };
    return colors[plan] || 'bg-secondary-400';
};

const getActivityIcon = (action) => {
    const icons = {
        profile_view: 'heroicons:eye',
        nfc_tap: 'heroicons:credit-card',
        link_click: 'heroicons:cursor-arrow-rays',
        user_login: 'heroicons:arrow-right-on-rectangle'
    };
    return icons[action] || 'heroicons:information-circle';
};

const getActivityIconClass = (action) => {
    const classes = {
        profile_view: 'bg-blue-500',
        nfc_tap: 'bg-green-500',
        link_click: 'bg-purple-500',
        user_login: 'bg-orange-500'
    };
    return classes[action] || 'bg-secondary-500';
};

const getActivityDescription = (activity) => {
    const descriptions = {
        profile_view: `${activity.trackable?.name || 'Profile'} was viewed`,
        nfc_tap: `NFC tag was tapped`,
        link_click: `Link was clicked`,
        user_login: `User logged in`
    };
    return descriptions[activity.action] || 'Activity recorded';
};

const formatTimeAgo = (timestamp) => {
    if (!timestamp) return '';
    const date = new Date(timestamp);
    const now = new Date();
    const diffInMinutes = Math.floor((now - date) / (1000 * 60));

    if (diffInMinutes < 1) return 'Just now';
    if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
    if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h ago`;
    return `${Math.floor(diffInMinutes / 1440)}d ago`;
};

// Export functions
const exportUsers = () => {
    // Implementation for exporting users data
    alert('Export users functionality would be implemented here');
};

const exportNfcCards = () => {
    // Implementation for exporting NFC cards data
    alert('Export NFC cards functionality would be implemented here');
};

const exportAnalytics = () => {
    // Implementation for exporting analytics data
    alert('Export analytics functionality would be implemented here');
};
</script>
