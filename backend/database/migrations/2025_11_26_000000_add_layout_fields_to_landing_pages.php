<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds section and field layout configuration fields to allow users
     * to customize the order and visibility of sections/fields on their landing page.
     */
    public function up(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            // Section layout - stores order and visibility of sections
            // Format: [{ id: 'hero', order: 0, enabled: true }, { id: 'about', order: 1, enabled: true }, ...]
            $table->json('section_layout')->nullable();
            
            // Field layout - stores order of fields within each section
            // Format: { hero: ['name', 'position', 'tagline'], about: ['bio', 'stats'], ... }
            $table->json('field_layout')->nullable();
            
            // Section settings - additional settings per section
            // Format: { hero: { showParticles: true, animation: 'fade' }, ... }
            $table->json('section_settings')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->dropColumn(['section_layout', 'field_layout', 'section_settings']);
        });
    }
};
