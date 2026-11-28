<?php

use App\Models\LandingPage;
use App\Models\NfcCard;
use App\Models\ProfileDesignOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

/**
 * Team Members → "Company profile & locks", end to end: what the owner shares
 * reaches member profiles, locks hold on every save path, and the public
 * member page shows the company values.
 */

function lockCard(User $owner, User $holder): NfcCard
{
    return NfcCard::create([
        'user_id' => $holder->id,
        'business_account_id' => $owner->id,
        'card_owner' => 'Card',
        'billing_address' => '1 Test St',
        'contact_number' => '+60123456789',
        'status' => 'active',
        'subscription_plan' => 'business',
        'purchase_amount' => 0,
        'purchase_date' => now(),
    ]);
}

function lockTenant(): array
{
    $owner = User::factory()->businessOwner(5)->create();
    $member = User::factory()->businessEmployee($owner)->create();
    $card = lockCard($owner, $member);
    $page = LandingPage::create(['nfc_card_id' => $card->id, 'name' => 'Member', 'industry' => null, 'website' => 'https://mine.example']);

    return [$owner, $member, $card, $page];
}

it('fills members\' empty fields with shared (unlocked) company values without overwriting theirs', function () {
    [$owner, , , $page] = lockTenant();
    Sanctum::actingAs($owner);

    $this->putJson('/api/business/settings', [
        'shared_fields' => ['industry' => 'Technology', 'website' => 'https://acme.example'],
        'locked_fields' => [],
    ])->assertOk();

    $page = $page->fresh();
    expect($page->industry)->toBe('Technology')            // empty → company value
        ->and($page->website)->toBe('https://mine.example'); // member's own value kept
});

it('rejects a shared theme that does not exist', function () {
    [$owner] = lockTenant();
    Sanctum::actingAs($owner);

    $this->putJson('/api/business/settings', [
        'shared_fields' => ['theme' => 'zora-pr0'],
        'locked_fields' => ['theme'],
    ])->assertStatus(422);
});

it('rejects a colour that is not a hex colour', function () {
    [$owner] = lockTenant();
    Sanctum::actingAs($owner);

    $this->putJson('/api/business/settings', [
        'shared_fields' => ['accent_color' => 'blue-ish'],
    ])->assertStatus(422);
});

it('shows a locked company theme on the member\'s public page, even if the member picks another', function () {
    ProfileDesignOption::create([
        'type' => 'theme', 'option_id' => 'zora-pro', 'name' => 'Zora', 'is_active' => true,
        'available_plans' => ['premium', 'business'],
        'config' => ['backgroundColor' => '#0F2744', 'textColor' => '#E8EEF4', 'accentColor' => '#5B8A86', 'bgGradient' => 'linear-gradient(160deg, #0F2744 0%, #1F5177 100%)'],
    ]);
    [$owner, $member, $card] = lockTenant();

    Sanctum::actingAs($owner);
    $this->putJson('/api/business/settings', [
        'shared_fields' => ['theme' => 'zora-pro', 'background_color' => '#0F2744', 'text_color' => '#E8EEF4'],
        'locked_fields' => ['theme', 'background_color', 'text_color'],
    ])->assertOk();

    Sanctum::actingAs($member->fresh());
    $this->putJson("/api/nfc-cards/{$card->id}/landing-page", [
        'theme' => 'warm-sand', 'background_color' => '#FBF7F0', 'text_color' => '#3F3427',
    ])->assertOk()->assertJsonPath('locked_overrides', ['theme', 'background_color', 'text_color']);

    $this->getJson("/api/nfc-cards/{$card->id}/landing-page")
        ->assertJsonPath('design.theme', 'zora-pro')
        ->assertJsonPath('design.background_color', '#0F2744');
});

