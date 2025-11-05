<!-- pages/reset-password.vue -->
<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-secondary-100 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <NuxtLink to="/" class="inline-flex items-center">
                    <Icon name="heroicons:identification" class="h-12 w-12 text-primary-600" />
                    <span class="ml-3 text-2xl font-bold text-secondary-900">NFCGo</span>
                </NuxtLink>
                <h2 class="mt-6 text-3xl font-bold text-secondary-900">Set new password</h2>
                <p class="mt-2 text-sm text-secondary-600">
                    Choose a strong password for your account.
                </p>
            </div>

            <!-- Error State (Invalid/Expired Token) -->
            <div v-if="tokenError" class="card p-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <Icon name="heroicons:x-circle" class="h-10 w-10 text-red-600" />
                    </div>
                    <h3 class="text-xl font-semibold text-secondary-900 mb-2">Invalid or expired link</h3>
                    <p class="text-secondary-600 mb-6">
                        This password reset link is invalid or has expired. Please request a new one.
                    </p>
                    <NuxtLink to="/ForgotPass" class="btn btn-primary w-full">
                        Request new link
                    </NuxtLink>
                </div>
            </div>

            <!-- Success State -->
            <div v-else-if="resetSuccess" class="card p-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <Icon name="heroicons:check-circle" class="h-10 w-10 text-green-600" />
                    </div>
                    <h3 class="text-xl font-semibold text-secondary-900 mb-2">Password reset successful</h3>
                    <p class="text-secondary-600 mb-6">
                        Your password has been changed. You can now sign in with your new password.
                    </p>
                    <NuxtLink to="/login" class="btn btn-primary w-full">
                        <Icon name="heroicons:arrow-right-on-rectangle" class="h-5 w-5 mr-2" />
                        Sign in
                    </NuxtLink>
                </div>
            </div>

            <!-- Reset Form -->
            <div v-else class="card p-8">
                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <!-- New Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">New password</label>
                        <div class="relative">
                            <input 
                                id="password" 
                                v-model="form.password" 
                                :type="showPassword ? 'text' : 'password'"
                                required 
                                :class="['input pr-10', errors.password ? 'input-error' : '']"
                                placeholder="Enter new password"
                                :disabled="loading"
                                autocomplete="new-password"
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
                        
                        <!-- Password Strength Indicator -->
                        <div v-if="form.password" class="mt-2">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-secondary-600">Password strength</span>
                                <span :class="passwordStrengthColor">{{ passwordStrengthText }}</span>
                            </div>
                            <div class="w-full bg-secondary-200 rounded-full h-1.5">
                                <div 
                                    :class="['h-1.5 rounded-full transition-all', passwordStrengthColor]"
                                    :style="{ width: passwordStrength + '%' }"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Confirm new password</label>
                        <div class="relative">
                            <input 
                                id="password_confirmation" 
                                v-model="form.password_confirmation" 
                                :type="showPasswordConfirm ? 'text' : 'password'"
                                required 
                                :class="['input pr-10', errors.password_confirmation ? 'input-error' : '']"
                                placeholder="Confirm new password"
                                :disabled="loading"
                                autocomplete="new-password"
                            />
                            <button 
                                type="button" 
                                @click="showPasswordConfirm = !showPasswordConfirm"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                :disabled="loading"
                            >
                                <Icon 
                                    :name="showPasswordConfirm ? 'heroicons:eye-slash' : 'heroicons:eye'"
                                    class="h-5 w-5 text-secondary-400"
                                />
                            </button>
                        </div>
                        <p v-if="errors.password_confirmation" class="form-error">{{ errors.password_confirmation[0] }}</p>
                        <p v-else-if="form.password_confirmation && form.password !== form.password_confirmation" class="form-error">
                            Passwords do not match
                        </p>
                    </div>

                    <!-- Password Requirements -->
                    <div class="bg-secondary-50 rounded-lg p-4">
                        <p class="text-xs font-medium text-secondary-700 mb-2">Password must contain:</p>
                        <ul class="text-xs text-secondary-600 space-y-1">
                            <li :class="form.password.length >= 8 ? 'text-green-600' : ''">
                                <Icon :name="form.password.length >= 8 ? 'heroicons:check-circle' : 'heroicons:minus-circle'" class="h-4 w-4 inline mr-1" />
                                At least 8 characters
                            </li>
                            <li :class="/[A-Z]/.test(form.password) ? 'text-green-600' : ''">
                                <Icon :name="/[A-Z]/.test(form.password) ? 'heroicons:check-circle' : 'heroicons:minus-circle'" class="h-4 w-4 inline mr-1" />
                                One uppercase letter
                            </li>
                            <li :class="/[a-z]/.test(form.password) ? 'text-green-600' : ''">
                                <Icon :name="/[a-z]/.test(form.password) ? 'heroicons:check-circle' : 'heroicons:minus-circle'" class="h-4 w-4 inline mr-1" />
                                One lowercase letter
                            </li>
                            <li :class="/[0-9]/.test(form.password) ? 'text-green-600' : ''">
                                <Icon :name="/[0-9]/.test(form.password) ? 'heroicons:check-circle' : 'heroicons:minus-circle'" class="h-4 w-4 inline mr-1" />
                                One number
                            </li>
                        </ul>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="loading || !isFormValid" 
                        class="btn btn-primary w-full"
                    >
                        <div v-if="loading" class="spinner mr-2"></div>
                        <Icon v-else name="heroicons:lock-closed" class="h-5 w-5 mr-2" />
                        {{ loading ? 'Resetting password...' : 'Reset password' }}
                    </button>

                    <div class="text-center">
                        <NuxtLink to="/login" class="text-sm text-primary-600 hover:text-primary-500">
                            <Icon name="heroicons:arrow-left" class="h-4 w-4 inline mr-1" />
                            Back to login
                        </NuxtLink>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
