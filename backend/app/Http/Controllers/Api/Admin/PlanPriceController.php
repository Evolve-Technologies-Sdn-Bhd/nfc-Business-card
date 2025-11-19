<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlanPriceController extends Controller
{
    /**
     * Get all plan prices
     */
    public function index()
    {
        try {
            $planPrices = PlanPrice::orderBy('plan_type')->get();

            return response()->json([
                'success' => true,
                'data' => $planPrices,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching plan prices: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch plan prices',
            ], 500);
        }
    }

    /**
     * Get a specific plan price
     */
    public function show($id)
    {
        try {
            $planPrice = PlanPrice::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $planPrice,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching plan price: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Plan price not found',
            ], 404);
        }
    }

    /**
     * Update plan price
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $planPrice = PlanPrice::findOrFail($id);
            
            $planPrice->update($request->only([
                'price',
                'description',
                'features',
                'is_active',
            ]));

            \Log::info('Plan price updated', [
                'plan_type' => $planPrice->plan_type,
                'new_price' => $planPrice->price,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Plan price updated successfully',
                'data' => $planPrice,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating plan price: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update plan price',
            ], 500);
        }
    }

    /**
     * Get public plan prices (for users to view)
     */
    public function publicPrices()
    {
        try {
            $planPrices = PlanPrice::active()->orderBy('plan_type')->get();

            return response()->json([
                'success' => true,
                'data' => $planPrices,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching public plan prices: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch plan prices',
            ], 500);
        }
    }

    /**
     * Bulk update prices
     */
    public function bulkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'updates' => 'required|array',
            'updates.*.id' => 'required|exists:plan_prices,id',
            'updates.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $updates = $request->input('updates');
            $updatedPlans = [];

            foreach ($updates as $update) {
                $planPrice = PlanPrice::findOrFail($update['id']);
                $planPrice->update([
                    'price' => $update['price'],
                ]);
                $updatedPlans[] = $planPrice;
            }

            \Log::info('Bulk plan prices updated', [
                'count' => count($updatedPlans),
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Plan prices updated successfully',
                'data' => $updatedPlans,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error bulk updating plan prices: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update plan prices',
            ], 500);
        }
    }
}
