<template>
  <div
    v-if="visible"
    :class="wrapperClass"
    role="status"
    aria-live="polite"
    aria-label="Loading"
  >
    <!-- ============================================================
         INNER GROUP WRAPPER: Group ALL loading elements together
         & force-centre them (both axes) inside ANY container / page.
         Applies to ALL 3 variants (wave / dotPulse / cardScan).
         ============================================================ -->
    <div class="flex items-center justify-center w-full h-full select-none">
      <!-- ============================================================
           VARIANT 1: NFC FAN-WAVE (wave — DEFAULT)
           Half-circle fan arcs LEFT (3) + RIGHT (3) + 3D beveled chip
           Exact style like traditional NFC icon, upgraded with 3D depth
           ============================================================ -->
      <template v-if="variant === 'wave'">
        <div
          :class="[
            'flex flex-row items-center justify-center relative select-none nfc-wave-stage',
            sizes[size].stage,
            !inline ? 'animate-nfc-fade-up' : '',
          ]"
          :style="!inline ? { animationDelay: '60ms' } : {}"
        >
          <!-- ==================== COLUMN 1: LEFT WAVE (3 arcs container) ==================== -->
          <div
            :class="['relative h-full flex items-center justify-end', sizes[size].leftCol]"
          >
            <div class="nfc-fan-group nfc-fan-left relative h-full w-full">
              <!-- LEFT arc 3 (OUTERMOST, thinnest) — MATTE GOLD — farthest from chip -->
              <div class="nfc-fan-wrap nfc-fan-left-c absolute inset-0">
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
              <div class="nfc-fan-wrap nfc-fan-left-b absolute inset-0">
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
              <div class="nfc-fan-wrap nfc-fan-left-a absolute inset-0">
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
          </div>

          <!-- ==================== COLUMN 2: CENTER — NFCGo BRAND (no hint, no chip) ==================== -->
          <div
            v-if="showText"
            :class="[
              'flex flex-col items-center justify-center relative z-[2]',
              sizes[size].centerCol,
            ]"
          >
            <span
              class="nfcgo-brand-text animate-nfc-text-shimmer font-poppins whitespace-nowrap relative"
              :class="sizes[size].brand"
            >
              {{ labelText }}
            </span>
          </div>

          <!-- If no text shown (inline), still render a minimal invisible spacer -->
          <div
            v-else
            :class="[
              'flex flex-col items-center justify-center relative z-[2]',
              sizes[size].centerCol,
            ]"
          >
            <div class="relative" :class="sizes[size].chipWrapper">
              <div
                class="nfc-chip-3d"
                :class="[
                  sizes[size].isLg ? 'animate-nfc-chip-3d-lg' : 'animate-nfc-chip-3d',
                  sizes[size].chip,
                ]"
              ></div>
            </div>
          </div>

          <!-- ==================== COLUMN 3: RIGHT WAVE (3 arcs container) ==================== -->
          <div
            :class="['relative h-full flex items-center justify-start', sizes[size].rightCol]"
          >
            <div class="nfc-fan-group nfc-fan-right relative h-full w-full">
              <!-- RIGHT arc 3 (OUTERMOST, thinnest) — MATTE GOLD — farthest from chip -->
              <div class="nfc-fan-wrap nfc-fan-right-c absolute inset-0">
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
              <div class="nfc-fan-wrap nfc-fan-right-b absolute inset-0">
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
              <div class="nfc-fan-wrap nfc-fan-right-a absolute inset-0">
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
        </div>
      </template>

      <!-- ============================================================
           VARIANT 2: DOT PULSE (inline / compact)
           3 staggered dots — Matte Navy / Teal / Gold
           ============================================================ -->
      <template v-else-if="variant === 'dotPulse'">
        <div
          :class="[
            'items-center justify-center',
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
    </div><!-- END inner group wrapper (force w-full h-full centre both axes) -->
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
/* Construct a single half-circle WiFi fan arc using pure SVG elliptical arc.
   All 3 arcs share the same center (cx, cy) but have DIFFERENT RADII and
   DIFFERENT OPENING OFFSETS (per-radius).
   Critical detail about OPEN_OFFSET:
     - If all 3 arcs use the SAME opening x-line (cx - 10 = 40), then all 6
       endpoints (3 left + 3 right, 2 each top/bottom) land on the SAME
       horizontal Y-level because they all sit on the same x vertical line and
       have roughly proportional arc angles. This creates a "fake horizontal
       line" visual caused by 6 rounded stroke endpoints lining up perfectly.
     - To eliminate that spurious "red box line" artifact, the inner arc's
       endpoints sit DEEPER (larger offset, closer to chip) while outer arc's
       endpoints sit FURTHER OUT (smaller offset). This staggers the endpoint
       Y-levels so they never form a straight horizontal line.
   Per-layer offsets chosen for a natural nested WiFi symbol look:
     Arc1/R=16 (Navy,  innermost): off = 13  (deepest opening)
     Arc2/R=25 (Teal,  middle   ): off = 10  (middle)
     Arc3/R=34 (Gold,  outermost): off = 7   (widest opening)
*/
const circularFan = (r, openOffset) => {
  const theta = Math.acos(openOffset / r); // angle subtended by opening
  const sinA = Math.sin(theta);
  const x1 = cx - openOffset;
  const y1 = cy - r * sinA;
  const x2 = x1;
  const y2 = cy + r * sinA;
  // large-arc-flag = 0 (2θ < 180°, small arc)
  // sweep-flag     = 0 (counter-clockwise → curve bulges toward FAR LEFT)
  const f = (v) => v.toFixed(3);
  return `M ${f(x1)} ${f(y1)} A ${f(r)} ${f(r)} 0 0 0 ${f(x2)} ${f(y2)}`;
};
const FANPATH_1 = circularFan(R1, 13); // Navy inner  — deep opening  (x=37)
const FANPATH_2 = circularFan(R2, 10); // Teal middle — standard      (x=40)
const FANPATH_3 = circularFan(R3, 7);  // Gold outer  — wide opening  (x=43)

