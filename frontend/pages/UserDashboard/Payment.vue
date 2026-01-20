<!-- pages/onboarding/payment.vue -->
<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-50 to-secondary-100">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center">
            <NuxtLink to="/" class="inline-flex items-center">
              <Icon
                name="heroicons:identification"
                class="h-8 w-8 text-primary-600"
              />
              <span class="ml-2 text-xl font-bold text-secondary-900"
                >NFCGo</span
              >
            </NuxtLink>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-secondary-600">Step 3 of 3</span>
            <div class="flex space-x-1">
              <div class="w-2 h-2 bg-secondary-300 rounded-full"></div>
              <div class="w-2 h-2 bg-secondary-300 rounded-full"></div>
              <div class="w-2 h-2 bg-primary-600 rounded-full"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-secondary-900 mb-4">
          Complete Your Order
        </h1>
        <p class="text-xl text-secondary-600 max-w-2xl mx-auto">
          Review your order details and complete the payment to receive your NFC
          business card.
        </p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Left Panel - Order Summary -->
        <div class="space-y-6">
          <!-- Order Summary -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-2xl font-bold text-secondary-900 mb-6">
              Order Summary
            </h2>

            <div class="space-y-4">
              <!-- Plan Details -->
              <div
                class="flex justify-between items-center py-3 border-b border-secondary-200"
              >
                <div>
                  <h3 class="font-semibold text-secondary-900">
                    {{ orderSummary.planName }}
                  </h3>
                  <p class="text-sm text-secondary-600">
                    {{ orderSummary.planDescription }}
                  </p>
                </div>
                <div class="text-right">
                  <p class="font-semibold text-secondary-900">
                    ${{ orderSummary.planPrice }}/month
                  </p>
                  <p class="text-xs text-secondary-500">14-day free trial</p>
                </div>
              </div>

              <!-- NFC Card -->
              <div
                class="flex justify-between items-center py-3 border-b border-secondary-200"
              >
                <div>
                  <h3 class="font-semibold text-secondary-900">
                    Physical NFC Card
                  </h3>
                  <p class="text-sm text-secondary-600">
                    {{ orderSummary.cardType }}
                  </p>
                </div>
                <div class="text-right">
                  <p class="font-semibold text-secondary-900">
                    ${{ orderSummary.cardPrice }}
                  </p>
                  <p class="text-xs text-secondary-500">One-time</p>
                </div>
              </div>

              <!-- Shipping -->
              <div
                class="flex justify-between items-center py-3 border-b border-secondary-200"
              >
                <div>
                  <h3 class="font-semibold text-secondary-900">Shipping</h3>
                  <p class="text-sm text-secondary-600">
                    Standard delivery (5-7 business days)
                  </p>
                </div>
                <div class="text-right">
                  <p class="font-semibold text-secondary-900">
                    ${{ orderSummary.shippingCost }}
                  </p>
                </div>
              </div>

              <!-- Total -->
              <div class="flex justify-between items-center py-3">
                <h3 class="text-lg font-bold text-secondary-900">
                  Total Today
                </h3>
                <div class="text-right">
                  <p class="text-2xl font-bold text-primary-600">
                    ${{ orderSummary.total }}
                  </p>
                  <p class="text-xs text-secondary-500">
                    Then ${{ orderSummary.planPrice }}/month
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Card Preview -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-2xl font-bold text-secondary-900 mb-6">
              Your NFC Card
            </h2>

            <div class="flex justify-center">
              <div class="relative" style="width: 180px; height: 108px">
                <!-- Front Side -->
                <div
                  class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-700 rounded-lg shadow-lg"
                >
                  <div class="absolute inset-0 p-3 text-white text-xs">
                    <div class="flex items-center justify-between h-full">
                      <div>
                        <div class="font-bold">
                          {{ cardInfo.name || "Your Name" }}
                        </div>
                        <div class="opacity-80">
                          {{ cardInfo.position || "Position" }}
                        </div>
                        <div class="mt-1">
                          {{ cardInfo.contactNumber || "Phone" }}
                        </div>
                        <div>{{ cardInfo.email || "Email" }}</div>
                      </div>
                      <div
                        v-if="cardInfo.companyLogo"
                        class="w-8 h-8 bg-white rounded"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-4 text-center">
              <p class="text-sm text-secondary-600">Card Size: 90mm x 54mm</p>
              <p class="text-xs text-secondary-500">
                Standard business card dimensions
              </p>
            </div>
          </div>
        </div>

        <!-- Right Panel - Payment Form -->
        <div class="space-y-6">
          <!-- Payment Method Selection -->
          <div v-if="!selectedPaymentRail" class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-2xl font-bold text-secondary-900 mb-6">
              Choose Payment Method
            </h2>
            <PaymentMethodSelector
              :amount="orderSummary.total"
              currency="MYR"
              @rail-selected="handleRailSelected"
            />
          </div>

          <!-- Card Payment Form (Fiuu) -->
          <div v-else-if="selectedPaymentRail === 'card'" class="bg-white rounded-2xl shadow-lg p-6">
            <FiuuPaymentForm
              :amount="orderSummary.total"
              :fee="paymentFee"
              payment-rail="card"
              :description="`${orderSummary.planName} + NFC Card`"
              :metadata="orderMetadata"
              @payment-success="handlePaymentSuccess"
              @payment-failed="handlePaymentFailed"
              @back="selectedPaymentRail = null"
            />
          </div>

          <!-- FPX Bank Payment Form (Fiuu) -->
          <div v-else-if="selectedPaymentRail === 'fpx'" class="bg-white rounded-2xl shadow-lg p-6">
            <FiuuPaymentForm
              :amount="orderSummary.total"
              :fee="paymentFee"
              payment-rail="fpx"
              :description="`${orderSummary.planName} + NFC Card`"
              :metadata="orderMetadata"
              @payment-success="handlePaymentSuccess"
              @payment-failed="handlePaymentFailed"
              @back="selectedPaymentRail = null"
            />
          </div>

          <!-- E-Wallet Payment Form (Fiuu) -->
          <div v-else-if="selectedPaymentRail === 'ewallet'" class="bg-white rounded-2xl shadow-lg p-6">
            <FiuuPaymentForm
              :amount="orderSummary.total"
              :fee="paymentFee"
              payment-rail="ewallet"
              :description="`${orderSummary.planName} + NFC Card`"
              :metadata="orderMetadata"
              @payment-success="handlePaymentSuccess"
              @payment-failed="handlePaymentFailed"
              @back="selectedPaymentRail = null"
            />
          </div>

          <!-- Manual Bank Transfer Form -->
          <div v-else-if="selectedPaymentRail === 'manual_bank_transfer'" class="bg-white rounded-2xl shadow-lg p-6">
            <ManualBankTransfer
              :amount="orderSummary.total"
              :description="`${orderSummary.planName} + NFC Card`"
              :metadata="orderMetadata"
              @payment-initiated="handleManualTransferInitiated"
              @proof-uploaded="handleProofUploaded"
              @back="selectedPaymentRail = null"
            />
          </div>

          <!-- Billing Address (shown for all payment methods) -->
          <div v-if="selectedPaymentRail" class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-secondary-800 mb-4">
              Billing Address
            </h3>

            <div class="space-y-4">
              <div>
                <label
                  class="block text-sm font-medium text-secondary-700 mb-1"
                  >Full Name</label
                >
                <input
                  v-model="billingAddress.fullName"
                  type="text"
                  class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                  placeholder="John Doe"
                />
              </div>

              <div>
                <label
                  class="block text-sm font-medium text-secondary-700 mb-1"
                  >Address</label
                >
                <textarea
                  v-model="billingAddress.address"
                  rows="3"
                  class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                  placeholder="123 Main Street, City, State, ZIP"
                ></textarea>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-1"
                    >City</label
                  >
                  <input
                    v-model="billingAddress.city"
                    type="text"
                    class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder="City"
                  />
                </div>
                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-1"
                    >ZIP Code</label
                  >
                  <input
                    v-model="billingAddress.zipCode"
                    type="text"
                    class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder="12345"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Terms and Conditions -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="space-y-4">
              <div class="flex items-start">
                <input
                  id="terms"
                  v-model="termsAccepted"
                  type="checkbox"
                  required
                  class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-secondary-300 rounded mt-1"
                />
                <label
                  for="terms"
                  class="ml-2 block text-sm text-secondary-700"
                >
                  I agree to the
                  <button
                    type="button"
                    @click="showTermsModal = true"
                    class="text-primary-600 hover:text-primary-500 underline"
                  >
                    Terms of Service
                  </button>
                  and
                  <button
                    type="button"
                    @click="showPrivacyModal = true"
                    class="text-primary-600 hover:text-primary-500 underline"
                  >
                    Privacy Policy
                  </button>
                </label>
              </div>

              <div class="flex items-start">
                <input
                  id="billing"
                  v-model="billingConsent"
                  type="checkbox"
                  required
                  class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-secondary-300 rounded mt-1"
                />
                <label
                  for="billing"
                  class="ml-2 block text-sm text-secondary-700"
                >
                  I authorize NFCGo to charge my payment method for the
                  subscription amount
                </label>
              </div>

              <div class="flex items-start">
                <input
                  id="marketing"
                  v-model="marketingConsent"
                  type="checkbox"
                  class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-secondary-300 rounded mt-1"
                />
                <label
                  for="marketing"
                  class="ml-2 block text-sm text-secondary-700"
                >
                  I would like to receive marketing emails about new features
                  and updates
                </label>
              </div>
            </div>
          </div>

          <!-- Security Notice -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="text-center">
              <div
                class="flex items-center justify-center space-x-2 text-sm text-secondary-600"
              >
                <Icon name="heroicons:shield-check" class="h-4 w-4" />
                <span>Your payment is secured with SSL encryption</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <div class="flex justify-between items-center mt-12">
        <button
          @click="goBack"
          class="px-6 py-3 border border-secondary-300 text-secondary-700 rounded-xl font-medium hover:bg-secondary-50 transition-colors"
        >
          <Icon name="heroicons:arrow-left" class="h-5 w-5 inline mr-2" />
          Back
        </button>
      </div>
    </div>

    <!-- Terms of Service Modal -->
    <div
      v-if="showTermsModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
      @click.self="showTermsModal = false"
    >
      <div
        class="bg-white rounded-2xl max-w-4xl w-full max-h-[85vh] overflow-hidden flex flex-col"
      >
        <div class="p-6 border-b border-secondary-200">
          <div class="flex items-center justify-between">
            <h3 class="text-2xl font-bold text-secondary-900">
              Terms of Service
            </h3>
            <button
              @click="showTermsModal = false"
              class="text-secondary-400 hover:text-secondary-600"
            >
              <Icon name="heroicons:x-mark" class="h-6 w-6" />
            </button>
          </div>
          <p v-if="termsDocument" class="text-sm text-secondary-500 mt-2">
            Version {{ termsDocument.version }} • Effective
            {{ formatDate(termsDocument.effective_date) }}
          </p>
        </div>
        <div class="flex-1 p-6 overflow-hidden">
          <PdfViewer
            url="http://localhost:8000/api/legal/pdf/terms/view"
            :require-auth="false"
            :disable-download="true"
            min-height="calc(85vh - 200px)"
          />
        </div>
        <div class="p-6 border-t border-secondary-200">
          <button
            @click="showTermsModal = false"
            class="btn btn-primary w-full flex items-center justify-center"
          >
            <Icon name="heroicons:x-mark" class="h-5 w-5 mr-2" />
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- Privacy Policy Modal -->
    <div
      v-if="showPrivacyModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
      @click.self="showPrivacyModal = false"
    >
      <div
        class="bg-white rounded-2xl max-w-4xl w-full max-h-[85vh] overflow-hidden flex flex-col"
      >
        <div class="p-6 border-b border-secondary-200">
          <div class="flex items-center justify-between">
            <h3 class="text-2xl font-bold text-secondary-900">
              Privacy Policy
            </h3>
            <button
              @click="showPrivacyModal = false"
              class="text-secondary-400 hover:text-secondary-600"
            >
              <Icon name="heroicons:x-mark" class="h-6 w-6" />
            </button>
          </div>
          <p v-if="privacyDocument" class="text-sm text-secondary-500 mt-2">
            Version {{ privacyDocument.version }} • Effective
            {{ formatDate(privacyDocument.effective_date) }}
          </p>
        </div>
        <div class="flex-1 p-6 overflow-hidden">
          <PdfViewer
            url="http://localhost:8000/api/legal/pdf/privacy/view"
            :require-auth="false"
            :disable-download="true"
            min-height="calc(85vh - 200px)"
          />
        </div>
        <div class="p-6 border-t border-secondary-200">
          <button
            @click="showPrivacyModal = false"
            class="btn btn-primary w-full flex items-center justify-center"
          >
            <Icon name="heroicons:x-mark" class="h-5 w-5 mr-2" />
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <div
      v-if="showSuccessModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
    >
      <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 text-center">
        <div
          class="w-16 h-16 bg-success-100 rounded-full flex items-center justify-center mx-auto mb-4"
        >
          <Icon name="heroicons:check" class="h-8 w-8 text-success-600" />
        </div>
        <h3 class="text-2xl font-bold text-secondary-900 mb-2">
          Payment Successful!
        </h3>
        <p class="text-secondary-600 mb-6">
          Your order has been confirmed. We'll start processing your NFC card
          and ship it to you within 5-7 business days.
        </p>
        <button @click="goToUserDashboard" class="btn btn-primary w-full">
          <Icon name="heroicons:arrow-right" class="h-5 w-5 mr-2" />
          Go to UserDashboard
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
// Import payment components
import PaymentMethodSelector from '~/components/PaymentMethodSelector.vue';
import FiuuPaymentForm from '~/components/FiuuPaymentForm.vue';
import ManualBankTransfer from '~/components/ManualBankTransfer.vue';

