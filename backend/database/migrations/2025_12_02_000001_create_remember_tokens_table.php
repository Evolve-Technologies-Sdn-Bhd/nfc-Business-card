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
        Schema::create('remember_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('token_hash', 64)->unique(); // SHA-256 hash of the token
            $table->timestamp('expires_at');
            $table->timestamp('last_used_at')->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'expires_at']);
            $table->index('token_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remember_tokens');
    }
};
