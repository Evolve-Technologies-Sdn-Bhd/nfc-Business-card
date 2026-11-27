<!-- pages/UserDashboard/index.vue — portal home for Free / Basic / Premium / Business.
     Everything shown is real account state: plan + status from /me (auth store),
     plan inclusions from /plan-prices/public, reach from the analytics endpoint
     the user is entitled to, and team seats from /business/card-quota. -->
<template>
  <div>
    <AdminPageHeader
      :eyebrow="eyebrow"
      eyebrow-icon="heroicons:sparkles"
      :title="`Welcome back, ${firstName}`"
      :description="headline"
    >
      <template #actions>
        <!-- Mobile only: md+ uses the same button in the portal top bar.
             The wrapper owns visibility because .admin-btn sets display. -->
        <span v-if="publicProfileUrl" class="inline-flex md:hidden">
          <a :href="publicProfileUrl" target="_blank" rel="noopener" class="admin-btn admin-btn--outline">
            <Icon name="heroicons:arrow-top-right-on-square" class="h-4 w-4" />
            View public profile
          </a>
        </span>
        <NuxtLink :to="primaryAction.to" class="admin-btn admin-btn--primary">
          <Icon :name="primaryAction.icon" class="h-4 w-4" />
          {{ primaryAction.label }}
        </NuxtLink>
      </template>
    </AdminPageHeader>

    <!-- Attention items (only real, actionable states) -->
    <div v-if="alerts.length" class="mb-6 space-y-3">
      <div
        v-for="alert in alerts"
        :key="alert.key"
        class="portal-alert"
        :class="`portal-alert--${alert.tone}`"
        role="status"
      >
        <Icon :name="alert.icon" class="mt-0.5 h-5 w-5 shrink-0" />
        <div class="min-w-0 flex-1">
          <p class="text-body font-semibold">{{ alert.title }}</p>
          <p class="text-body-sm opacity-90">{{ alert.text }}</p>
        </div>
        <NuxtLink v-if="alert.to" :to="alert.to" class="admin-btn admin-btn--sm admin-btn--outline shrink-0 bg-white">
          {{ alert.cta }}
        </NuxtLink>
      </div>
    </div>

    <!-- KPIs -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <AdminStatTile
        label="NFC cards"
        :value="access.cards.length"
        icon="heroicons:credit-card"
        :hint="cardsHint"
      />
      <AdminStatTile
        label="Profile Builder"
        :value="access.can.builder ? 'Unlocked' : 'Locked'"
        icon="heroicons:identification"
        :hint="access.can.builder ? `${access.planLabel} builder` : 'Unlocks after payment is verified'"
      />
      <AdminStatTile
        label="Taps · last 30 days"
        :value="access.can.analytics ? formatNumber(reach.taps) : '—'"
        icon="heroicons:cursor-arrow-rays"
        :loading="access.can.analytics && reach.loading"
        :hint="reachHint"
      />
      <AdminStatTile
        v-if="access.canManageBusiness && team.loaded"
        :label="team.total > 0 ? 'Team seats' : 'Team members'"
        :value="team.total > 0 ? `${team.used}/${team.total}` : team.used"
        icon="heroicons:users"
        :hint="
          team.total > 0
            ? `${team.available} seat${team.available === 1 ? '' : 's'} available`
            : 'No seat quota set on this account yet'
        "
      />
      <AdminStatTile
        v-else
        label="Unread notifications"
        :value="unreadCount"
        icon="heroicons:bell"
        :hint="unreadCount > 0 ? 'Open the bell to review' : 'You are all caught up'"
      />
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <!-- Cards -->
      <AdminSectionCard
        class="lg:col-span-2"
        :title="access.isBusiness ? 'Business cards' : 'Your cards'"
        :subtitle="access.cards.length ? 'Status of every card on your account' : ''"
        flush
      >
        <template v-if="access.cards.length" #header-actions>
          <NuxtLink :to="access.routes.cards" class="admin-btn admin-btn--ghost admin-btn--sm">
            Manage cards
            <Icon name="heroicons:arrow-right" class="h-4 w-4" />
          </NuxtLink>
        </template>

        <ul v-if="access.cards.length" class="divide-y divide-secondary-100">
          <li v-for="card in visibleCards" :key="card.id" class="flex items-center gap-4 px-6 py-4">
            <span class="portal-card-thumb" :class="`is-${String(card.subscription_plan || 'free').toLowerCase()}`">
              <Icon name="heroicons:wifi" class="h-4 w-4 rotate-90" />
            </span>
            <div class="min-w-0 flex-1">
              <p class="truncate text-body font-semibold text-secondary-900">
                {{ card.card_name || card.name || `Card #${card.card_number || card.id}` }}
              </p>
              <p class="truncate text-caption text-secondary-500">
                {{ planName(card.subscription_plan) }} card
                <template v-if="card.nfc_card_id"> · {{ card.nfc_card_id }}</template>
              </p>
            </div>
            <AdminStatusBadge :status="String(card.status || '').toLowerCase()" :label="card.status_label || card.status" />
          </li>
        </ul>
        <p
          v-if="access.cards.length > visibleCards.length"
          class="border-t border-secondary-100 px-6 py-3 text-caption text-secondary-500"
        >
          + {{ access.cards.length - visibleCards.length }} more on the
          <NuxtLink :to="access.routes.cards" class="font-semibold text-primary-600">cards page</NuxtLink>
        </p>

        <AdminEmptyState
          v-if="!access.cards.length"
          icon="heroicons:credit-card"
          title="No NFC card yet"
          :description="
            access.isFree
              ? 'Your digital profile works without a card. Add a physical NFC card to share it with a single tap.'
              : 'Order a card to start sharing your profile with a tap.'
          "
        >
          <template #actions>
            <NuxtLink v-if="access.can.builder" :to="access.routes.builder" class="admin-btn admin-btn--outline">
              Edit my profile
            </NuxtLink>
            <NuxtLink :to="access.isBusiness ? access.routes.cards : '/Onboarding/plan'" class="admin-btn admin-btn--primary">
              {{ access.isBusiness ? 'Order cards' : 'Get an NFC card' }}
            </NuxtLink>
          </template>
        </AdminEmptyState>
      </AdminSectionCard>

      <!-- Plan -->
      <AdminSectionCard title="Your plan" accent>
        <template #header-actions>
          <AdminStatusBadge :status="access.plan" :label="access.planLabel" />
        </template>

        <div class="flex items-center gap-2 text-body">
          <span class="portal-dot" :class="`is-${planStatus.tone}`"></span>
          <span class="font-medium text-secondary-800">{{ planStatus.label }}</span>
        </div>

        <p v-if="access.businessRole" class="mt-2 text-body-sm text-secondary-600">
          Your role: <span class="font-semibold text-secondary-800">{{ roleLabel }}</span>
          <template v-if="access.isBusinessEmployee"> in your company's Business account</template>
        </p>

        <div v-if="planFeatures.length" class="mt-5">
          <p class="admin-kpi-label mb-2">Included</p>
          <ul class="space-y-2">
            <li v-for="feature in planFeatures" :key="feature" class="flex items-start gap-2 text-body text-secondary-700">
              <Icon name="heroicons:check-circle" class="mt-0.5 h-4 w-4 shrink-0 text-accent-600" />
              <span>{{ feature }}</span>
            </li>
          </ul>
        </div>
        <p v-else-if="access.isFree" class="mt-4 text-body text-secondary-600">
          The Free plan gives you a digital profile you can share by link or QR code. Paid plans add a physical NFC card and more profile sections.
        </p>

        <template v-if="!access.isBusiness" #footer>
          <NuxtLink to="/Onboarding/plan" class="admin-btn admin-btn--outline w-full justify-center">
            <Icon name="heroicons:arrows-up-down" class="h-4 w-4" />
            {{ access.isFree ? 'See paid plans' : 'Compare plans' }}
          </NuxtLink>
        </template>
      </AdminSectionCard>
    </div>

    <!-- Shortcuts -->
    <h2 class="admin-kpi-label mb-3 mt-8">Shortcuts</h2>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <component
        :is="item.locked ? 'button' : NuxtLinkComp"
        v-for="item in shortcuts"
        :key="item.label"
        v-bind="item.locked ? { type: 'button' } : { to: item.to }"
        class="portal-shortcut"
        :class="{ 'is-locked': item.locked }"
        @click="item.locked && $toast.info(item.lockedMessage)"
      >
        <span class="admin-kpi-ico">
          <Icon :name="item.locked ? 'heroicons:lock-closed' : item.icon" class="h-5 w-5" />
        </span>
        <span class="mt-3 block text-body font-semibold text-secondary-900">{{ item.label }}</span>
        <span class="mt-0.5 block text-body-sm text-secondary-600">
          {{ item.locked ? item.lockedMessage : item.description }}
        </span>
      </component>
    </div>

    <!-- Setup progress (hidden once everything is done) -->
    <AdminSectionCard v-if="setupSteps.some((s) => !s.done)" class="mt-8" title="Set up your NFCGo" :subtitle="`${completedSteps} of ${setupSteps.length} done`">
      <ol class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
        <li v-for="(step, idx) in setupSteps" :key="step.label" class="portal-step" :class="{ 'is-done': step.done }">
          <span class="portal-step-num">
            <Icon v-if="step.done" name="heroicons:check" class="h-3.5 w-3.5" />
            <template v-else>{{ idx + 1 }}</template>
          </span>
          <div class="min-w-0">
            <p class="text-body font-semibold text-secondary-900">{{ step.label }}</p>
            <p class="text-body-sm text-secondary-600">{{ step.description }}</p>
            <NuxtLink v-if="!step.done && step.to" :to="step.to" class="mt-1 inline-flex text-body-sm font-semibold text-primary-600">
              {{ step.cta }} →
            </NuxtLink>
          </div>
        </li>
      </ol>
    </AdminSectionCard>
  </div>
