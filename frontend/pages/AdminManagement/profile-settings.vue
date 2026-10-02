<!-- pages/AdminManagement/profile-settings.vue -->
<template>
  <div>
    <!-- Header -->
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-secondary-900">
            Profile Settings
          </h1>
          <p class="mt-2 text-secondary-600">
            Manage your admin account profile, security, and preferences
          </p>
        </div>
      </div>
    </div>

    <!-- Main Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left Column: Profile Summary -->
      <div class="lg:col-span-1">
        <div class="card sticky top-6">
          <div class="p-6 text-center border-b border-secondary-200">
            <div class="relative inline-block">
              <img
                :src="form.profile_image || user?.profile_image || '/default-avatar.png'"
                :alt="`${user?.first_name || 'Admin'} ${user?.last_name || ''}`"
                class="h-24 w-24 rounded-full object-cover ring-4 ring-white shadow-lg mx-auto"
              />
              <button
                type="button"
                @click="$refs.imageInput?.click()"
                class="absolute bottom-0 right-0 h-9 w-9 rounded-full bg-primary-600 text-white flex items-center justify-center shadow-md hover:bg-primary-700 transition-colors"
                title="Change photo"
              >
                <Icon name="heroicons:camera" class="h-4 w-4" />
              </button>
              <input
                type="file"
                ref="imageInput"
                @change="handleImageUpload"
                accept="image/*"
                class="hidden"
              />
            </div>
            <h2 class="mt-4 text-lg font-bold text-secondary-900">
              {{ user?.first_name && user?.last_name ? `${user.first_name} ${user.last_name}` : user?.name || 'Admin User' }}
            </h2>
            <p class="text-sm text-secondary-500">{{ user?.email || 'admin@nfcgo.my' }}</p>
            <span class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
              {{ user?.admin_role_display || 'Administrator' }}
            </span>
          </div>

          <div class="p-6 space-y-4">
            <div class="flex items-center justify-between text-sm">
              <span class="text-secondary-500">Account Status</span>
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <Icon name="heroicons:check-circle" class="h-3 w-3 mr-1" />
                Active
              </span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-secondary-500">Member Since</span>
              <span class="font-medium text-secondary-900">{{ formatDate(user?.created_at) }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-secondary-500">Last Login</span>
              <span class="font-medium text-secondary-900">{{ formatDate(user?.last_login_at) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Settings Forms -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Personal Information -->
        <div class="card">
          <div class="px-6 py-4 border-b border-secondary-200 flex items-center justify-between">
            <div>
              <h2 class="text-lg font-semibold text-secondary-900">
                Personal Information
              </h2>
              <p class="text-sm text-secondary-600">
                Update your personal details
              </p>
            </div>
          </div>
          <div class="p-6">
            <form @submit.prevent="savePersonalInfo">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-2">
                    First Name *
                  </label>
                  <input
                    v-model="form.first_name"
                    type="text"
                    required
                    class="input w-full"
                    placeholder="Enter first name"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-2">
                    Last Name *
                  </label>
                  <input
                    v-model="form.last_name"
                    type="text"
                    required
                    class="input w-full"
                    placeholder="Enter last name"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-2">
                    Email Address *
                  </label>
                  <input
                    v-model="form.email"
                    type="email"
                    required
                    class="input w-full"
                    placeholder="admin@nfcgo.my"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-2">
                    Phone Number
                  </label>
                  <input
                    v-model="form.phone"
                    type="tel"
                    class="input w-full"
                    placeholder="+60 12-345 6789"
                  />
                </div>
              </div>

              <div class="flex justify-end mt-6">
                <button
                  type="submit"
                  :disabled="savingPersonal"
                  class="btn btn-primary"
                >
                  <span v-if="savingPersonal" class="loading loading-spinner loading-sm mr-2"></span>
                  {{ savingPersonal ? 'Saving...' : 'Save Changes' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Change Password -->
        <div class="card">
          <div class="px-6 py-4 border-b border-secondary-200">
            <h2 class="text-lg font-semibold text-secondary-900">
              Change Password
            </h2>
            <p class="text-sm text-secondary-600">
              Update your account password regularly for security
            </p>
          </div>
          <div class="p-6">
            <form @submit.prevent="changePassword">
              <div class="space-y-4 max-w-xl">
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-2">
                    Current Password
                  </label>
                  <div class="relative">
                    <input
                      v-model="passwordForm.current"
                      :type="showCurrentPassword ? 'text' : 'password'"
                      required
                      class="input w-full pr-10"
                      placeholder="Enter current password"
                    />
                    <button
                      type="button"
                      @click="showCurrentPassword = !showCurrentPassword"
                      class="absolute inset-y-0 right-0 pr-3 flex items-center text-secondary-400 hover:text-secondary-600"
                    >
                      <Icon :name="showCurrentPassword ? 'heroicons:eye-slash' : 'heroicons:eye'" class="h-5 w-5" />
                    </button>
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-2">
                    New Password
                  </label>
                  <div class="relative">
                    <input
                      v-model="passwordForm.new"
                      :type="showNewPassword ? 'text' : 'password'"
                      required
                      class="input w-full pr-10"
                      placeholder="Enter new password"
                    />
                    <button
                      type="button"
                      @click="showNewPassword = !showNewPassword"
                      class="absolute inset-y-0 right-0 pr-3 flex items-center text-secondary-400 hover:text-secondary-600"
                    >
                      <Icon :name="showNewPassword ? 'heroicons:eye-slash' : 'heroicons:eye'" class="h-5 w-5" />
                    </button>
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-secondary-700 mb-2">
                    Confirm New Password
                  </label>
                  <div class="relative">
                    <input
                      v-model="passwordForm.confirm"
                      :type="showConfirmPassword ? 'text' : 'password'"
                      required
                      class="input w-full pr-10"
                      placeholder="Confirm new password"
                    />
                    <button
                      type="button"
                      @click="showConfirmPassword = !showConfirmPassword"
                      class="absolute inset-y-0 right-0 pr-3 flex items-center text-secondary-400 hover:text-secondary-600"
                    >
                      <Icon :name="showConfirmPassword ? 'heroicons:eye-slash' : 'heroicons:eye'" class="h-5 w-5" />
                    </button>
                  </div>
                </div>
              </div>

              <div class="flex justify-end mt-6">
                <button
                  type="submit"
                  :disabled="changingPassword"
                  class="btn btn-primary"
                >
                  <span v-if="changingPassword" class="loading loading-spinner loading-sm mr-2"></span>
                  {{ changingPassword ? 'Updating...' : 'Change Password' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Preferences -->
        <div class="card">
          <div class="px-6 py-4 border-b border-secondary-200">
            <h2 class="text-lg font-semibold text-secondary-900">
              Preferences
            </h2>
            <p class="text-sm text-secondary-600">
              Customize your interface experience
            </p>
          </div>
          <div class="p-6 space-y-6">
            <!-- Theme -->
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-3">
                Interface Theme
              </label>
              <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <button
                  v-for="t in themeOptions"
                  :key="t.key"
                  type="button"
                  @click="setTheme(t.key)"
                  :class="[
                    'relative p-4 rounded-xl border-2 text-left transition-all duration-200',
                    currentTheme === t.key
                      ? 'border-primary-500 bg-primary-50 shadow-md'
                      : 'border-secondary-200 hover:border-primary-300 hover:bg-secondary-50'
                  ]"
                >
                  <div class="flex items-center gap-3">
                    <div
                      class="w-10 h-10 rounded-lg border border-secondary-200 flex items-center justify-center shadow-sm"
                      :style="{ backgroundColor: t.color + '1A' }"
                    >
                      <Icon :name="t.icon" class="h-5 w-5" :style="{ color: t.color }" />
                    </div>
                    <div>
                      <p class="text-sm font-semibold text-secondary-900">{{ t.name }}</p>
                    </div>
                  </div>
                  <div
                    v-if="currentTheme === t.key"
                    class="absolute top-2 right-2 w-5 h-5 rounded-full bg-primary-600 text-white flex items-center justify-center"
                  >
                    <Icon name="heroicons:check" class="h-3 w-3" />
                  </div>
                </button>
              </div>
            </div>

            <!-- Language -->
            <div>
              <label class="block text-sm font-medium text-secondary-700 mb-2">
                Interface Language
              </label>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 max-w-2xl">
                <button
                  v-for="lang in languageOptions"
                  :key="lang.code"
                  type="button"
                  @click="setLanguage(lang.code)"
                  :class="[
                    'p-3 rounded-xl border-2 text-left transition-all duration-200 flex items-center justify-between',
                    currentLanguage === lang.code
                      ? 'border-primary-500 bg-primary-50'
                      : 'border-secondary-200 hover:border-primary-300 hover:bg-secondary-50'
                  ]"
                >
                  <span class="font-medium text-secondary-900 text-sm">{{ lang.label }}</span>
                  <Icon v-if="currentLanguage === lang.code" name="heroicons:check" class="h-4 w-4 text-primary-600" />
                </button>
              </div>
            </div>
          </div>
        </div>
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

// Theme integration
const themeState = useState('theme', () => 'default');
const currentTheme = ref(themeState.value || 'default');

const setTheme = (key) => {
  currentTheme.value = key;
  themeState.value = key;
  if (typeof window !== 'undefined') {
    localStorage.setItem('nfcgo-theme', key);
    document.documentElement.setAttribute('data-theme', key);
  }
  if ($toast) $toast.success('Theme updated');
};

const themeOptions = [
  { key: 'default', name: 'Default', icon: 'heroicons:sun', color: '#4F46E5' },
  { key: 'zora-navy', name: 'Navy Premium', icon: 'heroicons:moon', color: '#1E3A5F' },
  { key: 'zora-teal', name: 'Matte Teal', icon: 'heroicons:sparkles', color: '#0D9488' },
];

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

// Image upload
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
  const reader = new FileReader();
  reader.onload = (ev) => {
    form.value.profile_image = ev.target?.result || '';
  };
  reader.readAsDataURL(file);
};

// Save personal info
const savePersonalInfo = async () => {
  savingPersonal.value = true;
  try {
    if (!$api) {
      if ($toast) $toast.error('API not ready. Please try again.');
      return;
    }

    const payload = {
      first_name: form.value.first_name,
      last_name: form.value.last_name,
      email: form.value.email,
      phone: form.value.phone,
    };

    // If profile image is a data URL (newly uploaded), send it
    if (form.value.profile_image && form.value.profile_image.startsWith('data:')) {
      payload.profile_image = form.value.profile_image;
    }

    const res = await $api.post('/admin/profile/update', payload);
    if (res?.success) {
      if (res?.data?.user || res?.user) {
        authStore.setUser(res.data?.user || res.user);
      }
      if ($toast) $toast.success('Profile updated successfully');
    } else {
      if ($toast) $toast.error(res?.message || 'Failed to update profile');
    }
  } catch (e) {
    console.error('Profile update failed:', e);
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
  if (passwordForm.value.new !== passwordForm.value.confirm) {
    if ($toast) $toast.error('New passwords do not match');
    return;
  }
  if (passwordForm.value.new.length < 8) {
    if ($toast) $toast.error('New password must be at least 8 characters');
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
      if ($toast) $toast.error(res?.message || 'Failed to change password');
    }
  } catch (e) {
    console.error('Password change failed:', e);
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
    const savedTheme = localStorage.getItem('nfcgo-theme');
    if (savedTheme) {
      currentTheme.value = savedTheme;
      themeState.value = savedTheme;
    }
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
