<!-- pages/onboarding/plan-selection.vue -->
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
            <span class="text-sm text-secondary-600">Step 1 of 3</span>
            <div class="flex space-x-1">
              <div class="w-2 h-2 bg-primary-600 rounded-full"></div>
              <div class="w-2 h-2 bg-secondary-300 rounded-full"></div>
              <div class="w-2 h-2 bg-secondary-300 rounded-full"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-secondary-900 mb-4">
          Choose Your Plan
        </h1>
        <p class="text-xl text-secondary-600 max-w-2xl mx-auto">
          Select the perfect plan for your business needs. Start with our free
          plan or unlock premium features with our paid plans.
        </p>
      </div>

      <!-- Plans Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <!-- Free Plan -->
        <div
          class="bg-white rounded-2xl shadow-lg border-2 border-secondary-200 p-6 relative"
        >
          <div class="text-center">
            <div
              class="w-16 h-16 bg-secondary-100 rounded-full flex items-center justify-center mx-auto mb-4"
            >
              <Icon name="heroicons:gift" class="h-8 w-8 text-secondary-600" />
            </div>
            <h3 class="text-xl font-bold text-secondary-900 mb-2">Free</h3>
            <div class="mb-6">
              <span class="text-3xl font-bold text-secondary-900">$0</span>
              <span class="text-secondary-600">/month</span>
            </div>
            <p class="text-sm text-secondary-600 mb-6">
              Perfect for getting started
            </p>

            <ul class="text-left space-y-3 mb-8">
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>Digital business card</span>
              </li>
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>Basic analytics</span>
              </li>
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>Up to 5 social links</span>
              </li>
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>Basic customization</span>
              </li>
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>QR code generation</span>
              </li>
            </ul>

            <button
              @click="selectPlan('free')"
              class="w-full py-3 px-4 bg-secondary-100 text-secondary-700 rounded-xl font-medium hover:bg-secondary-200 transition-colors"
            >
              Get Started Free
            </button>
          </div>
        </div>

        <!-- Basic Plan -->
        <div
          class="bg-white rounded-2xl shadow-lg border-2 border-primary-200 p-6 relative"
        >
          <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
            <span
              class="bg-primary-600 text-white px-3 py-1 rounded-full text-xs font-medium"
              >Most Popular</span
            >
          </div>
          <div class="text-center">
            <div
              class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4"
            >
              <Icon name="heroicons:star" class="h-8 w-8 text-primary-600" />
            </div>
            <h3 class="text-xl font-bold text-secondary-900 mb-2">Basic</h3>
            <div class="mb-6">
              <span class="text-3xl font-bold text-secondary-900">$9</span>
              <span class="text-secondary-600">/month</span>
            </div>
            <p class="text-sm text-secondary-600 mb-6">
              Great for small businesses
            </p>

            <ul class="text-left space-y-3 mb-8">
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>Everything in Free</span>
              </li>
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>Physical NFC card (Basic)</span>
              </li>
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>Advanced analytics</span>
              </li>
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>Unlimited social links</span>
              </li>
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>Custom branding</span>
              </li>
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>Priority support</span>
              </li>
            </ul>

            <button
              @click="selectPlan('basic')"
              class="w-full py-3 px-4 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors"
            >
              Choose Basic
            </button>
          </div>
        </div>

        <!-- Premium Plan -->
        <div
          class="bg-white rounded-2xl shadow-lg border-2 border-secondary-200 p-6 relative"
        >
          <div class="text-center">
            <div
              class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4"
            >
              <Icon name="heroicons:sparkles" class="h-8 w-8 text-purple-600" />
            </div>
            <h3 class="text-xl font-bold text-secondary-900 mb-2">Premium</h3>
            <div class="mb-6">
              <span class="text-3xl font-bold text-secondary-900">$19</span>
              <span class="text-secondary-600">/month</span>
            </div>
            <p class="text-sm text-secondary-600 mb-6">
              For growing businesses
            </p>

            <ul class="text-left space-y-3 mb-8">
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>Everything in Basic</span>
              </li>
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>Physical NFC card (Premium)</span>
              </li>
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>Advanced card customization</span>
              </li>
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>Detailed analytics</span>
              </li>
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>Team management</span>
              </li>
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>API access</span>
              </li>
            </ul>

            <button
              @click="selectPlan('premium')"
              class="w-full py-3 px-4 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition-colors"
            >
              Choose Premium
            </button>
          </div>
        </div>

        <!-- Business Plan -->
        <div
          class="bg-white rounded-2xl shadow-lg border-2 border-secondary-200 p-6 relative"
        >
          <div class="text-center">
            <div
              class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4"
            >
              <Icon
                name="heroicons:building-office"
                class="h-8 w-8 text-indigo-600"
              />
            </div>
            <h3 class="text-xl font-bold text-secondary-900 mb-2">Business</h3>
            <div class="mb-6">
              <span class="text-3xl font-bold text-secondary-900">$49</span>
              <span class="text-secondary-600">/month</span>
            </div>
            <p class="text-sm text-secondary-600 mb-6">
              For large organizations
            </p>

            <ul class="text-left space-y-3 mb-8">
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>Everything in Premium</span>
              </li>
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>Multiple NFC cards</span>
              </li>
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>White-label solution</span>
              </li>
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>Advanced integrations</span>
              </li>
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>Dedicated support</span>
              </li>
              <li class="flex items-center text-sm">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-500 mr-2 flex-shrink-0"
                />
                <span>Custom development</span>
              </li>
            </ul>

            <button
              @click="selectPlan('business')"
              class="w-full py-3 px-4 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition-colors"
            >
              Choose Business
            </button>
          </div>
        </div>
      </div>

      <!-- Additional Info -->
      <div class="text-center">
        <p class="text-sm text-secondary-600 mb-4">
          All paid plans include a 14-day free trial. No credit card required to
          start.
        </p>
        <p class="text-xs text-secondary-500">
          Need help choosing?
          <a href="#" class="text-primary-600 hover:text-primary-500"
            >Contact our sales team</a
          >
        </p>
      </div>
    </div>

    <!-- Business Plan Request Modal -->
    <div
      v-if="showBusinessModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
      @click="closeBusinessModal"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
        @click.stop
      >
        <!-- Modal Header -->
        <div
          class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 rounded-t-2xl"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="p-3 bg-white/20 rounded-xl mr-4">
                <Icon
                  name="heroicons:building-office"
                  class="h-8 w-8 text-white"
                />
              </div>
              <div>
                <h3 class="text-2xl font-bold text-white">
                  Business Plan Request
                </h3>
                <p class="text-indigo-100 text-sm mt-1">
                  Tell us about your organization
                </p>
              </div>
            </div>
            <button
              @click="closeBusinessModal"
              class="p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-colors"
            >
              <Icon name="heroicons:x-mark" class="h-6 w-6" />
            </button>
          </div>
        </div>

        <!-- Modal Body -->
        <form @submit.prevent="submitBusinessRequest" class="p-6 space-y-6">
          <!-- Company Name -->
          <div>
            <label class="block text-sm font-semibold text-secondary-900 mb-2">
              Company Name <span class="text-red-500">*</span>
            </label>
            <input
              v-model="businessForm.company_name"
              type="text"
              required
              placeholder="Enter your company name"
              class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
            />
          </div>

          <!-- Company Address -->
          <div>
            <label class="block text-sm font-semibold text-secondary-900 mb-2">
              Company Address <span class="text-red-500">*</span>
            </label>
            <textarea
              v-model="businessForm.company_address"
              required
              rows="3"
              placeholder="Enter your company address"
              class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
            ></textarea>
          </div>

          <!-- Number of Cards (Quota) -->
          <div>
            <label class="block text-sm font-semibold text-secondary-900 mb-2">
              Number of NFC Cards Needed <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="businessForm.quota"
              type="number"
              required
              min="1"
              placeholder="e.g., 50"
              class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
            />
            <p class="text-xs text-secondary-500 mt-2">
              How many NFC cards do you need for your team?
            </p>
          </div>

          <!-- Contact Person -->
          <div>
            <label class="block text-sm font-semibold text-secondary-900 mb-2">
              Contact Person <span class="text-red-500">*</span>
            </label>
            <input
              v-model="businessForm.contact_person"
              type="text"
              required
              placeholder="Full name"
              class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
            />
          </div>

          <!-- Phone Number -->
          <div>
            <label class="block text-sm font-semibold text-secondary-900 mb-2">
              Phone Number <span class="text-red-500">*</span>
            </label>
            <input
              v-model="businessForm.phone"
              type="tel"
              required
              placeholder="+60 12-345 6789"
              class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
            />
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-semibold text-secondary-900 mb-2">
              Email Address <span class="text-red-500">*</span>
            </label>
            <input
              v-model="businessForm.email"
              type="email"
              required
              placeholder="your@company.com"
              class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
            />
          </div>

          <!-- Additional Notes -->
          <div>
            <label class="block text-sm font-semibold text-secondary-900 mb-2">
              Additional Notes (Optional)
            </label>
            <textarea
              v-model="businessForm.notes"
              rows="3"
              placeholder="Any special requirements or questions?"
              class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
            ></textarea>
          </div>

          <!-- Action Buttons -->
          <div class="flex space-x-4 pt-4">
            <button
              type="button"
              @click="closeBusinessModal"
              class="flex-1 py-3 px-4 bg-secondary-100 text-secondary-700 rounded-xl font-medium hover:bg-secondary-200 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submittingBusinessRequest"
              class="flex-1 py-3 px-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-medium hover:from-indigo-700 hover:to-purple-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
            >
              <div v-if="submittingBusinessRequest" class="flex items-center">
                <div
                  class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"
                ></div>
                Submitting...
              </div>
              <span v-else>Submit Request</span>
            </button>
          </div>

          <p class="text-xs text-secondary-500 text-center">
            Our team will contact you within 24 hours to discuss your Business
            Plan setup.
          </p>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
