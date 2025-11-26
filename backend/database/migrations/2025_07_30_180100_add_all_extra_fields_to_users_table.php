<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for all users extra fields
 * Merged from 7 separate migrations
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Subscription fields
            if (!Schema::hasColumn('users', 'subscription_plan')) {
                $table->enum('subscription_plan', ['free', 'basic', 'premium', 'business'])->default('free');
            }
            if (!Schema::hasColumn('users', 'subscription_start_date')) {
                $table->date('subscription_start_date')->nullable();
            }
            if (!Schema::hasColumn('users', 'subscription_end_date')) {
                $table->date('subscription_end_date')->nullable();
            }
            if (!Schema::hasColumn('users', 'subscription_active')) {
                $table->boolean('subscription_active')->default(false);
            }
            if (!Schema::hasColumn('users', 'stripe_customer_id')) {
                $table->string('stripe_customer_id')->nullable();
            }
            if (!Schema::hasColumn('users', 'stripe_subscription_id')) {
                $table->string('stripe_subscription_id')->nullable();
            }
            
            // Settings
            if (!Schema::hasColumn('users', 'settings')) {
                $table->json('settings')->nullable();
            }
            
            // Admin fields
            if (!Schema::hasColumn('users', 'is_admin')) {
                $table->boolean('is_admin')->default(false);
            }
            if (!Schema::hasColumn('users', 'admin_role')) {
                $table->string('admin_role')->nullable();
            }
            if (!Schema::hasColumn('users', 'admin_permissions')) {
                $table->json('admin_permissions')->nullable();
            }
            if (!Schema::hasColumn('users', 'last_admin_action_at')) {
                $table->timestamp('last_admin_action_at')->nullable();
            }
            
            // OAuth fields
            if (!Schema::hasColumn('users', 'provider')) {
                $table->string('provider')->nullable();
            }
            if (!Schema::hasColumn('users', 'provider_id')) {
                $table->string('provider_id')->nullable();
            }
            
            // Other fields
            if (!Schema::hasColumn('users', 'is_new_user')) {
                $table->boolean('is_new_user')->default(false);
            }
            if (!Schema::hasColumn('users', 'account_image')) {
                $table->string('account_image')->nullable();
            }
            if (!Schema::hasColumn('users', 'last_login_device')) {
                $table->text('last_login_device')->nullable();
            }
        });
        
        // Add index for OAuth (if not exists)
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['provider', 'provider_id'], 'users_provider_provider_id_index');
            });
        } catch (\Exception $e) {
            // Index already exists
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop index first
            try {
                $table->dropIndex('users_provider_provider_id_index');
            } catch (\Exception $e) {}
            
            $columns = [
                'subscription_plan', 'subscription_start_date', 'subscription_end_date',
                'subscription_active', 'stripe_customer_id', 'stripe_subscription_id',
                'settings', 'is_admin', 'admin_role', 'admin_permissions', 'last_admin_action_at',
                'provider', 'provider_id', 'is_new_user', 'account_image', 'last_login_device'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
