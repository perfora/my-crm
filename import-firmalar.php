<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$csv = storage_path('app/firmalar.csv');
$data = array_map('str_getcsv', file($csv));
$header = array_shift($data);

$imported = 0;
foreach ($data as $row) {
    $record = array_combine($header, $row);
    if (!empty($record['sirket'])) {
        \App\Models\Musteri::firstOrCreate(
            ['sirket' => $record['sirket']],
            [
                'sehir' => $record['sehir'] ?? null,
                'adres' => $record['adres'] ?? null,
                'telefon' => $record['telefon'] ?? null,
                'notlar' => $record['notlar'] ?? null,
                'derece' => $record['derece'] ?? null,
                'turu' => $record['turu'] ?? null,
            ]
        );
        $imported++;
    }
}

echo "✓ $imported firma kaydı kontrol edildi/eklendi\n";
$total = \App\Models\Musteri::count();
echo "Toplam: $total müşteri\n";
