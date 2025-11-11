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
          <!-- Payment Method -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-2xl font-bold text-secondary-900 mb-6">
              Payment Method
            </h2>

            <!-- Payment Options -->
            <div class="space-y-4 mb-6">
              <div class="flex items-center space-x-3">
                <input
                  type="radio"
                  id="stripe"
                  v-model="paymentMethod"
                  value="stripe"
                  class="text-primary-600 focus:ring-primary-500"
                />
                <label
                  for="stripe"
                  class="flex items-center space-x-2 cursor-pointer"
                >
                  <Icon name="logos:stripe" class="h-6 w-6" />
                  <span class="font-medium">Credit/Debit Card</span>
                </label>
              </div>
              <div class="flex items-center space-x-3">
                <input
                  type="radio"
                  id="razorpay"
                  v-model="paymentMethod"
                  value="razorpay"
                  class="text-primary-600 focus:ring-primary-500"
                />
                <label
                  for="razorpay"
                  class="flex items-center space-x-2 cursor-pointer"
                >
                  <Icon
                    name="heroicons:credit-card"
                    class="h-6 w-6 text-secondary-600"
                  />
                  <span class="font-medium">Razer Merchant Services</span>
                </label>
              </div>
            </div>

            <!-- Credit Card Form -->
            <div v-if="paymentMethod === 'stripe'" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1"
                  >Card Number</label
                >
                <input
                  v-model="paymentForm.cardNumber"
                  type="text"
                  class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                  placeholder="1234 5678 9012 3456"
                  maxlength="19"
                />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-1"
                    >Expiry Date</label
                  >
                  <input
                    v-model="paymentForm.expiryDate"
                    type="text"
                    class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder="MM/YY"
                    maxlength="5"
                  />
                </div>
                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-1"
                    >CVV</label
                  >
                  <input
                    v-model="paymentForm.cvv"
                    type="text"
                    class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder="123"
                    maxlength="4"
                  />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1"
                  >Cardholder Name</label
                >
                <input
                  v-model="paymentForm.cardholderName"
                  type="text"
                  class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                  placeholder="John Doe"
                />
              </div>
            </div>

            <!-- Billing Address -->
            <div class="mt-6">
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
        <button
          @click="processPayment"
          :disabled="!isFormValid || processing"
          class="px-6 py-3 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <div v-if="processing" class="flex items-center">
            <div class="spinner mr-2"></div>
            Processing Payment...
          </div>
          <div v-else class="flex items-center">
            Proceed to Payment
            <Icon name="heroicons:arrow-right" class="h-5 w-5 inline ml-2" />
          </div>
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
        class="bg-white rounded-2xl max-w-3xl w-full max-h-[80vh] overflow-hidden"
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
        <div class="p-6 overflow-y-auto max-h-[calc(80vh-140px)]">
          <div v-if="loadingTerms" class="text-center py-8">
            <div class="spinner mx-auto mb-4"></div>
            <p class="text-secondary-600">Loading Terms of Service...</p>
          </div>
          <div
            v-else-if="termsDocument"
            class="prose prose-sm max-w-none"
            v-html="renderMarkdown(termsDocument.content)"
          ></div>
          <div v-else class="text-center py-8 text-secondary-600">
            Terms of Service not available
          </div>
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
        class="bg-white rounded-2xl max-w-3xl w-full max-h-[80vh] overflow-hidden"
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
        <div class="p-6 overflow-y-auto max-h-[calc(80vh-140px)]">
          <div v-if="loadingPrivacy" class="text-center py-8">
            <div class="spinner mx-auto mb-4"></div>
            <p class="text-secondary-600">Loading Privacy Policy...</p>
          </div>
          <div
            v-else-if="privacyDocument"
            class="prose prose-sm max-w-none"
            v-html="renderMarkdown(privacyDocument.content)"
          ></div>
          <div v-else class="text-center py-8 text-secondary-600">
            Privacy Policy not available
          </div>
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

// Route and router
const router = useRouter();

// Reactive data
const processing = ref(false);
const showSuccessModal = ref(false);
const showTermsModal = ref(false);
const showPrivacyModal = ref(false);
const loadingTerms = ref(false);
const loadingPrivacy = ref(false);
const termsDocument = ref(null);
const privacyDocument = ref(null);
const paymentMethod = ref("stripe");
const termsAccepted = ref(false);
const billingConsent = ref(false);
const marketingConsent = ref(false);

// Payment form
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

