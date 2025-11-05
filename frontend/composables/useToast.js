// composables/useToast.js
export const useToast = () => {
  const success = (message) => {
    // If you're using a toast library like vue-toastification
    // return useNuxtApp().$toast.success(message)

    // For now, using console.log as fallback
    console.log("Success:", message);
    // You can implement your own toast notification here
  };

  const error = (message) => {
    // If you're using a toast library like vue-toastification
    // return useNuxtApp().$toast.error(message)

    // For now, using console.error as fallback
    console.error("Error:", message);
    // You can implement your own toast notification here
  };

  const info = (message) => {
    console.log("Info:", message);
  };

  const warning = (message) => {
    console.warn("Warning:", message);
  };

  return {
    success,
    error,
    info,
    warning,
  };
};
