<?php

use App\Http\Controllers\Ai\ReadOnlyController;
use Illuminate\Support\Facades\Route;

Route::prefix('ai')
    ->middleware(['ai.auth', 'ai.audit', 'throttle:ai-read', 'ai.scope:crm.read'])
    ->group(function (): void {
        Route::get('/summary/dashboard', [ReadOnlyController::class, 'dashboardSummary'])->name('ai.summary.dashboard');
        Route::get('/tum-isler', [ReadOnlyController::class, 'tumIsler'])->name('ai.tum-isler.index');
        Route::get('/musteriler', [ReadOnlyController::class, 'musteriler'])->name('ai.musteriler.index');
        Route::get('/ziyaretler', [ReadOnlyController::class, 'ziyaretler'])->name('ai.ziyaretler.index');
    });

