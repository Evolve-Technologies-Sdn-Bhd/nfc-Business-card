<!-- pages/Homepage/how-it-works.vue -->
<template>
  <section
    id="how-it-works"
    class="section ink-surface ink-grid relative overflow-hidden pt-28 sm:pt-32"
  >
    <div class="container relative">
      <div class="max-w-2xl mb-14" data-reveal>
        <span class="lp-kicker on-ink">Simple Process</span>
        <h1 class="lp-h2 on-ink mt-4 mb-4">How it works</h1>
        <p class="lp-lead lead-ink">
          Get started in minutes with our simple 4-step process.
        </p>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
        <div
          v-for="(step, index) in steps"
          :key="step.title"
          class="step-ink"
          data-reveal
        >
          <div class="step-ink__top">
            <span class="step-ink__num">{{
              String(index + 1).padStart(2, '0')
            }}</span>
            <span class="step-ink__ico">
              <Icon :name="step.icon" class="h-6 w-6" />
            </span>
          </div>
          <h2 class="step-ink__title">{{ step.title }}</h2>
          <p class="step-ink__desc">{{ step.desc }}</p>
        </div>
      </div>
      <div class="mt-14" data-reveal>
        <a
          href="https://youtu.be/PER3f8EIGjs"
          target="_blank"
          rel="noopener noreferrer"
          class="btn-ghost-light btn-lg"
        >
          <Icon name="heroicons:play-circle" class="h-5 w-5 mr-2" />
          Watch the 60-second demo
        </a>
      </div>
    </div>
  </section>
</template>

<script setup>
definePageMeta({ layout: 'public' });

useSeoMeta({
  title: "How it Works - NFCGo Smart Digital Business Cards",
  description:
    "See how NFCGo works: choose your card, set up your profile, activate with one tap and share anywhere.",
});

useScrollReveal();

const steps = [
  {
    title: "Choose your card",
    desc: "Pick a material and finish that suits your brand — PVC, metal or bamboo.",
    icon: "heroicons:rectangle-stack",
  },
  {
    title: "Set up your profile",
    desc: "Add your photo, role, contact details and links from your dashboard.",
    icon: "heroicons:user-circle",
  },
  {
    title: "Activate with one tap",
    desc: "Tap the card on your phone to link it to your live digital profile.",
    icon: "heroicons:bolt",
  },
  {
    title: "Share anywhere",
    desc: "One tap opens your profile — they save your contact, you keep the lead.",
    icon: "heroicons:share",
  },
];
</script>
