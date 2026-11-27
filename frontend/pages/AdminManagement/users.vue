<!-- pages/AdminManagement/users.vue -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="User Directory"
      eyebrow-icon="heroicons:users"
      title="User Management"
      description="Manage every registered account, its subscription plan and access level across NFCGo."
    >
      <template #actions>
        <button
          type="button"
          class="admin-btn admin-btn--primary"
          @click="showCreateModal = true"
        >
          <Icon name="heroicons:plus" class="h-4 w-4" />
          Add user
        </button>
      </template>
    </AdminPageHeader>

    <AdminFilterBar @submit="handleFilterChange">
      <template #search>
        <div>
          <label class="admin-label">Search</label>
          <div class="relative">
            <Icon
              name="heroicons:magnifying-glass"
              class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-secondary-400"
            />
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search users…"
              class="admin-field !pl-9"
              maxlength="150"
              @input="handleSearch"
            />
          </div>
        </div>
      </template>

      <div class="min-w-[12rem]">
        <label class="admin-label">Business plan user</label>
        <select
          v-model="filters.businessUserId"
          class="admin-field"
          @change="handleFilterChange"
        >
          <option value="">Select business user</option>
          <option value="all">All business plan users</option>
          <option v-for="u in businessUserList" :key="u.id" :value="u.id">
            {{ u.first_name }} {{ u.last_name }}
          </option>
        </select>
      </div>

      <div
        v-if="employeeList.length && filters.businessUserId"
        class="min-w-[12rem]"
      >
        <label class="admin-label">Employee account</label>
        <select
          v-model="filters.employeeId"
          class="admin-field"
          @change="handleFilterChange"
        >
          <option value="">All employees</option>
          <option v-for="e in employeeList" :key="e.id" :value="e.id">
            {{ e.first_name }} {{ e.last_name }}
          </option>
        </select>
      </div>

      <div class="min-w-[10rem]">
        <label class="admin-label">Subscription</label>
        <select
          v-model="filters.subscription_plan"
          class="admin-field"
          @change="handleFilterChange"
        >
          <option value="">All plans</option>
          <option value="free">Free</option>
          <option value="basic">Basic</option>
          <option value="premium">Premium</option>
          <option value="business">Business</option>
        </select>
      </div>

      <div class="min-w-[10rem]">
        <label class="admin-label">Status</label>
        <select
          v-model="filters.status"
          class="admin-field"
          @change="handleFilterChange"
        >
          <option value="">All status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="expired">Expired</option>
        </select>
      </div>

      <div class="min-w-[10rem]">
        <label class="admin-label">Role</label>
        <select
          v-model="filters.is_admin"
          class="admin-field"
          @change="handleFilterChange"
        >
          <option value="">All users</option>
          <option value="true">Admin only</option>
          <option value="false">Regular users</option>
        </select>
      </div>

      <template #actions>
        <span class="text-body-sm text-secondary-500">
          {{ pagination.total }} found
        </span>
        <button
          type="button"
          class="admin-btn admin-btn--outline admin-btn--sm"
          @click="clearFilters"
        >
          <Icon name="heroicons:x-mark" class="h-4 w-4" />
          Clear
        </button>
      </template>
    </AdminFilterBar>

    <AdminSectionCard flush>
      <template #title>Registered users</template>
      <template #header-actions>
        <span class="text-caption text-secondary-500">
          {{ pagination.total }} record{{ pagination.total === 1 ? "" : "s" }}
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
          <span class="admin-skeleton h-10 w-10 shrink-0 rounded-full"></span>
          <span class="admin-skeleton h-4 w-1/3"></span>
          <span class="admin-skeleton ml-auto h-4 w-24"></span>
          <span class="admin-skeleton h-4 w-16"></span>
        </div>
      </div>

      <!-- Empty -->
      <AdminEmptyState
        v-else-if="!users.length"
        icon="heroicons:users"
        title="No users found"
        description="Adjust the filters above, or add a new account to get started."
      />

      <!-- Table -->
      <div v-else class="admin-scroll">
        <table class="admin-table">
          <thead>
            <tr>
              <th>User</th>
              <th>Subscription</th>
              <th>Account status</th>
              <th>Last login</th>
              <th class="admin-col-actions">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td>
                <div class="flex items-center gap-3">
                  <UserAvatar
                    :src="user.profile_image"
                    :name="user.full_name"
                    class="h-10 w-10 shrink-0 rounded-full ring-1 ring-secondary-200"
                  />
                  <div class="min-w-0">
                    <div class="flex items-center gap-2">
                      <span class="truncate text-body font-semibold text-secondary-900">
                        {{ user.full_name }}
                      </span>
                      <span
                        v-if="user.is_admin"
                        class="admin-badge admin-badge--warning admin-badge--plain"
                      >
                        {{ user.admin_role_display }}
                      </span>
                    </div>
                    <div class="truncate text-caption text-secondary-500">
                      {{ user.email }}
                    </div>
                    <div
                      v-if="user.company"
                      class="truncate text-micro text-secondary-400"
                    >
                      {{ user.company }}
                    </div>
                  </div>
                </div>
              </td>
              <td>
                <AdminStatusBadge
                  :tone="planTone(user.subscription_plan)"
                  :label="planLabel(user.subscription_plan)"
                />
                <div
                  v-if="user.subscription_end_date"
                  class="text-helper mt-1 text-secondary-500"
                >
                  Expires {{ formatDate(user.subscription_end_date) }}
                </div>
              </td>
              <td>
                <div
                  class="inline-flex rounded-lg border border-secondary-200 bg-secondary-50 p-0.5"
                  role="radiogroup"
                  :aria-label="`Account status for ${user.full_name}`"
                >
                  <label class="cursor-pointer">
                    <input
                      type="radio"
                      class="peer sr-only"
                      :name="`status-${user.id}`"
                      :value="true"
                      v-model="user.subscription_active"
                      @change="requestStatusChange(user, true)"
                    />
                    <span
                      class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-caption font-semibold text-secondary-500 transition peer-checked:bg-white peer-checked:text-success-700 peer-checked:shadow-sm peer-focus-visible:ring-2 peer-focus-visible:ring-accent-400"
                    >
                      <Icon name="heroicons:check-circle" class="h-3.5 w-3.5" />
                      Active
                    </span>
                  </label>
                  <label class="cursor-pointer">
                    <input
                      type="radio"
                      class="peer sr-only"
                      :name="`status-${user.id}`"
                      :value="false"
                      v-model="user.subscription_active"
                      @change="requestStatusChange(user, false)"
                    />
                    <span
                      class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-caption font-semibold text-secondary-500 transition peer-checked:bg-white peer-checked:text-secondary-800 peer-checked:shadow-sm peer-focus-visible:ring-2 peer-focus-visible:ring-accent-400"
                    >
                      <Icon name="heroicons:no-symbol" class="h-3.5 w-3.5" />
                      Inactive
                    </span>
                  </label>
                </div>
                <div
                  v-if="user.has_physical_card"
                  class="text-helper mt-1 text-secondary-500"
                >
                  Physical card
                </div>
              </td>
              <td class="text-secondary-500">
                {{ user.last_login_at ? formatTimeAgo(user.last_login_at) : "Never" }}
              </td>
              <td class="admin-col-actions">
                <div class="inline-flex items-center gap-1.5">
                  <button
                    type="button"
                    title="View details"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-secondary-500 transition hover:bg-secondary-100 hover:text-primary-700"
                    @click="viewUser(user)"
                  >
                    <Icon name="heroicons:eye" class="h-4 w-4" />
                  </button>
                  <button
                    type="button"
                    title="Edit user"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-secondary-500 transition hover:bg-warning-50 hover:text-warning-700"
                    @click="editUser(user)"
                  >
                    <Icon name="heroicons:pencil" class="h-4 w-4" />
                  </button>
                  <button
                    type="button"
                    title="Delete user"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-secondary-500 transition hover:bg-error-50 hover:text-error-600"
                    @click="deleteUser(user)"
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
      v-if="users.length > 0"
      :current-page="pagination.current_page"
      :last-page="pagination.last_page"
      :per-page="pagination.per_page"
      :total="pagination.total"
      item-label="users"
      @page-change="changePage"
      @per-page-change="changeItemsPerPage"
    />

    <!-- Create / Edit User dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="showCreateModal || showEditModal"
          class="admin-dialog-backdrop"
          @click.self="closeModal"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            :aria-label="showEditModal ? 'Edit user' : 'Create new user'"
            style="width: min(46rem, 100%); margin-top: 5vh; max-height: 90vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
                >
                  <Icon
                    :name="
                      showEditModal
                        ? 'heroicons:pencil-square'
                        : 'heroicons:user-plus'
                    "
                    class="h-5 w-5"
                  />
                </span>
                <div>
                  <p class="admin-panel-title">
                    {{ showEditModal ? "Edit user" : "Create new user" }}
                  </p>
                  <p class="admin-panel-subtitle">
                    {{
                      showEditModal
                        ? selectedUser?.email
                        : "Add a new account to the platform"
                    }}
                  </p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="closeModal"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <form
              class="flex min-h-0 flex-1 flex-col"
              @submit.prevent="handleSubmit"
            >
              <div class="admin-panel-body flex-1 space-y-6 overflow-y-auto">
                <!-- Identity -->
                <div class="admin-form-section">
                  <p class="admin-form-section-title">Identity</p>
                  <p class="admin-form-section-desc">
                    Core account details the user signs in with.
                  </p>
                  <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                      <label class="admin-label">
                        First name <span class="text-error-600">*</span>
                      </label>
                      <input
                        v-model="form.first_name"
                        type="text"
                        required
                        maxlength="100"
                        class="admin-field"
                        :class="formErrors.first_name ? '!border-error-400' : ''"
                        @input="validateFirstName"
                      />
                      <div class="mt-1 flex justify-between">
                        <span
                          v-if="formErrors.first_name"
                          class="text-helper text-error-600"
                        >
                          {{ formErrors.first_name }}
                        </span>
                        <span v-else class="text-helper"></span>
                        <span class="text-helper text-secondary-400">
                          {{ form.first_name.length }}/100
                        </span>
                      </div>
                    </div>
                    <div>
                      <label class="admin-label">
                        Last name <span class="text-error-600">*</span>
                      </label>
                      <input
                        v-model="form.last_name"
                        type="text"
                        required
                        maxlength="100"
                        class="admin-field"
                        :class="formErrors.last_name ? '!border-error-400' : ''"
                        @input="validateLastName"
                      />
                      <div class="mt-1 flex justify-between">
                        <span
                          v-if="formErrors.last_name"
                          class="text-helper text-error-600"
                        >
                          {{ formErrors.last_name }}
                        </span>
                        <span v-else class="text-helper"></span>
                        <span class="text-helper text-secondary-400">
                          {{ form.last_name.length }}/100
                        </span>
                      </div>
                    </div>
                    <div>
                      <label class="admin-label">
                        Email <span class="text-error-600">*</span>
                      </label>
                      <input
                        v-model="form.email"
                        type="email"
                        required
                        maxlength="100"
                        class="admin-field"
                        :class="formErrors.email ? '!border-error-400' : ''"
                        @input="validateEmail"
                      />
                      <div class="mt-1 flex justify-between">
                        <span
                          v-if="formErrors.email"
                          class="text-helper text-error-600"
                        >
                          {{ formErrors.email }}
                        </span>
                        <span v-else class="text-helper"></span>
                        <span class="text-helper text-secondary-400">
                          {{ form.email.length }}/100
                        </span>
                      </div>
                    </div>
                    <div>
                      <label class="admin-label">Phone</label>
                      <PhoneInput
                        v-model="form.phone"
                        :has-error="!!formErrors.phone"
                        placeholder="Phone number"
                      />
                      <p
                        v-if="formErrors.phone"
                        class="text-helper mt-1 text-error-600"
                      >
                        {{ formErrors.phone }}
                      </p>
                    </div>
                    <div>
                      <label class="admin-label">Company</label>
                      <input
                        v-model="form.company"
                        type="text"
                        maxlength="150"
                        class="admin-field"
                      />
                      <span
                        class="text-helper mt-1 block text-right text-secondary-400"
                      >
                        {{ (form.company || "").length }}/150
                      </span>
                    </div>
                    <div>
                      <label class="admin-label">Job title</label>
                      <input
                        v-model="form.job_title"
                        type="text"
                        maxlength="30"
                        class="admin-field"
                      />
                      <span
                        class="text-helper mt-1 block text-right text-secondary-400"
                      >
                        {{ (form.job_title || "").length }}/30
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Credentials -->
                <div v-if="!showEditModal" class="admin-form-section">
                  <p class="admin-form-section-title">Credentials</p>
                  <p class="admin-form-section-desc">
                    Set an initial password. The user can change it later.
                  </p>
                  <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                      <label class="admin-label">
                        Password <span class="text-error-600">*</span>
                      </label>
                      <div class="relative">
                        <input
                          v-model="form.password"
                          :type="showPassword ? 'text' : 'password'"
                          required
                          class="admin-field pr-10"
                        />
                        <button
                          type="button"
                          class="absolute inset-y-0 right-0 flex items-center pr-3 text-secondary-400 hover:text-secondary-600"
                          @click="showPassword = !showPassword"
                        >
                          <Icon
                            :name="
                              showPassword ? 'heroicons:eye-slash' : 'heroicons:eye'
                            "
                            class="h-5 w-5"
                          />
                        </button>
                      </div>
                    </div>
                    <div>
                      <label class="admin-label">
                        Confirm password <span class="text-error-600">*</span>
                      </label>
                      <div class="relative">
                        <input
                          v-model="form.password_confirmation"
                          :type="showConfirmPassword ? 'text' : 'password'"
                          required
                          class="admin-field pr-10"
                        />
                        <button
                          type="button"
                          class="absolute inset-y-0 right-0 flex items-center pr-3 text-secondary-400 hover:text-secondary-600"
                          @click="showConfirmPassword = !showConfirmPassword"
                        >
                          <Icon
                            :name="
                              showConfirmPassword
                                ? 'heroicons:eye-slash'
                                : 'heroicons:eye'
                            "
                            class="h-5 w-5"
                          />
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Plan & status -->
                <div class="admin-form-section">
                  <p class="admin-form-section-title">Plan &amp; status</p>
                  <p class="admin-form-section-desc">
                    Subscription tier and whether the account can sign in.
                  </p>
                  <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                      <label class="admin-label">Subscription plan</label>
                      <select v-model="form.subscription_plan" class="admin-field">
                        <option value="free">Free</option>
                        <option value="basic">Basic</option>
                        <option value="premium">Premium</option>
                        <option value="business">Business</option>
                      </select>
                    </div>
                    <div>
                      <label class="admin-label">Account status</label>
                      <select v-model="form.subscription_active" class="admin-field">
                        <option :value="true">Active</option>
                        <option :value="false">Inactive</option>
                      </select>
                      <p class="admin-hint">
                        Inactive accounts cannot log in or use the system.
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Business quota -->
                <div
                  v-if="form.subscription_plan === 'business'"
                  class="admin-form-section"
                >
                  <p class="admin-form-section-title">Business plan quota</p>
                  <p class="admin-form-section-desc">
                    A single unified quota governs both accounts and NFC cards.
                  </p>

                  <div
                    v-if="showEditModal && selectedUser?.quota_info"
                    class="mt-4 rounded-xl border border-info-200 bg-info-50 p-4"
                  >
                    <div class="flex items-start gap-3">
                      <Icon
                        name="heroicons:information-circle"
                        class="mt-0.5 h-5 w-5 shrink-0 text-info-600"
                      />
                      <div class="flex-1">
                        <p class="text-body font-semibold text-info-900">
                          Current quota usage
                        </p>
                        <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-3">
                          <div>
                            <p class="text-caption font-medium text-info-700">
                              Total accounts
                            </p>
                            <p class="text-body text-info-900">
                              {{
                                selectedUser.quota_info.total_accounts ||
                                1 + selectedUser.quota_info.employees_count
                              }}
                              /
                              {{
                                selectedUser.quota_info.total_quota ||
                                selectedUser.quota_info.total_account_slots
                              }}
                              <span class="text-caption text-info-500">
                                (1 owner + {{ selectedUser.quota_info.employees_count }}
                                employees)
                              </span>
                            </p>
                          </div>
                          <div>
                            <p class="text-caption font-medium text-info-700">
                              NFC cards ordered
                            </p>
                            <p class="text-body text-info-900">
                              {{ selectedUser.quota_info.ordered_cards_count }} /
                              {{
                                selectedUser.quota_info.total_quota ||
                                selectedUser.quota_info.total_card_quota
                              }}
                            </p>
                          </div>
                          <div>
                            <p class="text-caption font-medium text-info-700">
                              Available quota
                            </p>
                            <p class="text-h5 text-info-900">
                              {{
                                selectedUser.quota_info.available_quota ||
                                Math.min(
                                  selectedUser.quota_info.available_account_slots || 0,
                                  selectedUser.quota_info.available_card_quota || 0
                                )
                              }}
                            </p>
                          </div>
                        </div>
                        <p class="text-caption mt-3 text-info-600">
                          Cannot set quota below max(total accounts, ordered cards).
                        </p>
                      </div>
                    </div>
                  </div>

                  <div class="mt-4">
                    <label class="admin-label">
                      Total business quota <span class="text-error-600">*</span>
                    </label>
                    <input
                      v-model.number="form.total_account_slots"
                      type="number"
                      :min="getMinimumQuota()"
                      required
                      class="admin-field"
                      placeholder="e.g. 50"
                    />
                    <p class="admin-hint">
                      Maximum number of accounts (including owner) AND NFC cards
                      this business can hold.
                    </p>
                    <p
                      v-if="showEditModal && selectedUser?.quota_info"
                      class="text-helper mt-1 text-warning-600"
                    >
                      Minimum: {{ getMinimumQuota() }} (based on current usage)
                    </p>
                  </div>

                  <div class="mt-3 rounded-xl border border-info-200 bg-info-50 p-4">
                    <div class="flex gap-3">
                      <Icon
                        name="heroicons:information-circle"
                        class="mt-0.5 h-5 w-5 shrink-0 text-info-600"
                      />
                      <div>
                        <p class="text-body font-semibold text-info-800">
                          Unified quota system
                        </p>
                        <ul class="text-body mt-1 space-y-1 text-info-700">
                          <li>• Quota applies to both accounts AND cards.</li>
                          <li>
                            • If quota = 10: max 10 accounts (1 owner + 9 employees)
                            AND max 10 cards.
                          </li>
                          <li>
                            • Creating accounts doesn't consume quota until they
                            order cards.
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Admin access (super admin only) -->
                <div v-if="isSuperAdmin" class="admin-form-section">
                  <p class="admin-form-section-title">Admin access</p>
                  <p class="admin-form-section-desc">
                    Grant this account access to the admin panel and scope its permissions.
                  </p>
                  <label class="mt-4 flex cursor-pointer items-center justify-between rounded-xl border border-secondary-200 bg-secondary-50 p-4">
                    <div>
                      <p class="text-body font-medium text-secondary-900">Administrator account</p>
                      <p class="text-caption text-secondary-500">
                        Enable to grant admin-panel access.
                      </p>
                    </div>
                    <button
                      type="button"
                      role="switch"
                      :aria-checked="form.is_admin"
                      :class="[
                        'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors',
                        form.is_admin ? 'bg-accent-600' : 'bg-secondary-300',
                      ]"
                      @click="toggleAdminAccess"
                    >
                      <span
                        :class="[
                          'inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform',
                          form.is_admin ? 'translate-x-5' : 'translate-x-0.5',
                        ]"
                      />
                    </button>
                  </label>

                  <div v-if="form.is_admin" class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                      <label class="admin-label">Admin role</label>
                      <select v-model="form.admin_role" class="admin-field">
                        <option value="admin">Admin</option>
                        <option value="moderator">Moderator</option>
                      </select>
                    </div>
                    <div>
                      <label class="admin-label">Admin permissions</label>
                      <div class="mt-2 max-h-64 space-y-3 overflow-y-auto pr-1">
                        <div
                          v-for="(domain, domainKey) in permissionCatalogue"
                          :key="domainKey"
                          class="rounded-lg border border-secondary-200 p-3"
                        >
                          <p class="mb-2 text-caption font-semibold uppercase tracking-wide text-secondary-500">
                            {{ domain.label }}
                          </p>
                          <div class="space-y-1.5">
                            <label
                              v-for="(label, permKey) in domain.permissions"
                              :key="permKey"
                              class="flex cursor-pointer items-start gap-2 text-body text-secondary-700"
                            >
                              <input
                                v-model="form.admin_permissions"
                                type="checkbox"
                                :value="permKey"
                                class="mt-0.5 rounded border-secondary-300 text-accent-600 focus:ring-accent-500"
                              />
                              <span>{{ label }}</span>
                            </label>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="admin-dialog-foot">
                <button
                  type="button"
                  class="admin-btn admin-btn--ghost"
                  @click="closeModal"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  :disabled="submitting"
                  class="admin-btn admin-btn--primary"
                >
                  <Icon
                    v-if="submitting"
                    name="heroicons:arrow-path"
                    class="h-4 w-4 animate-spin"
                  />
                  {{ showEditModal ? "Update user" : "Create user" }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- User details dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="showUserModal"
          class="admin-dialog-backdrop"
          @click.self="showUserModal = false"
        >
          <div
            class="admin-dialog flex max-h-[90vh] flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="User details"
            style="width: min(42rem, 100%); margin-top: 8vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
                >
                  <Icon name="heroicons:identification" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">User details</p>
                  <p class="admin-panel-subtitle">
                    {{ selectedUser?.email }}
                  </p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="showUserModal = false"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <div v-if="selectedUser" class="admin-panel-body overflow-y-auto">
              <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                <div>
                  <p class="admin-form-section-title">Basic information</p>
                  <div class="mt-4 flex items-center gap-3">
                    <UserAvatar
                      :src="selectedUser.profile_image"
                      :name="selectedUser.full_name"
                      class="h-12 w-12 rounded-full ring-1 ring-secondary-200"
                    />
                    <div>
                      <p class="text-body font-semibold text-secondary-900">
                        {{ selectedUser.full_name }}
                      </p>
                      <p class="text-caption text-secondary-500">
                        {{ selectedUser.email }}
                      </p>
                    </div>
                  </div>
                  <dl class="mt-4 space-y-3">
                    <div>
                      <dt class="text-caption font-medium text-secondary-500">
                        Company
                      </dt>
                      <dd class="text-body text-secondary-900">
                        {{ selectedUser.company || "N/A" }}
                      </dd>
                    </div>
                    <div>
                      <dt class="text-caption font-medium text-secondary-500">
                        Job title
                      </dt>
                      <dd class="text-body text-secondary-900">
                        {{ selectedUser.job_title || "N/A" }}
                      </dd>
                    </div>
                  </dl>
                </div>
                <div>
                  <p class="admin-form-section-title">Account details</p>
                  <dl class="mt-4 space-y-3">
                    <div>
                      <dt class="text-caption font-medium text-secondary-500">
                        Subscription plan
                      </dt>
                      <dd class="mt-1">
                        <AdminStatusBadge
                          :tone="planTone(selectedUser.subscription_plan)"
                          :label="planLabel(selectedUser.subscription_plan)"
                        />
                      </dd>
                    </div>
                    <div>
                      <dt class="text-caption font-medium text-secondary-500">
                        Status
                      </dt>
                      <dd class="mt-1">
                        <AdminStatusBadge
                          :tone="
                            selectedUser.subscription_active ? 'success' : 'danger'
                          "
                          :label="
                            selectedUser.subscription_active ? 'Active' : 'Inactive'
                          "
                        />
                      </dd>
                    </div>
                    <div>
                      <dt class="text-caption font-medium text-secondary-500">
                        Last login
                      </dt>
                      <dd class="text-body text-secondary-900">
                        {{
                          selectedUser.last_login_at
                            ? formatTimeAgo(selectedUser.last_login_at)
                            : "Never"
                        }}
                      </dd>
                    </div>
                    <div>
                      <dt class="text-caption font-medium text-secondary-500">
                        Member since
                      </dt>
                      <dd class="text-body text-secondary-900">
                        {{ formatDate(selectedUser.created_at) }}
                      </dd>
                    </div>
                  </dl>
                </div>
              </div>
            </div>

            <div class="admin-dialog-foot">
              <button
                type="button"
                class="admin-btn admin-btn--outline"
                @click="showUserModal = false"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Shared confirmation dialog -->
    <AdminConfirmDialog
      v-model="confirmOpen"
      :title="confirmConfig.title"
      :message="confirmConfig.message"
      :confirm-label="confirmConfig.confirmLabel"
      :tone="confirmConfig.tone"
      :loading="confirmBusy"
      @confirm="runConfirm"
      @cancel="cancelConfirm"
    />
  </div>
</template>

<script setup>
definePageMeta({
  layout: "admin-management",
  middleware: "admin",
});

const adminStore = useAdminStore();
const authStore = useAuthStore();
const { users, loading, pagination } = storeToRefs(adminStore);

const isSuperAdmin = computed(() => authStore.isSuperAdmin());
const permissionCatalogue = ref({});

// Modal states
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showUserModal = ref(false);
const selectedUser = ref(null);
const submitting = ref(false);

// Password visibility toggles
const showPassword = ref(false);
const showConfirmPassword = ref(false);

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
let confirmCancelAction = null;

const askConfirm = (config, action, onCancel = null) => {
  confirmConfig.value = { confirmLabel: "Confirm", tone: "primary", ...config };
  confirmAction = action;
  confirmCancelAction = onCancel;
  confirmOpen.value = true;
};

const cancelConfirm = () => {
  const handler = confirmCancelAction;
  confirmCancelAction = null;
  if (handler) handler();
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
    confirmCancelAction = null;
  }
};

