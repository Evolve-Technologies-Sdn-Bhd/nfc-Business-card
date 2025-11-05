<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Check and add missing columns
            if (!Schema::hasColumn('profiles', 'profile_image_path')) {
                $table->string('profile_image_path')->nullable()->after('profile_image');
            }

            if (!Schema::hasColumn('profiles', 'company_logo_path')) {
                $table->string('company_logo_path')->nullable()->after('company_logo');
            }

            if (!Schema::hasColumn('profiles', 'profile_style')) {
                $table->string('profile_style', 50)->nullable()->default('classic')->after('button_style');
            }
        });
    }

    public function down()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['profile_image_path', 'company_logo_path', 'profile_style']);
        });
    }
};
