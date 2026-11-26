<!-- pages/AdminManagement/nfc-cards.vue -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Card Fulfilment"
      eyebrow-icon="heroicons:credit-card"
      title="NFC Card Management"
      description="Register physical NFC cards, verify payments and follow every order from purchase through to activation."
    >
      <template #actions>
        <button
          type="button"
          class="admin-btn admin-btn--primary"
          @click="showRegisterModal = true"
        >
          <Icon name="heroicons:plus" class="h-4 w-4" />
          Register card
        </button>
      </template>
    </AdminPageHeader>

    <!-- Fulfilment summary -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <AdminStatTile
        label="Total cards"
        :value="pagination.total"
        icon="heroicons:credit-card"
        hint="Matching current filters"
      />
      <AdminStatTile
        label="Activated"
        :value="
          nfcCards.filter((c) => ['active', 'delivered'].includes(c.status))
            .length
        "
        icon="heroicons:check-badge"
        hint="On this page"
      />
      <AdminStatTile
        label="In transit"
        :value="
          nfcCards.filter((c) => ['processing', 'shipped'].includes(c.status))
            .length
        "
        icon="heroicons:truck"
        hint="On this page"
      />
      <AdminStatTile
        label="Awaiting action"
        :value="
          nfcCards.filter((c) =>
            [
              'pending_payment',
              'awaiting_payment_verification',
              'payment_verified',
            ].includes(c.status)
          ).length
        "
        icon="heroicons:clock"
        hint="On this page"
      />
    </div>

    <AdminFilterBar @submit="handleFilterChange">
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
            placeholder="Search by card ID, owner or email…"
            class="admin-field !pl-9"
            maxlength="150"
            @input="handleSearch"
          />
        </div>
      </template>

      <div class="min-w-[12rem]">
        <label class="admin-label">Status</label>
        <select
          v-model="filters.status"
          class="admin-field"
          @change="handleFilterChange"
        >
          <option value="">All statuses</option>
          <option value="pending_payment">Awaiting Payment</option>
          <option value="awaiting_payment_verification">Awaiting Payment Verification</option>
          <option value="payment_verified">Payment Verified</option>
          <option value="processing">Processing</option>
          <option value="shipped">Shipped</option>
          <option value="delivered">Delivered</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="expired">Expired</option>
          <option value="cancelled">Cancelled</option>
          <option value="replacement">Replacement</option>
        </select>
      </div>

      <div class="min-w-[10rem]">
        <label class="admin-label">Subscription plan</label>
        <select
          v-model="filters.subscription_plan"
          class="admin-field"
          @change="handleFilterChange"
        >
          <option value="">All plans</option>
          <option value="basic">Basic</option>
          <option value="premium">Premium</option>
          <option value="business">Business</option>
        </select>
      </div>

      <div class="min-w-[10rem]">
        <label class="admin-label">Sort by</label>
        <div class="flex gap-2">
          <select
            v-model="filters.sort_by"
            class="admin-field"
            @change="handleSortFieldChange"
          >
            <option value="created_at">Date Created</option>
            <option value="card_owner">Card Owner</option>
            <option value="nfc_card_id">NFC ID</option>
            <option value="purchase_amount">Amount</option>
          </select>
          <button
            type="button"
            class="admin-btn admin-btn--outline admin-btn--icon shrink-0"
            :title="`Sort ${filters.sort_order === 'asc' ? 'ascending' : 'descending'}`"
            @click="toggleSortOrder"
          >
            <Icon
              :name="filters.sort_order === 'asc' ? 'heroicons:arrow-small-up' : 'heroicons:arrow-small-down'"
              class="h-4 w-4"
            />
          </button>
        </div>
      </div>

      <template #actions>
        <span class="w-full text-right text-body-sm text-secondary-500 sm:w-auto">
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
      <template #title>Card orders</template>
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
          <span class="admin-skeleton h-11 w-8 shrink-0 rounded-md"></span>
          <span class="admin-skeleton h-4 w-1/4"></span>
          <span class="admin-skeleton ml-auto h-4 w-24"></span>
          <span class="admin-skeleton h-4 w-16"></span>
        </div>
      </div>

      <!-- Empty -->
      <AdminEmptyState
        v-else-if="!nfcCards.length"
        icon="heroicons:credit-card"
        title="No NFC cards found"
        description="Register a card, or adjust the filters above to see matching orders."
      />

      <!-- Table -->
      <div v-else class="admin-scroll">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Card</th>
              <th>Member</th>
              <th>Status</th>
              <th>Purchase</th>
              <th class="admin-col-actions">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="card in nfcCards" :key="card.id">
              <td>
                <div class="flex items-center gap-3">
                  <span
                    class="inline-flex h-11 w-8 shrink-0 items-center justify-center rounded-md bg-accent-100 text-accent-700 ring-1 ring-inset ring-accent-200"
                  >
                    <Icon name="heroicons:credit-card" class="h-5 w-5" />
                  </span>
                  <div class="min-w-0">
                    <div class="truncate text-body font-semibold text-secondary-900">
                      {{ card.card_owner }}
                    </div>
                    <div class="truncate font-mono text-caption text-secondary-500">
                      {{ card.nfc_card_id }}
                    </div>
                    <div class="text-micro text-secondary-400">
                      Created {{ formatDate(card.created_at) }}
                    </div>
                  </div>
                </div>
              </td>

              <td>
                <div class="flex items-center gap-3">
                  <UserAvatar
                    :src="card.user?.profile_image"
                    :name="card.user?.full_name"
                    class="h-9 w-9 shrink-0 rounded-full ring-1 ring-secondary-200"
                  />
                  <div class="min-w-0">
                    <div class="truncate text-body font-semibold text-secondary-900">
                      {{ card.user?.full_name }}
                    </div>
                    <div class="truncate text-caption text-secondary-500">
                      {{ card.user?.email }}
                    </div>
                  </div>
                </div>
              </td>

              <td>
                <AdminStatusBadge
                  :tone="
                    {
                      pending_payment: 'warning',
                      awaiting_payment_verification: 'warning',
                      payment_verified: 'info',
                      processing: 'info',
                      shipped: 'info',
                      delivered: 'success',
                      active: 'success',
                      inactive: 'neutral',
                      expired: 'danger',
                      cancelled: 'danger',
                      rejected: 'danger',
                      replacement: 'brass',
                    }[card.status] || 'neutral'
                  "
                  :label="getCardStatusLabel(card.status)"
                />
                <div
                  v-if="card.tracking_number"
                  class="text-helper mt-1 text-secondary-500"
                >
                  Tracking {{ card.tracking_number }}
                </div>
              </td>

              <td>
                <div class="text-body font-semibold text-secondary-900">
                  ${{ card.purchase_amount }}
                </div>
                <div class="text-caption capitalize text-secondary-500">
                  {{
                    String(card.subscription_plan || "")
                      .replace(/_/g, " ")
                      .replace(/\b\w/g, (c) => c.toUpperCase()) || "N/A"
                  }}
                </div>
                <div class="text-micro text-secondary-400">
                  {{ formatDate(card.purchase_date) }}
                </div>
              </td>

              <td class="admin-col-actions">
                <div class="flex flex-wrap items-center justify-end gap-1.5">
                  <!-- awaiting_payment_verification: Verify / Reject / Proof -->
                  <template
                    v-if="card.status === 'awaiting_payment_verification'"
                  >
                    <button
                      type="button"
                      :disabled="actionLoading[card.id]?.verify"
                      class="inline-flex items-center rounded-lg bg-success-50 px-2.5 py-1.5 text-caption font-semibold text-success-700 transition hover:bg-success-100 disabled:opacity-50"
                      title="Verify payment proof"
                      @click="verifyNfcCardPayment(card)"
                    >
                      <Icon
                        v-if="actionLoading[card.id]?.verify"
                        name="heroicons:arrow-path"
                        class="mr-1 h-3.5 w-3.5 animate-spin"
                      />
                      <Icon
                        v-else
                        name="heroicons:check-circle"
                        class="mr-0.5 h-3.5 w-3.5"
                      />
                      Verify
                    </button>
                    <button
                      type="button"
                      :disabled="actionLoading[card.id]?.reject"
                      class="inline-flex items-center rounded-lg bg-error-50 px-2.5 py-1.5 text-caption font-semibold text-error-700 transition hover:bg-error-100 disabled:opacity-50"
                      title="Reject payment proof"
                      @click="rejectNfcCardPayment(card)"
                    >
                      <Icon
                        v-if="actionLoading[card.id]?.reject"
                        name="heroicons:arrow-path"
                        class="mr-1 h-3.5 w-3.5 animate-spin"
                      />
                      <Icon
                        v-else
                        name="heroicons:x-circle"
                        class="mr-0.5 h-3.5 w-3.5"
                      />
                      Reject
                    </button>
                    <a
                      v-if="card.transaction?.payment_proof_url"
                      :href="card.transaction.payment_proof_url"
                      target="_blank"
                      class="inline-flex items-center rounded-lg bg-secondary-100 px-2.5 py-1.5 text-caption font-semibold text-secondary-700 transition hover:bg-secondary-200"
                      title="View payment proof"
                    >
                      <Icon name="heroicons:eye" class="mr-0.5 h-3.5 w-3.5" />
                      Proof
                    </a>
                  </template>

                  <!-- payment_verified: Mark Processing / Cancel -->
                  <template v-else-if="card.status === 'payment_verified'">
                    <button
                      type="button"
                      :disabled="actionLoading[card.id]?.processing"
                      class="inline-flex items-center rounded-lg bg-primary-50 px-2.5 py-1.5 text-caption font-semibold text-primary-700 transition hover:bg-primary-100 disabled:opacity-50"
                      title="Mark order as processing"
                      @click="markNfcCardProcessing(card)"
                    >
                      <Icon
                        v-if="actionLoading[card.id]?.processing"
                        name="heroicons:arrow-path"
                        class="mr-1 h-3.5 w-3.5 animate-spin"
                      />
                      <Icon
                        v-else
                        name="heroicons:arrow-path"
                        class="mr-0.5 h-3.5 w-3.5"
                      />
                      Mark Processing
                    </button>
                    <button
                      type="button"
                      :disabled="actionLoading[card.id]?.cancel"
                      class="inline-flex items-center rounded-lg bg-error-50 px-2.5 py-1.5 text-caption font-semibold text-error-700 transition hover:bg-error-100 disabled:opacity-50"
                      title="Cancel order"
                      @click="cancelNfcCard(card)"
                    >
                      <Icon
                        v-if="actionLoading[card.id]?.cancel"
                        name="heroicons:arrow-path"
                        class="mr-1 h-3.5 w-3.5 animate-spin"
                      />
                      <Icon
                        v-else
                        name="heroicons:no-symbol"
                        class="mr-0.5 h-3.5 w-3.5"
                      />
                      Cancel
                    </button>
                  </template>

                  <!-- processing: Mark Shipped / Cancel -->
                  <template v-else-if="card.status === 'processing'">
                    <button
                      type="button"
                      :disabled="actionLoading[card.id]?.shipped"
                      class="inline-flex items-center rounded-lg bg-info-50 px-2.5 py-1.5 text-caption font-semibold text-info-700 transition hover:bg-info-100 disabled:opacity-50"
                      title="Mark card as shipped"
                      @click="openMarkShippedModal(card)"
                    >
                      <Icon
                        v-if="actionLoading[card.id]?.shipped"
                        name="heroicons:arrow-path"
                        class="mr-1 h-3.5 w-3.5 animate-spin"
                      />
                      <Icon
                        v-else
                        name="heroicons:truck"
                        class="mr-0.5 h-3.5 w-3.5"
                      />
                      Mark Shipped
                    </button>
                    <button
                      type="button"
                      :disabled="actionLoading[card.id]?.cancel"
                      class="inline-flex items-center rounded-lg bg-error-50 px-2.5 py-1.5 text-caption font-semibold text-error-700 transition hover:bg-error-100 disabled:opacity-50"
                      title="Cancel order"
                      @click="cancelNfcCard(card)"
                    >
                      <Icon
                        v-if="actionLoading[card.id]?.cancel"
                        name="heroicons:arrow-path"
                        class="mr-1 h-3.5 w-3.5 animate-spin"
                      />
                      <Icon
                        v-else
                        name="heroicons:no-symbol"
                        class="mr-0.5 h-3.5 w-3.5"
                      />
                      Cancel
                    </button>
                  </template>

                  <!-- shipped: end user confirms receipt — admin has no Mark Delivered action -->
                  <template v-else-if="card.status === 'shipped'">
                    <span
                      class="inline-flex items-center gap-1 rounded-lg bg-secondary-100 px-2.5 py-1.5 text-caption font-medium italic text-secondary-600"
                      title="Admin cannot mark delivered — the end user confirms receipt in Card Management"
                    >
                      <Icon name="heroicons:clock" class="h-3.5 w-3.5" />
                      Waiting for user receipt
                    </span>
                  </template>

                  <!-- delivered / active / inactive / expired: Toggle activate/deactivate -->
                  <template
                    v-else-if="
                      ['delivered', 'active', 'inactive', 'expired'].includes(
                        card.status
                      )
                    "
                  >
                    <button
                      type="button"
                      :disabled="actionLoading[card.id]?.toggle"
                      class="inline-flex items-center rounded-lg px-2.5 py-1.5 text-caption font-semibold transition disabled:opacity-50"
                      :class="
                        ['active', 'delivered'].includes(card.status)
                          ? 'bg-warning-50 text-warning-700 hover:bg-warning-100'
                          : 'bg-success-50 text-success-700 hover:bg-success-100'
                      "
                      :title="
                        ['active', 'delivered'].includes(card.status)
                          ? 'Deactivate card'
                          : 'Activate card'
                      "
                      @click="toggleCardStatus(card)"
                    >
                      <Icon
                        v-if="actionLoading[card.id]?.toggle"
                        name="heroicons:arrow-path"
                        class="mr-1 h-3.5 w-3.5 animate-spin"
                      />
                      <Icon
                        v-else
                        :name="
                          ['active', 'delivered'].includes(card.status)
                            ? 'heroicons:pause'
                            : 'heroicons:play'
                        "
                        class="mr-0.5 h-3.5 w-3.5"
                      />
                      {{
                        ["active", "delivered"].includes(card.status)
                          ? "Deactivate"
                          : "Activate"
                      }}
                    </button>
                  </template>

                  <!-- pending_payment: show existing payment proof if any -->
                  <template
                    v-if="
                      card.transaction?.payment_proof_url &&
                      card.status === 'pending_payment'
                    "
                  >
                    <a
                      :href="card.transaction.payment_proof_url"
                      target="_blank"
                      class="inline-flex items-center rounded-lg bg-secondary-100 px-2.5 py-1.5 text-caption font-semibold text-secondary-700 transition hover:bg-secondary-200"
                      title="View existing payment proof"
                    >
                      <Icon name="heroicons:eye" class="mr-0.5 h-3.5 w-3.5" />
                      Proof
                    </a>
                  </template>

                  <!-- Global actions -->
                  <button
                    type="button"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-secondary-500 transition hover:bg-secondary-100 hover:text-primary-700"
                    title="View full details"
                    @click="viewCard(card)"
                  >
                    <Icon name="heroicons:eye" class="h-4 w-4" />
                  </button>
                  <button
                    type="button"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-secondary-500 transition hover:bg-warning-50 hover:text-warning-700"
                    title="Edit card details"
                    @click="editCard(card)"
                  >
                    <Icon name="heroicons:pencil" class="h-4 w-4" />
                  </button>
                  <button
                    v-if="card.status !== 'cancelled'"
                    type="button"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-secondary-500 transition hover:bg-error-50 hover:text-error-600"
                    title="Delete card record"
                    @click="deleteCard(card)"
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
      v-if="nfcCards.length > 0"
      :current-page="pagination.current_page"
      :last-page="pagination.last_page"
      :per-page="pagination.per_page"
      :total="pagination.total"
      item-label="cards"
      @page-change="changePage"
      @per-page-change="changeItemsPerPage"
    />

    <!-- Register NFC Card dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="showRegisterModal"
          class="admin-dialog-backdrop"
          @click.self="closeModal"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="Register NFC card"
            style="width: min(44rem, 100%); margin-top: 5vh; max-height: 90vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
                >
                  <Icon name="heroicons:credit-card" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">Register NFC card</p>
                  <p class="admin-panel-subtitle">
                    Link a physical card to a member account
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
                <!-- Assignment -->
                <div class="admin-form-section">
                  <p class="admin-form-section-title">Card assignment</p>
                  <p class="admin-form-section-desc">
                    Choose the member and enter the identifier printed on the
                    chip.
                  </p>
                  <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                      <label class="admin-label">
                        Member <span class="text-error-600">*</span>
                      </label>
                      <select v-model="form.user_id" required class="admin-field">
                        <option value="">Select member</option>
                        <option
                          v-for="user in availableUsers"
                          :key="user.id"
                          :value="user.id"
                        >
                          {{ user.full_name }} ({{ user.email }})
                        </option>
                      </select>
                    </div>
                    <div>
                      <label class="admin-label">
                        NFC card ID <span class="text-error-600">*</span>
                      </label>
                      <input
                        v-model="form.nfc_card_id"
                        type="text"
                        required
                        maxlength="16"
                        placeholder="e.g., NFC-ABC123DEF456"
                        class="admin-field font-mono"
                        :class="formErrors.nfc_card_id ? '!border-error-400' : ''"
                        @input="validateNfcCardId"
                      />
                      <div class="mt-1 flex items-start justify-between gap-3">
                        <p
                          v-if="formErrors.nfc_card_id"
                          class="text-helper text-error-600"
                        >
                          {{ formErrors.nfc_card_id }}
                        </p>
                        <p v-else class="text-helper text-secondary-500">
                          Identifier printed on the physical card
                        </p>
                        <span class="text-helper shrink-0 text-secondary-400">
                          {{ form.nfc_card_id.length }}/16
                        </span>
                      </div>
                    </div>
                    <div>
                      <label class="admin-label">
                        Card owner <span class="text-error-600">*</span>
                      </label>
                      <input
                        v-model="form.card_owner"
                        type="text"
                        required
                        maxlength="100"
                        placeholder="Full name on the card"
                        class="admin-field"
                        :class="formErrors.card_owner ? '!border-error-400' : ''"
                        @input="validateCardOwner"
                      />
                      <div class="mt-1 flex justify-end">
                        <span class="text-helper text-secondary-400">
                          {{ form.card_owner.length }}/100
                        </span>
                      </div>
                      <p
                        v-if="formErrors.card_owner"
                        class="text-helper text-error-600"
                      >
                        {{ formErrors.card_owner }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Billing & contact -->
                <div class="admin-form-section">
                  <p class="admin-form-section-title">Billing &amp; contact</p>
                  <p class="admin-form-section-desc">
                    Where the card ships and how the customer can be reached.
                  </p>
                  <div class="mt-4 space-y-4">
                    <div>
                      <label class="admin-label">
                        Billing address <span class="text-error-600">*</span>
                      </label>
                      <textarea
                        v-model="form.billing_address"
                        required
                        rows="3"
                        placeholder="Full billing address"
                        class="admin-field"
                      ></textarea>
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                      <div>
                        <label class="admin-label">
                          Contact number <span class="text-error-600">*</span>
                        </label>
                        <PhoneInput
                          v-model="form.contact_number"
                          :required="true"
                          :has-error="!!formErrors.contact_number"
                          placeholder="Phone number"
                          @blur="formatContactNumberOnBlur"
                        />
                        <p
                          v-if="formErrors.contact_number"
                          class="text-helper mt-1 text-error-600"
                        >
                          {{ formErrors.contact_number }}
                        </p>
                      </div>
                      <div>
                        <label class="admin-label">Shipping address</label>
                        <textarea
                          v-model="form.shipping_address"
                          rows="2"
                          placeholder="Shipping address (if different from billing)"
                          class="admin-field"
                        ></textarea>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Order details -->
                <div class="admin-form-section">
                  <p class="admin-form-section-title">Order details</p>
                  <p class="admin-form-section-desc">
                    Plan, amount paid and any internal notes for this order.
                  </p>
                  <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                      <label class="admin-label">
                        Subscription plan <span class="text-error-600">*</span>
                      </label>
                      <select
                        v-model="form.subscription_plan"
                        required
                        class="admin-field"
                      >
                        <option value="basic">Basic ($9/month)</option>
                        <option value="premium">Premium ($19/month)</option>
                        <option value="business">Business ($49/month)</option>
                      </select>
                    </div>
                    <div>
                      <label class="admin-label">
                        Purchase amount <span class="text-error-600">*</span>
                      </label>
                      <input
                        v-model.number="form.purchase_amount"
                        type="number"
                        min="0"
                        step="1"
                        required
                        placeholder="0"
                        class="admin-field"
                        :class="
                          formErrors.purchase_amount ? '!border-error-400' : ''
                        "
                        @input="validatePurchaseAmount"
                        @change="validatePurchaseAmount"
                      />
                      <p
                        v-if="formErrors.purchase_amount"
                        class="text-helper mt-1 text-error-600"
                      >
                        {{ formErrors.purchase_amount }}
                      </p>
                    </div>
                    <div>
                      <label class="admin-label">Payment method</label>
                      <select v-model="form.payment_method" class="admin-field">
                        <option value="">Select payment method</option>
                        <option value="stripe">Stripe</option>
                        <option value="razorpay">Razorpay</option>
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                      </select>
                    </div>
                    <div>
                      <label class="admin-label">Notes</label>
                      <textarea
                        v-model="form.notes"
                        rows="2"
                        placeholder="Additional notes or special instructions"
                        class="admin-field"
                      ></textarea>
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
                  Register card
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Edit NFC Card dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="showEditModal"
          class="admin-dialog-backdrop"
          @click.self="closeEditModal"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="Edit NFC card"
            style="width: min(44rem, 100%); margin-top: 5vh; max-height: 90vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
                >
                  <Icon name="heroicons:pencil-square" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">Edit NFC card</p>
                  <p class="admin-panel-subtitle">
                    {{ selectedCard?.nfc_card_id }}
                  </p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="closeEditModal"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <form
              class="flex min-h-0 flex-1 flex-col"
              @submit.prevent="handleEditSubmit"
            >
              <div class="admin-panel-body flex-1 space-y-6 overflow-y-auto">
                <!-- Ownership -->
                <div class="admin-form-section">
                  <p class="admin-form-section-title">Ownership &amp; billing</p>
                  <p class="admin-form-section-desc">
                    Customer-facing details stored against this card.
                  </p>
                  <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                      <label class="admin-label">Card owner</label>
                      <input
                        v-model="editForm.card_owner"
                        type="text"
                        required
                        class="admin-field"
                      />
                    </div>
                    <div>
                      <label class="admin-label">Contact number</label>
                      <PhoneInput
                        v-model="editForm.contact_number"
                        placeholder="Phone number"
                        @blur="formatEditContactNumberOnBlur"
                      />
                    </div>
                    <div class="md:col-span-2">
                      <label class="admin-label">Billing address</label>
                      <textarea
                        v-model="editForm.billing_address"
                        required
                        rows="3"
                        class="admin-field"
                      ></textarea>
                    </div>
                  </div>
                </div>

                <!-- Order status -->
                <div class="admin-form-section">
                  <p class="admin-form-section-title">Order status</p>
                  <p class="admin-form-section-desc">
                    Track fulfilment progress and shipping information.
                  </p>
                  <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                      <label class="admin-label">Status</label>
                      <select v-model="editForm.status" class="admin-field">
                        <option value="pending_payment">Awaiting Payment</option>
                        <option value="awaiting_payment_verification">Awaiting Payment Verification</option>
                        <option value="payment_verified">Payment Verified</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="expired">Expired</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="replacement">Replacement</option>
                      </select>
                    </div>
                    <div>
                      <label class="admin-label">Courier</label>
                      <input
                        v-model="editForm.courier"
                        type="text"
                        placeholder="Example: Poslaju, J&T, DHL, Ninja Van"
                        class="admin-field"
                      />
                    </div>
                    <div>
                      <label class="admin-label">Tracking no.</label>
                      <input
                        v-model="editForm.tracking_number"
                        type="text"
                        placeholder="Shipping tracking number"
                        class="admin-field"
                      />
                    </div>
                    <div>
                      <label class="admin-label">Shipped date</label>
                      <input
                        v-model="editForm.shipped_date"
                        type="date"
                        class="admin-field"
                      />
                    </div>
                    <div>
                      <label class="admin-label">Delivered date</label>
                      <input
                        v-model="editForm.delivered_date"
                        type="date"
                        class="admin-field"
                      />
                    </div>
                    <div v-if="editForm.status === 'cancelled'" class="md:col-span-2">
                      <label class="admin-label">Cancellation reason</label>
                      <textarea
                        v-model="editForm.cancellation_reason"
                        rows="2"
                        placeholder="Explain why this order is being cancelled. This will be shown to the user."
                        class="admin-field"
                      ></textarea>
                    </div>
                    <div class="md:col-span-2">
                      <label class="admin-label">Admin notes</label>
                      <textarea
                        v-model="editForm.notes"
                        rows="2"
                        placeholder="Internal notes (not shown to user)"
                        class="admin-field"
                      ></textarea>
                    </div>
                  </div>
                </div>

                <!-- Public profile URL (NFC encoding) -->
                <div
                  v-if="selectedCard && getCardPublicProfileUrl(selectedCard)"
                  class="admin-form-section"
                >
                  <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                      <p class="admin-form-section-title">Public profile URL</p>
                      <p class="admin-form-section-desc">
                        Copy this URL and encode it into the NFC tag chip.
                      </p>
                    </div>
                    <AdminStatusBadge tone="brass" label="For NFC encoding" />
                  </div>
                  <div class="mt-3 flex items-stretch gap-2">
                    <input
                      :value="getCardPublicProfileUrl(selectedCard, true)"
                      readonly
                      class="admin-field flex-1 font-mono text-caption"
                    />
                    <button
                      type="button"
                      class="admin-btn admin-btn--outline admin-btn--icon shrink-0"
                      title="Copy URL"
                      @click="
                        copyToClipboard(getCardPublicProfileUrl(selectedCard, true))
                      "
                    >
                      <Icon name="heroicons:document-duplicate" class="h-4 w-4" />
                    </button>
                    <button
                      type="button"
                      class="admin-btn admin-btn--outline admin-btn--icon shrink-0"
                      title="Open in new tab"
                      @click="
                        openInNewTab(getCardPublicProfileUrl(selectedCard, true))
                      "
                    >
                      <Icon
                        name="heroicons:arrow-top-right-on-square"
                        class="h-4 w-4"
                      />
                    </button>
                  </div>
                </div>
              </div>

              <div class="admin-dialog-foot">
                <button
                  type="button"
                  class="admin-btn admin-btn--ghost"
                  @click="closeEditModal"
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
                  Update card
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Mark Shipped dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="showMarkShippedModal"
          class="admin-dialog-backdrop"
          @click.self="showMarkShippedModal = false"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="Mark card as shipped"
            style="width: min(34rem, 100%); margin-top: 8vh; max-height: 90vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
                >
                  <Icon name="heroicons:truck" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">Mark card as shipped</p>
                  <p class="admin-panel-subtitle">
                    Record courier and tracking details
                  </p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="showMarkShippedModal = false"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <form
              class="flex min-h-0 flex-1 flex-col"
              @submit.prevent="submitMarkShipped"
            >
              <div class="admin-panel-body flex-1 space-y-6 overflow-y-auto">
                <div
                  v-if="shippedForm.card"
                  class="rounded-xl border border-info-200 bg-info-50 p-4"
                >
                  <p class="text-body font-semibold text-info-900">
                    {{ shippedForm.card.card_owner }}
                  </p>
                  <p class="text-caption mt-0.5 font-mono text-info-700">
                    Card ID:
                    {{
                      shippedForm.card.card_id ||
                      shippedForm.card.nfc_card_id ||
                      shippedForm.card.id
                    }}
                  </p>
                </div>

                <div class="admin-form-section">
                  <p class="admin-form-section-title">Shipping details</p>
                  <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                      <label class="admin-label">
                        Courier <span class="text-error-600">*</span>
                      </label>
                      <select
                        v-model="shippedForm.courier"
                        required
                        class="admin-field"
                      >
                        <option value="">Select courier</option>
                        <option value="Poslaju">Pos Malaysia / Poslaju</option>
                        <option value="J&T">J&T Express</option>
                        <option value="Ninja Van">Ninja Van</option>
                        <option value="DHL">DHL Express</option>
                        <option value="GDex">GDex</option>
                        <option value="Citylink">Citylink Express</option>
                        <option value="Skynet">Skynet</option>
                        <option value="Lalamove">Lalamove</option>
                        <option value="GrabExpress">GrabExpress</option>
                        <option value="Other">Other</option>
                      </select>
                    </div>
                    <div>
                      <label class="admin-label">
                        Tracking no. <span class="text-error-600">*</span>
                      </label>
                      <input
                        v-model="shippedForm.tracking_number"
                        type="text"
                        required
                        placeholder="Example: PL1234567890MY"
                        class="admin-field font-mono"
                      />
                    </div>
                    <div class="md:col-span-2">
                      <label class="admin-label">Date shipped</label>
                      <input
                        v-model="shippedForm.shipped_date"
                        type="date"
                        class="admin-field"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <div class="admin-dialog-foot">
                <button
                  type="button"
                  class="admin-btn admin-btn--ghost"
                  :disabled="actionLoading[shippedForm.card?.id]?.shipped"
                  @click="showMarkShippedModal = false"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="admin-btn admin-btn--primary"
                  :disabled="actionLoading[shippedForm.card?.id]?.shipped"
                >
                  <Icon
                    v-if="actionLoading[shippedForm.card?.id]?.shipped"
                    name="heroicons:arrow-path"
                    class="h-4 w-4 animate-spin"
                  />
                  Mark as shipped
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Card Details dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="showCardModal"
          class="admin-dialog-backdrop"
          @click.self="showCardModal = false"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="NFC card details"
            style="width: min(46rem, 100%); margin-top: 5vh; max-height: 90vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
                >
                  <Icon name="heroicons:identification" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">NFC card details</p>
                  <p class="admin-panel-subtitle">
                    {{ selectedCard?.nfc_card_id }}
                  </p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="showCardModal = false"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <div
              v-if="selectedCard"
              class="admin-panel-body flex-1 space-y-6 overflow-y-auto"
            >
              <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                <div>
                  <p class="admin-form-section-title">Card information</p>
                  <dl class="mt-4 space-y-3">
                    <div>
                      <dt class="text-caption font-medium text-secondary-500">
                        NFC card ID
                      </dt>
                      <dd class="font-mono text-body text-secondary-900">
                        {{ selectedCard.nfc_card_id }}
                      </dd>
                    </div>
                    <div>
                      <dt class="text-caption font-medium text-secondary-500">
                        Card owner
                      </dt>
                      <dd class="text-body text-secondary-900">
                        {{ selectedCard.card_owner }}
                      </dd>
                    </div>
                    <div>
                      <dt class="text-caption font-medium text-secondary-500">
                        Member
                      </dt>
                      <dd class="text-body text-secondary-900">
                        {{ selectedCard.user?.full_name || "N/A" }}
                        <span v-if="selectedCard.user?.email" class="block text-caption text-secondary-500">
                          {{ selectedCard.user.email }}
                        </span>
                      </dd>
                    </div>
                    <div>
                      <dt class="text-caption font-medium text-secondary-500">
                        Status
                      </dt>
                      <dd class="mt-1">
                        <AdminStatusBadge
                          :tone="
                            {
                              pending_payment: 'warning',
                              awaiting_payment_verification: 'warning',
                              payment_verified: 'info',
                              processing: 'info',
                              shipped: 'info',
                              delivered: 'success',
                              active: 'success',
                              inactive: 'neutral',
                              expired: 'danger',
                              cancelled: 'danger',
                              rejected: 'danger',
                              replacement: 'brass',
                            }[selectedCard.status] || 'neutral'
                          "
                          :label="getCardStatusLabel(selectedCard.status)"
                        />
                      </dd>
                    </div>
                    <div>
                      <dt class="text-caption font-medium text-secondary-500">
                        Subscription plan
                      </dt>
                      <dd class="text-body capitalize text-secondary-900">
                        {{ selectedCard.subscription_plan || "N/A" }}
                      </dd>
                    </div>
                    <div>
                      <dt class="text-caption font-medium text-secondary-500">
                        Contact number
                      </dt>
                      <dd class="text-body text-secondary-900">
                        {{ selectedCard.contact_number || "N/A" }}
                      </dd>
                    </div>
                  </dl>
                </div>
                <div>
                  <p class="admin-form-section-title">Purchase details</p>
                  <dl class="mt-4 space-y-3">
                    <div>
                      <dt class="text-caption font-medium text-secondary-500">
                        Purchase amount
                      </dt>
                      <dd class="text-body font-semibold text-secondary-900">
                        ${{ selectedCard.purchase_amount }}
                      </dd>
                    </div>
                    <div>
                      <dt class="text-caption font-medium text-secondary-500">
                        Purchase date
                      </dt>
                      <dd class="text-body text-secondary-900">
                        {{ formatDate(selectedCard.purchase_date) }}
                      </dd>
                    </div>
                    <div>
                      <dt class="text-caption font-medium text-secondary-500">
                        Payment method
                      </dt>
                      <dd class="text-body capitalize text-secondary-900">
                        {{ selectedCard.payment_method ? selectedCard.payment_method.replace(/_/g, ' ') : "N/A" }}
                      </dd>
                    </div>
                    <div>
                      <dt class="text-caption font-medium text-secondary-500">
                        Created
                      </dt>
                      <dd class="text-body text-secondary-900">
                        {{ formatDate(selectedCard.created_at) }}
                      </dd>
                    </div>
                  </dl>
                </div>
              </div>

              <!-- Shipping & fulfilment -->
              <div
                v-if="
                  selectedCard.courier ||
                  selectedCard.tracking_number ||
                  selectedCard.shipped_date ||
                  selectedCard.delivered_date
                "
                class="admin-form-section"
              >
                <p class="admin-form-section-title">Shipping &amp; fulfilment</p>
                <dl class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                  <div v-if="selectedCard.courier">
                    <dt class="text-caption font-medium text-secondary-500">Courier</dt>
                    <dd class="text-body text-secondary-900">{{ selectedCard.courier }}</dd>
                  </div>
                  <div v-if="selectedCard.tracking_number">
                    <dt class="text-caption font-medium text-secondary-500">Tracking no.</dt>
                    <dd class="font-mono text-body text-secondary-900">{{ selectedCard.tracking_number }}</dd>
                  </div>
                  <div v-if="selectedCard.shipped_date">
                    <dt class="text-caption font-medium text-secondary-500">Shipped date</dt>
                    <dd class="text-body text-secondary-900">{{ formatDate(selectedCard.shipped_date) }}</dd>
                  </div>
                  <div v-if="selectedCard.delivered_date">
                    <dt class="text-caption font-medium text-secondary-500">Delivered date</dt>
                    <dd class="text-body text-secondary-900">{{ formatDate(selectedCard.delivered_date) }}</dd>
                  </div>
                </dl>
              </div>

              <!-- Addresses -->
              <div
                v-if="selectedCard.billing_address || selectedCard.shipping_address"
                class="admin-form-section"
              >
                <p class="admin-form-section-title">Addresses</p>
                <dl class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div v-if="selectedCard.billing_address">
                    <dt class="text-caption font-medium text-secondary-500">Billing address</dt>
                    <dd class="mt-1 whitespace-pre-line text-body text-secondary-900">{{ selectedCard.billing_address }}</dd>
                  </div>
                  <div v-if="selectedCard.shipping_address">
                    <dt class="text-caption font-medium text-secondary-500">Shipping address</dt>
                    <dd class="mt-1 whitespace-pre-line text-body text-secondary-900">{{ selectedCard.shipping_address }}</dd>
                  </div>
                </dl>
              </div>

              <!-- Cancellation reason -->
              <div
                v-if="selectedCard.cancelled_reason"
                class="admin-form-section"
              >
                <p class="admin-form-section-title">Cancellation reason</p>
                <p class="mt-2 rounded-lg bg-error-50 px-3 py-2 text-body text-error-700">
                  {{ selectedCard.cancelled_reason }}
                </p>
              </div>

              <!-- Admin notes -->
              <div v-if="selectedCard.notes" class="admin-form-section">
                <p class="admin-form-section-title">Admin notes</p>
                <p class="mt-2 whitespace-pre-line text-body text-secondary-700">
                  {{ selectedCard.notes }}
                </p>
              </div>

              <!-- Public profile URL (NFC encoding) -->
              <div
                v-if="getCardPublicProfileUrl(selectedCard)"
                class="admin-form-section mt-6"
              >
                <div class="flex flex-wrap items-start justify-between gap-3">
                  <div>
                    <p class="admin-form-section-title">Public profile URL</p>
                    <p class="admin-form-section-desc">
                      Copy this URL and encode / write it into the physical NFC
                      tag chip.
                    </p>
                  </div>
                  <AdminStatusBadge tone="brass" label="For NFC encoding" />
                </div>
                <div class="mt-3 flex items-stretch gap-2">
                  <input
                    :value="getCardPublicProfileUrl(selectedCard, true)"
                    readonly
                    class="admin-field flex-1 font-mono text-caption"
                  />
                  <button
                    type="button"
                    class="admin-btn admin-btn--outline admin-btn--icon shrink-0"
                    title="Copy URL to clipboard"
                    @click="
                      copyToClipboard(getCardPublicProfileUrl(selectedCard, true))
                    "
                  >
                    <Icon name="heroicons:document-duplicate" class="h-4 w-4" />
                  </button>
                  <button
                    type="button"
                    class="admin-btn admin-btn--primary admin-btn--sm shrink-0"
                    title="Open profile URL to verify it works"
                    @click="
                      openInNewTab(getCardPublicProfileUrl(selectedCard, true))
                    "
                  >
                    <Icon
                      name="heroicons:arrow-top-right-on-square"
                      class="h-4 w-4"
                    />
                    Test URL
                  </button>
                </div>
              </div>
            </div>

            <div class="admin-dialog-foot">
              <button
                type="button"
                class="admin-btn admin-btn--outline"
                @click="showCardModal = false"
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
    >
      <div v-if="confirmConfig.prompt" class="mt-4">
        <label class="admin-label">{{ confirmConfig.fieldLabel }}</label>
        <textarea
          v-model="confirmConfig.text"
          rows="3"
          class="admin-field"
          :placeholder="confirmConfig.placeholder"
        ></textarea>
      </div>
    </AdminConfirmDialog>
  </div>
</template>

<script setup>
definePageMeta({
  layout: "admin-management",
  middleware: "admin",
});

const adminStore = useAdminStore();
const { nfcCards, loading, pagination } = storeToRefs(adminStore);
const { $api, $toast, $config } = useNuxtApp();

// Modal states
const showRegisterModal = ref(false);
const showEditModal = ref(false);
const showCardModal = ref(false);
const showMarkShippedModal = ref(false);
const selectedCard = ref(null);
const submitting = ref(false);

// Confirmation dialog state
const confirmOpen = ref(false);
const confirmBusy = ref(false);
const confirmConfig = ref({
  title: "",
  message: "",
  confirmLabel: "Confirm",
  tone: "primary",
  prompt: false,
  fieldLabel: "",
  placeholder: "",
  text: "",
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

// Per-action loading state keyed by card.id → action name
const actionLoading = reactive({});

// Mark Shipped form
const shippedForm = reactive({
  card: null,
  courier: "",
  tracking_number: "",
  shipped_date: "",
});

// Form data
const form = ref({
  user_id: "",
  nfc_card_id: "",
  card_owner: "",
  billing_address: "",
  contact_number: "",
  subscription_plan: "basic",
  purchase_amount: 0,
  payment_method: "",
  shipping_address: "",
  notes: "",
});

// Form validation errors
const formErrors = ref({
  nfc_card_id: "",
  card_owner: "",
  contact_number: "",
  purchase_amount: "",
});

const editForm = ref({
  card_owner: "",
  billing_address: "",
  contact_number: "",
  status: "",
  courier: "",
  tracking_number: "",
  shipped_date: "",
  delivered_date: "",
  cancellation_reason: "",
  notes: "",
});

// Filters
const filters = ref({
  search: "",
  status: "",
  subscription_plan: "",
  sort_by: "created_at",
  sort_order: "desc",
});

// Default sort orders for each field (applied when field changes)
const defaultSortOrders = {
  created_at: "desc",    // Newest first
  card_owner: "asc",     // A-Z
  nfc_card_id: "desc",   // Latest IDs first
  purchase_amount: "desc", // High to low
};

// Pagination state
const itemsPerPage = ref(15);

// Available users for registration
const availableUsers = ref([]);

// Fetch NFC cards on mount
onMounted(async () => {
  await adminStore.fetchNfcCards();
  await fetchAvailableUsers();
});

// Fetch available users for registration
const fetchAvailableUsers = async () => {
  try {
    const nuxtApp = useNuxtApp();
    const $api = nuxtApp.$api;
    if (!$api) {
      // Retry after a short delay if API not ready
      setTimeout(() => fetchAvailableUsers(), 500);
      return;
    }
    const response = await $api.get("/admin/users", {
      params: { per_page: 100 },
    });
    if (response.success) {
      availableUsers.value = response.data.data;
    }
  } catch (error) {
    console.error("Failed to fetch users:", error);
  }
};

// Handle search with debounce
let searchTimeout;
const handleSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    adminStore.updateFilters({ search: filters.value.search });
    adminStore.fetchNfcCards();
  }, 500);
};