useHead({
    title: 'Reset Password - NFCGo',
    meta: [
        { name: 'description', content: 'Reset your NFCGo account password.' }
    ]
})

const authStore = useAuthStore()
const { $toast } = useNuxtApp()
const route = useRoute()
const router = useRouter()

const loading = ref(false)
const resetSuccess = ref(false)
const tokenError = ref(false)
const showPassword = ref(false)
const showPasswordConfirm = ref(false)
const errors = ref({})

const form = reactive({
    token: '',
    password: '',
    password_confirmation: ''
})

// Get token from URL
onMounted(() => {
    const token = route.query.token
    if (!token) {
        tokenError.value = true
        return
    }
    form.token = token
})

// Password strength calculation
const passwordStrength = computed(() => {
    const password = form.password
    if (!password) return 0
    
    let strength = 0
    if (password.length >= 8) strength += 25
    if (password.length >= 12) strength += 15
    if (/[a-z]/.test(password)) strength += 20
    if (/[A-Z]/.test(password)) strength += 20
    if (/[0-9]/.test(password)) strength += 20
    if (/[^a-zA-Z0-9]/.test(password)) strength += 20
    
    return Math.min(strength, 100)
})

const passwordStrengthText = computed(() => {
    const strength = passwordStrength.value
    if (strength < 40) return 'Weak'
    if (strength < 70) return 'Fair'
    if (strength < 90) return 'Good'
    return 'Strong'
})

const passwordStrengthColor = computed(() => {
    const strength = passwordStrength.value
    if (strength < 40) return 'text-red-600 bg-red-600'
    if (strength < 70) return 'text-yellow-600 bg-yellow-600'
    if (strength < 90) return 'text-blue-600 bg-blue-600'
    return 'text-green-600 bg-green-600'
})

const isFormValid = computed(() => {
    return form.password.length >= 8 &&
           /[A-Z]/.test(form.password) &&
           /[a-z]/.test(form.password) &&
           /[0-9]/.test(form.password) &&
           form.password === form.password_confirmation
})

const handleSubmit = async () => {
    if (!isFormValid.value) {
        $toast.error('Please meet all password requirements')
        return
    }

    loading.value = true
    errors.value = {}

    try {
        await authStore.resetPassword({
            token: form.token,
            password: form.password,
            password_confirmation: form.password_confirmation
        })
        
        resetSuccess.value = true
        $toast.success('Password reset successfully!')
        
        // Redirect to login after 3 seconds
        setTimeout(() => {
            router.push('/login')
        }, 3000)
    } catch (error) {
        if (error.response?.status === 400 || error.response?.status === 410) {
            // Invalid or expired token
            tokenError.value = true
            $toast.error('This reset link is invalid or has expired')
        } else if (error.response?.status === 422) {
            errors.value = error.validationErrors || {}
            $toast.error('Please check the form for errors')
        } else {
            $toast.error('An error occurred. Please try again.')
        }
    } finally {
        loading.value = false
    }
}
</script>