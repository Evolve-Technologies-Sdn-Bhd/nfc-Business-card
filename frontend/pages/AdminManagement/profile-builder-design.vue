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
    <div class="card mb-6">
      <div class="flex items-center justify-between mb-4 pb-4 border-b border-secondary-200">
        <div>
          <h2 class="text-xl font-bold text-secondary-900 flex items-center">
            <Icon name="heroicons:clipboard-document-check" class="w-5 h-5 mr-2 text-primary-600" />
            Plan Field Assignments
          </h2>
          <p class="text-sm text-secondary-500 mt-1">Configure which fields and features are available for each plan</p>
        </div>
        <div class="flex items-center gap-2">
          <span class="text-xs text-secondary-500 bg-secondary-100 px-2 py-1 rounded">
            {{ generalTabs.length }} sections • {{ Object.values(fields).flat().length }} fields • {{ (options.feature_toggle || []).length }} features
          </span>
        </div>
      </div>

      <div class="space-y-3">
        <div
          v-for="plan in plans"
          :key="plan.id"
          :class="[
            'rounded-xl border-2 transition-all overflow-hidden',
            expandedPlans.includes(plan.id) && plan.id === 'business' && 'border-amber-400 shadow-lg shadow-amber-100',
            expandedPlans.includes(plan.id) && plan.id === 'premium' && 'border-purple-400 shadow-lg shadow-purple-100',
            expandedPlans.includes(plan.id) && plan.id === 'basic' && 'border-blue-400 shadow-lg shadow-blue-100',
            !expandedPlans.includes(plan.id) && 'border-secondary-200 hover:border-secondary-300'
          ]"
        >
          <button
            @click="togglePlan(plan.id)"
            :class="[
              'w-full flex items-center justify-between p-4 transition-all',
              expandedPlans.includes(plan.id) && plan.id === 'business' && 'bg-gradient-to-r from-amber-50 to-amber-100',
              expandedPlans.includes(plan.id) && plan.id === 'premium' && 'bg-gradient-to-r from-purple-50 to-purple-100',
              expandedPlans.includes(plan.id) && plan.id === 'basic' && 'bg-gradient-to-r from-blue-50 to-blue-100',
              !expandedPlans.includes(plan.id) && 'bg-white hover:bg-secondary-50'
            ]"
          >
            <div class="flex items-center gap-3">
              <div :class="[
                'w-10 h-10 rounded-lg flex items-center justify-center',
                plan.id === 'business' && 'bg-amber-500 text-white',
                plan.id === 'premium' && 'bg-purple-500 text-white',
                plan.id === 'basic' && 'bg-blue-500 text-white'
              ]">
                <Icon :name="plan.id === 'business' ? 'heroicons:building-office' : plan.id === 'premium' ? 'heroicons:star' : 'heroicons:user'" class="w-5 h-5" />
              </div>
              <div class="text-left">
                <span class="text-base font-bold text-secondary-900">{{ plan.name }}</span>
                <div class="flex items-center gap-2 mt-0.5">
                  <span :class="[
                    'text-xs font-medium px-2 py-0.5 rounded-full',
                    plan.id === 'business' && 'bg-amber-200 text-amber-800',
                    plan.id === 'premium' && 'bg-purple-200 text-purple-800',
                    plan.id === 'basic' && 'bg-blue-200 text-blue-800'
                  ]">
                    {{ getPlanOptionCount(plan.id) }} items
                  </span>
                </div>
              </div>
            </div>
            <Icon 
              :name="expandedPlans.includes(plan.id) ? 'heroicons:chevron-up' : 'heroicons:chevron-down'" 
              class="w-5 h-5 text-secondary-500"
            />
          </button>

          <!-- Expanded Content -->
          <div v-if="expandedPlans.includes(plan.id)" class="border-t border-secondary-200 bg-white">
            <div class="p-6">
              <!-- Business Plan Tabs -->
              <div v-if="plan.id === 'business'" class="mb-4">
                <div class="flex gap-2 border-b border-secondary-200">
                  <button
                    @click="businessViewTab = 'defaults'"
                    :class="[
                      'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
                      businessViewTab === 'defaults'
                        ? 'border-amber-500 text-amber-700'
                        : 'border-transparent text-secondary-500 hover:text-secondary-700'
                    ]"
                  >
                    <Icon name="heroicons:cog-6-tooth" class="w-4 h-4 inline mr-1" />
                    Default Settings
                  </button>
                  <button
                    @click="businessViewTab = 'users'; loadBusinessUsers()"
                    :class="[
                      'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
                      businessViewTab === 'users'
                        ? 'border-amber-500 text-amber-700'
                        : 'border-transparent text-secondary-500 hover:text-secondary-700'
                    ]"
                  >
                    <Icon name="heroicons:users" class="w-4 h-4 inline mr-1" />
                    User Customizations
                    <span v-if="businessUsers.filter(u => u.has_custom).length > 0" class="ml-1 px-1.5 py-0.5 text-xs bg-amber-200 text-amber-800 rounded-full">
                      {{ businessUsers.filter(u => u.has_custom).length }}
                    </span>
                  </button>
                </div>
              </div>

              <!-- Business Users List (when users tab is active) -->
              <div v-if="plan.id === 'business' && businessViewTab === 'users'" class="mb-4">
                <!-- Search Filter -->
                <div class="mb-3">
                  <div class="relative">
                    <Icon name="heroicons:magnifying-glass" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-secondary-400" />
                    <input
                      v-model="businessUserSearch"
                      type="text"
                      placeholder="Search by name or email..."
                      class="w-full pl-9 pr-3 py-2 text-sm border border-secondary-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                    />
                    <button
                      v-if="businessUserSearch"
                      @click="businessUserSearch = ''"
                      class="absolute right-2 top-1/2 -translate-y-1/2 text-secondary-400 hover:text-secondary-600"
                    >
                      <Icon name="heroicons:x-mark" class="w-4 h-4" />
                    </button>
                  </div>
                </div>
                <div v-if="businessUsersLoading" class="text-center py-8">
                  <Icon name="heroicons:arrow-path" class="w-6 h-6 text-amber-500 animate-spin mx-auto" />
                </div>
                <div v-else-if="filteredBusinessUsers.length === 0" class="text-center py-8 text-secondary-500">
                  <Icon name="heroicons:users" class="w-8 h-8 mx-auto mb-2 text-secondary-300" />
                  <p>{{ businessUserSearch ? 'No matching users found' : 'No Business users found' }}</p>
                </div>
                <div v-else class="space-y-2 max-h-[300px] overflow-y-auto">
                  <div
                    v-for="user in filteredBusinessUsers"
                    :key="user.id"
                    class="flex items-center justify-between p-3 bg-white border border-secondary-200 rounded-lg hover:border-amber-300 transition-colors"
                  >
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 font-medium text-sm">
                        {{ user.name?.charAt(0) || '?' }}
                      </div>
                      <div>
                        <div class="text-sm font-medium text-secondary-900">{{ user.name }}</div>
                        <div class="text-xs text-secondary-500">{{ user.email }}</div>
                      </div>
                    </div>
                    <div class="flex items-center gap-2">
                      <span v-if="user.has_custom" class="text-xs px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full">Custom</span>
                      <span v-else class="text-xs px-2 py-0.5 bg-secondary-100 text-secondary-500 rounded-full">Default</span>
                      <button
                        @click="openBusinessUserModal(user)"
                        class="p-1.5 text-primary-600 hover:bg-primary-50 rounded transition-colors"
                        title="Configure"
                      >
                        <Icon name="heroicons:pencil-square" class="w-4 h-4" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Three Column Layout (Default Settings) -->
              <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Column 1: Data Fields -->
                <div class="space-y-3">
                  <h3 class="text-sm font-bold text-secondary-900 flex items-center pb-2 border-b border-secondary-200">
                    <Icon name="heroicons:rectangle-stack" class="w-4 h-4 mr-2 text-blue-600" />
                    Data Fields
                    <span class="ml-auto text-xs font-normal text-secondary-500">{{ generalTabs.length }} sections</span>
                  </h3>
                  <div class="space-y-2 max-h-[400px] overflow-y-auto pr-2">
                    <div v-for="tab in generalTabs" :key="tab.id" class="border border-secondary-200 rounded-lg overflow-hidden">
                      <div 
                        @click="toggleCategory(plan.id, tab.id)"
                        class="flex items-center justify-between p-2.5 bg-secondary-50 hover:bg-secondary-100 cursor-pointer transition-all"
                      >
                        <div class="flex items-center gap-2">
                          <input
                            type="checkbox"
                            :checked="isSectionFullySelected(plan.id, tab.id)"
                            @change="toggleSectionForPlan(plan.id, tab.id, $event.target.checked)"
                            @click.stop
                            class="w-4 h-4 rounded border-secondary-300 text-primary-600"
                          />
                          <Icon :name="tab.icon" class="w-4 h-4 text-secondary-600" />
                          <span class="text-sm font-medium text-secondary-900">{{ tab.name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                          <span class="text-xs text-secondary-500 bg-white px-1.5 py-0.5 rounded">{{ getFieldTabItemCount(tab.id) }}</span>
                          <Icon 
                            :name="expandedCategories[plan.id]?.includes(tab.id) ? 'heroicons:chevron-up' : 'heroicons:chevron-down'"
                            class="w-4 h-4 text-secondary-400"
                          />
                        </div>
                      </div>
                      <div v-if="expandedCategories[plan.id]?.includes(tab.id)" class="p-2 bg-white space-y-1">
                        <label
                          v-for="field in (fields[tab.id.replace('field_', '')] || [])"
                          :key="field.id"
                          class="flex items-center p-1.5 rounded hover:bg-secondary-50 cursor-pointer transition-all text-xs"
                        >
                          <input
                            type="checkbox"
                            :checked="isAvailableForPlan(field, plan.id)"
                            @change="toggleOptionForPlan(field, plan.id, $event.target.checked)"
                            class="w-3.5 h-3.5 rounded border-secondary-300 text-primary-600"
                          />
                          <span class="ml-2 text-secondary-700 truncate">{{ field.label }}</span>
                        </label>
                        <div v-if="(fields[tab.id.replace('field_', '')] || []).length === 0" class="text-xs text-secondary-400 italic p-2 text-center">
                          No fields yet
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Column 2: Design Options -->
                <div class="space-y-3">
                  <h3 class="text-sm font-bold text-secondary-900 flex items-center pb-2 border-b border-secondary-200">
                    <Icon name="heroicons:paint-brush" class="w-4 h-4 mr-2 text-purple-600" />
                    Design Options
                    <span class="ml-auto text-xs font-normal text-secondary-500">{{ getDesignCategoryItemCount(['theme', 'font', 'button_style', 'color_scheme', 'layout']) }} items</span>
                  </h3>
                  <div class="space-y-2 max-h-[400px] overflow-y-auto pr-2">
                    <div v-for="designType in ['theme', 'font', 'button_style', 'color_scheme', 'layout']" :key="designType" class="border border-secondary-200 rounded-lg overflow-hidden">
                      <div 
                        @click="toggleCategory(plan.id, designType)"
                        class="flex items-center justify-between p-2.5 bg-secondary-50 hover:bg-secondary-100 cursor-pointer transition-all"
                      >
                        <div class="flex items-center gap-2">
                          <input
                            type="checkbox"
                            :checked="isDesignTypeFullySelected(plan.id, designType)"
                            @change="toggleDesignTypeForPlan(plan.id, designType, $event.target.checked)"
                            @click.stop
                            class="w-4 h-4 rounded border-secondary-300 text-primary-600"
                          />
                          <Icon :name="getDesignTypeIcon(designType)" class="w-4 h-4 text-secondary-600" />
                          <span class="text-sm font-medium text-secondary-900">{{ getTabName(designType) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                          <span class="text-xs text-secondary-500 bg-white px-1.5 py-0.5 rounded">{{ (options[designType] || []).length }}</span>
                          <Icon 
                            :name="expandedCategories[plan.id]?.includes(designType) ? 'heroicons:chevron-up' : 'heroicons:chevron-down'"
                            class="w-4 h-4 text-secondary-400"
                          />
                        </div>
                      </div>
                      <div v-if="expandedCategories[plan.id]?.includes(designType)" class="p-2 bg-white space-y-1">
                        <label
                          v-for="option in (options[designType] || [])"
                          :key="option.id"
                          class="flex items-center p-1.5 rounded hover:bg-secondary-50 cursor-pointer transition-all text-xs"
                        >
                          <input
                            type="checkbox"
                            :checked="isAvailableForPlan(option, plan.id)"
                            @change="toggleOptionForPlan(option, plan.id, $event.target.checked)"
                            class="w-3.5 h-3.5 rounded border-secondary-300 text-primary-600"
                          />
                          <span class="ml-2 text-secondary-700 truncate">{{ option.name }}</span>
                        </label>
                        <div v-if="(options[designType] || []).length === 0" class="text-xs text-secondary-400 italic p-2 text-center">
                          No options yet
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Column 3: Features -->
                <div class="space-y-3">
                  <h3 class="text-sm font-bold text-secondary-900 flex items-center pb-2 border-b border-secondary-200">
                    <Icon name="heroicons:sparkles" class="w-4 h-4 mr-2 text-amber-600" />
                    Features
                    <span class="ml-auto text-xs font-normal text-secondary-500">{{ (options.feature_toggle || []).length }} toggles</span>
                  </h3>
                  <div class="space-y-2">
                    <div class="border border-secondary-200 rounded-lg p-3 bg-white">
                      <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-medium text-secondary-700">Select All Features</span>
                        <input
                          type="checkbox"
                          :checked="isDesignTypeFullySelected(plan.id, 'feature_toggle')"
                          @change="toggleDesignTypeForPlan(plan.id, 'feature_toggle', $event.target.checked)"
                          class="w-4 h-4 rounded border-secondary-300 text-primary-600"
                        />
                      </div>
                      <div class="space-y-2">
                        <label
                          v-for="feature in (options.feature_toggle || [])"
                          :key="feature.id"
                          :class="[
                            'flex items-center justify-between p-2.5 rounded-lg border cursor-pointer transition-all',
                            isAvailableForPlan(feature, plan.id) 
                              ? 'border-primary-300 bg-primary-50' 
                              : 'border-secondary-200 hover:bg-secondary-50'
                          ]"
                        >
                          <div class="flex items-center gap-2">
                            <Icon name="heroicons:check-circle" :class="[
                              'w-4 h-4',
                              isAvailableForPlan(feature, plan.id) ? 'text-primary-600' : 'text-secondary-300'
                            ]" />
                            <span :class="[
                              'text-sm font-medium',
                              isAvailableForPlan(feature, plan.id) ? 'text-primary-900' : 'text-secondary-700'
                            ]">{{ feature.name }}</span>
                          </div>
                          <input
                            type="checkbox"
                            :checked="isAvailableForPlan(feature, plan.id)"
                            @change="toggleOptionForPlan(feature, plan.id, $event.target.checked)"
                            class="w-4 h-4 rounded border-secondary-300 text-primary-600"
                          />
                        </label>
                        <div v-if="(options.feature_toggle || []).length === 0" class="text-xs text-secondary-400 italic p-4 text-center">
                          No features configured yet
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

    <!-- Section Management -->
    <div class="card mb-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-secondary-200">
        <div>
          <h2 class="text-xl font-bold text-secondary-900 flex items-center">
            <Icon name="heroicons:rectangle-stack" class="w-5 h-5 mr-2 text-primary-600" />
            Manage Field Sections
          </h2>
          <p class="text-sm text-secondary-500 mt-1">
            Create and organize profile sections • {{ generalSections.length }} sections
          </p>
        </div>
        <div class="flex items-center gap-2">
          <button
            @click="loadSections"
            :disabled="sectionsLoading"
            class="p-2 text-secondary-500 hover:text-secondary-700 hover:bg-secondary-100 rounded-lg transition-colors"
            title="Refresh Sections"
          >
            <Icon 
              name="heroicons:arrow-path" 
              :class="['w-5 h-5', sectionsLoading && 'animate-spin']" 
            />
          </button>
          <button
            @click="openSectionModal('add')"
            class="btn btn-primary flex items-center gap-2"
          >
            <Icon name="heroicons:plus" class="w-4 h-4" />
            Add Section
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="sectionsLoading" class="flex items-center justify-center py-12">
        <div class="text-center">
          <Icon name="heroicons:arrow-path" class="w-8 h-8 text-primary-500 animate-spin mx-auto mb-3" />
          <p class="text-sm text-secondary-500">Loading sections...</p>
        </div>
      </div>

      <!-- Sections Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <div
          v-for="(section, index) in generalSections"
          :key="section.id || section.key"
          :class="[
            'relative rounded-xl overflow-hidden transition-all duration-200 group',
            'bg-gradient-to-br from-white to-secondary-50',
            'border-2 hover:shadow-lg',
            section.is_active !== false ? 'border-secondary-200 hover:border-primary-400' : 'border-secondary-200 opacity-60'
          ]"
        >
          <!-- Status Badge -->
          <div class="absolute top-2 right-2 z-10">
            <span
              v-if="section.is_active === false"
              class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-secondary-200 text-secondary-600"
            >
              Inactive
            </span>
          </div>

          <!-- Card Content -->
          <div class="p-4">
            <!-- Header -->
            <div class="flex items-start gap-3 mb-3">
              <div 
                :class="[
                  'w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0',
                  'bg-gradient-to-br',
                  index % 4 === 0 ? 'from-blue-100 to-blue-200' :
                  index % 4 === 1 ? 'from-purple-100 to-purple-200' :
                  index % 4 === 2 ? 'from-amber-100 to-amber-200' :
                  'from-emerald-100 to-emerald-200'
                ]"
              >
                <Icon 
                  :name="section.icon || 'heroicons:document-text'" 
                  :class="[
                    'w-6 h-6',
                    index % 4 === 0 ? 'text-blue-600' :
                    index % 4 === 1 ? 'text-purple-600' :
                    index % 4 === 2 ? 'text-amber-600' :
                    'text-emerald-600'
                  ]"
                />
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-secondary-900 truncate">{{ section.name }}</h3>
                <code class="text-xs text-secondary-400 bg-secondary-100 px-1.5 py-0.5 rounded font-mono">{{ section.key }}</code>
              </div>
            </div>

            <!-- Stats -->
            <div class="flex items-center gap-3 mb-3">
              <div class="flex items-center gap-1.5 text-xs text-secondary-600">
                <Icon name="heroicons:queue-list" class="w-3.5 h-3.5" />
                <span class="font-medium">{{ (fields[section.key] || []).length }}</span>
                <span>fields</span>
              </div>
              <div class="flex items-center gap-1.5 text-xs text-secondary-600">
                <span>#{{ section.display_order || index + 1 }}</span>
              </div>
            </div>

            <!-- Plan Badges (Auto-calculated from fields) -->
            <div class="flex flex-wrap gap-1 mb-3">
              <span
                v-if="getSectionPlans(section.key).length === 0 || getSectionPlans(section.key).length === 3"
                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-700"
              >
                All Plans
              </span>
              <template v-else>
                <span
                  v-for="plan in getSectionPlans(section.key)"
                  :key="plan"
                  :class="[
                    'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                    plan === 'basic' ? 'bg-blue-100 text-blue-700' :
                    plan === 'premium' ? 'bg-purple-100 text-purple-700' :
                    'bg-amber-100 text-amber-700'
                  ]"
                >
                  {{ plan.charAt(0).toUpperCase() + plan.slice(1) }}
                </span>
              </template>
            </div>

            <!-- Description -->
            <p v-if="section.description" class="text-xs text-secondary-500 line-clamp-2 mb-3">
              {{ section.description }}
            </p>

            <!-- Actions -->
            <div class="flex items-center gap-2 pt-3 border-t border-secondary-200">
              <button
                @click="openSectionModal('edit', section)"
                class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-medium text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition-colors"
              >
                <Icon name="heroicons:pencil" class="w-3.5 h-3.5" />
                Edit
              </button>
              <button
                @click="confirmDeleteSection(section)"
                class="p-1.5 text-error-600 hover:bg-error-50 rounded-lg transition-colors"
                title="Delete Section"
              >
                <Icon name="heroicons:trash" class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>

        <!-- Add New Section Card -->
        <button
          @click="openSectionModal('add')"
          class="relative rounded-xl border-2 border-dashed border-secondary-300 hover:border-primary-400 bg-secondary-50 hover:bg-primary-50 transition-all duration-200 min-h-[200px] flex flex-col items-center justify-center gap-3 group"
        >
          <div class="w-12 h-12 rounded-xl bg-white border-2 border-secondary-200 group-hover:border-primary-300 flex items-center justify-center transition-colors">
            <Icon name="heroicons:plus" class="w-6 h-6 text-secondary-400 group-hover:text-primary-600" />
          </div>
          <div class="text-center">
            <p class="text-sm font-medium text-secondary-600 group-hover:text-primary-700">Add New Section</p>
            <p class="text-xs text-secondary-400">Click to create</p>
          </div>
        </button>

        <!-- Empty State (when no sections at all) -->
        <div
          v-if="generalSections.length === 0 && !sectionsLoading"
          class="col-span-full text-center py-16 bg-secondary-50 rounded-xl border-2 border-dashed border-secondary-200"
        >
          <Icon name="heroicons:folder-open" class="w-16 h-16 text-secondary-300 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-secondary-700 mb-2">No Sections Yet</h3>
          <p class="text-sm text-secondary-500 mb-4">Get started by creating your first section</p>
          <button
            @click="openSectionModal('add')"
            class="btn btn-primary"
          >
            <Icon name="heroicons:plus" class="w-4 h-4 mr-2" />
            Create First Section
          </button>
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
                  <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-secondary-700 uppercase tracking-wider">
                    Group
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
                  <td class="px-4 py-4">
                    <div v-if="field.field_group" class="flex items-center gap-1.5">
                      <Icon v-if="field.field_group_icon" :name="field.field_group_icon" class="w-4 h-4 text-indigo-500" />
                      <span class="text-xs font-medium text-secondary-700">{{ field.field_group }}</span>
                    </div>
                    <span v-else class="text-xs text-secondary-400 italic">No group</span>
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
                            <optgroup label="Basic Input">
                              <option value="text">Text</option>
                              <option value="email">Email</option>
                              <option value="tel">Phone</option>
                              <option value="url">URL</option>
                              <option value="number">Number</option>
                            </optgroup>
                            <optgroup label="Text Areas">
                              <option value="textarea">Textarea</option>
                              <option value="richtext">Rich Text Editor</option>
                            </optgroup>
                            <optgroup label="Selection">
                              <option value="select">Dropdown Select</option>
                              <option value="toggle">Toggle Switch</option>
                              <option value="checkbox">Checkbox</option>
                              <option value="date">Date Picker</option>
                            </optgroup>
                            <optgroup label="Media & Files">
                              <option value="image">Image Upload</option>
                              <option value="gallery">Image Gallery</option>
                              <option value="video">Video</option>
                              <option value="file">File Upload</option>
                            </optgroup>
                            <optgroup label="Advanced">
                              <option value="repeater">Repeater (Multiple Items)</option>
                              <option value="tags">Tags Input</option>
                              <option value="icon">Icon Picker</option>
                              <option value="color">Color Picker</option>
                            </optgroup>
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

                      <!-- Field Grouping -->
                      <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                        <h4 class="text-sm font-semibold text-secondary-900 mb-3 flex items-center">
                          <Icon name="heroicons:rectangle-group" class="w-4 h-4 mr-2 text-indigo-600" />
                          Field Grouping (for UI display)
                        </h4>
                        <div class="grid grid-cols-3 gap-4">
                          <div>
                            <label class="block text-xs font-medium text-secondary-700 mb-1.5">Group Name</label>
                            <input
                              v-model="formData.field_group"
                              type="text"
                              class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg"
                              placeholder="e.g., Basic Information"
                            />
                          </div>
                          <div>
                            <label class="block text-xs font-medium text-secondary-700 mb-1.5">Group Icon</label>
                            <select
                              v-model="formData.field_group_icon"
                              class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg"
                            >
                              <option value="">Select icon...</option>
                              <option value="heroicons:user-circle">👤 User Circle</option>
                              <option value="heroicons:document-text">📄 Document</option>
                              <option value="heroicons:phone">📞 Phone</option>
                              <option value="heroicons:academic-cap">🎓 Academic</option>
                              <option value="heroicons:identification">🪪 Identification</option>
                              <option value="heroicons:information-circle">ℹ️ Information</option>
                              <option value="heroicons:star">⭐ Star</option>
                              <option value="heroicons:map-pin">📍 Map Pin</option>
                              <option value="heroicons:clock">🕐 Clock</option>
                              <option value="heroicons:trophy">🏆 Trophy</option>
                              <option value="heroicons:user-group">👥 User Group</option>
                              <option value="heroicons:rocket-launch">🚀 Rocket</option>
                              <option value="heroicons:photo">🖼️ Photo</option>
                              <option value="heroicons:currency-dollar">💵 Currency</option>
                              <option value="heroicons:pencil-square">✏️ Pencil</option>
                              <option value="heroicons:calendar">📅 Calendar</option>
                              <option value="heroicons:clipboard-document-list">📋 Clipboard</option>
                              <option value="heroicons:folder">📁 Folder</option>
                              <option value="heroicons:squares-plus">➕ Squares Plus</option>
                            </select>
                          </div>
                          <div>
                            <label class="block text-xs font-medium text-secondary-700 mb-1.5">Group Order</label>
                            <input
                              v-model.number="formData.group_order"
                              type="number"
                              class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg"
                              placeholder="1"
                              min="1"
                            />
                          </div>
                        </div>
                        <p class="text-xs text-secondary-500 mt-2">Fields with the same group name will be displayed together in Profile Builder</p>
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

    <!-- Section Modal -->
    <TransitionRoot as="template" :show="showSectionModal">
      <Dialog as="div" class="relative z-50" @close="closeSectionModal">
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
                    {{ sectionModalMode === 'add' ? 'Add New' : 'Edit' }} Section
                  </DialogTitle>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-6">
                  <div class="space-y-5 max-h-[calc(100vh-280px)] overflow-y-auto pr-2">
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <label class="block text-sm font-medium text-secondary-900 mb-2">Section Key <span class="text-error-600">*</span></label>
                        <input
                          v-model="sectionFormData.key"
                          type="text"
                          :disabled="sectionModalMode === 'edit'"
                          class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 disabled:bg-secondary-100"
                          placeholder="e.g., profile, company, services"
                        />
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-secondary-900 mb-2">Section Name <span class="text-error-600">*</span></label>
                        <input
                          v-model="sectionFormData.name"
                          type="text"
                          class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                          placeholder="e.g., Profile, Company, Services"
                        />
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-secondary-900 mb-2">Icon</label>
                        <input
                          v-model="sectionFormData.icon"
                          type="text"
                          class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                          placeholder="e.g., heroicons:user"
                        />
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-secondary-900 mb-2">Description</label>
                        <textarea
                          v-model="sectionFormData.description"
                          rows="2"
                          class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 resize-none"
                          placeholder="Brief description..."
                        ></textarea>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-secondary-900 mb-2">Display Order</label>
                        <input
                          v-model.number="sectionFormData.display_order"
                          type="number"
                          class="w-full px-3 py-2 text-sm border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                          min="0"
                          placeholder="0"
                        />
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-secondary-900 mb-2">Has Fields</label>
                        <label class="flex items-center p-2 bg-white rounded border border-secondary-300 cursor-pointer">
                          <input v-model="sectionFormData.has_fields" type="checkbox" class="w-4 h-4 rounded border-secondary-300 text-primary-600" />
                          <span class="ml-2 text-sm text-secondary-900">Yes</span>
                        </label>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-secondary-900 mb-2">Is Active</label>
                        <label class="flex items-center p-2 bg-white rounded border border-secondary-300 cursor-pointer">
                          <input v-model="sectionFormData.is_active" type="checkbox" class="w-4 h-4 rounded border-secondary-300 text-primary-600" />
                          <span class="ml-2 text-sm text-secondary-900">Yes</span>
                        </label>
                      </div>

                      <!-- Available Plans Info -->
                      <div class="col-span-2">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                          <div class="flex items-start gap-2">
                            <Icon name="heroicons:information-circle" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
                            <div>
                              <p class="text-sm font-medium text-blue-800">Plan Availability</p>
                              <p class="text-xs text-blue-600 mt-1">
                                Section plans are automatically calculated based on the fields' plan settings. 
                                Configure plan availability in the "Plan Assignments" section below.
                              </p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-secondary-50 px-6 py-4 border-t border-secondary-200 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                  <button
                    type="button"
                    @click="closeSectionModal"
                    class="px-4 py-2 text-sm font-medium text-secondary-700 bg-white border border-secondary-300 rounded-lg hover:bg-secondary-50"
                  >
                    Cancel
                  </button>
                  <button
                    type="button"
                    @click="saveSection"
                    :disabled="saving"
                    class="px-4 py-2 text-sm font-semibold text-white bg-primary-600 rounded-lg hover:bg-primary-700 disabled:opacity-50"
                  >
                    <Icon v-if="saving" name="heroicons:arrow-path" class="w-4 h-4 mr-1.5 animate-spin inline" />
                    {{ saving ? 'Saving...' : (sectionModalMode === 'add' ? 'Add' : 'Update') }}
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Business User Modal -->
    <TransitionRoot as="template" :show="showBusinessUserModal">
      <Dialog as="div" class="relative z-50" @close="closeBusinessUserModal">
        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-secondary-900/75 backdrop-blur-sm" />
        </TransitionChild>
        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="ease-in duration-200" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
              <DialogPanel class="relative w-full max-w-3xl bg-white rounded-xl shadow-2xl">
                <div class="flex items-center justify-between p-4 border-b border-secondary-200 bg-amber-50">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-amber-200 flex items-center justify-center">
                      <span class="text-amber-800 font-bold">{{ selectedBusinessUser?.name?.charAt(0) || '?' }}</span>
                    </div>
                    <div>
                      <DialogTitle class="font-semibold text-secondary-900">{{ selectedBusinessUser?.name }}</DialogTitle>
                      <p class="text-sm text-secondary-500">{{ selectedBusinessUser?.email }}</p>
                    </div>
                  </div>
                  <button @click="closeBusinessUserModal" class="p-2 hover:bg-amber-100 rounded-lg">
                    <Icon name="heroicons:x-mark" class="w-5 h-5 text-secondary-500" />
                  </button>
                </div>
                <div class="p-6 max-h-[60vh] overflow-y-auto">
                  <div class="mb-6 p-4 bg-secondary-50 rounded-lg">
                    <label class="flex items-center justify-between cursor-pointer">
                      <div>
                        <span class="font-medium text-secondary-900">Use Custom Settings</span>
                        <p class="text-sm text-secondary-500">Override default Business plan settings</p>
                      </div>
                      <input type="checkbox" v-model="businessUserFormData.use_custom" class="w-5 h-5 rounded border-secondary-300 text-amber-600" />
                    </label>
                  </div>
                  <div v-if="businessUserFormData.use_custom" class="space-y-4">
                    <!-- Data Fields - Grouped by Tab -->
                    <div>
                      <h4 class="font-medium text-secondary-900 mb-2"><Icon name="heroicons:rectangle-stack" class="w-4 h-4 inline mr-1 text-blue-600" />Data Fields</h4>
                      <div class="max-h-[250px] overflow-y-auto p-2 bg-secondary-50 rounded-lg space-y-3">
                        <div v-for="(tabFields, tabName) in businessUserAvailableData.fields" :key="tabName">
                          <div class="flex items-center justify-between sticky top-0 bg-secondary-50 py-1 mb-1">
                            <label class="flex items-center cursor-pointer">
                              <input 
                                type="checkbox" 
                                :checked="isTabFullySelected(tabFields)" 
                                @change="toggleTabFields(tabFields, $event.target.checked)"
                                class="w-3.5 h-3.5 rounded text-blue-600"
                              />
                              <span class="ml-2 text-xs font-semibold text-secondary-600 uppercase">{{ tabName }}</span>
                            </label>
                            <span class="text-xs text-secondary-400">{{ getTabSelectedCount(tabFields) }}/{{ tabFields.length }}</span>
                          </div>
                          <div class="grid grid-cols-3 gap-1">
                            <label v-for="field in tabFields" :key="field.id" class="flex items-center p-1.5 bg-white rounded border text-xs cursor-pointer hover:border-primary-300">
                              <input type="checkbox" :value="field.id" v-model="businessUserFormData.enabled_fields" class="w-3 h-3 rounded" />
                              <span class="ml-1.5 truncate">{{ field.label }}</span>
                            </label>
                          </div>
                        </div>
                      </div>
                    </div>
                    <!-- Design Options - Grouped by Type -->
                    <div>
                      <h4 class="font-medium text-secondary-900 mb-2"><Icon name="heroicons:paint-brush" class="w-4 h-4 inline mr-1 text-purple-600" />Design Options</h4>
                      <div class="max-h-[200px] overflow-y-auto p-2 bg-secondary-50 rounded-lg space-y-3">
                        <template v-for="(typeOptions, typeName) in businessUserAvailableData.design_options" :key="typeName">
                          <div v-if="typeName !== 'feature_toggle'">
                            <div class="flex items-center justify-between mb-1">
                              <label class="flex items-center cursor-pointer">
                                <input 
                                  type="checkbox" 
                                  :checked="isTypeFullySelected(typeOptions)" 
                                  @change="toggleTypeOptions(typeOptions, $event.target.checked)"
                                  class="w-3.5 h-3.5 rounded text-purple-600"
                                />
                                <span class="ml-2 text-xs font-semibold text-secondary-600 uppercase">{{ typeName.replace('_', ' ') }}</span>
                              </label>
                              <span class="text-xs text-secondary-400">{{ getTypeSelectedCount(typeOptions) }}/{{ typeOptions.length }}</span>
                            </div>
                            <div class="grid grid-cols-3 gap-1">
                              <label v-for="option in typeOptions" :key="option.id" class="flex items-center p-1.5 bg-white rounded border text-xs cursor-pointer hover:border-primary-300">
                                <input type="checkbox" :value="option.id" v-model="businessUserFormData.enabled_design_options" class="w-3 h-3 rounded" />
                                <span class="ml-1.5 truncate">{{ option.name }}</span>
                              </label>
                            </div>
                          </div>
                        </template>
                      </div>
                    </div>
                    <!-- Features -->
                    <div>
                      <h4 class="font-medium text-secondary-900 mb-2"><Icon name="heroicons:sparkles" class="w-4 h-4 inline mr-1 text-amber-600" />Features</h4>
                      <div class="grid grid-cols-2 gap-2">
                        <label v-for="feature in (businessUserAvailableData.design_options?.feature_toggle || [])" :key="feature.option_id"
                          :class="['flex items-center p-2 rounded-lg border cursor-pointer', businessUserFormData.enabled_features.includes(feature.option_id) ? 'border-amber-300 bg-amber-50' : 'border-secondary-200']">
                          <input type="checkbox" :value="feature.option_id" v-model="businessUserFormData.enabled_features" class="w-4 h-4 rounded text-amber-600" />
                          <span class="ml-2 text-sm font-medium">{{ feature.name }}</span>
                        </label>
                      </div>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-secondary-700 mb-2">Admin Notes</label>
                      <textarea v-model="businessUserFormData.notes" rows="2" class="w-full px-3 py-2 border border-secondary-300 rounded-lg text-sm" placeholder="Optional notes..."></textarea>
                    </div>
                  </div>
                  <div v-else class="text-center py-8 bg-secondary-50 rounded-lg">
                    <Icon name="heroicons:check-circle" class="w-12 h-12 text-success-500 mx-auto mb-2" />
                    <p class="font-medium text-secondary-700">Using Default Business Plan Settings</p>
                  </div>
                </div>
                <div class="flex items-center justify-between p-4 border-t border-secondary-200 bg-secondary-50">
                  <button v-if="businessUserFormData.use_custom" @click="resetBusinessUserToDefault" class="text-sm text-secondary-600 hover:text-secondary-900">Reset to Default</button>
                  <div v-else></div>
                  <div class="flex gap-3">
                    <button @click="closeBusinessUserModal" class="px-4 py-2 text-sm font-medium text-secondary-700 hover:bg-secondary-100 rounded-lg">Cancel</button>
                    <button @click="saveBusinessUserAssignment" :disabled="savingBusinessUser" class="px-4 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-lg disabled:opacity-50">
                      {{ savingBusinessUser ? 'Saving...' : 'Save' }}
                    </button>
                  </div>
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

