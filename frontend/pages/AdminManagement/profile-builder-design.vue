<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-secondary-900">Profile Builder Management</h1>
      <p class="mt-1 text-sm text-secondary-600">
        Manage design options and input fields across different subscription plans
      </p>
    </div>

    <!-- Plan Accordions -->
    <div class="space-y-3 mb-6">
      <div
        v-for="plan in plans"
        :key="plan.id"
        :class="[
          'rounded-lg border-2 transition-all overflow-hidden',
          expandedPlans.includes(plan.id) && plan.id === 'business' && 'border-amber-500',
          expandedPlans.includes(plan.id) && plan.id === 'premium' && 'border-purple-500',
          expandedPlans.includes(plan.id) && plan.id === 'basic' && 'border-blue-500',
          !expandedPlans.includes(plan.id) && 'border-secondary-200'
        ]"
      >
        <button
          @click="togglePlan(plan.id)"
          :class="[
            'w-full flex items-center justify-between p-4 transition-all',
            expandedPlans.includes(plan.id) && plan.id === 'business' && 'bg-amber-50',
            expandedPlans.includes(plan.id) && plan.id === 'premium' && 'bg-purple-50',
            expandedPlans.includes(plan.id) && plan.id === 'basic' && 'bg-blue-50',
            !expandedPlans.includes(plan.id) && 'bg-white hover:bg-secondary-50'
          ]"
        >
          <div class="flex items-center gap-3">
            <span class="text-base font-semibold text-secondary-900">{{ plan.name }}</span>
            <span class="text-xs text-secondary-500">
              {{ getPlanOptionCount(plan.id) }} items selected
            </span>
          </div>
          <Icon 
            :name="expandedPlans.includes(plan.id) ? 'heroicons:chevron-up' : 'heroicons:chevron-down'" 
            class="w-5 h-5 text-secondary-500"
          />
        </button>

        <!-- Expanded Content -->
        <div v-if="expandedPlans.includes(plan.id)" class="border-t border-secondary-200 bg-white">
          <div class="p-6 space-y-6">
            <!-- General Sections -->
            <div>
              <h3 class="text-sm font-semibold text-secondary-900 mb-3 flex items-center">
                <Icon name="heroicons:rectangle-stack" class="w-4 h-4 mr-2" />
                General Sections
              </h3>
              <div class="space-y-4">
                <div v-for="tab in generalTabs" :key="tab.id">
                  <!-- Category Header -->
                  <div class="w-full flex items-center justify-between p-2 hover:bg-secondary-50 rounded-md transition-all">
                    <div class="flex items-center gap-2 flex-1">
                      <!-- Select All Checkbox -->
                      <input
                        type="checkbox"
                        :checked="isSectionFullySelected(plan.id, tab.id)"
                        @change="toggleSectionForPlan(plan.id, tab.id, $event.target.checked)"
                        @click.stop
                        class="w-4 h-4 rounded border-secondary-300 text-primary-600"
                        :title="isSectionFullySelected(plan.id, tab.id) ? 'Unselect all' : 'Select all'"
                      />
                      <button
                        @click="toggleCategory(plan.id, tab.id)"
                        class="flex items-center gap-2 flex-1"
                      >
                        <Icon :name="tab.icon" class="w-4 h-4 text-secondary-600" />
                        <span class="text-sm font-medium text-secondary-900">{{ tab.name }} Fields</span>
                        <span class="text-xs text-secondary-500">({{ getFieldTabItemCount(tab.id) }})</span>
                      </button>
                    </div>
                    <button @click="toggleCategory(plan.id, tab.id)">
                      <Icon 
                        :name="expandedCategories[plan.id]?.includes(tab.id) ? 'heroicons:chevron-up' : 'heroicons:chevron-down'"
                        class="w-4 h-4 text-secondary-500"
                      />
                    </button>
                  </div>
                  
                  <!-- Items List -->
                  <div v-if="expandedCategories[plan.id]?.includes(tab.id)" class="grid grid-cols-2 gap-2 ml-6 mt-2">
                    <label
                      v-for="field in (fields[tab.id.replace('field_', '')] || [])"
                      :key="field.id"
                      class="flex items-center p-2 border border-secondary-200 rounded-md hover:bg-secondary-50 cursor-pointer transition-all text-sm"
                    >
                      <input
                        type="checkbox"
                        :checked="isAvailableForPlan(field, plan.id)"
                        @change="toggleOptionForPlan(field, plan.id, $event.target.checked)"
                        class="w-3.5 h-3.5 rounded border-secondary-300 text-primary-600"
                      />
                      <span class="ml-2 text-secondary-900 truncate">{{ field.label }}</span>
                    </label>
                    <div v-if="(fields[tab.id.replace('field_', '')] || []).length === 0" class="col-span-2 text-xs text-secondary-400 italic p-2">
                      No items yet
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Design Sections -->
            <div>
              <h3 class="text-sm font-semibold text-secondary-900 mb-3 flex items-center">
                <Icon name="heroicons:paint-brush" class="w-4 h-4 mr-2" />
                Design Sections
              </h3>
              <div class="space-y-4">
                <div v-for="designTab in designTabs" :key="designTab.id">
                  <!-- Category Header -->
                  <div class="w-full flex items-center justify-between p-2 hover:bg-secondary-50 rounded-md transition-all">
                    <div class="flex items-center gap-2 flex-1">
                      <!-- Select All Checkbox -->
                      <input
                        type="checkbox"
                        :checked="isDesignSectionFullySelected(plan.id, designTab.subItems)"
                        @change="toggleDesignSectionForPlan(plan.id, designTab.subItems, $event.target.checked)"
                        @click.stop
                        class="w-4 h-4 rounded border-secondary-300 text-primary-600"
                        :title="isDesignSectionFullySelected(plan.id, designTab.subItems) ? 'Unselect all' : 'Select all'"
                      />
                      <button
                        @click="toggleCategory(plan.id, designTab.id)"
                        class="flex items-center gap-2 flex-1"
                      >
                        <Icon :name="designTab.icon" class="w-4 h-4 text-secondary-600" />
                        <span class="text-sm font-medium text-secondary-900">{{ designTab.name }}</span>
                        <span class="text-xs text-secondary-500">({{ getDesignCategoryItemCount(designTab.subItems) }})</span>
                      </button>
                    </div>
                    <button @click="toggleCategory(plan.id, designTab.id)">
                      <Icon 
                        :name="expandedCategories[plan.id]?.includes(designTab.id) ? 'heroicons:chevron-up' : 'heroicons:chevron-down'"
                        class="w-4 h-4 text-secondary-500"
                      />
                    </button>
                  </div>
                  
                  <!-- Sub Items -->
                  <div v-if="expandedCategories[plan.id]?.includes(designTab.id)" class="ml-6 mt-2 space-y-2">
                    <div v-for="subItem in designTab.subItems" :key="subItem" class="space-y-2">
                      <div class="text-xs font-medium text-secondary-700 px-2 py-1">{{ getTabName(subItem) }}</div>
                      <div class="grid grid-cols-2 gap-2">
                        <label
                          v-for="option in (options[subItem] || [])"
                          :key="option.id"
                          class="flex items-center p-2 border border-secondary-200 rounded-md hover:bg-secondary-50 cursor-pointer transition-all text-sm"
                        >
                          <input
                            type="checkbox"
                            :checked="isAvailableForPlan(option, plan.id)"
                            @change="toggleOptionForPlan(option, plan.id, $event.target.checked)"
                            class="w-3.5 h-3.5 rounded border-secondary-300 text-primary-600"
                          />
                          <span class="ml-2 text-secondary-900 truncate">{{ option.name }}</span>
                        </label>
                        <div v-if="(options[subItem] || []).length === 0" class="col-span-2 text-xs text-secondary-400 italic p-2">
                          No items yet
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Apply Designs Header -->
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-secondary-900">Apply Designs & Functions to Plans</h2>
      <p class="text-sm text-secondary-600 mt-1">Select which designs and functions to apply to each plan</p>
    </div>

    <!-- Main Content Card -->
    <div class="card">
      <!-- Tabs Navigation -->
      <div class="border-b border-secondary-200">
        <nav class="-mb-px flex overflow-x-auto px-6" aria-label="Tabs">
          <button
            v-for="tab in allTabs"
            :key="tab.id"
            @click="currentTab = tab.id"
            :class="[
              currentTab === tab.id
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-secondary-500 hover:text-secondary-700 hover:border-secondary-300',
              'group inline-flex items-center py-4 px-3 border-b-2 font-medium text-sm whitespace-nowrap'
            ]"
          >
            <Icon :name="tab.icon" class="w-4 h-4 mr-1.5" />
            {{ tab.name }}
            <span 
              :class="[
                currentTab === tab.id ? 'bg-primary-100 text-primary-600' : 'bg-secondary-100 text-secondary-600',
                'ml-2 py-0.5 px-2 rounded-full text-xs font-medium'
              ]"
            >
              {{ getFilteredOptionCount(tab.id) }}
            </span>
          </button>
        </nav>
      </div>

      <div class="card-body">
        <!-- Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 pb-4 border-b border-secondary-200">
          <div class="flex items-center gap-3">
            <span class="text-sm font-medium text-secondary-900">
              {{ getFilteredOptions.length }} {{ currentTabName }}{{ getFilteredOptions.length !== 1 ? 's' : '' }}
            </span>
            <span v-if="selectedPlanFilter" class="text-xs text-secondary-500">
              • Showing {{ selectedPlanFilter.toUpperCase() }} plan options
            </span>
          </div>
          <button
            @click="openAddModal"
            class="btn btn-primary"
          >
            <Icon name="heroicons:plus" class="h-4 w-4 mr-1.5" />
            Add {{ currentTabName }}
          </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-16">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary-600 border-t-transparent"></div>
          <p class="mt-3 text-sm text-secondary-600">Loading options...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="getFilteredOptions.length === 0" class="text-center py-16">
          <Icon name="heroicons:inbox" class="w-16 h-16 text-secondary-300 mx-auto mb-4" />
          <p class="text-base font-medium text-secondary-900 mb-1">
            {{ selectedPlanFilter ? `No options for ${selectedPlanFilter.toUpperCase()} plan` : 'No options yet' }}
          </p>
          <p class="text-sm text-secondary-500 mb-6">
            {{ selectedPlanFilter ? 'Try selecting a different plan or add new options' : `Get started by creating your first ${currentTabName.toLowerCase()}` }}
          </p>
          <button
            @click="openAddModal"
            class="btn btn-primary"
          >
            <Icon name="heroicons:plus" class="h-4 w-4 mr-1.5" />
            Add {{ currentTabName }}
          </button>
        </div>

        <!-- Fields Table View -->
        <div v-else-if="isFieldTab" class="overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-secondary-200">
              <thead>
                <tr class="bg-secondary-50">
                  <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-secondary-700 uppercase tracking-wider">
                    Field
                  </th>
                  <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-secondary-700 uppercase tracking-wider">
                    Type
                  </th>
                  <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-secondary-700 uppercase tracking-wider">
                    Order
                  </th>
                  <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-secondary-700 uppercase tracking-wider w-[280px]">
                    Plan Availability
                  </th>
                  <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-secondary-700 uppercase tracking-wider">
                    Status
                  </th>
                  <th scope="col" class="px-4 py-3.5 text-center text-xs font-semibold text-secondary-700 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-secondary-100">
                <tr v-for="field in getFilteredOptions" :key="field.id" class="hover:bg-secondary-50 transition-colors">
                  <td class="px-4 py-4">
                    <div class="text-sm font-semibold text-secondary-900">{{ field.label }}</div>
                    <div class="text-xs text-secondary-500 font-mono mt-0.5">{{ field.field_key }}</div>
                    <div v-if="field.placeholder" class="text-xs text-secondary-400 italic mt-1">
                      "{{ field.placeholder }}"
                    </div>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700">
                      {{ field.field_type }}
                    </span>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap text-center">
                    <input
                      v-model.number="field.display_order"
                      type="number"
                      class="w-16 px-2 py-1.5 text-sm text-center border border-secondary-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                      @change="updateField(field)"
                    />
                  </td>
                  <td class="px-4 py-4">
                    <div class="flex items-center justify-center gap-2">
                      <div 
                        :class="[
                          'px-3 py-1.5 rounded-md text-xs font-semibold border-2',
                          isAvailableForPlan(field, 'basic')
                            ? 'bg-blue-50 text-blue-700 border-blue-300'
                            : 'bg-secondary-50 text-secondary-400 border-secondary-200'
                        ]"
                      >
                        Basic
                      </div>
                      <div 
                        :class="[
                          'px-3 py-1.5 rounded-md text-xs font-semibold border-2',
                          isAvailableForPlan(field, 'premium')
                            ? 'bg-purple-50 text-purple-700 border-purple-300'
                            : 'bg-secondary-50 text-secondary-400 border-secondary-200'
                        ]"
                      >
                        Premium
                      </div>
                      <div 
                        :class="[
                          'px-3 py-1.5 rounded-md text-xs font-semibold border-2',
                          isAvailableForPlan(field, 'business')
                            ? 'bg-amber-50 text-amber-700 border-amber-300'
                            : 'bg-secondary-50 text-secondary-400 border-secondary-200'
                        ]"
                      >
                        Business
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap text-center">
                    <div class="flex items-center justify-center gap-2">
                      <label class="flex items-center cursor-pointer" title="Required">
                        <input
                          type="checkbox"
                          v-model="field.is_required"
                          class="w-4 h-4 rounded border-secondary-300 text-error-600 focus:ring-error-500"
                          @change="updateField(field)"
                        />
                        <span class="ml-1 text-xs text-secondary-600">Req</span>
                      </label>
                      <label class="flex items-center cursor-pointer" title="Visible">
                        <input
                          type="checkbox"
                          v-model="field.is_visible"
                          class="w-4 h-4 rounded border-secondary-300 text-success-600 focus:ring-success-500"
                          @change="updateField(field)"
                        />
                        <span class="ml-1 text-xs text-secondary-600">Vis</span>
                      </label>
                    </div>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap">
                    <div class="flex items-center justify-center gap-2">
                      <button
                        @click="openEditModal(field)"
                        class="p-1.5 text-primary-600 hover:text-primary-700 hover:bg-primary-50 rounded transition-colors"
                        title="Edit"
                      >
                        <Icon name="heroicons:pencil" class="w-4 h-4" />
                      </button>
                      <button
                        @click="confirmDelete(field)"
                        class="p-1.5 text-error-600 hover:text-error-700 hover:bg-error-50 rounded transition-colors"
                        title="Delete"
                      >
                        <Icon name="heroicons:trash" class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Design Options Grid View -->
        <div v-else class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
          <div
            v-for="option in getFilteredOptions"
            :key="option.id"
            :class="[
              'group relative bg-white border-2 rounded-xl overflow-hidden transition-all duration-200',
              option.is_active 
                ? 'border-secondary-200 hover:border-primary-300 hover:shadow-lg' 
                : 'border-secondary-200 opacity-60'
            ]"
          >
            <!-- Plan Availability Header -->
            <div class="bg-secondary-50 px-4 py-2 border-b border-secondary-200">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                  <div 
                    :class="[
                      'w-2 h-2 rounded-full',
                      isAvailableForPlan(option, 'basic') ? 'bg-blue-500' : 'bg-secondary-300'
                    ]"
                    title="Basic"
                  ></div>
                  <div 
                    :class="[
                      'w-2 h-2 rounded-full',
                      isAvailableForPlan(option, 'premium') ? 'bg-purple-500' : 'bg-secondary-300'
                    ]"
                    title="Premium"
                  ></div>
                  <div 
                    :class="[
                      'w-2 h-2 rounded-full',
                      isAvailableForPlan(option, 'business') ? 'bg-amber-500' : 'bg-secondary-300'
                    ]"
                    title="Business"
                  ></div>
                  <span class="text-xs text-secondary-500 ml-1">
                    {{ getPlanNames(option).join(' + ') || 'All Plans' }}
                  </span>
                </div>
                <div class="flex items-center gap-1">
                  <span
                    v-if="option.is_default"
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-primary-100 text-primary-700"
                  >
                    <Icon name="heroicons:star-solid" class="w-3 h-3 mr-1" />
                    Default
                  </span>
                  <span
                    :class="[
                      'inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold',
                      option.is_active 
                        ? 'bg-success-100 text-success-700' 
                        : 'bg-secondary-200 text-secondary-600'
                    ]"
                  >
                    {{ option.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Content -->
            <div class="p-4">
              <!-- Preview Section -->
              <div class="mb-4 p-3 bg-secondary-50 rounded-lg">
                <!-- Theme Preview -->
                <div v-if="currentTab === 'theme'" :class="['w-full h-20 rounded-lg shadow-sm', option.config?.preview]"></div>
                
                <!-- Font Preview -->
                <div v-else-if="currentTab === 'font'" class="text-center">
                  <p :style="{ fontFamily: option.config?.family }" class="text-lg font-medium text-secondary-900">
                    The quick brown fox
                  </p>
                </div>
                
                <!-- Button Style Preview -->
                <button
                  v-else-if="currentTab === 'button_style'"
                  :class="['w-full py-2 px-4 text-sm font-medium', option.config?.class]"
                  disabled
                >
                  {{ option.name }}
                </button>
                
                <!-- Profile Style Preview -->
                <div v-else-if="currentTab === 'profile_style'" class="flex justify-center">
                  <div class="w-16 h-16 bg-gradient-to-br from-secondary-300 to-secondary-400 rounded-full"></div>
                </div>
                
                <!-- Color Scheme Preview -->
                <div v-else-if="currentTab === 'color_scheme'">
                  <div class="flex gap-2 mb-2">
                    <div 
                      :style="{ backgroundColor: option.config?.primary || '#000000' }" 
                      class="flex-1 h-10 rounded-md shadow-sm"
                    ></div>
                    <div 
                      :style="{ backgroundColor: option.config?.secondary || '#666666' }" 
                      class="flex-1 h-10 rounded-md shadow-sm"
                    ></div>
                    <div 
                      :style="{ backgroundColor: option.config?.accent || '#0066FF' }" 
                      class="flex-1 h-10 rounded-md shadow-sm"
                    ></div>
                  </div>
                  <div class="flex gap-2 text-xs text-center text-secondary-600">
                    <span class="flex-1">Primary</span>
                    <span class="flex-1">Secondary</span>
                    <span class="flex-1">Accent</span>
                  </div>
                </div>
                
                <!-- Layout Preview -->
                <div v-else-if="currentTab === 'layout'">
                  <div class="h-12 bg-white rounded border-2 border-dashed border-secondary-300 flex items-center justify-center">
                    <div class="text-xs text-secondary-600">
                      {{ option.config?.alignment }} • {{ option.config?.maxWidth }}
                    </div>
                  </div>
                </div>
                
                <!-- Tab Control Preview -->
                <div v-else-if="currentTab === 'tab_control'" class="flex flex-wrap gap-1 justify-center">
                  <span 
                    v-for="tab in (option.config?.tabs || [])" 
                    :key="tab"
                    class="px-2 py-0.5 text-xs font-medium bg-white border border-secondary-300 rounded"
                  >
                    {{ tab }}
                  </span>
                </div>
                
                <!-- Feature Toggle Preview -->
                <div v-else-if="currentTab === 'feature_toggle'" class="flex items-center justify-center">
                  <Icon 
                    :name="option.config?.enabled ? 'heroicons:check-circle-solid' : 'heroicons:x-circle-solid'" 
                    :class="option.config?.enabled ? 'text-success-600' : 'text-error-600'"
                    class="w-8 h-8"
                  />
                </div>
              </div>

              <!-- Info -->
              <div class="mb-3">
                <h3 class="text-sm font-semibold text-secondary-900 mb-1">{{ option.name }}</h3>
                <p v-if="option.description" class="text-xs text-secondary-600 line-clamp-2">
                  {{ option.description }}
                </p>
                <code class="text-xs text-secondary-500 bg-secondary-100 px-1.5 py-0.5 rounded font-mono mt-2 inline-block">
                  {{ option.option_id }}
                </code>
              </div>

              <!-- Actions -->
              <div class="flex items-center gap-2 pt-3 border-t border-secondary-200">
                <button
                  v-if="!option.is_default"
                  @click="setAsDefault(option)"
                  class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-1.5 text-xs font-medium text-secondary-700 bg-white border border-secondary-300 rounded-lg hover:bg-secondary-50"
                  title="Set as default"
                >
                  <Icon name="heroicons:star" class="w-3 h-3" />
                </button>
                <button
                  @click="toggleActive(option)"
                  :class="[
                    'flex-1 inline-flex items-center justify-center gap-1 px-3 py-1.5 text-xs font-medium border rounded-lg',
                    option.is_active
                      ? 'text-error-700 bg-white border-error-300 hover:bg-error-50'
                      : 'text-success-700 bg-white border-success-300 hover:bg-success-50'
                  ]"
                >
                  <Icon :name="option.is_active ? 'heroicons:eye-slash' : 'heroicons:eye'" class="w-3 h-3" />
                </button>
                <button
                  @click="openEditModal(option)"
                  class="p-1.5 text-primary-600 hover:bg-primary-50 border border-secondary-300 rounded-lg"
                  title="Edit"
                >
                  <Icon name="heroicons:pencil" class="w-3 h-3" />
                </button>
                <button
                  @click="confirmDelete(option)"
                  class="p-1.5 text-error-600 hover:bg-error-50 border border-error-300 rounded-lg"
                  title="Delete"
                >
                  <Icon name="heroicons:trash" class="w-3 h-3" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <TransitionRoot as="template" :show="showModal">
      <Dialog as="div" class="relative z-50" @close="closeModal">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-secondary-900/75 backdrop-blur-sm transition-opacity" />
        </TransitionChild>

        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <TransitionChild
              as="template"
              enter="ease-out duration-300"
              enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
              enter-to="opacity-100 translate-y-0 sm:scale-100"
              leave="ease-in duration-200"
              leave-from="opacity-100 translate-y-0 sm:scale-100"
              leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
              <DialogPanel class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                <!-- Modal Header -->
                <div class="bg-secondary-50 px-6 py-4 border-b border-secondary-200">
                  <DialogTitle as="h3" class="text-lg font-semibold text-secondary-900">
                    {{ modalMode === 'add' ? 'Add New' : 'Edit' }} {{ currentTabName }}
                  </DialogTitle>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-6">
                  <div class="space-y-5 max-h-[calc(100vh-280px)] overflow-y-auto pr-2">
                    <!-- FIELDS FORM -->
                    <template v-if="isFieldTab">
                      <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                          <label class="block text-sm font-medium text-secondary-900 mb-2">
                            Field Key <span class="text-error-600">*</span>
                          </label>
                          <input
                            v-model="formData.field_key"
                            type="text"
                            :disabled="modalMode === 'edit'"
                            class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 disabled:bg-secondary-100"
                            placeholder="e.g., name, email, bio"
                          />
                        </div>

                        <div>
                          <label class="block text-sm font-medium text-secondary-900 mb-2">Label <span class="text-error-600">*</span></label>
                          <input
                            v-model="formData.label"
                            type="text"
                            class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                            placeholder="Full Name"
                          />
                        </div>

                        <div>
                          <label class="block text-sm font-medium text-secondary-900 mb-2">Field Type <span class="text-error-600">*</span></label>
                          <select
                            v-model="formData.field_type"
                            class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                          >
                            <option value="text">Text</option>
                            <option value="email">Email</option>
                            <option value="tel">Phone</option>
                            <option value="url">URL</option>
                            <option value="textarea">Textarea</option>
                            <option value="number">Number</option>
                            <option value="image">Image</option>
                            <option value="repeater">Repeater</option>
                          </select>
                        </div>

                        <div>
                          <label class="block text-sm font-medium text-secondary-900 mb-2">Placeholder</label>
                          <input
                            v-model="formData.placeholder"
                            type="text"
                            class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                            placeholder="Enter your name"
                          />
                        </div>

                        <div>
                          <label class="block text-sm font-medium text-secondary-900 mb-2">Help Text</label>
                          <input
                            v-model="formData.help_text"
                            type="text"
                            class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                            placeholder="Optional"
                          />
                        </div>
                      </div>

                      <!-- Repeater Configuration -->
                      <div v-if="formData.field_type === 'repeater'" class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <h4 class="text-sm font-semibold text-secondary-900 mb-3 flex items-center">
                          <Icon name="heroicons:queue-list" class="w-4 h-4 mr-2" />
                          Repeater Configuration
                        </h4>
                        <div class="space-y-3">
                          <div>
                            <label class="block text-xs font-medium text-secondary-700 mb-1.5">Max Items</label>
                            <input
                              v-model.number="formData.config.max_items"
                              type="number"
                              class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg"
                              placeholder="e.g., 3 or 6"
                              min="1"
                            />
                          </div>
                          <div>
                            <label class="block text-xs font-medium text-secondary-700 mb-1.5">Sub-fields (JSON array)</label>
                            <textarea
                              v-model="formData.config.sub_fields"
                              rows="4"
                              class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg font-mono resize-none"
                              placeholder='[{"key": "num", "label": "Number", "type": "text"}, {"key": "label", "label": "Label", "type": "text"}]'
                            ></textarea>
                            <p class="text-xs text-secondary-500 mt-1">Define sub-fields as JSON array</p>
                          </div>
                        </div>
                      </div>

                      <div class="bg-secondary-50 rounded-lg p-4 border border-secondary-200">
                        <h4 class="text-sm font-semibold text-secondary-900 mb-3">Validation</h4>
                        <div class="grid grid-cols-2 gap-4">
                          <div>
                            <label class="block text-xs font-medium text-secondary-700 mb-1.5">Min Length</label>
                            <input
                              v-model.number="formData.validation_rules.min"
                              type="number"
                              class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg"
                              placeholder="0"
                            />
                          </div>
                          <div>
                            <label class="block text-xs font-medium text-secondary-700 mb-1.5">Max Length</label>
                            <input
                              v-model.number="formData.validation_rules.max"
                              type="number"
                              class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg"
                              placeholder="255"
                            />
                          </div>
                        </div>
                      </div>
                    </template>

                    <!-- DESIGN OPTIONS FORM -->
                    <template v-else>
                      <div v-if="modalMode === 'add'">
                        <label class="block text-sm font-medium text-secondary-900 mb-2">
                          Option ID <span class="text-error-600">*</span>
                        </label>
                        <input
                          v-model="formData.option_id"
                          type="text"
                          class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 font-mono"
                          placeholder="e.g., minimal, inter, solid"
                        />
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-secondary-900 mb-2">
                          Display Name <span class="text-error-600">*</span>
                        </label>
                        <input
                          v-model="formData.name"
                          type="text"
                          class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                          placeholder="e.g., Minimal, Inter, Solid"
                        />
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-secondary-900 mb-2">Description</label>
                        <textarea
                          v-model="formData.description"
                          rows="2"
                          class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 resize-none"
                          placeholder="Brief description..."
                        ></textarea>
                      </div>

                      <!-- Type-specific Configuration -->
                      <div class="bg-secondary-50 rounded-lg p-4 border border-secondary-200">
                        <h4 class="text-sm font-semibold text-secondary-900 mb-3">Configuration</h4>
                        
                        <div v-if="currentTab === 'theme'" class="space-y-3">
                          <div>
                            <label class="block text-xs font-medium text-secondary-700 mb-1.5">Background Color</label>
                            <input
                              v-model="formData.config.backgroundColor"
                              type="text"
                              class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg font-mono"
                              placeholder="#FFFFFF"
                            />
                          </div>
                          <div>
                            <label class="block text-xs font-medium text-secondary-700 mb-1.5">Preview Classes</label>
                            <input
                              v-model="formData.config.preview"
                              type="text"
                              class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg font-mono"
                              placeholder="bg-white"
                            />
                          </div>
                        </div>

                        <div v-if="currentTab === 'font'">
                          <label class="block text-xs font-medium text-secondary-700 mb-1.5">Font Family</label>
                          <input
                            v-model="formData.config.family"
                            type="text"
                            class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg"
                            placeholder="Inter, sans-serif"
                          />
                        </div>

                        <div v-if="currentTab === 'button_style'">
                          <label class="block text-xs font-medium text-secondary-700 mb-1.5">CSS Classes</label>
                          <textarea
                            v-model="formData.config.class"
                            rows="2"
                            class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg font-mono resize-none"
                            placeholder="bg-black text-white rounded-full"
                          ></textarea>
                        </div>

                        <div v-if="currentTab === 'color_scheme'" class="space-y-3">
                          <div>
                            <label class="block text-xs font-medium text-secondary-700 mb-1.5">Primary</label>
                            <div class="flex gap-2">
                              <input v-model="formData.config.primary" type="color" class="w-12 h-10 rounded border-2 border-secondary-300" />
                              <input v-model="formData.config.primary" type="text" class="flex-1 px-3 py-2 text-sm border border-secondary-300 rounded-lg font-mono" placeholder="#000000" />
                            </div>
                          </div>
                          <div>
                            <label class="block text-xs font-medium text-secondary-700 mb-1.5">Secondary</label>
                            <div class="flex gap-2">
                              <input v-model="formData.config.secondary" type="color" class="w-12 h-10 rounded border-2 border-secondary-300" />
                              <input v-model="formData.config.secondary" type="text" class="flex-1 px-3 py-2 text-sm border border-secondary-300 rounded-lg font-mono" placeholder="#666666" />
                            </div>
                          </div>
                          <div>
                            <label class="block text-xs font-medium text-secondary-700 mb-1.5">Accent</label>
                            <div class="flex gap-2">
                              <input v-model="formData.config.accent" type="color" class="w-12 h-10 rounded border-2 border-secondary-300" />
                              <input v-model="formData.config.accent" type="text" class="flex-1 px-3 py-2 text-sm border border-secondary-300 rounded-lg font-mono" placeholder="#0066FF" />
                            </div>
                          </div>
                        </div>

                        <div v-if="currentTab === 'layout'" class="space-y-3">
                          <div>
                            <label class="block text-xs font-medium text-secondary-700 mb-1.5">Alignment</label>
                            <select v-model="formData.config.alignment" class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg">
                              <option value="left">Left</option>
                              <option value="center">Center</option>
                              <option value="right">Right</option>
                            </select>
                          </div>
                          <div>
                            <label class="block text-xs font-medium text-secondary-700 mb-1.5">Max Width</label>
                            <input v-model="formData.config.maxWidth" type="text" class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg" placeholder="800px" />
                          </div>
                        </div>

                        <div v-if="currentTab === 'tab_control'">
                          <label class="block text-xs font-medium text-secondary-700 mb-1.5">Enabled Tabs (comma-separated)</label>
                          <input v-model="tabsInput" type="text" class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg" placeholder="profile, company, services" @blur="updateTabsArray" />
                        </div>

                        <div v-if="currentTab === 'feature_toggle'" class="space-y-3">
                          <div>
                            <label class="block text-xs font-medium text-secondary-700 mb-1.5">Feature Key</label>
                            <input v-model="formData.config.feature_key" type="text" class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg font-mono" placeholder="stats" />
                          </div>
                          <label class="flex items-center p-2 bg-white rounded border border-secondary-300 cursor-pointer">
                            <input v-model="formData.config.enabled" type="checkbox" class="w-4 h-4 rounded border-secondary-300 text-primary-600" />
                            <span class="ml-2 text-sm text-secondary-900">Enabled by Default</span>
                          </label>
                        </div>
                      </div>
                    </template>

                    <!-- Plan Availability - Redesigned -->
                    <div class="bg-gradient-to-br from-secondary-50 to-secondary-100 rounded-lg p-5 border-2 border-secondary-200">
                      <h4 class="text-sm font-semibold text-secondary-900 mb-4 flex items-center">
                        <Icon name="heroicons:shield-check" class="w-4 h-4 mr-2" />
                        Plan Availability
                      </h4>
                      <div class="space-y-3">
                        <label class="flex items-center p-3 bg-white rounded-lg border-2 cursor-pointer transition-all hover:border-blue-400"
                          :class="formData.available_plans.includes('basic') ? 'border-blue-500 shadow-sm' : 'border-secondary-300'">
                          <input
                            type="checkbox"
                            value="basic"
                            v-model="formData.available_plans"
                            class="w-5 h-5 rounded border-secondary-300 text-blue-600 focus:ring-blue-500"
                          />
                          <div class="ml-3 flex-1">
                            <div class="flex items-center justify-between">
                              <span class="text-sm font-semibold text-secondary-900">Basic Plan</span>
                              <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                            </div>
                            <p class="text-xs text-secondary-600 mt-0.5">Essential features for individuals</p>
                          </div>
                        </label>

                        <label class="flex items-center p-3 bg-white rounded-lg border-2 cursor-pointer transition-all hover:border-purple-400"
                          :class="formData.available_plans.includes('premium') ? 'border-purple-500 shadow-sm' : 'border-secondary-300'">
                          <input
                            type="checkbox"
                            value="premium"
                            v-model="formData.available_plans"
                            class="w-5 h-5 rounded border-secondary-300 text-purple-600 focus:ring-purple-500"
                          />
                          <div class="ml-3 flex-1">
                            <div class="flex items-center justify-between">
                              <span class="text-sm font-semibold text-secondary-900">Premium Plan</span>
                              <div class="w-3 h-3 rounded-full bg-purple-500"></div>
                            </div>
                            <p class="text-xs text-secondary-600 mt-0.5">Advanced features for professionals</p>
                          </div>
                        </label>

                        <label class="flex items-center p-3 bg-white rounded-lg border-2 cursor-pointer transition-all hover:border-amber-400"
                          :class="formData.available_plans.includes('business') ? 'border-amber-500 shadow-sm' : 'border-secondary-300'">
                          <input
                            type="checkbox"
                            value="business"
                            v-model="formData.available_plans"
                            class="w-5 h-5 rounded border-secondary-300 text-amber-600 focus:ring-amber-500"
                          />
                          <div class="ml-3 flex-1">
                            <div class="flex items-center justify-between">
                              <span class="text-sm font-semibold text-secondary-900">Business Plan</span>
                              <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                            </div>
                            <p class="text-xs text-secondary-600 mt-0.5">Full access for teams & enterprises</p>
                          </div>
                        </label>
                      </div>
                      <p class="mt-3 text-xs text-secondary-500 bg-white px-3 py-2 rounded border border-secondary-200">
                        <Icon name="heroicons:information-circle" class="w-3 h-3 inline mr-1" />
                        Leave all unchecked to make available for all plans
                      </p>
                    </div>

                    <!-- Additional Settings -->
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <label class="block text-sm font-medium text-secondary-900 mb-2">Display Order</label>
                        <input
                          v-model.number="formData.display_order"
                          type="number"
                          class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                          min="0"
                          placeholder="0"
                        />
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-secondary-900 mb-2">Status</label>
                        <div class="space-y-2 mt-2">
                          <template v-if="!isFieldTab">
                            <label class="flex items-center">
                              <input v-model="formData.is_active" type="checkbox" class="w-4 h-4 rounded border-secondary-300 text-success-600" />
                              <span class="ml-2 text-sm text-secondary-700">Active</span>
                            </label>
                            <label class="flex items-center">
                              <input v-model="formData.is_default" type="checkbox" class="w-4 h-4 rounded border-secondary-300 text-primary-600" />
                              <span class="ml-2 text-sm text-secondary-700">Default</span>
                            </label>
                          </template>
                          <template v-else>
                            <label class="flex items-center">
                              <input v-model="formData.is_required" type="checkbox" class="w-4 h-4 rounded border-secondary-300 text-error-600" />
                              <span class="ml-2 text-sm text-secondary-700">Required</span>
                            </label>
                            <label class="flex items-center">
                              <input v-model="formData.is_visible" type="checkbox" class="w-4 h-4 rounded border-secondary-300 text-success-600" />
                              <span class="ml-2 text-sm text-secondary-700">Visible</span>
                            </label>
                          </template>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-secondary-50 px-6 py-4 border-t border-secondary-200 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                  <button
                    type="button"
                    @click="closeModal"
                    class="px-4 py-2 text-sm font-medium text-secondary-700 bg-white border border-secondary-300 rounded-lg hover:bg-secondary-50"
                  >
                    Cancel
                  </button>
                  <button
                    type="button"
                    @click="saveOption"
                    :disabled="saving"
                    class="px-4 py-2 text-sm font-semibold text-white bg-primary-600 rounded-lg hover:bg-primary-700 disabled:opacity-50"
                  >
                    <Icon v-if="saving" name="heroicons:arrow-path" class="w-4 h-4 mr-1.5 animate-spin inline" />
                    {{ saving ? 'Saving...' : (modalMode === 'add' ? 'Add' : 'Update') }}
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { useAuthStore } from '~/stores/auth';

const authStore = useAuthStore();
const config = useRuntimeConfig();

// State
const currentTab = ref('theme');
const selectedPlanFilter = ref(null);
const loading = ref(false);
const saving = ref(false);
const isFetching = ref(false);
const options = ref({});
const fields = ref({});
const showModal = ref(false);
const modalMode = ref('add');
const editingOption = ref(null);
const expandedPlans = ref([]);

// Plans
const plans = [
  { id: 'business', name: 'Business', color: 'amber' },
  { id: 'premium', name: 'Premium', color: 'purple' },
  { id: 'basic', name: 'Basic', color: 'blue' },
];

// General Sections tabs (aligned with user frontend)
const generalTabs = [
  { id: 'field_profile', name: 'Profile', icon: 'heroicons:user', category: 'general', requiredPermission: 'profile' },
  { id: 'field_company', name: 'Company', icon: 'heroicons:building-office', category: 'general', requiredPermission: 'company' },
  { id: 'field_services', name: 'Services', icon: 'heroicons:rocket-launch', category: 'general', requiredPermission: 'services' },
  { id: 'field_links', name: 'Links', icon: 'heroicons:link', category: 'general', requiredPermission: 'links' },
];

// Design Sections tabs (aligned with user frontend)
const designTabs = [
  { id: 'design', name: 'Design', icon: 'heroicons:paint-brush', category: 'design', requiredPermission: 'design', subItems: ['profile_style'] },
  { id: 'style', name: 'Style', icon: 'heroicons:sparkles', category: 'design', requiredPermission: 'style', subItems: ['theme', 'font', 'button_style', 'color_scheme', 'layout'] },
  { id: 'watermarks', name: 'Watermarks', icon: 'heroicons:eye', category: 'design', requiredPermission: 'watermarks', subItems: ['feature_toggle'] },
];

// All tabs (for backward compatibility with legacy code)
const tabs = [
  { id: 'theme', name: 'Themes', icon: 'heroicons:paint-brush' },
  { id: 'font', name: 'Fonts', icon: 'heroicons:language' },
  { id: 'button_style', name: 'Button Styles', icon: 'heroicons:cursor-arrow-rays' },
  { id: 'profile_style', name: 'Profile Styles', icon: 'heroicons:user-circle' },
  { id: 'color_scheme', name: 'Color Schemes', icon: 'heroicons:swatch' },
  { id: 'layout', name: 'Layouts', icon: 'heroicons:squares-2x2' },
  { id: 'feature_toggle', name: 'Features', icon: 'heroicons:sparkles' },
];

// Field tabs (for backward compatibility with legacy code)
const fieldTabs = [
  { id: 'field_profile', name: 'Profile Fields', icon: 'heroicons:identification' },
  { id: 'field_company', name: 'Company Fields', icon: 'heroicons:building-office-2' },
  { id: 'field_services', name: 'Service Fields', icon: 'heroicons:sparkles' },
  { id: 'field_links', name: 'Link Fields', icon: 'heroicons:link' },
];

// Initialize expanded categories with all categories expanded by default
const expandedCategories = ref({
  business: [
    ...generalTabs.map(t => t.id), 
    ...designTabs.map(t => t.id),
    ...tabs.map(t => t.id), 
    ...fieldTabs.map(t => t.id)
  ],
  premium: [
    ...generalTabs.map(t => t.id), 
    ...designTabs.map(t => t.id),
    ...tabs.map(t => t.id), 
    ...fieldTabs.map(t => t.id)
  ],
  basic: [
    ...generalTabs.map(t => t.id), 
    ...designTabs.map(t => t.id),
    ...tabs.map(t => t.id), 
    ...fieldTabs.map(t => t.id)
  ],
});

// Default form data function
const defaultFormData = () => {
  const isFieldTab = currentTab.value.startsWith('field_');
  
  if (isFieldTab) {
    return {
      tab: currentTab.value.replace('field_', ''),
      field_key: '',
      field_type: 'text',
      label: '',
      placeholder: '',
      help_text: '',
      is_required: false,
      is_visible: true,
      validation_rules: {},
      available_plans: [],
      display_order: 0,
      config: {},
    };
  } else {
    return {
      type: currentTab.value,
      option_id: '',
      name: '',
      description: '',
      config: {},
      is_active: true,
      is_default: false,
      available_plans: [],
      display_order: 0,
    };
  }
};

const formData = ref(defaultFormData());
const tabsInput = ref('');

// Computed
const allTabs = computed(() => {
  return [...tabs, ...fieldTabs];
});

const isFieldTab = computed(() => currentTab.value.startsWith('field_'));
const currentTabName = computed(() => {
  const tab = allTabs.value.find(t => t.id === currentTab.value);
  return tab ? tab.name.slice(0, -1) : 'Option';
});

const currentOptions = computed(() => {
  if (isFieldTab.value) {
    const fieldTab = currentTab.value.replace('field_', '');
    return (fields.value[fieldTab] || []).sort((a, b) => a.display_order - b.display_order);
  }
  return options.value[currentTab.value] || [];
});

const getFilteredOptions = computed(() => {
  if (!selectedPlanFilter.value) {
    return currentOptions.value;
  }
  
  return currentOptions.value.filter(option => {
    return isAvailableForPlan(option, selectedPlanFilter.value);
  });
});

const getFilteredOptionCount = (type) => {
  let opts;
  
  if (type.startsWith('field_')) {
    const fieldTab = type.replace('field_', '');
    opts = (fields.value[fieldTab] || []).sort((a, b) => a.display_order - b.display_order);
  } else {
    opts = options.value[type] || [];
  }
  
  if (!selectedPlanFilter.value) {
    return opts.length;
  }
  
  return opts.filter(option => isAvailableForPlan(option, selectedPlanFilter.value)).length;
};

// Helper Functions
const isAvailableForPlan = (option, plan) => {
  if (!option.available_plans || option.available_plans.length === 0) {
    return true; // Available for all plans
  }
  return option.available_plans.includes(plan);
};

const getPlanNames = (option) => {
  if (!option.available_plans || option.available_plans.length === 0) {
    return [];
  }
  return option.available_plans.map(p => {
    if (p === 'basic') return 'Basic';
    if (p === 'premium') return 'Premium';
    if (p === 'business') return 'Business';
    return p;
  });
};

// Methods
const fetchOptions = async () => {
  if (!authStore.token) {
    console.log('⚠️ No auth token, skipping fetchOptions');
    return;
  }
  
  try {
    const response = await fetch(`${config.public.apiBaseUrl}/admin/profile-design-options`, {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
    });

    if (!response.ok) throw new Error('Failed to fetch options');

    const data = await response.json();
    options.value = data.data;
  } catch (error) {
    console.error('❌ Error fetching options:', error);
  }
};

const fetchFields = async () => {
  if (!authStore.token) {
    console.log('⚠️ No auth token, skipping fetchFields');
    return;
  }
  
  try {
    const response = await fetch(`${config.public.apiBaseUrl}/admin/profile-builder-fields`, {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
    });

    if (!response.ok) throw new Error('Failed to fetch fields');

    const data = await response.json();
    fields.value = data.data;
  } catch (error) {
    console.error('❌ Error fetching fields:', error);
  }
};

const fetchData = async () => {
  if (!authStore.token) {
    console.log('⚠️ No auth token, skipping fetchData');
    return;
  }
  
  if (isFetching.value) {
    console.log('⚠️ Already fetching data, skipping...');
    return;
  }
  
  isFetching.value = true;
  try {
    await Promise.all([fetchOptions(), fetchFields()]);
  } finally {
    isFetching.value = false;
  }
};

const togglePlan = (planId) => {
  const index = expandedPlans.value.indexOf(planId);
  if (index > -1) {
    expandedPlans.value.splice(index, 1);
  } else {
    expandedPlans.value.push(planId);
  }
};

// Toggle category expansion within a plan
const toggleCategory = (planId, categoryId) => {
  if (!expandedCategories.value[planId]) {
    expandedCategories.value[planId] = [];
  }
  
  const index = expandedCategories.value[planId].indexOf(categoryId);
  if (index > -1) {
    expandedCategories.value[planId].splice(index, 1);
  } else {
    expandedCategories.value[planId].push(categoryId);
  }
};

// Get total items selected for a plan
const getPlanOptionCount = (planId) => {
  let count = 0;
  
  // Count design options
  tabs.forEach(tab => {
    const tabOptions = options.value[tab.id] || [];
    count += tabOptions.filter(opt => isAvailableForPlan(opt, planId)).length;
  });
  
  // Count fields
  fieldTabs.forEach(tab => {
    const fieldTab = tab.id.replace('field_', '');
    const tabFields = fields.value[fieldTab] || [];
    count += tabFields.filter(f => isAvailableForPlan(f, planId)).length;
  });
  
  return count;
};

// Toggle single option/field for a plan
const toggleOptionForPlan = async (item, planId, checked, skipRefresh = false) => {
  try {
    const plans = item.available_plans || [];
    
    if (checked && !plans.includes(planId)) {
      plans.push(planId);
    } else if (!checked && plans.includes(planId)) {
      plans.splice(plans.indexOf(planId), 1);
    }
    
    item.available_plans = plans;
    
    // Determine if it's a field or design option
    if (item.field_key) {
      // It's a field
      await updateField(item);
    } else {
      // It's a design option
      await fetch(`${config.public.apiBaseUrl}/admin/profile-design-options/${item.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authStore.token}`,
        },
        body: JSON.stringify(item),
      });
    }
    
    // Only refresh if not skipping (for batch operations)
    if (!skipRefresh) {
      await fetchData();
    }
  } catch (error) {
    console.error('Error toggling option for plan:', error);
    alert('Failed to update');
  }
};

