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
