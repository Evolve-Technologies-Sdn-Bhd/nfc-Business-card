<!--
  pages/UserDashboard/UserManagement/NFCCardDesignCustomization.vue
  LEGACY ROUTE — kept only so old links and bookmarks never 404.

  Card design is now step 2 of the unified onboarding funnel
  (/Onboarding/design), which owns the shared chrome, the draft persistence
  and the step guards. This page just forwards there.
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
