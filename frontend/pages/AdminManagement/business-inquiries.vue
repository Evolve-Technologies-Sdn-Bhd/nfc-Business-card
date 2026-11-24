<template>
  <div>
    <!-- Header -->
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="page-title text-secondary-900">
            Business Plan Enquiries
          </h1>
          <p class="mt-2 text-secondary-600">
            Review and triage Business plan requests submitted from the website
            and plan selection.
          </p>
        </div>
        <div class="mt-4 sm:mt-0">
          <button @click="fetchInquiries" class="btn btn-secondary">
            <Icon
              name="heroicons:arrow-path"
              class="h-5 w-5 mr-2"
              :class="{ 'animate-spin': loading }"
            />
            Refresh
          </button>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
      <div class="card p-4">
        <p class="stat-label text-secondary-600">Total</p>
        <p class="stat-value text-secondary-900">
          {{ statistics.total }}
        </p>
      </div>
      <div
        v-for="status in statuses"
        :key="status.value"
        class="card p-4"
      >
        <p class="stat-label text-secondary-600">{{ status.label }}</p>
        <p class="stat-value text-secondary-900">
          {{ statistics.by_status[status.value] || 0 }}
        </p>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="md:col-span-2">
            <label class="block text-label text-secondary-700 mb-2"
              >Search</label
            >
            <input
              type="text"
              v-model="filters.search"
              @input="debounceSearch"
              placeholder="Search by company, contact or email..."
              class="input"
              maxlength="150"
            />
          </div>
          <div>
            <label class="block text-label text-secondary-700 mb-2"
              >Status</label
            >
            <select
              v-model="filters.status"
              @change="fetchInquiries"
              class="input"
            >
              <option value="">All Statuses</option>
              <option
                v-for="status in statuses"
                :key="status.value"
                :value="status.value"
              >
                {{ status.label }}
              </option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="py-12 text-center text-secondary-500">
          <Icon
            name="heroicons:arrow-path"
            class="h-6 w-6 mx-auto animate-spin"
          />
          <p class="mt-2 text-body">Loading enquiries...</p>
        </div>

        <div
          v-else-if="inquiries.length === 0"
          class="py-12 text-center text-secondary-500"
        >
          <Icon
            name="heroicons:inbox"
            class="h-10 w-10 mx-auto text-secondary-300"
          />
          <p class="mt-2 text-body">No business enquiries found.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-secondary-200">
            <thead>
              <tr>
                <th class="px-4 py-3 text-left table-head">
                  Company
                </th>
                <th class="px-4 py-3 text-left table-head">
                  Contact
                </th>
                <th class="px-4 py-3 text-left table-head">
                  Quota
                </th>
                <th class="px-4 py-3 text-left table-head">
                  Source
                </th>
                <th class="px-4 py-3 text-left table-head">
                  Status
                </th>
                <th class="px-4 py-3 text-left table-head">
                  Submitted
                </th>
                <th class="px-4 py-3 text-right table-head">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-secondary-200">
              <tr v-for="item in inquiries" :key="item.id" class="hover:bg-secondary-50">
                <td class="px-4 py-3 table-cell font-medium text-secondary-900">
                  {{ item.company_name }}
                </td>
                <td class="px-4 py-3 table-cell text-secondary-600">
                  <div>{{ item.contact_person }}</div>
                  <div class="text-caption text-secondary-500">{{ item.email }}</div>
                </td>
                <td class="px-4 py-3 table-cell text-secondary-600">
                  {{ item.quota ?? "—" }}
                </td>
                <td class="px-4 py-3 table-cell text-secondary-600 capitalize">
                  {{ (item.source || "").replace("_", " ") }}
                </td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-badge"
                    :class="statusClass(item.status)"
                  >
                    {{ statusLabel(item.status) }}
                  </span>
                </td>
                <td class="px-4 py-3 table-cell text-secondary-600">
                  {{ formatDate(item.created_at) }}
                </td>
                <td class="px-4 py-3 text-right">
                  <button
                    @click="openDetails(item)"
                    class="text-primary-600 hover:text-primary-700 text-button"
                  >
                    View
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <AdminPagination
      v-if="pagination.total > 0"
      :current-page="pagination.current_page"
      :last-page="pagination.last_page"
      :per-page="pagination.per_page"
      :total="pagination.total"
      item-label="enquiries"
      @page-change="changePage"
      @per-page-change="changeItemsPerPage"
    />

    <!-- Details Modal -->
    <div
      v-if="showDetailsModal"
      class="fixed inset-0 bg-secondary-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
      @click.self="closeDetails"
    >
      <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-secondary-200">
          <h3 class="modal-title text-secondary-900">
            Enquiry Details
          </h3>
          <button
            @click="closeDetails"
            class="text-secondary-400 hover:text-secondary-600"
          >
            <Icon name="heroicons:x-mark" class="h-6 w-6" />
          </button>
        </div>

        <div v-if="selected" class="px-6 py-5 space-y-5 max-h-[70vh] overflow-y-auto">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <p class="text-caption font-medium text-secondary-500 uppercase">Company</p>
              <p class="text-body text-secondary-900">{{ selected.company_name }}</p>
            </div>
            <div>
              <p class="text-caption font-medium text-secondary-500 uppercase">Company Address</p>
              <p class="text-body text-secondary-900">{{ selected.company_address || "—" }}</p>
            </div>
            <div>
              <p class="text-caption font-medium text-secondary-500 uppercase">Contact Person</p>
              <p class="text-body text-secondary-900">{{ selected.contact_person }}</p>
            </div>
            <div>
              <p class="text-caption font-medium text-secondary-500 uppercase">NFC Cards Needed</p>
              <p class="text-body text-secondary-900">{{ selected.quota ?? "—" }}</p>
            </div>
            <div>
              <p class="text-caption font-medium text-secondary-500 uppercase">Email</p>
              <p class="text-body text-secondary-900">{{ selected.email }}</p>
            </div>
            <div>
              <p class="text-caption font-medium text-secondary-500 uppercase">Phone</p>
              <p class="text-body text-secondary-900">{{ selected.phone || "—" }}</p>
            </div>
          </div>

          <div v-if="selected.notes">
            <p class="text-caption font-medium text-secondary-500 uppercase">Notes</p>
            <p class="text-body text-secondary-900 whitespace-pre-line">{{ selected.notes }}</p>
          </div>

          <div class="pt-4 border-t border-secondary-200">
            <label class="block text-label text-secondary-700 mb-2">
              Status
            </label>
            <select v-model="editForm.status" class="input">
              <option
                v-for="status in statuses"
                :key="status.value"
                :value="status.value"
              >
                {{ status.label }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-label text-secondary-700 mb-2">
              Admin Notes
            </label>
            <textarea
              v-model="editForm.admin_notes"
              rows="3"
              class="input"
              maxlength="5000"
              placeholder="Internal notes about this enquiry..."
            ></textarea>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-secondary-200">
          <button @click="closeDetails" class="btn btn-secondary">
            Cancel
          </button>
          <button
            @click="saveEnquiry"
            :disabled="saving"
            class="btn btn-primary"
          >
            <Icon
              v-if="saving"
              name="heroicons:arrow-path"
              class="h-4 w-4 mr-2 animate-spin"
            />
            Save Changes
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

definePageMeta({
  middleware: ["auth", "admin"],
  layout: "admin-management",
});

const { $api, $toast } = useNuxtApp();

const statuses = [
  { value: "new", label: "New" },
  { value: "contacted", label: "Contacted" },
  { value: "quoted", label: "Quoted" },
  { value: "converted", label: "Converted" },
  { value: "closed", label: "Closed" },
];

const loading = ref(false);
const saving = ref(false);
const inquiries = ref([]);
const statistics = ref({ total: 0, by_status: {} });

const filters = ref({ search: "", status: "" });

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
});

