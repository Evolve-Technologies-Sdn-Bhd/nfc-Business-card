<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds the layout_designer feature toggle to profile_design_options table.
     * This allows admin to control which plans can customize their landing page layout.
     */
    public function up(): void
    {
        // Add the layout_designer feature toggle
        DB::table('profile_design_options')->insert([
            'type' => 'feature_toggle',
            'option_id' => 'layout_designer',
            'name' => 'Layout Designer',
            'description' => 'Allow users to customize the order and visibility of sections on their landing page',
            'config' => json_encode([
                'icon' => 'heroicons:squares-2x2',
                'category' => 'customization',
            ]),
            'is_active' => true,
            'is_default' => false,
            'available_plans' => json_encode(['business', 'premium', 'enterprise']),
            'display_order' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('profile_design_options')
            ->where('type', 'feature_toggle')
            ->where('option_id', 'layout_designer')
            ->delete();
    }
};
