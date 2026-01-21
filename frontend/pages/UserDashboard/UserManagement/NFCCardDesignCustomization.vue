<!-- pages/onboarding/nfc-card-customization.vue -->
//for order new card page can use
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
            <span class="text-sm text-secondary-600">Step 2 of 3</span>
            <div class="flex space-x-1">
              <div class="w-2 h-2 bg-secondary-300 rounded-full"></div>
              <div class="w-2 h-2 bg-primary-600 rounded-full"></div>
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
          Customize Your NFC Card
        </h1>
        <p class="text-xl text-secondary-600 max-w-2xl mx-auto">
          Choose from our professional templates or upload your own design. Your
          card will be 54mm x 90mm (standard business card size).
        </p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Left Panel - Card Information -->
        <div class="space-y-8">
          <!-- Card Information Form -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-2xl font-bold text-secondary-900 mb-6">
              Card Information
            </h2>

            <form @submit.prevent="saveCardInfo" class="space-y-6">
              <!-- Basic Information -->
              <div class="space-y-4">
                <h3 class="text-lg font-semibold text-secondary-800">
                  Basic Information
                </h3>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label
                      class="block text-sm font-medium text-secondary-700 mb-1"
                      >Name *</label
                    >
                    <input
                      v-model="cardInfo.name"
                      type="text"
                      required
                      class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                      placeholder="Your full name"
                    />
                  </div>
                  <div>
                    <label
                      class="block text-sm font-medium text-secondary-700 mb-1"
                      >Position *</label
                    >
                    <input
                      v-model="cardInfo.position"
                      type="text"
                      required
                      class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                      placeholder="Job title"
                    />
                  </div>
                </div>

                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-1"
                    >Contact Number *</label
                  >
                  <input
                    v-model="cardInfo.contactNumber"
                    type="tel"
                    required
                    class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder="+60 12-345 6789"
                  />
                </div>

                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-1"
                    >Email Address *</label
                  >
                  <input
                    v-model="cardInfo.email"
                    type="email"
                    required
                    class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder="your@email.com"
                  />
                </div>

                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-1"
                    >Website</label
                  >
                  <input
                    v-model="cardInfo.website"
                    type="url"
                    class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder="https://yourwebsite.com"
                  />
                </div>

                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-1"
                    >Address *</label
                  >
                  <textarea
                    v-model="cardInfo.address"
                    rows="3"
                    required
                    class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder="Your business address"
                  ></textarea>
                </div>

                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-1"
                    >Company Logo (Optional)</label
                  >
                  <div
                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-secondary-300 border-dashed rounded-lg hover:border-secondary-400 transition-colors"
                  >
                    <div class="space-y-1 text-center">
                      <Icon
                        name="heroicons:building-office"
                        class="mx-auto h-12 w-12 text-secondary-400"
                      />
                      <div class="flex text-sm text-secondary-600">
                        <label
                          class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500"
                        >
                          <span>Upload a file</span>
                          <input
                            type="file"
                            class="sr-only"
                            accept="image/*"
                            @change="handleLogoUpload"
                          />
                        </label>
                        <p class="pl-1">or drag and drop</p>
                      </div>
                      <p class="text-xs text-secondary-500">
                        PNG, JPG, GIF up to 10MB
                      </p>
                    </div>
                  </div>
                  <div v-if="cardInfo.companyLogo" class="mt-2">
                    <img
                      :src="cardInfo.companyLogo"
                      alt="Company Logo"
                      class="h-12 w-auto"
                    />
                  </div>
                </div>
              </div>

              <!-- Premium Plan Additional Information -->
              <div v-if="isPremiumPlan" class="space-y-4">
                <h3 class="text-lg font-semibold text-secondary-800">
                  Back Side Information (Premium)
                </h3>

                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-1"
                    >Company Background</label
                  >
                  <textarea
                    v-model="cardInfo.companyBackground"
                    rows="3"
                    class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder="Brief description of your company"
                  ></textarea>
                </div>

                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-1"
                    >Services Offered</label
                  >
                  <textarea
                    v-model="cardInfo.services"
                    rows="3"
                    class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder="List your main services"
                  ></textarea>
                </div>

                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-1"
                    >Social Media Links</label
                  >
                  <div class="space-y-2">
                    <input
                      v-model="cardInfo.linkedin"
                      type="url"
                      class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                      placeholder="LinkedIn URL"
                    />
                    <input
                      v-model="cardInfo.twitter"
                      type="url"
                      class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                      placeholder="Twitter/X URL"
                    />
                    <input
                      v-model="cardInfo.facebook"
                      type="url"
                      class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                      placeholder="Facebook URL"
                    />
                    <input
                      v-model="cardInfo.instagram"
                      type="url"
                      class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                      placeholder="Instagram URL"
                    />
                  </div>
                </div>
              </div>

              <button
                type="submit"
                class="w-full py-3 px-4 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors"
              >
                Save Card Information
              </button>
            </form>
          </div>

          <!-- Card Examples -->
          <CardExamples
            :card-info="cardInfo"
            :selected-template="selectedTemplateData"
            :design-method="designMethod"
            :custom-design="cardDesign"
          />
        </div>

        <!-- Right Panel - Card Design -->
        <div class="space-y-8">
          <!-- Design Options -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-2xl font-bold text-secondary-900 mb-6">
              Card Design
            </h2>

            <!-- Design Method Selection -->
            <div class="mb-6">
              <h3 class="text-lg font-semibold text-secondary-800 mb-4">
                Choose Design Method
              </h3>
              <div class="grid grid-cols-2 gap-4">
                <button
                  @click="designMethod = 'template'"
                  :class="[
                    'p-4 rounded-lg border-2 transition-colors',
                    designMethod === 'template'
                      ? 'border-primary-500 bg-primary-50'
                      : 'border-secondary-200 hover:border-secondary-300',
                  ]"
                >
                  <Icon
                    name="heroicons:squares-2x2"
                    class="h-8 w-8 mx-auto mb-2 text-primary-600"
                  />
                  <p class="text-sm font-medium">Use Template</p>
                </button>
                <button
                  @click="designMethod = 'custom'"
                  :class="[
                    'p-4 rounded-lg border-2 transition-colors',
                    designMethod === 'custom'
                      ? 'border-primary-500 bg-primary-50'
                      : 'border-secondary-200 hover:border-secondary-300',
                  ]"
                >
                  <Icon
                    name="heroicons:arrow-up-tray"
                    class="h-8 w-8 mx-auto mb-2 text-primary-600"
                  />
                  <p class="text-sm font-medium">Upload Design</p>
                </button>
              </div>
            </div>

            <!-- Template Selection -->
            <div v-if="designMethod === 'template'" class="space-y-6">
              <h3 class="text-lg font-semibold text-secondary-800">
                Choose Template
              </h3>

              <!-- Loading State -->
              <div v-if="templatesLoading" class="flex justify-center py-8">
                <div
                  class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"
                ></div>
              </div>

              <!-- Error State -->
              <div v-else-if="templatesError" class="text-center py-8">
                <Icon
                  name="heroicons:exclamation-circle"
                  class="h-12 w-12 text-red-400 mx-auto mb-2"
                />
                <p class="text-sm text-red-600">{{ templatesError }}</p>
                <button
                  @click="fetchTemplates"
                  class="mt-2 text-sm text-primary-600 hover:underline"
                >
                  Try again
                </button>
              </div>

              <!-- Empty State -->
              <div
                v-else-if="!availableTemplates.length"
                class="text-center py-8"
              >
                <Icon
                  name="heroicons:photo"
                  class="h-12 w-12 text-secondary-300 mx-auto mb-2"
                />
                <p class="text-sm text-secondary-500">
                  No templates available for your {{ currentPlanName }} plan.
                </p>
                <p class="text-xs text-secondary-400 mt-1">
                  Contact support if you believe this is an error.
                </p>
              </div>

              <!-- Template Options -->
              <div v-else class="grid grid-cols-2 gap-4">
                <div
                  v-for="template in availableTemplates"
                  :key="template.id"
                  @click="selectTemplate(template)"
                  :class="[
                    'relative cursor-pointer rounded-lg border-2 p-4 transition-all',
                    selectedTemplate === template.id
                      ? 'border-primary-500 bg-primary-50'
                      : 'border-secondary-200 hover:border-secondary-300',
                  ]"
                >
                  <div
                    class="aspect-[90/54] rounded mb-2 relative overflow-hidden bg-secondary-100"
                  >
                    <!-- Template Image -->
                    <img
                      v-if="template.front_image_url"
                      :src="getTemplateImageUrl(template.front_image_url)"
                      :alt="template.name"
                      class="w-full h-full object-cover"
                    />
                    <!-- Fallback if no image -->
                    <div
                      v-else
                      class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-500 to-blue-700"
                    >
                      <Icon
                        name="heroicons:photo"
                        class="h-8 w-8 text-white/50"
                      />
                    </div>
                  </div>
                  <p class="text-sm font-medium text-center">
                    {{ template.name }}
                  </p>
                  <Icon
                    v-if="selectedTemplate === template.id"
                    name="heroicons:check"
                    class="absolute top-2 right-2 h-5 w-5 text-primary-500"
                  />
                </div>
              </div>
            </div>

            <!-- Custom Design Upload -->
            <div v-if="designMethod === 'custom'" class="space-y-6">
              <h3 class="text-lg font-semibold text-secondary-800">
                Upload Your Design
              </h3>

              <div class="space-y-4">
                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-2"
                    >Front Side *</label
                  >
                  <div
                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-secondary-300 border-dashed rounded-lg hover:border-secondary-400 transition-colors"
                  >
                    <div class="space-y-1 text-center">
                      <Icon
                        name="heroicons:document-arrow-up"
                        class="mx-auto h-12 w-12 text-secondary-400"
                      />
                      <div class="flex text-sm text-secondary-600">
                        <label
                          class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500"
                        >
                          <span>Upload front design</span>
                          <input
                            type="file"
                            class="sr-only"
                            accept="image/*"
                            @change="handleFrontUpload"
                          />
                        </label>
                      </div>
                      <p class="text-xs text-secondary-500">
                        PNG, JPG, PDF. Size: 90mm x 54mm
                      </p>
                    </div>
                  </div>
                  <div v-if="cardDesign.front" class="mt-2">
                    <img
                      :src="cardDesign.front"
                      alt="Front Design"
                      class="h-24 w-auto"
                    />
                  </div>
                </div>

                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-2"
                    >Back Side *</label
                  >
                  <div
                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-secondary-300 border-dashed rounded-lg hover:border-secondary-400 transition-colors"
                  >
                    <div class="space-y-1 text-center">
                      <Icon
                        name="heroicons:document-arrow-up"
                        class="mx-auto h-12 w-12 text-secondary-400"
                      />
                      <div class="flex text-sm text-secondary-600">
                        <label
                          class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500"
                        >
                          <span>Upload back design</span>
                          <input
                            type="file"
                            class="sr-only"
                            accept="image/*"
                            @change="handleBackUpload"
                          />
                        </label>
                      </div>
                      <p class="text-xs text-secondary-500">
                        PNG, JPG, PDF. Size: 90mm x 54mm
                      </p>
                    </div>
                  </div>
                  <div v-if="cardDesign.back" class="mt-2">
                    <img
                      :src="cardDesign.back"
                      alt="Back Design"
                      class="h-24 w-auto"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Card Preview -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-2xl font-bold text-secondary-900 mb-6">
              Card Preview
            </h2>

            <div class="flex justify-center">
              <div class="relative">
                <!-- Card Container -->
                <div class="relative" style="width: 180px; height: 108px">
                  <!-- Front Side -->
                  <div
                    class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-700 rounded-lg shadow-lg transform rotate-y-0 transition-transform duration-500"
                    :class="{ 'rotate-y-180': showBack }"
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

                  <!-- Back Side (Premium only) -->
                  <div
                    v-if="isPremiumPlan"
                    class="absolute inset-0 bg-gradient-to-br from-gray-800 to-gray-900 rounded-lg shadow-lg transform rotate-y-180 transition-transform duration-500"
                    :class="{ 'rotate-y-0': showBack }"
                  >
                    <div class="absolute inset-0 p-3 text-white text-xs">
                      <div class="h-full flex flex-col justify-between">
                        <div>
                          <div class="font-bold mb-1">Services</div>
                          <div class="opacity-80 text-xs">
                            {{ cardInfo.services || "Services" }}
                          </div>
                        </div>
                        <div class="text-center">
                          <div class="font-bold">Your Company</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Flip Button (Premium only) -->
                <button
                  v-if="isPremiumPlan"
                  @click="showBack = !showBack"
                  class="mt-4 w-full py-2 px-4 bg-secondary-100 text-secondary-700 rounded-lg text-sm font-medium hover:bg-secondary-200 transition-colors"
                >
                  {{ showBack ? "Show Front" : "Show Back" }}
                </button>
              </div>
            </div>

            <div class="mt-6 text-center">
              <p class="text-sm text-secondary-600">Card Size: 90mm x 54mm</p>
              <p class="text-xs text-secondary-500">
                Standard business card dimensions
              </p>
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
          @click="proceedToPayment"
          :disabled="!isFormValid"
          class="px-6 py-3 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Proceed to Payment
          <Icon name="heroicons:arrow-right" class="h-5 w-5 inline ml-2" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
