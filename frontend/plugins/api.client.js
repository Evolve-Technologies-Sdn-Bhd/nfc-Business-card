// plugins/api.client.js
import axios from "axios";

export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig();

  // Create axios instance
  const api = axios.create({
    baseURL: config.public.apiBaseUrl || "http://localhost:8000/api",
    timeout: 30000,
    headers: {
      Accept: "application/json",
      "X-Requested-With": "XMLHttpRequest",
    },
    withCredentials: false, // Changed to false for better CORS compatibility
  });

  // Request interceptor
  api.interceptors.request.use(
    (config) => {
      console.log("API Request:", config.method?.toUpperCase(), config.url);

      // Use cookie instead of localStorage for better SSR support
      const token = useCookie("auth-token").value;
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }

      // Set Content-Type only if not already set (important for multipart/form-data)
      if (!config.headers["Content-Type"]) {
        config.headers["Content-Type"] = "application/json";
      }

      // If Content-Type is multipart/form-data, let the browser set the boundary
      if (config.headers["Content-Type"] === "multipart/form-data") {
        delete config.headers["Content-Type"];
      }

      return config;
    },
    (error) => {
      console.error("API Request Error:", error);
      return Promise.reject(error);
    }
  );

  // Response interceptor
  api.interceptors.response.use(
    (response) => {
      console.log("API Response:", response.status, response.config.url);
      // Return the data portion for cleaner API calls
      return response.data;
    },
    async (error) => {
      console.error("API Response Error:", error);
      console.error("Error Details:", {
        message: error.message,
        status: error.response?.status,
        data: error.response?.data,
        config: error.config,
      });

      const originalRequest = error.config;

      // Handle 401 Unauthorized
      if (error.response?.status === 401 && !originalRequest._retry) {
        originalRequest._retry = true;

        const authStore = useAuthStore();
        authStore.clearAuth(); // Clear auth state
        await navigateTo("/UserAccount/login");
        return Promise.reject(error);
      }

      // Handle 403 Forbidden
      if (error.response?.status === 403) {
        const { $toast } = nuxtApp;
        $toast.error(
          "Access denied. You do not have permission to perform this action."
        );
      }

      // Handle 404 Not Found
      if (error.response?.status === 404) {
        console.error("Resource not found:", error.response.config.url);
      }

      // Handle 422 Validation Error
      if (error.response?.status === 422) {
        const validationErrors = error.response.data.errors || {};
        error.validationErrors = validationErrors;
        error.data = error.response.data; // Add this for consistency
      }

      // Handle 429 Rate Limit
      if (error.response?.status === 429) {
        const { $toast } = nuxtApp;
        $toast.error("Too many requests. Please try again later.");
      }

      // Handle 500 Server Error
      if (error.response?.status >= 500) {
        const { $toast } = nuxtApp;
        $toast.error("Server error. Please try again later.");
      }

      // Handle network errors
      if (!error.response) {
        console.error("Network Error - No response received");
        const { $toast } = nuxtApp;
        $toast.error(
          "Network error. Please check your connection and try again."
        );
      }

      // Add response data to error for easier access
      if (error.response) {
        error.status = error.response.status;
        error.data = error.response.data;
      }

      return Promise.reject(error);
    }
  );

  // Provide api instance
  nuxtApp.provide("api", api);
});
