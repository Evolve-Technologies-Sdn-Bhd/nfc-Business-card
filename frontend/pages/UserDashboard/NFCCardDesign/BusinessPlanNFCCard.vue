<!-- pages/onboarding/nfc-card-customization.vue -->
//for order new card page can use
<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-50 to-secondary-100">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center">
            <NuxtLink to="/" class="inline-flex items-center">
              <Icon
                name="heroicons:identification"
                class="h-8 w-8 text-primary-600"
              />
              <span class="ml-2 text-xl font-bold text-secondary-900"
                >NFCGo</span
              >
            </NuxtLink>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm font-medium text-primary-600"
              >Business Plan - Order New Card</span
            >
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-secondary-900 mb-4">
          Customize Your NFC Card
        </h1>
        <p class="text-xl text-secondary-600 max-w-2xl mx-auto">
          Choose from our professional templates or upload your own design. Your
          card will be 54mm x 90mm (standard business card size).
        </p>
      </div>

      <!-- Company Information Section -->
      <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
        <h2 class="text-2xl font-bold text-secondary-900 mb-6">
          <Icon name="heroicons:building-office-2" class="h-7 w-7 inline-block mr-2 text-primary-600" />
          Company Information
        </h2>
        <p class="text-sm text-secondary-600 mb-6">This information will appear on all cards ordered.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Company Logo Upload -->
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2">Company Logo</label>
            <div class="flex items-start gap-4">
              <!-- Logo Preview -->
              <div 
                class="w-24 h-24 border-2 border-dashed border-secondary-300 rounded-lg flex items-center justify-center bg-secondary-50 overflow-hidden"
                :class="{ 'border-primary-500': companyInfo.logo }"
              >
                <img 
                  v-if="companyInfo.logo" 
                  :src="companyInfo.logo" 
                  alt="Company Logo" 
                  class="w-full h-full object-contain"
                />
                <Icon v-else name="heroicons:photo" class="h-8 w-8 text-secondary-400" />
              </div>
              
              <!-- Upload Button -->
              <div class="flex-1">
                <input
                  type="file"
                  ref="logoInput"
                  @change="handleLogoUpload"
                  accept="image/*"
                  class="hidden"
                />
                <button
                  type="button"
                  @click="$refs.logoInput.click()"
                  class="w-full px-4 py-2 border border-secondary-300 rounded-lg text-sm font-medium text-secondary-700 hover:bg-secondary-50 transition-colors"
                >
                  <Icon name="heroicons:arrow-up-tray" class="h-4 w-4 inline-block mr-1" />
                  Upload Logo
                </button>
                <p class="text-xs text-secondary-500 mt-2">PNG, JPG up to 2MB. Recommended: 200x200px</p>
                <button
                  v-if="companyInfo.logo"
                  @click="removeLogo"
                  class="text-xs text-red-600 hover:text-red-800 mt-1"
                >
                  Remove logo
                </button>
              </div>
            </div>
          </div>

          <!-- Company Name -->
          <div>
            <label class="block text-sm font-medium text-secondary-700 mb-2">Company Name *</label>
            <input
              v-model="companyInfo.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              placeholder="Enter your company name"
            />
            <p class="text-xs text-secondary-500 mt-2">This will be displayed on your NFC cards</p>
          </div>
        </div>
      </div>

      <!-- Card Recipients Section -->
      <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
        <h2 class="text-2xl font-bold text-secondary-900 mb-6">Card Recipients</h2>

        <!-- Include Admin Card -->
        <div class="mb-6 border-2 border-secondary-200 rounded-lg overflow-hidden">
          <div class="flex items-center p-4 bg-secondary-50">
            <input
              id="includeAdmin"
              v-model="includeAdminCard"
              type="checkbox"
              class="h-5 w-5 text-primary-600 focus:ring-primary-500 border-secondary-300 rounded"
            />
            <label for="includeAdmin" class="ml-3 text-lg font-semibold text-secondary-900">
              Include myself (Admin)
            </label>
          </div>
          
          <!-- Admin Full Form (shown when checkbox is checked) -->
          <div v-if="includeAdminCard" class="p-4 bg-primary-50">
            <form class="space-y-3">
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-medium text-secondary-700 mb-1">Name *</label>
                  <input
                    v-model="adminInfo.name"
                    type="text"
                    required
                    class="w-full px-2 py-1.5 text-sm border border-secondary-300 rounded focus:ring-2 focus:ring-primary-500"
                    placeholder="Your full name"
                  />
                </div>
                <div>
                  <label class="block text-xs font-medium text-secondary-700 mb-1">Email *</label>
                  <input
                    v-model="adminInfo.email"
                    type="email"
                    required
                    class="w-full px-2 py-1.5 text-sm border border-secondary-300 rounded focus:ring-2 focus:ring-primary-500"
                    placeholder="your@email.com"
                  />
                </div>
              </div>
              
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-medium text-secondary-700 mb-1">Position *</label>
                  <input
                    v-model="adminInfo.position"
                    type="text"
                    required
                    class="w-full px-2 py-1.5 text-sm border border-secondary-300 rounded focus:ring-2 focus:ring-primary-500"
                    placeholder="Your position/job title"
                  />
                </div>
                <div>
                  <label class="block text-xs font-medium text-secondary-700 mb-1">Contact Number *</label>
                  <input
                    v-model="adminInfo.contactNumber"
                    type="tel"
                    required
                    class="w-full px-2 py-1.5 text-sm border border-secondary-300 rounded focus:ring-2 focus:ring-primary-500"
                    placeholder="+60 12-345 6789"
                  />
                </div>
              </div>
              
              <div>
                <label class="block text-xs font-medium text-secondary-700 mb-1">Website (Optional)</label>
                <input
                  v-model="adminInfo.website"
                  type="url"
                  class="w-full px-2 py-1.5 text-sm border border-secondary-300 rounded focus:ring-2 focus:ring-primary-500"
                  placeholder="https://yourwebsite.com"
                />
              </div>
              
              <div>
                <label class="block text-xs font-medium text-secondary-700 mb-1">Business Address *</label>
                <input
                  v-model="adminInfo.address"
                  type="text"
                  required
                  class="w-full px-2 py-1.5 text-sm border border-secondary-300 rounded focus:ring-2 focus:ring-primary-500"
                  placeholder="Your business address"
                />
              </div>
              
              <div>
                <label class="block text-xs font-medium text-secondary-700 mb-1">
                  Delivery Address * 
                  <span class="text-xs text-primary-600">(All cards will be delivered here)</span>
                </label>
                <input
                  v-model="adminInfo.deliveryAddress"
                  type="text"
                  required
                  class="w-full px-2 py-1.5 text-sm border border-secondary-300 rounded focus:ring-2 focus:ring-primary-500"
                  placeholder="Delivery address for all cards"
                />
              </div>
            </form>
          </div>
        </div>

        <!-- Delivery Address Only (when admin card not included) -->
        <div v-if="!includeAdminCard && uploadedEmployees.length > 0" class="mb-6 p-4 border-2 border-amber-200 bg-amber-50 rounded-lg">
          <div class="flex items-center mb-3">
            <Icon name="heroicons:truck" class="h-5 w-5 text-amber-600 mr-2" />
            <h3 class="text-sm font-semibold text-amber-900">Delivery Address for All Cards</h3>
          </div>
          <div>
            <input
              v-model="adminInfo.deliveryAddress"
              type="text"
              required
              class="w-full px-3 py-2 text-sm border border-amber-300 rounded focus:ring-2 focus:ring-amber-500 bg-white"
              placeholder="Enter delivery address for all employee cards"
            />
            <p class="text-xs text-amber-700 mt-1">
              All {{ uploadedEmployees.length }} employee card{{ uploadedEmployees.length > 1 ? 's' : '' }} will be delivered to this address
            </p>
          </div>
        </div>

        <!-- Add Employees Section -->
        <div class="space-y-4">
          <h3 class="text-lg font-semibold text-secondary-800">Add Employees</h3>
          
          <!-- Employee Add Method Selection -->
          <div class="flex gap-4 mb-4">
            <button
              @click="employeeAddMethod = 'manual'"
              :class="[
                'flex-1 py-2 px-4 rounded-lg border-2 transition-all',
                employeeAddMethod === 'manual'
                  ? 'border-primary-500 bg-primary-50 text-primary-700'
                  : 'border-secondary-200 hover:border-secondary-300',
              ]"
            >
              <Icon name="heroicons:user-plus" class="h-5 w-5 inline mr-2" />
              Add Manually
            </button>
            <button
              @click="employeeAddMethod = 'upload'"
              :class="[
                'flex-1 py-2 px-4 rounded-lg border-2 transition-all',
                employeeAddMethod === 'upload'
                  ? 'border-primary-500 bg-primary-50 text-primary-700'
                  : 'border-secondary-200 hover:border-secondary-300',
              ]"
            >
              <Icon name="heroicons:arrow-up-tray" class="h-5 w-5 inline mr-2" />
              Upload CSV
            </button>
          </div>

          <!-- CSV Upload Section -->
          <div v-if="employeeAddMethod === 'upload'" class="space-y-4">
              <!-- File Upload -->
              <div>
                <label class="block text-sm font-medium text-secondary-700 mb-2"
                  >Upload CSV or XLSX File *</label
                >
                <div
                  class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-secondary-300 border-dashed rounded-lg hover:border-secondary-400 transition-colors"
                >
                  <div class="space-y-1 text-center">
                    <Icon
                      name="heroicons:document-arrow-up"
                      class="mx-auto h-12 w-12 text-secondary-400"
                    />
                    <div class="flex text-sm text-secondary-600">
                      <label
                        class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500"
                      >
                        <span>Upload employee data file</span>
                        <input
                          type="file"
                          class="sr-only"
                          accept=".csv,.xlsx"
                          @change="handleEmployeeFileUpload"
                        />
                      </label>
                    </div>
                    <p class="text-xs text-secondary-500">
                      CSV or XLSX up to 5MB
                    </p>
                  </div>
                </div>
                <div
                  v-if="employeeFile"
                  class="mt-3 p-3 bg-primary-50 rounded-lg flex items-center justify-between"
                >
                  <div class="flex items-center">
                    <Icon
                      name="heroicons:document-text"
                      class="h-5 w-5 text-primary-600 mr-2"
                    />
                    <span class="text-sm text-primary-900 font-medium">{{
                      employeeFile.name
                    }}</span>
                  </div>
                  <button
                    @click="employeeFile = null"
                    class="text-red-600 hover:text-red-800"
                  >
                    <Icon name="heroicons:x-mark" class="h-5 w-5" />
                  </button>
                </div>
              </div>

              <!-- File Format Info -->
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                  <Icon
                    name="heroicons:information-circle"
                    class="h-5 w-5 text-blue-600 flex-shrink-0"
                  />
                  <div class="ml-3">
                    <p class="text-sm text-blue-700">
                      <strong>Required Columns:</strong>
                    </p>
                    <ul
                      class="text-sm text-blue-600 mt-1 list-disc list-inside"
                    >
                      <li>Name (required)</li>
                      <li>Email (required)</li>
                      <li>Position (required)</li>
                      <li>Contact Number (required)</li>
                      <li>Website (optional)</li>
                    </ul>
                    <p class="text-xs text-blue-600 mt-2 italic">
                      <strong>Note:</strong> Business address and delivery address will automatically use Admin's addresses
                    </p>
                    <div class="mt-3 flex items-center space-x-2">
                      <button
                        @click="downloadCSVTemplate"
                        class="text-sm text-primary-600 hover:text-primary-800 underline font-medium inline-flex items-center"
                      >
                        <Icon
                          name="heroicons:arrow-down-tray"
                          class="h-4 w-4 mr-1"
                        />
                        Download CSV Template
                      </button>
                      <span class="text-secondary-400">|</span>
                      <button
                        @click="downloadXLSXTemplate"
                        class="text-sm text-primary-600 hover:text-primary-800 underline font-medium inline-flex items-center"
                      >
                        <Icon
                          name="heroicons:arrow-down-tray"
                          class="h-4 w-4 mr-1"
                        />
                        Download XLSX Template
                      </button>
                    </div>
                  </div>
                </div>
              </div>

          </div>

          <!-- Manual Employee Entry Form -->
          <div v-if="employeeAddMethod === 'manual'" class="space-y-4">
            <form @submit.prevent="addManualEmployee" class="border border-secondary-200 rounded-lg p-4 bg-secondary-50 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                      <div>
                        <label
                          class="block text-xs font-medium text-secondary-700 mb-1"
                          >Name *</label
                        >
                        <input
                          v-model="manualEmployee.name"
                          type="text"
                          required
                          class="w-full px-2 py-1.5 text-sm border border-secondary-300 rounded focus:ring-2 focus:ring-primary-500"
                          placeholder="Employee name"
                        />
                      </div>
                      <div>
                        <label
                          class="block text-xs font-medium text-secondary-700 mb-1"
                          >Email *</label
                        >
                        <input
                          v-model="manualEmployee.email"
                          type="email"
                          required
                          class="w-full px-2 py-1.5 text-sm border border-secondary-300 rounded focus:ring-2 focus:ring-primary-500"
                          placeholder="email@company.com"
                        />
                      </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                      <div>
                        <label
                          class="block text-xs font-medium text-secondary-700 mb-1"
                          >Position *</label
                        >
                        <input
                          v-model="manualEmployee.position"
                          type="text"
                          required
                          class="w-full px-2 py-1.5 text-sm border border-secondary-300 rounded focus:ring-2 focus:ring-primary-500"
                          placeholder="Job title"
                        />
                      </div>
                      <div>
                        <label
                          class="block text-xs font-medium text-secondary-700 mb-1"
                          >Contact Number *</label
                        >
                        <input
                          v-model="manualEmployee.contactNumber"
                          type="tel"
                          required
                          class="w-full px-2 py-1.5 text-sm border border-secondary-300 rounded focus:ring-2 focus:ring-primary-500"
                          placeholder="+60 12-345 6789"
                        />
                      </div>
                    </div>
                    <div>
                      <label
                        class="block text-xs font-medium text-secondary-700 mb-1"
                        >Website (Optional)</label
                      >
                      <input
                        v-model="manualEmployee.website"
                        type="url"
                        class="w-full px-2 py-1.5 text-sm border border-secondary-300 rounded focus:ring-2 focus:ring-primary-500"
                        placeholder="https://website.com"
                      />
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded p-2">
                      <p class="text-xs text-blue-700">
                        <Icon name="heroicons:information-circle" class="h-4 w-4 inline mr-1" />
                        <strong>Note:</strong> Business address and delivery address will use Admin's addresses
                      </p>
                    </div>
              <div class="flex justify-end space-x-2 pt-2">
                <button
                  type="button"
                  @click="resetManualForm"
                  class="btn btn-sm btn-outline"
                >
                  Clear
                </button>
                <button type="submit" class="btn btn-sm btn-primary">
                  <Icon name="heroicons:plus" class="h-4 w-4 mr-1" />
                  Add Employee
                </button>
              </div>
            </form>
          </div>

          <!-- Employee Queue - Shared List -->
          <div v-if="uploadedEmployees.length > 0" class="space-y-3">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-secondary-800">
                Employee Queue ({{ uploadedEmployees.length }} employees)
              </h3>
              <button
                @click="uploadedEmployees = []"
                class="text-sm text-red-600 hover:text-red-800"
              >
                Clear All
              </button>
            </div>
            <div class="max-h-64 overflow-y-auto border border-secondary-200 rounded-lg">
              <table class="min-w-full divide-y divide-secondary-200">
                <thead class="bg-secondary-50 sticky top-0">
                  <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase">
                      Name
                    </th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase">
                      Email
                    </th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase">
                      Position
                    </th>
                    <th class="px-3 py-2 text-center text-xs font-medium text-secondary-500 uppercase">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-secondary-200">
                  <tr v-for="(emp, index) in uploadedEmployees" :key="index">
                    <td class="px-3 py-2 text-sm text-secondary-900">{{ emp.name }}</td>
                    <td class="px-3 py-2 text-sm text-secondary-600">{{ emp.email }}</td>
                    <td class="px-3 py-2 text-sm text-secondary-600">{{ emp.position }}</td>
                    <td class="px-3 py-2 text-center">
                      <button
                        @click="removeEmployee(index)"
                        class="text-red-600 hover:text-red-800"
                        title="Remove"
                      >
                        <Icon name="heroicons:trash" class="h-4 w-4" />
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Card Design Section -->
        <div class="space-y-8">
          <!-- Design Options -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-2xl font-bold text-secondary-900 mb-6">
              Card Design
            </h2>

            <!-- Design Method Selection -->
            <div class="mb-6">
              <h3 class="text-lg font-semibold text-secondary-800 mb-4">
                Choose Design Method
              </h3>
              <div class="grid grid-cols-3 gap-4">
                <button
                  @click="designMethod = 'previous'"
                  :class="[
                    'p-4 rounded-lg border-2 transition-colors',
                    designMethod === 'previous'
                      ? 'border-primary-500 bg-primary-50'
                      : 'border-secondary-200 hover:border-secondary-300',
                  ]"
                  :disabled="!hasPreviousDesigns"
                >
                  <Icon
                    name="heroicons:clock"
                    class="h-8 w-8 mx-auto mb-2 text-primary-600"
                  />
                  <p class="text-sm font-medium">Previous Design</p>
                  <p v-if="!hasPreviousDesigns" class="text-xs text-secondary-500 mt-1">No previous designs</p>
                </button>
                <button
                  @click="designMethod = 'template'"
                  :class="[
                    'p-4 rounded-lg border-2 transition-colors',
                    designMethod === 'template'
                      ? 'border-primary-500 bg-primary-50'
                      : 'border-secondary-200 hover:border-secondary-300',
                  ]"
                >
                  <Icon
                    name="heroicons:squares-2x2"
                    class="h-8 w-8 mx-auto mb-2 text-primary-600"
                  />
                  <p class="text-sm font-medium">Use Template</p>
                </button>
                <button
                  @click="designMethod = 'custom'"
                  :class="[
                    'p-4 rounded-lg border-2 transition-colors',
                    designMethod === 'custom'
                      ? 'border-primary-500 bg-primary-50'
                      : 'border-secondary-200 hover:border-secondary-300',
                  ]"
                >
                  <Icon
                    name="heroicons:arrow-up-tray"
                    class="h-8 w-8 mx-auto mb-2 text-primary-600"
                  />
                  <p class="text-sm font-medium">Upload Design</p>
                </button>
              </div>
            </div>

            <!-- Previous Designs Selection -->
            <div v-if="designMethod === 'previous' && previousDesigns.length > 0" class="space-y-4">
              <h3 class="text-sm font-semibold text-secondary-800">
                Select Previous Design
              </h3>
              <div class="grid grid-cols-3 gap-2">
                <div
                  v-for="design in previousDesigns"
                  :key="design.id"
                  @click="selectedPreviousDesign = design"
                  :class="[
                    'relative cursor-pointer rounded-lg border-2 p-1.5 transition-all',
                    selectedPreviousDesign?.id === design.id
                      ? 'border-primary-500 bg-primary-50'
                      : 'border-secondary-200 hover:border-secondary-300',
                  ]"
                >
                  <!-- Correct aspect ratio: 90mm x 54mm = 5:3 -->
                  <div class="w-full bg-secondary-100 rounded mb-1 overflow-hidden" style="aspect-ratio: 5/3">
                    <img
                      v-if="design.front_design"
                      :src="design.front_design"
                      alt="Front Design"
                      class="w-full h-full object-cover"
                    />
                    <div v-else class="flex items-center justify-center h-full text-secondary-400">
                      <Icon name="heroicons:photo" class="h-5 w-5" />
                    </div>
                  </div>
                  <p class="text-xs font-medium text-center truncate">
                    {{ design.card_name || 'Card ' + design.id }}
                  </p>
                  <p class="text-xs text-secondary-500 text-center truncate">
                    {{ formatDate(design.created_at) }}
                  </p>
                  <Icon
                    v-if="selectedPreviousDesign?.id === design.id"
                    name="heroicons:check-circle"
                    class="absolute top-0.5 right-0.5 h-3.5 w-3.5 text-primary-500"
                  />
                </div>
              </div>
            </div>

            <!-- Template Selection -->
            <div v-if="designMethod === 'template'" class="space-y-4">
              <h3 class="text-sm font-semibold text-secondary-800">
                Choose Template
              </h3>

              <!-- Loading State -->
              <div v-if="templatesLoading" class="flex justify-center py-8">
                <div class="spinner"></div>
              </div>

              <!-- No Templates -->
              <div v-else-if="availableTemplates.length === 0" class="text-center py-8">
                <Icon name="heroicons:photo" class="h-12 w-12 text-secondary-300 mx-auto mb-2" />
                <p class="text-secondary-500">No templates available for your plan</p>
              </div>

              <!-- Template Options - Compact Cards (90mm x 54mm ratio) -->
              <div v-else class="grid grid-cols-3 gap-2">
                <div
                  v-for="template in availableTemplates"
                  :key="template.id"
                  @click="selectedTemplate = template.id"
                  :class="[
                    'relative cursor-pointer rounded-lg border-2 p-1.5 transition-all',
                    selectedTemplate === template.id
                      ? 'border-primary-500 bg-primary-50'
                      : 'border-secondary-200 hover:border-secondary-300',
                  ]"
                >
                  <!-- Correct aspect ratio: 90mm width x 54mm height = 5:3 -->
                  <div
                    class="w-full rounded mb-1 relative overflow-hidden bg-secondary-100"
                    style="aspect-ratio: 5/3"
                  >
                    <!-- Template Image from API -->
                    <img
                      v-if="template.front_image_url"
                      :src="getTemplateImageUrl(template.front_image_url)"
                      :alt="template.name"
                      class="w-full h-full object-cover"
                    />
                    <!-- Fallback Preview if no image -->
                    <div v-else class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-700 p-1.5 text-white flex flex-col justify-between text-xs">
                      <div>
                        <div class="font-bold">{{ template.name }}</div>
                        <div class="opacity-80 text-xs">Template</div>
                      </div>
                      <div class="text-right text-xs">
                        <div>NFC Card</div>
                      </div>
                    </div>
                  </div>
                  <p class="text-xs font-medium text-center truncate">
                    {{ template.name }}
                  </p>
                  <Icon
                    v-if="selectedTemplate === template.id"
                    name="heroicons:check-circle"
                    class="absolute top-0.5 right-0.5 h-3.5 w-3.5 text-primary-500"
                  />
                </div>
              </div>
            </div>

            <!-- Custom Design Upload -->
            <div v-if="designMethod === 'custom'" class="space-y-6">
              <h3 class="text-lg font-semibold text-secondary-800">
                Upload Your Design
              </h3>

              <div class="space-y-4">
                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-2"
                    >Front Side *</label
                  >
                  <div
                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-secondary-300 border-dashed rounded-lg hover:border-secondary-400 transition-colors"
                  >
                    <div class="space-y-1 text-center">
                      <Icon
                        name="heroicons:document-arrow-up"
                        class="mx-auto h-12 w-12 text-secondary-400"
                      />
                      <div class="flex text-sm text-secondary-600">
                        <label
                          class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500"
                        >
                          <span>Upload front design</span>
                          <input
                            type="file"
                            class="sr-only"
                            accept="image/*"
                            @change="handleFrontUpload"
                          />
                        </label>
                      </div>
                      <p class="text-xs text-secondary-500">
                        PNG, JPG, PDF. Size: 90mm x 54mm
                      </p>
                    </div>
                  </div>
                  <div v-if="cardDesign.front" class="mt-2">
                    <img
                      :src="cardDesign.front"
                      alt="Front Design"
                      class="h-24 w-auto"
                    />
                  </div>
                </div>

                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-2"
                    >Back Side *</label
                  >
                  <div
                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-secondary-300 border-dashed rounded-lg hover:border-secondary-400 transition-colors"
                  >
                    <div class="space-y-1 text-center">
                      <Icon
                        name="heroicons:document-arrow-up"
                        class="mx-auto h-12 w-12 text-secondary-400"
                      />
                      <div class="flex text-sm text-secondary-600">
                        <label
                          class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500"
                        >
                          <span>Upload back design</span>
                          <input
                            type="file"
                            class="sr-only"
                            accept="image/*"
                            @change="handleBackUpload"
                          />
                        </label>
                      </div>
                      <p class="text-xs text-secondary-500">
                        PNG, JPG, PDF. Size: 90mm x 54mm
                      </p>
                    </div>
                  </div>
                  <div v-if="cardDesign.back" class="mt-2">
                    <img
                      :src="cardDesign.back"
                      alt="Back Design"
                      class="h-24 w-auto"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Card Design Preview -->
          <div v-if="hasSelectedDesign" class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-2xl font-bold text-secondary-900 mb-6">
              Card Design Preview
            </h2>

            <!-- Use same grid system as template selection for consistent sizing -->
            <div class="grid grid-cols-3 gap-2">
              <div class="col-start-2">
                <div class="relative">
                  <!-- Card Container with same size as selection cards -->
                  <div class="w-full bg-white rounded-lg shadow-2xl overflow-hidden border-2 border-primary-500" style="aspect-ratio: 5/3">
                    
                    <!-- Previous Design Preview -->
                    <div v-if="designMethod === 'previous' && selectedPreviousDesign" class="w-full h-full">
                      <img
                        v-if="selectedPreviousDesign.front_design"
                        :src="selectedPreviousDesign.front_design"
                        alt="Previous Design"
                        class="w-full h-full object-cover"
                      />
                      <div v-else class="w-full h-full bg-gradient-to-br from-secondary-200 to-secondary-300 flex items-center justify-center">
                        <div class="text-center text-secondary-600">
                          <Icon name="heroicons:photo" class="h-5 w-5 mx-auto mb-1" />
                          <p class="text-xs">Previous</p>
                        </div>
                      </div>
                    </div>

                    <!-- Template Preview -->
                    <div v-else-if="designMethod === 'template' && selectedTemplate" class="w-full h-full">
                      <!-- Show template image from API -->
                      <img
                        v-if="selectedTemplateData?.front_image_url"
                        :src="getTemplateImageUrl(selectedTemplateData.front_image_url)"
                        :alt="selectedTemplateData?.name || 'Template'"
                        class="w-full h-full object-cover"
                      />
                      <!-- Fallback gradient if no image -->
                      <div v-else class="w-full h-full bg-gradient-to-br from-blue-500 to-blue-700 p-1.5">
                        <div class="text-white h-full flex flex-col justify-between text-xs">
                          <div class="flex items-start gap-1.5">
                            <div v-if="companyInfo.logo" class="w-6 h-6 rounded bg-white/20 flex-shrink-0 overflow-hidden">
                              <img :src="companyInfo.logo" alt="Logo" class="w-full h-full object-contain" />
                            </div>
                            <div class="flex-1 min-w-0">
                              <div class="font-bold text-[10px] truncate">{{ companyInfo.name || 'Company Name' }}</div>
                              <div class="font-semibold truncate">{{ adminInfo.name || 'Your Name' }}</div>
                              <div class="opacity-90 text-[9px]">{{ adminInfo.position || 'Position' }}</div>
                            </div>
                          </div>
                          <div class="text-[9px] space-y-0.5">
                            <div>{{ adminInfo.contactNumber || '+60 12-345 6789' }}</div>
                            <div class="truncate">{{ adminInfo.email || 'email@company.com' }}</div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Custom Upload Preview -->
                    <div v-else-if="designMethod === 'custom' && cardDesign.front" class="w-full h-full">
                      <img
                        :src="cardDesign.front"
                        alt="Custom Design"
                        class="w-full h-full object-cover"
                      />
                    </div>

                    <!-- No Design Selected -->
                    <div v-else class="w-full h-full bg-secondary-100 flex items-center justify-center">
                      <div class="text-center text-secondary-500">
                        <Icon name="heroicons:photo" class="h-5 w-5 mx-auto mb-1" />
                        <p class="text-xs">Select design</p>
                      </div>
                    </div>
                  </div>

                  <!-- Design Info Badge -->
                  <div class="mt-2 text-center">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                      <Icon name="heroicons:sparkles" class="h-3 w-3 mr-1" />
                      {{ 
                        designMethod === 'previous' ? 'Previous' : 
                        designMethod === 'template' ? (selectedTemplateData?.name || 'Template') : 
                        'Custom'
                      }}
                    </span>
                    <p class="text-xs text-secondary-500 mt-1">
                      90mm × 54mm
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Order Summary -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-2xl font-bold text-secondary-900 mb-6">
              Order Summary
            </h2>

            <div class="space-y-4">
              <!-- Company Info Summary -->
              <div class="p-3 bg-primary-50 border border-primary-200 rounded-lg">
                <div class="flex items-center gap-3">
                  <div v-if="companyInfo.logo" class="w-10 h-10 rounded-lg bg-white border border-primary-200 overflow-hidden flex-shrink-0">
                    <img :src="companyInfo.logo" alt="Company Logo" class="w-full h-full object-contain" />
                  </div>
                  <div v-else class="w-10 h-10 rounded-lg bg-primary-100 border border-primary-200 flex items-center justify-center flex-shrink-0">
                    <Icon name="heroicons:building-office-2" class="h-5 w-5 text-primary-600" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs text-primary-600 font-medium">Company</p>
                    <p class="text-sm font-semibold text-primary-900 truncate">{{ companyInfo.name || 'Not set' }}</p>
                  </div>
                </div>
              </div>

              <div class="flex justify-between items-center p-3 bg-secondary-50 rounded-lg">
                <span class="text-sm font-medium text-secondary-700">Admin Card:</span>
                <span class="text-sm font-semibold text-secondary-900">
                  {{ includeAdminCard ? '1 card' : 'Not included' }}
                </span>
              </div>
              
              <div class="flex justify-between items-center p-3 bg-secondary-50 rounded-lg">
                <span class="text-sm font-medium text-secondary-700">Employee Cards:</span>
                <span class="text-sm font-semibold text-secondary-900">
                  {{ uploadedEmployees.length }} {{ uploadedEmployees.length === 1 ? 'card' : 'cards' }}
                </span>
              </div>
              
              <div class="border-t border-secondary-200 pt-4">
                <div class="flex justify-between items-center">
                  <span class="text-lg font-bold text-secondary-900">Total Cards:</span>
                  <span class="text-2xl font-bold text-primary-600">
                    {{ totalCardsCount }}
                  </span>
                </div>
              </div>

              <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-start">
                  <Icon name="heroicons:information-circle" class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" />
                  <div class="ml-3">
                    <p class="text-sm text-blue-800">
                      <strong>Design Method:</strong>
                      {{ designMethod === 'previous' ? 'Previous Design' : designMethod === 'template' ? 'Template' : 'Custom Upload' }}
                    </p>
                    <p class="text-xs text-blue-600 mt-1">
                      All cards will use the same design
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation & Action Buttons -->
      <div class="sticky bottom-0 bg-white border-t-2 border-secondary-200 shadow-lg py-4 px-6 mt-12 -mx-4 sm:-mx-6 lg:-mx-8">
        <div class="max-w-7xl mx-auto">
          <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <!-- Left: Back Button -->
            <button
              @click="goBack"
              class="w-full sm:w-auto px-6 py-3 border-2 border-secondary-300 text-secondary-700 rounded-xl font-medium hover:bg-secondary-50 transition-all hover:border-secondary-400 flex items-center justify-center"
            >
              <Icon name="heroicons:arrow-left" class="h-5 w-5 mr-2" />
              Back to Card Management
            </button>

            <!-- Right: Order Info + Submit Button -->
            <div class="flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto">
              <!-- Order Summary Badge -->
              <div v-if="totalCardsCount > 0" class="bg-primary-50 border border-primary-200 px-4 py-2 rounded-lg">
                <div class="flex items-center gap-2">
                  <Icon name="heroicons:shopping-cart" class="h-5 w-5 text-primary-600" />
                  <span class="text-sm font-medium text-primary-900">
                    {{ totalCardsCount }} {{ totalCardsCount === 1 ? 'Card' : 'Cards' }}
                  </span>
                  <span v-if="includeAdminCard" class="text-xs text-primary-600">
                    ({{ includeAdminCard ? '1 Admin' : '' }}{{ uploadedEmployees.length > 0 ? ' + ' + uploadedEmployees.length + ' Employee' + (uploadedEmployees.length > 1 ? 's' : '') : '' }})
                  </span>
                  <span v-else-if="uploadedEmployees.length > 0" class="text-xs text-primary-600">
                    ({{ uploadedEmployees.length }} Employee{{ uploadedEmployees.length > 1 ? 's' : '' }})
                  </span>
                </div>
              </div>

              <!-- Submit Button -->
              <button
                @click="placeOrder"
                :disabled="!isFormValid || isPlacingOrder"
                class="w-full sm:w-auto px-8 py-3 bg-primary-600 text-white rounded-xl font-semibold hover:bg-primary-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl disabled:hover:shadow-lg flex items-center justify-center gap-2"
              >
                <Icon v-if="!isPlacingOrder" name="heroicons:paper-airplane" class="h-5 w-5" />
                <div v-else class="spinner"></div>
                <span v-if="isPlacingOrder">Submitting Order...</span>
                <span v-else>Submit Order for Approval</span>
              </button>
            </div>
          </div>

          <!-- Validation Message -->
          <div v-if="!isFormValid && totalCardsCount === 0" class="mt-3 text-center">
            <p class="text-sm text-red-600 flex items-center justify-center gap-1">
              <Icon name="heroicons:exclamation-circle" class="h-4 w-4" />
              Please add at least one card recipient and select a design method
            </p>
          </div>
          <div v-else-if="!isFormValid && includeAdminCard && !adminInfo.position" class="mt-3 text-center">
            <p class="text-sm text-red-600 flex items-center justify-center gap-1">
              <Icon name="heroicons:exclamation-circle" class="h-4 w-4" />
              Please fill in all required Admin fields
            </p>
          </div>
          <div v-else-if="!isFormValid" class="mt-3 text-center">
            <p class="text-sm text-red-600 flex items-center justify-center gap-1">
              <Icon name="heroicons:exclamation-circle" class="h-4 w-4" />
              Please select a design method to continue
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <Transition name="modal">
      <div v-if="showConfirmModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
          <div
            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
            @click="showConfirmModal = false"
          ></div>
          <div
            class="bg-white rounded-lg max-w-2xl w-full p-6 relative z-10 shadow-xl"
          >
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-xl font-bold text-secondary-900">
                Confirm Order
              </h3>
              <button
                @click="showConfirmModal = false"
                class="text-secondary-400 hover:text-secondary-600"
              >
                <Icon name="heroicons:x-mark" class="h-6 w-6" />
              </button>
            </div>

            <div class="space-y-4">
              <!-- Order Summary -->
              <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                  <Icon
                    name="heroicons:information-circle"
                    class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5"
                  />
                  <div class="ml-3">
                    <h4 class="text-sm font-semibold text-blue-900 mb-2">
                      Order Summary
                    </h4>
                    <div class="text-sm text-blue-800 space-y-1">
                      <p>
                        <strong>Total Cards:</strong> {{ totalCardsCount }}
                        <span v-if="includeAdminCard">(1 Admin + {{ uploadedEmployees.length }} Employees)</span>
                        <span v-else>({{ uploadedEmployees.length }} Employees only)</span>
                      </p>
                      <p>
                        <strong>Design Method:</strong>
                        {{
                          designMethod === "previous"
                            ? "Previous Design"
                            : designMethod === "template"
                            ? "Template (" + selectedTemplate + ")"
                            : "Custom Upload"
                        }}
                      </p>
                      <p><strong>Plan:</strong> Business Plan</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Admin Card Info -->
              <div v-if="includeAdminCard" class="border border-primary-200 rounded-lg p-4 bg-primary-50">
                <h4 class="text-sm font-semibold text-primary-900 mb-3 flex items-center">
                  <Icon name="heroicons:user-circle" class="h-5 w-5 mr-2" />
                  Admin Card
                </h4>
                <div class="grid grid-cols-2 gap-3 text-sm">
                  <div>
                    <span class="text-primary-700">Name:</span>
                    <span class="ml-2 text-primary-900 font-medium">{{ adminInfo.name }}</span>
                  </div>
                  <div>
                    <span class="text-primary-700">Position:</span>
                    <span class="ml-2 text-primary-900 font-medium">{{ adminInfo.position }}</span>
                  </div>
                  <div>
                    <span class="text-primary-700">Email:</span>
                    <span class="ml-2 text-primary-900">{{ adminInfo.email }}</span>
                  </div>
                  <div>
                    <span class="text-primary-700">Phone:</span>
                    <span class="ml-2 text-primary-900">{{ adminInfo.contactNumber }}</span>
                  </div>
                  <div class="col-span-2">
                    <span class="text-primary-700">Business Address:</span>
                    <span class="ml-2 text-primary-900">{{ adminInfo.address }}</span>
                  </div>
                </div>
              </div>
              
              <!-- Delivery Address Info -->
              <div class="border border-blue-200 rounded-lg p-4 bg-blue-50">
                <h4 class="text-sm font-semibold text-blue-900 mb-2 flex items-center">
                  <Icon name="heroicons:truck" class="h-5 w-5 mr-2" />
                  Delivery Address (All Cards)
                </h4>
                <p class="text-sm text-blue-800">{{ adminInfo.deliveryAddress }}</p>
              </div>

              <!-- Employee List Preview -->
              <div v-if="uploadedEmployees.length > 0" class="border border-secondary-200 rounded-lg">
                <div class="bg-secondary-50 px-4 py-2 border-b border-secondary-200">
                  <h4 class="text-sm font-semibold text-secondary-900 flex items-center">
                    <Icon name="heroicons:user-group" class="h-5 w-5 mr-2" />
                    Employee Cards ({{ uploadedEmployees.length }})
                  </h4>
                </div>
                <div class="max-h-64 overflow-y-auto">
                  <table class="min-w-full divide-y divide-secondary-200">
                    <thead class="bg-secondary-50 sticky top-0">
                      <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-secondary-500 uppercase">
                          Name
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-secondary-500 uppercase">
                          Email
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-secondary-500 uppercase">
                          Position
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-secondary-200">
                      <tr v-for="(emp, index) in uploadedEmployees" :key="index">
                        <td class="px-4 py-2 text-sm text-secondary-900">{{ emp.name }}</td>
                        <td class="px-4 py-2 text-sm text-secondary-600">{{ emp.email }}</td>
                        <td class="px-4 py-2 text-sm text-secondary-600">{{ emp.position }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Warning Message -->
              <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex">
                  <Icon
                    name="heroicons:exclamation-triangle"
                    class="h-5 w-5 text-yellow-600 flex-shrink-0 mt-0.5"
                  />
                  <div class="ml-3">
                    <p class="text-sm text-yellow-800">
                      A notification will be sent to Super Admin for approval.
                      The order will be processed after admin confirmation.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex items-center justify-end space-x-3 pt-4">
                <button
                  @click="showConfirmModal = false"
                  class="btn btn-outline"
                  :disabled="isPlacingOrder"
                >
                  Cancel
                </button>
                <button
                  @click="confirmOrder"
                  class="btn btn-primary"
                  :disabled="isPlacingOrder"
                >
                  <div v-if="isPlacingOrder" class="spinner mr-2"></div>
                  {{
                    isPlacingOrder ? "Processing..." : "Confirm & Submit Order"
                  }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
</template>

<script setup>
// Meta tags
useHead({
  title: "Customize NFC Card - NFCGo",
  meta: [
    {
      name: "description",
      content:
        "Customize your NFC business card with professional templates or your own design.",
    },
  ],
});

// Stores
const authStore = useAuthStore();
const { $toast } = useNuxtApp();

// Route and router
const router = useRouter();

// Reactive data
const includeAdminCard = ref(false); // Include admin's own card
const employeeAddMethod = ref("manual"); // 'manual' or 'upload'
const employeeFile = ref(null);
const uploadedEmployees = ref([]);
const showConfirmModal = ref(false);
const designMethod = ref("template"); // 'previous', 'template', or 'custom'
const selectedTemplate = ref("basic");
const selectedPreviousDesign = ref(null);
const previousDesigns = ref([]);
const isPlacingOrder = ref(false);

// Company info (shared across all cards)
const companyInfo = reactive({
  name: "",
  logo: null,
  logoFile: null,
});

// Admin info (populated from authStore, fully editable)
const adminInfo = reactive({
  name: "",
  email: "",
  position: "",
  contactNumber: "",
  website: "",
  address: "",
  deliveryAddress: "", // Master delivery address for all cards
});

// Manual employee entry (address and deliveryAddress removed - uses admin's)
const manualEmployee = reactive({
  name: "",
  email: "",
  position: "",
  contactNumber: "",
  website: "",
});

// Card design
const cardDesign = reactive({
  front: null,
  back: null,
  frontFile: null,
  backFile: null,
});

// Available templates from API
const availableTemplates = ref([]);
const templatesLoading = ref(false);

// Load templates based on user's plan
const loadTemplates = async () => {
  templatesLoading.value = true;
  try {
    const { $api } = useNuxtApp();
    const userPlan = authStore.user?.subscription_plan || sessionStorage.getItem("selectedPlan") || 'free';
    const response = await $api.get(`/card-templates?plan=${userPlan}`);
    
    if (response.success && response.data) {
      availableTemplates.value = response.data;
      // Auto-select first template if none selected
      if (availableTemplates.value.length > 0 && !selectedTemplate.value) {
        selectedTemplate.value = availableTemplates.value[0].id;
      }
    }
  } catch (error) {
    console.error("Failed to load templates:", error);
    // Fallback to default templates if API fails
    availableTemplates.value = [
      { id: "default", name: "Default Template", front_image_url: null }
    ];
  } finally {
    templatesLoading.value = false;
  }
};

// Check if user has premium plan
const isPremiumPlan = computed(() => {
  const selectedPlan = sessionStorage.getItem("selectedPlan");
  return ["premium", "business"].includes(selectedPlan);
});

// Total cards count
const totalCardsCount = computed(() => {
  let count = 0;
  if (includeAdminCard.value) count += 1;
  count += uploadedEmployees.value.length;
  return count;
});

// Check if has previous designs
const hasPreviousDesigns = computed(() => {
  return previousDesigns.value && previousDesigns.value.length > 0;
});

// Check if user has selected a design
const hasSelectedDesign = computed(() => {
  if (designMethod.value === "previous") {
    return selectedPreviousDesign.value !== null;
  } else if (designMethod.value === "template") {
    return selectedTemplate.value !== null;
  } else if (designMethod.value === "custom") {
    return cardDesign.front !== null;
  }
  return false;
});

// Form validation
const isFormValid = computed(() => {
  // Company name is always required
  if (!companyInfo.name) {
    return false;
  }

  // Must have at least one card (admin or employees)
  if (totalCardsCount.value === 0) {
    return false;
  }

  // If admin card is included, all admin fields are required
  if (includeAdminCard.value) {
    if (
      !adminInfo.name ||
      !adminInfo.email ||
      !adminInfo.position ||
      !adminInfo.contactNumber ||
      !adminInfo.address ||
      !adminInfo.deliveryAddress
    ) {
      return false;
    }
  }

  // If only employees (no admin card), still need delivery address
  if (!includeAdminCard.value && uploadedEmployees.value.length > 0) {
    if (!adminInfo.deliveryAddress) {
      return false;
    }
  }

  // Design validation
  if (designMethod.value === "previous") {
    return selectedPreviousDesign.value !== null;
  } else if (designMethod.value === "template") {
    return selectedTemplate.value !== null;
  } else if (designMethod.value === "custom") {
    return cardDesign.front && cardDesign.back;
  }

  return false;
});

// Check if user is authenticated and load data
onMounted(async () => {
  if (!authStore.isAuthenticated) {
    router.push("/UserAccount/login");
    return;
  }

  // Load admin info with all available fields
  if (authStore.user) {
    adminInfo.name = authStore.user.full_name || "";
    adminInfo.email = authStore.user.email || "";
    adminInfo.position = authStore.user.job_title || "";
    adminInfo.contactNumber = authStore.user.phone || authStore.user.contact_number || "";
    adminInfo.website = authStore.user.website || "";
    adminInfo.address = authStore.user.address || authStore.user.business_address || "";
    adminInfo.deliveryAddress = authStore.user.delivery_address || authStore.user.address || "";
  }

  // Load previous designs and templates
  await Promise.all([
    loadPreviousDesigns(),
    loadTemplates(),
  ]);
});

// Load previous card designs from user's existing cards
const loadPreviousDesigns = async () => {
  try {
    const { $api } = useNuxtApp();
    const response = await $api.get("/nfc-cards");
    
    if (response.success && response.data) {
      // Filter cards that have design data
      previousDesigns.value = response.data.filter(card => 
        card.front_design || card.back_design || card.design_template
      );
    }
  } catch (error) {
    console.error("Failed to load previous designs:", error);
    // Non-critical error, just log it
  }
};

// Format date helper
const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", { 
    year: "numeric", 
    month: "short", 
    day: "numeric" 
  });
};

