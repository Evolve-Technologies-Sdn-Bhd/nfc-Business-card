<!-- pages/UserAccount/login.vue -->
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
        <div class="mt-6 relative">
          <NuxtLink
            to="/"
            class="absolute left-0 top-1/2 -translate-y-1/2 inline-flex items-center px-3 py-2 text-sm border border-secondary-300 rounded-lg text-secondary-600 hover:text-primary-600 hover:border-primary-600 transition-colors bg-white"
          >
            <Icon name="heroicons:arrow-left" class="h-4 w-4 mr-1" />
            Back
          </NuxtLink>
          <h2 class="text-3xl font-bold text-secondary-900">Welcome back</h2>
        </div>
        <p class="mt-2 text-sm text-secondary-600">
          Don't have an account?
          <NuxtLink
            to="/UserAccount/register"
            class="font-medium text-primary-600 hover:text-primary-500"
          >
            Sign up for free
          </NuxtLink>
        </p>
      </div>

      <!-- Login Form -->
      <div class="card p-8">
        <form @submit.prevent="handleLogin" class="space-y-6">
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

          <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                :class="['input pr-10', errors.password ? 'input-error' : '']"
                placeholder="Enter your password"
                :disabled="loading"
                autocomplete="current-password"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                :disabled="loading"
              >
                <Icon
                  :name="showPassword ? 'heroicons:eye-slash' : 'heroicons:eye'"
                  class="h-5 w-5 text-secondary-400"
                />
              </button>
            </div>
            <p v-if="errors.password" class="form-error">
              {{ errors.password[0] }}
            </p>
          </div>

          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input
                id="remember"
                v-model="form.remember"
                type="checkbox"
                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-secondary-300 rounded"
                :disabled="loading"
              />
              <label
                for="remember"
                class="ml-2 block text-sm text-secondary-700"
              >
                Remember me
              </label>
            </div>
            <NuxtLink
              to="/UserAccount/ForgotPass"
              class="text-sm text-primary-600 hover:text-primary-500 font-medium"
            >
              Forgot password?
            </NuxtLink>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="btn btn-primary w-full"
          >
            <div v-if="loading" class="spinner mr-2"></div>
            <Icon
              v-else
              name="heroicons:arrow-right-on-rectangle"
              class="h-5 w-5 mr-2"
            />
            {{ loading ? "Signing in..." : "Sign in" }}
          </button>
        </form>

        <!-- Divider -->
        <div class="mt-6">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-secondary-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-2 bg-white text-secondary-500"
                >Or continue with</span
              >
            </div>
          </div>
        </div>

        <!-- OAuth Blocked Warning -->
        <div v-if="oauthBlocked && form.email" class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
          <div class="flex items-start">
            <Icon name="heroicons:exclamation-triangle" class="h-5 w-5 text-amber-500 mt-0.5 mr-2 flex-shrink-0" />
            <p class="text-sm text-amber-700">
              This email uses password login. Sign in with your password, or link Google from Account Settings after logging in.
            </p>
          </div>
        </div>

        <!-- Social Login -->
        <div class="mt-6 grid grid-cols-2 gap-3">
          <div class="relative group">
            <button
              @click="handleGoogleLogin"
              :disabled="loading || oauthBlocked"
              :class="[
                'btn btn-outline w-full',
                oauthBlocked ? 'opacity-50 cursor-not-allowed' : ''
              ]"
            >
              <Icon name="logos:google-icon" class="h-5 w-5 mr-2" />
              Google
            </button>
            <!-- Tooltip for blocked OAuth -->
            <div v-if="oauthBlocked" class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 bg-secondary-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
              Use password login for this account
              <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-secondary-900"></div>
            </div>
          </div>
          <div class="relative group">
            <button
              @click="handleAppleLogin"
              :disabled="loading || oauthBlocked"
              :class="[
                'btn btn-outline w-full',
                oauthBlocked ? 'opacity-50 cursor-not-allowed' : ''
              ]"
            >
              <Icon name="logos:apple" class="h-5 w-5 mr-2" />
              Apple
            </button>
            <!-- Tooltip for blocked OAuth -->
            <div v-if="oauthBlocked" class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-2 bg-secondary-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
              Use password login for this account
              <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-secondary-900"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- 2FA Modal -->
      <div
        v-if="show2FA"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
      >
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4">
          <div class="text-center mb-6">
            <div
              class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4"
            >
              <Icon
                name="heroicons:shield-check"
                class="h-8 w-8 text-primary-600"
              />
            </div>
            <h3 class="text-2xl font-bold text-secondary-900 mb-2">
              Two-Factor Authentication
            </h3>
            <p class="text-secondary-600">
              Enter the 6-digit code from your authenticator app
            </p>
          </div>

          <form @submit.prevent="handle2FA" class="space-y-6">
            <div class="form-group">
              <label class="form-label text-center block"
                >Verification Code</label
              >
              <div class="flex justify-center">
                <input
                  v-model="twoFactorCode"
                  type="text"
                  maxlength="6"
                  class="w-48 h-12 text-center text-2xl tracking-widest border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  placeholder="000000"
                  :disabled="loading"
                />
              </div>
              <p v-if="errors.code" class="form-error text-center">
                {{ errors.code[0] }}
              </p>
            </div>

            <div class="flex space-x-3">
              <button
                type="button"
                @click="cancel2FA"
                class="btn btn-secondary flex-1"
                :disabled="loading"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="btn btn-primary flex-1"
                :disabled="loading || twoFactorCode.length !== 6"
              >
                <div v-if="loading" class="spinner mr-2"></div>
                {{ loading ? "Verifying..." : "Verify" }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Meta tags
useHead({
  title: "Login - NFCGo",
  meta: [
    {
      name: "description",
      content:
        "Sign in to your NFCGo account to manage your digital business cards.",
    },
  ],
});

// Stores
const authStore = useAuthStore();
const { $toast, $api } = useNuxtApp();

// Route and router
const route = useRoute();
const router = useRouter();

// Reactive data
const loading = ref(false);
const showPassword = ref(false);
const show2FA = ref(false);
const twoFactorCode = ref("");
const errors = ref({});
const emailCheckLoading = ref(false);
const emailHasPassword = ref(false);
const oauthBlocked = ref(false);
const oauthBlockedMessage = ref("");

const form = reactive({
  email: "",
  password: "",
  remember: false,
});

// Debounce timer for email check
let emailCheckTimer = null;

// Check email authentication method when user types
const checkEmailAuthMethod = async (email) => {
  if (!email || !email.includes('@')) {
    emailHasPassword.value = false;
    oauthBlocked.value = false;
    return;
  }

  emailCheckLoading.value = true;
  try {
    const response = await $api.get('/auth/check-email', { params: { email } });
    if (response.success && response.exists) {
      emailHasPassword.value = response.has_password;
      oauthBlocked.value = response.oauth_blocked || false;
    } else {
      emailHasPassword.value = false;
      oauthBlocked.value = false;
    }
  } catch (error) {
    console.error('Email check error:', error);
    // On error, don't block OAuth (fail open for UX)
    emailHasPassword.value = false;
    oauthBlocked.value = false;
  } finally {
    emailCheckLoading.value = false;
  }
};

// Watch email input with debounce
watch(() => form.email, (newEmail) => {
  if (emailCheckTimer) clearTimeout(emailCheckTimer);
  emailCheckTimer = setTimeout(() => {
    checkEmailAuthMethod(newEmail);
  }, 500);
});

// Check for OAuth blocked redirect on mount
onMounted(() => {
  // Check for OAuth blocked error from redirect
  if (route.query.error === 'oauth_blocked' && route.query.message) {
    oauthBlockedMessage.value = decodeURIComponent(route.query.message);
    $toast.warning(oauthBlockedMessage.value, { duration: 8000 });
    
    // Clean up URL
    router.replace({ query: {} });
  }

  if (authStore.isAuthenticated) {
    const redirect = route.query.redirect || authStore.getRedirectPathByPlan();
    router.push(redirect);
  }
});

// Handle login
const handleLogin = async () => {
  loading.value = true;
  errors.value = {};

  try {
    console.log("Attempting login with:", {
      email: form.email,
      password: "***",
    });
    const response = await authStore.login(form);
    console.log("Login response:", response);

    if (response.requires_2fa) {
      show2FA.value = true;
    } else {
      $toast.success("Welcome back!");

      let redirectPath = route.query.redirect;

      if (!redirectPath) {
        // Get redirect path based on user's plan
        redirectPath = authStore.getRedirectPathByPlan();
        console.log(
          "User plan:",
          authStore.user?.subscription_plan || authStore.user?.plan
        );
        console.log("Redirecting to:", redirectPath);
      }

      console.log("Redirecting to:", redirectPath);
      // Use window.location for reliable navigation
      window.location.href = redirectPath;
    }
  } catch (error) {
    console.error("Login error details:", error);
    console.error("Error message:", error.message);
    console.error("Error stack:", error.stack);

    if (error.response?.status === 422) {
      errors.value = error.validationErrors || {};
    } else if (error.response?.status === 401) {
      if ($toast && typeof $toast.error === "function") {
        $toast.error("Invalid email or password");
      } else {
        console.error("Invalid email or password");
      }
    } else {
      if ($toast && typeof $toast.error === "function") {
        $toast.error("An error occurred. Please try again.");
      } else {
        console.error("An error occurred. Please try again.");
      }
    }
  } finally {
    loading.value = false;
  }
};

// Handle 2FA verification
const handle2FA = async () => {
  loading.value = true;
  errors.value = {};

  try {
    await authStore.verify2FA(twoFactorCode.value);
    $toast.success("Welcome back!");

    let redirectPath = route.query.redirect;

    if (!redirectPath) {
      // Get redirect path based on user's plan
      redirectPath = authStore.getRedirectPathByPlan();
      console.log(
        "User plan (2FA):",
        authStore.user?.subscription_plan || authStore.user?.plan
      );
      console.log("Redirecting to:", redirectPath);
    }

    // Use window.location for reliable navigation
    window.location.href = redirectPath;
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.validationErrors || {};
    } else {
      $toast.error("Invalid verification code");
    }
  } finally {
    loading.value = false;
  }
};

