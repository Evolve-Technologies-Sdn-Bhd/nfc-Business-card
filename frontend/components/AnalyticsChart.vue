<!-- components/AnalyticsChart.vue -->
<!--
  Dependency-free inline-SVG chart (no chart.js / echarts / CDN).
  Supports:
    - type="line"  : single poly-line through the data points
    - type="area"  : line + translucent fill down to the baseline
    - type="bar"   : vertical bars for breakdown data
  Responsive: measures its own width with ResizeObserver, renders the SVG at
  real pixel size (no distortion, never overflows its container).
  Accessible: role="img" + generated aria-label summarising the series.
-->
<template>
  <div
    ref="containerRef"
    class="w-full overflow-hidden"
    :style="{ height: `${height}px` }"
    @mouseleave="activeIndex = null"
    @touchend="activeIndex = null"
  >
    <svg
      v-if="width > 0"
      :width="width"
      :height="height"
      :viewBox="`0 0 ${width} ${height}`"
      preserveAspectRatio="xMidYMid meet"
      role="img"
      :aria-label="ariaLabel"
      class="block w-full select-none"
    >
      <!-- Horizontal grid lines + y-axis labels -->
      <template v-if="!isEmpty">
        <g v-for="(tick, i) in ticks" :key="`grid-${i}`">
          <line
            :x1="plot.left"
            :x2="plot.right"
            :y1="tick.y"
            :y2="tick.y"
            class="text-secondary-200"
            stroke="currentColor"
            stroke-width="1"
          />
          <text
            :x="plot.left - 6"
            :y="tick.y + 3"
            text-anchor="end"
            class="text-secondary-400"
            fill="currentColor"
            font-size="10"
          >
            {{ formatValue(tick.value) }}
          </text>
        </g>
      </template>

      <!-- Baseline (dashed when the series is empty) -->
      <line
        :x1="plot.left"
        :x2="plot.right"
        :y1="plot.bottom"
        :y2="plot.bottom"
        class="text-secondary-200"
        stroke="currentColor"
        stroke-width="1"
        :stroke-dasharray="isEmpty ? '4 4' : '0'"
      />

      <!-- Area fill (area only) -->
      <path
        v-if="!isEmpty && type === 'area' && areaPath"
        :d="areaPath"
        class="text-primary-500"
        fill="currentColor"
        fill-opacity="0.15"
      />

      <!-- Line (line / area) -->
      <path
        v-if="!isEmpty && type !== 'bar' && linePath"
        :d="linePath"
        class="text-primary-600"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      />

      <!-- Single data point (line / area with 1 entry) -->
      <circle
        v-if="!isEmpty && type !== 'bar' && points.length === 1"
        :cx="points[0].x"
        :cy="points[0].y"
        r="3"
        class="text-primary-600"
        fill="currentColor"
      />

      <!-- Bars -->
      <template v-if="!isEmpty && type === 'bar'">
        <g v-for="(bar, i) in bars" :key="`bar-${i}`">
          <rect
            :x="bar.x"
            :y="bar.y"
            :width="bar.width"
            :height="bar.height"
            class="text-primary-500"
            fill="currentColor"
            :fill-opacity="activeIndex === i ? 1 : 0.75"
            rx="2"
          />
          <text
            v-if="bars.length <= 12 && bar.value > 0"
            :x="bar.cx"
            :y="bar.y - 4"
            text-anchor="middle"
            class="text-secondary-500"
            fill="currentColor"
            font-size="10"
          >
            {{ formatValue(bar.value) }}
          </text>
        </g>
      </template>

      <!-- X-axis labels (thinned out to avoid overlap) -->
      <text
        v-for="(item, i) in xLabelItems"
        :key="`x-${i}`"
        :x="item.x"
        :y="height - 8"
        text-anchor="middle"
        class="text-secondary-400"
        fill="currentColor"
        font-size="10"
      >
        {{ item.text }}
      </text>

      <!-- Invisible hover / tap hit areas -->
      <rect
        v-for="area in hitAreas"
        :key="`hit-${area.index}`"
        :x="area.x"
        :y="plot.top"
        :width="area.width"
        :height="plot.bottom - plot.top"
        fill="transparent"
        @mouseenter="activeIndex = area.index"
        @touchstart.prevent="activeIndex = area.index"
      />

      <!-- Active marker (line / area) -->
      <g v-if="active && type !== 'bar'">
        <line
          :x1="active.x"
          :x2="active.x"
          :y1="plot.top"
          :y2="plot.bottom"
          class="text-secondary-300"
          stroke="currentColor"
          stroke-width="1"
          stroke-dasharray="3 3"
        />
        <circle
          :cx="active.x"
          :cy="active.y"
          r="3.5"
          class="text-primary-600"
          fill="currentColor"
          stroke="#ffffff"
          stroke-width="1.5"
        />
      </g>

      <!-- Tooltip -->
      <g v-if="active">
        <rect
          :x="active.bx"
          :y="active.by"
          :width="active.boxW"
          height="20"
          rx="4"
          class="text-secondary-800"
          fill="currentColor"
          fill-opacity="0.94"
        />
        <text
          :x="active.bx + 8"
          :y="active.by + 14"
          fill="#ffffff"
          font-size="11"
        >
          {{ active.text }}
        </text>
      </g>

      <!-- Empty state -->
      <text
        v-if="isEmpty"
        :x="width / 2"
        :y="height / 2 + 3"
        text-anchor="middle"
        class="text-secondary-400"
        fill="currentColor"
        font-size="12"
      >
        {{ emptyMessage }}
      </text>
    </svg>
  </div>
