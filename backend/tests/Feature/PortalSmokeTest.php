<?php

use App\Models\NfcCard;
use App\Models\User;
use App\Services\DigitalCardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

/**
 * System smoke test: every user-facing GET endpoint, for every kind of user,
 * must answer without a server error. A 4xx (no access / not found) is a
 * valid answer; a 5xx is a bug.
 */

const PORTAL_GET_ROUTES = [
    '/api/me',
    '/api/user/profile',
    '/api/user/linked-accounts',
    '/api/user/sessions',
    '/api/settings',
    '/api/nfc-cards',
    '/api/nfc/tags',
    '/api/links',
    '/api/notifications',
    '/api/notifications/unread-count',
    '/api/invoices',
    '/api/invoices/statistics',
    '/api/payment/transactions',
    '/api/subscription/status',
    '/api/subscriptions/active',
    '/api/subscriptions/history',
    '/api/onboarding/draft',
    '/api/onboarding/order-status',
    '/api/profile-builder-fields',
    '/api/profile-builder-init',
    '/api/profile-builder-sections',
    '/api/profile-design-options',
    '/api/analytics/overview',
    '/api/analytics/profile',
    '/api/analytics/user/overview',
    '/api/analytics/user/overview/export',
    '/api/analytics/business/overview',
    '/api/analytics/business/overview/export',
    '/api/business/activity-logs',
    '/api/business/activity-logs/action-types',
    '/api/business/activity-logs/statistics',
    '/api/business/activity-logs/export',
    '/api/business/can-order-card',
    '/api/business/card-quota',
    '/api/business/employees',
    '/api/business/team-members',
    // public
    '/api/card-templates',
    '/api/plan-prices/public',
    '/api/payment/config',
    '/api/payment/rails',
    '/api/legal/documents',
    '/api/system-settings/brand/public',
];

function smokeUser(string $kind): User
{
    $physicalCard = function (User $u, string $plan, ?User $business = null) {
        NfcCard::create([
            'user_id' => $u->id,
            'business_account_id' => $business?->id,
            'card_owner' => $u->first_name ?? 'Owner',
            'billing_address' => '1 Test Street',
            'contact_number' => '+60123456789',
            'status' => 'active',
            'subscription_plan' => $plan,
            'purchase_amount' => 0,
            'payment_method' => 'test',
            'purchase_date' => now(),
        ]);
    };

    switch ($kind) {
        case 'free':
            $u = User::factory()->create();
            $u->forceFill(['subscription_plan' => 'free', 'subscription_active' => true,
                'subscription_end_date' => now()->addDays(14)])->save();
            app(DigitalCardService::class)->ensureFor($u->fresh());
            return $u->fresh();
        case 'free-deactivated':
            $u = User::factory()->create();
            $u->forceFill(['subscription_plan' => 'free', 'subscription_active' => false,
                'deactivated_at' => now()])->save();
            return $u->fresh();
        case 'basic':
        case 'premium':
            $u = User::factory()->create();
            $u->forceFill(['subscription_plan' => $kind, 'subscription_active' => true])->save();
            $physicalCard($u, $kind);
            return $u->fresh();
        case 'business-owner':
            $u = User::factory()->businessOwner()->create();
            $physicalCard($u, 'business', $u);
            return $u->fresh();
        case 'business-member':
            $owner = User::factory()->businessOwner()->create();
            $u = User::factory()->businessEmployee($owner)->create();
            $physicalCard($u, 'business', $owner);
            return $u->fresh();
    }

    throw new InvalidArgumentException($kind);
}

dataset('portal users', ['free', 'free-deactivated', 'basic', 'premium', 'business-owner', 'business-member']);

