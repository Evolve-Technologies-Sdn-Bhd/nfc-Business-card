<!-- <template>
  <div>
    <NuxtRouteAnnouncer />
    <NuxtWelcome />
  </div>
</template> -->

<!-- app.vue -->
<template>
  <div class="app-root">
    <!-- ==============================================
         APP BOOT LOADING (initial SPA hydration)
         Shows branded NFCGo wave loader until app is hydrated
         ============================================== -->
    <Transition name="nfc-boot-fade">
      <section
        v-if="appBootLoading"
        class="nfc-boot-screen"
        role="status"
        aria-live="polite"
        aria-label="Preparing NFCGo"
      >
        <div class="nfc-boot-ambient nfc-boot-ambient-one"></div>
        <div class="nfc-boot-ambient nfc-boot-ambient-two"></div>
        <div class="nfc-boot-grid" aria-hidden="true"></div>

        <div class="nfc-boot-frame">
          <header class="nfc-boot-header">
            <div class="nfc-boot-wordmark">
              <span class="nfc-boot-wordmark-dot"></span>
              <span>SMART WORKSPACE</span>
            </div>
            <span class="nfc-boot-version">NFC TECHNOLOGY</span>
          </header>

          <main class="nfc-boot-main">
            <div class="nfc-boot-visual" aria-hidden="true">
              <div class="nfc-boot-orbit nfc-boot-orbit-outer"></div>
              <div class="nfc-boot-orbit nfc-boot-orbit-middle"></div>
              <div class="nfc-boot-orbit nfc-boot-orbit-inner"></div>
              <div class="nfc-boot-orbit-dash"></div>

              <div class="nfc-boot-signal nfc-boot-signal-left">
                <svg viewBox="0 0 40 40" fill="none">
                  <path d="M24 8C31 14 31 26 24 32" />
                  <path d="M17 13C22 17 22 23 17 27" />
                  <circle cx="11" cy="20" r="2" />
                </svg>
              </div>

              <div class="nfc-boot-signal nfc-boot-signal-right">
                <svg viewBox="0 0 40 40" fill="none">
                  <path d="M16 8C9 14 9 26 16 32" />
                  <path d="M23 13C18 17 18 23 23 27" />
                  <circle cx="29" cy="20" r="2" />
                </svg>
              </div>

              <div class="nfc-boot-core">
                <div class="nfc-boot-core-glow"></div>
                <div class="nfc-boot-logo-wrap">
                  <img
                    v-if="systemBrand.system_logo_url"
                    :src="systemBrand.system_logo_url + (systemBrand.system_logo_url.includes('?') ? '&' : '?') + 'v=' + (systemBrand.brandVersion || 1)"
                    :alt="systemBrand.app_name || 'NFCGo'"
                    class="nfc-boot-logo"
                  />
                  <div v-else class="nfc-boot-fallback-logo">
                    <span class="nfc-boot-fallback-nfc">NFC</span>
                    <span class="nfc-boot-fallback-go">GO</span>
                  </div>
                </div>
              </div>

              <span class="nfc-boot-particle nfc-boot-particle-one"></span>
              <span class="nfc-boot-particle nfc-boot-particle-two"></span>
              <span class="nfc-boot-particle nfc-boot-particle-three"></span>
            </div>

            <div class="nfc-boot-copy">
              <p class="nfc-boot-eyebrow">YOUR OPERATIONS, CONNECTED</p>
              <h1 class="nfc-boot-title">{{ systemBrand.app_name || 'NFCGo' }}</h1>
              <p class="nfc-boot-description">
                A smarter way to manage your connected workspace.
              </p>
            </div>

            <div class="nfc-boot-loading">
              <div class="nfc-boot-loading-track" aria-hidden="true">
                <span></span>
              </div>
              <div class="nfc-boot-loading-label">
                <span class="nfc-boot-loading-dot"></span>
                <span>Preparing your workspace</span>
              </div>
            </div>
          </main>

          <footer class="nfc-boot-footer">
            <span class="nfc-boot-footer-line"></span>
            <span>SMART. SEAMLESS. CONNECTED.</span>
            <span class="nfc-boot-footer-line"></span>
          </footer>
        </div>
      </section>
    </Transition>

    <!-- ==============================================
         PAGE NAVIGATION LOADING BAR (top progress)
         Matches brand gradient: Matte Navy → Matte Teal → Gold
         ============================================== -->

    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useTheme } from '~/composables/useTheme';

