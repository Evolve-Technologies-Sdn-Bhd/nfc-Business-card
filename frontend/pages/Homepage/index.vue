<!-- pages/index.vue -->
<template>
  <div class="min-h-screen bg-[color:var(--paper)]">
    <!-- Navigation - STICKY HEADER (ink glass) -->
    <nav
      class="fixed top-0 left-0 right-0 z-50 bg-[color:var(--ink-950)]/85 backdrop-blur-xl border-b border-white/10"
    >
      <div class="container">
        <div
          class="flex h-16 items-center justify-between gap-4 md:grid md:grid-cols-[1fr_auto_1fr] md:gap-8"
        >
          <!-- Brand -->
          <NuxtLink
            to="/"
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
            <a
              href="#features"
              @click="smoothScroll('#features')"
              class="nav-ink-link"
              >Features</a
            >
            <a
              href="#how-it-works"
              @click="smoothScroll('#how-it-works')"
              class="nav-ink-link"
              >How it Works</a
            >
            <a
              href="#product-gallery"
              @click="smoothScroll('#product-gallery')"
              class="nav-ink-link"
              >Cards</a
            >
            <a
              href="#pricing"
              @click="smoothScroll('#pricing')"
              class="nav-ink-link"
              >Pricing</a
            >
            <a
              href="#faq"
              @click="smoothScroll('#faq')"
              class="nav-ink-link"
              >FAQ</a
            >
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
        class="md:hidden bg-[color:var(--ink-950)]/95 backdrop-blur-md border-t border-white/10"
      >
        <div class="px-4 pt-3 pb-4 space-y-1">
          <a
            href="#features"
            @click="smoothScroll('#features')"
            class="nav-m-ink-link"
            >Features</a
          >
          <a
            href="#how-it-works"
            @click="smoothScroll('#how-it-works')"
            class="nav-m-ink-link"
            >How it Works</a
          >
          <a
            href="#product-gallery"
            @click="smoothScroll('#product-gallery')"
            class="nav-m-ink-link"
            >Cards</a
          >
          <a
            href="#pricing"
            @click="smoothScroll('#pricing')"
            class="nav-m-ink-link"
            >Pricing</a
          >
          <a
            href="#faq"
            @click="smoothScroll('#faq')"
            class="nav-m-ink-link"
            >FAQ</a
          >
          <NuxtLink to="/UserAccount/login" class="nav-m-ink-link"
            >Login</NuxtLink
          >
          <NuxtLink
            to="/UserAccount/register"
            class="block mt-2 px-4 py-2.5 rounded-lg btn-brass text-center"
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
          <div class="bg-success-50 border border-success-200 rounded-2xl shadow-lg p-6">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-success-100 rounded-full flex items-center justify-center">
                  <Icon name="heroicons:check-circle" class="h-7 w-7 text-success-600" />
                </div>
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-success-800 mb-1">
                  Business Plan Request Submitted!
                </h3>
                <p class="text-success-700 mb-3">
                  Thank you for your interest in our Business plan. Our sales team will review your request and contact you within 1-2 business days via email or phone.
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
          <div class="bg-warning-50 border border-warning-200 rounded-2xl shadow-lg p-6">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-warning-100 rounded-full flex items-center justify-center">
                  <Icon name="heroicons:clock" class="h-7 w-7 text-warning-600" />
                </div>
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-warning-800 mb-1">
                  Business Plan - Account Pending
                </h3>
                <p class="text-warning-700 mb-3">
                  Your Business plan request has been submitted. Our team will create your account and contact you within 24 hours.
                  <strong>You cannot login or register</strong> until your account is created by our admin team.
                </p>
                <div v-if="businessPlanEmail" class="flex items-center gap-2 text-sm text-warning-600 mb-3">
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

    <!-- Hero Section (ink, product-led) -->
    <section class="relative overflow-hidden ink-surface ink-grid pt-32 pb-20 lg:pt-40 lg:pb-28">
      <div class="container relative">
        <div class="lg:grid lg:grid-cols-[1.05fr_0.95fr] lg:gap-16 items-center">
          <div class="mb-16 lg:mb-0">
            <span class="eyebrow-brass animate-fade-in-up">
              <span class="dot"></span>
              Smart NFC Business Card
            </span>
            <h1 class="showcase-display mt-7 mb-6 text-white animate-fade-in-up">
              Your digital business card,
              <span class="brass-grad">one tap away</span>
            </h1>
            <p class="lead-ink max-w-xl mb-9 text-lg animate-fade-in-up">
              Create a smart NFC business card that instantly shares your
              professional profile. Tap the card and your contact, portfolio and
              social links open right on their phone — no app required.
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
              <NuxtLink to="/UserAccount/register" class="btn-brass btn-lg">
                <Icon name="heroicons:rocket-launch" class="h-5 w-5 mr-2" />
                Start Free Trial
              </NuxtLink>
              <button
                @click="smoothScroll('#how-it-works')"
                class="btn-ghost-light btn-lg"
              >
                <Icon name="heroicons:play" class="h-5 w-5 mr-2" />
                See How It Works
              </button>
            </div>
            <div class="mt-9 flex flex-wrap items-center gap-x-6 gap-y-3">
              <div class="lp-tick">
                <Icon
                  name="heroicons:check-circle"
                  class="h-5 w-5 text-[color:var(--brass)]"
                />
                <span class="text-sm text-white/70">No app required</span>
              </div>
              <div class="lp-tick">
                <Icon
                  name="heroicons:check-circle"
                  class="h-5 w-5 text-[color:var(--brass)]"
                />
                <span class="text-sm text-white/70">Works on all phones</span>
              </div>
              <div class="lp-tick">
                <Icon
                  name="heroicons:check-circle"
                  class="h-5 w-5 text-[color:var(--brass)]"
                />
                <span class="text-sm text-white/70">Instant setup</span>
              </div>
            </div>
          </div>

          <!-- Hero product: CSS 3D NFC card -->
          <div class="hero-stage">
            <div class="hero-halo"></div>

            <div class="hero-chip hero-chip-1 chip-ink">
              <Icon name="heroicons:bolt" class="h-4 w-4 text-[color:var(--brass)]" />
              Opens in 1 tap
            </div>
            <div class="hero-chip hero-chip-2 chip-ink">
              <Icon
                name="heroicons:device-phone-mobile"
                class="h-4 w-4 text-[color:var(--brass)]"
              />
              Works on iOS &amp; Android
            </div>
            <div class="hero-chip hero-chip-3 chip-ink">
              <Icon
                name="heroicons:user-plus"
                class="h-4 w-4 text-[color:var(--brass)]"
              />
              Saved to Contacts
            </div>

            <span class="tap-ripple hero-ripple"></span>

            <div class="hero-card-wrap">
              <div
                class="nfc-mock hero-card"
                :data-flipped="heroFlipped ? 'true' : 'false'"
              >
                <!-- Front -->
                <div class="nfc-mock__face nfc-face-ink">
                  <div class="nfc-mock__sheen"></div>
                  <div class="relative z-10 flex items-start justify-between">
                    <div class="flex flex-col">
                      <span
                        class="text-2xl font-black tracking-tighter brass-grad leading-none"
                        >NFCGo</span
                      >
                      <span
                        class="mt-[0.3rem] text-[length:var(--text-micro)] font-bold tracking-[0.2em] text-[color:var(--brass-200)]"
                        >SMART DIGITAL IDENTITY</span
                      >
                    </div>
                    <Icon name="heroicons:wifi" class="h-7 w-7 text-white/60" />
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
                <!-- Back -->
                <div class="nfc-mock__face nfc-mock__face--back nfc-face-ink">
                  <div class="nfc-mock__sheen"></div>
                  <div class="relative z-10 flex items-center justify-between">
                    <span
                      class="text-xs font-bold tracking-[0.2em] uppercase text-[color:var(--brass-200)]"
                      >Tap to connect</span
                    >
                    <span class="nfc-mock__kwave">
                      <Icon name="heroicons:wifi" class="h-6 w-6" />
                    </span>
                  </div>
                  <div class="relative z-10 flex items-center gap-4">
                    <div class="nfc-mock__qr">
                      <Icon name="heroicons:qr-code" class="h-10 w-10" />
                    </div>
                    <p class="text-white/65 text-xs leading-relaxed">
                      Scan or tap with any<br />NFC-enabled smartphone
                    </p>
                  </div>
                  <span
                    class="relative z-10 text-[0.6rem] tracking-[0.18em] uppercase text-white/60"
                    >nfcgo · smart digital identity</span
                  >
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Hero stat strip (glass on ink) -->
        <div
          class="mt-16 lg:mt-24 grid grid-cols-2 lg:grid-cols-4 overflow-hidden rounded-2xl border border-white/12 bg-white/5 backdrop-blur-sm"
        >
          <div class="hero-stat p-6 text-center border-b border-r border-white/10 lg:border-b-0">
            <div class="hero-stat__num">1</div>
            <div class="hero-stat__lbl">Tap to share</div>
          </div>
          <div class="hero-stat p-6 text-center border-b border-white/10 lg:border-b-0 lg:border-r">
            <div class="hero-stat__num">&lt;3s</div>
            <div class="hero-stat__lbl">To connect</div>
          </div>
          <div class="hero-stat p-6 text-center border-r border-white/10">
            <div class="hero-stat__num">0</div>
            <div class="hero-stat__lbl">Apps to install</div>
          </div>
          <div class="hero-stat p-6 text-center">
            <div class="hero-stat__num">100%</div>
            <div class="hero-stat__lbl">Paperless</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Trust marquee band -->
    <section
      class="ink-surface-2 lp-marquee-mask overflow-hidden border-y border-white/10 py-5"
    >
      <div class="lp-marquee">
        <div v-for="n in 2" :key="`mq-row-${n}`" class="flex items-center">
          <span
            v-for="label in marqueeLabels"
            :key="`mq-${n}-${label}`"
            class="marquee-item-ink"
            >{{ label }}</span
          >
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="section bg-[color:var(--paper)]">
      <div class="container">
        <div class="max-w-2xl mb-14" data-reveal>
          <span class="lp-kicker">Capabilities</span>
          <h2 class="lp-h2 mt-4 mb-4">
            Everything you need for modern networking
          </h2>
          <p class="lp-lead">
            Powerful features designed to make your professional networking
            effortless and memorable.
          </p>
        </div>

        <!-- Auto-sliding feature row (seamless loop) -->
        <div class="overflow-hidden">
          <div
            class="flex transition-transform duration-700 ease-in-out"
            :class="{ 'duration-0': !featureTopIsTransitioning }"
            :style="{ transform: `translateX(-${featureTopCurrentSlide * (featureMobileMode ? 100 : 100/3)}%)` }"
          >
            <div
              v-for="(feature, index) in featuresRow1"
              :key="`feature-${index}`"
              class="flex-shrink-0 w-full md:w-1/3 px-4"
            >
              <div class="feature-card group">
                <div class="feature-card__ico">
                  <Icon :name="feature.icon" class="h-6 w-6" />
                </div>
                <h3 class="feature-card__title">{{ feature.title }}</h3>
                <p class="feature-card__desc">{{ feature.description }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works Section -->
    <section
      id="how-it-works"
      class="section ink-surface ink-grid relative overflow-hidden"
    >
      <div class="container relative">
        <div class="max-w-2xl mb-14" data-reveal>
          <span class="lp-kicker on-ink">Simple Process</span>
          <h2 class="lp-h2 on-ink mt-4 mb-4">How it works</h2>
          <p class="lp-lead lead-ink">
            Get started in minutes with our simple 4-step process.
          </p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
          <div
            v-for="(step, index) in steps"
            :key="step.title"
            class="step-ink"
            data-reveal
          >
            <div class="step-ink__top">
              <span class="step-ink__num">{{
                String(index + 1).padStart(2, '0')
              }}</span>
              <span class="step-ink__ico">
                <Icon :name="step.icon" class="h-6 w-6" />
              </span>
            </div>
            <h3 class="step-ink__title">{{ step.title }}</h3>
            <p class="step-ink__desc">{{ step.desc }}</p>
          </div>
        </div>
        <div class="mt-14" data-reveal>
          <a
            href="https://youtu.be/PER3f8EIGjs"
            target="_blank"
            rel="noopener noreferrer"
            class="btn-ghost-light btn-lg"
          >
            <Icon name="heroicons:play-circle" class="h-5 w-5 mr-2" />
            Watch the 60-second demo
          </a>
        </div>
      </div>
    </section>

    <!-- Digital Identity Section -->
    <section id="digital-identity" class="section bg-[color:var(--paper)]">
      <div class="container">
        <div class="lg:grid lg:grid-cols-2 lg:gap-16 items-center">
          <!-- Copy -->
          <div data-reveal>
            <span class="lp-kicker">Your Digital Identity</span>
            <h2 class="lp-h2 mt-4 mb-4">
              More than a card — it's your whole profile
            </h2>
            <p class="lp-lead mb-8">
              Every NFCGo card links to a live digital business card. Your photo,
              role, contact details, social links and portfolio live in one clean
              page that updates instantly — no reprinting, ever.
            </p>
            <ul class="space-y-5 mb-10">
              <li class="flex items-start gap-3">
                <span class="lp-prof-bullet">
                  <Icon name="heroicons:user-circle" class="h-4 w-4" />
                </span>
                <div>
                  <p class="font-semibold text-secondary-900">
                    Photo, name and role
                  </p>
                  <p class="text-sm text-secondary-600">
                    Present yourself exactly how you want to be seen.
                  </p>
                </div>
              </li>
              <li class="flex items-start gap-3">
                <span class="lp-prof-bullet">
                  <Icon name="heroicons:link" class="h-4 w-4" />
                </span>
                <div>
                  <p class="font-semibold text-secondary-900">
                    All your links in one place
                  </p>
                  <p class="text-sm text-secondary-600">
                    Website, social profiles, portfolio and booking links.
                  </p>
                </div>
              </li>
              <li class="flex items-start gap-3">
                <span class="lp-prof-bullet">
                  <Icon name="heroicons:arrow-path" class="h-4 w-4" />
                </span>
                <div>
                  <p class="font-semibold text-secondary-900">
                    Update anytime, instantly
                  </p>
                  <p class="text-sm text-secondary-600">
                    Change your details and every tap shows the latest version.
                  </p>
                </div>
              </li>
            </ul>
            <div class="flex flex-col sm:flex-row gap-4">
              <NuxtLink to="/UserAccount/register" class="btn-brass btn-lg">
                <Icon name="heroicons:rocket-launch" class="h-5 w-5 mr-2" />
                Create your profile
              </NuxtLink>
              <button
                @click="smoothScroll('#product-gallery')"
                class="btn-outline-ink btn-lg"
              >
                Explore card designs
              </button>
            </div>
          </div>

          <!-- Phone mockup (design demonstration) -->
          <div class="relative mt-14 lg:mt-0" data-reveal>
            <div class="lp-glow"></div>
            <div class="lp-phone mx-auto">
              <div class="lp-phone-notch"></div>
              <div class="lp-phone-screen">
                <div class="lp-prof-hero">
                  <div class="lp-prof-avatar">YN</div>
                  <div class="lp-prof-name">Your Name</div>
                  <div class="lp-prof-role">Product Designer · NFCGo</div>
                  <div class="lp-prof-socials">
                    <span class="lp-prof-social">
                      <Icon name="heroicons:globe-alt" class="h-4 w-4" />
                    </span>
                    <span class="lp-prof-social">
                      <Icon name="heroicons:user-group" class="h-4 w-4" />
                    </span>
                    <span class="lp-prof-social">
                      <Icon name="heroicons:envelope" class="h-4 w-4" />
                    </span>
                  </div>
                  <div class="lp-prof-actions">
                    <span class="lp-prof-action lp-prof-action-primary">
                      <Icon name="heroicons:user-plus" class="h-4 w-4" />
                      Save Contact
                    </span>
                    <span class="lp-prof-action">
                      <Icon
                        name="heroicons:chat-bubble-left-right"
                        class="h-4 w-4"
                      />
                      WhatsApp
                    </span>
                  </div>
                </div>
                <div class="lp-prof-links">
                  <div
                    v-for="l in profileLinks"
                    :key="l.label"
                    class="lp-prof-link"
                  >
                    <span class="lp-prof-link-ico">
                      <Icon :name="l.icon" class="h-4 w-4" />
                    </span>
                    <span class="lp-prof-link-label">{{ l.label }}</span>
                    <Icon
                      name="heroicons:chevron-right"
                      class="h-4 w-4 text-secondary-400"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Who Uses Section -->
    <section id="who-uses" class="section bg-white">
      <div class="container">
        <div class="max-w-2xl mb-14" data-reveal>
          <span class="lp-kicker">Built For Everyone</span>
          <h2 class="lp-h2 mt-4 mb-4">Who uses NFC business cards?</h2>
          <p class="lp-lead">
            From entrepreneurs to business teams, discover how professionals
            across industries are revolutionizing their networking.
          </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            class="use-card group"
            data-reveal
          >
            <div
              class="w-16 h-16 bg-gradient-to-br from-primary-100 to-primary-200 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300"
            >
              <Icon name="heroicons:briefcase" class="h-8 w-8 text-primary-600" />
            </div>
            <h3 class="text-xl font-semibold text-secondary-900 mb-3">
              Salespeople
            </h3>
            <p class="text-secondary-600 leading-relaxed mb-4">
              Close deals faster by instantly sharing product demos, calendars,
              and contact info. Track engagement and follow-ups effortlessly.
            </p>
            <ul class="space-y-2 text-sm">
              <li class="flex items-center text-secondary-600">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2"
                />
                Instant lead capture
              </li>
              <li class="flex items-center text-secondary-600">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2"
                />
                Meeting scheduler integration
              </li>
            </ul>
          </div>

          <div
            class="use-card group"
            data-reveal
          >
            <div
              class="w-16 h-16 bg-gradient-to-br from-[#e4efed] to-[#cfe2df] rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300"
            >
              <Icon
                name="heroicons:rocket-launch"
                class="h-8 w-8 text-[#4b7a75]"
              />
            </div>
            <h3 class="text-xl font-semibold text-secondary-900 mb-3">
              Entrepreneurs & Startups
            </h3>
            <p class="text-secondary-600 leading-relaxed mb-4">
              Make memorable first impressions at pitch events and conferences.
              Share your startup story and portfolio instantly.
            </p>
            <ul class="space-y-2 text-sm">
              <li class="flex items-center text-secondary-600">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2"
                />
                Pitch deck sharing
              </li>
              <li class="flex items-center text-secondary-600">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2"
                />
                Investor contact forms
              </li>
            </ul>
          </div>

          <div
            class="use-card group"
            data-reveal
          >
            <div
              class="w-16 h-16 bg-gradient-to-br from-success-100 to-success-200 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300"
            >
              <Icon
                name="heroicons:building-office"
                class="h-8 w-8 text-success-600"
              />
            </div>
            <h3 class="text-xl font-semibold text-secondary-900 mb-3">
              Corporate Teams
            </h3>
            <p class="text-secondary-600 leading-relaxed mb-4">
              Maintain brand consistency across your organization. Manage team
              profiles centrally with advanced permissions.
            </p>
            <ul class="space-y-2 text-sm">
              <li class="flex items-center text-secondary-600">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2"
                />
                Brand compliance
              </li>
                <li class="flex items-center text-secondary-600">
                  <Icon
                    name="heroicons:check"
                    class="h-4 w-4 text-success-600 mr-2"
                  />
                  Team analytics
                </li>
                <li class="flex items-center text-secondary-600">
                  <Icon
                    name="heroicons:check"
                    class="h-4 w-4 text-success-600 mr-2"
                  />
                  Team User Dashboard
                </li>
            </ul>
          </div>

          <div
            class="use-card group"
            data-reveal
          >
            <div
              class="w-16 h-16 bg-gradient-to-br from-[#f3ebdd] to-[#e6d6bd] rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300"
            >
              <Icon
                name="heroicons:paint-brush"
                class="h-8 w-8 text-[#a17e4f]"
              />
            </div>
            <h3 class="text-xl font-semibold text-secondary-900 mb-3">
              Creatives & Freelancers
            </h3>
            <p class="text-secondary-600 leading-relaxed mb-4">
              Showcase portfolios, testimonials, and project galleries. Convert
              casual meetings into paying clients.
            </p>
            <ul class="space-y-2 text-sm">
              <li class="flex items-center text-secondary-600">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2"
                />
                Portfolio showcase
              </li>
              <li class="flex items-center text-secondary-600">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2"
                />
                Service booking forms
              </li>
            </ul>
          </div>

          <div
            class="use-card group"
            data-reveal
          >
            <div
              class="w-16 h-16 bg-gradient-to-br from-primary-100 to-primary-200 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300"
            >
              <Icon name="heroicons:users" class="h-8 w-8 text-primary-600" />
            </div>
            <h3 class="text-xl font-semibold text-secondary-900 mb-3">
              Event Networkers
            </h3>
            <p class="text-secondary-600 leading-relaxed mb-4">
              Stand out at conferences and trade shows. Collect leads
              efficiently and track event ROI with detailed analytics.
            </p>
            <ul class="space-y-2 text-sm">
              <li class="flex items-center text-secondary-600">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2"
                />
                Event-specific profiles
              </li>
              <li class="flex items-center text-secondary-600">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2"
                />
                Lead scoring system
              </li>
            </ul>
          </div>

          <!-- Real Estate Agents -->
          <div
            class="use-card group"
            data-reveal
          >
            <div class="w-16 h-16 bg-gradient-to-br from-[#e4efed] to-[#cfe2df] rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
              <Icon name="heroicons:home-modern" class="h-8 w-8 text-[#4b7a75]" />
            </div>
            <h3 class="text-xl font-semibold text-secondary-900 mb-3">
              Real Estate Agents
            </h3>
            <p class="text-secondary-600 leading-relaxed mb-4">
              Close properties faster with instant access to listings, virtual tours, and mortgage calculators. Schedule showings on the spot.
            </p>
            <ul class="space-y-2 text-sm">
              <li class="flex items-center text-secondary-600">
                <Icon name="heroicons:check" class="h-4 w-4 text-success-600 mr-2" />
                Property listing links
              </li>
              <li class="flex items-center text-secondary-600">
                <Icon name="heroicons:check" class="h-4 w-4 text-success-600 mr-2" />
                Virtual tour integration
              </li>
              <li class="flex items-center text-secondary-600">
                <Icon name="heroicons:check" class="h-4 w-4 text-success-600 mr-2" />
                Instant showing scheduler
              </li>
            </ul>
          </div>

          <!-- Consultants & Coaches -->
          <div
            class="use-card group"
            data-reveal
          >
            <div class="w-16 h-16 bg-gradient-to-br from-[#f3ebdd] to-[#e6d6bd] rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
              <Icon name="heroicons:presentation-chart-line" class="h-8 w-8 text-[#a17e4f]" />
            </div>
            <h3 class="text-xl font-semibold text-secondary-900 mb-3">
              Consultants & Coaches
            </h3>
            <p class="text-secondary-600 leading-relaxed mb-4">
              Convert conversations into consultations with embedded booking links and testimonials. Share your methodology instantly.
            </p>
            <ul class="space-y-2 text-sm">
              <li class="flex items-center text-secondary-600">
                <Icon name="heroicons:check" class="h-4 w-4 text-success-600 mr-2" />
                Discovery call scheduler
              </li>
              <li class="flex items-center text-secondary-600">
                <Icon name="heroicons:check" class="h-4 w-4 text-success-600 mr-2" />
                Case study gallery
              </li>
              <li class="flex items-center text-secondary-600">
                <Icon name="heroicons:check" class="h-4 w-4 text-success-600 mr-2" />
                Assessment forms
              </li>
            </ul>
          </div>

          <!-- Recruiters & HR -->
          <div
            class="use-card group"
            data-reveal
          >
            <div class="w-16 h-16 bg-gradient-to-br from-primary-100 to-primary-200 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
              <Icon name="heroicons:user-group" class="h-8 w-8 text-primary-600" />
            </div>
            <h3 class="text-xl font-semibold text-secondary-900 mb-3">
              Recruiters & HR
            </h3>
            <p class="text-secondary-600 leading-relaxed mb-4">
              Capture candidate info instantly at job fairs. Share open positions and company culture videos on tap.
            </p>
            <ul class="space-y-2 text-sm">
              <li class="flex items-center text-secondary-600">
                <Icon name="heroicons:check" class="h-4 w-4 text-success-600 mr-2" />
                Job board integration
              </li>
              <li class="flex items-center text-secondary-600">
                <Icon name="heroicons:check" class="h-4 w-4 text-success-600 mr-2" />
                Application form links
              </li>
              <li class="flex items-center text-secondary-600">
                <Icon name="heroicons:check" class="h-4 w-4 text-success-600 mr-2" />
                Company overview videos
              </li>
            </ul>
          </div>

          <!-- Financial Advisors -->
          <div
            class="use-card group"
            data-reveal
          >
            <div class="w-16 h-16 bg-gradient-to-br from-success-100 to-success-200 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
              <Icon name="heroicons:banknotes" class="h-8 w-8 text-success-600" />
            </div>
            <h3 class="text-xl font-semibold text-secondary-900 mb-3">
              Financial Advisors
            </h3>
            <p class="text-secondary-600 leading-relaxed mb-4">
              Build trust with credentials, client testimonials, and secure meeting scheduling. Share market insights and resources.
            </p>
            <ul class="space-y-2 text-sm">
              <li class="flex items-center text-secondary-600">
                <Icon name="heroicons:check" class="h-4 w-4 text-success-600 mr-2" />
                Compliance-friendly sharing
              </li>
              <li class="flex items-center text-secondary-600">
                <Icon name="heroicons:check" class="h-4 w-4 text-success-600 mr-2" />
                Document portal links
              </li>
              <li class="flex items-center text-secondary-600">
                <Icon name="heroicons:check" class="h-4 w-4 text-success-600 mr-2" />
                Educational content hub
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Product Gallery Section (ink, product-led) -->
    <section
      id="product-gallery"
      class="section ink-surface ink-grid relative overflow-hidden"
    >
      <div class="container relative">
        <div class="max-w-2xl mb-14" data-reveal>
          <span class="lp-kicker on-ink">Materials &amp; Design</span>
          <h2 class="lp-h2 on-ink mt-4 mb-4">Design your perfect card</h2>
          <p class="lp-lead lead-ink">
            Choose from premium materials and stunning designs that reflect your
            personal brand. Hover any card to see the back.
          </p>
        </div>

        <div class="mb-20">
          <h3 class="gallery-sub">Premium card materials</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <div
              v-for="m in cardMaterials"
              :key="m.name"
              class="material-item group cursor-pointer"
              data-reveal
            >
              <div class="material-stage">
                <div class="nfc-mock material-card">
                  <!-- Front -->
                  <div class="nfc-mock__face" :class="m.face">
                    <div class="nfc-mock__sheen"></div>
                    <div class="relative z-10 flex items-center justify-between">
                      <span class="nfc-mock__brand" :class="m.accent"
                        >NFCGo</span
                      >
                      <Icon
                        name="heroicons:wifi"
                        class="h-4 w-4 nfc-mock__kwave"
                        :class="m.accent"
                      />
                    </div>
                    <div class="nfc-mock__chip relative z-10"></div>
                    <div class="relative z-10 flex justify-between items-end">
                      <div>
                        <div class="nfc-mock__name" :class="m.accent">
                          {{ m.sampleName }}
                        </div>
                        <div class="nfc-mock__role" :class="m.sub">
                          {{ m.sampleRole }}
                        </div>
                      </div>
                      <div class="nfc-mock__qr">
                        <Icon name="heroicons:qr-code" class="h-5 w-5" />
                      </div>
                    </div>
                  </div>
                  <!-- Back -->
                  <div
                    class="nfc-mock__face nfc-mock__face--back"
                    :class="m.face"
                  >
                    <div class="nfc-mock__sheen"></div>
                    <div class="relative z-10 flex items-center justify-between">
                      <span class="nfc-mock__brand" :class="m.accent"
                        >{{ m.name }}</span
                      >
                      <span class="nfc-mock__kwave" :class="m.accent">
                        <Icon name="heroicons:wifi" class="h-5 w-5" />
                      </span>
                    </div>
                    <div class="relative z-10 flex items-center gap-4">
                      <div class="nfc-mock__qr">
                        <Icon name="heroicons:qr-code" class="h-10 w-10" />
                      </div>
                      <div :class="m.sub" class="text-xs leading-relaxed">
                        Tap or scan to open<br />a live digital profile
                      </div>
                    </div>
                    <span class="relative z-10 text-[0.6rem] tracking-[0.18em] uppercase" :class="m.accent"
                      >embedded nfc · no battery</span
                    >
                  </div>
                </div>
              </div>
              <div class="mt-6">
                <div class="flex items-start justify-between gap-3">
                  <h4 class="text-xl font-semibold text-white">{{ m.name }}</h4>
                  <span class="material-badge" :class="m.badgeClass">{{
                    m.badge
                  }}</span>
                </div>
                <p class="text-white/60 mt-2 mb-3">{{ m.tagline }}</p>
                <span class="text-sm font-semibold text-[color:var(--brass)]"
                  >Starting from {{ m.price }}</span
                >
              </div>
            </div>
          </div>
        </div>

        <div class="mb-20">
          <h3 class="gallery-sub">Design inspirations</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
            <div
              v-for="d in cardDesigns"
              :key="d.label"
              class="group"
              data-reveal
            >
              <div class="lp-design" :class="d.face">
                <div class="flex items-center justify-between">
                  <span class="lp-design-brand">NFCGo</span>
                  <span class="lp-design-qr">
                    <Icon name="heroicons:qr-code" class="h-3 w-3" />
                  </span>
                </div>
                <div>
                  <div class="lp-design-name">Your Name</div>
                  <div class="lp-design-role">Job Title</div>
                </div>
              </div>
              <p class="mt-3 text-center text-sm font-medium text-white/65">
                {{ d.label }}
              </p>
            </div>
          </div>
        </div>

        <div class="lp-cta rounded-3xl p-8 md:p-12">
          <div class="lp-cta-grid"></div>
          <div class="relative max-w-2xl">
            <span class="eyebrow-brass mb-6">
              <span class="dot"></span>
              Custom &amp; bulk
            </span>
            <h3 class="text-2xl md:text-3xl font-bold text-white mb-4">
              Need a custom design or bulk order?
            </h3>
            <p class="text-lg text-white/70 mb-8">
              We help teams print branded NFC cards with your logo, colours and
              layout — plus volume pricing for company-wide rollouts.
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
              <NuxtLink to="/UserAccount/register" class="btn-brass btn-lg">
                <Icon name="heroicons:rocket-launch" class="h-5 w-5 mr-2" />
                Start Free Trial
              </NuxtLink>
              <button @click="openContactModal" class="btn-ghost-light btn-lg">
                <Icon
                  name="heroicons:chat-bubble-left-right"
                  class="h-5 w-5 mr-2"
                />
                Talk to Our Team
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>



    <!-- Why Choose NFCGo Section (honest product capabilities) -->
    <section id="why-choose" class="section bg-white">
      <div class="container">
        <div class="max-w-2xl mb-14" data-reveal>
          <span class="lp-kicker">Why NFCGo</span>
          <h2 class="lp-h2 mt-4 mb-4">Why teams choose NFCGo</h2>
          <p class="lp-lead">
            A digital business card platform built around one-tap sharing and
            real-time insight into your connections.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            class="why-card"
            data-reveal
          >
            <div class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center mb-4">
              <Icon name="heroicons:user-circle" class="h-6 w-6 text-primary-600" />
            </div>
            <h3 class="text-lg font-semibold text-secondary-900 mb-2">Digital Profile</h3>
            <p class="text-secondary-600">
              Keep your contact details, links, and portfolio in one profile you can update at any time.
            </p>
          </div>

          <div
            class="why-card"
            data-reveal
          >
            <div class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center mb-4">
              <Icon name="heroicons:bolt" class="h-6 w-6 text-primary-600" />
            </div>
            <h3 class="text-lg font-semibold text-secondary-900 mb-2">One-Tap Sharing</h3>
            <p class="text-secondary-600">
              Share your profile instantly by tapping an NFC card or scanning a QR code.
            </p>
          </div>

          <div
            class="why-card"
            data-reveal
          >
            <div class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center mb-4">
              <Icon name="heroicons:chart-bar" class="h-6 w-6 text-primary-600" />
            </div>
            <h3 class="text-lg font-semibold text-secondary-900 mb-2">Real-Time Analytics</h3>
            <p class="text-secondary-600">
              See taps, views, and link clicks as they happen from your dashboard.
            </p>
          </div>

          <div
            class="why-card"
            data-reveal
          >
            <div class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center mb-4">
              <Icon name="heroicons:rectangle-stack" class="h-6 w-6 text-primary-600" />
            </div>
            <h3 class="text-lg font-semibold text-secondary-900 mb-2">Multiple Cards Per Account</h3>
            <p class="text-secondary-600">
              Manage several cards and profiles from a single account as your needs grow.
            </p>
          </div>

          <div
            class="why-card"
            data-reveal
          >
            <div class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center mb-4">
              <Icon name="heroicons:device-phone-mobile" class="h-6 w-6 text-primary-600" />
            </div>
            <h3 class="text-lg font-semibold text-secondary-900 mb-2">No App Required</h3>
            <p class="text-secondary-600">
              Your profile opens in the recipient's browser — nothing to install.
            </p>
          </div>

          <div
            class="why-card"
            data-reveal
          >
            <div class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center mb-4">
              <Icon name="heroicons:paint-brush" class="h-6 w-6 text-primary-600" />
            </div>
            <h3 class="text-lg font-semibold text-secondary-900 mb-2">Customizable Templates</h3>
            <p class="text-secondary-600">
              Match your profile to your personal or company brand with ready-made templates.
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12">
          <div class="flex items-center justify-center gap-3 bg-[color:var(--paper-2)] rounded-xl p-5">
            <Icon name="heroicons:check-circle" class="h-6 w-6 text-success-600 flex-shrink-0" />
            <span class="text-secondary-700 font-medium">Works on iPhone and Android</span>
          </div>
          <div class="flex items-center justify-center gap-3 bg-[color:var(--paper-2)] rounded-xl p-5">
            <Icon name="heroicons:check-circle" class="h-6 w-6 text-success-600 flex-shrink-0" />
            <span class="text-secondary-700 font-medium">No app required</span>
          </div>
          <div class="flex items-center justify-center gap-3 bg-[color:var(--paper-2)] rounded-xl p-5">
            <Icon name="heroicons:check-circle" class="h-6 w-6 text-success-600 flex-shrink-0" />
            <span class="text-secondary-700 font-medium">Card activation in minutes</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing Section -->
    <section
      id="pricing"
      class="section ink-surface ink-grid relative overflow-hidden"
    >
      <div class="container relative">
        <div class="max-w-2xl mb-14" data-reveal>
          <span class="lp-kicker on-ink">Pricing</span>
          <h2 class="lp-h2 on-ink mt-4 mb-4">Simple, flexible pricing</h2>
          <p class="lp-lead lead-ink">
            Choose the plan that fits your networking needs. Start free and
            upgrade as you grow.
          </p>
        </div>
        <div
          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto"
        >
          <!-- Free -->
          <div class="price-card" data-reveal>
            <h3 class="price-card__name">Free</h3>
            <div class="price-card__amount">RM0</div>
            <p class="price-card__desc">Perfect for getting started</p>
            <ul class="price-card__list">
              <li>
                <Icon name="heroicons:check" class="h-4 w-4 price-card__tick" />
                <span>1 Digital Profile</span>
              </li>
              <li>
                <Icon name="heroicons:check" class="h-4 w-4 price-card__tick" />
                <span>Basic Templates</span>
              </li>
              <li>
                <Icon name="heroicons:check" class="h-4 w-4 price-card__tick" />
                <span>Contact Information</span>
              </li>
              <li>
                <Icon name="heroicons:check" class="h-4 w-4 price-card__tick" />
                <span>Social Media Links</span>
              </li>
              <li>
                <Icon name="heroicons:check" class="h-4 w-4 price-card__tick" />
                <span>QR Code Backup</span>
              </li>
              <li>
                <Icon name="heroicons:check" class="h-4 w-4 price-card__tick" />
                <span>Basic Analytics</span>
              </li>
            </ul>
            <div class="price-card__cta">
              <NuxtLink to="/UserAccount/register" class="btn-ghost-light w-full">
                Get Started Free
              </NuxtLink>
            </div>
          </div>

          <!-- Basic -->
          <div class="price-card" data-reveal>
            <h3 class="price-card__name">Basic</h3>
            <div class="price-card__amount">
              {{ loadingPrices ? 'Loading...' : (basicPlan?.currency + ' ' + basicPlan?.price) || 'Contact for Pricing' }}
            </div>
            <p class="price-card__desc">
              {{ basicPlan?.description || 'For individual professionals' }}
            </p>
            <ul class="price-card__list">
              <li v-for="feature in basicPlan?.features" :key="feature">
                <Icon name="heroicons:check" class="h-4 w-4 price-card__tick" />
                <span>{{ feature }}</span>
              </li>
            </ul>
            <div class="price-card__cta">
              <NuxtLink to="/UserAccount/register" class="btn-ghost-light w-full">
                Start Free Trial
              </NuxtLink>
            </div>
          </div>

          <!-- Premium -->
          <div class="price-card price-card--pop" data-reveal>
            <span class="price-card__badge">Most Popular</span>
            <h3 class="price-card__name">Premium</h3>
            <div class="price-card__amount">
              {{ loadingPrices ? 'Loading...' : (premiumPlan?.currency + ' ' + premiumPlan?.price) || 'Contact for Pricing' }}
            </div>
            <p class="price-card__desc">
              {{ premiumPlan?.description || 'For growing businesses' }}
            </p>
            <ul class="price-card__list">
              <li v-for="feature in premiumPlan?.features" :key="feature">
                <Icon name="heroicons:check" class="h-4 w-4 price-card__tick" />
                <span>{{ feature }}</span>
              </li>
            </ul>
            <div class="price-card__cta">
              <NuxtLink to="/UserAccount/register" class="btn-brass w-full">
                Start Free Trial
              </NuxtLink>
            </div>
          </div>

          <!-- Business -->
          <div class="price-card" data-reveal>
            <h3 class="price-card__name">Business</h3>
            <div class="price-card__amount">
              {{ loadingPrices ? 'Loading...' : (businessPlan?.currency + ' ' + businessPlan?.price) || 'Contact for Pricing' }}
            </div>
            <p class="price-card__desc">
              {{ businessPlan?.description || 'For large organizations' }}
            </p>
            <ul class="price-card__list">
              <li v-for="feature in businessPlan?.features" :key="feature">
                <Icon name="heroicons:check" class="h-4 w-4 price-card__tick" />
                <span>{{ feature }}</span>
              </li>
            </ul>
            <div class="price-card__cta">
              <button @click="contactSupport" class="btn-ghost-light w-full">
                Contact Sales
              </button>
            </div>
          </div>
        </div>

        <div class="mt-12" data-reveal>
          <p class="text-white/55">
            All paid plans include 14-day free trial • No credit card required •
            Cancel anytime
          </p>
          <div class="mt-3 flex flex-col sm:flex-row gap-2 items-start sm:items-center">
            <p class="text-sm text-white/65">Need a custom solution?</p>
            <button
              @click="contactSupport"
              class="text-[color:var(--brass)] hover:text-[color:var(--brass-200)] font-medium text-sm underline"
            >
              Contact our support team
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ Section -->
    <section
      v-if="faqLoading || faqItems.length > 0"
      id="faq"
      class="section bg-[color:var(--paper)]"
    >
      <div class="container">
        <div class="max-w-2xl mb-14" data-reveal>
          <span class="lp-kicker">FAQ</span>
          <h2 class="lp-h2 mt-4 mb-4">Frequently asked questions</h2>
          <p class="lp-lead">
            Answers to common questions about NFCGo — cards, shipping,
            activation and your digital profile.
          </p>
        </div>

        <div v-if="faqLoading" class="text-center py-8">
          <div
            class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[color:var(--brass)]"
          ></div>
        </div>

        <div v-else class="max-w-3xl faq-list" data-reveal>
          <div
            v-for="(item, index) in faqItems"
            :key="item.id"
            class="faq-item"
            :class="openFaqIndex === index ? 'faq-item--open' : ''"
          >
            <button
              @click="toggleFaq(index)"
              class="faq-item__head focus:outline-none"
              :aria-expanded="openFaqIndex === index ? 'true' : 'false'"
              :aria-controls="`faq-answer-${item.id}`"
            >
              <span class="faq-item__q">{{ item.question }}</span>
              <span class="faq-item__icon">
                <Icon name="heroicons:plus" class="h-4 w-4" />
              </span>
            </button>
            <div
              v-show="openFaqIndex === index"
              :id="`faq-answer-${item.id}`"
              class="faq-item__body"
            >
              {{ item.answer }}
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Shipping & Activation Section -->
    <section id="shipping" class="section bg-white">
      <div class="container">
        <div class="max-w-2xl mb-14" data-reveal>
          <span class="lp-kicker">How It Ships</span>
          <h2 class="lp-h2 mt-4 mb-4">From order to first tap</h2>
          <p class="lp-lead">
            A simple flow to get your NFC card in hand and your profile ready to
            share.
          </p>
        </div>

        <div class="ship-track">
          <div
            v-for="(step, i) in shippingSteps"
            :key="step.title"
            class="ship-step"
            data-reveal
          >
            <div class="ship-step__marker">
              <Icon :name="step.icon" class="h-6 w-6" />
            </div>
            <span class="ship-step__index">{{ String(i + 1).padStart(2, '0') }}</span>
            <h3 class="ship-step__title">{{ step.title }}</h3>
            <p class="ship-step__desc">{{ step.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="lp-cta section">
      <div class="lp-cta-grid"></div>
      <div class="container relative text-center">
        <span class="eyebrow-brass"><span class="dot"></span> Start today</span>
        <h2 class="text-3xl md:text-5xl font-extrabold text-white mt-6 mb-4 tracking-tight" data-reveal>
          Ready to transform your networking?
        </h2>
        <p class="text-lg md:text-xl text-white/70 mb-8 max-w-2xl mx-auto" data-reveal>
          Create your smart business card in minutes — no app, no printing, no
          waste. Just tap and connect.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
          <NuxtLink to="/UserAccount/register" class="btn-brass btn-lg">
            <Icon name="heroicons:rocket-launch" class="h-5 w-5 mr-2" />
            Start Your Free Trial
          </NuxtLink>
          <button
            @click="smoothScroll('#how-it-works')"
            class="btn-ghost-light btn-lg"
          >
            <Icon name="heroicons:play" class="h-5 w-5 mr-2" />
            See How It Works
          </button>
        </div>
        <div
          class="flex flex-wrap items-center justify-center gap-8 text-white/65"
        >
          <div class="flex items-center">
            <Icon name="heroicons:check-circle" class="h-5 w-5 mr-2" />
            <span class="text-sm">Free 14-day trial</span>
          </div>
          <div class="flex items-center">
            <Icon name="heroicons:check-circle" class="h-5 w-5 mr-2" />
            <span class="text-sm">No credit card required</span>
          </div>
          <div class="flex items-center">
            <Icon name="heroicons:check-circle" class="h-5 w-5 mr-2" />
            <span class="text-sm">Cancel anytime</span>
          </div>
        </div>
      </div>
    </section>

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
                <a
                  href="#features"
                  @click="smoothScroll('#features')"
                  class="footer-ink__link"
                  >Features</a
                >
              </li>
              <li>
                <a
                  href="#product-gallery"
                  @click="smoothScroll('#product-gallery')"
                  class="footer-ink__link"
                  >Cards</a
                >
              </li>
              <li>
                <a
                  href="#pricing"
                  @click="smoothScroll('#pricing')"
                  class="footer-ink__link"
                  >Pricing</a
                >
              </li>
            </ul>
          </div>
          <div>
            <h4 class="footer-ink__head">Support</h4>
            <ul class="space-y-2">
              <li>
                <a
                  href="#faq"
                  @click="smoothScroll('#faq')"
                  class="footer-ink__link"
                  >FAQ</a
                >
              </li>
              <li>
                <a
                  href="#shipping"
                  @click="smoothScroll('#shipping')"
                  class="footer-ink__link"
                  >Shipping</a
                >
              </li>
              <li>
                <button @click="openContactModal" class="footer-ink__link"
                  >Contact Us</button
                >
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
                <button @click="openLegalModal('privacy')" class="footer-ink__link"
                  >Privacy</button
                >
              </li>
              <li>
                <button @click="openLegalModal('terms')" class="footer-ink__link"
                  >Terms</button
                >
              </li>
            </ul>
          </div>
        </div>
        <div class="footer-ink__bottom">
          <p>
            @Copyright CLB SDN BHD – 2025 -RFID Label Supplier , 
            RFID Solution and Stick Printing I All Rights Reserved
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
      <div class="contact-modal-content bg-white rounded-2xl shadow-2xl w-full max-w-[700px] max-h-[90vh] overflow-hidden animate-modal-appear">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-secondary-200 bg-gradient-to-r from-primary-50 to-secondary-50">
          <div>
            <h2 class="text-2xl font-bold text-secondary-900">Contact Us</h2>
            <p class="text-sm text-secondary-600 mt-1">We'd love to hear from you!</p>
          </div>
          <button
            @click="closeContactModal"
            class="p-2 rounded-full hover:bg-white/80 transition-colors"
            aria-label="Close modal"
          >
            <Icon name="heroicons:x-mark" class="h-6 w-6 text-secondary-600" />
          </button>
        </div>
        
        <!-- Modal Body - Google Form Iframe -->
        <div class="overflow-y-auto" style="height: calc(90vh - 100px); max-height: 800px;">
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

    <!-- AI Chatbot Modal -->
    <div
      v-if="showChatbot"
      class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4 bg-black/50 backdrop-blur-sm"
    >
      <div class="bg-white rounded-t-3xl sm:rounded-2xl w-full sm:max-w-2xl h-[80vh] sm:h-[600px] flex flex-col shadow-2xl">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-secondary-200 bg-gradient-to-r from-primary-600 to-primary-500 rounded-t-3xl sm:rounded-t-2xl">
          <div class="flex items-center">
            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center mr-3">
              <Icon name="heroicons:chat-bubble-left-right" class="h-6 w-6 text-white" />
            </div>
            <div>
              <h3 class="text-lg font-bold text-white">AI Assistant</h3>
              <p class="text-xs text-primary-100">Ask me anything about NFCGo</p>
            </div>
          </div>
          <button
            @click="showChatbot = false"
            class="p-2 hover:bg-white/20 rounded-lg transition-colors"
          >
            <Icon name="heroicons:x-mark" class="h-6 w-6 text-white" />
          </button>
        </div>

        <!-- Messages Container -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4" ref="chatMessages">
          <!-- Welcome Message -->
          <div v-if="messages.length === 0" class="text-center py-8">
            <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <Icon name="heroicons:sparkles" class="h-8 w-8 text-primary-600" />
            </div>
            <h4 class="text-lg font-semibold text-secondary-900 mb-2">Welcome to NFCGo AI Assistant!</h4>
            <p class="text-sm text-secondary-600 mb-4">I can help you with:</p>
            
            <div v-if="loadingQuestions" class="text-center py-4">
              <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600"></div>
            </div>
            
            <div v-else-if="popularQuestions.length > 0" class="grid grid-cols-1 gap-2 max-w-sm mx-auto text-left">
              <button
                v-for="(q, index) in popularQuestions.slice(0, 3)"
                :key="q.id"
                @click="sendQuickMessage(q.question)"
                class="p-3 bg-secondary-50 hover:bg-secondary-100 rounded-lg text-sm text-secondary-700 transition-colors text-left"
              >
                {{ ['💡', '💰', '🎨', '📱', '🚀', '✨'][index % 6] }} {{ q.question }}
              </button>
            </div>
            
            <!-- Fallback if no questions loaded -->
            <div v-else class="grid grid-cols-1 gap-2 max-w-sm mx-auto text-left">
              <button
                @click="sendQuickMessage('How does NFC business card work?')"
                class="p-3 bg-secondary-50 hover:bg-secondary-100 rounded-lg text-sm text-secondary-700 transition-colors text-left"
              >
                💡 How does NFC business card work?
              </button>
              <button
                @click="sendQuickMessage('What are the pricing plans?')"
                class="p-3 bg-secondary-50 hover:bg-secondary-100 rounded-lg text-sm text-secondary-700 transition-colors text-left"
              >
                💰 What are the pricing plans?
              </button>
              <button
                @click="sendQuickMessage('Can I customize my digital profile?')"
                class="p-3 bg-secondary-50 hover:bg-secondary-100 rounded-lg text-sm text-secondary-700 transition-colors text-left"
              >
                🎨 Can I customize my digital profile?
              </button>
            </div>
          </div>

          <!-- Chat Messages -->
          <div
            v-for="(message, index) in messages"
            :key="index"
            :class="[
              'flex',
              message.sender === 'user' ? 'justify-end' : 'justify-start'
            ]"
          >
            <div class="max-w-[80%]">
              <div
                :class="[
                  'rounded-2xl px-4 py-3',
                  message.sender === 'user'
                    ? 'bg-primary-600 text-white rounded-br-sm'
                    : 'bg-secondary-100 text-secondary-900 rounded-bl-sm'
                ]"
              >
                <p class="text-sm whitespace-pre-wrap">{{ message.text }}</p>
                <p
                  :class="[
                    'text-xs mt-1',
                    message.sender === 'user' ? 'text-primary-100' : 'text-secondary-500'
                  ]"
                >
                  {{ formatTime(message.timestamp) }}
                </p>
              </div>
              
              <!-- Feedback buttons for AI responses -->
              <div v-if="message.sender === 'ai' && message.questionId && !message.feedbackGiven" class="flex items-center space-x-2 mt-2 ml-2">
                <span class="text-xs text-secondary-500">Was this helpful?</span>
                <button
                  @click="submitFeedback(message, true)"
                  class="p-1 hover:bg-success-100 rounded transition-colors"
                  title="Helpful"
                >
                  <Icon name="heroicons:hand-thumb-up" class="h-4 w-4 text-success-600" />
                </button>
                <button
                  @click="submitFeedback(message, false)"
                  class="p-1 hover:bg-error-100 rounded transition-colors"
                  title="Not helpful"
                >
                  <Icon name="heroicons:hand-thumb-down" class="h-4 w-4 text-error-600" />
                </button>
              </div>
              <div v-else-if="message.sender === 'ai' && message.feedbackGiven" class="ml-2 mt-1">
                <span class="text-xs text-success-600">✓ Thanks for your feedback!</span>
              </div>
            </div>
          </div>

          <!-- Typing Indicator -->
          <div v-if="isTyping" class="flex justify-start">
            <div class="bg-secondary-100 rounded-2xl rounded-bl-sm px-4 py-3">
              <div class="flex space-x-2">
                <div class="w-2 h-2 bg-secondary-400 rounded-full animate-bounce" style="animation-delay: 0s"></div>
                <div class="w-2 h-2 bg-secondary-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                <div class="w-2 h-2 bg-secondary-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Input Area -->
        <div class="p-4 border-t border-secondary-200 bg-secondary-50">
          <form @submit.prevent="sendMessage" class="flex space-x-2">
            <input
              v-model="chatInput"
              type="text"
              placeholder="Type your message..."
              class="flex-1 px-4 py-3 border border-secondary-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              :disabled="isTyping"
            />
            <button
              type="submit"
              :disabled="!chatInput.trim() || isTyping"
              class="btn btn-primary px-6 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <Icon name="heroicons:paper-airplane" class="h-5 w-5" />
            </button>
          </form>
          <button
            @click="showFeedbackModal = true"
            class="mt-2 text-xs text-primary-600 hover:text-primary-700 flex items-center space-x-1"
          >
            <Icon name="heroicons:chat-bubble-bottom-center-text" class="h-4 w-4" />
            <span>Send us feedback or report an issue</span>
          </button>
        </div>
      </div>
    </div>

    <!-- User Feedback Modal -->
    <div
      v-if="showFeedbackModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
      @click="showFeedbackModal = false"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto"
        @click.stop
      >
        <div class="p-6 border-b border-secondary-200">
          <h3 class="text-xl font-bold text-secondary-900">Send Feedback</h3>
          <p class="text-sm text-secondary-600 mt-1">Help us improve by sharing your thoughts</p>
        </div>

        <form @submit.prevent="submitUserFeedback" class="p-6 space-y-4">
          <!-- Category -->
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2">
              Category <span class="text-red-500">*</span>
            </label>
            <select
              v-model="feedbackForm.category"
              required
              class="w-full px-4 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            >
              <option value="">Select a category</option>
              <option value="bug">🐛 Bug Report</option>
              <option value="feature">💡 Feature Request</option>
              <option value="question">❓ Question</option>
              <option value="complaint">😞 Complaint</option>
              <option value="suggestion">💭 Suggestion</option>
              <option value="other">📝 Other</option>
            </select>
          </div>

          <!-- Name (Optional) -->
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2">
              Your Name (Optional)
            </label>
            <input
              v-model="feedbackForm.user_name"
              type="text"
              placeholder="John Doe"
              class="w-full px-4 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            />
          </div>

          <!-- Email (Optional) -->
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2">
              Your Email (Optional)
            </label>
            <input
              v-model="feedbackForm.user_email"
              type="email"
              placeholder="john@example.com"
              class="w-full px-4 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            />
          </div>

          <!-- Message -->
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2">
              Message <span class="text-red-500">*</span>
            </label>
            <textarea
              v-model="feedbackForm.message"
              rows="4"
              required
              placeholder="Please describe your feedback in detail..."
              class="w-full px-4 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            ></textarea>
          </div>

          <!-- Rating -->
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2">
              Overall Experience (Optional)
            </label>
            <div class="flex space-x-2">
              <button
                v-for="star in 5"
                :key="star"
                type="button"
                @click="feedbackForm.rating = star"
                class="text-2xl focus:outline-none transition-transform hover:scale-110"
              >
                {{ star <= feedbackForm.rating ? '⭐' : '☆' }}
              </button>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex space-x-3 pt-4">
            <button
              type="button"
              @click="showFeedbackModal = false"
              class="flex-1 py-2 px-4 bg-secondary-100 text-secondary-700 rounded-lg font-medium hover:bg-secondary-200 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submittingFeedback"
              class="flex-1 py-2 px-4 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors disabled:opacity-50"
            >
              {{ submittingFeedback ? 'Sending...' : 'Send Feedback' }}
            </button>
          </div>
        </form>
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
        <!-- Modal Header -->
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

        <!-- Modal Body -->
        <form @submit.prevent="submitBusinessRequest" class="p-6 space-y-6">
          <!-- Company Name -->
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

          <!-- Company Address -->
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

          <!-- Number of Cards (Quota) -->
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

          <!-- Contact Person -->
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

          <!-- Phone Number -->
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

          <!-- Email -->
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

          <!-- Additional Notes -->
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

          <!-- Action Buttons -->
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
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col animate-modal-appear">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 border-b border-secondary-200 bg-gradient-to-r from-primary-50 to-secondary-50">
          <div class="flex-1 min-w-0">
            <h2 class="text-xl font-bold text-secondary-900 truncate">
              {{ legalModalType === 'privacy' ? 'Privacy Policy' : 'Terms of Service' }}
            </h2>
            <div v-if="legalDocInfo && !loadingLegalDoc" class="flex items-center gap-3 mt-1 text-sm text-secondary-600">
              <span class="truncate max-w-[200px]" :title="legalDocInfo.filename">
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
        
        <!-- Modal Body -->
        <div class="flex-1 overflow-hidden relative">
          <!-- Loading State -->
          <div v-if="loadingLegalDoc" class="absolute inset-0 flex items-center justify-center bg-secondary-50">
            <div class="text-center">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto mb-4"></div>
              <p class="text-secondary-600">Loading document...</p>
            </div>
          </div>
          
          <!-- Error State -->
          <div v-else-if="legalDocError" class="absolute inset-0 flex items-center justify-center bg-secondary-50 p-8">
            <div class="text-center">
              <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <Icon name="heroicons:exclamation-triangle" class="h-8 w-8 text-red-600" />
              </div>
              <h3 class="text-lg font-semibold text-secondary-900 mb-2">Document Not Available</h3>
              <p class="text-secondary-600 mb-4">{{ legalDocError }}</p>
              <button
                @click="closeLegalModal"
                class="px-4 py-2 bg-secondary-100 text-secondary-700 rounded-lg hover:bg-secondary-200 transition-colors"
              >
                Close
              </button>
            </div>
          </div>
          
          <!-- PDF Viewer -->
          <iframe
            v-else-if="legalDocInfo"
            :src="legalPdfUrl"
            class="w-full h-full border-0"
            style="min-height: 70vh;"
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
import { watch, onMounted, computed } from 'vue';

