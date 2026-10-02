<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 translate-y-1 scale-95"
      enter-to-class="opacity-100 translate-y-0 scale-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0 scale-100"
      leave-to-class="opacity-0 translate-y-1 scale-95"
    >
      <div
        v-if="isOpen"
        ref="panelRef"
        class="fixed z-[95] w-96 bg-white rounded-2xl shadow-2xl border border-secondary-200 overflow-hidden flex flex-col"
        :style="panelStyle"
        @click.stop
      >
        <!-- Header -->
        <div class="px-5 py-4 border-b border-secondary-200/60 flex items-center justify-between flex-shrink-0">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center shadow-sm">
              <Icon name="heroicons:bell" class="w-4.5 h-4.5 text-white" />
            </div>
            <div>
              <h3 class="text-sm font-bold text-secondary-900 leading-tight">Notifications</h3>
              <p class="text-[11px] text-secondary-500 leading-tight mt-0.5">
                {{ unreadCount > 0 ? `${unreadCount} unread · ${topNotifications.length} latest` : "All caught up" }}
              </p>
            </div>
          </div>
          <div class="flex items-center gap-0.5">
            <button
              v-if="unreadCount > 0 && topNotifications.length > 0"
              @click="$emit('mark-all-read')"
              class="px-2.5 py-1.5 text-[11px] font-semibold text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-lg transition-colors flex-shrink-0"
            >
              Read all
            </button>
            <button
              @click="$emit('close')"
              class="p-1.5 text-secondary-400 hover:text-secondary-600 hover:bg-secondary-100 rounded-lg transition-colors flex-shrink-0"
              title="Close"
            >
              <Icon name="heroicons:x-mark" class="w-4.5 h-4.5" />
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto max-h-[60vh]">
          <!-- Loading -->
          <div v-if="loading && topNotifications.length === 0" class="py-12 text-center">
            <div
              class="inline-block animate-spin rounded-full h-7 w-7 border-3 border-secondary-200 border-t-primary-600"
            ></div>
            <p class="text-secondary-500 mt-3 text-[11px] font-medium">Loading...</p>
          </div>

          <!-- List -->
          <div
            v-else-if="topNotifications.length > 0"
            class="divide-y divide-secondary-100"
          >
            <div
              v-for="notification in topNotifications"
              :key="notification.id"
              @click="$emit('item-click', notification)"
              class="px-4 py-3.5 hover:bg-secondary-50/70 cursor-pointer transition-colors group"
              :class="{ 'bg-primary-50/30': !notification.is_read }"
            >
              <div class="flex items-start gap-3">
                <div
                  class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm"
                  :class="getIconBgClass(notification.type)"
                >
                  <span class="text-sm leading-none">
                    {{ getIcon(notification.type) }}
                  </span>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-start justify-between gap-2 mb-0.5">
                    <h4
                      class="text-[13px] leading-snug min-w-0"
                      :class="[
                        !notification.is_read
                          ? 'font-bold text-secondary-900'
                          : 'font-semibold text-secondary-700',
                      ]"
                    >
                      <span class="truncate">{{ notification.title }}</span>
                      <span
                        v-if="!notification.is_read"
                        class="inline-block w-1.5 h-1.5 rounded-full bg-primary-500 ml-1.5 align-middle flex-shrink-0"
                      ></span>
                    </h4>
                  </div>
                  <p class="text-xs text-secondary-500 leading-relaxed line-clamp-2 mb-1.5">
                    {{ notification.message }}
                  </p>
                  <div class="flex items-center justify-between">
                    <span class="text-[10px] font-semibold text-secondary-400">
                      {{ getTimeAgo(notification.created_at) }}
                    </span>
                    <div class="flex items-center gap-0 opacity-0 group-hover:opacity-100 transition-opacity">
                      <button
                        v-if="!notification.is_read"
                        @click.stop="$emit('mark-read', notification.id)"
                        class="p-1 text-secondary-400 hover:text-primary-600 hover:bg-primary-50 rounded-md transition-colors"
                        title="Mark as read"
                      >
                        <Icon name="heroicons:check" class="w-3.5 h-3.5" />
                      </button>
                      <button
                        @click.stop="$emit('delete', notification.id)"
                        class="p-1 text-secondary-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors"
                        title="Delete"
                      >
                        <Icon name="heroicons:trash" class="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty -->
          <div v-else class="py-14 px-5 text-center">
            <div class="w-16 h-16 bg-gradient-to-br from-secondary-100 to-secondary-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
              <Icon
                name="heroicons:bell-slash"
                class="w-8 h-8 text-secondary-400"
              />
            </div>
            <h4 class="text-sm font-bold text-secondary-900 mb-1">No notifications</h4>
            <p class="text-[11px] text-secondary-500 leading-relaxed">
              We'll alert you here when something needs your attention.
            </p>
          </div>
        </div>

        <!-- Footer -->
        <div
          class="px-4 py-3 border-t border-secondary-200/60 flex-shrink-0 flex items-center justify-between"
          :class="hasRead ? 'bg-secondary-50/50' : 'bg-secondary-50/30'"
        >
          <button
            v-if="hasRead"
            @click="$emit('clear-read')"
            class="px-2.5 py-1.5 text-[11px] font-semibold text-red-600 hover:bg-red-50 rounded-lg transition-colors inline-flex items-center gap-1"
          >
            <Icon name="heroicons:trash" class="w-3.5 h-3.5" />
            Clear read
          </button>
          <div v-else></div>
          <NuxtLink
            :to="viewAllHref"
            @click="$emit('close')"
            class="px-3 py-1.5 text-[11px] font-bold text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-lg transition-colors inline-flex items-center gap-1"
          >
            View all
            <Icon name="heroicons:arrow-right" class="w-3 h-3" />
          </NuxtLink>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  buttonRef: {
    type: Object,
    default: null,
  },
  notifications: {
    type: Array,
    default: () => [],
  },
  unreadCount: {
    type: Number,
    default: 0,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  viewAllHref: {
    type: String,
    default: "/UserDashboard/Notifications",
  },
  getIcon: {
    type: Function,
    default: () => "🔔",
  },
  getTimeAgo: {
    type: Function,
    default: (d) => {
      try {
        const s = Math.floor((Date.now() - new Date(d).getTime()) / 1000);
        if (s < 60) return "Just now";
        if (s < 3600) return `${Math.floor(s / 60)}m ago`;
        if (s < 86400) return `${Math.floor(s / 3600)}h ago`;
        if (s < 604800) return `${Math.floor(s / 86400)}d ago`;
        return new Date(d).toLocaleDateString();
      } catch { return ""; }
    },
  },
  getCategory: {
    type: Function,
    default: () => "other",
  },
});