it('serves every portal GET endpoint without a server error', function (string $kind) {
    $user = smokeUser($kind);
    Sanctum::actingAs($user);

    $failures = [];
    foreach (PORTAL_GET_ROUTES as $uri) {
        $res = $this->getJson($uri);
        if ($res->baseResponse->getStatusCode() >= 500) {
            $failures[] = "{$uri} -> " . $res->baseResponse->getStatusCode() . " " . substr((string) $res->baseResponse->getContent(), 0, 300);
        }
    }

    // Per-card endpoints for the user's own card(s)
    foreach (NfcCard::where('user_id', $user->id)->get() as $card) {
        foreach ([
            "/api/nfc-cards/{$card->id}",
            "/api/nfc-cards/{$card->id}/landing-page",
            "/api/nfc-cards/{$card->id}/analytics",
            "/api/analytics/nfc-card/{$card->id}",
        ] as $uri) {
            $res = $this->getJson($uri);
            if ($res->baseResponse->getStatusCode() >= 500) {
                $failures[] = "{$uri} -> " . $res->baseResponse->getStatusCode() . " " . substr((string) $res->baseResponse->getContent(), 0, 300);
            }
        }
    }

    expect($failures)->toBe([]);
})->with('portal users');

it('lets every user load and save their settings', function (string $kind) {
    $user = smokeUser($kind);
    Sanctum::actingAs($user);

    $this->getJson('/api/settings')->assertOk()
        ->assertJsonStructure(['notification_settings' => ['profile_views', 'nfc_taps', 'weekly_reports', 'product_updates'], 'support_email']);

    $this->putJson('/api/settings/notifications', ['weekly_reports' => false])
        ->assertOk()->assertJsonPath('notification_settings.weekly_reports', false);

    $this->putJson('/api/settings/personal-info', [
        'first_name' => 'Smoke',
        'last_name' => 'Test',
        'email' => $user->email,
        'phone' => '+60111111111',
    ])->assertOk();

    $this->postJson('/api/settings/export-data')->assertOk()->assertJsonPath('success', true);
})->with('portal users');

// ─── Settings fixes ─────────────────────────────────────────────────────

it('keeps the session when the current password is wrong (422, not 401)', function () {
    $user = smokeUser('basic');
    Sanctum::actingAs($user);

    $this->postJson('/api/user/change-password', [
        'current_password' => 'definitely-wrong',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertStatus(422)->assertJsonPath('success', false);

    $this->postJson('/api/user/verify-password', ['password' => 'definitely-wrong'])
        ->assertStatus(422);
});

it('lets a Google-only account set a first password without a current one', function () {
    $user = smokeUser('free');
    $user->forceFill(['password' => null, 'provider' => 'google'])->save();
    Sanctum::actingAs($user->fresh());

    $this->getJson('/api/me')->assertJsonPath('user.has_local_password', false);

    $this->postJson('/api/user/change-password', [
        'password' => 'first-password-1',
        'password_confirmation' => 'first-password-1',
    ])->assertOk();

    expect($user->fresh()->hasLocalPassword())->toBeTrue();
});

it('requires re-verification when the account e-mail changes, without touching profiles', function () {
    \Illuminate\Support\Facades\Notification::fake();
    $user = smokeUser('free');
    $card = NfcCard::where('user_id', $user->id)->first();
    \App\Models\LandingPage::create(['nfc_card_id' => $card->id, 'name' => 'Public Name', 'email' => 'public@example.com']);
    Sanctum::actingAs($user);

    $this->putJson('/api/settings/personal-info', [
        'first_name' => 'New', 'last_name' => 'Name', 'email' => 'changed@example.com',
    ])->assertOk()->assertJsonPath('email_verification_required', true);

    expect($user->fresh()->email_verified_at)->toBeNull()
        ->and($card->landingPage()->first()->email)->toBe('public@example.com');
});

it('honours the Profile activity notification preference', function () {
    $user = smokeUser('basic');
    $service = app(\App\Services\NotificationService::class);

    expect($service->create($user, 'link_milestone', ['link_title' => 'x', 'click_count' => 100]))->not->toBeNull();

    \App\Models\UserNotificationPreference::updateOrCreate(['user_id' => $user->id], ['profile_views' => false]);

    expect($service->create($user, 'link_milestone', ['link_title' => 'x', 'click_count' => 200]))->toBeNull()
        // account notifications are never suppressed
        ->and($service->create($user, 'password_changed', []))->not->toBeNull();
});