const config = useRuntimeConfig();
const canonicalUrl = `${config.public.appUrl}/Homepage`;

useSeoMeta({
  title: "NFCGo - Smart Digital Business Cards with NFC Technology",
  description:
    "Create smart NFC business cards that instantly share your professional profile. Modern networking made simple with just a tap.",
  keywords: [
    "NFC business card",
    "digital business card",
    "smart networking",
    "professional networking",
    "NFC technology",
  ],
  ogTitle: "NFCGo - Smart Digital Business Cards",
  ogDescription:
    "Create smart NFC business cards that instantly share your professional profile. Modern networking made simple with just a tap.",
  ogType: "website",
  ogUrl: canonicalUrl,
  twitterCard: "summary_large_image",
});

// Canonical URL + structured data (Organization / WebSite)
useHead({
  link: [{ rel: "canonical", href: canonicalUrl }],
  script: [
    {
      type: "application/ld+json",
      innerHTML: JSON.stringify({
        "@context": "https://schema.org",
        "@graph": [
          {
            "@type": "Organization",
            name: "NFCGo",
            url: config.public.appUrl,
            sameAs: [
              "https://clbgroups.com/",
              "https://www.facebook.com/profile.php?id=61568045614509",
            ],
          },
          {
            "@type": "WebSite",
            name: "NFCGo",
            url: config.public.appUrl,
          },
        ],
      }),
    },
  ],
});

