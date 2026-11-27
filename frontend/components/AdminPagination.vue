<template>
  <div class="mt-6 flex flex-col items-center justify-between gap-4 sm:flex-row">
    <!-- Results counter -->
    <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
      <span class="text-body text-secondary-700">
        Showing
        <span class="font-semibold text-secondary-900">
          {{ total === 0 ? 0 : (currentPage - 1) * perPage + 1 }}
        </span>
        –
        <span class="font-semibold text-secondary-900">
          {{ Math.min(currentPage * perPage, total) }}
        </span>
        of
        <span class="font-semibold text-secondary-900">{{ total }}</span>
        {{ itemLabel }}
      </span>
      <!-- Per page selector -->
      <div class="flex items-center gap-2">
        <label class="text-body text-secondary-600" :for="perPageId">Per page:</label>
        <select
          :id="perPageId"
          :value="perPage"
          @change="$emit('per-page-change', Number($event.target.value))"
          class="admin-field !h-9 !w-20 !py-0 text-center"
        >
          <option v-for="n in perPageOptions" :key="n" :value="n">
            {{ n }}
          </option>
        </select>
      </div>
    </div>

    <!-- Page navigation -->
    <nav class="flex items-center gap-1.5" aria-label="Pagination">
      <button
        type="button"
        class="admin-btn admin-btn--outline admin-btn--sm"
        :disabled="currentPage === 1"
        @click="$emit('page-change', currentPage - 1)"
      >
        <Icon name="heroicons:chevron-left" class="h-4 w-4" />
        Previous
      </button>

      <template v-for="page in visiblePages" :key="page">
        <span v-if="page === '...'" class="px-1 text-body text-secondary-400">…</span>
        <button
          v-else
          type="button"
          class="admin-btn admin-btn--sm"
          :class="page === currentPage ? 'admin-btn--ink' : 'admin-btn--ghost'"
          :aria-current="page === currentPage ? 'page' : undefined"
          @click="$emit('page-change', page)"
        >
          {{ page }}
        </button>
      </template>

      <button
        type="button"
        class="admin-btn admin-btn--outline admin-btn--sm"
        :disabled="currentPage === lastPage"
        @click="$emit('page-change', currentPage + 1)"
      >
        Next
        <Icon name="heroicons:chevron-right" class="h-4 w-4" />
      </button>
    </nav>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  currentPage: {
    type: Number,
    required: true
  },
  lastPage: {
    type: Number,
    required: true
  },
  perPage: {
    type: Number,
    default: 10
  },
  total: {
    type: Number,
    required: true
  },
  itemLabel: {
    type: String,
    default: 'results'
  }
});

defineEmits(['page-change', 'per-page-change']);

// Stable unique id for the per-page select label association
const perPageId = `per-page-${Math.random().toString(36).slice(2, 9)}`;

// Presets always include the active value so the select is never blank
// (e.g. backend defaults such as 15 or 20 that aren't in the preset list).
const perPageOptions = computed(() => {
  const presets = [10, 25, 50, 100];
  const current = Number(props.perPage);
  if (!Number.isFinite(current) || presets.includes(current)) {
    return presets;
  }
  return [...presets, current].sort((a, b) => a - b);
});

const visiblePages = computed(() => {
  const current = props.currentPage;
  const last = props.lastPage;

  if (last <= 7) {
    return Array.from({ length: last }, (_, i) => i + 1);
  }

  const pages = [];

  // Always show first page
  pages.push(1);

  // Calculate range around current page
  let start = Math.max(2, current - 1);
  let end = Math.min(last - 1, current + 1);

  // Adjust if at the beginning
  if (current <= 3) {
    end = Math.min(5, last - 1);
  }

  // Adjust if at the end
  if (current >= last - 2) {
    start = Math.max(2, last - 4);
  }

  // Add ellipsis if needed before range
  if (start > 2) {
    pages.push('...');
  }

  // Add range
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }

  // Add ellipsis if needed after range
  if (end < last - 1) {
    pages.push('...');
  }

  // Always show last page
  if (last > 1) {
    pages.push(last);
  }

  return pages;
});
</script>
