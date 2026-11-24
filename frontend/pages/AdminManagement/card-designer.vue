<!-- pages/AdminManagement/card-designer.vue -->
<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
          <h1 class="page-title text-secondary-900">Card Designer</h1>
          <p class="mt-2 text-secondary-600">
            Design NFC business card artwork — background, draggable placeholders, PDF export
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <button @click="newDesign" class="btn btn-outline btn-sm">
            <Icon name="heroicons:document-plus" class="h-4 w-4 mr-1.5" />
            New
          </button>
          <button @click="saveDesign" :disabled="saving" class="btn btn-primary btn-sm">
            <Icon v-if="saving" name="heroicons:arrow-path" class="h-4 w-4 mr-1.5 animate-spin" />
            <Icon v-else name="heroicons:check" class="h-4 w-4 mr-1.5" />
            {{ design.id ? 'Update' : 'Save' }}
          </button>
          <button @click="exportPdf" :disabled="exporting" class="btn btn-success btn-sm">
            <Icon v-if="exporting" name="heroicons:arrow-path" class="h-4 w-4 mr-1.5 animate-spin" />
            <Icon v-else name="heroicons:arrow-down-tray" class="h-4 w-4 mr-1.5" />
            Export PDF
          </button>
        </div>
      </div>
    </div>

    <!-- Design meta -->
    <div class="card mb-6">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="md:col-span-2">
            <label class="block text-label text-secondary-700 mb-1.5">Design Name</label>
            <input v-model="design.name" type="text" class="input" placeholder="e.g. CEO Card - Front & Back" />
          </div>
          <div>
            <label class="block text-label text-secondary-700 mb-1.5">Card Size</label>
            <select v-model="design.card_size" class="input">
              <option v-for="(s, key) in cardSizes" :key="key" :value="key">{{ s.label }}</option>
            </select>
          </div>
          <div>
            <label class="block text-label text-secondary-700 mb-1.5">3mm Bleed</label>
            <label class="flex items-center gap-2 h-[42px] px-3 border border-secondary-300 rounded-lg cursor-pointer">
              <input v-model="design.bleed" type="checkbox" class="rounded text-primary-600" />
              <span class="text-body text-secondary-600">Include bleed area</span>
            </label>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[260px_minmax(0,1fr)_300px] gap-6">
      <!-- LEFT: palette + saved designs -->
      <div class="space-y-6">
        <!-- Side switcher -->
        <div class="card">
          <div class="card-body">
            <div class="grid grid-cols-2 gap-2">
              <button
                @click="activeSide = 'front'"
                class="btn btn-sm"
                :class="activeSide === 'front' ? 'btn-primary' : 'btn-outline'"
              >
                Front
              </button>
              <button
                @click="activeSide = 'back'"
                class="btn btn-sm"
                :class="activeSide === 'back' ? 'btn-primary' : 'btn-outline'"
              >
                Back
              </button>
            </div>
          </div>
        </div>

        <!-- Background -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title text-secondary-900">Background ({{ activeSide }})</h3>
          </div>
          <div class="card-body space-y-3">
            <div
              class="border-2 border-dashed border-secondary-300 rounded-lg p-4 text-center cursor-pointer hover:border-primary-400"
              @click="bgInput?.click()"
            >
              <Icon name="heroicons:photo" class="h-6 w-6 mx-auto text-secondary-400" />
              <p class="mt-1 text-helper">
                {{ uploadingBg ? 'Uploading...' : 'Click to upload (JPG/PNG/WEBP)' }}
              </p>
            </div>
            <input ref="bgInput" type="file" accept="image/*" class="hidden" @change="onBgFile" />
            <div v-if="activeBgUrl" class="flex items-center gap-3">
              <img :src="activeBgUrl" class="h-12 w-20 object-cover rounded border border-secondary-200" />
              <button @click="removeBackground" class="btn btn-outline btn-sm text-error-600 border-error-300">
                <Icon name="heroicons:trash" class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>

        <!-- Placeholders -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title text-secondary-900">Add Element</h3>
          </div>
          <div class="card-body">
            <p class="text-helper mb-3">Click to add, or drag onto the card.</p>
            <div class="grid grid-cols-2 gap-2">
              <button
                v-for="p in palette"
                :key="p.type"
                class="flex flex-col items-center gap-1 p-3 rounded-lg border border-secondary-200 hover:border-primary-400 hover:bg-primary-50 cursor-grab"
                draggable="true"
                @click="addElement(p.type)"
                @dragstart="onPaletteDragStart($event, p.type)"
              >
                <Icon :name="p.icon" class="h-5 w-5 text-primary-600" />
                <span class="text-caption text-secondary-600">{{ p.label }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Saved designs -->
        <div class="card">
          <div class="card-header flex items-center justify-between">
            <h3 class="card-title text-secondary-900">Saved Designs</h3>
            <button @click="loadDesigns" class="text-secondary-400 hover:text-primary-600">
              <Icon name="heroicons:arrow-path" class="h-4 w-4" />
            </button>
          </div>
          <div class="card-body">
            <p v-if="loadingDesigns" class="text-caption text-secondary-500">Loading...</p>
            <p v-else-if="!designs.length" class="text-caption text-secondary-500">No saved designs yet.</p>
            <ul v-else class="space-y-1 max-h-64 overflow-y-auto">
              <li
                v-for="d in designs"
                :key="d.id"
                class="flex items-center justify-between px-2 py-1.5 rounded hover:bg-secondary-100"
                :class="design.id === d.id ? 'bg-primary-50' : ''"
              >
                <button class="text-left text-body text-secondary-700 truncate flex-1" @click="openDesign(d.id)">
                  {{ d.name }}
                </button>
                <button class="text-secondary-400 hover:text-error-600" @click="deleteDesign(d)">
                  <Icon name="heroicons:trash" class="h-4 w-4" />
                </button>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- CENTER: canvas -->
      <div class="space-y-4">
        <div class="card">
          <div class="card-body flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="text-body text-secondary-600">
                {{ activeSide === 'front' ? 'Front side' : 'Back side' }} ·
                {{ cardSize.w }} × {{ cardSize.h }} mm
                <span v-if="design.bleed" class="text-secondary-400">(+3mm bleed)</span>
              </span>
            </div>
            <div class="flex items-center gap-1">
              <button class="btn btn-outline btn-sm" @click="zoom = Math.max(3, +(zoom - 1).toFixed(1))">−</button>
              <span class="text-caption text-secondary-500 w-12 text-center">{{ zoom }}x</span>
              <button class="btn btn-outline btn-sm" @click="zoom = Math.min(12, +(zoom + 1).toFixed(1))">+</button>
            </div>
          </div>
        </div>

        <div
          class="card overflow-auto p-6 flex justify-center bg-secondary-100"
          @click.self="selectedId = null"
        >
          <div
            class="relative shadow-lg"
            :style="{ width: pageW * zoom + 'px', height: pageH * zoom + 'px', background: '#ffffff' }"
            @dragover.prevent
            @drop="onCanvasDrop"
          >
            <!-- background -->
            <img
              v-if="activeBgUrl"
              :src="activeBgUrl"
              class="absolute inset-0 w-full h-full object-cover pointer-events-none select-none"
              alt=""
            />
            <!-- bleed/trim guide -->
            <div
              v-if="design.bleed"
              class="absolute border border-dashed border-primary-400 pointer-events-none"
              :style="{ left: bleedPx + 'px', top: bleedPx + 'px', width: cardSize.w * zoom + 'px', height: cardSize.h * zoom + 'px' }"
            ></div>

            <!-- elements -->
            <div
              class="absolute"
              :style="{ left: bleedPx + 'px', top: bleedPx + 'px', width: cardSize.w * zoom + 'px', height: cardSize.h * zoom + 'px' }"
              @click.self="selectedId = null"
            >
              <div
                v-for="el in elements"
                :key="el.id"
                class="absolute cursor-move"
                :class="selectedId === el.id ? 'ring-2 ring-primary-500' : ''"
                :style="elementStyle(el)"
                @pointerdown="beginDrag($event, el, 'move')"
                @click.stop="selectedId = el.id"
              >
                <!-- text -->
                <template v-if="el.type === 'text'">
                  <div class="w-full h-full" :style="textInnerStyle(el)">{{ el.text }}</div>
                </template>
                <!-- image -->
                <template v-else-if="el.type === 'image'">
                  <img v-if="el.src" :src="el.src" class="w-full h-full pointer-events-none" :style="{ objectFit: el.fit, opacity: el.opacity }" />
                  <div v-else class="w-full h-full bg-secondary-200 border border-secondary-300 flex items-center justify-center text-micro text-secondary-500">
                    IMAGE
                  </div>
                </template>
                <!-- qr -->
                <template v-else-if="el.type === 'qr'">
                  <img :src="qrUrl(el)" class="w-full h-full pointer-events-none" alt="QR" />
                </template>
                <!-- rect -->
                <template v-else-if="el.type === 'rect'">
                  <div class="w-full h-full" :style="rectStyle(el)"></div>
                </template>

                <!-- resize handle -->
                <div
                  v-if="selectedId === el.id"
                  class="absolute -bottom-1.5 -right-1.5 w-3 h-3 bg-primary-600 border border-white rounded-sm cursor-se-resize"
                  @pointerdown.stop="beginDrag($event, el, 'resize')"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT: properties -->
      <div class="space-y-6">
        <div class="card">
          <div class="card-header flex items-center justify-between">
            <h3 class="card-title text-secondary-900">Properties</h3>
            <button v-if="selected" @click="deleteSelected" class="text-secondary-400 hover:text-error-600">
              <Icon name="heroicons:trash" class="h-4 w-4" />
            </button>
          </div>
          <div class="card-body">
            <p v-if="!selected" class="text-body text-secondary-500">
              Select an element on the card to edit its properties.
            </p>

            <div v-else class="space-y-4">
              <!-- geometry -->
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="form-label">X (mm)</label>
                  <input v-model.number="selected.x" type="number" step="0.5" class="input" />
                </div>
                <div>
                  <label class="form-label">Y (mm)</label>
                  <input v-model.number="selected.y" type="number" step="0.5" class="input" />
                </div>
                <div>
                  <label class="form-label">Width (mm)</label>
                  <input v-model.number="selected.w" type="number" step="0.5" min="2" class="input" />
                </div>
                <div>
                  <label class="form-label">Height (mm)</label>
                  <input v-model.number="selected.h" type="number" step="0.5" min="2" class="input" />
                </div>
                <div class="col-span-2">
                  <label class="form-label">Rotation (°)</label>
                  <input v-model.number="selected.rotation" type="range" min="-45" max="45" step="1" class="w-full" />
                  <span class="text-caption text-secondary-500">{{ selected.rotation }}°</span>
                </div>
              </div>

              <hr class="border-secondary-200" />

              <!-- text -->
              <template v-if="selected.type === 'text'">
                <div>
                  <label class="form-label">Text</label>
                  <textarea v-model="selected.text" rows="2" class="input"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="form-label">Font</label>
                    <select v-model="selected.fontFamily" class="input">
                      <option value="helvetica">Helvetica</option>
                      <option value="times">Times</option>
                      <option value="courier">Courier</option>
                    </select>
                  </div>
                  <div>
                    <label class="form-label">Size (pt)</label>
                    <input v-model.number="selected.fontSize" type="number" min="4" max="72" class="input" />
                  </div>
                  <div>
                    <label class="form-label">Color</label>
                    <input v-model="selected.color" type="color" class="input h-[38px] p-1" />
                  </div>
                  <div>
                    <label class="form-label">Weight</label>
                    <select v-model="selected.fontWeight" class="input">
                      <option value="normal">Normal</option>
                      <option value="bold">Bold</option>
                    </select>
                  </div>
                  <div class="col-span-2">
                    <label class="form-label">Align</label>
                    <select v-model="selected.align" class="input">
                      <option value="left">Left</option>
                      <option value="center">Center</option>
                      <option value="right">Right</option>
                    </select>
                  </div>
                </div>
              </template>

              <!-- image -->
              <template v-else-if="selected.type === 'image'">
                <div>
                  <label class="form-label">Image</label>
                  <button class="btn btn-outline btn-sm w-full" @click="imgInput?.click()">
                    <Icon name="heroicons:arrow-up-tray" class="h-4 w-4 mr-1.5" />
                    Upload image
                  </button>
                  <input ref="imgInput" type="file" accept="image/*" class="hidden" @change="onElementImage" />
                </div>
                <div>
                  <label class="form-label">Fit</label>
                  <select v-model="selected.fit" class="input">
                    <option value="contain">Contain</option>
                    <option value="cover">Cover</option>
                  </select>
                </div>
                <div>
                  <label class="form-label">Opacity ({{ selected.opacity }})</label>
                  <input v-model.number="selected.opacity" type="range" min="0.1" max="1" step="0.1" class="w-full" />
                </div>
              </template>

              <!-- qr -->
              <template v-else-if="selected.type === 'qr'">
                <div>
                  <label class="form-label">QR content (URL / text)</label>
                  <input v-model="selected.value" type="text" class="input" placeholder="https://..." />
                </div>
              </template>

              <!-- rect -->
              <template v-else-if="selected.type === 'rect'">
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="form-label">Fill</label>
                    <input v-model="selected.fill" type="color" class="input h-[38px] p-1" />
                  </div>
                  <div>
                    <label class="form-label">Radius (mm)</label>
                    <input v-model.number="selected.borderRadius" type="number" min="0" step="0.5" class="input" />
                  </div>
                  <div>
                    <label class="form-label">Border</label>
                    <input v-model.number="selected.borderWidth" type="number" min="0" step="0.1" class="input" />
                  </div>
                  <div>
                    <label class="form-label">Border color</label>
                    <input v-model="selected.borderColor" type="color" class="input h-[38px] p-1" />
                  </div>
                </div>
              </template>

              <hr class="border-secondary-200" />
              <div class="flex items-center justify-between gap-2">
                <button class="btn btn-outline btn-sm" @click="duplicateSelected">
                  <Icon name="heroicons:document-duplicate" class="h-4 w-4 mr-1" /> Duplicate
                </button>
                <div class="flex gap-1">
                  <button class="btn btn-outline btn-sm" title="Backward" @click="moveLayer(-1)">
                    <Icon name="heroicons:chevron-down" class="h-4 w-4" />
                  </button>
                  <button class="btn btn-outline btn-sm" title="Forward" @click="moveLayer(1)">
                    <Icon name="heroicons:chevron-up" class="h-4 w-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <label class="form-label">Notes</label>
            <textarea v-model="design.notes" rows="3" class="input" placeholder="Internal notes (paper, finish, etc.)"></textarea>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { toRaw } from 'vue';

definePageMeta({
  layout: 'admin-management',
  middleware: 'admin',
});

const { $api, $toast } = useNuxtApp();

// ── Constants ───────────────────────────────────────────────
const cardSizes = {
  '90x54': { label: '90 × 54 mm (Standard MY/EU)', w: 90, h: 54 },
  '88.9x50.8': { label: '88.9 × 50.8 mm (US)', w: 88.9, h: 50.8 },
};
const PT_TO_MM = 0.352778;
const BLEED_MM = 3;
const QR_ENDPOINT = 'https://api.qrserver.com/v1/create-qr-code/';

const palette = [
  { type: 'text', label: 'Text', icon: 'heroicons:language' },
  { type: 'image', label: 'Image', icon: 'heroicons:photo' },
  { type: 'qr', label: 'QR Code', icon: 'heroicons:qr-code' },
  { type: 'rect', label: 'Shape', icon: 'heroicons:square-2-stack' },
];

// ── State ───────────────────────────────────────────────────
const activeSide = ref('front');
const selectedId = ref(null);
const zoom = ref(7);
const saving = ref(false);
const exporting = ref(false);
const uploadingBg = ref(false);
const loadingDesigns = ref(false);
const designs = ref([]);
const bgInput = ref(null);
const imgInput = ref(null);

const design = reactive({
  id: null,
  name: '',
  card_size: '90x54',
  bleed: false,
  background_front_url: null,
  background_back_url: null,
  front_elements: [],
  back_elements: [],
  notes: '',
  is_active: true,
});

// ── Computed ────────────────────────────────────────────────
const cardSize = computed(() => cardSizes[design.card_size] || cardSizes['90x54']);
const bleedMm = computed(() => (design.bleed ? BLEED_MM : 0));
const bleedPx = computed(() => bleedMm.value * zoom.value);
const pageW = computed(() => cardSize.value.w + bleedMm.value * 2);
const pageH = computed(() => cardSize.value.h + bleedMm.value * 2);
const elements = computed(() => design[`${activeSide.value}_elements`] || []);
const activeBgUrl = computed(() => design[`background_${activeSide.value}_url`]);
const selected = computed(() => elements.value.find((e) => e.id === selectedId.value) || null);

// ── Helpers ─────────────────────────────────────────────────
const uid = () => 'el_' + Math.random().toString(36).slice(2, 9);
const round = (v) => Math.round(v * 10) / 10;
const clamp = (v, min, max) => Math.min(Math.max(v, min), max);

const qrUrl = (el) =>
  `${QR_ENDPOINT}?size=300x300&margin=0&data=${encodeURIComponent(el.value || '')}`;

function elementStyle(el) {
  return {
    left: el.x * zoom.value + 'px',
    top: el.y * zoom.value + 'px',
    width: Math.max(el.w * zoom.value, 6) + 'px',
    height: Math.max(el.h * zoom.value, 6) + 'px',
    transform: `rotate(${el.rotation || 0}deg)`,
  };
}

function textInnerStyle(el) {
  return {
    fontFamily: el.fontFamily,
    fontSize: el.fontSize * PT_TO_MM * zoom.value + 'px',
    fontWeight: el.fontWeight,
    color: el.color,
    textAlign: el.align,
    lineHeight: el.lineHeight || 1.2,
    whiteSpace: 'pre-wrap',
    wordBreak: 'break-word',
    overflow: 'hidden',
  };
}

function rectStyle(el) {
  return {
    background: el.fill,
    borderRadius: (el.borderRadius || 0) * zoom.value + 'px',
    border: el.borderWidth ? `${Math.max(el.borderWidth * zoom.value, 1)}px solid ${el.borderColor}` : 'none',
    opacity: el.opacity ?? 1,
  };
}

// ── Element CRUD ────────────────────────────────────────────
function makeElement(type, x = 8, y = 8) {
  const base = { id: uid(), type, x, y, w: 30, h: 10, rotation: 0 };
  if (type === 'text')
    return { ...base, w: 40, h: 8, text: 'Your Name', fontSize: 11, fontFamily: 'helvetica', color: '#1c1a15', fontWeight: 'bold', align: 'left', lineHeight: 1.2 };
  if (type === 'image') return { ...base, w: 20, h: 20, src: '', fit: 'contain', opacity: 1 };
  if (type === 'qr') return { ...base, w: 18, h: 18, value: 'https://nfcgo.clbgroups.com' };
  if (type === 'rect') return { ...base, w: 30, h: 12, fill: '#3d496a', borderRadius: 1, borderWidth: 0, borderColor: '#000000', opacity: 1 };
  return base;
}

function pushElement(el) {
  design[`${activeSide.value}_elements`].push(el);
  selectedId.value = el.id;
}

function addElement(type) {
  const cw = cardSize.value.w;
  const ch = cardSize.value.h;
  const el = makeElement(type, round((cw - 30) / 2), round((ch - 10) / 2));
  pushElement(el);
}

function deleteSelected() {
  const arr = design[`${activeSide.value}_elements`];
  const idx = arr.findIndex((e) => e.id === selectedId.value);
  if (idx > -1) arr.splice(idx, 1);
  selectedId.value = null;
}

function duplicateSelected() {
  if (!selected.value) return;
  const copy = JSON.parse(JSON.stringify(toRaw(selected.value)));
  copy.id = uid();
  copy.x = round(copy.x + 2);
  copy.y = round(copy.y + 2);
  pushElement(copy);
}

function moveLayer(dir) {
  const arr = design[`${activeSide.value}_elements`];
  const idx = arr.findIndex((e) => e.id === selectedId.value);
  if (idx < 0) return;
  const target = idx + dir;
  if (target < 0 || target >= arr.length) return;
  const [item] = arr.splice(idx, 1);
  arr.splice(target, 0, item);
}

// ── Drag & resize ───────────────────────────────────────────
let dragState = null;

function beginDrag(e, el, mode) {
  e.preventDefault();
  selectedId.value = el.id;
  dragState = {
    el,
    mode,
    startX: e.clientX,
    startY: e.clientY,
    orig: { x: el.x, y: el.y, w: el.w, h: el.h },
  };
  window.addEventListener('pointermove', onDragMove);
  window.addEventListener('pointerup', endDrag);
}

function onDragMove(e) {
  if (!dragState) return;
  const { el, mode, orig } = dragState;
  const dx = (e.clientX - dragState.startX) / zoom.value;
  const dy = (e.clientY - dragState.startY) / zoom.value;
  const cw = cardSize.value.w;
  const ch = cardSize.value.h;

  if (mode === 'move') {
    el.x = round(clamp(orig.x + dx, 0, cw - el.w));
    el.y = round(clamp(orig.y + dy, 0, ch - el.h));
  } else {
    el.w = round(clamp(orig.w + dx, 3, cw - el.x));
    el.h = round(clamp(orig.h + dy, 3, ch - el.y));
  }
}

function endDrag() {
  dragState = null;
  window.removeEventListener('pointermove', onDragMove);
  window.removeEventListener('pointerup', endDrag);
}

// ── Native drag from palette ────────────────────────────────
function onPaletteDragStart(e, type) {
  e.dataTransfer.setData('text/nfc-element', type);
  e.dataTransfer.effectAllowed = 'copy';
}

function onCanvasDrop(e) {
  const type = e.dataTransfer.getData('text/nfc-element');
  if (!type) return;
  const rect = e.currentTarget.getBoundingClientRect();
  const x = round(clamp((e.clientX - rect.left) / zoom.value - bleedMm.value, 0, cardSize.value.w - 5));
  const y = round(clamp((e.clientY - rect.top) / zoom.value - bleedMm.value, 0, cardSize.value.h - 5));
  pushElement(makeElement(type, x, y));
}

// ── Background / images ─────────────────────────────────────
async function onBgFile(e) {
  const file = e.target.files?.[0];
  if (!file) return;
  const fd = new FormData();
  fd.append('file', file);
  fd.append('side', activeSide.value);
  uploadingBg.value = true;
  try {
    const res = await $api.post('/admin/card-designs/upload-background', fd);
    if (res?.success && res.url) {
      design[`background_${activeSide.value}_url`] = res.url;
      $toast.success('Background uploaded');
    }
  } catch (err) {
    console.error(err);
    $toast.error('Failed to upload background');
  } finally {
    uploadingBg.value = false;
    if (bgInput.value) bgInput.value.value = '';
  }
}

function removeBackground() {
  design[`background_${activeSide.value}_url`] = null;
}

async function onElementImage(e) {
  const file = e.target.files?.[0];
  if (!file || !selected.value) return;
  try {
    selected.value.src = await fileToDataURL(file);
  } catch (err) {
    $toast.error('Failed to read image');
  } finally {
    if (imgInput.value) imgInput.value.value = '';
  }
}

// ── Persistence ─────────────────────────────────────────────
async function loadDesigns() {
  loadingDesigns.value = true;
  try {
    const res = await $api.get('/admin/card-designs');
    if (res?.success) designs.value = res.data || [];
  } catch (err) {
    console.error(err);
  } finally {
    loadingDesigns.value = false;
  }
}

function newDesign() {
  design.id = null;
  design.name = '';
  design.card_size = '90x54';
  design.bleed = false;
  design.background_front_url = null;
  design.background_back_url = null;
  design.front_elements = [];
  design.back_elements = [];
  design.notes = '';
  design.is_active = true;
  activeSide.value = 'front';
  selectedId.value = null;
}

async function openDesign(id) {
  try {
    const res = await $api.get(`/admin/card-designs/${id}`);
    if (res?.success && res.data) {
      const d = res.data;
      design.id = d.id;
      design.name = d.name || '';
      design.card_size = cardSizes[d.card_size] ? d.card_size : '90x54';
      design.bleed = !!d.bleed;
      design.background_front_url = d.background_front_url || null;
      design.background_back_url = d.background_back_url || null;
      design.front_elements = Array.isArray(d.front_elements) ? d.front_elements : [];
      design.back_elements = Array.isArray(d.back_elements) ? d.back_elements : [];
      design.notes = d.notes || '';
      design.is_active = d.is_active !== false;
      activeSide.value = 'front';
      selectedId.value = null;
    }
  } catch (err) {
    console.error(err);
    $toast.error('Failed to open design');
  }
}

async function saveDesign() {
  if (!design.name.trim()) {
    $toast.error('Please enter a design name');
    return;
  }
  saving.value = true;
  const payload = {
    name: design.name.trim(),
    card_size: design.card_size,
    bleed: design.bleed,
    background_front_url: design.background_front_url,
    background_back_url: design.background_back_url,
    front_elements: toRaw(design.front_elements),
    back_elements: toRaw(design.back_elements),
    notes: design.notes,
    is_active: design.is_active,
  };
  try {
    if (design.id) {
      const res = await $api.put(`/admin/card-designs/${design.id}`, payload);
      if (res?.success) $toast.success('Design updated');
    } else {
      const res = await $api.post('/admin/card-designs', payload);
      if (res?.success) {
        design.id = res.data?.id ?? null;
        $toast.success('Design saved');
      }
    }
    await loadDesigns();
  } catch (err) {
    console.error(err);
    $toast.error(err?.data?.message || 'Failed to save design');
  } finally {
    saving.value = false;
  }
}

async function deleteDesign(d) {
  if (!confirm(`Delete design "${d.name}"?`)) return;
  try {
    await $api.delete(`/admin/card-designs/${d.id}`);
    if (design.id === d.id) newDesign();
    await loadDesigns();
    $toast.success('Design deleted');
  } catch (err) {
    console.error(err);
    $toast.error('Failed to delete design');
  }
}

// ── PDF export ──────────────────────────────────────────────
function fileToDataURL(file) {
  return new Promise((resolve, reject) => {
    const fr = new FileReader();
    fr.onload = () => resolve(fr.result);
    fr.onerror = reject;
    fr.readAsDataURL(file);
  });
}

function loadImage(src) {
  return new Promise((resolve, reject) => {
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => resolve(img);
    img.onerror = () => reject(new Error('image load failed'));
    img.src = src;
  });
}

async function urlToDataURL(url) {
  if (!url) return null;
  if (url.startsWith('data:')) return url;
  try {
    const img = await loadImage(url);
    const c = document.createElement('canvas');
    c.width = img.naturalWidth || 600;
    c.height = img.naturalHeight || 400;
    c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
    return c.toDataURL('image/png');
  } catch (_) {
    const res = await fetch(url, { mode: 'cors' });
    const blob = await res.blob();
    return fileToDataURL(new File([blob], 'img.png', { type: blob.type || 'image/png' }));
  }
}

const EXPORT_PX_PER_MM = 12;

// Draw a rect/image onto a canvas (with optional rotation) → dataURL.
async function renderShape(el, pxPerMm) {
  const wPx = Math.max(Math.round(el.w * pxPerMm), 2);
  const hPx = Math.max(Math.round(el.h * pxPerMm), 2);
  const deg = el.rotation || 0;
  const rad = (deg * Math.PI) / 180;
  const cos = Math.abs(Math.cos(rad));
  const sin = Math.abs(Math.sin(rad));
  const cw = Math.ceil(wPx * cos + hPx * sin);
  const ch = Math.ceil(wPx * sin + hPx * cos);

  const canvas = document.createElement('canvas');
  canvas.width = cw;
  canvas.height = ch;
  const ctx = canvas.getContext('2d');
  ctx.translate(cw / 2, ch / 2);
  ctx.rotate(rad);

  if (el.type === 'rect') {
    ctx.globalAlpha = el.opacity ?? 1;
    ctx.fillStyle = el.fill || '#000000';
    const r = Math.min((el.borderRadius || 0) * pxPerMm, Math.min(wPx, hPx) / 2);
    roundRectPath(ctx, -wPx / 2, -hPx / 2, wPx, hPx, r);
    ctx.fill();
    if (el.borderWidth) {
      ctx.lineWidth = Math.max(el.borderWidth * pxPerMm, 1);
      ctx.strokeStyle = el.borderColor || '#000000';
      ctx.stroke();
    }
  } else if (el.type === 'image' && el.src) {
    const dataURL = await urlToDataURL(el.src);
    const img = await loadImage(dataURL);
    ctx.globalAlpha = el.opacity ?? 1;
    if (el.fit === 'cover') {
      drawCover(ctx, img, -wPx / 2, -hPx / 2, wPx, hPx);
    } else {
      ctx.drawImage(img, -wPx / 2, -hPx / 2, wPx, hPx);
    }
  } else if (el.type === 'qr') {
    const dataURL = await urlToDataURL(qrUrl(el));
    const img = await loadImage(dataURL);
    ctx.drawImage(img, -wPx / 2, -hPx / 2, wPx, hPx);
  }

  return { dataURL: canvas.toDataURL('image/png'), wMm: cw / pxPerMm, hMm: ch / pxPerMm };
}

function roundRectPath(ctx, x, y, w, h, r) {
  ctx.beginPath();
  ctx.moveTo(x + r, y);
  ctx.arcTo(x + w, y, x + w, y + h, r);
  ctx.arcTo(x + w, y + h, x, y + h, r);
  ctx.arcTo(x, y + h, x, y, r);
  ctx.arcTo(x, y, x + w, y, r);
  ctx.closePath();
}

function drawCover(ctx, img, dx, dy, dw, dh) {
  const ir = img.width / img.height;
  const r = dw / dh;
  let sx, sy, sw, sh;
  if (ir > r) {
    sh = img.height;
    sw = sh * r;
    sx = (img.width - sw) / 2;
    sy = 0;
  } else {
    sw = img.width;
    sh = sw / r;
    sx = 0;
    sy = (img.height - sh) / 2;
  }
  ctx.drawImage(img, sx, sy, sw, sh, dx, dy, dw, dh);
}

function hexToRgb(hex) {
  const m = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex || '#000000');
  return m ? [parseInt(m[1], 16), parseInt(m[2], 16), parseInt(m[3], 16)] : [0, 0, 0];
}