const authStore = useAuthStore();
const { $api, $toast } = useNuxtApp();
const mobileMenuOpen = ref(false);

// Hero card 3D flip (front/back preview) — auto-cycles every 15s
const heroFlipped = ref(false);
const HERO_FLIP_INTERVAL = 15000;
let heroFlipTimer = null;

// Landing page: trust marquee labels + scroll-reveal observer handle
const marqueeLabels = [
  "Instant Sharing",
  "Real-Time Analytics",
  "Works on iPhone & Android",
  "No App Required",
  "Custom Templates",
  "Eco-Friendly",
  "QR Backup",
  "Team Ready",
];
let revealObserver = null;

// FAQ (built from the public chatbot questions endpoint)
const faqItems = ref([]);
const faqLoading = ref(true);
const openFaqIndex = ref(null);

// Plan prices from API
const { planPrices, loading: loadingPrices, loadPrices } = usePlanPrices();

// Get plan details
const basicPlan = computed(() => planPrices.value.find(p => p.plan_type === 'basic'));
const premiumPlan = computed(() => planPrices.value.find(p => p.plan_type === 'premium'));
const businessPlan = computed(() => planPrices.value.find(p => p.plan_type === 'business'));

// AI Chatbot
const showChatbot = ref(false);
const chatId = ref(null); // Session identifier for n8n webhook
const chatInput = ref('');
const messages = ref([]);
const isTyping = ref(false);
const chatMessages = ref(null);
const popularQuestions = ref([]);
const loadingQuestions = ref(false);

