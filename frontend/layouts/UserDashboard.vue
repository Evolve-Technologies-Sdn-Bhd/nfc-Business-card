<!-- layouts/UserDashboard.vue -->
<template>
  <div class="min-h-screen bg-secondary-50">
    <!-- Global Route Loading Indicator -->
    <Transition
      enter-active-class="transition-opacity duration-150"
      leave-active-class="transition-opacity duration-150"
      enter-from-class="opacity-0"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isPageLoading"
        class="fixed top-0 left-0 right-0 h-1 bg-blue-600 z-[100] animate-pulse"
        style="box-shadow: 0 0 10px rgba(37, 99, 235, 0.5)"
      ></div>
    </Transition>

    <!-- Desktop Layout Container -->
    <div class="flex h-screen overflow-hidden">
      <!-- Sidebar -->
      <div
        class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:static lg:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
      >
        <div class="flex flex-col h-full">
          <!-- Logo -->
          <div
            class="flex items-center justify-center h-16 px-4 border-b border-secondary-200 flex-shrink-0"
          >
            <NuxtLink to="/" class="flex items-center">
              <Icon
                name="heroicons:id-card"
                class="h-8 w-8 text-primary-600"
              />
              <span class="ml-2 text-xl font-bold text-secondary-900"
                >NFCGo</span
              >
            </NuxtLink>
          </div>

          <!-- Navigation - Scrollable -->
          <nav class="flex-1 overflow-y-auto px-4 py-6">
            <div class="space-y-1">
              <!--
                ─── SIDEBAR NAV: PREVENT RAPID CLICK SPAMMING ──────────────────
                Single `<NuxtLink v-for>` — no template wrapper (Vue Vite virtual
                compiler trips on <template v-for> + v-if sibling wrappers in layouts).
                Three levels of guard:
                (a) Visual + click guard: `:class` adds pointer-events-none + opacity
                    while navigation is in progress.
                (b) Permanent disabled (no permission/locked): render a plain <div>
                    with cursor-not-allowed ONLY when item.disabled is true; this
                    branch also produces no NuxtLink.
                (c) Router-level hard block (in router.client.js): `next(false)`
                    aborts duplicated navigation attempts entirely.
              -->
              <!-- (1) Normal clickable items (not permanently disabled) -->
              <NuxtLink
                v-for="item in navigation.filter(n => !(n.disabled))"
                :key="item.name"
                :to="item.href"
                :class="[
                  'group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200',
                  isActiveRoute(item.href)
                    ? 'bg-primary-50 text-primary-700 border-l-3 border-primary-700'
                    : 'text-secondary-600 hover:bg-secondary-50 hover:text-secondary-900',
                  (routeNavigationInProgress && !isActiveRoute(item.href))
                    ? 'pointer-events-none opacity-60 cursor-not-allowed select-none'
                    : '',
                ]"
              >
                {{ item.name }}
                <span
                  v-if="item.badge"
                  class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800"
                >
                  {{ item.badge }}
                </span>
              </NuxtLink>
              <!-- (2) Permanently locked items: no NuxtLink (no href, no navigation) -->
              <div
                v-for="item in navigation.filter(n => !!n.disabled)"
                :key="'lock-' + item.name"
                :title="item.disabledMessage || 'This feature is locked — please complete onboarding first.'"
                :class="[
                  'group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200',
                  'text-secondary-400 opacity-70 cursor-not-allowed select-none',
                ]"
              >
                {{ item.name }}
                <span
                  v-if="item.badge"
                  class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600"
                >
                  {{ item.badge }}
                </span>
              </div>
            </div>
          </nav>

          <!-- Upgrade Banner - Bottom of sidebar (only non-Business users).
               Flex-shrink + explicit sizing so it never overlaps scrollable nav. -->
          <div
            v-if="authStore.user?.subscription_plan !== 'business'"
            class="p-4 border-t border-secondary-200 flex-shrink-0 bg-white"
          >
            <div
              class="bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl p-4 text-white shadow-sm"
            >
              <div class="flex items-start gap-3">
                <div class="flex-shrink-0 mt-0.5">
                  <Icon
                    name="heroicons:star"
                    class="h-5 w-5 text-yellow-300"
                  />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-semibold leading-snug truncate">
                    {{ authStore.user?.subscription_plan === 'premium' ? 'Upgrade to Business' : 'Upgrade to Premium' }}
                  </p>
                  <p class="text-xs text-primary-100 mt-0.5 leading-relaxed">
                    {{ authStore.user?.subscription_plan === 'premium' ? 'Team seats + advanced analytics' : 'Unlock the full Profile Builder' }}
                  </p>
                </div>
              </div>
              <button
                @click="navigateTo('/UserDashboard/PlanSelection')"
                class="mt-3.5 w-full bg-white text-primary-600 py-2 px-4 rounded-lg text-sm font-semibold hover:bg-primary-50 transition-colors shadow-sm whitespace-nowrap overflow-hidden text-ellipsis flex items-center justify-center gap-1.5"
              >
                <Icon name="heroicons:arrow-trending-up" class="h-3.5 w-3.5 flex-shrink-0" />
                <span class="truncate">Upgrade Now</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Area -->
      <div
        class="flex-1 flex flex-col min-w-0 overflow-hidden w-full lg:w-auto"
      >
        <!-- Top Bar (Desktop + Mobile) -->
        <header
          class="bg-white border-b border-secondary-200 flex-shrink-0 shadow-sm"
        >
          <div
            class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16"
          >
            <!-- Left: Mobile menu button (mobile only) + Page title (desktop) -->
            <div class="flex items-center gap-3">
              <button
                @click="sidebarOpen = !sidebarOpen"
                class="lg:hidden p-2 rounded-md text-secondary-600 hover:text-secondary-900 hover:bg-secondary-100 transition-colors"
              >
                <Icon
                  :name="sidebarOpen ? 'heroicons:x-mark' : 'heroicons:bars-3'"
                  class="h-6 w-6"
                />
              </button>
              <h1
                v-if="pageTitle"
                class="hidden lg:block text-lg font-semibold text-secondary-900"
              >
                {{ pageTitle }}
              </h1>
              <h1
                v-else
                class="lg:hidden text-lg font-semibold text-secondary-900"
              >
                NFCGo
              </h1>
            </div>

            <!-- Right: Actions -->
            <div class="flex items-center gap-2 sm:gap-4">
              <!-- Theme Switcher -->
              <ThemeSwitcher />

              <!-- Notifications Button -->
              <div ref="notifButtonWrapperRef" class="relative">
                <button
                  ref="notifButtonRef"
                  @click="toggleNotifications"
                  class="notifications-dropdown relative p-2 rounded-lg text-secondary-500 hover:text-secondary-700 hover:bg-secondary-100 transition-colors"
                  :class="{
                    'bg-primary-50 text-primary-600': showNotifications,
                  }"
                >
                  <Icon
                    name="heroicons:bell"
                    class="h-5 w-5"
                  />
                  <span
                    v-if="unreadCount > 0"
                    class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center h-5 min-w-[1.25rem] px-1 rounded-full text-[0.65rem] font-bold bg-red-500 text-white ring-2 ring-white"
                  >
                    {{ unreadCount > 9 ? "9+" : unreadCount }}
                  </span>
                </button>
              </div>

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
                          : user?.name || "User"
                      }}
                    </p>
                    <p class="text-xs text-secondary-500 truncate max-w-[140px]">
                      {{ user?.subscription_plan ? user.subscription_plan.charAt(0).toUpperCase() + user.subscription_plan.slice(1) : 'User' }}
                    </p>
                  </div>
                  <div class="flex-shrink-0">
                    <img
                      :src="user?.account_image || '/default-avatar.png'"
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
                    <!-- User Info Header -->
                    <div class="p-5 bg-gradient-to-br from-primary-50 to-secondary-50 border-b border-secondary-200">
                      <div class="flex items-center gap-3">
                        <img
                          :src="user?.account_image || '/default-avatar.png'"
                          :alt="user?.name"
                          class="h-12 w-12 rounded-full object-cover ring-2 ring-white shadow-sm"
                        />
                        <div class="min-w-0 flex-1">
                          <p class="text-sm font-bold text-secondary-900 truncate">
                            {{
                              user?.first_name && user?.last_name
                                ? `${user.first_name} ${user.last_name}`
                                : user?.name || "User"
                            }}
                          </p>
                          <p class="text-xs text-secondary-500 truncate">
                            {{ user?.email }}
                          </p>
                        </div>
                      </div>
                    </div>

                    <!-- Menu Items -->
                    <div class="py-2">
                      <button
                        @click="showProfilePreview = true; showProfileMenu = false"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-secondary-700 hover:bg-secondary-50 hover:text-secondary-900 transition-colors"
                      >
                        <Icon
                          name="heroicons:eye"
                          class="h-5 w-5 text-secondary-400"
                        />
                        <span>Preview Profile</span>
                      </button>

                      <button
                        @click="navigateTo('/UserDashboard/Notifications'); showProfileMenu = false"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-secondary-700 hover:bg-secondary-50 hover:text-secondary-900 transition-colors"
                      >
                        <Icon
                          name="heroicons:bell"
                          class="h-5 w-5 text-secondary-400"
                        />
                        <span>All Notifications</span>
                        <span
                          v-if="unreadCount > 0"
                          class="ml-auto inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-primary-100 text-primary-700"
                        >
                          {{ unreadCount > 9 ? "9+" : unreadCount }}
                        </span>
                      </button>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-secondary-200"></div>

                    <!-- Logout -->
                    <div class="py-2">
                      <button
                        @click="handleLogout"
                        :disabled="logoutLoading"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        <div v-if="logoutLoading" class="spinner"></div>
                        <Icon
                          v-else
                          name="heroicons:arrow-right-on-rectangle"
                          class="h-5 w-5 text-red-400"
                        />
                        <span>{{ logoutLoading ? "Signing out..." : "Sign Out" }}</span>
                      </button>
                    </div>
                  </div>
                </Transition>
              </div>
            </div>
          </div>
        </header>

        <!-- Page Content - Scrollable -->
        <main class="flex-1 overflow-y-auto bg-secondary-50 w-full">
          <div class="p-4 sm:p-6 lg:p-8 w-full">
            <div class="max-w-7xl mx-auto w-full">
              <slot />
            </div>
          </div>
        </main>
      </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <Transition
      enter-active-class="transition-opacity ease-linear duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity ease-linear duration-300"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="sidebarOpen"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-black bg-opacity-25 lg:hidden"
      ></div>
    </Transition>

    <!-- Profile Preview Modal -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="showProfilePreview"
          class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        >
          <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <div
              v-if="showProfilePreview"
              class="bg-white rounded-2xl p-6 max-w-md w-full max-h-[90vh] overflow-y-auto"
            >
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-secondary-900">
                  Profile Preview
                </h3>
                <button
                  @click="showProfilePreview = false"
                  class="p-2 hover:bg-secondary-100 rounded-lg transition-colors"
                >
                  <Icon
                    name="heroicons:x-mark"
                    class="h-6 w-6 text-secondary-600"
                  />
                </button>
              </div>
              <!-- Profile preview content -->
              <div v-if="hasPreviewProfile" class="space-y-3">
                <div class="flex items-center justify-between text-sm text-secondary-500">
                  <span>
                    Live preview of your public landing page
                  </span>
                  <a
                    :href="userPreviewProfileUrl"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-700 font-medium transition-colors"
                  >
                    <Icon name="heroicons:arrow-top-right-on-square" class="w-4 h-4" />
                    Open in new tab
                  </a>
                </div>
                <div class="overflow-hidden rounded-xl border border-secondary-200 shadow-sm bg-secondary-50">
                  <iframe
                    :src="userPreviewProfileUrl"
                    title="Profile Preview"
                    class="block w-full bg-white"
                    :style="{ height: '70vh', minHeight: '480px', border: 0 }"
                    sandbox="allow-scripts allow-same-origin allow-forms allow-popups"
                  />
                </div>
              </div>
              <div v-else class="bg-secondary-50 rounded-xl p-8 text-center space-y-4 border border-dashed border-secondary-200">
                <div class="mx-auto w-14 h-14 flex items-center justify-center rounded-full bg-white shadow-sm border border-secondary-200">
                  <Icon name="heroicons:id-card" class="w-7 h-7 text-secondary-400" />
                </div>
                <div class="space-y-1">
                  <h4 class="font-semibold text-secondary-800">No profile available yet</h4>
                  <p class="text-sm text-secondary-500">
                    Create your first NFC card and set up your profile landing page to preview it here.
                  </p>
                </div>
                <NuxtLink
                  to="/UserDashboard/CardManagement"
                  class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 transition-colors"
                  @click="showProfilePreview = false"
                >
                  Go to Card Management
                </NuxtLink>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>

    <!-- Simple Notification Drawer (Top 5) -->
    <NotificationDrawer
      :is-open="showNotifications"
      :button-ref="notifButtonRef"
      :notifications="notifications"
      :unread-count="unreadCount"
      :loading="notificationsLoading"
      :get-icon="getNotificationIcon"
      :get-time-ago="getTimeAgo"
      :get-category="getNotificationCategory"
      view-all-href="/UserDashboard/Notifications"
      @close="showNotifications = false"
      @item-click="handleNotificationClick"
      @mark-read="markAsRead"
      @mark-all-read="handleMarkAllAsRead"
      @delete="handleDeleteNotification"
      @clear-read="handleDeleteAllRead"
    />
  </div>
