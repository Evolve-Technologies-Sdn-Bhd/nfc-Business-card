<template>
  <div>
    <AdminPageHeader
      eyebrow="Design System"
      eyebrow-icon="heroicons:swatch"
      title="Profile Builder Design"
      description="Configure plan entitlements, field visibility, and design system options across all three subscription tiers. Every change here instantly reflects in your users' Profile Builder experience."
    />

    <!-- Overview Stat Cards -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <AdminStatTile
        label="Active profile sections"
        :value="totalSections"
        icon="heroicons:rectangle-stack"
        hint="Sections"
      />
      <AdminStatTile
        label="Configurable input fields"
        :value="totalFields"
        icon="heroicons:queue-list"
        hint="Fields"
      />
      <AdminStatTile
        label="Themes, fonts & styles"
        :value="totalDesignOptions"
        icon="heroicons:paint-brush"
        hint="Designs"
      />
      <AdminStatTile
        label="Feature toggles available"
        :value="totalFeatures"
        icon="heroicons:rocket-launch"
        hint="Features"
      />
    </div>

    <!-- Plan Entitlement Snapshot -->
    <AdminSectionCard
      title="Plan entitlement snapshot"
      subtitle="Real-time breakdown of enabled items across each tier."
      class="mb-6"
    >
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div
          v-for="stat in planStatsList"
          :key="stat.id"
          :class="[
            'rounded-xl border-2 p-4 transition-all',
            stat.id === 'free' ? 'border-secondary-200 bg-secondary-50/40' :
            stat.id === 'basic' ? 'border-info-200 bg-info-50/40' :
            stat.id === 'premium' ? 'border-primary-200 bg-primary-50/40' :
            'border-warning-200 bg-warning-50/40'
          ]"
        >
          <div class="mb-3 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
              <div
                :class="[
                  'flex h-8 w-8 items-center justify-center rounded-lg text-white',
                  stat.id === 'free' ? 'bg-secondary-500' :
                  stat.id === 'basic' ? 'bg-info-500' :
                  stat.id === 'premium' ? 'bg-primary-500' :
                  'bg-warning-500'
                ]"
              >
                <Icon
                  :name="stat.id === 'business' ? 'heroicons:building-office' : stat.id === 'premium' ? 'heroicons:star' : 'heroicons:user'"
                  class="h-4 w-4"
                />
              </div>
              <span class="text-body font-semibold text-secondary-900">{{ stat.name }}</span>
            </div>
            <div
              :class="[
                'rounded-md px-2.5 py-1 text-body font-bold',
                stat.id === 'free' ? 'bg-secondary-500 text-white' :
                stat.id === 'basic' ? 'bg-info-500 text-white' :
                stat.id === 'premium' ? 'bg-primary-500 text-white' :
                'bg-warning-500 text-white'
              ]"
            >
              {{ stat.percentage }}%
            </div>
          </div>
          <div class="mb-3 h-2 w-full overflow-hidden rounded-full border border-white bg-white/60">
            <div
              :class="[
                'h-full rounded-full transition-all duration-700',
                stat.id === 'free' ? 'bg-secondary-500' :
                stat.id === 'basic' ? 'bg-info-500' :
                stat.id === 'premium' ? 'bg-primary-500' :
                'bg-warning-500'
              ]"
              :style="{ width: stat.percentage + '%' }"
            ></div>
          </div>
          <div class="grid grid-cols-3 gap-2 text-center">
            <div class="rounded-lg bg-white/70 px-1 py-1.5">
              <div class="text-caption font-bold text-secondary-900">{{ stat.fieldsCount }}</div>
              <div class="text-micro uppercase tracking-wide text-secondary-500">Fields</div>
            </div>
            <div class="rounded-lg bg-white/70 px-1 py-1.5">
              <div class="text-caption font-bold text-secondary-900">{{ stat.designsCount }}</div>
              <div class="text-micro uppercase tracking-wide text-secondary-500">Designs</div>
            </div>
            <div class="rounded-lg bg-white/70 px-1 py-1.5">
              <div class="text-caption font-bold text-secondary-900">{{ stat.featuresCount }}</div>
              <div class="text-micro uppercase tracking-wide text-secondary-500">Features</div>
            </div>
          </div>
        </div>
      </div>
    </AdminSectionCard>

    <!-- Workspace view switcher -->
    <div class="admin-toolbar mb-6">
      <div class="admin-scroll rounded-xl border border-secondary-200 bg-white p-1 shadow-sm">
        <div class="flex min-w-max gap-1">
          <button
            type="button"
            class="flex items-center gap-2 whitespace-nowrap rounded-lg px-4 py-2 text-navigation transition-all"
            :class="activeView === 'assignments' ? 'bg-primary-700 text-white shadow-sm' : 'text-secondary-600 hover:bg-secondary-100 hover:text-secondary-900'"
            @click="activeView = 'assignments'"
          >
            <Icon name="heroicons:clipboard-document-check" class="h-4 w-4 shrink-0" />
            Plan assignments
          </button>
          <button
            type="button"
            class="flex items-center gap-2 whitespace-nowrap rounded-lg px-4 py-2 text-navigation transition-all"
            :class="activeView === 'sections' ? 'bg-primary-700 text-white shadow-sm' : 'text-secondary-600 hover:bg-secondary-100 hover:text-secondary-900'"
            @click="activeView = 'sections'"
          >
            <Icon name="heroicons:rectangle-stack" class="h-4 w-4 shrink-0" />
            Field sections
          </button>
          <button
            type="button"
            class="flex items-center gap-2 whitespace-nowrap rounded-lg px-4 py-2 text-navigation transition-all"
            :class="activeView === 'catalog' ? 'bg-primary-700 text-white shadow-sm' : 'text-secondary-600 hover:bg-secondary-100 hover:text-secondary-900'"
            @click="activeView = 'catalog'"
          >
            <Icon name="heroicons:squares-2x2" class="h-4 w-4 shrink-0" />
            Design catalog
          </button>
        </div>
      </div>
    </div>

    <!-- Plan Assignments Workspace -->
    <AdminSectionCard
      v-show="activeView === 'assignments'"
      title="Plan field assignments"
      subtitle="Choose which fields, design options and features unlock for each subscription tier."
      class="mb-6"
    >
      <template #header-actions>
        <span class="admin-badge admin-badge--neutral">
          {{ generalTabs.length }} sections · {{ Object.values(fields).flat().length }} fields · {{ filteredOptionsFeatureToggles.length }} features
        </span>
      </template>

      <div class="space-y-3">
        <div
          v-for="plan in plans"
          :key="plan.id"
          :class="[
            'rounded-xl border-2 transition-all overflow-hidden',
            expandedPlans.includes(plan.id) && plan.id === 'free' && 'border-secondary-400 shadow-lg shadow-secondary-100',
            expandedPlans.includes(plan.id) && plan.id === 'business' && 'border-warning-400 shadow-lg shadow-warning-100',
            expandedPlans.includes(plan.id) && plan.id === 'premium' && 'border-primary-400 shadow-lg shadow-primary-100',
            expandedPlans.includes(plan.id) && plan.id === 'basic' && 'border-info-400 shadow-lg shadow-info-100',
            !expandedPlans.includes(plan.id) && 'border-secondary-200 hover:border-secondary-300'
          ]"
        >
          <button
            @click="togglePlan(plan.id)"
            :class="[
              'w-full flex items-center justify-between p-4 transition-all',
              expandedPlans.includes(plan.id) && plan.id === 'free' && 'bg-gradient-to-r from-secondary-50 to-secondary-100',
              expandedPlans.includes(plan.id) && plan.id === 'business' && 'bg-gradient-to-r from-warning-50 to-warning-100',
              expandedPlans.includes(plan.id) && plan.id === 'premium' && 'bg-gradient-to-r from-primary-50 to-primary-100',
              expandedPlans.includes(plan.id) && plan.id === 'basic' && 'bg-gradient-to-r from-info-50 to-info-100',
              !expandedPlans.includes(plan.id) && 'bg-white hover:bg-secondary-50'
            ]"
          >
            <div class="flex items-center gap-3">
              <div :class="[
                'w-10 h-10 rounded-lg flex items-center justify-center',
                plan.id === 'free' && 'bg-secondary-500 text-white',
                plan.id === 'business' && 'bg-warning-500 text-white',
                plan.id === 'premium' && 'bg-primary-500 text-white',
                plan.id === 'basic' && 'bg-info-500 text-white'
              ]">
                <Icon :name="plan.id === 'business' ? 'heroicons:building-office' : plan.id === 'premium' ? 'heroicons:star' : 'heroicons:user'" class="w-5 h-5" />
              </div>
              <div class="text-left">
                <span class="text-body font-semibold text-secondary-900">{{ plan.name }}</span>
                <div class="flex items-center gap-2 mt-0.5">
                  <span :class="[
                    'text-badge px-2 py-0.5 rounded-full',
                    plan.id === 'free' && 'bg-secondary-200 text-secondary-800',
                    plan.id === 'business' && 'bg-warning-200 text-warning-800',
                    plan.id === 'premium' && 'bg-primary-200 text-primary-800',
                    plan.id === 'basic' && 'bg-info-200 text-info-800'
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
                      'px-4 py-2 text-navigation border-b-2 transition-colors',
                      businessViewTab === 'defaults'
                        ? 'border-warning-500 text-warning-700'
                        : 'border-transparent text-secondary-500 hover:text-secondary-700'
                    ]"
                  >
                    <Icon name="heroicons:cog-6-tooth" class="w-4 h-4 inline mr-1" />
                    Default Settings
                  </button>
                  <button
                    @click="businessViewTab = 'users'; loadBusinessUsers()"
                    :class="[
                      'px-4 py-2 text-navigation border-b-2 transition-colors',
                      businessViewTab === 'users'
                        ? 'border-warning-500 text-warning-700'
                        : 'border-transparent text-secondary-500 hover:text-secondary-700'
                    ]"
                  >
                    <Icon name="heroicons:users" class="w-4 h-4 inline mr-1" />
                    User Customizations
                    <span v-if="businessUsers.filter(u => u.has_custom).length > 0" class="ml-1 px-1.5 py-0.5 text-badge bg-warning-200 text-warning-800 rounded-full">
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
                      class="w-full pl-9 pr-3 py-2 text-input border border-secondary-300 rounded-lg focus:ring-2 focus:ring-warning-500 focus:border-transparent"
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
                  <Icon name="heroicons:arrow-path" class="w-6 h-6 text-warning-500 animate-spin mx-auto" />
                </div>
                <div v-else-if="filteredBusinessUsers.length === 0" class="text-center py-8 text-secondary-500">
                  <Icon name="heroicons:users" class="w-8 h-8 mx-auto mb-2 text-secondary-300" />
                  <p>{{ businessUserSearch ? 'No matching users found' : 'No Business users found' }}</p>
                </div>
                <div v-else class="space-y-2 max-h-[300px] overflow-y-auto">
                  <div
                    v-for="user in filteredBusinessUsers"
                    :key="user.id"
                    class="flex items-center justify-between p-3 bg-white border border-secondary-200 rounded-lg hover:border-warning-300 transition-colors"
                  >
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-full bg-warning-100 flex items-center justify-center text-warning-700 font-medium text-body">
                        {{ user.name?.charAt(0) || '?' }}
                      </div>
                      <div>
                        <div class="text-body font-medium text-secondary-900">{{ user.name }}</div>
                        <div class="text-caption text-secondary-500">{{ user.email }}</div>
                      </div>
                    </div>
                    <div class="flex items-center gap-2">
                      <span v-if="user.has_custom" class="text-badge px-2 py-0.5 bg-warning-100 text-warning-700 rounded-full">Custom</span>
                      <span v-else class="text-badge px-2 py-0.5 bg-secondary-100 text-secondary-500 rounded-full">Default</span>
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
                  <h3 class="flex items-center border-b border-secondary-200 pb-2 text-body font-semibold text-secondary-900">
                    <Icon name="heroicons:rectangle-stack" class="w-4 h-4 mr-2 text-info-600" />
                    Data Fields
                    <span class="ml-auto text-caption font-normal text-secondary-500">{{ generalTabs.length }} sections</span>
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
                          <span class="text-navigation text-secondary-900">{{ tab.name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                          <span class="text-badge text-secondary-500 bg-white px-1.5 py-0.5 rounded">{{ getFieldTabItemCount(tab.id) }}</span>
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
                          class="flex items-center p-1.5 rounded hover:bg-secondary-50 cursor-pointer transition-all text-caption"
                        >
                          <input
                            type="checkbox"
                            :checked="isAvailableForPlan(field, plan.id)"
                            @change="toggleOptionForPlan(field, plan.id, $event.target.checked)"
                            class="w-3.5 h-3.5 rounded border-secondary-300 text-primary-600"
                          />
                          <span class="ml-2 text-secondary-700 truncate">{{ field.label }}</span>
                        </label>
                        <div v-if="(fields[tab.id.replace('field_', '')] || []).length === 0" class="text-caption text-secondary-400 italic p-2 text-center">
                          No fields yet
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Column 2: Design Options -->
                <div class="space-y-3">
                  <h3 class="flex items-center border-b border-secondary-200 pb-2 text-body font-semibold text-secondary-900">
                    <Icon name="heroicons:paint-brush" class="w-4 h-4 mr-2 text-primary-600" />
                    Design Options
                    <span class="ml-auto text-caption font-normal text-secondary-500">{{ getDesignCategoryItemCount(['theme', 'font', 'button_style', 'color_scheme', 'layout']) }} items</span>
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
                          <span class="text-navigation text-secondary-900">{{ getTabName(designType) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                          <span class="text-badge text-secondary-500 bg-white px-1.5 py-0.5 rounded">{{ (options[designType] || []).length }}</span>
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
                          class="flex items-center p-1.5 rounded hover:bg-secondary-50 cursor-pointer transition-all text-caption"
                        >
                          <input
                            type="checkbox"
                            :checked="isAvailableForPlan(option, plan.id)"
                            @change="toggleOptionForPlan(option, plan.id, $event.target.checked)"
                            class="w-3.5 h-3.5 rounded border-secondary-300 text-primary-600"
                          />
                          <span class="ml-2 text-secondary-700 truncate">{{ option.name }}</span>
                        </label>
                        <div v-if="(options[designType] || []).length === 0" class="text-caption text-secondary-400 italic p-2 text-center">
                          No options yet
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Column 3: Features -->
                <div class="space-y-3">
                  <h3 class="flex items-center border-b border-secondary-200 pb-2 text-body font-semibold text-secondary-900">
                    <Icon name="heroicons:sparkles" class="w-4 h-4 mr-2 text-warning-600" />
                    Features
                    <span class="ml-auto text-caption font-normal text-secondary-500">{{ filteredOptionsFeatureToggles.length }} toggles</span>
                  </h3>
                  <div class="space-y-2">
                    <div class="border border-secondary-200 rounded-lg p-3 bg-white">
                      <div class="flex items-center justify-between mb-3">
                        <span class="text-label text-secondary-700">Select All Features</span>
                        <input
                          type="checkbox"
                          :checked="isDesignTypeFullySelected(plan.id, 'feature_toggle')"
                          @change="toggleDesignTypeForPlan(plan.id, 'feature_toggle', $event.target.checked)"
                          class="w-4 h-4 rounded border-secondary-300 text-primary-600"
                        />
                      </div>
                      <div class="space-y-2">
                        <label
                          v-for="feature in filteredOptionsFeatureToggles"
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
                              'text-navigation',
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
                        <div v-if="filteredOptionsFeatureToggles.length === 0" class="text-caption text-secondary-400 italic p-4 text-center">
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
    </AdminSectionCard>

    <!-- Section Management -->
    <AdminSectionCard
      v-show="activeView === 'sections'"
      title="Manage field sections"
      subtitle="Create and organize the profile sections shown to your users."
      class="mb-6"
    >
      <template #header-actions>
        <span class="admin-badge admin-badge--neutral">{{ generalSections.length }} sections</span>
        <button
          type="button"
          class="admin-btn admin-btn--ghost admin-btn--icon"
          :disabled="sectionsLoading"
          title="Refresh sections"
          @click="loadSections"
        >
          <Icon name="heroicons:arrow-path" :class="['h-4 w-4', sectionsLoading && 'animate-spin']" />
        </button>
        <button
          type="button"
          class="admin-btn admin-btn--primary"
          @click="openSectionModal('add')"
        >
          <Icon name="heroicons:plus" class="h-4 w-4" />
          Add section
        </button>
      </template>

      <!-- Loading State -->
      <div v-if="sectionsLoading" class="flex items-center justify-center py-12">
        <div class="text-center">
          <Icon name="heroicons:arrow-path" class="w-8 h-8 text-primary-500 animate-spin mx-auto mb-3" />
          <p class="text-body text-secondary-500">Loading sections...</p>
        </div>
      </div>

      <!-- Sections Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <div
          v-for="(section, index) in generalSections"
          :key="section.id || section.key"
          :class="[
            'relative rounded-xl overflow-hidden transition-all duration-300 group',
            'bg-gradient-to-br from-white to-secondary-50',
            'border-2 hover:shadow-xl hover:-translate-y-1',
            section.is_active !== false ? 'border-secondary-200 hover:border-accent-400 hover:shadow-accent-100/40' : 'border-secondary-200 opacity-70 grayscale-[40%]'
          ]"
        >
          <!-- Accent Border -->
          <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-accent-300 via-accent-500 to-accent-400"></div>

          <!-- Status Badge -->
          <div class="absolute top-3 right-3 z-10 flex gap-1.5">
            <span
              v-if="section.is_active === false"
              class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-badge bg-secondary-200 text-secondary-600 border border-secondary-300"
            >
              <Icon name="heroicons:eye-slash" class="w-3 h-3" />
              Hidden
            </span>
          </div>

          <!-- Card Content -->
          <div class="p-5 pt-6 pb-6 flex flex-col">
            <!-- Header -->
            <div class="flex items-start gap-3 mb-4">
              <div 
                :class="[
                  'w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm',
                  'bg-gradient-to-br from-accent-50 to-accent-100 text-accent-600'
                ]"
              >
                <Icon 
                  :name="section.icon || 'heroicons:document-text'" 
                  class="w-6 h-6"
                />
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="text-body font-semibold text-secondary-900 truncate group-hover:text-primary-700 transition-colors">{{ section.name }}</h3>
                <code class="text-micro text-secondary-400 bg-secondary-100/70 px-1.5 py-0.5 rounded font-mono mt-1 inline-block border border-secondary-200/50">{{ section.key }}</code>
              </div>
            </div>

            <!-- Stats -->
            <div class="flex items-center justify-between mb-4 px-3 py-2.5 bg-white/60 rounded-lg border border-secondary-100">
              <div class="flex items-center gap-1.5 text-caption text-secondary-600">
                <Icon name="heroicons:queue-list" class="w-3.5 h-3.5 text-secondary-500" />
                <span class="font-bold text-secondary-900 text-body">{{ (fields[section.key] || []).length }}</span>
                <span class="text-secondary-500">fields</span>
              </div>
              <div class="flex items-center gap-1.5 text-caption text-secondary-600">
                <Icon name="heroicons:hashtag" class="w-3.5 h-3.5 text-secondary-500" />
                <span class="font-bold text-secondary-900">{{ section.display_order || index + 1 }}</span>
              </div>
            </div>

            <!-- Plan Progress -->
            <div class="mb-3">
              <div class="flex items-center justify-between mb-1.5">
                <span class="text-micro font-bold text-secondary-500 uppercase tracking-wider">Plan Coverage</span>
                <span class="text-micro font-bold text-secondary-700">{{ getSectionPlans(section.key).length }}/4 plans</span>
              </div>
              <div class="w-full h-1.5 bg-secondary-100 rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full transition-all duration-500 bg-gradient-to-r from-accent-400 to-accent-600"
                  :style="{ width: Math.round((getSectionPlans(section.key).length / 4) * 100) + '%' }"
                ></div>
              </div>
            </div>

            <!-- Plan Badges (Auto-calculated from fields) -->
            <div class="flex flex-wrap gap-1.5 mb-3">
              <span
                v-if="getSectionPlans(section.key).length === 0 || getSectionPlans(section.key).length === 4"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-badge bg-success-50 text-success-700 border border-success-200"
              >
                <Icon name="heroicons:check-circle-solid" class="w-3 h-3" />
                All Plans
              </span>
              <template v-else>
                <span
                  v-for="plan in getSectionPlans(section.key)"
                  :key="plan"
                  :class="[
                    'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-badge border',
                    plan === 'free' ? 'bg-secondary-50 text-secondary-700 border-secondary-200' :
                    plan === 'basic' ? 'bg-info-50 text-info-700 border-info-200' :
                    plan === 'premium' ? 'bg-primary-50 text-primary-700 border-primary-200' :
                    'bg-warning-50 text-warning-700 border-warning-200'
                  ]"
                >
                  <div :class="[
                    'w-1.5 h-1.5 rounded-full',
                    plan === 'free' ? 'bg-secondary-500' :
                    plan === 'basic' ? 'bg-info-500' :
                    plan === 'premium' ? 'bg-primary-500' :
                    'bg-warning-500'
                  ]"></div>
                  {{ plan.charAt(0).toUpperCase() + plan.slice(1) }}
                </span>
              </template>
            </div>

            <!-- Description (equal height with spacer) -->
            <p v-if="section.description" class="text-caption text-secondary-500 line-clamp-2 mb-4 leading-relaxed flex-1">
              {{ section.description }}
            </p>
            <div v-else class="mb-4 flex-1 min-h-[2.5rem]"></div>

            <!-- Actions (pinned to bottom) -->
            <div class="flex items-center gap-2 pt-3 border-t border-secondary-200 mt-auto">
              <button
                @click="openSectionModal('edit', section)"
                class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-button text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition-all border border-primary-100 hover:border-primary-200 group/edit"
              >
                <Icon name="heroicons:pencil" class="w-3.5 h-3.5 group-hover/edit:scale-110 transition-transform" />
                Edit
              </button>
              <button
                @click="confirmDeleteSection(section)"
                class="p-2 text-error-600 hover:bg-error-50 rounded-lg transition-all border border-transparent hover:border-error-200 group/del"
                title="Delete Section"
              >
                <Icon name="heroicons:trash" class="w-4 h-4 group-hover/del:scale-110 transition-transform" />
              </button>
            </div>
          </div>
        </div>

        <!-- Add New Section Card -->
        <button
          @click="openSectionModal('add')"
          class="relative rounded-xl border-2 border-dashed border-secondary-300 hover:border-primary-400 bg-gradient-to-br from-secondary-50/50 to-white hover:bg-primary-50/30 transition-all duration-300 min-h-[300px] flex flex-col items-center justify-center gap-4 group cursor-pointer hover:-translate-y-1 hover:shadow-xl hover:shadow-primary-100/30"
        >
          <div class="w-16 h-16 rounded-2xl bg-white border-2 border-dashed border-secondary-300 group-hover:border-primary-400 group-hover:bg-primary-50 flex items-center justify-center transition-all group-hover:scale-110">
            <Icon name="heroicons:plus" class="w-8 h-8 text-secondary-400 group-hover:text-primary-600 transition-colors" />
          </div>
          <div class="text-center px-4">
            <p class="text-body font-semibold text-secondary-700 group-hover:text-primary-700 transition-colors mb-1">Create New Section</p>
            <p class="text-caption text-secondary-400 group-hover:text-secondary-500">Add a custom profile category</p>
          </div>
        </button>

        <!-- Empty State (when no sections at all) -->
        <div
          v-if="generalSections.length === 0 && !sectionsLoading"
          class="col-span-full text-center py-20 bg-gradient-to-br from-secondary-50 to-white rounded-2xl border-2 border-dashed border-secondary-200"
        >
          <div class="w-24 h-24 bg-primary-50/50 rounded-3xl flex items-center justify-center mx-auto mb-6">
            <Icon name="heroicons:folder-open" class="w-12 h-12 text-primary-400" />
          </div>
          <h3 class="text-body font-semibold text-secondary-800 mb-2">Welcome to Profile Sections</h3>
          <p class="text-body text-secondary-500 mb-6 max-w-md mx-auto leading-relaxed">
            Organize your user profile into logical sections like Personal Info, Company, Services, and more. 
            Each section can contain multiple fields and be restricted by subscription plan.
          </p>
          <button
            type="button"
            class="admin-btn admin-btn--primary"
            @click="openSectionModal('add')"
          >
            <Icon name="heroicons:plus" class="w-4 h-4 mr-2" />
            Create Your First Section
          </button>
        </div>
      </div>
    </AdminSectionCard>

    <!-- Catalog intro -->
    <div v-show="activeView === 'catalog'" class="mb-5">
      <h2 class="text-lg font-semibold tracking-tight text-secondary-900">Design &amp; field catalog</h2>
      <p class="mt-1 text-body text-secondary-600">Master catalog: view, edit, and manage every configurable option across the system. Use the plan filter to quickly audit what each tier gets.</p>
    </div>

    <!-- Catalog content -->
    <AdminSectionCard v-show="activeView === 'catalog'" flush>
      <!-- Tabs Navigation -->
      <div class="border-b border-secondary-200">
        <nav 
          class="-mb-px flex gap-1 overflow-x-auto overflow-y-hidden px-6 scroll-smooth [scrollbar-width:thin] [&::-webkit-scrollbar]:h-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-[color:var(--brass)] [&::-webkit-scrollbar-thumb]:rounded-full" 
          aria-label="Tabs"
        >
          <button
            v-for="tab in allTabs"
            :key="tab.id"
            @click="currentTab = tab.id"
            :class="[
              currentTab === tab.id
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-secondary-500 hover:text-secondary-700 hover:border-secondary-300',
              'group inline-flex items-center py-4 px-3 border-b-2 text-navigation whitespace-nowrap transition-colors flex-shrink-0'
            ]"
          >
            <Icon :name="tab.icon" class="w-4 h-4 mr-1.5 flex-shrink-0" />
            {{ tab.name }}
            <span 
              :class="[
                currentTab === tab.id ? 'bg-primary-100 text-primary-600' : 'bg-secondary-100 text-secondary-600',
                'ml-2 py-0.5 px-2 rounded-full text-badge flex-shrink-0'
              ]"
            >
              {{ getFilteredOptionCount(tab.id) }}
            </span>
          </button>
        </nav>
      </div>

      <div class="admin-panel-body">
        <!-- Action Bar -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6 pb-4 border-b border-secondary-200">
          <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <!-- Plan Filter Chips -->
            <div class="flex items-center gap-1.5 bg-secondary-50 p-1.5 rounded-xl">
              <span class="text-caption font-semibold text-secondary-600 px-2">Plan:</span>
              <button
                @click="selectedPlanFilter = null"
                :class="[
                  'px-3 py-1.5 text-navigation rounded-lg transition-all',
                  selectedPlanFilter === null
                    ? 'bg-white text-secondary-900 shadow-sm shadow-secondary-200'
                    : 'text-secondary-500 hover:text-secondary-700 hover:bg-white/50'
                ]"
              >
                All
              </button>
              <button
                @click="selectedPlanFilter = 'basic'"
                :class="[
                  'px-3 py-1.5 text-navigation rounded-lg transition-all flex items-center gap-1.5',
                  selectedPlanFilter === 'basic'
                    ? 'bg-info-500 text-white shadow-sm shadow-info-200'
                    : 'text-secondary-500 hover:text-info-700 hover:bg-info-50'
                ]"
              >
                <div class="w-2 h-2 rounded-full" :class="selectedPlanFilter === 'basic' ? 'bg-white' : 'bg-info-500'"></div>
                Basic
              </button>
              <button
                @click="selectedPlanFilter = 'premium'"
                :class="[
                  'px-3 py-1.5 text-navigation rounded-lg transition-all flex items-center gap-1.5',
                  selectedPlanFilter === 'premium'
                    ? 'bg-primary-500 text-white shadow-sm shadow-primary-200'
                    : 'text-secondary-500 hover:text-primary-700 hover:bg-primary-50'
                ]"
              >
                <div class="w-2 h-2 rounded-full" :class="selectedPlanFilter === 'premium' ? 'bg-white' : 'bg-primary-500'"></div>
                Premium
              </button>
              <button
                @click="selectedPlanFilter = 'business'"
                :class="[
                  'px-3 py-1.5 text-navigation rounded-lg transition-all flex items-center gap-1.5',
                  selectedPlanFilter === 'business'
                    ? 'bg-warning-500 text-white shadow-sm shadow-warning-200'
                    : 'text-secondary-500 hover:text-warning-700 hover:bg-warning-50'
                ]"
              >
                <div class="w-2 h-2 rounded-full" :class="selectedPlanFilter === 'business' ? 'bg-white' : 'bg-warning-500'"></div>
                Business
              </button>
              <button
                @click="selectedPlanFilter = 'free'"
                :class="[
                  'px-3 py-1.5 text-navigation rounded-lg transition-all flex items-center gap-1.5',
                  selectedPlanFilter === 'free'
                    ? 'bg-secondary-500 text-white shadow-sm shadow-secondary-200'
                    : 'text-secondary-500 hover:text-secondary-700 hover:bg-secondary-50'
                ]"
              >
                <div class="w-2 h-2 rounded-full" :class="selectedPlanFilter === 'free' ? 'bg-white' : 'bg-secondary-500'"></div>
                Free
              </button>
            </div>

            <span class="text-body text-secondary-900">
              {{ isFieldTab ? filteredFieldsTable.length : getFilteredOptions.length }} {{ currentTabName }}{{ (isFieldTab ? filteredFieldsTable.length : getFilteredOptions.length) !== 1 ? 's' : '' }}
            </span>
            <span v-if="selectedPlanFilter" class="text-badge text-secondary-500 bg-secondary-100 px-2.5 py-1 rounded-full">
              Showing for {{ selectedPlanFilter.charAt(0).toUpperCase() + selectedPlanFilter.slice(1) }}
            </span>
          </div>

          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            <!-- Search Input (Fields Only) -->
            <div v-if="isFieldTab" class="relative">
              <Icon name="heroicons:magnifying-glass" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-secondary-400" />
              <input
                v-model="fieldsSearchQuery"
                type="text"
                placeholder="Search fields..."
                class="w-full sm:w-64 pl-9 pr-9 py-2 text-input border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white"
              />
              <button
                v-if="fieldsSearchQuery"
                @click="fieldsSearchQuery = ''"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-secondary-400 hover:text-secondary-600"
              >
                <Icon name="heroicons:x-mark" class="w-4 h-4" />
              </button>
            </div>

            <button
              @click="openAddModal"
              class="admin-btn admin-btn--primary"
            >
              <Icon name="heroicons:plus" class="h-4 w-4 mr-1.5" />
              Add {{ currentTabName }}
            </button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-16">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary-600 border-t-transparent"></div>
          <p class="mt-3 text-body text-secondary-600">Loading options...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="(isFieldTab ? filteredFieldsTable.length : getFilteredOptions.length) === 0" class="text-center py-16">
          <div class="w-20 h-20 bg-secondary-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
            <Icon name="heroicons:inbox" class="w-10 h-10 text-secondary-300" />
          </div>
          <p class="text-body font-semibold text-secondary-900 mb-1">
            {{ selectedPlanFilter ? `No ${currentTabName.toLowerCase()} for ${selectedPlanFilter.charAt(0).toUpperCase() + selectedPlanFilter.slice(1)} plan` : fieldsSearchQuery ? 'No matching fields found' : `No ${currentTabName.toLowerCase()} yet` }}
          </p>
          <p class="text-body text-secondary-500 mb-6 max-w-md mx-auto leading-relaxed">
            {{ selectedPlanFilter ? 'Try selecting a different plan or adjust your search criteria' : fieldsSearchQuery ? 'Clear the search box or try different keywords' : `Get started by creating your first ${currentTabName.toLowerCase()} below` }}
          </p>
          <div v-if="fieldsSearchQuery" class="mb-4">
            <button
              @click="fieldsSearchQuery = ''"
              class="px-4 py-2 text-button text-secondary-700 bg-white border border-secondary-300 rounded-lg hover:bg-secondary-50 transition-colors"
            >
              <Icon name="heroicons:x-mark" class="w-4 h-4 inline mr-1.5" />
              Clear Search
            </button>
          </div>
          <button
            v-if="!fieldsSearchQuery"
            type="button"
            class="admin-btn admin-btn--primary"
            @click="openAddModal"
          >
            <Icon name="heroicons:plus" class="h-4 w-4 mr-1.5" />
            Add {{ currentTabName }}
          </button>
        </div>

        <!-- Fields Table View -->
        <div v-else-if="isFieldTab" class="overflow-hidden rounded-xl border border-secondary-200">
          <div class="admin-scroll max-h-[600px] overflow-y-auto">
            <table class="min-w-full divide-y divide-secondary-200">
              <thead class="sticky top-0 z-10">
                <tr class="bg-gradient-to-r from-secondary-50 to-white">
                  <th scope="col" class="px-4 py-3.5 text-left text-micro font-semibold uppercase tracking-wider text-secondary-500">
                    Field
                  </th>
                  <th scope="col" class="px-4 py-3.5 text-left text-micro font-semibold uppercase tracking-wider text-secondary-500">
                    Type
                  </th>
                  <th scope="col" class="px-4 py-3.5 text-left text-micro font-semibold uppercase tracking-wider text-secondary-500">
                    Group
                  </th>
                  <th scope="col" class="px-4 py-3.5 text-center text-micro font-semibold uppercase tracking-wider text-secondary-500">
                    Order
                  </th>
                  <th scope="col" class="px-4 py-3.5 text-center text-micro font-semibold uppercase tracking-wider text-secondary-500 w-[280px]">
                    Plan Availability
                  </th>
                  <th scope="col" class="px-4 py-3.5 text-center text-micro font-semibold uppercase tracking-wider text-secondary-500">
                    Status
                  </th>
                  <th scope="col" class="px-4 py-3.5 text-center text-micro font-semibold uppercase tracking-wider text-secondary-500">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-secondary-100">
                <tr v-for="field in filteredFieldsTable" :key="field.id" class="hover:bg-primary-50/30 transition-colors group">
                  <td class="px-4 py-4">
                    <div class="text-body font-semibold text-secondary-900 group-hover:text-primary-700 transition-colors">{{ field.label }}</div>
                    <div class="text-caption text-secondary-500 font-mono mt-0.5 flex items-center gap-1">
                      <Icon name="heroicons:hashtag" class="w-3 h-3 opacity-50" />
                      {{ field.field_key }}
                    </div>
                    <div v-if="field.placeholder" class="text-caption text-secondary-400 italic mt-1 pl-4 border-l-2 border-secondary-200">
                      "{{ field.placeholder }}"
                    </div>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-badge bg-info-50 text-info-700 border border-info-100">
                      <Icon name="heroicons:rectangle-group" class="w-3 h-3" />
                      {{ field.field_type }}
                    </span>
                  </td>
                  <td class="px-4 py-4">
                    <div v-if="field.field_group" class="flex items-center gap-1.5">
                      <Icon v-if="field.field_group_icon" :name="field.field_group_icon" class="w-4 h-4 text-primary-500" />
                      <span class="text-badge text-secondary-700 bg-primary-50 px-2 py-1 rounded-md">{{ field.field_group }}</span>
                      <span v-if="field.group_order" class="text-micro text-secondary-400">#{{ field.group_order }}</span>
                    </div>
                    <span v-else class="text-caption text-secondary-400 italic">— Ungrouped —</span>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap text-center">
                    <input
                      v-model.number="field.display_order"
                      type="number"
                      class="w-16 px-2 py-1.5 text-input text-center border border-secondary-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-white"
                      @change="updateField(field)"
                    />
                  </td>
                  <td class="px-4 py-4">
                    <div class="flex items-center justify-center gap-1.5">
                      <div 
                        :class="[
                          'px-3 py-1.5 rounded-md text-badge border-2 transition-all',
                          isAvailableForPlan(field, 'free')
                            ? 'bg-secondary-50 text-secondary-700 border-secondary-300 shadow-sm shadow-secondary-100'
                            : 'bg-secondary-50 text-secondary-400 border-secondary-200 line-through opacity-60'
                        ]"
                      >
                        F
                      </div>
                      <div 
                        :class="[
                          'px-3 py-1.5 rounded-md text-badge border-2 transition-all',
                          isAvailableForPlan(field, 'basic')
                            ? 'bg-info-50 text-info-700 border-info-300 shadow-sm shadow-info-100'
                            : 'bg-secondary-50 text-secondary-400 border-secondary-200 line-through opacity-60'
                        ]"
                      >
                        B
                      </div>
                      <div 
                        :class="[
                          'px-3 py-1.5 rounded-md text-badge border-2 transition-all',
                          isAvailableForPlan(field, 'premium')
                            ? 'bg-primary-50 text-primary-700 border-primary-300 shadow-sm shadow-primary-100'
                            : 'bg-secondary-50 text-secondary-400 border-secondary-200 line-through opacity-60'
                        ]"
                      >
                        P
                      </div>
                      <div 
                        :class="[
                          'px-3 py-1.5 rounded-md text-badge border-2 transition-all',
                          isAvailableForPlan(field, 'business')
                            ? 'bg-warning-50 text-warning-700 border-warning-300 shadow-sm shadow-warning-100'
                            : 'bg-secondary-50 text-secondary-400 border-secondary-200 line-through opacity-60'
                        ]"
                      >
                        Bz
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap text-center">
                    <div class="flex items-center justify-center gap-3">
                      <label class="flex items-center cursor-pointer group/req" title="Required field">
                        <input
                          type="checkbox"
                          v-model="field.is_required"
                          class="w-4 h-4 rounded border-secondary-300 text-error-600 focus:ring-error-500"
                          @change="updateField(field)"
                        />
                        <span :class="['ml-1.5 text-label transition-colors', field.is_required ? 'text-error-700' : 'text-secondary-500 group-hover/req:text-error-600']">Req</span>
                      </label>
                      <label class="flex items-center cursor-pointer group/vis" title="Visible in profile">
                        <input
                          type="checkbox"
                          v-model="field.is_visible"
                          class="w-4 h-4 rounded border-secondary-300 text-success-600 focus:ring-success-500"
                          @change="updateField(field)"
                        />
                        <span :class="['ml-1.5 text-label transition-colors', field.is_visible ? 'text-success-700' : 'text-secondary-500 group-hover/vis:text-success-600']">Vis</span>
                      </label>
                    </div>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap">
                    <div class="flex items-center justify-center gap-1">
                      <button
                        @click="openEditModal(field)"
                        class="p-2 text-primary-600 hover:text-primary-700 hover:bg-primary-50 rounded-lg transition-all"
                        title="Edit field"
                      >
                        <Icon name="heroicons:pencil" class="w-4 h-4" />
                      </button>
                      <button
                        @click="confirmDelete(field)"
                        class="p-2 text-error-600 hover:text-error-700 hover:bg-error-50 rounded-lg transition-all"
                        title="Delete field"
                      >
                        <Icon name="heroicons:trash" class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- Table Footer Summary -->
          <div class="px-4 py-3 bg-secondary-50 border-t border-secondary-200 flex items-center justify-between">
            <span class="text-caption font-medium text-secondary-600">
              Showing <span class="font-bold text-secondary-900">{{ filteredFieldsTable.length }}</span> of <span class="font-bold text-secondary-900">{{ getFilteredOptions.length }}</span> fields
            </span>
            <div class="flex items-center gap-3 text-caption text-secondary-500">
              <span class="flex items-center gap-1"><div class="w-2 h-2 rounded-full bg-info-500"></div> Basic</span>
              <span class="flex items-center gap-1"><div class="w-2 h-2 rounded-full bg-primary-500"></div> Premium</span>
              <span class="flex items-center gap-1"><div class="w-2 h-2 rounded-full bg-warning-500"></div> Business</span>
            </div>
          </div>
        </div>

        <!-- Design Options Grid View -->
        <div v-else class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-5">
          <div
            v-for="option in getFilteredOptions"
            :key="option.id"
            :class="[
              'group relative bg-white border-2 rounded-xl overflow-hidden transition-all duration-300',
              option.is_active 
                ? 'border-secondary-200 hover:border-primary-400 hover:shadow-xl hover:-translate-y-1 hover:shadow-primary-100/50' 
                : 'border-secondary-200 opacity-70 grayscale-[30%]'
            ]"
          >
            <!-- Plan Availability Header -->
            <div :class="[
              'px-4 py-2.5 border-b transition-all',
              option.is_active ? 'bg-gradient-to-r from-secondary-50 to-white' : 'bg-secondary-100'
            ]">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <div class="flex items-center gap-1">
                    <div 
                      :class="[
                        'w-2.5 h-2.5 rounded-full transition-all',
                        isAvailableForPlan(option, 'free') ? 'bg-secondary-500 shadow-sm shadow-secondary-200' : 'bg-secondary-300'
                      ]"
                      title="Free plan"
                    ></div>
                    <div 
                      :class="[
                        'w-2.5 h-2.5 rounded-full transition-all',
                        isAvailableForPlan(option, 'basic') ? 'bg-info-500 shadow-sm shadow-info-200' : 'bg-secondary-300'
                      ]"
                      title="Basic plan"
                    ></div>
                    <div 
                      :class="[
                        'w-2.5 h-2.5 rounded-full transition-all',
                        isAvailableForPlan(option, 'premium') ? 'bg-primary-500 shadow-sm shadow-primary-200' : 'bg-secondary-300'
                      ]"
                      title="Premium plan"
                    ></div>
                    <div 
                      :class="[
                        'w-2.5 h-2.5 rounded-full transition-all',
                        isAvailableForPlan(option, 'business') ? 'bg-warning-500 shadow-sm shadow-warning-200' : 'bg-secondary-300'
                      ]"
                      title="Business plan"
                    ></div>
                  </div>
                  <span class="text-caption font-medium text-secondary-500">
                    {{ getPlanNames(option).join(' + ') || 'All Plans' }}
                  </span>
                </div>
                <div class="flex items-center gap-1.5">
                  <span
                    v-if="option.is_default"
                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-badge bg-primary-100 text-primary-700 border border-primary-200"
                  >
                    <Icon name="heroicons:star-solid" class="w-3 h-3" />
                    Default
                  </span>
                  <span
                    :class="[
                      'inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-badge border',
                      option.is_active 
                        ? 'bg-success-50 text-success-700 border-success-200' 
                        : 'bg-secondary-100 text-secondary-600 border-secondary-200'
                    ]"
                  >
                    <Icon :name="option.is_active ? 'heroicons:check-circle-solid' : 'heroicons:eye-slash'" class="w-3 h-3" />
                    {{ option.is_active ? 'Active' : 'Hidden' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Content -->
            <div class="p-5">
              <!-- Preview Section -->
              <div class="mb-4 p-4 bg-gradient-to-br from-secondary-50/80 to-white rounded-xl border border-secondary-100 shadow-inner group-hover:shadow-md transition-all">
                <!-- Theme Preview -->
                <div v-if="currentTab === 'theme'" 
                  class="w-full h-24 rounded-lg shadow-sm ring-1 ring-black/5 group-hover:scale-[1.02] transition-transform origin-center overflow-hidden"
                  :class="option.config?.preview"
                  :style="option.config?.previewCss ? option.config.previewCss : { background: option.config?.bgGradient || option.config?.backgroundColor || '#ffffff' }"
                ></div>
                
                <!-- Font Preview -->
                <div v-else-if="currentTab === 'font'" class="text-center py-2">
                  <p :style="{ fontFamily: option.config?.family }" class="text-xl font-bold text-secondary-900 mb-1 leading-tight">
                    Quick Brown Fox
                  </p>
                  <p :style="{ fontFamily: option.config?.family }" class="text-caption text-secondary-500 italic">
                    0123456789 • Aa Bb Cc
                  </p>
                </div>
                
                <!-- Button Style Preview -->
                <div v-else-if="currentTab === 'button_style'" class="flex justify-center py-1">
                  <button
                    class="w-auto min-w-[180px] py-2.5 px-6 text-button font-semibold shadow-sm ring-1 ring-black/5"
                    :class="option.config?.class"
                    :style="option.config?.previewCss"
                    disabled
                  >
                    <Icon name="heroicons:bolt" class="w-4 h-4 inline mr-1.5" />
                    {{ option.name }}
                  </button>
                </div>
                
                <!-- Card Style Preview -->
                <div v-else-if="currentTab === 'card_style'" class="flex justify-center py-1">
                  <div
                    class="w-full h-20 rounded-2xl ring-1 ring-black/5"
                    :class="option.config?.class"
                    :style="option.config?.previewCss"
                  ></div>
                </div>
                
                <!-- Background Animation Preview -->
                <div v-else-if="currentTab === 'bg_animation'" class="flex justify-center py-1">
                  <div class="relative isolate w-full h-20 rounded-xl ring-1 ring-black/10 overflow-hidden bg-[#06090d]">
                    <div
                      class="absolute inset-0"
                      :style="option.config?.previewCss || ''"
                      aria-hidden="true"
                    ></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-[#06090d]/25 via-transparent to-[#06090d]/10" aria-hidden="true"></div>
                    <div v-if="option.option_id === 'particles'" class="pointer-events-none absolute inset-0 z-10" aria-hidden="true">
                      <span class="bg-effect-preview-particle absolute left-[62%] top-[18%] h-1.5 w-1.5 rounded-full bg-[#f7f5f0] shadow-[0_0_9px_3px_rgba(247,245,240,0.75)]"></span>
                      <span class="bg-effect-preview-particle absolute left-[75%] top-[72%] h-2 w-2 rounded-full bg-[#c2a068] shadow-[0_0_10px_3px_rgba(194,160,104,0.8)]"></span>
                      <span class="bg-effect-preview-particle absolute left-[87%] top-[22%] h-1.5 w-1.5 rounded-full bg-[#f7f5f0] shadow-[0_0_9px_3px_rgba(247,245,240,0.7)]"></span>
                    </div>
                    <div v-else-if="option.option_id === 'starfield'" class="pointer-events-none absolute inset-0 z-10" aria-hidden="true">
                      <span class="absolute left-[12%] top-[20%] h-1 w-1 rounded-full bg-[#f7f5f0] shadow-[0_0_6px_2px_rgba(247,245,240,0.75)]"></span>
                      <span class="absolute left-[28%] top-[74%] h-1.5 w-1.5 rounded-full bg-[#c2a068] shadow-[0_0_7px_2px_rgba(194,160,104,0.8)]"></span>
                      <span class="absolute left-[43%] top-[15%] h-1 w-1 rounded-full bg-[#f7f5f0] shadow-[0_0_6px_2px_rgba(247,245,240,0.75)]"></span>
                      <span class="absolute left-[59%] top-[78%] h-1 w-1 rounded-full bg-[#f7f5f0] shadow-[0_0_6px_2px_rgba(247,245,240,0.7)]"></span>
                      <span class="absolute left-[74%] top-[16%] h-1.5 w-1.5 rounded-full bg-[#c2a068] shadow-[0_0_7px_2px_rgba(194,160,104,0.8)]"></span>
                      <span class="absolute left-[91%] top-[68%] h-1 w-1 rounded-full bg-[#f7f5f0] shadow-[0_0_6px_2px_rgba(247,245,240,0.75)]"></span>
                    </div>
                    <svg v-else-if="option.option_id === 'slow-waves'" class="pointer-events-none absolute inset-0 z-10 h-full w-full" viewBox="0 0 320 80" preserveAspectRatio="none" aria-hidden="true">
                      <path d="M-10 18 C45 2 70 34 125 18 S205 2 260 18 S315 34 340 18" fill="none" stroke="#c2a068" stroke-opacity=".65" stroke-width="1.5" />
                      <path d="M-10 39 C45 23 70 55 125 39 S205 23 260 39 S315 55 340 39" fill="none" stroke="#f7f5f0" stroke-opacity=".32" stroke-width="1" />
                      <path d="M-10 61 C45 45 70 77 125 61 S205 45 260 61 S315 77 340 61" fill="none" stroke="#c2a068" stroke-opacity=".45" stroke-width="1.5" />
                    </svg>
                    <div v-else-if="option.option_id === 'sparkle'" class="pointer-events-none absolute inset-0 z-10 text-[#f7f5f0]" aria-hidden="true">
                      <Icon name="heroicons:sparkles" class="bg-effect-preview-sparkle absolute left-[18%] top-[18%] h-5 w-5 text-[#c2a068]" />
                      <Icon name="heroicons:sparkles" class="bg-effect-preview-sparkle absolute left-[44%] top-[62%] h-4 w-4" />
                      <Icon name="heroicons:sparkles" class="bg-effect-preview-sparkle absolute left-[72%] top-[14%] h-5 w-5 text-[#c2a068]" />
                      <Icon name="heroicons:sparkles" class="bg-effect-preview-sparkle absolute left-[88%] top-[66%] h-4 w-4" />
                    </div>
                    <div
                      v-else-if="option.option_id === 'grid-dots'"
                      class="pointer-events-none absolute inset-0 z-10"
                      :style="{ backgroundImage: 'radial-gradient(rgba(194,160,104,0.48) 1px, transparent 1.5px)', backgroundSize: '16px 16px' }"
                      aria-hidden="true"
                    ></div>
                    <div class="relative z-20 flex h-full items-center gap-3 px-4">
                      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-[#c2a068]/70 bg-[#c2a068]/15 text-[#c2a068] shadow-sm">
                        <Icon name="heroicons:user" class="h-5 w-5" />
                      </div>
                      <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-[#f7f5f0]">Alex Morgan</p>
                        <p class="truncate text-xs text-[#f7f5f0]/70">Product Designer</p>
                        <div class="mt-2 flex items-center gap-1.5" aria-hidden="true">
                          <span class="h-1 w-10 rounded-full bg-[#c2a068]/80"></span>
                          <span class="h-1 w-6 rounded-full bg-[#f7f5f0]/45"></span>
                          <span class="h-1 w-4 rounded-full bg-[#f7f5f0]/25"></span>
                        </div>
                      </div>
                      <span class="shrink-0 rounded-full border border-[#c2a068]/50 bg-[#06090d]/40 px-2.5 py-1 text-[10px] font-semibold tracking-wide text-[#c2a068]">CONNECT</span>
                    </div>
                  </div>
                </div>
                
                <!-- Profile Style Preview -->
                <div v-else-if="currentTab === 'profile_style'" class="flex justify-center py-2">
                  <div class="w-full max-w-[220px] bg-white rounded-xl border border-secondary-200 shadow-sm overflow-hidden">
                    <template v-if="option.config?.layout === 'cover'">
                      <div class="h-8 bg-gradient-to-r from-secondary-300 to-secondary-400"></div>
                      <div class="flex flex-col items-center px-3 pb-3 -mt-5">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-secondary-200 to-secondary-400 border-2 border-white shadow"></div>
                        <div class="mt-1.5 h-1.5 w-16 bg-secondary-300 rounded-full"></div>
                        <div class="mt-1 h-1.5 w-10 bg-secondary-200 rounded-full"></div>
                      </div>
                    </template>
                    <template v-else-if="option.config?.layout === 'horizontal'">
                      <div class="flex items-center gap-2.5 p-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-secondary-200 to-secondary-400 flex-shrink-0"></div>
                        <div class="flex flex-col gap-1 flex-1">
                          <div class="h-1.5 w-16 bg-secondary-300 rounded-full"></div>
                          <div class="h-1.5 w-10 bg-secondary-200 rounded-full"></div>
                        </div>
                      </div>
                    </template>
                    <template v-else>
                      <div class="flex flex-col items-center px-3 py-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-secondary-200 to-secondary-400"></div>
                        <div class="mt-1.5 h-1.5 w-16 bg-secondary-300 rounded-full"></div>
                        <div class="mt-1 h-1.5 w-10 bg-secondary-200 rounded-full"></div>
                      </div>
                    </template>
                  </div>
                </div>
                
                <!-- Color Scheme Preview -->
                <div v-else-if="currentTab === 'color_scheme'">
                  <div class="flex gap-2.5 mb-3">
                    <div 
                      :style="{ backgroundColor: option.config?.primary || '#000000' }" 
                      class="flex-1 h-14 rounded-xl shadow-inner ring-2 ring-white group-hover:scale-105 transition-transform"
                    ></div>
                    <div 
                      :style="{ backgroundColor: option.config?.secondary || '#666666' }" 
                      class="flex-1 h-14 rounded-xl shadow-inner ring-2 ring-white group-hover:scale-105 transition-transform"
                    ></div>
                    <div 
                      :style="{ backgroundColor: option.config?.accent || '#0066FF' }" 
                      class="flex-1 h-14 rounded-xl shadow-inner ring-2 ring-white group-hover:scale-105 transition-transform"
                    ></div>
                  </div>
                  <div class="flex gap-2.5 text-micro font-semibold text-center text-secondary-600 font-mono">
                    <span class="flex-1 bg-white px-1 py-0.5 rounded border border-secondary-200">{{ option.config?.primary || '—' }}</span>
                    <span class="flex-1 bg-white px-1 py-0.5 rounded border border-secondary-200">{{ option.config?.secondary || '—' }}</span>
                    <span class="flex-1 bg-white px-1 py-0.5 rounded border border-secondary-200">{{ option.config?.accent || '—' }}</span>
                  </div>
                </div>
                
                <!-- Layout Preview -->
                <div v-else-if="currentTab === 'layout'" class="py-1">
                  <div :class="[
                    'h-16 bg-white rounded-xl border-2 border-dashed border-secondary-300 flex items-center relative',
                    option.config?.alignment === 'left' ? 'justify-start pl-4' :
                    option.config?.alignment === 'right' ? 'justify-end pr-4' :
                    'justify-center'
                  ]">
                    <div class="flex flex-col gap-1.5" :style="{ maxWidth: option.config?.maxWidth || '60%' }" :class="[
                      'w-full',
                      option.config?.alignment === 'left' ? 'items-start' :
                      option.config?.alignment === 'right' ? 'items-end' :
                      'items-center'
                    ]">
                      <div class="h-2 w-3/4 bg-secondary-300 rounded-full"></div>
                      <div class="h-2 w-1/2 bg-secondary-200 rounded-full"></div>
                    </div>
                  </div>
                  <div class="mt-2 text-center text-micro font-semibold text-secondary-500 uppercase tracking-wider">
                    {{ option.config?.alignment }} • {{ option.config?.maxWidth || 'default' }}
                  </div>
                </div>
                
                <!-- Feature Toggle Preview -->
                <div v-else-if="currentTab === 'feature_toggle'" class="flex flex-col items-center justify-center py-2 gap-2">
                  <div :class="[
                    'w-16 h-8 rounded-full p-1 transition-all relative',
                    option.is_default ? 'bg-success-500' : 'bg-secondary-300'
                  ]">
                    <div :class="[
                      'absolute w-6 h-6 bg-white rounded-full shadow-md transition-all top-1',
                      option.is_default ? 'right-1' : 'left-1'
                    ]"></div>
                  </div>
                  <span :class="[
                    'text-caption font-bold uppercase tracking-wider',
                    option.is_default ? 'text-success-700' : 'text-secondary-500'
                  ]">
                    {{ option.is_default ? 'Enabled' : 'Disabled' }} by default
                  </span>
                </div>
              </div>

              <!-- Info -->
              <div class="mb-4">
                <h3 class="text-body font-semibold text-secondary-900 mb-1 group-hover:text-primary-700 transition-colors flex items-center gap-1.5">
                  {{ option.name }}
                  <Icon v-if="option.is_default" name="heroicons:star-solid" class="w-3.5 h-3.5 text-warning-500" />
                </h3>
                <p v-if="option.description" class="text-caption text-secondary-600 line-clamp-2 leading-relaxed">
                  {{ option.description }}
                </p>
                <code class="text-micro text-secondary-500 bg-secondary-50 px-2 py-1 rounded font-mono mt-2 inline-block border border-secondary-100">
                  {{ option.option_id }}
                </code>
              </div>

              <!-- Actions -->
              <div class="flex items-center gap-1.5 pt-4 border-t border-secondary-100">
                <button
                  v-if="!option.is_default"
                  @click="setAsDefault(option)"
                  class="flex-1 inline-flex items-center justify-center gap-1 px-2 py-2 text-button text-secondary-700 bg-white border border-secondary-200 rounded-lg hover:bg-warning-50 hover:border-warning-300 hover:text-warning-700 transition-all group/star"
                  title="Set as default"
                >
                  <Icon name="heroicons:star" class="w-3.5 h-3.5 group-hover/star:text-warning-500" />
                </button>
                <button
                  @click="toggleActive(option)"
                  :class="[
                    'flex-1 inline-flex items-center justify-center gap-1 px-2 py-2 text-button border rounded-lg transition-all',
                    option.is_active
                      ? 'text-error-700 bg-white border-error-200 hover:bg-error-50'
                      : 'text-success-700 bg-white border-success-200 hover:bg-success-50'
                  ]"
                >
                  <Icon :name="option.is_active ? 'heroicons:eye-slash' : 'heroicons:eye'" class="w-3.5 h-3.5" />
                </button>
                <button
                  @click="openEditModal(option)"
                  class="p-2 text-primary-600 hover:bg-primary-50 border border-secondary-200 hover:border-primary-300 rounded-lg transition-all"
                  title="Edit option"
                >
                  <Icon name="heroicons:pencil" class="w-4 h-4" />
                </button>
                <button
                  @click="confirmDelete(option)"
                  class="p-2 text-error-600 hover:bg-error-50 border border-secondary-200 hover:border-error-300 rounded-lg transition-all"
                  title="Delete option"
                >
                  <Icon name="heroicons:trash" class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </AdminSectionCard>

    <!-- Add/Edit Modal -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div v-if="showModal" class="admin-dialog-backdrop" @click.self="closeModal">
          <div class="admin-dialog flex flex-col" style="width: min(46rem, 100%); margin-top: 5vh; max-height: 90vh" role="dialog" aria-modal="true">
            <!-- Modal Header -->
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-accent-100 text-accent-700">
                  <Icon :name="isFieldTab ? 'heroicons:queue-list' : 'heroicons:paint-brush'" class="h-5 w-5" />
                </span>
                <div class="min-w-0">
                  <p class="admin-panel-title">{{ modalMode === 'add' ? 'Add' : 'Edit' }} {{ currentTabName }}</p>
                  <p class="admin-panel-subtitle">Configure this {{ isFieldTab ? 'field' : 'design option' }} and its plan availability.</p>
                </div>
              </div>
              <button type="button" class="admin-icon-btn" aria-label="Close" @click="closeModal">
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>

            <!-- Modal Body -->
            <div class="admin-panel-body flex-1 overflow-y-auto">
              <div class="space-y-6">
                    <!-- FIELDS FORM -->
                    <template v-if="isFieldTab">
                      <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                          <label class="block text-label text-secondary-900 mb-2">
                            Field Key <span class="text-error-600">*</span>
                          </label>
                          <input
                            v-model="formData.field_key"
                            type="text"
                            :disabled="modalMode === 'edit'"
                            class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 disabled:bg-secondary-100"
                            placeholder="e.g., name, email, bio"
                          />
                        </div>

                        <div>
                          <label class="block text-label text-secondary-900 mb-2">Label <span class="text-error-600">*</span></label>
                          <input
                            v-model="formData.label"
                            type="text"
                            class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                            placeholder="Full Name"
                          />
                        </div>

                        <div>
                          <label class="block text-label text-secondary-900 mb-2">Field Type <span class="text-error-600">*</span></label>
                          <select
                            v-model="formData.field_type"
                            class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"
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
                          <label class="block text-label text-secondary-900 mb-2">Placeholder</label>
                          <input
                            v-model="formData.placeholder"
                            type="text"
                            class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                            :placeholder="getFieldPlaceholderHint(formData.field_type)"
                          />
                        </div>

                        <div>
                          <label class="block text-label text-secondary-900 mb-2">Help Text</label>
                          <input
                            v-model="formData.help_text"
                            type="text"
                            class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                            placeholder="Optional"
                          />
                        </div>
                      </div>

                      <!-- Field Grouping -->
                      <div class="bg-primary-50 rounded-lg p-4 border border-primary-200">
                        <h4 class="admin-form-section-title mb-3 flex items-center">
                          <Icon name="heroicons:rectangle-group" class="w-4 h-4 mr-2 text-primary-600" />
                          Field Grouping (for UI display)
                        </h4>
                        <div class="grid grid-cols-3 gap-4">
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Group Name</label>
                            <input
                              v-model="formData.field_group"
                              type="text"
                              class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg"
                              placeholder="e.g., Basic Information"
                            />
                          </div>
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Group Icon</label>
                            <select
                              v-model="formData.field_group_icon"
                              class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg"
                            >
                              <option value="">Select icon...</option>
                              <option value="heroicons:user-circle">User Circle</option>
                              <option value="heroicons:document-text">Document</option>
                              <option value="heroicons:phone">Phone</option>
                              <option value="heroicons:academic-cap">Academic</option>
                              <option value="heroicons:identification">Identification</option>
                              <option value="heroicons:information-circle">Information</option>
                              <option value="heroicons:star">Star</option>
                              <option value="heroicons:map-pin">Map Pin</option>
                              <option value="heroicons:clock">Clock</option>
                              <option value="heroicons:trophy">Trophy</option>
                              <option value="heroicons:user-group">User Group</option>
                              <option value="heroicons:rocket-launch">Rocket</option>
                              <option value="heroicons:photo">Photo</option>
                              <option value="heroicons:currency-dollar">Currency</option>
                              <option value="heroicons:pencil-square">Pencil</option>
                              <option value="heroicons:calendar">Calendar</option>
                              <option value="heroicons:clipboard-document-list">Clipboard</option>
                              <option value="heroicons:folder">Folder</option>
                              <option value="heroicons:squares-plus">Squares Plus</option>
                            </select>
                          </div>
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Group Order</label>
                            <input
                              v-model.number="formData.group_order"
                              type="number"
                              class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg"
                              placeholder="1"
                              min="1"
                            />
                          </div>
                        </div>
                        <p class="text-helper mt-2">Fields with the same group name will be displayed together in Profile Builder</p>
                      </div>

                      <!-- Repeater Configuration -->
                      <div v-if="formData.field_type === 'repeater'" class="bg-info-50 rounded-lg p-4 border border-info-200">
                        <h4 class="admin-form-section-title mb-3 flex items-center">
                          <Icon name="heroicons:queue-list" class="w-4 h-4 mr-2" />
                          Repeater Configuration
                        </h4>
                        <div class="space-y-3">
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Max Items</label>
                            <input
                              v-model.number="formData.config.max_items"
                              type="number"
                              class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg"
                              placeholder="e.g., 3 or 6"
                              min="1"
                            />
                          </div>
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Sub-fields (JSON array)</label>
                            <textarea
                              v-model="formData.config.sub_fields"
                              rows="4"
                              class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg font-mono resize-none"
                              placeholder='[{"key": "num", "label": "Number", "type": "text"}, {"key": "label", "label": "Label", "type": "text"}]'
                            ></textarea>
                            <p class="text-helper mt-1">Define sub-fields as JSON array</p>
                          </div>
                        </div>
                      </div>

                      <div class="bg-secondary-50 rounded-lg p-4 border border-secondary-200">
                        <h4 class="admin-form-section-title mb-3">Validation</h4>
                        <div class="grid grid-cols-2 gap-4">
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Min Length</label>
                            <input
                              v-model.number="formData.validation_rules.min"
                              type="number"
                              class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg"
                              placeholder="0"
                            />
                          </div>
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Max Length</label>
                            <input
                              v-model.number="formData.validation_rules.max"
                              type="number"
                              class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg"
                              placeholder="255"
                            />
                          </div>
                        </div>
                      </div>
                    </template>

                    <!-- DESIGN OPTIONS FORM -->
                    <template v-else>
                      <div v-if="modalMode === 'add'">
                        <label class="block text-label text-secondary-900 mb-2">
                          Option ID <span class="text-error-600">*</span>
                        </label>
                        <input
                          v-model="formData.option_id"
                          type="text"
                          class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 font-mono"
                          placeholder="e.g., minimal, inter, solid"
                        />
                      </div>

                      <div>
                        <label class="block text-label text-secondary-900 mb-2">
                          Display Name <span class="text-error-600">*</span>
                        </label>
                        <input
                          v-model="formData.name"
                          type="text"
                          class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                          placeholder="e.g., Minimal, Inter, Solid"
                        />
                      </div>

                      <div>
                        <label class="block text-label text-secondary-900 mb-2">Description</label>
                        <textarea
                          v-model="formData.description"
                          rows="2"
                          class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 resize-none"
                          placeholder="Brief description..."
                        ></textarea>
                      </div>

                      <!-- Type-specific Configuration -->
                      <div class="bg-secondary-50 rounded-lg p-4 border border-secondary-200">
                        <h4 class="admin-form-section-title mb-3">Configuration</h4>
                        
                        <div v-if="currentTab === 'theme'" class="space-y-3">
                          <div class="grid grid-cols-2 gap-3">
                            <div>
                              <label class="block text-label text-secondary-700 mb-1.5">Background Color</label>
                              <div class="flex gap-2">
                                <input v-model="formData.config.backgroundColor" type="color" class="w-11 h-10 rounded border-2 border-secondary-300 cursor-pointer" />
                                <input v-model="formData.config.backgroundColor" type="text" class="flex-1 px-3 py-2 text-input border border-secondary-300 rounded-lg font-mono" placeholder="#FFFFFF" />
                              </div>
                            </div>
                            <div>
                              <label class="block text-label text-secondary-700 mb-1.5">Text Color</label>
                              <div class="flex gap-2">
                                <input v-model="formData.config.textColor" type="color" class="w-11 h-10 rounded border-2 border-secondary-300 cursor-pointer" />
                                <input v-model="formData.config.textColor" type="text" class="flex-1 px-3 py-2 text-input border border-secondary-300 rounded-lg font-mono" placeholder="#111827" />
                              </div>
                            </div>
                            <div>
                              <label class="block text-label text-secondary-700 mb-1.5">Accent Color</label>
                              <div class="flex gap-2">
                                <input v-model="formData.config.accentColor" type="color" class="w-11 h-10 rounded border-2 border-secondary-300 cursor-pointer" />
                                <input v-model="formData.config.accentColor" type="text" class="flex-1 px-3 py-2 text-input border border-secondary-300 rounded-lg font-mono" placeholder="#2563EB" />
                              </div>
                            </div>
                          </div>
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Gradient Override (optional)</label>
                            <input
                              v-model="formData.config.bgGradient"
                              type="text"
                              class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg font-mono"
                              placeholder="linear-gradient(135deg, #F9FAFB 0%, #E5E7EB 100%)"
                              @input="syncThemePreviewCss"
                            />
                          </div>
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Preview CSS (auto-generated)</label>
                            <textarea
                              v-model="formData.config.previewCss"
                              rows="2"
                              class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg font-mono resize-none bg-secondary-50"
                              placeholder="background: ..."
                            ></textarea>
                            <button type="button" @click="syncThemePreviewCss" class="mt-1.5 text-caption font-semibold text-primary-600 hover:text-primary-700">↻ Regenerate from colors</button>
                          </div>
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Fallback Tailwind Class</label>
                            <input
                              v-model="formData.config.preview"
                              type="text"
                              class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg font-mono"
                              placeholder="bg-white"
                            />
                          </div>
                          <div class="rounded-lg border border-secondary-200 p-3">
                            <p class="text-micro font-semibold text-secondary-500 uppercase tracking-wider mb-2">Live Preview</p>
                            <div
                              class="w-full h-16 rounded-lg ring-1 ring-black/5"
                              :style="formData.config.previewCss ? formData.config.previewCss : { background: formData.config.bgGradient || formData.config.backgroundColor || '#ffffff' }"
                            ></div>
                          </div>
                        </div>

                        <div v-if="currentTab === 'font'">
                          <label class="block text-label text-secondary-700 mb-1.5">Font Family</label>
                          <input
                            v-model="formData.config.family"
                            type="text"
                            class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg"
                            placeholder="Inter, sans-serif"
                          />
                        </div>

                        <div v-if="currentTab === 'button_style'" class="space-y-3">
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">CSS Classes (applied in profile builder)</label>
                            <textarea
                              v-model="formData.config.class"
                              rows="2"
                              class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg font-mono resize-none"
                              placeholder="bg-black text-white rounded-full"
                            ></textarea>
                          </div>
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Preview CSS (inline style for admin preview)</label>
                            <textarea
                              v-model="formData.config.previewCss"
                              rows="2"
                              class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg font-mono resize-none"
                              placeholder="background:#0F172A;color:#FFFFFF;border-radius:9999px;"
                            ></textarea>
                          </div>
                        </div>

                        <div v-if="currentTab === 'card_style'" class="space-y-3">
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">CSS Classes (applied in profile builder)</label>
                            <textarea
                              v-model="formData.config.class"
                              rows="2"
                              class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg font-mono resize-none"
                              placeholder="bg-white/90 backdrop-blur-sm rounded-2xl border"
                            ></textarea>
                          </div>
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Preview CSS (inline style)</label>
                            <textarea
                              v-model="formData.config.previewCss"
                              rows="3"
                              class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg font-mono resize-none"
                              placeholder="background:rgba(255,255,255,0.9);border-radius:16px;..."
                            ></textarea>
                          </div>
                        </div>

                        <div v-if="currentTab === 'bg_animation'" class="space-y-3">
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Animation Component</label>
                            <input
                              v-model="formData.config.component"
                              type="text"
                              class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg font-mono"
                              placeholder="BgAurora"
                            />
                            <p class="text-micro text-secondary-500 mt-1">Component rendered in the profile builder. Leave empty for "none".</p>
                          </div>
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Preview CSS (inline style for static preview)</label>
                            <textarea
                              v-model="formData.config.previewCss"
                              rows="3"
                              class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg font-mono resize-none"
                              placeholder="background: radial-gradient(...);"
                            ></textarea>
                          </div>
                        </div>

                        <div v-if="currentTab === 'profile_style'" class="space-y-3">
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Avatar Position</label>
                            <select v-model="formData.config.avatarPosition" class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg">
                              <option value="center">Center</option>
                              <option value="left">Left</option>
                              <option value="right">Right</option>
                            </select>
                          </div>
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Layout Variant</label>
                            <select v-model="formData.config.layout" class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg">
                              <option value="vertical">Vertical Stack</option>
                              <option value="horizontal">Horizontal (Side Avatar)</option>
                              <option value="cover">Cover + Profile</option>
                            </select>
                          </div>
                          <label class="flex items-center p-2 bg-white rounded border border-secondary-300 cursor-pointer">
                            <input v-model="formData.config.requireCoverImage" type="checkbox" class="w-4 h-4 rounded border-secondary-300 text-primary-600" />
                            <span class="ml-2 text-body text-secondary-900">Requires cover image</span>
                          </label>
                        </div>

                        <div v-if="currentTab === 'color_scheme'" class="space-y-3">
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Primary</label>
                            <div class="flex gap-2">
                              <input v-model="formData.config.primary" type="color" class="w-12 h-10 rounded border-2 border-secondary-300" />
                              <input v-model="formData.config.primary" type="text" class="flex-1 px-3 py-2 text-input border border-secondary-300 rounded-lg font-mono" placeholder="#000000" />
                            </div>
                          </div>
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Secondary</label>
                            <div class="flex gap-2">
                              <input v-model="formData.config.secondary" type="color" class="w-12 h-10 rounded border-2 border-secondary-300" />
                              <input v-model="formData.config.secondary" type="text" class="flex-1 px-3 py-2 text-input border border-secondary-300 rounded-lg font-mono" placeholder="#666666" />
                            </div>
                          </div>
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Accent</label>
                            <div class="flex gap-2">
                              <input v-model="formData.config.accent" type="color" class="w-12 h-10 rounded border-2 border-secondary-300" />
                              <input v-model="formData.config.accent" type="text" class="flex-1 px-3 py-2 text-input border border-secondary-300 rounded-lg font-mono" placeholder="#0066FF" />
                            </div>
                          </div>
                        </div>

                        <div v-if="currentTab === 'layout'" class="space-y-3">
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Alignment</label>
                            <select v-model="formData.config.alignment" class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg">
                              <option value="left">Left</option>
                              <option value="center">Center</option>
                              <option value="right">Right</option>
                            </select>
                          </div>
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Max Width</label>
                            <input v-model="formData.config.maxWidth" type="text" class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg" placeholder="800px" />
                          </div>
                        </div>

                        <div v-if="currentTab === 'feature_toggle'" class="space-y-3">
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Feature Key</label>
                            <input v-model="formData.config.feature_key" type="text" class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg font-mono" placeholder="stats" />
                          </div>
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Icon</label>
                            <input v-model="formData.config.icon" type="text" class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg font-mono" placeholder="heroicons:chart-bar" />
                          </div>
                          <div>
                            <label class="block text-label text-secondary-700 mb-1.5">Category</label>
                            <input v-model="formData.config.category" type="text" class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg" placeholder="analytics" />
                          </div>
                          <div class="rounded-lg border border-secondary-200 bg-secondary-50 p-3">
                            <p class="text-caption text-secondary-600">The "Enabled by default" state is controlled by the <strong>Default</strong> toggle on the option card (star icon). Set this option as default to enable it by default for new users.</p>
                          </div>
                        </div>
                      </div>
                    </template>

                    <!-- Plan Availability - Redesigned -->
                    <div class="bg-gradient-to-br from-secondary-50 to-secondary-100 rounded-lg p-5 border-2 border-secondary-200">
                      <h4 class="admin-form-section-title mb-4 flex items-center">
                        <Icon name="heroicons:shield-check" class="w-4 h-4 mr-2" />
                        Plan Availability
                      </h4>
                      <div class="space-y-3">
                        <label class="flex items-center p-3 bg-white rounded-lg border-2 cursor-pointer transition-all hover:border-info-400"
                          :class="formData.available_plans.includes('basic') ? 'border-info-500 shadow-sm' : 'border-secondary-300'">
                          <input
                            type="checkbox"
                            value="basic"
                            v-model="formData.available_plans"
                            class="w-5 h-5 rounded border-secondary-300 text-info-600 focus:ring-info-500"
                          />
                          <div class="ml-3 flex-1">
                            <div class="flex items-center justify-between">
                              <span class="text-label text-secondary-900">Basic Plan</span>
                              <div class="w-3 h-3 rounded-full bg-info-500"></div>
                            </div>
                            <p class="text-caption text-secondary-600 mt-0.5">Essential features for individuals</p>
                          </div>
                        </label>

                        <label class="flex items-center p-3 bg-white rounded-lg border-2 cursor-pointer transition-all hover:border-primary-400"
                          :class="formData.available_plans.includes('premium') ? 'border-primary-500 shadow-sm' : 'border-secondary-300'">
                          <input
                            type="checkbox"
                            value="premium"
                            v-model="formData.available_plans"
                            class="w-5 h-5 rounded border-secondary-300 text-primary-600 focus:ring-primary-500"
                          />
                          <div class="ml-3 flex-1">
                            <div class="flex items-center justify-between">
                              <span class="text-label text-secondary-900">Premium Plan</span>
                              <div class="w-3 h-3 rounded-full bg-primary-500"></div>
                            </div>
                            <p class="text-caption text-secondary-600 mt-0.5">Advanced features for professionals</p>
                          </div>
                        </label>

                        <label class="flex items-center p-3 bg-white rounded-lg border-2 cursor-pointer transition-all hover:border-warning-400"
                          :class="formData.available_plans.includes('business') ? 'border-warning-500 shadow-sm' : 'border-secondary-300'">
                          <input
                            type="checkbox"
                            value="business"
                            v-model="formData.available_plans"
                            class="w-5 h-5 rounded border-secondary-300 text-warning-600 focus:ring-warning-500"
                          />
                          <div class="ml-3 flex-1">
                            <div class="flex items-center justify-between">
                              <span class="text-label text-secondary-900">Business Plan</span>
                              <div class="w-3 h-3 rounded-full bg-warning-500"></div>
                            </div>
                            <p class="text-caption text-secondary-600 mt-0.5">Full access for teams & enterprises</p>
                          </div>
                        </label>

                        <label class="flex items-center p-3 bg-white rounded-lg border-2 cursor-pointer transition-all hover:border-secondary-400"
                          :class="formData.available_plans.includes('free') ? 'border-secondary-500 shadow-sm' : 'border-secondary-300'">
                          <input
                            type="checkbox"
                            value="free"
                            v-model="formData.available_plans"
                            class="w-5 h-5 rounded border-secondary-300 text-secondary-600 focus:ring-secondary-500"
                          />
                          <div class="ml-3 flex-1">
                            <div class="flex items-center justify-between">
                              <span class="text-label text-secondary-900">Free Plan</span>
                              <div class="w-3 h-3 rounded-full bg-secondary-500"></div>
                            </div>
                            <p class="text-caption text-secondary-600 mt-0.5">Free trial with limited features</p>
                          </div>
                        </label>
                      </div>
                      <p class="mt-3 text-helper bg-white px-3 py-2 rounded border border-secondary-200">
                        <Icon name="heroicons:information-circle" class="w-3 h-3 inline mr-1" />
                        Leave all unchecked to make available for all plans
                      </p>
                    </div>

                    <!-- Additional Settings -->
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <label class="block text-label text-secondary-900 mb-2">Display Order</label>
                        <input
                          v-model.number="formData.display_order"
                          type="number"
                          class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                          min="0"
                          placeholder="0"
                        />
                      </div>

                      <div>
                        <label class="block text-label text-secondary-900 mb-2">Status</label>
                        <div class="space-y-2 mt-2">
                          <template v-if="!isFieldTab">
                            <label class="flex items-center">
                              <input v-model="formData.is_active" type="checkbox" class="w-4 h-4 rounded border-secondary-300 text-success-600" />
                              <span class="ml-2 text-body text-secondary-700">Active</span>
                            </label>
                            <label class="flex items-center">
                              <input v-model="formData.is_default" type="checkbox" class="w-4 h-4 rounded border-secondary-300 text-primary-600" />
                              <span class="ml-2 text-body text-secondary-700">Default</span>
                            </label>
                          </template>
                          <template v-else>
                            <label class="flex items-center">
                              <input v-model="formData.is_required" type="checkbox" class="w-4 h-4 rounded border-secondary-300 text-error-600" />
                              <span class="ml-2 text-body text-secondary-700">Required</span>
                            </label>
                            <label class="flex items-center">
                              <input v-model="formData.is_visible" type="checkbox" class="w-4 h-4 rounded border-secondary-300 text-success-600" />
                              <span class="ml-2 text-body text-secondary-700">Visible</span>
                            </label>
                          </template>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Modal Footer -->
                <div class="admin-dialog-foot">
                  <button
                    type="button"
                    class="admin-btn admin-btn--ghost"
                    @click="closeModal"
                  >
                    Cancel
                  </button>
                  <button
                    type="button"
                    class="admin-btn admin-btn--primary"
                    :disabled="saving"
                    @click="saveOption"
                  >
                    <Icon v-if="saving" name="heroicons:arrow-path" class="h-4 w-4 animate-spin" />
                    {{ saving ? 'Saving…' : (modalMode === 'add' ? 'Add' : 'Update') }}
                  </button>
                </div>
              </div>
            </div>
      </Transition>
    </Teleport>

    <!-- Section Modal -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div v-if="showSectionModal && !showModal" class="admin-dialog-backdrop" @click.self="closeSectionModal">
          <div class="admin-dialog flex flex-col" style="width: min(56rem, 100%); margin-top: 5vh; max-height: 90vh" role="dialog" aria-modal="true">
            <!-- Modal Header -->
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-accent-100 text-accent-700">
                  <Icon name="heroicons:rectangle-stack" class="h-5 w-5" />
                </span>
                <div class="min-w-0">
                  <p class="admin-panel-title">{{ sectionModalMode === 'add' ? 'Add' : 'Edit' }} section</p>
                  <p class="admin-panel-subtitle">Define the section details and manage the fields it contains.</p>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <span class="admin-badge admin-badge--neutral">{{ sectionFormData.key || 'new-section' }}</span>
                <button type="button" class="admin-icon-btn" aria-label="Close" @click="closeSectionModal">
                  <Icon name="heroicons:x-mark" class="h-5 w-5" />
                </button>
              </div>
            </div>

            <!-- Modal Body -->
            <div class="admin-panel-body flex-1 overflow-y-auto">
              <div class="space-y-6">
                    <!-- ════════════════ SECTION SETTINGS ════════════════ -->
                    <div>
                      <h4 class="text-caption font-bold uppercase tracking-wider text-secondary-400 mb-3">Section Settings</h4>
                      <div class="grid grid-cols-2 gap-4">
                        <div>
                          <label class="block text-label text-secondary-900 mb-2">Section Key <span class="text-error-600">*</span></label>
                          <input
                            v-model="sectionFormData.key"
                            type="text"
                            :disabled="sectionModalMode === 'edit'"
                            class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 disabled:bg-secondary-100"
                            placeholder="e.g., profile, company, services"
                          />
                        </div>

                        <div>
                          <label class="block text-label text-secondary-900 mb-2">Section Name <span class="text-error-600">*</span></label>
                          <input
                            v-model="sectionFormData.name"
                            type="text"
                            class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                            placeholder="e.g., Profile, Company, Services"
                          />
                        </div>

                        <!-- ICON PICKER -->
                        <div class="relative">
                          <label class="block text-label text-secondary-900 mb-2">Icon</label>
                          <button
                            type="button"
                            @click="showIconPicker = !showIconPicker"
                            class="w-full flex items-center gap-3 px-3 py-2 text-input border border-secondary-300 rounded-lg hover:border-primary-400 hover:bg-primary-50/30 focus:ring-2 focus:ring-primary-500 transition-all bg-white text-left"
                          >
                            <div class="w-8 h-8 rounded-lg bg-primary-50 border border-primary-100 flex items-center justify-center flex-shrink-0">
                              <Icon :name="sectionFormData.icon" class="w-4 h-4 text-primary-600" />
                            </div>
                            <div class="flex-1 min-w-0">
                              <div class="text-secondary-900 font-medium truncate">{{ sectionFormData.icon }}</div>
                              <div class="text-helper">Click to pick an icon</div>
                            </div>
                            <Icon :name="showIconPicker ? 'heroicons:chevron-up' : 'heroicons:chevron-down'" class="w-4 h-4 text-secondary-400 flex-shrink-0" />
                          </button>

                          <!-- Icon Picker Popover -->
                          <div
                            v-if="showIconPicker"
                            class="absolute z-50 left-0 right-0 top-full mt-2 bg-white border border-secondary-200 rounded-xl shadow-2xl p-3 animate-in fade-in slide-in-from-top-2"
                          >
                            <div class="mb-3">
                              <div class="relative">
                                <Icon name="heroicons:magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-secondary-400" />
                                <input
                                  v-model="sectionIconSearch"
                                  type="text"
                                  placeholder="Search icons..."
                                  class="w-full pl-9 pr-3 py-2 text-input border border-secondary-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                />
                              </div>
                            </div>
                            <div class="grid grid-cols-8 gap-1.5 max-h-60 overflow-y-auto p-1 [scrollbar-width:thin] [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-thumb]:bg-[color:var(--brass)] [&::-webkit-scrollbar-thumb]:rounded-full">
                              <button
                                v-for="iconName in filteredSectionIconChoices"
                                :key="iconName"
                                type="button"
                                @click="selectSectionIcon(iconName)"
                                :class="[
                                  'aspect-square rounded-lg border flex items-center justify-center transition-all hover:-translate-y-0.5',
                                  sectionFormData.icon === iconName
                                    ? 'bg-primary-50 border-primary-500 ring-2 ring-primary-200 shadow-inner'
                                    : 'bg-white border-secondary-200 hover:border-primary-300 hover:bg-primary-50/40'
                                ]"
                                :title="iconName"
                              >
                                <Icon :name="iconName" :class="['w-5 h-5', sectionFormData.icon === iconName ? 'text-primary-600' : 'text-secondary-600']" />
                              </button>
                            </div>
                            <div v-if="filteredSectionIconChoices.length === 0" class="py-6 text-center text-body text-secondary-400 italic">
                              No icons match &quot;{{ sectionIconSearch }}&quot;
                            </div>
                          </div>
                        </div>

                        <div>
                          <label class="block text-label text-secondary-900 mb-2">Description</label>
                          <textarea
                            v-model="sectionFormData.description"
                            rows="2"
                            class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 resize-none"
                            placeholder="Brief description..."
                          ></textarea>
                        </div>

                        <div>
                          <label class="block text-label text-secondary-900 mb-2">Display Order</label>
                          <input
                            v-model.number="sectionFormData.display_order"
                            type="number"
                            class="w-full px-3 py-2 text-input border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500"
                            min="0"
                            placeholder="0"
                          />
                        </div>

                        <div>
                          <label class="block text-label text-secondary-900 mb-2">Has Fields</label>
                          <label class="flex items-center p-2 bg-white rounded border border-secondary-300 cursor-pointer">
                            <input v-model="sectionFormData.has_fields" type="checkbox" class="w-4 h-4 rounded border-secondary-300 text-primary-600" />
                            <span class="ml-2 text-body text-secondary-900">Yes</span>
                          </label>
                        </div>

                        <div>
                          <label class="block text-label text-secondary-900 mb-2">Is Active</label>
                          <label class="flex items-center p-2 bg-white rounded border border-secondary-300 cursor-pointer">
                            <input v-model="sectionFormData.is_active" type="checkbox" class="w-4 h-4 rounded border-secondary-300 text-primary-600" />
                            <span class="ml-2 text-body text-secondary-900">Yes</span>
                          </label>
                        </div>

                        <!-- Available Plans -->
                        <div class="col-span-2">
                          <label class="block text-label text-secondary-900 mb-2">Available Plans</label>
                          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <label
                              v-for="plan in plans"
                              :key="plan.id"
                              class="flex items-center gap-2 p-2.5 bg-white rounded-lg border-2 cursor-pointer transition-all"
                              :class="sectionFormData.available_plans.includes(plan.id) ? 'border-primary-500 shadow-sm' : 'border-secondary-300 hover:border-primary-300'"
                            >
                              <input
                                type="checkbox"
                                :value="plan.id"
                                v-model="sectionFormData.available_plans"
                                class="w-4 h-4 rounded border-secondary-300 text-primary-600 focus:ring-primary-500"
                              />
                              <div class="flex items-center gap-1.5 min-w-0">
                                <div
                                  class="w-2.5 h-2.5 rounded-full flex-shrink-0"
                                  :class="plan.id === 'free' ? 'bg-secondary-500' : plan.id === 'basic' ? 'bg-info-500' : plan.id === 'premium' ? 'bg-primary-500' : 'bg-warning-500'"
                                ></div>
                                <span class="text-label text-secondary-900 truncate">{{ plan.name }}</span>
                              </div>
                            </label>
                          </div>
                          <p class="text-helper mt-2 flex items-start gap-1">
                            <Icon name="heroicons:information-circle" class="w-3 h-3 mt-0.5 flex-shrink-0" />
                            Leave all unchecked to make this section available for all plans.
                          </p>
                        </div>
                      </div>
                    </div>

                    <!-- Divider -->
                    <div class="h-px bg-gradient-to-r from-transparent via-secondary-200 to-transparent -mx-2"></div>

                    <!-- ════════════════ SECTION FIELDS ════════════════ -->
                    <div>
                      <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                          <h4 class="text-caption font-bold uppercase tracking-wider text-secondary-400">Section Fields</h4>
                          <span class="text-badge px-2 py-0.5 bg-secondary-100 text-secondary-600 rounded-full font-bold">
                            {{ (fields[sectionFormData.key] || []).length }} fields
                          </span>
                        </div>
                        <button
                          v-if="sectionFormData.has_fields && sectionFormData.key"
                          type="button"
                          @click="openAddFieldFromSection"
                          class="inline-flex items-center gap-1.5 px-3 py-1.5 text-button text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors"
                        >
                          <Icon name="heroicons:plus" class="w-3.5 h-3.5" />
                          Add Field
                        </button>
                      </div>

                      <!-- Empty states -->
                      <div v-if="!sectionFormData.key" class="border-2 border-dashed border-secondary-200 rounded-xl p-6 text-center">
                        <Icon name="heroicons:pencil-square" class="w-8 h-8 text-secondary-300 mx-auto mb-2" />
                        <p class="text-body font-semibold text-secondary-400">Fill in Section Key first</p>
                        <p class="text-body text-secondary-400 mt-0.5">Fields are saved per section key</p>
                      </div>
                      <div v-else-if="!sectionFormData.has_fields" class="border-2 border-dashed border-secondary-200 rounded-xl p-6 text-center">
                        <Icon name="heroicons:cube" class="w-8 h-8 text-secondary-300 mx-auto mb-2" />
                        <p class="text-body font-semibold text-secondary-400">This section has no fields enabled</p>
                        <p class="text-body text-secondary-400 mt-0.5">Tick "Has Fields" above to manage fields here</p>
                      </div>

                      <!-- Fields list -->
                      <div v-else class="border border-secondary-200 rounded-xl overflow-hidden divide-y divide-secondary-100 bg-white">
                        <div
                          v-for="(field, fIdx) in (fields[sectionFormData.key] || [])"
                          :key="field.id || field.option_id"
                          class="group flex items-center gap-3 px-3 py-2.5 hover:bg-secondary-50/70 transition-colors"
                        >
                          <div class="w-6 text-center flex-shrink-0">
                            <span class="text-micro font-bold text-secondary-400">#{{ fIdx + 1 }}</span>
                          </div>
                          <div class="w-8 h-8 rounded-lg bg-secondary-50 border border-secondary-200 flex items-center justify-center flex-shrink-0">
                            <Icon name="heroicons:bars-3" class="w-4 h-4 text-secondary-400" />
                          </div>
                          <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                              <span class="text-body font-semibold text-secondary-900 truncate">{{ field.label }}</span>
                              <span v-if="field.required || field.is_required" class="text-badge px-1.5 py-0.5 rounded bg-error-50 text-error-700 font-bold border border-error-200">
                                Required
                              </span>
                              <span v-if="!field.is_active && field.is_active !== undefined" class="text-badge px-1.5 py-0.5 rounded bg-secondary-100 text-secondary-500 font-bold">
                                Inactive
                              </span>
                            </div>
                            <div class="flex items-center gap-2 mt-0.5">
                              <span class="text-badge px-1.5 py-0.5 rounded bg-primary-50 text-primary-700 font-medium border border-primary-100">
                                {{ field.field_type || field.type || field.input_type || 'text' }}
                              </span>
                              <span class="text-micro text-secondary-400 font-mono truncate">key: {{ field.field_key || field.key || field.name }}</span>
                            </div>
                          </div>
                          <!-- Plan chips -->
                          <div class="hidden md:flex items-center gap-1 flex-shrink-0">
                            <template v-for="plan in plans" :key="plan.id">
                              <span
                                v-if="isFieldAvailableForPlan(field, plan.id)"
                                :class="[
                                  'text-badge px-1.5 py-0.5 rounded-full font-bold',
                                  plan.color === 'amber' ? 'bg-warning-50 text-warning-700 border border-warning-200' :
                                  plan.color === 'purple' ? 'bg-primary-50 text-primary-700 border border-primary-200' :
                                  plan.color === 'slate' ? 'bg-secondary-50 text-secondary-700 border border-secondary-200' :
                                  'bg-info-50 text-info-700 border border-info-200'
                                ]"
                              >
                                {{ plan.name.charAt(0) }}
                              </span>
                            </template>
                          </div>
                          <div class="flex items-center gap-1 flex-shrink-0">
                            <button
                              type="button"
                              @click="openEditFieldFromSection(field)"
                              class="w-8 h-8 rounded-lg flex items-center justify-center text-secondary-500 hover:text-primary-600 hover:bg-primary-50 transition-colors"
                              title="Edit field"
                            >
                              <Icon name="heroicons:pencil" class="w-4 h-4" />
                            </button>
                            <button
                              type="button"
                              @click="deleteFieldFromSection(field)"
                              class="w-8 h-8 rounded-lg flex items-center justify-center text-secondary-500 hover:text-error-600 hover:bg-error-50 transition-colors"
                              title="Delete field"
                            >
                              <Icon name="heroicons:trash" class="w-4 h-4" />
                            </button>
                          </div>
                        </div>

                        <div v-if="(fields[sectionFormData.key] || []).length === 0" class="p-8 text-center">
                          <div class="w-14 h-14 mx-auto rounded-2xl bg-secondary-50 border-2 border-dashed border-secondary-200 flex items-center justify-center mb-3">
                            <Icon name="heroicons:queue-list" class="w-7 h-7 text-secondary-300" />
                          </div>
                          <p class="text-body font-semibold text-secondary-600 mb-1">No fields yet</p>
                          <p class="text-body text-secondary-400 mb-4">Add the first input field to this section</p>
                          <button
                            type="button"
                            @click="openAddFieldFromSection"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-button text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors"
                          >
                            <Icon name="heroicons:plus" class="w-4 h-4" />
                            Add First Field
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Modal Footer -->
                <div class="admin-dialog-foot">
                  <button
                    type="button"
                    class="admin-btn admin-btn--ghost"
                    @click="closeSectionModal"
                  >
                    Cancel
                  </button>
                  <button
                    type="button"
                    class="admin-btn admin-btn--primary"
                    :disabled="saving"
                    @click="saveSection"
                  >
                    <Icon v-if="saving" name="heroicons:arrow-path" class="h-4 w-4 animate-spin" />
                    {{ saving ? 'Saving…' : (sectionModalMode === 'add' ? 'Add' : 'Update') }}
                  </button>
                </div>
              </div>
            </div>
      </Transition>
    </Teleport>

    <!-- Business User Modal -->
    <Teleport to="body">
      <Transition name="admin-fade">
        <div v-if="showBusinessUserModal" class="admin-dialog-backdrop" @click.self="closeBusinessUserModal">
          <div class="admin-dialog flex flex-col" style="width: min(48rem, 100%); margin-top: 5vh; max-height: 90vh" role="dialog" aria-modal="true">
            <div class="admin-dialog-head">
              <div class="flex items-center gap-3">
                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-warning-100 text-warning-700">
                  <span class="text-label font-bold">{{ selectedBusinessUser?.name?.charAt(0) || '?' }}</span>
                </span>
                <div class="min-w-0">
                  <p class="admin-panel-title">{{ selectedBusinessUser?.name }}</p>
                  <p class="admin-panel-subtitle">{{ selectedBusinessUser?.email }}</p>
                </div>
              </div>
              <button type="button" class="admin-icon-btn" aria-label="Close" @click="closeBusinessUserModal">
                <Icon name="heroicons:x-mark" class="h-5 w-5" />
              </button>
            </div>
            <div class="admin-panel-body flex-1 overflow-y-auto">
                  <div class="mb-6 p-4 bg-secondary-50 rounded-lg">
                    <label class="flex items-center justify-between cursor-pointer">
                      <div>
                        <span class="font-medium text-secondary-900">Use Custom Settings</span>
                        <p class="text-helper">Override default Business plan settings</p>
                      </div>
                      <input type="checkbox" v-model="businessUserFormData.use_custom" class="w-5 h-5 rounded border-secondary-300 text-warning-600" />
                    </label>
                  </div>
                  <!-- Custom Settings UI - Always rendered but conditionally visible -->
                  <div :class="{'hidden': !businessUserFormData.use_custom}" class="space-y-4">
                    <!-- Data Fields - Grouped by Tab -->
                    <div>
                      <h4 class="font-medium text-secondary-900 mb-2"><Icon name="heroicons:rectangle-stack" class="w-4 h-4 inline mr-1 text-info-600" />Data Fields</h4>
                      <div class="max-h-[250px] overflow-y-auto p-2 bg-secondary-50 rounded-lg space-y-3">
                        <div v-for="(tabFields, tabName) in businessUserAvailableData.fields" :key="tabName">
                          <div class="flex items-center justify-between sticky top-0 bg-secondary-50 py-1 mb-1">
                            <label class="flex items-center cursor-pointer">
                              <input 
                                type="checkbox" 
                                :checked="isTabFullySelected(tabFields)" 
                                @change="toggleTabFields(tabFields, $event.target.checked)"
                                class="w-3.5 h-3.5 rounded text-info-600"
                              />
                              <span class="ml-2 text-caption font-semibold text-secondary-600 uppercase">{{ tabName }}</span>
                            </label>
                            <span class="text-caption text-secondary-400">{{ getTabSelectedCount(tabFields) }}/{{ tabFields.length }}</span>
                          </div>
                          <div class="grid grid-cols-3 gap-1">
                            <label v-for="field in tabFields" :key="field.id" class="flex items-center p-1.5 bg-white rounded border text-caption cursor-pointer hover:border-primary-300">
                              <input type="checkbox" :value="field.id" v-model="businessUserFormData.enabled_fields" class="w-3 h-3 rounded" />
                              <span class="ml-1.5 truncate">{{ field.label }}</span>
                            </label>
                          </div>
                        </div>
                      </div>
                    </div>
                    <!-- Design Options - Grouped by Type -->
                    <div>
                      <h4 class="font-medium text-secondary-900 mb-2"><Icon name="heroicons:paint-brush" class="w-4 h-4 inline mr-1 text-primary-600" />Design Options</h4>
                      <div class="max-h-[200px] overflow-y-auto p-2 bg-secondary-50 rounded-lg space-y-3">
                        <template v-for="(typeOptions, typeName) in businessUserAvailableData.design_options" :key="typeName">
                          <div v-if="typeName !== 'feature_toggle'">
                            <div class="flex items-center justify-between mb-1">
                              <label class="flex items-center cursor-pointer">
                                <input 
                                  type="checkbox" 
                                  :checked="isTypeFullySelected(typeOptions)" 
                                  @change="toggleTypeOptions(typeOptions, $event.target.checked)"
                                  class="w-3.5 h-3.5 rounded text-primary-600"
                                />
                                <span class="ml-2 text-caption font-semibold text-secondary-600 uppercase">{{ typeName.replace('_', ' ') }}</span>
                              </label>
                              <span class="text-caption text-secondary-400">{{ getTypeSelectedCount(typeOptions) }}/{{ typeOptions.length }}</span>
                            </div>
                            <div class="grid grid-cols-3 gap-1">
                              <label v-for="option in typeOptions" :key="option.id" class="flex items-center p-1.5 bg-white rounded border text-caption cursor-pointer hover:border-primary-300">
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
                      <h4 class="font-medium text-secondary-900 mb-2"><Icon name="heroicons:sparkles" class="w-4 h-4 inline mr-1 text-warning-600" />Features</h4>
                      <div class="grid grid-cols-2 gap-2">
                        <label v-for="feature in filteredBusinessUserFeatureToggles" :key="feature.option_id"
                          :class="['flex items-center p-2 rounded-lg border cursor-pointer', businessUserFormData.enabled_features.includes(feature.option_id) ? 'border-warning-300 bg-warning-50' : 'border-secondary-200']">
                          <input type="checkbox" :value="feature.option_id" v-model="businessUserFormData.enabled_features" class="w-4 h-4 rounded text-warning-600" />
                          <span class="ml-2 text-body">{{ feature.name }}</span>
                        </label>
                      </div>
                    </div>
                    <div>
                      <label class="block text-label text-secondary-700 mb-2">Admin Notes</label>
                      <textarea v-model="businessUserFormData.notes" rows="2" class="w-full px-3 py-2 border border-secondary-300 rounded-lg text-input" placeholder="Optional notes..."></textarea>
                    </div>
                  </div>
                  <!-- Default Settings UI -->
                  <div :class="{'hidden': businessUserFormData.use_custom}" class="text-center py-8 bg-secondary-50 rounded-lg">
                    <Icon name="heroicons:check-circle" class="w-12 h-12 text-success-500 mx-auto mb-2" />
                    <p class="font-medium text-secondary-700">Using Default Business Plan Settings</p>
                  </div>
                </div>
                <div class="admin-dialog-foot !justify-between">
                  <button v-if="businessUserFormData.use_custom" type="button" class="admin-btn admin-btn--ghost" @click="resetBusinessUserToDefault">Reset to default</button>
                  <div v-else></div>
                  <div class="flex items-center gap-2">
                    <button type="button" class="admin-btn admin-btn--ghost" @click="closeBusinessUserModal">Cancel</button>
                    <button type="button" class="admin-btn admin-btn--primary" :disabled="savingBusinessUser" @click="saveBusinessUserAssignment">
                      <Icon v-if="savingBusinessUser" name="heroicons:arrow-path" class="h-4 w-4 animate-spin" />
                      {{ savingBusinessUser ? 'Saving…' : 'Save' }}
                    </button>
                  </div>
                </div>
            </div>
          </div>
      </Transition>
    </Teleport>

    <AdminConfirmDialog
      v-model="confirmOpen"
      :title="confirmConfig.title"
      :message="confirmConfig.message"
      :confirm-label="confirmConfig.confirmLabel"
      :tone="confirmConfig.tone"
      :loading="confirmBusy"
      @confirm="runConfirm"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useAuthStore } from '~/stores/auth';

