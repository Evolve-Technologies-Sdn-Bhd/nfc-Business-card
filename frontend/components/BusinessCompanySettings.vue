<!-- components/BusinessCompanySettings.vue
     Company data the Business admin shares with every member, and which of
     those fields members cannot change. Enforced by the API
     (PUT /business/settings → BusinessTenantService::applyCompanyValues /
     applyLocks on every member profile save); this panel only edits them. -->
<template>
  <AdminSectionCard
    title="Company profile & locks"
    subtitle="Shared values fill in whatever members leave empty. Locked fields always show the company value, and members can't change them."
  >
    <template #header-actions>
      <button
        type="button"
        class="admin-btn admin-btn--primary"
        :disabled="saving || loading || !canManage"
        @click="save"
      >
        <Icon :name="saving ? 'heroicons:arrow-path' : 'heroicons:check'" class="h-4 w-4" :class="{ 'animate-spin': saving }" />
        {{ saving ? "Saving…" : "Save company settings" }}
      </button>
    </template>

    <AdminLoadingState v-if="loading" />

    <div v-else class="space-y-8">
      <p v-if="!canManage" class="text-body text-secondary-600">
        Only the account owner and company admins can change these settings.
      </p>

      <section v-for="(keys, group) in lockable" :key="group">
        <div class="mb-3 flex items-center justify-between gap-3">
          <p class="admin-label !mb-0">{{ group === "company" ? "Company details" : "Branding" }}</p>
          <span class="text-caption text-secondary-500">
            {{ lockedCount(keys) }} of {{ keys.length }} locked
          </span>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div
            v-for="key in keys"
            :key="key"
            class="rounded-lg border p-3 transition-colors"
            :class="locked.has(key) ? 'border-accent-300 bg-accent-50/40' : 'border-secondary-200'"
          >
            <div class="mb-1.5 flex items-center justify-between gap-2">
              <label :for="`cs-${key}`" class="text-caption font-medium text-secondary-700">{{ labelFor(key) }}</label>
              <button
                type="button"
                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-caption font-medium transition-colors"
                :class="locked.has(key) ? 'bg-accent-100 text-accent-800' : 'text-secondary-500 hover:bg-secondary-100'"
                :disabled="!canManage"
                :aria-pressed="locked.has(key)"
                :title="locked.has(key) ? 'Locked: members cannot change this' : 'Unlocked: members may change this'"
                @click="toggleLock(key)"
              >
                <Icon :name="locked.has(key) ? 'heroicons:lock-closed' : 'heroicons:lock-open'" class="h-3.5 w-3.5" />
                {{ locked.has(key) ? "Locked" : "Lock" }}
              </button>
            </div>

            <!-- Design options: pick from the real list -->
            <select
              v-if="choicesFor(key)"
              :id="`cs-${key}`"
              v-model="shared[key]"
              :disabled="!canManage"
              class="admin-field"
            >
              <option value="">Not shared</option>
              <option v-for="opt in choicesFor(key)" :key="opt.id" :value="opt.id">{{ opt.name }}</option>
            </select>

            <!-- Colours -->
            <div v-else-if="isColor(key)" class="flex items-center gap-2">
              <input
                type="color"
                :value="validHex(shared[key]) ? expandHex(shared[key]) : '#000000'"
                :disabled="!canManage"
                class="h-9 w-11 shrink-0 cursor-pointer rounded border border-secondary-300"
                :class="validHex(shared[key]) ? '' : 'opacity-30'"
                :title="validHex(shared[key]) ? shared[key] : 'Not shared — pick a colour'"
                :aria-label="`${labelFor(key)} picker`"
                @input="shared[key] = $event.target.value"
              />
              <input :id="`cs-${key}`" v-model.trim="shared[key]" type="text" placeholder="Not shared" :disabled="!canManage" class="admin-field font-mono" />
            </div>

            <textarea
              v-else-if="key === 'company_description'"
              :id="`cs-${key}`"
              v-model="shared[key]"
              rows="2"
              placeholder="Not shared"
              :disabled="!canManage"
              class="admin-field"
            ></textarea>

            <input
              v-else
              :id="`cs-${key}`"
              v-model.trim="shared[key]"
              :type="isUrl(key) ? 'url' : 'text'"
              :placeholder="isUrl(key) ? 'https://…' : 'Not shared'"
              :disabled="!canManage"
              class="admin-field"
            />

            <p v-if="errors[key]" class="mt-1 text-caption text-error-600">{{ errors[key] }}</p>
            <p v-else-if="locked.has(key) && !hasValue(key)" class="mt-1 text-caption text-secondary-500">
              No company value: members keep what they have but can't change it.
            </p>
          </div>
        </div>
      </section>

      <section>
        <p class="admin-label">Card template</p>
        <div class="flex flex-wrap items-end gap-3">
          <label class="min-w-[16rem] flex-1">
            <span class="text-caption text-secondary-600">Template for team cards</span>
            <select v-model="cardTemplateId" :disabled="!canManage" class="admin-field mt-1">
              <option :value="null">No company template</option>
              <option v-for="t in templates" :key="t.id" :value="t.id">{{ t.name }}</option>
            </select>
          </label>
          <label class="inline-flex items-center gap-1.5 pb-2 text-caption text-secondary-700">
            <input
              type="checkbox"
              :checked="locked.has('card_template')"
              :disabled="!canManage || !cardTemplateId"
              @change="toggleLock('card_template')"
            />
            Use it for every team card order
          </label>
        </div>
        <p v-if="errors.card_template_id" class="mt-1 text-caption text-error-600">{{ errors.card_template_id }}</p>
      </section>
    </div>
  </AdminSectionCard>
