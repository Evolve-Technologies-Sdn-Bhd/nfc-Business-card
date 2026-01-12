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

                      <!-- Right: Check Icon -->
                      <Icon
                        v-if="selectedNfcCardId === card.id"
                        name="heroicons:check-circle-solid"
                        class="w-6 h-6 text-blue-500 flex-shrink-0"
                      />
                    </div>
                  </div>
                </div>
              </div>
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
                      to="/UserDashboard/CardManagement"
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
                <div class="grid grid-cols-2 gap-2 sm:gap-3">
                  <div
                    v-for="style in profileStyles"
                    :key="style.id"
                    @click="profileData.profileStyle = style.id"
                    :class="[
                      'relative cursor-pointer rounded-lg border-2 p-3 sm:p-4 transition-all',
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
                    maxlength="500"
                    placeholder="Tell visitors about yourself..."
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  ></textarea>
                  <p class="text-xs text-gray-500 mt-1 text-right">
                    {{ (profileData.bio || '').length }} / 500 characters
                  </p>
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
              <div class="space-y-3 sm:space-y-4">
                <h3 class="text-sm font-medium text-gray-700">
                  Company Information
                </h3>

                <!-- Company Logo Upload -->
                <ProfileImageUpload
                  v-model="profileData.companyLogo"
                  upload-endpoint="/upload/company-logo"
                  delete-endpoint="/upload/company-logo"
                  label="Company Logo"
                  help-text="Will appear on your profile"
                  alt-text="Company logo"
                  :nfc-card-id="selectedNfcCardId"
                  @upload-success="handleCompanyLogoUpload"
                />

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Company Logo Text
                    <span class="text-xs text-gray-500"
                      >(Fallback if no logo)</span
                    ></label
                  >
                  <input
                    v-model="profileData.companyLogoText"
                    type="text"
                    placeholder="e.g., CLB"
                    maxlength="5"
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Company Name</label
                  >
                  <input
                    v-model="profileData.companyName"
                    type="text"
                    placeholder="Your company name"
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Registration Number
                    <span class="text-xs text-gray-500">(Optional)</span></label
                  >
                  <input
                    v-model="profileData.companyRegistrationNo"
                    type="text"
                    placeholder="e.g., 123456789-X"
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Department
                    <span class="text-xs text-gray-500">(Optional)</span></label
                  >
                  <input
                    v-model="profileData.companyDepartment"
                    type="text"
                    placeholder="e.g., Sales & Marketing Division"
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>

              <!-- Address Section -->
              <div class="space-y-3 sm:space-y-4 pt-4 border-t">
                <h3 class="text-sm font-medium text-gray-700">
                  Business Address
                </h3>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Location Name</label
                  >
                  <input
                    v-model="profileData.addressName"
                    type="text"
                    placeholder="e.g., CLB Group Headquarters"
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
                    placeholder="e.g., 18, Jalan Mutiara Emas 5/5"
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Area/District</label
                  >
                  <input
                    v-model="profileData.addressArea"
                    type="text"
                    placeholder="e.g., Taman Mount Austin"
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >City & State</label
                  >
                  <input
                    v-model="profileData.addressCityState"
                    type="text"
                    placeholder="e.g., 81100 Johor Bahru, Johor"
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
                    >Google Maps URL
                    <span class="text-xs text-gray-500">(Optional)</span></label
                  >
                  <input
                    v-model="profileData.addressMapUrl"
                    type="url"
                    placeholder="https://maps.google.com/?q=..."
                    class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>

              <!-- Contact Methods Section -->
              <div class="space-y-3 sm:space-y-4 pt-4 border-t">
                <h3 class="text-sm font-medium text-gray-700">
                  Contact Methods
                </h3>

                <div class="grid grid-cols-2 gap-3">
                  <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                      >Phone Number</label
                    >
                    <input
                      v-model="profileData.phoneNumber"
                      type="tel"
                      placeholder="+60 16-778 7616"
                      class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>

                  <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                      >Email Address</label
                    >
                    <input
                      v-model="profileData.emailAddress"
                      type="email"
                      placeholder="contact@company.com"
                      class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>

                  <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                      >WhatsApp Number
                      <span class="text-xs text-gray-500"
                        >(Optional)</span
                      ></label
                    >
                    <input
                      v-model="profileData.whatsappNumber"
                      type="tel"
                      placeholder="+60167787616"
                      class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>

                  <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                      >Website URL
                      <span class="text-xs text-gray-500"
                        >(Optional)</span
                      ></label
                    >
                    <input
                      v-model="profileData.websiteUrl"
                      type="url"
                      placeholder="https://www.yourcompany.com"
                      class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Services Tab -->
            <div v-if="activeTab === 'services'" class="space-y-4 sm:space-y-6">
              <div class="space-y-3 sm:space-y-4">
                <h3 class="text-sm font-medium text-gray-700">
                  Services & Expertise
                </h3>
                <p class="text-xs text-gray-500">
                  Add up to 6 services or areas of expertise
                </p>

                <div
                  v-for="(service, index) in profileData.services"
                  :key="index"
                  class="grid grid-cols-4 gap-3"
                >
                  <input
                    v-model="service.icon"
                    type="text"
                    placeholder="📱"
                    maxlength="2"
                    class="col-span-1 px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center"
                  />
                  <input
                    v-model="service.name"
                    type="text"
                    placeholder="Service name"
                    class="col-span-3 px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>

              <!-- Team Members Section -->
              <div class="space-y-3 sm:space-y-4 pt-4 border-t">
                <h3 class="text-sm font-medium text-gray-700">Team Members</h3>
                <p class="text-xs text-gray-500">
                  Showcase up to 3 team members (optional)
                </p>

                <div
                  v-for="(member, index) in profileData.teamMembers"
                  :key="index"
                  class="grid grid-cols-6 gap-3"
                >
                  <input
                    v-model="member.initials"
                    type="text"
                    placeholder="JD"
                    maxlength="3"
                    class="col-span-1 px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center uppercase"
                  />
                  <input
                    v-model="member.name"
                    type="text"
                    placeholder="Full Name"
                    class="col-span-3 px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                  <input
                    v-model="member.role"
                    type="text"
                    placeholder="Role"
                    class="col-span-2 px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>
            </div>

            <!-- Social Tab -->
            <div v-if="activeTab === 'social'" class="space-y-4 sm:space-y-6">
              <div class="space-y-3 sm:space-y-4">
                <h3 class="text-sm font-medium text-gray-700">
                  Social Media Links
                </h3>
                <p class="text-xs text-gray-500">
                  Add links to your social media profiles
                </p>

                <div
                  v-for="(social, index) in profileData.socialLinks"
                  :key="index"
                  class="grid grid-cols-8 gap-3"
                >
                  <input
                    v-model="social.emoji"
                    type="text"
                    placeholder="📱"
                    maxlength="2"
                    class="col-span-1 px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center"
                  />
                  <input
                    v-model="social.name"
                    type="text"
                    placeholder="Platform name"
                    class="col-span-2 px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                  <input
                    v-model="social.url"
                    type="url"
                    placeholder="https://..."
                    class="col-span-5 px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>
            </div>

            <!-- Links Tab -->
            <div v-if="activeTab === 'links'" class="space-y-4 sm:space-y-6">
              <div class="flex items-center justify-between mb-4">
                <div>
                  <h3 class="text-sm font-medium text-gray-700">
                    Manage Links
                  </h3>
                  <p class="text-xs text-gray-500 mt-1">
                    {{ links.length }} links
                  </p>
                </div>
              </div>

              <!-- Empty State -->
              <div v-if="links.length === 0" class="text-center py-8 sm:py-12">
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
              <div v-else>
                <draggable
                  v-model="links"
                  @end="updateLinkOrder"
                  item-key="id"
                  class="space-y-3"
                  handle=".drag-handle"
                >
                  <template #item="{ element: link }">
                    <div class="group relative">
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
                    </div>
                  </template>
                </draggable>

                <!-- Add Link Button -->
                <button
                  @click="showAddLinkModal = true"
                  class="w-full mt-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-600 transition-colors flex items-center justify-center"
                >
                  <Icon name="heroicons:plus" class="h-5 w-5 mr-2" />
                  Add Link
                </button>
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
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                  <div
                    v-for="theme in themes"
                    :key="theme.id"
                    @click="applyTheme(theme)"
                    :class="[
                      'relative cursor-pointer rounded-lg border-2 p-2 sm:p-3 transition-all',
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
                <div class="grid grid-cols-2 gap-2 sm:gap-3">
                  <div
                    v-for="style in buttonStyles"
                    :key="style.id"
                    @click="profileData.buttonStyle = style.id"
                    :class="[
                      'relative p-3 sm:p-4 rounded-lg border-2 cursor-pointer transition-all',
                      profileData.buttonStyle === style.id
                        ? 'border-blue-500 bg-blue-50'
                        : 'border-gray-200 hover:border-gray-300',
                    ]"
                  >
                    <button
                      :class="[
                        'w-full py-1.5 sm:py-2 px-3 sm:px-4 text-xs sm:text-sm',
                        style.class,
                      ]"
                      disabled
                    >
                      {{ style.name }}
                    </button>
                    <Icon
                      v-if="profileData.buttonStyle === style.id"
                      name="heroicons:check-circle"
                      class="absolute top-1 right-1 sm:top-2 sm:right-2 w-3 h-3 sm:w-4 sm:h-4 text-blue-500"
                    />
                  </div>
                </div>
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
                <!-- Dynamic Background -->
                <div
                  :style="{ backgroundColor: profileData.backgroundColor }"
                  class="absolute inset-0 rounded-2xl sm:rounded-3xl"
                ></div>

                <div class="relative z-10">
                  <!-- Profile Section -->
                  <div class="text-center mb-4 sm:mb-6">
                    <img
                      :src="
                        getImageUrl(profileData.image) || '/default-avatar.png'
                      "
                      alt="Profile preview"
                      :class="[
                        'mx-auto object-cover mb-3 sm:mb-4',
                        profileData.profileStyle === 'classic'
                          ? 'w-20 h-20 sm:w-24 sm:h-24 rounded-full'
                          : 'w-full h-24 sm:h-32 rounded-xl',
                      ]"
                      @error="(e) => (e.target.src = '/default-avatar.png')"
                    />
                    <h2
                      :style="{
                        fontFamily: getCurrentFont(),
                        color: profileData.textColor,
                      }"
                      class="text-lg sm:text-xl font-bold"
                    >
                      {{ profileData.name || "Your Name" }}
                    </h2>
                    <p
                      :style="{ color: profileData.textColor }"
                      class="text-sm sm:text-base mt-1 sm:mt-2 opacity-80"
                    >
                      {{ profileData.bio || "Your bio" }}
                    </p>
                  </div>

                  <!-- Sample Links/Actions -->
                  <div class="space-y-2 sm:space-y-3">
                    <a
                      v-for="link in sampleLinks"
                      :key="link.id"
                      :href="link.url"
                      :class="[
                        'block w-full text-center transition-all text-sm sm:text-base',
                        getButtonClass(),
                      ]"
                    >
                      {{ link.title }}
                    </a>
                  </div>

                  <!-- Watermark -->
                  <div
                    v-if="profileData.showWatermark"
                    class="mt-6 sm:mt-8 text-center"
                  >
                    <p
                      :style="{ color: profileData.textColor }"
                      class="text-xs opacity-60"
                    >
                      Powered by
                    </p>
                    <p
                      :style="{ color: profileData.textColor }"
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
              {{ savingLink ? "Adding..." : "Add Link" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
// import ProfileImageUpload from '~/components/ProfileImageUpload.vue'
import { useAuthStore } from "~/stores/auth";
import draggable from "vuedraggable";

// Layout
definePageMeta({
  layout: "user-dashboard",
  middleware: ["auth"],
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

  // Contact Methods (phone, email, whatsapp, website)
  phoneNumber: "",
  phoneLabel: "Phone",
  emailAddress: "",
  emailLabel: "Email",
  whatsappNumber: "",
  whatsappLabel: "WhatsApp",
  websiteUrl: "",
  websiteLabel: "Website",

  // Social Links (max 4)
  socialLinks: [
    { emoji: "📘", name: "Facebook", url: "" },
    { emoji: "💼", name: "LinkedIn", url: "" },
    { emoji: "📸", name: "Instagram", url: "" },
    { emoji: "🐦", name: "Twitter", url: "" },
  ],

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
  bio: "",
});

// Links management
const links = ref([]);
const showAddLinkModal = ref(false);
const showPlatformForm = ref(false);
const selectedPlatform = ref(null);
const insertIndex = ref(null);
const searchQuery = ref("");
const savingLink = ref(false);

// Link form
const linkForm = reactive({
  title: "",
  url: "",
  platform: "",
  is_active: true,
});

// Sample links for preview
const sampleLinks = [
  { id: 1, title: "Contact Me", url: "#" },
  { id: 2, title: "My Portfolio", url: "#" },
  { id: 3, title: "Social Media", url: "#" },
];

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
    id: "social",
    name: "Social",
    shortName: "Social",
    icon: "heroicons:globe-alt",
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

// Popular platforms for link management
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

const getButtonClass = () => {
  const style = buttonStyles.find((s) => s.id === profileData.buttonStyle);
  return `py-2.5 sm:py-3 px-5 sm:px-6 ${
    style ? style.class : buttonStyles[0].class
  }`;
};

const saveProfile = async () => {
  if (!selectedNfcCardId.value) {
    $toast.error("Please select an NFC card first");
    return;
  }

  saving.value = true;
  try {
    // Save the landing page design for the selected NFC card
    const response = await $api.put(
      `/nfc-cards/${selectedNfcCardId.value}/landing-page`,
      {
        // Basic Info
        name: profileData.name,
        title: profileData.position,
        qualification: profileData.qualification,
        bio: profileData.bio,
        phone: profileData.contactNumber,
        email: profileData.email,
        website: profileData.website,
        profile_image: profileData.image,

        // Company Info
        company_logo: profileData.companyLogo,
        company_logo_text: profileData.companyLogoText,
        company_name: profileData.companyName,
        company_registration_no: profileData.companyRegistrationNo,
        company_department: profileData.companyDepartment,

        // Address Info
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

        // Contact Methods
        phone_number: profileData.phoneNumber,
        phone_label: profileData.phoneLabel,
        email_address: profileData.emailAddress,
        email_label: profileData.emailLabel,
        whatsapp_number: profileData.whatsappNumber,
        whatsapp_label: profileData.whatsappLabel,
        website_url: profileData.websiteUrl,
        website_label: profileData.websiteLabel,

        // Social Links
        social_links: profileData.socialLinks,

        // Team Members
        team_members: profileData.teamMembers,

        // Design Settings
        profile_style: profileData.profileStyle,
        theme: profileData.theme,
        background_color: profileData.backgroundColor,
        text_color: profileData.textColor,
        font: profileData.font,
        button_style: profileData.buttonStyle,
        show_watermark: profileData.showWatermark,
      }
    );

    if (response.success) {
      $toast.success("Landing page saved successfully!");
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

      // Map backend fields to frontend fields
      profileData.name = landingPage.name || "";
      profileData.position = landingPage.title || landingPage.position || "";
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
      profileData.bio = landingPage.bio || "";
      profileData.showWatermark = landingPage.show_watermark !== false;

      console.log("Loaded landing page for card:", selectedNfcCardId.value);
    } else {
      // No landing page exists yet for this card, reset to defaults
      resetProfileData();
      console.log("No landing page found for card, using defaults");
    }
  } catch (error) {
    console.error("Error loading landing page:", error);

    // If 404, this card has no landing page yet - reset to defaults
    if (error.response?.status === 404) {
      resetProfileData();
      console.log("Card has no landing page yet, reset to defaults");
    } else {
      // Other errors
      $toast.error("Failed to load landing page");
    }
  }
};

// Reset profile data to defaults
const resetProfileData = () => {
  profileData.name = "";
  profileData.position = "";
  profileData.contactNumber = "";
  profileData.email = "";
  profileData.website = "";
  profileData.address = "";
  profileData.image = null;
  profileData.companyLogo = null;
  profileData.profileStyle = "classic";
  profileData.theme = "minimal";
  profileData.backgroundColor = "#FFFFFF";
  profileData.textColor = "#000000";
  profileData.font = "inter";
  profileData.buttonStyle = "solid";
  profileData.bio = "";
  profileData.showWatermark = true;
};

// NFC Card selection
const userNfcCards = ref([]);
const selectedNfcCardId = ref(null);
const loadingCards = ref(false);

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
      userNfcCards.value = response.nfc_cards;

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
          await loadProfile();
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
          await loadProfile();
        }
      } else if (userNfcCards.value.length > 0 && !selectedNfcCardId.value) {
        // Auto-select the first card if available and none selected
        selectedNfcCardId.value = userNfcCards.value[0].id;
        // Load landing page for the first card
        await loadProfile();
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

    // Load the landing page and links for this card
    await loadProfile();
    await loadLinks();
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

const getSelectedCard = () => {
  return userNfcCards.value.find((card) => card.id === selectedNfcCardId.value);
};

// Link management computed
const activeLinks = computed(() => {
  return links.value
    .filter((link) => link.is_active)
    .sort((a, b) => (a.order || 0) - (b.order || 0));
});

// Link management methods
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
  if (!selectedNfcCardId.value) return;
  
  try {
    const response = await $api.get(`/nfc-cards/${selectedNfcCardId.value}/links`);
    if (response.success) {
      links.value = response.links || [];
    }
  } catch (error) {
    console.error("Error loading links:", error);
    // Don't show error toast as links might not exist yet
    links.value = [];
  }
};

