<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new App\Services\RelieseStudioService();
$erData = $service->getErDiagramData();

echo "TABLES COUNT: " . count($erData['tables']) . "\n";
foreach ($erData['tables'] as $t) {
    echo "- Table: " . $t['name'] . " (Model: " . $t['model'] . ", Cols: " . count($t['columns']) . ")\n";
}

echo "\nRELATIONSHIPS COUNT: " . count($erData['relationships']) . "\n";
foreach ($erData['relationships'] as $r) {
    echo "- " . $r['from_model'] . " (" . $r['from_table'] . ") -> " . $r['to_model'] . " (" . $r['to_table'] . ") via " . $r['foreign_key'] . "\n";
}

echo "\nMERMAID:\n" . $erData['mermaid'] . "\n";