// Validation functions
const validateNfcCardId = () => {
  // Remove non-alphanumeric characters (allow letters, numbers, and hyphens)
  form.value.nfc_card_id = form.value.nfc_card_id.replace(/[^a-zA-Z0-9-]/g, '').toUpperCase();
  if (form.value.nfc_card_id.length > 16) {
    formErrors.value.nfc_card_id = "Maximum 16 characters allowed";
  } else {
    formErrors.value.nfc_card_id = "";
  }
};

const validateCardOwner = () => {
  if (form.value.card_owner.length > 100) {
    formErrors.value.card_owner = "Maximum 100 characters allowed";
  } else {
    formErrors.value.card_owner = "";
  }
};

// Phone number formatting composable
const { formatPhoneNumber } = usePhoneFormat();

const validateContactNumber = () => {
  // Allow digits, spaces, dashes, and + for international format
  const cleaned = form.value.contact_number.replace(/[^\d\s\-+]/g, '');
  form.value.contact_number = cleaned;
  if (cleaned.length > 16) {
    formErrors.value.contact_number = "Maximum 16 characters allowed";
  } else {
    formErrors.value.contact_number = "";
  }
};

// Format phone number on blur for registration form
const formatContactNumberOnBlur = () => {
  if (form.value.contact_number) {
    form.value.contact_number = formatPhoneNumber(form.value.contact_number);
  }
};

