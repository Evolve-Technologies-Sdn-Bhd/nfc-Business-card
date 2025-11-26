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
        Schema::table('profile_builder_fields', function (Blueprint $table) {
            $table->string('field_group')->nullable()->after('tab');
            $table->string('field_group_icon')->nullable()->after('field_group');
            $table->integer('group_order')->default(0)->after('field_group_icon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile_builder_fields', function (Blueprint $table) {
            $table->dropColumn(['field_group', 'field_group_icon', 'group_order']);
        });
    }
};
