<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Introduction Video (Profile Builder → About Me). The field existed in the
 * builder but had nowhere to be stored, so it was lost on save.
 *
 * Also switch it to the `video` field type (the YouTube/Vimeo input with a
 * preview), the same input Company Video uses.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('landing_pages', 'intro_video')) {
            Schema::table('landing_pages', function (Blueprint $table) {
                $table->string('intro_video', 500)->nullable()->after('bio');
            });
        }

        DB::table('profile_builder_fields')
            ->where('field_key', 'introVideo')
            ->update([
                'field_type' => 'video',
                'help_text' => 'A short YouTube or Vimeo video, shown under your About Me.',
            ]);

        DB::table('profile_builder_fields')
            ->where('field_key', 'companyVideo')
            ->whereNull('help_text')
            ->update(['help_text' => 'A YouTube or Vimeo video about your company.']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('landing_pages', 'intro_video')) {
            Schema::table('landing_pages', function (Blueprint $table) {
                $table->dropColumn('intro_video');
            });
        }

        DB::table('profile_builder_fields')
            ->where('field_key', 'introVideo')
            ->update(['field_type' => 'url']);
    }
};