// Format phone number on blur for edit form
const formatEditContactNumberOnBlur = () => {
  if (editForm.value.contact_number) {
    editForm.value.contact_number = formatPhoneNumber(editForm.value.contact_number);
  }
};

const validatePurchaseAmount = () => {
  // Ensure value is a number and not negative
  const amount = form.value.purchase_amount;

  if (amount === null || amount === undefined || amount === '') {
    form.value.purchase_amount = 0;
  } else if (typeof amount === 'number' && amount < 0) {
    formErrors.value.purchase_amount = "Amount cannot be negative";
    // Correct the value to 0
    form.value.purchase_amount = 0;
  } else if (typeof amount === 'number' && amount >= 0) {
    formErrors.value.purchase_amount = "";
  }
};

const validateAllFields = () => {
  validateNfcCardId();
  validateCardOwner();
  validateContactNumber();
  validatePurchaseAmount();
  return !formErrors.value.nfc_card_id && !formErrors.value.card_owner &&
         !formErrors.value.contact_number && !formErrors.value.purchase_amount;
};

const clearFormErrors = () => {
  formErrors.value = {
    nfc_card_id: "",
    card_owner: "",
    contact_number: "",
    purchase_amount: "",
  };
};

// Handle filter changes
const handleFilterChange = () => {
  adminStore.updateFilters(filters.value);
  adminStore.fetchNfcCards();
};