// Business User Customization
const businessViewTab = ref('defaults');
const businessUsers = ref([]);
const businessUsersLoading = ref(false);
const showBusinessUserModal = ref(false);
const selectedBusinessUser = ref(null);
const businessUserFormData = ref({
  use_custom: false,
  enabled_fields: [],
  enabled_design_options: [],
  enabled_features: [],
  notes: '',
});
const businessUserAvailableData = ref({
  fields: {},
  design_options: {},
  defaults: { fields: [], design_options: [], features: [] }
});
const savingBusinessUser = ref(false);
const businessUserSearch = ref('');

// Filtered business users based on search
const filteredBusinessUsers = computed(() => {
  if (!businessUserSearch.value) return businessUsers.value;
  const search = businessUserSearch.value.toLowerCase();
  return businessUsers.value.filter(user => 
    user.name?.toLowerCase().includes(search) || 
    user.email?.toLowerCase().includes(search)
  );
});

// Section Management
const showSectionModal = ref(false);
const sectionModalMode = ref('add');
const editingSection = ref(null);
const sectionFormData = ref({
  key: '',
  name: '',
  icon: 'heroicons:document-text',
  category: 'general',
  has_fields: true,
  description: '',
  display_order: 0,
  is_active: true,
  available_plans: ['basic', 'premium', 'business']
});

