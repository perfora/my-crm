<?php

namespace App\Http\Controllers;

use App\Services\ExchangeEwsService;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(ExchangeEwsService $ews)
    {
        $start = now()->startOfDay();
        $end = now()->addDays(30)->endOfDay();

        $result = $ews->getCalendarEvents($start, $end);

        if (isset($result['error'])) {
            return view('takvim.index', [
                'events' => [],
                'error' => $result['error'],
            ]);
        }

        return view('takvim.index', [
            'events' => $result['events'],
            'error' => null,
        ]);
    }

    public function sync(ExchangeEwsService $ews)
    {
        $start = now()->startOfDay();
        $end = now()->addDays(30)->endOfDay();

        $result = $ews->getCalendarEvents($start, $end);
        if (isset($result['error'])) {
            return response()->json([
                'success' => false,
                'error' => $result['error'],
            ], 500, [], JSON_INVALID_UTF8_SUBSTITUTE);
        }

        $events = $this->sanitizeUtf8($result['events'] ?? []);

        return response()->json([
            'success' => true,
            'events' => $events,
        ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }

    public function cleanup(ExchangeEwsService $ews)
    {
        $targetStart = now()->subDays(30)->startOfDay();
        $targetEnd = now()->addDays(60)->endOfDay();

        $crmIds = \App\Models\Ziyaret::whereIn('durumu', ['Beklemede', 'Planlandı'])
            ->where(function ($q) use ($targetStart, $targetEnd) {
                $q->whereBetween('ziyaret_tarihi', [$targetStart, $targetEnd])
                  ->orWhereBetween('arama_tarihi', [$targetStart, $targetEnd]);
            })
            ->pluck('ews_item_id')
            ->filter()
            ->values()
            ->all();

        $crmSet = array_flip($crmIds);

        $fetchStart = now()->subDays(60)->startOfDay();
        $fetchEnd = now()->addDays(120)->endOfDay();
        $result = $ews->getCalendarEvents($fetchStart, $fetchEnd);
        if (isset($result['error'])) {
            return response()->json([
                'success' => false,
                'error' => $result['error'],
            ], 500, [], JSON_INVALID_UTF8_SUBSTITUTE);
        }

        $events = $result['events'] ?? [];
        $deleted = 0;
        $checked = 0;

        foreach ($events as $event) {
            $itemId = $event['item_id'] ?? '';
            $start = $event['start'] ?? null;
            if (!$itemId || !$start) {
                continue;
            }

            $startAt = \Carbon\Carbon::parse($start);
            if ($startAt->lt($targetStart) || $startAt->gt($targetEnd)) {
                continue;
            }
            $checked++;

            if (!isset($crmSet[$itemId])) {
                $ews->deleteVisitEvent($itemId, $event['change_key'] ?? null);
                $deleted++;
            }
        }

        return response()->json([
            'success' => true,
            'checked' => $checked,
            'deleted' => $deleted,
        ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }

    public function pushCrm(ExchangeEwsService $ews)
    {
        $start = now()->subDays(30)->startOfDay();
        $end = now()->addDays(60)->endOfDay();

        $ziyaretler = \App\Models\Ziyaret::whereIn('durumu', ['Beklemede', 'Planlandı'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('ziyaret_tarihi', [$start, $end])
                  ->orWhereBetween('arama_tarihi', [$start, $end]);
            })
            ->get();

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($ziyaretler as $ziyaret) {
            $subject = $ziyaret->ziyaret_ismi ?: 'Ziyaret';
            $startAt = $ziyaret->ziyaret_tarihi
                ? \Carbon\Carbon::parse($ziyaret->ziyaret_tarihi, 'Europe/Istanbul')
                : null;
            if (!$startAt && $ziyaret->arama_tarihi) {
                $startAt = \Carbon\Carbon::parse($ziyaret->arama_tarihi, 'Europe/Istanbul');
                if ((int) $startAt->format('H') === 0 && (int) $startAt->format('i') === 0) {
                    $startAt->setTime(9, 0);
                }
            }
            if (!$startAt) {
                $skipped++;
                continue;
            }
            $endAt = $startAt->copy()->addMinutes(30);
            $body = $ziyaret->ziyaret_notlari ?? '';

            $result = $ews->createOrUpdateVisitEvent(
                $ziyaret->ews_item_id,
                $ziyaret->ews_change_key,
                $subject,
                $startAt,
                $endAt,
                $body
            );

            if (!empty($result['error'])) {
                $errors++;
                continue;
            }

            if (!empty($result['item_id'])) {
                $ziyaret->update([
                    'ews_item_id' => $result['item_id'],
                    'ews_change_key' => $result['change_key'] ?? $ziyaret->ews_change_key,
                ]);

                if ($ziyaret->wasChanged('ews_item_id')) {
                    $created++;
                } else {
                    $updated++;
                }
            } else {
                $skipped++;
            }
        }

        return response()->json([
            'success' => true,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
        ], 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }

    private function sanitizeUtf8($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'sanitizeUtf8'], $value);
        }

        if (is_string($value)) {
            $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
            return $converted !== false ? $converted : $value;
        }

        return $value;
    }
}