</template>

<script setup>
// ─── Explicit import: navigation-loading state exported by router.client plugin
//     (this one cannot be auto-imported by Nuxt since it's our custom module)
import { routeNavigationInProgress } from "~/plugins/router.client.js";

// Stores
const authStore = useAuthStore();
const { $toast } = useNuxtApp();
const route = useRoute();

// Mount guard to prevent post-unmount updates and teleport races
const isMounted = ref(false);

// Page loading state
const isPageLoading = ref(false);

// Watch for route changes using route watcher instead of router guards
watch(
  () => route.path,
  (newPath, oldPath) => {
    if (!isMounted.value) return;
    if (newPath !== oldPath) {
      isPageLoading.value = true;
      showNotifications.value = false;
      showProfileMenu.value = false;
      // Reset loading state after a short delay
      setTimeout(() => {
        if (!isMounted.value) return;
        isPageLoading.value = false;
      }, 300);
    }
  }
);

// Notifications composable
const {
  notifications,
  unreadCount,
  loading: notificationsLoading,
  loadNotifications,
  loadUnreadCount,
  markAsRead,
  markAllAsRead,
  deleteNotification,
  deleteAllRead,
  getTimeAgo,
  getNotificationIcon,
  getNotificationCategory,
  getPriorityClass,
} = useNotifications();

