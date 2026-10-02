<!-- pages/AdminManagement/configuration.vue -->
<template>
  <div>
    <!-- Header -->
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-secondary-900">
            System Configuration
          </h1>
          <p class="mt-2 text-secondary-600">
            Manage all system-wide settings from one central location
          </p>
        </div>
      </div>
    </div>

    <!-- Main Tabs -->
    <div class="card mb-6">
      <div class="border-b border-secondary-200">
        <nav class="-mb-px flex flex-wrap gap-x-8 gap-y-2 px-6" aria-label="Tabs">
          <button
            v-for="tab in mainTabs"
            :key="tab.id"
            @click="activeMainTab = tab.id"
            :class="[
              activeMainTab === tab.id
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-secondary-500 hover:text-secondary-700 hover:border-secondary-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2',
            ]"
          >
            <Icon :name="tab.icon" class="h-4 w-4" />
            {{ tab.name }}
          </button>
        </nav>
      </div>

      <!-- ================= GENERAL SETTINGS TAB ================= -->
      <div v-show="activeMainTab === 'general'" class="p-6">
        <form @submit.prevent="saveGeneralSettings">
          <div class="space-y-8">
            <!-- Brand Identity: Logo & Favicon -->
            <div>
              <h3 class="text-lg font-semibold text-secondary-900 mb-4 flex items-center gap-2">
                <Icon name="heroicons:swatch" class="h-5 w-5 text-primary-600" />
                Brand Identity
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- System Logo -->
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-2">
                    System Logo
                  </label>
                  <p class="text-xs text-secondary-500 mb-4">
                    Recommended: PNG/SVG transparent, 512x512px, max 2MB. Shown in sidebar and login page.
                  </p>
                  <div
                    class="border-2 border-dashed border-secondary-300 rounded-xl p-6 bg-white hover:bg-secondary-50 transition-colors"
                    :class="{ 'border-primary-400 bg-primary-50/50': generalSettings.system_logo_url }"
                  >
                    <div class="flex flex-col items-center justify-center">
                      <div
                        v-if="generalSettings.system_logo_url"
                        class="mb-4 w-32 h-32 rounded-xl bg-secondary-100 flex items-center justify-center overflow-hidden border border-secondary-200 shadow-sm"
                      >
                        <img
                          :src="generalSettings.system_logo_url + '?v=' + Date.now()"
                          alt="System Logo"
                          class="max-w-full max-h-full object-contain"
                        />
                      </div>
                      <div v-else class="mb-4 w-32 h-32 rounded-xl bg-secondary-100 flex items-center justify-center border border-secondary-200">
                        <Icon name="heroicons:photo" class="h-12 w-12 text-secondary-400" />
                      </div>
                      <div class="flex items-center gap-3 w-full justify-center">
                        <input
                          type="file"
                          ref="logoFileInput"
                          accept="image/png,image/svg+xml,image/jpeg,image/webp"
                          @change="handleLogoFileSelect"
                          class="hidden"
                        />
                        <button
                          type="button"
                          @click="$refs.logoFileInput.click()"
                          :disabled="uploadingLogo || __uploadLogoSubmitLock"
                          class="btn btn-sm btn-primary"
                        >
                          <Icon v-if="!uploadingLogo" name="heroicons:arrow-up-tray" class="h-4 w-4 mr-1" />
                          <span v-if="uploadingLogo" class="animate-spin">⏳</span>
                          {{ uploadingLogo ? 'Uploading...' : 'Upload Logo' }}
                        </button>
                        <button
                          v-if="generalSettings.system_logo_url"
                          type="button"
                          @click="removeLogo"
                          class="btn btn-sm btn-outline text-error-600 hover:bg-error-50"
                        >
                          <Icon name="heroicons:trash" class="h-4 w-4 mr-1" />
                          Remove
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- System Favicon -->
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-2">
                    System Favicon
                  </label>
                  <p class="text-xs text-secondary-500 mb-4">
                    Recommended: ICO/PNG, 64x64px or 32x32px, max 500KB. Shown in browser tab.
                  </p>
                  <div
                    class="border-2 border-dashed border-secondary-300 rounded-xl p-6 bg-white hover:bg-secondary-50 transition-colors"
                    :class="{ 'border-primary-400 bg-primary-50/50': generalSettings.system_favicon_url }"
                  >
                    <div class="flex flex-col items-center justify-center">
                      <div
                        v-if="generalSettings.system_favicon_url"
                        class="mb-4 w-20 h-20 rounded-xl bg-secondary-100 flex items-center justify-center overflow-hidden border border-secondary-200 shadow-sm"
                      >
                        <img
                          :src="generalSettings.system_favicon_url + '?v=' + Date.now()"
                          alt="System Favicon"
                          class="max-w-full max-h-full object-contain"
                        />
                      </div>
                      <div v-else class="mb-4 w-20 h-20 rounded-xl bg-secondary-100 flex items-center justify-center border border-secondary-200">
                        <Icon name="heroicons:bookmark-square" class="h-10 w-10 text-secondary-400" />
                      </div>
                      <div class="flex items-center gap-3 w-full justify-center">
                        <input
                          type="file"
                          ref="faviconFileInput"
                          accept="image/x-icon,image/png,image/vnd.microsoft.icon"
                          @change="handleFaviconFileSelect"
                          class="hidden"
                        />
                        <button
                          type="button"
                          @click="$refs.faviconFileInput.click()"
                          :disabled="uploadingFavicon || __uploadFaviconSubmitLock"
                          class="btn btn-sm btn-primary"
                        >
                          <Icon v-if="!uploadingFavicon" name="heroicons:arrow-up-tray" class="h-4 w-4 mr-1" />
                          <span v-if="uploadingFavicon" class="animate-spin">⏳</span>
                          {{ uploadingFavicon ? 'Uploading...' : 'Upload Favicon' }}
                        </button>
                        <button
                          v-if="generalSettings.system_favicon_url"
                          type="button"
                          @click="removeFavicon"
                          class="btn btn-sm btn-outline text-error-600 hover:bg-error-50"
                        >
                          <Icon name="heroicons:trash" class="h-4 w-4 mr-1" />
                          Remove
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- App Information -->
            <div class="pt-4 border-t border-secondary-200">
              <h3 class="text-lg font-semibold text-secondary-900 mb-4">
                Application Information
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    App Name *
                  </label>
                  <input
                    v-model="generalSettings.app_name"
                    type="text"
                    class="input"
                    placeholder="NFCGo"
                    required
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    App URL
                  </label>
                  <input
                    v-model="generalSettings.app_url"
                    type="url"
                    class="input"
                    placeholder="https://nfcgo.my"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Support Email
                  </label>
                  <input
                    v-model="generalSettings.support_email"
                    type="email"
                    class="input"
                    placeholder="support@nfcgo.my"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Support Phone
                  </label>
                  <input
                    v-model="generalSettings.support_phone"
                    type="tel"
                    class="input"
                    placeholder="+60 12-345 6789"
                  />
                </div>
              </div>
            </div>

            <!-- Company Information -->
            <div class="pt-4 border-t border-secondary-200">
              <h3 class="text-lg font-semibold text-secondary-900 mb-4">
                Company Information
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Company Name
                  </label>
                  <input
                    v-model="generalSettings.company_name"
                    type="text"
                    class="input"
                    placeholder="NFCGo Sdn Bhd"
                  />
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Company Address
                  </label>
                  <textarea
                    v-model="generalSettings.company_address"
                    rows="2"
                    class="input"
                    placeholder="123 Jalan Contoh, 50000 Kuala Lumpur, Malaysia"
                  ></textarea>
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Registration No.
                  </label>
                  <input
                    v-model="generalSettings.company_reg_no"
                    type="text"
                    class="input"
                    placeholder="1234567-X"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Tax ID / SST No.
                  </label>
                  <input
                    v-model="generalSettings.company_tax_id"
                    type="text"
                    class="input"
                    placeholder="C1234567890"
                  />
                </div>
              </div>
            </div>

            <!-- System Defaults -->
            <div class="pt-4 border-t border-secondary-200">
              <h3 class="text-lg font-semibold text-secondary-900 mb-4">
                System Defaults
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Default Currency
                  </label>
                  <select v-model="generalSettings.default_currency" class="input">
                    <option value="MYR">Malaysian Ringgit (MYR)</option>
                    <option value="SGD">Singapore Dollar (SGD)</option>
                    <option value="USD">US Dollar (USD)</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Default Language
                  </label>
                  <select v-model="generalSettings.default_language" class="input">
                    <option value="en">English</option>
                    <option value="ms">Bahasa Melayu</option>
                    <option value="zh">中文</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Default Timezone
                  </label>
                  <select v-model="generalSettings.default_timezone" class="input">
                    <option value="Asia/Kuala_Lumpur">Asia/Kuala_Lumpur (GMT+8)</option>
                    <option value="Asia/Singapore">Asia/Singapore (GMT+8)</option>
                    <option value="UTC">UTC</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end items-center pt-4 border-t border-secondary-200">
              <div class="flex space-x-3">
                <button
                  type="button"
                  @click="loadGeneralSettings"
                  class="btn btn-outline"
                >
                  <Icon name="heroicons:arrow-path" class="h-5 w-5 mr-2" />
                  Reset
                </button>
                <button
                  type="submit"
                  :disabled="savingGeneral"
                  class="btn btn-primary"
                >
                  <div v-if="savingGeneral" class="spinner mr-2"></div>
                  <Icon v-else name="heroicons:check" class="h-5 w-5 mr-2" />
                  {{ savingGeneral ? "Saving..." : "Save General Settings" }}
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>

      <!-- ================= PLAN PRICING TAB ================= -->
      <div v-show="activeMainTab === 'pricing'" class="p-6">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h3 class="text-lg font-semibold text-secondary-900">
              Subscription Plan Pricing
            </h3>
            <p class="text-sm text-secondary-600 mt-1">
              Manage pricing for Basic, Premium, and Business NFC card plans
            </p>
          </div>
          <div class="mt-4 sm:mt-0 flex items-center gap-3">
            <button
              @click="loadPrices"
              :disabled="loadingPrices"
              class="btn btn-outline btn-sm"
            >
              <Icon name="heroicons:arrow-path" class="h-4 w-4 mr-2" :class="{ 'animate-spin': loadingPrices }" />
              Refresh
            </button>
            <button
              @click="saveAllPlanChanges"
              :disabled="!hasPlanChanges || savingPlans"
              class="btn btn-primary btn-sm"
            >
              <Icon name="heroicons:check" class="h-4 w-4 mr-2" />
              {{ savingPlans ? 'Saving...' : 'Save All Changes' }}
            </button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loadingPrices" class="flex justify-center items-center py-12">
          <div class="text-center">
            <Icon name="svg-spinners:ring-resize" class="h-12 w-12 text-primary-600 mx-auto mb-4" />
            <p class="text-secondary-600">Loading plan prices...</p>
          </div>
        </div>

        <!-- Price Cards -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="plan in plans"
            :key="plan.id"
            class="card overflow-hidden border-2 transition-all"
            :class="getPlanBorderClass(plan.plan_type)"
          >
            <div class="p-6" :class="getPlanHeaderClass(plan.plan_type)">
              <div class="flex items-center justify-between mb-2">
                <h3 class="text-xl font-bold text-white capitalize">
                  {{ plan.plan_type }} Plan
                </h3>
                <span v-if="plan.is_active" class="px-3 py-1 bg-white bg-opacity-20 text-white text-xs font-medium rounded-full">
                  Active
                </span>
                <span v-else class="px-3 py-1 bg-secondary-500 text-white text-xs font-medium rounded-full">
                  Inactive
                </span>
              </div>
              <p class="text-white text-opacity-90 text-sm">
                {{ plan.description || 'NFC Card Plan' }}
              </p>
            </div>

            <div class="p-6">
              <div class="mb-6">
                <label class="block text-sm font-medium text-secondary-700 mb-2">
                  Price ({{ plan.currency }})
                </label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-secondary-500 font-medium">
                    {{ plan.currency }}
                  </span>
                  <input
                    v-model.number="plan.price"
                    @input="markPlanAsChanged(plan)"
                    type="number"
                    step="0.01"
                    min="0"
                    class="input w-full pl-16 text-2xl font-bold"
                    :class="{ 'border-warning-400 bg-warning-50': isPlanChanged(plan) }"
                  />
                </div>
                <p v-if="isPlanChanged(plan)" class="mt-2 text-xs text-warning-600 flex items-center">
                  <Icon name="heroicons:exclamation-triangle" class="h-4 w-4 mr-1" />
                  Unsaved changes
                </p>
              </div>

              <div class="mb-6">
                <label class="block text-sm font-medium text-secondary-700 mb-2">
                  Description
                </label>
                <textarea
                  v-model="plan.description"
                  @input="markPlanAsChanged(plan)"
                  rows="2"
                  class="input w-full text-sm"
                  :class="{ 'border-warning-400 bg-warning-50': isPlanChanged(plan) }"
                ></textarea>
              </div>

              <div class="mb-4">
                <label class="block text-sm font-medium text-secondary-700 mb-2">
                  Features
                </label>
                <div class="space-y-2">
                  <div v-for="(feature, index) in plan.features" :key="index" class="flex items-center gap-2">
                    <Icon name="heroicons:check-circle" class="h-5 w-5 text-success-500 flex-shrink-0" />
                    <input
                      v-model="plan.features[index]"
                      @input="markPlanAsChanged(plan)"
                      type="text"
                      class="input flex-1 text-sm py-1"
                      :class="{ 'border-warning-400 bg-warning-50': isPlanChanged(plan) }"
                    />
                    <button
                      @click="removePlanFeature(plan, index)"
                      class="p-1 text-error-600 hover:bg-error-50 rounded transition-colors"
                      title="Remove feature"
                    >
                      <Icon name="heroicons:x-mark" class="h-5 w-5" />
                    </button>
                  </div>
                </div>
                <button
                  @click="addPlanFeature(plan)"
                  class="mt-2 text-sm text-primary-600 hover:text-primary-700 flex items-center"
                >
                  <Icon name="heroicons:plus-circle" class="h-5 w-5 mr-1" />
                  Add Feature
                </button>
              </div>

              <div class="flex items-center justify-between pt-4 border-t border-secondary-200">
                <span class="text-sm font-medium text-secondary-700">Active Status</span>
                <button
                  @click="togglePlanStatus(plan)"
                  :class="plan.is_active ? 'bg-success-500' : 'bg-secondary-300'"
                  class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                >
                  <span
                    :class="plan.is_active ? 'translate-x-6' : 'translate-x-1'"
                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                  />
                </button>
              </div>

              <button
                v-if="isPlanChanged(plan)"
                @click="saveSinglePlan(plan)"
                :disabled="savingPlans"
                class="btn btn-primary w-full mt-4"
              >
                {{ savingPlans ? 'Saving...' : 'Save This Plan' }}
              </button>
            </div>

            <div class="px-6 py-3 bg-secondary-50 border-t border-secondary-200">
              <p class="text-xs text-secondary-500">
                Last updated: {{ formatPlanDate(plan.updated_at) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Change Summary -->
        <div v-if="hasPlanChanges && !loadingPrices" class="mt-8 card bg-warning-50 border-2 border-warning-400">
          <div class="p-6">
            <div class="flex items-start">
              <Icon name="heroicons:exclamation-triangle" class="h-6 w-6 text-warning-600 mr-3 flex-shrink-0 mt-0.5" />
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-warning-900 mb-2">
                  Unsaved Changes
                </h3>
                <p class="text-sm text-warning-800 mb-4">
                  You have unsaved changes in {{ changedPlansList.length }} plan(s). Click "Save All Changes" to apply them.
                </p>
                <div class="space-y-1">
                  <div v-for="plan in changedPlansList" :key="plan.id" class="text-sm text-warning-800">
                    • <strong class="capitalize">{{ plan.plan_type }}</strong>:
                    <span v-if="plan.originalPrice !== plan.price">
                      Price changed from {{ plan.currency }} {{ plan.originalPrice }} to {{ plan.currency }} {{ plan.price }}
                    </span>
                    <span v-if="plan.originalDescription !== plan.description && plan.originalPrice !== plan.price"> | </span>
                    <span v-if="plan.originalDescription !== plan.description">
                      Description updated
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Info Card -->
        <div v-if="!loadingPrices" class="mt-8 card bg-info-50 border border-info-200">
          <div class="p-6">
            <div class="flex items-start">
              <Icon name="heroicons:information-circle" class="h-6 w-6 text-info-600 mr-3 flex-shrink-0" />
              <div>
                <h3 class="text-sm font-semibold text-info-900 mb-2">
                  Important Notes
                </h3>
                <ul class="text-sm text-info-800 space-y-1 list-disc list-inside">
                  <li>Price changes will affect new orders immediately after saving</li>
                  <li>Existing users will keep their current plan prices</li>
                  <li>Inactive plans will not be shown to users during checkout</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ================= LEGAL DOCUMENTS TAB ================= -->
      <div v-show="activeMainTab === 'legal'" class="p-6">
        <div class="border-b border-secondary-200 mb-6">
          <nav class="-mb-px flex space-x-8" aria-label="Legal Document Tabs">
            <button
              @click="activeLegalTab = 'terms'"
              :class="[
                activeLegalTab === 'terms'
                  ? 'border-primary-500 text-primary-600'
                  : 'border-transparent text-secondary-500 hover:text-secondary-700 hover:border-secondary-300',
                'whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm',
              ]"
            >
              Terms of Service
            </button>
            <button
              @click="activeLegalTab = 'privacy'"
              :class="[
                activeLegalTab === 'privacy'
                  ? 'border-primary-500 text-primary-600'
                  : 'border-transparent text-secondary-500 hover:text-secondary-700 hover:border-secondary-300',
                'whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm',
              ]"
            >
              Privacy Policy
            </button>
          </nav>
        </div>

        <!-- Terms of Service Sub-tab -->
        <div v-show="activeLegalTab === 'terms'">
          <form @submit.prevent="saveDocument('terms_of_service')">
            <div class="space-y-6">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Version *
                  </label>
                  <input
                    v-model.number="termsData.version"
                    type="number"
                    min="1.0"
                    step="0.1"
                    :class="[
                      'input',
                      versionErrors.terms ? 'border-red-500 focus:ring-red-500' : ''
                    ]"
                    placeholder="1.0"
                    @input="validateTermsVersion"
                    @change="validateTermsVersion"
                    required
                  />
                  <p v-if="versionErrors.terms" class="text-xs text-red-500 mt-1">
                    {{ versionErrors.terms }}
                  </p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Effective Date
                  </label>
                  <input
                    v-model="termsData.effective_date"
                    type="date"
                    class="input"
                    required
                  />
                </div>
              </div>

              <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                <p class="text-sm text-amber-800">
                  <strong>Workflow:</strong> (1) Upload your PDF file below, (2) Set version and effective date, (3) Click Save Changes to store metadata.
                </p>
              </div>

              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-secondary-900 mb-3">
                  PDF Document Upload
                </h3>
                <p class="text-sm text-secondary-600 mb-4">
                  Upload a PDF file containing the Terms of Service document. This PDF will be displayed to users who access the Terms of Service.
                </p>

                <div
                  v-if="termsPdfStatus.exists"
                  class="mb-4 flex items-center justify-between bg-green-50 border border-green-200 rounded p-3"
                >
                  <div class="flex items-center space-x-2 text-sm text-green-700">
                    <Icon name="heroicons:document-check" class="h-5 w-5 text-green-500" />
                    <span>{{ getLegalDisplayFileName(termsPdfStatus.fileInfo?.original_filename) }} ({{
                      formatLegalFileSize(termsPdfStatus.fileInfo?.size)
                    }})</span>
                  </div>
                  <button
                    type="button"
                    @click="downloadLegalPdf('terms')"
                    class="btn btn-sm btn-outline"
                  >
                    <Icon name="heroicons:arrow-down-tray" class="h-4 w-4 mr-1" />
                    Download
                  </button>
                </div>

                <div v-else class="mb-4 text-sm text-secondary-500 bg-yellow-50 border border-yellow-200 rounded p-3">
                  <Icon name="heroicons:exclamation-triangle" class="h-5 w-5 inline mr-1 text-yellow-600" />
                  No PDF uploaded yet. Please upload a PDF file below.
                </div>

                <div class="flex items-center space-x-3">
                  <input
                    type="file"
                    ref="termsFileInput"
                    accept="application/pdf"
                    @change="handleTermsFileSelect"
                    class="hidden"
                  />
                  <button
                    type="button"
                    @click="$refs.termsFileInput.click()"
                    :disabled="uploadingTermsPdf"
                    class="btn btn-sm btn-primary"
                  >
                    <Icon v-if="!uploadingTermsPdf" name="heroicons:arrow-up-tray" class="h-4 w-4 mr-1" />
                    <span v-if="uploadingTermsPdf" class="animate-spin">⏳</span>
                    {{ uploadingTermsPdf ? 'Uploading...' : 'Choose PDF File' }}
                  </button>
                  <span class="text-xs text-secondary-500">Max 10MB</span>
                </div>
              </div>

              <div v-if="termsData.updated_at" class="text-sm text-secondary-600">
                <p>
                  Last updated: {{ formatLegalDate(termsData.updated_at) }}
                  <span v-if="termsData.updater">
                    by {{ termsData.updater.first_name }}
                    {{ termsData.updater.last_name }}
                  </span>
                </p>
              </div>

              <div class="flex justify-end items-center pt-4 border-t border-secondary-200">
                <div class="flex space-x-3">
                  <button
                    type="button"
                    @click="previewLegalDocument('terms')"
                    class="btn btn-outline"
                  >
                    <Icon name="heroicons:eye" class="h-5 w-5 mr-2" />
                    Preview
                  </button>
                  <button
                    type="submit"
                    :disabled="savingTerms"
                    class="btn btn-primary"
                  >
                    <div v-if="savingTerms" class="spinner mr-2"></div>
                    <Icon v-else name="heroicons:check" class="h-5 w-5 mr-2" />
                    {{ savingTerms ? "Saving..." : "Save Changes" }}
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>

        <!-- Privacy Policy Sub-tab -->
        <div v-show="activeLegalTab === 'privacy'">
          <form @submit.prevent="saveDocument('privacy_policy')">
            <div class="space-y-6">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Version *
                  </label>
                  <input
                    v-model.number="privacyData.version"
                    type="number"
                    min="1.0"
                    step="0.1"
                    :class="[
                      'input',
                      versionErrors.privacy ? 'border-red-500 focus:ring-red-500' : ''
                    ]"
                    placeholder="1.0"
                    @input="validatePrivacyVersion"
                    @change="validatePrivacyVersion"
                    required
                  />
                  <p v-if="versionErrors.privacy" class="text-xs text-red-500 mt-1">
                    {{ versionErrors.privacy }}
                  </p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Effective Date
                  </label>
                  <input
                    v-model="privacyData.effective_date"
                    type="date"
                    class="input"
                    required
                  />
                </div>
              </div>

              <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                <p class="text-sm text-amber-800">
                  <strong>Workflow:</strong> (1) Upload your PDF file below, (2) Set version and effective date, (3) Click Save Changes to store metadata.
                </p>
              </div>

              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-secondary-900 mb-3">
                  PDF Document Upload
                </h3>
                <p class="text-sm text-secondary-600 mb-4">
                  Upload a PDF file containing the Privacy Policy document. This PDF will be displayed to users who access the Privacy Policy.
                </p>

                <div
                  v-if="privacyPdfStatus.exists"
                  class="mb-4 flex items-center justify-between bg-green-50 border border-green-200 rounded p-3"
                >
                  <div class="flex items-center space-x-2 text-sm text-green-700">
                    <Icon name="heroicons:document-check" class="h-5 w-5 text-green-500" />
                    <span>{{ getLegalDisplayFileName(privacyPdfStatus.fileInfo?.original_filename) }} ({{
                      formatLegalFileSize(privacyPdfStatus.fileInfo?.size)
                    }})</span>
                  </div>
                  <button
                    type="button"
                    @click="downloadLegalPdf('privacy')"
                    class="btn btn-sm btn-outline"
                  >
                    <Icon name="heroicons:arrow-down-tray" class="h-4 w-4 mr-1" />
                    Download
                  </button>
                </div>

                <div v-else class="mb-4 text-sm text-secondary-500 bg-yellow-50 border border-yellow-200 rounded p-3">
                  <Icon name="heroicons:exclamation-triangle" class="h-5 w-5 inline mr-1 text-yellow-600" />
                  No PDF uploaded yet. Please upload a PDF file below.
                </div>

                <div class="flex items-center space-x-3">
                  <input
                    type="file"
                    ref="privacyFileInput"
                    accept="application/pdf"
                    @change="handlePrivacyFileSelect"
                    class="hidden"
                  />
                  <button
                    type="button"
                    @click="$refs.privacyFileInput.click()"
                    :disabled="uploadingPrivacyPdf"
                    class="btn btn-sm btn-primary"
                  >
                    <Icon v-if="!uploadingPrivacyPdf" name="heroicons:arrow-up-tray" class="h-4 w-4 mr-1" />
                    <span v-if="uploadingPrivacyPdf" class="animate-spin">⏳</span>
                    {{ uploadingPrivacyPdf ? 'Uploading...' : 'Choose PDF File' }}
                  </button>
                  <span class="text-xs text-secondary-500">Max 10MB</span>
                </div>
              </div>

              <div v-if="privacyData.updated_at" class="text-sm text-secondary-600">
                <p>
                  Last updated: {{ formatLegalDate(privacyData.updated_at) }}
                  <span v-if="privacyData.updater">
                    by {{ privacyData.updater.first_name }}
                    {{ privacyData.updater.last_name }}
                  </span>
                </p>
              </div>

              <div class="flex justify-end items-center pt-4 border-t border-secondary-200">
                <div class="flex space-x-3">
                  <button
                    type="button"
                    @click="previewLegalDocument('privacy')"
                    class="btn btn-outline"
                  >
                    <Icon name="heroicons:eye" class="h-5 w-5 mr-2" />
                    Preview
                  </button>
                  <button
                    type="submit"
                    :disabled="savingPrivacy"
                    class="btn btn-primary"
                  >
                    <div v-if="savingPrivacy" class="spinner mr-2"></div>
                    <Icon v-else name="heroicons:check" class="h-5 w-5 mr-2" />
                    {{ savingPrivacy ? "Saving..." : "Save Changes" }}
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- ================= PAYMENT SETTINGS TAB ================= -->
      <div v-show="activeMainTab === 'payment'" class="p-6">
        <form @submit.prevent="savePaymentSettings">
          <div class="space-y-8">
            <!-- Fiuu Payment Gateway -->
            <div>
              <h3 class="text-lg font-semibold text-secondary-900 mb-4 flex items-center gap-2">
                <Icon name="heroicons:credit-card" class="h-5 w-5 text-primary-600" />
                Fiuu Payment Gateway (formerly Razer Merchant Services)
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Merchant ID
                  </label>
                  <input
                    v-model="paymentSettings.fiuu_merchant_id"
                    type="text"
                    class="input"
                    placeholder="MXXXXXXX"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Verify Key
                  </label>
                  <input
                    v-model="paymentSettings.fiuu_verify_key"
                    type="text"
                    class="input"
                    placeholder="Enter Verify Key"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Secret Key
                  </label>
                  <input
                    v-model="paymentSettings.fiuu_secret_key"
                    type="password"
                    class="input"
                    placeholder="Enter Secret Key"
                  />
                </div>
                <div class="flex items-end">
                  <div class="flex items-center gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                      <input
                        v-model="paymentSettings.fiuu_enabled"
                        type="checkbox"
                        class="w-4 h-4 rounded text-primary-600 focus:ring-primary-500"
                      />
                      <span class="text-sm font-medium text-secondary-700">Enable Fiuu Payment</span>
                    </label>
                  </div>
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Environment
                  </label>
                  <select v-model="paymentSettings.fiuu_environment" class="input max-w-xs">
                    <option value="sandbox">Sandbox (Testing)</option>
                    <option value="production">Production (Live)</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Bank Transfer -->
            <div class="pt-4 border-t border-secondary-200">
              <h3 class="text-lg font-semibold text-secondary-900 mb-4 flex items-center gap-2">
                <Icon name="heroicons:building-library" class="h-5 w-5 text-primary-600" />
                Manual Bank Transfer
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Bank Name
                  </label>
                  <input
                    v-model="paymentSettings.bank_name"
                    type="text"
                    class="input"
                    placeholder="Maybank / CIMB / Public Bank"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Account Holder Name
                  </label>
                  <input
                    v-model="paymentSettings.bank_account_name"
                    type="text"
                    class="input"
                    placeholder="NFCGo Sdn Bhd"
                  />
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Account Number
                  </label>
                  <input
                    v-model="paymentSettings.bank_account_number"
                    type="text"
                    class="input"
                    placeholder="1234567890"
                  />
                </div>
                <div class="md:col-span-2 flex items-center">
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input
                      v-model="paymentSettings.bank_transfer_enabled"
                      type="checkbox"
                      class="w-4 h-4 rounded text-primary-600 focus:ring-primary-500"
                    />
                    <span class="text-sm font-medium text-secondary-700">Enable Manual Bank Transfer</span>
                  </label>
                </div>
              </div>
            </div>

            <!-- E-Wallet -->
            <div class="pt-4 border-t border-secondary-200">
              <h3 class="text-lg font-semibold text-secondary-900 mb-4 flex items-center gap-2">
                <Icon name="heroicons:wallet" class="h-5 w-5 text-primary-600" />
                E-Wallet Payment
              </h3>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <label class="flex items-center gap-3 p-4 card cursor-pointer hover:bg-secondary-50 transition-colors" :class="paymentSettings.ewallet_tng ? 'border-primary-500 bg-primary-50' : ''">
                  <input
                    v-model="paymentSettings.ewallet_tng"
                    type="checkbox"
                    class="w-4 h-4 rounded text-primary-600 focus:ring-primary-500"
                  />
                  <div>
                    <p class="text-sm font-semibold text-secondary-900">Touch 'n Go</p>
                    <p class="text-xs text-secondary-500">eWallet</p>
                  </div>
                </label>
                <label class="flex items-center gap-3 p-4 card cursor-pointer hover:bg-secondary-50 transition-colors" :class="paymentSettings.ewallet_grabpay ? 'border-primary-500 bg-primary-50' : ''">
                  <input
                    v-model="paymentSettings.ewallet_grabpay"
                    type="checkbox"
                    class="w-4 h-4 rounded text-primary-600 focus:ring-primary-500"
                  />
                  <div>
                    <p class="text-sm font-semibold text-secondary-900">GrabPay</p>
                    <p class="text-xs text-secondary-500">eWallet</p>
                  </div>
                </label>
                <label class="flex items-center gap-3 p-4 card cursor-pointer hover:bg-secondary-50 transition-colors" :class="paymentSettings.ewallet_shopeepay ? 'border-primary-500 bg-primary-50' : ''">
                  <input
                    v-model="paymentSettings.ewallet_shopeepay"
                    type="checkbox"
                    class="w-4 h-4 rounded text-primary-600 focus:ring-primary-500"
                  />
                  <div>
                    <p class="text-sm font-semibold text-secondary-900">ShopeePay</p>
                    <p class="text-xs text-secondary-500">eWallet</p>
                  </div>
                </label>
                <label class="flex items-center gap-3 p-4 card cursor-pointer hover:bg-secondary-50 transition-colors" :class="paymentSettings.ewallet_duitnow ? 'border-primary-500 bg-primary-50' : ''">
                  <input
                    v-model="paymentSettings.ewallet_duitnow"
                    type="checkbox"
                    class="w-4 h-4 rounded text-primary-600 focus:ring-primary-500"
                  />
                  <div>
                    <p class="text-sm font-semibold text-secondary-900">DuitNow</p>
                    <p class="text-xs text-secondary-500">QR Payment</p>
                  </div>
                </label>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end items-center pt-4 border-t border-secondary-200">
              <button
                type="submit"
                :disabled="savingPayment"
                class="btn btn-primary"
              >
                <div v-if="savingPayment" class="spinner mr-2"></div>
                <Icon v-else name="heroicons:check" class="h-5 w-5 mr-2" />
                {{ savingPayment ? "Saving..." : "Save Payment Settings" }}
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- ================= EMAIL SETTINGS TAB ================= -->
      <div v-show="activeMainTab === 'email'" class="p-6">
        <form @submit.prevent="saveEmailSettings">
          <div class="space-y-8">
            <!-- SMTP Configuration -->
            <div>
              <h3 class="text-lg font-semibold text-secondary-900 mb-4 flex items-center gap-2">
                <Icon name="heroicons:envelope" class="h-5 w-5 text-primary-600" />
                SMTP Configuration
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    SMTP Host
                  </label>
                  <input
                    v-model="emailSettings.smtp_host"
                    type="text"
                    class="input"
                    placeholder="smtp.example.com"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    SMTP Port
                  </label>
                  <input
                    v-model.number="emailSettings.smtp_port"
                    type="number"
                    class="input"
                    placeholder="587"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    SMTP Username
                  </label>
                  <input
                    v-model="emailSettings.smtp_username"
                    type="text"
                    class="input"
                    placeholder="no-reply@nfcgo.my"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    SMTP Password
                  </label>
                  <input
                    v-model="emailSettings.smtp_password"
                    type="password"
                    class="input"
                    placeholder="••••••••"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Encryption
                  </label>
                  <select v-model="emailSettings.smtp_encryption" class="input">
                    <option value="tls">TLS</option>
                    <option value="ssl">SSL</option>
                    <option value="">None</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Mailer
                  </label>
                  <select v-model="emailSettings.mailer" class="input">
                    <option value="smtp">SMTP</option>
                    <option value="sendmail">Sendmail</option>
                    <option value="log">Log (Debug)</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- From Address -->
            <div class="pt-4 border-t border-secondary-200">
              <h3 class="text-lg font-semibold text-secondary-900 mb-4">
                Email "From" Address
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    From Name
                  </label>
                  <input
                    v-model="emailSettings.from_name"
                    type="text"
                    class="input"
                    placeholder="NFCGo"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    From Email
                  </label>
                  <input
                    v-model="emailSettings.from_email"
                    type="email"
                    class="input"
                    placeholder="no-reply@nfcgo.my"
                  />
                </div>
              </div>
            </div>

            <!-- Test Email -->
            <div class="pt-4 border-t border-secondary-200">
              <h3 class="text-lg font-semibold text-secondary-900 mb-4">
                Test Configuration
              </h3>
              <div class="flex items-end gap-4">
                <div class="flex-1">
                  <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Send Test Email To
                  </label>
                  <input
                    v-model="testEmailAddress"
                    type="email"
                    class="input"
                    placeholder="admin@example.com"
                  />
                </div>
                <button
                  type="button"
                  @click="sendTestEmail"
                  :disabled="sendingTestEmail"
                  class="btn btn-outline"
                >
                  <Icon v-if="sendingTestEmail" name="heroicons:paper-airplane" class="h-5 w-5 mr-2 animate-pulse" />
                  <Icon v-else name="heroicons:paper-airplane" class="h-5 w-5 mr-2" />
                  {{ sendingTestEmail ? "Sending..." : "Send Test Email" }}
                </button>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end items-center pt-4 border-t border-secondary-200">
              <button
                type="submit"
                :disabled="savingEmail"
                class="btn btn-primary"
              >
                <div v-if="savingEmail" class="spinner mr-2"></div>
                <Icon v-else name="heroicons:check" class="h-5 w-5 mr-2" />
                {{ savingEmail ? "Saving..." : "Save Email Settings" }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Legal Document Preview Modal -->
    <div
      v-if="showLegalPreview"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
      @click="showLegalPreview = false"
    >
      <div
        class="card max-w-6xl w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col"
        @click.stop
      >
        <div class="flex items-center justify-between p-6 border-b border-secondary-200">
          <h3 class="text-xl font-bold text-secondary-900">
            {{
              legalPreviewType === "terms" ? "Terms of Service" : "Privacy Policy"
            }}
            Preview
          </h3>
          <button
            @click="showLegalPreview = false"
            class="p-2 text-secondary-400 hover:text-secondary-500 rounded-lg hover:bg-secondary-100"
          >
            <Icon name="heroicons:x-mark" class="h-6 w-6" />
          </button>
        </div>
        <div class="flex-1 p-6 overflow-hidden">
          <PdfViewer
            :url="legalPreviewPdfUrl"
            :require-auth="true"
            min-height="calc(90vh - 200px)"
            @loaded="onLegalPdfLoaded"
            @error="onLegalPdfError"
          />
        </div>
        <div class="flex justify-end p-6 border-t border-secondary-200">
          <button @click="showLegalPreview = false" class="btn btn-outline">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
useHead({
  title: "System Configuration - Admin - NFCGo",
});

definePageMeta({
  middleware: ["auth", "admin"],
  layout: "admin-management",
});

const { $api, $toast } = useNuxtApp();

// ================= MAIN TABS =================
const mainTabs = [
  { id: 'general', name: 'General', icon: 'heroicons:cog-6-tooth' },
  { id: 'pricing', name: 'Plan Pricing', icon: 'heroicons:currency-dollar' },
  { id: 'legal', name: 'Legal Documents', icon: 'heroicons:scale' },
  { id: 'payment', name: 'Payment Settings', icon: 'heroicons:credit-card' },
  { id: 'email', name: 'Email Settings', icon: 'heroicons:envelope' },
];
const activeMainTab = ref('general');

// ================= GENERAL SETTINGS =================
const savingGeneral = ref(false);
const uploadingLogo = ref(false);
const uploadingFavicon = ref(false);
const logoFileInput = ref(null);
const faviconFileInput = ref(null);

// Plain object File storage to bypass Vue 3 reactive() proxy wrapping
// (Native File inside reactive proxy coerces to [object Object] in FormData)
const __brandFiles = Object.create(null);
let __uploadLogoSubmitLock = false;
let __uploadFaviconSubmitLock = false;
const generalSettings = reactive({
  app_name: 'NFCGo',
  app_url: 'https://nfcgo.my',
  support_email: 'support@nfcgo.my',
  support_phone: '+60 12-345 6789',
  company_name: 'NFCGo Sdn Bhd',
  company_address: '',
  company_reg_no: '',
  company_tax_id: '',
  default_currency: 'MYR',
  default_language: 'en',
  default_timezone: 'Asia/Kuala_Lumpur',
  system_logo_url: '',
  system_favicon_url: '',
});

const handleLogoFileSelect = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  const allowedTypes = ['image/png', 'image/svg+xml', 'image/jpeg', 'image/webp'];
  if (!allowedTypes.includes(file.type)) {
    $toast.error('Invalid file type. Allowed: PNG, SVG, JPEG, WebP');
    if (logoFileInput.value) logoFileInput.value.value = '';
    return;
  }
  if (file.size > 2 * 1024 * 1024) {
    $toast.error('Logo file size must be less than 2MB');
    if (logoFileInput.value) logoFileInput.value.value = '';
    return;
  }

  // Store in plain object (non-reactive) to bypass Vue proxy wrapping
  __brandFiles.logo = file;

  // Sync scalar lock guard FIRST (before any await) to prevent double-submit in same tick
  if (__uploadLogoSubmitLock) return;
  __uploadLogoSubmitLock = true;
  uploadingLogo.value = true;
  try {
    const formData = new FormData();
    // Append DIRECTLY from plain object — use native File, bypass Proxy
    const logoFile = __brandFiles.logo;
    if (logoFile instanceof File && logoFile.size > 0) {
      formData.append('logo', logoFile, logoFile.name);
    }
    const response = await $api.post('/admin/system-settings/brand/upload-logo', formData);
    if (response.success) {
      // Prefer `public_url` when present — it is an APP_URL-based
      // absolute URL that definitely hits the /storage fallback route,
      // so even when the storage:link symlink is missing on Windows
      // or aaPanel, the preview <img> will resolve correctly.
      // Fall back to `url` (Storage::disk('public')->url() output) for
      // environments where a custom S3-compatible disk is in use.
      generalSettings.system_logo_url =
        (response.public_url && String(response.public_url).trim()) ||
        (response.url && String(response.url).trim()) ||
        '';
      // Also store the storage-relative path for sidebar/header consumers
      // that prepend a dynamic base via useRuntimeConfig.
      if (response.path) {
        generalSettings.system_logo_path = response.path;
      }
      $toast.success('System logo uploaded successfully');
      if (window) window.dispatchEvent(new CustomEvent('admin-brand-updated'));
    }
  } catch (e) {
    console.error('Error uploading logo:', e);
    // Flatten Laravel validation errors object
    let errMsg = e?.data?.message || 'Failed to upload logo';
    const valErrors = e?.data?.errors;
    if (valErrors && typeof valErrors === 'object') {
      const firstField = Object.keys(valErrors)[0];
      if (firstField && Array.isArray(valErrors[firstField]) && valErrors[firstField][0]) {
        errMsg = valErrors[firstField][0];
      }
    }
    $toast.error(errMsg);
  } finally {
    uploadingLogo.value = false;
    __uploadLogoSubmitLock = false;
    __brandFiles.logo = null;
    if (logoFileInput.value) logoFileInput.value.value = '';
  }
};

