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
    public function up(): void
    {
        // ----- analytics ----- trackable type + id + created_at for time-range scoped queries
        // Used by: AnalyticsController methods (getUserOverview, getBusinessOverview, getProfileAnalytics)
        Schema::table('analytics', function (Blueprint $table) {
            $indexName = 'analytics_trackable_trackable_id_created_at_index';
            $existing = collect(Schema::getIndexListing('analytics'))->pluck('name')->flip();
            if (!$existing->has($indexName)) {
                try {
                    $table->index(['trackable_type', 'trackable_id', 'created_at'], $indexName);
                } catch (\Throwable) { /* ignore pre-existing */ }
            }

            $eventIndex = 'analytics_event_type_created_at_index';
            if (!$existing->has($eventIndex)) {
                try {
                    $table->index(['event_type', 'created_at'], $eventIndex);
                } catch (\Throwable) { /* ignore */ }
            }
        });

        // ----- nfc_cards --------- user_id + status (user dashboard card listings)
        // ---- plus business_account_id + subscription_plan for Business user quota lookups
        Schema::table('nfc_cards', function (Blueprint $table) {
            $existing = collect(Schema::getIndexListing('nfc_cards'))->pluck('name')->flip();

            $userStatus = 'nfc_cards_user_id_status_index';
            if (!$existing->has($userStatus)) {
                try {
                    $table->index(['user_id', 'status'], $userStatus);
                } catch (\Throwable) { /* ignore */ }
            }

            $businessPlan = 'nfc_cards_business_account_id_subscription_plan_index';
            if (!$existing->has($businessPlan)) {
                try {
                    $table->index(['business_account_id', 'subscription_plan'], $businessPlan);
                } catch (\Throwable) { /* ignore */ }
            }

            $shipStatus = 'nfc_cards_status_assigned_at_index';
            if (!$existing->has($shipStatus)) {
                try {
                    $table->index(['status', 'assigned_at'], $shipStatus);
                } catch (\Throwable) { /* ignore */ }
            }
        });

        // ----- notifications -------- user_id + is_read + created_at DESC for paginated inbox
        Schema::table('notifications', function (Blueprint $table) {
            $existing = collect(Schema::getIndexListing('notifications'))->pluck('name')->flip();
            $indexName = 'notifications_user_id_is_read_created_at_index';
            if (!$existing->has($indexName)) {
                try {
                    $table->index(['user_id', 'is_read', 'created_at'], $indexName);
                } catch (\Throwable) { /* ignore */ }
            }
        });

        // ----- social_links -------- landing_page_id + is_active + sort_order for profile rendering
        Schema::table('social_links', function (Blueprint $table) {
            $existing = collect(Schema::getIndexListing('social_links'))->pluck('name')->flip();
            $indexName = 'social_links_landing_page_id_is_active_sort_order_index';
            if (!$existing->has($indexName)) {
                try {
                    $table->index(['landing_page_id', 'is_active', 'sort_order'], $indexName);
                } catch (\Throwable) { /* ignore */ }
            }
        });

        // ----- landing_pages ----- user_id + status for user landing page listings
        Schema::table('landing_pages', function (Blueprint $table) {
            $existing = collect(Schema::getIndexListing('landing_pages'))->pluck('name')->flip();
            $indexName = 'landing_pages_user_id_status_index';
            if (!$existing->has($indexName)) {
                try {
                    $table->index(['user_id', 'status'], $indexName);
                } catch (\Throwable) { /* ignore */ }
            }
        });

        // ----- subscriptions ----- user_id + status for active lookup performance
        Schema::table('subscriptions', function (Blueprint $table) {
            $existing = collect(Schema::getIndexListing('subscriptions'))->pluck('name')->flip();
            $indexName = 'subscriptions_user_id_status_next_billing_index';
            if (!$existing->has($indexName)) {
                try {
                    $table->index(['user_id', 'status', 'next_billing_date'], $indexName);
                } catch (\Throwable) { /* ignore */ }
            }
            $billingIndex = 'subscriptions_status_next_billing_date_index';
            if (!$existing->has($billingIndex)) {
                try {
                    $table->index(['status', 'next_billing_date'], $billingIndex);
                } catch (\Throwable) { /* ignore */ }
            }
        });

        // ----- activity_logs ----- user_id + created_at for audit log listing
        Schema::table('activity_logs', function (Blueprint $table) {
            $existing = collect(Schema::getIndexListing('activity_logs'))->pluck('name')->flip();
            $indexName = 'activity_logs_user_type_created_at_index';
            if (!$existing->has($indexName)) {
                try {
                    $table->index(['user_type', 'created_at'], $indexName);
                } catch (\Throwable) { /* ignore */ }
            }
            $entityIndex = 'activity_logs_entity_created_at_index';
            if (!$existing->has($entityIndex)) {
                try {
                    $table->index(['loggable_type', 'loggable_id', 'created_at'], $entityIndex);
                } catch (\Throwable) { /* ignore */ }
            }
        });
    }

    public function down(): void
    {
        Schema::table('analytics', function (Blueprint $table) {
            $existing = collect(Schema::getIndexListing('analytics'))->pluck('name')->flip();
            if ($existing->has('analytics_trackable_trackable_id_created_at_index')) {
                $table->dropIndex('analytics_trackable_trackable_id_created_at_index');
            }
            if ($existing->has('analytics_event_type_created_at_index')) {
                $table->dropIndex('analytics_event_type_created_at_index');
            }
        });

        Schema::table('nfc_cards', function (Blueprint $table) {
            $existing = collect(Schema::getIndexListing('nfc_cards'))->pluck('name')->flip();
            foreach ([
                'nfc_cards_user_id_status_index',
                'nfc_cards_business_account_id_subscription_plan_index',
                'nfc_cards_status_assigned_at_index',
            ] as $idx) {
                if ($existing->has($idx)) {
                    $table->dropIndex($idx);
                }
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            $existing = collect(Schema::getIndexListing('notifications'))->pluck('name')->flip();
            if ($existing->has('notifications_user_id_is_read_created_at_index')) {
                $table->dropIndex('notifications_user_id_is_read_created_at_index');
            }
        });

        Schema::table('social_links', function (Blueprint $table) {
            $existing = collect(Schema::getIndexListing('social_links'))->pluck('name')->flip();
            if ($existing->has('social_links_landing_page_id_is_active_sort_order_index')) {
                $table->dropIndex('social_links_landing_page_id_is_active_sort_order_index');
            }
        });

        Schema::table('landing_pages', function (Blueprint $table) {
            $existing = collect(Schema::getIndexListing('landing_pages'))->pluck('name')->flip();
            if ($existing->has('landing_pages_user_id_status_index')) {
                $table->dropIndex('landing_pages_user_id_status_index');
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $existing = collect(Schema::getIndexListing('subscriptions'))->pluck('name')->flip();
            foreach ([
                'subscriptions_user_id_status_next_billing_index',
                'subscriptions_status_next_billing_date_index',
            ] as $idx) {
                if ($existing->has($idx)) {
                    $table->dropIndex($idx);
                }
            }
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $existing = collect(Schema::getIndexListing('activity_logs'))->pluck('name')->flip();
            foreach ([
                'activity_logs_user_type_created_at_index',
                'activity_logs_entity_created_at_index',
            ] as $idx) {
                if ($existing->has($idx)) {
                    $table->dropIndex($idx);
                }
            }
        });
    }
};
