<!-- pages/UserDashboard/LinkManagement.vue -->
<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-14 sm:h-16">
          <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">
              Link Management
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 hidden sm:block">
              Manage your links and share your profile
            </p>
          </div>
          <div class="flex items-center space-x-2 sm:space-x-4">
            <div
              class="hidden sm:flex items-center space-x-2 px-3 sm:px-4 py-1.5 sm:py-2 bg-gray-100 rounded-lg"
            >
              <Icon name="heroicons:link" class="h-4 w-4 text-gray-600" />
              <span class="text-xs sm:text-sm font-medium text-gray-700"
                >Your Link:</span
              >
              <a
                :href="profileUrl"
                target="_blank"
                class="text-xs sm:text-sm text-blue-600 hover:text-blue-700 font-medium truncate max-w-[150px] sm:max-w-none"
              >
                {{ shortProfileUrl }}
              </a>
              <button
                @click="copyProfileUrl"
                class="ml-2 p-1 hover:bg-gray-200 rounded"
              >
                <Icon
                  name="heroicons:clipboard-document"
                  class="h-4 w-4 text-gray-600"
                />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
      <!-- Mobile Profile URL -->
      <div
        class="sm:hidden mb-4 flex items-center space-x-2 px-3 py-2 bg-white rounded-lg shadow-sm"
      >
        <Icon
          name="heroicons:link"
          class="h-4 w-4 text-gray-600 flex-shrink-0"
        />
        <a
          :href="profileUrl"
          target="_blank"
          class="text-xs text-blue-600 hover:text-blue-700 font-medium truncate flex-1"
        >
          {{ profileUrl }}
        </a>
        <button @click="copyProfileUrl" class="p-1">
          <Icon
            name="heroicons:clipboard-document"
            class="h-4 w-4 text-gray-600"
          />
        </button>
      </div>

      <!-- Mobile Preview Toggle -->
      <div class="lg:hidden mb-4">
        <button
          @click="showMobilePreview = !showMobilePreview"
          class="w-full py-2 px-4 bg-white rounded-lg shadow-sm border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
          {{ showMobilePreview ? "Hide Preview" : "Show Preview" }}
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <!-- Left Panel - Links Editor -->
        <div
          v-show="!showMobilePreview || !isMobile"
          class="space-y-4 sm:space-y-6"
        >
          <!-- Links Section -->
          <div class="bg-white rounded-lg sm:rounded-xl shadow-sm">
            <div class="p-4 sm:p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900">
                  Your Links
                </h3>
                <span class="text-xs sm:text-sm text-gray-500"
                  >{{ links.length }} links</span
                >
              </div>

              <!-- Empty State -->
              <div
                v-if="links.length === 0 && !loading"
                class="text-center py-8 sm:py-12"
              >
                <div
                  class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 rounded-full mx-auto mb-4 flex items-center justify-center"
                >
                  <Icon
                    name="heroicons:link"
                    class="h-8 w-8 sm:h-10 sm:w-10 text-gray-400"
                  />
                </div>
                <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">
                  No Links Yet
                </h3>
                <p class="text-sm text-gray-600 mb-6">
                  Add your first link to get started
                </p>
                <button
                  @click="showAddLinkModal = true"
                  class="px-6 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors text-sm font-medium"
                >
                  <Icon name="heroicons:plus" class="h-4 w-4 inline mr-2" />
                  Add Your First Link
                </button>
              </div>

              <!-- Links List -->
              <div v-else-if="!loading">
                <draggable
                  v-model="links"
                  @end="updateLinkOrder"
                  item-key="id"
                  class="space-y-3"
                  handle=".drag-handle"
                >
                  <template #item="{ element: link, index }">
                    <div class="group relative">
                      <!-- Link Container -->
                      <div
                        :class="[
                          'border-2 rounded-lg p-3 sm:p-4 transition-all',
                          link.is_active
                            ? 'border-gray-200 bg-white'
                            : 'border-gray-200 bg-gray-50 opacity-60',
                        ]"
                      >
                        <div class="flex items-start space-x-3">
                          <!-- Drag Handle -->
                          <div class="drag-handle cursor-move pt-1">
                            <Icon
                              name="heroicons:bars-3"
                              class="h-5 w-5 text-gray-400 hover:text-gray-600"
                            />
                          </div>

                          <!-- Platform Icon -->
                          <div
                            :class="[
                              'w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0',
                              link.is_active
                                ? getPlatformColor(link.platform)
                                : 'bg-gray-200',
                            ]"
                          >
                            <Icon
                              :name="getPlatformIcon(link.platform)"
                              :class="[
                                'h-5 w-5',
                                link.is_active ? 'text-white' : 'text-gray-400',
                              ]"
                            />
                          </div>

                          <!-- Link Content -->
                          <div class="flex-1 min-w-0">
                            <input
                              v-model="link.title"
                              type="text"
                              placeholder="Link title"
                              class="w-full px-3 py-1.5 text-sm font-medium bg-transparent border-0 focus:ring-2 focus:ring-blue-500 rounded"
                              @blur="updateLink(link)"
                            />
                            <input
                              v-model="link.url"
                              type="url"
                              placeholder="URL"
                              class="w-full px-3 py-1 mt-1 text-xs sm:text-sm text-gray-600 bg-transparent border-0 focus:ring-2 focus:ring-blue-500 rounded"
                              @blur="updateLink(link)"
                            />
                            <p class="text-xs text-gray-500 mt-1">
                              <Icon
                                name="heroicons:cursor-arrow-ripple"
                                class="h-3 w-3 inline mr-1"
                              />
                              {{ link.click_count || 0 }} clicks
                            </p>
                          </div>

                          <!-- Actions -->
                          <div class="flex items-center space-x-2">
                            <!-- Toggle Switch -->
                            <button
                              @click="toggleLink(link)"
                              :class="[
                                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out',
                                link.is_active ? 'bg-blue-600' : 'bg-gray-200',
                              ]"
                            >
                              <span
                                :class="[
                                  'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                                  link.is_active
                                    ? 'translate-x-5'
                                    : 'translate-x-0',
                                ]"
                              />
                            </button>

                            <!-- Delete Button -->
                            <button
                              @click="deleteLink(link)"
                              class="p-1 text-gray-400 hover:text-red-600 transition-colors"
                            >
                              <Icon name="heroicons:trash" class="h-4 w-4" />
                            </button>
                          </div>
                        </div>
                      </div>

                      <!-- Add Button Between Links -->
                      <div
                        v-if="index < links.length - 1"
                        class="absolute left-1/2 -bottom-3 transform -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity z-10"
                      >
                        <button
                          @click="
                            showAddLinkModal = true;
                            insertIndex = index + 1;
                          "
                          class="w-8 h-8 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors shadow-lg flex items-center justify-center"
                        >
                          <Icon name="heroicons:plus" class="h-4 w-4" />
                        </button>
                      </div>
                    </div>
                  </template>
                </draggable>

                <!-- Add Link Button -->
                <button
                  @click="
                    showAddLinkModal = true;
                    insertIndex = null;
                  "
                  class="w-full mt-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-600 transition-colors flex items-center justify-center"
                >
                  <Icon name="heroicons:plus" class="h-5 w-5 mr-2" />
                  Add Link
                </button>
              </div>

              <!-- Loading State -->
              <div
                v-if="loading"
                class="flex items-center justify-center py-12"
              >
                <div class="spinner mr-3"></div>
                <span class="text-gray-600">Loading your links...</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Panel - Live Preview -->
        <div
          v-show="showMobilePreview || !isMobile"
          class="lg:sticky lg:top-20 sm:lg:top-24 h-fit"
        >
          <div
            class="bg-gray-900 rounded-xl sm:rounded-2xl p-4 sm:p-8 shadow-xl"
          >
            <div class="mx-auto max-w-sm">
              <!-- Phone Frame -->
              <div
                class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-2xl overflow-hidden"
              >
                <div class="relative z-10">
                  <!-- Profile Section -->
                  <div class="text-center mb-4 sm:mb-6">
                    <img
                      :src="userProfile.avatar || '/default-avatar.png'"
                      class="w-20 h-20 sm:w-24 sm:h-24 mx-auto rounded-full object-cover mb-3 sm:mb-4"
                    />
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900">
                      {{ userProfile.name || "Your Name" }}
                    </h2>
                    <p class="text-sm sm:text-base text-gray-600 mt-1">
                      {{ userProfile.bio || "Your bio" }}
                    </p>
                  </div>

                  <!-- Links Preview -->
                  <div class="space-y-2 sm:space-y-3">
                    <a
                      v-for="link in activeLinks"
                      :key="link.id"
                      :href="link.url"
                      target="_blank"
                      class="block w-full py-3 px-4 bg-black text-white rounded-full text-center text-sm sm:text-base font-medium hover:bg-gray-800 transition-colors"
                    >
                      {{ link.title }}
                    </a>
                  </div>

                  <!-- Watermark -->
                  <div class="mt-6 sm:mt-8 text-center">
                    <p class="text-xs text-gray-500">Powered by</p>
                    <p class="text-sm font-semibold text-gray-700">NFC GO</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Link Modal -->
    <div
      v-if="showAddLinkModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
    >
      <div
        class="bg-white rounded-2xl p-4 sm:p-6 max-w-md w-full max-h-[90vh] overflow-y-auto"
      >
        <div class="flex items-center justify-between mb-4 sm:mb-6">
          <h3 class="text-lg sm:text-xl font-bold text-gray-900">
            Add New Link
          </h3>
          <button
            @click="closeAddLinkModal"
            class="p-2 hover:bg-gray-100 rounded-lg"
          >
            <Icon
              name="heroicons:x-mark"
              class="h-5 w-5 sm:h-6 sm:w-6 text-gray-600"
            />
          </button>
        </div>

        <!-- URL Input -->
        <div class="mb-6">
          <div class="relative">
            <Icon
              name="heroicons:magnifying-glass"
              class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400"
            />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="URL or App"
              class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
        </div>

        <!-- Platform Selection -->
        <div class="space-y-4">
          <h4 class="text-sm font-medium text-gray-700">Popular Platforms</h4>
          <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
            <button
              v-for="platform in popularPlatforms"
              :key="platform.id"
              @click="selectPlatform(platform)"
              class="flex flex-col items-center justify-center p-3 sm:p-4 rounded-xl hover:bg-gray-50 transition-colors"
            >
              <div
                :class="[
                  'w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center mb-2',
                  platform.color,
                ]"
              >
                <Icon
                  :name="platform.icon"
                  class="h-6 w-6 sm:h-7 sm:w-7 text-white"
                />
              </div>
              <span class="text-xs sm:text-sm text-gray-700">{{
                platform.name
              }}</span>
            </button>
          </div>

          <!-- Custom URL Option -->
          <button
            @click="selectCustomUrl"
            class="w-full p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center justify-center space-x-2"
          >
            <Icon name="heroicons:link" class="h-5 w-5 text-gray-600" />
            <span class="text-sm text-gray-700">Add Custom URL</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Platform Form Modal -->
    <div
      v-if="showPlatformForm"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
    >
      <div class="bg-white rounded-2xl p-4 sm:p-6 max-w-md w-full">
        <div class="flex items-center justify-between mb-4 sm:mb-6">
          <h3 class="text-lg sm:text-xl font-bold text-gray-900">
            {{
              selectedPlatform
                ? `Add ${selectedPlatform.name}`
                : "Add Custom Link"
            }}
          </h3>
          <button
            @click="closePlatformForm"
            class="p-2 hover:bg-gray-100 rounded-lg"
          >
            <Icon
              name="heroicons:x-mark"
              class="h-5 w-5 sm:h-6 sm:w-6 text-gray-600"
            />
          </button>
        </div>

        <form @submit.prevent="saveLink" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Title</label
            >
            <input
              v-model="linkForm.title"
              type="text"
              :placeholder="selectedPlatform?.placeholder || 'My Link'"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >URL</label
            >
            <input
              v-model="linkForm.url"
              type="url"
              :placeholder="
                selectedPlatform?.urlExample || 'https://example.com'
              "
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              required
            />
          </div>

          <div class="flex items-center space-x-3">
            <input
              type="checkbox"
              v-model="linkForm.is_active"
              id="activeLink"
              class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
            />
            <label for="activeLink" class="text-sm text-gray-700"
              >Make this link active</label
            >
          </div>

          <div class="flex space-x-3 pt-4">
            <button
              type="button"
              @click="closePlatformForm"
              class="flex-1 py-2 px-4 bg-gray-200 text-gray-800 rounded-full hover:bg-gray-300 transition-colors text-sm font-medium"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="savingLink"
              class="flex-1 py-2 px-4 bg-blue-600 text-white rounded-full hover:bg-blue-700 disabled:opacity-50 transition-colors text-sm font-medium"
            >
              <div v-if="savingLink" class="spinner mr-2"></div>
              Add Link
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import draggable from "vuedraggable";

