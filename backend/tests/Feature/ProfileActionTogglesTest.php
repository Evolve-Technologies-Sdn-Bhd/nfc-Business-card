<?php

use App\Models\LandingPage;
use App\Models\NfcCard;
use App\Models\ProfileDesignOption;
use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

/**
 * Public profile actions (Save Contact, Message, QR Code, Share, Book) and
 * link options must follow the owner's builder switches and the admin plan
 * rules — not just show by default.
 */

function seedFeatureToggles(): void
{
    foreach ([
        'contact_form' => ['premium', 'business'],
        'vcard_download' => ['basic', 'premium', 'business'],
        'qr_code' => ['basic', 'premium', 'business'],
        'booking_integration' => ['business'],
        'click_tracking' => ['premium', 'business'],
    ] as $id => $plans) {
        ProfileDesignOption::create([
            'type' => 'feature_toggle',
            'option_id' => $id,
            'name' => $id,
            'is_active' => true,
            'available_plans' => $plans,
        ]);
    }
}

function profileFor(string $plan, array $features = []): array
{
    $user = User::factory()->create();
    $user->forceFill(['subscription_plan' => $plan, 'subscription_active' => true])->save();
    $card = NfcCard::create([
        'user_id' => $user->id,
        'card_owner' => 'Owner',
        'billing_address' => '1 Test St',
        'contact_number' => '+60123456789',
        'status' => 'active',
        'subscription_plan' => $plan,
        'purchase_amount' => 0,
        'purchase_date' => now(),
    ]);
    $page = LandingPage::create(['nfc_card_id' => $card->id, 'name' => 'Owner', 'features' => $features]);

    return [$user, $card, $page];
}

beforeEach(fn () => seedFeatureToggles());

it('hides Save Contact when the Profile-tab vCard toggle is off', function () {
    // Exactly the reported case: vcardEnabled=false, but the old
    // vcard_download flag still true.
    [, $card] = profileFor('premium', ['vcard_download' => true, 'vcardEnabled' => false]);

    $this->getJson("/api/nfc-cards/{$card->id}/landing-page")
        ->assertOk()
        ->assertJsonPath('effective_features.vcard_download', false)
        ->assertJsonPath('effective_features.qr_code', true)
        ->assertJsonPath('effective_features.contact_form', true);
});

it('hides actions the plan does not include, even if never switched off', function () {
    [, $card] = profileFor('basic');

    $this->getJson("/api/nfc-cards/{$card->id}/landing-page")
        ->assertOk()
        ->assertJsonPath('effective_features.vcard_download', true)
        ->assertJsonPath('effective_features.contact_form', false)      // premium+
        ->assertJsonPath('effective_features.booking_integration', false) // business
        ->assertJsonPath('effective_features.click_tracking', false)    // premium+
        ->assertJsonPath('effective_features.social_sharing', true);    // no admin toggle
});

it('honours an admin toggle switched off for everyone', function () {
    ProfileDesignOption::where('option_id', 'qr_code')->update(['is_active' => false]);
    [, $card] = profileFor('business');

    $this->getJson("/api/nfc-cards/{$card->id}/landing-page")
        ->assertJsonPath('effective_features.qr_code', false);
});

it('resolves link options for the public page', function () {
    [, $card] = profileFor('premium', ['openNewTab' => false, 'clickTracking' => false]);

    $this->getJson("/api/nfc-cards/{$card->id}/landing-page")
        ->assertJsonPath('effective_features.open_new_tab', false)
        ->assertJsonPath('effective_features.click_tracking', false);
});

it('delivers a contact message only when the Message action is on', function () {
    Mail::fake();

    [$owner, $card] = profileFor('premium', ['contact_form_email' => 'leads@example.com']);
    $this->postJson("/api/nfc-cards/{$card->id}/contact", [
        'name' => 'Visitor', 'email' => 'visitor@example.com', 'message' => 'Hello!',
    ])->assertOk()->assertJsonPath('success', true);

    expect($owner->notifications()->where('type', 'contact_request')->count())->toBe(1);

    [, $offCard] = profileFor('premium', ['contactFormEnabled' => false]);
    $this->postJson("/api/nfc-cards/{$offCard->id}/contact", [
        'name' => 'Visitor', 'email' => 'visitor@example.com', 'message' => 'Hello!',
    ])->assertForbidden();

    [, $basicCard] = profileFor('basic');
    $this->postJson("/api/nfc-cards/{$basicCard->id}/contact", [
        'name' => 'Visitor', 'email' => 'visitor@example.com', 'message' => 'Hello!',
    ])->assertForbidden();
});

it('counts public link clicks only when Click Analytics is on', function () {
    [, , $page] = profileFor('premium');
    $link = SocialLink::create(['landing_page_id' => $page->id, 'title' => 'Site', 'url' => 'https://example.com', 'platform' => 'website', 'is_active' => true]);

    $this->postJson("/api/links/{$link->id}/click")->assertOk()->assertJsonPath('tracked', true);
    expect($link->fresh()->click_count)->toBe(1);

    $page->update(['features' => ['clickTracking' => false]]);
    $this->postJson("/api/links/{$link->id}/click")->assertOk()->assertJsonPath('tracked', false);
    expect($link->fresh()->click_count)->toBe(1);

    [, , $basicPage] = profileFor('basic');
    $basicLink = SocialLink::create(['landing_page_id' => $basicPage->id, 'title' => 'Site', 'url' => 'https://example.com', 'platform' => 'website', 'is_active' => true]);
    $this->postJson("/api/links/{$basicLink->id}/click")->assertJsonPath('tracked', false);
});
