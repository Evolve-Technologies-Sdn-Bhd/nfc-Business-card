<!-- layouts/AdminManagement.vue
     NFCGo Administration shell — "Ink & Brass".
     Deep-ink navigation rail + paper workspace + brass accents.
     All original behaviour is preserved: brand settings, notification &
     feedback counts (30s polling), notification drawer, profile menu,
     logout and every navigation destination. -->
<template>
  <div class="admin-shell h-screen overflow-hidden">
    <div class="flex h-full overflow-hidden">
      <!-- ============================ RAIL (desktop) ============================ -->
      <aside
        class="admin-rail hidden shrink-0 flex-col lg:flex"
        :class="{ 'admin-rail--collapsed': railCollapsed }"
      >
        <!-- Brand -->
        <div class="admin-rail-brand flex h-16 items-center px-4">
          <NuxtLink to="/AdminManagement" class="flex min-w-0 items-center gap-3">
            <template v-if="brandSettings.system_logo_url">
              <img
                :src="brandSettings.system_logo_url + '?v=' + brandVersion"
                :alt="brandSettings.app_name || 'NFCGo'"
                class="h-9 max-h-9 max-w-[46px] shrink-0 object-contain"
              />
              <span v-if="!railCollapsed" class="truncate text-h5 text-white">
                {{ brandSettings.app_name || "NFCGo" }}
              </span>
            </template>
            <NFCGoLogo
              v-else
              variant="dark"
              size="sm"
              :show-text="!railCollapsed"
              :label="brandSettings.app_name || 'NFCGo'"
            />
          </NuxtLink>
        </div>

        <!-- Navigation -->
        <nav class="admin-scroll flex-1 overflow-y-auto px-3 py-5">
          <div v-for="group in navigation" :key="group.category" class="admin-nav-group">
            <p v-if="!railCollapsed" class="admin-rail-label px-3 pb-2">{{ group.category }}</p>
            <NuxtLink
              v-for="item in group.items"
              :key="item.name"
              :to="item.href"
              class="admin-nav-link"
              :class="{ 'is-active': isActiveRoute(item.href) }"
              :title="railCollapsed ? item.name : ''"
            >
              <Icon :name="item.icon" class="admin-nav-ico h-[18px] w-[18px]" />
              <span v-if="!railCollapsed" class="truncate">{{ item.name }}</span>
              <span
                v-if="!railCollapsed && item.badge && Number(item.badge) > 0"
                class="ml-auto inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-error-500 px-1 text-badge text-white"
                :title="`${item.badge} unread`"
              >
                {{ item.badge }}
              </span>
            </NuxtLink>
          </div>
        </nav>

        <!-- Rail footer -->
        <div class="admin-rail-foot flex items-center justify-center gap-2 px-3 py-3">
          <button
            type="button"
            class="admin-icon-btn shrink-0 text-white/60 hover:bg-white/10 hover:text-white"
            :aria-label="railCollapsed ? 'Expand navigation' : 'Collapse navigation'"
            @click="toggleRail"
          >
            <Icon
              :name="railCollapsed ? 'heroicons:chevron-double-right' : 'heroicons:chevron-double-left'"
              class="h-5 w-5"
            />
          </button>
        </div>
      </aside>

      <!-- ============================ WORKSPACE ============================ -->
      <div class="admin-workspace flex flex-1 flex-col overflow-hidden">
        <!-- Topbar -->
        <header class="admin-topbar relative z-30 shrink-0">
          <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
            <!-- Mobile menu -->
            <button
              type="button"
              class="admin-icon-btn lg:hidden"
              aria-label="Toggle navigation"
              @click="mobileNavOpen = true"
            >
              <Icon name="heroicons:bars-3" class="h-6 w-6" />
            </button>

            <!-- Breadcrumb + title -->
            <div class="flex min-w-0 flex-col">
              <nav class="admin-crumb hidden items-center gap-1.5 sm:flex" aria-label="Breadcrumb">
                <NuxtLink to="/AdminManagement">Administration</NuxtLink>
                <span class="text-secondary-300">/</span>
                <span>{{ pageTitle }}</span>
              </nav>
              <h1 class="truncate text-h5 leading-tight text-secondary-900">{{ pageTitle }}</h1>
            </div>

            <!-- Right actions -->
            <div class="ml-auto flex items-center gap-1.5 sm:gap-2">
              <!-- Command search -->
              <button type="button" class="admin-search-trigger hidden md:inline-flex" @click="openCommand">
                <Icon name="heroicons:magnifying-glass" class="h-4 w-4" />
                <span>Search…</span>
                <span class="admin-kbd ml-2">
                  <Icon name="heroicons:command-line" class="h-3 w-3" />K
                </span>
              </button>
              <button type="button" class="admin-icon-btn md:hidden" aria-label="Search" @click="openCommand">
                <Icon name="heroicons:magnifying-glass" class="h-5 w-5" />
              </button>

              <!-- Admin notifications -->
              <div class="relative">
                <button
                  ref="adminNotifButtonRef"
                  type="button"
                  class="admin-icon-btn relative"
                  :class="{ 'bg-secondary-100 text-secondary-900': showAdminNotifications }"
                  aria-label="Notifications"
                  @click="toggleAdminNotifications"
                >
                  <Icon name="heroicons:bell" class="h-5 w-5" />
                  <span
                    v-if="pendingNotificationCount > 0"
                    class="absolute -right-0.5 -top-0.5 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-error-500 px-1 text-badge font-bold text-white ring-2 ring-white"
                  >
                    {{ pendingNotificationCount > 9 ? "9+" : pendingNotificationCount }}
                  </span>
                </button>
              </div>

              <!-- Feedback -->
              <NuxtLink
                to="/AdminManagement/feedback"
                class="admin-icon-btn relative"
                aria-label="User feedback"
              >
                <Icon name="heroicons:chat-bubble-left-right" class="h-5 w-5" />
                <span
                  v-if="unreadFeedbackCount > 0"
                  class="absolute -right-0.5 -top-0.5 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-warning-500 px-1 text-badge font-bold text-white ring-2 ring-white"
                >
                  {{ unreadFeedbackCount > 9 ? "9+" : unreadFeedbackCount }}
                </span>
              </NuxtLink>

              <div class="mx-1 hidden h-6 w-px bg-secondary-200 sm:block"></div>

              <!-- Profile menu -->
              <div ref="profileMenuWrapperRef" class="relative">
                <button
                  type="button"
                  class="flex items-center gap-3 rounded-xl p-1.5 transition-colors hover:bg-secondary-100"
                  @click="toggleProfileMenu"
                >
                  <div class="hidden text-right leading-tight sm:block">
                    <p class="max-w-[140px] truncate text-body font-semibold text-secondary-900">
                      {{
                        user?.first_name && user?.last_name
                          ? `${user.first_name} ${user.last_name}`
                          : user?.name || "Admin"
                      }}
                    </p>
                    <p class="max-w-[140px] truncate text-caption text-secondary-500">
                      {{ user?.admin_role_display || "Administrator" }}
                    </p>
                  </div>
                  <UserAvatar
                    :src="user?.profile_image"
                    :name="
                      user?.first_name && user?.last_name
                        ? `${user.first_name} ${user.last_name}`
                        : user?.name || 'Admin'
                    "
                    class="h-9 w-9 shrink-0 rounded-full shadow-sm ring-2 ring-white"
                  />
                </button>

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
                    class="profile-dropdown absolute right-0 z-[90] mt-2 w-72 overflow-hidden rounded-2xl border border-secondary-200 bg-white shadow-xl"
                  >
                    <div class="border-b border-secondary-200 bg-gradient-to-br from-accent-50 to-secondary-50 p-5">
                      <div class="flex items-center gap-3">
                        <UserAvatar
                          :src="user?.profile_image"
                          :name="
                            user?.first_name && user?.last_name
                              ? `${user.first_name} ${user.last_name}`
                              : user?.name || 'Admin'
                          "
                          class="h-12 w-12 rounded-full shadow-sm ring-2 ring-white"
                        />
                        <div class="min-w-0 flex-1">
                          <p class="truncate text-body font-bold text-secondary-900">
                            {{
                              user?.first_name && user?.last_name
                                ? `${user.first_name} ${user.last_name}`
                                : user?.name || "Admin"
                            }}
                          </p>
                          <p class="truncate text-caption text-secondary-500">
                            {{ user?.email || "admin@nfcgo.my" }}
                          </p>
                          <span class="mt-1 inline-flex items-center rounded-full bg-accent-100 px-2 py-0.5 text-badge font-bold text-accent-800">
                            {{ user?.admin_role_display || "Administrator" }}
                          </span>
                        </div>
                      </div>
                    </div>

                    <div class="py-2">
                      <NuxtLink
                        to="/AdminManagement/profile-settings"
                        class="flex w-full items-center gap-3 px-4 py-2.5 text-navigation text-secondary-700 transition-colors hover:bg-secondary-50 hover:text-secondary-900"
                        @click="showProfileMenu = false"
                      >
                        <Icon name="heroicons:user-circle" class="h-5 w-5 text-secondary-400" />
                        <span>Profile Setting</span>
                      </NuxtLink>
                      <NuxtLink
                        to="/AdminManagement/configuration"
                        class="flex w-full items-center gap-3 px-4 py-2.5 text-navigation text-secondary-700 transition-colors hover:bg-secondary-50 hover:text-secondary-900"
                        @click="showProfileMenu = false"
                      >
                        <Icon name="heroicons:cog-6-tooth" class="h-5 w-5 text-secondary-400" />
                        <span>Configuration</span>
                      </NuxtLink>
                    </div>

                    <div class="border-t border-secondary-200"></div>

                    <div class="py-2">
                      <button
                        type="button"
                        class="flex w-full items-center gap-3 px-4 py-2.5 text-navigation text-error-600 transition-colors hover:bg-error-50 hover:text-error-700"
                        @click="handleLogout"
                      >
                        <Icon name="heroicons:arrow-right-on-rectangle" class="h-5 w-5 text-error-400" />
                        <span>Logout</span>
                      </button>
                    </div>
                  </div>
                </Transition>
              </div>
            </div>
          </div>
        </header>

        <!-- Content -->
        <main class="admin-main flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
          <div class="admin-container">
            <slot />
          </div>
        </main>
      </div>
    </div>

    <!-- ============================ MOBILE NAV ============================ -->
    <Transition name="admin-fade">
      <div v-if="mobileNavOpen" class="fixed inset-0 z-[110] lg:hidden">
        <div class="absolute inset-0 bg-black/55" @click="mobileNavOpen = false"></div>
        <aside class="admin-rail absolute inset-y-0 left-0 flex w-72 flex-col shadow-2xl">
          <div class="admin-rail-brand flex h-16 items-center justify-between px-4">
            <NuxtLink to="/AdminManagement" class="flex min-w-0 items-center gap-3" @click="mobileNavOpen = false">
              <NFCGoLogo variant="dark" size="sm" :label="brandSettings.app_name || 'NFCGo'" />
            </NuxtLink>
            <button
              type="button"
              class="admin-icon-btn text-white/70 hover:bg-white/10 hover:text-white"
              aria-label="Close navigation"
              @click="mobileNavOpen = false"
            >
              <Icon name="heroicons:x-mark" class="h-5 w-5" />
            </button>
          </div>
          <nav class="admin-scroll flex-1 overflow-y-auto px-3 py-5">
            <div v-for="group in navigation" :key="group.category" class="admin-nav-group">
              <p class="admin-rail-label px-3 pb-2">{{ group.category }}</p>
              <NuxtLink
                v-for="item in group.items"
                :key="item.name"
                :to="item.href"
                class="admin-nav-link"
                :class="{ 'is-active': isActiveRoute(item.href) }"
                @click="mobileNavOpen = false"
              >
                <Icon :name="item.icon" class="admin-nav-ico h-[18px] w-[18px]" />
                <span class="truncate">{{ item.name }}</span>
                <span
                  v-if="item.badge && Number(item.badge) > 0"
                  class="ml-auto inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-error-500 px-1 text-badge text-white"
                >
                  {{ item.badge }}
                </span>
              </NuxtLink>
            </div>
          </nav>
        </aside>
      </div>
    </Transition>

    <!-- ============================ COMMAND PALETTE ============================ -->
    <Transition name="admin-fade">
      <div v-if="commandOpen" class="admin-cmdk-backdrop" @click.self="closeCommand">
        <div class="admin-cmdk" role="dialog" aria-modal="true" aria-label="Search administration">
          <div class="flex items-center gap-3 border-b border-secondary-200 px-4 py-3">
            <Icon name="heroicons:magnifying-glass" class="h-5 w-5 text-secondary-400" />
            <input
              ref="commandInputRef"
              v-model="commandQuery"
              type="text"
              class="w-full border-0 bg-transparent p-0 text-body text-secondary-900 placeholder-secondary-400 focus:outline-none focus:ring-0"
              placeholder="Jump to a page…"
              @keydown.down.prevent="moveCommand(1)"
              @keydown.up.prevent="moveCommand(-1)"
              @keydown.enter.prevent="runCommand"
            />
            <span class="admin-kbd">ESC</span>
          </div>
          <div class="admin-scroll max-h-[22rem] overflow-y-auto p-2">
            <NuxtLink
              v-for="(item, i) in filteredCommands"
              :key="item.href"
              :to="item.href"
              class="admin-cmdk-item"
              :class="{ 'is-active': i === commandIndex }"
              @click="closeCommand"
              @mouseenter="commandIndex = i"
            >
              <Icon :name="item.icon" class="h-[18px] w-[18px] text-secondary-500" />
              <span class="flex-1 truncate">{{ item.name }}</span>
              <span class="text-caption text-secondary-400">{{ item.category }}</span>
            </NuxtLink>
            <p v-if="filteredCommands.length === 0" class="px-3 py-8 text-center text-body text-secondary-500">
              No pages match “{{ commandQuery }}”.
            </p>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Notification drawer -->
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