const authStore = useAuthStore();
const config = useRuntimeConfig();
const { $toast } = useNuxtApp();

// Shared confirmation dialog state
const confirmOpen = ref(false);
const confirmBusy = ref(false);
const confirmConfig = ref({
  title: '',
  message: '',
  confirmLabel: 'Confirm',
  tone: 'primary',
});
let confirmAction = null;

const askConfirm = (options, action) => {
  confirmConfig.value = { confirmLabel: 'Confirm', tone: 'primary', ...options };
  confirmAction = action;
  confirmOpen.value = true;
};

const runConfirm = async () => {
  if (!confirmAction) return;
  confirmBusy.value = true;
  try {
    await confirmAction();
  } finally {
    confirmBusy.value = false;
    confirmOpen.value = false;
    confirmAction = null;
  }
};

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
const fieldsSearchQuery = ref('');
const activeView = ref('assignments'); // assignments | sections | catalog

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

// ========================================
// 📊 OVERVIEW STATISTICS COMPUTED
// ========================================
const totalSections = computed(() => generalSections.value.length);
const totalFields = computed(() => Object.values(fields.value).flat().length);
const totalDesignOptions = computed(() => {
  const designTabs = ['theme', 'font', 'button_style', 'card_style', 'bg_animation', 'profile_style', 'color_scheme', 'layout'];
  return designTabs.reduce((sum, tab) => sum + (options.value[tab]?.length || 0), 0);
});
const totalFeatures = computed(() => filteredOptionsFeatureToggles.value.length);

