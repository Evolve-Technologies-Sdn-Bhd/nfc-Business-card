<!-- pages/UserAccount/register.vue -->
<template>
  <div class="lg-auth-root">
    <div class="lg-auth-split">
      <!-- ============================================================
           BRAND PANEL (Ink & Brass) — same shell as the login page
           ============================================================ -->
      <aside class="lg-ink-side ink-surface ink-grid">
        <div class="lg-ink-glow" aria-hidden="true"></div>

        <div class="lg-ink-inner">
          <!-- Brand row -->
          <div class="lg-brand-row">
            <NuxtLink to="/Homepage" class="lg-brand" aria-label="NFCGo home">
              <NFCGoLogo variant="dark" size="lg" />
            </NuxtLink>
            <NuxtLink to="/Homepage" class="lg-explore-pill">
              <Icon name="heroicons:arrow-left" class="h-4 w-4" />
              <span class="lg-explore-pill-text">Back to website</span>
            </NuxtLink>
          </div>

          <!-- Brand message -->
          <div class="lg-hero-copy">
            <span class="eyebrow-brass">
              <span class="dot"></span>
              Smart NFC Business Card
            </span>
            <h1 class="lg-hero-title">
              Create your digital identity,
              <span class="brass-grad">ready in one tap</span>
            </h1>
            <p class="lg-hero-lead">
              Set up your NFCGo account and start sharing your professional
              profile with a single tap.
            </p>
          </div>

          <!-- NFC card mockup (desktop) -->
          <div class="lg-card-stage">
            <span class="tap-ripple lg-ripple" aria-hidden="true"></span>

            <div class="nfc-mock lg-mock">
              <div class="nfc-mock__face nfc-face-ink">
                <div class="nfc-mock__sheen"></div>
                <div class="relative z-10 flex items-start justify-between">
                  <div class="flex flex-col">
                    <span
                      class="text-2xl font-black tracking-tighter brass-grad leading-none"
                      >NFCGo</span
                    >
                    <span class="lg-mock-sub">SMART DIGITAL IDENTITY</span>
                  </div>
                  <Icon name="heroicons:wifi" class="h-7 w-7 text-white/45" />
                </div>
                <div class="nfc-mock__chip relative z-10"></div>
                <div class="relative z-10 flex justify-between items-end">
                  <div>
                    <h3 class="nfc-mock__name text-white">Your Name</h3>
                    <p class="nfc-mock__role text-white">Job Title</p>
                  </div>
                  <div class="nfc-mock__qr">
                    <Icon name="heroicons:qr-code" class="h-8 w-8" />
                  </div>
                </div>
              </div>
            </div>

            <div class="lg-chip lg-chip-1 chip-ink">
              <Icon name="heroicons:bolt" class="lg-chip-ico" />
              Opens in 1 tap
            </div>
            <div class="lg-chip lg-chip-2 chip-ink">
              <Icon name="heroicons:device-phone-mobile" class="lg-chip-ico" />
              Works on iOS &amp; Android
            </div>
          </div>

          <!-- Trust ticks -->
          <div class="lg-ticks">
            <span>
              <Icon name="heroicons:check-circle" class="lg-tick-ico" />
              No app required
            </span>
            <span>
              <Icon name="heroicons:check-circle" class="lg-tick-ico" />
              Instant setup
            </span>
            <span>
              <Icon name="heroicons:check-circle" class="lg-tick-ico" />
              Secure sign-up
            </span>
          </div>

          <!-- Explore CTA (desktop) -->
          <div class="lg-explore">
            <span class="lg-explore-label">Already with NFCGo?</span>
            <div class="lg-explore-links">
              <NuxtLink to="/UserAccount/login" class="btn-brass">
                <Icon name="heroicons:arrow-right-on-rectangle" class="h-4 w-4" />
                Sign in instead
              </NuxtLink>
              <NuxtLink to="/Homepage#pricing" class="btn-ghost-light">
                View pricing
              </NuxtLink>
              <NuxtLink to="/Homepage#how-it-works" class="lg-explore-text">
                How it works
              </NuxtLink>
            </div>
          </div>
        </div>
      </aside>

      <!-- ============================================================
           AUTH PANEL (Paper)
           ============================================================ -->
      <main class="lg-form-side">
        <div class="lg-form-wrap lg-form-wrap--wide">
          <!-- Mobile brand row -->
          <NuxtLink to="/Homepage" class="lg-brand-mobile" aria-label="NFCGo home">
            <NFCGoLogo variant="light" size="md" />
          </NuxtLink>

          <div class="lg-card">
            <header class="lg-card-head">
              <span class="lg-kicker">Get started</span>
              <h2 class="lg-title">Create your account</h2>
              <p class="lg-subtitle">
                Start building your smart digital business card in minutes.
              </p>
            </header>

            <form class="lg-form lg-form--two-col" @submit.prevent="handleRegister">
              <div class="lg-name-grid">
                <div class="lg-field">
                  <label for="first_name" class="lg-label">First name</label>
                  <div class="lg-control">
                    <Icon name="heroicons:user" class="lg-control-ico" />
                    <input
                      id="first_name"
                      v-model="form.first_name"
                      type="text"
                      required
                      class="lg-input"
                      :class="errors.first_name ? 'lg-input--error' : ''"
                      placeholder="John"
                      :disabled="loading"
                      autocomplete="given-name"
                      :aria-invalid="!!errors.first_name"
                      :aria-describedby="errors.first_name ? 'first_name-error' : undefined"
                    />
                  </div>
                  <p
                    v-if="errors.first_name"
                    id="first_name-error"
                    class="lg-error"
                    role="alert"
                  >
                    <Icon name="heroicons:exclamation-circle" class="lg-error-ico" />
                    {{ errors.first_name[0] }}
                  </p>
                </div>

                <div class="lg-field">
                  <label for="last_name" class="lg-label">Last name</label>
                  <div class="lg-control">
                    <Icon name="heroicons:user" class="lg-control-ico" />
                    <input
                      id="last_name"
                      v-model="form.last_name"
                      type="text"
                      required
                      class="lg-input"
                      :class="errors.last_name ? 'lg-input--error' : ''"
                      placeholder="Doe"
                      :disabled="loading"
                      autocomplete="family-name"
                      :aria-invalid="!!errors.last_name"
                      :aria-describedby="errors.last_name ? 'last_name-error' : undefined"
                    />
                  </div>
                  <p
                    v-if="errors.last_name"
                    id="last_name-error"
                    class="lg-error"
                    role="alert"
                  >
                    <Icon name="heroicons:exclamation-circle" class="lg-error-ico" />
                    {{ errors.last_name[0] }}
                  </p>
                </div>
              </div>

              <div class="lg-field lg-field--full">
                <label for="email" class="lg-label">Email address</label>
                <div class="lg-control">
                  <Icon name="heroicons:envelope" class="lg-control-ico" />
                  <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    class="lg-input"
                    :class="errors.email ? 'lg-input--error' : ''"
                    placeholder="john@example.com"
                    :disabled="loading"
                    autocomplete="email"
                    inputmode="email"
                    :aria-invalid="!!errors.email"
                    :aria-describedby="errors.email ? 'email-error' : undefined"
                  />
                </div>
                <p v-if="errors.email" id="email-error" class="lg-error" role="alert">
                  <Icon name="heroicons:exclamation-circle" class="lg-error-ico" />
                  {{ errors.email[0] }}
                </p>
              </div>

              <div class="lg-field">
                <label for="password" class="lg-label">Password</label>
                <div class="lg-control">
                  <Icon name="heroicons:lock-closed" class="lg-control-ico" />
                  <input
                    id="password"
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    required
                    class="lg-input lg-input--pw"
                    :class="errors.password ? 'lg-input--error' : ''"
                    placeholder="Create a strong password"
                    :disabled="loading"
                    autocomplete="new-password"
                    aria-describedby="password-requirements"
                    :aria-invalid="!!errors.password"
                  />
                  <button
                    type="button"
                    class="lg-toggle"
                    :disabled="loading"
                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                    :aria-pressed="showPassword"
                    @click="showPassword = !showPassword"
                  >
                    <Icon
                      :name="showPassword ? 'heroicons:eye-slash' : 'heroicons:eye'"
                      class="h-5 w-5"
                    />
                  </button>
                </div>

                <!-- Password strength -->
                <div v-if="form.password" class="lg-strength" aria-live="polite">
                  <div
                    class="lg-strength-bars"
                    role="group"
                    :aria-label="`Password strength: ${getPasswordStrengthText(passwordStrength)}`"
                  >
                    <span
                      v-for="segment in 4"
                      :key="segment"
                      class="lg-strength-seg"
                      :class="
                        segment <= passwordStrength
                          ? `is-on is-${strengthTone}`
                          : ''
                      "
                      aria-hidden="true"
                    ></span>
                  </div>
                  <div class="lg-strength-meta">
                    <span class="lg-strength-text" :class="`is-${strengthTone}`">
                      {{ getPasswordStrengthText(passwordStrength) }}
                    </span>
                    <span class="lg-strength-count">
                      {{ form.password.length }} characters
                    </span>
                  </div>
                  <ul id="password-requirements" class="lg-reqs">
                    <li class="lg-req" :class="hasMinLength ? 'lg-req--ok' : ''">
                      <Icon
                        :name="hasMinLength ? 'heroicons:check-circle' : 'heroicons:minus-circle'"
                        class="lg-req-ico"
                      />
                      Min 8 characters
                    </li>
                    <li class="lg-req" :class="hasMixedCase ? 'lg-req--ok' : ''">
                      <Icon
                        :name="hasMixedCase ? 'heroicons:check-circle' : 'heroicons:minus-circle'"
                        class="lg-req-ico"
                      />
                      Upper + lowercase
                    </li>
                    <li class="lg-req" :class="hasNumber ? 'lg-req--ok' : ''">
                      <Icon
                        :name="hasNumber ? 'heroicons:check-circle' : 'heroicons:minus-circle'"
                        class="lg-req-ico"
                      />
                      Contains a number
                    </li>
                    <li class="lg-req" :class="hasSymbol ? 'lg-req--ok' : ''">
                      <Icon
                        :name="hasSymbol ? 'heroicons:check-circle' : 'heroicons:minus-circle'"
                        class="lg-req-ico"
                      />
                      Special symbol
                    </li>
                  </ul>
                </div>

                <p v-if="errors.password" class="lg-error" role="alert">
                  <Icon name="heroicons:exclamation-circle" class="lg-error-ico" />
                  {{ errors.password[0] }}
                </p>
              </div>

              <div class="lg-field">
                <label for="password_confirmation" class="lg-label">
                  Confirm password
                </label>
                <div class="lg-control">
                  <Icon name="heroicons:lock-closed" class="lg-control-ico" />
                  <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    required
                    class="lg-input lg-input--pw-wide"
                    :class="
                      errors.password_confirmation || passwordsMismatch
                        ? 'lg-input--error'
                        : ''
                    "
                    placeholder="Confirm your password"
                    :disabled="loading"
                    autocomplete="new-password"
                    :aria-invalid="passwordsMismatch || !!errors.password_confirmation"
                    :aria-describedby="
                      passwordsMismatch ? 'password-confirmation-status' : undefined
                    "
                  />
                  <div class="lg-trail-group">
                    <span
                      v-if="passwordsMatch || passwordsMismatch"
                      class="lg-match-ico"
                      :class="passwordsMatch ? 'is-ok' : 'is-bad'"
                      aria-hidden="true"
                    >
                      <Icon
                        :name="
                          passwordsMatch
                            ? 'heroicons:check-circle'
                            : 'heroicons:x-circle'
                        "
                        class="h-full w-full"
                      />
                    </span>
                    <button
                      type="button"
                      class="lg-toggle"
                      :disabled="loading"
                      :aria-label="
                        showConfirmPassword ? 'Hide password' : 'Show password'
                      "
                      :aria-pressed="showConfirmPassword"
                      @click="showConfirmPassword = !showConfirmPassword"
                    >
                      <Icon
                        :name="
                          showConfirmPassword ? 'heroicons:eye-slash' : 'heroicons:eye'
                        "
                        class="h-5 w-5"
                      />
                    </button>
                  </div>
                </div>

                <p
                  v-if="errors.password_confirmation"
                  class="lg-error"
                  role="alert"
                >
                  <Icon name="heroicons:exclamation-circle" class="lg-error-ico" />
                  {{ errors.password_confirmation[0] }}
                </p>
                <p
                  v-if="passwordsMismatch && !errors.password_confirmation"
                  id="password-confirmation-status"
                  class="lg-error"
                  role="alert"
                >
                  <Icon name="heroicons:information-circle" class="lg-error-ico" />
                  Passwords do not match
                </p>
                <p
                  v-else-if="passwordsMatch"
                  class="lg-strength-text is-strong"
                  style="margin-top: 0.45rem; display: block; font-size: 0.8rem"
                  role="status"
                >
                  Passwords match
                </p>
              </div>

              <div class="lg-field">
                <label for="company" class="lg-label">Company (optional)</label>
                <div class="lg-control">
                  <Icon name="heroicons:building-office" class="lg-control-ico" />
                  <input
                    id="company"
                    v-model="form.company"
                    type="text"
                    class="lg-input"
                    :class="errors.company ? 'lg-input--error' : ''"
                    placeholder="Your company name"
                    :disabled="loading"
                    autocomplete="organization"
                  />
                </div>
                <p v-if="errors.company" class="lg-error" role="alert">
                  <Icon name="heroicons:exclamation-circle" class="lg-error-ico" />
                  {{ errors.company[0] }}
                </p>
              </div>

              <div class="lg-field">
                <label for="job_title" class="lg-label">Job title (optional)</label>
                <div class="lg-control">
                  <Icon name="heroicons:briefcase" class="lg-control-ico" />
                  <input
                    id="job_title"
                    v-model="form.job_title"
                    type="text"
                    class="lg-input"
                    :class="errors.job_title ? 'lg-input--error' : ''"
                    placeholder="Your job title"
                    :disabled="loading"
                    autocomplete="organization-title"
                  />
                </div>
                <p v-if="errors.job_title" class="lg-error" role="alert">
                  <Icon name="heroicons:exclamation-circle" class="lg-error-ico" />
                  {{ errors.job_title[0] }}
                </p>
              </div>

              <div class="lg-check-row">
                <input
                  id="terms"
                  v-model="form.terms"
                  type="checkbox"
                  required
                  class="lg-checkbox"
                  :disabled="loading"
                />
                <label for="terms" class="lg-check-label">
                  I agree to the
                  <button
                    type="button"
                    class="lg-link-btn"
                    @click="showTermsModal = true"
                  >
                    Terms of Service
                  </button>
                  and
                  <button
                    type="button"
                    class="lg-link-btn"
                    @click="showPrivacyModal = true"
                  >
                    Privacy Policy
                  </button>
                </label>
              </div>
              <p v-if="errors.terms" class="lg-error" role="alert">
                <Icon name="heroicons:exclamation-circle" class="lg-error-ico" />
                {{ errors.terms[0] }}
              </p>

              <div class="lg-check-row">
                <input
                  id="marketing"
                  v-model="form.marketing"
                  type="checkbox"
                  class="lg-checkbox"
                  :disabled="loading"
                />
                <label for="marketing" class="lg-check-label">
                  I would like to receive marketing emails about new features and
                  updates
                </label>
              </div>

              <button
                type="submit"
                :disabled="loading || !form.terms || !isFormValid"
                :aria-busy="loading"
                class="btn-brass lg-submit"
              >
                <NFCGoWaveLoader
                  v-if="loading"
                  variant="dotPulse"
                  size="xs"
                  :showText="false"
                  inline
                />
                <Icon
                  v-else
                  name="heroicons:user-plus"
                  class="h-5 w-5"
                />
                <span>{{ loading ? "Creating account…" : "Create account" }}</span>
              </button>

              <p class="lg-pill">
                <Icon name="heroicons:sparkles" class="lg-pill-ico" />
                14-day free trial of our Professional plan. No credit card
                required.
              </p>
            </form>

            <!-- Social signup -->
            <div class="lg-divider"><span>or sign up with</span></div>
            <div class="lg-oauth">
              <button
                type="button"
                class="lg-oauth-btn"
                :disabled="loading"
                title="Sign up with Google"
                @click="handleGoogleSignup"
              >
                <Icon name="logos:google-icon" class="h-5 w-5" />
                <span>Google</span>
              </button>
              <button
                type="button"
                class="lg-oauth-btn"
                :disabled="loading"
                title="Sign up with Apple"
                @click="handleAppleSignup"
              >
                <Icon name="logos:apple" class="h-5 w-5" />
                <span>Apple</span>
              </button>
            </div>

            <p class="lg-register">
              Already have an account?
              <NuxtLink to="/UserAccount/login">Sign in here</NuxtLink>
            </p>
          </div>

          <!-- Trust strip -->
          <div class="lg-trust">
            <span>
              <Icon name="heroicons:shield-check" class="h-4 w-4" />
              SSL secured
            </span>
            <span>
              <Icon name="heroicons:lock-closed" class="h-4 w-4" />
              Privacy protected
            </span>
            <NuxtLink to="/Homepage#faq" class="lg-trust-link">
              <Icon name="heroicons:question-mark-circle" class="h-4 w-4" />
              Help
            </NuxtLink>
          </div>
        </div>
      </main>
    </div>

    <!-- ============================================================
         Terms of Service
         ============================================================ -->
    <div
      v-if="showTermsModal"
      class="lg-modal-overlay"
      @click.self="showTermsModal = false"
    >
      <div
        class="lg-modal lg-modal--wide"
        role="dialog"
        aria-modal="true"
        aria-labelledby="lg-terms-title"
      >
        <div class="lg-modal-bar">
          <div>
            <h3 id="lg-terms-title" class="lg-modal-title">Terms of Service</h3>
            <p v-if="termsDocument" class="lg-modal-meta">
              Version {{ termsDocument.version }} • Effective
              {{ formatDate(termsDocument.effective_date) }}
            </p>
          </div>
          <button
            type="button"
            class="lg-modal-close"
            aria-label="Close Terms of Service"
            @click="showTermsModal = false"
          >
            <Icon name="heroicons:x-mark" class="h-5 w-5" />
          </button>
        </div>
        <div class="lg-modal-body">
          <PdfViewer
            url="http://localhost:8000/api/legal/pdf/terms/view"
            :require-auth="false"
            :disable-download="true"
            min-height="calc(85vh - 200px)"
          />
        </div>
        <div class="lg-modal-foot">
          <button
            type="button"
            class="lg-oauth-btn"
            style="width: 100%"
            @click="showTermsModal = false"
          >
            <Icon name="heroicons:x-mark" class="h-5 w-5" />
            <span>Close</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ============================================================
         Privacy Policy
         ============================================================ -->
    <div
      v-if="showPrivacyModal"
      class="lg-modal-overlay"
      @click.self="showPrivacyModal = false"
    >
      <div
        class="lg-modal lg-modal--wide"
        role="dialog"
        aria-modal="true"
        aria-labelledby="lg-privacy-title"
      >
        <div class="lg-modal-bar">
          <div>
            <h3 id="lg-privacy-title" class="lg-modal-title">Privacy Policy</h3>
            <p v-if="privacyDocument" class="lg-modal-meta">
              Version {{ privacyDocument.version }} • Effective
              {{ formatDate(privacyDocument.effective_date) }}
            </p>
          </div>
          <button
            type="button"
            class="lg-modal-close"
            aria-label="Close Privacy Policy"
            @click="showPrivacyModal = false"
          >
            <Icon name="heroicons:x-mark" class="h-5 w-5" />
          </button>
        </div>
        <div class="lg-modal-body">
          <PdfViewer
            url="http://localhost:8000/api/legal/pdf/privacy/view"
            :require-auth="false"
            :disable-download="true"
            min-height="calc(85vh - 200px)"
          />
        </div>
        <div class="lg-modal-foot">
          <button
            type="button"
            class="lg-oauth-btn"
            style="width: 100%"
            @click="showPrivacyModal = false"
          >
            <Icon name="heroicons:x-mark" class="h-5 w-5" />
            <span>Close</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ============================================================
         2FA setup
         ============================================================ -->
    <div v-if="show2FASetup" class="lg-modal-overlay">
      <div
        class="lg-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="lg-2fa-setup-title"
      >
        <div class="lg-modal-head">
          <span class="lg-modal-mark">
            <Icon name="heroicons:shield-check" class="h-6 w-6" />
          </span>
          <h3 id="lg-2fa-setup-title" class="lg-modal-title">
            Set up two-factor authentication
          </h3>
          <p class="lg-modal-text">
            Secure your account with an additional layer of protection.
          </p>
        </div>

        <div class="lg-qr">
          <div class="lg-qr-box">
            <Icon name="heroicons:qr-code" class="h-14 w-14" />
          </div>
          <p class="lg-qr-hint">
            Scan this QR code with your authenticator app
          </p>
          <p class="lg-qr-apps">
            Recommended: Google Authenticator, Authy, 1Password
          </p>
          <button
            type="button"
            class="lg-inline-link"
            style="margin-top: 0.75rem"
            @click="showManualSetup = !showManualSetup"
          >
            Can't scan? Enter code manually
          </button>
          <p v-if="showManualSetup" class="lg-manual">JBSWY3DPEHPK3PXP</p>
        </div>

        <form class="lg-modal-form" style="margin-top: 1.4rem" @submit.prevent="handle2FASetup">
          <div class="lg-field">
            <label class="lg-label lg-centered block" for="two_factor_code">
              Enter the 6-digit verification code
            </label>
            <input
              id="two_factor_code"
              v-model="twoFactorCode"
              type="text"
              inputmode="numeric"
              maxlength="6"
              class="lg-otp"
              placeholder="000000"
              :disabled="loading"
              :aria-invalid="!!errors.code"
            />
            <p v-if="errors.code" class="lg-error lg-centered" role="alert">
              <Icon name="heroicons:exclamation-circle" class="lg-error-ico" />
              {{ errors.code[0] }}
            </p>
          </div>

          <div class="lg-modal-actions">
            <button
              type="button"
              class="lg-oauth-btn"
              :disabled="loading"
              @click="skip2FASetup"
            >
              Skip for now
            </button>
            <button
              type="submit"
              class="btn-brass lg-submit"
              :disabled="loading || twoFactorCode.length !== 6"
            >
              <span v-if="loading" class="lg-spin lg-spin--light"></span>
              {{ loading ? "Verifying…" : "Complete setup" }}
            </button>
          </div>

          <p class="lg-modal-text" style="text-align: center">
            You can always enable 2FA later in your account settings.
          </p>
        </form>
      </div>
    </div>

    <!-- ============================================================
         Registration success
         ============================================================ -->
    <div v-if="showSuccessModal" class="lg-modal-overlay">
      <div
        class="lg-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="lg-success-title"
      >
        <div class="lg-modal-head">
          <span class="lg-modal-mark lg-modal-mark--success">
            <Icon name="heroicons:check" class="h-6 w-6" />
          </span>
          <h3 id="lg-success-title" class="lg-modal-title">Welcome to NFCGo</h3>
          <p class="lg-modal-text">
            Your account has been created successfully. Let's choose your plan
            and get started.
          </p>
        </div>

        <button type="button" class="btn-brass lg-submit" @click="goToPlanSelection">
          <Icon name="heroicons:arrow-right" class="h-5 w-5" />
          <span>Choose your plan</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
