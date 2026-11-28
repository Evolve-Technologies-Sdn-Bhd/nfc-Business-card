<!-- pages/UserDashboard/UserManagement/BusinessPlanUser/BusinessEmployeeManagement.vue
     Team Members (Business). Owner + company admins manage members; card
     managers can view the team and jump to its cards. Every action button is
     shown only to roles the API accepts it from (business.manager routes). -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Team"
      eyebrow-icon="heroicons:users"
      title="Team Members"
      description="Everyone on your Business account: add or remove members, set their role and status, and choose the company details they share."
    >
      <template #actions>
        <NuxtLink to="/UserDashboard/NFCCardDesign/BusinessPlanNFCCard" class="admin-btn admin-btn--outline">
          <Icon name="heroicons:paint-brush" class="h-4 w-4" />
          Design team cards
        </NuxtLink>
        <button
          v-if="canManageCompany"
          type="button"
          class="admin-btn admin-btn--primary"
          :disabled="loading || availableAccountSlots <= 0"
          :title="availableAccountSlots <= 0 ? 'All seats are in use' : 'Add a team member'"
          @click="openAddMember"
        >
          <Icon name="heroicons:user-plus" class="h-4 w-4" />
          Add member
        </button>
      </template>
    </AdminPageHeader>

    <!-- Seats -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
      <AdminStatTile
        label="Seats used"
        :value="totalQuota > 0 ? `${totalAccounts} / ${totalQuota}` : totalAccounts"
        icon="heroicons:user-group"
        :hint="`You + ${employees.length} member${employees.length === 1 ? '' : 's'}`"
        :loading="loading"
      />
      <AdminStatTile
        label="Free seats"
        :value="availableAccountSlots"
        icon="heroicons:user-plus"
        :hint="availableAccountSlots > 0 ? 'Ready for new members' : 'No free seats'"
        :loading="loading"
      />
      <AdminStatTile
        label="Cards ordered"
        :value="totalQuota > 0 ? `${orderedCardsCount} / ${totalQuota}` : orderedCardsCount"
        icon="heroicons:credit-card"
        hint="Company NFC cards"
        :loading="loading"
      />
    </div>

    <!-- One clear notice only when seats block adding people -->
    <div
      v-if="!loading && availableAccountSlots <= 0"
      class="mb-6 flex items-start gap-3 rounded-xl border border-warning-200 bg-warning-50 px-4 py-3"
      role="status"
    >
      <Icon name="heroicons:exclamation-triangle" class="mt-0.5 h-5 w-5 shrink-0 text-warning-600" />
      <div class="text-body text-warning-800">
        <p class="font-semibold">{{ totalQuota > 0 ? "All seats are in use" : "No seats assigned yet" }}</p>
        <p class="text-warning-700">
          {{
            totalQuota > 0
              ? `Your plan includes ${totalQuota} seats (you count as one). Remove a member or contact NFCGo support to add seats.`
              : "NFCGo hasn't set a seat quota for this account yet. Contact support to add team members."
          }}
        </p>
      </div>
    </div>

    <!-- Members -->
    <AdminSectionCard flush>
      <template #title>Members</template>
      <template #header-actions>
        <div class="flex flex-wrap items-center gap-2">
          <div class="relative">
            <Icon
              name="heroicons:magnifying-glass"
              class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-secondary-400"
            />
            <input
              v-model="searchQuery"
              type="search"
              placeholder="Search name, e-mail, title…"
              class="admin-field !py-1.5 !pl-9 w-56"
              aria-label="Search members"
            />
          </div>
          <select v-model="filterStatus" class="admin-field !py-1.5 w-36" aria-label="Filter by status">
            <option value="all">All statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Deactivated</option>
          </select>
        </div>
      </template>

      <div v-if="loading" class="divide-y divide-secondary-100" role="status" aria-busy="true">
        <div v-for="i in 4" :key="i" class="flex items-center gap-4 px-5 py-4">
          <span class="admin-skeleton h-10 w-10 shrink-0 rounded-full"></span>
          <span class="admin-skeleton h-4 w-1/4"></span>
          <span class="admin-skeleton h-4 w-20"></span>
          <span class="admin-skeleton ml-auto h-4 w-24"></span>
        </div>
      </div>

      <AdminEmptyState
        v-else-if="employees.length === 0"
        icon="heroicons:user-group"
        title="No team members yet"
        :description="canManageCompany ? 'Add a member to give them their own profile and card. They get an e-mail to set their password.' : 'Your team has no members yet.'"
      >
        <template v-if="canManageCompany && availableAccountSlots > 0" #actions>
          <button type="button" class="admin-btn admin-btn--primary admin-btn--sm" @click="openAddMember">
            <Icon name="heroicons:user-plus" class="h-4 w-4" />
            Add member
          </button>
        </template>
      </AdminEmptyState>

      <AdminEmptyState
        v-else-if="filteredEmployees.length === 0"
        icon="heroicons:magnifying-glass"
        title="No members match"
        description="Try another name or clear the status filter."
      >
        <template #actions>
          <button type="button" class="admin-btn admin-btn--outline admin-btn--sm" @click="searchQuery = ''; filterStatus = 'all'">
            Clear search
          </button>
        </template>
      </AdminEmptyState>

      <div v-else class="admin-scroll">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Member</th>
              <th>Role</th>
              <th>Status</th>
              <th>Joined</th>
              <th class="text-right"><span class="sr-only">Actions</span></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="employee in filteredEmployees" :key="employee.id">
              <td>
                <button type="button" class="flex items-center gap-3 text-left min-w-0" @click="viewEmployeeDetails(employee)">
                  <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent-100 text-caption font-semibold text-accent-800">
                    {{ getInitials(employee.name) }}
                  </span>
                  <span class="min-w-0">
                    <span class="block truncate font-medium text-secondary-900">{{ employee.name }}</span>
                    <span class="block truncate text-caption text-secondary-500">
                      {{ employee.email }}<template v-if="employee.position"> · {{ employee.position }}</template>
                    </span>
                  </span>
                </button>
              </td>
              <td class="whitespace-nowrap">
                <!-- Only the owner reassigns roles -->
                <select
                  v-if="isOwnerViewer"
                  :value="employee.business_role || 'member'"
                  :disabled="updatingRoleId === employee.id"
                  class="admin-field !py-1 !text-sm w-40"
                  :aria-label="`Role for ${employee.name}`"
                  @change="updateEmployeeRole(employee, $event.target.value)"
                >
                  <option value="admin">Admin</option>
                  <option value="card_manager">Card manager</option>
                  <option value="member">Member</option>
                </select>
                <span v-else class="admin-badge" :class="employee.business_role === 'admin' ? 'admin-badge--brass' : 'admin-badge--neutral'">
                  {{ roleLabels[employee.business_role] || "Member" }}
                </span>
              </td>
              <td class="whitespace-nowrap">
                <span class="admin-badge" :class="employee.subscription_active ? 'admin-badge--success' : 'admin-badge--neutral'">
                  {{ employee.subscription_active ? "Active" : "Deactivated" }}
                </span>
              </td>
              <td class="whitespace-nowrap text-secondary-600">{{ formatDate(employee.created_at) }}</td>
              <td>
                <div class="flex items-center justify-end gap-1">
                  <button
                    type="button"
                    class="admin-icon-btn"
                    :title="`View ${employee.name}'s cards`"
                    :aria-label="`View ${employee.name}'s cards`"
                    @click="viewEmployeeCard(employee)"
                  >
                    <Icon name="heroicons:credit-card" class="h-[18px] w-[18px]" />
                  </button>
                  <template v-if="canManageCompany && canActOn(employee)">
                    <button
                      type="button"
                      class="admin-icon-btn"
                      title="Send password reset link"
                      :aria-label="`Send ${employee.name} a password reset link`"
                      @click="resetEmployeePassword(employee)"
                    >
                      <Icon name="heroicons:key" class="h-[18px] w-[18px]" />
                    </button>
                    <button
                      type="button"
                      class="admin-icon-btn"
                      :title="employee.subscription_active ? 'Deactivate' : 'Reactivate'"
                      :aria-label="`${employee.subscription_active ? 'Deactivate' : 'Reactivate'} ${employee.name}`"
                      @click="toggleEmployeeStatus(employee)"
                    >
                      <Icon :name="employee.subscription_active ? 'heroicons:pause-circle' : 'heroicons:play-circle'" class="h-[18px] w-[18px]" />
                    </button>
                    <button
                      type="button"
                      class="admin-icon-btn hover:!text-error-600"
                      title="Remove member"
                      :aria-label="`Remove ${employee.name}`"
                      @click="removeEmployee(employee)"
                    >
                      <Icon name="heroicons:user-minus" class="h-[18px] w-[18px]" />
                    </button>
                  </template>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <template v-if="!loading && employees.length" #footer>
        <p class="text-caption text-secondary-500">
          Removing a member frees their seat and returns their company cards to you.
        </p>
      </template>
    </AdminSectionCard>

    <!-- Company data shared with members + which fields they cannot change -->
    <div class="mt-8">
      <BusinessCompanySettings v-if="!loading" :can-manage="canManageCompany" />
    </div>

    <!-- Add member -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div v-if="showAddMemberModal" class="admin-dialog-backdrop" @click.self="showAddMemberModal = false">
          <form
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="Add team member"
            style="width: min(32rem, 100%); margin-top: 8vh"
            @submit.prevent="submitAddMember"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700">
                  <Icon name="heroicons:user-plus" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">Add team member</p>
                  <p class="admin-panel-subtitle">Uses 1 of {{ availableAccountSlots }} free seat{{ availableAccountSlots === 1 ? "" : "s" }}</p>
                </div>
              </div>
              <button type="button" class="admin-icon-btn" aria-label="Close" @click="showAddMemberModal = false">
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>
            <div class="admin-panel-body space-y-4">
              <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                  <label class="admin-label" for="nm-first">First name</label>
                  <input id="nm-first" v-model.trim="newMember.first_name" required class="admin-field" autocomplete="off" />
                </div>
                <div>
                  <label class="admin-label" for="nm-last">Last name</label>
                  <input id="nm-last" v-model.trim="newMember.last_name" required class="admin-field" autocomplete="off" />
                </div>
              </div>
              <div>
                <label class="admin-label" for="nm-email">Work e-mail</label>
                <input id="nm-email" v-model.trim="newMember.email" type="email" required class="admin-field" autocomplete="off" />
                <p class="admin-hint">We e-mail them a link to set their password.</p>
              </div>
              <div>
                <label class="admin-label" for="nm-title">Job title <span class="font-normal text-secondary-400">(optional)</span></label>
                <input id="nm-title" v-model.trim="newMember.job_title" class="admin-field" autocomplete="off" />
              </div>
              <div>
                <label class="admin-label" for="nm-role">Role</label>
                <select id="nm-role" v-model="newMember.role" class="admin-field">
                  <option value="member">Member — edits their own profile</option>
                  <option value="card_manager">Card manager — also assigns company cards</option>
                  <option v-if="isOwnerViewer" value="admin">Admin — manages the team and company settings</option>
                </select>
              </div>
            </div>
            <div class="admin-dialog-foot">
              <button type="button" class="admin-btn admin-btn--outline" @click="showAddMemberModal = false">Cancel</button>
              <button type="submit" class="admin-btn admin-btn--primary" :disabled="addMemberLoading">
                <Icon v-if="addMemberLoading" name="heroicons:arrow-path" class="h-4 w-4 animate-spin" />
                {{ addMemberLoading ? "Adding…" : "Add member" }}
              </button>
            </div>
          </form>
        </div>
      </Transition>
    </Teleport>

    <!-- Member details -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div v-if="showViewDetailsModal && selectedEmployee" class="admin-dialog-backdrop" @click.self="closeViewDetailsModal">
          <div class="admin-dialog flex flex-col" role="dialog" aria-modal="true" aria-label="Member details" style="width: min(30rem, 100%); margin-top: 8vh">
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3 min-w-0">
                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-accent-100 font-semibold text-accent-800">
                  {{ getInitials(selectedEmployee.name) }}
                </span>
                <div class="min-w-0">
                  <p class="admin-panel-title truncate">{{ selectedEmployee.name }}</p>
                  <p class="admin-panel-subtitle">
                    {{ roleLabels[selectedEmployee.business_role] || "Member" }} ·
                    {{ selectedEmployee.subscription_active ? "Active" : "Deactivated" }}
                  </p>
                </div>
              </div>
              <button type="button" class="admin-icon-btn" aria-label="Close" @click="closeViewDetailsModal">
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>
            <dl class="admin-panel-body grid grid-cols-1 gap-4">
              <div>
                <dt class="admin-label">E-mail</dt>
                <dd class="text-body text-secondary-900 break-all">{{ selectedEmployee.email }}</dd>
              </div>
              <div>
                <dt class="admin-label">Job title</dt>
                <dd class="text-body text-secondary-900">{{ selectedEmployee.position || "—" }}</dd>
              </div>
              <div>
                <dt class="admin-label">Phone</dt>
                <dd class="text-body text-secondary-900">{{ selectedEmployee.phone || "—" }}</dd>
              </div>
              <div>
                <dt class="admin-label">Joined</dt>
                <dd class="text-body text-secondary-900">{{ formatDate(selectedEmployee.created_at) }}</dd>
              </div>
            </dl>
            <div class="admin-dialog-foot">
              <button type="button" class="admin-btn admin-btn--outline" @click="viewEmployeeCard(selectedEmployee)">
                <Icon name="heroicons:credit-card" class="h-4 w-4" />
                View cards
              </button>
              <button type="button" class="admin-btn admin-btn--primary" @click="closeViewDetailsModal">Close</button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Password reset -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div v-if="showResetPasswordModal && selectedEmployee" class="admin-dialog-backdrop" @click.self="closeResetPasswordModal">
          <div class="admin-dialog flex flex-col" role="dialog" aria-modal="true" aria-label="Send password reset link" style="width: min(28rem, 100%); margin-top: 10vh">
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-warning-100 text-warning-700">
                  <Icon name="heroicons:key" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">Send password reset link</p>
                  <p class="admin-panel-subtitle">{{ selectedEmployee.name }}</p>
                </div>
              </div>
              <button type="button" class="admin-icon-btn" aria-label="Close" @click="closeResetPasswordModal">
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>
            <div class="admin-panel-body">
              <p class="text-body text-secondary-700">
                We'll e-mail <strong class="text-secondary-900">{{ selectedEmployee.email }}</strong> a link to set a new
                password. The link is valid for 1 hour, and their current password keeps working until they use it.
              </p>
            </div>
            <div class="admin-dialog-foot">
              <button type="button" class="admin-btn admin-btn--outline" @click="closeResetPasswordModal">Cancel</button>
              <button type="button" class="admin-btn admin-btn--primary" :disabled="resetPasswordLoading" @click="confirmResetPassword">
                <Icon v-if="resetPasswordLoading" name="heroicons:arrow-path" class="h-4 w-4 animate-spin" />
                {{ resetPasswordLoading ? "Sending…" : "Send link" }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <AdminConfirmDialog
      v-model="confirmState.open"
      :title="confirmState.title"
      :message="confirmState.message"
      :confirm-label="confirmState.confirmLabel"
      :cancel-label="confirmState.cancelLabel"
      :tone="confirmState.tone"
      @confirm="resolveConfirm(true)"
      @cancel="resolveConfirm(false)"
    />
  </div>
</template>

<script setup>
// Layout
definePageMeta({
  layout: "user-dashboard",
  middleware: ["auth"],
});

// Styled replacement for window.confirm() (composables/useConfirmDialog.js)
const { confirmState, askConfirm, resolveConfirm } = useConfirmDialog();


// Stores and Composables
const { $api, $toast } = useNuxtApp();
const authStore = useAuthStore();

// Reactive Data
const loading = ref(true);
const employees = ref([]);
const searchQuery = ref("");
const filterStatus = ref("all");

// Modals
const showViewDetailsModal = ref(false);
const showResetPasswordModal = ref(false);
const resetPasswordLoading = ref(false);

// Role management — only the account owner may reassign delegated roles
const isOwnerViewer = ref(false);
const viewerRole = ref(null);
// Owner and company admins add/remove members and edit company settings.
const canManageCompany = computed(() => ["owner", "admin"].includes(viewerRole.value));
// A company admin may not act on another admin (the API enforces the same rule).
const canActOn = (employee) =>
  isOwnerViewer.value || (viewerRole.value === "admin" && employee.business_role !== "admin");

// Add member
const showAddMemberModal = ref(false);
const addMemberLoading = ref(false);
const blankMember = () => ({ first_name: "", last_name: "", email: "", job_title: "", role: "member" });
const newMember = ref(blankMember());
const roleLabels = {
  owner: "Owner",
  admin: "Admin",
  card_manager: "Card Manager",
  member: "Member",
};
const updatingRoleId = ref(null);

// Selected Employee for view/reset actions
const selectedEmployee = ref(null);

// Company unified quota (for both accounts and cards)
// Quota comes only from the API (assigned by NFCGo admin). No invented default:
// a made-up 10 used to show free seats that did not exist.
const totalQuota = ref(0);
const totalAccounts = computed(() => 1 + employees.value.length); // 1 owner + employees
const orderedCardsCount = ref(0); // Actual number of Business Plan cards ordered

// Available quota is the lesser of:
// 1. Quota not used by accounts (total - total accounts)
// 2. Quota not used by cards (total - ordered cards)
const availableQuota = computed(() => {
  const accountsRemaining = Math.max(0, totalQuota.value - totalAccounts.value);
  const cardsRemaining = Math.max(
    0,
    totalQuota.value - orderedCardsCount.value
  );
  return Math.min(accountsRemaining, cardsRemaining);
});

// Seats left for new accounts (owner counts as one).
const availableAccountSlots = computed(() => Math.max(0, totalQuota.value - totalAccounts.value));

const openAddMember = () => {
  newMember.value = blankMember();
  showAddMemberModal.value = true;
};

const submitAddMember = async () => {
  addMemberLoading.value = true;
  try {
    const response = await $api.post("/business/employees", newMember.value);
    if (response.success) {
      $toast.success(response.message || "Member added");
      showAddMemberModal.value = false;
      await loadEmployees();
    }
  } catch (error) {
    const errors = error?.data?.errors;
    $toast.error(
      (errors && Object.values(errors)[0]?.[0]) || error?.data?.message || "Failed to add member"
    );
  } finally {
    addMemberLoading.value = false;
  }
};

const removeEmployee = async (employee) => {
  const ok = await askConfirm({
    title: `Remove ${employee.name}?`,
    message:
      "Their account is deleted and the seat is freed. Company cards they hold come back to you and their public profile goes offline until you reassign the card.",
    confirmLabel: "Remove member",
    tone: "danger",
  });
  if (!ok) return;
  try {
    const response = await $api.delete(`/business/employees/${employee.id}`);
    if (response.success) {
      $toast.success(response.message || "Member removed");
      await loadEmployees();
    }
  } catch (error) {
    $toast.error(error?.data?.message || "Failed to remove member");
  }
};

const filteredEmployees = computed(() => {
  let filtered = employees.value;

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(
      (emp) =>
        String(emp.name || "").toLowerCase().includes(query) ||
        String(emp.email || "").toLowerCase().includes(query) ||
        (emp.position && emp.position.toLowerCase().includes(query))
    );
  }

  // Filter by status
  if (filterStatus.value !== "all") {
    const isActive = filterStatus.value === "active";
    filtered = filtered.filter((emp) => emp.subscription_active === isActive);
  }

  return filtered;
});

