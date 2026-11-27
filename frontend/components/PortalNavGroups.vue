<!-- components/PortalNavGroups.vue
     Grouped navigation list for the user portal rail (desktop + mobile).
     Locked items stay visible — so users can see what their plan lacks — but
     render as buttons that explain why instead of linking to a dead end. -->
<template>
  <div>
    <div v-for="group in groups" :key="group.category" class="admin-nav-group">
      <p v-if="!collapsed" class="admin-rail-label px-3 pb-2">{{ group.category }}</p>
      <template v-for="item in group.items" :key="item.name">
        <button
          v-if="item.locked"
          type="button"
          class="admin-nav-link portal-nav-locked w-full text-left"
          :title="collapsed ? `${item.name} — ${item.lockedMessage}` : item.lockedMessage"
          :aria-label="collapsed ? `${item.name} (locked)` : undefined"
          @click="$emit('locked', item)"
        >
          <Icon :name="item.icon" class="admin-nav-ico h-[18px] w-[18px]" />
          <span v-if="!collapsed" class="truncate">{{ item.name }}</span>
          <Icon v-if="!collapsed" name="heroicons:lock-closed" class="ml-auto h-3.5 w-3.5 opacity-70" />
        </button>
        <NuxtLink
          v-else
          :to="item.href"
          class="admin-nav-link relative"
          :class="{
            'is-active': isActive(item.href),
            'pointer-events-none opacity-60': busy && !isActive(item.href),
          }"
          :title="collapsed ? item.name : undefined"
          :aria-label="collapsed ? item.name : undefined"
          :aria-current="isActive(item.href) ? 'page' : undefined"
          @click="$emit('navigate', item)"
        >
          <Icon :name="item.icon" class="admin-nav-ico h-[18px] w-[18px]" />
          <span v-if="!collapsed" class="truncate">{{ item.name }}</span>
          <span
            v-if="item.badge && !collapsed"
            class="ml-auto inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-error-500 px-1 text-badge text-white"
          >
            {{ item.badge }}
          </span>
          <!-- Collapsed rail: unread badge becomes a dot on the icon -->
          <span
            v-else-if="item.badge && collapsed"
            class="absolute right-3 top-2 h-2 w-2 rounded-full bg-error-500 ring-2 ring-[color:var(--admin-ink)]"
          ></span>
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
  // Icon-only rail (desktop collapse); labels move into tooltips
  collapsed: { type: Boolean, default: false },
});
defineEmits(["locked", "navigate"]);
</script>

<style scoped>
.portal-nav-locked {
  color: color-mix(in srgb, var(--admin-paper) 40%, transparent);
  cursor: not-allowed;
}
.portal-nav-locked:hover {
  color: color-mix(in srgb, var(--admin-paper) 55%, transparent);
  background: rgba(255, 255, 255, 0.03);
}
</style>
