<!-- layouts/admin.vue -->
<template>
  <div class="min-h-screen bg-secondary-50">
    <!-- Desktop Layout Container -->
    <div class="flex h-screen overflow-hidden">
      <!-- Sidebar -->
      <div
        class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:relative lg:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
      >
        <div class="flex flex-col h-full">
          <!-- Logo -->
          <div
            class="flex items-center justify-center h-16 px-4 border-b border-secondary-200 flex-shrink-0"
          >
            <NuxtLink to="/" class="flex items-center w-full justify-center">
              <template v-if="brandSettings.system_logo_url">
                <img
                  :src="brandSettings.system_logo_url + '?v=' + brandVersion"
                  :alt="brandSettings.app_name || 'NFCGo'"
                  class="h-8 max-h-8 max-w-[120px] object-contain"
                />
              </template>
              <template v-else>
                <Icon
                  name="heroicons:identification"
                  class="h-8 w-8 text-primary-600"
                />
                <span class="ml-2 text-xl font-bold text-secondary-900">{{ brandSettings.app_name || 'NFCGo' }}</span>
              </template>
              <span
                class="ml-2 px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full"
                >Admin</span
              >
            </NuxtLink>
          </div>

          <!-- Navigation - Scrollable -->
          <nav class="flex-1 overflow-y-auto px-3 py-6 text-sm font-normal text-secondary-700">
            <div
              v-for="(group, groupIndex) in navigation"
              :key="group.category"
              :class="groupIndex === 0 ? '' : 'mt-4'"
            >
              <!-- Single-item group: render as direct page link (no dropdown) -->
              <NuxtLink
                v-if="group.items.length === 1"
                :to="group.items[0].href"
                :class="[
                  'group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200 w-full text-left',
                  isActiveRoute(group.items[0].href)
                    ? 'bg-primary-50 text-primary-700 border-l-3 border-primary-700'
                    : 'text-secondary-700 hover:bg-secondary-50 hover:text-secondary-900',
                ]"
              >
                {{ group.items[0].name }}
                <span
                  v-if="group.items[0].badge && Number(group.items[0].badge) > 0"
                  class="ml-auto inline-flex items-center justify-center rounded-full bg-red-500 text-white text-xs font-semibold h-5 min-w-[1.25rem] px-1"
                  :title="`${group.items[0].badge} unread`"
                >
                  {{ group.items[0].badge }}
                </span>
              </NuxtLink>

              <!-- Multi-item group: collapsible dropdown -->
              <template v-else>
                <button
                  type="button"
                  @click.stop="toggleGroup(group.category)"
                  class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-secondary-50 transition-colors group mb-1"
                >
                  <span class="text-sm font-semibold text-secondary-500 capitalize">
                    {{ group.category }}
                  </span>
                  <Icon
                    :name="isGroupExpanded(group.category) ? 'heroicons:chevron-up' : 'heroicons:chevron-down'"
                    class="h-4 w-4 text-secondary-400 group-hover:text-secondary-600 transition-colors"
                  />
                </button>

                <Transition
                  enter-active-class="transition ease-out duration-200"
                  enter-from-class="opacity-0 -translate-y-1"
                  enter-to-class="opacity-100 translate-y-0"
                  leave-active-class="transition ease-in duration-150"
                  leave-from-class="opacity-100 translate-y-0"
                  leave-to-class="opacity-0 -translate-y-1"
                >
                  <div v-show="isGroupExpanded(group.category)" class="space-y-1 overflow-hidden">
                    <NuxtLink
                      v-for="item in group.items"
                      :key="item.name"
                      :to="item.href"
                      :class="[
                        'group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200 w-full text-left',
                        isActiveRoute(item.href)
                          ? 'bg-primary-50 text-primary-700 border-l-3 border-primary-700'
                          : 'text-secondary-700 hover:bg-secondary-50 hover:text-secondary-900',
                      ]"
                    >
                      {{ item.name }}
                      <span
                        v-if="item.badge && Number(item.badge) > 0"
                        class="ml-auto inline-flex items-center justify-center rounded-full bg-red-500 text-white text-xs font-semibold h-5 min-w-[1.25rem] px-1"
                        :title="`${item.badge} unread`"
                      >
                        {{ item.badge }}
                      </span>
                    </NuxtLink>
                  </div>
                </Transition>
              </template>
            </div>
          </nav>
        </div>
      </div>

      <!-- Main Content -->
      <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Bar -->
        <header
          class="bg-white shadow-sm border-b border-secondary-200 flex-shrink-0"
        >
          <div
            class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16"
          >
            <!-- Left: Mobile menu button + Page Title -->
            <div class="flex items-center gap-3">
              <button
                @click="sidebarOpen = !sidebarOpen"
                class="lg:hidden p-2 rounded-md text-secondary-500 hover:text-secondary-700 hover:bg-secondary-100 transition-colors"
              >
                <Icon
                  :name="sidebarOpen ? 'heroicons:x-mark' : 'heroicons:bars-3'"
                  class="h-6 w-6"
                />
              </button>
              <h1 class="text-lg font-semibold text-secondary-900">
                {{ pageTitle }}
              </h1>
            </div>

            <!-- Right side actions -->
            <div class="flex items-center gap-2 sm:gap-3">
              <!-- Theme Switcher -->
              <ThemeSwitcher />

              <!-- Admin Notifications Bell with Drawer -->
              <div class="relative">
                <button
                  ref="adminNotifButtonRef"
                  @click="toggleAdminNotifications"
                  :class="[
                    'relative p-2 rounded-lg transition-colors',
                    showAdminNotifications
                      ? 'bg-primary-50 text-primary-600'
                      : 'text-secondary-500 hover:text-secondary-700 hover:bg-secondary-100',
                  ]"
                >
                  <Icon name="heroicons:bell" class="h-5 w-5" />
                  <span
                    v-if="pendingNotificationCount > 0"
                    class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center h-5 min-w-[1.25rem] px-1 rounded-full text-[0.65rem] font-bold bg-red-500 text-white ring-2 ring-white"
                  >
                    {{ pendingNotificationCount > 9 ? "9+" : pendingNotificationCount }}
                  </span>
                </button>
              </div>

              <!-- User Feedback Bell -->
              <NuxtLink
                to="/AdminManagement/feedback"
                class="relative p-2 rounded-lg text-secondary-500 hover:text-secondary-700 hover:bg-secondary-100 transition-colors"
              >
                <Icon name="heroicons:chat-bubble-left-right" class="h-5 w-5" />
                <span
                  v-if="unreadFeedbackCount > 0"
                  class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center h-5 min-w-[1.25rem] px-1 rounded-full text-[0.65rem] font-bold bg-amber-500 text-white ring-2 ring-white"
                >
                  {{ unreadFeedbackCount > 9 ? "9+" : unreadFeedbackCount }}
                </span>
              </NuxtLink>

              <!-- Divider -->
              <div class="w-px h-6 bg-secondary-200 mx-1 hidden sm:block"></div>

              <!-- Profile Menu -->
              <div class="relative" ref="profileMenuWrapperRef">
                <button
                  @click="toggleProfileMenu"
                  class="flex items-center gap-2 sm:gap-3 p-1.5 rounded-xl hover:bg-secondary-50 transition-colors"
                >
                  <div class="hidden sm:block text-right leading-tight">
                    <p class="text-sm font-semibold text-secondary-900 truncate max-w-[140px]">
                      {{
                        user?.first_name && user?.last_name
                          ? `${user.first_name} ${user.last_name}`
                          : user?.name || "Admin"
                      }}
                    </p>
                    <p class="text-xs text-secondary-500 truncate max-w-[140px]">
                      {{ user?.admin_role_display || "Administrator" }}
                    </p>
                  </div>
                  <div class="flex-shrink-0">
                    <img
                      :src="user?.profile_image || '/default-avatar.png'"
                      :alt="user?.name"
                      class="h-9 w-9 rounded-full object-cover ring-2 ring-white shadow-sm"
                    />
                  </div>
                </button>

                <!-- Profile Dropdown -->
                <Transition
                  enter-active-class="transition ease-out duration-200"
                  enter-from-class="opacity-0 translate-y-1"
                  enter-to-class="opacity-100 translate-y-0"
                  leave-active-class="transition ease-in duration-150"
                  leave-from-class="opacity-100 translate-y-0"
                  leave-to-class="opacity-0 translate-y-1"
                >
                  <div
                    v-if="showProfileMenu"
                    ref="profileMenuPanelRef"
                    class="profile-dropdown absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-secondary-200 z-[90] overflow-hidden"
                  >
                    <!-- Admin Info Header -->
                    <div class="p-5 bg-gradient-to-br from-red-50 to-secondary-50 border-b border-secondary-200">
                      <div class="flex items-center gap-3">
                        <img
                          :src="user?.profile_image || '/default-avatar.png'"
                          :alt="user?.name"
                          class="h-12 w-12 rounded-full object-cover ring-2 ring-white shadow-sm"
                        />
                        <div class="min-w-0 flex-1">
                          <p class="text-sm font-bold text-secondary-900 truncate">
                            {{
                              user?.first_name && user?.last_name
                                ? `${user.first_name} ${user.last_name}`
                                : user?.name || "Admin"
                            }}
                          </p>
                          <p class="text-xs text-secondary-500 truncate">
                            {{ user?.email || "admin@nfcgo.my" }}
                          </p>
                          <span class="mt-1 inline-flex items-center px-2 py-0.5 rounded-full text-[0.65rem] font-bold bg-red-100 text-red-700">
                            {{ user?.admin_role_display || "Administrator" }}
                          </span>
                        </div>
                      </div>
                    </div>

                    <!-- Menu Items -->
                    <div class="py-2">
                      <NuxtLink
                        to="/AdminManagement/profile-settings"
                        @click="showProfileMenu = false"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-secondary-700 hover:bg-secondary-50 hover:text-secondary-900 transition-colors"
                      >
                        <Icon
                          name="heroicons:user-circle"
                          class="h-5 w-5 text-secondary-400"
                        />
                        <span>Profile Setting</span>
                      </NuxtLink>

                      <NuxtLink
                        to="/AdminManagement/configuration"
                        @click="showProfileMenu = false"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-secondary-700 hover:bg-secondary-50 hover:text-secondary-900 transition-colors"
                      >
                        <Icon
                          name="heroicons:cog-6-tooth"
                          class="h-5 w-5 text-secondary-400"
                        />
                        <span>Configuration</span>
                      </NuxtLink>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-secondary-200"></div>

                    <!-- Logout -->
                    <div class="py-2">
                      <button
                        @click="handleLogout"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors"
                      >
                        <Icon
                          name="heroicons:arrow-right-on-rectangle"
                          class="h-5 w-5 text-red-400"
                        />
                        <span>Logout</span>
                      </button>
                    </div>
                  </div>
                </Transition>
              </div>
            </div>
          </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto bg-secondary-50 p-4 sm:p-6 lg:p-8">
          <slot />
        </main>
      </div>
    </div>

    <!-- Mobile overlay -->
    <div
      v-if="sidebarOpen"
      @click="sidebarOpen = false"
      class="fixed inset-0 z-40 lg:hidden bg-black bg-opacity-50"
    ></div>

    <!-- Admin Notification Drawer (Top 5, simple) -->
    <NotificationDrawer
      :is-open="showAdminNotifications"
      :button-ref="adminNotifButtonRef"
      :notifications="adminNotifications"
      :unread-count="pendingNotificationCount"
      :loading="adminNotifLoading"
      :get-icon="adminGetIcon"
      :get-time-ago="adminGetTimeAgo"
      :get-category="adminGetCategory"
      view-all-href="/AdminManagement/notifications"
      @close="showAdminNotifications = false"
      @item-click="handleAdminNotifClick"
      @mark-read="adminMarkAsRead"
      @mark-all-read="adminMarkAllAsRead"
      @delete="adminDeleteNotif"
      @clear-read="adminClearRead"
    />
  </div>
