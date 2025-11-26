<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfileBuilderField;
use App\Models\ProfileBuilderSection;
use App\Models\BusinessUserAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileBuilderFieldController extends Controller
{
    /**
     * Get all profile builder fields grouped by tab
     * Supports filtering by plan parameter
     * For Business users, checks for custom assignments
     */
    public function index(Request $request)
    {
        $plan = $request->query('plan');
        $tab = $request->query('tab');
        $user = auth()->user();
        
        $query = ProfileBuilderField::orderBy('display_order');
        
        // Filter by tab if provided
        if ($tab) {
            $query->where('tab', $tab);
        }
        
        // Check if Business user has custom assignments
        $customFieldIds = null;
        if ($plan === 'business' && $user) {
            $assignment = BusinessUserAssignment::where('user_id', $user->id)->first();
            if ($assignment && $assignment->use_custom) {
                $customFieldIds = $assignment->enabled_fields ?? [];
            }
        }
        
        // If Business user has custom assignments, use those
        if ($customFieldIds !== null) {
            $query->whereIn('id', $customFieldIds);
        } elseif ($plan) {
            // Default plan filtering
            $query->where(function($q) use ($plan) {
                $q->whereJsonContains('available_plans', $plan)
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
     * Returns ALL active sections from database (even without fields)
     * For Business users, checks for custom assignments
     */
    public function getSections(Request $request)
    {
        $plan = $request->query('plan');
        $user = auth()->user();
        
        // Get all active sections from database
        $sectionsQuery = ProfileBuilderSection::active()->orderBy('display_order');
        
        // Filter by plan if specified
        if ($plan) {
            $sectionsQuery->where(function($q) use ($plan) {
                $q->whereJsonContains('available_plans', $plan)
                  ->orWhereNull('available_plans')
                  ->orWhereJsonLength('available_plans', 0);
            });
        }
        
        $dbSections = $sectionsQuery->get();
        
        // Check if Business user has custom assignments
        $customFieldIds = null;
        if ($plan === 'business' && $user) {
            $assignment = BusinessUserAssignment::where('user_id', $user->id)->first();
            if ($assignment && $assignment->use_custom) {
                $customFieldIds = $assignment->enabled_fields ?? [];
            }
        }
        
        // Build field query
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
        
        // Map sections with their fields
        $sections = $dbSections->map(function ($section) use ($fields) {
            $sectionFields = $fields->get($section->key, collect([]))->values();
            
            return [
                'section_key' => $section->key,
                'section_name' => $section->name,
                'icon' => $section->icon,
                'category' => $section->category,
                'description' => $section->description ?? '',
                'display_order' => $section->display_order ?? 999,
                'available_plans' => $section->available_plans ?? [],
                'fields' => $sectionFields,
            ];
        })->values();
        
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

        $data = $request->all();
        // Default to all plans if not specified or empty
        if (empty($data['available_plans'])) {
            $data['available_plans'] = ['basic', 'premium', 'business'];
        }
        
        $field = ProfileBuilderField::create($data);

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
