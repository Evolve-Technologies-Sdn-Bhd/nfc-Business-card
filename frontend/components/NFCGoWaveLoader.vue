<template>
  <div
    v-if="visible"
    :class="wrapperClass"
    role="status"
    aria-live="polite"
    aria-label="Loading"
  >
    <!-- ============================================================
         VARIANT 1: NFC FAN-WAVE (wave — DEFAULT)
         Half-circle fan arcs LEFT (3) + RIGHT (3) + 3D beveled chip
         Exact style like traditional NFC icon, upgraded with 3D depth
         ============================================================ -->
    <template v-if="variant === 'wave'">
      <div
        :class="[
          'relative select-none',
          sizes[size].stage,
          !inline ? 'animate-nfc-fade-up' : '',
        ]"
        :style="!inline ? { animationDelay: '60ms' } : {}"
      >
        <!-- ============== LEFT HALF-WAVE FAN (3 arcs) ============== -->
        <div
          class="nfc-fan-group"
          :class="sizes[size].fanLeft"
        >
          <!-- LEFT arc 3 (OUTERMOST, thinnest) — MATTE GOLD — farthest from chip (-13px left) -->
          <div
            class="absolute inset-0 w-full h-full pointer-events-none"
            style="transform: translateX(-13px)"
          >
            <svg
              class="nfc-fan-svg animate-nfc-fan-l-3"
              :class="sizes[size].fanSvg3"
              :viewBox="sizes[size].fanViewBox"
              fill="none"
            >
              <path
                :d="sizes[size].fanPath3"
                :stroke="sizes[size].strokeGold"
                :stroke-width="sizes[size].strokeW3"
                stroke-linecap="round"
              />
            </svg>
          </div>
          <!-- LEFT arc 2 (MIDDLE) — MATTE TEAL — middle distance (-6px left) -->
          <div
            class="absolute inset-0 w-full h-full pointer-events-none"
            style="transform: translateX(-6px)"
          >
            <svg
              class="nfc-fan-svg animate-nfc-fan-l-2"
              :class="sizes[size].fanSvg2"
              :viewBox="sizes[size].fanViewBox"
              fill="none"
            >
              <path
                :d="sizes[size].fanPath2"
                :stroke="sizes[size].strokeTeal"
                :stroke-width="sizes[size].strokeW2"
                stroke-linecap="round"
              />
            </svg>
          </div>
          <!-- LEFT arc 1 (INNERMOST, thickest) — MATTE NAVY — closest to chip (0 offset) -->
          <div
            class="absolute inset-0 w-full h-full pointer-events-none"
            style="transform: translateX(0px)"
          >
            <svg
              class="nfc-fan-svg animate-nfc-fan-l-1"
              :class="sizes[size].fanSvg1"
              :viewBox="sizes[size].fanViewBox"
              fill="none"
            >
              <path
                :d="sizes[size].fanPath1"
                :stroke="sizes[size].strokeNavy"
                :stroke-width="sizes[size].strokeW1"
                stroke-linecap="round"
              />
            </svg>
          </div>
        </div>

        <!-- ============== CENTER: 3D BEVELLED NFC CHIP ============== -->
        <div
          class="relative z-[2] absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2"
          :class="sizes[size].chipWrapper"
        >
          <div
            class="nfc-chip-3d"
            :class="[
              sizes[size].isLg ? 'animate-nfc-chip-3d-lg' : 'animate-nfc-chip-3d',
              sizes[size].chip,
            ]"
          ></div>
        </div>

        <!-- ============== RIGHT HALF-WAVE FAN (3 arcs) ============== -->
        <div
          class="nfc-fan-group"
          :class="sizes[size].fanRight"
        >
          <!-- RIGHT arc 3 (OUTERMOST, thinnest) — MATTE GOLD — farthest from chip (+13px right) -->
          <div
            class="absolute inset-0 w-full h-full pointer-events-none"
            style="transform: translateX(13px)"
          >
            <svg
              class="nfc-fan-svg animate-nfc-fan-r-3"
              :class="sizes[size].fanSvg3"
              :viewBox="sizes[size].fanViewBox"
              fill="none"
            >
              <path
                :d="sizes[size].fanPath3"
                :stroke="sizes[size].strokeGold"
                :stroke-width="sizes[size].strokeW3"
                stroke-linecap="round"
              />
            </svg>
          </div>
          <!-- RIGHT arc 2 (MIDDLE) — MATTE TEAL — middle distance (+6px right) -->
          <div
            class="absolute inset-0 w-full h-full pointer-events-none"
            style="transform: translateX(6px)"
          >
            <svg
              class="nfc-fan-svg animate-nfc-fan-r-2"
              :class="sizes[size].fanSvg2"
              :viewBox="sizes[size].fanViewBox"
              fill="none"
            >
              <path
                :d="sizes[size].fanPath2"
                :stroke="sizes[size].strokeTeal"
                :stroke-width="sizes[size].strokeW2"
                stroke-linecap="round"
              />
            </svg>
          </div>
          <!-- RIGHT arc 1 (INNERMOST, thickest) — MATTE NAVY — closest to chip (0 offset) -->
          <div
            class="absolute inset-0 w-full h-full pointer-events-none"
            style="transform: translateX(0px)"
          >
            <svg
              class="nfc-fan-svg animate-nfc-fan-r-1"
              :class="sizes[size].fanSvg1"
              :viewBox="sizes[size].fanViewBox"
              fill="none"
            >
              <path
                :d="sizes[size].fanPath1"
                :stroke="sizes[size].strokeNavy"
                :stroke-width="sizes[size].strokeW1"
                stroke-linecap="round"
              />
            </svg>
          </div>
        </div>
      </div>

      <!-- ============== BRAND LABEL STACK ============== -->
      <div
        v-if="showText"
        :class="[
          'flex flex-col items-center justify-center',
          sizes[size].stackGap,
        ]"
      >
        <span
          class="nfcgo-brand-text animate-nfc-text-shimmer font-poppins whitespace-nowrap relative"
          :class="sizes[size].brand"
        >
          {{ labelText }}
        </span>

        <!-- Animated accent underline under brand text (size sm+) -->
        <div
          v-if="size !== 'xs'"
          class="animate-nfc-accent-line rounded-full origin-center"
          :class="sizes[size].accentLine"
          :style="{
            backgroundImage: 'linear-gradient(90deg, ' +
              'color-mix(in srgb, var(--theme-primary-500,#4f5d82) 72%, transparent) 0%, ' +
              'color-mix(in srgb, var(--theme-accent,#5d8c87) 96%, transparent) 50%, ' +
              'color-mix(in srgb, var(--theme-accent-secondary,#b8956a) 72%, transparent) 100%)',
          }"
        ></div>

        <span
          v-if="showHintText && hintText"
          class="nfcgo-hint-text font-inter whitespace-nowrap"
          :class="sizes[size].hint"
        >
          {{ hintText }}
        </span>
      </div>
    </template>

    <!-- ============================================================
         VARIANT 2: DOT PULSE (inline / compact)
         3 staggered dots — Matte Navy / Teal / Gold
         ============================================================ -->
    <template v-else-if="variant === 'dotPulse'">
      <div
        :class="[
          'items-center',
          inline ? 'inline-flex' : 'flex',
          pulseSizes[size].gap,
          !inline ? 'animate-nfc-fade-up' : '',
        ]"
      >
        <span
          class="inline-block rounded-full animate-dot-bounce-1"
          :class="pulseSizes[size].dot"
          :style="{ backgroundColor: 'var(--theme-primary-500, #4f5d82)' }"
        ></span>
        <span
          class="inline-block rounded-full animate-dot-bounce-2"
          :class="pulseSizes[size].dot"
          :style="{ backgroundColor: 'var(--theme-accent, #5d8c87)' }"
        ></span>
        <span
          class="inline-block rounded-full animate-dot-bounce-3"
          :class="pulseSizes[size].dot"
          :style="{ backgroundColor: 'var(--theme-accent-secondary, #b8956a)' }"
        ></span>
        <span
          v-if="showText"
          :class="[
            'nfcgo-brand-text animate-nfc-text-shimmer font-poppins whitespace-nowrap',
            pulseSizes[size].label,
          ]"
        >
          {{ labelText }}
        </span>
      </div>
    </template>

    <!-- ============================================================
         VARIANT 3: CARD SCAN (skeleton / panel)
         Minimal flat panel with single diagonal scan line + matte bars
         ============================================================ -->
    <template v-else-if="variant === 'cardScan'">
      <div
        class="nfc-loader-card animate-nfc-fade-up"
        :class="scanSizes[size].card"
      >
        <!-- Scan line sweep -->
        <div class="nfc-skeleton-scan animate-card-skeleton-scan rounded-xl"></div>

        <div :class="scanSizes[size].pad">
          <!-- Header row -->
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div
                class="nfc-chip shrink-0"
                :class="scanSizes[size].chip"
              ></div>
              <div class="flex flex-col gap-2">
                <div
                  class="nfc-skeleton-bar"
                  :class="scanSizes[size].titleBar"
                ></div>
                <div
                  class="nfc-skeleton-bar"
                  :class="scanSizes[size].subBar"
                ></div>
              </div>
            </div>
            <div
              class="nfc-skeleton-bar rounded-full shrink-0"
              :class="scanSizes[size].dotBar"
            ></div>
          </div>

          <!-- Row skeletons -->
          <div :class="['flex flex-col', scanSizes[size].rowsGap]">
            <div
              v-for="n in scanSizes[size].rowCount"
              :key="n"
              class="nfc-skeleton-bar"
              :class="scanSizes[size].rowBar"
              :style="{ width: barWidth(n, scanSizes[size].rowCount) }"
            ></div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['xs', 'sm', 'md', 'lg', 'xl', 'full'].includes(v),
  },
  variant: {
    type: String,
    default: 'wave',
    validator: (v) => ['wave', 'dotPulse', 'cardScan'].includes(v),
  },
  showText: {
    type: Boolean,
    default: true,
  },
  showHintText: {
    type: Boolean,
    default: true,
  },
  labelText: {
    type: String,
    default: 'NFCGo',
  },
  hintText: {
    type: String,
    default: 'Memuatkan...',
  },
  visible: {
    type: Boolean,
    default: true,
  },
  overlay: {
    type: Boolean,
    default: false,
  },
  overlayTransparent: {
    type: Boolean,
    default: false,
  },
  inline: {
    type: Boolean,
    default: false,
  },
});