// ========================================
// 🆕 DYNAMIC SECTIONS - Loaded from API
// ========================================
const availableSections = ref([]);
const sectionsLoading = ref(false);
const fieldTypes = ref({});

// Load available sections from API
const loadSections = async () => {
  try {
    sectionsLoading.value = true;
    console.log('🔍 Loading sections from API...');
    
    const response = await fetch(`${config.public.apiBaseUrl}/admin/profile-builder/sections`, {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
    });

    if (!response.ok) throw new Error('Failed to load sections');
    const data = await response.json();
    
    console.log('📋 Sections API Response:', data);
    
    if (data.success && data.data) {
      availableSections.value = data.data.sections || [];
      fieldTypes.value = data.data.field_types || {};
      console.log('✅ Sections loaded:', availableSections.value.length);
    }
  } catch (error) {
    console.error('❌ Error loading sections:', error);
    // Fallback to basic sections
    availableSections.value = [
      { key: 'profile', name: 'Profile', icon: 'heroicons:user', category: 'general', has_fields: true },
      { key: 'company', name: 'Company', icon: 'heroicons:building-office', category: 'general', has_fields: true },
      { key: 'services', name: 'Services', icon: 'heroicons:rocket-launch', category: 'general', has_fields: true },
      { key: 'links', name: 'Links', icon: 'heroicons:link', category: 'general', has_fields: true },
    ];
  } finally {
    sectionsLoading.value = false;
  }
};

