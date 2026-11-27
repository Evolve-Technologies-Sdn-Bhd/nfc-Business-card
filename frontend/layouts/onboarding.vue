<!--
  layouts/onboarding.vue
  Shared chrome for the self-serve onboarding funnel. Reuses the Ink + Brass
  language of the marketing funnel so onboarding and marketing read as ONE
  product. The step indicator derives the current step from the route.
-->
<template>
  <div class="min-h-screen ink-surface ink-grid relative overflow-hidden">
    <header class="ink-topbar sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <NuxtLink to="/Homepage" class="inline-flex items-center">
            <NFCGoLogo variant="dark" size="sm" />
          </NuxtLink>

          <div class="flex items-center gap-3 sm:gap-5">
            <OnboardingStepper />

            <NuxtLink
              to="/UserDashboard"
              class="hidden sm:inline-flex items-center gap-1.5 text-micro uppercase text-white/55 hover:text-white transition-colors"
              style="letter-spacing: 0.16em"
            >
              Exit setup
              <Icon name="heroicons:x-mark" class="h-4 w-4" />
            </NuxtLink>
          </div>
        </div>
      </div>
    </header>

    <main class="relative">
      <slot />
    </main>
  </div>
</template>
