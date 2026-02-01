<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class DashboardFilterService
{
    /**
     * Dinamik filtreleri query'ye uygula
     * Filtre format: ['type' => 'license_expiring', 'days' => 30]
     */
    public function applyFilters(Builder $query, array $filters, string $dataSource): Builder
    {
        foreach ($filters as $filter) {
            $query = match ($filter['type'] ?? null) {
                // Lisans filtreleri (Marka modeli için)
                'license_expiring' => $this->filterLicenseExpiring($query, $filter['days'] ?? 30, $dataSource),
                'license_expired' => $this->filterLicenseExpired($query, $dataSource),

                // Güncelleme filtreleri
                'not_updated_days' => $this->filterNotUpdatedDays($query, $filter['days'] ?? 30, $dataSource),
                'updated_after' => $this->filterUpdatedAfter($query, $filter['date'] ?? null, $dataSource),

                // Tarih aralığı
                'date_range' => $this->filterDateRange($query, $filter, $dataSource),

                // Durum filtreleri
                'status' => $this->filterByStatus($query, $filter['field'] ?? null, $filter['value'] ?? null, $dataSource),

                // Metin araması
                'text_search' => $this->filterTextSearch($query, $filter['field'] ?? 'name', $filter['value'] ?? '', $dataSource),

                // Sayısal aralık
                'numeric_range' => $this->filterNumericRange($query, $filter, $dataSource),

                default => $query,
            };
        }

        return $query;
    }

    private function filterLicenseExpiring(Builder $query, int $days, string $dataSource): Builder
    {
        if ($dataSource !== 'markalar') return $query;

        return $query->whereBetween('lisans_bitis_tarihi', [
            Carbon::now(),
            Carbon::now()->addDays($days)
        ]);
    }

    private function filterLicenseExpired(Builder $query, string $dataSource): Builder
    {
        if ($dataSource !== 'markalar') return $query;

        return $query->where('lisans_bitis_tarihi', '<', Carbon::now());
    }

    private function filterNotUpdatedDays(Builder $query, int $days, string $dataSource): Builder
    {
        $dateField = match ($dataSource) {
            'tum_isler' => 'updated_at',
            'musteriler' => 'updated_at',
            'ziyaretler' => 'updated_at',
            'kisiler' => 'updated_at',
            default => 'updated_at',
        };

        return $query->where($dateField, '<', Carbon::now()->subDays($days));
    }

    private function filterUpdatedAfter(Builder $query, ?string $date, string $dataSource): Builder
    {
        if (!$date) return $query;

        $dateField = match ($dataSource) {
            'tum_isler' => 'updated_at',
            'musteriler' => 'updated_at',
            default => 'updated_at',
        };

        return $query->where($dateField, '>=', Carbon::parse($date));
    }

    private function filterDateRange(Builder $query, array $filter, string $dataSource): Builder
    {
        $field = $filter['field'] ?? 'created_at';
        $from = isset($filter['from']) ? Carbon::parse($filter['from']) : null;
        $to = isset($filter['to']) ? Carbon::parse($filter['to']) : null;

        if ($from) $query = $query->where($field, '>=', $from);
        if ($to) $query = $query->where($field, '<=', $to);

        return $query;
    }

    private function filterByStatus(Builder $query, ?string $field, ?string $value, string $dataSource): Builder
    {
        if (!$field || !$value) return $query;

        // Müşteri için özel işlem - ismi ID'ye çevir
        if ($field === 'musteri_id') {
            $customer = \App\Models\Musteri::where('sirket', $value)->first();
            if ($customer) {
                return $query->where('musteri_id', $customer->id);
            }
            return $query;
        }

        // Normal filtreleme
        return $query->where($field, $value);
    }

    private function filterTextSearch(Builder $query, string $field, string $value, string $dataSource): Builder
    {
        if (!$value) return $query;

        return $query->where($field, 'LIKE', "%{$value}%");
    }

    private function filterNumericRange(Builder $query, array $filter, string $dataSource): Builder
    {
        $field = $filter['field'] ?? null;
        if (!$field) return $query;

        if (isset($filter['min'])) {
            $query = $query->where($field, '>=', $filter['min']);
        }
        if (isset($filter['max'])) {
            $query = $query->where($field, '<=', $filter['max']);
        }

        return $query;
    }

    /**
     * Mevcut filtreleri Türkçe açıklama olarak döndür
     */
    public function formatFilterLabel(array $filter): string
    {
        $type = $filter['type'] ?? 'unknown';
        return match ($type) {
            'license_expiring' => "Lisansı " . ($filter['days'] ?? 30) . " gün içinde biten",
            'license_expired' => 'Lisansı süresi dolmuş',
            'not_updated_days' => ($filter['days'] ?? 30) . " gündür güncellenmemiş",
            'updated_after' => "Şuradan sonra güncellenen: " . ($filter['date'] ?? ''),
            'status' => "Durum: " . ($filter['value'] ?? ''),
            'text_search' => ($filter['field'] ?? 'Ad') . " içinde: " . ($filter['value'] ?? ''),
            'numeric_range' => ($filter['field'] ?? 'Alan') . ": " . ($filter['min'] ?? '0') . " - " . ($filter['max'] ?? 'sınırsız'),
            default => 'Bilinmeyen filtre',
        };
    }

    /**
     * Bir alan'ın benzersiz değerlerini getir
     */
    public function getDistinctValues(Builder $query, string $field): array
    {
        try {
            // Müşteri ID'si için özel işlem - isim getir
            if ($field === 'musteri_id') {
                $customerIds = $query->whereNotNull($field)
                    ->distinct()
                    ->pluck($field)
                    ->filter(fn($val) => !empty($val))
                    ->toArray();
                
                $customers = \App\Models\Musteri::whereIn('id', $customerIds)
                    ->pluck('sirket', 'id')
                    ->toArray();
                
                return array_values($customers);
            }
            
            // Diğer alanlar için normal işlem
            $values = $query->whereNotNull($field)
                ->distinct()
                ->pluck($field)
                ->filter(fn($val) => !empty($val))
                ->take(50)
                ->toArray();
            
            return array_map(fn($val) => (string)$val, $values);
        } catch (\Exception $e) {
            return [];
        }
    }
}
