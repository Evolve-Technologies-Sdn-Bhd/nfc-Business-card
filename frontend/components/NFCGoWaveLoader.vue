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
           VARIANT 1: NFCGo LOGO DRAW (wave — DEFAULT)
           Official loading animation: the NFCGo mark draws itself in
           (inner ring → bar) while the outer signal waves pulse.
           Source: "NFCGo – Loading animation-html".
           ============================================================ -->
      <template v-if="variant === 'wave'">
        <div
          :class="[
            'nfg-loader',
            `nfg-loader--${theme}`,
            sizes[size],
            !inline ? 'animate-nfc-fade-up' : '',
          ]"
          :style="!inline ? { animationDelay: '60ms' } : {}"
        >
          <!-- Brand mark (draw-in animation) -->
          <svg
            class="nfg-loader__mark"
            viewBox="0 0 120 120"
            fill="none"
            aria-hidden="true"
          >
            <defs>
              <linearGradient :id="gradientId" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" :stop-color="palette.gradStart" />
                <stop offset="0.5" :stop-color="palette.gradMid" />
                <stop offset="1" :stop-color="palette.gradEnd" />
              </linearGradient>
            </defs>

            <!-- Static track behind the animated inner ring -->
            <path
              d="M84 62 A32 32 0 1 1 72.57 37.49"
              :stroke="palette.track"
              stroke-width="10"
              stroke-linecap="round"
            />
            <!-- Inner ring — draws in -->
            <path
              class="nfg-ring"
              d="M84 62 A32 32 0 1 1 72.57 37.49"
              :stroke="gradientUrl"
              stroke-width="10"
              stroke-linecap="round"
            />
            <!-- Cross bar — draws in just after the ring -->
            <path
              class="nfg-bar"
              d="M84 62 H58"
              :stroke="gradientUrl"
              stroke-width="10"
              stroke-linecap="round"
            />
            <!-- Outer signal waves — pulse -->
            <path
              class="nfg-w1"
              d="M85.94 28.06 A48 48 0 0 1 98.36 49.58"
              :stroke="gradientUrl"
              stroke-width="8"
              stroke-linecap="round"
            />
            <path
              class="nfg-w2"
              d="M95.84 18.16 A62 62 0 0 1 111.89 45.95"
              :stroke="gradientUrl"
              stroke-width="8"
              stroke-linecap="round"
            />
          </svg>

          <!-- Wordmark + progress bar + status label -->
          <div v-if="showText" class="nfg-loader__meta">
            <div class="nfg-loader__wordmark">
              <span class="nfg-loader__word">NFC</span
              ><span class="nfg-loader__go">Go</span>
            </div>

            <div class="nfg-loader__bar">
              <span class="nfg-loader__track-line"></span>
            </div>

            <p v-if="hintText && showHintText" class="nfg-loader__label">
              {{ hintText }}
            </p>
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

