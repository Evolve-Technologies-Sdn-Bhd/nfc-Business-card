<template>
  <div class="space-y-2">
    <!-- YouTube / Vimeo link only. The old "Upload" option stored a temporary
         blob: URL that broke after reload, and <video> could not play
         YouTube/Vimeo links at all. -->
    <div class="relative">
      <Icon
        name="heroicons:play-circle"
        class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 pointer-events-none"
        :class="parsed ? 'text-success-600' : 'text-gray-400'"
      />
      <input
        type="url"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value.trim())"
        placeholder="https://youtu.be/… or https://vimeo.com/…"
        class="w-full pl-10 pr-10 py-2 text-input border rounded-lg focus:ring-2 focus:border-transparent"
        :class="invalid ? 'border-error-400 focus:ring-error-300' : 'border-gray-300 focus:ring-blue-500'"
        :aria-invalid="invalid ? 'true' : 'false'"
      />
      <button
        v-if="modelValue"
        type="button"
        @click="$emit('update:modelValue', '')"
        class="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded text-gray-400 hover:text-gray-700"
        aria-label="Remove video"
      >
        <Icon name="heroicons:x-mark" class="w-4 h-4" />
      </button>
    </div>

    <p v-if="invalid" class="text-caption text-error-600">
      Use a YouTube or Vimeo link. Other sites can't be shown on your profile.
    </p>
    <p v-else-if="!modelValue" class="text-caption text-gray-500">Supports YouTube and Vimeo links.</p>

    <!-- Preview: the same player visitors will see -->
    <div v-if="parsed" class="relative aspect-video rounded-lg overflow-hidden bg-black border border-gray-200">
      <iframe
        :src="parsed.embedUrl"
        :title="`${parsed.provider === 'youtube' ? 'YouTube' : 'Vimeo'} video preview`"
        class="absolute inset-0 w-full h-full"
        loading="lazy"
        referrerpolicy="strict-origin-when-cross-origin"
        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen"
        allowfullscreen
      ></iframe>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { parseVideoUrl } from "~/utils/videoEmbed";

const props = defineProps({
  modelValue: { type: String, default: "" },
  // Kept for compatibility with existing field configs; uploads are no
  // longer offered.
  allowUpload: { type: Boolean, default: false },
  maxSize: { type: Number, default: 50 },
});
defineEmits(["update:modelValue"]);

const parsed = computed(() => parseVideoUrl(props.modelValue));
const invalid = computed(() => !!String(props.modelValue || "").trim() && !parsed.value);
</script>