// Handle sort field change from dropdown (applies default order for new field)
const handleSortFieldChange = () => {
  // When changing sort field, apply the default order for that field
  filters.value.sort_order = defaultSortOrders[filters.value.sort_by] || "desc";
  handleFilterChange();
};

// Toggle sort direction without changing the sort field
const toggleSortOrder = () => {
  filters.value.sort_order = filters.value.sort_order === "asc" ? "desc" : "asc";
  handleFilterChange();
};

// Clear all filters
const clearFilters = () => {
  adminStore.clearFilters();
  filters.value = {
    search: "",
    status: "",
    subscription_plan: "",
    sort_by: "created_at",
    sort_order: "desc",
  };
  adminStore.fetchNfcCards();
};

// Change page
const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    adminStore.fetchNfcCards({ page });
  }
};

// Change items per page
const changeItemsPerPage = () => {
  adminStore.fetchNfcCards({ page: 1, per_page: itemsPerPage.value });
};

// View card details
const viewCard = (card) => {
  selectedCard.value = card;
  showCardModal.value = true;
};

// Edit card
const editCard = (card) => {
  selectedCard.value = card;
  editForm.value = {
    card_owner: card.card_owner,
    billing_address: card.billing_address,
    contact_number: card.contact_number,
    status: card.status,
    courier: card.courier || "",
    tracking_number: card.tracking_number || "",
    shipped_date: card.shipped_date ? card.shipped_date.split("T")[0] : "",
    delivered_date: card.delivered_date
      ? card.delivered_date.split("T")[0]
      : "",
    cancellation_reason: card.cancelled_reason || "",
    notes: card.notes || "",
  };
  showEditModal.value = true;
};

