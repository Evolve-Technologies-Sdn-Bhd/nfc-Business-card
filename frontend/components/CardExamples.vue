<!-- components/CardExamples.vue
     Live card preview for the onboarding design step.
     One accurate preview instead of two: it renders the design the customer
     actually picked (template images or their own upload), at the real
     90 × 54 mm proportions, with a true 3D front/back flip. Empty fields are
     shown as obvious placeholders — never as fake contact details. -->
<template>
  <section class="rounded-2xl bg-white p-6 shadow-lg" aria-labelledby="card-preview-title">
    <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
      <div>
        <h2 id="card-preview-title" class="section-title">{{ title }}</h2>
        <p class="mt-1 text-body-sm text-secondary-500">
          {{ subtitle ?? (view === "both" ? "Front and back of the card you are ordering." : "Updates as you type and choose a design.") }}
        </p>
      </div>

      <!-- Front / back switch -->
      <div v-if="view !== 'both'" class="preview-switch" role="tablist" aria-label="Card side">
        <button
          type="button"
          role="tab"
          :aria-selected="side === 'front'"
          :class="{ 'is-active': side === 'front' }"
          @click="side = 'front'"
        >
          Front
        </button>
        <button
          type="button"
          role="tab"
          :aria-selected="side === 'back'"
          :class="{ 'is-active': side === 'back' }"
          @click="side = 'back'"
        >
          Back
        </button>
      </div>
    </div>

    <!-- Stage -->
    <div class="preview-stage" :class="{ 'preview-stage--both': view === 'both' }">
      <figure v-for="s in shownSides" :key="s" class="preview-figure">
      <component
        :is="view === 'both' ? 'div' : 'button'"
        :type="view === 'both' ? undefined : 'button'"
        class="preview-card"
        :class="{ 'is-flipped': s === 'back', 'is-static': view === 'both' }"
        :aria-label="view === 'both' ? undefined : side === 'front' ? 'Show back of card' : 'Show front of card'"
        @click="view === 'both' ? null : (side = side === 'front' ? 'back' : 'front')"
      >
        <!-- FRONT -->
        <span class="preview-face preview-face--front" aria-hidden="true">
          <img v-if="frontImage" :src="frontImage" alt="" class="preview-bg" />
          <span v-else class="preview-bg preview-bg--default"></span>
          <span v-if="frontImage && !frontLayout" class="preview-scrim"></span>

          <!-- Template layout from the card designer -->
          <span v-if="frontLayout" class="preview-layout">
            <template v-for="(el, i) in frontLayout" :key="'f' + i">
              <span v-if="el.type === 'image'" class="preview-el" :style="boxStyle(el)">
                <img v-if="logoSrc" :src="logoSrc" alt="" class="h-full w-full" :style="{ objectFit: el.fit, opacity: el.opacity }" />
              </span>
              <span
                v-else
                class="preview-el"
                :class="{ 'is-placeholder': !fieldValue(el.field) }"
                :style="[boxStyle(el), textStyle(el)]"
              >{{ fieldValue(el.field) || FIELD_PLACEHOLDERS[el.field] }}</span>
            </template>
          </span>

          <span v-else class="preview-content">
            <span class="flex items-start justify-between gap-3">
              <span class="min-w-0">
                <span class="preview-name" :class="{ 'is-placeholder': !cardInfo?.name }">
                  {{ cardInfo?.name || "Your name" }}
                </span>
                <span class="preview-role" :class="{ 'is-placeholder': !cardInfo?.position }">
                  {{ cardInfo?.position || "Job title" }}
                </span>
              </span>
              <span v-if="logoSrc" class="preview-logo">
                <img :src="logoSrc" alt="" />
              </span>
            </span>

            <span class="preview-contact">
              <span class="preview-line" :class="{ 'is-placeholder': !cardInfo?.contactNumber }">
                <Icon name="heroicons:phone" class="h-3 w-3 shrink-0" />
                <span class="truncate">{{ cardInfo?.contactNumber || "Phone number" }}</span>
              </span>
              <span class="preview-line" :class="{ 'is-placeholder': !cardInfo?.email }">
                <Icon name="heroicons:envelope" class="h-3 w-3 shrink-0" />
                <span class="truncate">{{ cardInfo?.email || "Email address" }}</span>
              </span>
            </span>
          </span>

          <Icon v-if="!frontLayout" name="heroicons:wifi" class="preview-nfc rotate-90" />
        </span>

        <!-- BACK -->
        <span class="preview-face preview-face--back" aria-hidden="true">
          <img v-if="backImage" :src="backImage" alt="" class="preview-bg" />
          <span v-else class="preview-bg preview-bg--default"></span>
          <span v-if="backImage && !backLayout" class="preview-scrim"></span>

          <span v-if="backLayout" class="preview-layout">
            <template v-for="(el, i) in backLayout" :key="'b' + i">
              <span v-if="el.type === 'image'" class="preview-el" :style="boxStyle(el)">
                <img v-if="logoSrc" :src="logoSrc" alt="" class="h-full w-full" :style="{ objectFit: el.fit, opacity: el.opacity }" />
              </span>
              <span
                v-else
                class="preview-el"
                :class="{ 'is-placeholder': !fieldValue(el.field) }"
                :style="[boxStyle(el), textStyle(el)]"
              >{{ fieldValue(el.field) || FIELD_PLACEHOLDERS[el.field] }}</span>
            </template>
          </span>

          <span v-else class="preview-content items-center justify-center text-center">
            <span class="preview-tap">
              <Icon name="heroicons:wifi" class="h-5 w-5 rotate-90" />
            </span>
            <span class="mt-2 text-[0.72rem] font-semibold tracking-wide">Tap to open my profile</span>
            <span v-if="website" class="mt-1 max-w-full truncate text-[0.68rem] opacity-80">{{ website }}</span>
            <span v-if="cardInfo?.address" class="mt-0.5 max-w-[85%] truncate text-[0.62rem] opacity-70">
              {{ cardInfo.address }}
            </span>
          </span>
        </span>
      </component>
      <figcaption v-if="view === 'both'" class="preview-caption">{{ s === "front" ? "Front" : "Back" }}</figcaption>
      </figure>
    </div>

    <p v-if="view !== 'both'" class="mt-3 text-center text-caption text-secondary-500">
      <Icon name="heroicons:cursor-arrow-rays" class="mr-1 inline h-3.5 w-3.5 align-[-2px]" />
      Click the card to flip it
    </p>

    <!-- Design source + specs -->
    <dl class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
      <div class="preview-spec">
        <dt>Design</dt>
        <dd>{{ designLabel }}</dd>
      </div>
      <div class="preview-spec">
        <dt>Size</dt>
        <dd>90 × 54 mm</dd>
      </div>
      <div class="preview-spec">
        <dt>Tap</dt>
        <dd>NFC chip built in</dd>
      </div>
    </dl>

    <p v-if="!hasDesign" class="mt-4 rounded-xl border border-dashed border-secondary-200 bg-secondary-50 px-4 py-3 text-body-sm text-secondary-600">
      <Icon name="heroicons:swatch" class="mr-1 inline h-4 w-4 align-[-3px] text-accent-600" />
      {{ designMethod === "custom" ? "Upload your front design to see it here." : "Pick a template to see your card in that design." }}
    </p>
  </section>
