<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">

    @foreach ($pages as $page)
    <url>
        <loc>{{ $baseUrl }}{{ $page['loc'] }}</loc>
        <lastmod>{{ $lastmod }}</lastmod>
        <changefreq>{{ $page['changefreq'] }}</changefreq>
        <priority>{{ $page['priority'] }}</priority>

        <xhtml:link rel="alternate" hreflang="en"        href="{{ $baseUrl }}{{ $page['loc'] }}" />
        <xhtml:link rel="alternate" hreflang="es"        href="{{ $baseUrl }}{{ $page['loc'] }}" />
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ $baseUrl }}{{ $page['loc'] }}" />
    </url>
    @endforeach

</urlset>