// Contact Us Modal
const showContactModal = ref(false);

// User Feedback Modal
const showFeedbackModal = ref(false);
const submittingFeedback = ref(false);
const feedbackForm = reactive({
  category: '',
  user_name: '',
  user_email: '',
  message: '',
  rating: 0
});

// Business Plan Modal
const showBusinessModal = ref(false);
const showBusinessRequestSuccess = ref(false);
const showBusinessPlanPending = ref(false); // Shown when business plan user is blocked from login/register
const businessPlanEmail = ref(''); // Email of the business plan requester
const submittingBusinessRequest = ref(false);

// Clear business plan restriction and allow login
const clearBusinessPlanRestriction = () => {
  if (process.client) {
    localStorage.removeItem('businessPlanRequested');
    localStorage.removeItem('businessPlanEmail');
    localStorage.removeItem('businessPlanCompany');
    localStorage.removeItem('businessPlanTimestamp');
  }
  showBusinessPlanPending.value = false;
  // Navigate to login
  navigateTo('/UserAccount/login');
};

// Legal Document Modal (Privacy Policy / Terms of Service)
const showLegalModal = ref(false);
const legalModalType = ref(''); // 'privacy' or 'terms'
const loadingLegalDoc = ref(false);
const legalDocInfo = ref(null);
const legalDocError = ref('');