</template>

<script setup>
import { computed, ref, watch } from "vue";

const props = defineProps({
  cardInfo: { type: Object, default: () => ({}) },
  selectedTemplate: { type: Object, default: null },
  designMethod: { type: String, default: "template" },
  customDesign: { type: Object, default: () => ({}) },
  // "flip": one card that flips on click · "both": front and back side by side
  view: { type: String, default: "flip" },
  title: { type: String, default: "Live card preview" },
  subtitle: { type: String, default: null },
});

const side = ref("front");
const shownSides = computed(() => (props.view === "both" ? ["front", "back"] : [side.value]));

const frontImage = computed(() =>
  props.designMethod === "custom"
    ? props.customDesign?.front || null
    : props.selectedTemplate?.front_image_url || null,
);

const backImage = computed(() =>
  props.designMethod === "custom"
    ? props.customDesign?.back || null
    : props.selectedTemplate?.back_image_url || null,
);

const hasDesign = computed(() => !!frontImage.value);

// companyLogo may be a URL/data-URL string or a File picked in the form
const logoSrc = ref(null);
watch(
  () => props.cardInfo?.companyLogo,
  (logo, _old, onCleanup) => {
    if (!logo) {
      logoSrc.value = null;
    } else if (typeof logo === "string") {
      logoSrc.value = logo;
    } else if (typeof Blob !== "undefined" && logo instanceof Blob) {
      const url = URL.createObjectURL(logo);
      logoSrc.value = url;
      onCleanup(() => URL.revokeObjectURL(url));
    } else {
      logoSrc.value = null;
    }
  },
  { immediate: true },
);

