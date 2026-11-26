<!-- components/PortalNavGroups.vue
     Grouped navigation list for the user portal rail (desktop + mobile).
     Locked items stay visible — so users can see what their plan lacks — but
     render as buttons that explain why instead of linking to a dead end. -->
<template>
  <div>
    <div v-for="group in groups" :key="group.category" class="admin-nav-group">
      <p class="admin-rail-label px-3 pb-2">{{ group.category }}</p>
      <template v-for="item in group.items" :key="item.name">
        <button
          v-if="item.locked"
          type="button"
          class="admin-nav-link portal-nav-locked w-full text-left"
          :title="item.lockedMessage"
          @click="$emit('locked', item)"
        >
          <Icon :name="item.icon" class="admin-nav-ico h-[18px] w-[18px]" />
          <span class="truncate">{{ item.name }}</span>
          <Icon name="heroicons:lock-closed" class="ml-auto h-3.5 w-3.5 opacity-70" />
        </button>
        <NuxtLink
          v-else
          :to="item.href"
          class="admin-nav-link"
          :class="{
            'is-active': isActive(item.href),
            'pointer-events-none opacity-60': busy && !isActive(item.href),
          }"
          :aria-current="isActive(item.href) ? 'page' : undefined"
          @click="$emit('navigate', item)"
        >
          <Icon :name="item.icon" class="admin-nav-ico h-[18px] w-[18px]" />
          <span class="truncate">{{ item.name }}</span>
          <span
            v-if="item.badge"
            class="ml-auto inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-error-500 px-1 text-badge text-white"
          >
            {{ item.badge }}
          </span>
        </NuxtLink>
      </template>
    </div>
  </div>
</template>

<script setup>
defineProps({
  groups: { type: Array, required: true },
  isActive: { type: Function, required: true },
  busy: { type: Boolean, default: false },
});
defineEmits(["locked", "navigate"]);
</script>

<style scoped>
.portal-nav-locked {
  color: rgba(245, 241, 232, 0.4);
  cursor: not-allowed;
}
.portal-nav-locked:hover {
  color: rgba(245, 241, 232, 0.55);
  background: rgba(255, 255, 255, 0.03);
}
</style>
