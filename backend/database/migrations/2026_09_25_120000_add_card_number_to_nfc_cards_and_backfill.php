<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\NfcCard;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * PURPOSE (performance):
     *   Previously NfcCard->card_number was computed via a COUNT(...) query on
     *   EVERY card serialization (N+1 query). Adding a persisted column and
     *   assigning it at creation time eliminates all per-card DB queries.
     *   This migration also back-fills the column for all historical cards.
     */
    public function up(): void
    {
        Schema::table('nfc_cards', function (Blueprint $table) {
            $table->unsignedInteger('card_number')->nullable()->after('id');
            $table->index(['user_id', 'card_number'], 'nfc_cards_user_card_number_idx');
        });

        // Back-fill: for each user, order cards by created_at, id and assign
        // sequential 1-based card_number.
        // Chunked by user to keep memory bounded.
        $userIds = DB::table('nfc_cards')
            ->select('user_id')
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        foreach ($userIds as $userId) {
            $cards = DB::table('nfc_cards')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->select('id')
                ->get();

            $number = 1;
            foreach ($cards as $card) {
                DB::table('nfc_cards')
                    ->where('id', $card->id)
                    ->update(['card_number' => $number]);
                $number++;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nfc_cards', function (Blueprint $table) {
            $table->dropIndex('nfc_cards_user_card_number_idx');
            $table->dropColumn('card_number');
        });
    }
};
