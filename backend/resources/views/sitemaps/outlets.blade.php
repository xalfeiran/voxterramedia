<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    @foreach ($outlets as $outlet)
    <url>
        <loc>{{ $baseUrl }}/outlets/{{ $outlet->slug }}</loc>
        <lastmod>{{ $outlet->updated_at->toDateString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach

</urlset>
