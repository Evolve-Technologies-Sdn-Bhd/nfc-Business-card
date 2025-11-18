<!-- pages/index.vue -->
<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-50 to-secondary-100">
    <!-- Navigation - STICKY HEADER -->
    <nav
      class="fixed top-0 left-0 right-0 z-50 bg-white/10 backdrop-blur-md border-b border-white/20 shadow-sm"
    >
      <div class="container">
        <div class="flex items-center justify-between h-16">
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
              class="nav-link"
              >Pricing</a
            >
            <NuxtLink to="/UserAccount/login" class="btn btn-ghost"
              >Login</NuxtLink
            >
            <NuxtLink to="/UserAccount/register" class="btn btn-primary"
              >Get Started</NuxtLink
            >
            <!-- AI Chatbot Button -->
            <button
              @click="showChatbot = true"
              class="btn btn-ghost btn-circle"
              title="AI Assistant"
            >
              <Icon name="heroicons:chat-bubble-left-right" class="h-6 w-6" />
            </button>
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
          <button
            @click="showChatbot = true"
            class="block px-3 py-2 nav-link text-left"
          >
            <Icon name="heroicons:chat-bubble-left-right" class="h-5 w-5 inline mr-2" />
            AI Assistant
          </button>
        </div>
      </div>
    </nav>

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

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            class="card-hover p-8 text-center group animate-fade-in-up"
            style="animation-delay: 0.1s"
          >
            <div
              class="w-16 h-16 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300"
            >
              <Icon name="heroicons:bolt" class="h-8 w-8 text-blue-600" />
            </div>
            <h3 class="text-xl font-semibold text-secondary-900 mb-3">
              Works Instantly
            </h3>
            <p class="text-secondary-600 leading-relaxed">No app needed</p>
          </div>

          <div
            class="card-hover p-8 text-center group animate-fade-in-up"
            style="animation-delay: 0.2s"
          >
            <div
              class="w-16 h-16 bg-gradient-to-br from-purple-100 to-purple-200 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300"
            >
              <Icon
                name="heroicons:paint-brush"
                class="h-8 w-8 text-purple-600"
              />
            </div>
            <h3 class="text-xl font-semibold text-secondary-900 mb-3">
              Customizable Profiles
            </h3>
            <p class="text-secondary-600 leading-relaxed">
              Customizable and ready template for content and card
            </p>
          </div>

          <div
            class="card-hover p-8 text-center group animate-fade-in-up"
            style="animation-delay: 0.3s"
          >
            <div
              class="w-16 h-16 bg-gradient-to-br from-green-100 to-green-200 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300"
            >
              <Icon name="heroicons:wifi" class="h-8 w-8 text-green-600" />
            </div>
            <h3 class="text-xl font-semibold text-secondary-900 mb-3">
              Seamless Linking
            </h3>
            <p class="text-secondary-600 leading-relaxed">
              Link to WhatsApp, LinkedIn, website, and other social media apps
            </p>
          </div>

          <div
            class="card-hover p-8 text-center group animate-fade-in-up"
            style="animation-delay: 0.4s"
          >
            <div
              class="w-16 h-16 bg-gradient-to-br from-orange-100 to-orange-200 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300"
            >
              <Icon
                name="heroicons:chart-bar"
                class="h-8 w-8 text-orange-600"
              />
            </div>
            <h3 class="text-xl font-semibold text-secondary-900 mb-3">
              Analytics
            </h3>
            <p class="text-secondary-600 leading-relaxed">
              Track taps and engagement
            </p>
          </div>

          <div
            class="card-hover p-8 text-center group animate-fade-in-up"
            style="animation-delay: 0.6s"
          >
            <div
              class="w-16 h-16 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300"
            >
              <Icon name="heroicons:users" class="h-8 w-8 text-indigo-600" />
            </div>
            <h3 class="text-xl font-semibold text-secondary-900 mb-3">
              Centralized Connection Management
            </h3>
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
              <div
                class="w-20 h-20 bg-primary-600 rounded-full flex items-center justify-center mx-auto shadow-lg"
              >
                <span class="text-2xl font-bold text-white">{{
                  index + 1
                }}</span>
              </div>
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
                Team analytics UserDashboard
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

    <!-- Testimonials Section -->
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

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
          <div
            v-for="testimonial in testimonials"
            :key="testimonial.id"
            class="card p-6 hover:shadow-xl transition-all duration-300 relative"
          >
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
            <p class="text-secondary-600 mb-6 italic leading-relaxed">
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
            class="card p-6 text-center relative transition-all duration-300 bg-gradient-to-br from-green-50 to-emerald-50 border-green-200 hover:shadow-lg"
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
            class="card p-6 text-center relative transition-all duration-300 hover:shadow-lg"
          >
            <h3 class="text-xl font-bold text-secondary-900 mb-2">Basic</h3>
            <div class="mb-6">
              <div class="text-2xl font-bold text-secondary-900 mb-2">
                Contact for Pricing
              </div>
              <p class="text-sm text-secondary-500">
                For individual professionals
              </p>
            </div>
            <ul class="space-y-2 mb-6 text-left text-sm">
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">3 Digital Profiles</span>
              </li>
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">Premium Templates</span>
              </li>
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">Custom Branding</span>
              </li>
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">Contact Forms</span>
              </li>
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">Advanced Analytics</span>
              </li>
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">Email Support</span>
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
            class="card p-6 text-center relative transition-all duration-300 hover:shadow-lg"
          >
            <h3 class="text-xl font-bold text-secondary-900 mb-2">Premium</h3>
            <div class="mb-6">
              <div class="text-2xl font-bold text-secondary-900 mb-2">
                Contact for Pricing
              </div>
              <p class="text-sm text-secondary-500">For growing businesses</p>
            </div>
            <ul class="space-y-2 mb-6 text-left text-sm">
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">10 Digital Profiles</span>
              </li>
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">All Premium Features</span>
              </li>
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">Team Management</span>
              </li>
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">Lead Capture Forms</span>
              </li>
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">CRM Integration</span>
              </li>
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">Priority Support</span>
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
            class="card p-6 text-center relative transition-all duration-300 hover:shadow-lg"
          >
            <h3 class="text-xl font-bold text-secondary-900 mb-2">Business</h3>
            <div class="mb-6">
              <div class="text-2xl font-bold text-secondary-900 mb-2">
                Contact for Pricing
              </div>
              <p class="text-sm text-secondary-500">For large organizations</p>
            </div>
            <ul class="space-y-2 mb-6 text-left text-sm">
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">Unlimited Profiles</span>
              </li>
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">Business Features</span>
              </li>
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">White Label Solution</span>
              </li>
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">API Access</span>
              </li>
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">Custom Integrations</span>
              </li>
              <li class="flex items-start">
                <Icon
                  name="heroicons:check"
                  class="h-4 w-4 text-success-600 mr-2 mt-0.5 flex-shrink-0"
                />
                <span class="text-secondary-600">24/7 Priority Support</span>
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
                href="#"
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
                  class="text-secondary-400 hover:text-white transition-colors"
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
                  href="#"
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
                <a
                  href="#"
                  class="text-secondary-400 hover:text-white transition-colors"
                  >Privacy</a
                >
              </li>
              <li>
                <a
                  href="#"
                  class="text-secondary-400 hover:text-white transition-colors"
                  >Terms</a
                >
              </li>
            </ul>
          </div>
        </div>
        <div class="border-t border-secondary-800 mt-12 pt-8 text-center">
          <p class="text-secondary-400">
            © 2025 NFCGo. Evolve Technology Platform Sdn. Bhd. All rights
            reserved.
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
  </div>
