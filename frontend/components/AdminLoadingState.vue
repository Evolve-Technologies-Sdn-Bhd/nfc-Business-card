<!-- components/AdminLoadingState.vue
     Skeleton placeholder for tables, card grids or generic blocks so
     pages never flash empty state before data arrives. -->
<template>
  <div v-if="variant === 'table'" class="admin-panel overflow-hidden" role="status" aria-busy="true">
    <div class="admin-scroll">
      <table class="admin-table">
        <thead>
          <tr>
            <th v-for="c in columns" :key="c"><span class="admin-skeleton block h-3 w-20"></span></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in rows" :key="r">
            <td v-for="c in columns" :key="c"><span class="admin-skeleton block h-4 w-full max-w-[10rem]"></span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div v-else-if="variant === 'cards'" class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3" role="status" aria-busy="true">
    <div v-for="i in rows" :key="i" class="admin-panel p-5">
      <span class="admin-skeleton block h-5 w-1/2"></span>
      <span class="admin-skeleton mt-3 block h-3 w-3/4"></span>
      <span class="admin-skeleton mt-2 block h-3 w-2/3"></span>
      <span class="admin-skeleton mt-5 block h-9 w-28"></span>
    </div>
  </div>

  <div v-else class="admin-panel admin-panel-body" role="status" aria-busy="true">
    <span class="admin-skeleton block h-4 w-1/3"></span>
    <span class="admin-skeleton mt-3 block h-4 w-2/3"></span>
    <span class="admin-skeleton mt-3 block h-4 w-1/2"></span>
  </div>
</template>

<script setup>
defineProps({
  variant: { type: String, default: "block" }, // table | cards | block
  rows: { type: Number, default: 6 },
  columns: { type: Number, default: 5 },
});
</script>
