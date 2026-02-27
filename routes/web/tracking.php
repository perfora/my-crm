<?php

Route::middleware(['auth'])->group(function () {
    Route::get('/api/saved-filters', [\App\Http\Controllers\Api\TrackingController::class, 'savedFiltersIndex']);
    Route::post('/api/saved-filters', [\App\Http\Controllers\Api\TrackingController::class, 'savedFiltersStore']);
    Route::delete('/api/saved-filters/{name}', [\App\Http\Controllers\Api\TrackingController::class, 'savedFiltersDestroy']);

    Route::post('/api/client-errors', [\App\Http\Controllers\Api\TrackingController::class, 'clientErrors'])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

    Route::get('/api/change-journals', [\App\Http\Controllers\Api\TrackingController::class, 'changeJournalsIndex']);
    Route::post('/api/change-journals', [\App\Http\Controllers\Api\TrackingController::class, 'changeJournalsStore']);
});
