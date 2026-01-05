<!-- pages/AdminManagement/users.vue -->
<template>
  <div>
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-3xl font-bold text-secondary-900">User Management</h1>
          <p class="mt-2 text-secondary-600">
            Manage all registered users and their accounts
          </p>
        </div>
        <div class="mt-4 sm:mt-0">
          <button @click="showCreateModal = true" class="btn btn-primary">
            <Icon name="heroicons:plus" class="h-5 w-5 mr-2" />
            Add User
          </button>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Search</label
            >
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search users..."
              class="input"
              maxlength="150"
              @input="handleSearch"
            />
            <p class="text-xs text-secondary-500 mt-1">{{ filters.search.length }}/150</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Business Plan User</label
            >
            <select
              v-model="filters.businessUserId"
              @change="handleFilterChange"
              class="input"
            >
              <option value="">Select Business User</option>
              <option value="all">All Business Plan Users</option>
              <option v-for="u in businessUserList" :key="u.id" :value="u.id">
                {{ u.first_name }} {{ u.last_name }}
              </option>
            </select>
          </div>
          <div v-if="employeeList.length && filters.businessUserId">
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Employee Account</label
            >
            <select
              v-model="filters.employeeId"
              @change="handleFilterChange"
              class="input"
            >
              <option value="">All Employees</option>
              <option v-for="e in employeeList" :key="e.id" :value="e.id">
                {{ e.first_name }} {{ e.last_name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Subscription Plan</label
            >
            <select
              v-model="filters.subscription_plan"
              @change="handleFilterChange"
              class="input"
            >
              <option value="">All Plans</option>
              <option value="free">Free</option>
              <option value="basic">Basic</option>
              <option value="premium">Premium</option>
              <option value="business">Business</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Status</label
            >
            <select
              v-model="filters.status"
              @change="handleFilterChange"
              class="input"
            >
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="expired">Expired</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Admin Status</label
            >
            <select
              v-model="filters.is_admin"
              @change="handleFilterChange"
              class="input"
            >
              <option value="">All Users</option>
              <option value="true">Admin Only</option>
              <option value="false">Regular Users</option>
            </select>
          </div>
        </div>
        <div class="mt-4 flex justify-between items-center">
          <button @click="clearFilters" class="btn btn-outline btn-sm">
            Clear Filters
          </button>
          <div class="text-sm text-secondary-500">
            {{ pagination.total }} users found
          </div>
        </div>
      </div>
    </div>

    <!-- Users Table -->
    <div class="card">
      <div class="card-body p-0">
        <div v-if="loading" class="flex justify-center py-12">
          <div class="spinner"></div>
        </div>
        <div v-else-if="users.length" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-secondary-200">
            <thead class="bg-secondary-50">
              <tr>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  User
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  Subscription
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  Status
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  Last Login
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-secondary-200">
              <tr
                v-for="user in users"
                :key="user.id"
                class="hover:bg-secondary-50"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <img
                      :src="
                        user.profile?.profile_image || '/default-avatar.png'
                      "
                      :alt="user.full_name"
                      class="h-10 w-10 rounded-full object-cover"
                    />
                    <div class="ml-4">
                      <div class="text-sm font-medium text-secondary-900">
                        {{ user.full_name }}
                        <span
                          v-if="user.is_admin"
                          class="ml-2 px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full"
                        >
                          {{ user.admin_role_display }}
                        </span>
                      </div>
                      <div class="text-sm text-secondary-500">
                        {{ user.email }}
                      </div>
                      <div class="text-xs text-secondary-400">
                        {{ user.company }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'px-2 py-1 text-xs font-medium rounded-full',
                      getSubscriptionBadgeClass(user.subscription_plan),
                    ]"
                  >
                    {{ user.subscription_plan }}
                  </span>
                  <div
                    v-if="user.subscription_end_date"
                    class="text-xs text-secondary-500 mt-1"
                  >
                    Expires: {{ formatDate(user.subscription_end_date) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'px-2 py-1 text-xs font-medium rounded-full',
                      user.subscription_active
                        ? 'bg-success-100 text-success-800'
                        : 'bg-error-100 text-error-800',
                    ]"
                  >
                    {{ user.subscription_active ? "Active" : "Inactive" }}
                  </span>
                  <div
                    v-if="user.has_physical_card"
                    class="text-xs text-secondary-500 mt-1"
                  >
                    Has Physical Card
                  </div>
                </td>
                <td
                  class="px-6 py-4 whitespace-nowrap text-sm text-secondary-500"
                >
                  {{
                    user.last_login_at
                      ? formatTimeAgo(user.last_login_at)
                      : "Never"
                  }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex items-center space-x-2">
                    <button
                      @click="toggleUserStatus(user)"
                      :class="[
                        'px-2 py-1 rounded text-xs font-medium',
                        user.subscription_active
                          ? 'bg-warning-100 text-warning-700 hover:bg-warning-200'
                          : 'bg-success-100 text-success-700 hover:bg-success-200',
                      ]"
                      :title="
                        user.subscription_active
                          ? 'Deactivate account'
                          : 'Activate account'
                      "
                    >
                      {{ user.subscription_active ? "Deactivate" : "Activate" }}
                    </button>
                    <button
                      @click="viewUser(user)"
                      class="text-primary-600 hover:text-primary-900"
                      title="View details"
                    >
                      <Icon name="heroicons:eye" class="h-4 w-4" />
                    </button>
                    <button
                      @click="editUser(user)"
                      class="text-warning-600 hover:text-warning-900"
                      title="Edit user"
                    >
                      <Icon name="heroicons:pencil" class="h-4 w-4" />
                    </button>
                    <button
                      @click="deleteUser(user)"
                      class="text-error-600 hover:text-error-900"
                      title="Delete user"
                    >
                      <Icon name="heroicons:trash" class="h-4 w-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center py-12">
          <Icon
            name="heroicons:users"
            class="h-12 w-12 mx-auto text-secondary-300 mb-4"
          />
          <p class="text-secondary-500">No users found</p>
        </div>
      </div>
    </div>

    <!-- Pagination -->
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

    <!-- Create/Edit User Modal -->
    <div
      v-if="showCreateModal || showEditModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    >
      <div
        class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
      >
        <div class="px-6 py-4 border-b border-secondary-200">
          <h3 class="text-lg font-medium text-secondary-900">
            {{ showEditModal ? "Edit User" : "Create New User" }}
          </h3>
        </div>
        <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >First Name *</label
              >
              <input
                v-model="form.first_name"
                type="text"
                required
                maxlength="100"
                :class="['input', formErrors.first_name ? 'border-red-500' : '']"
                @input="validateFirstName"
              />
              <div class="flex justify-between mt-1">
                <span v-if="formErrors.first_name" class="text-xs text-red-500">{{ formErrors.first_name }}</span>
                <span v-else class="text-xs text-secondary-500"></span>
                <span class="text-xs text-secondary-500">{{ form.first_name.length }}/100</span>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Last Name *</label
              >
              <input
                v-model="form.last_name"
                type="text"
                required
                maxlength="100"
                :class="['input', formErrors.last_name ? 'border-red-500' : '']"
                @input="validateLastName"
              />
              <div class="flex justify-between mt-1">
                <span v-if="formErrors.last_name" class="text-xs text-red-500">{{ formErrors.last_name }}</span>
                <span v-else class="text-xs text-secondary-500"></span>
                <span class="text-xs text-secondary-500">{{ form.last_name.length }}/100</span>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Email *</label
              >
              <input
                v-model="form.email"
                type="email"
                required
                maxlength="100"
                :class="['input', formErrors.email ? 'border-red-500' : '']"
                @input="validateEmail"
              />
              <div class="flex justify-between mt-1">
                <span v-if="formErrors.email" class="text-xs text-red-500">{{ formErrors.email }}</span>
                <span v-else class="text-xs text-secondary-500"></span>
                <span class="text-xs text-secondary-500">{{ form.email.length }}/100</span>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Phone</label
              >
              <PhoneInput
                v-model="form.phone"
                :has-error="!!formErrors.phone"
                placeholder="Phone number"
              />
              <p v-if="formErrors.phone" class="text-xs text-red-500 mt-1">{{ formErrors.phone }}</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Company</label
              >
              <input
                v-model="form.company"
                type="text"
                maxlength="150"
                class="input"
              />
              <span class="text-xs text-secondary-500 mt-1 block text-right">{{ (form.company || '').length }}/150</span>
            </div>
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Job Title</label
              >
              <input
                v-model="form.job_title"
                type="text"
                maxlength="30"
                class="input"
              />
              <span class="text-xs text-secondary-500 mt-1 block text-right">{{ (form.job_title || '').length }}/30</span>
            </div>
          </div>

          <div
            v-if="!showEditModal"
            class="grid grid-cols-1 md:grid-cols-2 gap-4"
          >
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Password *</label
              >
              <div class="relative">
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  class="input pr-10"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-secondary-400 hover:text-secondary-600"
                >
                  <Icon :name="showPassword ? 'heroicons:eye-slash' : 'heroicons:eye'" class="h-5 w-5" />
                </button>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Confirm Password *</label
              >
              <div class="relative">
                <input
                  v-model="form.password_confirmation"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  required
                  class="input pr-10"
                />
                <button
                  type="button"
                  @click="showConfirmPassword = !showConfirmPassword"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-secondary-400 hover:text-secondary-600"
                >
                  <Icon :name="showConfirmPassword ? 'heroicons:eye-slash' : 'heroicons:eye'" class="h-5 w-5" />
                </button>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Subscription Plan</label
              >
              <select v-model="form.subscription_plan" class="input">
                <option value="free">Free</option>
                <option value="basic">Basic</option>
                <option value="premium">Premium</option>
                <option value="business">Business</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Account Status</label
              >
              <select v-model="form.subscription_active" class="input">
                <option :value="true">Active</option>
                <option :value="false">Inactive</option>
              </select>
              <p class="text-xs text-secondary-500 mt-1">
                Inactive accounts cannot log in or use the system
              </p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Admin Status</label
              >
              <select v-model="form.is_admin" class="input">
                <option :value="false">Regular User</option>
                <option :value="true">Admin</option>
              </select>
            </div>
          </div>

          <!-- Business Plan Quota Settings -->
          <div
            v-if="form.subscription_plan === 'business'"
            class="border-t border-secondary-200 pt-4 mt-4"
          >
            <h4 class="text-sm font-medium text-secondary-900 mb-4">
              Business Plan Settings
            </h4>

            <!-- Current Quota Usage (Edit Mode Only) -->
            <div
              v-if="showEditModal && selectedUser?.quota_info"
              class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4"
            >
              <div class="flex items-start">
                <Icon
                  name="heroicons:information-circle"
                  class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5"
                />
                <div class="ml-3 flex-1">
                  <h5 class="text-sm font-medium text-blue-900 mb-2">
                    Current Quota Usage
                  </h5>
                  <div class="space-y-2 text-sm">
                    <div>
                      <p class="text-blue-700 font-medium">Total Accounts:</p>
                      <p class="text-blue-600">
                        {{
                          selectedUser.quota_info.total_accounts ||
                          1 + selectedUser.quota_info.employees_count
                        }}
                        /
                        {{
                          selectedUser.quota_info.total_quota ||
                          selectedUser.quota_info.total_account_slots
                        }}
                        <span class="text-xs text-blue-500">
                          (1 owner +
                          {{ selectedUser.quota_info.employees_count }}
                          employees)
                        </span>
                      </p>
                    </div>
                    <div>
                      <p class="text-blue-700 font-medium">
                        NFC Cards Ordered:
                      </p>
                      <p class="text-blue-600">
                        {{ selectedUser.quota_info.ordered_cards_count }} /
                        {{
                          selectedUser.quota_info.total_quota ||
                          selectedUser.quota_info.total_card_quota
                        }}
                      </p>
                    </div>
                    <div class="pt-2 border-t border-blue-200">
                      <p class="text-blue-700 font-medium">Available Quota:</p>
                      <p class="text-blue-600 text-lg font-semibold">
                        {{
                          selectedUser.quota_info.available_quota ||
                          Math.min(
                            selectedUser.quota_info.available_account_slots ||
                              0,
                            selectedUser.quota_info.available_card_quota || 0
                          )
                        }}
                      </p>
                    </div>
                  </div>
                  <p class="text-xs text-blue-600 mt-3">
                    ⚠️ Cannot set quota below max(total_accounts, ordered_cards)
                  </p>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Total Business Quota *</label
              >
              <input
                v-model.number="form.total_account_slots"
                type="number"
                :min="getMinimumQuota()"
                required
                class="input"
                placeholder="e.g. 50"
              />
              <p class="text-xs text-secondary-500 mt-1">
                Maximum number of accounts (including owner) AND NFC cards this
                business can have
              </p>
              <p
                v-if="showEditModal && selectedUser?.quota_info"
                class="text-xs text-warning-600 mt-1"
              >
                Minimum: {{ getMinimumQuota() }} (based on current usage)
              </p>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mt-3">
              <div class="flex">
                <Icon
                  name="heroicons:information-circle"
                  class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5"
                />
                <div class="ml-3">
                  <p class="text-sm text-blue-700">
                    <strong>Unified Quota System:</strong>
                  </p>
                  <ul class="text-sm text-blue-600 mt-1 space-y-1">
                    <li>• Quota applies to both accounts AND cards</li>
                    <li>
                      • If quota = 10: max 10 accounts (1 owner + 9 employees)
                      AND max 10 cards
                    </li>
                    <li>
                      • Creating accounts doesn't consume quota until they order
                      cards
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <div
            v-if="form.is_admin"
            class="grid grid-cols-1 md:grid-cols-2 gap-4"
          >
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Admin Role</label
              >
              <select v-model="form.admin_role" class="input">
                <option value="admin">Admin</option>
                <option value="moderator">Moderator</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Admin Permissions</label
              >
              <div class="space-y-2">
                <label class="flex items-center">
                  <input
                    v-model="form.admin_permissions"
                    type="checkbox"
                    value="user_management"
                    class="mr-2"
                  />
                  User Management
                </label>
                <label class="flex items-center">
                  <input
                    v-model="form.admin_permissions"
                    type="checkbox"
                    value="nfc_management"
                    class="mr-2"
                  />
                  NFC Management
                </label>
                <label class="flex items-center">
                  <input
                    v-model="form.admin_permissions"
                    type="checkbox"
                    value="analytics"
                    class="mr-2"
                  />
                  Analytics
                </label>
              </div>
            </div>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button type="button" @click="closeModal" class="btn btn-outline">
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="btn btn-primary"
            >
              <div v-if="submitting" class="spinner mr-2"></div>
              {{ showEditModal ? "Update User" : "Create User" }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- User Details Modal -->
    <div
      v-if="showUserModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    >
      <div
        class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto"
      >
        <div class="px-6 py-4 border-b border-secondary-200">
          <h3 class="text-lg font-medium text-secondary-900">User Details</h3>
        </div>
        <div v-if="selectedUser" class="p-6">
          <!-- User info will be displayed here -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h4 class="font-medium text-secondary-900 mb-4">
                Basic Information
              </h4>
              <dl class="space-y-3">
                <div>
                  <dt class="text-sm font-medium text-secondary-500">Name</dt>
                  <dd class="text-sm text-secondary-900">
                    {{ selectedUser.full_name }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-secondary-500">Email</dt>
                  <dd class="text-sm text-secondary-900">
                    {{ selectedUser.email }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-secondary-500">
                    Company
                  </dt>
                  <dd class="text-sm text-secondary-900">
                    {{ selectedUser.company || "N/A" }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-secondary-500">
                    Job Title
                  </dt>
                  <dd class="text-sm text-secondary-900">
                    {{ selectedUser.job_title || "N/A" }}
                  </dd>
                </div>
              </dl>
            </div>
            <div>
              <h4 class="font-medium text-secondary-900 mb-4">
                Account Details
              </h4>
              <dl class="space-y-3">
                <div>
                  <dt class="text-sm font-medium text-secondary-500">
                    Subscription Plan
                  </dt>
                  <dd class="text-sm text-secondary-900">
                    {{ selectedUser.subscription_plan }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-secondary-500">Status</dt>
                  <dd class="text-sm text-secondary-900">
                    <span
                      :class="[
                        'px-2 py-1 text-xs font-medium rounded-full',
                        selectedUser.subscription_active
                          ? 'bg-success-100 text-success-800'
                          : 'bg-error-100 text-error-800',
                      ]"
                    >
                      {{
                        selectedUser.subscription_active ? "Active" : "Inactive"
                      }}
                    </span>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-secondary-500">
                    Last Login
                  </dt>
                  <dd class="text-sm text-secondary-900">
                    {{
                      selectedUser.last_login_at
                        ? formatTimeAgo(selectedUser.last_login_at)
                        : "Never"
                    }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-secondary-500">
                    Member Since
                  </dt>
                  <dd class="text-sm text-secondary-900">
                    {{ formatDate(selectedUser.created_at) }}
                  </dd>
                </div>
              </dl>
            </div>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-secondary-200">
          <button @click="showUserModal = false" class="btn btn-outline">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: "admin-management",
  middleware: "admin",
});

const adminStore = useAdminStore();
const { users, loading, pagination } = storeToRefs(adminStore);

// Modal states
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showUserModal = ref(false);
const selectedUser = ref(null);
const submitting = ref(false);

// Password visibility toggles
const showPassword = ref(false);
const showConfirmPassword = ref(false);

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

  if (form.value.password !== form.value.password_confirmation) {
    alert("Passwords do not match");
    return;
  }

  submitting.value = true;
  try {
    if (showEditModal.value) {
      await adminStore.updateUser(selectedUser.value.id, form.value);
    } else {
      await adminStore.createUser(form.value);
    }
    closeModal();
  } catch (error) {
    console.error("Failed to save user:", error);
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
const deleteUser = async (user) => {
  if (
    confirm(
      `Are you sure you want to delete ${user.full_name}? This action cannot be undone.`
    )
  ) {
    try {
      await adminStore.deleteUser(user.id);
    } catch (error) {
      console.error("Failed to delete user:", error);
    }
  }
};
// Toggle user account status (activate/deactivate)
const toggleUserStatus = async (user) => {
  const newStatus = !user.subscription_active;
  const action = newStatus ? "activate" : "deactivate";

  if (
    confirm(
      `Are you sure you want to ${action} ${user.full_name}'s account?${
        !newStatus
          ? "\n\nDeactivated users cannot log in or use the system."
          : ""
      }`
    )
  ) {
    try {
      await adminStore.updateUser(user.id, {
        subscription_active: newStatus,
      });

      const { $toast } = useNuxtApp();
      $toast.success(
        `${user.full_name}'s account has been ${
          newStatus ? "activated" : "deactivated"
        }.`
      );
    } catch (error) {
      console.error("Failed to toggle user status:", error);
      const { $toast } = useNuxtApp();
      $toast.error("Failed to update user status. Please try again.");
    }
  }
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

const getSubscriptionBadgeClass = (plan) => {
  const classes = {
    free: "bg-secondary-100 text-secondary-800",
    basic: "bg-blue-100 text-blue-800",
    premium: "bg-purple-100 text-purple-800",
    business: "bg-green-100 text-green-800",
  };
  return classes[plan] || classes.free;
};

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
