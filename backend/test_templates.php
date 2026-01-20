<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test fetching templates
$templates = App\Models\CardTemplate::active()->completed()->get();

echo "=== Active + Completed Templates ===" . PHP_EOL;
echo "Total: " . $templates->count() . PHP_EOL;

foreach ($templates as $t) {
    echo "ID: {$t->id} | Name: {$t->name} | Plans: " . json_encode($t->plan_types) . PHP_EOL;
}

echo PHP_EOL . "=== Testing plan=basic filter ===" . PHP_EOL;
$basic = App\Models\CardTemplate::active()
    ->completed()
    ->whereJsonContains('plan_types', 'basic')
    ->get();
echo "Found for basic: " . $basic->count() . PHP_EOL;

echo PHP_EOL . "Done!" . PHP_EOL;
