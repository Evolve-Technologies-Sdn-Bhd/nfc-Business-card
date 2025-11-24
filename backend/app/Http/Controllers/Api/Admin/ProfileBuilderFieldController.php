<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfileBuilderField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileBuilderFieldController extends Controller
{
    /**
     * Get all profile builder fields grouped by tab
     * Supports filtering by plan parameter
     */
    public function index(Request $request)
    {
        $plan = $request->query('plan');
        $tab = $request->query('tab');
        
        $query = ProfileBuilderField::orderBy('display_order');
        
        // Filter by tab if provided
        if ($tab) {
            $query->where('tab', $tab);
        }
        
        // If plan parameter is provided, filter fields by plan availability
        if ($plan) {
            $query->where(function($q) use ($plan) {
                // Include fields that have this plan in available_plans
                $q->whereJsonContains('available_plans', $plan)
                  // OR fields with empty/null available_plans (available for all)
                  ->orWhereNull('available_plans')
                  ->orWhereJsonLength('available_plans', 0);
            });
        }
        
        $fields = $query->get();

        // If tab filter is applied, return flat array
        if ($tab) {
            return response()->json([
                'success' => true,
                'data' => $fields,
            ]);
        }

        // Group fields by tab
        $grouped = $fields->groupBy('tab')->map(function($tabFields) {
            return $tabFields->values();
        });

        return response()->json([
            'success' => true,
            'data' => $grouped,
        ]);
    }

    /**
     * Get all sections with their fields
     * Groups fields by tab and includes section metadata from config
     */
    public function getSections(Request $request)
    {
        $plan = $request->query('plan');
        
        $query = ProfileBuilderField::orderBy('tab')->orderBy('display_order');
        
        // If plan parameter is provided, filter fields by plan availability
        if ($plan) {
            $query->where(function($q) use ($plan) {
                $q->whereJsonContains('available_plans', $plan)
                  ->orWhereNull('available_plans')
                  ->orWhereJsonLength('available_plans', 0);
            });
        }
        
        $fields = $query->get();
        
        // Group by tab (section)
        $sections = $fields->groupBy('tab')->map(function ($sectionFields, $tabKey) {
            // Get section metadata from config file
            $sectionConfig = config("profile_sections.sections.{$tabKey}", [
                'name' => ucfirst($tabKey),
                'icon' => 'heroicons:folder',
                'category' => 'general',
                'description' => '',
                'display_order' => 999,
            ]);
            
            return [
                'section_key' => $tabKey,
                'section_name' => $sectionConfig['name'],
                'icon' => $sectionConfig['icon'],
                'category' => $sectionConfig['category'],
                'description' => $sectionConfig['description'] ?? '',
                'display_order' => $sectionConfig['display_order'] ?? 999,
                'available_plans' => $sectionConfig['available_plans'] ?? [],
                'fields' => $sectionFields->values(),
            ];
        })->sortBy('display_order')->values();
        
        return response()->json([
            'success' => true,
            'data' => $sections,
        ]);
    }

    /**
     * Get available section types
     */
    public function getAvailableSections()
    {
        $sections = config('profile_sections.sections', []);
        
        // Get sections that have at least one field
        $usedSections = ProfileBuilderField::select('tab')
            ->distinct()
            ->pluck('tab')
            ->toArray();
        
        // Format sections data
        $formattedSections = collect($sections)->map(function($config, $key) use ($usedSections) {
            return [
                'key' => $key,
                'name' => $config['name'],
                'icon' => $config['icon'],
                'category' => $config['category'],
                'description' => $config['description'] ?? '',
                'display_order' => $config['display_order'] ?? 999,
                'available_plans' => $config['available_plans'] ?? [],
                'has_fields' => in_array($key, $usedSections),
            ];
        })->sortBy('display_order')->values();
        
        return response()->json([
            'success' => true,
            'data' => [
                'sections' => $formattedSections,
                'field_types' => config('profile_sections.field_types', []),
            ],
        ]);
    }

    /**
     * Store a new field
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tab' => 'required|string|max:50',
            'field_key' => 'required|string|max:100|unique:profile_builder_fields,field_key',
            'field_type' => 'required|in:text,email,tel,url,textarea,richtext,number,date,select,image,video,file,gallery,repeater,toggle,checkbox,icon,color',
            'label' => 'required|string|max:255',
            'placeholder' => 'nullable|string',
            'help_text' => 'nullable|string',
            'is_required' => 'boolean',
            'is_visible' => 'boolean',
            'validation_rules' => 'nullable|array',
            'available_plans' => 'nullable|array',
            'display_order' => 'integer|min:0',
            'config' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $field = ProfileBuilderField::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Field created successfully',
            'data' => $field,
        ], 201);
    }

    /**
     * Update an existing field
     */
    public function update(Request $request, $id)
    {
        $field = ProfileBuilderField::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'tab' => 'sometimes|required|string|max:50',
            'field_key' => 'sometimes|required|string|max:100|unique:profile_builder_fields,field_key,' . $id,
            'field_type' => 'sometimes|required|in:text,email,tel,url,textarea,richtext,number,date,select,image,video,file,gallery,repeater,toggle,checkbox,icon,color',
            'label' => 'sometimes|required|string|max:255',
            'placeholder' => 'nullable|string',
            'help_text' => 'nullable|string',
            'is_required' => 'boolean',
            'is_visible' => 'boolean',
            'validation_rules' => 'nullable|array',
            'available_plans' => 'nullable|array',
            'display_order' => 'integer|min:0',
            'config' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $field->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Field updated successfully',
            'data' => $field,
        ]);
    }

    /**
     * Delete a field
     */
    public function destroy($id)
    {
        $field = ProfileBuilderField::findOrFail($id);
        $field->delete();

        return response()->json([
            'success' => true,
            'message' => 'Field deleted successfully',
        ]);
    }
}
