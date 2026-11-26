<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-secondary-100">
    <div class="text-center">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full shadow-lg mb-4">
        <div v-if="!error" class="animate-spin">
          <Icon name="heroicons:arrow-path" class="h-8 w-8 text-primary-600" />
        </div>
        <Icon v-else name="heroicons:x-circle" class="h-8 w-8 text-error-600" />
      </div>
      
      <h2 class="page-title text-secondary-900 mb-2">
        {{ error ? 'Authentication Failed' : 'Completing sign in...' }}
      </h2>
      
      <p v-if="error" class="text-secondary-600 mb-4">
        {{ errorMessage }}
      </p>
      
      <p v-else class="text-secondary-600">
        Please wait while we complete your authentication.
      </p>

      <button
        v-if="error"
        @click="goToLogin"
        class="mt-6 btn btn-primary"
      >
        Return to Login
      </button>
    </div>
  </div>
</template>

<script setup>
// Meta tags
useHead({
  title: 'Authenticating - NFCGo',
});

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const { $api, $toast } = useNuxtApp();

const error = ref(false);
const errorMessage = ref('');

onMounted(async () => {
  // Check for error parameter
  if (route.query.error) {
    error.value = true;
    
    switch (route.query.error) {
      case 'invalid_state':
        errorMessage.value = 'Invalid authentication state. Please try again.';
        break;
      case 'no_code':
        errorMessage.value = 'No authorization code received.';
        break;
      case 'auth_failed':
        if (route.query.message) {
          errorMessage.value = `Authentication failed: ${decodeURIComponent(route.query.message)}`;
        } else {
          errorMessage.value = 'Authentication failed. Please try again.';
        }
        break;
      default:
        errorMessage.value = 'An unknown error occurred.';
    }
    
    $toast.error(errorMessage.value);
    
    // Show a more helpful message for SSL errors
    if (errorMessage.value.includes('SSL') || errorMessage.value.includes('certificate')) {
      errorMessage.value += '\n\nThis is a common Windows development issue. Check the SSL_FIX.md file for solutions.';
    }
    
    return;
  }

  // The backend redirects here with a short-lived, single-use code.
  // The Sanctum token is never placed in the URL — it is fetched below and
  // stored in the auth-token cookie only.
  const code = route.query.code;

  if (!code) {
    error.value = true;
    errorMessage.value = 'No authentication code received.';
    $toast.error(errorMessage.value);
    return;
  }

  try {
    // Exchange the one-time code for a Sanctum token
    const response = await $api.post('/auth/oauth/exchange', { code });

    if (!response?.success || !response?.token) {
      throw new Error(response?.message || 'Failed to complete authentication.');
    }

    const tokenCookie = useCookie('auth-token', {
      httpOnly: false,
      secure: process.env.NODE_ENV === 'production',
      sameSite: 'lax',
      maxAge: 60 * 60 * 24 * 7, // 7 days
    });
    tokenCookie.value = response.token;

    // Set the token in the store directly
    authStore.token = response.token;

    // Longer delay to ensure cookie is written and readable by Vue's reactivity
    // This is critical for OAuth flow where the API plugin reads from the cookie
    await new Promise(resolve => setTimeout(resolve, 300));

    // Fetch user profile with the token
    await authStore.fetchProfile();

    if (!authStore.isAuthenticated) {
      throw new Error('Failed to complete authentication.');
    }

    const user = authStore.user;

    // Determine if user already has a valid plan/subscription (onboarding effectively done)
    const hasValidPlan =
      user.subscription_plan &&
      ["free", "basic", "premium", "business"].includes(
        String(user.subscription_plan).toLowerCase()
      );
    const alreadySetup = user.subscription_active === true || hasValidPlan;

    // User is considered truly new (needs onboarding) ONLY if marked new AND not already setup
    const isNewUserRequiringOnboarding = user.is_new_user === true && !alreadySetup;

    if (isNewUserRequiringOnboarding) {
      // Truly new user with no plan yet - redirect to Plan Selection
      $toast.success('Welcome to NFCGo! Let\'s choose your plan.');
      await router.push('/Onboarding/plan');
    } else {
      // Existing user or user already has valid plan/subscription - go to appropriate dashboard
      $toast.success('Successfully signed in!');
      await router.push(authStore.getRedirectPathByPlan());
    }
  } catch (err) {
    console.error('OAuth callback error:', err);
    error.value = true;
    errorMessage.value = err?.message || 'Failed to complete authentication. Please try again.';
    if ($toast && typeof $toast.error === 'function') {
      $toast.error(errorMessage.value);
    }
  }
});

const goToLogin = () => {
  router.push('/UserAccount/login');
};
</script>
