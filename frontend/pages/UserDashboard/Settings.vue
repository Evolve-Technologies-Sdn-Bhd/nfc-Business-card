<!-- pages/UserDashboard/Settings.vue -->
<template>
  <div>
    <!-- Header Section -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-secondary-900">
        Account Settings
      </h1>
      <p class="mt-2 text-secondary-600">
        Manage your account, security, and preferences
      </p>
    </div>

    <!-- Main Layout -->
    <div class="flex flex-col lg:flex-row gap-8">
      <!-- Sidebar Navigation -->
      <div class="w-full lg:w-64 flex-shrink-0">
        <div class="card">
          <div class="card-body">
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
      </div>

      <!-- Main Content Area -->
      <div class="flex-1">
          <!-- Account Settings Tab -->
          <div v-if="activeTab === 'account'" class="space-y-6">
            <!-- Personal Information Card -->
            <div
              class="card"
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
                      <div class="flex items-center space-x-6">
                        <div class="shrink-0">
                          <img 
                            :src="personalInfoForm.account_image || '/default-avatar.png'" 
                            :alt="`${personalInfoForm.first_name} ${personalInfoForm.last_name}`"
                            class="h-16 w-16 object-cover rounded-full border-2 border-gray-300"
                          />
                        </div>
                        <div class="flex-1">
                          <label class="block text-sm font-medium text-secondary-700 mb-2">
                            Account Picture
                          </label>
                          <div class="flex items-center space-x-3">
                            <input
                              type="file"
                              ref="imageInput"
                              @change="handleImageUpload"
                              accept="image/*"
                              class="hidden"
                            />
                            <button
                              type="button"
                              @click="$refs.imageInput?.click()"
                              class="btn btn-outline btn-sm"
                            >
                              <Icon name="heroicons:camera" class="h-4 w-4 mr-2" />
                              Change Photo
                            </button>
                            <button
                              v-if="personalInfoForm.account_image"
                              type="button"
                              @click="removeImage"
                              class="btn btn-outline btn-sm text-red-600 border-red-300 hover:bg-red-50"
                            >
                              <Icon name="heroicons:trash" class="h-4 w-4 mr-2" />
                              Remove
                            </button>
                          </div>
                          <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF or WebP. Max 5MB</p>
                        </div>
                      </div>
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

            <!-- Profile URLs Card -->
            <div
              class="card"
            >
              <div class="px-6 py-4 border-b border-secondary-200">
                <h2 class="text-lg font-semibold text-secondary-900">
                  Profile URLs
                </h2>
                <p class="text-sm text-secondary-600">
                  Your NFC card profile URLs
                </p>
              </div>
              <div class="p-6">
                <div v-if="userNfcCards && userNfcCards.length > 0" class="space-y-4">
                  <div 
                    v-for="card in userNfcCards" 
                    :key="card.id"
                    class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border"
                  >
                    <div class="flex-1">
                      <div class="flex items-center gap-3 mb-2">
                        <h3 class="text-sm font-medium text-secondary-900">
                          {{ card.nfc_card_id || `Card #${card.id}` }}
                        </h3>
                        <span 
                          :class="card.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                          class="px-2 py-1 text-xs font-medium rounded-full"
                        >
                          {{ card.status }}
                        </span>
                        <span 
                          :class="card.has_landing_page ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'"
                          class="px-2 py-1 text-xs font-medium rounded-full"
                        >
                          {{ card.has_landing_page ? 'Profile Ready' : 'Profile Needed' }}
                        </span>
                      </div>
                      <div class="space-y-1 mb-2">
                        <div class="flex items-center gap-2">
                          <Icon name="heroicons:link" class="h-4 w-4 text-gray-400" />
                          <span class="text-xs text-gray-500">Live URL:</span>
                          <code class="text-sm text-blue-600 bg-blue-50 px-2 py-1 rounded">
                            {{ getCardDisplayLiveUrl(card) }}
                          </code>
                        </div>
                        <div class="flex items-center gap-2">
                          <Icon name="heroicons:eye" class="h-4 w-4 text-gray-400" />
                          <span class="text-xs text-gray-500">Preview URL:</span>
                          <code class="text-sm text-purple-600 bg-purple-50 px-2 py-1 rounded">
                            {{ getCardDisplayPreviewUrl(card) }}
                          </code>
                        </div>
                      </div>
                      <p v-if="!card.has_landing_page" class="text-xs text-yellow-600">
                        <Icon name="heroicons:exclamation-triangle" class="h-3 w-3 inline mr-1" />
                        This card needs a profile to display content
                      </p>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                      <div class="dropdown dropdown-top">
                        <button tabindex="0" class="btn btn-outline btn-sm" title="Copy URL">
                          <Icon name="heroicons:clipboard" class="h-4 w-4" />
                        </button>
                        <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52">
                          <li><a @click="copyProfileUrl(card, false)">Copy Live URL</a></li>
                          <li><a @click="copyProfileUrl(card, true)">Copy Preview URL</a></li>
                        </ul>
                      </div>
                      <button
                        @click="openProfileUrl(card)"
                        class="btn btn-outline btn-sm"
                        title="Open in new tab"
                      >
                        <Icon name="heroicons:arrow-top-right-on-square" class="h-4 w-4" />
                      </button>
                      <button
                        @click="openProfilePreview(card)"
                        class="btn btn-outline btn-sm"
                        title="Preview mode"
                      >
                        <Icon name="heroicons:eye" class="h-4 w-4" />
                      </button>
                      <button
                        @click="editCardProfile(card.nfc_card_id)"
                        class="btn btn-primary btn-sm"
                        title="Edit profile"
                      >
                        <Icon name="heroicons:pencil" class="h-4 w-4" />
                      </button>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-8">
                  <Icon name="heroicons:credit-card" class="h-12 w-12 mx-auto mb-3 text-gray-300" />
                  <p class="text-sm text-gray-500 mb-4">No NFC cards found</p>
                  <button
                    @click="navigateTo('/UserDashboard/UserManagement/BusinessPlanUser/BusinessCardManagement')"
                    class="btn btn-primary btn-sm"
                  >
                    <Icon name="heroicons:plus" class="h-4 w-4 mr-2" />
                    Order NFC Cards
                  </button>
                </div>
              </div>
            </div>

            <!-- Account Information Card -->
            <div
              class="card"
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
              class="card"
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
                <div class="flex items-center justify-between mb-6">
                  <div>
                    <div class="flex items-center gap-3 mb-2">
                      <h3 class="text-lg font-medium text-secondary-900">
                        {{ getSubscriptionDisplayName(user?.subscription_plan) }}
                      </h3>
                      <span 
                        :class="getSubscriptionBadgeClass(user?.subscription_plan)"
                        class="px-2 py-1 text-xs font-medium rounded-full"
                      >
                        {{ user?.subscription_plan?.toUpperCase() || 'FREE' }}
                      </span>
                    </div>
                    <p class="text-sm text-secondary-600">
                      {{ getSubscriptionDescription(user?.subscription_plan) }}
                    </p>
                    <div class="mt-2 text-xs text-secondary-500">
                      <p v-if="user?.subscription_expires_at">
                        <Icon name="heroicons:calendar" class="h-3 w-3 inline mr-1" />
                        {{ user?.subscription_plan === 'free' ? 'No expiration' : `Expires: ${formatDate(user.subscription_expires_at)}` }}
                      </p>
                      <p v-if="user?.subscription_plan !== 'free' && user?.subscription_status">
                        <Icon name="heroicons:information-circle" class="h-3 w-3 inline mr-1" />
                        Status: {{ user.subscription_status }}
                      </p>
                    </div>
                  </div>
                  <div class="text-right">
                    <p class="text-2xl font-bold text-secondary-900">
                      {{ getSubscriptionPrice(user?.subscription_plan) }}
                      <span class="text-sm font-normal text-secondary-500">/month</span>
                    </p>
                  </div>
                </div>

                <!-- Subscription Features -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                  <h4 class="text-sm font-medium text-secondary-900 mb-3">Current Plan Features</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div 
                      v-for="feature in getSubscriptionFeatures(user?.subscription_plan)" 
                      :key="feature"
                      class="flex items-center text-sm text-secondary-700"
                    >
                      <Icon name="heroicons:check" class="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                      {{ feature }}
                    </div>
                  </div>
                </div>

                <!-- Available Plans (if user is on free plan) -->
                <div v-if="user?.subscription_plan === 'free' && planPrices.length > 1" class="mb-6">
                  <h4 class="text-sm font-medium text-secondary-900 mb-3">Available Plans</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div 
                      v-for="plan in planPrices.filter(p => p.plan_type !== 'free')" 
                      :key="plan.id"
                      class="p-4 border border-secondary-200 rounded-lg hover:border-primary-300 transition-colors"
                    >
                      <div class="flex items-center justify-between mb-2">
                        <h5 class="font-medium text-secondary-900 capitalize">{{ plan.plan_type }}</h5>
                        <span class="text-lg font-bold text-primary-600">
                          {{ plan.currency === 'MYR' ? 'RM' : plan.currency }} {{ plan.price }}
                          <span class="text-sm font-normal text-secondary-500">/month</span>
                        </span>
                      </div>
                      <p class="text-sm text-secondary-600 mb-3">{{ plan.description }}</p>
                      <div class="space-y-1">
                        <div 
                          v-for="feature in plan.features?.slice(0, 3)" 
                          :key="feature"
                          class="flex items-center text-xs text-secondary-600"
                        >
                          <Icon name="heroicons:check" class="h-3 w-3 text-green-500 mr-1 flex-shrink-0" />
                          {{ feature }}
                        </div>
                        <div v-if="plan.features?.length > 3" class="text-xs text-secondary-500">
                          +{{ plan.features.length - 3 }} more features
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="flex flex-wrap gap-3">
                  <button
                    v-if="user?.subscription_plan === 'free'"
                    @click="upgradePlan"
                    class="btn btn-primary"
                  >
                    <Icon
                      name="heroicons:arrow-trending-up"
                      class="h-4 w-4 mr-2"
                    />
                    Upgrade to Business Plan
                  </button>
                  <button 
                    v-else 
                    @click="manageBilling" 
                    class="btn btn-outline"
                  >
                    <Icon name="heroicons:credit-card" class="h-4 w-4 mr-2" />
                    Manage Billing
                  </button>
                  <button
                    v-if="user?.subscription_plan !== 'free'"
                    @click="viewInvoices"
                    class="btn btn-outline"
                  >
                    <Icon name="heroicons:document-text" class="h-4 w-4 mr-2" />
                    View Invoices
                  </button>
                  <button
                    v-if="user?.subscription_plan !== 'free'"
                    @click="cancelSubscription"
                    class="btn btn-outline text-red-600 border-red-300 hover:bg-red-50"
                  >
                    <Icon name="heroicons:x-circle" class="h-4 w-4 mr-2" />
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
              class="card"
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
                      <div class="relative">
                        <input
                          v-model="passwordForm.current_password"
                          :type="showCurrentPassword ? 'text' : 'password'"
                          required
                          class="input input-bordered w-full pr-10"
                          placeholder="Enter current password"
                        />
                        <button
                          type="button"
                          @click="showCurrentPassword = !showCurrentPassword"
                          class="absolute inset-y-0 right-0 pr-3 flex items-center text-secondary-400 hover:text-secondary-600"
                        >
                          <Icon :name="showCurrentPassword ? 'heroicons:eye-slash' : 'heroicons:eye'" class="h-5 w-5" />
                        </button>
                      </div>
                    </div>
                    <div>
                      <label
                        class="block text-sm font-medium text-secondary-700 mb-2"
                        >New Password</label
                      >
                      <div class="relative">
                        <input
                          v-model="passwordForm.new_password"
                          :type="showNewPassword ? 'text' : 'password'"
                          required
                          class="input input-bordered w-full pr-10"
                          placeholder="Enter new password"
                        />
                        <button
                          type="button"
                          @click="showNewPassword = !showNewPassword"
                          class="absolute inset-y-0 right-0 pr-3 flex items-center text-secondary-400 hover:text-secondary-600"
                        >
                          <Icon :name="showNewPassword ? 'heroicons:eye-slash' : 'heroicons:eye'" class="h-5 w-5" />
                        </button>
                      </div>
                    </div>
                    <div>
                      <label
                        class="block text-sm font-medium text-secondary-700 mb-2"
                        >Confirm New Password</label
                      >
                      <div class="relative">
                        <input
                          v-model="passwordForm.confirm_password"
                          :type="showConfirmNewPassword ? 'text' : 'password'"
                          required
                          class="input input-bordered w-full pr-10"
                          placeholder="Confirm new password"
                        />
                        <button
                          type="button"
                          @click="showConfirmNewPassword = !showConfirmNewPassword"
                          class="absolute inset-y-0 right-0 pr-3 flex items-center text-secondary-400 hover:text-secondary-600"
                        >
                          <Icon :name="showConfirmNewPassword ? 'heroicons:eye-slash' : 'heroicons:eye'" class="h-5 w-5" />
                        </button>
                      </div>
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
              class="card"
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

            <!-- Linked Accounts Card -->
            <div class="card">
              <div class="px-6 py-4 border-b border-secondary-200">
                <h2 class="text-lg font-semibold text-secondary-900">
                  Linked Accounts
                </h2>
                <p class="text-sm text-secondary-600">
                  Connect third-party accounts for easier sign-in
                </p>
              </div>
              <div class="p-6">
                <div class="space-y-4">
                  <!-- Google Account -->
                  <div class="flex items-center justify-between p-4 border border-secondary-200 rounded-lg">
                    <div class="flex items-center">
                      <div class="w-10 h-10 bg-white border border-secondary-200 rounded-lg flex items-center justify-center mr-4">
                        <Icon name="logos:google-icon" class="h-5 w-5" />
                      </div>
                      <div>
                        <h3 class="text-sm font-medium text-secondary-900">Google</h3>
                        <p v-if="linkedAccounts.google" class="text-sm text-green-600">
                          Connected as {{ linkedAccounts.google.email }}
                        </p>
                        <p v-else class="text-sm text-secondary-500">
                          Not connected
                        </p>
                      </div>
                    </div>
                    <button
                      v-if="linkedAccounts.google"
                      @click="confirmUnlinkAccount('google')"
                      :disabled="unlinkingAccount"
                      class="btn btn-outline btn-sm text-red-600 border-red-300 hover:bg-red-50"
                    >
                      <Icon name="heroicons:link-slash" class="h-4 w-4 mr-1" />
                      Unlink
                    </button>
                    <button
                      v-else
                      @click="openLinkAccountModal('google')"
                      :disabled="linkingAccount"
                      class="btn btn-outline btn-sm"
                    >
                      <Icon name="heroicons:link" class="h-4 w-4 mr-1" />
                      Link Account
                    </button>
                  </div>

                  <!-- Apple Account -->
                  <div class="flex items-center justify-between p-4 border border-secondary-200 rounded-lg">
                    <div class="flex items-center">
                      <div class="w-10 h-10 bg-black rounded-lg flex items-center justify-center mr-4">
                        <Icon name="logos:apple" class="h-5 w-5 text-white" />
                      </div>
                      <div>
                        <h3 class="text-sm font-medium text-secondary-900">Apple</h3>
                        <p v-if="linkedAccounts.apple" class="text-sm text-green-600">
                          Connected as {{ linkedAccounts.apple.email || 'Private Email' }}
                        </p>
                        <p v-else class="text-sm text-secondary-500">
                          Not connected
                        </p>
                      </div>
                    </div>
                    <button
                      v-if="linkedAccounts.apple"
                      @click="confirmUnlinkAccount('apple')"
                      :disabled="unlinkingAccount"
                      class="btn btn-outline btn-sm text-red-600 border-red-300 hover:bg-red-50"
                    >
                      <Icon name="heroicons:link-slash" class="h-4 w-4 mr-1" />
                      Unlink
                    </button>
                    <button
                      v-else
                      @click="openLinkAccountModal('apple')"
                      :disabled="linkingAccount"
                      class="btn btn-outline btn-sm"
                    >
                      <Icon name="heroicons:link" class="h-4 w-4 mr-1" />
                      Link Account
                    </button>
                  </div>
                </div>

                <!-- Info notice -->
                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                  <div class="flex items-start">
                    <Icon name="heroicons:information-circle" class="h-5 w-5 text-blue-500 mt-0.5 mr-2 flex-shrink-0" />
                    <p class="text-sm text-blue-700">
                      Linking accounts allows you to sign in with either your password or the linked provider. 
                      You must verify your password before linking a new account.
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Link Account Modal -->
            <div v-if="showLinkAccountModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
              <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4">
                <div class="text-center mb-6">
                  <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <Icon :name="linkAccountProvider === 'google' ? 'logos:google-icon' : 'logos:apple'" class="h-8 w-8" />
                  </div>
                  <h3 class="text-xl font-bold text-secondary-900 mb-2">
                    Link {{ linkAccountProvider === 'google' ? 'Google' : 'Apple' }} Account
                  </h3>
                  <p class="text-secondary-600">
                    Enter your current password to verify account ownership before linking.
                  </p>
                </div>

                <form @submit.prevent="linkAccount">
                  <div class="mb-4">
                    <label class="block text-sm font-medium text-secondary-700 mb-2">
                      Current Password
                    </label>
                    <div class="relative">
                      <input
                        v-model="linkAccountPassword"
                        :type="showLinkAccountPassword ? 'text' : 'password'"
                        required
                        class="input input-bordered w-full pr-10"
                        placeholder="Enter your password"
                        :disabled="linkingAccount"
                      />
                      <button
                        type="button"
                        @click="showLinkAccountPassword = !showLinkAccountPassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-secondary-400 hover:text-secondary-600"
                      >
                        <Icon :name="showLinkAccountPassword ? 'heroicons:eye-slash' : 'heroicons:eye'" class="h-5 w-5" />
                      </button>
                    </div>
                    <p v-if="linkAccountError" class="text-sm text-red-600 mt-1">
                      {{ linkAccountError }}
                    </p>
                  </div>

                  <div class="flex gap-3">
                    <button
                      type="button"
                      @click="closeLinkAccountModal"
                      class="btn btn-outline flex-1"
                      :disabled="linkingAccount"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      class="btn btn-primary flex-1"
                      :disabled="linkingAccount || !linkAccountPassword"
                    >
                      <span v-if="linkingAccount" class="loading loading-spinner loading-sm mr-2"></span>
                      {{ linkingAccount ? 'Verifying...' : 'Continue to Link' }}
                    </button>
                  </div>
                </form>
              </div>
            </div>

            <!-- Active Sessions Card -->
            <div
              class="card"
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
              class="card"
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
              class="card"
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
              class="card"
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
              class="card"
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
  middleware: ["auth"],
});

