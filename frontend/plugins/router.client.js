// plugins/router.client.js
import { ref } from "vue";

/**
 * ─── EXPORTED GLOBAL STATE for sidebar / nav UI ─────────────────────────────
 * Any component (layouts, AppSidebar) can import and use this ref to visually
 * disable navigation buttons while a route transition is in progress.
 * Prevents the "click fast 5x → 5 route transitions queue → 25 API calls"
 * anti-pattern that the user was experiencing with heavy profile-builder pages.
 */
export const routeNavigationInProgress = ref(false);
export const lastNavigationAtMs = ref(0);
// Minimum enforced delay between accepting new navigation clicks:
const NAVIGATION_DEBOUNCE_MS = 350;

export default defineNuxtPlugin((nuxtApp) => {
  const router = nuxtApp.$router;
  let isNavigating = false;
  // If user attempts navigation WHILE navigating, we REJECT and RETURN FALSE.
  // We do NOT stack pending navigations — that creates a deep queue of heavy
  // page onMounted hooks with 5+ API calls each, all fighting for bandwidth.

  // Add route change start handler
  router.beforeEach((to, from, next) => {
    // (A) Same page? Always allow, no transition needed (query hash changes).
    if (to.path === from.path) {
      next();
      return;
    }

    // (B) Debounce: ignore navigation clicks < 350ms after previous accept.
    //     Guards against mouse double-click registering 2 route starts.
    const nowMs = Date.now();
    const tooSoon = nowMs - lastNavigationAtMs.value < NAVIGATION_DEBOUNCE_MS;

    if (isNavigating || tooSoon) {
      // ⚠️ THIS IS THE KEY LINE THAT WAS MISSING BEFORE:
      // Previously we logged a warning and then called next(), which meant every
      // single fast click queued a full route transition and 4+ onMounted API
      // calls for heavy pages.  Now we silently abort the duplicated attempt.
      if (process.env.NODE_ENV !== "production") {
        console.warn(
          `🛑 NAV BLOCKED (isNavigating=${isNavigating}, debounced=${tooSoon}, ` +
            `${nowMs - lastNavigationAtMs.value}ms since last): ${to.path}`
        );
      }
      next(false); // ← abort duplicate navigation immediately, NOT next()
      return;
    }

    isNavigating = true;
    routeNavigationInProgress.value = true;
    lastNavigationAtMs.value = nowMs;
    console.log("🚀 Navigating from", from.path, "to", to.path);
    next();
  });

  // Add route change complete handler
  router.afterEach((to, from) => {
    isNavigating = false;
    routeNavigationInProgress.value = false;
    if (to.path !== from.path) {
      console.log("✅ Navigation complete:", to.path);
    }

    // Scroll to top on route change
    if (to.path !== from.path) {
      window.scrollTo({ top: 0, behavior: "smooth" });
    }
  });

  // Handle navigation errors
  router.onError((error) => {
    isNavigating = false;
    routeNavigationInProgress.value = false;
    console.error("❌ Navigation error:", error);
  });
});

