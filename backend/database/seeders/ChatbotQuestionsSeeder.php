<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ChatbotQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->warn('⚠️  ChatbotQuestionsSeeder is DEPRECATED.');
        $this->command->warn('   Content has been merged into ChatbotSeeder (16 comprehensive questions).');
        $this->command->warn('   Running this seeder independently is no longer required.');
        $this->command->info('✅ No action performed — safely skipped.');
    }
}

