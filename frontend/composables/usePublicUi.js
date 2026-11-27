/**
 * Shared UI state for the public (marketing) site.
 *
 * The navbar, footer and the global modals live in `layouts/public.vue`, but
 * page content also needs to open them — the Cards page opens the contact
 * modal and the Pricing page opens the business-plan request modal. `useState`
 * keeps a single reactive instance shared between the layout and every public
 * page, so the modals never need to be duplicated per page.
 */

const emptyBusinessForm = () => ({
  company_name: "",
  company_address: "",
  quota: null,
  contact_person: "",
  phone: "",
  email: "",
  notes: "",
});

export const usePublicUi = () => {
  const { $api, $toast } = useNuxtApp();
  const authStore = useAuthStore();
  const runtimeConfig = useRuntimeConfig();

  /* ---------------- Contact modal ---------------- */
  const showContactModal = useState("public:contact-modal", () => false);

  const openContactModal = () => {
    showContactModal.value = true;
  };

  const closeContactModal = () => {
    showContactModal.value = false;
  };

  /* ---------------- Business plan request modal ---------------- */
  const showBusinessModal = useState("public:business-modal", () => false);
  const showBusinessRequestSuccess = useState(
    "public:business-success",
    () => false
  );
  const showBusinessPlanPending = useState(
    "public:business-pending",
    () => false
  );
  const businessPlanEmail = useState("public:business-email", () => "");
  const submittingBusinessRequest = useState(
    "public:business-submitting",
    () => false
  );
  const businessForm = useState("public:business-form", emptyBusinessForm);

  // Pre-filled contact details for the signed-in user, if any.
  const knownContact = () => ({
    contact_person:
      authStore.user?.first_name && authStore.user?.last_name
        ? `${authStore.user.first_name} ${authStore.user.last_name}`
        : authStore.user?.name || "",
    email: authStore.user?.email || "",
  });

  const contactSupport = () => {
    // Open the modal for everyone — anonymous visitors can submit an enquiry
    // too. Signed-in users get their name/email pre-filled.
    Object.assign(businessForm.value, knownContact());
    showBusinessModal.value = true;
  };

  const closeBusinessModal = () => {
    showBusinessModal.value = false;
    Object.assign(businessForm.value, emptyBusinessForm(), knownContact());
  };

  const dismissBusinessRequestSuccess = () => {
    showBusinessRequestSuccess.value = false;
    if (process.client) {
      const url = new URL(window.location.href);
      url.searchParams.delete("businessRequest");
      window.history.replaceState({}, "", url.pathname);
    }
  };

  const clearBusinessPlanRestriction = () => {
    if (process.client) {
      localStorage.removeItem("businessPlanRequested");
      localStorage.removeItem("businessPlanEmail");
      localStorage.removeItem("businessPlanCompany");
      localStorage.removeItem("businessPlanTimestamp");
    }
    showBusinessPlanPending.value = false;
    navigateTo("/UserAccount/login");
  };

  const submitBusinessRequest = async () => {
    submittingBusinessRequest.value = true;

    try {
      const response = await $api.post("/business-inquiries", {
        company_name: businessForm.value.company_name,
        company_address: businessForm.value.company_address,
        quota: businessForm.value.quota,
        contact_person: businessForm.value.contact_person,
        phone: businessForm.value.phone,
        email: businessForm.value.email,
        notes: businessForm.value.notes,
        source: "homepage",
      });

      if (!response?.success) {
        throw new Error(response?.message || "Failed to submit enquiry");
      }

      $toast.success(
        "Your Business Plan enquiry has been received. Our team will contact you soon."
      );

      closeBusinessModal();
      // Show the inline success alert on whatever public page we are on.
      showBusinessRequestSuccess.value = true;
      setTimeout(() => {
        dismissBusinessRequestSuccess();
      }, 15000);
    } catch (error) {
      console.error("Business plan request error:", error);
      $toast.error(
        error?.response?.data?.message ||
          "Failed to submit your enquiry. Please try again or contact support."
      );
    } finally {
      submittingBusinessRequest.value = false;
    }
  };

  /* ---------------- Legal document modal ---------------- */
  const showLegalModal = useState("public:legal-modal", () => false);
  const legalModalType = useState("public:legal-type", () => "");
  const loadingLegalDoc = useState("public:legal-loading", () => false);
  const legalDocInfo = useState("public:legal-info", () => null);
  const legalDocError = useState("public:legal-error", () => "");

  const legalPdfUrl = computed(() => {
    if (!legalModalType.value) return "";
    const apiBaseUrl =
      runtimeConfig.public.apiBaseUrl || "http://localhost:8000/api";
    return `${apiBaseUrl}/legal/pdf/${legalModalType.value}/view`;
  });

  const openLegalModal = async (type) => {
    legalModalType.value = type;
    showLegalModal.value = true;
    loadingLegalDoc.value = true;
    legalDocError.value = "";
    legalDocInfo.value = null;

    try {
      const response = await $api.get(`/legal/pdf/${type}/info`);
      if (response.success && response.data) {
        legalDocInfo.value = response.data;
      } else {
        legalDocError.value = response.message || "Document not available.";
      }
    } catch (error) {
      console.error("Error loading legal document info:", error);
      legalDocError.value =
        error.data?.message || "Failed to load document. Please try again later.";
    } finally {
      loadingLegalDoc.value = false;
    }
  };

  const closeLegalModal = () => {
    showLegalModal.value = false;
    legalModalType.value = "";
    legalDocInfo.value = null;
    legalDocError.value = "";
  };

  const formatFileSize = (bytes) => {
    if (!bytes) return "";
    if (bytes < 1024) return bytes + " B";
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
    return (bytes / (1024 * 1024)).toFixed(1) + " MB";
  };

  return {
    // Contact
    showContactModal,
    openContactModal,
    closeContactModal,
    // Business plan
    showBusinessModal,
    showBusinessRequestSuccess,
    showBusinessPlanPending,
    businessPlanEmail,
    submittingBusinessRequest,
    businessForm,
    contactSupport,
    closeBusinessModal,
    dismissBusinessRequestSuccess,
    clearBusinessPlanRestriction,
    submitBusinessRequest,
    // Legal
    showLegalModal,
    legalModalType,
    loadingLegalDoc,
    legalDocInfo,
    legalDocError,
    legalPdfUrl,
    openLegalModal,
    closeLegalModal,
    formatFileSize,
  };
};
