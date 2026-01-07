<template>
  <div class="relative" ref="dropdownRef">
    <!-- Appearance Toggle Button -->
    <button
      @click="toggleDropdown"
      class="flex items-center justify-center p-2 rounded-lg text-secondary-600 hover:text-primary-600 hover:bg-secondary-100/80 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
      :title="`Appearance: ${currentThemeInfo?.name || 'Default'}`"
      aria-label="Change appearance"
    >
      <Icon name="heroicons:swatch" class="h-5 w-5" />
    </button>

    <!-- Dropdown Menu -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="transform opacity-0 scale-95 -translate-y-1"
      enter-to-class="transform opacity-100 scale-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="transform opacity-100 scale-100 translate-y-0"
      leave-to-class="transform opacity-0 scale-95 -translate-y-1"
    >
      <div
        v-if="isOpen"
        class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-2xl border border-secondary-200/50 overflow-hidden z-50 backdrop-blur-sm"
      >
        <!-- Header -->
        <div class="px-4 py-3 bg-gradient-to-r from-secondary-50 to-white border-b border-secondary-100">
          <h3 class="text-sm font-semibold text-secondary-900 flex items-center gap-2">
            <Icon name="heroicons:swatch" class="h-4 w-4 text-primary-500" />
            Appearance
          </h3>
        </div>

        <!-- Theme Options -->
        <div class="p-2 space-y-1">
          <button
            v-for="theme in availableThemes"
            :key="theme.key"
            @click="selectTheme(theme.key)"
            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 text-left group"
            :class="[
              currentTheme === theme.key
                ? 'bg-primary-50 ring-1 ring-primary-200'
                : 'hover:bg-secondary-50'
            ]"
          >
            <!-- Color Preview Swatch -->
            <div
              class="flex-shrink-0 w-8 h-8 rounded-lg shadow-sm flex items-center justify-center text-base transition-transform duration-200 group-hover:scale-105 ring-1 ring-black/5"
              :style="{ backgroundColor: theme.previewColor }"
            >
              <span class="drop-shadow-sm">{{ theme.icon }}</span>
            </div>

            <!-- Theme Info -->
            <div class="flex-1 min-w-0">
              <span class="text-sm font-medium text-secondary-900 block">{{ theme.name }}</span>
              <span class="text-xs text-secondary-500 truncate block">{{ theme.description }}</span>
            </div>

            <!-- Checkmark for active theme -->
            <Icon
              v-if="currentTheme === theme.key"
              name="heroicons:check"
              class="h-5 w-5 text-primary-600 flex-shrink-0"
            />
          </button>
        </div>

        <!-- Footer -->
        <div class="px-4 py-2 bg-secondary-50/50 border-t border-secondary-100">
          <p class="text-xs text-secondary-400 text-center flex items-center justify-center gap-1">
            <Icon name="heroicons:check-circle" class="h-3 w-3" />
            Saved automatically
          </p>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useTheme } from '~/composables/useTheme';

const { currentTheme, setTheme, getThemes, getCurrentThemeInfo } = useTheme();

const isOpen = ref(false);
const dropdownRef = ref(null);

// Get available themes for display
const availableThemes = computed(() => getThemes());

// Get current theme info
const currentThemeInfo = computed(() => getCurrentThemeInfo());

// Toggle dropdown
const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

// Select theme
const selectTheme = (themeKey) => {
  setTheme(themeKey);
  isOpen.value = false;
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isOpen.value = false;
  }
};

// Close on escape key
const handleEscape = (event) => {
  if (event.key === 'Escape') {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  document.addEventListener('keydown', handleEscape);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
  document.removeEventListener('keydown', handleEscape);
});
</script>
