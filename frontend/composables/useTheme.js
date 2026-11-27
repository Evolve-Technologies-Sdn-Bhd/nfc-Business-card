// composables/useTheme.js
// System-wide theme. The active theme is controlled by the ADMIN only
// (persisted server-side under system-settings/appearance) and applied to
// every visitor. End users cannot change the system theme — they only control
// the theme of their own public landing page.
//
// The chosen theme is cached in localStorage purely to paint the correct
// palette instantly on the next cold load (avoids a flash of the default).
// The legacy per-user key `nfcgo-color-theme` is deliberately ignored.
// Icons use Heroicons name paths (SVG) — EMOJI-FREE per UI guidelines

const SYSTEM_THEME_STORAGE_KEY = 'nfcgo-system-theme';
const DEFAULT_THEME = 'zoraPro';

// Theme definitions with complete color palettes
// NOTE: Secondary colors can change between themes now since we support warm slate vs cool slate
const themes = {
  zoraPro: {
    // Key kept as `zoraPro` so existing localStorage selections stay valid.
    name: 'Ink & Brass',
    description: 'The system default. Ink (near-black) + Brass (gold) + Paper (warm off-white) — three colours, AA contrast.',
    icon: 'heroicons:square-3-stack-3d',
    colors: {
      // Primary — INK (near-black navy)
      primary50: '#F4F5F7',
      primary100: '#E6E8EC',
      primary200: '#CCD1D9',
      primary300: '#A5ADBA',
      primary400: '#6F7A8B',
      primary500: '#414C5E',
      primary600: '#1D2839',
      primary700: '#141D2B',
      primary800: '#0F1621',
      primary900: '#0B1017',
      // Accent — BRASS (burnished gold)
      accent: '#C2A068',
      accentSecondary: '#A9854D',
      // Secondary — PAPER (warm off-white neutral)
      secondary50: '#FAF9F6',
      secondary100: '#F3F1EA',
      secondary200: '#E6E2D6',
      secondary300: '#CDC7B7',
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
    icon: 'heroicons:building-office',
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
    icon: 'heroicons:moon',
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
  },
  onyxPlatinum: {
    name: 'Onyx & Platinum',
    description: 'Cool onyx black with platinum-silver accents — a refined, monochrome executive look.',
    icon: 'heroicons:cube-transparent',
    colors: {
      // Primary — ONYX (cool near-black)
      primary50: '#F5F6F8',
      primary100: '#E7E9ED',
      primary200: '#CFD3DA',
      primary300: '#AAB0BB',
      primary400: '#7E8794',
      primary500: '#545C69',
      primary600: '#333A45',
      primary700: '#232932',
      primary800: '#171B21',
      primary900: '#0C0E12',
      // Accent — PLATINUM (cool silver)
      accent: '#C9CED6',
      accentSecondary: '#9AA3AE',
      // Secondary — cool slate neutral
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

// ---------------------------------------------------------------------------
// Accent scale helpers
// Every theme only declares a single `accent` (+ `accentSecondary`); we derive
// a full 50–900 ramp from it so `bg-accent-*` / `text-accent-*` utilities work
// for any palette, not just Ink & Brass.
// ---------------------------------------------------------------------------
const clampChannel = (v) => Math.max(0, Math.min(255, Math.round(v)));

const parseHex = (hex) => {
  const h = String(hex).replace('#', '').trim();
  const full = h.length === 3 ? h.split('').map((c) => c + c).join('') : h;
  return [
    parseInt(full.slice(0, 2), 16),
    parseInt(full.slice(2, 4), 16),
    parseInt(full.slice(4, 6), 16),
  ];
};

const toHex = (rgb) =>
  '#' + rgb.map((c) => clampChannel(c).toString(16).padStart(2, '0')).join('').toUpperCase();

// Mix a colour toward white (amount > 0) or black (amount < 0)
const mix = (hex, amount) => {
  const [r, g, b] = parseHex(hex);
  const target = amount >= 0 ? 255 : 0;
  const t = Math.abs(amount);
  return toHex([
    r + (target - r) * t,
    g + (target - g) * t,
    b + (target - b) * t,
  ]);
};

const buildAccentScale = (accent, accentSecondary) => ({
  accent50: mix(accent, 0.92),
  accent100: mix(accent, 0.84),
  accent200: mix(accent, 0.66),
  accent300: mix(accent, 0.46),
  accent400: mix(accent, 0.24),
  accent500: accent,
  accent600: accentSecondary || mix(accent, -0.18),
  accent700: mix(accent, -0.3),
  accent800: mix(accent, -0.46),
  accent900: mix(accent, -0.6),
});

// ---------------------------------------------------------------------------
// Design-system role tokens
// The Ink & Brass design (admin shell, user portal, public site, auth pages)
// is written against role tokens — --ink-*, --brass*, --paper*, --admin-* —
// not against the Tailwind palette. Setting them here makes the admin's theme
// choice reach EVERY part of the system, not just `primary-*` utilities.
//   - Ink & Brass (default) keeps its exact hand-tuned values.
//   - Every other theme derives its roles from its own palette:
//       ink   = dark structure (rails, hero surfaces) from primary-900
//       brass = accent ramp
//       paper = warm/cool neutral from secondary-50/100
// The same names are declared in main.css as first-paint fallbacks.
// ---------------------------------------------------------------------------
const INK_AND_BRASS_ROLES = {
  '--ink-950': '#06090d',
  '--ink-900': '#0b1017',
  '--ink-850': '#0f1621',
  '--ink-800': '#141d2b',
  '--ink-700': '#1d2839',
  '--ink-600': '#2a3648',
  '--paper': '#f7f5f0',
  '--paper-2': '#efece4',
  '--brass': '#c2a068',
  '--brass-600': '#a9854d',
  '--brass-200': '#e8d9b8',
  '--brass-700': '#8a6a33',
  '--admin-ink': '#171d2b',
  '--admin-ink-soft': '#252b38',
  '--admin-ink-raise': '#1f2634',
  '--admin-brass': '#c2a068',
  '--admin-brass-deep': '#a88750',
  '--admin-brass-soft': '#dcc28a',
  '--admin-brass-ink': '#3a2c14',
  '--admin-paper': '#f5f1e8',
  '--admin-border': '#e6e0d5',
  '--admin-border-strong': '#d7cfbf',
  '--admin-text': '#252b38',
  '--admin-text-2': '#77746d',
  '--admin-text-3': '#9c988f',
};

const buildRoleTokens = (themeName, colors, accentScale) => {
  if (themeName === 'zoraPro') return INK_AND_BRASS_ROLES;

  const deep = colors.primary900;
  return {
    // Dark structure — always dark enough for light text (AA on rails/heroes)
    '--ink-950': mix(deep, -0.72),
    '--ink-900': mix(deep, -0.58),
    '--ink-850': mix(deep, -0.5),
    '--ink-800': mix(deep, -0.42),
    '--ink-700': mix(deep, -0.28),
    '--ink-600': mix(deep, -0.14),
    // Paper — page backgrounds
    '--paper': colors.secondary50,
    '--paper-2': colors.secondary100,
    // Accent family
    '--brass': accentScale.accent500,
    '--brass-600': accentScale.accent600,
    '--brass-200': accentScale.accent200,
    // Small accent text on light surfaces needs the darker step to stay AA
    '--brass-700': accentScale.accent800,
    // Admin / portal shell
    '--admin-ink': mix(deep, -0.4),
    '--admin-ink-soft': mix(deep, -0.2),
    '--admin-ink-raise': mix(deep, -0.3),
    '--admin-brass': accentScale.accent500,
    '--admin-brass-deep': accentScale.accent600,
    '--admin-brass-soft': accentScale.accent300,
    // Text placed ON an accent button / badge
    '--admin-brass-ink': mix(accentScale.accent500, -0.72),
    '--admin-paper': colors.secondary100,
    '--admin-border': colors.secondary200,
    '--admin-border-strong': colors.secondary300,
    '--admin-text': colors.secondary800,
    '--admin-text-2': colors.secondary500,
    '--admin-text-3': colors.secondary400,
  };
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

    // Derive + apply the full accent ramp (50–900) for every theme
    const accentScale = buildAccentScale(colors.accent, colors.accentSecondary);
    Object.entries(accentScale).forEach(([key, value]) => {
      const shade = key.replace('accent', '');
      root.style.setProperty(`--theme-accent-${shade}`, value);
    });
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

    // Role tokens used by the Ink & Brass design system (admin, portal,
    // public site, auth) so they follow the admin's theme choice too.
    Object.entries(buildRoleTokens(themeName, colors, accentScale)).forEach(([prop, value]) => {
      root.style.setProperty(prop, value);
    });

    // Also set data attribute for potential CSS selectors
    root.setAttribute('data-theme', themeName);
  };

  // Apply the admin-controlled system theme (server value) and cache it so the
  // next cold load paints the correct palette instantly. This is what every
  // visitor goes through — end users never set this value themselves.
  const applySystemTheme = (themeName) => {
    const resolved = themes[themeName] ? themeName : DEFAULT_THEME;

    themeRef.value = resolved;
    currentTheme = resolved;
    applyTheme(resolved);

    if (typeof localStorage !== 'undefined') {
      localStorage.setItem(SYSTEM_THEME_STORAGE_KEY, resolved);
    }
  };

  // Set theme and persist to localStorage (admin action)
  const setTheme = (themeName) => {
    if (!themes[themeName]) {
      console.warn(`Theme "${themeName}" not found. Using default.`);
      themeName = DEFAULT_THEME;
    }
    
    themeRef.value = themeName;
    currentTheme = themeName;
    applyTheme(themeName);
    
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem(SYSTEM_THEME_STORAGE_KEY, themeName);
    }
  };

  // Initialize theme from the cached system theme or the default fallback
  const initTheme = () => {
    let savedTheme = DEFAULT_THEME;
    
    if (typeof localStorage !== 'undefined') {
      savedTheme = localStorage.getItem(SYSTEM_THEME_STORAGE_KEY) || DEFAULT_THEME;
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
    return Object.entries(themes).map(([key, value]) => {
      const roles = buildRoleTokens(
        key,
        value.colors,
        buildAccentScale(value.colors.accent, value.colors.accentSecondary),
      );
      return {
        key,
        name: value.name,
        description: value.description,
        icon: value.icon,
        previewColor: value.colors.primary500,
        // What the admin shell / portal / public site will actually use
        swatches: {
          ink: roles['--admin-ink'],
          accent: roles['--admin-brass'],
          paper: roles['--admin-paper'],
        },
      };
    });
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
    applySystemTheme,
    initTheme,
    getThemes,
    getCurrentThemeInfo
  };
}