// Reactive data
const sidebarOpen = ref(false);
const showProfilePreview = ref(false);
const showNotifications = ref(false);
const showProfileMenu = ref(false);
const logoutLoading = ref(false);
const notifButtonRef = ref(null);
const profileMenuWrapperRef = ref(null);
const profileMenuPanelRef = ref(null);

// Computed
const user = computed(() => authStore.user);

// Page title based on current route
const flatNavigation = computed(() => navigation.value);
const pageTitle = computed(() => {
  const currentNav = flatNavigation.value.find((item) => item.href === route.path);
  return currentNav ? currentNav.name : "Dashboard";
});

const userPreviewProfileUrl = computed(() => {
  const cards = user.value?.nfc_cards ?? user.value?.nfcCards;
  if (!Array.isArray(cards) || cards.length === 0) return null;
  const primary = cards[0];
  if (!primary) return null;

  let base = '';
  if (typeof primary.public_url === 'string' && primary.public_url.startsWith('/profile/')) {
    base = primary.public_url;
  }
  if (!base) {
    const authUser = user.value || {};
    const userSlug = authUser.name_slug;
    const localCardNumber = cards.length > 0
      ? cards
          .slice()
          .sort((a, b) => (new Date(a.created_at || a.createdAt || 0) - new Date(b.created_at || b.createdAt || 0)) || ((a.id ?? 0) - (b.id ?? 0)))
          .findIndex(c => c.id === primary.id) + 1
      : null;
    if (userSlug && (localCardNumber || primary.card_number)) {
      base = `/profile/${userSlug}/${localCardNumber || primary.card_number}`;
      if (authUser.id != null && authUser.has_name_slug_clash === true) {
        base += '/userid-' + authUser.id;
      }
    }
  }
  if (!base) {
    base = `/profile/${primary.nfc_card_id || primary.id}`;
  }
  return base;
});

