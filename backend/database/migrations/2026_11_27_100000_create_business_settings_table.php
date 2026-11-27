<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Company-level settings for a Business tenant.
 *
 * The tenant itself stays modelled the way it already was (owner = Business user
 * with parent_business_id NULL; members point at the owner through
 * users.parent_business_id). This table adds what that model was missing: the
 * company data the owner shares with every member, and which of those profile
 * fields members are NOT allowed to change. The server enforces the locks on
 * every profile save (see App\Services\BusinessTenantService).
 *
 * Existing Business owners get no row until they first save their settings;
 * BusinessTenantService::settingsFor() falls back to "nothing shared, nothing
 * locked", which is exactly how the app behaved before.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->unique()->constrained('users')->cascadeOnDelete();

            // Landing-page column => value the company shares with its members.
            $table->json('shared_fields')->nullable();
            // Landing-page columns (and the pseudo key `card_template`) members cannot change.
            $table->json('locked_fields')->nullable();
            // Card template every member card is ordered with when `card_template` is locked.
            $table->foreignId('card_template_id')->nullable()->constrained('card_templates')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