</template>

<script setup>
import { resolveComponent } from "vue";
import { PORTAL_ROUTES, portalPlanStatus } from "~/composables/usePortalAccess";

definePageMeta({
  layout: "user-dashboard",
  middleware: ["auth"],
});

useHead({ title: "Dashboard — NFCGo" });

const NuxtLinkComp = resolveComponent("NuxtLink");
const { $api } = useNuxtApp();
const authStore = useAuthStore();
const access = usePortalAccess();
const { planPrices, loadPrices } = usePlanPrices();
const { unreadCount, loadUnreadCount } = useNotifications();

const PLAN_NAMES = { free: "Free", basic: "Basic", premium: "Premium", business: "Business" };
const planName = (p) => PLAN_NAMES[String(p || "free").toLowerCase()] || "Free";
const ROLE_LABELS = { owner: "Owner", admin: "Admin", card_manager: "Card manager", member: "Member" };

const firstName = computed(() => {
  const u = authStore.user;
  return u?.first_name || u?.full_name?.split(" ")[0] || u?.email?.split("@")[0] || "there";
});

const roleLabel = computed(() => ROLE_LABELS[access.value.businessRole] || "Member");
const planStatus = computed(() => portalPlanStatus(access.value));

const eyebrow = computed(() =>
  access.value.businessRole ? `Business · ${roleLabel.value}` : `${access.value.planLabel} plan`,
);

