<!-- pages/AdminManagement/roles-permissions.vue -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Access Control"
      eyebrow-icon="heroicons:shield-check"
      title="Roles &amp; Permissions"
      description="Manage admin roles and granular permissions across the platform."
    >
      <template #actions>
        <NuxtLink
          v-if="isSuperAdmin"
          to="/AdminManagement/users"
          class="admin-btn admin-btn--outline"
        >
          <Icon name="heroicons:user-plus" class="h-4 w-4" />
          Add administrator
        </NuxtLink>
        <button
          type="button"
          class="admin-btn admin-btn--outline"
          :disabled="loading"
          @click="loadData"
        >
          <Icon
            name="heroicons:arrow-path"
            class="h-4 w-4"
            :class="{ 'animate-spin': loading }"
          />
          Refresh
        </button>
      </template>
    </AdminPageHeader>

    <!-- Super admin only notice -->
    <div
      v-if="!loading && !isSuperAdmin"
      class="mb-5 flex items-start gap-3 rounded-xl border border-warning-300 bg-warning-50 p-4"
    >
      <Icon
        name="heroicons:shield-exclamation"
        class="mt-0.5 h-5 w-5 shrink-0 text-warning-600"
      />
      <div>
        <p class="text-body font-semibold text-secondary-900">
          Read-only access
        </p>
        <p class="mt-0.5 text-body text-secondary-600">
          Only a super admin can grant or modify admin roles and permissions.
          You can view the current assignments below but cannot change them.
        </p>
      </div>
    </div>

    <!-- Administrator list -->
    <AdminSectionCard flush>
      <template #title>Administrators</template>
      <template #header-actions>
        <span class="text-caption text-secondary-500">
          {{ admins.length }} total
        </span>
      </template>

      <!-- Loading -->
      <div
        v-if="loading"
        class="divide-y divide-secondary-100"
        role="status"
        aria-busy="true"
      >
        <div
          v-for="i in 5"
          :key="i"
          class="flex items-center gap-4 px-5 py-4"
        >
          <span class="admin-skeleton h-10 w-10 shrink-0 rounded-full"></span>
          <span class="admin-skeleton h-4 w-1/3"></span>
          <span class="admin-skeleton ml-auto h-4 w-20"></span>
          <span class="admin-skeleton h-8 w-16"></span>
        </div>
      </div>

      <!-- Empty -->
      <AdminEmptyState
        v-else-if="!admins.length"
        icon="heroicons:user-group"
        title="No administrators found"
        description="Administrators and their assigned roles will appear here."
      />

      <!-- Table -->
      <div v-else class="admin-scroll">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Administrator</th>
              <th>Role</th>
              <th>Permissions</th>
              <th class="admin-col-actions">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="admin in admins" :key="admin.id">
              <td>
                <div class="flex items-center gap-3">
                  <UserAvatar
                    :src="admin.profile_image"
                    :name="admin.full_name"
                    class="h-10 w-10 shrink-0 rounded-full ring-1 ring-secondary-200"
                  />
                  <div class="min-w-0">
                    <div class="flex items-center gap-2">
                      <span class="truncate text-body font-semibold text-secondary-900">
                        {{ admin.full_name }}
                      </span>
                      <span
                        v-if="isCurrentUser(admin)"
                        class="admin-badge admin-badge--neutral admin-badge--plain"
                      >
                        You
                      </span>
                    </div>
                    <div class="truncate text-caption text-secondary-500">
                      {{ admin.email }}
                    </div>
                  </div>
                </div>
              </td>
              <td>
                <AdminStatusBadge
                  :tone="
                    {
                      super_admin: 'brass',
                      admin: 'info',
                      moderator: 'warning',
                    }[admin.admin_role] || 'neutral'
                  "
                  :label="admin.admin_role_display"
                />
              </td>
              <td>
                <span
                  v-if="admin.admin_role === 'super_admin'"
                  class="admin-badge admin-badge--brass admin-badge--plain"
                >
                  All permissions
                </span>
                <span
                  v-else-if="(admin.admin_permissions || []).length"
                  class="admin-badge admin-badge--info admin-badge--plain"
                >
                  {{ admin.admin_permissions.length }} assigned
                </span>
                <span v-else class="text-caption text-secondary-400">None</span>
              </td>
              <td class="admin-col-actions">
                <button
                  v-if="canEdit(admin)"
                  type="button"
                  class="admin-btn admin-btn--outline admin-btn--sm"
                  @click="openEdit(admin)"
                >
                  <Icon name="heroicons:pencil" class="h-4 w-4" />
                  Edit
                </button>
                <span
                  v-else
                  class="inline-flex items-center gap-1.5 text-caption text-secondary-400"
                >
                  <Icon name="heroicons:lock-closed" class="h-3.5 w-3.5" />
                  Locked
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </AdminSectionCard>

    <!-- Permission catalogue reference -->
    <AdminSectionCard
      v-if="Object.keys(catalogue).length"
      class="mt-6"
      title="Permission Catalogue"
      subtitle="Every capability that can be assigned to an administrator role."
      accent
    >
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
        <div
          v-for="(domain, domainKey) in catalogue"
          :key="domainKey"
          class="rounded-xl border border-secondary-200 p-4"
        >
          <p class="admin-form-section-title">{{ domain.label }}</p>
          <ul class="mt-2 space-y-1.5">
            <li
              v-for="(label, permKey) in domain.permissions"
              :key="permKey"
              class="flex items-start gap-2 text-caption text-secondary-600"
            >
              <Icon
                name="heroicons:ellipsis-horizontal-circle"
                class="mt-0.5 h-4 w-4 shrink-0 text-secondary-400"
              />
              <span>{{ label }}</span>
            </li>
          </ul>
        </div>
      </div>
    </AdminSectionCard>

    <!-- Edit dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="showEditModal"
          class="admin-dialog-backdrop"
          @click.self="closeModal"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="Edit role and permissions"
            style="width: min(46rem, 100%); margin-top: 5vh; max-height: 90vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
                >
                  <Icon name="heroicons:key" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">Edit role &amp; permissions</p>
                  <p class="admin-panel-subtitle">
                    {{ selectedAdmin?.full_name }} — {{ selectedAdmin?.email }}
                  </p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="closeModal"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <form class="flex min-h-0 flex-1 flex-col" @submit.prevent="handleSave">
              <div class="admin-panel-body flex-1 space-y-6 overflow-y-auto">
                <!-- Role -->
                <div class="admin-form-section">
                  <p class="admin-form-section-title">Assigned role</p>
                  <p class="admin-form-section-desc">
                    Super admins implicitly hold every permission.
                  </p>
                  <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                      <label class="admin-label">Admin role</label>
                      <select v-model="formRole" class="admin-field">
                        <option
                          v-for="role in roles"
                          :key="role.value"
                          :value="role.value"
                        >
                          {{ role.label }}
                        </option>
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Permissions -->
                <div class="admin-form-section">
                  <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                      <p class="admin-form-section-title">Permissions</p>
                      <p class="admin-form-section-desc">
                        Fine-grained capabilities granted to this administrator.
                      </p>
                    </div>
                    <span class="admin-badge admin-badge--neutral admin-badge--plain">
                      {{ formPermissions.length }} selected
                    </span>
                  </div>

                  <div
                    v-if="formRole === 'super_admin'"
                    class="mt-4 flex items-start gap-3 rounded-xl border border-secondary-200 bg-secondary-50 p-3"
                  >
                    <Icon
                      name="heroicons:information-circle"
                      class="mt-0.5 h-5 w-5 shrink-0 text-info-600"
                    />
                    <p class="text-caption text-secondary-600">
                      Super admins automatically hold all permissions —
                      individual selections are not required.
                    </p>
                  </div>

                  <div class="mt-4 space-y-4">
                    <div
                      v-for="(domain, domainKey) in catalogue"
                      :key="domainKey"
                      class="rounded-xl border border-secondary-200 p-4"
                    >
                      <div class="flex items-center justify-between gap-3">
                        <p class="admin-form-section-title">{{ domain.label }}</p>
                        <label
                          class="flex cursor-pointer items-center gap-2 text-caption font-medium text-secondary-600"
                        >
                          <input
                            type="checkbox"
                            :checked="isDomainFullySelected(domainKey)"
                            :indeterminate.prop="isDomainPartiallySelected(domainKey)"
                            @change="toggleDomainAll(domainKey)"
                            class="rounded border-secondary-300 text-accent-600 focus:ring-accent-500"
                          />
                          Select all
                        </label>
                      </div>
                      <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <label
                          v-for="(label, permKey) in domain.permissions"
                          :key="permKey"
                          class="flex cursor-pointer items-start gap-2 text-body text-secondary-700"
                        >
                          <input
                            v-model="formPermissions"
                            type="checkbox"
                            :value="permKey"
                            class="mt-0.5 rounded border-secondary-300 text-accent-600 focus:ring-accent-500"
                          />
                          <span>{{ label }}</span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="admin-dialog-foot">
                <button
                  type="button"
                  class="admin-btn admin-btn--ghost"
                  @click="closeModal"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="submitting"
                  class="admin-btn admin-btn--primary"
                >
                  <Icon
                    v-if="submitting"
                    name="heroicons:arrow-path"
                    class="h-4 w-4 animate-spin"
                  />
                  Save changes
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>
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

const domainPermissionKeys = (domainKey) =>
  catalogue.value[domainKey]
    ? Object.keys(catalogue.value[domainKey].permissions)
    : [];

const isDomainFullySelected = (domainKey) => {
  const keys = domainPermissionKeys(domainKey);
  return keys.length > 0 && keys.every((k) => formPermissions.value.includes(k));
};

const isDomainPartiallySelected = (domainKey) => {
  const keys = domainPermissionKeys(domainKey);
  const selectedCount = keys.filter((k) => formPermissions.value.includes(k)).length;
  return selectedCount > 0 && selectedCount < keys.length;
};

const toggleDomainAll = (domainKey) => {
  const keys = domainPermissionKeys(domainKey);
  if (isDomainFullySelected(domainKey)) {
    formPermissions.value = formPermissions.value.filter((p) => !keys.includes(p));
  } else {
    const merged = new Set([...formPermissions.value, ...keys]);
    formPermissions.value = Array.from(merged);
  }
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
