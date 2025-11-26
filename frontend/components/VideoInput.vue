<template>
  <div class="space-y-3">
    <!-- Method Selector -->
    <div class="flex gap-2">
      <button
        @click="inputMethod = 'url'"
        :class="[
          'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
          inputMethod === 'url'
            ? 'bg-blue-600 text-white'
            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
        ]"
        type="button"
      >
        <Icon name="heroicons:link" class="w-4 h-4 inline mr-1" />
        Video URL
      </button>
      <button
        v-if="allowUpload"
        @click="inputMethod = 'upload'"
        :class="[
          'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
          inputMethod === 'upload'
            ? 'bg-blue-600 text-white'
            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
        ]"
        type="button"
      >
        <Icon name="heroicons:arrow-up-tray" class="w-4 h-4 inline mr-1" />
        Upload
      </button>
    </div>
    
    <!-- URL Input -->
    <div v-if="inputMethod === 'url'" class="space-y-2">
      <input
        type="url"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        placeholder="https://youtube.com/watch?v=... or direct video URL"
        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
      />
      <p class="text-xs text-gray-500">
        Supports: YouTube, Vimeo, or direct video URLs (.mp4, .webm)
      </p>
    </div>
    
    <!-- Upload Input -->
    <div v-else class="space-y-2">
      <input
        ref="fileInput"
        type="file"
        accept="video/*"
        @change="handleFileUpload"
        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
      />
      <p class="text-xs text-gray-500">
        Max file size: {{ maxSize }}MB. Supported formats: MP4, WebM, MOV
      </p>
      <div v-if="uploading" class="text-sm text-blue-600">
        <Icon name="heroicons:arrow-path" class="w-4 h-4 inline animate-spin" />
        Uploading... {{ uploadProgress }}%
      </div>
    </div>
    
    <!-- Preview -->
    <div v-if="modelValue" class="relative aspect-video bg-black rounded-lg overflow-hidden">
      <video
        :src="modelValue"
        controls
        class="w-full h-full"
      />
      <button
        @click="clearVideo"
        type="button"
        class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-600 transition-colors"
      >
        <Icon name="heroicons:x-mark" class="w-5 h-5" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  allowUpload: {
    type: Boolean,
    default: true
  },
  maxSize: {
    type: Number,
    default: 50 // MB
  }
});

const emit = defineEmits(['update:modelValue']);

const inputMethod = ref('url');
const uploading = ref(false);
const uploadProgress = ref(0);
const fileInput = ref(null);

const handleFileUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;
  
  // Check file size
  if (file.size > props.maxSize * 1024 * 1024) {
    alert(`File size exceeds ${props.maxSize}MB limit`);
    return;
  }
  
  try {
    uploading.value = true;
    uploadProgress.value = 0;
    
    // TODO: Implement actual upload to server
    // For now, create a local URL
    const videoUrl = URL.createObjectURL(file);
    emit('update:modelValue', videoUrl);
    
    uploadProgress.value = 100;
  } catch (error) {
    console.error('Video upload error:', error);
    alert('Failed to upload video. Please try again.');
  } finally {
    uploading.value = false;
  }
};

const clearVideo = () => {
  emit('update:modelValue', '');
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};
</script>