async function exportPdf() {
  if (!design.name.trim()) {
    $toast.error('Please enter a design name before exporting');
    return;
  }
  exporting.value = true;
  try {
    const { jsPDF } = await import('jspdf');
    const b = design.bleed ? BLEED_MM : 0;
    const pw = cardSize.value.w + b * 2;
    const ph = cardSize.value.h + b * 2;
    const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: [pw, ph] });

    const sides = ['front', 'back'].filter((s) => {
      const hasEls = (design[`${s}_elements`] || []).length > 0;
      const hasBg = !!design[`background_${s}_url`];
      return hasEls || hasBg;
    });
    const usedSides = sides.length ? sides : ['front'];

    for (let i = 0; i < usedSides.length; i++) {
      const side = usedSides[i];
      if (i > 0) doc.addPage([pw, ph], 'landscape');

      // Background across the full bleed page.
      const bgUrl = design[`background_${side}_url`];
      if (bgUrl) {
        try {
          const bgData = await urlToDataURL(bgUrl);
          doc.addImage(bgData, 'PNG', 0, 0, pw, ph, undefined, 'FAST');
        } catch (err) {
          console.warn('Background export skipped', err);
        }
      }

      const els = design[`${side}_elements`] || [];
      for (const el of els) {
        const x = el.x + b;
        const y = el.y + b;
        if (el.type === 'text') {
          const [r, g, bl] = hexToRgb(el.color);
          doc.setTextColor(r, g, bl);
          doc.setFont(el.fontFamily || 'helvetica', el.fontWeight === 'bold' ? 'bold' : 'normal');
          doc.setFontSize(el.fontSize || 10);
          const lineH = (el.fontSize || 10) * PT_TO_MM * (el.lineHeight || 1.2);
          const lines = String(el.text || '').split('\n');
          let tx = x;
          let align = 'left';
          if (el.align === 'center') {
            tx = x + el.w / 2;
            align = 'center';
          } else if (el.align === 'right') {
            tx = x + el.w;
            align = 'right';
          }
          lines.forEach((line, idx) => {
            doc.text(line, tx, y + (el.fontSize || 10) * PT_TO_MM * 0.82 + idx * lineH, {
              align,
              angle: el.rotation ? -el.rotation : 0,
            });
          });
        } else if (el.type === 'image' || el.type === 'rect' || el.type === 'qr') {
          try {
            const { dataURL, wMm, hMm } = await renderShape(el, EXPORT_PX_PER_MM);
            const offX = x - (wMm - el.w) / 2;
            const offY = y - (hMm - el.h) / 2;
            doc.addImage(dataURL, 'PNG', offX, offY, wMm, hMm, undefined, 'FAST');
          } catch (err) {
            console.warn(`Element ${el.type} export skipped`, err);
          }
        }
      }
    }

    const safe = design.name.trim().replace(/[^a-z0-9\-_]+/gi, '-').toLowerCase();
    doc.save(`${safe || 'business-card'}.pdf`);
    $toast.success('PDF exported');
  } catch (err) {
    console.error(err);
    $toast.error('Failed to export PDF');
  } finally {
    exporting.value = false;
  }
}

// ── Lifecycle ───────────────────────────────────────────────
onMounted(loadDesigns);
onBeforeUnmount(endDrag);
</script>
