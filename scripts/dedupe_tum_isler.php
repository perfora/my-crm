<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Dedupe tum_isler started\n";
$groups = DB::select("SELECT name, musteri_id, kapanis_tarihi, COUNT(*) as cnt FROM tum_isler GROUP BY name,musteri_id,kapanis_tarihi HAVING cnt>1");
$totalDeleted = 0;
foreach ($groups as $g) {
    $name = $g->name;
    $musteri = $g->musteri_id === null ? 'NULL' : $g->musteri_id;
    $kapanis = $g->kapanis_tarihi === null ? 'NULL' : $g->kapanis_tarihi;
    $rows = DB::select("SELECT id FROM tum_isler WHERE name = ? AND (musteri_id IS ? OR musteri_id = ?) AND (kapanis_tarihi IS ? OR kapanis_tarihi = ?) ORDER BY id ASC", [$name, $g->musteri_id, $g->musteri_id, $g->kapanis_tarihi, $g->kapanis_tarihi]);
    if (count($rows) <= 1) continue;
    // Keep the last (highest id)
    $ids = array_map(function($r){return $r->id;}, $rows);
    $keep = array_pop($ids);
    $toDelete = $ids;
    $deleted = DB::delete("DELETE FROM tum_isler WHERE id IN (" . implode(',', $toDelete) . ")");
    $totalDeleted += $deleted;
    echo "Group: {$name} ({$musteri}, {$kapanis}) - kept {$keep}, deleted {$deleted}\n";
}

echo "Dedupe complete. Total deleted: {$totalDeleted}\n";