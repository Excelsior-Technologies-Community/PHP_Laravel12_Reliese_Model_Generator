<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement("DROP DATABASE IF EXISTS `school`");
    echo "Dropped database school successfully\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
