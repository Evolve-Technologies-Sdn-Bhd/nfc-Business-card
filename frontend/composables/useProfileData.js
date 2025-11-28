// composables/useProfileData.js
// Unified Profile Data Management - Connecting ProfileBuilder and Landing Page

import { ref, reactive, computed } from 'vue';

/**
 * Complete mapping between ProfileBuilder Tabs and Landing Page Sections
 * 
 * ProfileBuilder Tabs:
 * 1. Profile    -> Hero Section (name, position, qualification, bio, image, stats)
 * 2. Company    -> Company Section (logo, name, registration, department) + Contact + Location
 * 3. Services   -> Services Section + Team Members
 * 4. Social     -> Social Links Section
 * 5. Links      -> Custom Links Section
 * 6. Design     -> Design Settings (theme, colors, fonts)
 * 7. Style      -> Button Styles
 */

// Complete data structure definition for ProfileBuilder
export const profileDataSchema = {
  // ==================== PROFILE TAB ====================
  profile: {
    name: 'Profile',
    icon: 'heroicons:user',
    fields: {
      // Basic Info
      name: { type: 'text', label: 'Full Name', required: true },
      position: { type: 'text', label: 'Position/Title', required: false },
      qualification: { type: 'text', label: 'Qualification', required: false },
      bio: { type: 'textarea', label: 'Bio/About', required: false },
      image: { type: 'image', label: 'Profile Picture', required: false },
      
      // Stats (repeater)
      stats: { 
        type: 'repeater', 
        label: 'Statistics',
        max: 3,
        fields: {
          num: { type: 'text', label: 'Number' },
          label: { type: 'text', label: 'Label' }
        }
      }
    }
  },

  // ==================== COMPANY TAB ====================
  company: {
    name: 'Company',
    icon: 'heroicons:building-office',
    fields: {
      // Company Info
      companyLogo: { type: 'image', label: 'Company Logo' },
      companyLogoText: { type: 'text', label: 'Logo Alt Text' },
      companyName: { type: 'text', label: 'Company Name' },
      companyRegistrationNo: { type: 'text', label: 'Registration No.' },
      companyDepartment: { type: 'text', label: 'Department' },

      // Contact Methods
      phoneNumber: { type: 'phone', label: 'Phone Number' },
      phoneLabel: { type: 'text', label: 'Phone Label', default: 'Phone' },
      emailAddress: { type: 'email', label: 'Email Address' },
      emailLabel: { type: 'text', label: 'Email Label', default: 'Email' },
      whatsappNumber: { type: 'phone', label: 'WhatsApp Number' },
      whatsappLabel: { type: 'text', label: 'WhatsApp Label', default: 'WhatsApp' },
      websiteUrl: { type: 'url', label: 'Website URL' },
      websiteLabel: { type: 'text', label: 'Website Label', default: 'Website' },

      // Address Info
      addressName: { type: 'text', label: 'Address Name' },
      addressStreet: { type: 'text', label: 'Street Address' },
      addressArea: { type: 'text', label: 'Area/District' },
      addressCityState: { type: 'text', label: 'City/State' },
      addressCountry: { type: 'text', label: 'Country' },
      addressMapUrl: { type: 'url', label: 'Google Maps URL' }
    }
  },

  // ==================== SERVICES TAB ====================
  services: {
    name: 'Services',
    icon: 'heroicons:rocket-launch',
    fields: {
      services: {
        type: 'repeater',
        label: 'Services',
        max: 6,
        fields: {
          icon: { type: 'emoji', label: 'Icon' },
          name: { type: 'text', label: 'Service Name' },
          description: { type: 'text', label: 'Description' }
        }
      },
      teamMembers: {
        type: 'repeater',
        label: 'Team Members',
        max: 6,
        fields: {
          initials: { type: 'text', label: 'Initials' },
          name: { type: 'text', label: 'Name' },
          role: { type: 'text', label: 'Role' },
          profile_image: { type: 'image', label: 'Profile Image' },
          landing_page_url: { type: 'url', label: 'Profile URL' }
        }
      }
    }
  },

  // ==================== SOCIAL TAB ====================
  social: {
    name: 'Social',
    icon: 'heroicons:globe-alt',
    fields: {
      socialLinks: {
        type: 'repeater',
        label: 'Social Links',
        max: 8,
        fields: {
          emoji: { type: 'emoji', label: 'Icon' },
          name: { type: 'text', label: 'Platform Name' },
          url: { type: 'url', label: 'Profile URL' }
        }
      }
    }
  },

  // ==================== LINKS TAB ====================
  links: {
    name: 'Links',
    icon: 'heroicons:link',
    fields: {
      customLinks: {
        type: 'repeater',
        label: 'Custom Links',
        fields: {
          title: { type: 'text', label: 'Link Title' },
          url: { type: 'url', label: 'URL' },
          platform: { type: 'select', label: 'Platform' },
          is_active: { type: 'boolean', label: 'Active', default: true }
        }
      }
    }
  },

  // ==================== DESIGN TAB ====================
  design: {
    name: 'Design',
    icon: 'heroicons:paint-brush',
    fields: {
      profileStyle: { type: 'select', label: 'Profile Style', default: 'classic' },
      theme: { type: 'select', label: 'Theme', default: 'minimal' },
      backgroundColor: { type: 'color', label: 'Background Color', default: '#FFFFFF' },
      textColor: { type: 'color', label: 'Text Color', default: '#000000' },
      font: { type: 'select', label: 'Font Family', default: 'inter' }
    }
  },

  // ==================== STYLE TAB ====================
  style: {
    name: 'Style',
    icon: 'heroicons:sparkles',
    fields: {
      buttonStyle: { type: 'select', label: 'Button Style', default: 'solid' },
      showWatermark: { type: 'boolean', label: 'Show Watermark', default: true }
    }
  }
};

