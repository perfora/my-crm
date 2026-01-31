<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TumIsler;

$teklif = TumIsler::where('tipi','Kazanıldı')->whereYear('kapanis_tarihi',2025)->sum('teklif_tutari');
$alis = TumIsler::where('tipi','Kazanıldı')->whereYear('kapanis_tarihi',2025)->sum('alis_tutari');
$count = TumIsler::where('tipi','Kazanıldı')->whereYear('kapanis_tarihi',2025)->count();

echo "count: $count\n";
echo "sum teklif: $teklif\n";
echo "sum alis: $alis\n";
