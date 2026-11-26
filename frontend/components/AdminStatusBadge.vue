<!-- components/AdminStatusBadge.vue
     Status pill with automatic Ink & Brass tone resolution.
     Pass `tone` to force a specific variant, otherwise it is inferred
     from the `status` string. -->
<template>
  <span class="admin-badge" :class="`admin-badge--${resolvedTone}`">
    {{ label || status }}
  </span>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  status: { type: String, default: "" },
  label: { type: String, default: "" },
  tone: { type: String, default: "" },
  plain: { type: Boolean, default: false },
});

const TONE_MAP = {
  success: ["active", "approved", "completed", "complete", "paid", "success", "enabled", "published", "verified", "live", "settled", "delivered", "payment_verified"],
  warning: ["pending", "pending_payment", "awaiting_payment_verification", "draft", "review", "in_review", "awaiting", "onboarding", "unpaid", "partial", "warning"],
  danger: ["failed", "rejected", "cancelled", "canceled", "suspended", "error", "expired", "overdue", "declined", "disabled", "blocked", "revoked"],
  info: ["new", "info", "open", "queued", "submitted", "in_progress", "scheduled", "processing", "shipped"],
  brass: ["premium", "pro", "business", "enterprise", "featured", "vip", "replacement"],
};

const resolvedTone = computed(() => {
  if (props.tone) return props.tone;
  const key = String(props.status || "").toLowerCase().trim();
  for (const [tone, values] of Object.entries(TONE_MAP)) {
    if (values.includes(key)) return tone;
  }
  return "neutral";
});
</script>
