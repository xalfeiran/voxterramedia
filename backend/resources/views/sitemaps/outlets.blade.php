<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">

    @foreach ($outlets as $outlet)
    <url>
        <loc>{{ $baseUrl }}/outlets/{{ $outlet->slug }}</loc>
        <lastmod>{{ $outlet->updated_at->toDateString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>

        {{-- Hreflang: same URL serves all locales (language is per-outlet, not per-URL) --}}
        <xhtml:link rel="alternate" hreflang="{{ $outlet->language ?? 'en' }}" href="{{ $baseUrl }}/outlets/{{ $outlet->slug }}" />
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ $baseUrl }}/outlets/{{ $outlet->slug }}" />

        @if (!empty($outlet->logo_url))
        <image:image>
            <image:loc>{{ $outlet->logo_url }}</image:loc>
            <image:title>{{ htmlspecialchars($outlet->name, ENT_XML1 | ENT_QUOTES, 'UTF-8') }} logo</image:title>
        </image:image>
        @endif
    </url>
    @endforeach

</urlset>