const hasPreviewProfile = computed(() => !!userPreviewProfileUrl.value);

// Development mode check
const isDevelopment = computed(() => {
  if (process.client) {
    return (
      window.location.hostname === "localhost" ||
      window.location.hostname === "127.0.0.1" ||
      window.location.hostname.includes("192.168") ||
      window.location.hostname.includes("172.19")
    );
  }
  return false;
});

// Plan-aware navigation items — priority NFC order plan (payment_verified+) over user.subscription_plan (activated only after delivered)
const navigation = computed(() => {
  const plan = authStore.resolvedSubscriptionPlan || "free";
  const isBusinessPlan = plan === "business";
  const isPremiumPlan = plan === "premium";
  const isBasicPlan = plan === "basic";
  const isFreePlan = plan === "free";

  // Permission helper: Profile Builder boleh access jika:
  // - user.subscription_active === true, ATAU
  // - user ada sekurang-kurangnya 1 NFC card dengan status >= payment_verified
  // - free users selalunya ada active sub, tapi fallback show juga
  const canAccessProfileBuilder =
    authStore.user?.subscription_active === true ||
    authStore.hasAccessPaidBuilderCards ||
    isFreePlan;

  const baseNavigation = [];

  // ─── Dashboard (ITEM #1 SIDE BAR — landing page after login)
  baseNavigation.push({
    name: "Dashboard",
    href: "/UserDashboard/",
    icon: "heroicons:square-3-stack-3d",
    disabled: false,
  });

  // ─── Card Management (ITEM #2 SIDE BAR — primary user action: manage cards, payments, activation)
  // SELALU accessible jika user ada order (walaupun pending), guard dalam onMounted page handle redirect
  const hasAnyCardOrder = authStore.hasAnyPhysicalNfcCard || authStore.user?.subscription_active === true;
  baseNavigation.push({
    name: "Card Management",
    href: isBusinessPlan
      ? "/UserDashboard/UserManagement/BusinessPlanUser/BusinessCardManagement"
      : "/UserDashboard/CardManagement",
    icon: "heroicons:credit-card",
    badge: plan.charAt(0).toUpperCase() + plan.slice(1),
    disabled: false,
  });

  // ─── Profile Builder (ITEM #2 SIDE BAR — secondary: design landing page after card is active)
  baseNavigation.push({
    name: "Profile Builder",
    href: isBusinessPlan
      ? "/UserDashboard/UserManagement/BusinessPlanUser/BusinessProfileBuilder"
      : isPremiumPlan
      ? "/UserDashboard/UserManagement/PremiumPlanUser/PremiumProfileBuilder"
      : isBasicPlan
      ? "/UserDashboard/UserManagement/BasicPlanUser/BasicProfileBuilder"
      : "/UserDashboard/UserManagement/FreePlanUser/FreeProfileBuilder",
    icon: "heroicons:user",
    disabled: !canAccessProfileBuilder,
  });

  // Analytics — hanya enable bila subscription active (data analytics empty sebelum active)
  const canAccessAnalytics = authStore.user?.subscription_active === true || authStore.hasAccessPaidBuilderCards;
  baseNavigation.push({
    name: "Analytics",
    href: isBusinessPlan
      ? "/UserDashboard/UserManagement/BusinessPlanUser/BusinessAnalytics"
      : isPremiumPlan
      ? "/UserDashboard/UserManagement/PremiumPlanUser/PremiumAnalytics"
      : "/UserDashboard/Analytics",
    icon: "heroicons:chart-bar",
    disabled: !canAccessAnalytics,
  });

  // Only add Employee Management and Activity Log for Business plan users
  if (isBusinessPlan && (authStore.user?.subscription_active === true || authStore.hasAccessPaidBuilderCards)) {
    baseNavigation.push({
      name: "Employee Management",
      href: "/UserDashboard/UserManagement/BusinessPlanUser/BusinessEmployeeManagement",
      icon: "heroicons:users",
    });
    baseNavigation.push({
      name: "Activity Log",
      href: "/UserDashboard/UserManagement/BusinessPlanUser/BusinessActivityLog",
      icon: "heroicons:document-text",
    });
  }

  baseNavigation.push({
    name: "Notifications",
    href: "/UserDashboard/Notifications",
    icon: "heroicons:bell",
    badge: unreadCount.value > 0 ? (unreadCount.value > 9 ? "9+" : unreadCount.value) : null,
  });

  baseNavigation.push({
    name: "Settings",
    href: "/UserDashboard/Settings",
    icon: "heroicons:cog-6-tooth",
  });

  return baseNavigation;
});

