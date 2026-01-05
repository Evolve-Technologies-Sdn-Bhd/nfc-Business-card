<template>
  <section class="section bg-gradient-to-r from-primary-600 to-primary-500">
    <div class="container text-center">
      <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
        Ready to Transform Your Networking?
      </h2>
      <p class="text-xl text-primary-100 mb-8 max-w-2xl mx-auto">
        Join thousands of professionals who are already networking smarter
        with NFC business cards.
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
        <NuxtLink
          to="/UserAccount/register"
          class="btn bg-white text-primary-600 hover:bg-primary-50 btn-lg shadow-lg hover:shadow-xl transition-all duration-300"
        >
          <Icon name="heroicons:rocket-launch" class="h-5 w-5 mr-2" />
          Start Your Free Trial
        </NuxtLink>
        <button
          @click="$emit('show-demo')"
          class="btn btn-outline border-white text-white hover:bg-white hover:text-primary-600 btn-lg transition-all duration-300"
        >
          <Icon name="heroicons:play" class="h-5 w-5 mr-2" />
          Watch Demo
        </button>
      </div>
      <div
        class="flex flex-wrap items-center justify-center gap-8 text-primary-100"
      >
        <div class="flex items-center">
          <Icon name="heroicons:check-circle" class="h-5 w-5 mr-2" />
          <span class="text-sm">Free 14-day trial</span>
        </div>
        <div class="flex items-center">
          <Icon name="heroicons:check-circle" class="h-5 w-5 mr-2" />
          <span class="text-sm">No credit card required</span>
        </div>
        <div class="flex items-center">
          <Icon name="heroicons:check-circle" class="h-5 w-5 mr-2" />
          <span class="text-sm">Cancel anytime</span>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
defineEmits(['show-demo'])
</script>
