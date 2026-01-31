<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class NotionService
{
    private $apiToken;
    private $version = '2022-06-28';
    private $baseUrl = 'https://api.notion.com/v1';

    public function __construct()
    {
        // Database'den API token'ı al
        $token = DB::table('notion_settings')
            ->where('key', 'api_token')
            ->value('value');
        
        $this->apiToken = $token ?? env('NOTION_API_TOKEN');
    }

    /**
     * Notion veritabanından tüm kayıtları çek
     */
    public function getDatabaseRecords($databaseId, $filter = null)
    {
        $url = "{$this->baseUrl}/databases/{$databaseId}/query";
        
        $body = [];
        if ($filter) {
            $body['filter'] = $filter;
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiToken}",
            'Notion-Version' => $this->version,
            'Content-Type' => 'application/json',
        ])->post($url, $body);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Notion API Error', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return null;
    }

    /**
     * Notion veritabanı yapısını öğren
     */
    public function getDatabaseSchema($databaseId)
    {
        $url = "{$this->baseUrl}/databases/{$databaseId}";

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiToken}",
            'Notion-Version' => $this->version,
        ])->get($url);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     * Notion page'in title'ını al
     */
    public function getPageTitle($pageId)
    {
        $url = "{$this->baseUrl}/pages/{$pageId}";

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiToken}",
            'Notion-Version' => $this->version,
        ])->get($url);

        if ($response->successful()) {
            $page = $response->json();
            // Title property'sini bul
            foreach ($page['properties'] as $property) {
                if ($property['type'] === 'title' && !empty($property['title'])) {
                    return $property['title'][0]['plain_text'] ?? null;
                }
            }
        }

        return null;
    }

    /**
     * Notion property değerlerini Laravel formatına çevir
     */
    public function parseProperty($property)
    {
        if (!$property || !isset($property['type'])) {
            return null;
        }

        $type = $property['type'];
        $value = $property[$type] ?? null;

        switch ($type) {
            case 'title':
                return $value[0]['plain_text'] ?? '';
            
            case 'rich_text':
                return $value[0]['plain_text'] ?? '';
            
            case 'number':
                return $value;
            
            case 'select':
                return $value['name'] ?? null;
            
            case 'multi_select':
                return collect($value)->pluck('name')->implode(', ');
            
            case 'date':
                if ($value && isset($value['start'])) {
                    return $value['start'];
                }
                return null;
            
            case 'checkbox':
                return $value ? 1 : 0;
            
            case 'url':
                return $value;
            
            case 'email':
                return $value;
            
            case 'phone_number':
                return $value;
            
            case 'relation':
                // İlişkili kayıtların ID'lerini döndür - property adıyla birlikte
                return [
                    'ids' => collect($value)->pluck('id')->toArray(),
                    'has_more' => $property['has_more'] ?? false,
                ];
            
            case 'formula':
                // Formula sonucunu parse et
                if (isset($value['type'])) {
                    return $this->parseFormulaResult($value);
                }
                return null;
            
            case 'rollup':
                // Rollup sonucunu parse et
                if (isset($value['type'])) {
                    return $this->parseRollupResult($value);
                }
                return null;
            
            default:
                return null;
        }
    }

    private function parseFormulaResult($formula)
    {
        $type = $formula['type'];
        return $formula[$type] ?? null;
    }

    private function parseRollupResult($rollup)
    {
        $type = $rollup['type'];
        return $rollup[$type] ?? null;
    }

    /**
     * Sayfalandırma ile tüm kayıtları çek
     */
    public function getAllDatabaseRecords($databaseId, $filter = null)
    {
        $allResults = [];
        $hasMore = true;
        $startCursor = null;

        while ($hasMore) {
            $url = "{$this->baseUrl}/databases/{$databaseId}/query";
            
            $body = [];
            if ($filter) {
                $body['filter'] = $filter;
            }
            if ($startCursor) {
                $body['start_cursor'] = $startCursor;
            }

            // Boş array'i object'e çevir
            if (empty($body)) {
                $body = new \stdClass();
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiToken}",
                'Notion-Version' => $this->version,
                'Content-Type' => 'application/json',
            ])->post($url, $body);

            if ($response->successful()) {
                $data = $response->json();
                
                // Debug için
                Log::info('Notion API Response', [
                    'has_more' => $data['has_more'] ?? false,
                    'results_count' => count($data['results'] ?? []),
                    'total_collected' => count($allResults)
                ]);
                
                $allResults = array_merge($allResults, $data['results'] ?? []);
                
                $hasMore = $data['has_more'] ?? false;
                $startCursor = $data['next_cursor'] ?? null;
            } else {
                Log::error('Notion API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                break;
            }
        }

        return $allResults;
    }

    /**
     * Notion'dan gelen tüm kayıtları Laravel array formatına çevir
     */
    public function parseRecords($records)
    {
        $parsed = [];

        foreach ($records as $record) {
            $item = [
                'notion_id' => $record['id'],
                'notion_url' => $record['url'],
                'created_time' => $record['created_time'],
                'last_edited_time' => $record['last_edited_time'],
            ];

            // Properties'i parse et
            foreach ($record['properties'] as $key => $property) {
                $item[$key] = $this->parseProperty($property);
            }

            $parsed[] = $item;
        }

        return $parsed;
    }

    /**
     * Notion'da yeni sayfa oluştur
     */
    public function createPage($databaseId, $properties)
    {
        $url = "{$this->baseUrl}/pages";

        $body = [
            'parent' => ['database_id' => $databaseId],
            'properties' => $this->formatPropertiesForNotion($properties)
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiToken}",
            'Notion-Version' => $this->version,
            'Content-Type' => 'application/json',
        ])->post($url, $body);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Notion Create Page Error', [
            'status' => $response->status(),
            'body' => $response->body(),
            'request' => $body
        ]);

        return null;
    }

    /**
     * Notion'da mevcut sayfayı güncelle
     */
    public function updatePage($pageId, $properties)
    {
        $url = "{$this->baseUrl}/pages/{$pageId}";

        $body = [
            'properties' => $this->formatPropertiesForNotion($properties)
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiToken}",
            'Notion-Version' => $this->version,
            'Content-Type' => 'application/json',
        ])->patch($url, $body);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Notion Update Page Error', [
            'status' => $response->status(),
            'body' => $response->body(),
            'request' => $body
        ]);

        return null;
    }

    /**
     * Laravel array'ini Notion property formatına çevir
     */
    private function formatPropertiesForNotion($data)
    {
        $properties = [];

        // İş Adı
        if (isset($data['name'])) {
            $properties['Name'] = [
                'title' => [
                    ['text' => ['content' => $data['name']]]
                ]
            ];
        }

        // Müşteri (Relation - database_id gerekir)
        if (isset($data['musteri_notion_id'])) {
            $properties['Müşteri'] = [
                'relation' => [
                    ['id' => $data['musteri_notion_id']]
                ]
            ];
        }

        // Marka (Relation - database_id gerekir)
        if (isset($data['marka_notion_id'])) {
            $properties['Marka'] = [
                'relation' => [
                    ['id' => $data['marka_notion_id']]
                ]
            ];
        }

        // Tipi (Select)
        if (isset($data['tipi'])) {
            $properties['Tipi'] = [
                'select' => ['name' => $data['tipi']]
            ];
        }

        // Durum (Select)
        if (isset($data['durum'])) {
            $properties['Durum'] = [
                'select' => ['name' => $data['durum']]
            ];
        }

        // Türü (Select)
        if (isset($data['turu'])) {
            $properties['Türü'] = [
                'select' => ['name' => $data['turu']]
            ];
        }

        // Öncelik (Select)
        if (isset($data['oncelik'])) {
            $properties['Öncelik'] = [
                'select' => ['name' => (string)$data['oncelik']]
            ];
        }

        // Register Durumu (Select)
        if (isset($data['register_durum'])) {
            $properties['Register Durumu'] = [
                'select' => ['name' => $data['register_durum']]
            ];
        }

        // Tutarlar (Number)
        if (isset($data['teklif_tutari'])) {
            $properties['Teklif Tutarı'] = [
                'number' => (float)$data['teklif_tutari']
            ];
        }

        if (isset($data['alis_tutari'])) {
            $properties['Alış Tutarı'] = [
                'number' => (float)$data['alis_tutari']
            ];
        }

        if (isset($data['maliyet_tutari'])) {
            $properties['Maliyet Tutarı'] = [
                'number' => (float)$data['maliyet_tutari']
            ];
        }

        if (isset($data['kur'])) {
            $properties['Kur'] = [
                'number' => (float)$data['kur']
            ];
        }

        // Dövizler (Select)
        if (isset($data['teklif_doviz'])) {
            $properties['Teklif Döviz'] = [
                'select' => ['name' => $data['teklif_doviz']]
            ];
        }

        if (isset($data['alis_doviz'])) {
            $properties['Alış Döviz'] = [
                'select' => ['name' => $data['alis_doviz']]
            ];
        }

        // Tarihler (Date)
        if (isset($data['is_guncellenme_tarihi']) && $data['is_guncellenme_tarihi']) {
            $properties['Açılış Tarihi'] = [
                'date' => ['start' => $data['is_guncellenme_tarihi']]
            ];
        }

        if (isset($data['kapanis_tarihi']) && $data['kapanis_tarihi']) {
            $properties['Kapanış Tarihi'] = [
                'date' => ['start' => $data['kapanis_tarihi']]
            ];
        }

        if (isset($data['lisans_bitis']) && $data['lisans_bitis']) {
            $properties['Lisans Bitiş'] = [
                'date' => ['start' => $data['lisans_bitis']]
            ];
        }

        // Notlar (Rich Text)
        if (isset($data['notlar'])) {
            $properties['Notlar'] = [
                'rich_text' => [
                    ['text' => ['content' => substr($data['notlar'], 0, 2000)]] // 2000 char limit
                ]
            ];
        }

        if (isset($data['gecmis_notlar'])) {
            $properties['Geçmiş Notlar'] = [
                'rich_text' => [
                    ['text' => ['content' => substr($data['gecmis_notlar'], 0, 2000)]]
                ]
            ];
        }

        if (isset($data['aciklama'])) {
            $properties['Açıklama'] = [
                'rich_text' => [
                    ['text' => ['content' => substr($data['aciklama'], 0, 2000)]]
                ]
            ];
        }

        // Kaybedilme Nedeni (Select)
        if (isset($data['kaybedilme_nedeni'])) {
            $properties['Kaybedilme Nedeni'] = [
                'select' => ['name' => $data['kaybedilme_nedeni']]
            ];
        }

        return $properties;
    }

    /**
     * Laravel'den gelen relation'ları Notion ID'ye çevir
     */
    public function resolveRelations(&$data, $model)
    {
        // Müşteri
        if (isset($data['musteri_id']) && $data['musteri_id']) {
            $musteri = \App\Models\Musteri::find($data['musteri_id']);
            if ($musteri && $musteri->notion_id) {
                $data['musteri_notion_id'] = $musteri->notion_id;
            }
        }

        // Marka
        if (isset($data['marka_id']) && $data['marka_id']) {
            $marka = \App\Models\Marka::find($data['marka_id']);
            if ($marka && $marka->notion_id) {
                $data['marka_notion_id'] = $marka->notion_id;
            }
        }

        return $data;
    }
}