// Helper — widths for skeleton bars so look natural
const barWidth = (n, total) => {
  if (n === total) return '58%';
  if (n % 3 === 0) return '74%';
  if (n % 2 === 0) return '86%';
  return '96%';
};

// Colors (stroke for fan arcs — consistent with matte theme)
const NAVY = 'var(--theme-primary-600, #3d496a)';
const TEAL = 'var(--theme-accent, #5d8c87)';
const GOLD = 'var(--theme-accent-secondary, #b8956a)';

// Common SVG fan geometry.
// Viewbox 100x100, center (50,50).
// LEFT-side OPEN half-arcs (opening toward the RIGHT / center chip).
// These are WIFI-SIGNAL style arcs — a single curved stroke from upper-left
// sweeping around to lower-left WITHOUT any vertical connecting line.
// Radius set: inner=16, middle=28, outer=40
// Sweep angle 58° above & below horizontal → 236° of pure arc (no chord)
const VB = '0 0 100 100';
const R1 = 16, R2 = 28, R3 = 40;
const RAD = (deg) => (deg * Math.PI) / 180;
// Draw a pure open arc from (50 - r·cosθ, 50 - r·sinθ) to (50 - r·cosθ, 50 + r·sinθ)
// Using small-arc counter-clockwise so the curve passes through the LEFT side
// of the circle (farthest from center chip), with the opening facing +x
// (directly toward the chip). This avoids any chord/vertical line on the right.
const fanPath = (r, sweepDeg = 58) => {
  const theta = RAD(sweepDeg);
  const cx = 50;
  const cy = 50;
  const x = cx - r * Math.cos(theta);
  const y1 = cy - r * Math.sin(theta);
  const y2 = cy + r * Math.sin(theta);
  // large-arc-flag = 0 (angle between y1 & y2 through LEFT side = 2θ < 180°)
  // sweep-flag     = 0 (counter-clockwise from y1 → y2 through the left arc)
  return `M ${x.toFixed(3)} ${y1.toFixed(3)} A ${r} ${r} 0 0 0 ${x.toFixed(3)} ${y2.toFixed(3)}`;
};
const FANPATH_1 = fanPath(R1, 56);
const FANPATH_2 = fanPath(R2, 57);
const FANPATH_3 = fanPath(R3, 58);