const headline = computed(() => {
  const a = access.value;
  if (a.isBusiness && a.canManageBusiness) return "Manage your team's cards, profiles and reach from one place.";
  if (a.isBusiness) return "Keep your business profile up to date and track how it is being shared.";
  if (!a.cards.length) return "Your digital profile is ready to build. Add an NFC card whenever you want to share it with a tap.";
  return "Here is how your cards and profile are doing.";
});

const cardsHint = computed(() => {
  const a = access.value;
  if (!a.cards.length) return "No card ordered yet";
  const active = a.cards.filter((c) => String(c.status).toLowerCase() === "active").length;
  return `${active} active${a.pendingCards.length ? ` · ${a.pendingCards.length} awaiting payment` : ""}`;
});

// Same URL rules as the portal shell's preview (first card)
const publicProfileUrl = computed(() => {
  const first = access.value.cards[0];
  if (!first) return null;
  return typeof first.public_url === "string" && first.public_url.startsWith("/profile/")
    ? first.public_url
    : null;
});

const visibleCards = computed(() => access.value.cards.slice(0, 5));

const planFeatures = computed(() => {
  const row = planPrices.value.find((p) => p.plan_type === access.value.plan);
  return Array.isArray(row?.features) ? row.features.slice(0, 6) : [];
});

