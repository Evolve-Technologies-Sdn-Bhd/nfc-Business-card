<!--
  pages/UserDashboard/PlanSelection.vue
  Onboarding step 1 — in-app plan selection.

  Design contract: this step deliberately reuses the exact card language of
  /Homepage/pricing (`.price-card*` in assets/css/public.css) so the marketing
  funnel and the onboarding funnel read as ONE product. The only additions are
  the states that only exist in-app: "Current Plan" and a per-button loading
  state. Palette is the system standard: Ink (black) + Brass (gold) + Paper.
-->
<template>
  <div class="min-h-screen ink-surface ink-grid relative overflow-hidden">
    <!-- Onboarding top bar -->
    <header class="ink-topbar sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <NuxtLink to="/Homepage" class="inline-flex items-center">
            <NFCGoLogo variant="dark" size="sm" />
          </NuxtLink>

          <div class="flex items-center gap-3 sm:gap-4">
            <span
              class="hidden sm:inline text-micro uppercase text-white/55"
              style="letter-spacing: 0.16em"
            >
              Step 1 of 3 · Plan
            </span>
            <div class="flex items-center gap-1.5" aria-hidden="true">
              <span class="step-dot step-dot--active"></span>
              <span class="step-dot"></span>
              <span class="step-dot"></span>
            </div>
          </div>
        </div>
      </div>
    </header>

    <div class="container relative py-14 sm:py-20">
      <!-- Step header -->
      <div class="max-w-2xl mb-14">
        <span class="lp-kicker on-ink">Pricing · Step 1 of 3</span>
        <h1 class="lp-h2 on-ink mt-4 mb-4">Choose your plan</h1>
        <p class="lp-lead lead-ink">
          Same plans as our pricing page — pick one now to finish setting up
          your account<template v-if="accountEmail"> (<span class="text-[color:var(--brass)]">{{ accountEmail }}</span>)</template>.
          You can upgrade, downgrade or cancel anytime.
        </p>
        <NuxtLink
          to="/Homepage/pricing"
          class="inline-flex items-center gap-1.5 mt-5 text-body-sm font-semibold text-[color:var(--brass)] hover:text-[color:var(--brass-200)] transition-colors"
        >
          <Icon name="heroicons:arrows-right-left" class="h-4 w-4" />
          Compare the full feature breakdown on the pricing page
        </NuxtLink>
      </div>

      <!-- Plans grid -->
      <div
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto"
      >
        <!-- Free -->
        <div
          class="price-card"
          :class="{ 'price-card--current': isCurrentPlan('free') }"
        >
          <span
            v-if="isCurrentPlan('free')"
            class="price-card__badge price-card__badge--outline"
          >
            <Icon name="heroicons:check-circle" class="h-3 w-3" />
            Current Plan
          </span>
          <h2 class="price-card__name">Free</h2>
          <div class="price-card__amount">RM0</div>
          <p class="price-card__desc">Perfect for getting started</p>
          <ul class="price-card__list">
            <li v-for="feature in freeFeatures" :key="feature">
              <Icon name="heroicons:check" class="h-4 w-4 price-card__tick" />
              <span>{{ feature }}</span>
            </li>
          </ul>
          <div class="price-card__cta">
            <button
              class="btn-ghost-light w-full"
              :disabled="isCurrentPlan('free') || selectingPlan !== null"
              @click="selectPlan('free')"
            >
              <span v-if="selectingPlan === 'free'" class="btn-spinner"></span>
              <span>{{ isCurrentPlan('free') ? 'Current Plan' : 'Get Started Free' }}</span>
            </button>
          </div>
        </div>

        <!-- Basic -->
        <div
          class="price-card"
          :class="{ 'price-card--current': isCurrentPlan('basic') }"
        >
          <span
            v-if="isCurrentPlan('basic')"
            class="price-card__badge price-card__badge--outline"
          >
            <Icon name="heroicons:check-circle" class="h-3 w-3" />
            Current Plan
          </span>
          <h2 class="price-card__name">Basic</h2>
          <div class="price-card__amount">{{ priceLabel('basic') }}</div>
          <p class="price-card__desc">
            {{ basicPlan?.description || 'For individual professionals' }}
          </p>
          <ul class="price-card__list">
            <li v-for="feature in basicPlan?.features" :key="feature">
              <Icon name="heroicons:check" class="h-4 w-4 price-card__tick" />
              <span>{{ feature }}</span>
            </li>
          </ul>
          <div class="price-card__cta">
            <button
              class="btn-ghost-light w-full"
              :disabled="isCurrentPlan('basic') || selectingPlan !== null"
              @click="selectPlan('basic')"
            >
              <span v-if="selectingPlan === 'basic'" class="btn-spinner"></span>
              <span>{{ isCurrentPlan('basic') ? 'Current Plan' : 'Choose Basic' }}</span>
            </button>
          </div>
        </div>

        <!-- Premium -->
        <div
          class="price-card price-card--pop"
          :class="{ 'price-card--current': isCurrentPlan('premium') }"
        >
          <span
            class="price-card__badge"
            :class="
              isCurrentPlan('premium') ? 'price-card__badge--left' : undefined
            "
          >
            Most Popular
          </span>
          <span
            v-if="isCurrentPlan('premium')"
            class="price-card__badge price-card__badge--outline price-card__badge--right"
          >
            <Icon name="heroicons:check-circle" class="h-3 w-3" />
            Current Plan
          </span>
          <h2 class="price-card__name">Premium</h2>
          <div class="price-card__amount">{{ priceLabel('premium') }}</div>
          <p class="price-card__desc">
            {{ premiumPlan?.description || 'For growing businesses' }}
          </p>
          <ul class="price-card__list">
            <li v-for="feature in premiumPlan?.features" :key="feature">
              <Icon name="heroicons:check" class="h-4 w-4 price-card__tick" />
              <span>{{ feature }}</span>
            </li>
          </ul>
          <div class="price-card__cta">
            <button
              class="btn-brass w-full"
              :disabled="isCurrentPlan('premium') || selectingPlan !== null"
              @click="selectPlan('premium')"
            >
              <span v-if="selectingPlan === 'premium'" class="btn-spinner"></span>
              <span>{{ isCurrentPlan('premium') ? 'Current Plan' : 'Choose Premium' }}</span>
            </button>
          </div>
        </div>

        <!-- Business -->
        <div class="price-card">
          <h2 class="price-card__name">Business</h2>
          <div class="price-card__amount">{{ priceLabel('business') }}</div>
          <p class="price-card__desc">
            {{ businessPlan?.description || 'For large organizations' }}
          </p>
          <ul class="price-card__list">
            <li v-for="feature in businessPlan?.features" :key="feature">
              <Icon name="heroicons:check" class="h-4 w-4 price-card__tick" />
              <span>{{ feature }}</span>
            </li>
          </ul>
          <div class="price-card__cta">
            <button
              class="btn-ghost-light w-full"
              :disabled="selectingPlan !== null"
              @click="selectPlan('business')"
            >
              Contact Sales
            </button>
          </div>
        </div>
      </div>

      <!-- Footnotes — same wording as the pricing page -->
      <div class="mt-12">
        <p class="text-white/55">
          Free plan: 14 days • No credit card required • Upgrade anytime
        </p>
        <div
          class="mt-3 flex flex-col sm:flex-row gap-2 items-start sm:items-center"
        >
          <p class="text-body-sm text-white/65">Need help choosing?</p>
          <NuxtLink
            to="/Homepage/pricing"
            class="text-[color:var(--brass)] hover:text-[color:var(--brass-200)] font-medium text-body-sm underline"
          >
            Talk to our sales team
          </NuxtLink>
        </div>
      </div>
    </div>

    <!-- Business plan enquiry modal (paper panel on the ink page) -->
    <div
      v-if="showBusinessModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
      @click="closeBusinessModal"
    >
      <div
        class="paper-surface text-secondary-900 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
        @click.stop
      >
        <!-- Modal header -->
        <div
          class="relative flex items-start justify-between gap-4 p-6 rounded-t-2xl"
          style="background-color: var(--ink-900)"
        >
          <div class="flex items-start gap-4">
            <div
              class="flex items-center justify-center h-12 w-12 rounded-xl"
              style="
                background: color-mix(in srgb, var(--brass) 18%, transparent);
                border: 1px solid color-mix(in srgb, var(--brass) 40%, transparent);
              "
            >
              <Icon
                name="heroicons:building-office"
                class="h-6 w-6 text-[color:var(--brass)]"
              />
            </div>
            <div>
              <h3 class="modal-title text-[color:var(--paper)]">
                Business Plan Request
              </h3>
              <p class="text-body-sm text-white/60 mt-1">
                Tell us about your organization and we'll tailor a plan.
              </p>
            </div>
          </div>
          <button
            type="button"
            aria-label="Close"
            class="p-2 rounded-lg text-white/70 hover:text-white hover:bg-white/10 transition-colors"
            @click="closeBusinessModal"
          >
            <Icon name="heroicons:x-mark" class="h-5 w-5" />
          </button>
        </div>

        <!-- Modal body -->
        <form class="p-6 space-y-5" @submit.prevent="submitBusinessRequest">
          <div>
            <label class="block text-label text-secondary-900 mb-2">
              Company Name <span class="text-[color:var(--brass-700)]">*</span>
            </label>
            <input
              v-model="businessForm.company_name"
              type="text"
              required
              placeholder="Enter your company name"
              class="ink-field"
            />
          </div>

          <div>
            <label class="block text-label text-secondary-900 mb-2">
              Company Address <span class="text-[color:var(--brass-700)]">*</span>
            </label>
            <textarea
              v-model="businessForm.company_address"
              required
              rows="3"
              placeholder="Enter your company address"
              class="ink-field"
            ></textarea>
          </div>

          <div>
            <label class="block text-label text-secondary-900 mb-2">
              Number of NFC Cards Needed
              <span class="text-[color:var(--brass-700)]">*</span>
            </label>
            <input
              v-model.number="businessForm.quota"
              type="number"
              required
              min="1"
              placeholder="e.g., 50"
              class="ink-field"
            />
            <p class="text-helper mt-2">
              How many NFC cards do you need for your team?
            </p>
          </div>

          <div>
            <label class="block text-label text-secondary-900 mb-2">
              Contact Person <span class="text-[color:var(--brass-700)]">*</span>
            </label>
            <input
              v-model="businessForm.contact_person"
              type="text"
              required
              placeholder="Full name"
              class="ink-field"
            />
          </div>

          <div>
            <label class="block text-label text-secondary-900 mb-2">
              Phone Number <span class="text-[color:var(--brass-700)]">*</span>
            </label>
            <input
              v-model="businessForm.phone"
              type="tel"
              required
              placeholder="+60 12-345 6789"
              class="ink-field"
            />
          </div>

          <div>
            <label class="block text-label text-secondary-900 mb-2">
              Email Address <span class="text-[color:var(--brass-700)]">*</span>
            </label>
            <input
              v-model="businessForm.email"
              type="email"
              required
              placeholder="your@company.com"
              class="ink-field"
            />
          </div>

          <div>
            <label class="block text-label text-secondary-900 mb-2">
              Additional Notes (Optional)
            </label>
            <textarea
              v-model="businessForm.notes"
              rows="3"
              placeholder="Any special requirements or questions?"
              class="ink-field"
            ></textarea>
          </div>

          <div class="flex flex-col sm:flex-row gap-3 pt-4">
            <button
              type="button"
              @click="closeBusinessModal"
              class="flex-1 inline-flex items-center justify-center rounded-xl px-6 py-3 text-sm font-semibold text-secondary-700 bg-secondary-100 hover:bg-secondary-200 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submittingBusinessRequest"
              class="flex-1 btn-brass"
            >
              <span v-if="submittingBusinessRequest" class="btn-spinner"></span>
              <span>{{ submittingBusinessRequest ? 'Submitting...' : 'Submit Request' }}</span>
            </button>
          </div>

          <p class="text-helper text-center text-secondary-600">
            Our team will contact you within 24 hours to discuss your Business
            Plan setup.
          </p>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