// Form data
const form = ref({
  first_name: "",
  last_name: "",
  email: "",
  phone: "",
  company: "",
  job_title: "",
  password: "",
  password_confirmation: "",
  subscription_plan: "free",
  subscription_active: true,
  subscription_end_date: "",
  is_admin: false,
  admin_role: "admin",
  admin_permissions: [],
  total_account_slots: 10, // Default for Business plan
  total_card_quota: 10, // Default for Business plan
});

// Form validation errors
const formErrors = ref({
  first_name: "",
  last_name: "",
  email: "",
  phone: "",
});

// Filters
const filters = ref({
  search: "",
  subscription_plan: "",
  status: "",
  is_admin: "",
  businessUserId: "",
  employeeId: "",
});

const businessUserList = ref([]);
const employeeList = ref([]);
const itemsPerPage = ref(15);
onMounted(async () => {
  await adminStore.fetchUsers();
  const { $api } = useNuxtApp();
  const res = await $api.get("/admin/business-users");
  businessUserList.value = res.data;

  // Fetch permission catalogue for the admin-access section (super admin only).
  try {
    const rolesRes = await $api.get("/admin/roles");
    if (rolesRes.success) {
      permissionCatalogue.value = rolesRes.data.permission_catalogue || {};
    }
  } catch (e) {
    console.error("Failed to load permission catalogue:", e);
  }

  // Deep-link: open the user detail modal when ?open={userId} is present
  const route = useRoute();
  const openId = route.query.open;
  if (openId) {
    const target = adminStore.users.find((u) => String(u.id) === String(openId));
    if (target) {
      viewUser(target);
    }
  }
});

