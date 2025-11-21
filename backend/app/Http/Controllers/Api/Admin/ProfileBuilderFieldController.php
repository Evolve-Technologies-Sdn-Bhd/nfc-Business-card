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
     */
    public function index()
    {
        $fields = ProfileBuilderField::orderBy('display_order')->get();

        // Group fields by tab
        $grouped = [
            'profile' => $fields->where('tab', 'profile')->values(),
            'company' => $fields->where('tab', 'company')->values(),
            'services' => $fields->where('tab', 'services')->values(),
            'links' => $fields->where('tab', 'links')->values(),
        ];

        return response()->json([
            'success' => true,
            'data' => $grouped,
        ]);
    }

    /**
     * Store a new field
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tab' => 'required|in:profile,company,services,links',
            'field_key' => 'required|string|max:100|unique:profile_builder_fields,field_key',
            'field_type' => 'required|in:text,email,tel,url,textarea,number,image,repeater',
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
            'tab' => 'sometimes|required|in:profile,company,services,links',
            'field_key' => 'sometimes|required|string|max:100|unique:profile_builder_fields,field_key,' . $id,
            'field_type' => 'sometimes|required|in:text,email,tel,url,textarea,number,image,repeater',
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