const appBootLoading = ref(true);
const { initTheme } = useTheme();
const systemBrand = useState("systemBrand", () => ({
  app_name: "NFCGo",
  system_logo_url: "",
  system_favicon_url: "",
  brandVersion: 0,
}));

const { $api } = useNuxtApp();

const loadSystemBrand = async () => {
  try {
    const response = await $api.get("/system-settings/brand/public");
    // api.client.js response interceptor returns the JSON body directly.
    // Keep compatibility with either a direct body or an Axios response.
    const payload = response?.data?.success !== undefined
      ? response.data
      : response;
    const data = payload?.data || {};

    if (!payload?.success) return;

    systemBrand.value = {
      ...systemBrand.value,
      app_name: data.app_name || "NFCGo",
      system_logo_url: data.system_logo_public_url || data.system_logo_url || "",
      system_favicon_url: data.system_favicon_public_url || data.system_favicon_url || "",
      brandVersion: Date.now(),
    };

    if (systemBrand.value.system_favicon_url && typeof document !== "undefined") {
      let link = document.querySelector("link[rel~='icon']");
      if (!link) {
        link = document.createElement("link");
        link.rel = "icon";
        document.head.appendChild(link);
      }

      const url = systemBrand.value.system_favicon_url;
      link.href = url + (url.includes("?") ? "&" : "?") + "v=" + systemBrand.value.brandVersion;
      link.type = url.split("?")[0].endsWith(".svg")
        ? "image/svg+xml"
        : url.split("?")[0].endsWith(".png")
          ? "image/png"
          : "image/x-icon";
    }
  } catch (error) {
    console.warn("Public system branding unavailable; using defaults.");
  }
};


onMounted(() => {
  initTheme();

  const startedAt = Date.now();
  const MIN_VISIBLE_MS = 3000;
  const MAX_BRAND_WAIT_MS = 900;

  // Fetch the configured logo while the splash is visible, without
  // allowing a slow branding endpoint to block application entry.
  const brandRequest = loadSystemBrand();

  Promise.race([
    brandRequest,
    new Promise((resolve) => {
      window.setTimeout(resolve, MAX_BRAND_WAIT_MS);
    }),
  ]).finally(() => {
    const remaining = Math.max(0, MIN_VISIBLE_MS - (Date.now() - startedAt));

    window.setTimeout(() => {
      appBootLoading.value = false;
    }, remaining);
  });
});
</script>

<style>
/* Global styles are handled in assets/css/main.css */

.app-root {
  width: 100%;
  min-height: 100vh;
  overflow-x: hidden;
  position: relative;
}

/* Prevent layout shift and horizontal scroll */
* {
  box-sizing: border-box;
}

body {
  overflow-x: hidden;
  position: relative;
}

/* NFCGo signature boot experience */
.nfc-boot-screen {
  --boot-bg: #07111d;
  --boot-teal: #00BDE9;
  --boot-cyan: #00BDE9;
  position: fixed;
  inset: 0;
  z-index: 999999;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  isolation: isolate;
  padding: 28px;
  color: #f4f8fc;
  background:
    radial-gradient(ellipse at 50% 42%, rgba(0, 189, 233, 0.18), transparent 35%),
    radial-gradient(ellipse at 50% 100%, rgba(0, 113, 153, 0.18), transparent 52%),
    linear-gradient(145deg, #09202d 0%, #071722 48%, #041019 100%);
  font-family: inherit;
}

.nfc-boot-ambient {
  position: absolute;
  z-index: -1;
  width: min(68vw, 700px);
  aspect-ratio: 1;
  pointer-events: none;
  border-radius: 50%;
  filter: blur(2px);
}

.nfc-boot-ambient-one {
  top: -38%;
  right: -22%;
  background: radial-gradient(circle, rgba(0, 189, 233, 0.15), transparent 68%);
  animation: nfc-boot-ambient-drift 12s ease-in-out infinite alternate;
}

.nfc-boot-ambient-two {
  bottom: -50%;
  left: -22%;
  background: radial-gradient(circle, rgba(0, 146, 190, 0.14), transparent 67%);
  animation: nfc-boot-ambient-drift 15s ease-in-out infinite alternate-reverse;
}

.nfc-boot-grid {
  position: absolute;
  z-index: -1;
  inset: 0;
  pointer-events: none;
  opacity: 0.18;
  background-image:
    linear-gradient(rgba(142, 183, 202, 0.055) 1px, transparent 1px),
    linear-gradient(90deg, rgba(142, 183, 202, 0.055) 1px, transparent 1px);
  background-size: 52px 52px;
  mask-image: radial-gradient(ellipse at center, black 0%, transparent 78%);
  -webkit-mask-image: radial-gradient(ellipse at center, black 0%, transparent 78%);
}

.nfc-boot-frame {
  display: flex;
  width: min(100%, 480px);
  min-height: min(720px, calc(100dvh - 56px));
  flex-direction: column;
  justify-content: space-between;
  animation: nfc-boot-enter 800ms cubic-bezier(0.2, 0.8, 0.2, 1) both;
}

.nfc-boot-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding-top: 5px;
}