const removeLogo = async () => {
  if (!confirm('Remove system logo? Default logo will be used instead.')) return;
  try {
    const response = await $api.post('/admin/system-settings/brand/remove-logo');
    if (response.success) {
      generalSettings.system_logo_url = '';
      $toast.success('System logo removed successfully');
      if (window) window.dispatchEvent(new CustomEvent('admin-brand-updated'));
    }
  } catch (e) {
    console.error('Error removing logo:', e);
    $toast.error(e?.data?.message || 'Failed to remove logo');
  }
};

const handleFaviconFileSelect = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  const allowedTypes = ['image/x-icon', 'image/png', 'image/vnd.microsoft.icon'];
  if (!allowedTypes.includes(file.type)) {
    $toast.error('Invalid file type. Allowed: ICO, PNG');
    if (faviconFileInput.value) faviconFileInput.value.value = '';
    return;
  }
  if (file.size > 500 * 1024) {
    $toast.error('Favicon file size must be less than 500KB');
    if (faviconFileInput.value) faviconFileInput.value.value = '';
    return;
  }

  // Store in plain object (non-reactive) to bypass Vue proxy wrapping
  __brandFiles.favicon = file;

  // Sync scalar lock guard FIRST (before any await) to prevent double-submit in same tick
  if (__uploadFaviconSubmitLock) return;
  __uploadFaviconSubmitLock = true;
  uploadingFavicon.value = true;
  try {
    const formData = new FormData();
    // Append DIRECTLY from plain object — use native File, bypass Proxy
    const faviconFile = __brandFiles.favicon;
    if (faviconFile instanceof File && faviconFile.size > 0) {
      formData.append('favicon', faviconFile, faviconFile.name);
    }
    const response = await $api.post('/admin/system-settings/brand/upload-favicon', formData);
    if (response.success) {
      generalSettings.system_favicon_url = response.url;
      $toast.success('System favicon uploaded successfully');
      if (window) window.dispatchEvent(new CustomEvent('admin-brand-updated'));
    }
  } catch (e) {
    console.error('Error uploading favicon:', e);
    // Flatten Laravel validation errors object
    let errMsg = e?.data?.message || 'Failed to upload favicon';
    const valErrors = e?.data?.errors;
    if (valErrors && typeof valErrors === 'object') {
      const firstField = Object.keys(valErrors)[0];
      if (firstField && Array.isArray(valErrors[firstField]) && valErrors[firstField][0]) {
        errMsg = valErrors[firstField][0];
      }
    }
    $toast.error(errMsg);
  } finally {
    uploadingFavicon.value = false;
    __uploadFaviconSubmitLock = false;
    __brandFiles.favicon = null;
    if (faviconFileInput.value) faviconFileInput.value.value = '';
  }
};