// Handle form submission
const handleSubmit = async () => {
  // Validate all fields before submission
  if (!validateAllFields()) {
    return;
  }

  submitting.value = true;
  try {
    await adminStore.registerNfcCard(form.value);
    closeModal();
  } catch (error) {
    console.error("Failed to register NFC card:", error);
  } finally {
    submitting.value = false;
  }
};

// Handle edit form submission
const handleEditSubmit = async () => {
  submitting.value = true;
  try {
    await adminStore.updateNfcCard(selectedCard.value.id, editForm.value);
    closeEditModal();
  } catch (error) {
    console.error("Failed to update NFC card:", error);
  } finally {
    submitting.value = false;
  }
};

// Close modal
const closeModal = () => {
  showRegisterModal.value = false;
  clearFormErrors();
  form.value = {
    user_id: "",
    nfc_card_id: "",
    card_owner: "",
    billing_address: "",
    contact_number: "",
    subscription_plan: "basic",
    purchase_amount: 0,
    payment_method: "",
    shipping_address: "",
    notes: "",
  };
};

// Close edit modal
const closeEditModal = () => {
  showEditModal.value = false;
  selectedCard.value = null;
  editForm.value = {
    card_owner: "",
    billing_address: "",
    contact_number: "",
    status: "",
    courier: "",
    tracking_number: "",
    shipped_date: "",
    delivered_date: "",
    cancellation_reason: "",
    notes: "",
  };
};