// Computed URL for PDF viewer
const legalPdfUrl = computed(() => {
  if (!legalModalType.value) return '';
  const config = useRuntimeConfig();
  const apiBaseUrl = config.public.apiBaseUrl || 'http://localhost:8000/api';
  return `${apiBaseUrl}/legal/pdf/${legalModalType.value}/view`;
});

// Business form data
const businessForm = reactive({
  company_name: "",
  company_address: "",
  quota: null,
  contact_person: "",
  phone: "",
  email: "",
  notes: "",
});

const steps = [
  {
    title: "Choose your card",
    desc: "Pick a material and finish that suits your brand — PVC, metal or bamboo.",
    icon: "heroicons:rectangle-stack",
  },
  {
    title: "Set up your profile",
    desc: "Add your photo, role, contact details and links from your dashboard.",
    icon: "heroicons:user-circle",
  },
  {
    title: "Activate with one tap",
    desc: "Tap the card on your phone to link it to your live digital profile.",
    icon: "heroicons:bolt",
  },
  {
    title: "Share anywhere",
    desc: "One tap opens your profile — they save your contact, you keep the lead.",
    icon: "heroicons:share",
  },
];

// Landing page: order-to-first-tap shipping flow
const shippingSteps = [
  {
    title: "Place Your Order",
    desc: "Choose your card and complete checkout.",
    icon: "heroicons:shopping-cart",
  },
  {
    title: "Get It Delivered",
    desc: "Your card is shipped to the address you provide.",
    icon: "heroicons:truck",
  },
  {
    title: "Activate It",
    desc: "Tap the card on your phone or log in to activate it.",
    icon: "heroicons:device-phone-mobile",
  },
  {
    title: "Link or Build Your Profile",
    desc: "Add your details, then share your card with one tap.",
    icon: "heroicons:share",
  },
];

// Landing page: digital profile preview (design demonstration on a phone mockup)
const profileLinks = [
  { label: "Website", icon: "heroicons:globe-alt" },
  { label: "Portfolio", icon: "heroicons:briefcase" },
  { label: "LinkedIn Profile", icon: "heroicons:user-group" },
  { label: "Book a Meeting", icon: "heroicons:calendar-days" },
];

// Landing page: card material showcase (rendered as pure-CSS card mockups)
const cardMaterials = [
  {
    name: "Classic PVC",
    tagline:
      "Durable, affordable and perfect for everyday professional use.",
    price: "RM29",
    badge: "Most Popular",
    badgeClass: "text-[#e8d9b8]",
    face: "lp-face-pvc",
    accent: "text-secondary-900",
    sub: "text-secondary-500",
    sampleName: "Your Name",
    sampleRole: "Job Title",
  },
  {
    name: "Premium Metal",
    tagline:
      "Luxurious brushed metal with laser engraving. Unforgettable in hand.",
    price: "RM89",
    badge: "Premium",
    badgeClass: "text-[#c2a068]",
    face: "lp-face-metal",
    accent: "text-white",
    sub: "text-white/60",
    sampleName: "Your Name",
    sampleRole: "Job Title",
  },
  {
    name: "Eco Bamboo",
    tagline:
      "Sustainable wood-grain finish. Perfect for eco-conscious brands.",
    price: "RM59",
    badge: "Eco-Friendly",
    badgeClass: "text-[#9db98f]",
    face: "lp-face-bamboo",
    accent: "text-secondary-900",
    sub: "text-secondary-600",
    sampleName: "Your Name",
    sampleRole: "Job Title",
  },
];

// Landing page: ready-made design presets (rendered as pure-CSS card faces)
const cardDesigns = [
  { label: "Minimalist", face: "lp-design-minimal" },
  { label: "Creative", face: "lp-design-creative" },
  { label: "Professional", face: "lp-design-professional" },
  { label: "Bold", face: "lp-design-bold" },
  { label: "Executive", face: "lp-design-executive" },
  { label: "Tech", face: "lp-design-tech" },
  { label: "Finance", face: "lp-design-finance" },
  { label: "Healthcare", face: "lp-design-healthcare" },
];

// FAQ logic (uses the existing public chatbot questions endpoint)
const loadFaq = async () => {
  try {
    const response = await $api.get('/chatbot/questions');
    if (response?.success && Array.isArray(response?.data)) {
      faqItems.value = response.data;
    }
  } catch (error) {
    // Degrade silently — render nothing if the request fails
    faqItems.value = [];
  } finally {
    faqLoading.value = false;
    // The FAQ block only exists after this response lands — re-arm reveal for it.
    nextTick(setupReveal);
  }
};

// Toggle a single FAQ item open/closed (one open at a time)
const toggleFaq = (index) => {
  openFaqIndex.value = openFaqIndex.value === index ? null : index;
};

const smoothScroll = (target) => {
  const element = document.querySelector(target);
  if (element) {
    element.scrollIntoView({ behavior: "smooth", block: "start" });
  }
  mobileMenuOpen.value = false;
};

// Open Contact Us Modal
const openContactModal = () => {
  showContactModal.value = true;
  mobileMenuOpen.value = false;
};

// Close Contact Us Modal
const closeContactModal = () => {
  showContactModal.value = false;
};

// Handle ESC key to close modals
const handleEscKey = (event) => {
  if (event.key === 'Escape') {
    if (showContactModal.value) {
      closeContactModal();
    }
    if (showLegalModal.value) {
      closeLegalModal();
    }
  }
};

const contactSupport = () => {
  // Check if user is logged in
  if (!authStore.isAuthenticated) {
    $toast.error("Please login or register to request a Business Plan");
    navigateTo("/UserAccount/login");
    return;
  }

  // Pre-fill user info if available
  businessForm.email = authStore.user?.email || "";
  businessForm.contact_person =
    authStore.user?.first_name && authStore.user?.last_name
      ? `${authStore.user.first_name} ${authStore.user.last_name}`
      : authStore.user?.name || "";

  showBusinessModal.value = true;
};

// Close business modal
const closeBusinessModal = () => {
  showBusinessModal.value = false;
  // Reset form
  Object.assign(businessForm, {
    company_name: "",
    company_address: "",
    quota: null,
    contact_person:
      authStore.user?.first_name && authStore.user?.last_name
        ? `${authStore.user.first_name} ${authStore.user.last_name}`
        : authStore.user?.name || "",
    phone: "",
    email: authStore.user?.email || "",
    notes: "",
  });
};

// Dismiss business request success alert
const dismissBusinessRequestSuccess = () => {
  showBusinessRequestSuccess.value = false;
  // Clean up URL query parameter
  const url = new URL(window.location.href);
  url.searchParams.delete('businessRequest');
  window.history.replaceState({}, '', url.pathname);
};

// Open legal document modal (Privacy Policy or Terms of Service)
const openLegalModal = async (type) => {
  legalModalType.value = type;
  showLegalModal.value = true;
  loadingLegalDoc.value = true;
  legalDocError.value = '';
  legalDocInfo.value = null;

  try {
    const response = await $api.get(`/legal/pdf/${type}/info`);
    if (response.success && response.data) {
      legalDocInfo.value = response.data;
    } else {
      legalDocError.value = response.message || 'Document not available.';
    }
  } catch (error) {
    console.error('Error loading legal document info:', error);
    legalDocError.value = error.data?.message || 'Failed to load document. Please try again later.';
  } finally {
    loadingLegalDoc.value = false;
  }
};

// Close legal document modal
const closeLegalModal = () => {
  showLegalModal.value = false;
  legalModalType.value = '';
  legalDocInfo.value = null;
  legalDocError.value = '';
};

// Format file size for display
const formatFileSize = (bytes) => {
  if (!bytes) return '';
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

// Submit business plan request
const submitBusinessRequest = async () => {
  submittingBusinessRequest.value = true;

  try {
    const response = await $api.post("/business-inquiries", {
      company_name: businessForm.company_name,
      company_address: businessForm.company_address,
      quota: businessForm.quota,
      contact_person: businessForm.contact_person,
      phone: businessForm.phone,
      email: businessForm.email,
      notes: businessForm.notes,
      source: "homepage",
    });

    if (!response?.success) {
      throw new Error(response?.message || "Failed to submit enquiry");
    }

    $toast.success(
      "Your Business Plan enquiry has been received. Our team will contact you soon."
    );

    closeBusinessModal();
    // Show inline success alert since already on Homepage
    showBusinessRequestSuccess.value = true;
    // Auto-dismiss after 15 seconds
    setTimeout(() => {
      dismissBusinessRequestSuccess();
    }, 15000);
  } catch (error) {
    console.error("Business plan request error:", error);
    $toast.error(
      error?.response?.data?.message ||
        "Failed to submit your enquiry. Please try again or contact support."
    );
  } finally {
    submittingBusinessRequest.value = false;
  }
};

const sendMessage = async () => {
  if (!chatInput.value.trim()) return;

  const userMessage = {
    sender: 'user',
    text: chatInput.value,
    timestamp: new Date()
  };

  messages.value.push(userMessage);
  const question = chatInput.value;
  chatInput.value = '';

  // Scroll to bottom
  nextTick(() => {
    if (chatMessages.value) {
      chatMessages.value.scrollTop = chatMessages.value.scrollHeight;
    }
  });

  // Show typing indicator
  isTyping.value = true;

  try {
    // Direct POST to n8n webhook
    const response = await fetch('https://n8n.jiosgroup.com/webhook/e529b3a4-d09d-45d6-8de5-01cc6885bcb7/chat', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        sessionId: chatId.value,
        chatInput: question
      })
    });

    if (!response.ok) {
      throw new Error(`Webhook error: ${response.status} ${response.statusText}`);
    }

    const data = await response.json();

    let aiResponse = data?.output || data?.text || data?.message || (data?.data && data.data.output) || null;
    let questionId = data?.id || null;

    if (!aiResponse) {
      aiResponse = "I couldn't find a specific answer to your question. Please contact support or try again later.";
    }

    messages.value.push({
      sender: 'ai',
      text: aiResponse,
      timestamp: new Date(),
      questionId: questionId
    });

    isTyping.value = false;

    // Scroll to bottom
    nextTick(() => {
      if (chatMessages.value) {
        chatMessages.value.scrollTop = chatMessages.value.scrollHeight;
      }
    });
  } catch (error) {
    console.error('Chatbot error:', error);
    
    // Fallback to static responses if API fails
    const aiResponse = getAIResponseFallback(question);
    messages.value.push({
      sender: 'ai',
      text: aiResponse,
      timestamp: new Date()
    });
    
    isTyping.value = false;

    nextTick(() => {
      if (chatMessages.value) {
        chatMessages.value.scrollTop = chatMessages.value.scrollHeight;
      }
    });
  }
};

const sendQuickMessage = (message) => {
  chatInput.value = message;
  sendMessage();
};

// Load popular questions - Deprecated: No longer loading from backend
const loadPopularQuestions = async () => {
  loadingQuestions.value = false;
  // Use fallback questions from template
};

// Submit feedback (helpful/not helpful) - Deprecated: No longer sending to backend
const submitFeedback = async (message, isHelpful) => {
  // Just mark as given locally without backend call
  message.feedbackGiven = true;
};

// Watch for chatbot modal opening to load questions
watch(showChatbot, (newValue) => {
  if (newValue) {
    // Initialize a new chat session ID
    if (!chatId.value) {
      chatId.value = Date.now();
    }
    if (popularQuestions.value.length === 0) {
      loadPopularQuestions();
    }
  }
});

// Submit user feedback - Deprecated: No longer sending to backend
const submitUserFeedback = async () => {
  if (!feedbackForm.category || !feedbackForm.message) {
    alert('Please fill in all required fields');
    return;
  }

  submittingFeedback.value = true;
  
  const payload = {
    category: feedbackForm.category,
    message: feedbackForm.message,
    user_name: feedbackForm.user_name,
    user_email: feedbackForm.user_email,
    rating: feedbackForm.rating,
  };

  console.log('=== FEEDBACK SUBMISSION START ===');
  console.log('$api available:', !!$api);
  console.log('$api type:', typeof $api);
  console.log('Payload:', payload);
  console.log('Payload JSON:', JSON.stringify(payload));
  
  try {
    console.log('About to call $api.post(/chatbot/feedback)');
    const response = await $api.post('/chatbot/feedback', payload);
    console.log('Success response:', response);

    if (response.success) {
      $toast.success('Thank you for your feedback!');
      
      // Reset form
      feedbackForm.category = '';
      feedbackForm.message = '';
      feedbackForm.user_name = '';
      feedbackForm.user_email = '';
      feedbackForm.rating = null;
      
      // Close modal
      showFeedbackModal.value = false;
    } else {
      $toast.error(response.message || 'Failed to submit feedback');
    }
  } catch (error) {
    console.error('Feedback submission error:', error);
    console.error('Error response data:', error.response?.data);
    console.error('Error status:', error.response?.status);
    console.error('Error config:', {
      url: error.config?.url,
      method: error.config?.method,
      data: error.config?.data,
      headers: error.config?.headers
    });
    console.log('Payload sent:', {
      category: feedbackForm.category,
      message: feedbackForm.message,
      user_name: feedbackForm.user_name,
      user_email: feedbackForm.user_email,
      rating: feedbackForm.rating,
    });
    
    let errorMessage = 'Failed to submit feedback. Please try again later.';
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    } else if (error.response?.data?.errors) {
      const errors = error.response.data.errors;
      const firstErrorKey = Object.keys(errors)[0];
      errorMessage = errors[firstErrorKey]?.[0] || JSON.stringify(errors);
    }
    
    $toast.error(errorMessage);
  } finally {
    submittingFeedback.value = false;
  }
};