const saveLink = async () => {
  if (!selectedNfcCardId.value) {
    $toast.error("Please select an NFC card first");
    return;
  }

  try {
    savingLink.value = true;
    const response = await $api.post(`/nfc-cards/${selectedNfcCardId.value}/links`, linkForm);

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
  if (!selectedNfcCardId.value) return;

  try {
    await $api.put(`/nfc-cards/${selectedNfcCardId.value}/links/${link.id}`, {
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
  if (!selectedNfcCardId.value) return;

  try {
    await $api.delete(`/nfc-cards/${selectedNfcCardId.value}/links/${link.id}`);

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
  if (!selectedNfcCardId.value) return;

  try {
    const linksOrder = links.value.map((link, index) => ({
      id: link.id,
      order: index,
    }));

    await $api.post(`/nfc-cards/${selectedNfcCardId.value}/links/reorder`, { links: linksOrder });
    $toast.success("Link order updated");
  } catch (error) {
    console.error("Failed to update link order:", error);
    $toast.error("Failed to update link order");
  }
};

const toggleLink = async (link) => {
  try {
    link.is_active = !link.is_active;
    await updateLink(link);
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

// Check if mobile
const checkMobile = () => {
  isMobile.value = window.innerWidth < 1024;
};

// Initialize
onMounted(async () => {
  checkMobile();
  window.addEventListener("resize", checkMobile);

  // Load user's NFC cards (which will auto-select a card and load its landing page)
  // Use nextTick to ensure DOM is ready
  await nextTick();
  await loadUserNfcCards();

  // Close card selector when clicking outside
  document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
  window.removeEventListener("resize", checkMobile);
  document.removeEventListener("click", handleClickOutside);
});

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  const cardSelector = event.target.closest(".relative");
  if (!cardSelector && showCardSelector.value) {
    showCardSelector.value = false;
  }
};
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
