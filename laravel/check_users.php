<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = \App\Models\User::all(['id', 'name', 'email', 'phone', 'role', 'plain_password', 'is_active']);
echo json_encode($users, JSON_PRETTY_PRINT);
