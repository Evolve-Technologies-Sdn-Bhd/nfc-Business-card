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
}


