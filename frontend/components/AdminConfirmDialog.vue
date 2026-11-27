<!-- components/AdminConfirmDialog.vue
     Accessible confirmation dialog replacing window.confirm()/alert()
     across the admin. Teleports to <body>; tokens live on :root so the
     Ink & Brass styling still resolves outside the .admin-shell tree. -->
<template>
  <Teleport to="body">
    <Transition name="admin-fade">
      <div v-if="modelValue" class="admin-dialog-backdrop" @click.self="cancel">
        <div class="admin-dialog" role="dialog" aria-modal="true" :aria-label="title">
          <div class="admin-dialog-head">
            <div class="flex items-center gap-3">
              <span
                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg"
                :class="tone === 'danger' ? 'bg-error-50 text-error-600' : 'bg-accent-100 text-accent-700'"
              >
                <Icon :name="tone === 'danger' ? 'heroicons:exclamation-triangle' : 'heroicons:information-circle'" class="h-5 w-5" />
              </span>
              <p class="admin-panel-title">{{ title }}</p>
            </div>
            <button type="button" class="admin-icon-btn" aria-label="Close" @click="cancel">
              <Icon name="heroicons:x-mark" class="h-5 w-5" />
            </button>
          </div>

          <div class="admin-panel-body">
            <p class="text-sm leading-relaxed text-secondary-700">{{ message }}</p>
            <slot />
          </div>

          <div class="admin-dialog-foot">
            <button type="button" class="admin-btn admin-btn--ghost" :disabled="loading" @click="cancel">
              {{ cancelLabel }}
            </button>
            <button
              type="button"
              class="admin-btn"
              :class="tone === 'danger' ? 'admin-btn--danger' : 'admin-btn--primary'"
              :disabled="loading"
              @click="$emit('confirm')"
            >
              <Icon v-if="loading" name="heroicons:arrow-path" class="h-4 w-4 animate-spin" />
              {{ confirmLabel }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
defineProps({
  modelValue: { type: Boolean, default: false },
  title: { type: String, default: "Are you sure?" },
  message: { type: String, default: "" },
  confirmLabel: { type: String, default: "Confirm" },
  cancelLabel: { type: String, default: "Cancel" },
  tone: { type: String, default: "primary" }, // primary | danger
  loading: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue", "confirm", "cancel"]);

const cancel = () => {
  emit("update:modelValue", false);
  emit("cancel");
};
</script>
