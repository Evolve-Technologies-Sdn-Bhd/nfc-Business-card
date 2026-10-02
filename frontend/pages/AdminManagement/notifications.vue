<!-- pages/AdminManagement/notifications.vue -->
<template>
  <div class="min-h-screen bg-secondary-50">
    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
      <div>
        <div class="flex items-center gap-3 mb-2">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center shadow-sm">
            <Icon name="heroicons:megaphone" class="w-5 h-5 text-white" />
          </div>
          <div>
            <h1 class="text-2xl font-bold text-secondary-900 tracking-tight">Notification Management</h1>
            <p class="text-sm text-secondary-500 mt-0.5">
              Manage system notifications, announcements, and delivery status
            </p>
          </div>
        </div>
      </div>
      <div class="flex items-center gap-3 flex-shrink-0">
        <button
          @click="showSendModal = true"
          class="px-4 py-2.5 text-sm bg-primary-600 text-white rounded-xl hover:bg-primary-700 active:bg-primary-800 transition-all duration-200 font-medium flex items-center shadow-sm hover:shadow-md hover:shadow-primary-600/10"
        >
          <Icon name="heroicons:megaphone" class="w-4 h-4 mr-2" />
          Send Announcement
        </button>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-2xl shadow-sm border border-secondary-200/60 p-5 hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs text-secondary-500 font-semibold uppercase tracking-wider">Total</p>
            <p class="text-3xl font-bold text-secondary-900 mt-1.5 tabular-nums">
              {{ statistics.total_notifications || 0 }}
            </p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center">
            <Icon
              name="heroicons:inbox-stack"
              class="w-6 h-6 text-blue-600"
            />
          </div>
        </div>
        <div class="mt-4 h-1 bg-blue-100 rounded-full overflow-hidden">
          <div class="h-full bg-blue-500 rounded-full w-full"></div>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-secondary-200/60 p-5 hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs text-orange-600 font-semibold uppercase tracking-wider">Unread</p>
            <p class="text-3xl font-bold text-orange-600 mt-1.5 tabular-nums">
              {{ statistics.total_unread || 0 }}
            </p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-orange-100 to-orange-200 rounded-xl flex items-center justify-center">
            <Icon
              name="heroicons:envelope"
              class="w-6 h-6 text-orange-600"
            />
          </div>
        </div>
        <div class="mt-4 h-1 bg-orange-100 rounded-full overflow-hidden">
          <div
            class="h-full bg-orange-500 rounded-full transition-all duration-500"
            :style="{ width: statistics.total_notifications ? Math.min(100, Math.round((statistics.total_unread || 0) / statistics.total_notifications * 100)) + '%' : '0%' }"
          ></div>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-secondary-200/60 p-5 hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs text-green-600 font-semibold uppercase tracking-wider">Today</p>
            <p class="text-3xl font-bold text-green-600 mt-1.5 tabular-nums">
              {{ statistics.today || 0 }}
            </p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-green-100 to-green-200 rounded-xl flex items-center justify-center">
            <Icon
              name="heroicons:calendar-days"
              class="w-6 h-6 text-green-600"
            />
          </div>
        </div>
        <div class="mt-4 h-1 bg-green-100 rounded-full overflow-hidden">
          <div
            class="h-full bg-green-500 rounded-full transition-all duration-500"
            :style="{ width: statistics.total_notifications ? Math.min(100, Math.round((statistics.today || 0) / statistics.total_notifications * 100)) + '%' : '0%' }"
          ></div>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-secondary-200/60 p-5 hover:shadow-md transition-all duration-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs text-purple-600 font-semibold uppercase tracking-wider">Last 7 Days</p>
            <p class="text-3xl font-bold text-purple-600 mt-1.5 tabular-nums">
              {{ statistics.recent_7_days || 0 }}
            </p>
          </div>
          <div class="w-12 h-12 bg-gradient-to-br from-purple-100 to-purple-200 rounded-xl flex items-center justify-center">
            <Icon
              name="heroicons:chart-bar"
              class="w-6 h-6 text-purple-600"
            />
          </div>
        </div>
        <div class="mt-4 h-1 bg-purple-100 rounded-full overflow-hidden">
          <div
            class="h-full bg-purple-500 rounded-full transition-all duration-500"
            :style="{ width: statistics.total_notifications ? Math.min(100, Math.round((statistics.recent_7_days || 0) / statistics.total_notifications * 100)) + '%' : '0%' }"
          ></div>
        </div>
      </div>
    </div>

    <!-- Actions Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-secondary-200/60 mb-5 overflow-hidden">
      <div class="p-5">
        <!-- Main filters row -->
        <div class="flex flex-wrap items-center gap-3">
          <!-- Search Input -->
          <div class="flex-1 min-w-[220px] relative">
            <Icon
              name="heroicons:magnifying-glass"
              class="absolute left-3.5 top-1/2 transform -translate-y-1/2 w-4 h-4 text-secondary-400"
            />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search users, title, or message..."
              maxlength="150"
              class="w-full pl-10 pr-10 py-2.5 border border-secondary-200 rounded-xl bg-secondary-50/50 focus:bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all text-sm text-secondary-900 placeholder:text-secondary-400"
              @input="handleSearch"
            />
            <button
              v-if="searchQuery"
              @click="searchQuery = ''; handleSearch()"
              class="absolute right-3 top-1/2 transform -translate-y-1/2 text-secondary-400 hover:text-secondary-600 transition-colors"
            >
              <Icon name="heroicons:x-mark" class="w-4 h-4" />
            </button>
          </div>
          <div class="text-xs text-secondary-400 font-medium min-w-[3.5rem] text-right">
            {{ searchQuery.length }}/150
          </div>

          <!-- Type Filter -->
          <select
            v-model="filterType"
            @change="handleSearch"
            class="px-3.5 py-2.5 border border-secondary-200 rounded-xl bg-white text-sm text-secondary-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all appearance-none cursor-pointer min-w-[160px]"
          >
            <option value="">All Types</option>
            <option value="admin_announcement">Announcements</option>
            <option value="system_message">System Messages</option>
            <option value="registration_success">Registration</option>
            <option value="login_new_device">Login Alerts</option>
            <option value="profile_updated">Profile Updates</option>
            <option value="password_changed">Password Changes</option>
            <option value="payment_successful">Successful Payments</option>
            <option value="payment_failed">Failed Payments</option>
            <option value="nfc_card_purchased">NFC Purchases</option>
            <option value="nfc_card_activated">NFC Activations</option>
          </select>

          <!-- Status Filter -->
          <select
            v-model="filterStatus"
            @change="handleSearch"
            class="px-3.5 py-2.5 border border-secondary-200 rounded-xl bg-white text-sm text-secondary-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all appearance-none cursor-pointer min-w-[130px]"
          >
            <option value="">All Status</option>
            <option value="read">Read</option>
            <option value="unread">Unread</option>
          </select>

          <!-- Date Range Filter -->
          <select
            v-model="filterDateRange"
            @change="filterDateRange !== 'custom' && handleSearch()"
            class="px-3.5 py-2.5 border border-secondary-200 rounded-xl bg-white text-sm text-secondary-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all appearance-none cursor-pointer min-w-[140px]"
          >
            <option value="">All Time</option>
            <option value="today">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
            <option value="custom">Custom Range</option>
          </select>
        </div>

        <!-- Custom Date Range Picker -->
        <div
          v-if="filterDateRange === 'custom'"
          class="mt-4 flex flex-wrap items-center gap-3 pt-4 border-t border-secondary-100"
        >
          <div class="flex items-center gap-2">
            <label class="text-xs text-secondary-500 font-semibold">From</label>
            <input
              v-model="customDateFrom"
              type="date"
              class="px-3 py-2 border border-secondary-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white"
            />
          </div>
          <Icon name="heroicons:arrow-right" class="w-4 h-4 text-secondary-300" />
          <div class="flex items-center gap-2">
            <label class="text-xs text-secondary-500 font-semibold">To</label>
            <input
              v-model="customDateTo"
              type="date"
              class="px-3 py-2 border border-secondary-200 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white"
            />
          </div>
          <button
            @click="applyCustomDateRange"
            class="ml-auto px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 text-xs font-semibold transition-colors shadow-sm"
          >
            Apply Range
          </button>
        </div>

        <!-- Action Buttons Row -->
        <div class="mt-4 pt-4 border-t border-secondary-100 flex items-center gap-2 flex-wrap justify-between">
          <div class="text-xs text-secondary-500 font-medium">
            {{ loading ? 'Loading data...' : `${paginationData.total || 0} total records found` }}
          </div>
          <div class="flex items-center gap-2 flex-wrap">
            <button
              @click="handleRefresh"
              :disabled="refreshing"
              class="px-3.5 py-2 bg-white text-secondary-700 rounded-xl hover:bg-secondary-50 transition-colors flex items-center border border-secondary-200 text-xs font-semibold disabled:opacity-50"
            >
              <Icon 
                name="heroicons:arrow-path" 
                :class="['w-3.5 h-3.5 mr-1.5', { 'animate-spin': refreshing }]" 
              />
              {{ refreshing ? 'Refreshing' : 'Refresh' }}
            </button>

            <button
              @click="showCleanupModal = true"
              class="px-3.5 py-2 bg-red-50 text-red-700 rounded-xl hover:bg-red-100 transition-colors flex items-center border border-red-100 text-xs font-semibold"
            >
              <Icon name="heroicons:trash" class="w-3.5 h-3.5 mr-1.5" />
              Cleanup
            </button>

            <button
              @click="exportNotifications"
              class="px-3.5 py-2 bg-green-50 text-green-700 rounded-xl hover:bg-green-100 transition-colors flex items-center border border-green-100 text-xs font-semibold"
            >
              <Icon name="heroicons:arrow-down-tray" class="w-3.5 h-3.5 mr-1.5" />
              Export CSV
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Notifications Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-secondary-200/60 overflow-hidden">
      <!-- Table Header Strip -->
      <div class="px-5 py-3.5 border-b border-secondary-200/60 bg-secondary-50/40 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-secondary-800">Notification Log</h3>
        <div class="flex items-center gap-1.5 text-xs text-secondary-500">
          <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
          Showing {{ paginationData.from || 0 }}–{{ paginationData.to || 0 }} of {{ paginationData.total || 0 }}
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-secondary-50/60">
            <tr>
              <th
                class="px-3 sm:px-5 py-3 text-left text-[11px] font-bold text-secondary-500 uppercase tracking-wider"
              >
                User
              </th>
              <th
                class="px-3 sm:px-5 py-3 text-left text-[11px] font-bold text-secondary-500 uppercase tracking-wider"
              >
                Type
              </th>
              <th
                class="px-3 sm:px-5 py-3 text-left text-[11px] font-bold text-secondary-500 uppercase tracking-wider"
              >
                Details
              </th>
              <th
                class="px-3 sm:px-5 py-3 text-left text-[11px] font-bold text-secondary-500 uppercase tracking-wider"
              >
                Priority
              </th>
              <th
                class="px-3 sm:px-5 py-3 text-left text-[11px] font-bold text-secondary-500 uppercase tracking-wider"
              >
                Status
              </th>
              <th
                class="px-3 sm:px-5 py-3 text-left text-[11px] font-bold text-secondary-500 uppercase tracking-wider"
              >
                Delivery
              </th>
              <th
                class="px-3 sm:px-5 py-3 text-left text-[11px] font-bold text-secondary-500 uppercase tracking-wider"
              >
                Date
              </th>
              <th
                class="px-3 sm:px-5 py-3 text-right text-[11px] font-bold text-secondary-500 uppercase tracking-wider"
              >
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-secondary-100/80">
            <!-- Loading State -->
            <tr v-if="loading">
              <td colspan="8" class="px-3 sm:px-5 py-16">
                <NFCGoWaveLoader
                  variant="wave"
                  size="md"
                  :showText="true"
                  labelText="NFCGo"
                  hintText="Loading notifications..."
                />
              </td>
            </tr>
            <!-- Empty State -->
            <tr v-else-if="notifications.length === 0">
              <td colspan="8" class="px-3 sm:px-5 py-16">
                <div class="flex flex-col items-center text-center">
                  <div class="w-20 h-20 bg-gradient-to-br from-secondary-100 to-secondary-200 rounded-2xl flex items-center justify-center mb-4">
                    <Icon
                      name="heroicons:bell-slash"
                      class="w-10 h-10 text-secondary-400"
                    />
                  </div>
                  <h3 class="text-base font-semibold text-secondary-900 mb-1">
                    No notifications found
                  </h3>
                  <p class="text-sm text-secondary-500 max-w-sm">
                    No notifications match your current filters. Try adjusting your search or clearing filters.
                  </p>
                </div>
              </td>
            </tr>
            <!-- Rows -->
            <tr
              v-for="notification in notifications"
              :key="notification.id"
              class="hover:bg-secondary-50/60 transition-colors group"
              :class="{ 'bg-primary-50/20': !notification.is_read }"
            >
              <td class="px-3 sm:px-5 py-4 whitespace-nowrap">
                <div class="flex items-center gap-3">
                  <div
                    v-if="
                      notification.type === 'business_card_order_request'
                        ? notification.data?.requesting_user_name
                        : notification.user?.full_name
                    "
                    class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center flex-shrink-0 text-xs font-bold text-primary-700 shadow-sm"
                  >
                    {{
                      (notification.type === 'business_card_order_request'
                        ? (notification.data?.requesting_user_name || notification.data?.name || '?')
                        : (notification.user?.full_name || notification.user?.name || '?')
                      ).charAt(0).toUpperCase()
                    }}
                  </div>
                  <div class="min-w-0">
                    <div class="text-sm font-semibold text-secondary-900 truncate max-w-[160px]">
                      {{
                        notification.type === "business_card_order_request"
                          ? notification.data?.requesting_user_name ||
                            notification.data?.name ||
                            "—"
                          : notification.user?.full_name || notification.user?.name || "System"
                      }}
                    </div>
                    <div class="text-xs text-secondary-500 truncate max-w-[160px]">
                      {{
                        notification.type === "business_card_order_request"
                          ? notification.data?.requesting_user_email ||
                            notification.data?.email ||
                            "—"
                          : notification.user?.email || "—"
                      }}
                    </div>
                    <div
                      v-if="
                        notification.type === 'business_card_order_request' &&
                        notification.data?.total_cards
                      "
                      class="text-[11px] text-blue-600 font-semibold mt-0.5 inline-flex items-center gap-1"
                    >
                      <Icon name="heroicons:identification" class="w-3 h-3" />
                      {{ notification.data.total_cards }} card{{
                        notification.data.total_cards > 1 ? "s" : ""
                      }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-3 sm:px-5 py-4 whitespace-nowrap">
                <span
                  class="inline-flex px-2.5 py-1 text-[11px] font-semibold rounded-lg"
                  :class="getTypeClass(notification.type)"
                >
                  {{ formatType(notification.type) }}
                </span>
              </td>
              <td class="px-3 sm:px-5 py-4 max-w-[320px]">
                <div class="text-sm font-semibold text-secondary-900 mb-0.5 leading-snug">
                  {{ notification.title }}
                  <span v-if="!notification.is_read" class="inline-block w-1.5 h-1.5 rounded-full bg-primary-500 ml-1.5 align-middle"></span>
                </div>
                <div class="text-xs text-secondary-500 leading-relaxed line-clamp-2">
                  {{ truncateText(notification.message, 80) }}
                </div>
              </td>
              <td class="px-3 sm:px-5 py-4 whitespace-nowrap">
                <span
                  class="inline-flex px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide rounded-lg"
                  :class="getPriorityClass(notification.priority)"
                >
                  {{ (notification.priority || 'normal').charAt(0).toUpperCase() + (notification.priority || 'normal').slice(1) }}
                </span>
              </td>
              <td class="px-3 sm:px-5 py-4 whitespace-nowrap">
                <span
                  v-if="notification.is_read"
                  class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-secondary-100 text-secondary-700"
                >
                  <Icon name="heroicons:check-circle-20-solid" class="w-3 h-3" />
                  Read
                </span>
                <span
                  v-else
                  class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-primary-100 text-primary-700"
                >
                  <Icon name="heroicons:envelope" class="w-3 h-3" />
                  Unread
                </span>
              </td>
              <td class="px-3 sm:px-5 py-4 whitespace-nowrap">
                <span
                  v-if="notification.delivery_failed"
                  class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-red-100 text-red-700"
                >
                  <Icon
                    name="heroicons:exclamation-triangle"
                    class="w-3 h-3"
                  />
                  Failed
                </span>
                <span
                  v-else-if="notification.delivered_at"
                  class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-green-100 text-green-700"
                >
                  <Icon name="heroicons:check-circle-20-solid" class="w-3 h-3" />
                  Delivered
                </span>
                <span
                  v-else
                  class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-amber-100 text-amber-700"
                >
                  <Icon name="heroicons:clock" class="w-3 h-3" />
                  Pending
                </span>
              </td>
              <td class="px-3 sm:px-5 py-4 whitespace-nowrap">
                <div class="text-xs font-semibold text-secondary-700">
                  {{ formatDate(notification.created_at).split(',')[0] }}
                </div>
                <div class="text-[11px] text-secondary-400">
                  {{ formatDate(notification.created_at).split(',')[1]?.trim() || '' }}
                </div>
              </td>
              <td class="px-3 sm:px-5 py-4 whitespace-nowrap text-right">
                <div class="inline-flex items-center gap-0.5 opacity-70 group-hover:opacity-100 transition-opacity">
                  <!-- Approve/Reject buttons for business card order requests -->
                  <template
                    v-if="
                      notification.type === 'business_card_order_request' &&
                      !notification.is_approved &&
                      !notification.is_rejected
                    "
                  >
                    <button
                      @click="approveOrder(notification)"
                      :disabled="approvingId === notification.id"
                      class="px-2.5 py-1.5 bg-green-50 text-green-700 hover:bg-green-100 rounded-lg text-[11px] font-bold transition-colors disabled:opacity-50 inline-flex items-center gap-1 border border-green-100"
                      title="Approve order"
                    >
                      <Icon name="heroicons:check" class="w-3 h-3" />
                      {{ approvingId === notification.id ? "..." : "Approve" }}
                    </button>
                    <button
                      @click="openRejectModal(notification)"
                      class="px-2.5 py-1.5 bg-red-50 text-red-700 hover:bg-red-100 rounded-lg text-[11px] font-bold transition-colors inline-flex items-center gap-1 border border-red-100"
                      title="Reject order"
                    >
                      <Icon name="heroicons:x-mark" class="w-3 h-3" />
                      Reject
                    </button>
                  </template>
                  <!-- Status badges for processed orders -->
                  <template
                    v-else-if="
                      notification.type === 'business_card_order_request'
                    "
                  >
                    <span
                      v-if="notification.is_approved"
                      class="px-2.5 py-1.5 bg-green-50 text-green-700 rounded-lg text-[11px] font-bold inline-flex items-center gap-1 border border-green-100"
                    >
                      <Icon
                        name="heroicons:check-circle-20-solid"
                        class="w-3 h-3"
                      />
                      Approved
                    </span>
                    <span
                      v-else-if="notification.is_rejected"
                      class="px-2.5 py-1.5 bg-red-50 text-red-700 rounded-lg text-[11px] font-bold inline-flex items-center gap-1 border border-red-100"
                    >
                      <Icon name="heroicons:x-circle" class="w-3 h-3" />
                      Rejected
                    </span>
                  </template>
                  <div class="w-px h-5 bg-secondary-200 mx-1"></div>
                  <button
                    @click="viewDetails(notification)"
                    class="p-2 text-purple-600 hover:text-purple-700 hover:bg-purple-50 rounded-lg transition-colors"
                    title="View details"
                  >
                    <Icon name="heroicons:eye" class="w-4 h-4" />
                  </button>
                  <button
                    @click="viewUserHistory(notification.user)"
                    class="p-2 text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors"
                    title="View user history"
                  >
                    <Icon name="heroicons:clock" class="w-4 h-4" />
                  </button>
                  <button
                    @click="deleteNotification(notification.id)"
                    class="p-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
                    title="Delete"
                  >
                    <Icon name="heroicons:trash" class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="!loading && paginationData.total > 0" class="border-t border-secondary-200/60 px-5 py-4 bg-secondary-50/30">
        <AdminPagination
          :current-page="currentPage"
          :last-page="paginationData.lastPage"
          :per-page="10"
          :total="paginationData.total"
          item-label="notifications"
          @page-change="goToPage"
          @per-page-change="(val) => { /* per-page handled in loadNotifications */ }"
        />
      </div>
    </div>

    <!-- Send Announcement Modal -->
    <Teleport to="body">
      <div
        v-if="showSendModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="showSendModal = false"
      >
        <div
          class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0"
        >
          <div
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
          ></div>

          <div
            class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
          >
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <h3 class="text-lg font-medium text-gray-900 mb-4">
                Send Announcement
              </h3>

              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Title
                  </label>
                  <input
                    v-model="announcementForm.title"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="Announcement title"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Message
                  </label>
                  <textarea
                    v-model="announcementForm.message"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter your message"
                  ></textarea>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Priority
                  </label>
                  <select
                    v-model="announcementForm.priority"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                  >
                    <option value="low">Low</option>
                    <option value="normal">Normal</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Action URL (Optional)
                  </label>
                  <input
                    v-model="announcementForm.action_url"
                    type="url"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="https://..."
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Action Text (Optional)
                  </label>
                  <input
                    v-model="announcementForm.action_text"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="Learn More"
                  />
                </div>

                <!-- Scheduling Options -->
                <div class="border-t pt-4">
                  <div class="flex items-center mb-3">
                    <input
                      id="schedule-checkbox"
                      v-model="announcementForm.schedule"
                      type="checkbox"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <label
                      for="schedule-checkbox"
                      class="ml-2 block text-sm font-medium text-gray-700"
                    >
                      Schedule for later
                    </label>
                  </div>

                  <div v-if="announcementForm.schedule" class="space-y-3 pl-6">
                    <div>
                      <label
                        class="block text-sm font-medium text-gray-700 mb-1"
                      >
                        Schedule Date & Time
                      </label>
                      <input
                        v-model="announcementForm.scheduled_at"
                        type="datetime-local"
                        :min="minScheduleDateTime"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                      />
                      <p class="text-xs text-gray-500 mt-1">
                        Notification will be sent at the specified time
                      </p>
                    </div>

                    <div>
                      <label
                        class="block text-sm font-medium text-gray-700 mb-1"
                      >
                        Timezone
                      </label>
                      <select
                        v-model="announcementForm.timezone"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                      >
                        <option value="UTC">UTC</option>
                        <option value="Asia/Kuala_Lumpur">
                          Asia/Kuala Lumpur (MYT)
                        </option>
                        <option value="Asia/Singapore">
                          Asia/Singapore (SGT)
                        </option>
                        <option value="America/New_York">
                          America/New York (EST)
                        </option>
                        <option value="Europe/London">
                          Europe/London (GMT)
                        </option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div
              class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse"
            >
              <button
                @click="sendAnnouncement"
                :disabled="sending"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                {{
                  sending
                    ? "Sending..."
                    : announcementForm.schedule
                    ? "Schedule Announcement"
                    : "Send to All Users"
                }}
              </button>
              <button
                @click="showSendModal = false"
                type="button"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Cleanup Modal -->
    <Teleport to="body">
      <div
        v-if="showCleanupModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="showCleanupModal = false"
      >
        <div
          class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0"
        >
          <div
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
          ></div>

          <div
            class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
          >
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <h3 class="text-lg font-medium text-gray-900 mb-4">
                Cleanup Old Notifications
              </h3>

              <p class="text-sm text-gray-500 mb-4">
                Delete read notifications older than:
              </p>

              <select
                v-model="cleanupDays"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              >
                <option :value="7">7 days</option>
                <option :value="14">14 days</option>
                <option :value="30">30 days</option>
                <option :value="60">60 days</option>
                <option :value="90">90 days</option>
              </select>
            </div>

            <div
              class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse"
            >
              <button
                @click="cleanupNotifications"
                :disabled="cleaning"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                {{ cleaning ? "Cleaning..." : "Delete" }}
              </button>
              <button
                @click="showCleanupModal = false"
                type="button"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Reject Order Modal -->
    <Teleport to="body">
      <div
        v-if="showRejectModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="showRejectModal = false"
      >
        <div
          class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0"
        >
          <div
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
          ></div>

          <div
            class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
          >
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <h3 class="text-lg font-medium text-gray-900 mb-4">
                Reject Order
              </h3>

              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Rejection Reason *
                  </label>
                  <textarea
                    v-model="rejectForm.reason"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="Please provide a reason for rejecting this order..."
                  ></textarea>
                </div>
              </div>
            </div>

            <div
              class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse"
            >
              <button
                @click="submitRejectOrder"
                :disabled="rejectingId || !rejectForm.reason.trim()"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                {{ rejectingId ? "Rejecting..." : "Reject Order" }}
              </button>
              <button
                @click="showRejectModal = false"
                type="button"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Notification Details Modal -->
    <Teleport to="body">
      <div
        v-if="showDetailsModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="showDetailsModal = false"
      >
        <div
          class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0"
        >
          <div
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
          ></div>

          <div
            class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full"
          >
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">
                  Notification Details
                </h3>
                <button
                  @click="showDetailsModal = false"
                  class="text-gray-400 hover:text-gray-500"
                >
                  <Icon name="heroicons:x-mark" class="w-6 h-6" />
                </button>
              </div>

              <div v-if="selectedNotificationDetail" class="space-y-4">
                <!-- Basic Info -->
                <div class="border-b pb-4">
                  <h4 class="text-sm font-semibold text-gray-700 mb-3">
                    Basic Information
                  </h4>
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="text-xs text-gray-500">Type</label>
                      <p class="text-sm font-medium">
                        {{ formatType(selectedNotificationDetail.type) }}
                      </p>
                    </div>
                    <div>
                      <label class="text-xs text-gray-500">Priority</label>
                      <p class="text-sm">
                        <span
                          :class="[
                            'px-2 py-1 text-xs font-medium rounded-full',
                            getPriorityClass(
                              selectedNotificationDetail.priority
                            ),
                          ]"
                        >
                          {{ getPriorityLabel(selectedNotificationDetail.priority) }}
                        </span>
                      </p>
                    </div>
                    <div>
                      <label class="text-xs text-gray-500">Status</label>
                      <p class="text-sm">
                        <span
                          v-if="selectedNotificationDetail.is_read"
                          class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800"
                        >
                          Read
                        </span>
                        <span
                          v-else
                          class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800"
                        >
                          Unread
                        </span>
                      </p>
                    </div>
                    <div>
                      <label class="text-xs text-gray-500">Date</label>
                      <p class="text-sm">
                        {{ formatDate(selectedNotificationDetail.created_at) }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Message -->
                <div class="border-b pb-4">
                  <h4 class="text-sm font-semibold text-gray-700 mb-2">
                    Message
                  </h4>
                  <p class="text-lg font-medium text-gray-900 mb-2">
                    {{ selectedNotificationDetail.title }}
                  </p>
                  <p class="text-sm text-gray-600">
                    {{ selectedNotificationDetail.message }}
                  </p>
                </div>

                <!-- Business Card Order Details -->
                <div
                  v-if="
                    selectedNotificationDetail.type ===
                    'business_card_order_request'
                  "
                  class="border-b pb-4"
                >
                  <h4 class="text-sm font-semibold text-gray-700 mb-3">
                    Order Details
                  </h4>
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="text-xs text-gray-500"
                        >Requesting User</label
                      >
                      <p class="text-sm font-medium">
                        {{
                          selectedNotificationDetail.data
                            ?.requesting_user_name || "N/A"
                        }}
                      </p>
                    </div>
                    <div>
                      <label class="text-xs text-gray-500">Email</label>
                      <p class="text-sm">
                        {{
                          selectedNotificationDetail.data
                            ?.requesting_user_email || "N/A"
                        }}
                      </p>
                    </div>
                    <div>
                      <label class="text-xs text-gray-500">Total Cards</label>
                      <p class="text-sm font-medium text-blue-600">
                        {{ selectedNotificationDetail.data?.total_cards || 0 }}
                      </p>
                    </div>
                    <div>
                      <label class="text-xs text-gray-500">Plan</label>
                      <p class="text-sm">
                        {{
                          selectedNotificationDetail.data?.subscription_plan ||
                          "N/A"
                        }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Cards List (Batch Order) -->
                <div
                  v-if="
                    selectedNotificationDetail.type ===
                      'business_card_order_request' &&
                    selectedNotificationDetail.data?.cards
                  "
                  class="border-b pb-4"
                >
                  <h4 class="text-sm font-semibold text-gray-700 mb-3">
                    Cards in this Order
                  </h4>
                  <div class="space-y-3 max-h-64 overflow-y-auto">
                    <div
                      v-for="(card, index) in selectedNotificationDetail.data
                        .cards"
                      :key="index"
                      class="bg-gray-50 p-3 rounded-lg"
                    >
                      <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center">
                          <span class="text-xs font-medium text-gray-500 mr-2"
                            >#{{ index + 1 }}</span
                          >
                          <h5 class="text-sm font-semibold text-gray-900">
                            {{ card.name }}
                          </h5>
                        </div>
                        <span
                          v-if="card.is_admin_card"
                          class="px-2 py-0.5 text-xs font-medium rounded bg-purple-100 text-purple-700"
                        >
                          Admin
                        </span>
                        <span
                          v-else
                          class="px-2 py-0.5 text-xs font-medium rounded bg-blue-100 text-blue-700"
                        >
                          Employee
                        </span>
                      </div>
                      <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                          <span class="text-gray-500">Email:</span>
                          <span class="text-gray-700 ml-1">{{
                            card.email
                          }}</span>
                        </div>
                        <div>
                          <span class="text-gray-500">Position:</span>
                          <span class="text-gray-700 ml-1">{{
                            card.position
                          }}</span>
                        </div>
                        <div>
                          <span class="text-gray-500">Contact:</span>
                          <span class="text-gray-700 ml-1">{{
                            card.contact_number
                          }}</span>
                        </div>
                        <div v-if="card.website">
                          <span class="text-gray-500">Website:</span>
                          <span class="text-gray-700 ml-1">{{
                            card.website
                          }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Delivery Address (Only for business_card_order_request) -->
                <div
                  v-if="
                    selectedNotificationDetail.type ===
                      'business_card_order_request' &&
                    selectedNotificationDetail.data?.delivery_address
                  "
                  class="border-b pb-4"
                >
                  <h4
                    class="text-sm font-semibold text-gray-700 mb-3 flex items-center"
                  >
                    <Icon
                      name="heroicons:map-pin"
                      class="w-4 h-4 mr-2 text-blue-600"
                    />
                    Delivery Address
                  </h4>
                  <div class="bg-blue-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-800">
                      {{ selectedNotificationDetail.data.delivery_address }}
                    </p>
                  </div>
                </div>

                <!-- Approval Status -->
                <div
                  v-if="
                    selectedNotificationDetail.type ===
                      'business_card_order_request' &&
                    (selectedNotificationDetail.is_approved ||
                      selectedNotificationDetail.is_rejected)
                  "
                >
                  <h4 class="text-sm font-semibold text-gray-700 mb-2">
                    Status
                  </h4>
                  <div
                    v-if="selectedNotificationDetail.is_approved"
                    class="bg-green-50 p-4 rounded-lg"
                  >
                    <div class="flex items-center">
                      <Icon
                        name="heroicons:check-circle"
                        class="w-5 h-5 text-green-600 mr-2"
                      />
                      <div>
                        <p class="text-sm font-medium text-green-800">
                          Approved
                        </p>
                        <p class="text-xs text-green-600">
                          {{
                            formatDate(selectedNotificationDetail.approved_at)
                          }}
                        </p>
                      </div>
                    </div>
                  </div>
                  <div
                    v-if="selectedNotificationDetail.is_rejected"
                    class="bg-red-50 p-4 rounded-lg"
                  >
                    <div class="flex items-start">
                      <Icon
                        name="heroicons:x-circle"
                        class="w-5 h-5 text-red-600 mr-2 mt-0.5"
                      />
                      <div class="flex-1">
                        <p class="text-sm font-medium text-red-800 mb-1">
                          Rejected
                        </p>
                        <p class="text-xs text-red-600 mb-2">
                          {{
                            formatDate(selectedNotificationDetail.rejected_at)
                          }}
                        </p>
                        <p class="text-sm text-gray-700">
                          <strong>Reason:</strong>
                          {{ selectedNotificationDetail.rejection_reason }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div
              class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse"
            >
              <button
                @click="showDetailsModal = false"
                type="button"
                class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:w-auto sm:text-sm"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Notification History Modal -->
    <Teleport to="body">
      <div
        v-if="showHistoryModal"
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="showHistoryModal = false"
      >
        <div
          class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0"
        >
          <div
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
          ></div>

          <div
            class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
          >
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">
                  Notification History
                  <span v-if="selectedUser" class="text-sm text-gray-500">
                    - {{ selectedUser.full_name }}
                  </span>
                </h3>
                <button
                  @click="showHistoryModal = false"
                  class="text-gray-400 hover:text-gray-500"
                >
                  <Icon name="heroicons:x-mark" class="w-6 h-6" />
                </button>
              </div>

              <!-- History Timeline -->
              <div class="max-h-96 overflow-y-auto">
                <div class="space-y-4">
                  <div
                    v-for="item in notificationHistory"
                    :key="item.id"
                    class="flex gap-4 pb-4 border-b last:border-b-0"
                  >
                    <div class="flex-shrink-0">
                      <div
                        :class="[
                          'w-10 h-10 rounded-full flex items-center justify-center',
                          item.is_read ? 'bg-gray-100' : 'bg-blue-100',
                        ]"
                      >
                        <Icon
                          :name="
                            item.is_read
                              ? 'heroicons:envelope-open'
                              : 'heroicons:envelope'
                          "
                          class="w-5 h-5"
                          :class="
                            item.is_read ? 'text-gray-600' : 'text-blue-600'
                          "
                        />
                      </div>
                    </div>
                    <div class="flex-1">
                      <div class="flex items-start justify-between">
                        <div>
                          <h4 class="text-sm font-medium text-gray-900">
                            {{ item.title }}
                          </h4>
                          <p class="text-sm text-gray-500 mt-1">
                            {{ item.message }}
                          </p>
                        </div>
                        <span
                          :class="[
                            'px-2 py-1 text-xs font-medium rounded-full',
                            getPriorityClass(item.priority),
                          ]"
                        >
                          {{ getPriorityLabel(item.priority) }}
                        </span>
                      </div>
                      <div
                        class="flex items-center gap-4 mt-2 text-xs text-gray-500"
                      >
                        <span>{{ formatDate(item.created_at) }}</span>
                        <span v-if="item.read_at">
                          Read: {{ formatDate(item.read_at) }}
                        </span>
                        <span
                          v-if="item.delivery_failed"
                          class="text-red-600 flex items-center"
                        >
                          <Icon
                            name="heroicons:exclamation-circle"
                            class="w-3 h-3 mr-1"
                          />
                          Delivery Failed
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-if="loadingHistory" class="text-center py-8">
                  <div
                    class="inline-block animate-spin rounded-full h-6 w-6 border-2 border-gray-300 border-t-blue-600"
                  ></div>
                </div>

                <div
                  v-if="!loadingHistory && notificationHistory.length === 0"
                  class="text-center py-8 text-gray-500"
                >
                  No notification history found
                </div>
              </div>
            </div>

            <div
              class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse"
            >
              <button
                @click="showHistoryModal = false"
                type="button"
                class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:w-auto sm:text-sm"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
definePageMeta({
  layout: "admin-management",
  middleware: ["auth", "admin"],
});

const { $api, $toast } = useNuxtApp();

// State
const loading = ref(false);
const sending = ref(false);
const cleaning = ref(false);
const loadingHistory = ref(false);
const notifications = ref([]);
const notificationHistory = ref([]);
const statistics = ref({});
const searchQuery = ref("");
const filterType = ref("");
const filterStatus = ref("");
const filterDateRange = ref("");
const customDateFrom = ref("");
const customDateTo = ref("");
const showSendModal = ref(false);
const showCleanupModal = ref(false);
const showHistoryModal = ref(false);
const showRejectModal = ref(false);
const showDetailsModal = ref(false);
const selectedUser = ref(null);
const selectedNotificationDetail = ref(null);
const cleanupDays = ref(30);
const approvingId = ref(null);
const rejectingId = ref(null);
const selectedNotification = ref(null);
const refreshing = ref(false);
const currentPage = ref(1);
const paginationData = ref({
  total: 0,
  from: 0,
  to: 0,
  lastPage: 1,
});

const announcementForm = reactive({
  title: "",
  message: "",
  priority: "normal",
  action_url: "",
  action_text: "",
  schedule: false,
  scheduled_at: "",
  timezone: "Asia/Kuala_Lumpur",
});

const rejectForm = reactive({
  reason: "",
});

// Computed minimum datetime for scheduling (current time + 5 minutes)
const minScheduleDateTime = computed(() => {
  const now = new Date();
  now.setMinutes(now.getMinutes() + 5);
  return now.toISOString().slice(0, 16);
});

// Load notifications
const loadNotifications = async () => {
  loading.value = true;
  try {
    const params = {};
    if (filterType.value) {
      params.type = filterType.value;
    }
    if (filterStatus.value) {
      params.status = filterStatus.value;
    }
    if (searchQuery.value) {
      params.search = searchQuery.value;
    }
    if (filterDateRange.value && filterDateRange.value !== "custom") {
      params.date_range = filterDateRange.value;
    }
    if (customDateFrom.value && customDateTo.value) {
      params.date_from = customDateFrom.value;
      params.date_to = customDateTo.value;
    }
    
    // Pagination
    params.page = currentPage.value;
    params.per_page = 10;

    const response = await $api.get("/admin/notifications", { params });

    if (response.success) {
      notifications.value = response.data.data || [];
      // Extract pagination data
      paginationData.value = {
        total: response.data.total || 0,
        from: response.data.from || 0,
        to: response.data.to || 0,
        lastPage: response.data.last_page || 1,
      };
    }
  } catch (error) {
    console.error("Error loading notifications:", error);
    $toast.error("Failed to load notifications");
  } finally {
    loading.value = false;
  }
};

// Load statistics
const loadStatistics = async () => {
  try {
    const response = await $api.get("/admin/notifications/statistics");

    if (response.success) {
      statistics.value = response.data;
    }
  } catch (error) {
    console.error("Error loading statistics:", error);
  }
};

// Computed: visible page numbers for pagination
const visiblePages = computed(() => {
  const total = paginationData.value.lastPage;
  const current = currentPage.value;
  const pages = [];
  
  if (total <= 7) {
    for (let i = 1; i <= total; i++) pages.push(i);
  } else {
    pages.push(1);
    if (current > 3) pages.push('...');
    for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
      pages.push(i);
    }
    if (current < total - 2) pages.push('...');
    pages.push(total);
  }
  return pages;
});

// Handle refresh button click
const handleRefresh = async () => {
  // Reset filters to default
  searchQuery.value = "";
  filterType.value = "";
  filterStatus.value = "";
  filterDateRange.value = "";
  customDateFrom.value = "";
  customDateTo.value = "";
  currentPage.value = 1;

  refreshing.value = true;
  try {
    await Promise.all([loadNotifications(), loadStatistics()]);
    $toast.success("Notifications refreshed");
  } finally {
    refreshing.value = false;
  }
};

// Go to specific page
const goToPage = (page) => {
  if (page < 1 || page > paginationData.value.lastPage) return;
  currentPage.value = page;
  loadNotifications();
};

// Send announcement
const sendAnnouncement = async () => {
  if (!announcementForm.title || !announcementForm.message) {
    $toast.error("Please fill in title and message");
    return;
  }

  if (announcementForm.schedule && !announcementForm.scheduled_at) {
    $toast.error("Please select a schedule date and time");
    return;
  }

  sending.value = true;
  try {
    const response = await $api.post(
      "/admin/notifications/announcement",
      announcementForm
    );

    if (response.success) {
      $toast.success(
        announcementForm.schedule
          ? "Announcement scheduled successfully"
          : "Announcement sent successfully"
      );
      showSendModal.value = false;

      // Reset form
      announcementForm.title = "";
      announcementForm.message = "";
      announcementForm.priority = "normal";
      announcementForm.action_url = "";
      announcementForm.action_text = "";
      announcementForm.schedule = false;
      announcementForm.scheduled_at = "";
      announcementForm.timezone = "Asia/Kuala_Lumpur";

      loadNotifications();
      loadStatistics();
    }
  } catch (error) {
    console.error("Error sending announcement:", error);
    $toast.error("Failed to send announcement");
  } finally {
    sending.value = false;
  }
};

// Delete notification
const deleteNotification = async (id) => {
  if (!confirm("Are you sure you want to delete this notification?")) {
    return;
  }

  try {
    const response = await $api.delete(`/admin/notifications/${id}`);

    if (response.success) {
      $toast.success("Notification deleted");
      loadNotifications();
      loadStatistics();
    }
  } catch (error) {
    console.error("Error deleting notification:", error);
    $toast.error("Failed to delete notification");
  }
};

// Cleanup old notifications
const cleanupNotifications = async () => {
  cleaning.value = true;
  try {
    const response = await $api.post("/admin/notifications/cleanup", {
      days: cleanupDays.value,
    });

    if (response.success) {
      $toast.success(response.message);
      showCleanupModal.value = false;
      loadNotifications();
      loadStatistics();
    }
  } catch (error) {
    console.error("Error cleaning up notifications:", error);
    $toast.error("Failed to cleanup notifications");
  } finally {
    cleaning.value = false;
  }
};

// Export notifications to CSV
const exportNotifications = async () => {
  try {
    $toast.info("Preparing export...");
    const params = {};
    if (filterType.value) params.type = filterType.value;
    if (filterStatus.value) params.status = filterStatus.value;
    if (searchQuery.value) params.search = searchQuery.value;
    if (filterDateRange.value && filterDateRange.value !== "custom") {
      params.date_range = filterDateRange.value;
    }
    if (customDateFrom.value && customDateTo.value) {
      params.date_from = customDateFrom.value;
      params.date_to = customDateTo.value;
    }
    params.per_page = 1000;
    params.page = 1;

    const response = await $api.get("/admin/notifications", { params });
    if (!response.success) throw new Error("Failed to fetch");

    const rows = response.data.data || [];
    if (rows.length === 0) {
      $toast.error("No data to export");
      return;
    }

    const headers = ["ID", "User", "Email", "Type", "Title", "Message", "Priority", "Status", "Delivery", "Created At"];
    const csvRows = [headers.join(",")];

    for (const n of rows) {
      const name =
        n.type === "business_card_order_request"
          ? n.data?.requesting_user_name || n.data?.name || "N/A"
          : n.user?.full_name || n.user?.name || "System";
      const email =
        n.type === "business_card_order_request"
          ? n.data?.requesting_user_email || n.data?.email || "N/A"
          : n.user?.email || "N/A";
      const deliveryStatus = n.delivery_failed
        ? "Failed"
        : n.delivered_at
        ? "Delivered"
        : "Pending";
      const status = n.is_read ? "Read" : "Unread";
      const safe = (val) => {
        if (val == null) return "";
        return `"${String(val).replace(/"/g, '""').replace(/\n/g, " ")}"`;
      };
      csvRows.push([
        safe(n.id),
        safe(name),
        safe(email),
        safe(n.type),
        safe(n.title),
        safe(n.message),
        safe(n.priority || "normal"),
        safe(status),
        safe(deliveryStatus),
        safe(n.created_at),
      ].join(","));
    }

    const blob = new Blob(["\uFEFF" + csvRows.join("\n")], { type: "text/csv;charset=utf-8;" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `notifications_${new Date().toISOString().slice(0, 10)}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);

    $toast.success(`Exported ${rows.length} notification(s)`);
  } catch (error) {
    console.error("Export failed:", error);
    $toast.error("Failed to export notifications");
  }
};

// Helper functions
const getTypeClass = (type) => {
  const classes = {
    admin_announcement: "bg-purple-100 text-purple-800",
    system_message: "bg-blue-100 text-blue-800",
    registration_success: "bg-green-100 text-green-800",
    login_new_device: "bg-red-100 text-red-800",
    profile_updated: "bg-teal-100 text-teal-800",
    password_changed: "bg-rose-100 text-rose-800",
    payment_successful: "bg-emerald-100 text-emerald-800",
    payment_failed: "bg-red-100 text-red-800",
    nfc_card_purchased: "bg-orange-100 text-orange-800",
    nfc_card_activated: "bg-amber-100 text-amber-800",
    business_card_order_request: "bg-indigo-100 text-indigo-800",
  };
  return classes[type] || "bg-gray-100 text-gray-700";
};

const getPriorityClass = (priority) => {
  const classes = {
    low: "bg-gray-100 text-gray-800",
    normal: "bg-blue-100 text-blue-800",
    high: "bg-orange-100 text-orange-800",
    urgent: "bg-red-100 text-red-800",
  };
  return classes[priority] || "bg-gray-100 text-gray-800";
};

const getPriorityLabel = (priority) => {
  const labels = {
    low: "Low",
    normal: "Normal",
    high: "High",
    urgent: "Urgent",
  };
  return (
    labels[String(priority || "").toLowerCase()] ||
    String(priority || "Unknown")
      .toLowerCase()
      .replace(/_/g, " ")
      .replace(/\b\w/g, (c) => c.toUpperCase())
      .trim() ||
    "Unknown"
  );
};

const formatType = (type) => {
  return type.replace(/_/g, " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const formatDate = (date) => {
  return new Date(date).toLocaleString();
};

const truncateText = (text, length) => {
  if (text.length <= length) return text;
  return text.substring(0, length) + "...";
};

// View notification details
const viewDetails = async (notification) => {
  selectedNotificationDetail.value = notification;
  showDetailsModal.value = true;
  
  // Mark notification as read if unread
  if (!notification.is_read) {
    try {
      const response = await $api.post(`/admin/notifications/${notification.id}/mark-read`);
      if (response.success) {
        notification.is_read = true;
        notification.read_at = new Date();
        // Trigger sidebar badge update
        window.dispatchEvent(new CustomEvent('admin-notifications-updated'));
        // Refresh statistics
        loadStatistics();
      }
    } catch (error) {
      console.error("Error marking notification as read:", error);
    }
  }
};

// View user notification history
const viewUserHistory = async (user) => {
  if (!user) return;

  selectedUser.value = user;
  showHistoryModal.value = true;
  loadingHistory.value = true;

  try {
    const response = await $api.get("/admin/notifications", {
      params: {
        user_id: user.id,
        per_page: 100,
      },
    });

    if (response.success) {
      notificationHistory.value = response.data.data || [];
    }
  } catch (error) {
    console.error("Error loading notification history:", error);
    $toast.error("Failed to load notification history");
  } finally {
    loadingHistory.value = false;
  }
};

// Apply custom date range
const applyCustomDateRange = () => {
  if (!customDateFrom.value || !customDateTo.value) {
    $toast.error("Please select both start and end dates");
    return;
  }
  loadNotifications();
};

// Approve order
const approveOrder = async (notification) => {
  // Get order details for confirmation
  const orderData = notification.data || {};
  const totalCards = orderData.total_cards || orderData.cards?.length || 1;
  const employeeCount = orderData.employee_count || 0;

  let confirmMessage = `Are you sure you want to approve this order?\n\n`;
  confirmMessage += `📦 Total Cards: ${totalCards}\n`;

  if (orderData.include_admin) {
    confirmMessage += `👤 Admin Card: 1\n`;
  }

  if (employeeCount > 0) {
    confirmMessage += `👥 Employee Cards: ${employeeCount}\n`;
    confirmMessage += `\n⚠️ This will automatically:\n`;
    confirmMessage += `✓ Create ${totalCards} NFC card(s)\n`;
    confirmMessage += `✓ Create ${employeeCount} employee account(s)\n`;
    confirmMessage += `✓ Set default password: Welcome123@\n`;
    confirmMessage += `✓ Send login credentials to employees`;
  }

  if (!confirm(confirmMessage)) {
    return;
  }

  approvingId.value = notification.id;
  try {
    const response = await $api.post(
      `/admin/notifications/${notification.id}/approve`
    );

    if (response.success) {
      // Show detailed success message
      const data = response.data || {};
      const cardsCreated = data.total_cards_created || 0;
      const employeesCreated = data.total_employees_created || 0;

      let successMessage = `✅ Order approved successfully!\n\n`;
      successMessage += `📦 ${cardsCreated} NFC card(s) created`;

      if (employeesCreated > 0) {
        successMessage += `\n👥 ${employeesCreated} employee account(s) created`;
        successMessage += `\n📧 Login credentials sent to employees`;
      }

      // Check if any accounts already existed
      const cards = data.cards || [];
      const existingAccounts = cards.filter(
        (c) => c.is_employee_card && !c.employee_account_created
      ).length;

      if (existingAccounts > 0) {
        successMessage += `\n\nℹ️ ${existingAccounts} employee(s) already had accounts`;
      }

      $toast.success(successMessage, {
        duration: 8000, // Show for 8 seconds
      });

      notification.is_approved = true;
      notification.approved_at = new Date();
      loadNotifications();
      loadStatistics();

      // Trigger sidebar badge update
      window.dispatchEvent(new CustomEvent("admin-notifications-updated"));
    }
  } catch (error) {
    console.error("Error approving order:", error);

    // Handle validation errors (existing employees - auto-rejected)
    if (error.data?.existing_employees) {
      const existingEmps = error.data.existing_employees;
      const isRejected = error.data?.is_rejected;

      let errorMessage = isRejected
        ? `🔴 Order Automatically Rejected!\n\n`
        : `❌ Cannot approve order!\n\n`;

      errorMessage += `${existingEmps.length} employee email(s) already exist:\n\n`;

      existingEmps.forEach((emp) => {
        errorMessage += `• ${emp.name} (${emp.email})\n`;
      });

      if (isRejected) {
        errorMessage += `\n✅ The user has been notified with the rejection reason.`;
        errorMessage += `\n\n💡 Solution: User should remove duplicate employees or use different emails.`;

        // Immediately update the notification status in the UI
        notification.is_rejected = true;
        notification.is_approved = false;
        notification.rejection_reason =
          error.data?.rejection_reason || "Duplicate employee emails detected";
        notification.rejected_at = new Date();
      } else {
        errorMessage += `\nThese employees already have accounts in the system.`;
      }

      $toast.error(errorMessage, {
        duration: 12000,
      });

      // Reload notifications to show rejected status
      if (isRejected) {
        loadNotifications();
        loadStatistics();
      }
    } else {
      // Generic error
      const errorMessage = error.data?.message || "Failed to approve order";
      $toast.error(`❌ ${errorMessage}`, {
        duration: 5000,
      });
    }
  } finally {
    approvingId.value = null;
  }
};

// Open reject modal
const openRejectModal = (notification) => {
  selectedNotification.value = notification;
  rejectForm.reason = "";
  showRejectModal.value = true;
};

// Submit reject order
const submitRejectOrder = async () => {
  if (!selectedNotification.value || !rejectForm.reason.trim()) {
    $toast.error("Please provide a rejection reason");
    return;
  }

  rejectingId.value = selectedNotification.value.id;
  try {
    const response = await $api.post(
      `/admin/notifications/${selectedNotification.value.id}/reject`,
      {
        rejection_reason: rejectForm.reason,
      }
    );

    if (response.success) {
      $toast.success("Order rejected successfully");
      selectedNotification.value.is_rejected = true;
      selectedNotification.value.rejection_reason = rejectForm.reason;
      selectedNotification.value.rejected_at = new Date();
      showRejectModal.value = false;
      loadNotifications();
      loadStatistics();

      // Trigger sidebar badge update
      window.dispatchEvent(new CustomEvent("admin-notifications-updated"));
    }
  } catch (error) {
    console.error("Error rejecting order:", error);
    $toast.error(error.data?.message || "Failed to reject order");
  } finally {
    rejectingId.value = null;
  }
};

// Watch filters - reset to page 1 when filters change
watch([searchQuery, filterType, filterStatus, filterDateRange], () => {
  currentPage.value = 1;
  loadNotifications();
});

// Initialize
onMounted(() => {
  loadNotifications();
  loadStatistics();
});
</script>
