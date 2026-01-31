<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// CSV dosyasını kontrol et
$csvFile = storage_path('app/kisiler.csv');

if (!file_exists($csvFile)) {
    echo "CSV dosyası bulunamadı: " . $csvFile . "\n";
    exit(1);
}

// Dosyayı oku
$content = file_get_contents($csvFile);

// BOM'u temizle
$content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

// Satırları böl
$lines = explode("\n", $content);

echo "CSV'deki kişi-firma eşleştirmesi:\n";
echo "=================================\n\n";

// Headers
$headers = str_getcsv($lines[0]);

$matched = 0;
$unmatched = [];
$rowCount = 0;

for ($i = 1; $i < count($lines); $i++) {
    if (empty(trim($lines[$i]))) continue;
    
    $row = str_getcsv($lines[$i]);
    if (count($row) < 5) continue;
    
    $rowCount++;
    
    $adSoyad = $row[0] ?? '';
    $firma = $row[4] ?? ''; // Firma column'u 4. index (0-based)
    
    if (empty(trim($firma))) continue;
    
    // Firma adını URL'den ayıkla
    $firmaName = $firma;
    if (preg_match('/^([^(]+?)\s*\(https:/', $firma, $m)) {
        $firmaName = trim($m[1]);
    }
    
    // DB'de eşleşen firmayı bul
    $dbFirma = DB::table('musteriler')
        ->where('sirket', 'like', '%' . str_replace('%', '\%', $firmaName) . '%')
        ->first();
    
    if ($dbFirma) {
        $matched++;
        echo "✅ " . $adSoyad . " → " . $firmaName . " (ID: " . $dbFirma->id . ")\n";
    } else {
        $unmatched[$firmaName] = ($unmatched[$firmaName] ?? 0) + 1;
        echo "❌ " . $adSoyad . " → " . $firmaName . " (EŞLEŞTİRİLEMEDİ)\n";
    }
}

echo "\n=================================\n";
echo "Toplamlar:\n";
echo "  Firma bilgisi olan kişi: " . $rowCount . "\n";
echo "  Başarıyla eşleştirilen: " . $matched . "\n";
echo "  Eşleştirilemeyen: " . count($unmatched) . "\n";

if (!empty($unmatched)) {
    echo "\nEşleştirilemeyen firmalar:\n";
    foreach ($unmatched as $firma => $count) {
        echo "  - " . $firma . " (" . $count . " kişi)\n";
    }
}
