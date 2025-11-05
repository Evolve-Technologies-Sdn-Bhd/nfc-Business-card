<!-- pages/onboarding/plan-selection.vue -->
<template>
    <div class="min-h-screen bg-gradient-to-br from-primary-50 to-secondary-100">
        <!-- Header -->
        <div class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center">
                        <NuxtLink to="/" class="inline-flex items-center">
                            <Icon name="heroicons:identification" class="h-8 w-8 text-primary-600" />
                            <span class="ml-2 text-xl font-bold text-secondary-900">NFCGo</span>
                        </NuxtLink>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-secondary-600">Step 1 of 3</span>
                        <div class="flex space-x-1">
                            <div class="w-2 h-2 bg-primary-600 rounded-full"></div>
                            <div class="w-2 h-2 bg-secondary-300 rounded-full"></div>
                            <div class="w-2 h-2 bg-secondary-300 rounded-full"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Page Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-secondary-900 mb-4">Choose Your Plan</h1>
                <p class="text-xl text-secondary-600 max-w-2xl mx-auto">
                    Select the perfect plan for your business needs. Start with our free plan or unlock premium features with our paid plans.
                </p>
            </div>

            <!-- Plans Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                <!-- Free Plan -->
                <div class="bg-white rounded-2xl shadow-lg border-2 border-secondary-200 p-6 relative">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-secondary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <Icon name="heroicons:gift" class="h-8 w-8 text-secondary-600" />
                        </div>
                        <h3 class="text-xl font-bold text-secondary-900 mb-2">Free</h3>
                        <div class="mb-6">
                            <span class="text-3xl font-bold text-secondary-900">$0</span>
                            <span class="text-secondary-600">/month</span>
                        </div>
                        <p class="text-sm text-secondary-600 mb-6">Perfect for getting started</p>
                        
                        <ul class="text-left space-y-3 mb-8">
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>Digital business card</span>
                            </li>
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>Basic analytics</span>
                            </li>
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>Up to 5 social links</span>
                            </li>
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>Basic customization</span>
                            </li>
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>QR code generation</span>
                            </li>
                        </ul>

                        <button @click="selectPlan('free')" 
                            class="w-full py-3 px-4 bg-secondary-100 text-secondary-700 rounded-xl font-medium hover:bg-secondary-200 transition-colors">
                            Get Started Free
                        </button>
                    </div>
                </div>

                <!-- Basic Plan -->
                <div class="bg-white rounded-2xl shadow-lg border-2 border-primary-200 p-6 relative">
                    <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                        <span class="bg-primary-600 text-white px-3 py-1 rounded-full text-xs font-medium">Most Popular</span>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <Icon name="heroicons:star" class="h-8 w-8 text-primary-600" />
                        </div>
                        <h3 class="text-xl font-bold text-secondary-900 mb-2">Basic</h3>
                        <div class="mb-6">
                            <span class="text-3xl font-bold text-secondary-900">$9</span>
                            <span class="text-secondary-600">/month</span>
                        </div>
                        <p class="text-sm text-secondary-600 mb-6">Great for small businesses</p>
                        
                        <ul class="text-left space-y-3 mb-8">
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>Everything in Free</span>
                            </li>
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>Physical NFC card (Basic)</span>
                            </li>
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>Advanced analytics</span>
                            </li>
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>Unlimited social links</span>
                            </li>
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>Custom branding</span>
                            </li>
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>Priority support</span>
                            </li>
                        </ul>

                        <button @click="selectPlan('basic')" 
                            class="w-full py-3 px-4 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors">
                            Choose Basic
                        </button>
                    </div>
                </div>

                <!-- Premium Plan -->
                <div class="bg-white rounded-2xl shadow-lg border-2 border-secondary-200 p-6 relative">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <Icon name="heroicons:sparkles" class="h-8 w-8 text-purple-600" />
                        </div>
                        <h3 class="text-xl font-bold text-secondary-900 mb-2">Premium</h3>
                        <div class="mb-6">
                            <span class="text-3xl font-bold text-secondary-900">$19</span>
                            <span class="text-secondary-600">/month</span>
                        </div>
                        <p class="text-sm text-secondary-600 mb-6">For growing businesses</p>
                        
                        <ul class="text-left space-y-3 mb-8">
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>Everything in Basic</span>
                            </li>
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>Physical NFC card (Pro)</span>
                            </li>
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>Advanced card customization</span>
                            </li>
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>Detailed analytics</span>
                            </li>
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>Team management</span>
                            </li>
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>API access</span>
                            </li>
                        </ul>

                        <button @click="selectPlan('premium')" 
                            class="w-full py-3 px-4 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-700 transition-colors">
                            Choose Premium
                        </button>
                    </div>
                </div>

                <!-- Business Plan -->
                <div class="bg-white rounded-2xl shadow-lg border-2 border-secondary-200 p-6 relative">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <Icon name="heroicons:building-office" class="h-8 w-8 text-indigo-600" />
                        </div>
                        <h3 class="text-xl font-bold text-secondary-900 mb-2">Business</h3>
                        <div class="mb-6">
                            <span class="text-3xl font-bold text-secondary-900">$49</span>
                            <span class="text-secondary-600">/month</span>
                        </div>
                        <p class="text-sm text-secondary-600 mb-6">For large organizations</p>
                        
                        <ul class="text-left space-y-3 mb-8">
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>Everything in Premium</span>
                            </li>
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>Multiple NFC cards</span>
                            </li>
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>White-label solution</span>
                            </li>
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>Advanced integrations</span>
                            </li>
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>Dedicated support</span>
                            </li>
                            <li class="flex items-center text-sm">
                                <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2 flex-shrink-0" />
                                <span>Custom development</span>
                            </li>
                        </ul>

                        <button @click="selectPlan('business')" 
                            class="w-full py-3 px-4 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 transition-colors">
                            Choose Business
                        </button>
                    </div>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="text-center">
                <p class="text-sm text-secondary-600 mb-4">
                    All paid plans include a 14-day free trial. No credit card required to start.
                </p>
                <p class="text-xs text-secondary-500">
                    Need help choosing? <a href="#" class="text-primary-600 hover:text-primary-500">Contact our sales team</a>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
// Meta tags
useHead({
    title: 'Choose Your Plan - NFCGo',
    meta: [
        { name: 'description', content: 'Choose the perfect NFCGo plan for your business needs.' }
    ]
})

// Stores
const authStore = useAuthStore()
const { $toast } = useNuxtApp()

// Route and router
const router = useRouter()

// Check if user is authenticated
onMounted(() => {
    if (!authStore.isAuthenticated) {
        router.push('/login')
    }
})

// Select plan and proceed
const selectPlan = async (plan) => {
    try {
        // Store selected plan in session storage for the onboarding flow
        sessionStorage.setItem('selectedPlan', plan)
        
        // Call backend API to update user's plan
        const { $api } = useNuxtApp()
        await $api.post('/onboarding/select-plan', { plan })
        
        if (plan === 'free') {
            // For free plan, go directly to dashboard
            $toast.success('Welcome to NFCGo! Let\'s build your digital business card.')
            await router.push('/dashboard')
        } else {
            // For paid plans, go to NFC card customization
            $toast.success(`Great choice! Let's customize your ${plan} NFC card.`)
            await router.push('/onboarding/nfc-card-customization')
        }
    } catch (error) {
        console.error('Plan selection error:', error)
        $toast.error('Failed to select plan. Please try again.')
    }
}
</script> 