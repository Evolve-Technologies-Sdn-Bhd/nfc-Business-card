<?php

use App\Models\CardTemplate;
use App\Models\NfcCard;
use App\Models\User;
use App\Notifications\AdminBulkOrderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

/**
 * Business → Order cards page (NFCCardDesign/BusinessPlanNFCCard): the
 * recipient list it builds is posted to /business/employees/upload, which
 * creates the cards and tells NFCGo. The page used to post to endpoints that
 * do not exist, so no order was ever placed.
 */

function bulkRecipients(int $n = 2): array
{
    return collect(range(1, $n))->map(fn ($i) => [
        'name' => "Staff {$i}",
        'email' => "staff{$i}@acme.example",
        'position' => 'Executive',
        'contact_number' => '+60 12-000 000' . $i,
        'address' => 'Jalan Ampang, Kuala Lumpur',
        'delivery_address' => 'Jalan Ampang, Kuala Lumpur',
    ])->all();
}

it('places a team card order from the recipient list and notifies the super admin', function () {
    Notification::fake();
    $admin = User::factory()->create(['is_admin' => true, 'admin_role' => 'super_admin']);
    $template = CardTemplate::create(['name' => 'Corporate', 'slug' => 'corporate-bulk']);
    $owner = User::factory()->businessOwner(5)->create();
    Sanctum::actingAs($owner);

    $this->postJson('/api/business/employees/upload', [
        'employees' => json_encode(bulkRecipients(2)),
        'design_method' => 'template',
        'selected_template' => (string) $template->id,
        'company_name' => 'Acme Sdn Bhd',
    ])->assertCreated()->assertJsonPath('data.created_count', 2);

    $cards = NfcCard::where('business_account_id', $owner->id)->get();
    expect($cards)->toHaveCount(2)
        ->and($cards->pluck('card_template_id')->unique()->all())->toBe([$template->id])
        ->and($cards->first()->notes)->toContain('Company: Acme Sdn Bhd')
        ->and($owner->fresh()->company)->toBe('Acme Sdn Bhd');

    Notification::assertSentTo($admin, AdminBulkOrderNotification::class);
});

it('refuses an order larger than the remaining card quota', function () {
    $owner = User::factory()->businessOwner(1)->create();
    Sanctum::actingAs($owner);

    $this->postJson('/api/business/employees/upload', [
        'employees' => bulkRecipients(3),
        'design_method' => 'template',
        'selected_template' => '1',
    ])->assertStatus(403);

    expect(NfcCard::count())->toBe(0);
});

it('only re-uses a previous custom design that belongs to the business', function () {
    $owner = User::factory()->businessOwner(5)->create();
    NfcCard::create([
        'user_id' => $owner->id, 'business_account_id' => $owner->id, 'card_owner' => 'Old',
        'billing_address' => 'x', 'contact_number' => '1', 'status' => 'active', 'subscription_plan' => 'business',
        'purchase_amount' => 0, 'purchase_date' => now(),
        'is_custom_design' => true, 'custom_design_front_url' => '/storage/card-designs/ours.png',
    ]);
    Sanctum::actingAs($owner);

    // Someone else's artwork URL is rejected.
    $this->postJson('/api/business/employees/upload', [
        'employees' => bulkRecipients(1),
        'design_method' => 'custom',
        'front_design_url' => '/storage/card-designs/someone-else.png',
    ])->assertStatus(422);

    // Its own previous design is accepted.
    $this->postJson('/api/business/employees/upload', [
        'employees' => bulkRecipients(1),
        'design_method' => 'custom',
        'front_design_url' => '/storage/card-designs/ours.png',
    ])->assertCreated();

    expect(NfcCard::where('card_owner', 'Staff 1')->value('custom_design_front_url'))->toBe('/storage/card-designs/ours.png');
});

it('validates each recipient', function () {
    $owner = User::factory()->businessOwner(5)->create();
    Sanctum::actingAs($owner);

    $this->postJson('/api/business/employees/upload', [
        'employees' => [['name' => 'No Email', 'position' => 'Exec']],
        'design_method' => 'template',
        'selected_template' => '1',
    ])->assertStatus(422)->assertJsonValidationErrors('employees.0.email');
});
