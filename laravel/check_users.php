<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo "=== users columns ===\n";
foreach (\Illuminate\Support\Facades\Schema::getColumnListing('users') as $c) echo "  - $c\n";
