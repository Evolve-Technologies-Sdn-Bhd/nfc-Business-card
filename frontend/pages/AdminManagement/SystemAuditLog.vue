<!-- pages/AdminManagement/SystemAuditLog.vue -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Security"
      eyebrow-icon="heroicons:clipboard-document-list"
      title="Audit Log"
      description="Track and review every user activity, admin action and data mutation recorded across the NFCGo system."
    >
      <template #actions>
        <button
          type="button"
          class="admin-btn admin-btn--outline"
          :disabled="loading"
          @click="handleRefresh"
        >
          <Icon
            name="heroicons:arrow-path"
            class="h-4 w-4"
            :class="{ 'animate-spin': loading }"
          />
          {{ loading ? 'Loading…' : 'Refresh' }}
        </button>
        <button
          type="button"
          class="admin-btn admin-btn--primary"
          :disabled="exportingCsv || logs.length === 0"
          @click="exportAuditCsv"
        >
          <Icon
            :name="exportingCsv ? 'heroicons:arrow-path' : 'heroicons:arrow-down-tray'"
            class="h-4 w-4"
            :class="{ 'animate-spin': exportingCsv }"
          />
          {{ exportingCsv ? 'Generating…' : 'Export CSV' }}
        </button>
      </template>
    </AdminPageHeader>

    <!-- Statistic tiles -->
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <AdminStatTile
        label="Total all logs"
        :value="formatNumber(meta.total || logs.length)"
        icon="heroicons:document-text"
        :hint="meta.from && meta.to ? `Showing ${meta.from}–${meta.to} of ${meta.total}` : ''"
        :loading="loading"
      />
      <AdminStatTile
        label="Today"
        :value="formatNumber(stats.today)"
        icon="heroicons:clock"
        :hint="`Activity for ${formatDateDMY(new Date())}`"
        :loading="loading"
      />
      <AdminStatTile
        label="Last 7 days"
        :value="formatNumber(stats.last7Days)"
        icon="heroicons:calendar-days"
        hint="Recent activity trend"
        :loading="loading"
      />
      <AdminStatTile
        label="Admin actions"
        :value="formatNumber(stats.adminActions)"
        icon="heroicons:check-circle"
        :hint="meta.total ? `${Math.round((stats.adminActions / Math.max(meta.total, 1)) * 100)}% of total` : 'N/A'"
        :loading="loading"
      />
    </div>

    <!-- Filters -->
    <AdminFilterBar @submit="handleSearch">
      <template #search>
        <div class="mb-[0.35rem] flex items-baseline justify-between gap-2">
          <label class="admin-label !mb-0">Keyword search</label>
          <span class="shrink-0 whitespace-nowrap text-helper text-secondary-400">
            Press Enter to search
          </span>
        </div>
        <div class="relative">
          <Icon
            name="heroicons:magnifying-glass"
            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-secondary-400"
          />
          <input
            v-model="filters.search"
            type="text"
            placeholder="Description, entity, metadata…"
            maxlength="150"
            class="admin-field !pl-9"
            @keydown.enter="handleSearch"
          />
        </div>
      </template>

      <div class="min-w-[12rem]">
        <label class="admin-label">Action type</label>
        <select v-model="filters.action_type" class="admin-field">
          <option value="">All types</option>
          <option
            v-for="actionType in meta.available_action_types || []"
            :key="actionType"
            :value="actionType"
          >
            {{ formatActionType(actionType) }}
          </option>
        </select>
      </div>

      <div class="min-w-[12rem]">
        <label class="admin-label">Entity type</label>
        <select v-model="filters.entity_type" class="admin-field">
          <option value="">All entities</option>
          <option
            v-for="entityType in meta.available_entity_types || []"
            :key="entityType"
            :value="entityType"
          >
            {{ formatActionType(entityType) }}
          </option>
        </select>
      </div>

      <div class="min-w-[10rem]">
        <label class="admin-label">Date from</label>
        <input v-model="filters.date_from" type="date" class="admin-field" />
      </div>

      <div class="min-w-[10rem]">
        <label class="admin-label">Date to</label>
        <input v-model="filters.date_to" type="date" class="admin-field" />
      </div>

      <div class="min-w-[13rem]">
        <label class="admin-label">Actor name / email</label>
        <div class="relative">
          <Icon
            name="heroicons:users"
            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-secondary-400"
          />
          <input
            v-model="filters.actor_search"
            type="text"
            placeholder="Name or email…"
            maxlength="100"
            class="admin-field !pl-9"
            @keydown.enter="handleSearch"
          />
        </div>
      </div>

      <template #actions>
        <span class="text-body-sm text-secondary-500">
          {{ formatNumber(meta.total) }} found
        </span>
        <button
          type="button"
          class="admin-btn admin-btn--outline admin-btn--sm"
          :disabled="loading"
          @click="handleResetFilters"
        >
          <Icon name="heroicons:x-mark" class="h-4 w-4" />
          Reset
        </button>
        <button
          type="submit"
          class="admin-btn admin-btn--primary admin-btn--sm"
          :disabled="loading"
        >
          <Icon name="heroicons:funnel" class="h-4 w-4" />
          Apply search
        </button>
      </template>
    </AdminFilterBar>

    <!-- Activity table -->
    <AdminSectionCard flush>
      <template #title>Activity records</template>
      <template #header-actions>
        <span class="text-caption text-secondary-500">
          {{ formatNumber(meta.total) }} record{{ meta.total === 1 ? '' : 's' }}
        </span>
      </template>

      <!-- Loading skeleton -->
      <div
        v-if="loading"
        class="divide-y divide-secondary-100"
        role="status"
        aria-busy="true"
      >
        <div
          v-for="i in 7"
          :key="i"
          class="flex items-center gap-4 px-5 py-4"
        >
          <span class="admin-skeleton h-4 w-24 shrink-0"></span>
          <span class="admin-skeleton h-9 w-9 shrink-0 rounded-full"></span>
          <span class="admin-skeleton h-4 w-1/4"></span>
          <span class="admin-skeleton ml-auto h-4 w-20"></span>
        </div>
      </div>

      <!-- Empty -->
      <AdminEmptyState
        v-else-if="!logs.length"
        icon="heroicons:document-text"
        title="No activity records"
        description="Nothing matches the current filters. Try resetting them or adjusting your search keywords."
      >
        <template #actions>
          <button
            type="button"
            class="admin-btn admin-btn--outline admin-btn--sm"
            @click="handleResetFilters"
          >
            <Icon name="heroicons:arrow-path" class="h-4 w-4" />
            Reset all filters
          </button>
        </template>
      </AdminEmptyState>

      <!-- Table -->
      <div v-else class="admin-scroll">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Time</th>
              <th>Actor</th>
              <th>Type</th>
              <th>Description</th>
              <th>Entity</th>
              <th>IP &amp; HTTP</th>
              <th class="admin-col-actions">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="log in logs" :key="log.id">
              <!-- Timestamp -->
              <td class="whitespace-nowrap">
                <div class="font-medium text-secondary-900">
                  {{ formatTime(log.created_at) }}
                </div>
                <div class="text-caption text-secondary-500">
                  {{ formatDateDMY(log.created_at) }}
                </div>
              </td>

              <!-- Actor -->
              <td>
                <div class="flex items-center gap-3">
                  <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-secondary-200 ring-1 ring-secondary-200"
                  >
                    <img
                      v-if="log.actor?.avatar_url"
                      :src="log.actor.avatar_url"
                      :alt="log.actor?.name || 'User'"
                      class="h-full w-full object-cover"
                      @error="$event.target.style.display = 'none'"
                    />
                    <Icon v-else name="heroicons:users" class="h-5 w-5 text-secondary-400" />
                  </div>
                  <div class="min-w-0">
                    <div class="flex items-center gap-2">
                      <span class="truncate text-body font-semibold text-secondary-900 max-w-[180px]">
                        {{ log.actor?.name || 'System' }}
                      </span>
                      <span
                        v-if="log.actor?.admin_role || log.metadata?.admin_context"
                        class="admin-badge admin-badge--warning admin-badge--plain"
                      >
                        Admin
                      </span>
                    </div>
                    <div class="truncate text-caption text-secondary-500 max-w-[180px]">
                      {{ log.actor?.email || 'N/A' }}
                    </div>
                  </div>
                </div>
              </td>

              <!-- Action type -->
              <td class="whitespace-nowrap">
                <div class="flex items-center gap-1.5">
                  <Icon
                    :name="getActionIcon(log.action_type)"
                    class="h-4 w-4 shrink-0 text-secondary-400"
                  />
                  <AdminStatusBadge
                    :tone="actionTone(log.action_type)"
                    :label="formatActionType(log.action_type)"
                  />
                </div>
              </td>

              <!-- Description -->
              <td class="min-w-[260px]">
                <div class="text-body text-secondary-800 leading-snug">
                  {{ log.action_description || 'No description' }}
                </div>
                <div
                  v-if="log.new_values && typeof log.new_values === 'object' && Object.keys(log.new_values).length > 0"
                  class="mt-2"
                >
                  <span class="admin-badge admin-badge--success admin-badge--plain">
                    <Icon name="heroicons:pencil-square" class="h-3 w-3" />
                    {{ Object.keys(log.new_values).length }} fields updated
                  </span>
                </div>
              </td>

              <!-- Entity -->
              <td class="whitespace-nowrap">
                <div v-if="log.entity_type || log.entity_id" class="space-y-1.5">
                  <span class="admin-badge admin-badge--neutral">
                    <Icon name="heroicons:cube" class="h-3.5 w-3.5" />
                    <span class="capitalize">{{ log.entity_type || 'unknown' }}</span>
                  </span>
                  <div v-if="log.entity_id">
                    <span class="font-mono text-table-body rounded border border-secondary-200 bg-secondary-50 px-1.5 py-0.5 text-secondary-600">
                      #{{ log.entity_id }}
                    </span>
                  </div>
                </div>
                <span v-else class="text-caption italic text-secondary-400">—</span>
              </td>

              <!-- IP & HTTP -->
              <td class="whitespace-nowrap">
                <div v-if="log.ip_address || log.metadata?.http_method" class="space-y-1.5">
                  <div v-if="log.ip_address" class="flex items-center gap-1.5">
                    <Icon name="heroicons:globe-alt" class="h-3.5 w-3.5 text-secondary-400" />
                    <span class="font-mono text-caption rounded border border-secondary-200 bg-secondary-50 px-1.5 py-0.5 text-secondary-700">
                      {{ log.ip_address }}
                    </span>
                  </div>
                  <div v-if="log.metadata?.http_method" class="flex items-center gap-1.5">
                    <Icon name="heroicons:link" class="h-3.5 w-3.5 text-secondary-400" />
                    <AdminStatusBadge
                      :tone="httpTone(log.metadata.http_method)"
                      :label="String(log.metadata.http_method).toUpperCase()"
                    />
                    <span v-if="log.metadata?.response_status" class="font-mono text-table-body text-secondary-500">
                      {{ log.metadata.response_status }}
                    </span>
                  </div>
                </div>
                <span v-else class="text-caption italic text-secondary-400">—</span>
              </td>

              <!-- Actions -->
              <td class="admin-col-actions">
                <button
                  type="button"
                  class="admin-btn admin-btn--outline admin-btn--sm"
                  @click="openLogDetail(log)"
                >
                  <Icon name="heroicons:eye" class="h-4 w-4" />
                  Details
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </AdminSectionCard>

    <AdminPagination
      v-if="logs.length > 0"
      :current-page="meta.current_page"
      :last-page="meta.last_page"
      :per-page="itemsPerPage"
      :total="meta.total"
      item-label="records"
      @page-change="changePage"
      @per-page-change="changeItemsPerPage"
    />

    <!-- ============= Log detail dialog ============= -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="detailModal.show"
          class="admin-dialog-backdrop"
          @click.self="closeLogDetail"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="Activity details"
            style="width: min(48rem, 100%); margin-top: 5vh; max-height: 90vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
                >
                  <Icon name="heroicons:document-text" class="h-5 w-5" />
                </span>
                <div class="min-w-0">
                  <p class="admin-panel-title">
                    Activity details {{ detailModal.log?.id ? `#${detailModal.log.id}` : '' }}
                  </p>
                  <p class="admin-panel-subtitle flex flex-wrap items-center gap-2">
                    <AdminStatusBadge
                      v-if="detailModal.log?.action_type"
                      :tone="actionTone(detailModal.log.action_type)"
                      :label="formatActionType(detailModal.log.action_type)"
                    />
                    <span v-if="detailModal.log?.created_at" class="flex items-center gap-1 text-caption text-secondary-500">
                      <Icon name="heroicons:calendar" class="h-3.5 w-3.5" />
                      {{ formatDateDMY(detailModal.log.created_at) }} · {{ formatTime(detailModal.log.created_at) }}
                    </span>
                  </p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="closeLogDetail"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <div
              v-if="detailModal.log"
              class="admin-panel-body flex-1 space-y-6 overflow-y-auto"
            >
              <!-- Summary grid -->
              <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-secondary-200 bg-secondary-50/60 p-4">
                  <p class="mb-1.5 text-caption font-semibold uppercase tracking-wider text-secondary-500">
                    Actor
                  </p>
                  <p class="text-body font-semibold text-secondary-900">
                    {{ detailModal.log.actor?.name || 'NFCGo System' }}
                  </p>
                  <p class="mt-0.5 text-caption text-secondary-600">
                    {{ detailModal.log.actor?.email || 'No email' }}
                  </p>
                  <p v-if="detailModal.log.actor?.id" class="mt-1 text-micro text-secondary-500">
                    User ID: <span class="font-mono">{{ detailModal.log.actor.id }}</span>
                  </p>
                </div>

                <div class="rounded-xl border border-secondary-200 bg-secondary-50/60 p-4">
                  <p class="mb-1.5 text-caption font-semibold uppercase tracking-wider text-secondary-500">
                    Device &amp; location
                  </p>
                  <p v-if="detailModal.log.ip_address" class="font-mono text-body text-secondary-800">
                    IP: {{ detailModal.log.ip_address }}
                  </p>
                  <p v-if="detailModal.log.metadata?.http_method" class="mt-1 flex items-center gap-1.5 text-body text-secondary-700">
                    HTTP:
                    <AdminStatusBadge
                      :tone="httpTone(detailModal.log.metadata.http_method)"
                      :label="String(detailModal.log.metadata.http_method).toUpperCase()"
                    />
                    <span v-if="detailModal.log.metadata?.response_status" class="font-mono text-caption text-secondary-600">
                      ({{ detailModal.log.metadata.response_status }})
                    </span>
                  </p>
                  <p v-if="detailModal.log.metadata?.route_uri" class="mt-1 break-all text-caption text-secondary-600">
                    Route: <span class="font-mono">{{ detailModal.log.metadata.route_uri }}</span>
                  </p>
                </div>

                <div class="rounded-xl border border-secondary-200 bg-secondary-50/60 p-4">
                  <p class="mb-1.5 text-caption font-semibold uppercase tracking-wider text-secondary-500">
                    Target entity
                  </p>
                  <p v-if="detailModal.log.entity_type" class="text-body font-semibold capitalize text-secondary-900">
                    {{ detailModal.log.entity_type }}
                    <span v-if="detailModal.log.entity_id" class="ml-1 font-mono text-caption font-normal text-secondary-600">
                      #{{ detailModal.log.entity_id }}
                    </span>
                  </p>
                  <p v-else class="text-body italic text-secondary-500">No specific entity</p>
                </div>

                <div class="rounded-xl border border-secondary-200 bg-secondary-50/60 p-4">
                  <p class="mb-1.5 text-caption font-semibold uppercase tracking-wider text-secondary-500">
                    Description
                  </p>
                  <p class="text-body leading-snug text-secondary-800">
                    {{ detailModal.log.action_description || 'No description' }}
                  </p>
                </div>
              </div>

              <!-- Old values -->
              <div v-if="detailModal.log.old_values && typeof detailModal.log.old_values === 'object' && Object.keys(detailModal.log.old_values).length > 0">
                <p class="admin-form-section-title flex items-center gap-2">
                  <Icon name="heroicons:archive-box-arrow-down" class="h-4 w-4 text-warning-500" />
                  Old values
                </p>
                <div class="mt-2.5 overflow-hidden rounded-xl border border-warning-200 bg-warning-50">
                  <pre class="overflow-x-auto whitespace-pre-wrap p-4 font-mono text-caption leading-relaxed text-warning-900">{{ prettyJSON(detailModal.log.old_values) }}</pre>
                </div>
              </div>

              <!-- New values -->
              <div v-if="detailModal.log.new_values && typeof detailModal.log.new_values === 'object' && Object.keys(detailModal.log.new_values).length > 0">
                <p class="admin-form-section-title flex items-center gap-2">
                  <Icon name="heroicons:sparkles" class="h-4 w-4 text-success-600" />
                  New values
                </p>
                <div class="mt-2.5 overflow-hidden rounded-xl border border-success-200 bg-success-50">
                  <pre class="overflow-x-auto whitespace-pre-wrap p-4 font-mono text-caption leading-relaxed text-success-900">{{ prettyJSON(detailModal.log.new_values) }}</pre>
                </div>
              </div>

              <!-- Full metadata -->
              <div v-if="hasExtraMetadata">
                <p class="admin-form-section-title flex items-center gap-2">
                  <Icon name="heroicons:code-bracket-square" class="h-4 w-4 text-secondary-600" />
                  Full metadata (JSON)
                </p>
                <div class="mt-2.5 overflow-hidden rounded-xl bg-secondary-900">
                  <pre class="overflow-x-auto whitespace-pre-wrap p-4 font-mono text-caption leading-relaxed text-secondary-100">{{ prettyJSON(detailModal.log.metadata) }}</pre>
                </div>
              </div>

              <!-- User agent -->
              <div v-if="detailModal.log.user_agent">
                <p class="admin-form-section-title flex items-center gap-2">
                  <Icon name="heroicons:command-line" class="h-4 w-4 text-secondary-600" />
                  User agent
                </p>
                <div class="mt-2.5 rounded-xl border border-secondary-200 bg-secondary-50 p-4">
                  <p class="break-all font-mono text-caption leading-relaxed text-secondary-700">
                    {{ detailModal.log.user_agent }}
                  </p>
                </div>
              </div>
            </div>

            <div class="admin-dialog-foot">
              <button
                type="button"
                class="admin-btn admin-btn--ghost"
                @click="closeLogDetail"
              >
                Close
              </button>
              <button
                v-if="detailModal.log?.id"
                type="button"
                class="admin-btn admin-btn--primary"
                @click="copyLogDetail"
              >
                <Icon name="heroicons:clipboard" class="h-4 w-4" />
                Copy details
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
    <!-- ============= End log detail dialog ============= -->
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';

