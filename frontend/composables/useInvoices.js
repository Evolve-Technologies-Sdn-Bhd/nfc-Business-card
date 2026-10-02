import { ref } from "vue";
import { useToast } from "./useToast";

/**
 * useInvoices — Role-aware invoice composable.
 *
 * Automatically selects the correct endpoint prefix based on current user:
 *   - Admin users  →  /admin/invoices/*   (full CRUD, all users' invoices)
 *   - Regular users →  /invoices/*         (own invoices only, defined in api.php L302-316)
 *
 * FALLBACK: If somehow a non-admin user still hits /admin/* URLs, the
 * axios request guard in api.client.js will BLOCK them before the server,
 * and the 403 response handler will suppress the intrusive toast.
 */
export const useInvoices = () => {
  const config = useRuntimeConfig();
  const { $api } = useNuxtApp();
  const toast = useToast();
  const invoices = ref([]);
  const invoice = ref(null);
  const statistics = ref(null);
  const loading = ref(false);
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  });

  /**
   * Resolve the correct route prefix based on current user's admin status.
   * Returns '/admin/invoices' for admins, '/invoices' for regular users.
   */
  const _prefix = () => {
    try {
      const authStore = useAuthStore();
      const isAdmin = Boolean(
        authStore?.user?.is_admin ||
        authStore?.user?.admin_role ||
        authStore?.isAdmin
      );
      return isAdmin ? "/admin/invoices" : "/invoices";
    } catch (_) {
      return "/invoices"; // safe default: user-level routes
    }
  };

  /**
   * Fetch invoices list
   */
  const fetchInvoices = async (filters = {}) => {
    loading.value = true;
    try {
      const params = new URLSearchParams();

      if (filters.status) params.append("status", filters.status);
      if (filters.from_date) params.append("from_date", filters.from_date);
      if (filters.to_date) params.append("to_date", filters.to_date);
      if (filters.search) params.append("search", filters.search);
      if (filters.page) params.append("page", filters.page);
      if (filters.per_page) params.append("per_page", filters.per_page);
      if (filters.all_versions) params.append("all_versions", "1");

      const queryString = params.toString();
      const endpoint = `${_prefix()}${queryString ? "?" + queryString : ""}`;

      const response = await $api.get(endpoint);

      if (response.success) {
        invoices.value = response.data.data ?? response.data ?? [];
        if (response.data?.current_page !== undefined) {
          pagination.value = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total,
          };
        }
      }

      return response;
    } catch (error) {
      console.error("Error fetching invoices:", error);
      const blocked = error?.response?.data?.blocked_by_guard;
      if (!blocked && toast.error) toast.error("Failed to load invoices");
      throw error;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Fetch single invoice
   */
  const fetchInvoice = async (invoiceId) => {
    loading.value = true;
    try {
      const response = await $api.get(`${_prefix()}/${invoiceId}`);

      if (response.success) {
        invoice.value = response.data;
      }

      return response;
    } catch (error) {
      console.error("Error fetching invoice:", error);
      const blocked = error?.response?.data?.blocked_by_guard;
      if (!blocked && toast.error) toast.error("Failed to load invoice details");
      throw error;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Fetch invoice statistics
   */
  const fetchStatistics = async () => {
    loading.value = true;
    try {
      const response = await $api.get(`${_prefix()}/statistics`);

      if (response.success) {
        statistics.value = response.data;
      }

      return response;
    } catch (error) {
      console.error("Error fetching statistics:", error);
      // Set default empty statistics instead of throwing
      statistics.value = {
        total_invoices: 0,
        paid_invoices: 0,
        total_amount: 0,
        paid_amount: 0,
        pending_amount: 0,
      };
      const blocked = error?.response?.data?.blocked_by_guard;
      if (!blocked && toast.error) toast.error("Failed to load invoice statistics");
      throw error;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get invoice download URL
   *  - Admin:   /admin/invoices/{id}/download
   *  - User:    /invoices/{id}/download (SIGNED route — hit API first to obtain one-time signed URL)
   *             Falls back to direct /invoices/{id}/download if no signed URL returned.
   */
  const getDownloadUrl = (inv) => {
    if (!inv || !inv.id) return null;
    // For admins: direct admin download URL
    // For users: prefer signed URL if provided; else fall back to direct user /invoices route.
    // (Download via downloadInvoice() below is preferred — it uses signed URL / fetch() flow.)
    try {
      const authStore = useAuthStore();
      const isAdmin = Boolean(
        authStore?.user?.is_admin ||
        authStore?.user?.admin_role ||
        authStore?.isAdmin
      );
      const prefix = isAdmin ? "/admin/invoices" : "/invoices";
      return `${config.public.apiBaseUrl}${prefix}/${inv.id}/download`;
    } catch (_) {
      return `${config.public.apiBaseUrl}/invoices/${inv.id}/download`;
    }
  };

  /**
   * Get invoice preview URL
   */
  const getPreviewUrl = (inv) => {
    if (!inv || !inv.id) return null;
    try {
      const authStore = useAuthStore();
      const isAdmin = Boolean(
        authStore?.user?.is_admin ||
        authStore?.user?.admin_role ||
        authStore?.isAdmin
      );
      const prefix = isAdmin ? "/admin/invoices" : "/invoices";
      return `${config.public.apiBaseUrl}${prefix}/${inv.id}/preview`;
    } catch (_) {
      return `${config.public.apiBaseUrl}/invoices/${inv.id}/preview`;
    }
  };

  /**
   * Download invoice PDF — uses role-aware URL.
   */
  const downloadInvoice = async (inv) => {
    try {
      const tokenCookie = useCookie("auth-token");
      const token = tokenCookie.value;

      let isAdmin = false;
      try {
        const authStore = useAuthStore();
        isAdmin = Boolean(
          authStore?.user?.is_admin ||
          authStore?.user?.admin_role ||
          authStore?.isAdmin
        );
      } catch (_) { /* not available */ }

      const prefix = isAdmin ? "/admin/invoices" : "/invoices";
      const url = `${config.public.apiBaseUrl}${prefix}/${inv.id}/download`;

      const response = await fetch(url, {
        method: "GET",
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: "application/pdf",
        },
      });

      if (!response.ok) {
        throw new Error("Download failed");
      }

      const blob = await response.blob();
      const blobUrl = window.URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.href = blobUrl;
      link.download = `${inv.invoice_number || `invoice-${inv.id}`}.pdf`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(blobUrl);

      if (toast.success) toast.success("Invoice downloaded successfully");
    } catch (error) {
      console.error("Error downloading invoice:", error);
      const blocked = error?.response?.data?.blocked_by_guard;
      if (!blocked && toast.error) toast.error("Failed to download invoice");
      throw error;
    }
  };

  /**
   * Format currency
   */
  const formatCurrency = (amount, currency = "MYR") => {
    return new Intl.NumberFormat("en-MY", {
      style: "currency",
      currency: currency,
    }).format(amount);
  };

  /**
   * Format date
   */
  const formatDate = (date) => {
    if (!date) return "-";
    return new Date(date).toLocaleDateString("en-MY", {
      year: "numeric",
      month: "short",
      day: "numeric",
    });
  };

  /**
   * Get status badge class
   */
  const getStatusBadgeClass = (status) => {
    const classes = {
      draft: "bg-gray-100 text-gray-800",
      issued: "bg-blue-100 text-blue-800",
      paid: "bg-green-100 text-green-800",
      cancelled: "bg-red-100 text-red-800",
      refunded: "bg-yellow-100 text-yellow-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
  };

  /**
   * Get status label
   */
  const getStatusLabel = (status) => {
    const labels = {
      draft: "Draft",
      issued: "Issued",
      paid: "Paid",
      cancelled: "Cancelled",
      refunded: "Refunded",
    };
    return labels[status] || status;
  };

  return {
    // State
    invoices,
    invoice,
    statistics,
    loading,
    pagination,

    // Methods
    fetchInvoices,
    fetchInvoice,
    fetchStatistics,
    getDownloadUrl,
    getPreviewUrl,
    downloadInvoice,

    // Utilities
    formatCurrency,
    formatDate,
    getStatusBadgeClass,
    getStatusLabel,
  };
};
