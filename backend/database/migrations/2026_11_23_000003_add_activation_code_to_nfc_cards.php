<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add a unique, human-friendly activation_code to nfc_cards.
     *
     * The code proves ownership when a user CLAIMS/ACTIVATES a card that is
     * not already assigned to their account. Alphabet deliberately excludes
     * visually ambiguous characters 0 / O / 1 / I.
     */
    public function up(): void
    {
        Schema::table('nfc_cards', function (Blueprint $table) {
            if (!Schema::hasColumn('nfc_cards', 'activation_code')) {
                $table->string('activation_code', 12)->nullable()->unique()->after('nfc_card_id');
            }
        });

        // Back-fill existing rows with unique codes.
        $alphabet = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $length = 10;
        $alphabetLength = strlen($alphabet);

        $used = DB::table('nfc_cards')
            ->whereNotNull('activation_code')
            ->pluck('activation_code')
            ->all();
        $used = array_flip($used);

        $cards = DB::table('nfc_cards')
            ->whereNull('activation_code')
            ->select('id')
            ->get();

        foreach ($cards as $card) {
            do {
                $code = '';
                for ($i = 0; $i < $length; $i++) {
                    $code .= $alphabet[random_int(0, $alphabetLength - 1)];
                }
            } while (isset($used[$code]));

            $used[$code] = true;

            DB::table('nfc_cards')
                ->where('id', $card->id)
                ->update(['activation_code' => $code]);
        }
    }

    public function down(): void
    {
        Schema::table('nfc_cards', function (Blueprint $table) {
            if (Schema::hasColumn('nfc_cards', 'activation_code')) {
                $table->dropUnique(['activation_code']);
                $table->dropColumn('activation_code');
            }
        });
    }
};
