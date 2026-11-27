<?php

namespace App\Services;

use App\Models\NfcCard;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Free-plan digital card.
 *
 * Profiles are stored per card (landing_pages.nfc_card_id), so a Free account
 * with no card could design a profile but never save, preview or share it. The
 * Free plan therefore gets ONE digital card: an nfc_cards row with is_digital =
 * true and nothing to print or ship. It gives the user a saved profile, a public
 * link and a QR code.
 *
 * A digital card never grants paid entitlement (see EntitlementService) and is
 * kept out of orders, fulfilment and admin card statistics (NfcCard::physical()).
 * When the free window ends, DeactivateExpiredFreePlans sets it to inactive.
 */
class DigitalCardService
{
    /**
     * Return the user's digital card, creating it when the user is on an active
     * Free plan and has none yet. Returns null when the user is not eligible.
     * Idempotent: safe to call on every builder / card-list load.
     */
    public function ensureFor(User $user): ?NfcCard
    {
        $existing = $user->nfcCards()
            ->digital()
            ->whereNotIn('status', ['cancelled'])
            ->oldest()
            ->first();

        if ($existing) {
            // Re-open a card withdrawn at the end of an earlier free window if
            // the account is an active Free plan again (e.g. reactivated).
            if ($existing->status !== 'active' && $this->isEligible($user)) {
                $existing->update(['status' => 'active']);
            }

            return $existing;
        }

        if (!$this->isEligible($user)) {
            return null;
        }

        $card = NfcCard::create([
            'user_id' => $user->id,
            'nfc_card_id' => 'DIGI-' . strtoupper(\Illuminate\Support\Str::random(10)),
            'card_owner' => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->name ?? $user->email),
            // Required columns that only mean something for a shipped card.
            'billing_address' => '',
            'contact_number' => (string) ($user->phone ?? ''),
            'subscription_plan' => 'free',
            'is_digital' => true,
            'status' => 'active',
            'purchase_date' => now(),
            'purchase_amount' => 0,
            'payment_method' => 'none',
            'notes' => 'Free plan digital card (no physical card).',
        ]);

        Log::info('Free plan digital card created', [
            'user_id' => $user->id,
            'nfc_card_id' => $card->nfc_card_id,
        ]);

        return $card;
    }

    /**
     * Only an active Free account gets a digital card. Paid plans get physical
     * cards through checkout/fulfilment instead.
     */
    public function isEligible(User $user): bool
    {
        return $user->subscription_plan === 'free' && (bool) $user->subscription_active;
    }

    /**
     * Withdraw the digital card when the free window ends.
     */
    public function deactivateFor(User $user): int
    {
        return $user->nfcCards()
            ->digital()
            ->where('status', 'active')
            ->update(['status' => 'inactive']);
    }
}
