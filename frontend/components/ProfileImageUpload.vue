<!-- components/ProfileImageUpload.vue -->
<template>
  <div class="profile-image-upload">
    <div class="flex items-center space-x-4">
      <div class="relative">
        <div v-if="imageUrl" class="relative">
          <img
            :src="imageUrl"
            :alt="altText"
            class="w-20 h-20 rounded-full object-cover border-2 border-gray-200"
            @error="handleImageError"
          />
          <button
            v-if="!uploading"
            @click="removeImage"
            class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 rounded-full flex items-center justify-center hover:bg-red-600 transition-colors"
          >
            <Icon name="heroicons:x-mark" class="w-4 h-4 text-white" />
          </button>
        </div>
        <div
          v-else
          class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center border-2 border-gray-200"
        >
          <img
            src="/default-avatar.png"
            alt="Default avatar"
            class="w-20 h-20 rounded-full object-cover"
          />
        </div>

        <button
          @click="$refs.fileInput.click()"
          :disabled="uploading"
          class="absolute bottom-0 right-0 w-8 h-8 bg-white rounded-full border border-gray-200 flex items-center justify-center hover:bg-gray-50 disabled:opacity-50"
        >
          <Icon
            v-if="!uploading"
            name="heroicons:camera"
            class="w-4 h-4 text-gray-600"
          />
          <div v-else class="animate-spin">
            <Icon name="heroicons:arrow-path" class="w-4 h-4 text-gray-600" />
          </div>
        </button>

        <input
          ref="fileInput"
          type="file"
          accept="image/*"
          @change="handleFileSelect"
          class="hidden"
        />
      </div>

      <div class="flex-1">
        <p class="text-sm font-medium text-gray-700">{{ label }}</p>
        <p class="text-xs text-gray-500">{{ helpText }}</p>
        <p v-if="error" class="text-xs text-red-500 mt-1">{{ error }}</p>
      </div>
    </div>

    <!-- Upload Progress -->
    <div v-if="uploading && uploadProgress > 0" class="mt-3">
      <div class="w-full bg-gray-200 rounded-full h-2">
        <div
          class="bg-blue-600 h-2 rounded-full transition-all duration-300"
          :style="{ width: `${uploadProgress}%` }"
        ></div>
      </div>
      <p class="text-xs text-gray-600 mt-1">
        Uploading... {{ uploadProgress }}%
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";

const props = defineProps({
  modelValue: String,
  uploadEndpoint: {
    type: String,
    required: true,
  },
  deleteEndpoint: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    default: "Profile Picture",
  },
  helpText: {
    type: String,
    default: "Click to upload",
  },
  altText: {
    type: String,
    default: "Profile image",
  },
  maxSize: {
    type: Number,
    default: 5 * 1024 * 1024, // 5MB
  },
  nfcCardId: {
    type: [Number, String],
    default: null,
  },
});

const emit = defineEmits([
  "update:modelValue",
  "upload-success",
  "upload-error",
]);

const { $api, $toast } = useNuxtApp();
const config = useRuntimeConfig();

const fileInput = ref(null);
const uploading = ref(false);
const uploadProgress = ref(0);
const error = ref(null);

// Helper to get full image URL
const getFullImageUrl = (path) => {
  if (!path) return null;

  // If already a full URL, return as is
  if (path.startsWith("http://") || path.startsWith("https://")) {
    return path;
  }

  // Get base URL from API config
  const apiBase = config.public.apiBaseUrl || "http://localhost:8000/api";
  const baseUrl = apiBase.replace("/api", "");

  // Ensure path starts with /
  const imagePath = path.startsWith("/") ? path : `/${path}`;

  return `${baseUrl}${imagePath}`;
};

const imageUrl = computed({
  get: () => {
    const fullUrl = getFullImageUrl(props.modelValue);
    console.log("🖼️ Image URL:", {
      original: props.modelValue,
      full: fullUrl,
    });
    return fullUrl;
  },
  set: (value) => emit("update:modelValue", value),
});

const handleFileSelect = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  error.value = null;

  // Validate file type
  if (!file.type.startsWith("image/")) {
    error.value = "Please select a valid image file";
    return;
  }

  // Validate file size
  if (file.size > props.maxSize) {
    error.value = `File size must be less than ${
      props.maxSize / (1024 * 1024)
    }MB`;
    return;
  }

  await uploadFile(file);
};

// Compute correct upload base URL using same smart host detection
const safeParseJson = (raw) => {
  if (!raw) return {};
  let s = String(raw);
  const braceIdx = s.indexOf("{");
  const bracketIdx = s.indexOf("[");
  let start = -1;
  if (braceIdx === -1) start = bracketIdx;
  else if (bracketIdx === -1) start = braceIdx;
  else start = Math.min(braceIdx, bracketIdx);
  if (start > 0) s = s.slice(start);
  const lastBrace = s.lastIndexOf("}");
  const lastBracket = s.lastIndexOf("]");
  let end = -1;
  if (lastBrace === -1) end = lastBracket;
  else if (lastBracket === -1) end = lastBrace;
  else end = Math.max(lastBrace, lastBracket);
  if (end !== -1) s = s.slice(0, end + 1);
  return JSON.parse(s);
};