// Apply middleware to block business plan requesters
definePageMeta({
  middleware: ['business-plan-guard']
})

// Meta tags
useHead({
  title: "Sign Up - NFCGo",
  meta: [
    {
      name: "description",
      content:
        "Create your NFCGo account and start building smart digital business cards.",
    },
  ],
});

// Stores
const authStore = useAuthStore();
const { $toast } = useNuxtApp();

// Route and router
const route = useRoute();
const router = useRouter();

// Reactive data
const loading = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const show2FASetup = ref(false);
const showManualSetup = ref(false);
const showSuccessModal = ref(false);
const showTermsModal = ref(false);
const showPrivacyModal = ref(false);
const loadingTerms = ref(false);
const loadingPrivacy = ref(false);
const termsDocument = ref(null);
const privacyDocument = ref(null);
const twoFactorCode = ref("");
const errors = ref({});

const form = reactive({
  first_name: "",
  last_name: "",
  email: "",
  password: "",
  password_confirmation: "",
  company: "",
  job_title: "",
  terms: false,
  marketing: false,
});

// Regex patterns (defined in script to avoid template parser issues with
// characters like " and < that break attribute binding)
const RE_UPPER = /[A-Z]/;
const RE_LOWER = /[a-z]/;
const RE_DIGIT = /\d/;
const RE_SYMBOL = /[!@#$%^&*(),.?"':{}|<>[\]\\\/+=`~_-]/;

// Individual requirement flags (used by template checklist UI)
const hasMinLength = computed(() => form.password.length >= 8);
const hasMixedCase = computed(() => RE_UPPER.test(form.password) && RE_LOWER.test(form.password));
const hasNumber    = computed(() => RE_DIGIT.test(form.password));
const hasSymbol    = computed(() => RE_SYMBOL.test(form.password));

// Password strength computation
const passwordStrength = computed(() => {
  const password = form.password;
  let strength = 0;

  if (password.length >= 8) strength++;
  if (RE_LOWER.test(password) && RE_UPPER.test(password)) strength++;
  if (RE_DIGIT.test(password)) strength++;
  if (RE_SYMBOL.test(password)) strength++;

  return strength;
});

// Strength tone used for the meter colours (weak | fair | good | strong)
const strengthTone = computed(() => {
  const tones = { 1: "weak", 2: "fair", 3: "good", 4: "strong" };
  return tones[passwordStrength.value] || "weak";
});

// Password confirmation match state
const passwordsMatch = computed(
  () =>
    form.password_confirmation.length > 0 &&
    form.password === form.password_confirmation
);
const passwordsMismatch = computed(
  () =>
    form.password_confirmation.length > 0 &&
    form.password !== form.password_confirmation
);

// Form validation
const isFormValid = computed(() => {
  return (
    form.first_name &&
    form.last_name &&
    form.email &&
    form.password &&
    form.password_confirmation &&
    form.password === form.password_confirmation &&
    form.terms
  );
});

// Password strength helper
const getPasswordStrengthText = (strength) => {
  const texts = {
    0: "Enter a password",
    1: "Weak password",
    2: "Fair password",
    3: "Good password",
    4: "Strong password",
  };
  return texts[strength] || "";
};

// Check if user is already authenticated
onMounted(() => {
  if (authStore.isAuthenticated) {
    // Check if user has completed onboarding by looking for subscription plan
    const user = authStore.user;
    if (user && user.subscription_plan) {
      // User has completed onboarding, redirect to CardManagement
      router.push("/UserDashboard/CardManagement");
    } else {
      // User hasn't completed onboarding, let them continue with onboarding flow
      // Don't redirect automatically
    }
  }
});

// Handle registration
const handleRegister = async () => {
  loading.value = true;
  errors.value = {};

  try {
    const response = await authStore.register(form);

    if (response.requires_2fa) {
      show2FASetup.value = true;
    } else {
      showSuccessModal.value = true;
    }
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.validationErrors;
    } else if (error.response?.status === 409) {
      $toast.error("An account with this email already exists");
    } else {
      $toast.error("An error occurred. Please try again.");
    }
  } finally {
    loading.value = false;
  }
};