const primaryAction = computed(() => {
  const a = access.value;
  if (a.pendingCards.length) return { label: "Complete payment", to: a.routes.cards, icon: "heroicons:credit-card" };
  if (a.can.builder) return { label: "Edit profile", to: a.routes.builder, icon: "heroicons:pencil-square" };
  return { label: "Manage cards", to: a.routes.cards, icon: "heroicons:credit-card" };
});

// ---------------------------------------------------------------------------
// Alerts — derived from card / subscription status only
// ---------------------------------------------------------------------------
const alerts = computed(() => {
  const a = access.value;
  const list = [];
  if (a.deactivated) {
    list.push({
      key: "deactivated",
      tone: "danger",
      icon: "heroicons:exclamation-triangle",
      title: "Your free window has ended",
      text: "Your profile is no longer active. Choose a plan with an NFC card to reactivate it.",
      to: "/Onboarding/plan",
      cta: "Choose a plan",
    });
  }
  const awaitingProof = a.cards.filter((c) => String(c.status).toLowerCase() === "pending_payment");
  if (awaitingProof.length) {
    list.push({
      key: "pending",
      tone: "warning",
      icon: "heroicons:clock",
      title: `${awaitingProof.length} card order${awaitingProof.length > 1 ? "s" : ""} waiting for payment`,
      text: "Finish payment or upload your transfer receipt so we can verify it.",
      to: a.routes.cards,
      cta: "Go to cards",
    });
  }
  const verifying = a.cards.filter((c) => String(c.status).toLowerCase() === "awaiting_payment_verification");
  if (verifying.length) {
    list.push({
      key: "verifying",
      tone: "info",
      icon: "heroicons:shield-check",
      title: "Payment under review",
      text: "We have your payment proof and will notify you once it is verified.",
    });
  }
  const arrived = a.cards.filter((c) => ["shipped", "delivered"].includes(String(c.status).toLowerCase()));
  if (arrived.length) {
    list.push({
      key: "arrived",
      tone: "info",
      icon: "heroicons:truck",
      title: "Your card is on its way",
      text: "Once it arrives, confirm receipt on the cards page to activate it.",
      to: a.routes.cards,
      cta: "Activate card",
    });
  }
  return list;
});

