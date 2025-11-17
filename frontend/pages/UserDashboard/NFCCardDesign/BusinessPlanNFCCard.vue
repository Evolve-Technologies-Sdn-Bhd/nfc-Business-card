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

      <!-- Order Type Selection -->
      <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
        <h2 class="text-2xl font-bold text-secondary-900 mb-6">Order Type</h2>
        <div class="grid grid-cols-2 gap-4">
          <button
            @click="orderType = 'single'"
            :class="[
              'p-6 rounded-lg border-2 transition-all',
              orderType === 'single'
                ? 'border-primary-500 bg-primary-50'
                : 'border-secondary-200 hover:border-secondary-300',
            ]"
          >
            <Icon
              name="heroicons:user"
              class="h-12 w-12 mx-auto mb-3 text-primary-600"
            />
            <p class="text-lg font-semibold text-secondary-900 mb-2">
              Single Employee
            </p>
            <p class="text-sm text-secondary-600">
              Order card for one employee manually
            </p>
          </button>
          <button
            @click="orderType = 'bulk'"
            :class="[
              'p-6 rounded-lg border-2 transition-all',
              orderType === 'bulk'
                ? 'border-primary-500 bg-primary-50'
                : 'border-secondary-200 hover:border-secondary-300',
            ]"
          >
            <Icon
              name="heroicons:arrow-up-tray"
              class="h-12 w-12 mx-auto mb-3 text-primary-600"
            />
            <p class="text-lg font-semibold text-secondary-900 mb-2">
              Bulk Upload
            </p>
            <p class="text-sm text-secondary-600">
              Upload CSV/XLSX with multiple employees
            </p>
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Left Panel - Card Information -->
        <div class="space-y-8">
          <!-- Bulk Upload Section -->
          <div
            v-if="orderType === 'bulk'"
            class="bg-white rounded-2xl shadow-lg p-6"
          >
            <h2 class="text-2xl font-bold text-secondary-900 mb-6">
              Upload Employee Data
            </h2>

            <div class="space-y-6">
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
                      <li>Address (required)</li>
                      <li>Delivery Address (required)</li>
                    </ul>
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

              <!-- Preview Uploaded Data -->
              <div v-if="uploadedEmployees.length > 0" class="space-y-3">
                <h3 class="text-lg font-semibold text-secondary-800">
                  Preview ({{ uploadedEmployees.length }} employees)
                </h3>
                <div
                  class="max-h-64 overflow-y-auto border border-secondary-200 rounded-lg"
                >
                  <table class="min-w-full divide-y divide-secondary-200">
                    <thead class="bg-secondary-50 sticky top-0">
                      <tr>
                        <th
                          class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase"
                        >
                          Name
                        </th>
                        <th
                          class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase"
                        >
                          Email
                        </th>
                        <th
                          class="px-3 py-2 text-left text-xs font-medium text-secondary-500 uppercase"
                        >
                          Position
                        </th>
                        <th
                          class="px-3 py-2 text-center text-xs font-medium text-secondary-500 uppercase"
                        >
                          Actions
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-secondary-200">
                      <tr
                        v-for="(emp, index) in uploadedEmployees"
                        :key="index"
                      >
                        <td class="px-3 py-2 text-sm text-secondary-900">
                          {{ emp.name }}
                        </td>
                        <td class="px-3 py-2 text-sm text-secondary-600">
                          {{ emp.email }}
                        </td>
                        <td class="px-3 py-2 text-sm text-secondary-600">
                          {{ emp.position }}
                        </td>
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

              <!-- Manual Employee Entry Form -->
              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <h3 class="text-lg font-semibold text-secondary-800">
                    Add Employees Manually
                  </h3>
                  <button
                    @click="showManualEmployeeForm = !showManualEmployeeForm"
                    class="btn btn-sm btn-outline"
                  >
                    <Icon
                      :name="
                        showManualEmployeeForm
                          ? 'heroicons:minus'
                          : 'heroicons:plus'
                      "
                      class="h-4 w-4 mr-1"
                    />
                    {{ showManualEmployeeForm ? "Hide Form" : "Add Employee" }}
                  </button>
                </div>

                <!-- Manual Entry Form -->
                <div
                  v-if="showManualEmployeeForm"
                  class="border border-secondary-200 rounded-lg p-4 bg-secondary-50"
                >
                  <form @submit.prevent="addManualEmployee" class="space-y-3">
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
                        >Address *</label
                      >
                      <input
                        v-model="manualEmployee.address"
                        type="text"
                        required
                        class="w-full px-2 py-1.5 text-sm border border-secondary-300 rounded focus:ring-2 focus:ring-primary-500"
                        placeholder="Business address"
                      />
                    </div>
                    <div>
                      <label
                        class="block text-xs font-medium text-secondary-700 mb-1"
                        >Delivery Address *</label
                      >
                      <input
                        v-model="manualEmployee.deliveryAddress"
                        type="text"
                        required
                        class="w-full px-2 py-1.5 text-sm border border-secondary-300 rounded focus:ring-2 focus:ring-primary-500"
                        placeholder="Delivery address (can be same as business address)"
                      />
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
              </div>
            </div>
          </div>

          <!-- Single Employee Card Information Form -->
          <div v-else class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-2xl font-bold text-secondary-900 mb-6">
              Card Information
            </h2>

            <form @submit.prevent="saveCardInfo" class="space-y-6">
              <!-- Basic Information -->
              <div class="space-y-4">
                <h3 class="text-lg font-semibold text-secondary-800">
                  Basic Information
                </h3>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label
                      class="block text-sm font-medium text-secondary-700 mb-1"
                      >Name *</label
                    >
                    <input
                      v-model="cardInfo.name"
                      type="text"
                      required
                      class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                      placeholder="Your full name"
                    />
                  </div>
                  <div>
                    <label
                      class="block text-sm font-medium text-secondary-700 mb-1"
                      >Position *</label
                    >
                    <input
                      v-model="cardInfo.position"
                      type="text"
                      required
                      class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                      placeholder="Job title"
                    />
                  </div>
                </div>

                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-1"
                    >Contact Number *</label
                  >
                  <input
                    v-model="cardInfo.contactNumber"
                    type="tel"
                    required
                    class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder="+60 12-345 6789"
                  />
                </div>

                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-1"
                    >Email Address *</label
                  >
                  <input
                    v-model="cardInfo.email"
                    type="email"
                    required
                    class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder="your@email.com"
                  />
                </div>

                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-1"
                    >Website</label
                  >
                  <input
                    v-model="cardInfo.website"
                    type="url"
                    class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder="https://yourwebsite.com"
                  />
                </div>

                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-1"
                    >Business Address *</label
                  >
                  <textarea
                    v-model="cardInfo.address"
                    rows="3"
                    required
                    class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder="Your business address"
                  ></textarea>
                </div>

                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-1"
                    >Delivery Address *</label
                  >
                  <textarea
                    v-model="cardInfo.deliveryAddress"
                    rows="3"
                    required
                    class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder="Delivery address for your NFC card"
                  ></textarea>
                </div>

                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-1"
                    >Company Logo (Optional)</label
                  >
                  <div
                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-secondary-300 border-dashed rounded-lg hover:border-secondary-400 transition-colors"
                  >
                    <div class="space-y-1 text-center">
                      <Icon
                        name="heroicons:building-office"
                        class="mx-auto h-12 w-12 text-secondary-400"
                      />
                      <div class="flex text-sm text-secondary-600">
                        <label
                          class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500"
                        >
                          <span>Upload a file</span>
                          <input
                            type="file"
                            class="sr-only"
                            accept="image/*"
                            @change="handleLogoUpload"
                          />
                        </label>
                        <p class="pl-1">or drag and drop</p>
                      </div>
                      <p class="text-xs text-secondary-500">
                        PNG, JPG, GIF up to 10MB
                      </p>
                    </div>
                  </div>
                  <div v-if="cardInfo.companyLogo" class="mt-2">
                    <img
                      :src="cardInfo.companyLogo"
                      alt="Company Logo"
                      class="h-12 w-auto"
                    />
                  </div>
                </div>
              </div>

              <!-- Premium Plan Additional Information -->
              <div v-if="isPremiumPlan" class="space-y-4">
                <h3 class="text-lg font-semibold text-secondary-800">
                  Back Side Information (Premium)
                </h3>

                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-1"
                    >Company Background</label
                  >
                  <textarea
                    v-model="cardInfo.companyBackground"
                    rows="3"
                    class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder="Brief description of your company"
                  ></textarea>
                </div>

                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-1"
                    >Services Offered</label
                  >
                  <textarea
                    v-model="cardInfo.services"
                    rows="3"
                    class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder="List your main services"
                  ></textarea>
                </div>

                <div>
                  <label
                    class="block text-sm font-medium text-secondary-700 mb-1"
                    >Social Media Links</label
                  >
                  <div class="space-y-2">
                    <input
                      v-model="cardInfo.linkedin"
                      type="url"
                      class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                      placeholder="LinkedIn URL"
                    />
                    <input
                      v-model="cardInfo.twitter"
                      type="url"
                      class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                      placeholder="Twitter/X URL"
                    />
                    <input
                      v-model="cardInfo.facebook"
                      type="url"
                      class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                      placeholder="Facebook URL"
                    />
                    <input
                      v-model="cardInfo.instagram"
                      type="url"
                      class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                      placeholder="Instagram URL"
                    />
                  </div>
                </div>
              </div>

              <button
                type="submit"
                class="w-full py-3 px-4 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors"
              >
                Save Card Information
              </button>
            </form>
          </div>

          <!-- Card Examples -->
          <CardExamples />
        </div>

        <!-- Right Panel - Card Design -->
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
              <div class="grid grid-cols-2 gap-4">
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

            <!-- Template Selection -->
            <div v-if="designMethod === 'template'" class="space-y-6">
              <h3 class="text-lg font-semibold text-secondary-800">
                Choose Template
              </h3>

              <!-- Template Options -->
              <div class="grid grid-cols-2 gap-4">
                <div
                  v-for="template in availableTemplates"
                  :key="template.id"
                  @click="selectedTemplate = template.id"
                  :class="[
                    'relative cursor-pointer rounded-lg border-2 p-4 transition-all',
                    selectedTemplate === template.id
                      ? 'border-primary-500 bg-primary-50'
                      : 'border-secondary-200 hover:border-secondary-300',
                  ]"
                >
                  <div
                    class="aspect-[90/54] bg-gradient-to-br from-blue-500 to-blue-700 rounded mb-2 relative overflow-hidden"
                  >
                    <!-- Template Preview -->
                    <div class="absolute inset-0 p-2 text-white text-xs">
                      <div class="flex items-center justify-between">
                        <div>
                          <div class="font-bold">JOHN DOE</div>
                          <div class="opacity-80">Software Engineer</div>
                        </div>
                        <div class="text-right">
                          <div>+60 12-345 6789</div>
                          <div>john@example.com</div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <p class="text-sm font-medium text-center">
                    {{ template.name }}
                  </p>
                  <Icon
                    v-if="selectedTemplate === template.id"
                    name="heroicons:check"
                    class="absolute top-2 right-2 h-5 w-5 text-primary-500"
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

          <!-- Card Preview -->
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-2xl font-bold text-secondary-900 mb-6">
              Card Preview
            </h2>

            <div class="flex justify-center">
              <div class="relative">
                <!-- Card Container -->
                <div class="relative" style="width: 180px; height: 108px">
                  <!-- Front Side -->
                  <div
                    class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-700 rounded-lg shadow-lg transform rotate-y-0 transition-transform duration-500"
                    :class="{ 'rotate-y-180': showBack }"
                  >
                    <div class="absolute inset-0 p-3 text-white text-xs">
                      <div class="flex items-center justify-between h-full">
                        <div>
                          <div class="font-bold">
                            {{ cardInfo.name || "Your Name" }}
                          </div>
                          <div class="opacity-80">
                            {{ cardInfo.position || "Position" }}
                          </div>
                          <div class="mt-1">
                            {{ cardInfo.contactNumber || "Phone" }}
                          </div>
                          <div>{{ cardInfo.email || "Email" }}</div>
                        </div>
                        <div
                          v-if="cardInfo.companyLogo"
                          class="w-8 h-8 bg-white rounded"
                        ></div>
                      </div>
                    </div>
                  </div>

                  <!-- Back Side (Premium only) -->
                  <div
                    v-if="isPremiumPlan"
                    class="absolute inset-0 bg-gradient-to-br from-gray-800 to-gray-900 rounded-lg shadow-lg transform rotate-y-180 transition-transform duration-500"
                    :class="{ 'rotate-y-0': showBack }"
                  >
                    <div class="absolute inset-0 p-3 text-white text-xs">
                      <div class="h-full flex flex-col justify-between">
                        <div>
                          <div class="font-bold mb-1">Services</div>
                          <div class="opacity-80 text-xs">
                            {{ cardInfo.services || "Services" }}
                          </div>
                        </div>
                        <div class="text-center">
                          <div class="font-bold">Your Company</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Flip Button (Premium only) -->
                <button
                  v-if="isPremiumPlan"
                  @click="showBack = !showBack"
                  class="mt-4 w-full py-2 px-4 bg-secondary-100 text-secondary-700 rounded-lg text-sm font-medium hover:bg-secondary-200 transition-colors"
                >
                  {{ showBack ? "Show Front" : "Show Back" }}
                </button>
              </div>
            </div>

            <div class="mt-6 text-center">
              <p class="text-sm text-secondary-600">Card Size: 90mm x 54mm</p>
              <p class="text-xs text-secondary-500">
                Standard business card dimensions
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <div class="flex justify-between items-center mt-12">
        <button
          @click="goBack"
          class="px-6 py-3 border border-secondary-300 text-secondary-700 rounded-xl font-medium hover:bg-secondary-50 transition-colors"
        >
          <Icon name="heroicons:arrow-left" class="h-5 w-5 inline mr-2" />
          Back
        </button>
        <button
          @click="placeOrder"
          :disabled="!isFormValid || isPlacingOrder"
          class="px-6 py-3 bg-primary-600 text-white rounded-xl font-medium hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="isPlacingOrder">Placing Order...</span>
          <span v-else>Place Order</span>
          <Icon name="heroicons:check" class="h-5 w-5 inline ml-2" />
        </button>
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
                      <p v-if="orderType === 'bulk'">
                        <strong>Order Type:</strong> Bulk Upload ({{
                          uploadedEmployees.length
                        }}
                        employees)
                      </p>
                      <p v-else><strong>Order Type:</strong> Single Employee</p>
                      <p>
                        <strong>Design Method:</strong>
                        {{
                          designMethod === "template"
                            ? "Template (" + selectedTemplate + ")"
                            : "Custom Upload"
                        }}
                      </p>
                      <p><strong>Plan:</strong> Business Plan</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Employee List Preview -->
              <div
                v-if="orderType === 'bulk' && uploadedEmployees.length > 0"
                class="border border-secondary-200 rounded-lg"
              >
                <div
                  class="bg-secondary-50 px-4 py-2 border-b border-secondary-200"
                >
                  <h4 class="text-sm font-semibold text-secondary-900">
                    Employees ({{ uploadedEmployees.length }})
                  </h4>
                </div>
                <div class="max-h-64 overflow-y-auto">
                  <table class="min-w-full divide-y divide-secondary-200">
                    <thead class="bg-secondary-50 sticky top-0">
                      <tr>
                        <th
                          class="px-4 py-2 text-left text-xs font-medium text-secondary-500 uppercase"
                        >
                          Name
                        </th>
                        <th
                          class="px-4 py-2 text-left text-xs font-medium text-secondary-500 uppercase"
                        >
                          Email
                        </th>
                        <th
                          class="px-4 py-2 text-left text-xs font-medium text-secondary-500 uppercase"
                        >
                          Position
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-secondary-200">
                      <tr
                        v-for="(emp, index) in uploadedEmployees"
                        :key="index"
                      >
                        <td class="px-4 py-2 text-sm text-secondary-900">
                          {{ emp.name }}
                        </td>
                        <td class="px-4 py-2 text-sm text-secondary-600">
                          {{ emp.email }}
                        </td>
                        <td class="px-4 py-2 text-sm text-secondary-600">
                          {{ emp.position }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Single Employee Info -->
              <div v-else class="border border-secondary-200 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-secondary-900 mb-3">
                  Employee Information
                </h4>
                <div class="grid grid-cols-2 gap-3 text-sm">
                  <div>
                    <span class="text-secondary-500">Name:</span>
                    <span class="ml-2 text-secondary-900 font-medium">{{
                      cardInfo.name
                    }}</span>
                  </div>
                  <div>
                    <span class="text-secondary-500">Position:</span>
                    <span class="ml-2 text-secondary-900 font-medium">{{
                      cardInfo.position
                    }}</span>
                  </div>
                  <div>
                    <span class="text-secondary-500">Email:</span>
                    <span class="ml-2 text-secondary-900">{{
                      cardInfo.email
                    }}</span>
                  </div>
                  <div>
                    <span class="text-secondary-500">Phone:</span>
                    <span class="ml-2 text-secondary-900">{{
                      cardInfo.contactNumber
                    }}</span>
                  </div>
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
  </div>
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
const orderType = ref("single"); // 'single' or 'bulk'
const employeeFile = ref(null);
const uploadedEmployees = ref([]);
const showManualEmployeeForm = ref(false);
const showConfirmModal = ref(false);
const designMethod = ref("template");
const selectedTemplate = ref("basic");
const showBack = ref(false);
const isPlacingOrder = ref(false);

// Manual employee entry
const manualEmployee = reactive({
  name: "",
  email: "",
  position: "",
  contactNumber: "",
  website: "",
  address: "",
  deliveryAddress: "",
});

// Card information
const cardInfo = reactive({
  name: "",
  position: "",
  contactNumber: "",
  email: "",
  website: "",
  address: "",
  deliveryAddress: "",
  companyLogo: null,
  // Premium plan fields
  companyBackground: "",
  services: "",
  linkedin: "",
  twitter: "",
  facebook: "",
  instagram: "",
});

// Card design
const cardDesign = reactive({
  front: null,
  back: null,
  frontFile: null,
  backFile: null,
});

// Available templates based on plan
const availableTemplates = computed(() => {
  const selectedPlan = sessionStorage.getItem("selectedPlan");
  if (selectedPlan === "basic") {
    return [{ id: "basic", name: "Basic Template" }];
  } else {
    return [
      { id: "free", name: "Free Template" },
      { id: "basic", name: "Basic Template" },
      { id: "premium", name: "Premium Template" },
      { id: "business", name: "Business Template" },
    ];
  }
});

// Check if user has premium plan
const isPremiumPlan = computed(() => {
  const selectedPlan = sessionStorage.getItem("selectedPlan");
  return ["premium", "business"].includes(selectedPlan);
});

// Form validation
const isFormValid = computed(() => {
  // For bulk upload, only need file and design
  if (orderType.value === "bulk") {
    if (designMethod.value === "template") {
      return uploadedEmployees.value.length > 0 && selectedTemplate.value;
    } else {
      return (
        uploadedEmployees.value.length > 0 &&
        cardDesign.front &&
        cardDesign.back
      );
    }
  }

  // For single employee, need all basic fields
  const basicFields =
    cardInfo.name &&
    cardInfo.position &&
    cardInfo.contactNumber &&
    cardInfo.email &&
    cardInfo.address &&
    cardInfo.deliveryAddress;

  if (designMethod.value === "template") {
    return basicFields && selectedTemplate.value;
  } else {
    return basicFields && cardDesign.front && cardDesign.back;
  }
});

// Check if user is authenticated
onMounted(async () => {
  if (!authStore.isAuthenticated) {
    router.push("/UserAccount/login");
    return;
  }

  // Load user data if available
  if (authStore.user) {
    cardInfo.name = authStore.user.full_name || "";
    cardInfo.email = authStore.user.email || "";
    cardInfo.position = authStore.user.job_title || "";
  }
});

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
          address: employee.address || "",
          deliveryAddress:
            employee["delivery address"] ||
            employee["delivery_address"] ||
            employee.address ||
            "",
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
    "Name,Email,Position,Contact Number,Website,Address,Delivery Address\n" +
    'John Doe,john@example.com,Sales Manager,+60 12-345 6789,https://example.com,"123 Main St, KL","123 Main St, KL"\n' +
    'Jane Smith,jane@example.com,Marketing Lead,+60 12-345 6790,https://example.com,"456 Oak Ave, PJ","456 Oak Ave, PJ"';

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
        Address: "123 Main St, KL",
        "Delivery Address": "123 Main St, KL",
      },
      {
        Name: "Jane Smith",
        Email: "jane@example.com",
        Position: "Marketing Lead",
        "Contact Number": "+60 12-345 6790",
        Website: "https://example.com",
        Address: "456 Oak Ave, PJ",
        "Delivery Address": "456 Oak Ave, PJ",
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
    address: manualEmployee.address,
    deliveryAddress: manualEmployee.deliveryAddress,
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
  manualEmployee.address = "";
  manualEmployee.deliveryAddress = "";
};

