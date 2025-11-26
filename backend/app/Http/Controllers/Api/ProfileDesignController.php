<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProfileDesignOption;
use App\Models\BusinessUserAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileDesignController extends Controller
{
    /**
     * Get all design options (for users)
     * Returns only active options filtered by user's plan
     * For Business users, checks for custom assignments
     */
    public function index(Request $request)
    {
        $type = $request->query('type');
        $plan = $request->query('plan');
        $user = auth()->user();
        
        $query = ProfileDesignOption::active()->ordered();
        
        if ($type) {
            $query->ofType($type);
        }
        
        // Check if Business user has custom assignments
        $customDesignOptionIds = null;
        $customFeatureIds = null;
        if ($plan === 'business' && $user) {
            $assignment = BusinessUserAssignment::where('user_id', $user->id)->first();
            if ($assignment && $assignment->use_custom) {
                $customDesignOptionIds = $assignment->enabled_design_options ?? [];
                $customFeatureIds = $assignment->enabled_features ?? [];
            }
        }
        
        // Get options
        $options = $query->get();
        
        // Filter based on custom assignments or plan
        if ($customDesignOptionIds !== null) {
            $options = $options->filter(function($option) use ($customDesignOptionIds, $customFeatureIds) {
                if ($option->type === 'feature_toggle') {
                    return in_array($option->option_id, $customFeatureIds);
                }
                return in_array($option->id, $customDesignOptionIds);
            });
        } elseif ($plan && \Schema::hasColumn('profile_design_options', 'available_plans')) {
            $options = $options->filter(function($option) use ($plan) {
                return $option->isAvailableForPlan($plan);
            });
        }
        
        $grouped = $options->groupBy('type');
        
        return response()->json([
            'success' => true,
            'data' => $grouped,
        ]);
    }

    /**
     * Get all design options (for admin)
     * Returns all options including inactive
     */
    public function adminIndex(Request $request)
    {
        $type = $request->query('type');
        
        $query = ProfileDesignOption::query()->ordered();
        
        if ($type) {
            $query->ofType($type);
        }
        
        $options = $query->get()->groupBy('type');
        
        return response()->json([
            'success' => true,
            'data' => $options,
        ]);
    }

    /**
     * Get all available option types (admin only)
     */
    public function getTypes()
    {
        $types = ProfileDesignOption::select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');
        
        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    /**
     * Create a new design option (admin only)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|max:100',
            'option_id' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'config' => 'nullable|array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'available_plans' => 'nullable|array',
            'available_plans.*' => 'in:basic,premium,business',
            'display_order' => 'integer',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if option_id already exists for this type
        $exists = ProfileDesignOption::where('type', $request->type)
            ->where('option_id', $request->option_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This option ID already exists for this type',
            ], 422);
        }

        // If setting as default, unset other defaults for this type
        if ($request->is_default) {
            ProfileDesignOption::where('type', $request->type)
                ->update(['is_default' => false]);
        }

        $option = ProfileDesignOption::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Design option created successfully',
            'data' => $option,
        ], 201);
    }

    /**
     * Update a design option (admin only)
     */
    public function update(Request $request, $id)
    {
        $option = ProfileDesignOption::find($id);

        if (!$option) {
            return response()->json([
                'success' => false,
                'message' => 'Design option not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'config' => 'sometimes|nullable|array',
            'is_active' => 'sometimes|boolean',
            'is_default' => 'sometimes|boolean',
            'display_order' => 'sometimes|integer',
            'description' => 'sometimes|nullable|string',
            'available_plans' => 'sometimes|nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // If setting as default, unset other defaults for this type
        if ($request->has('is_default') && $request->is_default) {
            ProfileDesignOption::where('type', $option->type)
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $option->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Design option updated successfully',
            'data' => $option,
        ]);
    }

    /**
     * Delete a design option (admin only)
     */
    public function destroy($id)
    {
        $option = ProfileDesignOption::find($id);

        if (!$option) {
            return response()->json([
                'success' => false,
                'message' => 'Design option not found',
            ], 404);
        }

        // Prevent deleting if it's the default and only active option of its type
        if ($option->is_default && $option->is_active) {
            $activeCount = ProfileDesignOption::where('type', $option->type)
                ->where('is_active', true)
                ->count();

            if ($activeCount <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete the only active option. Please add or activate another option first.',
                ], 422);
            }
        }

        $option->delete();

        return response()->json([
            'success' => true,
            'message' => 'Design option deleted successfully',
        ]);
    }

    /**
     * Toggle active status (admin only)
     */
    public function toggleActive($id)
    {
        $option = ProfileDesignOption::find($id);

        if (!$option) {
            return response()->json([
                'success' => false,
                'message' => 'Design option not found',
            ], 404);
        }

        // Prevent deactivating if it's the only active option of its type
        if ($option->is_active) {
            $activeCount = ProfileDesignOption::where('type', $option->type)
                ->where('is_active', true)
                ->count();

            if ($activeCount <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deactivate the only active option',
                ], 422);
            }
        }

        $option->is_active = !$option->is_active;
        $option->save();

        return response()->json([
            'success' => true,
            'message' => 'Option status updated successfully',
            'data' => $option,
        ]);
    }

    /**
     * Set as default (admin only)
     */
    public function setDefault($id)
    {
        $option = ProfileDesignOption::find($id);

        if (!$option) {
            return response()->json([
                'success' => false,
                'message' => 'Design option not found',
            ], 404);
        }

        // Unset other defaults for this type
        ProfileDesignOption::where('type', $option->type)
            ->update(['is_default' => false]);

        $option->is_default = true;
        $option->is_active = true; // Auto-activate when setting as default
        $option->save();

        return response()->json([
            'success' => true,
            'message' => 'Default option updated successfully',
            'data' => $option,
        ]);
    }

    /**
     * Reorder options (admin only)
     */
    public function reorder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'options' => 'required|array',
            'options.*.id' => 'required|exists:profile_design_options,id',
            'options.*.display_order' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        foreach ($request->options as $item) {
            ProfileDesignOption::where('id', $item['id'])
                ->update(['display_order' => $item['display_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Options reordered successfully',
        ]);
    }
}
