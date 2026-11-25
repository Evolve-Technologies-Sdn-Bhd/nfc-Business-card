<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a delegated-role column to Business tenant employees.
 *
 * The Business account owner (users.subscription_plan = 'business' with
 * parent_business_id = NULL) is implicitly the OWNER. Employees may be granted
 * one of the assignable roles so the owner can delegate management without
 * sharing the account credentials.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'business_role')) {
                $table->enum('business_role', ['owner', 'admin', 'card_manager', 'member'])
                    ->nullable()
                    ->after('parent_business_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'business_role')) {
                $table->dropColumn('business_role');
            }
        });
    }
};
