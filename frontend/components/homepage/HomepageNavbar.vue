<template>
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
            @click.prevent="smoothScroll('#features')"
            class="nav-link"
            >Features</a
          >
          <a
            href="#how-it-works"
            @click.prevent="smoothScroll('#how-it-works')"
            class="nav-link"
            >How it Works</a
          >
          <a
            href="#who-uses"
            @click.prevent="smoothScroll('#who-uses')"
            class="block px-3 py-2 nav-link"
            >Who Uses</a
          >
          <a
            href="#product-gallery"
            @click.prevent="smoothScroll('#product-gallery')"
            class="block px-3 py-2 nav-link"
            >Product Gallery</a
          >
          <a
            href="#testimonials"
            @click.prevent="smoothScroll('#testimonials')"
            class="block px-3 py-2 nav-link"
            >Testimonials</a
          >
          <a
            href="#pricing"
            @click.prevent="smoothScroll('#pricing')"
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
            @click="$emit('toggle-chatbot')"
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
          @click.prevent="smoothScroll('#features')"
          class="block px-3 py-2 nav-link"
          >Features</a
        >
        <a
          href="#how-it-works"
          @click.prevent="smoothScroll('#how-it-works')"
          class="block px-3 py-2 nav-link"
          >How it Works</a
        >
        <a
          href="#who-uses"
          @click.prevent="smoothScroll('#who-uses')"
          class="block px-3 py-2 nav-link"
          >Who Uses</a
        >
        <a
          href="#product-gallery"
          @click.prevent="smoothScroll('#product-gallery')"
          class="block px-3 py-2 nav-link"
          >Product Gallery</a
        >
        <a
          href="#testimonials"
          @click.prevent="smoothScroll('#testimonials')"
          class="block px-3 py-2 nav-link"
          >Testimonials</a
        >
        <a
          href="#pricing"
          @click.prevent="smoothScroll('#pricing')"
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
          @click="$emit('toggle-chatbot'); mobileMenuOpen = false"
          class="block px-3 py-2 nav-link text-left w-full"
        >
          <Icon name="heroicons:chat-bubble-left-right" class="h-5 w-5 inline mr-2" />
          AI Assistant
        </button>
      </div>
    </div>
  </nav>
</template>

<script setup>
defineEmits(['toggle-chatbot'])

const mobileMenuOpen = ref(false)

const smoothScroll = (target) => {
  const element = document.querySelector(target)
  if (element) {
    element.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
  mobileMenuOpen.value = false
}

// Close mobile menu when clicking outside
const handleClickOutside = (e) => {
  if (!e.target.closest('nav')) {
    mobileMenuOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
