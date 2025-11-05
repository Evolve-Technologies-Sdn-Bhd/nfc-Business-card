<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NfcTag;
use App\Models\Analytics;
use Illuminate\Http\Request;
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
        $nfcTag = NfcTag::where('nfc_id', $nfcId)
                       ->where('status', 'active')
                       ->first();

        if (!$nfcTag) {
            return response()->json([
                'success' => false,
                'message' => 'NFC tag not found or inactive'
            ], 404);
        }

        // Update tap count and last tapped time
        $nfcTag->increment('tap_count');
        $nfcTag->update(['last_tapped_at' => now()]);

        // Track analytics
        Analytics::create([
            'trackable_type' => NfcTag::class,
            'trackable_id' => $nfcTag->id,
            'action' => 'nfc_tap',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $this->getDeviceType($request->userAgent()),
            'browser' => $this->getBrowser($request->userAgent()),
            'platform' => $this->getPlatform($request->userAgent()),
        ]);

        // Return profile data for the NFC tap
        $profile = $nfcTag->user->profile;
        
        return response()->json([
            'success' => true,
            'profile' => $profile->load(['socialLinks' => function ($query) {
                $query->where('is_active', true)->orderBy('order');
            }]),
            'nfc_tag' => $nfcTag
        ]);
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

