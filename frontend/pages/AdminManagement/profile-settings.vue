<!-- pages/AdminManagement/profile-settings.vue -->
<template>
  <div>
    <AdminPageHeader
      eyebrow="Account"
      eyebrow-icon="heroicons:user-circle"
      title="Profile Settings"
      description="Manage your admin profile, sign-in credentials and interface preferences."
    />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <!-- Profile summary -->
      <aside class="lg:col-span-1">
        <div class="admin-panel sticky top-6 overflow-hidden">
          <div class="admin-panel-body flex flex-col items-center text-center">
            <div class="relative">
              <UserAvatar
                :src="form.profile_image || user?.profile_image"
                :name="`${user?.first_name || 'Admin'} ${user?.last_name || ''}`"
                class="h-24 w-24 rounded-full shadow-lg ring-4 ring-white"
              />
              <button
                type="button"
                title="Change photo"
                class="absolute -bottom-1 -right-1 inline-flex h-9 w-9 items-center justify-center rounded-full bg-accent-500 text-white shadow-md transition-colors hover:bg-accent-600"
                @click="$refs.imageInput?.click()"
              >
                <Icon name="heroicons:camera" class="h-4 w-4" />
              </button>
              <input
                ref="imageInput"
                type="file"
                accept="image/*"
                class="hidden"
                @change="handleImageUpload"
              />
            </div>
            <p class="admin-panel-title mt-4">
              {{ user?.first_name && user?.last_name ? `${user.first_name} ${user.last_name}` : user?.name || 'Admin User' }}
            </p>
            <p class="text-body-sm text-secondary-500">{{ user?.email || 'admin@nfcgo.com' }}</p>
            <AdminStatusBadge
              tone="brass"
              :label="user?.admin_role_display || 'Administrator'"
              class="mt-2"
            />
          </div>

          <dl class="divide-y divide-secondary-100 border-t border-secondary-200 px-6 py-2">
            <div class="flex items-center justify-between py-3">
              <dt class="text-body-sm text-secondary-500">Account status</dt>
              <dd><AdminStatusBadge tone="success" label="Active" /></dd>
            </div>
            <div class="flex items-center justify-between py-3">
              <dt class="text-body-sm text-secondary-500">Member since</dt>
              <dd class="text-body-sm font-medium text-secondary-900">
                {{ formatDate(user?.created_at) }}
              </dd>
            </div>
            <div class="flex items-center justify-between py-3">
              <dt class="text-body-sm text-secondary-500">Last login</dt>
              <dd class="text-body-sm font-medium text-secondary-900">
                {{ formatDate(user?.last_login_at) }}
              </dd>
            </div>
          </dl>
        </div>
      </aside>

      <!-- Settings stack -->
      <div class="space-y-6 lg:col-span-2">
        <!-- Personal information -->
        <form ref="personalForm" @submit.prevent="savePersonalInfo">
          <AdminSectionCard
            title="Personal information"
            subtitle="The details shown on your admin account."
          >
            <div class="admin-form-section">
              <p class="admin-form-section-title">Identity details</p>
              <p class="admin-form-section-desc">
                Your name, sign-in email and a contact number.
              </p>
              <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                  <label class="admin-label">
                    First name <span class="text-error-600">*</span>
                  </label>
                  <input
                    v-model="form.first_name"
                    type="text"
                    required
                    class="admin-field"
                    placeholder="Enter first name"
                  />
                  <p v-if="personalErrors.first_name" class="mt-1 text-xs text-error-600">{{ personalErrors.first_name }}</p>
                </div>
                <div>
                  <label class="admin-label">
                    Last name <span class="text-error-600">*</span>
                  </label>
                  <input
                    v-model="form.last_name"
                    type="text"
                    required
                    class="admin-field"
                    placeholder="Enter last name"
                  />
                  <p v-if="personalErrors.last_name" class="mt-1 text-xs text-error-600">{{ personalErrors.last_name }}</p>
                </div>
                <div>
                  <label class="admin-label">
                    Email address <span class="text-error-600">*</span>
                  </label>
                  <input
                    v-model="form.email"
                    type="email"
                    required
                    class="admin-field"
                    placeholder="admin@nfcgo.com"
                  />
                  <p v-if="personalErrors.email" class="mt-1 text-xs text-error-600">{{ personalErrors.email }}</p>
                  <p class="mt-1 flex items-start gap-1 text-xs text-secondary-400">
                    <Icon name="heroicons:information-circle" class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                    This is your sign-in email. Changing it updates your login credentials.
                  </p>
                </div>
                <div>
                  <label class="admin-label">Phone number</label>
                  <input
                    v-model="form.phone"
                    type="tel"
                    class="admin-field"
                    placeholder="+60 12-345 6789"
                  />
                  <p v-if="personalErrors.phone" class="mt-1 text-xs text-error-600">{{ personalErrors.phone }}</p>
                </div>
              </div>
            </div>

            <template #footer>
              <div class="flex justify-end">
                <button
                  type="submit"
                  :disabled="savingPersonal"
                  class="admin-btn admin-btn--primary"
                >
                  <Icon
                    v-if="savingPersonal"
                    name="heroicons:arrow-path"
                    class="h-4 w-4 animate-spin"
                  />
                  {{ savingPersonal ? 'Saving…' : 'Save changes' }}
                </button>
              </div>
            </template>
          </AdminSectionCard>
        </form>

        <!-- Password & security -->
        <form @submit.prevent="changePassword">
          <AdminSectionCard
            title="Password &amp; security"
            subtitle="Keep your account safe with a strong, unique password."
          >
            <div class="admin-form-section">
              <p class="admin-form-section-title">Change password</p>
              <p class="admin-form-section-desc">
                Use at least 8 characters. You stay signed in on this device.
              </p>
              <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                  <label class="admin-label">Current password</label>
                  <div class="relative">
                    <input
                      v-model="passwordForm.current"
                      :type="showCurrentPassword ? 'text' : 'password'"
                      required
                      class="admin-field !pr-10"
                      :class="passwordErrors.current ? 'border-error-400 focus:border-error-500' : ''"
                      placeholder="Enter current password"
                    />
                    <button
                      type="button"
                      class="absolute inset-y-0 right-0 flex items-center pr-3 text-secondary-400 transition-colors hover:text-secondary-600"
                      @click="showCurrentPassword = !showCurrentPassword"
                    >
                      <Icon
                        :name="showCurrentPassword ? 'heroicons:eye-slash' : 'heroicons:eye'"
                        class="h-5 w-5"
                      />
                    </button>
                  </div>
                  <p v-if="passwordErrors.current" class="mt-1 text-xs text-error-600">{{ passwordErrors.current }}</p>
                </div>
                <div>
                  <label class="admin-label">New password</label>
                  <div class="relative">
                    <input
                      v-model="passwordForm.new"
                      :type="showNewPassword ? 'text' : 'password'"
                      required
                      class="admin-field !pr-10"
                      :class="passwordErrors.new ? 'border-error-400 focus:border-error-500' : ''"
                      placeholder="Enter new password"
                      @input="clearPasswordError('new')"
                    />
                    <button
                      type="button"
                      class="absolute inset-y-0 right-0 flex items-center pr-3 text-secondary-400 transition-colors hover:text-secondary-600"
                      @click="showNewPassword = !showNewPassword"
                    >
                      <Icon
                        :name="showNewPassword ? 'heroicons:eye-slash' : 'heroicons:eye'"
                        class="h-5 w-5"
                      />
                    </button>
                  </div>
                  <p v-if="passwordErrors.new" class="mt-1 text-xs text-error-600">{{ passwordErrors.new }}</p>
                  <!-- Password strength meter -->
                  <div v-if="passwordForm.new" class="mt-2">
                    <div class="flex h-1.5 gap-1">
                      <div
                        v-for="i in 4"
                        :key="i"
                        class="h-full flex-1 rounded-full transition-colors"
                        :class="i <= passwordStrength.score ? passwordStrength.barClass : 'bg-secondary-200'"
                      />
                    </div>
                    <p class="mt-1 text-xs" :class="passwordStrength.textClass">{{ passwordStrength.label }}</p>
                  </div>
                </div>
                <div class="md:col-span-2">
                  <label class="admin-label">Confirm new password</label>
                  <div class="relative">
                    <input
                      v-model="passwordForm.confirm"
                      :type="showConfirmPassword ? 'text' : 'password'"
                      required
                      class="admin-field !pr-10"
                      :class="passwordErrors.confirm ? 'border-error-400 focus:border-error-500' : ''"
                      placeholder="Confirm new password"
                      @input="clearPasswordError('confirm')"
                    />
                    <button
                      type="button"
                      class="absolute inset-y-0 right-0 flex items-center pr-3 text-secondary-400 transition-colors hover:text-secondary-600"
                      @click="showConfirmPassword = !showConfirmPassword"
                    >
                      <Icon
                        :name="showConfirmPassword ? 'heroicons:eye-slash' : 'heroicons:eye'"
                        class="h-5 w-5"
                      />
                    </button>
                  </div>
                  <p v-if="passwordErrors.confirm" class="mt-1 text-xs text-error-600">{{ passwordErrors.confirm }}</p>
                  <p
                    v-else-if="passwordForm.confirm && passwordForm.new !== passwordForm.confirm"
                    class="mt-1 text-xs text-error-600"
                  >
                    Passwords do not match.
                  </p>
                </div>
              </div>
            </div>

            <template #footer>
              <div class="flex justify-end">
                <button
                  type="submit"
                  :disabled="changingPassword"
                  class="admin-btn admin-btn--primary"
                >
                  <Icon
                    v-if="changingPassword"
                    name="heroicons:arrow-path"
                    class="h-4 w-4 animate-spin"
                  />
                  {{ changingPassword ? 'Updating…' : 'Change password' }}
                </button>
              </div>
            </template>
          </AdminSectionCard>
        </form>

        <!-- Preferences -->
        <AdminSectionCard
          title="Preferences"
          subtitle="Personalise how the admin console looks and reads."
        >
          <div class="admin-form-section">
            <p class="admin-form-section-title">Interface language</p>
            <p class="admin-form-section-desc">
              Set the language used across the admin console.
            </p>
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
              <button
                v-for="lang in languageOptions"
                :key="lang.code"
                type="button"
                class="flex items-center justify-between rounded-xl border-2 p-3 text-left transition-all duration-200"
                :class="
                  currentLanguage === lang.code
                    ? 'border-accent-500 bg-accent-50'
                    : 'border-secondary-200 hover:border-accent-300 hover:bg-secondary-50'
                "
                @click="setLanguage(lang.code)"
              >
                <span class="text-body-sm text-secondary-900">{{ lang.label }}</span>
                <Icon
                  v-if="currentLanguage === lang.code"
                  name="heroicons:check"
                  class="h-4 w-4 text-accent-600"
                />
              </button>
            </div>
          </div>
        </AdminSectionCard>
      </div>
    </div>
  </div>