// Lock the viewport so the document never grows a scrollbar of its own.
// All vertical scrolling is owned by <main class="admin-main"> inside this
// shell. Without this, sub-pixel rounding of 100vh can leak 1px and the
// browser paints a second (page-level) scrollbar next to the content one.
useHead({
  htmlAttrs: { class: "shell-scroll-lock" },
});

const mobileNavOpen = ref(false);
const railCollapsed = ref(false);
const showProfileMenu = ref(false);
const showAdminNotifications = ref(false);
const profileMenuWrapperRef = ref(null);
const profileMenuPanelRef = ref(null);
const adminNotifButtonRef = ref(null);
const unreadFeedbackCount = ref(0);
const pendingNotificationCount = ref(0);
const adminNotifications = ref([]);
const adminNotifLoading = ref(false);

// Brand settings (custom logo, app name)
const brandSettings = reactive({
  app_name: 'NFCGo',
  system_logo_url: '',
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

      brandVersion.value = Date.now();
      applyFavicon();
      updateDocumentTitle();
    }
  } catch (e) {
    console.log('Admin brand: using defaults');
  }
};

// Favicon is hardcoded to the official NFCGo system logo so it always
// matches the system branding, regardless of admin-configured favicon.
const SYSTEM_FAVICON_URL = '/nfcgo-app-icon.svg';

