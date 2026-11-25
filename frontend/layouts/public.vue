<!-- layouts/public.vue -->
<!--
  Shared chrome for every public (marketing) page: sticky navbar, footer,
  global modals and the floating assistant. Public pages opt in with
  `definePageMeta({ layout: 'public' })`.
-->
<template>
  <div class="min-h-screen flex flex-col bg-[color:var(--paper)]">
    <!-- Navigation - STICKY HEADER (ink glass) -->
    <nav
      class="public-navbar fixed top-0 left-0 right-0 z-50 backdrop-blur-md border-b border-white/10"
    >
      <div class="container">
        <div
          class="flex h-16 items-center justify-between gap-4 md:grid md:grid-cols-[1fr_auto_1fr] md:gap-8"
        >
          <!-- Brand -->
          <NuxtLink
            to="/Homepage"
            class="flex shrink-0 items-center md:justify-self-start"
            aria-label="NFCGo home"
          >
            <NFCGoLogo variant="dark" />
          </NuxtLink>

          <!-- Primary navigation (desktop) -->
          <nav
            class="hidden items-center justify-center gap-x-8 md:flex lg:gap-x-10"
            aria-label="Primary"
          >
            <NuxtLink to="/Homepage/features" class="nav-ink-link"
              >Features</NuxtLink
            >
            <NuxtLink to="/Homepage/how-it-works" class="nav-ink-link"
              >How it Works</NuxtLink
            >
            <NuxtLink to="/Homepage/cards" class="nav-ink-link"
              >Cards</NuxtLink
            >
            <NuxtLink to="/Homepage/pricing" class="nav-ink-link"
              >Pricing</NuxtLink
            >
            <NuxtLink to="/Homepage/faq" class="nav-ink-link">FAQ</NuxtLink>
          </nav>

          <!-- Account actions (desktop) -->
          <div
            class="hidden items-center justify-end gap-3 md:flex md:justify-self-end"
          >
            <NuxtLink to="/UserAccount/login" class="nav-ink-ghost"
              >Login</NuxtLink
            >
            <NuxtLink to="/UserAccount/register" class="btn-brass"
              >Get Started</NuxtLink
            >
          </div>

          <!-- Mobile menu trigger -->
          <div class="flex justify-end md:hidden">
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="rounded-md p-2 text-white/75 transition-colors hover:text-white focus:outline-none focus:ring-2 focus:ring-white/40"
              :aria-expanded="mobileMenuOpen"
              aria-label="Toggle navigation menu"
            >
              <Icon name="heroicons:bars-3" class="h-6 w-6" />
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Menu -->
      <div
        v-if="mobileMenuOpen"
        class="public-mobile-menu md:hidden backdrop-blur-md border-t border-white/10"
      >
        <div class="px-4 pt-3 pb-4 space-y-1">
          <NuxtLink
            to="/Homepage/features"
            class="nav-m-ink-link"
            @click="mobileMenuOpen = false"
            >Features</NuxtLink
          >
          <NuxtLink
            to="/Homepage/how-it-works"
            class="nav-m-ink-link"
            @click="mobileMenuOpen = false"
            >How it Works</NuxtLink
          >
          <NuxtLink
            to="/Homepage/cards"
            class="nav-m-ink-link"
            @click="mobileMenuOpen = false"
            >Cards</NuxtLink
          >
          <NuxtLink
            to="/Homepage/pricing"
            class="nav-m-ink-link"
            @click="mobileMenuOpen = false"
            >Pricing</NuxtLink
          >
          <NuxtLink
            to="/Homepage/faq"
            class="nav-m-ink-link"
            @click="mobileMenuOpen = false"
            >FAQ</NuxtLink
          >
          <NuxtLink
            to="/UserAccount/login"
            class="nav-m-ink-link"
            @click="mobileMenuOpen = false"
            >Login</NuxtLink
          >
          <NuxtLink
            to="/UserAccount/register"
            class="block mt-2 px-4 py-2.5 rounded-lg btn-brass text-center"
            @click="mobileMenuOpen = false"
            >Get Started</NuxtLink
          >
        </div>
      </div>
    </nav>

    <!-- Business Plan Request Success Alert -->
    <Transition
      enter-active-class="transition-all duration-500 ease-out"
      enter-from-class="opacity-0 -translate-y-full"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition-all duration-300 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-full"
    >
      <div
        v-if="showBusinessRequestSuccess"
        class="fixed top-20 left-0 right-0 z-40 px-4"
      >
        <div class="max-w-3xl mx-auto">
          <div
            class="bg-success-50 border border-success-200 rounded-2xl shadow-lg p-6"
          >
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0">
                <div
                  class="w-12 h-12 bg-success-100 rounded-full flex items-center justify-center"
                >
                  <Icon
                    name="heroicons:check-circle"
                    class="h-7 w-7 text-success-600"
                  />
                </div>
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-success-800 mb-1">
                  Business Plan Request Submitted!
                </h3>
                <p class="text-success-700 mb-3">
                  Thank you for your interest in our Business plan. Our sales
                  team will review your request and contact you within 1-2
                  business days via email or phone.
                </p>
                <div class="flex items-center gap-2 text-sm text-success-600">
                  <Icon name="heroicons:clock" class="h-4 w-4" />
                  <span>Expected response time: 1-2 business days</span>
                </div>
              </div>
              <button
                @click="dismissBusinessRequestSuccess"
                class="flex-shrink-0 p-1 text-success-500 hover:text-success-700 hover:bg-success-100 rounded-lg transition-colors"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Business Plan Pending Alert (shown when blocked from login/register) -->
    <Transition
      enter-active-class="transition-all duration-500 ease-out"
      enter-from-class="opacity-0 -translate-y-full"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition-all duration-300 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-full"
    >
      <div
        v-if="showBusinessPlanPending"
        class="fixed top-20 left-0 right-0 z-40 px-4"
      >
        <div class="max-w-3xl mx-auto">
          <div
            class="bg-warning-50 border border-warning-200 rounded-2xl shadow-lg p-6"
          >
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0">
                <div
                  class="w-12 h-12 bg-warning-100 rounded-full flex items-center justify-center"
                >
                  <Icon name="heroicons:clock" class="h-7 w-7 text-warning-600" />
                </div>
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-warning-800 mb-1">
                  Business Plan - Account Pending
                </h3>
                <p class="text-warning-700 mb-3">
                  Your Business plan request has been submitted. Our team will
                  create your account and contact you within 24 hours.
                  <strong>You cannot login or register</strong> until your
                  account is created by our admin team.
                </p>
                <div
                  v-if="businessPlanEmail"
                  class="flex items-center gap-2 text-sm text-warning-600 mb-3"
                >
                  <Icon name="heroicons:envelope" class="h-4 w-4" />
                  <span>We'll contact you at: {{ businessPlanEmail }}</span>
                </div>
                <div class="flex items-center gap-4">
                  <button
                    @click="showBusinessPlanPending = false"
                    class="text-sm text-warning-600 hover:text-warning-800 font-medium"
                  >
                    Dismiss
                  </button>
                  <button
                    @click="clearBusinessPlanRestriction"
                    class="text-sm text-warning-700 hover:text-warning-900 underline"
                  >
                    Clear & Login Anyway
                  </button>
                </div>
              </div>
              <button
                @click="showBusinessPlanPending = false"
                class="flex-shrink-0 p-1 text-warning-500 hover:text-warning-700 hover:bg-warning-100 rounded-lg transition-colors"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Page content (flex-1 keeps the footer pinned to the bottom on short pages) -->
    <main class="public-main flex-1">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="footer-ink">
      <div class="container">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div>
            <div class="flex items-center mb-4">
              <NFCGoLogo variant="dark" />
            </div>
            <p class="text-white/55 mb-4">
              The future of professional networking is here. Connect smarter
              with NFC technology.
            </p>
            <div class="flex space-x-4">
              <a
                href="https://clbgroups.com/"
                target="_blank"
                rel="noopener noreferrer"
                class="text-white/50 hover:text-[color:var(--brass)] transition-colors"
              >
                <Icon name="heroicons:globe-alt" class="h-5 w-5" />
              </a>
              <a
                href="https://www.facebook.com/profile.php?id=61568045614509"
                target="_blank"
                rel="noopener noreferrer"
                class="text-white/50 hover:text-[color:var(--brass)] transition-colors"
              >
                <Icon name="bi:facebook" class="h-5 w-5" />
              </a>
            </div>
          </div>
          <div>
            <h4 class="footer-ink__head">Product</h4>
            <ul class="space-y-2">
              <li>
                <NuxtLink to="/Homepage/features" class="footer-ink__link"
                  >Features</NuxtLink
                >
              </li>
              <li>
                <NuxtLink to="/Homepage/cards" class="footer-ink__link"
                  >Cards</NuxtLink
                >
              </li>
              <li>
                <NuxtLink to="/Homepage/pricing" class="footer-ink__link"
                  >Pricing</NuxtLink
                >
              </li>
            </ul>
          </div>
          <div>
            <h4 class="footer-ink__head">Support</h4>
            <ul class="space-y-2">
              <li>
                <NuxtLink to="/Homepage/faq" class="footer-ink__link"
                  >FAQ</NuxtLink
                >
              </li>
              <li>
                <NuxtLink to="/Homepage#shipping" class="footer-ink__link"
                  >Shipping</NuxtLink
                >
              </li>
              <li>
                <button @click="openContactModal" class="footer-ink__link">
                  Contact Us
                </button>
              </li>
            </ul>
          </div>
          <div>
            <h4 class="footer-ink__head">Company</h4>
            <ul class="space-y-2">
              <li>
                <a
                  href="https://clbgroups.com/"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="footer-ink__link"
                  >About</a
                >
              </li>
              <li>
                <button
                  @click="openLegalModal('privacy')"
                  class="footer-ink__link"
                >
                  Privacy
                </button>
              </li>
              <li>
                <button
                  @click="openLegalModal('terms')"
                  class="footer-ink__link"
                >
                  Terms
                </button>
              </li>
            </ul>
          </div>
        </div>
        <div class="footer-ink__bottom">
          <p>
            @Copyright CLB SDN BHD – 2025 -RFID Label Supplier , RFID Solution
            and Stick Printing I All Rights Reserved
          </p>
        </div>
      </div>
    </footer>

    <!-- Contact Us Modal -->
    <div
      v-if="showContactModal"
      class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
      @click.self="closeContactModal"
    >
      <div
        class="contact-modal-content bg-white rounded-2xl shadow-2xl w-full max-w-[700px] max-h-[90vh] overflow-hidden animate-modal-appear"
      >
        <div
          class="flex items-center justify-between p-6 border-b border-secondary-200 bg-gradient-to-r from-primary-50 to-secondary-50"
        >
          <div>
            <h2 class="text-2xl font-bold text-secondary-900">Contact Us</h2>
            <p class="text-sm text-secondary-600 mt-1">
              We'd love to hear from you!
            </p>
          </div>
          <button
            @click="closeContactModal"
            class="p-2 rounded-full hover:bg-white/80 transition-colors"
            aria-label="Close modal"
          >
            <Icon name="heroicons:x-mark" class="h-6 w-6 text-secondary-600" />
          </button>
        </div>

        <div
          class="overflow-y-auto"
          style="height: calc(90vh - 100px); max-height: 800px"
        >
          <iframe
            src="https://docs.google.com/forms/d/e/1FAIpQLScGpcS-JYMWqHLMYsNHnzowmC09EvFQ97oYTGNue7H-IRuAZA/viewform?embedded=true"
            width="100%"
            height="800"
            frameborder="0"
            marginheight="0"
            marginwidth="0"
            class="w-full"
          >
            Loading…
          </iframe>
        </div>
      </div>
    </div>

    <!-- Business Plan Request Modal -->
    <div
      v-if="showBusinessModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
      @click="closeBusinessModal"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
        @click.stop
      >
        <div
          class="bg-gradient-to-r from-[#5d8c87] to-primary-700 p-6 rounded-t-2xl"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="p-3 bg-white/20 rounded-xl mr-4">
                <Icon
                  name="heroicons:building-office"
                  class="h-8 w-8 text-white"
                />
              </div>
              <div>
                <h3 class="text-2xl font-bold text-white">
                  Business Plan Request
                </h3>
                <p class="text-primary-100 text-sm mt-1">
                  Tell us about your organization
                </p>
              </div>
            </div>
            <button
              @click="closeBusinessModal"
              class="p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-colors"
            >
              <Icon name="heroicons:x-mark" class="h-6 w-6" />
            </button>
          </div>
        </div>

        <form @submit.prevent="submitBusinessRequest" class="p-6 space-y-6">
          <div>
            <label class="block text-sm font-semibold text-secondary-900 mb-2">
              Company Name <span class="text-red-500">*</span>
            </label>
            <input
              v-model="businessForm.company_name"
              type="text"
              required
              placeholder="Enter your company name"
              class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-secondary-900 mb-2">
              Company Address <span class="text-red-500">*</span>
            </label>
            <textarea
              v-model="businessForm.company_address"
              required
              rows="3"
              placeholder="Enter your company address"
              class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-semibold text-secondary-900 mb-2">
              Number of NFC Cards Needed <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="businessForm.quota"
              type="number"
              required
              min="1"
              placeholder="e.g., 50"
              class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
            />
            <p class="text-xs text-secondary-500 mt-2">
              How many NFC cards do you need for your team?
            </p>
          </div>

          <div>
            <label class="block text-sm font-semibold text-secondary-900 mb-2">
              Contact Person <span class="text-red-500">*</span>
            </label>
            <input
              v-model="businessForm.contact_person"
              type="text"
              required
              placeholder="Full name"
              class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-secondary-900 mb-2">
              Phone Number <span class="text-red-500">*</span>
            </label>
            <input
              v-model="businessForm.phone"
              type="tel"
              required
              placeholder="+60 12-345 6789"
              class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-secondary-900 mb-2">
              Email Address <span class="text-red-500">*</span>
            </label>
            <input
              v-model="businessForm.email"
              type="email"
              required
              placeholder="your@company.com"
              class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-secondary-900 mb-2">
              Additional Notes (Optional)
            </label>
            <textarea
              v-model="businessForm.notes"
              rows="3"
              placeholder="Any special requirements or questions?"
              class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all"
            ></textarea>
          </div>

          <div class="flex space-x-4 pt-4">
            <button
              type="button"
              @click="closeBusinessModal"
              class="flex-1 py-3 px-4 bg-secondary-100 text-secondary-700 rounded-xl font-medium hover:bg-secondary-200 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submittingBusinessRequest"
              class="flex-1 py-3 px-4 bg-gradient-to-r from-[#5d8c87] to-primary-700 text-white rounded-xl font-medium hover:opacity-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
            >
              <div v-if="submittingBusinessRequest" class="flex items-center">
                <div
                  class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"
                ></div>
                Submitting...
              </div>
              <span v-else>Submit Request</span>
            </button>
          </div>

          <p class="text-xs text-secondary-500 text-center">
            Our team will contact you within 24 hours to discuss your Business
            Plan setup.
          </p>
        </form>
      </div>
    </div>

    <!-- Legal Document Modal (Privacy Policy / Terms of Service) -->
    <div
      v-if="showLegalModal"
      class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
      @click.self="closeLegalModal"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col animate-modal-appear"
      >
        <div
          class="flex items-center justify-between p-4 border-b border-secondary-200 bg-gradient-to-r from-primary-50 to-secondary-50"
        >
          <div class="flex-1 min-w-0">
            <h2 class="text-xl font-bold text-secondary-900 truncate">
              {{
                legalModalType === 'privacy'
                  ? 'Privacy Policy'
                  : 'Terms of Service'
              }}
            </h2>
            <div
              v-if="legalDocInfo && !loadingLegalDoc"
              class="flex items-center gap-3 mt-1 text-sm text-secondary-600"
            >
              <span
                class="truncate max-w-[200px]"
                :title="legalDocInfo.filename"
              >
                <Icon name="heroicons:document" class="h-4 w-4 inline mr-1" />
                {{ legalDocInfo.filename }}
              </span>
              <span class="text-secondary-400">•</span>
              <span>{{ formatFileSize(legalDocInfo.size) }}</span>
            </div>
          </div>
          <button
            @click="closeLegalModal"
            class="p-2 rounded-full hover:bg-white/80 transition-colors ml-4"
            aria-label="Close modal"
          >
            <Icon name="heroicons:x-mark" class="h-6 w-6 text-secondary-600" />
          </button>
        </div>

        <div class="flex-1 overflow-hidden relative">
          <div
            v-if="loadingLegalDoc"
            class="absolute inset-0 flex items-center justify-center bg-secondary-50"
          >
            <div class="text-center">
              <div
                class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto mb-4"
              ></div>
              <p class="text-secondary-600">Loading document...</p>
            </div>
          </div>

          <div
            v-else-if="legalDocError"
            class="absolute inset-0 flex items-center justify-center bg-secondary-50 p-8"
          >
            <div class="text-center">
              <div
                class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4"
              >
                <Icon
                  name="heroicons:exclamation-triangle"
                  class="h-8 w-8 text-red-600"
                />
              </div>
              <h3 class="text-lg font-semibold text-secondary-900 mb-2">
                Document Not Available
              </h3>
              <p class="text-secondary-600 mb-4">{{ legalDocError }}</p>
              <button
                @click="closeLegalModal"
                class="px-4 py-2 bg-secondary-100 text-secondary-700 rounded-lg hover:bg-secondary-200 transition-colors"
              >
                Close
              </button>
            </div>
          </div>

          <iframe
            v-else-if="legalDocInfo"
            :src="legalPdfUrl"
            class="w-full h-full border-0"
            style="min-height: 70vh"
            title="Legal Document"
          ></iframe>
        </div>
      </div>
    </div>

    <!-- Floating AI Chatbot Widget (bottom-right) -->
    <ChatbotWidget />
  </div>
