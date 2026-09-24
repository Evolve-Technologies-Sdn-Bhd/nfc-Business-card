// composables/useTheme.js
// Global theme switcher composable with 5 themes and localStorage persistence
// Icons use Heroicons name paths (SVG) — EMOJI-FREE per UI guidelines

const THEME_STORAGE_KEY = 'nfcgo-color-theme';
const DEFAULT_THEME = 'zoraPro';

// Theme definitions with complete color palettes
// NOTE: Secondary colors can change between themes now since we support warm slate vs cool slate
const themes = {
  zoraPro: {
    name: 'Zora Pro',
    description: 'Matte Deep Navy + Matte Teal. Mature-eyes friendly, premium professional tone.',
    icon: 'heroicons:squares-2x2',
    colors: {
      // Primary — Matte Deep Navy (non-glare, non-neon)
      primary50: '#F4F6FA',
      primary100: '#E5E9F1',
      primary200: '#C9D0DE',
      primary300: '#9FAAC1',
      primary400: '#6F7EA0',
      primary500: '#4F5D82',
      primary600: '#3D496A',
      primary700: '#323B56',
      primary800: '#2A3248',
      primary900: '#23293B',
      // Accents — Matte Teal (calm) + Burnished Gold (luxury)
      accent: '#5D8C87',
      accentSecondary: '#B8956A',
      // Secondary — Warm Slate Neutral (matte, not OLED-black)
      secondary50: '#FAFAF7',
      secondary100: '#F4F2EC',
      secondary200: '#E6E2D8',
      secondary300: '#CDC7B9',
      secondary400: '#A8A190',
      secondary500: '#7F7763',
      secondary600: '#5C5548',
      secondary700: '#454037',
      secondary800: '#2E2A24',
      secondary900: '#1C1A15',
    }
  },
  holographicPurple: {
    name: 'Holographic Purple',
    description: 'Futuristic purple with holographic accents (original default)',
    icon: 'heroicons:sparkles',
    colors: {
      primary50: '#F5F3FF',
      primary100: '#EDE9FE',
      primary200: '#DDD6FE',
      primary300: '#C4B5FD',
      primary400: '#A78BFA',
      primary500: '#8B5CF6',
      primary600: '#7C3AED',
      primary700: '#6D28D9',
      primary800: '#5B21B6',
      primary900: '#4C1D95',
      accent: '#22D3EE',
      accentSecondary: '#FBBF24',
      secondary50: '#F8FAFC',
      secondary100: '#F1F5F9',
      secondary200: '#E2E8F0',
      secondary300: '#CBD5E1',
      secondary400: '#94A3B8',
      secondary500: '#64748B',
      secondary600: '#475569',
      secondary700: '#334155',
      secondary800: '#1E293B',
      secondary900: '#0F172A',
    }
  },
  landingPage: {
    name: 'Corporate Blue',
    description: 'Original blue theme for brand landing consistency',
    icon: 'heroicons:building-office-2',
    colors: {
      primary50: '#EFF6FF',
      primary100: '#DBEAFE',
      primary200: '#BFDBFE',
      primary300: '#93C5FD',
      primary400: '#60A5FA',
      primary500: '#3B82F6',
      primary600: '#2563EB',
      primary700: '#1D4ED8',
      primary800: '#1E40AF',
      primary900: '#1E3A8A',
      accent: '#3B82F6',
      accentSecondary: '#10B981',
      secondary50: '#F8FAFC',
      secondary100: '#F1F5F9',
      secondary200: '#E2E8F0',
      secondary300: '#CBD5E1',
      secondary400: '#94A3B8',
      secondary500: '#64748B',
      secondary600: '#475569',
      secondary700: '#334155',
      secondary800: '#1E293B',
      secondary900: '#0F172A',
    }
  },
  cyberExecutive: {
    name: 'Cyber-Executive',
    description: 'Deep space navy with cool electric cyan accents',
    icon: 'heroicons:bolt',
    colors: {
      primary50: '#ECFEFF',
      primary100: '#CFFAFE',
      primary200: '#A5F3FC',
      primary300: '#67E8F9',
      primary400: '#22D3EE',
      primary500: '#06B6D4',
      primary600: '#0891B2',
      primary700: '#0E7490',
      primary800: '#155E75',
      primary900: '#164E63',
      accent: '#22D3EE',
      accentSecondary: '#3B82F6',
      secondary50: '#F8FAFC',
      secondary100: '#F1F5F9',
      secondary200: '#E2E8F0',
      secondary300: '#CBD5E1',
      secondary400: '#94A3B8',
      secondary500: '#64748B',
      secondary600: '#475569',
      secondary700: '#334155',
      secondary800: '#1E293B',
      secondary900: '#0F172A',
    }
  },
  obsidianGlass: {
    name: 'Obsidian Glass',
    description: 'Warm dark palette with burnished gold accents',
    icon: 'heroicons:gem',
    colors: {
      primary50: '#FFFBEB',
      primary100: '#FEF3C7',
      primary200: '#FDE68A',
      primary300: '#FCD34D',
      primary400: '#FBBF24',
      primary500: '#F59E0B',
      primary600: '#D97706',
      primary700: '#B45309',
      primary800: '#92400E',
      primary900: '#78350F',
      accent: '#D4AF37',
      accentSecondary: '#C0C0C0',
      secondary50: '#F8FAFC',
      secondary100: '#F1F5F9',
      secondary200: '#E2E8F0',
      secondary300: '#CBD5E1',
      secondary400: '#94A3B8',
      secondary500: '#64748B',
      secondary600: '#475569',
      secondary700: '#334155',
      secondary800: '#1E293B',
      secondary900: '#0F172A',
    }
  }
};

