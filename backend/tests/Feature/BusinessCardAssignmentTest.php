<?php

use App\Models\NfcCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

/**
 * Build a Business owner plus one employee with the given delegated role.
 *
 * @return array{0: User, 1: User}
 */
function makeBusinessWithEmployee(string $role = User::BUSINESS_ROLE_MEMBER): array
{
    $owner = User::factory()->businessOwner()->create();
    $employee = User::factory()->businessEmployee($owner, $role)->create();

    return [$owner, $employee];
}

/**
 * Create a Business NFC card owned by $owner (or a given assignee).
 */
function makeBusinessCard(User $owner, ?User $assignee = null): NfcCard
{
    return NfcCard::create([
        'user_id' => $assignee?->id ?? $owner->id,
        'business_account_id' => $owner->id,
        'card_owner' => 'Business Card Owner',
        'billing_address' => '1 Test Street',
        'contact_number' => '+60123456789',
        'status' => 'active',
        'subscription_plan' => 'business',
        'purchase_amount' => 0,
        'payment_method' => 'business_plan',
        'purchase_date' => now(),
    ]);
}

// ─── H-4: bulk card assignment ──────────────────────────────────────────

it('lets the business owner assign cards to an employee', function () {
    [$owner, $employee] = makeBusinessWithEmployee();
    $card = makeBusinessCard($owner);

    Sanctum::actingAs($owner);

    $response = $this->postJson('/api/business/cards/bulk-assign', [
        'card_ids' => [$card->id],
        'employee_id' => $employee->id,
    ]);

    $response->assertOk()->assertJsonPath('success', true);
    expect($card->fresh()->user_id)->toBe($employee->id);
});

it('lets the owner return cards to themselves by passing a null employee', function () {
    [$owner, $employee] = makeBusinessWithEmployee();
    $card = makeBusinessCard($owner, $employee);

    Sanctum::actingAs($owner);

    $response = $this->postJson('/api/business/cards/bulk-assign', [
        'card_ids' => [$card->id],
        'employee_id' => null,
    ]);

    $response->assertOk();
    expect($card->fresh()->user_id)->toBe($owner->id);
});

it('rejects cards that belong to a different business account', function () {
    [$owner, $employee] = makeBusinessWithEmployee();
    $otherOwner = User::factory()->businessOwner()->create();
    $foreignCard = makeBusinessCard($otherOwner);

    Sanctum::actingAs($owner);

    $this->postJson('/api/business/cards/bulk-assign', [
        'card_ids' => [$foreignCard->id],
        'employee_id' => $employee->id,
    ])->assertStatus(404);
});

it('rejects an employee_id that does not belong to the tenant', function () {
    [$owner] = makeBusinessWithEmployee();
    $card = makeBusinessCard($owner);
    $outsider = User::factory()->businessOwner()->create();

    Sanctum::actingAs($owner);

    $this->postJson('/api/business/cards/bulk-assign', [
        'card_ids' => [$card->id],
        'employee_id' => $outsider->id,
    ])->assertStatus(404);
});

// ─── M-3: within-tenant roles ───────────────────────────────────────────

it('forbids a plain member from listing employees and from assigning cards', function () {
    [$owner, $member] = makeBusinessWithEmployee(User::BUSINESS_ROLE_MEMBER);
    $card = makeBusinessCard($owner);

    Sanctum::actingAs($member);

    $this->getJson('/api/business/employees')->assertStatus(403);
    $this->postJson('/api/business/cards/bulk-assign', [
        'card_ids' => [$card->id],
        'employee_id' => $member->id,
    ])->assertStatus(403);
});

it('lets a card_manager list employees and assign cards but not manage employees', function () {
    [$owner, $cardManager] = makeBusinessWithEmployee(User::BUSINESS_ROLE_CARD_MANAGER);
    $card = makeBusinessCard($owner);

    Sanctum::actingAs($cardManager);

    // Can read the assignee list and assign cards…
    $this->getJson('/api/business/employees')->assertOk()->assertJsonPath('success', true);
    $this->postJson('/api/business/cards/bulk-assign', [
        'card_ids' => [$card->id],
        'employee_id' => $cardManager->id,
    ])->assertOk();
    expect($card->fresh()->user_id)->toBe($cardManager->id);

    // …but cannot perform destructive employee management.
    $this->deleteJson("/api/business/employees/{$owner->id}")->assertStatus(403);
});

it('lets a delegated admin manage employees but not change roles', function () {
    [$owner, $admin] = makeBusinessWithEmployee(User::BUSINESS_ROLE_ADMIN);
    $target = User::factory()->businessEmployee($owner)->create();

    Sanctum::actingAs($admin);

    // Admin can read the employee list…
    $this->getJson('/api/business/employees')->assertOk();

    // …but role changes are owner-only.
    $this->postJson("/api/business/employees/{$target->id}/role", [
        'role' => User::BUSINESS_ROLE_CARD_MANAGER,
    ])->assertStatus(403);
});

it('lets the owner change an employee role', function () {
    [$owner, $employee] = makeBusinessWithEmployee(User::BUSINESS_ROLE_MEMBER);

    Sanctum::actingAs($owner);

    $response = $this->postJson("/api/business/employees/{$employee->id}/role", [
        'role' => User::BUSINESS_ROLE_CARD_MANAGER,
    ]);

    $response->assertOk()->assertJsonPath('data.business_role', User::BUSINESS_ROLE_CARD_MANAGER);
    expect($employee->fresh()->business_role)->toBe(User::BUSINESS_ROLE_CARD_MANAGER);
});

it('derives the owner role automatically and defaults employees to member', function () {
    $owner = User::factory()->businessOwner()->create();
    $employee = User::factory()->businessEmployee($owner)->create(['business_role' => null]);

    expect($owner->businessRole())->toBe(User::BUSINESS_ROLE_OWNER);
    expect($employee->businessRole())->toBe(User::BUSINESS_ROLE_MEMBER);
    expect($owner->canManageBusiness())->toBeTrue();
    expect($employee->canManageBusiness())->toBeFalse();
});