// Meta tags
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

// Route and router
const router = useRouter();

// Business plan modal state
const showBusinessModal = ref(false);
const submittingBusinessRequest = ref(false);

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

// Check if user is authenticated
onMounted(async () => {
  console.log("PlanSelection mounted, checking auth...");
  console.log("Initial auth state:", {
    isAuthenticated: authStore.isAuthenticated,
    hasUser: !!authStore.user,
    userEmail: authStore.user?.email,
  });

  // Wait for auth state to be ready (especially important after registration)
  if (!authStore.isAuthenticated || !authStore.user) {
    console.log("Waiting for auth state to initialize...");

    // Wait up to 2 seconds for auth state to be ready
    let attempts = 0;
    while ((!authStore.isAuthenticated || !authStore.user) && attempts < 10) {
      await new Promise((resolve) => setTimeout(resolve, 200));
      attempts++;
      console.log(`Waiting for auth (attempt ${attempts})...`);
    }

    // If still not authenticated after waiting, redirect to login
    if (!authStore.isAuthenticated || !authStore.user) {
      console.log("Auth state not ready, redirecting to login");
      router.push("/UserAccount/login");
      return;
    }
  }

  console.log("User authenticated:", authStore.user.email);

  // Pre-fill user info
  businessForm.email = authStore.user?.email || "";
  businessForm.contact_person =
    authStore.user?.first_name && authStore.user?.last_name
      ? `${authStore.user.first_name} ${authStore.user.last_name}`
      : authStore.user?.name || "";
});

