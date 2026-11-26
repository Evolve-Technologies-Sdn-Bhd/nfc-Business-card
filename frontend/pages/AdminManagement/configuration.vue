<!-- pages/AdminManagement/configuration.vue -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Settings"
      eyebrow-icon="heroicons:cog-6-tooth"
      title="System Configuration"
      description="Manage branding, pricing, legal documents, payments and email delivery from one central location."
    />

    <!-- Section switcher -->
    <div class="admin-panel mb-6">
      <div class="admin-panel-body">
        <nav class="flex flex-wrap gap-1.5" aria-label="Configuration sections">
          <button
            v-for="tab in mainTabs"
            :key="tab.id"
            type="button"
            class="admin-btn admin-btn--sm"
            :class="activeMainTab === tab.id ? 'admin-btn--primary' : 'admin-btn--ghost'"
            @click="activeMainTab = tab.id"
          >
            <Icon :name="tab.icon" class="h-4 w-4" />
            {{ tab.name }}
          </button>
        </nav>
      </div>
    </div>

    <!-- ================= GENERAL SETTINGS TAB ================= -->
    <div v-show="activeMainTab === 'general'">
      <form class="space-y-6" @submit.prevent="saveGeneralSettings">
        <!-- Brand identity -->
        <AdminSectionCard
          title="Brand identity"
          subtitle="Logo and favicon shown across the console, sidebar and public pages."
        >
          <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
            <!-- System Logo -->
            <div>
              <label class="admin-label">System logo</label>
              <p class="admin-hint mb-3">
                PNG/SVG with transparency, 512×512px, max 2MB.
              </p>
              <div
                class="rounded-xl border-2 border-dashed p-6 transition-colors"
                :class="
                  generalSettings.system_logo_url
                    ? 'border-accent-400 bg-accent-50/40'
                    : 'border-secondary-300 bg-white'
                "
              >
                <div class="flex flex-col items-center justify-center">
                  <div
                    v-if="generalSettings.system_logo_url"
                    class="mb-4 flex h-32 w-32 items-center justify-center overflow-hidden rounded-xl border border-secondary-200 bg-secondary-100 shadow-sm"
                  >
                    <img
                      :src="generalSettings.system_logo_url + '?v=' + Date.now()"
                      alt="System Logo"
                      class="max-h-full max-w-full object-contain"
                    />
                  </div>
                  <div
                    v-else
                    class="mb-4 flex h-32 w-32 items-center justify-center rounded-xl border border-secondary-200 bg-secondary-100"
                  >
                    <Icon name="heroicons:photo" class="h-12 w-12 text-secondary-400" />
                  </div>
                  <div class="flex w-full flex-wrap items-center justify-center gap-3">
                    <input
                      ref="logoFileInput"
                      type="file"
                      accept="image/png,image/svg+xml,image/jpeg,image/webp"
                      class="hidden"
                      @change="handleLogoFileSelect"
                    />
                    <button
                      type="button"
                      :disabled="uploadingLogo || __uploadLogoSubmitLock"
                      class="admin-btn admin-btn--sm admin-btn--primary"
                      @click="$refs.logoFileInput.click()"
                    >
                      <Icon
                        :name="uploadingLogo ? 'heroicons:arrow-path' : 'heroicons:arrow-up-tray'"
                        class="h-4 w-4"
                        :class="{ 'animate-spin': uploadingLogo }"
                      />
                      {{ uploadingLogo ? 'Uploading…' : 'Upload logo' }}
                    </button>
                    <button
                      v-if="generalSettings.system_logo_url"
                      type="button"
                      class="admin-btn admin-btn--sm admin-btn--outline !text-error-600"
                      @click="removeLogo"
                    >
                      <Icon name="heroicons:trash" class="h-4 w-4" />
                      Remove
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- System Favicon -->
            <div>
              <label class="admin-label">System favicon</label>
              <p class="admin-hint mb-3">
                ICO/PNG, 64×64px or 32×32px, max 500KB. Shown in the browser tab.
              </p>
              <div
                class="rounded-xl border-2 border-dashed p-6 transition-colors"
                :class="
                  generalSettings.system_favicon_url
                    ? 'border-accent-400 bg-accent-50/40'
                    : 'border-secondary-300 bg-white'
                "
              >
                <div class="flex flex-col items-center justify-center">
                  <div
                    v-if="generalSettings.system_favicon_url"
                    class="mb-4 flex h-20 w-20 items-center justify-center overflow-hidden rounded-xl border border-secondary-200 bg-secondary-100 shadow-sm"
                  >
                    <img
                      :src="generalSettings.system_favicon_url + '?v=' + Date.now()"
                      alt="System Favicon"
                      class="max-h-full max-w-full object-contain"
                    />
                  </div>
                  <div
                    v-else
                    class="mb-4 flex h-20 w-20 items-center justify-center rounded-xl border border-secondary-200 bg-secondary-100"
                  >
                    <Icon name="heroicons:bookmark-square" class="h-10 w-10 text-secondary-400" />
                  </div>
                  <div class="flex w-full flex-wrap items-center justify-center gap-3">
                    <input
                      ref="faviconFileInput"
                      type="file"
                      accept="image/x-icon,image/png,image/vnd.microsoft.icon"
                      class="hidden"
                      @change="handleFaviconFileSelect"
                    />
                    <button
                      type="button"
                      :disabled="uploadingFavicon || __uploadFaviconSubmitLock"
                      class="admin-btn admin-btn--sm admin-btn--primary"
                      @click="$refs.faviconFileInput.click()"
                    >
                      <Icon
                        :name="uploadingFavicon ? 'heroicons:arrow-path' : 'heroicons:arrow-up-tray'"
                        class="h-4 w-4"
                        :class="{ 'animate-spin': uploadingFavicon }"
                      />
                      {{ uploadingFavicon ? 'Uploading…' : 'Upload favicon' }}
                    </button>
                    <button
                      v-if="generalSettings.system_favicon_url"
                      type="button"
                      class="admin-btn admin-btn--sm admin-btn--outline !text-error-600"
                      @click="removeFavicon"
                    >
                      <Icon name="heroicons:trash" class="h-4 w-4" />
                      Remove
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </AdminSectionCard>

        <!-- Application information -->
        <AdminSectionCard
          title="Application information"
          subtitle="Public identity and support contact details."
        >
          <div class="admin-form-section">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label class="admin-label">
                  App name <span class="text-error-600">*</span>
                </label>
                <input
                  v-model="generalSettings.app_name"
                  type="text"
                  class="admin-field"
                  placeholder="NFCGo"
                  required
                />
              </div>
              <div>
                <label class="admin-label">App URL</label>
                <input
                  v-model="generalSettings.app_url"
                  type="url"
                  class="admin-field"
                  placeholder="https://nfcgo.com"
                />
              </div>
              <div>
                <label class="admin-label">Support email</label>
                <input
                  v-model="generalSettings.support_email"
                  type="email"
                  class="admin-field"
                  placeholder="support@nfcgo.com"
                />
              </div>
              <div>
                <label class="admin-label">Support phone</label>
                <input
                  v-model="generalSettings.support_phone"
                  type="tel"
                  class="admin-field"
                  placeholder="+60 12-345 6789"
                />
              </div>
            </div>
          </div>
        </AdminSectionCard>

        <!-- Company information -->
        <AdminSectionCard
          title="Company information"
          subtitle="Legal entity details used on invoices and documents."
        >
          <div class="admin-form-section">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div class="md:col-span-2">
                <label class="admin-label">Company name</label>
                <input
                  v-model="generalSettings.company_name"
                  type="text"
                  class="admin-field"
                  placeholder="NFCGo Sdn Bhd"
                />
              </div>
              <div class="md:col-span-2">
                <label class="admin-label">Company address</label>
                <textarea
                  v-model="generalSettings.company_address"
                  rows="2"
                  class="admin-field"
                  placeholder="123 Jalan Contoh, 50000 Kuala Lumpur, Malaysia"
                ></textarea>
              </div>
              <div>
                <label class="admin-label">Registration no.</label>
                <input
                  v-model="generalSettings.company_reg_no"
                  type="text"
                  class="admin-field"
                  placeholder="1234567-X"
                />
              </div>
              <div>
                <label class="admin-label">Tax ID / SST no.</label>
                <input
                  v-model="generalSettings.company_tax_id"
                  type="text"
                  class="admin-field"
                  placeholder="C1234567890"
                />
              </div>
            </div>
          </div>
        </AdminSectionCard>

        <!-- System defaults -->
        <AdminSectionCard
          title="System defaults"
          subtitle="Default currency, language and timezone for new users."
        >
          <div class="admin-form-section">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label class="admin-label">Default currency</label>
                <select v-model="generalSettings.default_currency" class="admin-field">
                  <option value="MYR">Malaysian Ringgit (MYR)</option>
                  <option value="SGD">Singapore Dollar (SGD)</option>
                  <option value="USD">US Dollar (USD)</option>
                </select>
              </div>
              <div>
                <label class="admin-label">Default language</label>
                <select v-model="generalSettings.default_language" class="admin-field">
                  <option value="en">English</option>
                  <option value="ms">Bahasa Melayu</option>
                  <option value="zh">中文</option>
                </select>
              </div>
              <div>
                <label class="admin-label">Default timezone</label>
                <select v-model="generalSettings.default_timezone" class="admin-field">
                  <option value="Asia/Kuala_Lumpur">Asia/Kuala_Lumpur (GMT+8)</option>
                  <option value="Asia/Singapore">Asia/Singapore (GMT+8)</option>
                  <option value="UTC">UTC</option>
                </select>
              </div>
            </div>
          </div>
        </AdminSectionCard>

        <!-- Actions -->
        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
          <button
            type="button"
            class="admin-btn admin-btn--outline"
            @click="loadGeneralSettings"
          >
            <Icon name="heroicons:arrow-path" class="h-4 w-4" />
            Reset
          </button>
          <button
            type="submit"
            :disabled="savingGeneral"
            class="admin-btn admin-btn--primary"
          >
            <Icon
              :name="savingGeneral ? 'heroicons:arrow-path' : 'heroicons:check'"
              class="h-4 w-4"
              :class="{ 'animate-spin': savingGeneral }"
            />
            {{ savingGeneral ? 'Saving…' : 'Save general settings' }}
          </button>
        </div>
      </form>
    </div>

    <!-- ================= APPEARANCE TAB ================= -->
    <div v-show="activeMainTab === 'appearance'">
      <form class="space-y-6" @submit.prevent="saveAppearanceSettings">
        <AdminSectionCard
          title="System appearance"
          subtitle="Colour theme applied across the entire system for every visitor. Only administrators can change this — end users cannot."
        >
          <div
            v-if="loadingAppearance"
            class="flex items-center justify-center py-10 text-secondary-500"
          >
            <Icon name="heroicons:arrow-path" class="h-5 w-5 animate-spin" />
            <span class="ml-2 text-body-sm">Loading appearance…</span>
          </div>

          <div
            v-else
            class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
          >
            <button
              v-for="t in availableThemes"
              :key="t.key"
              type="button"
              class="relative rounded-xl border-2 p-4 text-left transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-accent-500 focus:ring-offset-2"
              :class="
                appearanceSettings.system_theme === t.key
                  ? 'border-accent-500 bg-accent-50/70 shadow-md'
                  : 'border-secondary-200 hover:border-accent-300 hover:bg-secondary-50'
              "
              :aria-pressed="appearanceSettings.system_theme === t.key"
              @click="appearanceSettings.system_theme = t.key"
            >
              <div class="mb-3 flex items-start justify-between">
                <div class="flex items-center gap-3">
                  <div
                    class="flex h-10 w-10 items-center justify-center rounded-lg border border-secondary-200 shadow-sm"
                    :style="{ backgroundColor: t.previewColor + '1A' }"
                  >
                    <Icon
                      :name="t.icon"
                      class="h-5 w-5"
                      :style="{ color: t.previewColor }"
                    />
                  </div>
                  <div>
                    <h4 class="text-label text-secondary-900">{{ t.name }}</h4>
                    <div class="mt-1 flex gap-1">
                      <span
                        class="h-3 w-3 rounded-full border border-secondary-200"
                        :style="{ backgroundColor: t.previewColor }"
                      ></span>
                    </div>
                  </div>
                </div>
                <div
                  v-if="appearanceSettings.system_theme === t.key"
                  class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-accent-600 text-white shadow"
                >
                  <Icon name="heroicons:check" class="h-4 w-4" />
                </div>
              </div>
              <p class="min-h-[32px] text-caption leading-relaxed text-secondary-600">
                {{ t.description }}
              </p>
            </button>
          </div>

          <div
            v-if="!loadingAppearance && availableThemes.length === 0"
            class="py-8 text-center text-body text-secondary-500"
          >
            Unable to load the theme list.
          </div>
        </AdminSectionCard>

        <div class="flex items-center justify-end gap-3">
          <p class="mr-auto text-caption text-secondary-500">
            Changes apply immediately to all visitors once saved.
          </p>
          <button
            type="submit"
            class="admin-btn admin-btn--primary"
            :disabled="savingAppearance || loadingAppearance"
          >
            <Icon
              :name="savingAppearance ? 'heroicons:arrow-path' : 'heroicons:check'"
              class="h-4 w-4"
              :class="{ 'animate-spin': savingAppearance }"
            />
            {{ savingAppearance ? 'Saving…' : 'Save appearance' }}
          </button>
        </div>
      </form>
    </div>

    <!-- ================= PLAN PRICING TAB ================= -->
    <div v-show="activeMainTab === 'pricing'" class="space-y-6">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h2 class="admin-panel-title">Subscription plan pricing</h2>
          <p class="text-body-sm text-secondary-500">
            Manage pricing for Basic, Premium and Business NFC card plans.
          </p>
        </div>
        <div class="flex items-center gap-2">
          <button
            class="admin-btn admin-btn--outline admin-btn--sm"
            :disabled="loadingPrices"
            @click="loadPrices"
          >
            <Icon name="heroicons:arrow-path" class="h-4 w-4" :class="{ 'animate-spin': loadingPrices }" />
            Refresh
          </button>
          <button
            class="admin-btn admin-btn--primary admin-btn--sm"
            :disabled="!hasPlanChanges || savingPlans"
            @click="saveAllPlanChanges"
          >
            <Icon name="heroicons:check" class="h-4 w-4" />
            {{ savingPlans ? 'Saving…' : 'Save all changes' }}
          </button>
        </div>
      </div>

      <AdminLoadingState v-if="loadingPrices" variant="cards" :rows="3" />

      <div v-else class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <div
          v-for="plan in plans"
          :key="plan.id"
          class="overflow-hidden rounded-2xl border-2 bg-white shadow-sm transition-all"
          :class="getPlanBorderClass(plan.plan_type)"
        >
          <div class="p-5" :class="getPlanHeaderClass(plan.plan_type)">
            <div class="flex items-center justify-between gap-3">
              <h3 class="text-h5 capitalize text-white">{{ plan.plan_type }} plan</h3>
              <AdminStatusBadge
                :tone="plan.is_active ? 'success' : 'neutral'"
                :label="plan.is_active ? 'Active' : 'Inactive'"
              />
            </div>
            <p class="mt-1 text-body-sm text-white/85">
              {{ plan.description || 'NFC Card Plan' }}
            </p>
          </div>

          <div class="space-y-5 p-5">
            <div>
              <label class="admin-label">Price ({{ plan.currency }})</label>
              <div class="relative">
                <span
                  class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-medium text-secondary-500"
                >
                  {{ plan.currency }}
                </span>
                <input
                  v-model.number="plan.price"
                  type="number"
                  step="0.01"
                  min="0"
                  class="admin-field !pl-16 text-2xl font-bold"
                  :class="{ '!border-warning-400 !bg-warning-50': isPlanChanged(plan) }"
                  @input="markPlanAsChanged(plan)"
                />
              </div>
              <p
                v-if="isPlanChanged(plan)"
                class="admin-hint flex items-center text-warning-600"
              >
                <Icon name="heroicons:exclamation-triangle" class="mr-1 h-4 w-4" />
                Unsaved changes
              </p>
            </div>

            <div>
              <label class="admin-label">Description</label>
              <textarea
                v-model="plan.description"
                rows="2"
                class="admin-field text-body-sm"
                :class="{ '!border-warning-400 !bg-warning-50': isPlanChanged(plan) }"
                @input="markPlanAsChanged(plan)"
              ></textarea>
            </div>

            <div>
              <label class="admin-label">Features</label>
              <div class="space-y-2">
                <div
                  v-for="(feature, index) in plan.features"
                  :key="index"
                  class="flex items-center gap-2"
                >
                  <Icon name="heroicons:check-circle" class="h-5 w-5 shrink-0 text-success-500" />
                  <input
                    v-model="plan.features[index]"
                    type="text"
                    class="admin-field flex-1 !py-1 text-body-sm"
                    :class="{ '!border-warning-400 !bg-warning-50': isPlanChanged(plan) }"
                    @input="markPlanAsChanged(plan)"
                  />
                  <button
                    type="button"
                    title="Remove feature"
                    class="admin-icon-btn !text-error-600"
                    @click="removePlanFeature(plan, index)"
                  >
                    <Icon name="heroicons:x-mark" class="h-5 w-5" />
                  </button>
                </div>
              </div>
              <button
                type="button"
                class="admin-btn admin-btn--ghost admin-btn--sm mt-2"
                @click="addPlanFeature(plan)"
              >
                <Icon name="heroicons:plus-circle" class="h-4 w-4" />
                Add feature
              </button>
            </div>

            <div class="flex items-center justify-between border-t border-secondary-200 pt-4">
              <div>
                <p class="text-body-sm font-semibold text-secondary-900">Active status</p>
                <p class="admin-hint">Visible to users at checkout.</p>
              </div>
              <button
                type="button"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                :class="plan.is_active ? 'bg-accent-500' : 'bg-secondary-300'"
                @click="togglePlanStatus(plan)"
              >
                <span
                  class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                  :class="plan.is_active ? 'translate-x-6' : 'translate-x-1'"
                ></span>
              </button>
            </div>

            <button
              v-if="isPlanChanged(plan)"
              type="button"
              :disabled="savingPlans"
              class="admin-btn admin-btn--primary w-full"
              @click="saveSinglePlan(plan)"
            >
              {{ savingPlans ? 'Saving…' : 'Save this plan' }}
            </button>
          </div>

          <div class="border-t border-secondary-200 px-5 py-3">
            <p class="text-caption text-secondary-500">
              Last updated: {{ formatPlanDate(plan.updated_at) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Change summary -->
      <div
        v-if="hasPlanChanges && !loadingPrices"
        class="admin-panel admin-panel-accent"
      >
        <div class="admin-panel-body">
          <div class="flex items-start gap-3">
            <span
              class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-warning-50 text-warning-600"
            >
              <Icon name="heroicons:exclamation-triangle" class="h-5 w-5" />
            </span>
            <div class="min-w-0">
              <p class="admin-panel-title">Unsaved changes</p>
              <p class="admin-panel-subtitle">
                You have changes in {{ changedPlansList.length }} plan(s). Save all changes to apply them.
              </p>
              <ul class="mt-3 space-y-1.5">
                <li
                  v-for="plan in changedPlansList"
                  :key="plan.id"
                  class="text-body-sm text-secondary-700"
                >
                  <span class="font-semibold capitalize text-secondary-900">{{ plan.plan_type }}</span>:
                  <span v-if="plan.originalPrice !== plan.price">
                    price {{ plan.currency }} {{ plan.originalPrice }} → {{ plan.currency }} {{ plan.price }}
                  </span>
                  <span
                    v-if="plan.originalDescription !== plan.description && plan.originalPrice !== plan.price"
                  >
                    ·
                  </span>
                  <span v-if="plan.originalDescription !== plan.description">
                    description updated
                  </span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Important notes -->
      <div v-if="!loadingPrices" class="admin-panel">
        <div class="admin-panel-body">
          <div class="flex items-start gap-3">
            <span
              class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-info-50 text-info-600"
            >
              <Icon name="heroicons:information-circle" class="h-5 w-5" />
            </span>
            <div>
              <p class="admin-panel-title">Important notes</p>
              <ul class="mt-2 space-y-1 text-body-sm text-secondary-600">
                <li>Price changes affect new orders immediately after saving.</li>
                <li>Existing users keep their current plan prices.</li>
                <li>Inactive plans are hidden from users during checkout.</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ================= LEGAL DOCUMENTS TAB ================= -->
    <div v-show="activeMainTab === 'legal'" class="space-y-6">
      <div class="admin-panel">
        <div class="admin-panel-body">
          <nav class="flex flex-wrap gap-1.5" aria-label="Legal document tabs">
            <button
              type="button"
              class="admin-btn admin-btn--sm"
              :class="activeLegalTab === 'terms' ? 'admin-btn--primary' : 'admin-btn--ghost'"
              @click="activeLegalTab = 'terms'"
            >
              Terms of Service
            </button>
            <button
              type="button"
              class="admin-btn admin-btn--sm"
              :class="activeLegalTab === 'privacy' ? 'admin-btn--primary' : 'admin-btn--ghost'"
              @click="activeLegalTab = 'privacy'"
            >
              Privacy Policy
            </button>
          </nav>
        </div>
      </div>

      <!-- Terms of Service -->
      <div v-show="activeLegalTab === 'terms'">
        <form class="space-y-6" @submit.prevent="saveDocument('terms_of_service')">
          <AdminSectionCard
            title="Terms of Service"
            subtitle="Publish the PDF and the metadata shown to users."
          >
            <div class="admin-form-section">
              <p class="admin-form-section-title">Document metadata</p>
              <p class="admin-form-section-desc">
                Version number and the date this document takes effect.
              </p>
              <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                  <label class="admin-label">
                    Version <span class="text-error-600">*</span>
                  </label>
                  <input
                    v-model.number="termsData.version"
                    type="number"
                    min="1.0"
                    step="0.1"
                    required
                    class="admin-field"
                    :class="versionErrors.terms ? '!border-error-500' : ''"
                    placeholder="1.0"
                    @input="validateTermsVersion"
                    @change="validateTermsVersion"
                  />
                  <p v-if="versionErrors.terms" class="admin-hint text-error-600">
                    {{ versionErrors.terms }}
                  </p>
                </div>
                <div>
                  <label class="admin-label">Effective date</label>
                  <input
                    v-model="termsData.effective_date"
                    type="date"
                    required
                    class="admin-field"
                  />
                </div>
              </div>
            </div>

            <div class="admin-form-section">
              <p class="admin-form-section-title">PDF upload</p>
              <p class="admin-form-section-desc">
                Upload the Terms of Service PDF (max 10MB), then set the version and save.
              </p>
              <div class="mt-4 flex flex-wrap items-center gap-3">
                <input
                  ref="termsFileInput"
                  type="file"
                  accept="application/pdf"
                  class="hidden"
                  @change="handleTermsFileSelect"
                />
                <button
                  type="button"
                  :disabled="uploadingTermsPdf"
                  class="admin-btn admin-btn--sm admin-btn--primary"
                  @click="$refs.termsFileInput.click()"
                >
                  <Icon
                    :name="uploadingTermsPdf ? 'heroicons:arrow-path' : 'heroicons:arrow-up-tray'"
                    class="h-4 w-4"
                    :class="{ 'animate-spin': uploadingTermsPdf }"
                  />
                  {{ uploadingTermsPdf ? 'Uploading…' : 'Choose PDF file' }}
                </button>
                <span class="text-helper text-secondary-500">Max 10MB</span>
              </div>

              <div
                v-if="termsPdfStatus.exists"
                class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-success-200 bg-success-50 p-3"
              >
                <div class="flex min-w-0 items-center gap-2 text-body-sm text-success-700">
                  <Icon name="heroicons:document-check" class="h-5 w-5 shrink-0 text-success-500" />
                  <span class="truncate">
                    {{ getLegalDisplayFileName(termsPdfStatus.fileInfo?.original_filename) }}
                    ({{ formatLegalFileSize(termsPdfStatus.fileInfo?.size) }})
                  </span>
                </div>
                <button
                  type="button"
                  class="admin-btn admin-btn--sm admin-btn--outline"
                  @click="downloadLegalPdf('terms')"
                >
                  <Icon name="heroicons:arrow-down-tray" class="h-4 w-4" />
                  Download
                </button>
              </div>
              <div
                v-else
                class="mt-4 flex items-center gap-2 rounded-xl border border-warning-200 bg-warning-50 p-3 text-body-sm text-warning-700"
              >
                <Icon name="heroicons:exclamation-triangle" class="h-5 w-5 shrink-0 text-warning-600" />
                No PDF uploaded yet. Upload a PDF file to enable saving.
              </div>
            </div>

            <template #footer>
              <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-end">
                <p
                  v-if="termsData.updated_at"
                  class="text-caption text-secondary-500 sm:mr-auto"
                >
                  Last updated {{ formatLegalDate(termsData.updated_at) }}
                  <span v-if="termsData.updater">
                    by {{ termsData.updater.first_name }} {{ termsData.updater.last_name }}
                  </span>
                </p>
                <button
                  type="button"
                  class="admin-btn admin-btn--outline admin-btn--sm"
                  @click="previewLegalDocument('terms')"
                >
                  <Icon name="heroicons:eye" class="h-4 w-4" />
                  Preview
                </button>
                <button
                  type="submit"
                  :disabled="savingTerms"
                  class="admin-btn admin-btn--primary admin-btn--sm"
                >
                  <Icon
                    v-if="savingTerms"
                    name="heroicons:arrow-path"
                    class="h-4 w-4 animate-spin"
                  />
                  {{ savingTerms ? 'Saving…' : 'Save changes' }}
                </button>
              </div>
            </template>
          </AdminSectionCard>
        </form>
      </div>

      <!-- Privacy Policy -->
      <div v-show="activeLegalTab === 'privacy'">
        <form class="space-y-6" @submit.prevent="saveDocument('privacy_policy')">
          <AdminSectionCard
            title="Privacy Policy"
            subtitle="Publish the PDF and the metadata shown to users."
          >
            <div class="admin-form-section">
              <p class="admin-form-section-title">Document metadata</p>
              <p class="admin-form-section-desc">
                Version number and the date this document takes effect.
              </p>
              <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                  <label class="admin-label">
                    Version <span class="text-error-600">*</span>
                  </label>
                  <input
                    v-model.number="privacyData.version"
                    type="number"
                    min="1.0"
                    step="0.1"
                    required
                    class="admin-field"
                    :class="versionErrors.privacy ? '!border-error-500' : ''"
                    placeholder="1.0"
                    @input="validatePrivacyVersion"
                    @change="validatePrivacyVersion"
                  />
                  <p v-if="versionErrors.privacy" class="admin-hint text-error-600">
                    {{ versionErrors.privacy }}
                  </p>
                </div>
                <div>
                  <label class="admin-label">Effective date</label>
                  <input
                    v-model="privacyData.effective_date"
                    type="date"
                    required
                    class="admin-field"
                  />
                </div>
              </div>
            </div>

            <div class="admin-form-section">
              <p class="admin-form-section-title">PDF upload</p>
              <p class="admin-form-section-desc">
                Upload the Privacy Policy PDF (max 10MB), then set the version and save.
              </p>
              <div class="mt-4 flex flex-wrap items-center gap-3">
                <input
                  ref="privacyFileInput"
                  type="file"
                  accept="application/pdf"
                  class="hidden"
                  @change="handlePrivacyFileSelect"
                />
                <button
                  type="button"
                  :disabled="uploadingPrivacyPdf"
                  class="admin-btn admin-btn--sm admin-btn--primary"
                  @click="$refs.privacyFileInput.click()"
                >
                  <Icon
                    :name="uploadingPrivacyPdf ? 'heroicons:arrow-path' : 'heroicons:arrow-up-tray'"
                    class="h-4 w-4"
                    :class="{ 'animate-spin': uploadingPrivacyPdf }"
                  />
                  {{ uploadingPrivacyPdf ? 'Uploading…' : 'Choose PDF file' }}
                </button>
                <span class="text-helper text-secondary-500">Max 10MB</span>
              </div>

              <div
                v-if="privacyPdfStatus.exists"
                class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-success-200 bg-success-50 p-3"
              >
                <div class="flex min-w-0 items-center gap-2 text-body-sm text-success-700">
                  <Icon name="heroicons:document-check" class="h-5 w-5 shrink-0 text-success-500" />
                  <span class="truncate">
                    {{ getLegalDisplayFileName(privacyPdfStatus.fileInfo?.original_filename) }}
                    ({{ formatLegalFileSize(privacyPdfStatus.fileInfo?.size) }})
                  </span>
                </div>
                <button
                  type="button"
                  class="admin-btn admin-btn--sm admin-btn--outline"
                  @click="downloadLegalPdf('privacy')"
                >
                  <Icon name="heroicons:arrow-down-tray" class="h-4 w-4" />
                  Download
                </button>
              </div>
              <div
                v-else
                class="mt-4 flex items-center gap-2 rounded-xl border border-warning-200 bg-warning-50 p-3 text-body-sm text-warning-700"
              >
                <Icon name="heroicons:exclamation-triangle" class="h-5 w-5 shrink-0 text-warning-600" />
                No PDF uploaded yet. Upload a PDF file to enable saving.
              </div>
            </div>

            <template #footer>
              <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-end">
                <p
                  v-if="privacyData.updated_at"
                  class="text-caption text-secondary-500 sm:mr-auto"
                >
                  Last updated {{ formatLegalDate(privacyData.updated_at) }}
                  <span v-if="privacyData.updater">
                    by {{ privacyData.updater.first_name }} {{ privacyData.updater.last_name }}
                  </span>
                </p>
                <button
                  type="button"
                  class="admin-btn admin-btn--outline admin-btn--sm"
                  @click="previewLegalDocument('privacy')"
                >
                  <Icon name="heroicons:eye" class="h-4 w-4" />
                  Preview
                </button>
                <button
                  type="submit"
                  :disabled="savingPrivacy"
                  class="admin-btn admin-btn--primary admin-btn--sm"
                >
                  <Icon
                    v-if="savingPrivacy"
                    name="heroicons:arrow-path"
                    class="h-4 w-4 animate-spin"
                  />
                  {{ savingPrivacy ? 'Saving…' : 'Save changes' }}
                </button>
              </div>
            </template>
          </AdminSectionCard>
        </form>
      </div>
    </div>

    <!-- ================= PAYMENT SETTINGS TAB ================= -->
    <div v-show="activeMainTab === 'payment'">
      <form class="space-y-6" @submit.prevent="savePaymentSettings">
        <!-- Fiuu Payment Gateway -->
        <AdminSectionCard
          title="Fiuu payment gateway"
          subtitle="Card payments via Fiuu (formerly Razer Merchant Services)."
        >
          <div class="admin-form-section">
            <p class="admin-form-section-title">API credentials</p>
            <p class="admin-form-section-desc">
              Keys issued to your Fiuu merchant account.
            </p>
            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label class="admin-label">Merchant ID</label>
                <input
                  v-model="paymentSettings.fiuu_merchant_id"
                  type="text"
                  class="admin-field"
                  placeholder="MXXXXXXX"
                />
              </div>
              <div>
                <label class="admin-label">Verify key</label>
                <input
                  v-model="paymentSettings.fiuu_verify_key"
                  type="text"
                  class="admin-field"
                  placeholder="Enter Verify Key"
                />
              </div>
              <div>
                <label class="admin-label">Secret key</label>
                <input
                  v-model="paymentSettings.fiuu_secret_key"
                  type="password"
                  class="admin-field"
                  placeholder="Enter Secret Key"
                />
              </div>
              <div>
                <label class="admin-label">Environment</label>
                <select v-model="paymentSettings.fiuu_environment" class="admin-field">
                  <option value="sandbox">Sandbox (Testing)</option>
                  <option value="production">Production (Live)</option>
                </select>
              </div>
            </div>
          </div>

          <div class="admin-form-section">
            <label class="flex cursor-pointer items-center justify-between gap-3">
              <span>
                <span class="block text-body-sm font-semibold text-secondary-900">
                  Enable Fiuu payment
                </span>
                <span class="admin-hint">Show card payments at checkout.</span>
              </span>
              <span
                class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors"
                :class="paymentSettings.fiuu_enabled ? 'bg-accent-500' : 'bg-secondary-300'"
              >
                <span
                  class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                  :class="paymentSettings.fiuu_enabled ? 'translate-x-6' : 'translate-x-1'"
                ></span>
                <input
                  v-model="paymentSettings.fiuu_enabled"
                  type="checkbox"
                  class="sr-only"
                />
              </span>
            </label>
          </div>
        </AdminSectionCard>

        <!-- Manual Bank Transfer -->
        <AdminSectionCard
          title="Manual bank transfer"
          subtitle="Offline payments verified by your team."
        >
          <div class="admin-form-section">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label class="admin-label">Bank name</label>
                <input
                  v-model="paymentSettings.bank_name"
                  type="text"
                  class="admin-field"
                  placeholder="Maybank / CIMB / Public Bank"
                />
              </div>
              <div>
                <label class="admin-label">Account holder name</label>
                <input
                  v-model="paymentSettings.bank_account_name"
                  type="text"
                  class="admin-field"
                  placeholder="NFCGo Sdn Bhd"
                />
              </div>
              <div class="md:col-span-2">
                <label class="admin-label">Account number</label>
                <input
                  v-model="paymentSettings.bank_account_number"
                  type="text"
                  class="admin-field"
                  placeholder="1234567890"
                />
              </div>
            </div>
          </div>

          <div class="admin-form-section">
            <label class="flex cursor-pointer items-center justify-between gap-3">
              <span>
                <span class="block text-body-sm font-semibold text-secondary-900">
                  Enable manual bank transfer
                </span>
                <span class="admin-hint">Let customers pay by transferring funds.</span>
              </span>
              <span
                class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors"
                :class="paymentSettings.bank_transfer_enabled ? 'bg-accent-500' : 'bg-secondary-300'"
              >
                <span
                  class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                  :class="paymentSettings.bank_transfer_enabled ? 'translate-x-6' : 'translate-x-1'"
                ></span>
                <input
                  v-model="paymentSettings.bank_transfer_enabled"
                  type="checkbox"
                  class="sr-only"
                />
              </span>
            </label>
          </div>
        </AdminSectionCard>

        <!-- E-Wallet -->
        <AdminSectionCard
          title="E-wallet payments"
          subtitle="Choose the wallets customers can pay with."
        >
          <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <label
              class="flex cursor-pointer items-center gap-3 rounded-xl border-2 p-4 transition"
              :class="
                paymentSettings.ewallet_tng
                  ? 'border-accent-500 bg-accent-50'
                  : 'border-secondary-200 hover:border-secondary-300'
              "
            >
              <input
                v-model="paymentSettings.ewallet_tng"
                type="checkbox"
                class="h-4 w-4 rounded border-secondary-300 text-accent-600 focus:ring-accent-500"
              />
              <span class="min-w-0">
                <span class="block text-body-sm font-semibold text-secondary-900">Touch 'n Go</span>
                <span class="block text-caption text-secondary-500">eWallet</span>
              </span>
            </label>
            <label
              class="flex cursor-pointer items-center gap-3 rounded-xl border-2 p-4 transition"
              :class="
                paymentSettings.ewallet_grabpay
                  ? 'border-accent-500 bg-accent-50'
                  : 'border-secondary-200 hover:border-secondary-300'
              "
            >
              <input
                v-model="paymentSettings.ewallet_grabpay"
                type="checkbox"
                class="h-4 w-4 rounded border-secondary-300 text-accent-600 focus:ring-accent-500"
              />
              <span class="min-w-0">
                <span class="block text-body-sm font-semibold text-secondary-900">GrabPay</span>
                <span class="block text-caption text-secondary-500">eWallet</span>
              </span>
            </label>
            <label
              class="flex cursor-pointer items-center gap-3 rounded-xl border-2 p-4 transition"
              :class="
                paymentSettings.ewallet_shopeepay
                  ? 'border-accent-500 bg-accent-50'
                  : 'border-secondary-200 hover:border-secondary-300'
              "
            >
              <input
                v-model="paymentSettings.ewallet_shopeepay"
                type="checkbox"
                class="h-4 w-4 rounded border-secondary-300 text-accent-600 focus:ring-accent-500"
              />
              <span class="min-w-0">
                <span class="block text-body-sm font-semibold text-secondary-900">ShopeePay</span>
                <span class="block text-caption text-secondary-500">eWallet</span>
              </span>
            </label>
            <label
              class="flex cursor-pointer items-center gap-3 rounded-xl border-2 p-4 transition"
              :class="
                paymentSettings.ewallet_duitnow
                  ? 'border-accent-500 bg-accent-50'
                  : 'border-secondary-200 hover:border-secondary-300'
              "
            >
              <input
                v-model="paymentSettings.ewallet_duitnow"
                type="checkbox"
                class="h-4 w-4 rounded border-secondary-300 text-accent-600 focus:ring-accent-500"
              />
              <span class="min-w-0">
                <span class="block text-body-sm font-semibold text-secondary-900">DuitNow</span>
                <span class="block text-caption text-secondary-500">QR Payment</span>
              </span>
            </label>
          </div>
        </AdminSectionCard>

        <!-- Actions -->
        <div class="flex justify-end">
          <button
            type="submit"
            :disabled="savingPayment"
            class="admin-btn admin-btn--primary"
          >
            <Icon
              v-if="savingPayment"
              name="heroicons:arrow-path"
              class="h-4 w-4 animate-spin"
            />
            <Icon v-else name="heroicons:check" class="h-4 w-4" />
            {{ savingPayment ? 'Saving…' : 'Save payment settings' }}
          </button>
        </div>
      </form>
    </div>

    <!-- ================= EMAIL SETTINGS TAB ================= -->
    <div v-show="activeMainTab === 'email'">
      <form class="space-y-6" @submit.prevent="saveEmailSettings">
        <!-- SMTP Configuration -->
        <AdminSectionCard
          title="SMTP configuration"
          subtitle="Outgoing mail server credentials."
        >
          <div class="admin-form-section">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label class="admin-label">SMTP host</label>
                <input
                  v-model="emailSettings.smtp_host"
                  type="text"
                  class="admin-field"
                  placeholder="smtp.example.com"
                />
              </div>
              <div>
                <label class="admin-label">SMTP port</label>
                <input
                  v-model.number="emailSettings.smtp_port"
                  type="number"
                  class="admin-field"
                  placeholder="587"
                />
              </div>
              <div>
                <label class="admin-label">SMTP username</label>
                <input
                  v-model="emailSettings.smtp_username"
                  type="text"
                  class="admin-field"
                  placeholder="no-reply@nfcgo.com"
                />
              </div>
              <div>
                <label class="admin-label">SMTP password</label>
                <input
                  v-model="emailSettings.smtp_password"
                  type="password"
                  class="admin-field"
                  placeholder="Enter new password to change"
                  @focus="clearSmtpPasswordMask"
                />
                <p class="admin-hint mt-1">
                  Leave as-is to keep the current password. Enter a new value to update it.
                </p>
              </div>
              <div>
                <label class="admin-label">Encryption</label>
                <select v-model="emailSettings.smtp_encryption" class="admin-field">
                  <option value="tls">TLS</option>
                  <option value="ssl">SSL</option>
                  <option value="">None</option>
                </select>
              </div>
              <div>
                <label class="admin-label">Mailer</label>
                <select v-model="emailSettings.mailer" class="admin-field">
                  <option value="smtp">SMTP</option>
                  <option value="sendmail">Sendmail</option>
                  <option value="log">Log (Debug)</option>
                </select>
              </div>
            </div>
          </div>
        </AdminSectionCard>

        <!-- From Address -->
        <AdminSectionCard
          title="Sender identity"
          subtitle="How outgoing emails are addressed."
        >
          <div class="admin-form-section">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
              <div>
                <label class="admin-label">
                  From name <span class="text-error-600">*</span>
                </label>
                <input
                  v-model="emailSettings.from_name"
                  type="text"
                  class="admin-field"
                  placeholder="NFCGo"
                />
              </div>
              <div>
                <label class="admin-label">
                  From email <span class="text-error-600">*</span>
                </label>
                <input
                  v-model="emailSettings.from_email"
                  type="email"
                  class="admin-field"
                  :class="emailErrors.from_email ? '!border-error-500' : ''"
                  placeholder="no-reply@nfcgo.com"
                  @input="validateFromEmail"
                  @blur="validateFromEmail"
                />
                <p v-if="emailErrors.from_email" class="admin-hint mt-1 text-error-600">
                  {{ emailErrors.from_email }}
                </p>
              </div>
            </div>
          </div>
        </AdminSectionCard>

        <!-- Test Email -->
        <AdminSectionCard
          title="Test delivery"
          subtitle="Send a test email to confirm your settings."
        >
          <div class="admin-form-section">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
              <div class="flex-1">
                <label class="admin-label">Send test email to</label>
                <input
                  v-model="testEmailAddress"
                  type="email"
                  class="admin-field"
                  placeholder="admin@example.com"
                />
              </div>
              <button
                type="button"
                :disabled="sendingTestEmail"
                class="admin-btn admin-btn--outline"
                @click="sendTestEmail"
              >
                <Icon
                  name="heroicons:paper-airplane"
                  class="h-4 w-4"
                  :class="{ 'animate-pulse': sendingTestEmail }"
                />
                {{ sendingTestEmail ? 'Sending…' : 'Send test email' }}
              </button>
            </div>
          </div>
        </AdminSectionCard>

        <!-- Actions -->
        <div class="flex justify-end">
          <button
            type="submit"
            :disabled="savingEmail"
            class="admin-btn admin-btn--primary"
          >
            <Icon
              v-if="savingEmail"
              name="heroicons:arrow-path"
              class="h-4 w-4 animate-spin"
            />
            <Icon v-else name="heroicons:check" class="h-4 w-4" />
            {{ savingEmail ? 'Saving…' : 'Save email settings' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Legal document preview dialog -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div
          v-if="showLegalPreview"
          class="admin-dialog-backdrop"
          @click.self="showLegalPreview = false"
        >
          <div
            class="admin-dialog flex flex-col"
            role="dialog"
            aria-modal="true"
            :aria-label="legalPreviewType === 'terms' ? 'Terms of Service preview' : 'Privacy Policy preview'"
            style="width: min(72rem, 100%); margin-top: 5vh; max-height: 90vh"
          >
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-accent-100 text-accent-700"
                >
                  <Icon name="heroicons:document-text" class="h-5 w-5" />
                </span>
                <div>
                  <p class="admin-panel-title">
                    {{ legalPreviewType === "terms" ? "Terms of Service" : "Privacy Policy" }}
                  </p>
                  <p class="admin-panel-subtitle">PDF preview</p>
                </div>
              </div>
              <button
                type="button"
                class="admin-icon-btn"
                aria-label="Close"
                @click="showLegalPreview = false"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <div class="admin-panel-body flex-1 overflow-y-auto">
              <PdfViewer
                :url="legalPreviewPdfUrl"
                :require-auth="true"
                min-height="calc(90vh - 200px)"
                @loaded="onLegalPdfLoaded"
                @error="onLegalPdfError"
              />
            </div>

            <div class="admin-dialog-foot">
              <button
                type="button"
                class="admin-btn admin-btn--outline"
                @click="showLegalPreview = false"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Shared confirmation dialog -->
    <AdminConfirmDialog
      v-model="confirmOpen"
      :title="confirmConfig.title"
      :message="confirmConfig.message"
      :confirm-label="confirmConfig.confirmLabel"
      :tone="confirmConfig.tone"
      :loading="confirmBusy"
      @confirm="runConfirm"
      @cancel="handleConfirmCancel"
    />
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

// Confirmation dialog state
const confirmOpen = ref(false);
const confirmBusy = ref(false);
const confirmConfig = ref({
  title: "",
  message: "",
  confirmLabel: "Confirm",
  tone: "primary",
});
let confirmAction = null;
let confirmCancelAction = null;

const askConfirm = (config, action, onCancel = null) => {
  confirmConfig.value = { confirmLabel: "Confirm", tone: "primary", ...config };
  confirmAction = action;
  confirmCancelAction = onCancel;
  confirmOpen.value = true;
};

const runConfirm = async () => {
  if (!confirmAction) return;
  confirmBusy.value = true;
  try {
    await confirmAction();
  } finally {
    confirmBusy.value = false;
    confirmOpen.value = false;
    confirmAction = null;
    confirmCancelAction = null;
  }
};

const handleConfirmCancel = () => {
  const cb = confirmCancelAction;
  confirmAction = null;
  confirmCancelAction = null;
  if (cb) cb();
};

// ================= MAIN TABS =================
const mainTabs = [
  { id: 'general', name: 'General', icon: 'heroicons:cog-6-tooth' },
  { id: 'appearance', name: 'Appearance', icon: 'heroicons:swatch' },
  { id: 'pricing', name: 'Plan Pricing', icon: 'heroicons:currency-dollar' },
  { id: 'legal', name: 'Legal Documents', icon: 'heroicons:scale' },
  { id: 'payment', name: 'Payment Settings', icon: 'heroicons:credit-card' },
  { id: 'email', name: 'Email Settings', icon: 'heroicons:envelope' },
];
const activeMainTab = ref('general');

// ================= APPEARANCE (SYSTEM THEME) =================
// Admin-only, system-wide colour theme. Applied to every visitor — end users
// cannot change it, and unset values fall back to Ink & Brass (zoraPro).
const { getThemes, applySystemTheme } = useTheme();
const availableThemes = getThemes();
const appearanceSettings = reactive({ system_theme: 'zoraPro' });
const loadingAppearance = ref(false);
const savingAppearance = ref(false);

const loadAppearanceSettings = async () => {
  loadingAppearance.value = true;
  try {
    const response = await $api.get('/admin/system-settings/appearance');
    if (response.success && response.data?.system_theme) {
      appearanceSettings.system_theme = response.data.system_theme;
    }
  } catch (error) {
    console.error('Error loading appearance settings:', error);
    $toast.error(error?.data?.message || 'Failed to load appearance settings');
  } finally {
    loadingAppearance.value = false;
  }
};

const saveAppearanceSettings = async () => {
  savingAppearance.value = true;
  try {
    const response = await $api.post('/admin/system-settings/appearance', {
      system_theme: appearanceSettings.system_theme,
    });
    if (response.success) {
      applySystemTheme(response.data?.system_theme || appearanceSettings.system_theme);
      $toast.success('System theme updated for all users');
    }
  } catch (error) {
    console.error('Error saving appearance settings:', error);
    $toast.error(error?.data?.message || 'Failed to save appearance settings');
  } finally {
    savingAppearance.value = false;
  }
};

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
  app_url: 'https://nfcgo.com',
  support_email: 'support@nfcgo.com',
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

const removeLogo = () => {
  askConfirm(
    {
      title: 'Remove system logo',
      message: 'Remove the system logo? The default logo will be used instead.',
      confirmLabel: 'Remove',
      tone: 'danger',
    },
    async () => {
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
    }
  );
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

const removeFavicon = () => {
  askConfirm(
    {
      title: 'Remove system favicon',
      message: 'Remove the system favicon? The default favicon will be used instead.',
      confirmLabel: 'Remove',
      tone: 'danger',
    },
    async () => {
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
    }
  );
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
    case 'premium': return 'border-accent-300 hover:border-accent-400';
    case 'business': return 'border-success-300 hover:border-success-400';
    default: return 'border-secondary-300';
  }
};

const getPlanHeaderClass = (planType) => {
  switch (planType) {
    case 'basic': return 'bg-gradient-to-r from-info-500 to-info-600';
    case 'premium': return 'bg-gradient-to-r from-accent-500 to-accent-600';
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

const runtimeConfig = useRuntimeConfig();
const legalPreviewPdfUrl = computed(() => {
  const type = legalPreviewType.value === "terms" ? "terms" : "privacy";
  const apiBaseUrl = runtimeConfig.public.apiBaseUrl || "http://localhost:8000/api";
  return `${apiBaseUrl}/admin/legal/pdf/${type}/download`;
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
const emailErrors = reactive({ from_email: '' });
const emailSettings = reactive({
  smtp_host: '',
  smtp_port: 587,
  smtp_username: '',
  smtp_password: '',
  smtp_encryption: 'tls',
  mailer: 'smtp',
  from_name: 'NFCGo',
  from_email: 'no-reply@nfcgo.com',
});

const clearSmtpPasswordMask = () => {
  if (emailSettings.smtp_password === '********') {
    emailSettings.smtp_password = '';
  }
};

const validateFromEmail = () => {
  const email = String(emailSettings.from_email || '').trim();
  if (!email) {
    emailErrors.from_email = 'From email is required';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    emailErrors.from_email = 'Please enter a valid email address';
  } else {
    emailErrors.from_email = '';
  }
  return !emailErrors.from_email;
};

const loadEmailSettings = async () => {
  try {
    const response = await $api.get('/admin/system-settings/email');
    if (response.success && response.data) {
      Object.assign(emailSettings, response.data);
    }
  } catch (e) { console.log('No saved email settings found, using defaults'); }
};

const saveEmailSettings = async () => {
  if (!validateFromEmail()) {
    $toast.error('Please fix the errors in the email settings form');
    return;
  }
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
  await loadAppearanceSettings();
  await loadPrices();
  await loadLegalDocuments();
  await checkPdfStatus("terms");
  await checkPdfStatus("privacy");
  await loadPaymentSettings();
  await loadEmailSettings();
});

onBeforeRouteLeave(async (to, from) => {
  if (!hasPlanChanges.value) return true;
  return await new Promise((resolve) => {
    askConfirm(
      {
        title: 'Discard unsaved changes?',
        message: 'You have unsaved plan changes. Leave without saving?',
        confirmLabel: 'Leave',
        tone: 'danger',
      },
      () => resolve(true),
      () => resolve(false)
    );
  });
});
</script>
