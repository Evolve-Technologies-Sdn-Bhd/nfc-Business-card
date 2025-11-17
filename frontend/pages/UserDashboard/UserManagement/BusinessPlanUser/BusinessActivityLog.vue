<template>
  <div>
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-secondary-900">
        Employee Activity Log
      </h1>
      <p class="mt-2 text-secondary-600">
        Monitor all actions performed by your employees
      </p>
    </div>

    <!-- Statistics Dashboard -->
    <div
      v-if="statistics"
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
    >
      <div class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="p-3 rounded-lg bg-primary-500">
              <span class="text-3xl">📊</span>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-secondary-600">
              Total Activities
            </p>
            <p class="text-2xl font-semibold text-secondary-900">
              {{ statistics.total_logs }}
            </p>
          </div>
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="p-3 rounded-lg bg-success-500">
              <span class="text-3xl">📈</span>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-secondary-600">
              Activities Today
            </p>
            <p class="text-2xl font-semibold text-secondary-900">
              {{ statistics.today_count }}
            </p>
          </div>
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="p-3 rounded-lg bg-info-500">
              <span class="text-3xl">👤</span>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-secondary-600">
              Most Active Employee
            </p>
            <p class="text-lg font-semibold text-secondary-900">
              {{ statistics.most_active_employee?.name || "N/A" }}
            </p>
          </div>
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="p-3 rounded-lg bg-warning-500">
              <span class="text-3xl">🔥</span>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-secondary-600">
              Most Common Action
            </p>
            <p class="text-lg font-semibold text-secondary-900">
              {{ statistics.most_common_action?.type || "N/A" }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters Section -->
    <div class="card mb-6">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Employee</label
            >
            <select
              v-model="filters.employee_id"
              @change="fetchActivityLogs"
              class="input"
            >
              <option value="">All Employees</option>
              <option
                v-for="employee in employees"
                :key="employee.id"
                :value="employee.id"
              >
                {{ employee.name }} ({{ employee.email }})
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Action Type</label
            >
            <select
              v-model="filters.action_type"
              @change="fetchActivityLogs"
              class="input"
            >
              <option value="">All Actions</option>
              <option
                v-for="actionType in actionTypes"
                :key="actionType"
                :value="actionType"
              >
                {{ formatActionType(actionType) }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Start Date</label
            >
            <input
              type="date"
              v-model="filters.start_date"
              @change="fetchActivityLogs"
              class="input"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >End Date</label
            >
            <input
              type="date"
              v-model="filters.end_date"
              @change="fetchActivityLogs"
              class="input"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Search</label
            >
            <input
              type="text"
              v-model="filters.search"
              @input="debounceSearch"
              placeholder="Search descriptions..."
              class="input"
            />
          </div>
        </div>
        <div class="mt-4 flex justify-end space-x-3">
          <button @click="resetFilters" class="btn btn-outline btn-sm">
            Clear Filters
          </button>
          <button @click="exportLogs" class="btn btn-primary btn-sm">
            <Icon name="heroicons:arrow-down-tray" class="h-4 w-4 mr-2" />
            Export CSV
          </button>
        </div>
      </div>
    </div>

    <!-- Activity Log Table -->
    <div class="card">
      <div class="card-body p-0">
        <div v-if="activityLogs.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-secondary-200">
            <thead class="bg-secondary-50">
              <tr>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  Date & Time
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  Employee
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  Action
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  Description
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  IP Address
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  Details
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-secondary-200">
              <tr
                v-for="log in activityLogs"
                :key="log.id"
                class="hover:bg-secondary-50"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-secondary-900">
                    {{ formatDate(log.created_at) }}
                  </div>
                  <div class="text-xs text-secondary-500">
                    {{ formatTime(log.created_at) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-secondary-900">
                    {{ log.user?.name }}
                  </div>
                  <div class="text-xs text-secondary-500">
                    {{ log.user?.email }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'px-2 py-1 text-xs font-medium rounded-full',
                      getActionClass(log.action_type),
                    ]"
                  >
                    {{ formatActionType(log.action_type) }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-secondary-900 max-w-xs truncate">
                    {{ log.action_description }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-secondary-900">
                    {{ log.ip_address }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button
                    @click="viewDetails(log)"
                    class="text-primary-600 hover:text-primary-900"
                  >
                    View
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="text-center py-12 text-secondary-500">
          <div class="text-6xl mb-4 opacity-50">📋</div>
          <h3 class="text-lg font-medium text-secondary-900 mb-2">
            No Activity Logs Found
          </h3>
          <p class="text-sm">
            There are no employee activities matching your filters.
          </p>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div
      v-if="pagination.total > 0"
      class="mt-6 flex items-center justify-between"
    >
      <div class="text-sm text-secondary-500">
        Showing {{ (pagination.current_page - 1) * pagination.per_page + 1 }} to
        {{
          Math.min(
            pagination.current_page * pagination.per_page,
            pagination.total
          )
        }}
        of {{ pagination.total }} results
      </div>
      <div class="flex space-x-2">
        <button
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="btn btn-outline btn-sm"
        >
          Previous
        </button>
        <button
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="btn btn-outline btn-sm"
        >
          Next
        </button>
      </div>
    </div>

    <!-- Details Modal -->
    <div
      v-if="showDetailsModal"
      class="fixed inset-0 bg-secondary-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
      @click="closeDetailsModal"
    >
      <div class="card max-w-4xl w-full" @click.stop>
        <div class="card-header">
          <h2 class="text-xl font-bold text-secondary-900">Activity Details</h2>
          <button
            @click="closeDetailsModal"
            class="text-secondary-400 hover:text-secondary-600"
          >
            <Icon name="heroicons:x-mark" class="h-6 w-6" />
          </button>
        </div>

        <div v-if="selectedLog" class="card-body space-y-6">
          <div class="bg-secondary-50 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-secondary-900 mb-4">
              Basic Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-xs font-medium text-secondary-500 mb-1">
                  Employee
                </p>
                <p class="text-sm text-secondary-900">
                  {{ selectedLog.user?.name }} ({{ selectedLog.user?.email }})
                </p>
              </div>
              <div>
                <p class="text-xs font-medium text-secondary-500 mb-1">
                  Action Type
                </p>
                <span
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    getActionClass(selectedLog.action_type),
                  ]"
                >
                  {{ formatActionType(selectedLog.action_type) }}
                </span>
              </div>
              <div>
                <p class="text-xs font-medium text-secondary-500 mb-1">
                  Date & Time
                </p>
                <p class="text-sm text-secondary-900">
                  {{ formatFullDate(selectedLog.created_at) }}
                </p>
              </div>
              <div>
                <p class="text-xs font-medium text-secondary-500 mb-1">
                  IP Address
                </p>
                <p class="text-sm text-secondary-900">
                  {{ selectedLog.ip_address }}
                </p>
              </div>
              <div class="md:col-span-2">
                <p class="text-xs font-medium text-secondary-500 mb-1">
                  User Agent
                </p>
                <p class="text-xs text-secondary-900 font-mono break-all">
                  {{ selectedLog.user_agent }}
                </p>
              </div>
            </div>
          </div>

          <div class="bg-secondary-50 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-secondary-900 mb-4">
              Description
            </h3>
            <p class="text-sm text-secondary-900">
              {{ selectedLog.action_description }}
            </p>
          </div>

          <div
            v-if="selectedLog.entity_type"
            class="bg-secondary-50 rounded-lg p-4"
          >
            <h3 class="text-lg font-semibold text-secondary-900 mb-4">
              Entity Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-xs font-medium text-secondary-500 mb-1">
                  Entity Type
                </p>
                <p class="text-sm text-secondary-900">
                  {{ selectedLog.entity_type }}
                </p>
              </div>
              <div>
                <p class="text-xs font-medium text-secondary-500 mb-1">
                  Entity ID
                </p>
                <p class="text-sm text-secondary-900">
                  {{ selectedLog.entity_id }}
                </p>
              </div>
            </div>
          </div>

          <div
            v-if="selectedLog.change_summary"
            class="bg-secondary-50 rounded-lg p-4"
          >
            <h3 class="text-lg font-semibold text-secondary-900 mb-4">
              Changes Made
            </h3>
            <div
              class="text-sm text-secondary-900"
              v-html="selectedLog.change_summary"
            ></div>
          </div>

          <div
            v-if="selectedLog.metadata"
            class="bg-secondary-50 rounded-lg p-4"
          >
            <h3 class="text-lg font-semibold text-secondary-900 mb-4">
              Additional Metadata
            </h3>
            <pre
              class="text-xs font-mono text-secondary-900 bg-white rounded p-3 overflow-x-auto"
              >{{ JSON.stringify(selectedLog.metadata, null, 2) }}</pre
            >
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

// Middleware to check Business Plan access
definePageMeta({
  middleware: ["auth"],
  layout: "user-dashboard",
});

const config = useRuntimeConfig();
const apiBaseUrl = config.public.apiBaseUrl || "http://localhost:8000";
const authStore = useAuthStore();
const { $api } = useNuxtApp();

// Check if user is Business Plan
const checkBusinessAccess = () => {
  const user = authStore.user;
  if (!user || user.subscription_plan !== "business") {
    navigateTo("/UserDashboard");
    return false;
  }
  // Check if user is Business Admin (not employee)
  if (user.parent_business_id) {
    navigateTo("/UserDashboard");
    return false;
  }
  return true;
};

// State
const activityLogs = ref([]);
const employees = ref([]);
const actionTypes = ref([]);
const statistics = ref(null);
const selectedLog = ref(null);
const showDetailsModal = ref(false);

// Filters
const filters = ref({
  employee_id: "",
  action_type: "",
  start_date: "",
  end_date: "",
  search: "",
});

// Pagination
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
});

// Debounce timer
let searchTimeout = null;

// Fetch activity logs
const fetchActivityLogs = async (page = 1) => {
  try {
    const queryParams = new URLSearchParams({
      page: page,
      per_page: pagination.value.per_page,
      ...Object.fromEntries(
        Object.entries(filters.value).filter(([_, v]) => v !== "")
      ),
    });

    const response = await $api.get(`/business/activity-logs?${queryParams}`);

    if (response.success) {
      activityLogs.value = response.data;
      pagination.value = {
        current_page: response.current_page,
        last_page: response.last_page,
        per_page: response.per_page,
        total: response.total,
      };
    }
  } catch (error) {
    console.error("Error fetching activity logs:", error);
    alert(
      `Failed to load activity logs: ${error.message || "Please try again."}`
    );
  }
};

// Fetch employees
const fetchEmployees = async () => {
  try {
    const response = await $api.get("/business/employees");
    if (response.success) {
      employees.value = response.employees || [];
    }
  } catch (error) {
    console.error("Error fetching employees:", error);
  }
};

// Fetch action types
const fetchActionTypes = async () => {
  try {
    const response = await $api.get("/business/activity-logs/action-types");
    if (response.success) {
      actionTypes.value = response.action_types || [];
    }
  } catch (error) {
    console.error("Error fetching action types:", error);
  }
};

// Fetch statistics
const fetchStatistics = async () => {
  try {
    const response = await $api.get("/business/activity-logs/statistics");
    if (response.success) {
      statistics.value = response;
    }
  } catch (error) {
    console.error("Error fetching statistics:", error);
  }
};

// Export logs
const exportLogs = async () => {
  try {
    const queryParams = new URLSearchParams(
      Object.fromEntries(
        Object.entries(filters.value).filter(([_, v]) => v !== "")
      )
    );

    // Use $api to get the token automatically
    const token = localStorage.getItem("token");
    window.open(
      `${apiBaseUrl}/business/activity-logs/export?${queryParams}&token=${token}`,
      "_blank"
    );
  } catch (error) {
    console.error("Error exporting logs:", error);
    alert("Failed to export logs. Please try again.");
  }
};

// View details
const viewDetails = (log) => {
  selectedLog.value = log;
  showDetailsModal.value = true;
};

// Close details modal
const closeDetailsModal = () => {
  showDetailsModal.value = false;
  selectedLog.value = null;
};

// Reset filters
const resetFilters = () => {
  filters.value = {
    employee_id: "",
    action_type: "",
    start_date: "",
    end_date: "",
    search: "",
  };
  fetchActivityLogs();
};

// Debounce search
const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchActivityLogs();
  }, 500);
};

// Change page
const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchActivityLogs(page);
  }
};

