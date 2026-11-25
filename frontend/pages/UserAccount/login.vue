<!-- pages/UserAccount/login.vue -->
<template>
  <div class="lg-auth-root">
    <div class="lg-auth-split">
      <!-- ============================================================
           BRAND PANEL (Ink & Brass) — desktop storytelling / mobile intro
           ============================================================ -->
      <aside class="lg-ink-side ink-surface ink-grid">
        <div class="lg-ink-glow" aria-hidden="true"></div>

        <div class="lg-ink-inner">
          <!-- Brand row -->
          <div class="lg-brand-row">
            <NuxtLink to="/Homepage" class="lg-brand" aria-label="NFCGo home">
              <NFCGoLogo variant="dark" size="lg" />
            </NuxtLink>
            <NuxtLink to="/Homepage" class="lg-explore-pill">
              <Icon name="heroicons:arrow-left" class="h-4 w-4" />
              <span class="lg-explore-pill-text">Back to website</span>
            </NuxtLink>
          </div>

          <!-- Brand message -->
          <div class="lg-hero-copy">
            <span class="eyebrow-brass">
              <span class="dot"></span>
              {{ isAdminLogin ? 'Administration Console' : 'Smart NFC Business Card' }}
            </span>
            <h1 class="lg-hero-title">
              {{ isAdminLogin ? 'Manage your platform,' : 'Your digital identity,' }}
              <span class="brass-grad">{{ isAdminLogin ? 'admin access' : 'one tap away' }}</span>
            </h1>
            <p class="lg-hero-lead">
              {{ isAdminLogin ? 'Sign in to manage users, cards, invoices, and platform configuration.' : 'Sign in to manage your smart business cards and share your professional profile with a single tap.' }}
            </p>
          </div>

          <!-- NFC card mockup (desktop) -->
          <div class="lg-card-stage">
            <span class="tap-ripple lg-ripple" aria-hidden="true"></span>

            <div class="nfc-mock lg-mock">
              <div class="nfc-mock__face nfc-face-ink">
                <div class="nfc-mock__sheen"></div>
                <div class="relative z-10 flex items-start justify-between">
                  <div class="flex flex-col">
                    <span
                      class="text-2xl font-black tracking-tighter brass-grad leading-none"
                      >NFCGo</span
                    >
                    <span class="lg-mock-sub">SMART DIGITAL IDENTITY</span>
                  </div>
                  <Icon name="heroicons:wifi" class="h-7 w-7 text-white/60" />
                </div>
                <div class="nfc-mock__chip relative z-10"></div>
                <div class="relative z-10 flex justify-between items-end">
                  <div>
                    <h3 class="nfc-mock__name text-white">Your Name</h3>
                    <p class="nfc-mock__role text-white">Job Title</p>
                  </div>
                  <div class="nfc-mock__qr">
                    <Icon name="heroicons:qr-code" class="h-8 w-8" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Trust ticks -->
          <div class="lg-ticks">
            <span>
              <Icon name="heroicons:check-circle" class="lg-tick-ico" />
              No app required
            </span>
            <span>
              <Icon name="heroicons:check-circle" class="lg-tick-ico" />
              Instant setup
            </span>
            <span>
              <Icon name="heroicons:check-circle" class="lg-tick-ico" />
              Secure sign-in
            </span>
          </div>

          <!-- Explore CTA (desktop) -->
          <div v-if="!isAdminLogin" class="lg-explore">
            <span class="lg-explore-label">New to NFCGo?</span>
            <div class="lg-explore-links">
              <NuxtLink to="/UserAccount/register" class="btn-brass">
                <Icon name="heroicons:sparkles" class="h-4 w-4" />
                Create free account
              </NuxtLink>
              <NuxtLink to="/Homepage/pricing" class="btn-ghost-light">
                View pricing
              </NuxtLink>
              <NuxtLink to="/Homepage/how-it-works" class="lg-explore-text">
                How it works
              </NuxtLink>
            </div>
          </div>
        </div>
      </aside>

      <!-- ============================================================
           AUTH PANEL (Paper)
           ============================================================ -->
      <main class="lg-form-side">
        <div class="lg-form-wrap">
          <!-- Mobile brand row -->
          <NuxtLink to="/Homepage" class="lg-brand-mobile" aria-label="NFCGo home">
            <NFCGoLogo variant="light" size="md" />
          </NuxtLink>

          <div class="lg-card">
            <header class="lg-card-head">
              <span class="lg-kicker">{{ isAdminLogin ? 'Administration Portal' : 'Account access' }}</span>
              <h2 class="lg-title">{{ isAdminLogin ? 'Admin Sign In' : 'Welcome back' }}</h2>
              <p class="lg-subtitle">
                {{ isAdminLogin ? 'Sign in to access the NFCGo administration dashboard.' : 'Sign in to continue to your NFCGo dashboard.' }}
              </p>
            </header>

            <!-- Status banners -->
            <Transition
              enter-active-class="transition ease-out duration-200"
              enter-from-class="opacity-0 -translate-y-2"
              enter-to-class="opacity-100 translate-y-0"
            >
              <div
                v-if="emailVerifiedBanner"
                class="lg-banner lg-banner--success"
                role="status"
              >
                <Icon name="heroicons:check-circle" class="lg-banner-ico" />
                <div>
                  <h3 class="lg-banner-title">
                    {{
                      emailVerifiedBanner === 'new'
                        ? 'Email verified successfully'
                        : 'Email already verified'
                    }}
                  </h3>
                  <p class="lg-banner-text">
                    {{
                      emailVerifiedBanner === 'new'
                        ? 'Thanks for confirming your email. You can now sign in.'
                        : 'Your email was confirmed earlier. Please sign in.'
                    }}
                  </p>
                </div>
              </div>
              <div
                v-else-if="needsVerificationBanner"
                class="lg-banner lg-banner--warning"
                role="alert"
              >
                <Icon
                  name="heroicons:exclamation-triangle"
                  class="lg-banner-ico"
                />
                <div class="flex-1">
                  <h3 class="lg-banner-title">
                    Verify your email to unlock full access
                  </h3>
                  <p class="lg-banner-text">
                    We sent a confirmation link when you signed up. Please check
                    your inbox.
                  </p>
                  <NuxtLink
                    :to="`/UserAccount/verify-email${form.email ? '?email=' + encodeURIComponent(form.email) : ''}`"
                    class="lg-banner-link"
                  >
                    Resend link or troubleshoot
                    <Icon name="heroicons:arrow-right" class="h-4 w-4" />
                  </NuxtLink>
                </div>
              </div>
            </Transition>

            <!-- Login form -->
            <form class="lg-form" @submit.prevent="handleLogin">
              <div class="lg-field">
                <label for="email" class="lg-label">Email address</label>
                <div class="lg-control">
                  <Icon name="heroicons:envelope" class="lg-control-ico" />
                  <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    class="lg-input"
                    :class="errors.email ? 'lg-input--error' : ''"
                    placeholder="you@company.com"
                    :disabled="loading"
                    autocomplete="email"
                    inputmode="email"
                    :aria-invalid="!!errors.email"
                    :aria-describedby="errors.email ? 'email-error' : undefined"
                  />
                  <span
                    v-if="emailCheckLoading"
                    class="lg-control-trail"
                    aria-hidden="true"
                  >
                    <span class="lg-spin"></span>
                  </span>
                </div>
                <p v-if="errors.email" id="email-error" class="lg-error" role="alert">
                  <Icon name="heroicons:exclamation-circle" class="lg-error-ico" />
                  {{ errors.email[0] }}
                </p>
              </div>

              <div class="lg-field">
                <div class="lg-label-row">
                  <label for="password" class="lg-label">Password</label>
                  <NuxtLink to="/UserAccount/ForgotPass" class="lg-inline-link">
                    Forgot password?
                  </NuxtLink>
                </div>
                <div class="lg-control">
                  <Icon name="heroicons:lock-closed" class="lg-control-ico" />
                  <input
                    id="password"
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    required
                    class="lg-input lg-input--pw"
                    :class="errors.password ? 'lg-input--error' : ''"
                    placeholder="Enter your password"
                    :disabled="loading"
                    autocomplete="current-password"
                    :aria-invalid="!!errors.password"
                    :aria-describedby="errors.password ? 'password-error' : undefined"
                  />
                  <button
                    type="button"
                    class="lg-toggle"
                    :disabled="loading"
                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                    :aria-pressed="showPassword"
                    @click="showPassword = !showPassword"
                  >
                    <Icon
                      :name="showPassword ? 'heroicons:eye-slash' : 'heroicons:eye'"
                      class="h-5 w-5"
                    />
                  </button>
                </div>
                <p
                  v-if="errors.password"
                  id="password-error"
                  class="lg-error"
                  role="alert"
                >
                  <Icon name="heroicons:exclamation-circle" class="lg-error-ico" />
                  {{ errors.password[0] }}
                </p>
              </div>

              <label class="lg-remember" for="remember">
                <input
                  id="remember"
                  v-model="form.remember"
                  type="checkbox"
                  class="lg-checkbox"
                  :disabled="loading"
                />
                <span>Remember me for 7 days</span>
              </label>

              <button
                type="submit"
                :disabled="loading"
                :aria-busy="loading"
                class="btn-brass lg-submit"
              >
                <NFCGoWaveLoader
                  v-if="loading"
                  variant="dotPulse"
                  size="xs"
                  :showText="false"
                  inline
                />
                <Icon
                  v-else
                  name="heroicons:arrow-right-on-rectangle"
                  class="h-5 w-5"
                />
                <span>{{ loading ? 'Signing in…' : 'Sign in' }}</span>
              </button>
            </form>

            <!-- OAuth blocked warning -->
            <div v-if="oauthBlocked && form.email" class="lg-notice">
              <Icon name="heroicons:exclamation-triangle" class="lg-notice-ico" />
              <p>
                This email uses password login. Sign in with your password, or
                link Google from Account Settings after logging in.
              </p>
            </div>

            <!-- Social login -->
            <div v-if="!isAdminLogin" class="lg-divider"><span>or continue with</span></div>
            <div v-if="!isAdminLogin" class="lg-oauth">
              <button
                type="button"
                class="lg-oauth-btn"
                :disabled="loading || oauthBlocked"
                :title="oauthBlocked ? 'Use password login for this account' : 'Sign in with Google'"
                @click="handleGoogleLogin"
              >
                <Icon name="logos:google-icon" class="h-5 w-5" />
                <span>Google</span>
              </button>
              <button
                type="button"
                class="lg-oauth-btn"
                :disabled="loading || oauthBlocked"
                :title="oauthBlocked ? 'Use password login for this account' : 'Sign in with Apple'"
                @click="handleAppleLogin"
              >
                <Icon name="logos:apple" class="h-5 w-5" />
                <span>Apple</span>
              </button>
            </div>

            <p v-if="!isAdminLogin" class="lg-register">
              Don't have an account?
              <NuxtLink to="/UserAccount/register">Create one for free</NuxtLink>
            </p>
          </div>

          <!-- Trust strip -->
          <div class="lg-trust">
            <span>
              <Icon name="heroicons:shield-check" class="h-4 w-4" />
              SSL secured
            </span>
            <span>
              <Icon name="heroicons:lock-closed" class="h-4 w-4" />
              Privacy protected
            </span>
            <NuxtLink to="/Homepage/faq" class="lg-trust-link">
              <Icon name="heroicons:question-mark-circle" class="h-4 w-4" />
              Help
            </NuxtLink>
          </div>
        </div>
      </main>
    </div>

    <!-- 2FA Modal -->
    <div
      v-if="show2FA"
      class="lg-modal-overlay"
      @click.self="cancel2FA"
    >
      <div
        class="lg-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="lg-2fa-title"
      >
        <div class="lg-modal-head">
          <span class="lg-modal-mark">
            <Icon name="heroicons:shield-check" class="h-6 w-6" />
          </span>
          <h3 id="lg-2fa-title" class="lg-modal-title">Two-Factor Authentication</h3>
          <p class="lg-modal-text">
            Enter the 6-digit code from your authenticator app.
          </p>
        </div>

        <form class="lg-modal-form" @submit.prevent="handle2FA">
          <div class="lg-field">
            <label class="lg-label text-center block" for="otp">
              Verification code
            </label>
            <input
              id="otp"
              v-model="twoFactorCode"
              type="text"
              inputmode="numeric"
              maxlength="6"
              class="lg-otp"
              placeholder="000000"
              :disabled="loading"
              :aria-invalid="!!errors.code"
            />
            <p v-if="errors.code" class="lg-error justify-center" role="alert">
              <Icon name="heroicons:exclamation-circle" class="lg-error-ico" />
              {{ errors.code[0] }}
            </p>
          </div>

          <div class="lg-modal-actions">
            <button
              type="button"
              class="lg-oauth-btn"
              :disabled="loading"
              @click="cancel2FA"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="btn-brass lg-submit"
              :disabled="loading || twoFactorCode.length !== 6"
            >
              <span v-if="loading" class="lg-spin lg-spin--light"></span>
              {{ loading ? 'Verifying…' : 'Verify' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
// Apply middleware to block business plan requesters
definePageMeta({
  middleware: ['business-plan-guard']
})

// Route and router
const route = useRoute();
const router = useRouter();

// Detect admin login context (set by admin middleware when redirecting
// unauthenticated users from /AdminManagement routes)
const isAdminLogin = computed(() => route.query.admin === "1");

// Meta tags
useHead({
  title: computed(() => (isAdminLogin.value ? "Admin Login - NFCGo" : "Login - NFCGo")),
  meta: [
    {
      name: "description",
      content: computed(() =>
        isAdminLogin.value
          ? "Sign in to the NFCGo administration portal to manage users, cards, invoices, and platform settings."
          : "Sign in to your NFCGo account to manage your digital business cards."
      ),
    },
  ],
});

// Stores
const authStore = useAuthStore();
const { $toast, $api } = useNuxtApp();

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

// Verification status banners
const emailVerifiedBanner = computed(() => {
  if (route.query.email_verified === '1') return 'new';
  if (route.query.email_already_verified === '1') return 'already';
  return null;
});

// Show "verify your email" banner if user IS authenticated but email not verified yet
// (User came from register or tried login and backend attached needs_email_verification flag)
const needsVerificationBanner = computed(() => {
  if (!authStore.isAuthenticated) return false;
  if (emailVerifiedBanner.value) return false;
  const user = authStore.user;
  if (!user) return false;
  return !user.email_verified_at;
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

  // Safety check for $api
  const nuxtApp = useNuxtApp();
  const api = nuxtApp.$api;
  if (!api) {
    console.warn('API plugin not available for email check');
    return;
  }

  emailCheckLoading.value = true;
  try {
    const response = await api.get('/auth/check-email', { params: { email } });
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

    // Safety check for undefined response
    if (!response) {
      console.error("Login returned undefined response");
      if ($toast && typeof $toast.error === "function") {
        $toast.error("Login failed. Please try again.");
      }
      return;
    }

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

<style scoped src="../../assets/css/auth.css"></style>