const removeFavicon = async () => {
  if (!confirm('Remove system favicon? Default favicon will be used instead.')) return;
  try {
    const response = await $api.post('/admin/system-settings/brand/remove-favicon');
    if (response.success) {
      generalSettings.system_favicon_url = '';
      $toast.success('System favicon removed successfully');
      if (window) window.dispatchEvent(new CustomEvent('admin-brand-updated'));
    }
  } catch (e) {
    console.error('Error removing favicon:', e);
    $toast.error(e?.data?.message || 'Failed to remove favicon');
  }
};

const loadGeneralSettings = async () => {
  try {
    const response = await $api.get('/admin/system-settings/general');
    if (response.success && response.data) {
      // Merge raw response into reactive model
      Object.assign(generalSettings, response.data);

      // If `system_logo_public_url` exists (generated by the new backend
      // upload flow which builds an APP_URL-prefixed absolute URL) and
      // `system_logo_url` is either empty or a same-host /storage path
      // that the SPA on a different port may not reach, upgrade to the
      // reachable public URL so preview loads reliably.
      if (
        response.data.system_logo_public_url &&
        String(response.data.system_logo_public_url).trim()
      ) {
        const currentUrl = String(generalSettings.system_logo_url || '').trim();
        const isRelativeOrEmpty =
          !currentUrl ||
          currentUrl.startsWith('/') ||
          currentUrl.startsWith('data:') === false &&
            !/^https?:\/\//i.test(currentUrl);
        if (isRelativeOrEmpty) {
          generalSettings.system_logo_url = String(
            response.data.system_logo_public_url
          ).trim();
        }
      }
      if (response.data.system_logo_path) {
        generalSettings.system_logo_path = response.data.system_logo_path;
      }
      if (
        response.data.system_favicon_public_url &&
        String(response.data.system_favicon_public_url).trim() &&
        !String(generalSettings.system_favicon_url || '').trim().startsWith('http')
      ) {
        generalSettings.system_favicon_url = String(
          response.data.system_favicon_public_url
        ).trim();
      }
    }
  } catch (error) {
    console.log('No saved general settings found, using defaults');
  }
};

