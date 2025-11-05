<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('nfc_tags', function (Blueprint $table) {
            $table->string('nfc_card_id')->nullable()->after('nfc_id');
            $table->foreign('nfc_card_id')->references('nfc_card_id')->on('nfc_cards')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('nfc_tags', function (Blueprint $table) {
            $table->dropForeign(['nfc_card_id']);
            $table->dropColumn('nfc_card_id');
        });
    }
}; 