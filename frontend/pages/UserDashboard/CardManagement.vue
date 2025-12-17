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
              v-if="!hasPremiumSubscription"
              @click="upgradeToPremium"
              class="btn btn-primary"
            >
              <Icon name="heroicons:star" class="h-4 w-4 mr-2" />
              Upgrade to Premium
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
                <div class="text-2xl font-bold text-primary-600 capitalize">
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
      <div v-if="!hasPremiumSubscription" class="text-center py-12">
        <div class="max-w-md mx-auto">
          <Icon
            name="heroicons:lock-closed"
            class="h-16 w-16 text-secondary-400 mx-auto mb-4"
          />
          <h3 class="text-xl font-semibold text-secondary-900 mb-2">
            Premium Feature
          </h3>
          <p class="text-secondary-600 mb-6">
            Physical NFC card management is available exclusively to Premium
            subscribers. Upgrade your plan to access this feature.
          </p>
          <button @click="upgradeToPremium" class="btn btn-primary btn-lg">
            <Icon name="heroicons:star" class="h-5 w-5 mr-2" />
            Upgrade to Premium
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
      <div v-else-if="hasPremiumSubscription" class="text-center py-12">
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
                  <option value="premium">Premium - $49/month</option>
                  <option value="business">Business - $99/month</option>
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
const subscriptionData = ref(null);
const showOrderModal = ref(false);
const orderLoading = ref(false);
const showCardDetailsModal = ref(false);
const selectedCard = ref(null);

const orderForm = ref({
  card_owner: "",
  billing_address: "",
  contact_number: "",
  subscription_plan: "basic,premium,business",
  shipping_address: "",
  notes: "",
});

// Computed
const hasPremiumSubscription = computed(() => {
  return subscriptionData.value?.has_premium_subscription || false;
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
  if (!hasPremiumSubscription.value) {
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
      $toast.info("This feature requires a Premium subscription");
    } else {
      $toast.error("Failed to load NFC cards");
      console.error("Failed to load NFC cards:", error);
    }
  } finally {
    loading.value = false;
  }
};

const upgradeToPremium = () => {
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
      $toast.error("This feature requires a Premium subscription");
    } else {
      $toast.error("Failed to place order");
      console.error("Order error:", error);
    }
  } finally {
    orderLoading.value = false;
  }
};

const editCard = (card) => {
  selectedCard.value = card;
  showCardDetailsModal.value = true;
};

const goToProfileBuilder = () => {
  if (!selectedCard.value) {
    $toast.error("No card selected");
    return;
  }

  // Get user's subscription plan
  const userPlan = authStore.user?.subscription_plan?.toLowerCase() || 'free';
  
  // Determine the correct ProfileBuilder path based on plan
  let profileBuilderPath = '';
  
  if (userPlan === 'premium') {
    // Premium users go to dedicated Premium ProfileBuilder
    profileBuilderPath = '/UserDashboard/UserManagement/PremiumPlanUser/PremiumProfileBuilder';
  } else if (userPlan === 'basic') {
    // Basic users go to dedicated Basic ProfileBuilder
    profileBuilderPath = '/UserDashboard/UserManagement/BasicPlanUser/BasicProfileBuilder';
  } else {
    // Free users go to shared ProfileBuilder
    profileBuilderPath = '/UserDashboard/ProfileBuilder';
  }

  // If card has a linked profile, edit it
  if (selectedCard.value.nfcTag?.id) {
    navigateTo(
      `${profileBuilderPath}?nfc_tag_id=${selectedCard.value.nfcTag.id}`
    );
  } else {
    // If no profile, go to Profile Builder to create new one with this card pre-selected
    navigateTo(
      `${profileBuilderPath}?nfc_card_id=${selectedCard.value.nfc_card_id}`
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
onMounted(async () => {
  // GUARD: Redirect users still in onboarding process back to plan selection
  // This ensures CardManagement is only accessible after payment completion
  if (!authStore.isAuthenticated || !authStore.user) {
    navigateTo("/UserAccount/login");
    return;
  }

  // If user is marked as new (hasn't completed onboarding), redirect to plan selection
  if (authStore.user.is_new_user === true) {
    console.log(
      "🚫 Access denied: User is still in onboarding process (is_new_user=true)"
    );
    $toast.warning("Please complete plan selection and payment first.");
    navigateTo("/UserDashboard/PlanSelection");
    return;
  }

  console.log(
    "✅ CardManagement guard passed: User has completed onboarding (is_new_user=false)"
  );

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