// Get item count for a field tab
const getFieldTabItemCount = (tabId) => {
  const fieldTab = tabId.replace('field_', '');
  const tabFields = fields.value[fieldTab] || [];
  return tabFields.length;
};

// Get item count for a design category (Design, Style, Watermarks)
const getDesignCategoryItemCount = (subItems) => {
  if (!subItems || !Array.isArray(subItems)) return 0;
  let count = 0;
  subItems.forEach(subItem => {
    const tabOptions = options.value[subItem] || [];
    count += tabOptions.length;
  });
  return count;
};

// Get tab name from tab ID
const getTabName = (tabId) => {
  const tab = tabs.find(t => t.id === tabId);
  return tab ? tab.name : tabId;
};

// Check if all items in a section are selected for a plan
const isSectionFullySelected = (planId, tabId) => {
  const fieldTab = tabId.replace('field_', '');
  const tabFields = fields.value[fieldTab] || [];
  
  if (tabFields.length === 0) return false;
  
  // Check if ALL fields are available for this plan
  const allAvailable = tabFields.every(field => isAvailableForPlan(field, planId));
  
  return allAvailable;
};

// Check if all items in a design section are selected for a plan
const isDesignSectionFullySelected = (planId, subItems) => {
  if (!subItems || subItems.length === 0) return false;
  
  let allSelected = true;
  subItems.forEach(subItem => {
    const itemOptions = options.value[subItem] || [];
    if (itemOptions.length === 0) {
      return;
    }
    if (!itemOptions.every(opt => isAvailableForPlan(opt, planId))) {
      allSelected = false;
    }
  });
  
  return allSelected;
};