// Store
const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();

// Reactive data
const activeTab = ref("account"); // Default to account tab
const user = computed(() => authStore.user); // Use user from authStore
const accountImageTimestamp = ref(Date.now()); // Add timestamp to force image refresh
const baseUrl = computed(() => {
  if (process.client) {
    return window.location.origin;
  }
  return 'https://nfccard.app'; // fallback for SSR
});

// Development mode check
const isDevelopment = computed(() => {
  if (process.client) {
    return window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1' || window.location.hostname.includes('192.168') || window.location.hostname.includes('172.19');
  }
  return false;
});

// Auth token check
const authToken = computed(() => {
  if (process.client) {
    // Check multiple possible token storage locations
    return localStorage.getItem('auth_token') || 
           localStorage.getItem('token') || 
           localStorage.getItem('access_token') ||
           sessionStorage.getItem('auth_token') ||
           sessionStorage.getItem('token');
  }
  return null;
});

// Safe storage status check for template
const getStorageStatus = (storageType, key) => {
  if (process.client) {
    try {
      const storage = storageType === 'localStorage' ? localStorage : sessionStorage;
      return storage.getItem(key) ? '✅' : '❌';
    } catch (error) {
      return '❌';
    }
  }
  return 'SSR';
};

// Image upload
const imageInput = ref(null);
const uploadingImage = ref(false);

