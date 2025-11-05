// database/migrations/xxxx_create_profiles_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('company')->nullable();
            $table->text('bio')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('location')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('company_logo')->nullable();
            $table->enum('theme', ['minimal', 'modern', 'creative', 'professional', 'dark'])->default('minimal');
            $table->string('background_color')->default('#FFFFFF');
            $table->string('text_color')->default('#000000');
            $table->string('font')->default('inter');
            $table->string('button_style')->default('solid');
            $table->boolean('show_watermark')->default(true);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('profiles');
    }
};
