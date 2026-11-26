<!-- components/AdminStatTile.vue
     KPI tile used across the dashboard and module summaries. -->
<template>
  <div class="admin-kpi group">
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0">
        <p class="admin-kpi-label">{{ label }}</p>
        <p class="admin-kpi-value">
          <template v-if="loading">
            <span class="admin-skeleton inline-block h-7 w-20 align-middle"></span>
          </template>
          <template v-else>{{ value }}</template>
        </p>
      </div>
      <span v-if="icon" class="admin-kpi-ico" :class="iconClass">
        <Icon :name="icon" class="h-5 w-5" />
      </span>
    </div>

    <div v-if="hint || trend" class="mt-3 flex items-center gap-2 text-xs">
      <span
        v-if="trend"
        class="inline-flex items-center gap-1 font-semibold"
        :class="trend === 'down' ? 'text-error-600' : 'text-success-600'"
      >
        <Icon
          :name="trend === 'down' ? 'heroicons:arrow-trending-down' : 'heroicons:arrow-trending-up'"
          class="h-4 w-4"
        />
        {{ trendValue }}
      </span>
      <span v-if="hint" class="text-secondary-600">{{ hint }}</span>
    </div>

    <slot />
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number], default: 0 },
  icon: { type: String, default: "" },
  iconClass: { type: String, default: "" },
  hint: { type: String, default: "" },
  trend: { type: String, default: "" },
  trendValue: { type: String, default: "" },
  loading: { type: Boolean, default: false },
});
</script>