const selected = ref(null);
const showDetailsModal = ref(false);
const editForm = ref({ status: "new", admin_notes: "" });

let searchTimeout = null;

const statusLabel = (value) =>
  statuses.find((s) => s.value === value)?.label || value;

const statusClass = (value) => {
  const map = {
    new: "bg-info-100 text-info-800",
    contacted: "bg-warning-100 text-warning-800",
    quoted: "bg-primary-100 text-primary-800",
    converted: "bg-success-100 text-success-800",
    closed: "bg-secondary-100 text-secondary-700",
  };
  return map[value] || "bg-secondary-100 text-secondary-700";
};

const formatDate = (value) => {
  if (!value) return "—";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return "—";
  return date.toLocaleString();
};

const fetchInquiries = async () => {
  loading.value = true;
  try {
    const response = await $api.get("/admin/business-inquiries", {
      params: {
        page: pagination.value.current_page,
        per_page: pagination.value.per_page,
        search: filters.value.search || undefined,
        status: filters.value.status || undefined,
      },
    });

    if (response.success) {
      inquiries.value = response.data.data || [];
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total,
      };
    }
  } catch (error) {
    console.error("Failed to load business enquiries:", error);
    $toast.error("Failed to load business enquiries");
  } finally {
    loading.value = false;
  }
};

const fetchStatistics = async () => {
  try {
    const response = await $api.get("/admin/business-inquiries/statistics");
    if (response.success) {
      statistics.value = response.data;
    }
  } catch (error) {
    console.error("Failed to load business enquiry statistics:", error);
  }
};

const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1;
    fetchInquiries();
  }, 400);
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    pagination.value.current_page = page;
    fetchInquiries();
  }
};

const changeItemsPerPage = (perPage) => {
  pagination.value.per_page = perPage;
  pagination.value.current_page = 1;
  fetchInquiries();
};

const openDetails = (item) => {
  selected.value = item;
  editForm.value = {
    status: item.status || "new",
    admin_notes: item.admin_notes || "",
  };
  showDetailsModal.value = true;
};

const closeDetails = () => {
  showDetailsModal.value = false;
  selected.value = null;
};

const saveEnquiry = async () => {
  if (!selected.value) return;
  saving.value = true;
  try {
    const response = await $api.put(
      `/admin/business-inquiries/${selected.value.id}`,
      {
        status: editForm.value.status,
        admin_notes: editForm.value.admin_notes,
      }
    );

    if (!response.success) {
      throw new Error(response.message || "Failed to update enquiry");
    }

    $toast.success("Enquiry updated");
    closeDetails();
    await Promise.all([fetchInquiries(), fetchStatistics()]);
  } catch (error) {
    console.error("Failed to update enquiry:", error);
    $toast.error(
      error?.response?.data?.message || "Failed to update enquiry"
    );
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchInquiries();
  fetchStatistics();
});
</script>
