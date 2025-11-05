<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('token_hash', 64)->unique();
            $table->boolean('used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->string('request_ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('used_ip', 45)->nullable();
            $table->timestamps();

            $table->index(['token_hash', 'used', 'expires_at']);
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('password_resets');
    }
};