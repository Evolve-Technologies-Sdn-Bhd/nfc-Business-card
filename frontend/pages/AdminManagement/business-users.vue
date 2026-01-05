<template>
  <div>
    <!-- Header -->
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-3xl font-bold text-secondary-900">
            Business Plan User Management
          </h1>
          <p class="mt-2 text-secondary-600">
            Manage Business Plan users and create employee accounts
          </p>
        </div>
        <div class="mt-4 sm:mt-0">
          <button @click="showCreateModal = true" class="btn btn-primary">
            <Icon name="heroicons:plus" class="h-5 w-5 mr-2" />
            Create Employee Account
          </button>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="p-3 rounded-lg bg-primary-500">
              <Icon
                name="heroicons:building-office-2"
                class="h-6 w-6 text-white"
              />
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-secondary-600">
              Business Accounts
            </p>
            <p class="text-2xl font-semibold text-secondary-900">
              {{ statistics.total_business_users }}
            </p>
          </div>
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="p-3 rounded-lg bg-success-500">
              <Icon name="heroicons:users" class="h-6 w-6 text-white" />
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-secondary-600">
              Total Employees
            </p>
            <p class="text-2xl font-semibold text-secondary-900">
              {{ statistics.total_employees }}
            </p>
          </div>
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="p-3 rounded-lg bg-info-500">
              <Icon name="heroicons:user-group" class="h-6 w-6 text-white" />
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-secondary-600">
              Active Accounts
            </p>
            <p class="text-2xl font-semibold text-secondary-900">
              {{ statistics.active_accounts }}
            </p>
          </div>
        </div>
      </div>

      <div class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="p-3 rounded-lg bg-warning-500">
              <Icon name="heroicons:credit-card" class="h-6 w-6 text-white" />
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-secondary-600">Total Cards</p>
            <p class="text-2xl font-semibold text-secondary-900">
              {{ statistics.total_cards }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Search</label
            >
            <input
              type="text"
              v-model="filters.search"
              @input="debounceSearch"
              placeholder="Search by name, email..."
              class="input"
              maxlength="150"
            />
            <p class="text-xs text-secondary-500 mt-1">{{ filters.search.length }}/150</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Status</label
            >
            <select
              v-model="filters.status"
              @change="fetchBusinessUsers"
              class="input"
            >
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <div class="flex items-end">
            <button @click="resetFilters" class="btn btn-outline btn-sm w-full">
              Clear Filters
            </button>
          </div>
        </div>
        <div class="mt-4 flex justify-end">
          <div class="text-sm text-secondary-500">
            {{ pagination.total }} business users found
          </div>
        </div>
      </div>
    </div>

    <!-- Business Users Table -->
    <div class="card">
      <div class="card-body p-0">
        <div v-if="loading" class="flex justify-center py-12">
          <div class="spinner"></div>
        </div>
        <div v-else-if="businessUsers.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-secondary-200">
            <thead class="bg-secondary-50">
              <tr>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  Business Account
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  Email
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  <div class="flex flex-col">
                    <span>Employees</span>
                    <span class="text-xs font-semibold text-secondary-500 normal-case mt-0.5">
                      (excl. admin)
                    </span>
                  </div>
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  Quota
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  Cards
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  Status
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
                v-for="user in businessUsers"
                :key="user.id"
                class="hover:bg-secondary-50"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div
                        class="h-10 w-10 rounded-full bg-gradient-to-r from-primary-500 to-secondary-500 flex items-center justify-center"
                      >
                        <span class="text-white font-medium text-sm">
                          {{ user.first_name?.charAt(0)
                          }}{{ user.last_name?.charAt(0) }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-secondary-900">
                        {{ user.first_name }} {{ user.last_name }}
                      </div>
                      <div class="text-sm text-secondary-500">
                        {{ user.company || "N/A" }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-secondary-900">{{ user.email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-secondary-900">
                    {{ user.employees_count || 0 }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-secondary-900">
                    <span class="font-semibold">{{
                      user.quota_info?.available_quota || 0
                    }}</span>
                    <span class="text-secondary-500">
                      / {{ user.total_account_slots || 0 }}</span
                    >
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full"
                  >
                    {{ user.cards_count || 0 }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'px-2 py-1 text-xs font-medium rounded-full',
                      user.subscription_active
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800',
                    ]"
                  >
                    {{ user.subscription_active ? "Active" : "Inactive" }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button
                      @click="viewDetails(user)"
                      class="text-primary-600 hover:text-primary-900"
                      title="View Details"
                    >
                      <Icon name="heroicons:eye" class="h-5 w-5" />
                    </button>
                    <button
                      @click="createEmployee(user)"
                      class="text-success-600 hover:text-success-900"
                      title="Create Employee"
                    >
                      <Icon name="heroicons:user-plus" class="h-5 w-5" />
                    </button>
                    <button
                      @click="manageQuota(user)"
                      class="text-info-600 hover:text-info-900"
                      title="Manage Quota"
                    >
                      <Icon name="heroicons:cog-6-tooth" class="h-5 w-5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="text-center py-12 text-secondary-500">
          <Icon
            name="heroicons:building-office-2"
            class="h-16 w-16 mx-auto text-secondary-300 mb-4"
          />
          <h3 class="text-lg font-medium text-secondary-900 mb-2">
            No Business Users Found
          </h3>
          <p class="text-sm">
            There are no business plan users matching your filters.
          </p>
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
      item-label="results"
      @page-change="changePage"
      @per-page-change="changeItemsPerPage"
    />

    <!-- Create Employee Modal -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 bg-secondary-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
      @click="closeCreateModal"
    >
      <div class="card max-w-5xl w-full" @click.stop>
        <div class="card-header">
          <h2 class="text-xl font-bold text-secondary-900">
            Create Employee Account
          </h2>
        </div>

        <!-- Tabs -->
        <div class="border-b border-secondary-200">
          <div class="flex">
            <button
              @click="createMode = 'single'"
              :class="[
                'px-6 py-3 text-sm font-medium border-b-2 transition-colors',
                createMode === 'single'
                  ? 'border-primary-500 text-primary-600'
                  : 'border-transparent text-secondary-500 hover:text-secondary-700 hover:border-secondary-300',
              ]"
            >
              <Icon name="heroicons:user-plus" class="h-4 w-4 inline mr-2" />
              Single Employee
            </button>
            <button
              @click="createMode = 'bulk'"
              :class="[
                'px-6 py-3 text-sm font-medium border-b-2 transition-colors',
                createMode === 'bulk'
                  ? 'border-primary-500 text-primary-600'
                  : 'border-transparent text-secondary-500 hover:text-secondary-700 hover:border-secondary-300',
              ]"
            >
              <Icon
                name="heroicons:document-arrow-up"
                class="h-4 w-4 inline mr-2"
              />
              Bulk Import (CSV/XLSX)
            </button>
          </div>
        </div>

        <div class="card-body">
          <!-- Single Employee Form -->
          <div v-if="createMode === 'single'">
            <form @submit.prevent="submitCreateEmployee" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-2"
                  >Business Account *</label
                >
                <select
                  v-model="newEmployee.business_account_id"
                  required
                  class="input"
                >
                  <option value="">Select Business Account</option>
                  <option
                    v-for="user in businessUsers"
                    :key="user.id"
                    :value="user.id"
                  >
                    {{ user.first_name }} {{ user.last_name }} -
                    {{ user.email }} (Available:
                    {{ user.quota_info?.available_quota || 0 }})
                  </option>
                </select>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-2"
                    >First Name *</label
                  >
                  <input
                    type="text"
                    v-model="newEmployee.first_name"
                    required
                    class="input"
                  />
                </div>

                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-2"
                    >Last Name *</label
                  >
                  <input
                    type="text"
                    v-model="newEmployee.last_name"
                    required
                    class="input"
                  />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-2"
                  >Email *</label
                >
                <input
                  type="email"
                  v-model="newEmployee.email"
                  required
                  class="input"
                />
              </div>

              <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <p class="text-sm text-blue-700">
                  <Icon
                    name="heroicons:information-circle"
                    class="h-4 w-4 inline mr-1"
                  />
                  <strong>Default Password:</strong> The employee will receive a
                  temporary password
                  <code class="bg-blue-100 px-1 rounded">Welcome123@</code> and
                  can change it after first login.
                </p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-2"
                    >Phone</label
                  >
                  <input type="tel" v-model="newEmployee.phone" class="input" />
                </div>

                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-2"
                    >Job Title</label
                  >
                  <input
                    type="text"
                    v-model="newEmployee.job_title"
                    class="input"
                  />
                </div>
              </div>

              <div class="flex justify-end space-x-3 pt-4">
                <button
                  type="button"
                  @click="closeCreateModal"
                  class="btn btn-outline"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="btn btn-primary"
                  :disabled="isSubmitting"
                >
                  <span v-if="isSubmitting">Creating...</span>
                  <span v-else>Create Employee</span>
                </button>
              </div>
            </form>
          </div>

          <!-- Bulk Import Section -->
          <div v-else class="space-y-4">
            <!-- Business Account Selection -->
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Business Account *</label
              >
              <select v-model="bulkImport.business_account_id" class="input">
                <option value="">Select Business Account</option>
                <option
                  v-for="user in businessUsers"
                  :key="user.id"
                  :value="user.id"
                >
                  {{ user.first_name }} {{ user.last_name }} -
                  {{ user.email }} (Available:
                  {{ user.quota_info?.available_quota || 0 }})
                </option>
              </select>
            </div>

            <!-- File Upload Area -->
            <div v-if="!bulkImport.employees.length" class="space-y-4">
              <div
                class="bg-secondary-50 border-2 border-dashed border-secondary-300 rounded-lg p-8"
              >
                <div class="text-center">
                  <Icon
                    name="heroicons:document-arrow-up"
                    class="h-12 w-12 mx-auto text-secondary-400 mb-4"
                  />
                  <div class="mb-4">
                    <label
                      for="file-upload"
                      class="btn btn-primary cursor-pointer"
                    >
                      <Icon
                        name="heroicons:arrow-up-tray"
                        class="h-5 w-5 mr-2"
                      />
                      Upload CSV/XLSX File
                    </label>
                    <input
                      id="file-upload"
                      type="file"
                      accept=".csv,.xlsx,.xls"
                      @change="handleFileUpload"
                      class="hidden"
                    />
                  </div>
                  <p class="text-sm text-secondary-500 mb-2">
                    Upload a CSV or XLSX file with employee information
                  </p>
                  <button
                    @click="downloadTemplate"
                    class="text-sm text-primary-600 hover:text-primary-800 underline"
                  >
                    <Icon
                      name="heroicons:arrow-down-tray"
                      class="h-4 w-4 inline mr-1"
                    />
                    Download Template
                  </button>
                </div>
              </div>

              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="text-sm font-medium text-blue-900 mb-2">
                  <Icon
                    name="heroicons:information-circle"
                    class="h-5 w-5 inline mr-1"
                  />
                  Template Format
                </h4>
                <p class="text-sm text-blue-700 mb-2">
                  Your file should include the following columns:
                </p>
                <ul class="text-sm text-blue-700 space-y-1 ml-4">
                  <li>• <strong>first_name</strong> (required)</li>
                  <li>• <strong>last_name</strong> (required)</li>
                  <li>• <strong>email</strong> (required)</li>
                  <li>
                    • <strong>password</strong> (optional, defaults to
                    <code class="bg-blue-100 px-1 rounded">Welcome123@</code>)
                  </li>
                  <li>• <strong>phone</strong> (optional)</li>
                  <li>• <strong>job_title</strong> (optional)</li>
                </ul>
              </div>
            </div>

            <!-- Editable Table Preview -->
            <div v-else class="space-y-4">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-secondary-900">
                  Preview & Edit Employee Data ({{
                    bulkImport.employees.length
                  }}
                  employees)
                </h3>
                <button @click="clearBulkImport" class="btn btn-outline btn-sm">
                  <Icon name="heroicons:x-mark" class="h-4 w-4 mr-1" />
                  Clear
                </button>
              </div>

              <div
                class="overflow-x-auto border border-secondary-200 rounded-lg"
              >
                <table class="min-w-full divide-y divide-secondary-200">
                  <thead class="bg-secondary-50">
                    <tr>
                      <th
                        class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase"
                      >
                        #
                      </th>
                      <th
                        class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase"
                      >
                        First Name *
                      </th>
                      <th
                        class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase"
                      >
                        Last Name *
                      </th>
                      <th
                        class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase"
                      >
                        Email *
                      </th>
                      <th
                        class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase"
                      >
                        Password
                      </th>
                      <th
                        class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase"
                      >
                        Phone
                      </th>
                      <th
                        class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase"
                      >
                        Job Title
                      </th>
                      <th
                        class="px-4 py-3 text-left text-xs font-medium text-secondary-500 uppercase"
                      >
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-secondary-200">
                    <tr
                      v-for="(employee, index) in bulkImport.employees"
                      :key="index"
                      class="hover:bg-secondary-50"
                    >
                      <td class="px-4 py-3 text-sm text-secondary-900">
                        {{ index + 1 }}
                      </td>
                      <td class="px-4 py-3">
                        <input
                          v-model="employee.first_name"
                          type="text"
                          class="input input-sm w-full"
                          :class="{ 'border-red-500': !employee.first_name }"
                        />
                      </td>
                      <td class="px-4 py-3">
                        <input
                          v-model="employee.last_name"
                          type="text"
                          class="input input-sm w-full"
                          :class="{ 'border-red-500': !employee.last_name }"
                        />
                      </td>
                      <td class="px-4 py-3">
                        <input
                          v-model="employee.email"
                          type="email"
                          class="input input-sm w-full"
                          :class="{
                            'border-red-500':
                              !employee.email || !isValidEmail(employee.email),
                          }"
                        />
                      </td>
                      <td class="px-4 py-3">
                        <input
                          v-model="employee.password"
                          type="text"
                          placeholder="Welcome123@"
                          class="input input-sm w-full"
                          :class="{
                            'border-red-500':
                              employee.password && employee.password.length < 8,
                          }"
                        />
                      </td>
                      <td class="px-4 py-3">
                        <input
                          v-model="employee.phone"
                          type="tel"
                          class="input input-sm w-full"
                        />
                      </td>
                      <td class="px-4 py-3">
                        <input
                          v-model="employee.job_title"
                          type="text"
                          class="input input-sm w-full"
                        />
                      </td>
                      <td class="px-4 py-3">
                        <button
                          @click="removeEmployee(index)"
                          class="text-red-600 hover:text-red-900"
                          title="Remove"
                        >
                          <Icon name="heroicons:trash" class="h-5 w-5" />
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div
                v-if="bulkImport.errors.length"
                class="bg-red-50 border border-red-200 rounded-lg p-4"
              >
                <h4 class="text-sm font-medium text-red-900 mb-2">
                  <Icon
                    name="heroicons:exclamation-triangle"
                    class="h-5 w-5 inline mr-1"
                  />
                  Validation Errors ({{ bulkImport.errors.length }})
                </h4>
                <ul class="text-sm text-red-800 font-medium space-y-1 ml-4">
                  <li v-for="(error, index) in bulkImport.errors" :key="index">
                    • {{ error }}
                  </li>
                </ul>
              </div>

              <div class="flex justify-end space-x-3 pt-4">
                <button
                  type="button"
                  @click="closeCreateModal"
                  class="btn btn-outline"
                >
                  Cancel
                </button>
                <button
                  @click="submitBulkImport"
                  class="btn btn-primary"
                  :disabled="
                    isSubmitting ||
                    !bulkImport.business_account_id ||
                    bulkImport.errors.length > 0
                  "
                >
                  <span v-if="isSubmitting"
                    >Creating
                    {{ bulkImport.employees.length }} Employees...</span
                  >
                  <span v-else
                    >Create {{ bulkImport.employees.length }} Employees</span
                  >
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Details Modal -->
    <div
      v-if="showDetailsModal && selectedUser"
      class="fixed inset-0 bg-secondary-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
      @click="closeDetailsModal"
    >
      <div class="card max-w-3xl w-full" @click.stop>
        <div class="card-header">
          <h2 class="text-xl font-bold text-secondary-900">
            Business Account Details
          </h2>
        </div>

        <div class="card-body space-y-6">
          <div class="bg-secondary-50 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-secondary-900 mb-4">
              Account Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-xs font-medium text-secondary-500 mb-1">Name</p>
                <p class="text-sm text-secondary-900">
                  {{ selectedUser.first_name }} {{ selectedUser.last_name }}
                </p>
              </div>
              <div>
                <p class="text-xs font-medium text-secondary-500 mb-1">Email</p>
                <p class="text-sm text-secondary-900">
                  {{ selectedUser.email }}
                </p>
              </div>
              <div>
                <p class="text-xs font-medium text-secondary-500 mb-1">
                  Company
                </p>
                <p class="text-sm text-secondary-900">
                  {{ selectedUser.company || "N/A" }}
                </p>
              </div>
              <div>
                <p class="text-xs font-medium text-secondary-500 mb-1">Phone</p>
                <p class="text-sm text-secondary-900">
                  {{ selectedUser.phone || "N/A" }}
                </p>
              </div>
            </div>
          </div>

          <div class="bg-secondary-50 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-secondary-900 mb-4">
              Quota Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-xs font-medium text-secondary-500 mb-1">
                  Total Quota
                </p>
                <p class="text-sm text-secondary-900">
                  {{ selectedUser.total_account_slots || 0 }}
                </p>
              </div>
              <div>
                <p class="text-xs font-medium text-secondary-500 mb-1">
                  Employees
                </p>
                <p class="text-sm text-secondary-900">
                  {{ selectedUser.employees_count || 0 }}
                </p>
              </div>
              <div>
                <p class="text-xs font-medium text-secondary-500 mb-1">
                  Available
                </p>
                <p class="text-sm text-secondary-900">
                  {{ selectedUser.quota_info?.available_quota || 0 }}
                </p>
              </div>
              <div>
                <p class="text-xs font-medium text-secondary-500 mb-1">
                  Total Cards
                </p>
                <p class="text-sm text-secondary-900">
                  {{ selectedUser.cards_count || 0 }}
                </p>
              </div>
            </div>
          </div>

          <div
            v-if="selectedUser.employees && selectedUser.employees.length > 0"
            class="bg-secondary-50 rounded-lg p-4"
          >
            <h3 class="text-lg font-semibold text-secondary-900 mb-4">
              Employees ({{ selectedUser.employees.length }})
            </h3>
            <div class="space-y-3">
              <div
                v-for="employee in selectedUser.employees"
                :key="employee.id"
                class="bg-white rounded-lg p-3"
              >
                <div class="flex items-center justify-between mb-2">
                  <div>
                    <p class="text-sm font-medium text-secondary-900">
                      {{ employee.first_name }} {{ employee.last_name }}
                    </p>
                    <p class="text-xs text-secondary-500">{{ employee.email }}</p>
                  </div>
                  <div class="flex items-center gap-2">
                    <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                      {{ employee.cards_count || 0 }} card{{ (employee.cards_count || 0) !== 1 ? 's' : '' }}
                    </span>
                    <span
                      :class="[
                        'px-2 py-1 text-xs font-medium rounded-full',
                        employee.subscription_active
                          ? 'bg-green-100 text-green-800'
                          : 'bg-red-100 text-red-800',
                      ]"
                    >
                      {{ employee.subscription_active ? "Active" : "Inactive" }}
                    </span>
                  </div>
                </div>
                
                <!-- Show NFC cards if any -->
                <div v-if="employee.nfc_cards && employee.nfc_cards.length > 0" class="mt-2 pl-4 border-l-2 border-blue-200">
                  <p class="text-xs text-secondary-600 font-medium mb-1">NFC Cards:</p>
                  <div class="space-y-1">
                    <div v-for="card in employee.nfc_cards" :key="card.id" class="text-xs text-secondary-500">
                      • {{ card.nfc_card_id }} - {{ card.card_owner }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";

definePageMeta({
  middleware: ["auth", "admin"],
  layout: "admin-management",
});

const { $api } = useNuxtApp();

// State
const loading = ref(false);
const businessUsers = ref([]);
const statistics = ref({
  total_business_users: 0,
  total_employees: 0,
  active_accounts: 0,
  total_cards: 0,
});

const selectedUser = ref(null);
const showDetailsModal = ref(false);
const showCreateModal = ref(false);
const isSubmitting = ref(false);
const createMode = ref("single"); // 'single' or 'bulk'

// Filters
const filters = ref({
  search: "",
  status: "",
});

// Pagination
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
});

