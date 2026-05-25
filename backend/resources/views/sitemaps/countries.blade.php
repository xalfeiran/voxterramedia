<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">

    @foreach ($countries as $country)
    <url>
        <loc>{{ $baseUrl }}/countries/{{ strtolower($country->code) }}</loc>
        <lastmod>{{ $country->updated_at->toDateString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>

        {{-- Country pages are served in English and Spanish --}}
        <xhtml:link rel="alternate" hreflang="en"        href="{{ $baseUrl }}/countries/{{ strtolower($country->code) }}" />
        <xhtml:link rel="alternate" hreflang="es"        href="{{ $baseUrl }}/countries/{{ strtolower($country->code) }}" />
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ $baseUrl }}/countries/{{ strtolower($country->code) }}" />
    </url>
    @endforeach

</urlset>
