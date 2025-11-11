<!-- layouts/user.vue -->
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
                  'group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200',
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
                <span
                  v-if="item.badge"
                  class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800"
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
                        : user?.name || "User"
                    }}
                  </p>
                  <p class="text-xs text-secondary-500 truncate">
                    {{ user?.email }}
                  </p>
                </div>
              </div>

              <!-- Notifications -->
              <div class="mt-3 px-3 notifications-dropdown">
                <button
                  @click="showNotifications = !showNotifications"
                  class="w-full flex items-center px-3 py-2 text-sm font-medium text-secondary-600 rounded-lg hover:bg-secondary-50 hover:text-secondary-900 transition-colors"
                >
                  <Icon
                    name="heroicons:bell"
                    class="mr-3 h-5 w-5 text-secondary-400"
                  />
                  <span class="flex-1 text-left">Notifications</span>
                  <span
                    class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                  >
                    3
                  </span>
                </button>

                <!-- Notifications Dropdown -->
                <Transition
                  enter-active-class="transition ease-out duration-100"
                  enter-from-class="transform opacity-0 scale-95"
                  enter-to-class="transform opacity-100 scale-100"
                  leave-active-class="transition ease-in duration-75"
                  leave-from-class="transform opacity-100 scale-100"
                  leave-to-class="transform opacity-0 scale-95"
                >
                  <div
                    v-if="showNotifications"
                    class="mt-2 bg-white rounded-lg shadow-lg border border-secondary-200 overflow-hidden"
                  >
                    <div class="p-3 border-b border-secondary-200">
                      <h3 class="text-sm font-medium text-secondary-900">
                        Recent Notifications
                      </h3>
                    </div>
                    <div class="max-h-64 overflow-y-auto">
                      <div
                        class="p-3 border-b border-secondary-100 hover:bg-secondary-50 cursor-pointer"
                      >
                        <p class="text-sm text-secondary-900">
                          Your profile was viewed 5 times today
                        </p>
                        <p class="text-xs text-secondary-500 mt-1">
                          2 hours ago
                        </p>
                      </div>
                      <div
                        class="p-3 border-b border-secondary-100 hover:bg-secondary-50 cursor-pointer"
                      >
                        <p class="text-sm text-secondary-900">
                          New NFC tag activated successfully
                        </p>
                        <p class="text-xs text-secondary-500 mt-1">1 day ago</p>
                      </div>
                      <div class="p-3 hover:bg-secondary-50 cursor-pointer">
                        <p class="text-sm text-secondary-900">
                          Welcome to NFCGo! Start building your profile
                        </p>
                        <p class="text-xs text-secondary-500 mt-1">
                          3 days ago
                        </p>
                      </div>
                    </div>
                    <div class="p-3 border-t border-secondary-200">
                      <button
                        class="text-sm text-primary-600 hover:text-primary-500 font-medium"
                      >
                        View all notifications
                      </button>
                    </div>
                  </div>
                </Transition>
              </div>

              <!-- Logout Button -->
              <div class="mt-3 px-3">
                <button
                  @click="handleLogout"
                  :disabled="logoutLoading"
                  class="w-full flex items-center px-3 py-2 text-sm font-medium text-secondary-600 rounded-lg hover:bg-secondary-50 hover:text-secondary-900 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <div v-if="logoutLoading" class="spinner mr-3"></div>
                  <Icon
                    v-else
                    name="heroicons:arrow-right-on-rectangle"
                    class="mr-3 h-5 w-5 text-secondary-400"
                  />
                  {{ logoutLoading ? "Signing out..." : "Sign Out" }}
                </button>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-6">
              <h3
                class="px-3 text-xs font-semibold text-secondary-500 uppercase tracking-wider"
              >
                Quick Actions
              </h3>
              <div class="mt-3 space-y-1">
                <button
                  @click="showProfilePreview = true"
                  class="group flex items-center w-full px-3 py-2 text-sm font-medium text-secondary-600 rounded-lg hover:bg-secondary-50 hover:text-secondary-900 transition-colors"
                >
                  <Icon
                    name="heroicons:eye"
                    class="mr-3 h-5 w-5 text-secondary-400 group-hover:text-secondary-500"
                  />
                  Preview Profile
                </button>
              </div>
            </div>
          </nav>

          <!-- Upgrade Banner - Fixed at bottom -->
          <div class="p-4 border-t border-secondary-200">
            <div
              class="bg-gradient-to-r from-primary-500 to-primary-600 rounded-lg p-4 text-white"
            >
              <div class="flex items-center">
                <Icon
                  name="heroicons:star"
                  class="h-6 w-6 text-yellow-300 flex-shrink-0"
                />
                <div class="ml-3 flex-1">
                  <p class="text-sm font-medium">Upgrade to Premium</p>
                  <p class="text-xs text-primary-100">
                    Unlock premium features
                  </p>
                </div>
              </div>
              <button
                class="mt-3 w-full bg-white text-primary-600 py-2 px-4 rounded-md text-sm font-medium hover:bg-primary-50 transition-colors"
              >
                Upgrade Now
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Area -->
      <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Mobile menu button (only visible on mobile) -->
        <div class="lg:hidden bg-white border-b border-secondary-200 p-4">
          <button
            @click="sidebarOpen = !sidebarOpen"
            class="p-2 rounded-md text-secondary-600 hover:text-secondary-900 hover:bg-secondary-100 transition-colors"
          >
            <Icon name="heroicons:bars-3" class="h-6 w-6" />
          </button>
        </div>

        <!-- Page Content - Scrollable -->
        <main class="flex-1 overflow-y-auto bg-secondary-50">
          <div class="p-4 sm:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto">
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
              <!-- Profile preview content would go here -->
              <div class="bg-secondary-100 rounded-lg p-8 text-center">
                <p class="text-secondary-600">
                  Profile preview will be displayed here
                </p>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
