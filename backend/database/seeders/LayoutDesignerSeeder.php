<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LayoutDesignerSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->warn('⚠️  LayoutDesignerSeeder is DEPRECATED since schema update.');
        $this->command->warn('   Using replacement seeders instead:');
        $this->command->warn('    • ProfileBuilderSectionsSeeder (sections)');
        $this->command->warn('    • ProfileBuilderFieldsSeeder (fields base)');
        $this->command->warn('    • AddMissingProfileFieldsSeeder (extra fields)');
        $this->command->warn('    • BlogSectionSeeder & PortfolioSectionSeeder (section fields)');
        $this->command->warn('    • UpdateFieldGroupsSeeder & UpdateFieldValidationSeeder (updates)');
        $this->command->info('✅ No action performed — safely skipped.');
    }
}
