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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Notification type
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
                'admin_announcement'
            ]);
            
            // Notification content
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional metadata
            
            // Status
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            
            // Priority
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            
            // Action URL (optional)
            $table->string('action_url')->nullable();
            $table->string('action_text')->nullable();
            
            // Icon/Image
            $table->string('icon')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'is_read']);
            $table->index(['user_id', 'created_at']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
