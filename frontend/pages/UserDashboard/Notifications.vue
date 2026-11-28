<!-- pages/UserDashboard/Notifications.vue
     Every portal user's notification inbox (all plans and roles). Built on the
     shared admin design system so it matches the rest of the portal. -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Account"
      eyebrow-icon="heroicons:bell"
      title="Notifications"
      description="Card status, payments, security alerts and announcements, all in one place."
    >
      <template #actions>
        <button type="button" class="admin-btn admin-btn--outline" :disabled="loading" @click="reload">
          <Icon name="heroicons:arrow-path" class="h-4 w-4" :class="{ 'animate-spin': loading }" />
          Refresh
        </button>
        <button
          v-if="unreadCount > 0"
          type="button"
          class="admin-btn admin-btn--primary"
          :disabled="markingAll"
          @click="handleMarkAllAsRead"
        >
          <Icon name="heroicons:check-circle" class="h-4 w-4" />
          Mark all as read
        </button>
      </template>
    </AdminPageHeader>

    <!-- Toolbar: read state, category, search -->
    <div class="admin-panel mb-5">
      <div class="admin-panel-body space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div class="inline-flex rounded-lg border border-secondary-200 bg-secondary-50 p-0.5" role="tablist" aria-label="Read status">
            <button
              v-for="tab in filterTabs"
              :key="tab.value"
              type="button"
              role="tab"
              :aria-selected="filterTab === tab.value"
              class="rounded-md px-3.5 py-1.5 text-navigation transition-colors"
              :class="filterTab === tab.value ? 'bg-white text-secondary-900 shadow-sm' : 'text-secondary-500 hover:text-secondary-800'"
              @click="filterTab = tab.value"
            >
              {{ tab.label }}
              <span class="ml-1 tabular-nums text-secondary-400">{{ tab.count }}</span>
            </button>
          </div>

          <div class="relative w-full sm:w-72">
            <Icon
              name="heroicons:magnifying-glass"
              class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-secondary-400"
            />
            <input
              v-model="searchQuery"
              type="search"
              placeholder="Search notifications…"
              class="admin-field !pl-9"
              aria-label="Search notifications"
            />
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2" aria-label="Category">
          <button
            v-for="cat in categoriesWithCounts"
            :key="cat.value"
            type="button"
            class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-caption font-medium transition-colors"
            :class="categoryFilter === cat.value
              ? 'border-secondary-900 bg-secondary-900 text-white'
              : 'border-secondary-200 bg-white text-secondary-600 hover:border-secondary-300 hover:text-secondary-900'"
            :aria-pressed="categoryFilter === cat.value"
            @click="categoryFilter = cat.value"
          >
            <Icon :name="cat.icon" class="h-3.5 w-3.5" />
            {{ cat.label }}
            <span class="tabular-nums opacity-60">{{ cat.count }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- List -->
    <AdminSectionCard flush>
      <template #title>{{ listTitle }}</template>
      <template #header-actions>
        <button
          v-if="unreadVisibleCount > 0 && (filterTab !== 'all' || categoryFilter !== 'all' || searchQuery.trim())"
          type="button"
          class="admin-btn admin-btn--ghost admin-btn--sm"
          @click="handleMarkFilteredAsRead"
        >
          <Icon name="heroicons:check" class="h-4 w-4" />
          Mark these {{ unreadVisibleCount }} as read
        </button>
      </template>

      <!-- Loading -->
      <div v-if="loading && notifications.length === 0" class="divide-y divide-secondary-100" role="status" aria-busy="true">
        <div v-for="i in 5" :key="i" class="flex items-start gap-4 px-5 py-4">
          <span class="admin-skeleton h-10 w-10 shrink-0 rounded-xl"></span>
          <div class="flex-1 space-y-2">
            <span class="admin-skeleton block h-4 w-1/3"></span>
            <span class="admin-skeleton block h-3 w-2/3"></span>
          </div>
        </div>
      </div>

      <!-- Empty -->
      <AdminEmptyState
        v-else-if="filteredNotifications.length === 0"
        :icon="notifications.length ? 'heroicons:funnel' : 'heroicons:bell-slash'"
        :title="emptyState.title"
        :description="emptyState.description"
      >
        <template v-if="notifications.length" #actions>
          <button type="button" class="admin-btn admin-btn--outline admin-btn--sm" @click="clearFilters">
            Show all notifications
          </button>
        </template>
      </AdminEmptyState>

      <!-- Grouped by day -->
      <div v-else>
        <section v-for="group in groupedNotifications" :key="group.label">
          <h3 class="sticky top-0 z-[1] border-b border-secondary-100 bg-secondary-50/95 px-5 py-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-secondary-500 backdrop-blur">
            {{ group.label }}
          </h3>
          <ul class="divide-y divide-secondary-100">
            <li
              v-for="notification in group.items"
              :key="notification.id"
              class="group relative flex items-start gap-4 px-5 py-4 transition-colors"
              :class="[
                notification.is_read ? 'hover:bg-secondary-50/70' : 'bg-accent-50/40 hover:bg-accent-50/70',
                notification.action_url ? 'cursor-pointer' : '',
              ]"
              @click="handleNotificationClick(notification)"
            >
              <span
                v-if="!notification.is_read"
                class="absolute left-0 top-0 h-full w-[3px] bg-accent-500"
                aria-hidden="true"
              ></span>

              <span
                class="mt-0.5 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                :class="categoryTone(getNotificationCategory(notification.type))"
              >
                <Icon :name="getNotificationIcon(notification.type)" class="h-5 w-5" aria-hidden="true" />
              </span>

              <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                  <p
                    class="text-body leading-snug"
                    :class="notification.is_read ? 'font-medium text-secondary-700' : 'font-semibold text-secondary-900'"
                  >
                    {{ notification.title }}
                  </p>
                  <span v-if="notification.priority === 'urgent'" class="admin-badge admin-badge--danger">Urgent</span>
                  <span v-else-if="notification.priority === 'high'" class="admin-badge admin-badge--warning">Important</span>
                  <span v-if="!notification.is_read" class="sr-only">Unread</span>
                </div>
                <p v-if="notification.message" class="mt-1 line-clamp-2 text-body text-secondary-600">
                  {{ notification.message }}
                </p>
                <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-caption text-secondary-500">
                  <time :datetime="notification.created_at" :title="fullDate(notification.created_at)">
                    {{ getTimeAgo(notification.created_at) }}
                  </time>
                  <span aria-hidden="true">·</span>
                  <span>{{ getCategoryInfo(getNotificationCategory(notification.type)).label }}</span>
                  <span v-if="notification.action_url" class="inline-flex items-center gap-1 font-medium text-accent-700">
                    {{ notification.action_text || "Open" }}
                    <Icon :name="isExternal(notification.action_url) ? 'heroicons:arrow-top-right-on-square' : 'heroicons:arrow-right'" class="h-3.5 w-3.5" />
                  </span>
                </div>
              </div>

              <div class="flex shrink-0 items-center gap-1 opacity-100 transition-opacity sm:opacity-0 sm:group-hover:opacity-100 sm:group-focus-within:opacity-100">
                <button
                  v-if="!notification.is_read"
                  type="button"
                  class="admin-icon-btn"
                  title="Mark as read"
                  :aria-label="`Mark '${notification.title}' as read`"
                  @click.stop="markAsRead(notification.id)"
                >
                  <Icon name="heroicons:check" class="h-[18px] w-[18px]" />
                </button>
                <button
                  type="button"
                  class="admin-icon-btn hover:!text-error-600"
                  title="Delete"
                  :aria-label="`Delete '${notification.title}'`"
                  @click.stop="handleDeleteNotification(notification.id)"
                >
                  <Icon name="heroicons:trash" class="h-[18px] w-[18px]" />
                </button>
              </div>
            </li>
          </ul>
        </section>
      </div>

      <template v-if="notifications.length" #footer>
        <div class="flex w-full flex-wrap items-center justify-between gap-3">
          <button
            v-if="hasReadNotifications"
            type="button"
            class="admin-btn admin-btn--ghost admin-btn--sm text-error-600"
            @click="showClearReadConfirm = true"
          >
            <Icon name="heroicons:trash" class="h-4 w-4" />
            Clear read
          </button>
          <span v-else></span>
          <span class="text-caption text-secondary-500">
            Showing {{ filteredNotifications.length }} of {{ notifications.length }}
          </span>
          <button
            v-if="canLoadMore"
            type="button"
            class="admin-btn admin-btn--outline admin-btn--sm"
            :disabled="loading"
            @click="loadMore"
          >
            Load more
            <Icon name="heroicons:chevron-down" class="h-4 w-4" />
          </button>
          <span v-else></span>
        </div>
      </template>
    </AdminSectionCard>

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
} = useNotifications();

