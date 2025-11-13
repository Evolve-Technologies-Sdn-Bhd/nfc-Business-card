<!-- pages/UserDashboard/Settings.vue -->
<template>
  <div class="min-h-screen bg-secondary-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Header Section -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-secondary-900 mb-2">
          Account Settings
        </h1>
        <p class="text-secondary-600">
          Manage your account, security, and preferences
        </p>
      </div>

      <!-- Main Layout -->
      <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Navigation -->
        <div class="w-full lg:w-64 flex-shrink-0">
          <div
            class="bg-white rounded-lg shadow-sm border border-secondary-200 p-4"
          >
            <nav class="space-y-2">
              <button
                v-for="tab in settingsTabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                :class="[
                  'w-full text-left px-3 py-2 rounded-lg transition-colors duration-200 flex items-center',
                  activeTab === tab.id
                    ? 'bg-primary-100 text-primary-700 font-medium'
                    : 'text-secondary-700 hover:bg-secondary-50',
                ]"
              >
                <Icon :name="tab.icon" class="h-5 w-5 mr-3" />
                {{ tab.label }}
              </button>
            </nav>
          </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1">
          <!-- Account Settings Tab (Integrated Profile + Account) -->
          <div v-if="activeTab === 'account'" class="space-y-6">
            <!-- Personal Information Card -->
            <div
              class="bg-white rounded-lg shadow-sm border border-secondary-200"
            >
              <div class="px-6 py-4 border-b border-secondary-200">
                <h2 class="text-lg font-semibold text-secondary-900">
                  Personal Information
                </h2>
                <p class="text-sm text-secondary-600">
                  Update your personal details
                </p>
              </div>
              <div class="p-6">
                <form @submit.prevent="updatePersonalInfo">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Account Picture -->
                    <div class="md:col-span-2">
                      <ProfileImageUpload
                        v-model="personalInfoForm.account_image"
                        upload-endpoint="/settings/upload-account-image"
                        delete-endpoint="/settings/delete-account-image"
                        label="Account Picture"
                        help-text="JPG, PNG, GIF or WebP. Max 5MB"
                        @upload-success="handleAccountImageUpload"
                      />
                    </div>

                    <!-- First Name -->
                    <div>
                      <label
                        class="block text-sm font-medium text-secondary-700 mb-2"
                        >First Name *</label
                      >
                      <input
                        v-model="personalInfoForm.first_name"
                        type="text"
                        required
                        class="input input-bordered w-full"
                        placeholder="Enter your first name"
                      />
                    </div>

                    <!-- Last Name -->
                    <div>
                      <label
                        class="block text-sm font-medium text-secondary-700 mb-2"
                        >Last Name *</label
                      >
                      <input
                        v-model="personalInfoForm.last_name"
                        type="text"
                        required
                        class="input input-bordered w-full"
                        placeholder="Enter your last name"
                      />
                    </div>

                    <!-- Email -->
                    <div>
                      <label
                        class="block text-sm font-medium text-secondary-700 mb-2"
                        >Email Address *</label
                      >
                      <input
                        v-model="personalInfoForm.email"
                        type="email"
                        required
                        class="input input-bordered w-full"
                        placeholder="Enter your email"
                      />
                    </div>

                    <!-- Phone -->
                    <div>
                      <label
                        class="block text-sm font-medium text-secondary-700 mb-2"
                        >Phone Number</label
                      >
                      <input
                        v-model="personalInfoForm.phone"
                        type="tel"
                        class="input input-bordered w-full"
                        placeholder="Enter your phone number"
                      />
                    </div>
                  </div>

                  <!-- Submit Button -->
                  <div class="flex justify-end mt-6">
                    <button
                      type="submit"
                      :disabled="updatingPersonalInfo"
                      class="btn btn-primary"
                    >
                      <span
                        v-if="updatingPersonalInfo"
                        class="loading loading-spinner loading-sm mr-2"
                      ></span>
                      {{
                        updatingPersonalInfo
                          ? "Updating..."
                          : "Update Personal Info"
                      }}
                    </button>
                  </div>
                </form>
              </div>
            </div>

            <!-- Profile URL Settings Card -->
            <div
              class="bg-white rounded-lg shadow-sm border border-secondary-200"
            >
              <div class="px-6 py-4 border-b border-secondary-200">
                <h2 class="text-lg font-semibold text-secondary-900">
                  Profile URL
                </h2>
                <p class="text-sm text-secondary-600">
                  Customize your public profile URL
                </p>
              </div>
              <div class="p-6">
                <div class="flex items-center space-x-2">
                  <span class="text-secondary-500">nfccard.app/Homepage/</span>
                  <input
                    v-model="profileForm.slug"
                    type="text"
                    class="input input-bordered flex-1"
                    placeholder="your-username"
                    @input="checkSlugAvailability"
                  />
                  <button
                    @click="updateSlug"
                    :disabled="!isSlugValid || updatingSlug"
                    class="btn btn-primary"
                  >
                    <span
                      v-if="updatingSlug"
                      class="loading loading-spinner loading-sm mr-2"
                    ></span>
                    Update
                  </button>
                </div>
                <div class="mt-2">
                  <p
                    v-if="slugStatus === 'checking'"
                    class="text-sm text-secondary-500"
                  >
                    <Icon
                      name="heroicons:arrow-path"
                      class="h-4 w-4 inline animate-spin mr-1"
                    />
                    Checking availability...
                  </p>
                  <p
                    v-else-if="slugStatus === 'available'"
                    class="text-sm text-green-600"
                  >
                    <Icon
                      name="heroicons:check-circle"
                      class="h-4 w-4 inline mr-1"
                    />
                    URL is available
                  </p>
                  <p
                    v-else-if="slugStatus === 'taken'"
                    class="text-sm text-red-600"
                  >
                    <Icon
                      name="heroicons:x-circle"
                      class="h-4 w-4 inline mr-1"
                    />
                    URL is already taken
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Account Settings Tab -->
          <div v-if="activeTab === 'account'" class="space-y-6">
            <!-- Account Information Card -->
            <div
              class="bg-white rounded-lg shadow-sm border border-secondary-200"
            >
              <div class="px-6 py-4 border-b border-secondary-200">
                <h2 class="text-lg font-semibold text-secondary-900">
                  Account Information
                </h2>
                <p class="text-sm text-secondary-600">
                  View and manage your account details
                </p>
              </div>
              <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label
                      class="block text-sm font-medium text-secondary-700 mb-1"
                      >Account Status</label
                    >
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                    >
                      <Icon
                        name="heroicons:check-circle"
                        class="h-3 w-3 mr-1"
                      />
                      Active
                    </span>
                  </div>
                  <div>
                    <label
                      class="block text-sm font-medium text-secondary-700 mb-1"
                      >Subscription Plan</label
                    >
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                    >
                      {{ user?.subscription_plan || "Free" }}
                    </span>
                  </div>
                  <div>
                    <label
                      class="block text-sm font-medium text-secondary-700 mb-1"
                      >Member Since</label
                    >
                    <p class="text-sm text-secondary-900">
                      {{ formatDate(user?.created_at) }}
                    </p>
                  </div>
                  <div>
                    <label
                      class="block text-sm font-medium text-secondary-700 mb-1"
                      >Last Login</label
                    >
                    <p class="text-sm text-secondary-900">
                      {{ formatDate(user?.last_login_at) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Subscription Management Card -->
            <div
              class="bg-white rounded-lg shadow-sm border border-secondary-200"
            >
              <div class="px-6 py-4 border-b border-secondary-200">
                <h2 class="text-lg font-semibold text-secondary-900">
                  Subscription
                </h2>
                <p class="text-sm text-secondary-600">
                  Manage your subscription and billing
                </p>
              </div>
              <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                  <div>
                    <h3 class="text-lg font-medium text-secondary-900">
                      {{ user?.subscription_plan || "Free Plan" }}
                    </h3>
                    <p class="text-sm text-secondary-600">
                      {{
                        user?.subscription_plan === "free"
                          ? "Basic features included"
                          : "Premium features included"
                      }}
                    </p>
                  </div>
                  <div class="text-right">
                    <p class="text-2xl font-bold text-secondary-900">
                      {{ user?.subscription_plan === "free" ? "$0" : "$9.99" }}
                      <span class="text-sm font-normal text-secondary-500"
                        >/month</span
                      >
                    </p>
                  </div>
                </div>
                <div class="flex space-x-3">
                  <button
                    v-if="user?.subscription_plan === 'free'"
                    @click="upgradePlan"
                    class="btn btn-primary"
                  >
                    <Icon
                      name="heroicons:arrow-trending-up"
                      class="h-4 w-4 mr-2"
                    />
                    Upgrade to Premium
                  </button>
                  <button v-else @click="manageBilling" class="btn btn-outline">
                    <Icon name="heroicons:credit-card" class="h-4 w-4 mr-2" />
                    Manage Billing
                  </button>
                  <button
                    v-if="user?.subscription_plan !== 'free'"
                    @click="cancelSubscription"
                    class="btn btn-outline text-red-600 border-red-300 hover:bg-red-50"
                  >
                    Cancel Subscription
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Security Settings Tab -->
          <div v-if="activeTab === 'security'" class="space-y-6">
            <!-- Change Password Card -->
            <div
              class="bg-white rounded-lg shadow-sm border border-secondary-200"
            >
              <div class="px-6 py-4 border-b border-secondary-200">
                <h2 class="text-lg font-semibold text-secondary-900">
                  Change Password
                </h2>
                <p class="text-sm text-secondary-600">
                  Update your account password
                </p>
              </div>
              <div class="p-6">
                <form @submit.prevent="changePassword">
                  <div class="space-y-4">
                    <div>
                      <label
                        class="block text-sm font-medium text-secondary-700 mb-2"
                        >Current Password</label
                      >
                      <input
                        v-model="passwordForm.current_password"
                        type="password"
                        required
                        class="input input-bordered w-full"
                        placeholder="Enter current password"
                      />
                    </div>
                    <div>
                      <label
                        class="block text-sm font-medium text-secondary-700 mb-2"
                        >New Password</label
                      >
                      <input
                        v-model="passwordForm.new_password"
                        type="password"
                        required
                        class="input input-bordered w-full"
                        placeholder="Enter new password"
                      />
                    </div>
                    <div>
                      <label
                        class="block text-sm font-medium text-secondary-700 mb-2"
                        >Confirm New Password</label
                      >
                      <input
                        v-model="passwordForm.confirm_password"
                        type="password"
                        required
                        class="input input-bordered w-full"
                        placeholder="Confirm new password"
                      />
                    </div>
                  </div>
                  <div class="flex justify-end mt-6">
                    <button
                      type="submit"
                      :disabled="changingPassword"
                      class="btn btn-primary"
                    >
                      <span
                        v-if="changingPassword"
                        class="loading loading-spinner loading-sm mr-2"
                      ></span>
                      {{ changingPassword ? "Updating..." : "Change Password" }}
                    </button>
                  </div>
                </form>
              </div>
            </div>

            <!-- Two-Factor Authentication Card -->
            <div
              class="bg-white rounded-lg shadow-sm border border-secondary-200"
            >
              <div class="px-6 py-4 border-b border-secondary-200">
                <h2 class="text-lg font-semibold text-secondary-900">
                  Two-Factor Authentication
                </h2>
                <p class="text-sm text-secondary-600">
                  Add an extra layer of security to your account
                </p>
              </div>
              <div class="p-6">
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <h3 class="text-sm font-medium text-secondary-900">
                        SMS Authentication
                      </h3>
                      <p class="text-sm text-secondary-600">
                        Receive verification codes via SMS
                      </p>
                    </div>
                    <div class="form-control">
                      <label class="label cursor-pointer">
                        <input
                          v-model="securitySettings.two_factor_sms"
                          type="checkbox"
                          class="toggle toggle-primary"
                          @change="updateSecuritySetting('two_factor_sms')"
                        />
                      </label>
                    </div>
                  </div>
                  <div class="flex items-center justify-between">
                    <div>
                      <h3 class="text-sm font-medium text-secondary-900">
                        Email Notifications
                      </h3>
                      <p class="text-sm text-secondary-600">
                        Get notified of login attempts
                      </p>
                    </div>
                    <div class="form-control">
                      <label class="label cursor-pointer">
                        <input
                          v-model="securitySettings.login_notifications"
                          type="checkbox"
                          class="toggle toggle-primary"
                          @change="updateSecuritySetting('login_notifications')"
                        />
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Active Sessions Card -->
            <div
              class="bg-white rounded-lg shadow-sm border border-secondary-200"
            >
              <div class="px-6 py-4 border-b border-secondary-200">
                <h2 class="text-lg font-semibold text-secondary-900">
                  Active Sessions
                </h2>
                <p class="text-sm text-secondary-600">
                  Manage your active login sessions
                </p>
              </div>
              <div class="p-6">
                <div class="space-y-4">
                  <div
                    v-for="session in activeSessions"
                    :key="session.id"
                    class="flex items-center justify-between p-4 border border-secondary-200 rounded-lg"
                  >
                    <div class="flex items-center">
                      <div
                        class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4"
                      >
                        <Icon
                          :name="getDeviceIcon(session.device_type)"
                          class="h-5 w-5 text-blue-600"
                        />
                      </div>
                      <div>
                        <p class="text-sm font-medium text-secondary-900">
                          {{ session.device_name }}
                        </p>
                        <p class="text-xs text-secondary-600">
                          {{ session.location }} •
                          {{ formatDate(session.last_activity) }}
                        </p>
                      </div>
                    </div>
                    <div class="flex items-center space-x-2">
                      <span
                        v-if="session.is_current"
                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"
                      >
                        Current
                      </span>
                      <button
                        v-else
                        @click="revokeSession(session.id)"
                        class="btn btn-outline btn-sm text-red-600 border-red-300 hover:bg-red-50"
                      >
                        Revoke
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Privacy Settings Tab -->
          <div v-if="activeTab === 'privacy'" class="space-y-6">
            <!-- Profile Visibility Card -->
            <div
              class="bg-white rounded-lg shadow-sm border border-secondary-200"
            >
              <div class="px-6 py-4 border-b border-secondary-200">
                <h2 class="text-lg font-semibold text-secondary-900">
                  Profile Visibility
                </h2>
                <p class="text-sm text-secondary-600">
                  Control who can see your profile
                </p>
              </div>
              <div class="p-6">
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <h3 class="text-sm font-medium text-secondary-900">
                        Public Profile
                      </h3>
                      <p class="text-sm text-secondary-600">
                        Make your profile visible to everyone
                      </p>
                    </div>
                    <div class="form-control">
                      <label class="label cursor-pointer">
                        <input
                          v-model="privacySettings.public_profile"
                          type="checkbox"
                          class="toggle toggle-primary"
                          @change="updatePrivacySetting('public_profile')"
                        />
                      </label>
                    </div>
                  </div>
                  <div class="flex items-center justify-between">
                    <div>
                      <h3 class="text-sm font-medium text-secondary-900">
                        Show in Search
                      </h3>
                      <p class="text-sm text-secondary-600">
                        Allow search engines to index your profile
                      </p>
                    </div>
                    <div class="form-control">
                      <label class="label cursor-pointer">
                        <input
                          v-model="privacySettings.search_indexing"
                          type="checkbox"
                          class="toggle toggle-primary"
                          @change="updatePrivacySetting('search_indexing')"
                        />
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Analytics & Tracking Card -->
            <div
              class="bg-white rounded-lg shadow-sm border border-secondary-200"
            >
              <div class="px-6 py-4 border-b border-secondary-200">
                <h2 class="text-lg font-semibold text-secondary-900">
                  Analytics & Tracking
                </h2>
                <p class="text-sm text-secondary-600">
                  Manage data collection preferences
                </p>
              </div>
              <div class="p-6">
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <h3 class="text-sm font-medium text-secondary-900">
                        Profile Analytics
                      </h3>
                      <p class="text-sm text-secondary-600">
                        Track profile views and interactions
                      </p>
                    </div>
                    <div class="form-control">
                      <label class="label cursor-pointer">
                        <input
                          v-model="privacySettings.analytics_enabled"
                          type="checkbox"
                          class="toggle toggle-primary"
                          @change="updatePrivacySetting('analytics_enabled')"
                        />
                      </label>
                    </div>
                  </div>
                  <div class="flex items-center justify-between">
                    <div>
                      <h3 class="text-sm font-medium text-secondary-900">
                        Location Tracking
                      </h3>
                      <p class="text-sm text-secondary-600">
                        Track visitor locations for analytics
                      </p>
                    </div>
                    <div class="form-control">
                      <label class="label cursor-pointer">
                        <input
                          v-model="privacySettings.location_tracking"
                          type="checkbox"
                          class="toggle toggle-primary"
                          @change="updatePrivacySetting('location_tracking')"
                        />
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Notifications Tab -->
          <div v-if="activeTab === 'notifications'" class="space-y-6">
            <!-- Email Notifications Card -->
            <div
              class="bg-white rounded-lg shadow-sm border border-secondary-200"
            >
              <div class="px-6 py-4 border-b border-secondary-200">
                <h2 class="text-lg font-semibold text-secondary-900">
                  Email Notifications
                </h2>
                <p class="text-sm text-secondary-600">
                  Choose what email notifications you receive
                </p>
              </div>
              <div class="p-6">
                <div class="space-y-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <h3 class="text-sm font-medium text-secondary-900">
                        Profile Views
                      </h3>
                      <p class="text-sm text-secondary-600">
                        Get notified when someone views your profile
                      </p>
                    </div>
                    <div class="form-control">
                      <label class="label cursor-pointer">
                        <input
                          v-model="notificationSettings.profile_views"
                          type="checkbox"
                          class="toggle toggle-primary"
                          @change="updateNotificationSetting('profile_views')"
                        />
                      </label>
                    </div>
                  </div>
                  <div class="flex items-center justify-between">
                    <div>
                      <h3 class="text-sm font-medium text-secondary-900">
                        NFC Taps
                      </h3>
                      <p class="text-sm text-secondary-600">
                        Get notified when someone taps your NFC card
                      </p>
                    </div>
                    <div class="form-control">
                      <label class="label cursor-pointer">
                        <input
                          v-model="notificationSettings.nfc_taps"
                          type="checkbox"
                          class="toggle toggle-primary"
                          @change="updateNotificationSetting('nfc_taps')"
                        />
                      </label>
                    </div>
                  </div>
                  <div class="flex items-center justify-between">
                    <div>
                      <h3 class="text-sm font-medium text-secondary-900">
                        Weekly Reports
                      </h3>
                      <p class="text-sm text-secondary-600">
                        Receive weekly analytics summaries
                      </p>
                    </div>
                    <div class="form-control">
                      <label class="label cursor-pointer">
                        <input
                          v-model="notificationSettings.weekly_reports"
                          type="checkbox"
                          class="toggle toggle-primary"
                          @change="updateNotificationSetting('weekly_reports')"
                        />
                      </label>
                    </div>
                  </div>
                  <div class="flex items-center justify-between">
                    <div>
                      <h3 class="text-sm font-medium text-secondary-900">
                        Product Updates
                      </h3>
                      <p class="text-sm text-secondary-600">
                        Get notified about new features and updates
                      </p>
                    </div>
                    <div class="form-control">
                      <label class="label cursor-pointer">
                        <input
                          v-model="notificationSettings.product_updates"
                          type="checkbox"
                          class="toggle toggle-primary"
                          @change="updateNotificationSetting('product_updates')"
                        />
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Danger Zone Tab -->
          <div v-if="activeTab === 'danger'" class="space-y-6">
            <!-- Export Data Card -->
            <div
              class="bg-white rounded-lg shadow-sm border border-secondary-200"
            >
              <div class="px-6 py-4 border-b border-secondary-200">
                <h2 class="text-lg font-semibold text-secondary-900">
                  Export Data
                </h2>
                <p class="text-sm text-secondary-600">Download all your data</p>
              </div>
              <div class="p-6">
                <p class="text-sm text-secondary-600 mb-4">
                  Export all your profile data, analytics, and settings in JSON
                  format.
                </p>
                <button
                  @click="exportData"
                  :disabled="exportingData"
                  class="btn btn-outline"
                >
                  <span
                    v-if="exportingData"
                    class="loading loading-spinner loading-sm mr-2"
                  ></span>
                  <Icon
                    v-else
                    name="heroicons:arrow-down-tray"
                    class="h-4 w-4 mr-2"
                  />
                  {{ exportingData ? "Exporting..." : "Export My Data" }}
                </button>
              </div>
            </div>

            <!-- Delete Account Card -->
            <div class="bg-white rounded-lg shadow-sm border border-red-200">
              <div class="px-6 py-4 border-b border-red-200">
                <h2 class="text-lg font-semibold text-red-900">
                  Delete Account
                </h2>
                <p class="text-sm text-red-600">
                  Permanently delete your account and all data
                </p>
              </div>
              <div class="p-6">
                <div
                  class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4"
                >
                  <div class="flex">
                    <Icon
                      name="heroicons:exclamation-triangle"
                      class="h-5 w-5 text-red-400 mr-3 mt-0.5"
                    />
                    <div>
                      <h3 class="text-sm font-medium text-red-800">Warning</h3>
                      <p class="text-sm text-red-700 mt-1">
                        This action cannot be undone. All your data, profiles,
                        and NFC tags will be permanently deleted.
                      </p>
                    </div>
                  </div>
                </div>
                <button
                  @click="showDeleteConfirmation = true"
                  class="btn bg-red-600 text-white hover:bg-red-700"
                >
                  <Icon name="heroicons:trash" class="h-4 w-4 mr-2" />
                  Delete My Account
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Account Confirmation Modal -->
    <div
      v-if="showDeleteConfirmation"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg p-6 max-w-md mx-4">
        <div class="flex items-center mb-6">
          <div
            class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4"
          >
            <Icon
              name="heroicons:exclamation-triangle"
              class="h-6 w-6 text-red-600"
            />
          </div>
          <div>
            <h3 class="text-lg font-semibold text-secondary-900">
              Delete Account
            </h3>
            <p class="text-sm text-secondary-600">
              This action cannot be undone
            </p>
          </div>
        </div>

        <div class="mb-6">
          <p class="text-sm text-secondary-700 mb-4">
            To confirm deletion, please type <strong>DELETE</strong> in the
            field below:
          </p>
          <input
            v-model="deleteConfirmation"
            type="text"
            class="input input-bordered w-full"
            placeholder="Type DELETE to confirm"
          />
        </div>

        <div class="flex justify-end space-x-3">
          <button
            @click="showDeleteConfirmation = false"
            class="btn btn-outline"
          >
            Cancel
          </button>
          <button
            @click="deleteAccount"
            :disabled="deleteConfirmation !== 'DELETE' || deletingAccount"
            class="btn bg-red-600 text-white hover:bg-red-700 disabled:opacity-50"
          >
            <span
              v-if="deletingAccount"
              class="loading loading-spinner loading-sm mr-2"
            ></span>
            {{ deletingAccount ? "Deleting..." : "Delete Account" }}
          </button>
        </div>
      </div>
    </div>

    <!-- Success Toast -->
    <div
      v-if="showSuccessToast"
      class="fixed top-4 right-4 bg-green-100 border border-green-200 rounded-lg p-4 shadow-lg z-50"
    >
      <div class="flex items-center">
        <Icon
          name="heroicons:check-circle"
          class="h-5 w-5 text-green-600 mr-3"
        />
        <p class="text-sm font-medium text-green-800">{{ successMessage }}</p>
      </div>
    </div>

    <!-- Error Toast -->
    <div
      v-if="showErrorToast"
      class="fixed top-4 right-4 bg-red-100 border border-red-200 rounded-lg p-4 shadow-lg z-50"
    >
      <div class="flex items-center">
        <Icon name="heroicons:x-circle" class="h-5 w-5 text-red-600 mr-3" />
        <p class="text-sm font-medium text-red-800">{{ errorMessage }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";

// Meta
definePageMeta({
  layout: "user-dashboard",
  middleware: "auth",
});

// Store
const authStore = useAuthStore();

// Reactive data
const activeTab = ref("account"); // Default to account tab
const user = computed(() => authStore.user); // Use user from authStore
const accountImageTimestamp = ref(Date.now()); // Add timestamp to force image refresh

// Personal info form
const personalInfoForm = ref({
  first_name: "",
  last_name: "",
  email: "",
  phone: "",
  account_image: null,
});

const passwordForm = ref({
  current_password: "",
  new_password: "",
  confirm_password: "",
});

const securitySettings = ref({
  two_factor_sms: false,
  login_notifications: true,
});

const privacySettings = ref({
  public_profile: true,
  search_indexing: true,
  analytics_enabled: true,
  location_tracking: true,
});

const notificationSettings = ref({
  profile_views: true,
  nfc_taps: true,
  weekly_reports: true,
  product_updates: false,
});

const activeSessions = ref([]);
const slugStatus = ref("");
const isSlugValid = computed(() => slugStatus.value === "available");

// Loading states
const updatingPersonalInfo = ref(false);
const changingPassword = ref(false);
const exportingData = ref(false);
const deletingAccount = ref(false);

// Modal states
const showDeleteConfirmation = ref(false);
const deleteConfirmation = ref("");

// Toast states
const showSuccessToast = ref(false);
const showErrorToast = ref(false);
const successMessage = ref("");
const errorMessage = ref("");

// Settings tabs (removed separate profile tab, integrated with account)
const settingsTabs = [
  { id: "account", label: "Account", icon: "heroicons:user-circle" },
  { id: "security", label: "Security", icon: "heroicons:shield-check" },
  { id: "privacy", label: "Privacy", icon: "heroicons:eye-slash" },
  { id: "notifications", label: "Notifications", icon: "heroicons:bell" },
  {
    id: "danger",
    label: "Danger Zone",
    icon: "heroicons:exclamation-triangle",
  },
];

// Methods
const loadUserData = async () => {
  // User data is loaded via authStore and watch
  // This function is kept for loading additional settings if needed
  try {
    const { $api } = useNuxtApp();
    const response = await $api.get("/user/settings");

    // Load settings data if available
    if (response?.security_settings) {
      securitySettings.value = { ...response.security_settings };
    }
    if (response?.privacy_settings) {
      privacySettings.value = { ...response.privacy_settings };
    }
    if (response?.notification_settings) {
      notificationSettings.value = { ...response.notification_settings };
    }
  } catch (error) {
    // Silently fail if settings endpoint doesn't exist yet
    // User data is already loaded from authStore
    console.warn("Settings endpoint not available:", error);
  }
};

// Helper function to get account image URL with cache-busting timestamp
const getAccountImageUrl = (imageUrl) => {
  if (!imageUrl) return "";
  // Add timestamp to force browser to reload the image
  const separator = imageUrl.includes("?") ? "&" : "?";
  return `${imageUrl}${separator}t=${accountImageTimestamp.value}`;
};

const updatePersonalInfo = async () => {
  updatingPersonalInfo.value = true;
  try {
    const { $api } = useNuxtApp();

    // Update user personal information using Settings API
    const response = await $api.put("/settings/personal-info", {
      first_name: personalInfoForm.value.first_name,
      last_name: personalInfoForm.value.last_name,
      email: personalInfoForm.value.email,
      phone: personalInfoForm.value.phone,
    });

    // Update authStore user with new data
    if (response.success && response.user) {
      authStore.user = response.user;
    } else {
      // Refresh user data from server
      await authStore.fetchProfile();
    }

    showSuccess("Personal information updated successfully");
  } catch (error) {
    console.error("Error updating personal info:", error);

    // Handle validation errors
    if (error.response?.data?.errors) {
      // Get first validation error message
      const errors = error.response.data.errors;
      const firstError = Object.values(errors)[0];
      showError(Array.isArray(firstError) ? firstError[0] : firstError);

      // Revert email back to original value if email validation failed
      if (errors.email) {
        personalInfoForm.value.email = user.value?.email || "";
      }
    } else {
      showError(
        error.response?.data?.message || "Failed to update personal information"
      );
    }
  } finally {
    updatingPersonalInfo.value = false;
  }
};

// Handle account image upload success
const handleAccountImageUpload = async (data) => {
  console.log("Account image uploaded:", data);
  // Update authStore and form with new image URL
  if (data.url) {
    personalInfoForm.value.account_image = data.url;
    if (authStore.user) {
      authStore.user = {
        ...authStore.user,
        account_image: data.url,
      };
    }
    accountImageTimestamp.value = Date.now(); // Force image refresh
  }
  // Note: No need to fetchProfile() - the upload response already contains updated user data
  // and we've already updated authStore.user above
};

const loadActiveSessions = async () => {
  try {
    const response = await $fetch("/api/user/sessions");
    activeSessions.value = response.sessions;
  } catch (error) {
    console.error("Error loading sessions:", error);
  }
};

const changePassword = async () => {
  if (passwordForm.value.new_password !== passwordForm.value.confirm_password) {
    showError("New passwords do not match");
    return;
  }

  changingPassword.value = true;
  try {
    await $fetch("/api/user/password", {
      method: "PUT",
      body: passwordForm.value,
    });

    passwordForm.value = {
      current_password: "",
      new_password: "",
      confirm_password: "",
    };

    showSuccess("Password updated successfully");
  } catch (error) {
    console.error("Error changing password:", error);
    showError("Failed to change password");
  } finally {
    changingPassword.value = false;
  }
};

const updateSecuritySetting = async (setting) => {
  try {
    await $fetch("/api/user/security-settings", {
      method: "PUT",
      body: { [setting]: securitySettings.value[setting] },
    });
    showSuccess("Security settings updated");
  } catch (error) {
    console.error("Error updating security settings:", error);
    showError("Failed to update security settings");
  }
};

const updatePrivacySetting = async (setting) => {
  try {
    await $fetch("/api/user/privacy-settings", {
      method: "PUT",
      body: { [setting]: privacySettings.value[setting] },
    });
    showSuccess("Privacy settings updated");
  } catch (error) {
    console.error("Error updating privacy settings:", error);
    showError("Failed to update privacy settings");
  }
};

const updateNotificationSetting = async (setting) => {
  try {
    await $fetch("/api/user/notification-settings", {
      method: "PUT",
      body: { [setting]: notificationSettings.value[setting] },
    });
    showSuccess("Notification settings updated");
  } catch (error) {
    console.error("Error updating notification settings:", error);
    showError("Failed to update notification settings");
  }
};

const revokeSession = async (sessionId) => {
  try {
    await $fetch(`/api/user/sessions/${sessionId}`, {
      method: "DELETE",
    });
    await loadActiveSessions();
    showSuccess("Session revoked successfully");
  } catch (error) {
    console.error("Error revoking session:", error);
    showError("Failed to revoke session");
  }
};

const upgradePlan = () => {
  // Redirect to billing page or open upgrade modal
  navigateTo("/UserDashboard/billing/upgrade");
};

const manageBilling = () => {
  // Redirect to billing management
  navigateTo("/UserDashboard/billing");
};

const cancelSubscription = async () => {
  if (!confirm("Are you sure you want to cancel your subscription?")) return;

  try {
    await $fetch("/api/user/subscription/cancel", {
      method: "POST",
    });
    showSuccess("Subscription cancelled successfully");
    await loadUserData();
  } catch (error) {
    console.error("Error cancelling subscription:", error);
    showError("Failed to cancel subscription");
  }
};

const exportData = async () => {
  exportingData.value = true;
  try {
    const response = await $fetch("/api/user/export", {
      method: "POST",
    });

    // Create download link
    const blob = new Blob([JSON.stringify(response.data, null, 2)], {
      type: "application/json",
    });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = `nfc-card-data-${
      new Date().toISOString().split("T")[0]
    }.json`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);

    showSuccess("Data exported successfully");
  } catch (error) {
    console.error("Error exporting data:", error);
    showError("Failed to export data");
  } finally {
    exportingData.value = false;
  }
};

const deleteAccount = async () => {
  deletingAccount.value = true;
  try {
    await $fetch("/api/user/account", {
      method: "DELETE",
    });

    // Redirect to goodbye page
    await navigateTo("/goodbye");
  } catch (error) {
    console.error("Error deleting account:", error);
    showError("Failed to delete account");
    deletingAccount.value = false;
  }
};

const getDeviceIcon = (deviceType) => {
  const icons = {
    desktop: "heroicons:computer-desktop",
    mobile: "heroicons:device-phone-mobile",
    tablet: "heroicons:device-tablet",
  };
  return icons[deviceType] || "heroicons:computer-desktop";
};

const formatDate = (date) => {
  if (!date) return "Never";
  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

const showSuccess = (message) => {
  successMessage.value = message;
  showSuccessToast.value = true;
  setTimeout(() => {
    showSuccessToast.value = false;
  }, 3000);
};

const showError = (message) => {
  errorMessage.value = message;
  showErrorToast.value = true;
  setTimeout(() => {
    showErrorToast.value = false;
  }, 3000);
};

// Lifecycle
onMounted(() => {
  // Initialize form with user data
  if (user.value) {
    personalInfoForm.value = {
      first_name: user.value.first_name || "",
      last_name: user.value.last_name || "",
      email: user.value.email || "",
      phone: user.value.phone || "",
      account_image: user.value.account_image || null,
    };
  }

  // loadUserData(); // Commented out - endpoint not implemented yet
  loadActiveSessions();
});

// Watch user changes to update form
watch(
  user,
  (newUser) => {
    if (newUser) {
      // Only update if values have actually changed to avoid overwriting recent uploads
      if (personalInfoForm.value.first_name !== newUser.first_name) {
        personalInfoForm.value.first_name = newUser.first_name || "";
      }
      if (personalInfoForm.value.last_name !== newUser.last_name) {
        personalInfoForm.value.last_name = newUser.last_name || "";
      }
      if (personalInfoForm.value.email !== newUser.email) {
        personalInfoForm.value.email = newUser.email || "";
      }
      if (personalInfoForm.value.phone !== newUser.phone) {
        personalInfoForm.value.phone = newUser.phone || "";
      }
      // Only update account_image if it's actually different and not null
      // This prevents overwriting a just-uploaded image
      if (
        newUser.account_image &&
        personalInfoForm.value.account_image !== newUser.account_image
      ) {
        personalInfoForm.value.account_image = newUser.account_image;
      } else if (
        !personalInfoForm.value.account_image &&
        !newUser.account_image
      ) {
        personalInfoForm.value.account_image = null;
      }
    }
  },
  { immediate: true }
);
</script>

<style scoped>
.btn {
  @apply px-4 py-2 rounded-lg font-medium transition-all duration-200 inline-flex items-center justify-center;
}

.btn-primary {
  @apply bg-primary-600 text-white hover:bg-primary-700 disabled:opacity-50;
}

.btn-outline {
  @apply border border-secondary-300 text-secondary-700 hover:bg-secondary-50 disabled:opacity-50;
}

.btn-sm {
  @apply px-3 py-1.5 text-sm;
}

.input {
  @apply px-3 py-2 border border-secondary-300 rounded-lg focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500;
}

.input-bordered {
  @apply border-secondary-300;
}

.textarea {
  @apply px-3 py-2 border border-secondary-300 rounded-lg focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 resize-none;
}

.textarea-bordered {
  @apply border-secondary-300;
}

.toggle {
  @apply relative inline-flex h-6 w-11 items-center rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2;
  background-color: #d1d5db;
}

.toggle:checked {
  @apply bg-primary-600;
}

.toggle::before {
  @apply inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out;
  content: "";
  transform: translateX(0);
}

.toggle:checked::before {
  transform: translateX(20px);
}

.toggle-primary:checked {
  @apply bg-primary-600;
}

.form-control .label {
  @apply cursor-pointer;
}

.loading {
  @apply animate-spin;
}

.loading-spinner {
  @apply border-2 border-current border-t-transparent rounded-full;
}

.loading-sm {
  @apply w-4 h-4;
}
</style>
