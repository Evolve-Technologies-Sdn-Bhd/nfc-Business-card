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
              Manage employee accounts and NFC cards
            </p>
          </div>
          <button
            @click="showAddEmployeeModal = true"
            :disabled="!canAddMoreEmployees"
            class="btn btn-primary"
            :class="{ 'opacity-50 cursor-not-allowed': !canAddMoreEmployees }"
          >
            <Icon name="heroicons:plus" class="h-4 w-4 mr-2" />
            Add Employee
          </button>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Account & Card Usage Stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
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
                  Total Employees
                </p>
                <p class="text-2xl font-semibold text-secondary-900">
                  {{ employees.length }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <div class="flex items-center">
              <div
                class="flex-shrink-0 h-12 w-12 rounded-lg bg-success-100 flex items-center justify-center"
              >
                <Icon
                  name="heroicons:check-circle"
                  class="h-6 w-6 text-success-600"
                />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-secondary-600">
                  Active Accounts
                </p>
                <p class="text-2xl font-semibold text-secondary-900">
                  {{ activeEmployeesCount }}
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
                  Account Slots
                </p>
                <p class="text-2xl font-semibold text-secondary-900">
                  {{ availableSlots }} / {{ totalSlots }}
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
                  NFC Card Quota
                </p>
                <p class="text-2xl font-semibold text-secondary-900">
                  {{ availableCardQuota }} / {{ totalCardQuota }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Warning Messages -->
      <div class="space-y-4 mb-6">
        <!-- Account Limit Warning -->
        <div
          v-if="availableSlots <= 0"
          class="bg-warning-50 border border-warning-200 rounded-lg p-4"
        >
          <div class="flex">
            <Icon
              name="heroicons:exclamation-triangle"
              class="h-5 w-5 text-warning-600 mt-0.5"
            />
            <div class="ml-3">
              <h3 class="text-sm font-medium text-warning-800">
                Account Limit Reached
              </h3>
              <p class="text-sm text-warning-700 mt-1">
                You have reached the maximum number of employee accounts ({{
                  totalSlots
                }}). Please contact support to purchase additional slots.
              </p>
            </div>
          </div>
        </div>

        <!-- Card Quota Warning -->
        <div
          v-if="availableCardQuota <= 0"
          class="bg-warning-50 border border-warning-200 rounded-lg p-4"
        >
          <div class="flex">
            <Icon
              name="heroicons:exclamation-triangle"
              class="h-5 w-5 text-warning-600 mt-0.5"
            />
            <div class="ml-3">
              <h3 class="text-sm font-medium text-warning-800">
                NFC Card Quota Reached
              </h3>
              <p class="text-sm text-warning-700 mt-1">
                You have ordered the maximum number of Business Plan NFC cards
                ({{ totalCardQuota }}). No more Business Plan cards can be
                ordered until quota is increased. Please contact support to
                purchase additional card quota.
              </p>
            </div>
          </div>
        </div>

        <!-- Info: Card Quota Explanation -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex">
            <Icon
              name="heroicons:information-circle"
              class="h-5 w-5 text-blue-600 mt-0.5 flex-shrink-0"
            />
            <div class="ml-3">
              <h3 class="text-sm font-medium text-blue-800">
                NFC Card Quota Information
              </h3>
              <p class="text-sm text-blue-700 mt-1">
                Card quota is deducted only when an account (admin or employee)
                <strong>successfully orders a Business Plan NFC card</strong>.
              </p>
              <p class="text-sm text-blue-700 mt-2">
                <strong>Currently ordered:</strong> {{ usedCardQuota }} /
                {{ totalCardQuota }} cards
              </p>
              <p class="text-sm text-blue-600 mt-1">
                Creating employee accounts does NOT consume card quota.
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
            <p class="text-secondary-600 mb-6">
              {{
                searchQuery
                  ? "No employees match your search."
                  : "Get started by adding your first employee."
              }}
            </p>
            <button
              v-if="!searchQuery"
              @click="showAddEmployeeModal = true"
              class="btn btn-primary"
            >
              <Icon name="heroicons:plus" class="h-4 w-4 mr-2" />
              Add First Employee
            </button>
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
                        employee.is_active
                          ? 'bg-success-100 text-success-800'
                          : 'bg-secondary-100 text-secondary-800',
                      ]"
                    >
                      {{ employee.is_active ? "Active" : "Inactive" }}
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
                      <!-- View NFC Card Button -->
                      <button
                        @click="viewEmployeeCard(employee)"
                        class="text-primary-600 hover:text-primary-900"
                        title="View NFC Card"
                      >
                        <Icon name="heroicons:credit-card" class="h-5 w-5" />
                      </button>
                      <!-- Edit Button -->
                      <button
                        @click="editEmployee(employee)"
                        class="text-secondary-600 hover:text-secondary-900"
                        title="Edit"
                      >
                        <Icon name="heroicons:pencil" class="h-5 w-5" />
                      </button>
                      <!-- Toggle Status Button -->
                      <button
                        @click="toggleEmployeeStatus(employee)"
                        :class="[
                          employee.is_active
                            ? 'text-warning-600 hover:text-warning-900'
                            : 'text-success-600 hover:text-success-900',
                        ]"
                        :title="employee.is_active ? 'Disable' : 'Enable'"
                      >
                        <Icon
                          :name="
                            employee.is_active
                              ? 'heroicons:pause-circle'
                              : 'heroicons:play-circle'
                          "
                          class="h-5 w-5"
                        />
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

    <!-- Add Employee Modal -->
    <Transition name="modal">
      <div
        v-if="showAddEmployeeModal"
        class="fixed inset-0 z-50 overflow-y-auto"
      >
        <div class="flex items-center justify-center min-h-screen px-4">
          <div
            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
            @click="closeAddEmployeeModal"
          ></div>
          <div
            class="bg-white rounded-lg max-w-md w-full p-6 relative z-10 shadow-xl"
          >
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-secondary-900">
                Add New Employee
              </h3>
              <button
                @click="closeAddEmployeeModal"
                class="text-secondary-400 hover:text-secondary-600"
              >
                <Icon name="heroicons:x-mark" class="h-6 w-6" />
              </button>
            </div>

            <form @submit.prevent="submitAddEmployee" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1"
                  >Employee Name</label
                >
                <input
                  v-model="addForm.name"
                  type="text"
                  class="input w-full"
                  placeholder="John Doe"
                  required
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1"
                  >Company Email</label
                >
                <input
                  v-model="addForm.email"
                  type="email"
                  class="input w-full"
                  placeholder="john.doe@company.com"
                  required
                />
                <p class="text-xs text-secondary-500 mt-1">
                  Employee will receive login credentials at this email
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1"
                  >Position (Optional)</label
                >
                <input
                  v-model="addForm.position"
                  type="text"
                  class="input w-full"
                  placeholder="Sales Manager"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1"
                  >Phone Number (Optional)</label
                >
                <input
                  v-model="addForm.phone"
                  type="tel"
                  class="input w-full"
                  placeholder="+60 12-345 6789"
                />
              </div>

              <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <div class="flex">
                  <Icon
                    name="heroicons:information-circle"
                    class="h-5 w-5 text-blue-600 flex-shrink-0"
                  />
                  <div class="ml-3">
                    <p class="text-sm text-blue-700">
                      <strong>Auto-Generated Credentials:</strong>
                    </p>
                    <ul
                      class="text-sm text-blue-600 mt-1 list-disc list-inside"
                    >
                      <li>Secure password will be auto-generated</li>
                      <li>Default plan: Premium</li>
                      <li>Credentials sent via email</li>
                    </ul>
                  </div>
                </div>
              </div>

              <div class="flex items-center justify-end space-x-3 pt-4">
                <button
                  type="button"
                  @click="closeAddEmployeeModal"
                  class="btn btn-outline"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="btn btn-primary"
                  :disabled="addLoading"
                >
                  <div v-if="addLoading" class="spinner mr-2"></div>
                  {{ addLoading ? "Creating..." : "Create Employee" }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Edit Employee Modal -->
    <Transition name="modal">
      <div
        v-if="showEditEmployeeModal"
        class="fixed inset-0 z-50 overflow-y-auto"
      >
        <div class="flex items-center justify-center min-h-screen px-4">
          <div
            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
            @click="closeEditEmployeeModal"
          ></div>
          <div
            class="bg-white rounded-lg max-w-md w-full p-6 relative z-10 shadow-xl"
          >
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-secondary-900">
                Edit Employee
              </h3>
              <button
                @click="closeEditEmployeeModal"
                class="text-secondary-400 hover:text-secondary-600"
              >
                <Icon name="heroicons:x-mark" class="h-6 w-6" />
              </button>
            </div>

            <form @submit.prevent="submitEditEmployee" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1"
                  >Employee Name</label
                >
                <input
                  v-model="editForm.name"
                  type="text"
                  class="input w-full"
                  required
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1"
                  >Email</label
                >
                <input
                  v-model="editForm.email"
                  type="email"
                  class="input w-full"
                  required
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1"
                  >Position</label
                >
                <input
                  v-model="editForm.position"
                  type="text"
                  class="input w-full"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1"
                  >Phone Number</label
                >
                <input
                  v-model="editForm.phone"
                  type="tel"
                  class="input w-full"
                />
              </div>

              <div class="flex items-center justify-end space-x-3 pt-4">
                <button
                  type="button"
                  @click="closeEditEmployeeModal"
                  class="btn btn-outline"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="btn btn-primary"
                  :disabled="editLoading"
                >
                  <div v-if="editLoading" class="spinner mr-2"></div>
                  {{ editLoading ? "Saving..." : "Save Changes" }}
                </button>
              </div>
            </form>
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
const showAddEmployeeModal = ref(false);
const showEditEmployeeModal = ref(false);
const addLoading = ref(false);
const editLoading = ref(false);

// Forms
const addForm = ref({
  name: "",
  email: "",
  position: "",
  phone: "",
});

const editForm = ref({
  id: null,
  name: "",
  email: "",
  position: "",
  phone: "",
});

// Company account limits
const totalSlots = ref(10); // Default: company purchased 10 account slots
const usedSlots = computed(() => employees.value.length);
const availableSlots = computed(() =>
  Math.max(0, totalSlots.value - usedSlots.value)
);
const canAddMoreEmployees = computed(() => availableSlots.value > 0);

// NFC Card Quota
// Card quota is based on ACTUAL ORDERS, not account creation
// Only when an account (admin or employee) orders a Business Plan card, quota is deducted
const totalCardQuota = ref(10); // Default: admin assigns total cards this business can order
const orderedCardsCount = ref(0); // Actual number of Business Plan cards ordered
const usedCardQuota = computed(() => {
  // Cards that have been ordered (not just account count)
  return orderedCardsCount.value;
});
const availableCardQuota = computed(() =>
  Math.max(0, totalCardQuota.value - usedCardQuota.value)
);
const canOrderMoreCards = computed(() => availableCardQuota.value > 0);

// Computed
const activeEmployeesCount = computed(() => {
  return employees.value.filter((emp) => emp.is_active).length;
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
    filtered = filtered.filter((emp) => emp.is_active === isActive);
  }

  return filtered;
});