// Plans
const plans = [
  { id: 'business', name: 'Business', color: 'amber' },
  { id: 'premium', name: 'Premium', color: 'purple' },
  { id: 'basic', name: 'Basic', color: 'blue' },
];

// Computed: General Sections (for Section Management UI)
const generalSections = computed(() => {
  return availableSections.value
    .filter(s => s.category === 'general')
    .sort((a, b) => (a.display_order || 0) - (b.display_order || 0));
});

// Computed: General Sections tabs from API (dynamic)
const generalTabs = computed(() => {
  return generalSections.value.map(s => ({
    id: `field_${s.key}`,
    name: s.name,
    icon: s.icon,
    category: s.category,
    requiredPermission: s.key,
    has_fields: s.has_fields
  }));
});

// Design Sections tabs (aligned with user frontend)
const designTabs = [
  { id: 'style', name: 'Style', icon: 'heroicons:sparkles', category: 'design', requiredPermission: 'style', subItems: ['theme', 'font', 'button_style', 'color_scheme', 'layout'] },
  { id: 'watermarks', name: 'Watermarks', icon: 'heroicons:eye', category: 'design', requiredPermission: 'watermarks', subItems: ['feature_toggle'] },
];

// All tabs (for backward compatibility with legacy code)
const tabs = [
  { id: 'theme', name: 'Themes', icon: 'heroicons:paint-brush' },
  { id: 'font', name: 'Fonts', icon: 'heroicons:language' },
  { id: 'button_style', name: 'Button Styles', icon: 'heroicons:cursor-arrow-rays' },
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
// Note: generalTabs is now a computed property, so we initialize with design tabs only
// and will populate general tabs dynamically after sections load
const expandedCategories = ref({
  business: [
    ...designTabs.map(t => t.id),
    ...tabs.map(t => t.id), 
    ...fieldTabs.map(t => t.id)
  ],
  premium: [
    ...designTabs.map(t => t.id),
    ...tabs.map(t => t.id), 
    ...fieldTabs.map(t => t.id)
  ],
  basic: [
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
      field_group: '',
      field_group_icon: '',
      group_order: 0,
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

// ========================================
// 🆕 SECTION MANAGEMENT FUNCTIONS
// ========================================

// Open Section Modal
const openSectionModal = (mode, section = null) => {
  sectionModalMode.value = mode;
  
  if (mode === 'edit' && section) {
    editingSection.value = section;
    sectionFormData.value = {
      key: section.key,
      name: section.name,
      icon: section.icon || 'heroicons:document-text',
      category: section.category || 'general',
      has_fields: section.has_fields !== false,
      description: section.description || '',
      display_order: section.display_order || 0,
      is_active: section.is_active !== false,
      available_plans: section.available_plans || ['basic', 'premium', 'business']
    };
  } else {
    editingSection.value = null;
    sectionFormData.value = {
      key: '',
      name: '',
      icon: 'heroicons:document-text',
      category: 'general',
      has_fields: true,
      description: '',
      display_order: availableSections.value.length, // Auto-increment order
      is_active: true,
      available_plans: ['basic', 'premium', 'business']
    };
  }
  
  showSectionModal.value = true;
};

// Close Section Modal
const closeSectionModal = () => {
  showSectionModal.value = false;
  editingSection.value = null;
  sectionFormData.value = {
    key: '',
    name: '',
    icon: 'heroicons:document-text',
    category: 'general',
    has_fields: true,
    description: '',
    display_order: 0,
    is_active: true,
    available_plans: ['basic', 'premium', 'business']
  };
};

// Save Section (Create or Update)
const saveSection = async () => {
  // Validation
  if (!sectionFormData.value.key || !sectionFormData.value.name) {
    alert('Please fill in Section Key and Name');
    return;
  }
  
  // Validate key format (lowercase, no spaces)
  const keyRegex = /^[a-z][a-z0-9_]*$/;
  if (!keyRegex.test(sectionFormData.value.key)) {
    alert('Section Key must be lowercase, start with a letter, and contain only letters, numbers, and underscores');
    return;
  }
  
  saving.value = true;
  
  try {
    const isEdit = sectionModalMode.value === 'edit' && editingSection.value?.id;
    const url = isEdit
      ? `${config.public.apiBaseUrl}/admin/profile-builder/sections/${editingSection.value.id}`
      : `${config.public.apiBaseUrl}/admin/profile-builder/sections`;
    
    const method = isEdit ? 'PUT' : 'POST';
    
    console.log(`📤 ${method} Section:`, sectionFormData.value);
    
    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${authStore.token}`,
      },
      body: JSON.stringify(sectionFormData.value),
    });
    
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      throw new Error(errorData.message || `Failed to ${isEdit ? 'update' : 'create'} section`);
    }
    
    const result = await response.json();
    console.log('✅ Section saved:', result);
    
    alert(`Section ${isEdit ? 'updated' : 'created'} successfully!`);
    closeSectionModal();
    
    // Reload sections and data
    await loadSections();
    await fetchData();
  } catch (error) {
    console.error('❌ Error saving section:', error);
    alert(`Failed to save section: ${error.message}`);
  } finally {
    saving.value = false;
  }
};

// Delete Section
const confirmDeleteSection = async (section) => {
  const fieldsCount = (fields.value[section.key] || []).length;
  
  let confirmMsg = `Are you sure you want to delete the "${section.name}" section?`;
  if (fieldsCount > 0) {
    confirmMsg += `\n\n⚠️ WARNING: This will also delete ${fieldsCount} field(s) in this section!`;
  }
  confirmMsg += '\n\nThis action cannot be undone.';
  
  if (!confirm(confirmMsg)) {
    return;
  }
  
  try {
    const sectionId = section.id || section.key;
    console.log(`🗑️ Deleting section: ${section.name} (${sectionId})`);
    
    const response = await fetch(`${config.public.apiBaseUrl}/admin/profile-builder/sections/${sectionId}`, {
      method: 'DELETE',
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
    });
    
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      throw new Error(errorData.message || 'Failed to delete section');
    }
    
    console.log('✅ Section deleted successfully');
    alert('Section deleted successfully!');
    
    // Reload sections and data
    await loadSections();
    await fetchData();
  } catch (error) {
    console.error('❌ Error deleting section:', error);
    alert(`Failed to delete section: ${error.message}`);
  }
};

// Helper to ensure available_plans is an array
const ensureArray = (value) => {
  if (!value) return [];
  if (Array.isArray(value)) return value;
  if (typeof value === 'string') {
    try {
      const parsed = JSON.parse(value);
      return Array.isArray(parsed) ? parsed : [];
    } catch {
      return [];
    }
  }
  return [];
};

// Get Section Plans (Union of all fields' available_plans)
const getSectionPlans = (sectionKey) => {
  const sectionFields = fields.value[sectionKey] || [];
  if (sectionFields.length === 0) {
    return ['basic', 'premium', 'business']; // No fields = all plans
  }
  
  // Calculate union of all fields' available_plans
  const plansSet = new Set();
  sectionFields.forEach(field => {
    const fieldPlans = ensureArray(field.available_plans);
    if (fieldPlans.length === 0) {
      // Empty means all plans
      plansSet.add('basic');
      plansSet.add('premium');
      plansSet.add('business');
    } else {
      fieldPlans.forEach(plan => plansSet.add(plan));
    }
  });
  
  return Array.from(plansSet);
};

// ========================================

// Computed
const allTabs = computed(() => {
  // Combine design tabs with dynamic field tabs from sections
  const dynamicFieldTabs = generalTabs.value.map(tab => ({
    id: tab.id,
    name: `${tab.name} Fields`,
    icon: tab.icon
  }));
  return [...tabs, ...dynamicFieldTabs];
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
    // Use correct API endpoint: /admin/profile-builder/fields
    const response = await fetch(`${config.public.apiBaseUrl}/admin/profile-builder/fields`, {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      },
    });

    if (!response.ok) throw new Error('Failed to fetch fields');

    const data = await response.json();
    console.log('📋 Fields loaded:', data.data);
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
  
  // Count fields (using dynamic generalTabs)
  generalTabs.value.forEach(tab => {
    const fieldTab = tab.id.replace('field_', '');
    const tabFields = fields.value[fieldTab] || [];
    count += tabFields.filter(f => isAvailableForPlan(f, planId)).length;
  });
  
  return count;
};

// Toggle single option/field for a plan
const toggleOptionForPlan = async (item, planId, checked, skipRefresh = false) => {
  try {
    console.log(`🔄 toggleOptionForPlan: ${item.label || item.name}, plan=${planId}, checked=${checked}`);
    console.log(`   Before: available_plans =`, JSON.stringify(item.available_plans));
    
    // If available_plans is empty, it means "all plans" - initialize with all 3
    let plans = item.available_plans || [];
    if (plans.length === 0) {
      plans = ['basic', 'premium', 'business'];
      console.log(`   Initialized empty to all plans`);
    } else {
      plans = [...plans]; // Clone to avoid reference issues
    }
    
    if (checked && !plans.includes(planId)) {
      plans.push(planId);
    } else if (!checked && plans.includes(planId)) {
      plans = plans.filter(p => p !== planId);
    }
    
    console.log(`   After: available_plans =`, JSON.stringify(plans));
    
    item.available_plans = plans;
    
    // Only send the available_plans field to avoid validation issues
    const updateData = { available_plans: plans };
    
    // Determine if it's a field or design option
    if (item.field_key) {
      // It's a field
      await fetch(`${config.public.apiBaseUrl}/admin/profile-builder/fields/${item.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authStore.token}`,
        },
        body: JSON.stringify(updateData),
      });
    } else {
      // It's a design option
      await fetch(`${config.public.apiBaseUrl}/admin/profile-design-options/${item.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authStore.token}`,
        },
        body: JSON.stringify(updateData),
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

// Check if all items in a design type are selected for a plan
const isDesignTypeFullySelected = (planId, designType) => {
  const typeOptions = options.value[designType] || [];
  if (typeOptions.length === 0) return false;
  return typeOptions.every(opt => isAvailableForPlan(opt, planId));
};

// Toggle all items in a design type for a plan
const toggleDesignTypeForPlan = async (planId, designType, checked) => {
  const typeOptions = options.value[designType] || [];
  if (typeOptions.length === 0) return;
  
  try {
    console.log(`🔄 Toggling ${typeOptions.length} ${designType} options for ${planId}, checked=${checked}...`);
    
    const updatePromises = typeOptions.map(async (option) => {
      // If empty, initialize with all plans first
      let newPlans = option.available_plans?.length > 0 
        ? [...option.available_plans] 
        : ['basic', 'premium', 'business'];
      
      if (checked && !newPlans.includes(planId)) {
        newPlans.push(planId);
      } else if (!checked) {
        newPlans = newPlans.filter(p => p !== planId);
      }
      
      option.available_plans = newPlans;
      
      // Only send available_plans
      const updateData = { available_plans: newPlans };
      
      const response = await fetch(`${config.public.apiBaseUrl}/admin/profile-design-options/${option.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authStore.token}`,
        },
        body: JSON.stringify(updateData),
      });
      
      if (!response.ok) throw new Error(`Failed to update ${option.name}`);
      return response;
    });
    
    await Promise.allSettled(updatePromises);
    await fetchData();
    console.log(`✅ Successfully toggled all ${typeOptions.length} ${designType} options`);
  } catch (error) {
    console.error('❌ Error in toggleDesignTypeForPlan:', error);
    alert(`Failed to update: ${error.message}`);
  }
};

// Get icon for design type
const getDesignTypeIcon = (designType) => {
  const icons = {
    'theme': 'heroicons:paint-brush',
    'font': 'heroicons:language',
    'button_style': 'heroicons:cursor-arrow-rays',
    'color_scheme': 'heroicons:swatch',
    'layout': 'heroicons:squares-2x2',
    'feature_toggle': 'heroicons:sparkles'
  };
  return icons[designType] || 'heroicons:cog';
};

// Toggle all items in a section for a plan
const toggleSectionForPlan = async (planId, tabId, checked) => {
  const fieldTab = tabId.replace('field_', '');
  const tabFields = fields.value[fieldTab] || [];
  
  if (tabFields.length === 0) return;
  
  try {
    console.log(`🔄 Toggling ${tabFields.length} fields for ${planId}, checked=${checked}...`);
    
    // Update all fields in parallel (fast!)
    const updatePromises = tabFields.map(async (field) => {
      // If empty, initialize with all plans first
      let newPlans = field.available_plans?.length > 0 
        ? [...field.available_plans] 
        : ['basic', 'premium', 'business'];
      
      if (checked && !newPlans.includes(planId)) {
        newPlans.push(planId);
      } else if (!checked) {
        newPlans = newPlans.filter(p => p !== planId);
      }
      
      // Update field with new plans array
      field.available_plans = newPlans;
      
      // Only send the fields that backend expects
      const updateData = {
        available_plans: newPlans,
      };
      
      console.log(`📤 Sending PUT to /admin/profile-builder/fields/${field.id}`, updateData);
      const response = await fetch(`${config.public.apiBaseUrl}/admin/profile-builder/fields/${field.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authStore.token}`,
        },
        body: JSON.stringify(updateData),
      });
      
      console.log(`📥 Response status: ${response.status} ${response.statusText}`);
      
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        console.error(`❌ Field ${field.id} update failed:`, errorData);
        throw new Error(`Failed to update ${field.label}: ${errorData.message || response.statusText}`);
      }
      
      const result = await response.json();
      console.log(`✅ Field ${field.id} updated:`, result);
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
    
    console.log(`🔄 Toggling ${allOptions.length} design options for ${planId}, checked=${checked}...`);
    
    // Update all options in parallel (fast!)
    const updatePromises = allOptions.map(async (option) => {
      // If empty, initialize with all plans first
      let newPlans = option.available_plans?.length > 0 
        ? [...option.available_plans] 
        : ['basic', 'premium', 'business'];
      
      if (checked && !newPlans.includes(planId)) {
        newPlans.push(planId);
      } else if (!checked) {
        newPlans = newPlans.filter(p => p !== planId);
      }
      
      option.available_plans = newPlans;
      
      // Only send available_plans
      const updateData = { available_plans: newPlans };
      
      // Send API request
      let response = await fetch(`${config.public.apiBaseUrl}/admin/profile-design-options/${option.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authStore.token}`,
        },
        body: JSON.stringify(updateData),
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
        ? `${config.public.apiBaseUrl}/admin/profile-builder/fields`
        : `${config.public.apiBaseUrl}/admin/profile-builder/fields/${editingOption.value.id}`;
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
      ? `${config.public.apiBaseUrl}/admin/profile-builder/fields/${option.id}`
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
      `${config.public.apiBaseUrl}/admin/profile-builder/fields/${field.id}`,
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

// ========================================
// 🏢 BUSINESS USER CUSTOMIZATION FUNCTIONS
// ========================================

const loadBusinessUsers = async () => {
  if (businessUsersLoading.value) return;
  businessUsersLoading.value = true;
  try {
    console.log('🔍 Loading business users from:', `${config.public.apiBaseUrl}/admin/business-user-assignments`);
    console.log('🔑 Token:', authStore.token ? 'Present' : 'MISSING');
    
    const response = await fetch(`${config.public.apiBaseUrl}/admin/business-user-assignments`, {
      headers: { 
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    });
    
    console.log('📡 Response status:', response.status);
    
    if (!response.ok) {
      const errorText = await response.text();
      console.error('❌ API Error Response:', errorText);
      throw new Error(`HTTP ${response.status}: ${errorText}`);
    }
    
    const data = await response.json();
    console.log('✅ Business users loaded:', data);
    businessUsers.value = data.data || [];
  } catch (error) {
    console.error('❌ Error loading business users:', error);
    alert(`Failed to load business users: ${error.message}`);
  } finally {
    businessUsersLoading.value = false;
  }
};

const openBusinessUserModal = async (user) => {
  selectedBusinessUser.value = user;
  businessUsersLoading.value = true;
  try {
    console.log('🔓 Opening business user modal for:', user);
    const response = await fetch(`${config.public.apiBaseUrl}/admin/business-user-assignments/${user.id}`, {
      headers: { 
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
      },
    });
    
    console.log('📡 Response status:', response.status);
    
    if (!response.ok) {
      const errorText = await response.text();
      console.error('❌ API Error:', response.status, errorText);
      throw new Error(`HTTP ${response.status}: ${errorText}`);
    }
    
    const data = await response.json();
    console.log('✅ User assignment data:', data);
    
    if (!data.success || !data.data) {
      throw new Error(data.message || 'Invalid response format');
    }
    
    businessUserAvailableData.value = {
      fields: data.data.available?.fields || {},
      design_options: data.data.available?.design_options || {},
      defaults: data.data.defaults || { fields: [], design_options: [], features: [] },
    };
    
    const assignment = data.data.assignment || {};
    businessUserFormData.value = {
      use_custom: assignment.use_custom || false,
      enabled_fields: assignment.enabled_fields?.length ? assignment.enabled_fields : [...(data.data.defaults?.fields || [])],
      enabled_design_options: assignment.enabled_design_options?.length ? assignment.enabled_design_options : [...(data.data.defaults?.design_options || [])],
      enabled_features: assignment.enabled_features?.length ? assignment.enabled_features : [...(data.data.defaults?.features || [])],
      notes: assignment.notes || '',
    };
    
    showBusinessUserModal.value = true;
  } catch (error) {
    console.error('❌ Error loading user assignment:', error);
    alert(`Failed to load user data: ${error.message}`);
  } finally {
    businessUsersLoading.value = false;
  }
};

const closeBusinessUserModal = () => {
  showBusinessUserModal.value = false;
  selectedBusinessUser.value = null;
};

// Section toggle helpers for User Customization
const isTabFullySelected = (tabFields) => {
  if (!tabFields || tabFields.length === 0) return false;
  return tabFields.every(f => businessUserFormData.value.enabled_fields.includes(f.id));
};

const getTabSelectedCount = (tabFields) => {
  if (!tabFields) return 0;
  return tabFields.filter(f => businessUserFormData.value.enabled_fields.includes(f.id)).length;
};

const toggleTabFields = (tabFields, checked) => {
  if (!tabFields) return;
  const fieldIds = tabFields.map(f => f.id);
  if (checked) {
    // Add all fields from this tab
    fieldIds.forEach(id => {
      if (!businessUserFormData.value.enabled_fields.includes(id)) {
        businessUserFormData.value.enabled_fields.push(id);
      }
    });
  } else {
    // Remove all fields from this tab
    businessUserFormData.value.enabled_fields = businessUserFormData.value.enabled_fields.filter(
      id => !fieldIds.includes(id)
    );
  }
};

const isTypeFullySelected = (typeOptions) => {
  if (!typeOptions || typeOptions.length === 0) return false;
  return typeOptions.every(o => businessUserFormData.value.enabled_design_options.includes(o.id));
};

const getTypeSelectedCount = (typeOptions) => {
  if (!typeOptions) return 0;
  return typeOptions.filter(o => businessUserFormData.value.enabled_design_options.includes(o.id)).length;
};

const toggleTypeOptions = (typeOptions, checked) => {
  if (!typeOptions) return;
  const optionIds = typeOptions.map(o => o.id);
  if (checked) {
    // Add all options from this type
    optionIds.forEach(id => {
      if (!businessUserFormData.value.enabled_design_options.includes(id)) {
        businessUserFormData.value.enabled_design_options.push(id);
      }
    });
  } else {
    // Remove all options from this type
    businessUserFormData.value.enabled_design_options = businessUserFormData.value.enabled_design_options.filter(
      id => !optionIds.includes(id)
    );
  }
};

const saveBusinessUserAssignment = async () => {
  savingBusinessUser.value = true;
  try {
    const response = await fetch(`${config.public.apiBaseUrl}/admin/business-user-assignments/${selectedBusinessUser.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${authStore.token}`,
      },
      body: JSON.stringify(businessUserFormData.value),
    });
    
    if (!response.ok) throw new Error('Failed to save');
    
    await loadBusinessUsers();
    closeBusinessUserModal();
    alert('Settings saved successfully');
  } catch (error) {
    console.error('Error saving:', error);
    alert('Failed to save settings');
  } finally {
    savingBusinessUser.value = false;
  }
};

const resetBusinessUserToDefault = async () => {
  if (!confirm('Reset this user to default Business plan settings?')) return;
  
  savingBusinessUser.value = true;
  try {
    await fetch(`${config.public.apiBaseUrl}/admin/business-user-assignments/${selectedBusinessUser.value.id}/reset`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${authStore.token}` },
    });
    
    await loadBusinessUsers();
    closeBusinessUserModal();
    alert('Reset to default settings');
  } catch (error) {
    console.error('Error resetting:', error);
  } finally {
    savingBusinessUser.value = false;
  }
};

onMounted(async () => {
  console.log('🚀 Component mounted, loading sections and data...');
  await loadSections(); // Load dynamic sections first
  
  // After sections are loaded, add general tabs to expanded categories
  if (generalTabs.value && generalTabs.value.length > 0) {
    const generalTabIds = generalTabs.value.map(t => t.id);
    expandedCategories.value.business.unshift(...generalTabIds);
    expandedCategories.value.premium.unshift(...generalTabIds);
    expandedCategories.value.basic.unshift(...generalTabIds);
    console.log('✅ Expanded categories updated with general tabs');
  }
  
  fetchData();
});

definePageMeta({
  middleware: ['auth', 'admin'],
  layout: 'admin-management',
});
</script>