// New Employee Form
const newEmployee = ref({
  business_account_id: "",
  first_name: "",
  last_name: "",
  email: "",
  phone: "",
  job_title: "",
});

// Bulk Import
const bulkImport = ref({
  business_account_id: "",
  employees: [],
  errors: [],
});

let searchTimeout = null;

// Fetch business users
const fetchBusinessUsers = async (page = 1) => {
  try {
    const queryParams = new URLSearchParams({
      page: page,
      per_page: pagination.value.per_page,
      ...Object.fromEntries(
        Object.entries(filters.value).filter(([_, v]) => v !== "")
      ),
    });

    const response = await $api.get(`/admin/business-users?${queryParams}`);

    if (response.success) {
      businessUsers.value = response.data;
      pagination.value = {
        current_page: response.current_page,
        last_page: response.last_page,
        per_page: response.per_page,
        total: response.total,
      };
    }
  } catch (error) {
    console.error("Error fetching business users:", error);
    alert("Failed to load business users. Please try again.");
  }
};

// Fetch statistics
const fetchStatistics = async () => {
  try {
    const response = await $api.get("/admin/business-users/statistics");
    if (response.success) {
      statistics.value = response.statistics;
    }
  } catch (error) {
    console.error("Error fetching statistics:", error);
  }
};