</template>

<script setup>
const props = defineProps({
  canManage: { type: Boolean, default: false },
});

const { $api, $toast } = useNuxtApp();

const loading = ref(true);
const saving = ref(false);
const lockable = ref({});
const choices = ref({});
const shared = reactive({});
const locked = ref(new Set());
const cardTemplateId = ref(null);
const templates = ref([]);
const errors = reactive({});

const LABELS = {
  company_name: "Company name",
  company_logo: "Company logo URL",
  company_logo_text: "Logo text",
  company_registration_no: "Registration no.",
  company_description: "Company description",
  industry: "Industry",
  website: "Website",
  website_url: "Website button URL",
  address: "Address",
  address_name: "Address name",
  address_street: "Street",
  address_area: "Area",
  address_city_state: "City / state",
  address_country: "Country",
  address_map_url: "Map link",
  postal_code: "Postal code",
  profile_style: "Profile style",
  theme: "Theme",
  background_color: "Background colour",
  text_color: "Text colour",
  accent_color: "Accent colour",
  bg_gradient: "Background gradient",
  card_style: "Card style",
  button_style: "Button style",
  color_scheme: "Colour scheme",
  font: "Font",
  layout: "Layout",
  cover_banner: "Cover banner URL",
};

const URL_KEYS = ["company_logo", "cover_banner", "website_url", "address_map_url"];

const labelFor = (key) => LABELS[key] || key.replace(/_/g, " ");
const isColor = (key) => key.endsWith("_color");
const isUrl = (key) => URL_KEYS.includes(key);
const choicesFor = (key) => (Array.isArray(choices.value[key]) && choices.value[key].length ? choices.value[key] : null);
const hasValue = (key) => String(shared[key] ?? "").trim() !== "";
const lockedCount = (keys) => keys.filter((k) => locked.value.has(k)).length;
const validHex = (v) => /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(String(v || ""));
const expandHex = (v) => (v.length === 4 ? "#" + v.slice(1).split("").map((c) => c + c).join("") : v);

const toggleLock = (key) => {
  const next = new Set(locked.value);
  next.has(key) ? next.delete(key) : next.add(key);
  locked.value = next;
};

const load = async () => {
  loading.value = true;
  try {
    const [ctx, tpl] = await Promise.all([
      $api.get("/business/context"),
      $api.get("/card-templates", { params: { plan: "business" } }).catch(() => null),
    ]);
    const data = ctx?.data || {};
    lockable.value = data.lockable_fields || {};
    choices.value = data.design_choices || {};
    // Every field starts as "" so empty dropdowns show "Not shared".
    Object.values(lockable.value).flat().forEach((k) => { shared[k] = ""; });
    Object.assign(shared, data.shared_fields || {});
    locked.value = new Set(data.locked_fields || []);
    cardTemplateId.value = data.card_template_id ?? null;
    const list = tpl?.data?.templates || tpl?.data || tpl?.templates || [];
    templates.value = Array.isArray(list) ? list : [];
  } catch (error) {
    console.error("Failed to load company settings:", error);
    $toast.error("Failed to load company settings");
  } finally {
    loading.value = false;
  }
};

const save = async () => {
  if (!props.canManage) return;
  Object.keys(errors).forEach((k) => delete errors[k]);
  saving.value = true;
  try {
    const sharedFields = {};
    Object.entries(shared).forEach(([k, v]) => {
      if (v !== null && v !== undefined && String(v).trim() !== "") sharedFields[k] = String(v).trim();
    });
    const response = await $api.put("/business/settings", {
      shared_fields: sharedFields,
      locked_fields: [...locked.value],
      card_template_id: cardTemplateId.value,
    });
    $toast.success(response?.message || "Company settings saved");
  } catch (error) {
    // Field-level reasons from the API (e.g. bad colour, unknown theme)
    const fieldErrors = error?.data?.errors || {};
    Object.entries(fieldErrors).forEach(([k, msgs]) => {
      errors[k.replace(/^shared_fields\./, "")] = Array.isArray(msgs) ? msgs[0] : String(msgs);
    });
    $toast.error(error?.data?.message || "Failed to save company settings");
  } finally {
    saving.value = false;
  }
};

onMounted(load);
</script>
