<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('subscription_plan', ['free', 'basic', 'premium', 'business'])->default('free')->after('plan');
            $table->date('subscription_start_date')->nullable()->after('subscription_plan');
            $table->date('subscription_end_date')->nullable()->after('subscription_start_date');
            $table->boolean('subscription_active')->default(false)->after('subscription_end_date');
            $table->string('stripe_customer_id')->nullable()->after('subscription_active');
            $table->string('stripe_subscription_id')->nullable()->after('stripe_customer_id');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_plan',
                'subscription_start_date',
                'subscription_end_date',
                'subscription_active',
                'stripe_customer_id',
                'stripe_subscription_id'
            ]);
        });
    }
}; 