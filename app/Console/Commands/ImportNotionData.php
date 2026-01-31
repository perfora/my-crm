<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Musteri;
use App\Models\Marka;
use App\Models\Kisi;
use App\Models\Ziyaret;
use App\Models\TumIsler;

class ImportNotionData extends Command
{
    protected $signature = "notion:import {type} {--dry-run} {--create-missing} {--fix-currencies} {--preserve-csv}";
    protected $description = 'Import data from Notion CSV export (supports --dry-run, --create-missing and --fix-currencies)';

    public function handle()
    {
        $type = $this->argument('type');
        
        switch($type) {
            case 'musteriler':
                $this->importMusteriler();
                break;
            case 'markalar':
                $this->importMarkalar();
                break;
            case 'kisiler':
                $this->importKisiler();
                break;
            case 'ziyaretler':
                $this->importZiyaretler();
                break;
            case 'tum-isler':
                $this->importTumIsler();
                break;
            default:
                $this->error('Geçersiz tip! Kullanım: musteriler, markalar, kisiler, ziyaretler, tum-isler');
        }
    }
    
    private function importMusteriler()
    {
        $this->info('Müşteriler import ediliyor...');
        
        $file = storage_path('app/firmalar.csv');
        
        if (!file_exists($file)) {
            $this->error('firmalar.csv bulunamadı!');
            return;
        }
        
        // Daha güvenilir CSV okuma: delimiter otomatik tespiti (',' veya ';') ve fgetcsv kullanımı
        if (($handle = fopen($file, 'r')) === false) {
            $this->error('firmalar.csv açılamadı!');
            return;
        }
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = (substr_count($firstLine, ';') >= substr_count($firstLine, ',')) ? ';' : ',';

        $headers = fgetcsv($handle, 0, $delimiter);
        if ($headers === false) {
            $this->error('firmalar.csv header okunamadı!');
            fclose($handle);
            return;
        }

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        $csv = $rows;

        // BOM karakterini temizle
        $headers = array_map(function($header) {
            return trim(str_replace("\xEF\xBB\xBF", '', $header));
        }, $headers);

        $this->info("Total rows: " . count($csv));
        $this->info("Headers count: " . count($headers));
        $this->info("First 3 headers: " . json_encode(array_slice($headers, 0, 3)));
        
        $count = 0;
        
        foreach ($csv as $row) {
            // Debug: İlk kaydı göster
            if ($count == 0) {
                $this->info("Headers: " . json_encode(array_slice($headers, 0, 5)));
                $this->info("First Row: " . json_encode(array_slice($row, 0, 5)));
                $data_test = array_combine($headers, $row);
                $this->info("Name value: [" . ($data_test['Name'] ?? 'YOK') . "]");
            }
            
            // Sütun sayısı eşleşmiyorsa skip et
            if (count($headers) !== count($row)) {
                continue;
            }
            
            $data = array_combine($headers, $row);
            
            // Derece dönüşümü
            $derece = trim($data['Derece'] ?? '');
            $derece = empty($derece) ? null : $derece;
            
            $turu = trim($data['Türü'] ?? '');
            $turu = empty($turu) ? null : $turu;
            
            Musteri::create([
                'sirket' => $data['Şirket'] ?? '',
                'sehir' => $data['Şehir'] ?? '',
                'adres' => $data['Adres'] ?? '',
                'telefon' => $data['Telefon'] ?? '',
                'notlar' => $data['Notlar'] ?? '',
                'derece' => $derece,
                'turu' => $turu,
            ]);
            
            $count++;
            $this->info("İşlendi: " . ($data['Şirket'] ?? 'İsimsiz'));
        }
        
        $this->info("Toplam {$count} müşteri import edildi!");
    }
    
    private function importMarkalar()
    {
        $this->info('Markalar import ediliyor...');
        // CSV okuma kodu buraya gelecek
        $this->info('Tamamlandı!');
    }
    