// Open Mark Shipped Modal
const openMarkShippedModal = (card) => {
  shippedForm.card = card;
  shippedForm.courier = card.courier || "";
  shippedForm.tracking_number = card.tracking_number || "";
  shippedForm.shipped_date = card.shipped_date
    ? card.shipped_date.split("T")[0]
    : new Date().toISOString().split("T")[0];
  showMarkShippedModal.value = true;
};

// Set loading flag helper
const setLoading = (cardId, actionName, state) => {
  if (!actionLoading[cardId]) actionLoading[cardId] = {};
  actionLoading[cardId][actionName] = state;
};

// Generic NFC card admin order action helper
const runNfcAdminAction = async (card, endpointPath, actionKey, payload = {}) => {
  if (!card?.id) return false;
  setLoading(card.id, actionKey, true);
  try {
    const response = await $api.post(`/admin/nfc-cards/${card.id}/${endpointPath}`, payload);
    if (response && response.success) {
      $toast.success(response.message || `Order ${actionKey} successful!`);
      await adminStore.fetchNfcCards();
      return true;
    } else {
      $toast.error(response?.message || `Failed: ${actionKey}`);
      return false;
    }
  } catch (error) {
    const msg = error?.response?.data?.message || error?.message || `Error during ${actionKey}`;
    $toast.error(msg);
    console.error(`[NFC Admin ${actionKey}] error:`, error);
    return false;
  } finally {
    setLoading(card.id, actionKey, false);
  }
};