const website = computed(() => {
  const w = props.cardInfo?.website;
  return w ? String(w).replace(/^https?:\/\//, "").replace(/\/$/, "") : "";
});

// ── Template layout (positions set in the admin card designer) ──────────
// Elements are stored in mm relative to the card's trim edge; they are drawn
// as percentages of the card so the preview matches at any width.
const PT_TO_MM = 0.352778;
const FONT_STACKS = {
  helvetica: "Helvetica, Arial, sans-serif",
  times: "'Times New Roman', Times, serif",
  courier: "'Courier New', Courier, monospace",
};
const FIELD_PLACEHOLDERS = {
  name: "Your name",
  position: "Job title",
  company: "Company name",
  phone: "Phone number",
  email: "Email address",
  website: "Website",
  address: "Address",
};

const usesTemplate = computed(() => props.designMethod !== "custom" && !!props.selectedTemplate);

const layoutSize = computed(() => {
  const [w, h] = String(props.selectedTemplate?.card_size || "90x54").split("x").map(Number);
  return { w: w || 90, h: h || 54 };
});

const parseLayout = (raw) => {
  let list = raw;
  if (typeof list === "string") {
    try { list = JSON.parse(list); } catch { list = null; }
  }
  return Array.isArray(list) && list.length ? list : null;
};

const frontLayout = computed(() => (usesTemplate.value ? parseLayout(props.selectedTemplate.front_layout) : null));
const backLayout = computed(() => (usesTemplate.value ? parseLayout(props.selectedTemplate.back_layout) : null));

function fieldValue(field) {
  const info = props.cardInfo || {};
  switch (field) {
    case "name": return info.name;
    case "position": return info.position;
    case "company": return info.companyName || info.company;
    case "phone": return info.contactNumber;
    case "email": return info.email;
    case "website": return website.value;
    case "address": return info.address;
    default: return "";
  }
}

function boxStyle(el) {
  const { w, h } = layoutSize.value;
  return {
    left: (el.x / w) * 100 + "%",
    top: (el.y / h) * 100 + "%",
    width: (el.w / w) * 100 + "%",
    height: (el.h / h) * 100 + "%",
    transform: el.rotation ? `rotate(${el.rotation}deg)` : undefined,
  };
}

function textStyle(el) {
  // Font size in mm as a share of card width, via container query units.
  const sizeMm = (el.fontSize || 10) * PT_TO_MM;
  return {
    fontFamily: FONT_STACKS[el.fontFamily] || FONT_STACKS.helvetica,
    fontSize: (sizeMm / layoutSize.value.w) * 100 + "cqw",
    fontWeight: el.fontWeight === "bold" ? 700 : 400,
    color: el.color || "#1c1a15",
    textAlign: el.align || "left",
    lineHeight: el.lineHeight || 1.2,
  };
}

const designLabel = computed(() => {
  if (props.designMethod === "custom") return hasDesign.value ? "Your own upload" : "Custom (not uploaded)";
  return props.selectedTemplate?.name || "No template yet";
});
</script>

<style scoped>
.preview-switch {
  display: inline-flex;
  padding: 0.2rem;
  border-radius: 9999px;
  background: #f3efe6;
  border: 1px solid #e6e0d5;
}
.preview-switch button {
  padding: 0.35rem 0.9rem;
  border-radius: 9999px;
  font-size: 0.8125rem;
  font-weight: 600;
  color: #77746d;
  transition: background-color 150ms ease, color 150ms ease;
}
.preview-switch button.is-active {
  background: #171d2b;
  color: #f5f1e8;
}

/* Stage: soft paper backdrop so the card reads as a physical object */
.preview-stage {
  display: flex;
  justify-content: center;
  padding: 1.75rem 1rem;
  border-radius: 1rem;
  background:
    radial-gradient(70% 60% at 50% 40%, rgba(194, 160, 104, 0.12) 0%, transparent 70%),
    #f8f5ee;
  perspective: 1200px;
}

/* 90 × 54 mm → aspect 5 : 3 */
.preview-card {
  position: relative;
  width: min(100%, 340px);
  aspect-ratio: 90 / 54;
  border-radius: 0.85rem;
  transform-style: preserve-3d;
  transition: transform 650ms cubic-bezier(0.2, 0.7, 0.2, 1);
  cursor: pointer;
}
.preview-card.is-flipped {
  transform: rotateY(180deg);
}
.preview-card.is-static {
  cursor: default;
  transition: none;
}
.preview-figure {
  display: flex;
  width: 100%;
  flex-direction: column;
  align-items: center;
  margin: 0;
}
.preview-stage--both {
  flex-wrap: wrap;
  gap: 1.25rem;
}
.preview-stage--both .preview-figure {
  flex: 1 1 220px;
  max-width: 340px;
}
.preview-caption {
  margin-top: 0.6rem;
  font-size: 0.6875rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #9c988f;
}
.preview-card:focus-visible {
  outline: 3px solid rgba(194, 160, 104, 0.6);
  outline-offset: 4px;
}

.preview-face {
  position: absolute;
  inset: 0;
  display: block;
  overflow: hidden;
  border-radius: inherit;
  backface-visibility: hidden;
  -webkit-backface-visibility: hidden;
  color: #ffffff;
  box-shadow:
    0 22px 40px -22px rgba(23, 29, 43, 0.55),
    0 2px 6px rgba(23, 29, 43, 0.12);
}
.preview-face--back {
  transform: rotateY(180deg);
}

.preview-bg {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.preview-bg--default {
  background:
    radial-gradient(90% 70% at 100% 0%, rgba(194, 160, 104, 0.35) 0%, transparent 55%),
    linear-gradient(140deg, #252b38 0%, #171d2b 100%);
}
/* Keeps overlaid text legible on light template artwork */
.preview-scrim {
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, rgba(10, 13, 20, 0.45) 0%, rgba(10, 13, 20, 0.08) 65%, transparent 100%);
}

.preview-layout {
  position: absolute;
  inset: 0;
  container-type: inline-size;
}
.preview-el {
  position: absolute;
  display: block;
  overflow: hidden;
  white-space: pre-wrap;
  word-break: break-word;
}
.preview-content {
  position: relative;
  display: flex;
  height: 100%;
  flex-direction: column;
  justify-content: space-between;
  padding: 1rem 1.1rem;
}
.preview-name {
  display: block;
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.2;
  letter-spacing: -0.01em;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}
.preview-role {
  display: block;
  margin-top: 0.15rem;
  font-size: 0.72rem;
  opacity: 0.85;
}
.preview-contact {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  max-width: 78%;
}
.preview-line {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.68rem;
  opacity: 0.9;
}
.is-placeholder {
  opacity: 0.5;
  font-style: italic;
}
.preview-logo {
  display: inline-flex;
  height: 2.25rem;
  width: 2.25rem;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  border-radius: 0.5rem;
  background: rgba(255, 255, 255, 0.92);
}
.preview-logo img {
  max-height: 1.75rem;
  max-width: 1.75rem;
  object-fit: contain;
}
.preview-nfc {
  position: absolute;
  right: 0.9rem;
  bottom: 0.8rem;
  height: 1.1rem;
  width: 1.1rem;
  opacity: 0.75;
}
.preview-tap {
  display: inline-flex;
  height: 2.6rem;
  width: 2.6rem;
  align-items: center;
  justify-content: center;
  border-radius: 9999px;
  border: 1.5px solid rgba(220, 194, 138, 0.8);
  color: #dcc28a;
}

.preview-spec {
  border-radius: 0.75rem;
  border: 1px solid #e6e0d5;
  background: #fbfaf6;
  padding: 0.6rem 0.8rem;
}
.preview-spec dt {
  font-size: 0.6875rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #9c988f;
}
.preview-spec dd {
  margin-top: 0.15rem;
  font-size: 0.8125rem;
  font-weight: 600;
  color: #252b38;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

@media (prefers-reduced-motion: reduce) {
  .preview-card {
    transition: none;
  }
}
</style>
