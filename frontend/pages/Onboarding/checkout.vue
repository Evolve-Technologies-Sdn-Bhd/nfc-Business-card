<!--
  pages/Onboarding/checkout.vue
  The single "Checkout" step of the onboarding funnel
  (replaces pages/UserDashboard/Payment.vue).

  Chrome (top bar + step indicator + "Exit setup") comes from
  layouts/onboarding.vue, so this file only renders the step body: a paper
  panel on the dark "Ink" layout. State (picked plan + card draft) comes from
  useOnboardingFlow — never read sessionStorage directly here.

  This page is NOT a completion point: only /Onboarding/complete calls
  POST /complete-onboarding.
-->
<template>
  <div class="relative">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div
        class="bg-white rounded-2xl shadow-xl ring-1 ring-black/5 p-6 sm:p-10 text-secondary-900"
      >
        <!-- Page Header -->
        <div class="text-center mb-12">
          <h1 class="page-title text-secondary-900 mb-4">
            Complete Your Order
          </h1>
          <p class="card-subtitle max-w-2xl mx-auto">
            Review your order details and complete the payment to receive your
            NFC business card.
          </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Left Panel - Order Summary -->
          <div class="space-y-6">
            <!-- Order Summary -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
              <h2 class="section-title text-secondary-900 mb-6">
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
                    <p class="card-subtitle">
                      {{ orderSummary.planDescription }}
                    </p>
                  </div>
                  <div class="text-right">
                    <p class="font-semibold text-secondary-900">
                      {{ formatMoney(orderSummary.planPrice) }}/month
                    </p>
                    <p class="text-helper">Billed monthly</p>
                  </div>
                </div>

                <!-- NFC Card -->
                <div class="py-3 border-b border-secondary-200">
                  <div class="flex justify-between items-center">
                    <div>
                      <h3 class="font-semibold text-secondary-900">
                        Physical NFC Card
                      </h3>
                      <p class="card-subtitle">
                        {{ orderSummary.cardType }}
                      </p>
                    </div>
                    <div class="text-right">
                      <p class="font-semibold text-secondary-900">
                        {{ formatMoney(orderSummary.cardPrice) }}
                      </p>
                      <p class="text-helper">One-time</p>
                    </div>
                  </div>
                  <div class="flex items-center justify-between mt-3">
                    <span class="text-body text-secondary-600">Quantity</span>
                    <div class="flex items-center gap-2">
                      <button
                        type="button"
                        @click="decreaseQuantity"
                        :disabled="cardQuantity <= 1"
                        class="w-8 h-8 rounded-lg border border-secondary-300 text-secondary-700 hover:bg-secondary-50 disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center"
                        aria-label="Decrease card quantity"
                      >
                        <Icon name="heroicons:minus" class="h-4 w-4" />
                      </button>
                      <span
                        class="w-8 text-center font-medium text-secondary-900"
                        >{{ cardQuantity }}</span
                      >
                      <button
                        type="button"
                        @click="increaseQuantity"
                        :disabled="cardQuantity >= maxQuantity"
                        class="w-8 h-8 rounded-lg border border-secondary-300 text-secondary-700 hover:bg-secondary-50 disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center"
                        aria-label="Increase card quantity"
                      >
                        <Icon name="heroicons:plus" class="h-4 w-4" />
                      </button>
                    </div>
                  </div>
                  <p v-if="cardQuantity > 1" class="text-helper mt-2">
                    {{ formatMoney(orderSummary.cardUnitPrice) }} ×
                    {{ cardQuantity }} cards
                  </p>
                </div>

                <!-- Shipping -->
                <div
                  class="flex justify-between items-center py-3 border-b border-secondary-200"
                >
                  <div>
                    <h3 class="font-semibold text-secondary-900">Shipping</h3>
                    <p class="card-subtitle">
                      Standard delivery (5-7 business days)
                    </p>
                  </div>
                  <div class="text-right">
                    <p class="font-semibold text-secondary-900">
                      {{ formatMoney(orderSummary.shippingCost) }}
                    </p>
                  </div>
                </div>

                <!-- Total -->
                <div class="flex justify-between items-center py-3">
                  <h3 class="card-title text-secondary-900">Total Today</h3>
                  <div class="text-right">
                    <p class="stat-value text-primary-600">
                      {{ formatMoney(orderSummary.total) }}
                    </p>
                    <p class="text-helper">
                      Then {{ formatMoney(orderSummary.planPrice) }}/month
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Card Preview -->
            <CardExamples
              view="both"
              title="Your NFC Card"
              :card-info="cardInfo"
              :selected-template="selectedTemplateData"
              :design-method="draft.designMethod || 'template'"
              :custom-design="draft.cardDesign || {}"
            />
          </div>

          <!-- Right Panel - Payment Form -->
          <div class="space-y-6">
            <!-- Shipping Address (shown once a payment method is chosen) -->
            <div
              v-if="selectedPaymentRail"
              class="bg-white rounded-2xl shadow-lg p-6"
            >
              <h3 class="card-title text-secondary-800">Shipping Address</h3>
              <p class="card-subtitle mb-4">
                We deliver your physical NFC card(s) to this address.
              </p>

              <!-- Guest checkout: this email becomes (or claims) the account
                   and is where the order confirmation + verification link go. -->
              <div v-if="isGuest" class="mb-4">
                <label class="block text-label text-secondary-700 mb-1"
                  >Email Address *</label
                >
                <input
                  v-model="billingAddress.email"
                  type="email"
                  autocomplete="email"
                  class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                  placeholder="your@email.com"
                />
                <p class="text-helper mt-1">
                  We'll create your account with this email and send your order
                  updates there.
                </p>
              </div>

              <!-- The email already belongs to a real account -->
              <div
                v-if="guestAccountError"
                class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-body text-red-700"
              >
                {{ guestAccountError }}
                <NuxtLink
                  to="/UserAccount/login"
                  class="ml-1 font-semibold underline"
                >
                  Log in
                </NuxtLink>
              </div>

              <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-label text-secondary-700 mb-1"
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
                    <label class="block text-label text-secondary-700 mb-1"
                      >Phone Number</label
                    >
                    <input
                      v-model="billingAddress.phone"
                      type="tel"
                      class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                      placeholder="0123456789"
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-label text-secondary-700 mb-1"
                    >Address</label
                  >
                  <textarea
                    v-model="billingAddress.address"
                    rows="3"
                    class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder="123 Main Street, Unit 4-2"
                  ></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-label text-secondary-700 mb-1"
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
                    <label class="block text-label text-secondary-700 mb-1"
                      >State</label
                    >
                    <input
                      v-model="billingAddress.state"
                      type="text"
                      class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                      placeholder="Selangor"
                    />
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-label text-secondary-700 mb-1"
                      >ZIP / Postcode</label
                    >
                    <input
                      v-model="billingAddress.zipCode"
                      type="text"
                      class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                      placeholder="47300"
                    />
                  </div>
                  <div>
                    <label class="block text-label text-secondary-700 mb-1"
                      >Country</label
                    >
                    <input
                      v-model="billingAddress.country"
                      type="text"
                      class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                      placeholder="Malaysia"
                    />
                  </div>
                </div>

                <p
                  v-if="!isAddressValid"
                  class="text-error text-amber-600 flex items-center gap-1"
                >
                  <Icon name="heroicons:exclamation-triangle" class="h-4 w-4" />
                  Please complete your shipping address before paying.
                </p>
              </div>
            </div>

            <!-- Payment Method Selection -->
            <div
              v-if="!selectedPaymentRail"
              class="bg-white rounded-2xl shadow-lg p-6"
            >
              <h2 class="section-title text-secondary-900 mb-6">
                Choose Payment Method
              </h2>
              <PaymentMethodSelector
                :amount="orderSummary.total"
                currency="MYR"
                @rail-selected="handleRailSelected"
              />
            </div>

            <!-- Card Payment Form (Fiuu) -->
            <div
              v-else-if="selectedPaymentRail === 'card'"
              class="bg-white rounded-2xl shadow-lg p-6"
            >
              <FiuuPaymentForm
                :amount="orderSummary.total"
                :fee="paymentFee"
                payment-rail="card"
                :description="`${orderSummary.planName} + NFC Card`"
                :metadata="orderMetadata"
                :before-pay="beforePay"
                :disabled="!isFormValid"
                @payment-success="handlePaymentSuccess"
                @payment-failed="handlePaymentFailed"
                @back="selectedPaymentRail = null"
              />
            </div>

            <!-- FPX Bank Payment Form (Fiuu) -->
            <div
              v-else-if="selectedPaymentRail === 'fpx'"
              class="bg-white rounded-2xl shadow-lg p-6"
            >
              <FiuuPaymentForm
                :amount="orderSummary.total"
                :fee="paymentFee"
                payment-rail="fpx"
                :description="`${orderSummary.planName} + NFC Card`"
                :metadata="orderMetadata"
                :before-pay="beforePay"
                :disabled="!isFormValid"
                @payment-success="handlePaymentSuccess"
                @payment-failed="handlePaymentFailed"
                @back="selectedPaymentRail = null"
              />
            </div>

            <!-- E-Wallet Payment Form (Fiuu) -->
            <div
              v-else-if="selectedPaymentRail === 'ewallet'"
              class="bg-white rounded-2xl shadow-lg p-6"
            >
              <FiuuPaymentForm
                :amount="orderSummary.total"
                :fee="paymentFee"
                payment-rail="ewallet"
                :description="`${orderSummary.planName} + NFC Card`"
                :metadata="orderMetadata"
                :before-pay="beforePay"
                :disabled="!isFormValid"
                @payment-success="handlePaymentSuccess"
                @payment-failed="handlePaymentFailed"
                @back="selectedPaymentRail = null"
              />
            </div>

            <!-- Manual Bank Transfer Form -->
            <div
              v-else-if="selectedPaymentRail === 'manual_bank_transfer'"
              class="bg-white rounded-2xl shadow-lg p-6"
            >
              <ManualBankTransfer
                :amount="orderSummary.total"
                :description="`${orderSummary.planName} + NFC Card`"
                :metadata="orderMetadata"
                :before-pay="beforePay"
                :disabled="!isFormValid"
                @payment-initiated="handleManualTransferInitiated"
                @proof-uploaded="handleProofUploaded"
                @back="selectedPaymentRail = null"
              />
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
                    class="ml-2 block text-body text-secondary-700"
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
                    class="ml-2 block text-body text-secondary-700"
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
                    class="ml-2 block text-body text-secondary-700"
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
                  class="flex items-center justify-center space-x-2 text-body text-secondary-600"
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
            <h3 class="modal-title text-secondary-900">Terms of Service</h3>
            <button
              @click="showTermsModal = false"
              class="text-secondary-400 hover:text-secondary-600"
            >
              <Icon name="heroicons:x-mark" class="h-6 w-6" />
            </button>
          </div>
          <p v-if="termsDocument" class="text-helper mt-2">
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
            <h3 class="modal-title text-secondary-900">Privacy Policy</h3>
            <button
              @click="showPrivacyModal = false"
              class="text-secondary-400 hover:text-secondary-600"
            >
              <Icon name="heroicons:x-mark" class="h-6 w-6" />
            </button>
          </div>
          <p v-if="privacyDocument" class="text-helper mt-2">
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
        <h3 class="modal-title text-secondary-900 mb-2">
          Payment Successful!
        </h3>
        <p class="text-secondary-600 mb-6">
          Your order has been confirmed. We'll start processing your NFC card
          and ship it to you within 5-7 business days.
        </p>
        <button @click="goToUserDashboard" class="btn btn-primary w-full">
          <Icon name="heroicons:arrow-right" class="h-5 w-5 mr-2" />
          Go to dashboard
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

