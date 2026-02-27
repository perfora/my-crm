<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\TumIsler;
use App\Services\TcmbExchangeService;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\PageController;

if (!function_exists('crmToIstanbulCarbon')) {
    function crmToIstanbulCarbon($value): \Carbon\Carbon
    {
        if ($value instanceof \Carbon\Carbon) {
            // DB'de timezone'suz saklanan tarihleri "yerel saat" olarak yorumla.
            return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value->format('Y-m-d H:i:s'), 'Europe/Istanbul');
        }

        return \Carbon\Carbon::parse($value, 'Europe/Istanbul');
    }
}

if (!function_exists('crmAutoFillTcmKur')) {
    function crmAutoFillTcmKur(array $validated, ?TumIsler $existing = null): array
    {
        $finalTipi = $validated['tipi'] ?? ($existing?->tipi);
        $finalKapanis = $validated['kapanis_tarihi'] ?? ($existing?->kapanis_tarihi);
        $finalKur = array_key_exists('kur', $validated) ? $validated['kur'] : ($existing?->kur);

        $kurBos = $finalKur === null || $finalKur === '' || (is_numeric($finalKur) && (float) $finalKur <= 0);
        if ($finalTipi !== 'Kazanıldı' || empty($finalKapanis) || !$kurBos) {
            return $validated;
        }

        $tcmbRate = app(TcmbExchangeService::class)->getUsdSellingRateForDate($finalKapanis);
        if ($tcmbRate !== null) {
            $validated['kur'] = $tcmbRate;
        }

        return $validated;
    }
}

// Login/Logout Routes (no auth middleware)
Route::get('/finans', [PageController::class, 'finans'])->name('finans')->middleware('auth');

Route::get('/login', [SessionController::class, 'create'])->name('login')->middleware('guest');
Route::post('/login', [SessionController::class, 'store'])->middleware('guest');
Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');

// Static asset compatibility routes for environments serving files under /public
Route::get('/favicon.ico', fn () => redirect('/public/favicon.ico', 301));
Route::get('/favicon.svg', fn () => redirect('/public/favicon.svg', 301));
Route::get('/apple-touch-icon.png', fn () => redirect('/public/apple-touch-icon.png', 301));

require __DIR__ . '/web/authenticated.php';
require __DIR__ . '/web/tracking.php';
