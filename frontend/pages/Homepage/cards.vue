<!-- pages/Homepage/cards.vue -->
<template>
  <section
    id="product-gallery"
    class="section ink-surface ink-grid relative overflow-hidden pt-28 sm:pt-32"
  >
    <div class="container relative">
      <div class="max-w-2xl mb-14" data-reveal>
        <span class="lp-kicker on-ink">Materials &amp; Design</span>
        <h1 class="lp-h2 on-ink mt-4 mb-4">Design your perfect card</h1>
        <p class="lp-lead lead-ink">
          Choose from premium materials and stunning designs that reflect your
          personal brand. Hover any card to see the back.
        </p>
      </div>

      <div class="mb-20">
        <h2 class="gallery-sub">Premium card materials</h2>
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
                    <span class="nfc-mock__brand" :class="m.accent">NFCGo</span>
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
                <div class="nfc-mock__face nfc-mock__face--back" :class="m.face">
                  <div class="nfc-mock__sheen"></div>
                  <div class="relative z-10 flex items-center justify-between">
                    <span class="nfc-mock__brand" :class="m.accent">{{
                      m.name
                    }}</span>
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
                  <span
                    class="relative z-10 text-[0.6rem] tracking-[0.18em] uppercase"
                    :class="m.accent"
                    >embedded nfc · no battery</span
                  >
                </div>
              </div>
            </div>
            <div class="mt-6">
              <div class="flex items-start justify-between gap-3">
                <h3 class="text-xl font-semibold text-white">{{ m.name }}</h3>
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
        <h2 class="gallery-sub">Design inspirations</h2>
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
          <h2 class="text-2xl md:text-3xl font-bold text-white mb-4">
            Need a custom design or bulk order?
          </h2>
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
</template>

<script setup>
definePageMeta({ layout: 'public' });

useSeoMeta({
  title: "Cards - NFCGo Premium NFC Card Materials & Designs",
  description:
    "Browse NFCGo card materials — Classic PVC, Premium Metal and Eco Bamboo — plus ready-made design templates and custom bulk orders.",
});

useScrollReveal();

const { openContactModal } = usePublicUi();

// Card material showcase (rendered as pure-CSS card mockups)
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

// Ready-made design presets (rendered as pure-CSS card faces)
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
</script>