const applyFavicon = () => {
  if (!process.client) return;
  let link = document.querySelector("link[rel~='icon']");
  if (!link) {
    link = document.createElement('link');
    link.rel = 'icon';
    document.getElementsByTagName('head')[0].appendChild(link);
  }
  link.href = SYSTEM_FAVICON_URL;
  link.type = 'image/svg+xml';
};

const updateDocumentTitle = () => {
  if (!process.client) return;
  const name = brandSettings.app_name || 'NFCGo';
  const existing = document.title;
  if (!existing.includes(name)) {
    document.title = existing + ' — ' + name;
  }
};

// Rail collapse (desktop) — persisted so the choice survives reloads
const toggleRail = () => {
  railCollapsed.value = !railCollapsed.value;
  if (process.client) {
    localStorage.setItem('nfcgo.admin.railCollapsed', railCollapsed.value ? '1' : '0');
  }
};

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
        icon: "heroicons:squares-2x2",
      },
    ],
  },
  {
    category: "Users",
    items: [
      {
        name: "User Management",
        href: "/AdminManagement/users",
        icon: "heroicons:users",
      },
      {
        name: "Business Users",
        href: "/AdminManagement/business-users",
        icon: "heroicons:briefcase",
      },
      {
        name: "Business Enquiries",
        href: "/AdminManagement/business-inquiries",
        icon: "heroicons:envelope",
      },
      {
        name: "Roles & Permissions",
        href: "/AdminManagement/roles-permissions",
        icon: "heroicons:shield-check",
      },
    ],
  },
  {
    category: "Cards",
    items: [
      {
        name: "NFC Card Management",
        href: "/AdminManagement/nfc-cards",
        icon: "heroicons:credit-card",
      },
      {
        name: "Card Templates",
        href: "/AdminManagement/card-templates",
        icon: "heroicons:rectangle-stack",
      },
      {
        name: "Card Designer",
        href: "/AdminManagement/card-designer",
        icon: "heroicons:paint-brush",
      },
      {
        name: "Profile Builder Design",
        href: "/AdminManagement/profile-builder-design",
        icon: "heroicons:swatch",
      },
      {
        name: "Invoices",
        href: "/AdminManagement/invoices",
        icon: "heroicons:document-text",
      },
      {
        name: "Refunds",
        href: "/AdminManagement/refunds",
        icon: "heroicons:arrow-uturn-left",
      },
    ],
  },
  {
    category: "System",
    items: [
      {
        name: "Analytics",
        href: "/AdminManagement/stats",
        icon: "heroicons:chart-bar",
      },
      {
        name: "Notifications",
        href: "/AdminManagement/notifications",
        icon: "heroicons:bell",
        badge: pendingNotificationCount.value,
      },
      {
        name: "User Feedback",
        href: "/AdminManagement/feedback",
        icon: "heroicons:chat-bubble-left-right",
        badge: unreadFeedbackCount.value,
      },
      {
        name: "Chatbot FAQ",
        href: "/AdminManagement/chatbot-faq",
        icon: "heroicons:question-mark-circle",
      },
      {
        name: "Audit Log",
        href: "/AdminManagement/SystemAuditLog",
        icon: "heroicons:clipboard-document-list",
      },
    ],
  },
  {
    category: "Account",
    items: [
      {
        name: "Profile Setting",
        href: "/AdminManagement/profile-settings",
        icon: "heroicons:user-circle",
      },
      {
        name: "Configuration",
        href: "/AdminManagement/configuration",
        icon: "heroicons:cog-6-tooth",
      },
    ],
  },
]);

