<!--
  pages/UserDashboard/Payment.vue
  LEGACY ROUTE — kept only so old links and bookmarks never 404.

  Checkout is now step 3 of the unified onboarding funnel
  (/Onboarding/checkout), which owns the shared chrome, the draft persistence
  and the step guards. This page just forwards there.
-->
<script setup>
definePageMeta({ middleware: "auth" });

const route = useRoute();

await navigateTo(
  { path: "/Onboarding/checkout", query: route.query },
  { replace: true }
);
</script>

<template>
  <div class="min-h-screen flex items-center justify-center text-secondary-500">
    Taking you to checkout…
  </div>
</template>
