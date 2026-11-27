<!-- layouts/UserDashboard.vue
     NFCGo user portal shell — "Ink & Brass".
     Shares the Admin Panel's design system (ink rail, paper workspace, brass
     accents — see the ADMIN DESIGN SYSTEM block in assets/css/main.css) while
     keeping a user-facing structure. Navigation is built from usePortalAccess,
     the same rule set the `portal-access` route middleware enforces, so what a
     Free / Basic / Premium / Business user sees always matches what they can open. -->
<template>
  <div class="admin-shell portal-shell h-screen overflow-hidden">
    <div class="flex h-full overflow-hidden">
      <!-- ============================ RAIL (desktop) ============================ -->
      <aside class="admin-rail hidden shrink-0 flex-col lg:flex">
        <div class="admin-rail-brand flex h-16 items-center px-4">
          <NuxtLink to="/UserDashboard" class="flex min-w-0 items-center gap-3" aria-label="NFCGo dashboard">
            <NFCGoLogo variant="dark" size="sm" />
          </NuxtLink>
        </div>

        <nav class="admin-scroll flex-1 overflow-y-auto px-3 py-5" aria-label="Portal navigation">
          <PortalNavGroups
            :groups="navigation"
            :is-active="isActiveRoute"
            :busy="routeNavigationInProgress"
            @locked="onLockedClick"
          />
        </nav>

        <div class="admin-rail-foot px-3 py-3">
          <PortalPlanChip :access="access" />
        </div>
      </aside>

      <!-- ============================ WORKSPACE ============================ -->
      <div class="admin-workspace flex min-w-0 flex-1 flex-col overflow-hidden">
        <header class="admin-topbar relative z-30 shrink-0">
          <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
            <button
              type="button"
              class="admin-icon-btn lg:hidden"
              aria-label="Open navigation"
              @click="sidebarOpen = true"
            >
              <Icon name="heroicons:bars-3" class="h-6 w-6" />
            </button>

            <div class="flex min-w-0 flex-col">
              <nav class="admin-crumb hidden items-center gap-1.5 sm:flex" aria-label="Breadcrumb">
                <NuxtLink to="/UserDashboard">My NFCGo</NuxtLink>
                <template v-if="activeNavItem?.group && activeNavItem.group !== 'Workspace'">
                  <span class="text-secondary-300">/</span>
                  <span>{{ activeNavItem.group }}</span>
                </template>
              </nav>
              <h1 class="truncate text-h5 leading-tight text-secondary-900">{{ pageTitle }}</h1>
            </div>

            <div class="ml-auto flex items-center gap-1.5 sm:gap-2">
              <!-- Wrapper owns visibility: .admin-btn sets its own display,
                   which would override a `hidden` utility on the link itself. -->
              <span v-if="hasPreviewProfile" class="hidden md:inline-flex">
                <a
                  :href="userPreviewProfileUrl"
                  target="_blank"
                  rel="noopener"
                  class="admin-btn admin-btn--outline admin-btn--sm"
                >
                  <Icon name="heroicons:arrow-top-right-on-square" class="h-4 w-4" />
                  View public profile
                </a>
              </span>

              <button
                ref="notifButtonRef"
                type="button"
                class="notifications-dropdown admin-icon-btn relative"
                :class="{ 'bg-secondary-100 text-secondary-900': showNotifications }"
                aria-label="Notifications"
                @click="toggleNotifications"
              >
                <Icon name="heroicons:bell" class="h-5 w-5" />
                <span
                  v-if="unreadCount > 0"
                  class="absolute -right-0.5 -top-0.5 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-error-500 px-1 text-badge font-bold text-white ring-2 ring-white"
                >
                  {{ unreadCount > 9 ? "9+" : unreadCount }}
                </span>
              </button>

              <div class="mx-1 hidden h-6 w-px bg-secondary-200 sm:block"></div>

              <!-- Profile menu -->
              <div ref="profileMenuWrapperRef" class="relative">
                <button
                  type="button"
                  class="flex items-center gap-3 rounded-xl p-1.5 transition-colors hover:bg-secondary-100"
                  :aria-expanded="showProfileMenu"
                  aria-haspopup="menu"
                  @click="showProfileMenu = !showProfileMenu"
                >
                  <div class="hidden text-right leading-tight sm:block">
                    <p class="max-w-[160px] truncate text-body font-semibold text-secondary-900">{{ displayName }}</p>
                    <p class="max-w-[160px] truncate text-caption text-secondary-500">{{ accountSubtitle }}</p>
                  </div>
                  <UserAvatar
                    :src="user?.account_image"
                    :name="displayName"
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
                    class="profile-dropdown absolute right-0 z-[90] mt-2 w-72 overflow-hidden rounded-2xl border border-secondary-200 bg-white shadow-xl"
                    role="menu"
                  >
                    <div class="border-b border-secondary-200 bg-gradient-to-br from-accent-50 to-secondary-50 p-5">
                      <div class="flex items-center gap-3">
                        <UserAvatar
                          :src="user?.account_image"
                          :name="displayName"
                          class="h-12 w-12 rounded-full shadow-sm ring-2 ring-white"
                        />
                        <div class="min-w-0 flex-1">
                          <p class="truncate text-body font-bold text-secondary-900">{{ displayName }}</p>
                          <p class="truncate text-caption text-secondary-500">{{ user?.email }}</p>
                          <span class="mt-1 inline-flex items-center rounded-full bg-accent-100 px-2 py-0.5 text-badge font-bold text-accent-800">
                            {{ accountSubtitle }}
                          </span>
                        </div>
                      </div>
                    </div>

                    <div class="py-2">
                      <button type="button" class="portal-menu-item" role="menuitem" @click="openPreview">
                        <Icon name="heroicons:eye" class="h-5 w-5 text-secondary-400" />
                        <span>Preview profile</span>
                      </button>
                      <NuxtLink to="/UserDashboard/Settings" class="portal-menu-item" role="menuitem" @click="showProfileMenu = false">
                        <Icon name="heroicons:cog-6-tooth" class="h-5 w-5 text-secondary-400" />
                        <span>Account settings</span>
                      </NuxtLink>
                      <NuxtLink
                        v-if="!access.isBusiness"
                        to="/Onboarding/plan"
                        class="portal-menu-item"
                        role="menuitem"
                        @click="showProfileMenu = false"
                      >
                        <Icon name="heroicons:sparkles" class="h-5 w-5 text-secondary-400" />
                        <span>Compare plans</span>
                      </NuxtLink>
                    </div>

                    <div class="border-t border-secondary-200 py-2">
                      <button
                        type="button"
                        class="portal-menu-item text-error-600 hover:bg-error-50 hover:text-error-700"
                        role="menuitem"
                        @click="askLogout"
                      >
                        <Icon name="heroicons:arrow-right-on-rectangle" class="h-5 w-5 text-error-400" />
                        <span>Sign out</span>
                      </button>
                    </div>
                  </div>
                </Transition>
              </div>
            </div>
          </div>
        </header>

        <main class="dashboard-main admin-main flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
          <div class="portal-container">
            <slot />
          </div>
        </main>
      </div>
    </div>

    <!-- ============================ MOBILE NAV ============================ -->
    <Transition name="admin-fade">
      <div v-if="sidebarOpen" class="fixed inset-0 z-[110] lg:hidden">
        <div class="absolute inset-0 bg-black/55" @click="sidebarOpen = false"></div>
        <aside class="admin-rail absolute inset-y-0 left-0 flex w-72 max-w-[85vw] flex-col shadow-2xl">
          <div class="admin-rail-brand flex h-16 items-center justify-between px-4">
            <NuxtLink to="/UserDashboard" class="flex min-w-0 items-center gap-3" @click="sidebarOpen = false">
              <NFCGoLogo variant="dark" size="sm" />
            </NuxtLink>
            <button
              type="button"
              class="admin-icon-btn text-white/70 hover:bg-white/10 hover:text-white"
              aria-label="Close navigation"
              @click="sidebarOpen = false"
            >
              <Icon name="heroicons:x-mark" class="h-5 w-5" />
            </button>
          </div>
          <nav class="admin-scroll flex-1 overflow-y-auto px-3 py-5" aria-label="Portal navigation">
            <PortalNavGroups
              :groups="navigation"
              :is-active="isActiveRoute"
              :busy="routeNavigationInProgress"
              @locked="onLockedClick"
              @navigate="sidebarOpen = false"
            />
          </nav>
          <div class="admin-rail-foot px-3 py-3">
            <PortalPlanChip :access="access" />
          </div>
        </aside>
      </div>
    </Transition>

    <!-- ============================ PROFILE PREVIEW ============================ -->
    <Transition name="admin-fade">
      <div
        v-if="showProfilePreview"
        class="admin-dialog-backdrop"
        @click.self="showProfilePreview = false"
      >
        <div class="admin-dialog portal-preview-dialog" role="dialog" aria-modal="true" aria-label="Profile preview">
          <div class="admin-dialog-head">
            <div>
              <p class="admin-panel-title">Profile preview</p>
              <p class="admin-panel-subtitle">Live view of your public landing page</p>
            </div>
            <div class="flex items-center gap-2">
              <a
                v-if="hasPreviewProfile"
                :href="userPreviewProfileUrl"
                target="_blank"
                rel="noopener"
                class="admin-btn admin-btn--ghost admin-btn--sm"
              >
                <Icon name="heroicons:arrow-top-right-on-square" class="h-4 w-4" />
                Open in new tab
              </a>
              <button type="button" class="admin-icon-btn" aria-label="Close preview" @click="showProfilePreview = false">
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>
          </div>
          <div class="admin-panel-body">
            <div v-if="hasPreviewProfile" class="overflow-hidden rounded-xl border border-secondary-200 bg-secondary-50">
              <iframe
                :src="userPreviewProfileUrl"
                title="Profile preview"
                class="block w-full bg-white"
                :style="{ height: '70vh', minHeight: '420px', border: 0 }"
                sandbox="allow-scripts allow-same-origin allow-forms allow-popups"
              />
            </div>
            <div v-else class="admin-empty">
              <div class="admin-empty-ico">
                <Icon name="heroicons:identification" class="h-6 w-6" />
              </div>
              <p class="text-h5 text-secondary-900">No profile to preview yet</p>
              <p class="mt-1 max-w-sm text-body text-secondary-500">
                Your public profile appears here once you have a card and have saved your profile.
              </p>
              <NuxtLink
                :to="access.can.builder ? access.routes.builder : access.routes.cards"
                class="admin-btn admin-btn--primary mt-4"
                @click="showProfilePreview = false"
              >
                {{ access.can.builder ? "Open Profile Builder" : "Go to Card Management" }}
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <AdminConfirmDialog
      v-model="showLogoutConfirm"
      title="Sign out?"
      message="You will need to sign in again to manage your cards and profile."
      confirm-label="Sign out"
      :loading="logoutLoading"
      @confirm="handleLogout"
      @cancel="showLogoutConfirm = false"
    />

    <AdminConfirmDialog
      v-model="showClearReadConfirm"
      title="Clear read notifications?"
      message="All notifications you have already read will be permanently removed."
      confirm-label="Clear"
      tone="danger"
      @confirm="handleDeleteAllRead"
      @cancel="showClearReadConfirm = false"
    />

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
      @clear-read="showClearReadConfirm = true"
    />
  </div>
