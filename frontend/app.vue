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
         Shows the official NFCGo logo-draw loader on an ink
         surface until the SPA is hydrated.
         ============================================== -->
    <Transition name="nfc-boot-fade" mode="out-in">
      <div
        v-if="appBootLoading"
        class="fixed inset-0 z-[999999] flex flex-col items-center justify-center"
        style="background-color: #0a0a0a !important; opacity: 1;"
      >
        <NFCGoWaveLoader
          variant="wave"
          size="xl"
          theme="dark"
          :showText="true"
          hintText="Loading application..."
        />
      </div>
    </Transition>

    <!-- ==============================================
         PAGE NAVIGATION LOADING BAR (top progress)
         Matches the system palette gradient: Ink → Brass → Deep Brass
         ============================================== -->
    <ClientOnly>
      <NuxtLoadingIndicator
        :height="4"
        :duration="2500"
        :throttle="0"
        color="linear-gradient(90deg, var(--theme-primary-600, #3d496a) 0%, var(--theme-accent, #5d8c87) 60%, var(--theme-accent-secondary, #b8956a) 100%)"
      />
    </ClientOnly>

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

// The browser favicon is hardcoded to the official NFCGo system logo so it
// always matches the system branding, regardless of admin-configured values.
// Kept in sync with the app icon declared in nuxt.config.ts.
const SYSTEM_FAVICON_URL = "/nfcgo-app-icon.svg";

const applySystemFavicon = () => {
  if (typeof document === "undefined") return;

  let link = document.querySelector("link[rel~='icon']");
  if (!link) {
    link = document.createElement("link");
    link.rel = "icon";
    document.head.appendChild(link);
  }

  link.href = SYSTEM_FAVICON_URL;
  link.type = "image/svg+xml";
};

onMounted(() => {
  initTheme();

  applySystemFavicon();

  // Minimum visible duration for the branded NFCGo logo-draw loader.
  // The animation completes one full cycle every 2s, so we keep the
  // loader on-screen for at least 2 seconds so users can see at least
  // one full draw cycle before the dashboard takes over.
  // This is intentional — UX research shows branded 1.8-2.2s splash
  // screens increase perceived platform quality without feeling slow.
  const MIN_VISIBLE_MS = 2000;

  // Hydration head-start detection:
  // - If __NUXT__ data is already attached (HMR / SPA cached reload),
  //   hydration is nearly instant but we still keep MIN_VISIBLE_MS.
  // - First paint (cold load) we add a tiny buffer on top.
  const hydrationBuffer =
    typeof window !== 'undefined' && window.__NUXT__ ? 0 : 120;

  const hideDelay = MIN_VISIBLE_MS + hydrationBuffer;

  setTimeout(() => {
    appBootLoading.value = false;
  }, hideDelay);
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

/* ============================================================
   APP BOOT TRANSITION — smooth fade-out for the splash screen
   after the NFCGo WiFi-wave animation has shown 1 full cycle.
   ============================================================ */
.nfc-boot-fade-enter-active,
.nfc-boot-fade-leave-active {
  transition:
    opacity 420ms ease,
    filter 420ms ease,
    transform 420ms cubic-bezier(0.22, 1, 0.36, 1);
}
.nfc-boot-fade-enter-from {
  opacity: 0;
  filter: blur(6px);
  transform: scale(1.04);
}
.nfc-boot-fade-leave-to {
  opacity: 0;
  filter: blur(8px);
  transform: scale(1.06);
}
</style>
