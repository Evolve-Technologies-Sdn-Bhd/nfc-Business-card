// composables/usePlanPrices.js
export const usePlanPrices = () => {
  const { $api } = useNuxtApp();
  
  const planPrices = ref([]);
  const loading = ref(false);
  const error = ref(null);

  // Load prices from API
  const loadPrices = async () => {
    loading.value = true;
    error.value = null;
    
    try {
      const response = await $api.get('/plan-prices/public');
      if (response.success) {
        planPrices.value = response.data;
      }
    } catch (err) {
      console.error('Error loading plan prices:', err);
      error.value = err;
      
      // Fallback to default prices if API fails
      planPrices.value = [
        {
          plan_type: 'basic',
          price: 99.00,
          currency: 'MYR',
          description: 'Basic NFC Card Plan',
          features: ['One NFC card', 'Basic profile', 'Contact sharing', 'Basic analytics'],
          is_active: true
        },
        {
          plan_type: 'premium',
          price: 199.00,
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
    
    return `${plan.currency} ${parseFloat(plan.price).toFixed(2)}`;
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
