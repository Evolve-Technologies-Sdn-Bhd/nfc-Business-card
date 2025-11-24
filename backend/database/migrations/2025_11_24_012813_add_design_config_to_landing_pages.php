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
        Schema::table('landing_pages', function (Blueprint $table) {
            // 存储完整的 design options 配置 (theme, font, button_style 等的完整 config)
            $table->json('design_config')->nullable()->after('show_watermark');
            
            // 存储用户 plan 可见的字段列表
            $table->json('visible_fields')->nullable()->after('design_config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            //
        });
    }
};
