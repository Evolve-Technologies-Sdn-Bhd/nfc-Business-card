<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 修改 type 列为 string，允许自定义类型
        Schema::table('profile_design_options', function (Blueprint $table) {
            // 先修改列类型
            $table->string('type', 100)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 恢复为 enum（如果需要）
        DB::statement("ALTER TABLE profile_design_options MODIFY type ENUM('theme', 'font', 'button_style', 'profile_style') NOT NULL");
    }
};
