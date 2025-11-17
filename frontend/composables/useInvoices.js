import { ref } from "vue";
import { useToast } from "./useToast";

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
      const endpoint = `/admin/invoices${queryString ? "?" + queryString : ""}`;

      const response = await $api.get(endpoint);

      if (response.success) {
        invoices.value = response.data.data;
        pagination.value = {
          current_page: response.data.current_page,
          last_page: response.data.last_page,
          per_page: response.data.per_page,
          total: response.data.total,
        };
      }

      return response;
    } catch (error) {
      console.error("Error fetching invoices:", error);
      toast.showToast("Failed to load invoices", "error");
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
      const response = await $api.get(`/admin/invoices/${invoiceId}`);

      if (response.success) {
        invoice.value = response.data;
      }

      return response;
    } catch (error) {
      console.error("Error fetching invoice:", error);
      toast.showToast("Failed to load invoice details", "error");
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
      const response = await $api.get("/admin/invoices/statistics");

      if (response.success) {
        statistics.value = response.data;
      }

      return response;
    } catch (error) {
      console.error("Error fetching statistics:", error);
      toast.showToast("Failed to load invoice statistics", "error");
      // Set default empty statistics instead of throwing
      statistics.value = {
        total_invoices: 0,
        paid_invoices: 0,
        total_amount: 0,
        paid_amount: 0,
        pending_amount: 0,
      };
      throw error;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get invoice download URL
   */
  const getDownloadUrl = (invoice) => {
    if (!invoice || !invoice.id) return null;
    return `${config.public.apiBaseUrl}/admin/invoices/${invoice.id}/download`;
  };

  /**
   * Get invoice preview URL
   */
  const getPreviewUrl = (invoice) => {
    if (!invoice || !invoice.id) return null;
    return `${config.public.apiBaseUrl}/admin/invoices/${invoice.id}/preview`;
  };

  /**
   * Download invoice PDF
   */
  const downloadInvoice = async (invoice) => {
    try {
      const tokenCookie = useCookie("auth-token");
      const token = tokenCookie.value;
      const url = `${config.public.apiBaseUrl}/admin/invoices/${invoice.id}/download`;

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
      link.download = `${invoice.invoice_number}.pdf`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(blobUrl);

      toast.showToast("Invoice downloaded successfully", "success");
    } catch (error) {
      console.error("Error downloading invoice:", error);
      toast.showToast("Failed to download invoice", "error");
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