// Admin order flow: Verify Payment
const verifyNfcCardPayment = (card) => {
  askConfirm(
    {
      title: "Verify payment",
      message: `Verify payment for order ${card.card_owner || card.card_id || ''}? After this, the user can start designing their profile.`,
      confirmLabel: "Verify",
      tone: "primary",
    },
    async () => {
      await runNfcAdminAction(card, 'verify-payment', 'verify');
    }
  );
};

// Admin order flow: Reject Payment
const rejectNfcCardPayment = (card) => {
  askConfirm(
    {
      title: "Reject payment",
      message: `State the rejection reason for order ${card.card_owner || ''} (will be shown to user). Can be left blank.`,
      confirmLabel: "Reject",
      tone: "danger",
      prompt: true,
      fieldLabel: "Rejection reason (optional)",
      placeholder: "Reason shown to the user…",
      text: "",
    },
    async () => {
      await runNfcAdminAction(card, 'reject-payment', 'reject', { cancellation_reason: confirmConfig.value.text || undefined });
    }
  );
};

// Admin order flow: Mark Processing
const markNfcCardProcessing = (card) => {
  askConfirm(
    {
      title: "Mark as processing",
      message: `Mark order ${card.card_owner || ''} as PROCESSING (print + prepare card)?`,
      confirmLabel: "Mark processing",
      tone: "primary",
    },
    async () => {
      await runNfcAdminAction(card, 'mark-processing', 'processing');
    }
  );
};

