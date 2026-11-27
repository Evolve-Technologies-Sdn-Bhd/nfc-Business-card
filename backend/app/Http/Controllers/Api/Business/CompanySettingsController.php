<?php

namespace App\Http\Controllers\Api\Business;

use App\Http\Controllers\Controller;
use App\Services\BusinessTenantService;
use Illuminate\Http\Request;

/**
 * Company-level settings of a Business tenant: the data the owner shares with
 * members (company name, logo, branding, card template) and which of those
 * fields members may not change.
 */
class CompanySettingsController extends Controller
{
    public function __construct(private BusinessTenantService $tenant)
    {
    }

    /**
     * GET /business/context — what the portal needs to render a Business user:
     * their role, whether their seat works, and the company's shared/locked fields.
     * Any signed-in user may call it; a non-Business user gets `is_business: false`.
     */
    public function context(Request $request)
    {
        $user = $request->user();
        $owner = $this->tenant->ownerOf($user);

        if (!$owner) {
            return response()->json(['success' => true, 'data' => ['is_business' => false]]);
        }

        $settings = $this->tenant->settingsFor($owner);
        $isOwner = $owner->id === $user->id;

        return response()->json([
            'success' => true,
            'data' => [
                'is_business' => true,
                'role' => $user->businessRole(),
                'is_owner' => $isOwner,
                'can_manage' => $user->canManageBusiness(),
                'can_manage_cards' => $user->canManageBusinessCards(),
                // false when the owner's plan lapsed / was downgraded or this seat was switched off
                'has_access' => $isOwner
                    ? $owner->hasBusinessSubscription()
                    : $this->tenant->memberHasAccess($user),
                'company' => [
                    'owner_id' => $owner->id,
                    'owner_name' => $owner->full_name,
                    'name' => ($settings->shared_fields['company_name'] ?? null) ?: $owner->company,
                ],
                'shared_fields' => (object) ($settings->shared_fields ?? []),
                'locked_fields' => array_values($settings->locked_fields ?? []),
                // What applies to *this* user's own profile (empty for the owner).
                'my_locked_fields' => $this->tenant->lockedFieldsFor($user),
                'card_template_id' => $settings->card_template_id,
                'lockable_fields' => BusinessTenantService::LOCKABLE_FIELDS,
                'quota' => $owner->getQuotaInfo(false),
            ],
        ]);
    }

    /**
     * PUT /business/settings — owner or delegated admin (business.manager).
     *
     * Body: shared_fields {column: value}, locked_fields [column|'card_template'],
     *       card_template_id. Locked + shared values are pushed to every
     *       existing member profile right away.
     */
    public function update(Request $request)
    {
        $owner = $request->user()->resolvedBusinessAccount();

        if (!$owner || !$owner->isBusinessAccount()) {
            return response()->json([
                'success' => false,
                'message' => 'Your company does not have an active Business plan.',
            ], 403);
        }

        $lockable = BusinessTenantService::lockableKeys();
        $lockOptions = array_merge($lockable, [BusinessTenantService::CARD_TEMPLATE_LOCK]);

        $validated = $request->validate([
            'shared_fields' => 'sometimes|array',
            'shared_fields.*' => 'nullable|string|max:5000',
            'locked_fields' => 'sometimes|array',
            'locked_fields.*' => 'string|in:' . implode(',', $lockOptions),
            'card_template_id' => 'sometimes|nullable|integer|exists:card_templates,id',
        ]);

        $unknown = array_diff(array_keys($validated['shared_fields'] ?? []), $lockable);
        if ($unknown !== []) {
            return response()->json([
                'success' => false,
                'message' => 'These fields cannot be shared: ' . implode(', ', $unknown),
                'errors' => ['shared_fields' => ['Unknown field(s): ' . implode(', ', $unknown)]],
            ], 422);
        }

        $settings = $this->tenant->settingsFor($owner);

        if (array_key_exists('shared_fields', $validated)) {
            // Empty strings mean "not shared" rather than "force blank".
            $settings->shared_fields = array_filter(
                $validated['shared_fields'],
                fn ($v) => $v !== null && $v !== ''
            );
        }
        if (array_key_exists('locked_fields', $validated)) {
            $settings->locked_fields = array_values(array_unique($validated['locked_fields']));
        }
        if (array_key_exists('card_template_id', $validated)) {
            $settings->card_template_id = $validated['card_template_id'];
        }

        if (in_array(BusinessTenantService::CARD_TEMPLATE_LOCK, $settings->locked_fields ?? [], true)
            && !$settings->card_template_id) {
            return response()->json([
                'success' => false,
                'message' => 'Choose a card template before locking it.',
                'errors' => ['card_template_id' => ['Required when the card template is locked.']],
            ], 422);
        }

        $settings->save();

        // Keep the owner's account company name in step with the shared one.
        if (!empty($settings->shared_fields['company_name']) && $owner->company !== $settings->shared_fields['company_name']) {
            $owner->forceFill(['company' => $settings->shared_fields['company_name']])->save();
        }

        $synced = $this->tenant->syncMembers($owner);

        return response()->json([
            'success' => true,
            'message' => $synced > 0
                ? "Company settings saved and applied to {$synced} member profile(s)."
                : 'Company settings saved.',
            'data' => [
                'shared_fields' => (object) ($settings->shared_fields ?? []),
                'locked_fields' => $settings->locked_fields ?? [],
                'card_template_id' => $settings->card_template_id,
                'synced_profiles' => $synced,
            ],
        ]);
    }
}