// Wave variant — Size map (HALF-WAVE fan style)
// Horizontal gap between arc layers (via margin so it doesn't clash with
// keyframe animations that also use the CSS transform property).
// These are CONSTANT for all sizes (see template for inline margin styles):
//   Left  inner/arc1 = marginLeft 0px,   mid/arc2 = -6px,   outer/arc3 = -13px
//   Right inner/arc1 = marginLeft 0px,   mid/arc2 = +6px,   outer/arc3 = +13px
const sizes = {
  xs: {
    stage: 'w-16 h-7',
    fanLeft: 'left-0 w-[44%] h-full absolute top-0 bottom-0',
    fanRight: 'right-0 w-[44%] h-full absolute top-0 bottom-0',
    fanSvg1: 'absolute inset-0 w-full h-full',
    fanSvg2: 'absolute inset-0 w-full h-full',
    fanSvg3: 'absolute inset-0 w-full h-full',
    fanViewBox: VB,
    fanPath1: FANPATH_1,
    fanPath2: FANPATH_2,
    fanPath3: FANPATH_3,
    strokeNavy: NAVY,
    strokeTeal: TEAL,
    strokeGold: GOLD,
    strokeW1: 5,
    strokeW2: 5,
    strokeW3: 5,
    chipWrapper: 'w-6 h-6',
    chip: 'w-6 h-6',
    isLg: false,
    stackGap: 'gap-0.5 mt-1.5',
    brand: 'text-[11px] leading-tight',
    hint: 'text-[9px] leading-tight',
    accentLine: 'h-[1px] w-5',
  },
  sm: {
    stage: 'w-28 h-12',
    fanLeft: 'left-0 w-[44%] h-full absolute top-0 bottom-0',
    fanRight: 'right-0 w-[44%] h-full absolute top-0 bottom-0',
    fanSvg1: 'absolute inset-0 w-full h-full',
    fanSvg2: 'absolute inset-0 w-full h-full',
    fanSvg3: 'absolute inset-0 w-full h-full',
    fanViewBox: VB,
    fanPath1: FANPATH_1,
    fanPath2: FANPATH_2,
    fanPath3: FANPATH_3,
    strokeNavy: NAVY,
    strokeTeal: TEAL,
    strokeGold: GOLD,
    strokeW1: 5,
    strokeW2: 5,
    strokeW3: 5,
    chipWrapper: 'w-10 h-10',
    chip: 'w-10 h-10',
    isLg: false,
    stackGap: 'gap-0.5 mt-3',
    brand: 'text-[13px] leading-tight',
    hint: 'text-[10.5px] leading-tight',
    accentLine: 'h-[1px] w-7 mt-0.5',
  },
  md: {
    stage: 'w-44 h-20',
    fanLeft: 'left-0 w-[44%] h-full absolute top-0 bottom-0',
    fanRight: 'right-0 w-[44%] h-full absolute top-0 bottom-0',
    fanSvg1: 'absolute inset-0 w-full h-full',
    fanSvg2: 'absolute inset-0 w-full h-full',
    fanSvg3: 'absolute inset-0 w-full h-full',
    fanViewBox: VB,
    fanPath1: FANPATH_1,
    fanPath2: FANPATH_2,
    fanPath3: FANPATH_3,
    strokeNavy: NAVY,
    strokeTeal: TEAL,
    strokeGold: GOLD,
    strokeW1: 5,
    strokeW2: 5,
    strokeW3: 5,
    chipWrapper: 'w-16 h-16',
    chip: 'w-16 h-16',
    isLg: false,
    stackGap: 'gap-1 mt-5',
    brand: 'text-lg leading-tight',
    hint: 'text-xs leading-tight',
    accentLine: 'h-[1.5px] w-12 mt-1',
  },
  lg: {
    stage: 'w-60 h-24',
    fanLeft: 'left-0 w-[44%] h-full absolute top-0 bottom-0',
    fanRight: 'right-0 w-[44%] h-full absolute top-0 bottom-0',
    fanSvg1: 'absolute inset-0 w-full h-full',
    fanSvg2: 'absolute inset-0 w-full h-full',
    fanSvg3: 'absolute inset-0 w-full h-full',
    fanViewBox: VB,
    fanPath1: FANPATH_1,
    fanPath2: FANPATH_2,
    fanPath3: FANPATH_3,
    strokeNavy: NAVY,
    strokeTeal: TEAL,
    strokeGold: GOLD,
    strokeW1: 5,
    strokeW2: 5,
    strokeW3: 5,
    chipWrapper: 'w-20 h-20',
    chip: 'w-20 h-20',
    isLg: true,
    stackGap: 'gap-1.5 mt-7',
    brand: 'text-2xl leading-tight',
    hint: 'text-sm leading-tight',
    accentLine: 'h-[1.5px] w-18 mt-1',
  },
  xl: {
    stage: 'w-72 h-28',
    fanLeft: 'left-0 w-[44%] h-full absolute top-0 bottom-0',
    fanRight: 'right-0 w-[44%] h-full absolute top-0 bottom-0',
    fanSvg1: 'absolute inset-0 w-full h-full',
    fanSvg2: 'absolute inset-0 w-full h-full',
    fanSvg3: 'absolute inset-0 w-full h-full',
    fanViewBox: VB,
    fanPath1: FANPATH_1,
    fanPath2: FANPATH_2,
    fanPath3: FANPATH_3,
    strokeNavy: NAVY,
    strokeTeal: TEAL,
    strokeGold: GOLD,
    strokeW1: 5,
    strokeW2: 5,
    strokeW3: 5,
    chipWrapper: 'w-22 h-22',
    chip: 'w-22 h-22',
    isLg: true,
    stackGap: 'gap-1.5 mt-8',
    brand: 'text-3xl leading-tight',
    hint: 'text-[15px] leading-tight',
    accentLine: 'h-[2px] w-20 mt-1.5',
  },
  full: {
    stage: 'w-96 h-32',
    fanLeft: 'left-0 w-[44%] h-full absolute top-0 bottom-0',
    fanRight: 'right-0 w-[44%] h-full absolute top-0 bottom-0',
    fanSvg1: 'absolute inset-0 w-full h-full',
    fanSvg2: 'absolute inset-0 w-full h-full',
    fanSvg3: 'absolute inset-0 w-full h-full',
    fanViewBox: VB,
    fanPath1: FANPATH_1,
    fanPath2: FANPATH_2,
    fanPath3: FANPATH_3,
    strokeNavy: NAVY,
    strokeTeal: TEAL,
    strokeGold: GOLD,
    strokeW1: 5,
    strokeW2: 5,
    strokeW3: 5,
    chipWrapper: 'w-28 h-28',
    chip: 'w-28 h-28',
    isLg: true,
    stackGap: 'gap-2 mt-10',
    brand: 'text-4xl leading-tight',
    hint: 'text-base leading-tight',
    accentLine: 'h-[2px] w-24 mt-1.5',
  },
};

