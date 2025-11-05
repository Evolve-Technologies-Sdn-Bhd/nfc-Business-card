// stores/nfc.js
import { defineStore } from "pinia";

export const useNFCStore = defineStore("nfc", {
  state: () => ({
    nfcTags: [],
    loading: false,
    analytics: null,
    selectedTag: null,
    isNFCSupported: false,
    isNFCEnabled: false,
  }),

  getters: {
    activeTags: (state) =>
      state.nfcTags.filter((tag) => tag.status === "active"),
    inactiveTags: (state) =>
      state.nfcTags.filter((tag) => tag.status === "inactive"),
    totalTaps: (state) => state.analytics?.total_taps || 0,
    todayTaps: (state) => state.analytics?.today_taps || 0,
    weeklyTaps: (state) => state.analytics?.weekly_taps || 0,
    monthlyTaps: (state) => state.analytics?.monthly_taps || 0,
  },

  actions: {
    // Initialize NFC functionality
    async initializeNFC() {
      this.checkNFCSupport();
      await this.fetchNFCTags();
      await this.fetchAnalytics();
    },

    // Check if NFC is supported
    checkNFCSupport() {
      if (process.client) {
        this.isNFCSupported = "NDEFReader" in window;
        this.isNFCEnabled = this.isNFCSupported;
      }
    },

    // Fetch all NFC tags
    async fetchNFCTags() {
      this.loading = true;
      try {
        const { $api } = useNuxtApp();
        const response = await $api.get("/nfc/tags");

        this.nfcTags = response.data;
        return response.data;
      } catch (error) {
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Register new NFC tag
    async registerNFCTag(tagData) {
      this.loading = true;
      try {
        const { $api } = useNuxtApp();
        const response = await $api.post("/nfc/tags", tagData);

        this.nfcTags.push(response.data);
        return response.data;
      } catch (error) {
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Update NFC tag
    async updateNFCTag(tagId, tagData) {
      this.loading = true;
      try {
        const { $api } = useNuxtApp();
        const response = await $api.put(`/nfc/tags/${tagId}`, tagData);

        const index = this.nfcTags.findIndex((tag) => tag.id === tagId);
        if (index !== -1) {
          this.nfcTags[index] = { ...this.nfcTags[index], ...response.data };
        }

        return response.data;
      } catch (error) {
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Delete NFC tag
    async deleteNFCTag(tagId) {
      this.loading = true;
      try {
        const { $api } = useNuxtApp();
        await $api.delete(`/nfc/tags/${tagId}`);

        this.nfcTags = this.nfcTags.filter((tag) => tag.id !== tagId);
      } catch (error) {
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Activate NFC tag
    async activateNFCTag(tagId) {
      this.loading = true;
      try {
        const { $api } = useNuxtApp();
        const response = await $api.post(`/nfc/tags/${tagId}/activate`);

        const index = this.nfcTags.findIndex((tag) => tag.id === tagId);
        if (index !== -1) {
          this.nfcTags[index].status = "active";
        }

        return response.data;
      } catch (error) {
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Deactivate NFC tag
    async deactivateNFCTag(tagId) {
      this.loading = true;
      try {
        const { $api } = useNuxtApp();
        const response = await $api.post(`/nfc/tags/${tagId}/deactivate`);

        const index = this.nfcTags.findIndex((tag) => tag.id === tagId);
        if (index !== -1) {
          this.nfcTags[index].status = "inactive";
        }

        return response.data;
      } catch (error) {
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Write to NFC tag
    async writeNFCTag(tagId, profileUrl) {
      if (!this.isNFCSupported) {
        throw new Error("NFC is not supported on this device");
      }

      try {
        const ndef = new NDEFReader();
        await ndef.write({
          records: [
            { recordType: "url", data: profileUrl },
            { recordType: "text", data: `NFC Business Card - ${tagId}` },
          ],
        });

        // Update tag status
        await this.updateNFCTag(tagId, {
          status: "active",
          profile_url: profileUrl,
          last_programmed: new Date().toISOString(),
        });

        return true;
      } catch (error) {
        throw error;
      }
    },

    // Read NFC tag
    async readNFCTag() {
      if (!this.isNFCSupported) {
        throw new Error("NFC is not supported on this device");
      }

      try {
        const ndef = new NDEFReader();
        await ndef.scan();

        return new Promise((resolve, reject) => {
          ndef.addEventListener("readingerror", () => {
            reject(new Error("Cannot read data from the NFC tag"));
          });

          ndef.addEventListener("reading", ({ message }) => {
            const record = message.records[0];
            if (record.recordType === "url") {
              resolve(record.data);
            } else {
              reject(new Error("Invalid NFC tag format"));
            }
          });
        });
      } catch (error) {
        throw error;
      }
    },

    // Fetch analytics data
    async fetchAnalytics(period = "month") {
      this.loading = true;
      try {
        const { $api } = useNuxtApp();
        const response = await $api.get(`/nfc/analytics?period=${period}`);

        this.analytics = response.data;
        return response.data;
      } catch (error) {
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Get tag analytics
    async getTagAnalytics(tagId, period = "month") {
      try {
        const { $api } = useNuxtApp();
        const response = await $api.get(
          `/nfc/tags/${tagId}/analytics?period=${period}`
        );

        return response.data;
      } catch (error) {
        throw error;
      }
    },

    // Record tap (called when NFC tag is tapped)
    async recordTap(tagId, location = null) {
      try {
        const { $api } = useNuxtApp();
        const response = await $api.post(`/nfc/tags/${tagId}/tap`, {
          location,
          timestamp: new Date().toISOString(),
          user_agent: navigator.userAgent,
        });

        return response.data;
      } catch (error) {
        throw error;
      }
    },

    // Get tap history
    async getTapHistory(tagId, limit = 50) {
      try {
        const { $api } = useNuxtApp();
        const response = await $api.get(
          `/nfc/tags/${tagId}/taps?limit=${limit}`
        );

        return response.data;
      } catch (error) {
        throw error;
      }
    },

    // Bulk operations
    async bulkUpdateTags(tagIds, updateData) {
      this.loading = true;
      try {
        const { $api } = useNuxtApp();
        const response = await $api.post("/nfc/tags/bulk-update", {
          tag_ids: tagIds,
          update_data: updateData,
        });

        // Update local state
        tagIds.forEach((tagId) => {
          const index = this.nfcTags.findIndex((tag) => tag.id === tagId);
          if (index !== -1) {
            this.nfcTags[index] = { ...this.nfcTags[index], ...updateData };
          }
        });

        return response.data;
      } catch (error) {
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Export analytics
    async exportAnalytics(format = "csv", period = "month") {
      try {
        const { $api } = useNuxtApp();
        const response = await $api.get(
          `/nfc/analytics/export?format=${format}&period=${period}`,
          {
            responseType: "blob",
          }
        );

        // Create download link
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", `nfc-analytics-${period}.${format}`);
        document.body.appendChild(link);
        link.click();
        link.remove();

        return true;
      } catch (error) {
        throw error;
      }
    },

    // Select tag for detailed view
    selectTag(tag) {
      this.selectedTag = tag;
    },

    // Clear selected tag
    clearSelectedTag() {
      this.selectedTag = null;
    },

    // Generate QR code for NFC tag (backup method)
    async generateQRCode(tagId) {
      try {
        const { $api } = useNuxtApp();
        const response = await $api.post(`/nfc/tags/${tagId}/qr-code`);

        return response.data;
      } catch (error) {
        throw error;
      }
    },

    // Validate NFC tag ID
    async validateNFCTagId(tagId) {
      try {
        const { $api } = useNuxtApp();
        const response = await $api.post("/nfc/validate-tag", {
          tag_id: tagId,
        });

        return response.data;
      } catch (error) {
        throw error;
      }
    },
  },
});
