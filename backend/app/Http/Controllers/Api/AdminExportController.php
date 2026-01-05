<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NfcCard;
use App\Models\User;
use App\Models\Analytics;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminExportController extends Controller
{
    /**
     * Export all NFC Cards to CSV
     * Priority: HIGH
     */
    public function exportNfcCards(Request $request)
    {
        // Set a longer timeout for large exports
        set_time_limit(60);

        $nfcCards = NfcCard::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        // Define CSV headers
        $headers = [
            'NFC Card ID',
            'Card Owner',
            'Linked User Email',
            'Purchase Amount',
            'Subscription Plan',
            'Order Status',
            'Created Date',
            'Billing Address',
            'Contact Number'
        ];

        // Build CSV data
        $csvData = $this->buildCsv($headers, $nfcCards->map(function ($card) {
            return [
                $card->nfc_card_id ?? 'N/A',
                $card->card_owner ?? 'N/A',
                $card->user->email ?? 'N/A',
                $card->purchase_amount ?? '0',
                $card->subscription_plan ?? 'N/A',
                $card->status ?? 'pending',
                $card->created_at ? $card->created_at->format('Y-m-d') : 'N/A',
                $card->billing_address ?? 'N/A',
                $card->contact_number ?? 'N/A',
            ];
        })->toArray());

        $filename = 'nfc_cards_export_' . date('Ymd') . '.csv';

        return response($csvData, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export all Users to CSV
     * Priority: MEDIUM
     */
    public function exportUsers(Request $request)
    {
        set_time_limit(60);

        $users = User::orderBy('created_at', 'desc')->get();

        $headers = [
            'User ID',
            'Name',
            'Email',
            'Role',
            'Subscription Plan',
            'Status',
            'Join Date'
        ];

        $csvData = $this->buildCsv($headers, $users->map(function ($user) {
            $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
            return [
                $user->id,
                $name ?: 'N/A',
                $user->email ?? 'N/A',
                $user->role ?? 'user',
                $user->subscription_plan ?? 'free',
                $user->status ?? 'active',
                $user->created_at ? $user->created_at->format('Y-m-d') : 'N/A',
            ];
        })->toArray());

        $filename = 'users_export_' . date('Ymd') . '.csv';

        return response($csvData, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export Analytics to CSV (Last 30 days by default)
     * Priority: LOW
     */
    public function exportAnalytics(Request $request)
    {
        set_time_limit(60);

        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(30);

        $headers = [
            'Date',
            'Total Platform Views',
            'Total NFC Taps',
            'Total Link Clicks',
            'Active Users Count',
            'Mobile %',
            'Desktop %',
            'Top Location'
        ];

        // Get daily analytics for date range
        $rows = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $dayStart = $currentDate->copy()->startOfDay();
            $dayEnd = $currentDate->copy()->endOfDay();

            // Platform views (landing_page_view action)
            $views = Analytics::whereBetween('created_at', [$dayStart, $dayEnd])
                ->where('action', 'landing_page_view')
                ->count();

            // NFC taps
            $taps = Analytics::whereBetween('created_at', [$dayStart, $dayEnd])
                ->where('action', 'nfc_tap')
                ->count();

            // Link clicks
            $clicks = Analytics::whereBetween('created_at', [$dayStart, $dayEnd])
                ->where('action', 'link_click')
                ->count();

            // Active users (unique IPs for that day)
            $activeUsers = Analytics::whereBetween('created_at', [$dayStart, $dayEnd])
                ->distinct('ip_address')
                ->count('ip_address');

            // Device breakdown
            $totalEvents = Analytics::whereBetween('created_at', [$dayStart, $dayEnd])->count();
            $mobileCount = Analytics::whereBetween('created_at', [$dayStart, $dayEnd])
                ->where('device_type', 'mobile')
                ->count();
            $desktopCount = Analytics::whereBetween('created_at', [$dayStart, $dayEnd])
                ->where('device_type', 'desktop')
                ->count();

            $mobilePercent = $totalEvents > 0 ? round(($mobileCount / $totalEvents) * 100, 1) : 0;
            $desktopPercent = $totalEvents > 0 ? round(($desktopCount / $totalEvents) * 100, 1) : 0;

            // Top location
            $topLocation = Analytics::whereBetween('created_at', [$dayStart, $dayEnd])
                ->whereNotNull('city')
                ->selectRaw('city, COUNT(*) as count')
                ->groupBy('city')
                ->orderByDesc('count')
                ->first();

            $rows[] = [
                $dateStr,
                $views,
                $taps,
                $clicks,
                $activeUsers,
                $mobilePercent . '%',
                $desktopPercent . '%',
                $topLocation ? $topLocation->city : 'N/A',
            ];

            $currentDate->addDay();
        }

        $csvData = $this->buildCsv($headers, $rows);

        $filename = 'analytics_export_' . $startDate->format('Ymd') . '_to_' . $endDate->format('Ymd') . '.csv';

        return response($csvData, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Build CSV string from headers and rows
     */
    private function buildCsv(array $headers, array $rows): string
    {
        $csv = "\xEF\xBB\xBF"; // UTF-8 BOM for Excel compatibility

        // Add headers
        $csv .= implode(',', array_map(function ($field) {
            return '"' . str_replace('"', '""', $field) . '"';
        }, $headers)) . "\n";

        // Add data rows
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(function ($field) {
                return '"' . str_replace('"', '""', (string) $field) . '"';
            }, $row)) . "\n";
        }

        return $csv;
    }
}