// NFC Cards for profile URLs
const userNfcCards = ref([]);

// Plan prices from admin management
const planPrices = ref([]);

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

// ---------------- 2FA / TOTP State ---------------- //
const twoFactor = ref({
  loading: false,
  showSetup: false,
  showDisable: false,
  secret: '',
  qrData: '',
  recoveryCodes: [],
  recoveryCodeInput: '',
  otpInput: '',
  passwordInput: '',
  recoveryCodesShown: false,
});

const loadingSessions = ref(false);
const loadingLogoutEverywhere = ref(false);
const revokingSessionId = ref(null);

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

// Linked accounts state
const linkedAccounts = ref({
  google: null,
  apple: null,
});
const showLinkAccountModal = ref(false);
const linkAccountProvider = ref('');
const linkAccountPassword = ref('');
const linkAccountError = ref('');
const linkingAccount = ref(false);
const unlinkingAccount = ref(false);

// Profile URLs management - no longer needed since URLs are based on NFC card IDs

// Loading states
const updatingPersonalInfo = ref(false);
const changingPassword = ref(false);
const exportingData = ref(false);
const deletingAccount = ref(false);

// Password visibility toggles
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmNewPassword = ref(false);
const showLinkAccountPassword = ref(false);

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
  // Skip API calls if no auth token to avoid permission errors
  if (!authToken.value) {
    console.log("⚠️ No auth token, using default settings");
    return;
  }

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
    console.log("✅ Loaded user settings");
  } catch (error) {
    // Silently fail if settings endpoint doesn't exist yet
    // User data is already loaded from authStore
    console.log("ℹ️ Settings endpoint not available, using defaults");
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

// Handle image upload
const handleImageUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  // Validate file size (5MB)
  if (file.size > 5 * 1024 * 1024) {
    showError('File size must be less than 5MB');
    return;
  }

  // Validate file type
  if (!file.type.startsWith('image/')) {
    showError('Please select a valid image file');
    return;
  }

  uploadingImage.value = true;
  try {
    const { $api } = useNuxtApp();
    const formData = new FormData();
    formData.append('image', file);

    const response = await $api.post('/settings/upload-account-image', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    if (response.success && response.url) {
      personalInfoForm.value.account_image = response.url;
      if (authStore.user) {
        authStore.user = {
          ...authStore.user,
          account_image: response.url,
        };
      }
      accountImageTimestamp.value = Date.now();
      showSuccess('Profile picture updated successfully');
    }
  } catch (error) {
    console.error('Error uploading image:', error);
    showError(error.data?.message || 'Failed to upload image');
  } finally {
    uploadingImage.value = false;
    // Clear the input
    if (imageInput.value) {
      imageInput.value.value = '';
    }
  }
};

// Remove image
const removeImage = async () => {
  try {
    const { $api } = useNuxtApp();
    await $api.delete('/settings/delete-account-image');
    
    personalInfoForm.value.account_image = null;
    if (authStore.user) {
      authStore.user = {
        ...authStore.user,
        account_image: null,
      };
    }
    showSuccess('Profile picture removed successfully');
  } catch (error) {
    console.error('Error removing image:', error);
    showError('Failed to remove image');
  }
};

// Check if user is authenticated and has proper access
const checkUserPermissions = () => {
  if (!authStore.user) {
    console.log("❌ User not authenticated in authStore");
    
    // Check if we have a token but no user data
    if (authToken.value) {
      console.log("🔄 Token exists but no user data - may need to refresh user info");
      // Don't show error immediately, user data might be loading
      return true;
    } else {
      console.log("❌ No authentication token found");
      showError("Please log in to access this page");
      return false;
    }
  }
  
  console.log("✅ User authenticated:", authStore.user.email);
  return true;
};

const loadActiveSessions = async () => {
  if (!authStore.user || !authToken.value) {
    activeSessions.value = [
      {
        id: 1,
        name: 'Current Session',
        device: 'Current Device',
        platform: null,
        browser: null,
        ip_address: null,
        last_seen_at: new Date().toISOString(),
        is_current: true,
      },
    ];
    return;
  }

  loadingSessions.value = true;
  try {
    const { $api } = useNuxtApp();
    const response = await $api.get("/user/sessions");

    if (response?.success && Array.isArray(response.sessions)) {
      activeSessions.value = response.sessions;
      console.log("✅ Loaded active sessions:", activeSessions.value.length);
    } else {
      throw new Error("Invalid response format");
    }
  } catch (error) {
    console.error("Error loading sessions:", error);
    // Graceful fallback: still show at least "current session" indicator
    activeSessions.value = [
      {
        id: 'fallback-current',
        type: 'token',
        name: 'Current Session',
        device: 'Current Device',
        platform: null,
        browser: null,
        ip_address: null,
        last_seen_at: new Date().toISOString(),
        created_at: new Date().toISOString(),
        is_current: true,
      },
    ];
  } finally {
    loadingSessions.value = false;
  }
};

/**
 * Map backend session fields → icon for visual display
 */
const sessionDeviceIcon = (session) => {
  const d = (session.device || session.platform || '').toString().toLowerCase();
  if (d.includes('iphone') || d.includes('mobile') || d.includes('android')) {
    return 'heroicons:device-phone-mobile';
  }
  if (d.includes('ipad') || d.includes('tablet')) {
    return 'heroicons:device-tablet';
  }
  return 'heroicons:computer-desktop';
};

const revokeSession = async (sessionId) => {
  if (revokingSessionId.value) return;
  revokingSessionId.value = sessionId;
  try {
    const { $api } = useNuxtApp();
    const resp = await $api.delete(`/user/sessions/${sessionId}`);
    if (resp?.success) {
      showSuccess(resp.message || 'Sesi telah dilog keluar.');
      activeSessions.value = activeSessions.value.filter(s => s.id !== sessionId);
    } else {
      showError(resp?.message || 'Gagal membatalkan sesi.');
    }
  } catch (e) {
    console.error('Revoke session failed', e);
    showError(e.data?.message || e.message || 'Gagal membatalkan sesi.');
  } finally {
    revokingSessionId.value = null;
  }
};

const logoutEverywhere = async () => {
  if (loadingLogoutEverywhere.value) return;
  if (!confirm('Log keluar dari SEMUA peranti? Anda perlu login semula pada peranti ini.')) return;
  loadingLogoutEverywhere.value = true;
  try {
    const { $api } = useNuxtApp();
    const resp = await $api.post('/user/logout-everywhere');
    if (resp?.success) {
      showSuccess('Anda telah log keluar dari semua peranti.');
      await authStore.logout();
      await navigateTo('/UserAccount/login');
    } else {
      showError(resp?.message || 'Gagal.');
    }
  } catch (e) {
    console.error('logoutEverywhere failed', e);
    showError(e.data?.message || 'Gagal logout semua peranti.');
  } finally {
    loadingLogoutEverywhere.value = false;
  }
};

const setup2FA = async () => {
  twoFactor.value.loading = true;
  try {
    const { $api } = useNuxtApp();
    const resp = await $api.post('/user/2fa/setup');
    if (resp?.success) {
      twoFactor.value.secret = resp.secret || '';
      twoFactor.value.qrData = resp.qr_data || '';
      twoFactor.value.recoveryCodes = resp.recovery_codes || [];
      twoFactor.value.otpInput = '';
      twoFactor.value.recoveryCodesShown = true;
      twoFactor.value.showSetup = true;
      twoFactor.value.showDisable = false;
    } else {
      showError(resp?.message || 'Gagal setup 2FA.');
    }
  } catch (e) {
    console.error('setup2FA failed', e);
    showError(e.data?.message || 'Gagal menjana setup 2FA.');
  } finally {
    twoFactor.value.loading = false;
  }
};

const close2FASetup = () => {
  twoFactor.value.showSetup = false;
  twoFactor.value.otpInput = '';
  twoFactor.value.passwordInput = '';
};

const confirm2FA = async () => {
  if (!/^[0-9]{6}$/.test(twoFactor.value.otpInput)) {
    showError('Masukkan 6 digit kod OTP dari Authenticator app anda.');
    return;
  }
  twoFactor.value.loading = true;
  try {
    const { $api } = useNuxtApp();
    const resp = await $api.post('/user/2fa/confirm', { code: twoFactor.value.otpInput });
    if (resp?.success) {
      twoFactor.value.recoveryCodes = resp.recovery_codes || twoFactor.value.recoveryCodes;
      // Mark 2FA enabled at user object
      if (user.value) {
        user.value.two_factor_enabled = true;
      }
      // Keep modal open a moment longer so user sees success + copies recovery codes
      showSuccess('2FA berjaya diaktifkan. Simpan recovery codes di tempat selamat.');
      setTimeout(() => {
        twoFactor.value.showSetup = false;
        twoFactor.value.otpInput = '';
      }, 1500);
    } else {
      showError(resp?.message || 'Kod OTP tidak tepat.');
    }
  } catch (e) {
    console.error('confirm2FA failed', e);
    showError(e.data?.message || 'Gagal aktifkan 2FA. Cuba lagi.');
  } finally {
    twoFactor.value.loading = false;
  }
};

const copyText = (text, label) => {
  if (navigator.clipboard && window.isSecureContext) {
    navigator.clipboard.writeText(text).then(() => {
      showSuccess(`${label} disalin ke clipboard.`);
    }).catch(() => {
      // Fallback
      fallbackCopy(text);
      showSuccess(`${label} disalin ke clipboard.`);
    });
  } else {
    fallbackCopy(text);
    showSuccess(`${label} disalin ke clipboard.`);
  }
};

const fallbackCopy = (text) => {
  try {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.left = '-9999px';
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
  } catch {}
};

const downloadRecoveryCodes = () => {
  const codes = twoFactor.value.recoveryCodes || [];
  const lines = [
    'NFCGo Business Card — 2FA Recovery Codes',
    `Dijana: ${new Date().toLocaleString()}`,
    '',
    ...codes.map((c, i) => `${String(i + 1).padStart(2, '0')}. ${c}`),
    '',
    'GUNAKAN SATU SAHAJA BILA PERLU. SETIAP CODE SEKALI GUNA.',
  ];
  const blob = new Blob([lines.join('\n')], { type: 'text/plain;charset=utf-8' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'nfcgo-2fa-recovery-codes.txt';
  document.body.appendChild(a);
  a.click();
  setTimeout(() => {
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  }, 100);
};

const disable2FA = async () => {
  const needPassword = !twoFactor.value.passwordInput && !twoFactor.value.recoveryCodeInput;
  if (needPassword && !twoFactor.value.passwordInput) {
    showError('Masukkan kata laluan anda untuk sahkan nyahaktif 2FA.');
    return;
  }
  twoFactor.value.loading = true;
  try {
    const payload = {};
    if (twoFactor.value.passwordInput) payload.password = twoFactor.value.passwordInput;
    if (twoFactor.value.recoveryCodeInput) payload.recovery_code = twoFactor.value.recoveryCodeInput;
    const { $api } = useNuxtApp();
    const resp = await $api.post('/user/2fa/disable', payload);
    if (resp?.success) {
      if (user.value) user.value.two_factor_enabled = false;
      twoFactor.value.showDisable = false;
      twoFactor.value.passwordInput = '';
      twoFactor.value.recoveryCodeInput = '';
      showSuccess(resp.message || '2FA berjaya dinyahaktifkan.');
    } else {
      showError(resp?.message || 'Gagal nyahaktifkan 2FA.');
    }
  } catch (e) {
    console.error('disable2FA failed', e);
    showError(e.data?.message || 'Gagal nyahaktifkan 2FA.');
  } finally {
    twoFactor.value.loading = false;
  }
};

const regenerateRecoveryCodes = async () => {
  if (!confirm('Regenerate 8 recovery codes baru? Semua recovery codes sedia ada akan batal serta-merta.')) return;
  twoFactor.value.loading = true;
  try {
    const { $api } = useNuxtApp();
    const resp = await $api.post('/user/2fa/recovery-codes/regenerate');
    if (resp?.success) {
      twoFactor.value.recoveryCodes = resp.recovery_codes || [];
      twoFactor.value.recoveryCodesShown = true;
      showSuccess(resp.message || 'Recovery codes baharu dijana.');
    } else {
      showError(resp?.message || 'Gagal jana recovery codes.');
    }
  } catch (e) {
    console.error('regenerateRecoveryCodes failed', e);
    showError(e.data?.message || 'Gagal jana recovery codes.');
  } finally {
    twoFactor.value.loading = false;
  }
};

const changePassword = async () => {
  if (passwordForm.value.new_password !== passwordForm.value.confirm_password) {
    showError("Kata laluan baharu tidak sepadan.");
    return;
  }

  changingPassword.value = true;
  try {
    const { $api } = useNuxtApp();
    const payload = {
      current_password: passwordForm.value.current_password,
      password: passwordForm.value.new_password,
      password_confirmation: passwordForm.value.confirm_password,
    };
    const response = await $api.post("/user/change-password", payload);

    if (response?.success) {
      passwordForm.value = {
        current_password: "",
        new_password: "",
        confirm_password: "",
      };
      showSuccess(response.message || "Kata laluan berjaya dikemaskini.");
    } else {
      showError(response?.message || "Gagal menukar kata laluan.");
    }
  } catch (error) {
    console.error("Error changing password:", error);
    showError(error?.data?.message || error?.message || "Gagal menukar kata laluan.");
  } finally {
    changingPassword.value = false;
  }
};

// Load user NFC cards for profile URLs
const loadUserNfcCards = async () => {
  // Always try to load cards if user exists
  if (!authStore.user) {
    console.log("⚠️ User not authenticated");
    userNfcCards.value = [];
    return;
  }

  try {
    const { $api } = useNuxtApp();
    const response = await $api.get("/nfc-cards");
    
    if (response.success && response.nfc_cards) {
      // Filter to show only user's own cards (not employee cards)
      const currentUserId = authStore.user?.id;
      console.log("🔍 Filtering cards for user ID:", currentUserId);
      console.log("📦 Total cards from API:", response.nfc_cards.length);
      
      const userCards = response.nfc_cards.filter(card => {
        const isOwnCard = card.user_id === currentUserId;
        if (!isOwnCard) {
          console.log(`⏭️ Skipping card ${card.nfc_card_id} (belongs to user ${card.user_id})`);
        }
        return isOwnCard;
      });

      console.log("✅ User's own cards:", userCards.length);

      // Add cards and assume they have landing pages (since user saved in ProfileBuilder)
      const cardsWithStatus = userCards.map(card => ({
        ...card,
        has_landing_page: true // Assume true since user has been using ProfileBuilder
      }));

      userNfcCards.value = cardsWithStatus;
      console.log("✅ Loaded NFC cards for Settings:", userNfcCards.value.length);
    } else {
      console.log("ℹ️ No cards returned from API");
      userNfcCards.value = [];
    }
  } catch (error) {
    console.error("Error loading NFC cards:", error);
    
    // Don't show errors to user, just log them
    console.log("⚠️ Failed to load cards, but continuing...");
    
    // If API fails but we know user exists, show empty state
    userNfcCards.value = [];
  }
};

// Load plan prices - avoid permission errors for regular users
const loadPlanPrices = async () => {
  console.log("🔄 Loading plan prices...");
  
  // Directly use database data to avoid permission issues
  // This ensures consistent pricing without API dependency
  planPrices.value = [
    {
      id: 1,
      plan_type: 'basic',
      price: 99.00,
      currency: 'MYR',
      description: 'Basic NFC Card Plan',
      features: [
        'One NFC card',
        'Basic profile',
        'Contact sharing',
        'Basic analytics'
      ],
      is_active: true
    },
    {
      id: 2,
      plan_type: 'premium',
      price: 199.00,
      currency: 'MYR',
      description: 'Premium NFC Card Plan',
      features: [
        'One premium NFC card',
        'Advanced profile customization',
        'Social media integration',
        'Advanced analytics',
        'Priority support'
      ],
      is_active: true
    },
    {
      id: 3,
      plan_type: 'business',
      price: 299.00,
      currency: 'MYR',
      description: 'Business NFC Card Plan',
      features: [
        'Multiple NFC cards',
        'Employee management',
        'Bulk ordering',
        'Business analytics',
        'Dedicated support'
      ],
      is_active: true
    }
  ];
  
  console.log("✅ Plan prices loaded from database schema:", planPrices.value.length, "plans");
  
  // Optional: Only admins try to get live data (silently)
  if (authStore.user?.admin_role) {
    try {
      const { $api } = useNuxtApp();
      const response = await $api.get("/admin/plan-prices");
      if (response.success && response.data) {
        planPrices.value = response.data.filter(plan => plan.is_active);
        console.log("🔄 Updated with live admin data:", planPrices.value.length, "plans");
      }
    } catch (error) {
      // Silent fail for admins - just use database data
      console.log("ℹ️ Using database data (admin API unavailable)");
    }
  }
};

// Profile URL helpers — prefer normalized phone number, fall back to legacy nfc_card_id
const normalizePhoneNumber = (raw) => {
  if (!raw) return '';
  return String(raw).replace(/\D/g, '');
};

const getProfileUrlIdentifier = (card) => {
  if (!card) return '';
  const normalizedPhone = normalizePhoneNumber(card.contact_number || card.normalized_contact_number || '');
  if (normalizedPhone && normalizedPhone.length >= 9) {
    return normalizedPhone;
  }
  return card.nfc_card_id || (`Card #${card.id}`);
};

const getCardDisplayLiveUrl = (card) => `${baseUrl.value}/profile/${getProfileUrlIdentifier(card)}`;
const getCardDisplayPreviewUrl = (card) => `${getCardDisplayLiveUrl(card)}?preview=true`;

// Profile URL management functions
const copyProfileUrl = async (card, isPreview = false) => {
  const url = isPreview ? getCardDisplayPreviewUrl(card) : getCardDisplayLiveUrl(card);
  try {
    await navigator.clipboard.writeText(url);
    const urlType = isPreview ? "Preview URL" : "Live URL";
    showSuccess(`${urlType} copied to clipboard`);
  } catch (error) {
    console.error("Error copying to clipboard:", error);
    showError("Failed to copy URL");
  }
};

const openProfileUrl = (card) => {
  window.open(getCardDisplayLiveUrl(card), '_blank');
};

const openProfilePreview = (card) => {
  window.open(getCardDisplayPreviewUrl(card), '_blank');
};

const editCardProfile = (nfcCardId) => {
  // Navigate to BusinessProfileBuilder with the ORIGINAL nfc_card_id (internal routing)
  navigateTo(`/UserDashboard/UserManagement/BusinessPlanUser/BusinessProfileBuilder?cardId=${nfcCardId}`);
};

const updateSecuritySetting = async (setting) => {
  try {
    const { $api } = useNuxtApp();
    const response = await $api.put("/settings/security", {
      [setting]: securitySettings.value[setting]
    });
    
    if (response.success) {
      showSuccess("Security settings updated");
    } else {
      showError(response.message || "Failed to update security settings");
    }
  } catch (error) {
    console.error("Error updating security settings:", error);
    showError(error.data?.message || "Failed to update security settings");
  }
};

const updatePrivacySetting = async (setting) => {
  try {
    const { $api } = useNuxtApp();
    const response = await $api.put("/settings/privacy", {
      [setting]: privacySettings.value[setting]
    });
    
    if (response.success) {
      showSuccess("Privacy settings updated");
    } else {
      showError(response.message || "Failed to update privacy settings");
    }
  } catch (error) {
    console.error("Error updating privacy settings:", error);
    showError(error.data?.message || "Failed to update privacy settings");
  }
};

const updateNotificationSetting = async (setting) => {
  try {
    const { $api } = useNuxtApp();
    const response = await $api.put("/settings/notifications", {
      [setting]: notificationSettings.value[setting]
    });
    
    if (response.success) {
      showSuccess("Notification settings updated");
    } else {
      showError(response.message || "Failed to update notification settings");
    }
  } catch (error) {
    console.error("Error updating notification settings:", error);
    if (error.response?.status === 403) {
      showError("You don't have permission to update notification settings");
    } else if (error.response?.status === 401) {
      showError("Your session has expired. Please log in again");
    } else {
      showError("Failed to update notification settings");
    }
  }
};

const upgradePlan = () => {
  // Redirect to plan selection page
  navigateTo("/UserDashboard/PlanSelection");
};

const manageBilling = () => {
  // Redirect to billing management
  navigateTo("/UserDashboard/PlanSelection");
};

const viewInvoices = () => {
  // Redirect to invoices page
  navigateTo("/UserDashboard/billing/invoices");
};

// Subscription helper methods - now using data from price-management
const getCurrentPlanData = (planType) => {
  return planPrices.value.find(plan => plan.plan_type === planType) || null;
};

const getSubscriptionDisplayName = (plan) => {
  const planData = getCurrentPlanData(plan);
  if (planData) {
    return `${planData.plan_type.charAt(0).toUpperCase() + planData.plan_type.slice(1)} Plan`;
  }
  
  // Fallback for plans not in price management
  const plans = {
    free: 'Free Plan',
    basic: 'Basic Plan',
    business: 'Business Plan',
    premium: 'Premium Plan',
    enterprise: 'Enterprise Plan'
  };
  return plans[plan] || 'Free Plan';
};

const getSubscriptionDescription = (plan) => {
  const planData = getCurrentPlanData(plan);
  if (planData && planData.description) {
    return planData.description;
  }
  
  // Fallback descriptions
  const descriptions = {
    free: 'Basic features for personal use',
    basic: 'Essential features for getting started',
    business: 'Advanced features for business professionals',
    premium: 'Premium features with priority support',
    enterprise: 'Full enterprise features with dedicated support'
  };
  return descriptions[plan] || 'Basic features for personal use';
};

const getSubscriptionPrice = (plan) => {
  const planData = getCurrentPlanData(plan);
  if (planData) {
    const currency = planData.currency === 'MYR' ? 'RM' : planData.currency;
    return planData.price === 0 ? 'Free' : `${currency} ${planData.price}`;
  }
  
  // Fallback prices - match database plan_prices table
  const prices = {
    basic: 'RM 99.00',
    premium: 'RM 199.00',
    business: 'RM 299.00'
  };
  return prices[plan] || 'Contact Sales';
};

const getSubscriptionBadgeClass = (plan) => {
  const classes = {
    free: 'bg-gray-100 text-gray-800',
    basic: 'bg-blue-100 text-blue-800',
    business: 'bg-green-100 text-green-800',
    premium: 'bg-purple-100 text-purple-800',
    enterprise: 'bg-yellow-100 text-yellow-800'
  };
  return classes[plan] || 'bg-gray-100 text-gray-800';
};

const getSubscriptionFeatures = (plan) => {
  const planData = getCurrentPlanData(plan);
  if (planData && planData.features && planData.features.length > 0) {
    return planData.features;
  }
  
  // Fallback features - match database plan_prices table
  const features = {
    basic: [
      'One NFC card',
      'Basic profile',
      'Contact sharing',
      'Basic analytics'
    ],
    premium: [
      'One premium NFC card',
      'Advanced profile customization',
      'Social media integration',
      'Advanced analytics',
      'Priority support'
    ],
    business: [
      'Multiple NFC cards',
      'Employee management',
      'Bulk ordering',
      'Business analytics',
      'Dedicated support'
    ]
  };
  return features[plan] || ['Contact sales for details'];
};

const cancelSubscription = async () => {
  if (!confirm("Are you sure you want to cancel your subscription?")) return;

  try {
    const { $api } = useNuxtApp();
    const response = await $api.post("/settings/subscription/cancel");
    
    if (response.success) {
      showSuccess("Subscription cancelled successfully");
      // Refresh user data
      await authStore.fetchProfile();
    } else {
      showError(response.message || "Failed to cancel subscription");
    }
  } catch (error) {
    console.error("Error cancelling subscription:", error);
    showError(error.data?.message || "Failed to cancel subscription");
  }
};

const exportData = async () => {
  exportingData.value = true;
  try {
    const { $api } = useNuxtApp();
    const response = await $api.post("/settings/export-data");

    if (response.success && response.data) {
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
    } else {
      showError(response.message || "Failed to export data");
    }
  } catch (error) {
    console.error("Error exporting data:", error);
    showError(error.data?.message || "Failed to export data");
  } finally {
    exportingData.value = false;
  }
};

const deleteAccount = async () => {
  deletingAccount.value = true;
  try {
    const { $api } = useNuxtApp();
    const response = await $api.delete("/settings/delete-account");

    if (response.success) {
      // Clear auth store and redirect
      await authStore.logout();
      await navigateTo("/goodbye");
    } else {
      showError(response.message || "Failed to delete account");
      deletingAccount.value = false;
    }
  } catch (error) {
    console.error("Error deleting account:", error);
    showError(error.data?.message || "Failed to delete account");
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

// Linked accounts functions
const loadLinkedAccounts = async () => {
  try {
    const { $api } = useNuxtApp();
    const response = await $api.get('/user/linked-accounts');
    if (response.success) {
      linkedAccounts.value = {
        google: response.accounts?.find(a => a.provider === 'google') || null,
        apple: response.accounts?.find(a => a.provider === 'apple') || null,
      };
    }
  } catch (error) {
    console.error('Failed to load linked accounts:', error);
  }
};

const openLinkAccountModal = (provider) => {
  linkAccountProvider.value = provider;
  linkAccountPassword.value = '';
  linkAccountError.value = '';
  showLinkAccountModal.value = true;
};

const closeLinkAccountModal = () => {
  showLinkAccountModal.value = false;
  linkAccountProvider.value = '';
  linkAccountPassword.value = '';
  linkAccountError.value = '';
};

const linkAccount = async () => {
  if (!linkAccountPassword.value) {
    linkAccountError.value = 'Please enter your password';
    return;
  }

  linkingAccount.value = true;
  linkAccountError.value = '';

  try {
    const { $api } = useNuxtApp();
    // First verify the password
    const verifyResponse = await $api.post('/user/verify-password', {
      password: linkAccountPassword.value,
    });

    if (!verifyResponse.success) {
      linkAccountError.value = 'Incorrect password. Please try again.';
      return;
    }

    // Password verified - initiate OAuth linking flow
    // Store a linking token in session to indicate this is a link operation
    const linkResponse = await $api.post('/user/initiate-link-account', {
      provider: linkAccountProvider.value,
      password: linkAccountPassword.value,
    });

    if (linkResponse.success && linkResponse.redirect_url) {
      // Close modal and redirect to OAuth
      closeLinkAccountModal();
      window.location.href = linkResponse.redirect_url;
    } else {
      linkAccountError.value = linkResponse.message || 'Failed to initiate account linking';
    }
  } catch (error) {
    console.error('Link account error:', error);
    linkAccountError.value = error.response?.data?.message || 'Failed to verify password';
  } finally {
    linkingAccount.value = false;
  }
};

const confirmUnlinkAccount = async (provider) => {
  const providerName = provider === 'google' ? 'Google' : 'Apple';
  
  if (!confirm(`Are you sure you want to unlink your ${providerName} account? You will no longer be able to sign in with ${providerName}.`)) {
    return;
  }

  unlinkingAccount.value = true;

  try {
    const { $api } = useNuxtApp();
    const response = await $api.delete(`/user/linked-accounts/${provider}`);
    
    if (response.success) {
      linkedAccounts.value[provider] = null;
      showSuccess(`${providerName} account unlinked successfully`);
    } else {
      showError(response.message || `Failed to unlink ${providerName} account`);
    }
  } catch (error) {
    console.error('Unlink account error:', error);
    showError(error.response?.data?.message || `Failed to unlink ${providerName} account`);
  } finally {
    unlinkingAccount.value = false;
  }
};

// Lifecycle
onMounted(async () => {
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

  // Load additional data
  await loadUserData();
  await loadActiveSessions();
  await loadUserNfcCards();
  await loadPlanPrices();
  await loadLinkedAccounts();

  // Check for OAuth linking result from redirect
  if (route.query.link_success) {
    showSuccess(decodeURIComponent(String(route.query.link_success)));
    // Clean up URL
    router.replace({ query: {} });
  } else if (route.query.link_error) {
    showError(decodeURIComponent(String(route.query.link_error)));
    // Clean up URL
    router.replace({ query: {} });
  }
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

      // Reload NFC cards if user data changes
      loadUserNfcCards();
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
