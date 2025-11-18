<!-- pages/UserDashboard/UserManagement/BusinessPlanUser/BusinessEmployeeManagement.vue -->
<template>
  <div class="min-h-screen bg-secondary-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-secondary-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div>
            <h1 class="text-2xl font-semibold text-secondary-900">
              Employee Management
            </h1>
            <p class="text-sm text-secondary-600">
              Manage employee details, cards and passwords
            </p>
          </div>
          <div class="flex items-center space-x-3">
            <button @click="showCardDesignModal = true" class="btn btn-primary">
              <Icon name="heroicons:paint-brush" class="h-4 w-4 mr-2" />
              Design Cards
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Account & Card Usage Stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="card">
          <div class="card-body">
            <div class="flex items-center">
              <div
                class="flex-shrink-0 h-12 w-12 rounded-lg bg-primary-100 flex items-center justify-center"
              >
                <Icon
                  name="heroicons:user-group"
                  class="h-6 w-6 text-primary-600"
                />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-secondary-600">
                  Total Accounts
                </p>
                <p class="text-2xl font-semibold text-secondary-900">
                  {{ totalAccounts }}
                </p>
                <p class="text-xs text-secondary-500">
                  1 owner + {{ employees.length }} employees
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <div class="flex items-center">
              <div
                class="flex-shrink-0 h-12 w-12 rounded-lg bg-info-100 flex items-center justify-center"
              >
                <Icon
                  name="heroicons:credit-card"
                  class="h-6 w-6 text-info-600"
                />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-secondary-600">
                  NFC Cards Ordered
                </p>
                <p class="text-2xl font-semibold text-secondary-900">
                  {{ orderedCardsCount }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <div class="flex items-center">
              <div
                class="flex-shrink-0 h-12 w-12 rounded-lg bg-warning-100 flex items-center justify-center"
              >
                <Icon
                  name="heroicons:square-3-stack-3d"
                  class="h-6 w-6 text-warning-600"
                />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-secondary-600">
                  Available Quota
                </p>
                <p class="text-2xl font-semibold text-secondary-900">
                  {{ availableQuota }} / {{ totalQuota }}
                </p>
                <p class="text-xs text-secondary-500">For accounts & cards</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Warning Messages -->
      <div class="space-y-4 mb-6">
        <!-- Quota Limit Warning -->
        <div
          v-if="availableQuota <= 0"
          class="bg-warning-50 border border-warning-200 rounded-lg p-4"
        >
          <div class="flex">
            <Icon
              name="heroicons:exclamation-triangle"
              class="h-5 w-5 text-warning-600 mt-0.5"
            />
            <div class="ml-3">
              <h3 class="text-sm font-medium text-warning-800">
                Quota Limit Reached
              </h3>
              <p class="text-sm text-warning-700 mt-1">
                You have reached the maximum quota ({{ totalQuota }}). Cannot
                create more accounts or order more cards until quota is
                increased. Please contact support to purchase additional quota.
              </p>
            </div>
          </div>
        </div>

        <!-- Info: Unified Quota Explanation -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex">
            <Icon
              name="heroicons:information-circle"
              class="h-5 w-5 text-blue-600 mt-0.5 flex-shrink-0"
            />
            <div class="ml-3">
              <h3 class="text-sm font-medium text-blue-800">
                Business Quota Information
              </h3>
              <p class="text-sm text-blue-700 mt-1">
                Your business has a total quota of
                <strong>{{ totalQuota }}</strong> for both accounts and NFC
                cards.
              </p>
              <div class="text-sm text-blue-700 mt-2 space-y-1">
                <p>📊 <strong>Current Usage:</strong></p>
                <p class="ml-4">
                  • Accounts: {{ totalAccounts }} / {{ totalQuota }} (1 owner +
                  {{ employees.length }} employees)
                </p>
                <p class="ml-4">
                  • Cards Ordered: {{ orderedCardsCount }} / {{ totalQuota }}
                </p>
                <p class="ml-4">• Available: {{ availableQuota }}</p>
              </div>
              <p class="text-sm text-blue-600 mt-2">
                💡 Employee accounts are created by Super Admin. You can upload
                employee details and design their cards.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Employees List -->
      <div class="card">
        <div class="card-header">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-secondary-900">
              Employee Accounts
            </h3>
            <div class="flex items-center space-x-3">
              <!-- Search -->
              <div class="relative">
                <Icon
                  name="heroicons:magnifying-glass"
                  class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-secondary-400"
                />
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search employees..."
                  class="pl-10 pr-4 py-2 border border-secondary-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                />
              </div>
              <!-- Filter -->
              <select
                v-model="filterStatus"
                class="px-4 py-2 border border-secondary-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              >
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>
        </div>
        <div class="card-body p-0">
          <!-- Loading State -->
          <div v-if="loading" class="text-center py-12">
            <div class="spinner mx-auto mb-4"></div>
            <p class="text-secondary-600">Loading employees...</p>
          </div>

          <!-- Empty State -->
          <div
            v-else-if="filteredEmployees.length === 0"
            class="text-center py-12"
          >
            <Icon
              name="heroicons:user-group"
              class="h-16 w-16 text-secondary-400 mx-auto mb-4"
            />
            <h3 class="text-lg font-semibold text-secondary-900 mb-2">
              No Employees Found
            </h3>
            <p class="text-secondary-500">
              {{
                searchQuery
                  ? "No employees match your search criteria."
                  : "No employees found. Employee accounts are created by Super Admin."
              }}
            </p>
          </div>

          <!-- Employees Table -->
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-secondary-200">
              <thead class="bg-secondary-50">
                <tr>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                  >
                    Employee
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                  >
                    Email
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                  >
                    Plan
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                  >
                    Status
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                  >
                    Created
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-medium text-secondary-500 uppercase tracking-wider"
                  >
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-secondary-200">
                <tr
                  v-for="employee in filteredEmployees"
                  :key="employee.id"
                  class="hover:bg-secondary-50 transition-colors"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div
                        class="flex-shrink-0 h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center"
                      >
                        <span class="text-sm font-medium text-primary-600">{{
                          getInitials(employee.name)
                        }}</span>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-secondary-900">
                          {{ employee.name }}
                        </div>
                        <div class="text-sm text-secondary-500">
                          {{ employee.position || "Employee" }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-secondary-900">
                      {{ employee.email }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-primary-100 text-primary-800"
                    >
                      {{ employee.subscription_plan || "Premium" }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        'px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full',
                        employee.subscription_active
                          ? 'bg-success-100 text-success-800'
                          : 'bg-secondary-100 text-secondary-800',
                      ]"
                    >
                      {{ employee.subscription_active ? "Active" : "Inactive" }}
                    </span>
                  </td>
                  <td
                    class="px-6 py-4 whitespace-nowrap text-sm text-secondary-500"
                  >
                    {{ formatDate(employee.created_at) }}
                  </td>
                  <td
                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                  >
                    <div class="flex items-center justify-end space-x-2">
                      <!-- Toggle Status Button -->
                      <button
                        @click="toggleEmployeeStatus(employee)"
                        :class="[
                          'px-2 py-1 rounded text-xs font-medium',
                          employee.subscription_active
                            ? 'bg-warning-100 text-warning-700 hover:bg-warning-200'
                            : 'bg-success-100 text-success-700 hover:bg-success-200',
                        ]"
                        :title="
                          employee.subscription_active
                            ? 'Deactivate employee'
                            : 'Activate employee'
                        "
                      >
                        {{ employee.subscription_active ? 'Deactivate' : 'Activate' }}
                      </button>
                      <!-- View Details Button -->
                      <button
                        @click="viewEmployeeDetails(employee)"
                        class="text-primary-600 hover:text-primary-900"
                        title="View Details"
                      >
                        <Icon name="heroicons:eye" class="h-5 w-5" />
                      </button>
                      <!-- Reset Password Button -->
                      <button
                        @click="resetEmployeePassword(employee)"
                        class="text-warning-600 hover:text-warning-900"
                        title="Reset Password"
                      >
                        <Icon name="heroicons:key" class="h-5 w-5" />
                      </button>
                      <!-- View NFC Card Button -->
                      <button
                        @click="viewEmployeeCard(employee)"
                        class="text-info-600 hover:text-info-900"
                        title="View NFC Card"
                      >
                        <Icon name="heroicons:credit-card" class="h-5 w-5" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- View Employee Details Modal -->
    <Transition name="modal">
      <div
        v-if="showViewDetailsModal"
        class="fixed inset-0 z-50 overflow-y-auto"
      >
        <div class="flex items-center justify-center min-h-screen px-4">
          <div
            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
            @click="closeViewDetailsModal"
          ></div>
          <div
            class="bg-white rounded-lg max-w-md w-full p-6 relative z-10 shadow-xl"
          >
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-secondary-900">
                Employee Details
              </h3>
              <button
                @click="closeViewDetailsModal"
                class="text-secondary-400 hover:text-secondary-600"
              >
                <Icon name="heroicons:x-mark" class="h-6 w-6" />
              </button>
            </div>

            <div v-if="selectedEmployee" class="space-y-4">
              <div class="flex items-center space-x-4 mb-4">
                <div
                  class="w-16 h-16 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-xl font-bold"
                >
                  {{ getInitials(selectedEmployee.name) }}
                </div>
                <div>
                  <h4 class="text-lg font-medium text-secondary-900">
                    {{ selectedEmployee.name }}
                  </h4>
                  <span
                    :class="[
                      'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                      selectedEmployee.subscription_active
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800',
                    ]"
                  >
                    {{
                      selectedEmployee.subscription_active
                        ? "Active"
                        : "Inactive"
                    }}
                  </span>
                </div>
              </div>

              <div class="space-y-3">
                <div>
                  <label
                    class="text-xs font-medium text-secondary-500 uppercase"
                    >Email</label
                  >
                  <p class="text-sm text-secondary-900 mt-1">
                    {{ selectedEmployee.email }}
                  </p>
                </div>
                <div>
                  <label
                    class="text-xs font-medium text-secondary-500 uppercase"
                    >Position</label
                  >
                  <p class="text-sm text-secondary-900 mt-1">
                    {{ selectedEmployee.position || "N/A" }}
                  </p>
                </div>
                <div>
                  <label
                    class="text-xs font-medium text-secondary-500 uppercase"
                    >Phone</label
                  >
                  <p class="text-sm text-secondary-900 mt-1">
                    {{ selectedEmployee.phone || "N/A" }}
                  </p>
                </div>
                <div>
                  <label
                    class="text-xs font-medium text-secondary-500 uppercase"
                    >Account Created</label
                  >
                  <p class="text-sm text-secondary-900 mt-1">
                    {{ formatDate(selectedEmployee.created_at) }}
                  </p>
                </div>
              </div>

              <div class="flex items-center justify-end pt-4">
                <button @click="closeViewDetailsModal" class="btn btn-primary">
                  Close
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Reset Password Modal -->
    <Transition name="modal">
      <div
        v-if="showResetPasswordModal"
        class="fixed inset-0 z-50 overflow-y-auto"
      >
        <div class="flex items-center justify-center min-h-screen px-4">
          <div
            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
            @click="closeResetPasswordModal"
          ></div>
          <div
            class="bg-white rounded-lg max-w-md w-full p-6 relative z-10 shadow-xl"
          >
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-secondary-900">
                Reset Employee Password
              </h3>
              <button
                @click="closeResetPasswordModal"
                class="text-secondary-400 hover:text-secondary-600"
              >
                <Icon name="heroicons:x-mark" class="h-6 w-6" />
              </button>
            </div>

            <div v-if="selectedEmployee" class="space-y-4">
              <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex">
                  <Icon
                    name="heroicons:exclamation-triangle"
                    class="h-5 w-5 text-yellow-600 flex-shrink-0"
                  />
                  <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                      A new password will be generated and sent to
                      <strong>{{ selectedEmployee.email }}</strong>
                    </p>
                  </div>
                </div>
              </div>

              <div class="flex items-center justify-end space-x-3 pt-4">
                <button
                  type="button"
                  @click="closeResetPasswordModal"
                  class="btn btn-outline"
                >
                  Cancel
                </button>
                <button
                  @click="confirmResetPassword"
                  class="btn btn-warning"
                  :disabled="resetPasswordLoading"
                >
                  <div v-if="resetPasswordLoading" class="spinner mr-2"></div>
                  {{ resetPasswordLoading ? "Resetting..." : "Reset Password" }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Card Design Modal -->
    <Transition name="modal">
      <div
        v-if="showCardDesignModal"
        class="fixed inset-0 z-50 overflow-y-auto"
      >
        <div class="flex items-center justify-center min-h-screen px-4">
          <div
            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
            @click="closeCardDesignModal"
          ></div>
          <div
            class="bg-white rounded-lg max-w-2xl w-full p-6 relative z-10 shadow-xl"
          >
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-secondary-900">
                Design Employee NFC Cards
              </h3>
              <button
                @click="closeCardDesignModal"
                class="text-secondary-400 hover:text-secondary-600"
              >
                <Icon name="heroicons:x-mark" class="h-6 w-6" />
              </button>
            </div>

            <div class="space-y-4">
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                  <Icon
                    name="heroicons:information-circle"
                    class="h-5 w-5 text-blue-600 flex-shrink-0"
                  />
                  <div class="ml-3">
                    <p class="text-sm text-blue-700">
                      Navigate to <strong>Card Design</strong> page to create
                      and manage NFC card templates for your employees.
                    </p>
                  </div>
                </div>
              </div>

              <div class="flex items-center justify-end space-x-3 pt-4">
                <button @click="closeCardDesignModal" class="btn btn-outline">
                  Cancel
                </button>
                <button @click="navigateToCardDesign" class="btn btn-primary">
                  Go to Card Design
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
// Layout
definePageMeta({
  layout: "user-dashboard",
  middleware: "auth",
});

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
const showCardDesignModal = ref(false);
const resetPasswordLoading = ref(false);

// Selected Employee for view/reset actions
const selectedEmployee = ref(null);

// Company unified quota (for both accounts and cards)
const totalQuota = ref(10); // Default: admin assigns 10 quota
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

// Computed
const activeEmployeesCount = computed(() => {
  return employees.value.filter((emp) => emp.subscription_active).length;
});

const filteredEmployees = computed(() => {
  let filtered = employees.value;

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(
      (emp) =>
        emp.name.toLowerCase().includes(query) ||
        emp.email.toLowerCase().includes(query) ||
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
      }));

      const quotaInfo = response.data.quota_info || {};
      totalQuota.value =
        quotaInfo.total_quota || quotaInfo.total_account_slots || 10;
      orderedCardsCount.value = quotaInfo.ordered_cards_count || 0;
    }
  } catch (error) {
    console.error("Failed to load employees:", error);
    $toast.error("Failed to load employees");
  } finally {
    loading.value = false;
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

// Card Design
const closeCardDesignModal = () => {
  showCardDesignModal.value = false;
};

const navigateToCardDesign = () => {
  navigateTo("/UserDashboard/NFCCardDesign/BusinessPlanNFCCard");
};

const viewEmployeeCard = (employee) => {
  // Navigate to employee's NFC card management page
  navigateTo(`/UserDashboard/employees/${employee.id}/card`);
};

// Toggle employee account status (activate/deactivate)
const toggleEmployeeStatus = async (employee) => {
  const newStatus = !employee.subscription_active;
  const action = newStatus ? 'activate' : 'deactivate';

  if (
    confirm(
      `Are you sure you want to ${action} ${employee.name}'s account?${
        !newStatus
          ? '\n\nDeactivated employees cannot log in or use the system.'
          : ''
      }`
    )
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

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
  display: inline-block;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
