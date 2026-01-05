<template>
  <div 
    class="flex w-full bg-white border border-secondary-300 rounded-lg shadow-sm transition-all duration-150 ease-in-out relative"
    :class="{ 
      'border-red-500 focus-within:ring-red-500 focus-within:border-red-500': hasError,
      'focus-within:ring-2 focus-within:ring-primary-500 focus-within:border-primary-500': !hasError,
      'opacity-50 bg-secondary-50 cursor-not-allowed': disabled 
    }"
  >
    <!-- Country Code Dropdown -->
    <div class="relative flex items-stretch" ref="dropdownRef">
      <button
        type="button"
        @click="toggleDropdown"
        class="flex items-center gap-1 px-3 py-2 bg-secondary-50 border-r border-secondary-200 rounded-l-lg hover:bg-secondary-100 focus:outline-none transition-colors duration-150 ease-in-out min-w-[100px]"
        :disabled="disabled"
      >
        <span class="text-xl mr-1">{{ selectedCountry.flag }}</span>
        <span class="text-sm font-medium text-gray-700">{{ selectedCountry.dialCode }}</span>
        <Icon name="heroicons:chevron-down" class="h-4 w-4 text-secondary-400 ml-auto" />
      </button>

      <!-- Dropdown Menu -->
      <Transition
        enter-active-class="transition ease-out duration-100"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-75"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <div
          v-if="isOpen"
          class="absolute top-[calc(100%+4px)] left-0 z-50 w-[300px] max-h-[350px] bg-white border border-secondary-200 rounded-lg shadow-lg overflow-hidden"
        >
          <!-- Search Input -->
          <div class="relative p-3 border-b border-secondary-200">
            <Icon name="heroicons:magnifying-glass" class="absolute left-5 top-1/2 -translate-y-1/2 w-4 h-4 text-secondary-400" />
            <input
              ref="searchInputRef"
              v-model="searchQuery"
              type="text"
              placeholder="Search country..."
              class="w-full pl-9 pr-3 py-2 text-sm border border-secondary-200 rounded-md focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-200"
              @keydown.escape="closeDropdown"
            />
          </div>

          <!-- Country List -->
          <div class="max-h-[250px] overflow-y-auto">
            <button
              v-for="country in filteredCountries"
              :key="country.code"
              type="button"
              @click="selectCountry(country)"
              class="flex items-center gap-2 w-full px-3 py-2.5 text-left hover:bg-secondary-50 transition-colors duration-75"
              :class="{ 'bg-primary-50': country.code === selectedCountry.code }"
            >
              <span class="text-xl">{{ country.flag }}</span>
              <span class="flex-1 text-sm text-secondary-700">{{ country.name }}</span>
              <span class="text-xs text-secondary-500">{{ country.dialCode }}</span>
            </button>
            <div v-if="filteredCountries.length === 0" class="p-4 text-center text-sm text-secondary-500">
              No countries found
            </div>
          </div>
        </div>
      </Transition>
    </div>

    <!-- Phone Number Input -->
    <input
      ref="phoneInputRef"
      v-model="phoneNumber"
      type="tel"
      :placeholder="placeholder"
      :maxlength="maxLength"
      :required="required"
      :disabled="disabled"
      class="flex-1 px-3 py-2 text-sm bg-transparent border-none rounded-r-lg focus:outline-none w-full"
      @input="handleInput"
      @blur="handleBlur"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { 
  countryPhoneCodes, 
  getSortedCountries, 
  getCountryByCode, 
  getCountryByDialCode,
  defaultCountry 
} from '~/utils/countryPhoneCodes';

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  defaultCountryCode: {
    type: String,
    default: 'MY'
  },
  placeholder: {
    type: String,
    default: 'Phone number'
  },
  maxLength: {
    type: Number,
    default: 15
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  hasError: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue', 'country-change', 'blur']);

// State
const isOpen = ref(false);
const searchQuery = ref('');
const phoneNumber = ref('');
const selectedCountry = ref(getCountryByCode(props.defaultCountryCode));

// Refs
const dropdownRef = ref(null);
const searchInputRef = ref(null);
const phoneInputRef = ref(null);

// Computed
const filteredCountries = computed(() => {
  const sorted = getSortedCountries();
  if (!searchQuery.value) return sorted;
  
  const query = searchQuery.value.toLowerCase();
  return sorted.filter(c => 
    c.name.toLowerCase().includes(query) || 
    c.dialCode.includes(query) ||
    c.code.toLowerCase().includes(query)
  );
});

// Parse incoming modelValue to extract country code and number
const parsePhoneNumber = (value) => {
  if (!value) {
    phoneNumber.value = '';
    return;
  }

  // Check if starts with a country code
  for (const country of countryPhoneCodes) {
    if (value.startsWith(country.dialCode)) {
      selectedCountry.value = country;
      phoneNumber.value = value.slice(country.dialCode.length);
      return;
    }
  }

  // If no country code found, just set the number
  phoneNumber.value = value.replace(/^\+/, '');
};

// Format and emit the full phone number
const emitFullNumber = () => {
  let number = phoneNumber.value.trim();
  
  // Remove leading zero if present
  if (number.startsWith('0')) {
    number = number.slice(1);
  }
  
  // Remove any existing country code patterns
  const dialCodeDigits = selectedCountry.value.dialCode.replace('+', '');
  if (number.startsWith(dialCodeDigits)) {
    number = number.slice(dialCodeDigits.length);
  }
  
  // Emit full international format
  const fullNumber = number ? `${selectedCountry.value.dialCode}${number}` : '';
  emit('update:modelValue', fullNumber);
};

// Methods
const toggleDropdown = () => {
  if (props.disabled) return;
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    nextTick(() => {
      searchInputRef.value?.focus();
    });
  }
};

const closeDropdown = () => {
  isOpen.value = false;
  searchQuery.value = '';
};

const selectCountry = (country) => {
  selectedCountry.value = country;
  closeDropdown();
  emit('country-change', country);
  emitFullNumber();
  nextTick(() => {
    phoneInputRef.value?.focus();
  });
};

const handleInput = () => {
  // Clean input - allow only digits and spaces
  phoneNumber.value = phoneNumber.value.replace(/[^\d\s]/g, '');
};

const handleBlur = () => {
  // Format on blur
  let number = phoneNumber.value.trim();
  
  // Remove leading zero
  if (number.startsWith('0')) {
    number = number.slice(1);
  }
  
  // Remove spaces
  number = number.replace(/\s/g, '');
  
  phoneNumber.value = number;
  emitFullNumber();
  emit('blur');
};

// Click outside handler
const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    closeDropdown();
  }
};

// Watchers
watch(() => props.modelValue, (newValue) => {
  parsePhoneNumber(newValue);
}, { immediate: true });

watch(() => props.defaultCountryCode, (newCode) => {
  if (!props.modelValue) {
    selectedCountry.value = getCountryByCode(newCode);
  }
});

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
