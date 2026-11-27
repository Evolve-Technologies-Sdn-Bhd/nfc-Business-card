<!-- pages/UserDashboard/Notifications.vue -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Account"
      eyebrow-icon="heroicons:bell"
      title="Notifications"
      description="Card status, payments, security alerts and announcements, all in one place."
    >
      <template #actions>
        <button
          v-if="unreadCount > 0"
          type="button"
          @click="handleMarkAllAsRead"
          class="admin-btn admin-btn--primary"
        >
          <Icon name="heroicons:check-circle" class="h-4 w-4" />
          Mark all as read
          <span class="ml-1 rounded-full bg-white/40 px-2 py-0.5 text-badge">
            {{ unreadCount > 9 ? "9+" : unreadCount }}
          </span>
        </button>
        <button
          type="button"
          @click="loadNotifications()"
          :disabled="loading"
          class="admin-btn admin-btn--outline"
        >
          <Icon name="heroicons:arrow-path" :class="['h-4 w-4', { 'animate-spin': loading }]" />
          Refresh
        </button>
      </template>
    </AdminPageHeader>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <div class="bg-white rounded-2xl shadow-sm border border-secondary-200/60 p-5 hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="stat-label">Showing</p>
            <p class="stat-value text-secondary-900 mt-1.5 tabular-nums">
              {{ notifications.length }}
            </p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-secondary-100 to-secondary-200 rounded-xl flex items-center justify-center">
            <Icon
              name="heroicons:inbox-stack"
              class="w-6 h-6 text-secondary-600"
            />
          </div>
        </div>
        <div class="mt-4 h-1 bg-secondary-100 rounded-full overflow-hidden">
          <div
            class="h-full bg-secondary-400 rounded-full transition-all duration-500"
            :style="{ width: notifications.length > 0 ? '100%' : '0%' }"
          ></div>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-secondary-200/60 p-5 hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="stat-label text-warning-600">Unread</p>
            <p class="stat-value text-warning-600 mt-1.5 tabular-nums">
              {{ unreadCount }}
            </p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-warning-100 to-warning-200 rounded-xl flex items-center justify-center">
            <Icon
              name="heroicons:envelope"
              class="w-6 h-6 text-warning-600"
            />
          </div>
        </div>
        <div class="mt-4 h-1 bg-warning-100 rounded-full overflow-hidden">
          <div
            class="h-full bg-warning-500 rounded-full transition-all duration-500"
            :style="{ width: unreadPct + '%' }"
          ></div>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-secondary-200/60 p-5 hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="stat-label text-success-600">Read</p>
            <p class="stat-value text-success-600 mt-1.5 tabular-nums">
              {{ readCount }}
            </p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-success-100 to-success-200 rounded-xl flex items-center justify-center">
            <Icon
              name="heroicons:check-circle-20-solid"
              class="w-6 h-6 text-success-600"
            />
          </div>
        </div>
        <div class="mt-4 h-1 bg-success-100 rounded-full overflow-hidden">
          <div
            class="h-full bg-success-500 rounded-full transition-all duration-500"
            :style="{ width: readPct + '%' }"
          ></div>
        </div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-secondary-200/60 mb-5 overflow-hidden">
      <!-- Category Pills -->
      <div class="px-5 pt-5 pb-3 flex items-center justify-between gap-4 flex-wrap border-b border-secondary-100">
        <div class="flex items-center gap-2 flex-wrap">
          <button
            v-for="cat in categories"
            :key="cat.value"
            @click="categoryFilter = cat.value"
            :class="[
              'inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-navigation transition-all duration-200 whitespace-nowrap',
              categoryFilter === cat.value
                ? 'bg-primary-600 text-white shadow-sm shadow-primary-600/20'
                : 'bg-secondary-100 text-secondary-600 hover:bg-secondary-200 hover:text-secondary-800',
            ]"
          >
            <Icon :name="cat.icon" class="w-3.5 h-3.5" />
            {{ cat.label }}
          </button>
        </div>

        <!-- Read Status Tabs -->
        <div class="inline-flex items-center p-0.5 bg-secondary-100 rounded-xl">
          <button
            v-for="tab in filterTabs"
            :key="tab.value"
            @click="filterTab = tab.value"
            :class="[
              'px-4 py-1.5 rounded-lg text-navigation transition-all duration-200 whitespace-nowrap',
              filterTab === tab.value
                ? 'bg-white text-secondary-900 shadow-sm'
                : 'text-secondary-500 hover:text-secondary-700',
            ]"
          >
            {{ tab.label }}
            <span
              v-if="tab.value === 'unread' && unreadCount > 0"
              class="ml-1.5"
            >
              · {{ unreadCount > 9 ? "9+" : unreadCount }}
            </span>
          </button>
        </div>
      </div>

      <!-- Search + Quick Actions -->
      <div class="p-5 flex flex-col lg:flex-row gap-3 items-stretch lg:items-center">
        <!-- Search Box -->
        <div class="flex-1 relative">
          <Icon
            name="heroicons:magnifying-glass"
            class="absolute left-3.5 top-1/2 transform -translate-y-1/2 h-4 w-4 text-secondary-400"
          />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search by title or message..."
            class="w-full pl-10 pr-10 py-2.5 border border-secondary-200 rounded-xl bg-secondary-50/50 focus:bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all text-input text-secondary-900 placeholder:text-secondary-400"
          />
          <button
            v-if="searchQuery"
            @click="searchQuery = ''"
            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-secondary-400 hover:text-secondary-600 transition-colors"
          >
            <Icon name="heroicons:x-mark" class="h-4 w-4" />
          </button>
        </div>

        <!-- Quick Actions -->
        <div class="flex items-center gap-2 flex-shrink-0">
          <button
            v-if="unreadVisibleCount > 0"
            @click="handleMarkFilteredAsRead"
            class="px-3.5 py-2.5 text-button bg-primary-50 text-primary-700 rounded-xl hover:bg-primary-100 active:bg-primary-150 transition-colors flex items-center border border-primary-100"
            :title="`Mark ${unreadVisibleCount} visible notification(s) as read`"
          >
            <Icon name="heroicons:check" class="w-3.5 h-3.5 mr-1.5" />
            Mark visible
            <span class="ml-1.5 bg-primary-200/60 px-1.5 py-0.5 rounded-md font-bold">
              {{ unreadVisibleCount }}
            </span>
          </button>
        </div>
      </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-white rounded-2xl shadow-sm border border-secondary-200/60 overflow-hidden">
      <div class="divide-y divide-secondary-100/80">
        <!-- Loading State -->
        <div v-if="loading && filteredNotifications.length === 0" class="py-20 text-center">
          <div
            class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-secondary-200 border-t-primary-600"
          ></div>
          <p class="text-secondary-500 mt-4 font-medium">Loading notifications...</p>
        </div>

        <!-- Empty State -->
        <div
          v-else-if="filteredNotifications.length === 0"
          class="py-20 text-center px-6"
        >
          <div class="w-20 h-20 bg-gradient-to-br from-secondary-100 to-secondary-200 rounded-2xl flex items-center justify-center mx-auto mb-5">
            <Icon
              name="heroicons:bell-slash"
              class="h-10 w-10 text-secondary-400"
            />
          </div>
          <h3 class="card-title text-secondary-900 mb-2">
            No notifications found
          </h3>
          <p class="text-body text-secondary-500 max-w-sm mx-auto leading-relaxed">
            {{ getEmptyStateMessage() }}
          </p>
        </div>

        <!-- Notification Items -->
        <div
          v-for="notification in filteredNotifications"
          :key="notification.id"
          @click="handleNotificationClick(notification)"
          class="p-5 hover:bg-secondary-50/60 cursor-pointer transition-all duration-150 group"
          :class="{ 'bg-primary-50/40 border-l-4 border-l-primary-500': !notification.is_read }"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-start flex-1 min-w-0">
              <div
                class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 mr-4 shadow-sm"
                :class="getCategoryBgClass(getNotificationCategory(notification.type))"
              >
                <span class="text-lg leading-none">
                  {{ getNotificationIcon(notification.type) }}
                </span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-start gap-2 mb-1.5 flex-wrap">
                  <h3
                    class="text-body leading-snug"
                    :class="[
                      !notification.is_read
                        ? 'font-bold text-secondary-900'
                        : 'font-semibold text-secondary-700',
                    ]"
                  >
                    {{ notification.title }}
                  </h3>
                  <span
                    v-if="
                      notification.priority === 'urgent' ||
                      notification.priority === 'high'
                    "
                    class="px-2 py-0.5 text-badge uppercase tracking-wide rounded-full flex-shrink-0"
                    :class="getPriorityClass(notification.priority)"
                  >
                    {{ getPriorityLabel(notification.priority) }}
                  </span>
                  <span
                    v-if="!notification.is_read"
                    class="w-2 h-2 rounded-full bg-primary-500 flex-shrink-0 mt-1.5"
                  ></span>
                </div>
                <p class="text-body text-secondary-600 leading-relaxed mb-2 line-clamp-2">
                  {{ notification.message }}
                </p>
                <div class="flex items-center justify-between gap-3 flex-wrap">
                  <div class="flex items-center gap-2.5 text-helper">
                    <span class="inline-flex items-center gap-1 font-medium">
                      <Icon name="heroicons:clock" class="w-3 h-3" />
                      {{ getTimeAgo(notification.created_at) }}
                    </span>
                    <span class="text-secondary-300">·</span>
                    <span
                      class="px-2 py-0.5 rounded-lg font-semibold"
                      :class="getCategoryBadgeClass(getNotificationCategory(notification.type))"
                    >
                      {{ getCategoryInfo(getNotificationCategory(notification.type)).label }}
                    </span>
                  </div>
                  <div class="flex items-center gap-2">
                    <span
                      v-if="notification.is_read"
                      class="flex items-center text-caption text-success-600 font-semibold"
                    >
                      <Icon
                        name="heroicons:check-circle-20-solid"
                        class="w-3.5 h-3.5 mr-1"
                      />
                      Read
                    </span>
                    <span
                      v-else
                      class="flex items-center text-caption text-primary-600 font-semibold"
                    >
                      <Icon name="heroicons:envelope" class="w-3.5 h-3.5 mr-1" />
                      New
                    </span>
                  </div>
                </div>
                <div
                  v-if="notification.action_url"
                  class="mt-3 inline-flex items-center text-navigation text-primary-600 hover:text-primary-700 transition-colors"
                >
                  {{ notification.action_text || "View Details" }}
                  <Icon name="heroicons:arrow-right" class="w-4 h-4 ml-1" />
                </div>
              </div>
            </div>
            <div class="flex items-center gap-0.5 flex-shrink-0 opacity-70 group-hover:opacity-100 transition-opacity">
              <button
                v-if="!notification.is_read"
                @click.stop="markAsRead(notification.id)"
                class="p-2 text-secondary-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-all"
                title="Mark as read"
              >
                <Icon name="heroicons:check-circle" class="h-4.5 w-4.5" />
              </button>
              <button
                @click.stop="handleDeleteNotification(notification.id)"
                class="p-2 text-secondary-400 hover:text-error-600 hover:bg-error-50 rounded-lg transition-all"
                title="Delete notification"
              >
                <Icon name="heroicons:trash" class="h-4.5 w-4.5" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="border-t border-secondary-200/60 px-5 py-4 flex items-center justify-between gap-4 flex-wrap bg-secondary-50/30">
        <div>
          <button
            v-if="hasReadNotifications"
            @click="handleDeleteAllRead"
            class="px-3.5 py-2 text-button text-error-600 bg-error-50 hover:bg-error-100 rounded-xl transition-colors inline-flex items-center gap-1.5 border border-error-100"
          >
            <Icon name="heroicons:trash" class="w-3.5 h-3.5" />
            Clear all read
          </button>
        </div>
        <div class="text-helper font-medium">
          Showing <span class="text-secondary-800 font-bold tabular-nums">{{ filteredNotifications.length }}</span> of <span class="text-secondary-800 font-bold tabular-nums">{{ notifications.length }}</span> notifications
        </div>
        <div>
          <button
            v-if="notifications.length >= 50"
            @click="loadNotifications(100)"
            class="px-4 py-2 text-button bg-white text-secondary-700 rounded-xl hover:bg-secondary-50 transition-all border border-secondary-200 inline-flex items-center gap-1.5"
          >
            Load more
            <Icon name="heroicons:chevron-down" class="w-3.5 h-3.5" />
          </button>
        </div>
      </div>
    </div>

    <AdminConfirmDialog
      v-model="showClearReadConfirm"
      title="Clear read notifications?"
      message="Every notification you have already read will be permanently removed."
      confirm-label="Clear"
      tone="danger"
      @confirm="confirmDeleteAllRead"
    />
  </div>
