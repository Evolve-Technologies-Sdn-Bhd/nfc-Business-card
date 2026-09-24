<!-- pages/UserAccount/verify-email.vue -->
<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-secondary-100 px-4 sm:px-6 lg:px-8"
  >
    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div class="text-center">
        <NuxtLink to="/" class="inline-flex items-center">
          <Icon name="heroicons:identification" class="h-12 w-12 text-primary-600" />
          <span class="ml-3 text-2xl font-bold text-secondary-900">NFCGo</span>
        </NuxtLink>
        <div class="mt-6 relative">
          <NuxtLink
            to="/UserAccount/login"
            class="absolute -left-4 top-1/2 -translate-y-1/2 inline-flex items-center px-3 py-2 text-sm border border-secondary-300 rounded-lg text-secondary-600 hover:text-primary-600 hover:border-primary-600 transition-colors bg-white"
          >
            <Icon name="heroicons:arrow-left" class="h-4 w-4 mr-1" />
            Back
          </NuxtLink>
          <h2 class="text-3xl font-bold text-secondary-900">Verify your email</h2>
        </div>
      </div>

      <div class="card p-8 space-y-6">
        <!-- Status banners from URL params -->
        <div
          v-if="status === 'verified'"
          class="flex items-start gap-3 p-4 rounded-xl bg-success-50 border border-success-200 text-success-800"
          role="status"
        >
          <Icon name="heroicons:check-circle" class="w-6 h-6 flex-shrink-0" />
          <div>
            <h3 class="font-semibold">Email verified successfully</h3>
            <p class="text-sm mt-0.5">You can now sign in to your account.</p>
          </div>
        </div>
        <div
          v-else-if="status === 'already_verified'"
          class="flex items-start gap-3 p-4 rounded-xl bg-info-50 border border-info-200 text-info-800"
          role="status"
        >
          <Icon name="heroicons:information-circle" class="w-6 h-6 flex-shrink-0" />
          <div>
            <h3 class="font-semibold">Email already verified</h3>
            <p class="text-sm mt-0.5">Your email was already confirmed. Please sign in.</p>
          </div>
        </div>
        <div
          v-else-if="status === 'invalid_signature'"
          class="flex items-start gap-3 p-4 rounded-xl bg-error-50 border border-error-200 text-error-800"
          role="alert"
        >
          <Icon name="heroicons:x-circle" class="w-6 h-6 flex-shrink-0" />
          <div>
            <h3 class="font-semibold">Invalid or expired link</h3>
            <p class="text-sm mt-0.5">Request a new verification link using the form below.</p>
          </div>
        </div>

        <!-- Instruction card -->
        <div
          v-if="status !== 'verified' && status !== 'already_verified'"
          class="p-5 rounded-xl bg-secondary-50 border border-secondary-200 space-y-3"
        >
          <div class="flex items-start gap-3">
            <div
              class="w-10 h-10 flex items-center justify-center rounded-full bg-white shadow-sm border border-secondary-200 flex-shrink-0"
            >
              <Icon name="heroicons:envelope" class="w-5 h-5 text-primary-600" />
            </div>
            <div class="space-y-1">
              <h3 class="font-semibold text-secondary-900">Check your inbox</h3>
              <p class="text-sm text-secondary-600">
                We sent a verification link to
                <span v-if="authenticatedUser?.email" class="font-medium text-secondary-900">
                  {{ authenticatedUser.email }}
                </span>
                <span v-else class="font-medium text-secondary-900">your registered email</span>.
                Click the link to confirm ownership of this email address.
              </p>
              <p class="text-xs text-secondary-500">
                Can't find the email? Check your spam or promotions folder, or request a new link below.
              </p>
            </div>
          </div>
        </div>

        <!-- Resend form -->
        <form
          v-if="status !== 'verified' && status !== 'already_verified'"
          @submit.prevent="handleResend"
          class="space-y-5"
        >
          <!-- Email field only if user is NOT authenticated in SPA -->
          <div v-if="!isAuthenticated" class="form-group">
            <label for="resend_email" class="form-label">Registered email address</label>
            <input
              id="resend_email"
              v-model="form.email"
              type="email"
              required
              :class="['input', errorMessage ? 'input-error' : '']"
              placeholder="john@example.com"
              :disabled="resendLoading || countdown > 0"
            />
          </div>

          <button
            type="submit"
            :disabled="resendLoading || countdown > 0 || (!isAuthenticated && !form.email)"
            class="btn btn-primary w-full flex items-center justify-center"
          >
            <div v-if="resendLoading" class="spinner mr-2"></div>
            <Icon v-else name="heroicons:paper-airplane" class="h-5 w-5 mr-2" />
            <span v-if="countdown > 0">Resend available in {{ countdown }}s</span>
            <span v-else>{{ isAuthenticated ? 'Resend verification link' : 'Send verification link' }}</span>
          </button>

          <p v-if="successMessage" role="status" class="text-sm text-success-700 font-medium text-center">
            {{ successMessage }}
          </p>
          <p v-else-if="errorMessage" role="alert" class="text-sm text-error-700 font-medium text-center">
            {{ errorMessage }}
          </p>
          <p v-else-if="retryAfter" class="text-xs text-warning-700 text-center">
            Please wait {{ retryAfter }} seconds before retrying.
          </p>
        </form>

        <!-- Navigation -->
        <div class="pt-2 border-t border-secondary-200 text-sm text-center space-y-2 text-secondary-600">
          <NuxtLink
            v-if="isAuthenticated"
            to="/UserDashboard/CardManagement"
            class="text-secondary-500 hover:text-primary-600 font-medium"
          >
            Go to dashboard anyway →
          </NuxtLink>
          <div v-if="!isAuthenticated">
            Remembered your password?
            <NuxtLink
              to="/UserAccount/login"
              class="font-medium text-primary-600 hover:text-primary-500"
            >
              Sign in
            </NuxtLink>
          </div>
          <div v-if="isAuthenticated">
            Want to use a different account?
            <button
              @click="handleLogout"
              :disabled="logoutLoading"
              class="font-medium text-primary-600 hover:text-primary-500"
            >
              Sign out
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  middleware: ['business-plan-guard'],
});

