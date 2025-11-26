<template>
  <div class="space-y-3">
    <!-- Items Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
      <!-- Existing Items -->
      <div
        v-for="(item, index) in items"
        :key="index"
        class="relative aspect-square bg-gray-100 rounded-lg overflow-hidden group"
      >
        <!-- Image -->
        <img
          v-if="item.type === 'image' || !item.type"
          :src="item.url || item"
          :alt="`Gallery item ${index + 1}`"
          class="w-full h-full object-cover"
        />
        
        <!-- Video -->
        <video
          v-else-if="item.type === 'video'"
          :src="item.url"
          class="w-full h-full object-cover"
          muted
        />
        
        <!-- Remove Button -->
        <button
          @click="removeItem(index)"
          type="button"
          class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-7 h-7 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg hover:bg-red-600"
        >
          <Icon name="heroicons:x-mark" class="w-4 h-4" />
        </button>
        
        <!-- Type Badge -->
        <div
          v-if="item.type === 'video'"
          class="absolute bottom-2 left-2 bg-black/70 text-white px-2 py-1 rounded text-xs"
        >
          <Icon name="heroicons:play" class="w-3 h-3 inline" />
          Video
        </div>
      </div>
      
      <!-- Add Button -->
      <label
        v-if="!maxItems || items.length < maxItems"
        class="aspect-square bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center cursor-pointer hover:bg-gray-100 hover:border-gray-400 transition-colors"
      >
        <Icon name="heroicons:plus" class="w-8 h-8 text-gray-400" />
        <span class="text-xs text-gray-500 mt-1">Add Media</span>
        <input
          type="file"
          multiple
          :accept="accept"
          @change="handleFileUpload"
          class="hidden"
        />
      </label>
    </div>
    
    <!-- Info Bar -->
    <div class="flex items-center justify-between text-xs text-gray-500">
      <span>
        {{ items.length }} / {{ maxItems || '∞' }} items
      </span>
      <span v-if="uploading">
        <Icon name="heroicons:arrow-path" class="w-3 h-3 inline animate-spin" />
        Uploading...
      </span>
    </div>
    
    <!-- Help Text -->
    <p class="text-xs text-gray-500">
      Drag to reorder. {{ allowVideos ? 'Images and videos' : 'Images only' }} supported.
      Max {{ maxFileSize }}MB per file.
    </p>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';

const { $api } = useNuxtApp();

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  maxItems: {
    type: Number,
    default: 12
  },
  allowVideos: {
    type: Boolean,
    default: false
  },
  maxFileSize: {
    type: Number,
    default: 10 // MB
  },
  uploadEndpoint: {
    type: String,
    default: '/upload/gallery-image'
  }
});

const emit = defineEmits(['update:modelValue']);

const items = ref([...(props.modelValue || [])]);
const uploading = ref(false);

// Watch for external changes
watch(() => props.modelValue, (newVal) => {
  items.value = [...(newVal || [])];
}, { deep: true });

// Compute accept attribute
const accept = computed(() => {
  return props.allowVideos ? 'image/*,video/*' : 'image/*';
});

const handleFileUpload = async (event) => {
  const files = Array.from(event.target.files);
  if (files.length === 0) return;
  
  // Check max items limit
  if (props.maxItems && items.value.length + files.length > props.maxItems) {
    alert(`Maximum ${props.maxItems} items allowed`);
    return;
  }
  
  try {
    uploading.value = true;
    
    for (const file of files) {
      // Check file size
      if (file.size > props.maxFileSize * 1024 * 1024) {
        alert(`File "${file.name}" exceeds ${props.maxFileSize}MB limit`);
        continue;
      }
      
      // Determine type
      const type = file.type.startsWith('video/') ? 'video' : 'image';
      
      // Upload to server
      const formData = new FormData();
      formData.append('image', file); // Backend expects 'image' field
      formData.append('type', type);
      
      try {
        const response = await $api.post(props.uploadEndpoint, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        // Backend returns { success: true, data: { url: '...' } }
        const uploadedUrl = response.data?.data?.url || response.data?.url;
        if (uploadedUrl) {
          items.value.push({
            type,
            url: uploadedUrl,
            name: file.name
          });
        }
      } catch (uploadError) {
        console.error('File upload failed:', uploadError);
        // Fallback to local preview if upload fails
        const url = URL.createObjectURL(file);
        items.value.push({
          type,
          url,
          name: file.name,
          _local: true // Mark as local (not uploaded)
        });
      }
    }
    
    // Emit updated array
    emit('update:modelValue', items.value);
    
  } catch (error) {
    console.error('Gallery upload error:', error);
    alert('Failed to upload files. Please try again.');
  } finally {
    uploading.value = false;
    // Clear input
    event.target.value = '';
  }
};

const removeItem = (index) => {
  items.value.splice(index, 1);
  emit('update:modelValue', items.value);
};
</script>
