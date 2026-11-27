<!--
  pages/UserDashboard/NFCCardDesign/BasicPlanNFCCard.vue
  LEGACY ROUTE — kept only so old links and bookmarks never 404.

  Card design is now a single step for every plan (/Onboarding/design); the
  per-plan duplicates Basic/Premium/Business were collapsed into it. This page
  just forwards there.
-->
<script setup>
definePageMeta({ middleware: "auth" });

const route = useRoute();

await navigateTo(
  { path: "/Onboarding/design", query: route.query },
  { replace: true }
);
</script>

<template>
  <div class="min-h-screen flex items-center justify-center text-secondary-500">
    Taking you to the card designer…
  </div>
</template>