definePageMeta({
  layout: "onboarding",
  // Guests may walk the funnel for Basic/Premium; everyone else is bounced to
  // register (see middleware/onboarding-access.js).
  middleware: "onboarding-access",
});

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

// Canonical onboarding state: the picked plan and the card draft live in the
// composable (mirrored to sessionStorage + the server draft), never in
// sessionStorage-only reads.
const { plan, draft, hydrate, guardFor, planForDesign, isGuest, flushDraftToServer } =
  useOnboardingFlow();

// Plan prices from API (same source as /Homepage/pricing)
const { planPrices, loadPrices } = usePlanPrices();

// The plan currently being priced (falls back to the account plan for design).
const pricedPlan = computed(() =>
  planPrices.value.find((p) => p.plan_type === planForDesign.value)
);

// Currency-aware money formatter — replaces the page's old hardcoded "$".
// e.g. "RM 99.00" (falls back to MYR when the plan has no currency).
const formatMoney = (amount) =>
  `${pricedPlan.value?.currency || "MYR"} ${Number(amount).toFixed(2)}`;

// Reactive data
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

// Shipping address (delivery address for the physical NFC card). For a guest
// the email doubles as the address of the account created at Pay time.
const billingAddress = reactive({
  fullName: "",
  email: "",
  phone: "",
  address: "",
  city: "",
  state: "",
  zipCode: "",
  country: "Malaysia",
});