// Meta tags
useHead({
  title: "Payment - NFCGo",
  meta: [
    {
      name: "description",
      content: "Complete your NFC business card order with secure payment.",
    },
  ],
});

// Stores
const authStore = useAuthStore();
const { $toast } = useNuxtApp();

// Reactive data
const processing = ref(false);
const showSuccessModal = ref(false);
const showTermsModal = ref(false);
const showPrivacyModal = ref(false);
const loadingTerms = ref(false);
const loadingPrivacy = ref(false);
const termsDocument = ref(null);
const privacyDocument = ref(null);
const termsAccepted = ref(false);
const billingConsent = ref(false);
const marketingConsent = ref(false);

// Payment rail selection
const selectedPaymentRail = ref(null);
const paymentFee = ref(0);
const feeCalculation = ref(null);

// Payment form (legacy - kept for compatibility)
const paymentMethod = ref("stripe");
const paymentForm = reactive({
  cardNumber: "",
  expiryDate: "",
  cvv: "",
  cardholderName: "",
});

// Billing address
const billingAddress = reactive({
  fullName: "",
  address: "",
  city: "",
  zipCode: "",
});

// Plan prices from API
const { getPlanPrice, getPlanDetails, loadPrices } = usePlanPrices();

// Order summary
const orderSummary = computed(() => {
  const selectedPlan = sessionStorage.getItem("selectedPlan");
  const planNames = {
    basic: "Basic Plan",
    premium: "Premium Plan",
    business: "Business Plan",
  };
  const planDescriptions = {
    basic: "Great for small businesses",
    premium: "For growing businesses",
    business: "For large organizations",
  };
  const cardTypes = {
    basic: "Basic NFC Card",
    premium: "Premium NFC Card",
    business: "Business NFC Card",
  };

  const planPrice = getPlanPrice(selectedPlan) || 0;
  const cardPrice = 15; // Fixed card price
  const shippingCost = 5; // Fixed shipping cost
  const total = planPrice + cardPrice + shippingCost;

  return {
    planName: planNames[selectedPlan] || "Free Plan",
    planDescription:
      planDescriptions[selectedPlan] || "Perfect for getting started",
    planPrice,
    cardType: cardTypes[selectedPlan] || "Digital Only",
    cardPrice,
    shippingCost,
    total,
  };
});

