<!-- components/CardTemplateFace.vue
     One side of a card template, drawn the way the printed card will look:
     the template artwork plus the placeholders positioned in the admin card
     designer (front_layout / back_layout, in mm), filled with `data`.
     Without `data` it uses the shared sample person and the system logo, so a
     template shown as a design never looks blank.
     Shares its maths with CardExamples.vue (the customer's live preview). -->
<template>
  <div class="ctf" :style="{ aspectRatio: `${size.w} / ${size.h}` }">
    <img v-if="background" :src="background" :alt="alt" class="ctf-bg" loading="lazy" />
    <span v-else class="ctf-bg ctf-bg--blank"></span>

    <template v-for="(el, i) in layout" :key="i">
      <span v-if="el.type === 'image' && el.field === 'logo'" class="ctf-el" :style="boxStyle(el)" aria-hidden="true">
        <img v-if="logo" :src="logo" alt="" class="h-full w-full" :style="{ objectFit: el.fit || 'contain', opacity: el.opacity ?? 1 }" />
      </span>
      <span v-else-if="valueOf(el)" class="ctf-el" :style="[boxStyle(el), textStyle(el)]">{{ valueOf(el) }}</span>
    </template>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { SAMPLE_CARD_DATA, SAMPLE_LOGO_URL } from "~/utils/cardSampleData";

const props = defineProps({
  template: { type: Object, required: true },
  side: { type: String, default: "front" }, // front | back
  // { name, position, company, phone, email, website, address, logo }
  data: { type: Object, default: null },
  // Artwork URL to use instead of the template's (e.g. a resized thumbnail)
  imageUrl: { type: String, default: "" },
  alt: { type: String, default: "" },
});

const PT_TO_MM = 0.352778;
const FONT_STACKS = {
  helvetica: "Helvetica, Arial, sans-serif",
  times: "'Times New Roman', Times, serif",
  courier: "'Courier New', Courier, monospace",
};

const size = computed(() => {
  const [w, h] = String(props.template?.card_size || "90x54").split("x").map(Number);
  return { w: w || 90, h: h || 54 };
});

const background = computed(
  () => props.imageUrl || (props.side === "back" ? props.template?.back_image_url : props.template?.front_image_url) || ""
);

const layout = computed(() => {
  let list = props.side === "back" ? props.template?.back_layout : props.template?.front_layout;
  if (typeof list === "string") {
    try { list = JSON.parse(list); } catch { list = null; }
  }
  return Array.isArray(list) ? list : [];
});

const source = computed(() => props.data || SAMPLE_CARD_DATA);
const logo = computed(() => (props.data ? props.data.logo : SAMPLE_LOGO_URL) || "");

// Only data-bound elements are drawn: fixed text/images are already part of
// the template artwork (see card-designer.vue), so drawing them would double up.
const valueOf = (el) => (el.field ? source.value[el.field] || "" : "");

function boxStyle(el) {
  const { w, h } = size.value;
  return {
    left: (el.x / w) * 100 + "%",
    top: (el.y / h) * 100 + "%",
    width: (el.w / w) * 100 + "%",
    height: (el.h / h) * 100 + "%",
    transform: el.rotation ? `rotate(${el.rotation}deg)` : undefined,
  };
}

function textStyle(el) {
  const sizeMm = (el.fontSize || 10) * PT_TO_MM;
  return {
    fontFamily: FONT_STACKS[el.fontFamily] || FONT_STACKS.helvetica,
    fontSize: (sizeMm / size.value.w) * 100 + "cqw",
    fontWeight: el.fontWeight === "bold" ? 700 : 400,
    color: el.color || "#1c1a15",
    textAlign: el.align || "left",
    lineHeight: el.lineHeight || 1.2,
  };
}
</script>

<style scoped>
.ctf {
  position: relative;
  width: 100%;
  overflow: hidden;
  container-type: inline-size; /* font sizes are cqw of the card width */
  background: #f4f1ea;
}
.ctf-bg {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.ctf-bg--blank {
  background: linear-gradient(135deg, #f7f5f0, #e9e3d6);
}
/* Same box rules as CardExamples.vue .preview-el, so both draw identically. */
.ctf-el {
  position: absolute;
  display: block;
  overflow: hidden;
  white-space: pre-wrap;
  word-break: break-word;
}
</style>