</template>

<script setup>
import { watch } from "vue";
const route = useRoute();
const authStore = useAuthStore();
const nuxtApp = useNuxtApp();
const { $api, $toast } = nuxtApp;
const user = computed(() => authStore.user);

const sidebarOpen = ref(false);
const showProfileMenu = ref(false);
const showAdminNotifications = ref(false);
const profileMenuWrapperRef = ref(null);
const profileMenuPanelRef = ref(null);
const adminNotifButtonRef = ref(null);
const unreadFeedbackCount = ref(0);
const pendingNotificationCount = ref(0);
const adminNotifications = ref([]);
const adminNotifLoading = ref(false);

// Brand settings (custom logo, app name, favicon)
const brandSettings = reactive({
  app_name: 'NFCGo',
  system_logo_url: '',
  system_favicon_url: '',
});
const brandVersion = ref(Date.now());

const loadAdminBrandSettings = async () => {
  try {
    if (!$api) {
      setTimeout(() => loadAdminBrandSettings(), 400);
      return;
    }
    // Use admin endpoint since we're in admin layout
    const response = await $api.get('/admin/system-settings/general');
    if (response.success && response.data) {
      brandSettings.app_name = response.data.app_name || 'NFCGo';

      // Prefer `system_logo_public_url` when present — it's an absolute
      // URL built from APP_URL that always hits the /storage fallback
      // route, so even on dev machines where the Symfony server runs
      // on a different port (SPA 3000 / API 8000) and storage:link
      // symlink is missing, the custom logo still renders in sidebar.
      let logoUrl = response.data.system_logo_public_url
        ? String(response.data.system_logo_public_url).trim()
        : (response.data.system_logo_url
            ? String(response.data.system_logo_url).trim()
            : '');
      brandSettings.system_logo_url = logoUrl;

      let faviconUrl = response.data.system_favicon_public_url
        ? String(response.data.system_favicon_public_url).trim()
        : (response.data.system_favicon_url
            ? String(response.data.system_favicon_url).trim()
            : '');
      brandSettings.system_favicon_url = faviconUrl;

      brandVersion.value = Date.now();
      applyFavicon();
      updateDocumentTitle();
    }
  } catch (e) {
    console.log('Admin brand: using defaults');
  }
};

