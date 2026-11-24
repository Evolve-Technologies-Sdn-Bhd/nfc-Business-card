<!--
  components/NFCGoLogo.vue
  ----------------------------------------------------------------------------
  Official NFCGo logo lockup (signal-arc mark + wordmark).
  Single source of truth for the brand logo across the whole system.
  Design source: "NFCGo Logo-project" (Main.dc.html — Black & Gold).

  Usage:
    <NFCGoLogo variant="dark" />          → for ink / dark surfaces (gold mark + light word)
    <NFCGoLogo variant="light" size="sm"/> → for paper / white surfaces (dark mark + dark word)
    <NFCGoLogo :show-text="false" />       → mark only
  ----------------------------------------------------------------------------
-->
<script>
// Module-scope counter → guarantees a unique gradient id per instance.
let gradientSeq = 0;
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  // 'dark' = place on dark/ink backgrounds, 'light' = place on white/paper backgrounds
  variant: {
    type: String,
    default: 'dark',
    validator: (v) => ['dark', 'light'].includes(v),
  },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(v),
  },
  showText: { type: Boolean, default: true },
  accent: { type: String, default: '#D4AF37' },
  label: { type: String, default: 'NFCGo' },
});

const gradientId = `nfcgo-mark-gold-${++gradientSeq}`;

const isDark = computed(() => props.variant === 'dark');
const innerStroke = computed(() =>
  isDark.value ? `url(#${gradientId})` : '#0A0A0A',
);
const outerStroke = computed(() =>
  isDark.value ? `url(#${gradientId})` : '#A8801F',
);
</script>

<template>
  <span
    class="nfcgo-logo"
    :class="[`nfcgo-logo--${variant}`, `nfcgo-logo--${size}`]"
    :style="{ '--nfcgo-accent': accent }"
  >
    <svg
      class="nfcgo-logo__mark"
      viewBox="0 0 120 120"
      fill="none"
      role="img"
      :aria-label="label"
    >
      <defs>
        <linearGradient :id="gradientId" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0" stop-color="#F7E2A1" />
          <stop offset="0.5" :stop-color="accent" />
          <stop offset="1" stop-color="#9C7420" />
        </linearGradient>
      </defs>
      <path
        d="M84 62 A32 32 0 1 1 72.57 37.49"
        :stroke="innerStroke"
        stroke-width="10"
        stroke-linecap="round"
      />
      <path
        d="M58 62 H84"
        :stroke="innerStroke"
        stroke-width="10"
        stroke-linecap="round"
      />
      <path
        d="M85.94 28.06 A48 48 0 0 1 98.36 49.58"
        :stroke="outerStroke"
        stroke-width="8"
        stroke-linecap="round"
      />
      <path
        d="M95.84 18.16 A62 62 0 0 1 111.89 45.95"
        :stroke="outerStroke"
        stroke-width="8"
        stroke-linecap="round"
      />
    </svg>

    <span v-if="showText" class="nfcgo-logo__wordmark">
      <span class="nfcgo-logo__word">NFC</span
      ><span class="nfcgo-logo__go">Go</span>
    </span>
  </span>
</template>

<style scoped>
.nfcgo-logo {
  display: inline-flex;
  align-items: center;
  gap: calc(var(--nfcgo-mark) * 0.16);
  font-family: 'Outfit', 'Poppins', system-ui, sans-serif;
  line-height: 1;
  white-space: nowrap;
  user-select: none;
}

.nfcgo-logo__mark {
  display: block;
  flex-shrink: 0;
  width: var(--nfcgo-mark);
  height: var(--nfcgo-mark);
}

.nfcgo-logo__wordmark {
  font-size: var(--nfcgo-word);
  font-weight: 600;
  letter-spacing: -0.03em;
  line-height: 1;
}

/* Wordmark palette */
.nfcgo-logo--dark .nfcgo-logo__word {
  color: #f6f1e4;
}
.nfcgo-logo--dark .nfcgo-logo__go {
  color: var(--nfcgo-accent, #d4af37);
}
.nfcgo-logo--light .nfcgo-logo__word {
  color: #0a0a0a;
}
.nfcgo-logo--light .nfcgo-logo__go {
  color: #8a6915;
}

/* Size scale */
.nfcgo-logo--xs {
  --nfcgo-mark: 1.15rem;
  --nfcgo-word: 0.9rem;
}
.nfcgo-logo--sm {
  --nfcgo-mark: 1.5rem;
  --nfcgo-word: 1.05rem;
}
.nfcgo-logo--md {
  --nfcgo-mark: 1.85rem;
  --nfcgo-word: 1.3rem;
}
.nfcgo-logo--lg {
  --nfcgo-mark: 2.35rem;
  --nfcgo-word: 1.6rem;
}
.nfcgo-logo--xl {
  --nfcgo-mark: 3rem;
  --nfcgo-word: 2rem;
}
</style>
