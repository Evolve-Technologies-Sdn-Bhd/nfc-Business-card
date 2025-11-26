<template>
  <div class="space-y-2">
    <!-- Selected Icon Display -->
    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-300">
      <div class="text-3xl">{{ modelValue || '❓' }}</div>
      <div class="flex-1">
        <p class="text-sm font-medium text-gray-900">
          {{ modelValue ? 'Selected Icon' : 'No icon selected' }}
        </p>
        <p class="text-xs text-gray-500">
          Click to choose an icon
        </p>
      </div>
      <button
        v-if="modelValue"
        @click="clearIcon"
        type="button"
        class="px-3 py-1 text-xs bg-gray-200 rounded hover:bg-gray-300"
      >
        Clear
      </button>
    </div>
    
    <!-- Icon Picker Button -->
    <button
      @click="showPicker = !showPicker"
      type="button"
      class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
    >
      <Icon name="heroicons:face-smile" class="w-4 h-4 inline mr-2" />
      Choose Icon
    </button>
    
    <!-- Icon Picker Modal -->
    <div
      v-if="showPicker"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
      @click.self="showPicker = false"
    >
      <div class="bg-white rounded-lg max-w-2xl w-full max-h-[80vh] overflow-hidden">
        <!-- Header -->
        <div class="p-4 border-b flex items-center justify-between">
          <h3 class="text-lg font-semibold">Select an Icon</h3>
          <button
            @click="showPicker = false"
            type="button"
            class="text-gray-400 hover:text-gray-600"
          >
            <Icon name="heroicons:x-mark" class="w-6 h-6" />
          </button>
        </div>
        
        <!-- Search -->
        <div class="p-4 border-b">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search icons..."
            class="w-full px-3 py-2 border rounded-lg"
          />
        </div>
        
        <!-- Icon Grid -->
        <div class="p-4 overflow-y-auto max-h-96">
          <div class="grid grid-cols-8 gap-2">
            <button
              v-for="icon in filteredIcons"
              :key="icon"
              @click="selectIcon(icon)"
              type="button"
              :class="[
                'aspect-square flex items-center justify-center text-2xl rounded-lg transition-colors',
                modelValue === icon
                  ? 'bg-blue-100 ring-2 ring-blue-500'
                  : 'hover:bg-gray-100'
              ]"
            >
              {{ icon }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:modelValue']);

const showPicker = ref(false);
const searchQuery = ref('');

// Common emoji icons
const commonIcons = [
  '😀', '😃', '😄', '😁', '😅', '😂', '🤣', '😊',
  '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘',
  '💼', '💻', '📱', '⚙️', '🔧', '🔨', '🛠️', '⚡',
  '🚀', '✈️', '🚗', '🏠', '🏢', '🏭', '🏪', '🏬',
  '📊', '📈', '📉', '💰', '💵', '💳', '💎', '🎯',
  '🎨', '🎭', '🎪', '🎬', '🎮', '🎲', '🎵', '🎶',
  '📧', '📞', '📱', '💬', '💭', '🗨️', '🗯️', '💡',
  '🔍', '🔎', '🔐', '🔒', '🔓', '🔑', '🗝️', '🔖',
  '📝', '📄', '📃', '📑', '📊', '📈', '📉', '📌',
  '⭐', '🌟', '✨', '💫', '🔥', '💧', '🌈', '☀️',
  '🏆', '🥇', '🥈', '🥉', '🎖️', '🏅', '🎗️', '👍',
  '👎', '👏', '🙌', '👐', '🤝', '✋', '👋', '🤙',
  '✅', '❌', '⭕', '🔴', '🔵', '🟢', '🟡', '🟠',
  '❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍'
];

const filteredIcons = computed(() => {
  if (!searchQuery.value) return commonIcons;
  // In a real app, you'd have icon names/tags to search
  return commonIcons;
});

const selectIcon = (icon) => {
  emit('update:modelValue', icon);
  showPicker.value = false;
};

const clearIcon = () => {
  emit('update:modelValue', '');
};
</script>
