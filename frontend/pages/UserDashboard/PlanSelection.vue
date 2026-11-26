<!--
  pages/UserDashboard/PlanSelection.vue
  LEGACY ROUTE — kept only so old links and bookmarks never 404.

  Plan selection is now step 1 of the unified onboarding funnel
  (/Onboarding/plan), which owns the shared chrome, the draft persistence and
  the step guards. This page just forwards there.
-->
<script setup>
definePageMeta({ middleware: "auth" });

const route = useRoute();

await navigateTo(
  { path: "/Onboarding/plan", query: route.query },
  { replace: true }
);
</script>

<template>
  <div class="min-h-screen flex items-center justify-center text-secondary-500">
    Taking you to plan selection…
  </div>
</template>