useHead({
  title: 'Verify Email - NFCGo',
  meta: [
    {
      name: 'description',
      content: 'Verify your NFCGo account email address to unlock full access.',
    },
  ],
});

const authStore = useAuthStore();
const { $api, $toast } = useNuxtApp();
const route = useRoute();
const router = useRouter();

// Status from URL query params
const status = computed(() => {
  if (route.query.email_verified === '1') return 'verified';
  if (route.query.email_already_verified === '1') return 'already_verified';
  if (route.query.error === 'invalid_signature') return 'invalid_signature';
  return null;
});

// Auth context
const isAuthenticated = computed(() => authStore.isAuthenticated);
const authenticatedUser = computed(() => authStore.user);

// Resend form state
const resendLoading = ref(false);
const logoutLoading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const retryAfter = ref(0);
const countdown = ref(0);

const form = reactive({
  email: '',
});

// Prefill email from logged-in user (if any)
watch(
  () => authenticatedUser.value?.email,
  (email) => {
    if (email && !form.email) {
      form.email = email;
    }
  },
  { immediate: true }
);

// Countdown timer helper
const startCountdown = (seconds) => {
  countdown.value = seconds;
  const tick = setInterval(() => {
    countdown.value -= 1;
    if (countdown.value <= 0) {
      clearInterval(tick);
    }
  }, 1000);
};

const handleResend = async () => {
  successMessage.value = '';
  errorMessage.value = '';
  retryAfter.value = 0;
  resendLoading.value = true;

  try {
    const payload = isAuthenticated.value ? {} : { email: form.email };
    const response = await $api.post('/email/resend', payload);

    if (response.data?.success) {
      if (response.data?.already_verified) {
        successMessage.value = 'Your email is already verified.';
        setTimeout(() => {
          router.push('/UserAccount/login');
        }, 1500);
      } else {
        successMessage.value = response.data.message || 'Verification link sent.';
        startCountdown(60);
      }
    } else {
      errorMessage.value = response.data?.message || 'Failed to send verification email.';
      if (response.data?.retry_after) {
        retryAfter.value = response.data.retry_after;
      }
    }
  } catch (e) {
    const data = e?.response?.data;
    if (data?.retry_after) {
      retryAfter.value = data.retry_after;
    }
    errorMessage.value =
      data?.message || 'Unable to resend verification link. Please try again shortly.';
  } finally {
    resendLoading.value = false;
  }
};

const handleLogout = async () => {
  logoutLoading.value = true;
  try {
    await authStore.logout();
    // After logout, stay on page but form now shows email input
  } finally {
    logoutLoading.value = false;
  }
};
</script>