// Save card information
const saveCardInfo = async () => {
  try {
    // Store card info in session storage for backup
    sessionStorage.setItem("businessCardInfo", JSON.stringify(cardInfo));
    sessionStorage.setItem("businessCardDesign", JSON.stringify(cardDesign));
    sessionStorage.setItem("businessSelectedTemplate", selectedTemplate.value);
    sessionStorage.setItem("businessDesignMethod", designMethod.value);

    $toast.success("Card information saved!");
  } catch (error) {
    console.error("Save card info error:", error);
    $toast.error("Failed to save card information. Please try again.");
  }
};

// Handle logo upload
const handleLogoUpload = async (event) => {
  const file = event.target.files[0];
  if (file) {
    try {
      const formData = new FormData();
      formData.append("logo", file);

      const { $api } = useNuxtApp();
      const response = await $api.post("/upload/company-logo", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });

      if (response.success) {
        cardInfo.companyLogo = response.data.url;
        $toast.success("Company logo uploaded successfully!");
      }
    } catch (error) {
      console.error("Logo upload error:", error);
      $toast.error("Failed to upload logo. Please try again.");
    }
  }
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

// Confirm and submit order
const confirmOrder = async () => {
  isPlacingOrder.value = true;

  try {
    const { $api } = useNuxtApp();

    // For bulk upload
    if (orderType.value === "bulk") {
      // Upload designs if custom method
      const uploadSuccess = await uploadCardDesigns();
      if (!uploadSuccess) {
        isPlacingOrder.value = false;
        return;
      }

      // Upload employee file with design info
      const formData = new FormData();
      formData.append("file", employeeFile.value);
      formData.append("design_method", designMethod.value);
      formData.append("selected_template", selectedTemplate.value);
      if (cardDesign.front) formData.append("front_design", cardDesign.front);
      if (cardDesign.back) formData.append("back_design", cardDesign.back);

      const response = await $api.post("/business/employees/upload", formData, {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      });

      if (response.success) {
        showConfirmModal.value = false;
        $toast.success(
          `Successfully created ${uploadedEmployees.value.length} employee card orders! Super Admin has been notified.`
        );
        await router.push(
          "/UserDashboard/UserManagement/BusinessPlanUser/BusinessCardManagement"
        );
      }
      return;
    }

    // For single employee order
    // Save card information first
    await saveCardInfo();

    // Upload designs if custom method
    const uploadSuccess = await uploadCardDesigns();

    if (!uploadSuccess) {
      isPlacingOrder.value = false;
      return;
    }

    // Place the order
    const orderData = {
      subscription_plan: "business",
      name: cardInfo.name,
      position: cardInfo.position,
      contact_number: cardInfo.contactNumber,
      email: cardInfo.email,
      website: cardInfo.website,
      business_address: cardInfo.address,
      delivery_address: cardInfo.deliveryAddress,
      company_logo: cardInfo.companyLogo,
      company_background: cardInfo.companyBackground,
      services: cardInfo.services,
      linkedin: cardInfo.linkedin,
      twitter: cardInfo.twitter,
      facebook: cardInfo.facebook,
      instagram: cardInfo.instagram,
      design_method: designMethod.value,
      selected_template: selectedTemplate.value,
      front_design: cardDesign.front,
      back_design: cardDesign.back,
    };

    const response = await $api.post("/nfc-cards", orderData);

    if (response.success) {
      showConfirmModal.value = false;
      $toast.success(
        "NFC Card order placed successfully! Super Admin has been notified and will process your order."
      );

      // Redirect to card management page
      await router.push(
        "/UserDashboard/UserManagement/BusinessPlanUser/BusinessCardManagement"
      );
    } else {
      throw new Error(response.message || "Failed to place order");
    }
  } catch (error) {
    console.error("Place order error:", error);
    $toast.error(error.message || "Failed to place order. Please try again.");
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
