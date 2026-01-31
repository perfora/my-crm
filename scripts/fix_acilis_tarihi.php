<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TumIsler;
use Carbon\Carbon;

$csvPath = storage_path('app/tum-isler.csv');

if (!file_exists($csvPath)) {
    die("CSV dosyası bulunamadı: $csvPath\n");
}

$file = fopen($csvPath, 'r');

// BOM karakterini atla
$bom = fread($file, 3);
if ($bom !== "\xEF\xBB\xBF") {
    rewind($file);
}

$headers = fgetcsv($file);

// Başlık indekslerini bul
$nameIndex = array_search('Name', $headers);
$acilisTarihiIndex = array_search('Açılış Tarihi', $headers);

echo "Başlıklar: " . implode(', ', array_slice($headers, 0, 10)) . "\n";
echo "Name index: $nameIndex, Açılış Tarihi index: $acilisTarihiIndex\n\n";

if ($nameIndex === false || $acilisTarihiIndex === false) {
    die("Gerekli sütunlar bulunamadı!\n");
}

$updated = 0;
$notFound = 0;

while (($row = fgetcsv($file)) !== false) {
    $name = $row[$nameIndex] ?? '';
    $acilisTarihi = $row[$acilisTarihiIndex] ?? '';
    
    if (empty($name) || empty($acilisTarihi)) {
        continue;
    }
    
    // Tarihi parse et
    try {
        $tarih = Carbon::parse($acilisTarihi);
    } catch (\Exception $e) {
        echo "Tarih parse hatası: $name - $acilisTarihi\n";
        continue;
    }
    
    // İşi bul ve güncelle
    $is = TumIsler::where('name', $name)->first();
    
    if ($is) {
        $is->is_guncellenme_tarihi = $tarih;
        $is->save();
        $updated++;
        echo "✓ $name -> " . $tarih->format('d.m.Y') . "\n";
    } else {
        $notFound++;
    }
}

fclose($file);

echo "\n";
echo "Güncellendi: $updated\n";
echo "Bulunamadı: $notFound\n";