// Layout
definePageMeta({
  layout: "user-dashboard",
  middleware: "auth",
});

// Composables & Utilities
const { $toast } = useNuxtApp();
const { $api } = useNuxtApp();
const config = useRuntimeConfig();

// Reactive data
const loading = ref(false);
const savingLink = ref(false);
const showAddLinkModal = ref(false);
const showPlatformForm = ref(false);
const showMobilePreview = ref(false);
const isMobile = ref(false);
const searchQuery = ref("");
const selectedPlatform = ref(null);
const insertIndex = ref(null);

// User profile data
const userProfile = reactive({
  name: "John Doe",
  bio: "Digital creator & entrepreneur",
  username: "johndoe",
  avatar: "/default-avatar.png",
});

const profileUrl = computed(() => {
  const baseUrl = config.public.appUrl || "https://nfcgo.app";
  return `${baseUrl}/Homepage/${userProfile.username}`;
});

const shortProfileUrl = computed(() => {
  return `nfcgo.app/Homepage/${userProfile.username}`;
});

// Stats
const stats = reactive({
  total_views: 1234,
  total_clicks: 567,
  ctr: "46.0",
});

// Platforms configuration
const popularPlatforms = [
  {
    id: "instagram",
    name: "Instagram",
    icon: "mdi:instagram",
    color: "bg-gradient-to-br from-purple-600 to-pink-500",
    placeholder: "Instagram Profile",
    urlExample: "https://instagram.com/yourusername",
  },
  {
    id: "facebook",
    name: "Facebook",
    icon: "mdi:facebook",
    color: "bg-blue-600",
    placeholder: "Facebook Page",
    urlExample: "https://facebook.com/yourusername",
  },
  {
    id: "tiktok",
    name: "TikTok",
    icon: "mdi:tiktok",
    color: "bg-black",
    placeholder: "TikTok Profile",
    urlExample: "https://tiktok.com/@yourusername",
  },
  {
    id: "youtube",
    name: "YouTube",
    icon: "mdi:youtube",
    color: "bg-red-600",
    placeholder: "YouTube Channel",
    urlExample: "https://youtube.com/@yourchannel",
  },
  {
    id: "spotify",
    name: "Spotify",
    icon: "mdi:spotify",
    color: "bg-green-600",
    placeholder: "Spotify Profile",
    urlExample: "https://open.spotify.com/user/yourusername",
  },
  {
    id: "whatsapp",
    name: "WhatsApp",
    icon: "mdi:whatsapp",
    color: "bg-green-500",
    placeholder: "WhatsApp Chat",
    urlExample: "https://wa.me/1234567890",
  },
  {
    id: "x",
    name: "X",
    icon: "mdi:twitter",
    color: "bg-black",
    placeholder: "X (Twitter) Profile",
    urlExample: "https://x.com/yourusername",
  },
  {
    id: "snapchat",
    name: "Snapchat",
    icon: "mdi:snapchat",
    color: "bg-yellow-400",
    placeholder: "Snapchat Profile",
    urlExample: "https://snapchat.com/add/yourusername",
  },
  {
    id: "linkedin",
    name: "LinkedIn",
    icon: "mdi:linkedin",
    color: "bg-blue-700",
    placeholder: "LinkedIn Profile",
    urlExample: "https://linkedin.com/in/yourusername",
  },
  {
    id: "telegram",
    name: "Telegram",
    icon: "mdi:telegram",
    color: "bg-blue-500",
    placeholder: "Telegram",
    urlExample: "https://t.me/yourusername",
  },
  {
    id: "website",
    name: "Website",
    icon: "heroicons:globe-alt",
    color: "bg-gray-700",
    placeholder: "My Website",
    urlExample: "https://yourwebsite.com",
  },
  {
    id: "email",
    name: "Email",
    icon: "heroicons:envelope",
    color: "bg-gray-600",
    placeholder: "Email Me",
    urlExample: "mailto:your@email.com",
  },
];

