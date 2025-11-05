<!-- pages/admin/users.vue -->
<template>
    <div>
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-secondary-900">User Management</h1>
                    <p class="mt-2 text-secondary-600">Manage all registered users and their accounts</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <button @click="showCreateModal = true" class="btn btn-primary">
                        <Icon name="heroicons:plus" class="h-5 w-5 mr-2" />
                        Add User
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-6">
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-2">Search</label>
                        <input v-model="filters.search" type="text" placeholder="Search users..." class="input"
                            @input="handleSearch" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-2">Subscription Plan</label>
                        <select v-model="filters.subscription_plan" @change="handleFilterChange" class="input">
                            <option value="">All Plans</option>
                            <option value="free">Free</option>
                            <option value="basic">Basic</option>
                            <option value="pro">Pro</option>
                            <option value="enterprise">Enterprise</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-2">Status</label>
                        <select v-model="filters.status" @change="handleFilterChange" class="input">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-2">Admin Status</label>
                        <select v-model="filters.is_admin" @change="handleFilterChange" class="input">
                            <option value="">All Users</option>
                            <option value="true">Admin Only</option>
                            <option value="false">Regular Users</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex justify-between items-center">
                    <button @click="clearFilters" class="btn btn-outline btn-sm">
                        Clear Filters
                    </button>
                    <div class="text-sm text-secondary-500">
                        {{ pagination.total }} users found
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-body p-0">
                <div v-if="loading" class="flex justify-center py-12">
                    <div class="spinner"></div>
                </div>
                <div v-else-if="users.length" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-secondary-200">
                        <thead class="bg-secondary-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    User
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Subscription
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Last Login
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-secondary-200">
                            <tr v-for="user in users" :key="user.id" class="hover:bg-secondary-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <img :src="user.profile?.profile_image || '/default-avatar.png'"
                                            :alt="user.full_name" class="h-10 w-10 rounded-full object-cover" />
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-secondary-900">
                                                {{ user.full_name }}
                                                <span v-if="user.is_admin"
                                                    class="ml-2 px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                                    {{ user.admin_role_display }}
                                                </span>
                                            </div>
                                            <div class="text-sm text-secondary-500">{{ user.email }}</div>
                                            <div class="text-xs text-secondary-400">{{ user.company }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        getSubscriptionBadgeClass(user.subscription_plan)
                                    ]">
                                        {{ user.subscription_plan }}
                                    </span>
                                    <div v-if="user.subscription_end_date" class="text-xs text-secondary-500 mt-1">
                                        Expires: {{ formatDate(user.subscription_end_date) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        user.subscription_active ? 'bg-success-100 text-success-800' : 'bg-error-100 text-error-800'
                                    ]">
                                        {{ user.subscription_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <div v-if="user.has_physical_card" class="text-xs text-secondary-500 mt-1">
                                        Has Physical Card
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-500">
                                    {{ user.last_login_at ? formatTimeAgo(user.last_login_at) : 'Never' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <button @click="viewUser(user)" class="text-primary-600 hover:text-primary-900">
                                            <Icon name="heroicons:eye" class="h-4 w-4" />
                                        </button>
                                        <button @click="editUser(user)" class="text-warning-600 hover:text-warning-900">
                                            <Icon name="heroicons:pencil" class="h-4 w-4" />
                                        </button>
                                        <button @click="deleteUser(user)" class="text-error-600 hover:text-error-900">
                                            <Icon name="heroicons:trash" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-12">
                    <Icon name="heroicons:users" class="h-12 w-12 mx-auto text-secondary-300 mb-4" />
                    <p class="text-secondary-500">No users found</p>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total > pagination.per_page" class="mt-6 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="text-sm text-secondary-700">
                    Showing {{ (pagination.current_page - 1) * pagination.per_page + 1 }} to
                    {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of
                    {{ pagination.total }} results
                </span>
            </div>
            <div class="flex items-center space-x-2">
                <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
                    class="btn btn-outline btn-sm">
                    Previous
                </button>
                <span class="text-sm text-secondary-700">
                    Page {{ pagination.current_page }} of {{ pagination.total_pages }}
                </span>
                <button @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page === pagination.total_pages" class="btn btn-outline btn-sm">
                    Next
                </button>
            </div>
        </div>

        <!-- Create/Edit User Modal -->
        <div v-if="showCreateModal || showEditModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-secondary-200">
                    <h3 class="text-lg font-medium text-secondary-900">
                        {{ showEditModal ? 'Edit User' : 'Create New User' }}
                    </h3>
                </div>
                <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-2">First Name *</label>
                            <input v-model="form.first_name" type="text" required class="input" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-2">Last Name *</label>
                            <input v-model="form.last_name" type="text" required class="input" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-2">Email *</label>
                            <input v-model="form.email" type="email" required class="input" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-2">Phone</label>
                            <input v-model="form.phone" type="tel" class="input" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-2">Company</label>
                            <input v-model="form.company" type="text" class="input" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-2">Job Title</label>
                            <input v-model="form.job_title" type="text" class="input" />
                        </div>
                    </div>

                    <div v-if="!showEditModal" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-2">Password *</label>
                            <input v-model="form.password" type="password" required class="input" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-2">Confirm Password *</label>
                            <input v-model="form.password_confirmation" type="password" required class="input" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-2">Subscription Plan</label>
                            <select v-model="form.subscription_plan" class="input">
                                <option value="free">Free</option>
                                <option value="basic">Basic</option>
                                <option value="pro">Pro</option>
                                <option value="enterprise">Enterprise</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-2">Admin Status</label>
                            <select v-model="form.is_admin" class="input">
                                <option :value="false">Regular User</option>
                                <option :value="true">Admin</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="form.is_admin" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-2">Admin Role</label>
                            <select v-model="form.admin_role" class="input">
                                <option value="admin">Admin</option>
                                <option value="moderator">Moderator</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-2">Admin Permissions</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input v-model="form.admin_permissions" type="checkbox" value="user_management"
                                        class="mr-2" />
                                    User Management
                                </label>
                                <label class="flex items-center">
                                    <input v-model="form.admin_permissions" type="checkbox" value="nfc_management"
                                        class="mr-2" />
                                    NFC Management
                                </label>
                                <label class="flex items-center">
                                    <input v-model="form.admin_permissions" type="checkbox" value="analytics"
                                        class="mr-2" />
                                    Analytics
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" @click="closeModal" class="btn btn-outline">
                            Cancel
                        </button>
                        <button type="submit" :disabled="submitting" class="btn btn-primary">
                            <div v-if="submitting" class="spinner mr-2"></div>
                            {{ showEditModal ? 'Update User' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- User Details Modal -->
        <div v-if="showUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-secondary-200">
                    <h3 class="text-lg font-medium text-secondary-900">User Details</h3>
                </div>
                <div v-if="selectedUser" class="p-6">
                    <!-- User info will be displayed here -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium text-secondary-900 mb-4">Basic Information</h4>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-secondary-500">Name</dt>
                                    <dd class="text-sm text-secondary-900">{{ selectedUser.full_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-secondary-500">Email</dt>
                                    <dd class="text-sm text-secondary-900">{{ selectedUser.email }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-secondary-500">Company</dt>
                                    <dd class="text-sm text-secondary-900">{{ selectedUser.company || 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-secondary-500">Job Title</dt>
                                    <dd class="text-sm text-secondary-900">{{ selectedUser.job_title || 'N/A' }}</dd>
                                </div>
                            </dl>
                        </div>
                        <div>
                            <h4 class="font-medium text-secondary-900 mb-4">Account Details</h4>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-secondary-500">Subscription Plan</dt>
                                    <dd class="text-sm text-secondary-900">{{ selectedUser.subscription_plan }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-secondary-500">Status</dt>
                                    <dd class="text-sm text-secondary-900">
                                        <span :class="[
                                            'px-2 py-1 text-xs font-medium rounded-full',
                                            selectedUser.subscription_active ? 'bg-success-100 text-success-800' : 'bg-error-100 text-error-800'
                                        ]">
                                            {{ selectedUser.subscription_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-secondary-500">Last Login</dt>
                                    <dd class="text-sm text-secondary-900">
                                        {{ selectedUser.last_login_at ? formatTimeAgo(selectedUser.last_login_at) :
                                        'Never' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-secondary-500">Member Since</dt>
                                    <dd class="text-sm text-secondary-900">{{ formatDate(selectedUser.created_at) }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-secondary-200">
                    <button @click="showUserModal = false" class="btn btn-outline">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'admin',
    middleware: 'admin'
});

const adminStore = useAdminStore();
const { users, loading, pagination } = storeToRefs(adminStore);

// Modal states
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showUserModal = ref(false);
const selectedUser = ref(null);
const submitting = ref(false);

// Form data
const form = ref({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    company: '',
    job_title: '',
    password: '',
    password_confirmation: '',
    subscription_plan: 'free',
    is_admin: false,
    admin_role: 'admin',
    admin_permissions: []
});

// Filters
const filters = ref({
    search: '',
    subscription_plan: '',
    status: '',
    is_admin: ''
});

// Fetch users on mount
onMounted(async () => {
    await adminStore.fetchUsers();
});

// Handle search with debounce
let searchTimeout;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        adminStore.updateFilters({ search: filters.value.search });
        adminStore.fetchUsers();
    }, 500);
};

// Handle filter changes
const handleFilterChange = () => {
    adminStore.updateFilters(filters.value);
    adminStore.fetchUsers();
};

// Clear all filters
const clearFilters = () => {
    adminStore.clearFilters();
    filters.value = {
        search: '',
        subscription_plan: '',
        status: '',
        is_admin: ''
    };
    adminStore.fetchUsers();
};

// Change page
const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        adminStore.fetchUsers({ page });
    }
};

// View user details
const viewUser = (user) => {
    selectedUser.value = user;
    showUserModal.value = true;
};

// Edit user
const editUser = (user) => {
    selectedUser.value = user;
    form.value = {
        first_name: user.first_name,
        last_name: user.last_name,
        email: user.email,
        phone: user.phone || '',
        company: user.company || '',
        job_title: user.job_title || '',
        password: '',
        password_confirmation: '',
        subscription_plan: user.subscription_plan,
        is_admin: user.is_admin,
        admin_role: user.admin_role || 'admin',
        admin_permissions: user.admin_permissions || []
    };
    showEditModal.value = true;
};

// Create new user
const createUser = () => {
    form.value = {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        company: '',
        job_title: '',
        password: '',
        password_confirmation: '',
        subscription_plan: 'free',
        is_admin: false,
        admin_role: 'admin',
        admin_permissions: []
    };
    showCreateModal.value = true;
};

// Handle form submission
const handleSubmit = async () => {
    if (form.value.password !== form.value.password_confirmation) {
        alert('Passwords do not match');
        return;
    }

    submitting.value = true;
    try {
        if (showEditModal.value) {
            await adminStore.updateUser(selectedUser.value.id, form.value);
        } else {
            await adminStore.createUser(form.value);
        }
        closeModal();
    } catch (error) {
        console.error('Failed to save user:', error);
    } finally {
        submitting.value = false;
    }
};

// Close modal
const closeModal = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    showUserModal.value = false;
    selectedUser.value = null;
    form.value = {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        company: '',
        job_title: '',
        password: '',
        password_confirmation: '',
        subscription_plan: 'free',
        is_admin: false,
        admin_role: 'admin',
        admin_permissions: []
    };
};

// Delete user
const deleteUser = async (user) => {
    if (confirm(`Are you sure you want to delete ${user.full_name}? This action cannot be undone.`)) {
        try {
            await adminStore.deleteUser(user.id);
        } catch (error) {
            console.error('Failed to delete user:', error);
        }
    }
};

// Helper functions
const getSubscriptionBadgeClass = (plan) => {
    const classes = {
        free: 'bg-secondary-100 text-secondary-800',
        basic: 'bg-blue-100 text-blue-800',
        pro: 'bg-purple-100 text-purple-800',
        enterprise: 'bg-green-100 text-green-800'
    };
    return classes[plan] || classes.free;
};

const formatTimeAgo = (timestamp) => {
    if (!timestamp) return '';
    const date = new Date(timestamp);
    const now = new Date();
    const diffInMinutes = Math.floor((now - date) / (1000 * 60));

    if (diffInMinutes < 1) return 'Just now';
    if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
    if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h ago`;
    return `${Math.floor(diffInMinutes / 1440)}d ago`;
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString();
};
</script>
