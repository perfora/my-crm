<?php

namespace App\Console\Commands;

use App\Models\TumIsler;
use App\Models\Musteri;
use App\Models\Marka;
use App\Services\NotionService;
use Illuminate\Console\Command;

class SyncNotionData extends Command
{
    protected $signature = 'notion:sync {database_id} {--type=tum-isler}';
    protected $description = 'Notion veritabanından verileri çek ve Laravel\'e senkronize et';

    private $notionService;

    public function __construct(NotionService $notionService)
    {
        parent::__construct();
        $this->notionService = $notionService;
    }

    public function handle()
    {
        $databaseId = $this->argument('database_id');
        $type = $this->option('type');

        $this->info("🔄 Notion'dan veri çekiliyor...");

        // Önce database şemasını öğren
        $schema = $this->notionService->getDatabaseSchema($databaseId);
        
        if (!$schema) {
            $this->error('❌ Database şeması alınamadı. API token ve database ID\'yi kontrol et!');
            return 1;
        }

        $this->info("✓ Database: " . ($schema['title'][0]['plain_text'] ?? 'Untitled'));
        
        // Tüm kayıtları çek
        $records = $this->notionService->getAllDatabaseRecords($databaseId);
        $this->info("✓ " . count($records) . " kayıt bulundu");

        // Parse et
        $parsed = $this->notionService->parseRecords($records);

        // Senkronize et
        $this->info("📥 Veriler senkronize ediliyor...");
        
        if ($type === 'tum-isler') {
            $result = $this->syncTumIsler($parsed, $schema);
        } elseif ($type === 'musteriler') {
            $result = $this->syncMusteriler($parsed, $schema);
        } else {
            $this->error("❌ Bilinmeyen tip: {$type}");
            return 1;
        }

        $this->info("✅ Senkronizasyon tamamlandı!");
        $this->table(
            ['Durum', 'Sayı'],
            [
                ['Yeni Eklenen', $result['created']],
                ['Güncellenen', $result['updated']],
                ['Atlanan', $result['skipped']],
            ]
        );

        return 0;
    }

    private function syncTumIsler($records, $schema)
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;

        // Property mapping'i kullanıcıya göster
        $this->info("\n📋 Notion Property Mapping:");
        $properties = $schema['properties'] ?? [];
        foreach ($properties as $key => $prop) {
            $this->line("  {$key} → {$prop['type']}");
        }

        $bar = $this->output->createProgressBar(count($records));
        $bar->start();