// Flattened navigation for lookups (page title + active highlighting)
const flatNavigation = computed(() =>
  navigation.value.flatMap((group) =>
    group.items.map((item) => ({ ...item, category: group.category })),
  ),
);

// Resolve the navigation item for a path, preferring the most specific
// (longest) matching href. This keeps nested routes such as
// /AdminManagement/invoices/1 resolving to "Invoices" instead of the
// parent "/AdminManagement" dashboard entry.
const resolveNavItem = (path) => {
  let match = null;
  for (const item of flatNavigation.value) {
    if (path === item.href || path.startsWith(item.href + "/")) {
      if (!match || item.href.length > match.href.length) {
        match = item;
      }
    }
  }
  return match;
};

// Currently active navigation item (also drives the top-bar page title)
const activeNavItem = computed(() => resolveNavItem(route.path));

// Page title based on current route
const pageTitle = computed(() => activeNavItem.value?.name || "Admin Panel");

// A nav link is active only if it is the most specific match
const isActiveRoute = (href) => activeNavItem.value?.href === href;

// ------------------------------------------------------------------
// Command palette
// ------------------------------------------------------------------
const commandOpen = ref(false);
const commandQuery = ref("");
const commandIndex = ref(0);
const commandInputRef = ref(null);

const filteredCommands = computed(() => {
  const q = commandQuery.value.trim().toLowerCase();
  if (!q) return flatNavigation.value;
  return flatNavigation.value.filter(
    (item) =>
      item.name.toLowerCase().includes(q) ||
      item.category.toLowerCase().includes(q),
  );
});

