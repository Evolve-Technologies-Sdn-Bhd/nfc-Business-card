<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nfc_cards', function (Blueprint $table) {
            // Frozen copy of what the customer ordered: template artwork, the
            // designer layout (field positions) and the customer's own details.
            // Production prints from this, so later template edits never change
            // a card that has already been bought.
            $table->json('card_design')->nullable()->after('design_notes');
        });
    }

    public function down(): void
    {
        Schema::table('nfc_cards', function (Blueprint $table) {
            $table->dropColumn('card_design');
        });
    }
};
