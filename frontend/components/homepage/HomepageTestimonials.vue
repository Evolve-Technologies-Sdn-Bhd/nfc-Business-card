<template>
  <section id="testimonials" class="section bg-secondary-50">
    <div class="container">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-secondary-900 mb-4">
          Loved by Professionals Worldwide
        </h2>
        <p class="text-xl text-secondary-600 max-w-2xl mx-auto">
          Join thousands of professionals who have transformed their
          networking with NFCGo.
        </p>
      </div>

      <!-- Testimonials Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
        <div
          v-for="testimonial in testimonials"
          :key="testimonial.id"
          class="card-hover p-6"
        >
          <div class="flex mb-4">
            <Icon
              v-for="i in 5"
              :key="i"
              name="heroicons:star"
              class="h-5 w-5 text-yellow-500 fill-current"
            />
          </div>
          <p class="text-secondary-600 mb-6 italic leading-relaxed">
            "{{ testimonial.text }}"
          </p>
          <div class="flex items-center">
            <img
              :src="testimonial.avatar"
              :alt="testimonial.name"
              class="w-12 h-12 rounded-full object-cover mr-4"
            />
            <div>
              <h4 class="font-semibold text-secondary-900">
                {{ testimonial.name }}
              </h4>
              <p class="text-sm text-secondary-500">{{ testimonial.role }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Brand Logos -->
      <div
        class="bg-gradient-to-r from-secondary-50 to-primary-50 rounded-2xl p-8 md:p-12 overflow-hidden"
      >
        <h3 class="text-xl font-semibold text-secondary-900 text-center mb-8">
          Trusted by Leading Companies
        </h3>
        <div class="relative">
          <div
            class="absolute left-0 top-0 bottom-0 w-20 bg-gradient-to-r from-secondary-50 to-transparent z-10"
          ></div>
          <div
            class="absolute right-0 top-0 bottom-0 w-20 bg-gradient-to-l from-primary-50 to-transparent z-10"
          ></div>
          <div class="overflow-hidden">
            <div class="flex animate-scroll-x">
              <div class="flex items-center space-x-12 px-6">
                <div
                  v-for="brand in brands"
                  :key="`${brand.name}-1`"
                  class="flex-shrink-0 w-32 h-16 flex items-center justify-center"
                >
                  <div
                    class="text-secondary-400 hover:text-secondary-600 transition-colors duration-300"
                  >
                    <Icon :name="brand.icon" class="h-8 w-auto" />
                    <span class="sr-only">{{ brand.name }}</span>
                  </div>
                </div>
              </div>
              <div class="flex items-center space-x-12 px-6">
                <div
                  v-for="brand in brands"
                  :key="`${brand.name}-2`"
                  class="flex-shrink-0 w-32 h-16 flex items-center justify-center"
                >
                  <div
                    class="text-secondary-400 hover:text-secondary-600 transition-colors duration-300"
                  >
                    <Icon :name="brand.icon" class="h-8 w-auto" />
                    <span class="sr-only">{{ brand.name }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mt-12">
          <div class="text-center">
            <p class="text-3xl font-bold text-primary-600 mb-1">10K+</p>
            <p class="text-sm text-secondary-600">Active Users</p>
          </div>
          <div class="text-center">
            <p class="text-3xl font-bold text-primary-600 mb-1">500+</p>
            <p class="text-sm text-secondary-600">Companies</p>
          </div>
          <div class="text-center">
            <p class="text-3xl font-bold text-primary-600 mb-1">98%</p>
            <p class="text-sm text-secondary-600">Satisfaction Rate</p>
          </div>
          <div class="text-center">
            <p class="text-3xl font-bold text-primary-600 mb-1">2M+</p>
            <p class="text-sm text-secondary-600">Cards Tapped</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  testimonials: {
    type: Array,
    default: () => []
  },
  brands: {
    type: Array,
    default: () => []
  }
})
</script>

<style scoped>
@keyframes scroll-x {
  0% {
    transform: translateX(0);
  }
  100% {
    transform: translateX(-100%);
  }
}

.animate-scroll-x {
  display: flex;
  width: max-content;
  animation: scroll-x 30s linear infinite;
}

.animate-scroll-x:hover {
  animation-play-state: paused;
}
</style>