    private function importKisiler()
    {
        $this->info('Kişiler import ediliyor...');
        
        $file = storage_path('app/kisiler.csv');
        
        if (!file_exists($file)) {
            $this->error('kisiler.csv bulunamadı!');
            return;
        }
        
        // Daha güvenilir CSV okuma: delimiter otomatik tespiti ve fgetcsv
        if (($handle = fopen($file, 'r')) === false) {
            $this->error('kisiler.csv açılamadı!');
            return;
        }
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = (substr_count($firstLine, ';') >= substr_count($firstLine, ',')) ? ';' : ',';

        $headers = fgetcsv($handle, 0, $delimiter);
        if ($headers === false) {
            $this->error('kisiler.csv header okunamadı!');
            fclose($handle);
            return;
        }

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        $csv = $rows;

        // BOM karakterini temizle
        $headers = array_map(function($header) {
            return trim(str_replace("\xEF\xBB\xBF", '', $header));
        }, $headers);
        
        $count = 0;
        
        foreach ($csv as $row) {
            $data = array_combine($headers, $row);
            
            // Müşteri adından ID bul
            $musteriAdi = trim($data['Firma'] ?? '');
            $musteri = null;
            
            if (!empty($musteriAdi)) {
                $musteri = \App\Models\Musteri::where('sirket', $musteriAdi)->first();
            }
            
            \App\Models\Kisi::create([
                'ad_soyad' => $data['Ad Soyad'] ?? '',
                'telefon_numarasi' => $data['Telefon Numarası'] ?? '',
                'email_adresi' => $data['Email Adresi'] ?? '',
                'bolum' => $data['Bölüm'] ?? '',
                'gorev' => $data['Görev'] ?? '',
                'musteri_id' => $musteri ? $musteri->id : null,
                'url' => $data['Url'] ?? '',
            ]);
            
            $count++;
            $this->info("İşlendi: " . ($data['Ad Soyad'] ?? 'İsimsiz'));
        }
        
        $this->info("Toplam {$count} kişi import edildi!");
    }
    
    private function importZiyaretler()
    {
        $this->info('Ziyaretler import ediliyor...');
        
        $file = storage_path('app/ziyaretler.csv');
        
        if (!file_exists($file)) {
            $this->error('ziyaretler.csv bulunamadı!');
            return;
        }
        
        // Daha güvenilir CSV okuma: delimiter otomatik tespiti ve fgetcsv
        if (($handle = fopen($file, 'r')) === false) {
            $this->error('ziyaretler.csv açılamadı!');
            return;
        }
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = (substr_count($firstLine, ';') >= substr_count($firstLine, ',')) ? ';' : ',';

        $headers = fgetcsv($handle, 0, $delimiter);
        if ($headers === false) {
            $this->error('ziyaretler.csv header okunamadı!');
            fclose($handle);
            return;
        }

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        $csv = $rows;

        // BOM karakterini temizle
        $headers = array_map(function($header) {
            return trim(str_replace("\xEF\xBB\xBF", '', $header));
        }, $headers);
        
        $count = 0;
        
        foreach ($csv as $row) {
            // Sütun sayısı eşleşmiyorsa skip et
            if (count($headers) !== count($row)) {
                $this->warn("Satır atlandı: " . (isset($row[0]) ? $row[0] : 'Bilinmiyor'));
                continue;
            }
            
            $data = array_combine($headers, $row);
            
            // Müşteri adından ID bul
            $musteriAdi = trim($data['Müşteri'] ?? '');
            $musteri = null;
            
            if (!empty($musteriAdi)) {
                $musteri = \App\Models\Musteri::where('sirket', $musteriAdi)->first();
            }
            
            // Tarih dönüşümleri
            $ziyaretTarihiStr = trim($data['Ziyaret Tarihi'] ?? '');
            $ziyaretTarihi = null;
            if (!empty($ziyaretTarihiStr)) {
                // (GMT+3) gibi timezone bilgisini temizle
                $ziyaretTarihiStr = preg_replace('/\s*\(.*?\)$/', '', $ziyaretTarihiStr);
                try {
                    $ziyaretTarihi = \Carbon\Carbon::parse($ziyaretTarihiStr)->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    // İkinci deneme: farklı format
                    try {
                        $ziyaretTarihi = \Carbon\Carbon::createFromFormat('m/d/Y g:i A', $ziyaretTarihiStr)->format('Y-m-d H:i:s');
                    } catch (\Exception $e2) {
                        // Skip
                    }
                }
            }
            
            $aramaTarihiStr = trim($data['Arama Tarihi'] ?? '');
            $aramaTarihi = null;
            if (!empty($aramaTarihiStr)) {
                $aramaTarihiStr = preg_replace('/\s*\(.*?\)$/', '', $aramaTarihiStr);
                try {
                    $aramaTarihi = \Carbon\Carbon::parse($aramaTarihiStr)->format('Y-m-d');
                } catch (\Exception $e) {
                    try {
                        $aramaTarihi = \Carbon\Carbon::createFromFormat('m/d/Y', $aramaTarihiStr)->format('Y-m-d');
                    } catch (\Exception $e2) {
                        // Skip
                    }
                }
            }
            
            $tur = trim($data['Tür'] ?? '');
            $tur = empty($tur) ? null : $tur;
            
            $durumu = trim($data['Durumu'] ?? '');
            $durumu = empty($durumu) ? null : $durumu;
            
            \App\Models\Ziyaret::create([
                'ziyaret_ismi' => $data['Ziyaret İsmi'] ?? '',
                'musteri_id' => $musteri ? $musteri->id : null,
                'ziyaret_tarihi' => $ziyaretTarihi,
                'arama_tarihi' => $aramaTarihi,
                'tur' => $tur,
                'durumu' => $durumu,
                'ziyaret_notlari' => $data['Ziyaret Notları'] ?? '',
            ]);
            
            $count++;
            $this->info("İşlendi: " . ($data['Ziyaret İsmi'] ?? 'İsimsiz'));
        }
        
        $this->info("Toplam {$count} ziyaret import edildi!");
    }
    
