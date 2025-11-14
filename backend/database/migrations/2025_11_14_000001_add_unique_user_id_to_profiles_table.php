<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds a UNIQUE constraint to user_id in profiles table
     * to ensure one user can only have ONE profile (1:1 relationship)
     */
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Add unique constraint to user_id
            // This ensures: 1 User = 1 Profile (One-to-One relationship)
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Drop the unique constraint
            $table->dropUnique(['user_id']);
        });
    }
};