// Order metadata for payment components
const orderMetadata = computed(() => ({
  plan: sessionStorage.getItem("selectedPlan"),
  card_type: orderSummary.value.cardType,
  shipping_cost: orderSummary.value.shippingCost,
  card_info: cardInfo.value,
}));

// Card info from previous step
const cardInfo = computed(() => {
  const stored = sessionStorage.getItem("cardInfo");
  return stored ? JSON.parse(stored) : {};
});

// Form validation
const isFormValid = computed(() => {
  if (!selectedPaymentRail.value) {
    return false;
  }
  
  const billingValid =
    billingAddress.fullName &&
    billingAddress.address &&
    billingAddress.city &&
    billingAddress.zipCode;
    
  return billingValid && termsAccepted.value && billingConsent.value;
});

// Handle payment rail selection
const handleRailSelected = ({ rail, feeCalculation: fee }) => {
  console.log('Rail selected:', rail, 'Fee calculation:', fee);
  selectedPaymentRail.value = rail;
  feeCalculation.value = fee;
  
  // Handle different fee calculation structures
  if (fee?.fees?.total_fee !== undefined) {
    paymentFee.value = fee.fees.total_fee;
  } else if (fee?.fee !== undefined) {
    paymentFee.value = fee.fee;
  } else {
    paymentFee.value = 0;
    console.warn('Fee calculation missing total_fee:', fee);
  }
  
  $toast.success(`Selected ${rail.toUpperCase()} payment method`);
};

