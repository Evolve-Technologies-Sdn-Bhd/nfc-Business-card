<!-- pages/UserDashboard/Settings.vue -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Account"
      eyebrow-icon="heroicons:cog-6-tooth"
      title="Settings"
      description="Your account details, security, notifications and plan."
    />

    <!-- Main Layout -->
    <div class="flex flex-col lg:flex-row gap-8">
      <!-- Section navigation: horizontal scroller on mobile, rail on desktop -->
      <div class="w-full lg:w-60 flex-shrink-0">
        <nav class="admin-panel p-2 flex gap-1 overflow-x-auto lg:flex-col lg:sticky lg:top-0" aria-label="Settings sections">
          <button
            v-for="tab in settingsTabs"
            :key="tab.id"
            type="button"
            @click="activeTab = tab.id"
            :aria-current="activeTab === tab.id ? 'page' : undefined"
            :class="[
              'shrink-0 whitespace-nowrap text-left px-3 py-2 rounded-lg transition-colors duration-150 flex items-center text-navigation',
              activeTab === tab.id
                ? 'bg-accent-100 text-secondary-900 shadow-[inset_0_0_0_1px_color-mix(in_srgb,var(--brass)_35%,transparent)]'
                : 'text-secondary-600 hover:bg-secondary-50 hover:text-secondary-900',
              tab.id === 'danger' && activeTab !== tab.id ? 'lg:mt-2 text-error-600 hover:text-error-700' : '',
            ]"
          >
            <Icon :name="tab.icon" class="h-[18px] w-[18px] mr-2.5" :class="activeTab === tab.id ? 'text-accent-700' : ''" />
            {{ tab.label }}
          </button>
        </nav>
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
                <h2 class="card-title text-secondary-900">
                  Personal Information
                </h2>
                <p class="card-subtitle text-secondary-600">
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
                          <UserAvatar
                            :src="personalInfoForm.account_image"
                            :name="`${personalInfoForm.first_name} ${personalInfoForm.last_name}`"
                            class="h-16 w-16 rounded-full border-2 border-secondary-300"
                          />
                        </div>
                        <div class="flex-1">
                          <label class="block text-label text-secondary-700 mb-2">
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
                              class="btn btn-outline btn-sm text-error-600 border-error-300 hover:bg-error-50"
                            >
                              <Icon name="heroicons:trash" class="h-4 w-4 mr-2" />
                              Remove
                            </button>
                          </div>
                          <p class="text-helper text-secondary-500 mt-1">JPG, PNG, GIF or WebP. Max 5MB</p>
                        </div>
                      </div>
                    </div>

                    <!-- First Name -->
                    <div>
                      <label
                        class="block text-label text-secondary-700 mb-2"
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
                        class="block text-label text-secondary-700 mb-2"
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
                        class="block text-label text-secondary-700 mb-2"
                        >Email Address *</label
                      >
                      <input
                        v-model="personalInfoForm.email"
                        type="email"
                        required
                        class="input input-bordered w-full"
                        placeholder="Enter your email"
                      />
                      <p v-if="emailChanged" class="text-helper text-warning-700 mt-1">
                        You'll need to verify the new address — we'll e-mail you a link.
                      </p>
                      <p v-else-if="user && !user.email_verified_at" class="text-helper text-warning-700 mt-1">
                        Not verified yet — check your inbox for the verification link.
                      </p>
                    </div>

                    <!-- Phone -->
                    <div>
                      <label
                        class="block text-label text-secondary-700 mb-2"
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

            <!-- Profile links (one per card the user owns) -->
            <div class="card">
              <div class="px-6 py-4 border-b border-secondary-200">
                <h2 class="card-title text-secondary-900">Profile links</h2>
                <p class="card-subtitle text-secondary-600">
                  The public link each of your cards opens
                </p>
              </div>
              <div class="p-6">
                <div v-if="userNfcCards.length > 0" class="space-y-3">
                  <div
                    v-for="card in userNfcCards"
                    :key="card.id"
                    class="p-4 bg-secondary-50 rounded-lg border border-secondary-200"
                  >
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                      <Icon
                        :name="card.is_digital ? 'heroicons:device-phone-mobile' : 'heroicons:credit-card'"
                        class="h-4 w-4 text-secondary-500"
                      />
                      <h3 class="text-label text-secondary-900">
                        {{ card.is_digital ? "Digital card" : card.nfc_card_id || `Card #${card.id}` }}
                      </h3>
                      <span
                        :class="card.status_badge_class || 'bg-secondary-100 text-secondary-800'"
                        class="px-2 py-0.5 text-badge rounded-full"
                      >
                        {{ card.status_label || card.status }}
                      </span>
                    </div>
                    <code class="block text-caption text-info-700 bg-white border border-secondary-200 px-2 py-1.5 rounded break-all">
                      {{ getCardDisplayLiveUrl(card) }}
                    </code>
                    <div class="flex flex-wrap items-center gap-2 mt-3">
                      <button type="button" @click="copyProfileUrl(card)" class="btn btn-outline btn-sm">
                        <Icon name="heroicons:clipboard-document" class="h-4 w-4 mr-1.5" />
                        Copy link
                      </button>
                      <button type="button" @click="openProfileUrl(card)" class="btn btn-outline btn-sm">
                        <Icon name="heroicons:arrow-top-right-on-square" class="h-4 w-4 mr-1.5" />
                        Open
                      </button>
                      <NuxtLink :to="portalAccess.routes.builder" class="btn btn-primary btn-sm">
                        <Icon name="heroicons:pencil-square" class="h-4 w-4 mr-1.5" />
                        Edit profile
                      </NuxtLink>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-8">
                  <Icon name="heroicons:credit-card" class="h-12 w-12 mx-auto mb-3 text-secondary-300" />
                  <p class="text-body text-secondary-500 mb-4">
                    {{ portalAccess.deactivated ? "Your free profile is offline." : "You don't have a card yet." }}
                  </p>
                  <NuxtLink to="/Onboarding/plan" class="btn btn-primary btn-sm">
                    <Icon name="heroicons:plus" class="h-4 w-4 mr-2" />
                    Get an NFC card
                  </NuxtLink>
                </div>
              </div>
            </div>

            <!-- Account Information Card -->
            <div class="card">
              <div class="px-6 py-4 border-b border-secondary-200">
                <h2 class="card-title text-secondary-900">Account information</h2>
              </div>
              <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-label text-secondary-700 mb-1">Status</label>
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-badge"
                      :class="statusBadgeClass"
                    >
                      {{ accountStatus.label }}
                    </span>
                  </div>
                  <div>
                    <label class="block text-label text-secondary-700 mb-1">Plan</label>
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-badge"
                      :class="getSubscriptionBadgeClass(portalAccess.accountPlan)"
                    >
                      {{ getSubscriptionDisplayName(portalAccess.accountPlan) }}
                      <template v-if="portalAccess.isBusinessEmployee"> · team member</template>
                    </span>
                  </div>
                  <div>
                    <label
                      class="block text-label text-secondary-700 mb-1"
                      >Member Since</label
                    >
                    <p class="text-body text-secondary-900">
                      {{ formatDate(user?.created_at) }}
                    </p>
                  </div>
                  <div>
                    <label
                      class="block text-label text-secondary-700 mb-1"
                      >Last Login</label
                    >
                    <p class="text-body text-secondary-900">
                      {{ formatDate(user?.last_login_at) }}
                    </p>
                  </div>
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
                <h2 class="card-title text-secondary-900">
                  {{ hasLocalPassword ? "Change password" : "Set a password" }}
                </h2>
                <p class="card-subtitle text-secondary-600">
                  {{
                    hasLocalPassword
                      ? "Update your account password"
                      : "You sign in with Google or Apple. Add a password to also sign in with your e-mail."
                  }}
                </p>
              </div>
              <div class="p-6">
                <form @submit.prevent="changePassword">
                  <div class="space-y-4">
                    <div v-if="hasLocalPassword">
                      <label
                        class="block text-label text-secondary-700 mb-2"
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
                        class="block text-label text-secondary-700 mb-2"
                        >New Password</label
                      >
                      <div class="relative">
                        <input
                          v-model="passwordForm.new_password"
                          :type="showNewPassword ? 'text' : 'password'"
                          required
                          minlength="8"
                          autocomplete="new-password"
                          class="input input-bordered w-full pr-10"
                          placeholder="At least 8 characters"
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
                        class="block text-label text-secondary-700 mb-2"
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
                      {{ changingPassword ? "Saving..." : hasLocalPassword ? "Change password" : "Set password" }}
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
                <h2 class="card-title text-secondary-900">
                  Two-Factor Authentication (2FA)
                </h2>
                <p class="card-subtitle text-secondary-600">
                  Add an extra layer of security to your account
                </p>
              </div>
              <div class="p-6">
                <div class="space-y-5">
                  <div class="flex items-start justify-between">
                    <div class="flex-1">
                      <div class="flex items-center gap-3 mb-2">
                        <h3 class="text-label text-secondary-900">
                          Authenticator App (TOTP)
                        </h3>
                        <span
                          v-if="user?.two_factor_enabled"
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-badge bg-success-100 text-success-800"
                        >
                          Active
                        </span>
                        <span
                          v-else
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-badge bg-secondary-100 text-secondary-800"
                        >
                          Inactive
                        </span>
                      </div>
                      <p class="card-subtitle text-secondary-600 leading-relaxed">
                        Add an extra layer of security — every login requires a 6-digit code from an Authenticator app (Google Authenticator, Authy, 1Password, Bitwarden, etc.)
                      </p>
                    </div>
                  </div>

                  <div class="flex flex-wrap items-center gap-3 pt-1">
                    <button
                      v-if="!user?.two_factor_enabled"
                      @click="setup2FA()"
                      :disabled="twoFactor.loading"
                      class="btn btn-primary disabled:opacity-60"
                    >
                      <Icon name="heroicons:shield-check" class="h-4 w-4 mr-2" />
                      <span v-if="twoFactor.loading" class="loading loading-spinner loading-xs mr-2"></span>
                      Enable 2FA
                    </button>
                    <template v-else>
                      <button
                        @click="twoFactor.showDisable = true; twoFactor.passwordInput = ''; twoFactor.recoveryCodeInput = '';"
                        :disabled="twoFactor.loading"
                        class="btn btn-outline btn-sm text-error-600 border-error-300 hover:bg-error-50 disabled:opacity-60"
                      >
                        <Icon name="heroicons:shield-exclamation" class="h-4 w-4 mr-1" />
                        Disable 2FA
                      </button>
                      <button
                        @click="regenerateRecoveryCodes()"
                        :disabled="twoFactor.loading"
                        class="btn btn-outline btn-sm disabled:opacity-60"
                      >
                        <Icon name="heroicons:arrow-path" class="h-4 w-4 mr-1" />
                        Regenerate Recovery Codes
                      </button>
                    </template>
                  </div>
                </div>
              </div>
            </div>

            <!-- Linked Accounts Card -->
            <div class="card">
              <div class="px-6 py-4 border-b border-secondary-200">
                <h2 class="card-title text-secondary-900">
                  Linked Accounts
                </h2>
                <p class="card-subtitle text-secondary-600">
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
                        <h3 class="text-label text-secondary-900">Google</h3>
                        <p v-if="linkedAccounts.google" class="text-body text-success-600">
                          Connected as {{ linkedAccounts.google.email }}
                        </p>
                        <p v-else class="text-body text-secondary-500">
                          Not connected
                        </p>
                      </div>
                    </div>
                    <button
                      v-if="linkedAccounts.google"
                      @click="confirmUnlinkAccount('google')"
                      :disabled="unlinkingAccount"
                      class="btn btn-outline btn-sm text-error-600 border-error-300 hover:bg-error-50"
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
                      <div class="w-10 h-10 bg-white border border-secondary-200 rounded-lg flex items-center justify-center mr-4">
                        <Icon name="logos:apple" class="h-5 w-5" />
                      </div>
                      <div>
                        <h3 class="text-label text-secondary-900">Apple</h3>
                        <p v-if="linkedAccounts.apple" class="text-body text-success-600">
                          Connected as {{ linkedAccounts.apple.email || 'Private Email' }}
                        </p>
                        <p v-else class="text-body text-secondary-500">
                          Not connected
                        </p>
                      </div>
                    </div>
                    <button
                      v-if="linkedAccounts.apple"
                      @click="confirmUnlinkAccount('apple')"
                      :disabled="unlinkingAccount"
                      class="btn btn-outline btn-sm text-error-600 border-error-300 hover:bg-error-50"
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
                <div class="mt-4 p-3 bg-info-50 border border-info-200 rounded-lg">
                  <div class="flex items-start">
                    <Icon name="heroicons:information-circle" class="h-5 w-5 text-info-500 mt-0.5 mr-2 flex-shrink-0" />
                    <p class="text-body text-info-700">
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
                  <h3 class="modal-title text-secondary-900 mb-2">
                    Link {{ linkAccountProvider === 'google' ? 'Google' : 'Apple' }} Account
                  </h3>
                  <p class="text-secondary-600">
                    Enter your current password to verify account ownership before linking.
                  </p>
                </div>

                <form @submit.prevent="linkAccount">
                  <div class="mb-4">
                    <label class="block text-label text-secondary-700 mb-2">
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
                    <p v-if="linkAccountError" class="text-error text-error-600 mt-1">
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
                <h2 class="card-title text-secondary-900">
                  Active Sessions
                </h2>
                <p class="card-subtitle text-secondary-600">
                  Manage your active login sessions across all devices
                </p>
              </div>
              <div class="p-6 space-y-0">
                <div class="space-y-4">
                  <div
                    v-for="session in activeSessions"
                    :key="session.id"
                    class="flex items-center justify-between p-4 border border-secondary-200 rounded-lg"
                  >
                    <div class="flex items-center">
                      <div
                        class="w-10 h-10 bg-info-100 rounded-lg flex items-center justify-center mr-4"
                      >
                        <Icon
                          :name="sessionDeviceIcon(session)"
                          class="h-5 w-5 text-info-600"
                        />
                      </div>
                      <div>
                        <p class="text-body font-medium text-secondary-900">
                          {{ sessionLabel(session) }}
                        </p>
                        <p class="text-helper text-secondary-600">
                          <template v-if="session.ip_address">{{ session.ip_address }} · </template>
                          Last active {{ formatDate(session.last_seen_at) }}
                        </p>
                      </div>
                    </div>
                    <div class="flex items-center space-x-2">
                      <span
                        v-if="session.is_current"
                        class="inline-flex items-center px-2 py-1 rounded-full text-badge bg-success-100 text-success-800"
                      >
                        Current Session
                      </span>
                      <button
                        v-else
                        @click="revokeSession(session.id)"
                        :disabled="revokingSessionId === session.id"
                        class="btn btn-outline btn-sm text-error-600 border-error-300 hover:bg-error-50 disabled:opacity-50"
                      >
                        <span v-if="revokingSessionId === session.id" class="loading loading-spinner loading-xs mr-1"></span>
                        End Session
                      </button>
                    </div>
                  </div>
                </div>

                <div class="border-t border-secondary-200 mt-6 pt-5 flex items-center justify-between">
                  <div class="text-body text-secondary-600 flex items-center">
                    <span v-if="loadingSessions" class="loading loading-spinner loading-xs mr-2"></span>
                    <span>{{ loadingSessions ? 'Loading sessions...' : `${activeSessions.length} active sessions` }}</span>
                  </div>
                  <button
                    @click="logoutEverywhere()"
                    :disabled="loadingLogoutEverywhere"
                    class="btn bg-error-600 text-white hover:bg-error-700 disabled:opacity-60"
                  >
                    <span v-if="loadingLogoutEverywhere" class="loading loading-spinner loading-xs mr-2"></span>
                    <Icon v-else name="heroicons:arrow-left-on-rectangle" class="h-4 w-4 mr-2" />
                    Logout All Devices
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Privacy Settings Tab -->
          <div v-if="activeTab === 'privacy'" class="space-y-6">
            <!-- Profile visibility is a per-card setting (landing page active
                 flag), so it is managed where the cards are — no duplicate
                 account-wide toggle that nothing would read. -->
            <div class="card">
              <div class="px-6 py-4 border-b border-secondary-200">
                <h2 class="card-title text-secondary-900">Profile visibility</h2>
                <p class="card-subtitle text-secondary-600">Who can see your public profile</p>
              </div>
              <div class="p-6">
                <p class="text-body text-secondary-700">
                  Each NFC card has its own public profile. Deactivating a card on the cards page hides
                  its profile from anyone who taps the card or opens its link; reactivate it at any time.
                </p>
                <NuxtLink :to="portalAccess.routes.cards" class="btn btn-outline mt-4 inline-flex items-center">
                  <Icon name="heroicons:credit-card" class="h-4 w-4 mr-2" />
                  Manage card visibility
                </NuxtLink>
              </div>
            </div>

            <!-- Export Data (moved here from Danger Zone: it is not destructive) -->
            <div class="card">
              <div class="px-6 py-4 border-b border-secondary-200">
                <h2 class="card-title text-secondary-900">Export your data</h2>
                <p class="card-subtitle text-secondary-600">Download a copy of what NFCGo stores about you</p>
              </div>
              <div class="p-6">
                <p class="text-body text-secondary-700 mb-4">
                  Includes your account details, NFC cards, profile pages, links, invoices and
                  notification preferences, as a JSON file.
                </p>
                <button @click="exportData" :disabled="exportingData" class="btn btn-outline inline-flex items-center">
                  <span v-if="exportingData" class="loading loading-spinner loading-sm mr-2"></span>
                  <Icon v-else name="heroicons:arrow-down-tray" class="h-4 w-4 mr-2" />
                  {{ exportingData ? "Preparing export…" : "Export my data" }}
                </button>
              </div>
            </div>
          </div>

          <!-- Notifications Tab -->
          <div v-if="activeTab === 'notifications'" class="space-y-6">
            <!-- Only settings the backend actually honours are offered here
                 (NotificationService::PROFILE_ACTIVITY_TYPES). -->
            <div class="card">
              <div class="px-6 py-4 border-b border-secondary-200">
                <h2 class="card-title text-secondary-900">Notifications</h2>
                <p class="card-subtitle text-secondary-600">Choose which alerts you receive</p>
              </div>
              <div class="p-6 space-y-5">
                <label class="flex items-start justify-between gap-4 cursor-pointer">
                  <span>
                    <span class="block text-label text-secondary-900">Profile activity</span>
                    <span class="block card-subtitle text-secondary-600">
                      Alerts when your profile and links reach view milestones.
                    </span>
                  </span>
                  <span class="relative shrink-0 mt-0.5">
                    <input
                      v-model="notificationSettings.profile_views"
                      type="checkbox"
                      class="sr-only peer"
                      @change="updateNotificationSetting('profile_views')"
                    />
                    <span class="block w-10 h-6 rounded-full bg-secondary-300 transition-colors peer-checked:bg-primary-600 peer-focus-visible:ring-2 peer-focus-visible:ring-primary-400 peer-focus-visible:ring-offset-2"></span>
                    <span class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow-sm transition-transform peer-checked:translate-x-4"></span>
                  </span>
                </label>

                <div class="rounded-lg border border-secondary-200 bg-secondary-50 p-4">
                  <p class="text-label text-secondary-900 flex items-center gap-2">
                    <Icon name="heroicons:lock-closed" class="h-4 w-4 text-secondary-500" />
                    Always on
                  </p>
                  <p class="card-subtitle text-secondary-600 mt-1">
                    Account, security, payment and card-order updates are always sent in the app,
                    and important ones (payments, shipping, security) are also e-mailed to you.
                  </p>
                  <NuxtLink :to="'/UserDashboard/Notifications'" class="inline-flex items-center mt-3 text-navigation text-primary-700 hover:text-primary-800">
                    View all notifications
                    <Icon name="heroicons:arrow-right" class="ml-1 h-4 w-4" />
                  </NuxtLink>
                </div>
              </div>
            </div>
          </div>

          <!-- Billing & Subscription Tab -->
          <div v-if="activeTab === 'billing'" class="space-y-6">
            <!-- Business team members: the plan and its invoices belong to the
                 business owner, so there is nothing to manage here. -->
            <div v-if="portalAccess.isBusinessEmployee" class="card">
              <div class="p-6 flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl bg-accent-100 flex items-center justify-center shrink-0">
                  <Icon name="heroicons:building-office-2" class="h-5 w-5 text-accent-700" />
                </div>
                <div>
                  <h2 class="card-title text-secondary-900">Business plan — provided by your company</h2>
                  <p class="card-subtitle text-secondary-600 mt-1">
                    Your account is part of a Business team. The plan, cards and invoices are
                    managed by your business owner, so contact them for any plan or billing change.
                  </p>
                </div>
              </div>
            </div>

            <!-- Subscription Management Card -->
            <div v-else class="card">
              <div class="px-6 py-4 border-b border-secondary-200">
                <div class="flex items-center justify-between flex-wrap gap-3">
                  <div>
                    <h2 class="card-title text-secondary-900">
                      Current Subscription Plan
                    </h2>
                    <p class="card-subtitle text-secondary-600">
                      Manage your NFCGo Business Card subscription
                    </p>
                  </div>
                </div>
              </div>
              <div class="p-6">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6 mb-8">
                  <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3 flex-wrap">
                      <h3 class="card-title text-secondary-900">
                        {{ getSubscriptionDisplayName(user?.subscription_plan) }}
                      </h3>
                      <span 
                        :class="getSubscriptionBadgeClass(user?.subscription_plan)"
                        class="px-2.5 py-1 text-badge rounded-full"
                      >
                        {{ user?.subscription_plan?.toUpperCase() || 'FREE' }}
                      </span>
                    </div>
                    <p class="card-subtitle text-secondary-600 mb-4 leading-relaxed">
                      {{ getSubscriptionDescription(user?.subscription_plan) }}
                    </p>
                    <div class="card-subtitle text-secondary-600 space-y-1.5">
                      <p
                        class="flex items-center gap-2"
                        :class="accountStatus.tone === 'danger' ? 'text-error-700' : ''"
                      >
                        <Icon name="heroicons:information-circle" class="h-4 w-4 text-secondary-400" />
                        {{ accountStatus.label }}
                      </p>
                      <p v-if="user?.subscription_end_date && user?.subscription_plan !== 'free'" class="flex items-center gap-2">
                        <Icon name="heroicons:calendar-days" class="h-4 w-4 text-secondary-400" />
                        Plan valid until
                        <strong class="text-secondary-900">{{ formatDate(user.subscription_end_date) }}</strong>
                      </p>
                      <p v-if="user?.subscription_plan !== 'free' && user?.subscription_status" class="flex items-center gap-2">
                        <Icon name="heroicons:information-circle" class="h-4 w-4 text-secondary-400" />
                        Payment status:
                        <span class="font-semibold text-secondary-900 capitalize">{{ user.subscription_status }}</span>
                      </p>
                      <p v-if="user?.latestSubscription?.next_billing_date && user?.subscription_plan !== 'free'" class="flex items-center gap-2">
                        <Icon name="heroicons:arrow-path" class="h-4 w-4 text-secondary-400" />
                        Next automatic bill:
                        <strong class="text-secondary-900">{{ formatDate(user.latestSubscription.next_billing_date) }}</strong>
                      </p>
                    </div>
                  </div>
                  <div class="md:text-right bg-secondary-50 rounded-xl border border-secondary-200 p-5 min-w-[240px]">
                    <!-- Same price the public pricing page shows (PlanPrice). The
                         card checkout is a one-off payment, so no "/mo" or
                         auto-charge wording here. -->
                    <p class="stat-label mb-2">
                      Plan price
                    </p>
                    <p class="stat-value text-secondary-900 leading-none mb-2">
                      {{ getSubscriptionPrice(user?.subscription_plan) }}
                    </p>
                    <p class="text-helper text-secondary-500">
                      {{ user?.subscription_plan === 'free' ? "Free plan — nothing to pay" : 'Paid at card checkout' }}
                    </p>
                  </div>
                </div>

                <!-- Subscription Features -->
                <div
                  v-if="getSubscriptionFeatures(user?.subscription_plan).length"
                  class="mb-8 p-5 bg-gradient-to-br from-secondary-50 to-white rounded-xl border border-secondary-200"
                >
                  <h4 class="text-label text-secondary-900 mb-4 flex items-center gap-2">
                    <Icon name="heroicons:check-circle" class="h-5 w-5 text-success-600" />
                    Current Plan Features
                  </h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2.5">
                    <div 
                      v-for="feature in getSubscriptionFeatures(user?.subscription_plan)" 
                      :key="feature"
                      class="flex items-start text-body text-secondary-700"
                    >
                      <Icon name="heroicons:check" class="h-4 w-4 text-success-500 mr-2.5 flex-shrink-0 mt-0.5" />
                      <span>{{ feature }}</span>
                    </div>
                  </div>
                </div>

                <!-- Available Plans (if user is on free plan) -->
                <div v-if="user?.subscription_plan === 'free' && planPrices.length > 1" class="mb-8">
                  <h4 class="text-label text-secondary-900 mb-4 flex items-center gap-2">
                    <Icon name="heroicons:sparkles" class="h-5 w-5 text-warning-500" />
                    Available Plans to Upgrade
                  </h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div 
                      v-for="plan in planPrices.filter(p => p.plan_type !== 'free')" 
                      :key="plan.id"
                      class="p-5 border border-secondary-200 rounded-xl hover:border-primary-300 hover:shadow-sm transition-all"
                    >
                      <div class="flex items-start justify-between mb-3">
                        <div>
                          <h5 class="card-title text-secondary-900 capitalize mb-1">{{ plan.plan_type }}</h5>
                          <p class="card-subtitle text-secondary-600">{{ plan.description }}</p>
                        </div>
                        <span class="card-title text-primary-600 whitespace-nowrap ml-3">
                          {{ plan.currency === 'MYR' ? 'RM' : plan.currency }} {{ plan.price }}
                        </span>
                      </div>
                      <div class="space-y-1.5 mb-4 pt-2 border-t border-secondary-100">
                        <div 
                          v-for="feature in plan.features?.slice(0, 4)" 
                          :key="feature"
                          class="flex items-start text-caption text-secondary-600"
                        >
                          <Icon name="heroicons:check" class="h-3.5 w-3.5 text-success-500 mr-1.5 flex-shrink-0 mt-0.5" />
                          {{ feature }}
                        </div>
                        <div v-if="plan.features?.length > 4" class="text-helper text-secondary-500 pl-5">
                          +{{ plan.features.length - 4 }} more additional features
                        </div>
                      </div>
                      <button
                        @click="upgradePlan"
                        class="btn btn-primary btn-sm w-full"
                      >
                        <Icon name="heroicons:arrow-trending-up" class="h-4 w-4 mr-2" />
                        Choose This Plan
                      </button>
                    </div>
                  </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-secondary-200">
                  <button
                    v-if="user?.subscription_plan === 'free'"
                    @click="upgradePlan"
                    class="btn btn-primary"
                  >
                    <Icon name="heroicons:arrow-trending-up" class="h-4 w-4 mr-2" />
                    Compare paid plans
                  </button>
                  <!-- Business is set up by NFCGo (enquiry-first), so there is no
                       self-serve plan change for Business accounts. -->
                  <button
                    v-else-if="user?.subscription_plan !== 'business'"
                    @click="manageBilling"
                    class="btn btn-outline"
                  >
                    <Icon name="heroicons:arrows-up-down" class="h-4 w-4 mr-2" />
                    Change plan
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
                    class="btn btn-outline text-error-600 border-error-300 hover:bg-error-50"
                  >
                    <Icon name="heroicons:x-circle" class="h-4 w-4 mr-2" />
                    Cancel Subscription
                  </button>
                </div>
              </div>
            </div>

            <!-- Billing & Invoice History — driven by the real invoice records -->
            <div v-if="!portalAccess.isBusinessEmployee" class="card">
              <div class="px-6 py-4 border-b border-secondary-200">
                <div class="flex items-center justify-between flex-wrap gap-3">
                  <div>
                    <h2 class="card-title text-secondary-900">
                      Billing & Invoice History
                    </h2>
                    <p class="card-subtitle text-secondary-600">
                      All your subscription payment receipts and invoices
                    </p>
                  </div>
                  <button
                    class="btn btn-outline btn-sm"
                    @click="viewInvoices"
                  >
                    <Icon name="heroicons:arrow-top-right-on-square" class="h-4 w-4 mr-2" />
                    Open Full Page
                  </button>
                </div>
              </div>
              <div class="p-6">
                <InvoiceList @preview="handleInvoicePreview" />
              </div>
            </div>

            <InvoicePreviewModal
              :show="showInvoicePreview"
              :invoice="selectedInvoice"
              @close="closeInvoicePreview"
            />
          </div>

          <!-- Danger Zone Tab -->
          <div v-if="activeTab === 'danger'" class="space-y-6">
            <!-- There is no self-serve deletion endpoint: closing an account
                 touches orders, invoices and (for Business) employee accounts,
                 so it is handled by support. This card says so honestly
                 instead of offering a button that always failed. -->
            <div class="bg-white rounded-lg shadow-sm border border-error-200">
              <div class="px-6 py-4 border-b border-error-200">
                <h2 class="card-title text-error-900">Close your account</h2>
                <p class="text-body text-error-600">Permanently remove your account and profiles</p>
              </div>
              <div class="p-6 space-y-4">
                <p class="text-body text-secondary-700">
                  Account closure is handled by the NFCGo support team so that any card orders, invoices
                  and linked team accounts are closed properly. Send the request from the e-mail address
                  on this account and we will confirm with you before anything is deleted.
                </p>
                <p class="text-body text-secondary-700">
                  Before you go, you can
                  <button type="button" class="font-semibold text-primary-600 underline" @click="activeTab = 'privacy'">export a copy of your data</button>.
                </p>
                <a
                  :href="closeAccountMailto"
                  class="btn btn-outline inline-flex items-center text-error-600 border-error-300 hover:bg-error-50"
                >
                  <Icon name="heroicons:envelope" class="h-4 w-4 mr-2" />
                  Request account closure
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

    <!-- ================== MODAL SETUP 2FA ================== -->
    <div
      v-if="twoFactor.showSetup"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    >
      <div class="bg-white rounded-2xl p-6 max-w-lg w-full mx-4 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center mb-6">
          <div class="w-12 h-12 bg-info-100 rounded-full flex items-center justify-center mr-4">
            <Icon name="heroicons:shield-check" class="h-6 w-6 text-info-600" />
          </div>
          <div>
            <h3 class="modal-title text-secondary-900">
              Enable Two-Factor Authentication (2FA)
            </h3>
            <p class="card-subtitle text-secondary-600">
              Complete the 3 steps below to enable 2FA
            </p>
          </div>
        </div>

        <!-- STEP 1: Scan QR Code -->
        <div class="mb-6">
          <div class="flex items-center gap-2 mb-3">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-info-600 text-white text-badge">1</span>
            <h4 class="text-label text-secondary-900">Scan QR Code with Authenticator App</h4>
          </div>

          <div class="bg-secondary-50 rounded-xl p-5 flex flex-col items-center border border-secondary-200">
            <!-- Rendered locally: the otpauth:// URI contains the 2FA secret and
                 must never be sent to a third-party QR service. -->
            <div
              v-if="twoFactorQrSvg"
              class="h-[220px] w-[220px] overflow-hidden rounded-lg border-2 border-white bg-white p-2 shadow-sm [&>svg]:h-full [&>svg]:w-full"
              role="img"
              aria-label="Two-factor authentication QR code"
              v-html="twoFactorQrSvg"
            ></div>

            <div v-else class="w-[220px] h-[220px] rounded-lg border-2 border-dashed border-secondary-300 flex items-center justify-center">
              <span class="loading loading-spinner text-info-600"></span>
            </div>

            <div class="mt-4 w-full">
              <div class="flex items-center justify-between mb-1.5">
                <p class="text-label text-secondary-700">Secret Key (if you can't scan QR):</p>
                <button
                  type="button"
                  @click="copyText(twoFactor.secret, 'Secret 2FA')"
                  class="btn btn-outline btn-xs py-0.5 h-7"
                >
                  <Icon name="heroicons:clipboard" class="h-3.5 w-3.5 mr-1" />
                  Copy
                </button>
              </div>
              <code class="block w-full p-2.5 bg-white border border-secondary-200 rounded-lg font-mono text-caption text-secondary-800 break-all text-center select-all">
                {{ twoFactor.secret || '—' }}
              </code>
            </div>
          </div>
        </div>

        <!-- STEP 2: Enter OTP -->
        <div class="mb-6">
          <div class="flex items-center gap-2 mb-3">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-info-600 text-white text-badge">2</span>
            <h4 class="text-label text-secondary-900">Enter 6 Digit OTP Code</h4>
          </div>
          <p class="text-helper text-secondary-600 mb-2.5">
            Open your Authenticator app, select the NFCGo account, then type the 6-digit code displayed:
          </p>
          <input
            type="text"
            inputmode="numeric"
            v-model="twoFactor.otpInput"
            @input="twoFactor.otpInput = twoFactor.otpInput.replace(/[^0-9]/g, '').slice(0, 6)"
            maxlength="6"
            placeholder="••••••"
            class="input input-bordered w-full text-center text-h3 tracking-[0.5em] h-14 font-mono"
          />
        </div>

        <!-- STEP 3: Save Recovery Codes -->
        <div class="mb-6">
          <div class="flex items-center gap-2 mb-3">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-warning-500 text-white text-badge">3</span>
            <h4 class="text-label text-secondary-900">Save Recovery Codes (IMPORTANT!)</h4>
          </div>

          <div class="bg-warning-50 border border-warning-200 rounded-xl p-4">
            <div class="flex gap-3">
              <Icon name="heroicons:exclamation-triangle" class="h-5 w-5 text-warning-600 flex-shrink-0 mt-0.5" />
              <div>
                <h5 class="text-label text-warning-900 mb-1">Save in a SAFE place</h5>
                <p class="text-caption text-warning-800 leading-relaxed">
                  The 8 recovery codes below can be used <strong>ONE TIME ONLY</strong> if you lose access to your phone / Authenticator app.
                  Do not share with anyone. If lost, you won't be able to log into your account.
                </p>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-2 mt-4">
            <div
              v-for="(code, idx) in twoFactor.recoveryCodes"
              :key="idx"
              class="p-2.5 bg-secondary-50 border border-secondary-200 rounded-lg font-mono text-body text-secondary-800 text-center select-all"
            >
              {{ code }}
            </div>
          </div>

          <div class="flex gap-2 mt-4">
            <button
              type="button"
              @click="copyText(twoFactor.recoveryCodes.join('\n'), 'All Recovery Codes')"
              class="btn btn-outline btn-sm flex-1"
            >
              <Icon name="heroicons:clipboard" class="h-4 w-4 mr-2" />
              Copy All
            </button>
            <button
              type="button"
              @click="downloadRecoveryCodes()"
              class="btn btn-outline btn-sm flex-1"
            >
              <Icon name="heroicons:arrow-down-tray" class="h-4 w-4 mr-2" />
              Download .txt
            </button>
          </div>
        </div>

        <!-- Footer Buttons -->
        <div class="flex gap-3 justify-end pt-2 border-t border-secondary-200">
          <button
            type="button"
            @click="close2FASetup()"
            :disabled="twoFactor.loading"
            class="btn btn-outline disabled:opacity-50"
          >
            Close
          </button>
          <button
            type="button"
            @click="confirm2FA()"
            :disabled="!/^[0-9]{6}$/.test(twoFactor.otpInput) || twoFactor.loading"
            class="btn btn-primary disabled:opacity-50"
          >
            <span v-if="twoFactor.loading" class="loading loading-spinner loading-xs mr-2"></span>
            Save & Enable
          </button>
        </div>
      </div>
    </div>
    <!-- ================== END MODAL SETUP 2FA ================== -->

    <!-- ================== MODAL NYAHAKTIF 2FA ================== -->
    <div
      v-if="twoFactor.showDisable"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    >
      <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-2xl">
        <div class="flex items-center mb-6">
          <div class="w-12 h-12 bg-error-100 rounded-full flex items-center justify-center mr-4">
            <Icon name="heroicons:shield-exclamation" class="h-6 w-6 text-error-600" />
          </div>
          <div>
            <h3 class="modal-title text-secondary-900">
              Disable 2FA
            </h3>
            <p class="card-subtitle text-secondary-600">
              Verify your identity to continue
            </p>
          </div>
        </div>

        <p class="text-body text-secondary-700 mb-5 leading-relaxed">
          Enter your <strong>current password</strong> OR <strong>one recovery code</strong>
          to confirm you want to disable 2FA on this account.
        </p>

        <!-- Option 1: Current Password -->
        <div class="mb-4">
          <label class="block text-label text-secondary-700 mb-1.5">
            Option 1 — Current Password
          </label>
          <div class="relative">
            <input
              :type="showCurrentPassword ? 'text' : 'password'"
              v-model="twoFactor.passwordInput"
              placeholder="Enter current password"
              class="input input-bordered w-full pr-10"
              autocomplete="current-password"
            />
            <button
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-400 hover:text-secondary-600"
              @click="showCurrentPassword = !showCurrentPassword"
              tabindex="-1"
            >
              <Icon v-if="!showCurrentPassword" name="heroicons:eye" class="h-5 w-5" />
              <Icon v-else name="heroicons:eye-slash" class="h-5 w-5" />
            </button>
          </div>
        </div>

        <div class="text-center my-4">
          <span class="inline-block px-3 py-1 text-badge text-secondary-500 bg-secondary-100 rounded-full uppercase tracking-wider">
            Or
          </span>
        </div>

        <!-- Option 2: Recovery Code -->
        <div class="mb-8">
          <label class="block text-label text-secondary-700 mb-1.5">
            Option 2 — Recovery Code
          </label>
          <input
            type="text"
            v-model="twoFactor.recoveryCodeInput"
            placeholder="AAAA-BBBB"
            class="input input-bordered w-full font-mono uppercase tracking-widest text-center"
            maxlength="9"
          />
        </div>

        <!-- Footer Buttons -->
        <div class="flex gap-3 justify-end pt-4 border-t border-secondary-200">
          <button
            type="button"
            @click="twoFactor.showDisable = false; twoFactor.passwordInput = ''; twoFactor.recoveryCodeInput = '';"
            :disabled="twoFactor.loading"
            class="btn btn-outline disabled:opacity-50"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="disable2FA()"
            :disabled="!twoFactor.passwordInput && !twoFactor.recoveryCodeInput || twoFactor.loading"
            class="btn bg-error-600 text-white hover:bg-error-700 disabled:opacity-50"
          >
            <span v-if="twoFactor.loading" class="loading loading-spinner loading-xs mr-2"></span>
            Confirm Disable
          </button>
        </div>
      </div>
    </div>
    <!-- ================== END MODAL NYAHAKTIF 2FA ================== -->

    <!-- Success Toast -->
    <div
      v-if="showSuccessToast"
      class="fixed top-4 right-4 bg-success-100 border border-success-200 rounded-lg p-4 shadow-lg z-50"
    >
      <div class="flex items-center">
        <Icon
          name="heroicons:check-circle"
          class="h-5 w-5 text-success-600 mr-3"
        />
        <p class="text-body font-medium text-success-800">{{ successMessage }}</p>
      </div>
    </div>

    <!-- Error Toast -->
    <div
      v-if="showErrorToast"
      class="fixed top-4 right-4 bg-error-100 border border-error-200 rounded-lg p-4 shadow-lg z-50"
    >
      <div class="flex items-center">
        <Icon name="heroicons:x-circle" class="h-5 w-5 text-error-600 mr-3" />
        <p class="text-body font-medium text-error-800">{{ errorMessage }}</p>
      </div>
    </div>

    <AdminConfirmDialog
      v-model="confirmState.open"
      :title="confirmState.title"
      :message="confirmState.message"
      :confirm-label="confirmState.confirmLabel"
      :cancel-label="confirmState.cancelLabel"
      :tone="confirmState.tone"
      @confirm="resolveConfirm(true)"
      @cancel="resolveConfirm(false)"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch, onBeforeUnmount } from "vue";
