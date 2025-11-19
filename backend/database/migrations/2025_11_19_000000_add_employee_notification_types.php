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
        // Drop the type column
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        // Recreate type column with new enum values including account_created and order_rejected
        Schema::table('notifications', function (Blueprint $table) {
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
                'account_created',           // New: When employee account is created
                'order_rejected'             // New: When order is rejected
            ])->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the type column
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        // Recreate type column with old enum values (without account_created and order_rejected)
        Schema::table('notifications', function (Blueprint $table) {
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
        });
    }
};