// Meta tags
useHead({
  title: "Customize NFC Card - NFCGo",
  meta: [
    {
      name: "description",
      content:
        "Customize your NFC business card with professional templates or your own design.",
    },
  ],
});

// Stores
const authStore = useAuthStore();
const { $api, $toast } = useNuxtApp();
const config = useRuntimeConfig();

// Reactive data
const designMethod = ref("template");
const selectedTemplate = ref(null);
const selectedTemplateData = ref(null);
const showBack = ref(false);

// Template fetching state
const apiTemplates = ref([]);
const templatesLoading = ref(true);
const templatesError = ref(null);

// Card information
const cardInfo = reactive({
  name: "",
  position: "",
  contactNumber: "",
  email: "",
  website: "",
  address: "",
  companyLogo: null,
  // Premium plan fields
  companyBackground: "",
  services: "",
  linkedin: "",
  twitter: "",
  facebook: "",
  instagram: "",
});

// Card design
const cardDesign = reactive({
  front: null,
  back: null,
  frontFile: null,
  backFile: null,
});

// Available templates from API
const availableTemplates = computed(() => apiTemplates.value);

// Get template image URL
const getTemplateImageUrl = (url) => {
  if (!url) return "";
  if (url.startsWith("http")) return url;
  // Handle relative URLs
  const apiBase = config.public.apiBase || "";
  return `${apiBase.replace("/api", "")}${url}`;
};