const applyFavicon = () => {
  if (!process.client) return;
  const url = brandSettings.system_favicon_url;
  let link = document.querySelector("link[rel~='icon']");
  if (!link) {
    link = document.createElement('link');
    link.rel = 'icon';
    document.getElementsByTagName('head')[0].appendChild(link);
  }
  link.href = url ? (url + '?v=' + brandVersion.value) : '/favicon.ico';
  link.type = url && url.endsWith('.svg') ? 'image/svg+xml' : (url && url.endsWith('.png') ? 'image/png' : 'image/x-icon');
};

const updateDocumentTitle = () => {
  if (!process.client) return;
  const name = brandSettings.app_name || 'NFCGo';
  const existing = document.title;
  if (!existing.includes(name)) {
    document.title = existing + ' — ' + name;
  }
};

// Collapsible sidebar groups (key = category)
const expandedGroups = ref(new Set(["Dashboard"]));

const toggleGroup = (category) => {
  const next = new Set(expandedGroups.value);
  if (next.has(category)) {
    next.delete(category);
  } else {
    next.add(category);
  }
  expandedGroups.value = next;
};

const isGroupExpanded = (category) => expandedGroups.value.has(category);

// Profile menu handlers
const toggleProfileMenu = () => {
  showProfileMenu.value = !showProfileMenu.value;
};

