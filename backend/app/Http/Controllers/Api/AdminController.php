<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
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
        $totalProfiles = Profile::count();
        $totalNfcCards = NfcCard::count();
        $totalNfcTags = NfcTag::count();
        $totalAnalytics = Analytics::count();

        // Get recent registrations
        $recentUsers = User::with('profile')
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
                    'total_profiles' => $totalProfiles,
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
        $query = User::with(['profile', 'nfcTag', 'nfcCards'])
            ->withCount(['analytics', 'nfcCards']);

        // Apply filters
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
            'profile',
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
     * Create new user
     */
    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'subscription_plan' => 'nullable|in:free,basic,premium,business',
            'is_admin' => 'boolean',
            'admin_role' => 'nullable|in:admin,moderator',
            'admin_permissions' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'company' => $request->company,
                'job_title' => $request->job_title,
                'phone' => $request->phone,
                'subscription_plan' => $request->subscription_plan ?? 'free',
                'subscription_active' => $request->subscription_plan !== 'free',
                'subscription_start_date' => $request->subscription_plan !== 'free' ? now() : null,
                'subscription_end_date' => $request->subscription_plan !== 'free' ? now()->addYear() : null,
                'is_admin' => $request->boolean('is_admin'),
                'admin_role' => $request->admin_role,
                'admin_permissions' => $request->admin_permissions,
                'total_account_slots' => $request->subscription_plan ?? 'business' ? 10 : 0,
                'total_card_quota' => $request->subscription_plan ?? 'business' ? 10 : 0,
            ]);

            // Create profile
            $slug = Str::slug($user->full_name);
            $originalSlug = $slug;
            $count = 1;

            while (Profile::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            $profile = Profile::create([
                'user_id' => $user->id,
                'slug' => $slug,
                'name' => $user->full_name,
                'title' => $user->job_title,
                'company' => $user->company,
                'email' => $user->email,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'user' => $user->load('profile'),
                    'profile' => $profile,
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
            'phone' => 'nullable|string|max:20',
            'subscription_plan' => 'nullable|in:free,basic,premium,business',
            'subscription_active' => 'boolean',
            'subscription_start_date' => 'nullable|date',
            'subscription_end_date' => 'nullable|date',
            'is_admin' => 'boolean',
            'admin_role' => 'nullable|in:admin,moderator',
            'admin_permissions' => 'nullable|array',
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

        $user->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user->load('profile')
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
            $user->profile()->delete();
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
                'profiles' => Profile::whereNotNull('profile_image_path')->count(),
                'logos' => Profile::whereNotNull('company_logo_path')->count(),
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
}