.nfc-boot-wordmark,
.nfc-boot-version {
  display: inline-flex;
  align-items: center;
  gap: 9px;
  color: rgba(221, 235, 244, 0.78);
  font-size: 10px;
  font-weight: 650;
  letter-spacing: 0.17em;
}

.nfc-boot-wordmark-dot {
  width: 7px;
  height: 7px;
  flex: 0 0 7px;
  border-radius: 50%;
  background: var(--boot-teal);
  box-shadow: 0 0 13px rgba(0, 189, 233, 0.85);
}

.nfc-boot-version {
  color: rgba(162, 187, 204, 0.55);
  font-size: 9px;
  letter-spacing: 0.13em;
}

.nfc-boot-main {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 28px 0 34px;
  text-align: center;
}

.nfc-boot-visual {
  position: relative;
  display: grid;
  width: min(76vw, 310px);
  aspect-ratio: 1;
  flex: 0 0 auto;
  place-items: center;
  margin-bottom: 26px;
}

.nfc-boot-orbit {
  position: absolute;
  border: 1px solid rgba(117, 197, 205, 0.12);
  border-radius: 50%;
  pointer-events: none;
}

.nfc-boot-orbit-outer {
  inset: 2%;
  border-color: rgba(0, 189, 233, 0.20);
  border-style: dashed;
  animation: nfc-boot-rotate 38s linear infinite;
}

.nfc-boot-orbit-middle {
  inset: 11%;
  border-color: rgba(0, 189, 233, 0.25);
  box-shadow: 0 0 34px rgba(0, 189, 233, 0.07), inset 0 0 34px rgba(0, 189, 233, 0.045);
  animation: nfc-boot-rotate-reverse 24s linear infinite;
}

.nfc-boot-orbit-inner {
  inset: 21%;
  border-color: rgba(0, 189, 233, 0.25);
  background: radial-gradient(circle, rgba(0, 189, 233, 0.09), transparent 72%);
}

.nfc-boot-orbit-dash {
  position: absolute;
  inset: 6%;
  border: 2px solid transparent;
  border-top-color: rgba(0, 189, 233, 0.95);
  border-right-color: rgba(0, 189, 233, 0.45);
  border-radius: 50%;
  filter: drop-shadow(0 0 6px rgba(0, 189, 233, 0.35));
  animation: nfc-boot-rotate 5s linear infinite;
}

.nfc-boot-core {
  position: relative;
  display: grid;
  width: 49%;
  aspect-ratio: 1;
  place-items: center;
  overflow: hidden;
  border: 1px solid rgba(0, 189, 233, 0.42);
  border-radius: 30%;
  background: linear-gradient(145deg, rgba(12, 65, 84, 0.96), rgba(5, 28, 41, 0.98));
  box-shadow:
    0 0 0 7px rgba(0, 189, 233, 0.045),
    0 0 0 15px rgba(0, 189, 233, 0.03),
    0 16px 60px rgba(0, 0, 0, 0.3),
    0 0 42px rgba(0, 189, 233, 0.16),
    inset 0 1px 0 rgba(255, 255, 255, 0.11);
  animation: nfc-boot-core-breathe 3.2s ease-in-out infinite;
}

.nfc-boot-core::before {
  position: absolute;
  inset: 0;
  border-radius: inherit;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), transparent 42%);
  content: "";
  pointer-events: none;
}

