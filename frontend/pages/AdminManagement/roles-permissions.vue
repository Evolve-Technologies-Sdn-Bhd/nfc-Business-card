<!-- pages/AdminManagement/roles-permissions.vue -->
<template>
  <div>
    <!-- Header -->
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="page-title text-secondary-900">Roles &amp; Permissions</h1>
          <p class="mt-2 text-secondary-600">
            Manage admin roles and granular permissions across the platform
          </p>
        </div>
        <div class="mt-4 sm:mt-0">
          <button @click="loadData" class="btn btn-outline" :disabled="loading">
            <Icon name="heroicons:arrow-path" class="h-5 w-5 mr-2" />
            Refresh
          </button>
        </div>
      </div>
    </div>

    <!-- Super admin only notice -->
    <div v-if="!loading && !isSuperAdmin" class="card mb-6 border-warning-300">
      <div class="card-body flex items-start">
        <Icon name="heroicons:shield-exclamation" class="h-6 w-6 text-warning-600 flex-shrink-0 mt-0.5" />
        <div class="ml-3">
          <h3 class="text-body font-semibold text-secondary-900">Read-only access</h3>
          <p class="mt-1 text-body text-secondary-600">
            Only a super admin can grant or modify admin roles and permissions.
            You can view the current assignments below but cannot change them.
          </p>
        </div>
      </div>
    </div>

    <!-- Admin list -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title text-secondary-900">
          Administrators ({{ admins.length }})
        </h3>
      </div>
      <div class="card-body p-0">
        <div v-if="loading" class="flex justify-center py-12">
          <div class="spinner text-primary-600"></div>
        </div>
        <div v-else-if="admins.length" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-secondary-200">
            <thead class="bg-secondary-50">
              <tr>
                <th class="px-3 sm:px-6 py-3 text-left table-head">Administrator</th>
                <th class="px-3 sm:px-6 py-3 text-left table-head">Role</th>
                <th class="px-3 sm:px-6 py-3 text-left table-head">Permissions</th>
                <th class="px-3 sm:px-6 py-3 text-left table-head">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-secondary-200">
              <tr v-for="admin in admins" :key="admin.id" class="hover:bg-secondary-50">
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-secondary-100 flex items-center justify-center flex-shrink-0">
                      <Icon name="heroicons:user" class="h-5 w-5 text-secondary-500" />
                    </div>
                    <div class="ml-4">
                      <div class="text-body font-medium text-secondary-900">
                        {{ admin.full_name }}
                        <span v-if="isCurrentUser(admin)" class="ml-2 px-2 py-0.5 text-badge bg-secondary-100 text-secondary-600 rounded-full">
                          You
                        </span>
                      </div>
                      <div class="card-subtitle">{{ admin.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                  <span :class="['px-2 py-1 text-badge rounded-full', getRoleBadgeClass(admin.admin_role)]">
                    {{ admin.admin_role_display }}
                  </span>
                </td>
                <td class="px-3 sm:px-6 py-4 table-cell text-secondary-600">
                  <span v-if="admin.admin_role === 'super_admin'" class="text-secondary-700 font-medium">
                    All permissions
                  </span>
                  <span v-else-if="(admin.admin_permissions || []).length">
                    {{ admin.admin_permissions.length }} assigned
                  </span>
                  <span v-else class="text-secondary-400">None</span>
                </td>
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap table-cell font-medium">
                  <button
                    v-if="canEdit(admin)"
                    @click="openEdit(admin)"
                    class="btn btn-outline btn-sm"
                  >
                    <Icon name="heroicons:pencil" class="h-4 w-4 mr-1" />
                    Edit
                  </button>
                  <span v-else class="text-caption text-secondary-400">Locked</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center py-12">
          <Icon name="heroicons:user-group" class="h-12 w-12 mx-auto text-secondary-300 mb-4" />
          <p class="text-secondary-500">No administrators found</p>
        </div>
      </div>
    </div>

    <!-- Permission catalogue reference -->
    <div class="card mt-6">
      <div class="card-header">
        <h3 class="card-title text-secondary-900">Permission Catalogue</h3>
      </div>
      <div class="card-body grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="(domain, domainKey) in catalogue" :key="domainKey">
          <h4 class="text-body font-semibold text-secondary-900 mb-2">{{ domain.label }}</h4>
          <ul class="space-y-1">
            <li
              v-for="(label, permKey) in domain.permissions"
              :key="permKey"
              class="text-caption text-secondary-600 flex items-start"
            >
              <Icon name="heroicons:check-circle" class="h-4 w-4 text-success-500 flex-shrink-0 mr-1.5" />
              <span>{{ label }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Edit modal -->
    <div
      v-if="showEditModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-secondary-200">
          <h3 class="modal-title text-secondary-900">Edit Role &amp; Permissions</h3>
          <p class="mt-1 card-subtitle">{{ selectedAdmin?.full_name }} — {{ selectedAdmin?.email }}</p>
        </div>
        <form @submit.prevent="handleSave" class="p-6 space-y-6">
          <!-- Role -->
          <div>
            <label class="block text-label text-secondary-700 mb-2">Admin Role</label>
            <select v-model="formRole" class="input">
              <option v-for="role in roles" :key="role.value" :value="role.value">
                {{ role.label }}
              </option>
            </select>
            <p class="text-helper mt-1">
              Super admins implicitly hold every permission.
            </p>
          </div>

          <!-- Permissions -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="block text-label text-secondary-700">Permissions</label>
              <span class="text-caption text-secondary-500">{{ formPermissions.length }} selected</span>
            </div>
            <div
              v-if="formRole === 'super_admin'"
              class="bg-secondary-50 border border-secondary-200 rounded-lg p-3 mb-3 text-caption text-secondary-600"
            >
              Super admins automatically hold all permissions — individual selections are not required.
            </div>
            <div class="space-y-4 max-h-72 overflow-y-auto pr-1">
              <div v-for="(domain, domainKey) in catalogue" :key="domainKey" class="border border-secondary-200 rounded-lg p-3">
                <h4 class="text-body font-semibold text-secondary-800 mb-2">{{ domain.label }}</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                  <label
                    v-for="(label, permKey) in domain.permissions"
                    :key="permKey"
                    class="flex items-start text-body text-secondary-700 cursor-pointer"
                  >
                    <input
                      v-model="formPermissions"
                      type="checkbox"
                      :value="permKey"
                      class="mt-0.5 mr-2 rounded border-secondary-300 text-primary-600 focus:ring-primary-500"
                    />
                    <span>{{ label }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-end space-x-3 pt-2">
            <button type="button" @click="closeModal" class="btn btn-outline">Cancel</button>
            <button type="submit" :disabled="submitting" class="btn btn-primary">
              <div v-if="submitting" class="spinner mr-2"></div>
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: "admin-management",
  middleware: "admin",
});

const { $api, $toast } = useNuxtApp();
const authStore = useAuthStore();

const loading = ref(false);
const submitting = ref(false);
const isSuperAdmin = ref(false);

const admins = ref([]);
const roles = ref([]);
const catalogue = ref({});

const showEditModal = ref(false);
const selectedAdmin = ref(null);
const formRole = ref("admin");
const formPermissions = ref([]);

const currentUser = computed(() => authStore.user);

const isCurrentUser = (admin) => currentUser.value?.id === admin.id;

const canEdit = (admin) => {
  if (!isSuperAdmin.value) return false;
  if (isCurrentUser(admin)) return false; // cannot modify own privileges
  if (admin.admin_role === "super_admin") return false; // protect super admin accounts
  return true;
};

const getRoleBadgeClass = (role) => {
  const classes = {
    super_admin: "bg-error-100 text-error-800",
    admin: "bg-primary-100 text-primary-800",
    moderator: "bg-warning-100 text-warning-800",
  };
  return classes[role] || "bg-secondary-100 text-secondary-700";
};

const loadData = async () => {
  loading.value = true;
  try {
    const response = await $api.get("/admin/roles");
    if (response.success) {
      admins.value = response.data.admins || [];
      roles.value = response.data.roles || [];
      catalogue.value = response.data.permission_catalogue || {};
      isSuperAdmin.value = !!response.data.is_super_admin;
    }
  } catch (error) {
    console.error("Failed to load roles:", error);
    $toast.error("Failed to load roles and permissions");
  } finally {
    loading.value = false;
  }
};

const openEdit = (admin) => {
  selectedAdmin.value = admin;
  formRole.value = admin.admin_role || "admin";
  formPermissions.value = Array.isArray(admin.admin_permissions)
    ? [...admin.admin_permissions]
    : [];
  showEditModal.value = true;
};

const closeModal = () => {
  showEditModal.value = false;
  selectedAdmin.value = null;
  formRole.value = "admin";
  formPermissions.value = [];
};

const handleSave = async () => {
  if (!selectedAdmin.value) return;
  submitting.value = true;
  try {
    const response = await $api.put(`/admin/users/${selectedAdmin.value.id}/role`, {
      admin_role: formRole.value,
      admin_permissions: formRole.value === "super_admin" ? [] : formPermissions.value,
    });
    if (response.success) {
      $toast.success("Role and permissions updated successfully");
      closeModal();
      loadData();
    } else {
      $toast.error(response.message || "Failed to update role");
    }
  } catch (error) {
    console.error("Failed to update role:", error);
    const serverMsg =
      error?.data?.message ||
      error?.message ||
      "Failed to update role and permissions. Please try again.";
    $toast.error(serverMsg);
  } finally {
    submitting.value = false;
  }
};

onMounted(loadData);
</script>