const saveGeneralSettings = async () => {
  savingGeneral.value = true;
  try {
    const response = await $api.post('/admin/system-settings/general', generalSettings);
    if (response.success) {
      $toast.success('General settings saved successfully');
    }
  } catch (error) {
    console.error('Error saving general settings:', error);
    $toast.error(error?.data?.message || 'Failed to save general settings');
  } finally {
    savingGeneral.value = false;
  }
};

// ================= PLAN PRICING =================
const plans = ref([]);
const loadingPrices = ref(false);
const savingPlans = ref(false);
const changedPlanIds = ref(new Set());

const loadPrices = async () => {
  loadingPrices.value = true;
  try {
    const response = await $api.get('/admin/plan-prices');
    if (response.success) {
      plans.value = response.data.map(plan => ({
        ...plan,
        originalPrice: plan.price,
        originalDescription: plan.description,
        originalFeatures: [...(plan.features || [])],
        originalIsActive: plan.is_active,
      }));
      changedPlanIds.value.clear();
    }
  } catch (error) {
    console.error('Error loading plan prices:', error);
    $toast.error('Failed to load plan prices');
  } finally {
    loadingPrices.value = false;
  }
};

const isPlanChanged = (plan) => changedPlanIds.value.has(plan.id);

const markPlanAsChanged = (plan) => {
  const featuresChanged = JSON.stringify(plan.features) !== JSON.stringify(plan.originalFeatures);
  const hasChanges =
    plan.price !== plan.originalPrice ||
    plan.description !== plan.originalDescription ||
    plan.is_active !== plan.originalIsActive ||
    featuresChanged;

  if (hasChanges) {
    changedPlanIds.value.add(plan.id);
  } else {
    changedPlanIds.value.delete(plan.id);
  }
};