// Get template image URL (handle relative and absolute URLs)
const config = useRuntimeConfig();
const getTemplateImageUrl = (url) => {
  if (!url) return '';
  if (url.startsWith('http')) return url;
  // Handle relative URLs - prepend API base URL
  const apiBase = config.public?.apiBase || '';
  return `${apiBase.replace('/api', '')}${url}`;
};

// Get selected template object
const selectedTemplateData = computed(() => {
  if (!selectedTemplate.value) return null;
  return availableTemplates.value.find(t => t.id === selectedTemplate.value);
});

// Handle company logo upload
const handleLogoUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  // Validate file type
  if (!file.type.startsWith("image/")) {
    $toast.error("Please upload an image file");
    return;
  }

  // Validate file size (max 2MB)
  if (file.size > 2 * 1024 * 1024) {
    $toast.error("Logo file must be less than 2MB");
    return;
  }

  companyInfo.logoFile = file;

  // Create preview URL
  const reader = new FileReader();
  reader.onload = (e) => {
    companyInfo.logo = e.target.result;
  };
  reader.readAsDataURL(file);

  $toast.success("Logo uploaded successfully");
};

// Remove company logo
const removeLogo = () => {
  companyInfo.logo = null;
  companyInfo.logoFile = null;
};

// Handle employee file upload
const handleEmployeeFileUpload = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  employeeFile.value = file;

  try {
    // Parse CSV/XLSX file
    const reader = new FileReader();
    reader.onload = async (e) => {
      const data = e.target.result;

      if (file.name.endsWith(".csv")) {
        parseCSV(data);
      } else if (file.name.endsWith(".xlsx")) {
        // For XLSX, you would need a library like xlsx
        $toast.info("XLSX parsing will be implemented");
      }
    };

    if (file.name.endsWith(".csv")) {
      reader.readAsText(file);
    } else {
      reader.readAsArrayBuffer(file);
    }
  } catch (error) {
    console.error("File upload error:", error);
    $toast.error("Failed to read file. Please try again.");
  }
};

