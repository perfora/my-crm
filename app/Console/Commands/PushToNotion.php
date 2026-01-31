<?php

namespace App\Console\Commands;

use App\Models\TumIsler;
use App\Models\Musteri;
use App\Models\Marka;
use App\Services\NotionService;
use Illuminate\Console\Command;

class PushToNotion extends Command
{
    protected $signature = 'notion:push {database_id} {--type=tum-isler} {--force}';
    protected $description = 'Laravel\'deki verileri Notion\'a gönder';

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
        $force = $this->option('force');

        $this->info("📤 Laravel'den Notion'a veri gönderiliyor...");

        if ($type === 'tum-isler') {
            $result = $this->pushTumIsler($databaseId, $force);
        } elseif ($type === 'musteriler') {
            $result = $this->pushMusteriler($databaseId, $force);
        } elseif ($type === 'markalar') {
            $result = $this->pushMarkalar($databaseId, $force);
        } else {
            $this->error("❌ Bilinmeyen tip: {$type}");
            return 1;
        }

        $this->info("✅ Push işlemi tamamlandı!");
        $this->table(
            ['Durum', 'Sayı'],
            [
                ['Yeni Oluşturulan', $result['created']],
                ['Güncellenen', $result['updated']],
                ['Atlanan', $result['skipped']],
                ['Hata', $result['errors']],
            ]
        );

        return 0;
    }

    private function pushTumIsler($databaseId, $force)
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        // Notion ID'si olmayan veya force ile tüm kayıtları al
        if ($force) {
            $records = TumIsler::with(['musteri', 'marka'])->get();
            $this->warn("⚠️  --force: Tüm kayıtlar Notion'a gönderilecek");
        } else {
            $records = TumIsler::with(['musteri', 'marka'])
                ->where(function($q) {
                    $q->whereNull('notion_id')
                      ->orWhere('updated_at', '>', now()->subHours(24)); // Son 24 saatte güncellenenler
                })
                ->get();
        }

        $this->info("📋 {$records->count()} kayıt işlenecek");

        $bar = $this->output->createProgressBar($records->count());
        $bar->start();

        foreach ($records as $is) {
            try {
                $data = $is->toArray();
                
                // İlişkileri çözümle
                $this->notionService->resolveRelations($data, $is);

                if ($is->notion_id) {
                    // Güncelle
                    $result = $this->notionService->updatePage($is->notion_id, $data);
                    if ($result) {
                        $updated++;
                    } else {
                        $errors++;
                        $this->newLine();
                        $this->error("❌ Güncelleme hatası: {$is->name}");
                    }
                } else {
                    // Yeni oluştur
                    $result = $this->notionService->createPage($databaseId, $data);
                    if ($result) {
                        // Notion ID'yi Laravel'e kaydet
                        $is->update([
                            'notion_id' => $result['id'],
                            'notion_url' => $result['url'],
                        ]);
                        $created++;
                    } else {
                        $errors++;
                        $this->newLine();
                        $this->error("❌ Oluşturma hatası: {$is->name}");
                    }
                }

            } catch (\Exception $e) {
                $this->newLine();
                $this->error("❌ Hata ({$is->name}): " . $e->getMessage());
                $errors++;
            }

            $bar->advance();
            
            // Rate limit: Notion API saniyede 3 request
            usleep(350000); // 350ms bekle
        }

        $bar->finish();
        $this->newLine();

        return compact('created', 'updated', 'skipped', 'errors');
    }

    private function pushMusteriler($databaseId, $force)
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        if ($force) {
            $records = Musteri::all();
        } else {
            $records = Musteri::whereNull('notion_id')
                ->orWhere('updated_at', '>', now()->subHours(24))
                ->get();
        }

        $this->info("📋 {$records->count()} müşteri işlenecek");

        $bar = $this->output->createProgressBar($records->count());
        $bar->start();

        foreach ($records as $musteri) {
            try {
                $data = [
                    'Name' => [
                        'title' => [
                            ['text' => ['content' => $musteri->sirket]]
                        ]
                    ],
                ];

                if ($musteri->telefon) {
                    $data['Telefon'] = [
                        'phone_number' => $musteri->telefon
                    ];
                }

                if ($musteri->email) {
                    $data['Email'] = [
                        'email' => $musteri->email
                    ];
                }

                if ($musteri->notlar) {
                    $data['Notlar'] = [
                        'rich_text' => [
                            ['text' => ['content' => substr($musteri->notlar, 0, 2000)]]
                        ]
                    ];
                }

                if ($musteri->notion_id) {
                    $result = $this->notionService->updatePage($musteri->notion_id, ['properties' => $data]);
                    if ($result) $updated++;
                    else $errors++;
                } else {
                    $result = $this->notionService->createPage($databaseId, ['properties' => $data]);
                    if ($result) {
                        $musteri->update([
                            'notion_id' => $result['id'],
                            'notion_url' => $result['url'],
                        ]);
                        $created++;
                    } else {
                        $errors++;
                    }
                }

            } catch (\Exception $e) {
                $this->newLine();
                $this->error("❌ Hata ({$musteri->sirket}): " . $e->getMessage());
                $errors++;
            }

            $bar->advance();
            usleep(350000);
        }

        $bar->finish();
        $this->newLine();

        return compact('created', 'updated', 'skipped', 'errors');
    }

    private function pushMarkalar($databaseId, $force)
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        if ($force) {
            $records = Marka::all();
        } else {
            $records = Marka::whereNull('notion_id')
                ->orWhere('updated_at', '>', now()->subHours(24))
                ->get();
        }

        $this->info("📋 {$records->count()} marka işlenecek");

        $bar = $this->output->createProgressBar($records->count());
        $bar->start();

        foreach ($records as $marka) {
            try {
                $data = [
                    'Name' => [
                        'title' => [
                            ['text' => ['content' => $marka->name]]
                        ]
                    ],
                ];

                if ($marka->notion_id) {
                    $result = $this->notionService->updatePage($marka->notion_id, ['properties' => $data]);
                    if ($result) $updated++;
                    else $errors++;
                } else {
                    $result = $this->notionService->createPage($databaseId, ['properties' => $data]);
                    if ($result) {
                        $marka->update([
                            'notion_id' => $result['id'],
                            'notion_url' => $result['url'],
                        ]);
                        $created++;
                    } else {
                        $errors++;
                    }
                }

            } catch (\Exception $e) {
                $this->newLine();
                $this->error("❌ Hata ({$marka->name}): " . $e->getMessage());
                $errors++;
            }

            $bar->advance();
            usleep(350000);
        }

        $bar->finish();
        $this->newLine();

        return compact('created', 'updated', 'skipped', 'errors');
    }
}
