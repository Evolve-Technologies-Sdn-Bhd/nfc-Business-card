<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_builder_fields', function (Blueprint $table) {
            $table->id();
            $table->string('tab', 50); // profile, company, services, links
            $table->string('field_key', 100)->unique();
            $table->string('field_type', 50); // text, email, tel, url, textarea, number, image, repeater
            $table->string('label');
            $table->text('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->json('validation_rules')->nullable();
            $table->json('available_plans')->nullable();
            $table->integer('display_order')->default(0);
            $table->json('config')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_builder_fields');
    }
};
