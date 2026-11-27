<!-- pages/Homepage/index.vue -->
<template>
  <div>
    <!-- Hero Section (ink, product-led) -->
    <section
      class="relative overflow-hidden ink-surface ink-grid pt-20 pb-0 lg:pt-28"
    >
      <div class="container relative">
        <div class="lg:grid lg:grid-cols-[1.05fr_0.95fr] lg:gap-16 items-center">
          <div class="mb-16 lg:mb-0">
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
              <NuxtLink
                to="/Homepage/how-it-works"
                class="btn-ghost-light btn-lg"
              >
                <Icon name="heroicons:play" class="h-5 w-5 mr-2" />
                See How It Works
              </NuxtLink>
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
              <Icon
                name="heroicons:bolt"
                class="h-4 w-4 text-[color:var(--brass)]"
              />
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
          class="mt-10 lg:mt-14 grid grid-cols-2 lg:grid-cols-4 overflow-hidden rounded-2xl border border-white/12 bg-white/5 backdrop-blur-sm"
        >
          <div
            class="hero-stat p-6 text-center border-b border-r border-white/10 lg:border-b-0"
          >
            <div class="hero-stat__num">1</div>
            <div class="hero-stat__lbl">Tap to share</div>
          </div>
          <div
            class="hero-stat p-6 text-center border-b border-white/10 lg:border-b-0 lg:border-r"
          >
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

      <!-- Trust marquee band — sits inside the hero, pinned to its bottom edge -->
      <div
        class="mt-8 ink-surface-2 lp-marquee-mask overflow-hidden border-y border-white/10 py-5"
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
              <NuxtLink to="/Homepage/cards" class="btn-outline-ink btn-lg">
                Explore card designs
              </NuxtLink>
            </div>
          </div>

          <!-- Phone mockup (design demonstration) -->
          <div class="relative mt-14 lg:mt-0" data-reveal>
            <div class="lp-glow"></div>
            <div class="lp-phone mx-auto">
              <div class="lp-phone-notch"></div>
              <div class="lp-phone-screen">
                <!-- Default NFCGo landing page (dark theme) as a real user sees it -->
                <div class="lp-prof-page">
                  <!-- Hero -->
                  <div class="lp-prof-hero">
                    <!-- NFCGo watermark (hidden once "Remove branding" is on) -->
                    <div class="lp-prof-brand">
                      <NFCGoLogo variant="light" size="xs" />
                    </div>
                    <div class="lp-prof-avatar">
                      <span>YN</span>
                      <span class="lp-prof-status"></span>
                    </div>
                    <div class="lp-prof-name">Your Name</div>
                    <span class="lp-prof-chip">
                      <Icon name="heroicons:briefcase" class="h-3 w-3" />
                      Product Designer
                    </span>
                    <p class="lp-prof-tagline">
                      "Tap. Connect. Remember me."
                    </p>
                  </div>

                  <!-- Quick actions -->
                  <div class="lp-prof-actions">
                    <span
                      v-for="action in quickActions"
                      :key="action.label"
                      class="lp-prof-action"
                    >
                      <span class="lp-prof-action-ico">
                        <Icon :name="action.icon" class="h-4 w-4" />
                      </span>
                      <span class="lp-prof-action-label">{{
                        action.label
                      }}</span>
                    </span>
                  </div>

                  <!-- About Me -->
                  <div class="lp-prof-card">
                    <div class="lp-prof-card-head">
                      <span class="lp-prof-card-ico">
                        <Icon name="heroicons:user" class="h-3.5 w-3.5" />
                      </span>
                      <h3 class="lp-prof-card-title">About Me</h3>
                    </div>
                    <p class="lp-prof-text">
                      I design digital products that make everyday
                      interactions simpler — and I love meeting people who
                      care about the same things.
                    </p>
                  </div>

                  <!-- Get In Touch -->
                  <div class="lp-prof-card">
                    <div class="lp-prof-card-head">
                      <span class="lp-prof-card-ico">
                        <Icon name="heroicons:phone" class="h-3.5 w-3.5" />
                      </span>
                      <h3 class="lp-prof-card-title">Get In Touch</h3>
                    </div>
                    <div class="lp-prof-rows">
                      <div
                        v-for="row in contactRows"
                        :key="row.label"
                        class="lp-prof-row"
                      >
                        <span class="lp-prof-row-ico">
                          <Icon :name="row.icon" class="h-3.5 w-3.5" />
                        </span>
                        <span class="lp-prof-row-body">
                          <span class="lp-prof-row-label">{{
                            row.label
                          }}</span>
                          <span class="lp-prof-row-value">{{
                            row.value
                          }}</span>
                        </span>
                        <span class="lp-prof-row-arrow">→</span>
                      </div>
                    </div>
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
          <div class="use-card group" data-reveal>
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

          <div class="use-card group" data-reveal>
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

          <div class="use-card group" data-reveal>
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

          <div class="use-card group" data-reveal>
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

          <div class="use-card group" data-reveal>
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
          <div class="use-card group" data-reveal>
            <div
              class="w-16 h-16 bg-gradient-to-br from-[#e4efed] to-[#cfe2df] rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300"
            >
              <Icon
                name="heroicons:home-modern"
                class="h-8 w-8 text-[#4b7a75]"
              />
            </div>
            <h3 class="text-xl font-semibold text-secondary-900 mb-3">
              Real Estate Agents
            </h3>
            <p class="text-secondary-600 leading-relaxed mb-4">
              Close properties faster with instant access to listings, virtual
              tours, and mortgage calculators. Schedule showings on the spot.
            </p>
            <ul class="space-y-2 text-sm">
              <li class="flex items-center text-secondary-600">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2"
                />
                Property listing links
              </li>
              <li class="flex items-center text-secondary-600">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2"
                />
                Virtual tour integration
              </li>
              <li class="flex items-center text-secondary-600">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2"
                />
                Instant showing scheduler
              </li>
            </ul>
          </div>

          <!-- Consultants & Coaches -->
          <div class="use-card group" data-reveal>
            <div
              class="w-16 h-16 bg-gradient-to-br from-[#f3ebdd] to-[#e6d6bd] rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300"
            >
              <Icon
                name="heroicons:presentation-chart-line"
                class="h-8 w-8 text-[#a17e4f]"
              />
            </div>
            <h3 class="text-xl font-semibold text-secondary-900 mb-3">
              Consultants & Coaches
            </h3>
            <p class="text-secondary-600 leading-relaxed mb-4">
              Convert conversations into consultations with embedded booking
              links and testimonials. Share your methodology instantly.
            </p>
            <ul class="space-y-2 text-sm">
              <li class="flex items-center text-secondary-600">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2"
                />
                Discovery call scheduler
              </li>
              <li class="flex items-center text-secondary-600">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2"
                />
                Case study gallery
              </li>
              <li class="flex items-center text-secondary-600">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2"
                />
                Assessment forms
              </li>
            </ul>
          </div>

          <!-- Recruiters & HR -->
          <div class="use-card group" data-reveal>
            <div
              class="w-16 h-16 bg-gradient-to-br from-primary-100 to-primary-200 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300"
            >
              <Icon name="heroicons:user-group" class="h-8 w-8 text-primary-600" />
            </div>
            <h3 class="text-xl font-semibold text-secondary-900 mb-3">
              Recruiters & HR
            </h3>
            <p class="text-secondary-600 leading-relaxed mb-4">
              Capture candidate info instantly at job fairs. Share open
              positions and company culture videos on tap.
            </p>
            <ul class="space-y-2 text-sm">
              <li class="flex items-center text-secondary-600">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2"
                />
                Job board integration
              </li>
              <li class="flex items-center text-secondary-600">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2"
                />
                Application form links
              </li>
              <li class="flex items-center text-secondary-600">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2"
                />
                Company overview videos
              </li>
            </ul>
          </div>

          <!-- Financial Advisors -->
          <div class="use-card group" data-reveal>
            <div
              class="w-16 h-16 bg-gradient-to-br from-success-100 to-success-200 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300"
            >
              <Icon name="heroicons:banknotes" class="h-8 w-8 text-success-600" />
            </div>
            <h3 class="text-xl font-semibold text-secondary-900 mb-3">
              Financial Advisors
            </h3>
            <p class="text-secondary-600 leading-relaxed mb-4">
              Build trust with credentials, client testimonials, and secure
              meeting scheduling. Share market insights and resources.
            </p>
            <ul class="space-y-2 text-sm">
              <li class="flex items-center text-secondary-600">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2"
                />
                Compliance-friendly sharing
              </li>
              <li class="flex items-center text-secondary-600">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2"
                />
                Document portal links
              </li>
              <li class="flex items-center text-secondary-600">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2"
                />
                Educational content hub
              </li>
            </ul>
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
          <div class="why-card" data-reveal>
            <div
              class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center mb-4"
            >
              <Icon
                name="heroicons:user-circle"
                class="h-6 w-6 text-primary-600"
              />
            </div>
            <h3 class="text-lg font-semibold text-secondary-900 mb-2">
              Digital Profile
            </h3>
            <p class="text-secondary-600">
              Keep your contact details, links, and portfolio in one profile you
              can update at any time.
            </p>
          </div>

          <div class="why-card" data-reveal>
            <div
              class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center mb-4"
            >
              <Icon name="heroicons:bolt" class="h-6 w-6 text-primary-600" />
            </div>
            <h3 class="text-lg font-semibold text-secondary-900 mb-2">
              One-Tap Sharing
            </h3>
            <p class="text-secondary-600">
              Share your profile instantly by tapping an NFC card or scanning a
              QR code.
            </p>
          </div>

          <div class="why-card" data-reveal>
            <div
              class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center mb-4"
            >
              <Icon name="heroicons:chart-bar" class="h-6 w-6 text-primary-600" />
            </div>
            <h3 class="text-lg font-semibold text-secondary-900 mb-2">
              Real-Time Analytics
            </h3>
            <p class="text-secondary-600">
              See taps, views, and link clicks as they happen from your
              dashboard.
            </p>
          </div>

          <div class="why-card" data-reveal>
            <div
              class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center mb-4"
            >
              <Icon
                name="heroicons:rectangle-stack"
                class="h-6 w-6 text-primary-600"
              />
            </div>
            <h3 class="text-lg font-semibold text-secondary-900 mb-2">
              Multiple Cards Per Account
            </h3>
            <p class="text-secondary-600">
              Manage several cards and profiles from a single account as your
              needs grow.
            </p>
          </div>

          <div class="why-card" data-reveal>
            <div
              class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center mb-4"
            >
              <Icon
                name="heroicons:device-phone-mobile"
                class="h-6 w-6 text-primary-600"
              />
            </div>
            <h3 class="text-lg font-semibold text-secondary-900 mb-2">
              No App Required
            </h3>
            <p class="text-secondary-600">
              Your profile opens in the recipient's browser — nothing to install.
            </p>
          </div>

          <div class="why-card" data-reveal>
            <div
              class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center mb-4"
            >
              <Icon
                name="heroicons:paint-brush"
                class="h-6 w-6 text-primary-600"
              />
            </div>
            <h3 class="text-lg font-semibold text-secondary-900 mb-2">
              Customizable Templates
            </h3>
            <p class="text-secondary-600">
              Match your profile to your personal or company brand with
              ready-made templates.
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12">
          <div
            class="flex items-center justify-center gap-3 bg-[color:var(--paper-2)] rounded-xl p-5"
          >
            <Icon
              name="heroicons:check-circle"
              class="h-6 w-6 text-success-600 flex-shrink-0"
            />
            <span class="text-secondary-700 font-medium"
              >Works on iPhone and Android</span
            >
          </div>
          <div
            class="flex items-center justify-center gap-3 bg-[color:var(--paper-2)] rounded-xl p-5"
          >
            <Icon
              name="heroicons:check-circle"
              class="h-6 w-6 text-success-600 flex-shrink-0"
            />
            <span class="text-secondary-700 font-medium">No app required</span>
          </div>
          <div
            class="flex items-center justify-center gap-3 bg-[color:var(--paper-2)] rounded-xl p-5"
          >
            <Icon
              name="heroicons:check-circle"
              class="h-6 w-6 text-success-600 flex-shrink-0"
            />
            <span class="text-secondary-700 font-medium"
              >Card activation in minutes</span
            >
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
            <span class="ship-step__index">{{
              String(i + 1).padStart(2, '0')
            }}</span>
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
        <h2
          class="text-3xl md:text-5xl font-extrabold text-white mt-6 mb-4 tracking-tight"
          data-reveal
        >
          Ready to transform your networking?
        </h2>
        <p
          class="text-lg md:text-xl text-white/70 mb-8 max-w-2xl mx-auto"
          data-reveal
        >
          Create your smart business card in minutes — no app, no printing, no
          waste. Just tap and connect.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
          <NuxtLink to="/UserAccount/register" class="btn-brass btn-lg">
            <Icon name="heroicons:rocket-launch" class="h-5 w-5 mr-2" />
            Start Your Free Trial
          </NuxtLink>
          <NuxtLink to="/Homepage/how-it-works" class="btn-ghost-light btn-lg">
            <Icon name="heroicons:play" class="h-5 w-5 mr-2" />
            See How It Works
          </NuxtLink>
        </div>
        <div
          class="flex flex-wrap items-center justify-center gap-8 text-white/65"
        >
          <div class="flex items-center">
            <Icon name="heroicons:check-circle" class="h-5 w-5 mr-2" />
            <span class="text-sm">14-day free account</span>
          </div>
          <div class="flex items-center">
            <Icon name="heroicons:check-circle" class="h-5 w-5 mr-2" />
            <span class="text-sm">No credit card required</span>
          </div>
          <div class="flex items-center">
            <Icon name="heroicons:check-circle" class="h-5 w-5 mr-2" />
            <span class="text-sm">Upgrade anytime</span>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
