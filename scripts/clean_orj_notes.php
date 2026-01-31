<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Cleaning ORJ notes from aciklama field...\n";

$rows = DB::select("SELECT id, aciklama FROM tum_isler WHERE aciklama LIKE '%ORJ:%'");
$count = 0;

foreach ($rows as $row) {
    $cleaned = preg_replace('/\s*\[ORJ:[^\]]+\]\s*/i', ' ', $row->aciklama);
    $cleaned = preg_replace('/\s+/', ' ', $cleaned);
    $cleaned = trim($cleaned);
    
    DB::update("UPDATE tum_isler SET aciklama = ? WHERE id = ?", [$cleaned, $row->id]);
    $count++;
    echo "Cleaned ID {$row->id}\n";
}

echo "Total cleaned: {$count}\n";
