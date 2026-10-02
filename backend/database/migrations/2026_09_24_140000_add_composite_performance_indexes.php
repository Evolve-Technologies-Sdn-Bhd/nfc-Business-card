<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance tuning migration — add composite indexes to high-traffic tables that
 * will grow quickly with real users. These indexes are designed for the exact
 * query patterns used in AnalyticsController, UserDashboard queries, and list pages.
 *
 * IMPORTANT: ALL indexes use IF NOT EXISTS-compatible techniques — running this
 * migration twice on the same database is a no-op (safe for environments where
 * indexes may have been added manually).
 */
return new class extends Migration
{
    private function getExistingIndexNames(string $table): \Illuminate\Support\Collection
    {
        try {
            $connection = Schema::getConnection();
            $driver = $connection->getDriverName();
            if ($driver === 'sqlite') {
                $rows = $connection->select(
                    "SELECT name FROM sqlite_master WHERE type='index' AND tbl_name = ?",
                    [$table]
                );
                return collect($rows)->map(fn ($r) => $r->name ?? null)
                    ->filter(fn ($n) => is_string($n))
                    ->map(fn ($n) => strtolower($n))
                    ->flip();
            }
            return collect(Schema::getIndexListing($table))
                ->pluck('name')
                ->filter(fn ($name) => is_string($name) || is_int($name))
                ->map(fn ($n) => strtolower((string) $n))
                ->flip();
        } catch (\Throwable) {
            return collect();
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        return $this->getExistingIndexNames($table)->has(strtolower($indexName));
    }

    private function safeCreateIndex(string $table, array $columns, string $indexName): void
    {
        if ($this->hasIndex($table, $indexName)) {
            return;
        }
        try {
            Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
        } catch (\Throwable) {
            // ignore pre-existing or driver-specific issues
        }
    }

    private function safeDropIndex(string $table, string $indexName): void
    {
        if (!$this->hasIndex($table, $indexName)) {
            return;
        }
        try {
            Schema::table($table, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        } catch (\Throwable) {
            // ignore
        }
    }

    public function up(): void
    {
        // ----- analytics ----- trackable type + id + created_at for time-range scoped queries
        $this->safeCreateIndex('analytics', ['trackable_type', 'trackable_id', 'created_at'], 'analytics_trackable_trackable_id_created_at_index');
        $this->safeCreateIndex('analytics', ['event_type', 'created_at'], 'analytics_event_type_created_at_index');

        // ----- nfc_cards --------- user_id + status (user dashboard card listings)
        $this->safeCreateIndex('nfc_cards', ['user_id', 'status'], 'nfc_cards_user_id_status_index');
        $this->safeCreateIndex('nfc_cards', ['business_account_id', 'subscription_plan'], 'nfc_cards_business_account_id_subscription_plan_index');
        $this->safeCreateIndex('nfc_cards', ['status', 'assigned_at'], 'nfc_cards_status_assigned_at_index');

        // ----- notifications -------- user_id + is_read + created_at DESC for paginated inbox
        $this->safeCreateIndex('notifications', ['user_id', 'is_read', 'created_at'], 'notifications_user_id_is_read_created_at_index');

        // ----- social_links -------- landing_page_id + is_active + sort_order for profile rendering
        $this->safeCreateIndex('social_links', ['landing_page_id', 'is_active', 'sort_order'], 'social_links_landing_page_id_is_active_sort_order_index');

        // ----- landing_pages ----- user_id + status for user landing page listings
        $this->safeCreateIndex('landing_pages', ['user_id', 'status'], 'landing_pages_user_id_status_index');

        // ----- subscriptions ----- user_id + status for active lookup performance
        $this->safeCreateIndex('subscriptions', ['user_id', 'status', 'next_billing_date'], 'subscriptions_user_id_status_next_billing_index');
        $this->safeCreateIndex('subscriptions', ['status', 'next_billing_date'], 'subscriptions_status_next_billing_date_index');

        // ----- activity_logs ----- user_id + created_at for audit log listing
        $this->safeCreateIndex('activity_logs', ['user_type', 'created_at'], 'activity_logs_user_type_created_at_index');
        $this->safeCreateIndex('activity_logs', ['loggable_type', 'loggable_id', 'created_at'], 'activity_logs_entity_created_at_index');
    }

    public function down(): void
    {
        $this->safeDropIndex('analytics', 'analytics_trackable_trackable_id_created_at_index');
        $this->safeDropIndex('analytics', 'analytics_event_type_created_at_index');

        $this->safeDropIndex('nfc_cards', 'nfc_cards_user_id_status_index');
        $this->safeDropIndex('nfc_cards', 'nfc_cards_business_account_id_subscription_plan_index');
        $this->safeDropIndex('nfc_cards', 'nfc_cards_status_assigned_at_index');

        $this->safeDropIndex('notifications', 'notifications_user_id_is_read_created_at_index');

        $this->safeDropIndex('social_links', 'social_links_landing_page_id_is_active_sort_order_index');

        $this->safeDropIndex('landing_pages', 'landing_pages_user_id_status_index');

        $this->safeDropIndex('subscriptions', 'subscriptions_user_id_status_next_billing_index');
        $this->safeDropIndex('subscriptions', 'subscriptions_status_next_billing_date_index');

        $this->safeDropIndex('activity_logs', 'activity_logs_user_type_created_at_index');
        $this->safeDropIndex('activity_logs', 'activity_logs_entity_created_at_index');
    }
};
