// composables/useNotifications.js
export const useNotifications = () => {
  const { $api } = useNuxtApp();

  const notifications = ref([]);
  const unreadCount = ref(0);
  const loading = ref(false);

  // Load notifications
  const loadNotifications = async (limit = 50, unreadOnly = false) => {
    loading.value = true;
    try {
      const params = { limit };
      if (unreadOnly) {
        params.unread_only = true;
      }

      const response = await $api.get("/notifications", { params });

      if (response.success) {
        notifications.value = response.data.notifications || [];
        unreadCount.value = response.data.unread_count || 0;
      }
    } catch (error) {
      console.error("Error loading notifications:", error);
    } finally {
      loading.value = false;
    }
  };

  // Get unread count only
  const loadUnreadCount = async () => {
    try {
      const response = await $api.get("/notifications/unread-count");

      if (response.success) {
        unreadCount.value = response.data.unread_count || 0;
      }
    } catch (error) {
      console.error("Error loading unread count:", error);
    }
  };

  // Mark as read
  const markAsRead = async (notificationId) => {
    try {
      const response = await $api.post(
        `/notifications/${notificationId}/mark-read`
      );

      if (response.success) {
        // Update local state
        const notification = notifications.value.find(
          (n) => n.id === notificationId
        );
        if (notification) {
          notification.is_read = true;
          notification.read_at = new Date().toISOString();
        }
        unreadCount.value = Math.max(0, unreadCount.value - 1);
      }
    } catch (error) {
      console.error("Error marking notification as read:", error);
    }
  };

  // Mark all as read
  const markAllAsRead = async () => {
    try {
      const response = await $api.post("/notifications/mark-all-read");

      if (response.success) {
        notifications.value.forEach((n) => {
          n.is_read = true;
          n.read_at = new Date().toISOString();
        });
        unreadCount.value = 0;
        return { ok: true, message: response.message || "All notifications marked as read" };
      }
      return { ok: false, message: response?.message || "Failed to mark all as read" };
    } catch (error) {
      console.error("Error marking all as read:", error);
      return { ok: false, message: error?.message || "Network error while marking all as read" };
    }
  };

  // Delete notification
  const deleteNotification = async (notificationId) => {
    try {
      const response = await $api.delete(`/notifications/${notificationId}`);

      if (response.success) {
        // Remove from local state
        const index = notifications.value.findIndex(
          (n) => n.id === notificationId
        );
        if (index !== -1) {
          const notification = notifications.value[index];
          if (!notification.is_read) {
            unreadCount.value = Math.max(0, unreadCount.value - 1);
          }
          notifications.value.splice(index, 1);
        }
      }
    } catch (error) {
      console.error("Error deleting notification:", error);
    }
  };

  // Delete all read
  const deleteAllRead = async () => {
    try {
      const response = await $api.delete("/notifications/read/all");

      if (response.success) {
        // Remove read notifications from local state
        notifications.value = notifications.value.filter((n) => !n.is_read);
      }
    } catch (error) {
      console.error("Error deleting read notifications:", error);
    }
  };

  // Format time ago
  const getTimeAgo = (date) => {
    const now = new Date();
    const notifDate = new Date(date);
    const seconds = Math.floor((now - notifDate) / 1000);

    if (seconds < 60) return "Just now";
    if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
    if (seconds < 604800) return `${Math.floor(seconds / 86400)}d ago`;
    return notifDate.toLocaleDateString();
  };

  // Notification category (filters, colours). Explicit types first, then a
  // prefix rule so new types (e.g. every nfc_card_* order step) still land in
  // the right group instead of "Other".
  const NOTIFICATION_CATEGORIES = {
    registration_success: "system",
    email_verification: "system",
    account_created: "system",
    guest_checkout_welcome: "system",
    free_plan_deactivated: "system",
    app_update: "system",
    system_message: "system",
    admin_announcement: "system",
    login_new_device: "security",
    password_changed: "security",
    account_warning: "security",
    profile_updated: "activity",
    link_milestone: "activity",
    landing_page_viewed: "activity",
    contact_request: "activity",
    subscription_upgrade: "payment",
    payment_successful: "payment",
    payment_failed: "payment",
    order_rejected: "nfc",
  };

  const getNotificationCategory = (type) => {
    const t = String(type || "");
    if (NOTIFICATION_CATEGORIES[t]) return NOTIFICATION_CATEGORIES[t];
    if (t.startsWith("nfc_card_") || t.startsWith("order_")) return "nfc";
    if (t.startsWith("payment_") || t.startsWith("subscription_") || t.startsWith("invoice_") || t.startsWith("refund_")) return "payment";
    if (t.startsWith("login_") || t.startsWith("password_") || t.includes("two_factor")) return "security";
    return "other";
  };

  // Icon (heroicons name) for a notification: a few specific ones, otherwise
  // the category icon. Rendered with <Icon>, never as emoji text.
  const NOTIFICATION_ICONS = {
    registration_success: "heroicons:sparkles",
    email_verification: "heroicons:envelope",
    login_new_device: "heroicons:device-phone-mobile",
    password_changed: "heroicons:key",
    account_warning: "heroicons:exclamation-triangle",
    profile_updated: "heroicons:user-circle",
    link_milestone: "heroicons:trophy",
    landing_page_viewed: "heroicons:eye",
    contact_request: "heroicons:chat-bubble-left-right",
    payment_successful: "heroicons:check-badge",
    payment_failed: "heroicons:x-circle",
    order_rejected: "heroicons:x-circle",
    nfc_card_shipped: "heroicons:truck",
    nfc_card_delivered: "heroicons:archive-box",
    nfc_card_cancelled: "heroicons:x-circle",
    nfc_card_payment_rejected: "heroicons:x-circle",
    admin_announcement: "heroicons:megaphone",
    free_plan_deactivated: "heroicons:pause-circle",
  };

  const getNotificationIcon = (type) =>
    NOTIFICATION_ICONS[type] || getCategoryInfo(getNotificationCategory(type)).icon;

  // Get category info
  const getCategoryInfo = (category) => {
    const info = {
      all: { label: "All", icon: "heroicons:bell", color: "text-gray-600" },
      system: {
        label: "System",
        icon: "heroicons:information-circle",
        color: "text-blue-600",
      },
      security: {
        label: "Security",
        icon: "heroicons:shield-check",
        color: "text-red-600",
      },
      activity: {
        label: "Activity",
        icon: "heroicons:chart-bar-square",
        color: "text-green-600",
      },
      payment: {
        label: "Payment",
        icon: "heroicons:banknotes",
        color: "text-purple-600",
      },
      nfc: {
        label: "NFC Cards",
        icon: "heroicons:credit-card",
        color: "text-orange-600",
      },
      other: {
        label: "Other",
        icon: "heroicons:inbox",
        color: "text-gray-600",
      },
    };
    return info[category] || info.other;
  };

  // Get priority color
  const getPriorityColor = (priority) => {
    const colors = {
      low: "text-gray-600",
      normal: "text-blue-600",
      high: "text-orange-600",
      urgent: "text-red-600",
    };
    return colors[priority] || "text-gray-600";
  };

  // Get priority class (for background + text color)
  const getPriorityClass = (priority) => {
    const classes = {
      low: "bg-gray-100 text-gray-700",
      normal: "bg-blue-100 text-blue-700",
      high: "bg-orange-100 text-orange-700",
      urgent: "bg-red-100 text-red-700",
    };
    return classes[priority] || "bg-gray-100 text-gray-700";
  };

  return {
    notifications,
    unreadCount,
    loading,
    loadNotifications,
    loadUnreadCount,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    deleteAllRead,
    getTimeAgo,
    getNotificationIcon,
    getNotificationCategory,
    getCategoryInfo,
    getPriorityColor,
    getPriorityClass,
  };
};
