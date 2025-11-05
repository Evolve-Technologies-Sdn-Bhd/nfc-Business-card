// database/migrations/xxxx_create_analytics_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('analytics', function (Blueprint $table) {
            $table->id();
            $table->morphs('trackable'); // Can track profiles or nfc_tags
            $table->string('action'); // profile_view, nfc_tap, link_click, etc.
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('platform')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('referrer')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('analytics');
    }
};
