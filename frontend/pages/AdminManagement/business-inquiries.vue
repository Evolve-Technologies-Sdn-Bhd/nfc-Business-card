<!-- pages/AdminManagement/business-inquiries.vue -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Growth Pipeline"
      eyebrow-icon="heroicons:briefcase"
      title="Business Plan Enquiries"
      description="Review and triage Business plan requests submitted from the website and plan selection."
    >
      <template #actions>
        <button
          type="button"
          class="admin-btn admin-btn--outline"
          @click="fetchInquiries"
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

    <!-- Pipeline summary -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
      <button
        type="button"
        class="admin-kpi-tile-btn text-left"
        :class="{ 'is-active': !filters.status }"
        :aria-pressed="!filters.status"
        @click="setStatusFilter('')"
        title="Show all enquiries"
      >
        <AdminStatTile
          label="Total"
          :value="statistics.total"
          icon="heroicons:inbox-stack"
        />
      </button>
      <button
        v-for="status in statuses"
        :key="status.value"
        type="button"
        class="admin-kpi-tile-btn text-left"
        :class="{ 'is-active': filters.status === status.value }"
        :aria-pressed="filters.status === status.value"
        :title="`Filter by ${status.label.toLowerCase()}`"
        @click="setStatusFilter(status.value)"
      >
        <AdminStatTile
          :label="status.label"
          :value="statistics.by_status[status.value] || 0"
          :icon="
            {
              new: 'heroicons:sparkles',
              contacted: 'heroicons:phone',
              quoted: 'heroicons:document-text',
              converted: 'heroicons:check-badge',
              closed: 'heroicons:archive-box',
            }[status.value]
          "
        />
      </button>
    </div>

    <AdminFilterBar @submit="applyFilters">
      <template #search>
        <label class="admin-label">Search</label>
        <div class="relative">
          <Icon
            name="heroicons:magnifying-glass"
            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-secondary-400"
          />
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search by company, contact or email…"
            class="admin-field !pl-9"
            maxlength="150"
            @input="debounceSearch"
          />
        </div>
      </template>

      <div class="w-full sm:w-auto sm:min-w-[12rem]">
        <label class="admin-label">Status</label>
        <select
          v-model="filters.status"
          class="admin-field"
          @change="applyFilters"
        >
          <option value="">All statuses</option>
          <option
            v-for="status in statuses"
            :key="status.value"
            :value="status.value"
          >
            {{ status.label }}
          </option>
        </select>
      </div>

      <template #actions>
        <span class="w-full text-right text-body-sm text-secondary-500 sm:w-auto">
          {{ pagination.total }} found
        </span>
        <button
          type="button"
          class="admin-btn admin-btn--outline admin-btn--sm"
          :disabled="!hasActiveFilters"
          @click="resetFilters"
        >
          <Icon name="heroicons:x-mark" class="h-4 w-4" />
          Clear
        </button>
      </template>
    </AdminFilterBar>

    <AdminSectionCard flush>
      <template #title>Enquiry inbox</template>
      <template #header-actions>
        <span class="text-caption text-secondary-500">
          {{ pagination.total }} record{{ pagination.total === 1 ? "" : "s" }}
        </span>
      </template>

      <div
        v-if="error && inquiries.length"
        class="m-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-error-200 bg-error-50 px-4 py-3"
        role="alert"
      >
        <p class="text-body-sm text-error-800">{{ error }}</p>
        <button
          type="button"
          class="admin-btn admin-btn--outline admin-btn--sm"
          :disabled="loading"
          @click="fetchInquiries()"
        >
          Retry
        </button>
      </div>

      <AdminEmptyState
        v-if="error && !inquiries.length"
        icon="heroicons:exclamation-triangle"
        title="Couldn't load business enquiries"
        :description="error"
      >
        <template #actions>
          <button
            type="button"
            class="admin-btn admin-btn--primary"
            :disabled="loading"
            @click="fetchInquiries()"
          >
            <Icon
              name="heroicons:arrow-path"
              class="h-4 w-4"
              :class="{ 'animate-spin': loading }"
            />
            Retry
          </button>
        </template>
      </AdminEmptyState>

      <!-- Loading -->
      <div
        v-else-if="loading && !inquiries.length"
        class="divide-y divide-secondary-100"
        role="status"
        aria-busy="true"
      >
        <div
          v-for="i in 6"
          :key="i"
          class="flex items-center gap-4 px-5 py-4"
        >
          <span class="admin-skeleton h-4 w-32"></span>
          <span class="admin-skeleton h-4 w-1/4"></span>
          <span class="admin-skeleton ml-auto h-4 w-20"></span>
          <span class="admin-skeleton h-4 w-16"></span>
        </div>
      </div>

      <!-- Empty -->
      <AdminEmptyState
        v-else-if="!inquiries.length"
        icon="heroicons:inbox"
        title="No business enquiries found"
        description="New Business plan requests submitted from the website will appear here for triage. Adjust or clear your filters to see more."
      />

      <!-- Table -->
      <div v-else class="admin-scroll">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Enquiry</th>
              <th>Registered user</th>
              <th>Cards requested</th>
              <th>Status</th>
              <th>Submitted</th>
              <th class="admin-col-actions">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in inquiries" :key="item.id">
              <td>
                <div class="text-body font-semibold text-secondary-900">
                  {{ item.company_name }}
                </div>
                <div class="text-caption text-secondary-700">
                  {{ item.contact_person }}
                </div>
                <div class="text-caption text-secondary-500">
                  {{ item.email }}
                </div>
                <div class="mt-1 text-micro capitalize text-secondary-400">
                  {{ (item.source || "").replaceAll("_", " ") || "Website" }}
                </div>
              </td>
              <td>
                <NuxtLink
                  v-if="item.user"
                  :to="{
                    path: '/AdminManagement/users',
                    query: { open: item.user.id },
                  }"
                  class="text-body-sm font-medium text-accent-700 hover:text-accent-900 hover:underline"
                >
                  {{ [item.user.first_name, item.user.last_name].filter(Boolean).join(" ") || item.user.email }}
                </NuxtLink>
                <span v-else class="text-caption text-secondary-400">
                  No linked account
                </span>
                <div
                  v-if="item.user?.email"
                  class="text-caption text-secondary-500"
                >
                  {{ item.user.email }}
                </div>
              </td>
              <td class="text-secondary-600">
                {{ item.quota ?? "—" }}
              </td>
              <td>
                <AdminStatusBadge
                  :tone="
                    {
                      new: 'info',
                      contacted: 'warning',
                      quoted: 'brass',
                      converted: 'success',
                      closed: 'neutral',
                    }[item.status] || 'neutral'
                  "
                  :label="statusLabel(item.status)"
                />
              </td>
              <td class="text-secondary-500">
                {{ formatDate(item.created_at) }}
              </td>
              <td class="admin-col-actions">
                <button
                  type="button"
                  class="admin-btn admin-btn--outline admin-btn--sm"
                  @click="openDetails(item)"
                >
                  <Icon name="heroicons:eye" class="h-4 w-4" />
                  View
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </AdminSectionCard>

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

    <!-- Details dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="showDetailsModal"
          class="admin-dialog-backdrop"
          @click.self="closeDetails"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="Enquiry details"
            style="width: min(42rem, 100%); margin-top: 5vh; max-height: 90vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
                >
                  <Icon name="heroicons:inbox-arrow-down" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">Enquiry details</p>
                  <p class="admin-panel-subtitle">
                    {{ selected?.company_name }}
                  </p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="closeDetails"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <form class="flex min-h-0 flex-1 flex-col" @submit.prevent="saveEnquiry">
              <div class="admin-panel-body flex-1 space-y-6 overflow-y-auto">
                <div v-if="selected">
                  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                      <p class="text-caption font-semibold uppercase tracking-wide text-secondary-500">
                        Company
                      </p>
                      <p class="mt-0.5 text-body text-secondary-900">
                        {{ selected.company_name }}
                      </p>
                    </div>
                    <div>
                      <p class="text-caption font-semibold uppercase tracking-wide text-secondary-500">
                        Company address
                      </p>
                      <p class="mt-0.5 text-body text-secondary-900">
                        {{ selected.company_address || "—" }}
                      </p>
                    </div>
                    <div>
                      <p class="text-caption font-semibold uppercase tracking-wide text-secondary-500">
                        Contact person
                      </p>
                      <p class="mt-0.5 text-body text-secondary-900">
                        {{ selected.contact_person }}
                      </p>
                    </div>
                    <div>
                      <p class="text-caption font-semibold uppercase tracking-wide text-secondary-500">
                        NFC cards needed
                      </p>
                      <p class="mt-0.5 text-body text-secondary-900">
                        {{ selected.quota ?? "—" }}
                      </p>
                    </div>
                    <div>
                      <p class="text-caption font-semibold uppercase tracking-wide text-secondary-500">
                        Email
                      </p>
                      <p class="mt-0.5 text-body text-secondary-900">
                        {{ selected.email }}
                      </p>
                    </div>
                    <div>
                      <p class="text-caption font-semibold uppercase tracking-wide text-secondary-500">
                        Phone
                      </p>
                      <p class="mt-0.5 text-body text-secondary-900">
                        {{ selected.phone || "—" }}
                      </p>
                    </div>
                    <div v-if="selected.user" class="sm:col-span-2">
                      <p class="text-caption font-semibold uppercase tracking-wide text-secondary-500">
                        Registered user
                      </p>
                      <NuxtLink
                        :to="{
                          path: '/AdminManagement/users',
                          query: { open: selected.user.id },
                        }"
                        class="mt-0.5 inline-flex flex-wrap items-center gap-1 text-body-sm font-medium text-accent-700 hover:text-accent-900 hover:underline"
                      >
                        {{ [selected.user.first_name, selected.user.last_name].filter(Boolean).join(" ") || selected.user.email }}
                        <span v-if="selected.user.email" class="text-secondary-500">
                          ({{ selected.user.email }})
                        </span>
                        <Icon name="heroicons:arrow-top-right-on-square" class="h-3.5 w-3.5" />
                      </NuxtLink>
                    </div>
                  </div>

                  <div v-if="selected.notes" class="mt-6">
                    <p class="text-caption font-semibold uppercase tracking-wide text-secondary-500">
                      Notes
                    </p>
                    <p class="mt-0.5 whitespace-pre-line text-body text-secondary-900">
                      {{ selected.notes }}
                    </p>
                  </div>
                </div>

                <!-- Triage -->
                <div class="admin-form-section">
                  <p class="admin-form-section-title">Triage</p>
                  <p class="admin-form-section-desc">
                    Update the enquiry status and record internal notes.
                  </p>
                  <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                      <label class="admin-label">Status</label>
                      <select v-model="editForm.status" class="admin-field">
                        <option
                          v-for="status in statuses"
                          :key="status.value"
                          :value="status.value"
                        >
                          {{ status.label }}
                        </option>
                      </select>
                    </div>
                    <div class="md:col-span-2">
                      <label class="admin-label">Admin notes</label>
                      <textarea
                        v-model="editForm.admin_notes"
                        rows="3"
                        class="admin-field"
                        maxlength="5000"
                        placeholder="Internal notes about this enquiry…"
                      ></textarea>
                    </div>
                  </div>
                </div>
              </div>

              <div class="admin-dialog-foot">
                <button
                  type="button"
                  class="admin-btn admin-btn--ghost"
                  @click="closeDetails"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="saving"
                  class="admin-btn admin-btn--primary"
                >
                  <Icon
                    v-if="saving"
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
import { ref, computed, onMounted } from "vue";

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
const error = ref("");
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