// Handle 2FA setup
const handle2FASetup = async () => {
  loading.value = true;
  errors.value = {};

  try {
    await authStore.verify2FA(twoFactorCode.value);
    show2FASetup.value = false;
    showSuccessModal.value = true;
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.validationErrors;
    } else {
      $toast.error("Invalid verification code");
    }
  } finally {
    loading.value = false;
  }
};

// Skip 2FA setup
const skip2FASetup = () => {
  show2FASetup.value = false;
  showSuccessModal.value = true;
};

// Go to plan selection
const goToPlanSelection = () => {
  console.log("=== goToPlanSelection Debug Start ===");
  console.log("Auth store state:", {
    isAuthenticated: authStore.isAuthenticated,
    hasUser: !!authStore.user,
    userEmail: authStore.user?.email,
    token: !!authStore.token,
  });

  // Verify user is authenticated
  if (!authStore.isAuthenticated || !authStore.user) {
    console.error("User not authenticated");
    $toast.error("Please log in to continue.");
    window.location.href = "/UserAccount/login";
    return;
  }

  console.log("User authenticated successfully:", authStore.user.email);

  // Close the modal immediately
  showSuccessModal.value = false;

  // Use window.location for hard navigation to ensure clean page transition
  console.log("Navigating to /UserDashboard/PlanSelection...");
  window.location.href = "/UserDashboard/PlanSelection";
};

