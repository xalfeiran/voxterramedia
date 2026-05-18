<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    {{-- Static pages live on the frontend domain --}}
    <sitemap>
        <loc>{{ $baseUrl }}/sitemap-static.xml</loc>
        <lastmod>{{ $lastmod }}</lastmod>
    </sitemap>

    {{-- Dynamic sitemaps are served by this API --}}
    <sitemap>
        <loc>{{ $apiUrl }}/sitemap-countries.xml</loc>
        <lastmod>{{ $lastmod }}</lastmod>
    </sitemap>

    <sitemap>
        <loc>{{ $apiUrl }}/sitemap-outlets.xml</loc>
        <lastmod>{{ $lastmod }}</lastmod>
    </sitemap>

</sitemapindex>
