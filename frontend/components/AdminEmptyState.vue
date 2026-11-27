<!-- components/AdminEmptyState.vue -->
<template>
  <div class="admin-empty">
    <span class="admin-empty-ico">
      <Icon :name="icon" class="h-7 w-7" />
    </span>
    <p class="text-base font-semibold text-secondary-900">{{ title }}</p>
    <p v-if="description" class="mt-1 max-w-md text-sm text-secondary-600">{{ description }}</p>
    <div v-if="$slots.actions" class="mt-5 flex flex-wrap items-center justify-center gap-2">
      <slot name="actions" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, default: "Nothing here yet" },
  description: { type: String, default: "" },
  icon: { type: String, default: "heroicons:inbox" },
});
</script>
