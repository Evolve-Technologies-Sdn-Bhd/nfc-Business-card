<!-- components/AdminSectionCard.vue
     Panel primitive with optional head/accent, replacing the many
     bespoke `.card` / `.card-header` / `.card-body` blocks. -->
<template>
  <section class="admin-panel" :class="{ 'admin-panel--flush': flush, 'admin-panel-accent': accent }">
    <header v-if="title || $slots['header-actions'] || $slots.title" class="admin-panel-head">
      <div class="min-w-0">
        <p class="admin-panel-title">
          <slot name="title">{{ title }}</slot>
        </p>
        <p v-if="subtitle" class="admin-panel-subtitle">{{ subtitle }}</p>
      </div>
      <div v-if="$slots['header-actions']" class="flex shrink-0 flex-wrap items-center gap-2">
        <slot name="header-actions" />
      </div>
    </header>

    <div :class="flush ? '' : 'admin-panel-body'" :style="flush ? '' : ''">
      <slot />
    </div>

    <footer v-if="$slots.footer" class="border-t border-secondary-200 px-6 py-4">
      <slot name="footer" />
    </footer>
  </section>
</template>

<script setup>
defineProps({
  title: { type: String, default: "" },
  subtitle: { type: String, default: "" },
  flush: { type: Boolean, default: false },
  accent: { type: Boolean, default: false },
});
</script>
