<!--
  pages/UserDashboard/ProfileBuilder.vue
  LEGACY ROUTE — kept only so old links and bookmarks never 404.

  This used to be a second, older copy of the Free profile builder. Each plan
  now has exactly one builder (see PORTAL_ROUTES.builders in
  composables/usePortalAccess.js); the `portal-access` middleware forwards this
  URL to the builder for the user's plan, keeping any ?card_id= query.
-->
<script setup>
definePageMeta({ middleware: "auth" });
</script>

<template>
  <div class="min-h-screen flex items-center justify-center text-secondary-500">
    Opening your Profile Builder…
  </div>
</template>