// Plan breakdown for fields
const fieldPlanBreakdown = computed(() => {
  const allFields = Object.values(fields.value).flat();
  return {
    basic: allFields.filter(f => isAvailableForPlan(f, 'basic')).length,
    premium: allFields.filter(f => isAvailableForPlan(f, 'premium')).length,
    business: allFields.filter(f => isAvailableForPlan(f, 'business')).length,
    free: allFields.filter(f => isAvailableForPlan(f, 'free')).length,
    total: allFields.length,
  };
});

// Plan breakdown for design options
const designPlanBreakdown = computed(() => {
  const designTabs = ['theme', 'font', 'button_style', 'card_style', 'bg_animation', 'profile_style', 'color_scheme', 'layout'];
  const allDesigns = designTabs.flatMap(tab => options.value[tab] || []);
  return {
    basic: allDesigns.filter(o => isAvailableForPlan(o, 'basic')).length,
    premium: allDesigns.filter(o => isAvailableForPlan(o, 'premium')).length,
    business: allDesigns.filter(o => isAvailableForPlan(o, 'business')).length,
    free: allDesigns.filter(o => isAvailableForPlan(o, 'free')).length,
    total: allDesigns.length,
  };
});

// Plan breakdown stats: KEYED OBJECT — used in matrix / plan-by-plan access: planStats.basic.features etc
const planStats = computed(() => {
  const totalPossible = totalFields.value + totalDesignOptions.value + totalFeatures.value;
  const keyed = {};
  plans.forEach(plan => {
    const sectionsCount = availableSections.value.filter(section => {
      const planList = getSectionPlans(section.key);
      return planList.includes(plan.id);
    }).length;
    const fieldsCount = fieldPlanBreakdown.value[plan.id];
    const designsCount = designPlanBreakdown.value[plan.id];
    const featuresCount = filteredOptionsFeatureToggles.value.filter(f => isAvailableForPlan(f, plan.id)).length;
    const total = fieldsCount + designsCount + featuresCount;
    keyed[plan.id] = {
      ...plan,
      sections: sectionsCount,
      fields: fieldsCount,
      designs: designsCount,
      features: featuresCount,
      fieldsCount,
      designsCount,
      featuresCount,
      sectionsCount,
      total,
      percentage: totalPossible > 0 ? Math.round((total / totalPossible) * 100) : 0,
    };
  });
  return keyed;
});