const handleCompanyChange = async () => {
  filters.value.employeeId = "";
  if (!filters.value.businessUserId) {
    employeeList.value = [];
    handleFilterChange();
    return;
  }
  const { $api } = useNuxtApp();
  const res = await $api.get(
    `/admin/business-users/${filters.value.businessUserId}`
  );
  employeeList.value = res.data.user.employees || [];
  // Immediately trigger filter after company selection
  adminStore.updateFilters(filters.value);
  adminStore.fetchUsers();
};
// Handle search with debounce
let searchTimeout;
const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    adminStore.updateFilters({ search: filters.value.search });
    adminStore.fetchUsers();
  }, 500);
};

// Handle filter changes
const handleFilterChange = () => {
  adminStore.updateFilters(filters.value);
  adminStore.fetchUsers();
};

// Clear all filters
const clearFilters = () => {
  adminStore.clearFilters();
  filters.value = {
    search: "",
    subscription_plan: "",
    status: "",
    is_admin: "",
    businessUserId: "",
    employeeId: "",
  };
  employeeList.value = [];
  adminStore.fetchUsers();
};

// Change page
const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    adminStore.fetchUsers({ page });
  }
};

// Change items per page
const changeItemsPerPage = () => {
  adminStore.fetchUsers({ page: 1, per_page: itemsPerPage.value });
};