</template>

<script setup>
const props = defineProps({
  // 'line' | 'area' | 'bar'
  type: { type: String, default: "line" },
  // Array of { label, value }
  data: { type: Array, default: () => [] },
  // Rendered height in px
  height: { type: Number, default: 220 },
  // Optional (v) => string used for axis labels, value labels and tooltips
  valueFormatter: { type: Function, default: null },
  // Shown when the series has no data
  emptyMessage: { type: String, default: "No data yet" },
  // Used in the generated aria-label
  title: { type: String, default: "Analytics chart" },
});

const padding = { top: 16, right: 12, bottom: 26, left: 38 };

const containerRef = ref(null);
const width = ref(0);
const activeIndex = ref(null);

// ── Responsive width ────────────────────────────────────────────────────────
let observer = null;

const measure = () => {
  const el = containerRef.value;
  if (el) width.value = Math.max(0, Math.floor(el.clientWidth));
};

onMounted(() => {
  measure();
  if (typeof ResizeObserver !== "undefined" && containerRef.value) {
    observer = new ResizeObserver((entries) => {
      for (const entry of entries) {
        const w = entry.contentRect?.width;
        if (typeof w === "number") width.value = Math.max(0, Math.floor(w));
      }
    });
    observer.observe(containerRef.value);
  } else if (typeof window !== "undefined") {
    window.addEventListener("resize", measure);
  }
});

onBeforeUnmount(() => {
  if (observer) observer.disconnect();
  else if (typeof window !== "undefined") window.removeEventListener("resize", measure);
});

// ── Helpers ─────────────────────────────────────────────────────────────────
const truncate = (str, max = 9) => {
  const s = String(str ?? "");
  return s.length > max ? `${s.slice(0, Math.max(1, max - 1))}…` : s;
};

const formatNumber = (v) => {
  const n = Number(v) || 0;
  const abs = Math.abs(n);
  if (abs >= 1e9) return `${(n / 1e9).toFixed(1).replace(/\.0$/, "")}B`;
  if (abs >= 1e6) return `${(n / 1e6).toFixed(1).replace(/\.0$/, "")}M`;
  if (abs >= 1e3) return `${(n / 1e3).toFixed(1).replace(/\.0$/, "")}k`;
  if (Number.isInteger(n)) return String(n);
  return String(Math.round(n * 10) / 10);
};

const formatValue = (v) =>
  typeof props.valueFormatter === "function" ? props.valueFormatter(v) : formatNumber(v);

// ── Scales ──────────────────────────────────────────────────────────────────
const values = computed(() => props.data.map((d) => Number(d?.value) || 0));
const isEmpty = computed(() => !Array.isArray(props.data) || props.data.length === 0);

const maxValue = computed(() => {
  const m = values.value.length ? Math.max(...values.value) : 0;
  return m > 0 ? m : 1;
});

const plot = computed(() => ({
  left: padding.left,
  right: Math.max(padding.left + 10, width.value - padding.right),
  top: padding.top,
  bottom: Math.max(padding.top + 10, props.height - padding.bottom),
}));

const yFor = (v) => {
  const p = plot.value;
  return p.bottom - (v / maxValue.value) * (p.bottom - p.top);
};

const points = computed(() => {
  const n = values.value.length;
  const p = plot.value;
  if (!n) return [];
  return values.value.map((v, i) => ({
    x: n === 1 ? (p.left + p.right) / 2 : p.left + (i / (n - 1)) * (p.right - p.left),
    y: yFor(v),
    value: v,
    label: props.data[i]?.label ?? "",
  }));
});

