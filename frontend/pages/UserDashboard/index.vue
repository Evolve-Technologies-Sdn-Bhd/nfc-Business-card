<!-- pages/UserDashboard/index.vue - User Dashboard Landing Page -->
<template>
  <div class="relative">
    <!-- Welcome Header -->
    <div class="mb-8">
      <h1 class="text-2xl sm:text-3xl font-bold text-secondary-900">
        Welcome back, {{ displayName }}
      </h1>
      <p class="mt-2 text-secondary-600">
        Here's what's happening with your NFC cards today.
      </p>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div v-for="stat in statsCards" :key="stat.name" class="card p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div :class="['p-3 rounded-xl', stat.bgColor]">
              <Icon :name="stat.icon" class="h-6 w-6 text-white" />
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-secondary-600">
              {{ stat.name }}
            </p>
            <p class="text-2xl font-semibold text-secondary-900">
              {{ stat.value }}
            </p>
          </div>
          <div class="ml-auto">
            <span v-if="stat.badge" :class="['px-2.5 py-1 text-xs font-semibold rounded-full', stat.badgeClass]">
              {{ stat.badge }}
            </span>
          </div>
        </div>
        <div class="mt-4">
          <div class="h-2 bg-secondary-100 rounded-full overflow-hidden">
            <div
              :class="['h-full rounded-full transition-all duration-700', stat.progressColor]"
              :style="{ width: stat.progress }"
            ></div>
          </div>
          <p class="mt-2 text-xs text-secondary-500">{{ stat.progressLabel }}</p>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-8">
      <h2 class="text-lg font-semibold text-secondary-900 mb-4">Quick Actions</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <NuxtLink
          v-for="action in quickActions"
          :key="action.label"
          :to="action.href"
          :class="[
            'group card p-5 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md flex flex-col h-full',
            action.disabled ? 'opacity-50 pointer-events-none cursor-not-allowed' : '',
          ]"
        >
          <div :class="['inline-flex p-2.5 rounded-xl flex-shrink-0 w-fit', action.iconBg]">
            <Icon :name="action.icon" :class="['h-5 w-5', action.iconText]" />
          </div>
          <div class="mt-4 flex-1 flex flex-col">
            <h3 class="text-sm font-semibold text-secondary-900 leading-snug">{{ action.label }}</h3>
            <p class="mt-1 text-xs text-secondary-500 leading-relaxed">{{ action.description }}</p>
          </div>
          <div v-if="action.disabled" class="mt-3 pt-3 border-t border-secondary-100">
            <span class="text-[11px] leading-relaxed font-medium text-secondary-500 block">{{ action.disabledMessage }}</span>
          </div>
        </NuxtLink>
      </div>
    </div>

    <!-- Next Steps / Plan Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Current Plan Card -->
      <div class="card p-6 lg:col-span-2">
        <div class="flex items-start justify-between">
          <div>
            <h3 class="text-lg font-semibold text-secondary-900">Current Plan</h3>
            <p class="mt-1 text-sm text-secondary-500">Your subscription details and status</p>
          </div>
          <span :class="['px-3 py-1 text-xs font-bold rounded-full uppercase tracking-wide', planBadgeClass]">
            {{ planLabel }}
          </span>
        </div>
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="p-4 rounded-xl bg-secondary-50 border border-secondary-100">
            <p class="text-xs font-medium text-secondary-500">Subscription</p>
            <p class="mt-1 text-base font-semibold text-secondary-900">{{ subscriptionStatus }}</p>
          </div>
          <div class="p-4 rounded-xl bg-secondary-50 border border-secondary-100">
            <p class="text-xs font-medium text-secondary-500">Active Cards</p>
            <p class="mt-1 text-base font-semibold text-secondary-900">{{ nfcCardsCount }}</p>
          </div>
          <div class="p-4 rounded-xl bg-secondary-50 border border-secondary-100">
            <p class="text-xs font-medium text-secondary-500">Builder Access</p>
            <p class="mt-1 text-base font-semibold text-secondary-900">{{ builderAccessStatus }}</p>
          </div>
        </div>
        <div v-if="resolvedPlan !== 'business'" class="mt-6 pt-6 border-t border-secondary-100 flex items-center justify-between">
          <div class="max-w-[62%]">
            <p class="text-sm font-semibold text-secondary-900">
              {{ resolvedPlan === 'premium' ? 'Need more features?' : 'Ready to unlock more?'}}
            </p>
            <p class="mt-0.5 text-xs text-secondary-500 leading-relaxed">
              {{ resolvedPlan === 'premium'
                ? 'Upgrade to Business for team seats and full analytics.'
                : 'Upgrade to Premium for Profile Builder, custom branding, and unlimited taps.' }}
            </p>
          </div>
          <NuxtLink
            to="/UserDashboard/PlanSelection"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 transition-all shadow-sm hover:shadow whitespace-nowrap flex-shrink-0"
          >
            <Icon name="heroicons:arrow-trending-up" class="h-4 w-4" />
            <span class="truncate max-w-[180px]">
              {{ resolvedPlan === 'premium' ? 'Upgrade to Business' : 'Upgrade to Premium' }}
            </span>
          </NuxtLink>
        </div>
      </div>

      <!-- Getting Started -->
      <div class="card p-6">
        <h3 class="text-lg font-semibold text-secondary-900">Getting Started</h3>
        <p class="mt-1 text-sm text-secondary-500">Complete these steps</p>
        <div class="mt-5 space-y-3">
          <div v-for="(step, idx) in gettingStartedSteps" :key="idx" class="flex items-start gap-3">
            <div :class="[
              'flex-shrink-0 mt-0.5 h-5 w-5 rounded-full flex items-center justify-center text-xs font-bold',
              step.completed ? 'bg-success-100 text-success-700' : 'bg-secondary-100 text-secondary-500'
            ]">
              <Icon v-if="step.completed" name="heroicons:check" class="h-3 w-3" />
              <span v-else>{{ idx + 1 }}</span>
            </div>
            <div class="flex-1 min-w-0">
              <p :class="['text-sm font-medium', step.completed ? 'text-secondary-500 line-through' : 'text-secondary-900']">
                {{ step.label }}
              </p>
              <p v-if="step.description" class="text-xs text-secondary-500 mt-0.5">{{ step.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from "~/stores/auth";

definePageMeta({
  layout: "user-dashboard",
  middleware: ["auth"],
});

const authStore = useAuthStore();
const router = useRouter();

const displayName = computed(() => {
  const u = authStore.user;
  return u?.full_name || u?.name || u?.email?.split('@')[0] || 'User';
});

const resolvedPlan = computed(() => authStore.resolvedSubscriptionPlan || 'free');

const planLabel = computed(() => {
  const p = resolvedPlan.value;
  return p.charAt(0).toUpperCase() + p.slice(1);
});

const planBadgeClass = computed(() => {
  const map = {
    free: 'bg-secondary-100 text-secondary-700',
    basic: 'bg-blue-100 text-blue-700',
    premium: 'bg-purple-100 text-purple-700',
    business: 'bg-green-100 text-green-700',
  };
  return map[resolvedPlan.value] || map.free;
});

const nfcCardsCount = computed(() => {
  const cards = authStore.user?.nfc_cards ?? authStore.user?.nfcCards;
  return Array.isArray(cards) ? cards.length : 0;
});

const subscriptionStatus = computed(() => {
  if (authStore.user?.subscription_active === true) {
    return 'Active';
  }
  return authStore.hasAnyPhysicalNfcCard ? 'Pending Activation' : 'Not Started';
});

const builderAccessStatus = computed(() => {
  if (authStore.canAccessPaidProfileBuilder()) {
    return 'Unlocked';
  }
  return 'Locked';
});

const canAccessAnalytics = computed(() => {
  return authStore.user?.subscription_active === true || authStore.hasAccessPaidBuilderCards;
});

const statsCards = computed(() => {
  const cards = [
    {
      name: 'NFC Cards',
      value: nfcCardsCount.value,
      icon: 'heroicons:credit-card',
      bgColor: 'bg-primary-500',
      badge: nfcCardsCount.value > 0 ? 'On Track' : 'Start',
      badgeClass: nfcCardsCount.value > 0 ? 'bg-success-100 text-success-700' : 'bg-warning-100 text-warning-700',
      progress: nfcCardsCount.value > 0 ? '75%' : '10%',
      progressColor: 'bg-primary-500',
      progressLabel: nfcCardsCount.value > 0 ? `${nfcCardsCount.value} card(s) linked` : 'Order your first card',
    },
    {
      name: 'Subscription',
      value: planLabel.value,
      icon: 'heroicons:sparkles',
      bgColor: 'bg-success-500',
      badge: authStore.user?.subscription_active ? 'Active' : (authStore.hasAnyPhysicalNfcCard ? 'Pending' : 'Inactive'),
      badgeClass: authStore.user?.subscription_active ? 'bg-success-100 text-success-700' : (authStore.hasAnyPhysicalNfcCard ? 'bg-warning-100 text-warning-700' : 'bg-secondary-100 text-secondary-600'),
      progress: authStore.user?.subscription_active ? '100%' : (authStore.hasAnyPhysicalNfcCard ? '50%' : '0%'),
      progressColor: 'bg-success-500',
      progressLabel: authStore.user?.subscription_active ? 'All features available' : (authStore.hasAnyPhysicalNfcCard ? 'Payment pending verification' : 'Choose a plan to start'),
    },
    {
      name: 'Profile Builder',
      value: authStore.canAccessPaidProfileBuilder() ? 'Ready' : 'Locked',
      icon: 'heroicons:user',
      bgColor: 'bg-warning-500',
      badge: authStore.canAccessPaidProfileBuilder() ? 'Open' : 'Locked',
      badgeClass: authStore.canAccessPaidProfileBuilder() ? 'bg-success-100 text-success-700' : 'bg-error-100 text-error-700',
      progress: authStore.canAccessPaidProfileBuilder() ? '100%' : '0%',
      progressColor: 'bg-warning-500',
      progressLabel: authStore.canAccessPaidProfileBuilder() ? 'Customize your profile' : 'Verify payment first',
    },
    {
      name: 'Analytics',
      value: canAccessAnalytics.value ? 'Live' : 'Soon',
      icon: 'heroicons:chart-bar',
      bgColor: 'bg-info-500',
      badge: canAccessAnalytics.value ? 'View' : 'Locked',
      badgeClass: canAccessAnalytics.value ? 'bg-success-100 text-success-700' : 'bg-secondary-100 text-secondary-600',
      progress: canAccessAnalytics.value ? '100%' : '0%',
      progressColor: 'bg-info-500',
      progressLabel: canAccessAnalytics.value ? 'Track profile visits & taps' : 'Activates after payment verified',
    },
  ];
  return cards;
});

const cardManagementHref = computed(() => {
  return resolvedPlan.value === 'business'
    ? '/UserDashboard/UserManagement/BusinessPlanUser/BusinessCardManagement'
    : '/UserDashboard/CardManagement';
});

const profileBuilderHref = computed(() => {
  const p = resolvedPlan.value;
  if (p === 'business') return '/UserDashboard/UserManagement/BusinessPlanUser/BusinessProfileBuilder';
  if (p === 'premium') return '/UserDashboard/UserManagement/PremiumPlanUser/PremiumProfileBuilder';
  if (p === 'basic') return '/UserDashboard/UserManagement/BasicPlanUser/BasicProfileBuilder';
  return '/UserDashboard/UserManagement/FreePlanUser/FreeProfileBuilder';
});

const analyticsHref = computed(() => {
  return resolvedPlan.value === 'business'
    ? '/UserDashboard/UserManagement/BusinessPlanUser/BusinessAnalytics'
    : resolvedPlan.value === 'premium'
      ? '/UserDashboard/UserManagement/PremiumPlanUser/PremiumAnalytics'
      : '/UserDashboard/Analytics';
});

const quickActions = computed(() => {
  const actions = [
    {
      label: 'Manage Cards',
      description: 'Order, activate, track NFC cards',
      icon: 'heroicons:credit-card',
      iconBg: 'bg-primary-100',
      iconText: 'text-primary-600',
      href: cardManagementHref.value,
      disabled: false,
    },
    {
      label: 'Profile Builder',
      description: 'Customize your landing page',
      icon: 'heroicons:user',
      iconBg: 'bg-success-100',
      iconText: 'text-success-600',
      href: profileBuilderHref.value,
      disabled: !authStore.canAccessPaidProfileBuilder(),
      disabledMessage: 'Complete payment first to unlock',
    },
    {
      label: 'Analytics',
      description: 'View visits, taps & insights',
      icon: 'heroicons:chart-bar',
      iconBg: 'bg-info-100',
      iconText: 'text-info-600',
      href: analyticsHref.value,
      disabled: !canAccessAnalytics.value,
      disabledMessage: 'Activates after card verified',
    },
    {
      label: 'Settings',
      description: 'Account & preferences',
      icon: 'heroicons:cog-6-tooth',
      iconBg: 'bg-secondary-100',
      iconText: 'text-secondary-600',
      href: '/UserDashboard/Settings',
      disabled: false,
    },
  ];
  return actions;
});

const gettingStartedSteps = computed(() => {
  const hasCard = authStore.hasAnyPhysicalNfcCard;
  const paidBuilder = authStore.canAccessPaidProfileBuilder();
  const subActive = authStore.user?.subscription_active === true;
  return [
    {
      label: 'Choose a subscription plan',
      description: 'Free, Basic, Premium, or Business',
      completed: subActive || hasCard,
    },
    {
      label: 'Order your NFC card',
      description: 'Physical card shipped to you',
      completed: hasCard,
    },
    {
      label: 'Payment verified by admin',
      description: 'Unlocks Profile Builder',
      completed: paidBuilder,
    },
    {
      label: 'Build your profile page',
      description: 'Customize links, info & links',
      completed: false,
    },
    {
      label: 'Share & start networking',
      description: 'Tap, share & track analytics',
      completed: false,
    },
  ];
});
</script>
