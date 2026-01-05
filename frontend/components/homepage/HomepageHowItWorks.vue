<template>
  <section id="how-it-works" class="section bg-secondary-50">
    <div class="container">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-secondary-900 mb-4">
          How It Works
        </h2>
        <p class="text-xl text-secondary-600 max-w-2xl mx-auto">
          Get started in minutes with our simple 4-step process.
        </p>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <div
          v-for="(step, index) in steps"
          :key="step.title"
          class="text-center"
        >
          <div class="relative mb-6">
            <div
              class="w-20 h-20 bg-primary-600 rounded-full flex items-center justify-center mx-auto shadow-lg"
            >
              <span class="text-2xl font-bold text-white">{{
                index + 1
              }}</span>
            </div>
            <div
              v-if="index < steps.length - 1"
              class="hidden md:block absolute top-10 left-1/2 w-full h-0.5 bg-primary-200 transform translate-x-10"
            ></div>
          </div>
          <h3 class="text-xl font-semibold text-secondary-900 mb-3">
            {{ step.title }}
          </h3>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  steps: {
    type: Array,
    default: () => [
      { title: 'Ensure phone have NFC function' },
      { title: 'Tap card on phone' },
      { title: 'Digital profile instantly opens' },
      { title: 'Save contact, view portfolio, connect on LinkedIn, interact with profile' }
    ]
  }
})
</script>
