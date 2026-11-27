<template>
  <div class="space-y-1">
    <!-- Label row: mirrors DynamicFormField so the grid stays aligned -->
    <div class="flex items-center justify-between gap-2">
      <span class="block text-label text-gray-700 truncate">{{ label }}</span>
      <span
        class="inline-flex items-center gap-1 shrink-0 rounded-full bg-warning-50 border border-warning-200 px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-warning-700"
      >
        <Icon name="heroicons:sparkles-solid" class="w-3 h-3" />
        {{ badge }}
      </span>
    </div>

    <!-- Input-sized locked slot (same height as a text input) -->
    <button
      type="button"
      class="group w-full flex items-center gap-3 px-3 py-2 min-h-[42px] rounded-lg border border-dashed border-warning-300 bg-warning-50/40 text-left transition-colors hover:bg-warning-50 hover:border-warning-400 focus:outline-none focus:ring-2 focus:ring-warning-400 focus:ring-offset-1"
      :aria-label="`Upgrade your plan to unlock ${label}`"
      @click="$emit('upgrade')"
    >
      <span class="flex items-center justify-center w-6 h-6 shrink-0 rounded-md bg-warning-100 text-warning-600">
        <Icon name="heroicons:lock-closed-solid" class="w-3.5 h-3.5" />
      </span>
      <span class="flex-1 min-w-0 truncate text-input text-gray-500">
        Not in your plan
      </span>
      <span class="inline-flex items-center gap-1 shrink-0 text-sm font-semibold text-warning-700 group-hover:text-warning-800">
        {{ cta }}
        <Icon name="heroicons:arrow-right" class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" />
      </span>
    </button>
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, required: true },
  badge: { type: String, default: 'Pro' },
  cta: { type: String, default: 'Upgrade' },
})
defineEmits(['upgrade'])
</script>