// Landing Page Section definition - Complete mapping to BusinessProfileBuilder
// Note: About + Education + Awards are combined into one card, Company + Video + Team are combined into one card
export const landingPageSections = [
  {
    id: 'hero',
    name: 'Hero Section',
    icon: '👤',
    sourceTab: 'profile',
    fields: ['profilePicture', 'coverBanner', 'name', 'position', 'qualification', 'tagline', 'pronouns'],
    enabled: true,
    order: 1
  },
  {
    id: 'about',
    name: 'About Me',
    icon: '✨',
    sourceTab: 'profile',
    // Combined: About Me + Education + Awards
    fields: ['bio', 'stats', 'education', 'certifications', 'expertise', 'awards'],
    subSections: ['aboutMe', 'education', 'awards'],
    enabled: true,
    order: 2
  },
  {
    id: 'company',
    name: 'Company',
    icon: '🏢',
    sourceTab: 'company',
    // Combined: Company Info + Video + Team
    fields: ['companyLogo', 'companyName', 'companyRegistrationNo', 'companyDepartment', 'companyDescription', 'companyVideo', 'industry', 'establishedYear', 'employeeCount', 'teamMembers'],
    subSections: ['companyInfo', 'video', 'team'],
    enabled: true,
    order: 3
  },
  {
    id: 'contact',
    name: 'Contact Section',
    icon: '📞',
    sourceTab: 'company',
    fields: ['phoneNumber', 'phoneLabel', 'emailAddress', 'emailLabel', 'whatsappNumber', 'whatsappLabel', 'websiteUrl', 'websiteLabel', 'workingHours'],
    enabled: true,
    order: 4
  },
  {
    id: 'location',
    name: 'Location Section',
    icon: '📍',
    sourceTab: 'company',
    fields: ['addressName', 'addressStreet', 'addressArea', 'addressCityState', 'addressCountry', 'postalCode', 'addressMapUrl', 'coordinates'],
    enabled: true,
    order: 5
  },
  {
    id: 'services',
    name: 'Services Section',
    icon: '🚀',
    sourceTab: 'services',
    fields: ['services', 'gallery', 'bookingEnabled', 'bookingUrl'],
    enabled: true,
    order: 6
  },
  {
    id: 'portfolio',
    name: 'Portfolio Section',
    icon: '📁',
    sourceTab: 'portfolio',
    fields: ['projects'],
    enabled: false,
    order: 7
  },
  {
    id: 'blog',
    name: 'Blog Section',
    icon: '📝',
    sourceTab: 'blog',
    fields: ['blogPosts', 'blogEnabled'],
    enabled: false,
    order: 8
  },
  {
    id: 'social',
    name: 'Social Links',
    icon: '🌐',
    sourceTab: 'links',
    fields: ['socialLinks'],
    enabled: true,
    order: 9
  },
  {
    id: 'links',
    name: 'Custom Links',
    icon: '🔗',
    sourceTab: 'links',
    fields: ['customLinks', 'appointmentLink', 'paymentButtonText', 'paymentButtonUrl'],
    enabled: false,
    order: 10
  },
  {
    id: 'vcard',
    name: 'Save Contact',
    icon: '💾',
    sourceTab: null,
    fields: [],
    enabled: true,
    order: 11
  }
];

