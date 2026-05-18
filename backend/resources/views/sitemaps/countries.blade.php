<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    @foreach ($countries as $country)
    <url>
        <loc>{{ $baseUrl }}/countries/{{ strtolower($country->code) }}</loc>
        <lastmod>{{ $country->updated_at->toDateString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach

</urlset>
