<?php

namespace App\Http\Controllers\Api\Business;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\NfcCard;
use Illuminate\Http\Request;

class QuotaController extends Controller
{
    /**
     * Get card quota information for the current Business account
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCardQuota(Request $request)
    {
        $user = auth()->user();

        // Determine if user is Business account or employee
        if ($user->subscription_plan === 'business' && !$user->parent_business_id) {
            // User is Business account owner
            $businessAccountId = $user->id;
            $businessAccount = $user;
        } elseif ($user->parent_business_id) {
            // User is employee under Business account
            $businessAccount = User::find($user->parent_business_id);

            if (!$businessAccount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Business account not found'
                ], 404);
            }

            $businessAccountId = $businessAccount->id;
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Only Business accounts and their employees can access this endpoint'
            ], 403);
        }

        // Get quota information from Business account
        $quotaInfo = $businessAccount->getQuotaInfo();

        return response()->json([
            'success' => true,
            'data' => [
                'total_card_quota' => $quotaInfo['total_card_quota'],
                'ordered_cards_count' => $quotaInfo['ordered_cards_count'],
                'available_card_quota' => $quotaInfo['available_card_quota'],
                'business_account_id' => $businessAccountId,
                'account_info' => [
                    'total_account_slots' => $quotaInfo['total_account_slots'],
                    'employees_count' => $quotaInfo['employees_count'],
                    'available_account_slots' => $quotaInfo['available_account_slots'],
                ]
            ]
        ]);
    }

    /**
     * Get employees list with quota information
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmployees(Request $request)
    {
        // Resolve the Business account (owner) — works for the owner and for
        // employees delegated the `admin` business role.
        $businessAccount = $request->user()->resolvedBusinessAccount();

        if (!$businessAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Only Business accounts and their delegated managers can access this endpoint'
            ], 403);
        }

        // Get employees
        $employees = $businessAccount->employees()
            ->select('id', 'first_name', 'last_name', 'email', 'job_title', 'phone', 'created_at', 'subscription_active', 'business_role')
            ->orderBy('created_at', 'desc')
            ->get();

        // Resolve display name and effective role for each employee.
        $employees->each(function ($employee) {
            $employee->full_name = $employee->full_name;
            $employee->business_role = $employee->businessRole();
        });

        // Get quota info
        $quotaInfo = $businessAccount->getQuotaInfo();

        return response()->json([
            'success' => true,
            'data' => [
                'employees' => $employees,
                'quota_info' => $quotaInfo,
                'viewer_role' => $request->user()->businessRole(),
                'is_owner' => $request->user()->isBusinessAccount(),
            ]
        ]);
    }

    /**
     * Create new employee under Business account
     * 
     * NOTE: This endpoint is deprecated. Employee accounts should be created by Super Admin.
     * Business accounts can only upload employee data for NFC card orders via /business/employees/upload
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createEmployee(Request $request)
    {
        // Employee accounts must be created by Super Admin
        return response()->json([
            'success' => false,
            'message' => 'Employee accounts must be created by Super Admin. Business accounts can upload employee data for NFC card orders via /business/employees/upload endpoint.',
            'action_required' => 'Please contact Super Admin to create employee accounts.'
        ], 403);
        
        /* DEPRECATED CODE - Keep for reference
        $user = auth()->user();

        // Validate user is Business account
        if (!$user->isBusinessAccount()) {
            return response()->json([
                'success' => false,
                'message' => 'Only Business account owners can create employees'
            ], 403);
        }

        // Check quota
        $quotaInfo = $user->getQuotaInfo();

        if ($quotaInfo['available_account_slots'] <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot create employee. Account slots quota exceeded.',
                'data' => [
                    'total_account_slots' => $quotaInfo['total_account_slots'],
                    'employees_count' => $quotaInfo['employees_count'],
                    'available_account_slots' => 0
                ]
            ], 403);
        }

        // Validate request
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'job_title' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create employee
        $employee = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'job_title' => $validated['job_title'],
            'password' => bcrypt($validated['password']),
            'subscription_plan' => 'business',
            'subscription_active' => true,
            'parent_business_id' => $user->id,
            'company' => $user->company, // Inherit company from Business account
        ]);

        // Get updated quota info
        $updatedQuotaInfo = $user->getQuotaInfo();

        return response()->json([
            'success' => true,
            'message' => 'Employee created successfully',
            'data' => [
                'employee' => [
                    'id' => $employee->id,
                    'full_name' => $employee->full_name,
                    'email' => $employee->email,
                    'job_title' => $employee->job_title
                ],
                'remaining_slots' => $updatedQuotaInfo['available_account_slots']
            ]
        ], 201);
        */
    }

    /**
     * Delete employee
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteEmployee(Request $request, $id)
    {
        $businessAccount = $request->user()->resolvedBusinessAccount();

        if (!$businessAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Only Business accounts and their delegated managers can delete employees'
            ], 403);
        }

        // Find employee
        $employee = User::where('id', $id)
            ->where('parent_business_id', $businessAccount->id)
            ->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found or does not belong to your account'
            ], 404);
        }

        if ($denied = EmployeeController::denyUnlessCanActOn($request->user(), $employee)) {
            return $denied;
        }

        // Cards the member bought themself (not company cards) still block the
        // removal: those belong to the person, not to the company.
        $hasPersonalCards = NfcCard::where('user_id', $employee->id)
            ->where(function ($q) use ($businessAccount) {
                $q->whereNull('business_account_id')
                  ->orWhere('business_account_id', '!=', $businessAccount->id);
            })
            ->exists();

        if ($hasPersonalCards) {
            return response()->json([
                'success' => false,
                'message' => 'This member has NFC cards that do not belong to the company. Ask NFCGo support to move them first.'
            ], 403);
        }

        // Company cards go back to the owner, with the member's public profile
        // taken offline so their details stop showing on a card they no longer hold.
        $returnedCards = \Illuminate\Support\Facades\DB::transaction(function () use ($employee, $businessAccount) {
            $cardIds = NfcCard::where('user_id', $employee->id)
                ->where('business_account_id', $businessAccount->id)
                ->pluck('id');

            NfcCard::whereIn('id', $cardIds)->update(['user_id' => $businessAccount->id]);
            \App\Models\LandingPage::whereIn('nfc_card_id', $cardIds)->update(['is_active' => false]);

            $employee->tokens()->delete();
            // Deleting an employee frees their account slot.
            $employee->delete();

            return $cardIds->count();
        });

        return response()->json([
            'success' => true,
            'message' => $returnedCards > 0
                ? "Member removed. {$returnedCards} company card(s) returned to you for reassignment."
                : 'Member removed.',
            'data' => [
                'returned_cards' => $returnedCards,
                'quota_info' => $businessAccount->getQuotaInfo(false),
            ],
        ]);
    }

    /**
     * Check if card can be ordered (quota validation)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function canOrderCard(Request $request)
    {
        $user = auth()->user();

        // Get business account
        if ($user->isBusinessAccount()) {
            $businessAccount = $user;
        } elseif ($user->isBusinessEmployee()) {
            $businessAccount = $user->parentBusiness;
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Only Business accounts and employees can order Business Plan cards'
            ], 403);
        }

        // Get quota info
        $quotaInfo = $businessAccount->getQuotaInfo();

        $canOrder = $quotaInfo['available_card_quota'] > 0;

        return response()->json([
            'success' => true,
            'data' => [
                'can_order' => $canOrder,
                'total_card_quota' => $quotaInfo['total_card_quota'],
                'ordered_cards_count' => $quotaInfo['ordered_cards_count'],
                'available_card_quota' => $quotaInfo['available_card_quota'],
                'message' => $canOrder 
                    ? 'Card can be ordered' 
                    : 'Card quota exceeded. Please contact admin to increase quota.'
            ]
        ]);
    }
}