// Data arrays
const links = ref([]);

// Forms
const linkForm = reactive({
  title: "",
  url: "",
  platform: "",
  is_active: true,
});

// Computed
const activeLinks = computed(() => {
  return links.value
    .filter((link) => link.is_active)
    .sort((a, b) => (a.order || 0) - (b.order || 0));
});

// Methods
const getPlatformIcon = (platformId) => {
  const platform = popularPlatforms.find((p) => p.id === platformId);
  return platform?.icon || "heroicons:link";
};

const getPlatformColor = (platformId) => {
  const platform = popularPlatforms.find((p) => p.id === platformId);
  return platform?.color || "bg-gray-600";
};

const selectPlatform = (platform) => {
  selectedPlatform.value = platform;
  linkForm.platform = platform.id;
  linkForm.title = platform.placeholder;
  showAddLinkModal.value = false;
  showPlatformForm.value = true;
};

const selectCustomUrl = () => {
  selectedPlatform.value = null;
  linkForm.platform = "custom";
  linkForm.title = "";
  showAddLinkModal.value = false;
  showPlatformForm.value = true;
};

const loadLinks = async () => {
  try {
    loading.value = true;
    const { $api } = useNuxtApp();
    const response = await $api.get("/links"); // Added .get()

    if (response.success) {
      links.value = response.links;
    }
  } catch (error) {
    console.error("Error loading links:", error);
    $toast.error("Failed to load links");
  } finally {
    loading.value = false;
  }
};

