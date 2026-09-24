<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LandingPage;
use App\Models\NfcCard;
use App\Models\NfcTag;
use App\Models\Analytics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Get admin dashboard overview
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();

        // Get total counts
        $totalUsers = User::count();
        $totalLandingPages = LandingPage::count();
        $totalNfcCards = NfcCard::count();
        $totalNfcTags = NfcTag::count();
        $totalAnalytics = Analytics::count();

        // Get recent registrations
        $recentUsers = User::with('nfcCards.landingPage')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get subscription breakdown
        $subscriptionBreakdown = User::selectRaw('subscription_plan, COUNT(*) as count')
            ->groupBy('subscription_plan')
            ->get();

        // Get recent activity
        $recentActivity = Analytics::with('trackable')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get system stats
        $systemStats = [
            'total_storage_used' => $this->getStorageUsage(),
            'active_sessions' => DB::table('sessions')->count(),
            'last_backup' => now()->subDays(2)->format('Y-m-d H:i:s'), // Mock data
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'overview' => [
                    'total_users' => $totalUsers,
                    'total_landing_pages' => $totalLandingPages,
                    'total_nfc_cards' => $totalNfcCards,
                    'total_nfc_tags' => $totalNfcTags,
                    'total_analytics' => $totalAnalytics,
                ],
                'recent_users' => $recentUsers,
                'subscription_breakdown' => $subscriptionBreakdown,
                'recent_activity' => $recentActivity,
                'system_stats' => $systemStats,
            ]
        ]);
    }

    /**
     * Get all users with pagination and filters
     */
    public function getUsers(Request $request)
    {
        $query = User::with(['nfcCards.landingPage', 'nfcTag', 'nfcCards'])
            ->withCount(['analytics', 'nfcCards']);

        // Filter by businessUserId: handle 'all' for "All Business Plan Users" or specific ID
        if ($request->businessUserId === 'all') {
            // Show only business plan users (owners + employees)
            $query->where('subscription_plan', 'business');
        } elseif ($request->filled('businessUserId')) {
            $businessUserId = $request->businessUserId;
            $query->where(function ($q) use ($businessUserId) {
                $q->where('id', $businessUserId)
                    ->orWhere('parent_business_id', $businessUserId);
            });
        }

        // Filter by employeeId: show only the selected employee
        if ($request->filled('employeeId')) {
            $employeeId = $request->employeeId;
            $query->where('id', $employeeId);
        }

        // Apply other filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if ($request->filled('subscription_plan')) {
            $query->where('subscription_plan', $request->subscription_plan);
        }

        if ($request->filled('is_admin')) {
            $query->where('is_admin', $request->boolean('is_admin'));
        }

        if ($request->filled('status')) {
            switch ($request->status) {
                case 'active':
                    $query->where('subscription_active', true);
                    break;
                case 'inactive':
                    $query->where('subscription_active', false);
                    break;
                case 'expired':
                    $query->where('subscription_end_date', '<', now());
                    break;
            }
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $perPage = $request->get('per_page', 15);
        $users = $query->paginate($perPage);

        // Add quota information for Business accounts
        $users->getCollection()->transform(function ($user) {
            if ($user->isBusinessAccount()) {
                $quotaInfo = $user->getQuotaInfo();
                $user->quota_info = $quotaInfo;
            }
            return $user;
        });

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Get user details
     */
    public function getUser(Request $request, $userId)
    {
        $user = User::with([
            'nfcCards.landingPage',
            'nfcTag',
            'nfcCards',
            'analytics' => function ($query) {
                $query->latest()->limit(50);
            }
        ])->findOrFail($userId);

        // Get user sessions
        $sessions = DB::table('sessions')
            ->where('user_id', $userId)
            ->orderBy('last_activity', 'desc')
            ->limit(10)
            ->get();

        // Get user analytics summary
        $analyticsSummary = Analytics::where('trackable_type', User::class)
            ->where('trackable_id', $userId)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'sessions' => $sessions,
                'analytics_summary' => $analyticsSummary,
            ]
        ]);
    }

    /**
     * Create a new employee under a Business account
     * Only Super Admin can create employee accounts
     */
    public function createBusinessEmployee(Request $request)
    {
        $validated = $request->validate([
            'business_account_id' => 'required|exists:users,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'job_title' => 'nullable|string|max:255',
            'password' => 'required|string|min:8',
        ]);

        // Verify business account exists and is a Business plan
        $businessAccount = User::find($validated['business_account_id']);

        if (!$businessAccount || !$businessAccount->isBusinessAccount()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Business account ID'
            ], 400);
        }

        // Check quota
        $quotaInfo = $businessAccount->getQuotaInfo();

        if ($quotaInfo['available_account_slots'] <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot create employee. Business account has reached its account slots quota.',
                'data' => [
                    'business_account' => $businessAccount->full_name,
                    'total_slots' => $quotaInfo['total_account_slots'],
                    'used_slots' => $quotaInfo['employees_count'],
                    'available_slots' => 0
                ]
            ], 403);
        }

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
            'parent_business_id' => $businessAccount->id,
            'company' => $businessAccount->company,
            'is_admin' => false,
        ]);

        // Get updated quota
        $updatedQuotaInfo = $businessAccount->getQuotaInfo();

        return response()->json([
            'success' => true,
            'message' => 'Employee account created successfully',
            'data' => [
                'employee' => [
                    'id' => $employee->id,
                    'full_name' => $employee->full_name,
                    'email' => $employee->email,
                    'job_title' => $employee->job_title,
                    'parent_business_id' => $employee->parent_business_id,
                ],
                'business_account' => [
                    'id' => $businessAccount->id,
                    'name' => $businessAccount->full_name,
                    'remaining_slots' => $updatedQuotaInfo['available_account_slots']
                ]
            ]
        ], 201);
    }

    /**
     * Create a new user
     */
    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100|regex:/^[a-zA-Z\s\-\']+$/',
            'last_name' => 'required|string|max:100|regex:/^[a-zA-Z\s\-\']+$/',
            'email' => 'required|email|min:5|max:100|unique:users',
            'password' => 'required|string|min:8',
            'company' => 'nullable|string|max:150',
            'job_title' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20|regex:/^\+?\d*$/',
            'subscription_plan' => 'nullable|in:free,basic,premium,business',
            'subscription_active' => 'nullable|boolean',
            'is_admin' => 'boolean',
            'admin_role' => 'nullable|in:admin,moderator',
            'admin_permissions' => 'nullable|array',
            'total_account_slots' => 'nullable|integer|min:0',
            'total_card_quota' => 'nullable|integer|min:0',
        ], [
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens, and apostrophes',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, and apostrophes',
            'phone.regex' => 'Phone can only contain digits and optional + prefix (e.g. +60123456789)',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $subscriptionPlan = $request->subscription_plan ?? 'free';
            $isBusinessPlan = $subscriptionPlan === 'business';
            $subscriptionActive = $request->has('subscription_active')
                ? $request->boolean('subscription_active')
                : ($subscriptionPlan !== 'free');

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'company' => $request->company,
                'job_title' => $request->job_title,
                'phone' => $request->phone,
                'subscription_plan' => $subscriptionPlan,
                'subscription_active' => $subscriptionActive,
                'subscription_start_date' => $subscriptionActive ? now() : null,
                'subscription_end_date' => $subscriptionActive ? now()->addYear() : null,
                'is_admin' => $request->boolean('is_admin'),
                'admin_role' => $request->admin_role,
                'admin_permissions' => $request->admin_permissions,
                'total_account_slots' => $isBusinessPlan ? ($request->total_account_slots ?? 10) : 0,
                'total_card_quota' => $isBusinessPlan ? ($request->total_card_quota ?? 10) : 0,
            ]);

            // Create NFC card for the user
            $nfcCard = NfcCard::create([
                'user_id' => $user->id,
                'business_account_id' => $isBusinessPlan ? $user->id : null,
                'card_owner' => $user->full_name,
                'billing_address' => '',
                'contact_number' => $user->phone ?? '',
                'purchase_date' => now(),
                'status' => 'active',
                'subscription_plan' => $user->subscription_plan,
                'purchase_amount' => 0,
            ]);

            // Create landing page for the NFC card
            $landingPage = LandingPage::create([
                'nfc_card_id' => $nfcCard->id,
                'name' => $user->full_name,
                'title' => $user->job_title,
                'company_name' => $user->company,
                'email' => $user->email,
                'is_active' => true,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'user' => $user->load('nfcCards.landingPage'),
                    'nfc_card' => $nfcCard,
                    'landing_page' => $landingPage,
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20|regex:/^\+?\d*$/',
            'subscription_plan' => 'nullable|in:free,basic,premium,business',
            'subscription_active' => 'boolean',
            'subscription_start_date' => 'nullable|date',
            'subscription_end_date' => 'nullable|date',
            'is_admin' => 'boolean',
            'admin_role' => 'nullable|in:admin,moderator',
            'admin_permissions' => 'nullable|array',
            'total_account_slots' => 'nullable|integer|min:0',
            'total_card_quota' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if admin is trying to modify super admin
        if ($user->isSuperAdmin() && $request->user()->id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot modify super admin account'
            ], 403);
        }

        // Handle Business Plan quota updates
        if ($request->has('subscription_plan') && $request->subscription_plan === 'business') {
            // If updating to Business Plan or already Business
            if ($request->has('total_card_quota') || $request->has('total_account_slots')) {
                $quotaInfo = $user->getQuotaInfo();

                // Validate card quota
                if ($request->has('total_card_quota')) {
                    $newCardQuota = $request->total_card_quota;
                    if ($newCardQuota < $quotaInfo['ordered_cards_count']) {
                        return response()->json([
                            'success' => false,
                            'message' => "Cannot set card quota to {$newCardQuota}. Already ordered: {$quotaInfo['ordered_cards_count']} cards.",
                            'errors' => [
                                'total_card_quota' => ["Minimum value is {$quotaInfo['ordered_cards_count']} (already ordered cards)"]
                            ]
                        ], 422);
                    }
                }

                // Validate account slots
                if ($request->has('total_account_slots')) {
                    $newAccountSlots = $request->total_account_slots;
                    if ($newAccountSlots < $quotaInfo['employees_count']) {
                        return response()->json([
                            'success' => false,
                            'message' => "Cannot set account slots to {$newAccountSlots}. Current employees: {$quotaInfo['employees_count']}.",
                            'errors' => [
                                'total_account_slots' => ["Minimum value is {$quotaInfo['employees_count']} (current employees)"]
                            ]
                        ], 422);
                    }
                }
            }

            // Update quota fields
            if ($request->has('total_account_slots')) {
                $user->total_account_slots = $request->total_account_slots;
            }
            if ($request->has('total_card_quota')) {
                $user->total_card_quota = $request->total_card_quota;
            }
        } elseif ($request->has('subscription_plan') && $request->subscription_plan !== 'business') {
            // Changing from Business to another plan
            if ($user->subscription_plan === 'business') {
                $quotaInfo = $user->getQuotaInfo();

                if ($quotaInfo['employees_count'] > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot change plan. Please delete all employees first.',
                        'errors' => [
                            'subscription_plan' => ['Cannot change from Business plan while employees exist']
                        ]
                    ], 422);
                }

                // Clear quota
                $user->total_account_slots = 0;
                $user->total_card_quota = 0;
            }
        }

        // Update other fields (exclude password and quota fields)
        $fieldsToUpdate = $request->except(['password', 'password_confirmation', 'total_account_slots', 'total_card_quota']);
        $user->fill($fieldsToUpdate);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user->load('nfcCards.landingPage')
        ]);
    }

    /**
     * Delete user
     */
    public function deleteUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        // Check if admin is trying to delete super admin
        if ($user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete super admin account'
            ], 403);
        }

        // Check if admin is trying to delete themselves
        if ($request->user()->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your own account'
            ], 403);
        }

        DB::beginTransaction();

        try {
            // Delete related data
            // Delete landing pages through NFC cards
            foreach ($user->nfcCards as $nfcCard) {
                $nfcCard->landingPage()->delete();
            }
            $user->nfcTag()->delete();
            $user->nfcCards()->delete();
            $user->analytics()->delete();

            // Delete user
            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get NFC card management data
     */
    public function getNfcCards(Request $request)
    {
        $query = NfcCard::with(['user', 'nfcTag'])
            ->withCount('analytics');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('card_owner', 'like', "%{$search}%")
                    ->orWhere('nfc_card_id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('subscription_plan')) {
            $query->where('subscription_plan', $request->subscription_plan);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $perPage = $request->get('per_page', 15);
        $nfcCards = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $nfcCards
        ]);
    }

    /**
     * Register NFC card
     */
    public function registerNfcCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'nfc_card_id' => 'required|string|unique:nfc_cards,nfc_card_id|max:50',
            'card_owner' => 'required|string|max:255',
            'billing_address' => 'required|string',
            'contact_number' => 'required|string|max:20',
            'subscription_plan' => 'required|in:free,basic,premium,business',
            'purchase_amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:100',
            'shipping_address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $user = User::findOrFail($request->user_id);

            // Create NFC card
            $nfcCard = NfcCard::create([
                'user_id' => $user->id,
                'nfc_card_id' => $request->nfc_card_id,
                'card_owner' => $request->card_owner,
                'billing_address' => $request->billing_address,
                'contact_number' => $request->contact_number,
                'purchase_date' => now(),
                'subscription_plan' => $request->subscription_plan,
                'purchase_amount' => $request->purchase_amount,
                'payment_method' => $request->payment_method,
                'shipping_address' => $request->shipping_address,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            // Update user subscription
            $user->update([
                'subscription_plan' => $request->subscription_plan,
                'subscription_start_date' => now(),
                'subscription_end_date' => now()->addYear(),
                'subscription_active' => true,
                'has_physical_card' => true,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'NFC card registered successfully',
                'data' => $nfcCard->load('user')
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to register NFC card: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update NFC card
     */
    public function updateNfcCard(Request $request, $cardId)
    {
        $nfcCard = NfcCard::findOrFail($cardId);

        $validator = Validator::make($request->all(), [
            'card_owner' => 'nullable|string|max:255',
            'billing_address' => 'nullable|string',
            'contact_number' => 'nullable|string|max:20',
            'shipping_address' => 'nullable|string',
            'status' => 'nullable|in:pending,active,inactive,shipped,delivered',
            'tracking_number' => 'nullable|string|max:100',
            'shipped_date' => 'nullable|date',
            'delivered_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $nfcCard->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'NFC card updated successfully',
            'data' => $nfcCard->load('user')
        ]);
    }

    /**
     * Delete NFC card
     */
    public function deleteNfcCard(Request $request, $cardId)
    {
        $nfcCard = NfcCard::findOrFail($cardId);

        DB::beginTransaction();

        try {
            // Delete associated NFC tag
            if ($nfcCard->nfcTag) {
                $nfcCard->nfcTag->delete();
            }

            // Delete NFC card
            $nfcCard->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'NFC card deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete NFC card: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system statistics
     */
    public function getSystemStats(Request $request)
    {
        $stats = [
            'users' => [
                'total' => User::count(),
                'active' => User::where('subscription_active', true)->count(),
                'admin' => User::where('is_admin', true)->count(),
                'new_this_month' => User::whereMonth('created_at', now()->month)->count(),
            ],
            'subscriptions' => [
                'free' => User::where('subscription_plan', 'free')->count(),
                'basic' => User::where('subscription_plan', 'basic')->count(),
                'premium' => User::where('subscription_plan', 'premium')->count(),
                'business' => User::where('subscription_plan', 'business')->count(),
            ],
            'nfc_cards' => [
                'total' => NfcCard::count(),
                'active' => NfcCard::where('status', 'active')->count(),
                'pending' => NfcCard::where('status', 'pending')->count(),
                'shipped' => NfcCard::where('status', 'shipped')->count(),
            ],
            'analytics' => [
                'total_tracks' => Analytics::count(),
                'profile_views' => Analytics::where('action', 'profile_view')->count(),
                'nfc_taps' => Analytics::where('action', 'nfc_tap')->count(),
                'link_clicks' => Analytics::where('action', 'link_click')->count(),
            ],
            'storage' => [
                'total_used' => $this->getStorageUsage(),
                'landing_pages' => LandingPage::whereNotNull('profile_image')->count(),
                'logos' => LandingPage::whereNotNull('company_logo')->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get storage usage
     */
    private function getStorageUsage()
    {
        $totalBytes = 0;

        try {
            if (Storage::disk('public')->exists('profile-images')) {
                $profileImages = Storage::disk('public')->size('profile-images');
                $totalBytes += $profileImages;
            }
        } catch (\Exception $e) {
            // Directory doesn't exist or can't be accessed
        }

        try {
            if (Storage::disk('public')->exists('company-logos')) {
                $companyLogos = Storage::disk('public')->size('company-logos');
                $totalBytes += $companyLogos;
            }
        } catch (\Exception $e) {
            // Directory doesn't exist or can't be accessed
        }

        try {
            if (Storage::disk('public')->exists('card-designs')) {
                $cardDesigns = Storage::disk('public')->size('card-designs');
                $totalBytes += $cardDesigns;
            }
        } catch (\Exception $e) {
            // Directory doesn't exist or can't be accessed
        }

        return [
            'bytes' => $totalBytes,
            'formatted' => $this->formatBytes($totalBytes),
        ];
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Get super admin user
     */
    public function getSuperAdmin()
    {
        $superAdmin = User::where('admin_role', 'super_admin')
            ->where('is_admin', true)
            ->first();

        if (!$superAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Super admin not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $superAdmin->id,
                'email' => $superAdmin->email,
                'full_name' => $superAdmin->full_name,
                'admin_role' => $superAdmin->admin_role,
            ],
        ]);
    }

    /**
     * System-wide Admin Audit Log — list all activity_logs entries with filters:
     *   ?action_type=admin_create_user
     *   ?entity_type=user&entity_id=123
     *   ?actor_id=7 (user_id that performed the action)
     *   ?date_from=2026-01-01 &date_to=2026-01-31
     *   ?search= (wildcard across description, action_type, entity_type)
     *   ?per_page=25
     *
     * GET /admin/audit-logs
     */
    public function auditLogsIndex(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'action_type' => 'sometimes|string|max:100',
            'entity_type' => 'sometimes|string|max:100',
            'entity_id' => 'sometimes|string',
            'actor_id' => 'sometimes|integer',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
            'search' => 'sometimes|string|max:255',
            'per_page' => 'sometimes|integer|min:1|max:200',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $query = \App\Models\ActivityLog::query()
            ->with(['user:id,email,first_name,last_name,avatar_url,admin_role'])
            ->orderByDesc('created_at');

        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }
        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }
        if ($request->filled('entity_id')) {
            $query->where('entity_id', $request->entity_id);
        }
        if ($request->filled('actor_id')) {
            $query->where('user_id', (int) $request->actor_id);
        }
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->date_from)->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('action_description', 'like', $searchTerm)
                  ->orWhere('action_type', 'like', $searchTerm)
                  ->orWhere('entity_type', 'like', $searchTerm)
                  ->orWhere('entity_id', 'like', $searchTerm)
                  ->orWhere('ip_address', 'like', $searchTerm);
            });
        }

        $perPage = (int) $request->input('per_page', 25);
        $logs = $query->paginate($perPage);

        // Distinct action types for filter dropdown
        $distinctActionTypes = \App\Models\ActivityLog::query()
            ->distinct()
            ->orderBy('action_type')
            ->limit(100)
            ->pluck('action_type');

        $logs->getCollection()->transform(function (\App\Models\ActivityLog $log) {
            return [
                'id' => $log->id,
                'created_at' => $log->created_at->toIso8601String(),
                'action_type' => $log->action_type,
                'action_description' => $log->action_description,
                'entity_type' => $log->entity_type,
                'entity_id' => $log->entity_id,
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
                'metadata' => $log->metadata,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'actor' => $log->user ? [
                    'id' => $log->user->id,
                    'email' => $log->user->email,
                    'name' => $log->user->full_name ?? trim(($log->user->first_name ?? '') . ' ' . ($log->user->last_name ?? '')),
                    'avatar_url' => $log->user->avatar_url ?? null,
                    'admin_role' => $log->user->admin_role ?? null,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'logs' => $logs,
            'meta' => [
                'available_action_types' => $distinctActionTypes->values()->all(),
                'total_count' => $logs->total(),
            ],
        ]);
    }

    /**
     * Audit Log CSV export (same filters as index, no pagination).
     *
     * GET /admin/audit-logs/export-csv
     */
    public function auditLogsExportCsv(Request $request)
    {
        // Reuse same filters from index
        $filters = $request->only(['action_type', 'entity_type', 'entity_id', 'actor_id', 'date_from', 'date_to', 'search']);
        $internalRequest = Request::create('/internal/audit-export', 'GET', $filters + ['per_page' => 5000]);
        $indexResponse = $this->auditLogsIndex($internalRequest);

        // Extract log entries from paginated data
        $data = $indexResponse->getData(true);
        $logs = $data['logs']['data'] ?? [];

        $csvHeaders = [
            'Timestamp',
            'Actor Email',
            'Actor Name',
            'Action Type',
            'Description',
            'Entity Type',
            'Entity ID',
            'IP Address',
            'User Agent',
            'Old Values (JSON)',
            'New Values (JSON)',
            'Metadata (JSON)',
        ];

        $filename = 'audit-log-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($csvHeaders, $logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $csvHeaders);
            foreach ($logs as $l) {
                fputcsv($handle, [
                    $l['created_at'],
                    $l['actor']['email'] ?? '',
                    $l['actor']['name'] ?? '',
                    $l['action_type'],
                    $l['action_description'],
                    (string) $l['entity_type'],
                    (string) $l['entity_id'],
                    (string) $l['ip_address'],
                    (string) $l['user_agent'],
                    $l['old_values'] !== null ? json_encode($l['old_values'], JSON_UNESCAPED_SLASHES) : '',
                    $l['new_values'] !== null ? json_encode($l['new_values'], JSON_UNESCAPED_SLASHES) : '',
                    $l['metadata'] !== null ? json_encode($l['metadata'], JSON_UNESCAPED_SLASHES) : '',
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