// Theme presets
export const themePresets = {
  minimal: {
    id: 'minimal',
    name: 'Minimal',
    backgroundColor: '#FFFFFF',
    textColor: '#000000',
    accentColor: '#3B82F6',
    cardBackground: 'rgba(0, 0, 0, 0.05)',
    gradient: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
  },
  modern: {
    id: 'modern',
    name: 'Modern',
    backgroundColor: '#F9FAFB',
    textColor: '#111827',
    accentColor: '#6366F1',
    cardBackground: 'rgba(255, 255, 255, 0.8)',
    gradient: 'linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%)'
  },
  dark: {
    id: 'dark',
    name: 'Dark',
    backgroundColor: '#111827',
    textColor: '#FFFFFF',
    accentColor: '#60A5FA',
    cardBackground: 'rgba(255, 255, 255, 0.05)',
    gradient: 'linear-gradient(135deg, #1e3a8a 0%, #312e81 100%)'
  },
  creative: {
    id: 'creative',
    name: 'Creative',
    backgroundColor: '#A855F7',
    textColor: '#FFFFFF',
    accentColor: '#F472B6',
    cardBackground: 'rgba(255, 255, 255, 0.1)',
    gradient: 'linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%)'
  },
  professional: {
    id: 'professional',
    name: 'Professional',
    backgroundColor: '#2563EB',
    textColor: '#FFFFFF',
    accentColor: '#93C5FD',
    cardBackground: 'rgba(255, 255, 255, 0.1)',
    gradient: 'linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%)'
  }
};

// Font presets
export const fontPresets = {
  inter: { id: 'inter', name: 'Inter', family: 'Inter, sans-serif' },
  poppins: { id: 'poppins', name: 'Poppins', family: 'Poppins, sans-serif' },
  roboto: { id: 'roboto', name: 'Roboto', family: 'Roboto, sans-serif' },
  playfair: { id: 'playfair', name: 'Playfair', family: 'Playfair Display, serif' }
};

// Button Style presets
export const buttonStylePresets = {
  solid: { id: 'solid', name: 'Solid', class: 'bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-full' },
  outline: { id: 'outline', name: 'Outline', class: 'border-2 border-current text-current rounded-full' },
  soft: { id: 'soft', name: 'Soft', class: 'bg-white/20 backdrop-blur-sm text-white rounded-xl' },
  shadow: { id: 'shadow', name: 'Shadow', class: 'bg-white text-black rounded-xl shadow-lg' }
};

/**
 * useProfileData Composable
 * Manage Profile data, connecting ProfileBuilder and Landing Page
 */