<script>
// Module-scope counter → guarantees a unique gradient id per instance.
let gradientSeq = 0;
</script>

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
  // Surface the loader sits on: 'dark' = ink/black surface, 'light' = white/paper surface
  theme: {
    type: String,
    default: 'dark',
    validator: (v) => ['dark', 'light'].includes(v),
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

const gradientId = `nfg-loader-gold-${++gradientSeq}`;
const gradientUrl = `url(#${gradientId})`;

// Colour sets for the logo-draw variant (dark ink vs. light paper surfaces).
const palettes = {
  dark: {
    track: '#1E1A0E',
    gradStart: '#F7E2A1',
    gradMid: '#D4AF37',
    gradEnd: '#9C7420',
  },
  light: {
    track: '#EAE3D0',
    gradStart: '#E6C15A',
    gradMid: '#C9A227',
    gradEnd: '#9C7420',
  },
};
const palette = computed(() => palettes[props.theme] ?? palettes.dark);

// Helper — widths for skeleton bars so look natural
const barWidth = (n, total) => {
  if (n === total) return '58%';
  if (n % 3 === 0) return '74%';
  if (n % 2 === 0) return '86%';
  return '96%';
};

// Wave variant — scale class (everything else derives from --nfg-mark)
const sizes = {
  xs: 'nfg-loader--xs',
  sm: 'nfg-loader--sm',
  md: 'nfg-loader--md',
  lg: 'nfg-loader--lg',
  xl: 'nfg-loader--xl',
  full: 'nfg-loader--full',
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

<style scoped>
/* ============================================================
   VARIANT 1 — NFCGo LOGO DRAW (official loading animation)
   Every dimension derives from --nfg-mark so the whole lockup
   scales proportionally (proportions taken from the design:
   mark 180px · wordmark 40px · bar 160×3px · gaps 28/14px).
   ============================================================ */
.nfg-loader {
  --nfg-mark: 6rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: calc(var(--nfg-mark) * 0.156);
  font-family: 'Outfit', 'Poppins', system-ui, sans-serif;
  line-height: 1;
  user-select: none;
}

.nfg-loader--xs {
  --nfg-mark: 2.25rem;
}
.nfg-loader--sm {
  --nfg-mark: 4rem;
}
.nfg-loader--md {
  --nfg-mark: 6rem;
}
.nfg-loader--lg {
  --nfg-mark: 8rem;
}
.nfg-loader--xl,
.nfg-loader--full {
  --nfg-mark: 11.25rem;
}

.nfg-loader__mark {
  width: var(--nfg-mark);
  height: var(--nfg-mark);
  display: block;
}

.nfg-loader__meta {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: calc(var(--nfg-mark) * 0.0778);
}

.nfg-loader__wordmark {
  font-size: calc(var(--nfg-mark) * 0.222);
  font-weight: 600;
  letter-spacing: -0.025em;
  line-height: 1;
  white-space: nowrap;
}

.nfg-loader__bar {
  width: calc(var(--nfg-mark) * 0.889);
  height: max(2px, calc(var(--nfg-mark) * 0.0167));
  border-radius: 999px;
  overflow: hidden;
}

.nfg-loader__track-line {
  display: block;
  width: 40%;
  height: 100%;
  border-radius: 999px;
  background: #d4af37;
  animation: nfgSlide 2s ease-in-out infinite;
}

.nfg-loader__label {
  margin: 0;
  font-size: max(10px, calc(var(--nfg-mark) * 0.0778));
  letter-spacing: 0.22em;
  text-transform: uppercase;
  white-space: nowrap;
}

/* ---- Palette: dark (ink) surfaces ---- */
.nfg-loader--dark .nfg-loader__word {
  color: #f6f1e4;
}
.nfg-loader--dark .nfg-loader__go {
  color: #d4af37;
}
.nfg-loader--dark .nfg-loader__bar {
  background: #1e1a0e;
}
.nfg-loader--dark .nfg-loader__label {
  color: #9c927a;
}

/* ---- Palette: light (paper) surfaces ---- */
.nfg-loader--light .nfg-loader__word {
  color: #0a0a0a;
}
.nfg-loader--light .nfg-loader__go {
  color: #8a6915;
}
.nfg-loader--light .nfg-loader__bar {
  background: #efe9d8;
}
.nfg-loader--light .nfg-loader__track-line {
  background: #c9a227;
}
.nfg-loader--light .nfg-loader__label {
  color: #8a8069;
}

/* ---- Mark animation: ring draws, bar follows, waves pulse ---- */
.nfg-ring {
  stroke-dasharray: 176;
  animation: nfgDraw 2s cubic-bezier(0.65, 0, 0.35, 1) infinite;
}
.nfg-bar {
  stroke-dasharray: 28;
  animation: nfgDraw2 2s cubic-bezier(0.65, 0, 0.35, 1) infinite;
}
.nfg-w1 {
  animation: nfgPulse 2s ease-in-out infinite 0.5s;
}
.nfg-w2 {
  animation: nfgPulse 2s ease-in-out infinite 0.75s;
}

@keyframes nfgDraw {
  0% {
    stroke-dashoffset: 176;
  }
  45%,
  70% {
    stroke-dashoffset: 0;
  }
  100% {
    stroke-dashoffset: -176;
  }
}
@keyframes nfgDraw2 {
  0%,
  25% {
    stroke-dashoffset: 28;
  }
  55%,
  75% {
    stroke-dashoffset: 0;
  }
  100% {
    stroke-dashoffset: -28;
  }
}
@keyframes nfgPulse {
  0%,
  100% {
    opacity: 0.12;
  }
  40% {
    opacity: 1;
  }
}
@keyframes nfgSlide {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(250%);
  }
}

@media (prefers-reduced-motion: reduce) {
  .nfg-ring,
  .nfg-bar,
  .nfg-w1,
  .nfg-w2,
  .nfg-loader__track-line {
    animation: none;
  }
}
</style>