// Select template
const selectTemplate = (template) => {
  selectedTemplate.value = template.id;
  selectedTemplateData.value = template;
};

// Get user's current plan (prioritize auth store, fallback to sessionStorage for onboarding)
const getCurrentUserPlan = () => {
  return (
    authStore.user?.subscription_plan ||
    sessionStorage.getItem("selectedPlan") ||
    "basic"
  );
};

// Fetch templates from API based on user's plan
const fetchTemplates = async () => {
  templatesLoading.value = true;
  templatesError.value = null;
  try {
    const userPlan = getCurrentUserPlan();
    const response = await $api.get(`/card-templates?plan=${userPlan}`);
    if (response.success) {
      apiTemplates.value = response.data;
      // Auto-select first template if available
      if (response.data.length > 0 && !selectedTemplate.value) {
        selectTemplate(response.data[0]);
      }
    } else {
      templatesError.value = response.message || "Failed to load templates";
    }
  } catch (error) {
    console.error("Failed to fetch templates:", error);
    templatesError.value = "Failed to load templates. Please try again.";
  } finally {
    templatesLoading.value = false;
  }
};

// Check if user has premium plan
const isPremiumPlan = computed(() => {
  const userPlan = getCurrentUserPlan();
  return ["premium", "business"].includes(userPlan);
});