    private function importTumIsler()
    {
        $this->info('Tüm İşler import ediliyor...');

        $file = storage_path('app/tum-isler.csv');

        if (!file_exists($file)) {
            $this->error('tum-isler.csv bulunamadı!');
            return;
        }

        // Eğer --fix-currencies seçeneği verilmişse, mevcut kayıtları dönüştür (kur>1 ve küçük tutarlar USD varsayılacak)
        if ($this->option('fix-currencies')) {
            $this->info('Mevcut kayıtlar için döviz düzeltmesi başlatılıyor... (kur>1 ve tutar < 10000 ise USD varsayılacak)');
            $dryRunFix = (bool) $this->option('dry-run');
            $usdThreshold = 10000;
            $candidates = \App\Models\TumIsler::whereNotNull('kur')->where('kur', '>', 1)->get();
            $converted = 0;
            $skipped = 0;
            foreach ($candidates as $rec) {
                if (strpos($rec->aciklama ?? '', '[ORJ:') !== false) {
                    $skipped++;
                    continue;
                }
                $orig_teklif = $rec->teklif_tutari;
                if ($orig_teklif === null) { $skipped++; continue; }
                if (abs($orig_teklif) < $usdThreshold) {
                    $new_teklif = round($orig_teklif * $rec->kur, 2);
                    $new_alis = null;
                    if (!is_null($rec->alis_tutari) && abs($rec->alis_tutari) < $usdThreshold) {
                        $new_alis = round($rec->alis_tutari * $rec->kur, 2);
                    }
                    if ($dryRunFix) {
                        $this->info("Dry-run: #{$rec->id} {$rec->name} teklif {$orig_teklif} -> {$new_teklif} (kur {$rec->kur})");
                    } else {
                        $rec->aciklama = trim(($rec->aciklama ?? '') . " [ORJ: teklif {$orig_teklif} USD, kur {$rec->kur}]");
                        $rec->teklif_tutari = $new_teklif;
                        if (!is_null($new_alis)) $rec->alis_tutari = $new_alis;
                        $rec->save();
                        $this->info("Dönüştürüldü: #{$rec->id} {$rec->name} teklif {$orig_teklif} -> {$new_teklif}");
                    }
                    $converted++;
                } else {
                    $skipped++;
                }
            }
            $this->info("Döviz düzeltme tamamlandı. Dönüştürülen: {$converted}, atlanan: {$skipped}");
            return;
        }

        $rows = [];
        $skippedRows = [];

        if (($handle = fopen($file, 'r')) === false) {
            $this->error('tum-isler.csv açılamadı!');
            return;
        }

        // Use fgetcsv to properly handle quoted fields (includes newlines inside fields)
        $headers = fgetcsv($handle, 0, ',');
        if ($headers === false) {
            $this->error('tum-isler.csv header okunamadı!');
            fclose($handle);
            return;
        }

        $lineNumber = 1;
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $lineNumber++;
            $rows[] = $row;
        }

        fclose($handle);

        $csv = $rows;