// Parse CSV data
const parseCSV = (csvData) => {
  try {
    const lines = csvData.split("\n");
    const headers = lines[0].split(",").map((h) => h.trim().toLowerCase());

    const employees = [];
    for (let i = 1; i < lines.length; i++) {
      if (!lines[i].trim()) continue;

      const values = lines[i].split(",");
      const employee = {};

      headers.forEach((header, index) => {
        employee[header] = values[index]?.trim() || "";
      });

      // Validate required fields
      if (
        employee.name &&
        employee.email &&
        employee.position &&
        employee["contact number"]
      ) {
        employees.push({
          name: employee.name,
          email: employee.email,
          position: employee.position,
          contactNumber:
            employee["contact number"] || employee["contact_number"],
          website: employee.website || "",
          // address and deliveryAddress will use admin's addresses
        });
      }
    }

    uploadedEmployees.value = employees;
    $toast.success(
      `Successfully parsed ${employees.length} employees from file`
    );
  } catch (error) {
    console.error("CSV parse error:", error);
    $toast.error("Failed to parse CSV file. Please check the format.");
  }
};

// Download CSV template
const downloadCSVTemplate = () => {
  const csvContent =
    "Name,Email,Position,Contact Number,Website\n" +
    'John Doe,john@example.com,Sales Manager,+60 12-345 6789,https://example.com\n' +
    'Jane Smith,jane@example.com,Marketing Lead,+60 12-345 6790,https://example.com';

  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = "employee_card_order_template.csv";
  a.click();
  window.URL.revokeObjectURL(url);
  $toast.success("CSV template downloaded!");
};

