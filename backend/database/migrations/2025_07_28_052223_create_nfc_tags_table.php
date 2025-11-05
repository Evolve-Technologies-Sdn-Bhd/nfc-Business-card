// database/migrations/xxxx_create_nfc_tags_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('nfc_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nfc_id')->unique();
            $table->string('name')->default('Business Card');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('tap_count')->default(0);
            $table->timestamp('last_tapped_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('nfc_tags');
    }
};