        // BOM ve başlık normalize
        $headers = array_map(function ($header) {
            $h = trim(str_replace("\xEF\xBB\xBF", '', $header));
            // Küçük harfe çevir ve Türkçe karakterleri sadeleştir
            $h = mb_strtolower($h);
            $trans = [
                'ş' => 's', 'ı' => 'i', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g', 'ç' => 'c',
                ' ' => '_', '\r' => '', '\n' => '', '\t' => '', '\"' => '', "'" => ''
            ];
            $h = strtr($h, $trans);
            // Temel mapping: sık kullanılan header varyasyonlarını eşle
            $map = [
                'müşteriler' => 'musteri', 'müşteri' => 'musteri', 'musteriler' => 'musteri',
                'name' => 'name', 'marka' => 'marka', 'tekif_tutari' => 'teklif_tutari',
                'teklif_tutarı' => 'teklif_tutari', 'teklif_tutari' => 'teklif_tutari',
                'alış_tutarı' => 'alis_tutari', 'alis_tutarı' => 'alis_tutari', 'alis_tutari' => 'alis_tutari',
                'kur' => 'kur', 'kapanış_tarihi' => 'kapanis_tarihi', 'kapanis_tarihi' => 'kapanis_tarihi',
                'lisans_bitiş' => 'lisans_bitis', 'lisans_bitis' => 'lisans_bitis',
                'tipi' => 'tipi', 'türü' => 'turu', 'turu' => 'turu', 'öncelik' => 'oncelik', 'oncelik' => 'oncelik',
                'kaybedilme_nedeni' => 'kaybedilme_nedeni', 'kaybedlime_nedeni' => 'kaybedilme_nedeni',
                'register_durum' => 'register_durum', 'register_durum' => 'register_durum',
                'notlar' => 'notlar', 'geçmiş_notlar' => 'gecmis_notlar', 'gecmis_notlar' => 'gecmis_notlar',
                'açıklama' => 'aciklama', 'aciklama' => 'aciklama', 'son_güncelleme' => 'is_guncellenme_tarihi', 'son_guncelleme' => 'is_guncellenme_tarihi',
            ];

            $h = $map[$h] ?? $h;
            return $h;
        }, $headers);

        // Komut seçenekleri
        $dryRun = (bool) $this->option('dry-run');
        $createMissing = (bool) $this->option('create-missing');
        $preserveCsv = (bool) $this->option('preserve-csv');

        $count = 0;
        $duplicates = 0;
        $createdMusteri = 0;
        $createdMarka = 0;
        $wouldCreate = 0;

        $parseNumber = function ($s) {
            $s = trim((string)$s);
            if ($s === '') {
                return null;
            }
            // Remove currency symbols and spaces
            $s = str_replace(['$', '€', '£', '\xc2\xa0', ' '], '', $s);
            
            // Check if it has both comma and dot
            if (strpos($s, ',') !== false && strpos($s, '.') !== false) {
                // Determine which is decimal separator (last one)
                $lastComma = strrpos($s, ',');
                $lastDot = strrpos($s, '.');
                if ($lastDot > $lastComma) {
                    // Dot is decimal, comma is thousands: 1,234.56
                    $s = str_replace(',', '', $s);
                } else {
                    // Comma is decimal, dot is thousands: 1.234,56
                    $s = str_replace('.', '', $s);
                    $s = str_replace(',', '.', $s);
                }
            } elseif (strpos($s, ',') !== false) {
                // Only comma - could be decimal or thousands
                // If only one comma and 2-3 digits after, it's decimal
                if (preg_match('/^[0-9]+,[0-9]{1,4}$/', $s)) {
                    $s = str_replace(',', '.', $s);
                } else {
                    // Multiple commas or different pattern - thousands
                    $s = str_replace(',', '', $s);
                }
            }
            // else: only dots or no separators - keep as is
            
            // Clean any remaining non-numeric characters
            $s = preg_replace('/[^0-9.\-]/', '', $s);
            return $s === '' ? null : (float)$s;
        };

        $parseDate = function ($s, $withTime = false) {
            $s = trim((string)$s);
            if ($s === '') return null;
            // (GMT+3) gibi parantez içlerini temizle
            $s = preg_replace('/\s*\(.*?\)$/', '', $s);
            try {
                $dt = \Carbon\Carbon::parse($s);
                return $withTime ? $dt->format('Y-m-d H:i:s') : $dt->format('Y-m-d');
            } catch (\Exception $e) {
                // Farklı format deneyleri
                $formats = ['d.m.Y H:i', 'd.m.Y', 'm/d/Y g:i A', 'm/d/Y'];
                foreach ($formats as $fmt) {
                    try {
                        $dt = \Carbon\Carbon::createFromFormat($fmt, $s);
                        return $withTime ? $dt->format('Y-m-d H:i:s') : $dt->format('Y-m-d');
                    } catch (\Exception $e2) {
                        // devam
                    }
                }
            }
            return null;
        };

