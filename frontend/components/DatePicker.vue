<template>
  <input
    type="date"
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
    :required="required"
    :min="min"
    :max="max"
    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
  />
</template>

<script setup>
defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  min: {
    type: String,
    default: null
  },
  max: {
    type: String,
    default: null
  }
});

defineEmits(['update:modelValue']);
</script>