import { renderSVG } from "uqr";
import InvoiceList from "~/components/InvoiceList.vue";
import InvoicePreviewModal from "~/components/InvoicePreviewModal.vue";
import { portalPlanStatus } from "~/composables/usePortalAccess";

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
// Deep-linkable: /UserDashboard/Settings?tab=billing opens that section.
const SETTINGS_TAB_IDS = ["account", "security", "privacy", "notifications", "billing", "danger"];
const settingsRoute = useRoute();
const initialTab = String(settingsRoute.query.tab || "");
const activeTab = ref(SETTINGS_TAB_IDS.includes(initialTab) ? initialTab : "account");
const user = computed(() => authStore.user); // Use user from authStore
const baseUrl = computed(() => {
  if (process.client) {
    return window.location.origin;
  }
  return 'https://nfccard.app'; // fallback for SSR
});

// The session lives in the `auth-token` cookie (stores/auth.js). This page
// used to look for a token in localStorage, found none, and so never loaded
// saved notification settings or the real session list.
const isSignedIn = computed(() => !!authStore.isAuthenticated && !!authStore.user);

// Google/Apple-only accounts have no password yet: they "set" one instead of
// "changing" it (backend: User::has_local_password).
const hasLocalPassword = computed(() => user.value?.has_local_password !== false);