// Toggle all items in a section for a plan
const toggleSectionForPlan = async (planId, tabId, checked) => {
  const fieldTab = tabId.replace('field_', '');
  const tabFields = fields.value[fieldTab] || [];
  
  if (tabFields.length === 0) return;
  
  try {
    console.log(`🔄 Toggling ${tabFields.length} fields for ${planId}...`);
    
    // Update all fields in parallel (fast!)
    const updatePromises = tabFields.map(async (field) => {
      // Update local data
      const plans = field.available_plans || [];
      if (checked && !plans.includes(planId)) {
        plans.push(planId);
      } else if (!checked && plans.includes(planId)) {
        plans.splice(plans.indexOf(planId), 1);
      }
      field.available_plans = plans;
      
      // Send API request
      const response = await fetch(`${config.public.apiBaseUrl}/admin/profile-builder-fields/${field.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authStore.token}`,
        },
        body: JSON.stringify(field),
      });
      
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(`Failed to update ${field.label}: ${errorData.message || response.statusText}`);
      }
      
      return response;
    });
    
    // Wait for all requests to complete in parallel
    const results = await Promise.allSettled(updatePromises);
    
    // Check if any requests failed
    const failures = results.filter(r => r.status === 'rejected');
    if (failures.length > 0) {
      console.error('❌ Some updates failed:', failures);
      throw new Error(`${failures.length} out of ${tabFields.length} fields failed to update`);
    }
    
    // Refresh data
    await fetchData();
    console.log(`✅ Successfully toggled all ${tabFields.length} fields`);
  } catch (error) {
    console.error('❌ Error in toggleSectionForPlan:', error);
    alert(`Failed to update section: ${error.message || 'Unknown error'}. Please try again or refresh the page.`);
  }
};

