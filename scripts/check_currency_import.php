<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$summary = DB::select("SELECT COUNT(*) AS total, SUM(CASE WHEN teklif_doviz='USD' THEN 1 ELSE 0 END) as usd_count, SUM(CASE WHEN teklif_doviz='USD' THEN teklif_tutari ELSE 0 END) as usd_sum, SUM(CASE WHEN teklif_doviz='TL' THEN teklif_tutari ELSE 0 END) as tl_sum FROM tum_isler");
$dupes = DB::select("SELECT name, musteri_id, kapanis_tarihi, COUNT(*) as cnt FROM tum_isler GROUP BY name,musteri_id,kapanis_tarihi HAVING cnt>1 ORDER BY cnt DESC LIMIT 50");
$report = ['summary' => $summary[0], 'duplicates_top' => $dupes];
file_put_contents(__DIR__ . '/currency_import_report.json', json_encode($report, JSON_PRETTY_PRINT));
print_r($report);
