<!-- pages/UserDashboard/CardManagement.vue -->
<template>
  <div class="min-h-screen bg-secondary-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-secondary-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center space-x-4">
            <div>
              <h1 class="text-2xl font-semibold text-secondary-900">
                Business Plan - Card Management
              </h1>
              <p class="text-sm text-secondary-600">
                Manage Business Plan NFC cards for employees
              </p>
            </div>
          </div>
          <div class="flex items-center space-x-4">
            <button @click="goToDesignPage" class="btn btn-primary">
              <Icon name="heroicons:plus" class="h-4 w-4 mr-2" />
              Order New Card
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Employee Filter Notice -->
      <div
        v-if="route.query.employee_id"
        class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 flex items-center justify-between"
      >
        <div class="flex items-center">
          <Icon name="heroicons:information-circle" class="h-5 w-5 text-blue-600 mr-2" />
          <div>
            <p class="text-sm font-medium text-blue-900">
              Viewing cards for: {{ route.query.employee_name || 'Selected Employee' }}
            </p>
            <p class="text-xs text-blue-700 mt-1">
              You're viewing NFC cards for a specific employee
            </p>
          </div>
        </div>
        <button
          @click="clearEmployeeFilter"
          class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center"
        >
          <Icon name="heroicons:x-mark" class="h-4 w-4 mr-1" />
          View All Cards
        </button>
      </div>

      <!-- Filter and Search Section -->
      <div
        class="bg-white rounded-lg shadow-sm border border-secondary-200 p-4 mb-6"
      >
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Employee Filter -->
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2">
              Filter by Employee
            </label>
            <select
              v-model="filters.employee_id"
              @change="applyFilters"
              class="input w-full"
            >
              <option value="">All Cards (My Cards + Employees)</option>
              <option value="self">My Cards Only</option>
              <option value="employees_only">Employee Cards Only</option>
              <option disabled>──────────</option>
              <option
                v-for="employee in employees"
                :key="employee.id"
                :value="employee.id"
              >
                {{ employee.name }} ({{ employee.email }})
              </option>
            </select>
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2">
              Filter by Status
            </label>
            <select
              v-model="filters.status"
              @change="applyFilters"
              class="input w-full"
            >
              <option value="">All Statuses</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="expired">Expired</option>
            </select>
          </div>

          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2">
              Search Cards
            </label>
            <div class="relative">
              <input
                v-model="filters.search"
                @input="applyFilters"
                type="text"
                placeholder="Search by owner, card ID..."
                class="input w-full pl-10"
              />
              <Icon
                name="heroicons:magnifying-glass"
                class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-secondary-400"
              />
            </div>
          </div>
        </div>

        <!-- Clear Filters -->
        <div class="mt-4 flex items-center justify-between">
          <p class="text-sm text-secondary-600">
            Showing {{ filteredCards.length }} of {{ allCards.length }} cards
          </p>
          <div class="flex items-center space-x-3">
            <button
              @click="loadNfcCards"
              class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center"
            >
              <Icon name="heroicons:arrow-path" class="h-4 w-4 mr-1" />
              Refresh
            </button>
            <button
              v-if="hasActiveFilters"
              @click="clearFilters"
              class="text-sm text-primary-600 hover:text-primary-700 font-medium"
            >
              Clear All Filters
            </button>
          </div>
        </div>
      </div>

      <!-- My Cards Section -->
      <div v-if="myCards.length > 0" class="mb-8">
        <h2
          class="text-lg font-semibold text-secondary-900 mb-4 flex items-center"
        >
          <Icon name="heroicons:user" class="h-5 w-5 mr-2 text-primary-600" />
          My Cards ({{ myCards.length }})
        </h2>
        <div class="space-y-4">
          <div v-for="card in myCards" :key="card.id" class="card">
            <div class="card-header">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-medium text-secondary-900">
                    {{ card.card_owner }}
                  </h3>
                  <p class="text-sm text-secondary-600">
                    Card ID: {{ card.card_id }}
                  </p>
                </div>
                <div class="flex items-center space-x-2">
                  <span
                    :class="getStatusBadgeClass(card.status_badge)"
                    class="px-3 py-1 rounded-full text-xs font-medium"
                  >
                    {{ card.status_badge }}
                  </span>
                  <button
                    @click="editCard(card)"
                    class="btn btn-sm btn-outline"
                  >
                    <Icon name="heroicons:pencil" class="h-4 w-4" />
                  </button>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                  <h4 class="text-sm font-medium text-secondary-500 mb-2">
                    Card Details
                  </h4>
                  <div class="space-y-1">
                    <p class="text-sm text-secondary-900">
                      <strong>NFC ID:</strong> {{ card.nfc_card_id }}
                    </p>
                    <p class="text-sm text-secondary-900">
                      <strong>Plan:</strong> {{ card.subscription_plan }}
                    </p>
                    <p class="text-sm text-secondary-900">
                      <strong>Amount:</strong>
                      {{ card.formatted_purchase_amount }}
                    </p>
                  </div>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-secondary-500 mb-2">
                    Contact
                  </h4>
                  <div class="space-y-1">
                    <p class="text-sm text-secondary-900">
                      {{ card.contact_number }}
                    </p>
                    <p class="text-sm text-secondary-600">
                      {{ card.billing_address }}
                    </p>
                  </div>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-secondary-500 mb-2">
                    Timeline
                  </h4>
                  <div class="space-y-1">
                    <p class="text-sm text-secondary-900">
                      <strong>Purchased:</strong>
                      {{ formatDate(card.purchase_date) }}
                    </p>
                    <p
                      v-if="card.shipped_date"
                      class="text-sm text-secondary-900"
                    >
                      <strong>Shipped:</strong>
                      {{ formatDate(card.shipped_date) }}
                    </p>
                    <p
                      v-if="card.delivered_date"
                      class="text-sm text-secondary-900"
                    >
                      <strong>Delivered:</strong>
                      {{ formatDate(card.delivered_date) }}
                    </p>
                  </div>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-secondary-500 mb-2">
                    Actions
                  </h4>
                  <div class="space-y-2">
                    <button
                      v-if="card.status === 'active'"
                      @click="deactivateCard(card)"
                      class="btn btn-sm btn-outline btn-warning w-full"
                    >
                      <Icon name="heroicons:pause" class="h-4 w-4 mr-1" />
                      Deactivate
                    </button>
                    <button
                      v-else
                      @click="activateCard(card)"
                      class="btn btn-sm btn-outline btn-success w-full"
                    >
                      <Icon name="heroicons:play" class="h-4 w-4 mr-1" />
                      Activate
                    </button>
                    <button
                      @click="viewAnalytics(card)"
                      class="btn btn-sm btn-outline w-full"
                    >
                      <Icon name="heroicons:chart-bar" class="h-4 w-4 mr-1" />
                      Analytics
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Employee Cards Section -->
      <div v-if="employeeCards.length > 0" class="mb-8">
        <h2
          class="text-lg font-semibold text-secondary-900 mb-4 flex items-center"
        >
          <Icon name="heroicons:users" class="h-5 w-5 mr-2 text-primary-600" />
          Employee Cards ({{ employeeCards.length }})
        </h2>
        <div class="space-y-4">
          <div
            v-for="card in employeeCards"
            :key="card.id"
            class="card border-l-4 border-primary-500"
          >
            <div class="card-header">
              <div class="flex items-center justify-between">
                <div>
                  <div class="flex items-center space-x-2">
                    <h3 class="text-lg font-medium text-secondary-900">
                      {{ card.card_owner }}
                    </h3>
                    <span
                      class="px-2 py-0.5 bg-primary-100 text-primary-700 text-xs font-medium rounded"
                    >
                      Employee
                    </span>
                  </div>
                  <p class="text-sm text-secondary-600">
                    Card ID: {{ card.card_id }}
                  </p>
                </div>
                <div class="flex items-center space-x-2">
                  <span
                    :class="getStatusBadgeClass(card.status_badge)"
                    class="px-3 py-1 rounded-full text-xs font-medium"
                  >
                    {{ card.status_badge }}
                  </span>
                  <button
                    @click="editCard(card)"
                    class="btn btn-sm btn-outline"
                  >
                    <Icon name="heroicons:pencil" class="h-4 w-4" />
                  </button>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                  <h4 class="text-sm font-medium text-secondary-500 mb-2">
                    Card Details
                  </h4>
                  <div class="space-y-1">
                    <p class="text-sm text-secondary-900">
                      <strong>NFC ID:</strong> {{ card.nfc_card_id }}
                    </p>
                    <p class="text-sm text-secondary-900">
                      <strong>Plan:</strong> {{ card.subscription_plan }}
                    </p>
                    <p class="text-sm text-secondary-900">
                      <strong>Amount:</strong>
                      {{ card.formatted_purchase_amount }}
                    </p>
                  </div>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-secondary-500 mb-2">
                    Contact
                  </h4>
                  <div class="space-y-1">
                    <p class="text-sm text-secondary-900">
                      {{ card.contact_number }}
                    </p>
                    <p class="text-sm text-secondary-600">
                      {{ card.billing_address }}
                    </p>
                  </div>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-secondary-500 mb-2">
                    Timeline
                  </h4>
                  <div class="space-y-1">
                    <p class="text-sm text-secondary-900">
                      <strong>Purchased:</strong>
                      {{ formatDate(card.purchase_date) }}
                    </p>
                    <p
                      v-if="card.shipped_date"
                      class="text-sm text-secondary-900"
                    >
                      <strong>Shipped:</strong>
                      {{ formatDate(card.shipped_date) }}
                    </p>
                    <p
                      v-if="card.delivered_date"
                      class="text-sm text-secondary-900"
                    >
                      <strong>Delivered:</strong>
                      {{ formatDate(card.delivered_date) }}
                    </p>
                  </div>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-secondary-500 mb-2">
                    Actions
                  </h4>
                  <div class="space-y-2">
                    <button
                      v-if="card.status === 'active'"
                      @click="deactivateCard(card)"
                      class="btn btn-sm btn-outline btn-warning w-full"
                    >
                      <Icon name="heroicons:pause" class="h-4 w-4 mr-1" />
                      Deactivate
                    </button>
                    <button
                      v-else
                      @click="activateCard(card)"
                      class="btn btn-sm btn-outline btn-success w-full"
                    >
                      <Icon name="heroicons:play" class="h-4 w-4 mr-1" />
                      Activate
                    </button>
                    <button
                      @click="viewAnalytics(card)"
                      class="btn btn-sm btn-outline w-full"
                    >
                      <Icon name="heroicons:chart-bar" class="h-4 w-4 mr-1" />
                      Analytics
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- No Cards Message -->
      <div v-if="allCards.length === 0 && !loading" class="text-center py-12">
        <div class="max-w-md mx-auto">
          <Icon
            name="heroicons:credit-card"
            class="h-16 w-16 text-secondary-400 mx-auto mb-4"
          />
          <h3 class="text-xl font-semibold text-secondary-900 mb-2">
            No Business Plan Cards
          </h3>
          <p class="text-secondary-600 mb-6">
            You haven't ordered any Business Plan NFC cards yet. Order cards for
            your employees to get started.
          </p>
          <button @click="orderNewCard" class="btn btn-primary">
            <Icon name="heroicons:plus" class="h-4 w-4 mr-2" />
            Order Your First Card
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="spinner mx-auto mb-4"></div>
        <p class="text-secondary-600">Loading card information...</p>
      </div>
    </div>

    <!-- Order New Card Modal -->
    <Transition name="modal">
      <div v-if="showOrderModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
          <div
            class="fixed inset-0 bg-black bg-opacity-50"
            @click="showOrderModal = false"
          ></div>
          <div class="bg-white rounded-lg max-w-md w-full p-6 relative">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-secondary-900">
                Order Business Plan NFC Card
              </h3>
              <button
                @click="showOrderModal = false"
                class="text-secondary-400 hover:text-secondary-600"
              >
                <Icon name="heroicons:x-mark" class="h-6 w-6" />
              </button>
            </div>

            <form @submit.prevent="submitOrder" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1"
                  >Card Owner</label
                >
                <input
                  v-model="orderForm.card_owner"
                  type="text"
                  class="input w-full"
                  required
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1"
                  >Billing Address</label
                >
                <textarea
                  v-model="orderForm.billing_address"
                  class="input w-full"
                  rows="3"
                  required
                ></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1"
                  >Contact Number</label
                >
                <input
                  v-model="orderForm.contact_number"
                  type="tel"
                  class="input w-full"
                  required
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1"
                  >Subscription Plan</label
                >
                <input
                  type="text"
                  value="Business - $99/month"
                  class="input w-full bg-secondary-50"
                  disabled
                  readonly
                />
                <p class="text-xs text-secondary-500 mt-1">
                  Business Plan is automatically applied for employee cards
                </p>
              </div>
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1"
                  >Shipping Address (Optional)</label
                >
                <textarea
                  v-model="orderForm.shipping_address"
                  class="input w-full"
                  rows="2"
                ></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1"
                  >Notes (Optional)</label
                >
                <textarea
                  v-model="orderForm.notes"
                  class="input w-full"
                  rows="2"
                ></textarea>
              </div>

              <div class="flex items-center justify-end space-x-3 pt-4">
                <button
                  type="button"
                  @click="showOrderModal = false"
                  class="btn btn-outline"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="btn btn-primary"
                  :disabled="orderLoading"
                >
                  <div v-if="orderLoading" class="spinner mr-2"></div>
                  Place Order
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Card Details Modal (Read-only) -->
    <Transition name="modal">
      <div
        v-if="showCardDetailsModal && selectedCard"
        class="fixed inset-0 z-50 overflow-y-auto"
      >
        <div class="flex items-center justify-center min-h-screen px-4">
          <div
            class="fixed inset-0 bg-black bg-opacity-50"
            @click="showCardDetailsModal = false"
          ></div>
          <div
            class="bg-white rounded-lg max-w-3xl w-full p-6 relative max-h-[90vh] overflow-y-auto"
          >
            <div class="flex items-center justify-between mb-6">
              <div>
                <h3 class="text-xl font-semibold text-secondary-900">
                  Card Details
                </h3>
                <p class="text-sm text-secondary-600 mt-1">
                  View all information for {{ selectedCard.card_owner }}'s card
                </p>
              </div>
              <button
                @click="showCardDetailsModal = false"
                class="text-secondary-400 hover:text-secondary-600"
              >
                <Icon name="heroicons:x-mark" class="h-6 w-6" />
              </button>
            </div>

            <div class="space-y-6">
              <!-- Status Badge -->
              <div
                class="flex items-center justify-between p-4 bg-secondary-50 rounded-lg"
              >
                <div>
                  <p class="text-sm text-secondary-600">Status</p>
                  <p
                    class="text-lg font-semibold text-secondary-900 capitalize"
                  >
                    {{ selectedCard.status }}
                  </p>
                </div>
                <span
                  :class="getStatusBadgeClass(selectedCard.status_badge)"
                  class="px-4 py-2 rounded-full text-sm font-medium"
                >
                  {{ selectedCard.status_badge }}
                </span>
              </div>

              <!-- Card Information -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                  <h4
                    class="text-sm font-semibold text-secondary-900 uppercase tracking-wide"
                  >
                    Card Information
                  </h4>

                  <div>
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Card ID</label
                    >
                    <p
                      class="text-base text-secondary-900 font-mono bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ selectedCard.card_id }}
                    </p>
                  </div>

                  <div>
                    <label class="block text-sm text-secondary-600 mb-1"
                      >NFC Card ID</label
                    >
                    <p
                      class="text-base text-secondary-900 font-mono bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ selectedCard.nfc_card_id || "Not assigned" }}
                    </p>
                  </div>

                  <div>
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Card Owner</label
                    >
                    <p
                      class="text-base text-secondary-900 bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ selectedCard.card_owner }}
                    </p>
                  </div>

                  <div>
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Subscription Plan</label
                    >
                    <p
                      class="text-base text-secondary-900 capitalize bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ selectedCard.subscription_plan }}
                    </p>
                  </div>
                </div>

                <div class="space-y-4">
                  <h4
                    class="text-sm font-semibold text-secondary-900 uppercase tracking-wide"
                  >
                    Contact Information
                  </h4>

                  <div>
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Contact Number</label
                    >
                    <p
                      class="text-base text-secondary-900 bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ selectedCard.contact_number }}
                    </p>
                  </div>

                  <div>
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Billing Address</label
                    >
                    <p
                      class="text-base text-secondary-900 bg-secondary-50 px-3 py-2 rounded whitespace-pre-wrap"
                    >
                      {{ selectedCard.billing_address }}
                    </p>
                  </div>

                  <div v-if="selectedCard.shipping_address">
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Shipping Address</label
                    >
                    <p
                      class="text-base text-secondary-900 bg-secondary-50 px-3 py-2 rounded whitespace-pre-wrap"
                    >
                      {{ selectedCard.shipping_address }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Financial & Timeline Information -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                  <h4
                    class="text-sm font-semibold text-secondary-900 uppercase tracking-wide"
                  >
                    Financial Details
                  </h4>

                  <div>
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Purchase Amount</label
                    >
                    <p
                      class="text-lg font-semibold text-primary-600 bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ selectedCard.formatted_purchase_amount }}
                    </p>
                  </div>

                  <div v-if="selectedCard.payment_method">
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Payment Method</label
                    >
                    <p
                      class="text-base text-secondary-900 capitalize bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ selectedCard.payment_method }}
                    </p>
                  </div>
                </div>

                <div class="space-y-4">
                  <h4
                    class="text-sm font-semibold text-secondary-900 uppercase tracking-wide"
                  >
                    Timeline
                  </h4>

                  <div>
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Purchase Date</label
                    >
                    <p
                      class="text-base text-secondary-900 bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ formatDate(selectedCard.purchase_date) }}
                    </p>
                  </div>

                  <div v-if="selectedCard.shipped_date">
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Shipped Date</label
                    >
                    <p
                      class="text-base text-secondary-900 bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ formatDate(selectedCard.shipped_date) }}
                    </p>
                  </div>

                  <div v-if="selectedCard.delivered_date">
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Delivered Date</label
                    >
                    <p
                      class="text-base text-secondary-900 bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ formatDate(selectedCard.delivered_date) }}
                    </p>
                  </div>

                  <div v-if="selectedCard.expiry_date">
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Expiry Date</label
                    >
                    <p
                      class="text-base text-secondary-900 bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ formatDate(selectedCard.expiry_date) }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Shipping Information -->
              <div v-if="selectedCard.tracking_number" class="space-y-4">
                <h4
                  class="text-sm font-semibold text-secondary-900 uppercase tracking-wide"
                >
                  Shipping Information
                </h4>

                <div>
                  <label class="block text-sm text-secondary-600 mb-1"
                    >Tracking Number</label
                  >
                  <p
                    class="text-base text-secondary-900 font-mono bg-secondary-50 px-3 py-2 rounded"
                  >
                    {{ selectedCard.tracking_number }}
                  </p>
                </div>
              </div>

              <!-- Notes -->
              <div v-if="selectedCard.notes" class="space-y-4">
                <h4
                  class="text-sm font-semibold text-secondary-900 uppercase tracking-wide"
                >
                  Notes
                </h4>

                <div class="bg-secondary-50 px-3 py-2 rounded">
                  <p class="text-base text-secondary-900 whitespace-pre-wrap">
                    {{ selectedCard.notes }}
                  </p>
                </div>
              </div>

              <!-- NFC Tag Information -->
              <div v-if="selectedCard.nfcTag" class="space-y-4 border-t pt-6">
                <h4
                  class="text-sm font-semibold text-secondary-900 uppercase tracking-wide"
                >
                  Linked Profile
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm text-secondary-600 mb-1"
                      >NFC Tag ID</label
                    >
                    <p
                      class="text-base text-secondary-900 font-mono bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ selectedCard.nfcTag.nfc_id }}
                    </p>
                  </div>

                  <div>
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Profile Name</label
                    >
                    <p
                      class="text-base text-secondary-900 bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ selectedCard.nfcTag.name }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div
                class="flex items-center justify-end space-x-3 pt-6 border-t"
              >
                <button
                  @click="showCardDetailsModal = false"
                  class="btn btn-outline"
                >
                  Close
                </button>
                <button @click="goToProfileBuilder" class="btn btn-primary">
                  <Icon name="heroicons:pencil-square" class="h-4 w-4 mr-2" />
                  {{
                    selectedCard.nfcTag
                      ? "Edit Profile in Profile Builder"
                      : "Edit Landing Page"
                  }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
// Layout
definePageMeta({
  layout: "user-dashboard",
});

// Stores
const authStore = useAuthStore();
const { $toast, $api } = useNuxtApp();

// Reactive data
const loading = ref(true);
const nfcCards = ref([]);
const allCards = ref([]);
const employees = ref([]);
const subscriptionData = ref(null);
const showOrderModal = ref(false);
const orderLoading = ref(false);
const showCardDetailsModal = ref(false);
const selectedCard = ref(null);

// Filters
const filters = ref({
  employee_id: "",
  status: "",
  search: "",
});

const orderForm = ref({
  card_owner: "",
  billing_address: "",
  contact_number: "",
  subscription_plan: "business",
  shipping_address: "",
  notes: "",
});

// Computed properties
const myCards = computed(() => {
  const currentUserId = authStore.user?.id;

  const filtered = filteredCards.value.filter((card) => {
    // Prefer explicit business flags if present
    if (
      typeof card.is_admin_card !== "undefined" ||
      typeof card.is_employee_card !== "undefined"
    ) {
      return !!card.is_admin_card;
    }

    // Fallback: use ownership by user_id
    return card.user_id === currentUserId;
  });

  console.log("myCards computed:", {
    currentUserId,
    totalCards: filteredCards.value.length,
    myCardsCount: filtered.length,
    cards: filtered,
  });

  return filtered;
});

const employeeCards = computed(() => {
  const currentUserId = authStore.user?.id;

  return filteredCards.value.filter((card) => {
    // Prefer explicit business flags if present
    if (
      typeof card.is_admin_card !== "undefined" ||
      typeof card.is_employee_card !== "undefined"
    ) {
      return !!card.is_employee_card && !card.is_admin_card;
    }

    // Fallback: any card not owned by current user is treated as employee card
    return card.user_id !== currentUserId;
  });
});

const filteredCards = computed(() => {
  let cards = [...allCards.value];
  const currentUserId = authStore.user?.id;
  
  console.log("filteredCards computed:", {
    allCardsCount: allCards.value.length,
    allCards: allCards.value,
    currentUserId,
    filters: filters.value
  });

  // Filter by employee
  if (filters.value.employee_id === "self") {
    cards = cards.filter((card) => card.user_id === currentUserId);
  } else if (filters.value.employee_id === "employees_only") {
    cards = cards.filter((card) => card.user_id !== currentUserId);
  } else if (filters.value.employee_id) {
    cards = cards.filter(
      (card) => card.user_id === parseInt(filters.value.employee_id)
    );
  }

  // Filter by status
  if (filters.value.status) {
    cards = cards.filter((card) => card.status === filters.value.status);
  }

  // Search
  if (filters.value.search) {
    const searchLower = filters.value.search.toLowerCase();
    cards = cards.filter(
      (card) =>
        card.card_owner?.toLowerCase().includes(searchLower) ||
        card.card_id?.toLowerCase().includes(searchLower) ||
        card.nfc_card_id?.toLowerCase().includes(searchLower)
    );
  }

  console.log("filteredCards result:", cards);
  return cards;
});

const hasActiveFilters = computed(() => {
  return (
    filters.value.employee_id || filters.value.status || filters.value.search
  );
});

// Methods
const router = useRouter();
const route = useRoute();

const goToDesignPage = () => {
  router.push("/UserDashboard/NFCCardDesign/BusinessPlanNFCCard");
};

const loadEmployees = async () => {
  try {
    const response = await $api.get("/business/employees");
    if (response.success) {
      employees.value = response.employees || [];
    }
  } catch (error) {
    console.error("Failed to load employees:", error);
  }
};

const loadNfcCards = async () => {
  try {
    console.log("Loading NFC cards...");
    const response = await $api.get("/nfc-cards");
    console.log("NFC cards response:", response);
    if (response.success) {
      console.log("Cards loaded:", response.nfc_cards);
      allCards.value = response.nfc_cards;
      nfcCards.value = response.nfc_cards;
    } else {
      console.error("Response not successful:", response);
    }
  } catch (error) {
    $toast.error("Failed to load NFC cards");
    console.error("Failed to load NFC cards:", error);
  } finally {
    loading.value = false;
  }
};

const applyFilters = () => {
  // Filters are applied via computed property
  // This function is called to trigger reactivity
};

const clearFilters = () => {
  filters.value = {
    employee_id: "",
    status: "",
    search: "",
  };
};

const clearEmployeeFilter = () => {
  // Clear filter
  filters.value.employee_id = "";
  // Remove query params from URL
  router.push({ 
    path: route.path,
    query: {} 
  });
};

const orderNewCard = () => {
  // Pre-fill form with user data
  const user = authStore.user;
  orderForm.value.card_owner = user?.full_name || "";
  orderForm.value.contact_number = user?.phone || "";
  showOrderModal.value = true;
};

const submitOrder = async () => {
  orderLoading.value = true;
  try {
    const selectedPlan = orderForm.value.subscription_plan;
    const currentUser = authStore.user;

    // Check if user is selecting Business Plan
    if (selectedPlan === "business") {
      // Check if user has permission to use Business Plan
      const isBusinessAccount = currentUser?.subscription_plan === "business";
      const isBusinessEmployee = currentUser?.parent_business_id !== null;

      // If user is neither Business account nor employee under Business account
      if (!isBusinessAccount && !isBusinessEmployee) {
        $toast.warning(
          "Business Plan is not available. Redirecting to purchase page..."
        );
        setTimeout(() => {
          navigateTo("/UserDashboard/PlanSelection");
        }, 1500);
        return;
      }

      // If user is employee but trying to order Business Plan without parent permission
      if (isBusinessEmployee && !isBusinessAccount) {
        // Need to verify with backend that parent has quota
        // Backend will check: parent_business_id has available quota
      }
    }

    // Prepare order data
    const orderData = {
      ...orderForm.value,
      purchase_amount: getPlanPrice(selectedPlan),
    };

    // For Business Plan orders, always check card quota
    // This applies to:
    // 1. Business account (main) ordering Business Plan
    // 2. Employee account ordering Business Plan (checks parent's quota)
    if (selectedPlan === "business") {
      orderData.check_card_quota = true; // Tell backend to validate card quota

      // If current user is employee, include parent_business_id for quota tracking
      if (currentUser?.parent_business_id) {
        orderData.parent_business_id = currentUser.parent_business_id;
      }
    }

    const response = await $api.post("/nfc-cards", orderData);

    if (response.success) {
      $toast.success("NFC card order placed successfully!");
      showOrderModal.value = false;

      // Redirect to NFCCardDesign page based on selected plan
      const planRoutes = {
        basic: "/UserDashboard/NFCCardDesign/BasicPlanNFCCard",
        premium: "/UserDashboard/NFCCardDesign/PremiumPlanNFCCard",
        business: "/UserDashboard/NFCCardDesign/BusinessPlanNFCCard",
      };

      const redirectPath =
        planRoutes[selectedPlan] || "/UserDashboard/NFCCardDesign";

      // Wait a moment for the toast to show, then redirect
      setTimeout(() => {
        navigateTo(redirectPath);
      }, 1000);
    }
  } catch (error) {
    if (
      error.response?.status === 403 &&
      error.response?.data?.upgrade_required
    ) {
      $toast.error("This feature requires a Premium subscription");
    } else if (
      error.response?.status === 403 &&
      error.response?.data?.business_plan_not_allowed
    ) {
      // User not authorized to use Business Plan
      $toast.error(
        error.response.data.message ||
          "You are not authorized to use Business Plan. Redirecting to purchase page..."
      );
      setTimeout(() => {
        navigateTo("/UserDashboard/PlanSelection");
      }, 1500);
    } else {
      $toast.error("Failed to place order");
      console.error("Order error:", error);
    }
  } finally {
    orderLoading.value = false;
  }
};

const editCard = (card) => {
  // Navigate to BusinessProfileBuilder with the selected card's NFC tag and open Apply Design modal
  if (card.nfcTag?.id) {
    router.push(
      `/UserDashboard/UserManagement/BusinessPlanUser/BusinessProfileBuilder?nfc_tag_id=${card.nfcTag.id}&openApplyDesign=true`
    );
  } else if (card.nfc_card_id) {
    // If no profile yet, pass the card ID to create a new profile and open Apply Design modal
    router.push(
      `/UserDashboard/UserManagement/BusinessPlanUser/BusinessProfileBuilder?nfc_card_id=${card.nfc_card_id}&openApplyDesign=true`
    );
  } else {
    $toast.error("Cannot edit this card: No valid card ID found");
  }
};

const goToProfileBuilder = () => {
  if (!selectedCard.value) {
    $toast.error("No card selected");
    return;
  }

  // If card has a linked profile, edit it
  if (selectedCard.value.nfcTag?.id) {
    router.push(
      `/UserDashboard/UserManagement/BusinessPlanUser/BusinessProfileBuilder?nfc_tag_id=${selectedCard.value.nfcTag.id}`
    );
  } else {
    // If no profile, go to Profile Builder to create new one with this card pre-selected
    router.push(
      `/UserDashboard/UserManagement/BusinessPlanUser/BusinessProfileBuilder?nfc_card_id=${selectedCard.value.nfc_card_id}`
    );
  }
};

const activateCard = async (card) => {
  try {
    const response = await $api.post(`/nfc-cards/${card.id}/activate`, {
      nfc_id: `NFC-${Math.random().toString(36).substr(2, 9).toUpperCase()}`,
      name: card.card_owner,
    });

    if (response.success) {
      $toast.success("Card activated successfully");
      await loadNfcCards();
    }
  } catch (error) {
    $toast.error("Failed to activate card");
    console.error("Activate error:", error);
  }
};

const deactivateCard = async (card) => {
  if (!confirm("Are you sure you want to deactivate this card?")) return;

  try {
    const response = await $api.post(`/nfc-cards/${card.id}/deactivate`);

    if (response.success) {
      $toast.success("Card deactivated successfully");
      await loadNfcCards();
    }
  } catch (error) {
    $toast.error("Failed to deactivate card");
    console.error("Deactivate error:", error);
  }
};

const viewAnalytics = (card) => {
  // Navigate to analytics page for this specific card
  navigateTo(`/UserDashboard/card-management/${card.id}/analytics`);
};

const getStatusBadgeClass = (status) => {
  const classes = {
    active: "bg-success-100 text-success-800",
    inactive: "bg-secondary-100 text-secondary-800",
    expired: "bg-warning-100 text-warning-800",
    replacement: "bg-info-100 text-info-800",
  };
  return classes[status] || classes["inactive"];
};

const getPlanPrice = (plan) => {
  const prices = {
    basic: 29.0,
    premium: 49.0,
    business: 99.0,
  };
  return prices[plan] || 49.0;
};

const formatDate = (date) => {
  if (!date) return "N/A";
  return new Date(date).toLocaleDateString();
};

// Lifecycle
let refreshInterval = null;

onMounted(async () => {
  console.log("Current user:", authStore.user);
  console.log("User ID:", authStore.user?.id);
  console.log("Subscription plan:", authStore.user?.subscription_plan);
  
  await Promise.all([loadNfcCards(), loadEmployees()]);
  
  // Check if we have employee_id in query params (coming from Employee Management page)
  const employeeIdParam = route.query.employee_id;
  const employeeNameParam = route.query.employee_name;
  
  if (employeeIdParam) {
    // Set filter to show only this employee's cards
    filters.value.employee_id = parseInt(employeeIdParam);
    
    // Show toast notification
    if (employeeNameParam) {
      $toast.info(`Showing NFC cards for ${employeeNameParam}`);
    }
  }
  
  // Auto-refresh cards every 10 seconds to show newly approved cards
  refreshInterval = setInterval(() => {
    loadNfcCards();
  }, 10000);
});

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
  }
});
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>