// Toggle all items in a design section for a plan
const toggleDesignSectionForPlan = async (planId, subItems, checked) => {
  if (!subItems || subItems.length === 0) return;
  
  try {
    // Collect all options that need to be updated
    const allOptions = [];
    subItems.forEach(subItem => {
      const itemOptions = options.value[subItem] || [];
      allOptions.push(...itemOptions);
    });
    
    console.log(`🔄 Toggling ${allOptions.length} design options for ${planId}...`);
    
    // Update all options in parallel (fast!)
    const updatePromises = allOptions.map(async (option) => {
      // Update local data
      const plans = option.available_plans || [];
      if (checked && !plans.includes(planId)) {
        plans.push(planId);
      } else if (!checked && plans.includes(planId)) {
        plans.splice(plans.indexOf(planId), 1);
      }
      option.available_plans = plans;
      
      // Send API request
      const response = await fetch(`${config.public.apiBaseUrl}/admin/profile-design-options/${option.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authStore.token}`,
        },
        body: JSON.stringify(option),
      });
      
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(`Failed to update ${option.name}: ${errorData.message || response.statusText}`);
      }
      
      return response;
    });
    
    // Wait for all requests to complete in parallel
    const results = await Promise.allSettled(updatePromises);
    
    // Check for any failed requests
    const failures = results.filter(r => r.status === 'rejected');
    if (failures.length > 0) {
      console.error('❌ Some updates failed:', failures);
      throw new Error(`${failures.length} out of ${allOptions.length} options failed to update`);
    }
    
    // Refresh data
    await fetchData();
    console.log(`✅ Successfully toggled all ${allOptions.length} design options`);
  } catch (error) {
    console.error('❌ Error in toggleDesignSectionForPlan:', error);
    alert(`Failed to update design section: ${error.message || 'Unknown error'}. Please try again or refresh the page.`);
  }
};