// ---------------------------------------------------------------------------
// Shortcuts — lock state comes from usePortalAccess
// ---------------------------------------------------------------------------
const shortcuts = computed(() => {
  const a = access.value;
  const items = [
    {
      label: "Profile Builder",
      description: "Edit what people see when they tap your card",
      icon: "heroicons:identification",
      to: a.routes.builder,
      locked: !a.can.builder,
      lockedMessage: "Unlocks once your card payment is verified.",
    },
    {
      label: "Analytics",
      description: "Taps, visitors and where they come from",
      icon: "heroicons:chart-bar",
      to: a.routes.analytics,
      locked: !a.can.analytics,
      lockedMessage: "Available once your plan is active.",
    },
  ];
  if (a.can.employees) {
    items.push({
      label: "Team Members",
      description: "Add people, roles and card assignments",
      icon: "heroicons:users",
      to: PORTAL_ROUTES.employees,
    });
  } else {
    items.push({
      label: a.isBusiness ? "Business Cards" : "My Cards",
      description: "Order, track and activate cards",
      icon: "heroicons:credit-card",
      to: a.routes.cards,
    });
  }
  items.push({
    label: "Settings",
    description: "Account, security and notifications",
    icon: "heroicons:cog-6-tooth",
    to: PORTAL_ROUTES.settings,
  });
  return items;
});

// ---------------------------------------------------------------------------
// Setup progress — each step reads a real field
// ---------------------------------------------------------------------------
const setupSteps = computed(() => {
  const a = access.value;
  const u = authStore.user || {};
  const statuses = a.cards.map((c) => String(c.status).toLowerCase());
  const steps = [
    {
      label: "Verify your email",
      description: "Keeps your account secure",
      done: !!u.email_verified_at,
      to: PORTAL_ROUTES.settings,
      cta: "Open settings",
    },
  ];
  if (!a.isBusinessEmployee) {
    steps.push({
      label: "Get an NFC card",
      description: "Share your profile with a tap",
      done: a.cards.length > 0,
      to: a.isBusiness ? a.routes.cards : "/Onboarding/plan",
      cta: "Choose a card",
    });
    steps.push({
      label: "Payment verified",
      description: "Unlocks your plan's Profile Builder",
      done: a.entitledCards.length > 0,
      to: a.cards.length ? a.routes.cards : null,
      cta: "Check payment",
    });
  }
  steps.push({
    label: "Activate your card",
    description: "Confirm it arrived and switch it on",
    done: statuses.includes("active"),
    to: a.cards.length ? a.routes.cards : null,
    cta: "Activate",
  });
  return steps;
});
const completedSteps = computed(() => setupSteps.value.filter((s) => s.done).length);

// ---------------------------------------------------------------------------
// Live data
// ---------------------------------------------------------------------------
const reach = reactive({ taps: 0, visitors: 0, growth: null, loading: false });
const team = reactive({ loaded: false, used: 0, total: 0, available: 0 });

const formatNumber = (n) => Number(n || 0).toLocaleString();

const reachHint = computed(() => {
  if (!access.value.can.analytics) return "Available once your plan is active";
  if (reach.loading) return "";
  const v = `${formatNumber(reach.visitors)} unique visitor${reach.visitors === 1 ? "" : "s"}`;
  return v;
});

const loadReach = async () => {
  const a = access.value;
  if (!a.can.analytics) return;
  reach.loading = true;
  try {
    const endpoint =
      a.routes.analytics === PORTAL_ROUTES.businessAnalytics ? "/analytics/business/overview" : "/analytics/user/overview";
    const res = await $api.get(endpoint, { params: { period: "30d" } });
    const d = res?.data || {};
    reach.taps = d.total_taps ?? d.overview?.total_taps ?? 0;
    reach.visitors = d.unique_visitors ?? d.overview?.unique_visitors ?? 0;
  } catch (e) {
    // Leave the tile at 0 — the analytics page shows the full error state.
    console.warn("Dashboard reach unavailable:", e?.message || e);
  } finally {
    reach.loading = false;
  }
};

const loadTeam = async () => {
  if (!access.value.canManageBusiness) return;
  try {
    const res = await $api.get("/business/card-quota");
    const d = res?.data;
    const info = d?.account_info;
    if (res?.success && info) {
      // Same figures the Team Members page shows (QuotaController@getCardQuota)
      team.used = info.employees_count ?? 0;
      team.total = info.total_account_slots ?? d.total_card_quota ?? 0;
      team.available = info.available_account_slots ?? Math.max(0, team.total - team.used);
      team.loaded = true;
    }
  } catch (e) {
    console.warn("Team quota unavailable:", e?.message || e);
  }
};