</template>

<script setup>
useHead({
  title: "Profile Settings - Admin - NFCGo",
});

definePageMeta({
  middleware: ["auth", "admin"],
  layout: "admin-management",
});

const route = useRoute();
const authStore = useAuthStore();
const nuxtApp = useNuxtApp();
const { $api, $toast } = nuxtApp;
const user = computed(() => authStore.user);

// Language
const currentLanguage = ref(
  (typeof window !== 'undefined' && localStorage.getItem('nfcgo-language')) || 'en'
);

const setLanguage = (code) => {
  currentLanguage.value = code;
  if (typeof window !== 'undefined') {
    localStorage.setItem('nfcgo-language', code);
  }
  if ($toast) {
    const lang = languageOptions.find((l) => l.code === code);
    $toast.success(`Language changed to ${lang?.label || code}`);
  }
};

const languageOptions = [
  { code: 'en', label: 'English' },
  { code: 'ms', label: 'Bahasa Melayu' },
  { code: 'zh', label: '中文' },
];

// Form states
const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  profile_image: '',
});

const passwordForm = ref({
  current: '',
  new: '',
  confirm: '',
});

const savingPersonal = ref(false);
const changingPassword = ref(false);
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

// Inline error states
const personalErrors = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
});

const passwordErrors = reactive({
  current: '',
  new: '',
  confirm: '',
});

