<!-- pages/UserDashboard/CardManagement.vue -->
<template>
  <div class="min-h-screen bg-secondary-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-secondary-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center space-x-4">
            <div>
              <h1 class="text-2xl font-semibold text-secondary-900">
                Card Management
              </h1>
              <p class="text-sm text-secondary-600">
                Manage your physical NFC cards and subscriptions
              </p>
            </div>
          </div>
          <div class="flex items-center space-x-4">
            <!-- ─── Header right action button: NO FLICKER ────────────────────
                 Tier priority:
                 (1) While loading → use authStore INSTANT value (no API wait)
                 (2) After load → same computed hasPremiumSubscription as before
                 Result: "Upgrade to Premium" NEVER appears for a Premium user,
                 even for a 1ms flash.  The raw computed value is ALREADY safe
                 because it checks authStore first — but we add this explicit
                 early-return here for DOCUMENTATION / clarity of intent. -->
            <template v-if="!loading">
              <button
                v-if="!hasPremiumSubscription"
                @click="upgradeToPremium"
                class="btn btn-primary"
              >
                <Icon name="heroicons:star" class="h-4 w-4 mr-2" />
                Upgrade to Premium
              </button>
              <button v-else @click="orderNewCard" class="btn btn-primary">
                <Icon name="heroicons:plus" class="h-4 w-4 mr-2" />
                Order New Card
              </button>
            </template>
            <!-- Semasa loading: show placeholder same size BUTTON so layout tak shift -->
            <button v-else disabled class="btn btn-primary opacity-70">
              <div class="spinner spinner-sm text-white mr-2"></div>
              Loading...
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- ─── LOADING STATE (renders ALONE — no gate, no content, no empty state) ───
           Elak FLICKER "Premium padlock → content" yang nampak selama 300ms.
           Semasa data tengah loading, user cuma nampak spinner SINGLE block.  -->
      <div v-if="loading" class="text-center py-16">
        <div class="spinner mx-auto mb-4"></div>
        <p class="text-secondary-600">Loading card information...</p>
      </div>

      <!-- ─── AFTER LOADING: ONLY then decide gate/content/empty ─────────────── -->
      <template v-else>
        <!-- Upgrade Required Message (ONLY if !hasPremiumSubscription AFTER load) -->
        <div v-if="!hasPremiumSubscription" class="text-center py-12">
          <div class="max-w-md mx-auto">
            <Icon
              name="heroicons:lock-closed"
              class="h-16 w-16 text-secondary-400 mx-auto mb-4"
            />
            <h3 class="text-xl font-semibold text-secondary-900 mb-2">
              Premium Feature
            </h3>
            <p class="text-secondary-600 mb-6">
              Physical NFC card management is available exclusively to Premium
              subscribers. Upgrade your plan to access this feature.
            </p>
            <button @click="upgradeToPremium" class="btn btn-primary btn-lg">
              <Icon name="heroicons:star" class="h-5 w-5 mr-2" />
              Upgrade to Premium
            </button>
          </div>
        </div>

        <!-- NFC Cards List -->
        <div v-else-if="nfcCards.length > 0" class="space-y-3">
          <div class="flex items-center justify-between mb-4 mt-2">
            <h3 class="text-lg font-semibold text-secondary-900">
              Your Cards
              <span class="text-sm font-normal text-secondary-500 ml-2">
                ({{ nfcCards.length }} total)
              </span>
            </h3>
          </div>
          <div v-for="card in nfcCards" :key="card.id" class="card overflow-hidden">
            <!-- Compact Card Header - Click to Expand -->
            <div
              class="grid grid-cols-3 items-center p-5 cursor-pointer hover:bg-secondary-50 transition-colors gap-4"
              @click="toggleExpand(card.id)"
            >
              <!-- Left: Name + Card ID -->
              <div class="flex items-center space-x-4 min-w-0">
                <div class="h-11 w-11 rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center shrink-0 shadow-md">
                  <Icon name="heroicons:credit-card" class="h-5 w-5 text-white" />
                </div>
                <div class="min-w-0">
                  <h4 class="text-base font-semibold text-secondary-900 truncate">
                    {{ card.card_owner }}
                  </h4>
                  <p class="text-sm text-secondary-500 font-mono truncate">
                    ID: {{ card.card_id || card.nfc_card_id }}
                  </p>
                </div>
              </div>

              <!-- Center: Plan -->
              <div class="flex justify-center min-w-0">
                <div class="text-center">
                  <p class="text-xs text-secondary-500">Plan</p>
                  <p class="text-sm font-semibold text-primary-700">
                    {{ String(card.subscription_plan || '').replace(/\b\w/g, c => c.toUpperCase()) }}
                  </p>
                </div>
              </div>

              <!-- Right: Status + Expand Button -->
              <div class="flex items-center justify-end space-x-3 shrink-0 min-w-0">
                <span
                  :class="card.status_badge_class || getStatusBadgeClass(card.status)"
                  class="px-3 py-1.5 rounded-full text-xs font-medium whitespace-nowrap"
                >
                  {{ getCardStatusLabel(card) }}
                </span>
                <button
                  class="p-2 rounded-lg hover:bg-secondary-100 transition-colors text-secondary-400 hover:text-secondary-700 shrink-0"
                  @click.stop
                >
                  <Icon
                    :name="expandedCardId === card.id ? 'heroicons:chevron-up' : 'heroicons:chevron-down'"
                    class="h-5 w-5 transition-transform"
                  />
                </button>
              </div>
            </div>

            <!-- Expanded Content -->
            <Transition name="accordion">
              <div v-if="expandedCardId === card.id" class="border-t border-secondary-200">
                <div class="card-body space-y-6">
                  <!-- Payment Instructions Panel: pending_payment only -->
                  <div
                    v-if="card.status === 'pending_payment'"
                    class="border border-orange-200 bg-orange-50 rounded-lg p-5"
                  >
                    <div class="flex items-start space-x-4">
                      <div class="shrink-0">
                        <div class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center text-orange-600">
                          <Icon name="heroicons:banknotes" class="h-5 w-5" />
                        </div>
                      </div>
                      <div class="flex-1 space-y-3">
                        <div>
                          <h4 class="font-semibold text-orange-900">
                            Complete Payment — Awaiting Payment
                          </h4>
                          <p class="text-sm text-orange-800 mt-1">
                            Please make payment for this order using the bank details below, then upload your payment proof.
                          </p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white rounded-lg border border-orange-100 p-4 text-sm">
                          <div>
                            <p class="text-secondary-500">Bank Name</p>
                            <p class="font-medium text-secondary-900">Maybank Berhad</p>
                          </div>
                          <div>
                            <p class="text-secondary-500">Account No.</p>
                            <p class="font-medium text-secondary-900">1234 5678 9012</p>
                          </div>
                          <div>
                            <p class="text-secondary-500">Account Owner Name</p>
                            <p class="font-medium text-secondary-900">CLB Groups Sdn Bhd</p>
                          </div>
                          <div>
                            <p class="text-secondary-500">Amount To Pay</p>
                            <p class="font-bold text-primary-700 text-lg">
                              RM {{ (Number(card.purchase_amount) || 0).toFixed(2) }}
                            </p>
                          </div>
                          <div v-if="card.transaction?.transaction_id" class="md:col-span-2">
                            <p class="text-secondary-500">Order Reference (put in payment description)</p>
                            <p class="font-mono font-medium text-secondary-900">
                              {{ card.transaction.transaction_id }}
                            </p>
                          </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                          <button
                            @click="openUploadPaymentProof(card)"
                            class="btn btn-primary"
                          >
                            <Icon name="heroicons:arrow-up-tray" class="h-4 w-4 mr-2" />
                            Upload Payment Proof
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Awaiting verification notice -->
                  <div
                    v-if="card.status === 'awaiting_payment_verification'"
                    class="border border-yellow-200 bg-yellow-50 rounded-lg p-5"
                  >
                    <div class="flex items-start space-x-4">
                      <div class="shrink-0">
                        <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-700">
                          <Icon name="heroicons:clock" class="h-5 w-5" />
                        </div>
                      </div>
                      <div class="flex-1 space-y-3">
                        <div>
                          <h4 class="font-semibold text-yellow-900">
                            Awaiting Payment Verification
                          </h4>
                          <p class="text-sm text-yellow-800 mt-1">
                            Your payment proof is being reviewed by admin. You will receive a notification after verification is complete. You may upload a new proof if needed.
                          </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                          <button
                            @click="openUploadPaymentProof(card)"
                            class="btn btn-outline btn-sm"
                          >
                            <Icon name="heroicons:arrow-up-tray" class="h-4 w-4 mr-2" />
                            Re-upload Proof
                          </button>
                          <a
                            v-if="card.transaction?.payment_proof_url"
                            :href="card.transaction.payment_proof_url"
                            target="_blank"
                            class="btn btn-outline btn-sm"
                          >
                            <Icon name="heroicons:eye" class="h-4 w-4 mr-2" />
                            View Uploaded Proof
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Confirm Received: shipped state prominent CTA -->
                  <div
                    v-if="card.status === 'shipped'"
                    class="border border-blue-200 bg-blue-50 rounded-lg p-5"
                  >
                    <div class="flex items-start space-x-4">
                      <div class="shrink-0">
                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700">
                          <Icon name="heroicons:truck" class="h-5 w-5" />
                        </div>
                      </div>
                      <div class="flex-1 space-y-3">
                        <div>
                          <h4 class="font-semibold text-blue-900">
                            Card Has Been Shipped!
                          </h4>
                          <p class="text-sm text-blue-800 mt-1">
                            Your NFC card has been shipped. If you have physically received the card, please click the button below to confirm receipt and activate your subscription.
                          </p>
                        </div>
                        <div
                          v-if="card.tracking_number"
                          class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white rounded-md border border-blue-100 p-4 text-sm"
                        >
                          <div>
                            <p class="text-secondary-500">Courier</p>
                            <p class="font-medium text-secondary-900">
                              {{ card.courier || "Standard Delivery" }}
                            </p>
                          </div>
                          <div>
                            <p class="text-secondary-500">Tracking No.</p>
                            <p class="font-mono font-medium text-secondary-900">
                              {{ card.tracking_number }}
                            </p>
                          </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                          <button
                            @click="confirmCardReceived(card)"
                            :disabled="confirmLoading.id === card.id && confirmLoading.loading"
                            class="btn btn-success"
                          >
                            <Icon
                              v-if="!(confirmLoading.id === card.id && confirmLoading.loading)"
                              name="heroicons:check-badge"
                              class="h-5 w-5 mr-2"
                            />
                            <div
                              v-else
                              class="spinner spinner-sm text-white mr-2"
                            ></div>
                            {{ confirmLoading.id === card.id && confirmLoading.loading ? "Confirming..." : "I've Received The Card" }}
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Cancelled notice -->
                  <div
                    v-if="card.status === 'cancelled'"
                    class="border border-red-200 bg-red-50 rounded-lg p-5"
                  >
                    <div class="flex items-start space-x-4">
                      <div class="shrink-0">
                        <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center text-red-700">
                          <Icon name="heroicons:x-circle" class="h-5 w-5" />
                        </div>
                      </div>
                      <div class="flex-1">
                        <h4 class="font-semibold text-red-900">Order Cancelled</h4>
                        <p class="text-sm text-red-800 mt-1" v-if="card.cancelled_reason">
                          Reason: {{ card.cancelled_reason }}
                        </p>
                        <p class="text-sm text-red-700 mt-1">
                          Please contact customer support if you believe this is a mistake.
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- Row 1: Card Details (2 cols span) + Actions (1 col) -->
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2">
                      <h4 class="text-sm font-medium text-secondary-500 mb-3">
                        Card Details
                      </h4>
                      <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2.5">
                        <div>
                          <p class="text-xs text-secondary-500 uppercase tracking-wide">NFC ID</p>
                          <p class="text-sm font-medium text-secondary-900 font-mono">{{ card.nfc_card_id || "-" }}</p>
                        </div>
                        <div>
                          <p class="text-xs text-secondary-500 uppercase tracking-wide">Plan</p>
                          <p class="text-sm font-semibold text-primary-700">{{ String(card.subscription_plan || '').replace(/\b\w/g, c => c.toUpperCase()) }}</p>
                        </div>
                        <div>
                          <p class="text-xs text-secondary-500 uppercase tracking-wide">Amount</p>
                          <p class="text-sm font-medium text-secondary-900">
                            RM {{ (Number(card.purchase_amount) || 0).toFixed(2) }}
                          </p>
                        </div>
                        <div>
                          <p class="text-xs text-secondary-500 uppercase tracking-wide">Purchase Date</p>
                          <p class="text-sm font-medium text-secondary-900">
                            {{ card.purchase_date ? formatDate(card.purchase_date) : "-" }}
                          </p>
                        </div>
                        <div>
                          <p class="text-xs text-secondary-500 uppercase tracking-wide">Expires</p>
                          <p class="text-sm font-medium text-secondary-900">
                            {{
                              card.expiry_date || card.subscription_end_date
                                ? formatDate(card.expiry_date || card.subscription_end_date)
                                : "-"
                            }}
                          </p>
                        </div>
                        <div v-if="card.courier || card.tracking_number">
                          <p class="text-xs text-secondary-500 uppercase tracking-wide">Tracking</p>
                          <p class="text-sm font-medium text-secondary-900">
                            {{ card.courier || "-" }}
                            <span v-if="card.tracking_number">#{{ card.tracking_number }}</span>
                          </p>
                        </div>
                      </div>
                    </div>
                    <div>
                      <h4 class="text-sm font-medium text-secondary-500 mb-3">
                        Actions
                      </h4>
                      <div class="space-y-2">
                        <button
                          @click="goToProfileBuilderForCard(card)"
                          :disabled="!cardCanDesignProfile(card)"
                          :class="[
                            'btn btn-sm w-full',
                            cardCanDesignProfile(card)
                              ? 'btn-primary'
                              : 'btn-secondary opacity-60 cursor-not-allowed'
                          ]"
                        >
                          <Icon name="heroicons:wrench-screwdriver" class="h-4 w-4 mr-1" />
                          {{ cardCanDesignProfile(card) ? "Design Profile" : "Design (After Payment)" }}
                        </button>

                        <template
                          v-if="['active','delivered','inactive','expired'].includes(card.status)"
                        >
                          <button
                            v-if="card.status === 'active' || card.status === 'delivered'"
                            @click="deactivateCard(card)"
                            class="btn btn-sm btn-outline btn-warning w-full"
                          >
                            <Icon name="heroicons:pause" class="h-4 w-4 mr-1" />
                            Deactivate
                          </button>
                          <button
                            v-else
                            @click="activateCard(card)"
                            class="btn btn-sm btn-outline btn-success w-full"
                          >
                            <Icon name="heroicons:play" class="h-4 w-4 mr-1" />
                            Activate
                          </button>
                        </template>

                        <button
                          v-if="['pending_payment','awaiting_payment_verification'].includes(card.status)"
                          @click="openUploadPaymentProof(card)"
                          class="btn btn-sm btn-outline w-full"
                        >
                          <Icon name="heroicons:arrow-up-tray" class="h-4 w-4 mr-1" />
                          Upload Proof
                        </button>

                        <button
                          v-if="card.status === 'shipped' || card.status === 'delivered'"
                          @click="confirmCardReceived(card)"
                          :disabled="confirmLoading.id === card.id && confirmLoading.loading"
                          class="btn btn-sm btn-success w-full"
                          :title="card.status === 'delivered' ? 'Activate subscription now' : 'Confirm physical card received'"
                        >
                          <Icon name="heroicons:check-circle" class="h-4 w-4 mr-1" />
                          <template v-if="confirmLoading.id === card.id && confirmLoading.loading">
                            Confirming...
                          </template>
                          <template v-else-if="card.status === 'delivered'">
                            Activate My Card
                          </template>
                          <template v-else>
                            I've Received The Card
                          </template>
                        </button>

                        <button
                          @click="viewAnalytics(card)"
                          class="btn btn-sm btn-outline w-full"
                        >
                          <Icon name="heroicons:chart-bar" class="h-4 w-4 mr-1" />
                          Analytics
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Row 2: Progress (full-width, horizontal stepper, bottom row) -->
                  <div class="pt-5 mt-2 border-t border-secondary-200">
                    <h4 class="text-sm font-medium text-secondary-500 mb-4">
                      Progress
                    </h4>
                    <div class="w-full overflow-x-auto pb-2">
                      <div class="flex items-start justify-between min-w-[600px]">
                        <template v-for="(step, idx) in (card.timeline && card.timeline.length ? card.timeline : [])" :key="step.key">
                          <div class="flex flex-col items-center flex-1 relative">
                            <!-- Circle -->
                            <div
                              :class="[
                                'h-7 w-7 rounded-full flex items-center justify-center border text-[10px] font-semibold z-10 shrink-0',
                                step.current
                                  ? 'bg-primary-500 border-primary-500 text-white ring-4 ring-primary-100'
                                  : step.completed
                                  ? 'bg-success-500 border-success-500 text-white'
                                  : 'bg-white border-secondary-300 text-secondary-400'
                              ]"
                            >
                              <Icon v-if="step.completed && !step.current" name="heroicons:check" class="h-3.5 w-3.5" />
                              <span v-else>{{ idx + 1 }}</span>
                            </div>
                            <!-- Connector line (hide for last step) -->
                            <div
                              v-if="idx < (card.timeline?.length || 0) - 1"
                              :class="[
                                'absolute top-3.5 left-1/2 w-full h-0.5 -z-0',
                                step.completed ? 'bg-success-400' : 'bg-secondary-200'
                              ]"
                            ></div>
                            <!-- Label under circle -->
                            <div class="mt-3 text-center px-1">
                              <p
                                :class="[
                                  'text-xs font-medium leading-tight',
                                  step.current
                                    ? 'text-primary-700'
                                    : step.completed
                                    ? 'text-secondary-900'
                                    : 'text-secondary-500'
                                ]"
                              >
                                {{ step.label }}
                              </p>
                              <p v-if="step.description" class="text-[10px] text-secondary-500 mt-0.5 leading-tight">
                                {{ step.description }}
                              </p>
                              <p v-if="step.date" class="text-[10px] text-secondary-400 mt-0.5">
                                {{ formatDate(step.date) }}
                              </p>
                            </div>
                          </div>
                        </template>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </Transition>
          </div>
        </div>

      <!-- No Cards Message (premium tapi empty) -->
      <div v-else-if="hasPremiumSubscription" class="text-center py-12">
        <div class="max-w-md mx-auto">
          <Icon
            name="heroicons:credit-card"
            class="h-16 w-16 text-secondary-400 mx-auto mb-4"
          />
          <h3 class="text-xl font-semibold text-secondary-900 mb-2">
            No Physical Cards
          </h3>
          <p class="text-secondary-600 mb-6">
            You haven't ordered any physical NFC cards yet. Order your first
            card to get started.
          </p>
          <button @click="orderNewCard" class="btn btn-primary">
            <Icon name="heroicons:plus" class="h-4 w-4 mr-2" />
            Order Your First Card
          </button>
        </div>
      </div>

      </template>
    </div>

    <!-- Order New Card Modal -->
    <Transition name="modal">
      <div v-if="showOrderModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
          <div
            class="fixed inset-0 bg-black bg-opacity-50"
            @click="showOrderModal = false"
          ></div>
          <div class="bg-white rounded-lg max-w-xl w-full p-6 relative max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
              <h3 class="text-xl font-semibold text-secondary-900">
                Order New NFC Card
              </h3>
              <button
                @click="showOrderModal = false"
                class="text-secondary-400 hover:text-secondary-600"
              >
                <Icon name="heroicons:x-mark" class="h-6 w-6" />
              </button>
            </div>

            <form @submit.prevent="submitOrder" class="space-y-5">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-secondary-700 mb-1"
                    >Card Owner</label
                  >
                  <input
                    v-model="orderForm.card_owner"
                    type="text"
                    class="input w-full"
                    required
                  />
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-secondary-700 mb-1"
                    >Billing Address</label
                  >
                  <textarea
                    v-model="orderForm.billing_address"
                    class="input w-full"
                    rows="2"
                    required
                  ></textarea>
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1"
                    >Contact Number</label
                  >
                  <input
                    v-model="orderForm.contact_number"
                    type="tel"
                    class="input w-full"
                    required
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1"
                    >Subscription Plan</label
                  >
                  <select
                    v-model="orderForm.subscription_plan"
                    class="input w-full"
                    required
                  >
                    <option value="basic">Basic - $29/month</option>
                    <option value="premium">Premium - $49/month</option>
                    <option value="business">Business - $99/month</option>
                  </select>
                </div>
              </div>

              <div class="border-t border-secondary-200 pt-5">
                <div class="flex items-center justify-between mb-3">
                  <h4 class="text-base font-semibold text-secondary-900 flex items-center">
                    <Icon name="heroicons:swatch" class="h-5 w-5 mr-2 text-primary-600" />
                    Card Design
                  </h4>
                  <div class="inline-flex rounded-lg bg-secondary-100 p-1">
                    <button
                      type="button"
                      @click="designMode = 'template'"
                      :class="[
                        'px-4 py-1.5 rounded-md text-sm font-medium transition-colors',
                        designMode === 'template'
                          ? 'bg-white text-primary-700 shadow-sm'
                          : 'text-secondary-600 hover:text-secondary-900'
                      ]"
                    >
                      Choose Template
                    </button>
                    <button
                      type="button"
                      @click="designMode = 'custom'"
                      :class="[
                        'px-4 py-1.5 rounded-md text-sm font-medium transition-colors',
                        designMode === 'custom'
                          ? 'bg-white text-primary-700 shadow-sm'
                          : 'text-secondary-600 hover:text-secondary-900'
                      ]"
                    >
                      Upload Custom
                    </button>
                  </div>
                </div>

                <div v-if="designMode === 'template'" class="space-y-3">
                  <div v-if="cardTemplatesLoading" class="text-center py-8 bg-secondary-50 rounded-lg border border-secondary-200">
                    <div class="spinner mx-auto mb-2"></div>
                    <p class="text-sm text-secondary-500">Loading designs...</p>
                  </div>
                  <template v-else-if="cardTemplates.length > 0">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-[360px] overflow-y-auto pr-1">
                      <label
                        v-for="tpl in cardTemplates"
                        :key="tpl.id"
                        :class="[
                          'group relative cursor-pointer rounded-xl border-2 overflow-hidden bg-white transition-all',
                          orderForm.card_template_id === tpl.id
                            ? 'border-primary-500 ring-4 ring-primary-100'
                            : 'border-secondary-200 hover:border-primary-300'
                        ]"
                      >
                        <input
                          type="radio"
                          name="card_template"
                          :value="tpl.id"
                          v-model="orderForm.card_template_id"
                          class="sr-only"
                        />
                        <div class="aspect-[4/3] bg-secondary-100 overflow-hidden">
                          <img
                            :src="tpl.thumbnail_url || tpl.front_image_url"
                            :alt="tpl.name"
                            class="w-full h-full object-cover"
                            loading="lazy"
                            onerror="this.style.visibility='hidden'"
                          />
                        </div>
                        <div class="p-3">
                          <p
                            :class="[
                              'text-sm font-semibold truncate',
                              orderForm.card_template_id === tpl.id
                                ? 'text-primary-700'
                                : 'text-secondary-900'
                            ]"
                          >
                            {{ tpl.name }}
                          </p>
                          <p class="text-xs text-secondary-500 mt-0.5 line-clamp-2" v-if="tpl.description">
                            {{ tpl.description }}
                          </p>
                          <div class="flex items-center gap-1 mt-2">
                            <span
                              v-if="tpl.category"
                              class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-secondary-100 text-secondary-700 capitalize"
                            >
                              {{ tpl.category }}
                            </span>
                          </div>
                        </div>
                        <div
                          v-if="orderForm.card_template_id === tpl.id"
                          class="absolute top-2 right-2 h-6 w-6 rounded-full bg-primary-500 flex items-center justify-center shadow"
                        >
                          <Icon name="heroicons:check" class="h-4 w-4 text-white" />
                        </div>
                      </label>
                    </div>
                  </template>
                  <div v-else class="text-center py-8 bg-secondary-50 rounded-lg border border-secondary-200 border-dashed">
                    <Icon name="heroicons:photo" class="h-10 w-10 text-secondary-300 mx-auto mb-2" />
                    <p class="text-sm font-medium text-secondary-700">No templates available</p>
                    <p class="text-xs text-secondary-500 mt-1">Try switching plan or use Upload Custom</p>
                  </div>
                  <p class="text-xs text-secondary-500 flex items-center">
                    <Icon name="heroicons:information-circle" class="h-3.5 w-3.5 mr-1" />
                    You will be able to customize the profile content after payment is verified.
                  </p>
                </div>

                <div v-else class="space-y-3">
                  <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-2">
                      Front Design
                      <span class="text-danger-500 ml-1">*</span>
                    </label>
                    <div
                      :class="[
                        'border-2 border-dashed rounded-lg p-5 text-center transition-colors cursor-pointer',
                        customDesignFront
                          ? 'border-primary-400 bg-primary-50/40'
                          : 'border-secondary-300 hover:border-primary-400 hover:bg-primary-50/30'
                      ]"
                      @click="$refs.customFrontInput.click()"
                    >
                      <input
                        ref="customFrontInput"
                        type="file"
                        class="hidden"
                        accept="image/jpeg,image/png,application/pdf"
                        @change="(e) => { const f = e.target.files; customDesignFront = f && f[0] ? markRaw(f[0]) : null; }"
                      />
                      <Icon
                        v-if="!customDesignFront"
                        name="heroicons:cloud-arrow-up"
                        class="h-9 w-9 text-secondary-400 mx-auto mb-2"
                      />
                      <div v-if="!customDesignFront" class="space-y-0.5">
                        <p class="text-sm font-medium text-secondary-700">
                          Click to upload front design
                        </p>
                        <p class="text-xs text-secondary-500">
                          JPG / PNG / PDF. Max 10MB
                        </p>
                      </div>
                      <div v-else class="space-y-1">
                        <div class="flex items-center justify-center gap-2 text-sm">
                          <Icon name="heroicons:document" class="h-5 w-5 text-primary-600 shrink-0" />
                          <span class="font-medium text-secondary-800 truncate max-w-[240px]">
                            {{ customDesignFront?.name }}
                          </span>
                        </div>
                        <p class="text-xs text-secondary-500">
                          {{ customDesignFront?.size != null ? (customDesignFront.size / 1024).toFixed(1) + ' KB' : '' }}
                        </p>
                        <button
                          type="button"
                          class="text-xs text-danger-600 hover:text-danger-800 font-medium mt-1"
                          @click.stop="customDesignFront = null"
                        >
                          Remove
                        </button>
                      </div>
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-2">
                      Back Design
                      <span class="text-secondary-400 font-normal text-xs ml-1">(Optional)</span>
                    </label>
                    <div
                      :class="[
                        'border-2 border-dashed rounded-lg p-5 text-center transition-colors cursor-pointer',
                        customDesignBack
                          ? 'border-primary-400 bg-primary-50/40'
                          : 'border-secondary-300 hover:border-primary-400 hover:bg-primary-50/30'
                      ]"
                      @click="$refs.customBackInput.click()"
                    >
                      <input
                        ref="customBackInput"
                        type="file"
                        class="hidden"
                        accept="image/jpeg,image/png,application/pdf"
                        @change="(e) => { const f = e.target.files; customDesignBack = f && f[0] ? markRaw(f[0]) : null; }"
                      />
                      <Icon
                        v-if="!customDesignBack"
                        name="heroicons:cloud-arrow-up"
                        class="h-9 w-9 text-secondary-400 mx-auto mb-2"
                      />
                      <div v-if="!customDesignBack" class="space-y-0.5">
                        <p class="text-sm font-medium text-secondary-700">
                          Click to upload back design
                        </p>
                        <p class="text-xs text-secondary-500">
                          JPG / PNG / PDF. Max 10MB
                        </p>
                      </div>
                      <div v-else class="space-y-1">
                        <div class="flex items-center justify-center gap-2 text-sm">
                          <Icon name="heroicons:document" class="h-5 w-5 text-primary-600 shrink-0" />
                          <span class="font-medium text-secondary-800 truncate max-w-[240px]">
                            {{ customDesignBack?.name }}
                          </span>
                        </div>
                        <p class="text-xs text-secondary-500">
                          {{ customDesignBack?.size != null ? (customDesignBack.size / 1024).toFixed(1) + ' KB' : '' }}
                        </p>
                        <button
                          type="button"
                          class="text-xs text-danger-600 hover:text-danger-800 font-medium mt-1"
                          @click.stop="customDesignBack = null"
                        >
                          Remove
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1"
                  >Design Notes / Instructions <span class="text-secondary-400 font-normal text-xs">(Optional)</span></label
                >
                <textarea
                  v-model="orderForm.design_notes"
                  class="input w-full"
                  rows="2"
                  placeholder="Any special instructions for printing, colors, typography, etc."
                ></textarea>
              </div>

              <div class="border-t border-secondary-200 pt-4 space-y-4">
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1"
                    >Shipping Address <span class="text-secondary-400 font-normal text-xs">(Optional)</span></label
                  >
                  <textarea
                    v-model="orderForm.shipping_address"
                    class="input w-full"
                    rows="2"
                  ></textarea>
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-1"
                    >Order Notes <span class="text-secondary-400 font-normal text-xs">(Optional)</span></label
                  >
                  <textarea
                    v-model="orderForm.notes"
                    class="input w-full"
                    rows="2"
                  ></textarea>
                </div>
              </div>

              <div class="flex items-center justify-end space-x-3 pt-2">
                <button
                  type="button"
                  @click="showOrderModal = false"
                  class="btn btn-outline"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="btn btn-primary"
                  :disabled="orderLoading || (designMode === 'custom' && !customDesignFront) || (designMode === 'template' && !orderForm.card_template_id)"
                >
                  <div v-if="orderLoading" class="spinner mr-2"></div>
                  Place Order
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Card Details Modal (Read-only) -->
    <Transition name="modal">
      <div
        v-if="showCardDetailsModal && selectedCard"
        class="fixed inset-0 z-50 overflow-y-auto"
      >
        <div class="flex items-center justify-center min-h-screen px-4">
          <div
            class="fixed inset-0 bg-black bg-opacity-50"
            @click="showCardDetailsModal = false"
          ></div>
          <div
            class="bg-white rounded-lg max-w-3xl w-full p-6 relative max-h-[90vh] overflow-y-auto"
          >
            <div class="flex items-center justify-between mb-6">
              <div>
                <h3 class="text-xl font-semibold text-secondary-900">
                  Card Details
                </h3>
                <p class="text-sm text-secondary-600 mt-1">
                  View all information for {{ selectedCard.card_owner }}'s card
                </p>
              </div>
              <button
                @click="showCardDetailsModal = false"
                class="text-secondary-400 hover:text-secondary-600"
              >
                <Icon name="heroicons:x-mark" class="h-6 w-6" />
              </button>
            </div>

            <div class="space-y-6">
              <!-- Status Badge -->
              <div
                class="flex items-center justify-between p-4 bg-secondary-50 rounded-lg"
              >
                <div>
                  <p class="text-sm text-secondary-600">Status</p>
                  <p
                    class="text-lg font-semibold text-secondary-900 capitalize"
                  >
                    {{ selectedCard.status }}
                  </p>
                </div>
                <span
                  :class="getStatusBadgeClass(selectedCard.status_badge)"
                  class="px-4 py-2 rounded-full text-sm font-medium"
                >
                  {{ selectedCard.status_badge }}
                </span>
              </div>

              <!-- Card Information -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                  <h4
                    class="text-sm font-semibold text-secondary-900 uppercase tracking-wide"
                  >
                    Card Information
                  </h4>

                  <div>
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Card ID</label
                    >
                    <p
                      class="text-base text-secondary-900 font-mono bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ selectedCard.card_id }}
                    </p>
                  </div>

                  <div>
                    <label class="block text-sm text-secondary-600 mb-1"
                      >NFC Card ID</label
                    >
                    <p
                      class="text-base text-secondary-900 font-mono bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ selectedCard.nfc_card_id || "Not assigned" }}
                    </p>
                  </div>

                  <div>
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Card Owner</label
                    >
                    <p
                      class="text-base text-secondary-900 bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ selectedCard.card_owner }}
                    </p>
                  </div>

                  <div>
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Subscription Plan</label
                    >
                    <p
                      class="text-base text-secondary-900 capitalize bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ String(selectedCard.subscription_plan || '').replace(/\b\w/g, c => c.toUpperCase()) }}
                    </p>
                  </div>
                </div>

                <div class="space-y-4">
                  <h4
                    class="text-sm font-semibold text-secondary-900 uppercase tracking-wide"
                  >
                    Contact Information
                  </h4>

                  <div>
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Contact Number</label
                    >
                    <p
                      class="text-base text-secondary-900 bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ selectedCard.contact_number }}
                    </p>
                  </div>

                  <div>
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Billing Address</label
                    >
                    <p
                      class="text-base text-secondary-900 bg-secondary-50 px-3 py-2 rounded whitespace-pre-wrap"
                    >
                      {{ selectedCard.billing_address }}
                    </p>
                  </div>

                  <div v-if="selectedCard.shipping_address">
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Shipping Address</label
                    >
                    <p
                      class="text-base text-secondary-900 bg-secondary-50 px-3 py-2 rounded whitespace-pre-wrap"
                    >
                      {{ selectedCard.shipping_address }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Financial & Timeline Information -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                  <h4
                    class="text-sm font-semibold text-secondary-900 uppercase tracking-wide"
                  >
                    Financial Details
                  </h4>

                  <div>
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Purchase Amount</label
                    >
                    <p
                      class="text-lg font-semibold text-primary-600 bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ selectedCard.formatted_purchase_amount }}
                    </p>
                  </div>

                  <div v-if="selectedCard.payment_method">
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Payment Method</label
                    >
                    <p
                      class="text-base text-secondary-900 capitalize bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ selectedCard.payment_method }}
                    </p>
                  </div>
                </div>

                <div class="space-y-4">
                  <h4
                    class="text-sm font-semibold text-secondary-900 uppercase tracking-wide"
                  >
                    Timeline
                  </h4>

                  <div>
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Purchase Date</label
                    >
                    <p
                      class="text-base text-secondary-900 bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ formatDate(selectedCard.purchase_date) }}
                    </p>
                  </div>

                  <div v-if="selectedCard.shipped_date">
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Shipped Date</label
                    >
                    <p
                      class="text-base text-secondary-900 bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ formatDate(selectedCard.shipped_date) }}
                    </p>
                  </div>

                  <div v-if="selectedCard.delivered_date">
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Delivered Date</label
                    >
                    <p
                      class="text-base text-secondary-900 bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ formatDate(selectedCard.delivered_date) }}
                    </p>
                  </div>

                  <div v-if="selectedCard.expiry_date">
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Expiry Date</label
                    >
                    <p
                      class="text-base text-secondary-900 bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ formatDate(selectedCard.expiry_date) }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Shipping Information -->
              <div v-if="selectedCard.tracking_number" class="space-y-4">
                <h4
                  class="text-sm font-semibold text-secondary-900 uppercase tracking-wide"
                >
                  Shipping Information
                </h4>

                <div>
                  <label class="block text-sm text-secondary-600 mb-1"
                    >Tracking Number</label
                  >
                  <p
                    class="text-base text-secondary-900 font-mono bg-secondary-50 px-3 py-2 rounded"
                  >
                    {{ selectedCard.tracking_number }}
                  </p>
                </div>
              </div>

              <!-- Notes -->
              <div v-if="selectedCard.notes" class="space-y-4">
                <h4
                  class="text-sm font-semibold text-secondary-900 uppercase tracking-wide"
                >
                  Notes
                </h4>

                <div class="bg-secondary-50 px-3 py-2 rounded">
                  <p class="text-base text-secondary-900 whitespace-pre-wrap">
                    {{ selectedCard.notes }}
                  </p>
                </div>
              </div>

              <!-- NFC Tag Information -->
              <div v-if="selectedCard.nfcTag" class="space-y-4 border-t pt-6">
                <h4
                  class="text-sm font-semibold text-secondary-900 uppercase tracking-wide"
                >
                  Linked Profile
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm text-secondary-600 mb-1"
                      >NFC Tag ID</label
                    >
                    <p
                      class="text-base text-secondary-900 font-mono bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ selectedCard.nfcTag.nfc_id }}
                    </p>
                  </div>

                  <div>
                    <label class="block text-sm text-secondary-600 mb-1"
                      >Profile Name</label
                    >
                    <p
                      class="text-base text-secondary-900 bg-secondary-50 px-3 py-2 rounded"
                    >
                      {{ selectedCard.nfcTag.name }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div
                class="flex items-center justify-between pt-6 border-t"
              >
                <div v-if="!cardCanDesignProfile(selectedCard)" class="flex items-center space-x-2">
                  <Icon name="heroicons:lock-closed" class="h-4 w-4 text-warning-500" />
                  <p class="text-xs text-warning-700">
                    Profile Builder access will be unlocked after payment is verified by admin
                  </p>
                </div>
                <div class="flex items-center justify-end space-x-3 ml-auto">
                  <button
                    @click="showCardDetailsModal = false"
                    class="btn btn-outline"
                  >
                    Close
                  </button>
                  <button
                    @click="goToProfileBuilder"
                    class="btn btn-primary"
                    :disabled="!cardCanDesignProfile(selectedCard)"
                    :class="!cardCanDesignProfile(selectedCard) && 'opacity-50 cursor-not-allowed'"
                  >
                    <Icon name="heroicons:pencil-square" class="h-4 w-4 mr-2" />
                    {{
                      selectedCard.nfcTag
                        ? "Design Profile"
                        : "Design Landing Page"
                    }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Upload Payment Proof Modal -->
    <Transition name="modal">
      <div v-if="showPaymentProofModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
          <div
            class="fixed inset-0 bg-black bg-opacity-50"
            @click="showPaymentProofModal = false"
          ></div>
          <div class="bg-white rounded-lg max-w-lg w-full p-6 relative">
            <div class="flex items-center justify-between mb-6">
              <div>
                <h3 class="text-lg font-semibold text-secondary-900">
                  Upload Payment Proof
                </h3>
                <p v-if="selectedCardForUpload" class="text-sm text-secondary-600 mt-1">
                  Card Order ID: {{ selectedCardForUpload.card_id || selectedCardForUpload.nfc_card_id }}
                </p>
              </div>
              <button
                @click="showPaymentProofModal = false"
                class="text-secondary-400 hover:text-secondary-600"
              >
                <Icon name="heroicons:x-mark" class="h-6 w-6" />
              </button>
            </div>

            <form @submit.prevent="uploadPaymentProof" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-2">
                  Payment Proof File
                  <span class="text-danger-500">*</span>
                </label>
                <div
                  class="border-2 border-dashed border-secondary-300 rounded-lg p-6 text-center hover:border-primary-400 hover:bg-primary-50/50 transition-colors cursor-pointer"
                  @click="$refs.paymentProofFileInput.click()"
                >
                  <input
                    ref="paymentProofFileInput"
                    type="file"
                    class="hidden"
                    accept="image/*,application/pdf"
                    @change="handlePaymentProofFileChange"
                  />
                  <Icon
                    name="heroicons:cloud-arrow-up"
                    class="h-10 w-10 text-secondary-400 mx-auto mb-3"
                  />
                  <div v-if="!paymentProofForm.file" class="space-y-1">
                    <p class="text-sm font-medium text-secondary-700">
                      Click to select a file or drag & drop
                    </p>
                    <p class="text-xs text-secondary-500">
                      Accepted formats: JPG, PNG, PDF. Max size: 5MB
                    </p>
                  </div>
                  <div v-else class="space-y-2">
                    <div class="flex items-center justify-center space-x-2 text-sm">
                      <Icon
                        name="heroicons:document"
                        class="h-5 w-5 text-primary-600"
                      />
                      <span class="font-medium text-secondary-800 truncate max-w-[240px]">
                        {{ paymentProofForm.file.name }}
                      </span>
                    </div>
                    <p class="text-xs text-secondary-500">
                      {{ (paymentProofForm.file.size / 1024).toFixed(1) }} KB
                    </p>
                  </div>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">
                  Bank Name
                </label>
                <input
                  v-model="paymentProofForm.bank_name"
                  type="text"
                  placeholder="Example: Maybank, CIMB, Public Bank"
                  class="input w-full"
                />
                <p class="text-xs text-secondary-500 mt-1">
                  Optional: The bank you used to make the payment
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">
                  Transaction Reference No.
                </label>
                <input
                  v-model="paymentProofForm.reference_code"
                  type="text"
                  placeholder="Example: 20260929ABC12345"
                  class="input w-full"
                />
                <p class="text-xs text-secondary-500 mt-1">
                  Optional: Transaction reference number to help admin verify
                </p>
              </div>

              <div class="bg-secondary-50 rounded-lg p-4 border border-secondary-200">
                <div class="flex items-start space-x-2">
                  <Icon name="heroicons:information-circle" class="h-5 w-5 text-secondary-500 shrink-0 mt-0.5" />
                  <div class="space-y-1 text-sm">
                    <p class="font-medium text-secondary-700">
                      After uploading:
                    </p>
                    <ul class="text-secondary-600 space-y-0.5 list-disc list-inside">
                      <li>Admin will review the payment proof within 24 hours</li>
                      <li>Once verified, you can start designing your profile</li>
                      <li>You will receive a notification after verification</li>
                    </ul>
                  </div>
                </div>
              </div>

              <div class="flex items-center justify-end space-x-3 pt-2">
                <button
                  type="button"
                  @click="showPaymentProofModal = false"
                  class="btn btn-outline"
                  :disabled="uploadLoading"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="btn btn-primary"
                  :disabled="uploadLoading || _uploadProofLock || !paymentProofForm.file"
                  :aria-busy="uploadLoading"
                >
                  <div v-if="uploadLoading" class="spinner mr-2"></div>
                  {{ uploadLoading ? 'Submitting...' : 'Submit Payment Proof' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { markRaw, toRaw } from "vue";
import { useAuthStore } from "~/stores/auth";

// Layout
definePageMeta({
  layout: "user-dashboard",
});

// Stores
const authStore = useAuthStore();
const { $toast, $api } = useNuxtApp();

// Reactive data
const loading = ref(true);
const nfcCards = ref([]);
const subscriptionData = ref(null);
const showOrderModal = ref(false);
const orderLoading = ref(false);
const showCardDetailsModal = ref(false);
const selectedCard = ref(null);
const expandedCardId = ref(null);

const toggleExpand = (cardId) => {
  if (expandedCardId.value === cardId) {
    expandedCardId.value = null;
  } else {
    expandedCardId.value = cardId;
  }
};

// ─── PERFORMANCE: Request-promise cache to eliminate duplicate /nfc-cards call ───
// Guard check on line ~1620 used to call /nfc-cards ONCE just to check array length,
// then loadNfcCards() called it AGAIN for full payload.  Now both paths share
// a single cached promise → 1 network call instead of 2.
let _nfcCardsPromise = null;
const _getNfcCardsCached = async () => {
  if (!_nfcCardsPromise) {
    _nfcCardsPromise = $api.get("/nfc-cards");
  }
  return _nfcCardsPromise;
};
// Invalidate cache after mutations (upload proof, order, deactivate, etc)
const _invalidateNfcCardsCache = () => { _nfcCardsPromise = null; };

/**
 * Resolve the PRETTIEST public profile URL for a given card object.
 * Priority 1 — Server-computed `card.public_url`:
 *    Auto decides SHORT "/profile/a/1" (name unique) OR LONG "/profile/a/1/userid-{pk}" (name clash).
 * Priority 2 — Derive locally using authStore user slug + local card index.
 * Priority 3 — Legacy fallbacks: nfc_card_id or numeric pk id.
 */
const getCardPublicUrl = (card, opts = {}) => {
  if (!card) return '';
  const { preview = false, suffix = '' } = opts;
  let base = '';

  if (typeof card.public_url === 'string' && card.public_url.startsWith('/profile/')) {
    base = card.public_url;
  }
  if (!base) {
    const authUser = authStore.user || {};
    const userSlug = authUser.name_slug;
    // Note: CardManagement.vue calls its array `nfcCards` not `userNfcCards`; handle both:
    const cardsSource =
      (typeof userNfcCards !== 'undefined' && userNfcCards && userNfcCards.value) ? userNfcCards.value :
      (typeof nfcCards !== 'undefined' && nfcCards && nfcCards.value) ? nfcCards.value :
      null;
    const localCardNumber = cardsSource && cardsSource.length > 0
      ? cardsSource
          .slice()
          .sort((a, b) => (new Date(a.created_at || a.createdAt || 0) - new Date(b.created_at || b.createdAt || 0)) || ((a.id ?? 0) - (b.id ?? 0)))
          .findIndex(c => c.id === card.id) + 1
      : null;
    if (userSlug && (localCardNumber || card.card_number)) {
      base = `/profile/${userSlug}/${localCardNumber || card.card_number}`;
      if (authUser.id != null && authUser.has_name_slug_clash === true) {
        base += '/userid-' + authUser.id;
      }
    }
  }
  if (!base) {
    base = `/profile/${card.nfc_card_id || card.id}`;
  }

  const qs = [];
  if (preview) qs.push('preview=true');
  if (suffix) qs.push(suffix.replace(/^\?/, ''));
  const qsStr = qs.length ? '?' + qs.join('&') : '';
  return base + qsStr;
};

// Internal single-param API identifier: encodes the multi-segment URL into the [id] param
// using "__" (double underscore) as path separator (frontend single-route-param limitation).
// Supports both 2-segment (short) and 3-segment (name clash + userid pk) URLs.
const getCardApiIdentifier = (card) => {
  if (!card) return '';
  if (typeof card.public_url === 'string' && card.public_url.startsWith('/profile/')) {
    const path = card.public_url.substring('/profile/'.length);
    if (path && path.includes('/')) {
      return path.split('/').filter(Boolean).join('__');
    }
    if (path) return path;
  }
  const authUser = authStore.user || {};
  const userSlug = authUser.name_slug;
  if (userSlug) {
    const cardsSource =
      (typeof userNfcCards !== 'undefined' && userNfcCards && userNfcCards.value) ? userNfcCards.value :
      (typeof nfcCards !== 'undefined' && nfcCards && nfcCards.value) ? nfcCards.value :
      null;
    const localNumber = card.card_number ??
      (cardsSource && cardsSource.length > 0
        ? (cardsSource
            .slice()
            .sort((a, b) => (new Date(a.created_at || a.createdAt || 0) - new Date(b.created_at || b.createdAt || 0)) || ((a.id ?? 0) - (b.id ?? 0)))
            .findIndex(c => c.id === card.id) + 1)
        : null);
    if (localNumber) {
      const two = `${userSlug}__${localNumber}`;
      if (authUser.id != null && authUser.has_name_slug_clash === true) {
        return `${two}__userid-${authUser.id}`;
      }
      return two;
    }
  }
  return card.nfc_card_id || card.id;
};

const orderForm = ref({
  card_owner: "",
  billing_address: "",
  contact_number: "",
  subscription_plan: "premium",
  shipping_address: "",
  notes: "",
  card_template_id: null,
  is_custom_design: false,
  design_notes: "",
});

const cardTemplates = ref([]);
const cardTemplatesLoading = ref(false);
const designMode = ref('template');
const customDesignFront = ref(null);
const customDesignBack = ref(null);

// Payment Proof Upload State
const showPaymentProofModal = ref(false);
const selectedCardForUpload = ref(null);
const uploadLoading = ref(false);
const paymentProofForm = ref({
  file: null,
  reference_code: "",
  bank_name: "",
});

// ⚠️ PLAIN OBJECT (not reactive) for payment proof File object — 100% Vue Proxy bypass
const __paymentProofFile = Object.create(null);
__paymentProofFile.value = null;

let _uploadProofLock = false;
let _uploadProofAbort = null;

// Confirm received state
const confirmLoading = ref({ id: null, loading: false });

// ─── Computed access permissions (3-TIER FALLBACK for ZERO FLICKER) ───────
// TIER 1 — authStore.user.subscription_plan: AVAILABLE INSTANTLY at render
//          time (populated by auth middleware initAuth → GET /me that ran
//          BEFORE page component even mounts). No API wait needed — this is
//          the authoritative guard that eliminates the 300ms "Premium padlock"
//          flash the user was seeing.
// TIER 2 — subscriptionData from /subscription/status: authoritative AFTER
//          load completes. Contains fine-grained flags like has_premium_subscription.
// TIER 3 — nfcCards.length >= 1: user with ANY paid card order still needs access.
const useAuthStoreForPlan = () => {
  const plan = String(authStore.user?.subscription_plan || '').toLowerCase();
  if (['basic', 'premium', 'business', 'free'].includes(plan)) return plan;
  return null;
};
const _userPlanFromAuth = useAuthStoreForPlan();

/**
 * True if current user should be TREATED as a Premium+ subscriber.
 * Uses a 3-tier progressive check so zero flash occurs between render and
 * the /subscription-status API returning.
 */
const hasPremiumSubscription = computed(() => {
  // ── TIER 1: authStore (instant, no waiting) ────────────────────────────
  if (_userPlanFromAuth === 'premium' || _userPlanFromAuth === 'business') {
    return true;
  }

  // ── TIER 2: subscriptionData from backend /subscription/status ─────────
  if (subscriptionData.value?.has_premium_subscription) {
    return true;
  }
  // Also accept subscription_plan on subscriptionData object itself:
  const subPlan = String(subscriptionData.value?.subscription_plan || '').toLowerCase();
  if (['premium', 'business'].includes(subPlan)) {
    return true;
  }

  // ── TIER 3: fallback — user has at least 1 NFC card (any order status) ─
  if (nfcCards.value && nfcCards.value.length > 0) {
    return true;
  }
  return false;
});

// Helpers — use label/badge from backend (NfcCard model accessors when available
const cardCanDesignProfile = (card) => {
  if (typeof card.can_access_profile_builder !== undefined) {
    return !!card.can_access_profile_builder;
  }
  return ["payment_verified","processing","shipped","delivered","active"].includes(card.status);
};

// Methods
const loadSubscriptionStatus = async () => {
  try {
    const response = await $api.get("/subscription/status");
    if (response.success) {
      subscriptionData.value = response.data;
    }
  } catch (error) {
    console.error("Failed to load subscription status:", error);
  }
};

const loadNfcCards = async () => {
  try {
    const response = await _getNfcCardsCached();
    if (response.success) {
      nfcCards.value = (response.nfc_cards || []).map((card) => {
        const planFallback = card.subscription_plan || subscriptionData.value?.subscription_plan || "basic";
        const timeline = buildDefaultTimeline(card);
        return {
          ...card,
          subscription_plan: planFallback,
          timeline,
        };
      });
    }
  } catch (error) {
    if (
      error.response?.status === 403 &&
      error.response?.data?.upgrade_required
    ) {
      if (nfcCards.value.length === 0) {
        // Leave empty
      } else {
        $toast.info(error.response.data.message || "This feature requires a Premium subscription");
      }
    } else {
      $toast.error("Failed to load NFC cards");
      console.error("Failed to load NFC cards:", error);
    }
  }
  // NOTE: loading.value = false intentionally REMOVED from here.
  // Centralized in onMounted() finally block so subscription + cards parallel race doesn't dismiss spinner too early.
};

/**
 * Default ENGLISH timeline for any NFC card (used if API doesn't send `card.timeline`).
 * Status order follows the 11 lifecycle states defined in project memory.
 * All labels are EN by default.
 */
const STATUS_ORDER = [
  { key: "pending_payment", label: "Order Submitted", desc: "Your order has been received and is awaiting payment" },
  { key: "awaiting_payment_verification", label: "Payment Proof Uploaded", desc: "Payment proof has been submitted" },
  { key: "payment_verified", label: "Payment Verified", desc: "Admin has verified your payment. You can now start designing your profile!" },
  { key: "processing", label: "Processing Order", desc: "We are preparing your NFC card" },
  { key: "shipped", label: "Shipped", desc: "Card has been shipped with tracking" },
  { key: "delivered", label: "Delivered", desc: "You have received the card. Enjoy using NFCGo!" },
  { key: "active", label: "Active", desc: "Card subscription is active and live" },
  { key: "inactive", label: "Inactive", desc: "Card is temporarily paused" },
  { key: "expired", label: "Expired", desc: "Subscription has expired" },
  { key: "replacement", label: "Replacement", desc: "A replacement card has been requested" },
  { key: "cancelled", label: "Cancelled", desc: "Order has been cancelled" },
];

const buildDefaultTimeline = (card) => {
  if (!card?.status) return [];
  const currentStatusIndex = STATUS_ORDER.findIndex((s) => s.key === card.status);
  if (currentStatusIndex === -1) return [];

  const finalKeys = [
    "pending_payment",
    card.status === "pending_payment" ? null : "awaiting_payment_verification",
    "payment_verified",
    "processing",
    "shipped",
    "delivered",
  ].filter(Boolean);

  return finalKeys
    .map((key, idx) => {
      const meta = STATUS_ORDER.find((s) => s.key === key);
      if (!meta) return null;
      const keyIndex = STATUS_ORDER.findIndex((s) => s.key === key);
      const isCurrent = key === card.status;
      const isCompleted = keyIndex < currentStatusIndex;
      const dateField =
        key === "pending_payment"
          ? card.created_at || card.purchase_date
          : key === "payment_verified"
          ? card.payment_verified_at
          : key === "processing"
          ? card.processing_started_at
          : key === "shipped"
          ? card.shipped_date
          : key === "delivered"
          ? card.delivered_date
          : card[`${key}_at`];
      return {
        key: meta.key,
        label: meta.label,
        description: meta.desc,
        date: dateField || null,
        current: isCurrent,
        completed: isCompleted,
      };
    })
    .filter(Boolean);
};

const upgradeToPremium = () => {
  // Redirect to upgrade page or show upgrade modal
  $toast.info("Redirecting to upgrade page...");
  // navigateTo('/upgrade')
};

const loadCardTemplates = async (plan) => {
  cardTemplatesLoading.value = true;
  try {
    const res = await $api.get("/card-templates", { plan });
    if (res?.success && Array.isArray(res.data)) {
      cardTemplates.value = res.data;
    } else {
      cardTemplates.value = [];
    }
  } catch (e) {
    console.error("Failed to load card templates:", e);
    cardTemplates.value = [];
  } finally {
    cardTemplatesLoading.value = false;
  }
};

const orderNewCard = () => {
  const user = authStore.user;
  orderForm.value.card_owner = user?.full_name || "";
  orderForm.value.contact_number = user?.phone || "";
  orderForm.value.card_template_id = null;
  orderForm.value.is_custom_design = false;
  orderForm.value.design_notes = "";
  designMode.value = "template";
  customDesignFront.value = null;
  customDesignBack.value = null;
  showOrderModal.value = true;
  void loadCardTemplates(orderForm.value.subscription_plan);
};

watch(() => orderForm.value.subscription_plan, (newPlan) => {
  if (showOrderModal.value && newPlan) {
    void loadCardTemplates(newPlan);
  }
});

const submitOrder = async () => {
  const useCustom = designMode.value === "custom";
  orderForm.value.is_custom_design = useCustom;
  if (!useCustom) {
    orderForm.value.is_custom_design = false;
  }

  const hasFiles = !!(customDesignFront.value || customDesignBack.value);

  orderLoading.value = true;
  try {
    let response;

    if (useCustom && hasFiles) {
      const formData = new FormData();
      formData.append("card_owner", orderForm.value.card_owner);
      formData.append("billing_address", orderForm.value.billing_address);
      formData.append("contact_number", orderForm.value.contact_number);
      formData.append("subscription_plan", orderForm.value.subscription_plan);
      formData.append("purchase_amount", String(getPlanPrice(orderForm.value.subscription_plan)));
      formData.append("shipping_address", orderForm.value.shipping_address || "");
      formData.append("notes", orderForm.value.notes || "");
      formData.append("is_custom_design", "1");
      if (orderForm.value.design_notes) {
        formData.append("design_notes", orderForm.value.design_notes);
      }
      if (customDesignFront.value) {
        formData.append("custom_design_front", toRaw(customDesignFront.value), customDesignFront.value.name);
      }
      if (customDesignBack.value) {
        formData.append("custom_design_back", toRaw(customDesignBack.value), customDesignBack.value.name);
      }
      response = await $api.post("/nfc-cards", formData);
    } else {
      const payload = {
        ...orderForm.value,
        card_template_id: useCustom ? null : orderForm.value.card_template_id,
        is_custom_design: useCustom,
        purchase_amount: getPlanPrice(orderForm.value.subscription_plan),
      };
      if (useCustom && orderForm.value.design_notes) {
        payload.design_notes = orderForm.value.design_notes;
      }
      response = await $api.post("/nfc-cards", payload);
    }

    if (response.success) {
      $toast.success("NFC card order placed successfully!");
      showOrderModal.value = false;
      await loadNfcCards();
      await loadSubscriptionStatus();
    }
  } catch (error) {
    if (
      error.response?.status === 403 &&
      error.response?.data?.upgrade_required
    ) {
      $toast.error("This feature requires a Premium subscription");
    } else if (error.response?.data?.errors) {
      const errs = error.response.data.errors;
      const first = Object.values(errs)[0];
      $toast.error(Array.isArray(first) ? first[0] : String(first));
    } else {
      $toast.error("Failed to place order");
      console.error("Order error:", error);
    }
  } finally {
    orderLoading.value = false;
  }
};

const editCard = (card) => {
  selectedCard.value = card;
  showCardDetailsModal.value = true;
};

const goToProfileBuilder = () => {
  if (!selectedCard.value) {
    $toast.error("No card selected");
    return;
  }
  goToProfileBuilderForCard(selectedCard.value);
};

/**
 * Direct navigate to Profile Builder for a SPECIFIC card (no global selectedCard required).
 * Usage: click Design Profile button on any card row → terus buka builder untuk card tu.
 */
const goToProfileBuilderForCard = (card) => {
  if (!card) {
    $toast.error("No card selected");
    return;
  }
  if (!cardCanDesignProfile(card)) {
    $toast.error("Design access will unlock after payment is verified by admin");
    return;
  }

  // Plan priority: guna card.subscription_plan DULU (specific per card), baru fallback user global
  const cardPlan = String(card.subscription_plan || "").toLowerCase();
  const userPlan = authStore.user?.subscription_plan?.toLowerCase() || "free";
  const effectivePlan = cardPlan || userPlan;

  let profileBuilderPath = "";

  if (effectivePlan === "premium") {
    profileBuilderPath =
      "/UserDashboard/UserManagement/PremiumPlanUser/PremiumProfileBuilder";
  } else if (effectivePlan === "business") {
    profileBuilderPath =
      "/UserDashboard/UserManagement/BusinessPlanUser/BusinessProfileBuilder";
  } else if (effectivePlan === "basic") {
    profileBuilderPath =
      "/UserDashboard/UserManagement/BasicPlanUser/BasicProfileBuilder";
  } else {
    profileBuilderPath = "/UserDashboard/ProfileBuilder";
  }

  // Priority: nfcTag.id → nfc_card_id → card.id
  const qsParts = [];
  if (card.nfcTag?.id) {
    qsParts.push(`nfc_tag_id=${encodeURIComponent(card.nfcTag.id)}`);
  }
  if (card.nfc_card_id) {
    qsParts.push(`nfc_card_id=${encodeURIComponent(card.nfc_card_id)}`);
  }
  qsParts.push(`card_id=${encodeURIComponent(card.id)}`);

  navigateTo(`${profileBuilderPath}?${qsParts.join("&")}`);
};

const activateCard = async (card) => {
  try {
    const response = await $api.post(`/nfc-cards/${card.id}/activate`, {
      nfc_id: `NFC-${Math.random().toString(36).substr(2, 9).toUpperCase()}`,
      name: card.card_owner,
    });

    if (response.success) {
      $toast.success("Card activated successfully");
      _invalidateNfcCardsCache();
      await Promise.all([loadNfcCards(), loadSubscriptionStatus()]);
    }
  } catch (error) {
    $toast.error("Failed to activate card");
    console.error("Activate error:", error);
  }
};

const deactivateCard = async (card) => {
  if (!confirm("Are you sure you want to deactivate this card?")) return;

  try {
    const response = await $api.post(`/nfc-cards/${card.id}/deactivate`);

    if (response.success) {
      $toast.success("Card deactivated successfully");
      _invalidateNfcCardsCache();
      await loadNfcCards();
    }
  } catch (error) {
    $toast.error("Failed to deactivate card");
    console.error("Deactivate error:", error);
  }
};

// Payment proof upload flow
const openUploadPaymentProof = (card) => {
  selectedCardForUpload.value = card;
  paymentProofForm.value = {
    file: null,
    reference_code: "",
    bank_name: "",
  };
  __paymentProofFile.value = null;
  showPaymentProofModal.value = true;
};

const handlePaymentProofFileChange = (event) => {
  const files = event.target.files;
  if (files && files.length > 0) {
    const f = files[0];
    __paymentProofFile.value = f;              // plain object = native File, NO Vue Proxy
    paymentProofForm.value.file = f;           // keep form reactive for UI (v-if display)
  }
};

const uploadPaymentProof = async () => {
  if (_uploadProofLock) return;
  if (!selectedCardForUpload.value) return;

  // Always prefer plain-object native File (bypass Vue proxy)
  const proofFile = __paymentProofFile.value
    ?? paymentProofForm.value.file;
  if (!(proofFile instanceof File) || proofFile.size === 0) {
    $toast.error("Please select a valid payment proof file");
    return;
  }

  if (_uploadProofAbort) {
    try { _uploadProofAbort.abort(); } catch (_) {}
    _uploadProofAbort = null;
  }
  _uploadProofLock = true;
  uploadLoading.value = true;

  try {
    const formData = new FormData();
    formData.append("payment_proof", proofFile, proofFile.name || "payment-proof.png");
    if (paymentProofForm.value.reference_code && String(paymentProofForm.value.reference_code).trim()) {
      formData.append("reference_code", String(paymentProofForm.value.reference_code).trim());
    }
    if (paymentProofForm.value.bank_name && String(paymentProofForm.value.bank_name).trim()) {
      formData.append("bank_name", String(paymentProofForm.value.bank_name).trim());
    }

    if (typeof window !== 'undefined' && console?.debug) {
      const entries = [];
      for (const [k, v] of formData.entries()) {
        if (v instanceof File) entries.push(`  ${k}: File[name=${v.name}, size=${v.size}, type=${v.type}]`);
        else entries.push(`  ${k}: ${String(v)}`);
      }
      console.debug('[uploadPaymentProof] FormData:\n' + entries.join('\n'));
    }

    _uploadProofAbort = new AbortController();
    const response = await $api.post(
      `/nfc-cards/${selectedCardForUpload.value.id}/upload-payment-proof`,
      formData,
      { signal: _uploadProofAbort.signal }
    );

    if (response?.success === true) {
      $toast.success(
        response.message || "Payment proof submitted successfully!"
      );
      showPaymentProofModal.value = false;
      _invalidateNfcCardsCache();
      await Promise.all([loadNfcCards(), loadSubscriptionStatus()]);
    } else {
      const msg = response?.message || "Failed to submit payment proof";
      $toast.error(msg);
      console.warn("[uploadPaymentProof] backend success=false:", response);
    }
  } catch (error) {
    if (error?.name === 'CanceledError' || error?.code === 'ERR_CANCELED') return;
    console.error("Upload payment proof error:", error);
    const errData = error?.data || error?.response?.data || {};
    const flattenErrors = (errs) => {
      if (!errs || typeof errs !== 'object') return '';
      const out = [];
      for (const val of Object.values(errs)) {
        if (Array.isArray(val)) out.push(...val.map(v => String(v)));
        else if (val && typeof val === 'object') out.push(flattenErrors(val));
        else out.push(String(val));
      }
      return out.filter(Boolean).join('; ');
    };
    const validationMsg = flattenErrors(errData.errors);
    const msg = errData?.message || validationMsg || error?.message || "Failed to upload payment proof";
    $toast.error(msg);
  } finally {
    uploadLoading.value = false;
    _uploadProofLock = false;
    _uploadProofAbort = null;
  }
};

// Confirm received flow — accept status shipped (flow biasa) ATAU delivered (admin override belum activate)
const confirmCardReceived = async (card) => {
  const isDeliveredState = card.status === 'delivered';
  const msg = isDeliveredState
    ? "You are in DELIVERED (NOT ACTIVE) status. Confirm to ACTIVATE your premium subscription NOW! This action will activate your subscription plan."
    : "Make SURE you have PHYSICALLY received the NFC card! This action will activate your premium subscription.";
  if (!confirm(msg)) {
    return;
  }

  confirmLoading.value = { id: card.id, loading: true };
  try {
    const response = await $api.post(`/nfc-cards/${card.id}/confirm-received`);

    if (response.success) {
      $toast.success(response.message || "Card receipt confirmed. Subscription activated!");
      _invalidateNfcCardsCache();
      await Promise.all([loadNfcCards(), loadSubscriptionStatus()]);
    }
  } catch (error) {
    $toast.error("Failed to confirm card receipt");
    console.error("Confirm received error:", error);
  } finally {
    confirmLoading.value = { id: null, loading: false };
  }
};

const viewAnalytics = (card) => {
  if (!card) {
    $toast.error("No card selected");
    return;
  }

  // Plan priority: card.subscription_plan DULU (specific), baru fallback user global
  const cardPlan = String(card.subscription_plan || "").toLowerCase();
  const userPlan = authStore.user?.subscription_plan?.toLowerCase() || "free";
  const effectivePlan = cardPlan || userPlan;

  let analyticsPath = "";

  if (effectivePlan === "business") {
    analyticsPath =
      "/UserDashboard/UserManagement/BusinessPlanUser/BusinessAnalytics";
  } else if (effectivePlan === "premium") {
    analyticsPath =
      "/UserDashboard/UserManagement/PremiumPlanUser/PremiumAnalytics";
  } else {
    analyticsPath = "/UserDashboard/Analytics";
  }

  // Pass both card_id + nfc_card_id as query params for unambiguous filter
  const qsParts = [`cardId=${encodeURIComponent(card.id)}`];
  if (card.nfc_card_id) {
    qsParts.push(`nfc_card_id=${encodeURIComponent(card.nfc_card_id)}`);
  }
  if (card.card_number) {
    qsParts.push(`card_number=${encodeURIComponent(card.card_number)}`);
  }

  navigateTo(`${analyticsPath}?${qsParts.join("&")}`);
};

const getStatusBadgeClass = (status) => {
  const classes = {
    pending_payment: "bg-orange-100 text-orange-800",
    awaiting_payment_verification: "bg-yellow-100 text-yellow-800",
    payment_verified: "bg-emerald-100 text-emerald-800",
    processing: "bg-purple-100 text-purple-800",
    shipped: "bg-blue-100 text-blue-800",
    delivered: "bg-green-100 text-green-800",
    active: "bg-success-100 text-success-800",
    inactive: "bg-secondary-100 text-secondary-800",
    expired: "bg-warning-100 text-warning-800",
    cancelled: "bg-red-100 text-red-800",
    replacement: "bg-info-100 text-info-800",
  };
  return classes[status] || classes["inactive"];
};

// Resolve display label — always use ENGLISH local mapping regardless of API status_label
const getCardStatusLabel = (card) => {
  const fallback = {
    pending_payment: "Pending Payment",
    awaiting_payment_verification: "Awaiting Verification",
    payment_verified: "Payment Verified",
    processing: "Processing",
    shipped: "Shipped",
    delivered: "Delivered (Not Active)",
    active: "Active",
    inactive: "Inactive",
    expired: "Expired",
    cancelled: "Cancelled",
    replacement: "Replacement",
  };
  return fallback[card?.status] || String(card?.status || "Unknown");
};

const getPlanPrice = (plan) => {
  const prices = {
    basic: 29.0,
    premium: 49.0,
    business: 99.0,
  };
  return prices[plan] || 49.0;
};

const formatDate = (date) => {
  if (!date) return "N/A";
  return new Date(date).toLocaleDateString();
};

// Lifecycle
onMounted(async () => {
  if (!authStore.isAuthenticated || !authStore.user) {
    navigateTo("/UserAccount/login");
    return;
  }

  // Guard is relaxed:
  // 1. Users with any NFC card order (even pending_payment) should always reach this page to upload proof / view status
  // 2. Otherwise, normal onboarding rules apply.
  const user = authStore.user;
  const hasValidPlan =
    user.subscription_plan &&
    ["free", "basic", "premium", "business"].includes(
      String(user.subscription_plan).toLowerCase()
    );
  const onboardingCompleted =
    user.is_new_user !== true || user.subscription_active === true || hasValidPlan;

  // ═══════════════════════════════════════════════════════════════════════
  // PERFORMANCE FIX: GUARD uses SHARED cache promise with loadNfcCards().
  // Old behaviour: onMounted fetched /nfc-cards TWICE (guard + loadNfcCards).
  // New behaviour: a SINGLE axios call powers BOTH the guard hasCardOrder
  // boolean AND the full downstream nfcCards.value population.
  // ═══════════════════════════════════════════════════════════════════════
  let hasCardOrder = false;
  try {
    // Kick off the /nfc-cards request NOW — cached promise will be re-used later
    const resp = await _getNfcCardsCached();
    if (resp && resp.success && Array.isArray(resp.nfc_cards) && resp.nfc_cards.length > 0) {
      hasCardOrder = true;
    }
  } catch (e) {
    // ignore — user might genuinely need onboarding yet
  }

  if (!onboardingCompleted && !hasCardOrder) {
    console.log(
      "🚫 Access denied: User is still in onboarding process (is_new_user=true, no active subscription, no valid plan, and no existing card orders)"
    );
    $toast.warning("Please complete plan selection and payment first.");
    navigateTo("/UserDashboard/PlanSelection");
    return;
  }

  console.log(
    "✅ CardManagement guard passed: User has completed onboarding OR has an active NFC card order"
  );

  try {
    // ═══════════════════════════════════════════════════════════════════
    // PARALLEL LOAD: Subscription status + NFC cards run simultaneously.
    // loadNfcCards() internally awaits the SAME cached promise from the
    // guard check above, so it contributes ZERO extra network latency here.
    // ═══════════════════════════════════════════════════════════════════
    await Promise.all([
      loadSubscriptionStatus(),
      loadNfcCards(),
    ]);
  } finally {
    // Centralized: dismiss loading spinner regardless of partial failures
    loading.value = false;
  }
});
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.accordion-enter-active,
.accordion-leave-active {
  transition: all 0.3s ease;
  overflow: hidden;
}

.accordion-enter-from,
.accordion-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
