<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-secondary-100">
        <div class="card p-8 text-center max-w-md">
            <div v-if="loading" class="space-y-4">
                <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto">
                    <div class="spinner"></div>
                </div>
                <h2 class="text-2xl font-bold text-secondary-900">Completing sign in...</h2>
                <p class="text-secondary-600">Please wait while we set up your account.</p>
            </div>

            <div v-else-if="error" class="space-y-4">
                <div class="w-16 h-16 bg-error-100 rounded-full flex items-center justify-center mx-auto">
                    <Icon name="heroicons:exclamation-triangle" class="h-8 w-8 text-error-600" />
                </div>
                <h2 class="text-2xl font-bold text-secondary-900">Sign in failed</h2>
                <p class="text-secondary-600">{{ errorMessage }}</p>
                <NuxtLink to="/login" class="btn btn-primary inline-flex items-center">
                    <Icon name="heroicons:arrow-left" class="h-5 w-5 mr-2" />
                    Back to login
                </NuxtLink>
            </div>
        </div>
    </div>
</template>

<script setup>
// Meta tags
useHead({
    title: 'Completing Sign In - NFCGo',
})

// Stores and utilities
const authStore = useAuthStore()
const { $toast } = useNuxtApp()
const route = useRoute()
const router = useRouter()

// Reactive state
const loading = ref(true)
const error = ref(false)
const errorMessage = ref('')

// Handle OAuth callback
onMounted(async () => {
    try {
        // Get token and redirect from query params
        const token = route.query.token
        const redirect = route.query.redirect || '/dashboard'
        const errorParam = route.query.error
        const errorMsg = route.query.message

        // Check for error
        if (errorParam) {
            error.value = true
            loading.value = false
            
            switch (errorParam) {
                case 'invalid_state':
                    errorMessage.value = 'Invalid authentication state. Please try again.'
                    break
                case 'no_code':
                    errorMessage.value = 'No authorization code received. Please try again.'
                    break
                case 'access_denied':
                    errorMessage.value = 'Access was denied. Please authorize the application to continue.'
                    break
                case 'auth_failed':
                    errorMessage.value = errorMsg || 'Authentication failed. Please try again.'
                    break
                default:
                    errorMessage.value = 'An unexpected error occurred. Please try again.'
            }
            
            $toast.error(errorMessage.value)
            
            // Redirect to login after 3 seconds
            setTimeout(() => {
                router.push('/login')
            }, 3000)
            
            return
        }

        // Check if token exists
        if (!token) {
            error.value = true
            errorMessage.value = 'No authentication token received.'
            loading.value = false
            $toast.error('Authentication failed')
            
            setTimeout(() => {
                router.push('/login')
            }, 3000)
            
            return
        }

        // Save token to store and cookie
        authStore.token = token
        const tokenCookie = useCookie('auth-token', {
            httpOnly: false,
            secure: process.env.NODE_ENV === 'production',
            sameSite: 'lax',
            maxAge: 60 * 60 * 24 * 7, // 7 days
        })
        tokenCookie.value = token

        // Fetch user profile
        await authStore.fetchProfile()
        
        // Set authenticated state
        authStore.isAuthenticated = true

        // Show success message
        $toast.success('Welcome back!')

        // Redirect to intended page or dashboard
        setTimeout(() => {
            router.push(redirect)
        }, 1000)

    } catch (err) {
        console.error('OAuth callback error:', err)
        error.value = true
        errorMessage.value = 'Failed to complete sign in. Please try again.'
        loading.value = false
        $toast.error('Authentication failed')
        
        setTimeout(() => {
            router.push('/login')
        }, 3000)
    }
})
</script>

<style scoped>
.spinner {
    border: 3px solid #f3f4f6;
    border-top: 3px solid #3b82f6;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