// Plan breakdown stats: PURE ARRAY — safe for v-for iteration with :key="stat.id"
const planStatsList = computed(() => plans.map(plan => planStats.value[plan.id]).filter(Boolean));

// Filtered fields table with search
const filteredFieldsTable = computed(() => {
  const opts = getFilteredOptions.value;
  if (!fieldsSearchQuery.value) return opts;
  const q = fieldsSearchQuery.value.toLowerCase();
  return opts.filter(f => 
    f.label?.toLowerCase().includes(q) ||
    f.field_key?.toLowerCase().includes(q) ||
    f.field_type?.toLowerCase().includes(q) ||
    f.placeholder?.toLowerCase().includes(q) ||
    f.field_group?.toLowerCase().includes(q)
  );
});

// Excluded features (deprecated, not shown in UI)
const excludedFeatureIds = ['layout_designer', 'click_tracking', 'analytics'];

// Filtered feature toggles (excludes deprecated features)
const filteredFeatureToggles = computed(() => {
  const features = businessUserAvailableData.value.design_options?.feature_toggle || [];
  return features.filter(f => !excludedFeatureIds.includes(f.option_id));
});

// Filtered feature toggles for options (excludes deprecated features)
const filteredOptionsFeatureToggles = computed(() => {
  const features = options.value.feature_toggle || [];
  return features.filter(f => !excludedFeatureIds.includes(f.option_id));
});

