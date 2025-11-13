<!-- pages/UserDashboard/Notifications.vue -->
<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b sticky top-0 z-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <h1 class="text-2xl font-semibold text-gray-900">Notifications</h1>
          <div class="flex items-center space-x-3">
            <!-- Mark All as Read Button (more prominent) -->
            <button
              v-if="unreadCount > 0"
              @click="handleMarkAllAsRead"
              class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center shadow-sm"
            >
              <Icon name="heroicons:check-circle" class="w-4 h-4 mr-2" />
              Mark all as read ({{ unreadCount }})
            </button>
            <button
              @click="loadNotifications()"
              class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center"
            >
              <Icon name="heroicons:arrow-path" class="w-4 h-4 mr-2" />
              Refresh
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Total</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ notifications.length }}
              </p>
            </div>
            <Icon
              name="heroicons:bell"
              class="w-10 h-10 text-gray-400 opacity-50"
            />
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Unread</p>
              <p class="text-2xl font-bold text-orange-600">
                {{ unreadCount }}
              </p>
            </div>
            <Icon
              name="heroicons:envelope"
              class="w-10 h-10 text-orange-400 opacity-50"
            />
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Read</p>
              <p class="text-2xl font-bold text-green-600">
                {{ notifications.length - unreadCount }}
              </p>
            </div>
            <Icon
              name="heroicons:check-circle"
              class="w-10 h-10 text-green-400 opacity-50"
            />
          </div>
        </div>
      </div>

      <!-- Search and Filters -->
      <div class="bg-white rounded-lg shadow mb-6 p-4">
        <div class="flex flex-col md:flex-row gap-4">
          <!-- Search Box -->
          <div class="flex-1">
            <div class="relative">
              <Icon
                name="heroicons:magnifying-glass"
                class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400"
              />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search notifications..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
              <button
                v-if="searchQuery"
                @click="searchQuery = ''"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>
          </div>

          <!-- Category Filter -->
          <div class="w-full md:w-48">
            <select
              v-model="categoryFilter"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="all">All Categories</option>
              <option value="system">System</option>
              <option value="security">Security</option>
              <option value="activity">Activity</option>
              <option value="payment">Payment</option>
              <option value="nfc">NFC Cards</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="bg-white rounded-lg shadow mb-6">
        <div class="border-b border-gray-200 flex items-center justify-between">
          <nav class="flex space-x-8 px-6" aria-label="Tabs">
            <button
              @click="filterTab = 'all'"
              :class="[
                'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                filterTab === 'all'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              ]"
            >
              All Notifications
            </button>
            <button
              @click="filterTab = 'unread'"
              :class="[
                'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                filterTab === 'unread'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              ]"
            >
              Unread
              <span
                v-if="unreadCount > 0"
                class="ml-2 bg-orange-100 text-orange-600 py-0.5 px-2 rounded-full text-xs"
              >
                {{ unreadCount }}
              </span>
            </button>
            <button
              @click="filterTab = 'read'"
              :class="[
                'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                filterTab === 'read'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              ]"
            >
              Read
            </button>
          </nav>

          <!-- Quick Actions -->
          <div class="flex items-center space-x-2 px-6">
            <button
              v-if="filteredNotifications.some((n) => !n.is_read)"
              @click="handleMarkFilteredAsRead"
              class="px-3 py-1.5 text-xs bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 transition-colors font-medium flex items-center"
              :title="`Mark ${
                filteredNotifications.filter((n) => !n.is_read).length
              } visible notification(s) as read`"
            >
              <Icon name="heroicons:check" class="w-3.5 h-3.5 mr-1" />
              Mark visible as read
            </button>
          </div>
        </div>
      </div>

      <!-- Notifications List -->
      <div class="space-y-3">
        <!-- Loading State -->
        <div v-if="loading" class="bg-white rounded-lg shadow p-8 text-center">
          <div
            class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-gray-300 border-t-blue-600"
          ></div>
          <p class="text-gray-600 mt-3">Loading notifications...</p>
        </div>

        <!-- Empty State -->
        <div
          v-else-if="filteredNotifications.length === 0"
          class="bg-white rounded-lg shadow p-12 text-center"
        >
          <Icon
            name="heroicons:bell-slash"
            class="mx-auto h-16 w-16 text-gray-300"
          />
          <h3 class="mt-4 text-lg font-medium text-gray-900">
            No notifications
          </h3>
          <p class="mt-2 text-sm text-gray-500">
            {{ getEmptyStateMessage() }}
          </p>
        </div>

        <!-- Notification Items -->
        <div
          v-for="notification in filteredNotifications"
          :key="notification.id"
          @click="handleNotificationClick(notification)"
          class="bg-white rounded-lg shadow hover:shadow-md transition-all cursor-pointer overflow-hidden"
          :class="{ 'border-l-4 border-blue-500': !notification.is_read }"
        >
          <div class="p-4">
            <div class="flex items-start justify-between">
              <div class="flex items-start flex-1">
                <span class="text-3xl mr-4 flex-shrink-0">
                  {{ getNotificationIcon(notification.type) }}
                </span>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between mb-1">
                    <h3
                      class="text-base font-semibold text-gray-900"
                      :class="{ 'font-bold': !notification.is_read }"
                    >
                      {{ notification.title }}
                    </h3>
                    <span
                      v-if="
                        notification.priority === 'urgent' ||
                        notification.priority === 'high'
                      "
                      class="ml-2 px-2 py-1 text-xs font-medium rounded-full flex-shrink-0"
                      :class="getPriorityClass(notification.priority)"
                    >
                      {{ notification.priority }}
                    </span>
                  </div>
                  <p class="text-sm text-gray-700 mb-2">
                    {{ notification.message }}
                  </p>
                  <div
                    class="flex items-center justify-between text-xs text-gray-500"
                  >
                    <span>{{ getTimeAgo(notification.created_at) }}</span>
                    <div class="flex items-center space-x-2">
                      <span
                        v-if="notification.is_read"
                        class="flex items-center text-green-600"
                      >
                        <Icon
                          name="heroicons:check-circle"
                          class="w-4 h-4 mr-1"
                        />
                        Read
                      </span>
                      <span v-else class="flex items-center text-blue-600">
                        <Icon name="heroicons:envelope" class="w-4 h-4 mr-1" />
                        Unread
                      </span>
                    </div>
                  </div>
                  <div
                    v-if="notification.action_url"
                    class="mt-3 flex items-center text-sm text-blue-600 hover:text-blue-700"
                  >
                    <Icon name="heroicons:arrow-right" class="w-4 h-4 mr-1" />
                    {{ notification.action_text || "View Details" }}
                  </div>
                </div>
              </div>
              <div class="ml-4 flex items-center space-x-2">
                <!-- Mark as Read Button (only for unread) -->
                <button
                  v-if="!notification.is_read"
                  @click.stop="markAsRead(notification.id)"
                  class="text-blue-500 hover:text-blue-700 transition-colors"
                  title="Mark as read"
                >
                  <Icon name="heroicons:check-circle" class="h-5 w-5" />
                </button>
                <!-- Delete Button -->
                <button
                  @click.stop="handleDeleteNotification(notification.id)"
                  class="text-gray-400 hover:text-red-600 transition-colors"
                  title="Delete notification"
                >
                  <Icon name="heroicons:trash" class="h-5 w-5" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Load More Button -->
      <div
        v-if="
          filteredNotifications.length > 0 && filteredNotifications.length >= 50
        "
        class="mt-6 text-center"
      >
        <button
          @click="loadNotifications(100)"
          class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          Load More
        </button>
      </div>

      <!-- Clear All Read Button -->
      <div
        v-if="notifications.length > 0 && notifications.some((n) => n.is_read)"
        class="mt-6 text-center"
      >
        <button
          @click="handleDeleteAllRead"
          class="px-6 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors"
        >
          Clear All Read Notifications
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: "user-dashboard",
  middleware: "auth",
});

const { $toast } = useNuxtApp();

// Use notifications composable
const {
  notifications,
  unreadCount,
  loading,
  loadNotifications,
  markAsRead,
  markAllAsRead,
  deleteNotification,
  deleteAllRead,
  getTimeAgo,
  getNotificationIcon,
  getNotificationCategory,
  getCategoryInfo,
  getPriorityClass,
} = useNotifications();

// Filter states
const filterTab = ref("all"); // all, unread, read
const categoryFilter = ref("all"); // all, system, security, activity, payment, nfc, other
const searchQuery = ref("");
const selectedNotifications = ref([]);

// Computed filtered notifications
const filteredNotifications = computed(() => {
  let result = notifications.value;

  // Filter by read/unread status
  if (filterTab.value === "unread") {
    result = result.filter((n) => !n.is_read);
  } else if (filterTab.value === "read") {
    result = result.filter((n) => n.is_read);
  }

  // Filter by category
  if (categoryFilter.value !== "all") {
    result = result.filter(
      (n) => getNotificationCategory(n.type) === categoryFilter.value
    );
  }

  // Filter by search query
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(
      (n) =>
        n.title.toLowerCase().includes(query) ||
        n.message.toLowerCase().includes(query)
    );
  }

  return result;
});

// Handlers
const handleNotificationClick = async (notification) => {
  // Mark as read if unread
  if (!notification.is_read) {
    await markAsRead(notification.id);
  }

  // Navigate to action URL if exists
  if (notification.action_url) {
    navigateTo(notification.action_url);
  }
};

const handleMarkAllAsRead = async () => {
  await markAllAsRead();
  $toast.success("All notifications marked as read");
};

const handleMarkFilteredAsRead = async () => {
  const unreadFiltered = filteredNotifications.value.filter((n) => !n.is_read);

  if (unreadFiltered.length === 0) {
    $toast.info("No unread notifications to mark");
    return;
  }

  // Mark each filtered unread notification as read
  const promises = unreadFiltered.map((n) => markAsRead(n.id));
  await Promise.all(promises);

  $toast.success(`Marked ${unreadFiltered.length} notification(s) as read`);
};

const handleDeleteNotification = async (notificationId) => {
  if (!confirm("Delete this notification?")) {
    return;
  }
  await deleteNotification(notificationId);
  $toast.success("Notification deleted");
};

const handleDeleteAllRead = async () => {
  if (!confirm("Delete all read notifications? This cannot be undone.")) {
    return;
  }
  await deleteAllRead();
  $toast.success("All read notifications cleared");
};

const getEmptyStateMessage = () => {
  if (searchQuery.value.trim()) {
    return "No notifications match your search";
  }
  if (categoryFilter.value !== "all") {
    const categoryInfo = getCategoryInfo(categoryFilter.value);
    return `No ${categoryInfo.label.toLowerCase()} notifications`;
  }
  if (filterTab.value === "unread") {
    return "You have no unread notifications";
  } else if (filterTab.value === "read") {
    return "You have no read notifications";
  }
  return "You have no notifications yet";
};

// Load notifications on mount
onMounted(() => {
  loadNotifications(50);
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
