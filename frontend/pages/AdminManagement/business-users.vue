<!-- pages/AdminManagement/business-users.vue -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Business Accounts"
      eyebrow-icon="heroicons:building-office-2"
      title="Business Plan Users"
      description="Manage business plan accounts, review their unified quota and provision employee seats."
    >
      <template #actions>
        <button
          type="button"
          class="admin-btn admin-btn--primary"
          @click="showCreateModal = true"
        >
          <Icon name="heroicons:user-plus" class="h-4 w-4" />
          Create employee
        </button>
      </template>
    </AdminPageHeader>

    <!-- KPI row -->
    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <AdminStatTile
        label="Business accounts"
        :value="statistics.total_business_users"
        icon="heroicons:building-office-2"
        icon-class="!bg-accent-50 !text-accent-600"
        hint="Accounts on the business plan"
      />
      <AdminStatTile
        label="Total employees"
        :value="statistics.total_employees"
        icon="heroicons:users"
        icon-class="!bg-info-50 !text-info-600"
        hint="Provisioned employee seats"
      />
      <AdminStatTile
        label="Active accounts"
        :value="statistics.active_accounts"
        icon="heroicons:user-group"
        icon-class="!bg-success-50 !text-success-600"
        hint="Sign-in enabled"
      />
      <AdminStatTile
        label="Total cards"
        :value="statistics.total_cards"
        icon="heroicons:credit-card"
        icon-class="!bg-warning-50 !text-warning-600"
        hint="Business-plan cards"
      />
    </div>

    <AdminFilterBar @submit="applyFilters">
      <template #search>
        <div class="mb-[0.35rem] flex items-baseline justify-between gap-2">
          <label class="admin-label !mb-0">Search</label>
          <span class="text-helper text-secondary-400">
            {{ filters.search.length }}/150
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
            placeholder="Search by name, email or company…"
            class="admin-field !pl-9"
            maxlength="150"
            @input="debounceSearch"
          />
        </div>
      </template>

      <div class="w-full sm:w-auto sm:min-w-[10rem]">
        <label class="admin-label">Status</label>
        <select v-model="filters.status" class="admin-field" @change="applyFilters">
          <option value="">All statuses</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
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
      <template #title>Business accounts</template>
      <template #header-actions>
        <span
          v-if="loading"
          class="inline-flex items-center gap-1.5 text-caption text-secondary-500"
          role="status"
        >
          <Icon name="heroicons:arrow-path" class="h-3.5 w-3.5 animate-spin" />
          Loading
        </span>
        <span class="text-caption text-secondary-500">
          {{ pagination.total }} record{{ pagination.total === 1 ? "" : "s" }}
        </span>
      </template>

      <div
        v-if="error && businessUsers.length"
        class="m-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-error-200 bg-error-50 px-4 py-3"
        role="alert"
      >
        <p class="text-body-sm text-error-800">{{ error }}</p>
        <button
          type="button"
          class="admin-btn admin-btn--outline admin-btn--sm"
          :disabled="loading"
          @click="fetchBusinessUsers()"
        >
          Retry
        </button>
      </div>

      <AdminEmptyState
        v-if="error && !businessUsers.length"
        icon="heroicons:exclamation-triangle"
        title="Couldn't load business accounts"
        :description="error"
      >
        <template #actions>
          <button
            type="button"
            class="admin-btn admin-btn--primary"
            :disabled="loading"
            @click="fetchBusinessUsers()"
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
        v-else-if="loading && !businessUsers.length"
        class="divide-y divide-secondary-100"
        role="status"
        aria-busy="true"
      >
        <div v-for="i in 6" :key="i" class="flex items-center gap-4 px-5 py-4">
          <span class="admin-skeleton h-10 w-10 shrink-0 rounded-full"></span>
          <span class="admin-skeleton h-4 w-1/4"></span>
          <span class="admin-skeleton ml-auto h-4 w-24"></span>
          <span class="admin-skeleton h-4 w-16"></span>
        </div>
      </div>

      <!-- Empty -->
      <AdminEmptyState
        v-else-if="!businessUsers.length"
        icon="heroicons:building-office-2"
        title="No business accounts found"
        description="Adjust your filters or clear them to see every business plan account."
      >
        <template v-if="hasActiveFilters" #actions>
          <button
            type="button"
            class="admin-btn admin-btn--outline admin-btn--sm"
            @click="resetFilters"
          >
            Clear filters
          </button>
        </template>
      </AdminEmptyState>

      <!-- Table -->
      <div v-else class="admin-scroll">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Business account</th>
              <th>
                <div class="flex flex-col">
                  <span>Employees</span>
                  <span class="text-micro font-semibold normal-case text-secondary-400">
                    (excl. admin)
                  </span>
                </div>
              </th>
              <th>
                <div
                  class="flex flex-col"
                  title="Remaining shared account and Business-card capacity, divided by the configured limit."
                >
                  <span>Shared quota</span>
                  <span class="text-micro font-semibold normal-case text-secondary-400">
                    (available / limit)
                  </span>
                </div>
              </th>
              <th>
                <div class="flex flex-col">
                  <span>Linked cards</span>
                  <span class="text-micro font-semibold normal-case text-secondary-400">
                    (all linked)
                  </span>
                </div>
              </th>
              <th>Status</th>
              <th class="admin-col-actions">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in businessUsers" :key="user.id">
              <td>
                <div class="flex min-w-0 items-center gap-3">
                  <UserAvatar
                    :src="user.profile_image"
                    :name="[user.first_name, user.last_name].filter(Boolean).join(' ')"
                    class="h-10 w-10 shrink-0 rounded-full ring-1 ring-secondary-200"
                  />
                  <div class="min-w-0">
                    <div class="truncate text-body font-semibold text-secondary-900">
                      {{ [user.first_name, user.last_name].filter(Boolean).join(" ") || user.email }}
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
                <span class="text-body font-semibold text-secondary-900">
                  {{ user.employees_count || 0 }}
                </span>
              </td>
              <td>
                <span
                  class="text-body font-semibold text-secondary-900"
                  :title="`${user.quota_info?.available_quota || 0} shared slots available out of ${user.total_account_slots || 0}`"
                >
                  {{ user.quota_info?.available_quota || 0 }}
                </span>
                <span class="text-secondary-500"> / {{ user.total_account_slots || 0 }}</span>
              </td>
              <td>
                <AdminStatusBadge
                  tone="info"
                  plain
                  :label="String(user.cards_count || 0)"
                  :title="`${user.cards_count || 0} cards linked to this business account`"
                />
              </td>
              <td>
                <div
                  class="inline-flex rounded-lg border border-secondary-200 bg-secondary-50 p-0.5"
                  role="radiogroup"
                  :aria-label="`Account status for ${user.first_name} ${user.last_name}`"
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
              </td>
              <td class="admin-col-actions">
                <div class="inline-flex items-center gap-1.5">
                  <button
                    type="button"
                    title="View details"
                    :aria-label="`View ${user.first_name} ${user.last_name} details`"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-secondary-500 transition hover:bg-secondary-100 hover:text-primary-700"
                    @click="viewDetails(user)"
                  >
                    <Icon name="heroicons:eye" class="h-4 w-4" />
                  </button>
                  <button
                    type="button"
                    title="Create employee"
                    :aria-label="`Create employee under ${user.first_name} ${user.last_name}`"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-secondary-500 transition hover:bg-success-50 hover:text-success-700"
                    @click="createEmployee(user)"
                  >
                    <Icon name="heroicons:user-plus" class="h-4 w-4" />
                  </button>
                  <button
                    type="button"
                    title="Manage quota"
                    :aria-label="`Manage quota for ${user.first_name} ${user.last_name}`"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-secondary-500 transition hover:bg-info-50 hover:text-info-700"
                    @click="manageQuota(user)"
                  >
                    <Icon name="heroicons:cog-6-tooth" class="h-4 w-4" />
                  </button>
                </div>
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
      item-label="results"
      @page-change="changePage"
      @per-page-change="changeItemsPerPage"
    />

    <!-- Create employee dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="showCreateModal"
          class="admin-dialog-backdrop"
          @click.self="closeCreateModal"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="Create employee account"
            style="width: min(52rem, 100%); margin-top: 5vh; max-height: 90vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
                >
                  <Icon name="heroicons:user-plus" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">Create employee account</p>
                  <p class="admin-panel-subtitle">
                    Add a single employee or bulk-import a whole roster
                  </p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="closeCreateModal"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <!-- Tabs -->
            <div class="flex gap-5 border-b border-secondary-200 px-6">
              <button
                type="button"
                class="inline-flex items-center gap-2 border-b-2 px-1 py-3 text-button transition"
                :class="
                  createMode === 'single'
                    ? 'border-accent-500 text-accent-700'
                    : 'border-transparent text-secondary-500 hover:text-secondary-700'
                "
                @click="createMode = 'single'"
              >
                <Icon name="heroicons:user-plus" class="h-4 w-4" />
                Single employee
              </button>
              <button
                type="button"
                class="inline-flex items-center gap-2 border-b-2 px-1 py-3 text-button transition"
                :class="
                  createMode === 'bulk'
                    ? 'border-accent-500 text-accent-700'
                    : 'border-transparent text-secondary-500 hover:text-secondary-700'
                "
                @click="createMode = 'bulk'"
              >
                <Icon name="heroicons:document-arrow-up" class="h-4 w-4" />
                Bulk import (CSV/XLSX)
              </button>
            </div>

            <!-- Single employee -->
            <form
              v-if="createMode === 'single'"
              class="flex min-h-0 flex-1 flex-col"
              @submit.prevent="submitCreateEmployee"
            >
              <div class="admin-panel-body flex-1 space-y-6 overflow-y-auto">
                <div class="admin-form-section">
                  <p class="admin-form-section-title">Business account</p>
                  <p class="admin-form-section-desc">
                    The business this employee will be provisioned under.
                  </p>
                  <div class="mt-4">
                    <label class="admin-label">
                      Business account <span class="text-error-600">*</span>
                    </label>
                    <select
                      v-model="newEmployee.business_account_id"
                      required
                      class="admin-field"
                    >
                      <option value="">Select business account</option>
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
                </div>

                <div class="admin-form-section">
                  <p class="admin-form-section-title">Employee details</p>
                  <p class="admin-form-section-desc">
                    Core information for the new employee account.
                  </p>
                  <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                      <label class="admin-label">
                        First name <span class="text-error-600">*</span>
                      </label>
                      <input
                        v-model="newEmployee.first_name"
                        type="text"
                        required
                        class="admin-field"
                      />
                    </div>
                    <div>
                      <label class="admin-label">
                        Last name <span class="text-error-600">*</span>
                      </label>
                      <input
                        v-model="newEmployee.last_name"
                        type="text"
                        required
                        class="admin-field"
                      />
                    </div>
                    <div class="md:col-span-2">
                      <label class="admin-label">
                        Email <span class="text-error-600">*</span>
                      </label>
                      <input
                        v-model="newEmployee.email"
                        type="email"
                        required
                        class="admin-field"
                      />
                    </div>
                    <div>
                      <label class="admin-label">Phone</label>
                      <input v-model="newEmployee.phone" type="tel" class="admin-field" />
                    </div>
                    <div>
                      <label class="admin-label">Job title</label>
                      <input
                        v-model="newEmployee.job_title"
                        type="text"
                        class="admin-field"
                      />
                    </div>
                  </div>

                  <div class="mt-4 rounded-xl border border-info-200 bg-info-50 p-4">
                    <p class="text-body text-info-700">
                      <Icon
                        name="heroicons:information-circle"
                        class="mr-1 inline h-4 w-4"
                      />
                      <strong>Default password:</strong> the employee receives a
                      temporary password
                      <code class="rounded bg-info-100 px-1">Welcome123@</code>
                      and can change it after first sign-in.
                    </p>
                  </div>
                </div>
              </div>

              <div class="admin-dialog-foot">
                <button
                  type="button"
                  class="admin-btn admin-btn--ghost"
                  @click="closeCreateModal"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="admin-btn admin-btn--primary"
                  :disabled="isSubmitting"
                >
                  <Icon
                    v-if="isSubmitting"
                    name="heroicons:arrow-path"
                    class="h-4 w-4 animate-spin"
                  />
                  {{ isSubmitting ? "Creating…" : "Create employee" }}
                </button>
              </div>
            </form>

            <!-- Bulk import -->
            <template v-else>
              <div class="admin-panel-body flex-1 space-y-6 overflow-y-auto">
                <div class="admin-form-section">
                  <p class="admin-form-section-title">Business account</p>
                  <p class="admin-form-section-desc">
                    Every imported employee is provisioned under this business.
                  </p>
                  <div class="mt-4">
                    <label class="admin-label">
                      Business account <span class="text-error-600">*</span>
                    </label>
                    <select v-model="bulkImport.business_account_id" class="admin-field">
                      <option value="">Select business account</option>
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
                </div>

                <!-- Upload -->
                <div v-if="!bulkImport.employees.length" class="space-y-4">
                  <div
                    class="rounded-xl border-2 border-dashed border-secondary-300 bg-secondary-50 p-8 text-center"
                  >
                    <Icon
                      name="heroicons:document-arrow-up"
                      class="mx-auto h-12 w-12 text-secondary-400"
                    />
                    <p class="mt-3 text-body text-secondary-500">
                      Upload a CSV or XLSX file containing the employee roster.
                    </p>
                    <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
                      <label for="file-upload" class="admin-btn admin-btn--primary cursor-pointer">
                        <Icon name="heroicons:arrow-up-tray" class="h-4 w-4" />
                        Upload CSV/XLSX
                      </label>
                      <input
                        id="file-upload"
                        type="file"
                        accept=".csv,.xlsx,.xls"
                        class="hidden"
                        @change="handleFileUpload"
                      />
                      <button
                        type="button"
                        class="admin-btn admin-btn--outline"
                        @click="downloadTemplate"
                      >
                        <Icon name="heroicons:arrow-down-tray" class="h-4 w-4" />
                        Download template
                      </button>
                    </div>
                  </div>

                  <div class="rounded-xl border border-info-200 bg-info-50 p-4">
                    <p class="text-body font-semibold text-info-900">Template format</p>
                    <p class="text-body mt-1 text-info-700">
                      Your file should include the following columns:
                    </p>
                    <ul class="text-body mt-2 ml-4 space-y-1 text-info-700">
                      <li>• <strong>first_name</strong> (required)</li>
                      <li>• <strong>last_name</strong> (required)</li>
                      <li>• <strong>email</strong> (required)</li>
                      <li>
                        • <strong>password</strong> (optional, defaults to
                        <code class="rounded bg-info-100 px-1">Welcome123@</code>)
                      </li>
                      <li>• <strong>phone</strong> (optional)</li>
                      <li>• <strong>job_title</strong> (optional)</li>
                    </ul>
                  </div>
                </div>

                <!-- Preview & edit -->
                <div v-else class="space-y-4">
                  <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="admin-form-section-title">
                      Preview &amp; edit
                      <span class="text-secondary-500">
                        ({{ bulkImport.employees.length }} employee{{
                          bulkImport.employees.length === 1 ? "" : "s"
                        }})
                      </span>
                    </p>
                    <button
                      type="button"
                      class="admin-btn admin-btn--outline admin-btn--sm"
                      @click="clearBulkImport"
                    >
                      <Icon name="heroicons:x-mark" class="h-4 w-4" />
                      Clear
                    </button>
                  </div>

                  <div class="admin-scroll rounded-xl border border-secondary-200">
                    <table class="admin-table">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>First name *</th>
                          <th>Last name *</th>
                          <th>Email *</th>
                          <th>Password</th>
                          <th>Phone</th>
                          <th>Job title</th>
                          <th class="admin-col-actions">Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr
                          v-for="(employee, index) in bulkImport.employees"
                          :key="index"
                        >
                          <td class="text-secondary-500">{{ index + 1 }}</td>
                          <td>
                            <input
                              v-model="employee.first_name"
                              type="text"
                              class="admin-field !h-auto !py-1.5 !text-body-sm"
                              :class="employee.first_name ? '' : '!border-error-400'"
                            />
                          </td>
                          <td>
                            <input
                              v-model="employee.last_name"
                              type="text"
                              class="admin-field !h-auto !py-1.5 !text-body-sm"
                              :class="employee.last_name ? '' : '!border-error-400'"
                            />
                          </td>
                          <td>
                            <input
                              v-model="employee.email"
                              type="email"
                              class="admin-field !h-auto !py-1.5 !text-body-sm"
                              :class="
                                !employee.email || !isValidEmail(employee.email)
                                  ? '!border-error-400'
                                  : ''
                              "
                            />
                          </td>
                          <td>
                            <input
                              v-model="employee.password"
                              type="text"
                              placeholder="Welcome123@"
                              class="admin-field !h-auto !py-1.5 !text-body-sm"
                              :class="
                                employee.password && employee.password.length < 8
                                  ? '!border-error-400'
                                  : ''
                              "
                            />
                          </td>
                          <td>
                            <input
                              v-model="employee.phone"
                              type="tel"
                              class="admin-field !h-auto !py-1.5 !text-body-sm"
                            />
                          </td>
                          <td>
                            <input
                              v-model="employee.job_title"
                              type="text"
                              class="admin-field !h-auto !py-1.5 !text-body-sm"
                            />
                          </td>
                          <td class="admin-col-actions">
                            <button
                              type="button"
                              title="Remove"
                              class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-secondary-500 transition hover:bg-error-50 hover:text-error-600"
                              @click="removeEmployee(index)"
                            >
                              <Icon name="heroicons:trash" class="h-4 w-4" />
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <div
                    v-if="bulkImport.errors.length"
                    class="rounded-xl border border-error-200 bg-error-50 p-4"
                  >
                    <p class="text-body font-semibold text-error-900">
                      <Icon
                        name="heroicons:exclamation-triangle"
                        class="mr-1 inline h-5 w-5"
                      />
                      Validation errors ({{ bulkImport.errors.length }})
                    </p>
                    <ul class="text-body mt-2 ml-4 space-y-1 font-medium text-error-800">
                      <li v-for="(error, index) in bulkImport.errors" :key="index">
                        • {{ error }}
                      </li>
                    </ul>
                  </div>
                </div>
              </div>

              <div class="admin-dialog-foot">
                <button
                  type="button"
                  class="admin-btn admin-btn--ghost"
                  @click="closeCreateModal"
                >
                  Cancel
                </button>
                <button
                  type="button"
                  class="admin-btn admin-btn--primary"
                  :disabled="
                    isSubmitting ||
                    !bulkImport.business_account_id ||
                    bulkImport.errors.length > 0
                  "
                  @click="submitBulkImport"
                >
                  <Icon
                    v-if="isSubmitting"
                    name="heroicons:arrow-path"
                    class="h-4 w-4 animate-spin"
                  />
                  {{
                    isSubmitting
                      ? `Creating ${bulkImport.employees.length} employees…`
                      : `Create ${bulkImport.employees.length} employees`
                  }}
                </button>
              </div>
            </template>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Business account details dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="showDetailsModal && selectedUser"
          class="admin-dialog-backdrop"
          @click.self="closeDetailsModal"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="Business account details"
            style="width: min(48rem, 100%); margin-top: 5vh; max-height: 90vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
                >
                  <Icon name="heroicons:identification" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">Business account details</p>
                  <p class="admin-panel-subtitle">{{ selectedUser.email }}</p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="closeDetailsModal"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <div class="admin-panel-body flex-1 space-y-6 overflow-y-auto">
              <div class="admin-form-section">
                <p class="admin-form-section-title">Account information</p>
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div>
                    <p class="text-caption font-medium text-secondary-500">Name</p>
                    <p class="text-body text-secondary-900">
                      {{ selectedUser.first_name }} {{ selectedUser.last_name }}
                    </p>
                  </div>
                  <div>
                    <p class="text-caption font-medium text-secondary-500">Email</p>
                    <p class="text-body text-secondary-900">{{ selectedUser.email }}</p>
                  </div>
                  <div>
                    <p class="text-caption font-medium text-secondary-500">Company</p>
                    <p class="text-body text-secondary-900">
                      {{ selectedUser.company || "N/A" }}
                    </p>
                  </div>
                  <div>
                    <p class="text-caption font-medium text-secondary-500">Phone</p>
                    <p class="text-body text-secondary-900">
                      {{ selectedUser.phone || "N/A" }}
                    </p>
                  </div>
                </div>
              </div>

              <div class="admin-form-section">
                <p class="admin-form-section-title">Quota information</p>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">
                  <div>
                    <p class="text-caption font-medium text-secondary-500">Total quota</p>
                    <p class="text-body font-semibold text-secondary-900">
                      {{ selectedUser.total_account_slots || 0 }}
                    </p>
                  </div>
                  <div>
                    <p class="text-caption font-medium text-secondary-500">Employees</p>
                    <p class="text-body font-semibold text-secondary-900">
                      {{ selectedUser.employees_count || 0 }}
                    </p>
                  </div>
                  <div>
                    <p class="text-caption font-medium text-secondary-500">Available</p>
                    <p class="text-body font-semibold text-secondary-900">
                      {{ selectedUser.quota_info?.available_quota || 0 }}
                    </p>
                  </div>
                  <div>
                    <p class="text-caption font-medium text-secondary-500">Total cards</p>
                    <p class="text-body font-semibold text-secondary-900">
                      {{ selectedUser.cards_count || 0 }}
                    </p>
                  </div>
                </div>
              </div>

              <div
                v-if="selectedUser.employees && selectedUser.employees.length > 0"
                class="admin-form-section"
              >
                <p class="admin-form-section-title">
                  Employees ({{ selectedUser.employees.length }})
                </p>
                <div class="mt-4 space-y-3">
                  <div
                    v-for="employee in selectedUser.employees"
                    :key="employee.id"
                    class="rounded-xl border border-secondary-200 bg-secondary-50 p-4"
                  >
                    <div class="flex flex-wrap items-center justify-between gap-2">
                      <div class="min-w-0">
                        <p class="truncate text-body font-medium text-secondary-900">
                          {{ employee.first_name }} {{ employee.last_name }}
                        </p>
                        <p class="truncate text-caption text-secondary-500">
                          {{ employee.email }}
                        </p>
                      </div>
                      <div class="flex items-center gap-2">
                        <AdminStatusBadge
                          tone="info"
                          plain
                          :label="`${employee.cards_count || 0} card${
                            (employee.cards_count || 0) !== 1 ? 's' : ''
                          }`"
                        />
                        <AdminStatusBadge
                          :tone="employee.subscription_active ? 'success' : 'danger'"
                          :label="employee.subscription_active ? 'Active' : 'Inactive'"
                        />
                      </div>
                    </div>

                    <div
                      v-if="employee.nfc_cards && employee.nfc_cards.length > 0"
                      class="mt-3 border-l-2 border-info-200 pl-4"
                    >
                      <p class="text-caption font-medium text-secondary-600">
                        NFC cards
                      </p>
                      <div class="mt-1 space-y-1">
                        <div
                          v-for="card in employee.nfc_cards"
                          :key="card.id"
                          class="text-caption text-secondary-500"
                        >
                          • {{ card.nfc_card_id }} - {{ card.card_owner }}
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
                class="admin-btn admin-btn--outline"
                @click="closeDetailsModal"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Shared quota dialog -->
    <AdminConfirmDialog
      v-model="quotaOpen"
      title="Manage quota"
      :message="
        quotaUser
          ? `Set a new total quota for ${quotaUser.first_name} ${quotaUser.last_name}.`
          : ''
      "
      confirm-label="Update quota"
      :loading="quotaBusy"
      @confirm="runQuotaUpdate"
    >
      <div class="mt-4">
        <label class="admin-label">Total quota</label>
        <input
          v-model="quotaValue"
          type="number"
          min="0"
          class="admin-field"
          placeholder="e.g. 50"
        />
        <p class="admin-hint">
          Maximum accounts (incl. owner) and NFC cards this business can hold.
        </p>
      </div>
    </AdminConfirmDialog>

    <!-- Account status dialog -->
    <AdminConfirmDialog
      v-model="statusOpen"
      :title="statusTarget ? 'Activate account' : 'Deactivate account'"
      :message="statusMessage"
      :confirm-label="statusTarget ? 'Activate' : 'Deactivate'"
      :tone="statusTarget ? 'primary' : 'danger'"
      :loading="statusBusy"
      @confirm="runStatusUpdate"
      @cancel="revertPendingStatus"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";

definePageMeta({
  middleware: ["auth", "admin"],
  layout: "admin-management",
});

const { $api, $toast } = useNuxtApp();

// State
const loading = ref(false);
const error = ref("");
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

// Quota dialog state
const quotaOpen = ref(false);
const quotaBusy = ref(false);
const quotaValue = ref("");
const quotaUser = ref(null);

// Account status dialog state
const statusOpen = ref(false);
const statusBusy = ref(false);
const statusUser = ref(null);
const statusTarget = ref(true);

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

const hasActiveFilters = computed(
  () => Boolean(filters.value.search.trim()) || Boolean(filters.value.status)
);

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
let latestRequestId = 0;

// Fetch business users
const fetchBusinessUsers = async (page = pagination.value.current_page || 1) => {
  const requestId = ++latestRequestId;
  loading.value = true;
  error.value = "";
  try {
    const queryParams = new URLSearchParams({
      page: page,
      per_page: pagination.value.per_page,
      ...Object.fromEntries(
        Object.entries(filters.value).filter(([_, v]) => v !== "")
      ),
    });

    const response = await $api.get(`/admin/business-users?${queryParams}`);

    if (!response?.success) {
      throw new Error(response?.message || "Failed to load business accounts.");
    }

    if (requestId !== latestRequestId) return;

    businessUsers.value = response.data || [];
    pagination.value = {
      current_page: response.current_page || 1,
      last_page: response.last_page || 1,
      per_page: response.per_page || pagination.value.per_page,
      total: response.total || 0,
    };
  } catch (requestError) {
    console.error("Error fetching business users:", requestError);
    if (requestId === latestRequestId) {
      error.value =
        requestError.response?.data?.message ||
        requestError.message ||
        "Failed to load business accounts.";
      $toast.error(error.value);
    }
  } finally {
    if (requestId === latestRequestId) {
      loading.value = false;
    }
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
    $toast.error("Failed to load user details.");
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
      $toast.success("Employee account created successfully!");
      closeCreateModal();
      fetchBusinessUsers();
      fetchStatistics();
    }
  } catch (error) {
    console.error("Error creating employee:", error);
    $toast.error(
      error.response?.data?.message || "Failed to create employee account."
    );
  } finally {
    isSubmitting.value = false;
  }
};

// Manage quota — open the dialog, then submit through runQuotaUpdate
const manageQuota = (user) => {
  quotaUser.value = user;
  quotaValue.value = String(user.total_account_slots || 0);
  quotaOpen.value = true;
};

const runQuotaUpdate = async () => {
  const quota = parseInt(quotaValue.value);
  if (isNaN(quota) || quota < 0) {
    $toast.error("Please enter a valid number.");
    return;
  }

  quotaBusy.value = true;
  try {
    const response = await $api.post(
      `/admin/business-users/${quotaUser.value.id}/update-quota`,
      {
        total_account_slots: quota,
      }
    );

    if (response.success) {
      $toast.success("Quota updated successfully!");
      quotaOpen.value = false;
      fetchBusinessUsers();
    }
  } catch (error) {
    console.error("Error updating quota:", error);
    $toast.error("Failed to update quota.");
  } finally {
    quotaBusy.value = false;
  }
};

// Account status — the radio toggle writes to the shared admin user endpoint,
// which also syncs the subscription row and entitlement flag.
const statusMessage = computed(() => {
  if (!statusUser.value) return "";
  const name =
    [statusUser.value.first_name, statusUser.value.last_name]
      .filter(Boolean)
      .join(" ") || statusUser.value.email;

  return statusTarget.value
    ? `Restore the business plan entitlement for ${name}.`
    : `${name}'s business plan entitlement will be suspended. The account can still sign in, but plan features stay locked until it is activated again.`;
});

