<!-- pages/index.vue -->
<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-50 to-secondary-100">
    <!-- Navigation - STICKY HEADER -->
    <nav
      class="fixed top-0 left-0 right-0 z-50 bg-white/10 backdrop-blur-md border-b border-white/20 shadow-sm"
    >
      <div class="container">
        <div class="flex items-center justify-between md:justify-start md:gap-8 h-16">
          <div class="flex items-center">
            <Icon
              name="heroicons:identification"
              class="h-8 w-8 text-primary-600"
            />
            <span class="ml-2 text-xl font-bold text-secondary-900">NFCGo</span>
          </div>
          <div class="hidden md:flex items-center space-x-8">
            <a
              href="#features"
              @click="smoothScroll('#features')"
              class="nav-link"
              >Features</a
            >
            <a
              href="#how-it-works"
              @click="smoothScroll('#how-it-works')"
              class="nav-link"
              >How it Works</a
            >
            <a
              href="#who-uses"
              @click="smoothScroll('#who-uses')"
              class="nav-link"
              >Who Uses</a
            >
            <a
              href="#product-gallery"
              @click="smoothScroll('#product-gallery')"
              class="nav-link"
              >Product Gallery</a
            >
            <a
              href="#testimonials"
              @click="smoothScroll('#testimonials')"
              class="nav-link"
              >Testimonials</a
            >
            <a
              href="#pricing"
              @click="smoothScroll('#pricing')"
              class="nav-link"
              >Pricing</a
            >
            <NuxtLink to="/UserAccount/login" class="btn btn-ghost"
              >Login</NuxtLink
            >
            <NuxtLink to="/UserAccount/register" class="btn btn-primary"
              >Get Started</NuxtLink
            >
            <!-- Theme Switcher -->
            <ThemeSwitcher />
          </div>
          <div class="md:hidden">
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="p-2 rounded-md text-secondary-600 hover:text-primary-600"
            >
              <Icon name="heroicons:bars-3" class="h-6 w-6" />
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Menu -->
      <div
        v-if="mobileMenuOpen"
        class="md:hidden bg-white/95 backdrop-blur-md border-t border-white/20"
      >
        <div class="px-2 pt-2 pb-3 space-y-1">
          <a
            href="#features"
            @click="smoothScroll('#features')"
            class="block px-3 py-2 nav-link"
            >Features</a
          >
          <a
            href="#how-it-works"
            @click="smoothScroll('#how-it-works')"
            class="block px-3 py-2 nav-link"
            >How it Works</a
          >
          <a
            href="#who-uses"
            @click="smoothScroll('#who-uses')"
            class="block px-3 py-2 nav-link"
            >Who Uses</a
          >
          <a
            href="#product-gallery"
            @click="smoothScroll('#product-gallery')"
            class="block px-3 py-2 nav-link"
            >Product Gallery</a
          >
          <a
            href="#testimonials"
            @click="smoothScroll('#testimonials')"
            class="block px-3 py-2 nav-link"
            >Testimonials</a
          >
          <a
            href="#pricing"
            @click="smoothScroll('#pricing')"
            class="block px-3 py-2 nav-link"
            >Pricing</a
          >
          <NuxtLink to="/UserAccount/login" class="block px-3 py-2 nav-link"
            >Login</NuxtLink
          >
          <NuxtLink
            to="/UserAccount/register"
            class="block px-3 py-2 text-primary-600 font-medium"
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
          <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-2xl shadow-lg p-6">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                  <Icon name="heroicons:check-circle" class="h-7 w-7 text-green-600" />
                </div>
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-green-800 mb-1">
                  Business Plan Request Submitted!
                </h3>
                <p class="text-green-700 mb-3">
                  Thank you for your interest in our Business plan. Our sales team will review your request and contact you within 1-2 business days via email or phone.
                </p>
                <div class="flex items-center gap-2 text-sm text-green-600">
                  <Icon name="heroicons:clock" class="h-4 w-4" />
                  <span>Expected response time: 1-2 business days</span>
                </div>
              </div>
              <button
                @click="dismissBusinessRequestSuccess"
                class="flex-shrink-0 p-1 text-green-500 hover:text-green-700 hover:bg-green-100 rounded-lg transition-colors"
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
          <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-2xl shadow-lg p-6">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                  <Icon name="heroicons:clock" class="h-7 w-7 text-amber-600" />
                </div>
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-amber-800 mb-1">
                  Business Plan - Account Pending
                </h3>
                <p class="text-amber-700 mb-3">
                  Your Business plan request has been submitted. Our team will create your account and contact you within 24 hours.
                  <strong>You cannot login or register</strong> until your account is created by our admin team.
                </p>
                <div v-if="businessPlanEmail" class="flex items-center gap-2 text-sm text-amber-600 mb-3">
                  <Icon name="heroicons:envelope" class="h-4 w-4" />
                  <span>We'll contact you at: {{ businessPlanEmail }}</span>
                </div>
                <div class="flex items-center gap-4">
                  <button
                    @click="showBusinessPlanPending = false"
                    class="text-sm text-amber-600 hover:text-amber-800 font-medium"
                  >
                    Dismiss
                  </button>
                  <button
                    @click="clearBusinessPlanRestriction"
                    class="text-sm text-amber-700 hover:text-amber-900 underline"
                  >
                    Clear & Login Anyway
                  </button>
                </div>
              </div>
              <button
                @click="showBusinessPlanPending = false"
                class="flex-shrink-0 p-1 text-amber-500 hover:text-amber-700 hover:bg-amber-100 rounded-lg transition-colors"
              >
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Hero Section -->
    <section class="section relative overflow-hidden pt-24">
      <div class="container">
        <div class="lg:grid lg:grid-cols-2 lg:gap-8 items-center">
          <div class="mb-8 lg:mb-0 animate-fade-in-up">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-secondary-900 mb-6 leading-tight">
              Your Digital Business Card,
              <span class="text-gradient">One Tap Away</span>
            </h1>
            <p class="text-xl text-secondary-600 mb-8 leading-relaxed">
              Create smart NFC business cards that instantly share your professional profile.
              Modern networking made simple with just a tap.
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
              <NuxtLink to="/UserAccount/register" class="btn btn-primary btn-lg">
                <Icon name="heroicons:rocket-launch" class="h-5 w-5 mr-2" />
                Start Free Trial
              </NuxtLink>
              <button @click="showDemo = true" class="btn btn-outline btn-lg">
                <Icon name="heroicons:play" class="h-5 w-5 mr-2" />
                Watch Demo
              </button>
            </div>
            <div class="mt-8 flex flex-wrap items-center gap-6">
              <div class="flex items-center">
                <Icon name="heroicons:check-circle" class="h-5 w-5 text-success-600 mr-2" />
                <span class="text-sm text-secondary-600">No app required</span>
              </div>
              <div class="flex items-center">
                <Icon name="heroicons:check-circle" class="h-5 w-5 text-success-600 mr-2" />
                <span class="text-sm text-secondary-600">Works on all phones</span>
              </div>
              <div class="flex items-center">
                <Icon name="heroicons:check-circle" class="h-5 w-5 text-success-600 mr-2" />
                <span class="text-sm text-secondary-600">Instant setup</span>
              </div>
            </div>
          </div>
          <div class="relative animate-fade-in">
            <div
                class="nfc-card w-64 h-40 mx-auto lg:w-80 lg:h-48 p-6 flex flex-col justify-between shadow-2xl transition-all duration-300 ease-out hover:scale-105 hover:-translate-y-1 hover:shadow-2xl bg-gradient-to-br from-cyan-100 via-purple-100 to-rose-100 border border-white/40 relative overflow-hidden">
                <!-- Iridescent/Holographic sheen overlay -->
                <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/40 to-transparent opacity-70 pointer-events-none mix-blend-overlay"></div>
                <!-- Shimmer effect -->
                <div class="absolute -inset-full top-0 block h-full w-1/2 -skew-x-12 bg-gradient-to-r from-transparent to-white opacity-30 group-hover:animate-shine" />
                
                <div class="flex items-start justify-between relative z-10">
                    <div class="flex flex-col">
                        <!-- Simulated Gold Logo - Retained as requested -->
                        <span class="text-3xl font-black tracking-tighter bg-gradient-to-b from-amber-200 via-yellow-400 to-amber-600 bg-clip-text text-transparent leading-none drop-shadow-sm">CLB</span>
                        <span class="text-[0.6rem] font-bold tracking-widest text-amber-600 uppercase mt-1">CLB GROUP</span>
                    </div>
                    <div>
                        <Icon name="heroicons:wifi" class="h-8 w-8 text-gray-500/50 animate-pulse" />
                    </div>
                </div>
                
                <!-- Spacer -->
                <div class="relative z-0"></div>

                <div class="relative z-10 flex justify-between items-end">
                    <div>
                        <h3 class="text-gray-900 text-xl font-bold tracking-wide">John Doe</h3>
                        <p class="text-gray-600 text-sm font-medium">Senior Developer</p>
                    </div>
                    <!-- QR Code -->
                    <div class="bg-white/80 p-1 rounded-sm backdrop-blur-sm">
                        <Icon name="heroicons:qr-code" class="h-8 w-8 text-gray-900" />
                    </div>
                </div>
            </div>
            <!-- Floating elements -->
            <div
                class="absolute -top-4 -right-4 w-24 h-24 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full opacity-20 animate-pulse-slow">
            </div>
            <div
                class="absolute -bottom-4 -left-4 w-16 h-16 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full opacity-20 animate-pulse-slow">
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="section bg-white">
      <div class="container">
        <div class="text-center mb-16">
          <h2
            class="text-3xl md:text-4xl font-bold text-secondary-900 mb-4 animate-fade-in-up"
          >
            Everything You Need for Modern Networking
          </h2>
          <p
            class="text-xl text-secondary-600 max-w-2xl mx-auto animate-fade-in-up"
          >
            Powerful features designed to make your professional networking
            effortless and memorable.
          </p>
        </div>

        <!-- Auto-Sliding Feature Rows -->
        <div class="space-y-8 overflow-hidden">
          <!-- Top Row -->
          <div class="relative">
            <div 
              class="flex transition-transform duration-700 ease-in-out"
              :class="{ 'duration-0': !featureTopIsTransitioning }"
              :style="{ transform: `translateX(-${featureTopCurrentSlide * (featureMobileMode ? 100 : 100/3)}%)` }"
            >
              <div
                v-for="(feature, index) in featuresRow1"
                :key="`row1-${index}`"
                class="flex-shrink-0 w-full md:w-1/3 px-4"
              >
                <div class="h-full bg-white rounded-2xl p-8 hover:shadow-xl transition-all duration-300 group border border-secondary-100/50">
                  <div
                    class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300"
                    :class="feature.bgClass"
                  >
                    <Icon :name="feature.icon" class="h-8 w-8" :class="feature.textClass" />
                  </div>
                  <h3 class="text-xl font-semibold text-secondary-900 mb-3">
                    {{ feature.title }}
                  </h3>
                  <p class="text-secondary-600 leading-relaxed">
                    {{ feature.description }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Bottom Row -->
          <div class="relative">
            <div 
              class="flex transition-transform duration-700 ease-in-out"
              :class="{ 'duration-0': !featureBottomIsTransitioning }"
              :style="{ transform: `translateX(-${featureBottomCurrentSlide * (featureMobileMode ? 100 : 100/3)}%)` }"
            >
              <div
                v-for="(feature, index) in featuresRow2"
                :key="`row2-${index}`"
                class="flex-shrink-0 w-full md:w-1/3 px-4"
              >
                <div class="h-full bg-white rounded-2xl p-8 hover:shadow-xl transition-all duration-300 group border border-secondary-100/50">
                  <div
                    class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300"
                    :class="feature.bgClass"
                  >
                    <Icon :name="feature.icon" class="h-8 w-8" :class="feature.textClass" />
                  </div>
                  <h3 class="text-xl font-semibold text-secondary-900 mb-3">
                    {{ feature.title }}
                  </h3>
                  <p class="text-secondary-600 leading-relaxed">
                    {{ feature.description }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="section bg-secondary-50">
      <div class="container">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-secondary-900 mb-4">
            How It Works
          </h2>
          <p class="text-xl text-secondary-600 max-w-2xl mx-auto">
            Get started in minutes with our simple 4-step process.
          </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div
            v-for="(step, index) in steps"
            :key="step.title"
            class="text-center"
          >
            <div class="relative mb-6">
              <a 
                href="https://youtu.be/PER3f8EIGjs" 
                target="_blank" 
                rel="noopener noreferrer" 
                class="block w-20 h-20 mx-auto relative z-10"
              >
                <div
                  class="w-full h-full bg-primary-600 rounded-full flex items-center justify-center shadow-lg transition-transform duration-300 cursor-pointer hover:scale-110 hover:shadow-xl"
                >
                  <span class="text-2xl font-bold text-white">{{
                    index + 1
                  }}</span>
                </div>
              </a>
              <div
                v-if="index < steps.length - 1"
                class="hidden md:block absolute top-10 left-1/2 w-full h-0.5 bg-primary-200 transform translate-x-10"
              ></div>
            </div>
            <h3 class="text-xl font-semibold text-secondary-900 mb-3">
              {{ step.title }}
            </h3>
          </div>
        </div>
      </div>
    </section>

    <!-- Who Uses Section -->
    <section id="who-uses" class="section bg-white">
      <div class="container">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-secondary-900 mb-4">
            Who Uses NFC Business Cards?
          </h2>
          <p class="text-xl text-secondary-600 max-w-2xl mx-auto">
            From entrepreneurs to business teams, discover how professionals
            across industries are revolutionizing their networking.
          </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            class="card p-8 hover:shadow-xl transition-all duration-300 group"
          >
            <div
              class="w-16 h-16 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300"
            >
              <Icon name="heroicons:briefcase" class="h-8 w-8 text-blue-600" />
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
            class="card p-8 hover:shadow-xl transition-all duration-300 group"
          >
            <div
              class="w-16 h-16 bg-gradient-to-br from-purple-100 to-purple-200 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300"
            >
              <Icon
                name="heroicons:rocket-launch"
                class="h-8 w-8 text-purple-600"
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
            class="card p-8 hover:shadow-xl transition-all duration-300 group"
          >
            <div
              class="w-16 h-16 bg-gradient-to-br from-green-100 to-green-200 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300"
            >
              <Icon
                name="heroicons:building-office"
                class="h-8 w-8 text-green-600"
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
            class="card p-8 hover:shadow-xl transition-all duration-300 group"
          >
            <div
              class="w-16 h-16 bg-gradient-to-br from-pink-100 to-pink-200 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300"
            >
              <Icon
                name="heroicons:paint-brush"
                class="h-8 w-8 text-pink-600"
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
            class="card p-8 hover:shadow-xl transition-all duration-300 group"
          >
            <div
              class="w-16 h-16 bg-gradient-to-br from-orange-100 to-orange-200 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300"
            >
              <Icon name="heroicons:users" class="h-8 w-8 text-orange-600" />
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
          <div class="card p-8 hover:shadow-xl transition-all duration-300 group">
            <div class="w-16 h-16 bg-gradient-to-br from-cyan-100 to-cyan-200 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
              <Icon name="heroicons:home-modern" class="h-8 w-8 text-cyan-600" />
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
          <div class="card p-8 hover:shadow-xl transition-all duration-300 group">
            <div class="w-16 h-16 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
              <Icon name="heroicons:presentation-chart-line" class="h-8 w-8 text-indigo-600" />
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
          <div class="card p-8 hover:shadow-xl transition-all duration-300 group">
            <div class="w-16 h-16 bg-gradient-to-br from-rose-100 to-rose-200 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
              <Icon name="heroicons:user-group" class="h-8 w-8 text-rose-600" />
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
          <div class="card p-8 hover:shadow-xl transition-all duration-300 group">
            <div class="w-16 h-16 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-full flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
              <Icon name="heroicons:banknotes" class="h-8 w-8 text-emerald-600" />
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

    <!-- Product Gallery Section -->
    <section
      id="product-gallery"
      class="section bg-gradient-to-br from-secondary-50 to-primary-50"
    >
      <div class="container">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-secondary-900 mb-4">
            Design Your Perfect Card
          </h2>
          <p class="text-xl text-secondary-600 max-w-2xl mx-auto">
            Choose from premium materials and stunning designs that reflect your
            personal brand.
          </p>
        </div>

        <div class="mb-16">
          <h3 class="text-2xl font-bold text-secondary-900 text-center mb-8">
            Premium Card Materials
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="group cursor-pointer">
              <div
                class="relative overflow-hidden rounded-2xl bg-white shadow-lg group-hover:shadow-2xl transition-all duration-300"
              >
                <div
                  class="aspect-[16/10] bg-gradient-to-br from-blue-400 to-blue-600 p-8 flex items-center justify-center"
                >
                  <div
                    class="bg-white/20 backdrop-blur-sm rounded-xl p-6 text-center"
                  >
                    <Icon
                      name="heroicons:credit-card"
                      class="h-16 w-16 text-white mx-auto mb-3"
                    />
                    <p class="text-white font-semibold">PVC Card</p>
                  </div>
                </div>
                <div class="p-6">
                  <h4 class="text-xl font-semibold text-secondary-900 mb-2">
                    Classic PVC
                  </h4>
                  <p class="text-secondary-600 mb-4">
                    Durable, affordable, and perfect for everyday professional
                    use.
                  </p>
                  <div class="flex items-center justify-between">
                    <span class="text-sm text-secondary-500"
                      >Starting from RM29</span
                    >
                    <span class="text-primary-600 font-medium"
                      >Most Popular</span
                    >
                  </div>
                </div>
              </div>
            </div>

            <div class="group cursor-pointer">
              <div
                class="relative overflow-hidden rounded-2xl bg-white shadow-lg group-hover:shadow-2xl transition-all duration-300"
              >
                <div
                  class="aspect-[16/10] bg-gradient-to-br from-gray-700 to-gray-900 p-8 flex items-center justify-center"
                >
                  <div
                    class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-center"
                  >
                    <Icon
                      name="heroicons:square-3-stack-3d"
                      class="h-16 w-16 text-gray-300 mx-auto mb-3"
                    />
                    <p class="text-gray-300 font-semibold">Metal Card</p>
                  </div>
                </div>
                <div class="p-6">
                  <h4 class="text-xl font-semibold text-secondary-900 mb-2">
                    Premium Metal
                  </h4>
                  <p class="text-secondary-600 mb-4">
                    Luxurious feel with laser engraving. Make an unforgettable
                    impression.
                  </p>
                  <div class="flex items-center justify-between">
                    <span class="text-sm text-secondary-500"
                      >Starting from RM89</span
                    >
                    <span class="text-yellow-600 font-medium">Premium</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="group cursor-pointer">
              <div
                class="relative overflow-hidden rounded-2xl bg-white shadow-lg group-hover:shadow-2xl transition-all duration-300"
              >
                <div
                  class="aspect-[16/10] bg-gradient-to-br from-yellow-600 to-yellow-800 p-8 flex items-center justify-center"
                >
                  <div
                    class="bg-white/20 backdrop-blur-sm rounded-xl p-6 text-center"
                  >
                    <Icon
                      name="heroicons:globe-asia-australia"
                      class="h-16 w-16 text-white mx-auto mb-3"
                    />
                    <p class="text-white font-semibold">Bamboo Card</p>
                  </div>
                </div>
                <div class="p-6">
                  <h4 class="text-xl font-semibold text-secondary-900 mb-2">
                    Eco Bamboo
                  </h4>
                  <p class="text-secondary-600 mb-4">
                    Sustainable choice with unique wood grain. Perfect for
                    eco-conscious brands.
                  </p>
                  <div class="flex items-center justify-between">
                    <span class="text-sm text-secondary-500"
                      >Starting from RM59</span
                    >
                    <span class="text-green-600 font-medium">Eco-Friendly</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mb-16">
          <h3 class="text-2xl font-bold text-secondary-900 text-center mb-8">
            Design Inspirations
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white rounded-2xl shadow-lg p-8">
              <h4 class="text-xl font-semibold text-secondary-900 mb-4">
                Personal Designs
              </h4>
              <div class="grid grid-cols-2 gap-4">
                <div
                  class="aspect-[16/10] bg-gradient-to-br from-purple-400 to-pink-400 rounded-lg flex items-center justify-center"
                >
                  <span class="text-white font-semibold">Minimalist</span>
                </div>
                <div
                  class="aspect-[16/10] bg-gradient-to-br from-blue-400 to-cyan-400 rounded-lg flex items-center justify-center"
                >
                  <span class="text-white font-semibold">Creative</span>
                </div>
                <div
                  class="aspect-[16/10] bg-gradient-to-br from-green-400 to-emerald-400 rounded-lg flex items-center justify-center"
                >
                  <span class="text-white font-semibold">Professional</span>
                </div>
                <div
                  class="aspect-[16/10] bg-gradient-to-br from-orange-400 to-red-400 rounded-lg flex items-center justify-center"
                >
                  <span class="text-white font-semibold">Bold</span>
                </div>
              </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-8">
              <h4 class="text-xl font-semibold text-secondary-900 mb-4">
                Corporate Designs
              </h4>
              <div class="grid grid-cols-2 gap-4">
                <div
                  class="aspect-[16/10] bg-gradient-to-br from-gray-700 to-gray-900 rounded-lg flex items-center justify-center"
                >
                  <span class="text-white font-semibold">Executive</span>
                </div>
                <div
                  class="aspect-[16/10] bg-gradient-to-br from-indigo-700 to-indigo-900 rounded-lg flex items-center justify-center"
                >
                  <span class="text-white font-semibold">Tech</span>
                </div>
                <div
                  class="aspect-[16/10] bg-gradient-to-br from-teal-700 to-teal-900 rounded-lg flex items-center justify-center"
                >
                  <span class="text-white font-semibold">Finance</span>
                </div>
                <div
                  class="aspect-[16/10] bg-gradient-to-br from-rose-700 to-rose-900 rounded-lg flex items-center justify-center"
                >
                  <span class="text-white font-semibold">Healthcare</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div
          class="bg-gradient-to-r from-primary-600 to-primary-500 rounded-2xl p-8 md:p-12 text-center"
        >
          <div class="max-w-2xl mx-auto">
            <div
              class="inline-flex items-center justify-center w-20 h-20 bg-white/20 rounded-full mb-6"
            >
              <Icon name="heroicons:cube" class="h-10 w-10 text-white" />
            </div>
            <h3 class="text-2xl md:text-3xl font-bold text-white mb-4">
              Interactive Card Builder Coming Soon
            </h3>
            <p class="text-xl text-primary-100 mb-6">
              Design your perfect NFC business card with our 3D configurator.
              Choose materials, colors, layouts, and see real-time previews.
            </p>
            <button
              class="btn bg-white text-primary-600 hover:bg-primary-50 btn-lg"
            >
              <Icon name="heroicons:bell" class="h-5 w-5 mr-2" />
              Get Notified When It's Ready
            </button>
          </div>
        </div>
      </div>
    </section>



    <!-- Testimonials Section - Auto-Sliding Carousel -->
    <section id="testimonials" class="section bg-white">
      <div class="container">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-secondary-900 mb-4">
            What Our Customers Say
          </h2>
          <p class="text-xl text-secondary-600 max-w-2xl mx-auto">
            Join thousands of professionals who have transformed their
            networking with NFCGo
          </p>
        </div>

        <!-- Auto-Sliding Carousel -->
        <div 
          class="relative mb-16"
          @mouseenter="pauseCarousel"
          @mouseleave="resumeCarousel"
        >
          <!-- Carousel Container with overflow hidden -->
          <div class="overflow-hidden">
            <!-- Slides Wrapper -->
            <div 
              class="flex"
              :class="[isTransitioning ? 'transition-transform duration-700 ease-in-out' : '']"
              :style="{ transform: `translateX(-${carouselPosition}%)` }"
            >
              <!-- Testimonial Cards -->
              <div
                v-for="(testimonial, index) in extendedTestimonials"
                :key="`testimonial-${index}`"
                class="flex-shrink-0 w-full md:w-1/2 lg:w-1/3 px-3"
              >
                <div class="card p-6 hover:shadow-xl transition-all duration-300 relative h-full bg-white rounded-xl">
                  <div class="absolute top-6 right-6 opacity-10">
                    <Icon
                      name="heroicons:chat-bubble-bottom-center-text"
                      class="h-16 w-16 text-primary-600"
                    />
                  </div>
                  <div class="flex items-center mb-4">
                    <Icon
                      v-for="i in 5"
                      :key="i"
                      name="heroicons:star"
                      class="h-5 w-5 text-yellow-500 fill-current"
                    />
                  </div>
                  <p class="text-secondary-600 mb-6 italic leading-relaxed min-h-[100px]">
                    "{{ testimonial.text }}"
                  </p>
                  <div class="flex items-center">
                    <img
                      :src="testimonial.avatar"
                      :alt="testimonial.name"
                      class="w-12 h-12 rounded-full object-cover mr-4"
                    />
                    <div>
                      <h4 class="font-semibold text-secondary-900">
                        {{ testimonial.name }}
                      </h4>
                      <p class="text-sm text-secondary-500">{{ testimonial.role }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Navigation Arrows -->
          <button
            @click="prevTestimonial"
            class="absolute left-0 md:-left-4 top-1/2 -translate-y-1/2 z-20 p-2 md:p-3 rounded-full bg-white shadow-lg hover:shadow-xl hover:bg-secondary-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-400"
            aria-label="Previous testimonial"
          >
            <Icon name="heroicons:chevron-left" class="h-5 w-5 md:h-6 md:w-6 text-secondary-600" />
          </button>
          <button
            @click="nextTestimonial"
            class="absolute right-0 md:-right-4 top-1/2 -translate-y-1/2 z-20 p-2 md:p-3 rounded-full bg-white shadow-lg hover:shadow-xl hover:bg-secondary-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-400"
            aria-label="Next testimonial"
          >
            <Icon name="heroicons:chevron-right" class="h-5 w-5 md:h-6 md:w-6 text-secondary-600" />
          </button>

          <!-- Navigation Dots -->
          <div class="flex justify-center mt-8 gap-2">
            <button
              v-for="(_, index) in testimonials"
              :key="`dot-${index}`"
              @click="goToTestimonial(index)"
              class="w-2.5 h-2.5 rounded-full transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-400 focus:ring-offset-2"
              :class="[
                currentTestimonial === index
                  ? 'bg-primary-600 w-8'
                  : 'bg-secondary-300 hover:bg-secondary-400'
              ]"
              :aria-label="`Go to testimonial ${index + 1}`"
            />
          </div>
        </div>

        <div
          class="bg-gradient-to-r from-secondary-50 to-primary-50 rounded-2xl p-8 md:p-12 overflow-hidden"
        >
          <h3 class="text-xl font-semibold text-secondary-900 text-center mb-8">
            Trusted by Leading Companies
          </h3>
          <div class="relative">
            <div
              class="absolute left-0 top-0 bottom-0 w-20 bg-gradient-to-r from-secondary-50 to-transparent z-10"
            ></div>
            <div
              class="absolute right-0 top-0 bottom-0 w-20 bg-gradient-to-l from-primary-50 to-transparent z-10"
            ></div>
            <div class="overflow-hidden">
              <div class="flex animate-scroll-x">
                <div class="flex items-center space-x-12 px-6">
                  <div
                    v-for="brand in brandLogos"
                    :key="`${brand.name}-1`"
                    class="flex-shrink-0 w-32 h-16 flex items-center justify-center"
                  >
                    <div
                      class="text-secondary-400 hover:text-secondary-600 transition-colors duration-300"
                    >
                      <Icon :name="brand.icon" class="h-8 w-auto" />
                      <span class="sr-only">{{ brand.name }}</span>
                    </div>
                  </div>
                </div>
                <div class="flex items-center space-x-12 px-6">
                  <div
                    v-for="brand in brandLogos"
                    :key="`${brand.name}-2`"
                    class="flex-shrink-0 w-32 h-16 flex items-center justify-center"
                  >
                    <div
                      class="text-secondary-400 hover:text-secondary-600 transition-colors duration-300"
                    >
                      <Icon :name="brand.icon" class="h-8 w-auto" />
                      <span class="sr-only">{{ brand.name }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mt-12">
            <div class="text-center">
              <p class="text-3xl font-bold text-primary-600 mb-1">10K+</p>
              <p class="text-sm text-secondary-600">Active Users</p>
            </div>
            <div class="text-center">
              <p class="text-3xl font-bold text-primary-600 mb-1">500+</p>
              <p class="text-sm text-secondary-600">Companies</p>
            </div>
            <div class="text-center">
              <p class="text-3xl font-bold text-primary-600 mb-1">98%</p>
              <p class="text-sm text-secondary-600">Satisfaction Rate</p>
            </div>
            <div class="text-center">
              <p class="text-3xl font-bold text-primary-600 mb-1">2M+</p>
              <p class="text-sm text-secondary-600">Cards Tapped</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="section bg-white">
      <div class="container">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-secondary-900 mb-4">
            Simple, Flexible Pricing
          </h2>
          <p class="text-xl text-secondary-600 max-w-2xl mx-auto">
            Choose the plan that fits your networking needs. Start free and
            upgrade as you grow.
          </p>
        </div>
        <div
          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto"
        >
          <div
            class="card p-6 text-center relative transition-all duration-300 bg-gradient-to-br from-green-50 to-emerald-50 border-green-200 hover:shadow-lg flex flex-col h-full"
          >
            <h3 class="text-xl font-bold text-secondary-900 mb-2">Free</h3>
            <div class="mb-6">
              <div class="text-3xl font-bold text-green-600 mb-2">Free</div>
              <p class="text-sm text-secondary-500">
                Perfect for getting started
              </p>
            </div>
            <ul class="space-y-2 mb-6 text-left text-sm">
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">1 Digital Profile</span>
              </li>
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">Basic Templates</span>
              </li>
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">Contact Information</span>
              </li>
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">Social Media Links</span>
              </li>
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">QR Code Backup</span>
              </li>
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">Basic Analytics</span>
              </li>
            </ul>
            <div class="mt-auto">
              <NuxtLink
                to="/UserAccount/register"
                class="btn w-full transition-all duration-300 bg-green-600 text-white hover:bg-green-700 shadow-lg hover:shadow-xl"
              >
                Get Started Free
              </NuxtLink>
            </div>
          </div>

          <div
            class="card p-6 text-center relative transition-all duration-300 hover:shadow-lg flex flex-col h-full"
          >
            <h3 class="text-xl font-bold text-secondary-900 mb-2">Basic</h3>
            <div class="mb-6">
              <div class="text-2xl font-bold text-secondary-900 mb-2">
                {{ loadingPrices ? 'Loading...' : (basicPlan?.currency + ' ' + basicPlan?.price) || 'Contact for Pricing' }}
              </div>
              <p class="text-sm text-secondary-500">
                {{ basicPlan?.description || 'For individual professionals' }}
              </p>
            </div>
            <ul class="space-y-2 mb-6 text-left text-sm">
              <li v-for="feature in basicPlan?.features" :key="feature" class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">{{ feature }}</span>
              </li>
            </ul>
            <div class="mt-auto">
              <NuxtLink
                to="/UserAccount/register"
                class="btn w-full transition-all duration-300 btn-outline hover:shadow-md"
              >
                Start Free Trial
              </NuxtLink>
            </div>
          </div>

          <div
            class="card p-6 text-center relative transition-all duration-300 hover:shadow-lg flex flex-col h-full"
          >
            <h3 class="text-xl font-bold text-secondary-900 mb-2">Premium</h3>
            <div class="mb-6">
              <div class="text-2xl font-bold text-secondary-900 mb-2">
                {{ loadingPrices ? 'Loading...' : (premiumPlan?.currency + ' ' + premiumPlan?.price) || 'Contact for Pricing' }}
              </div>
              <p class="text-sm text-secondary-500">{{ premiumPlan?.description || 'For growing businesses' }}</p>
            </div>
            <ul class="space-y-2 mb-6 text-left text-sm">
              <li v-for="feature in premiumPlan?.features" :key="feature" class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">{{ feature }}</span>
              </li>
            </ul>
            <div class="mt-auto">
              <NuxtLink
                to="/UserAccount/register"
                class="btn w-full transition-all duration-300 btn-outline shadow-lg hover:shadow-xl"
              >
                Start Free Trial
              </NuxtLink>
            </div>
          </div>

          <div
            class="card p-6 text-center relative transition-all duration-300 hover:shadow-lg flex flex-col h-full"
          >
            <h3 class="text-xl font-bold text-secondary-900 mb-2">Business</h3>
            <div class="mb-6">
              <div class="text-2xl font-bold text-secondary-900 mb-2">
                {{ loadingPrices ? 'Loading...' : (businessPlan?.currency + ' ' + businessPlan?.price) || 'Contact for Pricing' }}
              </div>
              <p class="text-sm text-secondary-500">{{ businessPlan?.description || 'For large organizations' }}</p>
            </div>
            <ul class="space-y-2 mb-6 text-left text-sm">
              <li v-for="feature in businessPlan?.features" :key="feature" class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">{{ feature }}</span>
              </li>
            </ul>
            <div class="mt-auto">
              <button
                @click="contactSupport"
                class="btn w-full transition-all duration-300 btn-outline hover:shadow-md"
              >
                Contact Sales
              </button>
            </div>
          </div>
        </div>

        <div class="text-center mt-12">
          <p class="text-secondary-600 mb-4">
            All paid plans include 14-day free trial • No credit card required •
            Cancel anytime
          </p>
          <div
            class="flex flex-col sm:flex-row gap-4 justify-center items-center"
          >
            <p class="text-sm text-secondary-500">Need a custom solution?</p>
            <button
              @click="contactSupport"
              class="text-primary-600 hover:text-primary-700 font-medium text-sm underline"
            >
              Contact our support team
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="section bg-gradient-to-r from-primary-600 to-primary-500">
      <div class="container text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
          Ready to Transform Your Networking?
        </h2>
        <p class="text-xl text-primary-100 mb-8 max-w-2xl mx-auto">
          Join thousands of professionals who are already networking smarter
          with NFC business cards.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-8">
          <NuxtLink
            to="/UserAccount/register"
            class="btn bg-white text-primary-600 hover:bg-primary-50 btn-lg shadow-lg hover:shadow-xl transition-all duration-300"
          >
            <Icon name="heroicons:rocket-launch" class="h-5 w-5 mr-2" />
            Start Your Free Trial
          </NuxtLink>
          <button
            @click="showDemo = true"
            class="btn btn-outline border-white text-white hover:bg-white hover:text-primary-600 btn-lg transition-all duration-300"
          >
            <Icon name="heroicons:play" class="h-5 w-5 mr-2" />
            Watch Demo
          </button>
        </div>
        <div
          class="flex flex-wrap items-center justify-center gap-8 text-primary-100"
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
    <footer class="bg-secondary-900 text-white py-12">
      <div class="container">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div>
            <div class="flex items-center mb-4">
              <Icon
                name="heroicons:identification"
                class="h-8 w-8 text-primary-400"
              />
              <span class="ml-2 text-xl font-bold">NFCGo</span>
            </div>
            <p class="text-secondary-400 mb-4">
              The future of professional networking is here. Connect smarter
              with NFC technology.
            </p>
            <div class="flex space-x-4">
              <a
                href="https://clbgroups.com/"
                target="_blank"
                rel="noopener noreferrer"
                class="text-secondary-400 hover:text-white transition-colors"
              >
                <Icon name="heroicons:globe-alt" class="h-5 w-5" />
              </a>
              <a
                href="#"
                class="text-secondary-400 hover:text-white transition-colors"
              >
                <Icon name="heroicons:envelope" class="h-5 w-5" />
              </a>
              <a
                href="https://www.facebook.com/profile.php?id=61568045614509"
                target="_blank"
                rel="noopener noreferrer"
                class="text-secondary-400 hover:text-white transition-colors"
              >
                <Icon name="bi:facebook" class="h-5 w-5" />
              </a>
            </div>
          </div>
          <div>
            <h4 class="font-semibold mb-4">Product</h4>
            <ul class="space-y-2">
              <li>
                <a
                  href="#features"
                  @click="smoothScroll('#features')"
                  class="text-secondary-400 hover:text-white transition-colors"
                  >Features</a
                >
              </li>
              <li>
                <a
                  href="#pricing"
                  @click="smoothScroll('#pricing')"
                  class="text-secondary-400 hover:text-white transition-colors"
                  >Pricing</a
                >
              </li>
              <li>
                <a
                  href="#"
                  class="text-secondary-400 hover:text-white transition-colors"
                  >Templates</a
                >
              </li>
              <li>
                <a
                  href="#"
                  class="text-secondary-400 hover:text-white transition-colors"
                  >Analytics</a
                >
              </li>
            </ul>
          </div>
          <div>
            <h4 class="font-semibold mb-4">Support</h4>
            <ul class="space-y-2">
              <li>
                <a
                  href="#"
                  class="text-secondary-400 hover:text-white transition-colors"
                  >Help Center</a
                >
              </li>
              <li>
                <a
                  href="#"
                  @click.prevent="openContactModal"
                  class="text-secondary-400 hover:text-white transition-colors cursor-pointer"
                  >Contact Us</a
                >
              </li>
              <li>
                <a
                  href="#"
                  class="text-secondary-400 hover:text-white transition-colors"
                  >API Docs</a
                >
              </li>
              <li>
                <a
                  href="#"
                  class="text-secondary-400 hover:text-white transition-colors"
                  >Status</a
                >
              </li>
            </ul>
          </div>
          <div>
            <h4 class="font-semibold mb-4">Company</h4>
            <ul class="space-y-2">
              <li>
                <a
                  href="https://clbgroups.com/"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="text-secondary-400 hover:text-white transition-colors"
                  >About</a
                >
              </li>
              <li>
                <a
                  href="#"
                  class="text-secondary-400 hover:text-white transition-colors"
                  >Blog</a
                >
              </li>
              <li>
                <button
                  @click="openLegalModal('privacy')"
                  class="text-secondary-400 hover:text-white transition-colors"
                  >Privacy</button
                >
              </li>
              <li>
                <button
                  @click="openLegalModal('terms')"
                  class="text-secondary-400 hover:text-white transition-colors"
                  >Terms</button
                >
              </li>
            </ul>
          </div>
        </div>
        <div class="border-t border-secondary-800 mt-12 pt-8 text-center">
          <p class="text-secondary-400">
            @Copyright CLB SDN BHD – 2025 -RFID Label Supplier , 
            RFID Solution and Stick Printing I All Rights Reserved
          </p>
        </div>
      </div>
    </footer>

    <!-- Demo Modal -->
    <div
      v-if="showDemo"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
    >
      <div class="bg-white rounded-2xl p-6 max-w-2xl w-full mx-4">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-2xl font-bold text-secondary-900">Product Demo</h3>
          <button
            @click="showDemo = false"
            class="p-2 hover:bg-secondary-100 rounded-lg transition-colors"
          >
            <Icon name="heroicons:x-mark" class="h-6 w-6 text-secondary-600" />
          </button>
        </div>
        <div
          class="aspect-video bg-secondary-100 rounded-lg flex items-center justify-center"
        >
          <div class="text-center">
            <Icon
              name="heroicons:play"
              class="h-16 w-16 text-secondary-400 mx-auto mb-4"
            />
            <p class="text-secondary-600">Demo video coming soon...</p>
            <p class="text-sm text-secondary-500 mt-2">
              Experience the future of networking
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Contact Us Modal -->
    <div
      v-if="showContactModal"
      class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
      @click.self="closeContactModal"
    >
      <div class="contact-modal-content bg-white rounded-2xl shadow-2xl w-full max-w-[700px] max-h-[90vh] overflow-hidden animate-modal-appear">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-primary-50 to-secondary-50">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Contact Us</h2>
            <p class="text-sm text-gray-600 mt-1">We'd love to hear from you!</p>
          </div>
          <button
            @click="closeContactModal"
            class="p-2 rounded-full hover:bg-white/80 transition-colors"
            aria-label="Close modal"
          >
            <Icon name="heroicons:x-mark" class="h-6 w-6 text-gray-600" />
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
                  class="p-1 hover:bg-green-100 rounded transition-colors"
                  title="Helpful"
                >
                  <Icon name="heroicons:hand-thumb-up" class="h-4 w-4 text-green-600" />
                </button>
                <button
                  @click="submitFeedback(message, false)"
                  class="p-1 hover:bg-red-100 rounded transition-colors"
                  title="Not helpful"
                >
                  <Icon name="heroicons:hand-thumb-down" class="h-4 w-4 text-red-600" />
                </button>
              </div>
              <div v-else-if="message.sender === 'ai' && message.feedbackGiven" class="ml-2 mt-1">
                <span class="text-xs text-green-600">✓ Thanks for your feedback!</span>
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
          class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 rounded-t-2xl"
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
                <p class="text-indigo-100 text-sm mt-1">
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
              class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
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
              class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
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
              class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
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
              class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
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
              class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
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
              class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
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
              class="w-full px-4 py-3 border border-secondary-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
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
              class="flex-1 py-3 px-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-medium hover:from-indigo-700 hover:to-purple-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
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
        <div class="flex items-center justify-between p-4 border-b border-gray-200 bg-gradient-to-r from-primary-50 to-secondary-50">
          <div class="flex-1 min-w-0">
            <h2 class="text-xl font-bold text-gray-900 truncate">
              {{ legalModalType === 'privacy' ? 'Privacy Policy' : 'Terms of Service' }}
            </h2>
            <div v-if="legalDocInfo && !loadingLegalDoc" class="flex items-center gap-3 mt-1 text-sm text-gray-600">
              <span class="truncate max-w-[200px]" :title="legalDocInfo.filename">
                <Icon name="heroicons:document" class="h-4 w-4 inline mr-1" />
                {{ legalDocInfo.filename }}
              </span>
              <span class="text-gray-400">•</span>
              <span>{{ formatFileSize(legalDocInfo.size) }}</span>
            </div>
          </div>
          <button
            @click="closeLegalModal"
            class="p-2 rounded-full hover:bg-white/80 transition-colors ml-4"
            aria-label="Close modal"
          >
            <Icon name="heroicons:x-mark" class="h-6 w-6 text-gray-600" />
          </button>
        </div>
        
        <!-- Modal Body -->
        <div class="flex-1 overflow-hidden relative">
          <!-- Loading State -->
          <div v-if="loadingLegalDoc" class="absolute inset-0 flex items-center justify-center bg-gray-50">
            <div class="text-center">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto mb-4"></div>
              <p class="text-gray-600">Loading document...</p>
            </div>
          </div>
          
          <!-- Error State -->
          <div v-else-if="legalDocError" class="absolute inset-0 flex items-center justify-center bg-gray-50 p-8">
            <div class="text-center">
              <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <Icon name="heroicons:exclamation-triangle" class="h-8 w-8 text-red-600" />
              </div>
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Document Not Available</h3>
              <p class="text-gray-600 mb-4">{{ legalDocError }}</p>
              <button
                @click="closeLegalModal"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
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
import { watch, onMounted, onBeforeUnmount, computed } from 'vue';

useHead({
  title: "NFCGo - Smart Digital Business Cards with NFC Technology",
  meta: [
    {
      name: "description",
      content:
        "Create smart NFC business cards that instantly share your professional profile. Modern networking made simple with just a tap.",
    },
    {
      name: "keywords",
      content:
        "NFC business card, digital business card, smart networking, professional networking, NFC technology",
    },
    { property: "og:title", content: "NFCGo - Smart Digital Business Cards" },
    {
      property: "og:description",
      content:
        "Create smart NFC business cards that instantly share your professional profile. Modern networking made simple with just a tap.",
    },
    { property: "og:type", content: "website" },
  ],
});

const authStore = useAuthStore();
const { $api, $toast } = useNuxtApp();
const mobileMenuOpen = ref(false);
const showDemo = ref(false);

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
  { title: "Ensure phone have NFC function" },
  { title: "Tap card on phone" },
  { title: "Digital profile instantly opens" },
  {
    title:
      "Save contact, view portfolio, connect on LinkedIn, interact with profile",
  },
];

const testimonials = [
  {
    id: 1,
    text: "NFCGo transformed how I network at conferences. I've made more meaningful connections in the last month than in the entire previous year. The analytics feature is a game-changer!",
    name: "Sarah Chen",
    role: "Marketing Director, TechCorp",
    avatar:
      "https://ui-avatars.com/api/?name=Sarah+Chen&background=6366f1&color=fff",
  },
  {
    id: 2,
    text: "As a freelance designer, NFCGo helps me showcase my portfolio instantly. Clients love the professional touch, and I've seen a 40% increase in follow-up rates.",
    name: "David Miller",
    role: "Creative Director, Studio M",
    avatar:
      "https://ui-avatars.com/api/?name=David+Miller&background=10b981&color=fff",
  },
  {
    id: 3,
    text: "Our sales team's productivity skyrocketed after switching to NFCGo. The lead capture feature alone paid for the investment within the first week!",
    name: "Jennifer Wong",
    role: "Sales Manager, GlobalTech",
    avatar:
      "https://ui-avatars.com/api/?name=Jennifer+Wong&background=f59e0b&color=fff",
  },
  {
    id: 4,
    text: "The bamboo cards perfectly align with our eco-friendly brand values. Clients are always impressed, and it starts conversations about sustainability.",
    name: "Michael Green",
    role: "CEO, EcoSolutions",
    avatar:
      "https://ui-avatars.com/api/?name=Michael+Green&background=059669&color=fff",
  },
  {
    id: 5,
    text: "Managing 50+ employee profiles is a breeze with NFCGo. The team UserDashboard gives us insights we never had before. Highly recommend for enterprises!",
    name: "Lisa Anderson",
    role: "HR Director, FinanceHub",
    avatar:
      "https://ui-avatars.com/api/?name=Lisa+Anderson&background=ec4899&color=fff",
  },
  {
    id: 6,
    text: "I love how I can update my profile instantly without reprinting cards. The metal card quality is exceptional - it's become a conversation starter itself!",
    name: "Robert Kim",
    role: "Startup Founder",
    avatar:
      "https://ui-avatars.com/api/?name=Robert+Kim&background=8b5cf6&color=fff",
  },
];

// Testimonial Carousel State & Logic
const currentTestimonial = ref(0);
let carouselTimer = null;
const CAROUSEL_INTERVAL = 4000; // 4 seconds between slides
const isTransitioning = ref(true); // Control transition class

// Extended testimonials for seamless loop
// Add clones: [Last3, ...Originals, First3] to allow infinite sliding both ways if needed
// For simple forward infinite loop, we just need [ ...Originals, ...First3 ]
const extendedTestimonials = computed(() => {
  return [...testimonials, ...testimonials.slice(0, 3)];
});

// Calculate carousel position (33.333% per card on desktop)
const carouselPosition = computed(() => {
  return currentTestimonial.value * 33.333;
});

// Go to specific testimonial
const goToTestimonial = (index) => {
  isTransitioning.value = true;
  currentTestimonial.value = index;
  resetCarouselTimer();
};

// Next testimonial with seamless loop
const nextTestimonial = () => {
  if (!isTransitioning.value) isTransitioning.value = true;
  
  // If we are at the last real item, slide to the first clone
  if (currentTestimonial.value >= testimonials.length) {
    // Should verify if we are already dealing with reset logic
    return;
  }
  
  currentTestimonial.value++;
  
  // If we reached the clone of the first item (index == length)
  if (currentTestimonial.value === testimonials.length) {
    // Wait for transition to finish, then snap back to 0
    setTimeout(() => {
      isTransitioning.value = false; // Disable transition
      currentTestimonial.value = 0;  // Snap to real 0
      
      // Re-enable transition after a small tick
      setTimeout(() => {
        isTransitioning.value = true;
      }, 50);
    }, 700); // Match CSS transition duration
  }
  
  resetCarouselTimer();
};

// Previous testimonial
const prevTestimonial = () => {
  isTransitioning.value = true;
  if (currentTestimonial.value <= 0) {
    // For simple implementation, just loop back to end (with rewind effect)
    // or implement similar clone logic for reverse direction if critical
    currentTestimonial.value = testimonials.length - 1;
  } else {
    currentTestimonial.value--;
  }
  resetCarouselTimer();
};

// Start auto-sliding
const startCarousel = () => {
  stopCarousel();
  carouselTimer = setInterval(() => {
    nextTestimonial();
  }, CAROUSEL_INTERVAL);
};

// Stop auto-sliding
const stopCarousel = () => {
  if (carouselTimer) {
    clearInterval(carouselTimer);
    carouselTimer = null;
  }
};

// Pause on hover
const pauseCarousel = () => {
  stopCarousel();
};

// Resume after hover
const resumeCarousel = () => {
  startCarousel();
};

// Reset timer after manual navigation
const resetCarouselTimer = () => {
  stopCarousel();
  startCarousel();
};

// Start carousel on mount
onMounted(() => {
  startCarousel();
});

// Cleanup on unmount
onBeforeUnmount(() => {
  stopCarousel();
});


const brandLogos = [
  { name: "Microsoft", icon: "simple-icons:microsoft" },
  { name: "Google", icon: "simple-icons:google" },
  { name: "Amazon", icon: "simple-icons:amazon" },
  { name: "Apple", icon: "simple-icons:apple" },
  { name: "Meta", icon: "simple-icons:meta" },
  { name: "Tesla", icon: "simple-icons:tesla" },
  { name: "Spotify", icon: "simple-icons:spotify" },
  { name: "Adobe", icon: "simple-icons:adobe" },
  { name: "Slack", icon: "simple-icons:slack" },
  { name: "Zoom", icon: "simple-icons:zoom" },
];

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
    // Prepare email content
    const subject = `Business Plan Request - ${businessForm.company_name}`;
    const body = `
Business Plan Request Details:

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
COMPANY INFORMATION
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Company Name: ${businessForm.company_name}
Company Address: ${businessForm.company_address}
Number of NFC Cards Needed: ${businessForm.quota} cards

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CONTACT INFORMATION
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Contact Person: ${businessForm.contact_person}
Email: ${businessForm.email}
Phone: ${businessForm.phone}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
USER ACCOUNT DETAILS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
User: ${authStore.user?.first_name || "Guest"} ${
      authStore.user?.last_name || "User"
    }
User Email: ${authStore.user?.email || businessForm.email}
Submitted: ${new Date().toLocaleString()}
${
  businessForm.notes
    ? `\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\nADDITIONAL NOTES\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n${businessForm.notes}`
    : ""
}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Please contact this customer within 24 hours.
This is an automated request from NFCGo platform.
`;

    // Create Gmail compose URL
    const gmailUrl = `https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent(
      "genn.chong@clbgroups.com"
    )}&su=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;

    // Open Gmail in new tab
    window.open(gmailUrl, "_blank");

    // Show success message
    $toast.success(
      "Opening Gmail... Please send the email to complete your Business Plan request."
    );

    // Close modal and show success alert after a short delay
    setTimeout(() => {
      closeBusinessModal();
      // Show inline success alert since already on Homepage
      showBusinessRequestSuccess.value = true;
      // Auto-dismiss after 15 seconds
      setTimeout(() => {
        dismissBusinessRequestSuccess();
      }, 15000);
    }, 2000);
  } catch (error) {
    console.error("Business plan request error:", error);
    $toast.error(
      "Failed to open email client. Please contact genn.chong@clbgroups.com directly."
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

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
  document.addEventListener("keydown", handleEscKey);
  
  // Load prices and questions
  loadPrices();
  loadPopularQuestions();

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
      navigateTo("/AdminManagement/nfc-cards");
    } else {
      navigateTo("/UserDashboard");
    }
  }
});

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
  document.removeEventListener("keydown", handleEscKey);
});
// Feature Auto-Slide Logic
const featureTopCurrentSlide = ref(0);
const featureBottomCurrentSlide = ref(0);
const featureTopIsTransitioning = ref(true);
const featureBottomIsTransitioning = ref(true);
const featureMobileMode = ref(false);
let featureSlideInterval;

const featuresList = [
  {
    title: 'Works Instantly',
    description: 'No app needed',
    icon: 'heroicons:bolt',
    bgClass: 'bg-gradient-to-br from-blue-100 to-blue-200',
    textClass: 'text-blue-600'
  },
  {
    title: 'Customizable Profiles',
    description: 'Customizable and ready template for content and card',
    icon: 'heroicons:paint-brush',
    bgClass: 'bg-gradient-to-br from-purple-100 to-purple-200',
    textClass: 'text-purple-600'
  },
  {
    title: 'Seamless Linking',
    description: 'Link to WhatsApp, LinkedIn, website, and other social media apps',
    icon: 'heroicons:wifi',
    bgClass: 'bg-gradient-to-br from-green-100 to-green-200',
    textClass: 'text-green-600'
  },
  {
    title: 'Analytics',
    description: 'Track taps and engagement',
    icon: 'heroicons:chart-bar',
    bgClass: 'bg-gradient-to-br from-orange-100 to-orange-200',
    textClass: 'text-orange-600'
  },
  {
    title: 'Centralized Connection Management',
    description: 'Organize every connection in one dashboard. Add notes, set reminders, and never lose a lead.',
    icon: 'heroicons:users',
    bgClass: 'bg-gradient-to-br from-indigo-100 to-indigo-200',
    textClass: 'text-indigo-600'
  },
  {
    title: 'Eco-Friendly',
    description: 'Eliminate paper waste. One digital card lasts forever.',
    icon: 'heroicons:globe-americas',
    bgClass: 'bg-gradient-to-br from-teal-100 to-teal-200',
    textClass: 'text-teal-600'
  },
  {
    title: 'QR Code Backup',
    description: 'Built-in QR code for non-NFC devices. Universal compatibility guaranteed.',
    icon: 'heroicons:qr-code',
    bgClass: 'bg-gradient-to-br from-slate-100 to-slate-200',
    textClass: 'text-slate-600'
  },
  {
    title: 'Privacy Controls',
    description: 'Choose what to share and when. Disable temporarily without replacing the card.',
    icon: 'heroicons:shield-check',
    bgClass: 'bg-gradient-to-br from-rose-100 to-rose-200',
    textClass: 'text-rose-600'
  },
  {
    title: 'Video Introduction',
    description: 'Add a personal video message. Memorable first impressions every time.',
    icon: 'heroicons:video-camera',
    bgClass: 'bg-gradient-to-br from-fuchsia-100 to-fuchsia-200',
    textClass: 'text-fuchsia-600'
  }
];

// Double the features to create seamless loop
const featuresRow1 = computed(() => [...featuresList, ...featuresList]);
// For row 2, we start from index 3 (offset), and also double to ensure enough items
const featuresRow2 = computed(() => {
  const rotated = [...featuresList.slice(3), ...featuresList.slice(0, 3)];
  return [...rotated, ...rotated];
});

const startFeatureSlideShow = () => {
  // Initialize start positions
  // Top row starts at index 9 (end of first set) to slide right (decrement)
  featureTopCurrentSlide.value = featuresList.length; 
  // Bottom row starts at 0 to slide left (increment)
  featureBottomCurrentSlide.value = 0;

  featureSlideInterval = setInterval(() => {
    // Enable transitions
    featureTopIsTransitioning.value = true;
    featureBottomIsTransitioning.value = true;

    // Slide Top Row Right (Decrement)
    featureTopCurrentSlide.value--;

    // Slide Bottom Row Left (Increment)
    featureBottomCurrentSlide.value++;

    // Check bounds and snap back
    // For Top Row (Right Slide): When we reach 0, we are identical to index 9.
    // We allow the transition to 0 to finish, then snap back to 9.
    const topReset = featureTopCurrentSlide.value <= 0;
    
    // For Bottom Row (Left Slide): When we reach length, we are identical to index 0.
    const bottomReset = featureBottomCurrentSlide.value >= featuresList.length;

    if (topReset || bottomReset) {
      setTimeout(() => {
        if (topReset) {
          featureTopIsTransitioning.value = false;
          featureTopCurrentSlide.value = featuresList.length;
        }
        if (bottomReset) {
          featureBottomIsTransitioning.value = false;
          featureBottomCurrentSlide.value = 0;
        }
      }, 700); // Match CSS duration
    }
  }, 3500); // 3-4 seconds per slide
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
</style>
