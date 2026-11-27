<?php

use App\Models\NfcCard;
use App\Models\User;
use App\Services\DigitalCardService;
use App\Services\EntitlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function makeActiveFreeUser(): User
{
    $user = User::factory()->create();
    $user->forceFill([
        'subscription_plan' => 'free',
        'subscription_active' => true,
        'subscription_start_date' => now(),
        'subscription_end_date' => now()->addDays(14),
    ])->save();

    return $user->fresh();
}

it('creates one digital card for an active free user, idempotently', function () {
    $user = makeActiveFreeUser();
    $service = app(DigitalCardService::class);

    $first = $service->ensureFor($user);
    $second = $service->ensureFor($user);

    expect($first)->not->toBeNull()
        ->and($first->is_digital)->toBeTrue()
        ->and($first->status)->toBe('active')
        ->and($first->subscription_plan)->toBe('free')
        ->and($second->id)->toBe($first->id)
        ->and(NfcCard::where('user_id', $user->id)->count())->toBe(1);
});

it('does not create a digital card for paid or inactive accounts', function () {
    $paid = User::factory()->create();
    $paid->forceFill(['subscription_plan' => 'basic', 'subscription_active' => true])->save();

    $inactiveFree = User::factory()->create();
    $inactiveFree->forceFill(['subscription_plan' => 'free', 'subscription_active' => false])->save();

    $service = app(DigitalCardService::class);

    expect($service->ensureFor($paid->fresh()))->toBeNull()
        ->and($service->ensureFor($inactiveFree->fresh()))->toBeNull();
});

it('never treats a digital card as an entitlement or physical card', function () {
    $user = makeActiveFreeUser();
    app(DigitalCardService::class)->ensureFor($user);

    $entitlements = app(EntitlementService::class);

    expect($entitlements->hasEligibleCard($user))->toBeFalse()
        ->and($entitlements->hasEligibleCard($user->load('nfcCards')))->toBeFalse()
        ->and($entitlements->canAccessPaidProfileBuilder($user, 'basic'))->toBeFalse()
        ->and($user->hasPhysicalCard())->toBeFalse()
        ->and(NfcCard::physical()->count())->toBe(0);
});

it('provisions the card when a free user lists cards and keeps upgrade_required', function () {
    $user = makeActiveFreeUser();
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/nfc-cards');

    $response->assertOk()
        ->assertJsonPath('upgrade_required', true)
        ->assertJsonPath('nfc_cards.0.is_digital', true);
});

it('lets a free user save and publicly view their digital profile', function () {
    $user = makeActiveFreeUser();
    $card = app(DigitalCardService::class)->ensureFor($user);
    Sanctum::actingAs($user);

    $this->putJson("/api/nfc-cards/{$card->id}/landing-page", ['name' => 'Jane Free'])
        ->assertOk();

    $this->getJson("/api/nfc-cards/{$card->id}/landing-page")
        ->assertOk()
        ->assertJsonPath('landing_page.name', 'Jane Free');
});

it('takes the digital profile offline when the free window ends', function () {
    $user = makeActiveFreeUser();
    $card = app(DigitalCardService::class)->ensureFor($user);

    $user->forceFill(['subscription_end_date' => now()->subDays(30)])->save();

    $this->artisan('free-plans:deactivate-expired')->assertSuccessful();

    expect($user->fresh()->subscription_active)->toBeFalse()
        ->and($card->fresh()->status)->toBe('inactive');

    $this->getJson("/api/nfc-cards/{$card->id}/landing-page")->assertNotFound();

    Sanctum::actingAs($user->fresh());
    $this->putJson("/api/nfc-cards/{$card->id}/landing-page", ['name' => 'X'])
        ->assertForbidden()
        ->assertJsonPath('code', 'FREE_WINDOW_ENDED');
});

it('hides digital cards from the admin card list', function () {
    $user = makeActiveFreeUser();
    app(DigitalCardService::class)->ensureFor($user);

    expect(NfcCard::count())->toBe(1)
        ->and(NfcCard::physical()->count())->toBe(0);
});