// =============== PAGE META ===============
definePageMeta({
  middleware: ['auth', 'admin'],
  layout: 'admin-management',
});

useHead({
  title: 'Audit Log - Admin - NFCGo',
});

// =============== UTILITIES (SHARED) ===============
const { $api, $toast } = useNuxtApp();

// =============== STATE ===============
const loading = ref(false);
const exportingCsv = ref(false);
const currentPage = ref(1);
const itemsPerPage = ref(25);

const logs = ref([]);
const meta = reactive({
  total: 0,
  per_page: 25,
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  available_action_types: [],
  available_entity_types: [],
});

const filters = reactive({
  search: '',
  action_type: '',
  entity_type: '',
  actor_search: '',
  date_from: '',
  date_to: '',
});

const detailModal = reactive({
  show: false,
  log: null,
});

// Stats are returned by the backend (computed over the full filtered set,
// not just the current page) so the KPI tiles are always accurate.
const stats = reactive({
  today: 0,
  last7Days: 0,
  adminActions: 0,
});

const hasExtraMetadata = computed(() => {
  const md = detailModal.log?.metadata;
  if (!md || typeof md !== 'object') return false;
  const keys = Object.keys(md);
  return keys.some(k => {
    const v = md[k];
    return Array.isArray(v) ? v.length > 0 : v !== null && v !== undefined && v !== '';
  });
});