// Filtered feature toggles for business user modal (excludes deprecated features)
const filteredBusinessUserFeatureToggles = computed(() => {
  const features = businessUserAvailableData.value.design_options?.feature_toggle || [];
  return features.filter(f => !excludedFeatureIds.includes(f.option_id));
});

// Section Management
const showSectionModal = ref(false);
// Tracks the catalog tab that was active before opening a field modal from the
// section modal, so we can restore it afterwards (field tabs were removed from
// the catalog tab bar to avoid duplication with the Field sections view).
const savedCatalogTab = ref(null);
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
  available_plans: ['free', 'basic', 'premium', 'business']
});

const showIconPicker = ref(false);
const sectionIconSearch = ref('');
const sectionIconChoices = [
  'heroicons:user', 'heroicons:user-circle', 'heroicons:user-group', 'heroicons:users',
  'heroicons:identification', 'heroicons:building-office', 'heroicons:building-office-2',
  'heroicons:rocket-launch', 'heroicons:briefcase', 'heroicons:sparkles', 'heroicons:star', 'heroicons:star-solid',
  'heroicons:link', 'heroicons:at-symbol', 'heroicons:phone', 'heroicons:envelope',
  'heroicons:map-pin', 'heroicons:calendar', 'heroicons:clock', 'heroicons:document-text', 'heroicons:document',
  'heroicons:queue-list', 'heroicons:clipboard-document-check', 'heroicons:clipboard-document-list',
  'heroicons:pencil', 'heroicons:pencil-square', 'heroicons:paint-brush', 'heroicons:swatch', 'heroicons:photo',
  'heroicons:video-camera', 'heroicons:musical-note', 'heroicons:heart', 'heroicons:hand-thumb-up',
  'heroicons:chat-bubble-left', 'heroicons:chat-bubble-left-right',
  'heroicons:book-open', 'heroicons:academic-cap', 'heroicons:trophy', 'heroicons:shield-check',
  'heroicons:gift', 'heroicons:credit-card', 'heroicons:wallet', 'heroicons:shopping-cart', 'heroicons:shopping-bag',
  'heroicons:tag', 'heroicons:fire', 'heroicons:light-bulb', 'heroicons:beaker',
  'heroicons:cog', 'heroicons:cog-6-tooth', 'heroicons:code-bracket', 'heroicons:command-line', 'heroicons:bolt',
  'heroicons:device-phone-mobile', 'heroicons:computer-desktop', 'heroicons:camera',
  'heroicons:folder', 'heroicons:folder-open', 'heroicons:archive-box',
  'heroicons:magnifying-glass', 'heroicons:bell', 'heroicons:paper-airplane',
  'heroicons:bookmark', 'heroicons:pin', 'heroicons:cube', 'heroicons:rss',
  'heroicons:language', 'heroicons:cursor-arrow-rays', 'heroicons:squares-2x2', 'heroicons:squares-plus',
  'heroicons:rectangle-stack', 'heroicons:rectangle-group', 'heroicons:hashtag', 'heroicons:bars-3',
  'heroicons:currency-dollar', 'heroicons:check-circle', 'heroicons:check-circle-solid',
  'heroicons:information-circle', 'heroicons:arrow-path', 'heroicons:plus', 'heroicons:inbox'
];

