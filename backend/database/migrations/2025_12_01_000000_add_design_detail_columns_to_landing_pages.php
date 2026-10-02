<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            if (!Schema::hasColumn('landing_pages', 'accent_color')) {
                $table->string('accent_color', 20)->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'bg_gradient')) {
                $table->text('bg_gradient')->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'card_style')) {
                $table->string('card_style', 50)->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'bg_animation')) {
                $table->string('bg_animation', 50)->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'card_effect_color')) {
                $table->string('card_effect_color', 20)->nullable();
            }
            if (!Schema::hasColumn('landing_pages', 'card_effect_opacity')) {
                $table->decimal('card_effect_opacity', 4, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $columns = [
                'accent_color', 'bg_gradient', 'card_style',
                'bg_animation', 'card_effect_color', 'card_effect_opacity',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('landing_pages', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
