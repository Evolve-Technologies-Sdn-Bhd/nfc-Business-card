<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user email notification preferences.
 *
 * Previously the Settings page toggles were hardcoded in SettingsController and
 * the PUT /settings/notifications endpoint did not exist, so every toggle change
 * was silently discarded. This table gives those preferences a real home.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('profile_views')->default(true);
            $table->boolean('nfc_taps')->default(true);
            $table->boolean('weekly_reports')->default(true);
            $table->boolean('product_updates')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notification_preferences');
    }
};
