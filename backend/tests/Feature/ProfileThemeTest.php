<?php

use App\Models\LandingPage;
use App\Models\NfcCard;
use App\Models\ProfileDesignOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * The public profile renders the owner's theme (it used to force "Ink &
 * Brass" on everyone), limited to the themes the card's plan includes.
 */

beforeEach(function () {
    foreach ([
        'warm-sand' => [['basic', 'premium', 'business'], '#FBF7F0', '#3F3427', '#B45309', 'linear-gradient(135deg, #FBF7F0 0%, #E9D9BE 100%)'],
        'zora-pro' => [['premium', 'business'], '#0F2744', '#E8EEF4', '#5B8A86', 'linear-gradient(160deg, #0F2744 0%, #1F5177 100%)'],
    ] as $id => [$plans, $bg, $text, $accent, $gradient]) {
        ProfileDesignOption::create([
            'type' => 'theme',
            'option_id' => $id,
            'name' => $id,
            'is_active' => true,
            'available_plans' => $plans,
            'config' => ['backgroundColor' => $bg, 'textColor' => $text, 'accentColor' => $accent, 'bgGradient' => $gradient],
        ]);
    }
});

function themedProfile(string $plan, array $page): NfcCard
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
    LandingPage::create(['nfc_card_id' => $card->id, 'name' => 'Owner'] + $page);

    return $card;
}

it('renders the theme the owner chose', function () {
    $card = themedProfile('premium', ['theme' => 'zora-pro', 'background_color' => '#0F2744', 'text_color' => '#E8EEF4', 'accent_color' => '#5B8A86']);

    $this->getJson("/api/nfc-cards/{$card->id}/landing-page")
        ->assertOk()
        ->assertJsonPath('design.theme', 'zora-pro')
        ->assertJsonPath('design.locked', false)
        ->assertJsonPath('design.background_color', '#0F2744')
        ->assertJsonPath('design.text_color', '#E8EEF4')
        // gradient restored from the theme while the background is unchanged
        ->assertJsonPath('design.bg_gradient', 'linear-gradient(160deg, #0F2744 0%, #1F5177 100%)');
});

it('uses a custom background colour instead of the theme gradient', function () {
    $card = themedProfile('basic', ['theme' => 'warm-sand', 'background_color' => '#123456', 'text_color' => '#FFFFFF']);

    $this->getJson("/api/nfc-cards/{$card->id}/landing-page")
        ->assertJsonPath('design.background_color', '#123456')
        ->assertJsonPath('design.bg_gradient', null);
});

it('falls back to the system palette for a theme the plan does not include', function () {
    $card = themedProfile('basic', ['theme' => 'zora-pro', 'background_color' => '#0F2744']);

    $this->getJson("/api/nfc-cards/{$card->id}/landing-page")
        ->assertJsonPath('design.locked', true)
        ->assertJsonPath('design.theme', 'ink-brass')
        ->assertJsonPath('design.background_color', '#06090d');
});

it('gives Free cards the themes offered to Free or Basic', function () {
    $sand = themedProfile('free', ['theme' => 'warm-sand', 'background_color' => '#FBF7F0']);
    $this->getJson("/api/nfc-cards/{$sand->id}/landing-page")
        ->assertJsonPath('design.locked', false)
        ->assertJsonPath('design.theme', 'warm-sand');

    $zora = themedProfile('free', ['theme' => 'zora-pro', 'background_color' => '#0F2744']);
    $this->getJson("/api/nfc-cards/{$zora->id}/landing-page")
        ->assertJsonPath('design.locked', true);
});

it('defaults to the system palette when nothing was chosen', function () {
    $card = themedProfile('premium', []);

    $this->getJson("/api/nfc-cards/{$card->id}/landing-page")
        ->assertJsonPath('design.theme', 'ink-brass')
        ->assertJsonPath('design.locked', false);
});

it('honours Minimal when the owner actually picked it', function () {
    // The options migration already seeds "minimal" (all plans).
    ProfileDesignOption::updateOrCreate(
        ['type' => 'theme', 'option_id' => 'minimal'],
        ['name' => 'Minimal', 'is_active' => true, 'available_plans' => null, 'config' => ['backgroundColor' => '#FFFFFF', 'textColor' => '#111827']]
    );
    $card = themedProfile('premium', ['theme' => 'minimal', 'background_color' => '#FFFFFF', 'text_color' => '#111827']);

    $this->getJson("/api/nfc-cards/{$card->id}/landing-page")
        ->assertJsonPath('design.theme', 'minimal')
        ->assertJsonPath('design.background_color', '#FFFFFF')
        ->assertJsonPath('design.bg_gradient', null);
});
