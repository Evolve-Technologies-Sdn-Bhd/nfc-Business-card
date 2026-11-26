<!-- components/UserAvatar.vue -->
<!--
  Avatar with a themed initials fallback.

  When no image is available (or it fails to load), the placeholder is drawn
  from the active theme's `secondary` tokens (Paper/Ink under the default
  "Ink & Brass" theme) instead of a hard-coded image, so it always follows the
  standard theme. Sizing is driven entirely by the classes passed in, so it is
  a drop-in replacement for a plain <img>.
-->
<template>
  <img
    v-if="showImage"
    :src="src"
    :alt="resolvedAlt"
    class="object-cover"
    @error="failed = true"
  />
  <span
    v-else
    class="flex select-none items-center justify-center overflow-hidden bg-secondary-100 text-secondary-600"
    role="img"
    :aria-label="resolvedAlt"
  >
    <svg viewBox="0 0 100 100" class="h-full w-full" aria-hidden="true">
      <text
        x="50"
        y="50"
        text-anchor="middle"
        dominant-baseline="central"
        font-size="44"
        font-weight="600"
        class="fill-current"
      >{{ initials }}</text>
    </svg>
  </span>
</template>

<script setup>
import { ref, computed, watch } from "vue";

const props = defineProps({
  src: { type: String, default: "" },
  name: { type: String, default: "" },
  alt: { type: String, default: "" },
});

const failed = ref(false);

// Reset the error state whenever a new image source is provided.
watch(
  () => props.src,
  () => {
    failed.value = false;
  }
);

const showImage = computed(() => Boolean(props.src) && !failed.value);

const resolvedAlt = computed(() => props.alt || props.name || "User");

const initials = computed(() => {
  const source = (props.name || "").trim();
  if (!source) return "?";
  const parts = source.split(/\s+/).filter(Boolean);
  if (parts.length > 1) {
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  }
  return parts[0][0].toUpperCase();
});
</script>
