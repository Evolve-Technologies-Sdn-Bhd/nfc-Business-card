<!--
  pages/Onboarding/complete.vue
  Final step of the onboarding funnel. This is the SINGLE place that calls
  POST /complete-onboarding — previously the payment page called it from three
  different code paths, which made the "finished onboarding" state racy.

  Query: ?status=paid | pending   (defaults to paid)
-->
<template>
  <div class="container relative py-16 sm:py-24">
    <div v-if="ready" class="max-w-2xl mx-auto text-center">
      <div
        class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl"
        :style="{
          background: `color-mix(in srgb, ${accent} 18%, transparent)`,
          border: `1px solid color-mix(in srgb, ${accent} 42%, transparent)`,
        }"
      >
        <Icon :name="icon" class="h-8 w-8" :style="{ color: accent }" />
      </div>

      <span class="lp-kicker on-ink mt-8 inline-block">{{ kicker }}</span>
      <h1 class="lp-h2 on-ink mt-4 mb-4">{{ title }}</h1>
      <p class="lp-lead lead-ink">{{ message }}</p>

      <ul
        v-if="isPaid"
        class="mt-10 space-y-3 text-left max-w-md mx-auto text-white/75 text-body-sm"
      >
        <li v-for="item in nextSteps" :key="item" class="flex items-start gap-3">
          <Icon
            name="heroicons:check-circle"
            class="h-5 w-5 mt-0.5 shrink-0"
            :style="{ color: 'var(--brass)' }"
          />
          <span>{{ item }}</span>
        </li>
      </ul>

      <div class="mt-12 flex flex-col sm:flex-row gap-3 justify-center">
        <NuxtLink v-if="primaryCta.to" :to="primaryCta.to" class="btn-brass">
          {{ primaryCta.label }}
        </NuxtLink>
        <NuxtLink to="/UserDashboard" class="btn-ghost-light">
          Go to dashboard
        </NuxtLink>
      </div>

      <!-- Guest checkout: the account was created at Pay time with no password.
           Offer to set one now so it can be signed into later. -->
      <section
        v-if="needsPasswordSetup"
        class="mt-12 mx-auto max-w-md rounded-2xl bg-white p-6 text-left text-secondary-900 shadow-xl ring-1 ring-black/5"
      >
        <h2 class="section-title text-secondary-900 mb-2">Set your password</h2>
        <p class="card-subtitle mb-4">
          Your account uses
          <span class="font-medium text-secondary-900">{{ accountEmail }}</span
          >. Set a password to sign in and manage your card anytime.
        </p>

        <div class="space-y-4">
          <div>
            <label class="block text-label text-secondary-700 mb-1"
              >Password</label
            >
            <input
              v-model="newPassword"
              type="password"
              autocomplete="new-password"
              class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              placeholder="At least 8 characters"
            />
          </div>
          <div>
            <label class="block text-label text-secondary-700 mb-1"
              >Confirm password</label
            >
            <input
              v-model="confirmPassword"
              type="password"
              autocomplete="new-password"
              class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              placeholder="Repeat your password"
            />
          </div>

          <p class="text-helper">
            Use at least 8 characters with an uppercase letter, a lowercase
            letter, a number and a symbol.
          </p>

          <p v-if="passwordError" class="text-body text-red-600">
            {{ passwordError }}
          </p>

          <button
            type="button"
            class="btn btn-primary w-full flex items-center justify-center"
            :disabled="savingPassword"
            @click="setPassword"
          >
            {{ savingPassword ? "Saving..." : "Save password" }}
          </button>
        </div>
      </section>

      <p class="mt-8 text-white/45 text-body-sm">
        Receipt and updates are sent to
        <span class="text-[color:var(--brass)]">{{ accountEmail }}</span>.
      </p>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: "onboarding",
  middleware: "auth",
});

const route = useRoute();
const { $api, $toast } = useNuxtApp();
const authStore = useAuthStore();

const { plan, hydrate, clearDraft } = useOnboardingFlow();

// The funnel state is cleared as part of finishing onboarding, so the outcome
// is snapshotted the moment it is known and the whole page is driven from that
// snapshot — otherwise a free plan would flip back to the default paid copy
// once clearDraft() resets the composable.
const snapshot = ref(null); // { status: "paid" | "pending", isFree: boolean }

