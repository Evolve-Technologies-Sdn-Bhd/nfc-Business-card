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
        // Update local state
        notifications.value.forEach((n) => {
          n.is_read = true;
          n.read_at = new Date().toISOString();
        });
        unreadCount.value = 0;
      }
    } catch (error) {
      console.error("Error marking all as read:", error);
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

  // Get notification icon
  const getNotificationIcon = (type) => {
    const icons = {
      registration_success: "🎉",
      email_verification: "✉️",
      login_new_device: "🔒",
      profile_updated: "✅",
      link_milestone: "🎯",
      landing_page_viewed: "👀",
      contact_request: "📧",
      subscription_upgrade: "⭐",
      payment_successful: "💳",
      payment_failed: "❌",
      account_warning: "⚠️",
      password_changed: "🔐",
      nfc_card_purchased: "🛒",
      nfc_card_delivered: "📦",
      nfc_card_linked: "🔗",
      nfc_card_activated: "✨",
      nfc_card_expired: "⏰",
      app_update: "🚀",
      system_message: "ℹ️",
      admin_announcement: "📢",
    };
    return icons[type] || "🔔";
  };

  // Get notification category
  const getNotificationCategory = (type) => {
    const categories = {
      // System notifications
      registration_success: "system",
      email_verification: "system",
      app_update: "system",
      system_message: "system",
      admin_announcement: "system",
      // Security notifications
      login_new_device: "security",
      password_changed: "security",
      account_warning: "security",
      // Profile & Activity
      profile_updated: "activity",
      link_milestone: "activity",
      landing_page_viewed: "activity",
      contact_request: "activity",
      // Payment & Subscription
      subscription_upgrade: "payment",
      payment_successful: "payment",
      payment_failed: "payment",
      // NFC Card
      nfc_card_purchased: "nfc",
      nfc_card_delivered: "nfc",
      nfc_card_linked: "nfc",
      nfc_card_activated: "nfc",
      nfc_card_expired: "nfc",
    };
    return categories[type] || "other";
  };

  // Get category info
  const getCategoryInfo = (category) => {
    const info = {
      all: { label: "All", icon: "heroicons:bell", color: "text-gray-600" },
      system: {
        label: "System",
        icon: "heroicons:cog-6-tooth",
        color: "text-blue-600",
      },
      security: {
        label: "Security",
        icon: "heroicons:shield-check",
        color: "text-red-600",
      },
      activity: {
        label: "Activity",
        icon: "heroicons:chart-bar",
        color: "text-green-600",
      },
      payment: {
        label: "Payment",
        icon: "heroicons:credit-card",
        color: "text-purple-600",
      },
      nfc: {
        label: "NFC Cards",
        icon: "heroicons:qr-code",
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
