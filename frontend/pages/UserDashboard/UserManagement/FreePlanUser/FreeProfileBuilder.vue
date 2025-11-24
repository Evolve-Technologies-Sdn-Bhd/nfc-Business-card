<!-- pages/UserDashboard/Free Profile Builder - Example Only -->
<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-14 sm:h-16">
          <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">
              Profile Preview - Example
            </h1>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
      <!-- Center - Live Preview Only -->
      <div class="flex justify-center">
        <div class="w-full max-w-md">
          <div
            class="bg-gray-900 rounded-xl sm:rounded-2xl p-4 sm:p-8 shadow-xl"
          >
            <div class="mx-auto max-w-sm">
              <!-- Phone Frame -->
              <div
                class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-2xl overflow-hidden relative"
              >
                <!-- Dynamic Background -->
                <div
                  :style="{ backgroundColor: exampleProfile.backgroundColor }"
                  class="absolute inset-0 rounded-2xl sm:rounded-3xl"
                ></div>

                <div class="relative z-10">
                  <!-- Profile Section -->
                  <div class="text-center mb-4 sm:mb-6">
                    <img
                      :src="exampleProfile.image"
                      :class="[
                        'mx-auto object-cover mb-3 sm:mb-4',
                        exampleProfile.profileStyle === 'classic'
                          ? 'w-20 h-20 sm:w-24 sm:h-24 rounded-full'
                          : 'w-full h-24 sm:h-32 rounded-xl',
                      ]"
                    />
                    <h2
                      :style="{
                        fontFamily: exampleProfile.font,
                        color: exampleProfile.textColor,
                      }"
                      class="text-lg sm:text-xl font-bold"
                    >
                      {{ exampleProfile.name }}
                    </h2>
                    <p
                      :style="{ color: exampleProfile.textColor }"
                      class="text-sm sm:text-base mt-1 sm:mt-2 opacity-80"
                    >
                      {{ exampleProfile.position }}
                    </p>
                  </div>

                  <!-- Contact Info -->
                  <div class="space-y-2 sm:space-y-3 mb-4">
                    <a
                      :href="'tel:' + exampleProfile.contactNumber"
                      class="block w-full text-center py-2.5 sm:py-3 px-5 sm:px-6 bg-black text-white rounded-full text-sm sm:text-base transition-all hover:bg-gray-800"
                    >
                      📞 {{ exampleProfile.contactNumber }}
                    </a>
                    <a
                      :href="'mailto:' + exampleProfile.email"
                      class="block w-full text-center py-2.5 sm:py-3 px-5 sm:px-6 bg-black text-white rounded-full text-sm sm:text-base transition-all hover:bg-gray-800"
                    >
                      ✉️ {{ exampleProfile.email }}
                    </a>
                    <a
                      v-if="exampleProfile.website"
                      :href="exampleProfile.website"
                      target="_blank"
                      class="block w-full text-center py-2.5 sm:py-3 px-5 sm:px-6 bg-black text-white rounded-full text-sm sm:text-base transition-all hover:bg-gray-800"
                    >
                      🌐 Visit Website
                    </a>
                  </div>

                  <!-- Address -->
                  <div v-if="exampleProfile.address" class="text-center mb-4">
                    <p
                      :style="{ color: exampleProfile.textColor }"
                      class="text-xs sm:text-sm opacity-70"
                    >
                      📍 {{ exampleProfile.address }}
                    </p>
                  </div>

                  <!-- Watermark -->
                  <div class="mt-6 sm:mt-8 text-center">
                    <p
                      :style="{ color: exampleProfile.textColor }"
                      class="text-xs opacity-60"
                    >
                      Powered by
                    </p>
                    <p
                      :style="{ color: exampleProfile.textColor }"
                      class="text-sm font-semibold"
                    >
                      NFC GO
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from "~/stores/auth";

// Layout
definePageMeta({
  layout: "user-dashboard",
  middleware: ["auth"],
});

// Example profile data (static)
const exampleProfile = reactive({
  name: "John Doe",
  position: "Senior Marketing Manager",
  contactNumber: "+60 12-345 6789",
  email: "john.doe@company.com",
  website: "https://www.example.com",
  address: "123 Business Street, Kuala Lumpur, Malaysia",
  image: "/default-avatar.png",
  profileStyle: "classic",
  backgroundColor: "#FFFFFF",
  textColor: "#000000",
  font: "Inter, sans-serif",
});
</script>

<style scoped>
/* Add any custom styles here */
.animate-fade-in-up {
  animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
