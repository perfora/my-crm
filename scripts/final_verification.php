<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== FINAL VERIFICATION REPORT ===\n\n";

// Overall stats
$total = DB::table('tum_isler')->count();
$usdCount = DB::table('tum_isler')->where('teklif_doviz', 'USD')->count();
$tlCount = DB::table('tum_isler')->where('teklif_doviz', 'TL')->orWhereNull('teklif_doviz')->count();

echo "Total records: {$total}\n";
echo "USD records: {$usdCount}\n";
echo "TL records: {$tlCount}\n\n";

// Sum by currency
$usdSum = DB::table('tum_isler')->where('teklif_doviz', 'USD')->sum('teklif_tutari');
$tlSum = DB::table('tum_isler')->where(function($q) {
    $q->where('teklif_doviz', 'TL')->orWhereNull('teklif_doviz');
})->sum('teklif_tutari');

echo "Total USD: $" . number_format($usdSum, 2) . "\n";
echo "Total TL: " . number_format($tlSum, 2) . " TL\n\n";

// 2025 Kazanıldı stats
$kazanildi2025 = DB::table('tum_isler')
    ->whereRaw("strftime('%Y', kapanis_tarihi) = '2025'")
    ->where('tipi', 'Kazanıldı')
    ->get();

$k2025Count = $kazanildi2025->count();
$k2025USD = $kazanildi2025->where('teklif_doviz', 'USD')->sum('teklif_tutari');
$k2025TL = $kazanildi2025->filter(function($r) {
    return $r->teklif_doviz === 'TL' || $r->teklif_doviz === null;
})->sum('teklif_tutari');
$k2025USDCount = $kazanildi2025->where('teklif_doviz', 'USD')->count();

echo "2025 Kazanıldı:\n";
echo "  Total: {$k2025Count} records\n";
echo "  USD: {$k2025USDCount} records = $" . number_format($k2025USD, 2) . "\n";
echo "  TL: " . number_format($k2025TL, 2) . " TL\n\n";

// Check for ORJ notes remaining
$orjCount = DB::table('tum_isler')->where('aciklama', 'like', '%ORJ:%')->count();
echo "Records with ORJ notes in aciklama: {$orjCount}\n\n";

// Sample USD records
echo "Sample USD records (top 5):\n";
$samples = DB::table('tum_isler')
    ->where('teklif_doviz', 'USD')
    ->orderBy('teklif_tutari', 'desc')
    ->limit(5)
    ->get(['id', 'name', 'teklif_tutari', 'teklif_tutari_orj', 'kur']);

foreach ($samples as $s) {
    echo "  ID {$s->id}: {$s->name} - \${$s->teklif_tutari} (orj: {$s->teklif_tutari_orj}, kur: {$s->kur})\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