// Load legal documents
const loadLegalDocuments = async () => {
  try {
    const { $api } = useNuxtApp();

    // Load Terms of Service and Privacy Policy in parallel
    const [termsResponse, privacyResponse] = await Promise.all([
      $api.get("/legal/terms"),
      $api.get("/legal/privacy"),
    ]);

    if (termsResponse.success && termsResponse.data) {
      termsDocument.value = termsResponse.data;
    }

    if (privacyResponse.success && privacyResponse.data) {
      privacyDocument.value = privacyResponse.data;
    }
  } catch (error) {
    console.error("Error loading legal documents:", error);
  }
};

// Format date
const formatDate = (date) => {
  if (!date) return "";
  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

// Watch for modal open to load documents if not already loaded
watch(showTermsModal, (isOpen) => {
  if (isOpen && !termsDocument.value && !loadingTerms.value) {
    loadingTerms.value = true;
    loadLegalDocuments().finally(() => {
      loadingTerms.value = false;
    });
  }
});

watch(showPrivacyModal, (isOpen) => {
  if (isOpen && !privacyDocument.value && !loadingPrivacy.value) {
    loadingPrivacy.value = true;
    loadLegalDocuments().finally(() => {
      loadingPrivacy.value = false;
    });
  }
});

// Handle Google signup
const handleGoogleSignup = () => {
  try {
    const config = useRuntimeConfig();
    const apiBaseUrl = config.public.apiBaseUrl;
    const oauthUrl = `${apiBaseUrl}/auth/google/redirect`;

    console.log("🔵 Google Signup Clicked");
    console.log("📍 API Base URL:", apiBaseUrl);
    console.log("🔗 OAuth URL:", oauthUrl);
    console.log("🚀 Navigating now...");

    // Immediate navigation to backend OAuth endpoint
    // Backend will redirect to Google's sign-in page
    window.location.href = oauthUrl;
  } catch (error) {
    console.error("❌ Error in handleGoogleSignup:", error);
    alert("Error: " + error.message);
  }
};

// Handle Apple signup
const handleAppleSignup = () => {
  const config = useRuntimeConfig();
  const apiBaseUrl = config.public.apiBaseUrl;

  // Immediate navigation to backend OAuth endpoint
  // Backend will redirect to Apple's sign-in page
  window.location.href = `${apiBaseUrl}/auth/apple/redirect`;
};

// Redirect if already authenticated (but not after registration)
watch(
  () => authStore.isAuthenticated,
  (isAuth) => {
    // Only redirect to CardManagement if user is authenticated but not in the middle of registration
    // This prevents automatic redirection during the onboarding flow
    if (isAuth && !showSuccessModal.value && !show2FASetup.value) {
      // Check if user has completed onboarding by looking for subscription plan
      const user = authStore.user;
      if (user && user.subscription_plan) {
        // User has completed onboarding, redirect to CardManagement
        router.push("/UserDashboard/CardManagement");
      } else {
        // User hasn't completed onboarding, let them go through the flow
        // Don't redirect automatically
      }
    }
  }
);

// Clear password confirmation error when passwords match
watch(
  () => [form.password, form.password_confirmation],
  () => {
    if (
      errors.value.password_confirmation &&
      form.password === form.password_confirmation
    ) {
      delete errors.value.password_confirmation;
    }
  }
);

// Watch for password changes to update strength
watch(
  () => form.password,
  () => {
    // Clear password errors when user starts typing
    if (errors.value.password) {
      delete errors.value.password;
    }
  }
);

// Auto-focus first input on mount
onMounted(() => {
  const firstInput = document.getElementById("first_name");
  if (firstInput) {
    firstInput.focus();
  }
});
</script>

<style scoped src="../../assets/css/auth.css"></style>
