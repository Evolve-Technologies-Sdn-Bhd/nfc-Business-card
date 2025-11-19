<template>
  <div>
    <!-- Header -->
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-3xl font-bold text-secondary-900">
            Plan Price Management
          </h1>
          <p class="mt-2 text-secondary-600">
            Manage pricing for Basic, Premium, and Business NFC card plans
          </p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center gap-3">
          <button
            @click="loadPrices"
            :disabled="loading"
            class="btn btn-outline"
          >
            <Icon name="heroicons:arrow-path" class="h-5 w-5 mr-2" :class="{ 'animate-spin': loading }" />
            Refresh
          </button>
          <button
            @click="saveAllChanges"
            :disabled="!hasChanges || saving"
            class="btn btn-primary"
          >
            <Icon name="heroicons:check" class="h-5 w-5 mr-2" />
            {{ saving ? 'Saving...' : 'Save All Changes' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="text-center">
        <Icon name="svg-spinners:ring-resize" class="h-12 w-12 text-primary-600 mx-auto mb-4" />
        <p class="text-secondary-600">Loading plan prices...</p>
      </div>
    </div>

    <!-- Price Cards -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="plan in plans" 
        :key="plan.id"
        class="card overflow-hidden border-2 transition-all"
        :class="getPlanBorderClass(plan.plan_type)"
      >
        <!-- Plan Header -->
        <div class="p-6" :class="getPlanHeaderClass(plan.plan_type)">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-xl font-bold text-white capitalize">
              {{ plan.plan_type }} Plan
            </h3>
            <span v-if="plan.is_active" class="px-3 py-1 bg-white bg-opacity-20 text-white text-xs font-medium rounded-full">
              Active
            </span>
            <span v-else class="px-3 py-1 bg-secondary-500 text-white text-xs font-medium rounded-full">
              Inactive
            </span>
          </div>
          <p class="text-white text-opacity-90 text-sm">
            {{ plan.description || 'NFC Card Plan' }}
          </p>
        </div>

        <!-- Price Input -->
        <div class="p-6">
          <div class="mb-6">
            <label class="block text-sm font-medium text-secondary-700 mb-2">
              Price ({{ plan.currency }})
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-secondary-500 font-medium">
                {{ plan.currency }}
              </span>
              <input
                v-model.number="plan.price"
                @input="markAsChanged(plan)"
                type="number"
                step="0.01"
                min="0"
                class="input w-full pl-16 text-2xl font-bold"
                :class="{ 'border-warning-400 bg-warning-50': isChanged(plan) }"
              />
            </div>
            <p v-if="isChanged(plan)" class="mt-2 text-xs text-warning-600 flex items-center">
              <Icon name="heroicons:exclamation-triangle" class="h-4 w-4 mr-1" />
              Unsaved changes
            </p>
          </div>

          <!-- Description -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-secondary-700 mb-2">
              Description
            </label>
            <textarea
              v-model="plan.description"
              @input="markAsChanged(plan)"
              rows="2"
              class="input w-full text-sm"
              :class="{ 'border-warning-400 bg-warning-50': isChanged(plan) }"
            ></textarea>
          </div>

          <!-- Features -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-secondary-700 mb-2">
              Features
            </label>
            <div class="space-y-2">
              <div v-for="(feature, index) in plan.features" :key="index" class="flex items-center gap-2">
                <Icon name="heroicons:check-circle" class="h-5 w-5 text-success-500 flex-shrink-0" />
                <input
                  v-model="plan.features[index]"
                  @input="markAsChanged(plan)"
                  type="text"
                  class="input flex-1 text-sm py-1"
                  :class="{ 'border-warning-400 bg-warning-50': isChanged(plan) }"
                />
                <button
                  @click="removeFeature(plan, index)"
                  class="p-1 text-error-600 hover:bg-error-50 rounded transition-colors"
                  title="Remove feature"
                >
                  <Icon name="heroicons:x-mark" class="h-5 w-5" />
                </button>
              </div>
            </div>
            <button
              @click="addFeature(plan)"
              class="mt-2 text-sm text-primary-600 hover:text-primary-700 flex items-center"
            >
              <Icon name="heroicons:plus-circle" class="h-5 w-5 mr-1" />
              Add Feature
            </button>
          </div>

          <!-- Status Toggle -->
          <div class="flex items-center justify-between pt-4 border-t border-secondary-200">
            <span class="text-sm font-medium text-secondary-700">Active Status</span>
            <button
              @click="toggleStatus(plan)"
              :class="plan.is_active ? 'bg-success-500' : 'bg-secondary-300'"
              class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
            >
              <span
                :class="plan.is_active ? 'translate-x-6' : 'translate-x-1'"
                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
              />
            </button>
          </div>

          <!-- Individual Save Button -->
          <button
            v-if="isChanged(plan)"
            @click="savePlan(plan)"
            :disabled="saving"
            class="btn btn-primary w-full mt-4"
          >
            {{ saving ? 'Saving...' : 'Save This Plan' }}
          </button>
        </div>

        <!-- Last Updated -->
        <div class="px-6 py-3 bg-secondary-50 border-t border-secondary-200">
          <p class="text-xs text-secondary-500">
            Last updated: {{ formatDate(plan.updated_at) }}
          </p>
        </div>
      </div>
    </div>

    <!-- Change Summary -->
    <div v-if="hasChanges" class="mt-8 card bg-warning-50 border-2 border-warning-400">
      <div class="p-6">
        <div class="flex items-start">
          <Icon name="heroicons:exclamation-triangle" class="h-6 w-6 text-warning-600 mr-3 flex-shrink-0 mt-0.5" />
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-warning-900 mb-2">
              Unsaved Changes
            </h3>
            <p class="text-sm text-warning-800 mb-4">
              You have unsaved changes in {{ changedPlans.length }} plan(s). Click "Save All Changes" to apply them.
            </p>
            <div class="space-y-1">
              <div v-for="plan in changedPlans" :key="plan.id" class="text-sm text-warning-800">
                • <strong class="capitalize">{{ plan.plan_type }}</strong>: 
                <span v-if="plan.originalPrice !== plan.price">
                  Price changed from {{ plan.currency }} {{ plan.originalPrice }} to {{ plan.currency }} {{ plan.price }}
                </span>
                <span v-if="plan.originalDescription !== plan.description && plan.originalPrice !== plan.price"> | </span>
                <span v-if="plan.originalDescription !== plan.description">
                  Description updated
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Info Card -->
    <div class="mt-8 card bg-info-50 border border-info-200">
      <div class="p-6">
        <div class="flex items-start">
          <Icon name="heroicons:information-circle" class="h-6 w-6 text-info-600 mr-3 flex-shrink-0" />
          <div>
            <h3 class="text-sm font-semibold text-info-900 mb-2">
              Important Notes
            </h3>
            <ul class="text-sm text-info-800 space-y-1 list-disc list-inside">
              <li>Price changes will affect new orders immediately after saving</li>
              <li>Existing users will keep their current plan prices</li>
              <li>Inactive plans will not be shown to users during checkout</li>
              <li>All prices are in Malaysian Ringgit (MYR)</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
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

const plans = ref([]);
const loading = ref(false);
const saving = ref(false);
const changedPlansIds = ref(new Set());

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

// Helper functions
const getPlanBorderClass = (planType) => {
  switch (planType) {
    case 'basic':
      return 'border-info-300 hover:border-info-400';
    case 'premium':
      return 'border-purple-300 hover:border-purple-400';
    case 'business':
      return 'border-success-300 hover:border-success-400';
    default:
      return 'border-secondary-300';
  }
};

const getPlanHeaderClass = (planType) => {
  switch (planType) {
    case 'basic':
      return 'bg-gradient-to-r from-info-500 to-info-600';
    case 'premium':
      return 'bg-gradient-to-r from-purple-500 to-purple-600';
    case 'business':
      return 'bg-gradient-to-r from-success-500 to-success-600';
    default:
      return 'bg-secondary-500';
  }
};

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
onBeforeRouteLeave((to, from, next) => {
  if (hasChanges.value) {
    const answer = window.confirm(
      'You have unsaved changes. Are you sure you want to leave?'
    );
    if (answer) {
      next();
    } else {
      next(false);
    }
  } else {
    next();
  }
});
</script>