// Fallback responses when API is unavailable (kept for offline resilience)
const getAIResponseFallback = (question) => {
  const lowerQuestion = question.toLowerCase();

  if (lowerQuestion.includes('how') && lowerQuestion.includes('work')) {
    return "NFC business cards work through Near Field Communication technology. Simply tap your card on any NFC-enabled smartphone, and your digital profile opens instantly - no app needed! The recipient can view your contact info, social media, portfolio, and save everything with one tap.";
  } else if (lowerQuestion.includes('pricing') || lowerQuestion.includes('price') || lowerQuestion.includes('cost')) {
    return "We offer 4 plans:\n\n• Free: 1 profile, basic features\n• Basic: 3 profiles, premium templates (contact for pricing)\n• Premium: 10 profiles, team management (contact for pricing)\n• Business: Unlimited profiles, white label (contact for pricing)\n\nAll paid plans include a 14-day free trial with no credit card required!";
  } else if (lowerQuestion.includes('custom')) {
    return "Yes! You can fully customize your digital profile with:\n\n• Your photo and branding\n• Custom colors and themes\n• Links to social media, website, portfolio\n• Contact forms and calendars\n• Video introductions\n• Product galleries\n\nUpdate anytime without reprinting cards!";
  } else if (lowerQuestion.includes('card') && (lowerQuestion.includes('material') || lowerQuestion.includes('type'))) {
    return "We offer premium card materials:\n\n• PVC Plastic - Durable, affordable, vibrant colors\n• Metal - Premium feel, ultra-durable, modern design\n• Bamboo - Eco-friendly, sustainable, unique texture\n\nAll cards include embedded NFC chips for instant sharing!";
  } else if (lowerQuestion.includes('analytics') || lowerQuestion.includes('track')) {
    return "Yes! Our analytics dashboard shows:\n\n• Total taps and views\n• Geographic locations\n• Time and date of interactions\n• Link clicks breakdown\n• Contact saves\n\nPerfect for measuring networking ROI!";
  } else if (lowerQuestion.includes('team') || lowerQuestion.includes('business') || lowerQuestion.includes('company')) {
    return "Absolutely! Our Business plan includes:\n\n• Centralized team management\n• Brand consistency controls\n• Bulk profile creation\n• Advanced analytics dashboard\n• API access\n• White label options\n\nPerfect for sales teams and enterprises!";
  } else {
    return "Thanks for your question! NFCGo makes professional networking effortless with NFC technology. You can:\n\n• Share contact info instantly\n• Showcase portfolios and social media\n• Track engagement analytics\n• Update profiles anytime\n• Manage team profiles\n\nWould you like to know more about our features, pricing, or how NFC technology works?";
  }
};

const formatTime = (date) => {
  return new Date(date).toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit'
  });
};

const handleClickOutside = (e) => {
  if (!e.target.closest("nav")) {
    mobileMenuOpen.value = false;
  }
};

// Scroll reveal — toggles .is-revealed on [data-reveal] elements as they enter view.
// Idempotent & re-armable: safe to call again whenever new content mounts
// (e.g. the FAQ list that renders only after its API response arrives).
const setupReveal = () => {
  if (typeof window === "undefined") return;
  const nodes = document.querySelectorAll("[data-reveal]:not(.is-revealed)");
  if (!nodes.length) return;

  const reduceMotion =
    window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  if (reduceMotion || !("IntersectionObserver" in window)) {
    nodes.forEach((el) => el.classList.add("is-revealed"));
    return;
  }

  if (!revealObserver) {
    revealObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-revealed");
            revealObserver.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.12, rootMargin: "0px 0px -8% 0px" }
    );
  }

  nodes.forEach((el) => revealObserver.observe(el));
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
  document.addEventListener("keydown", handleEscKey);

  // Reveal landing page sections on scroll
  setupReveal();

  // Auto-flip the hero card between front and back
  const reduceMotion =
    window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  if (!reduceMotion) {
    heroFlipTimer = window.setInterval(() => {
      heroFlipped.value = !heroFlipped.value;
    }, HERO_FLIP_INTERVAL);
  }
  
  // Load prices, questions and FAQ
  loadPrices();
  loadPopularQuestions();
  loadFaq();

  // Check for business request success query parameter
  const route = useRoute();
  if (route.query.businessRequest === 'success') {
    showBusinessRequestSuccess.value = true;
    // Auto-dismiss after 15 seconds
    setTimeout(() => {
      dismissBusinessRequestSuccess();
    }, 15000);
  }
  
  // Check if business plan user was redirected from login/register
  if (route.query.businessPlanPending === 'true') {
    showBusinessPlanPending.value = true;
    // Get the email from localStorage if available
    businessPlanEmail.value = localStorage.getItem('businessPlanEmail') || '';
  }
  
  // Also check localStorage for business plan pending status
  const businessPlanRequested = localStorage.getItem('businessPlanRequested');
  if (businessPlanRequested === 'true') {
    showBusinessPlanPending.value = true;
    businessPlanEmail.value = localStorage.getItem('businessPlanEmail') || '';
  }

  const authStore = useAuthStore();
  if (authStore.isAuthenticated) {
    // Don't redirect if coming from business request success
    if (route.query.businessRequest === 'success') {
      return;
    }
    if (authStore.isAdmin()) {
      navigateTo("/AdminManagement/");
    } else {
      navigateTo("/UserDashboard/");
    }
  }
});

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
  document.removeEventListener("keydown", handleEscKey);
  if (revealObserver) {
    revealObserver.disconnect();
    revealObserver = null;
  }
  if (heroFlipTimer) {
    window.clearInterval(heroFlipTimer);
    heroFlipTimer = null;
  }
});
// Feature Auto-Slide Logic (single seamless row)
const featureTopCurrentSlide = ref(0);
const featureTopIsTransitioning = ref(true);
const featureMobileMode = ref(false);
let featureSlideInterval;

const featuresList = [
  {
    title: 'Works Instantly',
    description: 'No app needed',
    icon: 'heroicons:bolt',
    bgClass: 'bg-gradient-to-br from-primary-100 to-primary-200',
    textClass: 'text-primary-600'
  },
  {
    title: 'Customizable Profiles',
    description: 'Customizable and ready template for content and card',
    icon: 'heroicons:paint-brush',
    bgClass: 'bg-gradient-to-br from-[#e4efed] to-[#cfe2df]',
    textClass: 'text-[#4b7a75]'
  },
  {
    title: 'Seamless Linking',
    description: 'Link to WhatsApp, LinkedIn, website, and other social media apps',
    icon: 'heroicons:wifi',
    bgClass: 'bg-gradient-to-br from-success-100 to-success-200',
    textClass: 'text-success-600'
  },
  {
    title: 'Analytics',
    description: 'Track taps and engagement',
    icon: 'heroicons:chart-bar',
    bgClass: 'bg-gradient-to-br from-primary-100 to-primary-200',
    textClass: 'text-primary-600'
  },
  {
    title: 'Centralized Connection Management',
    description: 'Organize every connection in one dashboard. Add notes, set reminders, and never lose a lead.',
    icon: 'heroicons:users',
    bgClass: 'bg-gradient-to-br from-[#f3ebdd] to-[#e6d6bd]',
    textClass: 'text-[#a17e4f]'
  },
  {
    title: 'Eco-Friendly',
    description: 'Eliminate paper waste. One digital card lasts forever.',
    icon: 'heroicons:globe-americas',
    bgClass: 'bg-gradient-to-br from-[#e4efed] to-[#cfe2df]',
    textClass: 'text-[#4b7a75]'
  },
  {
    title: 'QR Code Backup',
    description: 'Built-in QR code for non-NFC devices. Universal compatibility guaranteed.',
    icon: 'heroicons:qr-code',
    bgClass: 'bg-gradient-to-br from-secondary-100 to-secondary-200',
    textClass: 'text-secondary-600'
  },
  {
    title: 'Privacy Controls',
    description: 'Choose what to share and when. Disable temporarily without replacing the card.',
    icon: 'heroicons:shield-check',
    bgClass: 'bg-gradient-to-br from-primary-100 to-primary-200',
    textClass: 'text-primary-600'
  },
  {
    title: 'Video Introduction',
    description: 'Add a personal video message. Memorable first impressions every time.',
    icon: 'heroicons:video-camera',
    bgClass: 'bg-gradient-to-br from-[#f3ebdd] to-[#e6d6bd]',
    textClass: 'text-[#a17e4f]'
  }
];

// Double the list so the horizontal loop is seamless
const featuresRow1 = computed(() => [...featuresList, ...featuresList]);

const startFeatureSlideShow = () => {
  // Start at the end of the first set and slide right for an endless loop
  featureTopCurrentSlide.value = featuresList.length;

  featureSlideInterval = setInterval(() => {
    featureTopIsTransitioning.value = true;
    featureTopCurrentSlide.value--;

    // When we reach 0 the row is identical to the start — snap back invisibly
    if (featureTopCurrentSlide.value <= 0) {
      setTimeout(() => {
        featureTopIsTransitioning.value = false;
        featureTopCurrentSlide.value = featuresList.length;
      }, 700); // Match CSS transition duration
    }
  }, 3500); // 3.5s per slide
};

const checkFeatureMobile = () => {
  featureMobileMode.value = window.innerWidth < 768;
};

onMounted(() => {
  startFeatureSlideShow();
  checkFeatureMobile();
  window.addEventListener('resize', checkFeatureMobile);
});

onUnmounted(() => {
  clearInterval(featureSlideInterval);
  window.removeEventListener('resize', checkFeatureMobile);
});
</script>

<style scoped>
@keyframes scroll-x {
  0% {
    transform: translateX(0);
  }
  100% {
    transform: translateX(-100%);
  }
}

.animate-scroll-x {
  display: flex;
  width: max-content;
  animation: scroll-x 30s linear infinite;
}

.animate-scroll-x:hover {
  animation-play-state: paused;
}

/* Contact Modal Animations */
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

/* Smooth transitions for modal elements */
.contact-modal-content {
  transition: all 0.3s ease-out;
}

/* ============================================================
   LANDING PAGE — PREMIUM DESIGN LAYER
   Hand-written, scoped to the public homepage only.
   Palette follows the app theme tokens (navy / teal / gold).
   ============================================================ */

