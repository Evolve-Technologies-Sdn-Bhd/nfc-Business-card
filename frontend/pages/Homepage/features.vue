<!-- pages/Homepage/features.vue -->
<template>
  <section id="features" class="section bg-[color:var(--paper)] pt-28 sm:pt-32">
    <div class="container">
      <div class="max-w-2xl mb-14" data-reveal>
        <span class="lp-kicker">Capabilities</span>
        <h1 class="lp-h2 mt-4 mb-4">
          Everything you need for modern networking
        </h1>
        <p class="lp-lead">
          Powerful features designed to make your professional networking
          effortless and memorable.
        </p>
      </div>

      <!-- Auto-sliding feature row (seamless loop) -->
      <div class="overflow-hidden">
        <div
          class="flex transition-transform duration-700 ease-in-out"
          :class="{ 'duration-0': !featureTopIsTransitioning }"
          :style="{ transform: `translateX(-${featureTopCurrentSlide * (featureMobileMode ? 100 : 100/3)}%)` }"
        >
          <div
            v-for="(feature, index) in featuresRow1"
            :key="`feature-${index}`"
            class="flex-shrink-0 w-full md:w-1/3 px-4"
          >
            <div class="feature-card group">
              <div class="feature-card__ico">
                <Icon :name="feature.icon" class="h-6 w-6" />
              </div>
              <h3 class="feature-card__title">{{ feature.title }}</h3>
              <p class="feature-card__desc">{{ feature.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
definePageMeta({ layout: 'public' });

useSeoMeta({
  title: "Features - NFCGo Smart Digital Business Cards",
  description:
    "Explore the NFCGo features: one-tap sharing, customizable profiles, analytics, QR backup and more.",
});

useScrollReveal();

// Feature auto-slide logic (single seamless row)
const featureTopCurrentSlide = ref(0);
const featureTopIsTransitioning = ref(true);
const featureMobileMode = ref(false);
let featureSlideInterval;

const featuresList = [
  {
    title: 'Works Instantly',
    description: 'No app needed',
    icon: 'heroicons:bolt',
    bgClass: 'bg-gradient-to-br from-primary-100 to-primary-200',
    textClass: 'text-primary-600'
  },
  {
    title: 'Customizable Profiles',
    description: 'Customizable and ready template for content and card',
    icon: 'heroicons:paint-brush',
    bgClass: 'bg-gradient-to-br from-[#e4efed] to-[#cfe2df]',
    textClass: 'text-[#4b7a75]'
  },
  {
    title: 'Seamless Linking',
    description: 'Link to WhatsApp, LinkedIn, website, and other social media apps',
    icon: 'heroicons:wifi',
    bgClass: 'bg-gradient-to-br from-success-100 to-success-200',
    textClass: 'text-success-600'
  },
  {
    title: 'Analytics',
    description: 'Track taps and engagement',
    icon: 'heroicons:chart-bar',
    bgClass: 'bg-gradient-to-br from-primary-100 to-primary-200',
    textClass: 'text-primary-600'
  },
  {
    title: 'Centralized Connection Management',
    description: 'Organize every connection in one dashboard. Add notes, set reminders, and never lose a lead.',
    icon: 'heroicons:users',
    bgClass: 'bg-gradient-to-br from-[#f3ebdd] to-[#e6d6bd]',
    textClass: 'text-[#a17e4f]'
  },
  {
    title: 'Eco-Friendly',
    description: 'Eliminate paper waste. One digital card lasts forever.',
    icon: 'heroicons:globe-americas',
    bgClass: 'bg-gradient-to-br from-[#e4efed] to-[#cfe2df]',
    textClass: 'text-[#4b7a75]'
  },
  {
    title: 'QR Code Backup',
    description: 'Built-in QR code for non-NFC devices. Universal compatibility guaranteed.',
    icon: 'heroicons:qr-code',
    bgClass: 'bg-gradient-to-br from-secondary-100 to-secondary-200',
    textClass: 'text-secondary-600'
  },
  {
    title: 'Privacy Controls',
    description: 'Choose what to share and when. Disable temporarily without replacing the card.',
    icon: 'heroicons:shield-check',
    bgClass: 'bg-gradient-to-br from-primary-100 to-primary-200',
    textClass: 'text-primary-600'
  },
  {
    title: 'Video Introduction',
    description: 'Add a personal video message. Memorable first impressions every time.',
    icon: 'heroicons:video-camera',
    bgClass: 'bg-gradient-to-br from-[#f3ebdd] to-[#e6d6bd]',
    textClass: 'text-[#a17e4f]'
  }
];

// Double the list so the horizontal loop is seamless
const featuresRow1 = computed(() => [...featuresList, ...featuresList]);

const startFeatureSlideShow = () => {
  // Start at the end of the first set and slide right for an endless loop
  featureTopCurrentSlide.value = featuresList.length;

  featureSlideInterval = setInterval(() => {
    featureTopIsTransitioning.value = true;
    featureTopCurrentSlide.value--;

    // When we reach 0 the row is identical to the start — snap back invisibly
    if (featureTopCurrentSlide.value <= 0) {
      setTimeout(() => {
        featureTopIsTransitioning.value = false;
        featureTopCurrentSlide.value = featuresList.length;
      }, 700); // Match CSS transition duration
    }
  }, 3500); // 3.5s per slide
};

const checkFeatureMobile = () => {
  featureMobileMode.value = window.innerWidth < 768;
};

onMounted(() => {
  startFeatureSlideShow();
  checkFeatureMobile();
  window.addEventListener('resize', checkFeatureMobile);
});

onUnmounted(() => {
  clearInterval(featureSlideInterval);
  window.removeEventListener('resize', checkFeatureMobile);
});
</script>