definePageMeta({ layout: 'public', middleware: 'guest' });

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

// Scroll-reveal for the landing sections
useScrollReveal();

// Hero card 3D flip (front/back preview) — auto-cycles every 15s
const heroFlipped = ref(false);
const HERO_FLIP_INTERVAL = 15000;
let heroFlipTimer = null;

// Trust marquee labels
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

// Default landing page preview — mirrors the real /profile default template:
// quick actions (Call / Email / Chat / Website) and the contact rows.
const quickActions = [
  { label: "Call", icon: "heroicons:phone" },
  { label: "Email", icon: "heroicons:envelope" },
  { label: "Chat", icon: "heroicons:chat-bubble-left-right" },
  { label: "Website", icon: "heroicons:globe-alt" },
];

const contactRows = [
  {
    label: "Phone",
    value: "+60 12-345 6789",
    icon: "heroicons:phone",
  },
  {
    label: "Email",
    value: "you@yourcompany.com",
    icon: "heroicons:envelope",
  },
  {
    label: "Address",
    value: "Kuala Lumpur, Malaysia",
    icon: "heroicons:map-pin",
  },
];

// Order-to-first-tap shipping flow
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

onMounted(() => {
  // Auto-flip the hero card between front and back
  const reduceMotion =
    window.matchMedia &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  if (!reduceMotion) {
    heroFlipTimer = window.setInterval(() => {
      heroFlipped.value = !heroFlipped.value;
    }, HERO_FLIP_INTERVAL);
  }

  const route = useRoute();
  const authStore = useAuthStore();
  if (authStore.isAuthenticated) {
    // Don't redirect if coming from business request success
    if (route.query.businessRequest === 'success') {
      return;
    }
    if (authStore.isAdmin()) {
      navigateTo("/AdminManagement");
    } else {
      navigateTo("/UserDashboard");
    }
  }
});

onUnmounted(() => {
  if (heroFlipTimer) {
    window.clearInterval(heroFlipTimer);
    heroFlipTimer = null;
  }
});
</script>