const openCommand = () => {
  commandOpen.value = true;
  commandQuery.value = "";
  commandIndex.value = 0;
  nextTick(() => commandInputRef.value?.focus());
};

const closeCommand = () => {
  commandOpen.value = false;
};

const moveCommand = (delta) => {
  const len = filteredCommands.value.length;
  if (!len) return;
  commandIndex.value = (commandIndex.value + delta + len) % len;
};

const runCommand = () => {
  const item = filteredCommands.value[commandIndex.value];
  if (!item) return;
  closeCommand();
  navigateTo(item.href);
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

  // Restore rail preference
  if (process.client) {
    railCollapsed.value = localStorage.getItem('nfcgo.admin.railCollapsed') === '1';
  }

  // Load admin brand (logo, app name)
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

  // Global keyboard shortcuts — Escape closes popovers; ⌘/Ctrl+K opens search
  const handleKeydown = (e) => {
    if (!isMounted.value) return;
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === "k") {
      e.preventDefault();
      commandOpen.value ? closeCommand() : openCommand();
      return;
    }
    if (e.key === "Escape") {
      if (commandOpen.value) {
        closeCommand();
        return;
      }
      showProfileMenu.value = false;
      mobileNavOpen.value = false;
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
  document.addEventListener("keydown", handleKeydown);

  onBeforeUnmount(() => {
    // FIRST: prevent any further updates before DOM detaches
    isMounted.value = false;
    clearInterval(feedbackInterval);
    clearInterval(notificationInterval);
    document.removeEventListener("click", handleClickOutside);
    document.removeEventListener("keydown", handleKeydown);
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
    mobileNavOpen.value = false;
    showProfileMenu.value = false;
    showAdminNotifications.value = false;
    commandOpen.value = false;
  }
);
</script>

<style scoped>
/*
  Page-level loaders inside this layout (NFCGoWaveLoader `.nfg-viewport`)
  centre on the VISIBLE content area: subtract the sticky top bar (h-16 =
  4rem) plus the main padding above + below (1rem / 2rem).
*/
.admin-main {
  --nfg-viewport-offset: 6rem;
}
@media (min-width: 1024px) {
  .admin-main {
    --nfg-viewport-offset: 8rem;
  }
}
</style>
