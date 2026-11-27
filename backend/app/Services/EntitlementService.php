<?php

namespace App\Services;

use App\Models\User;

/**
 * Entitlement Service — the single source of truth for plan/feature access.
 *
 * Entitlement in NFCGo is granted by EITHER:
 *   1. an active subscription (users.subscription_active + a paid subscription_plan), OR
 *   2. a fulfilled physical card (an NFC card whose status proves payment was verified).
 *
 * Previously this rule was duplicated across User model helpers and the frontend
 * auth store. Centralising it here means every new entry point can ask one service
 * instead of re-implementing (and possibly forgetting) the rule — which is what
 * allowed paid features to be reachable without payment in the first place.
 */
class EntitlementService
{
    /**
     * Card statuses that prove payment was verified and therefore grant access.
     * Ordered by lifecycle position: payment_verified -> ... -> active.
     */
    public const CARD_ENTITLEMENT_STATUSES = [
        'payment_verified',
        'processing',
        'shipped',
        'delivered',
        'active',
    ];

    /**
     * Plans that carry paid entitlements.
     */
    public const PAID_PLANS = ['basic', 'premium', 'business'];

    /**
     * Does the user hold a card that grants entitlement for one of $plans?
     * When $plans is null any entitled card counts.
     */
    public function hasEligibleCard(User $user, ?array $plans = null): bool
    {
        // Fast path: if the caller has already eager-loaded the cards we can
        // resolve the answer in memory without an extra query.
        // Free-plan digital cards are never an entitlement: they exist only so a
        // Free account has somewhere to store its profile (DigitalCardService).
        if ($plans === null && $user->relationLoaded('nfcCards')) {
            return $user->getRelation('nfcCards')->contains(
                fn ($card) => !$card->is_digital
                    && in_array($card->status, self::CARD_ENTITLEMENT_STATUSES, true)
            );
        }

        $query = $user->nfcCards()->physical()->whereIn('status', self::CARD_ENTITLEMENT_STATUSES);

        if ($plans !== null) {
            $query->whereIn('subscription_plan', $plans);
        }

        return $query->exists();
    }

    /**
     * Does the user have an active subscription on one of $plans?
     * When $plans is null any paid plan counts.
     */
    public function hasActiveSubscription(User $user, ?array $plans = null): bool
    {
        if (!$user->subscription_active) {
            return false;
        }

        return in_array($user->subscription_plan, $plans ?? self::PAID_PLANS, true);
    }

    /**
     * Paid access in the broad sense: an active paid subscription OR an entitled card.
     */
    public function hasPaidAccess(User $user, ?array $plans = null): bool
    {
        if ($user->isBusinessEmployee()) {
            return $this->memberAccess($user, $plans);
        }

        return $this->hasActiveSubscription($user, $plans)
            || $this->hasEligibleCard($user, $plans);
    }

    /**
     * Business access: business subscription OR a business card that passed payment.
     */
    public function hasBusinessAccess(User $user): bool
    {
        if ($user->isBusinessEmployee()) {
            return $this->memberAccess($user, ['business']);
        }

        return $this->hasActiveSubscription($user, ['business'])
            || $this->hasEligibleCard($user, ['business']);
    }

    /**
     * May the user use the paid Profile Builder?
     * - $plan = null  : any paid entitlement OR any entitled card.
     * - $plan = basic : basic/premium/business entitlement.
     * - $plan = premium|business : that tier (or a card of that tier) only.
     */
    public function canAccessPaidProfileBuilder(User $user, ?string $plan = null): bool
    {
        $planMatch = $plan === null
            ? null
            : (in_array($plan, ['premium', 'business'], true) ? [$plan] : self::PAID_PLANS);

        if ($user->isBusinessEmployee()) {
            return $this->memberAccess($user, $planMatch);
        }

        if ($plan === null) {
            return (bool) $user->subscription_active || $this->hasEligibleCard($user);
        }

        return $this->hasActiveSubscription($user, $planMatch)
            || $this->hasEligibleCard($user, $planMatch);
    }

    /**
     * Business members do not own a plan: they hold a seat in the owner's
     * Business plan. Before this, a member kept full Business access from the
     * `subscription_active` flag set when the account was created (and from any
     * card assigned to them), even after the owner's plan lapsed or was
     * downgraded.
     */
    private function memberAccess(User $member, ?array $plans): bool
    {
        if ($plans !== null && !in_array('business', $plans, true)) {
            return false;
        }

        return app(BusinessTenantService::class)->memberHasAccess($member);
    }
}
