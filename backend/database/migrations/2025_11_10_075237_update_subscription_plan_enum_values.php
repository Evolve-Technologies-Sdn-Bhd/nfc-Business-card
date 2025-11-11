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
        // Update users table subscription_plan enum
        DB::statement("ALTER TABLE users MODIFY COLUMN subscription_plan ENUM('free', 'basic', 'premium', 'business') DEFAULT 'free'");
        
        // Update nfc_cards table subscription_plan enum
        DB::statement("ALTER TABLE nfc_cards MODIFY COLUMN subscription_plan ENUM('free', 'basic', 'premium', 'business') DEFAULT 'free'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE users MODIFY COLUMN subscription_plan ENUM('free', 'basic', 'premium', 'business') DEFAULT 'free'");
        DB::statement("ALTER TABLE nfc_cards MODIFY COLUMN subscription_plan ENUM('free', 'basic', 'premium', 'business') DEFAULT 'free'");
    }
};