// Cancel 2FA
const cancel2FA = () => {
  show2FA.value = false;
  twoFactorCode.value = "";
  errors.value = {};
};

// Handle Google login
const handleGoogleLogin = () => {
  try {
    const config = useRuntimeConfig();
    const apiBaseUrl = config.public.apiBaseUrl;
    const oauthUrl = `${apiBaseUrl}/auth/google/redirect`;

    console.log("🔵 Google Login Clicked");
    console.log("📍 API Base URL:", apiBaseUrl);
    console.log("🔗 OAuth URL:", oauthUrl);
    console.log("🚀 Navigating now...");

    // Immediate navigation to backend OAuth endpoint
    // Backend will redirect to Google's sign-in page
    window.location.href = oauthUrl;
  } catch (error) {
    console.error("❌ Error in handleGoogleLogin:", error);
    alert("Error: " + error.message);
  }
};

// Handle Apple login
const handleAppleLogin = () => {
  const config = useRuntimeConfig();
  const apiBaseUrl = config.public.apiBaseUrl;

  // Immediate navigation to backend OAuth endpoint
  // Backend will redirect to Apple's sign-in page
  window.location.href = `${apiBaseUrl}/auth/apple/redirect`;
};

// Redirect if already authenticated
watch(
  () => authStore.isAuthenticated,
  (isAuth) => {
    if (isAuth) {
      const redirect =
        route.query.redirect || authStore.getRedirectPathByPlan();
      router.push(redirect);
    }
  }
);
</script>