// Set when /guest-checkout/start reports the email already belongs to a real
// account — the visitor must log in instead of creating the order as a stranger.
const guestAccountError = ref(null);

// Physical NFC card quantity
const maxQuantity = ref(5);
const cardQuantity = ref(1);
const increaseQuantity = () => {
  if (cardQuantity.value < maxQuantity.value) cardQuantity.value++;
};
const decreaseQuantity = () => {
  if (cardQuantity.value > 1) cardQuantity.value--;
};

// Physical card / shipping pricing — sourced from the backend
// (GET /payment/config → config/payment.php 'checkout'). Defaults mirror the
// backend defaults so checkout still works if the config call fails.
const cardUnitPrice = ref(15);
const shippingCost = ref(5);

// Order summary
const orderSummary = computed(() => {
  const selectedPlan = plan.value;
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

  const planPrice = Number(pricedPlan.value?.price) || 0;
  const unitPrice = cardUnitPrice.value;
  const shipping = shippingCost.value;
  const cardPrice = unitPrice * cardQuantity.value;
  const total = planPrice + cardPrice + shipping;

  return {
    planName: planNames[selectedPlan] || "Free Plan",
    planDescription:
      planDescriptions[selectedPlan] || "Perfect for getting started",
    planPrice,
    cardType: cardTypes[selectedPlan] || "Digital Only",
    cardUnitPrice: unitPrice,
    cardPrice,
    shippingCost: shipping,
    total,
  };
});

