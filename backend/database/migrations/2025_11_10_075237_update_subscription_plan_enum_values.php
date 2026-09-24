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
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN subscription_plan ENUM('free', 'basic', 'premium', 'business') DEFAULT 'free'");
        
        DB::statement("ALTER TABLE nfc_cards MODIFY COLUMN subscription_plan ENUM('free', 'basic', 'premium', 'business') DEFAULT 'free'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN subscription_plan ENUM('free', 'basic', 'premium', 'business') DEFAULT 'free'");
        DB::statement("ALTER TABLE nfc_cards MODIFY COLUMN subscription_plan ENUM('free', 'basic', 'premium', 'business') DEFAULT 'free'");
    }
};