const _isLocalHostname = (h) =>
  h === "localhost" ||
  h === "127.0.0.1" ||
  h === "0.0.0.0" ||
  h.startsWith("10.") ||
  h.startsWith("192.168.") ||
  /^172\.(1[6-9]|2[0-9]|3[01])\./.test(h);

const getUploadBaseUrl = () => {
  let apiBase = (config.public?.apiBaseUrl || "").trim();
  if (typeof window !== "undefined" && window.location && window.location.hostname) {
    const host = window.location.hostname;
    if (_isLocalHostname(host)) {
      const scheme = window.location.protocol === "https:" ? "https" : "http";
      apiBase = `${scheme}://${host}:8000/api`;
    }
  }
  if (!apiBase) {
    apiBase = "http://localhost:8000/api";
  }
  return apiBase;
};

const uploadFile = async (file) => {
  uploading.value = true;
  uploadProgress.value = 0;
  error.value = null;

  try {
    const formData = new FormData();
    formData.append(
      props.uploadEndpoint.includes("logo") ? "logo" : "image",
      file
    );

    // Add nfc_card_id if provided
    if (props.nfcCardId) {
      formData.append("nfc_card_id", props.nfcCardId);
    }

    // Create a custom fetch with progress tracking
    const xhr = new XMLHttpRequest();

    // Track upload progress
    xhr.upload.addEventListener("progress", (e) => {
      if (e.lengthComputable) {
        uploadProgress.value = Math.round((e.loaded / e.total) * 100);
      }
    });

    // Create promise for XHR
    const uploadPromise = new Promise((resolve, reject) => {
      xhr.onload = () => {
        if (xhr.status >= 200 && xhr.status < 300) {
          try {
            resolve(safeParseJson(xhr.responseText));
          } catch (e) {
            console.error("Failed to parse response:", xhr.responseText);
            reject(new Error(e.message || "Invalid server response"));
          }
        } else {
          console.error("Upload failed:", {
            status: xhr.status,
            statusText: xhr.statusText,
            response: xhr.responseText,
          });
          try {
            const errorData = safeParseJson(xhr.responseText);
            reject(
              new Error(
                errorData.message || `Upload failed with status ${xhr.status}`
              )
            );
          } catch (e) {
            reject(
              new Error(
                e?.message
                  ? e.message
                  : `Upload failed with status ${xhr.status}: ${xhr.statusText}`
              )
            );
          }
        }
      };
      xhr.onerror = () => reject(new Error("Network error during upload"));
    });

    // Get auth token
    const token = useCookie("auth-token").value;

    // Send request (use smart host detection for local dev, not config default)
    const uploadApiBase = getUploadBaseUrl();
    xhr.open(
      "POST",
      `${uploadApiBase}${props.uploadEndpoint}`
    );
    xhr.setRequestHeader("Authorization", `Bearer ${token}`);
    xhr.setRequestHeader("Accept", "application/json");
    xhr.send(formData);

    const response = await uploadPromise;

    if (response.success) {
      // Handle different response formats
      // Format 1: { success: true, data: { url: '...' } } (ProfileBuilder)
      // Format 2: { success: true, account_image: '...' } (Settings - Account)
      // Format 3: { success: true, profile_image: '...' } (Settings - Profile or legacy)
      const uploadedUrl =
        response.data?.url ||
        response.account_image ||
        response.profile_image ||
        response.url;

      if (uploadedUrl) {
        imageUrl.value = uploadedUrl;
        emit("upload-success", { url: uploadedUrl, ...response });
        $toast.success("Image uploaded successfully");
      } else {
        throw new Error("No image URL in response");
      }
    } else {
      throw new Error(response.message || "Upload failed");
    }
  } catch (err) {
    console.error("Upload error:", err);
    error.value = err.message || "Failed to upload image";
    emit("upload-error", err);
    $toast.error(error.value);
  } finally {
    uploading.value = false;
    uploadProgress.value = 0;
    // Reset file input
    if (fileInput.value) {
      fileInput.value.value = "";
    }
  }
};

const handleImageError = (event) => {
  console.error("Image failed to load:", imageUrl.value);
  // Avoid triggering error loop: only flag error for the ORIGINAL image (not the fallback)
  if (event.target.dataset.isFallback !== "true") {
    event.target.dataset.isFallback = "true";
    event.target.src = "/default-avatar.png";
    error.value = "Failed to load image";
  } else {
    // Fallback also failed — stop trying and silently show placeholder.
    // Do not re-set src on the failing element again.
    console.warn("Fallback avatar also failed to load");
  }
};

const removeImage = async () => {
  if (!confirm("Are you sure you want to remove this image?")) return;

  try {
    const response = await $api.delete(props.deleteEndpoint);

    if (response.success) {
      imageUrl.value = null;
      $toast.success("Image removed successfully");
    }
  } catch (err) {
    console.error("Delete error:", err);
    $toast.error("Failed to remove image");
  }
};
</script>
