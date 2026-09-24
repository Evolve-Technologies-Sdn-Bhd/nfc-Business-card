<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            if (!Schema::hasColumn('notifications', 'pinned')) {
                Schema::table('notifications', function (Blueprint $table) {
                    $table->boolean('pinned')->default(false)->after('is_read');
                    $table->json('attachments')->nullable()->after('data');
                });
            }
        } catch (\Exception $e) {
        }

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM(
            'registration_success',
            'email_verification',
            'login_new_device',
            'profile_updated',
            'link_milestone',
            'landing_page_viewed',
            'contact_request',
            'subscription_upgrade',
            'payment_successful',
            'payment_failed',
            'account_warning',
            'password_changed',
            'nfc_card_purchased',
            'nfc_card_delivered',
            'nfc_card_linked',
            'nfc_card_activated',
            'nfc_card_expired',
            'app_update',
            'system_message',
            'admin_announcement',
            'business_bulk_order_placed',
            'employee_password_reset'
        )");
        }

        try {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index(['user_id', 'pinned', 'created_at']);
            });
        } catch (\Exception $e) {
        }
    }

    public function down(): void
    {
        try {
            if (Schema::hasColumn('notifications', 'pinned')) {
                Schema::table('notifications', function (Blueprint $table) {
                    try {
                        $table->dropIndex(['user_id', 'pinned', 'created_at']);
                    } catch (\Exception $e) {
                    }
                    $table->dropColumn(['pinned', 'attachments']);
                });
            }
        } catch (\Exception $e) {
        }

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM(
            'registration_success',
            'email_verification',
            'login_new_device',
            'profile_updated',
            'link_milestone',
            'landing_page_viewed',
            'contact_request',
            'subscription_upgrade',
            'payment_successful',
            'payment_failed',
            'account_warning',
            'password_changed',
            'nfc_card_purchased',
            'nfc_card_delivered',
            'nfc_card_linked',
            'nfc_card_activated',
            'nfc_card_expired',
            'app_update',
            'system_message',
            'admin_announcement'
        )");
        }
    }
};