// Wave variant — Size map (HALF-WAVE fan style)
// Horizontal gap between arc layers (via margin so it doesn't clash with
// keyframe animations that also use the CSS transform property).
// These are CONSTANT for all sizes (see template for inline margin styles):
//   Left  inner/arc1 = marginLeft 0px,   mid/arc2 = -6px,   outer/arc3 = -13px
//   Right inner/arc1 = marginLeft 0px,   mid/arc2 = +6px,   outer/arc3 = +13px
const sizes = {
  xs: {
    stage: 'w-28 h-8',
    leftCol: 'w-6 h-6 shrink-0 mr-[1px]',
    rightCol: 'w-6 h-6 shrink-0 ml-[1px]',
    centerCol: 'shrink-0 max-w-[70%] -translate-y-[10px] items-center justify-center',
    fanSvg1: 'w-full h-full block',
    fanSvg2: 'w-full h-full block',
    fanSvg3: 'w-full h-full block',
    fanViewBox: VB,
    fanPath1: FANPATH_1,
    fanPath2: FANPATH_2,
    fanPath3: FANPATH_3,
    strokeNavy: NAVY,
    strokeTeal: TEAL,
    strokeGold: GOLD,
    strokeW1: 4,
    strokeW2: 4,
    strokeW3: 4,
    chipWrapper: 'w-4 h-4',
    chip: 'w-4 h-4',
    isLg: false,
    brand: 'text-[11px] leading-none font-bold tracking-wide',
  },
  sm: {
    stage: 'w-48 h-14',
    leftCol: 'w-12 h-12 shrink-0 mr-[1px]',
    rightCol: 'w-12 h-12 shrink-0 ml-[1px]',
    centerCol: 'shrink-0 max-w-[65%] -translate-y-[18px] items-center justify-center',
    fanSvg1: 'w-full h-full block',
    fanSvg2: 'w-full h-full block',
    fanSvg3: 'w-full h-full block',
    fanViewBox: VB,
    fanPath1: FANPATH_1,
    fanPath2: FANPATH_2,
    fanPath3: FANPATH_3,
    strokeNavy: NAVY,
    strokeTeal: TEAL,
    strokeGold: GOLD,
    strokeW1: 4,
    strokeW2: 4,
    strokeW3: 4,
    chipWrapper: 'w-8 h-8',
    chip: 'w-8 h-8',
    isLg: false,
    brand: 'text-[15px] leading-none font-bold tracking-wide',
  },
  md: {
    stage: 'w-72 h-20',
    leftCol: 'w-20 h-20 shrink-0 mr-[2px]',
    rightCol: 'w-20 h-20 shrink-0 ml-[2px]',
    centerCol: 'shrink-0 max-w-[60%] -translate-y-[30px] items-center justify-center',
    fanSvg1: 'w-full h-full block',
    fanSvg2: 'w-full h-full block',
    fanSvg3: 'w-full h-full block',
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
    chipWrapper: 'w-12 h-12',
    chip: 'w-12 h-12',
    isLg: false,
    brand: 'text-xl leading-none font-bold tracking-wide',
  },
  lg: {
    stage: 'w-96 h-24',
    leftCol: 'w-24 h-24 shrink-0 mr-[2px]',
    rightCol: 'w-24 h-24 shrink-0 ml-[2px]',
    centerCol: 'shrink-0 max-w-[55%] -translate-y-[44px] items-center justify-center',
    fanSvg1: 'w-full h-full block',
    fanSvg2: 'w-full h-full block',
    fanSvg3: 'w-full h-full block',
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
    isLg: true,
    brand: 'text-3xl leading-none font-bold tracking-wide',
  },
  xl: {
    stage: 'w-[36rem] h-32',
    leftCol: 'w-32 h-32 shrink-0 mr-[3px]',
    rightCol: 'w-32 h-32 shrink-0 ml-[3px]',
    centerCol: 'shrink-0 max-w-[50%] -translate-y-[60px] items-center justify-center',
    fanSvg1: 'w-full h-full block',
    fanSvg2: 'w-full h-full block',
    fanSvg3: 'w-full h-full block',
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
    brand: 'text-4xl leading-none font-bold tracking-wide',
  },
  full: {
    stage: 'w-full max-w-[48rem] h-40',
    leftCol: 'w-40 h-40 shrink-0 mr-[4px]',
    rightCol: 'w-40 h-40 shrink-0 ml-[4px]',
    centerCol: 'shrink-0 max-w-[50%] -translate-y-[72px] items-center justify-center',
    fanSvg1: 'w-full h-full block',
    fanSvg2: 'w-full h-full block',
    fanSvg3: 'w-full h-full block',
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
    chipWrapper: 'w-24 h-24',
    chip: 'w-24 h-24',
    isLg: true,
    brand: 'text-5xl leading-none font-bold tracking-wide',
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
    // STANDALONE / BLOCK usage (not overlay, not inline): ALWAYS self-center
    // both horizontal + vertical axes, filling full viewport screen when used
    // directly on a page without a height-constrained parent.
    c.push(
      'flex flex-col items-center justify-center',
      'w-full max-w-full mx-auto',
      'min-h-screen h-full my-auto',
      'p-2 m-auto',
      'text-center',
      'place-self-center',
    );
  }

  return c.join(' ');
});
</script>
