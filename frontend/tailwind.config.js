// tailwind.config.js
module.exports = {
  content: [
    "./components/**/*.{js,ts,vue}",
    "./layouts/**/*.{js,ts,vue}",
    "./pages/**/*.{js,ts,vue}",
    "./composables/**/*.{js,ts}",
    "./plugins/**/*.{js,ts}",
    "./app.vue",
    "./nuxt.config.{js,ts}",
  ],
  theme: {
    extend: {
      fontFamily: {
        inter: ["Inter", "sans-serif"],
        poppins: ["Poppins", "sans-serif"],
      },
      /**
       * NFCGo Typography Scale — single source of truth.
       * Every `text-*` utility in the app resolves to one of these tokens,
       * each paired with an intentional line-height (and where relevant a
       * weight / letter-spacing) so type is consistent everywhere.
       *
       *  Display XL 48  | Display LG 40 | H1 32 | H2 28 | H3 24 | H4 20 | H5 18
       *  Body LG 16     | Body MD 14    | Body SM 13 | Caption 12 | Micro 11
       */
      fontSize: {
        /* --- Core scale (refined line-heights, keys preserved for existing usage) --- */
        xs: ["0.75rem", { lineHeight: "1.05rem" }], // 12px  → Caption
        sm: ["0.875rem", { lineHeight: "1.35rem" }], // 14px  → Body MD
        base: ["1rem", { lineHeight: "1.6rem" }], // 16px  → Body LG
        lg: ["1.125rem", { lineHeight: "1.75rem" }], // 18px  → H5
        xl: ["1.25rem", { lineHeight: "1.8rem" }], // 20px  → H4
        "2xl": ["1.5rem", { lineHeight: "1.95rem" }], // 24px  → H3
        "3xl": ["1.75rem", { lineHeight: "2.15rem" }], // 28px  → H2
        "4xl": ["2rem", { lineHeight: "2.4rem" }], // 32px  → H1
        "5xl": ["2.5rem", { lineHeight: "2.85rem" }], // 40px  → Display LG
        "6xl": ["3rem", { lineHeight: "3.25rem" }], // 48px  → Display XL

        /* --- Semantic scale (preferred for new markup) --- */
        "display-xl": [
          "clamp(2.5rem, 1.9rem + 2.4vw, 3rem)",
          { lineHeight: "1.05", letterSpacing: "-0.03em", fontWeight: "800" },
        ],
        "display-lg": [
          "clamp(2.125rem, 1.75rem + 1.6vw, 2.5rem)",
          { lineHeight: "1.1", letterSpacing: "-0.025em", fontWeight: "700" },
        ],
        h1: [
          "clamp(1.75rem, 1.55rem + 0.8vw, 2rem)",
          { lineHeight: "1.2", letterSpacing: "-0.02em", fontWeight: "700" },
        ],
        h2: [
          "clamp(1.5rem, 1.4rem + 0.5vw, 1.75rem)",
          { lineHeight: "1.25", letterSpacing: "-0.018em", fontWeight: "700" },
        ],
        h3: ["1.5rem", { lineHeight: "1.35", letterSpacing: "-0.012em", fontWeight: "700" }],
        h4: ["1.25rem", { lineHeight: "1.4", letterSpacing: "-0.008em", fontWeight: "600" }],
        h5: ["1.125rem", { lineHeight: "1.45", fontWeight: "600" }],
        "body-lg": ["1rem", { lineHeight: "1.6" }],
        body: ["0.875rem", { lineHeight: "1.55" }],
        "body-sm": ["0.8125rem", { lineHeight: "1.5" }],
        caption: ["0.75rem", { lineHeight: "1.4" }],
        micro: ["0.6875rem", { lineHeight: "1.35", letterSpacing: "0.01em" }],

        /* --- UI text roles --- */
        label: ["0.8125rem", { lineHeight: "1.35", fontWeight: "600" }],
        button: ["0.875rem", { lineHeight: "1.25", fontWeight: "600" }],
        input: ["0.875rem", { lineHeight: "1.5" }],
        helper: ["0.8125rem", { lineHeight: "1.4" }],
        error: ["0.8125rem", { lineHeight: "1.4", fontWeight: "500" }],
        navigation: ["0.875rem", { lineHeight: "1.4", fontWeight: "500" }],
        "table-header": [
          "0.75rem",
          { lineHeight: "1.35", fontWeight: "600", letterSpacing: "0.04em" },
        ],
        "table-body": ["0.875rem", { lineHeight: "1.5" }],
        badge: ["0.6875rem", { lineHeight: "1.25", fontWeight: "600", letterSpacing: "0.02em" }],
      },
      colors: {
        // Primary = INK (near-black navy) — CSS variables keep it theme-switchable
        primary: {
          50: "var(--theme-primary-50, #f4f5f7)",
          100: "var(--theme-primary-100, #e6e8ec)",
          200: "var(--theme-primary-200, #ccd1d9)",
          300: "var(--theme-primary-300, #a5adba)",
          400: "var(--theme-primary-400, #6f7a8b)",
          500: "var(--theme-primary-500, #414c5e)",
          600: "var(--theme-primary-600, #1d2839)",
          700: "var(--theme-primary-700, #141d2b)",
          800: "var(--theme-primary-800, #0f1621)",
          900: "var(--theme-primary-900, #0b1017)",
        },
        // Secondary = PAPER (warm off-white neutral) — CSS variables keep it theme-switchable
        secondary: {
          50: "var(--theme-secondary-50, #faf9f6)",
          100: "var(--theme-secondary-100, #f3f1ea)",
          200: "var(--theme-secondary-200, #e6e2d6)",
          300: "var(--theme-secondary-300, #cdc7b7)",
          400: "var(--theme-secondary-400, #a8a190)",
          500: "var(--theme-secondary-500, #7f7763)",
          600: "var(--theme-secondary-600, #5c5548)",
          700: "var(--theme-secondary-700, #454037)",
          800: "var(--theme-secondary-800, #2e2a24)",
          900: "var(--theme-secondary-900, #1c1a15)",
        },
        success: {
          50: "#f0fdf4",
          100: "#dcfce7",
          200: "#bbf7d0",
          300: "#86efac",
          400: "#4ade80",
          500: "#22c55e",
          600: "#16a34a",
          700: "#15803d",
          800: "#166534",
          900: "#14532d",
        },
        warning: {
          50: "#fffbeb",
          100: "#fef3c7",
          200: "#fde68a",
          300: "#fcd34d",
          400: "#fbbf24",
          500: "#f59e0b",
          600: "#d97706",
          700: "#b45309",
          800: "#92400e",
          900: "#78350f",
        },
        error: {
          50: "#fef2f2",
          100: "#fee2e2",
          200: "#fecaca",
          300: "#fca5a5",
          400: "#f87171",
          500: "#ef4444",
          600: "#dc2626",
          700: "#b91c1c",
          800: "#991b1b",
          900: "#7f1d1d",
        },
        info: {
          50: "#eff6ff",
          100: "#dbeafe",
          200: "#bfdbfe",
          300: "#93c5fd",
          400: "#60a5fa",
          500: "#3b82f6",
          600: "#2563eb",
          700: "#1d4ed8",
          800: "#1e40af",
          900: "#1e3a8a",
        },
        // Accent = BRASS (burnished gold) — brand highlight colour.
        // CSS variables keep it theme-switchable (see composables/useTheme.js).
        accent: {
          50: "var(--theme-accent-50, #fbf8f1)",
          100: "var(--theme-accent-100, #f5eedd)",
          200: "var(--theme-accent-200, #e9d9b5)",
          300: "var(--theme-accent-300, #dcc28a)",
          400: "var(--theme-accent-400, #cda863)",
          500: "var(--theme-accent-500, #c2a068)",
          600: "var(--theme-accent-600, #a9854d)",
          700: "var(--theme-accent-700, #8c6b3b)",
          800: "var(--theme-accent-800, #6e5330)",
          900: "var(--theme-accent-900, #523e26)",
        },
      },
      animation: {
        "fade-in": "fadeIn 0.5s ease-in-out",
        "fade-in-up": "fadeInUp 0.5s ease-in-out",
        "fade-in-down": "fadeInDown 0.5s ease-in-out",
        "slide-in-right": "slideInRight 0.3s ease-in-out",
        "slide-in-left": "slideInLeft 0.3s ease-in-out",
        "bounce-in": "bounceIn 0.6s ease-in-out",
        "pulse-slow": "pulse 3s infinite",
        "nfc-text-shimmer": "nfcTextShimmer 3.2s cubic-bezier(0.4, 0, 0.2, 1) infinite",
        "nfc-fade-up": "nfcFadeUp 0.55s cubic-bezier(0.22, 1, 0.36, 1) both",
        "nfc-accent-line": "nfcAccentLine 2.6s cubic-bezier(0.4, 0, 0.2, 1) infinite",
        "dot-bounce-1": "dotBounce 1.4s ease-in-out infinite",
        "dot-bounce-2": "dotBounce 1.4s ease-in-out 0.16s infinite",
        "dot-bounce-3": "dotBounce 1.4s ease-in-out 0.32s infinite",
        "card-skeleton-scan": "cardSkeletonScan 2.8s cubic-bezier(0.22, 1, 0.36, 1) infinite",
      },
      keyframes: {
        fadeIn: {
          "0%": { opacity: "0" },
          "100%": { opacity: "1" },
        },
        fadeInUp: {
          "0%": { opacity: "0", transform: "translateY(20px)" },
          "100%": { opacity: "1", transform: "translateY(0)" },
        },
        fadeInDown: {
          "0%": { opacity: "0", transform: "translateY(-20px)" },
          "100%": { opacity: "1", transform: "translateY(0)" },
        },
        slideInRight: {
          "0%": { transform: "translateX(100%)" },
          "100%": { transform: "translateX(0)" },
        },
        slideInLeft: {
          "0%": { transform: "translateX(-100%)" },
          "100%": { transform: "translateX(0)" },
        },
        bounceIn: {
          "0%": { transform: "scale(0.3)", opacity: "0" },
          "50%": { transform: "scale(1.05)" },
          "70%": { transform: "scale(0.9)" },
          "100%": { transform: "scale(1)", opacity: "1" },
        },
        nfcTextShimmer: {
          "0%, 100%": {
            backgroundPosition: "-120% 0",
          },
          "50%": {
            backgroundPosition: "120% 0",
          },
        },
        nfcFadeUp: {
          "0%": {
            opacity: "0",
            transform: "translateY(14px)",
          },
          "100%": {
            opacity: "1",
            transform: "translateY(0)",
          },
        },
        nfcAccentLine: {
          "0%, 100%": {
            transform: "scaleX(0.25)",
            opacity: "0.45",
            transformOrigin: "center",
          },
          "50%": {
            transform: "scaleX(1)",
            opacity: "1",
            transformOrigin: "center",
          },
        },
        dotBounce: {
          "0%, 80%, 100%": {
            transform: "scale(0.55)",
            opacity: "0.45",
          },
          "40%": {
            transform: "scale(1)",
            opacity: "1",
          },
        },
        cardSkeletonScan: {
          "0%": {
            transform: "translateX(-120%)",
            opacity: "0",
          },
          "12%": {
            opacity: "1",
          },
          "88%": {
            opacity: "1",
          },
          "100%": {
            transform: "translateX(120%)",
            opacity: "0",
          },
        },
      },
      boxShadow: {
        card: "0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)",
        "card-hover":
          "0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)",
        "inner-light": "inset 0 2px 4px 0 rgba(0, 0, 0, 0.06)",
      },
      backdropBlur: {
        xs: "2px",
      },
    },
  },
  // Card status badge classes are sent by the API (NfcCard::statusBadgeClasses),
  // which Tailwind cannot scan — keep them in the build.
  safelist: [
    { pattern: /^(bg|text)-(success|warning|error|info|secondary)-(50|100|700|800)$/ },
  ],
  plugins: [],
};
