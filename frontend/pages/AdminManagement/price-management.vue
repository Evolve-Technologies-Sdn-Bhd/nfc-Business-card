<!-- pages/AdminManagement/price-management.vue -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Billing & Pricing"
      eyebrow-icon="heroicons:banknotes"
      title="Plan Price Management"
      description="Curate the price, description and feature list of every NFCGo card plan. Changes reach new orders the moment you save them."
    >
      <template #actions>
        <button
          type="button"
          class="admin-btn admin-btn--outline"
          :disabled="loading"
          @click="loadPrices"
        >
          <Icon
            name="heroicons:arrow-path"
            class="h-4 w-4"
            :class="{ 'animate-spin': loading }"
          />
          {{ loading ? "Refreshing" : "Refresh" }}
        </button>
        <button
          type="button"
          class="admin-btn admin-btn--primary"
          :disabled="!hasChanges || saving"
          @click="saveAllChanges"
        >
          <Icon name="heroicons:check" class="h-4 w-4" />
          {{ saving ? "Saving…" : "Save all changes" }}
        </button>
      </template>
    </AdminPageHeader>

    <!-- KPI strip -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <AdminStatTile
        label="Total plans"
        :value="plans.length"
        icon="heroicons:banknotes"
        :loading="loading"
      />
      <AdminStatTile
        label="Active plans"
        :value="plans.filter((p) => p.is_active).length"
        icon="heroicons:check-circle"
        icon-class="!bg-success-50 !text-success-600"
        :loading="loading"
      />
      <AdminStatTile
        label="Inactive plans"
        :value="plans.filter((p) => !p.is_active).length"
        icon="heroicons:pause-circle"
        icon-class="!bg-secondary-100 !text-secondary-500"
        :loading="loading"
      />
      <AdminStatTile
        label="Unsaved changes"
        :value="changedPlans.length"
        icon="heroicons:exclamation-triangle"
        icon-class="!bg-warning-50 !text-warning-600"
        :loading="loading"
      />
    </div>

    <!-- Pending changes banner -->
    <AdminSectionCard
      v-if="hasChanges && !loading"
      class="mt-5 !border-warning-300"
    >
      <template #title>
        <span class="inline-flex items-center gap-2">
          <Icon
            name="heroicons:exclamation-triangle"
            class="h-4 w-4 text-warning-600"
          />
          Unsaved changes
        </span>
      </template>
      <template #header-actions>
        <AdminStatusBadge
          tone="warning"
          :label="`${changedPlans.length} plan${changedPlans.length === 1 ? '' : 's'}`"
        />
        <button
          type="button"
          class="admin-btn admin-btn--primary admin-btn--sm"
          :disabled="saving"
          @click="saveAllChanges"
        >
          <Icon name="heroicons:check" class="h-4 w-4" />
          Save all
        </button>
      </template>
      <p class="text-body-sm text-secondary-600">
        Review the pending edits below, then save them together or one plan at a
        time.
      </p>
      <div class="mt-3 flex flex-wrap gap-2">
        <div
          v-for="plan in changedPlans"
          :key="plan.id"
          class="flex flex-wrap items-center gap-2 rounded-lg border border-secondary-200 bg-secondary-50 px-3 py-2"
        >
          <span class="text-body-sm font-semibold capitalize text-secondary-900">
            {{ plan.plan_type }}
          </span>
          <span
            v-if="plan.originalPrice !== plan.price"
            class="admin-badge admin-badge--warning admin-badge--plain tabular-nums"
          >
            Price {{ plan.currency }} {{ plan.originalPrice }} → {{ plan.price }}
          </span>
          <span
            v-if="plan.originalDescription !== plan.description"
            class="admin-badge admin-badge--info admin-badge--plain"
          >
            Description updated
          </span>
        </div>
      </div>
    </AdminSectionCard>

    <!-- Loading -->
    <AdminLoadingState
      v-if="loading"
      variant="cards"
      :rows="4"
      class="mt-5"
    />

    <!-- Empty -->
    <AdminSectionCard v-else-if="!plans.length" class="mt-5">
      <AdminEmptyState
        icon="heroicons:banknotes"
        title="No plan prices configured"
        description="Plan pricing will appear here once it has been set up on the server."
      />
    </AdminSectionCard>

    <!-- Plan grid -->
    <div
      v-else
      class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4"
    >
      <AdminSectionCard
        v-for="plan in plans"
        :key="plan.id"
        :accent="plan.plan_type === 'premium'"
      >
        <template #title>
          <span class="capitalize">{{ plan.plan_type }} plan</span>
        </template>
        <template #header-actions>
          <span
            v-if="plan.plan_type === 'premium'"
            class="admin-badge admin-badge--brass"
          >
            Recommended
          </span>
          <AdminStatusBadge
            :tone="plan.is_active ? 'success' : 'neutral'"
            :label="plan.is_active ? 'Active' : 'Inactive'"
          />
        </template>

        <div class="space-y-5">
          <!-- Price hero + editor -->
          <div class="rounded-xl border border-secondary-200 bg-secondary-50 p-4">
            <div class="flex items-end justify-between gap-3">
              <div>
                <p class="admin-kpi-label">Plan price</p>
                <p class="mt-1 flex items-baseline gap-1.5">
                  <span class="text-caption font-semibold text-secondary-500">
                    {{ plan.currency }}
                  </span>
                  <span
                    class="text-4xl font-bold leading-none tabular-nums text-secondary-900"
                  >
                    {{ plan.price }}
                  </span>
                </p>
              </div>
              <span
                v-if="isChanged(plan)"
                class="admin-badge admin-badge--warning admin-badge--plain"
              >
                <Icon name="heroicons:exclamation-triangle" class="h-3.5 w-3.5" />
                Edited
              </span>
            </div>

            <div class="relative mt-3">
              <span
                class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-helper font-semibold text-secondary-500"
              >
                {{ plan.currency }}
              </span>
              <input
                v-model.number="plan.price"
                type="number"
                step="0.01"
                min="0"
                class="admin-field !pl-16 tabular-nums"
                :class="{ '!border-warning-400 !bg-warning-50': isChanged(plan) }"
                @input="markAsChanged(plan)"
              />
            </div>
          </div>

          <!-- Description -->
          <div>
            <label class="admin-label">Description</label>
            <textarea
              v-model="plan.description"
              rows="2"
              class="admin-field"
              :class="{ '!border-warning-400 !bg-warning-50': isChanged(plan) }"
              @input="markAsChanged(plan)"
            ></textarea>
          </div>

          <!-- Features -->
          <div>
            <label class="admin-label">Included features</label>
            <div class="space-y-2">
              <div
                v-for="(feature, index) in plan.features"
                :key="index"
                class="flex items-center gap-2"
              >
                <Icon
                  name="heroicons:check-circle"
                  class="h-4 w-4 shrink-0 text-success-500"
                />
                <input
                  v-model="plan.features[index]"
                  type="text"
                  class="admin-field !h-9 flex-1"
                  :class="{ '!border-warning-400 !bg-warning-50': isChanged(plan) }"
                  @input="markAsChanged(plan)"
                />
                <button
                  type="button"
                  title="Remove feature"
                  class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-secondary-400 transition hover:bg-error-50 hover:text-error-600"
                  @click="removeFeature(plan, index)"
                >
                  <Icon name="heroicons:x-mark" class="h-4 w-4" />
                </button>
              </div>
            </div>
            <button
              type="button"
              class="mt-2 inline-flex items-center gap-1 rounded-md px-1.5 py-1 text-caption font-semibold text-accent-700 transition hover:bg-accent-50"
              @click="addFeature(plan)"
            >
              <Icon name="heroicons:plus-circle" class="h-4 w-4" />
              Add feature
            </button>
          </div>
        </div>

        <template #footer>
          <div class="flex flex-wrap items-center justify-between gap-3">
            <label class="flex items-center gap-2">
              <button
                type="button"
                role="switch"
                :aria-checked="plan.is_active"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-accent-500 focus:ring-offset-2"
                :class="plan.is_active ? 'bg-success-500' : 'bg-secondary-300'"
                @click="toggleStatus(plan)"
              >
                <span
                  class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                  :class="plan.is_active ? 'translate-x-6' : 'translate-x-1'"
                />
              </button>
              <span class="text-caption font-medium text-secondary-700">
                {{ plan.is_active ? "Active" : "Inactive" }}
              </span>
            </label>
            <button
              v-if="isChanged(plan)"
              type="button"
              :disabled="saving"
              class="admin-btn admin-btn--primary admin-btn--sm"
              @click="savePlan(plan)"
            >
              {{ saving ? "Saving…" : "Save plan" }}
            </button>
          </div>
          <p class="text-helper mt-2 text-secondary-400">
            Last updated: {{ formatDate(plan.updated_at) }}
          </p>
        </template>
      </AdminSectionCard>
    </div>

    <!-- Publishing rules -->
    <AdminSectionCard class="mt-5" title="Publishing rules & notes">
      <ul
        class="grid list-disc gap-x-8 gap-y-2 pl-5 text-body-sm text-secondary-700 sm:grid-cols-2"
      >
        <li>Price changes reach new orders immediately after saving.</li>
        <li>Existing users keep the plan price they signed up with.</li>
        <li>Inactive plans are hidden from users during checkout.</li>
        <li>All prices are in Malaysian Ringgit (MYR).</li>
      </ul>
    </AdminSectionCard>

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
import { ref, computed, onMounted } from 'vue';
import { useNuxtApp } from '#app';