// Navigation items - grouped by categories (simplified names, ordered)
const navigation = computed(() => [
  {
    category: "Dashboard",
    items: [
      {
        name: "Dashboard",
        href: "/AdminManagement",
      },
    ],
  },
  {
    category: "Users",
    items: [
      {
        name: "User Management",
        href: "/AdminManagement/users",
      },
      {
        name: "Business Users",
        href: "/AdminManagement/business-users",
      },
    ],
  },
  {
    category: "Cards",
    items: [
      {
        name: "NFC Card Management",
        href: "/AdminManagement/nfc-cards",
      },
      {
        name: "Card Templates",
        href: "/AdminManagement/card-templates",
      },
      {
        name: "Profile Builder Design",
        href: "/AdminManagement/profile-builder-design",
      },
      {
        name: "Invoices",
        href: "/AdminManagement/invoices",
      },
    ],
  },
  {
    category: "System",
    items: [
      {
        name: "Analytics",
        href: "/AdminManagement/stats",
      },
      {
        name: "Notifications",
        href: "/AdminManagement/notifications",
        badge: pendingNotificationCount.value,
      },
      {
        name: "User Feedback",
        href: "/AdminManagement/feedback",
        badge: unreadFeedbackCount.value,
      },
      {
        name: "Audit Log",
        href: "/AdminManagement/SystemAuditLog",
      },
    ],
  },
  {
    category: "Account",
    items: [
      {
        name: "Profile Setting",
        href: "/AdminManagement/profile-settings",
      },
      {
        name: "Configuration",
        href: "/AdminManagement/configuration",
      },
    ],
  },
]);

