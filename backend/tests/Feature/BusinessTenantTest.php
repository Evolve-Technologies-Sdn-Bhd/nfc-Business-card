<?php

use App\Mail\PasswordResetMail;
use App\Models\LandingPage;
use App\Models\NfcCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

/**
 * Business tenant end to end: the first Business account (owner / Business
 * admin) governs its members — seats, roles, status, shared + locked company
 * fields — and members' access follows the owner's plan.
 */

function tenantCard(User $owner, User $holder): NfcCard
{
    return NfcCard::create([
        'user_id' => $holder->id,
        'business_account_id' => $owner->id,
        'card_owner' => $holder->full_name,
        'billing_address' => '1 Test Street',
        'contact_number' => '+60123456789',
        'status' => 'active',
        'subscription_plan' => 'business',
        'purchase_amount' => 0,
        'payment_method' => 'business_plan',
        'purchase_date' => now(),
    ]);
}

function tenantProfile(NfcCard $card, array $attrs = []): LandingPage
{
    return LandingPage::create(array_merge([
        'nfc_card_id' => $card->id,
        'name' => 'Member Name',
        'is_active' => true,
    ], $attrs));
}

// ─── Adding / removing members ─────────────────────────────────────────

it('lets the owner add a member, who inherits the company and gets a set-password e-mail', function () {
    Mail::fake();
    $owner = User::factory()->businessOwner(3)->create(['company' => 'Acme Sdn Bhd']);
    Sanctum::actingAs($owner);

    $this->postJson('/api/business/employees', [
        'first_name' => 'Aina',
        'last_name' => 'Rahman',
        'email' => 'Aina@Example.com',
        'job_title' => 'Sales',
    ])->assertCreated()->assertJsonPath('data.employee.business_role', 'member');

    $member = User::where('email', 'aina@example.com')->firstOrFail();
    expect($member->parent_business_id)->toBe($owner->id)
        ->and($member->company)->toBe('Acme Sdn Bhd')
        ->and($member->hasBusinessSubscription())->toBeTrue();
    Mail::assertSent(PasswordResetMail::class);
});

it('enforces the seat limit (owner counts as one seat)', function () {
    Mail::fake();
    $owner = User::factory()->businessOwner(2)->create();
    User::factory()->businessEmployee($owner)->create();
    Sanctum::actingAs($owner);

    $this->postJson('/api/business/employees', [
        'first_name' => 'No', 'last_name' => 'Seat', 'email' => 'noseat@example.com',
    ])->assertForbidden()->assertJsonPath('code', 'SEAT_LIMIT_REACHED');
});

it('does not let a plain member add members or change company settings', function () {
    $owner = User::factory()->businessOwner()->create();
    $member = User::factory()->businessEmployee($owner)->create();
    Sanctum::actingAs($member);

    $this->postJson('/api/business/employees', [
        'first_name' => 'X', 'last_name' => 'Y', 'email' => 'x@example.com',
    ])->assertForbidden();
    $this->putJson('/api/business/settings', ['locked_fields' => []])->assertForbidden();
});

it('stops a delegated admin from creating admins or acting on another admin', function () {
    $owner = User::factory()->businessOwner()->create();
    $admin = User::factory()->businessEmployee($owner, User::BUSINESS_ROLE_ADMIN)->create();
    $otherAdmin = User::factory()->businessEmployee($owner, User::BUSINESS_ROLE_ADMIN)->create();
    $member = User::factory()->businessEmployee($owner)->create();
    Sanctum::actingAs($admin);

    $this->postJson('/api/business/employees', [
        'first_name' => 'X', 'last_name' => 'Y', 'email' => 'x@example.com', 'role' => 'admin',
    ])->assertForbidden();
    $this->postJson("/api/business/employees/{$otherAdmin->id}/toggle-status", ['subscription_active' => false])
        ->assertForbidden();
    $this->deleteJson("/api/business/employees/{$admin->id}")->assertForbidden();

    // ...but can still manage plain members.
    $this->postJson("/api/business/employees/{$member->id}/toggle-status", ['subscription_active' => false])
        ->assertOk();
});

it('returns company cards to the owner and takes the profile offline when a member is removed', function () {
    $owner = User::factory()->businessOwner()->create();
    $member = User::factory()->businessEmployee($owner)->create();
    $card = tenantCard($owner, $member);
    $page = tenantProfile($card);
    Sanctum::actingAs($owner);

    $this->deleteJson("/api/business/employees/{$member->id}")
        ->assertOk()->assertJsonPath('data.returned_cards', 1);

    expect(User::find($member->id))->toBeNull()
        ->and($card->fresh()->user_id)->toBe($owner->id)
        ->and($page->fresh()->is_active)->toBeFalse();
});

// ─── Seat status ───────────────────────────────────────────────────────

