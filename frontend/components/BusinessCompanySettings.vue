<!-- components/BusinessCompanySettings.vue
     Company data the Business admin shares with every member, and which of
     those fields members cannot change. Locks are enforced by the API
     (PUT /business/settings, applied on every member profile save); this
     panel only edits them. -->
<template>
  <AdminSectionCard
    title="Company profile & locks"
    subtitle="Values here are copied onto every member's profile. Locked fields cannot be changed by members."
  >
    <template #header-actions>
      <button
        type="button"
        class="admin-btn admin-btn--primary"
        :disabled="saving || loading || !canManage"
        @click="save"
      >
        <Icon name="heroicons:check" class="h-4 w-4" />
        {{ saving ? "Saving..." : "Save company settings" }}
      </button>
    </template>

    <AdminLoadingState v-if="loading" />

    <div v-else class="space-y-8">
      <p v-if="!canManage" class="text-body text-secondary-600">
        Only the account owner and company admins can change these settings.
      </p>

      <div v-for="(keys, group) in lockable" :key="group">
        <p class="text-label text-secondary-700 uppercase mb-3">
          {{ group === "company" ? "Company details" : "Branding" }}
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="key in keys" :key="key" class="flex items-end gap-3">
            <label class="flex-1">
              <span class="text-caption text-secondary-600">{{ labelFor(key) }}</span>
              <input
                v-model="shared[key]"
                type="text"
                :placeholder="isColor(key) ? '#000000' : 'Not shared'"
                :disabled="!canManage"
                class="mt-1 w-full px-3 py-2 border border-secondary-300 rounded-lg text-input focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              />
            </label>
            <label
              class="inline-flex items-center gap-1.5 pb-2 text-caption text-secondary-700 cursor-pointer select-none"
              :title="locked.has(key) ? 'Members cannot change this' : 'Members may change this'"
            >
              <input
                type="checkbox"
                :checked="locked.has(key)"
                :disabled="!canManage"
                @change="toggleLock(key)"
              />
              <Icon
                :name="locked.has(key) ? 'heroicons:lock-closed' : 'heroicons:lock-open'"
                class="h-4 w-4"
              />
              Lock
            </label>
          </div>
        </div>
      </div>

      <div>
        <p class="text-label text-secondary-700 uppercase mb-3">Card template</p>
        <div class="flex flex-wrap items-end gap-3">
          <label class="flex-1 min-w-[16rem]">
            <span class="text-caption text-secondary-600">Template for team cards</span>
            <select
              v-model="cardTemplateId"
              :disabled="!canManage"
              class="mt-1 w-full px-3 py-2 border border-secondary-300 rounded-lg text-input"
            >
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
      </div>
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
const shared = reactive({});
const locked = ref(new Set());
const cardTemplateId = ref(null);
const templates = ref([]);

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

const labelFor = (key) => LABELS[key] || key.replace(/_/g, " ");
const isColor = (key) => key.endsWith("_color");

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
    console.error("Failed to save company settings:", error);
    $toast.error(error?.data?.message || "Failed to save company settings");
  } finally {
    saving.value = false;
  }
};

onMounted(load);
</script>