const openAddModal = () => {
  modalMode.value = 'add';
  formData.value = defaultFormData();
  if (!isFieldTab.value) {
    formData.value.type = currentTab.value;
  }
  showModal.value = true;
};

const openEditModal = (option) => {
  modalMode.value = 'edit';
  editingOption.value = option;
  formData.value = {
    ...option,
    config: { ...option.config },
    available_plans: option.available_plans || [],
  };
  
  if (currentTab.value === 'tab_control' && Array.isArray(option.config?.tabs)) {
    tabsInput.value = option.config.tabs.join(', ');
  }
  
  showModal.value = true;
};

const updateTabsArray = () => {
  if (currentTab.value === 'tab_control' && tabsInput.value) {
    formData.value.config.tabs = tabsInput.value
      .split(',')
      .map(t => t.trim())
      .filter(t => t.length > 0);
  }
};

const closeModal = () => {
  showModal.value = false;
  formData.value = defaultFormData();
  editingOption.value = null;
  tabsInput.value = '';
};

const saveOption = async () => {
  if (isFieldTab.value) {
    if (!formData.value.field_key || !formData.value.label) {
      alert('Please fill in all required fields');
      return;
    }
  } else {
    if (!formData.value.name) {
      alert('Please enter a name');
      return;
    }

    if (modalMode.value === 'add' && !formData.value.option_id) {
      alert('Please enter an option ID');
      return;
    }

    if (currentTab.value === 'tab_control') {
      updateTabsArray();
    }
  }

  saving.value = true;
  try {
    let url, method;
    
    if (isFieldTab.value) {
      url = modalMode.value === 'add'
        ? `${config.public.apiBaseUrl}/admin/profile-builder-fields`
        : `${config.public.apiBaseUrl}/admin/profile-builder-fields/${editingOption.value.id}`;
    } else {
      url = modalMode.value === 'add'
        ? `${config.public.apiBaseUrl}/admin/profile-design-options`
        : `${config.public.apiBaseUrl}/admin/profile-design-options/${editingOption.value.id}`;
    }

    method = modalMode.value === 'add' ? 'POST' : 'PUT';

    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${authStore.token}`,
      },
      body: JSON.stringify(formData.value),
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Failed to save');
    }

    await fetchData();
    closeModal();
    alert(`${isFieldTab.value ? 'Field' : 'Option'} ${modalMode.value === 'add' ? 'added' : 'updated'} successfully`);
  } catch (error) {
    console.error('Error saving:', error);
    alert(error.message);
  } finally {
    saving.value = false;
  }
};

const toggleActive = async (option) => {
  try {
    const response = await fetch(
      `${config.public.apiBaseUrl}/admin/profile-design-options/${option.id}/toggle-active`,
      {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${authStore.token}`,
        },
      }
    );

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Failed to toggle status');
    }

    await fetchOptions();
  } catch (error) {
    console.error('Error toggling status:', error);
    alert(error.message);
  }
};

