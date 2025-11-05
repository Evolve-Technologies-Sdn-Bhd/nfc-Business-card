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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('settings');
            $table->string('admin_role')->nullable()->after('is_admin'); // super_admin, admin, moderator
            $table->json('admin_permissions')->nullable()->after('admin_role');
            $table->timestamp('last_admin_action_at')->nullable()->after('admin_permissions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_admin',
                'admin_role',
                'admin_permissions',
                'last_admin_action_at'
            ]);
        });
    }
};