// Order metadata for payment components
const orderMetadata = computed(() => ({
  plan: plan.value,
  plan_price: orderSummary.value.planPrice,
  card_type: orderSummary.value.cardType,
  card_unit_price: orderSummary.value.cardUnitPrice,
  card_quantity: cardQuantity.value,
  shipping_cost: orderSummary.value.shippingCost,
  card_info: cardInfo.value,
  // The server snapshots the template + layout from this id at fulfilment.
  design_method: draft.value.designMethod || "template",
  card_template_id: draft.value.designMethod === "custom" ? null : draft.value.selectedTemplate || null,
  billing_address: {
    full_name: billingAddress.fullName,
    email: billingAddress.email,
    phone: billingAddress.phone,
    address: billingAddress.address,
    city: billingAddress.city,
    state: billingAddress.state,
    zip_code: billingAddress.zipCode,
    country: billingAddress.country,
  },
}));

// Card info from the onboarding draft
const cardInfo = computed(() => draft.value.cardInfo || {});

// The draft keeps only the template id; load the full template (images +
// designer layout) so the preview shows the actual card being ordered.
const selectedTemplateData = ref(null);
const loadSelectedTemplate = async () => {
  const id = draft.value.selectedTemplate;
  if (!id || draft.value.designMethod === "custom") return;
  try {
    const { $api } = useNuxtApp();
    const res = await $api.get(`/card-templates/${id}`);
    if (res?.success) selectedTemplateData.value = res.data;
  } catch (err) {
    console.warn("Could not load the selected card template", err);
  }
};

// Shipping address completeness. A guest has no account yet, so the email they
// type here is what creates it — it is required for them, optional otherwise.
const isAddressValid = computed(() => {
  const complete = !!(
    billingAddress.fullName &&
    billingAddress.phone &&
    billingAddress.address &&
    billingAddress.city &&
    billingAddress.state &&
    billingAddress.zipCode &&
    billingAddress.country
  );
  if (!complete) return false;
  if (isGuest.value) return !!billingAddress.email;
  return true;
});

// Form validation
const isFormValid = computed(() => {
  if (!selectedPaymentRail.value) {
    return false;
  }

  return isAddressValid.value && termsAccepted.value && billingConsent.value;
});

/**
 * Guest checkout: create (or claim) the placeholder account and store its token
 * before any payment request is sent — every transaction needs an owner, so the
 * account must exist first. Returns false when the visitor must not proceed.
 */
