<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
  >
    <div class="bg-white rounded-2xl p-6 max-w-2xl w-full mx-4">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-2xl font-bold text-secondary-900">Product Demo</h3>
        <button
          @click="$emit('update:show', false)"
          class="p-2 hover:bg-secondary-100 rounded-lg transition-colors"
        >
          <Icon name="heroicons:x-mark" class="h-6 w-6 text-secondary-600" />
        </button>
      </div>
      <div
        class="aspect-video bg-secondary-100 rounded-lg flex items-center justify-center"
      >
        <div class="text-center">
          <Icon
            name="heroicons:play"
            class="h-16 w-16 text-secondary-400 mx-auto mb-4"
          />
          <p class="text-secondary-600">Demo video coming soon...</p>
          <p class="text-sm text-secondary-500 mt-2">
            Experience the future of networking
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false
  }
})

defineEmits(['update:show'])
</script>
