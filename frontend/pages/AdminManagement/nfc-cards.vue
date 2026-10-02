<!-- pages/AdminManagement/nfc-cards.vue -->
<template>
  <div>
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-secondary-900">
            NFC Card Management
          </h1>
          <p class="mt-2 text-secondary-600">
            Register and manage physical NFC cards for users
          </p>
        </div>
        <div class="mt-4 sm:mt-0">
          <button @click="showRegisterModal = true" class="btn btn-primary">
            <Icon name="heroicons:plus" class="h-5 w-5 mr-2" />
            Register NFC Card
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
              placeholder="Search cards..."
              class="input"
              maxlength="150"
              @input="handleSearch"
            />
            <span class="text-xs text-secondary-500 mt-1 block">{{ filters.search.length }}/150</span>
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
              <option value="">All Statuses</option>
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
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Subscription Plan</label
            >
            <select
              v-model="filters.subscription_plan"
              @change="handleFilterChange"
              class="input"
            >
              <option value="">All Plans</option>
              <option value="basic">Basic</option>
              <option value="premium">Premium</option>
              <option value="business">Business</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Sort By</label
            >
            <select
              v-model="filters.sort_by"
              @change="handleSortFieldChange"
              class="input"
            >
              <option value="created_at">Date Created</option>
              <option value="card_owner">Card Owner</option>
              <option value="nfc_card_id">NFC ID</option>
              <option value="purchase_amount">Amount</option>
            </select>
          </div>
        </div>
        <div class="mt-4 flex justify-between items-center">
          <button @click="clearFilters" class="btn btn-outline btn-sm">
            Clear Filters
          </button>
          <div class="text-sm text-secondary-500">
            {{ pagination.total }} cards found
          </div>
        </div>
      </div>
    </div>

    <!-- NFC Cards Table -->
    <div class="card">
      <div class="card-body p-0">
        <div v-if="loading" class="flex justify-center py-12">
          <div class="spinner"></div>
        </div>
        <div v-else-if="nfcCards.length" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-secondary-200">
            <thead class="bg-secondary-50">
              <tr>
                <th
                  class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  Card Details
                </th>
                <th
                  class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  User
                </th>
                <th
                  class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  Status
                </th>
                <th
                  class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  Purchase Info
                </th>
                <th
                  class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-secondary-200">
              <tr
                v-for="card in nfcCards"
                :key="card.id"
                class="hover:bg-secondary-50"
              >
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div
                        class="h-12 w-8 bg-gradient-to-r from-primary-500 to-secondary-500 rounded-lg flex items-center justify-center"
                      >
                        <Icon
                          name="heroicons:credit-card"
                          class="h-6 w-6 text-white"
                        />
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-secondary-900">
                        {{ card.card_owner }}
                      </div>
                      <div class="text-sm text-secondary-500 font-mono">
                        {{ card.nfc_card_id }}
                      </div>
                      <div class="text-xs text-secondary-400">
                        Created: {{ formatDate(card.created_at) }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <img
                      :src="
                        card.user?.profile?.profile_image ||
                        '/default-avatar.png'
                      "
                      :alt="card.user?.full_name"
                      class="h-8 w-8 rounded-full object-cover"
                    />
                    <div class="ml-3">
                      <div class="text-sm font-medium text-secondary-900">
                        {{ card.user?.full_name }}
                      </div>
                      <div class="text-sm text-secondary-500">
                        {{ card.user?.email }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'px-2 py-1 text-xs font-medium rounded-full',
                      getStatusBadgeClass(card.status),
                    ]"
                  >
                    {{ getCardStatusLabel(card.status) }}
                  </span>
                  <div
                    v-if="card.tracking_number"
                    class="text-xs text-secondary-500 mt-1"
                  >
                    Tracking: {{ card.tracking_number }}
                  </div>
                </td>
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-secondary-900">
                    <span class="font-medium">${{ card.purchase_amount }}</span>
                  </div>
                  <div class="text-sm capitalize text-secondary-500">
                    {{ String(card.subscription_plan || '').replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) || 'N/A' }}
                  </div>
                  <div class="text-xs text-secondary-400">
                    {{ formatDate(card.purchase_date) }}
                  </div>
                </td>
                <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex items-center space-x-1.5 flex-wrap gap-y-1" :title="getCardStatusLabel(card.status)">
                    <!-- Status Badge -->
                    <span
                      :class="[
                        'px-2 py-1 text-[11px] font-medium rounded-full mr-1',
                        getStatusBadgeClass(card.status),
                      ]"
                    >
                      {{ getCardStatusLabel(card.status) }}
                    </span>

                    <!-- Contextual Order Flow Buttons -->
                    <!-- awaiting_payment_verification: Verify / Reject / View proof -->
                    <template v-if="card.status === 'awaiting_payment_verification'">
                      <button
                        @click="verifyNfcCardPayment(card)"
                        :disabled="actionLoading[card.id]?.verify"
                        class="px-2 py-1 rounded text-[11px] font-medium bg-emerald-100 text-emerald-700 hover:bg-emerald-200 disabled:opacity-50"
                        title="Verify payment proof"
                      >
                        <span v-if="actionLoading[card.id]?.verify" class="spinner spinner-sm mr-1"></span>
                        <Icon v-else name="heroicons:check-circle" class="h-3.5 w-3.5 inline mr-0.5" />
                        Verify
                      </button>
                      <button
                        @click="rejectNfcCardPayment(card)"
                        :disabled="actionLoading[card.id]?.reject"
                        class="px-2 py-1 rounded text-[11px] font-medium bg-red-100 text-red-700 hover:bg-red-200 disabled:opacity-50"
                        title="Reject payment proof"
                      >
                        <Icon v-if="!actionLoading[card.id]?.reject" name="heroicons:x-circle" class="h-3.5 w-3.5 inline mr-0.5" />
                        <span v-else class="spinner spinner-sm mr-1"></span>
                        Reject
                      </button>
                      <a
                        v-if="card.transaction?.payment_proof_url"
                        :href="card.transaction.payment_proof_url"
                        target="_blank"
                        class="px-2 py-1 rounded text-[11px] font-medium bg-secondary-100 text-secondary-700 hover:bg-secondary-200"
                        title="View payment proof"
                      >
                        <Icon name="heroicons:eye" class="h-3.5 w-3.5 inline mr-0.5" />
                        Proof
                      </a>
                    </template>

                    <!-- payment_verified: Mark Processing / Cancel -->
                    <template v-else-if="card.status === 'payment_verified'">
                      <button
                        @click="markNfcCardProcessing(card)"
                        :disabled="actionLoading[card.id]?.processing"
                        class="px-2 py-1 rounded text-[11px] font-medium bg-purple-100 text-purple-700 hover:bg-purple-200 disabled:opacity-50"
                        title="Mark order as processing"
                      >
                        <span v-if="actionLoading[card.id]?.processing" class="spinner spinner-sm mr-1"></span>
                        <Icon v-else name="heroicons:arrow-path" class="h-3.5 w-3.5 inline mr-0.5" />
                        Mark Processing
                      </button>
                      <button
                        @click="cancelNfcCard(card)"
                        :disabled="actionLoading[card.id]?.cancel"
                        class="px-2 py-1 rounded text-[11px] font-medium bg-red-100 text-red-700 hover:bg-red-200 disabled:opacity-50"
                        title="Cancel order"
                      >
                        <Icon v-if="!actionLoading[card.id]?.cancel" name="heroicons:no-symbol" class="h-3.5 w-3.5 inline mr-0.5" />
                        <span v-else class="spinner spinner-sm mr-1"></span>
                        Cancel
                      </button>
                    </template>

                    <!-- processing: Mark Shipped / Cancel -->
                    <template v-else-if="card.status === 'processing'">
                      <button
                        @click="openMarkShippedModal(card)"
                        :disabled="actionLoading[card.id]?.shipped"
                        class="px-2 py-1 rounded text-[11px] font-medium bg-blue-100 text-blue-700 hover:bg-blue-200 disabled:opacity-50"
                        title="Mark card as shipped"
                      >
                        <span v-if="actionLoading[card.id]?.shipped" class="spinner spinner-sm mr-1"></span>
                        <Icon v-else name="heroicons:truck" class="h-3.5 w-3.5 inline mr-0.5" />
                        Mark Shipped
                      </button>
                      <button
                        @click="cancelNfcCard(card)"
                        :disabled="actionLoading[card.id]?.cancel"
                        class="px-2 py-1 rounded text-[11px] font-medium bg-red-100 text-red-700 hover:bg-red-200 disabled:opacity-50"
                        title="Cancel order"
                      >
                        <Icon v-if="!actionLoading[card.id]?.cancel" name="heroicons:no-symbol" class="h-3.5 w-3.5 inline mr-0.5" />
                        <span v-else class="spinner spinner-sm mr-1"></span>
                        Cancel
                      </button>
                    </template>

                    <!-- shipped: END USER yang akan confirm received — Admin TIADA butang Mark Delivered, cuma show shipped badge + tracking info -->
                    <template v-else-if="card.status === 'shipped'">
                      <div class="flex items-center gap-1 px-2 py-1 rounded text-[11px] font-medium bg-gray-100 text-gray-700 italic" title="Admin cannot mark delivered — End user must press 'I've Received The Card' in Card Management">
                        <Icon name="heroicons:clock" class="h-3.5 w-3.5 inline mr-0.5" />
                        Waiting for user receipt
                      </div>
                    </template>

                    <!-- delivered / active / inactive: Toggle activate/deactivate -->
                    <template v-else-if="['delivered','active','inactive','expired'].includes(card.status)">
                      <button
                        @click="toggleCardStatus(card)"
                        :disabled="actionLoading[card.id]?.toggle"
                        :class="[
                          'px-2 py-1 rounded text-[11px] font-medium disabled:opacity-50',
                          ['active','delivered'].includes(card.status)
                            ? 'bg-warning-100 text-warning-700 hover:bg-warning-200'
                            : 'bg-success-100 text-success-700 hover:bg-success-200',
                        ]"
                        :title="
                          ['active','delivered'].includes(card.status)
                            ? 'Deactivate card'
                            : 'Activate card'
                        "
                      >
                        <span v-if="actionLoading[card.id]?.toggle" class="spinner spinner-sm mr-1"></span>
                        <Icon v-else :name="['active','delivered'].includes(card.status) ? 'heroicons:pause' : 'heroicons:play'" class="h-3.5 w-3.5 inline mr-0.5" />
                        {{ ['active','delivered'].includes(card.status) ? 'Deactivate' : 'Activate' }}
                      </button>
                    </template>

                    <!-- cancelled / replacement / pending_payment: Show payment proof if any -->
                    <template v-if="card.transaction?.payment_proof_url && card.status === 'pending_payment'">
                      <a
                        :href="card.transaction.payment_proof_url"
                        target="_blank"
                        class="px-2 py-1 rounded text-[11px] font-medium bg-secondary-100 text-secondary-700 hover:bg-secondary-200"
                        title="View existing payment proof"
                      >
                        <Icon name="heroicons:eye" class="h-3.5 w-3.5 inline mr-0.5" />
                        Proof
                      </a>
                    </template>

                    <!-- Global actions (view / edit / delete - selalu ada kecuali cancelled yg hide delete) -->
                    <button
                      @click="viewCard(card)"
                      class="text-primary-600 hover:text-primary-900 p-1"
                      title="View full details"
                    >
                      <Icon name="heroicons:eye" class="h-4 w-4" />
                    </button>
                    <button
                      @click="editCard(card)"
                      class="text-warning-600 hover:text-warning-900 p-1"
                      title="Edit card details"
                    >
                      <Icon name="heroicons:pencil" class="h-4 w-4" />
                    </button>
                    <button
                      v-if="card.status !== 'cancelled'"
                      @click="deleteCard(card)"
                      class="text-error-600 hover:text-error-900 p-1"
                      title="Delete card record"
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
            name="heroicons:credit-card"
            class="h-12 w-12 mx-auto text-secondary-300 mb-4"
          />
          <p class="text-secondary-500">No NFC cards found</p>
        </div>
      </div>
    </div>

    <!-- Pagination -->
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

    <!-- Register NFC Card Modal -->
    <div
      v-if="showRegisterModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    >
      <div
        class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
      >
        <div class="px-6 py-4 border-b border-secondary-200">
          <h3 class="text-lg font-medium text-secondary-900">
            Register New NFC Card
          </h3>
        </div>
        <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >User *</label
            >
            <select v-model="form.user_id" required class="input">
              <option value="">Select User</option>
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
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >NFC Card ID *</label
            >
            <input
              v-model="form.nfc_card_id"
              type="text"
              required
              maxlength="16"
              @input="validateNfcCardId"
              placeholder="e.g., NFC-ABC123DEF456"
              :class="[
                'input font-mono',
                formErrors.nfc_card_id ? 'border-red-500 focus:ring-red-500' : ''
              ]"
            />
            <div class="flex justify-between mt-1">
              <p v-if="formErrors.nfc_card_id" class="text-xs text-red-500">
                {{ formErrors.nfc_card_id }}
              </p>
              <p v-else class="text-xs text-secondary-500">
                Enter the unique identifier printed on the physical NFC card
              </p>
              <span class="text-xs text-secondary-400">{{ form.nfc_card_id.length }}/16</span>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Card Owner *</label
            >
            <input
              v-model="form.card_owner"
              type="text"
              required
              maxlength="100"
              @input="validateCardOwner"
              placeholder="Full name of card owner"
              :class="[
                'input',
                formErrors.card_owner ? 'border-red-500 focus:ring-red-500' : ''
              ]"
            />
            <div class="flex justify-between mt-1">
              <p v-if="formErrors.card_owner" class="text-xs text-red-500">
                {{ formErrors.card_owner }}
              </p>
              <span class="text-xs text-secondary-400 ml-auto">{{ form.card_owner.length }}/100</span>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Billing Address *</label
            >
            <textarea
              v-model="form.billing_address"
              required
              placeholder="Full billing address"
              class="input"
              rows="3"
            ></textarea>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Contact Number *</label
              >
              <PhoneInput
                v-model="form.contact_number"
                :required="true"
                :has-error="!!formErrors.contact_number"
                placeholder="Phone number"
              />
              <p v-if="formErrors.contact_number" class="text-xs text-red-500 mt-1">
                {{ formErrors.contact_number }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Subscription Plan *</label
              >
              <select v-model="form.subscription_plan" required class="input">
                <option value="basic">Basic ($9/month)</option>
                <option value="premium">Premium ($19/month)</option>
                <option value="business">Business ($49/month)</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Purchase Amount *</label
              >
              <input
                v-model.number="form.purchase_amount"
                type="number"
                min="0"
                step="1"
                required
                @input="validatePurchaseAmount"
                @change="validatePurchaseAmount"
                placeholder="0"
                :class="[
                  'input',
                  formErrors.purchase_amount ? 'border-red-500 focus:ring-red-500' : ''
                ]"
              />
              <p v-if="formErrors.purchase_amount" class="text-xs text-red-500 mt-1">
                {{ formErrors.purchase_amount }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Payment Method</label
              >
              <select v-model="form.payment_method" class="input">
                <option value="">Select Payment Method</option>
                <option value="stripe">Stripe</option>
                <option value="razorpay">Razorpay</option>
                <option value="cash">Cash</option>
                <option value="bank_transfer">Bank Transfer</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Shipping Address</label
            >
            <textarea
              v-model="form.shipping_address"
              placeholder="Shipping address (if different from billing)"
              class="input"
              rows="2"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Notes</label
            >
            <textarea
              v-model="form.notes"
              placeholder="Additional notes or special instructions"
              class="input"
              rows="2"
            ></textarea>
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
              Register Card
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit NFC Card Modal -->
    <div
      v-if="showEditModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    >
      <div
        class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
      >
        <div class="px-6 py-4 border-b border-secondary-200">
          <h3 class="text-lg font-medium text-secondary-900">Edit NFC Card</h3>
        </div>
        <form @submit.prevent="handleEditSubmit" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Card Owner</label
            >
            <input
              v-model="editForm.card_owner"
              type="text"
              required
              class="input"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Billing Address</label
            >
            <textarea
              v-model="editForm.billing_address"
              required
              class="input"
              rows="3"
            ></textarea>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Contact Number</label
              >
              <PhoneInput
                v-model="editForm.contact_number"
                placeholder="Phone number"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Status</label
              >
              <select v-model="editForm.status" class="input">
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
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Courier</label
              >
              <input
                v-model="editForm.courier"
                type="text"
                placeholder="Example: Poslaju, J&T, DHL, Ninja Van"
                class="input"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Tracking No.</label
              >
              <input
                v-model="editForm.tracking_number"
                type="text"
                placeholder="Shipping tracking number"
                class="input"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Shipped Date</label
              >
              <input
                v-model="editForm.shipped_date"
                type="date"
                class="input"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Delivered Date</label
              >
              <input
                v-model="editForm.delivered_date"
                type="date"
                class="input"
              />
            </div>
          </div>

          <div v-if="editForm.status === 'cancelled'">
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Cancellation Reason</label
            >
            <textarea
              v-model="editForm.cancellation_reason"
              placeholder="Explain why this order is being cancelled. This will be shown to the user."
              class="input"
              rows="2"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Admin Notes</label
            >
            <textarea
              v-model="editForm.notes"
              placeholder="Internal notes (not shown to user)"
              class="input"
              rows="2"
            ></textarea>
          </div>

          <!-- Public Profile URL (for NFC tag encoding) -->
          <div
            v-if="selectedCard && getCardPublicProfileUrl(selectedCard)"
            class="p-4 bg-primary-50 border border-primary-200 rounded-lg"
          >
            <div class="flex items-start justify-between gap-3 mb-2">
              <div>
                <h4
                  class="text-sm font-semibold text-primary-900 uppercase tracking-wide"
                >
                  Public Profile URL
                </h4>
                <p class="text-xs text-primary-700 mt-1">
                  Copy this URL and encode it into the NFC tag chip
                </p>
              </div>
              <span
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-600 text-white"
              >
                FOR NFC ENCODING
              </span>
            </div>
            <div class="flex items-stretch gap-2">
              <input
                :value="getCardPublicProfileUrl(selectedCard, true)"
                readonly
                class="input bg-white font-mono text-xs flex-1"
              />
              <button
                type="button"
                @click="copyToClipboard(getCardPublicProfileUrl(selectedCard, true))"
                class="btn btn-outline btn-sm flex-shrink-0"
                title="Copy URL"
              >
                <Icon name="heroicons:document-duplicate" class="h-4 w-4" />
              </button>
              <button
                type="button"
                @click="openInNewTab(getCardPublicProfileUrl(selectedCard, true))"
                class="btn btn-outline btn-sm flex-shrink-0"
                title="Open in new tab"
              >
                <Icon name="heroicons:arrow-top-right-on-square" class="h-4 w-4" />
              </button>
            </div>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="closeEditModal"
              class="btn btn-outline"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="btn btn-primary"
            >
              <div v-if="submitting" class="spinner mr-2"></div>
              Update Card
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Mark Shipped Modal -->
    <div
      v-if="showMarkShippedModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    >
      <div
        class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4"
      >
        <div class="px-6 py-4 border-b border-secondary-200 flex items-center justify-between">
          <h3 class="text-lg font-medium text-secondary-900">
            Mark Card As Shipped
          </h3>
          <button
            type="button"
            @click="showMarkShippedModal = false"
            class="text-secondary-400 hover:text-secondary-600"
          >
            <Icon name="heroicons:x-mark" class="h-5 w-5" />
          </button>
        </div>
        <form @submit.prevent="submitMarkShipped" class="p-6 space-y-4">
          <div v-if="shippedForm.card" class="bg-blue-50 border border-blue-200 rounded-lg p-3">
            <p class="text-sm text-blue-800">
              <span class="font-semibold">{{ shippedForm.card.card_owner }}</span>
              <span class="text-blue-600 text-xs block mt-0.5">
                Card ID: {{ shippedForm.card.card_id || shippedForm.card.nfc_card_id || shippedForm.card.id }}
              </span>
            </p>
          </div>
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-1">
              Courier <span class="text-error-500">*</span>
            </label>
            <select v-model="shippedForm.courier" required class="input">
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
            <label class="block text-sm font-medium text-secondary-700 mb-1">
              Tracking No. <span class="text-error-500">*</span>
            </label>
            <input
              v-model="shippedForm.tracking_number"
              type="text"
              placeholder="Example: PL1234567890MY"
              required
              class="input font-mono"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-1">
              Date Shipped
            </label>
            <input
              v-model="shippedForm.shipped_date"
              type="date"
              class="input"
            />
          </div>
          <div class="flex items-center justify-end space-x-3 pt-2">
            <button
              type="button"
              @click="showMarkShippedModal = false"
              class="btn btn-outline"
              :disabled="actionLoading[shippedForm.card?.id]?.shipped"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="btn btn-primary"
              :disabled="actionLoading[shippedForm.card?.id]?.shipped"
            >
              <span
                v-if="actionLoading[shippedForm.card?.id]?.shipped"
                class="spinner spinner-sm mr-2"
              ></span>
              Mark As Shipped
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Card Details Modal -->
    <div
      v-if="showCardModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
    >
      <div
        class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto"
      >
        <div class="px-6 py-4 border-b border-secondary-200">
          <h3 class="text-lg font-medium text-secondary-900">
            NFC Card Details
          </h3>
        </div>
        <div v-if="selectedCard" class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h4 class="font-medium text-secondary-900 mb-4">
                Card Information
              </h4>
              <dl class="space-y-3">
                <div>
                  <dt class="text-sm font-medium text-secondary-500">
                    NFC Card ID
                  </dt>
                  <dd class="text-sm text-secondary-900 font-mono">
                    {{ selectedCard.nfc_card_id }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-secondary-500">
                    Card Owner
                  </dt>
                  <dd class="text-sm text-secondary-900">
                    {{ selectedCard.card_owner }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-secondary-500">Status</dt>
                  <dd class="text-sm text-secondary-900">
                    <span
                      :class="[
                        'px-2 py-1 text-xs font-medium rounded-full',
                        getStatusBadgeClass(selectedCard.status),
                      ]"
                    >
                      {{ selectedCard.status }}
                    </span>
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-secondary-500">
                    Subscription Plan
                  </dt>
                  <dd class="text-sm text-secondary-900">
                    {{ String(selectedCard.subscription_plan || '').replace(/\b\w/g, c => c.toUpperCase()) }}
                  </dd>
                </div>
              </dl>
            </div>
            <div>
              <h4 class="font-medium text-secondary-900 mb-4">
                Purchase Details
              </h4>
              <dl class="space-y-3">
                <div>
                  <dt class="text-sm font-medium text-secondary-500">
                    Purchase Amount
                  </dt>
                  <dd class="text-sm text-secondary-900">
                    ${{ selectedCard.purchase_amount }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-secondary-500">
                    Purchase Date
                  </dt>
                  <dd class="text-sm text-secondary-900">
                    {{ formatDate(selectedCard.purchase_date) }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-secondary-500">
                    Payment Method
                  </dt>
                  <dd class="text-sm text-secondary-900">
                    {{ selectedCard.payment_method || "N/A" }}
                  </dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-secondary-500">
                    Created
                  </dt>
                  <dd class="text-sm text-secondary-900">
                    {{ formatDate(selectedCard.created_at) }}
                  </dd>
                </div>
              </dl>
            </div>
          </div>
        </div>

        <!-- Public Profile URL for NFC Encoding -->
        <div
          v-if="selectedCard && getCardPublicProfileUrl(selectedCard)"
          class="px-6 py-4 border-t border-secondary-200"
        >
          <div class="flex items-start justify-between gap-3 mb-2">
            <div>
              <h4
                class="text-sm font-semibold text-primary-900 uppercase tracking-wide"
              >
                Public Profile URL
                <span
                  class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-600 text-white align-middle"
                >
                  FOR NFC ENCODING
                </span>
              </h4>
              <p class="text-xs text-secondary-600 mt-1">
                Copy this URL and encode/write it into the physical NFC tag chip
              </p>
            </div>
          </div>
          <div class="flex items-stretch gap-2">
            <input
              :value="getCardPublicProfileUrl(selectedCard, true)"
              readonly
              class="input bg-white font-mono text-xs flex-1"
            />
            <button
              type="button"
              @click="copyToClipboard(getCardPublicProfileUrl(selectedCard, true))"
              class="btn btn-outline btn-sm flex-shrink-0"
              title="Copy URL to clipboard"
            >
              <Icon name="heroicons:document-duplicate" class="h-4 w-4" />
            </button>
            <button
              type="button"
              @click="openInNewTab(getCardPublicProfileUrl(selectedCard, true))"
              class="btn btn-primary btn-sm flex-shrink-0"
              title="Open profile URL to verify it works"
            >
              <Icon name="heroicons:arrow-top-right-on-square" class="h-4 w-4 mr-1" />
              Test URL
            </button>
          </div>
        </div>

        <div class="px-6 py-4 border-t border-secondary-200">
          <button @click="showCardModal = false" class="btn btn-outline">
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
const { nfcCards, loading, pagination } = storeToRefs(adminStore);
const { $api, $toast, $config } = useNuxtApp();

// Modal states
const showRegisterModal = ref(false);
const showEditModal = ref(false);
const showCardModal = ref(false);
const showMarkShippedModal = ref(false);
const selectedCard = ref(null);
const submitting = ref(false);

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
  if (!confirm(`Verify payment for order ${card.card_owner || card.card_id || ''}? After this, the user can start designing their profile.`)) return;
  runNfcAdminAction(card, 'verify-payment', 'verify');
};

// Admin order flow: Reject Payment
const rejectNfcCardPayment = (card) => {
  const reason = prompt(`State the rejection reason for order ${card.card_owner || ''} (will be shown to user). Can be left blank.`, "");
  if (reason === null) return;
  runNfcAdminAction(card, 'reject-payment', 'reject', { cancellation_reason: reason || undefined });
};

// Admin order flow: Mark Processing
const markNfcCardProcessing = (card) => {
  if (!confirm(`Mark order ${card.card_owner || ''} as PROCESSING (print + prepare card)?`)) return;
  runNfcAdminAction(card, 'mark-processing', 'processing');
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
  if (!confirm(`Mark order ${card.card_owner || ''} as DELIVERED? This will activate the user's subscription.`)) return;
  runNfcAdminAction(card, 'mark-delivered', 'delivered');
};

// Admin order flow: Cancel
const cancelNfcCard = (card) => {
  const reason = prompt(`State the CANCELLATION REASON for order ${card.card_owner || ''} (required - will be shown to user).`);
  if (!reason || !String(reason).trim()) {
    $toast.warning("Cancellation reason is required");
    return;
  }
  runNfcAdminAction(card, 'cancel', 'cancel', { cancellation_reason: reason });
};

// Delete card
const deleteCard = async (card) => {
  if (
    confirm(
      `Are you sure you want to delete the NFC card for ${card.card_owner}? This action cannot be undone.`
    )
  ) {
    try {
      await adminStore.deleteNfcCard(card.id);
    } catch (error) {
      console.error("Failed to delete NFC card:", error);
    }
  }
};

// Toggle card status (active/inactive)
const toggleCardStatus = async (card) => {
  const newStatus = card.status === 'active' ? 'inactive' : 'active';
  const action = newStatus === 'active' ? 'activate' : 'deactivate';

  if (
    confirm(
      `Are you sure you want to ${action} the NFC card for ${card.card_owner}?`
    )
  ) {
    try {
      await adminStore.updateNfcCard(card.id, { status: newStatus });
    } catch (error) {
      console.error("Failed to toggle card status:", error);
    }
  }
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

const getStatusBadgeClass = (status) => {
  const classes = {
    pending_payment: "bg-orange-100 text-orange-800",
    awaiting_payment_verification: "bg-yellow-100 text-yellow-800",
    payment_verified: "bg-emerald-100 text-emerald-800",
    processing: "bg-purple-100 text-purple-800",
    shipped: "bg-blue-100 text-blue-800",
    delivered: "bg-green-100 text-green-800",
    active: "bg-success-100 text-success-800",
    inactive: "bg-secondary-100 text-secondary-800",
    expired: "bg-warning-100 text-warning-800",
    cancelled: "bg-red-100 text-red-800",
    replacement: "bg-info-100 text-info-800",
  };
  return classes[status] || "bg-secondary-100 text-secondary-800";
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  return new Date(dateString).toLocaleDateString();
};
</script>