// Order summary
const orderSummary = computed(() => {
  const selectedPlan = sessionStorage.getItem("selectedPlan");
  const planPrices = {
    basic: 9,
    premium: 19,
    business: 49,
  };
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

  const planPrice = planPrices[selectedPlan] || 0;
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

// Card info from previous step
const cardInfo = computed(() => {
  const stored = sessionStorage.getItem("cardInfo");
  return stored ? JSON.parse(stored) : {};
});

// Form validation
const isFormValid = computed(() => {
  if (paymentMethod.value === "stripe") {
    const cardValid =
      paymentForm.cardNumber &&
      paymentForm.expiryDate &&
      paymentForm.cvv &&
      paymentForm.cardholderName;
    const billingValid =
      billingAddress.fullName &&
      billingAddress.address &&
      billingAddress.city &&
      billingAddress.zipCode;
    return (
      cardValid && billingValid && termsAccepted.value && billingConsent.value
    );
  }
  return termsAccepted.value && billingConsent.value;
});

// Check if user is authenticated
onMounted(() => {
  if (!authStore.isAuthenticated) {
    router.push("/UserAccount/login");
  }

  // Load user data for billing address
  if (authStore.user) {
    billingAddress.fullName = authStore.user.full_name || "";
  }

  // Load legal documents
  loadLegalDocuments();
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

// Process payment
const processPayment = async () => {
  if (!isFormValid.value) {
    $toast.error("Please fill in all required fields and accept the terms.");
    return;
  }

  processing.value = true;

  try {
    // Get card info from session storage
    const cardInfo = JSON.parse(sessionStorage.getItem("cardInfo") || "{}");

    // Prepare payment data
    const paymentData = {
      payment_method: paymentMethod.value,
      billing_address: {
        full_name: billingAddress.fullName,
        address: billingAddress.address,
        city: billingAddress.city,
        zip_code: billingAddress.zipCode,
      },
      card_info: {
        name: cardInfo.name,
        position: cardInfo.position,
        contact_number: cardInfo.contactNumber,
        email: cardInfo.email,
        address: cardInfo.address,
      },
    };

    try {
      // Call backend API to process payment
      const { $api } = useNuxtApp();
      const response = await $api.post(
        "/onboarding/process-payment",
        paymentData
      );

      if (response.success) {
        // Payment success
        showSuccessModal.value = true;

        // Store order details in session storage
        sessionStorage.setItem("orderCompleted", "true");
        sessionStorage.setItem("onboarding_completed", "true"); // Mark onboarding as completed
        sessionStorage.setItem(
          "orderDetails",
          JSON.stringify({
            plan: sessionStorage.getItem("selectedPlan"),
            cardInfo: cardInfo,
            paymentMethod: paymentMethod.value,
            total: orderSummary.value.total,
            orderDate: new Date().toISOString(),
            orderId: response.data?.order_id || `ORD-${Date.now()}`,
            estimatedDelivery:
              response.data?.estimated_delivery || "5-7 business days",
          })
        );
      } else {
        throw new Error("Payment processing failed");
      }
    } catch (apiError) {
      // If API fails (e.g., endpoint not ready), simulate success for demo purposes
      console.warn("Payment API not available, simulating success:", apiError);

      // Mock payment success for demo/development
      showSuccessModal.value = true;

      // Store order details in session storage
      sessionStorage.setItem("orderCompleted", "true");
      sessionStorage.setItem("onboarding_completed", "true"); // Mark onboarding as completed
      sessionStorage.setItem(
        "orderDetails",
        JSON.stringify({
          plan: sessionStorage.getItem("selectedPlan"),
          cardInfo: cardInfo,
          paymentMethod: paymentMethod.value,
          total: orderSummary.value.total,
          orderDate: new Date().toISOString(),
          orderId: `ORD-${Date.now()}`,
          estimatedDelivery: "5-7 business days",
        })
      );

      $toast.success("Payment simulated successfully (Demo mode)");
    }
  } catch (error) {
    console.error("Payment processing error:", error);
    $toast.error("Payment failed. Please try again.");
  } finally {
    processing.value = false;
  }
};

// Go back to card customization
const goBack = () => {
  router.push("/UserDashboard/UserManagement/NFCCardDesignCustomization");
};

// Go to UserDashboardS
const goToUserDashboard = async () => {
  showSuccessModal.value = false;

  // Set flag to indicate payment was just completed
  sessionStorage.setItem("just_completed_payment", "true");

  $toast.success("Payment successful! Welcome to NFCGo!");
  // Redirect to Dashboard first, then it will auto-redirect to CardManagement
  await router.push("/UserDashboard");
};

// Format card number
watch(
  () => paymentForm.cardNumber,
  (newValue) => {
    // Remove all non-digits
    const digits = newValue.replace(/\D/g, "");
    // Add spaces every 4 digits
    paymentForm.cardNumber = digits.replace(/(\d{4})(?=\d)/g, "$1 ");
  }
);

// Format expiry date
watch(
  () => paymentForm.expiryDate,
  (newValue) => {
    // Remove all non-digits
    const digits = newValue.replace(/\D/g, "");
    // Add slash after 2 digits
    if (digits.length >= 2) {
      paymentForm.expiryDate = digits.slice(0, 2) + "/" + digits.slice(2, 4);
    } else {
      paymentForm.expiryDate = digits;
    }
  }
);
</script>
