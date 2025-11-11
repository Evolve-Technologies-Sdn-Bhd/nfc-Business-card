import { defineStore } from "pinia";

export const useNfcCardStore = defineStore("nfcCard", {
  state: () => ({
    nfcCards: [],
    subscriptionData: null,
    loading: false,
    error: null,
  }),

  getters: {
    hasPremiumSubscription: (state) => {
      return state.subscriptionData?.has_premium_subscription || false;
    },

    activeCards: (state) => {
      return state.nfcCards.filter((card) => card.status === "active");
    },

    inactiveCards: (state) => {
      return state.nfcCards.filter((card) => card.status === "inactive");
    },

    expiredCards: (state) => {
      return state.nfcCards.filter((card) => card.status === "expired");
    },
  },

  actions: {
    async loadSubscriptionStatus() {
      try {
        const { $api } = useNuxtApp();
        const response = await $api.get("/subscription/status");

        if (response.success) {
          this.subscriptionData = response.data;
        }
      } catch (error) {
        console.error("Failed to load subscription status:", error);
        this.error = "Failed to load subscription status";
      }
    },

    async loadNfcCards() {
      if (!this.hasPremiumSubscription) {
        return;
      }

      this.loading = true;
      this.error = null;

      try {
        const { $api } = useNuxtApp();
        const response = await $api.get("/nfc-cards");

        if (response.success) {
          this.nfcCards = response.nfc_cards;
        }
      } catch (error) {
        if (
          error.response?.status === 403 &&
          error.response?.data?.upgrade_required
        ) {
          this.error = "This feature requires a Premium subscription";
        } else {
          this.error = "Failed to load NFC cards";
          console.error("Failed to load NFC cards:", error);
        }
      } finally {
        this.loading = false;
      }
    },

    async orderNewCard(cardData) {
      this.loading = true;
      this.error = null;

      try {
        const { $api } = useNuxtApp();
        const response = await $api.post("/nfc-cards", cardData);

        if (response.success) {
          // Reload cards and subscription data
          await this.loadNfcCards();
          await this.loadSubscriptionStatus();
          return { success: true, data: response.nfc_card };
        }
      } catch (error) {
        if (
          error.response?.status === 403 &&
          error.response?.data?.upgrade_required
        ) {
          this.error = "This feature requires a Premium subscription";
        } else {
          this.error = "Failed to place order";
          console.error("Order error:", error);
        }
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },

    async updateCard(cardId, updateData) {
      this.loading = true;
      this.error = null;

      try {
        const { $api } = useNuxtApp();
        const response = await $api.put(`/nfc-cards/${cardId}`, updateData);

        if (response.success) {
          // Update the card in the store
          const index = this.nfcCards.findIndex((card) => card.id === cardId);
          if (index !== -1) {
            this.nfcCards[index] = response.nfc_card;
          }
          return { success: true, data: response.nfc_card };
        }
      } catch (error) {
        if (
          error.response?.status === 403 &&
          error.response?.data?.upgrade_required
        ) {
          this.error = "This feature requires a Premium subscription";
        } else {
          this.error = "Failed to update card";
          console.error("Update error:", error);
        }
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },

    async activateCard(cardId, activationData) {
      this.loading = true;
      this.error = null;

      try {
        const { $api } = useNuxtApp();
        const response = await $api.post(
          `/nfc-cards/${cardId}/activate`,
          activationData
        );

        if (response.success) {
          // Reload cards to get updated status
          await this.loadNfcCards();
          return { success: true, data: response.nfc_tag };
        }
      } catch (error) {
        if (
          error.response?.status === 403 &&
          error.response?.data?.upgrade_required
        ) {
          this.error = "This feature requires a Premium subscription";
        } else {
          this.error = "Failed to activate card";
          console.error("Activate error:", error);
        }
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },

    async deactivateCard(cardId) {
      this.loading = true;
      this.error = null;

      try {
        const { $api } = useNuxtApp();
        const response = await $api.post(`/nfc-cards/${cardId}/deactivate`);

        if (response.success) {
          // Reload cards to get updated status
          await this.loadNfcCards();
          return { success: true };
        }
      } catch (error) {
        if (
          error.response?.status === 403 &&
          error.response?.data?.upgrade_required
        ) {
          this.error = "This feature requires a Premium subscription";
        } else {
          this.error = "Failed to deactivate card";
          console.error("Deactivate error:", error);
        }
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },

    async getCardAnalytics(cardId) {
      this.loading = true;
      this.error = null;

      try {
        const { $api } = useNuxtApp();
        const response = await $api.get(`/nfc-cards/${cardId}/analytics`);

        if (response.success) {
          return { success: true, data: response.data };
        }
      } catch (error) {
        if (
          error.response?.status === 403 &&
          error.response?.data?.upgrade_required
        ) {
          this.error = "This feature requires a Premium subscription";
        } else {
          this.error = "Failed to load analytics";
          console.error("Analytics error:", error);
        }
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },

    clearError() {
      this.error = null;
    },

    reset() {
      this.nfcCards = [];
      this.subscriptionData = null;
      this.loading = false;
      this.error = null;
    },
  },
});
