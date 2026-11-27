<?php

use App\Models\LandingPage;
use App\Models\NfcCard;
use App\Models\ProfileBuilderField;
use App\Models\User;
use App\Support\VideoEmbed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

/**
 * Introduction Video and Company Video: saved from the builder, validated as
 * YouTube/Vimeo, and returned to the public page as plan-gated player URLs.
 */

beforeEach(function () {
    foreach ([
        'introVideo' => ['profile', ['premium', 'business']],
        'companyVideo' => ['company', ['business']],
    ] as $key => [$tab, $plans]) {
        ProfileBuilderField::create([
            'tab' => $tab,
            'field_key' => $key,
            'field_type' => 'video',
            'label' => $key,
            'is_visible' => true,
            'available_plans' => $plans,
            'display_order' => 1,
        ]);
    }
});

function videoProfile(string $plan): array
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

    return [$user, $card];
}

it('turns YouTube and Vimeo links into privacy-friendly players and rejects others', function () {
    expect(VideoEmbed::embedUrl('https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=42'))->toBe('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ')
        ->and(VideoEmbed::embedUrl('youtu.be/dQw4w9WgXcQ'))->toBe('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ')
        ->and(VideoEmbed::embedUrl('https://youtube.com/shorts/dQw4w9WgXcQ'))->toBe('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ')
        ->and(VideoEmbed::embedUrl('https://vimeo.com/76979871'))->toBe('https://player.vimeo.com/video/76979871?dnt=1')
        ->and(VideoEmbed::embedUrl('https://evil.example/watch?v=dQw4w9WgXcQ'))->toBeNull()
        ->and(VideoEmbed::embedUrl('javascript:alert(1)'))->toBeNull()
        ->and(VideoEmbed::embedUrl('https://youtube.com/watch?v=too-short'))->toBeNull();
});

it('saves the introduction video and shows it on a Business profile', function () {
    [$user, $card] = videoProfile('business');
    Sanctum::actingAs($user);

    $this->putJson("/api/nfc-cards/{$card->id}/landing-page", [
        'name' => 'Owner',
        'intro_video' => 'https://youtu.be/dQw4w9WgXcQ',
        'company_video' => 'https://vimeo.com/76979871',
    ])->assertOk();

    expect(LandingPage::where('nfc_card_id', $card->id)->value('intro_video'))->toBe('https://youtu.be/dQw4w9WgXcQ');

    $this->getJson("/api/nfc-cards/{$card->id}/landing-page")
        ->assertOk()
        ->assertJsonPath('videos.intro', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ')
        ->assertJsonPath('videos.company', 'https://player.vimeo.com/video/76979871?dnt=1');
});

it('rejects links that are not YouTube or Vimeo', function () {
    [$user, $card] = videoProfile('premium');
    Sanctum::actingAs($user);

    $this->putJson("/api/nfc-cards/{$card->id}/landing-page", [
        'intro_video' => 'https://example.com/my-video.mp4',
    ])->assertStatus(422)->assertJsonValidationErrors('intro_video');
});

it('only shows each video on plans that include it', function () {
    [, $premium] = videoProfile('premium');
    LandingPage::create([
        'nfc_card_id' => $premium->id,
        'intro_video' => 'https://youtu.be/dQw4w9WgXcQ',
        'company_video' => 'https://vimeo.com/76979871',
    ]);

    $this->getJson("/api/nfc-cards/{$premium->id}/landing-page")
        ->assertJsonPath('videos.intro', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ')
        ->assertJsonPath('videos.company', null); // Business-only

    [, $basic] = videoProfile('basic');
    LandingPage::create(['nfc_card_id' => $basic->id, 'intro_video' => 'https://youtu.be/dQw4w9WgXcQ']);

    $this->getJson("/api/nfc-cards/{$basic->id}/landing-page")
        ->assertJsonPath('videos.intro', null); // Premium/Business only
});