// Download XLSX template
const downloadXLSXTemplate = async () => {
  try {
    const template = [
      {
        Name: "John Doe",
        Email: "john@example.com",
        Position: "Sales Manager",
        "Contact Number": "+60 12-345 6789",
        Website: "https://example.com",
      },
      {
        Name: "Jane Smith",
        Email: "jane@example.com",
        Position: "Marketing Lead",
        "Contact Number": "+60 12-345 6790",
        Website: "https://example.com",
      },
    ];

    const XLSX = await import("xlsx");
    const ws = XLSX.utils.json_to_sheet(template);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Employees");
    XLSX.writeFile(wb, "employee_card_order_template.xlsx");
    $toast.success("XLSX template downloaded!");
  } catch (error) {
    console.error("XLSX template download error:", error);
    $toast.error("Failed to generate XLSX template.");
  }
};

// Add manual employee
const addManualEmployee = () => {
  // Validate email is not duplicate
  const emailExists = uploadedEmployees.value.some(
    (emp) => emp.email.toLowerCase() === manualEmployee.email.toLowerCase()
  );

  if (emailExists) {
    $toast.error("An employee with this email already exists");
    return;
  }

  // Check if this is the current user's email
  if (
    authStore.user &&
    manualEmployee.email.toLowerCase() === authStore.user.email.toLowerCase()
  ) {
    $toast.error("Cannot add yourself as an employee");
    return;
  }

  uploadedEmployees.value.push({
    name: manualEmployee.name,
    email: manualEmployee.email,
    position: manualEmployee.position,
    contactNumber: manualEmployee.contactNumber,
    website: manualEmployee.website,
    // address and deliveryAddress will be taken from adminInfo
  });

  $toast.success(`Added ${manualEmployee.name} to the list`);
  resetManualForm();
};