const filteredSectionIconChoices = computed(() => {
  const q = sectionIconSearch.value.trim().toLowerCase();
  if (!q) return sectionIconChoices;
  return sectionIconChoices.filter(n => n.toLowerCase().includes(q));
});

const selectSectionIcon = (iconName) => {
  sectionFormData.value.icon = iconName;
  showIconPicker.value = false;
  sectionIconSearch.value = '';
};

const openAddFieldFromSection = () => {
  const sKey = sectionFormData.value.key;
  if (!sKey) {
    $toast.error('Please enter Section Key first before adding fields');
    return;
  }
  savedCatalogTab.value = currentTab.value;
  currentTab.value = `field_${sKey}`;
  nextTick(() => {
    openAddModal();
  });
};

const openEditFieldFromSection = (field) => {
  const sKey = sectionFormData.value.key || field.tab || (field.option_id ? field.option_id.split('__')[0] : null);
  savedCatalogTab.value = currentTab.value;
  if (sKey) currentTab.value = `field_${sKey}`;
  nextTick(() => {
    openEditModal(field);
  });
};

const deleteFieldFromSection = async (field) => {
  const sKey = sectionFormData.value.key || field.tab;
  savedCatalogTab.value = currentTab.value;
  if (sKey) currentTab.value = `field_${sKey}`;
  try {
    await confirmDelete(field);
  } finally {
    if (savedCatalogTab.value) {
      currentTab.value = savedCatalogTab.value;
      savedCatalogTab.value = null;
    }
  }
};

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
  { id: 'free', name: 'Free', color: 'slate' },
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
  { id: 'style', name: 'Style', icon: 'heroicons:sparkles', category: 'design', requiredPermission: 'style', subItems: ['theme', 'font', 'button_style', 'card_style', 'bg_animation', 'profile_style', 'color_scheme', 'layout'] },
  { id: 'watermarks', name: 'Watermarks', icon: 'heroicons:eye', category: 'design', requiredPermission: 'watermarks', subItems: ['feature_toggle'] },
];

