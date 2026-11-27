<!--
  components/OnboardingStepper.vue
  The single step indicator for the onboarding funnel. It derives the current
  step from the route so every step renders the exact same chrome — previously
  plan / design / checkout each had their own hand-rolled variant.
-->
<template>
  <div class="flex items-center gap-3 sm:gap-4">
    <span
      class="hidden sm:inline text-micro uppercase text-white/55"
      style="letter-spacing: 0.16em"
    >
      Step {{ current }} of {{ total }} · {{ label }}
    </span>
    <div class="flex items-center gap-1.5" aria-hidden="true">
      <span
        v-for="n in total"
        :key="n"
        class="step-dot"
        :class="{ 'step-dot--active': n === current }"
      ></span>
    </div>
  </div>
</template>

<script setup>
import { ONBOARDING_STEPS, ONBOARDING_STEP_LABELS } from "~/composables/useOnboardingFlow";

const total = ONBOARDING_STEPS.length;

const route = useRoute();

const current = computed(() => {
  const index = ONBOARDING_STEPS.findIndex((step) =>
    route.path.toLowerCase().endsWith(`/onboarding/${step}`)
  );
  return index === -1 ? 1 : index + 1;
});

const label = computed(() => ONBOARDING_STEP_LABELS[ONBOARDING_STEPS[current.value - 1]] || "Plan");
</script>
