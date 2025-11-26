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
        Schema::create('profile_builder_sections', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique(); // e.g., 'profile', 'company', 'blog'
            $table->string('name', 100); // Display name
            $table->string('icon', 100)->default('heroicons:document-text');
            $table->string('category', 50)->default('general'); // 'general' or 'design'
            $table->text('description')->nullable();
            $table->integer('display_order')->default(0);
            $table->json('available_plans')->nullable(); // ['basic', 'premium', 'business']
            $table->boolean('has_fields')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        // Migrate existing sections from config to database
        $sections = config('profile_sections.sections', []);
        
        foreach ($sections as $key => $config) {
            \DB::table('profile_builder_sections')->insert([
                'key' => $key,
                'name' => $config['name'],
                'icon' => $config['icon'] ?? 'heroicons:document-text',
                'category' => $config['category'] ?? 'general',
                'description' => $config['description'] ?? '',
                'display_order' => $config['display_order'] ?? 999,
                'available_plans' => json_encode($config['available_plans'] ?? []),
                'has_fields' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_builder_sections');
    }
};