// Get current plan name for display
const currentPlanName = computed(() => {
  const plan = getCurrentUserPlan();
  return plan.charAt(0).toUpperCase() + plan.slice(1);
});

// Form validation
const isFormValid = computed(() => {
  const basicFields =
    cardInfo.name &&
    cardInfo.position &&
    cardInfo.contactNumber &&
    cardInfo.email &&
    cardInfo.address;

  if (designMethod.value === "template") {
    return basicFields && selectedTemplate.value;
  } else {
    return basicFields && cardDesign.front && cardDesign.back;
  }
});

// Check if user is authenticated
onMounted(async () => {
  if (!authStore.isAuthenticated) {
    await navigateTo("/UserAccount/login");
    return;
  }

  // Load user data if available
  if (authStore.user) {
    cardInfo.name = authStore.user.full_name || "";
    cardInfo.email = authStore.user.email || "";
    cardInfo.position = authStore.user.job_title || "";
  }

  // Fetch templates from API
  await fetchTemplates();
});

// Watch for route changes to refetch templates on navigation
// This fixes the issue where templates don't load when navigating from another page
const route = useRoute();
watch(
  () => route.fullPath,
  async (newPath, oldPath) => {
    // Only refetch if we're navigating TO this page (not away from it)
    if (newPath && newPath !== oldPath && newPath.includes("NFCCardDesign")) {
      await fetchTemplates();
    }
  },
);

