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
          <!-- LEFT arc 3 (OUTERMOST, thinnest) — MATTE GOLD — farthest from chip -->
          <div class="nfc-fan-wrap nfc-fan-left-c">
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
          <!-- LEFT arc 2 (MIDDLE) — MATTE TEAL — middle distance -->
          <div class="nfc-fan-wrap nfc-fan-left-b">
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
          <!-- LEFT arc 1 (INNERMOST, thickest) — MATTE NAVY — closest to chip -->
          <div class="nfc-fan-wrap nfc-fan-left-a">
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
          <!-- RIGHT arc 3 (OUTERMOST, thinnest) — MATTE GOLD — farthest from chip -->
          <div class="nfc-fan-wrap nfc-fan-right-c">
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
          <!-- RIGHT arc 2 (MIDDLE) — MATTE TEAL — middle distance -->
          <div class="nfc-fan-wrap nfc-fan-right-b">
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
          <!-- RIGHT arc 1 (INNERMOST, thickest) — MATTE NAVY — closest to chip -->
          <div class="nfc-fan-wrap nfc-fan-right-a">
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

// Common SVG fan geometry (VIEWBOX 100x100, center 50,50).
// Exactly as in the reference image: 3 CONCENTRIC CIRCULAR OPEN ARCS,
// with a UNIFORM PERPENDICULAR GAP between each stroke (6px visual
// clearance), and ALL 3 ARCs OPEN AT THE SAME X-COORDINATE (on the side
// facing the chip) so they LOOK ALIGNED just like the reference image.
// Opening sits at openXOffset = 10 units inside from center → x = 40.
const VB = '0 0 100 100';
const cx = 50;
const cy = 50;
// -------- Geometry tuned to match reference image exactly --------
const OPEN_OFFSET = 10;        // Opening x = cx - OPEN_OFFSET = 40 (ALL arcs open here)
const R1 = 16;                 // inner (navy)  radius
const STROKE_STEP = 9;         // 9px between stroke centerlines → 9 - strokeW visual gap
const R2 = R1 + STROKE_STEP;   // middle (teal) radius
const R3 = R2 + STROKE_STEP;   // outer  (gold) radius
// For a given r, compute sweep angle (half-angle) so that the arc's
// endpoint lands exactly at x = cx - OPEN_OFFSET (the opening line).
// r · cos(θ) = r_projected  (where projected is the length from center
//                              toward the opening, measured along x)
// The opening is at x = cx - OPEN_OFFSET (10 units inward from center)
// → along the circle, the x-coordinate at angle θ from +x toward -x is:
//      cx - r·cos θ = cx - OPEN_OFFSET  ←  must equal the opening x line
//   →  r·cos θ = OPEN_OFFSET
//   →  θ = arccos(OPEN_OFFSET / r)
const arcHalfAngle = (r) => Math.acos(Math.min(OPEN_OFFSET / r, 0.999));
// Draw one OPEN CIRCULAR ARC. All arcs share the same center. All open
// toward +x (chip side) at exactly the same x boundary (cx - OPEN_OFFSET).
// Sweep counter-clockwise through the FAR LEFT side → no chord line.
const circularFan = (r) => {
  const theta = arcHalfAngle(r);
  const sinA = Math.sin(theta);
  // Endpoints sit on the opening x line (cx - OPEN_OFFSET), vertically
  // separated by ±r·sin θ → produces the reference's nested look.
  const x1 = cx - OPEN_OFFSET;
  const y1 = cy - r * sinA;
  const x2 = x1;
  const y2 = cy + r * sinA;
  // large-arc-flag = 0 (2θ < 180° for any valid r > OPEN_OFFSET)
  // sweep-flag     = 0 (counter-clockwise → curves through FAR LEFT)
  const f = (v) => v.toFixed(3);
  return `M ${f(x1)} ${f(y1)} A ${f(r)} ${f(r)} 0 0 0 ${f(x2)} ${f(y2)}`;
};
const FANPATH_1 = circularFan(R1);
const FANPATH_2 = circularFan(R2);
const FANPATH_3 = circularFan(R3);

// Wave variant — Size map (HALF-WAVE fan style)
// Horizontal gap between arc layers (via margin so it doesn't clash with
// keyframe animations that also use the CSS transform property).
// These are CONSTANT for all sizes (see template for inline margin styles):
//   Left  inner/arc1 = marginLeft 0px,   mid/arc2 = -6px,   outer/arc3 = -13px
//   Right inner/arc1 = marginLeft 0px,   mid/arc2 = +6px,   outer/arc3 = +13px
const sizes = {
  xs: {
    stage: 'w-16 h-7',
    fanLeft: 'nfc-fan-left',
    fanRight: 'nfc-fan-right',
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
    stackGap: 'gap-1 mt-1',
    brand: 'text-[11px] leading-tight',
    hint: 'text-[9px] leading-tight',
    accentLine: 'h-[1px] w-5',
  },
  sm: {
    stage: 'w-28 h-12',
    fanLeft: 'nfc-fan-left',
    fanRight: 'nfc-fan-right',
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
    stackGap: 'gap-1 mt-2',
    brand: 'text-[13px] leading-tight',
    hint: 'text-[10.5px] leading-tight',
    accentLine: 'h-[1px] w-7 mt-0.5',
  },
  md: {
    stage: 'w-44 h-20',
    fanLeft: 'nfc-fan-left',
    fanRight: 'nfc-fan-right',
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
    stackGap: 'gap-1.5 mt-3',
    brand: 'text-lg leading-tight',
    hint: 'text-xs leading-tight',
    accentLine: 'h-[1.5px] w-12 mt-1',
  },
  lg: {
    stage: 'w-60 h-24',
    fanLeft: 'nfc-fan-left',
    fanRight: 'nfc-fan-right',
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
    stackGap: 'gap-2 mt-4',
    brand: 'text-2xl leading-tight',
    hint: 'text-sm leading-tight',
    accentLine: 'h-[1.5px] w-18 mt-1',
  },
  xl: {
    stage: 'w-72 h-28',
    fanLeft: 'nfc-fan-left',
    fanRight: 'nfc-fan-right',
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
    stackGap: 'gap-2 mt-5',
    brand: 'text-3xl leading-tight',
    hint: 'text-[15px] leading-tight',
    accentLine: 'h-[2px] w-20 mt-1.5',
  },
  full: {
    stage: 'w-96 h-32',
    fanLeft: 'nfc-fan-left',
    fanRight: 'nfc-fan-right',
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
    stackGap: 'gap-3 mt-6',
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
