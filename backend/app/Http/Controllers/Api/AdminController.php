<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use App\Models\LandingPage;
use App\Models\NfcCard;
use App\Models\NfcTag;
use App\Models\Analytics;
use App\Models\Transaction;
use App\Models\ActivityLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Carbon\Carbon;

class AdminController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get admin dashboard overview
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();

        // =========================================================
        // 1. TIME RANGES — current month vs previous month (real trends, not hardcoded)
        // =========================================================
        $thisMonthStart = now()->startOfMonth();
        $prevMonthStart = now()->subMonthNoOverflow()->startOfMonth();
        $prevMonthEnd   = now()->subMonthNoOverflow()->endOfMonth();

        $counts = function ($query) use ($thisMonthStart, $prevMonthStart, $prevMonthEnd) {
            return [
                'current' => (clone $query)->where('created_at', '>=', $thisMonthStart)->count(),
                'previous' => (clone $query)->whereBetween('created_at', [$prevMonthStart, $prevMonthEnd])->count(),
                'all_time' => (clone $query)->count(),
            ];
        };

        $safePercentChange = function ($current, $previous) {
            if ($previous <= 0) {
                return $current > 0 ? 100.0 : 0.0;
            }
            return round((($current - $previous) / $previous) * 100, 1);
        };

        $usersStats      = $counts(User::query());
        $profilesStats   = $counts(LandingPage::query());
        $nfcCardsStats   = $counts(NfcCard::query());
        $analyticsStats  = $counts(Analytics::query());

        // =========================================================
        // 2. Recent registrations (with profile image if available)
        // =========================================================
        $recentUsers = User::with(['nfcCards.landingPage'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // =========================================================
        // 3. Subscription breakdown
        // =========================================================
        $subscriptionBreakdown = User::selectRaw('subscription_plan, COUNT(*) as count')
            ->groupBy('subscription_plan')
            ->get();

        // =========================================================
        // 4. Recent Activity — MIX of:
        //    A) Analytics (nfc taps, profile views, link clicks) — trackable NFC/card data
        //    B) ActivityLog (logins, registrations, payments, approvals, 2FA setup)
        //    Result: truly varied live feed, no more "all John Doe tapped"
        // =========================================================

        // ---- A) Pull top 8 Analytics (card interactions) ----
        $analyticsActivityRaw = Analytics::with([
            'trackable' => function ($morphTo) {
                $morphTo
                    ->morphWith([
                        NfcTag::class  => ['nfcCard.user', 'user'],
                        NfcCard::class => ['user', 'nfcTag'],
                        LandingPage::class => ['nfcCard.user'],
                    ]);
            },
        ])
        ->orderBy('created_at', 'desc')
        ->limit(8)
        ->get();

        $analyticsActivity = $analyticsActivityRaw->map(function ($a) {
            $t = $a->trackable;
            $cardInfo = null;
            $ownerInfo = null;
            if ($t instanceof NfcTag) {
                $c = $t->nfcCard;
                if ($c) {
                    $cardInfo = [
                        'card_id'      => $c->card_id,
                        'card_number'  => $c->card_number,
                        'card_owner'   => $c->card_owner,
                        'nfc_id'       => $t->nfc_id,
                        'tag_name'     => $t->name,
                    ];
                    $u = $c->user ?? $t->user;
                    if ($u) {
                        $ownerInfo = [
                            'user_id'   => $u->id,
                            'full_name' => $u->full_name,
                            'email'     => $u->email,
                        ];
                    }
                }
            } elseif ($t instanceof NfcCard) {
                $cardInfo = [
                    'card_id'      => $t->card_id,
                    'card_number'  => $t->card_number,
                    'card_owner'   => $t->card_owner,
                ];
                if ($t->user) {
                    $ownerInfo = [
                        'user_id'   => $t->user->id,
                        'full_name' => $t->user->full_name,
                        'email'     => $t->user->email,
                    ];
                }
            } elseif ($t instanceof LandingPage) {
                $c = $t->nfcCard;
                if ($c) {
                    $cardInfo = [
                        'card_id'      => $c->card_id,
                        'card_number'  => $c->card_number,
                        'card_owner'   => $c->card_owner,
                    ];
                    if ($c->user) {
                        $ownerInfo = [
                            'user_id'   => $c->user->id,
                            'full_name' => $c->user->full_name,
                            'email'     => $c->user->email,
                        ];
                    }
                }
            }

            $location = trim(($a->city ? $a->city . ', ' : '') . ($a->country ?? ''), ', ');

            return [
                'id'          => 'an-' . $a->id,
                'action'      => $a->action,                 // nfc_tap, profile_view, link_click
                'activity_source' => 'analytics',
                'trackable_type' => $a->trackable_type,
                'trackable_id'   => $a->trackable_id,
                'ip_address'  => $a->ip_address,
                'device_type' => $a->device_type,
                'browser'     => $a->browser,
                'platform'    => $a->platform,
                'location'    => $location ?: null,
                'data'        => $a->data,
                'card'        => $cardInfo,
                'owner'       => $ownerInfo,
                'description' => null,                        // ActivityLog uses this
                'actor'       => null,                        // ActivityLog uses this
                'created_at'  => $a->created_at?->toIso8601String(),
            ];
        });

        // ---- B) Pull top 8 ActivityLog (system events: login, register, payment, approvals) ----
        $systemActivityRaw = ActivityLog::with(['user:id,first_name,last_name,email,full_name'])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        $systemActivity = $systemActivityRaw->map(function ($log) {
            // Extract device/browser from user_agent
            $ua = $log->user_agent ?? '';
            $deviceType = 'desktop';
            $platform = 'Unknown';
            $browser = 'Unknown';

            if (stripos($ua, 'Mobile') !== false || stripos($ua, 'Android') !== false || stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) {
                $deviceType = 'mobile';
            } elseif (stripos($ua, 'Tablet') !== false) {
                $deviceType = 'tablet';
            }

            if (stripos($ua, 'Windows') !== false) $platform = 'Windows';
            elseif (stripos($ua, 'Mac OS') !== false) $platform = 'macOS';
            elseif (stripos($ua, 'Android') !== false) $platform = 'Android';
            elseif (stripos($ua, 'iOS') !== false || stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) $platform = 'iOS';
            elseif (stripos($ua, 'Linux') !== false) $platform = 'Linux';

            if (stripos($ua, 'Firefox') !== false) $browser = 'Firefox';
            elseif (stripos($ua, 'Edg/') !== false) $browser = 'Edge';
            elseif (stripos($ua, 'Chrome') !== false) $browser = 'Chrome';
            elseif (stripos($ua, 'Safari') !== false) $browser = 'Safari';

            $actor = null;
            if ($log->user) {
                $actor = [
                    'user_id'   => $log->user->id,
                    'full_name' => $log->user->full_name,
                    'email'     => $log->user->email,
                ];
            }

            return [
                'id'          => 'sl-' . $log->id,
                'action'      => $log->action_type,         // user_logged_in, user_registered, payment_created, ...
                'activity_source' => 'activity_log',
                'trackable_type' => $log->entity_type,
                'trackable_id'   => $log->entity_id,
                'ip_address'  => $log->ip_address,
                'device_type' => $deviceType,
                'browser'     => $browser,
                'platform'    => $platform,
                'location'    => null,
                'data'        => $log->metadata,
                'card'        => null,
                'owner'       => $actor,
                'description' => $log->action_description,
                'actor'       => $actor,
                'created_at'  => $log->created_at?->toIso8601String(),
            ];
        });

        // ---- C) Merge & sort by created_at descending, keep top 10 ----
        $recentActivity = $analyticsActivity
            ->concat($systemActivity)
            ->sortByDesc(function ($item) {
                return $item['created_at'] ?? '0';
            })
            ->take(10)
            ->values()
            ->all();

        // =========================================================
        // 5. System stats
        // =========================================================
        $systemStats = [
            'total_storage_used' => $this->getStorageUsage(),
            'active_sessions'    => DB::table('sessions')->count(),
            'last_backup'        => now()->subDays(2)->format('Y-m-d H:i:s'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'overview' => [
                    'total_users'           => $usersStats['all_time'],
                    'total_profiles'        => $profilesStats['all_time'],
                    'total_landing_pages'   => $profilesStats['all_time'],
                    'total_nfc_cards'       => $nfcCardsStats['all_time'],
                    'total_nfc_tags'        => NfcTag::count(),
                    'total_analytics'       => $analyticsStats['all_time'],
                    'trends' => [
                        'total_users' => [
                            'current'  => $usersStats['current'],
                            'previous' => $usersStats['previous'],
                            'change_pct'   => $safePercentChange($usersStats['current'], $usersStats['previous']),
                            'direction'    => $usersStats['current'] >= $usersStats['previous'] ? 'up' : 'down',
                        ],
                        'total_profiles' => [
                            'current'  => $profilesStats['current'],
                            'previous' => $profilesStats['previous'],
                            'change_pct'   => $safePercentChange($profilesStats['current'], $profilesStats['previous']),
                            'direction'    => $profilesStats['current'] >= $profilesStats['previous'] ? 'up' : 'down',
                        ],
                        'total_nfc_cards' => [
                            'current'  => $nfcCardsStats['current'],
                            'previous' => $nfcCardsStats['previous'],
                            'change_pct'   => $safePercentChange($nfcCardsStats['current'], $nfcCardsStats['previous']),
                            'direction'    => $nfcCardsStats['current'] >= $nfcCardsStats['previous'] ? 'up' : 'down',
                        ],
                        'total_analytics' => [
                            'current'  => $analyticsStats['current'],
                            'previous' => $analyticsStats['previous'],
                            'change_pct'   => $safePercentChange($analyticsStats['current'], $analyticsStats['previous']),
                            'direction'    => $analyticsStats['current'] >= $analyticsStats['previous'] ? 'up' : 'down',
                        ],
                    ],
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
        $query = User::with([
            'nfcCards.landingPage',
            'nfcTag',
            'latestSubscription',
            'employees',
        ])->withCount(['analytics', 'nfcCards']);

        // Filter by businessUserId: handle 'all' for "All Business Plan Users" or specific ID
        if ($request->businessUserId === 'all') {
            $query->where('subscription_plan', 'business');
        } elseif ($request->filled('businessUserId')) {
            $businessUserId = $request->businessUserId;
            $query->where(function ($q) use ($businessUserId) {
                $q->where('id', $businessUserId)
                    ->orWhere('parent_business_id', $businessUserId);
            });
        }

        if ($request->filled('employeeId')) {
            $query->where('id', $request->employeeId);
        }

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

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 15);
        $users = $query->paginate($perPage);

        // Single-pass hydration: name_slug clash counts for ALL users in page
        User::hydrateNameSlugClashCounts($users->getCollection());

        $users->getCollection()->transform(function ($user) {
            if ($user->isBusinessAccount()) {
                $quotaInfo = $user->getQuotaInfo(true);
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
            'admin_role' => 'nullable|in:admin,moderator,super_admin',
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
            // Free plan is never an active "paid" subscription
            if ($subscriptionPlan === 'free') {
                $subscriptionActive = false;
            }
            $startDate = $subscriptionActive ? now() : null;
            $endDate = $subscriptionActive ? now()->addYear() : null;

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
                'subscription_start_date' => $startDate,
                'subscription_end_date' => $endDate,
                'is_admin' => $request->boolean('is_admin'),
                'admin_role' => $request->admin_role,
                'admin_permissions' => $request->admin_permissions,
                'total_account_slots' => $isBusinessPlan ? ($request->total_account_slots ?? 10) : 0,
                'total_card_quota' => $isBusinessPlan ? ($request->total_card_quota ?? 10) : 0,
            ]);

            // =========================================================================
            // SINGLE SOURCE OF TRUTH: create corresponding Subscription row
            // (user accessors always read from subscriptions table first)
            // =========================================================================
            if ($subscriptionPlan !== 'free' && $subscriptionActive) {
                $amountMap = [
                    'basic'    => 0.00,
                    'premium'  => 0.00,
                    'business' => 0.00,
                ];
                Subscription::create([
                    'user_id'               => $user->id,
                    'provider'              => 'manual',
                    'plan_name'             => ucfirst($subscriptionPlan) . ' Plan',
                    'plan_type'             => $subscriptionPlan,
                    'amount'                => $amountMap[$subscriptionPlan] ?? 0.00,
                    'currency'              => 'MYR',
                    'interval'              => 'yearly',
                    'status'                => 'active',
                    'current_period_start'  => $startDate,
                    'current_period_end'    => $endDate,
                    'next_billing_date'     => $endDate,
                    'metadata'              => [
                        'admin_created' => true,
                        'admin_id'      => $request->user()?->id,
                        'source'        => 'User Management (Create)',
                    ],
                ]);
            }

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
                    'user' => $user->load(['nfcCards.landingPage', 'subscriptions']),
                    'nfc_card' => $nfcCard,
                    'landing_page' => $landingPage,
                ]
            ], 201);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Database error while creating user: ' . $e->getMessage(),
                'error_code' => $e->errorInfo[1] ?? null,
            ], 500);
        } catch (\Throwable $e) {
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
            'email' => 'nullable|email|max:255|unique:users,email,' . $userId,
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20|regex:/^\+?\d*$/',
            'subscription_plan' => 'nullable|in:free,basic,premium,business',
            'subscription_active' => 'boolean',
            'subscription_start_date' => 'nullable|date',
            'subscription_end_date' => 'nullable|date',
            'is_admin' => 'boolean',
            'admin_role' => 'nullable|in:admin,moderator,super_admin',
            'admin_permissions' => 'nullable|array',
            'total_account_slots' => 'nullable|integer|min:0',
            'total_card_quota' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
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

        DB::beginTransaction();
        try {
            // Handle Business Plan quota updates
            if ($request->has('subscription_plan') && $request->subscription_plan === 'business') {
                // If updating to Business Plan or already Business
                if ($request->has('total_card_quota') || $request->has('total_account_slots')) {
                    $quotaInfo = $user->getQuotaInfo();

                    // Validate card quota
                    if ($request->has('total_card_quota')) {
                        $newCardQuota = $request->total_card_quota;
                        if ($newCardQuota < $quotaInfo['ordered_cards_count']) {
                            DB::rollBack();
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
                            DB::rollBack();
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
                        DB::rollBack();
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

            // Update other fields (exclude password and quota fields, also exclude subscription_*
            // because we sync legacy columns from Subscription SoT below)
            $fieldsToUpdate = $request->except([
                'password', 'password_confirmation',
                'total_account_slots', 'total_card_quota',
                'subscription_plan', 'subscription_active',
                'subscription_start_date', 'subscription_end_date',
            ]);
            $user->fill($fieldsToUpdate);

            // =========================================================================
            // SINGLE SOURCE OF TRUTH: write changes to subscriptions table FIRST
            // (User accessors always read from subscriptions table, so legacy columns
            //  on users table are only caches — they get synced back by SubscriptionObserver)
            // =========================================================================
            $planChanged = $request->has('subscription_plan') && $request->subscription_plan !== ($user->getOriginal('subscription_plan') ?? $user->subscription_plan);
            $activeChanged = $request->has('subscription_active') && $request->boolean('subscription_active') !== (bool)$user->subscription_active;
            $datesChanged = $request->has('subscription_start_date') || $request->has('subscription_end_date');

            if ($planChanged || $activeChanged || $datesChanged || $request->subscription_plan === 'free' || $request->subscription_plan) {
                $newPlan = strtolower($request->subscription_plan ?? ($user->getOriginal('subscription_plan') ?? 'free'));
                $newActive = $request->has('subscription_active')
                    ? $request->boolean('subscription_active')
                    : (bool)$user->subscription_active;
                // Free plan is never "active" paid subscription
                if ($newPlan === 'free') {
                    $newActive = false;
                }

                $startDate = $request->subscription_start_date
                    ? Carbon::parse($request->subscription_start_date)->startOfDay()
                    : Carbon::now()->startOfDay();
                $endDate = $request->subscription_end_date
                    ? Carbon::parse($request->subscription_end_date)->endOfDay()
                    : ($newActive && $newPlan !== 'free'
                        ? Carbon::now()->startOfDay()->addYear()->endOfDay()
                        : null);

                // Cancel existing active subscriptions for user if plan/status changed
                if ($planChanged || $activeChanged) {
                    Subscription::where('user_id', $user->id)
                        ->where('status', 'active')
                        ->update([
                            'status' => $newPlan === 'free' ? 'cancelled' : 'cancelled',
                            'cancelled_at' => now(),
                            'cancellation_reason' => 'Admin changed plan/status via user management',
                            'current_period_end' => $endDate,
                        ]);
                }

                // Create new subscription row for non-free plans with active status
                if ($newPlan !== 'free' && $newActive) {
                    $amountMap = [
                        'basic'    => 0.00,
                        'premium'  => 0.00,
                        'business' => 0.00,
                    ];
                    Subscription::create([
                        'user_id'               => $user->id,
                        'provider'              => 'manual',
                        'plan_name'             => ucfirst($newPlan) . ' Plan',
                        'plan_type'             => $newPlan,
                        'amount'                => $amountMap[$newPlan] ?? 0.00,
                        'currency'              => 'MYR',
                        'interval'              => 'yearly',
                        'status'                => 'active',
                        'current_period_start'  => $startDate,
                        'current_period_end'    => $endDate,
                        'next_billing_date'     => $endDate,
                        'metadata'              => [
                            'admin_created' => true,
                            'admin_id'      => $request->user()?->id,
                            'source'        => 'User Management (Update)',
                        ],
                    ]);
                }
            }

            // Persist user model (we already filled non-subscription fields above;
            // SubscriptionObserver will overwrite user.subscription_* cache columns
            // AFTER the new Subscription is created, ensuring full consistency)
            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user->load(['nfcCards.landingPage', 'subscriptions'])
            ]);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Database error while updating user: ' . $e->getMessage(),
                'error_code' => $e->errorInfo[1] ?? null,
            ], 500);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage(),
            ], 500);
        }
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
        $query = NfcCard::with(['user', 'nfcTag', 'transaction', 'cancelledBy'])
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
            if ($request->status === 'pending') {
                $query->whereIn('status', ['pending_payment', 'awaiting_payment_verification']);
            } else {
                $query->where('status', $request->status);
            }
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
     * Register NFC card (admin-side manual register)
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
            'status' => 'nullable|in:pending_payment,awaiting_payment_verification,payment_verified,processing,shipped,delivered,active,inactive,expired,cancelled,replacement',
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

            $plan = $request->subscription_plan;
            $defaultStatus = $request->status ?? ($plan === 'free' ? 'active' : 'pending_payment');

            // Create NFC card
            $nfcCard = NfcCard::create([
                'user_id' => $user->id,
                'nfc_card_id' => $request->nfc_card_id,
                'card_owner' => $request->card_owner,
                'billing_address' => $request->billing_address,
                'contact_number' => $request->contact_number,
                'purchase_date' => now(),
                'subscription_plan' => $plan,
                'purchase_amount' => $request->purchase_amount,
                'payment_method' => $request->payment_method,
                'shipping_address' => $request->shipping_address,
                'notes' => $request->notes,
                'status' => $defaultStatus,
            ]);

            $shouldActivateSub = in_array($defaultStatus, ['payment_verified','processing','shipped','delivered','active']);

            if ($plan === 'free' || $shouldActivateSub) {
                // Update user subscription
                $user->update([
                    'subscription_plan' => $plan,
                    'subscription_start_date' => now(),
                    'subscription_end_date' => now()->addYear(),
                    'subscription_active' => true,
                    'has_physical_card' => true,
                ]);
                if ($defaultStatus === 'payment_verified') {
                    $nfcCard->update(['order_confirmed_at' => now()]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'NFC card registered successfully',
                'data' => $nfcCard->load(['user','transaction'])
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
            'status' => 'nullable|in:pending_payment,awaiting_payment_verification,payment_verified,processing,shipped,delivered,active,inactive,expired,cancelled,replacement',
            'tracking_number' => 'nullable|string|max:100',
            'courier' => 'nullable|string|max:100',
            'shipped_date' => 'nullable|date',
            'delivered_date' => 'nullable|date',
            'order_confirmed_at' => 'nullable|date',
            'notes' => 'nullable|string',
            'cancelled_reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except(['cancelled_by', 'transaction_id']);
        $nfcCard->update($data);

        // Auto sync subscription activation if new status is verified+
        $status = $nfcCard->status;
        if (in_array($status, ['payment_verified','processing','shipped','delivered','active'])) {
            if ($status === 'payment_verified' && !$nfcCard->order_confirmed_at) {
                $nfcCard->update(['order_confirmed_at' => now()]);
            }
            if (!$nfcCard->user->subscription_active) {
                $nfcCard->user->update([
                    'subscription_plan' => $nfcCard->subscription_plan,
                    'subscription_start_date' => now(),
                    'subscription_end_date' => now()->addYear(),
                    'subscription_active' => true,
                    'has_physical_card' => true,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'NFC card updated successfully',
            'data' => $nfcCard->load(['user','transaction'])
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
     * Verify NFC card payment (admin approve proof of payment)
     * Status: awaiting_payment_verification → payment_verified
     */
    public function verifyNfcCardPayment(Request $request, $cardId)
    {
        $admin = $request->user();
        $nfcCard = NfcCard::findOrFail($cardId);

        if ($nfcCard->status !== 'awaiting_payment_verification') {
            return response()->json([
                'success' => false,
                'message' => 'Only orders with status "Awaiting Payment Verification" can be verified'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $nfcCard->update([
                'status' => 'payment_verified',
                'order_confirmed_at' => now(),
            ]);

            $user = $nfcCard->user;

            // Sync linked transaction
            $transaction = $nfcCard->transaction;
            if ($transaction) {
                $transaction->update([
                    'status' => 'succeeded',
                    'verified_by' => $admin->id,
                    'verified_at' => now(),
                ]);
            }

            DB::commit();

            // Notify user about payment verified + profile builder access
            $this->notificationService->create($user, 'nfc_card_payment_verified', [
                'nfc_card_id' => $nfcCard->nfc_card_id,
                'amount' => 'RM' . number_format($nfcCard->purchase_amount, 2),
                'action_url' => '/UserDashboard/ProfileBuilder',
                'action_text' => 'Design Profile Now',
            ]);

            // Notify all other admins about approval
            $otherAdminIds = User::where('is_admin', true)->where('id', '!=', $admin->id)->pluck('id')->toArray();
            if (!empty($otherAdminIds)) {
                $this->notificationService->createForMultiple($otherAdminIds, 'system_message', [
                    'system_message' => 'Admin ' . ($admin->full_name ?? $admin->name ?? $admin->email) . ' has verified payment for card order #' . $nfcCard->nfc_card_id . '.',
                    'icon' => '✅',
                    'priority' => 'low',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment successfully verified. User can now start designing profile.',
                'data' => $nfcCard->load(['transaction', 'user'])
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject NFC card payment (admin reject proof)
     * Status: awaiting_payment_verification → pending_payment (allow user resubmit)
     */
    public function rejectNfcCardPayment(Request $request, $cardId)
    {
        $admin = $request->user();
        $nfcCard = NfcCard::findOrFail($cardId);

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|min:5|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($nfcCard->status !== 'awaiting_payment_verification') {
            return response()->json([
                'success' => false,
                'message' => 'Only orders with status "Awaiting Payment Verification" can be rejected'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $nfcCard->update([
                'status' => 'pending_payment',
            ]);

            $transaction = $nfcCard->transaction;
            if ($transaction) {
                $transaction->update([
                    'status' => 'failed',
                    'failure_message' => $request->rejection_reason,
                    'verified_by' => $admin->id,
                    'verified_at' => now(),
                ]);
            }

            DB::commit();

            $this->notificationService->create($nfcCard->user, 'nfc_card_payment_rejected', [
                'nfc_card_id' => $nfcCard->nfc_card_id,
                'rejection_reason' => $request->rejection_reason,
                'action_url' => '/UserDashboard/CardManagement',
                'action_text' => 'Upload New Proof',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment proof rejected. User notified to upload new proof.',
                'data' => $nfcCard->load(['transaction','user'])
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark NFC card as Processing (preparing for shipping)
     * payment_verified → processing
     */
    public function markNfcCardProcessing(Request $request, $cardId)
    {
        $admin = $request->user();
        $nfcCard = NfcCard::findOrFail($cardId);

        if (!$nfcCard->canMarkAsProcessing()) {
            return response()->json([
                'success' => false,
                'message' => 'Current card status cannot be changed to Processing (must be Payment Verified first)'
            ], 422);
        }

        $nfcCard->update(['status' => 'processing']);

        $this->notificationService->create($nfcCard->user, 'nfc_card_processing', [
            'nfc_card_id' => $nfcCard->nfc_card_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Card marked as processing.',
            'data' => $nfcCard->load(['user','transaction'])
        ]);
    }

    /**
     * Mark NFC card as Shipped (already posted)
     * payment_verified / processing → shipped
     */
    public function markNfcCardShipped(Request $request, $cardId)
    {
        $admin = $request->user();
        $nfcCard = NfcCard::findOrFail($cardId);

        $validator = Validator::make($request->all(), [
            'tracking_number' => 'required|string|max:100',
            'courier' => 'nullable|string|max:100',
            'shipped_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if (!$nfcCard->canMarkAsShipped()) {
            return response()->json([
                'success' => false,
                'message' => 'Current card status cannot be marked as shipped.'
            ], 422);
        }

        $nfcCard->update([
            'status' => 'shipped',
            'tracking_number' => $request->tracking_number,
            'courier' => $request->courier ?? null,
            'shipped_date' => $request->shipped_date ?? now(),
        ]);

        $this->notificationService->create($nfcCard->user, 'nfc_card_shipped', [
            'nfc_card_id' => $nfcCard->nfc_card_id,
            'tracking_number' => $request->tracking_number,
            'courier' => $request->courier ?? 'Pos Laju Standard',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Card marked as shipped.',
            'data' => $nfcCard->load(['user','transaction'])
        ]);
    }

    /**
     * ⚠️ EMERGENCY OVERRIDE ONLY: Admin force mark NFC card as Delivered
     * NORMAL flow: USER presses "I Have Received My Card" in CardManagement
     * Use this method ONLY if user doesn't confirm after 14+ days / customer support claim.
     * shipped → delivered
     */
    public function markNfcCardDelivered(Request $request, $cardId)
    {
        $admin = $request->user();
        $nfcCard = NfcCard::findOrFail($cardId);

        if (!$nfcCard->canMarkAsDelivered()) {
            return response()->json([
                'success' => false,
                'message' => 'Current card status cannot be marked as delivered (must be Shipped first).'
            ], 422);
        }

        // Emergency override requires explicit reason for audit trail
        $validator = Validator::make($request->all(), [
            'override_reason' => 'required|string|min:5|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Override reason is required (emergency admin force delivered).',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $nfcCard->update([
                'status' => 'delivered',
                'delivered_date' => $request->delivered_date ?? now(),
                'user_received_confirmed_at' => now(),
                'cancelled_by' => $admin->id, // Reuse field to store admin_override_id (audit trail)
                'cancelled_reason' => '[ADMIN EMERGENCY OVERRIDE FORCE DELIVERED] ' . $request->override_reason,
            ]);

            $user = $nfcCard->user;
            if (!$user->subscription_active) {
                $user->update([
                    'subscription_plan' => $nfcCard->subscription_plan,
                    'subscription_start_date' => now(),
                    'subscription_end_date' => now()->addYear(),
                    'subscription_active' => true,
                    'has_physical_card' => true,
                ]);
            }

            if ($nfcCard->transaction && $nfcCard->transaction->status !== 'succeeded') {
                $nfcCard->transaction->update([
                    'status' => 'succeeded',
                    'verified_at' => $nfcCard->transaction->verified_at ?? now(),
                    'verified_by' => $admin->id,
                ]);
            }

            DB::commit();

            // Non-critical audit log + warning (not normal user flow)
            try {
                ActivityLog::create([
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'user_id' => $admin->id,
                    'business_account_id' => $admin->isBusinessAccount() ? $admin->id : ($admin->business_account_id ?? null),
                    'action_type' => 'nfc_card_admin_override_delivered',
                    'action_description' => 'ADMIN OVERRIDE: Force mark card #' . $nfcCard->nfc_card_id . ' as delivered. Reason: ' . $request->override_reason,
                    'entity_type' => 'nfc_card',
                    'entity_id' => $nfcCard->id,
                    'metadata' => json_encode([
                        'admin_id' => $admin->id,
                        'admin_email' => $admin->email,
                        'card_id' => $nfcCard->nfc_card_id,
                        'user_email' => $user->email ?? null,
                        'override_reason' => $request->override_reason,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) { report($e); }

            Log::warning('Admin ' . ($admin->email ?? $admin->id) . ' EMERGENCY OVERRIDE delivered for card ' . $nfcCard->nfc_card_id . ' (user: ' . ($user->email ?? 'n/a') . ') — reason: ' . $request->override_reason);

            $this->notificationService->create($user, 'nfc_card_delivered', [
                'order_number' => $nfcCard->nfc_card_id,
                'message' => 'Admin has manually confirmed receipt of your card (not by you). If this is incorrect, please contact support.',
            ]);

            return response()->json([
                'success' => true,
                'warning' => true,
                'message' => '⚠️ [EMERGENCY OVERRIDE OK] Card marked as delivered by ADMIN. NORMAL flow: user presses "I Have Received My Card" themselves. Activity log audit trail has been recorded.',
                'data' => $nfcCard->load(['user','transaction'])
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark delivered: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel NFC card order (any status before delivered/active)
     */
    public function cancelNfcCard(Request $request, $cardId)
    {
        $admin = $request->user();
        $nfcCard = NfcCard::findOrFail($cardId);

        $validator = Validator::make($request->all(), [
            'cancellation_reason' => 'required|string|min:3|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if (!$nfcCard->canCancel()) {
            return response()->json([
                'success' => false,
                'message' => 'Cards that are already delivered/activated cannot be cancelled.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $nfcCard->update([
                'status' => 'cancelled',
                'cancelled_by' => $admin->id,
                'cancelled_reason' => $request->cancellation_reason,
            ]);

            if ($nfcCard->transaction && !in_array($nfcCard->transaction->status, ['succeeded','refunded'])) {
                $nfcCard->transaction->update([
                    'status' => 'cancelled',
                    'failure_message' => 'Order cancelled by admin: ' . $request->cancellation_reason,
                ]);
            }

            DB::commit();

            $this->notificationService->create($nfcCard->user, 'nfc_card_cancelled', [
                'nfc_card_id' => $nfcCard->nfc_card_id,
                'cancellation_reason' => $request->cancellation_reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Card order has been cancelled.',
                'data' => $nfcCard->load(['user','transaction','cancelledBy'])
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system statistics
     *
     * PERFORMANCE OPTIMIZATION:
     * Combine 12+ sequential COUNT(*) queries into 4 aggregate queries using
     * conditional COUNT(CASE WHEN ...) so each table is scanned only ONCE
     * (was: ~17 full table scans per admin dashboard load).
     */
    public function getSystemStats(Request $request)
    {
        $currentMonth = now()->month;
        $currentYear  = now()->year;

        $usersAgg = (array) User::query()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('COUNT(CASE WHEN subscription_active = 1 THEN 1 END) AS active')
            ->selectRaw('COUNT(CASE WHEN is_admin = 1 THEN 1 END) AS admin')
            ->selectRaw("COUNT(CASE WHEN subscription_plan = 'free' THEN 1 END) AS free")
            ->selectRaw("COUNT(CASE WHEN subscription_plan = 'basic' THEN 1 END) AS basic")
            ->selectRaw("COUNT(CASE WHEN subscription_plan = 'premium' THEN 1 END) AS premium")
            ->selectRaw("COUNT(CASE WHEN subscription_plan = 'business' THEN 1 END) AS business")
            ->first()
            ?->getAttributes() ?? [];

        $newThisMonth = (int) User::query()
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->count('id');

        $cardsAgg = (array) NfcCard::query()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("COUNT(CASE WHEN status = 'active' THEN 1 END) AS active")
            ->selectRaw("COUNT(CASE WHEN status = 'pending' THEN 1 END) AS pending")
            ->selectRaw("COUNT(CASE WHEN status = 'shipped' THEN 1 END) AS shipped")
            ->first()
            ?->getAttributes() ?? [];

        $analyticsAgg = (array) Analytics::query()
            ->selectRaw('COUNT(*) AS total_tracks')
            ->selectRaw("COUNT(CASE WHEN action = 'profile_view' THEN 1 END) AS profile_views")
            ->selectRaw("COUNT(CASE WHEN action = 'nfc_tap' THEN 1 END) AS nfc_taps")
            ->selectRaw("COUNT(CASE WHEN action = 'link_click' THEN 1 END) AS link_clicks")
            ->first()
            ?->getAttributes() ?? [];

        $lpAgg = (array) LandingPage::query()
            ->selectRaw('COUNT(CASE WHEN profile_image IS NOT NULL THEN 1 END) AS landing_pages')
            ->selectRaw('COUNT(CASE WHEN company_logo IS NOT NULL THEN 1 END) AS logos')
            ->first()
            ?->getAttributes() ?? [];

        $toInt = static fn ($v) => (int) ($v ?? 0);

        $stats = [
            'users' => [
                'total'           => $toInt($usersAgg['total'] ?? null),
                'active'          => $toInt($usersAgg['active'] ?? null),
                'admin'           => $toInt($usersAgg['admin'] ?? null),
                'new_this_month'  => $newThisMonth,
            ],
            'subscriptions' => [
                'free'     => $toInt($usersAgg['free'] ?? null),
                'basic'    => $toInt($usersAgg['basic'] ?? null),
                'premium'  => $toInt($usersAgg['premium'] ?? null),
                'business' => $toInt($usersAgg['business'] ?? null),
            ],
            'nfc_cards' => [
                'total'   => $toInt($cardsAgg['total'] ?? null),
                'active'  => $toInt($cardsAgg['active'] ?? null),
                'pending' => $toInt($cardsAgg['pending'] ?? null),
                'shipped' => $toInt($cardsAgg['shipped'] ?? null),
            ],
            'analytics' => [
                'total_tracks'  => $toInt($analyticsAgg['total_tracks'] ?? null),
                'profile_views' => $toInt($analyticsAgg['profile_views'] ?? null),
                'nfc_taps'      => $toInt($analyticsAgg['nfc_taps'] ?? null),
                'link_clicks'   => $toInt($analyticsAgg['link_clicks'] ?? null),
            ],
            'storage' => [
                'total_used'    => $this->getStorageUsage(),
                'landing_pages' => $toInt($lpAgg['landing_pages'] ?? null),
                'logos'         => $toInt($lpAgg['logos'] ?? null),
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