// Admin order flow: Submit Mark Shipped
const submitMarkShipped = async () => {
  if (!shippedForm.card) return;
  const card = shippedForm.card;
  const ok = await runNfcAdminAction(card, 'mark-shipped', 'shipped', {
    courier: shippedForm.courier,
    tracking_number: shippedForm.tracking_number,
    shipped_date: shippedForm.shipped_date || undefined,
  });
  if (ok) {
    showMarkShippedModal.value = false;
    shippedForm.card = null;
  }
};

// Admin order flow: Mark Delivered
const markNfcCardDelivered = (card) => {
  askConfirm(
    {
      title: "Mark as delivered",
      message: `Mark order ${card.card_owner || ''} as DELIVERED? This will activate the user's subscription.`,
      confirmLabel: "Mark delivered",
      tone: "primary",
    },
    async () => {
      await runNfcAdminAction(card, 'mark-delivered', 'delivered');
    }
  );
};

// Admin order flow: Cancel
const cancelNfcCard = (card) => {
  askConfirm(
    {
      title: "Cancel order",
      message: `State the CANCELLATION REASON for order ${card.card_owner || ''} (required - will be shown to user).`,
      confirmLabel: "Cancel order",
      tone: "danger",
      prompt: true,
      fieldLabel: "Cancellation reason",
      placeholder: "Why is this order being cancelled?",
      text: "",
    },
    async () => {
      const reason = confirmConfig.value.text;
      if (!reason || !String(reason).trim()) {
        $toast.warning("Cancellation reason is required");
        return;
      }
      await runNfcAdminAction(card, 'cancel', 'cancel', { cancellation_reason: reason });
    }
  );
};

// Delete card
const deleteCard = (card) => {
  askConfirm(
    {
      title: "Delete NFC card",
      message: `Are you sure you want to delete the NFC card for ${card.card_owner}? This action cannot be undone.`,
      confirmLabel: "Delete",
      tone: "danger",
    },
    async () => {
      try {
        await adminStore.deleteNfcCard(card.id);
      } catch (error) {
        console.error("Failed to delete NFC card:", error);
      }
    }
  );
};

// Toggle card status (active/inactive)
const toggleCardStatus = (card) => {
  const newStatus = card.status === 'active' ? 'inactive' : 'active';
  const action = newStatus === 'active' ? 'activate' : 'deactivate';

  askConfirm(
    {
      title: `${newStatus === 'active' ? "Activate" : "Deactivate"} card`,
      message: `Are you sure you want to ${action} the NFC card for ${card.card_owner}?`,
      confirmLabel: newStatus === 'active' ? "Activate" : "Deactivate",
      tone: newStatus === 'active' ? "primary" : "danger",
    },
    async () => {
      try {
        await adminStore.updateNfcCard(card.id, { status: newStatus });
      } catch (error) {
        console.error("Failed to toggle card status:", error);
      }
    }
  );
};

// Helper functions — Status labels in ENGLISH (match backend NfcCard model statusLabels)
const getCardStatusLabel = (status) => {
  const fallback = {
    pending_payment: "Pending Payment",
    awaiting_payment_verification: "Awaiting Payment Verification",
    payment_verified: "Payment Verified",
    processing: "Processing",
    shipped: "Shipped",
    delivered: "Delivered (Not Activated)",
    active: "Active",
    inactive: "Inactive",
    expired: "Expired",
    cancelled: "Cancelled",
    rejected: "Rejected",
    replacement: "Replacement",
  };
  const label = fallback[status];
  if (label) return label;
  // Fallback: convert snake_case to Title Case for unknown statuses
  return String(status || "Unknown")
    .toLowerCase()
    .replace(/_/g, " ")
    .replace(/\b\w/g, (c) => c.toUpperCase())
    .trim() || "Unknown";
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  return new Date(dateString).toLocaleDateString();
};

// ---- Public profile URL helpers (for NFC encoding) ----
// The backend appends `public_url` to every NfcCard (a relative path such
// as /profile/{name-slug}/{cardNumber}). When it is not present we fall
// back to the legacy nfc_card_id route.
const getCardPublicProfileUrl = (card, absolute = false) => {
  if (!card) return "";
  const relative = card.public_url || (card.nfc_card_id ? `/profile/${card.nfc_card_id}` : "");
  if (!relative) return "";
  if (!absolute) return relative;
  // Build an absolute URL using the current origin so the link works in any
  // environment (local, staging, production).
  if (process.client) {
    return `${window.location.origin}${relative}`;
  }
  return relative;
};

const copyToClipboard = async (text) => {
  if (!text) return;
  try {
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(text);
    } else {
      // Fallback for non-secure contexts / older browsers
      const textarea = document.createElement("textarea");
      textarea.value = text;
      textarea.style.position = "fixed";
      textarea.style.opacity = "0";
      document.body.appendChild(textarea);
      textarea.focus();
      textarea.select();
      document.execCommand("copy");
      document.body.removeChild(textarea);
    }
    $toast.success("Profile URL copied to clipboard");
  } catch (error) {
    console.error("Failed to copy:", error);
    $toast.error("Failed to copy URL");
  }
};

const openInNewTab = (url) => {
  if (!url) return;
  window.open(url, "_blank", "noopener,noreferrer");
};
</script>
