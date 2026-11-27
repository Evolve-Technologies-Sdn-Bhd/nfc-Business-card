<!-- pages/Homepage/faq.vue -->
<template>
  <section
    v-if="faqLoading || faqItems.length > 0"
    id="faq"
    class="section bg-[color:var(--paper)] pt-28 sm:pt-32"
  >
    <div class="container">
      <div class="max-w-2xl mb-14" data-reveal>
        <span class="lp-kicker">FAQ</span>
        <h1 class="lp-h2 mt-4 mb-4">Frequently asked questions</h1>
        <p class="lp-lead">
          Answers to common questions about NFCGo — cards, shipping, activation
          and your digital profile.
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
            <h2 class="faq-item__q">{{ item.question }}</h2>
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
</template>

<script setup>
definePageMeta({ layout: 'public', middleware: 'guest' });

useSeoMeta({
  title: "FAQ - NFCGo Smart Digital Business Cards",
  description:
    "Frequently asked questions about NFCGo cards, shipping, activation, pricing and your digital profile.",
});

const { $api } = useNuxtApp();
const { setupReveal } = useScrollReveal();

const faqItems = ref([]);
const faqLoading = ref(true);
const openFaqIndex = ref(null);

// FAQ items come from the public chatbot questions endpoint.
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

onMounted(loadFaq);
</script>
