<template>
  <div class="flex items-center gap-2">
    <input
      type="color"
      :value="modelValue || '#000000'"
      @input="handleColorChange"
      class="w-12 h-10 rounded border-2 border-gray-300 cursor-pointer"
      title="Pick a color"
    />
    <input
      type="text"
      :value="modelValue || '#000000'"
      @input="handleTextChange"
      placeholder="#000000"
      maxlength="7"
      pattern="^#[0-9A-Fa-f]{6}$"
      class="flex-1 px-3 py-2 border border-gray-300 rounded-lg font-mono text-sm uppercase focus:ring-2 focus:ring-blue-500 focus:border-transparent"
    />
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: String,
    default: '#000000'
  }
});

const emit = defineEmits(['update:modelValue']);

const handleColorChange = (event) => {
  emit('update:modelValue', event.target.value.toUpperCase());
};

const handleTextChange = (event) => {
  let value = event.target.value.trim();
  // Ensure # prefix
  if (value && !value.startsWith('#')) {
    value = '#' + value;
  }
  // Validate hex color
  if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
    emit('update:modelValue', value.toUpperCase());
  }
};
</script>