const ready = computed(() => snapshot.value !== null);
const status = computed(() => snapshot.value?.status ?? "paid");
const isFree = computed(() => snapshot.value?.isFree === true);
const isPaid = computed(() => status.value === "paid" && !isFree.value);

const accountEmail = computed(() => authStore.user?.email || "");

// A guest-checkout account is created without a password; the backend flags it
// via `requires_password_setup` so we can offer the set-password step here.
const needsPasswordSetup = computed(
  () => authStore.user?.requires_password_setup === true
);

const newPassword = ref("");
const confirmPassword = ref("");
const savingPassword = ref(false);
const passwordError = ref(null);

const setPassword = async () => {
  passwordError.value = null;

  if (newPassword.value.length < 8) {
    passwordError.value = "Password must be at least 8 characters.";
    return;
  }
  if (newPassword.value !== confirmPassword.value) {
    passwordError.value = "Passwords do not match.";
    return;
  }

  savingPassword.value = true;
  try {
    await $api.post("/guest-checkout/set-password", {
      password: newPassword.value,
      password_confirmation: confirmPassword.value,
    });

    // Refreshing clears `requires_password_setup`, which hides this form.
    await authStore.fetchProfile();

    newPassword.value = "";
    confirmPassword.value = "";
    $toast?.success?.("Your password is set. You can sign in with it anytime.");
  } catch (error) {
    const data = error?.response?.data || error?.response?._data || error?.data || {};
    passwordError.value =
      data?.errors?.password?.[0] ||
      data?.message ||
      "Could not set your password. Please try again.";
  } finally {
    savingPassword.value = false;
  }
};

const accent = computed(() => {
  if (isFree.value || status.value === "pending") return "#5d8c87";
  return "var(--brass)"; // follows the admin-selected theme
});

const icon = computed(() => {
  if (status.value === "pending") return "heroicons:clock";
  return "heroicons:check-badge";
});

const kicker = computed(() => {
  if (isFree.value) return "Free plan";
  return status.value === "pending" ? "Payment · Under review" : "Payment · Confirmed";
});

const title = computed(() => {
  if (isFree.value) return "Your free plan is active";
  if (status.value === "pending") return "We're reviewing your payment";
  return "You're all set";
});

const message = computed(() => {
  if (isFree.value)
    return "Your 14-day free plan has started. Build your digital profile and start sharing your card.";
  if (status.value === "pending")
    return "We've received your bank transfer proof. Our team verifies it within 1 business day — you'll get an email as soon as your order is confirmed.";
  return "Your payment was successful. Your NFC card is heading into production and we'll keep you posted by email.";
});

const nextSteps = [
  "We verify the payment and confirm your order",
  "Your NFC card is produced (2–3 business days)",
  "Shipped to your address, tracking sent by email",
];

const primaryCta = computed(() => {
  if (isFree.value)
    return {
      label: "Build your profile",
      to: "/UserDashboard/UserManagement/FreePlanUser/FreeProfileBuilder",
    };
  return { label: "Track your order", to: "/UserDashboard/CardManagement" };
});

const finishOnboarding = async () => {
  // A confirmed paid order and a free plan both end onboarding here. A pending
  // manual transfer does NOT: it keeps the user "new" until the order is
  // actually fulfilled.
  try {
    await $api.post("/complete-onboarding");
  } catch {
    // Non-fatal: the dashboard still works; the flag is a convenience.
  }
  await clearDraft();
};

onMounted(async () => {
  await hydrate();

  const requested = route.query.status === "pending" ? "pending" : "paid";
  snapshot.value = { status: requested, isFree: plan.value === "free" };

  if (requested === "pending") {
    // Manual bank transfer: the order stays unfulfilled until the team verifies
    // it, so keep the draft so the user can resume if the payment needs retrying.
    $toast?.info?.("We'll email you once your payment is confirmed.");
    return;
  }

  // Both the free plan and a confirmed paid payment finish the funnel: the free
  // plan needs no card to design, and a paid order is already fulfilled by the
  // Fiuu webhook. Either way this is the single place that closes onboarding.
  await finishOnboarding();
});
</script>