.nfc-boot-core-glow {
  position: absolute;
  inset: 10%;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(0, 189, 233, 0.20), transparent 72%);
}

.nfc-boot-logo-wrap {
  position: relative;
  display: grid;
  width: 82%;
  height: 82%;
  place-items: center;
}

.nfc-boot-logo {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: contain;
  filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.14));
}

.nfc-boot-fallback-logo {
  display: flex;
  align-items: baseline;
  justify-content: center;
  gap: 1px;
  white-space: nowrap;
  font-size: clamp(20px, 5.7vw, 29px);
  font-weight: 800;
  letter-spacing: -0.09em;
}

.nfc-boot-fallback-nfc {
  color: #f5fbff;
}

.nfc-boot-fallback-go {
  color: var(--boot-teal);
}

.nfc-boot-signal {
  position: absolute;
  top: 50%;
  display: grid;
  width: 40px;
  aspect-ratio: 1;
  place-items: center;
  color: rgba(0, 189, 233, 0.9);
  transform: translateY(-50%);
}

.nfc-boot-signal svg {
  width: 100%;
  height: 100%;
  stroke: currentColor;
  stroke-linecap: round;
  stroke-linejoin: round;
  stroke-width: 1.5;
}

.nfc-boot-signal-left {
  left: 1%;
  animation: nfc-boot-signal-pulse 2.4s ease-in-out infinite;
}

.nfc-boot-signal-right {
  right: 1%;
  animation: nfc-boot-signal-pulse 2.4s ease-in-out 1.2s infinite;
}

.nfc-boot-particle {
  position: absolute;
  width: 5px;
  aspect-ratio: 1;
  border-radius: 50%;
  background: var(--boot-teal);
  box-shadow: 0 0 12px rgba(0, 189, 233, 0.85);
}

.nfc-boot-particle-one {
  top: 17%;
  left: 27%;
  animation: nfc-boot-particle-float 3.8s ease-in-out infinite;
}

.nfc-boot-particle-two {
  right: 17%;
  bottom: 27%;
  width: 4px;
  background: var(--boot-cyan);
  box-shadow: 0 0 12px rgba(0, 189, 233, 0.85);
  animation: nfc-boot-particle-float 4.5s ease-in-out 0.6s infinite;
}

.nfc-boot-particle-three {
  right: 24%;
  top: 19%;
  width: 3px;
  animation: nfc-boot-particle-float 3.4s ease-in-out 1s infinite;
}

.nfc-boot-copy {
  max-width: 100%;
}

.nfc-boot-eyebrow {
  margin: 0 0 12px;
  color: var(--boot-teal);
  font-size: 9px;
  font-weight: 700;
  letter-spacing: 0.23em;
}

.nfc-boot-title {
  margin: 0;
  color: #f5f9fc;
  font-size: clamp(30px, 7vw, 43px);
  font-weight: 650;
  letter-spacing: -0.055em;
  line-height: 1.12;
  overflow-wrap: anywhere;
  text-shadow: 0 0 32px rgba(0, 189, 233, 0.12);
}

.nfc-boot-description {
  max-width: 290px;
  margin: 13px auto 0;
  color: rgba(189, 207, 220, 0.66);
  font-size: 13px;
  font-weight: 400;
  line-height: 1.8;
}

.nfc-boot-loading {
  width: min(100%, 236px);
  margin-top: 31px;
}

.nfc-boot-loading-track {
  position: relative;
  height: 3px;
  overflow: hidden;
  border-radius: 999px;
  background: rgba(0, 189, 233, 0.14);
}

.nfc-boot-loading-track span {
  position: absolute;
  inset: 0 auto 0 -38%;
  width: 38%;
  border-radius: inherit;
  background: linear-gradient(90deg, transparent, var(--boot-teal), var(--boot-cyan));
  box-shadow: 0 0 12px rgba(0, 189, 233, 0.65);
  animation: nfc-boot-loading-sweep 1.65s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

.nfc-boot-loading-label {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 13px;
  color: rgba(192, 211, 224, 0.67);
  font-size: 11px;
  letter-spacing: 0.025em;
}

.nfc-boot-loading-dot {
  width: 5px;
  height: 5px;
  flex: 0 0 5px;
  border-radius: 50%;
  background: var(--boot-teal);
  box-shadow: 0 0 9px rgba(0, 189, 233, 0.8);
  animation: nfc-boot-dot-pulse 1.6s ease-in-out infinite;
}

.nfc-boot-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 13px;
  padding-bottom: 5px;
  color: rgba(150, 177, 195, 0.43);
  font-size: 8px;
  font-weight: 650;
  letter-spacing: 0.2em;
  text-align: center;
}

