<!-- pages/AdminManagement/nfc-cards.vue -->
<template>
  <div>
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-3xl font-bold text-secondary-900">
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
              @input="handleSearch"
            />
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
              <option value="pending">Pending</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="shipped">Shipped</option>
              <option value="delivered">Delivered</option>
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
              @change="handleFilterChange"
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
                  class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  Card Details
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  User
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  Status
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider"
                >
                  Purchase Info
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
                v-for="card in nfcCards"
                :key="card.id"
                class="hover:bg-secondary-50"
              >
                <td class="px-6 py-4 whitespace-nowrap">
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
                <td class="px-6 py-4 whitespace-nowrap">
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
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'px-2 py-1 text-xs font-medium rounded-full',
                      getStatusBadgeClass(card.status),
                    ]"
                  >
                    {{ card.status }}
                  </span>
                  <div
                    v-if="card.tracking_number"
                    class="text-xs text-secondary-500 mt-1"
                  >
                    Tracking: {{ card.tracking_number }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-secondary-900">
                    <span class="font-medium">${{ card.purchase_amount }}</span>
                  </div>
                  <div class="text-sm text-secondary-500">
                    {{ card.subscription_plan }}
                  </div>
                  <div class="text-xs text-secondary-400">
                    {{ formatDate(card.purchase_date) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex items-center space-x-2">
                    <button
                      @click="viewCard(card)"
                      class="text-primary-600 hover:text-primary-900"
                    >
                      <Icon name="heroicons:eye" class="h-4 w-4" />
                    </button>
                    <button
                      @click="editCard(card)"
                      class="text-warning-600 hover:text-warning-900"
                    >
                      <Icon name="heroicons:pencil" class="h-4 w-4" />
                    </button>
                    <button
                      @click="deleteCard(card)"
                      class="text-error-600 hover:text-error-900"
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
    <div
      v-if="pagination.total > pagination.per_page"
      class="mt-6 flex items-center justify-between"
    >
      <div class="flex items-center space-x-2">
        <span class="text-sm text-secondary-700">
          Showing
          {{ (pagination.current_page - 1) * pagination.per_page + 1 }} to
          {{
            Math.min(
              pagination.current_page * pagination.per_page,
              pagination.total
            )
          }}
          of {{ pagination.total }} results
        </span>
      </div>
      <div class="flex items-center space-x-2">
        <button
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="btn btn-outline btn-sm"
        >
          Previous
        </button>
        <span class="text-sm text-secondary-700">
          Page {{ pagination.current_page }} of {{ pagination.total_pages }}
        </span>
        <button
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.total_pages"
          class="btn btn-outline btn-sm"
        >
          Next
        </button>
      </div>
    </div>

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
              placeholder="e.g., NFC-ABC123DEF456"
              class="input font-mono"
            />
            <p class="text-xs text-secondary-500 mt-1">
              Enter the unique identifier printed on the physical NFC card
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Card Owner *</label
            >
            <input
              v-model="form.card_owner"
              type="text"
              required
              placeholder="Full name of card owner"
              class="input"
            />
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
              <input
                v-model="form.contact_number"
                type="tel"
                required
                placeholder="Phone number"
                class="input"
              />
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
                v-model="form.purchase_amount"
                type="number"
                step="0.01"
                required
                placeholder="0.00"
                class="input"
              />
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
              <input
                v-model="editForm.contact_number"
                type="tel"
                required
                class="input"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Status</label
              >
              <select v-model="editForm.status" class="input">
                <option value="pending">Pending</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2"
                >Tracking Number</label
              >
              <input
                v-model="editForm.tracking_number"
                type="text"
                placeholder="Shipping tracking number"
                class="input"
              />
            </div>
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

          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Notes</label
            >
            <textarea
              v-model="editForm.notes"
              class="input"
              rows="2"
            ></textarea>
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
                    {{ selectedCard.subscription_plan }}
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

// Modal states
const showRegisterModal = ref(false);
const showEditModal = ref(false);
const showCardModal = ref(false);
const selectedCard = ref(null);
const submitting = ref(false);

// Form data
const form = ref({
  user_id: "",
  nfc_card_id: "",
  card_owner: "",
  billing_address: "",
  contact_number: "",
  subscription_plan: "basic",
  purchase_amount: "",
  payment_method: "",
  shipping_address: "",
  notes: "",
});

const editForm = ref({
  card_owner: "",
  billing_address: "",
  contact_number: "",
  status: "",
  tracking_number: "",
  shipped_date: "",
  delivered_date: "",
  notes: "",
});

// Filters
const filters = ref({
  search: "",
  status: "",
  subscription_plan: "",
  sort_by: "created_at",
});

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
    const { $api } = useNuxtApp();
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

// Handle filter changes
const handleFilterChange = () => {
  adminStore.updateFilters(filters.value);
  adminStore.fetchNfcCards();
};

// Clear all filters
const clearFilters = () => {
  adminStore.clearFilters();
  filters.value = {
    search: "",
    status: "",
    subscription_plan: "",
    sort_by: "created_at",
  };
  adminStore.fetchNfcCards();
};

// Change page
const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    adminStore.fetchNfcCards({ page });
  }
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
    tracking_number: card.tracking_number || "",
    shipped_date: card.shipped_date ? card.shipped_date.split("T")[0] : "",
    delivered_date: card.delivered_date
      ? card.delivered_date.split("T")[0]
      : "",
    notes: card.notes || "",
  };
  showEditModal.value = true;
};

// Handle form submission
const handleSubmit = async () => {
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
  form.value = {
    user_id: "",
    nfc_card_id: "",
    card_owner: "",
    billing_address: "",
    contact_number: "",
    subscription_plan: "basic",
    purchase_amount: "",
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
    tracking_number: "",
    shipped_date: "",
    delivered_date: "",
    notes: "",
  };
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

// Helper functions
const getStatusBadgeClass = (status) => {
  const classes = {
    pending: "bg-yellow-100 text-yellow-800",
    active: "bg-success-100 text-success-800",
    inactive: "bg-error-100 text-error-800",
    shipped: "bg-blue-100 text-blue-800",
    delivered: "bg-green-100 text-green-800",
  };
  return classes[status] || classes.pending;
};

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  return new Date(dateString).toLocaleDateString();
};
</script>
