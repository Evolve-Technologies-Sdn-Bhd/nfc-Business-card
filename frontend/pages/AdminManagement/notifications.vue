<!-- pages/AdminManagement/notifications.vue -->
<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b sticky top-0 z-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <h1 class="text-2xl font-semibold text-gray-900">
            Notification Management
          </h1>
          <button
            @click="showSendModal = true"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center"
          >
            <Icon name="heroicons:megaphone" class="w-5 h-5 mr-2" />
            Send Announcement
          </button>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Total Notifications</p>
              <p class="text-2xl font-bold text-gray-900">
                {{ statistics.total_notifications || 0 }}
              </p>
            </div>
            <Icon
              name="heroicons:bell"
              class="w-12 h-12 text-blue-500 opacity-50"
            />
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Unread</p>
              <p class="text-2xl font-bold text-orange-600">
                {{ statistics.total_unread || 0 }}
              </p>
            </div>
            <Icon
              name="heroicons:envelope"
              class="w-12 h-12 text-orange-500 opacity-50"
            />
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Today</p>
              <p class="text-2xl font-bold text-green-600">
                {{ statistics.today || 0 }}
              </p>
            </div>
            <Icon
              name="heroicons:calendar"
              class="w-12 h-12 text-green-500 opacity-50"
            />
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Last 7 Days</p>
              <p class="text-2xl font-bold text-purple-600">
                {{ statistics.recent_7_days || 0 }}
              </p>
            </div>
            <Icon
              name="heroicons:chart-bar"
              class="w-12 h-12 text-purple-500 opacity-50"
            />
          </div>
        </div>
      </div>

      <!-- Actions Bar -->
      <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex flex-wrap items-center gap-4">
          <!-- Search Input -->
          <div class="flex-1 min-w-[200px]">
            <div class="relative">
              <Icon
                name="heroicons:magnifying-glass"
                class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
              />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search users, messages..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>

          <!-- Type Filter -->
          <select
            v-model="filterType"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
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
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Status</option>
            <option value="read">Read</option>
            <option value="unread">Unread</option>
            <option value="failed">Failed/Non-delivery</option>
          </select>

          <!-- Date Range Filter -->
          <select
            v-model="filterDateRange"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Time</option>
            <option value="today">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
            <option value="custom">Custom Range</option>
          </select>

          <button
            @click="loadNotifications"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center"
          >
            <Icon name="heroicons:arrow-path" class="w-5 h-5 mr-2" />
            Refresh
          </button>

          <button
            @click="showCleanupModal = true"
            class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors flex items-center"
          >
            <Icon name="heroicons:trash" class="w-5 h-5 mr-2" />
            Cleanup
          </button>

          <button
            @click="exportNotifications"
            class="px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors flex items-center"
          >
            <Icon name="heroicons:arrow-down-tray" class="w-5 h-5 mr-2" />
            Export
          </button>
        </div>

        <!-- Custom Date Range Picker -->
        <div
          v-if="filterDateRange === 'custom'"
          class="mt-4 flex items-center gap-4"
        >
          <div>
            <label class="block text-xs text-gray-600 mb-1">From</label>
            <input
              v-model="customDateFrom"
              type="date"
              class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-xs text-gray-600 mb-1">To</label>
            <input
              v-model="customDateTo"
              type="date"
              class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <button
            @click="applyCustomDateRange"
            class="mt-5 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Apply
          </button>
        </div>
      </div>

      <!-- Notifications List -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  User
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Type
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Message
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Priority
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Status
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Delivery
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Date
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="notification in notifications"
                :key="notification.id"
                class="hover:bg-gray-50"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div>
                      <div class="text-sm font-medium text-gray-900">
                        {{ notification.user?.full_name || "N/A" }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ notification.user?.email || "N/A" }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="px-2 py-1 text-xs font-medium rounded-full"
                    :class="getTypeClass(notification.type)"
                  >
                    {{ formatType(notification.type) }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">
                    {{ notification.title }}
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ truncateText(notification.message, 50) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="px-2 py-1 text-xs font-medium rounded-full"
                    :class="getPriorityClass(notification.priority)"
                  >
                    {{ notification.priority }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    v-if="notification.is_read"
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
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    v-if="notification.delivery_failed"
                    class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 flex items-center w-fit"
                  >
                    <Icon
                      name="heroicons:exclamation-triangle"
                      class="w-3 h-3 mr-1"
                    />
                    Failed
                  </span>
                  <span
                    v-else-if="notification.delivered_at"
                    class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 flex items-center w-fit"
                  >
                    <Icon name="heroicons:check-circle" class="w-3 h-3 mr-1" />
                    Delivered
                  </span>
                  <span
                    v-else
                    class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 flex items-center w-fit"
                  >
                    <Icon name="heroicons:clock" class="w-3 h-3 mr-1" />
                    Pending
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(notification.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex items-center gap-2">
                    <button
                      @click="viewUserHistory(notification.user)"
                      class="text-blue-600 hover:text-blue-900"
                      title="View user's notification history"
                    >
                      <Icon name="heroicons:clock" class="w-5 h-5" />
                    </button>
                    <button
                      @click="deleteNotification(notification.id)"
                      class="text-red-600 hover:text-red-900"
                      title="Delete notification"
                    >
                      <Icon name="heroicons:trash" class="w-5 h-5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-8">
          <div
            class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-gray-300 border-t-blue-600"
          ></div>
        </div>

        <!-- Empty State -->
        <div
          v-if="!loading && notifications.length === 0"
          class="text-center py-12"
        >
          <Icon
            name="heroicons:bell-slash"
            class="mx-auto h-12 w-12 text-gray-400"
          />
          <h3 class="mt-2 text-sm font-medium text-gray-900">
            No notifications
          </h3>
          <p class="mt-1 text-sm text-gray-500">
            No notifications found matching your criteria.
          </p>
        </div>
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
                          {{ item.priority }}
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
const selectedUser = ref(null);
const cleanupDays = ref(30);

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

    const response = await $api.get("/admin/notifications", { params });

    if (response.success) {
      notifications.value = response.data.data || [];
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

// Helper functions
const getTypeClass = (type) => {
  const classes = {
    admin_announcement: "bg-purple-100 text-purple-800",
    system_message: "bg-blue-100 text-blue-800",
    payment_successful: "bg-green-100 text-green-800",
    payment_failed: "bg-red-100 text-red-800",
    nfc_card_purchased: "bg-yellow-100 text-yellow-800",
  };
  return classes[type] || "bg-gray-100 text-gray-800";
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

// Export notifications
const exportNotifications = async () => {
  try {
    const params = {};
    if (filterType.value) params.type = filterType.value;
    if (filterStatus.value) params.status = filterStatus.value;
    if (searchQuery.value) params.search = searchQuery.value;

    // Create CSV content
    let csv = "User,Email,Type,Title,Message,Priority,Status,Delivery,Date\n";

    notifications.value.forEach((notif) => {
      const row = [
        notif.user?.full_name || "N/A",
        notif.user?.email || "N/A",
        formatType(notif.type),
        `"${notif.title.replace(/"/g, '""')}"`,
        `"${notif.message.replace(/"/g, '""')}"`,
        notif.priority,
        notif.is_read ? "Read" : "Unread",
        notif.delivery_failed
          ? "Failed"
          : notif.delivered_at
          ? "Delivered"
          : "Pending",
        formatDate(notif.created_at),
      ];
      csv += row.join(",") + "\n";
    });

    // Download CSV
    const blob = new Blob([csv], { type: "text/csv" });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `notifications-export-${
      new Date().toISOString().split("T")[0]
    }.csv`;
    a.click();
    window.URL.revokeObjectURL(url);

    $toast.success("Notifications exported successfully");
  } catch (error) {
    console.error("Error exporting notifications:", error);
    $toast.error("Failed to export notifications");
  }
};

// Watch filters
watch([searchQuery, filterType, filterStatus, filterDateRange], () => {
  loadNotifications();
});

// Initialize
onMounted(() => {
  loadNotifications();
  loadStatistics();
});
</script>
