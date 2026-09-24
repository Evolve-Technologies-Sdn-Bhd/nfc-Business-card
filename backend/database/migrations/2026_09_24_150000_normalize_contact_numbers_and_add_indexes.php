<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Normalizes phone number columns (strips non-digit characters)
     * and adds performance indexes for phone-number-based lookups.
     */
    public function up(): void
    {
        // -------- Normalize existing contact_number values in nfc_cards --------
        DB::statement("
            UPDATE nfc_cards
            SET contact_number = REGEXP_REPLACE(COALESCE(contact_number, ''), '[^0-9]', '')
            WHERE contact_number IS NOT NULL AND contact_number <> ''
        ");

        // -------- Normalize phone numbers in users (for reference consistency) --------
        DB::statement("
            UPDATE users
            SET phone = REGEXP_REPLACE(COALESCE(phone, ''), '[^0-9]', '')
            WHERE phone IS NOT NULL AND phone <> ''
        ");

        // -------- Normalize phone numbers in landing_pages (for reference consistency) --------
        DB::statement("
            UPDATE landing_pages
            SET phone = REGEXP_REPLACE(COALESCE(phone, ''), '[^0-9]', '')
            WHERE phone IS NOT NULL AND phone <> ''
        ");
        DB::statement("
            UPDATE landing_pages
            SET phone_number = REGEXP_REPLACE(COALESCE(phone_number, ''), '[^0-9]', '')
            WHERE phone_number IS NOT NULL AND phone_number <> ''
        ");
        DB::statement("
            UPDATE landing_pages
            SET whatsapp_number = REGEXP_REPLACE(COALESCE(whatsapp_number, ''), '[^0-9]', '')
            WHERE whatsapp_number IS NOT NULL AND whatsapp_number <> ''
        ");
        DB::statement("
            UPDATE landing_pages
            SET company_whatsapp = REGEXP_REPLACE(COALESCE(company_whatsapp, ''), '[^0-9]', '')
            WHERE company_whatsapp IS NOT NULL AND company_whatsapp <> ''
        ");

        // -------- Add performance indexes for nfc_cards --------
        Schema::table('nfc_cards', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('nfc_cards');
            $existingIndexNames = array_keys($indexes);

            // Simple index on contact_number for fast phone-based lookups
            if (!in_array('nfc_cards_contact_number_index', $existingIndexNames)) {
                $table->index('contact_number', 'nfc_cards_contact_number_index');
            }

            // Composite index on (status, contact_number) for the conflict-resolution ordering
            if (!in_array('nfc_cards_status_contact_number_index', $existingIndexNames)) {
                $table->index(['status', 'contact_number'], 'nfc_cards_status_contact_number_index');
            }
        });

        // -------- Optional: index users.phone for quick cross-reference lookups --------
        Schema::table('users', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('users');
            $existingIndexNames = array_keys($indexes);

            if (!in_array('users_phone_index', $existingIndexNames)) {
                $table->index('phone', 'users_phone_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * NOTE: We cannot restore original formatting of phone numbers (stripped characters are lost).
     *       This only rolls back the added indexes. Data normalization is intentionally irreversible.
     */
    public function down(): void
    {
        Schema::table('nfc_cards', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('nfc_cards');
            $existingIndexNames = array_keys($indexes);

            if (in_array('nfc_cards_status_contact_number_index', $existingIndexNames)) {
                $table->dropIndex('nfc_cards_status_contact_number_index');
            }
            if (in_array('nfc_cards_contact_number_index', $existingIndexNames)) {
                $table->dropIndex('nfc_cards_contact_number_index');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('users');
            $existingIndexNames = array_keys($indexes);

            if (in_array('users_phone_index', $existingIndexNames)) {
                $table->dropIndex('users_phone_index');
            }
        });
    }
};