</template>

<script setup>
// Navigation-loading state exported by the router.client plugin (blocks
// rapid double-clicks on sidebar links while a navigation is in flight).
import { routeNavigationInProgress } from "~/plugins/router.client.js";
import { PORTAL_ROUTES } from "~/composables/usePortalAccess";

const authStore = useAuthStore();
const { $toast } = useNuxtApp();
const route = useRoute();
const access = usePortalAccess();

// The workspace <main> owns vertical scrolling; the document never scrolls.
useHead({ htmlAttrs: { class: "shell-scroll-lock" } });

const isMounted = ref(false);
const sidebarOpen = ref(false);
const showProfilePreview = ref(false);
const showNotifications = ref(false);
const showProfileMenu = ref(false);
const showLogoutConfirm = ref(false);
const showClearReadConfirm = ref(false);
const logoutLoading = ref(false);
const notifButtonRef = ref(null);
const profileMenuWrapperRef = ref(null);

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
} = useNotifications();

const user = computed(() => authStore.user);

const displayName = computed(() => {
  const u = user.value;
  if (u?.first_name || u?.last_name) return `${u.first_name || ""} ${u.last_name || ""}`.trim();
  return u?.full_name || u?.name || "Your account";
});

const ROLE_LABELS = { owner: "Owner", admin: "Admin", card_manager: "Card manager", member: "Member" };
const accountSubtitle = computed(() => {
  const a = access.value;
  if (a.businessRole) return `Business · ${ROLE_LABELS[a.businessRole] || "Member"}`;
  return `${a.planLabel} plan`;
});

