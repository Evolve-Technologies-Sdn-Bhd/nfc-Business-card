<!-- pages/UserDashboard/ProfileBuilder.vue -->
<template>
  <div>
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
          <p class="mt-4 text-sm text-secondary-600">Loading your profile...</p>
        </div>
      </div>
    </Transition>

    <!-- Header -->
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-secondary-900">Profile Builder</h1>
          <p class="mt-1 sm:mt-2 text-sm sm:text-base text-secondary-600">
            Customize your digital business card profile
          </p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center gap-2 sm:gap-3">
            <!-- NFC Card Selector -->
            <div class="relative">
              <button
                @click="toggleCardSelector"
                :disabled="loadingCards || userNfcCards.length === 0"
                class="btn btn-outline flex items-center gap-2"
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
            <button
              @click="showApplyDesignModal = true"
              :disabled="!selectedNfcCardId || (userNfcCards.length <= 1 && allEmployeeCards.length === 0)"
              class="btn btn-outline flex items-center gap-2"
            >
              <Icon name="heroicons:paint-brush" class="h-5 w-5" />
              <span class="hidden sm:inline">Apply Design</span>
            </button>

            <!-- Save Button -->
            <button
              @click="saveProfile"
              :disabled="saving || !selectedNfcCardId"
              class="btn btn-primary"
            >
              <Icon v-if="saving" name="heroicons:arrow-path" class="h-5 w-5 mr-2 animate-spin" />
              {{ saving ? "Saving..." : "Save Profile" }}
            </button>

            <!-- View Saved Landing Page Button -->
            <button
              @click="openSavedLandingPage"
              :disabled="!selectedNfcCardId"
              class="btn btn-success flex items-center gap-2"
              title="Open the saved landing page (without preview mode)"
            >
              <Icon name="heroicons:eye" class="h-5 w-5" />
              <span class="hidden sm:inline">View Saved</span>
            </button>
          </div>
        </div>
      </div>

    <!-- Always display two-column layout -->
    <div class="grid grid-cols-1 md:grid-cols-7 gap-4 sm:gap-6">
      <!-- Left Panel - Editor (5/7 width) -->
      <div class="space-y-4 sm:space-y-6 md:col-span-4">

        <!-- Main Category Tabs -->
        <div class="card">
          <div class="card-body">
            <!-- Display message when no options are available -->
            <div v-if="availableGeneralTabs.length === 0 && availableDesignTabs.length === 0" 
                 class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-center">
              <Icon name="heroicons:exclamation-triangle" class="w-8 h-8 text-yellow-400 mx-auto mb-2" />
              <h3 class="text-sm font-medium text-yellow-800 mb-1">No Options Available</h3>
              <p class="text-xs text-yellow-700">
                Your admin has not assigned any sections or features to your account yet.
                Please contact your administrator for assistance.
              </p>
            </div>
            
            <div v-else>
              <!-- Main Category Tabs Container -->
              <div class="flex items-center gap-1 sm:gap-2 border-b border-gray-200 pb-3 mb-5">
                <!-- Only show General Sections button when general options are available -->
                <button
                  v-if="availableGeneralTabs.length > 0"
                  @click="switchToCategory('general')"
                  :class="[
                    'px-4 sm:px-5 py-2.5 font-medium text-sm rounded-xl transition-all duration-200',
                    mainCategory === 'general'
                      ? 'bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 shadow-sm ring-1 ring-blue-200'
                      : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50',
                  ]"
                >
                  General Sections
                </button>
                <!-- Only show Design Sections button when design options are available -->
                <button
                  v-if="availableDesignTabs.length > 0"
                  @click="switchToCategory('design')"
                  :class="[
                    'px-4 sm:px-5 py-2.5 font-medium text-sm rounded-xl transition-all duration-200',
                    mainCategory === 'design'
                      ? 'bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 shadow-sm ring-1 ring-blue-200'
                      : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50',
                  ]"
                >
                  Design Sections
                </button>
              </div>

              <!-- Sub Tabs for General Sections -->
              <div v-if="mainCategory === 'general'" class="flex flex-wrap gap-2.5 sm:gap-3">
                <button
                  v-for="tab in availableGeneralTabs"
                  :key="tab.id"
                  @click="activeTab = tab.id"
                  :class="[
                    'px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl text-sm font-medium transition-all duration-200 flex items-center gap-2.5 border',
                    activeTab === tab.id
                      ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white border-transparent shadow-md shadow-blue-200/50'
                      : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800',
                  ]"
                >
                  <Icon :name="tab.icon" class="w-4.5 h-4.5" />
                  {{ tab.name }}
                </button>
              </div>

              <!-- Sub Tabs for Design Sections -->
              <div v-if="mainCategory === 'design'" class="flex flex-wrap gap-2.5 sm:gap-3">
                <button
                  v-for="tab in availableDesignTabs"
                  :key="tab.id"
                  @click="activeTab = tab.id"
                  :class="[
                    'px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl text-sm font-medium transition-all duration-200 flex items-center gap-2.5 border',
                    activeTab === tab.id
                      ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white border-transparent shadow-md shadow-blue-200/50'
                      : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800',
                  ]"
                >
                  <Icon :name="tab.icon" class="w-4.5 h-4.5" />
                  {{ tab.name }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab Content -->
        <div class="card">
          <div class="card-body">
            <!-- Content to display when no options are available -->
            <div v-if="availableGeneralTabs.length === 0 && availableDesignTabs.length === 0" class="space-y-4 sm:space-y-6">
              <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-xl p-8 text-center shadow-sm">
                <div class="w-32 h-32 mx-auto mb-6 relative">
                  <div class="absolute inset-0 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-full opacity-10 animate-pulse"></div>
                  <div class="absolute inset-3 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full opacity-20"></div>
                  <div class="absolute inset-0 flex items-center justify-center">
                    <Icon name="heroicons:document-text" class="w-16 h-16 text-indigo-500" />
                  </div>
                </div>
                <h3 class="text-xl font-semibold text-indigo-900 mb-3">No Profile Options Available</h3>
                <p class="text-sm text-indigo-700 mb-6 max-w-md mx-auto">
                  Your administrator has not assigned any profile sections or design options to your account yet.
                  Please contact your administrator for assistance.
                </p>
                <a href="/UserDashboard" class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg shadow-md hover:from-blue-700 hover:to-indigo-700 transition-all duration-200">
                  <Icon name="heroicons:arrow-left" class="w-4 h-4 mr-2" />
                  Return to Dashboard
                </a>
              </div>
            </div>
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

              <!-- Profile Style section removed -->

              <!-- Profile Images -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <ProfileImageUpload
                  v-if="shouldShowField('profilePicture')"
                  v-model="profileData.profilePicture"
                  upload-endpoint="/upload/profile-image"
                  delete-endpoint="/upload/profile-image"
                  :label="getFieldConfig('profilePicture')?.label || 'Profile Picture'"
                  :help-text="getFieldConfig('profilePicture')?.help_text || 'JPG, PNG, GIF or WebP. Max 5MB'"
                  :nfc-card-id="selectedNfcCardId"
                  @upload-success="handleProfileImageUpload"
                />
                <ProfileImageUpload
                  v-if="shouldShowField('coverBanner')"
                  v-model="profileData.coverBanner"
                  upload-endpoint="/upload/cover-banner"
                  delete-endpoint="/upload/cover-banner"
                  :label="getFieldConfig('coverBanner')?.label || 'Cover Banner'"
                  :help-text="getFieldConfig('coverBanner')?.help_text || 'Recommended size: 1200x400px'"
                  :nfc-card-id="selectedNfcCardId"
                  aspect-ratio="3:1"
                />
              </div>

              <!-- Dynamic Field Groups (from Backend) -->
              <div 
                v-for="group in getGroupedProfileFields()" 
                :key="group.name"
                class="bg-white border border-gray-200 rounded-xl p-4 space-y-4"
              >
                <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                  <Icon :name="group.icon" class="w-5 h-5 text-blue-600" />
                  <h3 class="text-sm font-semibold text-gray-800">{{ group.name }}</h3>
                </div>
                <div class="grid grid-cols-1 gap-4" :class="{ 'md:grid-cols-2': group.fields.length > 1 && !group.fields.some(f => f.field_type === 'richtext' || f.field_type === 'textarea' || f.field_type === 'repeater') }">
                  <DynamicFormField
                    v-for="field in group.fields"
                    :key="field.field_key"
                    :field="field"
                    v-model="profileData"
                    :user-plan="authStore.user?.subscription_plan || 'business'"
                  />
                </div>
              </div>
            </div>

            <!-- Company Tab - Dynamic Rendering -->
            <div v-if="activeTab === 'company'" class="space-y-4 sm:space-y-6">
              <!-- Company Logo -->
              <ProfileImageUpload
                v-if="shouldShowField('companyLogo')"
                v-model="profileData.companyLogo"
                upload-endpoint="/upload/company-logo"
                delete-endpoint="/upload/company-logo"
                :label="getFieldConfig('companyLogo')?.label || 'Company Logo'"
                :help-text="getFieldConfig('companyLogo')?.help_text || 'Will appear as background watermark'"
                alt-text="Company logo"
                :nfc-card-id="selectedNfcCardId"
                @upload-success="handleCompanyLogoUpload"
              />

              <!-- Dynamic Field Groups (from Backend) - exclude teamMembers -->
              <div 
                v-for="group in getGroupedCompanyFields().filter(g => g.name !== 'Team Members')" 
                :key="group.name"
                class="bg-white border border-gray-200 rounded-xl p-4 space-y-4"
              >
                <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                  <Icon :name="group.icon" class="w-5 h-5 text-blue-600" />
                  <h3 class="text-sm font-semibold text-gray-800">{{ group.name }}</h3>
                </div>
                <div class="grid grid-cols-1 gap-4" :class="{ 'md:grid-cols-2': group.fields.length > 1 && !group.fields.some(f => f.field_type === 'richtext' || f.field_type === 'textarea' || f.field_type === 'repeater') }">
                  <DynamicFormField
                    v-for="field in group.fields"
                    :key="field.field_key"
                    :field="field"
                    v-model="profileData"
                    :user-plan="authStore.user?.subscription_plan || 'business'"
                  />
                </div>
              </div>

              <!-- Simplified Location & Address (3 lines) -->
              <div v-if="shouldShowField('addressName') || shouldShowField('mapUrl') || shouldShowField('address1')" class="bg-white border border-gray-200 rounded-xl p-4 space-y-4">
                <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                  <Icon name="heroicons:map-pin" class="w-5 h-5 text-blue-600" />
                  <h3 class="text-sm font-semibold text-gray-800">Location &amp; Address</h3>
                </div>
                <div class="space-y-4">
                  <!-- Address Line 1 -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Address Line 1</label>
                    <input
                      v-model="profileData.address1"
                      type="text"
                      placeholder="e.g., No 38, Jalan Riang 21"
                      class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                    />
                  </div>
                  <!-- Address Line 2 -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Address Line 2 <span class="text-gray-400 font-normal">(Optional)</span></label>
                    <input
                      v-model="profileData.address2"
                      type="text"
                      placeholder="e.g., Taman Ria, Kawasan Perindustrian"
                      class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                    />
                  </div>
                  <!-- Address Line 3 -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Address Line 3 <span class="text-gray-400 font-normal">(Optional)</span></label>
                    <input
                      v-model="profileData.address3"
                      type="text"
                      placeholder="e.g., 81200 Johor Bahru, Johor, Malaysia"
                      class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                    />
                  </div>
                  <!-- Google Maps URL -->
                  <div v-if="shouldShowField('mapUrl')">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Google Maps URL <span class="text-gray-400 font-normal">(Optional)</span></label>
                    <input
                      v-model="profileData.mapUrl"
                      type="url"
                      placeholder="e.g., https://maps.google.com/..."
                      class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                    />
                  </div>
                </div>
              </div>

              <!-- Team Members Section (Special handling) -->
              <div v-if="shouldShowField('teamMembers')" class="bg-white border border-gray-200 rounded-xl p-4 space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                  <div class="flex items-center gap-2">
                    <Icon name="heroicons:user-group" class="w-5 h-5 text-cyan-600" />
                    <h3 class="text-sm font-semibold text-gray-800">Team Members</h3>
                  </div>
                  <button
                    type="button"
                    @click="loadBusinessTeamMembers"
                    :disabled="loadingTeamMembers"
                    class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors"
                  >
                    <Icon v-if="loadingTeamMembers" name="heroicons:arrow-path" class="w-4 h-4 animate-spin" />
                    <Icon v-else name="heroicons:users" class="w-4 h-4" />
                    {{ loadingTeamMembers ? 'Loading...' : 'Load Employees' }}
                  </button>
                </div>

                <!-- Available Employees -->
                <div v-if="businessEmployees.length > 0" class="space-y-2">
                  <p class="text-xs text-gray-500">Select team members to display on your landing page:</p>
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div
                      v-for="employee in businessEmployees"
                      :key="employee.user_id + '-' + employee.nfc_card_id"
                      @click="toggleTeamMember(employee)"
                      :class="[
                        'flex items-center gap-3 p-3 rounded-lg border-2 cursor-pointer transition-all',
                        isTeamMemberSelected(employee) 
                          ? 'border-blue-500 bg-blue-50' 
                          : 'border-gray-200 hover:border-gray-300 bg-white'
                      ]"
                    >
                      <div class="flex-shrink-0">
                        <img 
                          v-if="employee.profile_image" 
                          :src="employee.profile_image" 
                          :alt="employee.name"
                          class="w-10 h-10 rounded-full object-cover"
                        />
                        <div v-else class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white text-sm font-bold">
                          {{ employee.initials }}
                        </div>
                      </div>
                      <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ employee.name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ employee.role }}</p>
                      </div>
                      <div class="flex-shrink-0">
                        <Icon 
                          :name="isTeamMemberSelected(employee) ? 'heroicons:check-circle-solid' : 'heroicons:plus-circle'" 
                          :class="isTeamMemberSelected(employee) ? 'w-5 h-5 text-blue-500' : 'w-5 h-5 text-gray-300'"
                        />
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Selected Team Members Preview -->
                <div v-if="validTeamMembers.length > 0" class="space-y-2 pt-3 border-t border-gray-100">
                  <p class="text-xs font-medium text-gray-700">Selected ({{ validTeamMembers.length }}):</p>
                  <div class="flex flex-wrap gap-2">
                    <div
                      v-for="(member, idx) in validTeamMembers"
                      :key="idx"
                      class="flex items-center gap-2 px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs"
                    >
                      <span class="font-medium">{{ member.name }}</span>
                      <button @click="removeTeamMemberByName(member.name)" class="hover:text-red-600">
                        <Icon name="heroicons:x-mark" class="w-3 h-3" />
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Empty State -->
                <div v-else-if="businessEmployees.length === 0 && !loadingTeamMembers" class="text-center py-6 bg-gray-50 rounded-lg">
                  <Icon name="heroicons:user-group" class="w-10 h-10 mx-auto mb-2 text-gray-300" />
                  <p class="text-sm text-gray-500">Click "Load Employees" to fetch your team</p>
                </div>
              </div>
            </div>

            <!-- Services Tab - Dynamic Rendering -->
            <div v-if="activeTab === 'services'" class="space-y-4 sm:space-y-6">
              <!-- Dynamic Field Groups (from Backend) -->
              <div 
                v-for="group in getGroupedServicesFields()" 
                :key="group.name"
                class="bg-white border border-gray-200 rounded-xl p-4 space-y-4"
              >
                <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                  <Icon :name="group.icon" class="w-5 h-5 text-blue-600" />
                  <h3 class="text-sm font-semibold text-gray-800">{{ group.name }}</h3>
                </div>
                <div class="grid grid-cols-1 gap-4" :class="{ 'md:grid-cols-2': group.fields.length > 1 && !group.fields.some(f => f.field_type === 'richtext' || f.field_type === 'textarea' || f.field_type === 'repeater') }">
                  <DynamicFormField
                    v-for="field in group.fields"
                    :key="field.field_key"
                    :field="field"
                    v-model="profileData"
                    :user-plan="authStore.user?.subscription_plan || 'business'"
                  />
                </div>
              </div>

              <!-- Service Icon Picker & Form Fields Container -->
              <div class="bg-white border border-gray-200 rounded-xl p-4 space-y-5">
                <!-- Service Icon Picker -->
                <div>
                  <div class="flex items-center gap-2 pb-2 mb-3 border-b border-gray-100">
                    <Icon name="heroicons:face-smile" class="w-5 h-5 text-purple-600" />
                    <h3 class="text-sm font-semibold text-gray-800">Service Icon</h3>
                    <span class="text-xs text-gray-400">(Select icon for this service)</span>
                  </div>
                  <!-- Selected Icon Preview -->
                  <div class="mb-4 flex items-center gap-4">
                    <div class="flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-100 to-indigo-100 shadow-sm ring-1 ring-blue-200">
                      <span class="text-4xl">{{ profileData.serviceIcon || "🚀" }}</span>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-800 mb-0.5">Selected Icon</p>
                      <p class="text-xs text-gray-500">Click on an icon below to change</p>
                    </div>
                  </div>
                  <!-- Icon Grid Picker -->
                  <div class="grid grid-cols-8 gap-2 max-h-52 overflow-y-auto p-3 bg-gray-50 rounded-xl border border-gray-200">
                    <button
                      v-for="icon in SERVICE_ICONS"
                      :key="icon"
                      @click="profileData.serviceIcon = icon"
                      :class="[
                        'w-full aspect-square rounded-xl flex items-center justify-center text-2xl transition-all duration-150 hover:scale-110',
                        profileData.serviceIcon === icon
                          ? 'bg-blue-600 shadow-md ring-2 ring-blue-400'
                          : 'bg-white border border-gray-200 hover:bg-blue-50 hover:border-blue-300'
                      ]"
                      type="button"
                    >
                      {{ icon }}
                    </button>
                  </div>
                  <!-- Custom Emoji Input -->
                  <div class="mt-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Atau masukkan custom emoji sendiri:</label>
                    <div class="flex gap-2">
                      <input
                        v-model="profileData.serviceIcon"
                        type="text"
                        maxlength="4"
                        placeholder="Tampal emoji di sini..."
                        class="flex-1 px-3 py-2 text-lg border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                      />
                      <button
                        @click="profileData.serviceIcon = '🚀'"
                        type="button"
                        class="px-3 py-2 text-xs text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                      >
                        Reset
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Add/Update Service Button -->
              <div class="flex justify-end gap-2">
                <button 
                  type="button"
                  @click="clearServiceForm"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                >
                  <Icon name="heroicons:x-mark" class="w-4 h-4 inline mr-1" />
                  {{ editingServiceIndex !== null ? 'Cancel Edit' : 'Clear Form' }}
                </button>
                <button 
                  type="button"
                  @click="addService"
                  class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                  :class="editingServiceIndex !== null ? 'bg-blue-600 hover:bg-blue-700' : 'bg-green-600 hover:bg-green-700'"
                >
                  <Icon :name="editingServiceIndex !== null ? 'heroicons:check' : 'heroicons:plus'" class="w-4 h-4 inline mr-1" />
                  {{ editingServiceIndex !== null ? 'Update Service' : 'Add Service' }}
                </button>
              </div>

              <!-- Services List -->
              <div v-if="profileData.services && profileData.services.length > 0" class="bg-white border border-gray-200 rounded-xl p-4">
                <div class="flex items-center gap-2 pb-3 border-b border-gray-100 mb-4">
                  <Icon name="heroicons:briefcase" class="w-5 h-5 text-green-600" />
                  <h3 class="text-sm font-semibold text-gray-800">Services ({{ profileData.services.length }})</h3>
                </div>
                <div class="space-y-3">
                  <div 
                    v-for="(service, index) in profileData.services" 
                    :key="service.id || index"
                    class="flex items-center justify-between p-3 rounded-lg transition-colors"
                    :class="editingServiceIndex === index ? 'bg-green-100 ring-2 ring-green-500' : 'bg-gray-50 hover:bg-gray-100'"
                  >
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center gap-2">
                        <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 flex items-center justify-center text-xl ring-1 ring-blue-100">
                          {{ service.icon || "🚀" }}
                        </div>
                        <div class="min-w-0">
                          <h4 class="text-sm font-medium text-gray-900 truncate">{{ service.name }}</h4>
                          <span v-if="editingServiceIndex === index" class="text-xs bg-green-600 text-white px-2 py-0.5 rounded">Editing</span>
                        </div>
                      </div>
                      <p class="text-xs text-gray-500 mt-0.5">
                        <span v-if="service.category" class="mr-2">{{ service.category }}</span>
                        <span v-if="service.price" class="text-green-600 font-medium">{{ service.price }}</span>
                        <span v-if="service.duration" class="ml-2">• {{ service.duration }}</span>
                      </p>
                    </div>
                    <div class="flex items-center gap-2 ml-4">
                      <button 
                        type="button"
                        @click="editService(index)"
                        class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors"
                        :title="editingServiceIndex === index ? 'Currently editing' : 'Edit service'"
                      >
                        <Icon name="heroicons:pencil" class="w-4 h-4" />
                      </button>
                      <button 
                        type="button"
                        @click="removeService(index)"
                        class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                        title="Remove service"
                      >
                        <Icon name="heroicons:trash" class="w-4 h-4" />
                      </button>
                    </div>
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
                  <h3 class="text-base sm:text-lg font-medium text-secondary-900 mb-2">No Links Yet</h3>
                  <p class="text-sm text-secondary-600 mb-6">Add your social media and custom links. They'll be saved when you save your profile.</p>
                  <button @click="showAddLinkModal = true" class="btn btn-primary">
                    <Icon name="heroicons:plus" class="h-5 w-5 mr-2" />
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
                  <button @click="showAddLinkModal = true; insertIndex = null" class="w-full mt-4 py-3 border-2 border-dashed border-secondary-300 rounded-lg text-secondary-600 hover:border-primary-500 hover:text-primary-600 transition-colors flex items-center justify-center">
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

            <!-- Portfolio Tab - Dynamic Rendering -->
            <div v-if="activeTab === 'portfolio'" class="space-y-4 sm:space-y-6">
              <!-- Portfolio Cover Image -->
              <ProfileImageUpload
                v-if="shouldShowField('portfolioCoverImage')"
                v-model="profileData.portfolioCoverImage"
                upload-endpoint="/upload/portfolio-image"
                delete-endpoint="/upload/portfolio-image"
                :label="getFieldConfig('portfolioCoverImage')?.label || 'Cover Image'"
                :help-text="getFieldConfig('portfolioCoverImage')?.help_text || 'Portfolio project cover image'"
                :nfc-card-id="selectedNfcCardId"
              />

              <!-- Dynamic Field Groups (from Backend) -->
              <div 
                v-for="group in getGroupedPortfolioFields()" 
                :key="group.name"
                class="bg-white border border-gray-200 rounded-xl p-4 space-y-4"
              >
                <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                  <Icon :name="group.icon" class="w-5 h-5 text-blue-600" />
                  <h3 class="text-sm font-semibold text-gray-800">{{ group.name }}</h3>
                </div>
                <div class="grid grid-cols-1 gap-4" :class="{ 'md:grid-cols-2': group.fields.length > 1 && !group.fields.some(f => f.field_type === 'richtext' || f.field_type === 'textarea' || f.field_type === 'repeater') }">
                  <DynamicFormField
                    v-for="field in group.fields"
                    :key="field.field_key"
                    :field="field"
                    v-model="profileData"
                    :user-plan="authStore.user?.subscription_plan || 'business'"
                  />
                </div>
              </div>

              <!-- Add/Update Project Button -->
              <div class="flex justify-end gap-2">
                <button 
                  type="button"
                  @click="clearProjectForm"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                >
                  <Icon name="heroicons:x-mark" class="w-4 h-4 inline mr-1" />
                  {{ editingProjectIndex !== null ? 'Cancel Edit' : 'Clear Form' }}
                </button>
                <button 
                  type="button"
                  @click="addProject"
                  class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                  :class="editingProjectIndex !== null ? 'bg-blue-600 hover:bg-blue-700' : 'bg-purple-600 hover:bg-purple-700'"
                >
                  <Icon :name="editingProjectIndex !== null ? 'heroicons:check' : 'heroicons:plus'" class="w-4 h-4 inline mr-1" />
                  {{ editingProjectIndex !== null ? 'Update Project' : 'Add Project' }}
                </button>
              </div>

              <!-- Projects List -->
              <div v-if="profileData.projects && profileData.projects.length > 0" class="bg-white border border-gray-200 rounded-xl p-4">
                <div class="flex items-center gap-2 pb-3 border-b border-gray-100 mb-4">
                  <Icon name="heroicons:folder" class="w-5 h-5 text-purple-600" />
                  <h3 class="text-sm font-semibold text-gray-800">Projects ({{ profileData.projects.length }})</h3>
                </div>
                <div class="space-y-3">
                  <div 
                    v-for="(project, index) in profileData.projects" 
                    :key="project.id || index"
                    class="flex items-center justify-between p-3 rounded-lg transition-colors"
                    :class="editingProjectIndex === index ? 'bg-purple-100 ring-2 ring-purple-500' : 'bg-gray-50 hover:bg-gray-100'"
                  >
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center gap-2">
                        <h4 class="text-sm font-medium text-gray-900 truncate">{{ project.title }}</h4>
                        <span v-if="editingProjectIndex === index" class="text-xs bg-purple-600 text-white px-2 py-0.5 rounded">Editing</span>
                      </div>
                      <p class="text-xs text-gray-500 mt-0.5">
                        <span v-if="project.category" class="mr-2">{{ project.category }}</span>
                        <span v-if="project.client_name">for {{ project.client_name }}</span>
                        <span v-if="project.date_completed" class="ml-2">• {{ project.date_completed }}</span>
                      </p>
                    </div>
                    <div class="flex items-center gap-2 ml-4">
                      <button 
                        type="button"
                        @click="editProject(index)"
                        class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors"
                        :title="editingProjectIndex === index ? 'Currently editing' : 'Edit project'"
                      >
                        <Icon name="heroicons:pencil" class="w-4 h-4" />
                      </button>
                      <button 
                        type="button"
                        @click="removeProject(index)"
                        class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                        title="Remove project"
                      >
                        <Icon name="heroicons:trash" class="w-4 h-4" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Empty State -->
              <div v-if="portfolioFields.length === 0 && (!profileData.projects || profileData.projects.length === 0)" class="text-center py-8 bg-gray-50 rounded-lg">
                <Icon name="heroicons:folder" class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                <h4 class="text-sm font-medium text-gray-900 mb-1">No Projects Yet</h4>
                <p class="text-xs text-gray-500">Fill in the form above and click "Add Project" to showcase your work</p>
              </div>
            </div>

            <!-- Blog Tab - Dynamic Rendering -->
            <div v-if="activeTab === 'blog'" class="space-y-4 sm:space-y-6">
              <!-- Dynamic Field Groups (from Backend) -->
              <div 
                v-for="group in getGroupedBlogFields()" 
                :key="group.name"
                class="bg-white border border-gray-200 rounded-xl p-4 space-y-4"
              >
                <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                  <Icon :name="group.icon" class="w-5 h-5 text-blue-600" />
                  <h3 class="text-sm font-semibold text-gray-800">{{ group.name }}</h3>
                </div>
                <div class="grid grid-cols-1 gap-4" :class="{ 'md:grid-cols-2': group.fields.length > 1 && !group.fields.some(f => f.field_type === 'richtext' || f.field_type === 'textarea' || f.field_type === 'repeater') }">
                  <DynamicFormField
                    v-for="field in group.fields"
                    :key="field.field_key"
                    :field="field"
                    v-model="profileData"
                    :user-plan="authStore.user?.subscription_plan || 'business'"
                  />
                </div>
              </div>

              <!-- Add/Update Blog Post Button -->
              <div class="flex justify-end gap-2">
                <button 
                  type="button"
                  @click="clearBlogForm"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                >
                  <Icon name="heroicons:x-mark" class="w-4 h-4 inline mr-1" />
                  {{ editingBlogPostIndex !== null ? 'Cancel Edit' : 'Clear Form' }}
                </button>
                <button 
                  type="button"
                  @click="addBlogPost"
                  class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                  :class="editingBlogPostIndex !== null ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700'"
                >
                  <Icon :name="editingBlogPostIndex !== null ? 'heroicons:check' : 'heroicons:plus'" class="w-4 h-4 inline mr-1" />
                  {{ editingBlogPostIndex !== null ? 'Update Blog Post' : 'Add Blog Post' }}
                </button>
              </div>

              <!-- Blog Posts List -->
              <div v-if="profileData.blogPosts && profileData.blogPosts.length > 0" class="bg-white border border-gray-200 rounded-xl p-4">
                <div class="flex items-center gap-2 pb-3 border-b border-gray-100 mb-4">
                  <Icon name="heroicons:document-text" class="w-5 h-5 text-blue-600" />
                  <h3 class="text-sm font-semibold text-gray-800">Blog Posts ({{ profileData.blogPosts.length }})</h3>
                </div>
                <div class="space-y-3">
                  <div 
                    v-for="(post, index) in profileData.blogPosts" 
                    :key="post.id || index"
                    class="flex items-center justify-between p-3 rounded-lg transition-colors"
                    :class="editingBlogPostIndex === index ? 'bg-blue-100 ring-2 ring-blue-500' : 'bg-gray-50 hover:bg-gray-100'"
                  >
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center gap-2">
                        <h4 class="text-sm font-medium text-gray-900 truncate">{{ post.title }}</h4>
                        <span v-if="editingBlogPostIndex === index" class="text-xs bg-blue-600 text-white px-2 py-0.5 rounded">Editing</span>
                      </div>
                      <p class="text-xs text-gray-500 mt-0.5">
                        <span v-if="post.category" class="mr-2">{{ post.category }}</span>
                        <span v-if="post.author_name">by {{ post.author_name }}</span>
                        <span v-if="post.published_date" class="ml-2">• {{ post.published_date }}</span>
                      </p>
                    </div>
                    <div class="flex items-center gap-2 ml-4">
                      <button 
                        type="button"
                        @click="editBlogPost(index)"
                        class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors"
                        :title="editingBlogPostIndex === index ? 'Currently editing' : 'Edit post'"
                      >
                        <Icon name="heroicons:pencil" class="w-4 h-4" />
                      </button>
                      <button 
                        type="button"
                        @click="removeBlogPost(index)"
                        class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition-colors"
                        title="Remove post"
                      >
                        <Icon name="heroicons:trash" class="w-4 h-4" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Empty State -->
              <div v-if="blogFields.length === 0 && (!profileData.blogPosts || profileData.blogPosts.length === 0)" class="text-center py-8 bg-gray-50 rounded-lg">
                <Icon name="heroicons:document-text" class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                <h4 class="text-sm font-medium text-gray-900 mb-1">No Blog Posts Yet</h4>
                <p class="text-xs text-gray-500">Fill in the form above and click "Add Blog Post" to create your first post</p>
              </div>
            </div>

            <!-- Dynamic Sections (Admin-created) -->
            <div 
              v-if="isDynamicSection(activeTab)" 
              class="space-y-4 sm:space-y-6"
            >
              <!-- Dynamic Field Groups -->
              <div 
                v-for="group in getDynamicSectionGroups(activeTab)" 
                :key="group.name"
                class="bg-white border border-gray-200 rounded-xl p-4 space-y-4"
              >
                <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                  <Icon :name="group.icon" class="w-5 h-5 text-blue-600" />
                  <h3 class="text-sm font-semibold text-gray-800">{{ group.name }}</h3>
                </div>
                <div class="grid grid-cols-1 gap-4" :class="{ 'md:grid-cols-2': group.fields.length > 1 && !group.fields.some(f => f.field_type === 'richtext' || f.field_type === 'textarea' || f.field_type === 'repeater') }">
                  <DynamicFormField
                    v-for="field in group.fields"
                    :key="field.field_key"
                    :field="field"
                    v-model="profileData"
                    :user-plan="authStore.user?.subscription_plan || 'business'"
                  />
                </div>
              </div>

              <!-- Empty State -->
              <div v-if="getDynamicSectionFields(activeTab).length === 0" class="text-center py-8 bg-gray-50 rounded-lg">
                <Icon name="heroicons:squares-plus" class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                <h4 class="text-sm font-medium text-gray-900 mb-1">No Fields Yet</h4>
                <p class="text-xs text-gray-500">Admin hasn't configured any fields for this section yet.</p>
              </div>
            </div>

            <!-- Design Tab -->
            <div v-if="activeTab === 'design'" class="space-y-6">
              <!-- Theme Selection -->
              <div v-if="themes.length > 0">
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-2">
                    <Icon name="heroicons:sparkles" class="h-5 w-5 text-purple-600" />
                    <h3 class="text-sm font-semibold text-secondary-900">Theme Presets</h3>
                  </div>
                  <span class="text-xs text-secondary-500 bg-secondary-100 px-2.5 py-1 rounded-full font-medium">{{ themes.length }} available</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3.5">
                  <div
                    v-for="theme in themes"
                    :key="theme.id"
                    @click="applyTheme(theme)"
                    :class="[
                      'relative cursor-pointer rounded-2xl border-2 p-3 transition-all hover:shadow-lg hover:-translate-y-0.5',
                      profileData.theme === theme.id
                        ? 'border-primary-500 bg-primary-50/60 shadow-md'
                        : 'border-secondary-200 hover:border-secondary-300 bg-white',
                    ]"
                  >
                    <div
                      class="w-full h-20 rounded-xl mb-2.5 shadow-inner ring-1 ring-black/5 overflow-hidden"
                      :style="theme.previewCss || 'background: #FFFFFF;'"
                    ></div>
                    <p class="text-xs text-center font-semibold text-secondary-700">
                      {{ theme.name }}
                    </p>
                    <Icon
                      v-if="profileData.theme === theme.id"
                      name="heroicons:check-circle-solid"
                      class="absolute top-2 right-2 w-5 h-5 text-primary-500 drop-shadow"
                    />
                  </div>
                </div>
              </div>

              <!-- Background Animations (screensaver effects) -->
              <div v-if="bgAnimations.length > 0" class="border-t border-secondary-200 pt-6">
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-2">
                    <Icon name="heroicons:sparkles" class="h-5 w-5 text-fuchsia-600" />
                    <h3 class="text-sm font-semibold text-secondary-900">Background Animation</h3>
                  </div>
                  <span class="text-xs text-secondary-500 bg-secondary-100 px-2.5 py-1 rounded-full font-medium">{{ bgAnimations.length }} effects</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                  <div
                    v-for="anim in bgAnimations"
                    :key="anim.id"
                    @click="profileData.bgAnimation = anim.id"
                    :class="[
                      'relative cursor-pointer rounded-2xl border-2 p-3 transition-all hover:shadow-md',
                      profileData.bgAnimation === anim.id
                        ? 'border-fuchsia-500 bg-fuchsia-50/60 shadow-md'
                        : 'border-secondary-200 hover:border-secondary-300 bg-white',
                    ]"
                  >
                    <div
                      class="w-full h-16 rounded-xl mb-2 ring-1 ring-black/5 overflow-hidden"
                      :style="anim.previewCss + 'background-color: #312e81;'"
                    ></div>
                    <p class="text-[11px] text-center font-semibold text-secondary-700 leading-tight">
                      {{ anim.name }}
                    </p>
                    <Icon
                      v-if="profileData.bgAnimation === anim.id"
                      name="heroicons:check-circle-solid"
                      class="absolute top-2 right-2 w-4.5 h-4.5 text-fuchsia-500"
                    />
                  </div>
                </div>
              </div>

              <!-- Card / Section Container Style -->
              <div v-if="cardStyles.length > 0" class="border-t border-secondary-200 pt-6">
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-2">
                    <Icon name="heroicons:rectangle-stack" class="h-5 w-5 text-teal-600" />
                    <h3 class="text-sm font-semibold text-secondary-900">Card Style</h3>
                  </div>
                  <span class="text-xs text-secondary-500 bg-secondary-100 px-2.5 py-1 rounded-full font-medium">{{ cardStyles.length }} styles</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3.5">
                  <div
                    v-for="cs in cardStyles"
                    :key="cs.id"
                    @click="profileData.cardStyle = cs.id"
                    :class="[
                      'relative cursor-pointer rounded-2xl border-2 p-3.5 transition-all hover:shadow-md',
                      profileData.cardStyle === cs.id
                        ? 'border-teal-500 bg-teal-50/60 shadow-md'
                        : 'border-secondary-200 hover:border-secondary-300 bg-white',
                    ]"
                  >
                    <div
                      class="w-full h-20 rounded-xl mb-2.5 ring-1 ring-black/5 overflow-hidden flex items-center justify-center"
                      style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%);"
                    >
                      <div class="w-[70%] h-[70%]" :style="cs.previewCss">
                        <div class="w-full h-full flex items-center justify-center">
                          <div class="w-4/5 h-3 bg-white/30 rounded-full mb-2"></div>
                        </div>
                      </div>
                    </div>
                    <p class="text-xs text-center font-semibold text-secondary-700">
                      {{ cs.name }}
                    </p>
                    <Icon
                      v-if="profileData.cardStyle === cs.id"
                      name="heroicons:check-circle-solid"
                      class="absolute top-2 right-2 w-4.5 h-4.5 text-teal-500"
                    />
                  </div>
                </div>
              </div>

              <!-- Color Settings -->
              <div class="border-t border-secondary-200 pt-6">
                <div class="flex items-center gap-2 mb-4">
                  <Icon name="heroicons:swatch" class="h-5 w-5 text-amber-600" />
                  <h3 class="text-sm font-medium text-secondary-900">Colors</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <!-- Background Color -->
                  <div class="bg-secondary-50 rounded-xl p-4">
                    <label class="block text-xs font-medium text-secondary-600 mb-3">
                      Background Color
                    </label>
                    <div class="flex items-center gap-3">
                      <input
                        type="color"
                        v-model="profileData.backgroundColor"
                        class="w-12 h-12 rounded-lg border-2 border-secondary-300 cursor-pointer"
                      />
                      <input
                        type="text"
                        v-model="profileData.backgroundColor"
                        placeholder="#FFFFFF"
                        class="flex-1 input font-mono text-sm"
                      />
                    </div>
                  </div>

                  <!-- Text Color -->
                  <div class="bg-secondary-50 rounded-xl p-4">
                    <label class="block text-xs font-medium text-secondary-600 mb-3">
                      Text Color
                    </label>
                    <div class="flex items-center gap-3">
                      <input
                        type="color"
                        v-model="profileData.textColor"
                        class="w-12 h-12 rounded-lg border-2 border-secondary-300 cursor-pointer"
                      />
                      <input
                        type="text"
                        v-model="profileData.textColor"
                        placeholder="#000000"
                        class="flex-1 input font-mono text-sm"
                      />
                    </div>
                  </div>
                </div>

                <!-- Quick Color Presets -->
                <div class="mt-4">
                  <label class="block text-xs font-medium text-secondary-600 mb-2">Quick Presets</label>
                  <div class="flex flex-wrap gap-2">
                    <button
                      v-for="preset in colorPresets"
                      :key="preset.name"
                      @click="applyColorPreset(preset)"
                      class="flex items-center gap-2 px-3 py-2 rounded-lg border border-secondary-200 hover:border-primary-400 hover:bg-primary-50 transition-colors"
                      :title="preset.name"
                    >
                      <div class="flex gap-1">
                        <div :style="{ backgroundColor: preset.bg }" class="w-4 h-4 rounded-full border border-secondary-300"></div>
                        <div :style="{ backgroundColor: preset.text }" class="w-4 h-4 rounded-full border border-secondary-300"></div>
                      </div>
                      <span class="text-xs font-medium text-secondary-600">{{ preset.name }}</span>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Color Schemes (if available) -->
              <div v-if="colorSchemes.length > 0" class="border-t border-secondary-200 pt-6">
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-2">
                    <Icon name="heroicons:paint-brush" class="h-5 w-5 text-pink-600" />
                    <h3 class="text-sm font-medium text-secondary-900">Color Schemes</h3>
                  </div>
                  <span class="text-xs text-secondary-500 bg-secondary-100 px-2 py-1 rounded-full">{{ colorSchemes.length }} schemes</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                  <div
                    v-for="scheme in colorSchemes"
                    :key="scheme.id"
                    @click="applyColorScheme(scheme)"
                    :class="[
                      'relative cursor-pointer rounded-xl border-2 p-3 transition-all hover:shadow-md',
                      profileData.colorScheme === scheme.id
                        ? 'border-primary-500 bg-primary-50'
                        : 'border-secondary-200 hover:border-secondary-300',
                    ]"
                  >
                    <div class="flex gap-1 mb-2">
                      <div v-for="(color, idx) in Object.values(scheme.colors).slice(0, 4)" :key="idx" 
                        :style="{ backgroundColor: color }" 
                        class="w-6 h-6 rounded-full border border-secondary-300"
                      ></div>
                    </div>
                    <p class="text-xs font-medium text-secondary-700">{{ scheme.name }}</p>
                    <Icon
                      v-if="profileData.colorScheme === scheme.id"
                      name="heroicons:check-circle-solid"
                      class="absolute top-2 right-2 w-5 h-5 text-primary-500"
                    />
                  </div>
                </div>
              </div>

              <!-- Layout Options (if available) -->
              <div v-if="layouts.length > 0" class="border-t border-secondary-200 pt-6">
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-2">
                    <Icon name="heroicons:squares-2x2" class="h-5 w-5 text-cyan-600" />
                    <h3 class="text-sm font-medium text-secondary-900">Layout</h3>
                  </div>
                  <span class="text-xs text-secondary-500 bg-secondary-100 px-2 py-1 rounded-full">{{ layouts.length }} layouts</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                  <div
                    v-for="layout in layouts"
                    :key="layout.id"
                    @click="profileData.layout = layout.id"
                    :class="[
                      'relative cursor-pointer rounded-xl border-2 p-4 transition-all hover:shadow-md',
                      profileData.layout === layout.id
                        ? 'border-primary-500 bg-primary-50'
                        : 'border-secondary-200 hover:border-secondary-300',
                    ]"
                  >
                    <div class="flex flex-col items-center">
                      <Icon :name="layout.icon || 'heroicons:squares-2x2'" class="w-8 h-8 mb-2 text-secondary-600" />
                      <p class="text-xs font-medium text-secondary-700">{{ layout.name }}</p>
                    </div>
                    <Icon
                      v-if="profileData.layout === layout.id"
                      name="heroicons:check-circle-solid"
                      class="absolute top-2 right-2 w-5 h-5 text-primary-500"
                    />
                  </div>
                </div>
              </div>

              <!-- Empty State -->
              <div v-if="themes.length === 0 && fonts.length === 0 && buttonStyles.length === 0" class="text-center py-12 bg-secondary-50 rounded-xl">
                <Icon name="heroicons:paint-brush" class="w-12 h-12 mx-auto mb-3 text-secondary-300" />
                <h4 class="text-sm font-medium text-secondary-900 mb-1">No Design Options Available</h4>
                <p class="text-xs text-secondary-500">Design options will be configured by admin</p>
              </div>
            </div>

            <!-- Style Tab -->
            <div v-if="activeTab === 'style'" class="space-y-6">
              <!-- Typography -->
              <div>
                <div class="flex items-center gap-2 mb-4">
                  <Icon name="heroicons:language" class="h-5 w-5 text-secondary-600" />
                  <h3 class="text-sm font-medium text-secondary-900">Typography</h3>
                </div>

                <!-- Font Selection Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div
                    v-for="font in fonts"
                    :key="font.id"
                    @click="profileData.font = font.id"
                    :class="[
                      'relative cursor-pointer rounded-xl border-2 p-4 transition-all hover:shadow-md',
                      profileData.font === font.id
                        ? 'border-primary-500 bg-primary-50'
                        : 'border-secondary-200 hover:border-secondary-300',
                    ]"
                  >
                    <p :style="{ fontFamily: font.family || font.name }" class="text-lg font-medium text-secondary-900 mb-1">
                      {{ font.name }}
                    </p>
                    <p :style="{ fontFamily: font.family || font.name }" class="text-xs text-secondary-500">
                      The quick brown fox jumps over the lazy dog
                    </p>
                    <Icon
                      v-if="profileData.font === font.id"
                      name="heroicons:check-circle-solid"
                      class="absolute top-2 right-2 w-5 h-5 text-primary-500"
                    />
                  </div>
                </div>
              </div>

              <!-- Button Style -->
              <div class="border-t border-secondary-200 pt-6">
                <div class="flex items-center gap-2 mb-4">
                  <Icon name="heroicons:cursor-arrow-rays" class="h-5 w-5 text-secondary-600" />
                  <h3 class="text-sm font-medium text-secondary-900">Button Style</h3>
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div
                    v-for="style in buttonStyles"
                    :key="style.id"
                    @click="profileData.buttonStyle = style.id"
                    :class="[
                      'relative p-4 rounded-xl border-2 cursor-pointer transition-all hover:shadow-md',
                      profileData.buttonStyle === style.id
                        ? 'border-primary-500 bg-primary-50'
                        : 'border-secondary-200 hover:border-secondary-300',
                    ]"
                  >
                    <div class="mb-3">
                      <button
                        :class="['w-full py-2 px-4 text-sm', style.class]"
                        disabled
                      >
                        Sample Button
                      </button>
                    </div>
                    <p class="text-xs text-center font-medium text-secondary-700">{{ style.name }}</p>
                    <Icon
                      v-if="profileData.buttonStyle === style.id"
                      name="heroicons:check-circle-solid"
                      class="absolute top-2 right-2 w-5 h-5 text-primary-500"
                    />
                  </div>
                </div>
              </div>

              <!-- Additional Style Options -->
              <div class="border-t border-secondary-200 pt-6">
                <div class="flex items-center gap-2 mb-4">
                  <Icon name="heroicons:adjustments-horizontal" class="h-5 w-5 text-secondary-600" />
                  <h3 class="text-sm font-medium text-secondary-900">Additional Options</h3>
                </div>
                <div class="space-y-3">
                  <!-- Show Watermark Toggle -->
                  <div class="flex items-center justify-between bg-secondary-50 rounded-xl p-4">
                    <div>
                      <h4 class="text-sm font-medium text-secondary-900">Show Watermark</h4>
                      <p class="text-xs text-secondary-500 mt-0.5">Display company logo as background watermark</p>
                    </div>
                    <button
                      @click="profileData.showWatermark = !profileData.showWatermark"
                      :class="[
                        'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200',
                        profileData.showWatermark ? 'bg-primary-600' : 'bg-secondary-200',
                      ]"
                    >
                      <span
                        :class="[
                          'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200',
                          profileData.showWatermark ? 'translate-x-5' : 'translate-x-0',
                        ]"
                      />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Layout Designer Tab -->
            <div v-if="activeTab === 'layout'" class="space-y-6">
              <LayoutDesigner
                :sections="layoutDesignerSections"
                :section-layout="profileData.sectionLayout"
                :field-layout="profileData.fieldLayout"
                :can-customize-layout="canCustomizeLayout"
                @update:section-layout="updateSectionLayout"
                @update:field-layout="updateFieldLayout"
              />
            </div>

            <!-- Watermarks/Features Tab -->
            <div v-if="activeTab === 'watermarks' || activeTab === 'features'" class="space-y-6">
              <div>
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-2">
                    <Icon name="heroicons:sparkles" class="h-5 w-5 text-amber-600" />
                    <h3 class="text-sm font-medium text-secondary-900">Feature Settings</h3>
                  </div>
                  <span class="text-xs text-secondary-500 bg-secondary-100 px-2 py-1 rounded">
                    {{ sortedFeatureToggles.filter(f => getFeatureValue(f.feature_key)).length }} / {{ sortedFeatureToggles.length }} enabled
                  </span>
                </div>
                <p class="text-xs text-secondary-500 mb-4">Enable features and drag to reorder their position on your landing page</p>

                <!-- Feature Toggles List (Draggable) -->
                <div v-if="sortedFeatureToggles.length > 0" class="space-y-2">
                  <div
                    v-for="(feature, index) in sortedFeatureToggles"
                    :key="feature.id"
                    draggable="true"
                    @dragstart="handleFeatureDragStart($event, index)"
                    @dragover.prevent="handleFeatureDragOver($event, index)"
                    @dragend="handleFeatureDragEnd"
                    @drop="handleFeatureDrop($event, index)"
                    :class="[
                      'rounded-xl border-2 p-4 transition-all cursor-move',
                      featureDragIndex === index ? 'opacity-50 scale-95' : '',
                      featureDragOverIndex === index && featureDragOverIndex !== featureDragIndex ? 'border-primary-400 border-dashed' : '',
                      getFeatureValue(feature.feature_key)
                        ? 'border-primary-200 bg-primary-50 hover:border-primary-300'
                        : 'border-secondary-200 bg-secondary-50 hover:border-secondary-300',
                    ]"
                  >
                    <div class="flex items-center gap-3">
                      <!-- Drag Handle & Order Number -->
                      <div class="flex items-center gap-2">
                        <Icon name="heroicons:bars-3" class="h-4 w-4 text-secondary-400 cursor-grab active:cursor-grabbing" />
                        <span class="w-6 h-6 flex items-center justify-center text-xs font-bold text-secondary-500 bg-white rounded-full border border-secondary-200">
                          {{ index + 1 }}
                        </span>
                      </div>
                      
                      <!-- Feature Icon -->
                      <div :class="[
                        'w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0',
                        getFeatureValue(feature.feature_key) ? 'bg-primary-100' : 'bg-secondary-100'
                      ]">
                        <Icon 
                          :name="getFeatureIcon(feature.feature_key)" 
                          :class="[
                            'h-5 w-5',
                            getFeatureValue(feature.feature_key) ? 'text-primary-600' : 'text-secondary-400'
                          ]" 
                        />
                      </div>
                      
                      <!-- Feature Info -->
                      <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium text-secondary-900 truncate">
                          {{ feature.name }}
                        </h4>
                        <p v-if="feature.description" class="text-xs text-secondary-500 truncate">
                          {{ feature.description }}
                        </p>
                      </div>
                      
                      <!-- Toggle Switch -->
                      <button
                        @click.stop="toggleFeature(feature.feature_key)"
                        :class="[
                          'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200',
                          getFeatureValue(feature.feature_key) ? 'bg-primary-600' : 'bg-secondary-300',
                        ]"
                      >
                        <span
                          :class="[
                            'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200',
                            getFeatureValue(feature.feature_key) ? 'translate-x-5' : 'translate-x-0',
                          ]"
                        />
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Empty state -->
                <div v-else class="text-center py-12 bg-secondary-50 rounded-xl">
                  <Icon name="heroicons:sparkles" class="w-12 h-12 mx-auto mb-3 text-secondary-300" />
                  <h4 class="text-sm font-medium text-secondary-900 mb-1">No Features Available</h4>
                  <p class="text-xs text-secondary-500">Features will be configured by admin</p>
                  <p class="text-xs text-blue-600 mt-3">
                    <Icon name="heroicons:information-circle" class="inline w-4 h-4 mr-1" />
                    Quick Actions section will be hidden on landing page until features are assigned
                  </p>
                </div>
                
                <!-- Order Info -->
                <div v-if="sortedFeatureToggles.length > 0" class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                  <div class="flex items-start gap-2">
                    <Icon name="heroicons:information-circle" class="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" />
                    <p class="text-xs text-blue-700">
                      Drag features to reorder. Enabled features will appear on your landing page in this order.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Panel - Embedded Landing Page Preview -->
      <div class="md:col-span-3 md:sticky md:top-20 h-fit">
          <div class="card">
            <div class="card-body">
              <!-- Preview Header -->
              <div class="flex items-center justify-between mb-4">
                <div>
                  <h3 class="text-base font-semibold text-blue-800">Live Preview</h3>
                  <p class="text-xs text-blue-600 mt-0.5">Real-time preview</p>
                </div>
                <div class="flex gap-2">
                  <button
                    v-if="selectedNfcCardId"
                    @click="forceRefreshPreview"
                    class="btn btn-sm btn-outline"
                  >
                    <Icon name="heroicons:arrow-path" class="h-4 w-4" />
                  </button>
                  <button
                    v-if="selectedNfcCardId"
                    @click="openLandingPage"
                    class="btn btn-sm btn-primary"
                  >
                    <Icon name="heroicons:arrow-top-right-on-square" class="h-4 w-4 mr-1" />
                    <span class="hidden sm:inline">Open</span>
                  </button>
                </div>
              </div>
              
              <!-- iPhone 15 Mockup - Real Proportions (9:19.5 aspect ratio) -->
              <div class="w-full max-w-[360px] mx-auto">
                <div class="relative aspect-[9/19.5] w-full rounded-[54px] bg-neutral-900 shadow-2xl p-[10px] ring-[2px] ring-neutral-700">
                  <!-- Outer Frame Gradient for Titanium Effect -->
                  <div class="absolute inset-0 rounded-[54px] bg-gradient-to-b from-neutral-700 via-neutral-800 to-neutral-900 pointer-events-none"></div>
                  <div class="absolute inset-[2px] rounded-[52px] bg-gradient-to-br from-neutral-600/40 to-neutral-900/80 pointer-events-none"></div>

                  <!-- Left Side Buttons -->
                  <div class="absolute left-[-2px] top-[15%] w-[3px] h-8 bg-gradient-to-r from-neutral-600 to-neutral-500 rounded-l-sm"></div>
                  <div class="absolute left-[-2px] top-[23%] w-[3px] h-14 bg-gradient-to-r from-neutral-600 to-neutral-500 rounded-l-sm"></div>
                  <div class="absolute left-[-2px] top-[34%] w-[3px] h-14 bg-gradient-to-r from-neutral-600 to-neutral-500 rounded-l-sm"></div>
                  
                  <!-- Right Side Action Button -->
                  <div class="absolute right-[-2px] top-[20%] w-[3px] h-16 bg-gradient-to-l from-neutral-600 to-neutral-500 rounded-r-sm"></div>

                  <!-- Inner Screen Container -->
                  <div class="relative w-full h-full rounded-[44px] bg-black overflow-hidden shadow-inner">
                    
                    <!-- Dynamic Island -->
                    <div class="absolute top-2 left-1/2 -translate-x-1/2 w-[110px] h-[32px] bg-black rounded-full z-30 flex items-center justify-center">
                      <div class="w-[48px] h-[6px] bg-neutral-800 rounded-full opacity-70"></div>
                    </div>

                    <!-- Status Bar (Real iPhone Style) -->
                    <div class="absolute top-0 left-0 right-0 h-11 flex items-center justify-between px-7 z-20 text-white text-[14px] font-semibold tracking-tight">
                      <span class="pl-1">9:41</span>
                      <div class="flex items-center gap-1.5 pr-1">
                        <!-- Signal -->
                        <div class="flex items-end gap-0.5 h-3.5">
                          <div class="w-0.5 h-1 bg-white rounded-sm"></div>
                          <div class="w-0.5 h-1.5 bg-white rounded-sm"></div>
                          <div class="w-0.5 h-2 bg-white rounded-sm"></div>
                          <div class="w-0.5 h-3 bg-white rounded-sm"></div>
                        </div>
                        <!-- WiFi -->
                        <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="currentColor">
                          <path d="M12 3C7.8 3 3.9 4.6 1 7.2L12 21L23 7.2C20.1 4.6 16.2 3 12 3Z" opacity="1"/>
                          <path d="M12 7C9 7 6.2 8.1 4.1 10.1L12 18.5L19.9 10.1C17.8 8.1 15 7 12 7Z" opacity="0.7"/>
                        </svg>
                        <!-- Battery -->
                        <div class="w-6 h-3 border border-white/60 rounded-[3px] p-[1px] relative">
                          <div class="w-[85%] h-full bg-white rounded-[1.5px]"></div>
                          <div class="absolute -right-0.5 top-1/2 -translate-y-1/2 w-0.5 h-1.5 bg-white/60 rounded-r-[1px]"></div>
                        </div>
                      </div>
                    </div>

                    <!-- Main Content Area with Iframe -->
                    <div class="absolute inset-0 pt-11 pb-0 bg-white">
                      <template v-if="selectedNfcCardId && getSelectedCard()">
                        <iframe
                          :key="previewKey"
                          :src="`${getCardPublicUrl(getSelectedCard(), { preview: true })}&t=${previewKey}`"
                          class="w-full h-full border-0"
                          @load="onPreviewLoad"
                          @error="onPreviewError"
                        ></iframe>
                          
                        <!-- NFC Card ID Badge -->
                        <div class="absolute bottom-3 right-3 bg-blue-600/95 backdrop-blur-sm text-white text-[10px] px-2.5 py-1 rounded-full shadow-lg flex items-center gap-1 z-20">
                          <Icon name="heroicons:credit-card" class="h-3 w-3" />
                          <span class="font-semibold">{{ getSelectedCard()?.nfc_card_id || `Card #${getSelectedCard()?.id}` }}</span>
                        </div>
                      </template>
                      
                      <!-- Empty State -->
                      <div v-else class="w-full h-full bg-gradient-to-b from-blue-50 via-indigo-50 to-white flex items-center justify-center overflow-hidden">
                        <div class="text-center px-6 max-w-xs">
                          <div class="relative w-24 h-24 mx-auto mb-6">
                            <div class="absolute inset-0 bg-blue-100 rounded-full animate-pulse"></div>
                            <div class="absolute inset-2 bg-blue-200 rounded-full opacity-50"></div>
                            <Icon name="heroicons:device-phone-mobile" class="absolute inset-0 h-24 w-24 text-blue-500 p-5" />
                          </div>
                          <h3 class="text-lg font-bold text-indigo-900 mb-2">Preview Area</h3>
                          <p class="text-sm text-indigo-700/80 mb-6 leading-relaxed">
                            Select a card from the left to view real-time preview
                          </p>
                          <div class="inline-flex items-center justify-center px-4 py-2 bg-white text-blue-700 rounded-full text-xs font-semibold shadow-sm border border-blue-100">
                            <Icon name="heroicons:arrow-left" class="h-3.5 w-3.5 mr-1.5" />
                            <span>Please select a card first</span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Home Indicator -->
                    <div class="absolute bottom-1.5 left-1/2 -translate-x-1/2 w-[134px] h-[5px] bg-black rounded-full z-30"></div>
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
            <div class="flex gap-3 pt-4">
              <button type="button" @click="closePlatformForm" class="btn btn-outline flex-1">Cancel</button>
              <button type="submit" :disabled="savingLink" class="btn btn-primary flex-1 flex items-center justify-center">
                <Icon v-if="savingLink" name="heroicons:arrow-path" class="h-4 w-4 mr-2 animate-spin" />
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

          <!-- Search Filter -->
          <div class="mb-4">
            <div class="relative">
              <input
                v-model="cardSearchQuery"
                type="text"
                placeholder="Search by card owner or card ID..."
                class="w-full px-4 py-2 pl-10 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
              <Icon name="heroicons:magnifying-glass" class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
              <button
                v-if="cardSearchQuery"
                @click="cardSearchQuery = ''"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>
            <p v-if="cardSearchQuery" class="text-xs text-gray-500 mt-2">
              Showing {{ filteredAvailableCards.length }} of {{ allAvailableCards.length }} cards
            </p>
          </div>

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
              {{ selectedCardsForDesign.length }} / {{ filteredAvailableCards.length }} selected
            </span>
          </div>

          <!-- Card Selection List -->
          <div class="space-y-3 mb-6 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-2">
            <div v-if="filteredAvailableCards.length === 0" class="text-center py-12 text-gray-500 bg-gray-50 rounded-lg">
              <Icon name="heroicons:credit-card" class="h-12 w-12 mx-auto mb-3 text-gray-300" />
              <p class="text-sm font-medium">{{ cardSearchQuery ? 'No cards found' : 'No other cards available' }}</p>
              <p class="text-xs text-gray-400 mt-1">{{ cardSearchQuery ? 'Try a different search term' : 'All your cards are already selected or this is your only card' }}</p>
            </div>
            
            <label
              v-for="card in filteredAvailableCards"
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
          <div class="flex gap-3">
            <button
              @click="showApplyDesignModal = false"
              class="btn btn-outline flex-1"
            >
              Cancel
            </button>
            <button
              @click="applyDesignToCards"
              :disabled="selectedCardsForDesign.length === 0 || applyingDesign"
              class="btn btn-primary flex-1 flex items-center justify-center"
            >
              <Icon
                v-if="applyingDesign"
                name="heroicons:arrow-path"
                class="h-5 w-5 mr-2 animate-spin"
              />
              <Icon
                v-else
                name="heroicons:paint-brush"
                class="h-5 w-5 mr-2"
              />
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
import DynamicFormField from '~/components/DynamicFormField.vue';
import LayoutDesigner from '~/components/LayoutDesigner.vue';
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
const config = useRuntimeConfig();

