<!-- pages/Homepage/[slug].vue -->
<template>
  <div
    class="min-h-screen"
    :class="getThemeClasses(profile?.theme || 'minimal')"
  >
    <!-- Loading State -->
    <div
      v-if="loading"
      class="min-h-screen flex items-center justify-center bg-secondary-100"
    >
      <div class="text-center">
        <div
          class="w-16 h-16 border-4 border-primary-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"
        ></div>
        <p class="text-secondary-600">Loading profile...</p>
      </div>
    </div>

    <!-- Error State -->
    <div
      v-else-if="error"
      class="min-h-screen flex items-center justify-center bg-secondary-100"
    >
      <div class="text-center max-w-md mx-auto px-4">
        <Icon
          name="heroicons:exclamation-triangle"
          class="h-16 w-16 text-warning-500 mx-auto mb-4"
        />
        <h1 class="text-2xl font-bold text-secondary-900 mb-2">
          Profile Not Found
        </h1>
        <p class="text-secondary-600 mb-6">
          The profile you're looking for doesn't exist or has been deactivated.
        </p>
        <a href="https://nfccard.app" class="btn btn-primary">
          Create Your Own NFC Card
        </a>
      </div>
    </div>

    <!-- Profile Content -->
    <div v-else class="relative">
      <!-- Profile Header -->
      <div
        :class="getHeaderClasses(profile?.theme || 'minimal')"
        class="relative overflow-hidden"
      >
        <div class="container mx-auto px-4 py-12 text-center relative z-10">
          <div class="mb-6">
            <img
              :src="profile?.profile_image || '/default-avatar.png'"
              :alt="profile?.name"
              class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-xl mx-auto"
            />
          </div>
          <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">
            {{ profile?.name }}
          </h1>
          <p class="text-xl text-white/90 mb-2">{{ profile?.title }}</p>
          <p v-if="profile?.company" class="text-lg text-white/80 mb-4">
            {{ profile?.company }}
          </p>
          <p
            v-if="profile?.bio"
            class="text-white/85 max-w-2xl mx-auto leading-relaxed"
          >
            {{ profile?.bio }}
          </p>
        </div>

        <!-- Theme-specific decorations -->
        <div
          v-if="profile?.theme === 'modern'"
          class="absolute inset-0 overflow-hidden"
        >
          <div
            class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full"
          ></div>
          <div
            class="absolute -bottom-10 -left-10 w-32 h-32 bg-white/10 rounded-full"
          ></div>
        </div>
        <div
          v-else-if="profile?.theme === 'creative'"
          class="absolute inset-0 overflow-hidden"
        >
          <div
            class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-yellow-400 via-red-500 to-pink-500"
          ></div>
        </div>
      </div>

      <!-- Profile Sections -->
      <div class="container mx-auto px-4 py-8 max-w-2xl">
        <div class="space-y-6">
          <!-- Contact Information -->
          <div v-if="hasContactInfo" class="card animate-fade-in-up">
            <div class="card-body">
              <h3
                class="text-lg font-semibold text-secondary-900 mb-4 flex items-center"
              >
                <Icon
                  name="heroicons:envelope"
                  class="h-5 w-5 mr-2 text-blue-600"
                />
                Contact Information
              </h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a
                  v-if="profile?.email"
                  :href="`mailto:${profile.email}`"
                  class="flex items-center p-3 rounded-lg border border-secondary-200 hover:border-primary-300 hover:bg-primary-50 transition-all duration-200"
                  @click="trackAction('email_click')"
                >
                  <Icon
                    name="heroicons:envelope"
                    class="h-5 w-5 text-secondary-400 mr-3"
                  />
                  <div>
                    <p class="text-sm font-medium text-secondary-900">Email</p>
                    <p class="text-xs text-secondary-600">
                      {{ profile.email }}
                    </p>
                  </div>
                </a>

                <a
                  v-if="profile?.phone"
                  :href="`tel:${profile.phone}`"
                  class="flex items-center p-3 rounded-lg border border-secondary-200 hover:border-primary-300 hover:bg-primary-50 transition-all duration-200"
                  @click="trackAction('phone_click')"
                >
                  <Icon
                    name="heroicons:phone"
                    class="h-5 w-5 text-secondary-400 mr-3"
                  />
                  <div>
                    <p class="text-sm font-medium text-secondary-900">Phone</p>
                    <p class="text-xs text-secondary-600">
                      {{ profile.phone }}
                    </p>
                  </div>
                </a>

                <a
                  v-if="profile?.website"
                  :href="profile.website"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="flex items-center p-3 rounded-lg border border-secondary-200 hover:border-primary-300 hover:bg-primary-50 transition-all duration-200"
                  @click="trackAction('website_click')"
                >
                  <Icon
                    name="heroicons:globe-alt"
                    class="h-5 w-5 text-secondary-400 mr-3"
                  />
                  <div>
                    <p class="text-sm font-medium text-secondary-900">
                      Website
                    </p>
                    <p class="text-xs text-secondary-600">
                      {{ formatUrl(profile.website) }}
                    </p>
                  </div>
                </a>

                <div
                  v-if="profile?.location"
                  class="flex items-center p-3 rounded-lg border border-secondary-200"
                >
                  <Icon
                    name="heroicons:map-pin"
                    class="h-5 w-5 text-secondary-400 mr-3"
                  />
                  <div>
                    <p class="text-sm font-medium text-secondary-900">
                      Location
                    </p>
                    <p class="text-xs text-secondary-600">
                      {{ profile.location }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Social Links -->
          <div
            v-if="profile?.social_links?.length"
            class="card animate-fade-in-up"
            style="animation-delay: 0.1s"
          >
            <div class="card-body">
              <h3
                class="text-lg font-semibold text-secondary-900 mb-4 flex items-center"
              >
                <Icon
                  name="heroicons:share"
                  class="h-5 w-5 mr-2 text-purple-600"
                />
                Connect With Me
              </h3>
              <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <a
                  v-for="link in profile.social_links"
                  :key="link.id"
                  :href="link.url"
                  target="_blank"
                  rel="noopener noreferrer"
                  @click="
                    trackAction('social_click', { platform: link.platform })
                  "
                  class="flex flex-col items-center p-4 rounded-lg border border-secondary-200 hover:border-primary-300 hover:bg-primary-50 transition-all duration-200 group"
                >
                  <Icon
                    :name="getSocialIcon(link.platform)"
                    class="h-8 w-8 mb-2 group-hover:scale-110 transition-transform"
                    :class="getSocialColor(link.platform)"
                  />
                  <span class="text-sm font-medium text-secondary-900">{{
                    link.platform
                  }}</span>
                </a>
              </div>
            </div>
          </div>

          <!-- Custom Links -->
          <div
            v-if="profile?.custom_links?.length"
            class="card animate-fade-in-up"
            style="animation-delay: 0.2s"
          >
            <div class="card-body">
              <h3
                class="text-lg font-semibold text-secondary-900 mb-4 flex items-center"
              >
                <Icon
                  name="heroicons:link"
                  class="h-5 w-5 mr-2 text-orange-600"
                />
                Important Links
              </h3>
              <div class="space-y-3">
                <a
                  v-for="link in profile.custom_links"
                  :key="link.id"
                  :href="link.url"
                  target="_blank"
                  rel="noopener noreferrer"
                  @click="
                    trackAction('custom_link_click', { title: link.title })
                  "
                  class="flex items-center justify-between p-4 rounded-lg border border-secondary-200 hover:border-primary-300 hover:bg-primary-50 transition-all duration-200 group"
                >
                  <div class="flex items-center">
                    <div
                      class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3 group-hover:bg-orange-200 transition-colors"
                    >
                      <Icon
                        name="heroicons:link"
                        class="h-5 w-5 text-orange-600"
                      />
                    </div>
                    <div>
                      <h4 class="font-medium text-secondary-900">
                        {{ link.title }}
                      </h4>
                      <p
                        v-if="link.description"
                        class="text-sm text-secondary-600"
                      >
                        {{ link.description }}
                      </p>
                    </div>
                  </div>
                  <Icon
                    name="heroicons:arrow-top-right-on-square"
                    class="h-4 w-4 text-secondary-400 group-hover:text-primary-600"
                  />
                </a>
              </div>
            </div>
          </div>

          <!-- Portfolio -->
          <div
            v-if="profile?.portfolio?.length"
            class="card animate-fade-in-up"
            style="animation-delay: 0.3s"
          >
            <div class="card-body">
              <h3
                class="text-lg font-semibold text-secondary-900 mb-4 flex items-center"
              >
                <Icon
                  name="heroicons:photo"
                  class="h-5 w-5 mr-2 text-green-600"
                />
                Portfolio
              </h3>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div
                  v-for="item in profile.portfolio"
                  :key="item.id"
                  class="group relative overflow-hidden rounded-lg border border-secondary-200 hover:shadow-lg transition-all duration-200"
                >
                  <div class="aspect-video bg-secondary-100 relative">
                    <img
                      v-if="item.image"
                      :src="item.image"
                      :alt="item.title"
                      class="w-full h-full object-cover"
                    />
                    <div v-else class="flex items-center justify-center h-full">
                      <Icon
                        name="heroicons:photo"
                        class="h-8 w-8 text-secondary-400"
                      />
                    </div>
                    <div
                      v-if="item.url"
                      class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-200 flex items-center justify-center"
                    >
                      <a
                        :href="item.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        @click="
                          trackAction('portfolio_click', { title: item.title })
                        "
                        class="opacity-0 group-hover:opacity-100 btn btn-primary btn-sm"
                      >
                        <Icon
                          name="heroicons:arrow-top-right-on-square"
                          class="h-4 w-4 mr-2"
                        />
                        View Project
                      </a>
                    </div>
                  </div>
                  <div class="p-3">
                    <h4 class="font-medium text-secondary-900 mb-1">
                      {{ item.title }}
                    </h4>
                    <p class="text-sm text-secondary-600">
                      {{ item.description }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Skills Section -->
          <div
            v-if="profile?.skills?.length"
            class="card animate-fade-in-up"
            style="animation-delay: 0.4s"
          >
            <div class="card-body">
              <h3
                class="text-lg font-semibold text-secondary-900 mb-4 flex items-center"
              >
                <Icon
                  name="heroicons:star"
                  class="h-5 w-5 mr-2 text-yellow-600"
                />
                Skills & Expertise
              </h3>
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="skill in profile.skills"
                  :key="skill.id"
                  class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-primary-100 text-primary-800 font-medium"
                >
                  {{ skill.name }}
                </span>
              </div>
            </div>
          </div>

          <!-- Testimonials -->
          <div
            v-if="profile?.testimonials?.length"
            class="card animate-fade-in-up"
            style="animation-delay: 0.5s"
          >
            <div class="card-body">
              <h3
                class="text-lg font-semibold text-secondary-900 mb-4 flex items-center"
              >
                <Icon
                  name="heroicons:chat-bubble-left-ellipsis"
                  class="h-5 w-5 mr-2 text-indigo-600"
                />
                What People Say
              </h3>
              <div class="space-y-4">
                <div
                  v-for="testimonial in profile.testimonials"
                  :key="testimonial.id"
                  class="p-4 rounded-lg bg-secondary-50 border border-secondary-200"
                >
                  <p class="text-secondary-700 mb-3 italic">
                    "{{ testimonial.content }}"
                  </p>
                  <div class="flex items-center">
                    <img
                      v-if="testimonial.avatar"
                      :src="testimonial.avatar"
                      :alt="testimonial.author"
                      class="w-8 h-8 rounded-full object-cover mr-3"
                    />
                    <div
                      v-else
                      class="w-8 h-8 bg-secondary-300 rounded-full flex items-center justify-center mr-3"
                    >
                      <Icon
                        name="heroicons:user"
                        class="h-4 w-4 text-secondary-600"
                      />
                    </div>
                    <div>
                      <p class="text-sm font-medium text-secondary-900">
                        {{ testimonial.author }}
                      </p>
                      <p class="text-xs text-secondary-600">
                        {{ testimonial.position }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row gap-4 pt-6">
            <button @click="saveContact" class="btn btn-primary flex-1">
              <Icon name="heroicons:plus" class="h-4 w-4 mr-2" />
              Save Contact
            </button>
            <button @click="shareProfile" class="btn btn-outline flex-1">
              <Icon name="heroicons:share" class="h-4 w-4 mr-2" />
              Share Profile
            </button>
          </div>

          <!-- Powered By Footer -->
          <div class="text-center pt-8 border-t border-secondary-200">
            <p class="text-sm text-secondary-500 mb-2">Powered by</p>
            <a
              href="https://nfccard.app"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center text-primary-600 hover:text-primary-700 font-medium"
            >
              <Icon name="heroicons:credit-card" class="h-4 w-4 mr-2" />
              NFCCard.app
            </a>
            <p class="text-xs text-secondary-400 mt-2">
              Create your own smart NFC business card
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Contact Save Success Modal -->
    <div
      v-if="showSaveSuccess"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg p-6 max-w-sm mx-4 text-center">
        <div
          class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4"
        >
          <Icon name="heroicons:check" class="h-8 w-8 text-green-600" />
        </div>
        <h3 class="text-lg font-semibold text-secondary-900 mb-2">
          Contact Saved!
        </h3>
        <p class="text-secondary-600 mb-4">
          {{ profile?.name }}'s contact has been saved to your device.
        </p>
        <button @click="showSaveSuccess = false" class="btn btn-primary w-full">
          Great!
        </button>
      </div>
    </div>

    <!-- Share Modal -->
    <div
      v-if="showShareModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg p-6 max-w-md mx-4">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-secondary-900">
            Share Profile
          </h3>
          <button
            @click="showShareModal = false"
            class="text-secondary-400 hover:text-secondary-600"
          >
            <Icon name="heroicons:x-mark" class="h-6 w-6" />
          </button>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2"
              >Profile URL</label
            >
            <div class="flex items-center space-x-2">
              <input
                :value="profileUrl"
                readonly
                class="input input-bordered flex-1 text-sm"
              />
              <button @click="copyToClipboard" class="btn btn-outline btn-sm">
                <Icon name="heroicons:clipboard" class="h-4 w-4" />
              </button>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <button
              @click="shareVia('whatsapp')"
              class="btn btn-outline justify-start"
            >
              <Icon
                name="simple-icons:whatsapp"
                class="h-4 w-4 mr-2 text-green-600"
              />
              WhatsApp
            </button>
            <button
              @click="shareVia('telegram')"
              class="btn btn-outline justify-start"
            >
              <Icon
                name="simple-icons:telegram"
                class="h-4 w-4 mr-2 text-blue-600"
              />
              Telegram
            </button>
            <button
              @click="shareVia('twitter')"
              class="btn btn-outline justify-start"
            >
              <Icon
                name="simple-icons:twitter"
                class="h-4 w-4 mr-2 text-blue-400"
              />
              Twitter
            </button>
            <button
              @click="shareVia('linkedin')"
              class="btn btn-outline justify-start"
            >
              <Icon
                name="simple-icons:linkedin"
                class="h-4 w-4 mr-2 text-blue-700"
              />
              LinkedIn
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";

// Meta and SEO
const route = useRoute();
const slug = computed(() => route.params?.slug);

// Reactive data
const loading = ref(true);
const error = ref(false);
const profile = ref(null);
const showSaveSuccess = ref(false);
const showShareModal = ref(false);

// Computed properties
const hasContactInfo = computed(() => {
  return (
    profile.value?.email ||
    profile.value?.phone ||
    profile.value?.website ||
    profile.value?.location
  );
});

const profileUrl = computed(() => {
  if (process.client) {
    return window.location.href;
  }
  return `https://nfccard.app/Homepage/${slug.value}`;
});

// Methods
const loadProfile = async () => {
  try {
    loading.value = true;
    const { $api } = useNuxtApp();
    const response = await $api.get(`/profiles/${slug.value}`);

    if (response.success) {
      profile.value = response.data;

      // Set SEO meta
      useSeoMeta({
        title: `${profile.value.name} - ${
          profile.value.title || "Digital Business Card"
        }`,
        description: profile.value.bio || `Connect with ${profile.value.name}`,
        ogTitle: `${profile.value.name} - Digital Business Card`,
        ogDescription:
          profile.value.bio || `Connect with ${profile.value.name}`,
        ogImage: profile.value.profile_image,
        ogUrl: profileUrl.value,
        twitterCard: "summary_large_image",
      });
    }
  } catch (err) {
    console.error("Error loading profile:", err);
    error.value = true;
  } finally {
    loading.value = false;
  }
};

const trackAction = async (action, data = {}) => {
  try {
    const { $api } = useNuxtApp();
    await $api.post("/analytics/track", {
      slug: slug.value,
      action,
      data,
      user_agent: navigator.userAgent,
      referrer: document.referrer,
      timestamp: new Date().toISOString(),
    });
  } catch (error) {
    console.error("Error tracking action:", error);
  }
};

const saveContact = async () => {
  try {
    // Generate vCard
    const vCard = generateVCard();

    // Create download link
    const blob = new Blob([vCard], { type: "text/vcard" });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = `${profile.value.name.replace(/\s+/g, "_")}.vcf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);

    // Track action and show success
    await trackAction("contact_save");
    showSaveSuccess.value = true;
  } catch (error) {
    console.error("Error saving contact:", error);
    alert("Error saving contact. Please try again.");
  }
};

const generateVCard = () => {
  const vCard = [
    "BEGIN:VCARD",
    "VERSION:3.0",
    `FN:${profile.value.name}`,
    `ORG:${profile.value.company || ""}`,
    `TITLE:${profile.value.title || ""}`,
    `EMAIL:${profile.value.email || ""}`,
    `TEL:${profile.value.phone || ""}`,
    `URL:${profile.value.website || ""}`,
    `NOTE:${profile.value.bio || ""}`,
    "END:VCARD",
  ].join("\r\n");

  return vCard;
};

const shareProfile = () => {
  if (navigator.share) {
    navigator.share({
      title: `${profile.value.name} - Digital Business Card`,
      text: profile.value.bio || `Check out ${profile.value.name}'s profile`,
      url: profileUrl.value,
    });
  } else {
    showShareModal.value = true;
  }
};

const copyToClipboard = async () => {
  try {
    await navigator.clipboard.writeText(profileUrl.value);
    alert("Link copied to clipboard!");
  } catch (error) {
    console.error("Error copying to clipboard:", error);
  }
};

const shareVia = (platform) => {
  const url = encodeURIComponent(profileUrl.value);
  const text = encodeURIComponent(`Check out ${profile.value.name}'s profile`);

  const shareUrls = {
    whatsapp: `https://wa.me/?text=${text}%20${url}`,
    telegram: `https://t.me/share/url?url=${url}&text=${text}`,
    twitter: `https://twitter.com/intent/tweet?text=${text}&url=${url}`,
    linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${url}`,
  };

  window.open(shareUrls[platform], "_blank");
};

const getSocialIcon = (platform) => {
  const icons = {
    Instagram: "simple-icons:instagram",
    Twitter: "simple-icons:twitter",
    LinkedIn: "simple-icons:linkedin",
    Facebook: "simple-icons:facebook",
    YouTube: "simple-icons:youtube",
    TikTok: "simple-icons:tiktok",
    GitHub: "simple-icons:github",
    Behance: "simple-icons:behance",
    Dribbble: "simple-icons:dribbble",
  };
  return icons[platform] || "heroicons:link";
};

const getSocialColor = (platform) => {
  const colors = {
    Instagram: "text-pink-600",
    Twitter: "text-blue-400",
    LinkedIn: "text-blue-700",
    Facebook: "text-blue-600",
    YouTube: "text-red-600",
    TikTok: "text-black",
    GitHub: "text-gray-800",
    Behance: "text-blue-500",
    Dribbble: "text-pink-500",
  };
  return colors[platform] || "text-gray-600";
};

const formatUrl = (url) => {
  return url.replace(/^https?:\/\//, "").replace(/\/$/, "");
};

const getThemeClasses = (theme) => {
  const themes = {
    minimal: "bg-white",
    modern: "bg-gradient-to-br from-slate-50 to-blue-50",
    creative: "bg-gradient-to-br from-purple-50 to-pink-50",
    professional: "bg-gradient-to-br from-gray-50 to-slate-100",
    dark: "bg-gradient-to-br from-gray-900 to-black",
  };
  return themes[theme] || themes.minimal;
};

const getHeaderClasses = (theme) => {
  const headers = {
    minimal: "bg-gradient-to-br from-blue-600 to-blue-800",
    modern: "bg-gradient-to-br from-indigo-600 to-purple-700",
    creative: "bg-gradient-to-br from-pink-500 to-orange-500",
    professional: "bg-gradient-to-br from-gray-700 to-gray-900",
    dark: "bg-gradient-to-br from-purple-900 to-indigo-900",
  };
  return headers[theme] || headers.minimal;
};

// Lifecycle
onMounted(() => {
  loadProfile();
});
</script>

<style scoped>
@keyframes fade-in-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in-up {
  animation: fade-in-up 0.6s ease-out forwards;
}

.card {
  @apply bg-white rounded-lg shadow-sm border border-secondary-200;
}

.card-body {
  @apply p-6;
}

.btn {
  @apply px-4 py-2 rounded-lg font-medium transition-all duration-200 inline-flex items-center justify-center;
}

.btn-primary {
  @apply bg-primary-600 text-white hover:bg-primary-700;
}

.btn-outline {
  @apply border border-secondary-300 text-secondary-700 hover:bg-secondary-50;
}

.btn-sm {
  @apply px-3 py-1 text-sm;
}

.btn-xs {
  @apply px-2 py-1 text-xs;
}

.input {
  @apply px-3 py-2 border border-secondary-300 rounded-lg focus:outline-none focus:border-primary-500;
}

.input-bordered {
  @apply border-secondary-300;
}

.select {
  @apply px-3 py-2 border border-secondary-300 rounded-lg focus:outline-none focus:border-primary-500 bg-white;
}

.select-bordered {
  @apply border-secondary-300;
}

/* Responsive improvements */
@media (max-width: 640px) {
  .container {
    @apply px-2;
  }
}
</style>