// Remove employee from list
const removeEmployee = (index) => {
  const employee = uploadedEmployees.value[index];
  uploadedEmployees.value.splice(index, 1);
  $toast.success(`Removed ${employee.name} from the list`);
};

// Reset manual form
const resetManualForm = () => {
  manualEmployee.name = "";
  manualEmployee.email = "";
  manualEmployee.position = "";
  manualEmployee.contactNumber = "";
  manualEmployee.website = "";
};

// Handle front design upload
const handleFrontUpload = async (event) => {
  const file = event.target.files[0];
  if (file) {
    try {
      const reader = new FileReader();
      reader.onload = (e) => {
        cardDesign.front = e.target.result;
      };
      reader.readAsDataURL(file);

      // Store file for later upload
      cardDesign.frontFile = file;
    } catch (error) {
      console.error("Front design upload error:", error);
      $toast.error("Failed to upload front design. Please try again.");
    }
  }
};

// Handle back design upload
const handleBackUpload = async (event) => {
  const file = event.target.files[0];
  if (file) {
    try {
      const reader = new FileReader();
      reader.onload = (e) => {
        cardDesign.back = e.target.result;
      };
      reader.readAsDataURL(file);

      // Store file for later upload
      cardDesign.backFile = file;
    } catch (error) {
      console.error("Back design upload error:", error);
      $toast.error("Failed to upload back design. Please try again.");
    }
  }
};