// ─── IMPORTS (fast-fix performance: navigation cache + request abort) ─────
// Singleton module-level cache for builder config data (sections/fields/design)
// and NFC cards list — re-served for 2 minutes so rapid sidebar clicks cost 0ms
// instead of 4 fresh API calls per navigation.
import {
  getCachedBuilderInit,
  setCachedBuilderInit,
  getCachedNfcCards,
  setCachedNfcCards,
  createAbortOnRouteChange,
} from "~/plugins/api.client.js";

// Reactive data
const mainCategory = ref("general"); // 'general' or 'design'
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
const cardSearchQuery = ref(''); // Search query for filtering cards

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

// ============================================================
// CLIENT-SIDE DESIGN LIBRARY (OVERRIDES DB — ensures full previews)
// This guarantees previews work even before DB seeder is re-run.
// ============================================================
const DESIGN_LIBRARY = {
  themes: [
    { id: "minimal", name: "Minimal White", previewCss: "background: #FFFFFF; border: 1px solid #E5E7EB;", backgroundColor: "#FFFFFF", textColor: "#111827", accentColor: "#1F2937", bgGradient: null },
    { id: "modern", name: "Modern Soft Grey", previewCss: "background: linear-gradient(135deg, #F9FAFB 0%, #E5E7EB 100%);", backgroundColor: "#F9FAFB", textColor: "#111827", accentColor: "#2563EB", bgGradient: "linear-gradient(135deg, #F9FAFB 0%, #E5E7EB 100%)" },
    { id: "creative", name: "Creative Purple Pink", previewCss: "background: linear-gradient(135deg, #A855F7 0%, #EC4899 100%);", backgroundColor: "#A855F7", textColor: "#FFFFFF", accentColor: "#EC4899", bgGradient: "linear-gradient(135deg, #A855F7 0%, #EC4899 100%)" },
    { id: "zora-pro", name: "Zora Pro Navy Teal", previewCss: "background: linear-gradient(160deg, #0F2744 0%, #18395E 55%, #1F5177 100%);", backgroundColor: "#0F2744", textColor: "#E8EEF4", accentColor: "#5B8A86", bgGradient: "linear-gradient(160deg, #0F2744 0%, #18395E 55%, #1F5177 100%)" },
    { id: "professional", name: "Corporate Blue", previewCss: "background: linear-gradient(135deg, #2563EB 0%, #1E40AF 100%);", backgroundColor: "#2563EB", textColor: "#FFFFFF", accentColor: "#BFDBFE", bgGradient: "linear-gradient(135deg, #2563EB 0%, #1E40AF 100%)" },
    { id: "dark", name: "Dark Night Matte", previewCss: "background: linear-gradient(135deg, #111827 0%, #0B1220 100%);", backgroundColor: "#111827", textColor: "#F9FAFB", accentColor: "#60A5FA", bgGradient: "linear-gradient(135deg, #111827 0%, #0B1220 100%)" },
    { id: "warm-sand", name: "Warm Sand Beige", previewCss: "background: linear-gradient(135deg, #FBF7F0 0%, #F3EADB 50%, #E9D9BE 100%);", backgroundColor: "#FBF7F0", textColor: "#3F3427", accentColor: "#B45309", bgGradient: "linear-gradient(135deg, #FBF7F0 0%, #F3EADB 50%, #E9D9BE 100%)" },
    { id: "deep-charcoal", name: "Deep Charcoal Slate", previewCss: "background: linear-gradient(160deg, #1E293B 0%, #0F172A 100%);", backgroundColor: "#1E293B", textColor: "#E2E8F0", accentColor: "#60A5FA", bgGradient: "linear-gradient(160deg, #1E293B 0%, #0F172A 100%)" },
    { id: "sunset-gold", name: "Sunset Gold Orange", previewCss: "background: linear-gradient(135deg, #F97316 0%, #EF4444 45%, #DB2777 100%);", backgroundColor: "#F97316", textColor: "#FFFBEB", accentColor: "#F59E0B", bgGradient: "linear-gradient(135deg, #F97316 0%, #EF4444 45%, #DB2777 100%)" },
    { id: "ocean-breeze", name: "Ocean Breeze Cyan", previewCss: "background: linear-gradient(160deg, #0284C7 0%, #0EA5E9 40%, #14B8A6 100%);", backgroundColor: "#0EA5E9", textColor: "#ECFEFF", accentColor: "#14B8A6", bgGradient: "linear-gradient(160deg, #0284C7 0%, #0EA5E9 40%, #14B8A6 100%)" },
    { id: "emerald-forest", name: "Emerald Forest Green", previewCss: "background: linear-gradient(160deg, #065F46 0%, #047857 40%, #166534 100%);", backgroundColor: "#166534", textColor: "#F0FDF4", accentColor: "#84CC16", bgGradient: "linear-gradient(160deg, #065F46 0%, #047857 40%, #166534 100%)" },
    { id: "sakura-dusk", name: "Sakura Dusk Pastel", previewCss: "background: linear-gradient(135deg, #FCE7F3 0%, #EDE9FE 50%, #DBEAFE 100%);", backgroundColor: "#FCE7F3", textColor: "#831843", accentColor: "#A78BFA", bgGradient: "linear-gradient(135deg, #FCE7F3 0%, #EDE9FE 50%, #DBEAFE 100%)" },
    { id: "midnight-rose", name: "Midnight Rose Dark", previewCss: "background: linear-gradient(135deg, #4C1D95 0%, #831843 55%, #9D174D 100%);", backgroundColor: "#4C1D95", textColor: "#FCE7F3", accentColor: "#F472B6", bgGradient: "linear-gradient(135deg, #4C1D95 0%, #831843 55%, #9D174D 100%)" },
    { id: "mint-fresh", name: "Mint Fresh Cool", previewCss: "background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 45%, #A7F3D0 100%);", backgroundColor: "#ECFDF5", textColor: "#065F46", accentColor: "#10B981", bgGradient: "linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 45%, #A7F3D0 100%)" },
    { id: "cyberpunk-neon", name: "Cyberpunk Neon", previewCss: "background: linear-gradient(160deg, #0B0F19 0%, #1A0B2E 40%, #312E81 100%);", backgroundColor: "#0B0F19", textColor: "#F0ABFC", accentColor: "#22D3EE", bgGradient: "linear-gradient(160deg, #0B0F19 0%, #1A0B2E 40%, #312E81 100%)" },
    { id: "desert-mirage", name: "Desert Mirage Peach", previewCss: "background: linear-gradient(160deg, #FFF7ED 0%, #FFEDD5 45%, #FED7AA 100%);", backgroundColor: "#FFF7ED", textColor: "#7C2D12", accentColor: "#EA580C", bgGradient: "linear-gradient(160deg, #FFF7ED 0%, #FFEDD5 45%, #FED7AA 100%)" },
    { id: "royal-navy", name: "Royal Navy Gold", previewCss: "background: linear-gradient(160deg, #1E3A8A 0%, #1E40AF 50%, #0C4A6E 100%);", backgroundColor: "#1E3A8A", textColor: "#FEF3C7", accentColor: "#FBBF24", bgGradient: "linear-gradient(160deg, #1E3A8A 0%, #1E40AF 50%, #0C4A6E 100%)" },
    { id: "lavender-cream", name: "Lavender Cream", previewCss: "background: linear-gradient(135deg, #FAF5FF 0%, #F3E8FF 45%, #E9D5FF 100%);", backgroundColor: "#FAF5FF", textColor: "#581C87", accentColor: "#A855F7", bgGradient: "linear-gradient(135deg, #FAF5FF 0%, #F3E8FF 45%, #E9D5FF 100%)" },
    { id: "midnight-gold", name: "Midnight Matte Gold", previewCss: "background: linear-gradient(160deg, #0A0A0A 0%, #171717 50%, #262626 100%);", backgroundColor: "#0A0A0A", textColor: "#FDE68A", accentColor: "#EAB308", bgGradient: "linear-gradient(160deg, #0A0A0A 0%, #171717 50%, #262626 100%)" },
    { id: "coffee-bean", name: "Coffee Bean Mocha", previewCss: "background: linear-gradient(160deg, #451A03 0%, #78350F 55%, #92400E 100%);", backgroundColor: "#451A03", textColor: "#FEF3C7", accentColor: "#F59E0B", bgGradient: "linear-gradient(160deg, #451A03 0%, #78350F 55%, #92400E 100%)" },
  ],
  cardStyles: [
    { id: "minimal", name: "Minimal Clean", previewCss: "background: rgba(255,255,255,0.92); border-radius: 18px; border: 1.5px solid rgba(229,231,235,0.7); box-shadow: 0 6px 20px rgba(15,23,42,0.06);", class: "bg-white/90 backdrop-blur-sm rounded-2xl border border-gray-200/60" },
    { id: "glass", name: "Glassmorphism Pro", previewCss: "background: linear-gradient(135deg, rgba(255,255,255,0.28) 0%, rgba(255,255,255,0.08) 100%); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border-radius: 26px; border: 1.5px solid rgba(255,255,255,0.3); box-shadow: 0 30px 60px -12px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.45);", class: "bg-white/10 backdrop-blur-xl rounded-3xl border border-white/20 shadow-xl" },
    { id: "neumorph", name: "Neumorphism Soft", previewCss: "background: #F3F4F6; border-radius: 26px; box-shadow: 12px 12px 26px rgba(0,0,0,0.08), -12px -12px 26px rgba(255,255,255,0.98), inset 0 0 0 1px rgba(255,255,255,0.6);", class: "rounded-3xl bg-gray-100 shadow-[12px_12px_24px_rgba(0,0,0,0.08),-12px_-12px_24px_rgba(255,255,255,0.9)]" },
    { id: "frosted-bordered", name: "Frosted 3px Frame", previewCss: "background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(255,255,255,0.04)); backdrop-filter: blur(20px); border-radius: 26px; border: 3px solid rgba(255,255,255,0.5); box-shadow: 0 20px 40px rgba(0,0,0,0.2);", class: "bg-white/5 backdrop-blur-xl rounded-3xl border-[3px] border-white/40" },
    { id: "neon-outline", name: "Neon Outline Glow", previewCss: "background: rgba(0,0,0,0.3); backdrop-filter: blur(16px); border-radius: 26px; border: 1.5px solid #22D3EE; box-shadow: 0 0 22px rgba(34,211,238,0.5), inset 0 0 18px rgba(34,211,238,0.12), 0 0 60px rgba(34,211,238,0.22);", class: "bg-black/20 backdrop-blur-md rounded-3xl border border-cyan-400/50 shadow-[0_0_30px_rgba(34,211,238,0.3)]" },
    { id: "mica-blur", name: "Mica Fluent Blur", previewCss: "background: linear-gradient(180deg, rgba(255,255,255,0.18) 0%, rgba(255,255,255,0.06) 50%, rgba(255,255,255,0.02) 100%); backdrop-filter: blur(28px) saturate(160%); -webkit-backdrop-filter: blur(28px) saturate(160%); border-radius: 22px; border-top: 1px solid rgba(255,255,255,0.45); border-left: 1px solid rgba(255,255,255,0.25); border-right: 1px solid rgba(255,255,255,0.15); border-bottom: 1px solid rgba(255,255,255,0.1); box-shadow: 0 20px 50px rgba(0,0,0,0.35);", class: "backdrop-blur-2xl rounded-3xl" },
    { id: "liquid-chrome", name: "Liquid Chrome Metallic", previewCss: "background: linear-gradient(135deg, rgba(255,255,255,0.55) 0%, rgba(226,232,240,0.25) 35%, rgba(255,255,255,0.45) 55%, rgba(148,163,184,0.22) 80%, rgba(255,255,255,0.5) 100%); backdrop-filter: blur(20px); border-radius: 24px; border: 1px solid rgba(255,255,255,0.55); box-shadow: 0 18px 40px rgba(15,23,42,0.3), inset 0 0 0 1px rgba(255,255,255,0.35);", class: "backdrop-blur-xl rounded-3xl border" },
    { id: "aurora-tint", name: "Aurora Gradient Tint", previewCss: "background: linear-gradient(140deg, rgba(56,189,248,0.32) 0%, rgba(168,85,247,0.26) 45%, rgba(236,72,153,0.3) 100%); backdrop-filter: blur(18px); border-radius: 26px; border: 1px solid rgba(255,255,255,0.35); box-shadow: 0 24px 55px rgba(168,85,247,0.25);", class: "backdrop-blur-xl rounded-3xl border" },
    { id: "cyber-lines", name: "Cyber Lines Frame", previewCss: "background: rgba(11,15,25,0.68); backdrop-filter: blur(14px); border-radius: 22px; border: 1.5px solid transparent; background-clip: padding-box, border-box; background-origin: padding-box, border-box; background-image: linear-gradient(135deg, rgba(11,15,25,0.85), rgba(11,15,25,0.55)), linear-gradient(135deg, #22D3EE 0%, #A855F7 45%, #F472B6 100%); box-shadow: 0 20px 40px rgba(34,211,238,0.18);", class: "rounded-3xl backdrop-blur-md" },
    { id: "dot-matrix-frame", name: "Dot Matrix Border", previewCss: "background: rgba(255,255,255,0.82); border-radius: 22px; border: 2px dotted #6366F1; box-shadow: 0 14px 35px rgba(99,102,241,0.12), inset 0 0 0 3px rgba(255,255,255,0.88);", class: "bg-white/90 rounded-3xl border border-dotted" },
    { id: "velvet-matte", name: "Velvet Matte Dark", previewCss: "background: linear-gradient(160deg, rgba(17,24,39,0.88) 0%, rgba(30,41,59,0.82) 100%); backdrop-filter: blur(18px); border-radius: 24px; border: 1px solid rgba(148,163,184,0.25); box-shadow: 0 24px 50px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.05);", class: "bg-slate-900/80 backdrop-blur-xl rounded-3xl border" },
  ],
  bgAnimations: [
    { id: "none", name: "None / Static Flat", previewCss: "background: transparent;" },
    { id: "particles", name: "Floating Particles Blob", previewCss: "background: radial-gradient(circle at 18% 25%, rgba(255,255,255,0.32) 0%, transparent 38%), radial-gradient(circle at 72% 62%, rgba(255,255,255,0.28) 0%, transparent 42%), radial-gradient(circle at 38% 82%, rgba(255,255,255,0.2) 0%, transparent 36%), radial-gradient(circle at 88% 30%, rgba(255,255,255,0.18) 0%, transparent 30%);" },
    { id: "aurora", name: "Aurora Borealis Shift", previewCss: "background: linear-gradient(120deg, rgba(56,189,248,0.62), rgba(168,85,247,0.58), rgba(236,72,153,0.62), rgba(56,189,248,0.62)); background-size: 300% 300%;" },
    { id: "mesh-gradient", name: "Gradient Mesh 4 Blob", previewCss: "background: radial-gradient(circle at 18% 18%, rgba(99,102,241,0.55) 0%, transparent 48%), radial-gradient(circle at 82% 28%, rgba(236,72,153,0.48) 0%, transparent 48%), radial-gradient(circle at 50% 85%, rgba(20,184,166,0.48) 0%, transparent 48%), radial-gradient(circle at 65% 55%, rgba(251,191,36,0.32) 0%, transparent 48%);" },
    { id: "starfield", name: "Starfield Twinkle", previewCss: "background: radial-gradient(2.5px 2.5px at 20% 30%, rgba(255,255,255,0.95), transparent 60%), radial-gradient(2px 2px at 60% 70%, rgba(255,255,255,0.85), transparent 60%), radial-gradient(1.5px 1.5px at 40% 12%, rgba(255,255,255,0.9), transparent 60%), radial-gradient(2px 2px at 85% 45%, rgba(255,255,255,0.8), transparent 60%), radial-gradient(1.5px 1.5px at 15% 55%, rgba(255,255,255,0.75), transparent 60%), radial-gradient(2.5px 2.5px at 78% 88%, rgba(255,255,255,0.9), transparent 60%), radial-gradient(1.5px 1.5px at 5% 82%, rgba(255,255,255,0.7), transparent 60%);" },
    { id: "slow-waves", name: "Slow Waves Layered", previewCss: "background: linear-gradient(180deg, transparent 0%, rgba(255,255,255,0.08) 28%, transparent 56%, rgba(255,255,255,0.12) 82%, transparent 100%), repeating-linear-gradient(transparent, transparent 12px, rgba(255,255,255,0.18) 12px, rgba(255,255,255,0.18) 14px);" },
    { id: "sparkle", name: "Magic Sparkle Burst", previewCss: "background: radial-gradient(circle at 22% 22%, rgba(255,255,255,0.45) 0%, transparent 10%), radial-gradient(circle at 78% 68%, rgba(255,255,255,0.45) 0%, transparent 10%), radial-gradient(circle at 52% 48%, rgba(255,255,255,0.28) 0%, transparent 14%), radial-gradient(circle at 38% 78%, rgba(255,255,255,0.35) 0%, transparent 9%), radial-gradient(circle at 88% 28%, rgba(255,255,255,0.38) 0%, transparent 10%);" },
    { id: "grid-dots", name: "Dotted Grid Pattern", previewCss: "background-image: radial-gradient(rgba(255,255,255,0.3) 1.8px, transparent 1.8px); background-size: 20px 20px; background-position: 0 0;" },
    { id: "fireflies", name: "Fireflies Amber", previewCss: "background: radial-gradient(circle at 20% 30%, rgba(251,191,36,0.65) 0%, transparent 8%), radial-gradient(circle at 40% 75%, rgba(251,191,36,0.55) 0%, transparent 7%), radial-gradient(circle at 65% 35%, rgba(250,204,21,0.6) 0%, transparent 8%), radial-gradient(circle at 82% 68%, rgba(234,179,8,0.6) 0%, transparent 7%), radial-gradient(circle at 50% 20%, rgba(253,224,71,0.5) 0%, transparent 6%);" },
    { id: "bokeh", name: "Bokeh Circles Dreamy", previewCss: "background: radial-gradient(circle at 18% 28%, rgba(96,165,250,0.55) 0%, transparent 28%), radial-gradient(circle at 72% 38%, rgba(244,114,182,0.5) 0%, transparent 30%), radial-gradient(circle at 38% 72%, rgba(52,211,153,0.52) 0%, transparent 28%), radial-gradient(circle at 85% 75%, rgba(250,204,21,0.48) 0%, transparent 26%), radial-gradient(circle at 58% 55%, rgba(168,85,247,0.48) 0%, transparent 30%);" },
    { id: "liquid-blobs", name: "Liquid Metaball Blobs", previewCss: "background: radial-gradient(ellipse 38% 30% at 28% 28%, rgba(244,114,182,0.65), transparent 60%), radial-gradient(ellipse 40% 32% at 72% 72%, rgba(34,211,238,0.62), transparent 60%), radial-gradient(ellipse 30% 26% at 70% 22%, rgba(168,85,247,0.55), transparent 62%), radial-gradient(ellipse 35% 28% at 30% 78%, rgba(250,204,21,0.55), transparent 60%);" },
    { id: "neon-lines", name: "Neon Diagonal Lines", previewCss: "background: repeating-linear-gradient(125deg, transparent 0, transparent 28px, rgba(34,211,238,0.3) 28px, rgba(34,211,238,0.3) 30px), repeating-linear-gradient(55deg, transparent 0, transparent 40px, rgba(244,114,182,0.26) 40px, rgba(244,114,182,0.26) 42px);" },
    { id: "snow-fall", name: "Snow Fall White", previewCss: "background: radial-gradient(2.5px 2.5px at 15% 18%, rgba(255,255,255,0.95), transparent 60%), radial-gradient(2px 2px at 35% 55%, rgba(255,255,255,0.88), transparent 60%), radial-gradient(3px 3px at 55% 32%, rgba(255,255,255,1), transparent 60%), radial-gradient(2px 2px at 78% 72%, rgba(255,255,255,0.85), transparent 60%), radial-gradient(2.5px 2.5px at 92% 28%, rgba(255,255,255,0.95), transparent 60%), radial-gradient(1.5px 1.5px at 8% 78%, rgba(255,255,255,0.78), transparent 60%), radial-gradient(2px 2px at 65% 88%, rgba(255,255,255,0.85), transparent 60%);" },
    { id: "cosmic-dust", name: "Cosmic Dust Purple", previewCss: "background: radial-gradient(circle at 20% 20%, rgba(168,85,247,0.45) 0%, transparent 45%), radial-gradient(circle at 80% 30%, rgba(236,72,153,0.38) 0%, transparent 45%), radial-gradient(circle at 35% 85%, rgba(99,102,241,0.45) 0%, transparent 45%), radial-gradient(circle at 70% 70%, rgba(56,189,248,0.4) 0%, transparent 45%), radial-gradient(1.3px 1.3px at 25% 45%, rgba(255,255,255,0.8), transparent 60%), radial-gradient(1.3px 1.3px at 75% 55%, rgba(255,255,255,0.8), transparent 60%);" },
    { id: "confetti-pop", name: "Confetti Pop Color", previewCss: "background: radial-gradient(circle at 18% 25%, #EF4444 0 2.5%, transparent 3%), radial-gradient(circle at 35% 65%, #22D3EE 0 2.5%, transparent 3%), radial-gradient(circle at 52% 32%, #F59E0B 0 2.5%, transparent 3%), radial-gradient(circle at 70% 75%, #10B981 0 2.5%, transparent 3%), radial-gradient(circle at 85% 28%, #A855F7 0 2.5%, transparent 3%), radial-gradient(circle at 48% 80%, #EC4899 0 2%, transparent 2.5%), radial-gradient(circle at 12% 72%, #60A5FA 0 2%, transparent 2.5%);" },
  ],
  fonts: [
    { id: "inter", name: "Inter Clean", family: "Inter, system-ui, -apple-system, sans-serif" },
    { id: "poppins", name: "Poppins Rounded", family: "Poppins, Inter, system-ui, sans-serif" },
    { id: "roboto", name: "Roboto Classic", family: "Roboto, Inter, system-ui, sans-serif" },
    { id: "plus-jakarta", name: "Plus Jakarta Sans", family: '"Plus Jakarta Sans", Poppins, sans-serif' },
    { id: "playfair", name: "Playfair Display Serif", family: 'Playfair Display, Georgia, serif' },
    { id: "lora", name: "Lora Serif", family: "Lora, Georgia, serif" },
    { id: "space-grotesk", name: "Space Grotesk", family: '"Space Grotesk", Inter, sans-serif' },
    { id: "outfit", name: "Outfit Modern", family: "Outfit, Inter, system-ui, sans-serif" },
    { id: "manrope", name: "Manrope Sharp", family: "Manrope, Inter, system-ui, sans-serif" },
    { id: "dm-sans", name: "DM Sans Friendly", family: '"DM Sans", Poppins, sans-serif' },
    { id: "montserrat", name: "Montserrat Bold", family: 'Montserrat, Poppins, sans-serif' },
    { id: "crimson", name: "Crimson Pro Literary", family: '"Crimson Pro", Georgia, serif' },
  ],
  buttonStyles: [
    { id: "solid", name: "Solid Pill Bold", class: "bg-gray-900 text-white font-semibold rounded-full" },
    { id: "outline", name: "Outline Pill Border", class: "border-2 border-gray-800 text-gray-800 font-medium rounded-full" },
    { id: "soft", name: "Soft Glass Rounded", class: "bg-white/18 backdrop-blur-md text-gray-900 font-medium rounded-2xl border border-gray-200/70" },
    { id: "shadow", name: "Elevation Shadow", class: "bg-white text-gray-900 font-semibold rounded-2xl shadow-xl" },
    { id: "gradient", name: "Gradient Glow Pop", class: "bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white font-semibold rounded-full shadow-lg shadow-purple-500/30" },
    { id: "square", name: "Sharp Square Bold", class: "bg-gray-900 text-white font-semibold rounded-lg" },
    { id: "glass-pill", name: "Glass Frosted Pill", class: "bg-white/15 backdrop-blur-xl text-white font-medium rounded-full border border-white/30 shadow-lg" },
    { id: "neumorph-btn", name: "Neumorph Bevel", class: "rounded-3xl bg-gray-100 text-gray-800 font-semibold shadow-[6px_6px_14px_rgba(0,0,0,0.08),-6px_-6px_14px_rgba(255,255,255,0.95)]" },
    { id: "dark-soft", name: "Dark Soft Matte", class: "bg-slate-800/90 backdrop-blur-md text-white font-semibold rounded-2xl shadow-lg border border-white/10" },
    { id: "3d-shadow", name: "3D Offset Pop", class: "rounded-2xl text-white font-bold border-b-4 border-b-gray-900/40 shadow-[0_10px_0_0_rgba(0,0,0,0.12)]" },
    { id: "metallic", name: "Metallic Gradient", class: "bg-gradient-to-b from-white 0%, via-slate-200 55%, to-slate-400 100% text-slate-800 font-bold rounded-full shadow-xl border border-white/70" },
    { id: "double-outline", name: "Double Outline Frame", class: "rounded-full font-semibold border-2 border-current outline outline-2 outline-offset-4 outline-current/50" },
  ],
};

