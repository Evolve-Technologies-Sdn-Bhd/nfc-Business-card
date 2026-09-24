<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'type')) {
                try {
                    $table->dropColumn('type');
                } catch (\Exception $e) {
                }
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'type')) {
                $table->enum('type', [
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
                    'business_card_order_request',
                    'business_bulk_order_placed',
                    'account_created',
                    'order_rejected'
                ])->after('user_id');
            }
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'type')) {
                try {
                    $table->dropColumn('type');
                } catch (\Exception $e) {
                }
            }
        });

        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'type')) {
                $table->enum('type', [
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
                    'business_card_order_request',
                    'business_bulk_order_placed'
                ])->after('user_id');
            }
        });
    }
};
