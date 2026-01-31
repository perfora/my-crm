<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$service = new \App\Services\NotionService();
$records = $service->getDatabaseRecords('2084dfbd6e3e8077a2a3d7c3ec3ff4d4');

if (!empty($records['results'])) {
    // İlk 3 kaydı kontrol et
    for ($i = 0; $i < min(3, count($records['results'])); $i++) {
        $record = $records['results'][$i];
        echo "\n=== Kayıt " . ($i + 1) . " ===\n";
        echo "Name: " . ($record['properties']['Name']['title'][0]['plain_text'] ?? 'yok') . "\n";
        
        // Teklif Tutarı
        $teklifProp = $record['properties']['Teklif Tutarı'] ?? null;
        if ($teklifProp) {
            echo "Teklif Tutarı type: " . $teklifProp['type'] . "\n";
            echo "Teklif Tutarı value: " . ($teklifProp['number'] ?? 'null') . "\n";
        }
        
        // Alış Tutarı
        $alisProp = $record['properties']['Alış Tutarı'] ?? null;
        if ($alisProp) {
            echo "Alış Tutarı type: " . $alisProp['type'] . "\n";
            echo "Alış Tutarı value: " . ($alisProp['number'] ?? 'null') . "\n";
        }
    }
} else {
    echo "Kayıt yok!\n";
}
