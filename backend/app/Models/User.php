<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Subscription SOURCE OF TRUTH is the `subscriptions` table.
     * Columns users.subscription_* are maintained LEGACY — kept for query performance
     * and backward compatibility; they are write-through synced via Subscription Observer.
     * Never read these values directly when Subscription relationship is available.
     */

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'name_slug',
        'password',
        'provider',     
        'provider_id',
        'phone',
        'company',
        'job_title',
        'plan',
        'subscription_plan',
        'subscription_start_date',
        'subscription_end_date',
        'subscription_active',
        'stripe_customer_id',
        'stripe_subscription_id',
        'has_physical_card',
        'last_login_at',
        'two_factor_enabled',
        'two_factor_secret',
        'settings',
        'is_admin',
        'admin_role',
        'admin_permissions',
        'last_admin_action_at',
        'is_new_user',
        'account_image',
        'total_account_slots',
        'total_card_quota',
        'parent_business_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'subscription_start_date' => 'date',
        'subscription_end_date' => 'date',
        'subscription_active' => 'boolean',
        'has_physical_card' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'settings' => 'array',
        'is_admin' => 'boolean',
        'admin_permissions' => 'array',
        'last_admin_action_at' => 'datetime',
        'is_new_user' => 'boolean',
    ];

    protected $appends = ['full_name'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (User $user) {
            if (empty($user->name_slug)) {
                $user->name_slug = self::buildNameSlug(
                    $user->full_name ?: trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))
                );
            }
        });

        static::updating(function (User $user) {
            $nameChanged =
                $user->isDirty('first_name') || $user->isDirty('last_name');
            if ($nameChanged && empty($user->getOriginal('name_slug'))) {
                $user->name_slug = self::buildNameSlug(
                    trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))
                );
            }
        });
    }

    /**
     * Build a URL-safe slug from a user's display name.
     *
     * NOTE: name_slug is intentionally NOT unique. Multiple users who share the
     * same legal name (e.g. many "Ahmad bin Abdullah" accounts) will share the
     * same slug. Actual uniqueness/disambiguation is done at the URL layer with
     * an optional 3rd segment: /profile/{slug}/{cardNo}/userid-{userPk}
     */
    public static function buildNameSlug(string $rawName): string
    {
        $base = $rawName ? Str::slug($rawName) : '';
        if ($base === '') {
            $base = 'user';
        }
        return $base;
    }

    /**
     * Whether this user's name_slug clashes with another active registered user.
     * TRUE means the pretty URL must include the "/userid-{id}" suffix to be unique.
     * Uses the pre-loaded `nameSlugClashCount` relation (set via addSelect/subquery)
     * when available to avoid N+1 queries on list pages.
     */
    public function getHasNameSlugClashAttribute(): bool
    {
        if (empty($this->name_slug)) {
            return false;
        }
        if ($this->relationLoaded('nameSlugClashCount')) {
            return (int) $this->getRelation('nameSlugClashCount') > 1;
        }
        $count = (int) self::where('name_slug', $this->name_slug)->count();
        return $count > 1;
    }

    /**
     * Bulk-load name_slug clash counts onto a collection of Users using a single query.
     * Sets the `nameSlugClashCount` pseudo-relation so has_name_slug_clash accessor
     * can answer without additional DB hits.
     *
     * @param  \Illuminate\Database\Eloquent\Collection|array  $users
     * @return void
     */
    public static function hydrateNameSlugClashCounts($users): void
    {
        $userArr = $users instanceof \Illuminate\Database\Eloquent\Collection
            ? $users->all()
            : (is_array($users) ? $users : []);

        $slugs = [];
        foreach ($userArr as $u) {
            if ($u instanceof self && !empty($u->name_slug)) {
                $slugs[] = $u->name_slug;
            }
        }
        $slugs = array_values(array_unique($slugs));

        if ($slugs === []) {
            foreach ($userArr as $u) {
                if ($u instanceof self) {
                    $u->setRelation('nameSlugClashCount', 0);
                }
            }
            return;
        }

        $counts = self::query()
            ->whereIn('name_slug', $slugs)
            ->selectRaw('name_slug, COUNT(*) as c')
            ->groupBy('name_slug')
            ->pluck('c', 'name_slug')
            ->all();

        foreach ($userArr as $u) {
            if (!($u instanceof self)) {
                continue;
            }
            $u->setRelation(
                'nameSlugClashCount',
                empty($u->name_slug) ? 0 : (int) ($counts[$u->name_slug] ?? 0)
            );
        }
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    // =========================================================================
    // Subscription relationship — the SINGLE SOURCE OF TRUTH for paid status
    // =========================================================================

    /**
     * Subscriptions (all history — lifecycle: active → cancelled → soft-deleted.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class)->latest('next_billing_date');
    }

    /**
     * The single authoritative ACTIVE or MOST RECENT subscription for plan-level decisions.
     * Eager load this as `with('latestSubscription')` whenever possible.
     */
    public function latestSubscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    /**
     * Convenience scope to eager-load only active subscriptions (used by listings.
     */
    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)->where('status', 'active')->ofMany('created_at', 'max');
    }

    // =========================================================================
    // Legacy column accessors OVERRIDE — return subscriptions-backed values (read-through cache
    // Uses subscriptions table first, fall back to raw column for rows not yet migrated.
    // =========================================================================

    /**
     * Source-of-truth plan name. Prefer subscriptions over legacy column.
     * Only hits DB if subscriptions/latestSubscription are NOT already loaded (lazy fallback).
     */
    public function getSubscriptionPlanAttribute($value)
    {
        $latest = $this->getRelationValue('latestSubscription') ?? null;

        if (!$latest && $this->relationLoaded('subscriptions')) {
            $latest = $this->subscriptions->firstWhere('status', 'active') ?? $this->subscriptions->first();
        }

        if (!$latest) {
            if ($this->exists && !$this->relationLoaded('subscriptions') && !$this->relationLoaded('latestSubscription')) {
                try {
                    $latest = $this->subscriptions()->where('status', 'active')->latest('created_at')->first(['plan_type']);
                } catch (\Throwable) {
                    $latest = null;
                }
            }
        }

        if ($latest && !empty($latest->plan_type)) {
            return strtolower($latest->plan_type);
        }

        // Fallback to legacy column (may be empty/null on free users)
        return $value ?: 'free';
    }

    /**
     * Source-of-truth boolean active flag.
     * Uses loaded subscriptions relation whenever available; only lazy-loads on
     * direct single-model access (never on serialized collections unless eager-loaded).
     */
    public function getSubscriptionActiveAttribute($value): bool
    {
        if ($this->relationLoaded('subscriptions')) {
            $active = $this->subscriptions->firstWhere('status', 'active');
            if ($active instanceof Subscription) {
                return true;
            }
        } elseif ($this->relationLoaded('latestSubscription')) {
            $latest = $this->getRelation('latestSubscription');
            if ($latest instanceof Subscription && $latest->status === 'active') {
                return true;
            }
        } elseif ($this->exists) {
            try {
                $exists = $this->subscriptions()->where('status', 'active')->exists();
                if ($exists) {
                    return true;
                }
            } catch (\Throwable) {
                // fall through to legacy column
            }
        }
        return (bool) $value;
    }

    /**
     * Source-of-truth subscription start date (first ever active sub start, or legacy).
     */
    public function getSubscriptionStartDateAttribute($value)
    {
        if ($this->relationLoaded('subscriptions')) {
            $first = $this->subscriptions->sortBy('created_at')->first();
            if ($first?->current_period_start ?? null) {
                return $first->current_period_start?->toDateString();
            }
        }
        return $value;
    }

    /**
     * Source-of-truth subscription end date (next billing of active subs or cancelled_at end.
     */
    public function getSubscriptionEndDateAttribute($value)
    {
        if ($this->relationLoaded('subscriptions')) {
            $active = $this->subscriptions->firstWhere('status', 'active');
            if ($active?->next_billing_date ?? false) {
                return $active->next_billing_date->toDateString();
            }
            $cancelled = $this->subscriptions->firstWhere('status', 'cancelled');
            if ($cancelled?->current_period_end ?? false) {
                return $cancelled->current_period_end->toDateString();
            }
        }
        return $value;
    }

    // =========================================================================
    // NFC relationships (kept intact below untouched
    // =========================================================================

    public function nfcTag()
    {
        return $this->hasOne(NfcTag::class);
    }

    public function nfcTags()
    {
        return $this->hasMany(NfcTag::class);
    }

    public function nfcCards()
    {
        return $this->hasMany(NfcCard::class);
    }

    // Profile relationship removed - using landing_pages instead

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function analytics()
    {
        return $this->morphMany(Analytics::class, 'trackable');
    }

    public function activeNfcCard()
    {
        return $this->hasOne(NfcCard::class)->whereIn('status', ['active', 'delivered', 'payment_verified', 'processing', 'shipped']);
    }

    /**
     * Plan check helpers — now use accessors (accessors are SoT-backed already).
     */
    public function hasPremiumSubscription()
    {
        $hasActiveSub = in_array($this->subscription_plan, ['premium', 'basic','business'], true) && $this->subscription_active;
        if ($hasActiveSub) {
            return true;
        }
        if ($this->relationLoaded('nfcCards')) {
            return $this->getRelation('nfcCards')->contains(function ($c) {
                return in_array($c->status, ['payment_verified','processing','shipped','delivered','active'], true);
            });
        }
        return (bool) $this->nfcCards()
            ->whereIn('status', ['payment_verified','processing','shipped','delivered','active'])
            ->limit(1)
            ->count();
    }

    public function hasBasicSubscription()
    {
        $hasActiveSub = in_array($this->subscription_plan, ['basic', 'premium', 'business'], true) && $this->subscription_active;
        if ($hasActiveSub) {
            return true;
        }
        if ($this->relationLoaded('nfcCards')) {
            return $this->getRelation('nfcCards')->contains(function ($c) {
                return in_array($c->status, ['payment_verified','processing','shipped','delivered','active'], true);
            });
        }
        return (bool) $this->nfcCards()
            ->whereIn('status', ['payment_verified','processing','shipped','delivered','active'])
            ->limit(1)
            ->count();
    }

    /**
     * Strictly Business-plan only (includes active and trial grace period expired).
     */
    public function hasBusinessSubscription(): bool
    {
        if ($this->subscription_plan === 'business' && $this->subscription_active) {
            return true;
        }
        return $this->nfcCards()
            ->where('subscription_plan', 'business')
            ->whereIn('status', ['payment_verified','processing','shipped','delivered','active'])
            ->exists();
    }

    public function hasPhysicalCard()
    {
        return $this->nfcCards()->whereIn('status', ['active', 'delivered', 'shipped', 'processing', 'payment_verified'])->exists();
    }

    /**
     * Check whether user is allowed to use paid ProfileBuilder features
     * for a given plan tier (optional). Falls back to card-order status when
     * subscription isn't officially active yet (e.g. payment verified, waiting for delivery).
     */
    public function canAccessPaidProfileBuilder(?string $plan = null): bool
    {
        $eligibleStatuses = ['payment_verified','processing','shipped','delivered','active'];

        if ($plan === null) {
            // Any paid card/subscription is fine
            return $this->subscription_active
                || $this->nfcCards()->whereIn('status', $eligibleStatuses)->exists();
        }

        $planMatch = in_array($plan, ['premium','business'], true)
            ? [$plan]
            : ['basic','premium','business'];

        // Active subscription matches requested tier
        if ($this->subscription_active && in_array($this->subscription_plan, $planMatch, true)) {
            return true;
        }

        // Has card order matching tier, with verified+ status
        return $this->nfcCards()
            ->whereIn('subscription_plan', $planMatch)
            ->whereIn('status', $eligibleStatuses)
            ->exists();
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin()
    {
        return $this->is_admin === true;
    }

    /**
     * Check if user is a super admin
     */
    public function isSuperAdmin()
    {
        return $this->is_admin === true && $this->admin_role === 'super_admin';
    }

    /**
     * Check if user has specific admin permission
     */
    public function hasAdminPermission($permission)
    {
        if (!$this->is_admin) {
            return false;
        }

        if ($this->admin_role === 'super_admin') {
            return true;
        }

        $permissions = $this->admin_permissions ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Get admin role display name
     */
    public function getAdminRoleDisplayAttribute()
    {
        if (!$this->is_admin) {
            return 'User';
        }

        return match ($this->admin_role) {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'moderator' => 'Moderator',
            default => 'Admin'
        };
    }

    /**
     * Get the social identities for the user
     */
    public function socialIdentities(): HasMany
    {
        return $this->hasMany(SocialIdentity::class);
    }

    /**
     * Get the remember tokens for the user
     */
    public function rememberTokens(): HasMany
    {
        return $this->hasMany(RememberToken::class);
    }

    /**
     * Check if user has a specific provider linked
     */
    public function hasProvider(string $provider): bool
    {
        return $this->socialIdentities()
            ->where('provider', $provider)
            ->exists();
    }

    /**
     * Get social identity for a specific provider
     */
    public function getProviderIdentity(string $provider): ?SocialIdentity
    {
        return $this->socialIdentities()
            ->where('provider', $provider)
            ->first();
    }

    /**
     * Get employees under this Business account
     */
    public function employees(): HasMany
    {
        return $this->hasMany(User::class, 'parent_business_id');
    }

    /**
     * Get parent Business account (if this user is an employee)
     */
    public function parentBusiness()
    {
        return $this->belongsTo(User::class, 'parent_business_id');
    }

    /**
     * Get custom field/feature assignment for Business user
     */
    public function businessUserAssignment()
    {
        return $this->hasOne(BusinessUserAssignment::class);
    }

    /**
     * Get Business account ID (own ID if business account, or parent's ID if employee)
     */
    public function getBusinessAccountIdAttribute()
    {
        if ($this->subscription_plan === 'business' && !$this->parent_business_id) {
            return $this->id;
        }
        return $this->parent_business_id;
    }

    /**
     * Check if user is a Business account owner
     */
    public function isBusinessAccount(): bool
    {
        return $this->subscription_plan === 'business' && !$this->parent_business_id;
    }

    /**
     * Check if user is an employee under a Business account
     */
    public function isBusinessEmployee(): bool
    {
        return $this->parent_business_id !== null;
    }

    /**
     * Check if user signed up via OAuth only (Google/Apple) and has no local password.
     * These users cannot reset password - they must continue using OAuth login.
     */
    public function isOAuthOnly(): bool
    {
        // User has OAuth provider set and password is null or empty
        return !empty($this->provider) && (is_null($this->password) || $this->password === '');
    }

    /**
     * Check if user has a local password (can use password reset).
     * This includes users who registered with email+password, even if they later linked OAuth.
     */
    public function hasLocalPassword(): bool
    {
        return !is_null($this->password) && $this->password !== '';
    }

    /**
     * Get the OAuth provider name for display purposes.
     */
    public function getOAuthProviderDisplayName(): ?string
    {
        if (empty($this->provider)) {
            return null;
        }

        return match (strtolower($this->provider)) {
            'google' => 'Google',
            'apple' => 'Apple',
            default => ucfirst($this->provider),
        };
    }

    /**
     * Get quota information for Business account
     * Note: total_account_slots serves as the unified quota for both accounts and cards
     * If admin assigns 10 slots, business can have max 10 accounts (including employees) and 10 cards total.
     * Uses already-loaded Eloquent relations whenever possible to avoid N+1 DB queries.
     *
     * @param  bool  $useLoadedRelations  If true and relations are loaded, count via collection.
     */
    public function getQuotaInfo(bool $useLoadedRelations = true): array
    {
        if (!$this->isBusinessAccount()) {
            return [
                'total_quota' => 0,
                'employees_count' => 0,
                'ordered_cards_count' => 0,
                'available_quota' => 0,
            ];
        }

        $totalQuota = $this->total_account_slots ?? 0;

        // Employee count — use loaded `employees` relation on collection if available
        if ($useLoadedRelations && $this->relationLoaded('employees')) {
            $employeesCount = $this->getRelation('employees')->count();
        } else {
            $employeesCount = $this->employees()->count();
        }

        // Ordered cards count — use loaded `nfcCards` relation filtered when available
        if ($useLoadedRelations && $this->relationLoaded('nfcCards')) {
            $orderedCardsCount = $this->getRelation('nfcCards')
                ->where('subscription_plan', 'business')
                ->count();
        } else {
            $orderedCardsCount = NfcCard::where('business_account_id', $this->id)
                ->where('subscription_plan', 'business')
                ->count();
        }

        $totalAccounts = 1 + $employeesCount;
        $availableForAccounts = max(0, $totalQuota - $totalAccounts);
        $availableForCards = max(0, $totalQuota - $orderedCardsCount);

        return [
            'total_quota' => $totalQuota,
            'total_account_slots' => $totalQuota,
            'total_card_quota' => $totalQuota,
            'employees_count' => $employeesCount,
            'total_accounts' => $totalAccounts,
            'ordered_cards_count' => $orderedCardsCount,
            'available_account_slots' => $availableForAccounts,
            'available_card_quota' => $availableForCards,
            'available_quota' => min($availableForAccounts, $availableForCards),
        ];
    }
}


