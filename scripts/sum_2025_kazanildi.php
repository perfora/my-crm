<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$res = DB::select("SELECT COUNT(*) as cnt, SUM(CASE WHEN teklif_doviz='USD' THEN 1 ELSE 0 END) as usd_count, SUM(CASE WHEN teklif_doviz='USD' THEN teklif_tutari ELSE 0 END) as usd_sum, SUM(CASE WHEN teklif_doviz='TL' OR teklif_doviz IS NULL THEN teklif_tutari ELSE 0 END) as tl_sum FROM tum_isler WHERE strftime('%Y', kapanis_tarihi) = '2025' AND tipi = 'Kazanıldı'");
file_put_contents(__DIR__.'/sum_2025_kazanildi.json', json_encode($res, JSON_PRETTY_PRINT));
print_r($res);