const hasActiveFilters = computed(
  () => Boolean(filters.value.search.trim()) || Boolean(filters.value.status)
);

const selected = ref(null);
const showDetailsModal = ref(false);
const editForm = ref({ status: "new", admin_notes: "" });

let searchTimeout = null;
let latestRequestId = 0;

const statusLabel = (value) =>
  statuses.find((s) => s.value === value)?.label || value;

const formatDate = (value) => {
  if (!value) return "—";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return "—";
  return date.toLocaleString();
};

const fetchInquiries = async (page = pagination.value.current_page || 1) => {
  const requestId = ++latestRequestId;
  loading.value = true;
  error.value = "";
  try {
    const response = await $api.get("/admin/business-inquiries", {
      params: {
        page,
        per_page: pagination.value.per_page,
        search: filters.value.search || undefined,
        status: filters.value.status || undefined,
      },
    });

    if (!response?.success || !response.data) {
      throw new Error(response?.message || "Failed to load business enquiries.");
    }

    if (requestId !== latestRequestId) return;

    inquiries.value = response.data.data || [];
    pagination.value = {
      current_page: response.data.current_page || 1,
      last_page: response.data.last_page || 1,
      per_page: response.data.per_page || pagination.value.per_page,
      total: response.data.total || 0,
    };
  } catch (requestError) {
    console.error("Failed to load business enquiries:", requestError);
    if (requestId === latestRequestId) {
      error.value =
        requestError.response?.data?.message ||
        requestError.message ||
        "Failed to load business enquiries.";
    }
  } finally {
    if (requestId === latestRequestId) {
      loading.value = false;
    }
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
    applyFilters();
  }, 400);
};

const applyFilters = () => {
  clearTimeout(searchTimeout);
  pagination.value.current_page = 1;
  fetchInquiries(1);
};

const setStatusFilter = (status) => {
  clearTimeout(searchTimeout);
  // Toggle off if the same status is clicked again
  filters.value.status = filters.value.status === status ? "" : status;
  pagination.value.current_page = 1;
  fetchInquiries(1);
};

const resetFilters = () => {
  clearTimeout(searchTimeout);
  filters.value = { search: "", status: "" };
  pagination.value.current_page = 1;
  fetchInquiries(1);
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    pagination.value.current_page = page;
    fetchInquiries(page);
  }
};

const changeItemsPerPage = (perPage) => {
  pagination.value.per_page = Number(perPage) || 20;
  pagination.value.current_page = 1;
  fetchInquiries(1);
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
