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
        Schema::table('profile_design_options', function (Blueprint $table) {
            // 添加计划权限字段
            $table->json('available_plans')->nullable()->after('is_default');
            // available_plans 示例: ["basic", "premium", "business"] 或 null (所有计划可用)
        });

        // 设置所有现有选项对所有计划可用
        DB::table('profile_design_options')->update([
            'available_plans' => json_encode(['basic', 'premium', 'business'])
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile_design_options', function (Blueprint $table) {
            $table->dropColumn('available_plans');
        });
    }
};
