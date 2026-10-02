<template>
  <div>
    <!-- Header -->
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-secondary-900">
            Audit Log
          </h1>
          <p class="mt-2 text-secondary-600">
            Track and review ALL user activity, admin actions, and data mutations within the NFCGo system
          </p>
        </div>
        <div class="flex gap-3 flex-wrap">
          <button
            @click="handleRefresh"
            :disabled="loading"
            class="btn btn-outline disabled:opacity-60"
          >
            <Icon name="heroicons:arrow-path" :class="['h-4 w-4 mr-2', { 'animate-spin': loading }]" />
            {{ loading ? 'Loading...' : 'Refresh' }}
          </button>
          <button
            @click="exportAuditCsv"
            :disabled="exportingCsv || logs.length === 0"
            class="btn btn-primary disabled:opacity-60"
          >
            <span v-if="exportingCsv" class="loading loading-spinner loading-xs mr-2"></span>
            <Icon v-else name="heroicons:arrow-down-tray" class="h-4 w-4 mr-2" />
            {{ exportingCsv ? 'Generating CSV...' : 'Export CSV' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="p-3 rounded-xl bg-primary-100">
              <Icon name="heroicons:document-text" class="h-6 w-6 text-primary-600" />
            </div>
          </div>
          <div class="ml-5 flex-1">
            <p class="text-sm font-medium text-secondary-600">Total All Logs</p>
            <p class="text-2xl font-bold text-secondary-900 mt-1">
              {{ loading ? '—' : formatNumber(meta.total || logs.length) }}
            </p>
            <p v-if="meta.from && meta.to && !loading" class="text-xs text-secondary-500 mt-1">
              Showing {{ meta.from }}–{{ meta.to }} of {{ meta.total }}
            </p>
          </div>
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="p-3 rounded-xl bg-teal-100">
              <Icon name="heroicons:clock" class="h-6 w-6 text-teal-700" />
            </div>
          </div>
          <div class="ml-5">
            <p class="text-sm font-medium text-secondary-600">Today</p>
            <p class="text-2xl font-bold text-secondary-900 mt-1">
              {{ loading ? '—' : formatNumber(stats.today) }}
            </p>
            <p class="text-xs text-secondary-500 mt-1">Activity for {{ formatDateDMY(new Date()) }}</p>
          </div>
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="p-3 rounded-xl bg-amber-100">
              <Icon name="heroicons:clock" class="h-6 w-6 text-amber-700" />
            </div>
          </div>
          <div class="ml-5">
            <p class="text-sm font-medium text-secondary-600">Last 7 Days</p>
            <p class="text-2xl font-bold text-secondary-900 mt-1">
              {{ loading ? '—' : formatNumber(stats.last7Days) }}
            </p>
            <p class="text-xs text-secondary-500 mt-1">Recent activity trend</p>
          </div>
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="p-3 rounded-xl bg-violet-100">
              <Icon name="heroicons:check-circle" class="h-6 w-6 text-violet-700" />
            </div>
          </div>
          <div class="ml-5">
            <p class="text-sm font-medium text-secondary-600">Admin Actions</p>
            <p class="text-2xl font-bold text-secondary-900 mt-1">
              {{ loading ? '—' : formatNumber(stats.adminActions) }}
            </p>
            <p class="text-xs text-secondary-500 mt-1">
              {{ loading ? '—' : (meta.total ? `${Math.round((stats.adminActions / Math.max(meta.total, 1)) * 100)}% of total` : 'N/A') }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="card mb-6">
      <div class="card-body">
        <div class="flex flex-wrap items-end gap-4">
          <!-- 1. Keyword Search (Description / Metadata) -->
          <div class="flex-1 min-w-[240px]">
            <label class="block text-sm font-medium text-secondary-700 mb-1.5">
              Keyword Search
            </label>
            <div class="relative">
              <Icon
                name="heroicons:document-text"
                class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-secondary-400"
              />
              <input
                v-model="filters.search"
                type="text"
                placeholder="Search in description, entity, metadata..."
                maxlength="150"
                class="input input-bordered w-full pl-10 pr-4"
                @keydown.enter="handleSearch"
              />
            </div>
          </div>

          <!-- 2. Action Type Dropdown -->
          <div class="min-w-[200px]">
            <label class="block text-sm font-medium text-secondary-700 mb-1.5">
              Action Type
            </label>
            <select
              v-model="filters.action_type"
              class="input input-bordered w-full"
            >
              <option value="">All Types</option>
              <option
                v-for="actionType in meta.available_action_types || []"
                :key="actionType"
                :value="actionType"
              >
                {{ formatActionType(actionType) }}
              </option>
            </select>
          </div>

          <!-- 3. Date From -->
          <div class="min-w-[160px]">
            <label class="block text-sm font-medium text-secondary-700 mb-1.5">
              Date From
            </label>
            <input
              v-model="filters.date_from"
              type="date"
              class="input input-bordered w-full"
            />
          </div>

          <!-- 4. Date To -->
          <div class="min-w-[160px]">
            <label class="block text-sm font-medium text-secondary-700 mb-1.5">
              Date To
            </label>
            <input
              v-model="filters.date_to"
              type="date"
              class="input input-bordered w-full"
            />
          </div>

          <!-- 5. Actor Search (Nama / Email Admin/User) -->
          <div class="min-w-[220px]">
            <label class="block text-sm font-medium text-secondary-700 mb-1.5">
              Actor Name / Email
            </label>
            <div class="relative">
              <Icon
                name="heroicons:users"
                class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-secondary-400"
              />
              <input
                v-model="filters.actor_search"
                type="text"
                placeholder="Search name or email..."
                maxlength="100"
                class="input input-bordered w-full pl-10 pr-4"
                @keydown.enter="handleSearch"
              />
            </div>
          </div>
        </div>

        <!-- Filter Action Buttons Row -->
        <div class="mt-5 pt-5 border-t border-secondary-200 flex flex-wrap justify-between items-center gap-3">
          <div class="text-xs text-secondary-500 flex items-center gap-2">
            <Icon name="heroicons:information-circle" class="h-4 w-4" />
            <span>Tip: Press Enter on search to apply filters immediately</span>
          </div>
          <div class="flex gap-3 flex-wrap">
            <button
              @click="handleResetFilters"
              :disabled="loading"
              class="btn btn-outline disabled:opacity-60"
            >
              <Icon name="heroicons:x-mark" class="h-4 w-4 mr-2" />
              Reset Filters
            </button>
            <button
              @click="handleSearch"
              :disabled="loading"
              class="btn btn-primary disabled:opacity-60"
            >
              <Icon name="heroicons:funnel" class="h-4 w-4 mr-2" />
              Apply Search
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Audit Log Datatable -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-secondary-200">
          <thead class="bg-secondary-50">
            <tr>
              <th
                class="px-3 sm:px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500 whitespace-nowrap"
              >
                Time
              </th>
              <th
                class="px-3 sm:px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500 whitespace-nowrap"
              >
                Actor
              </th>
              <th
                class="px-3 sm:px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500 whitespace-nowrap"
              >
                Type
              </th>
              <th
                class="px-3 sm:px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500"
              >
                Description
              </th>
              <th
                class="px-3 sm:px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500 whitespace-nowrap"
              >
                Entity
              </th>
              <th
                class="px-3 sm:px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-secondary-500 whitespace-nowrap"
              >
                IP & HTTP
              </th>
              <th
                class="px-3 sm:px-6 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-secondary-500 whitespace-nowrap"
              >
                Actions
              </th>
            </tr>
          </thead>
          <tbody
            v-if="!loading && logs.length > 0"
            class="bg-white divide-y divide-secondary-200"
          >
            <tr
              v-for="log in logs"
              :key="log.id"
              class="hover:bg-secondary-50/60 transition-colors"
            >
              <!-- Timestamp -->
              <td class="px-3 sm:px-6 py-4 whitespace-nowrap align-top">
                <div class="text-sm font-medium text-secondary-900">
                  {{ formatTime(log.created_at) }}
                </div>
                <div class="text-xs text-secondary-500 mt-0.5">
                  {{ formatDateDMY(log.created_at) }}
                </div>
              </td>

              <!-- Actor (Admin/User) -->
              <td class="px-3 sm:px-6 py-4 whitespace-nowrap align-top">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-full bg-secondary-200 flex items-center justify-center flex-shrink-0 overflow-hidden shadow-sm">
                    <img
                      v-if="log.user?.account_image || log.user?.profile_image"
                      :src="log.user.account_image || log.user.profile_image"
                      :alt="log.user?.full_name || log.user?.name || 'User'"
                      class="w-full h-full object-cover"
                      @error="$event.target.style.display = 'none'"
                    />
                    <Icon v-else name="heroicons:users" class="h-5 w-5 text-secondary-400" />
                  </div>
                  <div class="min-w-0">
                    <div class="text-sm font-semibold text-secondary-900 truncate max-w-[180px]">
                      {{ log.user?.full_name || log.user?.name || 'System' }}
                    </div>
                    <div class="text-xs text-secondary-500 truncate max-w-[180px]">
                      {{ log.user?.email || log.metadata?.admin_context || 'N/A' }}
                    </div>
                    <div v-if="log.metadata?.admin_context" class="flex items-center gap-1 mt-1">
                      <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-violet-100 text-violet-800">
                        Admin
                      </span>
                    </div>
                  </div>
                </div>
              </td>

              <!-- Action Type Badge -->
              <td class="px-3 sm:px-6 py-4 whitespace-nowrap align-top">
                <span
                  :class="getActionBadgeClass(log.action_type)"
                  class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                >
                  <Icon :name="getActionIcon(log.action_type)" class="h-3.5 w-3.5 mr-1" />
                  {{ formatActionType(log.action_type) }}
                </span>
              </td>

              <!-- Description -->
              <td class="px-3 sm:px-6 py-4 align-top min-w-[260px]">
                <div class="text-sm text-secondary-800 leading-snug">
                  {{ log.description || 'No description' }}
                </div>
                <div v-if="log.metadata?.new_values && typeof log.metadata.new_values === 'object' && Object.keys(log.metadata.new_values).length > 0" class="mt-2.5">
                  <div class="inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded-full bg-green-50 text-green-700 border border-green-200">
                    <Icon name="heroicons:pencil-square" class="h-3 w-3" />
                    <span>{{ Object.keys(log.metadata.new_values).length }} fields updated</span>
                  </div>
                </div>
              </td>

              <!-- Entity Info -->
              <td class="px-3 sm:px-6 py-4 whitespace-nowrap align-top">
                <div v-if="log.entity_type || log.entity_id">
                  <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-secondary-100 border border-secondary-200">
                    <Icon name="heroicons:cube" class="h-3.5 w-3.5 text-secondary-500" />
                    <span class="text-xs font-medium text-secondary-700 capitalize">
                      {{ log.entity_type || 'unknown' }}
                    </span>
                  </div>
                  <div v-if="log.entity_id" class="mt-1.5">
                    <span class="text-[11px] font-mono px-1.5 py-0.5 rounded bg-secondary-50 text-secondary-600 border border-secondary-200">
                      #{{ log.entity_id }}
                    </span>
                  </div>
                </div>
                <span v-else class="text-xs text-secondary-400 italic">—</span>
              </td>

              <!-- IP Address + HTTP Method -->
              <td class="px-3 sm:px-6 py-4 whitespace-nowrap align-top">
                <div v-if="log.ip_address || log.metadata?.http_method" class="space-y-1">
                  <div v-if="log.ip_address" class="flex items-center gap-1.5">
                    <Icon name="heroicons:globe-alt" class="h-3.5 w-3.5 text-secondary-400" />
                    <span class="text-xs font-mono text-secondary-700 bg-secondary-50 px-1.5 py-0.5 rounded border border-secondary-200">
                      {{ log.ip_address }}
                    </span>
                  </div>
                  <div v-if="log.metadata?.http_method" class="flex items-center gap-1.5">
                    <Icon name="heroicons:link" class="h-3.5 w-3.5 text-secondary-400" />
                    <span
                      :class="getHttpMethodBadgeClass(log.metadata.http_method)"
                      class="text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded"
                    >
                      {{ log.metadata.http_method }}
                    </span>
                    <span v-if="log.metadata?.response_status" class="text-[11px] text-secondary-500 font-mono">
                      {{ log.metadata.response_status }}
                    </span>
                  </div>
                </div>
                <span v-else class="text-xs text-secondary-400 italic">—</span>
              </td>

              <!-- Action Button: View Detail -->
              <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-right align-top">
                <button
                  @click="openLogDetail(log)"
                  class="btn btn-outline btn-xs"
                >
                  <Icon name="heroicons:eye" class="h-3.5 w-3.5 mr-1" />
                  View Details
                </button>
              </td>
            </tr>
          </tbody>

          <!-- Loading State -->
          <tbody v-else-if="loading">
            <tr>
              <td colspan="7" class="px-3 sm:px-6 py-16 text-center">
                <div class="flex flex-col items-center justify-center gap-3">
                  <span class="loading loading-spinner text-primary-600 w-10 h-10"></span>
                  <p class="text-sm font-medium text-secondary-600">Loading audit log data...</p>
                </div>
              </td>
            </tr>
          </tbody>

          <!-- Empty State -->
          <tbody v-else>
            <tr>
              <td colspan="7" class="px-3 sm:px-6 py-20 text-center">
                <div class="flex flex-col items-center justify-center gap-4 max-w-md mx-auto">
                  <div class="w-16 h-16 rounded-full bg-secondary-100 flex items-center justify-center">
                    <Icon name="heroicons:document-text" class="h-8 w-8 text-secondary-400" />
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-secondary-800 mb-1.5">No Activity Records</h3>
                    <p class="text-sm text-secondary-500 leading-relaxed">
                      No activity logs match the current filters. Try resetting filters or changing search keywords.
                    </p>
                  </div>
                  <button
                    @click="handleResetFilters"
                    class="btn btn-outline btn-sm mt-2"
                  >
                    <Icon name="heroicons:arrow-path" class="h-4 w-4 mr-2" />
                    Reset All Filters
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div
        v-if="!loading && meta.last_page && meta.last_page > 1"
        class="px-6 py-4 border-t border-secondary-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4"
      >
        <div class="text-sm text-secondary-600">
          Showing
          <span class="font-semibold text-secondary-900">{{ meta.from || 0 }}</span>
          to
          <span class="font-semibold text-secondary-900">{{ meta.to || 0 }}</span>
          of
          <span class="font-semibold text-secondary-900">{{ meta.total || 0 }}</span>
          records
        </div>
        <div class="flex items-center gap-2 flex-wrap">
          <button
            @click="changePage(meta.current_page - 1)"
            :disabled="meta.current_page <= 1 || loading"
            class="btn btn-outline btn-sm disabled:opacity-40"
          >
            <Icon name="heroicons:chevron-left" class="h-4 w-4" />
            Previous
          </button>

          <div class="flex items-center gap-1">
            <template v-for="page in pageNumbers" :key="page">
              <span v-if="page === '...'" class="px-2.5 py-1 text-xs font-medium text-secondary-400">
                ···
              </span>
              <button
                v-else
                @click="changePage(page)"
                :disabled="loading"
                :class="[
                  'px-3 py-1.5 rounded-md text-sm font-semibold transition-colors min-w-[34px]',
                  page === meta.current_page
                    ? 'bg-primary-600 text-white shadow-sm'
                    : 'text-secondary-600 hover:bg-secondary-100'
                ]"
              >
                {{ page }}
              </button>
            </template>
          </div>

          <button
            @click="changePage(meta.current_page + 1)"
            :disabled="meta.current_page >= meta.last_page || loading"
            class="btn btn-outline btn-sm disabled:opacity-40"
          >
            Next
            <Icon name="heroicons:chevron-right" class="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- ============= LOG DETAIL MODAL ============= -->
    <div
      v-if="detailModal.show"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    >
      <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full mx-4 max-h-[88vh] overflow-hidden flex flex-col">
        <!-- Modal Header -->
        <div class="px-6 py-5 border-b border-secondary-200 flex items-start justify-between gap-4 flex-shrink-0">
          <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-xl bg-primary-100 flex items-center justify-center flex-shrink-0">
              <Icon name="heroicons:document-text" class="h-6 w-6 text-primary-700" />
            </div>
            <div>
              <h3 class="text-lg font-bold text-secondary-900 mb-0.5">
                Activity Details {{ detailModal.log?.id ? `#${detailModal.log.id}` : '' }}
              </h3>
              <p class="text-sm text-secondary-500 flex items-center gap-2 flex-wrap">
                <span
                  v-if="detailModal.log?.action_type"
                  :class="getActionBadgeClass(detailModal.log.action_type)"
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                >
                  {{ formatActionType(detailModal.log.action_type) }}
                </span>
                <span v-if="detailModal.log?.created_at" class="flex items-center gap-1">
                  <Icon name="heroicons:calendar" class="h-3.5 w-3.5" />
                  {{ formatDateDMY(detailModal.log.created_at) }} at {{ formatTime(detailModal.log.created_at) }}
                </span>
              </p>
            </div>
          </div>
          <button
            type="button"
            @click="closeLogDetail"
            class="text-secondary-400 hover:text-secondary-600 hover:bg-secondary-100 w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors"
          >
            <Icon name="heroicons:x-mark" class="h-5 w-5" />
          </button>
        </div>

        <!-- Modal Body Scrollable -->
        <div
          v-if="detailModal.log"
          class="px-6 py-5 overflow-y-auto flex-1 space-y-6"
        >
          <!-- Summary Info Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-secondary-50/60 rounded-xl border border-secondary-200">
              <p class="text-xs font-semibold uppercase tracking-wider text-secondary-500 mb-1.5">Actor</p>
              <p class="text-sm font-semibold text-secondary-900">
                {{ detailModal.log.user?.full_name || detailModal.log.user?.name || 'NFCGo System' }}
              </p>
              <p class="text-xs text-secondary-600 mt-0.5">
                {{ detailModal.log.user?.email || detailModal.log.metadata?.admin_context || 'No email' }}
              </p>
              <p v-if="detailModal.log.user?.id" class="text-[11px] text-secondary-500 mt-1">
                User ID: <span class="font-mono">{{ detailModal.log.user.id }}</span>
              </p>
            </div>
            <div class="p-4 bg-secondary-50/60 rounded-xl border border-secondary-200">
              <p class="text-xs font-semibold uppercase tracking-wider text-secondary-500 mb-1.5">Device & Location</p>
              <p v-if="detailModal.log.ip_address" class="text-sm font-mono text-secondary-800">
                IP: {{ detailModal.log.ip_address }}
              </p>
              <p v-if="detailModal.log.metadata?.http_method" class="text-sm text-secondary-700 mt-1">
                HTTP:
                <span :class="getHttpMethodBadgeClass(detailModal.log.metadata.http_method)" class="text-[11px] font-bold uppercase px-1.5 py-0.5 rounded">
                  {{ detailModal.log.metadata.http_method }}
                </span>
                <span v-if="detailModal.log.metadata?.response_status" class="ml-1 text-xs font-mono text-secondary-600">
                  ({{ detailModal.log.metadata.response_status }})
                </span>
              </p>
              <p v-if="detailModal.log.metadata?.route_uri" class="text-xs text-secondary-600 mt-1 break-all">
                Route: <span class="font-mono">{{ detailModal.log.metadata.route_uri }}</span>
              </p>
            </div>
            <div class="p-4 bg-secondary-50/60 rounded-xl border border-secondary-200">
              <p class="text-xs font-semibold uppercase tracking-wider text-secondary-500 mb-1.5">Target Entity</p>
              <p v-if="detailModal.log.entity_type" class="text-sm font-semibold text-secondary-900 capitalize">
                {{ detailModal.log.entity_type }}
                <span v-if="detailModal.log.entity_id" class="ml-1 text-xs font-mono font-normal text-secondary-600">#{{ detailModal.log.entity_id }}</span>
              </p>
              <p v-else class="text-sm text-secondary-500 italic">No specific entity</p>
            </div>
            <div class="p-4 bg-secondary-50/60 rounded-xl border border-secondary-200">
              <p class="text-xs font-semibold uppercase tracking-wider text-secondary-500 mb-1.5">Description</p>
              <p class="text-sm text-secondary-800 leading-snug">
                {{ detailModal.log.description || 'No description' }}
              </p>
            </div>
          </div>

          <!-- Old Values Section -->
          <div v-if="detailModal.log.metadata?.old_values && typeof detailModal.log.metadata.old_values === 'object' && Object.keys(detailModal.log.metadata.old_values).length > 0">
            <h4 class="text-sm font-bold text-secondary-800 mb-2.5 flex items-center gap-2">
              <Icon name="heroicons:archive-box-arrow-down" class="h-4 w-4 text-orange-500" />
              Old Values
            </h4>
            <div class="bg-orange-50 border border-orange-200 rounded-xl overflow-hidden">
              <pre class="p-4 text-xs font-mono text-orange-900 overflow-x-auto whitespace-pre-wrap leading-relaxed">{{ prettyJSON(detailModal.log.metadata.old_values) }}</pre>
            </div>
          </div>

          <!-- New Values Section -->
          <div v-if="detailModal.log.metadata?.new_values && typeof detailModal.log.metadata.new_values === 'object' && Object.keys(detailModal.log.metadata.new_values).length > 0">
            <h4 class="text-sm font-bold text-secondary-800 mb-2.5 flex items-center gap-2">
              <Icon name="heroicons:sparkles" class="h-4 w-4 text-green-600" />
              New Values
            </h4>
            <div class="bg-green-50 border border-green-200 rounded-xl overflow-hidden">
              <pre class="p-4 text-xs font-mono text-green-900 overflow-x-auto whitespace-pre-wrap leading-relaxed">{{ prettyJSON(detailModal.log.metadata.new_values) }}</pre>
            </div>
          </div>

          <!-- Full Metadata Section -->
          <div v-if="hasExtraMetadata">
            <h4 class="text-sm font-bold text-secondary-800 mb-2.5 flex items-center gap-2">
              <Icon name="heroicons:code-bracket-square" class="h-4 w-4 text-secondary-600" />
              Full Metadata (JSON)
            </h4>
            <div class="bg-secondary-900 rounded-xl overflow-hidden">
              <pre class="p-4 text-xs font-mono text-secondary-100 overflow-x-auto whitespace-pre-wrap leading-relaxed">{{ prettyJSON(detailModal.log.metadata) }}</pre>
            </div>
          </div>

          <!-- User Agent -->
          <div v-if="detailModal.log.user_agent">
            <h4 class="text-sm font-bold text-secondary-800 mb-2.5 flex items-center gap-2">
              <Icon name="heroicons:command-line" class="h-4 w-4 text-secondary-600" />
              User Agent
            </h4>
            <div class="bg-secondary-50 border border-secondary-200 rounded-xl p-4">
              <p class="text-xs font-mono text-secondary-700 break-all leading-relaxed">
                {{ detailModal.log.user_agent }}
              </p>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-secondary-200 flex flex-wrap justify-end gap-3 bg-secondary-50/50 flex-shrink-0">
          <button
            type="button"
            @click="closeLogDetail"
            class="btn btn-outline"
          >
            Close
          </button>
          <button
            v-if="detailModal.log?.id"
            type="button"
            @click="copyLogDetail"
            class="btn btn-primary"
          >
            <Icon name="heroicons:clipboard" class="h-4 w-4 mr-2" />
            Copy Details
          </button>
        </div>
      </div>
    </div>
    <!-- ============= END LOG DETAIL MODAL ============= -->
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
const { $api, showSuccess, showError } = useNuxtApp();

// =============== STATE ===============
const loading = ref(false);
const exportingCsv = ref(false);
const currentPage = ref(1);

const logs = ref([]);
const meta = reactive({
  total: 0,
  per_page: 25,
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  available_action_types: [],
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

// =============== COMPUTED ===============
const stats = computed(() => {
  // 🏰 Robust guard: NEVER crash even if logs.value is wrong shape
  // (e.g., Laravel pagination object accidentally assigned instead of array)
  const allLogs = Array.isArray(logs.value) ? logs.value : (
    Array.isArray(logs.value?.data) ? logs.value.data : []
  );
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const sevenDaysAgo = new Date();
  sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
  sevenDaysAgo.setHours(0, 0, 0, 0);

  let todayCount = 0;
  let last7Count = 0;
  let adminCount = 0;

  for (const log of allLogs) {
    if (!log || typeof log !== 'object') continue;
    const t = log.created_at ? new Date(log.created_at) : null;
    if (t && !Number.isNaN(t.getTime()) && t >= today) todayCount++;
    if (t && !Number.isNaN(t.getTime()) && t >= sevenDaysAgo) last7Count++;
    if (
      log.metadata?.admin_context ||
      log.user?.role === 'admin' ||
      log.user?.is_admin === true ||
      log.user?.is_admin === 1 ||
      log.metadata?.admin_role
    ) {
      adminCount++;
    }
  }

  return {
    today: todayCount,
    last7Days: last7Count,
    adminActions: adminCount,
  };
});

const hasExtraMetadata = computed(() => {
  const md = detailModal.log?.metadata;
  if (!md || typeof md !== 'object') return false;
  const keys = Object.keys(md);
  const baseKeys = ['old_values', 'new_values'];
  return keys.some(k => !baseKeys.includes(k) && (Array.isArray(md[k]) ? md[k].length > 0 : md[k] !== null && md[k] !== undefined && md[k] !== ''));
});

const pageNumbers = computed(() => {
  const current = meta.current_page || 1;
  const last = meta.last_page || 1;
  if (last <= 7) return Array.from({ length: last }, (_, i) => i + 1);
  const pages = [1];
  const start = Math.max(2, current - 1);
  const end = Math.min(last - 1, current + 1);
  if (start > 2) pages.push('...');
  for (let p = start; p <= end; p++) pages.push(p);
  if (end < last - 1) pages.push('...');
  pages.push(last);
  return pages;
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
  params.per_page = meta.per_page || 25;
  return params;
};

const fetchAuditLogs = async (resetPage = false) => {
  if (resetPage) currentPage.value = 1;
  loading.value = true;
  try {
    const params = buildQueryParams();
    const resp = await $api.get('/admin/audit-logs', { params });
    if (resp?.success) {
      // 🏰 Laravel API may return pagination in 3 shapes:
      // Shape A: { data: [...], meta: {...} }
      // Shape B: { data: { data: [...], meta: {...} } }  (double-wrapped from api.client unwrap of paginate resource)
      // Shape C: [ ... ] (plain array)
      let extractedData = resp.data ?? resp.logs ?? [];
      let extractedMeta = resp.meta ?? resp.pagination ?? {};

      // Handle double-wrapped pagination (Shape B is common in Laravel API Resources)
      if (extractedData && typeof extractedData === 'object' && !Array.isArray(extractedData)) {
        if (Array.isArray(extractedData.data)) {
          // Merge meta from both inner and outer
          const innerMeta = extractedData.meta || extractedData.pagination || {};
          extractedMeta = Object.assign({}, innerMeta, extractedMeta);
          extractedData = extractedData.data;
        } else {
          extractedData = [];
        }
      }

      // Final safety: always coerce to array (never set logs.value to an object!)
      logs.value = Array.isArray(extractedData) ? extractedData : [];

      // Merge meta (paginate fields: total/per_page/current_page/last_page/from/to + available_action_types)
      if (extractedMeta && typeof extractedMeta === 'object') {
        Object.assign(meta, extractedMeta);
      }
      if (resp.meta && typeof resp.meta === 'object') {
        Object.assign(meta, resp.meta);
      }

      // Parse available_action_types if serialized JSON string
      if (typeof meta.available_action_types === 'string') {
        try { meta.available_action_types = JSON.parse(meta.available_action_types); } catch {
          meta.available_action_types = [];
        }
      }
      if (!Array.isArray(meta.available_action_types)) meta.available_action_types = [];

      // If total not present in meta, fallback to array length
      if (!meta.total || typeof meta.total !== 'number') meta.total = logs.value.length;
      if (!meta.current_page) meta.current_page = currentPage.value;
      if (!meta.last_page) meta.last_page = Math.max(1, Math.ceil(meta.total / (meta.per_page || 25)));
      if (!meta.from || !meta.to) {
        const from = logs.value.length ? ((currentPage.value - 1) * (meta.per_page || 25)) + 1 : 0;
        meta.from = from;
        meta.to = logs.value.length ? from + logs.value.length - 1 : 0;
      }
    } else {
      showError(resp?.message || 'Failed to load audit log.');
    }
  } catch (e) {
    console.error('[Audit Log] Fetch failed:', e);
    showError(e?.data?.message || e?.message || 'Error while loading audit log data.');
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
    showSuccess('Audit log CSV file has been generated and downloaded.');
  } catch (e) {
    console.error('[Audit Log] Export CSV failed:', e);
    showError(e?.data?.message || e?.message || 'Failed to generate CSV. Please try again later.');
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
  const text = [
    `=== AUDIT LOG #${detailModal.log.id} ===`,
    `Time: ${formatDateDMY(detailModal.log.created_at)} ${formatTime(detailModal.log.created_at)}`,
    `Actor: ${detailModal.log.user?.full_name || detailModal.log.user?.name || 'System'} (${detailModal.log.user?.email || 'N/A'})`,
    `Action Type: ${detailModal.log.action_type}`,
    `Description: ${detailModal.log.description || 'N/A'}`,
    `Entity: ${detailModal.log.entity_type || 'N/A'}${detailModal.log.entity_id ? ' #' + detailModal.log.entity_id : ''}`,
    `IP Address: ${detailModal.log.ip_address || 'N/A'}`,
    `HTTP Method: ${detailModal.log.metadata?.http_method || 'N/A'} ${detailModal.log.metadata?.response_status || ''}`,
    `Route: ${detailModal.log.metadata?.route_uri || 'N/A'}`,
    ``,
    `Metadata (JSON):`,
    prettyJSON(detailModal.log.metadata),
  ].filter(Boolean).join('\n');
  try {
    await navigator.clipboard.writeText(text);
    showSuccess('Audit log details copied to clipboard.');
  } catch {
    showError('Failed to copy to clipboard.');
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

// =============== BADGE COLOR HELPERS ===============
const getActionBadgeClass = (actionType) => {
  const at = (actionType || '').toString().toLowerCase();
  if (/create|register|purchase|send|add_|_created$/.test(at)) return 'bg-green-100 text-green-800';
  if (/update|edit|modify|change|assign|enable|sync/.test(at)) return 'bg-amber-100 text-amber-800';
  if (/delete|remove|revoke|disable|cancel|unlink|purge/.test(at)) return 'bg-red-100 text-red-800';
  if (/login|auth|verify|2fa/.test(at)) return 'bg-sky-100 text-sky-800';
  if (/logout/.test(at)) return 'bg-gray-100 text-gray-700';
  if (/export|download|report|backup/.test(at)) return 'bg-purple-100 text-purple-800';
  if (/admin|audit|impersonate|bulk/.test(at)) return 'bg-violet-100 text-violet-800';
  return 'bg-secondary-100 text-secondary-700';
};

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

const getHttpMethodBadgeClass = (method) => {
  const m = (method || '').toString().toUpperCase();
  if (m === 'GET' || m === 'HEAD') return 'bg-sky-100 text-sky-800';
  if (m === 'POST') return 'bg-green-100 text-green-800';
  if (m === 'PUT' || m === 'PATCH') return 'bg-amber-100 text-amber-800';
  if (m === 'DELETE') return 'bg-red-100 text-red-800';
  return 'bg-secondary-100 text-secondary-700';
};

// =============== LIFECYCLE ===============
onMounted(async () => {
  await fetchAuditLogs(true);
});
</script>