// Compute visible page numbers (show max 5 pages at a time)
const visiblePages = computed(() => {
  const total = pagination.value.last_page || 1;
  const current = pagination.value.current_page || 1;
  const pages = [];

  let start = Math.max(1, current - 2);
  let end = Math.min(total, start + 4);

  // Adjust start if we're near the end
  if (end - start < 4) {
    start = Math.max(1, end - 4);
  }

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }

  return pages;
});

// View user details
const viewUser = (user) => {
  selectedUser.value = user;
  showUserModal.value = true;
};

// Edit user
const editUser = (user) => {
  selectedUser.value = user;
  form.value = {
    first_name: user.first_name,
    last_name: user.last_name,
    email: user.email,
    phone: user.phone || "",
    company: user.company || "",
    job_title: user.job_title || "",
    password: "",
    password_confirmation: "",
    subscription_plan: user.subscription_plan,
    subscription_active: user.subscription_active !== false, // Default to true if undefined
    subscription_end_date: user.subscription_end_date
      ? user.subscription_end_date.split("T")[0]
      : "",
    is_admin: user.is_admin,
    admin_role: user.admin_role || "admin",
    admin_permissions: user.admin_permissions || [],
    total_account_slots: user.total_account_slots || 10,
  };
  showEditModal.value = true;
};

