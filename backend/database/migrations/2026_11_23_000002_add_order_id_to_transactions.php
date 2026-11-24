<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add orders.order_id link on transactions.
     *
     * Kept in a SEPARATE migration (mirroring add_nfc_card_id_to_transactions)
     * so existing transaction rows are left completely untouched — the new
     * column is nullable and simply stays NULL for historical rows.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->index()->after('subscription_id');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('order_id');
        });
    }
};
