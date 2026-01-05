<template>
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
        <!-- Feature 1: Works Instantly -->
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

        <!-- Feature 2: Customizable Profiles -->
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

        <!-- Feature 3: Seamless Linking -->
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

        <!-- Feature 4: Analytics -->
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

        <!-- Feature 5: Centralized Connection Management -->
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
</template>

<script setup>
// Self-contained component with no props needed
</script>
