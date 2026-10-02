<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // name_slug is NOT unique. Multiple users who share the same display
            // name can and will share the same slug. Disambiguation happens at the
            // URL level via an optional "/userid-{id}" suffix in the 3rd path segment.
            $table->string('name_slug', 255)->nullable()->after('email');
            $table->index('name_slug');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['name_slug']);
            $table->dropColumn('name_slug');
        });
    }
};