// Auto-expand group that contains the currently active route
watch(
  () => route.path,
  (path) => {
    const activeGroup = navigation.value.find((g) =>
      g.items.some((i) => i.href === path)
    );
    if (activeGroup) {
      const next = new Set(expandedGroups.value);
      next.add(activeGroup.category);
      expandedGroups.value = next;
    }
  },
  { immediate: true }
);

// Flattened navigation for pageTitle lookup
const flatNavigation = computed(() =>
  navigation.value.flatMap((group) => group.items)
);

// Page title based on current route
const pageTitle = computed(() => {
  const currentNav = flatNavigation.value.find((item) => item.href === route.path);
  return currentNav ? currentNav.name : "Admin Panel";
});

// Check if route is active
const isActiveRoute = (href) => {
  return route.path === href;
};

// Fetch unread feedback count
const loadUnreadFeedbackCount = async () => {
  try {
    if (!$api) {
      // Retry after a short delay if API not ready
      setTimeout(() => loadUnreadFeedbackCount(), 500);
      return;
    }
    const response = await $api.get("/admin/chatbot/feedback/unread-count");
    if (response.success) {
      unreadFeedbackCount.value = response.unread_count;
    }
  } catch (error) {
    console.error("Failed to load unread feedback count:", error);
  }
};

// Fetch unread notification count (for sidebar badge)
const loadPendingNotificationCount = async () => {
  try {
    if (!$api) {
      // Retry after a short delay if API not ready
      setTimeout(() => loadPendingNotificationCount(), 500);
      return;
    }
    const response = await $api.get("/admin/notifications/unread-count");
    if (response.success) {
      pendingNotificationCount.value = response.unread_count;
    }
  } catch (error) {
    console.error("Failed to load unread notification count:", error);
  }
};

// Mount guard to prevent post-unmount updates
const isMounted = ref(false);

// Handle logout
const handleLogout = async () => {
  showProfileMenu.value = false;
  try {
    // Prevent any further reactive updates before unmount
    isMounted.value = false;
    await authStore.logout();
    // Now navigate to login — single point of navigation to avoid double call race
    await navigateTo("/UserAccount/login", { replace: true });
  } catch (error) {
    console.error("Logout failed:", error);
  }
};