// Check if route is active
const isActiveRoute = (href) => {
  const route = useRoute();

  // Special case for UserDashboard root - should be active for /UserDashboard/ and /UserDashboard
  if (href === "/UserDashboard/") {
    return route.path === "/UserDashboard/" || route.path === "/UserDashboard";
  }

  // Special case for Analytics - should be active for any analytics route
  if (href === "/UserDashboard/Analytics") {
    return route.path.includes("/Analytics");
  }

  // For dynamic routes like analytics
  if (href.includes("[id]")) {
    const regex = new RegExp(href.replace("[id]", "\\d+"));
    return regex.test(route.path);
  }

  return route.path === href;
};

// Profile menu handlers
const toggleProfileMenu = () => {
  showProfileMenu.value = !showProfileMenu.value;
};

// Handle logout
const handleLogout = async () => {
  // Show confirmation dialog
  if (!confirm("Are you sure you want to sign out?")) {
    return;
  }

  showProfileMenu.value = false;
  logoutLoading.value = true;
  try {
    // Prevent any further reactive updates before unmount
    isMounted.value = false;
    // Close any open modals/drawers that use Teleport to avoid unmount race
    showProfilePreview.value = false;
    showNotifications.value = false;
    await authStore.logout();
    $toast.success("Logged out successfully");
    // Now navigate to login — single point of navigation to avoid double call race
    await navigateTo("/UserAccount/login", { replace: true });
  } catch (error) {
    console.error("Logout error:", error);
    $toast.error("Error logging out");
  } finally {
    logoutLoading.value = false;
  }
};