// Format action type
const formatActionType = (actionType) => {
  return actionType
    .split("_")
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(" ");
};

// Get action class
const getActionClass = (actionType) => {
  const classes = {
    login: "bg-blue-100 text-blue-800",
    logout: "bg-red-100 text-red-800",
    profile_updated: "bg-orange-100 text-orange-800",
    password_changed: "bg-purple-100 text-purple-800",
    landing_page_updated: "bg-orange-100 text-orange-800",
    link_created: "bg-green-100 text-green-800",
    link_updated: "bg-orange-100 text-orange-800",
    link_deleted: "bg-red-100 text-red-800",
    nfc_card_activated: "bg-green-100 text-green-800",
    nfc_card_deactivated: "bg-yellow-100 text-yellow-800",
    image_uploaded: "bg-green-100 text-green-800",
    settings_changed: "bg-orange-100 text-orange-800",
  };
  return classes[actionType] || "bg-secondary-100 text-secondary-800";
};

// Format date
const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString("en-MY", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

// Format time
const formatTime = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleTimeString("en-MY", {
    hour: "2-digit",
    minute: "2-digit",
  });
};

// Format full date
const formatFullDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleString("en-MY", {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
  });
};

// Initialize
onMounted(() => {
  // Check Business Plan access
  if (!checkBusinessAccess()) {
    return;
  }

  fetchActivityLogs();
  fetchEmployees();
  fetchActionTypes();
  fetchStatistics();
});
</script>
