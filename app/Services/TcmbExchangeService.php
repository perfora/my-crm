<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TcmbExchangeService
{
    public function getUsdSellingRateForDate($date): ?float
    {
        $target = Carbon::parse($date, 'Europe/Istanbul')->startOfDay();

        // Hafta sonu / tatil durumları için geriye doğru dene
        for ($i = 0; $i <= 7; $i++) {
            $day = $target->copy()->subDays($i);
            $rate = $this->fetchUsdSellingRate($day);
            if ($rate !== null) {
                return $rate;
            }
        }

        return null;
    }

    private function fetchUsdSellingRate(Carbon $day): ?float
    {
        $url = sprintf(
            'https://www.tcmb.gov.tr/kurlar/%s/%s.xml',
            $day->format('Ym'),
            $day->format('dmY')
        );

        try {
            $response = Http::timeout(10)->get($url);
            if (!$response->ok()) {
                return null;
            }

            $xml = @simplexml_load_string($response->body());
            if (!$xml) {
                return null;
            }

            foreach ($xml->Currency as $currency) {
                $code = (string) ($currency['CurrencyCode'] ?? '');
                if ($code !== 'USD') {
                    continue;
                }

                // Döviz satış öncelikli, yoksa efektif satış fallback
                $value = trim((string) ($currency->ForexSelling ?? ''));
                if ($value === '') {
                    $value = trim((string) ($currency->BanknoteSelling ?? ''));
                }

                if ($value === '') {
                    return null;
                }

                return (float) str_replace(',', '.', $value);
            }
        } catch (\Throwable $e) {
            Log::warning('TCMB kur alınamadı', [
                'date' => $day->toDateString(),
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }
}