const SERVICE_ICONS = [
  '🚀', '💼', '🏢', '👨‍💻', '👩‍💼', '📊', '📈', '📉',
  '🎯', '💡', '🛠️', '🔧', '⚙️', '🔩', '🏗️', '🏭',
  '📱', '💻', '🖥️', '🖨️', '🌐', '☁️', '🔒', '🛡️',
  '📷', '🎨', '✏️', '🖌️', '🎬', '🎵', '📝', '📚',
  '🏥', '💊', '🦷', '👁️', '💉', '🧬', '🌿', '🍃',
  '🛒', '🛍️', '💳', '💰', '💵', '💎', '📦', '🚚',
  '✈️', '🚗', '🚕', '🚌', '🚢', '🏨', '🍽️', '☕',
  '🎓', '📖', '✍️', '🎤', '🎧', '📞', '📧', '💬',
  '🌱', '🌾', '🧑‍🌾', '🐄', '🍎', '🍽️', '🧾', '📋',
  '🧰', '🪛', '⚖️', '🏛️', '👮', '👷', '🧑‍🍳', '👨‍🔧',
];

// Profile data - field keys match backend ProfileBuilderField.field_key
const profileData = reactive({
  // ============ PROFILE TAB ============
  profilePicture: null,       // Profile Photo (image)
  coverBanner: null,          // Top Cover Banner (image)
  name: "",                   // Full Name
  position: "",               // Job Title / Role
  pronouns: "",               // Pronouns (select)
  qualification: "",          // Professional Qualification
  bio: "",                    // Personal Biography (richtext)
  tagline: "",                // Short Tagline
  contactNumber: "",          // Phone Number (tel)
  emailAddress: "",           // Email
  website: "",                // Personal Website (url)
  address: "",                // Address (textarea)
  education: [],              // Education Background (repeater)
  certifications: [],         // Licenses & Certifications (repeater)
  profileStats: [],           // Profile Statistics (repeater)

  // ============ COMPANY TAB ============
  companyLogo: null,          // Company Logo (image)
  companyLogoText: "",        // Logo Text
  companyName: "",            // Company Name
  companyRegistrationNo: "",  // Registration No
  companyDepartment: "",      // Department
  companyDescription: "",     // Company Description (richtext)
  companyVideo: "",           // Company Video URL
  industry: "",               // Industry Type (select)
  establishedYear: "",        // Year Founded
  employeeCount: "",          // Number of Employees
  // Company Address (Simplified: 3 lines)
  address1: "",               // Address Line 1
  address2: "",               // Address Line 2
  address3: "",               // Address Line 3 (Postcode, City, State, Country)
  // Legacy separate address fields (for backward compatibility)
  addressName: "",            // Building/Location Name
  addressStreet: "",          // Street Address
  addressArea: "",            // Area/District
  addressCityState: "",       // City & State
  addressCountry: "",         // Country
  postalCode: "",             // Postal Code
  mapUrl: "",                 // Google Maps URL
  coordinates: "",            // Map Coordinates
  phoneLabel: "",             // Phone Label
  phoneNumber: "",            // Phone Number
  companyWhatsapp: "",        // WhatsApp
  workingHours: [],           // Operating Hours (repeater)
  awards: [],                 // Awards & Achievements (repeater)
  teamMembers: [],            // Team Members (repeater)

  // ============ SERVICES TAB ============
  serviceName: "",            // Service Name
  serviceIcon: "🚀",          // Service Icon (emoji)
  serviceCategory: "",        // Category / Type (select)
  serviceImage: null,         // Service Image
  serviceVideo: "",           // Promo Video (url)
  serviceDescription: "",     // Full Service Description (richtext)
  serviceFeatures: [],        // Key Features (repeater)
  servicePrice: "",           // Current Price
  serviceOldPrice: "",        // Previous Price
  serviceDuration: "",        // Duration
  serviceTags: [],            // Keywords / Tags (repeater)
  serviceBrochure: null,      // Service Brochure PDF (file)
  bookingEnabled: false,      // Enable Booking Button
  bookingUrl: "",             // External Booking Link (url)
  gallery: [],                // Photo Gallery (gallery)
  services: [],               // Legacy services array

  // ============ LINKS TAB ============
  phone: "",                  // Contact Number (tel)
  whatsapp: "",               // WhatsApp (tel)
  appointmentLink: "",        // Appointment Link (url)
  paymentButtonText: "",      // Payment Button Text
  paymentButtonUrl: "",       // Payment Button URL
  socialLinks: [],            // Social Media Links (repeater)
  linkTitle: "",              // Link Title
  linkUrl: "",                // URL
  linkIcon: "",               // Icon Selection
  linkType: "",               // Link Behavior (select)
  utmParameters: "",          // Marketing Tracking

  // ============ PORTFOLIO TAB ============
  portfolioTitle: "",         // Project Title
  portfolioDescription: "",   // Project Details (richtext)
  portfolioCategory: "",      // Portfolio Category (select)
  portfolioTags: [],          // Tags (repeater)
  portfolioCoverImage: null,  // Cover Image
  portfolioGallery: [],       // Project Gallery (gallery)
  projects: [],               // Projects (repeater)
  projectUrl: "",             // External Project Link (url)
  dateCompleted: "",          // Completion Date
  clientName: "",             // Client Name
  location: "",               // Project Location
  skillsUsed: [],             // Tools / Skills Used (repeater)
  pdfDownload: null,          // PDF for Download (file)

  // ============ BLOG TAB ============
  blogEnabled: false,         // Enable Blog
  blogPosts: [],              // Blog Posts (repeater)
  blogGallery: [],            // Blog Gallery (gallery)
  blogTitle: "",              // Blog Post Title
  blogSlug: "",               // URL Slug
  blogCoverImage: null,       // Cover Banner
  blogCategory: "",           // Blog Category
  blogTags: [],               // Tags
  authorName: "",             // Author Name
  publishedDate: "",          // Publication Date
  readingTime: "",            // Reading Time
  blogContent: "",            // Blog Content (richtext)
  externalLink: "",           // External Article Link
  relatedPosts: [],           // Related Posts

  // ============ DESIGN SETTINGS ============
  profileStyle: "classic",
  theme: "minimal",
  backgroundColor: "#FFFFFF",
  textColor: "#000000",
  accentColor: "#1F2937",
  bgGradient: null,
  font: "inter",
  buttonStyle: "solid",
  cardStyle: "minimal",
  bgAnimation: "none",
  colorScheme: "",
  layout: "",
  showWatermark: true,
  
  // Features - user toggle state and order (all enabled by default except remove_branding)
  features: {
    contact_form: true,
    vcard_download: true,
    qr_code: true,
    remove_branding: false,  // OFF by default = show watermark
    booking_integration: true,
    social_sharing: true,
  },
  featureOrder: [],     // Array of feature IDs in custom order
  
  // Layout Configuration
  sectionLayout: [],    // Section order and visibility [{ id, order, enabled }]
  fieldLayout: {},      // Field order within sections { sectionId: [{ field_key, order, enabled }] }
  sectionSettings: {},  // Additional section settings { sectionId: { ... } }
  
  // Legacy compatibility
  image: null,
  stats: [],
  email: "",
  emailLabel: "Email",
  whatsappNumber: "",
  whatsappLabel: "WhatsApp",
  websiteUrl: "",
  websiteLabel: "Website",
  addressMapUrl: "",
  expertise: [],
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

/**
 * Resolve the PRETTIEST public profile URL for a given card object.
 * Priority 1 — Server-computed `card.public_url`:
 *    Auto decides SHORT "/profile/a/1" (name unique) OR LONG "/profile/a/1/userid-{pk}" (name clash).
 * Priority 2 — Derive locally using authStore user slug + local card index.
 * Priority 3 — Legacy fallbacks: nfc_card_id or numeric pk id.
 */
const getCardPublicUrl = (card, opts = {}) => {
  if (!card) return '';
  const { preview = false, suffix = '' } = opts;
  let base = '';

  if (typeof card.public_url === 'string' && card.public_url.startsWith('/profile/')) {
    base = card.public_url;
  }
  if (!base) {
    const authUser = authStore.user || {};
    const userSlug = authUser.name_slug;
    // Note: CardManagement.vue calls its array `nfcCards` not `userNfcCards`; handle both:
    const cardsSource =
      (typeof userNfcCards !== 'undefined' && userNfcCards && userNfcCards.value) ? userNfcCards.value :
      (typeof nfcCards !== 'undefined' && nfcCards && nfcCards.value) ? nfcCards.value :
      null;
    const localCardNumber = cardsSource && cardsSource.length > 0
      ? cardsSource
          .slice()
          .sort((a, b) => (new Date(a.created_at || a.createdAt || 0) - new Date(b.created_at || b.createdAt || 0)) || ((a.id ?? 0) - (b.id ?? 0)))
          .findIndex(c => c.id === card.id) + 1
      : null;
    if (userSlug && (localCardNumber || card.card_number)) {
      base = `/profile/${userSlug}/${localCardNumber || card.card_number}`;
      if (authUser.id != null && authUser.has_name_slug_clash === true) {
        base += '/userid-' + authUser.id;
      }
    }
  }
  if (!base) {
    base = `/profile/${card.nfc_card_id || card.id}`;
  }

  const qs = [];
  if (preview) qs.push('preview=true');
  if (suffix) qs.push(suffix.replace(/^\?/, ''));
  const qsStr = qs.length ? '?' + qs.join('&') : '';
  return base + qsStr;
};

// Internal single-param API identifier: encodes the multi-segment URL into the [id] param
// using "__" (double underscore) as path separator (frontend single-route-param limitation).
// Supports both 2-segment (short) and 3-segment (name clash + userid pk) URLs.
const getCardApiIdentifier = (card) => {
  if (!card) return '';
  if (typeof card.public_url === 'string' && card.public_url.startsWith('/profile/')) {
    const path = card.public_url.substring('/profile/'.length);
    if (path && path.includes('/')) {
      return path.split('/').filter(Boolean).join('__');
    }
    if (path) return path;
  }
  const authUser = authStore.user || {};
  const userSlug = authUser.name_slug;
  if (userSlug) {
    const cardsSource =
      (typeof userNfcCards !== 'undefined' && userNfcCards && userNfcCards.value) ? userNfcCards.value :
      (typeof nfcCards !== 'undefined' && nfcCards && nfcCards.value) ? nfcCards.value :
      null;
    const localNumber = card.card_number ??
      (cardsSource && cardsSource.length > 0
        ? (cardsSource
            .slice()
            .sort((a, b) => (new Date(a.created_at || a.createdAt || 0) - new Date(b.created_at || b.createdAt || 0)) || ((a.id ?? 0) - (b.id ?? 0)))
            .findIndex(c => c.id === card.id) + 1)
        : null);
    if (localNumber) {
      const two = `${userSlug}__${localNumber}`;
      if (authUser.id != null && authUser.has_name_slug_clash === true) {
        return `${two}__userid-${authUser.id}`;
      }
      return two;
    }
  }
  return card.nfc_card_id || card.id;
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
  { id: "reddit", name: "Reddit", icon: "mdi:reddit", color: "bg-orange-600", placeholder: "Reddit Profile", urlExample: "https://reddit.com/user/yourusername" },
  { id: "pinterest", name: "Pinterest", icon: "mdi:pinterest", color: "bg-red-700", placeholder: "Pinterest Profile", urlExample: "https://pinterest.com/yourusername" },
  { id: "wechat", name: "WeChat", icon: "mdi:wechat", color: "bg-green-500", placeholder: "WeChat ID", urlExample: "weixin://dl/chat?yourwechatid" },
  { id: "douyin", name: "Douyin (抖音)", icon: "simple-icons:douyin", color: "bg-black", placeholder: "Douyin Profile", urlExample: "https://douyin.com/user/yourusername" },
  { id: "discord", name: "Discord", icon: "mdi:discord", color: "bg-indigo-600", placeholder: "Discord Server/Profile", urlExample: "https://discord.gg/yourserver" },
  { id: "threads", name: "Threads", icon: "simple-icons:threads", color: "bg-black", placeholder: "Threads Profile", urlExample: "https://threads.net/@yourusername" },
  { id: "xiaohongshu", name: "Xiao Hong Shu (小红书)", icon: "simple-icons:xiaohongshu", color: "bg-red-500", placeholder: "Xiao Hong Shu Profile", urlExample: "https://xiaohongshu.com/user/profile/yourid" },
  { id: "quora", name: "Quora", icon: "mdi:quora", color: "bg-red-700", placeholder: "Quora Profile", urlExample: "https://quora.com/profile/yourusername" },
  { id: "custom", name: "Custom", icon: "heroicons:link", color: "bg-gradient-to-br from-blue-500 via-green-500 to-orange-500", placeholder: "Custom Link", urlExample: "https://yourlink.com" },
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

// Filtered available cards based on search query
const filteredAvailableCards = computed(() => {
  if (!cardSearchQuery.value.trim()) {
    return allAvailableCards.value;
  }
  
  const query = cardSearchQuery.value.toLowerCase().trim();
  return allAvailableCards.value.filter(card => {
    const cardOwner = (card.card_owner || '').toLowerCase();
    const cardId = (card.nfc_card_id || '').toLowerCase();
    const cardNumber = (card.id || '').toString();
    
    return cardOwner.includes(query) || 
           cardId.includes(query) || 
           cardNumber.includes(query);
  });
});

// Check if all cards are selected
const isAllCardsSelected = computed(() => {
  return filteredAvailableCards.value.length > 0 && 
         selectedCardsForDesign.value.length === filteredAvailableCards.value.length;
});

// ========================================
// DYNAMIC SECTIONS - Loaded from API
// ========================================
const sections = ref([]); // All sections with fields from API
const sectionsLoading = ref(false);

// Load sections dynamically from API
const loadSections = async () => {
  try {
    sectionsLoading.value = true;
    const userPlan = authStore.user?.subscription_plan || 'business';
    console.log('Loading sections for plan:', userPlan);
    
    const response = await fetch(`${config.public.apiBaseUrl}/profile-builder-sections?plan=${userPlan}`, {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
    });

    if (!response.ok) throw new Error('Failed to load sections');
    const data = await response.json();
    
    console.log('Sections API Response:', data);
    
    if (data.success && data.data) {
      sections.value = data.data.map(section => ({
        id: section.section_key,
        name: section.section_name,
        category: section.category,
        icon: section.icon,
        description: section.description,
        fields: section.fields || [],
        display_order: section.display_order
      }));
      console.log('Sections loaded:', sections.value);
    }
  } catch (error) {
    console.error('Error loading sections:', error);
    // Fallback to basic sections if API fails
    sections.value = [
      { id: 'profile', name: 'Profile', category: 'general', icon: 'heroicons:user', fields: [] },
      { id: 'company', name: 'Company', category: 'general', icon: 'heroicons:building-office', fields: [] },
      { id: 'services', name: 'Services', category: 'general', icon: 'heroicons:rocket-launch', fields: [] },
      { id: 'links', name: 'Links', category: 'general', icon: 'heroicons:link', fields: [] },
      { id: 'portfolio', name: 'Portfolio', category: 'general', icon: 'heroicons:folder', fields: [] },
      { id: 'blog', name: 'Blog', category: 'general', icon: 'heroicons:document-text', fields: [] },
      { id: 'design', name: 'Design', category: 'design', icon: 'heroicons:paint-brush', fields: [] },
    ];
  } finally {
    sectionsLoading.value = false;
  }
};

// Computed: All tabs from sections (backward compatible)
const allTabs = computed(() => {
  return sections.value.map(section => ({
    id: section.id,
    name: section.name,
    category: section.category,
    icon: section.icon,
    requiredPermission: section.id, // Use section id as permission
  }));
});

// Helper: Get visible fields for a section (supports dynamic sections)
const getVisibleFieldsForSection = (sectionId) => {
  // First try from sections data (works for ALL sections including dynamic ones)
  const section = sections.value.find(s => s.id === sectionId);
  if (section && section.fields && section.fields.length > 0) {
    return section.fields.filter(field => shouldShowField(field.field_key));
  }
  
  // Fallback to static refs for backward compatibility
  const fieldsMap = {
    'profile': profileFields.value,
    'company': companyFields.value,
    'services': servicesFields.value,
    'links': linksFields.value,
    'portfolio': portfolioFields.value,
    'blog': blogFields.value,
  };
  const fields = fieldsMap[sectionId] || [];
  return fields.filter(field => shouldShowField(field.field_key));
};

// Computed: Available tabs based on sections (only show sections with visible fields)
const availableGeneralTabs = computed(() => {
  return sections.value
    .filter(section => section.category === 'general')
    .filter(section => {
      // Only show section if it has visible fields
      const visibleFields = getVisibleFieldsForSection(section.id);
      return visibleFields.length > 0;
    })
    .map(section => ({
      id: section.id,
      name: section.name,
      category: section.category,
      icon: section.icon,
      fields: section.fields
    }))
    .sort((a, b) => (a.display_order || 0) - (b.display_order || 0));
});

const availableDesignTabs = computed(() => {
  // Design tabs
  const designTabs = [];
  
  // Only show Theme & Colors tab when admin has assigned theme options
  if (themes.value && themes.value.length > 0) {
    designTabs.push({ id: 'design', name: 'Theme & Colors', category: 'design', icon: 'heroicons:paint-brush' });
  }
  
  // Only show Typography tab when admin has assigned font options
  if (fonts.value && fonts.value.length > 0) {
    designTabs.push({ id: 'style', name: 'Typography', category: 'design', icon: 'heroicons:language' });
  }
  
  // Layout Designer option based on two conditions:
  // 1. User's subscription plan allows it
  // 2. Admin has assigned layout options
  if (canCustomizeLayout.value && layouts.value && layouts.value.length > 0) {
    designTabs.push({ id: 'layout', name: 'Layout Designer', category: 'design', icon: 'heroicons:squares-2x2' });
  }
  
  // Only show Features tab when admin has assigned features
  if (featureToggles.value && featureToggles.value.length > 0) {
    designTabs.push({ id: 'watermarks', name: 'Features', category: 'design', icon: 'heroicons:sparkles' });
  }
  
  return designTabs;
});

// Helper: Check if tab is a dynamic (admin-created) section
const hardcodedSections = ['profile', 'company', 'services', 'links', 'portfolio', 'blog', 'design', 'style', 'layout', 'watermarks', 'features'];
const isDynamicSection = (tabId) => {
  return !hardcodedSections.includes(tabId);
};

// Helper: Get fields for a dynamic section
const getDynamicSectionFields = (sectionId) => {
  const section = sections.value.find(s => s.id === sectionId);
  if (!section || !section.fields) return [];
  return section.fields.filter(field => shouldShowField(field.field_key));
};

// Helper: Get grouped fields for a dynamic section
const getDynamicSectionGroups = (sectionId) => {
  const fields = getDynamicSectionFields(sectionId);
  
  // Group by field_group
  const groups = {};
  fields.forEach(field => {
    const groupName = field.field_group || 'Other';
    if (!groups[groupName]) {
      groups[groupName] = {
        name: groupName,
        icon: field.field_group_icon || 'heroicons:squares-plus',
        order: field.group_order || 99,
        fields: []
      };
    }
    groups[groupName].fields.push(field);
  });
  
  return Object.values(groups).sort((a, b) => a.order - b.order);
};

// Computed: All available tabs
const tabs = computed(() => {
  return [...availableGeneralTabs.value, ...availableDesignTabs.value];
});

// Color presets for quick selection
const colorPresets = [
  { name: 'Light', bg: '#FFFFFF', text: '#1F2937' },
  { name: 'Dark', bg: '#1F2937', text: '#FFFFFF' },
  { name: 'Blue', bg: '#EFF6FF', text: '#1E40AF' },
  { name: 'Green', bg: '#F0FDF4', text: '#166534' },
  { name: 'Purple', bg: '#FAF5FF', text: '#7C3AED' },
  { name: 'Rose', bg: '#FFF1F2', text: '#BE123C' },
  { name: 'Amber', bg: '#FFFBEB', text: '#B45309' },
  { name: 'Cyan', bg: '#ECFEFF', text: '#0E7490' },
];

// Apply color preset
const applyColorPreset = (preset) => {
  profileData.backgroundColor = preset.bg;
  profileData.textColor = preset.text;
};

// Apply color scheme from API
const applyColorScheme = (scheme) => {
  profileData.colorScheme = scheme.id;
  if (scheme.colors) {
    if (scheme.colors.background) profileData.backgroundColor = scheme.colors.background;
    if (scheme.colors.text) profileData.textColor = scheme.colors.text;
    if (scheme.colors.primary) profileData.primaryColor = scheme.colors.primary;
    if (scheme.colors.secondary) profileData.secondaryColor = scheme.colors.secondary;
  }
};

// User's plan permissions (loaded from API)
const userPermissions = ref([
  "profile",
  "company",
  "services",
  "links",
  "design",
  "style",
  "watermarks",
]); // Default: all permissions

// Design options - will be loaded from API
// Note: profileStyles removed - fixed to 'classic'
const themes = ref([]);
const fonts = ref([]);
const buttonStyles = ref([]);
const colorSchemes = ref([]);
const layouts = ref([]);
const cardStyles = ref([]);
const bgAnimations = ref([]);
const featureToggles = ref([]);

// Feature drag state
const featureDragIndex = ref(null);
const featureDragOverIndex = ref(null);
const featureOrder = ref([]); // Stores the order of feature IDs

// Sorted feature toggles based on user's custom order
const sortedFeatureToggles = computed(() => {
  if (featureOrder.value.length === 0) {
    return featureToggles.value;
  }
  // Sort features based on saved order
  return [...featureToggles.value].sort((a, b) => {
    const indexA = featureOrder.value.indexOf(a.id);
    const indexB = featureOrder.value.indexOf(b.id);
    // If not in order array, put at end
    if (indexA === -1) return 1;
    if (indexB === -1) return -1;
    return indexA - indexB;
  });
});

// Feature drag handlers
const handleFeatureDragStart = (event, index) => {
  featureDragIndex.value = index;
  event.dataTransfer.effectAllowed = 'move';
};

const handleFeatureDragOver = (event, index) => {
  featureDragOverIndex.value = index;
};

const handleFeatureDragEnd = () => {
  featureDragIndex.value = null;
  featureDragOverIndex.value = null;
};

const handleFeatureDrop = (event, dropIndex) => {
  const dragIndex = featureDragIndex.value;
  if (dragIndex === null || dragIndex === dropIndex) return;
  
  // Get current order or initialize from current features
  const currentOrder = featureOrder.value.length > 0 
    ? [...featureOrder.value]
    : sortedFeatureToggles.value.map(f => f.id);
  
  // Reorder
  const [draggedItem] = currentOrder.splice(dragIndex, 1);
  currentOrder.splice(dropIndex, 0, draggedItem);
  
  // Update order
  featureOrder.value = currentOrder;
  
  // Save to profileData for persistence
  profileData.featureOrder = currentOrder;
  
  console.log('✅ Feature order updated:', currentOrder);
  
  // Reset drag state
  featureDragIndex.value = null;
  featureDragOverIndex.value = null;
};

// Fields - will be loaded from API
const profileFields = ref([]);
const companyFields = ref([]);
const servicesFields = ref([]);
const linksFields = ref([]);
const portfolioFields = ref([]);
const blogFields = ref([]);

// ========================================
// LAYOUT DESIGNER - Section & Field Layout
// ========================================

// Ref: Layout designer permission (loaded from API)
const layoutDesignerEnabled = ref(true); // Default enabled, will be updated from API

// Computed: Check if user's plan allows layout customization
const canCustomizeLayout = computed(() => {
  // Based on user's subscription plan allowing layout customization
  const userPlan = authStore.user?.subscription_plan || 'free';
  const allowedPlans = ['business', 'enterprise', 'premium'];
  return allowedPlans.includes(userPlan.toLowerCase());
});

// Landing Page Sections - granular control for each section
// These match exactly with the sections in profile/[id].vue
// IMPORTANT: fieldKeys must match the actual field_key values from ProfileBuilderFieldsSeeder (camelCase)
const landingPageSectionsConfig = [
  { id: 'hero', name: 'Hero / Profile', icon: 'heroicons:user-circle', description: 'Profile picture, name, title, badges', apiSource: 'profile', fieldKeys: ['profilePicture', 'name', 'position', 'qualification', 'pronouns', 'tagline', 'coverBanner'] },
  // Combined: Profile & Achievements (About + Education + Awards)
  { 
    id: 'profileAchievements', 
    name: 'Profile & Achievements', 
    icon: 'heroicons:sparkles', 
    description: 'About me, education, and awards',
    subSections: [
      { id: 'about', name: 'About Me', icon: 'heroicons:document-text', apiSource: 'profile', fieldKeys: ['bio', 'profileStats'] },
      { id: 'education', name: 'Education', icon: 'heroicons:academic-cap', apiSource: 'profile', fieldKeys: ['education', 'certifications'] },
      { id: 'awards', name: 'Awards', icon: 'heroicons:trophy', apiSource: 'company', fieldKeys: ['awards'] },
    ]
  },
  // Combined: Company & Team (Company Info + Team)
  { 
    id: 'companyTeam', 
    name: 'Company & Team', 
    icon: 'heroicons:building-office-2', 
    description: 'Company info and team members',
    subSections: [
      { id: 'company', name: 'Company Info', icon: 'heroicons:building-office', apiSource: 'company', fieldKeys: ['companyLogo', 'companyLogoText', 'companyName', 'companyRegNo', 'companyDescription', 'industry', 'establishedYear', 'employeeCount'] },
      { id: 'team', name: 'Team', icon: 'heroicons:user-group', apiSource: 'company', fieldKeys: ['teamMembers'] },
    ]
  },
  { id: 'services', name: 'Services', icon: 'heroicons:rocket-launch', description: 'Services and expertise', apiSource: 'services', fieldKeys: ['serviceName', 'serviceDescription', 'serviceImage', 'serviceFeatures', 'servicePrice', 'serviceOldPrice', 'serviceDuration', 'serviceTags', 'serviceBrochure', 'bookingUrl'] },
  { id: 'portfolio', name: 'Portfolio', icon: 'heroicons:folder', description: 'Projects and work samples', apiSource: 'portfolio', fieldKeys: ['portfolioTitle', 'portfolioDescription', 'portfolioCoverImage', 'portfolioGallery', 'projectUrl', 'dateCompleted', 'clientName', 'location', 'skillsUsed', 'pdfDownload'] },
  { id: 'blog', name: 'Blog', icon: 'heroicons:newspaper', description: 'Blog posts and articles', apiSource: 'blog', fieldKeys: ['blogTitle', 'blogCoverImage', 'blogCategory', 'blogTags', 'authorName', 'readingTime', 'blogContent', 'externalLink'] },
  { id: 'contact', name: 'Contact', icon: 'heroicons:phone', description: 'Phone, email, WhatsApp', apiSource: 'links', fieldKeys: ['phone', 'whatsapp'] },
  { id: 'location', name: 'Location', icon: 'heroicons:map-pin', description: 'Address and map', apiSource: 'company', fieldKeys: ['address1', 'address2', 'address3', 'mapUrl'] },
  { id: 'social', name: 'Social Media', icon: 'heroicons:share', description: 'Social media links', apiSource: 'links', fieldKeys: ['socialLinks'] },
  { id: 'gallery', name: 'Gallery', icon: 'heroicons:photo', description: 'Image gallery', apiSource: 'portfolio', fieldKeys: ['portfolioGallery'] },
];

// Map API source to fields ref
const getFieldsForApiSource = (apiSource) => {
  const sourceMap = {
    'profile': profileFields.value,
    'company': companyFields.value,
    'services': servicesFields.value,
    'links': linksFields.value,
    'portfolio': portfolioFields.value,
    'blog': blogFields.value,
  };
  return sourceMap[apiSource] || [];
};

// Computed: Sections for Layout Designer (granular landing page sections with fields)
// Combined sections now pass actual data fields (bio, stats, etc.) not sub-section IDs
const layoutDesignerSections = computed(() => {
  return landingPageSectionsConfig.map(section => {
    // Handle combined sections with subSections - collect all fieldKeys from sub-sections
    if (section.subSections) {
      // Collect all fields from all sub-sections
      const allFields = [];
      section.subSections.forEach(sub => {
        (sub.fieldKeys || []).forEach(fieldKey => {
          allFields.push({
            field_key: fieldKey,
            label: formatFieldLabel(fieldKey),
            field_type: 'text',
            enabled: true,
            subSection: sub.id // Track which sub-section this field belongs to
          });
        });
      });
      
      return {
        id: section.id,
        name: section.name,
        icon: section.icon,
        description: section.description,
        fields: allFields,
        subSections: section.subSections, // Keep sub-section info for grouping in UI
        isComposite: true
      };
    }
    
    // Regular sections: Get fields from API source and filter by fieldKeys
    const apiFields = getFieldsForApiSource(section.apiSource);
    const sectionFields = apiFields.filter(f => 
      section.fieldKeys?.includes(f.field_key)
    );
    
    return {
      id: section.id,
      name: section.name,
      icon: section.icon,
      description: section.description,
      fields: sectionFields.length > 0 ? sectionFields : (section.fieldKeys || []).map(fk => ({
        field_key: fk,
        label: formatFieldLabel(fk),
        field_type: 'text',
        enabled: true
      }))
    };
  });
});

// Helper: Format field key to human-readable label
const formatFieldLabel = (fieldKey) => {
  if (!fieldKey) return '';
  return fieldKey
    .replace(/([A-Z])/g, ' $1')
    .replace(/^./, str => str.toUpperCase())
    .trim();
};

// Update section layout from LayoutDesigner
const updateSectionLayout = (newLayout) => {
  profileData.sectionLayout = newLayout;
  console.log('📐 Section layout updated:', newLayout);
  // Watch will auto-trigger updatePreviewData with debounce
};

// Update field layout from LayoutDesigner
const updateFieldLayout = (newLayout) => {
  profileData.fieldLayout = newLayout;
  console.log('📝 Field layout updated:', newLayout);
  // Watch will auto-trigger updatePreviewData with debounce
};

// Update section settings from LayoutDesigner
const updateSectionSettings = (newSettings) => {
  profileData.sectionSettings = newSettings;
  console.log('⚙️ Section settings updated:', newSettings);
  // Watch will auto-trigger updatePreviewData with debounce
};

// Business Team Members (for auto-loading employees)
const businessEmployees = ref([]);
const loadingTeamMembers = ref(false);

// Load business team members from API
const loadBusinessTeamMembers = async () => {
  try {
    loadingTeamMembers.value = true;
    
    const response = await fetch(`${config.public.apiBaseUrl}/business/team-members`, {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
    });
    
    const data = await response.json();
    
    if (data.success && data.data) {
      businessEmployees.value = data.data;
      console.log('✅ Business team members loaded:', data.data.length);
      
      if (data.data.length === 0) {
        $toast.info('No team members found. Add employees to your business account first.');
      }
    } else if (data.data && Array.isArray(data.data)) {
      // Non-business user but still got empty array
      businessEmployees.value = [];
      $toast.info('No team members available');
    } else {
      console.warn('Team members API response:', data);
      businessEmployees.value = [];
    }
  } catch (error) {
    console.error('Failed to load business team members:', error);
    businessEmployees.value = [];
    $toast.error('Unable to load team members. Please try again.');
  } finally {
    loadingTeamMembers.value = false;
  }
};

// Check if team member is selected
const isTeamMemberSelected = (employee) => {
  return profileData.teamMembers.some(
    member => member.nfc_card_id === employee.nfc_card_id
  );
};

// Toggle team member selection
const toggleTeamMember = (employee) => {
  const index = profileData.teamMembers.findIndex(
    member => member.nfc_card_id === employee.nfc_card_id
  );
  
  if (index >= 0) {
    // Remove if already selected
    profileData.teamMembers.splice(index, 1);
  } else {
    // Add to team members with landing page URL
    profileData.teamMembers.push({
      name: employee.name,
      role: employee.role,
      initials: employee.initials,
      profile_image: employee.profile_image,
      nfc_card_id: employee.nfc_card_id,
      landing_page_url: employee.landing_page_url,
      is_admin: employee.is_admin,
    });
  }
};

// Remove team member by index
const removeTeamMember = (index) => {
  profileData.teamMembers.splice(index, 1);
};

// Remove team member by name
const removeTeamMemberByName = (name) => {
  const index = profileData.teamMembers.findIndex(m => m.name === name);
  if (index >= 0) {
    profileData.teamMembers.splice(index, 1);
  }
};

// Computed: Valid team members (with name)
const validTeamMembers = computed(() => {
  return profileData.teamMembers.filter(m => m.name && m.name.trim() !== '');
});

// Switch category and auto-select first tab
const switchToCategory = (category) => {
  // Check if the category to switch to has available options
  if (category === 'general' && availableGeneralTabs.value.length === 0) {
    console.log('No available options for category:', category);
    return; // Don't switch if there are no available options
  }
  
  if (category === 'design' && availableDesignTabs.value.length === 0) {
    console.log('No available options for category:', category);
    return; // Don't switch if there are no available options
  }
  
  mainCategory.value = category;
  
  // Auto-select first tab in the category
  if (category === 'general') {
    const firstTab = availableGeneralTabs.value[0];
    if (firstTab) activeTab.value = firstTab.id;
  } else if (category === 'design') {
    const firstTab = availableDesignTabs.value[0];
    if (firstTab) activeTab.value = firstTab.id;
  }
};

// Watch activeTab and auto-switch mainCategory
watch(activeTab, (newTab) => {
  const tab = allTabs.value.find(t => t.id === newTab);
  if (tab && tab.category !== mainCategory.value) {
    mainCategory.value = tab.category;
  }
});

// Load user permissions from API
const loadUserPermissions = async () => {
  try {
    const userPlan = authStore.user?.subscription_plan || 'business';
    console.log('🔍 Loading user permissions for plan:', userPlan);
    
    const response = await fetch(`${config.public.apiBaseUrl}/profile-design-options?plan=${userPlan}`, {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
    });

    if (!response.ok) throw new Error('Failed to load user permissions');
    const data = await response.json();
    
    console.log('📋 Permissions API Response:', data);
    
    // Load tab_control configuration to get available tabs/permissions
    if (data.data.tab_control && data.data.tab_control.length > 0) {
      const tabConfig = data.data.tab_control[0];
      console.log('✅ Tab Control Config found:', tabConfig);
      
      if (tabConfig.config?.tabs && Array.isArray(tabConfig.config.tabs)) {
        // Update user permissions based on available tabs
        userPermissions.value = tabConfig.config.tabs;
        console.log('✅ User permissions loaded:', userPermissions.value);
      }
    } else {
      console.log('ℹ️ No tab control config found, using default permissions');
    }
  } catch (error) {
    console.error('❌ Error loading user permissions:', error);
    // Keep default permissions as fallback
  }
};

// Load tabs configuration from API
const loadTabsConfig = async () => {
  try {
    const userPlan = authStore.user?.subscription_plan || 'business';
    console.log('🔍 Loading tabs config for plan:', userPlan);
    
    const response = await fetch(`${config.public.apiBaseUrl}/profile-design-options?plan=${userPlan}`, {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
    });

    if (!response.ok) throw new Error('Failed to load tabs config');
    const data = await response.json();
    
    console.log('📋 Tabs API Response:', data);
    
    // Load tab_control configuration
    if (data.data.tab_control && data.data.tab_control.length > 0) {
      const tabConfig = data.data.tab_control[0]; // Get the active config
      console.log('✅ Tab Control Config found:', tabConfig);
      
      if (tabConfig.config?.tabs && Array.isArray(tabConfig.config.tabs)) {
        // Map tab IDs to full tab objects with icons
        const iconMap = {
          profile: 'heroicons:user',
          company: 'heroicons:building-office',
          services: 'heroicons:rocket-launch',
          links: 'heroicons:link',
          design: 'heroicons:paint-brush',
          style: 'heroicons:sparkles',
          watermarks: 'heroicons:eye',
        };
        
        tabs.value = tabConfig.config.tabs.map(tabId => ({
          id: tabId,
          name: tabId.charAt(0).toUpperCase() + tabId.slice(1),
          shortName: tabId.charAt(0).toUpperCase() + tabId.slice(1),
          icon: iconMap[tabId] || 'heroicons:document',
        }));
        
        console.log('✅ Tabs loaded:', tabs.value.map(t => t.id).join(', '));
      }
    } else {
      console.log('ℹ️ No tab control config found, using default tabs');
    }
  } catch (error) {
    console.error('❌ Error loading tabs config:', error);
    // Keep default tabs as fallback
  }
};

// Load fields configuration from API
const loadFieldsConfig = async () => {
  try {
    const userPlan = authStore.user?.subscription_plan || 'business';
    console.log('🔍 Loading fields config for plan:', userPlan);
    
    const response = await fetch(`${config.public.apiBaseUrl}/profile-builder-fields?plan=${userPlan}`, {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
    });

    console.log('📡 Fields API Response Status:', response.status, response.statusText);

    if (!response.ok) {
      const errorText = await response.text();
      console.error('❌ Fields API Error:', {
        status: response.status,
        statusText: response.statusText,
        error: errorText
      });
      return; // Silently fail if API not available
    }
    
    const data = await response.json();
    console.log('📦 Fields API Response Data:', data);
    
    if (data.success && data.data) {
      // Store fields by tab
      profileFields.value = data.data.profile || [];
      companyFields.value = data.data.company || [];
      servicesFields.value = data.data.services || [];
      linksFields.value = data.data.links || [];
      portfolioFields.value = data.data.portfolio || [];
      blogFields.value = data.data.blog || [];
      
      console.log('✅ Fields loaded for plan', userPlan, ':', {
        profile: profileFields.value.length,
        company: companyFields.value.length,
        services: servicesFields.value.length,
        links: linksFields.value.length,
        portfolio: portfolioFields.value.length,
        blog: blogFields.value.length,
      });
    }
  } catch (error) {
    console.log('⚠️ Fields config not available:', error.message);
    // Continue without custom fields - this is OK
  }
};

// Load design options from API
const loadDesignOptions = async () => {
  try {
    const userPlan = authStore.user?.subscription_plan || 'business';
    console.log('🎨 Loading design options for plan:', userPlan);
    
    const response = await fetch(`${config.public.apiBaseUrl}/profile-design-options?plan=${userPlan}`, {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
    });

    if (!response.ok) throw new Error('Failed to load design options');
    const data = await response.json();
    
    console.log('📋 Design Options API Response:', data.data);
    
    // Load themes — MERGE with DESIGN_LIBRARY to ensure full preview data (no blank previews even from old DB)
    if (data.data.theme && data.data.theme.length > 0) {
      const dbThemes = data.data.theme.map(opt => ({
        id: opt.option_id,
        name: opt.name,
        previewCss: opt.config?.previewCss,
        backgroundColor: opt.config?.backgroundColor || '#FFFFFF',
        textColor: opt.config?.textColor,
        accentColor: opt.config?.accentColor,
        bgGradient: opt.config?.bgGradient,
        _previewClass: opt.config?.preview,
      }));
      themes.value = DESIGN_LIBRARY.themes.map(lib => {
        const hit = dbThemes.find(d => d.id === lib.id);
        return hit ? { ...lib, ...hit, previewCss: hit.previewCss || lib.previewCss } : lib;
      });
      // Append extra DB themes not in library
      dbThemes.filter(d => !DESIGN_LIBRARY.themes.some(l => l.id === d.id)).forEach(extra => themes.value.push(extra));
      console.log('✅ Themes loaded (merged):', themes.value.length);
    } else {
      themes.value = [...DESIGN_LIBRARY.themes];
    }
    
    // Load fonts — merge with DESIGN_LIBRARY
    if (data.data.font && data.data.font.length > 0) {
      const dbFonts = data.data.font.map(opt => ({
        id: opt.option_id,
        name: opt.name,
        family: opt.config?.family || opt.name + ', sans-serif',
      }));
      fonts.value = DESIGN_LIBRARY.fonts.map(lib => {
        const hit = dbFonts.find(d => d.id === lib.id);
        return hit ? { ...lib, ...hit } : lib;
      });
      dbFonts.filter(d => !DESIGN_LIBRARY.fonts.some(l => l.id === d.id)).forEach(extra => fonts.value.push(extra));
    } else {
      fonts.value = [...DESIGN_LIBRARY.fonts];
    }

    // Load button styles — merge with DESIGN_LIBRARY
    if (data.data.button_style && data.data.button_style.length > 0) {
      const dbBtns = data.data.button_style.map(opt => ({
        id: opt.option_id,
        name: opt.name,
        class: opt.config?.class || '',
      }));
      buttonStyles.value = DESIGN_LIBRARY.buttonStyles.map(lib => {
        const hit = dbBtns.find(d => d.id === lib.id);
        return hit ? { ...lib, ...hit } : lib;
      });
      dbBtns.filter(d => !DESIGN_LIBRARY.buttonStyles.some(l => l.id === d.id)).forEach(extra => buttonStyles.value.push(extra));
    } else {
      buttonStyles.value = [...DESIGN_LIBRARY.buttonStyles];
    }

    // Load card styles — merge with DESIGN_LIBRARY
    if (data.data.card_style && data.data.card_style.length > 0) {
      const dbCards = data.data.card_style.map(opt => ({
        id: opt.option_id,
        name: opt.name,
        previewCss: opt.config?.previewCss,
        class: opt.config?.class || '',
      }));
      cardStyles.value = DESIGN_LIBRARY.cardStyles.map(lib => {
        const hit = dbCards.find(d => d.id === lib.id);
        return hit ? { ...lib, ...hit, previewCss: hit.previewCss || lib.previewCss } : lib;
      });
      dbCards.filter(d => !DESIGN_LIBRARY.cardStyles.some(l => l.id === d.id)).forEach(extra => cardStyles.value.push(extra));
    } else {
      cardStyles.value = [...DESIGN_LIBRARY.cardStyles];
    }

    // Load background animations — merge with DESIGN_LIBRARY
    if (data.data.bg_animation && data.data.bg_animation.length > 0) {
      const dbAnim = data.data.bg_animation.map(opt => ({
        id: opt.option_id,
        name: opt.name,
        previewCss: opt.config?.previewCss,
        component: opt.config?.component || null,
      }));
      bgAnimations.value = DESIGN_LIBRARY.bgAnimations.map(lib => {
        const hit = dbAnim.find(d => d.id === lib.id);
        return hit ? { ...lib, ...hit, previewCss: hit.previewCss || lib.previewCss } : lib;
      });
      dbAnim.filter(d => !DESIGN_LIBRARY.bgAnimations.some(l => l.id === d.id)).forEach(extra => bgAnimations.value.push(extra));
    } else {
      bgAnimations.value = [...DESIGN_LIBRARY.bgAnimations];
    }
    console.log('✅ Fonts loaded:', fonts.value.length);
    console.log('✅ Button styles loaded:', buttonStyles.value.length);
    console.log('✅ Card styles loaded:', cardStyles.value.length);
    console.log('✅ Background animations loaded:', bgAnimations.value.length);
    
    // Load color schemes
    if (data.data.color_scheme && data.data.color_scheme.length > 0) {
      colorSchemes.value = data.data.color_scheme.map(opt => ({
        id: opt.option_id,
        name: opt.name,
        colors: opt.config || {},
      }));
      console.log('✅ Color schemes loaded:', colorSchemes.value.length);
    }
    
    // Load layouts
    if (data.data.layout && data.data.layout.length > 0) {
      layouts.value = data.data.layout.map(opt => ({
        id: opt.option_id,
        name: opt.name,
        icon: opt.config?.icon || 'heroicons:squares-2x2',
        config: opt.config || {},
      }));
      console.log('✅ Layouts loaded:', layouts.value.length);
    }
    
    // Load feature toggles (filter out deprecated features)
    // Completely remove these three features: layout_designer, click_tracking, analytics
    const excludedFeatures = ['layout_designer', 'click_tracking', 'analytics'];
    console.log('📋 Raw feature_toggle from API:', data.data.feature_toggle);
    if (data.data.feature_toggle && data.data.feature_toggle.length > 0) {
      featureToggles.value = data.data.feature_toggle
        .filter(opt => !excludedFeatures.includes(opt.option_id))
        .map(opt => ({
          id: opt.option_id,
          name: opt.name,
          feature_key: opt.option_id, // Use option_id as feature_key
          enabled: true, // Default enabled
          description: opt.description || '',
          available_plans: opt.available_plans || [], // Include for debugging
        }));
      console.log('✅ Feature toggles loaded:', featureToggles.value.length);
      console.log('📋 Features available for this user:', featureToggles.value.map(f => f.feature_key));
    } else {
      console.log('⚠️ No feature toggles received from API - Admin may not have assigned any features');
    }
    
    console.log('🎨 All design options loaded successfully');
  } catch (error) {
    console.error('❌ Error loading design options:', error);
    // Fallback to DESIGN_LIBRARY defaults if API fails
    themes.value = [...DESIGN_LIBRARY.themes];
    fonts.value = [...DESIGN_LIBRARY.fonts];
    buttonStyles.value = [...DESIGN_LIBRARY.buttonStyles];
    cardStyles.value = [...DESIGN_LIBRARY.cardStyles];
    bgAnimations.value = [...DESIGN_LIBRARY.bgAnimations];
  }
};

// Smart host detection helper (same logic as api.client.js)
const _isLocalHostname = (h) =>
  h === "localhost" ||
  h === "127.0.0.1" ||
  h === "0.0.0.0" ||
  h.startsWith("10.") ||
  h.startsWith("192.168.") ||
  /^172\.(1[6-9]|2[0-9]|3[01])\./.test(h);

// Helper function to get full image URL with smart host detection
const getImageUrl = (imagePath) => {
  if (!imagePath) return null;

  // If already a full URL, return as is
  if (imagePath.startsWith("http://") || imagePath.startsWith("https://")) {
    return imagePath;
  }

  // Smartly pick base URL based on current runtime host so local dev
  // always hits the local Laravel server (port 8000) for images, not production.
  let apiBase = (config.public?.apiBaseUrl || "").trim();
  if (typeof window !== "undefined" && window.location && window.location.hostname) {
    const host = window.location.hostname;
    if (_isLocalHostname(host)) {
      const scheme = window.location.protocol === "https:" ? "https" : "http";
      apiBase = `${scheme}://${host}:8000/api`;
    }
  }
  if (!apiBase) {
    apiBase = "http://localhost:8000/api";
  }

  // Remove /api from the end to get base URL
  const baseUrl = apiBase.replace(/\/api\/?$/, "");

  // Ensure the path starts with /
  const path = imagePath.startsWith("/") ? imagePath : `/${imagePath}`;

  return `${baseUrl}${path}`;
};

// Helper functions for dynamic field rendering
const shouldShowField = (fieldKey) => {
  // Include fields from all static arrays AND dynamic sections
  const staticFields = [...profileFields.value, ...companyFields.value, ...servicesFields.value, ...linksFields.value, ...portfolioFields.value, ...blogFields.value];
  
  // Also include fields from dynamic sections
  const dynamicFields = sections.value
    .filter(s => s.fields && s.fields.length > 0)
    .flatMap(s => s.fields);
  
  const allFields = [...staticFields, ...dynamicFields];
  const field = allFields.find(f => f.field_key === fieldKey);
  
  if (!field || !field.is_visible) return false;
  
  const userPlan = authStore.user?.subscription_plan || 'business';
  
  // If no plan restrictions, show to all
  if (!field.available_plans || field.available_plans.length === 0) return true;
  
  // Check if user's plan is in available plans
  return field.available_plans.includes(userPlan);
};

const getFieldConfig = (fieldKey) => {
  // Include fields from static arrays AND dynamic sections
  const staticFields = [...profileFields.value, ...companyFields.value, ...servicesFields.value, ...linksFields.value, ...portfolioFields.value, ...blogFields.value];
  const dynamicFields = sections.value
    .filter(s => s.fields && s.fields.length > 0)
    .flatMap(s => s.fields);
  const allFields = [...staticFields, ...dynamicFields];
  return allFields.find(f => f.field_key === fieldKey);
};

// Get visible fields for each tab (fully dynamic - controlled by Admin backend)
const getVisibleFields = (fields) => {
  return fields.filter(field => 
    field.field_type !== 'image' && shouldShowField(field.field_key)
  );
};

// Group fields by field_group from backend (fully dynamic)
const getGroupedFields = (fields) => {
  const visible = fields.filter(field => 
    field.field_type !== 'image' && shouldShowField(field.field_key)
  );
  
  // Group by field_group, sorted by group_order
  const groups = {};
  visible.forEach(field => {
    const groupName = field.field_group || 'Other';
    if (!groups[groupName]) {
      groups[groupName] = {
        name: groupName,
        icon: field.field_group_icon || 'heroicons:squares-plus',
        order: field.group_order || 99,
        fields: []
      };
    }
    groups[groupName].fields.push(field);
  });
  
  // Sort groups by order and return as array, filter out empty groups
  return Object.values(groups)
    .filter(group => group.fields.length > 0)
    .sort((a, b) => a.order - b.order);
};

// Fields to exclude from dynamic company rendering (replaced by simplified 3-line address section)
const COMPANY_ADDRESS_EXCLUDED_FIELDS = [
  'addressName', 'addressStreet', 'addressArea', 'addressCityState',
  'addressCountry', 'postalCode', 'mapUrl', 'coordinates',
  'address1', 'address2', 'address3',
];

const getGroupedProfileFields = () => getGroupedFields(profileFields.value);
const getGroupedCompanyFields = () => {
  const filtered = companyFields.value.filter(f => !COMPANY_ADDRESS_EXCLUDED_FIELDS.includes(f.field_key));
  return getGroupedFields(filtered);
};
const getGroupedServicesFields = () => getGroupedFields(servicesFields.value);
const getGroupedLinksFields = () => getGroupedFields(linksFields.value);
const getGroupedPortfolioFields = () => getGroupedFields(portfolioFields.value);
const getGroupedBlogFields = () => getGroupedFields(blogFields.value);

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
  if (theme.textColor) profileData.textColor = theme.textColor;
  if (theme.accentColor) profileData.accentColor = theme.accentColor;
  if (theme.bgGradient) profileData.bgGradient = theme.bgGradient;
};

// Get icon for feature toggle based on feature_key
const getFeatureIcon = (featureKey) => {
  const iconMap = {
    contact_form: 'heroicons:envelope',
    vcard_download: 'heroicons:document-arrow-down',
    qr_code: 'heroicons:qr-code',
    remove_branding: 'heroicons:eye-slash',
    booking_integration: 'heroicons:calendar',
    show_watermark: 'heroicons:photo',
    social_sharing: 'heroicons:share',
  };
  return iconMap[featureKey] || 'heroicons:cog-6-tooth';
};

const getCurrentFont = () => {
  const font = fonts.value.find((f) => f.id === profileData.font);
  return font ? font.family : "Inter, sans-serif";
};

// Feature toggle helpers
// getFeatureValue must be defined before toggleFeature uses it
const getFeatureValue = (featureKey) => {
  // For backward compatibility with showWatermark
  if (featureKey === 'watermark' || featureKey === 'show_watermark') {
    return profileData.showWatermark !== undefined 
      ? profileData.showWatermark 
      : (profileData.features?.[featureKey] ?? true);
  }
  
  // Check if feature has explicit value
  if (profileData.features?.hasOwnProperty(featureKey)) {
    return profileData.features[featureKey];
  }
  
  // Default values: all ON except remove_branding
  const defaultValues = {
    contact_form: true,
    vcard_download: true,
    qr_code: true,
    remove_branding: false,
    booking_integration: true,
    social_sharing: true,
  };
  return defaultValues[featureKey] ?? true;
};

const toggleFeature = (featureKey) => {
  if (!profileData.features) {
    profileData.features = {};
  }
  
  // Get current value (use default if not set)
  const currentValue = getFeatureValue(featureKey);
  profileData.features[featureKey] = !currentValue;
  
  // For backward compatibility with showWatermark
  if (featureKey === 'watermark' || featureKey === 'show_watermark') {
    profileData.showWatermark = profileData.features[featureKey];
  }
  
  // Update preview to reflect feature change
  updatePreviewData();
  console.log(`🔄 Feature "${featureKey}" toggled to:`, profileData.features[featureKey]);
};

const getFontName = (fontId) => {
  const font = fonts.value.find((f) => f.id === fontId);
  return font ? font.name : fontId;
};

const getButtonClass = () => {
  const style = buttonStyles.value.find((s) => s.id === profileData.buttonStyle);
  return `py-2.5 sm:py-3 px-5 sm:px-6 ${
    style ? style.class : (buttonStyles.value[0]?.class || 'bg-black text-white rounded-full')
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

// ============ SERVICE FUNCTIONS ============
const editingServiceIndex = ref(null);

const addService = async () => {
  if (!profileData.serviceName?.trim()) {
    $toast.error("Please enter a service name");
    return;
  }
  
  const serviceData = {
    id: editingServiceIndex.value !== null ? profileData.services[editingServiceIndex.value]?.id : Date.now(),
    name: profileData.serviceName,
    icon: profileData.serviceIcon || "🚀",
    category: profileData.serviceCategory,
    image: profileData.serviceImage,
    video: profileData.serviceVideo,
    description: profileData.serviceDescription,
    price: profileData.servicePrice,
    old_price: profileData.serviceOldPrice,
    duration: profileData.serviceDuration,
    features: profileData.serviceFeatures || [],
    tags: profileData.serviceTags || [],
    brochure: profileData.serviceBrochure,
    gallery: profileData.gallery || [],
    created_at: new Date().toISOString(),
  };
  
  if (!profileData.services) profileData.services = [];
  
  if (editingServiceIndex.value !== null) {
    // Update existing service
    profileData.services[editingServiceIndex.value] = serviceData;
    $toast.success("Service updated!");
  } else {
    // Add new service
    profileData.services.push(serviceData);
    $toast.success("Service added!");
  }
  clearServiceForm();
  // Auto-save to backend
  await saveProfile();
};

const removeService = async (index) => {
  if (editingServiceIndex.value === index) {
    clearServiceForm();
  }
  profileData.services.splice(index, 1);
  $toast.success("Service removed");
  // Auto-save to backend
  await saveProfile();
};

const editService = (index) => {
  const service = profileData.services[index];
  if (service) {
    editingServiceIndex.value = index;
    profileData.serviceName = service.name || "";
    profileData.serviceIcon = service.icon || "🚀";
    profileData.serviceCategory = service.category || "";
    profileData.serviceImage = service.image || null;
    profileData.serviceVideo = service.video || "";
    profileData.serviceDescription = service.description || "";
    profileData.servicePrice = service.price || "";
    profileData.serviceOldPrice = service.old_price || "";
    profileData.serviceDuration = service.duration || "";
    profileData.serviceFeatures = service.features || [];
    profileData.serviceTags = service.tags || [];
    profileData.serviceBrochure = service.brochure || "";
    profileData.gallery = service.gallery || [];
    $toast.info("Editing: " + service.name);
  }
};

const clearServiceForm = () => {
  editingServiceIndex.value = null;
  profileData.serviceName = "";
  profileData.serviceIcon = "🚀";
  profileData.serviceCategory = "";
  profileData.serviceImage = null;
  profileData.serviceVideo = "";
  profileData.serviceDescription = "";
  profileData.servicePrice = "";
  profileData.serviceOldPrice = "";
  profileData.serviceDuration = "";
  profileData.serviceFeatures = [];
  profileData.serviceTags = [];
  profileData.serviceBrochure = "";
  profileData.gallery = [];
};

// ============ PORTFOLIO PROJECT FUNCTIONS ============
const editingProjectIndex = ref(null);

const addProject = async () => {
  if (!profileData.portfolioTitle?.trim()) {
    $toast.error("Please enter a project title");
    return;
  }
  
  const projectData = {
    id: editingProjectIndex.value !== null ? profileData.projects[editingProjectIndex.value]?.id : Date.now(),
    title: profileData.portfolioTitle,
    description: profileData.portfolioDescription,
    category: profileData.portfolioCategory,
    tags: profileData.portfolioTags || [],
    cover_image: profileData.portfolioCoverImage,
    gallery: profileData.portfolioGallery || [],
    url: profileData.projectUrl,
    date_completed: profileData.dateCompleted,
    client_name: profileData.clientName,
    location: profileData.location,
    skills_used: profileData.skillsUsed || [],
    pdf_download: profileData.pdfDownload,
    created_at: new Date().toISOString(),
  };
  
  if (editingProjectIndex.value !== null) {
    profileData.projects[editingProjectIndex.value] = projectData;
    $toast.success("Project updated!");
  } else {
    profileData.projects.push(projectData);
    $toast.success("Project added!");
  }
  clearProjectForm();
  // Auto-save to backend
  await saveProfile();
};

const removeProject = async (index) => {
  if (editingProjectIndex.value === index) {
    clearProjectForm();
  }
  profileData.projects.splice(index, 1);
  $toast.success("Project removed");
  // Auto-save to backend
  await saveProfile();
};

const editProject = (index) => {
  const project = profileData.projects[index];
  if (project) {
    editingProjectIndex.value = index;
    profileData.portfolioTitle = project.title || "";
    profileData.portfolioDescription = project.description || "";
    profileData.portfolioCategory = project.category || "";
    profileData.portfolioTags = project.tags || [];
    profileData.portfolioCoverImage = project.cover_image || null;
    profileData.portfolioGallery = project.gallery || [];
    profileData.projectUrl = project.url || "";
    profileData.dateCompleted = project.date_completed || "";
    profileData.clientName = project.client_name || "";
    profileData.location = project.location || "";
    profileData.skillsUsed = project.skills_used || [];
    profileData.pdfDownload = project.pdf_download || null;
    $toast.info("Editing: " + project.title);
  }
};

const clearProjectForm = () => {
  editingProjectIndex.value = null;
  profileData.portfolioTitle = "";
  profileData.portfolioDescription = "";
  profileData.portfolioCategory = "";
  profileData.portfolioTags = [];
  profileData.portfolioCoverImage = null;
  profileData.portfolioGallery = [];
  profileData.projectUrl = "";
  profileData.dateCompleted = "";
  profileData.clientName = "";
  profileData.location = "";
  profileData.skillsUsed = [];
  profileData.pdfDownload = null;
};

// ============ BLOG POST FUNCTIONS ============
const editingBlogPostIndex = ref(null);

const addBlogPost = async () => {
  if (!profileData.blogTitle?.trim()) {
    $toast.error("Please enter a blog title");
    return;
  }
  
  const postData = {
    id: editingBlogPostIndex.value !== null ? profileData.blogPosts[editingBlogPostIndex.value]?.id : Date.now(),
    title: profileData.blogTitle,
    slug: profileData.blogSlug || profileData.blogTitle.toLowerCase().replace(/\s+/g, '-'),
    cover_image: profileData.blogCoverImage,
    category: profileData.blogCategory,
    tags: profileData.blogTags || [],
    author_name: profileData.authorName,
    published_date: profileData.publishedDate,
    reading_time: profileData.readingTime,
    content: profileData.blogContent,
    gallery: profileData.blogGallery || [],
    external_link: profileData.externalLink,
    created_at: new Date().toISOString(),
  };
  
  if (editingBlogPostIndex.value !== null) {
    profileData.blogPosts[editingBlogPostIndex.value] = postData;
    $toast.success("Blog post updated!");
  } else {
    profileData.blogPosts.push(postData);
    $toast.success("Blog post added!");
  }
  clearBlogForm();
  // Auto-save to backend
  await saveProfile();
};

const removeBlogPost = async (index) => {
  if (editingBlogPostIndex.value === index) {
    clearBlogForm();
  }
  profileData.blogPosts.splice(index, 1);
  $toast.success("Blog post removed");
  // Auto-save to backend
  await saveProfile();
};

const editBlogPost = (index) => {
  const post = profileData.blogPosts[index];
  if (post) {
    editingBlogPostIndex.value = index;
    profileData.blogTitle = post.title || "";
    profileData.blogSlug = post.slug || "";
    profileData.blogCoverImage = post.cover_image || null;
    profileData.blogCategory = post.category || "";
    profileData.blogTags = post.tags || [];
    profileData.authorName = post.author_name || "";
    profileData.publishedDate = post.published_date || "";
    profileData.readingTime = post.reading_time || "";
    profileData.blogContent = post.content || "";
    profileData.blogGallery = post.gallery || [];
    profileData.externalLink = post.external_link || "";
    $toast.info("Editing: " + post.title);
  }
};

const clearBlogForm = () => {
  editingBlogPostIndex.value = null;
  profileData.blogTitle = "";
  profileData.blogSlug = "";
  profileData.blogCoverImage = null;
  profileData.blogCategory = "";
  profileData.blogTags = [];
  profileData.authorName = "";
  profileData.publishedDate = "";
  profileData.readingTime = "";
  profileData.blogContent = "";
  profileData.blogGallery = [];
  profileData.externalLink = "";
};

const saveProfile = async () => {
  if (!selectedNfcCardId.value) {
    $toast.error("Please select an NFC card first");
    return;
  }

  saving.value = true;
  try {
    // 0. Auto-add pending form data before saving
    // Services: if there's a pending service name, add it
    if (profileData.serviceName?.trim()) {
      const serviceData = {
        id: editingServiceIndex.value !== null ? profileData.services[editingServiceIndex.value]?.id : Date.now(),
        name: profileData.serviceName,
        icon: profileData.serviceIcon || "🚀",
        category: profileData.serviceCategory,
        image: profileData.serviceImage,
        video: profileData.serviceVideo,
        description: profileData.serviceDescription,
        price: profileData.servicePrice,
        old_price: profileData.serviceOldPrice,
        duration: profileData.serviceDuration,
        features: profileData.serviceFeatures || [],
        tags: profileData.serviceTags || [],
        brochure: profileData.serviceBrochure,
        gallery: profileData.gallery || [],
        created_at: new Date().toISOString(),
      };
      if (!profileData.services) profileData.services = [];
      if (editingServiceIndex.value !== null) {
        profileData.services[editingServiceIndex.value] = serviceData;
      } else {
        profileData.services.push(serviceData);
      }
      clearServiceForm();
    }

    // Portfolio: if there's a pending project title, add it
    if (profileData.portfolioTitle?.trim()) {
      const projectData = {
        id: editingProjectIndex.value !== null ? profileData.projects[editingProjectIndex.value]?.id : Date.now(),
        title: profileData.portfolioTitle,
        description: profileData.portfolioDescription,
        category: profileData.portfolioCategory,
        tags: profileData.portfolioTags || [],
        cover_image: profileData.portfolioCoverImage,
        gallery: profileData.portfolioGallery || [],
        url: profileData.projectUrl,
        date_completed: profileData.dateCompleted,
        client_name: profileData.clientName,
        location: profileData.location,
        skills_used: profileData.skillsUsed || [],
        pdf_download: profileData.pdfDownload,
        created_at: new Date().toISOString(),
      };
      if (!profileData.projects) profileData.projects = [];
      if (editingProjectIndex.value !== null) {
        profileData.projects[editingProjectIndex.value] = projectData;
      } else {
        profileData.projects.push(projectData);
      }
      clearProjectForm();
    }

    // Blog: if there's a pending blog title, add it
    if (profileData.blogTitle?.trim()) {
      const postData = {
        id: editingBlogPostIndex.value !== null ? profileData.blogPosts[editingBlogPostIndex.value]?.id : Date.now(),
        title: profileData.blogTitle,
        slug: profileData.blogSlug || profileData.blogTitle.toLowerCase().replace(/\s+/g, '-'),
        cover_image: profileData.blogCoverImage,
        category: profileData.blogCategory,
        tags: profileData.blogTags || [],
        author_name: profileData.authorName,
        published_date: profileData.publishedDate,
        reading_time: profileData.readingTime,
        content: profileData.blogContent,
        gallery: profileData.blogGallery || [],
        external_link: profileData.externalLink,
        created_at: new Date().toISOString(),
      };
      if (!profileData.blogPosts) profileData.blogPosts = [];
      if (editingBlogPostIndex.value !== null) {
        profileData.blogPosts[editingBlogPostIndex.value] = postData;
      } else {
        profileData.blogPosts.push(postData);
      }
      clearBlogForm();
    }

    // 1. Collect complete design configuration
    const designConfig = {
      theme: themes.value.find(t => t.id === profileData.theme),
      font: fonts.value.find(f => f.id === profileData.font),
      buttonStyle: buttonStyles.value.find(b => b.id === profileData.buttonStyle),
      // profileStyle: always 'classic' (fixed)
      colorScheme: colorSchemes.value.find(c => c.id === profileData.colorScheme),
      layout: layouts.value.find(l => l.id === profileData.layout),
    };

    // 2. Collect list of visible fields
    const visibleFields = [];

    // Profile fields
    profileFields.value.forEach(field => {
      if (shouldShowField(field.field_key)) {
        visibleFields.push({
          tab: 'profile',
          field_key: field.field_key,
          label: field.label,
          field_type: field.field_type,
          config: field.config
        });
      }
    });

    // Company fields
    companyFields.value.forEach(field => {
      if (shouldShowField(field.field_key)) {
        visibleFields.push({
          tab: 'company',
          field_key: field.field_key,
          label: field.label,
          field_type: field.field_type,
          config: field.config
        });
      }
    });

    // Services fields
    servicesFields.value.forEach(field => {
      if (shouldShowField(field.field_key)) {
        visibleFields.push({
          tab: 'services',
          field_key: field.field_key,
          label: field.label,
          field_type: field.field_type,
          config: field.config
        });
      }
    });

    // Links fields
    linksFields.value.forEach(field => {
      if (shouldShowField(field.field_key)) {
        visibleFields.push({
          tab: 'links',
          field_key: field.field_key,
          label: field.label,
          field_type: field.field_type,
          config: field.config
        });
      }
    });

    // Portfolio fields
    portfolioFields.value.forEach(field => {
      if (shouldShowField(field.field_key)) {
        visibleFields.push({
          tab: 'portfolio',
          field_key: field.field_key,
          label: field.label,
          field_type: field.field_type,
          config: field.config
        });
      }
    });

    // Blog fields
    blogFields.value.forEach(field => {
      if (shouldShowField(field.field_key)) {
        visibleFields.push({
          tab: 'blog',
          field_key: field.field_key,
          label: field.label,
          field_type: field.field_type,
          config: field.config
        });
      }
    });

    // Map frontend camelCase to backend snake_case
    const payload = {
      // Basic Info (Profile Tab)
      name: profileData.name,
      title: profileData.position,
      pronouns: profileData.pronouns,
      qualification: profileData.qualification,
      bio: profileData.bio,
      tagline: profileData.tagline,
      phone: profileData.contactNumber,
      email: profileData.emailAddress || profileData.email, // Use emailAddress (new) or email (legacy)
      website: profileData.website,
      address: profileData.address,
      profile_image: profileData.profilePicture || profileData.image, // Use profilePicture (new) or image (legacy)
      cover_banner: profileData.coverBanner,
      company_logo: profileData.companyLogo,
      
      // Education & Certifications
      education: profileData.education,
      certifications: profileData.certifications,

      // Company Info
      company_logo_text: profileData.companyLogoText,
      company_name: profileData.companyName,
      company_registration_no: profileData.companyRegistrationNo,
      company_department: profileData.companyDepartment,
      company_description: profileData.companyDescription,
      company_video: profileData.companyVideo,
      industry: profileData.industry,
      established_year: profileData.establishedYear,
      employee_count: profileData.employeeCount,
      company_whatsapp: profileData.companyWhatsapp,

      // Address Details (new simplified 3-line format)
      address_1: profileData.address1,
      address_2: profileData.address2,
      address_3: profileData.address3,
      // Legacy separate address fields (for backward compatibility)
      address_name: profileData.addressName,
      address_street: profileData.addressStreet,
      address_area: profileData.addressArea,
      address_city_state: profileData.addressCityState,
      address_country: profileData.addressCountry,
      postal_code: profileData.postalCode,
      address_map_url: profileData.mapUrl,
      coordinates: profileData.coordinates,

      // Repeater Fields
      stats: profileData.profileStats || profileData.stats,
      services: profileData.services,
      team_members: validTeamMembers.value, // Only save members with valid names
      education: profileData.education,
      certifications: profileData.certifications,
      expertise: profileData.expertise,
      awards: profileData.awards,
      working_hours: profileData.workingHours,
      service_features: profileData.serviceFeatures,
      service_tags: profileData.serviceTags,
      
      // Services additional
      service_name: profileData.serviceName,
      service_icon: profileData.serviceIcon,
      service_category: profileData.serviceCategory,
      service_image: profileData.serviceImage,
      service_video: profileData.serviceVideo,
      service_description: profileData.serviceDescription,
      service_price: profileData.servicePrice,
      service_old_price: profileData.serviceOldPrice,
      service_duration: profileData.serviceDuration,
      service_brochure: profileData.serviceBrochure,
      booking_enabled: profileData.bookingEnabled,
      booking_url: profileData.bookingUrl,
      gallery: profileData.gallery,

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
      accent_color: profileData.accentColor,
      bg_gradient: profileData.bgGradient,
      font: profileData.font,
      button_style: profileData.buttonStyle,
      card_style: profileData.cardStyle,
      bg_animation: profileData.bgAnimation,
      color_scheme: profileData.colorScheme,
      layout: profileData.layout,
      show_watermark: profileData.showWatermark,
      
      // Features state and order - include dynamic field toggle values from root profileData
      features: (() => {
        const mergedFeatures = { ...(profileData.features || {}) };
        if (profileData.vcardEnabled !== undefined) mergedFeatures.vcard_download = profileData.vcardEnabled;
        if (profileData.vcardEnabled !== undefined) mergedFeatures.vcardEnabled = profileData.vcardEnabled;
        if (profileData.qrCodeEnabled !== undefined) mergedFeatures.qr_code = profileData.qrCodeEnabled;
        if (profileData.qrCodeEnabled !== undefined) mergedFeatures.qrCodeEnabled = profileData.qrCodeEnabled;
        if (profileData.contactFormEnabled !== undefined) mergedFeatures.contact_form = profileData.contactFormEnabled;
        if (profileData.contactFormEnabled !== undefined) mergedFeatures.contactFormEnabled = profileData.contactFormEnabled;
        if (profileData.contactFormEmail) {
          mergedFeatures.contact_form_email = profileData.contactFormEmail;
          mergedFeatures.contactFormEmail = profileData.contactFormEmail;
        }
        if (profileData.openNewTab !== undefined) mergedFeatures.openNewTab = profileData.openNewTab;
        if (profileData.clickTracking !== undefined) mergedFeatures.clickTracking = profileData.clickTracking;
        return mergedFeatures;
      })(),
      available_features: featureToggles.value.map(f => f.feature_key), // Features assigned by admin
      feature_order: featureOrder.value.length > 0 ? featureOrder.value : sortedFeatureToggles.value.map(f => f.id),
      
      // Design & Fields Configuration
      design_config: designConfig,
      visible_fields: visibleFields,
      
      // Layout Configuration
      section_layout: profileData.sectionLayout,
      field_layout: profileData.fieldLayout,
      section_settings: profileData.sectionSettings,
      
      // Portfolio
      portfolio_title: profileData.portfolioTitle,
      portfolio_description: profileData.portfolioDescription,
      portfolio_category: profileData.portfolioCategory,
      portfolio_tags: profileData.portfolioTags,
      portfolio_cover_image: profileData.portfolioCoverImage,
      portfolio_gallery: profileData.portfolioGallery,
      projects: profileData.projects,
      project_url: profileData.projectUrl,
      date_completed: profileData.dateCompleted,
      client_name: profileData.clientName,
      portfolio_location: profileData.location,
      skills_used: profileData.skillsUsed,
      pdf_download: profileData.pdfDownload,
      
      // Blog
      blog_enabled: profileData.blogEnabled,
      blog_posts: profileData.blogPosts,
      blog_gallery: profileData.blogGallery,
      blog_title: profileData.blogTitle,
      blog_slug: profileData.blogSlug,
      blog_cover_image: profileData.blogCoverImage,
      blog_category: profileData.blogCategory,
      blog_tags: profileData.blogTags,
      author_name: profileData.authorName,
      published_date: profileData.publishedDate,
      reading_time: profileData.readingTime,
      blog_content: profileData.blogContent,
      external_link: profileData.externalLink,
      related_posts: profileData.relatedPosts,
      
      // Links additional
      appointment_link: profileData.appointmentLink,
      payment_button_text: profileData.paymentButtonText,
      payment_button_url: profileData.paymentButtonUrl,
      
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

    // Log payload for debugging
    console.log("💾 Saving landing page with payload:", JSON.stringify(payload, null, 2));
    
    // Save the landing page design for the selected NFC card
    const response = await $api.put(
      `/nfc-cards/${selectedNfcCardId.value}/landing-page`,
      payload
    );

    if (response.success) {
      console.log("✅ Landing page saved successfully!");
      console.log("📄 Saved landing page data:", response.landing_page);
      console.log("🔑 Saved for NFC card ID:", selectedNfcCardId.value);
      console.log("🔗 Saved card nfc_card_id:", getSelectedCard()?.nfc_card_id);
      
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
    console.error("Error message:", error.message);
    console.error("Error response:", error.response);
    console.error("Error response data:", error.response?.data);
    console.error("Error status:", error.response?.status);
    console.error("Validation errors:", error.response?.data?.errors);
    
    // More detailed error logging
    if (error.response) {
      console.error("Full error response:", JSON.stringify(error.response.data, null, 2));
    }
    
    if (error.response?.data?.errors) {
      // Show first validation error
      const firstError = Object.values(error.response.data.errors)[0];
      $toast.error(Array.isArray(firstError) ? firstError[0] : firstError);
    } else if (error.response?.data?.message) {
      $toast.error(error.response.data.message);
    } else if (error.message) {
      $toast.error(error.message);
    } else {
      $toast.error("Failed to save landing page. Check console for details.");
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
      accentColor: profileData.accentColor,
      bgGradient: profileData.bgGradient,
      font: profileData.font,
      buttonStyle: profileData.buttonStyle,
      cardStyle: profileData.cardStyle,
      bgAnimation: profileData.bgAnimation,
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
    // Deselect all filtered cards
    const filteredIds = filteredAvailableCards.value.map(card => card.id);
    selectedCardsForDesign.value = selectedCardsForDesign.value.filter(id => !filteredIds.includes(id));
  } else {
    // Select all filtered cards (add to existing selection)
    const filteredIds = filteredAvailableCards.value.map(card => card.id);
    const uniqueIds = new Set([...selectedCardsForDesign.value, ...filteredIds]);
    selectedCardsForDesign.value = Array.from(uniqueIds);
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
      const authoritativePlan = authStore.user?.subscription_plan || null;
      // Filter to get only employee cards (cards where user_id is NOT current user)
      const employeeCardsList = response.nfc_cards.filter(card => {
        return card.user_id !== currentUserId;
      });
      
      // Mark as employee cards and normalize plan to authoritative subscription
      allEmployeeCards.value = employeeCardsList.map(card => {
        const effectivePlan =
          authoritativePlan &&
          (!card.subscription_plan ||
            card.subscription_plan === "free" ||
            card.subscription_plan !== authoritativePlan)
            ? authoritativePlan
            : card.subscription_plan;
        return {
          ...card,
          subscription_plan: effectivePlan,
          is_employee: true
        };
      });
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
      // Profile Picture - set both for compatibility
      profileData.profilePicture =
        landingPage.profile_image || landingPage.image || null;
      profileData.image = profileData.profilePicture; // Keep legacy field in sync
      
      // Cover Banner
      profileData.coverBanner =
        landingPage.cover_banner || landingPage.coverBanner || null;
      
      // Company Logo
      profileData.companyLogo =
        landingPage.company_logo || landingPage.companyLogo || null;
      
      // Additional fields that may have been missed
      profileData.pronouns = landingPage.pronouns || "";
      profileData.tagline = landingPage.tagline || "";

      // Debug image URLs
      console.log("📸 Loaded images:", {
        profile_image: profileData.profilePicture,
        profile_image_full: getImageUrl(profileData.profilePicture),
        cover_banner: profileData.coverBanner,
        cover_banner_full: getImageUrl(profileData.coverBanner),
        company_logo: profileData.companyLogo,
        company_logo_full: getImageUrl(profileData.companyLogo),
      });

      // Company Info
      profileData.companyLogoText = landingPage.company_logo_text || "";
      profileData.companyName = landingPage.company_name || "";
      profileData.companyRegistrationNo =
        landingPage.company_registration_no || "";
      profileData.companyDepartment = landingPage.company_department || "";
      profileData.companyDescription = landingPage.company_description || "";
      profileData.companyVideo = landingPage.company_video || "";
      profileData.industry = landingPage.industry || "";
      profileData.establishedYear = landingPage.established_year || "";
      profileData.employeeCount = landingPage.employee_count || "";
      profileData.companyWhatsapp = landingPage.company_whatsapp || "";

      // Address Details (new 3-line format, fallback auto-compose from legacy)
      if (landingPage.address_1 || landingPage.address1) {
        profileData.address1 = landingPage.address_1 || landingPage.address1 || "";
        profileData.address2 = landingPage.address_2 || landingPage.address2 || "";
        profileData.address3 = landingPage.address_3 || landingPage.address3 || "";
      } else {
        // Auto-compose 3 lines from legacy fields for smooth migration
        profileData.address1 = [landingPage.address_name, landingPage.address_street].filter(Boolean).join(", ");
        profileData.address2 = landingPage.address_area || "";
        profileData.address3 = [
          [landingPage.postal_code, landingPage.address_city_state].filter(Boolean).join(" "),
          landingPage.address_country
        ].filter(Boolean).join(", ");
      }
      // Legacy separate address fields (preserve values)
      profileData.addressName = landingPage.address_name || "";
      profileData.addressStreet = landingPage.address_street || "";
      profileData.addressArea = landingPage.address_area || "";
      profileData.addressCityState = landingPage.address_city_state || "";
      profileData.addressCountry = landingPage.address_country || "";
      profileData.postalCode = landingPage.postal_code || "";
      profileData.mapUrl = landingPage.address_map_url || landingPage.map_url || "";
      profileData.coordinates = landingPage.coordinates || "";

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
      
      // Services additional
      profileData.serviceName = landingPage.service_name || "";
      profileData.serviceIcon = landingPage.service_icon || "🚀";
      profileData.serviceCategory = landingPage.service_category || "";
      profileData.serviceImage = landingPage.service_image || "";
      profileData.serviceVideo = landingPage.service_video || "";
      profileData.serviceDescription = landingPage.service_description || "";
      profileData.servicePrice = landingPage.service_price || "";
      profileData.serviceOldPrice = landingPage.service_old_price || "";
      profileData.serviceDuration = landingPage.service_duration || "";
      profileData.serviceBrochure = landingPage.service_brochure || "";
      profileData.bookingEnabled = landingPage.booking_enabled || false;
      profileData.bookingUrl = landingPage.booking_url || "";
      if (landingPage.gallery && Array.isArray(landingPage.gallery)) {
        profileData.gallery = landingPage.gallery;
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

      // Education (repeater)
      if (landingPage.education && Array.isArray(landingPage.education)) {
        profileData.education = landingPage.education;
      }

      // Certifications (repeater)
      if (landingPage.certifications && Array.isArray(landingPage.certifications)) {
        profileData.certifications = landingPage.certifications;
      }

      // Expertise (repeater)
      if (landingPage.expertise && Array.isArray(landingPage.expertise)) {
        profileData.expertise = landingPage.expertise;
      }

      // Awards (repeater)
      if (landingPage.awards && Array.isArray(landingPage.awards)) {
        profileData.awards = landingPage.awards;
      }

      // Working Hours (repeater)
      if (landingPage.working_hours && Array.isArray(landingPage.working_hours)) {
        profileData.workingHours = landingPage.working_hours;
      }

      // Service Features (repeater)
      if (landingPage.service_features && Array.isArray(landingPage.service_features)) {
        profileData.serviceFeatures = landingPage.service_features;
      }

      // Service Tags (repeater)
      if (landingPage.service_tags && Array.isArray(landingPage.service_tags)) {
        profileData.serviceTags = landingPage.service_tags;
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
      profileData.accentColor =
        landingPage.accent_color || landingPage.accentColor || "#1F2937";
      profileData.bgGradient =
        landingPage.bg_gradient || landingPage.bgGradient || null;
      profileData.font = landingPage.font || "inter";
      profileData.buttonStyle =
        landingPage.button_style || landingPage.buttonStyle || "solid";
      profileData.cardStyle =
        landingPage.card_style || landingPage.cardStyle || "minimal";
      profileData.bgAnimation =
        landingPage.bg_animation || landingPage.bgAnimation || "none";
      profileData.colorScheme = 
        landingPage.color_scheme || landingPage.colorScheme || "";
      profileData.layout = 
        landingPage.layout || "";
      profileData.showWatermark = landingPage.show_watermark !== false;
      
      // Profile Stats (also map to profileStats)
      if (landingPage.stats && Array.isArray(landingPage.stats)) {
        profileData.profileStats = landingPage.stats;
      }
      
      // Load features state and order
      if (landingPage.features && typeof landingPage.features === 'object') {
        profileData.features = landingPage.features;

        // Extract dynamic field toggle values from features back to root profileData
        // This bridges the gap between features JSON storage and DynamicFormField bindings
        const f = landingPage.features;
        if (f.vcardEnabled !== undefined) profileData.vcardEnabled = f.vcardEnabled;
        else if (f.vcard_download !== undefined) profileData.vcardEnabled = f.vcard_download;

        if (f.qrCodeEnabled !== undefined) profileData.qrCodeEnabled = f.qrCodeEnabled;
        else if (f.qr_code !== undefined) profileData.qrCodeEnabled = f.qr_code;

        if (f.contactFormEnabled !== undefined) profileData.contactFormEnabled = f.contactFormEnabled;
        else if (f.contact_form !== undefined) profileData.contactFormEnabled = f.contact_form;

        if (f.contactFormEmail) profileData.contactFormEmail = f.contactFormEmail;
        else if (f.contact_form_email) profileData.contactFormEmail = f.contact_form_email;

        if (f.openNewTab !== undefined) profileData.openNewTab = f.openNewTab;
        if (f.clickTracking !== undefined) profileData.clickTracking = f.clickTracking;
      }
      if (landingPage.feature_order && Array.isArray(landingPage.feature_order)) {
        profileData.featureOrder = landingPage.feature_order;
        featureOrder.value = landingPage.feature_order;
      }
      
      // Load layout configuration
      if (landingPage.section_layout && Array.isArray(landingPage.section_layout)) {
        profileData.sectionLayout = landingPage.section_layout;
      }
      if (landingPage.field_layout && typeof landingPage.field_layout === 'object') {
        profileData.fieldLayout = landingPage.field_layout;
      }
      if (landingPage.section_settings && typeof landingPage.section_settings === 'object') {
        profileData.sectionSettings = landingPage.section_settings;
      }
      
      // Portfolio
      profileData.portfolioTitle = landingPage.portfolio_title || '';
      profileData.portfolioDescription = landingPage.portfolio_description || '';
      profileData.portfolioCategory = landingPage.portfolio_category || '';
      if (landingPage.portfolio_tags && Array.isArray(landingPage.portfolio_tags)) {
        profileData.portfolioTags = landingPage.portfolio_tags;
      }
      profileData.portfolioCoverImage = landingPage.portfolio_cover_image || null;
      if (landingPage.portfolio_gallery && Array.isArray(landingPage.portfolio_gallery)) {
        profileData.portfolioGallery = landingPage.portfolio_gallery;
      }
      if (landingPage.projects && Array.isArray(landingPage.projects)) {
        profileData.projects = landingPage.projects;
      }
      profileData.projectUrl = landingPage.project_url || '';
      profileData.dateCompleted = landingPage.date_completed || '';
      profileData.clientName = landingPage.client_name || '';
      profileData.location = landingPage.portfolio_location || '';
      if (landingPage.skills_used && Array.isArray(landingPage.skills_used)) {
        profileData.skillsUsed = landingPage.skills_used;
      }
      profileData.pdfDownload = landingPage.pdf_download || null;
      
      // Blog
      profileData.blogEnabled = landingPage.blog_enabled || false;
      if (landingPage.blog_posts && Array.isArray(landingPage.blog_posts)) {
        profileData.blogPosts = landingPage.blog_posts;
      }
      if (landingPage.blog_gallery && Array.isArray(landingPage.blog_gallery)) {
        profileData.blogGallery = landingPage.blog_gallery;
      }
      profileData.blogTitle = landingPage.blog_title || "";
      profileData.blogSlug = landingPage.blog_slug || "";
      profileData.blogCoverImage = landingPage.blog_cover_image || null;
      profileData.blogCategory = landingPage.blog_category || "";
      if (landingPage.blog_tags && Array.isArray(landingPage.blog_tags)) {
        profileData.blogTags = landingPage.blog_tags;
      }
      profileData.authorName = landingPage.author_name || "";
      profileData.publishedDate = landingPage.published_date || "";
      profileData.readingTime = landingPage.reading_time || "";
      profileData.blogContent = landingPage.blog_content || "";
      profileData.externalLink = landingPage.external_link || "";
      if (landingPage.related_posts && Array.isArray(landingPage.related_posts)) {
        profileData.relatedPosts = landingPage.related_posts;
      }
      
      // Links additional
      profileData.appointmentLink = landingPage.appointment_link || "";
      profileData.paymentButtonText = landingPage.payment_button_text || "";
      profileData.paymentButtonUrl = landingPage.payment_button_url || "";
      
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
  profileData.pronouns = "";
  profileData.qualification = "";
  profileData.bio = "";
  profileData.tagline = "";
  profileData.contactNumber = "";
  profileData.email = "";
  profileData.website = "";
  profileData.address = "";
  
  // Images
  profileData.profilePicture = null;
  profileData.image = null; // Legacy field
  profileData.coverBanner = null;

  // Company Info
  profileData.companyLogo = null;
  profileData.companyLogoText = "";
  profileData.companyName = "";
  profileData.companyRegistrationNo = "";
  profileData.companyDepartment = "";
  profileData.companyDescription = "";
  profileData.companyVideo = "";
  profileData.industry = "";
  profileData.establishedYear = "";
  profileData.employeeCount = "";
  profileData.companyWhatsapp = "";

  // Address Info (new 3-line + legacy)
  profileData.address1 = "";
  profileData.address2 = "";
  profileData.address3 = "";
  profileData.addressName = "";
  profileData.addressStreet = "";
  profileData.addressArea = "";
  profileData.addressCityState = "";
  profileData.addressCountry = "";
  profileData.postalCode = "";
  profileData.mapUrl = "";
  profileData.coordinates = "";

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

  // Repeater fields (reset to empty)
  profileData.education = [];
  profileData.certifications = [];
  profileData.expertise = [];
  profileData.awards = [];
  profileData.workingHours = [];
  profileData.serviceFeatures = [];
  profileData.serviceTags = [];
  
  // Services additional
  profileData.serviceName = "";
  profileData.serviceIcon = "🚀";
  profileData.serviceCategory = "";
  profileData.serviceImage = "";
  profileData.serviceVideo = "";
  profileData.serviceDescription = "";
  profileData.servicePrice = "";
  profileData.serviceOldPrice = "";
  profileData.serviceDuration = "";
  profileData.serviceBrochure = "";
  profileData.bookingEnabled = false;
  profileData.bookingUrl = "";
  profileData.gallery = [];

  // Design Settings
  profileData.profileStyle = "classic";
  profileData.theme = "minimal";
  profileData.backgroundColor = "#FFFFFF";
  profileData.textColor = "#000000";
  profileData.accentColor = "#1F2937";
  profileData.bgGradient = null;
  profileData.font = "inter";
  profileData.buttonStyle = "solid";
  profileData.cardStyle = "minimal";
  profileData.bgAnimation = "none";
  profileData.colorScheme = "";
  profileData.layout = "";
  profileData.showWatermark = true;
  
  // Profile Stats
  profileData.profileStats = [];
  
  // Features
  profileData.features = {};
  profileData.featureOrder = [];
  featureOrder.value = [];
  
  // Portfolio
  profileData.portfolioTitle = "";
  profileData.portfolioDescription = "";
  profileData.portfolioCategory = "";
  profileData.portfolioTags = [];
  profileData.portfolioCoverImage = null;
  profileData.portfolioGallery = [];
  profileData.projects = [];
  profileData.projectUrl = "";
  profileData.dateCompleted = "";
  profileData.clientName = "";
  profileData.location = "";
  profileData.skillsUsed = [];
  profileData.pdfDownload = null;
  
  // Blog
  profileData.blogEnabled = false;
  profileData.blogPosts = [];
  profileData.blogGallery = [];
  profileData.blogTitle = "";
  profileData.blogSlug = "";
  profileData.blogCoverImage = null;
  profileData.blogCategory = "";
  profileData.blogTags = [];
  profileData.authorName = "";
  profileData.publishedDate = "";
  profileData.readingTime = "";
  profileData.blogContent = "";
  profileData.externalLink = "";
  profileData.relatedPosts = [];
  
  // Links additional
  profileData.appointmentLink = "";
  profileData.paymentButtonText = "";
  profileData.paymentButtonUrl = "";
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
      const authoritativePlan = authStore.user?.subscription_plan || null;
      const rawOwnCards = response.nfc_cards.filter(card => {
        // Include cards where user_id equals current user (Admin's own cards)
        // This excludes employee cards (where user_id is the employee's ID)
        return card.user_id === currentUserId;
      });
      userNfcCards.value = rawOwnCards.map(card => {
        const effectivePlan =
          authoritativePlan &&
          (!card.subscription_plan ||
            card.subscription_plan === "free" ||
            card.subscription_plan !== authoritativePlan)
            ? authoritativePlan
            : card.subscription_plan;
        return {
          ...card,
          subscription_plan: effectivePlan,
        };
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

// Check if mobile - Used for screen size detection to adjust UI
// Does not affect layout switching
// isMobile property only used to adjust specific component sizes
// Currently set for devices smaller than 768px (md breakpoint)
const checkMobile = () => {
  isMobile.value = window.innerWidth < 768;
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

  // Get list of available feature keys (assigned by admin)
  const availableFeatureKeys = featureToggles.value.map(f => f.feature_key);
  
  // Build features object - only include available features with their enabled state
  const featuresData = {};
  availableFeatureKeys.forEach(key => {
    featuresData[key] = getFeatureValue(key);
  });

  const previewData = {
    ...profileData,
    // Include features with their enabled state
    features: featuresData,
    // Include list of available features (assigned by admin)
    availableFeatures: availableFeatureKeys,
    links: links.value.filter(link => link.is_active).map(link => ({
      title: link.title,
      url: link.url,
      platform: link.platform,
      is_active: link.is_active,
      order: link.order,
    })),
    timestamp: Date.now(),
  };
  
  console.log('📤 Preview data - availableFeatures:', availableFeatureKeys);
  console.log('📤 Preview data - features:', featuresData);
  
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
    console.log('Preview URL:', getCardPublicUrl(selectedCard, { preview: true }));
  }
};

// Iframe error callback
const onPreviewError = (error) => {
  console.error('❌ Preview iframe error:', error);
  const selectedCard = getSelectedCard();
  if (selectedCard) {
    console.error('Failed URL:', getCardPublicUrl(selectedCard, { preview: true }));
  }
  $toast.error('Failed to load preview. Please try again.');
};

// Force refresh preview
const forceRefreshPreview = () => {
  console.log('🔄 Force refreshing preview...');
  updatePreviewData();
  $toast.success('Preview refreshed');
};

// Open Landing Page in new tab (Preview Mode - shows unsaved changes)
const openLandingPage = () => {
  if (!selectedNfcCardId.value) {
    $toast.error("Please select an NFC card first");
    return;
  }

  const selectedCard = userNfcCards.value.find(
    (card) => card.id === selectedNfcCardId.value
  );

  if (!selectedCard) {
    $toast.error("Card information not found");
    return;
  }

  // Update preview data before opening
  updatePreviewData();

  // Open landing page in new tab with preview mode (use nfc_card_id or id as fallback)
  const landingPageUrl = getCardPublicUrl(selectedCard, { preview: true });
  window.open(landingPageUrl, '_blank');
  
  $toast.success("Opening live preview in new tab");
};

// Open Saved Landing Page in new tab (No Preview Mode - shows saved data from backend)
const openSavedLandingPage = () => {
  if (!selectedNfcCardId.value) {
    $toast.error("Please select an NFC card first");
    return;
  }

  const selectedCard = userNfcCards.value.find(
    (card) => card.id === selectedNfcCardId.value
  );

  if (!selectedCard) {
    $toast.error("Card information not found");
    return;
  }

  // Open real landing page without preview mode (shows saved data from backend)
  const landingPageUrl = getCardPublicUrl(selectedCard);
  window.open(landingPageUrl, '_blank');
  
  $toast.success("Opening saved landing page");
};

// Initialize
onMounted(async () => {
  checkMobile();
  window.addEventListener("resize", checkMobile);
  const uid = authStore.user?.id;
  const planType = 'business';

  // ═══════════════════════════════════════════════════════════════════════
  // PERFORMANCE #1 (0ms): STATIC CACHE HIT
  // If user navigated away and comes back within 120s the builder config
  // (sections/fields/design options + permissions + fields grouping) can be
  // applied SYNCHRONOUSLY with ZERO network calls.
  // ═══════════════════════════════════════════════════════════════════════
  let batchUsed = false;
  const cachedInit = getCachedBuilderInit(uid, planType);
  if (cachedInit && cachedInit.applySectionsData && cachedInit.applyFieldsGroupedData && cachedInit.applyDesignOptionsData) {
    try {
      cachedInit.applySectionsData(cachedInit.payload.sections);
      cachedInit.applyFieldsGroupedData(cachedInit.payload.fields);
      cachedInit.applyDesignOptionsData(cachedInit.payload.design_options);
      batchUsed = true;
      console.log('✅ BUILDER-INIT CACHE APPLIED (0ms, 0 network calls)');
    } catch (_) { /* fall-through to network */ }
  }

  // ═══════════════════════════════════════════════════════════════════════
  // PERFORMANCE #2 (NFC cards cache) — read from cache if available
  // ═══════════════════════════════════════════════════════════════════════
  let cachedCards = uid ? getCachedNfcCards(uid) : null;
  if (cachedCards) {
    userNfcCards.value = cachedCards;
    if (cachedCards.length > 0 && !selectedNfcCardId.value) {
      selectedNfcCardId.value = cachedCards[0].id;
      try { await loadProfile(); } catch(_){}
    }
  }

  try {
    // Load configurations and NFC cards in parallel for faster loading
    // (or skip all config calls entirely if cache hit succeeded for both)
    const tasks = [];

    if (!batchUsed) {
      tasks.push(
        loadSections().catch(err => console.log('⚠️ Sections loading failed, using fallback:', err.message)),
        loadUserPermissions().catch(err => console.log('User permissions skipped:', err.message)),
        loadFieldsConfig().catch(err => console.log('Fields config skipped:', err.message)),
        loadDesignOptions().catch(err => console.log('Design options skipped:', err.message)),
      );
    }
    if (!cachedCards) {
      const p = loadUserNfcCards().catch(err => console.log('NFC cards loading failed:', err.message));
      tasks.push(p);
      // After loading completes, cache the fresh list
      p.then(() => { setCachedNfcCards(uid, userNfcCards.value); });
    }
    if (tasks.length) await Promise.all(tasks);

    console.log(`✅ Core configurations loaded (configCached=${batchUsed}, nfcCardsCached=${!!cachedCards})`);
    
    // Check category availability and set initial category
    // First check if there are available categories
    if (availableGeneralTabs.value.length > 0) {
      // General options available, set to general category
      mainCategory.value = 'general';
      activeTab.value = availableGeneralTabs.value[0]?.id;
    } else if (availableDesignTabs.value.length > 0) {
      // No general options but design options available, set to design category
      mainCategory.value = 'design';
      activeTab.value = availableDesignTabs.value[0]?.id;
    } else {
      // No options available in either category
      console.log('\u26a0\ufe0f No available options - Admin has not assigned any features');
    }
  } catch (error) {
    console.error('Error loading configurations:', error);
  } finally {
    pageLoading.value = false;
  }
  
  // Load employee cards in background (non-blocking)
  loadAllEmployeeCards().catch(err => console.log('Employee cards skipped:', err.message));
  
  // Initialize preview data IMMEDIATELY (was 500ms delay → removed for perceived perf)
  updatePreviewData();

  // Check if we should open Apply Design modal from URL parameter
  const route = useRoute();
  if (route.query.openApplyDesign === 'true') {
    // Reduced from 1000ms → 200ms: modal only needs DOM stable, not iframe-ready
    setTimeout(() => {
      showApplyDesignModal.value = true;
    }, 200);
  }

  // Close card selector when clicking outside
  document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
  window.removeEventListener("resize", checkMobile);
  document.removeEventListener("click", handleClickOutside);

  // ─── Cancel all in-flight API requests initiated by this page ──────
  createAbortOnRouteChange("BusinessProfileBuilder unmounted")();

  // Clear update timeout
  if (updatePreviewTimeout) clearTimeout(updatePreviewTimeout);
});

// Watch for changes and update preview in real-time
let updatePreviewTimeout = null;

watch(
  () => JSON.stringify({ ...profileData, links: links.value }),
  () => {
    // Debounce the update to avoid too many refreshes
    if (updatePreviewTimeout) clearTimeout(updatePreviewTimeout);
    updatePreviewTimeout = setTimeout(() => {
      console.log('🔄 Auto-updating preview due to data change');
      updatePreviewData();
    }, 500); // 500ms debounce for faster feedback
  }
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