// Toggle admin access (clears permissions when disabled)
const toggleAdminAccess = () => {
  form.value.is_admin = !form.value.is_admin;
  if (!form.value.is_admin) {
    form.value.admin_permissions = [];
    form.value.admin_role = "admin";
  }
};

// Create new user
const createUser = () => {
  form.value = {
    first_name: "",
    last_name: "",
    email: "",
    phone: "",
    company: "",
    job_title: "",
    password: "",
    password_confirmation: "",
    subscription_plan: "free",
    is_admin: false,
    admin_role: "admin",
    admin_permissions: [],
  };
  showCreateModal.value = true;
};

// Form validation functions
const NAME_PATTERN = /^[a-zA-Z\s\-']+$/;
const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const PHONE_PATTERN = /^\d*$/;

const validateFirstName = () => {
  const value = form.value.first_name.trim();
  if (!value) {
    formErrors.value.first_name = "First name is required";
  } else if (!NAME_PATTERN.test(value)) {
    formErrors.value.first_name = "Only letters, spaces, hyphens, apostrophes allowed";
  } else {
    formErrors.value.first_name = "";
  }
};

const validateLastName = () => {
  const value = form.value.last_name.trim();
  if (!value) {
    formErrors.value.last_name = "Last name is required";
  } else if (!NAME_PATTERN.test(value)) {
    formErrors.value.last_name = "Only letters, spaces, hyphens, apostrophes allowed";
  } else {
    formErrors.value.last_name = "";
  }
};

const validateEmail = () => {
  const value = form.value.email.trim();
  if (!value) {
    formErrors.value.email = "Email is required";
  } else if (value.length < 5) {
    formErrors.value.email = "Email must be at least 5 characters";
  } else if (!EMAIL_PATTERN.test(value)) {
    formErrors.value.email = "Invalid email format";
  } else {
    formErrors.value.email = "";
  }
};

// Phone number formatting composable
const { formatPhoneNumber } = usePhoneFormat();

const validatePhone = () => {
  const value = form.value.phone;
  // Allow digits, +, spaces, and dashes for international format
  const cleaned = value.replace(/[^\d\s\-+]/g, '');
  if (value !== cleaned) {
    form.value.phone = cleaned;
  }
  if (cleaned.length > 16) {
    formErrors.value.phone = "Maximum 16 characters allowed";
  } else {
    formErrors.value.phone = "";
  }
};

// Format phone number on blur
const formatPhoneOnBlur = () => {
  if (form.value.phone) {
    form.value.phone = formatPhoneNumber(form.value.phone);
  }
};

const validateAllFields = () => {
  validateFirstName();
  validateLastName();
  validateEmail();
  validatePhone();

  return !formErrors.value.first_name &&
         !formErrors.value.last_name &&
         !formErrors.value.email &&
         !formErrors.value.phone;
};

const clearFormErrors = () => {
  formErrors.value = {
    first_name: "",
    last_name: "",
    email: "",
    phone: "",
  };
};

// Handle form submission
const handleSubmit = async () => {
  // Validate all fields first
  if (!validateAllFields()) {
    const { $toast } = useNuxtApp();
    $toast.error("Please fix the validation errors before submitting.");
    return;
  }

  // Skip password confirmation check on EDIT mode if both password fields are empty
  // (admin may only want to change plan or other fields, not password)
  const isEditWithEmptyPassword =
    showEditModal.value &&
    (!form.value.password || form.value.password.trim() === "") &&
    (!form.value.password_confirmation || form.value.password_confirmation.trim() === "");

  if (!isEditWithEmptyPassword) {
    if (form.value.password !== form.value.password_confirmation) {
      const { $toast } = useNuxtApp();
      $toast.error("Passwords do not match");
      return;
    }
    if (!showEditModal.value && (!form.value.password || form.value.password.length < 8)) {
      const { $toast } = useNuxtApp();
      $toast.error("Password must be at least 8 characters");
      return;
    }
  }

  submitting.value = true;
  try {
    // Build clean payload
    const payload = { ...form.value };
    // On edit mode with empty password, remove password fields entirely so backend ignores them
    if (isEditWithEmptyPassword) {
      delete payload.password;
      delete payload.password_confirmation;
    }
    // Remove undefined/null fields to avoid backend overwriting with null
    Object.keys(payload).forEach(key => {
      if (payload[key] === undefined || payload[key] === null) delete payload[key];
    });

    // Non-super-admins may never touch admin privileges (backend enforces this too).
    if (!isSuperAdmin.value) {
      delete payload.is_admin;
      delete payload.admin_role;
      delete payload.admin_permissions;
    }

    if (showEditModal.value) {
      await adminStore.updateUser(selectedUser.value.id, payload);
    } else {
      await adminStore.createUser(payload);
    }
    const { $toast } = useNuxtApp();
    $toast.success(showEditModal.value ? "User updated successfully" : "User created successfully");
    closeModal();
  } catch (error) {
    const { $toast } = useNuxtApp();
    console.error("Failed to save user:", error);
    const serverMsg =
      error?.data?.message ||
      error?.message ||
      (typeof error?.response?.data === "string" ? error.response.data : null) ||
      (error?.response?.data?.message ? error.response.data.message : null) ||
      "Failed to save user. Please try again.";
    // Collect validation errors if any
    let detailMsg = serverMsg;
    const errors = error?.data?.errors || error?.response?.data?.errors;
    if (errors && typeof errors === "object") {
      const firstField = Object.keys(errors)[0];
      if (firstField && errors[firstField] && Array.isArray(errors[firstField])) {
        detailMsg = `${serverMsg}: ${errors[firstField][0]}`;
      } else if (firstField && typeof errors[firstField] === "string") {
        detailMsg = `${serverMsg}: ${errors[firstField]}`;
      }
    }
    $toast.error(detailMsg);
  } finally {
    submitting.value = false;
  }
};

// Close modal
const closeModal = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  showUserModal.value = false;
  selectedUser.value = null;
  clearFormErrors(); // Reset validation errors
  form.value = {
    first_name: "",
    last_name: "",
    email: "",
    phone: "",
    company: "",
    job_title: "",
    password: "",
    password_confirmation: "",
    subscription_plan: "free",
    subscription_active: true,
    subscription_end_date: "",
    is_admin: false,
    admin_role: "admin",
    admin_permissions: [],
    total_account_slots: 10,
  };
};

