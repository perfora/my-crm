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
            ], 500);
        }

        return response()->json([
            'success' => true,
            'events' => $result['events'],
        ]);
    }
}
