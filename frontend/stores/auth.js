// stores/auth.js

const callApi = async (method, url, data = null) => {
  const nuxtApp = useNuxtApp();
  const config = useRuntimeConfig();

  if (nuxtApp.$api) {
    const { $api } = nuxtApp;
    if (method === "get") {
      return await $api.get(url, { params: data || undefined });
    }
    return await $api[method](url, data);
  }

  const baseURL = config.public.apiBaseUrl || "http://localhost:8000/api";
  const options = {
    baseURL,
    method: method.toUpperCase(),
  };
  if (data) {
    options.body = data;
  }
  console.warn("API plugin not available, falling back to $fetch for", url);
  return await $fetch(url, options);
};

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null,
    token: null,
    isAuthenticated: false,
  }),

  getters: {
    hasAnyPhysicalNfcCard() {
      const cards = this.user?.nfc_cards ?? this.user?.nfcCards;
      return Array.isArray(cards) && cards.length > 0;
    },

    eligibleBuilderStatuses() {
      return ['payment_verified','processing','shipped','delivered','active'];
    },

    hasAccessPaidBuilderCards() {
      const cards = this.user?.nfc_cards ?? this.user?.nfcCards;
      if (!Array.isArray(cards) || cards.length === 0) return false;
      const ok = this.eligibleBuilderStatuses;
      return cards.some(card => ok.includes(String(card.status || '').toLowerCase()));
    },

    /**
     * User boleh access Premium/Basic/Business Profile Builder JIKA:
     *  - subscription_active === true AND plan match, ATAU
     *  - mempunyai sekurang-kurangnya 1 NfcCard dengan status >= payment_verified DAN plan match
     */
    canAccessPaidProfileBuilder() {
      return (plan = null) => {
        const ok = this.eligibleBuilderStatuses;
        if (this.user?.subscription_active === true) {
          if (plan === null) return true;
          const userPlan = String(this.user.subscription_plan || 'free').toLowerCase();
          const matchPlan = ['premium','business'].includes(plan) ? [plan] : ['basic','premium','business'];
          if (matchPlan.includes(userPlan)) return true;
        }
        const cards = this.user?.nfc_cards ?? this.user?.nfcCards;
        if (Array.isArray(cards) && cards.length > 0) {
          if (plan === null) return cards.some(c => ok.includes(String(c.status || '')));
          const matchPlan = ['premium','business'].includes(plan) ? [plan] : ['basic','premium','business'];
          return cards.some(c =>
            ok.includes(String(c.status || '')) &&
            matchPlan.includes(String(c.subscription_plan || '').toLowerCase())
          );
        }
        return false;
      };
    },

    /**
     * Resolve PLAN yang akan digunakan untuk navigation + Profile Builder path selection.
     * Priority:
     *   1. Jika user ADA subscription active → guna user.subscription_plan
     *   2. Jika user TAKDE sub active tapi ADA NfcCard status eligible → guna plan kad pertama yang eligible
     *   3. Fallback user.subscription_plan atau "free"
     */
    resolvedSubscriptionPlan() {
      if (this.user?.subscription_active === true) {
        return String(this.user.subscription_plan || 'free').toLowerCase();
      }
      const ok = this.eligibleBuilderStatuses;
      const cards = this.user?.nfc_cards ?? this.user?.nfcCards;
      if (Array.isArray(cards) && cards.length > 0) {
        const eligible = cards.find(c => ok.includes(String(c.status || '')));
        if (eligible?.subscription_plan) return String(eligible.subscription_plan).toLowerCase();
      }
      return String(this.user?.subscription_plan || 'free').toLowerCase();
    },
  },

  actions: {
    /**
     * Login user
     */
    async login(credentials) {
      try {
        console.log("Auth store: Making login request");
        const response = await callApi("post", "/login", credentials);
        console.log("Auth store: Login response received:", response);

        if (response.success) {
          console.log("Auth store: Login successful, setting user data");
          this.token = response.token;
          this.user = response.user;
          this.isAuthenticated = true;

          // Use cookie instead of localStorage
          const tokenCookie = useCookie("auth-token", {
            httpOnly: false,
            secure: process.env.NODE_ENV === "production",
            sameSite: "lax",
            maxAge: 60 * 60 * 24 * 7, // 7 days
          });
          tokenCookie.value = response.token;
          console.log("Auth store: Token saved to cookie");

          return response;
        } else {
          console.log("Auth store: Login failed - no success flag");
          throw new Error(response.message || "Login failed");
        }
      } catch (error) {
        console.error("Auth store: Login error:", error);
        throw error;
      }
    },

    /**
     * Register new user
     */
    async register(data) {
      try {
        const response = await callApi("post", "/register", data);

        if (response.success) {
          this.token = response.token;
          this.user = response.user;
          this.isAuthenticated = true;

          const tokenCookie = useCookie("auth-token");
          tokenCookie.value = response.token;

          return response;
        } else {
          throw new Error(response.message || "Registration failed");
        }
      } catch (error) {
        console.error("Registration error:", error);
        throw error;
      }
    },

    /**
     * Logout user
     */
    async logout() {
      try {
        const nuxtApp = useNuxtApp();
        if (nuxtApp.$api) {
          await nuxtApp.$api.post("/logout");
        }
        console.log("Auth store: Logout successful");
      } catch (error) {
        console.error("Auth store: Logout API error:", error);
      } finally {
        this.clearAuth();
      }
    },

    /**
     * Fetch user profile
     */
    async fetchProfile() {
      try {
        const response = await callApi("get", "/me");

        if (response.success) {
          this.user = response.user;
          this.isAuthenticated = !!this.user;
        }
      } catch (error) {
        console.error("Fetch profile error:", error);
        throw error;
      }
    },

    /**
     * Request password reset
     */
    async requestPasswordReset(email) {
      try {
        console.log("Auth store: Requesting password reset for:", email);

        const response = await callApi("post", "/auth/password-reset-request", {
          email,
        });

        console.log("Auth store: Password reset request response:", response);
        return response;
      } catch (error) {
        console.error("Auth store: Password reset request error:", error);
        throw this.handleError(error);
      }
    },

    /**
     * Reset password with token
     */
    async resetPassword(data) {
      try {
        console.log("Auth store: Resetting password with token");

        const response = await callApi("post", "/auth/password-reset", {
          token: data.token,
          password: data.password,
          password_confirmation: data.password_confirmation,
        });

        console.log("Auth store: Password reset response:", response);
        return response;
      } catch (error) {
        console.error("Auth store: Password reset error:", error);
        throw this.handleError(error);
      }
    },

    /**
     * Verify 2FA code
     */
    async verify2FA(code) {
      try {
        const response = await callApi("post", "/verify-2fa", { code });

        if (response.success) {
          this.token = response.token;
          this.user = response.user;
          this.isAuthenticated = true;

          const tokenCookie = useCookie("auth-token", {
            httpOnly: false,
            secure: process.env.NODE_ENV === "production",
            sameSite: "lax",
            maxAge: 60 * 60 * 24 * 7,
          });
          tokenCookie.value = response.token;

          return response;
        } else {
          throw new Error(response.message || "2FA verification failed");
        }
      } catch (error) {
        console.error("2FA verification error:", error);
        throw error;
      }
    },

    /**
     * Check if current user is admin
     */
    isAdmin() {
      return this.user?.is_admin === true;
    },

    /**
     * Check if current user is super admin
     */
    isSuperAdmin() {
      return (
        this.user?.is_admin === true && this.user?.admin_role === "super_admin"
      );
    },

    /**
     * Get redirect path based on user's subscription plan
     */
    getRedirectPathByPlan() {
      if (!this.user) {
        return "/UserDashboard/";
      }

      // Admin users go to admin dashboard
      if (this.isAdmin()) {
        return "/AdminManagement/";
      }

      // All regular users (Free, Basic, Premium, Business) go to their main dashboard first
      return "/UserDashboard/";
    },

    /**
     * Clear authentication state
     */
    clearAuth() {
      this.user = null;
      this.token = null;
      this.isAuthenticated = false;

      const tokenCookie = useCookie("auth-token");
      tokenCookie.value = null;

      // NOTE: Navigation handled by route middleware (auth/admin) — avoid
      // double navigateTo calls that cause transition race conditions and
      // "insertBefore null" runtime DOM errors.
    },

    /**
     * Initialize auth state from cookie
     */
    async initAuth() {
      // Only run on client side
      if (process.server) return;

      const tokenCookie = useCookie("auth-token");
      console.log(
        "🔵 initAuth called, token:",
        tokenCookie.value ? "exists" : "missing"
      );

      if (tokenCookie.value) {
        try {
          console.log("🔄 Fetching user profile...");
          await this.fetchProfile();
          this.token = tokenCookie.value;
          this.isAuthenticated = true;
          console.log("✅ Auth initialized successfully");
        } catch (error) {
          console.error("❌ Failed to restore auth state:", error);
          console.error("Error details:", error.response || error.message);
          this.clearAuth();
          throw error; // Re-throw so callback page can catch it
        }
      } else {
        console.log("❌ No token cookie found");
      }
    },

    /**
     * Handle API errors consistently
     */
    handleError(error) {
      if (error.response) {
        const status = error.response.status;
        const data = error.response._data || error.response.data;

        if (status === 422 && data?.errors) {
          const formattedError = new Error(data.message || "Validation failed");
          formattedError.response = error.response;
          formattedError.validationErrors = data.errors;
          return formattedError;
        }

        const formattedError = new Error(
          data?.message || error.message || "An error occurred"
        );
        formattedError.response = error.response;
        formattedError.data = data;
        return formattedError;
      }

      return error;
    },
  },
});