// ---------------------------------------------------------------------------
// Navigation — every item and lock state comes from usePortalAccess
// ---------------------------------------------------------------------------
const navigation = computed(() => {
  const a = access.value;
  const lockedBuilderMsg = "Unlocks once your card payment is verified.";
  const lockedAnalyticsMsg = "Analytics becomes available once your plan is active.";

  const groups = [
    {
      category: "Workspace",
      items: [
        { name: "Dashboard", href: PORTAL_ROUTES.dashboard, icon: "heroicons:squares-2x2" },
        {
          name: a.isBusiness ? "Business Cards" : "My Cards",
          href: a.routes.cards,
          icon: "heroicons:credit-card",
        },
        {
          name: "Profile Builder",
          href: a.routes.builder,
          icon: "heroicons:identification",
          locked: !a.can.builder,
          lockedMessage: lockedBuilderMsg,
        },
        {
          name: "Analytics",
          href: a.routes.analytics,
          icon: "heroicons:chart-bar",
          locked: !a.can.analytics,
          lockedMessage: lockedAnalyticsMsg,
        },
      ],
    },
  ];

  if (a.can.employees || a.can.activityLog) {
    const team = [];
    if (a.can.employees) {
      team.push({ name: "Team Members", href: PORTAL_ROUTES.employees, icon: "heroicons:users" });
    }
    if (a.can.activityLog) {
      team.push({ name: "Activity Log", href: PORTAL_ROUTES.activityLog, icon: "heroicons:clipboard-document-list" });
    }
    groups.push({ category: "Team", items: team });
  }

  const accountItems = [
    {
      name: "Notifications",
      href: PORTAL_ROUTES.notifications,
      icon: "heroicons:bell",
      badge: unreadCount.value > 0 ? (unreadCount.value > 9 ? "9+" : String(unreadCount.value)) : null,
    },
  ];
  if (a.can.invoices) {
    accountItems.push({ name: "Invoices", href: PORTAL_ROUTES.invoices, icon: "heroicons:document-text" });
  }
  accountItems.push({ name: "Settings", href: PORTAL_ROUTES.settings, icon: "heroicons:cog-6-tooth" });
  groups.push({ category: "Account", items: accountItems });

  return groups;
});