const saveLink = async () => {
  try {
    savingLink.value = true;
    const { $api } = useNuxtApp();

    const response = await $api.post("/links", linkForm); // Changed to .post()

    if (response.success) {
      links.value.push(response.link);
      $toast.success("Link added successfully");
      closePlatformForm();
    }
  } catch (error) {
    console.error("Failed to save link:", error);
    $toast.error("Failed to save link");
  } finally {
    savingLink.value = false;
  }
};

const updateLink = async (link) => {
  try {
    const { $api } = useNuxtApp();
    await $api.put(`/links/${link.id}`, {
      // Changed to .put()
      title: link.title,
      url: link.url,
      is_active: link.is_active,
    });
    $toast.success("Link updated");
  } catch (error) {
    console.error("Failed to update link:", error);
    $toast.error("Failed to update link");
  }
};

const deleteLink = async (link) => {
  if (!confirm("Are you sure you want to delete this link?")) return;

  try {
    const { $api } = useNuxtApp();
    await $api.delete(`/links/${link.id}`); // Changed to .delete()

    const index = links.value.findIndex((l) => l.id === link.id);
    if (index > -1) {
      links.value.splice(index, 1);
    }

    $toast.success("Link deleted");
  } catch (error) {
    console.error("Failed to delete link:", error);
    $toast.error("Failed to delete link");
  }
};

