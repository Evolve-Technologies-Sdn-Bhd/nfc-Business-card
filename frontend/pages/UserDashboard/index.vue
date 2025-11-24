<!-- pages/UserDashboard/index.vue - Redirect to appropriate dashboard -->
<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div class="text-center">
      <div
        class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-blue-600 border-t-transparent mb-4"
      ></div>
      <p class="text-gray-600">Loading Dashboard...</p>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from "~/stores/auth";

definePageMeta({
  layout: "user-dashboard",
  middleware: ["auth"],
});

const router = useRouter();
const authStore = useAuthStore();

onMounted(() => {
  // Use authStore method to get proper redirect path based on user plan
  const redirectPath = authStore.getRedirectPathByPlan();
  router.replace(redirectPath);
});
</script>
