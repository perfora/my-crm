<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// CSV dosyasını oku
$csvFile = storage_path('app/kisiler.csv');
$content = file_get_contents($csvFile);

// BOM'u temizle
$content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

// Satırları böl
$lines = explode("\n", $content);

echo "Kişi firmalarını eşleştiriyorum...\n";
echo "==================================\n\n";

$updated = 0;
$failed = 0;

for ($i = 1; $i < count($lines); $i++) {
    if (empty(trim($lines[$i]))) continue;
    
    $row = str_getcsv($lines[$i]);
    if (count($row) < 5) continue;
    
    $adSoyad = $row[0] ?? '';
    $firma = $row[4] ?? '';
    
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
        // Kişiyi ara ve güncelle
        $kisi = DB::table('kisiler')
            ->where('ad_soyad', 'like', '%' . str_replace('%', '\%', $adSoyad) . '%')
            ->first();
        
        if ($kisi) {
            DB::table('kisiler')
                ->where('id', $kisi->id)
                ->update(['musteri_id' => $dbFirma->id]);
            
            echo "✅ " . $adSoyad . " → " . $firmaName . " (ID: " . $dbFirma->id . ")\n";
            $updated++;
        } else {
            echo "⚠️ Kişi bulunamadı: " . $adSoyad . "\n";
            $failed++;
        }
    } else {
        echo "❌ Firma bulunamadı: " . $firmaName . " (" . $adSoyad . ")\n";
        $failed++;
    }
}

echo "\n==================================\n";
echo "Güncellenen kişi: " . $updated . "\n";
echo "Başarısız: " . $failed . "\n";
