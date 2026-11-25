<!-- pages/Homepage/pricing.vue -->
<template>
  <section
    id="pricing"
    class="section ink-surface ink-grid relative overflow-hidden pt-28 sm:pt-32"
  >
    <div class="container relative">
      <div class="max-w-2xl mb-14" data-reveal>
        <span class="lp-kicker on-ink">Pricing</span>
        <h1 class="lp-h2 on-ink mt-4 mb-4">Simple, flexible pricing</h1>
        <p class="lp-lead lead-ink">
          Choose the plan that fits your networking needs. Start free and
          upgrade as you grow.
        </p>
      </div>
      <div
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto"
      >
        <!-- Free -->
        <div class="price-card" data-reveal>
          <h2 class="price-card__name">Free</h2>
          <div class="price-card__amount">RM0</div>
          <p class="price-card__desc">Perfect for getting started</p>
          <ul class="price-card__list">
            <li>
              <Icon name="heroicons:check" class="h-4 w-4 price-card__tick" />
              <span>1 Digital Profile</span>
            </li>
            <li>
              <Icon name="heroicons:check" class="h-4 w-4 price-card__tick" />
              <span>Basic Templates</span>
            </li>
            <li>
              <Icon name="heroicons:check" class="h-4 w-4 price-card__tick" />
              <span>Contact Information</span>
            </li>
            <li>
              <Icon name="heroicons:check" class="h-4 w-4 price-card__tick" />
              <span>Social Media Links</span>
            </li>
            <li>
              <Icon name="heroicons:check" class="h-4 w-4 price-card__tick" />
              <span>QR Code Backup</span>
            </li>
            <li>
              <Icon name="heroicons:check" class="h-4 w-4 price-card__tick" />
              <span>Basic Analytics</span>
            </li>
          </ul>
          <div class="price-card__cta">
            <NuxtLink to="/UserAccount/register" class="btn-ghost-light w-full">
              Get Started Free
            </NuxtLink>
          </div>
        </div>

        <!-- Basic -->
        <div class="price-card" data-reveal>
          <h2 class="price-card__name">Basic</h2>
          <div class="price-card__amount">
            {{
              loadingPrices
                ? 'Loading...'
                : (basicPlan?.currency + ' ' + basicPlan?.price) ||
                  'Contact for Pricing'
            }}
          </div>
          <p class="price-card__desc">
            {{ basicPlan?.description || 'For individual professionals' }}
          </p>
          <ul class="price-card__list">
            <li v-for="feature in basicPlan?.features" :key="feature">
              <Icon name="heroicons:check" class="h-4 w-4 price-card__tick" />
              <span>{{ feature }}</span>
            </li>
          </ul>
          <div class="price-card__cta">
            <button @click="buyPlan('basic')" class="btn-ghost-light w-full">
              Get Started
            </button>
          </div>
        </div>

        <!-- Premium -->
        <div class="price-card price-card--pop" data-reveal>
          <span class="price-card__badge">Most Popular</span>
          <h2 class="price-card__name">Premium</h2>
          <div class="price-card__amount">
            {{
              loadingPrices
                ? 'Loading...'
                : (premiumPlan?.currency + ' ' + premiumPlan?.price) ||
                  'Contact for Pricing'
            }}
          </div>
          <p class="price-card__desc">
            {{ premiumPlan?.description || 'For growing businesses' }}
          </p>
          <ul class="price-card__list">
            <li v-for="feature in premiumPlan?.features" :key="feature">
              <Icon name="heroicons:check" class="h-4 w-4 price-card__tick" />
              <span>{{ feature }}</span>
            </li>
          </ul>
          <div class="price-card__cta">
            <button @click="buyPlan('premium')" class="btn-brass w-full">
              Get Started
            </button>
          </div>
        </div>

        <!-- Business -->
        <div class="price-card" data-reveal>
          <h2 class="price-card__name">Business</h2>
          <div class="price-card__amount">
            {{
              loadingPrices
                ? 'Loading...'
                : (businessPlan?.currency + ' ' + businessPlan?.price) ||
                  'Contact for Pricing'
            }}
          </div>
          <p class="price-card__desc">
            {{ businessPlan?.description || 'For large organizations' }}
          </p>
          <ul class="price-card__list">
            <li v-for="feature in businessPlan?.features" :key="feature">
              <Icon name="heroicons:check" class="h-4 w-4 price-card__tick" />
              <span>{{ feature }}</span>
            </li>
          </ul>
          <div class="price-card__cta">
            <button @click="contactSupport" class="btn-ghost-light w-full">
              Contact Sales
            </button>
          </div>
        </div>
      </div>

      <div class="mt-12" data-reveal>
        <p class="text-white/55">
          Free plan: 14 days • No credit card required • Upgrade anytime
        </p>
        <div class="mt-3 flex flex-col sm:flex-row gap-2 items-start sm:items-center">
          <p class="text-sm text-white/65">Need a custom solution?</p>
          <button
            @click="contactSupport"
            class="text-[color:var(--brass)] hover:text-[color:var(--brass-200)] font-medium text-sm underline"
          >
            Contact our support team
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
definePageMeta({ layout: 'public' });

useSeoMeta({
  title: "Pricing - NFCGo Smart Digital Business Cards",
  description:
    "Compare NFCGo plans: Free, Basic, Premium and Business. Start with the Free plan — 14 days of access, no credit card required.",
});

useScrollReveal();

const { contactSupport } = usePublicUi();

// Paid plans (Basic / Premium) go straight to the checkout page with the
// chosen plan. Free keeps its own route to the registration page.
const buyPlan = (plan) => {
  if (process.client) {
    sessionStorage.setItem("selectedPlan", plan);
  }
  navigateTo("/UserDashboard/Payment");
};

// Plan prices from API
const { planPrices, loading: loadingPrices, loadPrices } = usePlanPrices();

const basicPlan = computed(() =>
  planPrices.value.find((p) => p.plan_type === 'basic')
);
const premiumPlan = computed(() =>
  planPrices.value.find((p) => p.plan_type === 'premium')
);
const businessPlan = computed(() =>
  planPrices.value.find((p) => p.plan_type === 'business')
);

onMounted(() => {
  loadPrices();
});
</script>