        foreach ($records as $record) {
            try {
                // Notion property isimlerini Laravel field'larına map et
                $data = $this->mapNotionToTumIsler($record);

                if (!$data) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Debug: İlk kayıt
                if ($created === 0 && $updated === 0) {
                    $this->info("\n🔍 İlk kayıt debug:");
                    $this->info("Teklif Tutarı: " . ($record['Teklif Tutarı'] ?? 'null'));
                    $this->info("Alış Tutarı: " . ($record['Alış Tutarı'] ?? 'null'));
                    $this->info("Kur: " . ($record['Kur'] ?? 'null'));
                    $this->info(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }

                // Notion ID ile kontrol et
                $existing = TumIsler::where('notion_id', $record['notion_id'])->first();

                if ($existing) {
                    // Güncelle
                    $existing->update($data);
                    $updated++;
                } else {
                    // Yeni ekle
                    TumIsler::create(array_merge($data, [
                        'notion_id' => $record['notion_id'],
                        'notion_url' => $record['notion_url'],
                    ]));
                    $created++;
                }

            } catch (\Exception $e) {
                $this->error("\n❌ Hata: " . $e->getMessage());
                $skipped++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        return compact('created', 'updated', 'skipped');
    }

    private function mapNotionToTumIsler($record)
    {
        // Notion property isimleri ile Laravel field'ları eşleştir
        // Bu mapping'i kendi Notion veritabanına göre güncelle
        
        $data = [];

        // İş Adı
        if (isset($record['Name']) || isset($record['İş Adı'])) {
            $data['name'] = $record['Name'] ?? $record['İş Adı'];
        }

        // Müşteri - Notion relation'dan çek ("Müşteriler" field'ı)
        if (isset($record['Müşteriler']) && is_array($record['Müşteriler']) && isset($record['Müşteriler']['ids'])) {
            $musteriIds = $record['Müşteriler']['ids'];
            if (!empty($musteriIds)) {
                $notionMusteriId = $musteriIds[0];
                $musteriTitle = $this->notionService->getPageTitle($notionMusteriId);
                if ($musteriTitle) {
                    $musteri = Musteri::firstOrCreate(['sirket' => $musteriTitle]);
                    $data['musteri_id'] = $musteri->id;
                }
            }
        } elseif (isset($record['Müşteri']) && $record['Müşteri']) {
            // Fallback
            $musteriName = is_array($record['Müşteri']) ? $record['Müşteri'][0] : $record['Müşteri'];
            $musteri = Musteri::firstOrCreate(
                ['sirket' => $musteriName],
                ['sirket' => $musteriName] // Duplicate to avoid mass assignment issue
            );
            $data['musteri_id'] = $musteri->id;
        }

        // Marka - Notion relation'dan çek
        if (isset($record['Marka']) && is_array($record['Marka']) && isset($record['Marka']['ids'])) {
            $markaIds = $record['Marka']['ids'];
            if (!empty($markaIds)) {
                $notionMarkaId = $markaIds[0];
                $markaTitle = $this->notionService->getPageTitle($notionMarkaId);
                if ($markaTitle) {
                    $marka = Marka::firstOrCreate(['name' => $markaTitle]);
                    $data['marka_id'] = $marka->id;
                }
            }
        } elseif (isset($record['Marka']) && $record['Marka'] && !is_array($record['Marka'])) {
            // Fallback
            $markaName = $record['Marka'];
            $marka = Marka::firstOrCreate(
                ['name' => $markaName],
                ['name' => $markaName]
            );
            $data['marka_id'] = $marka->id;
        }

        // Tipi
        if (isset($record['Tipi'])) {
            $data['tipi'] = $record['Tipi'];
        }

        // Durum
        if (isset($record['Durum'])) {
            $data['durum'] = $record['Durum'];
        }

        // Türü
        if (isset($record['Türü'])) {
            $data['turu'] = $record['Türü'];
        }

        // Öncelik
        if (isset($record['Öncelik'])) {
            $data['oncelik'] = $record['Öncelik'];
        }

        // Register Durumu
        if (isset($record['Register Durumu'])) {
            $data['register_durum'] = $record['Register Durumu'];
        }

        // Tutarlar
        if (isset($record['Teklif Tutarı'])) {
            $data['teklif_tutari'] = $record['Teklif Tutarı'];
        }
        
        if (isset($record['Alış Tutarı'])) {
            $data['alis_tutari'] = $record['Alış Tutarı'];
        }

        if (isset($record['Maliyet Tutarı'])) {
            $data['maliyet_tutari'] = $record['Maliyet Tutarı'];
        }

        if (isset($record['Kur'])) {
            $data['kur'] = $record['Kur'];
        }

        // Dövizler
        if (isset($record['Teklif Döviz'])) {
            $data['teklif_doviz'] = $record['Teklif Döviz'];
        }

        if (isset($record['Alış Döviz'])) {
            $data['alis_doviz'] = $record['Alış Döviz'];
        }

        // Tarihler
        if (isset($record['Açılış Tarihi'])) {
            $data['is_guncellenme_tarihi'] = $record['Açılış Tarihi'];
        } elseif (isset($record['created_time'])) {
            // Açılış Tarihi yoksa created_time kullan
            $data['is_guncellenme_tarihi'] = date('Y-m-d', strtotime($record['created_time']));
        }

        if (isset($record['Kapanış Tarihi'])) {
            $data['kapanis_tarihi'] = $record['Kapanış Tarihi'];
        }

        if (isset($record['Lisans Bitiş'])) {
            $data['lisans_bitis'] = $record['Lisans Bitiş'];
        }

        // Notlar
        if (isset($record['Açıklama'])) {
            $data['aciklama'] = $record['Açıklama'];
        }

        if (isset($record['Notlar'])) {
            $data['notlar'] = $record['Notlar'];
        }

        if (isset($record['Geçmiş Notlar'])) {
            $data['gecmis_notlar'] = $record['Geçmiş Notlar'];
        }

        // Kaybedilme Nedeni
        if (isset($record['Kaybedilme Nedeni'])) {
            $data['kaybedilme_nedeni'] = $record['Kaybedilme Nedeni'];
        }

        return $data;
    }

    private function syncMusteriler($records, $schema)
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;

        $bar = $this->output->createProgressBar(count($records));
        $bar->start();

        foreach ($records as $record) {
            try {
                $data = [];

                // Şirket adı (zorunlu)
                if (isset($record['Name']) || isset($record['Şirket'])) {
                    $data['sirket'] = $record['Name'] ?? $record['Şirket'];
                } else {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Diğer alanlar
                if (isset($record['Yetkili'])) $data['yetkili'] = $record['Yetkili'];
                if (isset($record['Telefon'])) $data['telefon'] = $record['Telefon'];
                if (isset($record['Email'])) $data['email'] = $record['Email'];
                if (isset($record['Adres'])) $data['adres'] = $record['Adres'];
                if (isset($record['Notlar'])) $data['notlar'] = $record['Notlar'];

                // Notion ID ile kontrol et
                $existing = Musteri::where('notion_id', $record['notion_id'])->first();

                if ($existing) {
                    $existing->update($data);
                    $updated++;
                } else {
                    Musteri::create(array_merge($data, [
                        'notion_id' => $record['notion_id'],
                        'notion_url' => $record['notion_url'],
                    ]));
                    $created++;
                }

            } catch (\Exception $e) {
                $this->error("\n❌ Hata: " . $e->getMessage());
                $skipped++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        return compact('created', 'updated', 'skipped');
    }
}