useHead({
  title: "Choose Your Plan - NFCGo",
  meta: [
    {
      name: "description",
      content: "Choose the perfect NFCGo plan for your business needs.",
    },
  ],
});

// Stores
const authStore = useAuthStore();
const { $toast } = useNuxtApp();

// Plan prices from API (same source as /Homepage/pricing)
const { loading, planPrices, loadPrices } = usePlanPrices();

const basicPlan = computed(() =>
  planPrices.value.find((p) => p.plan_type === "basic")
);
const premiumPlan = computed(() =>
  planPrices.value.find((p) => p.plan_type === "premium")
);
const businessPlan = computed(() =>
  planPrices.value.find((p) => p.plan_type === "business")
);

// Free plan copy is kept identical to the pricing page so both funnels match.
const freeFeatures = [
  "1 Digital Profile",
  "Basic Templates",
  "Contact Information",
  "Social Media Links",
  "QR Code Backup",
  "Basic Analytics",
];

// Same amount formatting as the pricing page.
const priceLabel = (planType) => {
  if (loading.value) return "Loading...";
  const plan = planPrices.value.find((p) => p.plan_type === planType);
  return plan ? `${plan.currency} ${plan.price}` : "Contact for Pricing";
};

// Get user's current plan
const currentPlan = computed(() => authStore.user?.subscription_plan || "free");
const accountEmail = computed(() => authStore.user?.email || "");