// View details
const viewDetails = async (user) => {
  try {
    const response = await $api.get(`/admin/business-users/${user.id}`);
    if (response.success) {
      selectedUser.value = response.user;
      showDetailsModal.value = true;
    }
  } catch (error) {
    console.error("Error fetching user details:", error);
    alert("Failed to load user details.");
  }
};

// Create employee
const createEmployee = (user) => {
  newEmployee.value.business_account_id = user.id;
  bulkImport.value.business_account_id = user.id;
  showCreateModal.value = true;
};

// Submit create employee
const submitCreateEmployee = async () => {
  if (isSubmitting.value) return;

  isSubmitting.value = true;
  try {
    const response = await $api.post("/admin/business-users/create-employee", {
      ...newEmployee.value,
      password: "Welcome123@",
    });

    if (response.success) {
      alert("Employee account created successfully!");
      closeCreateModal();
      fetchBusinessUsers();
      fetchStatistics();
    }
  } catch (error) {
    console.error("Error creating employee:", error);
    alert(
      error.response?.data?.message || "Failed to create employee account."
    );
  } finally {
    isSubmitting.value = false;
  }
};

// Manage quota
const manageQuota = async (user) => {
  const newQuota = prompt(
    `Enter new quota for ${user.first_name} ${user.last_name}:`,
    user.total_account_slots || 0
  );

  if (newQuota === null) return;

  const quota = parseInt(newQuota);
  if (isNaN(quota) || quota < 0) {
    alert("Please enter a valid number.");
    return;
  }

  try {
    const response = await $api.post(
      `/admin/business-users/${user.id}/update-quota`,
      {
        total_account_slots: quota,
      }
    );

    if (response.success) {
      alert("Quota updated successfully!");
      fetchBusinessUsers();
    }
  } catch (error) {
    console.error("Error updating quota:", error);
    alert("Failed to update quota.");
  }
};