const addPlanFeature = (plan) => {
  if (!plan.features) plan.features = [];
  plan.features.push('New feature');
  markPlanAsChanged(plan);
};

const removePlanFeature = (plan, index) => {
  plan.features.splice(index, 1);
  markPlanAsChanged(plan);
};

const togglePlanStatus = (plan) => {
  plan.is_active = !plan.is_active;
  markPlanAsChanged(plan);
};

const saveSinglePlan = async (plan) => {
  savingPlans.value = true;
  try {
    const response = await $api.put(`/admin/plan-prices/${plan.id}`, {
      price: plan.price,
      description: plan.description,
      features: plan.features,
      is_active: plan.is_active,
    });
    if (response.success) {
      plan.originalPrice = plan.price;
      plan.originalDescription = plan.description;
      plan.originalFeatures = [...plan.features];
      plan.originalIsActive = plan.is_active;
      changedPlanIds.value.delete(plan.id);
      $toast.success(`${plan.plan_type} plan updated successfully`);
    }
  } catch (error) {
    console.error('Error saving plan:', error);
    $toast.error(error.data?.message || 'Failed to update plan');
  } finally {
    savingPlans.value = false;
  }
};

const saveAllPlanChanges = async () => {
  if (!hasPlanChanges.value) return;
  savingPlans.value = true;
  try {
    const updates = changedPlansList.value.map(plan => ({
      id: plan.id,
      price: plan.price,
      description: plan.description,
      features: plan.features,
      is_active: plan.is_active,
    }));
    const promises = updates.map(update =>
      $api.put(`/admin/plan-prices/${update.id}`, update)
    );
    await Promise.all(promises);
    await loadPrices();
    $toast.success('All plan prices updated successfully');
  } catch (error) {
    console.error('Error saving all changes:', error);
    $toast.error('Failed to save all changes');
  } finally {
    savingPlans.value = false;
  }
};

