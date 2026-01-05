<template>
  <section id="pricing" class="section bg-white">
    <div class="container">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-secondary-900 mb-4">
          Simple, Flexible Pricing
        </h2>
        <p class="text-xl text-secondary-600 max-w-2xl mx-auto">
          Choose the plan that fits your networking needs. Start free and
          upgrade as you grow.
        </p>
      </div>
      <div
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto"
      >
        <!-- Free Plan -->
        <div
          class="card p-6 text-center relative transition-all duration-300 bg-gradient-to-br from-green-50 to-emerald-50 border-green-200 hover:shadow-lg"
        >
          <h3 class="text-xl font-bold text-secondary-900 mb-2">Free</h3>
          <div class="mb-6">
            <div class="text-3xl font-bold text-green-600 mb-2">Free</div>
            <p class="text-sm text-secondary-500">
              Perfect for getting started
            </p>
          </div>
          <ul class="space-y-2 mb-6 text-left text-sm">
            <li class="flex items-start">
              <Icon
                name="heroicons:check"
                class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
              />
              <span class="text-secondary-600">1 Digital Profile</span>
            </li>
            <li class="flex items-start">
              <Icon
                name="heroicons:check"
                class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
              />
              <span class="text-secondary-600">Basic Templates</span>
            </li>
            <li class="flex items-start">
              <Icon
                name="heroicons:check"
                class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
              />
              <span class="text-secondary-600">Contact Information</span>
            </li>
            <li class="flex items-start">
              <Icon
                name="heroicons:check"
                class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
              />
              <span class="text-secondary-600">Social Media Links</span>
            </li>
            <li class="flex items-start">
              <Icon
                name="heroicons:check"
                class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
              />
              <span class="text-secondary-600">QR Code Backup</span>
            </li>
            <li class="flex items-start">
              <Icon
                name="heroicons:check"
                class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
              />
              <span class="text-secondary-600">Basic Analytics</span>
            </li>
          </ul>
          <div class="mt-auto">
            <NuxtLink
              to="/UserAccount/register"
              class="btn w-full transition-all duration-300 bg-green-600 text-white hover:bg-green-700 shadow-lg hover:shadow-xl"
            >
              Get Started Free
            </NuxtLink>
          </div>
        </div>

        <!-- Basic Plan -->
        <div
          class="card p-6 text-center relative transition-all duration-300 hover:shadow-lg"
        >
          <h3 class="text-xl font-bold text-secondary-900 mb-2">Basic</h3>
          <div class="mb-6">
            <div class="text-2xl font-bold text-secondary-900 mb-2">
              {{ loading ? 'Loading...' : (basicPlan?.currency + ' ' + basicPlan?.price) || 'Contact for Pricing' }}
            </div>
            <p class="text-sm text-secondary-500">
              {{ basicPlan?.description || 'For individual professionals' }}
            </p>
          </div>
          <ul class="space-y-2 mb-6 text-left text-sm">
            <li v-for="feature in basicPlan?.features" :key="feature" class="flex items-start">
              <Icon
                name="heroicons:check"
                class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
              />
              <span class="text-secondary-600">{{ feature }}</span>
            </li>
          </ul>
          <div class="mt-auto">
            <NuxtLink
              to="/UserAccount/register"
              class="btn w-full transition-all duration-300 btn-outline hover:shadow-md"
            >
              Start Free Trial
            </NuxtLink>
          </div>
        </div>

        <!-- Premium Plan -->
        <div
          class="card p-6 text-center relative transition-all duration-300 hover:shadow-lg"
        >
          <h3 class="text-xl font-bold text-secondary-900 mb-2">Premium</h3>
          <div class="mb-6">
            <div class="text-2xl font-bold text-secondary-900 mb-2">
              {{ loading ? 'Loading...' : (premiumPlan?.currency + ' ' + premiumPlan?.price) || 'Contact for Pricing' }}
            </div>
            <p class="text-sm text-secondary-500">{{ premiumPlan?.description || 'For growing businesses' }}</p>
          </div>
          <ul class="space-y-2 mb-6 text-left text-sm">
            <li v-for="feature in premiumPlan?.features" :key="feature" class="flex items-start">
              <Icon
                name="heroicons:check"
                class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
              />
              <span class="text-secondary-600">{{ feature }}</span>
            </li>
          </ul>
          <div class="mt-auto">
            <NuxtLink
              to="/UserAccount/register"
              class="btn w-full transition-all duration-300 btn-outline shadow-lg hover:shadow-xl"
            >
              Start Free Trial
            </NuxtLink>
          </div>
        </div>

        <!-- Business Plan -->
        <div
          class="card p-6 text-center relative transition-all duration-300 hover:shadow-lg"
        >
          <h3 class="text-xl font-bold text-secondary-900 mb-2">Business</h3>
          <div class="mb-6">
            <div class="text-2xl font-bold text-secondary-900 mb-2">
              {{ loading ? 'Loading...' : (businessPlan?.currency + ' ' + businessPlan?.price) || 'Contact for Pricing' }}
            </div>
            <p class="text-sm text-secondary-500">{{ businessPlan?.description || 'For large organizations' }}</p>
          </div>
          <ul class="space-y-2 mb-6 text-left text-sm">
            <li v-for="feature in businessPlan?.features" :key="feature" class="flex items-start">
              <Icon
                name="heroicons:check"
                class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
              />
              <span class="text-secondary-600">{{ feature }}</span>
            </li>
          </ul>
          <div class="mt-auto">
            <button
              @click="$emit('contact-support')"
              class="btn w-full transition-all duration-300 btn-outline hover:shadow-md"
            >
              Contact Sales
            </button>
          </div>
        </div>
      </div>

      <div class="text-center mt-12">
        <p class="text-secondary-600 mb-4">
          All paid plans include 14-day free trial • No credit card required •
          Cancel anytime
        </p>
        <div
          class="flex flex-col sm:flex-row gap-4 justify-center items-center"
        >
          <p class="text-sm text-secondary-500">Need a custom solution?</p>
          <button
            @click="$emit('contact-support')"
            class="text-primary-600 hover:text-primary-700 font-medium text-sm underline"
          >
            Contact our support team
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  basicPlan: {
    type: Object,
    default: null
  },
  premiumPlan: {
    type: Object,
    default: null
  },
  businessPlan: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  }
})

defineEmits(['contact-support'])
</script>