// Bulk Import Functions
const handleFileUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = async (e) => {
    try {
      const data = new Uint8Array(e.target.result);
      const XLSX = await import("xlsx");
      const workbook = XLSX.read(data, { type: "array" });
      const sheetName = workbook.SheetNames[0];
      const worksheet = workbook.Sheets[sheetName];
      const jsonData = XLSX.utils.sheet_to_json(worksheet);

      bulkImport.value.employees = jsonData.map((row) => ({
        first_name: row.first_name || row["First Name"] || "",
        last_name: row.last_name || row["Last Name"] || "",
        email: row.email || row["Email"] || "",
        password: row.password || row["Password"] || "Welcome123@",
        phone: row.phone || row["Phone"] || "",
        job_title: row.job_title || row["Job Title"] || "",
      }));

      validateBulkImport();
    } catch (error) {
      console.error("Error parsing file:", error);
      alert(
        "Failed to parse file. Please ensure it matches the template format."
      );
    }
  };
  reader.readAsArrayBuffer(file);
  event.target.value = "";
};

const downloadTemplate = async () => {
  const template = [
    {
      first_name: "John",
      last_name: "Doe",
      email: "john.doe@example.com",
      password: "Welcome123@",
      phone: "+1234567890",
      job_title: "Sales Manager",
    },
  ];

  try {
    const XLSX = await import("xlsx");
    const ws = XLSX.utils.json_to_sheet(template);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Employees");
    XLSX.writeFile(wb, "employee_import_template.xlsx");
  } catch (error) {
    console.error("Error generating template:", error);
    alert("Failed to generate template file.");
  }
};

