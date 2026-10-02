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
      colors: {
        // Primary colors - using CSS variables for dynamic theming
        primary: {
          50: "var(--theme-primary-50, #F5F3FF)",
          100: "var(--theme-primary-100, #EDE9FE)",
          200: "var(--theme-primary-200, #DDD6FE)",
          300: "var(--theme-primary-300, #C4B5FD)",
          400: "var(--theme-primary-400, #A78BFA)",
          500: "var(--theme-primary-500, #8B5CF6)",
          600: "var(--theme-primary-600, #7C3AED)",
          700: "var(--theme-primary-700, #6D28D9)",
          800: "var(--theme-primary-800, #5B21B6)",
          900: "var(--theme-primary-900, #4C1D95)",
        },
        // Secondary colors - using CSS variables for dynamic theming
        secondary: {
          50: "var(--theme-secondary-50, #F8FAFC)",
          100: "var(--theme-secondary-100, #F1F5F9)",
          200: "var(--theme-secondary-200, #E2E8F0)",
          300: "var(--theme-secondary-300, #CBD5E1)",
          400: "var(--theme-secondary-400, #94A3B8)",
          500: "var(--theme-secondary-500, #64748B)",
          600: "var(--theme-secondary-600, #475569)",
          700: "var(--theme-secondary-700, #334155)",
          800: "var(--theme-secondary-800, #1E293B)",
          900: "var(--theme-secondary-900, #0F172A)",
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
      },
      animation: {
        "fade-in": "fadeIn 0.5s ease-in-out",
        "fade-in-up": "fadeInUp 0.5s ease-in-out",
        "fade-in-down": "fadeInDown 0.5s ease-in-out",
        "slide-in-right": "slideInRight 0.3s ease-in-out",
        "slide-in-left": "slideInLeft 0.3s ease-in-out",
        "bounce-in": "bounceIn 0.6s ease-in-out",
        "pulse-slow": "pulse 3s infinite",
        "nfc-fan-l-1": "nfcFanWaveLeft 2.2s cubic-bezier(0.22, 1, 0.36, 1) infinite",
        "nfc-fan-l-2": "nfcFanWaveLeft 2.2s cubic-bezier(0.22, 1, 0.36, 1) 0.28s infinite",
        "nfc-fan-l-3": "nfcFanWaveLeft 2.2s cubic-bezier(0.22, 1, 0.36, 1) 0.56s infinite",
        "nfc-fan-r-1": "nfcFanWaveRight 2.2s cubic-bezier(0.22, 1, 0.36, 1) infinite",
        "nfc-fan-r-2": "nfcFanWaveRight 2.2s cubic-bezier(0.22, 1, 0.36, 1) 0.28s infinite",
        "nfc-fan-r-3": "nfcFanWaveRight 2.2s cubic-bezier(0.22, 1, 0.36, 1) 0.56s infinite",
        "nfc-chip-3d": "nfcChip3D 2.2s cubic-bezier(0.22, 1, 0.36, 1) infinite",
        "nfc-chip-3d-lg": "nfcChip3DLg 2.2s cubic-bezier(0.4, 0, 0.2, 1) infinite",
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
        nfcFanWaveLeft: {
          "0%": {
            transform: "translateX(14%) scale(0.86)",
            opacity: "0",
          },
          "32%": {
            opacity: "0.92",
          },
          "100%": {
            transform: "translateX(-22%) scale(1.08)",
            opacity: "0",
          },
        },
        nfcFanWaveRight: {
          "0%": {
            transform: "translateX(-14%) scale(0.86)",
            opacity: "0",
          },
          "32%": {
            opacity: "0.92",
          },
          "100%": {
            transform: "translateX(22%) scale(1.08)",
            opacity: "0",
          },
        },
        nfcChip3D: {
          "0%, 100%": {
            transform: "translateY(0) scale(1)",
          },
          "50%": {
            transform: "translateY(-1.5px) scale(1.025)",
          },
        },
        nfcChip3DLg: {
          "0%, 100%": {
            transform: "perspective(600px) rotateX(3.5deg) rotateY(-4deg) translateY(0) scale(1)",
            filter:
              "drop-shadow(0 8px 20px color-mix(in srgb, var(--theme-primary-800,#2c344e) 22%, transparent)) drop-shadow(0 2px 4px color-mix(in srgb, var(--theme-accent,#5d8c87) 28%, transparent))",
          },
          "50%": {
            transform: "perspective(600px) rotateX(3.5deg) rotateY(-4deg) translateY(-3px) scale(1.03)",
            filter:
              "drop-shadow(0 16px 32px color-mix(in srgb, var(--theme-primary-800,#2c344e) 28%, transparent)) drop-shadow(0 4px 9px color-mix(in srgb, var(--theme-accent,#5d8c87) 40%, transparent))",
          },
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
  plugins: [],
};
