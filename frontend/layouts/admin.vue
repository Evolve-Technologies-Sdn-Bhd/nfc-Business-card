<!-- layouts/admin.vue -->
<template>
    <div class="min-h-screen bg-secondary-50">
        <!-- Desktop Layout Container -->
        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar -->
            <div class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:relative lg:translate-x-0"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

                <div class="flex flex-col h-full">
                    <!-- Logo -->
                    <div class="flex items-center justify-center h-16 px-4 border-b border-secondary-200 flex-shrink-0">
                        <NuxtLink to="/" class="flex items-center">
                            <Icon name="heroicons:identification" class="h-8 w-8 text-primary-600" />
                            <span class="ml-2 text-xl font-bold text-secondary-900">NFCGo</span>
                            <span
                                class="ml-2 px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Admin</span>
                        </NuxtLink>
                    </div>

                    <!-- Navigation - Scrollable -->
                    <nav class="flex-1 overflow-y-auto px-4 py-6">
                        <div class="space-y-1">
                            <NuxtLink v-for="item in navigation" :key="item.name" :to="item.href" :class="[
                                'group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200',
                                isActiveRoute(item.href)
                                    ? 'bg-primary-50 text-primary-700 border-l-3 border-primary-700'
                                    : 'text-secondary-600 hover:bg-secondary-50 hover:text-secondary-900'
                            ]">
                                <Icon :name="item.icon" :class="[
                                    'mr-3 h-5 w-5 flex-shrink-0',
                                    isActiveRoute(item.href)
                                        ? 'text-primary-500'
                                        : 'text-secondary-400 group-hover:text-secondary-500'
                                ]" />
                                {{ item.name }}
                                <span v-if="item.badge"
                                    class="ml-auto inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                                    {{ item.badge }}
                                </span>
                            </NuxtLink>
                        </div>

                        <!-- Profile Section -->
                        <div class="mt-8 pt-8 border-t border-secondary-200">
                            <div class="flex items-center px-3 py-2">
                                <div class="flex-shrink-0">
                                    <img :src="user?.profile_image || '/default-avatar.png'" :alt="user?.name"
                                        class="h-10 w-10 rounded-full object-cover" />
                                </div>
                                <div class="ml-3 flex-1 min-w-0">
                                    <p class="text-sm font-medium text-secondary-900 truncate">
                                        {{ user?.first_name && user?.last_name ? `${user.first_name} ${user.last_name}`
                                            : user?.name || 'Admin' }}
                                    </p>
                                    <p class="text-xs text-secondary-500 truncate">
                                        {{ user?.admin_role_display || 'Administrator' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Admin Actions -->
                            <div class="mt-3 space-y-1">
                                <NuxtLink to="/dashboard"
                                    class="flex items-center px-3 py-2 text-sm font-medium text-secondary-600 rounded-lg hover:bg-secondary-50 hover:text-secondary-900 transition-colors">
                                    <Icon name="heroicons:arrow-left" class="mr-3 h-5 w-5 text-secondary-400" />
                                    <span>Back to Dashboard</span>
                                </NuxtLink>

                                <button @click="handleLogout"
                                    class="w-full flex items-center px-3 py-2 text-sm font-medium text-red-600 rounded-lg hover:bg-red-50 hover:text-red-700 transition-colors">
                                    <Icon name="heroicons:arrow-right-on-rectangle" class="mr-3 h-5 w-5 text-red-400" />
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
                <header class="bg-white shadow-sm border-b border-secondary-200 flex-shrink-0">
                    <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16">
                        <!-- Mobile menu button -->
                        <button @click="sidebarOpen = !sidebarOpen"
                            class="lg:hidden p-2 rounded-md text-secondary-400 hover:text-secondary-500 hover:bg-secondary-100">
                            <Icon :name="sidebarOpen ? 'heroicons:x-mark' : 'heroicons:bars-3'" class="h-6 w-6" />
                        </button>

                        <!-- Page Title -->
                        <div class="flex-1 flex items-center">
                            <h1 class="text-lg font-semibold text-secondary-900">{{ pageTitle }}</h1>
                        </div>

                        <!-- Right side actions -->
                        <div class="flex items-center space-x-4">
                            <!-- Notifications -->
                            <button
                                class="p-2 text-secondary-400 hover:text-secondary-500 hover:bg-secondary-100 rounded-md">
                                <Icon name="heroicons:bell" class="h-6 w-6" />
                                <span class="sr-only">View notifications</span>
                            </button>

                            <!-- Profile dropdown -->
                            <div class="relative">
                                <button @click="profileDropdownOpen = !profileDropdownOpen"
                                    class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    <img :src="user?.profile_image || '/default-avatar.png'" :alt="user?.name"
                                        class="h-8 w-8 rounded-full" />
                                </button>

                                <!-- Profile dropdown menu -->
                                <Transition enter-active-class="transition ease-out duration-100"
                                    enter-from-class="transform opacity-0 scale-95"
                                    enter-to-class="transform opacity-100 scale-100"
                                    leave-active-class="transition ease-in duration-75"
                                    leave-from-class="transform opacity-100 scale-100"
                                    leave-to-class="transform opacity-0 scale-95">
                                    <div v-if="profileDropdownOpen"
                                        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                        <NuxtLink to="/dashboard"
                                            class="block px-4 py-2 text-sm text-secondary-700 hover:bg-secondary-100">
                                            Back to Dashboard
                                        </NuxtLink>
                                        <button @click="handleLogout"
                                            class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-100">
                                            Sign out
                                        </button>
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
        <div v-if="sidebarOpen" @click="sidebarOpen = false"
            class="fixed inset-0 z-40 lg:hidden bg-black bg-opacity-50"></div>
    </div>
</template>

<script setup>
const route = useRoute();
const authStore = useAuthStore();
const user = computed(() => authStore.user);

const sidebarOpen = ref(false);
const profileDropdownOpen = ref(false);

// Navigation items
const navigation = [
    {
        name: 'Dashboard',
        href: '/admin',
        icon: 'heroicons:chart-bar-square',
    },
    {
        name: 'User Management',
        href: '/admin/users',
        icon: 'heroicons:users',
    },
    {
        name: 'NFC Card Management',
        href: '/admin/nfc-cards',
        icon: 'heroicons:credit-card',
    },
    {
        name: 'System Statistics',
        href: '/admin/stats',
        icon: 'heroicons:chart-pie',
    },
];

// Page title based on current route
const pageTitle = computed(() => {
    const currentNav = navigation.find(item => item.href === route.path);
    return currentNav ? currentNav.name : 'Admin Panel';
});

// Check if route is active
const isActiveRoute = (href) => {
    return route.path === href;
};

// Handle logout
const handleLogout = async () => {
    try {
        await authStore.logout();
        await navigateTo('/login');
    } catch (error) {
        console.error('Logout failed:', error);
    }
};

// Close dropdowns when clicking outside
onMounted(() => {
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.relative')) {
            profileDropdownOpen.value = false;
        }
    });
});

// Watch for route changes to close mobile sidebar
watch(() => route.path, () => {
    sidebarOpen.value = false;
});
</script>

<style scoped>
.border-l-3 {
    border-left-width: 3px;
}
</style>
