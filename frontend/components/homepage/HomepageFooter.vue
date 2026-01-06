<template>
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
            <a
              href="https://www.facebook.com/profile.php?id=61568045614509"
              target="_blank"
              rel="noopener noreferrer"
              class="text-secondary-400 hover:text-white transition-colors"
            >
              <Icon name="mdi:facebook" class="h-5 w-5" />
            </a>
          </div>
        </div>
        <div>
          <h4 class="font-semibold mb-4">Product</h4>
          <ul class="space-y-2">
            <li>
              <a
                href="#features"
                @click.prevent="smoothScroll('#features')"
                class="text-secondary-400 hover:text-white transition-colors"
                >Features</a
              >
            </li>
            <li>
              <a
                href="#pricing"
                @click.prevent="smoothScroll('#pricing')"
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
                @click.prevent="$emit('open-contact')"
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
                @click="$emit('open-legal', 'privacy')"
                class="text-secondary-400 hover:text-white transition-colors"
                >Privacy</button
              >
            </li>
            <li>
              <button
                @click="$emit('open-legal', 'terms')"
                class="text-secondary-400 hover:text-white transition-colors"
                >Terms</button
              >
            </li>
          </ul>
        </div>
      </div>
      <div class="border-t border-secondary-800 mt-12 pt-8 text-center">
        <p class="text-secondary-400">
          @Copyright CLB SDN BHD – 2025 -RFID Label Supplier , RFID Solution and Stick Printing I All Rights Reserved
        </p>
      </div>
    </div>
  </footer>
</template>

<script setup>
defineEmits(['open-legal', 'open-contact'])

const smoothScroll = (target) => {
  const element = document.querySelector(target)
  if (element) {
    element.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}
</script>
