<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfileDesignOption;
use App\Models\ProfileBuilderField;
use App\Models\ProfileBuilderSection;
use App\Models\BusinessUserAssignment;
use Illuminate\Http\Request;

class ProfileBuilderInitController extends Controller
{
    /**
     * BATCH ENDPOINT: Merge 3 config datasets into ONE HTTP roundtrip for page-load performance.
     * Previously: frontend called /profile-builder-sections + /profile-builder-fields +
     *             /profile-design-options (3 separate network calls).
     * Now:        single call to /profile-builder-init returning all 3 payloads +
     *             a combined structure the frontend uses verbatim.
     * Saves 2x full request bootstrap (Laravel + auth + headers), ~200-800ms on real networks.
     */
    public function index(Request $request)
    {
        $plan = $request->query('plan');
        $user = auth()->user();

        // ─── DATASET 1: Sections with nested fields ──────────────────────────────
        $sections = $this->loadSections($plan, $user);

        // ─── DATASET 2: Fields grouped by tab ────────────────────────────────────
        $fieldsGrouped = $this->loadFieldsGrouped($plan, $user);

        // ─── DATASET 3: Design options grouped by type ───────────────────────────
        $designOptions = $this->loadDesignOptions($plan, $user);

        return response()->json([
            'success' => true,
            'data'    => [
                'sections'       => $sections,
                'fields'         => $fieldsGrouped,
                'design_options' => $designOptions,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Internal loaders — shared logic, no duplicated HTTP overhead
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Mirrors ProfileBuilderFieldController@getSections
     */
    private function loadSections(?string $plan, $user)
    {
        // All active sections ordered by display_order
        $sectionsQuery = ProfileBuilderSection::active()->orderBy('display_order');

        if ($plan) {
            $sectionsQuery->where(function($q) use ($plan) {
                $q->whereJsonContains('available_plans', $plan)
                  ->orWhereNull('available_plans')
                  ->orWhereJsonLength('available_plans', 0);
            });
        }

        $dbSections = $sectionsQuery->get();

        // Business user custom assignment
        $customFieldIds = null;
        if ($plan === 'business' && $user) {
            $assignment = BusinessUserAssignment::where('user_id', $user->id)->first();
            if ($assignment && $assignment->use_custom) {
                $customFieldIds = $assignment->enabled_fields ?? [];
            }
        }

        // Build nested fields query (reuse same custom/plan filter)
        $fieldsQuery = ProfileBuilderField::orderBy('tab')->orderBy('display_order');
        if ($customFieldIds !== null) {
            $fieldsQuery->whereIn('id', $customFieldIds);
        } elseif ($plan) {
            $fieldsQuery->where(function($q) use ($plan) {
                $q->whereJsonContains('available_plans', $plan)
                  ->orWhereNull('available_plans')
                  ->orWhereJsonLength('available_plans', 0);
            });
        }

        $fields = $fieldsQuery->get()->groupBy('tab');

        // Map sections with nested fields
        return $dbSections->map(function ($section) use ($fields) {
            $sectionFields = $fields->get($section->key, collect([]))->values();
            return [
                'section_key'    => $section->key,
                'section_name'   => $section->name,
                'icon'           => $section->icon,
                'category'       => $section->category,
                'description'    => $section->description ?? '',
                'display_order'  => $section->display_order ?? 999,
                'available_plans'=> $section->available_plans ?? [],
                'fields'         => $sectionFields,
            ];
        })->values();
    }

    /**
     * Mirrors ProfileBuilderFieldController@index (grouped-by-tab response)
     */
    private function loadFieldsGrouped(?string $plan, $user)
    {
        $query = ProfileBuilderField::orderBy('display_order');

        // Business user custom assignment
        $customFieldIds = null;
        if ($plan === 'business' && $user) {
            $assignment = BusinessUserAssignment::where('user_id', $user->id)->first();
            if ($assignment && $assignment->use_custom) {
                $customFieldIds = $assignment->enabled_fields ?? [];
            }
        }

        if ($customFieldIds !== null) {
            $query->whereIn('id', $customFieldIds);
        } elseif ($plan) {
            $query->where(function($q) use ($plan) {
                $q->whereJsonContains('available_plans', $plan)
                  ->orWhereNull('available_plans')
                  ->orWhereJsonLength('available_plans', 0);
            });
        }

        $fields = $query->get();

        // Group by tab, strip collection object wrapper for each tab
        return $fields->groupBy('tab')->map(function($tabFields) {
            return $tabFields->values();
        });
    }

    /**
     * Mirrors ProfileDesignController@index (grouped-by-type response)
     */
    private function loadDesignOptions(?string $plan, $user)
    {
        $query = ProfileDesignOption::active()->ordered();

        // Business user custom assignments (design options + feature toggles)
        $customDesignOptionIds = null;
        $customFeatureIds      = null;
        if ($plan === 'business' && $user) {
            $assignment = BusinessUserAssignment::where('user_id', $user->id)->first();
            if ($assignment && $assignment->use_custom) {
                $customDesignOptionIds = $assignment->enabled_design_options ?? [];
                $customFeatureIds      = $assignment->enabled_features ?? [];
            }
        }

        $options = $query->get();

        // Apply filters: custom assignments OR plan-based availability
        if ($customDesignOptionIds !== null) {
            $options = $options->filter(function($option) use ($customDesignOptionIds, $customFeatureIds) {
                if ($option->type === 'feature_toggle') {
                    return in_array($option->option_id, $customFeatureIds ?? []);
                }
                return in_array($option->id, $customDesignOptionIds);
            });
        } elseif ($plan && \Schema::hasColumn('profile_design_options', 'available_plans')) {
            $options = $options->filter(function($option) use ($plan) {
                return $option->isAvailableForPlan($plan);
            });
        }

        return $options->groupBy('type');
    }
}
