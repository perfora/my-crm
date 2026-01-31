<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TumIsler;

$rows = TumIsler::where('tipi','Kazanıldı')
    ->whereRaw("strftime('%Y', kapanis_tarihi) = '2025'")
    ->where('aciklama','like','%ORJ:%')
    ->get();

$sumTeklif = 0.0;
$sumAlis = 0.0;
$count = $rows->count();

foreach ($rows as $r) {
    if (preg_match('/\[ORJ:\s*teklif\s*([0-9.,\-]+)/i', $r->aciklama, $m)) {
        $s = str_replace('.', '', $m[1]);
        $s = str_replace(',', '.', $s);
        $sumTeklif += (float)$s;
    }
    if (preg_match('/\[ORJ:.*?alis\s*([0-9.,\-]+)/i', $r->aciklama, $m2)) {
        $s2 = str_replace('.', '', $m2[1]);
        $s2 = str_replace(',', '.', $s2);
        $sumAlis += (float)$s2;
    }
}

echo "Rows with ORJ in 2025+Kazanıldı: $count\n";
echo "Sum original teklif (USD): " . number_format($sumTeklif, 2) . "\n";
echo "Sum original alis (USD): " . number_format($sumAlis, 2) . "\n";