// Admin Notification Drawer helpers
const adminGetIcon = (type) => {
  const icons = {
    registration_success: "🎉",
    login_new_device: "🔒",
    profile_updated: "✅",
    payment_successful: "💳",
    payment_failed: "❌",
    nfc_card_purchased: "🛒",
    nfc_card_activated: "✨",
    admin_announcement: "📢",
    system_message: "ℹ️",
    business_card_order_request: "📋",
    contact_request: "📧",
    password_changed: "🔐",
  };
  return icons[type] || "🔔";
};

const adminGetCategory = (type) => {
  const cats = {
    admin_announcement: "system",
    system_message: "system",
    registration_success: "system",
    login_new_device: "security",
    password_changed: "security",
    profile_updated: "activity",
    contact_request: "activity",
    payment_successful: "payment",
    payment_failed: "payment",
    nfc_card_purchased: "nfc",
    nfc_card_activated: "nfc",
    business_card_order_request: "nfc",
  };
  return cats[type] || "other";
};

const adminGetTimeAgo = (date) => {
  try {
    const now = new Date();
    const d = new Date(date);
    const s = Math.floor((now - d) / 1000);
    if (s < 60) return "Just now";
    if (s < 3600) return `${Math.floor(s / 60)}m ago`;
    if (s < 86400) return `${Math.floor(s / 3600)}h ago`;
    if (s < 604800) return `${Math.floor(s / 86400)}d ago`;
    return d.toLocaleDateString();
  } catch {
    return "";
  }
};

const loadAdminNotifications = async () => {
  if (adminNotifications.value.length > 0) return;
  adminNotifLoading.value = true;
  try {
    if (!$api) return;
    const res = await $api.get("/admin/notifications", {
      params: { per_page: 5, page: 1 },
    });
    if (res.success) {
      adminNotifications.value = res.data.data || [];
    }
  } catch (e) {
    console.error("Failed to load admin notifications:", e);
  } finally {
    adminNotifLoading.value = false;
  }
};

const toggleAdminNotifications = () => {
  const wasOpen = showAdminNotifications.value;
  showAdminNotifications.value = !wasOpen;
  if (!wasOpen) {
    loadAdminNotifications();
  }
};

const handleAdminNotifClick = async (notif) => {
  if (!notif.is_read) {
    await adminMarkAsRead(notif.id);
  }
  // For admin drawer, no action_url navigation — just open full page for details
  showAdminNotifications.value = false;
  navigateTo("/AdminManagement/notifications");
};

const adminMarkAsRead = async (id) => {
  try {
    const res = await $api.post(`/admin/notifications/${id}/mark-read`);
    if (res.success) {
      const n = adminNotifications.value.find((x) => x.id === id);
      if (n) {
        n.is_read = true;
        n.read_at = new Date().toISOString();
      }
      pendingNotificationCount.value = Math.max(0, pendingNotificationCount.value - 1);
      window.dispatchEvent(new CustomEvent("admin-notifications-updated"));
    }
  } catch (e) {
    console.error("Mark read failed:", e);
  }
};

const adminMarkAllAsRead = async () => {
  try {
    const ids = adminNotifications.value.filter((n) => !n.is_read).map((n) => n.id);
    const res = await $api.post("/admin/notifications/mark-all-read", {
      scope: "displayed",
      ids,
    });
    if (res?.success) {
      adminNotifications.value.forEach((n) => {
        n.is_read = true;
        if (!n.read_at) n.read_at = new Date().toISOString();
      });
      pendingNotificationCount.value = 0;
      window.dispatchEvent(new CustomEvent("admin-notifications-updated"));
      if ($toast) {
        const count = res.marked_count ?? ids.length;
        $toast.success(`Marked ${count} notification${count === 1 ? "" : "s"} as read`);
      }
    } else {
      if ($toast) $toast.error(res?.message || "Failed to mark notifications as read");
    }
  } catch (e) {
    console.error("Mark all read failed:", e);
    if ($toast) $toast.error("Failed to mark all as read. Please try again.");
  }
};

