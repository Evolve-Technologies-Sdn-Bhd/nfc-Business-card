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
            <NuxtLink to="/" class="flex items-center">
              <Icon
                name="heroicons:identification"
                class="h-8 w-8 text-primary-600"
              />
              <span class="ml-2 text-xl font-bold text-secondary-900"
                >NFCGo</span
              >
              <span
                class="ml-2 px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full"
                >Admin</span
              >
            </NuxtLink>
          </div>

          <!-- Navigation - Scrollable -->
          <nav class="flex-1 overflow-y-auto px-4 py-6">
            <div class="space-y-1">
              <NuxtLink
                v-for="item in navigation"
                :key="item.name"
                :to="item.href"
                :class="[
                  'group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200 w-full text-left',
                  isActiveRoute(item.href)
                    ? 'bg-primary-50 text-primary-700 border-l-3 border-primary-700'
                    : 'text-secondary-600 hover:bg-secondary-50 hover:text-secondary-900',
                ]"
              >
                <Icon
                  :name="item.icon"
                  :class="[
                    'mr-3 h-5 w-5 flex-shrink-0',
                    isActiveRoute(item.href)
                      ? 'text-primary-500'
                      : 'text-secondary-400 group-hover:text-secondary-500',
                  ]"
                />
                {{ item.name }}
                <!-- Notification Badge -->
                <span
                  v-if="item.badge && Number(item.badge) > 0"
                  class="ml-auto inline-flex items-center justify-center rounded-full bg-red-500 text-white text-xs font-semibold h-5 min-w-[1.25rem] px-1"
                  :title="`${item.badge} unread feedback`"
                >
                  {{ item.badge }}
                </span>
              </NuxtLink>
            </div>

            <!-- Profile Section -->
            <div class="mt-8 pt-8 border-t border-secondary-200">
              <div class="flex items-center px-3 py-2">
                <div class="flex-shrink-0">
                  <img
                    :src="user?.profile_image || '/default-avatar.png'"
                    :alt="user?.name"
                    class="h-10 w-10 rounded-full object-cover"
                  />
                </div>
                <div class="ml-3 flex-1 min-w-0">
                  <p class="text-sm font-medium text-secondary-900 truncate">
                    {{
                      user?.first_name && user?.last_name
                        ? `${user.first_name} ${user.last_name}`
                        : user?.name || "Admin"
                    }}
                  </p>
                  <p class="text-xs text-secondary-500 truncate">
                    {{ user?.admin_role_display || "Administrator" }}
                  </p>
                </div>
              </div>

              <!-- Admin Actions -->
              <div class="mt-3 space-y-1">
                <button
                  @click="handleLogout"
                  class="w-full flex items-center px-3 py-2 text-sm font-medium text-red-600 rounded-lg hover:bg-red-50 hover:text-red-700 transition-colors"
                >
                  <Icon
                    name="heroicons:arrow-right-on-rectangle"
                    class="mr-3 h-5 w-5 text-red-400"
                  />
                  <span>Logout</span>
                </button>
              </div>
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
            <!-- Mobile menu button -->
            <button
              @click="sidebarOpen = !sidebarOpen"
              class="lg:hidden p-2 rounded-md text-secondary-400 hover:text-secondary-500 hover:bg-secondary-100"
            >
              <Icon
                :name="sidebarOpen ? 'heroicons:x-mark' : 'heroicons:bars-3'"
                class="h-6 w-6"
              />
            </button>

            <!-- Page Title -->
            <div class="flex-1 flex items-center">
              <h1 class="text-lg font-semibold text-secondary-900">
                {{ pageTitle }}
              </h1>
            </div>

            <!-- Right side actions -->
            <div class="flex items-center space-x-4">
              <!-- Icons removed as requested -->
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
  </div>
</template>

<script setup>
const route = useRoute();
const authStore = useAuthStore();
const { $api } = useNuxtApp();
const user = computed(() => authStore.user);

const sidebarOpen = ref(false);
const unreadFeedbackCount = ref(0);
const pendingNotificationCount = ref(0);

// Navigation items - using a computed property to dynamically update badge
const navigation = computed(() => [
  {
    name: "Dashboard",
    href: "/AdminManagement",
    icon: "heroicons:chart-bar-square",
  },
  {
    name: "User Management",
    href: "/AdminManagement/users",
    icon: "heroicons:users",
  },
  {
    name: "Business Users",
    href: "/AdminManagement/business-users",
    icon: "heroicons:building-office-2",
  },
  {
    name: "NFC Card Management",
    href: "/AdminManagement/nfc-cards",
    icon: "heroicons:credit-card",
  },
  {
    name: "Card Templates",
    href: "/AdminManagement/card-templates",
    icon: "heroicons:squares-2x2",
  },
  {
    name: "Invoices",
    href: "/AdminManagement/invoices",
    icon: "heroicons:document-text",
  },
  {
    name: "Price Management",
    href: "/AdminManagement/price-management",
    icon: "heroicons:currency-dollar",
  },
  {
    name: "Terms & Privacy",
    href: "/AdminManagement/legal-documents",
    icon: "heroicons:shield-check",
  },
  {
    name: "Profile Builder Design",
    href: "/AdminManagement/profile-builder-design",
    icon: "heroicons:paint-brush",
  },
  {
    name: "System Statistics",
    href: "/AdminManagement/stats",
    icon: "heroicons:chart-pie",
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
]);

// Page title based on current route
const pageTitle = computed(() => {
  const currentNav = navigation.value.find((item) => item.href === route.path);
  return currentNav ? currentNav.name : "Admin Panel";
});

// Check if route is active
const isActiveRoute = (href) => {
  return route.path === href;
};

// Fetch unread feedback count
const loadUnreadFeedbackCount = async () => {
  try {
    const nuxtApp = useNuxtApp();
    const $api = nuxtApp.$api;
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
    const nuxtApp = useNuxtApp();
    const $api = nuxtApp.$api;
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

// Handle logout
const handleLogout = async () => {
  try {
    await authStore.logout();
    await navigateTo("/UserAccount/login");
  } catch (error) {
    console.error("Logout failed:", error);
  }
};

// Close dropdowns when clicking outside
onMounted(() => {

  // Load unread feedback count
  loadUnreadFeedbackCount();

  // Load pending notification count
  loadPendingNotificationCount();

  // Refresh counts every 30 seconds
  const feedbackInterval = setInterval(loadUnreadFeedbackCount, 30000);
  const notificationInterval = setInterval(loadPendingNotificationCount, 30000);
  onBeforeUnmount(() => {
    clearInterval(feedbackInterval);
    clearInterval(notificationInterval);
  });

  // Listen for immediate updates from feedback page actions
  window.addEventListener("admin-feedback-updated", () => {
    loadUnreadFeedbackCount();
  });

  // Listen for immediate updates from notifications page actions
  window.addEventListener("admin-notifications-updated", () => {
    loadPendingNotificationCount();
  });
});

// Watch for route changes to close mobile sidebar
watch(
  () => route.path,
  () => {
    sidebarOpen.value = false;
  }
);
</script>

<style scoped>
.border-l-3 {
  border-left-width: 3px;
}
</style>
