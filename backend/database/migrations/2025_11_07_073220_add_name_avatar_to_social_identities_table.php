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
        Schema::table('social_identities', function (Blueprint $table) {
            if (!Schema::hasColumn('social_identities', 'name')) {
                $table->string('name')->nullable()->after('email');
            }
            if (!Schema::hasColumn('social_identities', 'avatar')) {
                $table->string('avatar')->nullable()->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('social_identities', function (Blueprint $table) {
            $table->dropColumn(['name', 'avatar']);
        });
    }
};