/* ---- Display typography ---- */
.lp-display {
  font-size: clamp(2.35rem, 5vw, 4.1rem);
  line-height: 1.02;
  letter-spacing: -0.035em;
  font-weight: 800;
  color: var(--theme-secondary-900, #1c1a15);
}
.lp-grad {
  background-image: linear-gradient(
    100deg,
    var(--theme-primary-600, #3d496a),
    var(--theme-accent, #5d8c87) 58%,
    var(--theme-accent-secondary, #b8956a)
  );
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}
.lp-grad-accent {
  font-style: italic;
  background-image: linear-gradient(
    100deg,
    var(--theme-accent, #5d8c87),
    var(--theme-primary-600, #3d496a)
  );
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}
.lp-h2 {
  font-size: clamp(1.8rem, 3.4vw, 2.6rem);
  line-height: 1.08;
  letter-spacing: -0.025em;
  font-weight: 800;
  color: var(--theme-secondary-900, #1c1a15);
}
.lp-lead {
  font-size: 1.075rem;
  line-height: 1.65;
  color: var(--theme-secondary-600, #5c5548);
}

/* ---- Eyebrow / kicker chips ---- */
.lp-eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.42rem 0.9rem;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--theme-primary-700, #323b56);
  background: color-mix(in srgb, #ffffff 74%, var(--theme-primary-50, #f4f6fa));
  border: 1px solid color-mix(in srgb, var(--theme-primary-200, #c9d0de) 72%, transparent);
  backdrop-filter: blur(8px);
}
.lp-eyebrow-dot {
  width: 0.45rem;
  height: 0.45rem;
  border-radius: 999px;
  background: var(--theme-accent, #5d8c87);
  box-shadow: 0 0 0 4px color-mix(in srgb, var(--theme-accent, #5d8c87) 22%, transparent);
}
.lp-kicker {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--theme-accent, #5d8c87);
}
.lp-kicker::before {
  content: "";
  width: 1.75rem;
  height: 1.5px;
  border-radius: 2px;
  background: linear-gradient(90deg, var(--theme-accent, #5d8c87), transparent);
}

/* ---- Ambient hero background ---- */
.lp-hero-mesh {
  position: absolute;
  inset: -18% -12% auto -12%;
  height: 125%;
  pointer-events: none;
  background:
    radial-gradient(42rem 42rem at 12% 6%, color-mix(in srgb, var(--theme-accent, #5d8c87) 20%, transparent), transparent 62%),
    radial-gradient(36rem 36rem at 88% 10%, color-mix(in srgb, var(--theme-primary-400, #6f7ea0) 22%, transparent), transparent 60%),
    radial-gradient(34rem 34rem at 72% 92%, color-mix(in srgb, var(--theme-accent-secondary, #b8956a) 16%, transparent), transparent 62%);
  filter: blur(6px);
}
.lp-hero-grid {
  position: absolute;
  inset: 0;
  pointer-events: none;
  opacity: 0.45;
  background-image:
    linear-gradient(color-mix(in srgb, var(--theme-secondary-300, #cdc7b9) 32%, transparent) 1px, transparent 1px),
    linear-gradient(90deg, color-mix(in srgb, var(--theme-secondary-300, #cdc7b9) 32%, transparent) 1px, transparent 1px);
  background-size: 58px 58px;
  -webkit-mask-image: radial-gradient(62% 55% at 50% 28%, #000 0%, transparent 76%);
  mask-image: radial-gradient(62% 55% at 50% 28%, #000 0%, transparent 76%);
}

/* ---- Navigation ---- */
.lp-nav-link {
  position: relative;
  font-size: 0.9rem;
  font-weight: 500;
  color: var(--theme-secondary-600, #5c5548);
  transition: color 0.2s ease;
}
.lp-nav-link::after {
  content: "";
  position: absolute;
  left: 0;
  right: 0;
  bottom: -6px;
  height: 2px;
  border-radius: 2px;
  background: linear-gradient(90deg, var(--theme-primary-600, #3d496a), var(--theme-accent, #5d8c87));
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.28s cubic-bezier(0.22, 1, 0.36, 1);
}
.lp-nav-link:hover {
  color: var(--theme-primary-700, #323b56);
}
.lp-nav-link:hover::after {
  transform: scaleX(1);
}
/* ---- Hero product card ---- */
.lp-card-3d {
  transform: perspective(1000px) rotateX(7deg) rotateY(-11deg);
  transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
  will-change: transform;
}
.lp-card-3d:hover {
  transform: perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(-8px) scale(1.02);
}
.lp-glow {
  position: absolute;
  inset: -16% -12%;
  z-index: 0;
  border-radius: 999px;
  background: radial-gradient(50% 50% at 50% 50%, color-mix(in srgb, var(--theme-accent, #5d8c87) 32%, transparent), transparent 70%);
  filter: blur(30px);
}
.lp-mini-chip {
  position: absolute;
  top: 1.35rem;
  left: 50%;
  width: 2.1rem;
  height: 1.6rem;
  transform: translateX(-50%);
  border-radius: 22%;
  background: linear-gradient(160deg, #f4d79b 0%, #d9a94f 45%, #b8956a 100%);
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.6),
    inset 0 -1px 2px rgba(0, 0, 0, 0.35);
  opacity: 0.9;
}
.lp-mini-chip::before,
.lp-mini-chip::after {
  content: "";
  position: absolute;
  left: 18%;
  right: 18%;
  border-radius: 1px;
  background: rgba(0, 0, 0, 0.28);
}
.lp-mini-chip::before {
  top: 22%;
  height: 12%;
}
.lp-mini-chip::after {
  bottom: 18%;
  height: 34%;
}
.lp-pulse {
  animation: lpPulse 2.4s ease-in-out infinite;
}
@keyframes lpPulse {
  0%, 100% { opacity: 0.45; }
  50% { opacity: 1; }
}

/* ---- Product gallery: pure-CSS card mockups ---- */
.lp-mini-card {
  position: relative;
  width: 100%;
  aspect-ratio: 16 / 10;
  border-radius: 14px;
  padding: 1.1rem 1.25rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  overflow: hidden;
  transform: rotate(-2deg);
  box-shadow:
    0 22px 44px -24px rgba(35, 41, 59, 0.6),
    inset 0 1px 0 rgba(255, 255, 255, 0.3);
  transition: transform 0.5s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.5s ease;
}
.lp-mini-card::after {
  content: "";
  position: absolute;
  inset: 0;
  pointer-events: none;
  background: linear-gradient(115deg, transparent 38%, rgba(255, 255, 255, 0.4) 50%, transparent 62%);
  opacity: 0.55;
}
.group:hover .lp-mini-card {
  transform: rotate(0deg) translateY(-6px);
  box-shadow: 0 30px 55px -26px rgba(35, 41, 59, 0.7);
}
.lp-mini-card-top,
.lp-mini-card-bottom {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.lp-mini-card-bottom {
  align-items: flex-end;
}
.lp-mini-card-brand {
  font-size: 0.95rem;
  font-weight: 800;
  letter-spacing: -0.02em;
}
.lp-mini-card-chip {
  position: relative;
  z-index: 1;
  width: 2rem;
  height: 1.45rem;
  border-radius: 5px;
  background: linear-gradient(135deg, #fde68a, #d4a13c 45%, #b8860b);
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.35);
}
.lp-mini-card-chip::before {
  content: "";
  position: absolute;
  inset: 4px 7px;
  border-left: 1px solid rgba(0, 0, 0, 0.25);
  border-right: 1px solid rgba(0, 0, 0, 0.25);
}
.lp-mini-card-name {
  font-size: 0.9rem;
  font-weight: 700;
  line-height: 1.15;
}
.lp-mini-card-role {
  margin-top: 2px;
  font-size: 0.7rem;
}
.lp-mini-card-qr {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 1.9rem;
  height: 1.9rem;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.92);
  color: #1c1a15;
}
.lp-face-pvc {
  background: linear-gradient(135deg, #ffffff, #eef1f6 55%, #dde3ec);
}
.lp-face-metal {
  background:
    repeating-linear-gradient(112deg, rgba(255, 255, 255, 0.1) 0 2px, rgba(255, 255, 255, 0) 2px 4px),
    linear-gradient(135deg, #3a4152, #20252f 50%, #14181f);
}
.lp-face-bamboo {
  background:
    repeating-linear-gradient(95deg, rgba(120, 72, 32, 0.14) 0 3px, rgba(120, 72, 32, 0) 3px 7px),
    linear-gradient(135deg, #e9d3ad, #d8b483 55%, #c19a63);
}

/* ---- Product gallery: design presets ---- */
.lp-design {
  position: relative;
  aspect-ratio: 16 / 10;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 0.7rem 0.85rem;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 16px 30px -20px rgba(35, 41, 59, 0.55);
  transition: transform 0.45s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.45s ease;
}
.group:hover .lp-design {
  transform: translateY(-6px);
  box-shadow: 0 24px 40px -22px rgba(35, 41, 59, 0.65);
}
.lp-design-brand {
  font-size: 0.6rem;
  font-weight: 800;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  opacity: 0.85;
}
.lp-design-qr {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 1.1rem;
  height: 1.1rem;
  border-radius: 4px;
  background: rgba(255, 255, 255, 0.85);
  color: #1c1a15;
}
.lp-design-name {
  font-size: 0.72rem;
  font-weight: 700;
  line-height: 1.15;
}
.lp-design-role {
  font-size: 0.58rem;
  opacity: 0.75;
}
.lp-design-minimal { background: linear-gradient(135deg, #ffffff, #eef1f6); color: #1c1a15; }
.lp-design-creative { background: linear-gradient(135deg, #a78bfa, #f0abfc); color: #ffffff; }
.lp-design-professional { background: linear-gradient(135deg, #1e3a8a, #0f172a); color: #ffffff; }
.lp-design-bold { background: linear-gradient(135deg, #f97316, #dc2626); color: #ffffff; }
.lp-design-executive { background: linear-gradient(135deg, #111827, #374151); color: #ffffff; }
.lp-design-tech { background: linear-gradient(135deg, #312e81, #0ea5e9); color: #ffffff; }
.lp-design-finance { background: linear-gradient(135deg, #134e4a, #0f766e); color: #ffffff; }
.lp-design-healthcare { background: linear-gradient(135deg, #881337, #be185d); color: #ffffff; }

/* ---- Digital profile phone mockup ---- */
.lp-prof-bullet {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 999px;
  color: var(--theme-accent, #5d8c87);
  background: color-mix(in srgb, var(--theme-accent, #5d8c87) 12%, transparent);
  border: 1px solid color-mix(in srgb, var(--theme-accent, #5d8c87) 26%, transparent);
}
.lp-phone {
  position: relative;
  z-index: 1;
  width: min(300px, 82vw);
  padding: 0.65rem;
  border-radius: 2.4rem;
  background: linear-gradient(160deg, #2b3040, #14171f);
  box-shadow:
    0 40px 80px -34px rgba(20, 23, 31, 0.75),
    inset 0 0 0 1px rgba(255, 255, 255, 0.08);
}
.lp-phone-notch {
  position: absolute;
  top: 0.9rem;
  left: 50%;
  transform: translateX(-50%);
  width: 5.5rem;
  height: 1.1rem;
  border-radius: 999px;
  background: #0d0f14;
  z-index: 3;
}
.lp-phone-screen {
  border-radius: 1.9rem;
  background: #ffffff;
  padding: 1.5rem 1.1rem 1.1rem;
  overflow: hidden;
}
.lp-prof-hero {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 1.1rem 1rem 1.25rem;
  border-radius: 1.25rem;
  background:
    radial-gradient(120% 100% at 50% 0%, color-mix(in srgb, var(--theme-accent, #5d8c87) 26%, transparent), transparent 70%),
    linear-gradient(150deg, var(--theme-primary-800, #2a3248), var(--theme-primary-900, #23293b));
}
.lp-prof-avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 3.4rem;
  height: 3.4rem;
  border-radius: 999px;
  font-weight: 800;
  font-size: 1.15rem;
  color: #ffffff;
  background: linear-gradient(150deg, var(--theme-accent, #5d8c87), var(--theme-primary-600, #3d496a));
  box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.14);
}
.lp-prof-name {
  margin-top: 0.7rem;
  font-size: 1.02rem;
  font-weight: 700;
  color: #ffffff;
}
.lp-prof-role {
  margin-top: 0.15rem;
  font-size: 0.72rem;
  color: rgba(255, 255, 255, 0.62);
}
.lp-prof-socials {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.85rem;
}
.lp-prof-social {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 1.9rem;
  height: 1.9rem;
  border-radius: 999px;
  color: #ffffff;
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.16);
}
.lp-prof-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.95rem;
  width: 100%;
}
.lp-prof-action {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
  flex: 1;
  padding: 0.5rem 0.6rem;
  border-radius: 0.65rem;
  font-size: 0.7rem;
  font-weight: 600;
  color: #ffffff;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.16);
}
.lp-prof-action-primary {
  background: linear-gradient(130deg, var(--theme-accent, #5d8c87), var(--theme-primary-600, #3d496a));
  border-color: transparent;
}
.lp-prof-links {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 0.9rem;
}
.lp-prof-link {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.6rem 0.7rem;
  border-radius: 0.75rem;
  background: var(--theme-secondary-50, #fafaf7);
  border: 1px solid var(--theme-secondary-200, #e6e2d8);
}
.lp-prof-link-ico {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 1.7rem;
  height: 1.7rem;
  border-radius: 0.5rem;
  color: var(--theme-accent, #5d8c87);
  background: color-mix(in srgb, var(--theme-accent, #5d8c87) 12%, transparent);
}
.lp-prof-link-label {
  flex: 1;
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--theme-secondary-800, #2e2a24);
}

/* ---- Floating pills ---- */
.lp-float-pill {
  position: absolute;
  z-index: 3;
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.55rem 0.85rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--theme-secondary-800, #2e2a24);
  background: color-mix(in srgb, #ffffff 84%, var(--theme-secondary-50, #fafaf7));
  border: 1px solid color-mix(in srgb, var(--theme-secondary-200, #e6e2d8) 88%, transparent);
  box-shadow: 0 14px 34px -16px color-mix(in srgb, var(--theme-secondary-800, #2e2a24) 55%, transparent);
  backdrop-filter: blur(8px);
}
@keyframes lpFloat {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-11px); }
}
.lp-float-1 { top: 4%; left: -8%; animation: lpFloat 6.5s ease-in-out infinite; }
.lp-float-2 { bottom: 16%; right: -9%; animation: lpFloat 7.5s ease-in-out 0.6s infinite; }
.lp-float-3 { bottom: -5%; left: 4%; animation: lpFloat 8.5s ease-in-out 1.1s infinite; }

/* Hide floating pills on small screens to avoid clipping */
@media (max-width: 640px) {
  .lp-float-pill {
    display: none;
  }
}

/* ---- Trust ticks ---- */
.lp-tick {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
}

/* ---- Marquee band ---- */
.lp-marquee-mask {
  -webkit-mask-image: linear-gradient(90deg, transparent, #000 12%, #000 88%, transparent);
  mask-image: linear-gradient(90deg, transparent, #000 12%, #000 88%, transparent);
}
.lp-marquee {
  display: flex;
  width: max-content;
  animation: lpMarquee 36s linear infinite;
}
.lp-marquee:hover {
  animation-play-state: paused;
}
@keyframes lpMarquee {
  from { transform: translateX(0); }
  to { transform: translateX(-50%); }
}
.lp-marquee-item {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0 1.75rem;
  font-size: 0.82rem;
  font-weight: 600;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  white-space: nowrap;
  color: var(--theme-secondary-500, #7f7763);
}
.lp-marquee-item::after {
  content: "";
  width: 0.35rem;
  height: 0.35rem;
  border-radius: 999px;
  background: var(--theme-accent, #5d8c87);
  opacity: 0.65;
}

/* ---- Stat strip cell ---- */
.lp-stat {
  background: color-mix(in srgb, #ffffff 82%, var(--theme-secondary-50, #fafaf7));
  transition: background 0.3s ease;
}
.lp-stat:hover {
  background: #ffffff;
}

/* ---- Featured pricing ring ---- */
.lp-price-pop {
  position: relative;
}
.lp-price-pop::before {
  content: "";
  position: absolute;
  inset: -1.5px;
  border-radius: inherit;
  padding: 1.5px;
  background: linear-gradient(140deg, var(--theme-accent, #5d8c87), var(--theme-primary-500, #4f5d82) 50%, var(--theme-accent-secondary, #b8956a));
  -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
  -webkit-mask-composite: xor;
  mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
  mask-composite: exclude;
  pointer-events: none;
}

/* ---- Premium CTA ---- */
.lp-cta {
  position: relative;
  overflow: hidden;
  background:
    radial-gradient(58% 130% at 12% 0%, color-mix(in srgb, var(--theme-accent, #5d8c87) 58%, transparent), transparent 62%),
    linear-gradient(120deg, var(--theme-primary-700, #323b56), var(--theme-primary-900, #23293b));
}
.lp-cta-grid {
  position: absolute;
  inset: 0;
  pointer-events: none;
  opacity: 0.25;
  background-image:
    linear-gradient(rgba(255, 255, 255, 0.16) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255, 255, 255, 0.16) 1px, transparent 1px);
  background-size: 46px 46px;
  -webkit-mask-image: radial-gradient(70% 80% at 50% 0%, #000, transparent 78%);
  mask-image: radial-gradient(70% 80% at 50% 0%, #000, transparent 78%);
}

/* ---- Scroll reveal ---- */
[data-reveal] {
  opacity: 0;
  transform: translateY(28px);
  transition:
    opacity 0.75s cubic-bezier(0.22, 1, 0.36, 1),
    transform 0.75s cubic-bezier(0.22, 1, 0.36, 1);
}
[data-reveal].is-revealed {
  opacity: 1;
  transform: none;
}
@media (prefers-reduced-motion: reduce) {
  [data-reveal] {
    opacity: 1;
    transform: none;
    transition: none;
  }
  .lp-marquee,
  .lp-float-1,
  .lp-float-2,
  .lp-float-3,
  .lp-pulse {
    animation: none;
  }
}

/* ============================================================
   SHOWCASE SECTION LAYER — Ink & Brass primitives
   Additive only; reuses the theme tokens + ink/brass tokens
   defined in assets/css/main.css.
   ============================================================ */

/* ---- Nav (ink glass) ---- */
.nav-ink-link {
  position: relative;
  font-size: var(--text-body);
  font-weight: var(--weight-medium);
  line-height: var(--leading-compact);
  color: rgba(255, 255, 255, 0.78);
  transition: color 0.2s ease;
}
.nav-ink-link::after {
  content: "";
  position: absolute;
  left: 0;
  right: 0;
  bottom: -7px;
  height: 1px;
  background: var(--brass, #c2a068);
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.28s cubic-bezier(0.22, 1, 0.36, 1);
}
.nav-ink-link:hover {
  color: #fff;
}
.nav-ink-link:hover::after {
  transform: scaleX(1);
}
.nav-ink-ghost {
  padding: 0.5rem 1rem;
  border-radius: 0.6rem;
  font-size: var(--text-body);
  font-weight: var(--weight-semibold);
  color: rgba(255, 255, 255, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.18);
  transition: all 0.2s ease;
}
.nav-ink-ghost:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.35);
  color: #fff;
}
.nav-m-ink-link {
  display: block;
  padding: 0.6rem 0.75rem;
  border-radius: 0.55rem;
  font-size: var(--text-body-lg);
  font-weight: var(--weight-medium);
  color: rgba(255, 255, 255, 0.85);
  transition: background 0.2s ease, color 0.2s ease;
}
.nav-m-ink-link:hover {
  background: rgba(255, 255, 255, 0.07);
  color: #fff;
}

/* ---- Hero product stage ---- */
.hero-stage {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  perspective: 1400px;
  padding: 1rem 0;
}
.hero-halo {
  position: absolute;
  inset: -6% -6%;
  z-index: 0;
  border-radius: 999px;
  background: radial-gradient(
    46% 46% at 50% 46%,
    color-mix(in srgb, var(--brass, #c2a068) 26%, transparent),
    transparent 70%
  );
  filter: blur(46px);
  pointer-events: none;
}
.hero-card-wrap {
  position: relative;
  z-index: 2;
  width: min(100%, 27rem);
  transform: rotate(-3deg);
  transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
}
.hero-card-wrap:hover {
  transform: rotate(0deg) translateY(-6px);
}
.hero-card {
  border-radius: 1.1rem;
  box-shadow:
    0 44px 74px -32px rgba(0, 0, 0, 0.82),
    0 0 0 1px rgba(255, 255, 255, 0.06);
}
.hero-chip {
  position: absolute;
  z-index: 3;
  font-size: 0.72rem;
  font-weight: 600;
}
.hero-chip-1 {
  top: 5%;
  left: -4%;
}
.hero-chip-2 {
  top: 45%;
  right: -6%;
}
.hero-chip-3 {
  bottom: 15%;
  left: -2%;
}
@media (max-width: 480px) {
  .hero-chip {
    display: none;
  }
}
.hero-ripple {
  z-index: 1;
  top: 50%;
  left: 50%;
  width: 20rem;
  height: 20rem;
  margin: -10rem 0 0 -10rem;
}
.hero-stat {
  transition: background 0.3s ease;
}
.hero-stat:hover {
  background: rgba(255, 255, 255, 0.06);
}
.hero-stat__num {
  font-size: clamp(1.5rem, 3vw, 2.1rem);
  font-weight: 800;
  letter-spacing: -0.03em;
  color: #fff;
}
.hero-stat__lbl {
  margin-top: 0.3rem;
  font-size: 0.78rem;
  letter-spacing: 0.04em;
  color: rgba(255, 255, 255, 0.55);
}

/* ---- Trust marquee (on ink) ---- */
.marquee-item-ink {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0 1.75rem;
  font-size: 0.82rem;
  font-weight: 600;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  white-space: nowrap;
  color: rgba(255, 255, 255, 0.6);
}
.marquee-item-ink::after {
  content: "";
  width: 0.35rem;
  height: 0.35rem;
  border-radius: 999px;
  background: var(--brass, #c2a068);
  opacity: 0.7;
}

/* ---- Feature card (paper) ---- */
.feature-card {
  position: relative;
  height: 100%;
  background: #fff;
  border: 1px solid color-mix(in srgb, var(--theme-secondary-300, #cdc7b9) 55%, transparent);
  border-radius: 1.1rem;
  padding: 1.75rem 1.6rem;
  transition:
    transform 0.35s cubic-bezier(0.22, 1, 0.36, 1),
    box-shadow 0.35s ease,
    border-color 0.35s ease;
}
.feature-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 26px 50px -30px rgba(20, 29, 43, 0.5);
  border-color: color-mix(in srgb, var(--brass, #c2a068) 50%, transparent);
}
.feature-card__ico {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.9rem;
  height: 2.9rem;
  border-radius: 0.85rem;
  margin-bottom: 1.1rem;
  color: #1b1407;
  background: linear-gradient(140deg, var(--brass-200, #e8d9b8), var(--brass, #c2a068));
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5);
}
.feature-card__title {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--theme-secondary-900, #1c1a15);
  margin-bottom: 0.5rem;
}
.feature-card__desc {
  font-size: 0.94rem;
  line-height: 1.6;
  color: var(--theme-secondary-600, #5c5548);
}

/* ---- Step (on ink) ---- */
.step-ink {
  position: relative;
  border-radius: 1.1rem;
  padding: 1.5rem 1.4rem;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(6px);
  transition:
    transform 0.35s cubic-bezier(0.22, 1, 0.36, 1),
    border-color 0.35s ease,
    background 0.35s ease;
}
.step-ink:hover {
  transform: translateY(-6px);
  border-color: color-mix(in srgb, var(--brass, #c2a068) 45%, transparent);
  background: rgba(255, 255, 255, 0.06);
}
.step-ink__top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.25rem;
}
.step-ink__num {
  font-size: 1.9rem;
  font-weight: 800;
  letter-spacing: -0.04em;
  color: rgba(255, 255, 255, 0.18);
}
.step-ink__ico {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.6rem;
  height: 2.6rem;
  border-radius: 0.75rem;
  color: var(--brass, #c2a068);
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
}
.step-ink__title {
  font-size: 1.05rem;
  font-weight: 700;
  color: #fff;
  margin-bottom: 0.5rem;
}
.step-ink__desc {
  font-size: 0.9rem;
  line-height: 1.6;
  color: rgba(255, 255, 255, 0.6);
}

/* ---- Use card (paper, who uses) ---- */
.use-card {
  position: relative;
  background: #fff;
  border: 1px solid color-mix(in srgb, var(--theme-secondary-200, #e6e2d8) 80%, transparent);
  border-radius: 1.1rem;
  padding: 2rem;
  transition:
    transform 0.35s cubic-bezier(0.22, 1, 0.36, 1),
    box-shadow 0.35s ease,
    border-color 0.35s ease;
}
.use-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 26px 50px -30px rgba(20, 29, 43, 0.45);
  border-color: color-mix(in srgb, var(--brass, #c2a068) 45%, transparent);
}

/* ---- Gallery sub-heading (on ink) ---- */
.gallery-sub {
  font-size: 1.35rem;
  font-weight: 700;
  letter-spacing: -0.01em;
  color: #fff;
  margin-bottom: 1.75rem;
}

/* ---- Material showcase ---- */
.material-item {
  display: flex;
  flex-direction: column;
}
.material-stage {
  perspective: 1200px;
}
.material-item:hover .material-card {
  transform: rotateY(180deg) translateY(-4px);
}
.material-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.2rem 0.6rem;
  border-radius: 999px;
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  white-space: nowrap;
  border: 1px solid currentColor;
  background: color-mix(in srgb, currentColor 12%, transparent);
  opacity: 0.92;
}

/* ---- Why card (paper) ---- */
.why-card {
  background: #fff;
  border: 1px solid color-mix(in srgb, var(--theme-secondary-200, #e6e2d8) 80%, transparent);
  border-radius: 1.1rem;
  padding: 1.75rem;
  transition:
    transform 0.35s cubic-bezier(0.22, 1, 0.36, 1),
    box-shadow 0.35s ease,
    border-color 0.35s ease;
}
.why-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 26px 50px -30px rgba(20, 29, 43, 0.45);
  border-color: color-mix(in srgb, var(--brass, #c2a068) 45%, transparent);
}

/* ---- Price card (on ink) ---- */
.price-card {
  position: relative;
  display: flex;
  flex-direction: column;
  border-radius: 1.25rem;
  padding: 1.9rem 1.6rem;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(6px);
  transition:
    transform 0.35s cubic-bezier(0.22, 1, 0.36, 1),
    border-color 0.35s ease,
    background 0.35s ease;
}
.price-card:hover {
  transform: translateY(-6px);
  border-color: color-mix(in srgb, var(--brass, #c2a068) 40%, transparent);
  background: rgba(255, 255, 255, 0.06);
}
.price-card--pop {
  background: linear-gradient(
    170deg,
    color-mix(in srgb, var(--brass, #c2a068) 14%, transparent),
    rgba(255, 255, 255, 0.03) 55%
  );
  border-color: color-mix(in srgb, var(--brass, #c2a068) 55%, transparent);
  box-shadow: 0 30px 60px -30px rgba(0, 0, 0, 0.75);
}
.price-card__badge {
  position: absolute;
  top: -0.7rem;
  left: 50%;
  transform: translateX(-50%);
  padding: 0.25rem 0.8rem;
  border-radius: 999px;
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  white-space: nowrap;
  color: #1b1407;
  background: linear-gradient(120deg, var(--brass-200, #e8d9b8), var(--brass, #c2a068));
}
.price-card__name {
  font-size: 1rem;
  font-weight: 700;
  letter-spacing: 0.02em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.7);
}
.price-card__amount {
  margin: 0.6rem 0 0.35rem;
  font-size: 2.1rem;
  font-weight: 800;
  letter-spacing: -0.03em;
  color: #fff;
}
.price-card__desc {
  font-size: 0.86rem;
  color: rgba(255, 255, 255, 0.55);
  margin-bottom: 1.4rem;
}
.price-card__list {
  display: flex;
  flex-direction: column;
  gap: 0.7rem;
  margin-bottom: 1.75rem;
  flex: 1;
}
.price-card__list li {
  display: flex;
  align-items: flex-start;
  gap: 0.6rem;
  font-size: 0.88rem;
  line-height: 1.5;
  color: rgba(255, 255, 255, 0.78);
}
.price-card__tick {
  flex-shrink: 0;
  margin-top: 0.15rem;
  color: var(--brass, #c2a068);
}
.price-card__cta {
  margin-top: auto;
}

/* ---- FAQ accordion (paper) ---- */
.faq-list {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}
.faq-item {
  border-radius: 1rem;
  background: #fff;
  border: 1px solid color-mix(in srgb, var(--theme-secondary-200, #e6e2d8) 85%, transparent);
  overflow: hidden;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}
.faq-item--open {
  border-color: color-mix(in srgb, var(--brass, #c2a068) 50%, transparent);
  box-shadow: 0 22px 44px -32px rgba(20, 29, 43, 0.5);
}
.faq-item__head {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  text-align: left;
  padding: 1.15rem 1.4rem;
}
.faq-item__q {
  font-size: 1.02rem;
  font-weight: 600;
  color: var(--theme-secondary-900, #1c1a15);
}
.faq-item__icon {
  flex-shrink: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 1.9rem;
  height: 1.9rem;
  border-radius: 999px;
  color: #1b1407;
  background: linear-gradient(140deg, var(--brass-200, #e8d9b8), var(--brass, #c2a068));
  transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
}
.faq-item--open .faq-item__icon {
  transform: rotate(135deg);
}
.faq-item__body {
  padding: 0 1.4rem 1.3rem;
  font-size: 0.94rem;
  line-height: 1.65;
  color: var(--theme-secondary-600, #5c5548);
}

/* ---- Shipping track (paper) ---- */
.ship-track {
  position: relative;
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.25rem;
}
.ship-step {
  position: relative;
  padding: 1.6rem 1.4rem;
  border-radius: 1.1rem;
  background: #fff;
  border: 1px solid color-mix(in srgb, var(--theme-secondary-200, #e6e2d8) 85%, transparent);
  transition:
    transform 0.35s cubic-bezier(0.22, 1, 0.36, 1),
    box-shadow 0.35s ease,
    border-color 0.35s ease;
}
.ship-step:hover {
  transform: translateY(-6px);
  box-shadow: 0 26px 50px -30px rgba(20, 29, 43, 0.45);
  border-color: color-mix(in srgb, var(--brass, #c2a068) 45%, transparent);
}
.ship-step__marker {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 3rem;
  height: 3rem;
  border-radius: 0.9rem;
  margin-bottom: 1rem;
  color: #1b1407;
  background: linear-gradient(140deg, var(--brass-200, #e8d9b8), var(--brass, #c2a068));
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5);
}
.ship-step__index {
  display: block;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.22em;
  color: var(--brass-600, #a9854d);
  margin-bottom: 0.35rem;
}
.ship-step__title {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--theme-secondary-900, #1c1a15);
  margin-bottom: 0.4rem;
}
.ship-step__desc {
  font-size: 0.9rem;
  line-height: 1.6;
  color: var(--theme-secondary-600, #5c5548);
}
@media (min-width: 768px) {
  .ship-track {
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
  }
  .ship-track::before {
    content: "";
    position: absolute;
    top: 2.6rem;
    left: 8%;
    right: 8%;
    height: 1px;
    background: linear-gradient(
      90deg,
      transparent,
      color-mix(in srgb, var(--brass, #c2a068) 45%, transparent),
      transparent
    );
  }
  .ship-step__marker {
    position: relative;
    z-index: 1;
  }
}

/* ---- Footer (ink) ---- */
.footer-ink {
  position: relative;
  padding: 3.5rem 0 2rem;
  color: #fff;
  background: var(--ink-950, #06090d);
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}
.footer-ink__head {
  font-size: 0.8rem;
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--brass, #c2a068);
  margin-bottom: 1rem;
}
.footer-ink__link {
  color: rgba(255, 255, 255, 0.55);
  transition: color 0.2s ease;
}
.footer-ink__link:hover {
  color: #fff;
}
.footer-ink__bottom {
  margin-top: 2.5rem;
  padding-top: 1.75rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  text-align: center;
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.4);
}

/* ---- Outline button for paper surfaces ---- */
.btn-outline-ink {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  border-radius: 0.65rem;
  font-weight: 600;
  color: var(--theme-secondary-900, #1c1a15);
  border: 1px solid color-mix(in srgb, var(--theme-secondary-900, #1c1a15) 25%, transparent);
  background: transparent;
  transition: all 0.2s ease;
}
.btn-outline-ink:hover {
  background: var(--theme-secondary-900, #1c1a15);
  border-color: var(--theme-secondary-900, #1c1a15);
  color: #fff;
}

@media (prefers-reduced-motion: reduce) {
  .hero-card-wrap,
  .material-card,
  .faq-item__icon {
    transition: none;
  }
}
</style>