        foreach ($csv as $rowIndex => $row) {
            // Sütun sayısı eşleşmiyorsa skip et ve kaydet
            if (count($headers) !== count($row)) {
                $this->warn("Satır atlandı (sutun: " . count($row) . ") satırIndex: {$rowIndex}");
                $skippedRows[] = ['index' => $rowIndex, 'row' => $row];
                continue;
            }

            $data = array_combine($headers, $row);

            // Müşteri bulma - parantez içi linkleri kaldır
            $musteriAdi = trim(preg_replace('/\s*\(.*$/', '', ($data['musteri'] ?? '')));
            $musteri = null;
            if (!empty($musteriAdi)) {
                $musteri = \App\Models\Musteri::where('sirket', $musteriAdi)->first();
                if (!$musteri) {
                    $musteri = \App\Models\Musteri::where('sirket', 'like', $musteriAdi . '%')->first();
                }

                // Eğer bulunamadıysa ve --create-missing verilmişse otomatik oluştur
                if (!$musteri && $createMissing) {
                    if ($dryRun) {
                        $wouldCreate++;
                        $this->info("Dry-run: Musteri oluşturulacak: {$musteriAdi}");
                    } else {
                        $musteri = \App\Models\Musteri::create(['sirket' => $musteriAdi]);
                        $createdMusteri++;
                        $this->info("Müşteri oluşturuldu: {$musteriAdi}");
                    }
                }
            }

            // Marka bulma
            $markaAdi = trim(preg_replace('/\s*\(.*$/', '', ($data['marka'] ?? '')));
            $marka = null;
            if (!empty($markaAdi)) {
                $marka = \App\Models\Marka::where('name', $markaAdi)->first();
                if (!$marka) {
                    $marka = \App\Models\Marka::where('name', 'like', $markaAdi . '%')->first();
                }

                // Eğer bulunamadıysa ve --create-missing verilmişse otomatik oluştur
                if (!$marka && $createMissing) {
                    if ($dryRun) {
                        $wouldCreate++;
                        $this->info("Dry-run: Marka oluşturulacak: {$markaAdi}");
                    } else {
                        $marka = \App\Models\Marka::create(['name' => $markaAdi]);
                        $createdMarka++;
                        $this->info("Marka oluşturuldu: {$markaAdi}");
                    }
                }
            }

            // Tarih dönüşümleri
            $kapanisTarihi = $parseDate($data['kapanis_tarihi'] ?? '', false);
            $lisansBitis = $parseDate($data['lisans_bitis'] ?? '', false);

            // Boş değerleri null yap
            $tipi = trim($data['tipi'] ?? '');
            $tipi = $tipi === '' ? null : $tipi;

            $turu = trim($data['turu'] ?? '');
            $turu = $turu === '' ? null : $turu;

            $oncelik = trim($data['oncelik'] ?? '');
            $oncelik = $oncelik === '' ? null : $oncelik;

            $kaybedilmeNedeni = trim($data['kaybedilme_nedeni'] ?? '');
            $kaybedilmeNedeni = $kaybedilmeNedeni === '' ? null : $kaybedilmeNedeni;

            $registerDurum = trim($data['register_durum'] ?? '');
            $registerDurum = $registerDurum === '' ? null : $registerDurum;

            // Sayı normalize
            $teklif = $parseNumber($data['teklif_tutari'] ?? $data['tekif_tutari'] ?? '');
            $alis = $parseNumber($data['alis_tutari'] ?? '');
            $kur = $parseNumber($data['kur'] ?? '');

            // Tüm tutarlar USD - kur kontrolüne gerek yok
            $orig_teklif = $teklif;
            $orig_alis = $alis;

            // is_guncellenme_tarihi
            $isGuncelleme = $parseDate($data['is_guncellenme_tarihi'] ?? $data['son_guncelleme'] ?? '', false);

            // Zorunlu alan kontrolü
            $name = trim($data['name'] ?? '');
            if ($name === '') {
                $this->warn("Satır {$rowIndex} atlandı: isim boş");
                $skippedRows[] = ['index' => $rowIndex, 'row' => $row, 'reason' => 'name_empty'];
                continue;
            }

            // Duplicate kontrolü (name + musteri + kapanis tarihi)
            $existsQuery = \App\Models\TumIsler::where('name', $name);
            if ($musteri) {
                $existsQuery->where('musteri_id', $musteri->id);
            } else {
                $existsQuery->whereNull('musteri_id');
            }
            if ($kapanisTarihi) {
                $existsQuery->where('kapanis_tarihi', $kapanisTarihi);
            }

            if ($existsQuery->exists() && !$preserveCsv) {
                $duplicates++;
                $this->info("Duplicate atlandı: {$name}");
                continue;
            }

            if ($dryRun) {
                $wouldCreate++;
                $this->info("Dry-run: yeni iş oluşturulacak: {$name}");
            } else {
                try {
                    $payload = [
                        'name' => $name,
                        'musteri_id' => $musteri ? $musteri->id : null,
                        'marka_id' => $marka ? $marka->id : null,
                        'tipi' => $tipi,
                        'turu' => $turu,
                        'oncelik' => $oncelik,
                        'kaybedilme_nedeni' => $kaybedilmeNedeni,
                        'register_durum' => $registerDurum,
                        // Eğer --preserve-csv verilmişse, CSV'deki ham değerleri olduğu gibi sakla (döviz işaretlemesi yapılacak)
                        'teklif_tutari' => $teklif,
                        'alis_tutari' => $alis,
                        'kur' => $kur,
                        'kapanis_tarihi' => $kapanisTarihi,
                        'lisans_bitis' => $lisansBitis,
                        'is_guncellenme_tarihi' => $isGuncelleme,
                        'notlar' => trim($data['notlar'] ?? ''),
                        'gecmis_notlar' => trim($data['gecmis_notlar'] ?? $data['gecmis_notlar'] ?? ''),
                        'aciklama' => trim($data['aciklama'] ?? ''),
                    ];

                    if ($preserveCsv) {
                        // Tüm tutarlar USD olarak sakla
                        if (!is_null($orig_teklif) && $orig_teklif != 0) {
                            $payload['teklif_doviz'] = 'USD';
                            $payload['teklif_tutari_orj'] = $orig_teklif;
                            $payload['teklif_tutari'] = $orig_teklif;
                            $payload['orj_kur'] = $kur;
                        }
                        if (!is_null($orig_alis) && $orig_alis != 0) {
                            $payload['alis_doviz'] = 'USD';
                            $payload['alis_tutari_orj'] = $orig_alis;
                            $payload['alis_tutari'] = $orig_alis;
                        }
                    }

                    if ($existsQuery->exists()) {
                        // Güncelle (preserve modu); bulunup güncellenecek
                        $rec = $existsQuery->first();
                        $rec->update($payload);
                        $this->info("Güncellendi: {$name}");
                    } else {
                        \App\Models\TumIsler::create($payload);
                        $this->info("İşlendi: {$name}");
                    }
                } catch (\Exception $e) {
                    $this->error("Satir {$rowIndex} - Hata: " . ($name ?: 'Isimsiz') . " - " . $e->getMessage());
                    $skippedRows[] = ['index' => $rowIndex, 'row' => $row, 'reason' => 'exception', 'message' => $e->getMessage()];
                    continue;
                }

                $count++;
            }
        }

        $this->info("Toplam {$count} iş import edildi!");
        $this->info("Duplicate atlananlar: {$duplicates}");
        $this->info("Oluşturulan müşteri: {$createdMusteri}, oluşturulan marka: {$createdMarka}");
        if ($dryRun) {
            $this->info("Dry-run modu etkin: Gerçekte oluşturulmayacak, toplam oluşturulması beklenen: {$wouldCreate}");
        }

        // Atlanan satırları raporla
        if (!empty($skippedRows)) {
            $path = storage_path('app/tum-isler-skipped.csv');
            $fp = fopen($path, 'w');
            // Başlık
            fputcsv($fp, ['row_index', 'columns_count', 'reason', 'raw'], ';');
            foreach ($skippedRows as $s) {
                fputcsv($fp, [$s['index'], count($s['row']), $s['reason'] ?? '', implode(';', $s['row'])], ';');
            }
            fclose($fp);
            $this->info("Atlanan satırlar kaydedildi: {$path}");
        }
    }
    }