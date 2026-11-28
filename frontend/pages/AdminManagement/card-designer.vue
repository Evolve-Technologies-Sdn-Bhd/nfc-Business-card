<!-- pages/AdminManagement/card-designer.vue -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Design Studio"
      eyebrow-icon="heroicons:paint-brush"
      title="Card Designer"
      description="Compose NFC business card artwork — background, draggable placeholders, and PDF export."
    >
      <template #actions>
        <button
          type="button"
          class="admin-btn admin-btn--outline admin-btn--sm"
          @click="newDesign"
        >
          <Icon name="heroicons:document-plus" class="h-4 w-4" />
          New
        </button>
        <button
          type="button"
          class="admin-btn admin-btn--primary admin-btn--sm"
          :disabled="saving"
          @click="saveDesign"
        >
          <Icon
            :name="saving ? 'heroicons:arrow-path' : 'heroicons:check'"
            class="h-4 w-4"
            :class="{ 'animate-spin': saving }"
          />
          {{ design.id ? 'Update' : 'Save' }}
        </button>
        <button
          type="button"
          class="admin-btn admin-btn--outline admin-btn--sm"
          :disabled="exporting"
          @click="exportPdf"
        >
          <Icon
            :name="exporting ? 'heroicons:arrow-path' : 'heroicons:arrow-down-tray'"
            class="h-4 w-4"
            :class="{ 'animate-spin': exporting }"
          />
          Export PDF
        </button>
        <button
          type="button"
          class="admin-btn admin-btn--outline admin-btn--sm"
          @click="openTemplateModal"
        >
          <Icon name="heroicons:rectangle-stack" class="h-4 w-4" />
          Save as Template
        </button>
      </template>
    </AdminPageHeader>

    <!-- TOP: design details -->
    <AdminSectionCard class="mb-6">
      <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-2 xl:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)_auto_auto]">
        <div>
          <label class="admin-label">Design name</label>
          <input
            v-model="design.name"
            type="text"
            class="admin-field"
            placeholder="e.g. CEO Card - Front & Back"
          />
        </div>
        <div>
          <label class="admin-label">Card size</label>
          <select v-model="design.card_size" class="admin-field">
            <option v-for="(s, key) in cardSizes" :key="key" :value="key">
              {{ s.label }}
            </option>
          </select>
        </div>
        <label class="flex h-[42px] cursor-pointer items-center gap-2 whitespace-nowrap rounded-lg border border-secondary-200 bg-secondary-50 px-3">
          <input
            v-model="design.bleed"
            type="checkbox"
            class="h-4 w-4 rounded border-secondary-300 text-accent-600 focus:ring-accent-500"
          />
          <span class="text-body text-secondary-600">3mm bleed</span>
        </label>
        <div>
          <label class="admin-label">Editing side</label>
          <div class="inline-flex rounded-lg border border-secondary-200 bg-secondary-50 p-1" role="tablist">
            <button
              v-for="s in ['front', 'back']"
              :key="s"
              type="button"
              role="tab"
              :aria-selected="activeSide === s"
              class="rounded-md px-4 py-1.5 text-body-sm font-semibold capitalize transition-colors"
              :class="activeSide === s ? 'bg-primary-900 text-white shadow-sm' : 'text-secondary-600 hover:text-secondary-900'"
              @click="switchSide(s)"
            >
              {{ s }}
            </button>
          </div>
        </div>
      </div>
    </AdminSectionCard>

    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[220px_minmax(0,1fr)] xl:grid-cols-[220px_minmax(0,1fr)_300px]">
      <!-- LEFT: tools -->
      <div class="lg:sticky lg:top-24">
        <AdminSectionCard title="Add element">
          <p class="-mt-1 mb-3 text-caption text-secondary-500">Click, or drag onto the card.</p>

          <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.08em] text-secondary-400">Basic</p>
          <div class="grid grid-cols-3 gap-1.5">
            <button
              v-for="p in palette"
              :key="p.type"
              type="button"
              class="flex cursor-grab flex-col items-center gap-1 whitespace-nowrap rounded-lg border border-secondary-200 px-1 py-2.5 transition hover:border-accent-400 hover:bg-accent-50"
              draggable="true"
              @click="addElement(p.type)"
              @dragstart="onPaletteDragStart($event, p.type)"
            >
              <Icon :name="p.icon" class="h-5 w-5 text-accent-700" />
              <span class="text-[11px] text-secondary-600">{{ p.label }}</span>
            </button>
          </div>

          <p class="mb-2 mt-5 text-[11px] font-bold uppercase tracking-[0.08em] text-secondary-400">Customer data</p>
          <p class="-mt-1 mb-2 text-caption text-secondary-500">Filled with each customer's own details.</p>
          <div class="flex flex-col gap-1.5">
            <button
              v-for="f in DATA_PALETTE"
              :key="f.field"
              type="button"
              class="flex cursor-grab items-center gap-2 rounded-lg border px-2.5 py-2 text-left text-caption transition"
              :class="fieldOnSide(f.field)
                ? 'border-primary-200 bg-primary-50 text-primary-800'
                : 'border-secondary-200 text-secondary-700 hover:border-accent-400 hover:bg-accent-50'"
              draggable="true"
              @click="addDataElement(f.field)"
              @dragstart="onPaletteDragStart($event, 'data:' + f.field)"
            >
              <Icon :name="f.icon" class="h-4 w-4 shrink-0 text-accent-700" />
              <span class="flex-1 truncate">{{ f.label }}</span>
              <Icon v-if="fieldOnSide(f.field)" name="heroicons:check-circle" class="h-4 w-4 shrink-0 text-primary-600" />
            </button>
          </div>
        </AdminSectionCard>
      </div>

      <!-- CENTER: canvas -->
      <div class="min-w-0 space-y-4">
        <AdminSectionCard flush accent>
          <template #title>
            <span class="whitespace-nowrap">{{ activeSide === 'front' ? 'Front side' : 'Back side' }}</span>
          </template>
          <template #header-actions>
            <span class="hidden whitespace-nowrap text-caption text-secondary-500 2xl:inline">
              {{ cardSize.w }} × {{ cardSize.h }} mm
              <span v-if="design.bleed" class="text-secondary-400">(+3mm bleed)</span>
            </span>
            <div class="flex items-center gap-1">
              <button
                type="button"
                class="admin-btn admin-btn--outline admin-btn--sm admin-btn--icon"
                :disabled="zoom <= 3"
                @click="autoFit = false; zoom = Math.max(3, +(zoom - 1).toFixed(1))"
              >
                −
              </button>
              <span class="w-12 text-center text-caption text-secondary-500">{{ zoom }}x</span>
              <button
                type="button"
                class="admin-btn admin-btn--outline admin-btn--sm admin-btn--icon"
                :disabled="zoom >= 12"
                @click="autoFit = false; zoom = Math.min(12, +(zoom + 1).toFixed(1))"
              >
                +
              </button>
              <button
                type="button"
                class="admin-btn admin-btn--sm"
                :class="autoFit ? 'admin-btn--primary' : 'admin-btn--outline'"
                title="Fit the card to the available space"
                @click="autoFit = true; fitZoom()"
              >
                Fit
              </button>
            </div>
          </template>

          <div
            ref="stageEl"
            class="flex min-h-[420px] items-center justify-center overflow-auto bg-secondary-50 p-6"
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
                class="pointer-events-none absolute inset-0 h-full w-full select-none object-cover"
                alt=""
              />
              <!-- bleed/trim guide -->
              <div
                v-if="design.bleed"
                class="pointer-events-none absolute border border-dashed border-primary-400"
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
                    <div class="h-full w-full" :style="textInnerStyle(el)">{{ el.text }}</div>
                  </template>
                  <!-- image -->
                  <template v-else-if="el.type === 'image'">
                    <img
                      v-if="el.src"
                      :src="el.src"
                      class="pointer-events-none h-full w-full"
                      :style="{ objectFit: el.fit, opacity: el.opacity }"
                    />
                    <div
                      v-else
                      class="flex h-full w-full items-center justify-center border border-secondary-300 bg-secondary-200 text-micro text-secondary-500"
                      :class="{ 'border-dashed !border-primary-400 !bg-primary-50 !text-primary-700': el.field === 'logo' }"
                    >
                      {{ el.field === 'logo' ? 'LOGO' : 'IMAGE' }}
                    </div>
                  </template>
                  <!-- qr -->
                  <template v-else-if="el.type === 'qr'">
                    <img :src="qrUrl(el)" class="pointer-events-none h-full w-full" alt="QR" />
                  </template>
                  <!-- rect -->
                  <template v-else-if="el.type === 'rect'">
                    <div class="h-full w-full" :style="rectStyle(el)"></div>
                  </template>

                  <!-- data-bound badge -->
                  <span
                    v-if="el.field"
                    class="pointer-events-none absolute -top-2 left-0 rounded bg-primary-600 px-1 text-[9px] font-semibold leading-[14px] text-white"
                  >{{ fieldLabel(el.field) }}</span>

                  <!-- resize handle -->
                  <div
                    v-if="selectedId === el.id"
                    class="absolute -bottom-1.5 -right-1.5 h-3 w-3 cursor-se-resize rounded-sm border border-white bg-primary-600"
                    @pointerdown.stop="beginDrag($event, el, 'resize')"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </AdminSectionCard>
      </div>

      <!-- RIGHT: properties -->
      <div class="space-y-6 lg:col-span-2 xl:sticky xl:top-24 xl:col-span-1">
        <AdminSectionCard>
          <template #title>Background <span class="font-normal capitalize text-secondary-500">· {{ activeSide }}</span></template>
          <div
            v-if="activeBgUrl"
            class="group relative overflow-hidden rounded-lg border border-secondary-200"
          >
            <img :src="activeBgUrl" class="aspect-[5/3] w-full object-cover" alt="" />
            <div class="absolute inset-x-0 bottom-0 flex gap-1 bg-gradient-to-t from-black/60 to-transparent p-2">
              <button
                type="button"
                class="admin-btn admin-btn--outline admin-btn--sm flex-1 !bg-white"
                @click="bgInput?.click()"
              >
                Replace
              </button>
              <button
                type="button"
                class="admin-btn admin-btn--outline admin-btn--sm admin-btn--icon !border-error-300 !bg-white !text-error-600"
                title="Remove background"
                @click="removeBackground"
              >
                <Icon name="heroicons:trash" class="h-4 w-4" />
              </button>
            </div>
          </div>
          <div
            v-else
            class="cursor-pointer rounded-lg border-2 border-dashed border-secondary-300 p-4 text-center transition-colors hover:border-accent-400"
            @click="bgInput?.click()"
          >
            <Icon name="heroicons:photo" class="mx-auto h-6 w-6 text-secondary-400" />
            <p class="mt-1 text-helper">
              {{ uploadingBg ? 'Uploading…' : 'Upload JPG, PNG or WEBP' }}
            </p>
          </div>
          <input ref="bgInput" type="file" accept="image/*" class="hidden" @change="onBgFile" />
        </AdminSectionCard>

        <AdminSectionCard title="Properties">
          <template #header-actions>
            <button
              v-if="selected"
              type="button"
              class="admin-icon-btn !text-error-500"
              title="Delete element"
              @click="deleteSelected"
            >
              <Icon name="heroicons:trash" class="h-4 w-4" />
            </button>
          </template>

          <p v-if="!selected" class="text-body text-secondary-500">
            Select an element on the card to edit its properties.
          </p>

          <div v-else class="space-y-4">
            <!-- geometry -->
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="admin-label">X (mm)</label>
                <input v-model.number="selected.x" type="number" step="0.5" class="admin-field" />
              </div>
              <div>
                <label class="admin-label">Y (mm)</label>
                <input v-model.number="selected.y" type="number" step="0.5" class="admin-field" />
              </div>
              <div>
                <label class="admin-label">Width (mm)</label>
                <input v-model.number="selected.w" type="number" step="0.5" min="2" class="admin-field" />
              </div>
              <div>
                <label class="admin-label">Height (mm)</label>
                <input v-model.number="selected.h" type="number" step="0.5" min="2" class="admin-field" />
              </div>
              <div class="col-span-2">
                <label class="admin-label">Rotation (°)</label>
                <input
                  v-model.number="selected.rotation"
                  type="range"
                  min="-45"
                  max="45"
                  step="1"
                  class="admin-range w-full"
                />
                <span class="text-caption text-secondary-500">{{ selected.rotation }}°</span>
              </div>
            </div>

            <hr class="border-secondary-200" />

            <!-- text -->
            <template v-if="selected.type === 'text'">
              <div>
                <label class="admin-label">Data field</label>
                <select
                  :value="selected.field || ''"
                  class="admin-field"
                  @change="setElementField(selected, $event.target.value)"
                >
                  <option v-for="f in TEXT_FIELDS" :key="f.value" :value="f.value">{{ f.label }}</option>
                </select>
                <p class="mt-1 text-caption text-secondary-500">
                  {{ selected.field
                    ? "Filled with the customer's own details. Left out of the template image."
                    : 'Fixed text, printed the same on every card.' }}
                </p>
              </div>
              <div>
                <label class="admin-label">{{ selected.field ? 'Sample text (designer only)' : 'Text' }}</label>
                <textarea v-model="selected.text" rows="2" class="admin-field"></textarea>
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="admin-label">Font</label>
                  <select v-model="selected.fontFamily" class="admin-field">
                    <option value="helvetica">Helvetica</option>
                    <option value="times">Times</option>
                    <option value="courier">Courier</option>
                  </select>
                </div>
                <div>
                  <label class="admin-label">Size (pt)</label>
                  <input v-model.number="selected.fontSize" type="number" min="4" max="72" class="admin-field" />
                </div>
                <div>
                  <label class="admin-label">Color</label>
                  <input v-model="selected.color" type="color" class="admin-field !p-1" />
                </div>
                <div>
                  <label class="admin-label">Weight</label>
                  <select v-model="selected.fontWeight" class="admin-field">
                    <option value="normal">Normal</option>
                    <option value="bold">Bold</option>
                  </select>
                </div>
                <div class="col-span-2">
                  <label class="admin-label">Align</label>
                  <select v-model="selected.align" class="admin-field">
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
                <label class="admin-label">Data field</label>
                <select
                  :value="selected.field || ''"
                  class="admin-field"
                  @change="setElementField(selected, $event.target.value)"
                >
                  <option v-for="f in IMAGE_FIELDS" :key="f.value" :value="f.value">{{ f.label }}</option>
                </select>
              </div>
              <div v-if="!selected.field">
                <label class="admin-label">Image</label>
                <button type="button" class="admin-btn admin-btn--outline admin-btn--sm w-full" @click="imgInput?.click()">
                  <Icon name="heroicons:arrow-up-tray" class="h-4 w-4" />
                  Upload image
                </button>
                <input ref="imgInput" type="file" accept="image/*" class="hidden" @change="onElementImage" />
              </div>
              <div>
                <label class="admin-label">Fit</label>
                <select v-model="selected.fit" class="admin-field">
                  <option value="contain">Contain</option>
                  <option value="cover">Cover</option>
                </select>
              </div>
              <div>
                <label class="admin-label">Opacity ({{ selected.opacity }})</label>
                <input
                  v-model.number="selected.opacity"
                  type="range"
                  min="0.1"
                  max="1"
                  step="0.1"
                  class="admin-range w-full"
                />
              </div>
            </template>

            <!-- qr -->
            <template v-else-if="selected.type === 'qr'">
              <div>
                <label class="admin-label">QR content (URL / text)</label>
                <input v-model="selected.value" type="text" class="admin-field" placeholder="https://..." />
              </div>
            </template>

            <!-- rect -->
            <template v-else-if="selected.type === 'rect'">
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="admin-label">Fill</label>
                  <input v-model="selected.fill" type="color" class="admin-field !p-1" />
                </div>
                <div>
                  <label class="admin-label">Radius (mm)</label>
                  <input v-model.number="selected.borderRadius" type="number" min="0" step="0.5" class="admin-field" />
                </div>
                <div>
                  <label class="admin-label">Border</label>
                  <input v-model.number="selected.borderWidth" type="number" min="0" step="0.1" class="admin-field" />
                </div>
                <div>
                  <label class="admin-label">Border color</label>
                  <input v-model="selected.borderColor" type="color" class="admin-field !p-1" />
                </div>
              </div>
            </template>

            <hr class="border-secondary-200" />
            <div class="flex items-center justify-between gap-2">
              <button type="button" class="admin-btn admin-btn--outline admin-btn--sm" @click="duplicateSelected">
                <Icon name="heroicons:document-duplicate" class="h-4 w-4" />
                Duplicate
              </button>
              <div class="flex gap-1">
                <button
                  type="button"
                  class="admin-btn admin-btn--outline admin-btn--sm admin-btn--icon"
                  title="Backward"
                  @click="moveLayer(-1)"
                >
                  <Icon name="heroicons:chevron-down" class="h-4 w-4" />
                </button>
                <button
                  type="button"
                  class="admin-btn admin-btn--outline admin-btn--sm admin-btn--icon"
                  title="Forward"
                  @click="moveLayer(1)"
                >
                  <Icon name="heroicons:chevron-up" class="h-4 w-4" />
                </button>
              </div>
            </div>
          </div>
        </AdminSectionCard>

        <AdminSectionCard title="Internal notes">
          <label class="admin-label">Notes</label>
          <textarea
            v-model="design.notes"
            rows="3"
            class="admin-field"
            placeholder="Internal notes (paper, finish, etc.)"
          ></textarea>
        </AdminSectionCard>
      </div>
    </div>

    <!-- Design library -->
    <div class="mb-4 mt-10 flex flex-wrap items-end justify-between gap-3 border-t border-secondary-200 pt-8">
      <div>
        <h2 class="section-title">Design library</h2>
        <p class="mt-1 text-body-sm text-secondary-500">Open a saved design to keep editing it.</p>
      </div>
      <button type="button" class="admin-btn admin-btn--primary admin-btn--sm" @click="newDesign">
        <Icon name="heroicons:plus" class="h-4 w-4" />
        New design
      </button>
    </div>

    <AdminFilterBar @submit="applyFilters">
      <template #search>
        <label class="admin-label">Search</label>
        <div class="relative">
          <Icon
            name="heroicons:magnifying-glass"
            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-secondary-400"
          />
          <input
            v-model="filters.search"
            type="text"
            class="admin-field !pl-9"
            placeholder="Search designs by name or notes…"
            maxlength="150"
            @input="onSearchInput"
          />
        </div>
      </template>

      <div class="min-w-[10rem]">
        <label class="admin-label">Status</label>
        <select v-model="filters.status" class="admin-field" @change="applyFilters">
          <option value="">All statuses</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>

      <div class="min-w-[12rem]">
        <label class="admin-label">Sort by</label>
        <div class="flex gap-2">
          <select v-model="filters.sort_by" class="admin-field" @change="applyFilters">
            <option value="updated_at">Last updated</option>
            <option value="created_at">Date created</option>
            <option value="name">Name</option>
          </select>
          <button
            type="button"
            class="admin-btn admin-btn--outline admin-btn--icon shrink-0"
            :title="`Sort ${filters.sort_order === 'asc' ? 'ascending' : 'descending'}`"
            @click="toggleSortOrder"
          >
            <Icon
              :name="filters.sort_order === 'asc' ? 'heroicons:arrow-small-up' : 'heroicons:arrow-small-down'"
              class="h-4 w-4"
            />
          </button>
        </div>
      </div>

      <template #actions>
        <span class="w-full text-right text-body-sm text-secondary-500 sm:w-auto">
          {{ pagination.total }} design{{ pagination.total === 1 ? '' : 's' }}
        </span>
        <button
          type="button"
          class="admin-btn admin-btn--outline admin-btn--sm"
          :disabled="!hasActiveFilters"
          @click="clearFilters"
        >
          <Icon name="heroicons:x-mark" class="h-4 w-4" />
          Clear
        </button>
      </template>
    </AdminFilterBar>

    <AdminSectionCard flush>
      <template #title>Saved designs</template>
      <template #header-actions>
        <button
          type="button"
          class="admin-icon-btn"
          title="Refresh"
          @click="loadDesigns()"
        >
          <Icon name="heroicons:arrow-path" :class="['h-4 w-4', loadingDesigns && 'animate-spin']" />
        </button>
      </template>

      <AdminLoadingState v-if="loadingDesigns" variant="table" :rows="5" :columns="6" />

      <AdminEmptyState
        v-else-if="!designs.length"
        icon="heroicons:document-duplicate"
        :title="hasActiveFilters ? 'No matching designs' : 'No saved designs yet'"
        :description="
          hasActiveFilters
            ? 'Try adjusting the search or filters above.'
            : 'Designs you save will appear in this library.'
        "
      />

      <div v-else class="admin-scroll">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Design</th>
              <th>Size</th>
              <th>Content</th>
              <th>Status</th>
              <th>Updated</th>
              <th class="admin-col-actions">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="d in designs"
              :key="d.id"
              :class="design.id === d.id ? 'bg-accent-50/70' : ''"
            >
              <td>
                <div class="flex items-center gap-3">
                  <span
                    class="inline-flex h-9 w-14 shrink-0 items-center justify-center overflow-hidden rounded-md border border-secondary-200 bg-secondary-100"
                  >
                    <img
                      v-if="d.background_front_url"
                      :src="d.background_front_url"
                      loading="lazy"
                      alt=""
                      class="h-full w-full object-cover"
                    />
                    <Icon v-else name="heroicons:photo" class="h-4 w-4 text-secondary-400" />
                  </span>
                  <div class="min-w-0">
                    <div class="flex items-center gap-2">
                      <span class="truncate text-body font-medium text-secondary-900">{{ d.name }}</span>
                      <AdminStatusBadge
                        v-if="design.id === d.id"
                        status="editing"
                        label="Editing"
                        tone="brass"
                      />
                    </div>
                    <div class="truncate text-caption text-secondary-500">
                      {{ d.notes || creatorName(d.creator) }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="whitespace-nowrap">
                <span class="text-body text-secondary-700">{{ sizeLabel(d.card_size) }}</span>
                <span v-if="d.bleed" class="admin-badge admin-badge--neutral admin-badge--plain ml-1.5">
                  Bleed
                </span>
              </td>
              <td>
                <div class="flex flex-wrap items-center gap-1.5">
                  <span class="admin-badge admin-badge--neutral admin-badge--plain">
                    {{ (d.front_elements?.length || 0) }} front
                  </span>
                  <span class="admin-badge admin-badge--neutral admin-badge--plain">
                    {{ (d.back_elements?.length || 0) }} back
                  </span>
                </div>
              </td>
              <td>
                <AdminStatusBadge
                  :status="d.is_active === false ? 'inactive' : 'active'"
                  :label="d.is_active === false ? 'Inactive' : 'Active'"
                />
              </td>
              <td class="whitespace-nowrap text-body text-secondary-600">
                {{ formatDate(d.updated_at) }}
              </td>
              <td class="admin-col-actions">
                <div class="flex items-center justify-end gap-1.5">
                  <button
                    type="button"
                    class="admin-btn admin-btn--outline admin-btn--sm"
                    @click="openDesign(d.id)"
                  >
                    <Icon name="heroicons:pencil-square" class="h-4 w-4" />
                    Open
                  </button>
                  <button
                    type="button"
                    class="admin-icon-btn !text-error-500"
                    title="Delete design"
                    @click="deleteDesign(d)"
                  >
                    <Icon name="heroicons:trash" class="h-4 w-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </AdminSectionCard>

    <AdminPagination
      v-if="!loadingDesigns && designs.length"
      :current-page="pagination.current_page"
      :last-page="pagination.last_page"
      :per-page="pagination.per_page"
      :total="pagination.total"
      item-label="designs"
      @page-change="changePage"
      @per-page-change="changePerPage"
    />

    <!-- Save as Template dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="showTemplateModal"
          class="admin-dialog-backdrop"
          @click.self="closeTemplateModal"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            aria-label="Save as template"
            style="width: min(38rem, 100%); margin-top: 6vh; max-height: 90vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
                >
                  <Icon name="heroicons:rectangle-stack" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">Save as template</p>
                  <p class="admin-panel-subtitle">
                    Render the current design into a pickable card template.
                  </p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="closeTemplateModal"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <form class="flex min-h-0 flex-1 flex-col" @submit.prevent="promoteToTemplate">
              <div class="admin-panel-body flex-1 space-y-6 overflow-y-auto">
                <div class="admin-form-section">
                  <p class="admin-form-section-title">Template details</p>
                  <div class="mt-4 space-y-4">
                    <div>
                      <label class="admin-label">
                        Template name <span class="text-error-600">*</span>
                      </label>
                      <input
                        v-model="templateForm.name"
                        type="text"
                        class="admin-field"
                        maxlength="80"
                        placeholder="e.g. Emerald Executive"
                      />
                    </div>
                    <div>
                      <label class="admin-label">Description</label>
                      <textarea
                        v-model="templateForm.description"
                        rows="2"
                        class="admin-field resize-none"
                        maxlength="200"
                        placeholder="Short description about this template"
                      ></textarea>
                    </div>
                    <div>
                      <label class="admin-label">Category</label>
                      <select v-model="templateForm.category" class="admin-field">
                        <option value="business">Business</option>
                        <option value="personal">Personal</option>
                        <option value="creative">Creative</option>
                      </select>
                    </div>
                    <div>
                      <label class="admin-label">
                        Available plans <span class="text-error-600">*</span>
                      </label>
                      <div
                        class="flex flex-wrap gap-4 rounded-lg border border-secondary-200 bg-secondary-50 p-3"
                      >
                        <label
                          v-for="p in templatePlanOptions"
                          :key="p.value"
                          class="flex cursor-pointer items-center gap-2 text-body text-secondary-600"
                        >
                          <input
                            type="checkbox"
                            class="h-4 w-4 rounded border-secondary-300 text-accent-600 focus:ring-accent-500"
                            :value="p.value"
                            v-model="templateForm.planTypes"
                          />
                          {{ p.label }}
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                <div
                  class="flex items-center gap-2 rounded-lg border border-secondary-200 bg-secondary-50 px-3 py-2"
                >
                  <Icon
                    name="heroicons:information-circle"
                    class="h-4 w-4 shrink-0 text-secondary-400"
                  />
                  <span class="text-helper">
                    Front and back sides are rendered at {{ EXPORT_PX_PER_MM }}px/mm and saved as a
                    new template.
                  </span>
                </div>
              </div>

              <div class="admin-dialog-foot">
                <button
                  type="button"
                  class="admin-btn admin-btn--ghost"
                  @click="closeTemplateModal"
                  :disabled="promoting"
                >
                  Cancel
                </button>
                <button type="submit" class="admin-btn admin-btn--primary" :disabled="promoting">
                  <Icon
                    v-if="promoting"
                    name="heroicons:arrow-path"
                    class="h-4 w-4 animate-spin"
                  />
                  <Icon v-else name="heroicons:check" class="h-4 w-4" />
                  {{ promoting ? 'Creating…' : 'Create template' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Shared confirmation dialog -->
    <AdminConfirmDialog
      v-model="confirmOpen"
      :title="confirmConfig.title"
      :message="confirmConfig.message"
      :confirm-label="confirmConfig.confirmLabel"
      :tone="confirmConfig.tone"
      :loading="confirmBusy"
      @confirm="runConfirm"
    />
  </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, toRaw } from 'vue';
import { SAMPLE_CARD_DATA } from '~/utils/cardSampleData';

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

// Customer data an element can be bound to. Bound elements are left out of the
// template image and drawn live from the customer's details (see CardExamples.vue).
const TEXT_FIELDS = [
  { value: '', label: 'None (fixed text)' },
  { value: 'name', label: 'Full name', sample: SAMPLE_CARD_DATA.name },
  { value: 'position', label: 'Job title', sample: SAMPLE_CARD_DATA.position },
  { value: 'company', label: 'Company name', sample: SAMPLE_CARD_DATA.company },
  { value: 'phone', label: 'Phone number', sample: SAMPLE_CARD_DATA.phone },
  { value: 'email', label: 'Email', sample: SAMPLE_CARD_DATA.email },
  { value: 'website', label: 'Website', sample: SAMPLE_CARD_DATA.website },
  { value: 'address', label: 'Address', sample: SAMPLE_CARD_DATA.address },
];
const IMAGE_FIELDS = [
  { value: '', label: 'None (fixed image)' },
  { value: 'logo', label: 'Company logo' },
];
const fieldLabel = (value) =>
  [...TEXT_FIELDS, ...IMAGE_FIELDS].find((f) => f.value === value)?.label || value;

// One-click data elements for the "Customer data" palette, with sensible
// default size and type per field.
const DATA_PALETTE = [
  { field: 'name', label: 'Full name', icon: 'heroicons:user', w: 45, h: 7, fontSize: 13, fontWeight: 'bold' },
  { field: 'position', label: 'Job title', icon: 'heroicons:briefcase', w: 45, h: 5, fontSize: 8 },
  { field: 'company', label: 'Company name', icon: 'heroicons:building-office', w: 45, h: 5, fontSize: 9, fontWeight: 'bold' },
  { field: 'phone', label: 'Phone number', icon: 'heroicons:phone', w: 40, h: 4.5, fontSize: 7.5 },
  { field: 'email', label: 'Email', icon: 'heroicons:envelope', w: 45, h: 4.5, fontSize: 7.5 },
  { field: 'website', label: 'Website', icon: 'heroicons:globe-alt', w: 40, h: 4.5, fontSize: 7.5 },
  { field: 'address', label: 'Address', icon: 'heroicons:map-pin', w: 50, h: 8, fontSize: 7 },
  { field: 'logo', label: 'Company logo', icon: 'heroicons:photo', image: true },
];

const fieldOnSide = (field) => elements.value.some((el) => el.field === field);

function makeDataElement(field, x, y) {
  const spec = DATA_PALETTE.find((f) => f.field === field);
  if (spec?.image) {
    return { ...makeElement('image', x, y), w: 16, h: 16, field, fit: 'contain' };
  }
  const el = makeElement('text', x, y);
  return {
    ...el,
    field,
    w: spec?.w ?? el.w,
    h: spec?.h ?? el.h,
    fontSize: spec?.fontSize ?? el.fontSize,
    fontWeight: spec?.fontWeight ?? 'normal',
    text: TEXT_FIELDS.find((f) => f.value === field)?.sample || el.text,
  };
}

function addDataElement(field) {
  const spec = DATA_PALETTE.find((f) => f.field === field);
  const w = spec?.image ? 16 : spec?.w ?? 40;
  const h = spec?.image ? 16 : spec?.h ?? 8;
  pushElement(makeDataElement(field, round((cardSize.value.w - w) / 2), round((cardSize.value.h - h) / 2)));
}

function setElementField(el, value) {
  const prevSample = TEXT_FIELDS.find((f) => f.value === el.field)?.sample;
  el.field = value || undefined;
  if (el.type !== 'text') return;
  const sample = TEXT_FIELDS.find((f) => f.value === value)?.sample;
  // Swap in a sample only if the admin hasn't typed their own text.
  if (sample && (!el.text || el.text === 'Your Name' || el.text === prevSample)) el.text = sample;
}

const palette = [
  { type: 'text', label: 'Text', icon: 'heroicons:language' },
  { type: 'image', label: 'Image', icon: 'heroicons:photo' },
  { type: 'qr', label: 'QR Code', icon: 'heroicons:qr-code' },
  { type: 'rect', label: 'Shape', icon: 'heroicons:square-2-stack' },
  { type: 'line', label: 'Line', icon: 'heroicons:minus' },
  { type: 'circle', label: 'Circle', icon: 'heroicons:stop-circle' },
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

// Design library filters & pagination
const filters = reactive({
  search: '',
  status: '',
  sort_by: 'updated_at',
  sort_order: 'desc',
});
const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
});
const hasActiveFilters = computed(() => !!(filters.search || filters.status));
let searchTimer = null;

// Confirmation dialog state
const confirmOpen = ref(false);
const confirmBusy = ref(false);
const confirmConfig = ref({
  title: '',
  message: '',
  confirmLabel: 'Delete',
  tone: 'danger',
});
let confirmAction = null;

const askConfirm = (config, action) => {
  confirmConfig.value = { confirmLabel: 'Confirm', tone: 'primary', ...config };
  confirmAction = action;
  confirmOpen.value = true;
};

const runConfirm = async () => {
  if (!confirmAction) return;
  confirmBusy.value = true;
  try {
    await confirmAction();
  } finally {
    confirmBusy.value = false;
    confirmOpen.value = false;
    confirmAction = null;
  }
};

// "Save as Template" state
const showTemplateModal = ref(false);
const promoting = ref(false);
const templatePlanOptions = [
  { value: 'basic', label: 'Basic' },
  { value: 'premium', label: 'Premium' },
  { value: 'business', label: 'Business' },
];
const templateForm = reactive({
  name: '',
  description: '',
  category: 'business',
  planTypes: ['business'],
});

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

// ── Library formatting helpers ──────────────────────────────
function sizeLabel(size) {
  const label = cardSizes[size]?.label;
  if (!label) return size || '—';
  return label.split(' (')[0];
}

function formatDate(iso) {
  if (!iso) return '—';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '—';
  return d.toLocaleDateString(undefined, { day: '2-digit', month: 'short', year: 'numeric' });
}

function creatorName(creator) {
  if (!creator) return '—';
  const name = [creator.first_name, creator.last_name].filter(Boolean).join(' ').trim();
  return name || creator.email || '—';
}

const qrUrl = (el) =>
  `${QR_ENDPOINT}?size=300x300&margin=0&data=${encodeURIComponent(el.value || '')}`;

function elementStyle(el) {
  return {
    left: el.x * zoom.value + 'px',
    top: el.y * zoom.value + 'px',
    width: Math.max(el.w * zoom.value, el.shape === 'line' ? 2 : 6) + 'px',
    height: Math.max(el.h * zoom.value, el.shape === 'line' ? 2 : 6) + 'px',
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
  // Line and circle are rect presets, so they render, export and print like shapes.
  if (type === 'line')
    return { ...base, type: 'rect', shape: 'line', w: 40, h: 0.4, fill: '#c2a068', borderRadius: 0, borderWidth: 0, borderColor: '#000000', opacity: 1 };
  if (type === 'circle')
    return { ...base, type: 'rect', shape: 'circle', w: 14, h: 14, fill: '#c2a068', borderRadius: 7, borderWidth: 0, borderColor: '#000000', opacity: 1 };
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
    el.w = round(clamp(orig.w + dx, el.shape === 'line' ? 1 : 3, cw - el.x));
    el.h = round(clamp(orig.h + dy, el.shape === 'line' ? 0.2 : 3, ch - el.y));
    // A circle stays round while it is resized.
    if (el.shape === 'circle') {
      el.h = el.w = round(Math.min(el.w, ch - el.y));
      el.borderRadius = round(el.w / 2);
    }
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
  if (type.startsWith('data:')) {
    pushElement(makeDataElement(type.slice(5), x, y));
    return;
  }
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
async function loadDesigns(page = 1) {
  loadingDesigns.value = true;
  try {
    const res = await $api.get('/admin/card-designs', {
      params: {
        page,
        per_page: pagination.per_page,
        search: filters.search || undefined,
        status: filters.status || undefined,
        sort_by: filters.sort_by,
        sort_order: filters.sort_order,
      },
    });
    if (res?.success) {
      const payload = res.data || {};
      designs.value = payload.data || [];
      pagination.current_page = payload.current_page ?? 1;
      pagination.last_page = payload.last_page ?? 1;
      pagination.per_page = payload.per_page ?? pagination.per_page;
      pagination.total = payload.total ?? designs.value.length;
    }
  } catch (err) {
    console.error(err);
  } finally {
    loadingDesigns.value = false;
  }
}

// ── Library filters & pagination ────────────────────────────
function applyFilters() {
  pagination.current_page = 1;
  loadDesigns(1);
}

function onSearchInput() {
  if (searchTimer) clearTimeout(searchTimer);
  searchTimer = setTimeout(() => applyFilters(), 400);
}

function toggleSortOrder() {
  filters.sort_order = filters.sort_order === 'asc' ? 'desc' : 'asc';
  applyFilters();
}

function clearFilters() {
  filters.search = '';
  filters.status = '';
  filters.sort_by = 'updated_at';
  filters.sort_order = 'desc';
  applyFilters();
}

function changePage(page) {
  const target = Math.min(Math.max(1, page), pagination.last_page || 1);
  if (target === pagination.current_page) return;
  loadDesigns(target);
}

function changePerPage(perPage) {
  pagination.per_page = perPage;
  pagination.current_page = 1;
  loadDesigns(1);
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

function deleteDesign(d) {
  askConfirm(
    {
      title: 'Delete design',
      message: `Delete design "${d.name}"? This action cannot be undone.`,
      confirmLabel: 'Delete',
      tone: 'danger',
    },
    async () => {
      try {
        await $api.delete(`/admin/card-designs/${d.id}`);
        if (design.id === d.id) newDesign();
        const back = pagination.current_page > 1 && designs.value.length === 1;
        await loadDesigns(back ? pagination.current_page - 1 : pagination.current_page);
        $toast.success('Design deleted');
      } catch (err) {
        console.error(err);
        $toast.error('Failed to delete design');
      }
    }
  );
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
    // Storage images are served directly by the web server, so they carry no
    // CORS headers — the browser blocks canvas pixel access from another origin.
    // Fetch the bytes through the CORS-enabled API instead.
    const blob = await $api.get('/admin/card-designs/background-media', {
      params: { url },
      responseType: 'blob',
    });
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

// ── Save as Template ────────────────────────────────────────
const FONT_FAMILY_MAP = {
  helvetica: 'Helvetica, Arial, sans-serif',
  times: '"Times New Roman", Times, serif',
  courier: '"Courier New", Courier, monospace',
};

function openTemplateModal() {
  if (!design.name.trim()) {
    $toast.error('Please enter a design name first');
    return;
  }
  templateForm.name = design.name.trim();
  templateForm.description = '';
  templateForm.category = 'business';
  templateForm.planTypes = ['business'];
  showTemplateModal.value = true;
}

function closeTemplateModal() {
  if (promoting.value) return;
  showTemplateModal.value = false;
}

function dataURLtoBlob(dataURL) {
  const [meta, b64] = dataURL.split(',');
  const mime = (meta.match(/:(.*?);/) || [])[1] || 'image/png';
  const bin = atob(b64);
  const arr = new Uint8Array(bin.length);
  for (let i = 0; i < bin.length; i++) arr[i] = bin.charCodeAt(i);
  return new Blob([arr], { type: mime });
}

function sideHasContent(side) {
  return (design[`${side}_elements`] || []).length > 0 || !!design[`background_${side}_url`];
}

// Draw a single element onto the target canvas at the given mm→px scale.
async function drawElementOnCanvas(ctx, el, bleedMm, pxPerMm) {
  if (el.type === 'text') {
    const fontPx = (el.fontSize || 10) * PT_TO_MM * pxPerMm;
    ctx.save();
    ctx.translate((el.x + bleedMm) * pxPerMm, (el.y + bleedMm) * pxPerMm);
    if (el.rotation) ctx.rotate((el.rotation * Math.PI) / 180);
    ctx.fillStyle = el.color || '#000000';
    ctx.font = `${el.fontWeight === 'bold' ? 'bold' : 'normal'} ${fontPx}px ${FONT_FAMILY_MAP[el.fontFamily] || 'sans-serif'}`;
    ctx.textAlign = el.align || 'left';
    ctx.textBaseline = 'alphabetic';
    const lineH = fontPx * (el.lineHeight || 1.2);
    const boxW = el.w * pxPerMm;
    const tx = el.align === 'center' ? boxW / 2 : el.align === 'right' ? boxW : 0;
    String(el.text || '').split('\n').forEach((line, i) => {
      ctx.fillText(line, tx, fontPx * 0.82 + i * lineH);
    });
    ctx.restore();
    return;
  }

  if (!['image', 'rect', 'qr'].includes(el.type)) return;

  try {
    const { dataURL } = await renderShape(el, pxPerMm);
    const img = await loadImage(dataURL);
    const cx = (el.x + bleedMm + el.w / 2) * pxPerMm;
    const cy = (el.y + bleedMm + el.h / 2) * pxPerMm;
    ctx.drawImage(img, cx - img.width / 2, cy - img.height / 2, img.width, img.height);
  } catch (err) {
    console.warn(`Template render skipped ${el.type} element`, err);
  }
}

// Render one side into a PNG data URL, trimmed to the card size (no bleed) so
// it lines up with the trim-relative layout coordinates. Data-bound elements
// are skipped: the preview draws them from the customer's own details.
async function renderSideToDataURL(side) {
  const pxPerMm = EXPORT_PX_PER_MM;
  const b = design.bleed ? BLEED_MM : 0;
  const pw = cardSize.value.w + b * 2;
  const ph = cardSize.value.h + b * 2;

  const canvas = document.createElement('canvas');
  canvas.width = Math.round(cardSize.value.w * pxPerMm);
  canvas.height = Math.round(cardSize.value.h * pxPerMm);
  const ctx = canvas.getContext('2d');
  ctx.fillStyle = '#ffffff';
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  const bgUrl = design[`background_${side}_url`];
  if (bgUrl) {
    try {
      const bgImg = await loadImage(await urlToDataURL(bgUrl));
      // Cover the full bleed page, then shift so the trim area lands at 0,0.
      drawCover(ctx, bgImg, -b * pxPerMm, -b * pxPerMm, pw * pxPerMm, ph * pxPerMm);
    } catch (err) {
      console.warn('Template background render skipped', err);
    }
  }

  for (const el of design[`${side}_elements`] || []) {
    if (el.field) continue;
    await drawElementOnCanvas(ctx, el, 0, pxPerMm);
  }

  return canvas.toDataURL('image/png');
}

// Data-bound elements of one side, positioned in mm relative to the trim edge.
function boundLayout(side) {
  return (design[`${side}_elements`] || [])
    .filter((el) => el.field)
    .map((el) => {
      const base = { field: el.field, type: el.type, x: el.x, y: el.y, w: el.w, h: el.h, rotation: el.rotation || 0 };
      if (el.type === 'text') {
        return {
          ...base,
          fontFamily: el.fontFamily,
          fontSize: el.fontSize,
          fontWeight: el.fontWeight,
          color: el.color,
          align: el.align,
          lineHeight: el.lineHeight || 1.2,
        };
      }
      return { ...base, fit: el.fit || 'contain', opacity: el.opacity ?? 1 };
    });
}

async function promoteToTemplate() {
  if (!templateForm.name.trim()) {
    $toast.error('Please enter a template name');
    return;
  }
  if (!templateForm.planTypes.length) {
    $toast.error('Select at least one plan');
    return;
  }

  promoting.value = true;
  try {
    const frontData = await renderSideToDataURL('front');
    const hasBack = sideHasContent('back');

    const fd = new FormData();
    fd.append('name', templateForm.name.trim());
    fd.append('description', templateForm.description.trim());
    fd.append('category', templateForm.category);
    templateForm.planTypes.forEach((p) => fd.append('plan_types[]', p));
    fd.append('front_image', dataURLtoBlob(frontData), 'front.png');
    if (hasBack) {
      const backData = await renderSideToDataURL('back');
      fd.append('back_image', dataURLtoBlob(backData), 'back.png');
    }
    fd.append('card_size', design.card_size);
    fd.append('front_layout', JSON.stringify(boundLayout('front')));
    fd.append('back_layout', JSON.stringify(boundLayout('back')));

    const res = await $api.post('/admin/card-templates', fd);
    if (res?.success) {
      $toast.success(res.message || 'Template created');
      showTemplateModal.value = false;
    } else {
      $toast.error(res?.message || 'Failed to create template');
    }
  } catch (err) {
    console.error(err);
    $toast.error(err?.data?.message || 'Failed to create template');
  } finally {
    promoting.value = false;
  }
}

// ── Side switching helper ───────────────────────────────────────
function switchSide(side) {
  if (activeSide.value === side) return;
  activeSide.value = side;
  selectedId.value = null;
}

// ── Keyboard shortcuts ─────────────────────────────────
function onKeyDown(e) {
  // Ignore when typing in an input / textarea / select
  const tag = e.target?.tagName;
  if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' || e.target?.isContentEditable) return;

  if (e.key === 'Delete' || e.key === 'Backspace') {
    if (selected.value) {
      e.preventDefault();
      deleteSelected();
    }
  } else if (e.key === 'Escape') {
    selectedId.value = null;
  }
}

// ── Lifecycle ───────────────────────────────────────────
// ── Auto-fit zoom: size the card to the canvas column ─────────────
const stageEl = ref(null);
const autoFit = ref(true);
let stageObserver = null;

function fitZoom() {
  const el = stageEl.value;
  if (!el) return;
  const avail = el.clientWidth - 48; // p-6 on both sides
  const z = Math.floor((avail / pageW.value) * 2) / 2; // 0.5 steps
  zoom.value = clamp(z, 3, 12);
}

watch(pageW, () => autoFit.value && fitZoom());

onMounted(() => {
  loadDesigns();
  window.addEventListener('keydown', onKeyDown);
  if (typeof ResizeObserver !== 'undefined' && stageEl.value) {
    stageObserver = new ResizeObserver(() => autoFit.value && fitZoom());
    stageObserver.observe(stageEl.value);
  }
  fitZoom();
});

onBeforeUnmount(() => {
  stageObserver?.disconnect();
  window.removeEventListener('keydown', onKeyDown);
  // Clean up drag listeners in case a drag is in progress
  window.removeEventListener('pointermove', onDragMove);
  window.removeEventListener('pointerup', endDrag);
  if (searchTimer) clearTimeout(searchTimer);
});
</script>
