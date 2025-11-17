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
        // Add quota and business relationship fields to users table
        Schema::table('users', function (Blueprint $table) {
            // Business account quota fields
            $table->integer('total_account_slots')->default(0)->after('subscription_plan');
            $table->integer('total_card_quota')->default(0)->after('total_account_slots');
            
            // Parent business account relationship (for employees)
            $table->foreignId('parent_business_id')->nullable()->after('total_card_quota')
                ->constrained('users')->onDelete('cascade');
        });

        // Add business account reference to nfc_cards table
        Schema::table('nfc_cards', function (Blueprint $table) {
            // Business account that owns this card (for quota tracking)
            $table->foreignId('business_account_id')->nullable()->after('user_id')
                ->constrained('users')->onDelete('set null');
            
            $table->index(['business_account_id', 'subscription_plan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nfc_cards', function (Blueprint $table) {
            $table->dropForeign(['business_account_id']);
            $table->dropIndex(['business_account_id', 'subscription_plan']);
            $table->dropColumn('business_account_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['parent_business_id']);
            $table->dropColumn(['total_account_slots', 'total_card_quota', 'parent_business_id']);
        });
    }
};