it('blocks a member from uploading a locked cover banner', function () {
    [$owner, $member, $card] = lockTenant();
    Sanctum::actingAs($owner);
    $this->putJson('/api/business/settings', ['locked_fields' => ['cover_banner']])->assertOk();

    Sanctum::actingAs($member->fresh());
    $this->postJson('/api/upload/cover-banner', [
        // create() instead of image(): this PHP build has no GD extension.
        'image' => \Illuminate\Http\UploadedFile::fake()->create('b.png', 20, 'image/png'),
        'nfc_card_id' => $card->id,
    ])->assertStatus(403)->assertJsonPath('code', 'FIELD_LOCKED_BY_COMPANY');
});

it('starts a new member profile with the company values', function () {
    $owner = User::factory()->businessOwner(5)->create();
    $member = User::factory()->businessEmployee($owner)->create();

    Sanctum::actingAs($owner);
    $this->putJson('/api/business/settings', [
        'shared_fields' => ['company_name' => 'Acme Sdn Bhd', 'industry' => 'Technology'],
        'locked_fields' => ['company_name'],
    ])->assertOk();

    // The member gets a card later; their profile is created on first save.
    $card = lockCard($owner, $member);
    Sanctum::actingAs($member->fresh());
    $this->putJson("/api/nfc-cards/{$card->id}/landing-page", ['title' => 'Engineer', 'company_name' => 'Mine'])
        ->assertOk();

    $page = LandingPage::where('nfc_card_id', $card->id)->first();
    expect($page->company_name)->toBe('Acme Sdn Bhd')   // locked: company value
        ->and($page->industry)->toBe('Technology')       // shared default filled
        ->and($page->title)->toBe('Engineer');
});

// ─── Company card template ──────────────────────────────────────────────

function lockOrder(array $extra = []): array
{
    return $extra + [
        'card_owner' => 'Team Card',
        'billing_address' => '1 Test St',
        'contact_number' => '+60123456789',
        'subscription_plan' => 'business',
        'purchase_amount' => 0,
    ];
}

it('uses the locked company template for every team card order', function () {
    $company = \App\Models\CardTemplate::create(['name' => 'Company', 'slug' => 'company-tpl']);
    $other = \App\Models\CardTemplate::create(['name' => 'Other', 'slug' => 'other-tpl']);
    $owner = User::factory()->businessOwner(5)->create();
    $member = User::factory()->businessEmployee($owner)->create();

    Sanctum::actingAs($owner);
    $this->putJson('/api/business/settings', [
        'card_template_id' => $company->id,
        'locked_fields' => ['card_template'],
    ])->assertOk();

    // The owner asks for another template and a custom design: both ignored.
    $this->postJson('/api/nfc-cards', lockOrder(['card_template_id' => $other->id, 'is_custom_design' => true]))
        ->assertCreated()
        ->assertJsonPath('company_template_applied', true)
        ->assertJsonPath('nfc_card.card_template_id', $company->id)
        ->assertJsonPath('nfc_card.is_custom_design', false);

    // A member's order follows the same rule.
    Sanctum::actingAs($member->fresh());
    $this->postJson('/api/nfc-cards', lockOrder(['card_template_id' => $other->id]))
        ->assertCreated()
        ->assertJsonPath('nfc_card.card_template_id', $company->id);
});

it('leaves the design choice free when the company template is not locked', function () {
    $company = \App\Models\CardTemplate::create(['name' => 'Company', 'slug' => 'company-tpl2']);
    $other = \App\Models\CardTemplate::create(['name' => 'Other', 'slug' => 'other-tpl2']);
    $owner = User::factory()->businessOwner(5)->create();

    Sanctum::actingAs($owner);
    $this->putJson('/api/business/settings', ['card_template_id' => $company->id, 'locked_fields' => []])->assertOk();

    $this->postJson('/api/nfc-cards', lockOrder(['card_template_id' => $other->id]))
        ->assertCreated()
        ->assertJsonPath('company_template_applied', false)
        ->assertJsonPath('nfc_card.card_template_id', $other->id);
});