// Save card information
const saveCardInfo = async () => {
  try {
    // Store card info in session storage
    sessionStorage.setItem("cardInfo", JSON.stringify(cardInfo));
    sessionStorage.setItem("cardDesign", JSON.stringify(cardDesign));
    sessionStorage.setItem("selectedTemplate", selectedTemplate.value);
    sessionStorage.setItem("designMethod", designMethod.value);

    // Call backend API to save card information
    const { $api, $toast } = useNuxtApp();
    await $api.post("/onboarding/save-card-info", {
      name: cardInfo.name,
      position: cardInfo.position,
      contactNumber: cardInfo.contactNumber,
      email: cardInfo.email,
      website: cardInfo.website,
      address: cardInfo.address,
      companyLogo: cardInfo.companyLogo,
      companyBackground: cardInfo.companyBackground,
      services: cardInfo.services,
      linkedin: cardInfo.linkedin,
      twitter: cardInfo.twitter,
      facebook: cardInfo.facebook,
      instagram: cardInfo.instagram,
    });

    $toast.success("Card information saved successfully!");
  } catch (error) {
    console.error("Save card info error:", error);

    // Handle specific error cases
    const { $toast } = useNuxtApp();

    if (error.response?.status === 404) {
      // No active NFC card found
      $toast.error(
        "You don't have an active NFC card yet. Please purchase or activate a card first.",
      );
    } else if (error.response?.status === 422) {
      // Validation error
      const errors = error.response.data?.errors;
      if (errors) {
        const firstError = Object.values(errors)[0];
        $toast.error(Array.isArray(firstError) ? firstError[0] : firstError);
      } else {
        $toast.error("Validation failed. Please check your input.");
      }
    } else if (error.response?.status === 401) {
      // Not authenticated
      $toast.error("Session expired. Please login again.");
    } else {
      // Generic error
      const message =
        error.response?.data?.message ||
        "Failed to save card information. Please try again.";
      $toast.error(message);
    }
  }
};

// Handle logo upload
const handleLogoUpload = async (event) => {
  const file = event.target.files[0];
  if (file) {
    try {
      const formData = new FormData();
      formData.append("logo", file);

      const { $api } = useNuxtApp();
      const response = await $api.post("/upload/company-logo", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });

      if (response.success) {
        cardInfo.companyLogo = response.data.url;
        $toast.success("Company logo uploaded successfully!");
      }
    } catch (error) {
      console.error("Logo upload error:", error);
      $toast.error("Failed to upload logo. Please try again.");
    }
  }
};

// Handle front design upload
const handleFrontUpload = async (event) => {
  const file = event.target.files[0];
  if (file) {
    try {
      const reader = new FileReader();
      reader.onload = (e) => {
        cardDesign.front = e.target.result;
      };
      reader.readAsDataURL(file);

      // Store file for later upload
      cardDesign.frontFile = file;
    } catch (error) {
      console.error("Front design upload error:", error);
      $toast.error("Failed to upload front design. Please try again.");
    }
  }
};

// Handle back design upload
const handleBackUpload = async (event) => {
  const file = event.target.files[0];
  if (file) {
    try {
      const reader = new FileReader();
      reader.onload = (e) => {
        cardDesign.back = e.target.result;
      };
      reader.readAsDataURL(file);

      // Store file for later upload
      cardDesign.backFile = file;
    } catch (error) {
      console.error("Back design upload error:", error);
      $toast.error("Failed to upload back design. Please try again.");
    }
  }
};

// Upload card designs to backend
const uploadCardDesigns = async () => {
  if (
    designMethod.value === "custom" &&
    cardDesign.frontFile &&
    cardDesign.backFile
  ) {
    try {
      const formData = new FormData();
      formData.append("front_design", cardDesign.frontFile);
      formData.append("back_design", cardDesign.backFile);
      formData.append("design_method", designMethod.value);
      formData.append("selected_template", selectedTemplate.value);

      const { $api } = useNuxtApp();
      const response = await $api.post(
        "/onboarding/upload-card-design",
        formData,
        {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        },
      );

      if (response.success) {
        $toast.success("Card designs uploaded successfully!");
        return true;
      }
    } catch (error) {
      console.error("Card design upload error:", error);
      $toast.error("Failed to upload card designs. Please try again.");
      return false;
    }
  }
  return true; // For template method, no upload needed
};

// Go back to plan selection
const goBack = () => {
  navigateTo("/UserDashboard/PlanSelection");
};

// Proceed to payment
const proceedToPayment = async () => {
  // Save current data
  await saveCardInfo();

  // Upload designs if custom method
  const uploadSuccess = await uploadCardDesigns();

  if (uploadSuccess) {
    // Do NOT complete onboarding here; defer until payment success.
    $toast.success("Card customization saved! Continue to payment.");
    await navigateTo("/UserDashboard/Payment");
  }
};
</script>

<style scoped>
.rotate-y-0 {
  transform: rotateY(0deg);
}

.rotate-y-180 {
  transform: rotateY(180deg);
}
</style>