// Methods
const loadEmployees = async () => {
  loading.value = true;
  try {
    const response = await $api.get("/business/employees");

    if (response.success && response.data) {
      employees.value = (response.data.employees || []).map((emp) => ({
        ...emp,
        subscription_active: emp.subscription_active,
        name: emp.full_name || `${emp.first_name} ${emp.last_name}`,
        position: emp.job_title,
        business_role: emp.business_role || "member",
      }));

      // Only the owner sees role selectors
      isOwnerViewer.value = response.data.is_owner === true;
      viewerRole.value = response.data.viewer_role || null;

      const quotaInfo = response.data.quota_info || {};
      totalQuota.value =
        quotaInfo.total_quota ?? quotaInfo.total_account_slots ?? 0;
      orderedCardsCount.value = quotaInfo.ordered_cards_count || 0;
    }
  } catch (error) {
    console.error("Failed to load employees:", error);
    $toast.error("Failed to load employees");
  } finally {
    loading.value = false;
  }
};

// Update an employee's delegated Business role (owner-only action)
const updateEmployeeRole = async (employee, role) => {
  if (!employee || updatingRoleId.value) return;

  const previous = employee.business_role;
  updatingRoleId.value = employee.id;

  try {
    const response = await $api.post(
      `/business/employees/${employee.id}/role`,
      { role }
    );

    if (response.success) {
      employee.business_role = response.data?.business_role || role;
      $toast.success("Employee role updated");
    }
  } catch (error) {
    // Revert the selection if the request was rejected
    employee.business_role = previous;
    console.error("Failed to update employee role:", error);
    $toast.error(error?.data?.message || "Failed to update employee role");
  } finally {
    updatingRoleId.value = null;
  }
};

