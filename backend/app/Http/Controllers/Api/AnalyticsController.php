<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Analytics;
use App\Models\Profile;
use App\Models\NfcTag;
use App\Models\SocialLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function track(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'trackable_type' => 'required|string|in:App\Models\Profile,App\Models\NfcTag,App\Models\SocialLink',
            'trackable_id' => 'required|integer',
            'action' => 'required|string',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $analytics = Analytics::create([
            'trackable_type' => $request->trackable_type,
            'trackable_id' => $request->trackable_id,
            'action' => $request->action,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $this->getDeviceType($request->userAgent()),
            'browser' => $this->getBrowser($request->userAgent()),
            'platform' => $this->getPlatform($request->userAgent()),
            'data' => $request->data,
        ]);

        // If it's a link click, increment the click count
        if ($request->action === 'link_click' && $request->trackable_type === 'App\Models\SocialLink') {
            $link = SocialLink::find($request->trackable_id);
            if ($link) {
                $link->incrementClickCount();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Analytics tracked successfully'
        ]);
    }

    public function overview(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;
        $nfcTag = $user->nfcTag;

        // Get date range (default to last 30 days)
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(30);

        // Profile views
        $profileViews = Analytics::where('trackable_type', Profile::class)
            ->where('trackable_id', $profile->id)
            ->where('action', 'profile_view')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // NFC taps
        $nfcTaps = 0;
        if ($nfcTag) {
            $nfcTaps = Analytics::where('trackable_type', NfcTag::class)
                ->where('trackable_id', $nfcTag->id)
                ->where('action', 'nfc_tap')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
        }

        // Link clicks
        $linkClicks = Analytics::where('trackable_type', SocialLink::class)
            ->whereIn('trackable_id', $profile->socialLinks->pluck('id'))
            ->where('action', 'link_click')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Daily breakdown for the last 7 days
        $dailyStats = Analytics::where(function ($query) use ($profile, $nfcTag) {
            $query->where(function ($q) use ($profile) {
                $q->where('trackable_type', Profile::class)
                    ->where('trackable_id', $profile->id)
                    ->where('action', 'profile_view');
            });

            if ($nfcTag) {
                $query->orWhere(function ($q) use ($nfcTag) {
                    $q->where('trackable_type', NfcTag::class)
                        ->where('trackable_id', $nfcTag->id)
                        ->where('action', 'nfc_tap');
                });
            }
        })
            ->whereBetween('created_at', [Carbon::now()->subDays(7), Carbon::now()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top performing links
        $topLinks = $profile->socialLinks()
            ->withCount(['analytics as click_count' => function ($query) use ($startDate, $endDate) {
                $query->where('action', 'link_click')
                    ->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->orderByDesc('click_count')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'overview' => [
                    'profile_views' => $profileViews,
                    'nfc_taps' => $nfcTaps,
                    'link_clicks' => $linkClicks,
                    'total_interactions' => $profileViews + $nfcTaps + $linkClicks,
                ],
                'daily_stats' => $dailyStats,
                'top_links' => $topLinks,
                'date_range' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                ]
            ]
        ]);
    }

    public function profileAnalytics(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        // Get date range from request or default to last 30 days
        $endDate = Carbon::now();
        $startDate = $request->get('start_date')
            ? Carbon::parse($request->get('start_date'))
            : $endDate->copy()->subDays(30);

        // Profile views over time
        $profileViews = Analytics::where('trackable_type', Profile::class)
            ->where('trackable_id', $profile->id)
            ->where('action', 'profile_view')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Device breakdown
        $deviceBreakdown = Analytics::where('trackable_type', Profile::class)
            ->where('trackable_id', $profile->id)
            ->where('action', 'profile_view')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->get();

        // Browser breakdown
        $browserBreakdown = Analytics::where('trackable_type', Profile::class)
            ->where('trackable_id', $profile->id)
            ->where('action', 'profile_view')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('browser, COUNT(*) as count')
            ->groupBy('browser')
            ->get();

        // Platform breakdown
        $platformBreakdown = Analytics::where('trackable_type', Profile::class)
            ->where('trackable_id', $profile->id)
            ->where('action', 'profile_view')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('platform, COUNT(*) as count')
            ->groupBy('platform')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'profile_views' => $profileViews,
                'device_breakdown' => $deviceBreakdown,
                'browser_breakdown' => $browserBreakdown,
                'platform_breakdown' => $platformBreakdown,
                'total_views' => $profileViews->sum('count'),
                'date_range' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                ]
            ]
        ]);
    }

    public function nfcAnalytics(Request $request, NfcTag $tag)
    {
        // Check ownership
        if ($tag->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if user has pro subscription for detailed analytics
        $hasProSubscription = $request->user()->hasProSubscription();

        // Get date range from request or default to last 30 days
        $endDate = Carbon::now();
        $startDate = $request->get('start_date')
            ? Carbon::parse($request->get('start_date'))
            : $endDate->copy()->subDays(30);

        // NFC taps over time
        $nfcTaps = Analytics::where('trackable_type', NfcTag::class)
            ->where('trackable_id', $tag->id)
            ->where('action', 'nfc_tap')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Device breakdown for NFC taps
        $deviceBreakdown = Analytics::where('trackable_type', NfcTag::class)
            ->where('trackable_id', $tag->id)
            ->where('action', 'nfc_tap')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->get();

        // Platform breakdown for NFC taps
        $platformBreakdown = Analytics::where('trackable_type', NfcTag::class)
            ->where('trackable_id', $tag->id)
            ->where('action', 'nfc_tap')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('platform, COUNT(*) as count')
            ->groupBy('platform')
            ->get();

        // Recent taps
        $recentTaps = Analytics::where('trackable_type', NfcTag::class)
            ->where('trackable_id', $tag->id)
            ->where('action', 'nfc_tap')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $responseData = [
            'nfc_taps' => $nfcTaps,
            'total_taps' => $nfcTaps->sum('count'),
            'tag_info' => $tag,
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ]
        ];

        // Add detailed analytics only for pro users
        if ($hasProSubscription) {
            $responseData['device_breakdown'] = $deviceBreakdown;
            $responseData['platform_breakdown'] = $platformBreakdown;
            $responseData['recent_taps'] = $recentTaps;
        }

        return response()->json([
            'success' => true,
            'data' => $responseData
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
