<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('card_templates', function (Blueprint $table) {
            // Card size the layout was authored against, e.g. "90x54" (mm).
            $table->string('card_size')->nullable()->after('back_image_url');
            // Data-bound elements placed in the card designer: [{field, x, y, w, h, ...}] in mm.
            // When null the preview falls back to the default fixed layout.
            $table->json('front_layout')->nullable()->after('card_size');
            $table->json('back_layout')->nullable()->after('front_layout');
        });
    }

    public function down(): void
    {
        Schema::table('card_templates', function (Blueprint $table) {
            $table->dropColumn(['card_size', 'front_layout', 'back_layout']);
        });
    }
};
