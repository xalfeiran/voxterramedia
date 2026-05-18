<?php

use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| These routes are served by Nginx without the /api prefix.
| The sitemap.xml and related URLs are proxied here from the frontend nginx
| config so that they are generated dynamically from the database.
*/

// ── Sitemap index ─────────────────────────────────────────────────────────
Route::get('/sitemap.xml', [SitemapController::class, 'index'])
    ->name('sitemap.index');

// ── Sub-sitemaps ──────────────────────────────────────────────────────────
Route::get('/sitemap-static.xml',   [SitemapController::class, 'static'])
    ->name('sitemap.static');

Route::get('/sitemap-countries.xml', [SitemapController::class, 'countries'])
    ->name('sitemap.countries');

// Single outlets sitemap (up to 500 entries — fine for most catalogs)
Route::get('/sitemap-outlets.xml', [SitemapController::class, 'outlets'])
    ->name('sitemap.outlets');

// Paginated outlets sitemaps for very large catalogs (> 500 outlets)
Route::get('/sitemap-outlets-{page}.xml', [SitemapController::class, 'outlets'])
    ->where('page', '[0-9]+')
    ->name('sitemap.outlets.page');