// =============== METHODS ===============
const buildQueryParams = () => {
  const params = {};
  if (filters.search) params.search = filters.search;
  if (filters.action_type) params.action_type = filters.action_type;
  if (filters.entity_type) params.entity_type = filters.entity_type;
  if (filters.actor_search) params.actor_search = filters.actor_search;
  if (filters.date_from) params.date_from = filters.date_from;
  if (filters.date_to) params.date_to = filters.date_to;
  params.page = currentPage.value;
  params.per_page = itemsPerPage.value;
  return params;
};

const fetchAuditLogs = async (resetPage = false) => {
  if (resetPage) currentPage.value = 1;
  loading.value = true;
  try {
    const params = buildQueryParams();
    const resp = await $api.get('/admin/audit-logs', { params });
    if (resp?.success) {
      // Laravel API returns { logs: { data, total, per_page, current_page, last_page, from, to, ... }, meta, stats }
      const paginator = resp.logs ?? resp.data ?? {};
      let extractedData = paginator.data ?? resp.data ?? resp.logs ?? [];

      // If the paginator itself is the array (plain array response)
      if (Array.isArray(paginator)) {
        extractedData = paginator;
      }

      // Final safety: always coerce to array
      logs.value = Array.isArray(extractedData) ? extractedData : [];

      // Pull pagination fields directly from the Laravel paginator object
      if (paginator && typeof paginator === 'object' && !Array.isArray(paginator)) {
        meta.total = typeof paginator.total === 'number' ? paginator.total : (logs.value.length || 0);
        meta.per_page = typeof paginator.per_page === 'number' ? paginator.per_page : (meta.per_page || 25);
        meta.current_page = typeof paginator.current_page === 'number' ? paginator.current_page : currentPage.value;
        meta.last_page = typeof paginator.last_page === 'number' ? paginator.last_page : Math.max(1, Math.ceil(meta.total / meta.per_page));
        meta.from = typeof paginator.from === 'number' ? paginator.from : (logs.value.length ? ((meta.current_page - 1) * meta.per_page) + 1 : 0);
        meta.to = typeof paginator.to === 'number' ? paginator.to : (logs.value.length ? meta.from + logs.value.length - 1 : 0);
      }

      // Merge additional meta (available_action_types, available_entity_types, total_count)
      if (resp.meta && typeof resp.meta === 'object') {
        if (Array.isArray(resp.meta.available_action_types)) {
          meta.available_action_types = resp.meta.available_action_types;
        }
        if (Array.isArray(resp.meta.available_entity_types)) {
          meta.available_entity_types = resp.meta.available_entity_types;
        }
        if (typeof resp.meta.total_count === 'number' && !meta.total) {
          meta.total = resp.meta.total_count;
        }
      }

      // Parse available_action_types if serialized JSON string
      if (typeof meta.available_action_types === 'string') {
        try { meta.available_action_types = JSON.parse(meta.available_action_types); } catch {
          meta.available_action_types = [];
        }
      }
      if (!Array.isArray(meta.available_action_types)) meta.available_action_types = [];
      if (!Array.isArray(meta.available_entity_types)) meta.available_entity_types = [];

      // Stats from backend (computed over full filtered dataset)
      if (resp.stats && typeof resp.stats === 'object') {
        stats.today = Number(resp.stats.today) || 0;
        stats.last7Days = Number(resp.stats.last_7_days) || 0;
        stats.adminActions = Number(resp.stats.admin_actions) || 0;
      }
    } else {
      $toast.error(resp?.message || 'Failed to load audit log.');
    }
  } catch (e) {
    console.error('[Audit Log] Fetch failed:', e);
    $toast.error(e?.data?.message || e?.message || 'Error while loading audit log data.');
  } finally {
    loading.value = false;
  }
};

