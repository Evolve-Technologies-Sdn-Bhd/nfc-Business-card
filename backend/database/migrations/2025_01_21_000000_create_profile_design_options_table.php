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
        Schema::create('profile_design_options', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['theme', 'font', 'button_style', 'profile_style'])->index();
            $table->string('option_id')->index(); // e.g., 'minimal', 'inter', 'solid'
            $table->string('name'); // Display name
            $table->json('config')->nullable(); // Additional configuration (colors, classes, etc.)
            $table->boolean('is_active')->default(true); // Can be enabled/disabled by admin
            $table->boolean('is_default')->default(false); // Default selection for new users
            $table->integer('display_order')->default(0); // Display order
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->unique(['type', 'option_id']);
        });
        
        // Insert default options
        DB::table('profile_design_options')->insert([
            // Themes
            [
                'type' => 'theme',
                'option_id' => 'minimal',
                'name' => 'Minimal',
                'config' => json_encode([
                    'backgroundColor' => '#FFFFFF',
                    'preview' => 'bg-white'
                ]),
                'is_active' => true,
                'is_default' => true,
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'theme',
                'option_id' => 'modern',
                'name' => 'Modern',
                'config' => json_encode([
                    'backgroundColor' => '#F9FAFB',
                    'preview' => 'bg-gradient-to-br from-gray-50 to-gray-100'
                ]),
                'is_active' => true,
                'is_default' => false,
                'display_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'theme',
                'option_id' => 'creative',
                'name' => 'Creative',
                'config' => json_encode([
                    'backgroundColor' => '#A855F7',
                    'preview' => 'bg-gradient-to-br from-purple-500 to-pink-500'
                ]),
                'is_active' => true,
                'is_default' => false,
                'display_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'theme',
                'option_id' => 'professional',
                'name' => 'Professional',
                'config' => json_encode([
                    'backgroundColor' => '#2563EB',
                    'preview' => 'bg-gradient-to-br from-blue-600 to-blue-700'
                ]),
                'is_active' => true,
                'is_default' => false,
                'display_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'theme',
                'option_id' => 'dark',
                'name' => 'Dark',
                'config' => json_encode([
                    'backgroundColor' => '#111827',
                    'preview' => 'bg-gray-900'
                ]),
                'is_active' => true,
                'is_default' => false,
                'display_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Fonts
            [
                'type' => 'font',
                'option_id' => 'inter',
                'name' => 'Inter',
                'config' => json_encode([
                    'family' => 'Inter, sans-serif'
                ]),
                'is_active' => true,
                'is_default' => true,
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'font',
                'option_id' => 'poppins',
                'name' => 'Poppins',
                'config' => json_encode([
                    'family' => 'Poppins, sans-serif'
                ]),
                'is_active' => true,
                'is_default' => false,
                'display_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'font',
                'option_id' => 'roboto',
                'name' => 'Roboto',
                'config' => json_encode([
                    'family' => 'Roboto, sans-serif'
                ]),
                'is_active' => true,
                'is_default' => false,
                'display_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'font',
                'option_id' => 'playfair',
                'name' => 'Playfair',
                'config' => json_encode([
                    'family' => 'Playfair Display, serif'
                ]),
                'is_active' => true,
                'is_default' => false,
                'display_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Button Styles
            [
                'type' => 'button_style',
                'option_id' => 'solid',
                'name' => 'Solid',
                'config' => json_encode([
                    'class' => 'bg-black text-white rounded-full'
                ]),
                'is_active' => true,
                'is_default' => true,
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'button_style',
                'option_id' => 'outline',
                'name' => 'Outline',
                'config' => json_encode([
                    'class' => 'border-2 border-black text-black rounded-full'
                ]),
                'is_active' => true,
                'is_default' => false,
                'display_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'button_style',
                'option_id' => 'soft',
                'name' => 'Soft',
                'config' => json_encode([
                    'class' => 'bg-gray-100 text-gray-900 rounded-xl'
                ]),
                'is_active' => true,
                'is_default' => false,
                'display_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'button_style',
                'option_id' => 'shadow',
                'name' => 'Shadow',
                'config' => json_encode([
                    'class' => 'bg-white text-black rounded-xl shadow-lg'
                ]),
                'is_active' => true,
                'is_default' => false,
                'display_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Profile Styles
            [
                'type' => 'profile_style',
                'option_id' => 'classic',
                'name' => 'Classic',
                'config' => json_encode([]),
                'is_active' => true,
                'is_default' => true,
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_design_options');
    }
};