.nfc-boot-footer-line {
  width: 25px;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(0, 189, 233, 0.4));
}

.nfc-boot-footer-line:last-child {
  transform: rotate(180deg);
}

@keyframes nfc-boot-enter {
  from { opacity: 0; transform: translateY(12px) scale(0.985); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

@keyframes nfc-boot-rotate {
  to { transform: rotate(360deg); }
}

@keyframes nfc-boot-rotate-reverse {
  to { transform: rotate(-360deg); }
}

@keyframes nfc-boot-core-breathe {
  0%, 100% { box-shadow: 0 0 0 7px rgba(0, 189, 233, 0.045), 0 0 0 15px rgba(0, 189, 233, 0.03), 0 16px 60px rgba(0, 0, 0, 0.3), 0 0 34px rgba(0, 189, 233, 0.13), inset 0 1px 0 rgba(255, 255, 255, 0.11); }
  50% { box-shadow: 0 0 0 9px rgba(0, 189, 233, 0.05), 0 0 0 19px rgba(0, 189, 233, 0.03), 0 16px 60px rgba(0, 0, 0, 0.3), 0 0 48px rgba(0, 189, 233, 0.24), inset 0 1px 0 rgba(255, 255, 255, 0.15); }
}

@keyframes nfc-boot-loading-sweep {
  from { transform: translateX(0); }
  to { transform: translateX(365%); }
}

@keyframes nfc-boot-dot-pulse {
  0%, 100% { opacity: 0.45; transform: scale(0.8); }
  50% { opacity: 1; transform: scale(1.15); }
}

@keyframes nfc-boot-signal-pulse {
  0%, 100% { opacity: 0.45; }
  50% { opacity: 1; }
}

@keyframes nfc-boot-particle-float {
  0%, 100% { opacity: 0.45; transform: translate3d(0, 0, 0); }
  50% { opacity: 1; transform: translate3d(0, -7px, 0); }
}

@keyframes nfc-boot-ambient-drift {
  from { transform: translate3d(-10px, -8px, 0) scale(0.96); }
  to { transform: translate3d(14px, 12px, 0) scale(1.04); }
}

.nfc-boot-fade-enter-active,
.nfc-boot-fade-leave-active {
  transition: opacity 280ms ease, filter 280ms ease;
}

.nfc-boot-fade-enter-from,
.nfc-boot-fade-leave-to {
  opacity: 0;
  filter: blur(5px);
}

@media (max-height: 700px) {
  .nfc-boot-frame {
    min-height: calc(100dvh - 36px);
  }

  .nfc-boot-main {
    padding: 12px 0 18px;
  }

  .nfc-boot-visual {
    width: min(58vw, 240px);
    margin-bottom: 14px;
  }

  .nfc-boot-loading {
    margin-top: 22px;
  }
}

@media (max-width: 480px) {
  .nfc-boot-screen {
    padding: 20px;
  }

  .nfc-boot-frame {
    min-height: calc(100dvh - 40px);
  }

  .nfc-boot-version {
    font-size: 8px;
    letter-spacing: 0.08em;
  }

  .nfc-boot-visual {
    width: min(78vw, 285px);
  }

  .nfc-boot-description {
    font-size: 12px;
  }

  .nfc-boot-footer {
    gap: 8px;
    font-size: 7px;
    letter-spacing: 0.14em;
  }

  .nfc-boot-footer-line {
    width: 16px;
  }
}

@media (prefers-reduced-motion: reduce) {
  .nfc-boot-frame,
  .nfc-boot-ambient,
  .nfc-boot-orbit-outer,
  .nfc-boot-orbit-middle,
  .nfc-boot-orbit-dash,
  .nfc-boot-core,
  .nfc-boot-signal,
  .nfc-boot-particle,
  .nfc-boot-loading-track span,
  .nfc-boot-loading-dot {
    animation: none !important;
  }

  .nfc-boot-fade-enter-active,
  .nfc-boot-fade-leave-active {
    transition: none !important;
    filter: none !important;
  }
}

</style>
