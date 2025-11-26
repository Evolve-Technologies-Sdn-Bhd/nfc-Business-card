<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table stores custom field/feature assignments for individual Business users.
     * If a Business user has an entry here, it overrides the default plan settings.
     */
    public function up(): void
    {
        Schema::create('business_user_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Store custom assignments as JSON
            // Format: { "fields": [1, 2, 3...], "design_options": [1, 2, 3...], "features": ["contact_form", "vcard_download"...] }
            $table->json('enabled_fields')->nullable(); // Array of field IDs
            $table->json('enabled_design_options')->nullable(); // Array of design option IDs
            $table->json('enabled_features')->nullable(); // Array of feature option_ids
            
            $table->boolean('use_custom')->default(false); // If false, use default business plan settings
            $table->text('notes')->nullable(); // Admin notes about this user's customization
            
            $table->timestamps();
            
            // Each user can only have one assignment record
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_user_assignments');
    }
};