const hasPlanChanges = computed(() => changedPlanIds.value.size > 0);
const changedPlansList = computed(() =>
  plans.value.filter(plan => changedPlanIds.value.has(plan.id))
);

const getPlanBorderClass = (planType) => {
  switch (planType) {
    case 'basic': return 'border-info-300 hover:border-info-400';
    case 'premium': return 'border-purple-300 hover:border-purple-400';
    case 'business': return 'border-success-300 hover:border-success-400';
    default: return 'border-secondary-300';
  }
};

const getPlanHeaderClass = (planType) => {
  switch (planType) {
    case 'basic': return 'bg-gradient-to-r from-info-500 to-info-600';
    case 'premium': return 'bg-gradient-to-r from-purple-500 to-purple-600';
    case 'business': return 'bg-gradient-to-r from-success-500 to-success-600';
    default: return 'bg-secondary-500';
  }
};

const formatPlanDate = (dateString) => {
  if (!dateString) return 'Never';
  return new Date(dateString).toLocaleDateString('en-MY', {
    year: 'numeric', month: 'short', day: 'numeric',
    hour: '2-digit', minute: '2-digit',
  });
};

// ================= LEGAL DOCUMENTS =================
const activeLegalTab = ref('terms');
const savingTerms = ref(false);
const savingPrivacy = ref(false);
const showLegalPreview = ref(false);
const legalPreviewType = ref('terms');
const uploadingTermsPdf = ref(false);
const uploadingPrivacyPdf = ref(false);
const termsFileInput = ref(null);
const privacyFileInput = ref(null);

