<!-- pages/UserDashboard/ProfileBuilder.vue -->
<template>
  <div class="min-h-screen bg-gray-50">
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
                      :src="profileData.image || '/default-avatar.png'"
                      :class="[
                        'mx-auto object-cover mb-3 sm:mb-4',
                        profileData.profileStyle === 'classic'
                          ? 'w-20 h-20 sm:w-24 sm:h-24 rounded-full'
                          : 'w-full h-24 sm:h-32 rounded-xl',
                      ]"
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
  </div>
</template>

<script setup>
// import ProfileImageUpload from '~/components/ProfileImageUpload.vue'
import { useAuthStore } from "~/stores/auth";

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
  showWatermark: true,
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

const profileStyles = [
  { id: "classic", name: "Classic" },
  { id: "hero", name: "Hero" },
];

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
  saving.value = true;
  try {
    const response = await $api.put("/user/profile", {
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
    });

    if (response.success) {
      $toast.success("Profile saved successfully!");
    } else {
      throw new Error("Failed to save profile");
    }
  } catch (error) {
    console.error("Save profile error:", error);
    if (error.data?.message) {
      $toast.error(error.data.message);
    } else {
      $toast.error("Failed to save profile");
    }
  } finally {
    saving.value = false;
  }
};

const loadProfile = async () => {
  try {
    const response = await $api.get("/user/profile");

    if (response.success && response.profile) {
      const profile = response.profile;

      // Basic Info
      profileData.name = profile.name || "";
      profileData.position = profile.title || profile.position || "";
      profileData.qualification = profile.qualification || "";
      profileData.bio = profile.bio || "";
      profileData.contactNumber = profile.phone || profile.contactNumber || "";
      profileData.email = profile.email || "";
      profileData.website = profile.website || "";
      profileData.image = profile.profile_image || null;

      // Company Info
      profileData.companyLogo = profile.company_logo || null;
      profileData.companyLogoText = profile.company_logo_text || "";
      profileData.companyName = profile.company_name || "";
      profileData.companyRegistrationNo = profile.company_registration_no || "";
      profileData.companyDepartment = profile.company_department || "";

      // Address Info
      profileData.addressName = profile.address_name || "";
      profileData.addressStreet = profile.address_street || "";
      profileData.addressArea = profile.address_area || "";
      profileData.addressCityState = profile.address_city_state || "";
      profileData.addressCountry = profile.address_country || "";
      profileData.addressMapUrl = profile.address_map_url || "";

      // Stats
      if (profile.stats && Array.isArray(profile.stats)) {
        profileData.stats = profile.stats;
      }

      // Services
      if (profile.services && Array.isArray(profile.services)) {
        profileData.services = profile.services;
      }

      // Contact Methods
      profileData.phoneNumber = profile.phone_number || "";
      profileData.phoneLabel = profile.phone_label || "Phone";
      profileData.emailAddress = profile.email_address || profile.email || "";
      profileData.emailLabel = profile.email_label || "Email";
      profileData.whatsappNumber = profile.whatsapp_number || "";
      profileData.whatsappLabel = profile.whatsapp_label || "WhatsApp";
      profileData.websiteUrl = profile.website_url || profile.website || "";
      profileData.websiteLabel = profile.website_label || "Website";

      // Social Links
      if (profile.social_links && Array.isArray(profile.social_links)) {
        profileData.socialLinks = profile.social_links;
      }

      // Team Members
      if (profile.team_members && Array.isArray(profile.team_members)) {
        profileData.teamMembers = profile.team_members;
      }

      // Design Settings
      profileData.profileStyle = profile.profile_style || "classic";
      profileData.theme = profile.theme || "minimal";
      profileData.backgroundColor = profile.background_color || "#FFFFFF";
      profileData.textColor = profile.text_color || "#000000";
      profileData.font = profile.font || "inter";
      profileData.buttonStyle = profile.button_style || "solid";
      profileData.showWatermark = profile.show_watermark !== false;
    }
  } catch (error) {
    console.error("Error loading profile:", error);
    $toast.error("Failed to load profile");
  }
};

// Check if mobile
const checkMobile = () => {
  isMobile.value = window.innerWidth < 1024;
};

// Initialize
onMounted(() => {
  checkMobile();
  window.addEventListener("resize", checkMobile);
  loadProfile(); // Load profile data when component mounts
});

onUnmounted(() => {
  window.removeEventListener("resize", checkMobile);
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
</style>