const handleRefresh = () => fetchAuditLogs(false);
const handleSearch = () => fetchAuditLogs(true);

const handleResetFilters = () => {
  filters.search = '';
  filters.action_type = '';
  filters.entity_type = '';
  filters.actor_search = '';
  filters.date_from = '';
  filters.date_to = '';
  fetchAuditLogs(true);
};

const changePage = async (pageNum) => {
  if (pageNum < 1 || pageNum > meta.last_page) return;
  currentPage.value = pageNum;
  await fetchAuditLogs(false);
  if (typeof window !== 'undefined') {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const changeItemsPerPage = (perPage) => {
  itemsPerPage.value = perPage;
  currentPage.value = 1;
  fetchAuditLogs(false);
};

const exportAuditCsv = async () => {
  if (exportingCsv.value) return;
  exportingCsv.value = true;
  try {
    const params = buildQueryParams();
    const qs = new URLSearchParams(params).toString();
    const url = `/admin/audit-logs/export-csv${qs ? `?${qs}` : ''}`;
    const { $api } = useNuxtApp();
    const resp = await $api.get(url, { responseType: 'blob', timeout: 60000 });
    const blob = resp instanceof Blob ? resp : new Blob([resp], { type: 'text/csv; charset=utf-8' });
    const downloadUrl = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = downloadUrl;
    const todayStr = formatDateFilename(new Date());
    a.download = `nfcgo-audit-log-${todayStr}.csv`;
    document.body.appendChild(a);
    a.click();
    setTimeout(() => {
      document.body.removeChild(a);
      URL.revokeObjectURL(downloadUrl);
    }, 150);
    $toast.success('Audit log CSV file has been generated and downloaded.');
  } catch (e) {
    console.error('[Audit Log] Export CSV failed:', e);
    $toast.error(e?.data?.message || e?.message || 'Failed to generate CSV. Please try again later.');
  } finally {
    exportingCsv.value = false;
  }
};

const openLogDetail = (log) => {
  detailModal.log = log;
  detailModal.show = true;
  document.body.style.overflow = 'hidden';
};
const closeLogDetail = () => {
  detailModal.show = false;
  detailModal.log = null;
  document.body.style.overflow = '';
};

const copyLogDetail = async () => {
  if (!detailModal.log) return;
  const log = detailModal.log;
  const text = [
    `=== AUDIT LOG #${log.id} ===`,
    `Time: ${formatDateDMY(log.created_at)} ${formatTime(log.created_at)}`,
    `Actor: ${log.actor?.name || 'System'} (${log.actor?.email || 'N/A'})`,
    `Action Type: ${log.action_type}`,
    `Description: ${log.action_description || 'N/A'}`,
    `Entity: ${log.entity_type || 'N/A'}${log.entity_id ? ' #' + log.entity_id : ''}`,
    `IP Address: ${log.ip_address || 'N/A'}`,
    `HTTP Method: ${log.metadata?.http_method || 'N/A'} ${log.metadata?.response_status || ''}`,
    `Route: ${log.metadata?.route_uri || 'N/A'}`,
    ``,
    `Metadata (JSON):`,
    prettyJSON(log.metadata),
  ].filter(Boolean).join('\n');
  try {
    await navigator.clipboard.writeText(text);
    $toast.success('Audit log details copied to clipboard.');
  } catch {
    $toast.error('Failed to copy to clipboard.');
  }
};

// =============== FORMATTING HELPERS ===============
const formatActionType = (raw) => {
  if (!raw && raw !== 0) return '';
  const str = String(raw)
    .replace(/[_-]+/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();
  return str
    .split(' ')
    .map((w) => (w.length ? w[0].toUpperCase() + w.slice(1).toLowerCase() : w))
    .join(' ');
};
const formatNumber = (n) => {
  if (n === null || n === undefined || isNaN(n)) return '0';
  return new Intl.NumberFormat('ms-MY').format(Number(n));
};
const pad2 = (n) => String(n).padStart(2, '0');
const formatTime = (iso) => {
  if (!iso) return '—';
  const d = new Date(iso);
  if (isNaN(d.getTime())) return iso;
  return `${pad2(d.getHours())}:${pad2(d.getMinutes())}:${pad2(d.getSeconds())}`;
};
const formatDateDMY = (iso) => {
  if (!iso) return '—';
  const d = new Date(iso);
  if (isNaN(d.getTime())) return iso;
  const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
  return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
};
const formatDateFilename = (d) => {
  if (!d) d = new Date();
  return `${d.getFullYear()}${pad2(d.getMonth() + 1)}${pad2(d.getDate())}-${pad2(d.getHours())}${pad2(d.getMinutes())}`;
};
const prettyJSON = (obj) => {
  try { return JSON.stringify(obj, null, 2); }
  catch { return String(obj); }
};

// =============== BADGE TONE RESOLVERS ===============
// Maps action types onto Ink & Brass badge tones (never Tailwind colours,
// so the shared AdminStatusBadge stays the single source of styling).
const ACTION_TONE_RULES = [
  [/create|register|purchase|send|add_|_created$/, 'success'],
  [/update|edit|modify|change|assign|enable|sync/, 'warning'],
  [/delete|remove|revoke|disable|cancel|unlink|purge/, 'danger'],
  [/login|auth|verify|2fa/, 'info'],
  [/logout/, 'neutral'],
  [/export|download|report|backup/, 'brass'],
  [/admin|audit|impersonate|bulk/, 'brass'],
];
const actionTone = (actionType) => {
  const at = (actionType || '').toString().toLowerCase();
  for (const [re, tone] of ACTION_TONE_RULES) {
    if (re.test(at)) return tone;
  }
  return 'neutral';
};

const HTTP_TONE_MAP = {
  GET: 'info',
  HEAD: 'info',
  POST: 'success',
  PUT: 'warning',
  PATCH: 'warning',
  DELETE: 'danger',
};
const httpTone = (method) => HTTP_TONE_MAP[(method || '').toString().toUpperCase()] || 'neutral';

// ⚠️ IKON DI BAWAH HANYA GUNA SET YANG CONFIRMED WORKING (pernah digunakan & verified render sebagai icon BUKAN plain text):
//   heroicons:document-text, heroicons:check-circle, heroicons:arrow-down-tray,
//   heroicons:clock, heroicons:users, heroicons:check, heroicons:x-mark, heroicons:cube
const getActionIcon = (actionType) => {
  const at = (actionType || '').toString().toLowerCase();
  if (/create|register|purchase|add_|create_|new_|placed|ordered/.test(at)) return 'heroicons:check-circle';
  if (/update|edit|modify|change|assign|update_|updated/.test(at)) return 'heroicons:check';
  if (/delete|remove|revoke|cancel|unlink|deleted/.test(at)) return 'heroicons:x-mark';
  if (/login|auth|verify|2fa|logged_in|signin/.test(at)) return 'heroicons:users';
  if (/logout|logged_out|signout/.test(at)) return 'heroicons:users';
  if (/export|download|backup/.test(at)) return 'heroicons:arrow-down-tray';
  if (/admin|audit|role|permission/.test(at)) return 'heroicons:check-circle';
  if (/view|access|read|show|list|search|index|visited|viewed/.test(at)) return 'heroicons:document-text';
  return 'heroicons:document-text';
};

// =============== LIFECYCLE ===============
onMounted(async () => {
  await fetchAuditLogs(true);
});
</script>
