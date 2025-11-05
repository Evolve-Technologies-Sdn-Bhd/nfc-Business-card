// stores/profile.js
import { defineStore } from "pinia";

export const useProfileStore = defineStore("profile", {
  state: () => ({
    profile: null,
    loading: false,
    saving: false,
    sections: [
      { id: "bio", name: "Bio", type: "text", enabled: true, order: 1 },
      {
        id: "contact",
        name: "Contact Info",
        type: "contact",
        enabled: true,
        order: 2,
      },
      {
        id: "social",
        name: "Social Links",
        type: "social",
        enabled: true,
        order: 3,
      },
      {
        id: "portfolio",
        name: "Portfolio",
        type: "media",
        enabled: false,
        order: 4,
      },
      {
        id: "links",
        name: "Custom Links",
        type: "links",
        enabled: false,
        order: 5,
      },
      { id: "video", name: "Video", type: "video", enabled: false, order: 6 },
      {
        id: "calendar",
        name: "Calendar",
        type: "calendar",
        enabled: false,
        order: 7,
      },
    ],
    selectedTheme: "minimal",
    themes: [
      { id: "minimal", name: "Minimal", preview: "/themes/minimal.jpg" },
      { id: "dark", name: "Dark", preview: "/themes/dark.jpg" },
      { id: "gradient", name: "Gradient", preview: "/themes/gradient.jpg" },
      { id: "colorful", name: "Colorful", preview: "/themes/colorful.jpg" },
      {
        id: "professional",
        name: "Professional",
        preview: "/themes/professional.jpg",
      },
      { id: "creative", name: "Creative", preview: "/themes/creative.jpg" },
    ],
    previewMode: false,
  }),

  getters: {
    enabledSections: (state) =>
      state.sections.filter((s) => s.enabled).sort((a, b) => a.order - b.order),
    profileData: (state) => state.profile,
    currentTheme: (state) =>
      state.themes.find((t) => t.id === state.selectedTheme),
    isPreviewMode: (state) => state.previewMode,
  },

  actions: {
    // Initialize profile
    async initializeProfile() {
      this.loading = true;
      try {
        await this.fetchProfile();
      } catch (error) {
        console.error("Error initializing profile:", error);
      } finally {
        this.loading = false;
      }
    },

    // Fetch profile data
    async fetchProfile() {
      try {
        const { $api } = useNuxtApp();
        const response = await $api.get("/profile");

        this.profile = response.data;

        // Update sections based on profile data
        if (response.data.sections) {
          this.sections = response.data.sections;
        }

        // Update theme
        if (response.data.theme) {
          this.selectedTheme = response.data.theme;
        }

        return response.data;
      } catch (error) {
        throw error;
      }
    },

    // Update profile data
    async updateProfile(profileData) {
      this.saving = true;
      try {
        const { $api } = useNuxtApp();
        const response = await $api.put("/profile", profileData);

        this.profile = { ...this.profile, ...response.data };
        return response.data;
      } catch (error) {
        throw error;
      } finally {
        this.saving = false;
      }
    },

    // Update section order
    updateSectionOrder(sections) {
      this.sections = sections.map((section, index) => ({
        ...section,
        order: index + 1,
      }));
    },

    // Toggle section enabled/disabled
    toggleSection(sectionId) {
      const section = this.sections.find((s) => s.id === sectionId);
      if (section) {
        section.enabled = !section.enabled;
      }
    },

    // Update theme
    updateTheme(themeId) {
      this.selectedTheme = themeId;
    },

    // Save profile configuration
    async saveProfile() {
      this.saving = true;
      try {
        const { $api } = useNuxtApp();
        const response = await $api.put("/profile/config", {
          sections: this.sections,
          theme: this.selectedTheme,
          profile: this.profile,
        });

        return response.data;
      } catch (error) {
        throw error;
      } finally {
        this.saving = false;
      }
    },

    // Upload media (profile picture, logo, portfolio items)
    async uploadMedia(file, type = "image") {
      this.loading = true;
      try {
        const { $api } = useNuxtApp();
        const formData = new FormData();
        formData.append("file", file);
        formData.append("type", type);

        const response = await $api.post("/profile/media", formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        });

        return response.data;
      } catch (error) {
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Delete media
    async deleteMedia(mediaId) {
      this.loading = true;
      try {
        const { $api } = useNuxtApp();
        await $api.delete(`/profile/media/${mediaId}`);

        // Remove from profile if it exists
        if (this.profile && this.profile.media) {
          this.profile.media = this.profile.media.filter(
            (m) => m.id !== mediaId
          );
        }
      } catch (error) {
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Add social link
    addSocialLink(platform, url) {
      if (!this.profile.social_links) {
        this.profile.social_links = [];
      }

      this.profile.social_links.push({
        id: Date.now(),
        platform,
        url,
        enabled: true,
      });
    },

    // Remove social link
    removeSocialLink(linkId) {
      if (this.profile.social_links) {
        this.profile.social_links = this.profile.social_links.filter(
          (l) => l.id !== linkId
        );
      }
    },

    // Add custom link
    addCustomLink(title, url) {
      if (!this.profile.custom_links) {
        this.profile.custom_links = [];
      }

      this.profile.custom_links.push({
        id: Date.now(),
        title,
        url,
        enabled: true,
      });
    },

    // Remove custom link
    removeCustomLink(linkId) {
      if (this.profile.custom_links) {
        this.profile.custom_links = this.profile.custom_links.filter(
          (l) => l.id !== linkId
        );
      }
    },

    // Toggle preview mode
    togglePreviewMode() {
      this.previewMode = !this.previewMode;
    },

    // Get profile by slug (for public view)
    async getProfileBySlug(slug) {
      this.loading = true;
      try {
        const { $api } = useNuxtApp();
        const response = await $api.get(`/profile/public/${slug}`);

        return response.data;
      } catch (error) {
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Get profile analytics
    async getProfileAnalytics() {
      try {
        const { $api } = useNuxtApp();
        const response = await $api.get("/profile/analytics");

        return response.data;
      } catch (error) {
        throw error;
      }
    },

    // Reset profile to default
    resetProfile() {
      this.profile = {
        name: "",
        title: "",
        bio: "",
        email: "",
        phone: "",
        location: "",
        website: "",
        profile_image: null,
        logo: null,
        social_links: [],
        custom_links: [],
        portfolio: [],
        video_url: null,
        calendar_url: null,
        background_color: "#ffffff",
        text_color: "#000000",
        accent_color: "#3b82f6",
      };

      this.sections = this.sections.map((section) => ({
        ...section,
        enabled: ["bio", "contact", "social"].includes(section.id),
      }));

      this.selectedTheme = "minimal";
    },
  },
});
