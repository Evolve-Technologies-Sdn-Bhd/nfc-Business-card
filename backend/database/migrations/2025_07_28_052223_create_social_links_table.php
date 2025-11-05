// database/migrations/xxxx_create_social_links_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained()->onDelete('cascade');
            $table->string('platform');
            $table->string('url');
            $table->string('title');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('click_count')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('social_links');
    }
};
