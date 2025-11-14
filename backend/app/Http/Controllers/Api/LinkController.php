<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LinkController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function index(Request $request)
    {
        $user = $request->user();
        $nfcCard = $user->nfcCards()->first();
        
        if (!$nfcCard || !$nfcCard->landingPage) {
            return response()->json([
                'success' => false,
                'message' => 'No landing page found',
                'links' => []
            ], 404);
        }
        
        $links = $nfcCard->landingPage->socialLinks;

        return response()->json([
            'success' => true,
            'links' => $links
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'platform' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'url' => 'required|url',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $nfcCard = $user->nfcCards()->first();
        
        if (!$nfcCard || !$nfcCard->landingPage) {
            return response()->json([
                'success' => false,
                'message' => 'No landing page found'
            ], 404);
        }
        
        $landingPage = $nfcCard->landingPage;
        $maxOrder = $landingPage->socialLinks()->max('order') ?? -1;

        $link = SocialLink::create([
            'landing_page_id' => $landingPage->id,
            'platform' => $request->platform,
            'title' => $request->title,
            'url' => $request->url,
            'is_active' => $request->is_active ?? true,
            'order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'link' => $link
        ], 201);
    }

    public function update(Request $request, SocialLink $link)
    {
        $user = $request->user();
        $nfcCard = $user->nfcCards()->first();
        
        if (!$nfcCard || !$nfcCard->landingPage) {
            return response()->json(['error' => 'No landing page found'], 404);
        }
        
        // Check ownership
        if ($link->landing_page_id !== $nfcCard->landingPage->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'url' => 'nullable|url',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $link->update($request->all());

        return response()->json([
            'success' => true,
            'link' => $link
        ]);
    }

    public function destroy(Request $request, SocialLink $link)
    {
        $user = $request->user();
        $nfcCard = $user->nfcCards()->first();
        
        if (!$nfcCard || !$nfcCard->landingPage) {
            return response()->json(['error' => 'No landing page found'], 404);
        }
        
        // Check ownership
        if ($link->landing_page_id !== $nfcCard->landingPage->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $link->delete();

        // Reorder remaining links
        $nfcCard->landingPage->socialLinks()
            ->where('order', '>', $link->order)
            ->decrement('order');

        return response()->json([
            'success' => true,
            'message' => 'Link deleted successfully'
        ]);
    }

    public function reorder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'links' => 'required|array',
            'links.*.id' => 'required|exists:social_links,id',
            'links.*.order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $nfcCard = $user->nfcCards()->first();
        
        if (!$nfcCard || !$nfcCard->landingPage) {
            return response()->json([
                'success' => false,
                'message' => 'No landing page found'
            ], 404);
        }
        
        $landingPage = $nfcCard->landingPage;

        foreach ($request->links as $linkData) {
            SocialLink::where('id', $linkData['id'])
                ->where('landing_page_id', $landingPage->id)
                ->update(['order' => $linkData['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Links reordered successfully'
        ]);
    }

    public function trackClick(Request $request, SocialLink $link)
    {
        // Increment click count
        $link->incrementClickCount();

        // Track analytics
        \App\Models\Analytics::create([
            'trackable_type' => SocialLink::class,
            'trackable_id' => $link->id,
            'action' => 'link_click',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $this->getDeviceType($request->userAgent()),
            'browser' => $this->getBrowser($request->userAgent()),
            'platform' => $this->getPlatform($request->userAgent()),
        ]);

        // Check for milestones (every 100 clicks)
        if ($link->click_count % 100 === 0 && $link->click_count > 0) {
            // Get the landing page owner through NFC card
            $landingPage = $link->landingPage;
            if ($landingPage && $landingPage->nfcCard) {
                $user = $landingPage->nfcCard->user;
                
                // Send milestone notification
                $this->notificationService->create($user, 'link_milestone', [
                    'link_title' => $link->title,
                    'click_count' => $link->click_count,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Click tracked successfully'
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

