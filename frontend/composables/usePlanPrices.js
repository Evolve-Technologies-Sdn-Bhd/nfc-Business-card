// composables/usePlanPrices.js
// PUBLIC user-safe composable. NEVER call /admin/* endpoints here — those are Admin Dashboard only.
// Any non-admin call to /admin/* will be AUTOMATICALLY BLOCKED by axios request guard in api.client.js.
export const usePlanPrices = () => {
  const { $api } = useNuxtApp();
  
  const planPrices = ref([]);
  const loading = ref(false);
  const error = ref(null);
  const hasLoaded = ref(false);

  // Static fallback list — guaranteed to exist even if API is fully unreachable
  const STATIC_FALLBACK = [
    {
      plan_type: 'free',
      price: 0,
      currency: 'MYR',
      description: 'Free Plan',
      features: ['Digital business card', 'Basic profile', 'Contact sharing', 'Basic analytics'],
      is_active: true
    },
    {
      plan_type: 'basic',
      price: 29.00,
      currency: 'MYR',
      description: 'Basic NFC Card Plan',
      features: ['One NFC card', 'Basic profile', 'Contact sharing', 'Basic analytics'],
      is_active: true
    },
    {
      plan_type: 'premium',
      price: 99.00,
      currency: 'MYR',
      description: 'Premium NFC Card Plan',
      features: ['One premium NFC card', 'Advanced profile customization', 'Social media integration', 'Advanced analytics', 'Priority support'],
      is_active: true
    },
    {
      plan_type: 'business',
      price: 299.00,
      currency: 'MYR',
      description: 'Business NFC Card Plan',
      features: ['Multiple NFC cards', 'Employee management', 'Bulk ordering', 'Business analytics', 'Dedicated support'],
      is_active: true
    }
  ];

  // Load prices from PUBLIC API (not admin)
  const loadPrices = async () => {
    // Deduplicate: avoid re-fetch if we already have valid data
    if (hasLoaded.value && planPrices.value?.length > 0) return;

    loading.value = true;
    error.value = null;

    // Always populate fallback FIRST — so UI never renders empty
    planPrices.value = STATIC_FALLBACK.slice();

    try {
      const response = await $api.get('/plan-prices/public', { timeout: 15000 });
      if (response?.success && Array.isArray(response?.data) && response.data.length > 0) {
        const active = response.data.filter(p => p.is_active !== false && p.is_active !== 0);
        if (active.length > 0) {
          planPrices.value = active;
        }
      }
      hasLoaded.value = true;
    } catch (err) {
      if (process.env.NODE_ENV !== 'production') {
        const blocked = err?.response?.data?.blocked_by_guard;
        if (blocked) {
          console.warn('[usePlanPrices] Guard blocked accidental /admin/* call — please fix route in caller.');
        } else {
          console.warn('[usePlanPrices] Public route failed, keeping static fallback:', err?.message || String(err));
        }
      }
      error.value = err;
    } finally {
      loading.value = false;
    }
  };

  // Get price for specific plan
  const getPlanPrice = (planType) => {
    const plan = planPrices.value.find(p => p.plan_type === planType);
    return plan ? parseFloat(plan.price) : 0;
  };

  // Get plan details
  const getPlanDetails = (planType) => {
    return planPrices.value.find(p => p.plan_type === planType) || null;
  };

  // Get formatted price
  const getFormattedPrice = (planType) => {
    const plan = planPrices.value.find(p => p.plan_type === planType);
    if (!plan) return 'Free';
    const currencySymbol = plan.currency === 'MYR' ? 'RM' : plan.currency;
    const price = parseFloat(plan.price);
    if (!price || price === 0) return 'Free';
    return `${currencySymbol} ${price.toFixed(2)}`;
  };

  // Get all active plans
  const activePlans = computed(() => {
    return planPrices.value.filter(p => p.is_active);
  });

  return {
    planPrices,
    loading,
    error,
    loadPrices,
    getPlanPrice,
    getPlanDetails,
    getFormattedPrice,
    activePlans
  };
};