// Stores
const authStore = useAuthStore();
const { $toast } = useNuxtApp();

// Reactive data
const sidebarOpen = ref(false);
const showProfilePreview = ref(false);
const showNotifications = ref(false);
const logoutLoading = ref(false);

// Computed
const user = computed(() => authStore.user);

// Navigation items
const navigation = [
  {
    name: "Profile Builder",
    href: "/UserDashboard/ProfileBuilder",
    icon: "heroicons:user",
  },
  {
    name: "Link Management",
    href: "/UserDashboard/LinkManagement",
    icon: "heroicons:wifi",
  },
  {
    name: "Card Management",
    href: "/UserDashboard/CardManagement",
    icon: "heroicons:credit-card",
    badge: "Premium",
  },
  {
    name: "Analytics",
    href: "/UserDashboard/Analytics",
    icon: "heroicons:chart-bar",
  },
  {
    name: "Settings",
    href: "/UserDashboard/Settings",
    icon: "heroicons:cog-6-tooth",
  },
];

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

// Handle logout
const handleLogout = async () => {
  // Show confirmation dialog
  if (!confirm("Are you sure you want to sign out?")) {
    return;
  }

  logoutLoading.value = true;
  try {
    await authStore.logout();
    $toast.success("Logged out successfully");
  } catch (error) {
    console.error("Logout error:", error);
    $toast.error("Error logging out");
  } finally {
    logoutLoading.value = false;
  }
};

// Close dropdowns when clicking outside
onMounted(() => {
  const handleClickOutside = (e) => {
    // Close notifications dropdown when clicking outside
    if (!e.target.closest(".notifications-dropdown")) {
      showNotifications.value = false;
    }
  };

  document.addEventListener("click", handleClickOutside);

  onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
  });
});

// Close sidebar on route change (mobile)
watch(
  () => useRoute().path,
  () => {
    sidebarOpen.value = false;
  }
);

// Handle escape key
onMounted(() => {
  const handleEscape = (e) => {
    if (e.key === "Escape") {
      showProfilePreview.value = false;
      showNotifications.value = false;
      if (window.innerWidth < 1024) {
        sidebarOpen.value = false;
      }
    }
  };

  document.addEventListener("keydown", handleEscape);

  onUnmounted(() => {
    document.removeEventListener("keydown", handleEscape);
  });
});
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
</style>