// View Employee Details
const viewEmployeeDetails = (employee) => {
  selectedEmployee.value = employee;
  showViewDetailsModal.value = true;
};

const closeViewDetailsModal = () => {
  showViewDetailsModal.value = false;
  selectedEmployee.value = null;
};

// Reset Employee Password
const resetEmployeePassword = (employee) => {
  selectedEmployee.value = employee;
  showResetPasswordModal.value = true;
};

const confirmResetPassword = async () => {
  if (!selectedEmployee.value) return;

  resetPasswordLoading.value = true;
  try {
    const response = await $api.post(
      `/business/employees/${selectedEmployee.value.id}/reset-password`
    );

    if (response.success) {
      $toast.success(
        `Password reset email sent to ${selectedEmployee.value.email}`
      );
      closeResetPasswordModal();
    }
  } catch (error) {
    console.error("Failed to reset password:", error);
    if (error.data?.message) {
      $toast.error(error.data.message);
    } else {
      $toast.error("Failed to reset password");
    }
  } finally {
    resetPasswordLoading.value = false;
  }
};

const closeResetPasswordModal = () => {
  showResetPasswordModal.value = false;
  selectedEmployee.value = null;
};

const viewEmployeeCard = async (employee) => {
  // Navigate to Business Card Management page with employee filter
  await navigateTo({
    path: '/UserDashboard/UserManagement/BusinessPlanUser/BusinessCardManagement',
    query: {
      employee_id: employee.id,
      employee_name: employee.name
    }
  });
};