// Handle payment success
const handlePaymentSuccess = async (data) => {
  console.log('Payment successful:', data);
  
  // Store order details
  sessionStorage.setItem("orderCompleted", "true");
  sessionStorage.setItem("onboarding_completed", "true");
  sessionStorage.setItem(
    "orderDetails",
    JSON.stringify({
      plan: sessionStorage.getItem("selectedPlan"),
      cardInfo: cardInfo.value,
      paymentMethod: selectedPaymentRail.value,
      total: orderSummary.value.total,
      transactionId: data.transaction_id,
      orderDate: new Date().toISOString(),
      orderId: data.order_id || `ORD-${Date.now()}`,
      estimatedDelivery: "5-7 business days",
    })
  );
  
  // BUGFIX: Auto-complete onboarding immediately after payment success
  // This ensures is_new_user is set to false in the auth store and user can access dashboard
  // even if they close the browser before clicking "Go to Dashboard"
  try {
    const { $api } = useNuxtApp();
    await $api.post("/complete-onboarding");
    console.log("✅ Onboarding auto-completed after payment");
    if (authStore.user) {
      authStore.user.is_new_user = false;
    }
  } catch (error) {
    console.warn("Failed to auto-complete onboarding (will complete via webhook):", error);
    // Continue anyway - the webhook will mark user as not new
  }
  
  // Show success modal
  showSuccessModal.value = true;
  $toast.success("Payment successful!");
};

