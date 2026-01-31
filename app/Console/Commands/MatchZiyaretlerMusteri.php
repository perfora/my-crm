<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ziyaret;
use App\Models\Musteri;

class MatchZiyaretlerMusteri extends Command
{
    protected $signature = 'ziyaretler:match-musteri';
    protected $description = 'Ziyaretler CSV dosyasındaki müşteri bilgilerini musteriler tablosuyla eşle';

    public function handle()
    {
        $normalize = function ($value) {
            $value = trim((string) $value);
            $value = preg_replace('/\s+/', ' ', $value);
            $map = [
                'ç' => 'c', 'Ç' => 'c',
                'ğ' => 'g', 'Ğ' => 'g',
                'ı' => 'i', 'İ' => 'i',
                'ö' => 'o', 'Ö' => 'o',
                'ş' => 's', 'Ş' => 's',
                'ü' => 'u', 'Ü' => 'u',
            ];
            $value = strtr($value, $map);
            $value = mb_strtolower($value, 'UTF-8');
            $value = preg_replace('/[^a-z0-9\s]/', '', $value);
            $value = trim(preg_replace('/\s+/', ' ', $value));
            return $value;
        };

        // CSV dosyasını oku
        $csvFile = storage_path('app/ziyaretler.csv');
        $handle = fopen($csvFile, 'r');

        if (!$handle) {
            $this->error("CSV dosyası açılamadı: $csvFile");
            return 1;
        }

        // Başlık satırını oku
        $header = fgetcsv($handle, 0, ',');
        
        // BOM karakterini kaldır
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\x{FEFF}/u', '', $header[0]);
        }
        
        $this->info("CSV Sütunları:");
        $this->line(json_encode($header));

        // Müşteriler tablosundan tüm firmalar al
        $musteriler = Musteri::all();
        $musteriMap = [];
        foreach ($musteriler as $m) {
            $musteriMap[strtolower($m->sirket)] = $m->id;
            $musteriMap[$normalize($m->sirket)] = $m->id;
        }

        $aliases = [
            'il saglik' => 'konya il saglik mudurlugu',
            'gida tarim' => 'konya gida ve tarim universitesi',
            'selcuklu bld' => 'selcuklu belediyesi',
            'selcuklu belediyesi' => 'selcuklu belediyesi',
            'mpg' => 'mpg',
            'koyuncu' => 'koyuncu',
            'daxler energy' => 'daxler energy',
        ];

        $this->info("\nVeritabanındaki Müşteriler:");
        foreach ($musteriMap as $sirket => $id) {
            $this->line("  - $sirket (ID: $id)");
        }

        // Ziyaretleri normalize ederek map oluştur
        $ziyaretMap = [];
        foreach (Ziyaret::all() as $z) {
            $key = $normalize($z->ziyaret_ismi);
            if (!isset($ziyaretMap[$key])) {
                $ziyaretMap[$key] = [];
            }
            $ziyaretMap[$key][] = $z->id;
        }

        // Her satırı işle
        $lineNum = 2;
        $matched = 0;
        $unmatched = 0;
        $notFound = [];
        $unmatchedDetails = [];
        $bar = $this->output->createProgressBar(156);

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $bar->advance();
            
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
            
            // CSV'deki müşteri adını parse et
            $musteriName = preg_replace('/\s*\(https?:\/\/.*\)$/i', '', $musteriCsvRaw);
            $musteriName = trim($musteriName);
            $musteriKey = $normalize($musteriName);

            if (isset($aliases[$musteriKey])) {
                $musteriKey = $aliases[$musteriKey];
            }
            
            if (empty($musteriName)) {
                $unmatched++;
                $unmatchedDetails[] = ['ziyaret' => $ziyaretIsmi, 'musteri' => '(boş)'];
                $lineNum++;
                continue;
            }
            
            // Veritabanında eşleştir
            $musteriNameLower = strtolower($musteriName);
            
            $musteriId = null;
            if (isset($musteriMap[$musteriNameLower])) {
                $musteriId = $musteriMap[$musteriNameLower];
            } elseif (isset($musteriMap[$musteriKey])) {
                $musteriId = $musteriMap[$musteriKey];
            }

            if ($musteriId !== null) {
                $ziyaretKey = $normalize($ziyaretIsmi);
                $targetIds = $ziyaretMap[$ziyaretKey] ?? [];
                if (!empty($targetIds)) {
                    $updated = Ziyaret::whereIn('id', $targetIds)
                                      ->update(['musteri_id' => $musteriId]);
                    if ($updated > 0) {
                        $matched++;
                    }
                }
            } else {
                $unmatched++;
                $unmatchedDetails[] = ['ziyaret' => $ziyaretIsmi, 'musteri' => $musteriName];
                if (!in_array($musteriName, $notFound)) {
                    $notFound[] = $musteriName;
                }
            }
            
            $lineNum++;
        }

        fclose($handle);
        $bar->finish();

        $this->newLine();
        $this->info("========== ÖZET ==========");
        $this->info("Eşleştirildi: $matched");
        $this->info("Eşleştirilemedi: $unmatched");

        if (!empty($unmatchedDetails)) {
            $this->warn("\nEşleştirilemeyenler:");
            foreach ($unmatchedDetails as $detail) {
                $this->line("  - Ziyaret: '{$detail['ziyaret']}' -> Müşteri: '{$detail['musteri']}'");
            }
        }

        return 0;
    }
}