const termsPdfStatus = reactive({ exists: false, fileInfo: null });
const privacyPdfStatus = reactive({ exists: false, fileInfo: null });

const legalPreviewPdfUrl = computed(() => {
  const type = legalPreviewType.value === "terms" ? "terms" : "privacy";
  return `http://localhost:8000/api/admin/legal/pdf/${type}/download`;
});

const onLegalPdfLoaded = () => console.log("PDF loaded successfully in preview");
const onLegalPdfError = (msg) => { console.error("PDF preview error:", msg); $toast.error("Failed to load PDF preview"); };

const termsData = reactive({
  content: "", version: 1.0,
  effective_date: new Date().toISOString().split("T")[0],
  updated_at: null, updater: null,
});
const privacyData = reactive({
  content: "", version: 1.0,
  effective_date: new Date().toISOString().split("T")[0],
  updated_at: null, updater: null,
});

const versionErrors = reactive({ terms: "", privacy: "" });

const validateTermsVersion = () => {
  const v = termsData.version;
  if (v === null || v === undefined || v === '') { termsData.version = 1.0; versionErrors.terms = ""; }
  else if (typeof v === 'number' && v < 1.0) { versionErrors.terms = "Version cannot be less than 1.0"; termsData.version = 1.0; }
  else { versionErrors.terms = ""; }
};
const validatePrivacyVersion = () => {
  const v = privacyData.version;
  if (v === null || v === undefined || v === '') { privacyData.version = 1.0; versionErrors.privacy = ""; }
  else if (typeof v === 'number' && v < 1.0) { versionErrors.privacy = "Version cannot be less than 1.0"; privacyData.version = 1.0; }
  else { versionErrors.privacy = ""; }
};

const checkPdfStatus = async (type) => {
  try {
    const response = await $api.get(`/admin/legal/pdf/${type}/status`);
    if (response.success) {
      const target = type === "terms" ? termsPdfStatus : privacyPdfStatus;
      Object.assign(target, { exists: response.exists, fileInfo: response.file_info });
    }
  } catch (e) { console.error("Error checking PDF status:", e); }
};

const loadLegalDocuments = async () => {
  try {
    const response = await $api.get("/legal/documents");
    if (response.success && response.data) {
      response.data.forEach((doc) => {
        if (doc.type === "terms_of_service") {
          Object.assign(termsData, {
            content: doc.content, version: doc.version,
            effective_date: doc.effective_date ? new Date(doc.effective_date).toISOString().split("T")[0] : termsData.effective_date,
            updated_at: doc.updated_at, updater: doc.updater,
          });
        } else if (doc.type === "privacy_policy") {
          Object.assign(privacyData, {
            content: doc.content, version: doc.version,
            effective_date: doc.effective_date ? new Date(doc.effective_date).toISOString().split("T")[0] : privacyData.effective_date,
            updated_at: doc.updated_at, updater: doc.updater,
          });
        }
      });
    }
  } catch (e) { console.error("Error loading documents:", e); }
};