// Notification handlers
const toggleNotifications = () => {
  const wasOpen = showNotifications.value;
  showNotifications.value = !wasOpen;

  if (!wasOpen && notifications.value.length === 0) {
    loadNotifications(5);
  }
};

const handleNotificationClick = async (notification) => {
  if (!notification.is_read) {
    await markAsRead(notification.id);
  }
  if (notification.action_url) {
    navigateTo(notification.action_url);
    showNotifications.value = false;
  }
};

const handleMarkAllAsRead = async () => {
  const res = await markAllAsRead();
  if (res?.ok) {
    $toast.success(res.message || "All notifications marked as read");
  } else {
    $toast.error(res?.message || "Failed to mark all as read");
  }
};

const handleDeleteNotification = async (notificationId) => {
  await deleteNotification(notificationId);
  $toast.success("Notification deleted");
};

const handleDeleteAllRead = async () => {
  if (!confirm("Delete all read notifications?")) {
    return;
  }
  await deleteAllRead();
  $toast.success("Read notifications cleared");
};

// Close panels when clicking outside
onMounted(() => {
  isMounted.value = true;
  loadUnreadCount();

  const intervalId = setInterval(() => {
    if (!isMounted.value) return;
    loadUnreadCount();
  }, 30000);

  // Click outside handler for profile menu ONLY
  // (notification drawer handles its own click-outside internally via capture listeners)
  const handleClickOutside = (e) => {
    if (!isMounted.value) return;
    if (!profileMenuWrapperRef.value) return;
    const inProfileWrapper = e.target.closest(".profile-dropdown") ||
      profileMenuWrapperRef.value.contains(e.target);
    if (!inProfileWrapper) {
      showProfileMenu.value = false;
    }
  };

  // Handle escape key
  const handleEscape = (e) => {
    if (!isMounted.value) return;
    if (e.key === "Escape") {
      showProfilePreview.value = false;
      showNotifications.value = false;
      showProfileMenu.value = false;
      if (window.innerWidth < 1024) {
        sidebarOpen.value = false;
      }
    }
  };

  document.addEventListener("click", handleClickOutside);
  document.addEventListener("keydown", handleEscape);

  onBeforeUnmount(() => {
    // FIRST: prevent any further updates before DOM detaches
    isMounted.value = false;
    // Close any Teleport modals immediately to avoid unmount race
    showProfilePreview.value = false;
    showNotifications.value = false;
    clearInterval(intervalId);
    document.removeEventListener("click", handleClickOutside);
    document.removeEventListener("keydown", handleEscape);
  });
});

// Close sidebar and popovers on route change (adds sidebar close that route watch at line ~506 doesn't have)
watch(
  () => route.path,
  () => {
    if (!isMounted.value) return;
    sidebarOpen.value = false;
  }
);
</script>

<style scoped>
/* Custom scrollbar for sidebar */
nav::-webkit-scrollbar {
  width: 6px;
}

nav::-webkit-scrollbar-track {
  background: transparent;
}

nav::-webkit-scrollbar-thumb {
  background-color: rgba(0, 0, 0, 0.1);
  border-radius: 3px;
}

nav:hover::-webkit-scrollbar-thumb {
  background-color: rgba(0, 0, 0, 0.2);
}

/* Border left for active state */
.border-l-3 {
  border-left-width: 3px;
}

/* Prevent horizontal overflow */
* {
  box-sizing: border-box;
}

/* Ensure main content doesn't overflow */
main {
  overflow-x: hidden;
}

/* Fix for mobile sidebar overlay */
@media (max-width: 1023px) {
  .fixed.inset-y-0 {
    position: fixed !important;
  }
}

/* Prevent layout shift on desktop */
@media (min-width: 1024px) {
  .lg\:static {
    position: static !important;
  }
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
