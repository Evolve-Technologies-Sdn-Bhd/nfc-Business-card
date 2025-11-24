<template>
  <div v-if="shouldShowField" class="space-y-1">
    <!-- Label -->
    <label class="block text-sm font-medium text-gray-700">
      {{ field.label }}
      <span v-if="!field.is_required" class="text-xs text-gray-500">(Optional)</span>
      <span v-if="field.is_required" class="text-error-600">*</span>
    </label>

    <!-- Help Text -->
    <p v-if="field.help_text" class="text-xs text-gray-500 mb-1">
      {{ field.help_text }}
    </p>

    <!-- Text Input -->
    <input
      v-if="field.field_type === 'text' || field.field_type === 'email' || field.field_type === 'tel' || field.field_type === 'url'"
      :type="field.field_type"
      v-model="modelValue[field.field_key]"
      :placeholder="field.placeholder"
      :required="field.is_required"
      :minlength="field.validation_rules?.min"
      :maxlength="field.validation_rules?.max"
      class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
      @input="$emit('update:modelValue', modelValue)"
    />

    <!-- Textarea -->
    <textarea
      v-else-if="field.field_type === 'textarea'"
      v-model="modelValue[field.field_key]"
      :placeholder="field.placeholder"
      :required="field.is_required"
      :minlength="field.validation_rules?.min"
      :maxlength="field.validation_rules?.max"
      :rows="field.config?.rows || 4"
      class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
      @input="$emit('update:modelValue', modelValue)"
    ></textarea>

    <!-- Number Input -->
    <input
      v-else-if="field.field_type === 'number'"
      type="number"
      v-model.number="modelValue[field.field_key]"
      :placeholder="field.placeholder"
      :required="field.is_required"
      :min="field.validation_rules?.min"
      :max="field.validation_rules?.max"
      class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
      @input="$emit('update:modelValue', modelValue)"
    />

    <!-- Repeater (Stats, Services, Team Members) -->
    <div v-else-if="field.field_type === 'repeater'" class="space-y-3">
      <div
        v-for="(item, index) in getRepeaterItems(field.field_key)"
        :key="index"
        class="p-3 bg-gray-50 rounded-lg border border-gray-200 space-y-2"
      >
        <div
          v-for="subField in field.config?.sub_fields"
          :key="subField.key"
          class="flex flex-col"
        >
          <label class="text-xs font-medium text-gray-600 mb-1">
            {{ subField.label }}
          </label>
          <input
            :type="subField.type || 'text'"
            v-model="modelValue[field.field_key][index][subField.key]"
            :placeholder="subField.placeholder"
            :maxlength="subField.maxlength"
            class="px-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500"
            @input="$emit('update:modelValue', modelValue)"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  field: {
    type: Object,
    required: true,
  },
  modelValue: {
    type: Object,
    required: true,
  },
  userPlan: {
    type: String,
    default: 'basic',
  },
});

defineEmits(['update:modelValue']);

// Check if field should be visible based on plan and visibility settings
const shouldShowField = computed(() => {
  if (!props.field.is_visible) return false;
  
  // If no plan restrictions, show to all
  if (!props.field.available_plans || props.field.available_plans.length === 0) {
    return true;
  }
  
  // Check if user's plan is in available plans
  return props.field.available_plans.includes(props.userPlan);
});

// Get repeater items (ensure array exists and has correct length)
const getRepeaterItems = (fieldKey) => {
  const maxItems = props.field.config?.max_items || 3;
  
  if (!props.modelValue[fieldKey]) {
    props.modelValue[fieldKey] = [];
  }
  
  // Ensure we have the right number of items
  while (props.modelValue[fieldKey].length < maxItems) {
    const newItem = {};
    props.field.config?.sub_fields?.forEach(subField => {
      newItem[subField.key] = '';
    });
    props.modelValue[fieldKey].push(newItem);
  }
  
  return props.modelValue[fieldKey].slice(0, maxItems);
};
</script>