// Only free/basic/premium can be shown as "current" — business is enquiry-only.
const PLANS_WITH_INDICATOR = ["free", "basic", "premium"];
const isCurrentPlan = (plan) => {
  if (!PLANS_WITH_INDICATOR.includes(currentPlan.value)) return false;
  return currentPlan.value === plan;
};

// Business plan modal state
const showBusinessModal = ref(false);
const submittingBusinessRequest = ref(false);
const selectingPlan = ref(null);

// Business form data
const businessForm = reactive({
  company_name: "",
  company_address: "",
  quota: null,
  contact_person: "",
  phone: "",
  email: authStore.user?.email || "",
  notes: "",
});

const prefillContact = () => ({
  contact_person:
    authStore.user?.first_name && authStore.user?.last_name
      ? `${authStore.user.first_name} ${authStore.user.last_name}`
      : authStore.user?.name || "",
  email: authStore.user?.email || "",
});

onMounted(async () => {
  // Wait for auth state to be ready (especially important after registration)
  if (!authStore.isAuthenticated || !authStore.user) {
    let attempts = 0;
    while ((!authStore.isAuthenticated || !authStore.user) && attempts < 10) {
      await new Promise((resolve) => setTimeout(resolve, 200));
      attempts++;
    }

    if (!authStore.isAuthenticated || !authStore.user) {
      // Bring the user back here after they sign in instead of dropping them
      // on the generic dashboard.
      await navigateTo({
        path: "/UserAccount/login",
        query: { redirect: "/UserDashboard/PlanSelection" },
      });
      return;
    }
  }

  await loadPrices();

  // Pre-fill user info
  Object.assign(businessForm, prefillContact());
});