const flatNavigation = computed(() =>
  navigation.value.flatMap((g) => g.items.map((item) => ({ ...item, group: g.category }))),
);

// Pages reached from a nav item but living on their own route.
const EXTRA_TITLES = [
  { href: PORTAL_ROUTES.cards, name: "My Cards", group: "Workspace" },
  { href: PORTAL_ROUTES.businessCards, name: "Business Cards", group: "Workspace" },
  { href: PORTAL_ROUTES.analytics, name: "Analytics", group: "Workspace" },
  { href: PORTAL_ROUTES.premiumAnalytics, name: "Analytics", group: "Workspace" },
  { href: PORTAL_ROUTES.businessAnalytics, name: "Analytics", group: "Workspace" },
  ...Object.values(PORTAL_ROUTES.builders).map((href) => ({ href, name: "Profile Builder", group: "Workspace" })),
  { href: PORTAL_ROUTES.invoices, name: "Invoices", group: "Account" },
];

const activeNavItem = computed(() => {
  const path = route.path.replace(/\/+$/, "") || "/";
  const candidates = [...flatNavigation.value, ...EXTRA_TITLES];
  let match = null;
  for (const item of candidates) {
    if (path === item.href || path.startsWith(item.href + "/")) {
      if (!match || item.href.length > match.href.length) match = item;
    }
  }
  return match;
});

const pageTitle = computed(() => activeNavItem.value?.name || "Dashboard");

const isActiveRoute = (href) => {
  const active = activeNavItem.value;
  if (!active) return false;
  if (active.href === href) return true;
  // Plan-specific variants of the same feature light up the same nav item
  return active.name === flatNavigation.value.find((i) => i.href === href)?.name;
};

const onLockedClick = (item) => {
  $toast.info(item.lockedMessage || "This feature is not included in your current plan.");
};

// ---------------------------------------------------------------------------
// Public profile preview URL (first card)
// ---------------------------------------------------------------------------
const userPreviewProfileUrl = computed(() => {
  const cards = user.value?.nfc_cards ?? user.value?.nfcCards;
  if (!Array.isArray(cards) || cards.length === 0) return null;
  const primary = cards[0];
  if (!primary) return null;

  if (typeof primary.public_url === "string" && primary.public_url.startsWith("/profile/")) {
    return primary.public_url;
  }
  const authUser = user.value || {};
  const sorted = cards
    .slice()
    .sort(
      (a, b) =>
        new Date(a.created_at || a.createdAt || 0) - new Date(b.created_at || b.createdAt || 0) ||
        (a.id ?? 0) - (b.id ?? 0),
    );
  const localCardNumber = sorted.findIndex((c) => c.id === primary.id) + 1;
  if (authUser.name_slug && (localCardNumber || primary.card_number)) {
    let base = `/profile/${authUser.name_slug}/${localCardNumber || primary.card_number}`;
    if (authUser.id != null && authUser.has_name_slug_clash === true) base += "/userid-" + authUser.id;
    return base;
  }
  return `/profile/${primary.nfc_card_id || primary.id}`;
});
const hasPreviewProfile = computed(() => !!userPreviewProfileUrl.value);