// Dot pulse variant — Size map
const pulseSizes = {
  xs:  { gap: 'gap-1',     dot: 'w-1.5 h-1.5', label: 'text-[11px] ml-1.5' },
  sm:  { gap: 'gap-1.5',   dot: 'w-2 h-2',     label: 'text-xs ml-2' },
  md:  { gap: 'gap-2',     dot: 'w-2.5 h-2.5', label: 'text-sm ml-2.5' },
  lg:  { gap: 'gap-2.5',   dot: 'w-3 h-3',     label: 'text-base ml-3' },
  xl:  { gap: 'gap-3',     dot: 'w-3.5 h-3.5', label: 'text-lg ml-4' },
  full:{ gap: 'gap-3.5',   dot: 'w-4 h-4',     label: 'text-xl ml-4' },
};

// Card scan variant — Size map
const scanSizes = {
  xs: {
    card: 'w-full max-w-xs',
    pad: 'p-3.5 space-y-4',
    chip: 'w-5 h-5',
    titleBar: 'w-16 h-2',
    subBar: 'w-11 h-1.5',
    dotBar: 'w-3.5 h-3.5',
    rowsGap: 'gap-2 mt-3',
    rowCount: 3,
    rowBar: 'h-1.5',
  },
  sm: {
    card: 'w-full max-w-sm',
    pad: 'p-4.5 space-y-5',
    chip: 'w-7 h-7',
    titleBar: 'w-20 h-2.5',
    subBar: 'w-14 h-2',
    dotBar: 'w-4 h-4',
    rowsGap: 'gap-2.5 mt-4',
    rowCount: 4,
    rowBar: 'h-2',
  },
  md: {
    card: 'w-full max-w-md',
    pad: 'p-6 space-y-6',
    chip: 'w-9 h-9',
    titleBar: 'w-24 h-3',
    subBar: 'w-18 h-2',
    dotBar: 'w-5 h-5',
    rowsGap: 'gap-3 mt-5',
    rowCount: 5,
    rowBar: 'h-2.5',
  },
  lg: {
    card: 'w-full max-w-lg',
    pad: 'p-7 space-y-7',
    chip: 'w-10 h-10',
    titleBar: 'w-28 h-3.5',
    subBar: 'w-22 h-2.5',
    dotBar: 'w-5.5 h-5.5',
    rowsGap: 'gap-3.5 mt-6',
    rowCount: 6,
    rowBar: 'h-3',
  },
  xl: {
    card: 'w-full max-w-xl',
    pad: 'p-8 space-y-8',
    chip: 'w-11 h-11',
    titleBar: 'w-32 h-4',
    subBar: 'w-26 h-3',
    dotBar: 'w-6 h-6',
    rowsGap: 'gap-4 mt-7',
    rowCount: 7,
    rowBar: 'h-3.5',
  },
  full: {
    card: 'w-full max-w-2xl',
    pad: 'p-9 space-y-9',
    chip: 'w-12 h-12',
    titleBar: 'w-36 h-4.5',
    subBar: 'w-30 h-3.5',
    dotBar: 'w-6.5 h-6.5',
    rowsGap: 'gap-4.5 mt-8',
    rowCount: 8,
    rowBar: 'h-4',
  },
};

const wrapperClass = computed(() => {
  const c = [];

  if (props.overlay || props.size === 'full') {
    c.push(
      'fixed inset-0 z-[9999]',
      props.overlayTransparent ? 'bg-white/0' : 'bg-[color:var(--theme-secondary-50,#fafaf7)]/90 backdrop-blur-[2px]',
      props.inline ? '' : 'flex-col',
    );
    if (!props.inline) c.push('flex items-center justify-center');
  } else if (props.inline) {
    c.push('inline-flex flex-col items-center align-middle py-0');
  } else {
    c.push(
      'flex flex-col items-center justify-center w-full',
      'py-3',
    );
  }

  return c.join(' ');
});
</script>