it('really blocks a deactivated member: login refused and sessions revoked', function () {
    $owner = User::factory()->businessOwner()->create();
    $member = User::factory()->businessEmployee($owner)->create(['password' => Hash::make('secret-pass')]);
    $member->createToken('old');
    Sanctum::actingAs($owner);

    $this->postJson("/api/business/employees/{$member->id}/toggle-status", ['subscription_active' => false])->assertOk();

    expect($member->tokens()->count())->toBe(0);
    $this->postJson('/api/login', ['email' => $member->email, 'password' => 'secret-pass'])
        ->assertForbidden()->assertJsonPath('code', 'BUSINESS_SEAT_DEACTIVATED');
});

// ─── Access follows the owner ──────────────────────────────────────────

it('removes member Business access when the owner is downgraded', function () {
    $owner = User::factory()->businessOwner()->create();
    $member = User::factory()->businessEmployee($owner)->create();
    $card = tenantCard($owner, $member);
    tenantProfile($card);

    expect($member->hasBusinessSubscription())->toBeTrue();

    $owner->forceFill(['subscription_plan' => 'premium'])->save();

    $member = $member->fresh();
    expect($member->hasBusinessSubscription())->toBeFalse()
        ->and($member->canAccessPaidProfileBuilder('business'))->toBeFalse();

    Sanctum::actingAs($member);
    $this->putJson("/api/nfc-cards/{$card->id}/landing-page", ['title' => 'Engineer'])
        ->assertForbidden()->assertJsonPath('code', 'BUSINESS_ACCESS_INACTIVE');
});

it('keeps member accounts when the owner account is deleted', function () {
    $owner = User::factory()->businessOwner()->create();
    $member = User::factory()->businessEmployee($owner)->create();

    $owner->delete();

    $member = User::find($member->id);
    expect($member)->not->toBeNull()
        ->and($member->parent_business_id)->toBeNull()
        ->and($member->hasBusinessSubscription())->toBeFalse();
});

// ─── Shared + locked company fields ────────────────────────────────────

it('applies locked company fields on the server and pushes them to member profiles', function () {
    $owner = User::factory()->businessOwner()->create();
    $member = User::factory()->businessEmployee($owner)->create();
    $card = tenantCard($owner, $member);
    $page = tenantProfile($card, ['company_name' => 'Old Name', 'accent_color' => '#000000']);

    Sanctum::actingAs($owner);
    $this->putJson('/api/business/settings', [
        'shared_fields' => ['company_name' => 'Acme Sdn Bhd', 'accent_color' => '#B08D57'],
        'locked_fields' => ['company_name', 'accent_color', 'company_logo'],
    ])->assertOk()->assertJsonPath('data.synced_profiles', 1);

    expect($page->fresh()->company_name)->toBe('Acme Sdn Bhd')
        ->and($owner->fresh()->company)->toBe('Acme Sdn Bhd');

    // A member trying to overwrite locked fields keeps the company values,
    // while their own fields save normally.
    Sanctum::actingAs($member->fresh());
    $this->putJson("/api/nfc-cards/{$card->id}/landing-page", [
        'company_name' => 'My Own Co',
        'accent_color' => '#FF0000',
        'company_logo' => 'https://evil.example/logo.png',
        'title' => 'Engineer',
    ])->assertOk();

    $page = $page->fresh();
    expect($page->company_name)->toBe('Acme Sdn Bhd')
        ->and($page->accent_color)->toBe('#B08D57')
        ->and($page->company_logo)->toBeNull()
        ->and($page->title)->toBe('Engineer');

    $this->getJson('/api/business/context')
        ->assertOk()
        ->assertJsonPath('data.role', 'member')
        ->assertJsonPath('data.has_access', true)
        ->assertJsonPath('data.my_locked_fields', ['company_name', 'accent_color', 'company_logo']);
});

it('does not apply company locks to the owner', function () {
    $owner = User::factory()->businessOwner()->create();
    $card = tenantCard($owner, $owner);
    $page = tenantProfile($card);
    Sanctum::actingAs($owner);

    $this->putJson('/api/business/settings', [
        'shared_fields' => ['company_name' => 'Acme'],
        'locked_fields' => ['company_name'],
    ])->assertOk();

    $this->putJson("/api/nfc-cards/{$card->id}/landing-page", ['company_name' => 'Acme Holdings'])->assertOk();
    expect($page->fresh()->company_name)->toBe('Acme Holdings');
});

it('rejects locking fields that are personal or unknown', function () {
    $owner = User::factory()->businessOwner()->create();
    Sanctum::actingAs($owner);

    $this->putJson('/api/business/settings', ['locked_fields' => ['name']])->assertUnprocessable();
    $this->putJson('/api/business/settings', ['shared_fields' => ['bio' => 'x']])->assertUnprocessable();
    $this->putJson('/api/business/settings', ['locked_fields' => ['card_template']])->assertUnprocessable();
});

it('blocks a member from uploading a locked company logo', function () {
    $owner = User::factory()->businessOwner()->create();
    $member = User::factory()->businessEmployee($owner)->create();
    \App\Models\BusinessSetting::create(['owner_id' => $owner->id, 'locked_fields' => ['company_logo']]);
    Sanctum::actingAs($member);

    $this->postJson('/api/upload/company-logo', [])
        ->assertForbidden()->assertJsonPath('code', 'FIELD_LOCKED_BY_COMPANY');
});