const openPreview = () => {
  showProfileMenu.value = false;
  showProfilePreview.value = true;
};

// ---------------------------------------------------------------------------
// Session
// ---------------------------------------------------------------------------
const askLogout = () => {
  showProfileMenu.value = false;
  showLogoutConfirm.value = true;
};

const handleLogout = async () => {
  logoutLoading.value = true;
  try {
    isMounted.value = false;
    showProfilePreview.value = false;
    showNotifications.value = false;
    await authStore.logout();
    $toast.success("Signed out");
    await navigateTo("/UserAccount/login", { replace: true });
  } catch (error) {
    console.error("Logout error:", error);
    $toast.error("Could not sign out. Please try again.");
  } finally {
    logoutLoading.value = false;
    showLogoutConfirm.value = false;
  }
};

// ---------------------------------------------------------------------------
// Notifications
// ---------------------------------------------------------------------------
const toggleNotifications = () => {
  const wasOpen = showNotifications.value;
  showNotifications.value = !wasOpen;
  if (!wasOpen && notifications.value.length === 0) loadNotifications(5);
};

const handleNotificationClick = async (notification) => {
  if (!notification.is_read) await markAsRead(notification.id);
  if (notification.action_url) {
    navigateTo(notification.action_url);
    showNotifications.value = false;
  }
};

const handleMarkAllAsRead = async () => {
  const res = await markAllAsRead();
  if (res?.ok) $toast.success(res.message || "All notifications marked as read");
  else $toast.error(res?.message || "Failed to mark all as read");
};

const handleDeleteNotification = async (notificationId) => {
  await deleteNotification(notificationId);
  $toast.success("Notification deleted");
};

const handleDeleteAllRead = async () => {
  showClearReadConfirm.value = false;
  await deleteAllRead();
  $toast.success("Read notifications cleared");
};

// ---------------------------------------------------------------------------
// Lifecycle
// ---------------------------------------------------------------------------
onMounted(() => {
  isMounted.value = true;
  loadUnreadCount();

  const intervalId = setInterval(() => {
    if (isMounted.value) loadUnreadCount();
  }, 30000);

  const handleClickOutside = (e) => {
    if (!isMounted.value || !profileMenuWrapperRef.value) return;
    const inside = e.target.closest(".profile-dropdown") || profileMenuWrapperRef.value.contains(e.target);
    if (!inside) showProfileMenu.value = false;
  };

  const handleEscape = (e) => {
    if (!isMounted.value || e.key !== "Escape") return;
    showProfilePreview.value = false;
    showNotifications.value = false;
    showProfileMenu.value = false;
    sidebarOpen.value = false;
  };

  document.addEventListener("click", handleClickOutside);
  document.addEventListener("keydown", handleEscape);

  onBeforeUnmount(() => {
    isMounted.value = false;
    showProfilePreview.value = false;
    showNotifications.value = false;
    clearInterval(intervalId);
    document.removeEventListener("click", handleClickOutside);
    document.removeEventListener("keydown", handleEscape);
  });
});

watch(
  () => route.path,
  (newPath, oldPath) => {
    if (!isMounted.value || newPath === oldPath) return;
    sidebarOpen.value = false;
    showNotifications.value = false;
    showProfileMenu.value = false;
  },
);
</script>

<style scoped>
/* Page loaders (NFCGoWaveLoader `.nfg-viewport`) centre on the visible
   workspace: subtract the h-16 top bar plus the main padding. */
.dashboard-main {
  --nfg-viewport-offset: 6rem;
  overflow-x: hidden;
}
@media (min-width: 1024px) {
  .dashboard-main {
    --nfg-viewport-offset: 8rem;
  }
}
.portal-container {
  width: 100%;
  max-width: 80rem;
  margin-left: auto;
  margin-right: auto;
}
.portal-menu-item {
  display: flex;
  width: 100%;
  align-items: center;
  gap: 0.75rem;
  padding: 0.625rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--admin-text);
  transition: background-color 150ms ease, color 150ms ease;
}
.portal-menu-item:hover {
  background: var(--admin-paper);
  color: var(--admin-ink);
}
.portal-preview-dialog {
  width: min(30rem, 100%);
  margin-top: 3vh;
  max-height: 94vh;
  overflow-y: auto;
}
</style>