// Upload card designs to backend
const uploadCardDesigns = async () => {
  if (
    designMethod.value === "custom" &&
    cardDesign.frontFile &&
    cardDesign.backFile
  ) {
    try {
      const formData = new FormData();
      formData.append("front_design", cardDesign.frontFile);
      formData.append("back_design", cardDesign.backFile);
      formData.append("design_method", designMethod.value);
      formData.append("selected_template", selectedTemplate.value);

      const { $api } = useNuxtApp();
      const response = await $api.post(
        "/business/upload-card-design",
        formData,
        {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        }
      );

      if (response.success) {
        // Update design URLs with backend response
        if (response.data.front_design_url) {
          cardDesign.front = response.data.front_design_url;
        }
        if (response.data.back_design_url) {
          cardDesign.back = response.data.back_design_url;
        }
        $toast.success("Card designs uploaded successfully!");
        return true;
      }
    } catch (error) {
      console.error("Card design upload error:", error);
      $toast.error("Failed to upload card designs. Please try again.");
      return false;
    }
  }
  return true; // For template method, no upload needed
};

// Go back to plan selection
const goBack = () => {
  router.push(
    "/UserDashboard/UserManagement/BusinessPlanUser/BusinessCardManagement"
  );
};

// Place order for Business Plan NFC Card
const placeOrder = async () => {
  // Show confirmation modal instead of directly placing order
  showConfirmModal.value = true;
};