const updateLinkOrder = async () => {
  try {
    const { $api } = useNuxtApp();

    const linksOrder = links.value.map((link, index) => ({
      id: link.id,
      order: index,
    }));

    await $api.post("/links/reorder", { links: linksOrder }); // Changed to .post()

    $toast.success("Link order updated");
  } catch (error) {
    console.error("Failed to update link order:", error);
    $toast.error("Failed to update link order");
  }
};

const toggleLink = async (link) => {
  try {
    link.is_active = !link.is_active;
    $toast.success(`Link ${link.is_active ? "activated" : "deactivated"}`);
  } catch (error) {
    console.error("Failed to toggle link:", error);
    link.is_active = !link.is_active;
    $toast.error("Failed to update link status");
  }
};

const copyProfileUrl = async () => {
  try {
    await navigator.clipboard.writeText(profileUrl.value);
    $toast.success("Profile URL copied to clipboard");
  } catch (error) {
    $toast.error("Failed to copy URL");
  }
};

const closeAddLinkModal = () => {
  showAddLinkModal.value = false;
  searchQuery.value = "";
  insertIndex.value = null;
};

const closePlatformForm = () => {
  showPlatformForm.value = false;
  selectedPlatform.value = null;
  // Reset form
  Object.assign(linkForm, {
    title: "",
    url: "",
    platform: "",
    is_active: true,
  });
};

// Check if mobile
const checkMobile = () => {
  isMobile.value = window.innerWidth < 1024;
};

// Initialize with sample data
const initializeSampleData = () => {
  links.value = [
    {
      id: 1,
      title: "Follow me on Instagram",
      url: "https://instagram.com/johndoe",
      platform: "instagram",
      is_active: true,
      click_count: 234,
      order: 0,
    },
    {
      id: 2,
      title: "Connect on LinkedIn",
      url: "https://linkedin.com/in/johndoe",
      platform: "linkedin",
      is_active: true,
      click_count: 156,
      order: 1,
    },
    {
      id: 3,
      title: "Subscribe to my YouTube",
      url: "https://youtube.com/@johndoe",
      platform: "youtube",
      is_active: false,
      click_count: 89,
      order: 2,
    },
  ];
};

// Lifecycle
onMounted(() => {
  checkMobile();
  window.addEventListener("resize", checkMobile);
  initializeSampleData();
});

onUnmounted(() => {
  window.removeEventListener("resize", checkMobile);
});
</script>

<style scoped>
/* Spinner */
.spinner {
  display: inline-block;
  width: 1rem;
  height: 1rem;
  border: 2px solid #f3f4f6;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Custom focus styles for blue theme */
input:focus,
textarea:focus,
select:focus {
  --tw-ring-color: #3b82f6;
}

/* Ensure proper text sizing on mobile */
@media (max-width: 640px) {
  input,
  textarea,
  select {
    font-size: 16px;
    /* Prevents zoom on iOS */
  }
}

/* Smooth transitions for drag and drop */
.sortable-ghost {
  opacity: 0.5;
}

.sortable-drag {
  cursor: move;
}

/* Custom scrollbar for modal */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f3f4f6;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>
