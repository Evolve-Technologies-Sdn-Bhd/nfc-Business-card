// https://nuxt.com/docs/api/configuration/nuxt-config
// nuxt.config.ts
export default defineNuxtConfig({
  compatibilityDate: "2025-07-07",
  devtools: {
    enabled: process.env.NODE_ENV !== "production",
    timeline: {
      enabled: process.env.NODE_ENV !== "production",
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
      apiBaseUrl:
        (process.env.NUXT_PUBLIC_API_BASE_URL ? process.env.NUXT_PUBLIC_API_BASE_URL.replace(/[\u0060\u00B4\u2018\u2019\u0027\u0022]/g, "") : "") ||
        "https://nfcgo.clbgroups.com/api",
      appUrl:
        (process.env.NUXT_PUBLIC_APP_URL ? process.env.NUXT_PUBLIC_APP_URL.replace(/[\u0060\u00B4\u2018\u2019\u0027\u0022]/g, "") : "") ||
        "https://nfcgo.clbgroups.com",
      googleClientId: process.env.NUXT_PUBLIC_GOOGLE_CLIENT_ID || "",
      appleClientId: process.env.NUXT_PUBLIC_APPLE_CLIENT_ID || "",
      stripePublishableKey: process.env.NUXT_PUBLIC_STRIPE_PUBLISHABLE_KEY || "",
      maxFileSize: process.env.NUXT_PUBLIC_MAX_FILE_SIZE || "5242880",
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

  vite: {
    vue: {
      customElement: true,
    },
    optimizeDeps: {
      include: ["vue", "vue-router"],
    },
    server: {
      hmr: {
        // Use 'ws' by default, but allow overriding for HTTPS tunneling
        protocol: process.env.NUXT_PUBLIC_APP_URL?.startsWith('https') ? 'wss' : 'ws',
      },
      // Allow all hosts (useful for tunneling/LAN access)
      allowedHosts: true,
    },
  },

  // SSR Configuration - Disabled for authentication-heavy SPA
  ssr: false,

  // Nitro configuration for better performance & static hosting fallback
  // CRITICAL for profile pretty URL deep-linking on aaPanel Nginx:
  // The static site is composed of a single index.html (SPA). For deep links
  // like /profile/nicole-tan/1/userid-87, the webserver MUST serve index.html
  // for any unknown path, then Vue Router does URL resolution client-side.
  //   • `generate.fallback = "404.html"` → emits a 404.html copy of index.html
  //     so aaPanel Nginx rule "error_page 404 /404.html" keeps URL in address bar.
  //   • See deploy.txt notes: set Nginx root "try_files $uri $uri/ /index.html"
  //     if serving directly, OR map 404 handler to the generated 404.html.
  nitro: {
    compressPublicAssets: true,
    prerender: {
      routes: ['/', '/UserAccount/login', '/UserAccount/register'],
      crawlLinks: true,
      failOnError: false,
    },
  },
  generate: {
    // Make Nuxt generate a 404.html duplicate of index.html so static hosts that
    // don't have try_files rule can still do client-side routing on 404 fallback.
    fallback: '404.html',
  },
  // 2026-09-25: dev server port was on 3001 — confusing because Laravel also
  // runs on 8000/3001 on some setups and users expected the API on 3001. Move
  // frontend dev server to 3000. If 3000 is occupied, Nuxt auto-increments to
  // 3002 etc and notifies user on the terminal instead of serving "API-like 404".
  devServer: {
    host: "0.0.0.0",
    port: 3000,
  },

  // Router configuration to handle trailing slashes
  router: {
    options: {
      strict: false, // This makes /path and /path/ equivalent
    },
  },

  // Hooks to add redirect rules for trailing slashes
  hooks: {
    'pages:extend'(pages) {
      // For each index.vue page, ensure both /path and /path/ work
      const indexPages = pages.filter(page => page.name?.endsWith('-index') || page.file?.endsWith('index.vue'));
      
      indexPages.forEach(page => {
        if (page.path && page.path !== '/') {
          // Remove trailing slash from the path for consistency
          const pathWithoutSlash = page.path.replace(/\/$/, '');
          const pathWithSlash = pathWithoutSlash + '/';
          
          // Update the page path to not have trailing slash
          page.path = pathWithoutSlash;
          
          // Add an alias for the version with trailing slash
          if (!page.alias) {
            page.alias = [];
          } else if (typeof page.alias === 'string') {
            page.alias = [page.alias];
          }
          page.alias.push(pathWithSlash);
        }
      });
    },
  },
});
