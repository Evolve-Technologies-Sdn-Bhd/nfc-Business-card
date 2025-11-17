<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
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

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

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

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

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
        return $this->hasOne(NfcCard::class)->where('status', 'active');
    }

    public function hasPremiumSubscription()
    {
        return in_array($this->subscription_plan, ['premium', 'basic','business']) && $this->subscription_active;
    }
    public function hasBasicSubscription()
{
    return in_array($this->subscription_plan, ['basic', 'premium', 'business']) && $this->subscription_active;
}

    public function hasPhysicalCard()
    {
        return $this->nfcCards()->where('status', 'active')->exists();
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
     * Get quota information for Business account
     * Note: total_account_slots serves as the unified quota for both accounts and cards
     * If admin assigns 10 slots, business can have max 10 accounts (including employees) and 10 cards total
     */
    public function getQuotaInfo(): array
    {
        if (!$this->isBusinessAccount()) {
            return [
                'total_quota' => 0,
                'employees_count' => 0,
                'ordered_cards_count' => 0,
                'available_quota' => 0,
            ];
        }

        // Use total_account_slots as the unified quota
        $totalQuota = $this->total_account_slots ?? 0;
        
        // Count employees (not including the business owner)
        $employeesCount = $this->employees()->count();
        
        // Count ordered Business Plan cards (including business owner's card)
        $orderedCardsCount = NfcCard::where('business_account_id', $this->id)
            ->where('subscription_plan', 'business')
            ->count();

        // Total accounts = 1 (business owner) + employees
        $totalAccounts = 1 + $employeesCount;
        
        // Available quota is the minimum of:
        // 1. Remaining account slots (total - current accounts)
        // 2. Remaining card quota (total - ordered cards)
        $availableForAccounts = max(0, $totalQuota - $totalAccounts);
        $availableForCards = max(0, $totalQuota - $orderedCardsCount);

        return [
            'total_quota' => $totalQuota,
            'total_account_slots' => $totalQuota, // For backward compatibility
            'total_card_quota' => $totalQuota, // For backward compatibility
            'employees_count' => $employeesCount,
            'total_accounts' => $totalAccounts,
            'ordered_cards_count' => $orderedCardsCount,
            'available_account_slots' => $availableForAccounts,
            'available_card_quota' => $availableForCards,
            'available_quota' => min($availableForAccounts, $availableForCards),
        ];
    }
}