</template>

<script setup>
definePageMeta({
  layout: "user-dashboard",
  middleware: ["auth"],
});

const { $toast } = useNuxtApp();

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

const getPriorityLabel = (priority) => {
  const labels = {
    low: "LOW",
    normal: "NORMAL",
    high: "HIGH",
    urgent: "URGENT",
  };
  return (
    labels[String(priority || "").toLowerCase()] ||
    String(priority || "N/A")
      .replace(/_/g, " ")
      .toUpperCase()
  );
};

const filterTab = ref("all");
const categoryFilter = ref("all");
const searchQuery = ref("");

const filterTabs = [
  { label: "All", value: "all" },
  { label: "Unread", value: "unread" },
  { label: "Read", value: "read" },
];

const categories = [
  { value: "all", label: "All", icon: "heroicons:bell" },
  { value: "system", label: "System", icon: "heroicons:cog-6-tooth" },
  { value: "security", label: "Security", icon: "heroicons:shield-check" },
  { value: "activity", label: "Activity", icon: "heroicons:chart-bar" },
  { value: "payment", label: "Payment", icon: "heroicons:credit-card" },
  { value: "nfc", label: "NFC Cards", icon: "heroicons:qr-code" },
];

const readCount = computed(() =>
  Math.max(0, notifications.value.length - unreadCount.value)
);