// Delete user
const deleteUser = (user) => {
  askConfirm(
    {
      title: "Delete user",
      message: `Are you sure you want to delete ${user.full_name}? This action cannot be undone.`,
      confirmLabel: "Delete",
      tone: "danger",
    },
    async () => {
      try {
        await adminStore.deleteUser(user.id);
      } catch (error) {
        console.error("Failed to delete user:", error);
        const { $toast } = useNuxtApp();
        $toast.error("Failed to delete user. Please try again.");
      }
    }
  );
};
// Set user account status from the status radio toggle (activate/deactivate)
const requestStatusChange = (user, isActive) => {
  const previousStatus = !isActive;

  askConfirm(
    {
      title: `${isActive ? "Activate" : "Deactivate"} account`,
      message: `Are you sure you want to ${
        isActive ? "activate" : "deactivate"
      } ${user.full_name}'s account?${
        isActive ? "" : " Deactivated users cannot log in or use the system."
      }`,
      confirmLabel: isActive ? "Activate" : "Deactivate",
      tone: isActive ? "primary" : "danger",
    },
    async () => {
      try {
        await adminStore.updateUser(user.id, {
          subscription_active: isActive,
        });

        const { $toast } = useNuxtApp();
        $toast.success(
          `${user.full_name}'s account has been ${
            isActive ? "activated" : "deactivated"
          }.`
        );
      } catch (error) {
        console.error("Failed to update user status:", error);
        user.subscription_active = previousStatus;
        const { $toast } = useNuxtApp();
        $toast.error("Failed to update user status. Please try again.");
      }
    },
    () => {
      // Revert the toggle when the confirmation is dismissed.
      user.subscription_active = previousStatus;
    }
  );
};

// Helper functions
const getMinimumQuota = () => {
  if (!selectedUser.value?.quota_info) return 1;

  const totalAccounts =
    selectedUser.value.quota_info.total_accounts ||
    1 + (selectedUser.value.quota_info.employees_count || 0);
  const orderedCards = selectedUser.value.quota_info.ordered_cards_count || 0;

  return Math.max(totalAccounts, orderedCards);
};

const PLAN_TONES = {
  free: "neutral",
  basic: "info",
  premium: "brass",
  business: "success",
};

const planTone = (plan) => PLAN_TONES[plan] || "neutral";

const planLabel = (plan) =>
  String(plan || "").replace(/\b\w/g, (c) => c.toUpperCase());

const formatTimeAgo = (timestamp) => {
  if (!timestamp) return "";
  const date = new Date(timestamp);
  const now = new Date();
  const diffInMinutes = Math.floor((now - date) / (1000 * 60));

  if (diffInMinutes < 1) return "Just now";
  if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
  if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h ago`;
  return `${Math.floor(diffInMinutes / 1440)}d ago`;
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  return new Date(dateString).toLocaleDateString();
};
</script>
