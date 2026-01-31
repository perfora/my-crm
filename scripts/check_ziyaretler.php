<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// CSV'deki kayıtları oku
$csvFile = storage_path('app/ziyaretler.csv');
$csvData = [];

if (($handle = fopen($csvFile, 'r')) !== false) {
    $headers = fgetcsv($handle);
    
    echo "CSV Başlıkları:\n";
    print_r($headers);
    echo "\n";
    
    $rowCount = 0;
    while (($row = fgetcsv($handle)) !== false) {
        $csvData[] = array_combine($headers, $row);
        $rowCount++;
    }
    fclose($handle);
    
    echo "CSV'de toplam satır: " . $rowCount . "\n";
} else {
    echo "CSV dosyası açılamadı!\n";
    exit(1);
}

// DB'deki kayıtları say
$dbCount = DB::table('ziyaretler')->count();
echo "Veritabanında toplam ziyaret: " . $dbCount . "\n\n";

if ($rowCount != $dbCount) {
    echo "⚠️ UYARI: CSV ve veritabanı sayıları farklı!\n";
    echo "Fark: " . ($rowCount - $dbCount) . " kayıt eksik\n\n";
    
    // CSV'deki ilk 5 kaydın detaylarını göster
    echo "CSV'deki ilk 5 kayıt:\n";
    for ($i = 0; $i < min(5, count($csvData)); $i++) {
        echo ($i + 1) . ". " . ($csvData[$i]['Ziyaret İsmi'] ?? 'N/A') . " - ";
        echo "Tarih: " . ($csvData[$i]['Ziyaret Tarihi'] ?? 'N/A') . "\n";
    }
    
    echo "\n";
    echo "DB'deki ilk 5 kayıt:\n";
    $dbRecords = DB::table('ziyaretler')
        ->join('musteriler', 'ziyaretler.musteri_id', '=', 'musteriler.id')
        ->select('ziyaretler.*', 'musteriler.sirket')
        ->orderBy('ziyaretler.id')
        ->limit(5)
        ->get();
    
    foreach ($dbRecords as $idx => $record) {
        echo ($idx + 1) . ". " . ($record->isim ?? 'N/A') . " - ";
        echo "Müşteri: " . ($record->sirket ?? 'N/A') . " - ";
        echo "Tarih: " . ($record->ziyaret_tarihi ?? 'N/A') . "\n";
    }
} else {
    echo "✅ CSV ve veritabanı kayıt sayıları eşleşiyor!\n";
}