const saveDocument = async (type) => {
  const isTerms = type === "terms_of_service";
  const docName = isTerms ? "Terms of Service" : "Privacy Policy";
  const data = isTerms ? termsData : privacyData;
  const pdfStatus = isTerms ? termsPdfStatus : privacyPdfStatus;

  if (!pdfStatus.exists) {
    $toast.error(`Please upload a PDF file first before saving ${docName} metadata.`);
    return;
  }

  if (isTerms) savingTerms.value = true;
  else savingPrivacy.value = true;

  try {
    const response = await $api.put(`/admin/legal/documents/${type}`, {
      content: `PDF document uploaded. Version: ${data.version}`,
      version: data.version,
      effective_date: data.effective_date,
    });
    if (response.success) {
      $toast.success(`${docName} metadata updated successfully`);
      await loadLegalDocuments();
    }
  } catch (e) {
    console.error("Error saving document:", e);
    $toast.error(`Failed to save ${docName} metadata. Please try again.`);
  } finally {
    if (isTerms) savingTerms.value = false;
    else savingPrivacy.value = false;
  }
};

const previewLegalDocument = (type) => {
  legalPreviewType.value = type;
  showLegalPreview.value = true;
};

const handleTermsFileSelect = async (event) => {
  const file = event.target.files[0];
  if (!file) return;
  if (file.type !== "application/pdf") { $toast.error("Please select a PDF file"); return; }
  if (file.size > 10 * 1024 * 1024) { $toast.error("File size must be less than 10MB"); return; }

  uploadingTermsPdf.value = true;
  try {
    const formData = new FormData();
    formData.append("pdf", toRaw(file));
    const response = await $api.post("/admin/legal/pdf/terms/upload", formData);
    if (response.success) {
      $toast.success("Terms of Service PDF uploaded successfully");
      if (response.original_filename && response.size) {
        Object.assign(termsPdfStatus, {
          exists: true,
          fileInfo: { original_filename: response.original_filename, size: response.size, url: response.path }
        });
      } else { await checkPdfStatus("terms"); }
    }
  } catch (e) {
    console.error("Error uploading PDF:", e);
    $toast.error("Failed to upload PDF. Please try again.");
  } finally {
    uploadingTermsPdf.value = false;
    if (termsFileInput.value) termsFileInput.value.value = "";
  }
};

const handlePrivacyFileSelect = async (event) => {
  const file = event.target.files[0];
  if (!file) return;
  if (file.type !== "application/pdf") { $toast.error("Please select a PDF file"); return; }
  if (file.size > 10 * 1024 * 1024) { $toast.error("File size must be less than 10MB"); return; }

  uploadingPrivacyPdf.value = true;
  try {
    const formData = new FormData();
    formData.append("pdf", toRaw(file));
    const response = await $api.post("/admin/legal/pdf/privacy/upload", formData);
    if (response.success) {
      $toast.success("Privacy Policy PDF uploaded successfully");
      if (response.original_filename && response.size) {
        Object.assign(privacyPdfStatus, {
          exists: true,
          fileInfo: { original_filename: response.original_filename, size: response.size, url: response.path }
        });
      } else { await checkPdfStatus("privacy"); }
    }
  } catch (e) {
    console.error("Error uploading PDF:", e);
    $toast.error("Failed to upload PDF. Please try again.");
  } finally {
    uploadingPrivacyPdf.value = false;
    if (privacyFileInput.value) privacyFileInput.value.value = "";
  }
};

const downloadLegalPdf = async (type) => {
  try {
    const config = useRuntimeConfig();
    const apiBaseUrl = config.public.apiBaseUrl;
    const tokenCookie = useCookie("auth-token");
    const token = tokenCookie.value;
    if (!token) { $toast.error("Authentication required. Please log in again."); return; }

    const url = `${apiBaseUrl}/admin/legal/pdf/${type}/download`;
    const response = await fetch(url, {
      headers: { Authorization: `Bearer ${token}`, Accept: "application/pdf" },
    });
    if (!response.ok) throw new Error("Download failed");

    const blob = await response.blob();
    const blobUrl = window.URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = blobUrl;
    link.download = type === "terms" ? "Terms-of-Service.pdf" : "Privacy-Policy.pdf";
    link.style.display = "none";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(blobUrl);
    $toast.success("PDF downloaded successfully");
  } catch (e) {
    console.error("Error downloading PDF:", e);
    $toast.error("Failed to download PDF. Please try again.");
  }
};

const formatLegalFileSize = (bytes) => {
  if (!bytes) return "0.00 KB";
  return (bytes / 1024).toFixed(2) + " KB";
};
const getLegalDisplayFileName = (filename) => {
  if (!filename) return "PDF uploaded";
  return filename.replace(/\.pdf$/i, '');
};
const formatLegalDate = (date) => {
  if (!date) return "";
  return new Date(date).toLocaleString("en-US", {
    year: "numeric", month: "long", day: "numeric",
    hour: "2-digit", minute: "2-digit",
  });
};

// ================= PAYMENT SETTINGS =================
const savingPayment = ref(false);
const paymentSettings = reactive({
  fiuu_merchant_id: '',
  fiuu_verify_key: '',
  fiuu_secret_key: '',
  fiuu_enabled: true,
  fiuu_environment: 'sandbox',
  bank_name: '',
  bank_account_name: '',
  bank_account_number: '',
  bank_transfer_enabled: true,
  ewallet_tng: true,
  ewallet_grabpay: true,
  ewallet_shopeepay: true,
  ewallet_duitnow: true,
});

const loadPaymentSettings = async () => {
  try {
    const response = await $api.get('/admin/system-settings/payment');
    if (response.success && response.data) {
      Object.assign(paymentSettings, response.data);
    }
  } catch (e) { console.log('No saved payment settings found, using defaults'); }
};

const savePaymentSettings = async () => {
  savingPayment.value = true;
  try {
    const response = await $api.post('/admin/system-settings/payment', paymentSettings);
    if (response.success) {
      $toast.success('Payment settings saved successfully');
    }
  } catch (e) {
    console.error('Error saving payment settings:', e);
    $toast.error(e?.data?.message || 'Failed to save payment settings');
  } finally {
    savingPayment.value = false;
  }
};

// ================= EMAIL SETTINGS =================
const savingEmail = ref(false);
const sendingTestEmail = ref(false);
const testEmailAddress = ref('');
const emailSettings = reactive({
  smtp_host: '',
  smtp_port: 587,
  smtp_username: '',
  smtp_password: '',
  smtp_encryption: 'tls',
  mailer: 'smtp',
  from_name: 'NFCGo',
  from_email: 'no-reply@nfcgo.my',
});

const loadEmailSettings = async () => {
  try {
    const response = await $api.get('/admin/system-settings/email');
    if (response.success && response.data) {
      Object.assign(emailSettings, response.data);
    }
  } catch (e) { console.log('No saved email settings found, using defaults'); }
};

const saveEmailSettings = async () => {
  savingEmail.value = true;
  try {
    const response = await $api.post('/admin/system-settings/email', emailSettings);
    if (response.success) {
      $toast.success('Email settings saved successfully');
    }
  } catch (e) {
    console.error('Error saving email settings:', e);
    $toast.error(e?.data?.message || 'Failed to save email settings');
  } finally {
    savingEmail.value = false;
  }
};

const sendTestEmail = async () => {
  if (!testEmailAddress.value) {
    $toast.error('Please enter an email address');
    return;
  }
  sendingTestEmail.value = true;
  try {
    const response = await $api.post('/admin/system-settings/email/test', {
      to: testEmailAddress.value,
    });
    if (response.success) {
      $toast.success('Test email sent successfully! Please check your inbox.');
    }
  } catch (e) {
    console.error('Error sending test email:', e);
    $toast.error(e?.data?.message || 'Failed to send test email');
  } finally {
    sendingTestEmail.value = false;
  }
};

// ================= LIFECYCLE =================
onMounted(async () => {
  await loadGeneralSettings();
  await loadPrices();
  await loadLegalDocuments();
  await checkPdfStatus("terms");
  await checkPdfStatus("privacy");
  await loadPaymentSettings();
  await loadEmailSettings();
});

onBeforeRouteLeave((to, from, next) => {
  if (hasPlanChanges.value) {
    const answer = window.confirm(
      'You have unsaved changes. Are you sure you want to leave?'
    );
    if (answer) next(); else next(false);
  } else {
    next();
  }
});
</script>
