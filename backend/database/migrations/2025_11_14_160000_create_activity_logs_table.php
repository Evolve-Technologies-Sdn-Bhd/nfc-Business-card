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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Who performed the action
            $table->foreignId('business_account_id')->nullable()->constrained('users')->onDelete('cascade'); // Related Business account
            
            // Activity details
            $table->enum('action_type', [
                'login',
                'logout',
                'profile_updated',
                'password_changed',
                'email_changed',
                'landing_page_updated',
                'landing_page_created',
                'nfc_card_activated',
                'nfc_card_deactivated',
                'link_added',
                'link_updated',
                'link_deleted',
                'link_reordered',
                'profile_image_updated',
                'company_logo_updated',
                'social_links_updated',
                'contact_info_updated',
                'card_design_updated',
                'settings_changed',
            ]);
            
            $table->string('action_description'); // Human-readable description
            $table->string('entity_type')->nullable(); // e.g., 'nfc_card', 'link', 'profile'
            $table->unsignedBigInteger('entity_id')->nullable(); // ID of the affected entity
            
            // Change details
            $table->json('old_values')->nullable(); // Previous values (for updates)
            $table->json('new_values')->nullable(); // New values (for updates)
            $table->json('metadata')->nullable(); // Additional context (IP, user agent, etc.)
            
            // Request information
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['business_account_id', 'created_at']);
            $table->index('action_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
