<template>
  <div
    v-if="show"
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
    @click.self="$emit('update:show', false)"
  >
    <div class="contact-modal-content bg-white rounded-2xl shadow-2xl w-full max-w-[700px] max-h-[90vh] overflow-hidden animate-modal-appear">
      <!-- Modal Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-primary-50 to-secondary-50">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Contact Us</h2>
          <p class="text-sm text-gray-600 mt-1">We'd love to hear from you!</p>
        </div>
        <button
          @click="$emit('update:show', false)"
          class="p-2 rounded-full hover:bg-white/80 transition-colors"
          aria-label="Close modal"
        >
          <Icon name="heroicons:x-mark" class="h-6 w-6 text-gray-600" />
        </button>
      </div>
      
      <!-- Modal Body - Google Form Iframe -->
      <div class="overflow-y-auto" style="height: calc(90vh - 100px); max-height: 800px;">
        <iframe
          src="https://docs.google.com/forms/d/e/1FAIpQLScGpcS-JYMWqHLMYsNHnzowmC09EvFQ97oYTGNue7H-IRuAZA/viewform?embedded=true"
          width="100%"
          height="800"
          frameborder="0"
          marginheight="0"
          marginwidth="0"
          class="w-full"
        >
          Loading…
        </iframe>
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

<style scoped>
@keyframes modalAppear {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(-20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.animate-modal-appear {
  animation: modalAppear 0.3s ease-out;
}

.contact-modal-content {
  transition: all 0.3s ease-out;
}
</style>