const setAsDefault = async (option) => {
  try {
    const response = await fetch(
      `${config.public.apiBaseUrl}/admin/profile-design-options/${option.id}/set-default`,
      {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${authStore.token}`,
        },
      }
    );

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Failed to set as default');
    }

    await fetchOptions();
    alert('Default option updated successfully');
  } catch (error) {
    console.error('Error setting default:', error);
    alert(error.message);
  }
};

const confirmDelete = async (option) => {
  const displayName = isFieldTab.value ? option.label : option.name;
  
  if (!confirm(`Are you sure you want to delete "${displayName}"?`)) {
    return;
  }

  try {
    const url = isFieldTab.value
      ? `${config.public.apiBaseUrl}/admin/profile-builder-fields/${option.id}`
      : `${config.public.apiBaseUrl}/admin/profile-design-options/${option.id}`;
      
    const response = await fetch(url, {
      method: 'DELETE',
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Failed to delete');
    }

    await fetchData();
    alert(`${isFieldTab.value ? 'Field' : 'Option'} deleted successfully`);
  } catch (error) {
    console.error('Error deleting:', error);
    alert(error.message);
  }
};

const updateField = async (field) => {
  try {
    console.log(`🔄 Updating field: ${field.label}`, {
      id: field.id,
      field_key: field.field_key,
      field_type: field.field_type,
      available_plans: field.available_plans,
      data: field
    });
    console.log(`📤 Request Body (JSON):`, JSON.stringify(field, null, 2));
    
    const response = await fetch(
      `${config.public.apiBaseUrl}/admin/profile-builder-fields/${field.id}`,
      {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authStore.token}`,
        },
        body: JSON.stringify(field),
      }
    );

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      console.error('❌ API Error Response:', {
        status: response.status,
        statusText: response.statusText,
        errorData: errorData,
        errors: errorData.errors,
        field: field.label
      });
      console.error('❌ Errors Detail (JSON):', JSON.stringify(errorData.errors, null, 2));
      
      // Build detailed error message
      let errorMsg = errorData.message || 'Failed to update field';
      if (errorData.errors) {
        const errorDetails = Object.entries(errorData.errors)
          .map(([key, msgs]) => `${key}: ${Array.isArray(msgs) ? msgs.join(', ') : msgs}`)
          .join('; ');
        errorMsg += ` (${errorDetails})`;
      }
      
      throw new Error(errorMsg);
    }

    await fetchFields();
    console.log(`✅ Successfully updated field: ${field.label}`);
  } catch (error) {
    console.error('❌ Error updating field:', field.label, error);
    alert(`Failed to update ${field.label}: ${error.message}`);
    throw error;
  }
};

onMounted(() => {
  console.log('🚀 Component mounted, fetching data...');
  fetchData();
});

definePageMeta({
  middleware: ['auth', 'admin'],
  layout: 'admin-management',
});
</script>