// All tabs (for backward compatibility with legacy code)
const tabs = [
  { id: 'theme', name: 'Themes', icon: 'heroicons:paint-brush' },
  { id: 'font', name: 'Fonts', icon: 'heroicons:language' },
  { id: 'button_style', name: 'Button Styles', icon: 'heroicons:cursor-arrow-rays' },
  { id: 'card_style', name: 'Card Styles', icon: 'heroicons:rectangle-stack' },
  { id: 'bg_animation', name: 'Background Effects', icon: 'heroicons:sparkles' },
  { id: 'profile_style', name: 'Profile Layouts', icon: 'heroicons:user-circle' },
  { id: 'color_scheme', name: 'Color Schemes', icon: 'heroicons:swatch' },
  { id: 'layout', name: 'Layouts', icon: 'heroicons:squares-2x2' },
  { id: 'feature_toggle', name: 'Features', icon: 'heroicons:rocket-launch' },
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

// ========================================
// 🎨 THEME PREVIEW CSS HELPER
// ========================================
// Auto-generates the previewCss for a theme from its bgGradient or backgroundColor.
const syncThemePreviewCss = () => {
  const cfg = formData.value.config || {};
  const bg = cfg.bgGradient || cfg.backgroundColor;
  if (bg) {
    formData.value.config = {
      ...cfg,
      previewCss: `background: ${bg};`,
    };
  }
};

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
      available_plans: section.available_plans || ['free', 'basic', 'premium', 'business']
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
      available_plans: ['free', 'basic', 'premium', 'business']
    };
  }
  
  showSectionModal.value = true;
};

