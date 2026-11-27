<!--
  pages/Onboarding/index.vue
  Entry point for the onboarding funnel. Always resolves to the step the user
  still actually needs, so deep links / bookmarks / post-login redirects all
  land in the right place.
-->
<script setup>
definePageMeta({
  layout: "onboarding",
  middleware: "auth",
});

const { hydrate, resolveEntry } = useOnboardingFlow();

await hydrate();
await navigateTo(resolveEntry(), { replace: true });
</script>

<template>
  <div class="container py-24 text-center text-white/60">
    Loading your setup…
  </div>
</template>