// Track the selected (but not yet uploaded) image file
const pendingImageFile = ref(null);

// Password strength meter
const passwordStrength = computed(() => {
  const pwd = passwordForm.value.new || '';
  if (!pwd) return { score: 0, label: '', barClass: '', textClass: '' };
  let score = 0;
  if (pwd.length >= 8) score++;
  if (/[A-Z]/.test(pwd)) score++;
  if (/[0-9]/.test(pwd)) score++;
  if (/[^A-Za-z0-9]/.test(pwd)) score++;
  const map = {
    0: { label: 'Too short', barClass: 'bg-error-500', textClass: 'text-error-600' },
    1: { label: 'Weak', barClass: 'bg-error-500', textClass: 'text-error-600' },
    2: { label: 'Fair', barClass: 'bg-warning-500', textClass: 'text-warning-600' },
    3: { label: 'Good', barClass: 'bg-accent-500', textClass: 'text-accent-600' },
    4: { label: 'Strong', barClass: 'bg-success-500', textClass: 'text-success-600' },
  };
  return { score, ...map[score] };
});

const clearPersonalErrors = () => {
  Object.keys(personalErrors).forEach((k) => (personalErrors[k] = ''));
};

const clearPasswordError = (field) => {
  if (field && passwordErrors[field]) passwordErrors[field] = '';
};

