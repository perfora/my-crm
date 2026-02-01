<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use App\Models\TumIsler;
use App\Models\Musteri;
use App\Models\Ziyaret;
use App\Models\Kisi;
use App\Models\Marka;

class DashboardDataService
{
    public function __construct(private DashboardFilterService $filterService)
    {
    }

    /**
     * Data source'a göre query builder döndür
     */
    public function getBaseQuery(string $dataSource): Builder
    {
        return match ($dataSource) {
            'tum_isler' => TumIsler::query(),
            'musteriler' => Musteri::query(),
            'ziyaretler' => Ziyaret::query(),
            'kisiler' => Kisi::query(),
            'markalar' => Marka::query(),
            default => TumIsler::query(),
        };
    }

    /**
     * Filtreleri uygula ve veri getir
     */
    public function getWidgetData(string $dataSource, array $filters = [], array $columns = []): array
    {
        $query = $this->getBaseQuery($dataSource);
        $query = $this->filterService->applyFilters($query, $filters, $dataSource);

        // Varsayılan tüm sütunlar
        if (empty($columns)) {
            return $query->get()->toArray();
        }

        // Seçili sütunlar
        return $query->select($columns)->get()->toArray();
    }

    /**
     * Mevcut veri kaynakları ve sütunları
     */
    public function getAvailableDataSources(): array
    {
        return [
            'tum_isler' => [
                'label' => 'Tüm İşler',
                'columns' => [
                    'id' => 'ID',
                    'is_adi' => 'İş Adı',
                    'musteri_id' => 'Müşteri',
                    'durum' => 'Durum',
                    'teklif' => 'Teklif',
                    'aliş' => 'Alış',
                    'kar' => 'Kar',
                    'is_tarihi' => 'İş Tarihi',
                    'created_at' => 'Oluşturma Tarihi',
                    'updated_at' => 'Güncellenme Tarihi',
                ]
            ],
            'musteriler' => [
                'label' => 'Müşteriler',
                'columns' => [
                    'id' => 'ID',
                    'adi' => 'Adı',
                    'sektor' => 'Sektor',
                    'durum' => 'Durum',
                    'telefon' => 'Telefon',
                    'email' => 'Email',
                    'created_at' => 'Oluşturma Tarihi',
                    'updated_at' => 'Güncellenme Tarihi',
                ]
            ],
            'ziyaretler' => [
                'label' => 'Ziyaretler',
                'columns' => [
                    'id' => 'ID',
                    'musteri_id' => 'Müşteri',
                    'ziyaret_tarihi' => 'Ziyaret Tarihi',
                    'notlar' => 'Notlar',
                    'durum' => 'Durum',
                    'created_at' => 'Oluşturma Tarihi',
                ]
            ],
            'kisiler' => [
                'label' => 'Kişiler',
                'columns' => [
                    'id' => 'ID',
                    'ad_soyad' => 'Ad Soyad',
                    'firma_id' => 'Firma',
                    'pozisyon' => 'Pozisyon',
                    'telefon' => 'Telefon',
                    'email' => 'Email',
                    'created_at' => 'Oluşturma Tarihi',
                ]
            ],
            'markalar' => [
                'label' => 'Markalar',
                'columns' => [
                    'id' => 'ID',
                    'marka_adi' => 'Marka Adı',
                    'lisans_bitis_tarihi' => 'Lisans Bitiş Tarihi',
                    'created_at' => 'Oluşturma Tarihi',
                ]
            ],
        ];
    }

    /**
     * Filtre seçenekleri
     */
    public function getAvailableFilters(): array
    {
        return [
            'license_expiring' => [
                'label' => 'Lisansı yakında biten',
                'params' => [
                    'days' => ['type' => 'number', 'label' => 'Gün sayısı', 'default' => 30]
                ]
            ],
            'license_expired' => [
                'label' => 'Lisansı süresi dolmuş',
                'params' => []
            ],
            'not_updated_days' => [
                'label' => 'Belirtilen günden beri güncellenmemiş',
                'params' => [
                    'days' => ['type' => 'number', 'label' => 'Gün sayısı', 'default' => 30]
                ]
            ],
            'updated_after' => [
                'label' => 'Belirtilen tarihten sonra güncellenen',
                'params' => [
                    'date' => ['type' => 'date', 'label' => 'Tarih']
                ]
            ],
            'status' => [
                'label' => 'Durum',
                'params' => [
                    'field' => ['type' => 'select', 'label' => 'Alan', 'options' => [
                        'register_durum' => 'Register Durum',
                        'tipi' => 'Tipi',
                        'turu' => 'Türü',
                    ]],
                    'value' => ['type' => 'text', 'label' => 'Değer', 'placeholder' => 'Örnek: Aktif, Tamamlandı']
                ]
            ],
            'text_search' => [
                'label' => 'Metin araması',
                'params' => [
                    'field' => ['type' => 'text', 'label' => 'Alan adı'],
                    'value' => ['type' => 'text', 'label' => 'Değer']
                ]
            ],
            'numeric_range' => [
                'label' => 'Sayısal aralık',
                'params' => [
                    'field' => ['type' => 'text', 'label' => 'Alan adı'],
                    'min' => ['type' => 'number', 'label' => 'Minimum'],
                    'max' => ['type' => 'number', 'label' => 'Maksimum']
                ]
            ],
        ];
    }
}
