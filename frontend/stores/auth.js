// stores/auth.js
export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null,
    token: null,
    isAuthenticated: false,
  }),

  actions: {
    /**
     * Login user
     */
    async login(credentials) {
      try {
        const { $api } = useNuxtApp();
        console.log("Auth store: Making login request");
        const response = await $api.post("/login", credentials);
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
        const { $api } = useNuxtApp();
        const response = await $api.post("/register", data);

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
        const { $api } = useNuxtApp();
        await $api.post("/logout");
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
        const { $api } = useNuxtApp();
        const response = await $api.get("/me");

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
        const { $api } = useNuxtApp();
        console.log("Auth store: Requesting password reset for:", email);

        const response = await $api.post("/password-reset-request", {
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
        const { $api } = useNuxtApp();
        console.log("Auth store: Resetting password with token");

        const response = await $api.post("/password-reset", {
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
        const { $api } = useNuxtApp();
        const response = await $api.post("/verify-2fa", { code });

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
     * Clear authentication state
     */
    clearAuth() {
      this.user = null;
      this.token = null;
      this.isAuthenticated = false;

      const tokenCookie = useCookie("auth-token");
      tokenCookie.value = null;

      // Only navigate if on client side
      if (process.client) {
        navigateTo("/UserAccount/login");
      }
    },

    /**
     * Initialize auth state from cookie
     */
    async initAuth() {
      // Only run on client side
      if (process.server) return;
      
      const tokenCookie = useCookie("auth-token");
      console.log('🔵 initAuth called, token:', tokenCookie.value ? 'exists' : 'missing');
      
      if (tokenCookie.value) {
        try {
          console.log('🔄 Fetching user profile...');
          await this.fetchProfile();
          this.token = tokenCookie.value;
          this.isAuthenticated = true;
          console.log('✅ Auth initialized successfully');
        } catch (error) {
          console.error("❌ Failed to restore auth state:", error);
          console.error("Error details:", error.response || error.message);
          this.clearAuth();
          throw error; // Re-throw so callback page can catch it
        }
      } else {
        console.log('❌ No token cookie found');
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