// Load user data into form
const loadUserData = () => {
  const u = user.value || {};
  form.value.first_name = u.first_name || '';
  form.value.last_name = u.last_name || u.name?.split(' ')[0] || '';
  if (!form.value.last_name && u.name) {
    const parts = u.name.split(' ');
    form.value.first_name = parts[0] || '';
    form.value.last_name = parts.slice(1).join(' ') || '';
  }
  form.value.email = u.email || '';
  form.value.phone = u.phone || u.phone_number || '';
  form.value.profile_image = u.profile_image || '';
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  try {
    const d = new Date(dateStr);
    return d.toLocaleDateString(undefined, {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  } catch {
    return '-';
  }
};

// Image upload — store the file for multipart upload, preview via object URL
const handleImageUpload = (e) => {
  const file = e.target?.files?.[0];
  if (!file) return;
  if (!file.type.startsWith('image/')) {
    if ($toast) $toast.error('Please select an image file');
    return;
  }
  if (file.size > 5 * 1024 * 1024) {
    if ($toast) $toast.error('File size must be less than 5MB');
    return;
  }
  pendingImageFile.value = file;
  // Preview using a temporary object URL (not uploaded until Save)
  form.value.profile_image = URL.createObjectURL(file);
};

// Save personal info
const savePersonalInfo = async () => {
  clearPersonalErrors();

  // Basic client-side validation
  if (!form.value.first_name.trim()) {
    personalErrors.first_name = 'First name is required';
    return;
  }
  if (!form.value.last_name.trim()) {
    personalErrors.last_name = 'Last name is required';
    return;
  }
  if (!form.value.email.trim()) {
    personalErrors.email = 'Email is required';
    return;
  }

  savingPersonal.value = true;
  try {
    if (!$api) {
      if ($toast) $toast.error('API not ready. Please try again.');
      return;
    }

    // Use FormData if there's a pending image upload, otherwise JSON.
    // The API client interceptor auto-sets Content-Type for FormData (lets browser set boundary).
    const payload = pendingImageFile.value
      ? (() => {
          const fd = new FormData();
          fd.append('first_name', form.value.first_name);
          fd.append('last_name', form.value.last_name);
          fd.append('email', form.value.email);
          fd.append('phone', form.value.phone || '');
          fd.append('profile_image', pendingImageFile.value);
          return fd;
        })()
      : {
          first_name: form.value.first_name,
          last_name: form.value.last_name,
          email: form.value.email,
          phone: form.value.phone,
        };

    const res = await $api.post('/admin/profile/update', payload);
    if (res?.success) {
      const updatedUser = res?.data?.user || res?.user;
      if (updatedUser) {
        authStore.setUser(updatedUser);
      }
      // Clear the pending image file now that it's saved
      pendingImageFile.value = null;
      if ($toast) $toast.success('Profile updated successfully');
    } else {
      // Surface field-level validation errors from the server
      if (res?.errors) {
        Object.keys(res.errors).forEach((field) => {
          const msg = Array.isArray(res.errors[field]) ? res.errors[field][0] : res.errors[field];
          if (personalErrors.hasOwnProperty(field)) personalErrors[field] = msg;
        });
      }
      if ($toast) $toast.error(res?.message || 'Failed to update profile');
    }
  } catch (e) {
    console.error('Profile update failed:', e);
    // Surface 422 field errors
    const fieldErrors = e?.response?.data?.errors;
    if (fieldErrors) {
      Object.keys(fieldErrors).forEach((field) => {
        const msg = Array.isArray(fieldErrors[field]) ? fieldErrors[field][0] : fieldErrors[field];
        if (personalErrors.hasOwnProperty(field)) personalErrors[field] = msg;
      });
    }
    if ($toast) {
      const msg = e?.response?.data?.message || e?.message || 'Failed to update profile';
      $toast.error(msg);
    }
  } finally {
    savingPersonal.value = false;
  }
};

// Change password
const changePassword = async () => {
  // Clear previous errors
  Object.keys(passwordErrors).forEach((k) => (passwordErrors[k] = ''));

  if (!passwordForm.value.current) {
    passwordErrors.current = 'Current password is required';
    return;
  }
  if (passwordForm.value.new.length < 8) {
    passwordErrors.new = 'New password must be at least 8 characters';
    return;
  }
  if (passwordForm.value.new !== passwordForm.value.confirm) {
    passwordErrors.confirm = 'Passwords do not match';
    return;
  }

  changingPassword.value = true;
  try {
    if (!$api) {
      if ($toast) $toast.error('API not ready. Please try again.');
      return;
    }
    const res = await $api.post('/admin/profile/change-password', {
      current_password: passwordForm.value.current,
      new_password: passwordForm.value.new,
      new_password_confirmation: passwordForm.value.confirm,
    });
    if (res?.success) {
      passwordForm.value = { current: '', new: '', confirm: '' };
      if ($toast) $toast.success('Password changed successfully');
    } else {
      // Surface field-level validation errors
      if (res?.errors) {
        Object.keys(res.errors).forEach((field) => {
          const msg = Array.isArray(res.errors[field]) ? res.errors[field][0] : res.errors[field];
          if (field === 'current_password' && passwordErrors.hasOwnProperty('current')) passwordErrors.current = msg;
          else if (field === 'new_password' && passwordErrors.hasOwnProperty('new')) passwordErrors.new = msg;
          else if (field === 'new_password_confirmation' && passwordErrors.hasOwnProperty('confirm')) passwordErrors.confirm = msg;
        });
      }
      if ($toast) $toast.error(res?.message || 'Failed to change password');
    }
  } catch (e) {
    console.error('Password change failed:', e);
    const fieldErrors = e?.response?.data?.errors;
    if (fieldErrors) {
      Object.keys(fieldErrors).forEach((field) => {
        const msg = Array.isArray(fieldErrors[field]) ? fieldErrors[field][0] : fieldErrors[field];
        if (field === 'current_password' && passwordErrors.hasOwnProperty('current')) passwordErrors.current = msg;
        else if (field === 'new_password' && passwordErrors.hasOwnProperty('new')) passwordErrors.new = msg;
        else if (field === 'new_password_confirmation' && passwordErrors.hasOwnProperty('confirm')) passwordErrors.confirm = msg;
      });
    }
    if ($toast) {
      const msg = e?.response?.data?.message || e?.message || 'Failed to change password';
      $toast.error(msg);
    }
  } finally {
    changingPassword.value = false;
  }
};

// Init
onMounted(() => {
  if (typeof window !== 'undefined') {
    const savedLang = localStorage.getItem('nfcgo-language');
    if (savedLang) {
      currentLanguage.value = savedLang;
    }
  }
  loadUserData();
});

// Re-sync form if user changes
watch(
  () => user.value,
  () => {
    loadUserData();
  },
  { deep: true }
);
</script>
