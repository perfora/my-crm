<?php

require_once __DIR__ . '/../bootstrap/app.php';

use App\Models\Ziyaret;
use App\Models\Musteri;

// CSV dosyasını oku
$csvFile = __DIR__ . '/../storage/app/ziyaretler.csv';
$handle = fopen($csvFile, 'r');

if (!$handle) {
    echo "CSV dosyası açılamadı: $csvFile\n";
    exit(1);
}

// Başlık satırını oku
$header = fgetcsv($handle, 0, ',');
$headerMap = array_flip($header);

echo "CSV Sütunları:\n";
print_r($header);
echo "\n";

// Müşteriler tablosundan tüm firmalar al
$musteriler = Musteri::all();
$musteriMap = [];
foreach ($musteriler as $m) {
    $musteriMap[strtolower($m->sirket)] = $m->id;
}

echo "Veritabanındaki Müşteriler:\n";
foreach ($musteriMap as $sirket => $id) {
    echo "  - $sirket (ID: $id)\n";
}
echo "\n";

// Her satırı işle
$lineNum = 2; // CSV satır numarası (başlık = 1)
$matched = 0;
$unmatched = 0;
$notFound = [];

while (($row = fgetcsv($handle, 0, ',')) !== false) {
    if (count($row) < count($header)) {
        $row = array_pad($row, count($header), '');
    }
    
    $data = array_combine($header, $row);
    $ziyaretIsmi = trim($data['Ziyaret İsmi'] ?? '');
    $musteriCsvRaw = trim($data['Müşteriler'] ?? '');
    
    if (empty($ziyaretIsmi)) {
        $lineNum++;
        continue;
    }
    
    // CSV'deki müşteri adını parse et (link'ten önce gelen kısım)
    // Format: "Fortinet Türkiye (https://www.notion.so/...)" veya sadece "Fortinet Türkiye"
    $musteriName = preg_replace('/\s*\(https?:\/\/.*\)$/i', '', $musteriCsvRaw);
    $musteriName = trim($musteriName);
    
    echo "Satır $lineNum: '$ziyaretIsmi' -> '$musteriName'\n";
    
    if (empty($musteriName)) {
        echo "  ⚠️  Müşteri bilgisi yok\n";
        $unmatched++;
        $lineNum++;
        continue;
    }
    
    // Veritabanında eşleştir
    $musteriNameLower = strtolower($musteriName);
    
    if (isset($musteriMap[$musteriNameLower])) {
        $musterId = $musteriMap[$musteriNameLower];
        
        // Ziyareti bul ve güncelle
        $ziyaret = Ziyaret::where('ziyaret_ismi', $ziyaretIsmi)
                          ->where('musteri_id', null)
                          ->first();
        
        if ($ziyaret) {
            $ziyaret->update(['musteri_id' => $musterId]);
            echo "  ✅ Eşleştirildi (Müşteri ID: $musterId)\n";
            $matched++;
        } else {
            // Zaten atanmış mı kontrol et
            $existing = Ziyaret::where('ziyaret_ismi', $ziyaretIsmi)
                               ->where('musteri_id', $musterId)
                               ->first();
            if ($existing) {
                echo "  ℹ️  Zaten atanmış (Müşteri ID: $musterId)\n";
                $matched++;
            } else {
                echo "  ⚠️  Ziyaret bulunamadı veya farklı müşteri var\n";
                $unmatched++;
                $notFound[] = $ziyaretIsmi;
            }
        }
    } else {
        echo "  ❌ Müşteri bulunamadı: '$musteriName'\n";
        $unmatched++;
        $notFound[] = $musteriName;
    }
    
    $lineNum++;
}

fclose($handle);

echo "\n";
echo "========== ÖZET ==========\n";
echo "Eşleştirildi: $matched\n";
echo "Eşleştirilemedi: $unmatched\n";

if (!empty($notFound)) {
    echo "\nEşleştirilemeyenler:\n";
    foreach (array_unique($notFound) as $name) {
        echo "  - $name\n";
    }
}