// Handle payment failure
const handlePaymentFailed = (error) => {
  console.error('Payment failed:', error);
  $toast.error(error.message || "Payment failed. Please try again.");
  
  // Reset payment rail selection to allow user to try again
  selectedPaymentRail.value = null;
};

// Handle manual transfer initiated
const handleManualTransferInitiated = (data) => {
  console.log('Manual transfer initiated:', data);
  $toast.success("Transfer reference created. Please complete the bank transfer and upload proof.");
};

// Handle proof uploaded
const handleProofUploaded = (data) => {
  console.log('Payment proof uploaded:', data);
  
  // Store pending order details
  sessionStorage.setItem("orderPending", "true");
  sessionStorage.setItem(
    "pendingOrderDetails",
    JSON.stringify({
      plan: sessionStorage.getItem("selectedPlan"),
      cardInfo: cardInfo.value,
      paymentMethod: 'manual_bank_transfer',
      total: orderSummary.value.total,
      transactionId: data.transaction_id,
      orderDate: new Date().toISOString(),
      orderId: `ORD-${Date.now()}`,
      status: 'pending_verification',
    })
  );
  
  $toast.success("Payment proof uploaded. We'll verify it within 24 hours.");
  
  // Redirect to dashboard after short delay
  setTimeout(() => {
    navigateTo("/UserDashboard");
  }, 2000);
};