// Shared state (singleton pattern for SSR compatibility)
let currentTheme = null;
let themeRef = null;

export function useTheme() {
  // Initialize reactive ref only once
  if (!themeRef) {
    themeRef = ref(DEFAULT_THEME);
  }

  // Apply theme CSS variables to document root
  const applyTheme = (themeName) => {
    if (typeof document === 'undefined') return;
    
    const theme = themes[themeName];
    if (!theme) return;

    const root = document.documentElement;
    const colors = theme.colors;

    // Set CSS custom properties
    root.style.setProperty('--theme-primary-50', colors.primary50);
    root.style.setProperty('--theme-primary-100', colors.primary100);
    root.style.setProperty('--theme-primary-200', colors.primary200);
    root.style.setProperty('--theme-primary-300', colors.primary300);
    root.style.setProperty('--theme-primary-400', colors.primary400);
    root.style.setProperty('--theme-primary-500', colors.primary500);
    root.style.setProperty('--theme-primary-600', colors.primary600);
    root.style.setProperty('--theme-primary-700', colors.primary700);
    root.style.setProperty('--theme-primary-800', colors.primary800);
    root.style.setProperty('--theme-primary-900', colors.primary900);
    root.style.setProperty('--theme-accent', colors.accent);
    root.style.setProperty('--theme-accent-secondary', colors.accentSecondary);
    root.style.setProperty('--theme-secondary-50', colors.secondary50);
    root.style.setProperty('--theme-secondary-100', colors.secondary100);
    root.style.setProperty('--theme-secondary-200', colors.secondary200);
    root.style.setProperty('--theme-secondary-300', colors.secondary300);
    root.style.setProperty('--theme-secondary-400', colors.secondary400);
    root.style.setProperty('--theme-secondary-500', colors.secondary500);
    root.style.setProperty('--theme-secondary-600', colors.secondary600);
    root.style.setProperty('--theme-secondary-700', colors.secondary700);
    root.style.setProperty('--theme-secondary-800', colors.secondary800);
    root.style.setProperty('--theme-secondary-900', colors.secondary900);

    // Also set data attribute for potential CSS selectors
    root.setAttribute('data-theme', themeName);
  };

  // Set theme and persist to localStorage
  const setTheme = (themeName) => {
    if (!themes[themeName]) {
      console.warn(`Theme "${themeName}" not found. Using default.`);
      themeName = DEFAULT_THEME;
    }
    
    themeRef.value = themeName;
    currentTheme = themeName;
    applyTheme(themeName);
    
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem(THEME_STORAGE_KEY, themeName);
    }
  };

  // Initialize theme from localStorage or default
  const initTheme = () => {
    let savedTheme = DEFAULT_THEME;
    
    if (typeof localStorage !== 'undefined') {
      savedTheme = localStorage.getItem(THEME_STORAGE_KEY) || DEFAULT_THEME;
    }
    
    // Validate saved theme
    if (!themes[savedTheme]) {
      savedTheme = DEFAULT_THEME;
    }
    
    themeRef.value = savedTheme;
    currentTheme = savedTheme;
    applyTheme(savedTheme);
  };

  // Get all available themes for the UI
  const getThemes = () => {
    return Object.entries(themes).map(([key, value]) => ({
      key,
      name: value.name,
      description: value.description,
      icon: value.icon,
      previewColor: value.colors.primary500
    }));
  };

  // Get current theme info
  const getCurrentThemeInfo = () => {
    const theme = themes[themeRef.value];
    return theme ? {
      key: themeRef.value,
      name: theme.name,
      description: theme.description,
      icon: theme.icon
    } : null;
  };

  return {
    currentTheme: themeRef,
    themes,
    setTheme,
    initTheme,
    getThemes,
    getCurrentThemeInfo
  };
}
