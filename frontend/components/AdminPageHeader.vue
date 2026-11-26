<!-- components/AdminPageHeader.vue
     Shared Ink & Brass page header. Replaces the repeated
     `<div class="mb-8"><div class="flex ..."><h1 class="page-title">…` block. -->
<template>
  <div class="admin-page-head">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
      <div class="min-w-0">
        <nav v-if="breadcrumb && breadcrumb.length" class="admin-crumb mb-2 flex flex-wrap items-center gap-1.5" aria-label="Breadcrumb">
          <template v-for="(crumb, i) in breadcrumb" :key="i">
            <NuxtLink v-if="crumb.to" :to="crumb.to">{{ crumb.label }}</NuxtLink>
            <span v-else>{{ crumb.label }}</span>
            <span v-if="i < breadcrumb.length - 1" class="text-secondary-300">/</span>
          </template>
        </nav>

        <span v-if="eyebrow" class="admin-eyebrow">
          <Icon :name="eyebrowIcon" class="h-3.5 w-3.5" />
          {{ eyebrow }}
        </span>
        <h1 class="admin-page-title" :class="{ 'mt-1.5': eyebrow }">
          <slot name="title">{{ title }}</slot>
        </h1>
        <div class="admin-rule mt-3"></div>
        <p v-if="description" class="admin-page-desc mt-3">{{ description }}</p>
      </div>

      <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2.5 lg:justify-end">
        <slot name="actions" />
      </div>
    </div>

    <slot />
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, default: "" },
  eyebrow: { type: String, default: "" },
  eyebrowIcon: { type: String, default: "heroicons:squares-2x2" },
  description: { type: String, default: "" },
  breadcrumb: { type: Array, default: () => [] },
});
</script>
