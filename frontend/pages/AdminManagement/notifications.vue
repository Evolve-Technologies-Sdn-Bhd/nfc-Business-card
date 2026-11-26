<!-- pages/AdminManagement/notifications.vue -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Notification Centre"
      eyebrow-icon="heroicons:megaphone"
      title="Notification Management"
      description="Broadcast announcements, audit the system log and track delivery status across NFCGo."
    >
      <template #actions>
        <button
          type="button"
          class="admin-btn admin-btn--outline"
          :disabled="refreshing"
          @click="handleRefresh"
        >
          <Icon
            name="heroicons:arrow-path"
            class="h-4 w-4"
            :class="{ 'animate-spin': refreshing }"
          />
          {{ refreshing ? "Refreshing" : "Refresh" }}
        </button>
        <button
          type="button"
          class="admin-btn admin-btn--outline"
          @click="exportNotifications"
        >
          <Icon name="heroicons:arrow-down-tray" class="h-4 w-4" />
          Export CSV
        </button>
        <button
          type="button"
          class="admin-btn admin-btn--outline"
          @click="showCleanupModal = true"
        >
          <Icon name="heroicons:trash" class="h-4 w-4" />
          Cleanup
        </button>
        <button
          type="button"
          class="admin-btn admin-btn--primary"
          @click="showSendModal = true"
        >
          <Icon name="heroicons:megaphone" class="h-4 w-4" />
          Send announcement
        </button>
      </template>
    </AdminPageHeader>

    <!-- KPI tiles -->
    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <AdminStatTile
        label="Total notifications"
        :value="statistics.total_notifications || 0"
        icon="heroicons:inbox-stack"
      />
      <AdminStatTile
        label="Unread"
        :value="statistics.total_unread || 0"
        icon="heroicons:envelope"
        icon-class="!bg-warning-50 !text-warning-600"
        :hint="
          statistics.total_notifications
            ? Math.min(
                100,
                Math.round(
                  ((statistics.total_unread || 0) /
                    statistics.total_notifications) *
                    100
                )
              ) + '% of all'
            : ''
        "
      />
      <AdminStatTile
        label="Today"
        :value="statistics.today || 0"
        icon="heroicons:calendar-days"
        icon-class="!bg-success-50 !text-success-600"
        :hint="
          statistics.total_notifications
            ? Math.min(
                100,
                Math.round(
                  ((statistics.today || 0) / statistics.total_notifications) *
                    100
                )
              ) + '% of all'
            : ''
        "
      />
      <AdminStatTile
        label="Last 7 days"
        :value="statistics.recent_7_days || 0"
        icon="heroicons:chart-bar"
        :hint="
          statistics.total_notifications
            ? Math.min(
                100,
                Math.round(
                  ((statistics.recent_7_days || 0) /
                    statistics.total_notifications) *
                    100
                )
              ) + '% of all'
            : ''
        "
      />
    </div>

    <!-- Filters -->
    <AdminFilterBar @submit="handleSearch">
      <template #search>
        <div class="mb-[0.35rem] flex items-baseline justify-between gap-2">
          <label class="admin-label !mb-0">Search</label>
          <span class="text-helper text-secondary-400">
            {{ searchQuery.length }}/150
          </span>
        </div>
        <div class="relative">
          <Icon
            name="heroicons:magnifying-glass"
            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-secondary-400"
          />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search users, title, or message…"
            maxlength="150"
            class="admin-field !pl-9 !pr-9"
            @input="onSearchInput"
          />
          <button
            v-if="searchQuery"
            type="button"
            class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md p-1 text-secondary-400 transition hover:bg-secondary-100 hover:text-secondary-600"
            aria-label="Clear search"
            @click="clearSearch"
          >
            <Icon name="heroicons:x-mark" class="h-4 w-4" />
          </button>
        </div>
      </template>

      <div class="min-w-[11rem]">
        <label class="admin-label">Type</label>
        <select
          v-model="filterType"
          class="admin-field"
          @change="handleSearch"
        >
          <option value="">All types</option>
          <optgroup label="Announcements &amp; system">
            <option value="admin_announcement">Announcements</option>
            <option value="system_message">System messages</option>
            <option value="app_update">App updates</option>
            <option value="email_verification">Email verification</option>
          </optgroup>
          <optgroup label="Account &amp; security">
            <option value="registration_success">Registration</option>
            <option value="account_created">Account created</option>
            <option value="login_new_device">Login alerts</option>
            <option value="password_changed">Password changes</option>
            <option value="profile_updated">Profile updates</option>
            <option value="account_warning">Account warnings</option>
          </optgroup>
          <optgroup label="Payments &amp; subscription">
            <option value="payment_successful">Successful payments</option>
            <option value="payment_failed">Failed payments</option>
            <option value="subscription_upgrade">Subscription upgrades</option>
          </optgroup>
          <optgroup label="NFC cards">
            <option value="nfc_card_purchased">NFC purchases</option>
            <option value="nfc_card_order_created">NFC orders created</option>
            <option value="nfc_card_payment_proof_uploaded">
              Payment proof uploaded
            </option>
            <option value="nfc_card_payment_verified">Payment verified</option>
            <option value="nfc_card_processing">NFC processing</option>
            <option value="nfc_card_shipped">NFC shipped</option>
            <option value="nfc_card_delivered">NFC delivered</option>
            <option value="nfc_card_linked">NFC linked</option>
            <option value="nfc_card_activated">NFC activations</option>
            <option value="nfc_card_expired">NFC expired</option>
          </optgroup>
          <optgroup label="Business orders">
            <option value="business_card_order_request">Order requests</option>
            <option value="order_rejected">Order rejected</option>
          </optgroup>
          <optgroup label="Engagement">
            <option value="link_milestone">Link milestones</option>
            <option value="landing_page_viewed">Landing page views</option>
            <option value="contact_request">Contact requests</option>
          </optgroup>
        </select>
      </div>

      <div class="min-w-[8rem]">
        <label class="admin-label">Status</label>
        <select
          v-model="filterStatus"
          class="admin-field"
          @change="handleSearch"
        >
          <option value="">All status</option>
          <option value="read">Read</option>
          <option value="unread">Unread</option>
        </select>
      </div>

      <div class="min-w-[9rem]">
        <label class="admin-label">Date range</label>
        <select
          v-model="filterDateRange"
          class="admin-field"
          @change="filterDateRange !== 'custom' && handleSearch()"
        >
          <option value="">All time</option>
          <option value="today">Today</option>
          <option value="week">This week</option>
          <option value="month">This month</option>
          <option value="custom">Custom range</option>
        </select>
      </div>

      <div v-if="filterDateRange === 'custom'" class="min-w-[9rem]">
        <label class="admin-label">From</label>
        <input v-model="customDateFrom" type="date" class="admin-field" />
      </div>
      <div v-if="filterDateRange === 'custom'" class="min-w-[9rem]">
        <label class="admin-label">To</label>
        <input v-model="customDateTo" type="date" class="admin-field" />
      </div>

      <template #actions>
        <span class="text-body-sm text-secondary-500">
          {{ loading ? "Loading…" : `${paginationData.total || 0} records` }}
        </span>
        <button
          v-if="filterDateRange === 'custom'"
          type="button"
          class="admin-btn admin-btn--primary admin-btn--sm"
          @click="applyCustomDateRange"
        >
          <Icon name="heroicons:check" class="h-4 w-4" />
          Apply range
        </button>
      </template>
    </AdminFilterBar>

    <!-- Notification log -->
    <AdminSectionCard flush>
      <template #title>Notification log</template>
      <template #header-actions>
        <span class="text-caption text-secondary-500">
          Showing {{ paginationData.from || 0 }}–{{ paginationData.to || 0 }} of
          {{ paginationData.total || 0 }}
        </span>
      </template>

      <!-- Loading -->
      <div
        v-if="loading"
        class="divide-y divide-secondary-100"
        role="status"
        aria-busy="true"
      >
        <div
          v-for="i in 6"
          :key="i"
          class="flex items-center gap-4 px-5 py-4"
        >
          <span class="admin-skeleton h-9 w-9 shrink-0 rounded-xl"></span>
          <span class="admin-skeleton h-4 w-1/4"></span>
          <span class="admin-skeleton h-4 w-28"></span>
          <span class="admin-skeleton h-4 w-20"></span>
          <span class="admin-skeleton ml-auto h-4 w-24"></span>
        </div>
      </div>

      <!-- Empty -->
      <AdminEmptyState
        v-else-if="notifications.length === 0"
        icon="heroicons:bell-slash"
        title="No notifications found"
        description="No notifications match the current filters. Adjust your search, or reset the filters to widen the results."
      />

      <!-- Table -->
      <div v-else class="admin-scroll">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Recipient</th>
              <th>Type</th>
              <th>Details</th>
              <th>Priority</th>
              <th>Status</th>
              <th>Date</th>
              <th class="admin-col-actions">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="notification in notifications"
              :key="notification.id"
              :class="!notification.is_read ? 'bg-accent-50/40' : ''"
            >
              <td class="relative whitespace-nowrap">
                <span
                  v-if="!notification.is_read"
                  class="absolute inset-y-0 left-0 w-0.5 rounded-full bg-accent-500"
                  aria-hidden="true"
                ></span>
                <div class="flex items-center gap-3">
                  <div
                    v-if="
                      notification.type === 'business_card_order_request'
                        ? notification.data?.requesting_user_name
                        : notification.user?.full_name
                    "
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-accent-100 text-caption font-bold text-accent-700"
                  >
                    {{
                      (notification.type === "business_card_order_request"
                        ? notification.data?.requesting_user_name ||
                          notification.data?.name ||
                          "?"
                        : notification.user?.full_name ||
                          notification.user?.name ||
                          "?"
                      )
                        .charAt(0)
                        .toUpperCase()
                    }}
                  </div>
                  <div class="min-w-0">
                    <div
                      class="max-w-[160px] truncate text-body font-semibold text-secondary-900"
                    >
                      {{
                        notification.type === "business_card_order_request"
                          ? notification.data?.requesting_user_name ||
                            notification.data?.name ||
                            "—"
                          : notification.user?.full_name ||
                            notification.user?.name ||
                            "System"
                      }}
                    </div>
                    <div
                      class="max-w-[160px] truncate text-caption text-secondary-500"
                    >
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
                      class="text-badge mt-0.5 inline-flex items-center gap-1 text-accent-700"
                    >
                      <Icon name="heroicons:identification" class="h-3 w-3" />
                      {{ notification.data.total_cards }} card{{
                        notification.data.total_cards > 1 ? "s" : ""
                      }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="whitespace-nowrap">
                <AdminStatusBadge
                  :tone="getTypeTone(notification.type)"
                  :label="formatType(notification.type)"
                />
              </td>
              <td class="max-w-[20rem]">
                <div class="flex items-start gap-2">
                  <span
                    v-if="!notification.is_read"
                    class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-accent-500"
                    aria-hidden="true"
                  ></span>
                  <div class="min-w-0">
                    <p
                      class="truncate text-body font-semibold leading-snug text-secondary-900"
                    >
                      {{ notification.title }}
                    </p>
                    <p class="mt-0.5 line-clamp-2 text-caption text-secondary-500">
                      {{ truncateText(notification.message, 80) }}
                    </p>
                  </div>
                </div>
              </td>
              <td class="whitespace-nowrap">
                <AdminStatusBadge
                  :tone="getPriorityTone(notification.priority)"
                  :label="getPriorityLabel(notification.priority)"
                />
              </td>
              <td class="whitespace-nowrap">
                <AdminStatusBadge
                  :tone="notification.is_read ? 'neutral' : 'brass'"
                  :label="notification.is_read ? 'Read' : 'Unread'"
                />
              </td>
              <td class="whitespace-nowrap">
                <div class="text-caption font-semibold text-secondary-700">
                  {{ formatDate(notification.created_at).split(",")[0] }}
                </div>
                <div class="text-micro text-secondary-400">
                  {{
                    formatDate(notification.created_at).split(",")[1]?.trim() ||
                    ""
                  }}
                </div>
              </td>
              <td class="admin-col-actions">
                <div class="inline-flex items-center gap-1">
                  <template
                    v-if="
                      notification.type === 'business_card_order_request' &&
                      !notification.is_approved &&
                      !notification.is_rejected
                    "
                  >
                    <button
                      type="button"
                      title="Approve order"
                      :disabled="approvingId === notification.id"
                      class="inline-flex items-center gap-1 rounded-lg border border-success-200 bg-success-50 px-2.5 py-1 text-badge font-semibold text-success-700 transition hover:bg-success-100 disabled:opacity-50"
                      @click="approveOrder(notification)"
                    >
                      <Icon name="heroicons:check" class="h-3 w-3" />
                      {{ approvingId === notification.id ? "…" : "Approve" }}
                    </button>
                    <button
                      type="button"
                      title="Reject order"
                      class="inline-flex items-center gap-1 rounded-lg border border-error-200 bg-error-50 px-2.5 py-1 text-badge font-semibold text-error-700 transition hover:bg-error-100"
                      @click="openRejectModal(notification)"
                    >
                      <Icon name="heroicons:x-mark" class="h-3 w-3" />
                      Reject
                    </button>
                  </template>
                  <template
                    v-else-if="
                      notification.type === 'business_card_order_request'
                    "
                  >
                    <AdminStatusBadge
                      v-if="notification.is_approved"
                      tone="success"
                      label="Approved"
                    />
                    <AdminStatusBadge
                      v-else-if="notification.is_rejected"
                      tone="danger"
                      label="Rejected"
                    />
                  </template>

                  <span
                    class="mx-1 h-5 w-px bg-secondary-200"
                    aria-hidden="true"
                  ></span>

                  <button
                    type="button"
                    title="View details"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-secondary-500 transition hover:bg-accent-50 hover:text-accent-700"
                    @click="viewDetails(notification)"
                  >
                    <Icon name="heroicons:eye" class="h-4 w-4" />
                  </button>
                  <button
                    type="button"
                    title="View user history"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-secondary-500 transition hover:bg-info-50 hover:text-info-700"
                    @click="viewUserHistory(notification.user)"
                  >
                    <Icon name="heroicons:clock" class="h-4 w-4" />
                  </button>
                  <button
                    type="button"
                    title="Delete"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-secondary-500 transition hover:bg-error-50 hover:text-error-600"
                    @click="deleteNotification(notification.id)"
                  >
                    <Icon name="heroicons:trash" class="h-4 w-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </AdminSectionCard>

    <AdminPagination
      v-if="!loading && paginationData.total > 0"
      :current-page="currentPage"
      :last-page="paginationData.lastPage"
      :per-page="perPage"
      :total="paginationData.total"
      item-label="notifications"
      @page-change="goToPage"
      @per-page-change="changePerPage"
    />

    <!-- Send Announcement dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="showSendModal"
          class="admin-dialog-backdrop"
          @click.self="showSendModal = false"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="Send announcement"
            style="width: min(46rem, 100%); margin-top: 5vh; max-height: 90vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
                >
                  <Icon name="heroicons:megaphone" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">Send announcement</p>
                  <p class="admin-panel-subtitle">
                    Broadcast a message to every NFCGo user.
                  </p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="showSendModal = false"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <form
              class="flex min-h-0 flex-1 flex-col"
              @submit.prevent="sendAnnouncement"
            >
              <div class="admin-panel-body flex-1 space-y-6 overflow-y-auto">
                <!-- Message -->
                <div class="admin-form-section">
                  <p class="admin-form-section-title">Message</p>
                  <p class="admin-form-section-desc">
                    What users will see in their notification feed.
                  </p>
                  <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                      <label class="admin-label">Title</label>
                      <input
                        v-model="announcementForm.title"
                        type="text"
                        class="admin-field"
                        placeholder="Announcement title"
                      />
                    </div>
                    <div class="md:col-span-2">
                      <label class="admin-label">Message</label>
                      <textarea
                        v-model="announcementForm.message"
                        rows="4"
                        class="admin-field"
                        placeholder="Enter your message"
                      ></textarea>
                    </div>
                    <div>
                      <label class="admin-label">Priority</label>
                      <select
                        v-model="announcementForm.priority"
                        class="admin-field"
                      >
                        <option value="low">Low</option>
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Call to action -->
                <div class="admin-form-section">
                  <p class="admin-form-section-title">Call to action</p>
                  <p class="admin-form-section-desc">
                    Optional link and button label attached to the notification.
                  </p>
                  <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                      <label class="admin-label">Action URL</label>
                      <input
                        v-model="announcementForm.action_url"
                        type="url"
                        class="admin-field"
                        placeholder="https://…"
                      />
                    </div>
                    <div>
                      <label class="admin-label">Action text</label>
                      <input
                        v-model="announcementForm.action_text"
                        type="text"
                        class="admin-field"
                        placeholder="Learn more"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <div class="admin-dialog-foot">
                <button
                  type="button"
                  class="admin-btn admin-btn--ghost"
                  @click="showSendModal = false"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="sending"
                  class="admin-btn admin-btn--primary"
                >
                  <Icon
                    v-if="sending"
                    name="heroicons:arrow-path"
                    class="h-4 w-4 animate-spin"
                  />
                  {{ sending ? "Sending…" : "Send to all users" }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Reject Order dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="showRejectModal"
          class="admin-dialog-backdrop"
          @click.self="showRejectModal = false"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="Reject order"
            style="width: min(38rem, 100%); margin-top: 8vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-error-50 text-error-600"
                >
                  <Icon name="heroicons:x-circle" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">Reject order</p>
                  <p class="admin-panel-subtitle">
                    {{
                      selectedNotification?.data?.requesting_user_name ||
                      "The customer will be notified of this decision."
                    }}
                  </p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="showRejectModal = false"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <div class="admin-panel-body flex-1 space-y-6 overflow-y-auto">
              <div class="admin-form-section">
                <p class="admin-form-section-title">Rejection reason</p>
                <p class="admin-form-section-desc">
                  Explain why this order is being declined.
                </p>
                <div class="mt-4">
                  <label class="admin-label">
                    Reason <span class="text-error-600">*</span>
                  </label>
                  <textarea
                    v-model="rejectForm.reason"
                    rows="4"
                    class="admin-field"
                    placeholder="Please provide a reason for rejecting this order…"
                  ></textarea>
                </div>
              </div>
            </div>

            <div class="admin-dialog-foot">
              <button
                type="button"
                class="admin-btn admin-btn--ghost"
                @click="showRejectModal = false"
              >
                Cancel
              </button>
              <button
                type="button"
                :disabled="rejectingId || !rejectForm.reason.trim()"
                class="admin-btn admin-btn--danger"
                @click="submitRejectOrder"
              >
                <Icon
                  v-if="rejectingId"
                  name="heroicons:arrow-path"
                  class="h-4 w-4 animate-spin"
                />
                {{ rejectingId ? "Rejecting…" : "Reject order" }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Notification Details dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="showDetailsModal"
          class="admin-dialog-backdrop"
          @click.self="showDetailsModal = false"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="Notification details"
            style="width: min(52rem, 100%); margin-top: 5vh; max-height: 90vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
                >
                  <Icon name="heroicons:document-text" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">Notification details</p>
                  <p class="admin-panel-subtitle">
                    {{
                      selectedNotificationDetail
                        ? formatType(selectedNotificationDetail.type)
                        : ""
                    }}
                  </p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="showDetailsModal = false"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <div
              v-if="selectedNotificationDetail"
              class="admin-panel-body flex-1 space-y-6 overflow-y-auto"
            >
              <!-- Overview -->
              <div class="admin-form-section">
                <p class="admin-form-section-title">Overview</p>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <p class="text-caption font-medium text-secondary-500">
                      Type
                    </p>
                    <p class="mt-1 text-body font-medium text-secondary-900">
                      {{ formatType(selectedNotificationDetail.type) }}
                    </p>
                  </div>
                  <div>
                    <p class="text-caption font-medium text-secondary-500">
                      Priority
                    </p>
                    <p class="mt-1">
                      <AdminStatusBadge
                        :tone="
                          getPriorityTone(selectedNotificationDetail.priority)
                        "
                        :label="
                          getPriorityLabel(selectedNotificationDetail.priority)
                        "
                      />
                    </p>
                  </div>
                  <div>
                    <p class="text-caption font-medium text-secondary-500">
                      Status
                    </p>
                    <p class="mt-1">
                      <AdminStatusBadge
                        :tone="
                          selectedNotificationDetail.is_read
                            ? 'neutral'
                            : 'brass'
                        "
                        :label="
                          selectedNotificationDetail.is_read
                            ? 'Read'
                            : 'Unread'
                        "
                      />
                    </p>
                  </div>
                  <div>
                    <p class="text-caption font-medium text-secondary-500">
                      Date
                    </p>
                    <p class="mt-1 text-body text-secondary-900">
                      {{ formatDate(selectedNotificationDetail.created_at) }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Message -->
              <div class="admin-form-section">
                <p class="admin-form-section-title">Message</p>
                <p class="mt-3 text-body font-semibold text-secondary-900">
                  {{ selectedNotificationDetail.title }}
                </p>
                <p class="mt-1 text-body leading-relaxed text-secondary-600">
                  {{ selectedNotificationDetail.message }}
                </p>
              </div>

              <!-- Order details -->
              <div
                v-if="
                  selectedNotificationDetail.type ===
                  'business_card_order_request'
                "
                class="admin-form-section"
              >
                <p class="admin-form-section-title">Order details</p>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <div>
                    <p class="text-caption font-medium text-secondary-500">
                      Requesting user
                    </p>
                    <p class="mt-1 text-body font-medium text-secondary-900">
                      {{
                        selectedNotificationDetail.data?.requesting_user_name ||
                        "N/A"
                      }}
                    </p>
                  </div>
                  <div>
                    <p class="text-caption font-medium text-secondary-500">
                      Email
                    </p>
                    <p class="mt-1 text-body text-secondary-900">
                      {{
                        selectedNotificationDetail.data?.requesting_user_email ||
                        "N/A"
                      }}
                    </p>
                  </div>
                  <div>
                    <p class="text-caption font-medium text-secondary-500">
                      Total cards
                    </p>
                    <p class="mt-1 text-body font-medium text-accent-700">
                      {{ selectedNotificationDetail.data?.total_cards || 0 }}
                    </p>
                  </div>
                  <div>
                    <p class="text-caption font-medium text-secondary-500">
                      Plan
                    </p>
                    <p class="mt-1 text-body text-secondary-900">
                      {{
                        selectedNotificationDetail.data?.subscription_plan ||
                        "N/A"
                      }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Cards list -->
              <div
                v-if="
                  selectedNotificationDetail.type ===
                    'business_card_order_request' &&
                  selectedNotificationDetail.data?.cards
                "
                class="admin-form-section"
              >
                <p class="admin-form-section-title">Cards in this order</p>
                <div class="mt-4 space-y-3">
                  <div
                    v-for="(card, index) in selectedNotificationDetail.data
                      .cards"
                    :key="index"
                    class="rounded-xl border border-secondary-200 bg-secondary-50 p-4"
                  >
                    <div class="flex items-center justify-between gap-3">
                      <div class="flex min-w-0 items-center gap-2">
                        <span class="text-caption font-semibold text-secondary-400">
                          #{{ index + 1 }}
                        </span>
                        <p
                          class="truncate text-body font-semibold text-secondary-900"
                        >
                          {{ card.name }}
                        </p>
                      </div>
                      <AdminStatusBadge
                        :tone="card.is_admin_card ? 'brass' : 'info'"
                        :label="card.is_admin_card ? 'Admin' : 'Employee'"
                      />
                    </div>
                    <div
                      class="mt-3 grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2"
                    >
                      <div class="text-caption">
                        <span class="text-secondary-500">Email:</span>
                        <span class="ml-1 text-secondary-800">{{
                          card.email
                        }}</span>
                      </div>
                      <div class="text-caption">
                        <span class="text-secondary-500">Position:</span>
                        <span class="ml-1 text-secondary-800">{{
                          card.position
                        }}</span>
                      </div>
                      <div class="text-caption">
                        <span class="text-secondary-500">Contact:</span>
                        <span class="ml-1 text-secondary-800">{{
                          card.contact_number
                        }}</span>
                      </div>
                      <div v-if="card.website" class="text-caption">
                        <span class="text-secondary-500">Website:</span>
                        <span class="ml-1 text-secondary-800">{{
                          card.website
                        }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Delivery address -->
              <div
                v-if="
                  selectedNotificationDetail.type ===
                    'business_card_order_request' &&
                  selectedNotificationDetail.data?.delivery_address
                "
                class="admin-form-section"
              >
                <p class="admin-form-section-title flex items-center gap-2">
                  <Icon
                    name="heroicons:map-pin"
                    class="h-4 w-4 text-accent-700"
                  />
                  Delivery address
                </p>
                <div
                  class="mt-3 rounded-xl border border-secondary-200 bg-secondary-50 p-4"
                >
                  <p class="text-body text-secondary-800">
                    {{ selectedNotificationDetail.data.delivery_address }}
                  </p>
                </div>
              </div>

              <!-- Decision -->
              <div
                v-if="
                  selectedNotificationDetail.type ===
                    'business_card_order_request' &&
                  (selectedNotificationDetail.is_approved ||
                    selectedNotificationDetail.is_rejected)
                "
                class="admin-form-section"
              >
                <p class="admin-form-section-title">Decision</p>
                <div
                  v-if="selectedNotificationDetail.is_approved"
                  class="mt-3 flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 p-4"
                >
                  <Icon
                    name="heroicons:check-circle"
                    class="mt-0.5 h-5 w-5 shrink-0 text-success-600"
                  />
                  <div>
                    <p class="text-body font-semibold text-success-800">
                      Approved
                    </p>
                    <p class="text-caption text-success-600">
                      {{ formatDate(selectedNotificationDetail.approved_at) }}
                    </p>
                  </div>
                </div>
                <div
                  v-if="selectedNotificationDetail.is_rejected"
                  class="mt-3 flex items-start gap-3 rounded-xl border border-error-200 bg-error-50 p-4"
                >
                  <Icon
                    name="heroicons:x-circle"
                    class="mt-0.5 h-5 w-5 shrink-0 text-error-600"
                  />
                  <div>
                    <p class="text-body font-semibold text-error-800">
                      Rejected
                    </p>
                    <p class="text-caption text-error-600">
                      {{ formatDate(selectedNotificationDetail.rejected_at) }}
                    </p>
                    <p class="mt-1 text-body text-secondary-700">
                      <strong class="font-semibold">Reason:</strong>
                      {{ selectedNotificationDetail.rejection_reason }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <div class="admin-dialog-foot">
              <button
                type="button"
                class="admin-btn admin-btn--outline"
                @click="showDetailsModal = false"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Notification History dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="showHistoryModal"
          class="admin-dialog-backdrop"
          @click.self="showHistoryModal = false"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="Notification history"
            style="width: min(52rem, 100%); margin-top: 5vh; max-height: 90vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
                >
                  <Icon name="heroicons:clock" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">Notification history</p>
                  <p class="admin-panel-subtitle">
                    {{ selectedUser ? selectedUser.full_name : "User activity" }}
                  </p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="showHistoryModal = false"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <div class="admin-panel-body flex-1 overflow-y-auto">
              <div v-if="loadingHistory" class="space-y-4" role="status" aria-busy="true">
                <div v-for="i in 4" :key="i" class="flex gap-4">
                  <span class="admin-skeleton h-10 w-10 shrink-0 rounded-full"></span>
                  <div class="flex-1 space-y-2">
                    <span class="admin-skeleton block h-4 w-1/3"></span>
                    <span class="admin-skeleton block h-3 w-2/3"></span>
                  </div>
                </div>
              </div>

              <AdminEmptyState
                v-else-if="notificationHistory.length === 0"
                icon="heroicons:bell-slash"
                title="No history yet"
                description="This user has no notification records."
              />

              <ol v-else class="space-y-4">
                <li
                  v-for="item in notificationHistory"
                  :key="item.id"
                  class="flex gap-4 rounded-xl border border-secondary-200 bg-white p-4"
                >
                  <span
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full"
                    :class="
                      item.is_read
                        ? 'bg-secondary-100 text-secondary-600'
                        : 'bg-accent-100 text-accent-700'
                    "
                  >
                    <Icon
                      :name="
                        item.is_read
                          ? 'heroicons:envelope-open'
                          : 'heroicons:envelope'
                      "
                      class="h-5 w-5"
                    />
                  </span>
                  <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                      <p class="text-body font-semibold text-secondary-900">
                        {{ item.title }}
                      </p>
                      <AdminStatusBadge
                        :tone="getPriorityTone(item.priority)"
                        :label="getPriorityLabel(item.priority)"
                      />
                    </div>
                    <p class="mt-1 text-body text-secondary-600">
                      {{ item.message }}
                    </p>
                    <div
                      class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-caption text-secondary-500"
                    >
                      <span>{{ formatDate(item.created_at) }}</span>
                      <span v-if="item.read_at">
                        Read: {{ formatDate(item.read_at) }}
                      </span>
                    </div>
                  </div>
                </li>
              </ol>
            </div>

            <div class="admin-dialog-foot">
              <button
                type="button"
                class="admin-btn admin-btn--outline"
                @click="showHistoryModal = false"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Cleanup dialog -->
    <AdminConfirmDialog
      v-model="showCleanupModal"
      title="Cleanup old notifications"
      message="Permanently delete read notifications older than the selected age. This action cannot be undone."
      confirm-label="Delete"
      tone="danger"
      :loading="cleaning"
      @confirm="cleanupNotifications"
    >
      <div class="mt-5">
        <label class="admin-label">Delete read notifications older than</label>
        <select v-model="cleanupDays" class="admin-field">
          <option :value="7">7 days</option>
          <option :value="14">14 days</option>
          <option :value="30">30 days</option>
          <option :value="60">60 days</option>
          <option :value="90">90 days</option>
        </select>
      </div>
    </AdminConfirmDialog>

    <!-- Shared confirmation dialog -->
    <AdminConfirmDialog
      v-model="confirmOpen"
      :title="confirmConfig.title"
      :message="confirmConfig.message"
      :confirm-label="confirmConfig.confirmLabel"
      :tone="confirmConfig.tone"
      :loading="confirmBusy"
      @confirm="runConfirm"
    />
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
const perPage = ref(10);
const paginationData = ref({
  total: 0,
  from: 0,
  to: 0,
  lastPage: 1,
});

// Confirmation dialog state
const confirmOpen = ref(false);
const confirmBusy = ref(false);
const confirmConfig = ref({
  title: "",
  message: "",
  confirmLabel: "Confirm",
  tone: "primary",
});
let confirmAction = null;

const askConfirm = (config, action) => {
  confirmConfig.value = { confirmLabel: "Confirm", tone: "primary", ...config };
  confirmAction = action;
  confirmOpen.value = true;
};

const runConfirm = async () => {
  if (!confirmAction) return;
  confirmBusy.value = true;
  try {
    await confirmAction();
  } finally {
    confirmBusy.value = false;
    confirmOpen.value = false;
    confirmAction = null;
  }
};

const announcementForm = reactive({
  title: "",
  message: "",
  priority: "normal",
  action_url: "",
  action_text: "",
});

const rejectForm = reactive({
  reason: "",
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
    if (
      filterDateRange.value === "custom" &&
      customDateFrom.value &&
      customDateTo.value
    ) {
      params.date_from = customDateFrom.value;
      params.date_to = customDateTo.value;
    }
    
    // Pagination
    params.page = currentPage.value;
    params.per_page = perPage.value;

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

// Handle search/filter changes (reset to first page and reload)
let searchTimer = null;
const handleSearch = () => {
  if (searchTimer) {
    clearTimeout(searchTimer);
    searchTimer = null;
  }
  currentPage.value = 1;
  loadNotifications();
};

// Debounced search input (400ms)
const onSearchInput = () => {
  if (searchTimer) clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    searchTimer = null;
    handleSearch();
  }, 400);
};

// Clear the search box immediately
const clearSearch = () => {
  searchQuery.value = "";
  handleSearch();
};

// Handle per-page change
const changePerPage = (val) => {
  perPage.value = val;
  currentPage.value = 1;
  loadNotifications();
};

// Handle refresh button click
const handleRefresh = async () => {
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

  sending.value = true;
  try {
    const response = await $api.post(
      "/admin/notifications/announcement",
      announcementForm
    );

    if (response.success) {
      $toast.success("Announcement sent successfully");
      showSendModal.value = false;

      // Reset form
      announcementForm.title = "";
      announcementForm.message = "";
      announcementForm.priority = "normal";
      announcementForm.action_url = "";
      announcementForm.action_text = "";

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
const deleteNotification = (id) => {
  askConfirm(
    {
      title: "Delete notification",
      message: "Are you sure you want to delete this notification? This action cannot be undone.",
      confirmLabel: "Delete",
      tone: "danger",
    },
    async () => {
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
    }
  );
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
    if (
      filterDateRange.value === "custom" &&
      customDateFrom.value &&
      customDateTo.value
    ) {
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

    const headers = ["ID", "User", "Email", "Type", "Title", "Message", "Priority", "Status", "Created At"];
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
const TYPE_TONES = {
  admin_announcement: "brass",
  system_message: "info",
  registration_success: "success",
  login_new_device: "danger",
  profile_updated: "info",
  password_changed: "danger",
  payment_successful: "success",
  payment_failed: "danger",
  nfc_card_purchased: "warning",
  nfc_card_activated: "warning",
  business_card_order_request: "brass",
};

const getTypeTone = (type) => TYPE_TONES[type] || "neutral";

const PRIORITY_TONES = {
  low: "neutral",
  normal: "info",
  high: "warning",
  urgent: "danger",
};

const getPriorityTone = (priority) => PRIORITY_TONES[priority] || "neutral";

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
  currentPage.value = 1;
  loadNotifications();
};

// Approve order
const approveOrder = (notification) => {
  // Get order details for confirmation
  const orderData = notification.data || {};
  const totalCards = orderData.total_cards || orderData.cards?.length || 1;
  const employeeCount = orderData.employee_count || 0;

  let confirmMessage = `Approve this order?\n\n`;
  confirmMessage += `Total cards: ${totalCards}\n`;

  if (orderData.include_admin) {
    confirmMessage += `Admin card: 1\n`;
  }

  if (employeeCount > 0) {
    confirmMessage += `Employee cards: ${employeeCount}\n`;
    confirmMessage += `\nThis will automatically:\n`;
    confirmMessage += `• Create ${totalCards} NFC card(s)\n`;
    confirmMessage += `• Create ${employeeCount} employee account(s)\n`;
    confirmMessage += `• Set the default password: Welcome123@\n`;
    confirmMessage += `• Send login credentials to employees`;
  }

  askConfirm(
    {
      title: "Approve order",
      message: confirmMessage,
      confirmLabel: "Approve",
      tone: "primary",
    },
    async () => {
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

          let successMessage = `Order approved — ${cardsCreated} NFC card(s) created.`;

          if (employeesCreated > 0) {
            successMessage += ` ${employeesCreated} employee account(s) created; login credentials emailed.`;
          }

          // Check if any accounts already existed
          const cards = data.cards || [];
          const existingAccounts = cards.filter(
            (c) => c.is_employee_card && !c.employee_account_created
          ).length;

          if (existingAccounts > 0) {
            successMessage += ` ${existingAccounts} employee(s) already had accounts.`;
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
            ? `Order automatically rejected.\n\n`
            : `Cannot approve order.\n\n`;

          errorMessage += `${existingEmps.length} employee email(s) already exist:\n\n`;

          existingEmps.forEach((emp) => {
            errorMessage += `• ${emp.name} (${emp.email})\n`;
          });

          if (isRejected) {
            errorMessage += `\nThe user has been notified with the rejection reason.`;
            errorMessage += `\n\nSolution: the user should remove duplicate employees or use different emails.`;

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
          $toast.error(errorMessage, {
            duration: 5000,
          });
        }
      } finally {
        approvingId.value = null;
      }
    }
  );
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

// Initialize
onMounted(() => {
  loadNotifications();
  loadStatistics();
});

onBeforeUnmount(() => {
  if (searchTimer) clearTimeout(searchTimer);
});
</script>