onMounted(async () => {
  // Refresh /me so card status changes made elsewhere (payment webhook,
  // admin verification) show up without a full reload.
  try {
    await authStore.fetchProfile();
  } catch {}
  loadPrices();
  loadUnreadCount();
  loadReach();
  loadTeam();
});
</script>

<style scoped>
.portal-alert {
  display: flex;
  align-items: flex-start;
  gap: 0.85rem;
  padding: 0.95rem 1.1rem;
  border-radius: var(--admin-radius);
  border: 1px solid;
}
.portal-alert--warning { background: #fffbeb; border-color: #fde68a; color: #92400e; }
.portal-alert--danger { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
.portal-alert--info { background: #f7f3ea; border-color: var(--admin-border-strong); color: var(--admin-text); }

.portal-card-thumb {
  display: inline-flex;
  height: 2.5rem;
  width: 3.75rem;
  flex-shrink: 0;
  align-items: center;
  justify-content: flex-end;
  padding-right: 0.55rem;
  border-radius: 0.5rem;
  color: color-mix(in srgb, var(--admin-paper) 80%, transparent);
  background: linear-gradient(135deg, var(--admin-ink-soft) 0%, var(--admin-ink) 100%);
  box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--brass) 25%, transparent);
}
.portal-card-thumb.is-premium,
.portal-card-thumb.is-business {
  color: var(--admin-brass-ink);
  background: linear-gradient(135deg, var(--admin-brass-soft) 0%, var(--admin-brass-deep) 100%);
}
.portal-card-thumb.is-free {
  color: var(--admin-text-2);
  background: var(--admin-paper);
  box-shadow: inset 0 0 0 1px var(--admin-border);
}

.portal-dot {
  height: 0.55rem;
  width: 0.55rem;
  border-radius: 9999px;
  background: var(--admin-text-3);
}
.portal-dot.is-success { background: #16a34a; }
.portal-dot.is-warning { background: #d97706; }
.portal-dot.is-danger { background: #dc2626; }

.portal-shortcut {
  display: block;
  text-align: left;
  padding: 1.15rem 1.25rem;
  border-radius: var(--admin-radius-lg);
  border: 1px solid var(--admin-border);
  background: var(--admin-surface);
  transition: border-color 160ms ease, box-shadow 160ms ease, transform 160ms ease;
}
.portal-shortcut:hover:not(.is-locked) {
  border-color: var(--admin-brass);
  box-shadow: var(--admin-shadow-md);
  transform: translateY(-1px);
}
.portal-shortcut:focus-visible {
  outline: 2px solid var(--admin-brass);
  outline-offset: 2px;
}
.portal-shortcut.is-locked {
  cursor: not-allowed;
  background: color-mix(in srgb, var(--admin-paper) 40%, #ffffff);
}
.portal-shortcut.is-locked .admin-kpi-ico {
  background: var(--admin-paper);
  color: var(--admin-text-3);
}

.portal-step {
  display: flex;
  gap: 0.75rem;
  padding: 0.9rem;
  border-radius: var(--admin-radius);
  border: 1px solid var(--admin-border);
  background: color-mix(in srgb, var(--admin-paper) 40%, #ffffff);
}
.portal-step.is-done {
  background: var(--admin-surface);
}
.portal-step-num {
  display: inline-flex;
  height: 1.5rem;
  width: 1.5rem;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 700;
  background: var(--admin-paper);
  color: var(--admin-text-2);
  border: 1px solid var(--admin-border);
}
.portal-step.is-done .portal-step-num {
  background: #dcfce7;
  border-color: #bbf7d0;
  color: #15803d;
}
</style>
