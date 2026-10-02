// plugins/api.client.js
import axios from "axios";

// ─── GLOBAL ABORT-CONTROLLER REGISTRY ───────────────────────────────────────
// Every in-flight axios request (that didn't opt out with _skipAbort=true)
// stores its AbortController here.  Navigation guards and page unmounts call
// abortAllInFlightRequests() to CANCEL any request the user no longer needs —
// this is crucial for heavy profile-builder pages where a user clicks away
// while 4+ onMounted XHR calls are still pending.
/** @type {Map<string, AbortController>} */
const _abortRegistry = new Map();
let _reqCounter = 0;

/**
 * Immediately abort every currently in-flight request tracked in the registry.
 * Called by router beforeEach before starting a new route transition.
 */
export function abortAllInFlightRequests(reason = "Page navigation: user left page") {
  if (_abortRegistry.size === 0) return 0;
  const count = _abortRegistry.size;
  if (process.env.NODE_ENV !== "production") {
    console.log(`🛑 Aborting ${count} in-flight API request(s): ${reason}`);
  }
  for (const ctrl of _abortRegistry.values()) {
    try { ctrl.abort(reason); } catch (_) { /* ignore DOMException on already-aborted */ }
  }
  _abortRegistry.clear();
  return count;
}

/**
 * Page-level helper: cancels all tracked requests when the component unmounts.
 * Usage (inside a page component's <script setup>):
 *     onUnmounted(createAbortOnRouteChange("ProfileBuilder unmount"));
 */
export function createAbortOnRouteChange(reason = "Component unmounted") {
  return () => abortAllInFlightRequests(reason);
}

// ─── PROFILE BUILDER STATIC CACHE ────────────────────────────────────────
// Data returned by `/profile-builder-init` / individual /sections /fields /design
// options IDENTICAL for a given plan for the entire user session (admin modifies
// them infrequently).  This avoids the "user navigates builder A → B → A 6 navigation
// repeatedly pressing sidebar: instead of 3+4 API calls each onMount = the FIRST call
// serves the cached payload on revisits.
//   Key:   `${userId}:${plan}`
//   Value: { cachedAtMs: number, payload: {sections, fields, design_options, fieldsRaw, permissions}
const BUILDER_CACHE_TTL_MS = 120_000; // 2 minutes
const BUILDER_CACHE = new Map();

export function getCachedBuilderInit(userId, plan) {
  const key = `${userId ?? 0}:${plan ?? 'free'}`;
  const hit = BUILDER_CACHE.get(key);
  if (!hit) return null;
  if (Date.now() - hit.cachedAtMs > BUILDER_CACHE_TTL_MS) {
    BUILDER_CACHE.delete(key);
    return null;
  }
  if (process.env.NODE_ENV !== 'production') {
    console.log(`✅ Builder cache HIT plan=${plan} age=${Math.round((Date.now() - hit.cachedAtMs)/1000)}s`);
  }
  return hit.payload;
}
export function setCachedBuilderInit(userId, plan, payload) {
  const key = `${userId ?? 0}:${plan ?? 'free'}`;
  BUILDER_CACHE.set(key, { cachedAtMs: Date.now(), payload });
}
export function invalidateBuilderCache(userId) {
  if (!userId) { BUILDER_CACHE.clear(); return; }
  for (const k of Array.from(BUILDER_CACHE.keys())) {
    if (k.startsWith(`${userId}:`)) BUILDER_CACHE.delete(k);
  }
}

// ─── NFC CARDS LIST CACHE ─────────────────────────────────────────────────
// The `/nfc-cards` payload rarely changes outside explicit mutations (order, upload proof,
// activate/deactivate, confirm received).  Invalidate only after those mutations —
// otherwise serve cached for 60 seconds.  Keyed by user ID.
const CARDS_CACHE_TTL_MS = 60_000;
const CARDS_CACHE = new Map();
export function getCachedNfcCards(userId) {
  const key = String(userId ?? 0);
  const hit = CARDS_CACHE.get(key);
  if (!hit) return null;
  if (Date.now() - hit.cachedAtMs > CARDS_CACHE_TTL_MS) {
    CARDS_CACHE.delete(key); return null;
  }
  if (process.env.NODE_ENV !== 'production') {
    console.log(`✅ NFC cards cache HIT for user=${key} age=${Math.round((Date.now()-hit.cachedAtMs)/1000)}s`);
  }
  return hit.payload;
}
export function setCachedNfcCards(userId, payload) {
  CARDS_CACHE.set(String(userId ?? 0), { cachedAtMs: Date.now(), payload });
}
export function invalidateCardsCache(userId) {
  if (!userId) { CARDS_CACHE.clear(); return; }
  CARDS_CACHE.delete(String(userId));
}

