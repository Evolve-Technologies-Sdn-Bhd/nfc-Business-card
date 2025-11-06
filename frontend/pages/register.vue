<!-- pages/register.vue -->
<template>
    <div
        class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-secondary-100 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <NuxtLink to="/" class="inline-flex items-center">
                    <Icon name="heroicons:identification" class="h-12 w-12 text-primary-600" />
                    <span class="ml-3 text-2xl font-bold text-secondary-900">NFCGo</span>
                </NuxtLink>
                <div class="mt-6 relative">
                    <NuxtLink to="/" class="absolute -left-4 top-1/2 -translate-y-1/2 inline-flex items-center px-3 py-2 text-sm border border-secondary-300 rounded-lg text-secondary-600 hover:text-primary-600 hover:border-primary-600 transition-colors bg-white">
                        <Icon name="heroicons:arrow-left" class="h-4 w-4 mr-1" />
                        Back 
                    </NuxtLink>
                    <h2 class="text-3xl font-bold text-secondary-900">Create your account</h2>
                </div>
                <p class="mt-2 text-sm text-secondary-600">
                    Already have an account?
                    <NuxtLink to="/login" class="font-medium text-primary-600 hover:text-primary-500">
                        Sign in here
                    </NuxtLink>
                </p>
            </div>

            <!-- Registration Form -->
            <div class="card p-8">
                <form @submit.prevent="handleRegister" class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="first_name" class="form-label">First name</label>
                            <input id="first_name" v-model="form.first_name" type="text" required :class="[
                                'input',
                                errors.first_name ? 'input-error' : ''
                            ]" placeholder="John" :disabled="loading" />
                            <p v-if="errors.first_name" class="form-error">{{ errors.first_name[0] }}</p>
                        </div>
                        <div class="form-group">
                            <label for="last_name" class="form-label">Last name</label>
                            <input id="last_name" v-model="form.last_name" type="text" required :class="[
                                'input',
                                errors.last_name ? 'input-error' : ''
                            ]" placeholder="Doe" :disabled="loading" />
                            <p v-if="errors.last_name" class="form-error">{{ errors.last_name[0] }}</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email address</label>
                        <input id="email" v-model="form.email" type="email" required :class="[
                            'input',
                            errors.email ? 'input-error' : ''
                        ]" placeholder="john@example.com" :disabled="loading" />
                        <p v-if="errors.email" class="form-error">{{ errors.email[0] }}</p>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="relative">
                            <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
                                required :class="[
                                    'input pr-10',
                                    errors.password ? 'input-error' : ''
                                ]" placeholder="Create a strong password" :disabled="loading" />
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center" :disabled="loading">
                                <Icon :name="showPassword ? 'heroicons:eye-slash' : 'heroicons:eye'"
                                    class="h-5 w-5 text-secondary-400" />
                            </button>
                        </div>
                        <p v-if="errors.password" class="form-error">{{ errors.password[0] }}</p>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Confirm password</label>
                        <div class="relative">
                            <input id="password_confirmation" v-model="form.password_confirmation"
                                :type="showConfirmPassword ? 'text' : 'password'" required :class="[
                                    'input pr-10',
                                    errors.password_confirmation ? 'input-error' : ''
                                ]" placeholder="Confirm your password" :disabled="loading" />
                            <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center" :disabled="loading">
                                <Icon :name="showConfirmPassword ? 'heroicons:eye-slash' : 'heroicons:eye'"
                                    class="h-5 w-5 text-secondary-400" />
                            </button>
                        </div>
                        <p v-if="errors.password_confirmation" class="form-error">{{ errors.password_confirmation[0] }}
                        </p>
                        <p v-if="form.password && form.password_confirmation && form.password !== form.password_confirmation"
                            class="text-xs text-error-600 mt-1">
                            Passwords do not match
                        </p>
                    </div>

                    <div class="form-group">
                        <label for="company" class="form-label">Company (optional)</label>
                        <input id="company" v-model="form.company" type="text" :class="[
                            'input',
                            errors.company ? 'input-error' : ''
                        ]" placeholder="Your company name" :disabled="loading" />
                        <p v-if="errors.company" class="form-error">{{ errors.company[0] }}</p>
                    </div>

                    <div class="form-group">
                        <label for="job_title" class="form-label">Job Title (optional)</label>
                        <input id="job_title" v-model="form.job_title" type="text" :class="[
                            'input',
                            errors.job_title ? 'input-error' : ''
                        ]" placeholder="Your job title" :disabled="loading" />
                        <p v-if="errors.job_title" class="form-error">{{ errors.job_title[0] }}</p>
                    </div>

                    <div class="flex items-start">
                        <input id="terms" v-model="form.terms" type="checkbox" required
                            class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-secondary-300 rounded mt-1"
                            :disabled="loading" />
                        <label for="terms" class="ml-2 block text-sm text-secondary-700">
                            I agree to the
                            <a href="/terms" target="_blank"
                                class="text-primary-600 hover:text-primary-500 underline">Terms of Service</a>
                            and
                            <a href="/privacy" target="_blank"
                                class="text-primary-600 hover:text-primary-500 underline">Privacy Policy</a>
                        </label>
                    </div>
                    <p v-if="errors.terms" class="form-error">{{ errors.terms[0] }}</p>

                    <div class="flex items-start">
                        <input id="marketing" v-model="form.marketing" type="checkbox"
                            class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-secondary-300 rounded mt-1"
                            :disabled="loading" />
                        <label for="marketing" class="ml-2 block text-sm text-secondary-700">
                            I would like to receive marketing emails about new features and updates
                        </label>
                    </div>

                    <button type="submit" :disabled="loading || !form.terms || !isFormValid"
                        class="btn btn-primary w-full">
                        <div v-if="loading" class="spinner mr-2"></div>
                        <Icon v-else name="heroicons:user-plus" class="h-5 w-5 mr-2" />
                        {{ loading ? 'Creating account...' : 'Create account' }}
                    </button>

                    <p class="text-xs text-secondary-500 text-center">
                        By creating an account, you'll get a 14-day free trial of our Professional plan.
                        No credit card required.
                    </p>
                </form>

                <!-- Divider -->
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-secondary-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-secondary-500">Or sign up with</span>
                        </div>
                    </div>
                </div>

                <!-- Social Registration -->
                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button @click="handleGoogleSignup" :disabled="loading" class="btn btn-outline w-full">
                        <Icon name="logos:google-icon" class="h-5 w-5 mr-2" />
                        Google
                    </button>
                    <button @click="handleAppleSignup" :disabled="loading" class="btn btn-outline w-full">
                        <Icon name="logos:apple" class="h-5 w-5 mr-2" />
                        Apple
                    </button>
                </div>

                <!-- Trust Indicators -->
                <div class="mt-6 text-center">
                    <div class="flex items-center justify-center space-x-6 text-secondary-500">
                        <div class="flex items-center">
                            <Icon name="heroicons:shield-check" class="h-4 w-4 mr-1" />
                            <span class="text-xs">SSL Secured</span>
                        </div>
                        <div class="flex items-center">
                            <Icon name="heroicons:clock" class="h-4 w-4 mr-1" />
                            <span class="text-xs">Free Trial</span>
                        </div>
                        <div class="flex items-center">
                            <Icon name="heroicons:x-mark" class="h-4 w-4 mr-1" />
                            <span class="text-xs">No Spam</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2FA Setup Modal -->
            <div v-if="show2FASetup"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4">
                    <div class="text-center mb-6">
                        <div
                            class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <Icon name="heroicons:shield-check" class="h-8 w-8 text-primary-600" />
                        </div>
                        <h3 class="text-2xl font-bold text-secondary-900 mb-2">Setup Two-Factor Authentication</h3>
                        <p class="text-secondary-600">Secure your account with an additional layer of protection</p>
                    </div>

                    <div class="space-y-6">
                        <!-- QR Code placeholder -->
                        <div class="bg-secondary-100 rounded-lg p-8 text-center">
                            <div
                                class="w-32 h-32 bg-white rounded-lg mx-auto flex items-center justify-center border-2 border-dashed border-secondary-300">
                                <div class="text-center">
                                    <Icon name="heroicons:qr-code" class="h-12 w-12 text-secondary-400 mx-auto mb-2" />
                                    <p class="text-xs text-secondary-500">QR Code</p>
                                </div>
                            </div>
                            <p class="text-sm text-secondary-600 mt-4">
                                Scan this QR code with your authenticator app
                            </p>
                            <p class="text-xs text-secondary-500 mt-2">
                                Recommended apps: Google Authenticator, Authy, 1Password
                            </p>
                        </div>

                        <!-- Manual Setup -->
                        <div class="text-center">
                            <button @click="showManualSetup = !showManualSetup"
                                class="text-sm text-primary-600 hover:text-primary-500">
                                Can't scan? Enter code manually
                            </button>
                            <div v-if="showManualSetup"
                                class="mt-3 p-3 bg-secondary-50 rounded text-xs font-mono text-secondary-700">
                                JBSWY3DPEHPK3PXP
                            </div>
                        </div>

                        <form @submit.prevent="handle2FASetup" class="space-y-4">
                            <div class="form-group">
                                <label class="form-label text-center block">Enter 6-digit verification code</label>
                                <div class="flex justify-center">
                                    <vue3-otp-input :value="twoFactorCode" @update:value="twoFactorCode = $event"
                                        :num-inputs="6" :should-auto-focus="true" :is-input-num="true" separator=""
                                        :class-list="{
                                            input: 'w-12 h-12 text-center border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-lg font-semibold',
                                            inputBox: 'mx-1'
                                        }" />
                                </div>
                                <p v-if="errors.code" class="form-error text-center">{{ errors.code[0] }}</p>
                            </div>

                            <div class="flex space-x-3">
                                <button type="button" @click="skip2FASetup" class="btn btn-secondary flex-1"
                                    :disabled="loading">
                                    Skip for now
                                </button>
                                <button type="submit" class="btn btn-primary flex-1"
                                    :disabled="loading || twoFactorCode.length !== 6">
                                    <div v-if="loading" class="spinner mr-2"></div>
                                    <Icon v-else name="heroicons:shield-check" class="h-4 w-4 mr-2" />
                                    {{ loading ? 'Verifying...' : 'Complete Setup' }}
                                </button>
                            </div>
                        </form>

                        <div class="text-center">
                            <p class="text-xs text-secondary-500">
                                You can always enable 2FA later in your account settings
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Modal -->
            <div v-if="showSuccessModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 text-center">
                    <div class="w-16 h-16 bg-success-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <Icon name="heroicons:check" class="h-8 w-8 text-success-600" />
                    </div>
                    <h3 class="text-2xl font-bold text-secondary-900 mb-2">Welcome to NFCGo!</h3>
                    <p class="text-secondary-600 mb-6">
                        Your account has been created successfully. Let's choose your plan and get started.
                    </p>
                    <button @click="goToPlanSelection" class="btn btn-primary w-full">
                        <Icon name="heroicons:arrow-right" class="h-5 w-5 mr-2" />
                        Choose Your Plan
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
// Meta tags
useHead({
    title: 'Sign Up - NFCGo',
    meta: [
        { name: 'description', content: 'Create your NFCGo account and start building smart digital business cards.' }
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
const showConfirmPassword = ref(false)
const show2FASetup = ref(false)
const showManualSetup = ref(false)
const showSuccessModal = ref(false)
const twoFactorCode = ref('')
const errors = ref({})

const form = reactive({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    company: '',
    job_title: '',
    terms: false,
    marketing: false
})

// Password strength computation
const passwordStrength = computed(() => {
    const password = form.password
    let strength = 0

    if (password.length >= 8) strength++
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++
    if (/\d/.test(password)) strength++
    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++

    return strength
})

// Form validation
const isFormValid = computed(() => {
    return form.first_name &&
        form.last_name &&
        form.email &&
        form.password &&
        form.password_confirmation &&
        form.password === form.password_confirmation &&
        form.terms
})

// Password strength helpers
const getPasswordStrengthColor = (strength) => {
    const colors = {
        1: 'bg-error-500',
        2: 'bg-warning-500',
        3: 'bg-warning-400',
        4: 'bg-success-500'
    }
    return colors[strength] || 'bg-secondary-200'
}

const getPasswordStrengthText = (strength) => {
    const texts = {
        0: 'Enter a password',
        1: 'Weak password',
        2: 'Fair password',
        3: 'Good password',
        4: 'Strong password'
    }
    return texts[strength] || ''
}

// Check if user is already authenticated
onMounted(() => {
    if (authStore.isAuthenticated) {
        // Check if user has completed onboarding by looking for subscription plan
        const user = authStore.user
        if (user && user.subscription_plan) {
            // User has completed onboarding, redirect to dashboard
            router.push('/dashboard')
        } else {
            // User hasn't completed onboarding, let them continue with onboarding flow
            // Don't redirect automatically
        }
    }
})

// Handle registration
const handleRegister = async () => {
    loading.value = true
    errors.value = {}

    try {
        const response = await authStore.register(form)

        if (response.requires_2fa) {
            show2FASetup.value = true
        } else {
            showSuccessModal.value = true
        }
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.validationErrors
        } else if (error.response?.status === 409) {
            $toast.error('An account with this email already exists')
        } else {
            $toast.error('An error occurred. Please try again.')
        }
    } finally {
        loading.value = false
    }
}

// Handle 2FA setup
const handle2FASetup = async () => {
    loading.value = true
    errors.value = {}

    try {
        await authStore.verify2FA(twoFactorCode.value)
        show2FASetup.value = false
        showSuccessModal.value = true
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.validationErrors
        } else {
            $toast.error('Invalid verification code')
        }
    } finally {
        loading.value = false
    }
}

// Skip 2FA setup
const skip2FASetup = () => {
    show2FASetup.value = false
    showSuccessModal.value = true
}

// Go to plan selection
const goToPlanSelection = async () => {
    showSuccessModal.value = false
    $toast.success('Welcome to NFCGo! Let\'s choose your plan.')
    await router.push('/onboarding/plan-selection')
}

// Handle Google signup
const handleGoogleSignup = async () => {
    loading.value = true

    try {
        // Initialize Google Sign-In
        // This is a placeholder - you'll need to implement actual Google OAuth
        $toast.info('Google signup integration coming soon')
    } catch (error) {
        $toast.error('Google signup failed')
    } finally {
        loading.value = false
    }
}

// Handle Apple signup
const handleAppleSignup = async () => {
    loading.value = true

    try {
        // Initialize Apple Sign-In
        // This is a placeholder - you'll need to implement actual Apple OAuth
        $toast.info('Apple signup integration coming soon')
    } catch (error) {
        $toast.error('Apple signup failed')
    } finally {
        loading.value = false
    }
}

// Redirect if already authenticated (but not after registration)
watch(() => authStore.isAuthenticated, (isAuth) => {
    // Only redirect to dashboard if user is authenticated but not in the middle of registration
    // This prevents automatic redirection during the onboarding flow
    if (isAuth && !showSuccessModal.value && !show2FASetup.value) {
        // Check if user has completed onboarding by looking for subscription plan
        const user = authStore.user
        if (user && user.subscription_plan) {
            // User has completed onboarding, redirect to dashboard
            router.push('/dashboard')
        } else {
            // User hasn't completed onboarding, let them go through the flow
            // Don't redirect automatically
        }
    }
})

// Clear password confirmation error when passwords match
watch(() => [form.password, form.password_confirmation], () => {
    if (errors.value.password_confirmation && form.password === form.password_confirmation) {
        delete errors.value.password_confirmation
    }
})

// Watch for password changes to update strength
watch(() => form.password, () => {
    // Clear password errors when user starts typing
    if (errors.value.password) {
        delete errors.value.password
    }
})

// Auto-focus first input on mount
onMounted(() => {
    const firstInput = document.getElementById('first_name')
    if (firstInput) {
        firstInput.focus()
    }
})
</script>