const bars = computed(() => {
  const n = values.value.length;
  const p = plot.value;
  if (!n) return [];
  const band = (p.right - p.left) / n;
  const barWidth = Math.max(2, Math.min(46, band * 0.62));
  return values.value.map((v, i) => {
    const cx = p.left + band * (i + 0.5);
    const y = yFor(v);
    return {
      cx,
      x: cx - barWidth / 2,
      y,
      width: barWidth,
      height: Math.max(0, p.bottom - y),
      value: v,
    };
  });
});

const positions = computed(() =>
  props.type === "bar" ? bars.value.map((b) => b.cx) : points.value.map((pt) => pt.x)
);

const linePath = computed(() => {
  const pts = points.value;
  if (pts.length < 2) return "";
  return pts
    .map((pt, i) => `${i === 0 ? "M" : "L"} ${pt.x.toFixed(2)} ${pt.y.toFixed(2)}`)
    .join(" ");
});

const areaPath = computed(() => {
  const pts = points.value;
  if (pts.length < 2) return "";
  const b = plot.value.bottom;
  return (
    `M ${pts[0].x.toFixed(2)} ${b.toFixed(2)} ` +
    pts.map((pt) => `L ${pt.x.toFixed(2)} ${pt.y.toFixed(2)}`).join(" ") +
    ` L ${pts[pts.length - 1].x.toFixed(2)} ${b.toFixed(2)} Z`
  );
});

const ticks = computed(() => {
  const lines = 3;
  const out = [];
  for (let i = 0; i <= lines; i++) {
    const value = maxValue.value * (i / lines);
    out.push({ value, y: yFor(value) });
  }
  return out;
});

const xLabelItems = computed(() => {
  const n = props.data.length;
  if (!n || !width.value) return [];
  const maxLabels = Math.max(2, Math.min(6, Math.floor(width.value / 72)));
  const step = Math.max(1, Math.ceil(n / maxLabels));
  const out = [];
  for (let i = 0; i < n; i += step) {
    out.push({ x: positions.value[i], text: truncate(props.data[i]?.label ?? "") });
  }
  // Always try to append the last label when it will not collide.
  const lastIdx = n - 1;
  const lastX = positions.value[lastIdx];
  if (n > 1 && out.length && lastX - out[out.length - 1].x > 40) {
    out.push({ x: lastX, text: truncate(props.data[lastIdx]?.label ?? "") });
  }
  return out;
});

const hitAreas = computed(() => {
  const n = props.data.length;
  const p = plot.value;
  if (!n) return [];
  const span = p.right - p.left;
  return props.data.map((_, i) => {
    let x;
    let w;
    if (props.type === "bar") {
      const band = span / n;
      x = p.left + band * i;
      w = band;
    } else if (n === 1) {
      x = p.left;
      w = span;
    } else {
      const cx = p.left + (i / (n - 1)) * span;
      const spacing = span / (n - 1);
      x = cx - spacing / 2;
      w = spacing;
    }
    return { x: Math.max(0, x), width: Math.max(1, w), index: i };
  });
});

const active = computed(() => {
  const i = activeIndex.value;
  if (i === null || i < 0 || i >= props.data.length) return null;
  const v = values.value[i];
  const x = positions.value[i];
  const y = yFor(v);
  const text = `${truncate(props.data[i]?.label ?? "", 14)}: ${formatValue(v)}`;
  const boxW = Math.min(
    Math.max(60, text.length * 6.2 + 16),
    Math.max(60, width.value - 8)
  );
  let bx = x - boxW / 2;
  bx = Math.max(4, Math.min(width.value - boxW - 4, bx));
  const by = Math.max(4, y - 30);
  return { x, y, text, bx, by, boxW };
});

const ariaLabel = computed(() => {
  const n = props.data.length;
  if (!n) return `${props.title} chart: no data`;
  const vals = values.value;
  const total = vals.reduce((a, b) => a + b, 0);
  const max = Math.max(...vals);
  const maxIdx = vals.indexOf(max);
  const first = props.data[0]?.label ?? "";
  const last = props.data[n - 1]?.label ?? "";
  return (
    `${props.title} ${props.type} chart with ${n} data points from ${first} to ${last}. ` +
    `Total ${formatValue(total)}. Peak ${formatValue(max)} at ${props.data[maxIdx]?.label ?? ""}.`
  );
});

watch(
  () => props.data,
  () => {
    activeIndex.value = null;
  }
);
</script>
