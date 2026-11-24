<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Grandfather existing accounts into "email verified".
 *
 * Email verification enforcement is introduced via
 * EnsureEmailIsVerifiedWithGrace, which blocks unverified accounts once their
 * grace period lapses. Every account that already exists predates this feature,
 * so it must never be locked out: backfill email_verified_at for any user that
 * has not yet verified.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update([
                'email_verified_at' => DB::raw('COALESCE(created_at, CURRENT_TIMESTAMP)'),
            ]);
    }

    public function down(): void
    {
        // Irreversible: we cannot distinguish originally-verified from
        // grandfathered accounts, and clearing them would lock users out.
    }
};