const requestStatusChange = (user, isActive) => {
  statusUser.value = user;
  statusTarget.value = isActive;
  statusOpen.value = true;
};

// The radio already moved locally, so a dismissal has to put it back.
const revertPendingStatus = () => {
  if (statusUser.value) {
    statusUser.value.subscription_active = !statusTarget.value;
  }
  statusUser.value = null;
};

const runStatusUpdate = async () => {
  const user = statusUser.value;
  const isActive = statusTarget.value;
  if (!user) return;

  statusBusy.value = true;
  try {
    const response = await $api.put(`/admin/users/${user.id}`, {
      subscription_active: isActive,
    });

    if (!response?.success) {
      throw new Error(response?.message || "Failed to update account status.");
    }

    $toast.success(
      `${user.first_name} ${user.last_name}'s account has been ${
        isActive ? "activated" : "deactivated"
      }.`
    );
    statusUser.value = null;
    statusOpen.value = false;
    fetchBusinessUsers();
    fetchStatistics();
  } catch (error) {
    console.error("Error updating account status:", error);
    user.subscription_active = !isActive;
    statusUser.value = null;
    statusOpen.value = false;
    $toast.error(
      error.response?.data?.message ||
        error.message ||
        "Failed to update account status."
    );
  } finally {
    statusBusy.value = false;
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
      $toast.error(
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
    $toast.error("Failed to generate template file.");
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
    $toast.error("Please select a business account.");
    return;
  }

  if (bulkImport.value.employees.length === 0) {
    $toast.error("No employees to import.");
    return;
  }

  validateBulkImport();
  if (bulkImport.value.errors.length > 0) {
    $toast.error("Please fix all validation errors before submitting.");
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
      $toast.success(
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

        $toast.error(`Backend validation failed: ${backendErrors.join("; ")}`);
      } else if (errorData.message) {
        $toast.error(`Backend error: ${errorData.message}`);
      } else {
        $toast.error("Validation failed. Please check the error details below.");
      }
    } else {
      $toast.error(
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
  clearTimeout(searchTimeout);
  filters.value = {
    search: "",
    status: "",
  };
  pagination.value.current_page = 1;
  fetchBusinessUsers(1);
};

const applyFilters = () => {
  clearTimeout(searchTimeout);
  pagination.value.current_page = 1;
  fetchBusinessUsers(1);
};

// Debounce search
const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 500);
};

// Change page
const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchBusinessUsers(page);
  }
};

const changeItemsPerPage = (perPage) => {
  pagination.value.per_page = Number(perPage) || 20;
  pagination.value.current_page = 1;
  fetchBusinessUsers(1);
};

// Initialize
onMounted(() => {
  fetchBusinessUsers();
  fetchStatistics();
});
</script>