const PAGE_SIZE = 50;
const limit = ref(PAGE_SIZE);
const markingAll = ref(false);

const filterTab = ref("all");
const categoryFilter = ref("all");
const searchQuery = ref("");

const readCount = computed(() => notifications.value.filter((n) => n.is_read).length);
const loadedUnread = computed(() => notifications.value.length - readCount.value);

const filterTabs = computed(() => [
  { label: "All", value: "all", count: notifications.value.length },
  { label: "Unread", value: "unread", count: loadedUnread.value },
  { label: "Read", value: "read", count: readCount.value },
]);

const CATEGORY_KEYS = ["all", "nfc", "payment", "security", "activity", "system", "other"];

const categoriesWithCounts = computed(() => {
  const counts = {};
  for (const n of notifications.value) {
    const c = getNotificationCategory(n.type);
    counts[c] = (counts[c] || 0) + 1;
  }
  return CATEGORY_KEYS
    .filter((key) => key === "all" || counts[key])
    .map((key) => ({
      value: key,
      label: getCategoryInfo(key).label,
      icon: getCategoryInfo(key).icon,
      count: key === "all" ? notifications.value.length : counts[key],
    }));
});

const filteredNotifications = computed(() => {
  let result = notifications.value;
  if (filterTab.value === "unread") result = result.filter((n) => !n.is_read);
  else if (filterTab.value === "read") result = result.filter((n) => n.is_read);

  if (categoryFilter.value !== "all") {
    result = result.filter((n) => getNotificationCategory(n.type) === categoryFilter.value);
  }

  const q = searchQuery.value.trim().toLowerCase();
  if (q) {
    result = result.filter(
      (n) => String(n.title || "").toLowerCase().includes(q) || String(n.message || "").toLowerCase().includes(q)
    );
  }
  return result;
});