</template>

<script setup>
import { watch } from 'vue';

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

// AI Chatbot
const showChatbot = ref(false);
const chatInput = ref('');
const messages = ref([]);
const isTyping = ref(false);
const chatMessages = ref(null);
const popularQuestions = ref([]);
const loadingQuestions = ref(false);

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
const submittingBusinessRequest = ref(false);

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
      "Opening Gmail... Please send the email, then you'll be redirected to your dashboard."
    );

    // Close modal and redirect to dashboard after a short delay
    setTimeout(() => {
      closeBusinessModal();
      // Navigate to dashboard if user is authenticated, otherwise to login
      if (authStore.isAuthenticated) {
        window.location.href = "/UserDashboard";
      } else {
        window.location.href = "/UserAccount/login";
      }
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
    // Call chatbot API
    const response = await $api.post('/chatbot/ask', {
      question: question
    });

    let aiResponse = '';
    let questionId = null;

    if (response.data.success && response.data.found) {
      // Found matching answer from database
      aiResponse = response.data.data.answer;
      questionId = response.data.data.id;
    } else {
      // No match found - use fallback
      aiResponse = "I couldn't find a specific answer to your question in our FAQ database. However, I'd be happy to help!\n\nFor immediate assistance:\n• Check our FAQ section\n• Contact support at support@nfcgo.com\n• Call us at +60 123-456-789\n\nWould you like me to connect you with a human agent?";
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

// Load popular questions for quick buttons
const loadPopularQuestions = async () => {
  loadingQuestions.value = true;
  try {
    const response = await $api.get('/chatbot/questions');
    if (response.data.success) {
      popularQuestions.value = response.data.data.slice(0, 6); // Top 6 questions
    }
  } catch (error) {
    console.error('Failed to load popular questions:', error);
    // Fallback questions are already in the template
  } finally {
    loadingQuestions.value = false;
  }
};

// Submit feedback (helpful/not helpful)
const submitFeedback = async (message, isHelpful) => {
  if (!message.questionId) return;
  
  try {
    await $api.post('/chatbot/feedback', {
      question_id: message.questionId,
      user_question: messages.value.find(m => m.sender === 'user' && m.timestamp < message.timestamp)?.text || '',
      rating: isHelpful ? 5 : 2,
      feedback_type: 'rating'
    });
    
    // Mark feedback as given
    message.feedbackGiven = true;
  } catch (error) {
    console.error('Failed to submit feedback:', error);
  }
};

// Watch for chatbot modal opening to load questions
watch(showChatbot, (newValue) => {
  if (newValue && popularQuestions.value.length === 0) {
    loadPopularQuestions();
  }
});

// Submit user feedback
const submitUserFeedback = async () => {
  if (!feedbackForm.category || !feedbackForm.message) {
    alert('Please fill in all required fields');
    return;
  }

  submittingFeedback.value = true;
  try {
    await $api.post('/chatbot/feedback', {
      user_question: 'General Feedback from Homepage',
      user_message: feedbackForm.message,
      user_name: feedbackForm.user_name || null,
      user_email: feedbackForm.user_email || null,
      rating: feedbackForm.rating || null,
      feedback_type: 'general',
      category: feedbackForm.category
    });

    $toast.success('Thank you for your feedback! We will review it shortly.');
    
    // Reset form
    Object.assign(feedbackForm, {
      category: '',
      user_name: '',
      user_email: '',
      message: '',
      rating: 0
    });
    
    showFeedbackModal.value = false;
  } catch (error) {
    console.error('Failed to submit feedback:', error);
    $toast.error('Failed to submit feedback. Please try again.');
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

  const authStore = useAuthStore();
  if (authStore.isAuthenticated) {
    if (authStore.isAdmin()) {
      navigateTo("/AdminManagement/nfc-cards");
    } else {
      navigateTo("/UserDashboard");
    }
  }
});

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
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
</style>
