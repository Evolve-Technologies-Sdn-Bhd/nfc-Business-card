<template>
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
          <!-- PVC Card -->
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
                  <span class="text-primary-600 font-medium">Most Popular</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Metal Card -->
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

          <!-- Bamboo Card -->
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
                    name="heroicons:leaf"
                    class="h-16 w-16 text-yellow-100 mx-auto mb-3"
                  />
                  <p class="text-yellow-100 font-semibold">Bamboo Card</p>
                </div>
              </div>
              <div class="p-6">
                <h4 class="text-xl font-semibold text-secondary-900 mb-2">
                  Eco Bamboo
                </h4>
                <p class="text-secondary-600 mb-4">
                  Sustainable and unique. Perfect for eco-conscious
                  professionals.
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

      <!-- CTA -->
      <div class="text-center">
        <NuxtLink
          to="/UserAccount/register"
          class="btn btn-primary btn-lg shadow-lg"
        >
          <Icon name="heroicons:sparkles" class="h-5 w-5 mr-2" />
          Start Designing Your Card
        </NuxtLink>
      </div>
    </div>
  </section>
</template>

<script setup>
// Self-contained component
</script>