// Today / Yesterday / This week / Earlier
const groupedNotifications = computed(() => {
  const startOfDay = (d) => new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime();
  const today = startOfDay(new Date());
  const day = 86400000;
  const groups = [
    { label: "Today", items: [] },
    { label: "Yesterday", items: [] },
    { label: "Earlier this week", items: [] },
    { label: "Earlier", items: [] },
  ];
  for (const n of filteredNotifications.value) {
    const t = startOfDay(new Date(n.created_at));
    if (t >= today) groups[0].items.push(n);
    else if (t >= today - day) groups[1].items.push(n);
    else if (t >= today - 6 * day) groups[2].items.push(n);
    else groups[3].items.push(n);
  }
  return groups.filter((g) => g.items.length);
});

const listTitle = computed(() => {
  const cat = categoryFilter.value === "all" ? "" : getCategoryInfo(categoryFilter.value).label + " ";
  const state = filterTab.value === "unread" ? "Unread " : filterTab.value === "read" ? "Read " : "";
  return `${state}${cat}notifications`.replace(/^\w/, (c) => c.toUpperCase());
});

const unreadVisibleCount = computed(() => filteredNotifications.value.filter((n) => !n.is_read).length);
const hasReadNotifications = computed(() => readCount.value > 0);

// The API returns at most `limit`; a full page means there may be more.
const canLoadMore = computed(() => notifications.value.length >= limit.value);

const emptyState = computed(() => {
  if (!notifications.value.length) {
    return { title: "You're all caught up", description: "We'll let you know here when something needs your attention." };
  }
  if (searchQuery.value.trim()) return { title: "No matches", description: "No notification matches your search." };
  if (filterTab.value === "unread") return { title: "No unread notifications", description: "You've read everything in this view." };
  return { title: "Nothing in this view", description: "Try another category or status." };
});

const CATEGORY_TONES = {
  system: "bg-info-100 text-info-700",
  security: "bg-error-100 text-error-700",
  activity: "bg-success-100 text-success-700",
  payment: "bg-primary-100 text-primary-700",
  nfc: "bg-accent-100 text-accent-700",
  other: "bg-secondary-100 text-secondary-600",
};
const categoryTone = (category) => CATEGORY_TONES[category] || CATEGORY_TONES.other;

const isExternal = (url) => /^https?:\/\//i.test(String(url || ""));

const fullDate = (value) =>
  new Date(value).toLocaleString(undefined, { dateStyle: "medium", timeStyle: "short" });

const reload = () => loadNotifications(limit.value);

const loadMore = async () => {
  limit.value += PAGE_SIZE;
  await loadNotifications(limit.value);
};

const clearFilters = () => {
  filterTab.value = "all";
  categoryFilter.value = "all";
  searchQuery.value = "";
};

const handleNotificationClick = async (notification) => {
  if (!notification.is_read) await markAsRead(notification.id);
  const url = notification.action_url;
  if (!url) return;
  // Absolute links open outside the app; in-app paths use the router.
  if (isExternal(url)) {
    window.open(url, "_blank", "noopener");
  } else {
    navigateTo(url);
  }
};

const handleMarkAllAsRead = async () => {
  markingAll.value = true;
  const result = await markAllAsRead();
  markingAll.value = false;
  if (result?.ok === false) $toast.error(result.message);
  else $toast.success("All notifications marked as read");
};

const handleMarkFilteredAsRead = async () => {
  const unread = filteredNotifications.value.filter((n) => !n.is_read);
  if (!unread.length) return;
  await Promise.all(unread.map((n) => markAsRead(n.id)));
  $toast.success(`Marked ${unread.length} as read`);
};

// Single delete is low-stakes: no confirmation step.
const handleDeleteNotification = async (id) => {
  await deleteNotification(id);
  $toast.success("Notification deleted");
};

const showClearReadConfirm = ref(false);
const confirmDeleteAllRead = async () => {
  showClearReadConfirm.value = false;
  await deleteAllRead();
  $toast.success("Read notifications cleared");
};

onMounted(() => {
  loadNotifications(limit.value);
});
</script>