const ensureGuestAccount = async () => {
  guestAccountError.value = null;

  const email = (billingAddress.email || "").trim();
  if (!email) {
    $toast.error("Please enter your email address before paying.");
    return false;
  }

  const [firstName, ...restName] = (billingAddress.fullName || "")
    .trim()
    .split(/\s+/)
    .filter(Boolean);

  try {
    const { $api } = useNuxtApp();
    const response = await $api.post("/guest-checkout/start", {
      email,
      plan: plan.value,
      first_name: firstName || "",
      last_name: restName.join(" "),
    });

    // Persist the token exactly like a normal login so every later request
    // (/payment/initiate, /onboarding/*) resolves auth()->user() as usual.
    const tokenCookie = useCookie("auth-token", {
      httpOnly: false,
      secure: process.env.NODE_ENV === "production",
      sameSite: "lax",
      maxAge: 60 * 60 * 24 * 7,
    });
    tokenCookie.value = response.token;

    authStore.token = response.token;
    authStore.user = response.user;
    authStore.isAuthenticated = true;

    // An account now exists: replay the draft/uploads the funnel held locally.
    await flushDraftToServer();

    return true;
  } catch (error) {
    const data = error?.response?.data || error?.response?._data || error?.data || {};
    guestAccountError.value =
      data.message ||
      "We could not start your checkout. Please check your email and try again.";
    return false;
  }
};

// Runs immediately before any payment request: a signed-in user proceeds as
// before, a guest first gets an account (and token) created for them.
const beforePay = async () =>
  authStore.isAuthenticated ? true : ensureGuestAccount();

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

  // Store order details (other pages still read these flags)
  sessionStorage.setItem("orderCompleted", "true");
  sessionStorage.setItem("onboarding_completed", "true");
  sessionStorage.setItem(
    "orderDetails",
    JSON.stringify({
      plan: plan.value,
      cardInfo: cardInfo.value,
      paymentMethod: selectedPaymentRail.value,
      total: orderSummary.value.total,
      transactionId: data.transaction_id,
      orderDate: new Date().toISOString(),
      orderId: data.order_id || `ORD-${Date.now()}`,
      estimatedDelivery: "5-7 business days",
    })
  );

  $toast.success("Payment successful!");
  await navigateTo("/Onboarding/complete?status=paid", { replace: true });
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
const handleProofUploaded = async (data) => {
  console.log('Payment proof uploaded:', data);

  // Store pending order details
  sessionStorage.setItem("orderPending", "true");
  sessionStorage.setItem(
    "pendingOrderDetails",
    JSON.stringify({
      plan: plan.value,
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

  await navigateTo("/Onboarding/complete?status=pending", { replace: true });
};

// Load physical-card / shipping pricing from the backend
const loadCheckoutConfig = async () => {
  try {
    const { $api } = useNuxtApp();
    const response = await $api.get("/payment/config");
    const data = response?.data ?? response;
    if (data?.card_unit_price !== undefined) {
      cardUnitPrice.value = Number(data.card_unit_price);
    }
    if (data?.shipping_fee !== undefined) {
      shippingCost.value = Number(data.shipping_fee);
    }
    if (data?.max_card_quantity) {
      maxQuantity.value = Number(data.max_card_quantity);
      if (cardQuantity.value > maxQuantity.value) {
        cardQuantity.value = maxQuantity.value;
      }
    }
  } catch (error) {
    // Non-critical: keep the backend-matching defaults
    console.warn("Failed to load checkout config, using defaults:", error);
  }
};

onMounted(async () => {
  // Auth + step gating is handled by middleware + the onboarding guard below.
  await hydrate();

  const redirect = guardFor("checkout");
  if (redirect) {
    await navigateTo(redirect, { replace: true });
    return;
  }

  // Card preview: fetch the chosen template in the background
  loadSelectedTemplate();

  // Load plan prices from API
  await loadPrices();

  // Load physical-card / shipping pricing from the backend config
  await loadCheckoutConfig();

  // Prefill shipping address from the user profile
  if (authStore.user) {
    billingAddress.fullName =
      authStore.user.full_name || authStore.user.name || "";
    billingAddress.phone = authStore.user.phone || "";
    billingAddress.city = authStore.user.city || "";
    billingAddress.state = authStore.user.state || "";
    billingAddress.zipCode =
      authStore.user.postcode || authStore.user.zip_code || "";
    billingAddress.country = authStore.user.country || "Malaysia";
  }

  // Load legal documents (non-blocking)
  loadLegalDocuments().catch((err) => {
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

// Go back to the design step
const goBack = () => {
  // If a payment rail is selected, go back to rail selection
  if (selectedPaymentRail.value) {
    selectedPaymentRail.value = null;
    return;
  }

  // Otherwise go back to the previous onboarding step
  navigateTo("/Onboarding/design");
};

// Go to UserDashboard
const goToUserDashboard = async () => {
  showSuccessModal.value = false;

  // Set flag to indicate payment was just completed
  sessionStorage.setItem("just_completed_payment", "true");

  $toast.success("Onboarding complete! Redirecting to your dashboard.");
  await navigateTo("/UserDashboard");
};
</script>