// Methods
const loadEmployees = async () => {
  loading.value = true;
  try {
    const response = await $api.get("/business/employees");

    if (response.success) {
      employees.value = response.employees || [];
      totalSlots.value = response.total_slots || 10;
      totalCardQuota.value = response.total_card_quota || 10; // Total card quota from backend
      orderedCardsCount.value = response.ordered_cards_count || 0; // Actual ordered cards
    }
  } catch (error) {
    console.error("Failed to load employees:", error);
    $toast.error("Failed to load employees");
  } finally {
    loading.value = false;
  }
};

const submitAddEmployee = async () => {
  if (!canAddMoreEmployees.value) {
    $toast.error("You have reached the maximum number of employee accounts");
    return;
  }

  addLoading.value = true;
  try {
    const response = await $api.post("/business/employees", {
      name: addForm.value.name,
      email: addForm.value.email,
      position: addForm.value.position,
      phone: addForm.value.phone,
      subscription_plan: "premium", // Auto-assign Premium plan
    });

    if (response.success) {
      $toast.success(
        `Employee created successfully! Password sent to ${addForm.value.email}`
      );
      closeAddEmployeeModal();
      await loadEmployees();
    }
  } catch (error) {
    console.error("Failed to create employee:", error);
    if (error.data?.message) {
      $toast.error(error.data.message);
    } else {
      $toast.error("Failed to create employee account");
    }
  } finally {
    addLoading.value = false;
  }
};

