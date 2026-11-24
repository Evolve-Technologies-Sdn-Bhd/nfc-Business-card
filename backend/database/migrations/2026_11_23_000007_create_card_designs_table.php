<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_designs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Physical size of the card artwork, e.g. "90x54" (mm, landscape).
            $table->string('card_size')->default('90x54');
            // Whether the exported PDF should include a 3mm bleed area.
            $table->boolean('bleed')->default(false);
            $table->string('background_front_url')->nullable();
            $table->string('background_back_url')->nullable();
            // Array of placed elements (text/image/qr/rect) with position in mm.
            $table->json('front_elements')->nullable();
            $table->json('back_elements')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_designs');
    }
};
