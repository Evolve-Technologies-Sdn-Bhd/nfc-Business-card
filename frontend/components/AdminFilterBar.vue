<!-- components/AdminFilterBar.vue
     Toolbar surface holding search fields, selects and reset actions.
     Accepts a submit handler so pages keep their existing behaviour. -->
<template>
  <form class="admin-panel mb-5" @submit.prevent="$emit('submit')">
    <div class="admin-panel-body">
      <div class="flex flex-wrap items-end gap-3">
        <div class="min-w-[14rem] flex-1">
          <slot name="search" />
        </div>
        <slot />
        <div
          v-if="$slots.actions"
          class="flex w-full flex-wrap items-center justify-end gap-2 sm:w-auto sm:ml-auto"
        >
          <slot name="actions" />
        </div>
      </div>
    </div>
  </form>
</template>

<script setup>
defineEmits(["submit"]);
</script>
