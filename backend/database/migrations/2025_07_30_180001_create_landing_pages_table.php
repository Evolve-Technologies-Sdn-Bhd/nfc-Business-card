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
        Schema::create('landing_pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nfc_card_id')->unique();
            $table->foreign('nfc_card_id')->references('id')->on('nfc_cards')->onDelete('cascade');
            
            // Basic Info
            $table->string('name')->nullable();
            $table->string('title')->nullable(); // position
            $table->string('qualification')->nullable();
            $table->text('bio')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('profile_image_path')->nullable();
            $table->string('company_logo')->nullable();
            $table->string('company_logo_path')->nullable();
            $table->string('location')->nullable();
            
            // Company Info
            $table->string('company_logo_text')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_registration_no')->nullable();
            $table->string('company_department')->nullable();
            
            // Address Details
            $table->string('address_name')->nullable();
            $table->string('address_street')->nullable();
            $table->string('address_area')->nullable();
            $table->string('address_city_state')->nullable();
            $table->string('address_country')->nullable();
            $table->text('address_map_url')->nullable();
            
            // Stats, Services, Social Links, Team Members (JSON)
            $table->json('stats')->nullable();
            $table->json('services')->nullable();
            $table->json('social_links')->nullable();
            $table->json('team_members')->nullable();
            
            // Contact Methods
            $table->string('phone_number')->nullable();
            $table->string('phone_label')->nullable();
            $table->string('email_address')->nullable();
            $table->string('email_label')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('whatsapp_label')->nullable();
            $table->text('website_url')->nullable();
            $table->string('website_label')->nullable();
            
            // Design Settings
            $table->string('profile_style')->default('classic');
            $table->string('theme')->default('minimal');
            $table->string('background_color')->default('#FFFFFF');
            $table->string('text_color')->default('#000000');
            $table->string('font')->default('inter');
            $table->string('button_style')->default('solid');
            $table->boolean('show_watermark')->default(true);
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_pages');
    }
};
