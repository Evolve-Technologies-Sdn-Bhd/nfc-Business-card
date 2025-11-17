// stores/admin.js
export const useAdminStore = defineStore("admin", {
  state: () => ({
    dashboard: null,
    users: [],
    nfcCards: [],
    stats: null,
    loading: false,
    error: null,
    pagination: {
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
    },
    filters: {
      search: "",
      subscription_plan: "",
      status: "",
      is_admin: "",
      businessUserId: "",
      employeeId: "",
    },
  }),

  actions: {
    async fetchDashboard() {
      try {
        this.loading = true;
        const { $api } = useNuxtApp();
        const response = await $api.get("/admin/dashboard");

        if (response.success) {
          this.dashboard = response.data;
        }
      } catch (error) {
        console.error("Failed to fetch dashboard:", error);
        this.error = error.message;
      } finally {
        this.loading = false;
      }
    },

    async fetchUsers(params = {}) {
      try {
        this.loading = true;
        const { $api } = useNuxtApp();

        const queryParams = {
          ...this.filters,
          ...params,
          page: params.page || this.pagination.current_page,
          per_page: params.per_page || this.pagination.per_page,
        };

        const response = await $api.get("/admin/users", {
          params: queryParams,
        });

        if (response.success) {
          this.users = response.data.data;
          this.pagination = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total,
          };
        }
      } catch (error) {
        console.error("Failed to fetch users:", error);
        this.error = error.message;
      } finally {
        this.loading = false;
      }
    },

    async fetchNfcCards(params = {}) {
      try {
        this.loading = true;
        const { $api } = useNuxtApp();

        const queryParams = {
          ...params,
          page: params.page || this.pagination.current_page,
          per_page: params.per_page || this.pagination.per_page,
        };

        const response = await $api.get("/admin/nfc-cards", {
          params: queryParams,
        });

        if (response.success) {
          this.nfcCards = response.data.data;
          this.pagination = {
            current_page: response.data.current_page,
            last_page: response.data.last_page,
            per_page: response.data.per_page,
            total: response.data.total,
          };
        }
      } catch (error) {
        console.error("Failed to fetch NFC cards:", error);
        this.error = error.message;
      } finally {
        this.loading = false;
      }
    },

    async fetchStats() {
      try {
        this.loading = true;
        const { $api } = useNuxtApp();
        const response = await $api.get("/admin/stats");

        if (response.success) {
          this.stats = response.data;
        }
      } catch (error) {
        console.error("Failed to fetch stats:", error);
        this.error = error.message;
      } finally {
        this.loading = false;
      }
    },

    async createUser(userData) {
      try {
        this.loading = true;
        const { $api } = useNuxtApp();
        const response = await $api.post("/admin/users", userData);

        if (response.success) {
          // Refresh users list
          await this.fetchUsers();
          return response;
        }
      } catch (error) {
        console.error("Failed to create user:", error);
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateUser(userId, userData) {
      try {
        this.loading = true;
        const { $api } = useNuxtApp();
        const response = await $api.put(`/admin/users/${userId}`, userData);

        if (response.success) {
          // Refresh users list
          await this.fetchUsers();
          return response;
        }
      } catch (error) {
        console.error("Failed to update user:", error);
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteUser(userId) {
      try {
        this.loading = true;
        const { $api } = useNuxtApp();
        const response = await $api.delete(`/admin/users/${userId}`);

        if (response.success) {
          // Refresh users list
          await this.fetchUsers();
          return response;
        }
      } catch (error) {
        console.error("Failed to delete user:", error);
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async registerNfcCard(cardData) {
      try {
        this.loading = true;
        const { $api } = useNuxtApp();
        const response = await $api.post("/admin/nfc-cards", cardData);

        if (response.success) {
          // Refresh NFC cards list
          await this.fetchNfcCards();
          return response;
        }
      } catch (error) {
        console.error("Failed to register NFC card:", error);
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateNfcCard(cardId, cardData) {
      try {
        this.loading = true;
        const { $api } = useNuxtApp();
        const response = await $api.put(`/admin/nfc-cards/${cardId}`, cardData);

        if (response.success) {
          // Refresh NFC cards list
          await this.fetchNfcCards();
          return response;
        }
      } catch (error) {
        console.error("Failed to update NFC card:", error);
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteNfcCard(cardId) {
      try {
        this.loading = true;
        const { $api } = useNuxtApp();
        const response = await $api.delete(`/admin/nfc-cards/${cardId}`);

        if (response.success) {
          // Refresh NFC cards list
          await this.fetchNfcCards();
          return response;
        }
      } catch (error) {
        console.error("Failed to delete NFC card:", error);
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    updateFilters(newFilters) {
      this.filters = { ...this.filters, ...newFilters };
      this.pagination.current_page = 1; // Reset to first page when filters change
    },

    clearFilters() {
      this.filters = {
        search: "",
        subscription_plan: "",
        status: "",
        is_admin: "",
        businessUserId: "",
        employeeId: "",
      };
      this.pagination.current_page = 1;
    },

    setError(error) {
      this.error = error;
    },

    clearError() {
      this.error = null;
    },
  },

  getters: {
    hasUsers: (state) => state.users.length > 0,
    hasNfcCards: (state) => state.nfcCards.length > 0,
    totalPages: (state) => state.pagination.last_page,
    currentPage: (state) => state.pagination.current_page,
    isLoading: (state) => state.loading,
    hasError: (state) => !!state.error,
  },
});
