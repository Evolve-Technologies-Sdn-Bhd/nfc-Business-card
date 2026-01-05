<template>
  <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
    <!-- Results counter -->
    <div class="flex items-center space-x-4">
      <span class="text-sm text-secondary-700">
        Showing
        {{ total === 0 ? 0 : (currentPage - 1) * perPage + 1 }} -
        {{ Math.min(currentPage * perPage, total) }}
        of {{ total }} {{ itemLabel }}
      </span>
      <!-- Per page selector -->
      <div class="flex items-center space-x-2">
        <span class="text-sm text-secondary-600">Per page:</span>
        <select
          :value="perPage"
          @change="$emit('per-page-change', Number($event.target.value))"
          class="input input-sm w-20"
        >
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
      </div>
    </div>
    <!-- Page navigation -->
    <div class="flex items-center space-x-2">
      <button
        @click="$emit('page-change', currentPage - 1)"
        :disabled="currentPage === 1"
        class="btn btn-outline btn-sm"
      >
        Previous
      </button>
      <!-- Page numbers -->
      <template v-for="page in visiblePages" :key="page">
        <span v-if="page === '...'" class="px-2 text-secondary-400">...</span>
        <button
          v-else
          @click="$emit('page-change', page)"
          :class="[
            'btn btn-sm',
            page === currentPage ? 'btn-primary' : 'btn-outline'
          ]"
        >
          {{ page }}
        </button>
      </template>
      <button
        @click="$emit('page-change', currentPage + 1)"
        :disabled="currentPage === lastPage"
        class="btn btn-outline btn-sm"
      >
        Next
      </button>
    </div>
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
