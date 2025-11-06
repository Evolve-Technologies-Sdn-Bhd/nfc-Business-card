<!-- pages/login.vue -->
<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-secondary-100 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <NuxtLink to="/" class="inline-flex items-center">
                    <Icon name="heroicons:identification" class="h-12 w-12 text-primary-600" />
                    <span class="ml-3 text-2xl font-bold text-secondary-900">NFCGo</span>
                </NuxtLink>
                <div class="mt-6 relative">
                    <NuxtLink to="/" class="absolute left-0 top-1/2 -translate-y-1/2 inline-flex items-center px-3 py-2 text-sm border border-secondary-300 rounded-lg text-secondary-600 hover:text-primary-600 hover:border-primary-600 transition-colors bg-white">
                        <Icon name="heroicons:arrow-left" class="h-4 w-4 mr-1" />
                        Back 
                    </NuxtLink>
                    <h2 class="text-3xl font-bold text-secondary-900">Welcome back</h2>
                </div>
                <p class="mt-2 text-sm text-secondary-600">
                    Don't have an account?
                    <NuxtLink to="/register" class="font-medium text-primary-600 hover:text-primary-500">
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
                        <p v-if="errors.password" class="form-error">{{ errors.password[0] }}</p>
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
                            <label for="remember" class="ml-2 block text-sm text-secondary-700">
                                Remember me
                            </label>
                        </div>
                        <NuxtLink to="/ForgotPass" class="text-sm text-primary-600 hover:text-primary-500 font-medium">
                            Forgot password?
                        </NuxtLink>
                    </div>

                    <button type="submit" :disabled="loading" class="btn btn-primary w-full">
                        <div v-if="loading" class="spinner mr-2"></div>
                        <Icon v-else name="heroicons:arrow-right-on-rectangle" class="h-5 w-5 mr-2" />
                        {{ loading ? 'Signing in...' : 'Sign in' }}
                    </button>
                </form>

                <!-- Divider -->
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-secondary-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-secondary-500">Or continue with</span>
                        </div>
                    </div>
                </div>

                <!-- Social Login -->
                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button @click="handleGoogleLogin" :disabled="loading" class="btn btn-outline w-full">
                        <Icon name="logos:google-icon" class="h-5 w-5 mr-2" />
                        Google
                    </button>
                    <button @click="handleAppleLogin" :disabled="loading" class="btn btn-outline w-full">
                        <Icon name="logos:apple" class="h-5 w-5 mr-2" />
                        Apple
                    </button>
                </div>
            </div>

            <!-- 2FA Modal -->
            <div v-if="show2FA" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <Icon name="heroicons:shield-check" class="h-8 w-8 text-primary-600" />
                        </div>
                        <h3 class="text-2xl font-bold text-secondary-900 mb-2">Two-Factor Authentication</h3>
                        <p class="text-secondary-600">Enter the 6-digit code from your authenticator app</p>
                    </div>

                    <form @submit.prevent="handle2FA" class="space-y-6">
                        <div class="form-group">
                            <label class="form-label text-center block">Verification Code</label>
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
                            <p v-if="errors.code" class="form-error text-center">{{ errors.code[0] }}</p>
                        </div>

                        <div class="flex space-x-3">
                            <button type="button" @click="cancel2FA" class="btn btn-secondary flex-1" :disabled="loading">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary flex-1" :disabled="loading || twoFactorCode.length !== 6">
                                <div v-if="loading" class="spinner mr-2"></div>
                                {{ loading ? 'Verifying...' : 'Verify' }}
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
    title: 'Login - NFCGo',
    meta: [
        { name: 'description', content: 'Sign in to your NFCGo account to manage your digital business cards.' }
    ]
})

// Stores
const authStore = useAuthStore()
const { $toast } = useNuxtApp()

// Route and router
const route = useRoute()
const router = useRouter()

// Reactive data
const loading = ref(false)
const showPassword = ref(false)
const show2FA = ref(false)
const twoFactorCode = ref('')
const errors = ref({})

const form = reactive({
    email: '',
    password: '',
    remember: false
})

// Check if user is already authenticated
onMounted(() => {
    if (authStore.isAuthenticated) {
        const redirect = route.query.redirect || '/dashboard'
        router.push(redirect)
    }
})

// Handle login
const handleLogin = async () => {
    loading.value = true
    errors.value = {}

    try {
        console.log('Attempting login with:', { email: form.email, password: '***' })
        const response = await authStore.login(form)
        console.log('Login response:', response)

        if (response.requires_2fa) {
            show2FA.value = true
        } else {
            $toast.success('Welcome back!')
            
            let redirectPath = route.query.redirect
            
            if (!redirectPath) {
                if (authStore.isAdmin()) {
                    redirectPath = '/admin'
                } else {
                    redirectPath = '/dashboard'
                }
            }
            
            await router.push(redirectPath)
        }
    } catch (error) {
        console.error('Login error details:', error)

        if (error.response?.status === 422) {
            errors.value = error.validationErrors || {}
        } else if (error.response?.status === 401) {
            $toast.error('Invalid email or password')
        } else {
            $toast.error('An error occurred. Please try again.')
        }
    } finally {
        loading.value = false
    }
}

// Handle 2FA verification
const handle2FA = async () => {
    loading.value = true
    errors.value = {}

    try {
        await authStore.verify2FA(twoFactorCode.value)
        $toast.success('Welcome back!')
        
        let redirectPath = route.query.redirect
        
        if (!redirectPath) {
            if (authStore.isAdmin()) {
                redirectPath = '/admin'
            } else {
                redirectPath = '/dashboard'
            }
        }
        
        await router.push(redirectPath)
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.validationErrors || {}
        } else {
            $toast.error('Invalid verification code')
        }
    } finally {
        loading.value = false
    }
}

// Cancel 2FA
const cancel2FA = () => {
    show2FA.value = false
    twoFactorCode.value = ''
    errors.value = {}
}

// Handle Google login
const handleGoogleLogin = async () => {
    loading.value = true
    try {
        const config = useRuntimeConfig()
        const backendUrl = config.public.apiBaseUrl.replace('/api', '') || 'http://localhost:8000'
        const redirectTo = route.query.redirect || '/dashboard'
        
        // Redirect to backend OAuth endpoint
        window.location.href = `${backendUrl}/api/auth/google/redirect?redirect_to=${encodeURIComponent(redirectTo)}`
    } catch (error) {
        $toast.error('Google login failed')
        loading.value = false
    }
}

// Handle Apple login
const handleAppleLogin = async () => {
    loading.value = true
    try {
        const config = useRuntimeConfig()
        const backendUrl = config.public.apiBaseUrl.replace('/api', '') || 'http://localhost:8000'
        const redirectTo = route.query.redirect || '/dashboard'
        
        // Redirect to backend OAuth endpoint
        window.location.href = `${backendUrl}/api/auth/apple/redirect?redirect_to=${encodeURIComponent(redirectTo)}`
    } catch (error) {
        $toast.error('Apple login failed')
        loading.value = false
    }
}

// Redirect if already authenticated
watch(() => authStore.isAuthenticated, (isAuth) => {
    if (isAuth) {
        const redirect = route.query.redirect || '/dashboard'
        router.push(redirect)
    }
})
</script>