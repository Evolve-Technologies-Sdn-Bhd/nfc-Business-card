<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Add approval fields
            $table->boolean('is_approved')->default(false)->after('is_read');
            $table->boolean('is_rejected')->default(false)->after('is_approved');
            $table->text('rejection_reason')->nullable()->after('is_rejected');
            $table->timestamp('approved_at')->nullable()->after('rejection_reason');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->after('rejected_at');
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null')->after('approved_by');
            
            // Add sticky field for pinned notifications
            $table->boolean('sticky')->default(false)->after('rejected_by');
            
            // Add business_card_order_request to type enum
            $table->dropColumn('type');
        });

        // Recreate type column with new enum values
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['approved_by']);
            $table->dropForeignKeyIfExists(['rejected_by']);
            $table->dropColumn([
                'is_approved',
                'is_rejected',
                'rejection_reason',
                'approved_at',
                'rejected_at',
                'approved_by',
                'rejected_by',
                'sticky',
            ]);
        });
    }
};
