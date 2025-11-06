<!-- pages/UserAccount/forgot-password.vue -->
<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-secondary-100 px-4 sm:px-6 lg:px-8"
  >
    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div class="text-center">
        <NuxtLink to="/" class="inline-flex items-center">
          <Icon
            name="heroicons:identification"
            class="h-12 w-12 text-primary-600"
          />
          <span class="ml-3 text-2xl font-bold text-secondary-900">NFCGo</span>
        </NuxtLink>
        <h2 class="mt-6 text-3xl font-bold text-secondary-900">
          Reset your password
        </h2>
        <p class="mt-2 text-sm text-secondary-600">
          Enter your email and we'll send you instructions to reset your
          password.
        </p>
      </div>

      <!-- Success Message -->
      <div v-if="emailSent" class="card p-8">
        <div class="text-center">
          <div
            class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4"
          >
            <Icon
              name="heroicons:check-circle"
              class="h-10 w-10 text-green-600"
            />
          </div>
          <h3 class="text-xl font-semibold text-secondary-900 mb-2">
            Check your email
          </h3>
          <p class="text-secondary-600 mb-6">
            If an account exists for <strong>{{ form.email }}</strong
            >, you'll receive password reset instructions shortly.
          </p>
          <div class="space-y-3">
            <button @click="resetForm" class="btn btn-outline w-full">
              Send another email
            </button>
            <NuxtLink to="/UserAccount/login" class="btn btn-primary w-full">
              Back to login
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- Reset Form -->
      <div v-else class="card p-8">
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div class="form-group">
            <label for="email" class="form-label">Email address</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              :class="['input', errors.email ? 'input-error' : '']"
              placeholder="Enter your email"
              :disabled="loading"
              autocomplete="email"
            />
            <p v-if="errors.email" class="form-error">{{ errors.email[0] }}</p>
          </div>

          <button
            type="submit"
            :disabled="loading || !form.email"
            class="btn btn-primary w-full"
          >
            <div v-if="loading" class="spinner mr-2"></div>
            <Icon v-else name="heroicons:paper-airplane" class="h-5 w-5 mr-2" />
            {{ loading ? "Sending..." : "Send reset instructions" }}
          </button>

          <div class="text-center">
            <NuxtLink
              to="/UserAccount/login"
              class="text-sm text-primary-600 hover:text-primary-500"
            >
              <Icon name="heroicons:arrow-left" class="h-4 w-4 inline mr-1" />
              Back to login
            </NuxtLink>
          </div>
        </form>
      </div>

      <!-- Help Text -->
      <div class="text-center text-sm text-secondary-600">
        <p>
          Having trouble?
          <a
            href="mailto:support@nfcgo.com"
            class="text-primary-600 hover:text-primary-500"
            >Contact support</a
          >
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
useHead({
  title: "Forgot Password - NFCGo",
  meta: [
    { name: "description", content: "Reset your NFCGo account password." },
  ],
});

const authStore = useAuthStore();
const { $toast } = useNuxtApp();
const router = useRouter();

const loading = ref(false);
const emailSent = ref(false);
const errors = ref({});

const form = reactive({
  email: "",
});

// Redirect if already authenticated
onMounted(() => {
  if (authStore.isAuthenticated) {
    router.push("/UserDashboard");
  }
});

const handleSubmit = async () => {
  loading.value = true;
  errors.value = {};

  try {
    await authStore.requestPasswordReset(form.email);
    emailSent.value = true;
    // Don't show different messages for existing/non-existing emails (security)
  } catch (error) {
    if (error.response?.status === 429) {
      $toast.error("Too many requests. Please wait before trying again.");
      errors.value = {
        email: ["Too many reset attempts. Please try again later."],
      };
    } else if (error.response?.status === 422) {
      errors.value = error.validationErrors || {};
    } else {
      $toast.error("An error occurred. Please try again.");
    }
  } finally {
    loading.value = false;
  }
};

const resetForm = () => {
  emailSent.value = false;
  form.email = "";
  errors.value = {};
};
</script>
