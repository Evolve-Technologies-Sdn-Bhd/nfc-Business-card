<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessUserAssignment;
use App\Models\User;
use App\Models\ProfileBuilderField;
use App\Models\ProfileDesignOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BusinessUserAssignmentController extends Controller
{
    /**
     * Get all business users with their assignment status.
     */
    public function index()
    {
        try {
            // Get only users with business plan (not employees, not admins)
            $businessUsers = User::where('subscription_plan', 'business')
                ->whereNull('parent_business_id') // Exclude employees
                ->where('is_admin', false) // Exclude admin users
                ->with('businessUserAssignment')
                ->get()
                ->map(function ($user) {
                    $assignment = $user->businessUserAssignment;
                    return [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'email' => $user->email,
                        'company' => $user->company ?? null,
                        'has_custom' => $assignment?->use_custom ?? false,
                        'created_at' => $user->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $businessUsers,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading business users: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load business users',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get assignment details for a specific business user.
     */
    public function show($userId)
    {
        $user = User::findOrFail($userId);
        
        // Get or create assignment
        $assignment = BusinessUserAssignment::getOrCreateForUser($userId);
        
        // Get all available fields grouped by tab (only business plan fields)
        $allFields = ProfileBuilderField::where('is_visible', true)
            ->whereJsonContains('available_plans', 'business')
            ->orderBy('display_order')
            ->get()
            ->groupBy('tab');
        
        // Get all design options grouped by type (only business plan options)
        $allDesignOptions = ProfileDesignOption::where('is_active', true)
            ->whereJsonContains('available_plans', 'business')
            ->orderBy('display_order')
            ->get()
            ->groupBy('type');
        
        // Get default business plan settings
        $defaultFields = ProfileBuilderField::where('is_visible', true)
            ->whereJsonContains('available_plans', 'business')
            ->pluck('id')
            ->toArray();
            
        $defaultDesignOptions = ProfileDesignOption::where('is_active', true)
            ->where('type', '!=', 'feature_toggle')
            ->whereJsonContains('available_plans', 'business')
            ->pluck('id')
            ->toArray();
            
        $defaultFeatures = ProfileDesignOption::where('is_active', true)
            ->where('type', 'feature_toggle')
            ->whereJsonContains('available_plans', 'business')
            ->pluck('option_id')
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'company' => $user->company_name ?? $user->businessAccount?->company_name ?? null,
                ],
                'assignment' => $assignment,
                'defaults' => [
                    'fields' => $defaultFields,
                    'design_options' => $defaultDesignOptions,
                    'features' => $defaultFeatures,
                ],
                'available' => [
                    'fields' => $allFields,
                    'design_options' => $allDesignOptions,
                ],
            ],
        ]);
    }

    /**
     * Update assignment for a specific business user.
     */
    public function update(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        $validated = $request->validate([
            'use_custom' => 'required|boolean',
            'enabled_fields' => 'nullable|array',
            'enabled_fields.*' => 'integer',
            'enabled_design_options' => 'nullable|array',
            'enabled_design_options.*' => 'integer',
            'enabled_features' => 'nullable|array',
            'enabled_features.*' => 'string',
            'notes' => 'nullable|string|max:1000',
        ]);

        $assignment = BusinessUserAssignment::updateOrCreate(
            ['user_id' => $userId],
            [
                'use_custom' => $validated['use_custom'],
                'enabled_fields' => $validated['enabled_fields'] ?? [],
                'enabled_design_options' => $validated['enabled_design_options'] ?? [],
                'enabled_features' => $validated['enabled_features'] ?? [],
                'notes' => $validated['notes'] ?? null,
            ]
        );

        Log::info("Business user assignment updated", [
            'user_id' => $userId,
            'use_custom' => $validated['use_custom'],
            'admin_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Assignment updated successfully',
            'data' => $assignment,
        ]);
    }

    /**
     * Reset a business user to default plan settings.
     */
    public function resetToDefault($userId)
    {
        $assignment = BusinessUserAssignment::where('user_id', $userId)->first();
        
        if ($assignment) {
            $assignment->update([
                'use_custom' => false,
                'enabled_fields' => [],
                'enabled_design_options' => [],
                'enabled_features' => [],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reset to default business plan settings',
        ]);
    }

    /**
     * Copy settings from one business user to another.
     */
    public function copyFrom(Request $request, $userId)
    {
        $validated = $request->validate([
            'source_user_id' => 'required|integer|exists:users,id',
        ]);

        $sourceAssignment = BusinessUserAssignment::where('user_id', $validated['source_user_id'])->first();
        
        if (!$sourceAssignment || !$sourceAssignment->use_custom) {
            return response()->json([
                'success' => false,
                'message' => 'Source user does not have custom settings',
            ], 400);
        }

        BusinessUserAssignment::updateOrCreate(
            ['user_id' => $userId],
            [
                'use_custom' => true,
                'enabled_fields' => $sourceAssignment->enabled_fields,
                'enabled_design_options' => $sourceAssignment->enabled_design_options,
                'enabled_features' => $sourceAssignment->enabled_features,
                'notes' => "Copied from user #{$validated['source_user_id']}",
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Settings copied successfully',
        ]);
    }
}