// ────────────────────────────────────────────────────────────────────────────
// Plugin export
// ────────────────────────────────────────────────────────────────────────────
export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig();

  // ─── Integrate with router: cancel on EVERY route change ────────────────
  // (Nuxt plugin ordering: api.client runs before router.client typically; we
  // use a setTimeout(0) pattern if $router isn't attached yet; otherwise direct hook)
  const hookRouter = () => {
    const router = nuxtApp.$router;
    if (!router) return false;
    router.beforeEach((to, from) => {
      // Don't abort for query/hash-only changes (tab switches etc.)
      if (to.path !== from.path) {
        abortAllInFlightRequests(`Route change ${from.path} → ${to.path}`);
      }
      // This guard only performs cleanup side-effects; navigation always proceeds.
      return true;
    });
    return true;
  };
  if (!hookRouter()) {
    setTimeout(hookRouter, 0);
  }
  nuxtApp.provide("abortApiRequests", abortAllInFlightRequests);

  // Smartly pick API base URL based on current runtime host so local dev
  // always hits the local Laravel API (port 3001/8000) without any manual env,
  // while production sticks to config.public.apiBaseUrl (nfcgo.clbgroups.com).
  // Local hostnames: localhost, 127.0.0.1, 10.x LAN, 172.16-31 LAN, 192.168 LAN, 0.0.0.0
  let baseURL = (config.public?.apiBaseUrl || "").trim();
  const _isLocalHostname = (h) =>
    h === "localhost" ||
    h === "127.0.0.1" ||
    h === "0.0.0.0" ||
    h.startsWith("10.") ||
    h.startsWith("192.168.") ||
    /^172\.(1[6-9]|2[0-9]|3[01])\./.test(h);
  if (typeof window !== "undefined" && window.location && window.location.hostname) {
    const host = window.location.hostname;
    if (_isLocalHostname(host)) {
      const scheme = window.location.protocol === "https:" ? "https" : "http";
      // Classic Laravel port 8000 is NEVER used by Nuxt/Vite (which uses 3000/5173/3001
      // for dev proxies). Prefer 8000 for local dev so we always hit a real PHP server,
      // not a stale Vite 404 fallback when ports get shuffled across restart cycles.
      baseURL = `${scheme}://${host}:8000/api`;
    }
  }
  if (!baseURL) {
    baseURL = "http://localhost:8000/api";
  }

  const isDev = process.env.NODE_ENV !== "production";

  const api = axios.create({
    baseURL,
    timeout: 60000, // 60s default; upload/export endpoints bump this per-request
    headers: {
      Accept: "application/json",
      "X-Requested-With": "XMLHttpRequest",
    },
    withCredentials: false, // Changed to false for better CORS compatibility
  });

  // Request interceptor
  api.interceptors.request.use(
    (config) => {
      // (A) Create & register AbortController for EVERY request unless opted out
      if (!config._skipAbort && !config.signal) {
        const ctrl = new AbortController();
        config.signal = ctrl.signal;
        const id = `req_${++_reqCounter}_${config.url || "unknown"}`;
        config._abortRegistryId = id;
        _abortRegistry.set(id, ctrl);
      }

      if (isDev) {
        console.log("API Request:", config.method?.toUpperCase(), config.url);
      }

      // Use cookie instead of localStorage for better SSR support
      const token = useCookie("auth-token").value;
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }

      // Set Content-Type only if not already set (important for multipart/form-data)
      // ⚠️ NEVER default to application/json WHEN data is FormData!
      //    Browser must set Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryXXX
      //    If we pre-set application/json here, PHP skips parsing $_POST & $_FILES.
      const dataIsFormData = typeof FormData !== "undefined" && config.data instanceof FormData;
      if (!config.headers["Content-Type"] && !dataIsFormData) {
        config.headers["Content-Type"] = "application/json";
      }

      // If Content-Type is multipart/form-data explicitly set (old legacy code), let the browser set the boundary
      if (config.headers["Content-Type"] === "multipart/form-data") {
        delete config.headers["Content-Type"];
        // Give file uploads more time (max 120s)
        config.timeout = config.timeout ?? 120000;
      }
      // If data is FormData but Content-Type already set to something wrong (like application/json from legacy),
      // nuke it so browser can set multipart with boundary correctly.
      if (dataIsFormData && config.headers["Content-Type"] && config.headers["Content-Type"] !== "multipart/form-data") {
        delete config.headers["Content-Type"];
        config.timeout = config.timeout ?? 120000;
      }

      // Admin CSV/exports also need more patience
      const url = (config.url ?? "").toLowerCase();
      if ((config.timeout ?? 0) <= 20000 && (url.includes("/export") || url.includes("invoice"))) {
        config.timeout = 60000;
      }

      // ─── NON-ADMIN GUARD: Prevent non-admin users from hitting /admin/* URLs ───
      // Catches accidental admin route calls from user-facing pages (e.g. /admin/plan-prices
      // on User Settings page) BEFORE they hit the server → avoids 403 toasts entirely.
      try {
        const rawUrl = config.url ?? "";
        const isAdminUrl = typeof rawUrl === "string" &&
          (rawUrl.startsWith("/admin") || rawUrl.includes("/admin/"));
        if (isAdminUrl) {
          const authStore = useAuthStore();
          const userIsAdmin = Boolean(
            authStore?.user?.is_admin ||
            authStore?.user?.admin_role ||
            authStore?.isAdmin
          );
          if (!userIsAdmin) {
            // REJECT THE REQUEST BEFORE IT LEAVES THE BROWSER
            if (process.env.NODE_ENV !== "production") {
              console.warn(
                `🛡️  Non-admin guard BLOCKED call to admin URL: ${config.method?.toUpperCase()} ${rawUrl}. ` +
                `User pages must NEVER call /admin/* endpoints — use public/user routes instead.`
              );
            }
            const err = new Error(`Non-admin users cannot call admin endpoint: ${rawUrl}`);
            err.name = "AdminGuardBlocked";
            err.response = { status: 403, data: { blocked_by_guard: true } };
            err.status = 403;
            return Promise.reject(err);
          }
        }
      } catch (_) { /* guard unavailable, allow the request to proceed as normal */ }

      return config;
    },
    (error) => {
      if (isDev) {
        console.error("API Request Error:", error);
      }
      return Promise.reject(error);
    }
  );

  // ═══════════════════════════════════════════════════════════════════════
  // 🛟 Helper: Extract valid JSON object from mangled text (e.g., PHP notices
  //    `<br /><b>Notice</b>:  PHP Request Startup...<br />{"success":true,...}`)
  //    Uses 3-tier fallback: direct parse → from last `{` → find first "{".
  // ═══════════════════════════════════════════════════════════════════════
  const _tryExtractJsonFromMangled = (raw) => {
    if (raw === null || raw === undefined) return raw;
    if (typeof raw !== "string") return raw;        // already parsed object? return as-is
    const s = raw.trim();
    if (!s) return raw;
    const maybe = (text) => {
      try {
        const parsed = JSON.parse(text);
        return (parsed && typeof parsed === "object") ? parsed : undefined;
      } catch (_) {
        return undefined;
      }
    };
    // 1) Try full string first (normal case)
    let obj = maybe(s);
    if (obj) return obj;
    // 2) Try from FIRST opening brace (covers "<notice> {json}")
    const firstOpen = s.indexOf("{");
    if (firstOpen !== -1) {
      obj = maybe(s.slice(firstOpen));
      if (obj) return obj;
      const lastClose = s.lastIndexOf("}");
      if (lastClose > firstOpen) {
        obj = maybe(s.slice(firstOpen, lastClose + 1));
        if (obj) return obj;
      }
    }
    // 3) Try from LAST opening brace (covers "{nested}{json}" from 2+ echos)
    const lastOpen = s.lastIndexOf("{");
    if (lastOpen !== -1 && lastOpen !== firstOpen) {
      obj = maybe(s.slice(lastOpen));
      if (obj) return obj;
      const lastClose = s.lastIndexOf("}");
      if (lastClose > lastOpen) {
        obj = maybe(s.slice(lastOpen, lastClose + 1));
        if (obj) return obj;
      }
    }
    // Nothing we can do — return original raw string
    return raw;
  };

  // Response interceptor
  api.interceptors.response.use(
    (response) => {
      // (Z) Remove request from abort registry — no longer in-flight
      const id = response.config?._abortRegistryId;
      if (id) _abortRegistry.delete(id);

      if (isDev) {
        console.log("API Response:", response.status, response.config.url);
      }

      // 🛟 Attempt to repair malformed JSON (PHP HTML notices prepended)
      //    Prevents the exact bug: "Template uploaded successfully" with 201,
      //    but response.data contains `<br />Notice` → string not object →
      //    response.success undefined → false-negative error toast.
      const data = _tryExtractJsonFromMangled(response.data);
      if (isDev && typeof response.data === "string" && typeof data === "object") {
        console.warn("🛟 [api.client] Malformed JSON recovered — response body contained PHP notice/HTML prepended. Fix backend output buffer clean.", {
          url: response.config?.url,
          status: response.status,
          extractedKeys: Object.keys(data || {}),
        });
      }

      // Return the (potentially repaired) data portion for cleaner API calls
      return data;
    },
    async (error) => {
      // (Z) Remove request from abort registry even on failure/abort
      const id = error.config?._abortRegistryId;
      if (id) _abortRegistry.delete(id);

      // ── Ignore cancelled requests (they are intentional) ─────────────
      const wasAborted = error.code === "ERR_CANCELED" || error.name === "CanceledError" || axios.isCancel?.(error);
      if (wasAborted) {
        if (isDev) {
          console.log("ℹ️ API request aborted (intentional):", error.config?.url);
        }
        // Never throw toasts / error handler UI for cancelled requests —
        // the user navigated away and doesn't care about this response.
        return Promise.reject({
          __aborted: true,
          message: error.message,
          config: error.config,
          __silent: true,
        });
      }

      const originalRequest = error.config;
      const isLandingPage404 = error.response?.status === 404 && originalRequest.url?.includes('/landing-page');

      // 🛟 Repair malformed JSON in ERROR responses too (same PHP notice HTML prepend on 422/500)
      if (error.response && typeof error.response.data === "string") {
        const repaired = _tryExtractJsonFromMangled(error.response.data);
        if (typeof repaired === "object" && repaired !== null) {
          error.response.data = repaired;
          error.data = repaired;   // set convenience alias used by pages' catch blocks
          if (isDev) {
            console.warn("🛟 [api.client] Malformed ERROR JSON recovered (PHP notice prepended).", {
              url: originalRequest?.url,
              status: error.response?.status,
              extractedKeys: Object.keys(repaired),
            });
          }
        }
      }
      // Also set error.data for backward compatibility (handlers read error.data?.message)
      if (!error.data && error.response?.data) error.data = error.response.data;

      // Reduce console noise for expected 404s on landing-page endpoints
      if (isLandingPage404) {
        if (isDev) {
          console.log("ℹ️ Landing page not found (this is normal for new cards):", originalRequest.url);
        }
      } else {
        console.error("API Response Error:", error);
        if (isDev) {
          console.error("Error Details:", {
            message: error.message,
            status: error.response?.status,
            data: error.response?.data,
            config: error.config,
          });
        }
      }

      // Handle 401 Unauthorized
      if (error.response?.status === 401 && !originalRequest._retry) {
        originalRequest._retry = true;

        const authStore = useAuthStore();
        authStore.clearAuth(); // Clear auth state
        await navigateTo("/UserAccount/login");
        return Promise.reject(error);
      }

      // Handle 403 Forbidden — SMART FILTER:
      // - If forbidden URL is an ADMIN (/admin/*) AND current user is NOT admin:
      //   DON'T show intrusive toast. User-level page accidentally hit admin route → console only.
      // - Otherwise show toast normally.
      if (error.response?.status === 403) {
        const { $toast } = nuxtApp;
        const reqUrl = originalRequest.url || '';
        const isAdminRoute = typeof reqUrl === 'string' && (reqUrl.startsWith('/admin') || reqUrl.includes('/admin/'));
        let userIsAdmin = false;

        try {
          const authStore = useAuthStore();
          userIsAdmin = Boolean(authStore?.user?.is_admin || authStore?.user?.admin_role || authStore?.isAdmin);
        } catch (_) { /* store unavailable */ }

        if (isAdminRoute && !userIsAdmin) {
          if (process.env.NODE_ENV !== 'production') {
            console.warn(
              `ℹ️  Non-admin user hit admin route ${reqUrl} (403 toast suppressed — accidental call from user-facing page)`
            );
          }
        } else {
          if ($toast && typeof $toast.error === 'function') {
            $toast.error(
              "Access denied. You do not have permission to perform this action."
            );
          }
        }
      }

      // Handle 404 Not Found (skip landing-page 404s as they're handled above)
      if (error.response?.status === 404 && !isLandingPage404) {
        console.error("Resource not found:", error.response.config.url);
      }

      // Handle 422 Validation Error
      if (error.response?.status === 422) {
        const validationErrors = error.response.data.errors || {};
        error.validationErrors = validationErrors;
        error.data = error.response.data; // Add this for consistency
      }

      // Handle 429 Rate Limit
      if (error.response?.status === 429) {
        const { $toast } = nuxtApp;
        $toast.error("Too many requests. Please try again later.");
      }

      // Handle 500 Server Error
      if (error.response?.status >= 500) {
        const { $toast } = nuxtApp;
        if ($toast && typeof $toast.error === 'function') {
          $toast.error("Server error. Please try again later.");
        }
      }

      // Handle network errors
      if (!error.response) {
        console.error("Network Error - No response received");
        const { $toast } = nuxtApp;
        if ($toast && typeof $toast.error === 'function') {
          $toast.error(
            "Network error. Please check your connection and try again."
          );
        }
      }

      // Add response data to error for easier access
      if (error.response) {
        error.status = error.response.status;
        error.data = error.response.data;
      } else {
        // Handle cases where there's no response
        error.status = null;
        error.data = null;
      }

      return Promise.reject(error);
    }
  );

  // Provide api instance
  nuxtApp.provide("api", api);
});

