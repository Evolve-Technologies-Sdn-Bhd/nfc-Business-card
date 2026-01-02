<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->string('front_image_url')->nullable();
            $table->string('back_image_url')->nullable();
            $table->string('original_front_url')->nullable(); // Original uploaded image
            $table->string('original_back_url')->nullable();
            $table->string('category')->default('business'); // business, personal, creative
            $table->json('plan_types')->nullable(); // ['free', 'basic', 'premium', 'business']
            $table->json('color_scheme')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_hidden')->default(false); // Admin can hide without deleting
            $table->integer('sort_order')->default(0);
            $table->string('processing_status')->default('pending'); // pending, processing, completed, failed
            $table->text('processing_error')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_templates');
    }
};