// Close business modal
const closeBusinessModal = () => {
  showBusinessModal.value = false;
  Object.assign(
    businessForm,
    {
      company_name: "",
      company_address: "",
      quota: null,
      phone: "",
      notes: "",
    },
    prefillContact()
  );
};

// Submit business plan request
const submitBusinessRequest = async () => {
  submittingBusinessRequest.value = true;

  try {
    const { $api } = useNuxtApp();

    const response = await $api.post("/business-inquiries", {
      company_name: businessForm.company_name,
      company_address: businessForm.company_address,
      quota: businessForm.quota,
      contact_person: businessForm.contact_person,
      phone: businessForm.phone,
      email: businessForm.email,
      notes: businessForm.notes,
      source: "plan_selection",
    });

    if (!response?.success) {
      throw new Error(response?.message || "Failed to submit enquiry");
    }

    $toast.success(
      "Your Business Plan enquiry has been received. Our team will contact you soon."
    );

    closeBusinessModal();
    await navigateTo("/Homepage?businessRequest=success");
  } catch (error) {
    console.error("Business plan request error:", error);
    $toast.error(
      error?.response?.data?.message ||
        "Failed to submit your enquiry. Please try again or contact support."
    );
  } finally {
    submittingBusinessRequest.value = false;
  }
};

// Select plan and proceed
const selectPlan = async (plan) => {
  // Business is an enquiry, not a self-serve plan.
  if (plan === "business") {
    showBusinessModal.value = true;
    return;
  }

  if (selectingPlan.value || isCurrentPlan(plan)) return;
  selectingPlan.value = plan;

  try {
    // Store selected plan in session storage for the onboarding flow
    sessionStorage.setItem("selectedPlan", plan);

    const { $api } = useNuxtApp();
    await $api.post("/onboarding/select-plan", { plan });

    // Free plan users skip NFC customization and go straight to the profile builder
    if (plan === "free") {
      $toast.success("Free plan activated! Welcome to your profile builder.");
      await navigateTo(
        "/UserDashboard/UserManagement/FreePlanUser/FreeProfileBuilder"
      );
      return;
    }

    // Paid plans continue to NFC card customization
    $toast.success(`Great choice! Let's customize your ${plan} plan NFC card.`);
    await navigateTo("/UserDashboard/UserManagement/NFCCardDesignCustomization");
  } catch (error) {
    console.error("Plan selection error:", error);
    // The global api.client 403 handler already toasts the specific reason for
    // one-time-trial refusals (TRIAL_* / DISPOSABLE_EMAIL) and for unverified
    // emails, so only show a fallback toast for everything else.
    const data = error?.response?.data;
    const handledByInterceptor =
      error?.response?.status === 403 &&
      (typeof data?.code === "string" ||
        data?.email_verification_required ||
        data?.error === "EMAIL_NOT_VERIFIED");
    if (!handledByInterceptor) {
      $toast.error(data?.message || "Failed to select plan. Please try again.");
    }
    selectingPlan.value = null;
  }
};
</script>

<style scoped>
/* Small inline spinner that inherits the button's own text colour. */
.btn-spinner {
  width: 1rem;
  height: 1rem;
  margin-right: 0.5rem;
  border-radius: 999px;
  border: 2px solid currentColor;
  border-top-color: transparent;
  animation: plan-spin 0.7s linear infinite;
}
@keyframes plan-spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
