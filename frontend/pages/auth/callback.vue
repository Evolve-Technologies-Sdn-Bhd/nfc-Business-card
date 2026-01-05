<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-secondary-100">
    <div class="text-center">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full shadow-lg mb-4">
        <div v-if="!error" class="animate-spin">
          <Icon name="heroicons:arrow-path" class="h-8 w-8 text-primary-600" />
        </div>
        <Icon v-else name="heroicons:x-circle" class="h-8 w-8 text-error-600" />
      </div>
      
      <h2 class="text-2xl font-bold text-secondary-900 mb-2">
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
const { $toast } = useNuxtApp();

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

  // Check for successful authentication (token in query or cookie)
  const token = route.query.token || useCookie('auth-token').value;
  
  console.log('🔵 Callback page loaded');
  console.log('📝 Token from URL:', route.query.token);
  console.log('🍪 Token from cookie:', useCookie('auth-token').value);
  
  if (token) {
    try {
      // Save token to cookie if it's in the query
      if (route.query.token) {
        console.log('💾 Saving token to cookie...');
        const tokenCookie = useCookie('auth-token', {
          httpOnly: false,
          secure: process.env.NODE_ENV === 'production',
          sameSite: 'lax',
          maxAge: 60 * 60 * 24 * 7, // 7 days
        });
        tokenCookie.value = token;
        console.log('✅ Token saved to cookie');
        
        // Set the token in the store directly
        authStore.token = token;
        console.log('✅ Token set in store');
        
        // Longer delay to ensure cookie is written and readable by Vue's reactivity
        // This is critical for OAuth flow where the API plugin reads from the cookie
        await new Promise(resolve => setTimeout(resolve, 300));
        
        // Verify the cookie was set correctly
        const savedToken = useCookie('auth-token').value;
        console.log('🔍 Verifying saved token:', savedToken ? 'Token exists' : 'Token missing');
        
        if (!savedToken) {
          console.error('❌ Cookie was not saved correctly, retrying...');
          const tokenCookieRetry = useCookie('auth-token', {
            httpOnly: false,
            secure: process.env.NODE_ENV === 'production',
            sameSite: 'lax',
            maxAge: 60 * 60 * 24 * 7,
          });
          tokenCookieRetry.value = token;
          await new Promise(resolve => setTimeout(resolve, 200));
        }
      }
      
      // Fetch user profile with the token
      console.log('🔄 Fetching user profile...');
      await authStore.fetchProfile();
      
      console.log('📊 Auth state:', {
        isAuthenticated: authStore.isAuthenticated,
        user: authStore.user
      });
      
      if (authStore.isAuthenticated) {
        const user = authStore.user;
        
        console.log('👤 User data:', user);
        console.log('📦 Subscription plan:', user.subscription_plan);
        console.log('🆕 Is new user from backend:', user.is_new_user);
        console.log('🆕 Is new user from URL:', route.query.is_new_user);
        console.log('📅 Created at:', user.created_at);
        
        // Check if user is marked as new from backend OR URL parameter
        const isNewUserFromBackend = user.is_new_user === true;
        const isNewUserFromUrl = route.query.is_new_user === 'true';
        
        // User is new if either flag is true
        const isNewUser = isNewUserFromBackend || isNewUserFromUrl;
        
        console.log('🔍 Final is new user decision:', isNewUser);
        
        if (isNewUser) {
          // New user - redirect to Plan Selection
          console.log('➡️ Redirecting to Plan Selection...');
          $toast.success('Welcome to NFCGo! Let\'s choose your plan.');
          await router.push('/UserDashboard/PlanSelection');
        } else {
          // Existing user - redirect to CardManagement
          console.log('➡️ Redirecting to CardManagement...');
          $toast.success('Successfully signed in!');
          await router.push('/UserDashboard/CardManagement');
        }
      } else {
        throw new Error('Failed to authenticate');
      }
    } catch (err) {
      console.error('OAuth callback error:', err);
      error.value = true;
      errorMessage.value = 'Failed to complete authentication. Please try again.';
      if ($toast && typeof $toast.error === 'function') {
        $toast.error(errorMessage.value);
      }
    }
  } else {
    // No token found, something went wrong
    error.value = true;
    errorMessage.value = 'No authentication token received.';
    $toast.error(errorMessage.value);
  }
});

const goToLogin = () => {
  router.push('/UserAccount/login');
};
</script>