const validateBulkImport = () => {
  const errors = [];

  bulkImport.value.employees.forEach((employee, index) => {
    const rowNum = index + 1;

    if (!employee.first_name) {
      errors.push(`Row ${rowNum}: First name is required`);
    }
    if (!employee.last_name) {
      errors.push(`Row ${rowNum}: Last name is required`);
    }
    if (!employee.email) {
      errors.push(`Row ${rowNum}: Email is required`);
    } else if (!isValidEmail(employee.email)) {
      errors.push(`Row ${rowNum}: Invalid email format`);
    }
    if (employee.password && employee.password.length < 8) {
      errors.push(`Row ${rowNum}: Password must be at least 8 characters`);
    }
  });

  bulkImport.value.errors = errors;
};

const isValidEmail = (email) => {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
};

const removeEmployee = (index) => {
  bulkImport.value.employees.splice(index, 1);
  validateBulkImport();
};

const clearBulkImport = () => {
  bulkImport.value = {
    business_account_id: bulkImport.value.business_account_id,
    employees: [],
    errors: [],
  };
};

const submitBulkImport = async () => {
  if (!bulkImport.value.business_account_id) {
    alert("Please select a business account.");
    return;
  }

  if (bulkImport.value.employees.length === 0) {
    alert("No employees to import.");
    return;
  }

  validateBulkImport();
  if (bulkImport.value.errors.length > 0) {
    alert("Please fix all validation errors before submitting.");
    return;
  }

  isSubmitting.value = true;
  try {
    const employeesWithPassword = bulkImport.value.employees.map((emp) => ({
      ...emp,
      password: emp.password || "Welcome123@",
    }));

    const response = await $api.post(
      "/admin/business-users/create-employees-bulk",
      {
        business_account_id: bulkImport.value.business_account_id,
        employees: employeesWithPassword,
      }
    );

    if (response.success) {
      alert(
        `Successfully created ${bulkImport.value.employees.length} employee accounts!`
      );
      closeCreateModal();
      fetchBusinessUsers();
      fetchStatistics();
    }
  } catch (error) {
    console.error("Error creating employees:", error);
    if (error.response?.status === 422) {
      const errorData = error.response.data;
      console.log("422 Error Details:", errorData);

      if (errorData.errors) {
        const backendErrors = [];
        Object.entries(errorData.errors).forEach(([field, messages]) => {
          if (Array.isArray(messages)) {
            messages.forEach((message) => {
              backendErrors.push(`${field}: ${message}`);
            });
          } else {
            backendErrors.push(`${field}: ${messages}`);
          }
        });

        bulkImport.value.errors = [
          ...bulkImport.value.errors,
          ...backendErrors,
        ];

        alert(`Backend validation failed:\n${backendErrors.join("\n")}`);
      } else if (errorData.message) {
        alert(`Backend error: ${errorData.message}`);
      } else {
        alert("Validation failed. Please check the error details below.");
      }
    } else {
      alert(
        error.response?.data?.message || "Failed to create employee accounts."
      );
    }
  } finally {
    isSubmitting.value = false;
  }
};

// Close modals
const closeDetailsModal = () => {
  showDetailsModal.value = false;
  selectedUser.value = null;
};

const closeCreateModal = () => {
  showCreateModal.value = false;
  createMode.value = "single";
  newEmployee.value = {
    business_account_id: "",
    first_name: "",
    last_name: "",
    email: "",
    phone: "",
    job_title: "",
  };
  bulkImport.value = {
    business_account_id: "",
    employees: [],
    errors: [],
  };
};

// Reset filters
const resetFilters = () => {
  filters.value = {
    search: "",
    status: "",
  };
  fetchBusinessUsers();
};

// Debounce search
const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchBusinessUsers();
  }, 500);
};

// Change page
const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchBusinessUsers(page);
  }
};

// Initialize
onMounted(() => {
  fetchBusinessUsers();
  fetchStatistics();
});
</script>

<style scoped>
.input-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
  line-height: 1.25rem;
}
</style>
