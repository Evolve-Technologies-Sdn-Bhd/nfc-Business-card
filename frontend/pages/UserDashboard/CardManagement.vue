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
                Card Management
              </h1>
              <p class="text-sm text-secondary-600">
                Manage your physical NFC cards and subscriptions
              </p>
            </div>
          </div>
          <div class="flex items-center space-x-4">
            <button
              v-if="!hasProSubscription"
              @click="upgradeToPro"
              class="btn btn-primary"
            >
              <Icon name="heroicons:star" class="h-4 w-4 mr-2" />
              Upgrade to Pro
            </button>
            <button v-else @click="orderNewCard" class="btn btn-primary">
              <Icon name="heroicons:plus" class="h-4 w-4 mr-2" />
              Order New Card
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Subscription Status -->
      <div v-if="subscriptionData" class="mb-8">
        <div class="card">
          <div class="card-header">
            <h3 class="text-lg font-medium text-secondary-900">
              Subscription Status
            </h3>
          </div>
          <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="text-center">
                <div class="text-2xl font-bold text-primary-600">
                  {{ subscriptionData.subscription_plan }}
                </div>
                <p class="text-sm text-secondary-600">Current Plan</p>
              </div>
              <div class="text-center">
                <div
                  class="text-2xl font-bold"
                  :class="
                    subscriptionData.subscription_active
                      ? 'text-success-600'
                      : 'text-warning-600'
                  "
                >
                  {{
                    subscriptionData.subscription_active ? "Active" : "Inactive"
                  }}
                </div>
                <p class="text-sm text-secondary-600">Status</p>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-secondary-900">
                  {{
                    subscriptionData.subscription_end_date
                      ? formatDate(subscriptionData.subscription_end_date)
                      : "N/A"
                  }}
                </div>
                <p class="text-sm text-secondary-600">Expires</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Upgrade Required Message -->
      <div v-if="!hasProSubscription" class="text-center py-12">
        <div class="max-w-md mx-auto">
          <Icon
            name="heroicons:lock-closed"
            class="h-16 w-16 text-secondary-400 mx-auto mb-4"
          />
          <h3 class="text-xl font-semibold text-secondary-900 mb-2">
            Pro Feature
          </h3>
          <p class="text-secondary-600 mb-6">
            Physical NFC card management is available exclusively to Pro
            subscribers. Upgrade your plan to access this feature.
          </p>
          <button @click="upgradeToPro" class="btn btn-primary btn-lg">
            <Icon name="heroicons:star" class="h-5 w-5 mr-2" />
            Upgrade to Pro
          </button>
        </div>
      </div>

      <!-- NFC Cards List -->
      <div v-else-if="nfcCards.length > 0" class="space-y-6">
        <div v-for="card in nfcCards" :key="card.id" class="card">
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
                <button @click="editCard(card)" class="btn btn-sm btn-outline">
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

      <!-- No Cards Message -->
      <div v-else-if="hasProSubscription" class="text-center py-12">
        <div class="max-w-md mx-auto">
          <Icon
            name="heroicons:credit-card"
            class="h-16 w-16 text-secondary-400 mx-auto mb-4"
          />
          <h3 class="text-xl font-semibold text-secondary-900 mb-2">
            No Physical Cards
          </h3>
          <p class="text-secondary-600 mb-6">
            You haven't ordered any physical NFC cards yet. Order your first
            card to get started.
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
                Order New NFC Card
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
                <select
                  v-model="orderForm.subscription_plan"
                  class="input w-full"
                  required
                >
                  <option value="basic">Basic - $29/month</option>
                  <option value="pro">Pro - $49/month</option>
                  <option value="enterprise">Enterprise - $99/month</option>
                </select>
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
const subscriptionData = ref(null);
const showOrderModal = ref(false);
const orderLoading = ref(false);

const orderForm = ref({
  card_owner: "",
  billing_address: "",
  contact_number: "",
  subscription_plan: "pro",
  shipping_address: "",
  notes: "",
});

// Computed
const hasProSubscription = computed(() => {
  return subscriptionData.value?.has_pro_subscription || false;
});

// Methods
const loadSubscriptionStatus = async () => {
  try {
    const response = await $api.get("/subscription/status");
    if (response.success) {
      subscriptionData.value = response.data;
    }
  } catch (error) {
    console.error("Failed to load subscription status:", error);
  }
};

const loadNfcCards = async () => {
  if (!hasProSubscription.value) {
    loading.value = false;
    return;
  }

  try {
    const response = await $api.get("/nfc-cards");
    if (response.success) {
      nfcCards.value = response.nfc_cards;
    }
  } catch (error) {
    if (
      error.response?.status === 403 &&
      error.response?.data?.upgrade_required
    ) {
      // User needs to upgrade
      $toast.info("This feature requires a Pro subscription");
    } else {
      $toast.error("Failed to load NFC cards");
      console.error("Failed to load NFC cards:", error);
    }
  } finally {
    loading.value = false;
  }
};

const upgradeToPro = () => {
  // Redirect to upgrade page or show upgrade modal
  $toast.info("Redirecting to upgrade page...");
  // navigateTo('/upgrade')
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
    const response = await $api.post("/nfc-cards", {
      ...orderForm.value,
      purchase_amount: getPlanPrice(orderForm.value.subscription_plan),
    });

    if (response.success) {
      $toast.success("NFC card order placed successfully!");
      showOrderModal.value = false;
      await loadNfcCards();
      await loadSubscriptionStatus();
    }
  } catch (error) {
    if (
      error.response?.status === 403 &&
      error.response?.data?.upgrade_required
    ) {
      $toast.error("This feature requires a Pro subscription");
    } else {
      $toast.error("Failed to place order");
      console.error("Order error:", error);
    }
  } finally {
    orderLoading.value = false;
  }
};

const editCard = (card) => {
  // Navigate to edit page or show edit modal
  $toast.info("Edit functionality coming soon...");
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
    pro: 49.0,
    enterprise: 99.0,
  };
  return prices[plan] || 49.0;
};

const formatDate = (date) => {
  if (!date) return "N/A";
  return new Date(date).toLocaleDateString();
};

// Lifecycle
onMounted(async () => {
  await loadSubscriptionStatus();
  await loadNfcCards();
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
