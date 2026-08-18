<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== materials columns ===\n";
$cols = \Illuminate\Support\Facades\Schema::getColumnListing('materials');
foreach ($cols as $c) echo "  - $c\n";

echo "\n=== material_transactions columns ===\n";
$cols2 = \Illuminate\Support\Facades\Schema::getColumnListing('material_transactions');
foreach ($cols2 as $c) echo "  - $c\n";
