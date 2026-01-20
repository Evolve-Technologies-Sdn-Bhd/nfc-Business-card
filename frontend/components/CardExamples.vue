<!-- components/CardExamples.vue -->
<template>
  <div class="bg-white rounded-2xl shadow-lg p-6">
    <h2 class="text-2xl font-bold text-secondary-900 mb-6">Card Examples</h2>

    <!-- No template selected state -->
    <div v-if="!hasTemplate" class="text-center py-12">
      <Icon name="heroicons:photo" class="mx-auto h-16 w-16 text-secondary-300 mb-4" />
      <p class="text-secondary-600">Please select a template to preview</p>
      <p class="text-sm text-secondary-500 mt-2">Your card preview will appear here</p>
    </div>

    <!-- Dynamic Card Previews -->
    <div v-else class="space-y-6">
      <!-- Card Preview Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Front View -->
        <div class="text-center">
          <div class="relative mx-auto rounded-xl overflow-hidden shadow-lg" 
               style="width: 180px; height: 108px;">
            <!-- Template Background -->
            <img 
              v-if="templateFrontImage" 
              :src="templateFrontImage" 
              alt="Card Front"
              class="absolute inset-0 w-full h-full object-cover"
            />
            <!-- Fallback gradient if no image -->
            <div v-else class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-700"></div>
            
            <!-- User Info Overlay -->
            <div class="absolute inset-0 p-3 text-white text-xs">
              <div class="flex items-center justify-between h-full">
                <div class="drop-shadow-md">
                  <div class="font-bold text-sm">{{ displayName }}</div>
                  <div class="opacity-90 text-xs">{{ displayPosition }}</div>
                  <div class="mt-2 text-xs opacity-80">{{ displayPhone }}</div>
                  <div class="text-xs opacity-80">{{ displayEmail }}</div>
                </div>
                <div v-if="cardInfo?.companyLogo" class="w-10 h-10 bg-white/90 rounded-lg flex items-center justify-center">
                  <img :src="cardInfo.companyLogo" alt="Logo" class="w-8 h-8 object-contain"/>
                </div>
              </div>
            </div>
          </div>
          <p class="text-xs text-secondary-500 mt-2">Front View</p>
        </div>

        <!-- Angled/3D View -->
        <div class="text-center">
          <div class="relative mx-auto rounded-xl overflow-hidden shadow-xl card-angled" 
               style="width: 180px; height: 108px;">
            <img 
              v-if="templateFrontImage" 
              :src="templateFrontImage" 
              alt="Card Angled"
              class="absolute inset-0 w-full h-full object-cover"
            />
            <div v-else class="absolute inset-0 bg-gradient-to-br from-purple-600 to-indigo-700"></div>
            
            <div class="absolute inset-0 p-3 text-white text-xs">
              <div class="flex items-center justify-between h-full">
                <div class="drop-shadow-md">
                  <div class="font-bold text-sm">{{ displayName }}</div>
                  <div class="opacity-90 text-xs">{{ displayPosition }}</div>
                  <div class="mt-2 text-xs opacity-80">{{ displayPhone }}</div>
                  <div class="text-xs opacity-80">{{ displayEmail }}</div>
                </div>
              </div>
            </div>
          </div>
          <p class="text-xs text-secondary-500 mt-2">Angled View</p>
        </div>

        <!-- Back View -->
        <div class="text-center">
          <div class="relative mx-auto rounded-xl overflow-hidden shadow-lg" 
               style="width: 180px; height: 108px;">
            <img 
              v-if="templateBackImage" 
              :src="templateBackImage" 
              alt="Card Back"
              class="absolute inset-0 w-full h-full object-cover"
            />
            <div v-else class="absolute inset-0 bg-gradient-to-br from-gray-800 to-gray-900 flex items-center justify-center">
              <div class="text-white text-center">
                <div class="text-xs opacity-60">Back Side</div>
                <div v-if="cardInfo?.website" class="text-xs mt-1">{{ displayWebsite }}</div>
                <div v-if="cardInfo?.address" class="text-xs mt-1 opacity-80 max-w-[140px] truncate">{{ cardInfo.address }}</div>
              </div>
            </div>
          </div>
          <p class="text-xs text-secondary-500 mt-2">Back View</p>
        </div>
      </div>

      <!-- Template Name Badge -->
      <div v-if="selectedTemplate?.name" class="text-center">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
          <Icon name="heroicons:check-circle" class="w-4 h-4 mr-1" />
          {{ selectedTemplate.name }}
        </span>
      </div>
    </div>

    <!-- Card Specifications -->
    <div class="mt-6 p-4 bg-secondary-50 rounded-lg">
      <div class="flex items-center">
        <Icon name="heroicons:information-circle" class="w-5 h-5 text-secondary-600 mr-2" />
        <div>
          <p class="text-sm font-medium text-secondary-800">Card Specifications</p>
          <p class="text-xs text-secondary-600">Size: 90mm x 54mm (Standard business card dimensions)</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  cardInfo: {
    type: Object,
    default: () => ({})
  },
  selectedTemplate: {
    type: Object,
    default: null
  },
  designMethod: {
    type: String,
    default: 'template'
  },
  customDesign: {
    type: Object,
    default: () => ({})
  }
});

// Check if template is available
const hasTemplate = computed(() => {
  if (props.designMethod === 'custom') {
    return props.customDesign?.front;
  }
  return props.selectedTemplate !== null;
});

// Get template front image
const templateFrontImage = computed(() => {
  if (props.designMethod === 'custom' && props.customDesign?.front) {
    return props.customDesign.front;
  }
  return props.selectedTemplate?.front_image_url || null;
});

// Get template back image
const templateBackImage = computed(() => {
  if (props.designMethod === 'custom' && props.customDesign?.back) {
    return props.customDesign.back;
  }
  return props.selectedTemplate?.back_image_url || null;
});

// Display values with fallbacks
const displayName = computed(() => props.cardInfo?.name || 'Your Name');
const displayPosition = computed(() => props.cardInfo?.position || 'Position');
const displayPhone = computed(() => props.cardInfo?.contactNumber || '+60 12-345 6789');
const displayEmail = computed(() => props.cardInfo?.email || 'email@example.com');
const displayWebsite = computed(() => {
  if (!props.cardInfo?.website) return 'www.yourwebsite.com';
  return props.cardInfo.website.replace(/^https?:\/\//, '');
});
</script>

<style scoped>
.card-angled {
  transform: perspective(1000px) rotateY(8deg);
  transition: transform 0.3s ease;
}
.card-angled:hover {
  transform: perspective(1000px) rotateY(0deg);
}
</style>