const emit = defineEmits(["close", "item-click", "mark-read", "mark-all-read", "delete", "clear-read"]);

const panelRef = ref(null);
const panelStyle = reactive({ top: "0px", left: "0px" });

const TOP_LIMIT = 5;
const topNotifications = computed(() =>
  (props.notifications || []).slice(0, TOP_LIMIT)
);

const hasRead = computed(() =>
  (props.notifications || []).some((n) => n.is_read)
);

const getIconBgClass = (type) => {
  const cat = props.getCategory(type);
  const map = {
    system: "bg-blue-100",
    security: "bg-red-100",
    activity: "bg-green-100",
    payment: "bg-purple-100",
    nfc: "bg-orange-100",
    other: "bg-secondary-100",
  };
  return map[cat] || "bg-secondary-100";
};

// Helper: buttonRef may be either:
//   (a) A raw DOM element (Vue template auto-unwraps ref when passed via :prop)
//   (b) A Vue ref wrapper { value: Element | null } (direct JS usage)
// Normalize to actual element safely.
const getButtonEl = () => {
  const br = props.buttonRef;
  if (!br) return null;
  if (br instanceof Element) return br;
  if (br.value && br.value instanceof Element) return br.value;
  return null;
};

const calculatePosition = () => {
  const btnEl = getButtonEl();
  if (!btnEl) return;
  const btn = btnEl.getBoundingClientRect();
  const panelWidth = 384;
  const gap = 8;

  // Align panel's RIGHT edge with button's RIGHT edge
  // position:fixed uses viewport-relative, same coordinate space as BCR
  let left = btn.right - panelWidth;
  left = Math.max(8, left);
  left = Math.min(left, window.innerWidth - panelWidth - 8);

  let top = btn.bottom + gap;
  const maxH = Math.min(window.innerHeight * 0.7, 600);
  if (top + maxH > window.innerHeight - 16) {
    top = Math.max(16, btn.top - maxH - gap);
  }

  panelStyle.top = `${top}px`;
  panelStyle.left = `${left}px`;
};

watch(
  () => props.isOpen,
  (v) => {
    if (v) {
      // 1. Run before paint
      nextTick(calculatePosition);
      // 2. Run again after layout settled (images/icons rendered, etc.)
      setTimeout(calculatePosition, 50);
      // 3. Final sanity check after transition
      setTimeout(calculatePosition, 220);
    }
  }
);

const handleDocClick = (e) => {
  if (!props.isOpen) return;
  const btnEl = getButtonEl();
  const inPanel = panelRef.value && panelRef.value.contains(e.target);
  const inButton = btnEl && btnEl.contains(e.target);
  if (!inPanel && !inButton) {
    emit("close");
  }
};

const handleDocKeydown = (e) => {
  if (e.key === "Escape" && props.isOpen) {
    emit("close");
  }
};

onMounted(() => {
  if (props.isOpen) {
    nextTick(calculatePosition);
  }
  window.addEventListener("resize", () => {
    if (props.isOpen) calculatePosition();
  });
  window.addEventListener(
    "scroll",
    () => {
      if (props.isOpen) calculatePosition();
    },
    true
  );
  document.addEventListener("click", handleDocClick, true);
  document.addEventListener("keydown", handleDocKeydown);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleDocClick, true);
  document.removeEventListener("keydown", handleDocKeydown);
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.border-3 {
  border-width: 3px;
}
</style>
