<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BulkImportController;
use App\Http\Controllers\Api\V1\CityController;
use App\Http\Controllers\Api\V1\CountryController;
use App\Http\Controllers\Api\V1\MediaOutletController;
use App\Http\Controllers\Api\V1\RegionController;
use App\Http\Controllers\Api\V1\StatsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — /api/v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ── Public (60 req/min) ────────────────────────────────────────────────
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/countries',              [CountryController::class, 'index']);
        Route::get('/countries/{code}',       [CountryController::class, 'show']);

        Route::get('/regions',                [RegionController::class, 'index']);
        Route::get('/cities',                 [CityController::class, 'index']);

        Route::get('/media-outlets/map',      [MediaOutletController::class, 'map']);
        Route::get('/media-outlets',          [MediaOutletController::class, 'index']);
        Route::get('/media-outlets/{slug}',   [MediaOutletController::class, 'show']);

        Route::get('/stats',                  [StatsController::class, 'index']);
    });

    // ── Auth (10 req/min) ──────────────────────────────────────────────────
    Route::middleware('throttle:10,1')->prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me',      [AuthController::class, 'me']);
        });
    });

    // ── Admin (auth required) ──────────────────────────────────────────────
    Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
        Route::apiResource('media-outlets', MediaOutletController::class)
            ->except(['index', 'show'])
            ->names('admin.media-outlets');

        Route::post('media-outlets/{mediaOutlet}/toggle-featured',
            [MediaOutletController::class, 'toggleFeatured']
        )->name('admin.media-outlets.toggle-featured');

        Route::post('media-outlets/bulk-import',
            [BulkImportController::class, 'import']
        )->name('admin.media-outlets.bulk-import');
    });
});