// Confirm and submit order - send notification to super admin for each card
const confirmOrder = async () => {
  isPlacingOrder.value = true;

  try {
    const { $api } = useNuxtApp();

    // Upload designs if custom method
    if (designMethod.value === "custom") {
      const uploadSuccess = await uploadCardDesigns();
      if (!uploadSuccess) {
        isPlacingOrder.value = false;
        return;
      }
    }

    // Get current user for tracking who placed the order
    const currentUserId = authStore.user?.id;

    // Get super admin user
    let superAdminId = 1; // Default fallback
    try {
      const adminResponse = await $api.get("/admin/super-admin");
      if (adminResponse.success && adminResponse.data?.id) {
        superAdminId = adminResponse.data.id;
      }
    } catch (error) {
      console.warn("Could not fetch super admin, using default ID 1", error);
    }

    // Prepare design data based on method
    let designData = {};
    if (designMethod.value === "previous" && selectedPreviousDesign.value) {
      designData = {
        design_method: "previous",
        previous_design_id: selectedPreviousDesign.value.id,
        front_design: selectedPreviousDesign.value.front_design,
        back_design: selectedPreviousDesign.value.back_design,
        selected_template: selectedPreviousDesign.value.design_template,
      };
    } else if (designMethod.value === "template") {
      designData = {
        design_method: "template",
        selected_template: selectedTemplate.value,
      };
    } else if (designMethod.value === "custom") {
      designData = {
        design_method: "custom",
        front_design: cardDesign.front,
        back_design: cardDesign.back,
      };
    }

    // Prepare all cards data in one batch order
    const cards = [];

    // Add admin card if included
    if (includeAdminCard.value) {
      cards.push({
        name: adminInfo.name,
        email: adminInfo.email,
        position: adminInfo.position,
        contact_number: adminInfo.contactNumber,
        website: adminInfo.website || "",
        business_address: adminInfo.address,
        is_admin_card: true,
      });
    }

    // Add all employee cards
    for (const employee of uploadedEmployees.value) {
      cards.push({
        name: employee.name,
        email: employee.email,
        position: employee.position,
        contact_number: employee.contactNumber || employee.contact_number,
        website: employee.website || "",
        business_address: adminInfo.address,
        is_employee_card: true,
      });
    }

    // Create one batch notification with all cards
    const batchOrderData = {
      requesting_user_id: currentUserId,
      requesting_user_name: adminInfo.name,
      requesting_user_email: adminInfo.email,
      subscription_plan: "business",
      delivery_address: adminInfo.deliveryAddress, // Master delivery address for all cards
      // Company information (shared across all cards)
      company_name: companyInfo.name,
      company_logo: companyInfo.logo, // Base64 encoded image
      total_cards: cards.length,
      include_admin: includeAdminCard.value,
      employee_count: uploadedEmployees.value.length,
      cards: cards, // Array of all card data
      ...designData,
    };

    const notification = {
      user_id: superAdminId,
      type: "business_card_order_request",
      title: `Batch NFC Card Order from ${adminInfo.name}`,
      message: `${adminInfo.name} has requested ${cards.length} NFC card${cards.length > 1 ? 's' : ''} (${includeAdminCard.value ? '1 Admin + ' : ''}${uploadedEmployees.value.length} Employee${uploadedEmployees.value.length !== 1 ? 's' : ''}). Please review and approve or reject the order.`,
      priority: "urgent",
      sticky: true,
      data: batchOrderData,
    };

    // Send single batch notification
    const result = await $api.post("/notifications", notification);

    const allSuccess = result.success;

    if (allSuccess) {
      showConfirmModal.value = false;
      $toast.success(
        `✅ Batch order submitted successfully! ${totalCardsCount.value} card${totalCardsCount.value > 1 ? 's' : ''} have been sent to the Super Admin for approval.`
      );

      // Redirect to card management page after a short delay
      setTimeout(() => {
        router.push(
          "/UserDashboard/UserManagement/BusinessPlanUser/BusinessCardManagement"
        );
      }, 2000);
    } else {
      throw new Error("Some notifications failed to send");
    }
  } catch (error) {
    console.error("Place order error:", error);
    const errorMessage = error.response?.data?.message || error.message || "Failed to place order. Please try again.";
    $toast.error(errorMessage);
  } finally {
    isPlacingOrder.value = false;
  }
};
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

.rotate-y-0 {
  transform: rotateY(0deg);
}

.rotate-y-180 {
  transform: rotateY(180deg);
}

.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid #e5e7eb;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
  display: inline-block;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
