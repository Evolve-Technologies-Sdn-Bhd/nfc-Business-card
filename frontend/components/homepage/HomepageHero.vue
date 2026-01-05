<template>
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
            <button @click="$emit('show-demo')" class="btn btn-outline btn-lg">
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
              class="nfc-card w-64 h-40 mx-auto lg:w-80 lg:h-48 p-6 flex flex-col justify-between shadow-2xl">
              <div class="flex items-center justify-between">
                  <div>
                      <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                          <Icon name="heroicons:user" class="h-6 w-6 text-white" />
                      </div>
                  </div>
                  <div>
                      <Icon name="heroicons:wifi" class="h-8 w-8 text-white/60 animate-pulse" />
                  </div>
              </div>
              <div>
                  <h3 class="text-white text-xl font-semibold">John Doe</h3>
                  <p class="text-white/80 text-sm">Senior Developer</p>
                  <p class="text-white/60 text-xs mt-1">👆 Tap to connect</p>
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
</template>

<script setup>
defineEmits(['show-demo'])
</script>
