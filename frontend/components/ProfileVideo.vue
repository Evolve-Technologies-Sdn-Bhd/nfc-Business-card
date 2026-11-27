<template>
  <!-- YouTube/Vimeo player on a public profile. `src` is an embed URL from
       the API (videos.intro / videos.company) or utils/videoEmbed.js in Live
       Preview: youtube-nocookie.com / Vimeo dnt=1, so nothing tracks the
       visitor before they press play. -->
  <figure :style="{ margin: 0 }">
    <div
      :style="{
        position: 'relative',
        width: '100%',
        aspectRatio: '16 / 9',
        borderRadius: '16px',
        overflow: 'hidden',
        background: '#000',
        border: `1px solid ${borderColor}`,
        boxShadow: '0 12px 30px rgba(0, 0, 0, 0.25)',
      }"
    >
      <iframe
        :src="src"
        :title="title"
        loading="lazy"
        referrerpolicy="strict-origin-when-cross-origin"
        allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; fullscreen"
        allowfullscreen
        :style="{ position: 'absolute', inset: 0, width: '100%', height: '100%', border: 0 }"
      ></iframe>
    </div>
    <figcaption
      v-if="caption"
      :style="{ marginTop: '10px', fontSize: '13px', letterSpacing: '0.04em', color: captionColor }"
    >
      {{ caption }}
    </figcaption>
  </figure>
</template>

<script setup>
defineProps({
  src: { type: String, required: true },
  title: { type: String, default: "Profile video" },
  caption: { type: String, default: "" },
  borderColor: { type: String, default: "rgba(194, 160, 104, 0.35)" },
  captionColor: { type: String, default: "rgba(247, 245, 240, 0.6)" },
});
</script>
