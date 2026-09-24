<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NfcCard;
use App\Models\NfcTag;
use App\Models\LandingPage;
use App\Models\Analytics;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NfcController extends Controller
{
    public function index(Request $request)
    {
        $nfcTags = $request->user()->nfcTags;

        return response()->json([
            'success' => true,
            'nfc_tags' => $nfcTags
        ]);
    }

    public function activate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nfc_id' => 'required|string|unique:nfc_tags,nfc_id',
            'name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Check if user already has an NFC tag
        if ($user->nfcTag) {
            return response()->json([
                'success' => false,
                'message' => 'User already has an NFC tag activated'
            ], 400);
        }

        $nfcTag = NfcTag::create([
            'user_id' => $user->id,
            'nfc_id' => $request->nfc_id,
            'name' => $request->name ?? 'Business Card',
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'nfc_tag' => $nfcTag
        ], 201);
    }

    public function update(Request $request, NfcTag $tag)
    {
        // Check ownership
        if ($tag->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $tag->update($request->only(['name', 'status']));

        return response()->json([
            'success' => true,
            'nfc_tag' => $tag
        ]);
    }

    public function tap(Request $request, $nfcId)
    {
        $deviceType = $this->getDeviceType($request->userAgent());
        $browser    = $this->getBrowser($request->userAgent());
        $platform   = $this->getPlatform($request->userAgent());
        $commonAnalytics = [
            'action'     => 'nfc_tap',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $deviceType,
            'browser'    => $browser,
            'platform'   => $platform,
        ];

        // ========================================================
        // Priority 1 — lookup by physical NFC tag chip id (nfc_id)
        // This is the canonical behavior for actual NFC hardware.
        // ========================================================
        $nfcTag = NfcTag::where('nfc_id', $nfcId)
            ->where('status', 'active')
            ->first();

        if ($nfcTag) {
            $nfcTag->increment('tap_count');
            $nfcTag->update(['last_tapped_at' => now()]);

            Analytics::create([
                'trackable_type' => NfcTag::class,
                'trackable_id'   => $nfcTag->id,
            ] + $commonAnalytics);

            $profile = $this->resolveProfileForTap($nfcTag->user, $nfcTag->nfcCard ?? null);

            return response()->json([
                'success' => true,
                'profile' => $profile ? $profile->load(['socialLinks' => function ($q) {
                    $q->where('is_active', true)->orderBy('order');
                }]) : null,
                'nfc_tag' => $nfcTag,
                'resolved_via' => 'nfc_tag_nfc_id',
            ]);
        }

        // ==============================================================
        // Priority 2 — fallback: treat $nfcId as a phone number lookup.
        // This lets the same tap endpoint double as a phone-number-based
        // profile resolver (useful for URLs and virtual NFC flows).
        // ==============================================================
        if (NfcCard::looksLikePhoneNumber($nfcId)) {
            $nfcCard = NfcCard::findByPhoneNumber($nfcId);

            if ($nfcCard) {
                $relatedTag = $nfcCard->nfcTag;
                if ($relatedTag && $relatedTag->status === 'active') {
                    $relatedTag->increment('tap_count');
                    $relatedTag->update(['last_tapped_at' => now()]);
                }

                $trackable = $relatedTag ?? $nfcCard;
                Analytics::create([
                    'trackable_type' => $trackable ? get_class($trackable) : LandingPage::class,
                    'trackable_id'   => $trackable->id ?? 0,
                ] + $commonAnalytics);

                Log::info('NFC tap resolved via phone number fallback', [
                    'lookup_value'   => $nfcId,
                    'nfc_card_id'    => $nfcCard->id,
                    'nfc_card_ref'   => $nfcCard->nfc_card_id,
                    'has_nfc_tag'    => (bool) $relatedTag,
                ]);

                $profile = $this->resolveProfileForTap($nfcCard->user ?? null, $nfcCard);

                return response()->json([
                    'success' => true,
                    'profile' => $profile ? $profile->load(['socialLinks' => function ($q) {
                        $q->where('is_active', true)->orderBy('order');
                    }]) : null,
                    'nfc_tag' => $relatedTag,
                    'nfc_card' => $nfcCard,
                    'resolved_via' => 'phone_number_fallback',
                ]);
            }
        }

        // ==============================================================
        // Neither physical chip id nor phone number matched anything.
        // ==============================================================
        return response()->json([
            'success' => false,
            'message' => 'NFC tag not found or inactive'
        ], 404);
    }

    /**
     * Resolve the profile (LandingPage) payload for an NFC tap.
     * Prefers the card's explicit LandingPage; falls back to building
     * a minimal payload from the User when no landing page exists yet.
     *
     * @param  User|null     $user
     * @param  NfcCard|null  $nfcCard
     * @return LandingPage|\stdClass|null
     */
    private function resolveProfileForTap($user, $nfcCard)
    {
        if ($nfcCard && $nfcCard->landingPage) {
            return $nfcCard->landingPage;
        }

        if ($nfcCard) {
            $landingPage = LandingPage::firstOrCreate(
                ['nfc_card_id' => $nfcCard->id],
                [
                    'name'     => $user->full_name ?? '',
                    'email'    => $user->email ?? '',
                    'is_active' => true,
                ]
            );
            return $landingPage;
        }

        if ($user && method_exists($user, 'profile') && $user->profile) {
            return $user->profile;
        }

        return null;
    }

    public function deactivate(Request $request, NfcTag $tag)
    {
        // Check ownership
        if ($tag->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $tag->update(['status' => 'inactive']);

        return response()->json([
            'success' => true,
            'message' => 'NFC tag deactivated successfully'
        ]);
    }

    private function getDeviceType($userAgent)
    {
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            return 'mobile';
        } elseif (preg_match('/Tablet|iPad/', $userAgent)) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }

    private function getBrowser($userAgent)
    {
        if (preg_match('/Chrome/', $userAgent)) {
            return 'Chrome';
        } elseif (preg_match('/Firefox/', $userAgent)) {
            return 'Firefox';
        } elseif (preg_match('/Safari/', $userAgent)) {
            return 'Safari';
        } elseif (preg_match('/Edge/', $userAgent)) {
            return 'Edge';
        } else {
            return 'Other';
        }
    }

    private function getPlatform($userAgent)
    {
        if (preg_match('/Windows/', $userAgent)) {
            return 'Windows';
        } elseif (preg_match('/Mac/', $userAgent)) {
            return 'macOS';
        } elseif (preg_match('/Linux/', $userAgent)) {
            return 'Linux';
        } elseif (preg_match('/Android/', $userAgent)) {
            return 'Android';
        } elseif (preg_match('/iPhone|iPad/', $userAgent)) {
            return 'iOS';
        } else {
            return 'Other';
        }
    }
}