// Define page layout
definePageMeta({
  layout: 'admin-management',
  middleware: ['auth', 'admin']
});

const { $api, $toast } = useNuxtApp();
const router = useRouter();

const plans = ref([]);
const loading = ref(false);
const saving = ref(false);
const changedPlansIds = ref(new Set());

// Confirmation dialog state
const confirmOpen = ref(false);
const confirmBusy = ref(false);
const confirmConfig = ref({
  title: "",
  message: "",
  confirmLabel: "Confirm",
  tone: "primary",
});
let confirmAction = null;

const askConfirm = (config, action) => {
  confirmConfig.value = { confirmLabel: "Confirm", tone: "primary", ...config };
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

// Load plan prices
const loadPrices = async () => {
  loading.value = true;
  try {
    const response = await $api.get('/admin/plan-prices');
    if (response.success) {
      plans.value = response.data.map(plan => ({
        ...plan,
        originalPrice: plan.price,
        originalDescription: plan.description,
        originalFeatures: [...(plan.features || [])],
        originalIsActive: plan.is_active,
      }));
      changedPlansIds.value.clear();
      $toast.success('Plan prices loaded successfully');
    }
  } catch (error) {
    console.error('Error loading plan prices:', error);
    $toast.error('Failed to load plan prices');
  } finally {
    loading.value = false;
  }
};

// Check if plan has changes
const isChanged = (plan) => {
  return changedPlansIds.value.has(plan.id);
};

// Mark plan as changed
const markAsChanged = (plan) => {
  const featuresChanged = JSON.stringify(plan.features) !== JSON.stringify(plan.originalFeatures);
  const hasChanges = 
    plan.price !== plan.originalPrice ||
    plan.description !== plan.originalDescription ||
    plan.is_active !== plan.originalIsActive ||
    featuresChanged;
  
  if (hasChanges) {
    changedPlansIds.value.add(plan.id);
  } else {
    changedPlansIds.value.delete(plan.id);
  }
};

// Add feature
const addFeature = (plan) => {
  if (!plan.features) {
    plan.features = [];
  }
  plan.features.push('New feature');
  markAsChanged(plan);
};

// Remove feature
const removeFeature = (plan, index) => {
  plan.features.splice(index, 1);
  markAsChanged(plan);
};

// Toggle plan status
const toggleStatus = (plan) => {
  plan.is_active = !plan.is_active;
  markAsChanged(plan);
};

// Save individual plan
const savePlan = async (plan) => {
  saving.value = true;
  try {
    const response = await $api.put(`/admin/plan-prices/${plan.id}`, {
      price: plan.price,
      description: plan.description,
      features: plan.features,
      is_active: plan.is_active,
    });

    if (response.success) {
      plan.originalPrice = plan.price;
      plan.originalDescription = plan.description;
      plan.originalFeatures = [...plan.features];
      plan.originalIsActive = plan.is_active;
      changedPlansIds.value.delete(plan.id);
      $toast.success(`${plan.plan_type} plan updated successfully`);
    }
  } catch (error) {
    console.error('Error saving plan:', error);
    $toast.error(error.data?.message || 'Failed to update plan');
  } finally {
    saving.value = false;
  }
};

// Save all changes
const saveAllChanges = async () => {
  if (!hasChanges.value) return;

  saving.value = true;
  try {
    const updates = changedPlans.value.map(plan => ({
      id: plan.id,
      price: plan.price,
      description: plan.description,
      is_active: plan.is_active,
    }));

    // Update each plan individually
    const promises = updates.map(update =>
      $api.put(`/admin/plan-prices/${update.id}`, update)
    );

    await Promise.all(promises);

    // Reload prices to get fresh data
    await loadPrices();
    
    $toast.success('All plan prices updated successfully');
  } catch (error) {
    console.error('Error saving all changes:', error);
    $toast.error('Failed to save all changes');
  } finally {
    saving.value = false;
  }
};

// Computed properties
const hasChanges = computed(() => changedPlansIds.value.size > 0);

const changedPlans = computed(() => {
  return plans.value.filter(plan => changedPlansIds.value.has(plan.id));
});

const formatDate = (dateString) => {
  if (!dateString) return 'Never';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-MY', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

// Lifecycle
onMounted(() => {
  loadPrices();
});

// Warn before leaving with unsaved changes
let allowLeave = false;
onBeforeRouteLeave((to, from, next) => {
  if (!hasChanges.value || allowLeave) {
    next();
    return;
  }
  askConfirm(
    {
      title: "Unsaved changes",
      message:
        "You have unsaved price changes. Leave this page and discard them?",
      confirmLabel: "Leave without saving",
      tone: "danger",
    },
    () => {
      allowLeave = true;
      router.push(to);
    }
  );
  next(false);
});
</script>
