<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\MediaOutlet;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SitemapController extends Controller
{
    private const BASE_URL = 'https://voxterra.media';

    // ── Sitemap index ─────────────────────────────────────────────────────

    public function index(): Response
    {
        $lastmod = Carbon::now()->toAtomString();

        $xml = view('sitemaps.index', [
            'baseUrl' => self::BASE_URL,
            'lastmod' => $lastmod,
        ])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    // ── Static pages ──────────────────────────────────────────────────────

    public function static(): Response
    {
        $pages = [
            ['loc' => '/',        'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => '/explore', 'priority' => '0.9', 'changefreq' => 'daily'],
        ];

        $xml = view('sitemaps.static', [
            'baseUrl' => self::BASE_URL,
            'pages'   => $pages,
            'lastmod' => Carbon::now()->toDateString(),
        ])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    // ── Countries ─────────────────────────────────────────────────────────

    public function countries(): Response
    {
        $countries = Country::select('code', 'updated_at')
            ->orderBy('code')
            ->get();

        $xml = view('sitemaps.countries', [
            'baseUrl'   => self::BASE_URL,
            'countries' => $countries,
        ])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    // ── Outlets (paginated, 500 per sitemap) ──────────────────────────────

    public function outlets(int $page = 1): Response
    {
        $perPage = 500;

        $outlets = MediaOutlet::where('is_active', true)
            ->select('slug', 'updated_at')
            ->orderBy('id')
            ->forPage($page, $perPage)
            ->get();

        $xml = view('sitemaps.outlets', [
            'baseUrl' => self::BASE_URL,
            'outlets' => $outlets,
        ])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    // ── Outlet sitemap index (one entry per page of 500) ─────────────────

    public function outletIndex(): Response
    {
        $total   = MediaOutlet::where('is_active', true)->count();
        $perPage = 500;
        $pages   = max(1, (int) ceil($total / $perPage));

        $xml = view('sitemaps.outlet-index', [
            'baseUrl' => self::BASE_URL,
            'pages'   => $pages,
            'lastmod' => Carbon::now()->toAtomString(),
        ])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
