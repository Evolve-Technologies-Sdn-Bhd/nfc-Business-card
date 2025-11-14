// https://nuxt.com/docs/api/configuration/nuxt-config
// nuxt.config.ts
export default defineNuxtConfig({
  compatibilityDate: "2025-07-07",
  devtools: {
    enabled: true,
    timeline: {
      enabled: true,
    },
  },

  // CSS Framework
  css: ["~/assets/css/main.css"],

  // Modules
  modules: [
    "@nuxtjs/tailwindcss",
    "@pinia/nuxt",
    "@nuxtjs/google-fonts",
    "@vueuse/nuxt",
    "@nuxt/image",
    "nuxt-icon",
  ],

  // Icon configuration
  icon: {
    size: "24px",
    class: "icon",
  },

  // Google Fonts
  googleFonts: {
    families: {
      Inter: [300, 400, 500, 600, 700],
      Poppins: [300, 400, 500, 600, 700],
    },
  },

  // Runtime Config
  runtimeConfig: {
    public: {
      // Updated to use NUXT_PUBLIC_ prefix
      apiBaseUrl:
        process.env.NUXT_PUBLIC_API_BASE_URL || "http://localhost:8000/api",
      appUrl: process.env.NUXT_PUBLIC_APP_URL || "http://localhost:3002",
      googleClientId: process.env.NUXT_PUBLIC_GOOGLE_CLIENT_ID || "",
      appleClientId: process.env.NUXT_PUBLIC_APPLE_CLIENT_ID || "",
      maxFileSize: process.env.NUXT_PUBLIC_MAX_FILE_SIZE || "5242880", // 5MB
      allowedFileTypes:
        process.env.NUXT_PUBLIC_ALLOWED_FILE_TYPES ||
        "image/jpeg,image/png,image/gif,image/webp",
    },
  },

  // App Config
  app: {
    head: {
      title: "NFC Business Card - Digital Professional Networking",
      meta: [
        { charset: "utf-8" },
        {
          name: "viewport",
          content:
            "width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes",
        },
        {
          name: "description",
          content:
            "Create smart digital business cards with NFC technology. Tap to share your professional profile instantly.",
        },
      ],
      link: [{ rel: "icon", type: "image/x-icon", href: "/favicon.ico" }],
    },
    pageTransition: { name: "page", mode: "out-in" },
    layoutTransition: { name: "layout", mode: "out-in" },
  },

  // Tailwind CSS
  tailwindcss: {
    cssPath: "~/assets/css/main.css",
    configPath: "tailwind.config.js",
  },

  // Development configuration
  devServer: {
    host: "0.0.0.0", // Bind to all network interfaces (accessible from localhost and LAN/mobile)
    port: 3000,
  },

  vite: {
    vue: {
      customElement: true,
    },
    optimizeDeps: {
      include: ["vue", "vue-router"],
    },
  },

  // SSR Configuration - Disabled for authentication-heavy SPA
  ssr: false,

  // Nitro configuration for better performance
  nitro: {
    compressPublicAssets: true,
  },
});
