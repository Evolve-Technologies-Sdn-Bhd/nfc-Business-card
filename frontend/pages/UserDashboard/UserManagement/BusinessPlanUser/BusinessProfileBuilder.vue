<!-- pages/UserDashboard/ProfileBuilder.vue -->
<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Global Loading Overlay -->
    <Transition
      enter-active-class="transition-opacity duration-200"
      leave-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      leave-to-class="opacity-0"
    >
      <div
        v-if="pageLoading"
        class="fixed inset-0 bg-white/80 backdrop-blur-sm z-50 flex items-center justify-center"
      >
        <div class="text-center">
          <div
            class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-blue-600 border-t-transparent"
          ></div>
          <p class="mt-4 text-sm text-gray-600">Loading your profile...</p>
        </div>
      </div>
    </Transition>

    <!-- Header -->
    <div class="bg-white shadow-sm border-b sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-14 sm:h-16">
          <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">
              Profile Builder
            </h1>
          </div>
          <div class="flex items-center gap-2 sm:gap-3">
            <!-- NFC Card Selector -->
            <div class="relative">
              <button
                @click="toggleCardSelector"
                :disabled="loadingCards || userNfcCards.length === 0"
                class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm sm:text-base border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed bg-white hover:bg-gray-50 transition-colors flex items-center gap-2"
              >
                <Icon name="heroicons:credit-card" class="w-4 h-4" />
                <span v-if="loadingCards">Loading...</span>
                <span v-else-if="userNfcCards.length === 0">No Cards</span>
                <span v-else-if="getSelectedCard()">
                  {{
                    getSelectedCard().nfc_card_id ||
                    "Card #" + getSelectedCard().id
                  }}
                </span>
                <span v-else>Select Card</span>
                <Icon
                  name="heroicons:chevron-down"
                  class="w-4 h-4 text-gray-400"
                />
              </button>

              <!-- Card Selector Dropdown -->
              <div
                v-if="showCardSelector"
                class="absolute right-0 mt-2 w-72 sm:w-80 bg-white rounded-xl shadow-xl border border-gray-200 z-50 max-h-96 overflow-y-auto"
              >
                <div class="p-3 space-y-2">
                  <div
                    v-for="card in userNfcCards"
                    :key="card.id"
                    @click="selectNfcCard(card.id)"
                    :class="[
                      'relative cursor-pointer rounded-lg border-2 p-3 transition-all hover:shadow-md',
                      selectedNfcCardId === card.id
                        ? 'border-blue-500 bg-gradient-to-br from-blue-50 to-blue-100'
                        : 'border-gray-200 hover:border-gray-300 bg-white',
                    ]"
                  >
                    <!-- Card Mini Design -->
                    <div class="flex items-start justify-between gap-3">
                      <!-- Left: Card Info -->
                      <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                          <Icon
                            name="heroicons:credit-card-solid"
                            :class="[
                              'w-5 h-5',
                              selectedNfcCardId === card.id
                                ? 'text-blue-600'
                                : 'text-gray-400',
                            ]"
                          />
                          <p class="text-sm font-bold text-gray-900 truncate">
                            {{ card.nfc_card_id || "Card #" + card.id }}
                          </p>
                        </div>

                        <p class="text-xs text-gray-600 mb-2 truncate">
                          {{ card.card_owner || "No owner" }}
                        </p>

                        <!-- Status & Plan Badges -->
                        <div class="flex items-center gap-2 flex-wrap">
                          <span
                            :class="[
                              'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                              card.status === 'active'
                                ? 'bg-green-100 text-green-800'
                                : card.status === 'pending'
                                ? 'bg-yellow-100 text-yellow-800'
                                : 'bg-gray-100 text-gray-800',
                            ]"
                          >
                            {{ card.status || "pending" }}
                          </span>
                          <span
                            :class="[
                              'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold uppercase',
                              getPlanBadgeClass(card.subscription_plan),
                            ]"
                          >
                            {{ card.subscription_plan || "free" }}
                          </span>
                        </div>
                      </div>

                      <!-- Right: Selected Indicator -->
                      <Icon
                        v-if="selectedNfcCardId === card.id"
                        name="heroicons:check-circle"
                        class="w-5 h-5 text-blue-500 flex-shrink-0"
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Apply Design Button -->
            <div class="relative">
              <button
                @click="showApplyDesignModal = true"
                :disabled="!selectedNfcCardId || (userNfcCards.length <= 1 && allEmployeeCards.length === 0)"
                class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm sm:text-base border border-gray-300 rounded-full hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2"
              >
                <Icon name="heroicons:paint-brush" class="w-4 h-4" />
                <span class="hidden sm:inline">Apply Design</span>
              </button>
            </div>

            <!-- Save Button -->
            <button
              @click="saveProfile"
              :disabled="saving || !selectedNfcCardId"
              class="px-4 sm:px-6 py-1.5 sm:py-2 text-sm sm:text-base bg-blue-600 text-white rounded-full hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {{ saving ? "Saving..." : "Save" }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
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
        <!-- Left Panel - Editor -->
        <div
          v-show="!showMobilePreview || !isMobile"
          class="space-y-4 sm:space-y-6"
        >
          <!-- Tab Navigation -->
          <div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-1">
            <div class="flex space-x-1">
              <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                :class="[
                  'flex-1 py-1.5 sm:py-2 px-2 sm:px-4 rounded-md sm:rounded-lg text-xs sm:text-sm font-medium transition-all',
                  activeTab === tab.id
                    ? 'bg-blue-100 text-blue-700'
                    : 'text-gray-600 hover:text-gray-900',
                ]"
              >
                <Icon
                  :name="tab.icon"
                  class="w-3 h-3 sm:w-4 sm:h-4 inline mr-1 sm:mr-2"
                />
                <span class="hidden sm:inline">{{ tab.name }}</span>
                <span class="sm:hidden">{{ tab.shortName || tab.name }}</span>
              </button>
            </div>
          </div>

          <!-- Tab Content -->
          <div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-4 sm:p-6">
            <!-- Profile Tab -->
            <div v-if="activeTab === 'profile'" class="space-y-4 sm:space-y-6">
              <!-- No Cards Warning (only show if no cards and not loading) -->
              <div
                v-if="!loadingCards && userNfcCards.length === 0"
                class="bg-yellow-50 border border-yellow-200 rounded-lg p-4"
              >
                <div class="flex">
                  <Icon
                    name="heroicons:exclamation-triangle"
                    class="h-5 w-5 text-yellow-400"
                  />
                  <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                      No NFC Cards Found
                    </h3>
                    <p class="text-sm text-yellow-700 mt-1">
                      You need to order an NFC card before creating a profile.
                      Go to Card Management to order your card.
                    </p>
                    <NuxtLink
                      to="/UserDashboard/UserManagement/BusinessPlanUser/BusinessCardManagement"
                      class="inline-flex items-center mt-2 text-sm font-medium text-yellow-800 hover:text-yellow-900"
                    >
                      Go to Card Management
                      <Icon name="heroicons:arrow-right" class="ml-1 h-4 w-4" />
                    </NuxtLink>
                  </div>
                </div>
              </div>

              <!-- Profile Picture & Layout -->
              <div>
                <h3 class="text-sm font-medium text-gray-700 mb-3 sm:mb-4">
                  Profile Style
                </h3>
                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                  <div
                    v-for="style in profileStyles"
                    :key="style.id"
                    @click="profileData.profileStyle = style.id"
                    :class="[
                      'relative cursor-pointer rounded-lg border-2 p-3 sm:p-4 transition-all min-h-[100px] flex flex-col items-center justify-center',
                      profileData.profileStyle === style.id
                        ? 'border-blue-500 bg-blue-50'
                        : 'border-gray-200 hover:border-gray-300',
                    ]"
                  >
                    <div class="flex items-center justify-center mb-2">
                      <div
                        v-if="style.id === 'classic'"
                        class="w-12 h-12 sm:w-16 sm:h-16 bg-gray-200 rounded-full"
                      ></div>
                      <div
                        v-else
                        class="w-full h-16 sm:h-20 bg-gray-200 rounded-lg"
                      ></div>
                    </div>
                    <p class="text-xs sm:text-sm text-center font-medium">
                      {{ style.name }}
                    </p>
                    <Icon
                      v-if="profileData.profileStyle === style.id"
                      name="heroicons:check-circle"
                      class="absolute top-1 right-1 sm:top-2 sm:right-2 w-4 h-4 sm:w-5 sm:h-5 text-blue-500"
                    />
                  </div>
                </div>
              </div>

              <!-- Basic Info -->
              <div class="space-y-3 sm:space-y-4">
                <h3 class="text-sm font-medium text-gray-700">
                  Basic Information
                </h3>

                <!-- Profile Image Upload -->
                <ProfileImageUpload
                  v-model="profileData.image"
                  upload-endpoint="/upload/profile-image"
                  delete-endpoint="/upload/profile-image"
                  label="Profile Picture"
                  help-text="JPG, PNG, GIF or WebP. Max 5MB"
                  :nfc-card-id="selectedNfcCardId"
                  @upload-success="handleProfileImageUpload"
                />

                <!-- Company Logo Upload -->
                <ProfileImageUpload
                  v-model="profileData.companyLogo"
                  upload-endpoint="/upload/company-logo"
                  delete-endpoint="/upload/company-logo"
                  label="Company Logo"
                  help-text="Will appear as background watermark"
                  alt-text="Company logo"
                  :nfc-card-id="selectedNfcCardId"
                  @upload-success="handleCompanyLogoUpload"
                />

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Name</label
                  >
                  <input
                    v-model="profileData.name"
                    type="text"
                    placeholder="Your name"
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Position</label
                  >
                  <input
                    v-model="profileData.position"
                    type="text"
                    placeholder="Your job title"
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Qualification
                    <span class="text-xs text-gray-500">(Optional)</span></label
                  >
                  <input
                    v-model="profileData.qualification"
                    type="text"
                    placeholder="e.g., Bachelor of Business Administration"
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Bio</label
                  >
                  <textarea
                    v-model="profileData.bio"
                    rows="4"
                    placeholder="Tell visitors about yourself..."
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  ></textarea>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Contact Number</label
                  >
                  <input
                    v-model="profileData.contactNumber"
                    type="tel"
                    placeholder="+60 12-345 6789"
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Email Address</label
                  >
                  <input
                    v-model="profileData.email"
                    type="email"
                    placeholder="your@email.com"
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Website
                    <span class="text-xs text-gray-500">(Optional)</span></label
                  >
                  <input
                    v-model="profileData.website"
                    type="url"
                    placeholder="https://yourwebsite.com"
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Address</label
                  >
                  <textarea
                    v-model="profileData.address"
                    rows="2"
                    placeholder="Your business address"
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  ></textarea>
                </div>
              </div>

              <!-- Stats Section -->
              <div class="space-y-3 sm:space-y-4 pt-4 border-t">
                <h3 class="text-sm font-medium text-gray-700">
                  Profile Statistics
                </h3>
                <p class="text-xs text-gray-500">
                  Add up to 3 statistics to showcase your achievements
                </p>
                <div
                  v-for="(stat, index) in profileData.stats"
                  :key="index"
                  class="grid grid-cols-2 gap-3"
                >
                  <input
                    v-model="stat.num"
                    type="text"
                    :placeholder="'e.g., 10+'"
                    class="px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                  <input
                    v-model="stat.label"
                    type="text"
                    :placeholder="'e.g., Years Experience'"
                    class="px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>
            </div>

            <!-- Company Tab -->
            <div v-if="activeTab === 'company'" class="space-y-4 sm:space-y-6">
              <!-- Company Information -->
              <div class="space-y-3 sm:space-y-4">
                <h3 class="text-sm font-medium text-gray-700">
                  Company Information
                </h3>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Company Logo Text</label
                  >
                  <input
                    v-model="profileData.companyLogoText"
                    type="text"
                    placeholder="e.g., COMPANY"
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                  <p class="text-xs text-gray-500 mt-1">
                    Text overlay for company logo
                  </p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Company Name</label
                  >
                  <input
                    v-model="profileData.companyName"
                    type="text"
                    placeholder="e.g., ABC Corporation Sdn. Bhd."
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Company Registration No.</label
                  >
                  <input
                    v-model="profileData.companyRegistrationNo"
                    type="text"
                    placeholder="e.g., 202201234567 (1234567-A)"
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Department</label
                  >
                  <input
                    v-model="profileData.companyDepartment"
                    type="text"
                    placeholder="e.g., Sales & Marketing"
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>

              <!-- Company Address -->
              <div class="space-y-3 sm:space-y-4 pt-4 border-t">
                <h3 class="text-sm font-medium text-gray-700">
                  Company Address
                </h3>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Location Name</label
                  >
                  <input
                    v-model="profileData.addressName"
                    type="text"
                    placeholder="e.g., Headquarters"
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Street Address</label
                  >
                  <input
                    v-model="profileData.addressStreet"
                    type="text"
                    placeholder="e.g., 123 Business Street"
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Area</label
                  >
                  <input
                    v-model="profileData.addressArea"
                    type="text"
                    placeholder="e.g., Taman ABC"
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >City, State</label
                  >
                  <input
                    v-model="profileData.addressCityState"
                    type="text"
                    placeholder="e.g., 50000 Kuala Lumpur"
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Country</label
                  >
                  <input
                    v-model="profileData.addressCountry"
                    type="text"
                    placeholder="e.g., Malaysia"
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Map URL</label
                  >
                  <input
                    v-model="profileData.addressMapUrl"
                    type="url"
                    placeholder="e.g., https://maps.google.com/..."
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                  <p class="text-xs text-gray-500 mt-1">
                    Google Maps or other map service link
                  </p>
                </div>
              </div>

              <!-- Contact Methods -->
              <div class="space-y-3 sm:space-y-4 pt-4 border-t">
                <h3 class="text-sm font-medium text-gray-700">
                  Contact Methods
                </h3>
                <p class="text-xs text-gray-500">
                  Customize labels and values for contact buttons
                </p>

                <!-- Phone -->
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1"
                      >Phone Label</label
                    >
                    <input
                      v-model="profileData.phoneLabel"
                      type="text"
                      placeholder="e.g., Call Us"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1"
                      >Phone Number</label
                    >
                    <input
                      v-model="profileData.phoneNumber"
                      type="tel"
                      placeholder="e.g., +60123456789"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                </div>

                <!-- Email -->
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1"
                      >Email Label</label
                    >
                    <input
                      v-model="profileData.emailLabel"
                      type="text"
                      placeholder="e.g., Email Us"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1"
                      >Email Address</label
                    >
                    <input
                      v-model="profileData.emailAddress"
                      type="email"
                      placeholder="e.g., info@company.com"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                </div>

                <!-- WhatsApp -->
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1"
                      >WhatsApp Label</label
                    >
                    <input
                      v-model="profileData.whatsappLabel"
                      type="text"
                      placeholder="e.g., WhatsApp"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1"
                      >WhatsApp Number</label
                    >
                    <input
                      v-model="profileData.whatsappNumber"
                      type="tel"
                      placeholder="e.g., +60123456789"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                </div>

                <!-- Website -->
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1"
                      >Website Label</label
                    >
                    <input
                      v-model="profileData.websiteLabel"
                      type="text"
                      placeholder="e.g., Visit Website"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1"
                      >Website URL</label
                    >
                    <input
                      v-model="profileData.websiteUrl"
                      type="url"
                      placeholder="e.g., https://company.com"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Services Tab -->
            <div v-if="activeTab === 'services'" class="space-y-4 sm:space-y-6">
              <!-- Services Section -->
              <div class="space-y-3 sm:space-y-4">
                <h3 class="text-sm font-medium text-gray-700">Our Services</h3>
                <p class="text-xs text-gray-500">
                  Add up to 6 services your company offers
                </p>

                <div
                  v-for="(service, index) in profileData.services"
                  :key="index"
                  class="grid grid-cols-3 gap-3 items-end"
                >
                  <div class="col-span-1">
                    <label class="block text-xs font-medium text-gray-600 mb-1"
                      >Icon {{ index + 1 }}</label
                    >
                    <input
                      v-model="service.icon"
                      type="text"
                      placeholder="e.g., 🏢"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center text-2xl"
                    />
                  </div>
                  <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1"
                      >Service Name</label
                    >
                    <input
                      v-model="service.name"
                      type="text"
                      :placeholder="'e.g., Consulting'"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                </div>
              </div>

              <!-- Team Members Section -->
              <div class="space-y-3 sm:space-y-4 pt-4 border-t">
                <h3 class="text-sm font-medium text-gray-700">Team Members</h3>
                <p class="text-xs text-gray-500">
                  Add up to 3 key team members to display
                </p>

                <div
                  v-for="(member, index) in profileData.teamMembers"
                  :key="index"
                  class="grid grid-cols-3 gap-3"
                >
                  <div class="col-span-1">
                    <label class="block text-xs font-medium text-gray-600 mb-1"
                      >Initials {{ index + 1 }}</label
                    >
                    <input
                      v-model="member.initials"
                      type="text"
                      placeholder="e.g., JD"
                      maxlength="2"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center uppercase"
                    />
                  </div>
                  <div class="col-span-1">
                    <label class="block text-xs font-medium text-gray-600 mb-1"
                      >Name</label
                    >
                    <input
                      v-model="member.name"
                      type="text"
                      placeholder="e.g., John Doe"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                  <div class="col-span-1">
                    <label class="block text-xs font-medium text-gray-600 mb-1"
                      >Role</label
                    >
                    <input
                      v-model="member.role"
                      type="text"
                      placeholder="e.g., CEO"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Links Tab -->
            <div v-if="activeTab === 'links'" class="space-y-4 sm:space-y-6">
              <div class="space-y-3 sm:space-y-4">
                <div class="flex items-center justify-between">
                  <h3 class="text-sm font-medium text-gray-700">Your Links</h3>
                  <span class="text-xs sm:text-sm text-gray-500">{{ links.length}} links</span>
                </div>
                
                <!-- Real-time Preview Notice -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                  <p class="text-xs text-blue-700 flex items-center gap-2">
                    <Icon name="heroicons:eye" class="h-4 w-4" />
                    <span>Changes appear instantly in the preview →</span>
                  </p>
                </div>

                <!-- Empty State -->
                <div v-if="links.length === 0 && !loading" class="text-center py-8 sm:py-12">
                  <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                    <Icon name="heroicons:link" class="h-8 w-8 sm:h-10 sm:w-10 text-gray-400" />
                  </div>
                  <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">No Links Yet</h3>
                  <p class="text-sm text-gray-600 mb-6">Add your social media and custom links. They'll be saved when you save your profile.</p>
                  <button @click="showAddLinkModal = true" class="px-6 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors text-sm font-medium">
                    <Icon name="heroicons:plus" class="h-4 w-4 inline mr-2" />
                    Add Your First Link
                  </button>
                </div>

                <!-- Links List -->
                <div v-else-if="!loading">
                  <draggable v-model="links" @end="updateLinkOrder" item-key="id" class="space-y-3" handle=".drag-handle">
                    <template #item="{ element: link, index }">
                      <div class="group relative">
                        <div :class="['border-2 rounded-lg p-3 sm:p-4 transition-all', link.is_active ? 'border-gray-200 bg-white' : 'border-gray-200 bg-gray-50 opacity-60']">
                          <div class="flex items-start space-x-3">
                            <div class="drag-handle cursor-move pt-1">
                              <Icon name="heroicons:bars-3" class="h-5 w-5 text-gray-400 hover:text-gray-600" />
                            </div>
                            <div :class="['w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0', link.is_active ? getPlatformColor(link.platform) : 'bg-gray-200']">
                              <Icon :name="getPlatformIcon(link.platform)" :class="['h-5 w-5', link.is_active ? 'text-white' : 'text-gray-400']" />
                            </div>
                            <div class="flex-1 min-w-0">
                              <input v-model="link.title" type="text" placeholder="Link title" class="w-full px-3 py-1.5 text-sm font-medium bg-transparent border-0 focus:ring-2 focus:ring-blue-500 rounded" @blur="updateLink(link)" />
                              <input v-model="link.url" type="url" placeholder="URL" class="w-full px-3 py-1 mt-1 text-xs sm:text-sm text-gray-600 bg-transparent border-0 focus:ring-2 focus:ring-blue-500 rounded" @blur="updateLink(link)" />
                              <p class="text-xs text-gray-500 mt-1">
                                <Icon name="heroicons:cursor-arrow-ripple" class="h-3 w-3 inline mr-1" />
                                {{ link.click_count || 0 }} clicks
                              </p>
                            </div>
                            <div class="flex items-center space-x-2">
                              <button @click="toggleLink(link)" :class="['relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out', link.is_active ? 'bg-blue-600' : 'bg-gray-200']">
                                <span :class="['pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out', link.is_active ? 'translate-x-5' : 'translate-x-0']" />
                              </button>
                              <button @click="deleteLink(link)" class="p-1 text-gray-400 hover:text-red-600 transition-colors">
                                <Icon name="heroicons:trash" class="h-4 w-4" />
                              </button>
                            </div>
                          </div>
                        </div>
                        <div v-if="index < links.length - 1" class="absolute left-1/2 -bottom-3 transform -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                          <button @click="showAddLinkModal = true; insertIndex = index + 1" class="w-8 h-8 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors shadow-lg flex items-center justify-center">
                            <Icon name="heroicons:plus" class="h-4 w-4" />
                          </button>
                        </div>
                      </div>
                    </template>
                  </draggable>
                  <button @click="showAddLinkModal = true; insertIndex = null" class="w-full mt-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-600 transition-colors flex items-center justify-center">
                    <Icon name="heroicons:plus" class="h-5 w-5 mr-2" />
                    Add Link
                  </button>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="flex items-center justify-center py-12">
                  <div class="animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent"></div>
                  <span class="ml-3 text-gray-600">Loading your links...</span>
                </div>
              </div>
            </div>

            <!-- Design Tab -->
            <div v-if="activeTab === 'design'" class="space-y-4 sm:space-y-6">
              <!-- Background Color -->
              <div>
                <h3 class="text-sm font-medium text-gray-700 mb-3 sm:mb-4">
                  Background
                </h3>
                <div class="space-y-3">
                  <div>
                    <label
                      class="block text-xs sm:text-sm font-medium text-gray-600 mb-2"
                      >Background Color</label
                    >
                    <div class="flex items-center space-x-3">
                      <div class="relative">
                        <input
                          type="color"
                          v-model="profileData.backgroundColor"
                          class="w-12 h-12 sm:w-16 sm:h-16 rounded-lg border-2 border-gray-300 cursor-pointer"
                        />
                      </div>
                      <div class="flex-1">
                        <input
                          type="text"
                          v-model="profileData.backgroundColor"
                          placeholder="#FFFFFF"
                          class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Theme Selection -->
              <div>
                <h3 class="text-sm font-medium text-gray-700 mb-3 sm:mb-4">
                  Theme Presets
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
                  <div
                    v-for="theme in themes"
                    :key="theme.id"
                    @click="applyTheme(theme)"
                    :class="[
                      'relative cursor-pointer rounded-lg border-2 p-3 sm:p-4 transition-all min-h-[90px] flex flex-col items-center justify-center',
                      profileData.theme === theme.id
                        ? 'border-blue-500'
                        : 'border-gray-200 hover:border-gray-300',
                    ]"
                  >
                    <div
                      :class="[
                        'w-full h-16 sm:h-24 rounded-md mb-1 sm:mb-2',
                        theme.preview,
                      ]"
                    ></div>
                    <p class="text-xs text-center font-medium">
                      {{ theme.name }}
                    </p>
                    <Icon
                      v-if="profileData.theme === theme.id"
                      name="heroicons:check-circle"
                      class="absolute top-1 right-1 sm:top-2 sm:right-2 w-3 h-3 sm:w-4 sm:h-4 text-blue-500"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Style Tab -->
            <div v-if="activeTab === 'style'" class="space-y-4 sm:space-y-6">
              <!-- Font Selection -->
              <div>
                <h3 class="text-sm font-medium text-gray-700 mb-3 sm:mb-4">
                  Typography
                </h3>

                <!-- Font Dropdown -->
                <div class="mb-4">
                  <label
                    class="block text-xs sm:text-sm font-medium text-gray-600 mb-2"
                    >Font Family</label
                  >
                  <select
                    v-model="profileData.font"
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  >
                    <option
                      v-for="font in fonts"
                      :key="font.id"
                      :value="font.id"
                    >
                      {{ font.name }}
                    </option>
                  </select>
                  <p
                    :style="{ fontFamily: getCurrentFont() }"
                    class="mt-2 text-xs sm:text-sm text-gray-600"
                  >
                    The quick brown fox jumps over the lazy dog
                  </p>
                </div>

                <!-- Text Color -->
                <div>
                  <label
                    class="block text-xs sm:text-sm font-medium text-gray-600 mb-2"
                    >Page Text Color</label
                  >
                  <div class="flex items-center space-x-3">
                    <div class="relative">
                      <input
                        type="color"
                        v-model="profileData.textColor"
                        class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg border-2 border-gray-300 cursor-pointer"
                      />
                    </div>
                    <div class="flex-1">
                      <input
                        type="text"
                        v-model="profileData.textColor"
                        placeholder="#000000"
                        class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Button Style -->
              <div>
                <h3 class="text-sm font-medium text-gray-700 mb-3 sm:mb-4">
                  Button Style
                </h3>
                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                  <div
                    v-for="style in buttonStyles"
                    :key="style.id"
                    @click="profileData.buttonStyle = style.id"
                    :class="[
                      'relative p-3 sm:p-4 rounded-lg border-2 cursor-pointer transition-all min-h-[60px] flex items-center justify-center',
                      profileData.buttonStyle === style.id
                        ? 'border-blue-500 bg-blue-50'
                        : 'border-gray-200 hover:border-gray-300',
                    ]"
                  >
                    <button
                      :class="[
                        'w-full py-1.5 sm:py-2 px-2 sm:px-4 text-xs sm:text-sm truncate',
                        style.class,
                      ]"
                      disabled
                    >
                      {{ style.name }}
                    </button>
                    <Icon
                      v-if="profileData.buttonStyle === style.id"
                      name="heroicons:check-circle"
                      class="absolute top-1 right-1 sm:top-2 sm:right-2 w-3 h-3 sm:w-4 sm:h-4 text-blue-500 flex-shrink-0"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Watermarks Tab -->
            <div
              v-if="activeTab === 'watermarks'"
              class="space-y-4 sm:space-y-6"
            >
              <div>
                <h3 class="text-sm font-medium text-gray-700 mb-3 sm:mb-4">
                  Watermark Settings
                </h3>

                <!-- Watermark Toggle -->
                <div class="bg-gray-50 rounded-lg p-4">
                  <div class="flex items-center justify-between">
                    <div class="flex-1">
                      <h4 class="text-sm font-medium text-gray-900">
                        Show "Powered by NFC GO" watermark
                      </h4>
                      <p class="text-xs sm:text-sm text-gray-500 mt-1">
                        Display the NFC GO branding at the bottom of your
                        profile
                      </p>
                    </div>
                    <button
                      @click="
                        profileData.showWatermark = !profileData.showWatermark
                      "
                      :class="[
                        'relative inline-flex h-6 w-11 sm:h-7 sm:w-12 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
                        profileData.showWatermark
                          ? 'bg-blue-600'
                          : 'bg-gray-200',
                      ]"
                    >
                      <span
                        :class="[
                          'pointer-events-none inline-block h-5 w-5 sm:h-6 sm:w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                          profileData.showWatermark
                            ? 'translate-x-5 sm:translate-x-5'
                            : 'translate-x-0',
                        ]"
                      />
                    </button>
                  </div>

                  <!-- Premium Upsell -->
                  <div
                    v-if="profileData.showWatermark"
                    class="mt-4 p-3 bg-blue-50 rounded-lg"
                  >
                    <p class="text-xs sm:text-sm text-blue-700">
                      <Icon
                        name="heroicons:sparkles"
                        class="w-4 h-4 inline mr-1"
                      />
                      Upgrade to Premium to remove the watermark and unlock
                      premium features
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Panel - Embedded Landing Page Preview -->
        <div
          v-show="showMobilePreview || !isMobile"
          class="lg:sticky lg:top-20 sm:lg:top-24 h-fit"
        >
          <!-- Preview Header -->
          <div class="mb-3 sm:mb-4">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm sm:text-base font-medium text-gray-700">Live Preview</h3>
              <div class="flex gap-1 sm:gap-2">
                <button
                  v-if="selectedNfcCardId"
                  @click="forceRefreshPreview"
                  class="flex items-center gap-1 sm:gap-2 px-2 sm:px-3 py-1 sm:py-1.5 bg-gray-600 text-white text-xs sm:text-sm font-medium rounded-md sm:rounded-lg hover:bg-gray-700 transition-colors shadow-sm"
                >
                  <Icon name="heroicons:arrow-path" class="h-3 w-3 sm:h-4 sm:w-4" />
                  <span class="hidden sm:inline">Refresh</span>
                </button>
                <button
                  v-if="selectedNfcCardId"
                  @click="openLandingPage"
                  class="flex items-center gap-1 sm:gap-2 px-2 sm:px-3 py-1 sm:py-1.5 bg-blue-600 text-white text-xs sm:text-sm font-medium rounded-md sm:rounded-lg hover:bg-blue-700 transition-colors shadow-sm"
                >
                  <Icon name="heroicons:arrow-top-right-on-square" class="h-3 w-3 sm:h-4 sm:w-4" />
                  <span class="hidden sm:inline">Open in New Tab</span>
                </button>
              </div>
            </div>
            <p class="text-xs text-gray-500">
              Real-time preview - updates as you edit
            </p>
          </div>
          
          <!-- Mobile Phone Frame with Embedded Landing Page -->
          <div class="bg-white rounded-lg sm:rounded-xl lg:rounded-2xl border border-gray-200 shadow-md sm:shadow-lg overflow-hidden">
            <div class="w-full">
              <!-- Phone Screen with iframe -->
              <div class="relative bg-white overflow-hidden">
                <!-- Embedded Landing Page iframe -->
                <template v-if="selectedNfcCardId && getSelectedCard()?.nfc_card_id">
                  <iframe
                    :key="previewKey"
                    :src="`/profile/${getSelectedCard().nfc_card_id}?preview=true`"
                    class="w-full border-0"
                    style="height: 400px; min-height: 350px;"
                    :style="{
                      height: isMobile ? '400px' : '600px',
                      minHeight: isMobile ? '350px' : '500px'
                    }"
                    @load="onPreviewLoad"
                    @error="onPreviewError"
                  ></iframe>
                    
                    <!-- Debug info (temporary) -->
                    <div class="absolute bottom-1 sm:bottom-2 left-1 sm:left-2 bg-black/70 text-white text-xs px-1.5 sm:px-2 py-0.5 sm:py-1 rounded text-xs">
                      <span class="hidden sm:inline">Loading: /profile/</span>{{ getSelectedCard()?.nfc_card_id }}
                    </div>
                  </template>
                  
                  <!-- Empty State -->
                  <div 
                    v-else 
                    class="flex items-center justify-center bg-gray-50"
                    :style="{
                      height: isMobile ? '400px' : '600px',
                      minHeight: isMobile ? '350px' : '500px'
                    }"
                  >
                    <div class="text-center p-4 sm:p-6">
                      <Icon name="heroicons:device-phone-mobile" class="h-12 w-12 sm:h-16 sm:w-16 mx-auto mb-2 sm:mb-3 text-gray-300" />
                      <p class="text-sm text-gray-500">Select a card to preview</p>
                      <p class="text-xs text-gray-400 mt-1 sm:mt-2">Card ID: {{ selectedNfcCardId || 'None' }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
      </div>
    </div>

    <!-- Add Link Modal -->
    <Teleport to="body">
      <div v-if="showAddLinkModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="closeAddLinkModal">
        <div class="bg-white rounded-2xl p-4 sm:p-6 max-w-md w-full max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-4 sm:mb-6">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900">Add New Link</h3>
            <button @click="closeAddLinkModal" class="p-2 hover:bg-gray-100 rounded-lg">
              <Icon name="heroicons:x-mark" class="h-5 w-5 sm:h-6 sm:w-6 text-gray-600" />
            </button>
          </div>
          <div class="mb-6">
            <div class="relative">
              <Icon name="heroicons:magnifying-glass" class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
              <input v-model="searchQuery" type="text" placeholder="URL or App" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>
          </div>
          <div class="space-y-4">
            <h4 class="text-sm font-medium text-gray-700">Popular Platforms</h4>
            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
              <button v-for="platform in popularPlatforms" :key="platform.id" @click="selectPlatform(platform)" class="flex flex-col items-center justify-center p-3 sm:p-4 rounded-xl hover:bg-gray-50 transition-colors">
                <div :class="['w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center mb-2', platform.color]">
                  <Icon :name="platform.icon" class="h-6 w-6 sm:h-7 sm:w-7 text-white" />
                </div>
                <span class="text-xs sm:text-sm text-gray-700">{{ platform.name }}</span>
              </button>
            </div>
            <button @click="selectCustomUrl" class="w-full p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center justify-center space-x-2">
              <Icon name="heroicons:link" class="h-5 w-5 text-gray-600" />
              <span class="text-sm text-gray-700">Add Custom URL</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Platform Form Modal -->
    <Teleport to="body">
      <div v-if="showPlatformForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="closePlatformForm">
        <div class="bg-white rounded-2xl p-4 sm:p-6 max-w-md w-full">
          <div class="flex items-center justify-between mb-4 sm:mb-6">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900">{{ selectedPlatform ? `Add ${selectedPlatform.name}` : "Add Custom Link" }}</h3>
            <button @click="closePlatformForm" class="p-2 hover:bg-gray-100 rounded-lg">
              <Icon name="heroicons:x-mark" class="h-5 w-5 sm:h-6 sm:w-6 text-gray-600" />
            </button>
          </div>
          <form @submit.prevent="saveLink" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
              <input v-model="linkForm.title" type="text" :placeholder="selectedPlatform?.placeholder || 'My Link'" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
              <input v-model="linkForm.url" type="url" :placeholder="selectedPlatform?.urlExample || 'https://example.com'" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required />
            </div>
            <div class="flex items-center space-x-3">
              <input type="checkbox" v-model="linkForm.is_active" id="activeLink" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500" />
              <label for="activeLink" class="text-sm text-gray-700">Make this link active</label>
            </div>
            <div class="flex space-x-3 pt-4">
              <button type="button" @click="closePlatformForm" class="flex-1 py-2 px-4 bg-gray-200 text-gray-800 rounded-full hover:bg-gray-300 transition-colors text-sm font-medium">Cancel</button>
              <button type="submit" :disabled="savingLink" class="flex-1 py-2 px-4 bg-blue-600 text-white rounded-full hover:bg-blue-700 disabled:opacity-50 transition-colors text-sm font-medium flex items-center justify-center">
                <div v-if="savingLink" class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent mr-2"></div>
                Add Link
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Apply Design Modal -->
    <Teleport to="body">
      <div v-if="showApplyDesignModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="showApplyDesignModal = false">
        <div class="bg-white rounded-2xl p-6 max-w-2xl w-full max-h-[80vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">Apply Design to Cards</h3>
            <button @click="showApplyDesignModal = false" class="p-2 hover:bg-gray-100 rounded-lg">
              <Icon name="heroicons:x-mark" class="h-6 w-6 text-gray-600" />
            </button>
          </div>

          <!-- Current Design Preview -->
          <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <h4 class="text-sm font-medium text-blue-900 mb-3">Current Design Settings</h4>
            <div class="space-y-2 text-sm">
              <div class="flex items-center justify-between">
                <span class="text-blue-700 font-medium">Font:</span>
                <span class="text-blue-900 truncate ml-2">{{ getFontName(profileData.font) }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-blue-700 font-medium">Theme:</span>
                <span class="text-blue-900 truncate ml-2">{{ profileData.theme }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-blue-700 font-medium">Button Style:</span>
                <span class="text-blue-900 truncate ml-2">{{ profileData.buttonStyle }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-blue-700 font-medium">Colors:</span>
                <div class="flex items-center gap-2 ml-2">
                  <div :style="{backgroundColor: profileData.backgroundColor}" class="w-5 h-5 rounded border border-gray-300 flex-shrink-0"></div>
                  <div :style="{backgroundColor: profileData.textColor}" class="w-5 h-5 rounded border border-gray-300 flex-shrink-0"></div>
                </div>
              </div>
            </div>
          </div>

          <p class="text-sm text-gray-600 mb-4">
            Select cards to apply the current design settings. Only design and style will be copied—content remains unchanged.
          </p>

          <!-- Select All Button -->
          <div class="mb-4 flex items-center justify-between gap-4 flex-wrap">
            <button
              @click="toggleSelectAllCards"
              class="px-4 py-2 text-sm font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition-colors flex items-center gap-2 flex-shrink-0"
            >
              <div v-if="isAllCardsSelected" class="h-5 w-5 flex-shrink-0">
                <Icon name="heroicons:check-circle" class="h-5 w-5 text-blue-600" />
              </div>
              <div v-else class="h-5 w-5 rounded-full border-2 border-blue-600 flex-shrink-0"></div>
              <span class="whitespace-nowrap">{{ isAllCardsSelected ? 'Deselect All' : 'Select All Employee Cards' }}</span>
            </button>
            <span class="text-sm text-gray-500 flex-shrink-0">
              {{ selectedCardsForDesign.length }} / {{ allAvailableCards.length }} selected
            </span>
          </div>

          <!-- Card Selection List -->
          <div class="space-y-3 mb-6 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-2">
            <div v-if="allAvailableCards.length === 0" class="text-center py-12 text-gray-500 bg-gray-50 rounded-lg">
              <Icon name="heroicons:credit-card" class="h-12 w-12 mx-auto mb-3 text-gray-300" />
              <p class="text-sm font-medium">No other cards available</p>
              <p class="text-xs text-gray-400 mt-1">All your cards are already selected or this is your only card</p>
            </div>
            
            <label
              v-for="card in allAvailableCards"
              :key="card.id"
              class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer transition-colors border-2 min-h-[70px]"
              :class="selectedCardsForDesign.includes(card.id) ? 'border-blue-500 bg-blue-50' : 'border-transparent'"
            >
              <input
                type="checkbox"
                :value="card.id"
                v-model="selectedCardsForDesign"
                class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded flex-shrink-0"
              />
              <div class="ml-4 flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate">
                  {{ card.nfc_card_id || `Card #${card.id}` }}
                </p>
                <p class="text-xs text-gray-600 truncate">
                  {{ card.card_owner }}
                  <span v-if="card.is_employee" class="ml-2 px-2 py-0.5 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">
                    Employee
                  </span>
                </p>
              </div>
              <div class="flex gap-2 flex-shrink-0">
                <span class="px-2 py-1 text-xs font-medium rounded-full"
                  :class="card.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                  {{ card.status }}
                </span>
              </div>
            </label>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-4">
            <button
              @click="showApplyDesignModal = false"
              class="flex-1 py-3 px-4 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 border border-gray-300 transition-colors"
            >
              Cancel
            </button>
            <button
              @click="applyDesignToCards"
              :disabled="selectedCardsForDesign.length === 0 || applyingDesign"
              class="flex-1 py-3 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:bg-blue-200 disabled:text-blue-400 disabled:cursor-not-allowed transition-colors flex items-center justify-center border border-blue-600 disabled:border-blue-200"
            >
              <Icon
                v-if="!applyingDesign"
                name="heroicons:paint-brush"
                class="h-5 w-5 mr-2"
              />
              <div
                v-else
                class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent mr-2"
              ></div>
              {{
                applyingDesign
                  ? 'Applying...'
                  : selectedCardsForDesign.length === 0
                  ? 'Select Cards'
                  : `Apply to ${selectedCardsForDesign.length} Card${selectedCardsForDesign.length > 1 ? 's' : ''}`
              }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
// import ProfileImageUpload from '~/components/ProfileImageUpload.vue'
import { useAuthStore } from "~/stores/auth";
import draggable from "vuedraggable";

// Layout
definePageMeta({
  layout: "user-dashboard",
  middleware: "auth",
});

// Composables and stores
const { $api, $toast } = useNuxtApp();
const authStore = useAuthStore();

// Reactive data
const activeTab = ref("profile");
const saving = ref(false);
const showMobilePreview = ref(false);
const isMobile = ref(false);
const showCardSelector = ref(false);
const pageLoading = ref(true);
const isInitialized = ref(false);
const selectedNfcCardId = ref(null);
const userNfcCards = ref([]);
const selectedCardsForDesign = ref([]);
const applyingDesign = ref(false);
const showApplyDesignModal = ref(false);
const allEmployeeCards = ref([]); // All employee cards for batch design

// Links Management
const links = ref([]);
const loading = ref(false);
const savingLink = ref(false);
const showAddLinkModal = ref(false);
const showPlatformForm = ref(false);
const searchQuery = ref("");
const selectedPlatform = ref(null);
const insertIndex = ref(null);

// Preview Management
const previewKey = ref(0);
const loadingCards = ref(false);

// Profile data
const profileData = reactive({
  // Basic Info
  name: "",
  position: "",
  qualification: "",
  bio: "",
  contactNumber: "",
  email: "",
  website: "",
  address: "",
  image: null,

  // Company Info
  companyLogo: null,
  companyLogoText: "",
  companyName: "",
  companyRegistrationNo: "",
  companyDepartment: "",

  // Address Info
  addressName: "",
  addressStreet: "",
  addressArea: "",
  addressCityState: "",
  addressCountry: "",
  addressMapUrl: "",

  // Stats (3 items)
  stats: [
    { num: "10+", label: "Years Experience" },
    { num: "500+", label: "Projects Done" },
    { num: "98%", label: "Client Satisfaction" },
  ],

  // Services (max 6)
  services: [
    { icon: "🏷️", name: "RFID Technology" },
    { icon: "🖨️", name: "Label Printing" },
    { icon: "💻", name: "Software Development" },
    { icon: "🌐", name: "IoT Implementation" },
    { icon: "🛒", name: "E-commerce Marketing" },
    { icon: "📄", name: "Printing Solutions" },
  ],

  // Contact Methods
  phoneNumber: "",
  phoneLabel: "Phone",
  emailAddress: "",
  emailLabel: "Email",
  whatsappNumber: "",
  whatsappLabel: "WhatsApp",
  websiteUrl: "",
  websiteLabel: "Website",

  // Team Members (max 3)
  teamMembers: [
    { initials: "", name: "", role: "" },
    { initials: "", name: "", role: "" },
    { initials: "", name: "", role: "" },
  ],

  // Design Settings
  profileStyle: "classic",
  theme: "minimal",
  backgroundColor: "#FFFFFF",
  textColor: "#000000",
  font: "inter",
  buttonStyle: "solid",
  showWatermark: true,
});

// Sample links for preview
const sampleLinks = computed(() => [
  { id: 1, title: "Contact Me", url: "#" },
  { id: 2, title: "My Portfolio", url: "#" },
  { id: 3, title: "Social Media", url: "#" },
]);

const getSelectedCard = () => {
  return userNfcCards.value.find(card => card.id === selectedNfcCardId.value);
};

// Link form data
const linkForm = reactive({
  title: "",
  url: "",
  platform: "",
  is_active: true,
});

// Popular platforms configuration
const popularPlatforms = [
  { id: "instagram", name: "Instagram", icon: "mdi:instagram", color: "bg-gradient-to-br from-purple-600 to-pink-500", placeholder: "Instagram Profile", urlExample: "https://instagram.com/yourusername" },
  { id: "facebook", name: "Facebook", icon: "mdi:facebook", color: "bg-blue-600", placeholder: "Facebook Page", urlExample: "https://facebook.com/yourusername" },
  { id: "tiktok", name: "TikTok", icon: "mdi:tiktok", color: "bg-black", placeholder: "TikTok Profile", urlExample: "https://tiktok.com/@yourusername" },
  { id: "youtube", name: "YouTube", icon: "mdi:youtube", color: "bg-red-600", placeholder: "YouTube Channel", urlExample: "https://youtube.com/@yourchannel" },
  { id: "spotify", name: "Spotify", icon: "mdi:spotify", color: "bg-green-600", placeholder: "Spotify Profile", urlExample: "https://open.spotify.com/user/yourusername" },
  { id: "whatsapp", name: "WhatsApp", icon: "mdi:whatsapp", color: "bg-green-500", placeholder: "WhatsApp Chat", urlExample: "https://wa.me/1234567890" },
  { id: "x", name: "X", icon: "mdi:twitter", color: "bg-black", placeholder: "X (Twitter) Profile", urlExample: "https://x.com/yourusername" },
  { id: "snapchat", name: "Snapchat", icon: "mdi:snapchat", color: "bg-yellow-400", placeholder: "Snapchat Profile", urlExample: "https://snapchat.com/add/yourusername" },
  { id: "linkedin", name: "LinkedIn", icon: "mdi:linkedin", color: "bg-blue-700", placeholder: "LinkedIn Profile", urlExample: "https://linkedin.com/in/yourusername" },
  { id: "telegram", name: "Telegram", icon: "mdi:telegram", color: "bg-blue-500", placeholder: "Telegram", urlExample: "https://t.me/yourusername" },
  { id: "website", name: "Website", icon: "heroicons:globe-alt", color: "bg-gray-700", placeholder: "My Website", urlExample: "https://yourwebsite.com" },
  { id: "email", name: "Email", icon: "heroicons:envelope", color: "bg-gray-600", placeholder: "Email Me", urlExample: "mailto:your@email.com" },
];

// Computed
const activeLinks = computed(() => {
  return links.value
    .filter((link) => link.is_active)
    .sort((a, b) => (a.order || 0) - (b.order || 0));
});

// All available cards for batch design (admin's other cards + all employee cards)
const allAvailableCards = computed(() => {
  const adminCards = userNfcCards.value.filter(c => c.id !== selectedNfcCardId.value);
  const employeeCards = allEmployeeCards.value;
  console.log("allAvailableCards computed:", {
    adminCards: adminCards.length,
    employeeCards: employeeCards.length,
    total: adminCards.length + employeeCards.length
  });
  return [...adminCards, ...employeeCards];
});

// Check if all cards are selected
const isAllCardsSelected = computed(() => {
  return allAvailableCards.value.length > 0 && 
         selectedCardsForDesign.value.length === allAvailableCards.value.length;
});

// Configuration options
const tabs = [
  {
    id: "profile",
    name: "Profile",
    shortName: "Profile",
    icon: "heroicons:user",
  },
  {
    id: "company",
    name: "Company",
    shortName: "Company",
    icon: "heroicons:building-office",
  },
  {
    id: "services",
    name: "Services",
    shortName: "Services",
    icon: "heroicons:rocket-launch",
  },
  {
    id: "links",
    name: "Links",
    shortName: "Links",
    icon: "heroicons:link",
  },
  {
    id: "design",
    name: "Design",
    shortName: "Design",
    icon: "heroicons:paint-brush",
  },
  {
    id: "style",
    name: "Style",
    shortName: "Style",
    icon: "heroicons:sparkles",
  },
  {
    id: "watermarks",
    name: "Watermarks",
    shortName: "Brand",
    icon: "heroicons:eye",
  },
];

const profileStyles = [{ id: "classic", name: "Classic" }];

const themes = [
  {
    id: "minimal",
    name: "Minimal",
    preview: "bg-white",
    backgroundColor: "#FFFFFF",
  },
  {
    id: "modern",
    name: "Modern",
    preview: "bg-gradient-to-br from-gray-50 to-gray-100",
    backgroundColor: "#F9FAFB",
  },
  {
    id: "creative",
    name: "Creative",
    preview: "bg-gradient-to-br from-purple-500 to-pink-500",
    backgroundColor: "#A855F7",
  },
  {
    id: "professional",
    name: "Professional",
    preview: "bg-gradient-to-br from-blue-600 to-blue-700",
    backgroundColor: "#2563EB",
  },
  {
    id: "dark",
    name: "Dark",
    preview: "bg-gray-900",
    backgroundColor: "#111827",
  },
];

const fonts = [
  { id: "inter", name: "Inter", family: "Inter, sans-serif" },
  { id: "poppins", name: "Poppins", family: "Poppins, sans-serif" },
  { id: "roboto", name: "Roboto", family: "Roboto, sans-serif" },
  { id: "playfair", name: "Playfair", family: "Playfair Display, serif" },
];

const buttonStyles = [
  { id: "solid", name: "Solid", class: "bg-black text-white rounded-full" },
  {
    id: "outline",
    name: "Outline",
    class: "border-2 border-black text-black rounded-full",
  },
  { id: "soft", name: "Soft", class: "bg-gray-100 text-gray-900 rounded-xl" },
  {
    id: "shadow",
    name: "Shadow",
    class: "bg-white text-black rounded-xl shadow-lg",
  },
];

// Helper function to get full image URL
const getImageUrl = (imagePath) => {
  if (!imagePath) return null;

  // If already a full URL, return as is
  if (imagePath.startsWith("http://") || imagePath.startsWith("https://")) {
    return imagePath;
  }

  // Get config
  const config = useRuntimeConfig();
  const apiBase = config.public.apiBaseUrl || "http://localhost:8000/api";

  // Remove /api from the end to get base URL
  const baseUrl = apiBase.replace("/api", "");

  // Ensure the path starts with /
  const path = imagePath.startsWith("/") ? imagePath : `/${imagePath}`;

  return `${baseUrl}${path}`;
};

// Methods - These are just event handlers for the ProfileImageUpload component
const handleProfileImageUpload = (data) => {
  // This is called when ProfileImageUpload component successfully uploads
  console.log("Profile image uploaded:", data);
  // The component already updates profileData.image via v-model
};

const handleCompanyLogoUpload = (data) => {
  // This is called when ProfileImageUpload component successfully uploads
  console.log("Company logo uploaded:", data);
  // The component already updates profileData.companyLogo via v-model
};

const applyTheme = (theme) => {
  profileData.theme = theme.id;
  profileData.backgroundColor = theme.backgroundColor;

  // Set text color based on theme
  if (theme.id === "dark") {
    profileData.textColor = "#FFFFFF";
  } else {
    profileData.textColor = "#000000";
  }

  // Auto-save when theme changes
  saveProfile();
};

const getCurrentFont = () => {
  const font = fonts.find((f) => f.id === profileData.font);
  return font ? font.family : "Inter, sans-serif";
};

const getFontName = (fontId) => {
  const font = fonts.find((f) => f.id === fontId);
  return font ? font.name : fontId;
};

const getButtonClass = () => {
  const style = buttonStyles.find((s) => s.id === profileData.buttonStyle);
  return `py-2.5 sm:py-3 px-5 sm:px-6 ${
    style ? style.class : buttonStyles[0].class
  }`;
};

// Links Management Methods
const getPlatformIcon = (platformId) => {
  const platform = popularPlatforms.find((p) => p.id === platformId);
  return platform?.icon || "heroicons:link";
};

const getPlatformColor = (platformId) => {
  const platform = popularPlatforms.find((p) => p.id === platformId);
  return platform?.color || "bg-gray-600";
};

const getPlatformEmojiPreview = (platformId) => {
  const emojiMap = {
    instagram: "📸",
    facebook: "📘",
    tiktok: "🎵",
    youtube: "📺",
    spotify: "🎵",
    whatsapp: "💬",
    x: "🐦",
    snapchat: "👻",
    linkedin: "💼",
    telegram: "✈️",
    website: "🌐",
    email: "📧",
    custom: "🔗",
  };
  return emojiMap[platformId?.toLowerCase()] || "🔗";
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

const saveLink = async () => {
  try {
    savingLink.value = true;
    
    // Add link locally (will be saved with profile)
    const newLink = {
      id: Date.now(), // Temporary ID
      title: linkForm.title,
      url: linkForm.url,
      platform: linkForm.platform,
      is_active: linkForm.is_active,
      click_count: 0,
      order: links.value.length,
      _isNew: true, // Mark as new (not yet saved to backend)
    };
    
    links.value.push(newLink);
    $toast.success("Link added! Save your profile to apply changes.");
    closePlatformForm();
  } catch (error) {
    console.error("Failed to add link:", error);
    $toast.error("Failed to add link. Please try again.");
  } finally {
    savingLink.value = false;
  }
};

const updateLink = async (link) => {
  try {
    // If it's a new link (not yet saved to backend), just update locally
    if (link._isNew) {
      // Local update only
      console.log("Link updated locally:", link.title);
      return;
    }
    
    // Update existing link in backend
    await $api.put(`/links/${link.id}`, {
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
    const index = links.value.findIndex((l) => l.id === link.id);
    if (index > -1) {
      links.value.splice(index, 1);
    }
    
    // If it's a new link, just remove locally
    if (link._isNew) {
      $toast.success("Link removed");
      return;
    }
    
    // Delete from backend if it exists
    await $api.delete(`/links/${link.id}`);
    $toast.success("Link deleted");
  } catch (error) {
    console.error("Failed to delete link:", error);
    $toast.error("Failed to delete link");
  }
};

const updateLinkOrder = async () => {
  try {
    // Update order locally
    links.value.forEach((link, index) => {
      link.order = index;
    });
    
    // Only sync to backend if there are saved links (non-new)
    const savedLinks = links.value.filter(link => !link._isNew);
    if (savedLinks.length > 0) {
      const linksOrder = savedLinks.map((link, index) => ({
        id: link.id,
        order: links.value.findIndex(l => l.id === link.id),
      }));
      await $api.post("/links/reorder", { links: linksOrder });
    }
    
    console.log("Link order updated locally");
  } catch (error) {
    console.error("Failed to update link order:", error);
  }
};

const toggleLink = async (link) => {
  try {
    link.is_active = !link.is_active;
    await updateLink(link);
    
    // Show different message for new links
    if (link._isNew) {
      console.log(`Link ${link.is_active ? "activated" : "deactivated"} (will be saved with profile)`);
    } else {
      $toast.success(`Link ${link.is_active ? "activated" : "deactivated"}`);
    }
  } catch (error) {
    console.error("Failed to toggle link:", error);
    link.is_active = !link.is_active;
    $toast.error("Failed to update link status");
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
  Object.assign(linkForm, {
    title: "",
    url: "",
    platform: "",
    is_active: true,
  });
};

const saveProfile = async () => {
  if (!selectedNfcCardId.value) {
    $toast.error("Please select an NFC card first");
    return;
  }

  saving.value = true;
  try {
    // Map frontend camelCase to backend snake_case
    const payload = {
      // Basic Info
      name: profileData.name,
      title: profileData.position,
      qualification: profileData.qualification,
      bio: profileData.bio,
      phone: profileData.contactNumber,
      email: profileData.email,
      website: profileData.website,
      address: profileData.address,
      profile_image: profileData.image,
      company_logo: profileData.companyLogo,

      // Company Info
      company_logo_text: profileData.companyLogoText,
      company_name: profileData.companyName,
      company_registration_no: profileData.companyRegistrationNo,
      company_department: profileData.companyDepartment,

      // Address Details
      address_name: profileData.addressName,
      address_street: profileData.addressStreet,
      address_area: profileData.addressArea,
      address_city_state: profileData.addressCityState,
      address_country: profileData.addressCountry,
      address_map_url: profileData.addressMapUrl,

      // Stats, Services, Team Members
      stats: profileData.stats,
      services: profileData.services,
      team_members: profileData.teamMembers,

      // Contact Methods
      phone_number: profileData.phoneNumber,
      phone_label: profileData.phoneLabel,
      email_address: profileData.emailAddress,
      email_label: profileData.emailLabel,
      whatsapp_number: profileData.whatsappNumber,
      whatsapp_label: profileData.whatsappLabel,
      website_url: profileData.websiteUrl,
      website_label: profileData.websiteLabel,

      // Design Settings
      profile_style: profileData.profileStyle,
      theme: profileData.theme,
      background_color: profileData.backgroundColor,
      text_color: profileData.textColor,
      font: profileData.font,
      button_style: profileData.buttonStyle,
      show_watermark: profileData.showWatermark,
      
      // Links - include all links (new and existing)
      links: links.value.map(link => ({
        id: link._isNew ? undefined : link.id, // Don't send temp ID for new links
        title: link.title,
        url: link.url,
        platform: link.platform,
        is_active: link.is_active,
        order: link.order,
      })),
    };

    // Save the landing page design for the selected NFC card
    const response = await $api.put(
      `/nfc-cards/${selectedNfcCardId.value}/landing-page`,
      payload
    );

    if (response.success) {
      $toast.success("Landing page and links saved successfully!");
      
      // Update links with backend IDs (remove _isNew flag)
      if (response.landing_page && response.landing_page.social_links) {
        links.value = response.landing_page.social_links.map(link => ({
          ...link,
          _isNew: false,
        }));
      }
    } else {
      throw new Error("Failed to save landing page");
    }
  } catch (error) {
    console.error("Save landing page error:", error);
    if (error.data?.message) {
      $toast.error(error.data.message);
    } else {
      $toast.error("Failed to save landing page");
    }
  } finally {
    saving.value = false;
  }
};

// Apply current design to selected cards
const applyDesignToCards = async () => {
  if (selectedCardsForDesign.value.length === 0) {
    $toast.error("Please select at least one card");
    return;
  }

  if (!selectedNfcCardId.value) {
    $toast.error("No source card selected");
    return;
  }

  applyingDesign.value = true;

  try {
    // Get current design settings from the active card
    const designSettings = {
      backgroundColor: profileData.backgroundColor,
      textColor: profileData.textColor,
      font: profileData.font,
      buttonStyle: profileData.buttonStyle,
      profileStyle: profileData.profileStyle,
      theme: profileData.theme,
    };

    // Apply design to each selected card
    const promises = selectedCardsForDesign.value.map(async (cardId) => {
      try {
        const response = await $api.post(
          `/nfc-cards/${cardId}/apply-design`,
          designSettings
        );
        return { cardId, success: response.success };
      } catch (error) {
        console.error(`Failed to apply design to card ${cardId}:`, error);
        return { cardId, success: false };
      }
    });

    const results = await Promise.all(promises);
    const successCount = results.filter((r) => r.success).length;
    const failCount = results.length - successCount;

    if (successCount > 0) {
      $toast.success(
        `Design applied to ${successCount} card${successCount > 1 ? "s" : ""} successfully!`
      );
      // Clear selection and close modal
      selectedCardsForDesign.value = [];
      showApplyDesignModal.value = false;
    }

    if (failCount > 0) {
      $toast.error(`Failed to apply design to ${failCount} card${failCount > 1 ? "s" : ""}`);
    }
  } catch (error) {
    console.error("Error applying design:", error);
    $toast.error("Failed to apply design. Please try again.");
  } finally {
    applyingDesign.value = false;
  }
};

// Toggle select all cards
const toggleSelectAllCards = () => {
  if (isAllCardsSelected.value) {
    // Deselect all
    selectedCardsForDesign.value = [];
  } else {
    // Select all available cards
    selectedCardsForDesign.value = allAvailableCards.value.map(card => card.id);
  }
};

// Load all employee cards
const loadAllEmployeeCards = async () => {
  try {
    console.log("Loading employee cards...");
    // Use the same API as Card Management
    const response = await $api.get("/nfc-cards");
    console.log("All cards response for employee filtering:", response);
    
    if (response.success && response.nfc_cards) {
      const currentUserId = authStore.user?.id;
      // Filter to get only employee cards (cards where user_id is NOT current user)
      const employeeCardsList = response.nfc_cards.filter(card => {
        return card.user_id !== currentUserId;
      });
      
      // Mark as employee cards
      allEmployeeCards.value = employeeCardsList.map(card => ({
        ...card,
        is_employee: true
      }));
      console.log("Employee cards loaded:", allEmployeeCards.value.length, allEmployeeCards.value);
    } else {
      console.warn("Failed to load employee cards - response not successful");
    }
  } catch (error) {
    console.error("Failed to load employee cards:", error);
  }
};

const loadProfile = async () => {
  if (!selectedNfcCardId.value) {
    console.log("No card selected, skipping profile load");
    return;
  }

  // Prevent multiple simultaneous loads
  if (saving.value) {
    console.log("Already loading, skipping");
    return;
  }

  try {
    // Load the landing page design for the selected NFC card
    const response = await $api.get(
      `/nfc-cards/${selectedNfcCardId.value}/landing-page`
    );

    if (response.success && response.landing_page) {
      const landingPage = response.landing_page;

      // Map backend fields to frontend fields - Basic Info
      profileData.name = landingPage.name || "";
      profileData.position = landingPage.title || landingPage.position || "";
      profileData.qualification = landingPage.qualification || "";
      profileData.bio = landingPage.bio || "";
      profileData.contactNumber =
        landingPage.phone || landingPage.contactNumber || "";
      profileData.email = landingPage.email || "";
      profileData.website = landingPage.website || "";
      profileData.address = landingPage.location || landingPage.address || "";
      profileData.image =
        landingPage.profile_image || landingPage.image || null;
      profileData.companyLogo =
        landingPage.company_logo || landingPage.companyLogo || null;

      // Debug image URLs
      console.log("📸 Loaded images:", {
        profile_image: profileData.image,
        profile_image_full: getImageUrl(profileData.image),
        company_logo: profileData.companyLogo,
        company_logo_full: getImageUrl(profileData.companyLogo),
      });

      // Company Info
      profileData.companyLogoText = landingPage.company_logo_text || "";
      profileData.companyName = landingPage.company_name || "";
      profileData.companyRegistrationNo =
        landingPage.company_registration_no || "";
      profileData.companyDepartment = landingPage.company_department || "";

      // Address Details
      profileData.addressName = landingPage.address_name || "";
      profileData.addressStreet = landingPage.address_street || "";
      profileData.addressArea = landingPage.address_area || "";
      profileData.addressCityState = landingPage.address_city_state || "";
      profileData.addressCountry = landingPage.address_country || "";
      profileData.addressMapUrl = landingPage.address_map_url || "";

      // Stats
      if (landingPage.stats && Array.isArray(landingPage.stats)) {
        profileData.stats = landingPage.stats.map((stat, index) => ({
          num: stat.num || profileData.stats[index]?.num || "",
          label: stat.label || profileData.stats[index]?.label || "",
        }));
      }

      // Services
      if (landingPage.services && Array.isArray(landingPage.services)) {
        profileData.services = landingPage.services.map((service, index) => ({
          icon: service.icon || profileData.services[index]?.icon || "",
          name: service.name || profileData.services[index]?.name || "",
        }));
      }

      // Contact Methods
      profileData.phoneNumber = landingPage.phone_number || "";
      profileData.phoneLabel = landingPage.phone_label || "Call Us";
      profileData.emailAddress = landingPage.email_address || "";
      profileData.emailLabel = landingPage.email_label || "Email Us";
      profileData.whatsappNumber = landingPage.whatsapp_number || "";
      profileData.whatsappLabel = landingPage.whatsapp_label || "WhatsApp";
      profileData.websiteUrl = landingPage.website_url || "";
      profileData.websiteLabel = landingPage.website_label || "Visit Website";

      // Team Members
      if (landingPage.team_members && Array.isArray(landingPage.team_members)) {
        profileData.teamMembers = landingPage.team_members.map(
          (member, index) => ({
            initials:
              member.initials || profileData.teamMembers[index]?.initials || "",
            name: member.name || profileData.teamMembers[index]?.name || "",
            role: member.role || profileData.teamMembers[index]?.role || "",
          })
        );
      }

      // Design Settings
      profileData.profileStyle =
        landingPage.profile_style || landingPage.profileStyle || "classic";
      profileData.theme = landingPage.theme || "minimal";
      profileData.backgroundColor =
        landingPage.background_color ||
        landingPage.backgroundColor ||
        "#FFFFFF";
      profileData.textColor =
        landingPage.text_color || landingPage.textColor || "#000000";
      profileData.font = landingPage.font || "inter";
      profileData.buttonStyle =
        landingPage.button_style || landingPage.buttonStyle || "solid";
      profileData.showWatermark = landingPage.show_watermark !== false;
      
      // Load links from landing page (if any)
      if (landingPage.social_links && Array.isArray(landingPage.social_links)) {
        links.value = landingPage.social_links.map(link => ({
          ...link,
          _isNew: false, // Mark as existing links from backend
        }));
      } else {
        links.value = [];
      }

      console.log("Loaded landing page for card:", selectedNfcCardId.value, "with", links.value.length, "links");
    } else {
      // No landing page exists yet for this card, reset to defaults
      resetProfileData();
      console.log("No landing page found for card, using defaults");
    }
  } catch (error) {
    console.error("Error loading landing page:", error);

    // If 404, this card has no landing page yet - reset to defaults
    if (error?.response?.status === 404 || error?.status === 404) {
      resetProfileData();
      console.log("Card has no landing page yet, reset to defaults");
    } else {
      // Other errors - check if error object exists before accessing properties
      const errorMessage = error?.data?.message || error?.message || "Failed to load landing page";
      $toast.error(errorMessage);
    }
  }
};

// Reset profile data to defaults
const resetProfileData = () => {
  // Basic Info
  profileData.name = "";
  profileData.position = "";
  profileData.qualification = "";
  profileData.bio = "";
  profileData.contactNumber = "";
  profileData.email = "";
  profileData.website = "";
  profileData.address = "";
  profileData.image = null;

  // Company Info
  profileData.companyLogo = null;
  profileData.companyLogoText = "";
  profileData.companyName = "";
  profileData.companyRegistrationNo = "";
  profileData.companyDepartment = "";

  // Address Info
  profileData.addressName = "";
  profileData.addressStreet = "";
  profileData.addressArea = "";
  profileData.addressCityState = "";
  profileData.addressCountry = "";
  profileData.addressMapUrl = "";

  // Stats (reset to default examples)
  profileData.stats = [
    { num: "10+", label: "Years Experience" },
    { num: "500+", label: "Projects Done" },
    { num: "98%", label: "Client Satisfaction" },
  ];

  // Services (reset to default examples)
  profileData.services = [
    { icon: "🏷️", name: "RFID Technology" },
    { icon: "🖨️", name: "Label Printing" },
    { icon: "💻", name: "Software Development" },
    { icon: "🌐", name: "IoT Implementation" },
    { icon: "🛒", name: "E-commerce Marketing" },
    { icon: "📄", name: "Printing Solutions" },
  ];

  // Contact Methods
  profileData.phoneNumber = "";
  profileData.phoneLabel = "Phone";
  profileData.emailAddress = "";
  profileData.emailLabel = "Email";
  profileData.whatsappNumber = "";
  profileData.whatsappLabel = "WhatsApp";
  profileData.websiteUrl = "";
  profileData.websiteLabel = "Website";

  // Team Members (reset to empty)
  profileData.teamMembers = [
    { initials: "", name: "", role: "" },
    { initials: "", name: "", role: "" },
    { initials: "", name: "", role: "" },
  ];

  // Design Settings
  profileData.profileStyle = "classic";
  profileData.theme = "minimal";
  profileData.backgroundColor = "#FFFFFF";
  profileData.textColor = "#000000";
  profileData.font = "inter";
  profileData.buttonStyle = "solid";
  profileData.showWatermark = true;
};

const loadUserNfcCards = async () => {
  // Prevent duplicate calls
  if (isInitialized.value && !loadingCards.value) {
    console.log("Already initialized, skipping reload");
    return;
  }

  loadingCards.value = true;
  pageLoading.value = true;
  try {
    const response = await $api.get("/nfc-cards");

    if (response.success && response.nfc_cards) {
      // Filter to show only Admin's own cards (exclude employee cards)
      // Admin's cards: user_id = current user
      // Employee's cards: user_id = employee, business_account_id = admin
      const currentUserId = authStore.user?.id;
      userNfcCards.value = response.nfc_cards.filter(card => {
        // Include cards where user_id equals current user (Admin's own cards)
        // This excludes employee cards (where user_id is the employee's ID)
        return card.user_id === currentUserId;
      });

      // Check URL parameters for card selection
      const route = useRoute();
      const nfcCardIdParam = route.query.nfc_card_id;
      const nfcTagIdParam = route.query.nfc_tag_id;

      if (nfcCardIdParam) {
        // Find card by nfc_card_id (when creating new profile for this card)
        const cardToSelect = userNfcCards.value.find(
          (card) => card.nfc_card_id === nfcCardIdParam
        );
        if (cardToSelect) {
          selectedNfcCardId.value = cardToSelect.id;
          console.log("Auto-selected card from URL:", cardToSelect.nfc_card_id);
          // Load landing page for this card
          try {
            await loadProfile();
          } catch (profileError) {
            console.error("Failed to load profile for selected card:", profileError);
          }
        }
      } else if (nfcTagIdParam) {
        // Find card by linked nfcTag.id (when editing existing profile)
        const cardToSelect = userNfcCards.value.find(
          (card) => card.nfcTag?.id == nfcTagIdParam
        );
        if (cardToSelect) {
          selectedNfcCardId.value = cardToSelect.id;
          console.log(
            "Auto-selected card by tag ID:",
            cardToSelect.nfc_card_id
          );
          // Load landing page for this card
          try {
            await loadProfile();
          } catch (profileError) {
            console.error("Failed to load profile for selected card by tag ID:", profileError);
          }
        }
      } else if (userNfcCards.value.length > 0 && !selectedNfcCardId.value) {
        // Auto-select the first card if available and none selected
        selectedNfcCardId.value = userNfcCards.value[0].id;
        // Load landing page for the first card
        try {
          await loadProfile();
        } catch (profileError) {
          console.error("Failed to load profile for first card:", profileError);
        }
      }
    }
  } catch (error) {
    console.error("Error loading NFC cards:", error);
    $toast.error("Failed to load NFC cards");
  } finally {
    loadingCards.value = false;
    pageLoading.value = false;
    isInitialized.value = true;
  }
};

const selectNfcCard = async (cardId) => {
  // Prevent selecting same card
  if (selectedNfcCardId.value === cardId) {
    showCardSelector.value = false;
    return;
  }

  selectedNfcCardId.value = cardId;
  showCardSelector.value = false;

  // Load the landing page design for the selected card
  const selectedCard = userNfcCards.value.find((card) => card.id == cardId);
  if (selectedCard) {
    console.log("Selected NFC Card:", selectedCard);
    $toast.success(
      `Selected: ${selectedCard.nfc_card_id || "Card #" + cardId}`
    );

    // Load the landing page for this card
    try {
      await loadProfile();
    } catch (profileError) {
      console.error("Failed to load profile for manually selected card:", profileError);
    }
  }
};

const toggleCardSelector = () => {
  showCardSelector.value = !showCardSelector.value;
};

const getPlanBadgeClass = (plan) => {
  const planLower = (plan || "free").toLowerCase();
  switch (planLower) {
    case "business":
      return "bg-purple-100 text-purple-800";
    case "premium":
      return "bg-gradient-to-r from-yellow-100 to-orange-100 text-orange-800";
    case "basic":
      return "bg-blue-100 text-blue-800";
    default:
      return "bg-gray-100 text-gray-600";
  }
};

// Check if mobile
const checkMobile = () => {
  isMobile.value = window.innerWidth < 1024;
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  const cardSelector = event.target.closest(".relative");
  if (!cardSelector && showCardSelector.value) {
    showCardSelector.value = false;
  }
};

// Update preview data in localStorage
const updatePreviewData = () => {
  if (!selectedNfcCardId.value) return;

  const previewData = {
    ...profileData,
    links: links.value.filter(link => link.is_active).map(link => ({
      title: link.title,
      url: link.url,
      platform: link.platform,
      is_active: link.is_active,
      order: link.order,
    })),
    timestamp: Date.now(),
  };
  
  try {
    localStorage.setItem('nfc_preview_data', JSON.stringify(previewData));
    // Force iframe reload by updating key
    previewKey.value++;
  } catch (error) {
    console.error('Failed to save preview data:', error);
  }
};

// Iframe loaded callback
const onPreviewLoad = () => {
  console.log('✅ Preview iframe loaded successfully');
  const selectedCard = getSelectedCard();
  if (selectedCard) {
    console.log('Card ID:', selectedCard.nfc_card_id);
    console.log('Preview URL:', `/profile/${selectedCard.nfc_card_id}?preview=true`);
  }
};

// Iframe error callback
const onPreviewError = (error) => {
  console.error('❌ Preview iframe error:', error);
  const selectedCard = getSelectedCard();
  if (selectedCard) {
    console.error('Failed URL:', `/profile/${selectedCard.nfc_card_id}?preview=true`);
  }
  $toast.error('Failed to load preview. Please try again.');
};

// Force refresh preview
const forceRefreshPreview = () => {
  console.log('🔄 Force refreshing preview...');
  updatePreviewData();
  $toast.success('Preview refreshed');
};

// Open Landing Page in new tab
const openLandingPage = () => {
  if (!selectedNfcCardId.value) {
    $toast.error("Please select an NFC card first");
    return;
  }

  const selectedCard = userNfcCards.value.find(
    (card) => card.id === selectedNfcCardId.value
  );

  if (!selectedCard || !selectedCard.nfc_card_id) {
    $toast.error("Card information not found");
    return;
  }

  // Update preview data before opening
  updatePreviewData();

  // Open landing page in new tab
  const landingPageUrl = `/profile/${selectedCard.nfc_card_id}?preview=true`;
  window.open(landingPageUrl, '_blank');
  
  $toast.success("Opening live preview in new tab");
};

// Initialize
onMounted(async () => {
  checkMobile();
  window.addEventListener("resize", checkMobile);

  // Load user's NFC cards (which will auto-select a card and load its landing page)
  // Use nextTick to ensure DOM is ready
  await nextTick();
  await loadUserNfcCards();
  
  // Load all employee cards for batch design feature
  await loadAllEmployeeCards();
  
  // Note: Links are now loaded as part of loadProfile() via landing page
  
  // Initialize preview data after a short delay
  setTimeout(() => {
    updatePreviewData();
  }, 1000);

  // Check if we should open Apply Design modal from URL parameter
  const route = useRoute();
  if (route.query.openApplyDesign === 'true') {
    // Wait a bit for data to load before opening modal
    setTimeout(() => {
      showApplyDesignModal.value = true;
    }, 1500);
  }

  // Close card selector when clicking outside
  document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
  window.removeEventListener("resize", checkMobile);
  document.removeEventListener("click", handleClickOutside);
  
  // Clear update timeout
  if (updatePreviewTimeout) clearTimeout(updatePreviewTimeout);
});

// Watch for changes and update preview in real-time
let updatePreviewTimeout = null;

watch(
  [() => profileData, () => links.value],
  () => {
    // Debounce the update to avoid too many refreshes
    if (updatePreviewTimeout) clearTimeout(updatePreviewTimeout);
    updatePreviewTimeout = setTimeout(() => {
      updatePreviewData();
    }, 1000); // 1 second debounce for iframe
  },
  { deep: true }
);
</script>

<style scoped>
/* Add any custom styles here */
.animate-fade-in-up {
  animation: fadeInUp 0.3s ease-out;
}

/* Hide scrollbar for preview iframe */
iframe {
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* IE and Edge */
}

iframe::-webkit-scrollbar {
  display: none; /* Chrome, Safari, Opera */
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
