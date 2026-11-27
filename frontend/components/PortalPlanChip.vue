<!-- components/PortalPlanChip.vue
     Compact "current plan" summary for the bottom of the portal rail.
     Reads only real account state (plan, subscription_active, card status,
     free-window end date) via usePortalAccess. -->
<template>
  <div class="portal-plan-chip">
    <div class="flex items-center justify-between gap-2">
      <span class="text-[0.6875rem] font-semibold uppercase tracking-[0.16em] text-white/45">Current plan</span>
      <span class="portal-plan-dot" :class="`is-${status.tone}`" :title="status.label"></span>
    </div>
    <p class="mt-1 text-body font-semibold text-white">{{ access.planLabel }}</p>
    <p class="text-caption text-white/60">{{ status.label }}</p>
    <NuxtLink
      v-if="!access.isBusiness"
      to="/Onboarding/plan"
      class="mt-2.5 inline-flex items-center gap-1 text-caption font-semibold text-[color:var(--admin-brass-soft)] hover:text-white"
    >
      {{ access.isFree ? "Upgrade plan" : "Compare plans" }}
      <Icon name="heroicons:arrow-right" class="h-3.5 w-3.5" />
    </NuxtLink>
  </div>
</template>

<script setup>
import { portalPlanStatus } from "~/composables/usePortalAccess";

const props = defineProps({
  access: { type: Object, required: true },
});

const status = computed(() => portalPlanStatus(props.access));
</script>

<style scoped>
.portal-plan-chip {
  border-radius: 0.8rem;
  padding: 0.75rem 0.85rem;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid color-mix(in srgb, var(--brass) 18%, transparent);
}
.portal-plan-dot {
  height: 0.5rem;
  width: 0.5rem;
  border-radius: 9999px;
  background: var(--admin-text-3);
}
.portal-plan-dot.is-success { background: #4ade80; box-shadow: 0 0 8px rgba(74, 222, 128, 0.6); }
.portal-plan-dot.is-warning { background: #fbbf24; }
.portal-plan-dot.is-danger { background: #f87171; }
</style>