const editEmployee = (employee) => {
  editForm.value = {
    id: employee.id,
    name: employee.name,
    email: employee.email,
    position: employee.position || "",
    phone: employee.phone || "",
  };
  showEditEmployeeModal.value = true;
};

const submitEditEmployee = async () => {
  editLoading.value = true;
  try {
    const response = await $api.put(
      `/business/employees/${editForm.value.id}`,
      {
        name: editForm.value.name,
        email: editForm.value.email,
        position: editForm.value.position,
        phone: editForm.value.phone,
      }
    );

    if (response.success) {
      $toast.success("Employee updated successfully!");
      closeEditEmployeeModal();
      await loadEmployees();
    }
  } catch (error) {
    console.error("Failed to update employee:", error);
    if (error.data?.message) {
      $toast.error(error.data.message);
    } else {
      $toast.error("Failed to update employee");
    }
  } finally {
    editLoading.value = false;
  }
};

const toggleEmployeeStatus = async (employee) => {
  const action = employee.is_active ? "disable" : "enable";
  const confirmMessage = `Are you sure you want to ${action} ${employee.name}'s account?`;

  if (!confirm(confirmMessage)) {
    return;
  }

  try {
    const response = await $api.post(
      `/business/employees/${employee.id}/toggle-status`,
      {
        is_active: !employee.is_active,
      }
    );

    if (response.success) {
      $toast.success(`Employee account ${action}d successfully!`);
      await loadEmployees();
    }
  } catch (error) {
    console.error("Failed to toggle employee status:", error);
    $toast.error(`Failed to ${action} employee account`);
  }
};

const viewEmployeeCard = (employee) => {
  // Navigate to employee's NFC card management page
  navigateTo(`/UserDashboard/employees/${employee.id}/card`);
};

const closeAddEmployeeModal = () => {
  showAddEmployeeModal.value = false;
  addForm.value = {
    name: "",
    email: "",
    position: "",
    phone: "",
  };
};

const closeEditEmployeeModal = () => {
  showEditEmployeeModal.value = false;
  editForm.value = {
    id: null,
    name: "",
    email: "",
    position: "",
    phone: "",
  };
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
