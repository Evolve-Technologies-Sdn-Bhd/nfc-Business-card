<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
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
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Create a profile when a user is created
        static::created(function ($user) {
            // Check if profile doesn't already exist (in case it was created in controller)
            if (!$user->profile()->exists()) {
                $slug = Str::slug($user->full_name ?? $user->email);
                $originalSlug = $slug;
                $count = 1;

                while (Profile::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }

                $user->profile()->create([
                    'slug' => $slug,
                    'name' => $user->full_name ?? $user->email,
                    'title' => $user->job_title,
                    'company' => $user->company,
                    'email' => $user->email,
                ]);
            }
        });
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
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
     * Get or create profile
     */
    public function getProfileAttribute()
    {
        if (!$this->relationLoaded('profile') || !$this->getRelation('profile')) {
            $profile = $this->profile()->first();

            if (!$profile) {
                // Create profile with unique slug
                $slug = Str::slug($this->full_name ?? $this->email);
                $originalSlug = $slug;
                $count = 1;

                while (Profile::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }

                $profile = $this->profile()->create([
                    'slug' => $slug,
                    'name' => $this->full_name ?? $this->email,
                    'title' => $this->job_title,
                    'company' => $this->company,
                    'email' => $this->email,
                ]);
            }

            $this->setRelation('profile', $profile);
        }

        return $this->getRelation('profile');
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