const unreadPct = computed(() => {
  const total = notifications.value.length;
  if (total === 0) return 0;
  return Math.min(100, Math.round((unreadCount.value / total) * 100));
});

const readPct = computed(() => {
  const total = notifications.value.length;
  if (total === 0) return 0;
  return Math.min(100, Math.round((readCount.value / total) * 100));
});

const filteredNotifications = computed(() => {
  let result = notifications.value;

  if (filterTab.value === "unread") {
    result = result.filter((n) => !n.is_read);
  } else if (filterTab.value === "read") {
    result = result.filter((n) => n.is_read);
  }

  if (categoryFilter.value !== "all") {
    result = result.filter(
      (n) => getNotificationCategory(n.type) === categoryFilter.value
    );
  }

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

const unreadVisibleCount = computed(() =>
  filteredNotifications.value.filter((n) => !n.is_read).length
);

const hasReadNotifications = computed(() =>
  notifications.value.some((n) => n.is_read)
);

const getCategoryBgClass = (category) => {
  const classes = {
    system: "bg-info-100",
    security: "bg-error-100",
    activity: "bg-success-100",
    payment: "bg-primary-100",
    nfc: "bg-warning-100",
    other: "bg-secondary-100",
  };
  return classes[category] || "bg-secondary-100";
};

const getCategoryBadgeClass = (category) => {
  const classes = {
    system: "bg-info-50 text-info-700",
    security: "bg-error-50 text-error-700",
    activity: "bg-success-50 text-success-700",
    payment: "bg-primary-50 text-primary-700",
    nfc: "bg-warning-50 text-warning-700",
    other: "bg-secondary-100 text-secondary-700",
  };
  return classes[category] || "bg-secondary-100 text-secondary-700";
};

const handleNotificationClick = async (notification) => {
  if (!notification.is_read) {
    await markAsRead(notification.id);
  }
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

  const promises = unreadFiltered.map((n) => markAsRead(n.id));
  await Promise.all(promises);

  $toast.success(`Marked ${unreadFiltered.length} notification(s) as read`);
};

// Single delete is low-stakes: no confirmation step.
const handleDeleteNotification = async (notificationId) => {
  await deleteNotification(notificationId);
  $toast.success("Notification deleted");
};

const showClearReadConfirm = ref(false);
const handleDeleteAllRead = () => {
  showClearReadConfirm.value = true;
};
const confirmDeleteAllRead = async () => {
  showClearReadConfirm.value = false;
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
    return "You have no unread notifications — great job staying on top of things!";
  } else if (filterTab.value === "read") {
    return "You have no read notifications yet";
  }
  return "You have no notifications yet. We'll notify you when something important happens.";
};

onMounted(() => {
  loadNotifications(50);
});
</script>

<style scoped>
select {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.75rem center;
  background-repeat: no-repeat;
  background-size: 1.25em 1.25em;
  padding-right: 2.75rem;
}
</style>