// Close Section Modal
const closeSectionModal = () => {
  showSectionModal.value = false;
  showIconPicker.value = false;
  sectionIconSearch.value = '';
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
    available_plans: ['free', 'basic', 'premium', 'business']
  };
};

// Save Section (Create or Update)
const saveSection = async () => {
  // Validation
  if (!sectionFormData.value.key || !sectionFormData.value.name) {
    $toast.error('Please fill in Section Key and Name');
    return;
  }
  
  // Validate key format (lowercase, no spaces)
  const keyRegex = /^[a-z][a-z0-9_]*$/;
  if (!keyRegex.test(sectionFormData.value.key)) {
    $toast.error('Section Key must be lowercase, start with a letter, and contain only letters, numbers, and underscores');
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
    
    $toast.success(`Section ${isEdit ? 'updated' : 'created'} successfully`);
    closeSectionModal();
    
    // Reload sections and data
    await loadSections();
    await fetchData();
  } catch (error) {
    console.error('❌ Error saving section:', error);
    $toast.error(`Failed to save section: ${error.message}`);
  } finally {
    saving.value = false;
  }
};

// Delete Section
const confirmDeleteSection = (section) => {
  const fieldsCount = (fields.value[section.key] || []).length;

  let confirmMsg = `Are you sure you want to delete the "${section.name}" section?`;
  if (fieldsCount > 0) {
    confirmMsg += ` This will also delete ${fieldsCount} field(s) in this section.`;
  }
  confirmMsg += ' This action cannot be undone.';

  askConfirm(
    { title: 'Delete section', message: confirmMsg, confirmLabel: 'Delete', tone: 'danger' },
    async () => {
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
        $toast.success('Section deleted successfully');

        // Reload sections and data
        await loadSections();
        await fetchData();
      } catch (error) {
        console.error('❌ Error deleting section:', error);
        $toast.error(`Failed to delete section: ${error.message}`);
      }
    }
  );
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

// Get Section Plans — prefer the section's own available_plans setting.
// Falls back to the union of all fields' available_plans if the section has none.
const getSectionPlans = (sectionKey) => {
  const section = availableSections.value.find(s => s.key === sectionKey);
  const sectionPlans = ensureArray(section?.available_plans);
  if (sectionPlans.length > 0) {
    return sectionPlans;
  }

  const sectionFields = fields.value[sectionKey] || [];
  if (sectionFields.length === 0) {
    return ['free', 'basic', 'premium', 'business']; // No fields = all plans
  }
  
  // Calculate union of all fields' available_plans
  const plansSet = new Set();
  sectionFields.forEach(field => {
    const fieldPlans = ensureArray(field.available_plans);
    if (fieldPlans.length === 0) {
      // Empty means all plans
      plansSet.add('free');
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
  // Design catalog only shows design/feature tabs.
  // Field tabs (profile, company, services, links, portfolio, blog) live under
  // the "Field sections" tab and are intentionally excluded here to avoid duplication.
  return [...tabs];
});

const isFieldTab = computed(() => currentTab.value.startsWith('field_'));
const currentTabName = computed(() => {
  if (isFieldTab.value) return 'Field';
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

// Safe plan-availability check for fields (handles array, string, or undefined)
const isFieldAvailableForPlan = (field, plan) => {
  const plans = ensureArray(field?.available_plans || field?.plan_assignments);
  if (plans.length === 0) return true; // empty = all plans
  return plans.includes(plan);
};

// Returns a context-appropriate placeholder hint based on field type
const getFieldPlaceholderHint = (fieldType) => {
  const hints = {
    text: 'Enter text',
    email: 'Enter email address',
    tel: 'Enter phone number',
    url: 'Enter website URL',
    number: 'Enter number',
    textarea: 'Enter text',
    richtext: 'Enter text',
    select: 'Select an option',
    toggle: '',
    checkbox: '',
    date: 'Select a date',
    image: 'Upload an image',
    gallery: 'Upload images',
    video: 'Upload a video',
    file: 'Upload a file',
    repeater: '',
    tags: 'Enter tags',
    icon: 'Select an icon',
    color: 'Select a color',
  };
  return hints[fieldType] || 'Enter value';
};

const getPlanNames = (option) => {
  if (!option.available_plans || option.available_plans.length === 0) {
    return [];
  }
  return option.available_plans.map(p => {
    if (p === 'free') return 'Free';
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
    
    // If available_plans is empty, it means "all plans" - initialize with all 4
    let plans = item.available_plans || [];
    if (plans.length === 0) {
      plans = ['free', 'basic', 'premium', 'business'];
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
    $toast.error('Failed to update plan assignment');
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
        : ['free', 'basic', 'premium', 'business'];
      
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
    $toast.error(`Failed to update options: ${error.message}`);
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
        : ['free', 'basic', 'premium', 'business'];
      
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
    $toast.error(`Failed to update section: ${error.message || 'Unknown error'}. Please try again or refresh the page.`);
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
        : ['free', 'basic', 'premium', 'business'];
      
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
    $toast.error(`Failed to update design section: ${error.message || 'Unknown error'}. Please try again or refresh the page.`);
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
  
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  formData.value = defaultFormData();
  editingOption.value = null;
  // Restore the catalog tab if this field modal was opened from the section modal
  if (savedCatalogTab.value) {
    currentTab.value = savedCatalogTab.value;
    savedCatalogTab.value = null;
  }
};

const saveOption = async () => {
  if (isFieldTab.value) {
    if (!formData.value.field_key || !formData.value.label) {
      $toast.error('Please fill in all required fields');
      return;
    }
  } else {
    if (!formData.value.name) {
      $toast.error('Please enter a display name');
      return;
    }

    if (modalMode.value === 'add' && !formData.value.option_id) {
      $toast.error('Please enter an option ID');
      return;
    }

    // Auto-generate theme previewCss if missing
    if (currentTab.value === 'theme' && !formData.value.config?.previewCss) {
      syncThemePreviewCss();
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
    $toast.success(`${isFieldTab.value ? 'Field' : 'Option'} ${modalMode.value === 'add' ? 'added' : 'updated'} successfully`);
  } catch (error) {
    console.error('Error saving:', error);
    $toast.error(error.message);
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
    $toast.success(`Option ${option.is_active ? 'hidden' : 'activated'}`);
  } catch (error) {
    console.error('Error toggling status:', error);
    $toast.error(error.message);
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
    $toast.success(`${option.name} is now the default ${currentTabName.value.toLowerCase()}`);
  } catch (error) {
    console.error('Error setting default:', error);
    $toast.error(error.message);
  }
};

const confirmDelete = (option) => {
  const displayName = isFieldTab.value ? option.label : option.name;

  askConfirm(
    {
      title: isFieldTab.value ? 'Delete field' : 'Delete option',
      message: `Are you sure you want to delete "${displayName}"? This action cannot be undone.`,
      confirmLabel: 'Delete',
      tone: 'danger',
    },
    async () => {
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
        $toast.success(`${isFieldTab.value ? 'Field' : 'Option'} deleted successfully`);
      } catch (error) {
        console.error('Error deleting:', error);
        $toast.error(error.message);
      }
    }
  );
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
    $toast.success(`${field.label} updated successfully`);
  } catch (error) {
    console.error('❌ Error updating field:', field.label, error);
    $toast.error(`Failed to update ${field.label}: ${error.message}`);
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
    $toast.error(`Failed to load business users: ${error.message}`);
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
    console.log('📊 User assignment data:', assignment);
    
    const isEmptyCustom = assignment.use_custom && 
      (!assignment.enabled_fields || assignment.enabled_fields.length === 0) &&
      (!assignment.enabled_design_options || assignment.enabled_design_options.length === 0) &&
      (!assignment.enabled_features || assignment.enabled_features.length === 0);
    
    if (isEmptyCustom) {
      console.log('🔔 Detected empty custom settings, using defaults as starting point');
    }
    
    businessUserFormData.value = {
      use_custom: assignment.use_custom || false,
      enabled_fields: isEmptyCustom ? [...(data.data.defaults?.fields || [])] :
        (assignment.use_custom ? (assignment.enabled_fields || []) : [...(data.data.defaults?.fields || [])]),
      enabled_design_options: isEmptyCustom ? [...(data.data.defaults?.design_options || [])] :
        (assignment.use_custom ? (assignment.enabled_design_options || []) : [...(data.data.defaults?.design_options || [])]),
      enabled_features: isEmptyCustom ? [...(data.data.defaults?.features || [])] :
        (assignment.use_custom ? (assignment.enabled_features || []) : [...(data.data.defaults?.features || [])]),
      notes: assignment.notes || '',
    };
    
    console.log('✅ User form initialized with:', {
      use_custom: businessUserFormData.value.use_custom,
      fields_count: businessUserFormData.value.enabled_fields.length,
      design_options_count: businessUserFormData.value.enabled_design_options.length,
      features_count: businessUserFormData.value.enabled_features.length,
      is_empty_custom: isEmptyCustom
    });
    
    showBusinessUserModal.value = true;
  } catch (error) {
    console.error('❌ Error loading user assignment:', error);
    $toast.error(`Failed to load user data: ${error.message}`);
  } finally {
    businessUsersLoading.value = false;
  }
};

const closeBusinessUserModal = () => {
  showBusinessUserModal.value = false;
  selectedBusinessUser.value = null;
};

// Section toggle helpers for User Customization
// 监听自定义模式状态变化，当首次启用时提供适当的初始值
watch(() => businessUserFormData.value.use_custom, (isCustom, wasCustom) => {
  console.log(`🔄 Custom mode ${wasCustom} -> ${isCustom}`);
  
  // 只处理从非自定义到自定义的转换（首次启用自定义）
  if (isCustom && !wasCustom) {
    console.log('✨ First time enabling custom mode, initializing with defaults');
    
    // 判断当前是否是空白设置
    const isEmpty = 
      (!businessUserFormData.value.enabled_fields || businessUserFormData.value.enabled_fields.length === 0) &&
      (!businessUserFormData.value.enabled_design_options || businessUserFormData.value.enabled_design_options.length === 0) &&
      (!businessUserFormData.value.enabled_features || businessUserFormData.value.enabled_features.length === 0);
    
    // 只有在当前设置为空时，才使用默认值填充
    if (isEmpty) {
      console.log('✨ Using default values as starting point');
      // 使用默认设置作为自定义模式的初始值
      businessUserFormData.value.enabled_fields = [...(businessUserAvailableData.value.defaults?.fields || [])];
      businessUserFormData.value.enabled_design_options = [...(businessUserAvailableData.value.defaults?.design_options || [])];
      businessUserFormData.value.enabled_features = [...(businessUserAvailableData.value.defaults?.features || [])];
    } else {
      console.log('✅ Using existing custom settings');
    }
  }
}, { immediate: true }); // 添加 immediate: true 确保初始化时就执行

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
    $toast.success('Settings saved successfully');
  } catch (error) {
    console.error('Error saving:', error);
    $toast.error('Failed to save settings');
  } finally {
    savingBusinessUser.value = false;
  }
};

const resetBusinessUserToDefault = () => {
  askConfirm(
    {
      title: 'Reset to default',
      message: 'Reset this user to the default Business plan settings?',
      confirmLabel: 'Reset',
      tone: 'danger',
    },
    async () => {
      savingBusinessUser.value = true;
      try {
        await fetch(`${config.public.apiBaseUrl}/admin/business-user-assignments/${selectedBusinessUser.value.id}/reset`, {
          method: 'POST',
          headers: { Authorization: `Bearer ${authStore.token}` },
        });

        await loadBusinessUsers();
        closeBusinessUserModal();
        $toast.success('Reset to default settings');
      } catch (error) {
        console.error('Error resetting:', error);
        $toast.error('Failed to reset settings');
      } finally {
        savingBusinessUser.value = false;
      }
    }
  );
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

<style>
.bg-effect-preview-particle {
  animation: preview-particle-drift 4s ease-in-out infinite;
}

.bg-effect-preview-particle:nth-child(2) {
  animation-delay: -1.3s;
}

.bg-effect-preview-particle:nth-child(3) {
  animation-delay: -2.6s;
}

.bg-effect-preview-sparkle {
  animation: preview-sparkle 2.4s ease-in-out infinite;
}

.bg-effect-preview-sparkle:nth-child(2) {
  animation-delay: -0.6s;
}

.bg-effect-preview-sparkle:nth-child(3) {
  animation-delay: -1.2s;
}

.bg-effect-preview-sparkle:nth-child(4) {
  animation-delay: -1.8s;
}

@keyframes preview-particle-drift {
  0%, 100% { opacity: 0.7; transform: translateY(2px) scale(0.9); }
  50% { opacity: 1; transform: translateY(-3px) scale(1.1); }
}

@keyframes preview-sparkle {
  0%, 100% { opacity: 0.55; transform: scale(0.82) rotate(-8deg); }
  50% { opacity: 1; transform: scale(1.08) rotate(8deg); }
}

@keyframes aurora {
  0%   { background-position: 0% 50%; }
  50%  { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

@media (prefers-reduced-motion: reduce) {
  .bg-effect-preview-particle,
  .bg-effect-preview-sparkle {
    animation: none;
  }
}
</style>