// Close business modal
const closeBusinessModal = () => {
  showBusinessModal.value = false;
  // Reset form
  Object.assign(businessForm, {
    company_name: "",
    company_address: "",
    quota: null,
    contact_person:
      authStore.user?.first_name && authStore.user?.last_name
        ? `${authStore.user.first_name} ${authStore.user.last_name}`
        : authStore.user?.name || "",
    phone: "",
    email: authStore.user?.email || "",
    notes: "",
  });
};

// Submit business plan request
const submitBusinessRequest = async () => {
  submittingBusinessRequest.value = true;

  try {
    // Prepare email content
    const subject = `Business Plan Request - ${businessForm.company_name}`;
    const body = `
Business Plan Request Details:

════════════════════════════════
COMPANY INFORMATION
════════════════════════════════
Company Name: ${businessForm.company_name}
Company Address: ${businessForm.company_address}
Number of NFC Cards Needed: ${businessForm.quota} cards

════════════════════════════════
CONTACT INFORMATION
════════════════════════════════
Contact Person: ${businessForm.contact_person}
Email: ${businessForm.email}
Phone: ${businessForm.phone}

════════════════════════════════
USER ACCOUNT DETAILS
════════════════════════════════
User: ${authStore.user?.first_name || ""} ${authStore.user?.last_name || ""}
User Email: ${authStore.user?.email || ""}
Submitted: ${new Date().toLocaleString()}
${
  businessForm.notes
    ? `\n════════════════════════════════\nADDITIONAL NOTES\n════════════════════════════════\n${businessForm.notes}`
    : ""
}

════════════════════════════════
Please contact this customer within 24 hours.
This is an automated request from NFCGo platform.
`;

    // Create Gmail compose URL
    const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent(
      "genn.chong@clbgroups.com"
    )}&su=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;

    // Open Gmail in new tab
    window.open(gmailUrl, "_blank");

    // Show success message
    $toast.success(
      "Opening Gmail... Please send the email, then you'll be redirected to your dashboard."
    );

    // Close modal and redirect to dashboard after a short delay
    setTimeout(() => {
      closeBusinessModal();
      router.push("/UserDashboard");
    }, 2000);
  } catch (error) {
    console.error("Business plan request error:", error);
    $toast.error(
      "Failed to open email client. Please contact genn.chong@clbgroups.com directly."
    );
  } finally {
    submittingBusinessRequest.value = false;
  }
};

// Select plan and proceed
const selectPlan = async (plan) => {
  try {
    // If business plan is selected, show the modal instead
    if (plan === "business") {
      showBusinessModal.value = true;
      return;
    }

    // Store selected plan in session storage for the onboarding flow
    sessionStorage.setItem("selectedPlan", plan);

    // Call backend API to update user's plan
    const { $api } = useNuxtApp();
    await $api.post("/onboarding/select-plan", { plan });

    if (plan === "free") {
      // For free plan, mark onboarding as complete and go directly to CardManagement
      await $api.post("/complete-onboarding");

      // Update auth store to reflect user is no longer new
      if (authStore.user) {
        authStore.user.is_new_user = false;
      }

      $toast.success(
        "Welcome to NFCGo! Let's get started with your free plan."
      );
      await router.push("/UserDashboard/CardManagement");
    } else {
      // For paid plans, proceed to NFC Card Customization first
      $toast.success(
        `Great choice! Let's customize your ${plan} plan NFC card.`
      );
      await router.push(
        "/UserDashboard/UserManagement/NFCCardDesignCustomization"
      );
    }
  } catch (error) {
    console.error("Plan selection error:", error);
    $toast.error("Failed to select plan. Please try again.");
  }
};
</script>