const adminDeleteNotif = async (id) => {
  try {
    const res = await $api.delete(`/admin/notifications/${id}`);
    if (res.success) {
      const idx = adminNotifications.value.findIndex((x) => x.id === id);
      if (idx !== -1) {
        const n = adminNotifications.value[idx];
        if (!n.is_read) {
          pendingNotificationCount.value = Math.max(0, pendingNotificationCount.value - 1);
        }
        adminNotifications.value.splice(idx, 1);
      }
      window.dispatchEvent(new CustomEvent("admin-notifications-updated"));
    }
  } catch (e) {
    console.error("Delete notif failed:", e);
  }
};

const adminClearRead = async () => {
  if (!confirm("Delete all read notifications?")) return;
  try {
    const res = await $api.post("/admin/notifications/cleanup", { days: 0 });
    adminNotifications.value = adminNotifications.value.filter((n) => !n.is_read);
    if ($toast && (res.success || res.message)) $toast.success("Read notifications cleared");
    window.dispatchEvent(new CustomEvent("admin-notifications-updated"));
  } catch (e) {
    console.error("Clear read failed:", e);
  }
};

// Close dropdowns when clicking outside
onMounted(() => {
  isMounted.value = true;

  // Load admin brand (logo, favicon, app name)
  loadAdminBrandSettings();

  // Load unread feedback count
  loadUnreadFeedbackCount();

  // Load pending notification count
  loadPendingNotificationCount();

  // Refresh counts every 30 seconds
  const feedbackInterval = setInterval(() => {
    if (!isMounted.value) return;
    loadUnreadFeedbackCount();
  }, 30000);
  const notificationInterval = setInterval(() => {
    if (!isMounted.value) return;
    loadPendingNotificationCount();
  }, 30000);

  // Click outside handler for profile menu
  const handleClickOutside = (e) => {
    if (!isMounted.value) return;
    if (!profileMenuWrapperRef.value) return;
    const inProfileWrapper = e.target.closest(".profile-dropdown") ||
      profileMenuWrapperRef.value.contains(e.target);
    if (!inProfileWrapper) {
      showProfileMenu.value = false;
    }
  };

  // Escape key handler (drawer component handles its own Escape; sidebar + profile handled here)
  const handleEscape = (e) => {
    if (!isMounted.value) return;
    if (e.key === "Escape") {
      showProfileMenu.value = false;
      if (window.innerWidth < 1024) {
        sidebarOpen.value = false;
      }
    }
  };

  const handleFeedbackUpdated = () => {
    if (!isMounted.value) return;
    loadUnreadFeedbackCount();
  };

  const handleNotifUpdated = () => {
    if (!isMounted.value) return;
    loadPendingNotificationCount();
  };

  const handleBrandUpdated = () => {
    if (!isMounted.value) return;
    loadAdminBrandSettings();
  };

  document.addEventListener("click", handleClickOutside);
  document.addEventListener("keydown", handleEscape);

  onBeforeUnmount(() => {
    // FIRST: prevent any further updates before DOM detaches
    isMounted.value = false;
    clearInterval(feedbackInterval);
    clearInterval(notificationInterval);
    document.removeEventListener("click", handleClickOutside);
    document.removeEventListener("keydown", handleEscape);
    window.removeEventListener("admin-feedback-updated", handleFeedbackUpdated);
    window.removeEventListener("admin-notifications-updated", handleNotifUpdated);
    window.removeEventListener("admin-brand-updated", handleBrandUpdated);
  });

  // Listen for immediate updates from feedback page actions
  window.addEventListener("admin-feedback-updated", handleFeedbackUpdated);

  // Listen for immediate updates from notifications page actions
  window.addEventListener("admin-notifications-updated", handleNotifUpdated);

  // Listen for brand updates from configuration page
  window.addEventListener("admin-brand-updated", handleBrandUpdated);
});

// Watch for route changes to close popovers
watch(
  () => route.path,
  () => {
    if (!isMounted.value) return;
    sidebarOpen.value = false;
    showProfileMenu.value = false;
    showAdminNotifications.value = false;
  }
);
</script>

<style scoped>
.border-l-3 {
  border-left-width: 3px;
}
</style>