const emailChanged = computed(() => {
  const current = String(user.value?.email || "").toLowerCase();
  const typed = String(personalInfoForm.value.email || "").trim().toLowerCase();
  return !!current && !!typed && current !== typed;
});

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

// QR for the authenticator app, generated in the browser from the otpauth URI.
const twoFactorQrSvg = computed(() => {
  const data = twoFactor.value.qrData;
  if (!data) return "";
  try {
    return renderSVG(data, { border: 1 });
  } catch (e) {
    console.error("Failed to render 2FA QR code:", e);
    return "";
  }
});

const loadingSessions = ref(false);
const loadingLogoutEverywhere = ref(false);
const revokingSessionId = ref(null);

// Only `profile_views` ("Profile activity") is offered: it is the one
// preference NotificationService honours.
const notificationSettings = ref({
  profile_views: true,
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

// Styled replacement for window.confirm() (composables/useConfirmDialog.js)
const { confirmState, askConfirm, resolveConfirm } = useConfirmDialog();

// Plan / role-aware routes shared with the portal shell
const portalAccess = usePortalAccess();

// Same status wording as the sidebar plan chip (Active / free window ends /
// inactive), instead of a hard-coded "Active".
const accountStatus = computed(() => portalPlanStatus(portalAccess.value));
const statusBadgeClass = computed(() => ({
  success: "bg-success-100 text-success-800",
  warning: "bg-warning-100 text-warning-800",
  danger: "bg-error-100 text-error-800",
}[accountStatus.value.tone] || "bg-secondary-100 text-secondary-800"));

// Account closure goes through support (no self-serve deletion endpoint).
const supportEmail = ref("support@nfcgo.com");
const closeAccountMailto = computed(() => {
  const subject = encodeURIComponent("Account closure request");
  const body = encodeURIComponent(
    `Please close my NFCGo account.\n\nAccount e-mail: ${user.value?.email || ""}\n`,
  );
  return `mailto:${supportEmail.value}?subject=${subject}&body=${body}`;
});

// Password visibility toggles
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmNewPassword = ref(false);
const showLinkAccountPassword = ref(false);

// Invoice preview modal state
const showInvoicePreview = ref(false);
const selectedInvoice = ref(null);
const handleInvoicePreview = (invoice) => {
  selectedInvoice.value = invoice;
  showInvoicePreview.value = true;
};
const closeInvoicePreview = () => {
  showInvoicePreview.value = false;
  selectedInvoice.value = null;
};

// Toast states
const showSuccessToast = ref(false);
const showErrorToast = ref(false);
const successMessage = ref("");
const errorMessage = ref("");

// Settings tabs (removed separate profile tab, integrated with account)
const settingsTabs = [
  { id: "account", label: "Account", icon: "heroicons:user-circle" },
  { id: "security", label: "Security", icon: "heroicons:shield-check" },
  { id: "privacy", label: "Privacy & Data", icon: "heroicons:eye-slash" },
  { id: "notifications", label: "Notifications", icon: "heroicons:bell" },
  { id: "billing", label: "Plan & Billing", icon: "heroicons:credit-card" },
  {
    id: "danger",
    label: "Close Account",
    icon: "heroicons:exclamation-triangle",
  },
];

// Methods
const loadUserData = async () => {
  if (!isSignedIn.value) return;

  try {
    const { $api } = useNuxtApp();
    // GET /api/settings → SettingsController@getSettings (persisted
    // notification preferences). Was "/user/settings", which 404'd, so saved
    // toggles always reloaded as the defaults.
    const response = await $api.get("/settings");

    if (response?.notification_settings) {
      notificationSettings.value = {
        profile_views: response.notification_settings.profile_views !== false,
      };
    }
    if (response?.support_email) {
      supportEmail.value = response.support_email;
    }
  } catch (error) {
    // Keep defaults — user data itself is already loaded from authStore
    console.warn("Could not load settings; using defaults", error);
  }
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
      authStore.user = { ...authStore.user, ...response.user };
    } else {
      // Refresh user data from server
      await authStore.fetchProfile();
    }

    showSuccess(response.message || "Personal information updated successfully");
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

    // SettingsController::uploadUserAccountImage answers `account_image`
    // (this used to read `response.url`, so a successful upload never showed).
    const imageUrl = response?.account_image || response?.url;
    if (response?.success && imageUrl) {
      personalInfoForm.value.account_image = imageUrl;
      if (authStore.user) {
        authStore.user = {
          ...authStore.user,
          account_image: imageUrl,
        };
      }
      showSuccess('Profile picture updated successfully');
    } else {
      showError(response?.message || 'Failed to upload image');
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

const loadActiveSessions = async () => {
  if (!isSignedIn.value) return;

  loadingSessions.value = true;
  try {
    const { $api } = useNuxtApp();
    const response = await $api.get("/user/sessions");

    if (response?.success && Array.isArray(response.sessions)) {
      activeSessions.value = response.sessions;
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

// Readable name for a session. Sanctum tokens carry an internal name such as
// "auth_token", which means nothing to the user, so show the device instead.
const sessionLabel = (session) => {
  const known = (v) => v && !/^unknown/i.test(String(v));
  const parts = [session.device, session.browser, session.platform].filter(known);
  if (parts.length) return [...new Set(parts)].join(" · ");
  if (session.type === "remember") return "Remembered browser";
  return session.is_current ? "This device" : "Signed-in device";
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
      showSuccess(resp.message || 'Session has been logged out.');
      activeSessions.value = activeSessions.value.filter(s => s.id !== sessionId);
    } else {
      showError(resp?.message || 'Failed to revoke session.');
    }
  } catch (e) {
    console.error('Revoke session failed', e);
    showError(e.data?.message || e.message || 'Failed to revoke session.');
  } finally {
    revokingSessionId.value = null;
  }
};

const logoutEverywhere = async () => {
  if (loadingLogoutEverywhere.value) return;
  if (!(await askConfirm({ title: "Sign out of all devices?", message: "Every session, including this one, will be signed out. You will need to sign in again.", confirmLabel: "Sign out everywhere", tone: "danger" }))) return;
  loadingLogoutEverywhere.value = true;
  try {
    const { $api } = useNuxtApp();
    const resp = await $api.post('/user/logout-everywhere');
    if (resp?.success) {
      showSuccess('You have been logged out from all devices.');
      await authStore.logout();
      await navigateTo('/UserAccount/login');
    } else {
      showError(resp?.message || 'Failed.');
    }
  } catch (e) {
    console.error('logoutEverywhere failed', e);
    showError(e.data?.message || 'Failed to logout all devices.');
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
      showError(resp?.message || 'Failed to setup 2FA.');
    }
  } catch (e) {
    console.error('setup2FA failed', e);
    showError(e.data?.message || 'Failed to generate 2FA setup.');
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
    showError('Enter the 6-digit OTP code from your Authenticator app.');
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
      showSuccess('2FA successfully enabled. Save recovery codes in a safe place.');
      setTimeout(() => {
        twoFactor.value.showSetup = false;
        twoFactor.value.otpInput = '';
      }, 1500);
    } else {
      showError(resp?.message || 'Incorrect OTP code.');
    }
  } catch (e) {
    console.error('confirm2FA failed', e);
    showError(e.data?.message || 'Failed to enable 2FA. Please try again.');
  } finally {
    twoFactor.value.loading = false;
  }
};

const copyText = (text, label) => {
  if (navigator.clipboard && window.isSecureContext) {
    navigator.clipboard.writeText(text).then(() => {
      showSuccess(`${label} copied to clipboard.`);
    }).catch(() => {
      // Fallback
      fallbackCopy(text);
      showSuccess(`${label} copied to clipboard.`);
    });
  } else {
    fallbackCopy(text);
    showSuccess(`${label} copied to clipboard.`);
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
    `Generated: ${new Date().toLocaleString()}`,
    '',
    ...codes.map((c, i) => `${String(i + 1).padStart(2, '0')}. ${c}`),
    '',
    'USE ONE ONLY WHEN NEEDED. EACH CODE IS FOR ONE-TIME USE.',
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
    showError('Enter your password to confirm disabling 2FA.');
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
      showSuccess(resp.message || '2FA successfully disabled.');
    } else {
      showError(resp?.message || 'Failed to disable 2FA.');
    }
  } catch (e) {
    console.error('disable2FA failed', e);
    showError(e.data?.message || 'Failed to disable 2FA.');
  } finally {
    twoFactor.value.loading = false;
  }
};

const regenerateRecoveryCodes = async () => {
  if (!(await askConfirm({ title: "Generate new recovery codes?", message: "You will get 8 new codes and your current recovery codes will stop working immediately.", confirmLabel: "Generate new codes" }))) return;
  twoFactor.value.loading = true;
  try {
    const { $api } = useNuxtApp();
    const resp = await $api.post('/user/2fa/recovery-codes/regenerate');
    if (resp?.success) {
      twoFactor.value.recoveryCodes = resp.recovery_codes || [];
      twoFactor.value.recoveryCodesShown = true;
      showSuccess(resp.message || 'New recovery codes generated.');
    } else {
      showError(resp?.message || 'Failed to generate recovery codes.');
    }
  } catch (e) {
    console.error('regenerateRecoveryCodes failed', e);
    showError(e.data?.message || 'Failed to generate recovery codes.');
  } finally {
    twoFactor.value.loading = false;
  }
};

const changePassword = async () => {
  if (passwordForm.value.new_password !== passwordForm.value.confirm_password) {
    showError("New passwords do not match.");
    return;
  }

  changingPassword.value = true;
  try {
    const { $api } = useNuxtApp();
    const settingFirstPassword = !hasLocalPassword.value;
    const payload = {
      current_password: settingFirstPassword ? null : passwordForm.value.current_password,
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
      if (settingFirstPassword && authStore.user) {
        authStore.user = { ...authStore.user, has_local_password: true };
      }
      showSuccess(settingFirstPassword ? "Password set. You can now also sign in with your e-mail." : response.message || "Password updated successfully.");
    } else {
      showError(response?.message || "Failed to change password.");
    }
  } catch (error) {
    console.error("Error changing password:", error);
    showError(error?.data?.message || error?.message || "Failed to change password.");
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

    // Own cards only (a Business owner's list also contains employee cards).
    // Cards still awaiting payment have no live profile yet.
    const currentUserId = authStore.user?.id;
    userNfcCards.value = (response?.success && Array.isArray(response.nfc_cards) ? response.nfc_cards : [])
      .filter((card) => card.user_id === currentUserId)
      .filter((card) => !["pending_payment", "awaiting_payment_verification", "cancelled"].includes(card.status));
  } catch (error) {
    console.error("Error loading NFC cards:", error);
    userNfcCards.value = [];
  }
};

// Load plan prices — SAFETY FIRST: only call PUBLIC user route, with multiple fallback layers,
// never fall back to any /admin/* endpoint even on total failure.
const _planPricesLoaded = ref(false);
const loadPlanPrices = async () => {
  // Guard: avoid duplicate load if already successfully loaded + populated
  if (_planPricesLoaded.value && planPrices.value?.length > 0) return;

  // Prices come only from the public plan-price list the pricing page uses.
  // There is deliberately no hard-coded fallback: it showed invented prices
  // that disagreed with the real ones. Never call /admin/plan-prices here.
  try {
    const { $api } = useNuxtApp();
    const response = await $api.get("/plan-prices/public", { _skipAbort: false, timeout: 15000 });
    if (response && response.success && Array.isArray(response.data) && response.data.length > 0) {
      const activePlans = response.data.filter(plan => plan.is_active !== false && plan.is_active !== 0);
      if (activePlans.length > 0) {
        planPrices.value = activePlans;
        _planPricesLoaded.value = true;
        return;
      }
    }
  } catch (error) {
    console.warn("Could not load plan prices", error?.response?.status ?? error);
  }

  _planPricesLoaded.value = true;
};

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

const getCardDisplayLiveUrl = (card) => `${baseUrl.value}${getCardPublicUrl(card)}`;

const copyProfileUrl = async (card) => {
  try {
    await navigator.clipboard.writeText(getCardDisplayLiveUrl(card));
    showSuccess("Link copied to clipboard");
  } catch (error) {
    console.error("Error copying to clipboard:", error);
    showError("Failed to copy link");
  }
};

const openProfileUrl = (card) => {
  window.open(getCardDisplayLiveUrl(card), '_blank', 'noopener');
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
  navigateTo("/Onboarding/plan");
};

const manageBilling = () => {
  // Redirect to billing management
  navigateTo("/Onboarding/plan");
};

const viewInvoices = () => {
  // Full invoices page (paginated list + statistics + PDF downloads)
  navigateTo("/dashboard/invoices");
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
  
  if (plan === 'free') return 'Free';
  return plan === 'business' ? 'Contact sales' : '—';
};

const getSubscriptionBadgeClass = (plan) => {
  const classes = {
    free: 'bg-secondary-100 text-secondary-800',
    basic: 'bg-info-100 text-info-800',
    // Paid tiers carry the brass accent, matching the portal plan badges
    business: 'bg-accent-100 text-accent-800',
    premium: 'bg-accent-100 text-accent-800',
    enterprise: 'bg-warning-100 text-warning-800'
  };
  return classes[plan] || 'bg-secondary-100 text-secondary-800';
};

const getSubscriptionFeatures = (plan) => {
  const planData = getCurrentPlanData(plan);
  if (planData && planData.features && planData.features.length > 0) {
    return planData.features;
  }
  
  // No invented fallback: the features shown are the admin-managed ones.
  return [];
};

const cancelSubscription = async () => {
  if (!(await askConfirm({ title: "Cancel your subscription?", message: "Your plan stays active until the end of the current paid period, then it will not renew.", confirmLabel: "Cancel subscription", cancelLabel: "Keep subscription", tone: "danger" }))) return;

  try {
    const { $api } = useNuxtApp();
    // Cancels the current user's active subscription at the end of the paid
    // period (see SubscriptionController::cancelActive + api.php subscription routes).
    const response = await $api.post("/subscription/cancel");
    
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
  
  if (!(await askConfirm({ title: `Unlink ${providerName}?`, message: `You will no longer be able to sign in with ${providerName}. Make sure you can still sign in with your password.`, confirmLabel: "Unlink", tone: "danger" }))) {
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

.loading {
  @apply animate-spin;
}

.loading-spinner {
  @apply border-2 border-current border-t-transparent rounded-full;
}

.loading-sm {
  @apply w-4 h-4;
}

.loading-xs {
  @apply w-3 h-3;
}
</style>
