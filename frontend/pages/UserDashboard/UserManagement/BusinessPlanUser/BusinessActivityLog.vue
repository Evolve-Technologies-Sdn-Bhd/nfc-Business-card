<!-- pages/UserDashboard/UserManagement/BusinessPlanUser/BusinessActivityLog.vue
     Business owner's read-only audit trail of the team (sign-ins, profile and
     card changes). Access: Business owner only (portal-access middleware and
     ActivityLogController). Built on the shared admin design system so it
     matches the rest of the portal. -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Team"
      eyebrow-icon="heroicons:clipboard-document-list"
      title="Activity Log"
      description="Sign-ins, profile edits and card changes made by people on your Business account. Read-only."
    >
      <template #actions>
        <button type="button" class="admin-btn admin-btn--outline" :disabled="loading" @click="refreshAll">
          <Icon name="heroicons:arrow-path" class="h-4 w-4" :class="{ 'animate-spin': loading }" />
          Refresh
        </button>
        <button
          type="button"
          class="admin-btn admin-btn--primary"
          :disabled="exporting || pagination.total === 0"
          @click="exportLogs"
        >
          <Icon
            :name="exporting ? 'heroicons:arrow-path' : 'heroicons:arrow-down-tray'"
            class="h-4 w-4"
            :class="{ 'animate-spin': exporting }"
          />
          {{ exporting ? "Preparing…" : "Export CSV" }}
        </button>
      </template>
    </AdminPageHeader>

    <!-- Statistics -->
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <AdminStatTile
        label="Total activity"
        :value="formatNumber(statistics?.total_logs)"
        icon="heroicons:document-text"
        hint="All recorded events"
        :loading="statsLoading"
      />
      <AdminStatTile
        label="Today"
        :value="formatNumber(statistics?.today_count)"
        icon="heroicons:clock"
        :hint="`Since midnight, ${formatDate(new Date())}`"
        :loading="statsLoading"
      />
      <AdminStatTile
        label="Most active"
        :value="statistics?.most_active_employee?.name || '—'"
        icon="heroicons:user"
        :hint="statistics?.most_active_employee ? `${statistics.most_active_employee.count} actions in 30 days` : 'No activity in 30 days'"
        :loading="statsLoading"
      />
      <AdminStatTile
        label="Most common action"
        :value="statistics?.most_common_action ? formatActionType(statistics.most_common_action.type) : '—'"
        icon="heroicons:bolt"
        :hint="statistics?.most_common_action ? `${statistics.most_common_action.count} times in 30 days` : 'No activity in 30 days'"
        :loading="statsLoading"
      />
    </div>

    <!-- Filters -->
    <AdminFilterBar @submit="fetchActivityLogs(1)">
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
            placeholder="Search descriptions…"
            maxlength="150"
            class="admin-field !pl-9"
            @input="debounceSearch"
          />
        </div>
      </template>

      <div class="min-w-[12rem]">
        <label class="admin-label">Person</label>
        <select v-model="filters.employee_id" class="admin-field" @change="fetchActivityLogs(1)">
          <option value="">Everyone</option>
          <option v-for="person in people" :key="person.id" :value="person.id">
            {{ person.name }}
          </option>
        </select>
      </div>

      <div class="min-w-[11rem]">
        <label class="admin-label">Action</label>
        <select v-model="filters.action_type" class="admin-field" @change="fetchActivityLogs(1)">
          <option value="">All actions</option>
          <option v-for="type in actionTypes" :key="type" :value="type">
            {{ formatActionType(type) }}
          </option>
        </select>
      </div>

      <div class="min-w-[9.5rem]">
        <label class="admin-label">From</label>
        <input v-model="filters.start_date" type="date" class="admin-field" :max="filters.end_date || undefined" @change="fetchActivityLogs(1)" />
      </div>

      <div class="min-w-[9.5rem]">
        <label class="admin-label">To</label>
        <input v-model="filters.end_date" type="date" class="admin-field" :min="filters.start_date || undefined" @change="fetchActivityLogs(1)" />
      </div>

      <template #actions>
        <button
          v-if="hasActiveFilters"
          type="button"
          class="admin-btn admin-btn--outline admin-btn--sm"
          :disabled="loading"
          @click="resetFilters"
        >
          <Icon name="heroicons:x-mark" class="h-4 w-4" />
          Clear filters
        </button>
      </template>
    </AdminFilterBar>

    <!-- Log table -->
    <AdminSectionCard flush>
      <template #title>Activity</template>
      <template #header-actions>
        <span class="text-caption text-secondary-500">
          {{ formatNumber(pagination.total) }} record{{ pagination.total === 1 ? "" : "s" }}
        </span>
      </template>

      <div v-if="loading" class="divide-y divide-secondary-100" role="status" aria-busy="true">
        <div v-for="i in 6" :key="i" class="flex items-center gap-4 px-5 py-4">
          <span class="admin-skeleton h-4 w-24 shrink-0"></span>
          <span class="admin-skeleton h-9 w-9 shrink-0 rounded-full"></span>
          <span class="admin-skeleton h-4 w-1/4"></span>
          <span class="admin-skeleton ml-auto h-4 w-20"></span>
        </div>
      </div>

      <AdminEmptyState
        v-else-if="loadError"
        icon="heroicons:exclamation-triangle"
        title="Couldn't load the activity log"
        description="Check your connection and try again."
      >
        <template #actions>
          <button type="button" class="admin-btn admin-btn--outline admin-btn--sm" @click="fetchActivityLogs(pagination.current_page)">
            <Icon name="heroicons:arrow-path" class="h-4 w-4" />
            Try again
          </button>
        </template>
      </AdminEmptyState>

      <AdminEmptyState
        v-else-if="!activityLogs.length"
        icon="heroicons:clipboard-document-list"
        :title="hasActiveFilters ? 'Nothing matches these filters' : 'No activity yet'"
        :description="hasActiveFilters ? 'Try a wider date range or clear the filters.' : 'Sign-ins, profile edits and card changes by your team will appear here.'"
      >
        <template v-if="hasActiveFilters" #actions>
          <button type="button" class="admin-btn admin-btn--outline admin-btn--sm" @click="resetFilters">
            <Icon name="heroicons:x-mark" class="h-4 w-4" />
            Clear filters
          </button>
        </template>
      </AdminEmptyState>

      <div v-else class="admin-scroll">
        <table class="admin-table">
          <thead>
            <tr>
              <th>When</th>
              <th>Person</th>
              <th>Action</th>
              <th>Description</th>
              <th class="text-right"><span class="sr-only">Details</span></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="log in activityLogs" :key="log.id">
              <td class="whitespace-nowrap">
                <div class="font-medium text-secondary-900">{{ formatDate(log.created_at) }}</div>
                <div class="text-caption text-secondary-500">{{ formatTime(log.created_at) }}</div>
              </td>
              <td>
                <div class="flex items-center gap-3 min-w-0">
                  <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-accent-100 text-caption font-semibold text-accent-800">
                    {{ initials(log.user?.name) }}
                  </span>
                  <div class="min-w-0">
                    <div class="flex items-center gap-2">
                      <span class="truncate font-medium text-secondary-900">{{ log.user?.name || "Removed member" }}</span>
                      <span v-if="isOwner(log.user)" class="admin-badge admin-badge--brass">Owner</span>
                    </div>
                    <div class="truncate text-caption text-secondary-500">{{ log.user?.email }}</div>
                  </div>
                </div>
              </td>
              <td class="whitespace-nowrap">
                <span class="admin-badge" :class="actionTone(log.action_type)">
                  {{ formatActionType(log.action_type) }}
                </span>
              </td>
              <td class="max-w-md">
                <p class="line-clamp-2 text-secondary-700">{{ log.action_description || "—" }}</p>
              </td>
              <td class="text-right">
                <button type="button" class="admin-btn admin-btn--ghost admin-btn--sm" @click="viewDetails(log)">
                  Details
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
      item-label="records"
      @page-change="changePage"
      @per-page-change="changePerPage"
    />

    <!-- Details dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div v-if="selectedLog" class="admin-dialog-backdrop" @click.self="closeDetails">
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="Activity details"
            style="width: min(40rem, 100%); margin-top: 6vh; max-height: 88vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700">
                  <Icon name="heroicons:clipboard-document-list" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">{{ formatActionType(selectedLog.action_type) }}</p>
                  <p class="admin-panel-subtitle">{{ formatFullDate(selectedLog.created_at) }}</p>
                </div>
              </div>
              <button type="button" class="admin-icon-btn" aria-label="Close" @click="closeDetails">
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <div class="admin-panel-body space-y-5 overflow-y-auto">
              <p class="text-body text-secondary-800">{{ selectedLog.action_description || "No description." }}</p>

              <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <dt class="admin-label">Person</dt>
                  <dd class="text-body text-secondary-900">
                    {{ selectedLog.user?.name || "Removed member" }}
                    <span v-if="selectedLog.user?.email" class="block text-caption text-secondary-500">{{ selectedLog.user.email }}</span>
                  </dd>
                </div>
                <div>
                  <dt class="admin-label">IP address</dt>
                  <dd class="text-body text-secondary-900">{{ selectedLog.ip_address || "—" }}</dd>
                </div>
                <div v-if="selectedLog.entity_type">
                  <dt class="admin-label">Affected item</dt>
                  <dd class="text-body text-secondary-900">
                    {{ formatActionType(selectedLog.entity_type) }}<template v-if="selectedLog.entity_id"> #{{ selectedLog.entity_id }}</template>
                  </dd>
                </div>
                <div class="sm:col-span-2" v-if="selectedLog.user_agent">
                  <dt class="admin-label">Device</dt>
                  <dd class="break-all font-mono text-caption text-secondary-600">{{ selectedLog.user_agent }}</dd>
                </div>
              </dl>

              <!-- What changed: API sends an array of { field, old, new }.
                   It used to be rendered with v-html, which printed
                   "[object Object]" (and would have run any HTML in it). -->
              <div v-if="Array.isArray(selectedLog.changes) && selectedLog.changes.length">
                <p class="admin-label">What changed</p>
                <div class="overflow-hidden rounded-lg border border-secondary-200">
                  <table class="admin-table">
                    <thead>
                      <tr><th>Field</th><th>Before</th><th>After</th></tr>
                    </thead>
                    <tbody>
                      <tr v-for="change in selectedLog.changes" :key="change.field">
                        <td class="font-medium text-secondary-900">{{ formatActionType(change.field) }}</td>
                        <td class="text-secondary-500 break-words">{{ displayValue(change.old) }}</td>
                        <td class="text-secondary-900 break-words">{{ displayValue(change.new) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <div class="admin-dialog-foot">
              <button type="button" class="admin-btn admin-btn--outline" @click="closeDetails">Close</button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";

definePageMeta({
  middleware: ["auth"],
  layout: "user-dashboard",
});

const authStore = useAuthStore();
const { $api, $toast } = useNuxtApp();

const activityLogs = ref([]);
const people = ref([]);
const actionTypes = ref([]);
const statistics = ref(null);
const selectedLog = ref(null);
const loading = ref(true);
const statsLoading = ref(true);
const loadError = ref(false);
const exporting = ref(false);

const blankFilters = () => ({ employee_id: "", action_type: "", start_date: "", end_date: "", search: "" });
const filters = ref(blankFilters());
const hasActiveFilters = computed(() => Object.values(filters.value).some((v) => v !== ""));

const pagination = ref({ current_page: 1, last_page: 1, per_page: 20, total: 0 });

const activeFilterParams = () =>
  Object.fromEntries(Object.entries(filters.value).filter(([, v]) => v !== ""));

const fetchActivityLogs = async (page = 1) => {
  loading.value = true;
  loadError.value = false;
  try {
    const response = await $api.get("/business/activity-logs", {
      params: { page, per_page: pagination.value.per_page, ...activeFilterParams() },
    });
    if (response.success) {
      activityLogs.value = response.data || [];
      pagination.value = {
        current_page: response.current_page,
        last_page: response.last_page,
        per_page: response.per_page,
        total: response.total,
      };
    }
  } catch (error) {
    if (error?.__aborted) return;
    console.error("Error fetching activity logs:", error);
    loadError.value = true;
  } finally {
    loading.value = false;
  }
};

// People filter: the owner plus every member. (A failed load used to inject
// two invented people, "John Doe" and "Jane Smith", into this list.)
const fetchPeople = async () => {
  try {
    const response = await $api.get("/business/employees");
    const members = response?.data?.employees || [];
    const me = authStore.user;
    people.value = [
      ...(me ? [{ id: me.id, name: `${me.full_name || `${me.first_name || ""} ${me.last_name || ""}`.trim()} (you)` }] : []),
      ...members.map((m) => ({ id: m.id, name: m.full_name || `${m.first_name} ${m.last_name}` })),
    ];
  } catch (error) {
    if (!error?.__aborted) console.error("Error fetching team:", error);
  }
};

const fetchActionTypes = async () => {
  try {
    const response = await $api.get("/business/activity-logs/action-types");
    if (response.success) actionTypes.value = response.data ? Object.keys(response.data) : [];
  } catch (error) {
    if (!error?.__aborted) console.error("Error fetching action types:", error);
  }
};

const fetchStatistics = async () => {
  statsLoading.value = true;
  try {
    const response = await $api.get("/business/activity-logs/statistics");
    if (response.success) statistics.value = response.data;
  } catch (error) {
    if (!error?.__aborted) console.error("Error fetching statistics:", error);
  } finally {
    statsLoading.value = false;
  }
};

const refreshAll = () => {
  fetchActivityLogs(pagination.value.current_page);
  fetchStatistics();
};

const exportLogs = async () => {
  exporting.value = true;
  try {
    const response = await $api.get("/business/activity-logs/export", {
      params: activeFilterParams(),
      responseType: "text",
    });
    const csvData = typeof response === "string" ? response : response?.data ?? "";
    const blob = new Blob([csvData], { type: "text/csv;charset=utf-8;" });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = `activity_log_${new Date().toISOString().slice(0, 10)}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
    $toast?.success?.("Activity log exported");
  } catch (error) {
    console.error("Error exporting logs:", error);
    $toast?.error?.("Export failed. Please try again.");
  } finally {
    exporting.value = false;
  }
};

const viewDetails = (log) => {
  selectedLog.value = log;
};
const closeDetails = () => {
  selectedLog.value = null;
};
const onKeydown = (e) => {
  if (e.key === "Escape" && selectedLog.value) closeDetails();
};

const resetFilters = () => {
  filters.value = blankFilters();
  fetchActivityLogs(1);
};

let searchTimeout = null;
const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => fetchActivityLogs(1), 400);
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) fetchActivityLogs(page);
};
const changePerPage = (perPage) => {
  pagination.value.per_page = perPage;
  fetchActivityLogs(1);
};

// ---------- formatting ----------
const formatNumber = (n) => (typeof n === "number" ? n.toLocaleString("en-MY") : n ?? "—");

const formatActionType = (value) =>
  String(value || "")
    .replace(/[_-]+/g, " ")
    .replace(/\b\w/g, (c) => c.toUpperCase());

const ACTION_TONES = {
  login: "admin-badge--info",
  logout: "admin-badge--neutral",
  password_changed: "admin-badge--warning",
  profile_updated: "admin-badge--brass",
  landing_page_updated: "admin-badge--brass",
  link_created: "admin-badge--success",
  link_updated: "admin-badge--brass",
  link_deleted: "admin-badge--danger",
  nfc_card_activated: "admin-badge--success",
  nfc_card_deactivated: "admin-badge--warning",
  image_uploaded: "admin-badge--success",
  settings_changed: "admin-badge--brass",
};
const actionTone = (type) => {
  if (ACTION_TONES[type]) return ACTION_TONES[type];
  if (/delete|remove|cancel|reject|fail/.test(type || "")) return "admin-badge--danger";
  if (/create|add|activat|verified|success/.test(type || "")) return "admin-badge--success";
  return "admin-badge--neutral";
};

const isOwner = (user) => !!user && user.id === authStore.user?.id;

const initials = (name) => {
  const parts = String(name || "").trim().split(/\s+/).filter(Boolean);
  if (!parts.length) return "?";
  return (parts[0][0] + (parts[1]?.[0] || "")).toUpperCase();
};

const displayValue = (value) => {
  if (value === null || value === undefined || value === "") return "—";
  if (typeof value === "boolean") return value ? "Yes" : "No";
  if (typeof value === "object") return JSON.stringify(value);
  return String(value);
};

// API timestamps are "YYYY-MM-DD HH:MM:SS"; make them parseable everywhere.
const toDate = (value) => (value instanceof Date ? value : new Date(String(value).replace(" ", "T")));
const formatDate = (value) =>
  toDate(value).toLocaleDateString("en-MY", { year: "numeric", month: "short", day: "numeric" });
const formatTime = (value) =>
  toDate(value).toLocaleTimeString("en-MY", { hour: "2-digit", minute: "2-digit" });
const formatFullDate = (value) =>
  toDate(value).toLocaleString("en-MY", {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });

onMounted(() => {
  // Access (Business owner only) is enforced by the portal-access middleware
  // and by the API.
  fetchActivityLogs();
  fetchPeople();
  fetchActionTypes();
  fetchStatistics();
  window.addEventListener("keydown", onKeydown);
});
onBeforeUnmount(() => {
  window.removeEventListener("keydown", onKeydown);
  clearTimeout(searchTimeout);
});
</script>
