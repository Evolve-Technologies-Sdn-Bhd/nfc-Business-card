// plugins/router.client.js
export default defineNuxtPlugin((nuxtApp) => {
  const router = useRouter();
  let isNavigating = false;

  // Add route change start handler
  router.beforeEach((to, from, next) => {
    // Skip if navigating to the same page
    if (to.path === from.path) {
      next();
      return;
    }

    // If already navigating, allow it to continue
    // (The flag will be reset in afterEach)
    if (isNavigating) {
      console.log("⚠️ Navigation already in progress, allowing:", to.path);
      next();
      return;
    }

    isNavigating = true;
    console.log("🚀 Navigating from", from.path, "to", to.path);
    next();
  });

  // Add route change complete handler
  router.afterEach((to, from) => {
    isNavigating = false;
    console.log("✅ Navigation complete:", to.path);

    // Scroll to top on route change
    if (to.path !== from.path) {
      window.scrollTo({ top: 0, behavior: "smooth" });
    }
  });

  // Handle navigation errors
  router.onError((error) => {
    isNavigating = false;
    console.error("❌ Navigation error:", error);
  });
});
