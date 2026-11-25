<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Free-plan lifecycle support.
 *
 * A free account is granted a limited window (config payment.subscriptions.trial_days,
 * currently 14 days) written to users.subscription_end_date when the plan is selected
 * during onboarding. When that window ends and the user has not purchased an NFC card,
 * the free-plans:deactivate-expired job marks the account inactive and records the
 * moment here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'deactivated_at')) {
                $table->timestamp('deactivated_at')->nullable()->after('subscription_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'deactivated_at')) {
                $table->dropColumn('deactivated_at');
            }
        });
    }
};
