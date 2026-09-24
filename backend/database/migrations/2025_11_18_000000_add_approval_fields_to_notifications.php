<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $cols = [
                'is_approved' => fn() => $table->boolean('is_approved')->default(false)->after('is_read'),
                'is_rejected' => fn() => $table->boolean('is_rejected')->default(false)->after('is_approved'),
                'rejection_reason' => fn() => $table->text('rejection_reason')->nullable()->after('is_rejected'),
                'approved_at' => fn() => $table->timestamp('approved_at')->nullable()->after('rejection_reason'),
                'rejected_at' => fn() => $table->timestamp('rejected_at')->nullable()->after('approved_at'),
            ];
            foreach ($cols as $name => $fn) {
                if (!Schema::hasColumn('notifications', $name)) {
                    try {
                        $fn->__invoke();
                    } catch (\Exception $e) {
                    }
                }
            }

            if (!Schema::hasColumn('notifications', 'approved_by')) {
                try {
                    $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->after('rejected_at');
                } catch (\Exception $e) {
                    if (DB::getDriverName() !== 'sqlite') {
                        throw $e;
                    }
                    try {
                        $table->unsignedBigInteger('approved_by')->nullable()->after('rejected_at');
                    } catch (\Exception $e2) {
                    }
                }
            }

            if (!Schema::hasColumn('notifications', 'rejected_by')) {
                try {
                    $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null')->after('approved_by');
                } catch (\Exception $e) {
                    if (DB::getDriverName() !== 'sqlite') {
                        throw $e;
                    }
                    try {
                        $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_by');
                    } catch (\Exception $e2) {
                    }
                }
            }

            if (!Schema::hasColumn('notifications', 'sticky')) {
                try {
                    $table->boolean('sticky')->default(false)->after('rejected_by');
                } catch (\Exception $e) {
                }
            }

            if (DB::getDriverName() !== 'sqlite' && Schema::hasColumn('notifications', 'type')) {
                try {
                    $table->dropColumn('type');
                } catch (\Exception $e) {
                }
            }
        });

        if (DB::getDriverName() !== 'sqlite' && !Schema::hasColumn('notifications', 'type')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->enum('type', [
                    'registration_success',
                    'email_verification',
                    'login_new_device',
                    'profile_updated',
                    'link_milestone',
                    'landing_page_viewed',
                    'contact_request',
                    'subscription_upgrade',
                    'payment_successful',
                    'payment_failed',
                    'account_warning',
                    'password_changed',
                    'nfc_card_purchased',
                    'nfc_card_delivered',
                    'nfc_card_linked',
                    'nfc_card_activated',
                    'nfc_card_expired',
                    'app_update',
                    'system_message',
                    'admin_announcement',
                    'business_card_order_request',
                    'business_bulk_order_placed'
                ])->after('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            try {
                if (DB::getDriverName() === 'sqlite') {
                    foreach (['approved_by', 'rejected_by'] as $col) {
                        if (Schema::hasColumn('notifications', $col)) {
                            try {
                                $table->dropColumn($col);
                            } catch (\Exception $e) {
                            }
                        }
                    }
                } else {
                    try {
                        $table->dropForeignKeyIfExists(['approved_by']);
                        $table->dropForeignKeyIfExists(['rejected_by']);
                    } catch (\Exception $e) {
                    }
                }
                $cols = ['is_approved', 'is_rejected', 'rejection_reason', 'approved_at', 'rejected_at', 'sticky'];
                foreach ($cols as $c) {
                    if (Schema::hasColumn('notifications', $c)) {
                        try {
                            $table->dropColumn($c);
                        } catch (\Exception $e) {
                        }
                    }
                }
            } catch (\Exception $e) {
            }
        });
    }
};
