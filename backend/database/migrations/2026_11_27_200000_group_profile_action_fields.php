<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The public-profile action switches (vCard, QR code, contact form) and the
 * introduction video had no field_group, so every builder showed them in a
 * catch-all "Other" card. Give them proper groups (mirrors
 * UpdateFieldGroupsSeeder) and plain-language help text.
 */
return new class extends Migration
{
    private const FIELDS = [
        'introVideo' => ['About Me', 'heroicons:document-text', 2, 41, null],
        'vcardEnabled' => ['Sharing & Contact', 'heroicons:share', 5, 94, 'Visitors can save your details straight to their phone contacts.'],
        'qrCodeEnabled' => ['Sharing & Contact', 'heroicons:share', 5, 95, 'Shows a QR code visitors can scan to open your profile.'],
        'contactFormEnabled' => ['Sharing & Contact', 'heroicons:share', 5, 96, 'Visitors can send you a message from your profile.'],
        'contactFormEmail' => ['Sharing & Contact', 'heroicons:share', 5, 97, 'Where messages are sent. Leave empty to use your account e-mail.'],
    ];

    public function up(): void
    {
        foreach (self::FIELDS as $key => [$group, $icon, $groupOrder, $order, $help]) {
            $update = [
                'field_group' => $group,
                'field_group_icon' => $icon,
                'group_order' => $groupOrder,
                'display_order' => $order,
            ];
            if ($help !== null) {
                $update['help_text'] = $help;
            }

            DB::table('profile_builder_fields')->where('field_key', $key)->update($update);
        }
    }

    public function down(): void
    {
        DB::table('profile_builder_fields')
            ->whereIn('field_key', array_keys(self::FIELDS))
            ->update(['field_group' => null, 'field_group_icon' => null, 'group_order' => null]);
    }
};
