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
