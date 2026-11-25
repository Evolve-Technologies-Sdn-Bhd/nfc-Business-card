<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permanent ledger of every free trial that has ever been claimed.
 *
 * This table is the anti-abuse counterpart to the 14-day free account: it lets
 * the app answer "has this person already used their one free trial?" even
 * AFTER the original account has been deleted. It therefore stores a NORMALISED
 * email hash (gmail dots / "+tag" aliases merged) plus the IP and device
 * fingerprint of the claim, and does NOT cascade-delete with the user.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('free_trial_claims')) {
            return;
        }

        Schema::create('free_trial_claims', function (Blueprint $table) {
            $table->id();

            // Kept for support/audit only — deliberately nullable and NOT a
            // foreign key: the ledger must outlive the account it belongs to.
            $table->unsignedBigInteger('user_id')->nullable()->index();

            $table->string('email');
            $table->string('email_normalized');
            $table->string('email_hash', 64)->index();

            $table->string('ip_address', 45)->nullable()->index();
            $table->string('ip_subnet', 45)->nullable();
            $table->string('device_id', 64)->nullable()->index();
            $table->text('user_agent')->nullable();

            $table->timestamp('claimed_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('free_trial_claims');
    }
};