// Toggle employee account status (activate/deactivate)
const toggleEmployeeStatus = async (employee) => {
  const newStatus = !employee.subscription_active;
  const action = newStatus ? 'activate' : 'deactivate';

  if (
    await askConfirm({
      title: newStatus ? `Reactivate ${employee.name}?` : `Deactivate ${employee.name}?`,
      message: newStatus
        ? "They will be able to sign in and use their card again."
        : "They will not be able to sign in or use NFCGo until you reactivate the account.",
      confirmLabel: newStatus ? "Reactivate" : "Deactivate",
      tone: newStatus ? "primary" : "danger",
    })
  ) {
    try {
      const response = await $api.post(
        `/business/employees/${employee.id}/toggle-status`,
        { subscription_active: newStatus }
      );

      if (response.success) {
        employee.subscription_active = newStatus;
        $toast.success(
          `${employee.name}'s account has been ${
            newStatus ? 'activated' : 'deactivated'
          }.`
        );
      }
    } catch (error) {
      console.error('Failed to toggle employee status:', error);
      if (error.data?.message) {
        $toast.error(error.data.message);
      } else {
        $toast.error('Failed to update employee status');
      }
    }
  }
};

const getInitials = (name) => {
  if (!name) return "??";
  const parts = name.split(" ");
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return name.substring(0, 2).toUpperCase();
};

const formatDate = (date) => {
  if (!date) return "N/A";
  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

// Lifecycle
onMounted(() => {
  loadEmployees();
});
</script>
