<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfileBuilderSection;
use App\Models\ProfileBuilderField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProfileBuilderSectionController extends Controller
{
    /**
     * Get all sections
     */
    public function index(Request $request)
    {
        $category = $request->query('category');
        
        $query = ProfileBuilderSection::orderBy('display_order');
        
        if ($category) {
            $query->where('category', $category);
        }
        
        $sections = $query->get();
        
        // Add field count to each section
        $sections = $sections->map(function ($section) {
            $section->fields_count = ProfileBuilderField::where('tab', $section->key)->count();
            return $section;
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'sections' => $sections,
                'field_types' => config('profile_sections.field_types', []),
            ],
        ]);
    }

    /**
     * Get a single section
     */
    public function show($id)
    {
        $section = ProfileBuilderSection::find($id);
        
        if (!$section) {
            // Try finding by key
            $section = ProfileBuilderSection::where('key', $id)->first();
        }
        
        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found',
            ], 404);
        }
        
        // Add fields
        $section->fields = ProfileBuilderField::where('tab', $section->key)
            ->orderBy('display_order')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $section,
        ]);
    }

    /**
     * Create a new section
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|max:50|unique:profile_builder_sections,key|regex:/^[a-z][a-z0-9_]*$/',
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:100',
            'category' => 'nullable|string|in:general,design',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
            'available_plans' => 'nullable|array',
            'has_fields' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->all();
        $data['icon'] = $data['icon'] ?? 'heroicons:document-text';
        $data['category'] = $data['category'] ?? 'general';
        $data['display_order'] = $data['display_order'] ?? ProfileBuilderSection::max('display_order') + 1;
        $data['has_fields'] = $data['has_fields'] ?? true;
        $data['is_active'] = $data['is_active'] ?? true;
        // Default to all plans if not specified
        $data['available_plans'] = $data['available_plans'] ?? ['basic', 'premium', 'business'];
        
        $section = ProfileBuilderSection::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Section created successfully',
            'data' => $section,
        ], 201);
    }

    /**
     * Update a section
     */
    public function update(Request $request, $id)
    {
        $section = ProfileBuilderSection::find($id);
        
        if (!$section) {
            // Try finding by key
            $section = ProfileBuilderSection::where('key', $id)->first();
        }
        
        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:100',
            'icon' => 'nullable|string|max:100',
            'category' => 'nullable|string|in:general,design',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
            'available_plans' => 'nullable|array',
            'has_fields' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Don't allow changing the key
        $data = $request->except('key');
        
        $section->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Section updated successfully',
            'data' => $section,
        ]);
    }

    /**
     * Delete a section
     */
    public function destroy($id)
    {
        $section = ProfileBuilderSection::find($id);
        
        if (!$section) {
            // Try finding by key
            $section = ProfileBuilderSection::where('key', $id)->first();
        }
        
        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found',
            ], 404);
        }

        DB::beginTransaction();
        
        try {
            // Delete all fields in this section
            $deletedFieldsCount = ProfileBuilderField::where('tab', $section->key)->delete();
            
            // Delete the section
            $section->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Section deleted successfully. {$deletedFieldsCount} field(s) were also deleted.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete section: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reorder sections
     */
    public function reorder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sections' => 'required|array',
            'sections.*.id' => 'required|exists:profile_builder_sections,id',
            'sections.*.display_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        
        try {
            foreach ($request->sections as $sectionData) {
                ProfileBuilderSection::where('id', $sectionData['id'])
                    ->update(['display_order' => $sectionData['display_order']]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Sections reordered successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder sections: ' . $e->getMessage(),
            ], 500);
        }
    }
}
