<?php

namespace App\Services;

use App\Models\BusinessSetting;
use App\Models\LandingPage;
use App\Models\NfcCard;
use App\Models\User;

/**
 * Rules of a Business tenant: one owner (the first Business account, the
 * "Business admin") and the member accounts that hang off it through
 * users.parent_business_id.
 *
 *  - Members do not have a Business plan of their own: their access is derived
 *    from the owner's (owner still on an active Business plan) AND their own
 *    seat being active (users.subscription_active, toggled by the owner).
 *  - The owner shares company data with members (business_settings.shared_fields)
 *    and decides which profile fields members may not change (locked_fields).
 *    Locks are applied here, on the server, on every member profile save.
 */
class BusinessTenantService
{
    /**
     * Landing-page columns the owner may share and lock, grouped for the UI.
     * Anything not listed here can never be locked (members keep full control
     * of their personal fields: name, title, phone, bio, photo, ...).
     */
    public const LOCKABLE_FIELDS = [
        'company' => [
            'company_name', 'company_logo', 'company_logo_text', 'company_registration_no',
            'company_description', 'industry', 'website', 'website_url',
            'address', 'address_name', 'address_street', 'address_area',
            'address_city_state', 'address_country', 'address_map_url', 'postal_code',
        ],
        'branding' => [
            'profile_style', 'theme', 'background_color', 'text_color', 'accent_color',
            'bg_gradient', 'card_style', 'button_style', 'color_scheme', 'font', 'layout',
            'cover_banner',
        ],
    ];

    /** Pseudo field: members' cards must be ordered with the company card template. */
    public const CARD_TEMPLATE_LOCK = 'card_template';

    public static function lockableKeys(): array
    {
        return array_merge(...array_values(self::LOCKABLE_FIELDS));
    }

    /** The owner of $user's tenant (the user themself for an owner), or null. */
    public function ownerOf(User $user): ?User
    {
        if ($user->isBusinessAccount()) {
            return $user;
        }
        if ($user->isBusinessEmployee()) {
            return $user->relationLoaded('parentBusiness')
                ? $user->getRelation('parentBusiness')
                : $user->parentBusiness()->first();
        }
        return null;
    }

    /**
     * The member's own seat switch. Read from the raw column: the accessor on
     * User looks at the subscriptions table first, and a member has no
     * subscription of their own.
     */
    public function seatActive(User $member): bool
    {
        return (bool) ($member->getAttributes()['subscription_active'] ?? false);
    }

    /**
     * May this member use the Business plan right now?
     * Requires the owner to still hold Business access and the seat to be on.
     */
    public function memberHasAccess(User $member): bool
    {
        if (!$member->isBusinessEmployee() || !$this->seatActive($member)) {
            return false;
        }

        $owner = $member->parentBusiness()->first();

        // An owner that was downgraded is no longer a Business account; its
        // members lose Business access with it.
        return $owner !== null
            && $owner->isBusinessAccount()
            && app(EntitlementService::class)->hasBusinessAccess($owner);
    }

    /** A member whose seat the owner switched off: may not sign in. */
    public function isSuspendedMember(User $user): bool
    {
        return $user->isBusinessEmployee() && !$this->seatActive($user);
    }

    public function settingsFor(User $owner): BusinessSetting
    {
        return BusinessSetting::firstOrNew(
            ['owner_id' => $owner->id],
            ['shared_fields' => [], 'locked_fields' => []]
        );
    }

    /** Locked keys that apply to $user (none for an owner or a non-Business user). */
    public function lockedFieldsFor(User $user): array
    {
        if (!$user->isBusinessEmployee()) {
            return [];
        }
        $owner = $this->ownerOf($user);
        return $owner ? array_values($this->settingsFor($owner)->locked_fields ?? []) : [];
    }

    public function isLockedFor(User $user, string $key): bool
    {
        return in_array($key, $this->lockedFieldsFor($user), true);
    }

    /** 403 response for a member touching a locked field, or null when allowed. */
    public function denyIfLocked(User $user, string $key)
    {
        if (!$this->isLockedFor($user, $key)) {
            return null;
        }

        return response()->json([
            'success' => false,
            'code' => 'FIELD_LOCKED_BY_COMPANY',
            'message' => 'This field is managed by your company administrator.',
            'field' => $key,
        ], 403);
    }

    /**
     * Strip / overwrite locked fields in a member's landing-page payload.
     *
     * A locked field with a shared company value is forced to that value; a
     * locked field without one keeps whatever is stored (the key is dropped).
     * The owner and non-Business users pass through untouched.
     *
     * @return array{0: array, 1: string[]} [clean payload, keys that were overridden]
     */
    public function applyLocks(User $user, array $data): array
    {
        if (!$user->isBusinessEmployee()) {
            return [$data, []];
        }

        $owner = $this->ownerOf($user);
        if (!$owner) {
            return [$data, []];
        }

        $settings = $this->settingsFor($owner);
        $shared = $settings->shared_fields ?? [];
        $overridden = [];

        foreach ($settings->locked_fields ?? [] as $key) {
            if ($key === self::CARD_TEMPLATE_LOCK) {
                continue;
            }
            if (array_key_exists($key, $shared)) {
                if (($data[$key] ?? null) !== $shared[$key]) {
                    $overridden[] = $key;
                }
                $data[$key] = $shared[$key];
            } elseif (array_key_exists($key, $data)) {
                $overridden[] = $key;
                unset($data[$key]);
            }
        }

        return [$data, $overridden];
    }

    /**
     * Shared values members inherit even when they are not locked: used to
     * prefill a member profile that has never been saved.
     */
    public function sharedFieldsFor(User $user): array
    {
        $owner = $this->ownerOf($user);
        return $owner ? ($this->settingsFor($owner)->shared_fields ?? []) : [];
    }

    /**
     * Push the locked, shared values onto every existing member profile so the
     * public pages show the company data the moment the owner changes it.
     *
     * @return int number of landing pages updated
     */
    public function syncMembers(User $owner): int
    {
        $settings = $this->settingsFor($owner);
        $shared = $settings->shared_fields ?? [];
        $values = array_intersect_key($shared, array_flip($settings->locked_fields ?? []));

        if ($values === []) {
            return 0;
        }

        $memberIds = $owner->employees()->pluck('id');
        $cardIds = NfcCard::whereIn('user_id', $memberIds)->pluck('id');

        $updated = 0;
        LandingPage::whereIn('nfc_card_id', $cardIds)->each(function (LandingPage $page) use ($values, &$updated) {
            $page->fill($values)->save();
            $updated++;
        });

        return $updated;
    }

    /**
     * The owner is being deleted. Members must not be deleted with it (the old
     * FK cascade did that, taking their cards and profiles along): they are
     * detached and their seat switched off, so the accounts and data survive and
     * NFCGo can move them to a new owner or plan.
     */
    public function detachMembers(User $owner): void
    {
        User::where('parent_business_id', $owner->id)->update([
            'parent_business_id' => null,
            'business_role' => null,
            'subscription_plan' => 'free',
            'subscription_active' => false,
        ]);

        NfcCard::where('business_account_id', $owner->id)->update(['business_account_id' => null]);
    }
}
