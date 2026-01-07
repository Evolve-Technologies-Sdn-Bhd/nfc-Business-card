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

      <!-- Auto-Sliding Testimonials Carousel -->
      <div 
        class="relative mb-16 overflow-hidden"
        @mouseenter="pauseAutoSlide"
        @mouseleave="resumeAutoSlide"
      >
        <!-- Carousel Container -->
        <div class="relative">
          <!-- Gradient Overlays for smooth edges -->
          <div class="absolute left-0 top-0 bottom-0 w-8 md:w-16 bg-gradient-to-r from-secondary-50 to-transparent z-10 pointer-events-none"></div>
          <div class="absolute right-0 top-0 bottom-0 w-8 md:w-16 bg-gradient-to-l from-secondary-50 to-transparent z-10 pointer-events-none"></div>
          
          <!-- Slides Wrapper -->
          <div 
            class="flex transition-transform duration-700 ease-in-out"
            :style="{ transform: `translateX(-${currentSlidePosition}%)` }"
          >
            <!-- Duplicate last items for seamless loop -->
            <div
              v-for="(testimonial, index) in extendedTestimonials"
              :key="`testimonial-${index}`"
              class="flex-shrink-0 w-full md:w-1/2 lg:w-1/3 px-3"
            >
              <div class="card-hover p-6 bg-white rounded-xl h-full">
                <div class="flex mb-4">
                  <Icon
                    v-for="i in 5"
                    :key="i"
                    name="heroicons:star"
                    class="h-5 w-5 text-yellow-500 fill-current"
                  />
                </div>
                <p class="text-secondary-600 mb-6 italic leading-relaxed min-h-[100px]">
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
          </div>
        </div>

        <!-- Navigation Dots -->
        <div class="flex justify-center mt-8 gap-2">
          <button
            v-for="(_, index) in testimonials"
            :key="`dot-${index}`"
            @click="goToSlide(index)"
            class="w-2.5 h-2.5 rounded-full transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-400 focus:ring-offset-2"
            :class="[
              currentSlide === index
                ? 'bg-primary-600 w-8'
                : 'bg-secondary-300 hover:bg-secondary-400'
            ]"
            :aria-label="`Go to testimonial ${index + 1}`"
          />
        </div>

        <!-- Navigation Arrows -->
        <button
          @click="prevSlide"
          class="absolute left-2 md:left-4 top-1/2 -translate-y-1/2 z-20 p-2 rounded-full bg-white/90 shadow-lg hover:bg-white transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-400"
          aria-label="Previous testimonial"
        >
          <Icon name="heroicons:chevron-left" class="h-5 w-5 text-secondary-600" />
        </button>
        <button
          @click="nextSlide"
          class="absolute right-2 md:right-4 top-1/2 -translate-y-1/2 z-20 p-2 rounded-full bg-white/90 shadow-lg hover:bg-white transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-400"
          aria-label="Next testimonial"
        >
          <Icon name="heroicons:chevron-right" class="h-5 w-5 text-secondary-600" />
        </button>
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
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
  testimonials: {
    type: Array,
    default: () => []
  },
  brands: {
    type: Array,
    default: () => []
  }
})

// Current slide index
const currentSlide = ref(0)

// Auto-slide interval (4 seconds)
const SLIDE_INTERVAL = 4000

// Reference to interval timer
let autoSlideTimer = null

// Calculate position based on responsive card width
const currentSlidePosition = computed(() => {
  // Each card is 33.333% on lg, 50% on md, 100% on mobile
  // We calculate based on single card movement
  const slideWidth = 33.333 // For lg screens (3 visible)
  return currentSlide.value * slideWidth
})

// Extended testimonials for seamless infinite loop
// We add 3 clones at the end for smooth transition
const extendedTestimonials = computed(() => {
  if (!props.testimonials || props.testimonials.length === 0) return []
  return [...props.testimonials, ...props.testimonials.slice(0, 3)]
})

// Go to specific slide
const goToSlide = (index) => {
  currentSlide.value = index
  resetAutoSlideTimer()
}

// Next slide
const nextSlide = () => {
  if (currentSlide.value >= props.testimonials.length - 1) {
    // Reset to beginning smoothly
    currentSlide.value = 0
  } else {
    currentSlide.value++
  }
  resetAutoSlideTimer()
}

// Previous slide
const prevSlide = () => {
  if (currentSlide.value <= 0) {
    currentSlide.value = props.testimonials.length - 1
  } else {
    currentSlide.value--
  }
  resetAutoSlideTimer()
}

// Start auto-sliding
const startAutoSlide = () => {
  stopAutoSlide()
  autoSlideTimer = setInterval(() => {
    nextSlide()
  }, SLIDE_INTERVAL)
}

// Stop auto-sliding
const stopAutoSlide = () => {
  if (autoSlideTimer) {
    clearInterval(autoSlideTimer)
    autoSlideTimer = null
  }
}

// Pause on hover
const pauseAutoSlide = () => {
  stopAutoSlide()
}

// Resume after hover
const resumeAutoSlide = () => {
  startAutoSlide()
}

// Reset timer after manual navigation
const resetAutoSlideTimer = () => {
  stopAutoSlide()
  startAutoSlide()
}

// Lifecycle hooks
onMounted(() => {
  startAutoSlide()
})

onBeforeUnmount(() => {
  stopAutoSlide()
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

/* Smooth card hover effect */
.card-hover {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card-hover:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.15);
}
</style>
