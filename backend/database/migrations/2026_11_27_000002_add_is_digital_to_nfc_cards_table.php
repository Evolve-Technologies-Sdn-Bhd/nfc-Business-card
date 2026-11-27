<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Digital (virtual) cards.
 *
 * A Free-plan account has no physical NFC card, but every profile in the system is
 * stored against an nfc_cards row (landing_pages.nfc_card_id). A digital card is
 * that row without anything to print or ship: it gives the Free user a working
 * profile, public link and QR code, and is excluded from orders, fulfilment,
 * entitlement and admin card statistics.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nfc_cards', function (Blueprint $table) {
            $table->boolean('is_digital')->default(false)->after('subscription_plan');
            $table->index(['user_id', 'is_digital']);
        });
    }

    public function down(): void
    {
        Schema::table('nfc_cards', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_digital']);
            $table->dropColumn('is_digital');
        });
    }
};
