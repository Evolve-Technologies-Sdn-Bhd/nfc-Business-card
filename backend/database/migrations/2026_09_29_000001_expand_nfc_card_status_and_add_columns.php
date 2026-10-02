<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::table('nfc_cards', function (Blueprint $table) {
            if (!Schema::hasColumn('nfc_cards', 'transaction_id')) {
                $table->unsignedBigInteger('transaction_id')->nullable()->index()->after('nfc_card_id');
            }
            if (!Schema::hasColumn('nfc_cards', 'order_confirmed_at')) {
                $table->timestamp('order_confirmed_at')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('nfc_cards', 'user_received_confirmed_at')) {
                $table->timestamp('user_received_confirmed_at')->nullable()->after('order_confirmed_at');
            }
            if (!Schema::hasColumn('nfc_cards', 'courier')) {
                $table->string('courier')->nullable()->after('tracking_number');
            }
            if (!Schema::hasColumn('nfc_cards', 'cancelled_by')) {
                $table->unsignedBigInteger('cancelled_by')->nullable()->index()->after('courier');
            }
            if (!Schema::hasColumn('nfc_cards', 'cancelled_reason')) {
                $table->text('cancelled_reason')->nullable()->after('cancelled_by');
            }
        });

        $allStatuses = [
            'pending_payment',
            'awaiting_payment_verification',
            'payment_verified',
            'processing',
            'shipped',
            'delivered',
            'active',
            'inactive',
            'expired',
            'cancelled',
            'replacement'
        ];

        if ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS nfc_cards_status_assigned_at_index');
            DB::statement('DROP INDEX IF EXISTS analytics_event_type_created_at_index');
            DB::statement('DROP INDEX IF EXISTS nfc_cards_status_contact_number_index');
            DB::statement('DROP INDEX IF EXISTS nfc_cards_user_id_status_index');

            $newCols = [
                'id INTEGER PRIMARY KEY AUTOINCREMENT',
                'user_id INTEGER NOT NULL',
                'business_account_id INTEGER',
                'card_id VARCHAR NOT NULL',
                'card_number INTEGER',
                'nfc_card_id VARCHAR',
                'transaction_id INTEGER',
                'card_owner VARCHAR NOT NULL',
                'billing_address TEXT NOT NULL',
                'contact_number VARCHAR NOT NULL',
                'purchase_date DATE NOT NULL',
                'expiry_date DATE',
                'status TEXT NOT NULL DEFAULT \'pending_payment\'',
                'subscription_plan VARCHAR NOT NULL DEFAULT \'free\'',
                'purchase_amount NUMERIC NOT NULL DEFAULT 0',
                'payment_method VARCHAR',
                'shipping_address VARCHAR',
                'tracking_number VARCHAR',
                'courier VARCHAR',
                'shipped_date DATE',
                'delivered_date DATE',
                'order_confirmed_at DATETIME',
                'user_received_confirmed_at DATETIME',
                'cancelled_by INTEGER',
                'cancelled_reason TEXT',
                'notes TEXT',
                'created_at DATETIME',
                'updated_at DATETIME',
            ];
            $copyCols = 'id, user_id, business_account_id, card_id, card_number, nfc_card_id, transaction_id, card_owner, billing_address, contact_number, purchase_date, expiry_date, status, subscription_plan, purchase_amount, payment_method, shipping_address, tracking_number, courier, shipped_date, delivered_date, order_confirmed_at, user_received_confirmed_at, cancelled_by, cancelled_reason, notes, created_at, updated_at';

            DB::statement('PRAGMA foreign_keys = OFF');
            DB::beginTransaction();
            try {
                DB::statement('CREATE TABLE nfc_cards_new (' . implode(', ', $newCols) . ')');
                DB::statement("INSERT INTO nfc_cards_new ($copyCols) SELECT $copyCols FROM nfc_cards");
                DB::statement('DROP TABLE nfc_cards');
                DB::statement('ALTER TABLE nfc_cards_new RENAME TO nfc_cards');
                DB::statement('CREATE UNIQUE INDEX nfc_cards_card_id_unique ON nfc_cards (card_id)');
                DB::statement('CREATE UNIQUE INDEX nfc_cards_nfc_card_id_unique ON nfc_cards (nfc_card_id)');
                DB::statement('CREATE INDEX nfc_cards_nfc_card_id_index ON nfc_cards (nfc_card_id)');
                DB::statement('CREATE INDEX nfc_cards_user_id_status_index ON nfc_cards (user_id, status)');
                DB::statement('CREATE INDEX nfc_cards_subscription_plan_index ON nfc_cards (subscription_plan)');
                DB::statement('CREATE INDEX nfc_cards_business_account_id_subscription_plan_index ON nfc_cards (business_account_id, subscription_plan)');
                DB::statement('CREATE INDEX nfc_cards_contact_number_index ON nfc_cards (contact_number)');
                DB::statement('CREATE INDEX nfc_cards_status_contact_number_index ON nfc_cards (status, contact_number)');
                DB::statement('CREATE INDEX nfc_cards_cancelled_by_index ON nfc_cards (cancelled_by)');
                DB::statement('CREATE INDEX nfc_cards_transaction_id_index ON nfc_cards (transaction_id)');
                DB::statement('CREATE INDEX nfc_cards_user_card_number_idx ON nfc_cards (user_id, card_number)');
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                DB::statement('PRAGMA foreign_keys = ON');
                throw $e;
            }
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            $statusList = implode("','", $allStatuses);
            DB::statement("ALTER TABLE nfc_cards MODIFY COLUMN status ENUM('{$statusList}') NOT NULL DEFAULT 'active'");
        }
    }

    public function down(): void
    {
        Schema::table('nfc_cards', function (Blueprint $table) {
            $table->dropColumn([
                'transaction_id',
                'order_confirmed_at',
                'user_received_confirmed_at',
                'courier',
                'cancelled_by',
                'cancelled_reason'
            ]);
        });

        $driver = Schema::getConnection()->getDriverName();
        $legacyStatuses = ['active', 'inactive', 'expired', 'replacement'];

        if ($driver === 'sqlite') {
            DB::statement('ALTER TABLE nfc_cards RENAME COLUMN status TO status_old');

            Schema::table('nfc_cards', function (Blueprint $table) use ($legacyStatuses) {
                $table->enum('status', $legacyStatuses)->default('active')->after('status_old');
            });

            DB::table('nfc_cards')
                ->whereIn('status_old', $legacyStatuses)
                ->update(['status' => DB::raw('status_old')]);

            DB::table('nfc_cards')
                ->whereNotIn('status_old', $legacyStatuses)
                ->update(['status' => 'inactive']);

            Schema::table('nfc_cards', function (Blueprint $table) {
                $table->dropColumn('status_old');
            });
        } else {
            $statusList = implode("','", $legacyStatuses);
            DB::statement("ALTER TABLE nfc_cards MODIFY COLUMN status ENUM('{$statusList}') NOT NULL DEFAULT 'active'");
        }
    }
};