// Check if user is authenticated
onMounted(async () => {
  console.log('Payment page mounted - authStore:', authStore.isAuthenticated);
  console.log('Selected payment rail:', selectedPaymentRail.value);
  console.log('Order summary total:', orderSummary.value.total);
  
  if (!authStore.isAuthenticated) {
    await navigateTo("/UserAccount/login");
    return;
  }

  // Load plan prices from API
  await loadPrices();

  // Load user data for billing address
  if (authStore.user) {
    billingAddress.fullName = authStore.user.full_name || "";
  }

  // Load legal documents (non-blocking)
  loadLegalDocuments().catch(err => {
    console.log('Legal documents failed to load (non-critical):', err.message);
  });
});

// Load legal documents
const loadLegalDocuments = async () => {
  try {
    const { $api } = useNuxtApp();

    // Load Terms of Service and Privacy Policy in parallel
    const [termsResponse, privacyResponse] = await Promise.all([
      $api.get("/legal/terms"),
      $api.get("/legal/privacy"),
    ]);

    if (termsResponse.success && termsResponse.data) {
      termsDocument.value = termsResponse.data;
    }

    if (privacyResponse.success && privacyResponse.data) {
      privacyDocument.value = privacyResponse.data;
    }
  } catch (error) {
    console.error("Error loading legal documents:", error);
  }
};

// Render Markdown to HTML (lightweight parser)
const renderMarkdown = (markdown) => {
  if (!markdown) return "";

  let html = markdown
    // Headers
    .replace(/^### (.*$)/gim, "<h3>$1</h3>")
    .replace(/^## (.*$)/gim, "<h2>$1</h2>")
    .replace(/^# (.*$)/gim, "<h1>$1</h1>")
    // Bold
    .replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>")
    // Italic
    .replace(/\*(.*?)\*/g, "<em>$1</em>")
    // Links
    .replace(
      /\[([^\]]+)\]\(([^)]+)\)/g,
      '<a href="$2" target="_blank" class="text-primary-600 hover:text-primary-500 underline">$1</a>'
    )
    // Line breaks
    .replace(/\n\n/g, "</p><p>")
    // Lists
    .replace(/^\- (.*$)/gim, "<li>$1</li>")
    .replace(/(<li>.*<\/li>)/s, "<ul>$1</ul>");

  return `<div>${html}</div>`;
};

// Format date
const formatDate = (date) => {
  if (!date) return "";
  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

// Watch for modal open to load documents if not already loaded
watch(showTermsModal, (isOpen) => {
  if (isOpen && !termsDocument.value && !loadingTerms.value) {
    loadingTerms.value = true;
    loadLegalDocuments().finally(() => {
      loadingTerms.value = false;
    });
  }
});

watch(showPrivacyModal, (isOpen) => {
  if (isOpen && !privacyDocument.value && !loadingPrivacy.value) {
    loadingPrivacy.value = true;
    loadLegalDocuments().finally(() => {
      loadingPrivacy.value = false;
    });
  }
});

// Go back to card customization
const goBack = () => {
  // If a payment rail is selected, go back to rail selection
  if (selectedPaymentRail.value) {
    selectedPaymentRail.value = null;
    return;
  }
  
  // Otherwise go back to previous page
  navigateTo("/UserDashboard/UserManagement/NFCCardDesignCustomization");
};

// Go to UserDashboard
const goToUserDashboard = async () => {
  showSuccessModal.value = false;

  // Set flag to indicate payment was just completed
  sessionStorage.setItem("just_completed_payment", "true");

  // Complete onboarding on backend and update auth store gating flag
  try {
    const { $api } = useNuxtApp();
    await $api.post("/complete-onboarding");
    if (authStore.user) {
      authStore.user.is_new_user = false;
    }
  } catch (error) {
    console.error("Failed to complete onboarding after payment:", error);
  }

  $toast.success("Onboarding complete! Redirecting to your dashboard.");
  await navigateTo("/UserDashboard");
};
</script>
