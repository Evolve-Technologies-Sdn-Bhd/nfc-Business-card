<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\NfcCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BusinessUserController extends Controller
{
    /**
     * Get all Business Plan users with pagination
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $search = $request->input('search');
        $status = $request->input('status');

        $query = User::where('subscription_plan', 'business')
            ->whereNull('parent_business_id')
            ->where(function($q) {
                $q->whereNull('admin_role')
                  ->orWhere('admin_role', '!=', 'super_admin');
            }); // Exclude only super_admin, show regular admins with business plan

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status === 'active') {
            $query->where('subscription_active', true);
        } elseif ($status === 'inactive') {
            $query->where('subscription_active', false);
        }

        $users = $query->with(['employees'])
            ->withCount(['employees'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Add quota info and cards count to each user
        $users->getCollection()->transform(function ($user) {
            $user->quota_info = $user->getQuotaInfo();
            // Count all cards for this business (admin + employees)
            $user->cards_count = NfcCard::where('business_account_id', $user->id)->count();
            return $user;
        });

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'per_page' => $users->perPage(),
            'total' => $users->total(),
        ]);
    }

    /**
     * Get statistics for Business Plan users
     */
    public function statistics()
    {
        $totalBusinessUsers = User::where('subscription_plan', 'business')
            ->whereNull('parent_business_id')
            ->where(function($q) {
                $q->whereNull('admin_role')
                  ->orWhere('admin_role', '!=', 'super_admin');
            }) // Exclude only super_admin
            ->count();

        $totalEmployees = User::whereNotNull('parent_business_id')->count();

        $activeAccounts = User::where('subscription_plan', 'business')
            ->whereNull('parent_business_id')
            ->where(function($q) {
                $q->whereNull('admin_role')
                  ->orWhere('admin_role', '!=', 'super_admin');
            }) // Exclude only super_admin
            ->where('subscription_active', true)
            ->count();

        $totalCards = NfcCard::where('subscription_plan', 'business')->count();

        return response()->json([
            'success' => true,
            'statistics' => [
                'total_business_users' => $totalBusinessUsers,
                'total_employees' => $totalEmployees,
                'active_accounts' => $activeAccounts,
                'total_cards' => $totalCards,
            ],
        ]);
    }

    /**
     * Get single Business user details
     */
    public function show($id)
    {
        $user = User::where('subscription_plan', 'business')
            ->whereNull('parent_business_id')
            ->where(function($q) {
                $q->whereNull('admin_role')
                  ->orWhere('admin_role', '!=', 'super_admin');
            }) // Exclude only super_admin
            ->with(['employees'])
            ->withCount(['employees'])
            ->findOrFail($id);

        // Get all NFC cards for this business (admin + employees)
        $user->nfc_cards = NfcCard::where('business_account_id', $user->id)->get();
        $user->cards_count = $user->nfc_cards->count();
        $user->quota_info = $user->getQuotaInfo();

        // Add cards info to each employee
        foreach ($user->employees as $employee) {
            $employeeCards = NfcCard::where('user_id', $employee->id)->get();
            $employee->nfc_cards = $employeeCards;
            $employee->cards_count = $employeeCards->count();
        }

        return response()->json([
            'success' => true,
            'user' => $user,
        ]);
    }

    /**
     * Create employee account for a Business user
     */
    public function createEmployee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_account_id' => 'required|exists:users,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'job_title' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Get business account
        $businessAccount = User::where('subscription_plan', 'business')
            ->whereNull('parent_business_id')
            ->findOrFail($request->business_account_id);

        // Check quota
        $quotaInfo = $businessAccount->getQuotaInfo();
        if ($quotaInfo['available_quota'] <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Business account has reached its quota limit.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Create employee user
            $employee = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'job_title' => $request->job_title,
                'company' => $businessAccount->company,
                'subscription_plan' => 'premium',
                'subscription_active' => true,
                'subscription_start_date' => $businessAccount->subscription_start_date,
                'subscription_end_date' => $businessAccount->subscription_end_date,
                'parent_business_id' => $businessAccount->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Employee account created successfully',
                'employee' => $employee,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create employee account: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk create employee accounts for a Business user
     */
    public function createEmployeesBulk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_account_id' => 'required|exists:users,id',
            'employees' => 'required|array|min:1',
            'employees.*.first_name' => 'required|string|max:255',
            'employees.*.last_name' => 'required|string|max:255',
            'employees.*.email' => 'required|email',
            'employees.*.password' => 'required|string|min:8',
            'employees.*.phone' => 'nullable|string|max:20',
            'employees.*.job_title' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Get business account
        $businessAccount = User::where('subscription_plan', 'business')
            ->whereNull('parent_business_id')
            ->findOrFail($request->business_account_id);

        // Check quota
        $quotaInfo = $businessAccount->getQuotaInfo();
        $employeeCount = count($request->employees);
        
        if ($quotaInfo['available_quota'] < $employeeCount) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient quota. Available: {$quotaInfo['available_quota']}, Requested: {$employeeCount}",
            ], 400);
        }

        // Check for duplicate emails
        $emails = array_column($request->employees, 'email');
        $existingEmails = User::whereIn('email', $emails)->pluck('email')->toArray();
        
        if (!empty($existingEmails)) {
            return response()->json([
                'success' => false,
                'message' => 'Some email addresses already exist: ' . implode(', ', $existingEmails),
            ], 400);
        }

        try {
            DB::beginTransaction();

            $createdEmployees = [];
            foreach ($request->employees as $employeeData) {
                $employee = User::create([
                    'first_name' => $employeeData['first_name'],
                    'last_name' => $employeeData['last_name'],
                    'email' => $employeeData['email'],
                    'password' => Hash::make($employeeData['password']),
                    'phone' => $employeeData['phone'] ?? null,
                    'job_title' => $employeeData['job_title'] ?? null,
                    'company' => $businessAccount->company,
                    'subscription_plan' => 'premium',
                    'subscription_active' => true,
                    'subscription_start_date' => $businessAccount->subscription_start_date,
                    'subscription_end_date' => $businessAccount->subscription_end_date,
                    'parent_business_id' => $businessAccount->id,
                ]);

                $createdEmployees[] = $employee;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully created {$employeeCount} employee accounts",
                'employees' => $createdEmployees,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create employee accounts: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update quota for Business user
     */
    public function updateQuota(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'total_account_slots' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('subscription_plan', 'business')
            ->whereNull('parent_business_id')
            ->findOrFail($id);

        // Check if new quota is less than current usage
        $quotaInfo = $user->getQuotaInfo();
        $currentUsage = $quotaInfo['total_accounts'];

        if ($request->total_account_slots < $currentUsage) {
            return response()->json([
                'success' => false,
                'message' => "Cannot set quota below current usage ({$currentUsage} accounts).",
            ], 400);
        }

        $user->update([
            'total_account_slots' => $request->total_account_slots,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Quota updated successfully',
            'user' => $user->fresh(),
        ]);
    }

    /**
     * Delete employee account
     */
    public function deleteEmployee($id)
    {
        $employee = User::whereNotNull('parent_business_id')->findOrFail($id);

        try {
            DB::beginTransaction();

            // Delete employee's NFC cards
            $employee->nfcCards()->delete();

            // Delete employee account
            $employee->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Employee account deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete employee account: ' . $e->getMessage(),
            ], 500);
        }
    }
}