</template>

<script setup>
const {
  showContactModal,
  closeContactModal,
  openContactModal,
  showBusinessModal,
  showBusinessRequestSuccess,
  showBusinessPlanPending,
  businessPlanEmail,
  submittingBusinessRequest,
  businessForm,
  closeBusinessModal,
  submitBusinessRequest,
  dismissBusinessRequestSuccess,
  clearBusinessPlanRestriction,
  showLegalModal,
  legalModalType,
  loadingLegalDoc,
  legalDocInfo,
  legalDocError,
  legalPdfUrl,
  openLegalModal,
  closeLegalModal,
  formatFileSize,
} = usePublicUi();

const route = useRoute();
const mobileMenuOpen = ref(false);

const handleClickOutside = (e) => {
  if (!e.target.closest("nav")) {
    mobileMenuOpen.value = false;
  }
};

const handleEscKey = (event) => {
  if (event.key !== "Escape") return;
  if (showContactModal.value) closeContactModal();
  if (showLegalModal.value) closeLegalModal();
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
  document.addEventListener("keydown", handleEscKey);

  // Landing here straight after a business-plan enquiry: show the receipt.
  if (route.query.businessRequest === 'success') {
    showBusinessRequestSuccess.value = true;
    setTimeout(() => {
      dismissBusinessRequestSuccess();
    }, 15000);
  }

  // Business-plan users are blocked from login/register until an admin creates
  // their account — surface why they were bounced back here.
  if (route.query.businessPlanPending === 'true') {
    showBusinessPlanPending.value = true;
    businessPlanEmail.value = localStorage.getItem('businessPlanEmail') || '';
  } else if (localStorage.getItem('businessPlanRequested') === 'true') {
    showBusinessPlanPending.value = true;
    businessPlanEmail.value = localStorage.getItem('businessPlanEmail') || '';
  }
});

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
  document.removeEventListener("keydown", handleEscKey);
});
</script>

<style scoped>
.public-navbar,
.public-mobile-menu {
  background-color: color-mix(in srgb, var(--ink-950, #06090d) 95%, transparent);
}

/*
  Page-level loaders inside this layout (NFCGoWaveLoader `.nfg-viewport`)
  centre on the VISIBLE content area: subtract the fixed navbar (h-16 = 4rem).
*/
.public-main {
  --nfg-viewport-offset: 4rem;
}

/* Contact / legal modals reuse the modal entrance animation that used to live
   in the page-level <style scoped> block. */
@keyframes modalAppear {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(-20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.animate-modal-appear {
  animation: modalAppear 0.3s ease-out;
}

.contact-modal-content {
  transition: all 0.3s ease-out;
}
</style>