export function useProfileData() {
  // ==================== STATE ====================
  
  // Complete Profile data - matches all fields in BusinessProfileBuilder
  const profileData = reactive({
    // ============ PROFILE TAB ============
    name: '',
    position: '',
    qualification: '',
    bio: '',
    tagline: '',
    pronouns: '',
    profilePicture: null,
    image: null, // Legacy
    coverBanner: null,
    contactNumber: '',
    email: '',
    emailAddress: '',
    website: '',
    address: '',
    
    // Education & Certifications
    education: [],
    certifications: [],
    
    // Stats
    stats: [],
    profileStats: [],

    // ============ COMPANY TAB ============
    companyLogo: null,
    companyLogoText: '',
    companyName: '',
    companyRegistrationNo: '',
    companyDepartment: '',
    companyDescription: '',
    companyVideo: '',
    industry: '',
    establishedYear: '',
    employeeCount: '',
    companyWhatsapp: '',

    // Address Details
    addressName: '',
    addressStreet: '',
    addressArea: '',
    addressCityState: '',
    addressCountry: '',
    postalCode: '',
    addressMapUrl: '',
    coordinates: null,

    // Contact Methods
    phoneNumber: '',
    phoneLabel: 'Phone',
    emailLabel: 'Email',
    whatsappNumber: '',
    whatsappLabel: 'WhatsApp',
    websiteUrl: '',
    websiteLabel: 'Website',

    // Working Hours
    workingHours: [],

    // ============ SERVICES TAB ============
    services: [],
    teamMembers: [],
    expertise: [],
    awards: [],
    
    // Service Form Fields
    serviceName: '',
    serviceCategory: '',
    serviceImage: null,
    serviceVideo: '',
    serviceDescription: '',
    servicePrice: '',
    serviceOldPrice: '',
    serviceDuration: '',
    serviceFeatures: [],
    serviceTags: [],
    serviceBrochure: '',
    bookingEnabled: false,
    bookingUrl: '',
    gallery: [],

    // ============ PORTFOLIO TAB ============
    projects: [],
    portfolioTitle: '',
    portfolioDescription: '',
    portfolioCategory: '',
    portfolioTags: [],
    portfolioCoverImage: null,
    portfolioGallery: [],
    projectUrl: '',
    dateCompleted: '',
    clientName: '',
    location: '',
    skillsUsed: [],
    pdfDownload: '',

    // ============ BLOG TAB ============
    blogEnabled: false,
    blogPosts: [],
    blogGallery: [],
    blogTitle: '',
    blogSlug: '',
    blogCoverImage: null,
    blogCategory: '',
    blogTags: [],
    authorName: '',
    publishedDate: '',
    readingTime: '',
    blogContent: '',
    externalLink: '',
    relatedPosts: [],

    // ============ LINKS TAB ============
    socialLinks: [],
    customLinks: [],
    appointmentLink: '',
    paymentButtonText: '',
    paymentButtonUrl: '',

    // ============ DESIGN TAB ============
    profileStyle: 'classic',
    theme: 'minimal',
    backgroundColor: '#FFFFFF',
    textColor: '#000000',
    font: 'inter',
    buttonStyle: 'solid',
    colorScheme: '',
    layout: '',
    showWatermark: true,
    
    // Features
    features: {},
    featureOrder: [],
    
    // Layout Configuration
    sectionLayout: [],
    fieldLayout: {},
    sectionSettings: {}
  });

  // Section configuration
  const sectionLayout = ref([...landingPageSections]);

  // Loading state
  const loading = ref(false);
  const saving = ref(false);

  // ==================== COMPUTED ====================

  // Current Theme settings
  const currentTheme = computed(() => {
    return themePresets[profileData.theme] || themePresets.minimal;
  });

  // Current Font settings
  const currentFont = computed(() => {
    return fontPresets[profileData.font] || fontPresets.inter;
  });

  // Current Button Style
  const currentButtonStyle = computed(() => {
    return buttonStylePresets[profileData.buttonStyle] || buttonStylePresets.solid;
  });

  // Enabled Sections (in order)
  const enabledSections = computed(() => {
    return sectionLayout.value
      .filter(s => s.enabled)
      .sort((a, b) => a.order - b.order);
  });

  // Section order array
  const orderedSectionIds = computed(() => {
    return enabledSections.value.map(s => s.id);
  });

  // Design Settings object (for Landing Page)
  const designSettings = computed(() => ({
    backgroundColor: profileData.backgroundColor,
    textColor: profileData.textColor,
    fontFamily: currentFont.value.family,
    buttonStyle: currentButtonStyle.value.class,
    theme: currentTheme.value,
    accentColor: currentTheme.value.accentColor,
    cardBackground: currentTheme.value.cardBackground,
    gradient: currentTheme.value.gradient
  }));

  // Contact Methods array (for Landing Page)
  const contactMethods = computed(() => {
    const methods = [];
    
    if (profileData.phoneNumber) {
      methods.push({
        type: 'phone',
        label: profileData.phoneLabel || 'Phone',
        value: profileData.phoneNumber,
        href: `tel:${profileData.phoneNumber}`,
        icon: 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
        color: 'linear-gradient(135deg, #667eea, #764ba2)'
      });
    }

    if (profileData.emailAddress) {
      methods.push({
        type: 'email',
        label: profileData.emailLabel || 'Email',
        value: profileData.emailAddress,
        href: `mailto:${profileData.emailAddress}`,
        icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        color: 'linear-gradient(135deg, #f093fb, #f5576c)'
      });
    }

    if (profileData.whatsappNumber) {
      methods.push({
        type: 'whatsapp',
        label: profileData.whatsappLabel || 'WhatsApp',
        value: profileData.whatsappNumber,
        href: `https://wa.me/${profileData.whatsappNumber.replace(/\D/g, '')}`,
        icon: 'M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z',
        color: 'linear-gradient(135deg, #25D366, #128C7E)'
      });
    }

    if (profileData.websiteUrl) {
      methods.push({
        type: 'website',
        label: profileData.websiteLabel || 'Website',
        value: profileData.websiteUrl,
        href: profileData.websiteUrl,
        icon: 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9',
        color: 'linear-gradient(135deg, #667eea, #764ba2)'
      });
    }

    return methods;
  });

  // Address object
  const address = computed(() => ({
    name: profileData.addressName,
    street: profileData.addressStreet,
    area: profileData.addressArea,
    cityState: profileData.addressCityState,
    country: profileData.addressCountry,
    mapUrl: profileData.addressMapUrl,
    fullAddress: [
      profileData.addressStreet,
      profileData.addressArea,
      profileData.addressCityState,
      profileData.addressCountry
    ].filter(Boolean).join(', ')
  }));

  // Company object
  const company = computed(() => ({
    logo: profileData.companyLogo,
    logoText: profileData.companyLogoText,
    name: profileData.companyName,
    registrationNo: profileData.companyRegistrationNo,
    department: profileData.companyDepartment
  }));

  // ==================== METHODS ====================

  /**
   * Check if Section is enabled
   */
  const isSectionEnabled = (sectionId) => {
    const section = sectionLayout.value.find(s => s.id === sectionId);
    return section ? section.enabled : false;
  };

  /**
   * Get Section order
   */
  const getSectionOrder = (sectionId) => {
    const section = sectionLayout.value.find(s => s.id === sectionId);
    return section ? section.order : 999;
  };

  /**
   * Toggle Section enabled state
   */
  const toggleSection = (sectionId) => {
    const section = sectionLayout.value.find(s => s.id === sectionId);
    if (section) {
      section.enabled = !section.enabled;
    }
  };

  /**
   * Update Section order
   */
  const updateSectionOrder = (sections) => {
    sectionLayout.value = sections.map((section, index) => ({
      ...section,
      order: index + 1
    }));
  };

  /**
   * Load data from API response - complete match with BusinessProfileBuilder
   */
  const loadFromApiResponse = (data) => {
    if (!data) return;

    // ============ PROFILE TAB ============
    profileData.name = data.name || '';
    profileData.position = data.title || data.position || '';
    profileData.qualification = data.qualification || '';
    profileData.bio = data.bio || '';
    profileData.tagline = data.tagline || '';
    profileData.pronouns = data.pronouns || '';
    profileData.profilePicture = data.profile_image || null;
    profileData.image = data.profile_image || null;
    profileData.coverBanner = data.cover_banner || null;
    profileData.contactNumber = data.phone || data.phone_number || '';
    profileData.email = data.email || data.email_address || '';
    profileData.emailAddress = data.email_address || data.email || '';
    profileData.website = data.website || data.website_url || '';
    profileData.address = data.address || '';
    
    // Education & Certifications
    if (data.education && Array.isArray(data.education)) {
      profileData.education = data.education;
    }
    if (data.certifications && Array.isArray(data.certifications)) {
      profileData.certifications = data.certifications;
    }
    
    // Stats
    if (data.stats && Array.isArray(data.stats)) {
      profileData.stats = data.stats;
      profileData.profileStats = data.stats;
    }

    // ============ COMPANY TAB ============
    profileData.companyLogo = data.company_logo || null;
    profileData.companyLogoText = data.company_logo_text || '';
    profileData.companyName = data.company_name || '';
    profileData.companyRegistrationNo = data.company_registration_no || '';
    profileData.companyDepartment = data.company_department || '';
    profileData.companyDescription = data.company_description || '';
    profileData.companyVideo = data.company_video || '';
    profileData.industry = data.industry || '';
    profileData.establishedYear = data.established_year || '';
    profileData.employeeCount = data.employee_count || '';
    profileData.companyWhatsapp = data.company_whatsapp || '';

    // Address Details
    profileData.addressName = data.address_name || '';
    profileData.addressStreet = data.address_street || '';
    profileData.addressArea = data.address_area || '';
    profileData.addressCityState = data.address_city_state || '';
    profileData.addressCountry = data.address_country || '';
    profileData.postalCode = data.postal_code || '';
    profileData.addressMapUrl = data.address_map_url || '';
    profileData.coordinates = data.coordinates || null;

    // Contact Methods
    profileData.phoneNumber = data.phone_number || data.phone || '';
    profileData.phoneLabel = data.phone_label || 'Phone';
    profileData.emailLabel = data.email_label || 'Email';
    profileData.whatsappNumber = data.whatsapp_number || '';
    profileData.whatsappLabel = data.whatsapp_label || 'WhatsApp';
    profileData.websiteUrl = data.website_url || data.website || '';
    profileData.websiteLabel = data.website_label || 'Website';

    // Working Hours
    if (data.working_hours && Array.isArray(data.working_hours)) {
      profileData.workingHours = data.working_hours;
    }

    // ============ SERVICES TAB ============
    if (data.services && Array.isArray(data.services)) {
      profileData.services = data.services;
    }
    if (data.team_members && Array.isArray(data.team_members)) {
      profileData.teamMembers = data.team_members;
    }
    if (data.expertise && Array.isArray(data.expertise)) {
      profileData.expertise = data.expertise;
    }
    if (data.awards && Array.isArray(data.awards)) {
      profileData.awards = data.awards;
    }
    if (data.gallery && Array.isArray(data.gallery)) {
      profileData.gallery = data.gallery;
    }
    profileData.bookingEnabled = data.booking_enabled || false;
    profileData.bookingUrl = data.booking_url || '';

    // ============ PORTFOLIO TAB ============
    if (data.projects && Array.isArray(data.projects)) {
      profileData.projects = data.projects;
    }

    // ============ BLOG TAB ============
    profileData.blogEnabled = data.blog_enabled || false;
    if (data.blog_posts && Array.isArray(data.blog_posts)) {
      profileData.blogPosts = data.blog_posts;
    }

    // ============ LINKS TAB ============
    if (data.social_links && Array.isArray(data.social_links)) {
      profileData.socialLinks = data.social_links;
    }
    if (data.links && Array.isArray(data.links)) {
      profileData.customLinks = data.links;
    }
    if (data.custom_links && Array.isArray(data.custom_links)) {
      profileData.customLinks = data.custom_links;
    }
    profileData.appointmentLink = data.appointment_link || '';
    profileData.paymentButtonText = data.payment_button_text || '';
    profileData.paymentButtonUrl = data.payment_button_url || '';

    // ============ DESIGN TAB ============
    profileData.profileStyle = data.profile_style || 'classic';
    profileData.theme = data.theme || 'minimal';
    profileData.backgroundColor = data.background_color || '#FFFFFF';
    profileData.textColor = data.text_color || '#000000';
    profileData.font = data.font || 'inter';
    profileData.buttonStyle = data.button_style || 'solid';
    profileData.colorScheme = data.color_scheme || '';
    profileData.layout = data.layout || '';
    profileData.showWatermark = data.show_watermark !== false;
    
    // Features
    if (data.features && typeof data.features === 'object') {
      profileData.features = data.features;
    }
    if (data.feature_order && Array.isArray(data.feature_order)) {
      profileData.featureOrder = data.feature_order;
    }

    // Layout Configuration
    if (data.section_layout && Array.isArray(data.section_layout)) {
      profileData.sectionLayout = data.section_layout;
      sectionLayout.value = data.section_layout;
    }
    if (data.field_layout && typeof data.field_layout === 'object') {
      profileData.fieldLayout = data.field_layout;
    }
    if (data.section_settings && typeof data.section_settings === 'object') {
      profileData.sectionSettings = data.section_settings;
    }
  };

  /**
   * Convert to API request format
   */
  const toApiPayload = () => {
    return {
      // Basic Info
      name: profileData.name,
      title: profileData.position,
      qualification: profileData.qualification,
      bio: profileData.bio,
      profile_image: profileData.image,

      // Company Info
      company_logo: profileData.companyLogo,
      company_logo_text: profileData.companyLogoText,
      company_name: profileData.companyName,
      company_registration_no: profileData.companyRegistrationNo,
      company_department: profileData.companyDepartment,

      // Contact Methods
      phone: profileData.phoneNumber,
      phone_number: profileData.phoneNumber,
      phone_label: profileData.phoneLabel,
      email: profileData.emailAddress,
      email_address: profileData.emailAddress,
      email_label: profileData.emailLabel,
      whatsapp_number: profileData.whatsappNumber,
      whatsapp_label: profileData.whatsappLabel,
      website: profileData.websiteUrl,
      website_url: profileData.websiteUrl,
      website_label: profileData.websiteLabel,

      // Address
      address_name: profileData.addressName,
      address_street: profileData.addressStreet,
      address_area: profileData.addressArea,
      address_city_state: profileData.addressCityState,
      address_country: profileData.addressCountry,
      address_map_url: profileData.addressMapUrl,

      // Stats
      stats: profileData.stats,

      // Services
      services: profileData.services,

      // Team Members
      team_members: profileData.teamMembers,

      // Social Links
      social_links: profileData.socialLinks,

      // Custom Links
      custom_links: profileData.customLinks,

      // Design Settings
      profile_style: profileData.profileStyle,
      theme: profileData.theme,
      background_color: profileData.backgroundColor,
      text_color: profileData.textColor,
      font: profileData.font,
      button_style: profileData.buttonStyle,
      show_watermark: profileData.showWatermark,

      // Section Layout
      section_layout: sectionLayout.value
    };
  };

  /**
   * Generate vCard data
   */
  const generateVCard = () => {
    const vcard = [
      'BEGIN:VCARD',
      'VERSION:3.0',
      `FN:${profileData.name}`,
      `TITLE:${profileData.position}`,
      `ORG:${profileData.companyName}`,
      profileData.phoneNumber ? `TEL;TYPE=WORK:${profileData.phoneNumber}` : '',
      profileData.emailAddress ? `EMAIL:${profileData.emailAddress}` : '',
      profileData.websiteUrl ? `URL:${profileData.websiteUrl}` : '',
      address.value.fullAddress ? `ADR;TYPE=WORK:;;${address.value.fullAddress}` : '',
      profileData.bio ? `NOTE:${profileData.bio}` : '',
      'END:VCARD'
    ].filter(Boolean).join('\n');

    return vcard;
  };

  /**
   * Download vCard
   */
  const downloadVCard = () => {
    const vcard = generateVCard();
    const blob = new Blob([vcard], { type: 'text/vcard;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${profileData.name || 'contact'}.vcf`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  };

  /**
   * Reset to default values
   */
  const resetToDefaults = () => {
    Object.assign(profileData, {
      name: '',
      position: '',
      qualification: '',
      bio: '',
      image: null,
      stats: [
        { num: '', label: '' },
        { num: '', label: '' },
        { num: '', label: '' }
      ],
      companyLogo: null,
      companyLogoText: '',
      companyName: '',
      companyRegistrationNo: '',
      companyDepartment: '',
      phoneNumber: '',
      phoneLabel: 'Phone',
      emailAddress: '',
      emailLabel: 'Email',
      whatsappNumber: '',
      whatsappLabel: 'WhatsApp',
      websiteUrl: '',
      websiteLabel: 'Website',
      addressName: '',
      addressStreet: '',
      addressArea: '',
      addressCityState: '',
      addressCountry: '',
      addressMapUrl: '',
      services: [],
      teamMembers: [],
      socialLinks: [],
      customLinks: [],
      profileStyle: 'classic',
      theme: 'minimal',
      backgroundColor: '#FFFFFF',
      textColor: '#000000',
      font: 'inter',
      buttonStyle: 'solid',
      showWatermark: true
    });

    sectionLayout.value = [...landingPageSections];
  };

  return {
    // State
    profileData,
    sectionLayout,
    loading,
    saving,

    // Computed
    currentTheme,
    currentFont,
    currentButtonStyle,
    enabledSections,
    orderedSectionIds,
    designSettings,
    contactMethods,
    address,
    company,

    // Methods
    isSectionEnabled,
    getSectionOrder,
    toggleSection,
    updateSectionOrder,
    loadFromApiResponse,
    toApiPayload,
    generateVCard,
    downloadVCard,
    resetToDefaults,

    // Constants
    profileDataSchema,
    landingPageSections,
    themePresets,
    fontPresets,
    buttonStylePresets
  };
}

export default useProfileData;
